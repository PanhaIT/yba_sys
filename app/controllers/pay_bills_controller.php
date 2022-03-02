<?php

class PayBillsController extends AppController {

    var $name = 'PayBills';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pay Bill', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.pay_bill_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('companies', 'branches', 'locations', 'vendors', 'cashBankAccountId'));
    }

    function ajax($companyId = null, $branchId = null, $vendorId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'vendorId'));
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['PayBill']['date'])) {
            $this->data['PayBill']['date'] = $this->Helper->dateConvert($this->data['PayBill']['date']);
        }
        if (isset($_POST['amount_us']) && sizeof($_POST['amount_us']) > 0) {
            // check status purchase_orders
            $arrPoIdList = array();
            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if ($_POST['amount_us'][$i] > 0 || $_POST['amount_other'][$i] > 0) {
                    $arrPoIdList[] = $_POST['id'][$i];
                }
            }
            $queryPoIdList = mysql_query("SELECT po_code,modified,(SELECT CONCAT_WS(first_name,last_name) FROM users WHERE id=purchase_orders.modified_by) AS name FROM purchase_orders WHERE status<1 AND id IN (" . implode(",", $arrPoIdList) . ")");
            if (mysql_num_rows($queryPoIdList)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Pay Bill', 'Save Add New (Error PB paid ready)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                echo '<br />';
                while ($dataPoIdList = mysql_fetch_array($queryPoIdList)) {
                    echo '<br />PB #' . $dataPoIdList['po_code'] . ' has been modified by ' . $dataPoIdList['name'] . ' on ' . $dataPoIdList['modified'];
                }
                exit();
            }
//            $r = 0;
//            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $cashBankAccount = ClassRegistry::init('AccountType')->findById(13);
            $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
            // save to table pay_bills
            $this->PayBill->create();
            $payBill = array();
            $payBill['PayBill']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $payBill['PayBill']['date'] = $this->data['PayBill']['date'];
            $payBill['PayBill']['company_id'] = $_POST['company_id'];
            $payBill['PayBill']['branch_id']  = $_POST['branch_id'];
            $payBill['PayBill']['vendor_id']  = $_POST['vendor_id'];
            $payBill['PayBill']['deposit_to'] = $cashBankAccountId;
            $payBill['PayBill']['reference']  = $this->data['PayBill']['reference'];
            $payBill['PayBill']['note']       = $this->data['PayBill']['note'];
            $payBill['PayBill']['created']    = $dateNow;
            $payBill['PayBill']['created_by'] = $user['User']['id'];
            $this->PayBill->save($payBill);
            $payBillId = $this->PayBill->getLastInsertId();
            // Get Module Code
            $modCode = $this->Helper->getModuleCode($this->data['PayBill']['reference'], $payBillId, 'reference', 'pay_bills', 'is_active = 1 AND branch_id = '.$_POST['branch_id']);
            // Updaet Module Code
            mysql_query("UPDATE pay_bills SET reference = '".$modCode."' WHERE id = ".$payBillId);
            // Convert to REST
