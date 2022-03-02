<?php

class VendorContactsController extends AppController {

    var $name = 'VendorContacts';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Dashboard');
    }

    function ajax($vendorId) {
        $this->layout = 'ajax';
        $this->set(compact('vendorId'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'View', $id);
        $this->data = $this->VendorContact->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('contact_name', 'vendor_contacts', $this->data['VendorContact']['contact_name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } 
            $r = 0;
            $restCode  = array();
            $dateNow   = date("Y-m-d H:i:s");
            $this->VendorContact->create();
            $this->data['VendorContact']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['VendorContact']['created']    = $dateNow;
            $this->data['VendorContact']['created_by'] = $user['User']['id'];
            $this->data['VendorContact']['is_active']  = 1;
            if ($this->VendorContact->save($this->data)) {    
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['VendorContact'], 'vendor_contacts');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'vendor_contacts';
                $restCode[$r]['actodo']   = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Add New', $this->VendorContact->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        } 
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $vendors = ClassRegistry::init('Vendor')->find('list', array('order' => 'name', 'conditions' => array('is_active' => 1)));          
        $this->set(compact('sexes', 'vendors', 'companies'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('contact_name', 'vendor_contacts', $id, $this->data['VendorContact']['contact_name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $r = 0;
            $restCode  = array();
            $dateNow   = date("Y-m-d H:i:s");
            $this->data['VendorContact']['modified']    = $dateNow;
            $this->data['VendorContact']['modified_by'] = $user['User']['id'];
            $this->data['VendorContact']['is_active']   = 1;
            if ($this->VendorContact->save($this->data)) {
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['VendorContact'], 'vendor_contacts');
                $restCode[$r]['dbtodo'] = 'vendor_contacts';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$this->data['VendorContact']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Edit', $id);
        if (empty($this->data)) {
            $this->data = $this->VendorContact->read(null, $id);
        }
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $vendors = ClassRegistry::init('Vendor')->find('list', array('order' => 'name', 'conditions' => array('is_active' => 1)));    
        $this->set(compact('sexes', 'vendors', 'companies'));
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
        $this->data = $this->VendorContact->read(null, $id);
        mysql_query("UPDATE `vendor_contacts` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id." AND `id` NOT IN (1,2);");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'vendor_contacts';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['VendorContact']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }    
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Concatct', 'Export to Excel');
            $filename = "public/report/vendor_contact_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'VendorContacts' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_CUSTOMER. "\t" . TABLE_TITLE_PERSON ."\t" . TABLE_CONTACT_NAME. "\t" . TABLE_CONTACT_TEL. "\t" . TABLE_CONTACT_EMAIL. "\t" . TABLE_NOTE;
            $conditionUser = " AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
            $query = mysql_query('SELECT id, (SELECT name FROM companies WHERE id = vendor_contacts.company_id), (SELECT name FROM vendors WHERE id = vendor_contacts.vendor_id), title, contact_name, contact_telephone, contact_email, note FROM vendor_contacts WHERE is_active=1'.$conditionUser.' ORDER BY contact_name ASC');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    

}

?>