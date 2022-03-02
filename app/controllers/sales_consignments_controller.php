<?php

class SalesConsignmentsController extends AppController {

    var $uses = 'SalesOrder';
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

    function ajax($customer = 'all', $filterStatus = 'all', $balance = 'all', $company = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('customer', 'filterStatus', 'balance', 'company', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->data = $this->SalesOrder->read(null, $id);
            if (!empty($this->data)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'View', $id);
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all",
                                array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all",
                                array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all",
                                array('conditions' => array('SalesOrderMisc.sales_order_id' => $id)));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all",
                                array('conditions' => array('SalesOrderReceipt.sales_order_id' => $id, 'SalesOrderReceipt.is_void' => 0)));
                $this->set(compact('salesOrderDetails', 'salesOrderServices', 'salesOrderMiscs', 'salesOrderReceipts'));
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
            $productOrder = array();
            // Group Product
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    if (array_key_exists($_POST['product_id'][$i], $productOrder)){
                        $productOrder[$_POST['product_id'][$i]]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$_POST['product_id'][$i]]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                }
            }
            // Check Qty in Stock Before Save
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty In Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty - total_order) FROM `".$this->data['SalesConsignment']['location_group_id']."_group_totals` WHERE product_id = ".$key." AND location_group_id = " . $this->data['SalesConsignment']['location_group_id']." AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$this->data['SalesConsignment']['location_group_id'].$locCon.")");
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
            if($checkErrorStock == 1){
                $result = array();
                $totalAmount = $this->data['SalesConsignment']['total_amount'] + $this->data['SalesConsignment']['total_vat'] - $this->data['SalesConsignment']['discount_us'];
                if($totalAmount < $this->data['total_deposit']){
                    // Error Save
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
                // Load Model
                $this->loadModel('SalesOrderTermCondition');
                $this->loadModel('SalesOrderDetail');
                $this->loadModel('StockOrder');
                $this->loadModel('SalesOrderMiscs');
                $this->loadModel('SalesOrderService');
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('InventoryValuation');
                $this->loadModel('AccountType');
                $this->loadModel('Company');

                // Chart Account
                $salesMiscAccount = $this->AccountType->findById(10);
                $salesDiscAccount = $this->AccountType->findById(11);
                
                $this->SalesOrder->create();
                $salesOrder = array();
                $salesOrder['SalesOrder']['so_code']           = $this->data['SalesConsignment']['so_code'];
                $salesOrder['SalesOrder']['company_id']        = $this->data['SalesConsignment']['company_id'];
                $salesOrder['SalesOrder']['branch_id']         = $this->data['SalesConsignment']['branch_id'];
                $salesOrder['SalesOrder']['location_group_id'] = $this->data['SalesConsignment']['location_group_id'];
                $salesOrder['SalesOrder']['customer_id']       = $this->data['SalesConsignment']['customer_id'];
                $salesOrder['SalesOrder']['customer_contact_id'] = $this->data['SalesConsignment']['customer_contact_id'];
                $salesOrder['SalesOrder']['currency_id']  = $this->data['SalesConsignment']['currency_id'];
                $salesOrder['SalesOrder']['ar_id']         = $this->data['SalesConsignment']['chart_account_id'];
                $salesOrder['SalesOrder']['sales_rep_id']  = $this->data['SalesConsignment']['sales_rep_id']!=''?$this->data['SalesConsignment']['sales_rep_id']:0;
                $salesOrder['SalesOrder']['collector_id']  = $this->data['SalesConsignment']['collector_id'];
                $salesOrder['SalesOrder']['consignment_id']        = $this->data['SalesConsignment']['consignment_id'];
                $salesOrder['SalesOrder']['consignment_code']      = $this->data['SalesConsignment']['consignment_code'];
                $salesOrder['SalesOrder']['customer_po_number']    = $this->data['SalesConsignment']['customer_po_number'];
                $salesOrder['SalesOrder']['vat_chart_account_id']  = $this->data['SalesConsignment']['vat_chart_account_id'];
                $salesOrder['SalesOrder']['vat_percent']  = $this->data['SalesConsignment']['vat_percent'];
                $salesOrder['SalesOrder']['total_vat']    = $this->data['SalesConsignment']['total_vat'];
                $salesOrder['SalesOrder']['vat_setting_id']  = $this->data['SalesConsignment']['vat_setting_id'];
                $salesOrder['SalesOrder']['vat_calculate']   = $this->data['SalesConsignment']['vat_calculate'];
                $salesOrder['SalesOrder']['total_amount'] = $this->data['SalesConsignment']['total_amount'];
                $salesOrder['SalesOrder']['discount']     = $this->data['SalesConsignment']['discount_us'];
                $salesOrder['SalesOrder']['discount_percent'] = $this->data['SalesConsignment']['discount_percent'];
                $salesOrder['SalesOrder']['balance']      = $totalAmount;
                $salesOrder['SalesOrder']['order_date']   = $this->data['SalesConsignment']['order_date'];
                $salesOrder['SalesOrder']['memo']         = $this->data['SalesConsignment']['memo'];
                $salesOrder['SalesOrder']['price_type_id'] = $this->data['SalesConsignment']['price_type_id'];
                $salesOrder['SalesOrder']['payment_term_id'] = $this->data['SalesConsignment']['payment_term_id'];
                $salesOrder['SalesOrder']['is_pos'] = 2;
                $salesOrder['SalesOrder']['is_approve'] = $this->data['SalesConsignment']['is_approve'];
                $salesOrder['SalesOrder']['created_by'] = $user['User']['id'];
                $salesOrder['SalesOrder']['is_deposit_reference'] = 0;
                // Check Total Deposit & QUOTE Applied
                $updateDpt = '';
                if($this->data['total_deposit'] > 0 && $this->data['SalesConsignment']['quotation_id'] > 0){
                    $updateDpt = ', total_deposit=(total_deposit+'.$this->data['total_deposit'].')';
                    $salesOrder['SalesOrder']['is_deposit_reference'] = 1;
                    $salesOrder['SalesOrder']['balance'] = $totalAmount - $this->data['total_deposit'];
                }
                // Check Approve
                if($this->data['SalesConsignment']['is_approve'] == 1){
                    $salesOrder['SalesOrder']['status'] = 1;
                }else{
                    $salesOrder['SalesOrder']['status'] = -2;
                }
                if ($this->SalesOrder->save($salesOrder)) {
                    $result['so_id'] = $saleOrderId = $this->SalesOrder->id;
                    $company         = $this->Company->read(null, $this->data['SalesConsignment']['company_id']);
                    $classId         = 0;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($this->data['SalesConsignment']['so_code'], $saleOrderId, 'so_code', 'sales_orders', 'status != -1 AND branch_id = '.$this->data['SalesConsignment']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE sales_orders SET so_code = '".$modCode."'".$updateDpt." WHERE id = ".$saleOrderId);
                    // Check Update QUOTE Applied is closed
                    if($this->data['total_deposit'] > 0 && $this->data['SalesConsignment']['quotation_id'] > 0){
                        mysql_query("UPDATE quotations SET is_close = 1 WHERE id = ".$this->data['SalesConsignment']['quotation_id']);
                    }
                    // Insert Term & Condition
                    if(!empty($_POST['term_condition_type_id'])){
                        for ($i = 0; $i < sizeof($_POST['term_condition_type_id']); $i++) {
                            if(!empty($_POST['term_condition_id'][$i])){
                                $termCondition = array();
                                // Term Condition
                                $this->SalesOrderTermCondition->create();
                                $termCondition['SalesOrderTermCondition']['sales_order_id'] = $saleOrderId;
                                $termCondition['SalesOrderTermCondition']['term_condition_type_id'] = $_POST['term_condition_type_id'][$i];
                                $termCondition['SalesOrderTermCondition']['term_condition_id'] = $_POST['term_condition_id'][$i];
                                $this->SalesOrderTermCondition->save($termCondition);
                            }
                        }
                    }
                    /* Create General Ledger */
                    $generalLedger = array();
                    $this->GeneralLedger->create();
                    $generalLedger['GeneralLedger']['sales_order_id'] = $saleOrderId;
                    $generalLedger['GeneralLedger']['date']       = $this->data['SalesConsignment']['order_date'];
                    $generalLedger['GeneralLedger']['reference']  = $modCode;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    // Check Approve
                    if($this->data['SalesConsignment']['is_approve'] == 1){
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
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['ar_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalAmount;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode;
                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    

                    /* General Ledger Detail Total Discount */
                    if ($this->data['SalesConsignment']['discount_us'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $this->data['SalesConsignment']['discount_us'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode . ' Discount';
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    }

                    /* General Ledger Detail Total VAT */
                    if (($salesOrder['SalesOrder']['total_vat']) > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['vat_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice VAT';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $salesOrder['SalesOrder']['total_vat'];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode;
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    }
                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            /* Sales Order Detail */
                            $salesOrderDetail = array();
                            $this->SalesOrderDetail->create();
                            $salesOrderDetail['SalesOrderDetail']['sales_order_id']   = $saleOrderId;
                            $salesOrderDetail['SalesOrderDetail']['discount_id']      = $_POST['discount_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['discount_amount']  = $_POST['discount'][$i];
                            $salesOrderDetail['SalesOrderDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                            $salesOrderDetail['SalesOrderDetail']['product_id']  = $_POST['product_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty']         = $_POST['qty'][$i];
                            $salesOrderDetail['SalesOrderDetail']['qty_free']    = $_POST['qty_free'][$i];
                            $salesOrderDetail['SalesOrderDetail']['unit_price']  = $_POST['unit_price'][$i];
                            $salesOrderDetail['SalesOrderDetail']['total_price'] = $_POST['total_price_bf_dis'][$i];
                            $salesOrderDetail['SalesOrderDetail']['conversion']  = $_POST['conversion'][$i];
                            $salesOrderDetail['SalesOrderDetail']['note']        = $_POST['note'][$i];
                            $this->SalesOrderDetail->save($salesOrderDetail);

                            $qtyOrder      = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_uom_val'][$i] / $_POST['conversion'][$i]);
                            $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['conversion'][$i];

                            /* Inventory Valuation */
                            if($this->data['calculate_cogs'] == 1){
                                 $inv_valutaion = array();
                                $this->InventoryValuation->create();
                                $inv_valutaion['InventoryValuation']['sales_order_id'] = $saleOrderId;
                                $inv_valutaion['InventoryValuation']['company_id'] = $this->data['SalesConsignment']['company_id'];
                                $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['SalesConsignment']['branch_id'];
                                $inv_valutaion['InventoryValuation']['type'] = "Invoice";
                                $inv_valutaion['InventoryValuation']['date'] = $this->data['SalesConsignment']['order_date'];
                                $inv_valutaion['InventoryValuation']['pid']  = $_POST['product_id'][$i];
                                $inv_valutaion['InventoryValuation']['small_qty'] = "-" . $qtyOrderSmall;
                                $inv_valutaion['InventoryValuation']['qty'] = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                                $inv_valutaion['InventoryValuation']['cost'] = null;
                                $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                                // Check Approve
                                if($this->data['SalesConsignment']['is_approve'] == 1){
                                    $inv_valutaion['InventoryValuation']['is_active'] = 1;
                                }else{
                                    $inv_valutaion['InventoryValuation']['is_active'] = 2;
                                }
                                $this->InventoryValuation->saveAll($inv_valutaion);
                                $inv_valutation_id = $this->InventoryValuation->getLastInsertId();
                            }else{
                                $inv_valutation_id = $this->SalesOrderDetail->id;
                            }
                            
                            if($locSetting['LocationSetting']['location_status'] == 1){
                                $locCon = ' AND is_for_sale = 1';
                            }
                            $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id'].$locCon.")";
                            $invInfos   = array();
                            $index      = 0;
                            $totalOrder = $qtyOrderSmall;
                            // Calculate Location, Lot, Expired Date
                            $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
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
                                $tmpDelivey['StockOrder']['sales_order_id'] = $saleOrderId;
                                $tmpDelivey['StockOrder']['product_id']    = $_POST['product_id'][$i];
                                $tmpDelivey['StockOrder']['location_group_id']   = $salesOrder['SalesOrder']['location_group_id'];
                                $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                $tmpDelivey['StockOrder']['date']  = $salesOrder['SalesOrder']['order_date'];
                                $tmpDelivey['StockOrder']['qty'] = $invInfo['total_qty'];
                                $this->StockOrder->save($tmpDelivey);
                                $this->Inventory->saveGroupQtyOrder($salesOrder['SalesOrder']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $salesOrder['SalesOrder']['order_date'], '+');
                            }

                            /* General Ledger Detail (Product Income) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                            $dataIncAccount  = mysql_fetch_array($queryIncAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);

                            /* General Ledger Detail (Product Discount) */
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i] . ' Discount';
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            }

                            /* General Ledger Detail (Inventory) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                            $dataInvAccount = mysql_fetch_array($queryInvAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $queryProductCodeName = mysql_query("SELECT CONCAT(code,' - ',name) FROM products WHERE id=" . $_POST['product_id'][$i]);
                            $dataProductCodeName  = mysql_fetch_array($queryProductCodeName);
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Inventory Adjustment for INV # ' . $modCode . ' ' . $dataProductCodeName[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);

                            /* General Ledger Detail (COGS) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                            $dataCogsAccount = mysql_fetch_array($queryCogsAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Inventory Adjustment for INV # ' . $modCode . ' ' . $dataProductCodeName[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);

                        } else if (!empty($_POST['service_id'][$i])) {
                            /* Sales Order Service */
                            $salesOrderService = array();
                            $this->SalesOrderService->create();
                            $salesOrderService['SalesOrderService']['sales_order_id']  = $saleOrderId;
                            $salesOrderService['SalesOrderService']['discount_id']      = $_POST['discount_id'][$i];
                            $salesOrderService['SalesOrderService']['discount_amount']  = $_POST['discount'][$i];
                            $salesOrderService['SalesOrderService']['discount_percent'] = $_POST['discount_percent'][$i];
                            $salesOrderService['SalesOrderService']['service_id']  = $_POST['service_id'][$i];
                            $salesOrderService['SalesOrderService']['qty']         = $_POST['qty'][$i];
                            $salesOrderService['SalesOrderService']['qty_free']    = $_POST['qty_free'][$i];
                            $salesOrderService['SalesOrderService']['unit_price']  = $_POST['unit_price'][$i];
                            $salesOrderService['SalesOrderService']['total_price'] = $_POST['total_price_bf_dis'][$i];
                            $salesOrderService['SalesOrderService']['note']        = $_POST['note'][$i];
                            $this->SalesOrderService->save($salesOrderService);

                            /* General Ledger Detail (Service) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                            $dataServiceAccount = mysql_fetch_array($queryServiceAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataServiceAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);

                            /* General Ledger Detail (Service Discount) */
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i] . ' Discount';
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            }
                        } else {
                            /* Sales Order Miscellaneous */
                            $this->SalesOrderMiscs->create();
                            $salesOrderMiscs['SalesOrderMiscs']['sales_order_id'] = $saleOrderId;
                            $salesOrderMiscs['SalesOrderMiscs']['discount_id']    = $_POST['discount_id'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['discount_amount']  = $_POST['discount'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['discount_percent'] = $_POST['discount_percent'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['description'] = $_POST['product'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['qty']         = $_POST['qty'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['qty_free']    = $_POST['qty_free'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['unit_price']  = $_POST['unit_price'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['total_price'] = $_POST['total_price_bf_dis'][$i];
                            $salesOrderMiscs['SalesOrderMiscs']['note'] = $_POST['note'][$i];
                            $this->SalesOrderMiscs->save($salesOrderMiscs);

                            /* General Ledger Detail (Misc) */
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesMiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);

                            /* General Ledger Detail (Misc Discount) */
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $modCode . ' ' . $_POST['product'][$i] . ' Discount';
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            }
                        }
                    }
                    // Recalculate Average Cost
                    $sqlTrack = mysql_query("SELECT val, is_recalculate FROM tracks WHERE id = 1");
                    $track    = mysql_fetch_array($sqlTrack);
                    $dateReca = $salesOrder['SalesOrder']['order_date'];
                    $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
                    if($track['val'] == "0000-00-00" || (strtotime($track['val']) >= strtotime($dateReca))){
                        mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New', $saleOrderId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New (Error)');
                    $result['code'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Add New (Error Out of Stock)');
                // Error Out of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = 3;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                            ),
                            'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $paymentTerms   = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('PaymentTerm.is_active=1')));
        $arAccount = ClassRegistry::init('AccountType')->findById(7);
        $arAccountId = $arAccount['AccountType']['chart_account_id'];
        $this->set(compact("paymentTerms","arAccountId","companies","branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact('uoms'));
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
            $this->loadModel('SalesOrderReceipt');
            $result = array();
            $salesOrder = array();
            $salesOrder['SalesOrder']['id'] = $this->data['SalesConsignment']['id'];
            $salesOrder['SalesOrder']['modified_by'] = $user['User']['id'];
            $salesOrder['SalesOrder']['balance'] = $this->data['SalesConsignment']['balance_us'];
            if ($this->SalesOrder->save($salesOrder)) {
                // Load Model
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('Company');
                $this->loadModel('AccountType');
                $salesOrder = $this->SalesOrder->findById($this->data['SalesConsignment']['id']);
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $salesOrder['SalesOrder']['branch_id'],
                                "ExchangeRate.currency_id" => $this->data['SalesConsignment']['currency_id']), "order" => array("ExchangeRate.created desc")));
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['SalesConsignment']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                    $totalDisOther  = ($this->data['SalesConsignment']['discount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                    $totalDisOther  = 0;
                }
                
                $totalPaidDebit = $this->data['SalesConsignment']['amount_us']   + $totalPaidOther ;
                $totalDis       = $this->data['SalesConsignment']['discount_us'] + $totalDisOther;
                $totalPaid      = $this->data['SalesConsignment']['amount_us']   + $totalPaidOther + $totalDis;
                
                // Get Default Chart Account Paid
                $cashBankAccount = $this->AccountType->findById(6);
                // Sales Order Receipt
                $this->SalesOrderReceipt->create();
                $salesOrderReceipt = array();
                $salesOrderReceipt['SalesOrderReceipt']['sales_order_id']     = $this->data['SalesConsignment']['id'];
                $salesOrderReceipt['SalesOrderReceipt']['branch_id']          = $salesOrder['SalesOrder']['branch_id'];
                $salesOrderReceipt['SalesOrderReceipt']['exchange_rate_id']   = $exchangeRateId;
                $salesOrderReceipt['SalesOrderReceipt']['currency_id'] = $this->data['SalesConsignment']['currency_id'];
                $salesOrderReceipt['SalesOrderReceipt']['chart_account_id'] = $this->data['SalesConsignment']['chart_account_id'];
                $salesOrderReceipt['SalesOrderReceipt']['receipt_code']     = '';
                $salesOrderReceipt['SalesOrderReceipt']['amount_us']    = $this->data['SalesConsignment']['amount_us'];
                $salesOrderReceipt['SalesOrderReceipt']['amount_other'] = $this->data['SalesConsignment']['amount_other'];
                $salesOrderReceipt['SalesOrderReceipt']['discount_us']    = $this->data['SalesConsignment']['discount_us'];
                $salesOrderReceipt['SalesOrderReceipt']['discount_other'] = $this->data['SalesConsignment']['discount_other'];
                $salesOrderReceipt['SalesOrderReceipt']['total_amount'] = $this->data['SalesConsignment']['total_amount'];
                $salesOrderReceipt['SalesOrderReceipt']['balance']      = $this->data['SalesConsignment']['balance_us'];
                $salesOrderReceipt['SalesOrderReceipt']['balance_other']      = $this->data['SalesConsignment']['balance_other'];
                $salesOrderReceipt['SalesOrderReceipt']['created_by']   = $user['User']['id'];
                $salesOrderReceipt['SalesOrderReceipt']['pay_date']     = $this->data['SalesConsignment']['pay_date']!=''?$this->data['SalesConsignment']['pay_date']:'0000-00-00';
                // Get Total Paid
                if ($this->data['SalesConsignment']['balance_us'] > 0) {
                    $salesOrderReceipt['SalesOrderReceipt']['due_date'] = $this->data['SalesConsignment']['aging']!=''?$this->data['SalesConsignment']['aging']:'0000-00-00';
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
                $company    = $this->Company->read(null, $salesOrder['SalesOrder']['location_group_id']);
                $classId    = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $salesOrder['SalesOrder']['location_group_id']);
                
                if (($this->data['SalesConsignment']['total_amount'] - $this->data['SalesConsignment']['balance_us']) > 0) {
                    // Save General Ledger Detail
                    $this->GeneralLedger->create();
                    $generalLedger = array();
                    $generalLedger['GeneralLedger']['sales_order_id']         = $salesOrder['SalesOrder']['id'];
                    $generalLedger['GeneralLedger']['sales_order_receipt_id'] = $result['sr_id'];
                    $generalLedger['GeneralLedger']['date']       = $this->data['SalesConsignment']['pay_date']!=''?$this->data['SalesConsignment']['pay_date']:'0000-00-00';
                    $generalLedger['GeneralLedger']['reference']  = $modCode;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    if ($this->GeneralLedger->save($generalLedger)) {
                        // Chart Account Payment
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $result['sr_id'];
                        if ($this->data['SalesConsignment']['chart_account_id'] == '') {
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $cashBankAccount['AccountType']['chart_account_id'];
                        } else {
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['SalesConsignment']['chart_account_id'];
                        }
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Receive Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalPaidDebit;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Payment for Invoice # ' . $salesOrder['SalesOrder']['so_code'];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        // A/P
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $result['sr_id'];
                        $queryAr = mysql_query("SELECT ar_id FROM sales_orders WHERE id=" . $salesOrder['SalesOrder']['id']);
                        $dataAr = mysql_fetch_array($queryAr);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataAr[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Receive Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Payment for Invoice # ' . $salesOrder['SalesOrder']['so_code'];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        
                        /* General Ledger Detail Total Discount */
                        if ($totalDis > 0) {
                            //Chart Account Discount
                            $paidDiscAccount = $this->AccountType->findById(11);
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $result['sr_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $paidDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Receive Payment';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalDis;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Payment for Invoice Discount # ' . $salesOrder['SalesOrder']['so_code'];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice Receipt', 'Save Add New', $result['sr_id']);
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
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all",
                                array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all",
                                array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all",
                                array('conditions' => array('SalesOrderMisc.sales_order_id' => $id)));
                $salesOrderReceipts = ClassRegistry::init('SalesOrderReceipt')->find("all",
                                array('conditions' => array('SalesOrderReceipt.sales_order_id' => $id, 'SalesOrderReceipt.is_void' => 0)));
                $salesOrderWithCms = ClassRegistry::init('CreditMemoWithSale')->find("all",
                                array('conditions' => array('CreditMemoWithSale.sales_order_id' => $id, 'CreditMemoWithSale.status' => 1)));

                $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
                $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];

                $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderMiscs', 'salesOrderReceipts', 'salesOrderServices', 'cashBankAccountId', 'salesOrderWithCms'));
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
        $sections = ClassRegistry::init('Section')->find("list", array("conditions" => array("Section.is_active = 1")));
        $services = $this->serviceCombo($companyId, $branchId);
        $this->set(compact('sections', 'services'));
    }

    function serviceCombo($companyId, $branchId) {
        $array = array();
        $services = ClassRegistry::init('Service')->find("all", array("conditions" => array("Service.company_id=" . $companyId . " AND Service.branch_id=" . $branchId . " AND Service.is_active = 1")));
        foreach ($services as $service) {
            $uomId = $service['Service']['uom_id']!=''?$service['Service']['uom_id']:'';
            array_push($array, array('value' => $service['Service']['id'], 'name' => $service['Service']['code']." - ".$service['Service']['name'], 'class' => $service['Section']['id'], 'abbr' => $service['Service']['name'], 'price' => $service['Service']['unit_price'], 'scode' => $service['Service']['code'], 'suom' => $uomId));
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
        if($sales_order['SalesOrder']['status'] == 1){
            $queryHasReceipt    = mysql_query("SELECT id FROM sales_order_receipts WHERE sales_order_id=" . $id . " AND is_void = 0");
            $queryHasReturn     = mysql_query("SELECT id FROM credit_memos WHERE status > 0 AND sales_order_id=" . $id);
            $queryHasSalesOrder = mysql_query("SELECT id FROM credit_memo_with_sales WHERE status > 0 AND sales_order_id=" . $id);
            // Get Total Deposit From GL
            $sqlGL = mysql_query("SELECT SUM(IFNULL(total_deposit,0)) FROM general_ledgers WHERE apply_to_id = ".$id." AND deposit_type = 5 AND is_active != 2");
            $rowGL = mysql_fetch_array($sqlGL);
            if((@mysql_num_rows($queryHasReturn) || @mysql_num_rows($queryHasSalesOrder) || @mysql_num_rows($queryHasReceipt)) || $sales_order['SalesOrder']['status'] != 1 || $rowGL[0] > 0){
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void (Error has transaction with other modules)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
            $this->loadModel('InventoryValuation');
            $this->loadModel('GeneralLedger');
            if (!isset($sales_order['SalesOrder']['order_date']) || is_null($sales_order['SalesOrder']['order_date']) || $sales_order['SalesOrder']['order_date'] == '0000-00-00' || $sales_order['SalesOrder']['order_date'] == '') {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Void (Error order date)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
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
            // Reset Stock Order
            $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$id.";");
            while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
            }
            // Detele Tmp Stock Order
            mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$id.";");
            
            // Recalculate Average Cost
            $sqlTrack = mysql_query("SELECT val FROM tracks WHERE id = 1");
            $track    = mysql_fetch_array($sqlTrack);
            $dateReca = $sales_order['SalesOrder']['order_date'];
            $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
            if($track[0] == "0000-00-00" || (strtotime($track[0]) >= strtotime($dateReca))){
                mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
            }
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
        $receipt = ClassRegistry::init('SalesOrderReceipt')->find("first",
                        array('conditions' => array('SalesOrderReceipt.id' => $id, 'SalesOrderReceipt.is_void' => 0)));
        $user = $this->getCurrentUser();
        if(!empty($receipt) && @$receipt['SalesOrderReceipt']['is_void'] == 0){
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
            $checkErrorStock = 1;
            $listOutStock    = "";
            $productOrder    = array();
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
            // Group Product
            for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                if($_POST['product_id'][$i] != ""){
                    if (array_key_exists($_POST['product_id'][$i], $productOrder)){
                        $productOrder[$_POST['product_id'][$i]]['qty'] += $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    } else {
                        $productOrder[$_POST['product_id'][$i]]['qty'] = $this->Helper->replaceThousand($_POST['qty'][$i] * $_POST['conversion'][$i]);
                    }
                }
            }
            // Check Qty in Stock Before Save
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            foreach($productOrder AS $key => $order){
                // Get Total Qty in Stock
                $totalStockAv = 0;
                $sqlInv = mysql_query("SELECT SUM(total_qty) FROM `".$this->data['SalesConsignment']['location_group_id']."_group_totals` WHERE product_id = ".$key." AND location_id IN (SELECT id FROM locations WHERE location_group_id = " . $this->data['SalesConsignment']['location_group_id'].$locCon.")");
                while($rInv=mysql_fetch_array($sqlInv)){
                    $totalStockAv = $rInv[0];
                }
                // Get Total Qty in Order
                $totalOrder = 0;
                $sqlOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.sales_order_id = ".$id." AND sor.product_id = ".$key." AND sor.location_group_id = ".$this->data['SalesConsignment']['location_group_id']." AND date = '".$this->data['SalesConsignment']['order_date']."' GROUP BY sor.product_id");
                while(@$rOrder=mysql_fetch_array($sqlOrder)){
                    $totalOrder = $rOrder['total_order'];
                }
                $totalSale       = $totalStockAv + $totalOrder;
                $qtyOrder        = $order['qty'];
                $qtyStockCompare = $totalSale;
                if($qtyOrder > $qtyStockCompare){
                    $checkErrorStock = 2; 
                    $listOutStock .= $key."|".$totalSale."-";
                }
            }
            if($checkErrorStock == 1){
                $sales_order = $this->SalesOrder->read(null, $id);
                if ($sales_order['SalesOrder']['status'] == 1) {
                    $result = array();
                    $totalAmount = $this->data['SalesConsignment']['total_amount'] + $this->data['SalesConsignment']['total_vat'] - $this->data['SalesConsignment']['discount_us'];
                    $totalDept   = 0;
                    $statuEdit = "-1";
                    if($this->data['SalesConsignment']['company_id'] != $sales_order['SalesOrder']['company_id']){
                        $statuEdit = 0;
                        $totalDept = $this->data['total_deposit'];
                    } else {
                        // Get Total Deposit From GL
                        $sqlGL = mysql_query("SELECT SUM(IFNULL(total_deposit,0)) FROM general_ledgers WHERE apply_to_id = ".$sales_order['SalesOrder']['id']." AND deposit_type = 5 AND is_active != 2");
                        $rowGL = mysql_fetch_array($sqlGL);
                        $totalDept = $rowGL[0];
                        // Check Total Amount With Deposit Applied
                        if($totalAmount < $totalDept){
                            // Error Save
                            $result['code'] = 2;
                            echo json_encode($result);
                            exit;
                        }
                    }
                    // Load Model
                    $this->loadModel('SalesOrderTermCondition');
                    $this->loadModel('InventoryValuation');
                    $this->loadModel('StockOrder');
                    $this->loadModel('SalesOrderDetail');
                    $this->loadModel('SalesOrderMiscs');
                    $this->loadModel('SalesOrderService');
                    $this->loadModel('GeneralLedger');
                    $this->loadModel('GeneralLedgerDetail');
                    $this->loadModel('AccountType');
                    $this->loadModel('Company');

                    // Chart Account
                    $salesMiscAccount = $this->AccountType->findById(10);
                    $salesDiscAccount = $this->AccountType->findById(11);
                    // Reset Stock Order
                    $sqlResetOrder = mysql_query("SELECT * FROM stock_orders WHERE `sales_order_id`=".$id.";");
                    while($rowResetOrder = mysql_fetch_array($sqlResetOrder)){
                        $this->Inventory->saveGroupQtyOrder($rowResetOrder['location_group_id'], $rowResetOrder['location_id'], $rowResetOrder['product_id'], $rowResetOrder['lots_number'], $rowResetOrder['expired_date'], $rowResetOrder['qty'], $rowResetOrder['date'], '-');
                    }
                    // Detele Tmp Stock Order
                    mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`=".$id.";");
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
                    
                    $this->SalesOrder->create();
                    $salesOrder = array();
                    $salesOrder['SalesOrder']['so_code']           = $sales_order['SalesOrder']['so_code'];
                    $salesOrder['SalesOrder']['company_id']        = $this->data['SalesConsignment']['company_id'];
                    $salesOrder['SalesOrder']['branch_id']         = $this->data['SalesConsignment']['branch_id'];
                    $salesOrder['SalesOrder']['location_group_id'] = $this->data['SalesConsignment']['location_group_id'];
                    $salesOrder['SalesOrder']['customer_id']       = $this->data['SalesConsignment']['customer_id'];
                    $salesOrder['SalesOrder']['customer_contact_id'] = $this->data['SalesConsignment']['customer_contact_id'];
                    $salesOrder['SalesOrder']['currency_id']  = $this->data['SalesConsignment']['currency_id'];
                    $salesOrder['SalesOrder']['ar_id']               = $this->data['SalesConsignment']['chart_account_id'];
                    $salesOrder['SalesOrder']['sales_rep_id']        = $this->data['SalesConsignment']['sales_rep_id']!=''?$this->data['SalesConsignment']['sales_rep_id']:0;
                    $salesOrder['SalesOrder']['collector_id']        = $this->data['SalesConsignment']['collector_id'];
                    $salesOrder['SalesOrder']['consignment_id']      = $this->data['SalesConsignment']['consignment_id'];
                    $salesOrder['SalesOrder']['consignment_code']    = $this->data['SalesConsignment']['consignment_code'];
                    $salesOrder['SalesOrder']['customer_po_number']  = $this->data['SalesConsignment']['customer_po_number'];
                    $salesOrder['SalesOrder']['vat_chart_account_id']  = $this->data['SalesConsignment']['vat_chart_account_id'];
                    $salesOrder['SalesOrder']['vat_percent']     = $this->data['SalesConsignment']['vat_percent'];
                    $salesOrder['SalesOrder']['total_vat']       = $this->data['SalesConsignment']['total_vat'];
                    $salesOrder['SalesOrder']['vat_setting_id']  = $this->data['SalesConsignment']['vat_setting_id'];
                    $salesOrder['SalesOrder']['vat_calculate']   = $this->data['SalesConsignment']['vat_calculate'];
                    $salesOrder['SalesOrder']['total_amount']    = $this->data['SalesConsignment']['total_amount'];
                    $salesOrder['SalesOrder']['discount']        = $this->data['SalesConsignment']['discount_us'];
                    $salesOrder['SalesOrder']['discount_percent'] = $this->data['SalesConsignment']['discount_percent'];
                    $salesOrder['SalesOrder']['balance']       = $totalAmount - $totalDept;
                    $salesOrder['SalesOrder']['total_deposit'] = $totalDept;
                    $salesOrder['SalesOrder']['order_date']    = $this->data['SalesConsignment']['order_date'];
                    $salesOrder['SalesOrder']['memo']          = $this->data['SalesConsignment']['memo'];
                    $salesOrder['SalesOrder']['price_type_id'] = $this->data['SalesConsignment']['price_type_id'];
                    $salesOrder['SalesOrder']['payment_term_id'] = $this->data['SalesConsignment']['payment_term_id'];
                    $salesOrder['SalesOrder']['is_pos']        = 2;
                    $salesOrder['SalesOrder']['is_approve']  = $this->data['SalesConsignment']['is_approve'];
                    $salesOrder['SalesOrder']['edited']      = date("Y-m-d H:i:s");
                    $salesOrder['SalesOrder']['edited_by']   = $user['User']['id'];
                    $salesOrder['SalesOrder']['created']     = $sales_order['SalesOrder']['created'];
                    $salesOrder['SalesOrder']['created_by']  = $sales_order['SalesOrder']['created_by'];
                    $salesOrder['SalesOrder']['is_deposit_reference'] = 0;
                    // Check Total Deposit & QUOTE Applied
                    if($this->data['total_deposit'] > 0 && $this->data['SalesConsignment']['quotation_id'] > 0){
                        $salesOrder['SalesOrder']['is_deposit_reference'] = 1;
                    }
                    // Check Approve
                    if($this->data['SalesConsignment']['is_approve'] == 1){
                        $salesOrder['SalesOrder']['status'] = 1;
                    }else{
                        $salesOrder['SalesOrder']['status'] = -2;
                    }
                    if ($this->SalesOrder->save($salesOrder)) {
                        $result['so_id'] = $saleOrderId = $this->SalesOrder->id;
                        $classId         = 0;
                        $glReference     = $sales_order['SalesOrder']['so_code'];
                        if($this->data['SalesConsignment']['branch_id'] != $sales_order['SalesOrder']['branch_id']){
                            // Get Module Code
                            $modCode = $this->Helper->getModuleCode($this->data['SalesConsignment']['so_code'], $saleOrderId, 'so_code', 'sales_orders', 'status != -1 AND branch_id = '.$this->data['SalesConsignment']['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE sales_orders SET so_code = '".$modCode."' WHERE id = ".$saleOrderId);
                            $glReference = $modCode;
                            // Update Changing SALE INVOICE ID (NULL) on Deposit GL
                            mysql_query("UPDATE `general_ledgers` SET apply_to_id = NULL WHERE apply_to_id = ".$sales_order['SalesOrder']['id']." AND deposit_type = 5 AND is_active = 1");
                        } else {
                            // Update Changing SALE INVOICE ID on Deposit GL
                            mysql_query("UPDATE `general_ledgers` SET apply_to_id = ".$saleOrderId." WHERE apply_to_id = ".$sales_order['SalesOrder']['id']." AND deposit_type = 5 AND is_active = 1");
                        }
                        // Insert Term & Condition
                        if(!empty($_POST['term_condition_type_id'])){
                            for ($i = 0; $i < sizeof($_POST['term_condition_type_id']); $i++) {
                                if(!empty($_POST['term_condition_id'][$i])){
                                    $termCondition = array();
                                    // Term Condition
                                    $this->SalesOrderTermCondition->create();
                                    $termCondition['SalesOrderTermCondition']['sales_order_id'] = $saleOrderId;
                                    $termCondition['SalesOrderTermCondition']['term_condition_type_id'] = $_POST['term_condition_type_id'][$i];
                                    $termCondition['SalesOrderTermCondition']['term_condition_id'] = $_POST['term_condition_id'][$i];
                                    $this->SalesOrderTermCondition->save($termCondition);
                                }
                            }
                        }
                        /* Create General Ledger */
                        $generalLedger = array();
                        $this->GeneralLedger->create();
                        $generalLedger['GeneralLedger']['sales_order_id'] = $saleOrderId;
                        $generalLedger['GeneralLedger']['date']       = $this->data['SalesConsignment']['order_date'];
                        $generalLedger['GeneralLedger']['reference']  = $glReference;
                        $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                        $generalLedger['GeneralLedger']['is_sys'] = 1;
                        $generalLedger['GeneralLedger']['is_adj'] = 0;
                        // Check Approve
                        if($this->data['SalesConsignment']['is_approve'] == 1){
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
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['ar_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalAmount;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference;
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        

                        /* General Ledger Detail Total Discount */
                        if ($this->data['SalesConsignment']['discount_us'] > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $this->data['SalesConsignment']['discount_us'];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference . ' Discount';
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }

                        /* General Ledger Detail Total VAT */
                        if (($salesOrder['SalesOrder']['total_vat']) > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['vat_chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice VAT';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $salesOrder['SalesOrder']['total_vat'];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference;
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }
                        for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                            if (!empty($_POST['product_id'][$i])) {
                                /* Sales Order Detail */
                                $salesOrderDetail = array();
                                $this->SalesOrderDetail->create();
                                $salesOrderDetail['SalesOrderDetail']['sales_order_id']   = $saleOrderId;
                                $salesOrderDetail['SalesOrderDetail']['discount_id']      = $_POST['discount_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['discount_amount']  = $_POST['discount'][$i];
                                $salesOrderDetail['SalesOrderDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                                $salesOrderDetail['SalesOrderDetail']['product_id']  = $_POST['product_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty']         = $_POST['qty'][$i];
                                $salesOrderDetail['SalesOrderDetail']['qty_free']    = $_POST['qty_free'][$i];
                                $salesOrderDetail['SalesOrderDetail']['unit_price']  = $_POST['unit_price'][$i];
                                $salesOrderDetail['SalesOrderDetail']['total_price'] = $_POST['total_price_bf_dis'][$i];
                                $salesOrderDetail['SalesOrderDetail']['conversion']  = $_POST['conversion'][$i];
                                $salesOrderDetail['SalesOrderDetail']['note']        = $_POST['note'][$i];
                                $this->SalesOrderDetail->save($salesOrderDetail);
                                $qtyOrder      = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_uom_val'][$i] / $_POST['conversion'][$i]);
                                $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['conversion'][$i];

                                /* Inventory Valuation */
                                if($this->data['calculate_cogs'] == 1){
                                     $inv_valutaion = array();
                                    $this->InventoryValuation->create();
                                    $inv_valutaion['InventoryValuation']['sales_order_id'] = $saleOrderId;
                                    $inv_valutaion['InventoryValuation']['company_id'] = $this->data['SalesConsignment']['company_id'];
                                    $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['SalesConsignment']['branch_id'];
                                    $inv_valutaion['InventoryValuation']['type'] = "Invoice";
                                    $inv_valutaion['InventoryValuation']['date'] = $this->data['SalesConsignment']['order_date'];
                                    $inv_valutaion['InventoryValuation']['pid'] = $_POST['product_id'][$i];
                                    $inv_valutaion['InventoryValuation']['small_qty'] = "-" . $qtyOrderSmall;
                                    $inv_valutaion['InventoryValuation']['qty'] = "-" . $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                                    $inv_valutaion['InventoryValuation']['cost'] = null;
                                    $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                                    // Check Approve
                                    if($this->data['SalesConsignment']['is_approve'] == 1){
                                        $inv_valutaion['InventoryValuation']['is_active'] = 1;
                                    }else{
                                        $inv_valutaion['InventoryValuation']['is_active'] = 2;
                                    }
                                    $this->InventoryValuation->saveAll($inv_valutaion);
                                    $inv_valutation_id = $this->InventoryValuation->getLastInsertId();
                                }else{
                                    $inv_valutation_id = $this->SalesOrderDetail->id;
                                }

                                if($locSetting['LocationSetting']['location_status'] == 1){
                                    $locCon = ' AND is_for_sale = 1';
                                }
                                $conLocInv  = "IN (SELECT id FROM locations WHERE location_group_id = ".$salesOrder['SalesOrder']['location_group_id'].$locCon.")";
                                $invInfos   = array();
                                $index      = 0;
                                $totalOrder = $qtyOrderSmall;
                                // Calculate Location, Lot, Expired Date
                                $sqlInventory = mysql_query("SELECT SUM(IFNULL(group_totals.total_qty,0) - IFNULL(group_totals.total_order,0)) AS total_qty, group_totals.location_id AS location_id, group_totals.lots_number AS lots_number, group_totals.expired_date AS expired_date FROM ".$salesOrder['SalesOrder']['location_group_id']."_group_totals AS group_totals WHERE group_totals.location_id ".$conLocInv." AND group_totals.product_id = ".$_POST['product_id'][$i]." GROUP BY group_totals.location_id, group_totals.product_id, group_totals.lots_number, group_totals.expired_date HAVING total_qty > 0 ORDER BY group_totals.expired_date, group_totals.lots_number, group_totals.total_qty ASC");
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
                                // Update Inventory
                                foreach($invInfos AS $invInfo){
                                    //Insert Into Tmp Sales
                                    $tmpDelivey = array();
                                    $this->StockOrder->create();
                                    $tmpDelivey['StockOrder']['sales_order_id'] = $saleOrderId;
                                    $tmpDelivey['StockOrder']['product_id']     = $_POST['product_id'][$i];
                                    $tmpDelivey['StockOrder']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                    $tmpDelivey['StockOrder']['location_id']   = $invInfo['location_id'];
                                    $tmpDelivey['StockOrder']['lots_number']   = $invInfo['lots_number'];
                                    $tmpDelivey['StockOrder']['expired_date']  = $invInfo['expired_date'];
                                    $tmpDelivey['StockOrder']['date']  = $salesOrder['SalesOrder']['order_date'];
                                    $tmpDelivey['StockOrder']['qty'] = $invInfo['total_qty'];
                                    $this->StockOrder->save($tmpDelivey);
                                    $this->Inventory->saveGroupQtyOrder($salesOrder['SalesOrder']['location_group_id'], $invInfo['location_id'], $_POST['product_id'][$i], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['total_qty'], $salesOrder['SalesOrder']['order_date'], '+');
                                }

                                /* General Ledger Detail (Product Income) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                                $dataIncAccount  = mysql_fetch_array($queryIncAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);

                                /* General Ledger Detail (Product Discount) */
                                if ($_POST['discount'][$i] > 0) {
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail = array();
                                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['discount'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i] . ' Discount';
                                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                }

                                /* General Ledger Detail (Inventory) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                                $dataInvAccount = mysql_fetch_array($queryInvAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $queryProductCodeName = mysql_query("SELECT CONCAT(code,' - ',name) FROM products WHERE id=" . $_POST['product_id'][$i]);
                                $dataProductCodeName  = mysql_fetch_array($queryProductCodeName);
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Inventory Adjustment for INV # ' . $glReference . ' ' . $dataProductCodeName[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);

                                /* General Ledger Detail (COGS) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = ".$_POST['product_id'][$i]." AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$_POST['product_id'][$i]." ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                                $dataCogsAccount = mysql_fetch_array($queryCogsAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $_POST['product_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                                $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: Inventory Adjustment for INV # ' . $glReference . ' ' . $dataProductCodeName[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);

                            } else if (!empty($_POST['service_id'][$i])) {
                                /* Sales Order Service */
                                $salesOrderService = array();
                                $this->SalesOrderService->create();
                                $salesOrderService['SalesOrderService']['sales_order_id']  = $saleOrderId;
                                $salesOrderService['SalesOrderService']['discount_id']      = $_POST['discount_id'][$i];
                                $salesOrderService['SalesOrderService']['discount_amount']  = $_POST['discount'][$i];
                                $salesOrderService['SalesOrderService']['discount_percent'] = $_POST['discount_percent'][$i];
                                $salesOrderService['SalesOrderService']['service_id']  = $_POST['service_id'][$i];
                                $salesOrderService['SalesOrderService']['qty']         = $_POST['qty'][$i];
                                $salesOrderService['SalesOrderService']['qty_free']    = $_POST['qty_free'][$i];
                                $salesOrderService['SalesOrderService']['unit_price']  = $_POST['unit_price'][$i];
                                $salesOrderService['SalesOrderService']['total_price'] = $_POST['total_price_bf_dis'][$i];
                                $salesOrderService['SalesOrderService']['note']        = $_POST['note'][$i];
                                $this->SalesOrderService->save($salesOrderService);

                                /* General Ledger Detail (Service) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                               $queryServiceAccount = mysql_query("SELECT IFNULL((SELECT chart_account_id FROM services WHERE id=" . $_POST['service_id'][$i] . "),(SELECT chart_account_id FROM account_types WHERE id=9))");
                                $dataServiceAccount = mysql_fetch_array($queryServiceAccount);
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataServiceAccount[0];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);

                                /* General Ledger Detail (Service Discount) */
                                if ($_POST['discount'][$i] > 0) {
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail = array();
                                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Invoice';
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['discount'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i] . ' Discount';
                                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                }
                            } else {
                                /* Sales Order Miscellaneous */
                                $this->SalesOrderMiscs->create();
                                $salesOrderMiscs['SalesOrderMiscs']['sales_order_id'] = $saleOrderId;
                                $salesOrderMiscs['SalesOrderMiscs']['discount_id']    = $_POST['discount_id'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['discount_amount']  = $_POST['discount'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['discount_percent'] = $_POST['discount_percent'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['description'] = $_POST['product'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['qty_uom_id']  = $_POST['qty_uom_id'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['qty']         = $_POST['qty'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['qty_free']    = $_POST['qty_free'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['unit_price']  = $_POST['unit_price'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['total_price'] = $_POST['total_price_bf_dis'][$i];
                                $salesOrderMiscs['SalesOrderMiscs']['note'] = $_POST['note'][$i];
                                $this->SalesOrderMiscs->save($salesOrderMiscs);

                                /* General Ledger Detail (Misc) */
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesMiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['total_price_bf_dis'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);

                                /* General Ledger Detail (Misc Discount) */
                                if ($_POST['discount'][$i] > 0) {
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail = array();
                                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id']  = $salesOrder['SalesOrder']['location_group_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Invoice';
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['discount'][$i];
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = 'ICS: INV # ' . $glReference . ' ' . $_POST['product'][$i] . ' Discount';
                                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                }
                            }
                        }
                        // Recalculate Average Cost
                        $sqlTrack = mysql_query("SELECT val, is_recalculate FROM tracks WHERE id = 1");
                        $track    = mysql_fetch_array($sqlTrack);
                        $dateReca = $salesOrder['SalesOrder']['order_date'];
                        $dateReca = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dateReca)) . " -1 day"));
                        if($track['val'] == "0000-00-00" || (strtotime($track['val']) >= strtotime($dateReca))){
                            mysql_query("UPDATE tracks SET val='".$dateReca."', is_recalculate = 1 WHERE id=1");
                        }
                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit', $id, $saleOrderId);
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error)', $id);
                        // Error Saves
                        $result['error'] = 2;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    // Error Saves
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error Status)', $id);
                    $result['error'] = 2;
                    echo json_encode($result);
                    exit;
                }
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Save Edit (Error Out of Stock)', $id);
                // Error Out Of Stock
                $result['listOutStock'] = $listOutStock;
                $result['code'] = '3';
                echo json_encode($result);
                exit;
            }
        }

        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Invoice', 'Edit', $id);
            $this->data = $this->SalesOrder->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                            ),
                            'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        ));
            $branches = ClassRegistry::init('Branch')->find('all',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Currency.symbol'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $paymentTerms   = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('PaymentTerm.is_active=1')));
            $arAccountId = $this->data['SalesOrder']['ar_id'];
            $this->set(compact("paymentTerms","companies","arAccountId","branches"));
        }else{
            exit;
        }
    }
    
    function editDetail($salesOrderId = null) {
        $this->layout = 'ajax';
        if ($salesOrderId >= 0) {
            $salesOrder         = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $salesOrderId)));
            $salesOrderMiscs    = ClassRegistry::init('SalesOrderMisc')->find("all", array('conditions' => array('SalesOrderMisc.sales_order_id' => $salesOrderId)));
            $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $salesOrderId)));
            $salesOrderDetails  = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $salesOrderId)));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderMiscs', 'salesOrderServices', 'uoms', 'locSetting'));
        } else {
            exit;
        }
    }
    
    function printInvoice($id = null){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $salesOrder = $this->SalesOrder->read(null, $id);
            if (!empty($salesOrder)) {
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' => $id)));
                $salesOrderServices = ClassRegistry::init('SalesOrderService')->find("all", array('conditions' => array('SalesOrderService.sales_order_id' => $id)));
                $salesOrderMiscs = ClassRegistry::init('SalesOrderMisc')->find("all", array('conditions' => array('SalesOrderMisc.sales_order_id' => $id)));
                $this->set(compact('salesOrder', 'salesOrderDetails', 'salesOrderServices', 'salesOrderMiscs'));
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
    
    function customerCondition($id = null){
        $this->layout = 'ajax';
        $result = array();
        if (!empty($id)) {
            $result['error']         = 0;
            $result['limit_invoice'] = 0;
            $result['limit_balance'] = 0;
            $result['invoice_used']  = 0;
            $result['balance_used']  = 0;
        }else{
            // Invalid Id
            $result['error'] = 1;
        }
        echo json_encode($result);
        exit;
    }
    
    function pick($id){
        $this->layout = 'ajax';
        if(empty($id) && empty($this->data)){
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
        // Error = 1: Can not save
        $result = array();
        $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $id)));
        if(!empty($salesOrder) && $salesOrder['SalesOrder']['status'] == 1){
            $access = true;
            $productOrder = array();
            // Get Loction Setting
            $locSetting = ClassRegistry::init('LocationSetting')->findById(4);
            $locCon     = '';
            if($locSetting['LocationSetting']['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
            $sqlDetail = mysql_query("SELECT id, product_id, SUM((IFNULL(qty,0) + IFNULL(qty_free,0)) * conversion) AS qty FROM sales_order_details WHERE sales_order_id =".$id." AND id NOT IN (SELECT sales_order_detail_id FROM delivery_details GROUP BY sales_order_detail_id) GROUP BY product_id");
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
                $this->loadModel('Delivery');
                $this->loadModel('DeliveryDetail');
                // Delivery
                $this->Delivery->create();
                $this->data['Delivery']['company_id']   = $salesOrder['SalesOrder']['company_id'];
                $this->data['Delivery']['branch_id']    = $salesOrder['SalesOrder']['branch_id'];
                $this->data['Delivery']['warehouse_id'] = $salesOrder['SalesOrder']['location_group_id'];
                $this->data['Delivery']['date'] = date("Y-m-d");
                $this->data['Delivery']['code'] = NULL;
                $this->data['Delivery']['created_by'] = $user['User']['id'];
                $this->data['Delivery']['status']  = 2;
                $this->Delivery->save($this->data);
                $deliveryId = $this->Delivery->id;

                $result['error']    = 0;
                $result['delivery_id'] = $deliveryId;
                // Update Code Delivery
                // Update Code & Change SO Generate Code
                $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $salesOrder['SalesOrder']['branch_id'])));
                $dnCode     = date("y").$modComCode['ModuleCodeBranch']['dn_code'];
                // Get Module Code
                $modCode    = $this->Helper->getModuleCode($dnCode, $deliveryId, 'code', 'deliveries', 'status >= 0');
                // Updaet Module Code
                mysql_query("UPDATE deliveries SET code = '".$modCode."' WHERE id = ".$deliveryId);
                // Update Sales Order
                mysql_query("UPDATE sales_orders SET delivery_id = ".$deliveryId.", status = 2, `modified` = '".date("Y-m-d H:i:s")."', `modified_by` = ".$user['User']['id']." WHERE  `id`= " . $id);
                $dateSale = $salesOrder['SalesOrder']['order_date'];
                // List Sale Detail
                $sqlDetail = mysql_query("SELECT id, product_id, SUM((IFNULL(qty,0) + IFNULL(qty_free,0)) * conversion) AS total_qty, SUM(IFNULL(qty,0)) AS qty_order, SUM(IFNULL(qty_free,0)) AS qty_free, (SELECT unit_cost FROM products WHERE id = sales_order_details.product_id) AS product_cost, (SELECT small_val_uom FROM products WHERE id = sales_order_details.product_id) AS small_val_uom, (total_price - IFNULL(discount_amount, 0)) AS total_price FROM sales_order_details WHERE sales_order_id =".$id." AND id NOT IN (SELECT sales_order_detail_id FROM delivery_details GROUP BY sales_order_detail_id) GROUP BY product_id");
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

                        // Calculate Location, Lot, Expired Date
                        $sqlInventory = mysql_query("SELECT * FROM stock_orders WHERE product_id = ".$rowDetail['product_id']." AND sales_order_id = ".$salesOrder['SalesOrder']['id']);
                        while($invInfo = mysql_fetch_array($sqlInventory)){
                            if($rowDetail['total_price'] > 0){
                                $unitPrice = $this->Helper->replaceThousand(number_format($rowDetail['total_price'] / $invInfo['qty'], 9));
                            } else {
                                $unitPrice = 0;
                            }
                            // Update Inventory (SO)
                            $data = array();
                            $data['module_type']       = 10;
                            $data['sales_order_id']    = $salesOrder['SalesOrder']['id'];
                            $data['product_id']        = $invInfo['product_id'];
                            $data['location_id']       = $invInfo['location_id'];
                            $data['location_group_id'] = $invInfo['location_group_id'];
                            $data['lots_number']  = $invInfo['lots_number']!=''?$invInfo['lots_number']:0;
                            $data['expired_date'] = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                            $data['date']         = $dateSale;
                            $data['total_qty']    = $invInfo['qty'];
                            $data['total_order']  = $invInfo['qty'];
                            $data['total_free']   = 0;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = $salesOrder['SalesOrder']['customer_id'];
                            $data['vendor_id']    = "";
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = $unitPrice;
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Reset Stock Order
                            $this->Inventory->saveGroupQtyOrder($invInfo['location_group_id'], $invInfo['location_id'], $invInfo['product_id'], $invInfo['lots_number'], $invInfo['expired_date'], $invInfo['qty'], $dateSale, '-');

                            //Insert Into Delivery Detail
                            $deliveyDetail = array();
                            $this->DeliveryDetail->create();
                            $deliveyDetail['DeliveryDetail']['delivery_id']    = $deliveryId;
                            $deliveyDetail['DeliveryDetail']['sales_order_id'] = $salesOrder['SalesOrder']['id'];
                            $deliveyDetail['DeliveryDetail']['sales_order_detail_id'] = $rowDetail['id'];
                            $deliveyDetail['DeliveryDetail']['product_id']    = $rowDetail['product_id'];
                            $deliveyDetail['DeliveryDetail']['location_id']   = $invInfo['location_id'];
                            $deliveyDetail['DeliveryDetail']['lots_number']   = 0;
                            $deliveyDetail['DeliveryDetail']['expired_date']  = $invInfo['expired_date']!='0000-00-00'?$invInfo['expired_date']:'0000-00-00';
                            $deliveyDetail['DeliveryDetail']['total_qty']     = $invInfo['qty'];
                            $this->DeliveryDetail->save($deliveyDetail);
                        }
                        // Update COGS
                        $sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
                        $rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
                        if($rowSettingUomDetail['calculate_cogs'] == 2){
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
                // Update Delivery Note Detail Pick Ready
                mysql_query("UPDATE delivery_details SET delivery_id = '".$deliveryId."' WHERE sales_order_id = ".$id);
                // Delete Tmp Stock And Delivery
                mysql_query("DELETE FROM `stock_orders` WHERE  `sales_order_id`= " . $id);
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery Consignment', 'Save Pick', $deliveryId);
            }else{
                $this->Helper->saveUserActivity($user['User']['id'], 'Delivery Consignment', 'Save Pick (Error Out of Stock)', $id);
                $result['error'] = 2;
            }
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Delivery Consignment', 'Save Pick (Error Status)', $id);
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
    
    function searchCustomer() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission     = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $customers = ClassRegistry::init('Customer')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Customer.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.name_kh LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.customer_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.main_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.mobile_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.email LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'Customer.is_active' => 1, $userPermission
                    ),
                    'fields' => array(
                        'Customer.*',
                        'LocationGroup.*'
                    ),
                    'joins' => array(
                        array('table' => 'location_groups', 'alias' => 'LocationGroup', 'type' => 'INNER', 'conditions' => array('LocationGroup.customer_id = Customer.id'))
                    ),
                    'group' => array('Customer.id')
                ));
        $this->set(compact('customers'));
    }
    
    function consignment($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
    }
    
    function consignmentAjax($companyId, $branchId, $customerId = ''){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
    }
    
    function getProductFromConsignment($id = null, $locationGroup = null){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id) && empty($locationGroup)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
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
        $sqlQuoteDetail  = mysql_query("SELECT products.code AS code, products.barcode AS barcode, products.name AS name, products.unit_cost AS unit_cost, products.price_uom_id AS price_uom_id, products.small_val_uom AS small_val_uom, consignment_details.product_id AS product_id, consignment_details.qty AS qty, consignment_details.qty_uom_id AS qty_uom_id, consignment_details.conversion AS conversion, consignment_details.unit_price AS unit_price, consignment_details.total_price AS total_price, consignments.customer_id AS customer_id FROM consignment_details INNER JOIN consignments ON consignments.id = consignment_details.consignment_id INNER JOIN products ON products.id = consignment_details.product_id WHERE consignment_details.consignment_id = ".$id.";");
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
                $totalAfterDis = $totalPrice;
            } else {
                $totalAfterDis = 0;
            }
            // Open Tr
            $rowLbl .= '<tr class="tblSOList" '.$highLight.'>';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center; padding: 0px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px;"><span class="lblUPC">'.$rowDetail['barcode'].'</span></td>';
            // SKU
            $rowLbl .= '<td style="width:9%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowDetail['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:16%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">';
            $rowLbl .= '<input type="hidden" class="totalQtySales" value="'.($totalQtySales).'" />';
            $rowLbl .= '<input type="hidden" class="totalQtyOrderSales" value="'.$totalOrder.'" />';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" name="product_id[]" value="'.$rowDetail['product_id'].'" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" name="service_id[]" />';
            $rowLbl .= '<input type="hidden" name="discount_id[]" value="" />';
            $rowLbl .= '<input type="hidden" name="discount_amount[]" value="0" />';
            $rowLbl .= '<input type="hidden" name="discount_percent[]" value="0" />';
            $rowLbl .= '<input type="hidden" name="conversion[]" class="conversion" value="'.$rowDetail['conversion'].'" />';
            $rowLbl .= '<input type="hidden" name="small_uom_val[]" class="small_uom_val" value="'.$rowDetail['small_val_uom'].'" />';
            $rowLbl .= '<input type="hidden" name="note[]" id="note_'.$index.'" class="note" />';
            $rowLbl .= '<input type="hidden" class="orgProName" value="PUC: '.$rowDetail['barcode'].'<br/><br/>SKU: '.$rowDetail['code'].'<br/><br/>Name: '.str_replace('"', '&quot;', $rowDetail['name']).'" />';
            $rowLbl .= '<input type="text" id="product_'.$index.'" value="'.$productName.'" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '<img alt="Information" src="'.$this->webroot.'img/button/view.png" class="btnProductSaleInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Information\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Input
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_'.$index.'" name="qty[]" value="'.number_format($qty, 0).'" style="width:60%;" class="floatQty qty" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="qty_free_'.$index.'" name="qty_free[]" value="0" style="width:60%;" class="floatQty qty_free" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $rowLbl .= '<td style="width:10%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
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
            $rowLbl .= '<td style="width: 11%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" class="float unit_cost" name="unit_cost[]" value="'.number_format($costSelected, 2).'" />';
            $rowLbl .= '<input type="text" id="unit_price_'.$index.'" name="unit_price[]" '.$readonly.' value="'.number_format($rowDetail['unit_price'], 2).'" style="width:60%; '.$priceColor.'" class="float unit_price validate[required]" />';
            $rowLbl .= '<img alt="'.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'" src="'.$this->webroot.'img/button/down.png" '.$msgStyle.' class="priceDownOrder" align="absmiddle" onmouseover="Tip(\''.MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST.'\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                $disPlay = 'display: none;';
                $disDisplay  = '<input type="text" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="0" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; '.$disPlay.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountSO float" value="0" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= number_format($disAmt, 2);
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="white-space: nowrap;vertical-align: top; width:11%">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="total_price_bf_dis_'.$index.'" name="total_price_bf_dis[]" value="'.number_format($totalPrice, 2).'" class="total_price_bf_dis float" />';
            $rowLbl .= '<input type="text" id="total_price_'.$index.'" name="total_price[]" '.$readonly.' value="'.number_format($totalAfterDis, 2).'" style="width:80%" class="total_price float" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:7%">';
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

}

?>