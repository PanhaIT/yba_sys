<?php

class DeliveriesController extends AppController {

    var $name = 'Deliveries';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = 'ajax';
    }

    function ajax($status) {
        $this->layout = 'ajax';
        $this->set(compact('status'));
    }
    
    function pickSlip($id = null){
        $this->layout = 'ajax';
        if(empty($id) && empty($this->data)){
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        if(!empty($this->data)){
            // Error = 1: Can not save
            $result = array();
            if($this->data['sales_order_id'] != ''){
                $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $this->data['sales_order_id'])));
                if(!empty($salesOrder) && $salesOrder['SalesOrder']['status'] == 1){
                    $access = true;
                    $productOrder = array();
                    // Get Loction Setting
                    $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
                    $locCon     = '';
                    if($locSetting['LocationSetting']['location_status'] == 1){
                        $locCon = ' AND is_for_sale = 1';
                    }
                    $sqlDetail = mysql_query("SELECT id, product_id, SUM((IFNULL(qty,0) + IFNULL(qty_free,0)) * conversion) AS qty FROM sales_order_details WHERE sales_order_id =".$this->data['sales_order_id']." AND id NOT IN (SELECT sales_order_detail_id FROM delivery_details GROUP BY sales_order_detail_id) GROUP BY product_id");
                    if(@mysql_num_rows($sqlDetail)) {
                        // Check With Current Stock
                        while($rowDetail = mysql_fetch_array($sqlDetail)){
                            // Check Not Exist in DN Detail
                            $sqlCheckDn = mysql_query("SELECT id FROM delivery_details WHERE sales_order_detail_id=".$rowDetail['id']);
                            if(!mysql_num_rows($sqlCheckDn)){
                                if (array_key_exists($rowDetail['product_id'], $productOrder)){
                                    $productOrder[$rowDetail['product_id']]['qty'] += $rowDetail['qty'];
                                } else {
                                    $productOrder[$rowDetail['product_id']]['qty'] = $rowDetail['qty'];
                                }
                            }
                        }
                    }
                    // Check Qty in Stock Before Save
                    foreach($productOrder AS $key => $order){
                        $sqlTotal = mysql_query("SELECT (SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) + (SELECT IFNULL(SUM(qty),0) FROM stock_orders WHERE product_id = ".$key." AND sales_order_id = ".$salesOrder['SalesOrder']['id'].")) AS total_qty FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals WHERE product_id = ".$key." AND location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id'].$locCon.") GROUP BY product_id;");
                        $rowTotal = mysql_fetch_array($sqlTotal);
                        if($rowTotal['total_qty'] < $order['qty']){
                            $access = false;
                        }
                    }
                    if($access == true) {
                        $r = 0;
                        $restCode = array();
                        $dateNow  = date("Y-m-d H:i:s");
                        $this->loadModel('DeliveryDetail');
                        // Delivery
                        $this->Delivery->create();
                        $this->data['Delivery']['sys_code']     = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->data['Delivery']['company_id']   = $salesOrder['SalesOrder']['company_id'];
                        $this->data['Delivery']['branch_id']    = $salesOrder['SalesOrder']['branch_id'];
                        $this->data['Delivery']['warehouse_id'] = $salesOrder['SalesOrder']['location_group_id'];
                        $this->data['Delivery']['date'] = date("Y-m-d");
                        $this->data['Delivery']['code'] = NULL;
                        $this->data['Delivery']['created']    = $dateNow;
                        $this->data['Delivery']['created_by'] = $user['User']['id'];
                        $this->data['Delivery']['status']  = 2;
                        $this->Delivery->save($this->data);
                        $deliveryId = $this->Delivery->id;

                        $result['error']    = 0;
                        $result['delivery_id'] = $deliveryId;
                        // Update Code Delivery
                        $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $salesOrder['SalesOrder']['branch_id'])));
                        $dnCode     = date("y").$modComCode['ModuleCodeBranch']['dn_code'];
                        // Get Module Code
                        $modCode    = $this->Helper->getModuleCode($dnCode, $deliveryId, 'code', 'deliveries', 'status >= 0');
                        // Updaet Module Code
                        mysql_query("UPDATE deliveries SET code = '".$modCode."' WHERE id = ".$deliveryId);
                        $dateSale  = $salesOrder['SalesOrder']['order_date'];
                        // Convert to REST
                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['Delivery'], 'deliveries');
                        $restCode[$r]['code']     = $modCode;
                        $restCode[$r]['modified'] = $dateNow;
                        $restCode[$r]['dbtodo']   = 'deliveries';
                        $restCode[$r]['actodo']   = 'is';
                        $r++;
                        // List Sale Detail
                        $sqlDetail = mysql_query("SELECT id, product_id, SUM((IFNULL(qty,0) + IFNULL(qty_free,0)) * conversion) AS total_qty, SUM(IFNULL(qty,0)) AS qty_order, SUM(IFNULL(qty_free,0)) AS qty_free, (SELECT unit_cost FROM products WHERE id = sales_order_details.product_id) AS product_cost, (SELECT small_val_uom FROM products WHERE id = sales_order_details.product_id) AS small_val_uom, (total_price - IFNULL(discount_amount, 0)) AS total_price FROM sales_order_details WHERE sales_order_id =".$this->data['sales_order_id']." AND id NOT IN (SELECT sales_order_detail_id FROM delivery_details GROUP BY sales_order_detail_id) GROUP BY product_id");
                        while($rowDetail = mysql_fetch_array($sqlDetail)){
                            $sqlCheckDn = mysql_query("SELECT id FROM delivery_details WHERE sales_order_detail_id=".$rowDetail['id']);
                            if(!mysql_num_rows($sqlCheckDn)){
                                $totalOrder = $rowDetail['total_qty'];
                                $qtyOrder   = $rowDetail['qty_order'];
                                $qtyFree    = $rowDetail['qty_free'];

                                // Update Inventory Location Group
                                $dataGroup = array();
                                $dataGroup['module_type']       = 10;
                                $dataGroup['sales_order_id']    = $salesOrder['SalesOrder']['id'];
                                $dataGroup['product_id']        = $rowDetail['product_id'];
                                $dataGroup['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                $dataGroup['date']         = $dateSale;
                                $dataGroup['total_qty']    = $totalOrder;
                                $dataGroup['total_order']  = $qtyOrder;
                                $dataGroup['total_free']   = $qtyFree;
                                // Update Inventory Group
                                $this->Inventory->saveGroupTotalDetail($dataGroup);
                                // Convert to REST
                                $restCode[$r]['module_type']       = 10;
                                $restCode[$r]['sales_order_id']    = $this->Helper->getSQLSyncCode("sales_orders", $salesOrder['SalesOrder']['id']);
                                $restCode[$r]['date']              = $dateSale;
                                $restCode[$r]['total_qty']         = $totalOrder;
                                $restCode[$r]['total_order']       = $qtyOrder;
                                $restCode[$r]['total_free']        = $qtyFree;
                                $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $rowDetail['product_id']);
                                $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $salesOrder['SalesOrder']['location_group_id']);
                                $restCode[$r]['dbtype']  = 'GroupDetail';
                                $restCode[$r]['actodo']  = 'inv';
                                $r++;
                                
                                // Calculate Location, Lot, Expired Date
                                $sqlOrder = mysql_query("SELECT * FROM stock_orders WHERE product_id = ".$rowDetail['product_id']." AND sales_order_id = ".$salesOrder['SalesOrder']['id']." AND sales_order_detail_id = ".$rowDetail['id']);
                                while($rowOrder = mysql_fetch_array($sqlOrder)){
                                    if($rowDetail['total_price'] > 0){
                                        $unitPrice = $this->Helper->replaceThousand(number_format($rowDetail['total_price'] / $rowOrder['qty'], 9));
                                    } else {
                                        $unitPrice = 0;
                                    }
                                    // Reset Stock Order
                                    $this->Inventory->saveGroupQtyOrder($rowOrder['location_group_id'], $rowOrder['location_id'], $rowOrder['product_id'], $rowOrder['lots_number'], $rowOrder['expired_date'], $rowOrder['qty'], $dateSale, '-');
                                    // Convert to REST
                                    $restCode[$r]['group']    = $this->Helper->getSQLSyncCode("location_groups", $rowOrder['location_group_id']);
                                    $restCode[$r]['location'] = $this->Helper->getSQLSyncCode("locations", $rowOrder['location_id']);
                                    $restCode[$r]['product']  = $this->Helper->getSQLSyncCode("products", $rowOrder['product_id']);
                                    $restCode[$r]['lots']   = $rowOrder['lots_number'];
                                    $restCode[$r]['expd']   = $rowOrder['expired_date'];
                                    $restCode[$r]['qty']    = $rowOrder['qty'];
                                    $restCode[$r]['date']   = $dateSale;
                                    $restCode[$r]['syml']   = '-';
                                    $restCode[$r]['dbtype'] = 'saveOrder';
                                    $restCode[$r]['actodo'] = 'inv';
                                    $r++;
                                    // Get Lots, Expired, Total Qty
                                    $invInfos   = array();
                                    $index      = 0;
                                    $totalOrder = $rowOrder['qty'];
                                    // Calculate Location, Lot, Expired Date
                                    $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$rowOrder['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id IN (SELECT id FROM locations WHERE location_group_id = ".$rowOrder['location_group_id'].$locCon.") AND group_totals.product_id = ".$rowOrder['product_id']." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.lots_number, group_totals.expired_date, group_totals.location_id ASC");
                                    while($rowInventory = mysql_fetch_array($sqlInventory)) {
                                        // Check Order
                                        $stockOrder = 0;
                                        $sqlStock = mysql_query("SELECT SUM(qty) FROM stock_orders WHERE sales_order_id IS NULL AND consignment_id IS NULL AND product_id = ".$rowOrder['product_id']." AND location_group_id = ".$rowOrder['location_group_id']." AND location_id = ".$rowInventory['location_id']." AND lots_number = '".$rowInventory['lots_number']."' AND expired_date = '".$rowInventory['expired_date']."'");
                                        if(mysql_num_rows($sqlStock)){
                                            $rowStock = mysql_fetch_array($sqlStock);
                                            $stockOrder = $rowStock[0];
                                        }
                                        $totalStock = $rowInventory['total_qty'] - $stockOrder;
                                        if($totalOrder > 0 && $totalStock > 0){
                                            if($totalStock >= $totalOrder) {
                                                $invInfos[$index]['total_qty']    = $totalOrder;
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = 0;
                                                ++$index;
                                            } else if($totalStock < $totalOrder) {
                                                $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = $totalOrder - $totalStock;
                                                ++$index;
                                            }
                                        }
                                    }
                                    // Cut Stock
                                    foreach($invInfos AS $invInfo){
                                        // Update Inventory (Sales)
                                        $data = array();
                                        $data['module_type']       = 10;
                                        $data['sales_order_id']    = $salesOrder['SalesOrder']['id'];
                                        $data['product_id']        = $rowOrder['product_id'];
                                        $data['location_id']       = $invInfo['location_id'];
                                        $data['location_group_id'] = $rowOrder['location_group_id'];
                                        $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                                        $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                                        $data['date']         = $dateSale;
                                        $data['total_qty']    = $invInfo['total_qty'];
                                        $data['total_order']  = $invInfo['total_qty'];
                                        $data['total_free']   = 0;
                                        $data['user_id']      = $user['User']['id'];
                                        $data['customer_id']  = $salesOrder['SalesOrder']['customer_id'];
                                        $data['vendor_id']    = "";
                                        $data['unit_cost']    = 0;
                                        $data['unit_price']   = $unitPrice;
                                        // Update Invetory Location
                                        $this->Inventory->saveInventory($data);
                                        // Convert to REST
                                        $restCode[$r] = $this->Helper->convertToDataSync($data, 'inventories');
                                        $restCode[$r]['module_type']  = 10;
                                        $restCode[$r]['total_qty']    = $invInfo['total_qty'];
                                        $restCode[$r]['total_order']  = $invInfo['total_qty'];
                                        $restCode[$r]['total_free']   = 0;
                                        $restCode[$r]['expired_date'] = $data['expired_date'];
                                        $restCode[$r]['vendor_id']    = "";
                                        $restCode[$r]['unit_cost']    = 0;
                                        $restCode[$r]['customer_id']  = $this->Helper->getSQLSyncCode("customers", $salesOrder['SalesOrder']['customer_id']);
                                        $restCode[$r]['sales_order_id']    = $this->Helper->getSQLSyncCode("sales_orders", $salesOrder['SalesOrder']['id']);
                                        $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $rowOrder['product_id']);
                                        $restCode[$r]['location_id']       = $this->Helper->getSQLSyncCode("locations", $invInfo['location_id']);
                                        $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $salesOrder['SalesOrder']['location_group_id']);
                                        $restCode[$r]['user_id']           = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
                                        $restCode[$r]['dbtype']  = 'saveInv';
                                        $restCode[$r]['actodo']  = 'inv';
                                        $r++;

                                        //Insert Into Delivery Detail
                                        $deliveyDetail = array();
                                        $this->DeliveryDetail->create();
                                        $deliveyDetail['DeliveryDetail']['delivery_id']    = $deliveryId;
                                        $deliveyDetail['DeliveryDetail']['sales_order_id'] = $salesOrder['SalesOrder']['id'];
                                        $deliveyDetail['DeliveryDetail']['sales_order_detail_id'] = $rowDetail['id'];
                                        $deliveyDetail['DeliveryDetail']['product_id']    = $rowOrder['product_id'];
                                        $deliveyDetail['DeliveryDetail']['location_id']   = $invInfo['location_id'];
                                        $deliveyDetail['DeliveryDetail']['lots_number']   = 0;
                                        $deliveyDetail['DeliveryDetail']['expired_date']  = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                                        $deliveyDetail['DeliveryDetail']['total_qty']     = $invInfo['total_qty'];
                                        $this->DeliveryDetail->save($deliveyDetail);
                                        // Convert to REST
                                        $restCode[$r] = $this->Helper->convertToDataSync($deliveyDetail['DeliveryDetail'], 'delivery_details');
                                        $restCode[$r]['dbtodo']   = 'delivery_details';
                                        $restCode[$r]['actodo']   = 'is';
                                        $r++;
                                    }
                                }
                                // Update COGS
                                if($this->data['calculate_cogs'] == 2){
                                    $sqlCogs = mysql_query("SELECT * FROM inventory_unit_costs WHERE product_id = ".$rowDetail['product_id']." AND unit_cost > 0 AND total_qty > 0 ORDER BY id ASC");
                                    if(mysql_num_rows($sqlCogs)){
                                        $totalOrder = $rowDetail['total_qty'];
                                        $cogs = 0;
                                        while($rowCogs = mysql_fetch_array($sqlCogs)){
                                            if($totalOrder > 0 && $rowCogs['total_qty'] > 0){
                                                if($rowCogs['total_qty'] >= $totalOrder){
                                                    $cogs += ($rowCogs['unit_cost'] / $rowDetail['small_val_uom']) * $totalOrder;
                                                    mysql_query("UPDATE inventory_unit_costs SET total_qty = (total_qty - ".$totalOrder.") WHERE id =".$rowCogs['id']);
                                                    $totalOrder = 0;
                                                }else if($rowCogs['total_qty'] < $totalOrder){
                                                    $cogs += ($rowCogs['unit_cost'] / $rowDetail['small_val_uom']) * $rowCogs['total_qty'];
                                                    mysql_query("UPDATE inventory_unit_costs SET total_qty = (total_qty - ".$totalOrder.") WHERE id =".$rowCogs['id']);
                                                    $totalOrder = $totalOrder - $rowCogs['total_qty'];
                                                }
                                            }else{
                                                break;
                                            }
                                        }
                                        mysql_query("UPDATE general_ledger_details SET debit = ".$cogs." WHERE inventory_valuation_is_debit = 1 AND inventory_valuation_id = ".$rowDetail['id']);
                                        mysql_query("UPDATE general_ledger_details SET credit = ".$cogs." WHERE inventory_valuation_is_debit = 0 AND inventory_valuation_id = ".$rowDetail['id']);
                                    }else{
                                        $cogs = $rowDetail['product_cost'];
                                        mysql_query("UPDATE general_ledger_details SET debit = ".$cogs." WHERE inventory_valuation_is_debit = 1 AND inventory_valuation_id = ".$rowDetail['id']);
                                        mysql_query("UPDATE general_ledger_details SET credit = ".$cogs." WHERE inventory_valuation_is_debit = 0 AND inventory_valuation_id = ".$rowDetail['id']);
                                    }
                                }
                            }
                        }
                        // Update Sales Order
                        mysql_query("UPDATE sales_orders SET delivery_id = ".$deliveryId.", status = 2, `modified` = '".$dateNow."', `modified_by` = ".$user['User']['id']." WHERE  `id`= " . $this->data['sales_order_id']);
                        // Convert to REST
                        $restCode[$r]['delivery_id'] = $this->Helper->getSQLSysCode("deliveries", $deliveryId);
                        $restCode[$r]['status']      = 2;
                        $restCode[$r]['modified']    = $dateNow;
                        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                        $restCode[$r]['dbtodo'] = 'sales_orders';
                        $restCode[$r]['actodo'] = 'ut';
                        $restCode[$r]['con']    = "sys_code = '".$salesOrder['SalesOrder']['sys_code']."'";
                        $r++;
                        // Update Delivery Note Detail Pick Ready
                        mysql_query("UPDATE delivery_details SET delivery_id = '".$deliveryId."' WHERE sales_order_id = ".$this->data['sales_order_id']);
                        // Convert to REST
                        $restCode[$r]['delivery_id'] = $this->Helper->getSQLSysCode("deliveries", $deliveryId);
                        $restCode[$r]['dbtodo'] = 'delivery_details';
                        $restCode[$r]['actodo'] = 'ut';
                        $restCode[$r]['con']    = "sales_order_id = ".$salesOrder['SalesOrder']['sys_code'];
                        // Delete Tmp Stock And Delivery
                        mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`= " . $this->data['sales_order_id']);
                        // Convert to REST
                        $restCode[$r]['dbtodo'] = 'stock_orders';
                        $restCode[$r]['actodo'] = 'dt';
                        $restCode[$r]['con']    = "sales_order_id = ".$salesOrder['SalesOrder']['sys_code'];
                        // Save File Send
                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Pick', $deliveryId);
                    }else{
                        $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Pick (Error Out of Stock)', $this->data['sales_order_id']);
                        $result['error'] = 2;
                    }
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Pick (Error Status)', $this->data['sales_order_id']);
                    $result['error'] = 1;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Pick (Error Invoice dont have ID)');
                $result['error'] = 1;
            }
            echo json_encode($result);
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Pick', $id);
        $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $id)));
        if (!empty($salesOrder) && $salesOrder['SalesOrder']['status'] == 1) {
            $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $salesOrder['SalesOrder']['id'])));
            $this->set(compact('salesOrder', 'salesOrderDetails', 'id'));
        } else {
            exit;
        }
    }
    
    function view($id = null){
        $this->layout = 'ajax';
        if(empty($id)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $id)));
        if (!empty($salesOrder)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'View', $id);
            $deliveryDetails = ClassRegistry::init('DeliveryDetail')->find("all", array('conditions' => array('DeliveryDetail.delivery_id' => $salesOrder['SalesOrder']['delivery_id'])));
            $this->set(compact('salesOrder', 'deliveryDetails'));
        } else {
            exit;
        }
    }
    
    function printInvoicePickSlip($id = null){
        $this->layout = 'ajax';
        if(empty($id)){
            exit;
        }
        $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.delivery_id' => $id)));
        $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $salesOrder['SalesOrder']['id'])));
        $this->set(compact('salesOrder', 'salesOrderDetails'));
    }
    
    function pickProduct($salesOrderDetailId = null, $locationGroupId = null){
        $this->layout = 'ajax';
        if(empty($salesOrderDetailId) || empty($locationGroupId)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $salesOrderDetail = ClassRegistry::init('SalesOrderDetail')->find("first", array('conditions' => array('SalesOrderDetail.id' => $salesOrderDetailId)));
        $this->set(compact("salesOrderDetailId", "locationGroupId", "salesOrderDetail"));
    }
    
    function pickProductAjax($productId = null, $locationGroupId = null, $smallUomLabel = null){
        $this->layout = 'ajax';
        $this->set('productId', $productId);
        $this->set('locationGroupId', $locationGroupId);
        $this->set('smallUomLabel', $smallUomLabel);
    }
    
    function pickProductSave(){
        $this->layout = 'ajax';
        if(!empty($this->data)){
            $user = $this->getCurrentUser();
            $sql = mysql_query("SELECT id FROM delivery_details WHERE sales_order_detail_id = ".$this->data['sales_order_detail_id']);
            if(!mysql_num_rows($sql)){
                $r = 0;
                $restCode = array();
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Product Pick DN');
                $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $this->data['sales_order_id'])));
                $salesOrderDetail = ClassRegistry::init('SalesOrderDetail')->find("first", array('conditions' => array('SalesOrderDetail.id' => $this->data['sales_order_detail_id'])));
                $sqlPro = mysql_query("SELECT small_val_uom FROM products WHERE id = ".$this->data['product_id']);
                $rowPro = mysql_fetch_array($sqlPro);
                $this->loadModel('DeliveryDetail');
                // Reset Stock Order
                $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$this->data['sales_order_id']." AND sales_order_detail_id = ".$this->data['sales_order_detail_id'].";");
                while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                    $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                    // Convert to REST
                    $restCode[$r]['group']    = $this->Helper->getSQLSyncCode("location_groups", $rowResetOrder['location_group_id']);
                    $restCode[$r]['location'] = $this->Helper->getSQLSyncCode("locations", $rowResetOrder['location_id']);
                    $restCode[$r]['product']  = $this->Helper->getSQLSyncCode("products", $rowResetOrder['product_id']);
                    $restCode[$r]['lots']   = $rowResetOrder['lots_number'];
                    $restCode[$r]['expd']   = $rowResetOrder['expired_date'];
                    $restCode[$r]['qty']    = $rowResetOrder['qty'];
                    $restCode[$r]['date']   = $rowResetOrder['date'];
                    $restCode[$r]['syml']   = '-';
                    $restCode[$r]['dbtype'] = 'saveOrder';
                    $restCode[$r]['actodo'] = 'inv';
                    $r++;
                }
                // Detele Tmp Stock Order
                mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$this->data['sales_order_id']." AND sales_order_detail_id = ".$this->data['sales_order_detail_id'].";");
                // Convert to REST
                $restCode[$r]['dbtodo'] = 'stock_orders';
                $restCode[$r]['actodo'] = 'dt';
                $restCode[$r]['con']    = "sales_order_detail_id = ".$salesOrderDetail['SalesOrderDetail']['sys_code'];
                $r++;
                for($i = 0; $i < sizeof($_POST['qty_pick']); $i++){
                    // Update Inventory Location Group
                    $dataGroup = array();
                    $dataGroup['module_type']       = 10;
                    $dataGroup['sales_order_id']    = $salesOrder['SalesOrder']['id'];
                    $dataGroup['product_id']        = $this->data['product_id'];
                    $dataGroup['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                    $dataGroup['date']         = $salesOrder['SalesOrder']['order_date'];
                    $dataGroup['total_qty']    = $_POST['qty_pick'][$i];
                    $dataGroup['total_order']  = $_POST['qty_pick'][$i];
                    $dataGroup['total_free']   = 0;
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataGroup);
                    // Convert to REST
                    $restCode[$r]['module_type']       = 10;
                    $restCode[$r]['sales_order_id']    = $this->Helper->getSQLSyncCode("sales_orders", $salesOrder['SalesOrder']['id']);
                    $restCode[$r]['date']              = $salesOrder['SalesOrder']['order_date'];
                    $restCode[$r]['total_qty']         = $_POST['qty_pick'][$i];
                    $restCode[$r]['total_order']       = $_POST['qty_pick'][$i];
                    $restCode[$r]['total_free']        = 0;
                    $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $this->data['product_id']);
                    $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $salesOrder['SalesOrder']['location_group_id']);
                    $restCode[$r]['dbtype']  = 'GroupDetail';
                    $restCode[$r]['actodo']  = 'inv';
                    $r++;
                            
                    // Update Inventory (Sales Invoice)
                    $data = array();
                    $data['module_type']       = 10;
                    $data['sales_order_id']    = $salesOrder['SalesOrder']['id'];
                    $data['product_id']        = $this->data['product_id'];
                    $data['location_id']       = $_POST['location_id'][$i];
                    $data['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                    $data['lots_number']  = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $data['expired_date'] = $_POST['expired_date'][$i]!=''?$_POST['expired_date'][$i]:'0000-00-00';
                    $data['date']         = $salesOrder['SalesOrder']['order_date'];
                    $data['total_qty']    = $_POST['qty_pick'][$i];
                    $data['total_order']  = $_POST['qty_pick'][$i];
                    $data['total_free']   = 0;
                    $data['user_id']      = $user['User']['id'];
                    $data['customer_id']  = $salesOrder['SalesOrder']['customer_id'];
                    $data['vendor_id']    = "";
                    $data['unit_cost']    = 0;
                    $data['unit_price']   = 0;
                    // Update Invetory Location
                    $this->Inventory->saveInventory($data);
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($data, 'inventories');
                    $restCode[$r]['module_type']  = 10;
                    $restCode[$r]['total_qty']    = $_POST['qty_pick'][$i];
                    $restCode[$r]['total_order']  = $_POST['qty_pick'][$i];
                    $restCode[$r]['total_free']   = 0;
                    $restCode[$r]['expired_date'] = $data['expired_date'];
                    $restCode[$r]['vendor_id']    = "";
                    $restCode[$r]['unit_cost']    = 0;
                    $restCode[$r]['unit_price']   = 0;
                    $restCode[$r]['customer_id']  = $this->Helper->getSQLSyncCode("customers", $salesOrder['SalesOrder']['customer_id']);
                    $restCode[$r]['sales_order_id']    = $this->Helper->getSQLSyncCode("sales_orders", $salesOrder['SalesOrder']['id']);
                    $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $this->data['product_id']);
                    $restCode[$r]['location_id']       = $this->Helper->getSQLSyncCode("locations", $_POST['location_id'][$i]);
                    $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $salesOrder['SalesOrder']['location_group_id']);
                    $restCode[$r]['user_id']           = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
                    $restCode[$r]['dbtype']  = 'saveInv';
                    $restCode[$r]['actodo']  = 'inv';
                    $r++;
                    // Update Inventory Valuation
                    if($this->data['calculate_cogs'] == 2){
                        $sqlCogs = mysql_query("SELECT * FROM inventory_unit_costs WHERE product_id = ".$this->data['product_id']." AND unit_cost > 0 AND total_qty > 0 ORDER BY id ASC");
                        if(mysql_num_rows($sqlCogs)){
                            $totalOrder = $_POST['qty_pick'][$i];
                            $cogs = 0;
                            while($rowCogs = mysql_fetch_array($sqlCogs)){
                                if($totalOrder > 0 && $rowCogs['total_qty'] > 0){
                                    if($rowCogs['total_qty'] >= $totalOrder){
                                        $cogs += ($rowCogs['unit_cost'] / $rowPro[0]) * $totalOrder;
                                        mysql_query("UPDATE inventory_unit_costs SET total_qty = (total_qty - ".$totalOrder.") WHERE id =".$rowCogs['id']);
                                        $totalOrder = 0;
                                    }else if($rowCogs['total_qty'] < $totalOrder){
                                        $cogs += ($rowCogs['unit_cost'] / $rowPro[0]) * $rowCogs['total_qty'];
                                        mysql_query("UPDATE inventory_unit_costs SET total_qty = (total_qty - ".$totalOrder.") WHERE id =".$rowCogs['id']);
                                        $totalOrder = $totalOrder - $rowCogs['total_qty'];
                                    }
                                }else{
                                    break;
                                }
                            }
                            mysql_query("UPDATE general_ledger_details SET debit = ".$cogs." WHERE inventory_valuation_is_debit = 1 AND inventory_valuation_id = ".$this->data['sales_order_detail_id']);
                            mysql_query("UPDATE general_ledger_details SET credit = ".$cogs." WHERE inventory_valuation_is_debit = 0 AND inventory_valuation_id = ".$this->data['sales_order_detail_id']);
                        }else{
                            $cogs = $this->data['product_cost'];
                            mysql_query("UPDATE general_ledger_details SET debit = ".$cogs." WHERE inventory_valuation_is_debit = 1 AND inventory_valuation_id = ".$this->data['sales_order_detail_id']);
                            mysql_query("UPDATE general_ledger_details SET credit = ".$cogs." WHERE inventory_valuation_is_debit = 0 AND inventory_valuation_id = ".$this->data['sales_order_detail_id']);
                        }
                    }
                        
                    //Insert Into Delivery Detail
                    $deliveyDetail = array();
                    $this->DeliveryDetail->create();
                    $deliveyDetail['DeliveryDetail']['sales_order_id'] = $salesOrder['SalesOrder']['id'];
                    $deliveyDetail['DeliveryDetail']['sales_order_detail_id'] = $this->data['sales_order_detail_id'];
                    $deliveyDetail['DeliveryDetail']['product_id']    = $this->data['product_id'];
                    $deliveyDetail['DeliveryDetail']['location_id']   = $_POST['location_id'][$i];
                    $deliveyDetail['DeliveryDetail']['lots_number']   = $_POST['lots_number'][$i]!=''?$_POST['lots_number'][$i]:0;
                    $deliveyDetail['DeliveryDetail']['expired_date']  = $_POST['expired_date'][$i]!=''?$_POST['expired_date'][$i]:'0000-00-00';
                    $deliveyDetail['DeliveryDetail']['total_qty']     = $_POST['qty_pick'][$i];
                    $this->DeliveryDetail->save($deliveyDetail);
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($deliveyDetail['DeliveryDetail'], 'delivery_details');
                    $restCode[$r]['dbtodo']   = 'delivery_details';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                }
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Product Pick', $this->data['sales_order_id']);
                $invalid['success'] = 1;
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery', 'Save Product Pick (Error Ready)', $this->data['sales_order_id']);
                $invalid['ready'] = 1;
            }
            echo json_encode($invalid);
            exit();
        }
    }
}

?>

