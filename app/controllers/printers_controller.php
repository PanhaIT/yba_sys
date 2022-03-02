<?php

class PrintersController extends AppController {

    var $name = 'Printers';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'View', $id);
        $this->data = $this->Printer->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->Printer->create();
            $this->data['Printer']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Printer']['created']  = $dateNow;
            $this->data['Printer']['created_by'] = $user['User']['id'];
            $this->data['Printer']['is_active'] = 1;
            if ($this->Printer->save($this->data)) {
                $sqlBranch = mysql_query("SELECT sys_code FROM branches WHERE id = ".$this->data['Printer']['branch_id']);
                $rowBranch = mysql_fetch_array($sqlBranch);
                $restCode[$r]['sys_code']  = $this->data['Printer']['sys_code'];
                $restCode[$r]['branch_id'] = "(SELECT id FROM branches WHERE sys_code = '".$rowBranch['sys_code']."' LIMIT 1)";
                $restCode[$r]['type_id']   = $this->data['Printer']['type_id'];
                $restCode[$r]['printer_name'] = $this->data['Printer']['printer_name'];
                $restCode[$r]['silent'] = $this->data['Printer']['silent'];
                $restCode[$r]['created'] = $this->data['Printer']['created'];
                $restCode[$r]['created_by'] = $this->data['Printer']['created_by'];
                $restCode[$r]['is_active']   = $this->data['Printer']['is_active'];
                $restCode[$r]['dbtodo'] = 'printers';
                $restCode[$r]['actodo'] = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode);
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Add New', $this->Printer->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Add New');
        $types = array(1 => 'Print Receipt', 2 => 'Print Product Code', 3 => 'Print Branch to Van');
        $silents = array(0 => 'False', 1 => 'True');
        $branches = ClassRegistry::init('Branch')->find('list', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact('types', 'silents', 'branches'));
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
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['Printer']['modified'] = $dateNow;
            $this->data['Printer']['modified_by'] = $user['User']['id'];
            if ($this->Printer->save($this->data)) {
                $sqlBranch = mysql_query("SELECT sys_code FROM branches WHERE id = ".$this->data['Printer']['branch_id']);
                $rowBranch = mysql_fetch_array($sqlBranch);
                $restCode[$r]['branch_id'] = "(SELECT id FROM branches WHERE sys_code = '".$rowBranch['sys_code']."' LIMIT 1)";
                $restCode[$r]['type_id']   = $this->data['Printer']['type_id'];
                $restCode[$r]['printer_name'] = $this->data['Printer']['printer_name'];
                $restCode[$r]['silent'] = $this->data['Printer']['silent'];
                $restCode[$r]['modified'] = $this->data['Printer']['modified'];
                $restCode[$r]['modified_by'] = $this->data['Printer']['modified_by'];
                $restCode[$r]['dbtodo'] = 'printers';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$this->data['Printer']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode);
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Edit', $id);
            $this->data = $this->Printer->read(null, $id);
            $types = array(1 => 'Print Receipt', 2 => 'Print Product Code', 3 => 'Print Branch to Van');
            $silents = array(0 => 'False', 1 => 'True');
            $branches = ClassRegistry::init('Branch')->find('list', array("conditions" => array("Branch.is_active = 1 AND Branch.id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")")));
            $this->set(compact('types', 'silents', 'branches'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->Printer->read(null, $id);
        mysql_query("UPDATE `printers` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']  = 2;
        $restCode[$r]['modified']   = $dateNow;
        $restCode[$r]['modified_by'] = $user['User']['id'];
        $restCode[$r]['dbtodo']  = 'printers';
        $restCode[$r]['actodo']  = 'ut';
        $restCode[$r]['con']     = "sys_code = '".$this->data['Printer']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode);
        $this->Helper->saveUserActivity($user['User']['id'], 'Printer', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>