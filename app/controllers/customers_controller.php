<?php

class CustomersController extends AppController {

    var $name = 'Customers';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Dashborad');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'View', $id);
        $this->set('customer', $this->Customer->read(null, $id));
    }
    
    function salesAjax($customerId = null) {
        $this->layout = 'ajax';
        $this->set(compact('customerId'));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('customer_code', 'customers', $this->data['Customer']['customer_code'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->Customer->create();
            $this->data['Customer']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Customer']['created']    = $dateNow;
            $this->data['Customer']['created_by'] = $user['User']['id'];
            $this->data['Customer']['is_active']  = 1;
            if ($this->Customer->save($this->data)) {
                $lastInsertId = $this->Customer->getLastInsertId();
                // Customer photo
                if ($this->data['Customer']['photo'] != '') {
                    $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";
                    @unlink('public/customer_photo/tmp/' . $this->data['Customer']['photo']);
                    rename('public/customer_photo/tmp/thumbnail/' . $this->data['Customer']['photo'], 'public/customer_photo/' . $photoName);
                    mysql_query("UPDATE customers SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    $this->data['Customer']['photo'] = $photoName;
                }
                // Customer group
                if (!empty($this->data['Customer']['cgroup_id'])) {
                    mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['cgroup_id'] . "')");
                }
                // Customer Company
                if (isset($this->data['Customer']['company_id'])) {
                    mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['company_id'] . "')");
                }
                // Customer Shipment
                if(!empty($_POST['customer_contact_name'])){
                    for($i=0; $i < sizeof($_POST['customer_contact_name']); $i++){
                        mysql_query("INSERT INTO `shipments`(`sys_code`, `customer_id`, `name`, `created`, `created_by`, `is_active`) VALUES ('".md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id'])."', '".$lastInsertId."', '".$_POST['customer_contact_name'][$i]."', '".date("Y-m-d H:i:s")."', '".$user['User']['id']."', 1)");
                    }
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New', $lastInsertId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Add New');
        $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        $code  = $this->Helper->getAutoGenerateCustomerCode();
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $cgroups = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $provinces = ClassRegistry::init('Province')->find('all', array('conditions' => array('is_active = 1')));
        $districts = $this->Address->districtList();
        $communes = $this->Address->communeList();
        $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $streets = ClassRegistry::init('Street')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $this->set(compact('code', 'sexes', 'cgroups', 'provinces', 'districts', 'communes', 'paymentTerms', 'streets', 'companies'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('customer_code', 'customers', $id, $this->data['Customer']['customer_code'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['Customer']['modified']    = $dateNow;
            $this->data['Customer']['modified_by'] = $user['User']['id'];
            $this->data['Customer']['is_active']   = 1;
            if ($this->Customer->save($this->data)) {
                // Customer photo
                if ($this->data['Customer']['new_photo'] != '') {
                    $photoName = md5($this->data['Customer']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";
                    @unlink('public/customer_photo/tmp/' . $this->data['Customer']['new_photo']);
                    rename('public/customer_photo/tmp/thumbnail/' . $this->data['Customer']['new_photo'], 'public/customer_photo/' . $photoName);
                    @unlink('public/customer_photo/' . $this->data['Customer']['old_photo']);
                    mysql_query("UPDATE customers SET photo='" . $photoName . "' WHERE id=" . $this->data['Customer']['id']);
                    $this->data['Customer']['photo'] = $photoName;
                }
                // Customer group
                mysql_query("DELETE FROM customer_cgroups WHERE customer_id=" . $id);
                if (!empty($this->data['Customer']['cgroup_id'])) {
                    mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $id . "','" . $this->data['Customer']['cgroup_id'] . "')");
                }
                // Customer Company
                mysql_query("DELETE FROM customer_companies WHERE customer_id=" . $id);
                if (isset($this->data['Customer']['company_id'])) {
                    mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $id . "','" . $this->data['Customer']['company_id'] . "')");
                }
                // Customer Shipment
                if(!empty($_POST['customer_contact_name'])){
                    mysql_query("UPDATE `shipments` SET `is_active` = 2 WHERE customer_id = '".$id."'");
                    for($i=0; $i < sizeof($_POST['customer_contact_name']); $i++){
                        mysql_query("INSERT INTO `shipments`(`sys_code`, `customer_id`, `name`, `created`, `created_by`, `is_active`) VALUES ('".md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id'])."', '".$id."', '".$_POST['customer_contact_name'][$i]."', '".date("Y-m-d H:i:s")."', '".$user['User']['id']."', 1)");
                    }
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Edit');
        if (empty($this->data)) {
            $this->data = $this->Customer->read(null, $id);
        }
        $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        $provinces = ClassRegistry::init('Province')->find('all', array('conditions' => array('is_active != 2')));
        $districts = $this->Address->districtList();
        $communes = $this->Address->communeList();
        $cgroupsSellecteds = ClassRegistry::init('CustomerCgroup')->find('list', array('fields' => array('id', 'cgroup_id'), 'order' => 'id', 'conditions' => array('customer_id' => $id)));
        $cgroupsSellected = array();
        foreach ($cgroupsSellecteds as $cs) {
            array_push($cgroupsSellected, $cs);
        }
        $companySellecteds = ClassRegistry::init('CustomerCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('customer_id' => $id)));
        $companySellected = array();
        foreach ($companySellecteds as $cs) {
            array_push($companySellected, $cs);
        }
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $cgroups = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $streets = ClassRegistry::init('Street')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $this->set(compact('sexes', 'cgroups', 'provinces', 'districts', 'communes', 'cgroupsSellected', 'paymentTerms', 'streets', 'companies', 'companySellected'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Customer->read(null, $id);
        mysql_query("UPDATE `customers` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function getVillage() {
        $this->layout = 'ajax';
        $villages = ClassRegistry::init('Village')->find('all', array('conditions' => array('Village.commune_id' => $this->data['commune']['id'], "Village.is_active != 2"), 'fields' => array("Village.id", "Village.name", "Village.commune_id")));
        $this->set(compact('villages'));
    }

    function searchCustomer() {
        Configure::write('debug', 0);
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Customer.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $customers = $this->Customer->find('all', array(
                    'conditions' => array('OR' => array(
                            'Customer.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.name_kh LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.customer_code LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.main_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.mobile_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.other_number LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.email LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Customer.fax LIKE' => '%' . $this->params['url']['q'] . '%',
                        ), 'Customer.is_active' => 1, $userPermission
                    ),
                ));

        $this->set(compact('customers'));
    }

    function searchCustomerByCode() {

        $customers = $this->Customer->find('all', array(
                    'conditions' => array(
                        'Customer.customer_code ' => $this->data['customer_code'], 'Customer.is_active' => 1
                    ),
                ));

        $this->set('result', $customers);
        $this->render(null, 'ajax');
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Export to Excel');
            // A/R CoA List
            $arrCoAIdList = array();
            $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
            while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
                $arrCoAIdList[]=$dataCoAIdList['id'];
            }
            /**
             * table MEMORY
             * default max_heap_table_size 16MB
             */
            $date = date('Y-m-d');
            $tableName = "general_ledger_detail_cus_view" . $user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `date` date DEFAULT NULL,
                              `chart_account_id` int(11) DEFAULT NULL,
                              `company_id` int(11) DEFAULT NULL,
                              `location_id` int(11) DEFAULT NULL,
                              `debit` double DEFAULT NULL,
                              `credit` double DEFAULT NULL,
                              `customer_id` bigint(20) DEFAULT NULL,
                              `vendor_id` bigint(20) DEFAULT NULL,
                              `employee_id` bigint(20) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `chart_account_id` (`chart_account_id`),
                              KEY `company_id` (`company_id`),
                              KEY `location_id` (`location_id`),
                              KEY `date` (`date`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableName");
            $queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id
                                        FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "' AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ") AND customer_id IS NOT NULL AND customer_id != ''
                                        GROUP BY chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id");
            while ($dataCoa = mysql_fetch_array($queryCoa)) {
                mysql_query("INSERT INTO $tableName (
                                        date,
                                        chart_account_id,
                                        company_id,
                                        location_id,
                                        debit,
                                        credit,
                                        customer_id,
                                        vendor_id,
                                        employee_id
                                    ) VALUES (
                                        '" . $date . "',
                                        " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['location_id']) ? $dataCoa['location_id'] : "NULL") . ",
                                        '" . $dataCoa['SUM(debit)'] . "',
                                        '" . $dataCoa['SUM(credit)'] . "',
                                        " . (!is_null($dataCoa['customer_id']) ? $dataCoa['customer_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['vendor_id']) ? $dataCoa['vendor_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['employee_id']) ? $dataCoa['employee_id'] : "NULL") . "
                                    )");
            }
            $filename = "public/report/customer_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Customers' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_CUSTOMER_GROUP. "\t" . TABLE_CODE. "\t" . TABLE_NAME. "\t" . TABLE_NAME_IN_KHMER. "\t" . TABLE_TELEPHONE. "\t" . GENERAL_DISCOUNT ." (%)". "\t" . TABLE_PAYMENT_TERMS. "\t" . TABLE_BALANCE;
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM cgroups WHERE id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = customers.id)), customer_code, name, name_kh, main_number, discount, payment_term_id, FORMAT((SELECT SUM(debit)-SUM(credit) AS amount FROM ' . $tableName . ' WHERE chart_account_id IN (' . implode(",", $arrCoAIdList) . ') AND customer_id = customers.id),2) '
                    . '           FROM customers WHERE is_active=1 ORDER BY customer_code');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $term = '';
                if(!empty($data['payment_term_id'])){
                    $sqlTerm = mysql_query("SELECT * FROM payment_terms WHERE id = ".$data['payment_term_id']);
                    $rowTerm = mysql_fetch_array($sqlTerm);
                    $term = $rowTerm['name'];
                }
                
                $excelContent .= "\n" . $index++ . "\t" . $data[1]. "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6]. "\t" . $term. "\t" . $data[8];
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
            $target_folder = 'public/customer_photo/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['customer_photo']) && $_SESSION['customer_photo'] != '') {
                @unlink($target_folder . $_SESSION['customer_photo']);
            }
            echo $_SESSION['customer_photo'] = $target_name;
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
            $target_folder = 'public/customer_photo/tmp/';
            $target_thumbnail = 'public/customer_photo/tmp/thumbnail/';
            $ext = explode(".", $_POST['photoName']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_folder, $target_name, $_POST['w'], $_POST['h'], 100, true);
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_thumbnail, $target_name, 300, 300, 100, true);
            @unlink($target_folder . $_POST['photoName']);
        }
        echo $target_name;
        exit();
    }
    
    function vendor() {
        $this->layout = "ajax";
    }

    function vendorAjax() {
        $this->layout = "ajax";
    }
    
    function addCgroup(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Cgroup');
            $result = array();
            $comCheck = $this->data['Cgroup']['company_id'];
            if ($this->Helper->checkDouplicate('name', 'cgroups', $this->data['Cgroup']['name'], 'is_active = 1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Cgroup->create();
                $user = $this->getCurrentUser();
                $this->data['Cgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Cgroup']['created']    = $dateNow;
                $this->data['Cgroup']['created_by'] = $user['User']['id'];
                $this->data['Cgroup']['is_active']  = 1;
                if ($this->Cgroup->save($this->data)) {
                    $lastInsertId = $this->Cgroup->getLastInsertId();
                    // Cgroup company
                    if (!empty($this->data['Cgroup']['company_id'])) {
                        mysql_query("INSERT INTO cgroup_companies (cgroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Cgroup']['company_id'] . "')");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New', $lastInsertId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $cgroups = ClassRegistry::init('Cgroup')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($cgroups AS $cgroup){
                        $selected = '';
                        if($cgroup['Cgroup']['id'] == $lastInsertId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$cgroup['Cgroup']['id'].'" '.$selected.'>'.$cgroup['Cgroup']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact("companies"));
    }
    
    function addTerm(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('PaymentTerm');
            $result = array();
            if ($this->Helper->checkDouplicate('name', 'payment_terms', $this->data['PaymentTerm']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->PaymentTerm->create();
                $this->data['PaymentTerm']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['PaymentTerm']['created']    = $dateNow;
                $this->data['PaymentTerm']['created_by'] = $user['User']['id'];
                $this->data['PaymentTerm']['is_active'] = 1;
                if ($this->PaymentTerm->save($this->data)) {
                    $termId = $this->PaymentTerm->id;
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New', $termId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $terms = ClassRegistry::init('PaymentTerm')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($terms AS $term){
                        $selected = '';
                        if($term['PaymentTerm']['id'] == $termId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$term['PaymentTerm']['id'].'" '.$selected.'>'.$term['PaymentTerm']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Quick Add New');
    }
    
    function quickAdd(){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $result = array();
            if ($this->Helper->checkDouplicate('name', 'customers', $this->data['Customer']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Customer']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Customer']['type']       = 2;
                $this->data['Customer']['created']    = $dateNow;
                $this->data['Customer']['created_by'] = $user['User']['id'];
                $this->data['Customer']['is_active']  = 1;
                if ($this->Customer->saveAll($this->data)) {
                    $lastInsertId = $this->Customer->getLastInsertId();
                    $pType = "";
                    $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$lastInsertId." GROUP BY cgroup_id) GROUP BY price_type_id");
                    if(mysql_num_rows($sqlPriceType)){
                        $rowPriceType = mysql_fetch_array($sqlPriceType);
                        $pType = $rowPriceType[0];
                    }
                    $result['error'] = 0;
                    $result['id']    = $lastInsertId;
                    $result['name']  = $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name'];
                    $result['term']  = $this->data['Customer']['payment_term_id'];
                    $result['price'] = $pType;
                    // Customer group
                    if (!empty($this->data['Customer']['cgroup_id'])) {
                        mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['cgroup_id'] . "')");
                    }
                    // Customer Company
                    if (isset($this->data['Customer']['company_id'])) {
                        mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['company_id'] . "')");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Quick Add New', $lastInsertId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Quick Add New');
            $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $cgroups   = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $code = $this->Helper->getAutoGenerateCustomerCode();
            $this->set(compact('paymentTerms', 'cgroups', "companies", "code"));
        }
    }

    function indexCustomer() {
        $this->layout = 'ajax';
    }

    function ajaxCustomer() {
        $this->layout = 'ajax';
    }

    function viewCustomer($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'View', $id);
        $this->data = $this->Customer->read(null, $id);
    }

    function addCustomer() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('customer_code', 'customers', $this->data['Customer']['customer_code'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->Customer->create();
            $this->data['Customer']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Customer']['created']    = $dateNow;
            $this->data['Customer']['created_by'] = $user['User']['id'];
            $this->data['Customer']['is_active']  = 1;
            if ($this->Customer->save($this->data)) {
                $lastInsertId = $this->Customer->getLastInsertId();
                // Customer photo
                if ($this->data['Customer']['photo'] != '') {
                    $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";
                    @unlink('public/customer_photo/tmp/' . $this->data['Customer']['photo']);
                    rename('public/customer_photo/tmp/thumbnail/' . $this->data['Customer']['photo'], 'public/customer_photo/' . $photoName);
                    mysql_query("UPDATE customers SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    $this->data['Customer']['photo'] = $photoName;
                }
                // Customer group
                if (!empty($this->data['Customer']['cgroup_id'])) {
                    mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['cgroup_id'] . "')");
                }
                // Customer Company
                if (isset($this->data['Customer']['company_id'])) {
                    mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['company_id'] . "')");
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New', $lastInsertId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Add New');
        $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        $code  = $this->Helper->getAutoGenerateCustomerCode();
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $cgroups = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $provinces = ClassRegistry::init('Province')->find('all', array('conditions' => array('is_active = 1')));
        $districts = $this->Address->districtList();
        $communes = $this->Address->communeList();
        $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $streets = ClassRegistry::init('Street')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $this->set(compact('code', 'sexes', 'cgroups', 'provinces', 'districts', 'communes', 'paymentTerms', 'streets', 'companies'));
    }

    function editCustomer($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('customer_code', 'customers', $id, $this->data['Customer']['customer_code'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            }
            $dateNow  = date("Y-m-d H:i:s");
            $this->data['Customer']['modified']    = $dateNow;
            $this->data['Customer']['modified_by'] = $user['User']['id'];
            $this->data['Customer']['is_active']   = 1;
            if ($this->Customer->save($this->data)) {
                // Customer photo
                if ($this->data['Customer']['new_photo'] != '') {
                    $photoName = md5($this->data['Customer']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";
                    @unlink('public/customer_photo/tmp/' . $this->data['Customer']['new_photo']);
                    rename('public/customer_photo/tmp/thumbnail/' . $this->data['Customer']['new_photo'], 'public/customer_photo/' . $photoName);
                    @unlink('public/customer_photo/' . $this->data['Customer']['old_photo']);
                    mysql_query("UPDATE customers SET photo='" . $photoName . "' WHERE id=" . $this->data['Customer']['id']);
                    $this->data['Customer']['photo'] = $photoName;
                }
                // Customer group
                mysql_query("DELETE FROM customer_cgroups WHERE customer_id=" . $id);
                if (!empty($this->data['Customer']['cgroup_id'])) {
                    mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $id . "','" . $this->data['Customer']['cgroup_id'] . "')");
                }
                // Customer Company
                mysql_query("DELETE FROM customer_companies WHERE customer_id=" . $id);
                if (isset($this->data['Customer']['company_id'])) {
                    mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $id . "','" . $this->data['Customer']['company_id'] . "')");
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Edit');
        if (empty($this->data)) {
            $this->data = $this->Customer->read(null, $id);
        }
        $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
        $provinces = ClassRegistry::init('Province')->find('all', array('conditions' => array('is_active != 2')));
        $districts = $this->Address->districtList();
        $communes = $this->Address->communeList();
        $cgroupsSellecteds = ClassRegistry::init('CustomerCgroup')->find('list', array('fields' => array('id', 'cgroup_id'), 'order' => 'id', 'conditions' => array('customer_id' => $id)));
        $cgroupsSellected = array();
        foreach ($cgroupsSellecteds as $cs) {
            array_push($cgroupsSellected, $cs);
        }
        $companySellecteds = ClassRegistry::init('CustomerCompany')->find('list', array('fields' => array('id', 'company_id'), 'order' => 'id', 'conditions' => array('customer_id' => $id)));
        $companySellected = array();
        foreach ($companySellecteds as $cs) {
            array_push($companySellected, $cs);
        }
        $sexes = array('Male' => 'Male', 'Female' => 'Female');
        $cgroups = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $streets = ClassRegistry::init('Street')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
        $this->set(compact('sexes', 'cgroups', 'provinces', 'districts', 'communes', 'cgroupsSellected', 'paymentTerms', 'streets', 'companies', 'companySellected'));
    }

    function deleteCustomer($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `customers` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function uploadPhotoCustomer() {
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        $result = array();
        if ($_FILES['image']['name'] != '') {
            $target_folder = 'public/customer_photo/tmp/';
            $target_thumbnail = 'public/customer_photo/tmp/thumbnail/';
            $ext = explode(".", $_FILES['image']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['image']['tmp_name'], $target_thumbnail . $target_name);
            if (isset($_SESSION['image']) && $_SESSION['image'] != '') {
                @unlink($target_folder . $_SESSION['image']);
                @unlink($target_thumbnail . $_SESSION['image']);
            }
            //echo $_SESSION['vendor_photo'] = $target_name;
            $result['name']  = $target_name;
            // $result['error'] = $_FILES['image']['error'];
            // $result['size']  = $_FILES['image']['size'];
            echo json_encode($result);
            exit;
        }
    }

    function removePhotoTmpCustomer() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($_POST['photo'] == '') {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            // @unlink('public/vendor_photo/tmp/'.$_POST['photo']);
            @unlink('public/customer_photo/tmp/thumbnail/'.$_POST['photo']);
            if($_POST['module_id']>0){
                mysql_query ("UPDATE `customers` SET `photo` = '' WHERE `id` = '".$_POST['module_id']. "';");
                @unlink('public/customer_photo/'.$_POST['photo']);
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