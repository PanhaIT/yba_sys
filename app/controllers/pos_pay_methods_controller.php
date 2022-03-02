<?php

class PosPayMethodsController extends AppController {

    var $name = 'PosPayMethods';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'View', $id);
        $this->data = $this->PosPayMethod->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'pos_pay_methods', $this->data['PosPayMethod']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->PosPayMethod->create();
                $this->data['PosPayMethod']['chart_of_account_id'] = 1;
                $this->data['PosPayMethod']['created']    = $dateNow;
                $this->data['PosPayMethod']['created_by'] = $user['User']['id'];
                $this->data['PosPayMethod']['is_active'] = 1;
                if ($this->PosPayMethod->save($this->data)) {
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Add New', $this->PosPayMethod->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'PosPayMethod', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'pos_pay_methods', $id, $this->data['PosPayMethod']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['PosPayMethod']['modified']    = $dateNow;
                $this->data['PosPayMethod']['modified_by'] = $user['User']['id'];
                if ($this->PosPayMethod->save($this->data)) {
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Edit', $id);
        $this->data = $this->PosPayMethod->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->data = $this->PosPayMethod->read(null, $id);
        mysql_query("UPDATE `pos_pay_methods` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Pos Payment Method', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>