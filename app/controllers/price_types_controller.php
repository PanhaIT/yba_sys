<?php

class PriceTypesController extends AppController {

    var $name = 'PriceTypes';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'View', $id);
        $this->set('price_type', $this->PriceType->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['PriceType']['company_id'])){
                $comCheck = implode(",", $this->data['PriceType']['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'price_types', $this->data['PriceType']['name'], 'is_active = 1 AND id IN (SELECT price_type_id FROM price_type_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Add New (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->PriceType->create();
                $this->data['PriceType']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['PriceType']['created']    = $dateNow;
                $this->data['PriceType']['created_by'] = $user['User']['id'];
                $this->data['PriceType']['is_active']  = 1;
                if ($this->PriceType->save($this->data)) {
                    $lastInsertId = $this->PriceType->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['PriceType'], 'price_types');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'price_types';
                    $restCode[$r]['actodo']     = 'is';
                    $r++;
                    // Price Type Company
                    if (isset($this->data['PriceType']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['PriceType']['company_id']); $i++) {
                            mysql_query("INSERT INTO price_type_companies (price_type_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['PriceType']['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['price_type_id'] = $this->data['PriceType']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'price_type_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                            if($_POST['apply_to'] == 1){
                                mysql_query("UPDATE pos_price_types SET is_active = 2 WHERE company_id = ".$this->data['PriceType']['company_id'][$i].";");
                                // Convert to REST
                                $restCode[$r]['is_active'] = 2;
                                $restCode[$r]['dbtodo'] = 'pos_price_types';
                                $restCode[$r]['actodo'] = 'ut';
                                $restCode[$r]['con']    = "company_id = ".$this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                                $r++;
                                mysql_query("INSERT INTO `pos_price_types` (`company_id`, `price_type_id`, `created`, `created_by`) VALUES (".$this->data['PriceType']['company_id'][$i].", ".$lastInsertId.", '".$dateNow."', ".$user['User']['id'].");");
                                // Convert to REST
                                $restCode[$r]['price_type_id'] = $this->data['PriceType']['sys_code'];
                                $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                                $restCode[$r]['created']    = $dateNow;
                                $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                                $restCode[$r]['dbtodo']     = 'pos_price_types';
                                $restCode[$r]['actodo']     = 'is';
                                $r++;
                            }
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Add New');
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
            if(!empty($this->data['PriceType']['company_id'])){
                $comCheck = implode(",", $this->data['PriceType']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'price_types', $id, $this->data['PriceType']['name'], 'is_active = 1 AND id IN (SELECT price_type_id FROM price_type_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Edit (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['PriceType']['modified']    = $dateNow;
                $this->data['PriceType']['modified_by'] = $user['User']['id'];
                if ($this->PriceType->save($this->data)) {
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['PriceType'], 'price_types');
                    $restCode[$r]['dbtodo'] = 'price_types';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "sys_code = '".$this->data['PriceType']['sys_code']."'";
                    $r++;
                    // Update Price Type
                    mysql_query("UPDATE pos_price_types SET is_active = 2 WHERE price_type_id = ".$id.";");
                    // Convert to REST
                    $restCode[$r]['is_active'] = 2;
                    $restCode[$r]['dbtodo'] = 'pos_price_types';
                    $restCode[$r]['actodo'] = 'ut';
                    $restCode[$r]['con']    = "price_type_id = ".$this->data['PriceType']['sys_code'];
                    $r++;
                    // Price Type Company
                    mysql_query("DELETE FROM price_type_companies WHERE price_type_id=" . $id);
                    // Convert to REST
                    $restCode[$r]['dbtodo'] = 'price_type_companies';
                    $restCode[$r]['actodo'] = 'dt';
                    $restCode[$r]['con']    = "price_type_id = ".$this->data['PriceType']['sys_code'];
                    $r++;
                    if (isset($this->data['PriceType']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['PriceType']['company_id']); $i++) {
                            mysql_query("INSERT INTO price_type_companies (price_type_id, company_id) VALUES ('" . $id . "','" . $this->data['PriceType']['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['price_type_id'] = $this->data['PriceType']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'price_type_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                            if($_POST['apply_to'] == 1){
                                mysql_query("UPDATE pos_price_types SET is_active = 2 WHERE company_id = ".$this->data['PriceType']['company_id'][$i].";");
                                // Convert to REST
                                $restCode[$r]['is_active'] = 2;
                                $restCode[$r]['dbtodo'] = 'pos_price_types';
                                $restCode[$r]['actodo'] = 'ut';
                                $restCode[$r]['con']    = "company_id = ".$this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                                $r++;
                                mysql_query("INSERT INTO `pos_price_types` (`company_id`, `price_type_id`, `created`, `created_by`) VALUES (".$this->data['PriceType']['company_id'][$i].", ".$id.", '".date("Y-m-d H:i:s")."', 1);");
                                // Convert to REST
                                $restCode[$r]['price_type_id'] = $this->data['PriceType']['sys_code'];
                                $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['PriceType']['company_id'][$i]);
                                $restCode[$r]['created']    = $dateNow;
                                $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                                $restCode[$r]['dbtodo']     = 'pos_price_types';
                                $restCode[$r]['actodo']     = 'is';
                                $r++;
                            }
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Save Edit (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Edit', $id);
            $this->data = $this->PriceType->read(null, $id);
            $companySellecteds = ClassRegistry::init('PriceTypeCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('price_type_id' => $id)));
            $companySellected = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact("companies", "companySellected"));
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
        $this->data = $this->PriceType->read(null, $id);
        mysql_query("UPDATE `price_types` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'price_types';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['PriceType']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function changeOrdering($id = null, $ordering = nll){
        if (!$id && !$ordering) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Price Type', 'Change Ordering', $id);
        mysql_query("UPDATE `price_types` SET `ordering`=".$ordering.", `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        exit;
    }

}

?>