//            $restCode[$r] = $this->Helper->convertToDataSync($payBill['PayBill'], 'pay_bills');
//            $restCode[$r]['reference'] = $modCode;
//            $restCode[$r]['modified']  = $dateNow;
//            $restCode[$r]['dbtodo']    = 'pay_bills';
//            $restCode[$r]['actodo']    = 'is';
//            $r++;
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Pay Bill', 'Save Add New', $payBillId);
            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if ($_POST['amount_us'][$i] > 0 || $_POST['amount_other'][$i] > 0) {
                    $this->loadModel('PurchaseOrder');
                    $this->loadModel('Pv');
                    $purchase = ClassRegistry::init('PurchaseOrder')->find("first", array('conditions' => array('PurchaseOrder.id' => $_POST['id'][$i])));
                    $purchaseOrder = array();
                    $purchaseOrder['PurchaseOrder']['id'] = $_POST['id'][$i];
                    $purchaseOrder['PurchaseOrder']['modified'] = $dateNow;
                    $purchaseOrder['PurchaseOrder']['modified_by'] = $user['User']['id'];
                    $purchaseOrder['PurchaseOrder']['balance'] = $_POST['balance_us'][$i];
                    if ($this->PurchaseOrder->save($purchaseOrder)) {
                        // Convert to REST
//                        $restCode[$r]['balance']     = $_POST['balance_us'][$i];
//                        $restCode[$r]['modified']    = $dateNow;
//                        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                        $restCode[$r]['dbtodo'] = 'purchase_orders';
//                        $restCode[$r]['actodo'] = 'ut';
//                        $restCode[$r]['con']    = "sys_code = '".$purchase['PurchaseOrder']['sys_code']."'";
//                        $r++;
                        // save to table pay_bill_details
                        $this->loadModel('PayBillDetail');
                        $this->PayBillDetail->create();
                        $payBillDetail = array();
                        $payBillDetail['PayBillDetail']['pay_bill_id']       = $payBillId;
                        $payBillDetail['PayBillDetail']['purchase_order_id'] = $_POST['id'][$i];
                        $payBillDetail['PayBillDetail']['amount_due']        = $_POST['amount_due'][$i];
                        $payBillDetail['PayBillDetail']['paid']              = $_POST['amount_us'][$i];
                        $payBillDetail['PayBillDetail']['paid_other']        = $_POST['amount_other'][$i];
                        $payBillDetail['PayBillDetail']['discount']          = $_POST['discount_us'][$i];
                        $payBillDetail['PayBillDetail']['discount_other']    = $_POST['discount_other'][$i];
                        $payBillDetail['PayBillDetail']['balance']           = $_POST['balance_us'][$i];
                        $payBillDetail['PayBillDetail']['due_date']          = $_POST['due_date'][$i]!=''?:'0000-00-00';
                        $this->PayBillDetail->save($payBillDetail);
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($payBillDetail['PayBillDetail'], 'pay_bill_details');
//                        $restCode[$r]['dbtodo']    = 'pay_bill_details';
//                        $restCode[$r]['actodo']    = 'is';
//                        $r++;
                        
                        // Purchase Order Receipt
                        $this->Pv->create();
                        $purchaseOrderReceipt = array();
                        $purchaseOrderReceipt['Pv']['sys_code']          = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $purchaseOrderReceipt['Pv']['purchase_order_id'] = $_POST['id'][$i];
                        $purchaseOrderReceipt['Pv']['branch_id']         = $_POST['branch_id'];
                        $purchaseOrderReceipt['Pv']['chart_account_id']  = $cashBankAccountId;
                        $purchaseOrderReceipt['Pv']['pv_code']           = '';
                        $purchaseOrderReceipt['Pv']['amount_us']         = $_POST['amount_us'][$i];
                        $purchaseOrderReceipt['Pv']['amount_other']      = $_POST['amount_other'][$i];
                        $purchaseOrderReceipt['Pv']['discount']          = $_POST['discount_us'][$i];
                        $purchaseOrderReceipt['Pv']['discount_other']    = $_POST['discount_other'][$i];
                        $purchaseOrderReceipt['Pv']['total_amount']      = $_POST['amount_due'][$i];
                        $purchaseOrderReceipt['Pv']['balance']           = $_POST['balance_us'][$i];
                        $purchaseOrderReceipt['Pv']['pay_date']          = $this->data['PayBill']['date']!=''?$this->data['PayBill']['date']:'0000-00-00';
                        $purchaseOrderReceipt['Pv']['due_date']          = $_POST['due_date'][$i]!=''?$_POST['due_date'][$i]:'0000-00-00';
                        $purchaseOrderReceipt['Pv']['created_by']        = $user['User']['id'];
                        if ($this->Pv->save($purchaseOrderReceipt)) {
                            $receiptId = $this->Pv->id;
                            $this->loadModel('GeneralLedger');
                            $this->loadModel('GeneralLedgerDetail');
                            $this->loadModel('AccountType');
                            $this->loadModel('Company');

                            $purchaseOrder  = $this->PurchaseOrder->findById($_POST['id'][$i]);
                            $company        = $this->Company->read(null, $purchaseOrder['PurchaseOrder']['company_id']);
                            $classId        = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $purchaseOrder['PurchaseOrder']['location_group_id']);
                            $exchangeRate   = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $this->data['PayBill']['exchange_rate_id'])));
                            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_purchase'] > 0){
                                $totalPaidOther = $_POST['amount_other'][$i]   / $exchangeRate['ExchangeRate']['rate_purchase'];
                                $totalDisOther  = $_POST['discount_other'][$i] / $exchangeRate['ExchangeRate']['rate_purchase'];
                            } else {
                                $totalPaidOther = 0;
                                $totalDisOther  = 0;
                            }
                            
                            $totalPaidCredit = $_POST['amount_us'][$i]    + $totalPaidOther;
                            $totalDis        = $_POST['discount_us'][$i]  + $totalDisOther;
                            $totalPaid       = $_POST['amount_us'][$i]    + $totalPaidOther + $totalDis;
                            // Update Code & Change Receipt Generate Code
                            $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $purchaseOrder['PurchaseOrder']['branch_id'])));
                            $repCode    = date("y").$modComCode['ModuleCodeBranch']['pb_rep_code'];
                            // Get Module Code
                            $modRepCode = $this->Helper->getModuleCode($repCode, $receiptId, 'pv_code', 'pvs', 'is_void = 0 AND branch_id = '.$_POST['branch_id']);
                            // Updaet Module Code
                            mysql_query("UPDATE pvs SET pv_code = '".$modRepCode."' WHERE id = ".$receiptId);
                            // Convert to REST
