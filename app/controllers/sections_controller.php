<?php

class SectionsController extends AppController {

    var $name = 'Sections';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'View', $id);
        $this->set('section', $this->Section->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'sections', $this->data['Section']['name'], 'is_active = 1 AND id IN (SELECT section_id FROM section_companies WHERE company_id IN ('.$this->data['company_id'].'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Section->create();
                $this->data['Section']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Section']['created']    = $dateNow;
                $this->data['Section']['created_by'] = $user['User']['id'];
                $this->data['Section']['is_active']  = 1;
                if ($this->Section->save($this->data)) {
                    $lastInsertId = $this->Section->getLastInsertId();
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Section'], 'sections');
//                    $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'sections';
//                    $restCode[$r]['actodo']     = 'is';
//                    $r++;
                    // Section Company
                    if (isset($this->data['company_id'])) {
                        mysql_query("INSERT INTO section_companies (section_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['company_id'] . "')");
                        // Convert to REST
//                        $restCode[$r]['section_id'] = $this->data['Section']['sys_code'];
//                        $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id']);
//                        $restCode[$r]['dbtodo']     = 'section_companies';
//                        $restCode[$r]['actodo']     = 'is';
//                        $r++;
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Add New');
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
            if ($this->Helper->checkDouplicateEdit('name', 'sections', $id, $this->data['Section']['name'], 'is_active = 1 AND id IN (SELECT section_id FROM section_companies WHERE company_id IN ('.$this->data['company_id'].'))')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Section']['modified']    = $dateNow;
                $this->data['Section']['modified_by'] = $user['User']['id'];
                if ($this->Section->save($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Section'], 'sections');
//                    $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//                    $restCode[$r]['dbtodo'] = 'sections';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "sys_code = '".$this->data['Section']['sys_code']."'";
//                    $r++;
                    // Section Company
                    mysql_query("DELETE FROM section_companies WHERE section_id=" . $id);
                    // Convert to REST
//                    $restCode[$r]['dbtodo'] = 'section_companies';
//                    $restCode[$r]['actodo'] = 'dt';
//                    $restCode[$r]['con']    = "section_id = ".$this->data['Section']['sys_code'];
//                    $r++;
                    if (isset($this->data['company_id'])) {
                        mysql_query("INSERT INTO section_companies (section_id, company_id) VALUES ('" . $id . "','" . $this->data['company_id']. "')");
                        // Convert to REST
//                        $restCode[$r]['section_id'] = $this->data['Section']['sys_code'];
//                        $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['company_id']);
//                        $restCode[$r]['dbtodo']     = 'other_companies';
//                        $restCode[$r]['actodo']     = 'is';
//                        $r++;
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Edit', $id);
            $this->data = $this->Section->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact("companies"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
//        $r = 0;
//        $restCode = array();
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Section->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Delete', $id);
        mysql_query("UPDATE `sections` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
//        $restCode[$r]['is_active']   = 2;
//        $restCode[$r]['modified']    = $dateNow;
//        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//        $restCode[$r]['dbtodo'] = 'sections';
//        $restCode[$r]['actodo'] = 'ut';
//        $restCode[$r]['con']    = "sys_code = '".$this->data['Section']['sys_code']."'";
        // Save File Send
//        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Section', 'Export to Excel');
            $filename = "public/report/section_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Sections' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . GENERAL_DESCRIPTION;
            $conditionUser = " AND id IN (SELECT section_id FROM section_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $query = mysql_query('SELECT id, name, description '
                    . '           FROM sections WHERE is_active=1'.$conditionUser.' ORDER BY name');
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