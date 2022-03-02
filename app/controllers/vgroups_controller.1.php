<?php

class VgroupsController extends AppController {

    var $name = 'Vgroups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'View', $id);

        $vendors = ClassRegistry::init('VendorVgroup')->find('all', array(
                    'fields' => array('Vendor.*'),
                    'conditions' => array('VendorVgroup.vgroup_id' => $id, 'Vendor.is_active' => 1),
                    'order' => array('Vendor.name DESC'))
        );
        $this->set('vgroup', $this->Vgroup->read(null, $id));
        $this->set(compact('vendors'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = $this->data['Vgroup']['company_id'];
            if ($this->Helper->checkDouplicate('name', 'vgroups', $this->data['Vgroup']['name'], 'is_active = 1 AND id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Add New (Error Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Vgroup->create();
                $this->data['Vgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Vgroup']['created']    = $dateNow;
                $this->data['Vgroup']['created_by'] = $user['User']['id'];
                $this->data['Vgroup']['is_active']  = 1;
                if ($this->Vgroup->save($this->data)) {
                    $lastInsertId = $this->Vgroup->getLastInsertId();
                    // vendor group
                    if (isset($this->data['Vgroup']['vendor_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vgroup']['vendor_id']); $i++) {
                            mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $this->data['Vgroup']['vendor_id'][$i] . "','" . $lastInsertId . "')");
                        }
                    }
                    // vgroup company
                    if (isset($this->data['Vgroup']['company_id'])) {
                        mysql_query("INSERT INTO vgroup_companies (vgroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vgroup']['company_id'] . "')");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Add New');
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
            $comCheck = $this->data['Vgroup']['company_id'];
            if ($this->Helper->checkDouplicateEdit('name', 'vgroups', $id, $this->data['Vgroup']['name'], 'is_active = 1 AND id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Edit (Error Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vgroup']['modified']    = $dateNow;
                $this->data['Vgroup']['modified_by'] = $user['User']['id'];
                if ($this->Vgroup->save($this->data)) {
                    mysql_query("DELETE FROM vendor_vgroups WHERE vgroup_id=" . $id);
                    if (isset($this->data['Vgroup']['vendor_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vgroup']['vendor_id']); $i++) {
                            mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $this->data['Vgroup']['vendor_id'][$i] . "','" . $id . "')");
                        }
                    }
                    mysql_query("DELETE FROM vgroup_companies WHERE vgroup_id=" . $id);
                    if (isset($this->data['Vgroup']['company_id'])) {
                        mysql_query("INSERT INTO vgroup_companies (vgroup_id, company_id) VALUES ('" . $id . "','" . $this->data['Vgroup']['company_id'] . "')");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Edit', $id);
            $this->data = $this->Vgroup->read(null, $id);
            $companySellecteds = ClassRegistry::init('PgroupCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('pgroup_id' => $id)));
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
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Vgroup->read(null, $id);
        mysql_query("UPDATE `vgroups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function vendor($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function vendorAjax($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Export to Excel');
            $filename = "public/report/vendor_group_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Vendor Groups' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_GROUP_NAME. "\t" . GENERAL_DESCRIPTION;
            $conditionUser = " AND id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $query = mysql_query('SELECT id, name, description FROM vgroups WHERE is_active=1'.$conditionUser.' ORDER BY name');
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