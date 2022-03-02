<?php

class LocationGroupsController extends AppController {

    var $name = 'LocationGroups';
    var $components = array('Helper', 'Inventory');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'View', $id);
        $this->set('locationGroup', $this->LocationGroup->read(null, $id));
    }
    
    function viewProductWarehouse($locationGroupId = null) {
        $this->layout = 'ajax';
        if (!$locationGroupId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'View Product Warehouse', $locationGroupId);
        $this->set(compact("locationGroupId"));
    }
    
    function viewProductWarehouseAjax($locationGroupId = null, $category = null) {
        $this->layout = 'ajax';
        if (!$locationGroupId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact("locationGroupId", "category"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'location_groups', $this->data['LocationGroup']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
                $dateNow   = date("Y-m-d H:i:s");
                $this->LocationGroup->create();
                $this->data['LocationGroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['LocationGroup']['created']    = $dateNow;
                $this->data['LocationGroup']['created_by'] = $user['User']['id'];
                $this->data['LocationGroup']['is_active']  = 1;
                if ($this->LocationGroup->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        $lastInsertId = $this->LocationGroup->id;
                        // Create Table For Store Total
                        mysql_query("CREATE TABLE `".$lastInsertId."_group_totals` (
                                            `vendor_id` INT(11) NULL DEFAULT NULL,
                                            `product_id` INT(11) NOT NULL DEFAULT '0',
                                            `color_id` INT(11) NULL DEFAULT NULL,
                                            `size_id` INT(11) NULL DEFAULT NULL,
                                            `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                            `expired_date` DATE NOT NULL,
                                            `location_id` INT(11) NOT NULL DEFAULT '0',
                                            `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                            `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0.000',
                                            `total_order` DECIMAL(15,3) NULL DEFAULT '0.000',
                                            PRIMARY KEY (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`),
                                            INDEX `index_keys` (`product_id`, `location_id`, `location_group_id`, `lots_number`, `expired_date`)
                                    )
                                    COLLATE='utf8_unicode_ci'
                                    ENGINE=InnoDB;");
                        // Create Table For Store Total Detail
                        mysql_query("CREATE TABLE `".$lastInsertId."_group_total_details` (
                                        `vendor_id` INT(11) NULL DEFAULT NULL,
                                        `product_id` INT(11) NOT NULL DEFAULT '0',
                                        `color_id` INT(11) NULL DEFAULT NULL,
                                        `size_id` INT(11) NULL DEFAULT NULL,
                                        `location_group_id` INT(11) NOT NULL DEFAULT '0',
                                        `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                        `total_inv_adj` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_si` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_si_free` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pos` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pos_free` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pb` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pbr` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_sr` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_sr_free` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_to_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_to_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_order` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `date` DATE NOT NULL,
                                        PRIMARY KEY (`product_id`, `location_group_id`, `date`),
                                        INDEX `index_key` (`product_id`, `location_group_id`, `date`)
                                )
                                COLLATE='utf8_unicode_ci'
                                ENGINE=InnoDB;");
                        // Trigger Group Total
                        $groupTotalAfInsert = $this->Inventory->createTrigger(3, $lastInsertId);
                        $groupTotalAfUpdate = $this->Inventory->createTrigger(4, $lastInsertId);
                        $groupTotalDetailAfInsert = $this->Inventory->createTrigger(1, $lastInsertId);
                        $groupTotalDetailAfUpdate = $this->Inventory->createTrigger(2, $lastInsertId);
                        $db  = ConnectionManager::getDataSource('default');
                        $mysqlLogin = " -u ".$db->config['login']." ";
                        if($db->config['password'] != ''){
                            $mysqlLogin .= ' -p'.$db->config['password']." ";
                        }
                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            // Mysql Version
                            $sql = mysql_query("SHOW VARIABLES LIKE 'version'");
                            $row = mysql_fetch_array($sql);
                            $mysqlFolder = str_replace("-log", "", $row['Value']);
                            $winPath = dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."mysql".DIRECTORY_SEPARATOR."mysql".$mysqlFolder.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR;
                            // Group Total
                            shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalAfInsert);
                            shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalAfUpdate);
                            // Group Total Detail
                            shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalDetailAfInsert);
                            shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalDetailAfUpdate);
                        } else {
                            // Group Total
                            shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalAfInsert);
                            shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalAfInsert);
                            // Group Total
                            shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalDetailAfInsert);
                            shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$groupTotalDetailAfUpdate);
                        }
                        // User Location Group
                        if(isset($this->data['LocationGroup']['user_id'])){
                            for($i=0;$i<sizeof($this->data['LocationGroup']['user_id']);$i++){
                                mysql_query("INSERT INTO user_location_groups (user_id, location_group_id) VALUES ('".$this->data['LocationGroup']['user_id'][$i]."','".$lastInsertId."')");
                            }
                        }
                        // User Location Group Class with Company
                        if(!empty($this->data['LocationGroup']['company_id']) && !empty($this->data['LocationGroup']['class_id'])){
                            for($i=0;$i<sizeof($this->data['LocationGroup']['company_id']);$i++){
                                mysql_query("INSERT INTO location_group_classese VALUES (".$this->data['LocationGroup']['company_id'][$i].", ".$lastInsertId.", ".$this->data['LocationGroup']['class_id'][$i].") ON DUPLICATE KEY UPDATE class_id='".$this->data['LocationGroup']['class_id'][$i]."';");
                                $classArray = array();
                                $sqlLocGroup = mysql_query("SELECT * FROM location_group_classese WHERE company_id = ".$this->data['LocationGroup']['company_id'][$i]);
                                while($rowLocGroup = mysql_fetch_array($sqlLocGroup)){
                                    $locationGroupId = $rowLocGroup['location_group_id'];
                                    $classArray[$this->data['LocationGroup']['company_id'][$i]][$locationGroupId] = $rowLocGroup['class_id'];
                                }
                                $fileClass = serialize($classArray);
                                mysql_query("UPDATE companies SET classes = '{$fileClass}' WHERE id = ".$this->data['LocationGroup']['company_id'][$i]);
                            }
                        }
                        // Create Location
                        $this->loadModel('Location');
                        $this->Location->create();
                        $location = array();
                        $location['Location']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $location['Location']['location_group_id'] = $lastInsertId;
                        $location['Location']['name']        = $this->data['LocationGroup']['name'];
                        $location['Location']['is_for_sale'] = 1;
                        $location['Location']['created']     = $dateNow;
                        $location['Location']['created_by']  = $user['User']['id'];
                        $location['Location']['is_active']   = 1;
                        if ($this->Location->save($location)) {
                            $locationId = $this->Location->id;
                            mysql_query("CREATE TABLE `".$locationId."_inventories` (
                                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                                        `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                        `consignment_id` INT(11) NULL DEFAULT NULL,
                                        `consignment_return_id` INT(11) NULL DEFAULT NULL,
                                        `vendor_consignment_id` INT(11) NULL DEFAULT NULL,
                                        `vendor_consignment_return_id` INT(11) NULL DEFAULT NULL,
                                        `inventory_adjustment_id` INT(11) NULL DEFAULT NULL,
                                        `inventory_adjustment_detail_id` INT(11) NULL DEFAULT NULL,
                                        `sales_invoice_id` INT(11) NULL DEFAULT NULL,
                                        `point_of_sales_id` INT(11) NULL DEFAULT NULL,
                                        `sales_return_id` INT(11) NULL DEFAULT NULL,
                                        `purchase_bill_id` INT(11) NULL DEFAULT NULL,
                                        `purchase_return_id` INT(11) NULL DEFAULT NULL,
                                        `transfer_order_id` INT(11) NULL DEFAULT NULL,
                                        `type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                                        `customer_id` INT(11) NULL DEFAULT NULL,
                                        `vendor_id` INT(11) NULL DEFAULT NULL,
                                        `product_id` INT(11) NOT NULL,
                                        `color_id` INT(11) NULL DEFAULT NULL,
                                        `size_id` INT(11) NULL DEFAULT NULL,
                                        `location_id` INT(11) NOT NULL,
                                        `location_group_id` INT(11) NOT NULL,
                                        `qty` DECIMAL(15,3) NOT NULL,
                                        `unit_cost` DECIMAL(18,9) NULL DEFAULT '0.000000000',
                                        `unit_price` DECIMAL(15,4) NULL DEFAULT '0.0000',
                                        `date` DATE NOT NULL,
                                        `lots_number` VARCHAR(50) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                        `date_expired` DATE NULL DEFAULT NULL,
                                        `created` DATETIME NOT NULL,
                                        `created_by` BIGINT(11) NOT NULL,
                                        `modified` DATETIME NOT NULL,
                                        `modified_by` BIGINT(11) NULL DEFAULT NULL,
                                        `is_active` TINYINT(4) NULL DEFAULT '1',
                                        PRIMARY KEY (`id`),
                                        INDEX `product_id` (`product_id`),
                                        INDEX `location_id` (`location_id`),
                                        INDEX `lots_number` (`lots_number`),
                                        INDEX `qty` (`qty`),
                                        INDEX `location_group_id` (`location_group_id`)
                                )
                                COLLATE='utf8_unicode_ci'
                                ENGINE=InnoDB;");
                            mysql_query("CREATE TABLE `".$locationId."_inventory_totals` (
                                        `vendor_id` INT(11) NULL DEFAULT NULL,
                                        `product_id` INT(11) NOT NULL DEFAULT '0',
                                        `color_id` INT(11) NULL DEFAULT NULL,
                                        `size_id` INT(11) NULL DEFAULT NULL,
                                        `location_id` INT(11) NOT NULL DEFAULT '0',
                                        `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                        `expired_date` DATE NOT NULL,
                                        `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                        `total_qty` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_order` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`),
                                        INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`)
                                )
                                COLLATE='utf8_unicode_ci'
                                ENGINE=InnoDB;");
                            mysql_query("CREATE TABLE `".$locationId."_inventory_total_details` (
                                        `vendor_id` INT(11) NULL DEFAULT NULL,
                                        `product_id` INT(11) NOT NULL DEFAULT '0',
                                        `color_id` INT(11) NULL DEFAULT NULL,
                                        `size_id` INT(11) NULL DEFAULT NULL,
                                        `location_id` INT(11) NOT NULL DEFAULT '0',
                                        `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                        `expired_date` DATE NOT NULL,
                                        `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                        `total_inv_adj` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_si` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pos` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pb` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_pbr` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_sr` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_to_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_to_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_cus_consign_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_cus_consign_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_ven_consign_in` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_ven_consign_out` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `total_order` DECIMAL(15,3) NULL DEFAULT '0.000',
                                        `date` DATE NOT NULL,
                                        PRIMARY KEY (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`),
                                        INDEX `index_keys` (`product_id`, `location_id`, `lots_number`, `expired_date`, `date`)
                                )
                                COLLATE='utf8_unicode_ci'
                                ENGINE=InnoDB;");
                            // Trigger Location
                            $inventoryAfInsert  = $this->Inventory->createTrigger(5, $locationId);
                            $locTotalAfInsert = $this->Inventory->createTrigger(8, $locationId);
                            $locTotalAfUpdate = $this->Inventory->createTrigger(9, $locationId);
                            $locTotalDetailAfInsert = $this->Inventory->createTrigger(6, $locationId);
                            $locTotalDetailAfUpdate = $this->Inventory->createTrigger(7, $locationId);
                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                // Mysql Version
                                $sql = mysql_query("SHOW VARIABLES LIKE 'version'");
                                $row = mysql_fetch_array($sql);
                                $mysqlFolder = str_replace("-log", "", $row['Value']);
                                $winPath = dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."mysql".DIRECTORY_SEPARATOR."mysql".$mysqlFolder.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR;
                                // Location Inventory
                                shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$inventoryAfInsert);
                                // Location Total
                                shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalAfInsert);
                                shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalAfUpdate);
                                // Location Total Detail
                                shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalDetailAfInsert);
                                shell_exec($winPath.'mysql.exe'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalDetailAfUpdate);
                            } else {
                                // Location Inventory
                                shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$inventoryAfInsert);
                                // Location Total
                                shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalAfInsert);
                                shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalAfUpdate);
                                // Location Total Detail
                                shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalDetailAfInsert);
                                shell_exec('mysql'.$mysqlLogin.$db->config['database'].' < '.WWW_ROOT.'public'.DIRECTORY_SEPARATOR.'trigger'.DIRECTORY_SEPARATOR.$locTotalDetailAfUpdate);
                            }
                        }
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Add New', $lastInsertId);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Add New (Error '.$error.')');
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Add New');
        $locationGroupTypes = ClassRegistry::init('LocationGroupType')->find("list", array("conditions" => array("LocationGroupType.is_active = 1", "LocationGroupType.id != 1")));
        $companies = ClassRegistry::init('Company')->find("all", array("conditions" => array("Company.is_active = 1", "Company.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact("companies", "locationGroupTypes"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'location_groups', $id, $this->data['LocationGroup']['name'])) {
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['LocationGroup']['modified']    = $dateNow;
                $this->data['LocationGroup']['modified_by'] = $user['User']['id'];
                if ($this->LocationGroup->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Data cloud not been delete' && $error != 'Invalid Data'){
                        // User Location Group
                        mysql_query("DELETE FROM user_location_groups WHERE location_group_id=".$id);
                        if(isset($this->data['LocationGroup']['user_id'])){
                            for($i=0;$i<sizeof($this->data['LocationGroup']['user_id']);$i++){
                                mysql_query("INSERT INTO user_location_groups (user_id, location_group_id) VALUES ('".$this->data['LocationGroup']['user_id'][$i]."','".$id."')");
                            }
                        }
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Edit', $id);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Edit (Error '.$error.')', $id);
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Edit', $id);
            $this->data = $this->LocationGroup->read(null, $id);
            $locationGroupTypes = ClassRegistry::init('LocationGroupType')->find("list", array("conditions" => array("LocationGroupType.is_active = 1", "LocationGroupType.id != 1")));
            $this->set(compact("locationGroupTypes"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $sqlCheck = mysql_query("SELECT id FROM locations WHERE location_group_id = ".$id." AND is_active = 1 LIMIT 1;");
        if(!mysql_num_rows($sqlCheck)){
            $dateNow  = date("Y-m-d H:i:s");
            Configure::write('debug', 0);
            $this->data = $this->LocationGroup->read(null, $id);
            mysql_query("UPDATE `location_groups` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            $error = mysql_error();
            if($error != 'Data cloud not been delete' && $error != 'Invalid Data'){
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Delete', $id);
                echo MESSAGE_DATA_HAS_BEEN_DELETED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Delete (Error have location)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Warehouse', 'Export to Excel');
            $filename = "public/report/location_group_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Location Group' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME;
            $query = mysql_query('SELECT id, name FROM location_groups WHERE is_active=1 ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>