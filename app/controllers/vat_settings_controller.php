<?php

class VatSettingsController extends AppController {

    var $name = 'VatSettings';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
    }
    
    function add(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'vat_settings', $this->data['VatSetting']['name'], 'is_active = 1 AND type = '.$this->data['VatSetting']['type'].' AND company_id = '.$this->data['VatSetting']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                if($this->data['VatSetting']['type'] == 1){
                    $chartAccountVat = 102;
                } else {
                    $chartAccountVat = 104;
                }
                $this->VatSetting->create();
                $this->data['VatSetting']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['VatSetting']['chart_account_id'] = $chartAccountVat;
                $this->data['VatSetting']['created']    = $dateNow;
                $this->data['VatSetting']['created_by'] = $user['User']['id'];
                if ($this->VatSetting->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        $lastInsertId = $this->VatSetting->getLastInsertId();
                        // Convert to REST
                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['VatSetting'], 'vat_settings');
                        $restCode[$r]['modified']   = $dateNow;
                        $restCode[$r]['dbtodo']     = 'vat_settings';
                        $restCode[$r]['actodo']     = 'is';
                        $r++;
                        // VAT for module default
                        if (isset($this->data['VatSetting']['apply_to'])) {
                            for ($i = 0; $i < sizeof($this->data['VatSetting']['apply_to']); $i++) {
                                // Update VAT Modules Disabled
                                mysql_query("UPDATE vat_modules SET is_active = 2 WHERE apply_to = " . $this->data['VatSetting']['apply_to'][$i]);
                                // Convert to REST
                                $restCode[$r]['is_active'] = 2;
                                $restCode[$r]['dbtodo']  = 'vat_modules';
                                $restCode[$r]['actodo']  = 'ut';
                                $restCode[$r]['con']     = "apply_to = ".$this->data['VatSetting']['apply_to'][$i];
                                $r++;
                                // Insert VAT Modules
                                mysql_query("INSERT INTO vat_modules (vat_setting_id, apply_to, created, created_by) VALUES ('".$lastInsertId."','".$this->data['VatSetting']['apply_to'][$i]."', '".$dateNow."', ".$user['User']['id'].")");
                                // Convert to REST
                                $restCode[$r]['vat_setting_id'] = $this->data['VatSetting']['sys_code'];
                                $restCode[$r]['apply_to']  = $this->data['VatSetting']['apply_to'][$i];
                                $restCode[$r]['created']   = $dateNow;
                                $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                                $restCode[$r]['dbtodo']     = 'vat_modules';
                                $restCode[$r]['actodo']     = 'is';
                                $r++;
                            }
                        }
                        // Save File Send
                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Add New', $lastInsertId);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Add New (Error '.$error.')');
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Add New');
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
            if ($this->Helper->checkDouplicateEdit('name', 'vat_settings', $id, $this->data['VatSetting']['name'], 'is_active = 1 AND type = '.$this->data['VatSetting']['type'].' AND company_id = '.$this->data['VatSetting']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                if($this->data['VatSetting']['type'] == 1){
                    $chartAccountVat = 102;
                } else {
                    $chartAccountVat = 104;
                }
                $this->data['VatSetting']['chart_account_id'] = $chartAccountVat;
                $this->data['VatSetting']['modified']    = $dateNow;
                $this->data['VatSetting']['modified_by'] = $user['User']['id'];
                if ($this->VatSetting->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['VatSetting'], 'vat_settings');
                    $restCode[$r]['dbtodo'] = 'vat_settings';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['VatSetting']['sys_code']."'";
                    $r++;
                    // Update VAT Modules Disabled
                    mysql_query("UPDATE vat_modules SET is_active = 2 WHERE vat_setting_id = " . $this->data['VatSetting']['id']);
                    // Convert to REST
                    $restCode[$r]['is_active'] = 2;
                    $restCode[$r]['dbtodo']  = 'vat_modules';
                    $restCode[$r]['actodo']  = 'ut';
                    $restCode[$r]['con']     = "vat_setting_id = ".$this->data['VatSetting']['sys_code'];
                    $r++;
                    // VAT for module default
                    if (isset($this->data['VatSetting']['apply_to'])) {
                        for ($i = 0; $i < sizeof($this->data['VatSetting']['apply_to']); $i++) {
                            // Insert VAT Modules
                            mysql_query("INSERT INTO vat_modules (vat_setting_id, apply_to, created, created_by) VALUES ('".$id."','".$this->data['VatSetting']['apply_to'][$i]."', '".$dateNow."', ".$user['User']['id'].")");
                            // Convert to REST
                            $restCode[$r]['vat_setting_id'] = $this->data['VatSetting']['sys_code'];
                            $restCode[$r]['apply_to']   = $this->data['VatSetting']['apply_to'][$i];
                            $restCode[$r]['created']    = $dateNow;
                            $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                            $restCode[$r]['dbtodo']     = 'vat_modules';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Edit', $id);
            $this->data = $this->VatSetting->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact("companies"));
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
        $this->data = $this->VatSetting->read(null, $id);
        mysql_query("UPDATE `vat_settings` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'vat_settings';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['VatSetting']['sys_code']."'";
        $r++;
        // Update VAT Modules
        mysql_query("UPDATE vat_modules SET is_active = 2 WHERE vat_setting_id = ".$id);
        // Convert to REST
        $restCode[$r]['is_active'] = 2;
        $restCode[$r]['dbtodo']  = 'vat_modules';
        $restCode[$r]['actodo']  = 'ut';
        $restCode[$r]['con']     = "vat_setting_id = ".$this->data['VatSetting']['sys_code'];
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Vat Setting', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>