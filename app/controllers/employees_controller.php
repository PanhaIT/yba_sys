<?php

class EmployeesController extends AppController {

    var $name = 'Employees';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Dashboard');
    }

    function ajax() {
        $this->layout = "ajax";
    }

    function view($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'View', $id);
        $this->data = $this->Employee->read(null, $id);
    }

    function add(){
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        if (!empty($this->data)) {
            // debug($this->data);exit;
            if ($this->Helper->checkDouplicate('employee_code', 'employees', $this->data['Employee']['employee_code'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Employee->create();
                $this->data['Employee']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Employee']['created']    = $dateNow;
                $this->data['Employee']['start_working_date'] = ((empty($this->data['Employee']['start_working_date']))?'0000-00-00':$this->data['Employee']['start_working_date']);
                $this->data['Employee']['termination_date']   = ((empty($this->data['Employee']['termination_date']))?'0000-00-00':$this->data['Employee']['termination_date']);
                $this->data['Employee']['dob']        = ((empty($this->data['Employee']['dob']))?'0000-00-00':$this->data['Employee']['dob']);
                $this->data['Employee']['created_by'] = $user['User']['id'];
                $this->data['Employee']['work_for_vendor_id'] = $this->data['Employee']['work_for_vendor_id'];
                $this->data['Employee']['employee_type_id'] = $this->data['Employee']['employee_type_id'];
                $this->data['Employee']['name']             = $this->data['Employee']['name'];
                $this->data['Employee']['name_kh']          = $this->data['Employee']['name_kh'];
                $this->data['Employee']['employee_code']    = $this->data['Employee']['employee_code'];
                $this->data['Employee']['personal_number']  = $this->data['Employee']['personal_number'];
                $this->data['Employee']['other_number']     = $this->data['Employee']['other_number'];
                $this->data['Employee']['sex']     = $this->data['Employee']['sex'];
                $this->data['Employee']['email']   = $this->data['Employee']['email'];
                $this->data['Employee']['salary']  = $this->data['Employee']['salary'];
                $this->data['Employee']['note']    = $this->data['Employee']['note'];
                $this->data['Employee']['is_active']  = 1;
                if ($this->Employee->saveAll($this->data)) {
                    $lastInsertId = $this->Employee->getLastInsertId();
                    // Employee photo
                    if ($this->data['Employee']['photo'] != '') {
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";
                        @unlink('public/employee_photo/tmp/' . $this->data['Employee']['photo']);
                        rename('public/employee_photo/tmp/thumbnail/' . $this->data['Employee']['photo'], 'public/employee_photo/' . $photoName);
                        mysql_query("UPDATE employees SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                        $this->data['Employee']['photo'] = $photoName;
                    }
                    // Employee group
                    if (!empty($this->data['Employee']['egroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['egroup_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Employee']['egroup_id'][$i] . "')");
                        }
                    }
                    // Employee Company
                    if (isset($this->data['Employee']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['company_id']); $i++) {
                            mysql_query("INSERT INTO employee_companies (employee_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Employee']['company_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Add New');
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $code = $this->Helper->getAutoGenerateEmployeeCode();
            $this->set('code',$code);
            $sexes   = array('Male' => 'Male', 'Female' => 'Female');
            $egroups = ClassRegistry::init('Egroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $provinces = ClassRegistry::init('Province')->find('list', array('conditions' => array('is_active != 2')));
            $workForVendors   = ClassRegistry::init('Vendor')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'Position.id', 'conditions' => array('Position.is_active' => 1)));
            $employeeTypes = ClassRegistry::init('EmployeeType')->find('list', array('order' => 'EmployeeType.id', 'conditions' => array('EmployeeType.is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('sexes','employeeTypes', 'egroups', 'provinces', 'districts', 'communes', 'villages', 'workForVendors', 'positions', 'streets', 'companies'));
        }
    }

    function edit($id=null){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            // debug($this->data);
            // exit;
            $comCheck = 0;
            if(!empty($this->data['Employee']['company_id'])){
                $comCheck = implode(",", $this->data['Employee']['company_id']);
            }
            // if ($this->Helper->checkDouplicateEdit('employee_code', 'employees', $id, $this->data['Employee']['employee_code'], 'is_active = 1 AND id IN (SELECT employee_id FROM employee_companies WHERE company_id IN ('.$comCheck.'))')) {
            //     $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit (Name ready existed)', $id);
            //     echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
            //     exit;
            // } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Employee']['modified']  = $dateNow;
                $this->data['Employee']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Employee']['created']   = $dateNow;
                $this->data['Employee']['start_working_date'] = ((empty($this->data['Employee']['start_working_date']))?'0000-00-00':$this->data['Employee']['start_working_date']);
                $this->data['Employee']['termination_date']   = ((empty($this->data['Employee']['termination_date']))?'0000-00-00':$this->data['Employee']['termination_date']);
                $this->data['Employee']['dob']                = ((empty($this->data['Employee']['dob']))?'0000-00-00':$this->data['Employee']['dob']);
                $this->data['Employee']['created_by']         = $user['User']['id'];
                $this->data['Employee']['work_for_vendor_id'] = $this->data['Employee']['work_for_vendor_id'];
                $this->data['Employee']['employee_type_id']   = $this->data['Employee']['employee_type_id'];
                $this->data['Employee']['name']               = $this->data['Employee']['name'];
                $this->data['Employee']['name_kh']            = $this->data['Employee']['name_kh'];
                $this->data['Employee']['employee_code']      = $this->data['Employee']['employee_code'];
                $this->data['Employee']['personal_number']    = $this->data['Employee']['personal_number'];
                $this->data['Employee']['other_number']       = $this->data['Employee']['other_number'];
                $this->data['Employee']['sex']       = $this->data['Employee']['sex'];
                $this->data['Employee']['email']     = $this->data['Employee']['email'];
                $this->data['Employee']['salary']    = $this->data['Employee']['salary'];
                $this->data['Employee']['note']      = $this->data['Employee']['note'];
                $this->data['Employee']['is_active'] = 1;
                if ($this->Employee->saveAll($this->data)) {
                    // Employee photo
                    if ($this->data['Employee']['new_photo'] != '') {
                        $photoName = md5($this->data['Employee']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";
                        @unlink('public/employee_photo/tmp/' . $this->data['Employee']['new_photo']);
                        rename('public/employee_photo/tmp/thumbnail/' . $this->data['Employee']['new_photo'], 'public/employee_photo/' . $photoName);
                        @unlink('public/employee_photo/' . $this->data['Employee']['old_photo']);
                        mysql_query("UPDATE employees SET photo='" . $photoName . "' WHERE id=" . $this->data['Employee']['id']);
                        $this->data['Employee']['photo'] = $photoName;
                    }
                    // Employee group
                    mysql_query("DELETE FROM employee_egroups WHERE employee_id=" . $id);
                    if (!empty($this->data['Employee']['egroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['egroup_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $id . "','" . $this->data['Employee']['egroup_id'][$i] . "')");
                        }
                    }
                    // Employee group
                    mysql_query("DELETE FROM employee_companies WHERE employee_id=" . $id);
                    if (!empty($this->data['Employee']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['company_id']); $i++) {
                            mysql_query("INSERT INTO employee_companies (employee_id,company_id) VALUES ('" . $id . "','" . $this->data['Employee']['company_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            // }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Edit', $id);
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $this->data = $this->Employee->read(null, $id);
            $sexes   = array('Male' => 'Male', 'Female' => 'Female');
            $egroups = ClassRegistry::init('Egroup')->find('list', array('order' => 'Egroup.id', 'conditions' => array('Egroup.is_active' => 1,$conditionUser)));
            $egroupsSellecteds = ClassRegistry::init('EmployeeEgroup')->find('list', array('fields' => array('EmployeeEgroup.id', 'EmployeeEgroup.egroup_id'), 'order' => 'EmployeeEgroup.id', 'conditions' => array('EmployeeEgroup.employee_id' => $id)));
            $egroupsSellected  = array();
            foreach ($egroupsSellecteds as $cs) {
                array_push($egroupsSellected, $cs);
            }
            $companySellecteds = ClassRegistry::init('EmployeeCompany')->find('list', array('fields' => array('EmployeeCompany.id', 'EmployeeCompany.company_id'), 'order' => 'EmployeeCompany.id', 'conditions' => array('EmployeeCompany.employee_id' => $id)));
            $companySellected  = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            // $provinces = ClassRegistry::init('Province')->find('list', array('conditions' => array('is_active != 2')));
            // $districts = $this->Address->districtList();
            // $communes  = $this->Address->communeList();
            // $villages  = $this->Address->villageList();
            $workForVendors   = ClassRegistry::init('Vendor')->find('list', array('order' => 'Vendor.id', 'conditions' => array('Vendor.is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'Position.id', 'conditions' => array('Position.is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'Street.id', 'conditions' => array('Street.is_active' => 1)));
            $employeeTypes = ClassRegistry::init('EmployeeType')->find('list', array('order' => 'EmployeeType.id', 'conditions' => array('EmployeeType.is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'Company.id', 'conditions' => array('Company.is_active' => 1, 'Company.id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('sexes', 'egroups','employeeTypes', 'egroupsSellected', 'provinces', 'districts', 'communes', 'villages', 'workForVendors', 'positions', 'streets', 'companies', 'companySellected'));
        }
    }

    function delete($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `employees` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function upload() {
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        $result = array();
        if ($_FILES['image']['name'] != '') {
            $target_folder = 'public/employee_photo/tmp/';
            $target_thumbnail = 'public/employee_photo/tmp/thumbnail/';
            $ext = explode(".", $_FILES['image']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['image']['tmp_name'], $target_thumbnail . $target_name);
            if (isset($_SESSION['image']) && $_SESSION['image'] != '') {
                @unlink($target_folder . $_SESSION['image']);
                @unlink($target_thumbnail . $_SESSION['image']);
            }
            //echo $_SESSION['employee_photo'] = $target_name;
            $result['name']  = $target_name;
            // $result['error'] = $_FILES['image']['error'];
            // $result['size']  = $_FILES['image']['size'];
            echo json_encode($result);
            exit;
        }
    }

    function removePhotoTmp() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($_POST['photo'] == '') {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            // @unlink('public/employee_photo/tmp/'.$_POST['photo']);
            @unlink('public/employee_photo/tmp/thumbnail/'.$_POST['photo']);
            if($_POST['employee_id']>0){
                mysql_query ("UPDATE `employees` SET `photo` = '' WHERE `id` = '".$_POST['employee_id']. "';");
                @unlink('public/employee_photo/'.$_POST['photo']);
            }
            echo PHOTO_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_DATA_INVALID;
            exit;
        }
    }

    function indexEmployee() {
        $this->layout = 'ajax';
    }

    function ajaxEmployee() {
        $this->layout = 'ajax';
    }

    function viewEmployee($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'View', $id);
        $this->data = $this->Employee->read(null, $id);
    }

    function addEmployee(){
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        if (!empty($this->data)) {
            // debug($this->data);exit;
            if ($this->Helper->checkDouplicate('employee_code', 'employees', $this->data['Employee']['employee_code'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->Employee->create();
                $this->data['Employee']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Employee']['created']    = $dateNow;
                // $this->data['Employee']['start_working_date'] = ((empty($this->data['Employee']['start_working_date']))?'0000-00-00':$this->data['Employee']['start_working_date']);
                // $this->data['Employee']['termination_date']   = ((empty($this->data['Employee']['termination_date']))?'0000-00-00':$this->data['Employee']['termination_date']);
                $this->data['Employee']['dob']        = ($this->data['Employee']['dob']!=""?$this->data['Employee']['dob']:NULL);
                $this->data['Employee']['created_by'] = $user['User']['id'];
                $this->data['Employee']['work_for_vendor_id'] = $this->data['Employee']['work_for_vendor_id'];
                $this->data['Employee']['employee_type_id'] = $this->data['Employee']['employee_type_id'];
                $this->data['Employee']['name']             = $this->data['Employee']['name'];
                $this->data['Employee']['name_kh']          = $this->data['Employee']['name_kh'];
                $this->data['Employee']['employee_code']    = $this->data['Employee']['employee_code'];
                $this->data['Employee']['personal_number']  = $this->data['Employee']['personal_number'];
                $this->data['Employee']['other_number']     = $this->data['Employee']['other_number'];

                $this->data['Employee']['street']    = $this->data['Employee']['street'];
                $this->data['Employee']['village']   = $this->data['Employee']['village'];
                $this->data['Employee']['commune']   = $this->data['Employee']['commune'];
                $this->data['Employee']['district']  = $this->data['Employee']['district'];
                $this->data['Employee']['province']  = $this->data['Employee']['province'];

                $this->data['Employee']['passports']     = $this->data['Employee']['passports'];
                $this->data['Employee']['identity_card'] = $this->data['Employee']['identity_card'];
                $this->data['Employee']['sex']     = $this->data['Employee']['sex'];
                $this->data['Employee']['email']   = $this->data['Employee']['email'];
                $this->data['Employee']['salary']  = $this->data['Employee']['salary'];
                $this->data['Employee']['note']    = $this->data['Employee']['note'];
                $this->data['Employee']['is_active']  = 1;
                if ($this->Employee->saveAll($this->data)) {
                    $lastInsertId = $this->Employee->getLastInsertId();
                    // Employee photo
                    if ($this->data['Employee']['photo'] != '') {
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";
                        @unlink('public/employee_photo/tmp/' . $this->data['Employee']['photo']);
                        rename('public/employee_photo/tmp/thumbnail/' . $this->data['Employee']['photo'], 'public/employee_photo/' . $photoName);
                        mysql_query("UPDATE employees SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                        $this->data['Employee']['photo'] = $photoName;
                    }
                    // Employee group
                    if (!empty($this->data['Employee']['egroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['egroup_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Employee']['egroup_id'][$i] . "')");
                        }
                    }
                    // Employee Company
                    if (isset($this->data['Employee']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['company_id']); $i++) {
                            mysql_query("INSERT INTO employee_companies (employee_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Employee']['company_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Add New');
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $code = $this->Helper->getAutoGenerateEmployeeCode();
            $sexes   = array('Male' => 'Male', 'Female' => 'Female');
            $egroups = ClassRegistry::init('Egroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $provinces = ClassRegistry::init('Province')->find('list', array('conditions' => array('is_active != 2')));
            $workForVendors   = ClassRegistry::init('Vendor')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'Position.id', 'conditions' => array('Position.is_active' => 1)));
            $employeeTypes = ClassRegistry::init('EmployeeType')->find('list', array('order' => 'EmployeeType.id', 'conditions' => array('EmployeeType.is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('code','sexes','employeeTypes', 'egroups', 'provinces', 'districts', 'communes', 'villages', 'workForVendors', 'positions', 'streets', 'companies'));
        }
    }

    function editEmployee($id=null){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            // debug($this->data);
            // exit;
            $comCheck = 0;
            if(!empty($this->data['Employee']['company_id'])){
                $comCheck = implode(",", $this->data['Employee']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('employee_code', 'employees', $id, $this->data['Employee']['employee_code'], 'is_active = 1 AND id IN (SELECT employee_id FROM employee_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Employee']['modified']  = $dateNow;
                $this->data['Employee']['sys_code']  = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Employee']['created']   = $dateNow;
                // $this->data['Employee']['start_working_date'] = ((empty($this->data['Employee']['start_working_date']))?'0000-00-00':$this->data['Employee']['start_working_date']);
                // $this->data['Employee']['termination_date']   = ((empty($this->data['Employee']['termination_date']))?'0000-00-00':$this->data['Employee']['termination_date']);
                $this->data['Employee']['dob']                = ($this->data['Employee']['dob']!=""?$this->data['Employee']['dob']:NULL);
                $this->data['Employee']['created_by']         = $user['User']['id'];
                $this->data['Employee']['work_for_vendor_id'] = $this->data['Employee']['work_for_vendor_id'];
                $this->data['Employee']['employee_type_id']   = $this->data['Employee']['employee_type_id'];
                $this->data['Employee']['name']               = $this->data['Employee']['name'];
                $this->data['Employee']['name_kh']            = $this->data['Employee']['name_kh'];
                $this->data['Employee']['employee_code']      = $this->data['Employee']['employee_code'];
                $this->data['Employee']['personal_number']    = $this->data['Employee']['personal_number'];
                $this->data['Employee']['other_number']       = $this->data['Employee']['other_number'];
                $this->data['Employee']['sex']       = $this->data['Employee']['sex'];
                $this->data['Employee']['email']     = $this->data['Employee']['email'];
                $this->data['Employee']['salary']    = $this->data['Employee']['salary'];
                $this->data['Employee']['note']      = $this->data['Employee']['note'];
                
                $this->data['Employee']['street']    = $this->data['Employee']['street'];
                $this->data['Employee']['village']   = $this->data['Employee']['village'];
                $this->data['Employee']['commune']   = $this->data['Employee']['commune'];
                $this->data['Employee']['district']  = $this->data['Employee']['district'];
                $this->data['Employee']['province']  = $this->data['Employee']['province'];

                $this->data['Employee']['passports']     = $this->data['Employee']['passports'];
                $this->data['Employee']['identity_card'] = $this->data['Employee']['identity_card'];
                $this->data['Employee']['is_active'] = 1;
                if ($this->Employee->saveAll($this->data)) {
                    // Employee photo
                    if ($this->data['Employee']['new_photo'] != '') {
                        $photoName = md5($this->data['Employee']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";
                        @unlink('public/employee_photo/tmp/' . $this->data['Employee']['new_photo']);
                        rename('public/employee_photo/tmp/thumbnail/' . $this->data['Employee']['new_photo'], 'public/employee_photo/' . $photoName);
                        @unlink('public/employee_photo/' . $this->data['Employee']['old_photo']);
                        mysql_query("UPDATE employees SET photo='" . $photoName . "' WHERE id=" . $this->data['Employee']['id']);
                        $this->data['Employee']['photo'] = $photoName;
                    }
                    // Employee group
                    mysql_query("DELETE FROM employee_egroups WHERE employee_id=" . $id);
                    if (!empty($this->data['Employee']['egroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['egroup_id']); $i++) {
                            mysql_query("INSERT INTO employee_egroups (employee_id,egroup_id) VALUES ('" . $id . "','" . $this->data['Employee']['egroup_id'][$i] . "')");
                        }
                    }
                    // Employee group
                    mysql_query("DELETE FROM employee_companies WHERE employee_id=" . $id);
                    if (!empty($this->data['Employee']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Employee']['company_id']); $i++) {
                            mysql_query("INSERT INTO employee_companies (employee_id,company_id) VALUES ('" . $id . "','" . $this->data['Employee']['company_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Edit', $id);
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $this->data = $this->Employee->read(null, $id);
            $sexes   = array('Male' => 'Male', 'Female' => 'Female');
            $egroups = ClassRegistry::init('Egroup')->find('list', array('order' => 'Egroup.id', 'conditions' => array('Egroup.is_active' => 1,$conditionUser)));
            $egroupsSellecteds = ClassRegistry::init('EmployeeEgroup')->find('list', array('fields' => array('EmployeeEgroup.id', 'EmployeeEgroup.egroup_id'), 'order' => 'EmployeeEgroup.id', 'conditions' => array('EmployeeEgroup.employee_id' => $id)));
            $egroupsSellected  = array();
            foreach ($egroupsSellecteds as $cs) {
                array_push($egroupsSellected, $cs);
            }
            $companySellecteds = ClassRegistry::init('EmployeeCompany')->find('list', array('fields' => array('EmployeeCompany.id', 'EmployeeCompany.company_id'), 'order' => 'EmployeeCompany.id', 'conditions' => array('EmployeeCompany.employee_id' => $id)));
            $companySellected  = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            // $provinces = ClassRegistry::init('Province')->find('list', array('conditions' => array('is_active != 2')));
            // $districts = $this->Address->districtList();
            // $communes  = $this->Address->communeList();
            // $villages  = $this->Address->villageList();
            $workForVendors   = ClassRegistry::init('Vendor')->find('list', array('order' => 'Vendor.id', 'conditions' => array('Vendor.is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'Position.id', 'conditions' => array('Position.is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'Street.id', 'conditions' => array('Street.is_active' => 1)));
            $employeeTypes = ClassRegistry::init('EmployeeType')->find('list', array('order' => 'EmployeeType.id', 'conditions' => array('EmployeeType.is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'Company.id', 'conditions' => array('Company.is_active' => 1, 'Company.id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('sexes', 'egroups','employeeTypes', 'egroupsSellected', 'provinces', 'districts', 'communes', 'villages', 'workForVendors', 'positions', 'streets', 'companies', 'companySellected'));
        }
    }

    function deleteEmployee($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `employees` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function uploadEmployee() {
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        $result = array();
        if ($_FILES['image']['name'] != '') {
            $target_folder = 'public/employee_photo/tmp/';
            $target_thumbnail = 'public/employee_photo/tmp/thumbnail/';
            $ext = explode(".", $_FILES['image']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['image']['tmp_name'], $target_thumbnail . $target_name);
            if (isset($_SESSION['image']) && $_SESSION['image'] != '') {
                @unlink($target_folder . $_SESSION['image']);
                @unlink($target_thumbnail . $_SESSION['image']);
            }
            //echo $_SESSION['employee_photo'] = $target_name;
            $result['name']  = $target_name;
            // $result['error'] = $_FILES['image']['error'];
            // $result['size']  = $_FILES['image']['size'];
            echo json_encode($result);
            exit;
        }
    }

    function removePhotoTmpEmployee() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($_POST['photo'] == '') {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            // @unlink('public/employee_photo/tmp/'.$_POST['photo']);
            @unlink('public/employee_photo/tmp/thumbnail/'.$_POST['photo']);
            if($_POST['module_id']>0){
                mysql_query ("UPDATE `employees` SET `photo` = '' WHERE `id` = '".$_POST['module_id']. "';");
                @unlink('public/employee_photo/'.$_POST['photo']);
            }
            echo PHOTO_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_DATA_INVALID;
            exit;
        }
    }

}

?>