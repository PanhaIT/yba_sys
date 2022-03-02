<?php

class DiscountsController extends AppController {

    var $name = 'Discounts';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Discount', 'Dashborad');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode  = array();
            $dateNow   = date("Y-m-d H:i:s");
            $this->Discount->create();
            $this->data['Discount']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Discount']['created']    = $dateNow;
            $this->data['Discount']['created_by'] = $user['User']['id'];
            $this->data['Discount']['is_active'] = 1;
            if ($this->Discount->save($this->data)) {
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['Discount'], 'discounts');
                $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['Discount']['company_id']);
                $restCode[$r]['created_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
                $restCode[$r]['modified']   = $dateNow;
                $restCode[$r]['dbtodo']     = 'discounts';
                $restCode[$r]['actodo']     = 'is';
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Discount', 'Save Add New', $this->Discount->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Discount', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Discount', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $this->set(compact('companies'));
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
        $this->data = $this->Discount->read(null, $id);
        mysql_query("UPDATE `discounts` SET `is_active`=2, `modified`='".date("Y-m-d H:i:s")."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'discounts';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['Discount']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Discount', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>