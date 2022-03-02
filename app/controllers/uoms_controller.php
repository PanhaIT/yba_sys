<?php

class UomsController extends AppController {

    var $name = 'Uoms';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'View', $id);
        $this->set('uom', $this->Uom->read(null, $id));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'uoms', $this->data['Uom']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Add New (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->Uom->create();
                $this->data['Uom']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Uom']['created']    = $dateNow;
                $this->data['Uom']['created_by'] = $user['User']['id'];
                $this->data['Uom']['is_active'] = 1;
                if ($this->Uom->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['Uom'], 'uoms');
//                        $restCode[$r]['modified'] = $dateNow;
//                        $restCode[$r]['dbtodo']   = 'uoms';
//                        $restCode[$r]['actodo']   = 'is';
                        // Save File Send
//                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Send to E-Commerce
//                        $e = 0;
//                        $syncEco = array();
                        // Convert to REST
//                        $syncEco[$e]['sys_code']  = $this->data['Uom']['sys_code'];
//                        $syncEco[$e]['name']      = $this->data['Uom']['name'];
//                        $syncEco[$e]['abbr']      = $this->data['Uom']['abbr'];
//                        $syncEco[$e]['created']   = $dateNow;
//                        $syncEco[$e]['dbtodo']    = 'uoms';
//                        $syncEco[$e]['actodo']    = 'is';
                        // Save File Send to E-Commerce
//                        $this->Helper->sendFileToSyncPublic($syncEco);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Add New', $this->Uom->id);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Add New (Error '.$error.')');
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $types = array(
            'Count' => 'Count',
            'Weight' => 'Weight',
            'Length' => 'Length',
            'Area' => 'Area',
            'Volume' => 'Volume',
            'Time' => 'Time'
        );
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Add New');
        $this->set(compact("types"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'uoms', $id, $this->data['Uom']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Edit (Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Uom']['modified']    = $dateNow;
                $this->data['Uom']['modified_by'] = $user['User']['id'];
                if ($this->Uom->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['Uom'], 'uoms');
//                        $restCode[$r]['dbtodo'] = 'uoms';
//                        $restCode[$r]['actodo'] = 'ut';
//                        $restCode[$r]['con']    = "sys_code = '".$this->data['Uom']['sys_code']."'";
                        // Save File Send
//                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Send to E-Commerce
//                        $e = 0;
//                        $syncEco = array();
                        // Convert to REST
//                        $syncEco[$e]['name']     = $this->data['Uom']['name'];
//                        $syncEco[$e]['abbr']     = $this->data['Uom']['abbr'];
//                        $syncEco[$e]['modified'] = $dateNow;
//                        $syncEco[$e]['dbtodo']   = 'uoms';
//                        $syncEco[$e]['actodo']   = 'ut';
//                        $syncEco[$e]['con']      = "sys_code = '".$this->data['Uom']['sys_code']."'";
                        // Save File Send to E-Commerce
//                        $this->Helper->sendFileToSyncPublic($syncEco);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Edit', $id);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Edit (Error '.$error.')', $id);
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Edit', $id);
            $this->data = $this->Uom->read(null, $id);
            $types = array(
                'Count' => 'Count',
                'Weight' => 'Weight',
                'Length' => 'Length',
                'Area' => 'Area',
                'Volume' => 'Volume',
                'Time' => 'Time'
            );
            $this->set(compact("types"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $queryHasProduct = mysql_query("SELECT id FROM products WHERE price_uom_id=" . $id . " AND is_active = 1");
        $queryHasSubUoM  = mysql_query("SELECT id FROM uom_conversions WHERE to_uom_id=" . $id . " AND is_active = 1");
        $queryHasProductSize = mysql_query("SELECT id FROM products WHERE size_uom_id=" . $id . " AND is_active = 1");
        $queryHasProductWeight = mysql_query("SELECT id FROM products WHERE weight_uom_id=" . $id . " AND is_active = 1");
        if(!mysql_num_rows($queryHasProduct) && !mysql_num_rows($queryHasProductSize) && !mysql_num_rows($queryHasProductWeight) && !mysql_num_rows($queryHasSubUoM)){
//            $r = 0;
//            $restCode = array();
            $dateNow  = date("Y-m-d H:i:s");
            $this->data = $this->Uom->read(null, $id);
            mysql_query("UPDATE `uoms` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
            // Convert to REST
//            $restCode[$r]['is_active']   = 2;
//            $restCode[$r]['modified']    = $dateNow;
//            $restCode[$r]['modified_by'] = $this->Helper->getSQLSysCode("users", $user['User']['id']);
//            $restCode[$r]['dbtodo'] = 'uoms';
//            $restCode[$r]['actodo'] = 'ut';
//            $restCode[$r]['con']    = "sys_code = '".$this->data['Uom']['sys_code']."'";
            // Save File Send
//            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Send to E-Commerce
//            $e = 0;
//            $syncEco = array();
            // Convert to REST
//            $syncEco[$e]['is_active'] = 2;
//            $syncEco[$e]['modified']  = $dateNow;
//            $syncEco[$e]['dbtodo']    = 'uoms';
//            $syncEco[$e]['actodo']    = 'ut';
//            $syncEco[$e]['con']       = "sys_code = '".$this->data['Uom']['sys_code']."'";
            // Save File Send to E-Commerce
//            $this->Helper->sendFileToSyncPublic($syncEco);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Delete (Error UoM ready in used)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function getRelativeUom($uomId = null, $uomSku = 'all', $productId = null, $branchId = null) {
        $this->layout = 'ajax';
        $this->set(compact('uomId', 'uomSku', 'productId', 'branchId'));
    }

    function getRelativeUomByProductId() {
        $this->layout = 'ajax';
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Export to Excel');
            $filename = "public/report/uoms_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Uom' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . GENERAL_TYPE . "\t" . TABLE_NAME . "\t" . GENERAL_ABBR;
            $query = mysql_query('  SELECT id, type, name, abbr FROM uoms WHERE is_active=1 ORDER BY name');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $excelContent .= "\n" . $index++ . "\t" . $data[1] . "\t" . $data[2] . "\t" . $data[3];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>