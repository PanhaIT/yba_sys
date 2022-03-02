<?php

class ApAgingsController extends AppController {

    var $name = 'ApAgings';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'PayBill (Journal)', 'Add New');
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
        $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1", "Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))")));
        $cashBankAccount = ClassRegistry::init('AccountType')->findById(6);
        $cashBankAccountId = $cashBankAccount['AccountType']['chart_account_id'];
        $this->set(compact('companies', 'branches', 'vendors', 'cashBankAccountId'));
    }

    function ajax($companyId = null, $branchId = null, $vendorId = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('vendorId', $vendorId);
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($this->Helper->checkDouplicate('reference', 'general_ledgers', $this->data['ApAging']['reference'], 'is_active = 1')) {
            echo 'duplicate';
            exit();
        }
        if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['ApAging']['date'])) {
            $this->data['ApAging']['date'] = $this->Helper->dateConvert($this->data['ApAging']['date']);
        }
        if (isset($_POST['amount_us']) && sizeof($_POST['amount_us']) > 0) {
            // save to table ar_agings
            $this->ApAging->create();
            $arAging['ApAging']['date'] = $this->data['ApAging']['date'];
            $arAging['ApAging']['company_id'] = $_POST['company_id'];
            $arAging['ApAging']['branch_id'] = $_POST['branch_id'];
            $arAging['ApAging']['vendor_id'] = $_POST['vendor_id'];
            $arAging['ApAging']['deposit_to'] = $this->data['ApAging']['chart_account_id'];
            $arAging['ApAging']['reference'] = $this->data['ApAging']['reference'];
            $arAging['ApAging']['note'] = $this->data['ApAging']['note'];
            $arAging['ApAging']['created_by'] = $user['User']['id'];
            $this->ApAging->save($arAging);
            $apAgingId = $this->ApAging->getLastInsertId();

            // Save General Ledger
            $this->loadModel('GeneralLedger');
            $this->GeneralLedger->create();
            $generalLedger = array();
            $generalLedger['GeneralLedger']['ap_aging_id'] = $apAgingId;
            $generalLedger['GeneralLedger']['date'] = $this->data['ApAging']['date'];
            $generalLedger['GeneralLedger']['reference'] = $this->data['ApAging']['reference'];
            $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
            $generalLedger['GeneralLedger']['is_sys'] = 0;
            $generalLedger['GeneralLedger']['is_adj'] = 0;
            $generalLedger['GeneralLedger']['is_active'] = 1;
            $this->GeneralLedger->save($generalLedger);

            for ($i = 0; $i < sizeof($_POST['amount_us']); $i++) {
                if ($_POST['amount_us'][$i] != '' && $_POST['amount_us'][$i] != 0) {
                    // save to table ar_aging_details
                    $this->loadModel('ApAgingDetail');
                    $this->ApAgingDetail->create();
                    $arAgingDetail = array();
                    $arAgingDetail['ApAgingDetail']['ap_aging_id'] = $apAgingId;
                    $arAgingDetail['ApAgingDetail']['general_ledger_id'] = $_POST['id'][$i];
                    $arAgingDetail['ApAgingDetail']['vendor_id'] = $_POST['vendor_id_ajax'][$i];
                    $arAgingDetail['ApAgingDetail']['amount_due'] = $_POST['amount_due'][$i];
                    $arAgingDetail['ApAgingDetail']['paid'] = $_POST['amount_us'][$i];
                    $arAgingDetail['ApAgingDetail']['balance'] = $_POST['balance_us'][$i];
                    $arAgingDetail['ApAgingDetail']['memo'] = $_POST['memo'][$i];
                    if ($this->ApAgingDetail->save($arAgingDetail)) {
                        $totalPaid = $_POST['amount_us'][$i];
                        // Save General Ledger Detail
                        $this->loadModel('GeneralLedgerDetail');
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        // Deposit to
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['ApAging']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $_POST['company_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id'] = $_POST['branch_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Pay Bill';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id_ajax'][$i];
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
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['ap_id'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $_POST['company_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id'] = $_POST['branch_id_ajax'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Pay Bill';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $totalPaid;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $generalLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id_ajax'][$i];
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    }
                }
            }
            $this->Helper->saveUserActivity($user['User']['id'], 'PayBill (Journal)', 'Save Add New', $apAgingId);
            echo $apAgingId;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'PayBill (Journal)', 'Save Add New (Error)');
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