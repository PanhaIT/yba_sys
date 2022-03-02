<?php

class BudgetPlsController extends AppController {

    var $name = 'BudgetPls';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Dashborad');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'View', $id);
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set('budgetPl', $this->BudgetPl->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'budget_pls', $this->data['BudgetPl']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->BudgetPl->create();
                $this->data['BudgetPl']['created_by'] = $user['User']['id'];
                $this->data['BudgetPl']['is_active'] = 1;
                if ($this->BudgetPl->save($this->data)) {
                    $budgetPlId = $this->BudgetPl->getLastInsertId();
                    /**
                     * Budget P&L Detail
                     */
                    $this->loadModel('BudgetPlDetail');
                    for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                        if ($_POST['chart_account_id'][$i] != '') {
                            $this->BudgetPlDetail->create();
                            $BudgetPlDetail['BudgetPlDetail']['budget_pl_id'] = $budgetPlId;
                            $BudgetPlDetail['BudgetPlDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m1'] = $_POST['m1'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m2'] = $_POST['m2'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m3'] = $_POST['m3'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m4'] = $_POST['m4'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m5'] = $_POST['m5'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m6'] = $_POST['m6'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m7'] = $_POST['m7'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m8'] = $_POST['m8'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m9'] = $_POST['m9'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m10'] = $_POST['m10'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m11'] = $_POST['m11'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m12'] = $_POST['m12'][$i];
                            $this->BudgetPlDetail->save($BudgetPlDetail);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Add New', $budgetPlId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Add New');
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
            if ($this->Helper->checkDouplicateEdit('name', 'budget_pls', $id, $this->data['BudgetPl']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $user = $this->getCurrentUser();
                $this->data['BudgetPl']['modified_by'] = $user['User']['id'];
                if ($this->BudgetPl->save($this->data)) {
                    $budgetPlId = $this->data['BudgetPl']['id'];
                    mysql_query("DELETE FROM budget_pl_details WHERE budget_pl_id=" . $budgetPlId);
                    /**
                     * Budget P&L Detail
                     */
                    $this->loadModel('BudgetPlDetail');
                    for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                        if ($_POST['chart_account_id'][$i] != '') {
                            $this->BudgetPlDetail->create();
                            $BudgetPlDetail['BudgetPlDetail']['budget_pl_id'] = $budgetPlId;
                            $BudgetPlDetail['BudgetPlDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m1'] = $_POST['m1'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m2'] = $_POST['m2'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m3'] = $_POST['m3'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m4'] = $_POST['m4'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m5'] = $_POST['m5'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m6'] = $_POST['m6'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m7'] = $_POST['m7'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m8'] = $_POST['m8'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m9'] = $_POST['m9'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m10'] = $_POST['m10'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m11'] = $_POST['m11'][$i];
                            $BudgetPlDetail['BudgetPlDetail']['m12'] = $_POST['m12'][$i];
                            $this->BudgetPlDetail->save($BudgetPlDetail);
                        }
                    }
                    $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Edit', $budgetPlId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Save Edit (Error)', $budgetPlId);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Edit', $id);
            $this->data = $this->BudgetPl->read(null, $id);
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Budget Plan (P&L)', 'Delete', $id);
        $this->BudgetPl->updateAll(
                array('BudgetPl.is_active' => "2"),
                array('BudgetPl.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

}

?>