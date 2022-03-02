<?php

class DistrictsController extends AppController {

    var $name = 'Districts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'District', 'View', $id);
        $this->set('district', $this->District->read(null, $id));
        ClassRegistry::init('Province')->id = $this->District->field('province_id');
        $this->set('province', ClassRegistry::init('Province')->field('name'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode  = array();
            for ($i = 0; $i < sizeof($_POST['name']); $i++) {
                $dateNow   = date("Y-m-d H:i:s");
                $this->District->create();
                $district = array();
                $district['District']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $district['District']['province_id'] = $this->data['District']['province_id'];
                $district['District']['name']        = $_POST['name'][$i];
                $district['District']['created']     = $dateNow;
                $district['District']['created_by']  = $user['User']['id'];
                $district['District']['is_active']   = 1;
                $this->District->save($district);
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($district['District'], 'districts');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'districts';
                $restCode[$r]['actodo']   = 'is';
                $r++;
            }
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Save Add New');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Add New');
        $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
        $this->set(compact("provinces"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode  = array();
            $dateNow   = date("Y-m-d H:i:s");
            $this->data['District']['modified']    = $dateNow;
            $this->data['District']['modified_by'] = $user['User']['id'];
            if ($this->District->save($this->data)) {
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['District'], 'districts');
                $restCode[$r]['dbtodo']  = 'districts';
                $restCode[$r]['actodo']  = 'ut';
                $restCode[$r]['con']     = "sys_code = '".$this->data['District']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Edit', $id);
            $this->data = $this->District->read(null, $id);
            $provinces = ClassRegistry::init('Province')->find("list", array("conditions" => array("Province.is_active != 2")));
            $this->set(compact("provinces"));
        }
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
        $this->data = $this->District->read(null, $id);
        mysql_query("UPDATE `districts` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'districts';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['District']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'District', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>