<?php

class SalesTargetsController extends AppController {

    var $name = 'SalesTargets';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Dashborad');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'View', $id);
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->data = $this->SalesTarget->read(null, $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('employee_id', 'sales_targets', $this->data['SalesTarget']['employee_id'], 'is_active = 1 AND company_id = '.$this->data['SalesTarget']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->SalesTarget->create();
                $this->data['SalesTarget']['created_by'] = $user['User']['id'];
                $this->data['SalesTarget']['is_active'] = 1;
                if ($this->SalesTarget->save($this->data)) {
                    $budgetPlId = $this->SalesTarget->getLastInsertId();
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Add New', $budgetPlId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Add New');
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

    function edit($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('employee_id', 'sales_targets', $id, $this->data['SalesTarget']['employee_id'], 'is_active = 1 AND company_id = '.$this->data['SalesTarget']['company_id'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $user = $this->getCurrentUser();
                $this->data['SalesTarget']['modified_by'] = $user['User']['id'];
                if ($this->SalesTarget->save($this->data)) {
                    $budgetPlId = $this->data['SalesTarget']['id'];
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Edit', $budgetPlId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Save Edit (Error)', $budgetPlId);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Edit', $id);
            $this->data = $this->SalesTarget->read(null, $id);
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
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Sales Target', 'Delete', $id);
        $this->SalesTarget->updateAll(
                array('SalesTarget.is_active' => "2"),
                array('SalesTarget.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function employee($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function employeeAjax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }

}

?>