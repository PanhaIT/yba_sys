<?php

class TermConditionAppliesController extends AppController {

    var $name = 'TermConditionApplies';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'View', $id);
        $this->data = $this->TermConditionApply->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            for ($i = 0; $i < sizeof($_POST['term_condition_type']); $i++) {
                $dateNow   = date("Y-m-d H:i:s");
                $termApply = array();
                $this->TermConditionApply->create();
                $termApply['TermConditionApply']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $termApply['TermConditionApply']['module_type_id'] = $this->data['TermConditionApply']['module_type_id'];
                $termApply['TermConditionApply']['term_condition_type_id']    = $_POST['term_condition_type'][$i];
                $termApply['TermConditionApply']['term_condition_default_id'] = $_POST['term_condition_default'][$i]!=''?$_POST['term_condition_default'][$i]:0;
                $termApply['TermConditionApply']['created']    = $dateNow;
                $termApply['TermConditionApply']['created_by'] = $user['User']['id'];
                $termApply['TermConditionApply']['is_active']  = 1;
                $this->TermConditionApply->save($termApply);
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($termApply['TermConditionApply'], 'term_condition_applies');
                $restCode[$r]['modified'] = $dateNow;
                $restCode[$r]['dbtodo']   = 'term_condition_applies';
                $restCode[$r]['actodo']   = 'is';
                $r++;
            }
            // Save File Send
            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Save Add New');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $r = 0;
            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['TermConditionApply']['term_condition_default_id'] = $this->data['TermConditionApply']['term_condition_default_id']!=""?$this->data['TermConditionApply']['term_condition_default_id']:0;
            $this->data['TermConditionApply']['modified']    = $dateNow;
            $this->data['TermConditionApply']['modified_by'] = $user['User']['id'];
            if ($this->TermConditionApply->save($this->data)) {
                // Convert to REST
                $restCode[$r] = $this->Helper->convertToDataSync($this->data['TermConditionApply'], 'term_condition_applies');
                $restCode[$r]['dbtodo'] = 'term_condition_applies';
                $restCode[$r]['actodo'] = 'ut';
                $restCode[$r]['con']    = "sys_code = '".$this->data['TermConditionApply']['sys_code']."'";
                // Save File Send
                $this->Helper->sendFileToSync($restCode, 0, 0);
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Edit', $id);
            $this->data = $this->TermConditionApply->read(null, $id);
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
        $this->data = $this->TermConditionApply->read(null, $id);
        mysql_query("UPDATE `term_condition_applies` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Convert to REST
        $restCode[$r]['is_active']   = 2;
        $restCode[$r]['modified']    = $dateNow;
        $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
        $restCode[$r]['dbtodo'] = 'term_condition_applies';
        $restCode[$r]['actodo'] = 'ut';
        $restCode[$r]['con']    = "sys_code = '".$this->data['TermConditionApply']['sys_code']."'";
        // Save File Send
        $this->Helper->sendFileToSync($restCode, 0, 0);
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'TermConditionApply', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>