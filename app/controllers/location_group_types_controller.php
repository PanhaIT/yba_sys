<?php

class LocationGroupTypesController extends AppController {

    var $name = 'LocationGroupTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'View', $id);
        $this->data = $this->LocationGroupType->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'location_group_types', $this->data['LocationGroupType']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->LocationGroupType->create();
                $this->data['LocationGroupType']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['LocationGroupType']['created']    = $dateNow;
                $this->data['LocationGroupType']['created_by'] = $user['User']['id'];
                $this->data['LocationGroupType']['is_active']  = 1;
                if ($this->LocationGroupType->save($this->data)) {
                    $lastInsertId = $this->LocationGroupType->id;
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['LocationGroupType'], 'location_group_types');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'location_group_types';
                    $restCode[$r]['actodo']   = 'is';
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'location_group_types', $id, $this->data['LocationGroupType']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['LocationGroupType']['modified'] = $dateNow;
                $this->data['LocationGroupType']['modified_by'] = $user['User']['id'];
                if ($this->LocationGroupType->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['LocationGroupType'], 'location_group_types');
                    $restCode[$r]['dbtodo'] = 'location_group_types';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['LocationGroupType']['sys_code']."'";
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Edit', $id);
            $this->data = $this->LocationGroupType->read(null, $id);
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
        Configure::write('debug', 0);
        $user = $this->getCurrentUser();
        $sqlCheck = mysql_query("SELECT id FROM location_groups WHERE location_group_type_id = ".$id." AND is_active = 1 LIMIT 1;");
        if(!mysql_num_rows($sqlCheck)){
            $this->data = $this->LocationGroupType->read(null, $id);
            mysql_query("UPDATE `location_group_types` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            $error = mysql_error();
            if($error != 'Data cloud not been delete'){
                // Convert to REST
                $restCode[$r]['is_active']   = 2;
                $restCode[$r]['modified']    = $dateNow;
                $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                $restCode[$r]['dbtodo'] = 'location_group_types';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$this->data['LocationGroupType']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Delete', $id);
                echo MESSAGE_DATA_HAS_BEEN_DELETED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Delete (Error have location group)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse Type', 'Export to Excel');
            $filename = "public/report/location_group_type_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Warehouse Type' . "\n\n";
            $excelContent .= TABLE_NO."\t".TABLE_NAME."\t".GENERAL_DESCRIPTION."\t".TABLE_ALLOW_NEGATIVE_STOCK."\t".TABLE_ALLOW_TRANSFER_CONFIRM;
            $query = mysql_query('SELECT id, name, description, allow_negative_stock, stock_tranfer_confirm FROM location_group_types WHERE is_active = 1 AND id != 1 ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $allowNegative = ACTION_NO;
                $allowTransfer = ACTION_NO;
                if($data[3] == 1){
                    $allowNegative = ACTION_YES;
                }
                if($data[4] == 1){
                    $allowTransfer = ACTION_YES;
                }
                $excelContent .= "\n".$index++."\t".$data[1]."\t".$data[2]."\t".$allowNegative."\t".$allowTransfer;
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>