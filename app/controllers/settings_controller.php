<?php

class SettingsController extends AppController {

    var $uses = 'User';
    var $components = array('Helper', 'Import');

    function index() {
        $this->layout = 'ajax';
    }

    function ics($productId = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($_POST)) {
            $queryAccountType = mysql_query("SELECT id,name FROM account_types WHERE status = 1 ORDER BY ordering");
            while ($dataAccountType = mysql_fetch_array($queryAccountType)) {
                $name = "t" . $dataAccountType['id'];
                mysql_query("UPDATE account_types SET chart_account_id=" . $_POST[$name] . " WHERE id=" . $dataAccountType['id']);
            }
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'ICS', 'Save Edit');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit();
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'ICS', 'Edit');
        $this->set('productId', $productId);
    }

    function accountClosingDate(){
        $this->layout = 'ajax';
        if (!empty($this->data)) {
            $user = $this->getCurrentUser();
            mysql_query("INSERT INTO account_closing_dates (date,created,created_by) VALUES ('" . $this->data['Setting']['date'] . "', now(), " . $user['User']['id'] . ")");
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Account Closing Date', 'Save Change');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit();
        }
    }
    
    function config() {
        $this->layout = 'ajax';
    }
    
    function configSetting($id) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('id'));
    }
    
    function downloadTemplate($fileName = null){
        $this->layout = 'ajax';
        if (!$fileName) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        // Get parameters
        $file = urldecode($fileName); // Decode URL-encoded string
        $filepath = "public/template_import/" . $file;
        if(file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer
            readfile($filepath);
        }
        exit;
    }
    
    function import($type = null){
        $this->layout = 'ajax';
        if ($type == 'product' || $type == 'customer' || $type == 'vendor' || $type == 'adjustment') {
            $user = $this->getCurrentUser();
            if ($_FILES['file_import']['name'] != '') {
                $targetFolder = 'public/import/';
                $allowed  = array('xls');
                $filename = $_FILES['file_import']['name'];
                $ext      = pathinfo($filename, PATHINFO_EXTENSION);
                if( in_array( $ext, $allowed ) ) {
                    $extDot     = explode(".", $_FILES['file_import']['name']);
                    $targetName = rand() . '.' . $extDot[sizeof($extDot) - 1];
                    move_uploaded_file($_FILES['file_import']['tmp_name'], $targetFolder . $targetName);
                    $fileImport = $targetFolder.$targetName;
                    $check      = true;
                    $csv        = $this->Import->ImportCSV2Array($fileImport);
                    if(!empty($csv)){
                        $csvRequired = array();
                        if($type == 'adjustment'){
                            $colDefault    = array("Product Barcode", "Qty");
                            $match['required'] = array('Product Barcode', 'Qty');
                            // Check Field Match
                            foreach($csv[0] AS $k => $field){
                                if (!in_array($field, $colDefault)){
                                    $check = false;
                                    break;
                                } else {
                                    $csvRequired[$k] = $field;
                                }
                            }
                        } else {
                            $match = $this->Import->matchField($type."s");
                            // Check Field Match
                            foreach($csv[0] AS $k => $field){
                                $fCsv  = str_replace(" ", "_", trim($field));
                                if (!array_key_exists($fCsv, $match)){
                                    $check = false;
                                    break;
                                } else {
                                    $csvRequired[$k] = $match[$fCsv];
                                }
                            }
                            // Check Field Required
                            foreach($match['required'] AS $required){
                                if (!in_array($required, $csvRequired)){
                                    $check = false;
                                    break;
                                }
                            }
                        }
                    } else {
                        $check = false;
                    }
                    if($check == false){
                        echo MESSAGE_DATA_INVALID;
                        exit();
                    } else {
                        if($type == 'adjustment'){
                            $branches   = ClassRegistry::init('Branch')->find('all', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')), array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))), 'fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
                            $locations  = ClassRegistry::init('Location')->find('all', array('joins' => array(array('table' => 'user_locations', 'type' => 'inner', 'conditions' => array('user_locations.location_id=Location.id'))), 'conditions' => array('user_locations.user_id=' . $user['User']['id'] . ' AND Location.is_active=1'), 'order' => 'Location.name'));
                            $locationGroups = ClassRegistry::init('LocationGroup')->find('all', array('joins' => array(array('table' => 'user_location_groups', 'type' => 'inner', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'))),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1')));
                            $this->set(compact('locations', 'locationGroups', 'branches'));
                        }
                        $this->set(compact('csv', 'targetName', 'match', 'csvRequired', 'type'));
                    }
                } else {
                    echo MESSAGE_DATA_INVALID;
                    exit();
                }
            }
        } else {
            echo MESSAGE_DATA_INVALID;
            exit();
        }
    }
    
    function convertImportToDb($fileName, $type = null){
        $this->layout = 'ajax';
        if ($type == 'product' || $type == 'customer' || $type == 'vendor' || $type == 'adjustment') {
            $filepath = "public/import/" . $fileName;
            $allowed  = array('xls');
            $ext      = pathinfo($filepath, PATHINFO_EXTENSION);
            if( in_array( $ext, $allowed ) ) {
                $user = $this->getCurrentUser();
                $csv  = $this->Import->ImportCSV2Array($filepath);
                if(!empty($csv)){
                    if($type != 'adjustment'){
                        $insert = $this->Import->insertImportToDB($type."s", $csv, $user);
                    } else {
                        $branchId = $_POST['branch_id'];
                        $locationGroupId = $_POST['location_group_id'];
                        $locationId = $_POST['location_id'];
                        $adjDate  = $_POST['date'];
                        $adjustAs = $_POST['adjust'];
                        $insert = $this->Import->insertAdjustment($branchId, $locationGroupId, $locationId, $adjDate, $adjustAs, $csv, $user);
                    }
                    if($insert == true){
                        // Import Activities
                        mysql_query("INSERT INTO `import_activities` (`file`, `type`, `created`, `created_by`) VALUES ('".$fileName."', '".$type."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].");");
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit();
                    } else {
                        @unlink($filepath);
                        echo MESSAGE_DATA_INVALID;
                        exit();
                    }
                } else {
                    @unlink($filepath);
                    echo MESSAGE_DATA_INVALID;
                    exit();
                }
            } else {
                echo MESSAGE_DATA_INVALID;
                exit();
            }
        } else {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
    }
    
    function saveCheckBox($id = null, $value = null){
        $this->layout = 'ajax';
        $result = array();
        if(empty($id) || $value == null){
            $result['msg'] = 0;
            echo json_encode($result);
            exit;
        }
        mysql_query("UPDATE s_module_detail_settings SET is_checked = ".$value." WHERE id = ".$id);
        if($id == 12){ // Purchase Order
            mysql_query("UPDATE modules SET status = ".$value." WHERE module_type_id = 48");
            mysql_query("UPDATE module_types SET status = ".$value." WHERE id = 48");
        } else if($id == 14){ // Purchase Receive
            mysql_query("UPDATE modules SET status = ".$value." WHERE module_type_id = 116");
            mysql_query("UPDATE module_types SET status = ".$value." WHERE id = 116");
        } else if($id == 18){ // Sales Order
            mysql_query("UPDATE modules SET status = ".$value." WHERE module_type_id = 69");
            mysql_query("UPDATE module_types SET status = ".$value." WHERE id = 69");
        } else if($id == 20){ // Delivery Note
            mysql_query("UPDATE modules SET status = ".$value." WHERE module_type_id = 115");
            mysql_query("UPDATE module_types SET status = ".$value." WHERE id = 115");
        } else if($id == 22){ // Allow Negative Stock
            mysql_query("UPDATE location_groups SET allow_negative_stock = ".$value." WHERE 1");
        }
        $result['msg'] = 1;
        $result['val'] = $value;
        echo json_encode($result);
        exit;
    }
    
    function saveBaseCurrencySetting($id = null, $value = null){
        $this->layout = 'ajax';
        $result = array();
        if(empty($id) || $value == null){
            $result['msg'] = 0;
            echo json_encode($result);
            exit;
        }
        $checkT = mysql_query("SELECT id FROM system_activities WHERE module IN ('Purchase Bill', 'Sales Invoice', 'Sales Return', 'Bill Return', 'Inventory Adjustment', 'Point Of Sales') LIMIT 1");
        if(!mysql_num_rows($checkT)){
            mysql_query("UPDATE s_module_detail_settings SET value = ".$value." WHERE id = ".$id);
            mysql_query("UPDATE companies SET currency_id = ".$value." WHERE id = 1");
        }
        $result['msg'] = 1;
        echo json_encode($result);
        exit;
    }
    
    function saveDecimalSetting($id = null, $value = null){
        $this->layout = 'ajax';
        $result = array();
        if(empty($id) || $value == null){
            $result['msg'] = 0;
            echo json_encode($result);
            exit;
        }
        mysql_query("UPDATE s_module_detail_settings SET value = ".$value." WHERE id = ".$id);
        $result['msg'] = 1;
        echo json_encode($result);
        exit;
    }
    
    function saveLockTransaction($id, $date = null){
        $this->layout = 'ajax';
        if(!empty($date)){
            $user = $this->getCurrentUser();
            mysql_query("UPDATE s_module_detail_settings SET date_value = '".$date."' WHERE id = ".$id);
            mysql_query("UPDATE `account_closing_dates` SET `date`='".$date."', `created`='".date("Y-m-d H:i:s")."', `created_by` = ".$user['User']['id']." WHERE  `id`=1;");
            $result['msg'] = 1;
            echo json_encode($result);
            exit;
        } else {
            $result['msg'] = 0;
            echo json_encode($result);
            exit;
        }
    }

}

?>