//                            $restCode[$r] = $this->Helper->convertToDataSync($purchaseOrderReceipt['Pv'], 'pvs');
//                            $restCode[$r]['pv_code']   = $modRepCode;
//                            $restCode[$r]['modified']  = $dateNow;
//                            $restCode[$r]['dbtodo']    = 'pvs';
//                            $restCode[$r]['actodo']    = 'is';
//                            $r++;
                            // Save General Ledger Detail
                            $this->GeneralLedger->create();
                            $generalLedger = array();
                            $generalLedger['GeneralLedger']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $generalLedger['GeneralLedger']['purchase_order_id'] = $_POST['id'][$i];
                            $generalLedger['GeneralLedger']['pv_id']       = $receiptId;
                            $generalLedger['GeneralLedger']['pay_bill_id'] = $payBillId;
                            $generalLedger['GeneralLedger']['date']        = $this->data['PayBill']['date']!=''?$this->data['PayBill']['date']:'0000-00-00';
                            $generalLedger['GeneralLedger']['reference']   = $modRepCode;
                            $generalLedger['GeneralLedger']['created_by']  = $user['User']['id'];
                            $generalLedger['GeneralLedger']['is_sys'] = 1;
                            $generalLedger['GeneralLedger']['is_adj'] = 0;
                            $generalLedger['GeneralLedger']['is_active'] = 1;
                            if ($this->GeneralLedger->save($generalLedger)) {
                                $glId = $this->GeneralLedger->id;
                                // Convert to REST
//                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedger['GeneralLedger'], 'general_ledgers');
//                                $restCode[$r]['modified']  = $dateNow;
//                                $restCode[$r]['dbtodo']    = 'general_ledgers';
//                                $restCode[$r]['actodo']    = 'is';
//                                $r++;
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail = array();
                                $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashBankAccountId;
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id']        = $purchaseOrder['PurchaseOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']         = $purchaseOrder['PurchaseOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_id']       = $purchaseOrder['PurchaseOrder']['location_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']      = 'Pay Bill';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']     = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit']    = $totalPaidCredit;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']      = 'ICS: PB# ' . $purchaseOrder['PurchaseOrder']['po_code'];
                                $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $purchaseOrder['PurchaseOrder']['vendor_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id']  = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                // Convert to REST
//                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                                $restCode[$r]['dbtodo']    = 'general_ledger_details';
//                                $restCode[$r]['actodo']    = 'is';
//                                $r++;
                                
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $purchaseOrder['PurchaseOrder']['ap_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']     = $totalPaid;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit']    = 0;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                // Convert to REST
//                                $restCode[$r] = $this->Helper->convertToDataSync($generalLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                                $restCode[$r]['dbtodo']    = 'general_ledger_details';
//                                $restCode[$r]['actodo']    = 'is';
//                                $r++;
                                
                                /* General Ledger Detail Total Discount */
                                if ($totalDis > 0) {
                                    //Chart Account Discount
                                    $paidDiscAccount = $this->AccountType->findById(11);
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $paidDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit']     = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit']    = $totalDis;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo']      = 'ICS: PB # ' . $purchaseOrder['PurchaseOrder']['po_code'] . ' Total Discount';
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                    // Convert to REST
//                                    $restCode[$r] = $this->Helper->convertToDataSync($generalLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                                    $restCode[$r]['dbtodo']    = 'general_ledger_details';
//                                    $restCode[$r]['actodo']    = 'is';
//                                    $r++;
                                }
                            }
                        }
                    }
                }
            }
            // Save File Send
//            $this->Helper->sendFileToSync($restCode, 0, 0);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Pay Bill', 'Save Add New (Error)');
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }

}

?>