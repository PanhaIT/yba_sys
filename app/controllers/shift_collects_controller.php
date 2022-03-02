<?php

class ShiftCollectsController extends AppController {

    var $name = 'ShiftCollects';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Receive Payment', 'Add New');
        $companies = ClassRegistry::init('Company')->find('all',
                    array(
                        'joins' => array(
                            array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                        ),
                        'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate'),
                        'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                    ));
        $branches = ClassRegistry::init('Branch')->find('all',
                        array(
                            'joins' => array(
                                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                            ),
                            'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.receive_collect_shift', 'Branch.currency_id', 'Currency.symbol'),
                            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                        ));
        $employees = ClassRegistry::init('employees')->find("list", array("conditions" => array("employees.is_active = 1")));
        $this->set(compact('companies', 'branches', 'employees'));
    }

    function ajax($companyId = null, $branchId = null, $userId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId', 'branchId', 'userId'));
    }

    function save() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->ShiftCollect->create();
        $this->data['ShiftCollect']['created_by']   = $user['User']['id'];
        $this->data['ShiftCollect']['is_active']    = 1;
        if ($this->ShiftCollect->save($this->data)) {
            $result['shift_id'] = $shiftId = $this->ShiftCollect->id;
            // Get Module Code
            $modCode = $this->Helper->getModuleCode($this->data['ShiftCollect']['reference'], $shiftId, 'code', 'shift_collects', 'branch_id = '.$this->data['ShiftCollect']['branch_id']);
            // Updaet Module Code
            mysql_query("UPDATE shift_collects SET code = '".$modCode."' WHERE id = ".$shiftId);
            if (isset($_POST['id'])) {
                // Update Shift Status
                for ($i = 0; $i < sizeof($_POST['id']); $i++) {
                    mysql_query("UPDATE `shifts` SET `status` = 3, `shift_collect_id` = '".$shiftId."' WHERE id = '".$_POST['id'][$i]."'");
                }
            }
            echo json_encode($result);
            exit;
        }
        exit;
    }
    
    function printShiftCollect($shiftId = null) {
        $this->layout = 'ajax';       
        if (!$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }        
        $user = $this->getCurrentUser();
        $shifts = $this->ShiftCollect->read(null, $shiftId);
        if (!empty($shifts)){    
            $company = ClassRegistry::init('Company')->find('first',
                            array(
                                'joins' => array(
                                    array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                                ),
                                'fields' => array('Company.photo'),
                                'conditions' => array('Company.is_active = 1', 'Company.id = '.$shifts['ShiftCollect']['company_id'].'', 'user_companies.user_id=' . $user['User']['id'])
                            ));
            $branch = ClassRegistry::init('Branch')->find('first',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Branch.pos_currency_id', 'Currency.symbol', 'Branch.address', 'Branch.telephone'),
                                'conditions' => array('Branch.is_active = 1', 'Branch.id = '.$shifts['ShiftCollect']['branch_id'].'', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $this->set(compact('shifts', 'branch', 'company'));            
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Print Shift (Error)');
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
}

?>