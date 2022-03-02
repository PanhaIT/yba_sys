<?php

class ChartAccountGroupsController extends AppController {

    var $name = 'ChartAccountGroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Dashborad');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'View', $id);
        $this->set('chartAccountGroup', $this->ChartAccountGroup->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'chart_account_groups', $this->data['ChartAccountGroup']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->ChartAccountGroup->create();
                $this->data['ChartAccountGroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['ChartAccountGroup']['created']    = $dateNow;
                $this->data['ChartAccountGroup']['created_by'] = $user['User']['id'];
                $this->data['ChartAccountGroup']['is_active'] = 1;
                if ($this->ChartAccountGroup->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['ChartAccountGroup'], 'chart_account_groups');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'chart_account_groups';
                    $restCode[$r]['actodo']   = 'is';
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Save Add New', $this->ChartAccountGroup->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Add New');
        $chartAccountTypes = ClassRegistry::init('ChartAccountType')->find("list", array("conditions" => array("ChartAccountType.is_active = 1")));
        $expenseTypes = array('0' => 'Expense', '1' => 'Depreciation Expense', '2' => 'Interest Expense', '3' => 'Tax Expense');
        $this->set(compact("chartAccountTypes", "expenseTypes"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'chart_account_groups', $id, $this->data['ChartAccountGroup']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Saves Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['ChartAccountGroup']['modified']    = $dateNow;
                $this->data['ChartAccountGroup']['modified_by'] = $user['User']['id'];
                if ($this->ChartAccountGroup->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['ChartAccountGroup'], 'chart_account_groups');
                    $restCode[$r]['dbtodo'] = 'chart_account_groups';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['ChartAccountGroup']['sys_code']."'";
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Saves Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Saves Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Edit', $id);
            $this->data = $this->ChartAccountGroup->read(null, $id);
            $chartAccountTypes = ClassRegistry::init('ChartAccountType')->find("list", array("conditions" => array("ChartAccountType.is_active = 1")));
            $expenseTypes = array('0' => 'Expense', '1' => 'Depreciation Expense', '2' => 'Interest Expense', '3' => 'Tax Expense');
            $this->set(compact("chartAccountTypes", "expenseTypes"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // check if coa group already in used
        $query=mysql_query("SELECT chart_account_group_id FROM chart_accounts WHERE is_active!=2 AND chart_account_group_id=" . $id);
        $notAllowDelete=mysql_num_rows($query);
        if(!$notAllowDelete){
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data = $this->ChartAccountGroup->read(null, $id);
            mysql_query("UPDATE `chart_account_groups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Convert to REST
            $restCode[$r]['is_active']   = 2;
            $restCode[$r]['modified']    = $dateNow;
            $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
            $restCode[$r]['dbtodo'] = 'chart_account_groups';
            $restCode[$r]['actodo'] = 'ut';
            $restCode[$r]['con']    = "sys_code = '".$this->data['ChartAccountGroup']['sys_code']."'";
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_COA_GROUP_ALREADY_IN_USED;
            exit;
        }
    }

    function status($id = null, $status = 0) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $r = 0;
        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->ChartAccountGroup->read(null, $id);
        mysql_query("UPDATE `chart_account_groups` SET `is_active`=".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = $status;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'chart_account_groups';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['ChartAccountGroup']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Change Status', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Chart Account Group', 'Export to Excel');
            $filename = "public/report/char_account_group_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Chart of Account Groups' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . GENERAL_TYPE;
            $query = mysql_query('SELECT g.id, g.name, t.name '
                    . '           FROM chart_account_groups g INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id WHERE g.is_active!=2 ORDER BY g.name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>