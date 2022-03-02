<?php
class TodoListsController extends AppController {
    var $name = 'TodoLists';
    var $components = array('Helper', 'Inventory');
    var $uses = array("TodoList");

    function add($tab = '0'){
        $this->layout = 'todolist';
        $user  = $this->getCurrentUser();
    }

    function viewByUser(){
        $this->layout = 'todolist';
        $user  = $this->getCurrentUser();
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Dashborad');
        $services   = ClassRegistry::init('Service')->find('all', array('conditions' => array('Service.is_active = 1'), 'order' => 'Service.name'));
        $progresses = ClassRegistry::init('Progresse')->find('list', array('conditions' => array('Progresse.is_active = 1'), 'order' => 'Progresse.name'));
        $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
        $customers  = ClassRegistry::init('Customer')->find('all', array('fields' => array('Customer.id', 'Customer.customer_code', 'Customer.name'),'conditions' => array('Customer.is_active = 1'), 'order' => 'Customer.name'));
        $priorities = ClassRegistry::init('Priority')->find('list', array('conditions' => array('Priority.is_active = 1'), 'order' => 'Priority.name'));
        $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact('companies', 'priorities', 'progresses', 'employees', 'customers','services'));
    }

    function ajax($TodoListPriorityId='all',$TodoListProgresseId='all',$TodoListCustomerId='all',$serviceId='all',$startDate=null,$endDate=null) {
        $this->layout = 'ajax';
        $this->set(compact('TodoListPriorityId','TodoListProgresseId','TodoListCustomerId','serviceId','startDate','endDate'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $todoListDetails = ClassRegistry::init('TodoListDetails')->find("all", array('conditions' => array('TodoListDetails.todo_list_id' => $id)));
        $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'View', $id);
        $this->data = $this->TodoList->read(null, $id);
        $this->set(compact('todoListDetails'));
    }

    function addTodoList($cloneId=null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('TodoListDetail');
        if (!empty($this->data)) {
            $result  = array();
            if ($this->Helper->checkDouplicate('code', 'todo_lists', $this->data['TodoList']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->TodoList->create();
                $this->data['TodoList']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['TodoList']['created']    = $dateNow;
                $this->data['TodoList']['created_by'] = $user['User']['id'];
                $this->data['TodoList']['code']  = '';
                $this->data['TodoList']['is_active']  = 1;
                $this->data['TodoList']['status'] = $this->data['TodoList']['progresse_id'];
                $this->data['TodoList']['start_date'] = $this->data['TodoList']['start_date']!=''?$this->data['TodoList']['start_date']:NULL;
                $this->data['TodoList']['end_date'] = $this->data['TodoList']['end_date']!=''?$this->data['TodoList']['end_date']:NULL;
                if ($this->TodoList->save($this->data)) {
                    $TodoListId = $this->TodoList->id;
                    $modCode = $this->Helper->getAutoGenerateTodoList();
                    mysql_query("UPDATE todo_lists SET code = '".$modCode."' WHERE id = ".$TodoListId);
                    for ($i = 0; $i < sizeof($_POST['service_id']); $i++) {
                        if(!empty($_POST['service_id'][$i])){
                            $todoListDetail = array();
                            // Quotation Detail
                            $this->TodoListDetail->create();
                            $todoListDetail['TodoListDetail']['todo_list_id'] = $TodoListId;
                            $todoListDetail['TodoListDetail']['service_id']   = $_POST['service_id'][$i];
                            $this->TodoListDetail->save($todoListDetail);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Add New', $TodoListId);
                    $result['id']    = $TodoListId;
                    $result['code']  = $modCode;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // User Activity
        if(!empty($cloneId)){
            $this->data = $this->TodoList->read(null, $cloneId);
            $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Clone Add New');
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Add New');
        }
        $todoListDetails = ClassRegistry::init('TodoListDetails')->find("all", array('conditions' => array('TodoListDetails.todo_list_id' => $cloneId)));
        $services   = ClassRegistry::init('Service')->find('list', array('conditions' => array('Service.is_active = 1'), 'order' => 'Service.name'));
        $progresses = ClassRegistry::init('Progresse')->find('list', array('conditions' => array('Progresse.is_active = 1'), 'order' => 'Progresse.name'));
        $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
        $customers  = ClassRegistry::init('Customer')->find('list', array('conditions' => array('Customer.is_active = 1'), 'order' => 'Customer.name'));
        $priorities = ClassRegistry::init('Priority')->find('list', array('conditions' => array('Priority.is_active = 1'), 'order' => 'Priority.name'));
        $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact('companies', 'priorities', 'progresses', 'employees', 'customers','services','todoListDetails','cloneId'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('TodoListDetail');
        if (!empty($this->data)) {
            $result = array();
            if ($this->Helper->checkDouplicateEdit('code', 'todo_lists', $this->data['TodoList']['id'], $this->data['TodoList']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['TodoList']['modified'] = $dateNow;
                $this->data['TodoList']['modified_by'] = $user['User']['id'];
                $this->data['TodoList']['status'] = $this->data['TodoList']['progresse_id'];
                $this->data['TodoList']['start_date'] = $this->data['TodoList']['start_date']!=''?$this->data['TodoList']['start_date']:NULL;
                $this->data['TodoList']['end_date'] = $this->data['TodoList']['end_date']!=''?$this->data['TodoList']['end_date']:NULL;
                if ($this->TodoList->save($this->data)) {
                    mysql_query("DELETE FROM todo_list_details WHERE todo_list_id=".$id);
                    for ($i = 0; $i < sizeof($_POST['service_id']); $i++) {
                        if(!empty($_POST['service_id'][$i])){
                            $todoListDetail = array();
                            $this->TodoListDetail->create();
                            $todoListDetail['TodoListDetail']['todo_list_id'] = $id;
                            $todoListDetail['TodoListDetail']['service_id']   = $_POST['service_id'][$i];
                            $this->TodoListDetail->save($todoListDetail);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Add New', $id);
                    $result['id']    = $id;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->data = $this->TodoList->read(null, $id);
            $todoListDetails = ClassRegistry::init('TodoListDetails')->find("all", array('conditions' => array('TodoListDetails.todo_list_id' => $id)));
            $services   = ClassRegistry::init('Service')->find('list', array('conditions' => array('Service.is_active = 1'), 'order' => 'Service.name'));
            $progresses = ClassRegistry::init('Progresse')->find('list', array('fields' => array('Progresse.id', 'Progresse.name'),  'conditions' => array('Progresse.is_active = 1'), 'order' => 'Progresse.name'));
            $employees  = ClassRegistry::init('Employee')->find('list', array('conditions' => array('Employee.is_active = 1'), 'order' => 'Employee.name'));
            $customers  = ClassRegistry::init('Customer')->find('list', array('conditions' => array('Customer.is_active = 1'), 'order' => 'Customer.name'));
            $priorities = ClassRegistry::init('Priority')->find('list', array('conditions' => array('Priority.is_active = 1'), 'order' => 'Priority.name'));
            $companies  = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('companies', 'priorities', 'progresses', 'employees', 'customers','services','todoListDetails'));
        }
    }

    function delete($id = null) {
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->TodoList->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Delete', $id);
        mysql_query("UPDATE `todo_lists` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function approve($id = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $dateNow  = date("Y-m-d H:i:s");
        // User Activity
        $this->data = $this->TodoList->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'TodoList', 'Approve', $id);
        mysql_query("UPDATE `todo_lists` SET `status`=3,`is_shared`=1,`start_date`='".$this->data['TodoList']['created']."', `end_date`='".$_POST['approve_date']."', `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save File Send
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function getServiceGroup($id=null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result = array();
        if(!empty($id)){
            $sqlService  = mysql_query("SELECT service_groups.name AS sectionName,services.code AS serviceCode FROM services 
            INNER JOIN service_groups ON service_groups.id = services.service_group_id WHERE services.is_active=1 AND services.id=".$id);
            $rowService  = mysql_fetch_array($sqlService);
            $result['service_code']  = $rowService['serviceCode'];
            $result['section_name']  = $rowService['sectionName'];
            $result['error'] = 0;
            echo json_encode($result);
            exit;
        }else{
            $result['error'] = 1;
            echo json_encode($result);
            exit;
        }
    }

    function shareTodolist($id=null,$owner=null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result = array();
        $this->loadModel('UserShareTodolist');
        if(!empty($_POST['todo_list_id'])){
            mysql_query("DELETE FROM user_share_todolists WHERE todo_list_id=".$_POST['todo_list_id']);
            for ($i = 0; $i < sizeof($this->data['User']['user_id']); $i++) {
                if(!empty($this->data['User']['user_id'][$i])){
                    $userShareTodolist = array();
                    $this->UserShareTodolist->create();
                    $userShareTodolist['UserShareTodolist']['user_id'] = $user['User']['id'];
                    $userShareTodolist['UserShareTodolist']['todo_list_id'] = $_POST['todo_list_id'];
                    $userShareTodolist['UserShareTodolist']['share_user_id']   = $this->data['User']['user_id'][$i];
                    $userShareTodolist['UserShareTodolist']['description']   = $this->data['User']['description'];
                    if ($this->UserShareTodolist->save($userShareTodolist)) {
                        $result['error'] = 0;
                    }else{
                        $result['error'] = 1;
                    }
                }else{
                    $result['error'] = 1;
                }
            }
            echo json_encode($result);
            exit;
        }
        $this->set(compact('id','owner'));
    }
}
?>