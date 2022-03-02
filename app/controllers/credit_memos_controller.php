<?php

class CreditMemosController extends AppController {

    var $name = 'CreditMemos';
    var $components = array('Helper', 'ProductCom', 'Inventory');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set('user', $user);
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Dashboard');
    }

    function ajax($customer = 'all', $filterStatus = 'all', $balance = 'all', $date = '') {
        $this->layout = 'ajax';
        $this->set(compact('customer', 'filterStatus', 'balance', 'date'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!empty($id)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'View', $id);
            $this->data = $this->CreditMemo->read(null, $id);
            if (!empty($this->data)) {
                $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
                $creditMemoServices = ClassRegistry::init('CreditMemoService')->find("all", array('conditions' => array('CreditMemoService.credit_memo_id' => $id)));
                $creditMemoReceipts = ClassRegistry::init('CreditMemoReceipt')->find("all", array('conditions' => array('CreditMemoReceipt.credit_memo_id' => $id, 'CreditMemoReceipt.is_void' => 0)));
                $cmWsales = ClassRegistry::init('CreditMemoWithSale')->find("all", array('conditions' => array('CreditMemoWithSale.credit_memo_id' => $id, 'CreditMemoWithSale.status>0')));
                $this->set(compact('creditMemo', 'creditMemoDetails', 'creditMemoReceipts', 'creditMemoServices', 'cmWsales'));
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
            $dateNow = date("Y-m-d H:i:s");
            $result  = array();
            // Load Table
            $this->loadModel('CreditMemoDetail');
            $this->loadModel('CreditMemoService');
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('InventoryValuation');
            $this->loadModel('AccountType');
            $this->loadModel('Company');
            $this->loadModel('Transaction');
            $this->loadModel('TransactionDetail');
            
            // Update Preview
            if($this->data['CreditMemo']['preview_id'] != ''){
                $credit_memo = $this->CreditMemo->read(null, $this->data['CreditMemo']['preview_id']);
                // Check Save Transaction
                $checkTransaction = true;
                $transactionLogId = 0;
                $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Return' AND action = 1 AND module_id = ".$this->data['CreditMemo']['preview_id']);
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
                                    if($credit_memo['CreditMemo']['status'] == 2){
                                        if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                            $checkTransaction = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if($checkTransaction == true){
                                // Check Account
                                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE credit_memo_id = ".$this->data['CreditMemo']['preview_id']." AND credit_memo_receipt_id IS NULL LIMIT 1)");
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
                if($checkTransaction == true){
                    // Remove Transaction Log
                    mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                    mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                    // Update
                    $this->CreditMemo->updateAll(
                        array('CreditMemo.status' => -1, 'CreditMemo.modified_by' => $user['User']['id']), array('CreditMemo.id' => $this->data['CreditMemo']['preview_id'])
                    );
                    $this->GeneralLedger->updateAll(
                            array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.credit_memo_id' => $this->data['CreditMemo']['preview_id'])
                    );
                    $this->InventoryValuation->updateAll(
                            array('InventoryValuation.is_active' => 2), array('InventoryValuation.credit_memo_id' => $this->data['CreditMemo']['preview_id'])
                    );
                    // Delete All Unearned Schedule
                    mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$this->data['CreditMemo']['preview_id']." AND type = 2");
                    mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$this->data['CreditMemo']['preview_id']." AND type = 2");
                    if($credit_memo['CreditMemo']['status'] == 2){
                        $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $this->data['CreditMemo']['preview_id'])));
                        foreach($creditMemoDetails AS $creditMemoDetail){
                            $totalQtyOrder = (($creditMemoDetail['CreditMemoDetail']['qty'] + $creditMemoDetail['CreditMemoDetail']['qty_free']) * $creditMemoDetail['CreditMemoDetail']['conversion']);
                            $qtyOrder      = ($creditMemoDetail['CreditMemoDetail']['qty'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                            $qtyFree       = ($creditMemoDetail['CreditMemoDetail']['qty_free'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                            // Update Inventory (Sales Return)
                            $data = array();
                            $data['module_type']        = 19;
                            $data['credit_memo_id']     = $credit_memo['CreditMemo']['id'];
                            $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
                            $data['location_id']        = $credit_memo['CreditMemo']['location_id'];
                            $data['location_group_id']  = $credit_memo['CreditMemo']['location_group_id'];
                            $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
                            $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
                            $data['date']         = $credit_memo['CreditMemo']['order_date'];
                            $data['total_qty']    = $totalQtyOrder;
                            $data['total_order']  = $qtyOrder;
                            $data['total_free']   = $qtyFree;
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = $credit_memo['CreditMemo']['customer_id'];
                            $data['vendor_id']    = "";
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = $creditMemoDetail['CreditMemoDetail']['total_price'] - $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                            $data['transaction_id'] = '';
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                        }
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Add New (Error Save Transaction)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }

            //  Find Chart Account
            $arAccount = $this->AccountType->findById(7);
            $salesDiscAccount   = $this->AccountType->findById(11);
            $salesMarkUpAccount = $this->AccountType->findById(17);
            $unearnedRevenueAccount  = $this->AccountType->findById(20);
            $unearnedDiscountAccount = $this->AccountType->findById(21);
            $total_balance = ($this->data['CreditMemo']['total_amount'] + $this->data['CreditMemo']['mark_up'] + $this->data['CreditMemo']['total_vat']) - $this->data['CreditMemo']['discount'];
            // Sales Return
            $this->CreditMemo->create();
            $creditMemo = array();
            $creditMemo['CreditMemo']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $creditMemo['CreditMemo']['created']    = $dateNow;
            $creditMemo['CreditMemo']['created_by'] = $user['User']['id'];
            $creditMemo['CreditMemo']['company_id'] = $this->data['CreditMemo']['company_id'];
            $creditMemo['CreditMemo']['branch_id']  = $this->data['CreditMemo']['branch_id'];
            $creditMemo['CreditMemo']['location_group_id'] = $this->data['CreditMemo']['location_group_id'];
            $creditMemo['CreditMemo']['location_id'] = $this->data['CreditMemo']['location_id'];
            $creditMemo['CreditMemo']['customer_id'] = $this->data['CreditMemo']['customer_id'];
            $creditMemo['CreditMemo']['currency_id'] = $this->data['CreditMemo']['currency_id'];
            $creditMemo['CreditMemo']['sales_order_id'] = $this->data['CreditMemo']['sales_order_id'];
            $creditMemo['CreditMemo']['invoice_code']   = $this->data['CreditMemo']['invoice_code'];
            $creditMemo['CreditMemo']['sales_rep_id']   = $this->data['CreditMemo']['sales_rep_id']!=""?$this->data['CreditMemo']['sales_rep_id']:0;
            $creditMemo['CreditMemo']['note']    = $this->data['CreditMemo']['note'];
            $creditMemo['CreditMemo']['ar_id']   = $arAccount['AccountType']['chart_account_id'];
            $creditMemo['CreditMemo']['cm_code'] = $this->data['CreditMemo']['cm_code'];
            $creditMemo['CreditMemo']['balance'] = $total_balance;
            $creditMemo['CreditMemo']['total_amount'] = $this->data['CreditMemo']['total_amount'];
            $creditMemo['CreditMemo']['mark_up']      = $this->data['CreditMemo']['mark_up'];
            $creditMemo['CreditMemo']['discount']     = $this->data['CreditMemo']['discount'];
            $creditMemo['CreditMemo']['discount_percent'] = $this->data['CreditMemo']['discount_percent'];
            $creditMemo['CreditMemo']['order_date']  = $this->data['CreditMemo']['order_date'];
            $creditMemo['CreditMemo']['status']      = 2;
            $creditMemo['CreditMemo']['total_vat']   = $this->data['CreditMemo']['total_vat'];
            $creditMemo['CreditMemo']['vat_percent'] = $this->data['CreditMemo']['vat_percent'];
            $creditMemo['CreditMemo']['vat_setting_id'] = $this->data['CreditMemo']['vat_setting_id'];
            $creditMemo['CreditMemo']['vat_calculate']  = $this->data['CreditMemo']['vat_calculate'];
            $creditMemo['CreditMemo']['vat_chart_account_id'] = $this->data['CreditMemo']['vat_chart_account_id'];
            $creditMemo['CreditMemo']['price_type_id'] = $this->data['CreditMemo']['price_type_id'];
            if ($this->CreditMemo->save($creditMemo)) {
                $creditMemoId = $this->CreditMemo->id;
                $company      = $this->Company->read(null, $this->data['CreditMemo']['company_id']);
                $classId      = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['CreditMemo']['location_group_id']);
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['CreditMemo']['cm_code'], $creditMemoId, 'cm_code', 'credit_memos', 'status != -1 AND branch_id = '.$this->data['CreditMemo']['branch_id']);
                // Updaet Module Code
                mysql_query("UPDATE credit_memos SET cm_code = '".$modCode."' WHERE id = ".$creditMemoId);
                // Transaction 
                $transactionAcct = 0;
                $transactionPro  = 0;
                $transactionSer  = 0;
                $transaction = array();
                $this->Transaction->create();
                $transaction['Transaction']['module_id']  = $creditMemoId;
                $transaction['Transaction']['type']       = 'Sales Return';
                $transaction['Transaction']['created']    = $dateNow;
                $transaction['Transaction']['created_by'] = $user['User']['id'];
                $this->Transaction->save($transaction);
                $transactionId = $this->Transaction->id;
                // Create General Ledger
                $this->GeneralLedger->create();
                $generalLedger = array();
                $generalLedger['GeneralLedger']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $generalLedger['GeneralLedger']['credit_memo_id'] = $creditMemoId;
                $generalLedger['GeneralLedger']['date']       = $this->data['CreditMemo']['order_date'];
                $generalLedger['GeneralLedger']['reference']  = $modCode;
                $generalLedger['GeneralLedger']['created']    = $dateNow;
                $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                $generalLedger['GeneralLedger']['is_sys']     = 1;
                $generalLedger['GeneralLedger']['is_adj']     = 0;
                $generalLedger['GeneralLedger']['is_active']  = 1;
                $this->GeneralLedger->save($generalLedger);
                $generalLedgerId = $this->GeneralLedger->id;

                // General Ledger Detail (A/R)
                $generalLedgerDetail = array();
                $this->GeneralLedgerDetail->create();
                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $arAccount['AccountType']['chart_account_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $creditMemo['CreditMemo']['company_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $creditMemo['CreditMemo']['branch_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $creditMemo['CreditMemo']['location_group_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['location_id'] = $creditMemo['CreditMemo']['location_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Sales Return';
                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $total_balance;
                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode;
                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                $transactionAcct++;
                // General Ledger Detail (Total Discount)
                if ($this->data['CreditMemo']['discount'] > 0) {
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $this->data['CreditMemo']['discount'];
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Total Discount';
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                }

                // General Ledger Detail (Total Mark Up)
                if ($this->data['CreditMemo']['mark_up'] > 0) {
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesMarkUpAccount['AccountType']['chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['CreditMemo']['mark_up'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Markup';
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                }

                // General Ledger Detail (Total VAT)
                if ($creditMemo['CreditMemo']['total_vat'] > 0) {
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['CreditMemo']['vat_chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $creditMemo['CreditMemo']['total_vat'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = "ICS: Sales Return # " . $modCode . ' Total VAT';
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                }

                for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                    if (!empty($_POST['product_id'][$i])) {
                        $tranDetailAcct = 0;
                        // Sales Order Detail
                        $this->CreditMemoDetail->create();
                        $creditMemoDetail = array();
                        $creditMemoDetail['CreditMemoDetail']['credit_memo_id'] = $creditMemoId;
                        $creditMemoDetail['CreditMemoDetail']['discount_id'] = $_POST['discount_id'][$i];
                        $creditMemoDetail['CreditMemoDetail']['discount_amount']  = $_POST['discount'][$i];
                        $creditMemoDetail['CreditMemoDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                        $creditMemoDetail['CreditMemoDetail']['product_id'] = $_POST['product_id'][$i];
                        $creditMemoDetail['CreditMemoDetail']['qty'] = $_POST['qty'][$i];
                        $creditMemoDetail['CreditMemoDetail']['qty_free'] = $_POST['qty_free'][$i];
                        $creditMemoDetail['CreditMemoDetail']['qty_uom_id'] = $_POST['qty_uom_id'][$i];
                        $creditMemoDetail['CreditMemoDetail']['unit_price'] = $_POST['unit_price'][$i];
                        $creditMemoDetail['CreditMemoDetail']['total_price'] = $_POST['h_total_price'][$i];
                        $creditMemoDetail['CreditMemoDetail']['lots_number'] = ($_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:"0");
                        $creditMemoDetail['CreditMemoDetail']['expired_date'] = ($_POST['expired_date'][$i]!=""?$_POST['expired_date'][$i]:"0000-00-00");
                        $creditMemoDetail['CreditMemoDetail']['conversion'] = ($_POST['cm_conversion'][$i]);
                        $creditMemoDetail['CreditMemoDetail']['note'] = $_POST['note'][$i];
                        $this->CreditMemoDetail->save($creditMemoDetail);
                        $creditMemoDetailId = $this->CreditMemoDetail->id;
                        $transactionPro++;
                        $qtyOrder      = (($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_val_uom'][$i] / $_POST['cm_conversion'][$i]));
                        $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['cm_conversion'][$i];
                        $priceSales    = $_POST['h_total_price'][$i] - $_POST['discount'][$i];
                        $queryProductCodeName = mysql_query("SELECT CONCAT(code,' - ',name) AS name, unit_cost AS unit_cost FROM products WHERE id=" . $_POST['product_id'][$i]);
                        $dataProductCodeName = mysql_fetch_array($queryProductCodeName);
                        // Transaction Detail
                        $tranDetail = array();
                        $this->TransactionDetail->create();
                        $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                        $tranDetail['TransactionDetail']['type']       = 1;
                        $tranDetail['TransactionDetail']['module_id']  = $creditMemoDetailId;
                        $this->TransactionDetail->save($tranDetail);
                        $tranDetailId = $this->TransactionDetail->id;
                        
                        // Update Inventory (Sales Return)
                        $data = array();
                        $data['module_type']        = 11;
                        $data['credit_memo_id']     = $creditMemoId;
                        $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
                        $data['location_id']        = $creditMemo['CreditMemo']['location_id'];
                        $data['location_group_id']  = $creditMemo['CreditMemo']['location_group_id'];
                        $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
                        $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
                        $data['date']         = $creditMemo['CreditMemo']['order_date'];
                        $data['total_qty']    = $qtyOrderSmall;
                        $data['total_order']  = $_POST['qty'][$i] * $_POST['cm_conversion'][$i];
                        $data['total_free']   = $_POST['qty_free'][$i] * $_POST['cm_conversion'][$i];
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = $creditMemo['CreditMemo']['customer_id'];
                        $data['vendor_id']    = "";
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = $priceSales;
                        $data['transaction_id'] = $tranDetailId;
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($data);
                        
                        // Inventory Valuation
                        $this->InventoryValuation->create();
                        $inv_valutaion = array();
                        $inv_valutaion['InventoryValuation']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                        $inv_valutaion['InventoryValuation']['credit_memo_id'] = $creditMemoId;
                        $inv_valutaion['InventoryValuation']['company_id'] = $this->data['CreditMemo']['company_id'];
                        $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['CreditMemo']['branch_id'];
                        $inv_valutaion['InventoryValuation']['type'] = "Sales Return";
                        $inv_valutaion['InventoryValuation']['reference']   = $modCode;
                        $inv_valutaion['InventoryValuation']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                        $inv_valutaion['InventoryValuation']['date'] = $this->data['CreditMemo']['order_date'];
                        $inv_valutaion['InventoryValuation']['pid'] = $_POST['product_id'][$i];
                        $inv_valutaion['InventoryValuation']['small_qty'] = $qtyOrderSmall;
                        $inv_valutaion['InventoryValuation']['qty'] = $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                        $inv_valutaion['InventoryValuation']['cost'] = null;
                        $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                        $inv_valutaion['InventoryValuation']['created'] = $dateNow;
                        $this->InventoryValuation->save($inv_valutaion);
                        $inv_valutation_id = $this->InventoryValuation->getLastInsertId();
                        $inventoryAsset = 0;
                        $cogs = 0;

                        // General Ledger Detail (Product Income)
                        $this->GeneralLedgerDetail->create();
                        $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                        $dataIncAccount = mysql_fetch_array($queryIncAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = $creditMemoDetail['CreditMemoDetail']['product_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Sales Return';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['h_total_price'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Product # ' . $_POST['product'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;
                        // General Ledger Detail (Product Discount)
                        if ($_POST['discount'][$i] > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['discount'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Product # ' . $_POST['product'][$i] . ' Discount';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                        }

                        // Update GL for Inventory
                        $this->GeneralLedgerDetail->create();
                        $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                        $dataInvAccount  = mysql_fetch_array($queryInvAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $inventoryAsset;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory for Sales Return # ' . $modCode . ' Product # ' . $dataProductCodeName[0];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;
                        // Update GL for COGS
                        $this->GeneralLedgerDetail->create();
                        $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                        $dataCogsAccount  = mysql_fetch_array($queryCogsAccount);
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $cogs;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS for Sales Return # ' . $modCode . ' Product # ' . $dataProductCodeName[0];
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
                        // Sales Return Service
                        $this->CreditMemoService->create();
                        $creditMemoService = array();
                        $creditMemoService['CreditMemoService']['credit_memo_id']   = $creditMemoId;
                        $creditMemoService['CreditMemoService']['discount_id']      = $_POST['discount_id'][$i];
                        $creditMemoService['CreditMemoService']['discount_amount']  = $_POST['discount'][$i];
                        $creditMemoService['CreditMemoService']['discount_percent'] = $_POST['discount_percent'][$i];
                        $creditMemoService['CreditMemoService']['service_id']  = $_POST['service_id'][$i];
                        $creditMemoService['CreditMemoService']['qty']         = $_POST['qty'][$i];
                        $creditMemoService['CreditMemoService']['qty_free']    = $_POST['qty_free'][$i];
                        $creditMemoService['CreditMemoService']['unit_price']  = $_POST['unit_price'][$i];
                        $creditMemoService['CreditMemoService']['total_price'] = $_POST['h_total_price'][$i];
                        $creditMemoService['CreditMemoService']['note']        = $_POST['note'][$i];
                        $creditMemoService['CreditMemoService']['type']        = $_POST['type'][$i];
                        if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00'){
                            $creditMemoService['CreditMemoService']['start'] = $_POST['start'][$i];
                        }
                        $this->CreditMemoService->save($creditMemoService);
                        $creditMemoServiceId = $this->CreditMemoService->id;
                        $transactionSer++;
                        // General Ledger Detail (Service)
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $serviceAcct;
                        $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = $_POST['service_id'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['product_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['h_total_price'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Service # ' . $_POST['service_id'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $tranDetailAcct++;
                        // General Ledger Detail (Service Discount)
                        if ($_POST['discount'][$i] > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $discAcct;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['discount'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $modCode . ' Service # ' . $_POST['service_id'][$i] . ' Discount';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                        }
                        // Transaction Detail
                        $tranDetail = array();
                        $this->TransactionDetail->create();
                        $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                        $tranDetail['TransactionDetail']['type']       = 2;
                        $tranDetail['TransactionDetail']['module_id']  = $creditMemoServiceId;
                        $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                        $this->TransactionDetail->save($tranDetail);
                        if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00' && $_POST['type'][$i] == 2 && $_POST['qty'][$i] > 0){
                            $this->loadModel('UnearnedSchedule');
                            $dateStart = $_POST['start'][$i];
                            for($k= 0; $k < $_POST['qty'][$i]; $k++){
                                $start  = date('Y-m-d', strtotime("+".$k." months", strtotime($dateStart)));
                                $unearnSchedule = array();
                                $unearnSchedule['UnearnedSchedule']['module_id']   = $creditMemoId;
                                $unearnSchedule['UnearnedSchedule']['module_detail_id'] = $creditMemoServiceId;
                                $unearnSchedule['UnearnedSchedule']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                                $unearnSchedule['UnearnedSchedule']['date']        = $start;
                                $unearnSchedule['UnearnedSchedule']['type']        = 2;
                                $unearnSchedule['UnearnedSchedule']['created']     = date("Y-m-d H:i:s");
                                $unearnSchedule['UnearnedSchedule']['created_by']  = $user['User']['id'];
                                // Unearned Revenue
                                $this->UnearnedSchedule->create();
                                $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $dataServiceAccount[0];
                                $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                $unearnSchedule['UnearnedSchedule']['debit']      = $_POST['unit_price'][$i];
                                $unearnSchedule['UnearnedSchedule']['credit']     = $_POST['unit_price'][$i];
                                $this->UnearnedSchedule->save($unearnSchedule);
                                // Check Discount
                                if ($_POST['discount'][$i] > 0) {
                                    $unitDiscount = $_POST['discount'][$i] / $_POST['qty'][$i];
                                    // Unearned Discount
                                    $this->UnearnedSchedule->create();
                                    $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                    $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                                    $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                    $unearnSchedule['UnearnedSchedule']['debit']      = $unitDiscount;
                                    $unearnSchedule['UnearnedSchedule']['credit']     = $unitDiscount;
                                    $this->UnearnedSchedule->save($unearnSchedule);
                                }
                            }
                        }
                    }
                }
                $result['id']    = $creditMemoId;
                $result['code']  = $modCode;
                $result['error'] = 0;
                // Update Transaction Save
                mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                // Recalculate Average Cost
                mysql_query("UPDATE tracks SET val='".$this->data['CreditMemo']['order_date']."', is_recalculate = 1 WHERE id=1");
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Add New', $creditMemoId);
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cm_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $joinUsers    = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
        $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id'));
        $locations    = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'), 'order' => 'Location.name'));
        $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
        $this->set(compact("locations", "locationGroups", "companies", "branches"));
    }

    function orderDetails() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cm_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
        $this->set(compact("branches", "uoms"));
    }

    function miscellaneous() {
        $this->layout = 'ajax';
    }

    function discount() {
        $this->layout = 'ajax';
        $discounts = ClassRegistry::init('Discount')->find("all", array('conditions' => array('Discount.is_active' => 1), 'order' => array('id DESC')));
        $this->set(compact('discounts'));
    }

    function product($companyId = null, $locationId = null, $branchId = null, $orderDate = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('locationId', $locationId);
        $this->set('orderDate', $orderDate);
        $this->set('branchId', $branchId);
    }

    function product_ajax($companyId = null, $locationId = null, $branchId = null, $orderDate = null, $category = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('category', $category);
        $this->set('locationId', $locationId);
        $this->set('orderDate', $orderDate);
        $this->set('branchId', $branchId);
    }

    function applyToInvoice($cmId, $cmBalance, $date) {
        $this->layout = 'ajax';
        $this->loadModel('CreditMemoWithSale');
        $this->loadModel('GeneralLedger');
        $this->loadModel('GeneralLedgerDetail');
        $user = $this->getCurrentUser();
        $creditMemo['CreditMemo']['id'] = $cmId;
        $credit_memo = $this->CreditMemo->read(null, $cmId);
        $total_amount_invoice = 0;
        if ($credit_memo['CreditMemo']['status'] > 0) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            if (!empty($_POST['sales_order'])) {
                for ($i = 0; $i < sizeOf($_POST['sales_order']); $i++) {
                    if ($_POST['sales_order'][$i] != "" && $_POST['sales_order'][$i] > 0) {
                        $cmWSale = array();
                        $saleBalance = 0;
                        $queryInvoice = mysql_query("SELECT balance, sys_code, so_code, ar_id FROM sales_orders WHERE id=" . $_POST['sales_order'][$i]);
                        $dataInvoice = mysql_fetch_array($queryInvoice);
                        if ($cmBalance > 0) {
                            $saleBalance = $dataInvoice['balance'] - $_POST['invoice_price'][$i];
                            mysql_query("UPDATE sales_orders SET balance = " . $saleBalance . " WHERE id=" . $_POST['sales_order'][$i]);
                            // Convert to REST
                            $restCode[$r]['balance'] = $saleBalance;
                            $restCode[$r]['dbtodo']  = 'sales_orders';
                            $restCode[$r]['actodo']  = 'ut';
                            $restCode[$r]['con']     = "sys_code = '".$dataInvoice['sys_code']."'";
                            $r++;
                            $this->CreditMemoWithSale->create();
                            $cmWSale['CreditMemoWithSale']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $cmWSale['CreditMemoWithSale']['credit_memo_id'] = $cmId;
                            $cmWSale['CreditMemoWithSale']['sales_order_id'] = $_POST['sales_order'][$i];
                            $cmWSale['CreditMemoWithSale']['total_price'] = $_POST['invoice_price'][$i];
                            $cmWSale['CreditMemoWithSale']['status'] = 1;
                            $cmWSale['CreditMemoWithSale']['apply_date'] = $date;
                            $cmWSale['CreditMemoWithSale']['created']    = $dateNow;
                            $cmWSale['CreditMemoWithSale']['created_by'] = $user['User']['id'];
                            $this->CreditMemoWithSale->save($cmWSale);
                            $cmWSaleId = $this->CreditMemoWithSale->id;
                            // Convert to REST
                            $restCode[$r] = $this->Helper->convertToDataSync($cmWSale['CreditMemoWithSale'], 'credit_memo_with_sales');
                            $restCode[$r]['modified'] = $dateNow;
                            $restCode[$r]['dbtodo']   = 'credit_memo_with_sales';
                            $restCode[$r]['actodo']   = 'is';
                            $r++;
                            // Save General Ledger Detail
                            $this->GeneralLedger->create();
                            $generalLedger = array();
                            $generalLedger['GeneralLedger']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $generalLedger['GeneralLedger']['credit_memo_with_sale_id']  = $cmWSaleId;
                            $generalLedger['GeneralLedger']['credit_memo_id']  = $cmId;
                            $generalLedger['GeneralLedger']['sales_order_id']  = NULL;
                            $generalLedger['GeneralLedger']['date']       = $date;
                            $generalLedger['GeneralLedger']['reference']  = $credit_memo['CreditMemo']['cm_code'];
                            $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                            $generalLedger['GeneralLedger']['is_sys'] = 1;
                            $generalLedger['GeneralLedger']['is_adj'] = 0;
                            $generalLedger['GeneralLedger']['is_active'] = 1;
                            if ($this->GeneralLedger->save($generalLedger)) {
                                $glId = $this->GeneralLedger->id;
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedger['GeneralLedger'], 'general_ledgers');
                                $restCode[$r]['modified'] = $dateNow;
                                $restCode[$r]['dbtodo']   = 'general_ledgers';
                                $restCode[$r]['actodo']   = 'is';
                                $r++;
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $credit_memo['CreditMemo']['ar_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id']        = $credit_memo['CreditMemo']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']         = $credit_memo['CreditMemo']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Apply Invoice';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['invoice_price'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Apply CM # '.$credit_memo['CreditMemo']['cm_code'].' and INV # ' . $dataInvoice['so_code'];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $credit_memo['CreditMemo']['customer_id'];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
                                $restCode[$r]['dbtodo']   = 'general_ledger_details';
                                $restCode[$r]['actodo']   = 'is';
                                $r++;
                            }
                            // Save General Ledger Detail
                            $this->GeneralLedger->create();
                            $generalLedger['GeneralLedger']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $generalLedger['GeneralLedger']['sales_order_id']  = $_POST['sales_order'][$i];
                            $generalLedger['GeneralLedger']['credit_memo_id']  = NULL;
                            $generalLedger['GeneralLedger']['reference']  = $dataInvoice['so_code'];
                            if ($this->GeneralLedger->save($generalLedger)) {
                                $glId = $this->GeneralLedger->id;
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedger['GeneralLedger'], 'general_ledgers');
                                $restCode[$r]['modified'] = $dateNow;
                                $restCode[$r]['dbtodo']   = 'general_ledgers';
                                $restCode[$r]['actodo']   = 'is';
                                $r++;
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $dataInvoice['ar_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id']        = $credit_memo['CreditMemo']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']         = $credit_memo['CreditMemo']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Apply Sales Return';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['invoice_price'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Apply CM # '.$credit_memo['CreditMemo']['cm_code'].' and INV # ' . $dataInvoice['so_code'];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $credit_memo['CreditMemo']['customer_id'];
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                // Convert to REST
                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
                                $restCode[$r]['dbtodo']   = 'general_ledger_details';
                                $restCode[$r]['actodo']   = 'is';
                                $r++;
                            }
                            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Apply to Invoice', $this->CreditMemoWithSale->id);
                            $total_amount_invoice += $_POST['invoice_price'][$i];
                        }
                    }
                }
            }
            $creditMemo['CreditMemo']['balance'] = $cmBalance - $total_amount_invoice;
            $creditMemo['CreditMemo']['total_amount_invoice'] = ($credit_memo['CreditMemo']['total_amount_invoice'] < 0 ? 0 : $credit_memo['CreditMemo']['total_amount_invoice'] + $total_amount_invoice);
            $creditMemo['CreditMemo']['modified_by'] = $user['User']['id'];
            $this->CreditMemo->save($creditMemo);
            // Convert to REST
            $restCode[$r]['balance']  = $cmBalance - $total_amount_invoice;
            $restCode[$r]['total_amount_invoice'] = $creditMemo['CreditMemo']['total_amount_invoice'];
            $restCode[$r]['modified']    = $dateNow;
            $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
            $restCode[$r]['dbtodo'] = 'credit_memos';
            $restCode[$r]['actodo'] = 'ut';
            $restCode[$r]['con']    = "sys_code = '".$credit_memo['CreditMemo']['sys_code']."'";
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
        }
        exit();
    }

    function aging($id = null) {
        $this->layout = 'ajax';
        $user         = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
            $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
            $result      = array();
            $credit_memo = array();
            
            //Check Credit memo
            $creditMemo = $this->CreditMemo->read(null, $this->data['CreditMemo']['id']);
            $balanceCm  = $creditMemo['CreditMemo']['balance'];
            
            //Update Credit
            $credit_memo['CreditMemo']['id']          = $this->data['CreditMemo']['id'];
            $credit_memo['CreditMemo']['modified_by'] = $user['User']['id'];
            $credit_memo['CreditMemo']['balance']     = $this->data['CreditMemo']['balance_us'];
            if ($this->CreditMemo->save($credit_memo)) {
                $creditMemo = $this->CreditMemo->findById($this->data['CreditMemo']['id']);
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array(
                                "ExchangeRate.branch_id" => $creditMemo['CreditMemo']['branch_id'],
                                "ExchangeRate.currency_id" => $this->data['CreditMemo']['currency_id']), "order" => array("ExchangeRate.created desc")));
                // Get Total Paid
                if(!empty($lastExchangeRate) && $lastExchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                    $exchangeRateId = $lastExchangeRate['ExchangeRate']['id'];
                    $totalPaidOther = ($this->data['CreditMemo']['amount_other'] / $lastExchangeRate['ExchangeRate']['rate_to_sell']);
                } else {
                    $exchangeRateId = 0;
                    $totalPaidOther = 0;
                }
                $totalPaid = $this->data['CreditMemo']['amount_us'] + $totalPaidOther;
                if($totalPaid <= $balanceCm){
                    // Load Model
                    $this->loadModel('CreditMemoReceipt');
                    $this->loadModel('GeneralLedger');
                    $this->loadModel('GeneralLedgerDetail');
                    $this->loadModel('Company');
                    $this->loadModel('Transaction');
                    $transactionAcct = 0;
                    // Sales Order Receipt
                    $this->CreditMemoReceipt->create();
                    $creditMemoReceipt = array();
                    $creditMemoReceipt['CreditMemoReceipt']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $creditMemoReceipt['CreditMemoReceipt']['credit_memo_id']     = $this->data['CreditMemo']['id'];
                    $creditMemoReceipt['CreditMemoReceipt']['branch_id']          = $creditMemo['CreditMemo']['branch_id'];
                    $creditMemoReceipt['CreditMemoReceipt']['exchange_rate_id']   = $exchangeRateId;
                    $creditMemoReceipt['CreditMemoReceipt']['currency_id'] = $this->data['CreditMemo']['currency_id'];
                    $creditMemoReceipt['CreditMemoReceipt']['chart_account_id'] = $cashBankAccountId;
                    $creditMemoReceipt['CreditMemoReceipt']['receipt_code']     = '';
                    $creditMemoReceipt['CreditMemoReceipt']['amount_us']        = $this->data['CreditMemo']['amount_us'];
                    $creditMemoReceipt['CreditMemoReceipt']['amount_other']     = $this->data['CreditMemo']['amount_other'];
                    $creditMemoReceipt['CreditMemoReceipt']['total_amount']     = $this->data['CreditMemo']['total_amount'];
                    $creditMemoReceipt['CreditMemoReceipt']['balance']          = $this->data['CreditMemo']['balance_us'];
                    $creditMemoReceipt['CreditMemoReceipt']['balance_other']    = $this->data['CreditMemo']['balance_other'];
                    $creditMemoReceipt['CreditMemoReceipt']['created_by']       = $user['User']['id'];
                    $creditMemoReceipt['CreditMemoReceipt']['pay_date']         = $this->data['CreditMemo']['pay_date']!=''?$this->data['CreditMemo']['pay_date']:'0000-00-00';
                    if ($this->data['CreditMemo']['balance_us'] > 0) {
                        $creditMemoReceipt['CreditMemoReceipt']['due_date'] = $this->data['CreditMemo']['aging']!=''?$this->data['CreditMemo']['aging']:'0000-00-00';
                    }
                    $this->CreditMemoReceipt->save($creditMemoReceipt);
                    $result['sr_id'] = $this->CreditMemoReceipt->id;
                    // Update Code & Change SO Generate Code
                    $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $creditMemo['CreditMemo']['branch_id'])));
                    $repCode    = date("y").$modComCode['ModuleCodeBranch']['cm_rep_code'];
                    // Get Module Code
                    $modCode    = $this->Helper->getModuleCode($repCode, $result['sr_id'], 'receipt_code', 'credit_memo_receipts', 'is_void = 0 AND branch_id = '.$creditMemo['CreditMemo']['branch_id']);
                    // Updaet Module Code
                    mysql_query("UPDATE credit_memo_receipts SET receipt_code = '".$modCode."' WHERE id = ".$result['sr_id']);
                    $company = $this->Company->read(null, $creditMemo['CreditMemo']['location_group_id']);
                    $classId = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $creditMemo['CreditMemo']['location_group_id']);

                    // Save General Ledger Detail
                    $this->GeneralLedger->create();
                    $generalLedger = array();
                    $generalLedger['GeneralLedger']['sys_code']               = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['credit_memo_id']         = $this->data['CreditMemo']['id'];
                    $generalLedger['GeneralLedger']['credit_memo_receipt_id'] = $result['sr_id'];
                    $generalLedger['GeneralLedger']['date']       = $this->data['CreditMemo']['pay_date']!=''?$this->data['CreditMemo']['pay_date']:'0000-00-00';
                    $generalLedger['GeneralLedger']['reference']  = $modCode;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    if ($this->GeneralLedger->save($generalLedger)) {
                        $glId = $this->GeneralLedger->id;
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashBankAccountId;
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id']  = $creditMemo['CreditMemo']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']   = $creditMemo['CreditMemo']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_id'] = $creditMemo['CreditMemo']['location_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $creditMemo['CreditMemo']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Sales Return Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $creditMemo['CreditMemo']['cm_code'];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                        
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $creditMemo['CreditMemo']['ar_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
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
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Save Add New', $id, $result['sr_id']);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Save Add New (Error)');
                    $result['sr_id'] = 0;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if (!empty($id)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Add New', $id);
            $this->data = $this->CreditMemo->read(null, $id);
            $creditMemo = ClassRegistry::init('CreditMemo')->find("first", array('conditions' => array('CreditMemo.id' => $id)));
            if (!empty($creditMemo)) {
                $cmWsales = ClassRegistry::init('CreditMemoWithSale')->find("all", array('conditions' => array('CreditMemoWithSale.credit_memo_id' => $id, 'CreditMemoWithSale.status>0')));
                $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
                $creditMemoServices = ClassRegistry::init('CreditMemoService')->find("all", array('conditions' => array('CreditMemoService.credit_memo_id' => $id)));
                $creditMemoReceipts = ClassRegistry::init('CreditMemoReceipt')->find("all", array('conditions' => array('CreditMemoReceipt.credit_memo_id' => $id, 'CreditMemoReceipt.is_void' => 0)));
                $this->set(compact('creditMemo', 'creditMemoDetails', 'creditMemoServices', 'creditMemoReceipts', 'cmWsales'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    function printInvoice($id = null) {
        if (!empty($id)) {
            $this->layout = 'ajax';
            $creditMemo = ClassRegistry::init('CreditMemo')->find("first", array('conditions' => array('CreditMemo.id' => $id)));
            if (!empty($creditMemo)) {
                $cmWsales = ClassRegistry::init('CreditMemoWithSale')->find("all", array('conditions' => array('CreditMemoWithSale.credit_memo_id' => $id, 'CreditMemoWithSale.status>0'),
                    'group' => 'CreditMemoWithSale.sales_order_id',
                    'fields' => array('sum(CreditMemoWithSale.total_price) as total_price', 'CreditMemoWithSale.*', 'SalesOrder.*')
                        ));
                $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
                $creditMemoMiscs = ClassRegistry::init('CreditMemoMisc')->find("all", array('conditions' => array('CreditMemoMisc.credit_memo_id' => $id)));
                $creditMemoServices = ClassRegistry::init('CreditMemoService')->find("all", array('conditions' => array('CreditMemoService.credit_memo_id' => $id)));

                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $creditMemo['CreditMemo']['location_id'], "Location.is_active" => "1")));
                $this->set(compact('creditMemo', 'creditMemoDetails', 'creditMemoMiscs', 'creditMemoServices', "location", "cmWsales"));
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
            $sr = ClassRegistry::init('CreditMemoReceipt')->find("first", array('conditions' => array('CreditMemoReceipt.id' => $receiptId, 'CreditMemoReceipt.is_void' => 0)));

            $creditMemo = ClassRegistry::init('CreditMemo')->find("first", array('conditions' => array('CreditMemo.id' => $sr['CreditMemo']['id'])));
            if (!empty($creditMemo)) {
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $creditMemo['CreditMemo']['location_id'], "Location.is_active" => "1")));
                $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoMiscs = ClassRegistry::init('CreditMemoMisc')->find("all", array('conditions' => array('CreditMemoMisc.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoServices = ClassRegistry::init('CreditMemoService')->find("all", array('conditions' => array('CreditMemoService.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoReceipts = ClassRegistry::init('CreditMemoReceipt')->find("all", array('conditions' => array('CreditMemoReceipt.credit_memo_id' => $sr['CreditMemo']['id'], 'CreditMemoReceipt.is_void' => 0)));

                $this->set(compact('creditMemo', 'creditMemoDetails', 'creditMemoMiscs', 'creditMemoServices', 'creditMemoReceipts', 'sr', 'lastExchangeRate', 'location'));
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
            $sr = ClassRegistry::init('CreditMemoReceipt')->find("first", array('conditions' => array('CreditMemoReceipt.id' => $receiptId, 'CreditMemoReceipt.is_void' => 0)));

            $creditMemo = ClassRegistry::init('CreditMemo')->find("first", array('conditions' => array('CreditMemo.id' => $sr['CreditMemo']['id'])));
            if (!empty($creditMemo)) {
                $location = ClassRegistry::init('Location')->find("first", array("conditions" => array("Location.id" => $creditMemo['CreditMemo']['location_id'], "Location.is_active" => "1")));
                $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoMiscs = ClassRegistry::init('CreditMemoMisc')->find("all", array('conditions' => array('CreditMemoMisc.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoServices = ClassRegistry::init('CreditMemoService')->find("all", array('conditions' => array('CreditMemoService.credit_memo_id' => $sr['CreditMemo']['id'])));
                $creditMemoReceipts = ClassRegistry::init('CreditMemoReceipt')->find("all", array('conditions' => array('CreditMemoReceipt.id <= ' . $receiptId, 'CreditMemoReceipt.credit_memo_id' => $sr['CreditMemo']['id'], 'CreditMemoReceipt.is_void' => 0)));
                $this->set(compact('creditMemo', 'creditMemoDetails', 'creditMemoMiscs', 'creditMemoServices', 'creditMemoReceipts', 'sr', 'lastExchangeRate', 'location'));
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
            $this->set(compact('companyId'));
        }else{
            exit;
        }
    }

    function customer_ajax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set(compact('companyId', 'group'));
        }else{
            exit;
        }
    }
    
    function salesOrder($companyId = null, $branchId = null, $customerId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'customerId', 'branchId'));
    }

    function salesOrderAjax($companyId = null, $branchId = null, $customerId = '') {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'customerId', 'branchId'));
    }

    function invoice($chartAccountId, $companyId, $branchId, $customerId, $balance, $cmId) {
        $this->layout = 'ajax';
        $this->set('chartAccountId', $chartAccountId);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('balance', $this->Helper->replaceThousand($balance));
        $this->data = $this->CreditMemo->read(null, $cmId);
    }

    function invoiceAjax($chartAccountId, $companyId, $branchId, $customerId, $cg = null) {
        $this->layout = 'ajax';
        $this->set('chartAccountId', $chartAccountId);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('cg', $cg);
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
        $sections = ClassRegistry::init('Pgroup')->find("list", array("conditions" => array("Pgroup.is_active = 1", "Pgroup.id IN (SELECT section_id FROM section_companies WHERE company_id = ".$companyId.")")));
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
                             ));
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

    function searchProductByCode($company_id, $branchId, $customerId) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $product_code = !empty($this->data['code']) ? $this->data['code'] : "";
        $joinProductBranch  = array(
                             'table' => 'product_branches',
                             'type' => 'INNER',
                             'alias' => 'ProductBranch',
                             'conditions' => array(
                                 'ProductBranch.product_id = Product.id',
                                 'ProductBranch.branch_id' => $branchId
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
        $product = ClassRegistry::init('Product')->find('first', array(
            'fields' => array(
                'Product.id',
                'Product.name',
                'Product.code',
                'Product.barcode',
                'Product.small_val_uom',
                'Product.price_uom_id',
                'Product.is_packet',
                'Product.is_expired_date',
                'Product.is_lots'
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
                '((Product.price_uom_id IS NOT NULL AND Product.is_packet = 0) OR (Product.price_uom_id IS NULL AND Product.is_packet = 1))'
            ),
            'joins' => $joins,
            'group' => array(
                'Product.id',
                'Product.name',
                'Product.code',
                'Product.barcode',
                'Product.price_uom_id',
            )
        ));
        $this->set(compact('product', 'customerId'));
        $db = ConnectionManager::getDataSource('default');
        mysql_select_db($db->config['database']);
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('CreditMemoDetail');
            $this->loadModel('CreditMemoService');
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('InventoryValuation');
            $this->loadModel('AccountType');
            $this->loadModel('Company');
            $this->loadModel('Transaction');
            $this->loadModel('TransactionDetail');
            $credit_memo = $this->CreditMemo->read(null, $this->data['id']);
            $queryHasReceipt = mysql_query("SELECT id FROM credit_memo_receipts WHERE credit_memo_id=" . $this->data['id'] . " AND is_void = 0");
            $queryHasApplyInv = mysql_query("SELECT id FROM credit_memo_with_sales WHERE credit_memo_id=" . $this->data['id'] . " AND status > 0");
            if ($credit_memo['CreditMemo']['status'] == 2 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)) {
                $dateNow   = date("Y-m-d H:i:s");
                $result    = array();
                $statuEdit = "-1";
                // Check Save Transaction
                $checkTransaction = true;
                $transactionLogId = 0;
                $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Return' AND action = 1 AND module_id = ".$this->data['id']);
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
                                    if($credit_memo['CreditMemo']['status'] == 2){
                                        if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                            $checkTransaction = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if($checkTransaction == true){
                                // Check Account
                                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE credit_memo_id = ".$this->data['id']." AND credit_memo_receipt_id IS NULL LIMIT 1)");
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
                if($checkTransaction == true){
                    // Remove Transaction Log
                    mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                    mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
                    // Update Status Edit
                    $this->CreditMemo->updateAll(
                        array('CreditMemo.status' => $statuEdit, 'CreditMemo.modified_by' => $user['User']['id']), array('CreditMemo.id' => $this->data['id'])
                    );
                    $this->GeneralLedger->updateAll(
                            array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.credit_memo_id' => $this->data['id'])
                    );
                    $this->InventoryValuation->updateAll(
                            array('InventoryValuation.is_active' => 2), array('InventoryValuation.credit_memo_id' => $this->data['id'])
                    );
                    // Delete All Unearned Schedule
                    mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$this->data['id']." AND type = 2");
                    mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$this->data['id']." AND type = 2");
                    $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $this->data['id'])));
                    foreach($creditMemoDetails AS $creditMemoDetail){
                        $totalQtyOrder = (($creditMemoDetail['CreditMemoDetail']['qty'] + $creditMemoDetail['CreditMemoDetail']['qty_free']) * $creditMemoDetail['CreditMemoDetail']['conversion']);
                        $qtyOrder      = ($creditMemoDetail['CreditMemoDetail']['qty'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                        $qtyFree       = ($creditMemoDetail['CreditMemoDetail']['qty_free'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                        // Update Inventory (Sales Return)
                        $data = array();
                        $data['module_type']        = 19;
                        $data['credit_memo_id']     = $credit_memo['CreditMemo']['id'];
                        $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
                        $data['location_id']        = $credit_memo['CreditMemo']['location_id'];
                        $data['location_group_id']  = $credit_memo['CreditMemo']['location_group_id'];
                        $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
                        $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
                        $data['date']         = $credit_memo['CreditMemo']['order_date'];
                        $data['total_qty']    = $totalQtyOrder;
                        $data['total_order']  = $qtyOrder;
                        $data['total_free']   = $qtyFree;
                        $data['user_id']      = $user['User']['id'];
                        $data['customer_id']  = $credit_memo['CreditMemo']['customer_id'];
                        $data['vendor_id']    = "";
                        $data['unit_cost']    = 0;
                        $data['unit_price']   = $creditMemoDetail['CreditMemoDetail']['total_price'] - $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                        $data['transaction_id'] = '';
                        // Update Invetory Location
                        $this->Inventory->saveInventory($data);
                        // Update Inventory Group
                        $this->Inventory->saveGroupTotalDetail($data);
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Edit (Error Save Transaction)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
                
                //  Find Chart Account
                $arAccount = $this->AccountType->findById(7);
                $salesDiscAccount   = $this->AccountType->findById(11);
                $salesMarkUpAccount = $this->AccountType->findById(17);
                $unearnedRevenueAccount  = $this->AccountType->findById(20);
                $unearnedDiscountAccount = $this->AccountType->findById(21);
                $total_balance = ($this->data['CreditMemo']['total_amount'] + $this->data['CreditMemo']['mark_up'] + $this->data['CreditMemo']['total_vat']) - $this->data['CreditMemo']['discount'];
                // Sales Return
                $this->CreditMemo->create();
                $creditMemo = array();
                $creditMemo['CreditMemo']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $creditMemo['CreditMemo']['created']    = $dateNow;
                $creditMemo['CreditMemo']['created_by'] = $user['User']['id'];
                $creditMemo['CreditMemo']['company_id'] = $this->data['CreditMemo']['company_id'];
                $creditMemo['CreditMemo']['branch_id']  = $this->data['CreditMemo']['branch_id'];
                $creditMemo['CreditMemo']['location_group_id'] = $this->data['CreditMemo']['location_group_id'];
                $creditMemo['CreditMemo']['location_id'] = $this->data['CreditMemo']['location_id'];
                $creditMemo['CreditMemo']['customer_id'] = $this->data['CreditMemo']['customer_id'];
                $creditMemo['CreditMemo']['currency_id'] = $this->data['CreditMemo']['currency_id'];
                $creditMemo['CreditMemo']['sales_order_id'] = $this->data['CreditMemo']['sales_order_id'];
                $creditMemo['CreditMemo']['invoice_code']   = $this->data['CreditMemo']['invoice_code'];
                $creditMemo['CreditMemo']['sales_rep_id']   = $this->data['CreditMemo']['sales_rep_id']!=""?$this->data['CreditMemo']['sales_rep_id']:0;
                $creditMemo['CreditMemo']['note']    = $this->data['CreditMemo']['note'];
                $creditMemo['CreditMemo']['ar_id']   = $arAccount['AccountType']['chart_account_id'];
                $creditMemo['CreditMemo']['cm_code'] = $credit_memo['CreditMemo']['cm_code'];
                $creditMemo['CreditMemo']['balance'] = $total_balance;
                $creditMemo['CreditMemo']['total_amount'] = $this->data['CreditMemo']['total_amount'];
                $creditMemo['CreditMemo']['mark_up']      = $this->data['CreditMemo']['mark_up'];
                $creditMemo['CreditMemo']['discount']     = $this->data['CreditMemo']['discount'];
                $creditMemo['CreditMemo']['discount_percent'] = $this->data['CreditMemo']['discount_percent'];
                $creditMemo['CreditMemo']['order_date']  = $this->data['CreditMemo']['order_date'];
                $creditMemo['CreditMemo']['status']      = 2;
                $creditMemo['CreditMemo']['total_vat']   = $this->data['CreditMemo']['total_vat'];
                $creditMemo['CreditMemo']['vat_percent'] = $this->data['CreditMemo']['vat_percent'];
                $creditMemo['CreditMemo']['vat_setting_id'] = $this->data['CreditMemo']['vat_setting_id'];
                $creditMemo['CreditMemo']['vat_calculate']  = $this->data['CreditMemo']['vat_calculate'];
                $creditMemo['CreditMemo']['vat_chart_account_id'] = $this->data['CreditMemo']['vat_chart_account_id'];
                $creditMemo['CreditMemo']['price_type_id'] = $this->data['CreditMemo']['price_type_id'];
                if ($this->CreditMemo->save($creditMemo)) {
                    $result['id'] = $creditMemoId = $this->CreditMemo->id;
                    $company      = $this->Company->read(null, $this->data['CreditMemo']['company_id']);
                    $classId      = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['CreditMemo']['location_group_id']);
                    $glReference  = $credit_memo['CreditMemo']['cm_code'];
                    if($this->data['CreditMemo']['company_id'] != $credit_memo['CreditMemo']['company_id']){
                        // Get Module Code
                        $modCode = $this->Helper->getModuleCode($this->data['CreditMemo']['cm_code'], $creditMemoId, 'cm_code', 'credit_memos', 'status != -1 AND branch_id = '.$this->data['CreditMemo']['branch_id']);
                        // Updaet Module Code
                        mysql_query("UPDATE credit_memos SET cm_code = '".$modCode."' WHERE id = ".$creditMemoId);
                        $glReference = $modCode;
                    }
                    // Transaction 
                    $transactionAcct = 0;
                    $transactionPro  = 0;
                    $transactionSer  = 0;
                    $transaction = array();
                    $this->Transaction->create();
                    $transaction['Transaction']['module_id']  = $creditMemoId;
                    $transaction['Transaction']['type']       = 'Sales Return';
                    $transaction['Transaction']['created']    = $dateNow;
                    $transaction['Transaction']['created_by'] = $user['User']['id'];
                    $this->Transaction->save($transaction);
                    $transactionId = $this->Transaction->id;
                    // Create General Ledger
                    $this->GeneralLedger->create();
                    $generalLedger = array();
                    $generalLedger['GeneralLedger']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $generalLedger['GeneralLedger']['credit_memo_id'] = $creditMemoId;
                    $generalLedger['GeneralLedger']['date']       = $this->data['CreditMemo']['order_date'];
                    $generalLedger['GeneralLedger']['reference']  = $glReference;
                    $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                    $generalLedger['GeneralLedger']['is_sys'] = 1;
                    $generalLedger['GeneralLedger']['is_adj'] = 0;
                    $generalLedger['GeneralLedger']['is_active'] = 1;
                    $this->GeneralLedger->save($generalLedger);
                    $generalLedgerId = $this->GeneralLedger->id;

                    // General Ledger Detail (A/R)
                    $generalLedgerDetail = array();
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $arAccount['AccountType']['chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id']  = $creditMemo['CreditMemo']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']   = $creditMemo['CreditMemo']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $creditMemo['CreditMemo']['location_group_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['location_id']       = $creditMemo['CreditMemo']['location_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Sales Return';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $total_balance;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference;
                    $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    $transactionAcct++;
                    // General Ledger Detail (Total Discount)
                    if ($this->data['CreditMemo']['discount'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $this->data['CreditMemo']['discount'];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference . ' Total Discount';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }

                    // General Ledger Detail (Total Mark Up)
                    if ($this->data['CreditMemo']['mark_up'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesMarkUpAccount['AccountType']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->data['CreditMemo']['mark_up'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference . ' Markup';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }

                    // General Ledger Detail (Total VAT)
                    if ($creditMemo['CreditMemo']['total_vat'] > 0) {
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['CreditMemo']['vat_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $creditMemo['CreditMemo']['total_vat'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = "ICS: Sales Return # " . $glReference . ' Total VAT';
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                    }

                    for ($i = 0; $i < sizeof($_POST['product']); $i++) {
                        if (!empty($_POST['product_id'][$i])) {
                            $tranDetailAcct = 0;
                            // Sales Order Detail
                            $this->CreditMemoDetail->create();
                            $creditMemoDetail = array();
                            $creditMemoDetail['CreditMemoDetail']['credit_memo_id'] = $creditMemoId;
                            $creditMemoDetail['CreditMemoDetail']['discount_id'] = $_POST['discount_id'][$i];
                            $creditMemoDetail['CreditMemoDetail']['discount_amount']  = $_POST['discount'][$i];
                            $creditMemoDetail['CreditMemoDetail']['discount_percent'] = $_POST['discount_percent'][$i];
                            $creditMemoDetail['CreditMemoDetail']['product_id'] = $_POST['product_id'][$i];
                            $creditMemoDetail['CreditMemoDetail']['qty'] = $_POST['qty'][$i];
                            $creditMemoDetail['CreditMemoDetail']['qty_free'] = $_POST['qty_free'][$i];
                            $creditMemoDetail['CreditMemoDetail']['qty_uom_id'] = $_POST['qty_uom_id'][$i];
                            $creditMemoDetail['CreditMemoDetail']['unit_price'] = $_POST['unit_price'][$i];
                            $creditMemoDetail['CreditMemoDetail']['total_price'] = $_POST['h_total_price'][$i];
                            $creditMemoDetail['CreditMemoDetail']['lots_number'] = ($_POST['lots_number'][$i]!=""?$_POST['lots_number'][$i]:"0");
                            $creditMemoDetail['CreditMemoDetail']['expired_date'] = ($_POST['expired_date'][$i]!=""?$_POST['expired_date'][$i]:"0000-00-00");
                            $creditMemoDetail['CreditMemoDetail']['conversion'] = ($_POST['cm_conversion'][$i]);
                            $creditMemoDetail['CreditMemoDetail']['note'] = $_POST['note'][$i];
                            $this->CreditMemoDetail->save($creditMemoDetail);
                            $creditMemoDetailId = $this->CreditMemoDetail->id;
                            $transactionPro++;
                            $qtyOrder      = (($_POST['qty'][$i] + $_POST['qty_free'][$i]) / ($_POST['small_val_uom'][$i] / $_POST['cm_conversion'][$i]));
                            $qtyOrderSmall = ($_POST['qty'][$i] + $_POST['qty_free'][$i]) * $_POST['cm_conversion'][$i];
                            $priceSales    = $_POST['h_total_price'][$i] - $_POST['discount'][$i];
                            $queryProductCodeName = mysql_query("SELECT CONCAT(code,' - ',name) AS name, unit_cost AS unit_cost FROM products WHERE id=" . $_POST['product_id'][$i]);
                            $dataProductCodeName = mysql_fetch_array($queryProductCodeName);
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 1;
                            $tranDetail['TransactionDetail']['module_id']  = $creditMemoDetailId;
                            $this->TransactionDetail->save($tranDetail);
                            $tranDetailId = $this->TransactionDetail->id;
                            // Update Inventory (Sales Return)
                            $data = array();
                            $data['module_type']        = 11;
                            $data['credit_memo_id']     = $creditMemoId;
                            $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
                            $data['location_id']        = $creditMemo['CreditMemo']['location_id'];
                            $data['location_group_id']  = $creditMemo['CreditMemo']['location_group_id'];
                            $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
                            $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
                            $data['date']         = $creditMemo['CreditMemo']['order_date'];
                            $data['total_qty']    = $qtyOrderSmall;
                            $data['total_order']  = $_POST['qty'][$i] * $_POST['cm_conversion'][$i];
                            $data['total_free']   = $_POST['qty_free'][$i] * $_POST['cm_conversion'][$i];
                            $data['user_id']      = $user['User']['id'];
                            $data['customer_id']  = $creditMemo['CreditMemo']['customer_id'];
                            $data['vendor_id']    = "";
                            $data['unit_cost']    = 0;
                            $data['unit_price']   = $priceSales;
                            $data['transaction_id'] = $tranDetailId;
                            // Update Invetory Location
                            $this->Inventory->saveInventory($data);
                            // Update Inventory Group
                            $this->Inventory->saveGroupTotalDetail($data);
                            
                            // Inventory Valuation
                            $invValOld = $this->InventoryValuation->find('first', array('conditions' => array('InventoryValuation.credit_memo_id' => $this->data['id'], 'InventoryValuation.pid' => $_POST['product_id'][$i], 'InventoryValuation.is_active = 2')));
                            $this->InventoryValuation->create();
                            $inv_valutaion = array();
                            $inv_valutaion['InventoryValuation']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $inv_valutaion['InventoryValuation']['transaction_detail_id'] = $tranDetailId;
                            $inv_valutaion['InventoryValuation']['credit_memo_id'] = $creditMemoId;
                            $inv_valutaion['InventoryValuation']['company_id'] = $this->data['CreditMemo']['company_id'];
                            $inv_valutaion['InventoryValuation']['branch_id']  = $this->data['CreditMemo']['branch_id'];
                            $inv_valutaion['InventoryValuation']['type'] = "Sales Return";
                            $inv_valutaion['InventoryValuation']['reference']   = $glReference;
                            $inv_valutaion['InventoryValuation']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                            $inv_valutaion['InventoryValuation']['date'] = $this->data['CreditMemo']['order_date'];
                            $inv_valutaion['InventoryValuation']['pid'] = $_POST['product_id'][$i];
                            $inv_valutaion['InventoryValuation']['small_qty'] = $qtyOrderSmall;
                            $inv_valutaion['InventoryValuation']['qty'] = $this->Helper->replaceThousand(number_format($qtyOrder, 6));
                            $inv_valutaion['InventoryValuation']['cost'] = null;
                            $inv_valutaion['InventoryValuation']['created'] = $invValOld['InventoryValuation']['created'];
                            $inv_valutaion['InventoryValuation']['date_edited'] = date('Y-m-d H:i:s');
                            $inv_valutaion['InventoryValuation']['is_var_cost'] = 1;
                            $inv_valutaion['InventoryValuation']['created']     = $dateNow;
                            $this->InventoryValuation->save($inv_valutaion);
                            $inv_valutation_id = $this->InventoryValuation->getLastInsertId();
                            $inventoryAsset = 0;
                            $cogs = 0;

                            // General Ledger Detail (Product Income)
                            $this->GeneralLedgerDetail->create();
                            $queryIncAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=8),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=8))),(SELECT chart_account_id FROM account_types WHERE id=8))");
                            $dataIncAccount  = mysql_fetch_array($queryIncAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataIncAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id']  = $creditMemoDetail['CreditMemoDetail']['product_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']       = $_POST['h_total_price'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit']      = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']        = 'ICS: Sales Return # ' . $glReference . ' Product # ' . $_POST['product'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // General Ledger Detail (Product Discount)
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference . ' Product # ' . $_POST['product'][$i] . ' Discount';
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                            }

                            // Update GL for Inventory
                            $this->GeneralLedgerDetail->create();
                            $queryInvAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=1),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=1))),(SELECT chart_account_id FROM account_types WHERE id=1))");
                            $dataInvAccount  = mysql_fetch_array($queryInvAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataInvAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 1;
                            $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Sales Return';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $inventoryAsset;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Inventory for Sales Return # ' . $glReference . ' Product # ' . $dataProductCodeName[0];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // Update GL for COGS
                            $this->GeneralLedgerDetail->create();
                            $queryCogsAccount = mysql_query("SELECT IFNULL((IFNULL((SELECT chart_account_id FROM accounts WHERE product_id = " . $_POST['product_id'][$i] . " AND account_type_id=2),(SELECT chart_account_id FROM pgroup_accounts WHERE pgroup_id = (SELECT pgroup_id FROM product_pgroups WHERE product_id = " . $_POST['product_id'][$i] . " ORDER BY id  DESC LIMIT 1) AND account_type_id=2))),(SELECT chart_account_id FROM account_types WHERE id=2))");
                            $dataCogsAccount  = mysql_fetch_array($queryCogsAccount);
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $dataCogsAccount[0];
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = $inv_valutation_id;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $cogs;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: COGS for Sales Return # ' . $glReference . ' Product # ' . $dataProductCodeName[0];
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
                            // Sales Return Service
                            $creditMemoService = array();
                            $this->CreditMemoService->create();
                            $creditMemoService['CreditMemoService']['credit_memo_id']   = $creditMemoId;
                            $creditMemoService['CreditMemoService']['discount_id']      = $_POST['discount_id'][$i];
                            $creditMemoService['CreditMemoService']['discount_amount']  = $_POST['discount'][$i];
                            $creditMemoService['CreditMemoService']['discount_percent'] = $_POST['discount_percent'][$i];
                            $creditMemoService['CreditMemoService']['service_id']  = $_POST['service_id'][$i];
                            $creditMemoService['CreditMemoService']['qty']         = $_POST['qty'][$i];
                            $creditMemoService['CreditMemoService']['qty_free']    = $_POST['qty_free'][$i];
                            $creditMemoService['CreditMemoService']['unit_price']  = $_POST['unit_price'][$i];
                            $creditMemoService['CreditMemoService']['total_price'] = $_POST['h_total_price'][$i];
                            $creditMemoService['CreditMemoService']['note']        = $_POST['note'][$i];
                            $creditMemoService['CreditMemoService']['type']        = $_POST['type'][$i];
                            if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00'){
                                $creditMemoService['CreditMemoService']['start'] = $_POST['start'][$i];
                            }
                            $this->CreditMemoService->save($creditMemoService);
                            $creditMemoServiceId = $this->CreditMemoService->id;
                            $transactionSer++;
                            // General Ledger Detail (Service)
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $serviceAcct;
                            $generalLedgerDetail['GeneralLedgerDetail']['service_id']  = $_POST['service_id'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['product_id']  = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_id'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['inventory_valuation_is_debit'] = NULL;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['h_total_price'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference . ' Service # ' . $_POST['service_id'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $tranDetailAcct++;
                            // General Ledger Detail (Service Discount)
                            if ($_POST['discount'][$i] > 0) {
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $discAcct;
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['discount'][$i];
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: Sales Return # ' . $glReference . ' Service # ' . $_POST['service_id'][$i] . ' Discount';
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                $tranDetailAcct++;
                            }
                            // Transaction Detail
                            $tranDetail = array();
                            $this->TransactionDetail->create();
                            $tranDetail['TransactionDetail']['transaction_id']  = $transactionId;
                            $tranDetail['TransactionDetail']['type']       = 2;
                            $tranDetail['TransactionDetail']['module_id']  = $creditMemoServiceId;
                            $tranDetail['TransactionDetail']['save_acct']  = $tranDetailAcct;
                            $this->TransactionDetail->save($tranDetail);
                            if($_POST['start'][$i] != '' && $_POST['start'][$i] != '0000-00-00' && $_POST['type'][$i] == 2 && $_POST['qty'][$i] > 0){
                                $this->loadModel('UnearnedSchedule');
                                $dateStart = $_POST['start'][$i];
                                for($k= 0; $k < $_POST['qty'][$i]; $k++){
                                    $start  = date('Y-m-d', strtotime("+".$k." months", strtotime($dateStart)));
                                    $unearnSchedule = array();
                                    $unearnSchedule['UnearnedSchedule']['module_id']   = $creditMemoId;
                                    $unearnSchedule['UnearnedSchedule']['module_detail_id'] = $creditMemoServiceId;
                                    $unearnSchedule['UnearnedSchedule']['customer_id'] = $creditMemo['CreditMemo']['customer_id'];
                                    $unearnSchedule['UnearnedSchedule']['date']        = $start;
                                    $unearnSchedule['UnearnedSchedule']['type']        = 2;
                                    $unearnSchedule['UnearnedSchedule']['created']     = date("Y-m-d H:i:s");
                                    $unearnSchedule['UnearnedSchedule']['created_by']  = $user['User']['id'];
                                    // Unearned Revenue
                                    $this->UnearnedSchedule->create();
                                    $unearnSchedule['UnearnedSchedule']['sys_code']  = md5(rand().date("Y-m-d H:i:s"));
                                    $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $dataServiceAccount[0];
                                    $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $unearnedRevenueAccount['AccountType']['chart_account_id'];
                                    $unearnSchedule['UnearnedSchedule']['debit']      = $_POST['unit_price'][$i];
                                    $unearnSchedule['UnearnedSchedule']['credit']     = $_POST['unit_price'][$i];
                                    $this->UnearnedSchedule->save($unearnSchedule);
                                    // Check Discount
                                    if ($_POST['discount'][$i] > 0) {
                                        $unitDiscount = $_POST['discount'][$i] / $_POST['qty'][$i];
                                        // Unearned Discount
                                        $this->UnearnedSchedule->create();
                                        $unearnSchedule['UnearnedSchedule']['sys_code']   = md5(rand().date("Y-m-d H:i:s"));
                                        $unearnSchedule['UnearnedSchedule']['debit_account_id']  = $unearnedDiscountAccount['AccountType']['chart_account_id'];
                                        $unearnSchedule['UnearnedSchedule']['credit_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
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
                    mysql_query("UPDATE tracks SET val='".$this->data['CreditMemo']['order_date']."', is_recalculate = 1 WHERE id=1");
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Edit', $this->data['id'], $creditMemoId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Edit (Error)', $this->data['id']);
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Edit (Error Status)', $this->data['id']);
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Edit', $id);
        $this->data = ClassRegistry::init('CreditMemo')->find('first', array('conditions' => array('CreditMemo.status = 2', 'CreditMemo.id' => $id)));
        $queryHasReceipt = mysql_query("SELECT id FROM credit_memo_receipts WHERE credit_memo_id=" . $id . " AND is_void = 0");
        $queryHasApplyInv = mysql_query("SELECT id FROM credit_memo_with_sales WHERE credit_memo_id=" . $id . " AND status > 0");
        if ($this->data['CreditMemo']['status'] == 2 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)) {
            $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
            $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cm_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $joinUsers    = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
            $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id'));
            $locations    = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'), 'order' => 'Location.name'));
            $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $this->set(compact("locations", "locationGroups", "companies", "branches"));
        } else {
            echo "Sorry Cannot Edit";
            exit;
        }
    }

    function editDetail($id = null) {
        $this->layout = 'ajax';
        if ($id >= 0) {
            $user = $this->getCurrentUser();
            $branches = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.cm_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $credit_memo = ClassRegistry::init('CreditMemo')->find('first', array('conditions' => array('CreditMemo.status = 2', 'CreditMemo.id' => $id)));
            $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find('all', array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
            $creditMemoServices = ClassRegistry::init('CreditMemoService')->find('all', array('conditions' => array('CreditMemoService.credit_memo_id' => $id)));
            $uoms = ClassRegistry::init('Uom')->find('all', array('fields' => array('Uom.id', 'Uom.name'), 'conditions' => array('Uom.is_active' => 1)));
            $this->set(compact('branches', 'uoms', "creditMemoDetails", "credit_memo", "creditMemoServices"));
        } else {
            exit;
        }
    }

    function receive($id = null) {
        $this->layout = 'ajax';
//        if (!$id && empty($this->data)) {
//            exit;
//        }
//        $user = $this->getCurrentUser();
//        if (!empty($this->data)) {
//            $db = ConnectionManager::getDataSource('default');
//            mysql_select_db($db->config['database']);
//            
//            $creditMemo = $this->CreditMemo->read(null, $this->data['memo_id']);
//            if ($creditMemo['CreditMemo']['status'] == 1) {
//                $r = 0;
//                $restCode = array();
//                $dateNow  = date("Y-m-d H:i:s");
//                $this->loadModel('CreditMemoDetail');
//                $creditMemo['CreditMemo']['id'] = $this->data['memo_id'];
//                $creditMemo['CreditMemo']['status'] = 2;
//                if ($this->CreditMemo->save($creditMemo)) {
//                    // Convert to REST
//                    $restCode[$r]['status']      = 2;
//                    $restCode[$r]['modified']    = $dateNow;
//                    $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['dbtodo'] = 'credit_memos';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$creditMemo['CreditMemo']['sys_code']."'";
//                    $r++;
//                    $credit_memo_id = $this->data['memo_id'];
//                    $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $credit_memo_id)));
//                    $dateCM = $creditMemo['CreditMemo']['order_date'];
//                    foreach ($creditMemoDetails as $creditMemoDetail) {
//                        
//                        $totalQtyOrder = (($creditMemoDetail['CreditMemoDetail']['qty'] + $creditMemoDetail['CreditMemoDetail']['qty_free']) * $creditMemoDetail['CreditMemoDetail']['conversion']);
//                        $qtyOrder      = ($creditMemoDetail['CreditMemoDetail']['qty'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
//                        $qtyFree       = ($creditMemoDetail['CreditMemoDetail']['qty_free'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
//                        $totalAmountSales = ($creditMemoDetail['CreditMemoDetail']['total_price'] - $creditMemoDetail['CreditMemoDetail']['discount_amount']);
//                        if($totalAmountSales > 0){
//                            $unitPrice = $this->Helper->replaceThousand(number_format($totalAmountSales / $totalQtyOrder, 9));
//                        } else {
//                            $unitPrice = 0;
//                        }
//                        // Update Inventory (Sales Return)
//                        $data = array();
//                        $data['module_type']        = 11;
//                        $data['credit_memo_id']     = $creditMemoDetail['CreditMemoDetail']['credit_memo_id'];
//                        $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
//                        $data['location_id']        = $creditMemo['CreditMemo']['location_id'];
//                        $data['location_group_id']  = $creditMemo['CreditMemo']['location_group_id'];
//                        $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
//                        $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
//                        $data['date']         = $dateCM;
//                        $data['total_qty']    = $totalQtyOrder;
//                        $data['total_order']  = $qtyOrder;
//                        $data['total_free']   = $qtyFree;
//                        $data['user_id']      = $user['User']['id'];
//                        $data['customer_id']  = $creditMemo['CreditMemo']['customer_id'];
//                        $data['vendor_id']    = "";
//                        $data['unit_cost']    = 0;
//                        $data['unit_price']   = $unitPrice;
//                        // Update Invetory Location
//                        $this->Inventory->saveInventory($data);
//                        // Update Inventory Group
//                        $this->Inventory->saveGroupTotalDetail($data);
//                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($data, 'inventories');
//                        $restCode[$r]['module_type']  = 11;
//                        $restCode[$r]['total_qty']    = $totalQtyOrder;
//                        $restCode[$r]['total_order']  = $qtyOrder;
//                        $restCode[$r]['total_free']   = $qtyFree;
//                        $restCode[$r]['expired_date'] = $data['expired_date'];
//                        $restCode[$r]['vendor_id']    = "";
//                        $restCode[$r]['unit_cost']    = 0;
//                        $restCode[$r]['customer_id']       = $this->Helper->getSQLSyncCode("customers", $creditMemo['CreditMemo']['customer_id']);
//                        $restCode[$r]['credit_memo_id']    = $this->Helper->getSQLSyncCode("credit_memos", $creditMemo['CreditMemo']['id']);
//                        $restCode[$r]['product_id']        = $this->Helper->getSQLSyncCode("products", $creditMemoDetail['CreditMemoDetail']['product_id']);
//                        $restCode[$r]['location_id']       = $this->Helper->getSQLSyncCode("locations", $creditMemo['CreditMemo']['location_id']);
//                        $restCode[$r]['location_group_id'] = $this->Helper->getSQLSyncCode("location_groups", $creditMemo['CreditMemo']['location_group_id']);
//                        $restCode[$r]['user_id']           = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
//                        $restCode[$r]['dbtype']  = 'saveInv,GroupDetail';
//                        $restCode[$r]['actodo']  = 'inv';
//                        $r++;
//                        // Insert Inventory Unit Cost
//                        mysql_query("INSERT INTO `inventory_unit_costs` (`product_id`, `total_qty`, `unit_cost`, `created`)
//                                     VALUES (".$creditMemoDetail['CreditMemoDetail']['product_id'].", ".$totalQtyOrder.", ".$creditMemoDetail['Product']['unit_cost'].", '".date("Y-m-d H:i:s")."');");
//                        // Convert to REST
//                        $restCode[$r]['unit_cost']  = $creditMemoDetail['Product']['unit_cost'];
//                        $restCode[$r]['total_qty']  = $totalQtyOrder;
//                        $restCode[$r]['created']    = $dateNow;
//                        $restCode[$r]['product_id'] = $this->Helper->getSQLSysCode("products", $creditMemoDetail['CreditMemoDetail']['product_id']);
//                        $restCode[$r]['dbtodo'] = 'inventory_unit_costs';
//                        $restCode[$r]['actodo'] = 'is';
//                        $r++;
//                    }
//                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
//                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Receive', $this->data['memo_id']);
//                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
//                    exit;
//                } else {
//                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Receive (Error)', $this->data['memo_id']);
//                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
//                    exit;
//                }
//            } else {
//                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Save Receive (Error Status)', $this->data['memo_id']);
//                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
//                exit;
//            }
//        }
//        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Receive', $id);
//        $credit_memo = ClassRegistry::init('CreditMemo')->find('first', array(
//            'conditions' => array('CreditMemo.status = 1', 'CreditMemo.id' => $id)
//                )
//        );
//        // Check Status
//        if($credit_memo['CreditMemo']['status'] == 1){
//            $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find('all', array(
//                'conditions' => array('CreditMemoDetail.credit_memo_id' => $id)
//                    )
//            );
//            $this->set(compact("credit_memo", "creditMemoDetails", "id"));
//        }else{
//            exit;
//        }
        exit;
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('CreditMemoDetail');
        $this->loadModel('InventoryValuation');
        $this->loadModel('Transaction');
        $creditMemo = ClassRegistry::init('CreditMemo')->find("first", array('conditions' => array('CreditMemo.id' => $id)));
        $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
        $queryHasReceipt  = mysql_query("SELECT id FROM credit_memo_receipts WHERE credit_memo_id=" . $id . " AND is_void = 0");
        $queryHasApplyInv = mysql_query("SELECT id FROM credit_memo_with_sales WHERE credit_memo_id=" . $id . " AND status > 0");
        if(@mysql_num_rows($queryHasReceipt) && @mysql_num_rows($queryHasApplyInv)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Void (Error has transaction with other modules)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }

        if ($creditMemo['CreditMemo']['status'] == 2) {
            $isLock = 0;
            $allowStockMinus = false;
            $sqlAllowMinus   = mysql_query("SELECT allow_negative_stock FROM location_groups WHERE id = ".$creditMemo['CreditMemo']['location_group_id']);
            if(mysql_num_rows($sqlAllowMinus)){
                $rowAllStock = mysql_fetch_array($sqlAllowMinus);
                if($rowAllStock[0] == 1){
                    $allowStockMinus = true;
                }
            }
            if($allowStockMinus == false){ // Not Allow Minus Check Stock
                foreach($creditMemoDetails AS $creditMemoDetail){
                    // Inventory By Order Date
                    $totalQtyByDate = 0;
                    $sqlInv = mysql_query("SELECT SUM(qty) FROM inventories WHERE location_group_id = ".$creditMemo['CreditMemo']['location_group_id']." AND location_id = ".$creditMemo['CreditMemo']['location_id']." AND product_id = ".$creditMemoDetail['Product']['id']." AND date <= '".$creditMemo['CreditMemo']['order_date']."' AND date_expired = '".$creditMemoDetail['CreditMemoDetail']['expired_date']."'");
                    if(mysql_num_rows($sqlInv)){
                        $rowInv = mysql_fetch_array($sqlInv);
                        $totalQtyByDate = $rowInv[0];
                    }
                    // Total Qty On Hand
                    $totalQtyOnHand = 0;
                    $sqlOnHand = mysql_query("SELECT SUM(total_qty - total_order) FROM ".$creditMemo['CreditMemo']['location_id']."_inventory_totals WHERE product_id = ".$creditMemoDetail['Product']['id']." AND expired_date = '".$creditMemoDetail['CreditMemoDetail']['expired_date']."'");
                    if(mysql_num_rows($sqlOnHand)){
                        $rowOnHand = mysql_fetch_array($sqlOnHand);
                        $totalQtyOnHand = $rowOnHand[0];
                    }
                    $totalQtySalesReturn = ($creditMemoDetail['CreditMemoDetail']['qty'] + $creditMemoDetail['CreditMemoDetail']['qty_free']) * $creditMemoDetail['CreditMemoDetail']['conversion'];
                    if($totalQtySalesReturn > $totalQtyByDate){
                        $isLock = 1;
                        break;
                    } else {
                        if($totalQtySalesReturn > $totalQtyOnHand){
                            $isLock = 1;
                            break;
                        }
                    }
                }
            }
            if($isLock == 1){
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Delete (Error with total qty)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
            // Check Save Transaction
            $checkTransaction = true;
            $transactionLogId = 0;
            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Invoice' AND action = 1 AND module_id = ".$id);
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
                                if($creditMemo['CreditMemo']['status'] == 2){
                                    if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                        $checkTransaction = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if($checkTransaction == true){
                            // Check Account
                            $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE credit_memo_id = ".$id." AND credit_memo_receipt_id IS NULL LIMIT 1)");
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
            if($checkTransaction == false){
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Void (Error Save Transaction)', $id);
                echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
                exit;
            }
            // Remove Transaction Log
            if($transactionLogId > 0){
                mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
            }
            // Update Inventory Valuation
            $this->InventoryValuation->updateAll(
                    array('InventoryValuation.is_active' => 2), array('InventoryValuation.credit_memo_id' => $id)
            );
            // General Ledger
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.credit_memo_id' => $id)
            );
            // Update Sales Return
            $this->CreditMemo->updateAll(
                    array('CreditMemo.status' => 0, 'CreditMemo.modified_by' => $user['User']['id']), array('CreditMemo.id' => $id)
            );
            // Delete All Unearned Schedule
            mysql_query("DELETE FROM unearned_schedules WHERE module_id = ".$id." AND type = 2");
            mysql_query("DELETE FROM unearned_recognitions WHERE module_id = ".$id." AND type = 2");
            $dateNow  = date("Y-m-d H:i:s");
            $this->Transaction->create();
            $transaction = array();
            $transaction['Transaction']['module_id']  = $id;
            $transaction['Transaction']['type']       = 'Sales Return';
            $transaction['Transaction']['action']     = 2;
            $transaction['Transaction']['created']    = $dateNow;
            $transaction['Transaction']['created_by'] = $user['User']['id'];
            $this->Transaction->save($transaction);
            $creditMemoDetails = ClassRegistry::init('CreditMemoDetail')->find("all", array('conditions' => array('CreditMemoDetail.credit_memo_id' => $id)));
            foreach($creditMemoDetails AS $creditMemoDetail){
                $totalQtyOrder = (($creditMemoDetail['CreditMemoDetail']['qty'] + $creditMemoDetail['CreditMemoDetail']['qty_free']) * $creditMemoDetail['CreditMemoDetail']['conversion']);
                $qtyOrder      = ($creditMemoDetail['CreditMemoDetail']['qty'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                $qtyFree       = ($creditMemoDetail['CreditMemoDetail']['qty_free'] * $creditMemoDetail['CreditMemoDetail']['conversion']);
                // Update Inventory (Sales Return)
                $data = array();
                $data['module_type']        = 19;
                $data['credit_memo_id']     = $creditMemo['CreditMemo']['id'];
                $data['product_id']         = $creditMemoDetail['CreditMemoDetail']['product_id'];
                $data['location_id']        = $creditMemo['CreditMemo']['location_id'];
                $data['location_group_id']  = $creditMemo['CreditMemo']['location_group_id'];
                $data['lots_number']  = $creditMemoDetail['CreditMemoDetail']['lots_number'];
                $data['expired_date'] = $creditMemoDetail['CreditMemoDetail']['expired_date'];
                $data['date']         = $creditMemo['CreditMemo']['order_date'];
                $data['total_qty']    = $totalQtyOrder;
                $data['total_order']  = $qtyOrder;
                $data['total_free']   = $qtyFree;
                $data['user_id']      = $user['User']['id'];
                $data['customer_id']  = $creditMemo['CreditMemo']['customer_id'];
                $data['vendor_id']    = "";
                $data['unit_cost']    = 0;
                $data['unit_price']   = $creditMemoDetail['CreditMemoDetail']['total_price'] - $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                $data['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($data);
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($data);
            }
            // Recalculate Average Cost
            mysql_query("UPDATE tracks SET val='".$creditMemo['CreditMemo']['order_date']."', is_recalculate = 1 WHERE id=1");
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return', 'Void (Error Status)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
        
    }

    function voidReceipt($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('CreditMemoReceipt');
        $this->loadModel('Transaction');
        $receipt = ClassRegistry::init('CreditMemoReceipt')->find("first", array('conditions' => array('CreditMemoReceipt.id' => $id)));
        if(!empty($receipt) && @$receipt['CreditMemoReceipt']['is_void'] == 0){
            // Check Save Transaction
            $checkTransaction = true;
            $transactionLogId = 0;
            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales Return Receipt' AND action = 1 AND module_id = ".$id);
            if(mysql_num_rows($sqlCheck)){
                $rowCheck = mysql_fetch_array($sqlCheck);
                $transactionLogId = $rowCheck['id'];
                // Check Account
                $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE credit_memo_id = ".$id." LIMIT 1)");
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
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Void (Error Save Transaction)', $id);
                echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
                exit;
            }
            $this->CreditMemoReceipt->updateAll(
                array('CreditMemoReceipt.is_void' => 1, 'CreditMemoReceipt.modified_by' => $user['User']['id']), array('CreditMemoReceipt.id' => $id)
            );
            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $receipt['CreditMemoReceipt']['exchange_rate_id'])));
            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                $totalPaidOther = $receipt['CreditMemoReceipt']['amount_other'] / $exchangeRate['ExchangeRate']['rate_to_sell'];
            } else {
                $totalPaidOther = 0;
            }
            $total_amount = $receipt['CreditMemoReceipt']['amount_us'] + $totalPaidOther;

            mysql_query("UPDATE credit_memos SET balance = balance+" . $total_amount . " WHERE id=" . $receipt['CreditMemoReceipt']['credit_memo_id']);
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']), array('GeneralLedger.credit_memo_receipt_id' => $id)
            );
            // Transaction
            $dateNow  = date("Y-m-d H:i:s");
            $this->Transaction->create();
            $transaction = array();
            $transaction['Transaction']['module_id']  = $id;
            $transaction['Transaction']['type']       = 'Sales Return Receipt';
            $transaction['Transaction']['action']     = 2;
            $transaction['Transaction']['created']    = $dateNow;
            $transaction['Transaction']['created_by'] = $user['User']['id'];
            $this->Transaction->save($transaction);
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Void (Error)', $id);
            echo MESSAGE_DATA_INVALID;
        }
    }

    function deleteCmWSlae($id) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $result = array();
        $this->loadModel('CreditMemoWithSale');
        $this->loadModel('GeneralLedger');
        $cmWsale = $this->CreditMemoWithSale->read(null, $id);
        if ($cmWsale['CreditMemoWithSale']['status'] == 1) {
            mysql_query("UPDATE sales_orders SET balance = balance + " . $cmWsale['CreditMemoWithSale']['total_price'] . " WHERE id=" . $cmWsale['CreditMemoWithSale']['sales_order_id']);
            mysql_query("UPDATE credit_memos SET balance = balance + " . $cmWsale['CreditMemoWithSale']['total_price'] . ", total_amount_invoice = (IF((total_amount_invoice - " . $cmWsale['CreditMemoWithSale']['total_price'] . ") < 0,0,(total_amount_invoice - " . $cmWsale['CreditMemoWithSale']['total_price'] . "))) WHERE id=" . $cmWsale['CreditMemoWithSale']['credit_memo_id']);
            $this->CreditMemoWithSale->updateAll(
                    array('CreditMemoWithSale.status' => 0), array('CreditMemoWithSale.id' => $id)
            );
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                    array('GeneralLedger.credit_memo_with_sale_id' => $id)
            );
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Void Apply to Invoice', $id);
            $result['result']   = 1;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Return Receipt', 'Void Apply to Invoice (Error Status)', $id);
            $result['result']   = 2;
        }
        echo json_encode($result);
        exit;
    }
    
    function editUnitPrice(){
        $this->layout = 'ajax';
    }
    
    function getProductFromSales($id = null){
        $this->layout = 'ajax';
        $result = array();
        if (empty($id)) {
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
        $user = $this->getCurrentUser();
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
        $index   = '';
        // Get Product
        $sqlSalesDetail  = mysql_query("SELECT products.is_lots AS is_lots, products.is_expired_date AS is_expired_date, products.id AS product_id, products.code AS code, products.barcode AS barcode, products.name AS name, products.small_val_uom AS small_val_uom, products.price_uom_id AS price_uom_id, sd.qty AS qty, sd.qty_free AS qty_free, sd.qty_uom_id AS qty_uom_id, sd.conversion AS conversion, sd.discount_id AS discount_id, sd.discount_amount AS discount_amount, sd.discount_percent AS discount_percent, sd.unit_price AS unit_price, sd.total_price AS total_price, sd.note AS note, sd.lots_number AS lots_number, sd.expired_date AS expired_date, so.customer_id AS customer_id FROM sales_order_details AS sd INNER JOIN sales_orders AS so ON so.id = sd.sales_order_id INNER JOIN products ON products.id = sd.product_id WHERE sd.sales_order_id = ".$id.";");
        while($rowDetail = mysql_fetch_array($sqlSalesDetail)){
            $index     = rand();
            $productName = str_replace('"', '&quot;', $rowDetail['name']);
            $sqlProCus = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$rowDetail['product_id']." AND customer_id = ".$rowDetail['customer_id']." ORDER BY created DESC LIMIT 1");
            if(mysql_num_rows($sqlProCus)){
                $rowProCus   = mysql_fetch_array($sqlProCus);
                $productName = str_replace('"', '&quot;', $rowProCus['name']);
            }
            // Caculate Discount
            $discountAmt = $rowDetail['discount_amount'];
            // Open Tr
            $rowLbl .= '<tr class="tblCMList">';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center;padding: 0px; height: 30px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:7%; text-align: left; padding: 5px;"><span class="lblUPC">'.$rowDetail['barcode'].'</span></td>';
            // SKU
            $rowLbl .= '<td style="width:7%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowDetail['code'].'</span></td>';
            // Product
            $rowLbl .= '<td style="width:14%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" value="'.$rowDetail['product_id'].'" name="product_id[]" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" value="" name="service_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['discount_id'].'" name="discount_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$discountAmt.'" name="discount_amount[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['discount_percent'].'" name="discount_percent[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['conversion'].'" name="cm_conversion[]" class="cm_conversion" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['small_val_uom'].'" name="small_val_uom[]" class="small_val_uom" />';
            $rowLbl .= '<input type="hidden" value="'.$rowDetail['note'].'" name="note[]" id="note" readonly="readonly" class="note" />';
            $rowLbl .= '<input type="hidden" name="type[]" class="typeCM" />';
            $rowLbl .= '<input type="hidden" value="1" class="isReferenceCM" />';
            $rowLbl .= '<input type="hidden" class="orgProName" value="PUC: '.$rowDetail['barcode'].'<br/><br/>SKU: '.$rowDetail['code'].'<br/><br/>Name: '.str_replace('"', '&quot;', $rowDetail['name']).'" />';
            $rowLbl .= '<input type="text" id="productName_'.$index.'" value="'.$rowDetail['name'].'" id="product" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '<img alt="Information" src="'.$this->webroot.'img/button/view.png" class="btnProductCMInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Information\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '<div class="inputContainer divStartCM" style="width:100%; margin-top: 3px; display: none;">';
            $rowLbl .= '<input type="text" name="start[]" id="startCM_'.rand().'" class="startCM" readonly="readonly" style="width: 75%; height: 25px;" placeholder="'.TABLE_START_DATE.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$rowDetail['price_uom_id']."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$rowDetail['price_uom_id'].")
                                ORDER BY conversion ASC");
            $i = 1;
            $length = mysql_num_rows($query);
            $optionUom = "";
            while($data=mysql_fetch_array($query)){
                $priceLbl   = "";
                $selected   = "";
                $isMain     = "other";
                $isSmall    = 0;
                // Check With Qty UOM Id
                if($data['id'] == $rowDetail['qty_uom_id']){   
                    $selected = ' selected="selected" ';
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
                        $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                    }
                }else{
                    $priceLbl .= 'price-uom-1="0" price-uom-2="0"';
                }
                $optionUom .= '<option '.$priceLbl.' '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" value="'.$data['id'].'" conversion="'.$data['conversion'].'">'.$data['name'].'</option>';
                $i++;
            }
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lot Number
            $lotDispaly  = '';
            $lotHidden   = 'display: none;';
            $lotRequired = '';
            $lotsNumber  = $rowDetail['lots_number']!=""?$rowDetail['lots_number']:'0';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($rowDetail['is_lots'] == 1){
                $lotHidden   = '';
                $lotRequired = 'validate[required]';
            }
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="lots_number_'.$index.'" name="lots_number[]" value="" style="width:90%; '.$lotHidden.'" class="lots_number '.$lotRequired.'" value="'.$lotsNumber.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expired Date
            $expDisplay  = '';
            if($allowExpired == false){
                $expDisplay = 'display: none;';
            }
            if($rowDetail['is_expired_date'] == 1){
                $classExp = 'class="expired_date validate[required]"';
                $disExp   = '';
            }else{
                $classExp = '';
                $disExp   = 'visibility: hidden;';
            }
            $dateExp = $rowDetail['expired_date']!=""?$rowDetail['expired_date']:'0000-00-00';
            $rowLbl .= '<td style="width:10%; text-align: center;padding: 0px;'.$expDisplay.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="expired_date_'.$index.'" name="expired_date[]" value="'.$dateExp.'" readonly="readonly" style="width:90%;'.$disExp.'" '.$classExp.' />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty
            $rowLbl .= '<td style="width:6%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$rowDetail['qty'].'" id="qty_'.$index.'" name="qty[]" style="width:70%;" class="qty interger" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:6%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$rowDetail['qty_free'].'" id="qty_free_'.$index.'" name="qty_free[]" style="width:70%;" class="qty_free interger" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" '.$readonly.' value="'.number_format($rowDetail['unit_price'], 2).'" id="unit_price_'.$index.'" name="unit_price[]" style="width:70%;" class="float unit_price" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                $btnDel = 'display: none;';
                if($discountAmt > 0){
                    $btnDel = '';
                }
                $disDisplay  = '<input type="text" value="'.number_format($discountAmt, 2).'" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountCM float" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscount" align="absmiddle" style="cursor: pointer; '.$btnDel.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" value="'.number_format($discountAmt, 2).'" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountCM float" value="0" style="width: 60%;" readonly="readonly" />';
            }
            $rowLbl .= '<td style="width:8%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; text-align: center; padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" value="'.number_format($rowDetail['total_price'], 2).'" id="h_total_price_'.$index.'" class="h_total_price float" name="h_total_price[]" />';
            $rowLbl .= '<input type="text" '.$readonly.' value="'.number_format(($rowDetail['total_price'] - $discountAmt), 2).'" id="total_price_'.$index.'" name="total_price[]" style="width:84%" class="float total_price" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:4%">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        // Get Service
        $sqlSalesService  = mysql_query("SELECT services.code AS code, services.name AS name, uoms.abbr AS uom, uoms.id AS uom_id, sd.service_id AS service_id, sd.qty AS qty, sd.qty_free AS qty_free, sd.discount_id AS discount_id, sd.discount_amount AS discount_amount, sd.discount_percent AS discount_percent, sd.unit_price AS unit_price, sd.total_price AS total_price, sd.note AS note, sd.type AS type, sd.start AS start FROM sales_order_services AS sd INNER JOIN services ON services.id = sd.service_id INNER JOIN uoms ON uoms.id = services.uom_id WHERE sd.sales_order_id = ".$id.";");
        while($rowService = mysql_fetch_array($sqlSalesService)){
            $index   = rand();
            // Open Tr
            $rowLbl .= '<tr class="tblCMList">';
            // Index
            $rowLbl .= '<td class="first" style="width:4%; text-align: center;padding: 0px; height: 30px;">'.++$index.'</td>';
            // UPC
            $rowLbl .= '<td style="width:7%; text-align: left; padding: 5px;"><span class="lblUPC"></span></td>';
            // SKU
            $rowLbl .= '<td style="width:7%; text-align: left; padding: 5px;"><span class="lblSKU">'.$rowService['code'].'</span></td>';
            // Product
            $startCSS  = '';
            $startRqu  = '';
            $dateStart = '';
            if($rowService['start'] != '' && $rowService['start'] != '0000-00-00'){
                $dateStart = $this->Helper->dateShort($rowService['start']);
            }
            if($rowService['type'] == 1){
                $startCSS = 'display: none;';
            } else {
                $startRqu = 'validate[required]';
            }
            $rowLbl .= '<td style="width:14%; text-align: left; padding: 5px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" id="product_id_'.$index.'" class="product_id" value="" name="product_id[]" />';
            $rowLbl .= '<input type="hidden" id="service_id_'.$index.'" value="'.$rowService['service_id'].'" name="service_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowService['discount_id'].'" name="discount_id[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowService['discount_amount'].'" name="discount_amount[]" />';
            $rowLbl .= '<input type="hidden" value="'.$rowService['discount_percent'].'" name="discount_percent[]" />';
            $rowLbl .= '<input type="hidden" value="1" name="cm_conversion[]" class="cm_conversion" />';
            $rowLbl .= '<input type="hidden" value="1" name="small_val_uom[]" class="small_val_uom" />';
            $rowLbl .= '<input type="hidden" value="'.$rowService['note'].'" name="note[]" id="note" readonly="readonly" class="note" />';
            $rowLbl .= '<input type="hidden" value="'.$rowService['type'].'" name="type[]" class="typeCM" />';
            $rowLbl .= '<input type="hidden" value="1" class="isReferenceCM" />';
            $rowLbl .= '<input type="text" id="productName_'.$index.'" value="'.$rowService['name'].'" id="product" readonly="readonly" name="product[]" class="product validate[required]" style="width: 75%;" />';
            $rowLbl .= '<img alt="Note" src="'.$this->webroot.'img/button/note.png" class="noteAddCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Note\')" />';
            $rowLbl .= '</div>';
            $rowLbl .= '<div class="inputContainer divStartCM" style="width:100%; margin-top: 3px; '.$startCSS.'">';
            $rowLbl .= '<input type="text" value="'.$dateStart.'" name="start[]" id="startCM" class="startCM '.$startRqu.'" readonly="readonly" style="width: 75%; height: 25px;" placeholder="'.TABLE_START_DATE.'" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // UOM
            $optionUom = '<option value="'.$rowService['uom_id'].'" conversion="1" selected="selected">'.$rowService['uom'].'</option>';
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<select id="qty_uom_id_'.$index.'" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id">'.$optionUom.'</select>';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Lot Number
            $lotDispaly = '';
            $expDisplay = '';
            if($allowLots == false){
                $lotDispaly = 'display: none;';
            }
            if($allowExpired == false){
                $expDisplay = 'display: none;';
            }
            $rowLbl .= '<td style="width:7%; text-align: center;padding: 0px;'.$lotDispaly.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="lots_number_'.$index.'" name="lots_number[]" value="" style="width:90%; display: none;" class="lots_number" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Expired Date
            $rowLbl .= '<td style="width:10%; text-align: center;padding: 0px;'.$expDisplay.'">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" id="expired_date_'.$index.'" name="expired_date[]" value="" readonly="readonly" style="width:90%; display: none;" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty
            $rowLbl .= '<td style="width:6%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$rowService['qty'].'" id="qty_'.$index.'" name="qty[]" style="width:70%;" class="qty interger" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Qty Free
            $rowLbl .= '<td style="width:6%; text-align: center;padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" value="'.$rowService['qty_free'].'" id="qty_free_'.$index.'" name="qty_free[]" style="width:70%;" class="qty_free interger" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Unit Price
            $rowLbl .= '<td style="width:9%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="text" '.$readonly.' value="'.number_format($rowService['unit_price'], 2).'" id="unit_price_'.$index.'" name="unit_price[]" style="width:70%;" class="float unit_price" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Discount
            // Check Permission Discount
            if($allowProductDiscount){
                $btnDel = 'display: none;';
                if($rowService['discount_amount'] > 0){
                    $btnDel = '';
                }
                $disDisplay  = '<input type="text" value="'.number_format($rowService['discount_amount'], 2).'" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountCM float" style="width: 60%;" readonly="readonly" />';
                $disDisplay .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveDiscount" align="absmiddle" style="cursor: pointer; '.$btnDel.'" onmouseover="Tip(\'Remove\')" />';
            }else{
                $disDisplay = '<input type="hidden" value="'.number_format($rowService['discount_amount'], 2).'" id="discount_'.$index.'" name="discount[]" class="discount btnDiscountCM float" value="0" style="width: 60%;" readonly="readonly" />';
            }
            $rowLbl .= '<td style="width:8%; padding: 0px; text-align: center">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= $disDisplay;
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Total Price
            $rowLbl .= '<td style="width:9%; text-align: center; padding: 0px;">';
            $rowLbl .= '<div class="inputContainer" style="width:100%">';
            $rowLbl .= '<input type="hidden" value="'.number_format($rowService['total_price'], 2).'" id="h_total_price_'.$index.'" class="h_total_price float" name="h_total_price[]" />';
            $rowLbl .= '<input type="text" '.$readonly.' value="'.number_format(($rowService['total_price'] - $rowService['discount_amount']), 2).'" id="total_price_'.$index.'" name="total_price[]" style="width:84%" class="float total_price" />';
            $rowLbl .= '</div>';
            $rowLbl .= '</td>';
            // Button Remove
            $rowLbl .= '<td style="width:4%">';
            $rowLbl .= '<img alt="Remove" src="'.$this->webroot.'img/button/cross.png" class="btnRemoveCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip(\'Remove\')" />';
            $rowLbl .= '</td>';
            // Close Tr
            $rowLbl .= '</tr>';
        }
        $rowList['error']  = 0;
        $rowList['result'] = $rowLbl;
        echo json_encode($rowList);
        exit;
    }
    
    function searchSalesInvoice(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('SalesOrder');
        $userPermission = 'SalesOrder.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].') AND SalesOrder.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id ='.$user['User']['id'].')';
        $salesOrders = $this->SalesOrder->find('all', array(
                    'conditions' => array('OR' => array(
                            'SalesOrder.so_code LIKE' => '%' . $this->params['url']['q'] . '%'
                        ), 
                        'SalesOrder.status' => 2,
                        $userPermission
                    ),
                    'limit' => $this->params['url']['limit']
                ));

        $this->set(compact('salesOrders'));
    }
    
    function invoiceDiscount(){
        $this->layout = 'ajax';
    }
    
    function viewCreditMemoIssued(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 504 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 504;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function addReason(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Reason');
            $result = array();
            if ($this->Helper->checkDouplicate('name', 'reasons', $this->data['Reason']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'CM Reason', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Reason->create();
                $this->data['Reason']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Reason']['created']    = $dateNow;
                $this->data['Reason']['created_by'] = $user['User']['id'];
                $this->data['Reason']['is_active'] = 1;
                if ($this->Reason->save($this->data)) {
                    $reasonId = $this->Reason->id;
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Reason'], 'reasons');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'reasons';
                    $restCode[$r]['actodo']     = 'is';
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'CM Reason', 'Save Quick Add New', $reasonId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $reasons = ClassRegistry::init('Reason')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($reasons AS $reason){
                        $selected = '';
                        if($reason['Reason']['id'] == $reasonId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$reason['Reason']['id'].'" '.$selected.'>'.$reason['Reason']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'CM Reason', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'CM Reason', 'Quick Add New');
    }

}

?>