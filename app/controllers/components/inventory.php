<?php

/**
 * Description of Helper
 *
 * @author UDAYA
 */
date_default_timezone_set('Asia/Phnom_Penh');

class InventoryComponent extends Object {
    
    function saveInventory($data){
        // Insert Inventory
        $sqlInventory = $this->insertInventory($data, 1, $data['module_type'], "inventories");
        $this->querySql($sqlInventory);
        
        // Insert Inventory Location
        $tblInventory = $data['location_id']."_inventories";
        $sqlInventoryLoc = $this->insertInventory($data, 1, $data['module_type'], $tblInventory);
        $this->querySql($sqlInventoryLoc);
        
        // Insert Inventory Total
        $sqlInventoryTotal = $this->insertInventory($data, 2, $data['module_type'], "inventory_totals");
        $this->querySql($sqlInventoryTotal);
        
        // Insert Inventory Total Location
        $tblTotal = $data['location_id']."_inventory_totals";
        $sqlInventoryTotalLoc = $this->insertInventory($data, 3, $data['module_type'], $tblTotal);
        $this->querySql($sqlInventoryTotalLoc);
        
        // Insert Inventory Total Detail Location
        $tblTotalDetail = $data['location_id']."_inventory_total_details";
        $sqlInventoryTotalDetail = $this->insertInventory($data, 4, $data['module_type'], $tblTotalDetail);
        $this->querySql($sqlInventoryTotalDetail);
        
        // Insert Group Total
        $tblGroupTotal = $data['location_group_id']."_group_totals";
        $sqlGroupTotal = $this->insertInventory($data, 5, $data['module_type'], $tblGroupTotal);
        $this->querySql($sqlGroupTotal);
    }
    
    function saveGroupTotalDetail($data){
        // Insert Group Total
        $sqlGroupTotalDetail = $this->insertGroupTotalDetail($data);
        $this->querySql($sqlGroupTotalDetail);
    }
    
