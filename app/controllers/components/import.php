<?php

/**
 * Description of Import
 *
 * @author UDAYA
 */

date_default_timezone_set('Asia/Phnom_Penh');

class ImportComponent extends Object {
    var $components = array('Helper');
    
    function ImportCSV2Array($filename){
        /** Include path **/
        set_include_path('includes/Classes/');

        /** PHPExcel_IOFactory */
        include 'PHPExcel/IOFactory.php';
        $results = array();
        $col = 0;
        //CVS
//        $handle  = @fopen($filename, "r");
//        if ($handle) {
//            while (($row = fgetcsv($handle)) !== false) {
//                foreach ($row as $k=>$value) {
//                    $results[$col][$k] = $value;
//                }
//                $col++;
//            }
//            if (!feof($handle)) {
//                echo "Error: unexpected fgets() failn";
//            }
//            fclose($handle);
//        }
        //Excel
        $handle  = @fopen($filename, "r");
        if ($handle) {
            $objReader   = new PHPExcel_Reader_Excel5();
            $objPHPExcel = $objReader->load($filename);
            $sheetDatas  = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            foreach($sheetDatas as $data){
                $colActive = false;
                // Check Coloums Not Empty
                foreach ($data as $k=>$value) {
                    if($value != ''){
                        $colActive = true;
                    }
                }
                if($colActive == true){
                    $indexCol = 0;
                    foreach ($data as $k => $value) {
                        $results[$col][$indexCol] = $value;
                        $indexCol++;
                    }
                    $col++;
                }
            }
            fclose($handle);
        }
        return $results;
    }
    
    function matchField($table){
        $field = array();
        if($table == 'products'){
            $field['Name']      = 'name';
            $field['Barcode']   = 'barcode';
            $field['Unit_Cost'] = array('default_cost', 'unit_cost');
            $field['UoM']       = 'price_uom_id';
            $field['Reorder_Level']   = 'reorder_level';
            $field['Spec']            = 'spec';
            $field['Description']     = 'description';
            $field['Sub_Product_Of']  = 'products';
            $field['Price']  = 'product_prices';
            $field['Group']  = 'product_pgroups';
            $field['Brand']  = 'brands';
            $field['required'] = array('name', 'barcode', 'price_uom_id', 'product_pgroups');
        } else if($table == 'customers'){
            $field['Customer_Code']   = 'customer_code';
            $field['Name_in_English'] = 'name';
            $field['Name_in_Khmer']   = 'name_kh';
            $field['Group']           = 'customer_cgroups';
            $field['Telephone']       = 'main_number';
            $field['Mobile']          = 'mobile_number';
            $field['Alternate_Telephone'] = 'other_number';
            $field['Email']         = 'email';
            $field['Fax']           = 'fax';
            $field['Payment_Terms'] = 'payment_term_id';
            $field['VAT']           = 'vat';
            $field['Limit_Credit']  = 'limit_balance';
            $field['Limit_Invoice'] = 'limit_total_invoice';
            $field['Discount%']     = 'discount';
            $field['Note']          = 'note';
            $field['required'] = array('customer_code', 'name', 'name_kh', 'main_number', 'customer_cgroups');
        } else if($table == 'vendors'){
            $field['Vendor_Code']   = 'vendor_code';
            $field['Vendor_Name']   = 'name';
            $field['Group']         = 'vendor_vgroups';
            $field['Email']         = 'email_address';
            $field['Fax']           = 'fax_number';
            $field['Address']       = 'address';
            $field['Note']          = 'note';
            $field['Payment_Terms']   = 'payment_term_id';
            $field['Work_Telephone']  = 'work_telephone';
            $field['Telephone_Other'] = 'other_number';
            $field['required'] = array('vendor_code', 'name', 'vendor_vgroups');
        }
        return $field;
    } 
    
