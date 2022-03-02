<?php

class BranchCurrenciesController extends AppController {

    var $name = 'BranchCurrencies';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Dashboard');
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
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'View', $id);
        $this->data = $this->BranchCurrency->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('currency_id', 'branch_currencies', $this->data['BranchCurrency']['currency_id'], 'is_active = 1 AND branch_id = '.$this->data['BranchCurrency']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Add New (Currency Has Existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->BranchCurrency->create();
            $this->data['BranchCurrency']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['BranchCurrency']['created']    = $dateNow;
            $this->data['BranchCurrency']['created_by'] = $user['User']['id'];
            $this->data['BranchCurrency']['is_active'] = 1;
            if ($this->BranchCurrency->save($this->data)) {
                $saveId = $this->BranchCurrency->id;
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['BranchCurrency'], 'branch_currencies');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'branch_currencies';
                $restCode[$r]['actodo']   = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Add New', $saveId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id']),
                            'group' => array('Branch.id')
                        ));
        $currencies = ClassRegistry::init('Currency')->find('list', array('conditions' => array('Currency.is_active = 1')));
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Add New');
        $this->set(compact('branches', 'currencies'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('currency_id', 'branch_currencies', $id, $this->data['BranchCurrency']['currency_id'], 'is_active = 1 AND branch_id = '.$this->data['BranchCurrency']['branch_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Edit (Currency Has Existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['BranchCurrency']['modified'] = $dateNow;
            $this->data['BranchCurrency']['modified_by'] = $user['User']['id'];
            if ($this->BranchCurrency->save($this->data)) {
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['BranchCurrency'], 'branch_currencies');
                $restCode[$r]['dbtodo'] = 'branch_currencies';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$this->data['BranchCurrency']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Edit', $id);
        $this->data = $this->BranchCurrency->read(null, $id);
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $currencies = ClassRegistry::init('Currency')->find('list', array('conditions' => array('Currency.is_active = 1')));
        $this->set(compact('branches', 'currencies'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->BranchCurrency->read(null, $id);
        mysql_query("UPDATE `branch_currencies` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'branch_types';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['BranchCurrency']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function applyPos($id = null){
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Branch Currency', 'Apply To POS', $id);
        // Reset Apply
        $this->data = $this->BranchCurrency->read(null, $id);
        mysql_query("UPDATE `branch_currencies` SET `is_pos_default`=0, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE branch_id = ".$this->data['BranchCurrency']['branch_id'].";");
        // Convert to REST
        $restCode[$r]['is_pos_default']   = 0;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'branch_currencies';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "branch_id = ".$this->data['Branch']['sys_code'];
        $r++;
        // Set to Branch
        mysql_query("UPDATE `branches` SET `pos_currency_id`=".$id." WHERE id = ".$this->data['BranchCurrency']['branch_id'].";");
        // Convert to REST
        $restCode[$r]['pos_currency_id'] = $id;
        $restCode[$r]['dbtodo'] = 'branches';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['Branch']['sys_code']."'";
        $r++;
        // Set Apply
        mysql_query("UPDATE `branch_currencies` SET `is_pos_default`=1, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_pos_default']   = 1;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'branch_currencies';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['BranchCurrency']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

}

?>