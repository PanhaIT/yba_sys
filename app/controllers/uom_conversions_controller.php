<?php

class UomConversionsController extends AppController {

    var $name = 'UomConversions';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Dashboard');
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
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'View');
        $this->set('uomConversion', $this->UomConversion->read(null, $id));
        $uomList = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2')));
        $this->set(compact("uomList"));
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $queryIfExists = mysql_query("SELECT id FROM uom_conversions WHERE from_uom_id='" . $this->data['UomConversion']['from_uom_id'] . "' AND is_active = 1");
            if (mysql_num_rows($queryIfExists)) {
                $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Add New (UoM From ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                Configure::write('debug', 0);
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->UomConversion->create();
                $this->data['UomConversion']['from_uom_id'] = $this->data['UomConversion']['from_uom_id'];
                $this->data['UomConversion']['to_uom_id'] = $this->data['UomConversion']['to_uom_id'];
                $this->data['UomConversion']['value'] = $this->Helper->replaceThousand($this->data['UomConversion']['value']);
                $this->data['UomConversion']['created']    = $dateNow;
                $this->data['UomConversion']['created_by'] = $user['User']['id'];
                $this->data['UomConversion']['is_active']  = 1;
                $this->data['UomConversion']['is_small_uom'] = 1;
                if ($this->UomConversion->saveAll($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['UomConversion'], 'uom_conversions');
//                        $restCode[$r]['modified']   = $dateNow;
//                        $restCode[$r]['dbtodo']     = 'uom_conversions';
//                        $restCode[$r]['actodo']     = 'is';
//                        $r++;
                        // Update Small Value of UoM in Product
                        mysql_query("UPDATE `products` SET `small_val_uom`=".$this->data['UomConversion']['value']." WHERE  `price_uom_id`=".$this->data['UomConversion']['from_uom_id'].";");
                        // Convert to REST
//                        $restCode[$r]['small_val_uom'] = $this->data['UomConversion']['value'];
//                        $restCode[$r]['dbtodo'] = 'products';
//                        $restCode[$r]['actodo'] = 'ut';
//                        $restCode[$r]['con']    = "price_uom_id = ".$this->Helper->getSQLSysCode("uoms", $this->data['UomConversion']['from_uom_id']);
//                        $r++;
                        if(!empty($this->data['other_uom'])){
                            for($i = 0; $i < sizeof($this->data['other_uom']); $i++){
                                $checkVal = abs($this->data['UomConversion']['value'] % $this->data['other_value'][$i]);
                                if($this->data['other_value'][$i] > 0 && $this->data['other_value'][$i] != '' && $checkVal == 0 && ($this->data['other_value'][$i] <= $this->data['UomConversion']['value'])){
                                    $this->UomConversion->create();
                                    $otherUom = array();
                                    $otherUom['UomConversion']['from_uom_id'] = $this->data['UomConversion']['from_uom_id'];
                                    $otherUom['UomConversion']['to_uom_id'] = $this->data['other_uom'][$i];
                                    $otherUom['UomConversion']['value'] = $this->Helper->replaceThousand($this->data['other_value'][$i]);
                                    $otherUom['UomConversion']['created']    = $dateNow;
                                    $otherUom['UomConversion']['created_by'] = $user['User']['id'];
                                    $otherUom['UomConversion']['is_active']  = 1;
                                    $this->UomConversion->saveAll($otherUom);
                                    // Convert to REST
//                                    $restCode[$r] = $this->Helper->convertToDataSync($otherUom['UomConversion'], 'uom_conversions');
//                                    $restCode[$r]['modified']   = $dateNow;
//                                    $restCode[$r]['dbtodo']     = 'uom_conversions';
//                                    $restCode[$r]['actodo']     = 'is';
//                                    $r++;
                                }
                            }
                        }
                        // Save File Send
//                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Add New', $this->data['UomConversion']['from_uom_id']);
                        echo MESSAGE_DATA_HAS_BEEN_SAVED;
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Add New (Error '.$error.')');
                        echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Add New');
        $uomListMain = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2', 'Uom.id NOT IN (SELECT from_uom_id FROM `uom_conversions` WHERE is_active = 1)', 'Uom.id NOT IN (SELECT to_uom_id FROM `uom_conversions` WHERE is_active = 1)', 'Uom.id NOT IN (SELECT price_uom_id FROM `products` WHERE id IN (SELECT product_id FROM inventories WHERE product_id = `products`.id GROUP BY product_id))')));
        $uomList = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2', 'Uom.id NOT IN (SELECT from_uom_id FROM `uom_conversions` WHERE is_active = 1)')));
        $this->set(compact("uomList", "uomListMain"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
//            $r = 0;
//            $restCode  = array();
//            $dateNow   = date("Y-m-d H:i:s");
            Configure::write('debug', 0);
            mysql_query("UPDATE `uom_conversions` SET `is_active`= 2 WHERE  `from_uom_id`=".$this->data['UomConversion']['from_uom_id']);
            $error = mysql_error();
            if($error != 'Cannot update or delete this data'){
                // Convert to REST
//                $restCode[$r]['is_active'] = 2;
//                $restCode[$r]['dbtodo'] = 'uom_conversions';
//                $restCode[$r]['actodo'] = 'ut';
//                $restCode[$r]['con']    = "from_uom_id = ".$this->Helper->getSQLSysCode("uoms", $this->data['UomConversion']['from_uom_id']);
//                $r++;
                $this->UomConversion->create();
                $this->data['UomConversion']['from_uom_id'] = $this->data['UomConversion']['from_uom_id'];
                $this->data['UomConversion']['to_uom_id'] = $this->data['UomConversion']['to_uom_id'];
                $this->data['UomConversion']['value'] = $this->Helper->replaceThousand($this->data['UomConversion']['value']);
                $this->data['UomConversion']['created_by'] = $user['User']['id'];
                $this->data['UomConversion']['modified_by'] = $user['User']['id'];
                $this->data['UomConversion']['is_active'] = 1;
                $this->data['UomConversion']['is_small_uom'] = 1;
                if ($this->UomConversion->saveAll($this->data)) {
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['UomConversion'], 'uom_conversions');
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'uom_conversions';
//                    $restCode[$r]['actodo']     = 'is';
//                    $r++;
                    // Update Small Value of UoM in Product
                    mysql_query("UPDATE `products` SET `small_val_uom`=".$this->data['UomConversion']['value']." WHERE  `price_uom_id`=".$this->data['UomConversion']['from_uom_id'].";");
                    // Convert to REST
//                    $restCode[$r]['small_val_uom'] = $this->data['UomConversion']['value'];
//                    $restCode[$r]['dbtodo'] = 'products';
//                    $restCode[$r]['actodo'] = 'ut';
//                    $restCode[$r]['con']    = "price_uom_id = ".$this->Helper->getSQLSysCode("uoms", $this->data['UomConversion']['from_uom_id']);
//                    $r++;
                    if(!empty($this->data['other_uom'])){
                        for($i = 0; $i < sizeof($this->data['other_uom']); $i++){
                            $checkVal = abs($this->data['UomConversion']['value'] % $this->data['other_value'][$i]);
                            if($this->data['other_value'][$i] > 0 && $this->data['other_value'][$i] != '' && $checkVal == 0 && ($this->data['other_value'][$i] <= $this->data['UomConversion']['value'])){
                                $this->UomConversion->create();
                                $otherUom = array();
                                $otherUom['UomConversion']['from_uom_id'] = $this->data['UomConversion']['from_uom_id'];
                                $otherUom['UomConversion']['to_uom_id'] = $this->data['other_uom'][$i];
                                $otherUom['UomConversion']['value'] = $this->Helper->replaceThousand($this->data['other_value'][$i]);
                                $otherUom['UomConversion']['created_by'] = $user['User']['id'];
                                $otherUom['UomConversion']['is_active'] = 1;
                                $this->UomConversion->saveAll($otherUom);
                                // Convert to REST
//                                $restCode[$r] = $this->Helper->convertToDataSync($otherUom['UomConversion'], 'uom_conversions');
//                                $restCode[$r]['modified']   = $dateNow;
//                                $restCode[$r]['dbtodo']     = 'uom_conversions';
//                                $restCode[$r]['actodo']     = 'is';
//                                $r++;
                            }
                        }
                    }
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Edit', $id, $this->data['UomConversion']['from_uom_id']);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Edit (Invalid Data Save)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Edit', $id);
            $uomAddes = ClassRegistry::init('UomConversion')->find('all', array('conditions' => array('UomConversion.is_active = 1', 'UomConversion.from_uom_id' => $id), 'fields'=>array('UomConversion.to_uom_id', 'UomConversion.value', 'UomConversion.is_small_uom')));
            $uomListMain = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2', 'Uom.id NOT IN (SELECT from_uom_id FROM `uom_conversions` WHERE is_active = 1 AND from_uom_id != '.$id.')', 'Uom.id NOT IN (SELECT to_uom_id FROM `uom_conversions` WHERE is_active = 1)', 'Uom.id NOT IN (SELECT price_uom_id FROM `products` WHERE id IN (SELECT product_id FROM inventories WHERE product_id = `products`.id GROUP BY product_id))')));
            $uomList = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2', 'Uom.id NOT IN (SELECT from_uom_id FROM `uom_conversions` WHERE is_active = 1)')));
            $this->set(compact("uomList","id","uomAddes", "uomListMain"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }  
        $user = $this->getCurrentUser();
//        $r = 0;
//        $restCode  = array();
        Configure::write('debug', 0);
        $this->UomConversion->updateAll(
                array('UomConversion.is_active' => "2"),
                array('UomConversion.from_uom_id' => $id)
        );
        $error = mysql_error();
        if($error != 'Cannot update or delete this data'){
            // Convert to REST
//            $restCode[$r]['is_active'] = 2;
//            $restCode[$r]['dbtodo'] = 'uom_conversions';
//            $restCode[$r]['actodo'] = 'ut';
//            $restCode[$r]['con']    = "from_uom_id = ".$this->Helper->getSQLSysCode("uoms", $id);
//            $r++;
            // Update Small Value of UoM in Product
            mysql_query("UPDATE `products` SET `small_val_uom`=1 WHERE  `price_uom_id`=".$id.";");
            // Convert to REST
//            $restCode[$r]['small_val_uom'] = 1;
//            $restCode[$r]['dbtodo'] = 'products';
//            $restCode[$r]['actodo'] = 'ut';
//            $restCode[$r]['con']    = "price_uom_id = ".$this->Helper->getSQLSysCode("uoms", $id);
            // Save File Send
//            $this->Helper->sendFileToSync($restCode, 0, 0);
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Delete (Cannot update or delete this data)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    
    function exportExcel(){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $this->Helper->saveUserActivity($user['User']['id'], 'UoM Conversion', 'Export to Excel');
            $filename = "public/report/uom_conversion_export.csv";
            $fp = fopen($filename, "wb");
            $excelContent = 'Uom Conversion' . "\n\n";
            $excelContent .= TABLE_NO . "\t" . UOM_FROM . "\t" . UOM_TO;
            $query = mysql_query('  SELECT from_uom_id, (SELECT name FROM uoms WHERE id=from_uom_id), from_uom_id FROM uom_conversions WHERE is_active=1 GROUP BY from_uom_id ORDER BY (SELECT name FROM uoms WHERE id=from_uom_id)');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $text = '';
                $symbol = '';
                $j = 0;
                $sql = mysql_query("SELECT CONCAT_WS(' ',value,'(',(SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id),')') as name FROM uom_conversions WHERE from_uom_id = ".$data[0]." AND is_active = 1");
                while($r=mysql_fetch_array($sql)){
                    if($j > 0){
                        $symbol = ", ";
                    }
                    $text .= $symbol.$r['name'];
                    $j++;
                }
                $excelContent .= "\n" . $index++ . "\t" . $data[1] . "\t" . $text;
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }

}

?>