    function insertImportToDB($table, $coloumns, $user){
        $result = false;
        if(!empty($table) && !empty($coloumns)){
            $getDbField = $this->matchField($table);
            $checkMatch = true;
            $field      = "";
            $values     = '';
            $fieldLen   = count($coloumns[0]) -1;
            $headerCol  = array();
            $fieldFirst = 0;
            // Check Field
            foreach($coloumns[0] AS $k => $val){
                $fCsv = str_replace(" ", "_", trim($val));
                if (!array_key_exists($fCsv, $getDbField)){
                    $checkMatch = false;
                    break;
                } else {
                    $headerCol[$k] = $getDbField[$fCsv];
                    if($k == $fieldFirst){
                        if(is_array($getDbField[$fCsv])){
                            $field .= '(';
                            foreach($getDbField[$fCsv] AS $c => $v){
                                if($c == 0){
                                    $field .= $v;
                                } else {
                                    $field .= ','.$v;
                                }
                            }
                        } else {
                            if($getDbField[$fCsv] == 'products'){
                                $field .= '(parent_id';
                            } else if($getDbField[$fCsv] == 'brands'){
                                $field .= '(brand_id';
                            } else if($getDbField[$fCsv] == 'chart_accounts'){
                                $field .= '(parent_id';
                            } else if($getDbField[$fCsv] == 'product_pgroups' || $getDbField[$fCsv] == 'product_prices' || $getDbField[$fCsv] == 'customer_cgroups' || $getDbField[$fCsv] == 'vendor_vgroups'){
                                $fieldFirst++;
                            } else {
                                $field .= '('.$getDbField[$fCsv];
                            }
                        }
                    } else if($k == $fieldLen){
                        if(is_array($getDbField[$fCsv])){
                            $c = 1;
                            foreach($getDbField[$fCsv] AS $v){
                                if($c == count($getDbField[$fCsv])){
                                    $field .= ','.$v;
                                } else {
                                    $field .= ','.$v;
                                }
                            }
                        } else {
                            if($getDbField[$fCsv] == 'products'){
                                $field .= ',parent_id)';
                            } else if($getDbField[$fCsv] == 'brands'){
                                $field .= ',brand_id)';
                            } else if($getDbField[$fCsv] == 'chart_accounts'){
                                $field .= ',parent_id)';
                            } else if($getDbField[$fCsv] == 'product_pgroups' || $getDbField[$fCsv] == 'product_prices' || $getDbField[$fCsv] == 'customer_cgroups' || $getDbField[$fCsv] == 'vendor_vgroups'){
                                $fieldFirst++;
                            } else {
                                $field .= ','.$getDbField[$fCsv];
                            }
                        }
                        $field .= ", created, created_by, modified)";
                    } else {
                        if(is_array($getDbField[$fCsv])){
                            foreach($getDbField[$fCsv] AS $c => $v){
                                $field .= ','.$v;
                            }
                        } else {
                            if($getDbField[$fCsv] == 'products'){
                                $field .= ',parent_id';
                            } else if($getDbField[$fCsv] == 'brands'){
                                $field .= ',brand_id';
                            } else if($getDbField[$fCsv] == 'chart_accounts'){
                                $field .= ',parent_id';
                            } else if($getDbField[$fCsv] == 'product_pgroups' || $getDbField[$fCsv] == 'product_prices' || $getDbField[$fCsv] == 'customer_cgroups' || $getDbField[$fCsv] == 'vendor_vgroups'){
                                $fieldFirst++;
                            } else {
                                $field .= ','.$getDbField[$fCsv];
                            }
                        }
                    }
                }
            }
            // Check Field Required
            foreach($getDbField['required'] AS $required){
                if (!in_array($required, $headerCol)){
                    $checkMatch = false;
                    break;
                }
            }
            if($checkMatch == true){
                foreach($coloumns AS $i => $col){
                    // Pass Header Index 0
                    if($i == 0){
                        continue;
                    }
                    // Check Value Required
                    $checkValRequired = true;
                    foreach($col AS $k => $val){
                        if (in_array($headerCol[$k], $getDbField['required']) && $val == ''){
                            $checkValRequired = false;
                        }
                    }
                    if($checkValRequired == false){
                        continue;
                    }
                    // Prepare Field
                    $valueFirst = 0;
                    $values     = '';
                    $otherTable = array();
                    foreach($col AS $k => $val){
                        if(!array_key_exists($k, $headerCol)){
                            continue;
                        }
                        if($k == $valueFirst){
                            if(is_array($headerCol[$k])){
                                $values .= '(';
                                foreach($headerCol[$k] AS $c => $v){
                                    if($c == 0){
                                        $values .= $this->checkValueNull($val);
                                    } else {
                                        $values .= ','.$this->checkValueNull($val);
                                    }
                                }
                            } else {
                                if($headerCol[$k] == 'products'){
                                    $values .= '('.$this->responseValue('products', $val);
                                } else if($headerCol[$k] == 'brands'){
                                    $values .= '('.$this->responseValue('brands', $val);
                                } else if($headerCol[$k] == 'price_uom_id'){
                                    $values .= '('.$this->responseValue('uoms', $val);
                                } else if($headerCol[$k] == 'payment_term_id'){
                                    $values .= '('.$this->responseValue('payment_terms', $val);
                                } else if($headerCol[$k] == 'chart_accounts'){
                                    $values .= '('.$this->responseValue('chart_accounts', $val);
                                } else if($headerCol[$k] == 'product_pgroups' || $headerCol[$k] == 'product_prices' || $headerCol[$k] == 'customer_cgroups' || $headerCol[$k] == 'vendor_vgroups'){
                                    $otherTable[$headerCol[$k]] = $val;
                                    $valueFirst++;
                                } else {
                                    $values .= '('.$this->checkValueNull($val);
                                }
                            }
                        } else if($k == $fieldLen){
                            if(is_array($headerCol[$k])){
                                foreach($headerCol[$k] AS $c => $v){
                                    if($c == count($headerCol[$k])){
                                        $values .= ','.$this->checkValueNull($val);
                                    } else {
                                        $values .= ','.$this->checkValueNull($val);
                                    }
                                }
                            } else {
                                if($headerCol[$k] == 'products'){
                                    $values .= ','.$this->responseValue('products', $val);
                                } else if($headerCol[$k] == 'brands'){
                                    $values .= ','.$this->responseValue('brands', $val);
                                } else if($headerCol[$k] == 'price_uom_id'){
                                    $values .= ','.$this->responseValue('uoms', $val);
                                } else if($headerCol[$k] == 'payment_term_id'){
                                    $values .= ','.$this->responseValue('payment_terms', $val);
                                } else if($headerCol[$k] == 'chart_accounts'){
                                    $values .= ','.$this->responseValue('chart_accounts', $val);
                                } else if($headerCol[$k] == 'product_pgroups' || $headerCol[$k] == 'product_prices' || $headerCol[$k] == 'customer_cgroups' || $headerCol[$k] == 'vendor_vgroups'){
                                    $otherTable[$headerCol[$k]] = $val;
                                    $valueFirst++;
                                } else {
                                    $values .= ','.$this->checkValueNull($val);
                                }
                            }
                            $values .= ", '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."')";
                        } else {
                            if(is_array($headerCol[$k])){
                                foreach($headerCol[$k] AS $c => $v){
                                    $values .= ','.$this->checkValueNull($val);
                                }
                            } else {
                                if($headerCol[$k] == 'products'){
                                    $values .= ','.$this->responseValue('products', $val);
                                } else if($headerCol[$k] == 'brands'){
                                    $values .= ','.$this->responseValue('brands', $val);
                                } else if($headerCol[$k] == 'price_uom_id'){
                                    $values .= ','.$this->responseValue('uoms', $val);
                                } else if($headerCol[$k] == 'payment_term_id'){
                                    $values .= ','.$this->responseValue('payment_terms', $val);
                                } else if($headerCol[$k] == 'chart_accounts'){
                                    $values .= ','.$this->responseValue('chart_accounts', $val);
                                } else if($headerCol[$k] == 'product_pgroups' || $headerCol[$k] == 'product_prices' || $headerCol[$k] == 'customer_cgroups' || $headerCol[$k] == 'vendor_vgroups'){
                                    $otherTable[$headerCol[$k]] = $val;
                                    $valueFirst++;
                                } else {
                                    $values .= ','.$this->checkValueNull($val);
                                }
                            }
                        }
                    }
                    if(!empty($field) && !empty($values)){
                        $result    = true;
                        $sqlQuery  = '';
                        $sqlQuery .= $this->getInsert();
                        $sqlQuery .= $table." ";
                        $sqlQuery .= $field;
                        $sqlQuery .= $this->getValue();
                        $sqlQuery .= $values;
                        $sqlQuery .= ";";
                        mysql_query($sqlQuery);
                        $insertId = mysql_insert_id();
                        if($table == 'products'){
                            mysql_query("INSERT INTO product_branches (product_id, branch_id) SELECT ".$insertId.", id FROM branches;");
                        } else if($table == 'vendors'){
                            mysql_query("INSERT INTO vendor_companies (vendor_id, company_id) VALUES (".$insertId.", 1);");
                        } else if($table == 'customers'){
                            mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES (".$insertId.", 1);");
                        }
                        if(!empty($otherTable)){
                            foreach($otherTable AS $key => $val){
                                if($key == 'product_pgroups'){
                                    if($this->responseValue('pgroups', $val) != 'NULL'){
                                        mysql_query("INSERT INTO product_pgroups (product_id, pgroup_id) VALUES ('".$insertId."', ".$this->responseValue('pgroups', $val).")");
                                    } else {
                                        mysql_query("INSERT INTO product_pgroups (product_id, pgroup_id) VALUES ('".$insertId."', 1)");
                                    }
                                } else if($key == 'customer_cgroups'){
                                    if($this->responseValue('cgroups', $val) != 'NULL'){
                                        mysql_query("INSERT INTO customer_cgroups (customer_id, cgroup_id) VALUES ('".$insertId."', ".$this->responseValue('cgroups', $val).")");
                                    } else {
                                        mysql_query("INSERT INTO customer_cgroups (customer_id, cgroup_id) VALUES ('".$insertId."', 1)");
                                    }
                                } else if($key == 'vendor_vgroups'){
                                    if($this->responseValue('vgroups', $val) != 'NULL'){
                                        mysql_query("INSERT INTO vendor_vgroups (vendor_id, vgroup_id) VALUES ('".$insertId."', ".$this->responseValue('vgroups', $val).")");
                                    } else {
                                        mysql_query("INSERT INTO vendor_vgroups (vendor_id, vgroup_id) VALUES ('".$insertId."', 1)");
                                    }
                                } else if($key == 'product_prices'){
                                    if($val > 0){
                                        $sqlBranch = mysql_query("SELECT id FROM branches WHERE is_active = 1");
                                        while($rowBranch = mysql_fetch_array($sqlBranch)){
                                            $sqlUom = mysql_query("SELECT price_uom_id FROM products WHERE id = ".$insertId);
                                            $rowUom = mysql_fetch_array($sqlUom);
                                            mysql_query("INSERT INTO product_prices (branch_id, product_id, price_type_id, uom_id, amount, set_type, created) VALUES (".$rowBranch['id'].", '".$insertId."', 2, ".$rowUom[0].", ".$val.", 1, '".date("Y-m-d H:i:s")."')");
                                            mysql_query("INSERT INTO product_prices (branch_id, product_id, price_type_id, uom_id, amount, set_type, created) VALUES (".$rowBranch['id'].", '".$insertId."', 3, ".$rowUom[0].", ".$val.", 1, '".date("Y-m-d H:i:s")."')");
                                            mysql_query("UPDATE products SET unit_price = ".$val." WHERE id = ".$insertId);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    function insertAdjustment($branchId, $locationGroupId, $locationId, $adjDate, $adjustAs, $coloumns, $user){
        // Check Field
        $result        = false;
        $checkColMatch = true;
        $colDefault    = array("Product Barcode", "Qty");
        $itemIndex     = "";
        $qtyIndex      = "";
        foreach($coloumns[0] AS $k => $val){
            if (!in_array($val, $colDefault)) {
                $checkColMatch = false;
            } else {
                if($val == "Product Barcode"){
                    $itemIndex = $k;
                } else if($val == "Qty"){
                    $qtyIndex  = $k;
                }
            }
        }
        
        if($checkColMatch == true){
            $companyId = 1;
            $result    = true;
            // Cycle Product
            mysql_query("INSERT INTO `cycle_products` (`company_id`, `branch_id`, `date`, `location_group_id`, `deposit_to`, `reference`, `created`, `created_by`, `modified`) VALUES (".$companyId.", ".$branchId.", '".$adjDate."', ".$locationGroupId.", ".$adjustAs.", 'IMP', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].", '".date("Y-m-d H:i:s")."');");
            $cycleProductId = mysql_insert_id();
            // Get Module Code
            $modCode = $this->Helper->getModuleCode("IMP", $cycleProductId, 'reference', 'cycle_products', 'status >= 0 AND branch_id = '.$branchId);
            mysql_query("UPDATE cycle_products SET reference = '".$modCode."' WHERE id = ".$cycleProductId);
            foreach($coloumns AS $i => $col){
                // Pass Header Index 0
                if($i == 0){
                    continue;
                }
                $barcode  = $col[$itemIndex];
                $qty      = $col[$qtyIndex];
                $sqlP     = mysql_query("SELECT products.id, products.small_val_uom FROM products 
                            LEFT JOIN product_with_skus ON product_with_skus.id = products.id 
                            WHERE products.code = '".$barcode."' OR product_with_skus.sku = '".$barcode."' GROUP BY products.id LIMIT 1");
                if(mysql_num_rows($sqlP)){
                    $rowP = mysql_fetch_array($sqlP);
                    $totalAdj = $qty;
                    // Cycle Detail
                    mysql_query("INSERT INTO `cycle_product_details` (`cycle_product_id`, `product_id`, `location_id`, `lots_number`, `expired_date`, `new_qty`, `qty_difference`) VALUES (".$cycleProductId.", ".$rowP['id'].", ".$locationId.", '0', '0000-00-00', ".$totalAdj.", ".$totalAdj.");");
                }
                $i++;
            }
        }
        return $result;
    }
    
    function getInsert(){
        return "INSERT INTO ";
    }
    
    function getValue(){
        return " VALUES ";
    }
    
    function checkValueNull($value){
        if($value == ""){
            $value = "NULL";
        }else{
            $value = "'".mysql_real_escape_string($value)."'";
        }
        return $value;
    }
    
    function responseValue($table, $value){
        $return = "NULL";
        if(!empty($table) && !empty($value)){
            $sqlCheck = mysql_query("SELECT id FROM ".$table." WHERE name = '".mysql_real_escape_string($value)."' LIMIT 1");
            if(@mysql_num_rows($sqlCheck)){
                $rowCheck = mysql_fetch_array($sqlCheck);
                $return = $rowCheck['id'];
            } else {
                if($table != 'products' && $table != 'payment_terms' && $table != 'chart_accounts'){
                    $now = date("Y-m-d H:i:s");
                    if($table == 'uoms'){
                        mysql_query("INSERT INTO ".$table." (`type`, `name`, `abbr`, `created`, `created_by`, `modified`) VALUES ('Count', '".trim($value)."', '".trim($value)."', '".$now."', 1, '".$now."');");
                    } else {
                        mysql_query("INSERT INTO ".$table." (`name`, `created`, `created_by`, `modified`) VALUES ('".trim($value)."', '".$now."', 1, '".$now."');");
                    }
                    $return = mysql_insert_id();
                    if($table == 'pgroups'){
                        mysql_query("INSERT INTO pgroup_companies (pgroup_id, company_id) VALUES (".$return.", 1);");
                    } else if($table == 'vgroups'){
                        mysql_query("INSERT INTO vgroup_companies (vgroup_id, company_id) VALUES (".$return.", 1);");
                    } else if($table == 'cgroups'){
                        mysql_query("INSERT INTO cgroup_companies (cgroup_id, company_id) VALUES (".$return.", 1);");
                    }
                }
            }
        }
        return $return;
    }
    
}
