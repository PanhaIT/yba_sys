<?php

class ColorsController extends AppController {

    var $name = 'Colors';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'View', $id);
        $this->data = $this->Color->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'colors', $this->data['Color']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Color->create();
                $this->data['Color']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Color']['created']    = $dateNow;
                $this->data['Color']['created_by'] = $user['User']['id'];
                $this->data['Color']['is_active']  = 1;
                if ($this->Color->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Color'], 'colors');
//                    $restCode[$r]['modified'] = $dateNow;
//                    $restCode[$r]['dbtodo']   = 'colors';
//                    $restCode[$r]['actodo']   = 'is';
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Add New', $this->Color->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'colors', $id, $this->data['Color']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Edit (Name ready existed', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Color']['modified']    = $dateNow;
                $this->data['Color']['modified_by'] = $user['User']['id'];
                if ($this->Color->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Color'], 'colors');
//                    $restCode[$r]['dbtodo'] = 'colors';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$this->data['Color']['sys_code']."'";
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Edit', $id);
        $this->data = $this->Color->read(null, $id);
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
//        $r = 0;
//        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->Color->read(null, $id);
        $user = $this->getCurrentUser();
        mysql_query("UPDATE `colors` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
//        $restCode[$r]['is_active']   = 2;
//        $restCode[$r]['modified']    = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'colors';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "sys_code = '".$this->data['Color']['sys_code']."'";
        // Save File Send
//        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>