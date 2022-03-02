<?php

class ExpensesController extends AppController {

    var $name = 'Expenses';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'View', $id);
        $this->data = $this->Expense->read(null, $id);
        $expenseDeatils = ClassRegistry::init('ExpenseDetail')->find('all', array('conditions' => array('ExpenseDetail.expense_id' => $id)));
        $this->set(compact("expenseDeatils"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('ExpenseDetail');
            $this->loadModel('AccountType');
            // Chart Account
            $cashExpense = $this->AccountType->findById(19);

            $this->Expense->create();
            $this->data['Expense']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Expense']['chart_account_id']  = $cashExpense['AccountType']['chart_account_id'];
            $this->data['Expense']['created']    = $dateNow;
            $this->data['Expense']['created_by'] = $user['User']['id'];
            $this->data['Expense']['status']     = 1;
            if ($this->Expense->save($this->data)) {
                $expenseId = $this->Expense->id;
                // Get Module Code
                $modCode = $this->Helper->getModuleCode($this->data['Expense']['reference'], $expenseId, 'reference', 'expenses', 'status >= 0 AND branch_id = '.$this->data['Expense']['branch_id']);
                // Updaet Module Code
                $this->data['Expense']['reference'] = $modCode;
                mysql_query("UPDATE expenses SET reference = '".$modCode."' WHERE id = ".$expenseId);
                // GL
                $this->GeneralLedger->create();
                $this->data['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['GeneralLedger']['expense_id'] = $expenseId;
                $this->data['GeneralLedger']['date']       = $this->data['Expense']['date'];
                $this->data['GeneralLedger']['reference']  = $this->data['Expense']['reference'];
                $this->data['GeneralLedger']['created']    = $dateNow;
                $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                $this->data['GeneralLedger']['is_approve'] = 1;
                $this->data['GeneralLedger']['is_active']  = 1;
                $this->data['GeneralLedger']['is_sys'] = 1;
                $this->GeneralLedger->save($this->data);
                $glId = $this->GeneralLedger->id;
                // GL Detail
                $GeneralLedgerDetail = array();
                $this->GeneralLedgerDetail->create();
                $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['Expense']['company_id'];
                $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['Expense']['branch_id'];
                $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashExpense['AccountType']['chart_account_id'];
                $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Expense';
                $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = 0;
                $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = $this->data['Expense']['total_amount'];
                $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $this->data['Expense']['note'];
                $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $this->data['Expense']['vendor_id'];
                $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    // Expense Detail
                    $expenseDetail = array();
                    $this->ExpenseDetail->create();
                    $expenseDetail['ExpenseDetail']['expense_id'] = $expenseId;
                    $expenseDetail['ExpenseDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                    $expenseDetail['ExpenseDetail']['amount'] = $_POST['amount'][$i];
                    $expenseDetail['ExpenseDetail']['note']   = $_POST['memo'][$i];
                    $this->ExpenseDetail->save($expenseDetail);
                    // GL Detail
                    $GeneralLedgerDetail = array();
                    $this->GeneralLedgerDetail->create();
                    $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['Expense']['company_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['Expense']['branch_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Expense';
                    $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = abs($_POST['amount'][$i]);
                    $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = 0;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $_POST['memo'][$i];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $this->data['Expense']['vendor_id'];
                    $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Add New', $expenseId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.expense_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
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
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('ExpenseDetail');
            $this->loadModel('AccountType');
            $expense = $this->Expense->read(null, $id);
            if($expense['Expense']['status'] == 1){
                // Update Old
                $this->Expense->updateAll(
                        array('Expense.status' => -1, 'Expense.modified_by' => $user['User']['id']),
                        array('Expense.id' => $id)
                );
                $this->GeneralLedger->updateAll(
                        array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                        array('GeneralLedger.expense_id' => $id)
                );
                // Chart Account
                $cashExpense = $this->AccountType->findById(19);

                $this->Expense->create();
                $this->data['Expense']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Expense']['chart_account_id']  = $cashExpense['AccountType']['chart_account_id'];
                $this->data['Expense']['reference']   = $expense['Expense']['reference'];
                $this->data['Expense']['status']      = 1;
                $this->data['Expense']['created']     = $expense['Expense']['created'];
                $this->data['Expense']['created_by']  = $expense['Expense']['created_by'];
                $this->data['Expense']['modified']    = $dateNow;
                $this->data['Expense']['modified_by'] = $user['User']['id'];
                if ($this->Expense->save($this->data)) {
                    $expenseId = $this->Expense->id;
                    if($this->data['Expense']['branch_id'] != $expense['Expense']['branch_id']){
                        // Get Module Code
                        $modCode = $this->Helper->getModuleCode($this->data['Expense']['reference'], $expenseId, 'reference', 'expenses', 'status >= 0 AND branch_id = '.$this->data['Expense']['branch_id']);
                        // Updaet Module Code
                        $this->data['Expense']['reference'] = $modCode;
                        mysql_query("UPDATE expenses SET reference = '".$modCode."' WHERE id = ".$expenseId);
                    }
                    // GL
                    $this->GeneralLedger->create();
                    $this->data['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $this->data['GeneralLedger']['expense_id'] = $expenseId;
                    $this->data['GeneralLedger']['date']       = $this->data['Expense']['date'];
                    $this->data['GeneralLedger']['reference']  = $this->data['Expense']['reference'];
                    $this->data['GeneralLedger']['created']    = $dateNow;
                    $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
                    $this->data['GeneralLedger']['is_approve'] = 1;
                    $this->data['GeneralLedger']['is_active']  = 1;
                    $this->data['GeneralLedger']['is_sys'] = 1;
                    $this->GeneralLedger->save($this->data);
                    $glId = $this->GeneralLedger->id;
                    // GL Detail
                    $GeneralLedgerDetail = array();
                    $this->GeneralLedgerDetail->create();
                    $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['Expense']['company_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['Expense']['branch_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $cashExpense['AccountType']['chart_account_id'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Expense';
                    $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = 0;
                    $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = $this->data['Expense']['total_amount'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $this->data['Expense']['note'];
                    $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $this->data['Expense']['vendor_id'];
                    $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                        // Expense Detail
                        $expenseDetail = array();
                        $this->ExpenseDetail->create();
                        $expenseDetail['ExpenseDetail']['expense_id'] = $expenseId;
                        $expenseDetail['ExpenseDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $expenseDetail['ExpenseDetail']['amount'] = $_POST['amount'][$i];
                        $expenseDetail['ExpenseDetail']['note']   = $_POST['memo'][$i];
                        $this->ExpenseDetail->save($expenseDetail);
                        // GL Detail
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['Expense']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['Expense']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type']        = 'Expense';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit']       = abs($_POST['amount'][$i]);
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit']      = 0;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo']        = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $this->data['Expense']['vendor_id'];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Edit', $id);
        $this->data = $this->Expense->read(null, $id);
        $companies  = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.expense_code', 'Branch.currency_id', 'Currency.symbol'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $expenseDeatils = ClassRegistry::init('ExpenseDetail')->find('all', array('conditions' => array('ExpenseDetail.expense_id' => $id)));
        $this->set(compact("companies", "branches", "expenseDeatils"));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $dateNow = date("Y-m-d H:i:s");
        $this->loadModel('GeneralLedger');
        mysql_query("UPDATE `expenses` SET `status`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->GeneralLedger->updateAll(
                array('GeneralLedger.is_active' => 2, 'GeneralLedger.modified_by' => $user['User']['id']),
                array('GeneralLedger.expense_id' => $id)
        );
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense', 'Delete', $id);
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

    function vendor($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }

    function vendorAjax($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }
    
    function addExpenseType(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('ChartAccount');
            $result   = array();
            if ($this->Helper->checkDouplicate('account_description', 'chart_accounts', $this->data['ExpenseType']['account_description'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->ChartAccount->create();
                $account = array();
                $account['ChartAccount']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $account['ChartAccount']['chart_account_type_id']  = 13;
                $account['ChartAccount']['chart_account_group_id'] = 7;
                $account['ChartAccount']['account_codes']       = '6';
                $account['ChartAccount']['account_description'] = $this->data['ExpenseType']['account_description'];
                $account['ChartAccount']['created']    = $dateNow;
                $account['ChartAccount']['created_by'] = $user['User']['id'];
                $account['ChartAccount']['is_active']  = 1;
                if ($this->ChartAccount->save($account)) {
                    $expenseTypeId = $this->ChartAccount->id;
                    // Get Module Code
                    $modCode = $this->Helper->getModuleCode($account['ChartAccount']['account_codes'], $expenseTypeId, 'account_codes', 'chart_accounts', 'is_active = 1', '3');
                    // Updaet Module Code
                    mysql_query("UPDATE chart_accounts SET account_codes = '".$modCode."' WHERE id = ".$expenseTypeId);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Quick Add New', $expenseTypeId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $query=mysql_query("SELECT id, CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                    while($data=mysql_fetch_array($query)){
                        $selected = '';
                        if($data['id'] == $expenseTypeId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$data['id'].'" '.$selected.'>'.$data['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Expense Type', 'Quick Add New');
    }

}

?>