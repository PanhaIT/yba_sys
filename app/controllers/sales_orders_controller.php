<?php
class SalesOrdersController extends AppController {

    var $name = 'SalesOrders';
    var $components = array('Helper', 'Inventory');
    
    function viewByUser() {
        $this->layout = 'ajax';
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('user',$user);
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Dashboard');
    }

    function ajax($branch = 'all', $customer = 'all', $filterStatus = 'all', $balance = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('branch', 'customer', 'filterStatus', 'balance', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->SalesOrder->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'View', $id);
                $salesOrderDetails  = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all", array('conditions' => array('SalesOrderReceipt.sales_order_id' => $id, 'SalesOrderReceipt.is_void' => 0)));
                $cmWsales = ClassRegistry::init('CreditMemoWithSale')->find("all", array('conditions' => array('CreditMemoWithSale.sales_order_id' => $id, 'CreditMemoWithSale.status>0')));
                $this->set(compact('salesOrderDetails', 'salesOrderServices', 'salesOrderReceipts', 'cmWsales'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            $checkErrorStock = 1;
            $listOutStock = "";
            // Check Qty in Stock Before Save
            $allowStockMinus = false;
            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($this->data['SalesOrder']['location_group_id']);
            if($warehouseOption['LocationGroup']['allow_negative_stock'] == 1){
                $allowStockMinus = true;
            }
            // Check Warehouse Option Allow Negative
            if($allowStockMinus == false){ // Not Allow Negative Stock Check Stock On Hand
                $productOrder = array();
                // Group Product
                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if($_POST['product_id'][$i] != ""){
                        $keyIndex = $_POST['product_id'][$i]."+".$_POST['lots_number'][$i]."+".$_POST['expired_date'][$i];
                        if (array_key_exists($keyIndex, $productOrder)){
                            $productOrder[$keyIndex]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        } else {
                            $productOrder[$keyIndex]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        }
                    }
                }
                foreach($productOrder AS $key => $order){
                    $check        = explode("+", $key);
                    $productId    = $check[0];
                    $lotsNumber   = '0';
                    $expiredDate  = '0000-00-00';
                    if($check[1] != '' && $check[1] != '0'){
                        $lotsNumber = $check[1];
                    }
                    if($check[2] != '' && $check[2] != '0000-00-00'){
                        $expiredDate = $check[2];
                    }
                    // Get Total Qty In Stock
                    $totalStockAv = 0;
                    $sqlInv = mysql_query("SELECT SUM(total_qty - total_order) FROM `".$this->data['SalesOrder']['location_group_id']."_group_totals` WHERE product_id = ".$productId." AND lots_number = '".$lotsNumber."' AND expired_date = '".$expiredDate."' AND location_group_id = " . $this->data['SalesOrder']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$this->data['SalesOrder']['location_group_id']." AND is_active = 1)");
                    while($rInv = mysql_fetch_array($sqlInv)){
                        $totalStockAv = $rInv[0];
                    }
                    // Compare QTY
                    $totalSale       = $totalStockAv;
                    $qtyOrder        = $order['qty'];
                    $qtyStockCompare = $totalSale;
                    if($qtyOrder > $qtyStockCompare){
                        $checkErrorStock = 2; 
                        $listOutStock .= $key."|".$totalSale."-";
                    }
                }
            }
            if($checkErrorStock == 1){
                $allowDelivery = false;
                $checkDelivery = mysql_query("SELECT is_checked FROM s_module_detail_settings WHERE id = 20");
                $rowDelivery   = mysql_fetch_array($checkDelivery);
                if($rowDelivery['is_checked'] == 1){
                    $allowDelivery = true;
                }
                $result = array();
                $totalAmount = $this->data['SalesOrder']['total_amount'] + $this->data['SalesOrder']['total_vat'] - $this->data['SalesOrder']['discount_us'];
                if($totalAmount < $this->data['SalesOrder']['total_deposit']){
                    $this->data['SalesOrder']['total_deposit'] = $totalAmount;
                }
                $dateNow  = date("Y-m-d H:i:s");
                // Load Model
                $this->loadModel('SalesOrderDetail');
                $this->loadModel('StockOrder');
                $this->loadModel('SalesOrderService');
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('InventoryValuation');
                $this->loadModel('AccountType');
                $this->loadModel('Company');
                $this->loadModel('Delivery');
                $this->loadModel('DeliveryDetail');
                $this->loadModel('Transaction');
                $this->loadModel('TransactionDetail');
                
                if($this->data['SalesOrder']['preview_id'] != ''){
                    $sales_order = $this->SalesOrder->read(null, $this->data['SalesOrder']['preview_id']);
                    // Check Save Transaction
                    $checkTransaction = true;
                    $transactionLogId = 0;
                    $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Invoice' AND action = 1 AND module_id = ".$this->data['SalesOrder']['preview_id']);
                    if(mysql_num_rows($sqlCheck)){
                        $rowCheck  = mysql_fetch_array($sqlCheck);
                        $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
                        $rowDetail = mysql_num_rows($sqlDetail);
                        $transactionLogId = $rowCheck['id'];
                        if($rowDetail > 0){
                            // Check Total Transaction
                            $totalD = $rowCheck['products'] + $rowCheck['service'];
                            if($totalD != $rowDetail){
                                $checkTransaction = false;
                            } else {
                                $totalAcctD = 0;
                                while($rowD = mysql_fetch_array($sqlDetail)){
                                    $totalAcctD += $rowD['save_acct'];
                                    if($rowD['type'] == 1){
                                        if($rowD['inventory_valutaion'] != '1'){
                                            $checkTransaction = false;
                                            break;
                                        }
                                        if($sales_order['SalesOrder']['status'] == 2){
                                            if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                                $checkTransaction = false;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if($checkTransaction == true){
                                    // Check Account
                                    $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE sales_order_id = ".$this->data['SalesOrder']['preview_id']." AND sales_order_receipt_id IS NULL LIMIT 1)");
                                    if(mysql_num_rows($sqlAcct)){
                                        $rowAcct = mysql_fetch_array($sqlAcct);
                                        if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
                                            $checkTransaction = false;
                                        }
                                    } else {
                                        $checkTransaction = false;
                                    }
                                }
                            }
                        } else {
                            $checkTransaction = false;
                        }
                    }
//                    if($checkTransaction == true){
//                        // Remove Transaction Log
//                        mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
//                        mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                        // Update
                        $this->SalesOrder->updateAll(
                                array('SalesOrder.status' => -1, 'SalesOrder.modified_by' => $user['User']['id']),
                                array('SalesOrder.id' => $this->data['SalesOrder']['preview_id'])
                        );
                        $this->GeneralLedger->updateAll(
                                array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                                array('GeneralLedger.sales_order_id' => $this->data['SalesOrder']['preview_id'])
                        );

                        $this->InventoryValuation->updateAll(
                                array('InventoryValuation.is_active' => 2),
                                array('InventoryValuation.sales_order_id' => $this->data['SalesOrder']['preview_id'])
                        );
                        // Delete All Unearned Schedule
                        mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$this->data['SalesOrder']['preview_id']." AND type = 1");
                        mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$this->data['SalesOrder']['preview_id']." AND type = 1");
                        if ($sales_order['SalesOrder']['status'] == 2) {
                            // Delivery
                            $this->Delivery->updateAll(
                                    array('Delivery.status' => 0),
                                    array('Delivery.id' => $sales_order['SalesOrder']['delivery_id'])
                            );

                            $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $this->data['SalesOrder']['preview_id'])));
                            foreach($salesOrderDetails AS $salesOrderDetail){
                                $totalOrder = ($salesOrderDetail['SalesOrderDetail']['qty'] + $salesOrderDetail['SalesOrderDetail']['qty_free']) * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                $qtyOrder   = $salesOrderDetail['SalesOrderDetail']['qty'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                $qtyFree    = $salesOrderDetail['SalesOrderDetail']['qty_free'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                // Update Inventory Location Group
                                $dataGroup = array();
                                $dataGroup['module_type']       = 21;
                                $dataGroup['sales_order_id']    = $sales_order['SalesOrder']['id'];
                                $dataGroup['product_id']        = $salesOrderDetail['SalesOrderDetail']['product_id'];
                                $dataGroup['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                                $dataGroup['date']         = $sales_order['SalesOrder']['order_date'];
                                $dataGroup['total_qty']    = $totalOrder;
                                $dataGroup['total_order']  = $qtyOrder;
                                $dataGroup['total_free']   = $qtyFree;
                                $dataGroup['transaction_id'] = '';
                                // Update Inventory Group
                                $this->Inventory->saveGroupTotalDetail($dataGroup);
                                $sqlDn = mysql_query("SELECT * FROM delivery_details WHERE sales_order_detail_id = ".$salesOrderDetail['SalesOrderDetail']['id']);
                                while($rowDn = mysql_fetch_array($sqlDn)){
                                    // Update Inventory (Sales)
                                    $data = array();
                                    $data['module_type']       = 21;
                                    $data['sales_order_id']    = $sales_order['SalesOrder']['id'];
                                    $data['product_id']        = $rowDn['product_id'];
                                    $data['location_id']       = $rowDn['location_id'];
                                    $data['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                                    $data['lots_number']  = $rowDn['lots_number']!=''?$rowDn['lots_number']:0;
                                    $data['expired_date'] = $rowDn['expired_date']!='0000-00-00'?$rowDn['expired_date']:'0000-00-00';
                                    $data['date']         = $sales_order['SalesOrder']['order_date'];
                                    $data['total_qty']    = $rowDn['total_qty'];
                                    $data['total_order']  = $rowDn['total_qty'];
                                    $data['total_free']   = 0;
                                    $data['user_id']      = $user['User']['id'];
                                    $data['customer_id']  = $sales_order['SalesOrder']['customer_id'];
                                    $data['vendor_id']    = "";
                                    $data['unit_cost']    = 0;
                                    $data['unit_price']   = $salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                                    $data['transaction_id'] = '';
                                    // Update Invetory Location
                                    $this->Inventory->saveInventory($data);
                                }
                            }
                        } else {
                            // Reset Stock Order
                            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$this->data['SalesOrder']['preview_id'].";");
                            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                            }
                            // Detele Tmp Stock Order
                            mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$this->data['SalesOrder']['preview_id'].";");
                        }
//                    } else {
//                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New (Error Save Transaction)');
//                        $result['error'] = 1;
//                        echo json_encode($result);
//                        exit;
//                    }
                }

                // Chart Account
                $depositAccount   = $this->AccountType->findById(23);
                $salesDiscAccount = $this->AccountType->findById(11);
                $arAccount        = $this->AccountType->findById(7);
                $unearnedRevenueAccount  = $this->AccountType->findById(20);
                $unearnedDiscountAccount = $this->AccountType->findById(21);
                
