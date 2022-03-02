<?php

class EmployeeTypesController extends AppController {

    var $name = 'EmployeeTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'View', $id);
        $this->data = $this->EmployeeType->read(null, $id);
    }

    function add(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'employee_types', $this->data['EmployeeType']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'EmployeeType', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow = date("Y-m-d H:i:s");
                $this->EmployeeType->create();
                $this->data['EmployeeType']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['EmployeeType']['created_by'] = $user['User']['id'];
                $this->data['EmployeeType']['name']       = $this->data['EmployeeType']['name'];
                $this->data['EmployeeType']['is_active']  = 1;
                if ($this->EmployeeType->saveAll($this->data)) {
                    $lastInsertId = $this->EmployeeType->getLastInsertId();
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'EmployeeType', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'EmployeeType', 'Add New');
        }
    }

    function edit($id=null){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'employee_types', $id, $this->data['EmployeeType']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['EmployeeType']['modified']  = $dateNow;
                $this->data['EmployeeType']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['EmployeeType']['created']   = $dateNow;
                $this->data['EmployeeType']['name']      = $this->data['EmployeeType']['name'];
                $this->data['EmployeeType']['is_active'] = 1;
                if ($this->EmployeeType->saveAll($this->data)) {
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->data = $this->EmployeeType->read(null, $id);
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Edit', $id);
        }
    }

    function delete($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `employee_types` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Type', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
}

?>