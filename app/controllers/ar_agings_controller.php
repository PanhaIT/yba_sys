<?php

class ArAgingsController extends AppController {

    var $name = 'ArAgings';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment Customer (Journal)', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                    array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                    ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $customerGroups = ClassRegistry::init('Cgroup')->find("list", array("conditions" => array("Cgroup.is_active = 1", "Cgroup.id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
        $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
        $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
        $this->set(compact('companies', 'branches', 'customerGroups', 'cashBankAccountId'));
    }

    function ajax($companyId = null, $branchId = null, $cGroupId = null, $customerId = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('cGroupId', $cGroupId);
        $this->set('customerId', $customerId);
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($this->Helper->checkDouplicate('reference', 'general_ledgers', $this->data['ArAging']['reference'], 'is_active = 1')) {
            echo 'duplicate';
            exit();
        }
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['ArAging']['date'])) {
            $this->data['ArAging']['date'] = $this->Helper->dateConvert($this->data['ArAging']['date']);
        }
        if (isset($_POST['amount_us']) && sizeof($_POST['amount_us']) > 0) {
            // save to table ar_agings
            $this->ArAging->create();
            $arAging['ArAging']['date'] = $this->data['ArAging']['date'];
            $arAging['ArAging']['company_id'] = $_POST['company_id'];
            $arAging['ArAging']['branch_id'] = $_POST['branch_id'];
            $arAging['ArAging']['cgroup_id'] = $_POST['cgroup_id'];
            $arAging['ArAging']['customer_id'] = $_POST['customer_id'];
            $arAging['ArAging']['deposit_to'] = $this->data['ArAging']['chart_account_id'];
            $arAging['ArAging']['reference'] = $this->data['ArAging']['reference'];
            $arAging['ArAging']['note'] = $this->data['ArAging']['note'];
            $arAging['ArAging']['created_by'] = $user['User']['id'];
            $this->ArAging->save($arAging);
            $arAgingId = $this->ArAging->getLastInsertId();

            // Save General Ledger
            $this->loadModel('GeneralLedger');
            $this->GeneralLedger->create();
            $generalLedger = array();
            $generalLedger['GeneralLedger']['ar_aging_id'] = $arAgingId;
            $generalLedger['GeneralLedger']['date'] = $this->data['ArAging']['date'];
            $generalLedger['GeneralLedger']['reference'] = $this->data['ArAging']['reference'];
            $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
            $generalLedger['GeneralLedger']['is_sys'] = 0;
            $generalLedger['GeneralLedger']['is_adj'] = 0;
            $generalLedger['GeneralLedger']['is_active'] = 1;
            $this->GeneralLedger->save($generalLedger);

            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if ($_POST['amount_us'][$i] != '' && $_POST['amount_us'][$i] != 0) {
                    // save to table ar_aging_details
                    $this->loadModel('ArAgingDetail');
                    $this->ArAgingDetail->create();
                    $arAgingDetail = array();
                    $arAgingDetail['ArAgingDetail']['ar_aging_id'] = $arAgingId;
                    $arAgingDetail['ArAgingDetail']['general_ledger_id'] = $_POST['id'][$i];
                    $arAgingDetail['ArAgingDetail']['customer_id'] = $_POST['customer_id_ajax'][$i];
                    $arAgingDetail['ArAgingDetail']['amount_due'] = $_POST['amount_due'][$i];
                    $arAgingDetail['ArAgingDetail']['paid'] = $_POST['amount_us'][$i];
                    $arAgingDetail['ArAgingDetail']['discount'] = $_POST['discount_us'][$i];
                    $arAgingDetail['ArAgingDetail']['balance'] = $_POST['balance_us'][$i];
                    $arAgingDetail['ArAgingDetail']['memo'] = $_POST['memo'][$i];
                    if ($this->ArAgingDetail->save($arAgingDetail)) {
                        $totalPaidDebit = $_POST['amount_us'][$i];
                        $totalDis       = $_POST['discount_us'][$i];
                        $totalPaid      = $_POST['amount_us'][$i] + $totalDis;
                        // Save General Ledger Detail
                        $this->loadModel('GeneralLedgerDetail');
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        // Deposit to
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['ArAging']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $_POST['company_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id'] = $_POST['branch_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalPaidDebit;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id_ajax'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        // update main gl
                        $queryMainGl = mysql_query("SELECT main_gl_id FROM general_ledger_details WHERE id=" . $_POST['id'][$i]);
                        $dataMainGl = mysql_fetch_array($queryMainGl);
                        if ($dataMainGl['main_gl_id'] != '') {
                            mysql_query("UPDATE general_ledger_details SET main_gl_id=" . $_POST['id'][$i] . " WHERE main_gl_id=" . $dataMainGl['main_gl_id']);
                        } else {
                            mysql_query("UPDATE general_ledger_details SET main_gl_id=" . $_POST['id'][$i] . " WHERE id=" . $_POST['id'][$i]);
                        }
                        $generalLedgerDetail['GeneralLedgerDetail']['main_gl_id'] = $_POST['id'][$i];
                        // A/R
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['ar_id'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $_POST['company_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id'] = $_POST['branch_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Payment';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id_ajax'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        
                        /* General Ledger Detail Total Discount */
                        if ($totalDis > 0) {
                            //Chart Account Discount
                            $paidDiscAccount = $this->AccountType->findById(11);
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail = array();
                            $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $paidDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $_POST['company_id_ajax'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['branch_id'] = $_POST['branch_id_ajax'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Payment';
                            $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalDis;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo'] = "Discount ".$_POST['memo'][$i];
                            $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id_ajax'][$i];
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        }
                    }
                }
            }
            $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment Customer (Journal)', 'Save Add New', $arAgingId);
            echo $arAgingId;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment Customer (Journal)', 'Save Add New (Error)');
            echo 'error';
        }
        exit;
    }

    function printInvoice($id = null) {
        $this->layout = 'ajax';
        $this->set('id', $id);
    }

}

?>