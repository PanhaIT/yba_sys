<?php

class ServiceGroupsController extends AppController {

    var $name = 'ServiceGroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Dashboard');
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
        $this->data = $this->ServiceGroup->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'View', $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'service_groups', $this->data['ServiceGroup']['name'], 'is_active = 1 AND id IN (SELECT service_group_id FROM service_group_companies WHERE company_id IN ('.$this->data['ServiceGroup']['company_id'].'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->ServiceGroup->create();
                $this->data['ServiceGroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['ServiceGroup']['created']    = $dateNow;
                $this->data['ServiceGroup']['created_by'] = $user['User']['id'];
                $this->data['ServiceGroup']['is_active']  = 1;
                if ($this->ServiceGroup->save($this->data)) {
                    $lastInsertId = $this->ServiceGroup->getLastInsertId();
                    if (isset($this->data['ServiceGroup']['company_id'])) {
                        mysql_query("INSERT INTO service_group_companies (service_group_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['ServiceGroup']['company_id'] . "')");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact("companies"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'service_groups', $id, $this->data['ServiceGroup']['name'], 'is_active = 1 AND id IN (SELECT service_group_id FROM service_group_companies WHERE company_id IN ('.$this->data['ServiceGroup']['company_id'].'))')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['ServiceGroup']['modified']    = $dateNow;
                $this->data['ServiceGroup']['modified_by'] = $user['User']['id'];
                if ($this->ServiceGroup->save($this->data)) {
                    mysql_query("DELETE FROM service_group_companies WHERE service_group_id=" . $id);
                    if (isset($this->data['ServiceGroup']['company_id'])) {
                        mysql_query("INSERT INTO service_group_companies (service_group_id, company_id) VALUES ('" . $id . "','" . $this->data['ServiceGroup']['company_id']. "')");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Edit', $id);
            $this->data = $this->ServiceGroup->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact("companies"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->ServiceGroup->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'ServiceGroup', 'Delete', $id);
        mysql_query("UPDATE `service_groups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>