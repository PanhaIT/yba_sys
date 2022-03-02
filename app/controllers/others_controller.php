<?php

class OthersController extends AppController {

    var $name = 'Others';
    var $components = array('Helper');

    function index() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Dashboard');
    }

    function ajax() {
        $this->layout = "ajax";
    }

    function add() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['company_id'])){
                $comCheck = implode(",", $this->data['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'others', $this->data['Other']['name'], 'is_active = 1 AND id IN (SELECT other_id FROM other_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Other->create();
                $this->data['Other']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Other']['created']    = $dateNow;
                $this->data['Other']['created_by'] = $user['User']['id'];
                $this->data['Other']['is_active']  = 1;
                if ($this->Other->saveAll($this->data)) {
                    $lastInsertId = $this->Other->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Other'], 'others');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'others';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                    // other company
                    if (isset($this->data['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['company_id']); $i++) {
                            mysql_query("INSERT INTO other_companies (other_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['other_id']   = $this->data['Other']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'other_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Add New');
            $code = $this->Helper->getAutoGenerateOtherCode();
            $this->set('code',$code);
        }
    }

    function edit($id=null) {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['company_id'])){
                $comCheck = implode(",", $this->data['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'others', $id, $this->data['Other']['name'], 'is_active = 1 AND id IN (SELECT other_id FROM other_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Other']['modified']    = $dateNow;
                $this->data['Other']['modified_by'] = $user['User']['id'];
                if ($this->Other->saveAll($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Other'], 'others');
                    $restCode[$r]['dbtodo'] = 'others';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['Other']['sys_code']."'";
                    $r++;
                    // Other company
                    mysql_query("DELETE FROM other_companies WHERE other_id=" . $id);
                    // Convert to REST
                    $restCode[$r]['dbtodo'] = 'other_companies';
                    $restCode[$r]['actodo'] = 'dt';
                    $restCode[$r]['con']    = "other_id = ".$this->data['Other']['sys_code'];
                    $r++;
                    if (isset($this->data['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['company_id']); $i++) {
                            mysql_query("INSERT INTO other_companies (other_id, company_id) VALUES ('" . $id . "','" . $this->data['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['other_id']   = $this->data['Other']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'other_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Edit', $id);
            $this->data = $this->Other->read(null, $id);
        }
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'View', $id);
        $this->set('other', $this->Other->read(null, $id));
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
        $this->data = $this->Other->read(null, $id);
        mysql_query("UPDATE `others` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'others';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['Other']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Other', 'Export to Excel');
            $filename = "public/report/other_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Others' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_COMPANY. "\t" . TABLE_CODE. "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE_PERSONAL. "\t" . TABLE_TELEPHONE_WORK. "\t" . TABLE_FAX. "\t" . TABLE_EMAIL;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND id IN (SELECT other_id FROM other_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM other_companies WHERE other_id = others.id)), other_code, name, personal_number, business_number, fax_number, email_address '
                    . '           FROM others WHERE is_active=1'.$conditionUser.' ORDER BY other_code');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6]. "\t" . $data[7];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>