<?php

class ReceivePaymentsController extends AppController {

    var $name = 'ReceivePayments';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.receive_pay_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact('companies', 'branches', 'customerGroups'));
    }

    function ajax($companyId = null, $branchId = null, $customerId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'customerId'));
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result = array();
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['CustomerPayment']['date'])) {
            $this->data['CustomerPayment']['date'] = $this->Helper->dateConvert($this->data['CustomerPayment']['date']);
        }
        if (isset($_POST['amount_us']) && sizeof($_POST['amount_us']) > 0) {
            // check status sales_orders
            $arrSoIdList = array();
            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if (($_POST['amount_us'][$i] != '' && $_POST['amount_us'][$i] > 0) || ($_POST['amount_other'][$i] != '' && $_POST['amount_other'][$i] > 0)) {
                    $arrSoIdList[] = $_POST['id'][$i];
                }
            }
            $querySoIdList = mysql_query("SELECT so_code,modified,(SELECT CONCAT_WS(first_name,last_name) FROM users WHERE id=sales_orders.modified_by) AS name FROM sales_orders WHERE status<1 AND id IN (" . implode(",", $arrSoIdList) . ")") or die(mysql_error());
            if (mysql_num_rows($querySoIdList)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment', 'Save Add New (Error Sales Invoice paid ready)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                echo '<br />';
                while ($dataSoIdList = mysql_fetch_array($querySoIdList)) {
                    echo '<br />Invoice #' . $dataSoIdList['so_code'] . ' has been modified by ' . $dataSoIdList['name'] . ' on ' . $dataSoIdList['modified'];
                }
                exit();
            }
            $dateNow  = date("Y-m-d H:i:s");
            $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
            $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
            // save to table receive_payments
            $this->ReceivePayment->create();
            $receivePayment = array();
            $receivePayment['ReceivePayment']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $receivePayment['ReceivePayment']['date']        = $this->data['CustomerPayment']['date'];
            $receivePayment['ReceivePayment']['company_id']  = $_POST['company_id'];
            $receivePayment['ReceivePayment']['branch_id']   = $_POST['branch_id'];
            $receivePayment['ReceivePayment']['customer_id'] = $_POST['customer_id'];
            $receivePayment['ReceivePayment']['deposit_to']  = $cashBankAccountId;
            $receivePayment['ReceivePayment']['reference']   = $this->data['CustomerPayment']['reference'];
            $receivePayment['ReceivePayment']['note']        = $this->data['CustomerPayment']['note'];
            $receivePayment['ReceivePayment']['created']     = $dateNow;
            $receivePayment['ReceivePayment']['created_by']  = $user['User']['id'];
            $this->ReceivePayment->save($receivePayment);
            $receivePaymentId = $this->ReceivePayment->getLastInsertId();
            // Get Module Code
            $modCode = $this->Helper->getModuleCode($this->data['CustomerPayment']['reference'], $receivePaymentId, 'reference', 'receive_payments', 'is_active = 1 AND branch_id = '.$_POST['branch_id']);
            // Updaet Module Code
            mysql_query("UPDATE receive_payments SET reference = '".$modCode."' WHERE id = ".$receivePaymentId);
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment', 'Save Add New', $receivePaymentId);
            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if ($_POST['amount_us'][$i] != '' && $_POST['amount_us'][$i] != 0) {
                    $this->loadModel('SalesOrder');
                    $sales = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.id' => $_POST['id'][$i])));
                    $salesOrder = array();
                    $salesOrder['SalesOrder']['id']       = $_POST['id'][$i];
                    $salesOrder['SalesOrder']['modified'] = $dateNow;
                    $salesOrder['SalesOrder']['modified_by'] = $user['User']['id'];
                    $salesOrder['SalesOrder']['balance']     = $_POST['balance_us'][$i];
                    if ($this->SalesOrder->save($salesOrder)) {
                        // save to table receive payment details
                        $this->loadModel('ReceivePaymentDetail');
                        $this->loadModel('SalesOrderReceipt');
                        
                        // Sales Order Receipt
                        $this->SalesOrderReceipt->create();
                        $salesOrderReceipt = array();
                        $salesOrderReceipt['SalesOrderReceipt']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $salesOrderReceipt['SalesOrderReceipt']['sales_order_id']     = $_POST['id'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['branch_id']          = $_POST['branch_id'];
                        $salesOrderReceipt['SalesOrderReceipt']['exchange_rate_id']   = $this->data['CustomerPayment']['exchange_rate_id'];
                        $salesOrderReceipt['SalesOrderReceipt']['currency_id'] = $this->data['CustomerPayment']['currency_id'];
                        $salesOrderReceipt['SalesOrderReceipt']['chart_account_id']   = $cashBankAccountId;
                        $salesOrderReceipt['SalesOrderReceipt']['receipt_code']     = $modCode;
                        $salesOrderReceipt['SalesOrderReceipt']['amount_us']        = $_POST['amount_us'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['amount_other']     = $_POST['amount_other'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['discount_us']      = $_POST['discount_us'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['discount_other']   = $_POST['discount_other'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['total_amount']     = $_POST['amount_due'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['balance']          = $_POST['balance_us'][$i];
                        $salesOrderReceipt['SalesOrderReceipt']['change']           = 0;
                        $salesOrderReceipt['SalesOrderReceipt']['pay_date']         = $this->data['CustomerPayment']['date']!=''?$this->data['CustomerPayment']['date']:'0000-00-00';
                        $salesOrderReceipt['SalesOrderReceipt']['due_date']         = $_POST['due_date'][$i]!=''?$_POST['due_date'][$i]:'0000-00-00';
                        $salesOrderReceipt['SalesOrderReceipt']['created']          = $dateNow;
                        $salesOrderReceipt['SalesOrderReceipt']['created_by']       = $user['User']['id'];
                        if ($this->SalesOrderReceipt->save($salesOrderReceipt)) {
                            $receiptId = $this->SalesOrderReceipt->id;
                            // Receive Payment Detail
                            $this->ReceivePaymentDetail->create();
                            $receivePaymentDetail = array();
                            $receivePaymentDetail['ReceivePaymentDetail']['receive_payment_id'] = $receivePaymentId;
                            $receivePaymentDetail['ReceivePaymentDetail']['sales_order_id']     = $_POST['id'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['sales_order_receipt_id']  = $receiptId;
                            $receivePaymentDetail['ReceivePaymentDetail']['amount_due']         = $_POST['amount_due'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['paid']               = $_POST['amount_us'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['paid_other']         = $_POST['amount_other'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['discount']           = $_POST['discount_us'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['discount_other']     = $_POST['discount_other'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['balance']            = $_POST['balance_us'][$i];
                            $receivePaymentDetail['ReceivePaymentDetail']['due_date']           = $_POST['due_date'][$i]!=''?$_POST['due_date'][$i]:'0000-00-00';
                            $this->ReceivePaymentDetail->save($receivePaymentDetail);
                            // Load Model
                            $this->loadModel('GeneralLedger');
                            $this->loadModel('GeneralLedgerDetail');
                            $this->loadModel('Company');
                            $this->loadModel('AccountType');
                            
                            $salesOrder = $this->SalesOrder->findById($_POST['id'][$i]);
                            $company    = $this->Company->read(null, $salesOrder['SalesOrder']['company_id']);
                            $classId    = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $salesOrder['SalesOrder']['location_group_id']);
                            $exchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array("conditions" => array("ExchangeRate.id" => $this->data['CustomerPayment']['exchange_rate_id'])));
                            if(!empty($exchangeRate) && $exchangeRate['ExchangeRate']['rate_to_sell'] > 0){
                                $totalPaidOther = $_POST['amount_other'][$i]   / $exchangeRate['ExchangeRate']['rate_to_sell'];
                                $totalDisOther  = $_POST['discount_other'][$i] / $exchangeRate['ExchangeRate']['rate_to_sell'];
                            } else {
                                $totalPaidOther = 0;
                                $totalDisOther  = 0;
                            }
                            
                            $totalPaidDebit = $_POST['amount_us'][$i]    + $totalPaidOther;
                            $totalDis       = $_POST['discount_us'][$i]  + $totalDisOther;
                            $totalPaid      = $_POST['amount_us'][$i]    + $totalPaidOther + $totalDis;
                            // Save General Ledger Detail
                            $this->GeneralLedger->create();
                            $generalLedger = array();
                            $generalLedger['GeneralLedger']['sys_code']       = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            $generalLedger['GeneralLedger']['sales_order_id'] = $_POST['id'][$i];
                            $generalLedger['GeneralLedger']['sales_order_receipt_id'] = $receiptId;
                            $generalLedger['GeneralLedger']['receive_payment_id'] = $receivePaymentId;
                            $generalLedger['GeneralLedger']['date']       = $this->data['CustomerPayment']['date'];
                            $generalLedger['GeneralLedger']['reference']  = $modCode;
                            $generalLedger['GeneralLedger']['created']    = $dateNow;
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
                                $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $salesOrder['SalesOrder']['company_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $salesOrder['SalesOrder']['branch_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $salesOrder['SalesOrder']['location_group_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'Receive Payment';
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalPaidDebit;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $salesOrder['SalesOrder']['so_code'];
                                $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $salesOrder['SalesOrder']['customer_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['class_id']    = $classId;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                
                                $this->GeneralLedgerDetail->create();
                                $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesOrder['SalesOrder']['ar_id'];
                                $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                                $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                                $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                
                                /* General Ledger Detail Total Discount */
                                if ($totalDis > 0) {
                                    //Chart Account Discount
                                    $paidDiscAccount = $this->AccountType->findById(11);
                                    $this->GeneralLedgerDetail->create();
                                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $paidDiscAccount['AccountType']['chart_account_id'];
                                    $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $totalDis;
                                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                                    $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: INV # ' . $salesOrder['SalesOrder']['so_code'] . ' Total Discount';
                                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                                }
                            }
                        }
                    }
                }
            }
            $result['id']    = $receivePaymentId;
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment', 'Save Add New (Error)');
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
    }
    
    function printReceipt($id = null){
        $this->layout = 'ajax';
        if (!empty($id)) {
            $this->data = $this->ReceivePayment->read(null, $id);
            if (!empty($this->data)) {
                $paymentDetails = ClassRegistry::init('ReceivePaymentDetail')->find("all", array('conditions' => array('ReceivePaymentDetail.receive_payment_id' => $id, 'ReceivePaymentDetail.is_void' => 0)));
                $this->set(compact('paymentDetails'));
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

}

?>