<?php

class LocationsController extends AppController {

    var $name = 'Locations';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'View', $id);
        $this->set('location', $this->Location->read(null, $id));
    }
    
    function viewProductLocation($locationId = null) {
        $this->layout = 'ajax';
        if (!$locationId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'View Product Location', $locationId);
        $this->set(compact("locationId"));
    }
    
    function viewProductLocationAjax($locationId = null, $category = null) {
        $this->layout = 'ajax';
        if (!$locationId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact("locationId", "category"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            for ($i = 0; $i < sizeof($_POST['name']); $i++) {
                if ($this->Helper->checkDouplicate('name', 'locations', $_POST['name'][$i])) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Add New (Name ready existed)');
                    echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                    exit;
                }
            }
            for ($i = 0; $i < sizeof($_POST['name']); $i++) {
                $dateNow  = date("Y-m-d H:i:s");
                $location = array();
                $this->Location->create();
                $location['Location']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $location['Location']['location_group_id'] = $this->data['Location']['location_group_id'];
                $location['Location']['name']        = $_POST['name'][$i];
                $location['Location']['level']       = $_POST['level'][$i];
                $location['Location']['aisle']       = $_POST['aisle'][$i];
                $location['Location']['bay']         = $_POST['bay'][$i];
                $location['Location']['bin']         = $_POST['bin'][$i];
                $location['Location']['position']    = $_POST['direction'][$i];
                $location['Location']['color']       = $_POST['color'][$i];
                $location['Location']['is_for_sale'] = $_POST['is_for_sale'][$i];
                $location['Location']['created']    = $dateNow;
                $location['Location']['created_by'] = $user['User']['id'];
                $location['Location']['is_active']  = 1;
                if ($this->Location->save($location)) {
                    $lastInsertId = $this->Location->getLastInsertId();
                    mysql_query("CREATE TABLE `".$lastInsertId."_inventories` (
                                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                                    `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                    `consignment_id` INT(11) NULL DEFAULT NULL,
                                    `consignment_return_id` INT(11) NULL DEFAULT NULL,
                                    `vendor_consignment_id` INT(11) NULL DEFAULT NULL,
                                    `vendor_consignment_return_id` INT(11) NULL DEFAULT NULL,
                                    `cycle_product_id` INT(11) NULL DEFAULT NULL,
                                    `cycle_product_detail_id` INT(11) NULL DEFAULT NULL,
                                    `sales_order_id` INT(11) NULL DEFAULT NULL,
                                    `point_of_sales_id` INT(11) NULL DEFAULT NULL,
                                    `credit_memo_id` INT(11) NULL DEFAULT NULL,
                                    `purchase_order_id` INT(11) NULL DEFAULT NULL,
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
                    mysql_query("CREATE TABLE `".$lastInsertId."_inventory_totals` (
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
                    mysql_query("CREATE TABLE `".$lastInsertId."_inventory_total_details` (
                                    `vendor_id` INT(11) NULL DEFAULT NULL,
                                    `product_id` INT(11) NOT NULL DEFAULT '0',
                                    `color_id` INT(11) NULL DEFAULT NULL,
                                    `size_id` INT(11) NULL DEFAULT NULL,
                                    `location_id` INT(11) NOT NULL DEFAULT '0',
                                    `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                                    `expired_date` DATE NOT NULL,
                                    `transaction_detail_id` INT(11) NULL DEFAULT NULL,
                                    `total_cycle` DECIMAL(15,3) NULL DEFAULT '0.000',
                                    `total_so` DECIMAL(15,3) NULL DEFAULT '0.000',
                                    `total_pos` DECIMAL(15,3) NULL DEFAULT '0.000',
                                    `total_pb` DECIMAL(15,3) NULL DEFAULT '0.000',
                                    `total_pbc` DECIMAL(15,3) NULL DEFAULT '0.000',
                                    `total_cm` DECIMAL(15,3) NULL DEFAULT '0.000',
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
                    $inventoryAfInsert  = $this->Inventory->createTrigger(5, $lastInsertId);
                    $locTotalAfInsert = $this->Inventory->createTrigger(8, $lastInsertId);
                    $locTotalAfUpdate = $this->Inventory->createTrigger(9, $lastInsertId);
                    $locTotalDetailAfInsert = $this->Inventory->createTrigger(6, $lastInsertId);
                    $locTotalDetailAfUpdate = $this->Inventory->createTrigger(7, $lastInsertId);
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
            }
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Add New');
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Add New');
        $locationGroups = ClassRegistry::init('LocationGroup')->find("list", array("conditions" => array("LocationGroup.is_active = 1", "id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")")));
        $this->set(compact("locationGroups"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'locations', $id, $this->data['Location']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Location']['modified']    = $dateNow;
                $this->data['Location']['modified_by'] = $user['User']['id'];
                if ($this->Location->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Data cloud not been delete' && $error != 'Cannot change warehouse' && $error != 'Invalid Data'){
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Edit', $id);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Edit ('.$error.')', $id);
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Edit', $id);
            $this->data = $this->Location->read(null, $id);
            $locationGroups = ClassRegistry::init('LocationGroup')->find("list", array("conditions" => array("LocationGroup.is_active = 1", "id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")")));
            $isForSales = array("1" => ACTION_YES, "0" => ACTION_NO);
            $this->set(compact("locationGroups", "isForSales"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $totalQty = 0;
        $sqlCheckTotal = mysql_query("SELECT SUM(IFNULL(total_qty, 0)) AS total_qty FROM {$id}_inventory_totals GROUP BY product_id");
        if(mysql_num_rows($sqlCheckTotal)){
            $rowTotal = mysql_fetch_array($sqlCheckTotal);
            $totalQty = $rowTotal['total_qty'];
        }
        if($totalQty == 0){
            $dateNow  = date("Y-m-d H:i:s");
            Configure::write('debug', 0);
            $this->data = $this->Location->read(null, $id);
            mysql_query("UPDATE `locations` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            $error = mysql_error();
            if($error != 'Data cloud not been delete' && $error != 'Cannot change warehouse' && $error != 'Invalid Data'){
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Delete', $id);
                echo MESSAGE_DATA_HAS_BEEN_DELETED;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Delete ('.$error.')', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Delete (Error)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function status($id = null, $status = 1) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->Location->read(null, $id);
        mysql_query("UPDATE `locations` SET `is_active`=".$status.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Change Status', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'Location', 'Export to Excel');
            $filename = "public/report/location_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Location' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_LOCATION_GROUP . "\t" . TABLE_NAME. "\t" . TABLE_STATUS;
            $query = mysql_query('  SELECT id, (SELECT name FROM location_groups WHERE id=locations.location_group_id), name FROM locations WHERE (is_active=1 OR is_active = 3) AND id IN (SELECT location_id FROM user_locations WHERE user_id = '.$user['User']['id'].') ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $status = '';
                if($data[3] == 1){
                    $status = TABLE_INACTIVE;
                }else{
                    $status = TABLE_ACTIVE;
                }
                $excelContent .= "\n" . $index++ . "\t" . $data[1] . "\t" . $data[2]. "\t" . $status;
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    
    function printLayout($id = null){
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $location = $this->Location->read(null, $id);
        $this->set(compact('location'));
    }

}

?>