                $this->SalesOrder->create();
                $salesOrder = array();
                $salesOrder['SalesOrder']['sys_code']          = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $salesOrder['SalesOrder']['so_code']           = $this->data['SalesOrder']['so_code'];
                $salesOrder['SalesOrder']['company_id']        = $this->data['SalesOrder']['company_id'];
                $salesOrder['SalesOrder']['branch_id']         = $this->data['SalesOrder']['branch_id'];
                $salesOrder['SalesOrder']['location_group_id'] = $this->data['SalesOrder']['location_group_id'];
                $salesOrder['SalesOrder']['customer_id']       = $this->data['SalesOrder']['customer_id'];
                $salesOrder['SalesOrder']['sales_rep_id']        = $this->data['SalesOrder']['sales_rep_id']!=""?$this->data['SalesOrder']['sales_rep_id']:0;
                $salesOrder['SalesOrder']['currency_id']  = $this->data['SalesOrder']['currency_id'];
                $salesOrder['SalesOrder']['ar_id']               = $arAccount['AccountType']['chart_account_id'];
                $salesOrder['SalesOrder']['payment_term_id']     = $this->data['SalesOrder']['payment_term_id'];
                $salesOrder['SalesOrder']['quotation_id']        = $this->data['SalesOrder']['quotation_id'];
                $salesOrder['SalesOrder']['quotation_number']    = $this->data['SalesOrder']['quotation_number'];
                $salesOrder['SalesOrder']['order_id']            = $this->data['SalesOrder']['order_id'];
                $salesOrder['SalesOrder']['order_number']        = $this->data['SalesOrder']['order_number'];
                $salesOrder['SalesOrder']['vat_chart_account_id'] = $this->data['SalesOrder']['vat_chart_account_id'];
                $salesOrder['SalesOrder']['vat_percent']      = $this->data['SalesOrder']['vat_percent'];
                $salesOrder['SalesOrder']['total_vat']        = $this->data['SalesOrder']['total_vat'];
                $salesOrder['SalesOrder']['vat_setting_id']   = $this->data['SalesOrder']['vat_setting_id'];
                $salesOrder['SalesOrder']['vat_calculate']    = $this->data['SalesOrder']['vat_calculate'];
                $salesOrder['SalesOrder']['total_amount']     = $this->data['SalesOrder']['total_amount'];
                $salesOrder['SalesOrder']['total_deposit']    = $this->data['SalesOrder']['total_deposit'];
                $salesOrder['SalesOrder']['discount']         = $this->data['SalesOrder']['discount_us'];
                $salesOrder['SalesOrder']['discount_percent'] = $this->data['SalesOrder']['discount_percent'];
                $salesOrder['SalesOrder']['balance']       = ($totalAmount - $this->data['SalesOrder']['total_deposit']);
                $salesOrder['SalesOrder']['order_date']    = $this->data['SalesOrder']['order_date'];
                $salesOrder['SalesOrder']['memo']          = $this->data['SalesOrder']['memo'];
                $salesOrder['SalesOrder']['price_type_id'] = $this->data['SalesOrder']['price_type_id'];
                $salesOrder['SalesOrder']['is_pos']     = 0;
                $salesOrder['SalesOrder']['is_approve'] = $this->data['SalesOrder']['is_approve'];
                $salesOrder['SalesOrder']['created']    = $dateNow;
                $salesOrder['SalesOrder']['created_by'] = $user['User']['id'];
                $salesOrder['SalesOrder']['is_deposit_reference'] = 0;
                // Check Approve
                if($this->data['SalesOrder']['is_approve'] == 1){
                    if($rowDelivery[0] == 1){
                        $salesOrder['SalesOrder']['status'] = 1;
                    } else {
                        $salesOrder['SalesOrder']['status'] = 2;
                    }
                }else{
                    $salesOrder['SalesOrder']['status'] = -2;
                }
                if ($this->SalesOrder->save($salesOrder)) {
                    $saleOrderId = $this->SalesOrder->id;
                    $company     = $this->Company->read(null, $this->data['SalesOrder']['company_id']);
                    $classId     = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['SalesOrder']['location_group_id']);
                    // Transaction 
                    $transactionAcct = 0;
                    $transactionPro  = 0;
                    $transactionSer  = 0;
                    $transaction = array();
                    $this->Transaction->create();
                    $transaction['Transaction']['module_id']  = $saleOrderId;
                    $transaction['Transaction']['type']       = 'Sales Invoice';
                    $transaction['Transaction']['created']    = $dateNow;
                    $transaction['Transaction']['created_by'] = $user['User']['id'];
                    $this->Transaction->save($transaction);
                    $transactionId = $this->Transaction->id;
                    // Check Delivery Option
                    if($allowDelivery == false){
                        // Delivery
                        $this->Delivery->create();
                        $this->data['Delivery']['sys_code']     = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->data['Delivery']['company_id']   = $salesOrder['SalesOrder']['company_id'];
                        $this->data['Delivery']['branch_id']    = $salesOrder['SalesOrder']['branch_id'];
                        $this->data['Delivery']['warehouse_id'] = $salesOrder['SalesOrder']['location_group_id'];
                        $this->data['Delivery']['date'] = $this->data['SalesOrder']['order_date'];
                        $this->data['Delivery']['code'] = NULL;
                        $this->data['Delivery']['created']    = $dateNow;
                        $this->data['Delivery']['created_by'] = $user['User']['id'];
                        $this->data['Delivery']['status']  = 2;
                        $this->Delivery->save($this->data);
                        $deliveryId = $this->Delivery->id;
                        // Update Code Delivery
                        $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $salesOrder['SalesOrder']['branch_id'])));
                        $dnCode     = date("y").$modComCode['ModuleCodeBranch']['dn_code'];
                        // Get Module DN Code
                        $modDnCode  = $this->Helper->getModuleCode($dnCode, $deliveryId, 'code', 'deliveries', 'status >= 0 AND branch_id = '.$salesOrder['SalesOrder']['branch_id']);
                        // Updaet Module Code to DN
                        mysql_query("UPDATE deliveries SET code = '".$modDnCode."' WHERE id = ".$deliveryId);
                    } else {
                        $deliveryId = 0;
                    }
                    
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['SalesOrder']['so_code'], $saleOrderId, 'so_code', 'sales_orders', 'status != -1 AND branch_id = '.$salesOrder['SalesOrder']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE sales_orders SET so_code = '".$modCode."', delivery_id = ".$deliveryId." WHERE id = ".$saleOrderId);
                    /* Create General Ledger */
                    $generalLedger = array();
                    $this->GeneralLedger->create();
                    $generalLedger['GeneralLedger']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['sales_order_id'] = $saleOrderId;
                    $generalLedger['GeneralLedger']['date']       = $this->data['SalesOrder']['order_date'];
                    $generalLedger['GeneralLedger']['reference']  = $modCode;
                    $generalLedger['GeneralLedger']['created']    = $dateNow;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    // Check Approve
                    if($this->data['SalesOrder']['is_approve'] == 1){
                        $generalLedger['GeneralLedger']['is_active'] = 1;
                    }else{
                        $generalLedger['GeneralLedger']['is_active'] = 2;
                    }
                    $this->GeneralLedger->save($generalLedger);
                    $generalLedgerId = $this->GeneralLedger->id;
                    /* General Ledger Detail (A/R) For save Finish */
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $salesOrder['SalesOrder']['ar_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $salesOrder['SalesOrder']['balance'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode;
                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                    /* General Ledger Detail Total Discount */
                    if ($this->data['SalesOrder']['discount_us'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['SalesOrder']['discount_us'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Total Discount';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }

                    /* General Ledger Detail Total VAT */
                    if (($salesOrder['SalesOrder']['total_vat']) > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['vat_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $salesOrder['SalesOrder']['total_vat'];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Total VAT';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }
                    
                    /* General Ledger Detail Total Deposit */
                    if ($this->data['SalesOrder']['total_deposit'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $depositAccount['AccountType']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['SalesOrder']['total_deposit'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Total Deposit';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }
                    
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            $tranDetailAcct = 0;
                            /* Sales Order Detail */
                            $salesOrderDetail = array();
                            $this->SalesOrderDetail->create();
                            $salesOrderDetail['SalesOrderDetail']['sys_code']         = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $salesOrderDetail['SalesOrderDetail']['sales_order_id']   = $saleOrderId;
                            $salesOrderDetail['SalesOrderDetail']['promotional_id']   = $_POST['promotional_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['discount_id']      = $_POST['discount_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['discount_amount']  = $_POST['discount'][$i];
                            $salesOrderDetail['SalesOrderDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                            $salesOrderDetail['SalesOrderDetail']['product_id']   = $_POST['product_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty']          = $_POST['qty'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty_free']     = $_POST['qty_free'][$i];
                            $salesOrderDetail['SalesOrderDetail']['unit_price']   = $_POST['unit_price'][$i];
                            $salesOrderDetail['SalesOrderDetail']['total_price']  = $_POST['total_price_bf_dis'][$i];
                            $salesOrderDetail['SalesOrderDetail']['conversion']   = $_POST['conversion'][$i];
                            $salesOrderDetail['SalesOrderDetail']['note']         = $_POST['note'][$i];
                            if($_POST['lots_number'][$i] != '' && $_POST['lots_number'][$i] != '0'){
                                $lotsNumber = $_POST['lots_number'][$i];
                            } else {
                                $lotsNumber = '0';
                            }
                            $salesOrderDetail['SalesOrderDetail']['lots_number']  = $lotsNumber;
                            if($_POST['expired_date'][$i] != '' && $_POST['expired_date'][$i] != '0000-00-00'){
                                $expiredDate = $_POST['expired_date'][$i];
                            } else {
                                $expiredDate = '0000-00-00';
                            }
                            $salesOrderDetail['SalesOrderDetail']['expired_date']  = $expiredDate;
                            $this->SalesOrderDetail->save($salesOrderDetail);
                            $salesOrderDetailId = $this->SalesOrderDetail->id;
                            $transactionPro++;
                            $qtyOrder      = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_uom_val'][$i] / $_POST['conversion'][$i]);
                            $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['conversion'][$i];
                            $priceSales    = $_POST['total_price_bf_dis'][$i] - $_POST['discount'][$i];
                            
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 1;
                            $tranDetail['TransactionDetail']['module_id']  = $salesOrderDetailId;
                            $this->TransactionDetail->save($tranDetail);
                            $tranDetailId = $this->TransactionDetail->id;
                            
                            $inv_valutaion = array();
                            $this->InventoryValuation->create();
                            $inv_valutaion['InventoryValuation']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                            $inv_valutaion['InventoryValuation']['sales_order_id'] = $saleOrderId;
                            $inv_valutaion['InventoryValuation']['company_id'] = $this->data['SalesOrder']['company_id'];
                            $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['SalesOrder']['branch_id'];
                            $inv_valutaion['InventoryValuation']['type'] = "Invoice";
                            $inv_valutaion['InventoryValuation']['reference']   = $modCode;
                            $inv_valutaion['InventoryValuation']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $inv_valutaion['InventoryValuation']['date'] = $this->data['SalesOrder']['order_date'];
                            $inv_valutaion['InventoryValuation']['pid']  = $_POST['product_id'][$i];
                            $inv_valutaion['InventoryValuation']['small_qty'] = "-" . $qtyOrderSmall;
                            $inv_valutaion['InventoryValuation']['qty'] = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                            $inv_valutaion['InventoryValuation']['cost'] = null;
                            $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                            $inv_valutaion['InventoryValuation']['created'] = $dateNow;
                            // Check Approve
                            if($this->data['SalesOrder']['is_approve'] == 1){
                                $inv_valutaion['InventoryValuation']['is_active'] = 1;
                            }else{
                                $inv_valutaion['InventoryValuation']['is_active'] = 2;
                            }
                            $this->InventoryValuation->saveAll($inv_valutaion);
                            $inv_valutation_id = $this->InventoryValuation->id;
                            
                            // Check Delivery Option
                            if($rowDelivery[0] == 0){
                                // Update Inventory Location Group
                                $dataGroup = array();
                                $dataGroup['module_type']       = 10;
                                $dataGroup['sales_order_id']    = $saleOrderId;
                                $dataGroup['product_id']        = $_POST['product_id'][$i];
                                $dataGroup['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                $dataGroup['date']         = $salesOrder['SalesOrder']['order_date'];
                                $dataGroup['total_qty']    = $qtyOrderSmall;
                                $dataGroup['total_order']  = ($_POST['qty'][$i] * $_POST['conversion'][$i]);
                                $dataGroup['total_free']   = ($_POST['qty_free'][$i] * $_POST['conversion'][$i]);
                                $dataGroup['transaction_id']  = $tranDetailId;
                                // Update Inventory Group
                                $this->Inventory->saveGroupTotalDetail($dataGroup);

                                $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." AND is_active = 1)";
                                $invInfos   = array();
                                $index      = 0;
                                $totalOrder = $qtyOrderSmall;
                                if($allowStockMinus == false){
                                    // Calculate Location, Lot, Expired Date
                                    $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$salesOrderDetail['SalesOrderDetail']['lots_number']."' AND group_totals.expired_date = '".$salesOrderDetail['SalesOrderDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                                    while($rowInventory = mysql_fetch_array($sqlInventory)){
                                        if($totalOrder > 0 && $rowInventory['total_qty'] > 0){
                                            if($rowInventory['total_qty'] >= $totalOrder){
                                                $invInfos[$index]['total_qty']    = $totalOrder;
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = 0;
                                                ++$index;
                                            }else if($rowInventory['total_qty'] < $totalOrder){
                                                $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = $totalOrder - $rowInventory['total_qty'];
                                                ++$index;
                                            }
                                        }else{
                                            break;
                                        }
                                    }
                                } else {
                                    $sqlLocation = mysql_query("SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." AND is_active = 1 ORDER BY id ASC LIMIT 1");
                                    $rowLocation = mysql_fetch_array($sqlLocation);
                                    $invInfos[$index]['total_qty']    = $totalOrder;
                                    $invInfos[$index]['location_id']  = $rowLocation['id'];
                                    $invInfos[$index]['lots_number']  = $salesOrderDetail['SalesOrderDetail']['lots_number'];
                                    $invInfos[$index]['expired_date'] = '0000-00-00';
                                }
                                // Update Inventory
                                foreach($invInfos AS $invInfo){
                                    // Update Inventory (Sales)
                                    $data = array();
                                    $data['module_type']       = 10;
                                    $data['sales_order_id']    = $saleOrderId;
                                    $data['product_id']        = $_POST['product_id'][$i];
                                    $data['location_id']       = $invInfo['location_id'];
                                    $data['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                    $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                                    $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                                    $data['date']         = $salesOrder['SalesOrder']['order_date'];
                                    $data['total_qty']    = $invInfo['total_qty'];
                                    $data['total_order']  = $invInfo['total_qty'];
                                    $data['total_free']   = 0;
                                    $data['user_id']      = $user['User']['id'];
                                    $data['customer_id']  = $salesOrder['SalesOrder']['customer_id'];
                                    $data['vendor_id']    = "";
                                    $data['unit_cost']    = 0;
                                    $data['unit_price']   = $priceSales;
                                    $data['transaction_id'] = $tranDetailId;
                                    // Update Invetory Location
                                    $this->Inventory->saveInventory($data);

                                    //Insert Into Delivery Detail
                                    $deliveyDetail = array();
                                    $this->DeliveryDetail->create();
                                    $deliveyDetail['DeliveryDetail']['delivery_id']    = $deliveryId;
                                    $deliveyDetail['DeliveryDetail']['sales_order_id'] = $saleOrderId;
                                    $deliveyDetail['DeliveryDetail']['sales_order_detail_id'] = $salesOrderDetailId;
                                    $deliveyDetail['DeliveryDetail']['product_id']    = $_POST['product_id'][$i];
                                    $deliveyDetail['DeliveryDetail']['location_id']   = $invInfo['location_id'];
                                    $deliveyDetail['DeliveryDetail']['lots_number']   = $data['lots_number'];
                                    $deliveyDetail['DeliveryDetail']['expired_date']  = $data['expired_date'];
                                    $deliveyDetail['DeliveryDetail']['total_qty']     = $invInfo['total_qty'];
                                    $this->DeliveryDetail->save($deliveyDetail);
                                }
                            } else {
                                $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id'].")";
                                $invInfos   = array();
                                $index      = 0;
                                $totalOrder = $qtyOrderSmall;
                                // Calculate Location, Lot, Expired Date
                                $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$salesOrderDetail['SalesOrderDetail']['lots_number']."' AND group_totals.expired_date = '".$salesOrderDetail['SalesOrderDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                                while($rowInventory = mysql_fetch_array($sqlInventory)){
                                    if($totalOrder > 0 && $rowInventory['total_qty'] > 0){
                                        if($rowInventory['total_qty'] >= $totalOrder){
                                            $invInfos[$index]['total_qty']    = $totalOrder;
                                            $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                            $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                            $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                            $totalOrder = 0;
                                            ++$index;
                                        }else if($rowInventory['total_qty'] < $totalOrder){
                                            $invInfos[$index]['total_qty']    = $rowInventory['total_qty'];
                                            $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                            $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                            $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                            $totalOrder = $totalOrder - $rowInventory['total_qty'];
                                            ++$index;
                                        }
                                    }else{
                                        break;
                                    }
                                }
                                // Update Inventory
                                foreach($invInfos AS $invInfo){
                                    //Insert Into Tmp Sales
                                    $tmpDelivey = array();
                                    $this->StockOrder->create();
                                    $tmpDelivey['StockOrder']['sales_order_id']        = $saleOrderId;
                                    $tmpDelivey['StockOrder']['sales_order_detail_id'] = $salesOrderDetailId;
                                    $tmpDelivey['StockOrder']['product_id']            = $_POST['product_id'][$i];
                                    $tmpDelivey['StockOrder']['location_group_id']   = $salesOrder['SalesOrder']['location_group_id'];
                                    $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                    $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                    $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                    $tmpDelivey['StockOrder']['date'] = $salesOrder['SalesOrder']['order_date'];
                                    $tmpDelivey['StockOrder']['qty']  = $invInfo['total_qty'];
                                    $this->StockOrder->save($tmpDelivey);
                                    $this->Inventory->saveGroupQtyOrder($salesOrder['SalesOrder']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $salesOrder['SalesOrder']['order_date'], '+');
                                }
                            }

                            /* General Ledger Detail (Product Income) */
                            $this->GeneralLedgerDetail->create();
                            $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                            $dataIncAccount  = mysql_fetch_array($queryIncAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            /* General Ledger Detail (Product Discount) */
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Product # ' . $_POST['product'][$i] . ' Discount';
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                            }

                            /* General Ledger Detail (Inventory) */
                            $this->GeneralLedgerDetail->create();
                            $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                            $dataInvAccount  = mysql_fetch_array($queryInvAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory for INV # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            
                            /* General Ledger Detail (COGS) */
                            $this->GeneralLedgerDetail->create();
                            $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                            $dataCogsAccount = mysql_fetch_array($queryCogsAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS for INV # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // Update Transaction Detail
                            mysql_query("UPDATE transaction_details SET save_acct = ".$tranDetailAcct." WHERE id = ".$tranDetailId);
                            if(!empty($_POST['order_detail_id'][$i])){
                                // Reset Stock Order
                                $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `order_detail_id`=".$_POST['order_detail_id'][$i].";");
                                while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                                    $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                                }
                                // Detele Tmp Stock Order
                                mysql_query("DELETE FROM `stock_orders` WHERE  `order_detail_id`=".$_POST['order_detail_id'][$i].";");
                            }
                        } else if (!empty($_POST['service_id'][$i])) {
                            $tranDetailAcct = 0;
                            $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                            $dataServiceAccount  = mysql_fetch_array($queryServiceAccount);
                            if($_POST['type'][$i] == 2){
                                $serviceAcct = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                $discAcct    = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                            } else {
                                $serviceAcct = $dataServiceAccount[0];
                                $discAcct    = $salesDiscAccount['AccountType']['chart_account_id'];
                            }
                            /* Sales Order Service */
                            $salesOrderService = array();
                            $this->SalesOrderService->create();
                            $salesOrderService['SalesOrderService']['sales_order_id']   = $saleOrderId;
                            $salesOrderService['SalesOrderService']['discount_id']      = $_POST['discount_id'][$i];
                            $salesOrderService['SalesOrderService']['discount_amount']  = $_POST['discount'][$i];
                            $salesOrderService['SalesOrderService']['discount_percent'] = $_POST['discount_percent'][$i];
                            $salesOrderService['SalesOrderService']['service_id']  = $_POST['service_id'][$i];
                            $salesOrderService['SalesOrderService']['qty']         = $_POST['qty'][$i];
                            $salesOrderService['SalesOrderService']['qty_free']    = $_POST['qty_free'][$i];
                            $salesOrderService['SalesOrderService']['unit_price']  = $_POST['unit_price'][$i];
                            $salesOrderService['SalesOrderService']['total_price'] = $_POST['total_price_bf_dis'][$i];
                            $salesOrderService['SalesOrderService']['note']        = $_POST['note'][$i];
                            $salesOrderService['SalesOrderService']['type']        = $_POST['type'][$i];
                            $salesOrderService['SalesOrderService']['chart_account_id'] = $dataServiceAccount[0];
                            if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00'){
                                $salesOrderService['SalesOrderService']['start'] = $_POST['start'][$i];
                            }
                            $this->SalesOrderService->save($salesOrderService);
                            $salesServiceId = $this->SalesOrderService->id;
                            $transactionSer++;
                            /* General Ledger Detail (Service) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $serviceAcct;
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Service # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            /* General Ledger Detail (Service Discount) */
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $discAcct;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' Service # ' . $_POST['product'][$i] . ' Discount';
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                            }
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 2;
                            $tranDetail['TransactionDetail']['module_id']  = $salesServiceId;
                            $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                            $this->TransactionDetail->save($tranDetail);
                            if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00' && $_POST['type'][$i] == 2 && $_POST['qty'][$i] > 0){
                                $this->loadModel('UnearnedSchedule');
                                $dateStart = $_POST['start'][$i];
                                for($k= 0; $k < $_POST['qty'][$i]; $k++){
                                    $start  = date('Y-m-d', strtotime("+".$k." months", strtotime($dateStart)));
                                    $unearnSchedule = array();
                                    $unearnSchedule['UnearnedSchedule']['module_id']   = $saleOrderId;
                                    $unearnSchedule['UnearnedSchedule']['module_detail_id'] = $salesServiceId;
                                    $unearnSchedule['UnearnedSchedule']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                    $unearnSchedule['UnearnedSchedule']['date']        = $start;
                                    $unearnSchedule['UnearnedSchedule']['created']     = date("Y-m-d H:i:s");
                                    $unearnSchedule['UnearnedSchedule']['created_by']  = $user['User']['id'];
                                    // Unearned Revenue
                                    $this->UnearnedSchedule->create();
                                    $unearnSchedule['UnearnedSchedule']['sys_code']  = md5(rand().date("Y-m-d H:i:s"));
                                    $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                    $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $dataServiceAccount[0];
                                    $unearnSchedule['UnearnedSchedule']['debit']      = $_POST['unit_price'][$i];
                                    $unearnSchedule['UnearnedSchedule']['credit']     = $_POST['unit_price'][$i];
                                    $this->UnearnedSchedule->save($unearnSchedule);
                                    // Check Discount
                                    if ($_POST['discount'][$i] > 0) {
                                        $unitDiscount = $_POST['discount'][$i] / $_POST['qty'][$i];
                                        // Unearned Discount
                                        $this->UnearnedSchedule->create();
                                        $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                        $unearnSchedule['UnearnedSchedule']['debit_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                        $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                                        $unearnSchedule['UnearnedSchedule']['debit']      = $unitDiscount;
                                        $unearnSchedule['UnearnedSchedule']['credit']     = $unitDiscount;
                                        $this->UnearnedSchedule->save($unearnSchedule);
                                    }
                                }
                            }
                        }
                    }
                    // Update Transaction Save
                    mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                    // Recalculate Average Cost
                    mysql_query("UPDATE tracks SET val='".$this->data['SalesOrder']['order_date']."', is_recalculate = 1 WHERE id=1");
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New', $saleOrderId);
                    $result['id']    = $saleOrderId;
                    $result['code']  = $modCode;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New (Error Out of Stock)');
                // Error Out of Stock
                $result['listOutStock'] = $listOutStock;
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Add New');
        $joinUsers = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
        $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id'));
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $paymentTerms   = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('PaymentTerm.is_active=1')));
        $this->set(compact("locationGroups", "paymentTerms", "companies", "branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('branches', 'uoms'));
    }

    function miscellaneous() {
        $this->layout = 'ajax';
    }

    function discount($companyId = null) {
        $this->layout = 'ajax';
        if (!$companyId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $discounts = ClassRegistry::init('Discount')->find("all", array('conditions' => array('Discount.is_active' => 1, 'Discount.company_id' => $companyId), 'order' => array('id DESC')));
        $this->set(compact('discounts'));
    }

    function product($companyId = null, $locationGroupId = null, $branchId = null, $saleOderId = null) {
        $this->layout = 'ajax';
        $this->set('orderDate', $_POST['order_date']);
        $this->set(compact('companyId', 'branchId', 'locationGroupId', 'saleOderId'));
    }

    function product_ajax($companyId, $locationGroupId, $branchId, $orderDate, $category = null) {
        $this->layout = 'ajax';
        $this->set('saleOderId', $_GET['sale_order_id']);
        $this->set(compact('companyId', 'branchId', 'locationGroupId', 'orderDate', 'category'));
    }

    function aging($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $salesOrder = $this->SalesOrder->findById($this->data['SalesOrder']['id']);
            $dateNow  = date("Y-m-d H:i:s");
            $cashBankAccount   = ClassRegistry::init('AccountType')->findById(6);
            $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
            $result = array();
            $sales  = array();
            $sales['SalesOrder']['id'] = $this->data['SalesOrder']['id'];
            $sales['SalesOrder']['modified']    = $dateNow;
            $sales['SalesOrder']['modified_by'] = $user['User']['id'];
            $sales['SalesOrder']['balance'] = $this->data['SalesOrder']['balance_us'];
            if ($this->SalesOrder->save($sales)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $salesOrder['SalesOrder']['branch_id'],
                                "ExchangeRate.currency_id" => $this->data['SalesOrder']['currency_id']), "order" => array("ExchangeRate.created desc")));
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['SalesOrder']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                    $totalDisOther  = ($this->data['SalesOrder']['discount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                    $totalDisOther  = 0;
                }
                
                $totalPaidDebit = $this->data['SalesOrder']['amount_us']   + $totalPaidOther ;
                $totalDis       = $this->data['SalesOrder']['discount_us'] + $totalDisOther;
                $totalPaid      = $this->data['SalesOrder']['amount_us']   + $totalPaidOther + $totalDis;

                if($totalPaid <= $salesOrder['SalesOrder']['balance']){
                    // Load Model
                    $this->loadModel('SalesOrderReceipt');
                    $this->loadModel('GeneralLedger');
                    $this->loadModel('GeneralLedgerDetail');
                    $this->loadModel('Company');
                    $this->loadModel('AccountType');
                    $this->loadModel('Transaction');
                    $transactionAcct = 0;
                    // Sales Order Receipt
                    $this->SalesOrderReceipt->create();
                    $salesOrderReceipt = array();
                    $salesOrderReceipt['SalesOrderReceipt']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $salesOrderReceipt['SalesOrderReceipt']['sales_order_id']     = $salesOrder['SalesOrder']['id'];
                    $salesOrderReceipt['SalesOrderReceipt']['branch_id']          = $salesOrder['SalesOrder']['branch_id'];
                    $salesOrderReceipt['SalesOrderReceipt']['exchange_rate_id']   = $exchangeRateId;
                    $salesOrderReceipt['SalesOrderReceipt']['currency_id'] = $this->data['SalesOrder']['currency_id'];
                    $salesOrderReceipt['SalesOrderReceipt']['chart_account_id']   = $cashBankAccountId;
                    $salesOrderReceipt['SalesOrderReceipt']['receipt_code']   = '';
                    $salesOrderReceipt['SalesOrderReceipt']['amount_us']      = $this->data['SalesOrder']['amount_us'];
                    $salesOrderReceipt['SalesOrderReceipt']['amount_other']   = $this->data['SalesOrder']['amount_other'];
                    $salesOrderReceipt['SalesOrderReceipt']['discount_us']    = $this->data['SalesOrder']['discount_us'];
                    $salesOrderReceipt['SalesOrderReceipt']['discount_other'] = $this->data['SalesOrder']['discount_other'];
                    $salesOrderReceipt['SalesOrderReceipt']['total_amount']   = $this->data['SalesOrder']['total_amount'];
                    $salesOrderReceipt['SalesOrderReceipt']['balance']        = $this->data['SalesOrder']['balance_us'];
                    $salesOrderReceipt['SalesOrderReceipt']['balance_other']  = $this->data['SalesOrder']['balance_other'];
                    $salesOrderReceipt['SalesOrderReceipt']['created']      = $dateNow;
                    $salesOrderReceipt['SalesOrderReceipt']['created_by']   = $user['User']['id'];
                    $salesOrderReceipt['SalesOrderReceipt']['pay_date']     = $this->data['SalesOrder']['pay_date']!=''?$this->data['SalesOrder']['pay_date']:'0000-00-00';
                    // Get Total Paid
                    if ($this->data['SalesOrder']['balance_us'] > 0) {
                        $salesOrderReceipt['SalesOrderReceipt']['due_date'] = $this->data['SalesOrder']['aging']!=''?$this->data['SalesOrder']['aging']:'0000-00-00';
                    }
                 
                    $this->SalesOrderReceipt->save($salesOrderReceipt);
                    $result['sr_id'] = $this->SalesOrderReceipt->id;
                    
                    // Update Code & Change Receipt Generate Code
                    $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $salesOrder['SalesOrder']['branch_id'])));
                    $repCode    = date("y").$modComCode['ModuleCodeBranch']['inv_rep_code'];
                    // Get Module Code
                    $modCode    = $this->Helper->getModuleCode($repCode, $result['sr_id'], 'receipt_code', 'sales_order_receipts', 'is_void = 0 AND branch_id = '.$salesOrder['SalesOrder']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE sales_order_receipts SET receipt_code = '".$modCode."' WHERE id = ".$result['sr_id']);
                    $company  = $this->Company->read(null, $salesOrder['SalesOrder']['location_group_id']);
                    $classId  = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $salesOrder['SalesOrder']['location_group_id']);

                    // Save General Ledger Detail
                    $this->GeneralLedger->create();
                    $generalLedger = array();
                    $generalLedger['GeneralLedger']['sys_code']               = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['sales_order_id']         = $salesOrder['SalesOrder']['id'];
                    $generalLedger['GeneralLedger']['sales_order_receipt_id'] = $result['sr_id'];
                    $generalLedger['GeneralLedger']['date']       = $this->data['SalesOrder']['pay_date']!=''?$this->data['SalesOrder']['pay_date']:'0000-00-00';
                    $generalLedger['GeneralLedger']['reference']  = $modCode;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    $generalLedger['GeneralLedger']['created'] = $dateNow;
                    if ($this->GeneralLedger->save($generalLedger)) {
                        $glId = $this->GeneralLedger->id;
                        // Chart Account Payment
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashBankAccountId;
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalPaidDebit;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $salesOrder['SalesOrder']['so_code'];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                        // A/P
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['ar_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                        /* General Ledger Detail Total Discount */
                        if ($totalDis > 0) {
                            //Chart Account Discount
                            $paidDiscAccount = $this->AccountType->findById(11);
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $paidDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalDis;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $salesOrder['SalesOrder']['so_code'] . ' Total Discount';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                    }
                    // Transaction
                    $transaction = array();
                    $this->Transaction->create();
                    $transaction['Transaction']['module_id']  = $result['sr_id'];
                    $transaction['Transaction']['type']       = 'Sales Invoice Receipt';
                    $transaction['Transaction']['save_acct']  = $transactionAcct;
                    $transaction['Transaction']['created']    = $dateNow;
                    $transaction['Transaction']['created_by'] = $user['User']['id'];
                    $this->Transaction->save($transaction);
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Save Add New', $result['sr_id']);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Save Add New (Error)');
                    $result['sr_id'] = 0;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Payment', 'Save Add New (Error)');
                $result['sr_id'] = 0;
                echo json_encode($result);
                exit;
            }
        }
        if (!empty($id)) {
            $this->data = $this->SalesOrder->read(null, $id);
            $salesOrder = ClassRegistry::init('SalesOrder')->find("first",
                            array('conditions' => array('SalesOrder.id' => $id)));
            if (!empty($salesOrder)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Add New', $id);
                $salesOrderDetails  = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all", array('conditions' => array('SalesOrderReceipt.sales_order_id' => $id, 'SalesOrderReceipt.is_void' => 0)));
                $salesOrderWithCms  = ClassRegistry::init('CreditMemoWithSale')->find("all", array('conditions' => array('CreditMemoWithSale.sales_order_id' => $id, 'CreditMemoWithSale.status' => 1)));
                $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderReceipts', 'salesOrderServices', 'salesOrderWithCms'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function customer($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $saleId = $_POST['sale_id'];
            $this->set("saleId", $saleId);
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function customer_ajax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $saleId = $_GET['sale_id'];
            $this->set("saleId", $saleId);
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }
    
    function employee($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function employeeAjax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }

    function service($companyId, $branchId) {
        $this->layout = 'ajax';
        $serviceP = array();
        $sqlSG = mysql_query("SELECT section_id FROM services WHERE is_active = 1 GROUP BY section_id;");
        while($rowSG = mysql_fetch_array($sqlSG)){
            $serviceP[] = $rowSG['section_id'];
        }
        $conSection = '';
        if(!empty($serviceP)){
            $conSection = ' AND Pgroup.id IN ('.implode(",",$serviceP).')';
        }
        $sections = ClassRegistry::init('Pgroup')->find("list", array("conditions" => array("Pgroup.is_active = 1".$conSection)));
        $services = $this->serviceCombo($companyId, $branchId);
        $this->set(compact('sections', 'services'));
    }

    function serviceCombo($companyId, $branchId) {
        $array = array();
        $services = ClassRegistry::init('Service')->find("all", array("conditions" => array("Service.company_id=" . $companyId. " AND Service.is_active = 1", "Service.id IN (SELECT service_id FROM service_branches WHERE branch_id = ".$branchId.")")));
        foreach ($services as $service) {
            $uomId = $service['Service']['uom_id']!=''?$service['Service']['uom_id']:'';
            array_push($array, array('value' => $service['Service']['id'], 'name' => $service['Service']['code']." - ".$service['Service']['name'], 'class' => $service['Pgroup']['id'], 'abbr' => $service['Service']['name'], 'price' => $service['Service']['unit_price'], 'scode' => $service['Service']['code'], 'suom' => $uomId, 'type' => $service['Service']['type']));
        }
        return $array;
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $sales_order = $this->SalesOrder->read(null, $id);
        if($sales_order['SalesOrder']['status'] > 0){
            $queryHasReceipt    = mysql_query("SELECT id FROM sales_order_receipts WHERE sales_order_id=" . $id . " AND is_void = 0");
            $queryHasReturn     = mysql_query("SELECT id FROM credit_memos WHERE status > 0 AND sales_order_id=" . $id);
            $queryHasSalesOrder = mysql_query("SELECT id FROM credit_memo_with_sales WHERE status > 0 AND sales_order_id=" . $id);
            if((@mysql_num_rows($queryHasReturn) || @mysql_num_rows($queryHasSalesOrder) || @mysql_num_rows($queryHasReceipt))){
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void (Error has transaction with other modules)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
            $this->loadModel('InventoryValuation');
            $this->loadModel('GeneralLedger');
            $this->loadModel('Transaction');
            // Check Save Transaction
//            $checkTransaction = true;
//            $transactionLogId = 0;
//            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Invoice' AND action = 1 AND module_id = ".$id);
//            if(mysql_num_rows($sqlCheck)){
//                $rowCheck  = mysql_fetch_array($sqlCheck);
//                $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
//                $rowDetail = mysql_num_rows($sqlDetail);
//                $transactionLogId = $rowCheck['id'];
//                if($rowDetail > 0){
//                    // Check Total Transaction
//                    $totalD = $rowCheck['products'] + $rowCheck['service'];
//                    if($totalD != $rowDetail){
//                        $checkTransaction = false;
//                    } else {
//                        $totalAcctD = 0;
//                        while($rowD = mysql_fetch_array($sqlDetail)){
//                            $totalAcctD += $rowD['save_acct'];
//                            if($rowD['type'] == 1){
//                                if($rowD['inventory_valutaion'] != '1'){
//                                    $checkTransaction = false;
//                                    break;
//                                }
//                                if($sales_order['SalesOrder']['status'] == 2){
//                                    if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
//                                        $checkTransaction = false;
//                                        break;
//                                    }
//                                }
//                            }
//                        }
//                        if($checkTransaction == true){
//                            // Check Account
//                            $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE sales_order_id = ".$id." AND sales_order_receipt_id IS NULL LIMIT 1)");
//                            if(mysql_num_rows($sqlAcct)){
//                                $rowAcct = mysql_fetch_array($sqlAcct);
//                                if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
//                                    $checkTransaction = false;
//                                }
//                            } else {
//                                $checkTransaction = false;
//                            }
//                        }
//                    }
//                } else {
//                    $checkTransaction = false;
//                }
//            }
//            if($checkTransaction == false){
//                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void (Error Save Transaction)', $id);
//                echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
//                exit;
//            }
//            // Remove Transaction Log
//            if($transactionLogId > 0){
//                mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
//                mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
//            }
            $this->SalesOrder->updateAll(
                    array('SalesOrder.status' => 0, 'SalesOrder.modified_by' => $user['User']['id']),
                    array('SalesOrder.id' => $id)
            );

            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                    array('GeneralLedger.sales_order_id' => $id)
            );
            $this->InventoryValuation->updateAll(
                    array('InventoryValuation.is_active' => 2),
                    array('InventoryValuation.sales_order_id' => $id)
            );
            // Delete All Unearned Schedule
            mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$id." AND type = 1");
            mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$id." AND type = 1");
            $dateNow  = date("Y-m-d H:i:s");
//            $this->Transaction->create();
//            $transaction = array();
//            $transaction['Transaction']['module_id']  = $id;
//            $transaction['Transaction']['type']       = 'Sales Invoice';
//            $transaction['Transaction']['action']     = 2;
//            $transaction['Transaction']['created']    = $dateNow;
//            $transaction['Transaction']['created_by'] = $user['User']['id'];
//            $this->Transaction->save($transaction);
            if($sales_order['SalesOrder']['status'] == 2){
                // Delivery
                $this->loadModel('Delivery');
                $this->Delivery->updateAll(
                        array('Delivery.status' => 0),
                        array('Delivery.id' => $sales_order['SalesOrder']['delivery_id'])
                );
                        
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                foreach($salesOrderDetails AS $salesOrderDetail){
                    $totalOrder = ($salesOrderDetail['SalesOrderDetail']['qty'] + $salesOrderDetail['SalesOrderDetail']['qty_free']) * $salesOrderDetail['SalesOrderDetail']['conversion'];
                    $qtyOrder   = $salesOrderDetail['SalesOrderDetail']['qty'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                    $qtyFree    = $salesOrderDetail['SalesOrderDetail']['qty_free'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                    // Update Inventory Location Group
                    $dataGroup = array();
                    $dataGroup['module_type']       = 21;
                    $dataGroup['sales_order_id']    = $sales_order['SalesOrder']['id'];
                    $dataGroup['product_id']        = $salesOrderDetail['SalesOrderDetail']['product_id'];
                    $dataGroup['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                    $dataGroup['date']         = $sales_order['SalesOrder']['order_date'];
                    $dataGroup['total_qty']    = $totalOrder;
                    $dataGroup['total_order']  = $qtyOrder;
                    $dataGroup['total_free']   = $qtyFree;
                    $dataGroup['transaction_id'] = '';
                    // Update Inventory Group
                    $this->Inventory->saveGroupTotalDetail($dataGroup);
                    $sqlDn = mysql_query("SELECT * FROM delivery_details WHERE sales_order_detail_id = ".$salesOrderDetail['SalesOrderDetail']['id']);
                    while($rowDn = mysql_fetch_array($sqlDn)){
                        // Update Inventory (Sales)
                        $data = array();
                        $data['module_type']       = 21;
                        $data['sales_order_id']    = $sales_order['SalesOrder']['id'];
                        $data['product_id']        = $rowDn['product_id'];
                        $data['location_id']       = $rowDn['location_id'];
                        $data['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                        $data['lots_number']  = $rowDn['lots_number']!=''?$rowDn['lots_number']:0;
                        $data['expired_date'] = $rowDn['expired_date']!='0000-00-00'?$rowDn['expired_date']:'0000-00-00';
                        $data['date']         = $sales_order['SalesOrder']['order_date'];
                        $data['total_qty']    = $rowDn['total_qty'];
                        $data['total_order']  = $rowDn['total_qty'];
                        $data['total_free']   = 0;
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = $sales_order['SalesOrder']['customer_id'];
                        $data['vendor_id']    = "";
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = $salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                        $data['transaction_id'] = '';
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                    }
                }
            } else {
                // Reset Stock Order
                $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$id.";");
                while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                    $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                }
                // Detele Tmp Stock Order
                mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$id.";");
            }
            // Recalculate Average Cost
            mysql_query("UPDATE tracks SET val='".$sales_order['SalesOrder']['order_date']."', is_recalculate = 1 WHERE id=1");
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void (Error Status)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function voidReceipt($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->loadModel('GeneralLedger');
        $this->loadModel('SalesOrderReceipt');
        $this->loadModel('Transaction');
        $receipt = ClassRegistry::init('SalesOrderReceipt')->find("first",
                        array('conditions' => array('SalesOrderReceipt.id' => $id, 'SalesOrderReceipt.is_void' => 0)));
        $user = $this->getCurrentUser();
        if(!empty($receipt) && @$receipt['SalesOrderReceipt']['is_void'] == 0){
            // Check Save Transaction
            $checkTransaction = true;
            $transactionLogId = 0;
            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Invoice Receipt' AND action = 1 AND module_id = ".$id);
            if(mysql_num_rows($sqlCheck)){
                $rowCheck = mysql_fetch_array($sqlCheck);
                $transactionLogId = $rowCheck['id'];
                // Check Account
                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE sales_order_receipt_id = ".$id." LIMIT 1)");
                if(mysql_num_rows($sqlAcct)){
                    $rowAcct = mysql_fetch_array($sqlAcct);
                    if($rowAcct[0] != $rowCheck['save_acct']){
                        $checkTransaction = false;
                    }
                } else {
                    $checkTransaction = false;
                }
            }
            if($checkTransaction == false){
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Void (Error Save Transaction)', $id);
                echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
                exit;
            }
            $this->SalesOrderReceipt->updateAll(
                    array('SalesOrderReceipt.is_void' => 1, 'SalesOrderReceipt.modified_by' => $user['User']['id']),
                    array('SalesOrderReceipt.id' => $id)
            );
            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $receipt['SalesOrderReceipt']['exchange_rate_id'])));
            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                $paidAmtOther = 0;
                $paidDisOther = 0;
                if($receipt['SalesOrderReceipt']['amount_other'] > 0){
                    $paidAmtOther = ($receipt['SalesOrderReceipt']['amount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell']);
                }
                if($receipt['SalesOrderReceipt']['discount_other'] > 0){
                    $paidDisOther = ($receipt['SalesOrderReceipt']['discount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell']);
                }
                $totalPaidOther =  + $paidAmtOther + $paidDisOther;
            } else {
                $totalPaidOther = 0;
            }
            $total_amount   = $receipt['SalesOrderReceipt']['amount_us'] + $receipt['SalesOrderReceipt']['discount_us'] + $totalPaidOther;
            mysql_query("UPDATE sales_orders SET balance = balance+" . $total_amount . " WHERE id=" . $receipt['SalesOrderReceipt']['sales_order_id']);
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                    array('GeneralLedger.sales_order_receipt_id' => $id)
            );
            // Transaction
            $dateNow  = date("Y-m-d H:i:s");
            $this->Transaction->create();
            $transaction = array();
            $transaction['Transaction']['module_id']  = $id;
            $transaction['Transaction']['type']       = 'Sales Invoice Receipt';
            $transaction['Transaction']['action']     = 2;
            $transaction['Transaction']['created']    = $dateNow;
            $transaction['Transaction']['created_by'] = $user['User']['id'];
            $this->Transaction->save($transaction);
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Void (Error)', $id);
            echo MESSAGE_DATA_INVALID;
        }
        exit;
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            $id = $this->data['id'];
            $checkErrorStock    = 1;
            $listOutStock       = "";
            $queryHasReceipt    = mysql_query("SELECT id FROM sales_order_receipts WHERE sales_order_id=" . $id . " AND is_void = 0");
            $queryHasReturn     = mysql_query("SELECT id FROM credit_memos WHERE status>0 AND sales_order_id=" . $id);
            $queryHasSalesOrder = mysql_query("SELECT id FROM credit_memo_with_sales WHERE status>0 AND sales_order_id=" . $id);
            if((@mysql_num_rows($queryHasReturn) || @mysql_num_rows($queryHasSalesOrder) || @mysql_num_rows($queryHasReceipt))){
                // Error Save
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error has transaction with other modules)', $id);
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
            $allowDelivery = false;
            $checkDelivery = mysql_query("SELECT is_checked FROM s_module_detail_settings WHERE id = 20");
            $rowDelivery   = mysql_fetch_array($checkDelivery);
            if($rowDelivery['is_checked'] == 1){
                $allowDelivery = true;
            }
            // Check Qty in Stock Before Save
            // Check Warehouse Option Allow Negative
            $allowStockMinus = false;
            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($this->data['SalesOrder']['location_group_id']);
            if($warehouseOption['LocationGroup']['allow_negative_stock'] == 1){
                $allowStockMinus = true;
            }
            if($allowStockMinus == false){ // Not Allow Negative Stock Check Stock On Hand
                // Group Product
                $productOrder = array();
                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if($_POST['product_id'][$i] != ""){
                        $keyIndex = $_POST['product_id'][$i]."+".$_POST['lots_number'][$i]."+".$_POST['expired_date'][$i];
                        if (array_key_exists($keyIndex, $productOrder)){
                            $productOrder[$keyIndex]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        } else {
                            $productOrder[$keyIndex]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                        }
                    }
                }
                foreach($productOrder AS $key => $order){
                    $check        = explode("+", $key);
                    $productId    = $check[0];
                    $lotsNumber   = '0';
                    $expiredDate  = '0000-00-00';
                    if($check[1] != '' && $check[1] != '0'){
                        $lotsNumber = $check[1];
                    }
                    if($check[2] != '' && $check[2] != '0000-00-00'){
                        $expiredDate = $check[2];
                    }
                    // Get Total Qty in Stock
                    $totalStockAv = 0;
                    $sqlInv = mysql_query("SELECT SUM(total_qty) FROM `".$this->data['SalesOrder']['location_group_id']."_group_totals` WHERE product_id = ".$productId." AND lots_number = '".$lotsNumber."' AND expired_date = '".$expiredDate."' AND location_id IN (SELECT id FROM locations WHERE location_group_id = " . $this->data['SalesOrder']['location_group_id']." AND is_active = 1)");
                    while($rInv=mysql_fetch_array($sqlInv)){
                        $totalStockAv = $rInv[0];
                    }
                    // Get Total For Detail
                    $totalDetail = 0;
                    $sqlDetail = mysql_query("SELECT SUM((qty+qty_free)*conversion) AS qty FROM sales_order_details WHERE sales_order_id = ".$id." AND product_id = ".$productId." AND lots_number = '".$lotsNumber."' AND expired_date = '".$expiredDate."';");
                    if(mysql_num_rows($sqlDetail)){
                        $rowDetail = mysql_fetch_array($sqlDetail);
                        $totalDetail = $rowDetail[0];
                    }
                    // Get Total Qty in Order
                    $totalStock = $totalStockAv + $totalDetail;
                    $qtyOrder   = $order['qty'];
                    if($qtyOrder > $totalStock){
                        $checkErrorStock = 2; 
                        $listOutStock .= $productId."|".$totalStock."-";
                    }
                }
            }
            if($checkErrorStock == 1){
                $sales_order = $this->SalesOrder->read(null, $id);
                if ($sales_order['SalesOrder']['status'] > 0) {
                    $dateNow  = date("Y-m-d H:i:s");
                    $result = array();
                    $totalAmount = $this->data['SalesOrder']['total_amount'] + $this->data['SalesOrder']['total_vat'] - $this->data['SalesOrder']['discount_us'];
                    $totalDept   = $this->data['SalesOrder']['total_deposit'];
                    if($totalDept > $totalAmount){
                        $totalDept = $totalAmount;
                    }
                    $statuEdit = "-1";
                    // Load Model
                    $this->loadModel('InventoryValuation');
                    $this->loadModel('StockOrder');
                    $this->loadModel('SalesOrderDetail');
                    $this->loadModel('SalesOrderService');
                    $this->loadModel('GeneralLedger');
                    $this->loadModel('GeneralLedgerDetail');
                    $this->loadModel('AccountType');
                    $this->loadModel('Company');
                    $this->loadModel('Delivery');
                    $this->loadModel('DeliveryDetail');
                    $this->loadModel('Transaction');
                    $this->loadModel('TransactionDetail');

                    // Chart Account
                    $depositAccount   = $this->AccountType->findById(23);
                    $salesDiscAccount = $this->AccountType->findById(11);
                    $arAccount        = $this->AccountType->findById(7);
                    $unearnedRevenueAccount  = $this->AccountType->findById(20);
                    $unearnedDiscountAccount = $this->AccountType->findById(21);
                    // Check Save Transaction
                    $checkTransaction = true;
                    $transactionLogId = 0;
//                    $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Invoice' AND action = 1 AND module_id = ".$id);
//                    if(mysql_num_rows($sqlCheck)){
//                        $rowCheck  = mysql_fetch_array($sqlCheck);
//                        $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
//                        $rowDetail = mysql_num_rows($sqlDetail);
//                        $transactionLogId = $rowCheck['id'];
//                        if($rowDetail > 0){
//                            // Check Total Transaction
//                            $totalD = $rowCheck['products'] + $rowCheck['service'];
//                            if($totalD != $rowDetail){
//                                $checkTransaction = false;
//                            } else {
//                                $totalAcctD = 0;
//                                while($rowD = mysql_fetch_array($sqlDetail)){
//                                    $totalAcctD += $rowD['save_acct'];
//                                    if($rowD['type'] == 1){
//                                        if($rowD['inventory_valutaion'] != '1'){
//                                            $checkTransaction = false;
//                                            break;
//                                        }
//                                        if($sales_order['SalesOrder']['status'] == 2){
//                                            if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
//                                                $checkTransaction = false;
//                                                break;
//                                            }
//                                        }
//                                    }
//                                }
//                                if($checkTransaction == true){
//                                    // Check Account
//                                    $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE sales_order_id = ".$id." AND sales_order_receipt_id IS NULL LIMIT 1)");
//                                    if(mysql_num_rows($sqlAcct)){
//                                        $rowAcct = mysql_fetch_array($sqlAcct);
//                                        if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
//                                            $checkTransaction = false;
//                                        }
//                                    } else {
//                                        $checkTransaction = false;
//                                    }
//                                }
//                            }
//                        } else {
//                            $checkTransaction = false;
//                        }
//                    }
                    if($checkTransaction == true){
                        // Remove Transaction Log
                        mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                        mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                        // Update Status Edit
                        $this->SalesOrder->updateAll(
                                array('SalesOrder.status' => $statuEdit, 'SalesOrder.modified_by' => $user['User']['id']),
                                array('SalesOrder.id' => $id)
                        );
                        $this->GeneralLedger->updateAll(
                                array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                                array('GeneralLedger.sales_order_id' => $id)
                        );

                        $this->InventoryValuation->updateAll(
                                array('InventoryValuation.is_active' => 2),
                                array('InventoryValuation.sales_order_id' => $id)
                        );
                        // Delete All Unearned Schedule
                        mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$id." AND type = 1");
                        mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$id." AND type = 1");
                        if ($sales_order['SalesOrder']['status'] == 2) {
                            // Delivery
                            $this->Delivery->updateAll(
                                    array('Delivery.status' => 0),
                                    array('Delivery.id' => $sales_order['SalesOrder']['delivery_id'])
                            );

                            $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                            foreach($salesOrderDetails AS $salesOrderDetail){
                                $totalOrder = ($salesOrderDetail['SalesOrderDetail']['qty'] + $salesOrderDetail['SalesOrderDetail']['qty_free']) * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                $qtyOrder   = $salesOrderDetail['SalesOrderDetail']['qty'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                $qtyFree    = $salesOrderDetail['SalesOrderDetail']['qty_free'] * $salesOrderDetail['SalesOrderDetail']['conversion'];
                                // Update Inventory Location Group
                                $dataGroup = array();
                                $dataGroup['module_type']       = 21;
                                $dataGroup['sales_order_id']    = $sales_order['SalesOrder']['id'];
                                $dataGroup['product_id']        = $salesOrderDetail['SalesOrderDetail']['product_id'];
                                $dataGroup['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                                $dataGroup['date']         = $sales_order['SalesOrder']['order_date'];
                                $dataGroup['total_qty']    = $totalOrder;
                                $dataGroup['total_order']  = $qtyOrder;
                                $dataGroup['total_free']   = $qtyFree;
                                $dataGroup['transaction_id'] = '';
                                // Update Inventory Group
                                $this->Inventory->saveGroupTotalDetail($dataGroup);
                                $sqlDn = mysql_query("SELECT * FROM delivery_details WHERE sales_order_detail_id = ".$salesOrderDetail['SalesOrderDetail']['id']);
                                while($rowDn = mysql_fetch_array($sqlDn)){
                                    // Update Inventory (Sales)
                                    $data = array();
                                    $data['module_type']       = 21;
                                    $data['sales_order_id']    = $sales_order['SalesOrder']['id'];
                                    $data['product_id']        = $rowDn['product_id'];
                                    $data['location_id']       = $rowDn['location_id'];
                                    $data['location_group_id'] = $sales_order['SalesOrder']['location_group_id'];
                                    $data['lots_number']  = $rowDn['lots_number']!=''?$rowDn['lots_number']:0;
                                    $data['expired_date'] = $rowDn['expired_date']!='0000-00-00'?$rowDn['expired_date']:'0000-00-00';
                                    $data['date']         = $sales_order['SalesOrder']['order_date'];
                                    $data['total_qty']    = $rowDn['total_qty'];
                                    $data['total_order']  = $rowDn['total_qty'];
                                    $data['total_free']   = 0;
                                    $data['user_id']      = $user['User']['id'];
                                    $data['customer_id']  = $sales_order['SalesOrder']['customer_id'];
                                    $data['vendor_id']    = "";
                                    $data['unit_cost']    = 0;
                                    $data['unit_price']   = $salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                                    $data['transaction_id'] = '';
                                    // Update Invetory Location
                                    $this->Inventory->saveInventory($data);
                                }
                            }
                        } else {
                            // Reset Stock Order
                            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$id.";");
                            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                            }
                            // Detele Tmp Stock Order
                            mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$id.";");
                        }
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error Save Transaction)');
                        $result['error'] = 3;
                        echo json_encode($result);
                        exit;
                    }
                    
                    $this->SalesOrder->create();
                    $salesOrder = array();
                    $salesOrder['SalesOrder']['sys_code']          = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $salesOrder['SalesOrder']['so_code']           = $sales_order['SalesOrder']['so_code'];
                    $salesOrder['SalesOrder']['company_id']        = $this->data['SalesOrder']['company_id'];
                    $salesOrder['SalesOrder']['branch_id']         = $this->data['SalesOrder']['branch_id'];
                    $salesOrder['SalesOrder']['location_group_id'] = $this->data['SalesOrder']['location_group_id'];
                    $salesOrder['SalesOrder']['customer_id']       = $this->data['SalesOrder']['customer_id'];
                    $salesOrder['SalesOrder']['sales_rep_id']        = $this->data['SalesOrder']['sales_rep_id']!=""?$this->data['SalesOrder']['sales_rep_id']:0;
                    $salesOrder['SalesOrder']['currency_id']  = $this->data['SalesOrder']['currency_id'];
                    $salesOrder['SalesOrder']['ar_id']               = $arAccount['AccountType']['chart_account_id'];
                    $salesOrder['SalesOrder']['payment_term_id']     = $this->data['SalesOrder']['payment_term_id'];
                    $salesOrder['SalesOrder']['quotation_id']        = $this->data['SalesOrder']['quotation_id'];
                    $salesOrder['SalesOrder']['quotation_number']    = $this->data['SalesOrder']['quotation_number'];
                    $salesOrder['SalesOrder']['order_id']            = $this->data['SalesOrder']['order_id'];
                    $salesOrder['SalesOrder']['order_number']        = $this->data['SalesOrder']['order_number'];
                    $salesOrder['SalesOrder']['vat_chart_account_id'] = $this->data['SalesOrder']['vat_chart_account_id'];
                    $salesOrder['SalesOrder']['vat_percent']      = $this->data['SalesOrder']['vat_percent'];
                    $salesOrder['SalesOrder']['total_vat']        = $this->data['SalesOrder']['total_vat'];
                    $salesOrder['SalesOrder']['vat_setting_id']   = $this->data['SalesOrder']['vat_setting_id'];
                    $salesOrder['SalesOrder']['vat_calculate']    = $this->data['SalesOrder']['vat_calculate'];
                    $salesOrder['SalesOrder']['total_amount']     = $this->data['SalesOrder']['total_amount'];
                    $salesOrder['SalesOrder']['discount']         = $this->data['SalesOrder']['discount_us'];
                    $salesOrder['SalesOrder']['discount_percent'] = $this->data['SalesOrder']['discount_percent'];
                    $salesOrder['SalesOrder']['balance']       = $totalAmount - $totalDept;
                    $salesOrder['SalesOrder']['total_deposit'] = $totalDept;
                    $salesOrder['SalesOrder']['order_date']    = $this->data['SalesOrder']['order_date'];
                    $salesOrder['SalesOrder']['memo']          = $this->data['SalesOrder']['memo'];
                    $salesOrder['SalesOrder']['price_type_id'] = $this->data['SalesOrder']['price_type_id'];
                    $salesOrder['SalesOrder']['is_pos']      = 0;
                    $salesOrder['SalesOrder']['is_approve']  = $this->data['SalesOrder']['is_approve'];
                    $salesOrder['SalesOrder']['edited']      = $dateNow;
                    $salesOrder['SalesOrder']['edited_by']   = $user['User']['id'];
                    $salesOrder['SalesOrder']['created']     = $sales_order['SalesOrder']['created'];
                    $salesOrder['SalesOrder']['created_by']  = $sales_order['SalesOrder']['created_by'];
                    $salesOrder['SalesOrder']['is_deposit_reference'] = 0;
                    // Check Approve
                    if($this->data['SalesOrder']['is_approve'] == 1){
                        if($rowDelivery[0] == 1){
                            $salesOrder['SalesOrder']['status'] = 1;
                        } else {
                            $salesOrder['SalesOrder']['status'] = 2;
                        }
                    }else{
                        $salesOrder['SalesOrder']['status'] = -2;
                    }
                    if ($this->SalesOrder->save($salesOrder)) {
                        $result['id'] = $saleOrderId = $this->SalesOrder->id;
                        $company      = $this->Company->read(null, $this->data['SalesOrder']['company_id']);
                        $classId      = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['SalesOrder']['location_group_id']);
                        $glReference  = $sales_order['SalesOrder']['so_code'];
                        // Transaction 
                        $transactionAcct = 0;
                        $transactionPro  = 0;
                        $transactionSer  = 0;
                        $transaction = array();
                        $this->Transaction->create();
                        $transaction['Transaction']['module_id']  = $saleOrderId;
                        $transaction['Transaction']['type']       = 'Sales Invoice';
                        $transaction['Transaction']['created']    = $dateNow;
                        $transaction['Transaction']['created_by'] = $user['User']['id'];
                        $this->Transaction->save($transaction);
                        $transactionId = $this->Transaction->id;
                        // Check Delivery Option
                        if($allowDelivery == false){
                            // Delivery
                            $this->Delivery->create();
                            $this->data['Delivery']['sys_code']     = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $this->data['Delivery']['company_id']   = $salesOrder['SalesOrder']['company_id'];
                            $this->data['Delivery']['branch_id']    = $salesOrder['SalesOrder']['branch_id'];
                            $this->data['Delivery']['warehouse_id'] = $salesOrder['SalesOrder']['location_group_id'];
                            $this->data['Delivery']['date'] = $salesOrder['SalesOrder']['order_date'];
                            $this->data['Delivery']['code'] = NULL;
                            $this->data['Delivery']['created']    = $dateNow;
                            $this->data['Delivery']['created_by'] = $user['User']['id'];
                            $this->data['Delivery']['status']  = 2;
                            $this->Delivery->save($this->data);
                            $deliveryId = $this->Delivery->id;
                            // Update Code Delivery
                            $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $salesOrder['SalesOrder']['branch_id'])));
                            $dnCode     = date("y").$modComCode['ModuleCodeBranch']['dn_code'];
                            // Get Module DN Code
                            $modDnCode  = $this->Helper->getModuleCode($dnCode, $deliveryId, 'code', 'deliveries', 'status >= 0 AND branch_id = '.$salesOrder['SalesOrder']['branch_id']);
                            // Updaet Module Code to DN
                            mysql_query("UPDATE deliveries SET code = '".$modDnCode."' WHERE id = ".$deliveryId);
                        } else {
                            $deliveryId = 0;
                        }
                        
                        if($this->data['SalesOrder']['branch_id'] != $sales_order['SalesOrder']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($this->data['SalesOrder']['so_code'], $saleOrderId, 'so_code', 'sales_orders', 'status != -1 AND branch_id = '.$salesOrder['SalesOrder']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE sales_orders SET so_code = '".$modCode."', delivery_id = ".$deliveryId." WHERE id = ".$saleOrderId);
                            $glReference = $modCode;
                        } else {
                            // Updaet Module Code
                            mysql_query("UPDATE sales_orders SET so_code = '".$glReference."', delivery_id = ".$deliveryId." WHERE id = ".$saleOrderId);
                        }
                        /* Create General Ledger */
                        $generalLedger = array();
                        $this->GeneralLedger->create();
                        $generalLedger['GeneralLedger']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $generalLedger['GeneralLedger']['sales_order_id'] = $saleOrderId;
                        $generalLedger['GeneralLedger']['date']       = $this->data['SalesOrder']['order_date'];
                        $generalLedger['GeneralLedger']['reference']  = $glReference;
                        $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                        $generalLedger['GeneralLedger']['is_sys']  = 1;
                        $generalLedger['GeneralLedger']['is_adj']  = 0;
                        $generalLedger['GeneralLedger']['created'] = $dateNow;
                        // Check Approve
                        if($this->data['SalesOrder']['is_approve'] == 1){
                            $generalLedger['GeneralLedger']['is_active'] = 1;
                        }else{
                            $generalLedger['GeneralLedger']['is_active'] = 2;
                        }
                        $this->GeneralLedger->save($generalLedger);
                        $generalLedgerId = $this->GeneralLedger->id;
                        /* General Ledger Detail (A/R) For save Finish */
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $salesOrder['SalesOrder']['ar_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $salesOrder['SalesOrder']['balance'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference;
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                        /* General Ledger Detail Total Discount */
                        if ($this->data['SalesOrder']['discount_us'] > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['SalesOrder']['discount_us'];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Total Discount';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }

                        /* General Ledger Detail Total VAT */
                        if (($salesOrder['SalesOrder']['total_vat']) > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['vat_chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $salesOrder['SalesOrder']['total_vat'];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Total VAT';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                        
                        /* General Ledger Detail Total Deposit */
                        if ($this->data['SalesOrder']['total_deposit'] > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $depositAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['SalesOrder']['total_deposit'];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Total Deposit';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                        for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                            if (!empty($_POST['product_id'][$i])) {
                                $tranDetailAcct = 0;
                                /* Sales Order Detail */
                                $salesOrderDetail = array();
                                $this->SalesOrderDetail->create();
                                $salesOrderDetail['SalesOrderDetail']['sys_code']         = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                                $salesOrderDetail['SalesOrderDetail']['sales_order_id']   = $saleOrderId;
                                $salesOrderDetail['SalesOrderDetail']['promotional_id']   = $_POST['promotional_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['discount_id']      = $_POST['discount_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['discount_amount']  = $_POST['discount'][$i];
                                $salesOrderDetail['SalesOrderDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                                $salesOrderDetail['SalesOrderDetail']['product_id']   = $_POST['product_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty_uom_id']   = $_POST['qty_uom_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty']          = $_POST['qty'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty_free']     = $_POST['qty_free'][$i];
                                $salesOrderDetail['SalesOrderDetail']['unit_price']   = $_POST['unit_price'][$i];
                                $salesOrderDetail['SalesOrderDetail']['total_price']  = $_POST['total_price_bf_dis'][$i];
                                $salesOrderDetail['SalesOrderDetail']['conversion']   = $_POST['conversion'][$i];
                                if($_POST['lots_number'][$i] != '' && $_POST['lots_number'][$i] != '0'){
                                    $lotsNumber = $_POST['lots_number'][$i];
                                } else {
                                    $lotsNumber = '0';
                                }
                                $salesOrderDetail['SalesOrderDetail']['lots_number']  = $lotsNumber;
                                if($_POST['expired_date'][$i] != '' && $_POST['expired_date'][$i] != '0000-00-00'){
                                    $expiredDate = $_POST['expired_date'][$i];
                                } else {
                                    $expiredDate = '0000-00-00';
                                }
                                $salesOrderDetail['SalesOrderDetail']['expired_date'] = $expiredDate;
                                $salesOrderDetail['SalesOrderDetail']['note']         = $_POST['note'][$i];
                                $this->SalesOrderDetail->save($salesOrderDetail);
                                $salesOrderDetailId = $this->SalesOrderDetail->id;
                                $transactionPro++;
                                $qtyOrder      = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_uom_val'][$i] / $_POST['conversion'][$i]);
                                $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['conversion'][$i];
                                $priceSales    = $_POST['total_price_bf_dis'][$i] - $_POST['discount'][$i];
                                
                                // Transaction Detail
                                $tranDetail = array();
                                $this->TransactionDetail->create();
                                $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                                $tranDetail['TransactionDetail']['type']       = 1;
                                $tranDetail['TransactionDetail']['module_id']  = $salesOrderDetailId;
                                $this->TransactionDetail->save($tranDetail);
                                $tranDetailId = $this->TransactionDetail->id;
                                
                                /* Inventory Valuation */
                                $inv_valutaion = array();
                                $this->InventoryValuation->create();
                                $inv_valutaion['InventoryValuation']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                                $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                                $inv_valutaion['InventoryValuation']['sales_order_id'] = $saleOrderId;
                                $inv_valutaion['InventoryValuation']['company_id']  = $this->data['SalesOrder']['company_id'];
                                $inv_valutaion['InventoryValuation']['branch_id']   = $this->data['SalesOrder']['branch_id'];
                                $inv_valutaion['InventoryValuation']['type']        = "Invoice";
                                $inv_valutaion['InventoryValuation']['reference']   = $glReference;
                                $inv_valutaion['InventoryValuation']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $inv_valutaion['InventoryValuation']['date'] = $this->data['SalesOrder']['order_date'];
                                $inv_valutaion['InventoryValuation']['pid']  = $_POST['product_id'][$i];
                                $inv_valutaion['InventoryValuation']['small_qty'] = "-" . $qtyOrderSmall;
                                $inv_valutaion['InventoryValuation']['qty']  = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                                $inv_valutaion['InventoryValuation']['cost'] = null;
                                $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                                $inv_valutaion['InventoryValuation']['created']     = $dateNow;
                                // Check Approve
                                if($this->data['SalesOrder']['is_approve'] == 1){
                                    $inv_valutaion['InventoryValuation']['is_active'] = 1;
                                }else{
                                    $inv_valutaion['InventoryValuation']['is_active'] = 2;
                                }
                                $this->InventoryValuation->saveAll($inv_valutaion);
                                $inv_valutation_id = $this->InventoryValuation->id;
                                
                                if($rowDelivery[0] == 0){
                                    // Update Inventory Location Group
                                    $dataGroup = array();
                                    $dataGroup['module_type']       = 10;
                                    $dataGroup['sales_order_id']    = $saleOrderId;
                                    $dataGroup['product_id']        = $_POST['product_id'][$i];
                                    $dataGroup['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                    $dataGroup['date']         = $salesOrder['SalesOrder']['order_date'];
                                    $dataGroup['total_qty']    = $qtyOrderSmall;
                                    $dataGroup['total_order']  = ($_POST['qty'][$i] * $_POST['conversion'][$i]);
                                    $dataGroup['total_free']   = ($_POST['qty_free'][$i] * $_POST['conversion'][$i]);
                                    $dataGroup['transaction_id']  = $tranDetailId;
                                    // Update Inventory Group
                                    $this->Inventory->saveGroupTotalDetail($dataGroup);

                                    $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." AND is_active = 1)";
                                    $invInfos   = array();
                                    $index      = 0;
                                    $totalOrder = $qtyOrderSmall;
                                    if($allowStockMinus == false){
                                        // Calculate Location, Lot, Expired Date
                                        $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$salesOrderDetail['SalesOrderDetail']['lots_number']."' AND group_totals.expired_date = '".$salesOrderDetail['SalesOrderDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                                        while($rowInventory = mysql_fetch_array($sqlInventory)){
                                            $stockOrder = 0;
                                            $sqlOrder   = mysql_query("SELECT SUM(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.location_id = ".$rowInventory['location_id']." AND sor.lots_number = '".$rowInventory['lots_number']."' AND sor.expired_date = '".$rowInventory['expired_date']."' AND sor.sales_order_id = ".$id." AND sor.product_id = ".$_POST['product_id'][$i]." AND sor.location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." GROUP BY sor.product_id");
                                            if(mysql_num_rows($sqlOrder)){
                                                $rOrder = mysql_fetch_array($sqlOrder);
                                                $stockOrder = $rOrder[0];
                                            }
                                            $totalInv = ($rowInventory['total_qty'] + $stockOrder);
                                            if($totalOrder > 0 && $totalInv > 0){
                                                if($totalInv >= $totalOrder){
                                                    $invInfos[$index]['total_qty']    = $totalOrder;
                                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                    $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                    $totalOrder = 0;
                                                    ++$index;
                                                }else if($totalInv < $totalOrder){
                                                    $invInfos[$index]['total_qty']    = $totalInv;
                                                    $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                    $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                    $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                    $totalOrder = $totalOrder - $totalInv;
                                                    ++$index;
                                                }
                                            }else{
                                                break;
                                            }
                                        }
                                    } else {
                                        $sqlLocation = mysql_query("SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." AND is_active = 1 ORDER BY id ASC LIMIT 1");
                                        $rowLocation = mysql_fetch_array($sqlLocation);
                                        $invInfos[$index]['total_qty']    = $totalOrder;
                                        $invInfos[$index]['location_id']  = $rowLocation['id'];
                                        $invInfos[$index]['lots_number']  = $salesOrderDetail['SalesOrderDetail']['lots_number'];
                                        $invInfos[$index]['expired_date'] = '0000-00-00';
                                    }
                                    // Update Inventory
                                    foreach($invInfos AS $invInfo){
                                        // Update Inventory (Sales)
                                        $data = array();
                                        $data['module_type']       = 10;
                                        $data['sales_order_id']    = $saleOrderId;
                                        $data['product_id']        = $_POST['product_id'][$i];
                                        $data['location_id']       = $invInfo['location_id'];
                                        $data['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                        $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                                        $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                                        $data['date']         = $salesOrder['SalesOrder']['order_date'];
                                        $data['total_qty']    = $invInfo['total_qty'];
                                        $data['total_order']  = $invInfo['total_qty'];
                                        $data['total_free']   = 0;
                                        $data['user_id']      = $user['User']['id'];
                                        $data['customer_id']  = $salesOrder['SalesOrder']['customer_id'];
                                        $data['vendor_id']    = "";
                                        $data['unit_cost']    = 0;
                                        $data['unit_price']   = $priceSales;
                                        $data['transaction_id'] = $tranDetailId;
                                        // Update Invetory Location
                                        $this->Inventory->saveInventory($data);
                                        //Insert Into Delivery Detail
                                        $deliveyDetail = array();
                                        $this->DeliveryDetail->create();
                                        $deliveyDetail['DeliveryDetail']['delivery_id']    = $deliveryId;
                                        $deliveyDetail['DeliveryDetail']['sales_order_id'] = $saleOrderId;
                                        $deliveyDetail['DeliveryDetail']['sales_order_detail_id'] = $salesOrderDetailId;
                                        $deliveyDetail['DeliveryDetail']['product_id']    = $_POST['product_id'][$i];
                                        $deliveyDetail['DeliveryDetail']['location_id']   = $invInfo['location_id'];
                                        $deliveyDetail['DeliveryDetail']['lots_number']   = $data['lots_number'];
                                        $deliveyDetail['DeliveryDetail']['expired_date']  = $data['expired_date'];
                                        $deliveyDetail['DeliveryDetail']['total_qty']     = $invInfo['total_qty'];
                                        $this->DeliveryDetail->save($deliveyDetail);
                                    }
                                } else {
                                    $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id'].")";
                                    $invInfos   = array();
                                    $index      = 0;
                                    $totalOrder = $qtyOrderSmall;
                                    // Calculate Location, Lot, Expired Date
                                    $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." AND group_totals.lots_number = '".$salesOrderDetail['SalesOrderDetail']['lots_number']."' AND group_totals.expired_date = '".$salesOrderDetail['SalesOrderDetail']['expired_date']."' GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
                                    while($rowInventory = mysql_fetch_array($sqlInventory)){
                                        $stockOrder = 0;
                                        $sqlOrder   = mysql_query("SELECT SUM(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.location_id = ".$rowInventory['location_id']." AND sor.lots_number = '".$rowInventory['lots_number']."' AND sor.lots_number = '".$rowInventory['lots_number']."' AND sor.expired_date = '".$rowInventory['expired_date']."' AND sor.sales_order_id = ".$id." AND sor.product_id = ".$_POST['product_id'][$i]." AND sor.location_group_id = ".$salesOrder['SalesOrder']['location_group_id']." GROUP BY sor.product_id");
                                        if(mysql_num_rows($sqlOrder)){
                                            $rOrder = mysql_fetch_array($sqlOrder);
                                            $stockOrder = $rOrder[0];
                                        }
                                        $totalInv = ($rowInventory['total_qty'] + $stockOrder);
                                        if($totalOrder > 0 && $totalInv > 0){
                                            if($totalInv >= $totalOrder){
                                                $invInfos[$index]['total_qty']    = $totalOrder;
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = 0;
                                                ++$index;
                                            }else if($totalInv < $totalOrder){
                                                $invInfos[$index]['total_qty']    = $totalInv;
                                                $invInfos[$index]['location_id']  = $rowInventory['location_id'];
                                                $invInfos[$index]['lots_number']  = $rowInventory['lots_number'];
                                                $invInfos[$index]['expired_date'] = $rowInventory['expired_date'];
                                                $totalOrder = $totalOrder - $totalInv;
                                                ++$index;
                                            }
                                        }else{
                                            break;
                                        }
                                    }
                                    // Update Inventory
                                    foreach($invInfos AS $invInfo){
                                        //Insert Into Tmp Sales
                                        $tmpDelivey = array();
                                        $this->StockOrder->create();
                                        $tmpDelivey['StockOrder']['sales_order_id']        = $saleOrderId;
                                        $tmpDelivey['StockOrder']['sales_order_detail_id'] = $salesOrderDetailId;
                                        $tmpDelivey['StockOrder']['product_id']            = $_POST['product_id'][$i];
                                        $tmpDelivey['StockOrder']['location_group_id']     = $salesOrder['SalesOrder']['location_group_id'];
                                        $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                        $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                        $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                        $tmpDelivey['StockOrder']['date'] = $salesOrder['SalesOrder']['order_date'];
                                        $tmpDelivey['StockOrder']['qty']  = $invInfo['total_qty'];
                                        $this->StockOrder->save($tmpDelivey);
                                        $this->Inventory->saveGroupQtyOrder($salesOrder['SalesOrder']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $salesOrder['SalesOrder']['order_date'], '+');
                                    }
                                }

                                /* General Ledger Detail (Product Income) */
                                $this->GeneralLedgerDetail->create();
                                $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                                $dataIncAccount  = mysql_fetch_array($queryIncAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Product # ' . $_POST['product'][$i];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                                
                                /* General Ledger Detail (Product Discount) */
                                if ($_POST['discount'][$i] > 0) {
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Product # ' . $_POST['product'][$i] . ' Discount';
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                    $tranDetailAcct++;
                                }

                                /* General Ledger Detail (Inventory) */
                                $this->GeneralLedgerDetail->create();
                                $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                                $dataInvAccount = mysql_fetch_array($queryInvAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory for INV # ' . $glReference . ' Product # ' . $_POST['product'][$i];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;

                                /* General Ledger Detail (COGS) */
                                $this->GeneralLedgerDetail->create();
                                $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                                $dataCogsAccount = mysql_fetch_array($queryCogsAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS for INV # ' . $glReference . ' Product # ' . $_POST['product'][$i];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                                // Update Transaction Detail
                                mysql_query("UPDATE transaction_details SET save_acct = ".$tranDetailAcct." WHERE id = ".$tranDetailId);
                            } else if (!empty($_POST['service_id'][$i])) {
                                $tranDetailAcct = 0;
                                $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                                $dataServiceAccount  = mysql_fetch_array($queryServiceAccount);
                                if($_POST['type'][$i] == 2){
                                    $serviceAcct = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                    $discAcct    = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                                } else {
                                    $serviceAcct = $dataServiceAccount[0];
                                    $discAcct    = $salesDiscAccount['AccountType']['chart_account_id'];
                                }
                                /* Sales Order Service */
                                $salesOrderService = array();
                                $this->SalesOrderService->create();
                                $salesOrderService['SalesOrderService']['sales_order_id']   = $saleOrderId;
                                $salesOrderService['SalesOrderService']['discount_id']      = $_POST['discount_id'][$i];
                                $salesOrderService['SalesOrderService']['discount_amount']  = $_POST['discount'][$i];
                                $salesOrderService['SalesOrderService']['discount_percent'] = $_POST['discount_percent'][$i];
                                $salesOrderService['SalesOrderService']['service_id']  = $_POST['service_id'][$i];
                                $salesOrderService['SalesOrderService']['qty']         = $_POST['qty'][$i];
                                $salesOrderService['SalesOrderService']['qty_free']    = $_POST['qty_free'][$i];
                                $salesOrderService['SalesOrderService']['unit_price']  = $_POST['unit_price'][$i];
                                $salesOrderService['SalesOrderService']['total_price'] = $_POST['total_price_bf_dis'][$i];
                                $salesOrderService['SalesOrderService']['note']        = $_POST['note'][$i];
                                $salesOrderService['SalesOrderService']['type']        = $_POST['type'][$i];
                                if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00'){
                                    $salesOrderService['SalesOrderService']['start'] = $_POST['start'][$i];
                                }
                                $this->SalesOrderService->save($salesOrderService);
                                $salesServiceId = $this->SalesOrderService->id;
                                $transactionSer++;
                                /* General Ledger Detail (Service) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $serviceAcct;
                                $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Service # ' . $_POST['product'][$i];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                                /* General Ledger Detail (Service Discount) */
                                if ($_POST['discount'][$i] > 0) {
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $discAcct;
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' Service # ' . $_POST['product'][$i] . ' Discount';
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                    $tranDetailAcct++;
                                }
                                // Transaction Detail
                                $tranDetail = array();
                                $this->TransactionDetail->create();
                                $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                                $tranDetail['TransactionDetail']['type']       = 2;
                                $tranDetail['TransactionDetail']['module_id']  = $salesServiceId;
                                $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                                $this->TransactionDetail->save($tranDetail);
                                if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00' && $_POST['type'][$i] == 2 && $_POST['qty'][$i] > 0){
                                    $this->loadModel('UnearnedSchedule');
                                    $dateStart = $_POST['start'][$i];
                                    for($k= 0; $k < $_POST['qty'][$i]; $k++){
                                        $start  = date('Y-m-d', strtotime("+".$k." months", strtotime($dateStart)));
                                        $unearnSchedule = array();
                                        $unearnSchedule['UnearnedSchedule']['module_id']   = $saleOrderId;
                                        $unearnSchedule['UnearnedSchedule']['module_detail_id'] = $salesServiceId;
                                        $unearnSchedule['UnearnedSchedule']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                        $unearnSchedule['UnearnedSchedule']['date']        = $start;
                                        $unearnSchedule['UnearnedSchedule']['created']     = date("Y-m-d H:i:s");
                                        $unearnSchedule['UnearnedSchedule']['created_by']  = $user['User']['id'];
                                        // Unearned Revenue
                                        $this->UnearnedSchedule->create();
                                        $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                        $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                        $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $dataServiceAccount[0];
                                        $unearnSchedule['UnearnedSchedule']['debit']      = $_POST['unit_price'][$i];
                                        $unearnSchedule['UnearnedSchedule']['credit']     = $_POST['unit_price'][$i];
                                        $this->UnearnedSchedule->save($unearnSchedule);
                                        // Check Discount
                                        if ($_POST['discount'][$i] > 0) {
                                            $unitDiscount = $_POST['discount'][$i] / $_POST['qty'][$i];
                                            // Unearned Discount
                                            $this->UnearnedSchedule->create();
                                            $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                            $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $salesDiscAccount['AccountType']['chart_account_id'];
                                            $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                                            $unearnSchedule['UnearnedSchedule']['debit']      = $unitDiscount;
                                            $unearnSchedule['UnearnedSchedule']['credit']     = $unitDiscount;
                                            $this->UnearnedSchedule->save($unearnSchedule);
                                        }
                                    }
                                }
                            }
                        }
                        // Update Transaction Save
                        mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                        // Recalculate Average Cost
                        mysql_query("UPDATE tracks SET val='".$this->data['SalesOrder']['order_date']."', is_recalculate = 1 WHERE id=1");
                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit', $id, $saleOrderId);
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error)', $id);
                        // Error Saves
                        $result['error'] = 4;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    // Error Saves
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error Status)', $id);
                    $result['error'] = 5;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error Out of Stock)', $id);
                // Error Out Of Stock
                $result['listOutStock'] = $listOutStock;
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            }
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Edit', $id);
            $this->data = $this->SalesOrder->read(null, $id);
            $joinUsers = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
            $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id'));
            $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
            $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $paymentTerms   = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('PaymentTerm.is_active=1')));
            $this->set(compact("paymentTerms", "locationGroups", "companies", "branches"));
        }else{
            exit;
        }
    }
    
    function editDetail($salesOrderId = null) {
        $this->layout = 'ajax';
        if ($salesOrderId >= 0) {
            $user = $this->getCurrentUser();
            $salesOrder         = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $salesOrderId)));
            $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $salesOrderId)));
            $salesOrderDetails  = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $salesOrderId)));
            $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $uoms     = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $this->set(compact('branches', 'salesOrder', 'salesOrderDetails', 'salesOrderMiscs', 'salesOrderServices', 'uoms', 'locSetting'));
        } else {
            exit;
        }
    }

    function searchProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')'
                             ));
        $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id')
                             );
        $joinPgroup  = array(
                             'table' => 'pgroups',
                             'type' => 'INNER',
                             'alias' => 'Pgroup',
                             'conditions' => array(
                                 'Pgroup.id = ProductPgroup.pgroup_id',
                                 '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                             )
                          );
        $joins = array(
            $joinProductgroup,
            $joinPgroup,
            $joinProductBranch
        );
        $products = ClassRegistry::init('Product')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Product.code LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.barcode LIKE' => '%' . trim($this->params['url']['q']) . '%',
                                'Product.name LIKE' => '%' . trim($this->params['url']['q']) . '%',
                            ), 'Product.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'
                            , 'Product.is_active' => 1
                            , '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
                        ),
                        'joins' => $joins,
                        'group' => array(
                            'Product.id'
                        )
                    ));
        $this->set(compact('products'));
    }

    function searchProductByCode($company_id, $customerId, $branchId, $saleOrderId = null) {
        $this->layout = 'ajax';
        $product_code       = !empty($this->data['code']) ? $this->data['code'] : "";
        $location_group_id  = $this->data['location_group_id'];
        $dateOrder   = $this->data['order_date'];
        $lotsNumber  = $this->data['lots_number']!=""?$this->data['lots_number']:'0';
        $expiredDate = $this->data['expired_date']!=""?$this->data['expired_date']:'0000-00-00';
        $user        = $this->getCurrentUser();
        $joinProductBranch  = array(
                            'table' => 'product_branches',
                            'type' => 'INNER',
                            'alias' => 'ProductBranch',
                            'conditions' => array(
                                'ProductBranch.product_id = Product.id',
                                'ProductBranch.branch_id' => $branchId
                            ));
        $joinInventory  = array(
                            'table' => $location_group_id.'_group_totals',
                            'type' => 'LEFT',
                            'alias' => 'InventoryTotal',
                            'conditions' => array(
                                'InventoryTotal.product_id = Product.id',
                                'InventoryTotal.location_group_id' => $location_group_id,
                                'InventoryTotal.lots_number' => $lotsNumber,
                                'InventoryTotal.expired_date' => $expiredDate,
                                '(InventoryTotal.total_qty) > 0',
                                'InventoryTotal.location_id IN (SELECT id FROM locations WHERE location_group_id = '.$location_group_id.' AND is_active = 1)'
                            )
                          );
        $joinProductgroup  = array(
                            'table' => 'product_pgroups',
                            'type' => 'INNER',
                            'alias' => 'ProductPgroup',
                            'conditions' => array('ProductPgroup.product_id = Product.id')
                            );
        $joinPgroup  = array(
                            'table' => 'pgroups',
                            'type' => 'INNER',
                            'alias' => 'Pgroup',
                            'conditions' => array(
                                'Pgroup.id = ProductPgroup.pgroup_id',
                                '(Pgroup.user_apply = 0 OR (Pgroup.user_apply = 1 AND Pgroup.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = '.$user['User']['id'].')))'
                            )
                          );
        $groupBy = "Product.id, InventoryTotal.product_id";
        $joins = array(
            $joinInventory,
            $joinProductgroup,
            $joinPgroup,
            $joinProductBranch
        );

        $product = ClassRegistry::init('Product')->find('first', array(
                    'fields' => array(
                        'Product.id',
                        'Product.code',
                        'Product.barcode',
                        'Product.name',
                        'Product.small_val_uom',
                        'Product.price_uom_id',
                        'Product.is_packet',
                        'Product.is_lots',
                        'Product.is_expired_date',
                        'SUM(InventoryTotal.total_qty - InventoryTotal.total_order) AS total_qty',
                    ),
                    'conditions' => array(
                        array(
                            "OR" => array(
                                'trim(Product.code)' => trim($product_code),
                                'trim(Product.barcode)' => trim($product_code)
                            )
                        ),
                        'Product.company_id' => $company_id,
                        'Product.is_active' => 1, 
                        "((is_not_for_sale = 0 AND period_from IS NULL AND period_to IS NULL) OR (is_not_for_sale = 0 AND period_from <= '".$dateOrder."' AND period_to >= '".$dateOrder."') OR (is_not_for_sale = 1 AND period_from IS NOT NULL AND period_to IS NOT NULL AND '".$dateOrder."' NOT BETWEEN period_from AND period_to))",
                        '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
                    ),
                    'joins' => $joins,
                    'group' => $groupBy
                ));
        // Check Promotional
        $promotional = array();
        if(!empty($product)){
            if(!empty($dateOrder)){
                $sqlPromo = mysql_query("SELECT promotional_details.promotional_id FROM promotional_details 
                INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id WHERE promotionals.status = 2 AND 
                promotionals.approved IS NOT NULL AND promotionals.approved_by IS NOT NULL AND promotionals.branch_id = ".$branchId." AND 
                promotionals.apply = 1 AND promotionals.start <= '".$dateOrder."' AND promotionals.end >= '".$dateOrder."' AND 
                ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR 
                (promotionals.customer_id IS NULL AND (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) AND 
                promotional_details.product_request_id = ".$product['Product']['id']." ORDER BY promotionals.id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPromo)){
                    $indexP = 0;
                    $rowPromo = mysql_fetch_array($sqlPromo);
                    $promotional['promotion_id'] = $rowPromo[0];
                    $sqlProduct = mysql_query("SELECT uom_request, discount_percent, discount_amount FROM promotional_details WHERE promotional_id = ".$rowPromo[0]." AND product_request_id = ".$product['Product']['id']);
                    while($rowProduct = mysql_fetch_array($sqlProduct)){
                        $promotional['product'][$indexP]['uom_id'] = $rowProduct['uom_request'];
                        $promotional['product'][$indexP]['discount_percent'] = $rowProduct['discount_percent'];
                        $promotional['product'][$indexP]['discount_amount']  = $rowProduct['discount_amount'];
                        $indexP++;
                    }
                } else {
                    $sqlPromoAll = mysql_query("SELECT promotional_details.promotional_id FROM promotional_details 
                    INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
                    WHERE promotionals.status = 2 AND
                    promotionals.approved IS NOT NULL AND
                    promotionals.approved_by IS NOT NULL AND
                    promotionals.branch_id IS NULL AND
                    promotionals.apply = 2 AND
                    promotionals.start <= '".$dateOrder."' AND
                    promotionals.end >= '".$dateOrder."' AND
                    ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR
                    (promotionals.customer_id IS NULL AND (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) AND
                    promotional_details.product_request_id = ".$product['Product']['id']." ORDER BY promotionals.id DESC LIMIT 1;");
                    if(mysql_num_rows($sqlPromoAll)){
                        $indexP = 0;
                        $rowPromo   = mysql_fetch_array($sqlPromoAll);
                        $promotional['promotion_id'] = $rowPromo[0];
                        $sqlProduct = mysql_query("SELECT uom_request, discount_percent, discount_amount FROM promotional_details WHERE promotional_id = ".$rowPromo[0]." AND product_request_id = ".$product['Product']['id']);
                        while($rowProduct = mysql_fetch_array($sqlProduct)){
                            $promotional['product'][$indexP]['uom_id'] = $rowProduct['uom_request'];
                            $promotional['product'][$indexP]['discount_percent'] = $rowProduct['discount_percent'];
                            $promotional['product'][$indexP]['discount_amount']  = $rowProduct['discount_amount'];
                            $indexP++;
                        }
                    }
                }
            }
        }
        $this->set(compact('product', 'location_group_id', 'saleOrderId', 'dateOrder', 'customerId', 'lotsNumber', 'expiredDate', 'promotional'));
    }
    
    function quotation($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
        $this->set('saleId', $_POST['sale_id']);
    }
    
    function quotationAjax($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
        $this->set('saleId', $_GET['sale_id']);
    }
    
    function order($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
        $this->set('saleId', $_POST['sale_id']);
    }
    
    function orderAjax($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
        $this->set('saleId', $_GET['sale_id']);
    }
    
    function printInvoice($id = null,$head=0){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $salesOrder = $this->SalesOrder->read(null, $id);
            if (!empty($salesOrder)) {
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all", array('conditions' => array('SalesOrderMisc.sales_order_id' => $id)));
                $this->set(compact('salesOrder','salesOrderDetails','salesOrderServices','salesOrderMiscs','head'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function printInvoiceNoHead($id = null,$head=0){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $salesOrder = $this->SalesOrder->read(null, $id);
            if (!empty($salesOrder)) {
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all", array('conditions' => array('SalesOrderMisc.sales_order_id' => $id)));
                $this->set(compact('salesOrder','salesOrderDetails','salesOrderServices','salesOrderMiscs','head'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function printReceipt($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $sr = ClassRegistry::init('SalesOrderReceipt')->find("first",
                            array('conditions' => array('SalesOrderReceipt.id' => $receiptId, 'SalesOrderReceipt.is_void' => 0)));

            $salesOrder = ClassRegistry::init('SalesOrder')->find("first",
                            array('conditions' => array('SalesOrder.id' => $sr['SalesOrder']['id'])));
            if (!empty($salesOrder)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first",
                                array(
                                    "conditions" => array("ExchangeRate.is_active" => 1),
                                    "order" => array("ExchangeRate.created desc")
                                )
                );
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $salesOrder['SalesOrder']['location_id'], "Location.is_active" => "1")));
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all",
                                array('conditions' => array('SalesOrderDetail.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all",
                                array('conditions' => array('SalesOrderService.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all",
                                array('conditions' => array('SalesOrderMisc.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all",
                                array('conditions' => array('SalesOrderReceipt.sales_order_id' => $sr['SalesOrder']['id'], 'SalesOrderReceipt.is_void' => 0)));

                $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderMiscs', 'salesOrderReceipts', 'sr', 'lastExchangeRate', 'salesOrderServices', 'location'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function printReceiptCurrent($receiptId = null) {
        if (!empty($receiptId)) {
            $this->layout = 'ajax';
            $sr = ClassRegistry::init('SalesOrderReceipt')->find("first",
                            array('conditions' => array('SalesOrderReceipt.id' => $receiptId, 'SalesOrderReceipt.is_void' => 0)));

            $salesOrder = ClassRegistry::init('SalesOrder')->find("first",
                            array('conditions' => array('SalesOrder.id' => $sr['SalesOrder']['id'])));
            if (!empty($salesOrder)) {
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first",
                                array(
                                    "conditions" => array("ExchangeRate.is_active" => 1),
                                    "order" => array("ExchangeRate.created desc")
                                )
                );
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $salesOrder['SalesOrder']['location_id'], "Location.is_active" => "1")));
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all",
                                array('conditions' => array('SalesOrderDetail.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all",
                                array('conditions' => array('SalesOrderService.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all",
                                array('conditions' => array('SalesOrderMisc.sales_order_id' => $sr['SalesOrder']['id'])));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all",
                                array('conditions' => array('SalesOrderReceipt.id <= ' . $receiptId, 'SalesOrderReceipt.sales_order_id' => $sr['SalesOrder']['id'], 'SalesOrderReceipt.is_void' => 0)));

                $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderMiscs', 'salesOrderReceipts', 'sr', 'lastExchangeRate', 'salesOrderServices', 'location'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }
    
    function customerCondition($id = null, $saleId = null){
        $this->layout = 'ajax';
        $result   = array();
        if (!empty($id)) {
            $condition = "";
            // Condition For Edit
            if($saleId > 0){
                $condition = " AND id != ".$saleId;
            }
            $sqlCus   = mysql_query("SELECT IFNULL(limit_balance, 0) AS limit_balance, IFNULL(limit_total_invoice, 0) AS limit_total_invoice FROM customers WHERE id =".$id);
            $rowCus   = mysql_fetch_array($sqlCus);
            $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(balance) AS total_amount FROM sales_orders WHERE customer_id = ".$id." AND status > 0 AND balance > 0".$condition." GROUP BY customer_id");
            $limitInvoice = $rowCus['limit_total_invoice'];
            $limitBalance = $rowCus['limit_balance'];
            $totalInvUsed = 0;
            $totalAmoUsed = 0;
            // Check Limit Balance
            if(@mysql_num_rows($queryCon)){
                $dataCon  = mysql_fetch_array($queryCon);
                $totalInvUsed = $dataCon['total_invoice'];
                $totalAmoUsed = $dataCon['total_amount'];
            }
            $result['error']         = 0;
            $result['limit_invoice'] = $limitInvoice;
            $result['limit_balance'] = $limitBalance;
            $result['invoice_used']  = ($totalInvUsed + 1);
            $result['balance_used']  = $totalAmoUsed;
        }else{
            // Invalid Id
            $result['error'] = 1;
        }
        echo json_encode($result);
        exit;
    }
    
    function approveSale($id){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            // Approve Sale & General Ledger & Inventory Valuation
            mysql_query("UPDATE `sales_orders` SET `status`=1, `approved_by` = {$user['User']['id']}, `approved` = '".date("Y-m-d H:i:s")."' WHERE  `id`={$id};");
            mysql_query("UPDATE `general_ledgers` SET `is_active` = 1 WHERE  `sales_order_id`= {$id};");
            mysql_query("UPDATE `inventory_valuations` SET `is_active` = 1 WHERE  `sales_order_id`= {$id};");
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        }else{
            echo MESSAGE_DATA_INVALID;
        }
        exit;
    }
    
    function getProductFromQuote($id = null, $locationGroup = null, $salesId = 0){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id) && empty($locationGroup)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $allowLots    = false;
        $allowExpired = false;
        $priceDecimal = 2;
        $sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40) AND is_active = 1");
        while($rowSetting = mysql_fetch_array($sqlSetting)){
            if($rowSetting['id'] == 6){
                if($rowSetting['is_checked'] == 1){
                    $allowLots = true;
                }
            } else if($rowSetting['id'] == 7){
                if($rowSetting['is_checked'] == 1){
                    $allowExpired = true;
                }
            } else if($rowSetting['id'] == 40){
                $priceDecimal = $rowSetting['value'];
            }
        }
        $user = $this->getCurrentUser();
        $quotation = ClassRegistry::init('Quotation')->read(null, $id);
        $allowProductDiscount = $this->Helper->checkAccess($user['User']['id'], $this->params['controller'], 'discount');
        $allowEditPrice = $this->Helper->checkAccess($user['User']['id'], $this->params['controller'], 'editUnitPrice');
        // Check Permission Edit Price
        if($allowEditPrice){
            $readonly = '';
        }else{
            $readonly = 'readonly="readonly"';
        }
        $rowList = array();
        $rowLbl  = "";
        // Get Product
        $sqlQuoteDetail  = mysql_query("SELECT products.code AS code, products.barcode AS barcode, products.name AS name, products.unit_cost AS unit_cost, products.price_uom_id AS price_uom_id, products.small_val_uom AS small_val_uom, products.is_lots AS is_lots, quotation_details.product_id AS product_id, quotation_details.qty AS qty,quotation_details.qty_free AS qty_free, quotation_details.qty_uom_id AS qty_uom_id, quotation_details.conversion AS conversion, quotation_details.unit_price AS unit_price, quotation_details.total_price AS total_price, quotation_details.discount_id AS discount_id, quotation_details.discount_amount AS discount_amount, quotation_details.discount_percent AS discount_percent, quotations.customer_id AS customer_id FROM quotation_details INNER JOIN quotations ON quotations.id = quotation_details.quotation_id INNER JOIN products ON products.id = quotation_details.product_id WHERE quotation_details.quotation_id = ".$id.";");
        while($rowDetail = mysql_fetch_array($sqlQuoteDetail)){
            $index   = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            $sqlProCus = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$rowDetail['product_id']." AND customer_id = ".$rowDetail['customer_id']." ORDER BY created DESC LIMIT 1");
            if(mysql_num_rows($sqlProCus)){
                $rowProCus   = mysql_fetch_array($sqlProCus);
                $productName = str_replace('"', '&quot;', $rowProCus['name']);
            }
            // Get Total Inventory
            $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$locationGroup}_group_totals WHERE product_id ={$rowDetail['product_id']} AND location_group_id =".$locationGroup." GROUP BY product_id");
            $rowInv = mysql_fetch_array($sqlInv);
            $totalQtySales = ($rowInv['total_qty']>0?$rowInv['total_qty']:0);
            $totalOrder    = (($rowDetail['qty'] + $rowDetail['qty_free'] )* $rowDetail['conversion']);
            // Qty
            if($totalQtySales >= $totalOrder){
                $qty = $rowDetail['qty'];
                $qty_free = $rowDetail['qty_free'];
                $highLight = '';
            }else{
                $qty = 0;
                $qty_free = 0;
                $highLight = 'style="backgroud-color: red;"';
            }
            // Get UOM
            $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                ORDER BY conversion ASC");
            $i = 1;
            $length = mysql_num_rows($query);
            $optionUom = "";
            $conversionUsed = 1;
            $costSelected   = 0;
            while($data=mysql_fetch_array($query)){
                $priceLbl   = "";
                $costLbl    = "";
                $selected   = "";
                $isMain     = "other";
                $isSmall    = 0;
                $conversion = ($rowDetail['small_val_uom'] / $data['conversion']);
                // Check With Qty UOM Id
                if($data['id'] == $rowDetail['qty_uom_id']){   
                    $selected = ' selected="selected" ';
                    $conversionUsed = $conversion;
                }
                // Check With Product UOM Id
                if($data['id'] == $rowDetail['price_uom_id']){
                    $isMain = "first";
                }
                // Check Is Small UOM
                if($length == $i){
                    $isSmall = 1;
                }
                // Get Price
                $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$rowDetail['product_id']." AND product_prices.uom_id =".$data['id']);
                if(@mysql_num_rows($sqlPrice)){
                    $price = 0;
                    while($rowPrice = mysql_fetch_array($sqlPrice)){
                        $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                        if($rowPrice['set_type'] == 1){
                            $price = $rowPrice['amount'];
                        }else if($rowPrice['set_type'] == 2){
                            $percent = ($unitCost * $rowPrice['percent']) / 100;
                            $price = $unitCost + $percent;
                        }else if($rowPrice['set_type'] == 3){
                            $price = $unitCost + $rowPrice['add_on'];
                        }
                        $costLbl  .= 'cost-uom-'.$rowPrice['price_type_id'].'="'.$unitCost.'" ';
                        $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                        if($data['id'] == $rowDetail['qty_uom_id'] && $rowPrice['price_type_id'] == $quotation['Quotation']['price_type_id']){
                            $costSelected = $unitCost;
                        }
                    }
                }else{
                    $unitCost = ($rowDetail['unit_cost'] /  $data['conversion']);
                    $sqlPriceType = mysql_query("SELECT price_types.id FROM price_types INNER JOIN price_type_companies ON price_type_companies.price_type_id = price_types.id AND price_type_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE price_types.is_active = 1 GROUP BY price_types.id;");
                    while($rowPriceType = mysql_fetch_array($sqlPriceType)){
                        $costLbl  .= 'cost-uom-'.$rowPriceType[0].'="'.$unitCost.'"';
                        $priceLbl .= 'price-uom-'.$rowPriceType[0].'="0"';
                    }
                }
                $optionUom .= '<option '.$priceLbl.' '.$costLbl.' '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" value="'.$data['id'].'" conversion="'.$data['conversion'].'">'.$data['name'].'</option>';
                $i++;
            }
            // Calculate Total Price
            $totalPrice = ($qty * $rowDetail['unit_price']);
            if($totalPrice > 0){
                $totalAfterDis = ($totalPrice - $rowDetail['discount_amount']);
                $disId      = $rowDetail['discount_id'];
                $disAmt     = $rowDetail['discount_amount'];
                $disPercent = $rowDetail['discount_percent'];
            } else {
                $totalAfterDis = 0;
                $disId      = '';
                $disAmt     = 0;
                $disPercent = 0;
            }
            // Open Tr
            $rowLbl .= '<tr class="tblSOList" '.$highLight.'>';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center; padding: 0px;">'.++$index.'</td>';
            // SKU
            $rowLbl .= '<td style="width:8%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowDetail['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:16%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">';
            $rowLbl .= '<input type="hidden" class="totalQtySales" value="'.($totalQtySales).'" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderSales" value="'.$totalOrder.'" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" name="product_id[]" value="'.$rowDetail['product_id'].'" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" name="service_id[]" />';
            $rowLbl .= '<input type="hidden" name="discount_id[]" value="'.$disId.'" />';
            $rowLbl .= '<input type="hidden" name="discount_amount[]" value="'.$disAmt.'" />';
            $rowLbl .= '<input type="hidden" name="discount_percent[]" value="'.$disPercent.'" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" class="conversion" value="'.$rowDetail['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_uom_val[]" class="small_uom_val" value="'.$rowDetail['small_val_uom'].'" />';
            $rowLbl .= '<input type="hidden" name="note[]" id="note_'.$index.'" class="note" />';
            $rowLbl .= '<input type="hidden" class="price6" />';
            $rowLbl .= '<input type="hidden" class="price12" />';
            $rowLbl .= '<input type="hidden" class="orgProName" value="PUC: '.$rowDetail['barcode'].'<br/><br/>SKU: '.$rowDetail['code'].'<br/><br/>Name: '.str_replace('"', '&quot;', $rowDetail['name']).'" />';
            $rowLbl .= '<input type="text" id="product_'.$index.'" value="'.$productName.'" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '<img alt="Information" src="'.$this->webroot.'img/button/view.png" class="btnProductSaleInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Information\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lot Number
            $lotDispaly  = '';
            $lotHidden   = 'display: none;';
            $expHidden   = 'display: none;';
            $lotRequired = '';
            $expRequired = '';
            $lotsNumber  = '0';
            $expDisplay  = '';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($rowDetail['is_lots'] == 1){
                $lotHidden   = '';
                $lotRequired = 'validate[required]';
                $lotsNumber  = '';
            }
            if($allowExpired == false){
                $expRequired = 'validate[required]';
                $expDisplay  = 'display: none;';
            }
            // Lot Number
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="lots_number'.$index.'" name="lots_number[]" style="width: 90%; height: 25px; '.$lotHidden.'" class="lots_number '.$lotRequired.'" value="'.$lotsNumber.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expired Date
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px;'.$expDisplay.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="expired_date'.$index.'" name="expired_date[]" style="width: 90%; height: 25px; '.$expHidden.'" class="expired_date '.$expRequired.'" value="'.$expDisplay.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Input
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_'.$index.'" name="qty[]" value="'.$qty.'" style="width:60%;" class="floatQty qty" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_free_'.$index.'" name="qty_free[]" value="'.$qty_free.'" style="width:60%;" class="floatQty qty_free" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $priceColor = 'color: red;';
            $msgStyle   = '';
            $unitPrice  = $rowDetail['unit_price'];
            if($unitPrice >= $costSelected){
                $priceColor = '';
                $msgStyle   = 'style="display: none;"';
            }
            $rowLbl .= '<td style="width: 9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="float unit_cost" name="unit_cost[]" value="'.number_format($costSelected, 2).'" />';
            $rowLbl .= '<input type="text" id="unit_price_'.$index.'" name="unit_price[]" '.$readonly.' value="'.number_format($rowDetail['unit_price'], 2).'" style="width:60%; '.$priceColor.'" class="float unit_price validate[required]" />';
            $rowLbl .= '<img alt="'.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'" src="'.$this->webroot.'img/button/down.png" '.$msgStyle.' class="priceDownOrder" align="absmiddle" onmouseover="Tip(\''.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                if($disId > 0){
                    $disPlay = '';
                }else{
                    $disPlay = 'display: none;';
                }
                $disDisplay  = '<input type="text" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.number_format($disAmt, 2).'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; '.$disPlay.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.$disAmt.'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= number_format($disAmt, 2);
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="total_price_bf_dis_'.$index.'" name="total_price_bf_dis[]" value="'.number_format($totalPrice, 2).'" class="total_price_bf_dis float" />';
            $rowLbl .= '<input type="text" id="total_price_'.$index.'" name="total_price[]" '.$readonly.' value="'.number_format($totalAfterDis, 2).'" style="width:80%" class="total_price float" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%;">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot.'img/button/move_down.png" class="btnMoveDownSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        // Get Service
        $sqlQuoteService  = mysql_query("SELECT services.code AS code, services.name AS name, uoms.abbr AS uom, uoms.id AS uom_id, quotation_services.service_id AS service_id, quotation_services.unit_price AS unit_price, quotation_services.total_price AS total_price, quotation_services.qty AS qty, quotation_services.conversion AS conversion, quotation_services.discount_id AS discount_id, quotation_services.discount_amount AS discount_amount, quotation_services.discount_percent AS discount_percent FROM quotation_services INNER JOIN services ON services.id = quotation_services.service_id INNER JOIN uoms ON uoms.id = services.uom_id WHERE quotation_services.quotation_id = ".$id.";");
        while($rowService = mysql_fetch_array($sqlQuoteService)){
            $index   = rand();
            $qty = $rowService['qty'];
            // Get UOM
            $optionUom = '<option value="'.$rowService['uom_id'].'" conversion="1" selected="selected">'.$rowService['uom'].'</option>';
            // Open Tr
            $rowLbl .= '<tr class="tblSOList">';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center; padding: 0px;">'.++$index.'</td>';
            // SKU
            $rowLbl .= '<td style="width:8%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowService['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:16%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">';
            $rowLbl .= '<input type="hidden" class="totalQtySales" value="1" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderSales" value="1" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" name="product_id[]" value="" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" name="service_id[]" value="'.$rowService['service_id'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_id[]" value="'.$rowService['discount_id'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_amount[]" value="'.$rowService['discount_amount'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_percent[]" value="'.$rowService['discount_percent'].'" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" class="conversion" value="'.$rowService['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_uom_val[]" class="small_uom_val" value="1" />';
            $rowLbl .= '<input type="hidden" name="note[]" id="note_'.$index.'" class="note" />';
            $rowLbl .= '<input type="hidden" class="price6" />';
            $rowLbl .= '<input type="hidden" class="price12" />';
            $rowLbl .= '<input type="text" id="product_'.$index.'" readonly="readonly" value="'.$rowService['name'].'" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lots Number
            $lotDispaly  = '';
            $expDisplay  = '';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($allowExpired == false){
                $expDisplay = 'display: none;';
            }
            // Lot Number
            $rowLbl .= '<td style="width:9%; text-align: center;padding: 0px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="lots_number[]" class="lots_number" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expired Date
            $rowLbl .= '<td style="width:9%; text-align: center;padding: 0px;'.$expDisplay.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="expired_date[]" class="expired_date" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Input
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_'.$index.'" name="qty[]" value="'.$qty.'" style="width:60%;" class="floatQty qty" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_free_'.$index.'" name="qty_free[]" value="0" style="width:60%;" class="floatQty qty_free" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $rowLbl .= '<td style="width: 9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />';
            $rowLbl .= '<input type="text" id="unit_price_'.$index.'" name="unit_price[]" '.$readonly.' value="'.number_format($rowService['unit_price'], 2).'" style="width:60%;" class="float unit_price validate[required]" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                if($rowService['discount_id'] > 0){
                    $disPlay = '';
                }else{
                    $disPlay = 'display: none;';
                }
                $disDisplay  = '<input type="text" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.number_format($rowService['discount_amount'], 2).'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; '.$disPlay.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.$rowService['discount_amount'].'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= number_format($rowService['discount_amount'], 2);
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="total_price_bf_dis_'.$index.'" name="total_price_bf_dis[]" value="'.number_format($rowService['total_price'], 2).'" class="total_price_bf_dis float" />';
            $rowLbl .= '<input type="text" id="total_price_'.$index.'" name="total_price[]" '.$readonly.' value="'.number_format($rowService['total_price'] - $rowService['discount_amount'], 2).'" style="width:80%" class="total_price float" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%;">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot.'img/button/move_down.png" class="btnMoveDownSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }
    
    function getProductFromOrder($id = null, $locationGroup = null, $salesId = 0){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id) && empty($locationGroup)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $allowLots    = false;
        $allowExpired = false;
        $priceDecimal = 2;
        $sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40) AND is_active = 1");
        while($rowSetting = mysql_fetch_array($sqlSetting)){
            if($rowSetting['id'] == 6){
                if($rowSetting['is_checked'] == 1){
                    $allowLots = true;
                }
            } else if($rowSetting['id'] == 7){
                if($rowSetting['is_checked'] == 1){
                    $allowExpired = true;
                }
            } else if($rowSetting['id'] == 40){
                $priceDecimal = $rowSetting['value'];
            }
        }
        $user = $this->getCurrentUser();
        $order = ClassRegistry::init('Order')->read(null, $id);
        $allowProductDiscount = $this->Helper->checkAccess($user['User']['id'], $this->params['controller'], 'discount');
        $allowEditPrice = $this->Helper->checkAccess($user['User']['id'], $this->params['controller'], 'editUnitPrice');
        // Check Permission Edit Price
        if($allowEditPrice){
            $readonly = '';
        }else{
            $readonly = 'readonly="readonly"';
        }
        $rowList = array();
        $rowLbl  = "";
        // Get Product
        $sqlQuoteDetail  = mysql_query("SELECT products.code AS code, products.barcode AS barcode, products.name AS name, products.unit_cost AS unit_cost, products.price_uom_id AS price_uom_id, products.small_val_uom AS small_val_uom, products.is_lots AS is_lots, order_details.product_id AS product_id, order_details.id AS order_detail_id, order_details.qty AS qty, order_details.qty_free AS qty_free, order_details.qty_uom_id AS qty_uom_id, order_details.conversion AS conversion, order_details.unit_price AS unit_price, order_details.total_price AS total_price, order_details.discount_id AS discount_id, order_details.discount_amount AS discount_amount, order_details.discount_percent AS discount_percent, orders.customer_id AS customer_id FROM order_details INNER JOIN orders ON orders.id = order_details.order_id INNER JOIN products ON products.id = order_details.product_id WHERE order_details.order_id = ".$id.";");
        while($rowDetail = mysql_fetch_array($sqlQuoteDetail)){
            $index   = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            $sqlProCus = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$rowDetail['product_id']." AND customer_id = ".$rowDetail['customer_id']." ORDER BY created DESC LIMIT 1");
            if(mysql_num_rows($sqlProCus)){
                $rowProCus   = mysql_fetch_array($sqlProCus);
                $productName = str_replace('"', '&quot;', $rowProCus['name']);
            }
            // Get Total Inventory
            $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$locationGroup}_group_totals WHERE product_id ={$rowDetail['product_id']} AND location_group_id =".$locationGroup." GROUP BY product_id");
            $rowInv = mysql_fetch_array($sqlInv);
            $totalQtySales = ($rowInv['total_qty']>0?$rowInv['total_qty']:0);
            $totalOrder    = ($rowDetail['qty'] * $rowDetail['conversion']);
            // Qty
            if($totalQtySales >= $totalOrder){
                $qty = $rowDetail['qty'];
                $highLight = '';
            }else{
                $qty = 0;
                $highLight = 'style="backgroud-color: red;"';
            }
            // Get UOM
            $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                ORDER BY conversion ASC");
            $i = 1;
            $length = mysql_num_rows($query);
            $optionUom = "";
            $conversionUsed = 1;
            $costSelected   = 0;
            while($data=mysql_fetch_array($query)){
                $priceLbl   = "";
                $costLbl    = "";
                $selected   = "";
                $isMain     = "other";
                $isSmall    = 0;
                $conversion = ($rowDetail['small_val_uom'] / $data['conversion']);
                // Check With Qty UOM Id
                if($data['id'] == $rowDetail['qty_uom_id']){   
                    $selected = ' selected="selected" ';
                    $conversionUsed = $conversion;
                }
                // Check With Product UOM Id
                if($data['id'] == $rowDetail['price_uom_id']){
                    $isMain = "first";
                }
                // Check Is Small UOM
                if($length == $i){
                    $isSmall = 1;
                }
                // Get Price
                $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$rowDetail['product_id']." AND product_prices.uom_id =".$data['id']);
                if(@mysql_num_rows($sqlPrice)){
                    $price = 0;
                    while($rowPrice = mysql_fetch_array($sqlPrice)){
                        $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                        if($rowPrice['set_type'] == 1){
                            $price = $rowPrice['amount'];
                        }else if($rowPrice['set_type'] == 2){
                            $percent = ($unitCost * $rowPrice['percent']) / 100;
                            $price = $unitCost + $percent;
                        }else if($rowPrice['set_type'] == 3){
                            $price = $unitCost + $rowPrice['add_on'];
                        }
                        $costLbl  .= 'cost-uom-'.$rowPrice['price_type_id'].'="'.$unitCost.'" ';
                        $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                        if($data['id'] == $rowDetail['qty_uom_id'] && $rowPrice['price_type_id'] == $order['Order']['price_type_id']){
                            $costSelected = $unitCost;
                        }
                    }
                }else{
                    $unitCost = ($rowDetail['unit_cost'] /  $data['conversion']);
                    $sqlPriceType = mysql_query("SELECT price_types.id FROM price_types INNER JOIN price_type_companies ON price_type_companies.price_type_id = price_types.id AND price_type_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE price_types.is_active = 1 GROUP BY price_types.id;");
                    while($rowPriceType = mysql_fetch_array($sqlPriceType)){
                        $costLbl  .= 'cost-uom-'.$rowPriceType[0].'="'.$unitCost.'"';
                        $priceLbl .= 'price-uom-'.$rowPriceType[0].'="0"';
                    }
                }
                $optionUom .= '<option '.$priceLbl.' '.$costLbl.' '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" value="'.$data['id'].'" conversion="'.$data['conversion'].'">'.$data['name'].'</option>';
                $i++;
            }
            // Calculate Total Price
            $totalPrice = ($qty * $rowDetail['unit_price']);
            if($totalPrice > 0){
                $totalAfterDis = ($totalPrice - $rowDetail['discount_amount']);
                $disId      = $rowDetail['discount_id'];
                $disAmt     = $rowDetail['discount_amount'];
                $disPercent = $rowDetail['discount_percent'];
            } else {
                $totalAfterDis = 0;
                $disId      = '';
                $disAmt     = 0;
                $disPercent = 0;
            }
            // Open Tr
            $rowLbl .= '<tr class="tblSOList" '.$highLight.'>';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center; padding: 0px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:8%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowDetail['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:16%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">';
            $rowLbl .= '<input type="hidden" class="totalQtySales" value="'.($totalQtySales).'" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderSales" value="'.$totalOrder.'" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" name="product_id[]" value="'.$rowDetail['product_id'].'" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" name="service_id[]" />';
            $rowLbl .= '<input type="hidden" name="discount_id[]" value="'.$disId.'" />';
            $rowLbl .= '<input type="hidden" name="discount_amount[]" value="'.$disAmt.'" />';
            $rowLbl .= '<input type="hidden" name="discount_percent[]" value="'.$disPercent.'" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" class="conversion" value="'.$rowDetail['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_uom_val[]" class="small_uom_val" value="'.$rowDetail['small_val_uom'].'" />';
            $rowLbl .= '<input type="hidden" name="note[]" id="note_'.$index.'" class="note" />';
            $rowLbl .= '<input type="hidden" name="promotional_id[]" class="promotional_id" />';
            $rowLbl .= '<input type="hidden" class="orgProName" value="PUC: '.$rowDetail['barcode'].'<br/><br/>SKU: '.$rowDetail['code'].'<br/><br/>Name: '.str_replace('"', '&quot;', $rowDetail['name']).'" />';
            $rowLbl .= '<input type="text" id="product_'.$index.'" value="'.$productName.'" name="product[]" class="product validate[required]" style="width: 75%; height: 25px;" />';
            $rowLbl .= '<input type="hidden" name="order_detail_id[]" value="'.$rowDetail['order_detail_id'].'" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '<img alt="Information" src="'.$this->webroot.'img/button/view.png" class="btnProductSaleInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Information\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lot Number
            $lotDispaly  = '';
            $lotHidden   = 'display: none;';
            $lotRequired = '';
            $lotsNumber  = '0';
            $expDisplay  = '';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($rowDetail['is_lots'] == 1){
                $lotHidden   = '';
                $lotRequired = 'validate[required]';
                $lotsNumber  = '';
            }
            if($allowExpired == false){
                $expDisplay = 'display: none;';
            }
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="lots_number'.$index.'" name="lots_number[]" style="width: 90%; height: 25px; '.$lotHidden.'" class="lots_number '.$lotRequired.'" value="'.$lotsNumber.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expired Date
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px; display: none;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="expired_date'.$index.'" name="expired_date[]" style="width: 90%; height: 25px;" class="expired_date '.$lotRequired.'" value="" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Input
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_'.$index.'" name="qty[]" value="'.$qty.'" style="width:60%; height: 25px;" class="floatQty qty" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_free_'.$index.'" name="qty_free[]" value="'.$rowDetail['qty_free'].'" style="width:60%; height: 25px;" class="floatQty qty_free" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $priceColor = 'color: red;';
            $msgStyle   = '';
            $unitPrice  = $rowDetail['unit_price'];
            if($unitPrice >= $costSelected){
                $priceColor = '';
                $msgStyle   = 'style="display: none;"';
            }
            $rowLbl .= '<td style="width: 9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="float unit_cost" name="unit_cost[]" value="'.number_format($costSelected, 2).'" />';
            $rowLbl .= '<input type="text" id="unit_price_'.$index.'" name="unit_price[]" '.$readonly.' value="'.number_format($rowDetail['unit_price'], 2).'" style="width:60%; height: 25px; '.$priceColor.'" class="float unit_price validate[required]" />';
            $rowLbl .= '<img alt="'.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'" src="'.$this->webroot.'img/button/down.png" '.$msgStyle.' class="priceDownOrder" align="absmiddle" onmouseover="Tip(\''.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                if($disId > 0){
                    $disPlay = '';
                }else{
                    $disPlay = 'display: none;';
                }
                $disDisplay  = '<input type="text" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.number_format($disAmt, 2).'" style="width: 60%; height: 25px;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; '.$disPlay.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.$disAmt.'" style="width: 60%; height: 25px;" readonly="readonly" />';
                $disDisplay .= number_format($disAmt, 2);
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="total_price_bf_dis_'.$index.'" name="total_price_bf_dis[]" value="'.number_format($totalPrice, 2).'" class="total_price_bf_dis float" />';
            $rowLbl .= '<input type="text" id="total_price_'.$index.'" name="total_price[]" '.$readonly.' value="'.number_format($totalAfterDis, 2).'" style="width:80%; height: 25px;" class="total_price float" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%;">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot.'img/button/move_down.png" class="btnMoveDownSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        // Get Service
        $sqlQuoteService  = mysql_query("SELECT services.code AS code, services.name AS name, uoms.abbr AS uom, uoms.id AS uom_id, order_services.service_id AS service_id, order_services.qty AS qty, order_services.qty_free AS qty_free, order_services.conversion AS conversion, order_services.unit_price AS unit_price, order_services.total_price AS total_price, order_services.discount_id AS discount_id, order_services.discount_amount AS discount_amount, order_services.discount_percent AS discount_percent FROM order_services INNER JOIN services ON services.id = order_services.service_id INNER JOIN uoms ON uoms.id = services.uom_id WHERE order_services.order_id = ".$id.";");
        while($rowService = mysql_fetch_array($sqlQuoteService)){
            $index   = rand();
            $qty = $rowService['qty'];
            // Get UOM
            $optionUom = '<option value="'.$rowService['uom_id'].'" conversion="1" selected="selected">'.$rowService['uom'].'</option>';
            // Open Tr
            $rowLbl .= '<tr class="tblSOList">';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center; padding: 0px;">'.++$index.'</td>';
            // SKU
            $rowLbl .= '<td style="width:8%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowService['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:16%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">';
            $rowLbl .= '<input type="hidden" class="totalQtySales" value="1" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderSales" value="1" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" name="product_id[]" value="" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" name="service_id[]" value="'.$rowService['service_id'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_id[]" value="'.$rowService['discount_id'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_amount[]" value="'.$rowService['discount_amount'].'" />';
            $rowLbl .= '<input type="hidden" name="discount_percent[]" value="'.$rowService['discount_percent'].'" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" class="conversion" value="'.$rowService['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_uom_val[]" class="small_uom_val" value="1" />';
            $rowLbl .= '<input type="hidden" name="note[]" id="note_'.$index.'" class="note" />';
            $rowLbl .= '<input type="hidden" name="promotional_id[]" class="promotional_id" />';
            $rowLbl .= '<input type="text" id="product_'.$index.'" readonly="readonly" value="'.$rowService['name'].'" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<input type="hidden" name="order_detail_id[]" value="" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lots Number
            $lotDispaly  = '';
            $expDisplay  = '';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($allowExpired == false){
                $expDisplay = 'display: none;';
            }
            $rowLbl .= '<td style="width:9%; text-align: center;padding: 0px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="lots_number[]" class="lots_number" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            $rowLbl .= '<td style="width:9%; text-align: center;padding: 0px; display: none;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" name="expired_date[]" class="expired_date" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Input
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_'.$index.'" name="qty[]" value="'.$qty.'" style="width:60%;" class="floatQty qty" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_free_'.$index.'" name="qty_free[]" value="'.$rowDetail['qty_free'].'" style="width:60%;" class="floatQty qty_free" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $rowLbl .= '<td style="width: 9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />';
            $rowLbl .= '<input type="text" id="unit_price_'.$index.'" name="unit_price[]" '.$readonly.' value="'.number_format($rowService['unit_price'], 2).'" style="width:60%;" class="float unit_price validate[required]" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                if($rowService['discount_id'] > 0){
                    $disPlay = '';
                }else{
                    $disPlay = 'display: none;';
                }
                $disDisplay  = '<input type="text" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.number_format($rowService['discount_amount'], 2).'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; '.$disPlay.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="'.$rowService['discount_amount'].'" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= number_format($rowService['discount_amount'], 2);
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="total_price_bf_dis_'.$index.'" name="total_price_bf_dis[]" value="'.number_format($rowService['total_price'], 2).'" class="total_price_bf_dis float" />';
            $rowLbl .= '<input type="text" id="total_price_'.$index.'" name="total_price[]" '.$readonly.' value="'.number_format($rowService['total_price'] - $rowService['discount_amount'], 2).'" style="width:80%" class="total_price float" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%;">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '&nbsp; <img alt="Up" src="'.$this->webroot.'img/button/move_up.png" class="btnMoveUpSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Up\')" />';
            $rowLbl .= '&nbsp; <img alt="Down" src="'.$this->webroot.'img/button/move_down.png" class="btnMoveDownSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Down\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }
    
    function searchQuotationCode($companyId = null, $branchId = null, $code = null, $saleId = 0){
        $this->layout = 'ajax';
        if (empty($code)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $result = array();
        $quotation = ClassRegistry::init('Quotation')->find('first', array(
                            'conditions' => array(
                                'Quotation.quotation_code' => $code,
                                'Quotation.company_id' => $companyId,
                                'Quotation.branch_id' => $branchId,
                                'Quotation.status' => 1,
                                'Quotation.is_close' => 0,
                            )
                          ));
       if(!empty($quotation)){
            $condition = "";
            $id = $quotation['Quotation']['customer_id'];
            // Condition For Edit
            if($saleId > 0){
                $condition = " AND id != ".$saleId;
            }
            $sqlCus   = mysql_query("SELECT IFNULL(limit_balance, 0) AS limit_balance, IFNULL(limit_total_invoice, 0) AS limit_total_invoice FROM customers WHERE id =".$id);
            $rowCus   = mysql_fetch_array($sqlCus);
            $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(total_amount) AS total_amount FROM sales_orders WHERE customer_id = ".$id." AND status > 0 AND balance > 0".$condition." GROUP BY customer_id");
            $limitInvoice = $rowCus['limit_total_invoice'];
            $limitBalance = $rowCus['limit_balance'];
            $totalInvUsed = 0;
            $totalAmoUsed = 0;
            // Check Limit Balance
            if(@mysql_num_rows($queryCon)){
                $dataCon  = mysql_fetch_array($queryCon);
                $totalInvUsed = $dataCon['total_invoice'];
                $totalAmoUsed = $dataCon['total_amount'];
            }
            $result['limit_invoice'] = $limitInvoice;
            $result['limit_balance'] = $limitBalance;
            $result['invoice_used']  = ($totalInvUsed + 1);
            $result['balance_used']  = $totalAmoUsed;
            $result['customer_id'] = $quotation['Customer']['id'];
            $result['customer_code'] = $quotation['Customer']['customer_code'];
            $result['customer_name'] = $quotation['Customer']['name'];
            $result['customer_name_kh'] = $quotation['Customer']['name_kh'];
            $result['customer_contact_id'] = $quotation['Quotation']['customer_contact_id'];
            $result['payment_term_id']  = $quotation['Customer']['payment_term_id'];
            $result['photo'] = $quotation['Customer']['photo'];
            $result['quotation_id'] = $quotation['Quotation']['id'];
            $result['quotation_code'] = $quotation['Quotation']['quotation_code'];
            $result['discount'] = $quotation['Quotation']['discount'];
            $result['discount_percent'] = $quotation['Quotation']['discount_percent'];
            $result['total_deposit'] = $quotation['Quotation']['total_deposit']!=''?$quotation['Quotation']['total_deposit']:0;
       }else{
            $result['error'] = 1;
       }
       echo json_encode($result);
       exit;
    }
    
    function searchQuotation(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = $userPermission = 'Quotation.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].') AND Quotation.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id ='.$user['User']['id'].')';
        $quotations = ClassRegistry::init('Quotation')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Quotation.quotation_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ), 
                            'Quotation.is_close' => 0,
                            'Quotation.status' => 1, $userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($quotations)) {
            foreach ($quotations as $quotation) {
                $sqlCus = mysql_query("SELECT * FROM customers WHERE id = ".$quotation['Quotation']['customer_id']);
                $rowCus = mysql_fetch_array($sqlCus);
                $limitBalance = $rowCus['limit_balance'];
                $limitInvoice = $rowCus['limit_total_invoice'];
                $totalInvUsed = 0;
                $totalAmoUsed = 0;
                $totalDeposit = $quotation['Quotation']['total_deposit']!=''?$quotation['Quotation']['total_deposit']:0;
                if($limitBalance > 0 || $limitInvoice > 0){
                    // Query Total Invoice & Total Amount Not Pay
                    $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(total_amount) AS total_amount FROM sales_orders WHERE customer_id = ".$record[1]." AND status > 0 AND balance > 0".$saleCon." GROUP BY customer_id");
                    $dataCon = mysql_fetch_array($queryCon);
                    // Check Limit Invoice
                    if(@mysql_num_rows($queryCon)){
                        $totalInvUsed = $dataCon['total_invoice'] + 1;
                        $totalAmoUsed = $dataCon['total_amount'];
                    }
                }
                echo "{$quotation['Quotation']['id']}.*{$quotation['Quotation']['quotation_code']}.*{$rowCus['id']}.*{$rowCus['customer_code']}.*{$rowCus['name_kh']}.*{$rowCus['name']}.*{$rowCus['customer_code']}.*{$rowCus['payment_term_id']}.*{$limitBalance}.*{$limitInvoice}.*{{$totalInvUsed}.*{$totalAmoUsed}.*{$quotation['Quotation']['discount']}.*{$quotation['Quotation']['discount_percent']}.*{$totalDeposit}.*{$quotation['Quotation']['customer_contact_id']}\n";
            }
        }
        exit;
    }
    
    function searchOrder(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Order.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].') AND Order.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id ='.$user['User']['id'].')';
        $orders = ClassRegistry::init('Order')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Order.order_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ), 
                            'Order.is_close' => 0,
                            'Order.status' => 1, $userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $sqlCus = mysql_query("SELECT * FROM customers WHERE id = ".$order['Order']['customer_id']);
                $rowCus = mysql_fetch_array($sqlCus);
                $limitBalance = $rowCus['limit_balance'];
                $limitInvoice = $rowCus['limit_total_invoice'];
                $totalInvUsed = 0;
                $totalAmoUsed = 0;
                if($limitBalance > 0 || $limitInvoice > 0){
                    // Query Total Invoice & Total Amount Not Pay
                    $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(total_amount) AS total_amount FROM sales_orders WHERE customer_id = ".$record[1]." AND status > 0 AND balance > 0".$saleCon." GROUP BY customer_id");
                    $dataCon = mysql_fetch_array($queryCon);
                    // Check Limit Invoice
                    if(@mysql_num_rows($queryCon)){
                        $totalInvUsed = $dataCon['total_invoice'] + 1;
                        $totalAmoUsed = $dataCon['total_amount'];
                    }
                }
                echo "{$order['Order']['id']}.*{$order['Order']['order_code']}.*{$rowCus['id']}.*{$rowCus['customer_code']}.*{$rowCus['name_kh']}.*{$rowCus['name']}.*{$rowCus['customer_code']}.*{$rowCus['payment_term_id']}.*{$limitBalance}.*{$limitInvoice}.*{$totalInvUsed}.*{$totalAmoUsed}.*{$order['Order']['customer_contact_id']}\n";
            }
        }
    }
    
    function searchOrderCode($companyId = null, $branchId = null, $code = null, $saleId = 0){
        $this->layout = 'ajax';
        if (empty($code)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $result = array();
        $orders = ClassRegistry::init('Order')->find('first', array(
                            'conditions' => array(
                                'Order.quotation_code' => $code,
                                'Order.company_id' => $companyId,
                                'Order.branch_id' => $branchId,
                                'Order.status' => 1,
                                'Order.is_close' => 0,
                            )
                          ));
       if(!empty($orders)){
            $condition = "";
            $id = $orders['Order']['customer_id'];
            // Condition For Edit
            if($saleId > 0){
                $condition = " AND id != ".$saleId;
            }
            $sqlCus   = mysql_query("SELECT IFNULL(limit_balance, 0) AS limit_balance, IFNULL(limit_total_invoice, 0) AS limit_total_invoice FROM customers WHERE id =".$id);
            $rowCus   = mysql_fetch_array($sqlCus);
            $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(total_amount) AS total_amount FROM sales_orders WHERE customer_id = ".$id." AND status > 0 AND balance > 0".$condition." GROUP BY customer_id");
            $limitInvoice = $rowCus['limit_total_invoice'];
            $limitBalance = $rowCus['limit_balance'];
            $totalInvUsed = 0;
            $totalAmoUsed = 0;
            // Check Limit Balance
            if(@mysql_num_rows($queryCon)){
                $dataCon  = mysql_fetch_array($queryCon);
                $totalInvUsed = $dataCon['total_invoice'];
                $totalAmoUsed = $dataCon['total_amount'];
            }
            $result['limit_invoice'] = $limitInvoice;
            $result['limit_balance'] = $limitBalance;
            $result['invoice_used']  = ($totalInvUsed + 1);
            $result['balance_used']  = $totalAmoUsed;
            $result['customer_id'] = $orders['Customer']['id'];
            $result['customer_code'] = $orders['Customer']['customer_code'];
            $result['customer_name'] = $orders['Customer']['name'];
            $result['customer_name_kh'] = $orders['Customer']['name_kh'];
            $result['customer_contact_id'] = $orders['Order']['customer_contact_id'];
            $result['payment_term_id']  = $orders['Customer']['payment_term_id'];
            $result['photo']    = $orders['Customer']['photo'];
            $result['order_id'] = $orders['Order']['id'];
            $result['order_code'] = $orders['Order']['order_code'];
       }else{
            $result['error'] = 1;
       }
       echo json_encode($result);
       exit;
    }
    
    function getCustomerContact($customerId){
        $this->layout = 'ajax';
        $result = '<option value="">'.INPUT_SELECT.'</option>';
        if (!empty($customerId)) {
            $customerContacts  = ClassRegistry::init('CustomerContact')->find("all", array('conditions' => array('CustomerContact.customer_id' => $customerId)));
            foreach($customerContacts AS $customerContact){
                $result .= '<option value="'.$customerContact['CustomerContact']['id'].'">'.$customerContact['CustomerContact']['contact_name'].'</option>';
            }
        }
        echo $result;
        exit;
    }
    
    function invoiceDiscount(){
        $this->layout = 'ajax';
    }
    
    function viewInvoiceNoDelivery(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 503 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 503;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function productHistory($productId = null, $customerId = null) {
        $this->layout = 'ajax';
        if (!empty($productId)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Order', 'View Product History', '');
            $product = ClassRegistry::init('Product')->find("first", array('conditions' => array('Product.id' => $productId)));
            $this->set(compact('product', 'customerId'));
        } else {
            exit;
        }
    }
    
    function productHistoryAjax($productId = null, $customerId = null) {
        $this->layout = 'ajax';
        $this->set(compact('productId', 'customerId'));
    }
    
    function getProductByExp($productId, $locationGroupId, $orderDate = 0){
        $this->layout = 'ajax';
        if(empty($productId) || empty($locationGroupId) || empty($orderDate)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('productId', 'orderDate', 'locationGroupId'));
    }

}

?>