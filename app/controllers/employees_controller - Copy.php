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

    function add() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Employee']['company_id'])){
                $comCheck = implode(",", $this->data['Employee']['company_id']);
            }
            if ($this->Helper->checkDouplicate('name', 'employees', $this->data['Employee']['name'], 'is_active = 1 AND id IN (SELECT employee_id FROM employee_companies WHERE company_id IN ('.$comCheck.'))')) {
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
                $this->data['Employee']['dob']   = ((empty($this->data['Employee']['dob']))?'0000-00-00':$this->data['Employee']['dob']);
                $this->data['Employee']['created_by'] = $user['User']['id'];
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
            $districts = $this->Address->districtList();
            $communes  = $this->Address->communeList();
            $villages   = $this->Address->villageList();
            $vendors   = ClassRegistry::init('Vendor')->find('all', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('sexes', 'egroups', 'provinces', 'districts', 'communes', 'villages', 'vendors', 'positions', 'streets', 'companies'));
        }
    }

    function edit($id=null) {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $comCheck = 0;
            if(!empty($this->data['Employee']['company_id'])){
                $comCheck = implode(",", $this->data['Employee']['company_id']);
            }
            if ($this->Helper->checkDouplicateEdit('name', 'employees', $id, $this->data['Employee']['name'], 'is_active = 1 AND id IN (SELECT employee_id FROM employee_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Employee']['modified']    = $dateNow;
                $this->data['Employee']['start_working_date'] = ((empty($this->data['Employee']['start_working_date']))?'0000-00-00':$this->data['Employee']['start_working_date']);
                $this->data['Employee']['termination_date']   = ((empty($this->data['Employee']['termination_date']))?'0000-00-00':$this->data['Employee']['termination_date']);
                $this->data['Employee']['dob']   = ((empty($this->data['Employee']['dob']))?'0000-00-00':$this->data['Employee']['dob']);
                $this->data['Employee']['modified_by'] = $user['User']['id'];
                $this->data['Employee']['is_active']   = 1;
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
            $egroups = ClassRegistry::init('Egroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $egroupsSellecteds = ClassRegistry::init('EmployeeEgroup')->find('list', array('fields' => array('id', 'egroup_id'), 'order' => 'id', 'conditions' => array('employee_id' => $id)));
            $egroupsSellected = array();
            foreach ($egroupsSellecteds as $cs) {
                array_push($egroupsSellected, $cs);
            }
            $companySellecteds = ClassRegistry::init('EmployeeCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('employee_id' => $id)));
            $companySellected = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $provinces = ClassRegistry::init('Province')->find('list', array('conditions' => array('is_active != 2')));
            $districts = $this->Address->districtList();
            $communes  = $this->Address->communeList();
            $villages   = $this->Address->villageList();
            $vendors   = ClassRegistry::init('Vendor')->find('all', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $positions = ClassRegistry::init('Position')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $streets   = ClassRegistry::init('Street')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1)));
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $this->set(compact('sexes', 'egroups', 'egroupsSellected', 'provinces', 'districts', 'communes', 'villages', 'vendors', 'positions', 'streets', 'companies', 'companySellected'));
        }
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'View', $id);
        $this->set('employee', $this->Employee->read(null, $id));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Employee->read(null, $id);
        mysql_query("UPDATE `employees` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function searchEmployee() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Employee.id IN (SELECT employee_id FROM employee_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $employees = $this->Employee->find('all', array(
                    'conditions' => array('OR' => array(
                            'Employee.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Employee.employee_code LIKE' => '%' . $this->params['url']['q'] . '%'
                        ), 'Employee.is_active' => 1, 'Employee.is_show_in_sales' => 1, $userPermission
                    ),
                ));

        $this->set(compact('employees'));
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Export to Excel');
            $filename = "public/report/employees_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Employees' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_COMPANY. "\t" . TABLE_CODE. "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE_PERSONAL. "\t" . TABLE_TELEPHONE_WORK. "\t" . TABLE_EMAIL;
            if($user['User']['id'] == 1 || $user['User']['id'] == 57){
                $conditionUser = "";
            }else{
                $conditionUser = " AND id IN (SELECT employee_id FROM employee_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM employee_companies WHERE employee_id = employees.id)), employee_code, name, personal_number, personal_number, email '
                    . '           FROM employees WHERE is_active=1'.$conditionUser.' ORDER BY employee_code');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    
    function upload() {
        $this->layout = 'ajax';
        if ($_FILES['photo']['name'] != '') {
            $target_folder = 'public/employee_photo/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['employee_photo']) && $_SESSION['employee_photo'] != '') {
                @unlink($target_folder . $_SESSION['employee_photo']);
            }
            echo $_SESSION['employee_photo'] = $target_name;
            exit();
        }
    }

    function cropPhoto() {
        $this->layout = 'ajax';

        // Function
        include('includes/function.php');

        $_POST['photoFolder'] = str_replace("|||", "/", $_POST['photoFolder']);
        list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($_POST['photoFolder'] . $_POST['photoName']);
        $ImageType = ($TypeCode == 1 ? "gif" : ($TypeCode == 2 ? "jpeg" : ($TypeCode == 3 ? "png" : ($TypeCode == 6 ? "bmp" : FALSE))));
        $CreateFunction = "imagecreatefrom" . $ImageType;
        $OutputFunction = "image" . $ImageType;
        if ($ImageType) {
            $ImageSource = $CreateFunction($_POST['photoFolder'] . $_POST['photoName']);
            $ResizedImage = imagecreatetruecolor($_POST['w'], $_POST['h']);
            imagecopyresampled($ResizedImage, $ImageSource, 0, 0, $_POST['x'], $_POST['y'], $ImageWidth, $ImageHeight, $ImageWidth, $ImageHeight);
            imagejpeg($ResizedImage, $_POST['photoFolder'] . $_POST['photoName'], 100);
            // Rename
            $target_folder = 'public/employee_photo/tmp/';
            $target_thumbnail = 'public/employee_photo/tmp/thumbnail/';
            $ext = explode(".", $_POST['photoName']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_folder, $target_name, $_POST['w'], $_POST['h'], 100, true);
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_thumbnail, $target_name, 300, 300, 100, true);
            @unlink($target_folder . $_POST['photoName']);
        }
        echo $target_name;
        exit();
    }
    
    function status($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Employee->read(null, $id);
        mysql_query("UPDATE `employees` SET `is_finger_enroll`= 1, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Employee', 'Enroll Fingerprint', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function viewFingerprint(){
        $this->layout = 'ajax';
    }
    
    function viewFingerprintResult(){
        $this->layout = 'ajax';
        $result = array();
        include("includes/function.php");
        $sqlEmpFP = mysql_query("SELECT emp.employee_code, emp.name, fp.type, fp.created FROM employee_fingerprint_activities AS fp INNER JOIN employees AS emp ON emp.id = fp.employee_id WHERE 1 ORDER BY created DESC LIMIT 30");
        if(mysql_num_rows($sqlEmpFP)){
            $index = 0;
            $msg = "";
            while($rowFp = mysql_fetch_array($sqlEmpFP)){
                $msg .= '<tr>';
                $msg .= '<td class="first">'.++$index.'</td>';
                $msg .= '<td>'.$rowFp['employee_code'].'</td>';
                $msg .= '<td>'.$rowFp['name'].'</td>';
                $msg .= '<td>'.$rowFp['type'].'</td>';
                $msg .= '<td>'.dateShort($rowFp['created'], "d/m/Y H:i:s").'</td>';
                $msg .= '</tr>';
            }
            $result['body'] = $msg;
        } else {
            $msg = '<tr><td colspan="5" class="first dataTables_empty">'.TABLE_NO_RECORD.'</td></tr>';
            $result['body'] = $msg;
        }
        // Check Door
        $door = file_get_contents('');
        $result['door'] = $door; // 1: Open; 0: Close;
        echo json_encode($result);
        exit;
    }

}

?>