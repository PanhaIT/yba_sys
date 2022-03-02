<?php
class MeetingThemesController extends AppController {
    var $name = 'MeetingThemes';
    var $components = array('Helper', 'Inventory');
    var $uses = array("MeetingTheme");

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Dashborad');
        $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
        $egroups  = ClassRegistry::init('Egroup')->find('list', array('fields' => array('Egroup.id', 'Egroup.name'),'conditions' => array('Egroup.is_active = 1'), 'order' => 'Egroup.name'));
        $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact('companies', 'employees','egroups'));
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function viewByUser(){
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
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'View', $id);
        $this->data = $this->MeetingTheme->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result  = array();
            if ($this->Helper->checkDouplicate('code', 'meeting_themes', $this->data['MeetingTheme']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->MeetingTheme->create();
                $this->data['MeetingTheme']['created']    = $dateNow;
                $this->data['MeetingTheme']['created_by'] = $user['User']['id'];
                $this->data['MeetingTheme']['is_active']  = 1;
                $this->data['MeetingTheme']['code']    = '';
                if ($this->MeetingTheme->save($this->data)) {
                    $MeetingThemeId = $this->MeetingTheme->id;
                    // User MeetingTheme
                    if(!empty($this->data['MeetingTheme']['user_id'])){
                        for($i=0;$i<sizeof($this->data['MeetingTheme']['user_id']);$i++){
                            mysql_query("INSERT INTO user_meeting_themes (user_id, meeting_theme_id) VALUES ('".$this->data['MeetingTheme']['user_id'][$i]."','".$MeetingThemeId."')");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Add New', $MeetingThemeId);
                    $result['id']    = $MeetingThemeId;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Add New');
        $code = $this->Helper->getAutoGenerateMeetingTheme();
        $egroups  = ClassRegistry::init('Egroup')->find('list', array('fields' => array('Egroup.id', 'Egroup.name'),'conditions' => array('Egroup.is_active = 1'), 'order' => 'Egroup.name'));
        $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
        $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact('companies','employees','code','egroups'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            if ($this->Helper->checkDouplicateEdit('code', 'meeting_themes',$id, $this->data['MeetingTheme']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['MeetingTheme']['modified'] = $dateNow;
                $this->data['MeetingTheme']['modified_by'] = $user['User']['id'];
                $this->data['MeetingTheme']['is_active']  = 1;
                if ($this->MeetingTheme->save($this->data)) {
                    mysql_query("DELETE FROM user_meeting_themes WHERE meeting_theme_id=".$id);
                    if(!empty($this->data['MeetingTheme']['user_id'])){
                        for($i=0;$i<sizeof($this->data['MeetingTheme']['user_id']);$i++){
                            mysql_query("INSERT INTO user_meeting_themes (user_id, meeting_theme_id) VALUES ('".$this->data['MeetingTheme']['user_id'][$i]."','".$id."')");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Add New',$id);
                    $result['id']    = $id;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->data = $this->MeetingTheme->read(null, $id);
            $egroups  = ClassRegistry::init('Egroup')->find('list', array('fields' => array('Egroup.id', 'Egroup.name'),'conditions' => array('Egroup.is_active = 1'), 'order' => 'Egroup.name'));
            $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
            $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('companies','employees','egroups'));
        }
    }

    function delete($id = null) {
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->MeetingTheme->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingTheme', 'Delete', $id);
        mysql_query("UPDATE `meeting_themes` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function todoListByUser($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $users  = ClassRegistry::init('User')->find('all', array('fields' => array('User.id', 'User.first_name', 'User.last_name'),'order' => 'User.id', 'conditions' => array('User.id IN (SELECT user_id FROM user_meeting_themes WHERE meeting_theme_id = '.$id.')')));
        $this->set(compact('users'));
    }

    function todoList($userId=null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set(compact('userId'));
    }

    function todoListAjax($userId=null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->set(compact('userId'));
    }

    function viewTodoList($id=null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('TodoList');
        $this->loadModel('TodoListDetail');
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $todoListDetails = ClassRegistry::init('TodoListDetails')->find("all", array('conditions' => array('TodoListDetails.todo_list_id' => $id)));
        $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'View', $id);
        $this->data = $this->TodoList->read(null, $id);
        $this->set(compact('todoListDetails'));
    }
}
?>