<?php

class VendorsController extends AppController {

    var $name = 'Vendors';
    var $components = array('Helper', 'Address');

    function index() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Dashboard');
    }

    function ajax() {
        $this->layout = "ajax";
    }

    function add() {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'vendors', $this->data['Vendor']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vendor']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Vendor']['created']    = $dateNow;
                $this->data['Vendor']['created_by'] = $user['User']['id'];
                $this->data['Vendor']['is_active']  = 1;
                if ($this->Vendor->saveAll($this->data)) {
                    $lastInsertId = $this->Vendor->getLastInsertId();
                    // Vendor photo
                    if ($this->data['Vendor']['photo'] != '') {
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/vendor_photo/tmp/' . $this->data['Vendor']['photo']);
                        rename('public/vendor_photo/tmp/thumbnail/' . $this->data['Vendor']['photo'], 'public/vendor_photo/' . $photoName);
                        mysql_query("UPDATE vendors SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                        $this->data['Vendor']['photo'] = $photoName;
                    }
                    // Vendor Company
                    if (isset($this->data['Vendor']['company_id'])) {
                        mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['company_id'] . "')");
                    }
                    
                    // Vendor Group
                    if(!empty($this->data['Vendor']['vgroup_id'])){
                        mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['vgroup_id'] . "')");
                    }
                    // Vendor Product
                    if (!empty($this->data['Vendor']['product_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['product_id']); $i++) {
                            mysql_query("INSERT INTO product_vendors (vendor_id, product_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Vendor']['product_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Add New');
            $conditionUser = "id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $vgroups = ClassRegistry::init('Vgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $countries = ClassRegistry::init('Country')->find("list");
            $code = $this->Helper->getAutoGenerateVendorCode();
            $this->set(compact('paymentTerms', 'vgroups', "countries", "companies", "code"));
        }
    }

    function edit($id=null) {
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'vendors', $id, $this->data['Vendor']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit (Error Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vendor']['modified']    = $dateNow;
                $this->data['Vendor']['modified_by'] = $user['User']['id'];
                $this->data['Vendor']['is_active']   = 1;
                if ($this->Vendor->saveAll($this->data)) {
                    // Vendor photo
                    if ($this->data['Vendor']['new_photo'] != '') {
                        $photoName = md5($this->data['Vendor']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/vendor_photo/tmp/' . $this->data['Vendor']['new_photo']);
                        rename('public/vendor_photo/tmp/thumbnail/' . $this->data['Vendor']['new_photo'], 'public/vendor_photo/' . $photoName);
                        @unlink('public/vendor_photo/' . $this->data['Vendor']['old_photo']);
                        mysql_query("UPDATE vendors SET photo='" . $photoName . "' WHERE id=" . $this->data['Vendor']['id']);
                        $this->data['Vendor']['photo'] = $photoName;
                    }
                    // Vendor Group
                    mysql_query("DELETE FROM vendor_vgroups WHERE vendor_id=" . $id);
                    if (!empty($this->data['Vendor']['vgroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['vgroup_id']); $i++) {
                            mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $id . "','" . $this->data['Vendor']['vgroup_id'][$i] . "')");
                        }
                    }
                    
                    // Vendor Company
                    mysql_query("DELETE FROM vendor_companies WHERE vendor_id=" . $id);
                    if (isset($this->data['Vendor']['company_id'])) {
                        mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES ('" . $id . "','" . $this->data['Vendor']['company_id'] . "')");
                    }
                    // Vendor Product
                    mysql_query("DELETE FROM product_vendors WHERE vendor_id=" . $id);
                    if (!empty($this->data['Vendor']['product_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['product_id']); $i++) {
                            mysql_query("INSERT INTO product_vendors (vendor_id, product_id) VALUES ('" . $id . "', '" . $this->data['Vendor']['product_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Edit', $id);
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $this->data = $this->Vendor->read(null, $id);
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $vgroupsSellecteds = ClassRegistry::init('VendorVgroup')->find('list', array('fields' => array('id', 'vgroup_id'), 'order' => 'id', 'conditions' => array('vendor_id' => $id)));
            $vgroupsSellected = array();
            foreach ($vgroupsSellecteds as $cs) {
                array_push($vgroupsSellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $vgroups = ClassRegistry::init('Vgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $countries = ClassRegistry::init('Country')->find("list");
            $this->set(compact('paymentTerms', 'vgroupsSellected', 'vgroups', 'countries', 'companies'));
        }
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'View', $id);
        $this->data = $this->Vendor->read(null, $id);
    }
    
    function purchaseAjax($vendorId = null) {
        $this->layout = 'ajax';
        $this->set(compact('vendorId'));
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->Vendor->read(null, $id);
        mysql_query("UPDATE `vendors` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function searchVendor() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Vendor.id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].'))';
        $vendors = $this->Vendor->find('all', array(
                    'conditions' => array('OR' => array(
                            'Vendor.name LIKE' => '%'.$this->params['url']['q'].'%',
                            'Vendor.vendor_code LIKE' => '%'.$this->params['url']['q'].'%',
                        ), 'Vendor.is_active' => 1, $userPermission
                    ),
                ));
        $this->set(compact('vendors'));
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Export to Excel');
            $filename = "public/report/vendor_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Vendors' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_VENDOR_GROUP. "\t" . TABLE_CODE. "\t" . TABLE_NAME. "\t" . TABLE_TELEPHONE_WORK. "\t" . TABLE_TELEPHONE_OTHER. "\t" . TABLE_FAX. "\t" . TABLE_EMAIL;
            $conditionUser = " AND id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $query = mysql_query('SELECT id, (SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM vendor_companies WHERE vendor_id = vendors.id)), (SELECT GROUP_CONCAT(name) FROM vgroups WHERE id IN (SELECT vgroup_id FROM vendor_vgroups WHERE vendor_id = vendors.id)), vendor_code, name, work_telephone, other_number, fax_number, email_address '
                    . '           FROM vendors WHERE is_active=1'.$conditionUser.' ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[2]. "\t" . $data[3]. "\t" . $data[4]. "\t" . $data[5]. "\t" . $data[6]. "\t" . $data[7]. "\t" . $data[8];
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
            $target_folder = 'public/vendor_photo/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['pos_photo']) && $_SESSION['pos_photo'] != '') {
                @unlink($target_folder . $_SESSION['pos_photo']);
            }
            echo $_SESSION['pos_photo'] = $target_name;
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
            $target_folder = 'public/vendor_photo/tmp/';
            $target_thumbnail = 'public/vendor_photo/tmp/thumbnail/';
            $ext = explode(".", $_POST['photoName']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_folder, $target_name, $_POST['w'], $_POST['h'], 100, true);
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_thumbnail, $target_name, 300, 300, 100, true);
            @unlink($target_folder . $_POST['photoName']);
        }
        echo $target_name;
        exit();
    }
    
    function addVgroup(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Vgroup');
            $result = array();
            $comCheck = $this->data['Vgroup']['company_id'];
            if ($this->Helper->checkDouplicate('name', 'vgroups', $this->data['Vgroup']['name'], 'is_active = 1 AND id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Quick Add New (Error Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->Vgroup->create();
                $this->data['Vgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Vgroup']['created']    = $dateNow;
                $this->data['Vgroup']['created_by'] = $user['User']['id'];
                $this->data['Vgroup']['is_active']  = 1;
                if ($this->Vgroup->save($this->data)) {
                    $lastInsertId = $this->Vgroup->getLastInsertId();
                    // vgroup company
                    if (isset($this->data['Vgroup']['company_id'])) {
                        mysql_query("INSERT INTO vgroup_companies (vgroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vgroup']['company_id'] . "')");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Quick Add New', $lastInsertId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $vgroups = ClassRegistry::init('Vgroup')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($vgroups AS $vgroup){
                        $selected = '';
                        if($vgroup['Vgroup']['id'] == $lastInsertId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$vgroup['Vgroup']['id'].'" '.$selected.'>'.$vgroup['Vgroup']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor Group', 'Quick Add New');
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
            if ($this->Helper->checkDouplicate('name', 'vendors', $this->data['Vendor']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vendor']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Vendor']['created']    = $dateNow;
                $this->data['Vendor']['created_by'] = $user['User']['id'];
                $this->data['Vendor']['is_active']  = 1;
                if ($this->Vendor->saveAll($this->data)) {
                    $lastInsertId = $this->Vendor->getLastInsertId();
                    $result['error'] = 0;
                    $result['id']    = $lastInsertId;
                    $result['name']  = $this->data['Vendor']['vendor_code']." - ".$this->data['Vendor']['name'];
                    // Vendor Group
                    if(!empty($this->data['Vendor']['vgroup_id'])){
                        mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['vgroup_id'] . "')");
                    }
                    // Vendor Company
                    if (isset($this->data['Vendor']['company_id'])) {
                        mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['company_id'] . "')");
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Quick Add New', $lastInsertId);
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Quick Add New');
            $conditionUser = "id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $vgroups = ClassRegistry::init('Vgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $countries = ClassRegistry::init('Country')->find("list");
            $code = $this->Helper->getAutoGenerateVendorCode();
            $this->set(compact('paymentTerms', 'vgroups', "companies", "code", "countries"));
        }
    }
    
    function product($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }

    function productAjax($companyId = null) {
        $this->layout = 'ajax';
        $this->set(compact('companyId'));
    }
    
    function searchProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $userPermission = 'Product.company_id IN (SELECT company_id FROM user_companies WHERE user_id ='.$user['User']['id'].')';
        $products = ClassRegistry::init('Product')->find('all', array(
                    'conditions' => array('OR' => array(
                            'Product.name LIKE' => '%' . $this->params['url']['q'] . '%',
                            'Product.code LIKE ' => '%' . $this->params['url']['q'] . '%',), 'Product.is_active' => 1, $userPermission)));
        $this->set(compact('products'));
    }



    
    function indexVendor() {
        $this->layout = 'ajax';
    }

    function ajaxVendor() {
        $this->layout = 'ajax';
    }

    function viewVendor($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'View', $id);
        $this->data = $this->Vendor->read(null, $id);
    }

    function addVendor(){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'vendors', $this->data['Vendor']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vendor']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Vendor']['created']    = $dateNow;
                $this->data['Vendor']['created_by'] = $user['User']['id'];
                $this->data['Vendor']['is_active']  = 1;
                if ($this->Vendor->saveAll($this->data)) {
                    $lastInsertId = $this->Vendor->getLastInsertId();
                    // Vendor photo
                    if ($this->data['Vendor']['photo'] != '') {
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/vendor_photo/tmp/' . $this->data['Vendor']['photo']);
                        rename('public/vendor_photo/tmp/thumbnail/' . $this->data['Vendor']['photo'], 'public/vendor_photo/' . $photoName);
                        mysql_query("UPDATE vendors SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                        $this->data['Vendor']['photo'] = $photoName;
                    }
                    // Vendor Company
                    if (isset($this->data['Vendor']['company_id'])) {
                        mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['company_id'] . "')");
                    }
                    
                    // Vendor Group
                    if(!empty($this->data['Vendor']['vgroup_id'])){
                        mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Vendor']['vgroup_id'] . "')");
                    }
                    // Vendor Product
                    if (!empty($this->data['Vendor']['product_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['product_id']); $i++) {
                            mysql_query("INSERT INTO product_vendors (vendor_id, product_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Vendor']['product_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Add New');
            $conditionUser = "id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $vgroups = ClassRegistry::init('Vgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $countries = ClassRegistry::init('Country')->find("list");
            $code = $this->Helper->getAutoGenerateVendorCode();
            $this->set(compact('paymentTerms', 'vgroups', "countries", "companies", "code"));
        }
    }

    function editVendor($id=null){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'vendors', $id, $this->data['Vendor']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit (Error Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow   = date("Y-m-d H:i:s");
                $this->data['Vendor']['modified']    = $dateNow;
                $this->data['Vendor']['modified_by'] = $user['User']['id'];
                $this->data['Vendor']['is_active']   = 1;
                if ($this->Vendor->saveAll($this->data)) {
                    // Vendor photo
                    if ($this->data['Vendor']['new_photo'] != '') {
                        $photoName = md5($this->data['Vendor']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/vendor_photo/tmp/' . $this->data['Vendor']['new_photo']);
                        rename('public/vendor_photo/tmp/thumbnail/' . $this->data['Vendor']['new_photo'], 'public/vendor_photo/' . $photoName);
                        @unlink('public/vendor_photo/' . $this->data['Vendor']['old_photo']);
                        mysql_query("UPDATE vendors SET photo='" . $photoName . "' WHERE id=" . $this->data['Vendor']['id']);
                        $this->data['Vendor']['photo'] = $photoName;
                    }
                    // Vendor Group
                    mysql_query("DELETE FROM vendor_vgroups WHERE vendor_id=" . $id);
                    if (!empty($this->data['Vendor']['vgroup_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['vgroup_id']); $i++) {
                            mysql_query("INSERT INTO vendor_vgroups (vendor_id,vgroup_id) VALUES ('" . $id . "','" . $this->data['Vendor']['vgroup_id'][$i] . "')");
                        }
                    }
                    
                    // Vendor Company
                    mysql_query("DELETE FROM vendor_companies WHERE vendor_id=" . $id);
                    if (isset($this->data['Vendor']['company_id'])) {
                        mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES ('" . $id . "','" . $this->data['Vendor']['company_id'] . "')");
                    }
                    // Vendor Product
                    mysql_query("DELETE FROM product_vendors WHERE vendor_id=" . $id);
                    if (!empty($this->data['Vendor']['product_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Vendor']['product_id']); $i++) {
                            mysql_query("INSERT INTO product_vendors (vendor_id, product_id) VALUES ('" . $id . "', '" . $this->data['Vendor']['product_id'][$i] . "')");
                        }
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Edit', $id);
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT vgroup_id FROM vgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $this->data = $this->Vendor->read(null, $id);
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $vgroupsSellecteds = ClassRegistry::init('VendorVgroup')->find('list', array('fields' => array('id', 'vgroup_id'), 'order' => 'id', 'conditions' => array('vendor_id' => $id)));
            $vgroupsSellected = array();
            foreach ($vgroupsSellecteds as $cs) {
                array_push($vgroupsSellected, $cs);
            }
            $companySellecteds = ClassRegistry::init('VendorCompany')->find('list', array('fields' => array('VendorCompany.id', 'VendorCompany.company_id'), 'order' => 'VendorCompany.id', 'conditions' => array('VendorCompany.vendor_id' => $id)));
            $companySellected  = array();
            foreach ($companySellecteds as $cs) {
                array_push($companySellected, $cs);
            }
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $vgroups = ClassRegistry::init('Vgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1,$conditionUser)));
            $countries = ClassRegistry::init('Country')->find("list");
            $this->set(compact('paymentTerms', 'vgroupsSellected','companySellected', 'vgroups', 'countries', 'companies'));
        }
    }

    function deleteVendor($id=null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        mysql_query("UPDATE `vendors` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }

    function uploadVendor() {
        $this->layout = 'ajax';
        $user  = $this->getCurrentUser();
        $result = array();
        if ($_FILES['image']['name'] != '') {
            $target_folder = 'public/vendor_photo/tmp/';
            $target_thumbnail = 'public/vendor_photo/tmp/thumbnail/';
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

    function removePhotoTmpVendor() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($_POST['photo'] == '') {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            // @unlink('public/vendor_photo/tmp/'.$_POST['photo']);
            @unlink('public/vendor_photo/tmp/thumbnail/'.$_POST['photo']);
            if($_POST['module_id']>0){
                mysql_query ("UPDATE `vendors` SET `photo` = '' WHERE `id` = '".$_POST['module_id']. "';");
                @unlink('public/vendor_photo/'.$_POST['photo']);
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