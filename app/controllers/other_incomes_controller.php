<?php

class OtherIncomesController extends AppController {

    var $name = 'OtherIncomes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'View', $id);
        $this->data = $this->OtherIncome->read(null, $id);
        $otherIncomeDeatils = ClassRegistry::init('OtherIncomeDetail')->find('all', array('conditions' => array('OtherIncomeDetail.other_income_id' => $id)));
        $this->set(compact("otherIncomeDeatils"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('reference', 'other_incomes', $this->data['OtherIncome']['reference'], 'status > 0')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Add New (Reference has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('OtherIncomeDetail');
                $this->loadModel('AccountType');
                // Chart Account
                $cashOtherIncome = $this->AccountType->findById(19);
                
                $this->OtherIncome->create();
                $this->data['OtherIncome']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['OtherIncome']['chart_account_id']  = $cashOtherIncome['AccountType']['chart_account_id'];
                $this->data['OtherIncome']['created']    = $dateNow;
                $this->data['OtherIncome']['created_by'] = $user['User']['id'];
                $this->data['OtherIncome']['status']     = 1;
                if ($this->OtherIncome->save($this->data)) {
                    $other_incomeId = $this->OtherIncome->id;
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['OtherIncome'], 'other_incomes');
//                    $restCode[$r]['modified'] = $dateNow;
//                    $restCode[$r]['dbtodo']   = 'other_incomes';
//                    $restCode[$r]['actodo']   = 'is';
//                    $r++;
                    // GL
                    $this->GeneralLedger->create();
                    $this->data['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $this->data['GeneralLedger']['other_income_id'] = $other_incomeId;
                    $this->data['GeneralLedger']['date']       = $this->data['OtherIncome']['date'];
                    $this->data['GeneralLedger']['reference']  = $this->data['OtherIncome']['reference'];
                    $this->data['GeneralLedger']['created']    = $dateNow;
                    $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                    $this->data['GeneralLedger']['is_approve'] = 1;
                    $this->data['GeneralLedger']['is_active']  = 1;
                    $this->data['GeneralLedger']['is_sys'] = 1;
                    $this->GeneralLedger->save($this->data);
                    $glId = $this->GeneralLedger->id;
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['GeneralLedger'], 'general_ledgers');
//                    $restCode[$r]['modified'] = $dateNow;
//                    $restCode[$r]['dbtodo']   = 'general_ledgers';
//                    $restCode[$r]['actodo']   = 'is';
//                    $r++;
                    // GL Detail
                    $GeneralLedgerDetail = array();
                    $this->GeneralLedgerDetail->create();
                    $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['OtherIncome']['company_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['OtherIncome']['branch_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashOtherIncome['AccountType']['chart_account_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Other Income';
                    $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = $this->data['OtherIncome']['total_amount'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = 0;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $this->data['OtherIncome']['note'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $this->data['OtherIncome']['customer_id'];
                    $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($GeneralLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                    $restCode[$r]['dbtodo']   = 'general_ledger_details';
//                    $restCode[$r]['actodo']   = 'is';
//                    $r++;
                    for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                        // OtherIncome Detail
                        $other_incomeDetail = array();
                        $this->OtherIncomeDetail->create();
                        $other_incomeDetail['OtherIncomeDetail']['other_income_id'] = $other_incomeId;
                        $other_incomeDetail['OtherIncomeDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $other_incomeDetail['OtherIncomeDetail']['amount'] = $_POST['amount'][$i];
                        $other_incomeDetail['OtherIncomeDetail']['note']   = $_POST['memo'][$i];
                        $this->OtherIncomeDetail->save($other_incomeDetail);
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($other_incomeDetail['OtherIncomeDetail'], 'other_income_details');
//                        $restCode[$r]['dbtodo']   = 'other_income_details';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                        // GL Detail
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['OtherIncome']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['OtherIncome']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Other Income';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = 0;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = abs($_POST['amount'][$i]);
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $this->data['OtherIncome']['customer_id'];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($GeneralLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                        $restCode[$r]['dbtodo']   = 'general_ledger_details';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Add New', $other_incomeId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'), 
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $this->set(compact("companies", "branches"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('reference', 'other_incomes', $id, $this->data['OtherIncome']['reference'], 'status > 0')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Edit (Reference has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r  = 0;
//                $rb = 0;
//                $restCode = array();
//                $restBackCode  = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->loadModel('GeneralLedger');
                $this->loadModel('GeneralLedgerDetail');
                $this->loadModel('OtherIncomeDetail');
                $this->loadModel('AccountType');
                $other_income = $this->OtherIncome->read(null, $id);
                if($other_income['OtherIncome']['status'] == 1){
                    // Update Old
                    $this->OtherIncome->updateAll(
                            array('OtherIncome.status' => -1, 'OtherIncome.modified_by' => $user['User']['id']),
                            array('OtherIncome.id' => $id)
                    );
                    // Convert to REST
//                    $restBackCode[$rb]['status']   = -1;
//                    $restBackCode[$rb]['modified'] = $dateNow;
//                    $restBackCode[$rb]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restBackCode[$rb]['dbtodo'] = 'other_incomes';
//                    $restBackCode[$rb]['actodo'] = 'ut';
//                    $restBackCode[$rb]['con']    = "sys_code = '".$other_income['OtherIncome']['sys_code']."'";
//                    $rb++;
                    $this->GeneralLedger->updateAll(
                            array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                            array('GeneralLedger.other_income_id' => $id)
                    );
                    // Convert to REST
//                    $restBackCode[$rb]['is_active'] = 2;
//                    $restBackCode[$rb]['modified']  = $dateNow;
//                    $restBackCode[$rb]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restBackCode[$rb]['dbtodo'] = 'general_ledgers';
//                    $restBackCode[$rb]['actodo'] = 'ut';
//                    $restBackCode[$rb]['con']    = "other_income_id = (SELECT id FROM other_incomes  WHERE sys_code = '".$other_income['OtherIncome']['sys_code']."' LIMIT 1)";
                    // Save File Send Delete
//                    $this->Helper->sendFileToSync($restBackCode, 0, 0);
                    
                    // Chart Account
                    $cashOtherIncome = $this->AccountType->findById(19);
                    
                    $this->OtherIncome->create();
                    $this->data['OtherIncome']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $this->data['OtherIncome']['chart_account_id']  = $cashOtherIncome['AccountType']['chart_account_id'];
                    $this->data['OtherIncome']['status']      = 1;
                    $this->data['OtherIncome']['created']     = $other_income['OtherIncome']['created'];
                    $this->data['OtherIncome']['created_by']  = $other_income['OtherIncome']['created_by'];
                    $this->data['OtherIncome']['modified']    = $dateNow;
                    $this->data['OtherIncome']['modified_by'] = $user['User']['id'];
                    if ($this->OtherIncome->save($this->data)) {
                        $other_incomeId = $this->OtherIncome->id;
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['OtherIncome'], 'other_incomes');
//                        $restCode[$r]['dbtodo']   = 'other_incomes';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                        // GL
                        $this->GeneralLedger->create();
                        $this->data['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $this->data['GeneralLedger']['other_income_id'] = $other_incomeId;
                        $this->data['GeneralLedger']['date']       = $this->data['OtherIncome']['date'];
                        $this->data['GeneralLedger']['reference']  = $this->data['OtherIncome']['reference'];
                        $this->data['GeneralLedger']['created']    = $dateNow;
                        $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                        $this->data['GeneralLedger']['is_approve'] = 1;
                        $this->data['GeneralLedger']['is_active']  = 1;
                        $this->data['GeneralLedger']['is_sys'] = 1;
                        $this->GeneralLedger->save($this->data);
                        $glId = $this->GeneralLedger->id;
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['GeneralLedger'], 'general_ledgers');
//                        $restCode[$r]['modified'] = $dateNow;
//                        $restCode[$r]['dbtodo']   = 'general_ledgers';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                        // GL Detail
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['OtherIncome']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['OtherIncome']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashOtherIncome['AccountType']['chart_account_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'OtherIncome';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = $this->data['OtherIncome']['total_amount'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = 0;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $this->data['OtherIncome']['note'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $this->data['OtherIncome']['customer_id'];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($GeneralLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                        $restCode[$r]['dbtodo']   = 'general_ledger_details';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                        for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                            // OtherIncome Detail
                            $other_incomeDetail = array();
                            $this->OtherIncomeDetail->create();
                            $other_incomeDetail['OtherIncomeDetail']['other_income_id'] = $other_incomeId;
                            $other_incomeDetail['OtherIncomeDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                            $other_incomeDetail['OtherIncomeDetail']['amount'] = $_POST['amount'][$i];
                            $other_incomeDetail['OtherIncomeDetail']['note']   = $_POST['memo'][$i];
                            $this->OtherIncomeDetail->save($other_incomeDetail);
                            // Convert to REST
//                            $restCode[$r] = $this->Helper->convertToDataSync($other_incomeDetail['OtherIncomeDetail'], 'other_income_details');
//                            $restCode[$r]['dbtodo']   = 'other_income_details';
//                            $restCode[$r]['actodo']   = 'is';
//                            $r++;
                            // GL Detail
                            $GeneralLedgerDetail = array();
                            $this->GeneralLedgerDetail->create();
                            $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                            $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['OtherIncome']['company_id'];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['OtherIncome']['branch_id'];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'OtherIncome';
                            $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = 0;
                            $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = abs($_POST['amount'][$i]);
                            $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $_POST['memo'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $this->data['OtherIncome']['customer_id'];
                            $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                            // Convert to REST
//                            $restCode[$r] = $this->Helper->convertToDataSync($GeneralLedgerDetail['GeneralLedgerDetail'], 'general_ledger_details');
//                            $restCode[$r]['dbtodo']   = 'general_ledger_details';
//                            $restCode[$r]['actodo']   = 'is';
//                            $r++;
                        }
                        // Save File Send
//                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Edit', $id);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Edit (Error)', $id);
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Edit', $id);
        $this->data = $this->OtherIncome->read(null, $id);
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'), 
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $otherIncomeDeatils = ClassRegistry::init('OtherIncomeDetail')->find('all', array('conditions' => array('OtherIncomeDetail.other_income_id' => $id)));
        $this->set(compact("companies", "branches", "otherIncomeDeatils"));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
//        $r  = 0;
//        $restCode = array();
        $user = $this->getCurrentUser();
        $dateNow = date("Y-m-d H:i:s");
        $this->loadModel('GeneralLedger');
        $other_income = $this->OtherIncome->read(null, $id);
        mysql_query("UPDATE `other_incomes` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
//        $restCode[$r]['status']   = 0;
//        $restCode[$r]['modified'] = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'other_incomes';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "sys_code = '".$other_income['OtherIncome']['sys_code']."'";
//        $r++;
        $this->GeneralLedger->updateAll(
                array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                array('GeneralLedger.other_income_id' => $id)
        );
        // Convert to REST
//        $restCode[$r]['is_active'] = 2;
//        $restCode[$r]['modified']  = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'general_ledgers';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "other_income_id = (SELECT id FROM other_incomes  WHERE sys_code = '".$other_income['OtherIncome']['sys_code']."' LIMIT 1)";
        // Save File Send Delete
//        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Other Income', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function customer($companyId = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
    }

    function customerAjax($companyId = null, $group = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('group', $group);
    }

}

?>