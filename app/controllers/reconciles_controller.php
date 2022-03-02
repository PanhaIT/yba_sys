<?php

class ReconcilesController extends AppController {

    var $uses = 'User';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Reconcile', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                    array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))),
                        'fields' => array('Company.id', 'Company.name'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                    ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact('companies', 'branches'));
    }

    function ajax($date, $companyId, $branchId, $coaId, $hideFutureValue) {
        $this->layout = 'ajax';
        $this->set('date', $date);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('coaId', $coaId);
        $this->set('hideFutureValue', $hideFutureValue);
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('GeneralLedger');
        $this->loadModel('GeneralLedgerDetail');
        if (isset($_POST['general_ledger_detail_id']) && sizeof($_POST['general_ledger_detail_id']) > 0) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['Reconcile']['date'])) {
                $this->data['Reconcile']['date'] = $this->Helper->dateConvert($this->data['Reconcile']['date']);
            }
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['Reconcile']['service_charge_date'])) {
                $this->data['Reconcile']['service_charge_date'] = $this->Helper->dateConvert($this->data['Reconcile']['service_charge_date']);
            }
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['Reconcile']['interest_earn_date'])) {
                $this->data['Reconcile']['interest_earn_date'] = $this->Helper->dateConvert($this->data['Reconcile']['interest_earn_date']);
            }
            mysql_query("INSERT INTO reconciles (
                                company_id,
                                branch_id,
                                chart_account_id,
                                date,
                                created,
                                created_by,
                                is_active
                            ) VALUES (
                                '" . $this->data['Reconcile']['company_id'] . "',
                                '" . $this->data['Reconcile']['branch_id'] . "',
                                '" . $this->data['Reconcile']['chart_account_id'] . "',
                                '" . $this->data['Reconcile']['date'] . "',
                                now(),
                                '" . $user['User']['id'] . "',1)");
            $reconcileId=mysql_insert_id();
            
            for ($i = 0; $i < sizeof($_POST['general_ledger_detail_id']); $i++) {
                if (isset($_POST['is_reconcile'][$i])) {
                    mysql_query("UPDATE general_ledger_details SET is_reconcile=1,reconcile_id='" . $reconcileId . "' WHERE id=" . $_POST['general_ledger_detail_id'][$i]);
                }
            }
            if ($this->data['Reconcile']['service_charge'] != '' && $this->data['Reconcile']['service_charge'] != 0) {
                $this->GeneralLedger->create();
                $generalLedger = array();
                $generalLedger['GeneralLedger']['date'] = $this->data['Reconcile']['service_charge_date'];
                $generalLedger['GeneralLedger']['reference'] = 'Service Charge';
                $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                $generalLedger['GeneralLedger']['is_sys'] = 1;
                $generalLedger['GeneralLedger']['is_adj'] = 0;
                $generalLedger['GeneralLedger']['is_active'] = 1;
                if ($this->GeneralLedger->save($generalLedger)) {
                    // update reconciles table
                    mysql_query("UPDATE reconciles SET service_charge_gl_id='" . $this->GeneralLedger->id . "' WHERE id=" . $reconcileId);
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Check';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $this->data['Reconcile']['service_charge'];
                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['service_charge_class_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                    $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['service_charge_chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Check';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $this->data['Reconcile']['service_charge'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['service_charge_class_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                    $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                }
            }
            if ($this->data['Reconcile']['interest_earn'] != '' && $this->data['Reconcile']['interest_earn'] != 0) {
                $this->GeneralLedger->create();
                $generalLedger = array();
                $generalLedger['GeneralLedger']['date'] = $this->data['Reconcile']['interest_earn_date'];
                $generalLedger['GeneralLedger']['reference'] = 'Interest Earned';
                $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                $generalLedger['GeneralLedger']['is_sys'] = 1;
                $generalLedger['GeneralLedger']['is_adj'] = 0;
                $generalLedger['GeneralLedger']['is_active'] = 1;
                if ($this->GeneralLedger->save($generalLedger)) {
                    // update reconciles table
                    mysql_query("UPDATE reconciles SET interested_earned_gl_id='" . $this->GeneralLedger->id . "' WHERE id=" . $reconcileId);
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Deposit';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $this->data['Reconcile']['interest_earn'];
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['interest_earn_class_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                    $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    
                    $this->GeneralLedgerDetail->create();
                    $generalLedgerDetail = array();
                    $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                    $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['interest_earn_chart_account_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Deposit';
                    $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                    $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $this->data['Reconcile']['interest_earn'];
                    $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                    $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['interest_earn_class_id'];
                    $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                    $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                    $this->GeneralLedgerDetail->save($generalLedgerDetail);
                }
            }
            if ($this->data['Reconcile']['diff'] != '' && $this->data['Reconcile']['diff'] != 0) {
                $this->GeneralLedger->create();
                $generalLedger = array();
                $generalLedger['GeneralLedger']['date'] = $this->data['Reconcile']['date'];
                $generalLedger['GeneralLedger']['reference'] = 'Balance Adjustment';
                $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                $generalLedger['GeneralLedger']['is_sys'] = 1;
                $generalLedger['GeneralLedger']['is_adj'] = 0;
                $generalLedger['GeneralLedger']['is_active'] = 1;
                if ($this->GeneralLedger->save($generalLedger)) {
                    if ($this->data['Reconcile']['diff'] >= 0) {
                        // update reconciles table
                        mysql_query("UPDATE reconciles SET diff_gl_id='" . $this->GeneralLedger->id . "' WHERE id=" . $reconcileId);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Reconcile';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = $this->data['Reconcile']['diff'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['diff_class_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['diff_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Reconcile';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $this->data['Reconcile']['diff'];
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['diff_class_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    } else {
                        // update reconciles table
                        mysql_query("UPDATE reconciles SET diff_gl_id='" . $this->GeneralLedger->id . "' WHERE id=" . $reconcileId);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Reconcile';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = abs($this->data['Reconcile']['diff']);
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['diff_class_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $this->GeneralLedger->id;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $this->data['Reconcile']['diff_chart_account_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['Reconcile']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['Reconcile']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['type'] = 'Reconcile';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit'] = abs($this->data['Reconcile']['diff']);
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo'] = '';
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $this->data['Reconcile']['diff_class_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['is_reconcile'] = 1;
                        $generalLedgerDetail['GeneralLedgerDetail']['reconcile_id'] = $reconcileId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                    }
                }
            }
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Reconcile', 'Save Add New', $reconcileId);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
        } else {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Reconcile', 'Save Add New (Error)');
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
        }
        exit;
    }

}

?>