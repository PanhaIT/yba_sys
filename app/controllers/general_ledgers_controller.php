<?php

class GeneralLedgersController extends AppController {

    var $name = 'GeneralLedgers';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Dashboard');
    }

    function ajax($filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $company = null) {
        $this->layout = 'ajax';
        $this->set(compact('filterDateFrom', 'filterDateTo', 'filterStatus', 'company'));
    }

    function saveNote($glId, $note) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Save Note');
        mysql_query('UPDATE general_ledgers SET note="' . mysql_real_escape_string($note) . '" WHERE id=' . $glId) or die(mysql_error());
        exit();
    }

    function indexAll() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Dashboard Supervisor Level');
    }

    function ajaxAll($filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null, $company = null, $class = null) {
        $this->layout = 'ajax';
        $this->set(compact('filterDateFrom', 'filterDateTo', 'filterStatus', 'filterCreatedBy', 'company', 'class'));
    }

    function indexById($general_ledger_id = null) {
        $this->layout = 'ajax';
        $this->set('general_ledger_id', $general_ledger_id);
    }

    function ajaxById($general_ledger_id = null) {
        $this->layout = 'ajax';
        $this->set('general_ledger_id', $general_ledger_id);

        $user = $this->getCurrentUser();
        $this->set('userId', $user['User']['id']);
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact("companies"));
    }

    function indexByTb($chart_account_id = null, $dateAsOf = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_id', $chart_account_id);
        $this->set('dateAsOf', $dateAsOf);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('title', $_GET['title']);
    }

    function ajaxByTb($chart_account_id = null, $dateAsOf = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null, $filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_id', $chart_account_id);
        $this->set('dateAsOf', $dateAsOf);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('filterDateFrom', $filterDateFrom);
        $this->set('filterDateTo', $filterDateTo);
        $this->set('filterStatus', $filterStatus);
        $this->set('filterCreatedBy', $filterCreatedBy);

        $user = $this->getCurrentUser();
        $this->set('userId', $user['User']['id']);
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact("companies"));
    }

    function indexByTbDateRange($chart_account_id = null, $dateFrom = null, $dateTo = null, $companyId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_id', $chart_account_id);
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        $this->set('companyId', $companyId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('title', $_GET['title']);
    }

    function ajaxByTbDateRange($chart_account_id = null, $dateFrom = null, $dateTo = null, $companyId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null, $filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_id', $chart_account_id);
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        $this->set('companyId', $companyId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('filterDateFrom', $filterDateFrom);
        $this->set('filterDateTo', $filterDateTo);
        $this->set('filterStatus', $filterStatus);
        $this->set('filterCreatedBy', $filterCreatedBy);

        $user = $this->getCurrentUser();
        $this->set('userId', $user['User']['id']);
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact("companies"));
    }

    function indexByGroup($chart_account_group_id = null, $dateAsOf = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_group_id', $chart_account_group_id);
        $this->set('dateAsOf', $dateAsOf);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('title', $_GET['title']);
    }

    function ajaxByGroup($chart_account_group_id = null, $dateAsOf = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null, $filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_group_id', $chart_account_group_id);
        $this->set('dateAsOf', $dateAsOf);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('filterDateFrom', $filterDateFrom);
        $this->set('filterDateTo', $filterDateTo);
        $this->set('filterStatus', $filterStatus);
        $this->set('filterCreatedBy', $filterCreatedBy);
        $user = $this->getCurrentUser();
        $this->set('userId', $user['User']['id']);
    }

    function indexByGroupDateRange($chart_account_group_id = null, $dateFrom = null, $dateTo = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_group_id', $chart_account_group_id);
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('title', $_GET['title']);
    }

    function ajaxByGroupDateRange($chart_account_group_id = null, $dateFrom = null, $dateTo = null, $companyId = null, $branchId = null, $customerId = null, $vendorId = null, $otherId = null, $classId = null, $filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null) {
        $this->layout = 'ajax';
        $this->set('chart_account_group_id', $chart_account_group_id);
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        $this->set('companyId', $companyId);
        $this->set('branchId', $branchId);
        $this->set('customerId', $customerId);
        $this->set('vendorId', $vendorId);
        $this->set('otherId', $otherId);
        $this->set('classId', $classId);
        $this->set('filterDateFrom', $filterDateFrom);
        $this->set('filterDateTo', $filterDateTo);
        $this->set('filterStatus', $filterStatus);
        $this->set('filterCreatedBy', $filterCreatedBy);
    }

    function indexByAging($type, $typeId, $dateType, $date, $from, $to, $through, $glIdList) {
        $this->layout = 'ajax';
        $this->set('type', $type);
        $this->set('typeId', $typeId);
        $this->set('dateType', $dateType);
        $this->set('date', $date);
        $this->set('from', $from);
        $this->set('to', $to);
        $this->set('through', $through);
        $this->set('glIdList', $glIdList);
    }

    function ajaxByAging($type, $typeId, $dateType, $date, $from, $to, $through, $glIdList, $filterDateFrom = null, $filterDateTo = null, $filterStatus = null, $filterCreatedBy = null) {
        $this->layout = 'ajax';
        $this->set('type', $type);
        $this->set('typeId', $typeId);
        $this->set('dateType', $dateType);
        $this->set('date', $date);
        $this->set('from', $from);
        $this->set('to', $to);
        $this->set('through', $through);
        $this->set('glIdList', $glIdList);
        $this->set('filterDateFrom', $filterDateFrom);
        $this->set('filterDateTo', $filterDateTo);
        $this->set('filterStatus', $filterStatus);
        $this->set('filterCreatedBy', $filterCreatedBy);

        $user = $this->getCurrentUser();
        $this->set('userId', $user['User']['id']);
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $this->set(compact("companies"));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'View', $id);
        $this->data = $this->GeneralLedger->read(null, $id);
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $cus = ClassRegistry::init('Customer')->find('all', array('fields' => array('customer_code', 'name', 'id')));
        $customers = Set::combine($cus, '{n}.Customer.id', array('{0} - {1}', '{n}.Customer.customer_code', '{n}.Customer.name'));
        $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1")));
        $employees = ClassRegistry::init('Employee')->find("list", array("conditions" => array("Employee.is_active = 1")));
        $others = ClassRegistry::init('Other')->find("list", array("conditions" => array("Other.is_active = 1")));
        $this->set(compact("companies", "customers", "vendors", "employees", "others"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            $this->GeneralLedger->create();
            $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_approve'] = 0;
            $this->data['GeneralLedger']['is_active'] = 1;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->GeneralLedger->getLastInsertId();
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = 'General Journal';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Save Add New', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact("companies", "branches"));
    }

    function addAll() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            $this->GeneralLedger->create();
            $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_active'] = 1;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->GeneralLedger->getLastInsertId();
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = 'General Journal';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Save Add New', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1")));
        $cus = ClassRegistry::init('Customer')->find('all', array('fields' => array('customer_code', 'name', 'id')));
        $customers = Set::combine($cus, '{n}.Customer.id', array('{0} - {1}', '{n}.Customer.customer_code', '{n}.Customer.name'));
        $employees = ClassRegistry::init('Employee')->find("list", array("conditions" => array("Employee.is_active = 1")));
        $others = ClassRegistry::init('Other')->find("list", array("conditions" => array("Other.is_active = 1")));
        $this->set(compact("companies", "branches", "customers", "vendors", "employees", "others"));
    }

    function writeChecks() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            $this->GeneralLedger->create();
            $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_approve'] = 0;
            $this->data['GeneralLedger']['is_active'] = 1;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->GeneralLedger->getLastInsertId();
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = 'Check';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = abs($_POST['debit'][$i]);
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = abs($_POST['credit'][$i]);
                        if ($_POST['debit'][$i] < 0) {
                            $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = 0;
                            $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = abs($_POST['debit'][$i]);
                        }
                        if ($_POST['credit'][$i] < 0) {
                            $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = abs($_POST['credit'][$i]);
                            $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        }
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Write Check)', 'Save Add New', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Write Check)', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Write Check)', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact("companies", "branches"));
    }

    function makeDeposits() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            // Check Status Apply
            $sqlApply = '';
            if($this->data['GeneralLedger']['deposit_type'] == 2){
                $sqlApply = 'SELECT status FROM purchase_requests WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 3){
                $sqlApply = 'SELECT status FROM purchase_orders WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 4){
                $sqlApply = 'SELECT status FROM quotations WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 5){
                $sqlApply = 'SELECT status FROM sales_orders WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            }
            if($sqlApply != ''){
                $queryApply = mysql_query($sqlApply);
                $rowApply   = mysql_fetch_array($queryApply);
                if($rowApply[0] < 1){
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
            $this->GeneralLedger->create();
            $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_approve'] = 0;
            $this->data['GeneralLedger']['is_active'] = 1;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->GeneralLedger->getLastInsertId();
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                $totalDeposit = 0;
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type']   = 'Deposit';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo']   = $_POST['memo'][$i];
                        if($i == 0){
                            $totalDeposit = $_POST['debit'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $_POST['vendor_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][1];
                        } else {
                            $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $_POST['vendor_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        }
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                // Update Total Deposit
                mysql_query("UPDATE general_ledgers SET total_deposit = ".$totalDeposit." WHERE id = ".$generalLedgerId);
                // Update Apply Total Deposit
                if($this->data['GeneralLedger']['deposit_type'] == 2){
                    // Purchase Order
                    mysql_query("UPDATE purchase_requests SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE purchase_request_id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 3){
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 4){
                    // Quotation
                    mysql_query("UPDATE quotations SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE quotation_id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 5){
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Make Deposit)', 'Save Add New', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Make Deposit)', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Make Deposit)', 'Add New');
        $companies = ClassRegistry::init('Company')->find('list',
                        array(
                            'joins' => array(
                                array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                )
                            ),
                            'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                        )
        );
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $this->set(compact("companies", "branches"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            $this->data['GeneralLedger']['modified_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_approve'] = 0;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->data['GeneralLedger']['id'];
                $queryOldType = mysql_query("SELECT type FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId . " LIMIT 1");
                $dataOldType = mysql_fetch_array($queryOldType);
                mysql_query("DELETE FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId);
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['main_gl_id'] = $_POST['main_gl_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = $dataOldType['type'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Save Edit', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Save Edit (Error)', $generalLedgerId);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $queryExceedClosingDate = mysql_query("SELECT date FROM general_ledgers WHERE date<=(SELECT date FROM account_closing_dates ORDER BY id DESC LIMIT 1) AND id=" . $id);
            if (!mysql_num_rows($queryExceedClosingDate)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Edit', $id);
                $this->data = $this->GeneralLedger->read(null, $id);
                $companies = ClassRegistry::init('Company')->find('list',
                                array(
                                    'joins' => array(
                                        array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                        )
                                    ),
                                    'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                                )
                );
                $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
                $this->set(compact("companies", "branches"));
            } else {
                $this->set('isExceed', true);
            }
        }
    }

    function editAll($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            $this->data['GeneralLedger']['modified_by'] = $user['User']['id'];
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->data['GeneralLedger']['id'];
                $queryOldType = mysql_query("SELECT type FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId . " LIMIT 1");
                $dataOldType = mysql_fetch_array($queryOldType);
                mysql_query("DELETE FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId);
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id'] = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']  = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['main_gl_id'] = $_POST['main_gl_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type'] = $dataOldType['type'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit'] = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo'] = $_POST['memo'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id'] = $_POST['vendor_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Save Edit', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Save Edit (Error)', $generalLedgerId);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $queryExceedClosingDate = mysql_query("SELECT date FROM general_ledgers WHERE date<=(SELECT date FROM account_closing_dates ORDER BY id DESC LIMIT 1) AND id=" . $id);
            if (!mysql_num_rows($queryExceedClosingDate)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Edit', $id);
                $this->data = $this->GeneralLedger->read(null, $id);
                $companies = ClassRegistry::init('Company')->find('list',
                                array(
                                    'joins' => array(
                                        array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                        )
                                    ),
                                    'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                                ));
                $branches = ClassRegistry::init('Branch')->find('all',
                                array(
                                    'joins' => array(
                                        array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                                    ),
                                    'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                                    'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                                ));
                $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1")));
                $cus = ClassRegistry::init('Customer')->find('all', array('fields' => array('customer_code', 'name', 'id')));
                $customers = Set::combine($cus, '{n}.Customer.id', array('{0} - {1}', '{n}.Customer.customer_code', '{n}.Customer.name'));
                $employees = ClassRegistry::init('Employee')->find("list", array("conditions" => array("Employee.is_active = 1")));
                $others = ClassRegistry::init('Other')->find("list", array("conditions" => array("Other.is_active = 1")));
                $this->set(compact("companies", "branches", "customers", "vendors", "employees", "others"));
            } else {
                $this->set('isExceed', true);
            }
        }
    }

    function printJournal($id = null) {
        $this->layout = 'ajax';
        $this->set('id', $id);
    }

    function printCheck($id = null) {
        $this->layout = 'ajax';
        $this->set('id', $id);
    }

    function printDeposit($id = null) {
        $this->layout = 'ajax';
        $this->set('id', $id);
    }

    function status($id = null, $status = 0) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry (Supervisor Level)', 'Save Approve', $id);
        $this->GeneralLedger->updateAll(
                array('GeneralLedger.is_approve' => $status),
                array('GeneralLedger.id' => $id)
        );
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $queryExceedClosingDate = mysql_query("SELECT date FROM general_ledgers WHERE date<=(SELECT date FROM account_closing_dates ORDER BY id DESC LIMIT 1) AND id=" . $id);
        if (!mysql_num_rows($queryExceedClosingDate)) {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Journal Entry', 'Delete', $id);
            $gl   = $this->GeneralLedger->read(null, $id);
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => "2"),
                    array('GeneralLedger.id' => $id)
            );
            // Check Update Old Deposit Type
            if($gl['GeneralLedger']['deposit_type'] != 1){
                // Get Old Deposit Amount
                $sqlGL = mysql_query("SELECT debit FROM general_ledger_details WHERE general_ledger_id=" . $id." ORDER BY id ASC LIMIT 1");
                $rowGL = mysql_fetch_array($sqlGL);
                // Update Apply Total Deposit
                if($gl['GeneralLedger']['deposit_type'] == 2){
                    // Purchase Order
                    mysql_query("UPDATE purchase_requests SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0].") WHERE id = ".$gl['GeneralLedger']['apply_to_id']);
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE purchase_request_id = ".$gl['GeneralLedger']['apply_to_id']);
                } else if($gl['GeneralLedger']['deposit_type'] == 3){
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE id = ".$gl['GeneralLedger']['apply_to_id']);
                } else if($gl['GeneralLedger']['deposit_type'] == 4){
                    // Quotation
                    mysql_query("UPDATE quotations SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0].") WHERE id = ".$gl['GeneralLedger']['apply_to_id']);
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE quotation_id = ".$gl['GeneralLedger']['apply_to_id']);
                } else if($gl['GeneralLedger']['deposit_type'] == 5){
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE id = ".$gl['GeneralLedger']['apply_to_id']);
                }
            }
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            echo MESSAGE_EXCEED_CLOSING_DATE;
            exit();
        }
    }

    function checkCompany($chartAccountId = null, $companyId = null) {
        $this->layout = 'ajax';
        if ($companyId != '') {
            $query = mysql_query("SELECT id FROM chart_account_companies WHERE chart_account_id=" . $chartAccountId);
            if (mysql_num_rows($query)) {
                $subQuery = mysql_query("SELECT id FROM chart_account_companies WHERE chart_account_id=" . $chartAccountId . " AND company_id=" . $companyId);
                if (mysql_num_rows($subQuery)) {
                    exit();
                } else {
                    echo 'not_belong_to';
                    exit();
                }
            } else {
                exit();
            }
        } else {
            exit();
        }
    }

    function getBalance($chart_account_id, $company_id = null) {
        $query = mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gl.is_active=1 AND gld.company_id" . ($company_id ? '=' . $company_id : ' IS NULL') . " AND gld.chart_account_id=" . $chart_account_id . ")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gl.is_active=1 AND gld.company_id" . ($company_id ? '=' . $company_id : ' IS NULL') . " AND gld.chart_account_id=" . $chart_account_id . "),0)");
        $data = mysql_fetch_array($query);
        echo number_format($data[0], 2);
        exit();
    }

    function customer($companyId = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
    }

    function customerAjax($companyId = null, $group = null) {
        $this->layout = 'ajax';
        $this->set('companyId', $companyId);
        $this->set('group', $group);
    }

    function vendor($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }

    function vendorAjax($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }

    function employee($companyId = null, $orderDate = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
        $this->set('orderDate', $orderDate);
    }

    function employeeAjax($companyId = null, $orderDate = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
        $this->set('orderDate', $orderDate);
    }

    function other($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }

    function otherAjax($companyId = null) {
        $this->layout = "ajax";
        $this->set('companyId', $companyId);
    }
    
    function searchPurchaseOrder($glId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'PurchaseRequest.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')';
        $conDeposit     = 'PurchaseRequest.total_deposit';
        if(!empty($glId)){
            $conDeposit = '(IFNULL(PurchaseRequest.total_deposit,0) - IFNULL((SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = '.$glId.' AND apply_to_id = PurchaseRequest.id AND deposit_type = 2),0))';
        }
        $pruchaseOrders = ClassRegistry::init('PurchaseRequest')->find('all', array(
                        'conditions' => array('OR' => array(
                                'PurchaseRequest.pr_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ), 
                            '(PurchaseRequest.total_amount + PurchaseRequest.total_vat) > '.$conDeposit,
                            'PurchaseRequest.is_close' => 0,
                            'PurchaseRequest.status' => 1, $userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($pruchaseOrders)) {
            foreach ($pruchaseOrders as $pruchaseOrder) {
                $totalRemain = 0;
                if(!empty($glId)){
                    $sqlGl = mysql_query("SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = ".$glId." AND apply_to_id = ".$pruchaseOrder['PurchaseRequest']['id']." AND deposit_type = 2");
                    if(mysql_num_rows($sqlGl)){
                        $rowGl = mysql_fetch_array($sqlGl);
                        $totalRemain = $rowGl[0];
                    }
                }
                $sqlVendor   = mysql_query("SELECT id, vendor_code, name FROM vendors WHERE id = ".$pruchaseOrder['PurchaseRequest']['vendor_id']);
                $rowVendor   = mysql_fetch_array($sqlVendor);
                $vendorId    = $rowVendor[0];
                $vendorName  = $rowVendor[1]." - ".$rowVendor[2];
                $totalAmount = ($pruchaseOrder['PurchaseRequest']['total_amount'] + $pruchaseOrder['PurchaseRequest']['total_vat']) - ($pruchaseOrder['PurchaseRequest']['total_deposit'] - $totalRemain);
                $dateOrder   = $this->Helper->dateShort($pruchaseOrder['PurchaseRequest']['order_date']);
                echo "{$pruchaseOrder['PurchaseRequest']['id']}.*{$dateOrder}.*{$pruchaseOrder['PurchaseRequest']['pr_code']}.*{$totalAmount}.*{$pruchaseOrder['PurchaseRequest']['company_id']}.*{$vendorId}.*{$vendorName}\n";
            }
        }
        exit();
    }
    
    function searchPurchaseBill($glId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'PurchaseOrder.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')';
        $conDeposit     = 'PurchaseOrder.balance';
        if(!empty($glId)){
            $conDeposit = '(PurchaseOrder.balance + IFNULL((SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = '.$glId.' AND apply_to_id = PurchaseOrder.id AND deposit_type = 3),0))';
        }
        $pruchaseBills = ClassRegistry::init('PurchaseOrder')->find('all', array(
                        'conditions' => array('OR' => array(
                                'PurchaseOrder.po_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ),
                            'PurchaseOrder.status > 0', 
                            $conDeposit.' > 0',$userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($pruchaseBills)) {
            foreach ($pruchaseBills as $pruchaseBill) {
                $totalRemain = 0;
                if(!empty($glId)){
                    $sqlGl = mysql_query("SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = ".$glId." AND apply_to_id = ".$pruchaseBill['PurchaseOrder']['id']." AND deposit_type = 3");
                    if(mysql_num_rows($sqlGl)){
                        $rowGl = mysql_fetch_array($sqlGl);
                        $totalRemain = $rowGl[0];
                    }
                }
                $sqlVendor   = mysql_query("SELECT id, vendor_code, name FROM vendors WHERE id = ".$pruchaseBill['PurchaseOrder']['vendor_id']);
                $rowVendor   = mysql_fetch_array($sqlVendor);
                $vendorId    = $rowVendor[0];
                $vendorName  = $rowVendor[1]." - ".$rowVendor[2];
                $totalAmount = $pruchaseBill['PurchaseOrder']['balance'] + $totalRemain;
                $dateOrder   = $this->Helper->dateShort($pruchaseBill['PurchaseOrder']['order_date']);
                echo "{$pruchaseBill['PurchaseOrder']['id']}.*{$dateOrder}.*{$pruchaseBill['PurchaseOrder']['po_code']}.*{$totalAmount}.*{$pruchaseBill['PurchaseOrder']['company_id']}.*{$vendorId}.*{$vendorName}\n";
            }
        }
        exit();
    }
    
    function searchQuote($glId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Quotation.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')';
        $conDeposit     = 'Quotation.total_deposit';
        if(!empty($glId)){
            $conDeposit = '(IFNULL(Quotation.total_deposit,0) - IFNUL((SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = '.$glId.' AND apply_to_id = Quotation.id AND deposit_type = 4),0))';
        }
        $quotes = ClassRegistry::init('Quotation')->find('all', array(
                        'conditions' => array('OR' => array(
                                'Quotation.quotation_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ), 
                            '((Quotation.total_amount - IFNULL(Quotation.discount, 0)) + Quotation.total_vat)  > '.$conDeposit,
                            'Quotation.is_close' => 0,
                            'Quotation.status' => 1, $userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($quotes)) {
            foreach ($quotes as $quote) {
                $totalRemain = 0;
                if(!empty($glId)){
                    $sqlGl = mysql_query("SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = ".$glId." AND apply_to_id = ".$quote['Quotation']['id']." AND deposit_type = 4");
                    if(mysql_num_rows($sqlGl)){
                        $rowGl = mysql_fetch_array($sqlGl);
                        $totalRemain = $rowGl[0];
                    }
                }
                $sqlCus      = mysql_query("SELECT id, customer_code, name FROM customers WHERE id = ".$quote['Quotation']['customer_id']);
                $rowCus      = mysql_fetch_array($sqlCus);
                $cusId       = $rowCus[0];
                $cusName     = $rowCus[1]." - ".$rowCus[2];
                $totalAmount = (($quote['Quotation']['total_amount'] - $quote['Quotation']['discount']) + $quote['Quotation']['total_vat']) - ($quote['Quotation']['total_deposit'] - $totalRemain);
                $dateOrder   = $this->Helper->dateShort($quote['Quotation']['order_date']);
                echo "{$quote['Quotation']['id']}.*{$dateOrder}.*{$quote['Quotation']['quotation_code']}.*{$totalAmount}.*{$quote['Quotation']['company_id']}.*{$cusId}.*{$cusName}\n";
            }
        }
        exit();
    }
    
    function searchSalesInvoice($glId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'SalesOrder.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')';
        $conDeposit     = 'SalesOrder.balance';
        if(!empty($glId)){
            $conDeposit = '(SalesOrder.balance + IFNULL((SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = '.$glId.' AND apply_to_id = SalesOrder.id AND deposit_type = 5),0))';
        }
        $salesInvoices = ClassRegistry::init('SalesOrder')->find('all', array(
                        'conditions' => array('OR' => array(
                                'SalesOrder.so_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            ),
                            'SalesOrder.status > 0', 
                            $conDeposit.' > 0',$userPermission
                        ),
                        'limit' => $this->params['url']['limit']
                    ));
        if (!empty($salesInvoices)) {
            foreach ($salesInvoices as $salesInvoice) {
                $totalRemain = 0;
                if(!empty($glId)){
                    $sqlGl = mysql_query("SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = ".$glId." AND apply_to_id = ".$salesInvoice['SalesOrder']['id']." AND deposit_type = 5");
                    if(mysql_num_rows($sqlGl)){
                        $rowGl = mysql_fetch_array($sqlGl);
                        $totalRemain = $rowGl[0];
                    }
                }
                $sqlCus      = mysql_query("SELECT id, customer_code, name FROM customers WHERE id = ".$salesInvoice['SalesOrder']['customer_id']);
                $rowCus      = mysql_fetch_array($sqlCus);
                $cusId       = $rowCus[0];
                $cusName     = $rowCus[1]." - ".$rowCus[2];
                $totalAmount = $salesInvoice['SalesOrder']['balance'] + $totalRemain;
                $dateOrder   = $this->Helper->dateShort($salesInvoice['SalesOrder']['order_date']);
                echo "{$salesInvoice['SalesOrder']['id']}.*{$dateOrder}.*{$salesInvoice['SalesOrder']['so_code']}.*{$totalAmount}.*{$salesInvoice['SalesOrder']['company_id']}.*{$cusId}.*{$cusName}\n";
            }
        }
        exit();
    }
    
    function purchaseRequest($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function purchaseRequestAjax($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function purchaseBill($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function purchaseBillAjax($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function quotation($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function quotationAjax($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function salesInvoice($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function salesInvoiceAjax($companyId, $glId = null){
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'glId'));
    }
    
    function editMakeDeposit($id = null){
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if (preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $this->data['GeneralLedger']['date'])) {
                $this->data['GeneralLedger']['date'] = $this->Helper->dateConvert($this->data['GeneralLedger']['date']);
            }
            // Check Status Apply
            $sqlApply = '';
            if($this->data['GeneralLedger']['deposit_type'] == 2){
                $sqlApply = 'SELECT status FROM purchase_requests WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 3){
                $sqlApply = 'SELECT status FROM purchase_orders WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 4){
                $sqlApply = 'SELECT status FROM quotations WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            } else if($this->data['GeneralLedger']['deposit_type'] == 5){
                $sqlApply = 'SELECT status FROM sales_orders WHERE id = '.$this->data['GeneralLedger']['apply_to_id'];
            }
            if($sqlApply != ''){
                $queryApply = mysql_query($sqlApply);
                $rowApply   = mysql_fetch_array($queryApply);
                if($rowApply[0] < 1){
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
            $totalDeposit = 0;
            $totalOldDep  = 0;
            $this->GeneralLedger->create();
            $this->data['GeneralLedger']['created_by'] = $user['User']['id'];
            $this->data['GeneralLedger']['is_approve'] = 0;
            $this->data['GeneralLedger']['is_active']  = 1;
            if ($this->GeneralLedger->save($this->data)) {
                $generalLedgerId = $this->data['GeneralLedger']['id'];
                // Check Update Old Deposit Type
                if($this->data['GeneralLedger']['old_deposit_type'] != 1){
                    // Get Old Deposit Amount
                    $sqlGL = mysql_query("SELECT debit FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId." ORDER BY id ASC LIMIT 1");
                    $rowGL = mysql_fetch_array($sqlGL);
                    $totalOldDep  = $rowGL[0];
                    // Update Apply Total Deposit
                    if($this->data['GeneralLedger']['old_deposit_type'] == 2){
                        // Purchase Order
                        mysql_query("UPDATE purchase_requests SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0].") WHERE id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                        // Purchase Bill
                        mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE purchase_request_id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                    } else if($this->data['GeneralLedger']['old_deposit_type'] == 3){
                        // Purchase Bill
                        mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                    } else if($this->data['GeneralLedger']['old_deposit_type'] == 4){
                        // Quotation
                        mysql_query("UPDATE quotations SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0].") WHERE id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                        // Sales Invoice
                        mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE quotation_id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                    } else if($this->data['GeneralLedger']['old_deposit_type'] == 5){
                        // Sales Invoice
                        mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) - ".$rowGL[0]."), balance = (IFNULL(balance,0) + ".$rowGL[0].") WHERE id = ".$this->data['GeneralLedger']['old_apply_to_id']);
                    }
                }
                mysql_query("DELETE FROM general_ledger_details WHERE general_ledger_id=" . $generalLedgerId);
                /**
                 * General Ledger Detail
                 */
                $this->loadModel('GeneralLedgerDetail');
                for ($i = 0; $i < sizeof($_POST['chart_account_id']); $i++) {
                    if ($_POST['chart_account_id'][$i] != '') {
                        $GeneralLedgerDetail = array();
                        $this->GeneralLedgerDetail->create();
                        $GeneralLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $generalLedgerId;
                        $GeneralLedgerDetail['GeneralLedgerDetail']['company_id']        = $this->data['GeneralLedger']['company_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['branch_id']         = $this->data['GeneralLedger']['branch_id'];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $_POST['chart_account_id'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['type']   = 'Deposit';
                        $GeneralLedgerDetail['GeneralLedgerDetail']['debit']  = $_POST['debit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['credit'] = $_POST['credit'][$i];
                        $GeneralLedgerDetail['GeneralLedgerDetail']['memo']   = $_POST['memo'][$i];
                        if($i == 0){
                            $totalDeposit = $_POST['debit'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $_POST['vendor_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][1];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][1];
                        } else {
                            $GeneralLedgerDetail['GeneralLedgerDetail']['vendor_id']   = $_POST['vendor_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['customer_id'] = $_POST['customer_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['employee_id'] = $_POST['employee_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['other_id'] = $_POST['other_id'][$i];
                            $GeneralLedgerDetail['GeneralLedgerDetail']['class_id'] = $_POST['class_id'][$i];
                        }
                        $this->GeneralLedgerDetail->save($GeneralLedgerDetail);
                    }
                }
                // Update Total Deposit
                mysql_query("UPDATE general_ledgers SET total_deposit = ".$totalDeposit." WHERE id = ".$generalLedgerId);
                // Update Apply Total Deposit
                if($this->data['GeneralLedger']['deposit_type'] == 2){
                    // Purchase Order
                    mysql_query("UPDATE purchase_requests SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE purchase_request_id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 3){
                    // Purchase Bill
                    mysql_query("UPDATE purchase_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 4){
                    // Quotation
                    mysql_query("UPDATE quotations SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE quotation_id = ".$this->data['GeneralLedger']['apply_to_id']);
                } else if($this->data['GeneralLedger']['deposit_type'] == 5){
                    // Sales Invoice
                    mysql_query("UPDATE sales_orders SET total_deposit = (IFNULL(total_deposit,0) + ".$totalDeposit."), balance = (IFNULL(balance,0) - ".$totalDeposit.") WHERE id = ".$this->data['GeneralLedger']['apply_to_id']);
                }
                $this->Helper->saveUserActivity($user['User']['id'], 'Make Deposit', 'Save Edit', $generalLedgerId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Make Deposit', 'Save Edit (Error)', $generalLedgerId);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $queryExceedClosingDate = mysql_query("SELECT date FROM general_ledgers WHERE date<=(SELECT date FROM account_closing_dates ORDER BY id DESC LIMIT 1) AND id=" . $id);
            if (!mysql_num_rows($queryExceedClosingDate)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Make Deposit', 'Edit', $id);
                $this->data = $this->GeneralLedger->read(null, $id);
                $companies = ClassRegistry::init('Company')->find('list',
                                array(
                                    'joins' => array(
                                        array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id')
                                        )
                                    ),
                                    'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                                )
                );
                $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
                $this->set(compact("companies", "branches"));
            } else {
                $this->set('isExceed', true);
            }
        }
    }

}

?>