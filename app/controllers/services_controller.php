<?php

class ServicesController extends AppController {

    var $name = 'Services';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Dashboard');
    }

    function ajax() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact('companies'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->data = $this->Service->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'View', $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $result  = array();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('code', 'services', $this->data['Service']['code'], 'is_active = 1 AND company_id = '.$this->data['Service']['company_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New (Name ready existed)');
                echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Service->create();
                $this->data['Service']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Service']['created']    = $dateNow;
                $this->data['Service']['created_by'] = $user['User']['id'];
                $this->data['Service']['is_active'] = 1;
                if ($this->Service->save($this->data)) {
                    $lastInsertId = $this->Service->id;
                    // Service Branch
                    if (!empty($this->data['Service']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Service']['branch_id']); $i++) {
                            mysql_query("INSERT INTO service_branches (service_id,branch_id) VALUES ('" . $lastInsertId . "','" . $this->data['Service']['branch_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New', $this->Service->id);
                    $result['id']    = $lastInsertId;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Add New');
        $code = $this->Helper->getAutoGenerateService();
        $companies = ClassRegistry::init('Company')->find('list',
                array(
                    'joins' => array(
                        array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                        )
                    ),
                    'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                )
        );
        $branches = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $serviceGroups = ClassRegistry::init('ServiceGroup')->find("list", array("conditions" => array("ServiceGroup.is_active = 1")));
        $this->set(compact('branches', 'companies','serviceGroups','code'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $result  = array();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 'services', $id, $this->data['Service']['code'], 'is_active = 1 AND company_id = '.$this->data['Service']['company_id'])) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Service']['modified']    = $dateNow;
                $this->data['Service']['modified_by'] = $user['User']['id'];
                if ($this->Service->save($this->data)) {
                    mysql_query("DELETE FROM service_branches WHERE service_id=" . $id);
                    if (!empty($this->data['Service']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Service']['branch_id']); $i++) {
                            mysql_query("INSERT INTO service_branches (service_id,branch_id) VALUES ('" . $id . "','" . $this->data['Service']['branch_id'][$i] . "')");
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit', $id);
                    $result['id']    = $id;
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Error)', $id);
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Edit', $id);
            $this->data = $this->Service->read(null, $id);
            $companies = ClassRegistry::init('Company')->find('list',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                            )
                        ),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                    )
            );
            $branches = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
            $serviceGroups = ClassRegistry::init('ServiceGroup')->find("list", array("conditions" => array("ServiceGroup.is_active = 1")));
            $this->set(compact('branches', 'companies','serviceGroups'));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Service->read(null, $id);
        // User Activity
        mysql_query("UPDATE `services` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>