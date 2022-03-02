<?php

class CurrenciesController extends AppController {

    var $name = 'Currencies';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'View');
        $this->data = $this->Currency->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'currencies', $this->data['Currency']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Add New (Name Has Existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->Currency->create();
                $this->data['Currency']['created_by'] = $user['User']['id'];
                $this->data['Currency']['is_active'] = 1;
                if ($this->Currency->save($this->data)) {
                    $saveId = $this->Currency->id;
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Add New', $saveId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $sqlCompany = mysql_query("SELECT id FROM companies WHERE currency_id = ".$id." AND is_active = 1 LIMIT 1;");
        $sqlUse     = mysql_query("SELECT id FROM company_currencies WHERE currency_id = ".$id." AND is_active = 1 LIMIT 1;");
        if(mysql_num_rows($sqlCompany) || mysql_num_rows($sqlUse)){
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Edit Error Used Ready', $id);
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'currencies', $id, $this->data['Currency']['name'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Edit (Name Has Existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->data['Currency']['modified_by'] = $user['User']['id'];
                if ($this->Currency->save($this->data)) {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Edit', $id);
        $this->data = $this->Currency->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $sqlCompany = mysql_query("SELECT id FROM companies WHERE currency_id = ".$id." AND is_active = 1 LIMIT 1;");
        $sqlUse     = mysql_query("SELECT id FROM company_currencies WHERE currency_id = ".$id." AND is_active = 1 LIMIT 1;");
        $user = $this->getCurrentUser();
        if(!mysql_num_rows($sqlCompany) && !mysql_num_rows($sqlUse)){
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Delete', $id);
            mysql_query("UPDATE `currencies` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Currency', 'Delete Error Used Ready', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

}

?>