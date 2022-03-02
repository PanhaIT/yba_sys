<?php

class GroupsController extends AppController {

    var $name = 'Groups';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'View', $id);
        $this->set('group', $this->Group->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $lastInsertId=9;
            if ($this->Helper->checkDouplicate('name', 'groups', $this->data['Group']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Add New (Name ready exsited)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Group->create();
                $this->data['Group']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Group']['created']  = $dateNow;
                $this->data['Group']['created_by'] = $user['User']['id'];
                $this->data['Group']['is_active']  = 1;
                if ($this->Group->save($this->data)) {
                    $lastInsertId=$this->Group->getLastInsertId();
                    // user group
                    if(!empty($this->data['Group']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Group']['user_id']);$i++){
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$this->data['Group']['user_id'][$i]."','".$lastInsertId."')");
                        }
                    }
                    // Permission
                    if(!empty($_POST['module_id'])){
                        for($i=0;$i<sizeof($_POST['module_id']);$i++){
                            mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$lastInsertId."','".$_POST['module_id'][$i]."');");
                            if(in_array($_POST['module_id'][$i],array(41,282,503,504,597,626))){//General Setting (Set Up), Sales (Set Up),Purchase (Set Up), POS (Set Up) ,Inventory (Set Up),Accounting (Set Up)
                                $permissionSetting = true;
                            }
                        }
                    }
                    // Permission
                    // $permissionSetting = false;
                    // $queryModule = mysql_query("SELECT id FROM modules");
                    // while($dataModule = mysql_fetch_array($queryModule)){
                    //     $module = "module_" . $dataModule['id'];
                    //     if(!empty($_POST[$module])){
                    //         mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$lastInsertId."','".$_POST[$module]."')");
                    //         if(in_array($dataModule['id'],array(41,282,503,504,597,626))){//General Setting (Set Up), Sales (Set Up),Purchase (Set Up), POS (Set Up) ,Inventory (Set Up),Accounting (Set Up)
                    //             $permissionSetting = true;
                    //         }
                    //     }
                    // }
                    if($permissionSetting == true){
                        mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$lastInsertId."', 646)");//module_id=646 is System Setting
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'groups', $id, $this->data['Group']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow = date("Y-m-d H:i:s");
                $this->data['Group']['modified']    = $dateNow;
                $this->data['Group']['modified_by'] = $user['User']['id'];
                if ($this->Group->save($this->data)) {
                    // user group
                    mysql_query("DELETE FROM user_groups WHERE group_id=".$id);
                    if(isset($this->data['Group']['user_id'])){
                        for($i=0;$i<sizeof($this->data['Group']['user_id']);$i++){
                            mysql_query("INSERT INTO user_groups (user_id,group_id) VALUES ('".$this->data['Group']['user_id'][$i]."','".$id."')");
                        }
                    }
                    // Permission
                    $permissionSetting = false;
                    mysql_query("DELETE FROM permissions WHERE group_id=".$id);
                    if(!empty($_POST['module_id'])){
                        for($i=0;$i<sizeof($_POST['module_id']);$i++){
                            mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$id."','".$_POST['module_id'][$i]."');");
                            if(in_array($_POST['module_id'][$i],array(41,282,503,504,597,626))){//General Setting (Set Up), Sales (Set Up),Purchase (Set Up), POS (Set Up) ,Inventory (Set Up),Accounting (Set Up)
                                $permissionSetting = true;
                            }
                        }
                    }
                    // $queryModule=mysql_query("SELECT id FROM modules");
                    // while($dataModule=mysql_fetch_array($queryModule)){
                    //     $module="module_" . $dataModule['id'];
                    //     if(isset($_POST[$module])){
                    //         mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$id."','".$_POST[$module]."')");
                    //         if(in_array($dataModule['id'],array(41,282,503,504,597,626))){
                    //             $permissionSetting = true;
                    //         }
                    //     }
                    // }
                    if($permissionSetting == true){
                        mysql_query("INSERT INTO permissions (group_id,module_id) VALUES ('".$id."', 646)");
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Edit', $id);
            $this->data = $this->Group->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Group', 'Delete');
        $this->Group->updateAll(
                array('Group.is_active' => "2"),
                array('Group.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>