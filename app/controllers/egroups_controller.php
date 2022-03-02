<?php

class EgroupsController extends AppController {

    var $name = 'Egroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'View', $id);
        
        $employees = ClassRegistry::init('EmployeeEgroup')->find('all', array(
                    'fields' => array('Employee.*'),
                    'conditions' => array('EmployeeEgroup.egroup_id' => $id, 'Employee.is_active' => 1),
                    'order' => array('Employee.name DESC'))
        );
        $this->set('egroup', $this->Egroup->read(null, $id));
        $this->set(compact('employees'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Egroup']['company_id'])){
                $comCheck = implode(",", $this->data['Egroup']['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'egroups', $this->data['Egroup']['name'], 'is_active = 1 AND id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->Egroup->create();
                $this->data['Egroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Egroup']['created']    = $dateNow;
                $this->data['Egroup']['created_by'] = $user['User']['id'];
                $this->data['Egroup']['is_active']  = 1;
                if ($this->Egroup->save($this->data)) {
                    $lastInsertId = $this->Egroup->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Egroup'], 'egroups');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'egroups';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                    // Employee Group
                    if (isset($this->data['Egroup']['employee_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['employee_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $this->data['Egroup']['employee_id'][$i] . "','" . $lastInsertId . "')");
                            // Convert to REST
                            $restCode[$r]['egroup_id']   = $this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                            $restCode[$r]['employee_id'] = $this->Helper->getSQLSyncCode("employees", $this->data['Egroup']['employee_id'][$i]);
                            $restCode[$r]['dbtodo']      = 'employee_egroups';
                            $restCode[$r]['actodo']      =  'is';
                            $r++;
                        }
                    }
                    // Egroup Company
                    if (isset($this->data['Egroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO egroup_companies (egroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Egroup']['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['egroup_id']  = $this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSyncCode("companies", $this->data['Egroup']['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'egroup_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Add New');
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
            $comCheck = 0;
            if(!empty($this->data['Egroup']['company_id'])){
                $comCheck = implode(",", $this->data['Egroup']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'egroups', $id, $this->data['Egroup']['name'], 'is_active = 1 AND id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Egroup']['modified']    = $dateNow;
                $this->data['Egroup']['modified_by'] = $user['User']['id'];
                if ($this->Egroup->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Egroup'], 'egroups');
                    $restCode[$r]['dbtodo'] = 'egroups';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['Egroup']['sys_code']."'";
                    $r++;
                    // Employee Group
                    mysql_query("DELETE FROM employee_egroups WHERE egroup_id=" . $id);
                    // Convert to REST
                    $restCode[$r]['dbtodo'] = 'employee_egroups';
                    $restCode[$r]['actodo'] = 'dt';
                    $restCode[$r]['con']    = "egroup_id = ".$this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                    $r++;
                    if (isset($this->data['Egroup']['employee_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['employee_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $this->data['Egroup']['employee_id'][$i] . "','" . $id . "')");
                            // Convert to REST
                            $restCode[$r]['egroup_id']   = $this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                            $restCode[$r]['employee_id'] = $this->Helper->getSQLSyncCode("employees", $this->data['Egroup']['employee_id'][$i]);
                            $restCode[$r]['dbtodo']      = 'employee_egroups';
                            $restCode[$r]['actodo']      =  'is';
                            $r++;
                        }
                    }
                    // Egroup Company
                    mysql_query("DELETE FROM egroup_companies WHERE egroup_id=" . $id);
                    // Convert to REST
                    $restCode[$r]['dbtodo'] = 'egroup_companies';
                    $restCode[$r]['actodo'] = 'dt';
                    $restCode[$r]['con']    = "egroup_id = ".$this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                    $r++;
                    if (isset($this->data['Egroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO egroup_companies (egroup_id, company_id) VALUES ('" . $id . "','" . $this->data['Egroup']['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['egroup_id']  = $this->Helper->getSQLSync("egroups", $this->data['Egroup']['sys_code']);
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSyncCode("companies", $this->data['Egroup']['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'egroup_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // User Egroup
                    mysql_query("DELETE FROM user_egroups WHERE egroup_id=".$this->data['Egroup']['id']);
                    if(!empty($this->data['Egroup']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Egroup']['user_id']);$i++){
                            mysql_query("INSERT INTO user_egroups (user_id, egroup_id) VALUES ('".$this->data['Egroup']['user_id'][$i]."','".$this->data['Egroup']['id']."')");
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Edit', $id);
            $this->data = $this->Egroup->read(null, $id);
            $companySellecteds = ClassRegistry::init('EgroupCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('egroup_id' => $id)));
            $companySellected = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('companies', 'companySellected'));
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
        $this->data = $this->Egroup->read(null, $id);
        mysql_query("UPDATE `egroups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSyncCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'egroups';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['Egroup']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function employee($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function employee_ajax($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee Group', 'Export to Excel');
            $filename = "public/report/employee_groups_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Employee Groups' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_COMPANY. "\t" . TABLE_NAME;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM egroup_companies WHERE egroup_id = egroups.id)), name '
                    . '           FROM egroups WHERE is_active=1'.$conditionUser.' ORDER BY name');
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

    function indexEgroup() {
        $this->layout = 'ajax';
    }

    function ajaxEgroup() {
        $this->layout = 'ajax';
    }

    function viewEgroup($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'View', $id);
        $this->data = $this->Egroup->read(null, $id);
    }

    function addEgroup(){
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        if (!empty($this->data)) {
            // debug($this->data);exit;
            if ($this->Helper->checkDouplicate('name', 'egroups', $this->data['Egroup']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Egroup->create();
                $this->data['Egroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Egroup']['created_by'] = $user['User']['id'];
                $this->data['Egroup']['name']             = $this->data['Egroup']['name'];
                $this->data['Egroup']['is_active']  = 1;
                if ($this->Egroup->saveAll($this->data)) {
                    $lastInsertId = $this->Egroup->getLastInsertId();
                    // Egroup Company
                    if (isset($this->data['Egroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO egroup_companies (egroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Egroup']['company_id'][$i] . "')");
                        }
                    }
                    // User Egroup
                    if(!empty($this->data['Egroup']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Egroup']['user_id']);$i++){
                            mysql_query("INSERT INTO user_egroups (user_id, egroup_id) VALUES ('".$this->data['Egroup']['user_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Add New');
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('companies'));
        }
    }

    function editEgroup($id=null){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Egroup']['company_id'])){
                $comCheck = implode(",", $this->data['Egroup']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'egroups', $id, $this->data['Egroup']['name'], 'is_active = 1 AND id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Egroup']['modified']  = $dateNow;
                $this->data['Egroup']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Egroup']['created']   = $dateNow;
                $this->data['Egroup']['name']      = $this->data['Egroup']['name'];
                $this->data['Egroup']['is_active'] = 1;
                if ($this->Egroup->saveAll($this->data)) {
                    // Egroup Company
                    mysql_query("DELETE FROM egroup_companies WHERE egroup_id=" . $id);
                    if (isset($this->data['Egroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Egroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO egroup_companies (egroup_id, company_id) VALUES ('" . $id . "','" . $this->data['Egroup']['company_id'][$i] . "')");
                        }
                    }
                    // User Egroup
                    mysql_query("DELETE FROM user_egroups WHERE egroup_id=".$this->data['Egroup']['id']);
                    if(!empty($this->data['Egroup']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Egroup']['user_id']);$i++){
                            mysql_query("INSERT INTO user_egroups (user_id, egroup_id) VALUES ('".$this->data['Egroup']['user_id'][$i]."','".$this->data['Egroup']['id']."')");
                        }
                     }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Egroup->read(null, $id);
            $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Edit', $id);
            $companySellecteds = ClassRegistry::init('EgroupCompany')->find('list', array('fields' => array('EgroupCompany.id', 'EgroupCompany.company_id'), 'order' => 'EgroupCompany.id', 'conditions' => array('EgroupCompany.egroup_id' => $id)));
            $companySellected  = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'Company.id', 'conditions' => array('Company.is_active' => 1)));
            $this->set(compact('companySellected','companies'));
        }
    }

    function deleteEgroup($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `egroups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Egroup', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
}

?>