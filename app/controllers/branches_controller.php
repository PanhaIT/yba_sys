<?php

class BranchesController extends AppController {

    var $name = 'Branches';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'View', $id);
        $this->data = $this->Branch->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'branches', $this->data['Branch']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->loadModel('ModuleCodeBranch');
                $dateNow   = date("Y-m-d H:i:s");
                $sqlBranch = mysql_query("SELECT id FROM branches WHERE company_id = ".$this->data['Branch']['company_id']." AND is_active = 1");
                if(!mysql_num_rows($sqlBranch)){
                    $this->data['Branch']['is_head'] = 1;
                }
                $company = ClassRegistry::init('Company')->read(null, $this->data['Branch']['company_id']);
                $this->Branch->create();
                $this->data['Branch']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Branch']['currency_id'] = $company['Company']['currency_id'];
                $this->data['Branch']['created']    = $dateNow;
                $this->data['Branch']['created_by'] = $user['User']['id'];
                $this->data['Branch']['is_active']  = 1;
                if ($this->Branch->save($this->data)) {
                    $lastInsertId = $this->Branch->id;
                    // User Branch
                    if(!empty($this->data['Branch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['user_id']);$i++){
                            mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$this->data['Branch']['user_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Branch Module Code
                    $this->ModuleCodeBranch->create();
                    $this->data['ModuleCodeBranch']['branch_id'] = $lastInsertId;
                    $this->data['ModuleCodeBranch']['receive_pay_code'] = $this->data['ModuleCodeBranch']['inv_rep_code'];
                    $this->ModuleCodeBranch->save($this->data);
                    // Update Product Price with Branch
                    if($lastInsertId > 1){
                        mysql_query("INSERT INTO product_prices (branch_id, product_id, price_type_id, uom_id, old_unit_cost, amount_before, amount, percent, add_on, set_type, created, created_by) 
                                     SELECT ".$lastInsertId.", product_id, price_type_id, uom_id, old_unit_cost, amount_before, amount, percent, add_on, set_type, created, created_by FROM product_prices WHERE branch_id = 1;");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
        $branchTypes = ClassRegistry::init('BranchType')->find('list', array('conditions' => array('BranchType.is_active = 1')));
        $this->set(compact('countries', 'companies', 'branchTypes'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if ((!$id && empty($this->data))) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'branches', $id, $this->data['Branch']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $branch   = $this->Branch->read(null, $id);
                if(!empty($this->data['Branch']['company_id'])){
                    $company = ClassRegistry::init('Company')->read(null, $this->data['Branch']['company_id']);
                    $this->data['Branch']['currency_id'] = $company['Company']['currency_id'];
                } else {
                    $company = ClassRegistry::init('Company')->read(null, $branch['Branch']['company_id']);
                }
                $this->data['Branch']['modified']    = $dateNow;
                $this->data['Branch']['modified_by'] = $user['User']['id'];
                if ($this->Branch->save($this->data)) {
                    // User Branch
                    mysql_query("DELETE FROM user_branches WHERE branch_id=".$id);
                    if(!empty($this->data['Branch']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Branch']['user_id']);$i++){
                            mysql_query("INSERT INTO user_branches (user_id, branch_id) VALUES ('".$this->data['Branch']['user_id'][$i]."','".$id."')");
                        }
                    }
                    // Branch Module Code
                    $this->loadModel('ModuleCodeBranch');
                    mysql_query("DELETE FROM module_code_branches WHERE branch_id=".$id);
                    $this->ModuleCodeBranch->create();
                    $this->data['ModuleCodeBranch']['branch_id'] = $id;
                    $this->data['ModuleCodeBranch']['receive_pay_code'] = $this->data['ModuleCodeBranch']['inv_rep_code'];
                    $this->ModuleCodeBranch->save($this->data);
                    // Save 
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Edit', $id);
            $this->data = $this->Branch->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
            $countries = ClassRegistry::init('Country')->find('list', array("conditions" => array("Country.is_active = 1")));
            $moduleCode = ClassRegistry::init('ModuleCodeBranch')->find("first", array("conditions" => array("ModuleCodeBranch.branch_id" => $id)));
            $branchTypes = ClassRegistry::init('BranchType')->find('list', array('conditions' => array('BranchType.is_active = 1')));
            $this->set(compact('countries', 'companies', 'moduleCode', 'branchTypes'));
        }
    }

    function delete($id = null) {
        $this->layout = 'ajax';
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Branch->read(null, $id);
        Configure::write('debug', 0);
        mysql_query("UPDATE `branches` SET `act`=2, `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $error = mysql_error();
        if($error != 'Data could not been delete' && $error != 'Invalid Data'){
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Branch', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

}

?>