    function saveGroupQtyOrder($locationGroupId, $locationId, $productId, $lotsNum, $expDate, $qtyOrder, $date, $symbol){
        $sym = '';
        if($symbol == "-"){
            $sym = '-';
        }
        if($lotsNum == ''){
            $lotsNum = 0;
        }
        if($expDate == ''){
            $expDate = '0000-00-00';
        }
        // Group Totals
        mysql_query("INSERT INTO ".$locationGroupId."_group_totals (transaction_detail_id, product_id, lots_number, expired_date, location_id, location_group_id, total_order) 
                     VALUES (NULL, ".$productId.", '".$lotsNum."', '".$expDate."', ".$locationId.", ".$locationGroupId.", ".$sym.$qtyOrder.") 
                     ON DUPLICATE KEY UPDATE transaction_detail_id = NULL, total_order = (total_order ".$symbol." ".$qtyOrder.")");
        // Group Total Details
        mysql_query("INSERT INTO ".$locationGroupId."_group_total_details (transaction_detail_id, product_id, location_group_id, date, total_order) 
                     VALUES (NULL,".$productId.", ".$locationGroupId.", '".$date."', ".$sym.$qtyOrder.") 
                     ON DUPLICATE KEY UPDATE transaction_detail_id = NULL, total_order = (total_order ".$symbol." ".$qtyOrder.")");
        // Inventory Totals
        mysql_query("INSERT INTO ".$locationId."_inventory_totals (transaction_detail_id, product_id, lots_number, expired_date, location_id, total_order) 
                     VALUES (NULL,".$productId.", '".$lotsNum."', '".$expDate."', ".$locationId.", ".$sym.$qtyOrder.")
                     ON DUPLICATE KEY UPDATE transaction_detail_id = NULL, total_order = (total_order ".$symbol." ".$qtyOrder.")");
        // Inventory Total Details
        mysql_query("INSERT INTO ".$locationId."_inventory_total_details (transaction_detail_id, product_id, lots_number, expired_date, location_id, date, total_order) 
                     VALUES (NULL,".$productId.", '".$lotsNum."', '".$expDate."', ".$locationId.", '".$date."', ".$sym.$qtyOrder.") 
                     ON DUPLICATE KEY UPDATE transaction_detail_id = NULL, total_order = (total_order ".$symbol." ".$qtyOrder.")");
        // Inventory Totals (All)
        mysql_query("INSERT INTO inventory_totals (transaction_detail_id, product_id, lots_number, expired_date, total_order) 
                     VALUES (NULL,".$productId.", '".$lotsNum."', '".$expDate."', ".$sym.$qtyOrder.")
                     ON DUPLICATE KEY UPDATE transaction_detail_id = NULL, total_order = (total_order ".$symbol." ".$qtyOrder.")");
    }
    
    function querySql($sql){
        mysql_query($sql) or die(mysql_error());
    }
    
    function insertGroupTotalDetail($data){
        $sqlQuery   = "";
        $tableName  = $data['location_group_id']."_group_total_details";
        $moduleType = $data['module_type'];
        $fieldList  = $this->checkModule($moduleType);
        $field      = "";
        $values     = "";
        $duplicate  = "";
        $calType    = $fieldList['module_operator']=="-"?$fieldList['module_operator']:"";
        $transDtId  = "NULL";
        if(!empty($data['transaction_id'])){
            $transDtId  = $data['transaction_id'];
        }
        
        // List Field, Value, Duplicate
        $field .= "(`product_id`, `location_group_id`, `transaction_detail_id`,";
        $field .= " `".$fieldList['filed']."`";
        $duplicate .= "`transaction_detail_id` = ".$transDtId.", `".$fieldList['filed']."` = (`".$fieldList['filed']."` ".$fieldList['module_operator']." ".$data['total_order'].")";
        if($fieldList['filed_free'] != ""){
            $field .= ", `".$fieldList['filed_free']."`";
            $duplicate .= ", `".$fieldList['filed_free']."` = (`".$fieldList['filed_free']."` ".$fieldList['module_operator']." ".$data['total_free'].")";
        }
        $field .= ", `date`)";
        $values .= "(";
        $values .= $data['product_id'];
        $values .= ",".$data['location_group_id'];
        $values .= ",".$transDtId;
        $values .= ",".$calType.$data['total_order'];
        if($fieldList['filed_free'] != ""){
            $values .= ",".$calType.$data['total_free'];
        }
        $values .= ",'".$data['date']."'";
        $values .= ")";
        // Get SQL
        $sqlQuery .= $this->getInsert();
        $sqlQuery .= $tableName." ";
        $sqlQuery .= $field;
        $sqlQuery .= $this->getValue();
        $sqlQuery .= $values;
        $sqlQuery .= $this->getDuplicate();
        $sqlQuery .= $duplicate;
        $sqlQuery .= ";";
        return $sqlQuery;
    }
    
    function insertInventory($data, $type, $modueType, $tableName){
        $sqlQuery    = "";
        $module      = $this->checkModule($modueType);
        $qtyOperator = $module['qty_operator'];
        $modOperator = $module['module_operator'];
        $transDtId   = "NULL";
        if(!empty($data['transaction_id'])){
            $transDtId  = $data['transaction_id'];
        }
        $field   = "(";
        $fileds  = $this->getFieldByType($type);
        $values  = "(";
        // Get Field & Value IF Inventory
        if($type == 1){
            $field .= "`transaction_detail_id`, `".$module['module']."`,";
            // Value
            $values .= $transDtId;
            $values .= ",'".$data[$module['module']]."'";
            $values .= ",'".$module['type']."'";
            $values .= ",'".$data['product_id']."'";
            $values .= ",'".$data['location_id']."'";
            $values .= ",'".$data['location_group_id']."'";
            $values .= ",'".($qtyOperator=="-"?$qtyOperator:"").$data['total_qty']."'";
            $values .= ",'".$data['unit_cost']."'";
            $values .= ",'".$data['unit_price']."'";
            $values .= ",'".$data['date']."'";
            $values .= ",'".$data['lots_number']."'";
            $values .= ",'".$data['expired_date']."'";
            $values .= ",".$this->checkValueNull($data['customer_id']);
            $values .= ",".$this->checkValueNull($data['vendor_id']);
            $values .= ",'".date("Y-m-d H:i:s")."'";
            $values .= ",'".$data['user_id']."'";
            $values .= ",'".date("Y-m-d H:i:s")."'";
            $values .= ",'".$data['user_id']."'";
        } else {
            $field .= "`transaction_detail_id`,";
            // Value
            $values .= $transDtId.",";
        }
        // List Field & Value Default
        $length = count($fileds);
        $index  = 1;
        foreach($fileds AS $filedList){
            if($index != $length){
                $symbol = ",";
            }else{
                $symbol = "";
            }
            $field .= "`".$filedList."`".$symbol;
            if($type != 1){
                if($filedList == 'total_qty'){
                    $operator = $qtyOperator=="-"?$qtyOperator:"";
                }else{
                    $operator = "";
                }
                $values .= "'".$operator.$data[$filedList]."'".$symbol;
            }
            $index++;
        }
        // Filed & Value of Module
        if($type == 4){
                $operator = $modOperator=="-"?$modOperator:"";
                $field   .= ",`".$module['filed']."`";
                $values  .= ",'".$operator.$data['total_qty']."'";
        }else if($type == 2){
            $operator = $modOperator=="-"?$modOperator:"";
            $field   .= ",`".$module['filed']."`";
            $values  .= ",'".$operator.$data['total_order']."'";
            if($module['filed_free'] != ""){
                $field   .= ",`".$module['filed_free']."`";
                $values  .= ",'".$operator.$data['total_free']."'";
            }
        }
        
        $field .= ")";
        $values .= ")";
        $duplicate  = "";
        // Get Duplicate Insert
        if($type == 2){
            $duplicate .= "`transaction_detail_id` = ".$transDtId.", `total_qty` = (`total_qty`".$qtyOperator.$data['total_qty']."), `".$module['filed']."` = (`".$module['filed']."`".$modOperator.$data['total_order'].")";
            if($module['filed_free'] != ""){
                $duplicate .= ", `".$module['filed_free']."` = (`".$module['filed_free']."` ".$modOperator." ".$data['total_free'].")";
            }
        }else if($type == 3 || $type == 5){
            $duplicate .= "`transaction_detail_id` = ".$transDtId.", `total_qty` = (`total_qty`".$qtyOperator.$data['total_qty'].")";
        }else if($type == 4){
            $duplicate .= "`transaction_detail_id` = ".$transDtId.", `".$module['filed']."` = (`".$module['filed']."`".$modOperator.$data['total_qty'].")";
        }
        // Get SQL
        $sqlQuery .= $this->getInsert();
        $sqlQuery .= $tableName." ";
        $sqlQuery .= $field;
        $sqlQuery .= $this->getValue();
        $sqlQuery .= $values;
        if($duplicate != ""){
            $sqlQuery .= $this->getDuplicate();
            $sqlQuery .= $duplicate;
        }
        $sqlQuery .= ";";
        return $sqlQuery;
    }
    
    function getFieldByType($type){
        $result = array();
        switch ($type) {
            // Inventory
            case '1':
                $result[] = "type";
                $result[] = "product_id";
                $result[] = "location_id";
                $result[] = "location_group_id";
                $result[] = "qty";
                $result[] = "unit_cost";
                $result[] = "unit_price";
                $result[] = "date";
                $result[] = "lots_number";
                $result[] = "date_expired";
                $result[] = "customer_id";
                $result[] = "vendor_id";
                $result[] = "created";
                $result[] = "created_by";
                $result[] = "modified";
                $result[] = "modified_by";
                break;
            // Inventory Total
            case '2':
                $result[] = "product_id";
                $result[] = "lots_number";
                $result[] = "expired_date";
                $result[] = "total_qty";
                break;
            // Inventory Total SELF
            case '3':
                $result[] = "product_id";
                $result[] = "lots_number";
                $result[] = "expired_date";
                $result[] = "location_id";
                $result[] = "total_qty";
                break;
            // Inventory Total Detail SELF
            case '4':
                $result[] = "product_id";
                $result[] = "lots_number";
                $result[] = "expired_date";
                $result[] = "location_id";
                $result[] = "date";
                break;
            // Group Total Detail SELF
            case '5':
                $result[] = "product_id";
                $result[] = "lots_number";
                $result[] = "expired_date";
                $result[] = "location_id";
                $result[] = "location_group_id";
                $result[] = "total_qty";
                break;
        }
        return $result;
    }
    
    function checkModule($moduleType = null){
        $result = array();
        if(!empty($moduleType)){
            $result['error'] = 0;
            switch ($moduleType){
                case '1':
                    $result['type']   = "Inv Adj";
                    $result['module'] = "inv_adj_id";
                    $result['filed']  = "total_inv_adj";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '2':
                    $result['type']   = "Transfer In";
                    $result['module'] = "transfer_order_id";
                    $result['filed']  = "total_to_in";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '3':
                    $result['type']   = "Transfer Out";
                    $result['module'] = "transfer_order_id";
                    $result['filed']  = "total_to_out";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '4':
                    $result['type']   = "Void Transfer Out";
                    $result['module'] = "transfer_order_id";
                    $result['filed']  = "total_to_in";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "-";
                    break;
                case '5':
                    $result['type']   = "Void Transfer In";
                    $result['module'] = "transfer_order_id";
                    $result['filed']  = "total_to_out";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "-";
                    break;
                case '6':
                    $result['type']   = "Purchase";
                    $result['module'] = "purchase_bill_id";
                    $result['filed']  = "total_pb";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '7':
                    $result['type']   = "Purchase Return";
                    $result['module'] = "purchase_return_id";
                    $result['filed']  = "total_pbr";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '8':
                    $result['type']   = "POS";
                    $result['module'] = "point_of_sales_id";
                    $result['filed']  = "total_pos";
                    $result['filed_free'] = "total_pos_free";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '9':
                    $result['type']   = "Void POS";
                    $result['module'] = "point_of_sales_id";
                    $result['filed']  = "total_pos";
                    $result['filed_free'] = "total_pos_free";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "-";
                    break;
                case '10':
                    $result['type']   = "Sale";
                    $result['module'] = "sales_invoice_id";
                    $result['filed']  = "total_si";
                    $result['filed_free'] = "total_si_free";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '11':
                    $result['type']   = "Sales Return";
                    $result['module'] = "sales_return_id";
                    $result['filed']  = "total_sr";
                    $result['filed_free'] = "total_sr_free";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '12':
                    $result['type']   = "Customer Consignment In";
                    $result['module'] = "consignment_id";
                    $result['filed']  = "total_cus_consign_in";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '13':
                    $result['type']   = "Customer Consignment Out";
                    $result['module'] = "consignment_id";
                    $result['filed']  = "total_cus_consign_out";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '14':
                    $result['type']   = "Customer Return Consignment In";
                    $result['module'] = "consignment_return_id";
                    $result['filed']  = "total_cus_consign_in";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '15':
                    $result['type']   = "Customer Return Consignment Out";
                    $result['module'] = "consignment_return_id";
                    $result['filed']  = "total_cus_consign_out";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '16':
                    $result['type']   = "Vendor Consignment";
                    $result['module'] = "vendor_consignment_id";
                    $result['filed']  = "total_ven_consign_in";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "+";
                    break;
                case '17':
                    $result['type']   = "Vendor Return Consignment";
                    $result['module'] = "vendor_consignment_return_id";
                    $result['filed']  = "total_ven_consign_out";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "+";
                    break;
                case '18':
                    $result['type']   = "Void Purchase";
                    $result['module'] = "purchase_bill_id";
                    $result['filed']  = "total_pb";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "-";
                    break;
                case '19':
                    $result['type']   = "Void Sales Return";
                    $result['module'] = "sales_return_id";
                    $result['filed']  = "total_sr";
                    $result['filed_free'] = "total_sr_free";
                    $result['qty_operator']    = "-";
                    $result['module_operator'] = "-";
                    break;
                case '20':
                    $result['type']   = "Void Purchase Return";
                    $result['module'] = "purchase_return_id";
                    $result['filed']  = "total_pbr";
                    $result['filed_free'] = "";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "-";
                    break;
                case '21':
                    $result['type']   = "Void Sale";
                    $result['module'] = "sales_invoice_id";
                    $result['filed']  = "total_si";
                    $result['filed_free'] = "total_si_free";
                    $result['qty_operator']    = "+";
                    $result['module_operator'] = "-";
                    break;
            }
        }else{
            $result['error'] = 1;
        }
        return $result;
    }
    
    function getInsert(){
        return "INSERT INTO ";
    }
    
    function getValue(){
        return " VALUES ";
    }
    
    function getDuplicate(){
        return " ON DUPLICATE KEY UPDATE ";
    }
    
    function checkValueNull($value){
        if($value == ""){
            $value = "NULL";
        }else{
            $value = "'".$value."'";
        }
        return $value;
    }
    
    function createTrigger($type, $id){
        $fileTrigger = "";
        if(!empty($type) && !empty($id)){
            if($type == 1){
                // Create Trigger Group Detail After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."GroupDetailAfInsert` AFTER INSERT ON `".$id."_group_total_details` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `g_inventory_detail` =  (`g_inventory_detail` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 2){
                // Create Trigger Group Detail After Update
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."GroupDetailAfUpdate` AFTER UPDATE ON `".$id."_group_total_details` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `g_inventory_detail` =  (`g_inventory_detail` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 3){
                // Create Trigger Group Total After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."GroupTotalAfInsert` AFTER INSERT ON `".$id."_group_totals` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `g_inventory` =  (`g_inventory` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 4){
                // Create Trigger Group Total After Update
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."GroupTotalAfUpdate` AFTER UPDATE ON `".$id."_group_totals` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `g_inventory` =  (`g_inventory` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 5){
                // Create Trigger Inventory After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."InventoryAfInsert` AFTER INSERT ON `".$id."_inventories` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `loc_inventory` =  (`loc_inventory` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 6){
                // Create Trigger Inventory Detail After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."InventoryDetailAfInsert` AFTER INSERT ON `".$id."_inventory_total_details` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `loc_inventory_detail` =  (`loc_inventory_detail` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 7){
                // Create Trigger Inventory Detail After Update
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."InventoryDetailAfUpdate` AFTER UPDATE ON `".$id."_inventory_total_details` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `loc_inventory_detail` =  (`loc_inventory_detail` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 8){
                // Create Trigger Inventory Total After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."InventoryTotalAfInsert` AFTER INSERT ON `".$id."_inventory_totals` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `loc_inventory_total` =  (`loc_inventory_total` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            } else if($type == 9){
                // Create Trigger Inventory Total After Insert
                $fileTrigger .= "SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';\n";
                $fileTrigger .= "DELIMITER //\n";
                $fileTrigger .= "CREATE TRIGGER `z".$id."InventoryTotalAfUpdate` AFTER UPDATE ON `".$id."_inventory_totals` FOR EACH ROW BEGIN\n";
                $fileTrigger .= "   IF NEW.transaction_detail_id IS NOT NULL OR NEW.transaction_detail_id != '' THEN\n";
                $fileTrigger .= "       UPDATE `transaction_details` SET `loc_inventory_total` =  (`loc_inventory_total` + 1) WHERE  `id`= NEW.transaction_detail_id;\n";
                $fileTrigger .= "   END IF;\n";
                $fileTrigger .= "END//\n";
                $fileTrigger .= "DELIMITER;\n";
                $fileTrigger .= "SET SQL_MODE=@OLDTMP_SQL_MODE;\n";
            }
            $filename = "tigger".rand(0,10).".sql";
            $fp = fopen("public/trigger/".$filename, "wb");
            fwrite($fp, $fileTrigger);
            fclose($fp);
        } else {
            $filename = '';
        }
        return $filename;
    }
    
    function getFileList($dir){
        // array to hold return value
        $retval = array();

        // add trailing slash if missing
        if(substr($dir, -1) != "/") {
          $dir .= "/";
        }

        // open pointer to directory and read list of files
        $i = 0;
        $d = @dir($dir) or die("getFileList: Failed opening directory {$dir} for reading");
        while(FALSE !== ($entry = $d->read())) {
            // skip hidden files
            if($entry{0} == ".") continue;
            if(is_file("{$dir}{$entry}")) {
              $retval[$i]['name'] = "{$dir}{$entry}";
              $retval[$i]['size'] = filesize("{$dir}{$entry}");
              $retval[$i]['lastmod'] = filemtime("{$dir}{$entry}");
              $i++;
            }
        }
        $d->close();

        return $retval;
    }
    
    function updateTmpPOS(){
        
    }

    function runInventoryValuation(){
        $queryPid  = mysql_query("SELECT product_id AS pid, date FROM inventory_valuation_cals WHERE 1 ORDER BY created;");
        while ($dataPid = mysql_fetch_array($queryPid)) {
            // Update Lock
            mysql_query("UPDATE inventory_valuation_cals SET is_lock = 1, runing_date = '".date("Y-m-d H:i:s")."' WHERE date = '".$dataPid['date']."' AND product_id = ".$dataPid['pid']." AND is_lock = 0;");
            $cal_date             = date('Y-m-d', strtotime($dataPid['date']. ' - 1 days'));
            $acc_total_cost       = array();
            $acc_total_qty        = array();
            $acc_total_qty_small  = array();
            $old_avg_cost         = array();
            // Get Value of Last Record
            $queryInit = mysql_query("SELECT pid,on_hand,on_hand_small,avg_cost,asset_value FROM inventory_valuations
                                      WHERE is_active = 1
                                      AND date < '".$cal_date."'
                                      AND pid = ".$dataPid[0]."
                                      ORDER BY date DESC,created DESC, id DESC LIMIT 1");
            if (mysql_num_rows($queryInit)) {
                $dataInit                   = mysql_fetch_array($queryInit);
                $pid                        = "pid" . $dataInit['pid'];
                $acc_total_cost[$pid]       = $dataInit['asset_value'];
                $acc_total_qty[$pid]        = $dataInit['on_hand'];
                $acc_total_qty_small[$pid]  = $dataInit['on_hand_small'];
                $old_avg_cost[$pid]         = $dataInit['avg_cost'];
            }
            // List Record Calculate AVG Cost
            $query = mysql_query("SELECT inv.id AS id, inv.is_var_cost AS is_var_cost, inv.is_adjust_value AS is_adjust_value,inv.pid AS pid, inv.small_qty AS small_qty, inv.qty AS qty, inv.date AS date, inv.cost AS cost, inv.price AS price, inv.is_refer_gm_id AS is_refer_gm_id, inv.avg_refer AS avg_refer,
                                  inv.on_hand, inv.on_hand_small, inv.avg_cost, inv.asset_value, p.small_val_uom AS small_val_uom
                                  FROM inventory_valuations AS inv INNER JOIN products AS p ON p.id = inv.pid
                                  WHERE inv.is_active = 1
                                  AND inv.date >= '" . $cal_date . "' AND inv.pid   = '" . $dataPid[0] . "'
                                  ORDER BY inv.date,inv.created,inv.id");
            while ($data = mysql_fetch_array($query)) {
                $pid = "pid" . $data['pid'];

                if (!isset($acc_total_cost[$pid])) {
                    $acc_total_cost[$pid] = 0;
                }

                if (!isset($acc_total_qty[$pid])) {
                    $acc_total_qty[$pid] = 0;
                }
                if (!isset($acc_total_qty_small[$pid])) {
                    $acc_total_qty_small[$pid] = 0;
                }

                if (!isset($old_avg_cost[$pid])) {
                    $queryDefaultCost   = mysql_query("SELECT default_cost FROM products WHERE id=" . $data['pid']);
                    $dataDefaultCost    = mysql_fetch_array($queryDefaultCost);
                    $old_avg_cost[$pid] = $dataDefaultCost['default_cost'];
                }

                if ($data['is_adjust_value'] == 1) {
                    $acc_total_cost[$pid]       = $data['asset_value'];
                    $acc_total_qty[$pid]       += $data['qty'];
                    $acc_total_qty_small[$pid] += $data['small_qty'];
                    $onHand      = $this->replaceThousand(number_format($acc_total_qty[$pid], 9));
                    $onHandSmall = $this->replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                    $cost        = $this->replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                    $avgCost     = $this->replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                    $assetVal    = $this->replaceThousand(number_format($acc_total_cost[$pid], 9));
                    mysql_query("UPDATE inventory_valuations SET
                                 on_hand           = '" . $onHand . "',
                                 on_hand_small     = '" . $onHandSmall . "',
                                 cost              = '" . preg_replace('/[-?]/', '',$cost) . "',
                                 avg_cost          = '" . preg_replace('/[-?]/', '',$avgCost) . "',
                                 asset_value       = '" . $assetVal . "'
                                 WHERE id          = " . $data['id']) or die(mysql_error());
                } else if ($data['is_var_cost'] == 1) {
                    $glDetailVal                 = $this->replaceThousand(number_format(($data['qty'] * $old_avg_cost[$pid]), 12));
                    $acc_total_cost[$pid]       += $data['qty'] * $old_avg_cost[$pid];
                    $acc_total_qty[$pid]        += $data['qty'];
                    $acc_total_qty_small[$pid]  += $data['small_qty'];
                    $onHand      = $this->replaceThousand(number_format($acc_total_qty[$pid], 9));
                    $onHandSmall = $this->replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                    $cost        = $this->replaceThousand(number_format($old_avg_cost[$pid], 9));
                    $avgCost     = $this->replaceThousand(number_format($old_avg_cost[$pid], 9));
                    $assetVal    = $this->replaceThousand(number_format($acc_total_cost[$pid], 9));
                    mysql_query("UPDATE inventory_valuations SET
                                 on_hand           = '" . $onHand . "',
                                 on_hand_small     = '" . $onHandSmall . "',
                                 cost              = '" . preg_replace('/[-?]/', '',$cost) . "',
                                 avg_cost          = '" . preg_replace('/[-?]/', '',$avgCost) . "',
                                 asset_value       = '" . $assetVal . "'
                                 WHERE id          = " . $data['id']) or die(mysql_error());
                    mysql_query("UPDATE general_ledger_details SET credit='" . preg_replace('/[-?]/', '',$glDetailVal) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=0 AND credit != '" . preg_replace('/[-?]/', '',$glDetailVal) . "'") or die(mysql_error());
                    if ($data['price'] != '') {
                        $cogs = $this->replaceThousand(number_format(preg_replace('/[-?]/', '',$data['qty'] * $data['price']) - preg_replace('/[-?]/', '',$data['qty'] * $old_avg_cost[$pid]), 12));
                        if ($cogs > 0) {
                            mysql_query("UPDATE general_ledger_details SET credit='" . preg_replace('/[-?]/', '',$cogs) . "',debit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND credit != '" . preg_replace('/[-?]/', '',$cogs) . "'") or die(mysql_error());
                        } else if ($cogs < 0) {
                            mysql_query("UPDATE general_ledger_details SET debit='" . preg_replace('/[-?]/', '',$cogs) . "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit != '" . preg_replace('/[-?]/', '',$cogs) . "'") or die(mysql_error());
                        } else {
                            mysql_query("UPDATE general_ledger_details SET debit=0,credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit=1 AND debit != '0'") or die(mysql_error());
                        }
                    } else {
                        $cogs = $this->replaceThousand(number_format($data['qty'] * $old_avg_cost[$pid], 12));
                        mysql_query("UPDATE general_ledger_details SET debit='" .preg_replace('/[-?]/', '',$cogs). "',credit='0' WHERE inventory_valuation_id=" . $data['id'] . " AND inventory_valuation_is_debit = 1 AND debit != '" .preg_replace('/[-?]/', '',$cogs). "'") or die(mysql_error());
                    }
                    // Check Refer to Goods Mixed
                    if($data['is_refer_gm_id'] > 0){
                        $sqlGM = mysql_query("SELECT * FROM inventory_physicals WHERE id = ".$data['is_refer_gm_id']);
                        if(mysql_num_rows($sqlGM)){
                            $rowGM = mysql_fetch_array($sqlGM);
                            $oldMixCost = 0;
                            $newMixCost = 0;
                            if($data['avg_refer'] > 0){
                                $oldMixCost = $data['avg_refer'] / $rowGM['qty'];
                            }
                            if($avgCost > 0){
                                $newMixCost = $avgCost / $rowGM['qty'];
                            }
                            // Update Goods Mixed Cost
                            mysql_query("UPDATE inventory_valuations SET cost = ((cost - ".$oldMixCost.") + ".$newMixCost.") WHERE inventory_physical_detail_id = ".$data['is_refer_gm_id']); 
                            // Update AVG Refer
                            mysql_query("UPDATE inventory_valuations SET avg_refer = ".$newMixCost." WHERE id = ".$data['id']);
                        }
                    }
                } else {
                    $acc_total_cost[$pid]       += $data['qty'] * $data['cost'];
                    $acc_total_qty[$pid]        += $data['qty'];
                    $acc_total_qty_small[$pid]  += $data['small_qty'];
                    $onHand      = $this->replaceThousand(number_format($acc_total_qty[$pid], 9));
                    $onHandSmall = $this->replaceThousand(number_format($acc_total_qty_small[$pid], 9));
                    $avgCost     = $this->replaceThousand(number_format(($acc_total_cost[$pid] / $acc_total_qty[$pid]), 9));
                    $assetVal    = $this->replaceThousand(number_format($acc_total_cost[$pid], 9));
                    mysql_query("UPDATE inventory_valuations SET
                                 on_hand           ='" . $onHand . "',
                                 on_hand_small     ='" . $onHandSmall . "',
                                 avg_cost          ='" . preg_replace('/[-?]/', '',$avgCost) . "',
                                 asset_value       ='" . $assetVal . "'
                                 WHERE id    =" . $data['id']) or die(mysql_error());
                }
                if ($acc_total_cost[$pid] != 0 || $acc_total_qty[$pid] != 0) {
                    $old_avg_cost[$pid] = @($acc_total_cost[$pid] / $acc_total_qty[$pid]);
                }
            }
            // Delete Lock
            mysql_query("DELETE FROM inventory_valuation_cals WHERE date = '".$dataPid['date']."' AND product_id = ".$dataPid['pid']." AND is_lock = 1;");
        }
    }
    
}

?>