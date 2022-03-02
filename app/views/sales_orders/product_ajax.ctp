<?php
header("Content-type: text/plain");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$allowLots    = false;
$allowExpired = false;
$allowStockMinus = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}

$sqlAllowMinus = mysql_query("SELECT allow_negative_stock FROM location_groups WHERE id = ".$locationGroupId);
if(mysql_num_rows($sqlAllowMinus)){
    $rowAllStock = mysql_fetch_array($sqlAllowMinus);
    if($rowAllStock[0] == 1){
        $allowStockMinus = true;
    }
}

include('includes/function.php');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
// Get Location Setting
$sqlLocSetting = mysql_query("SELECT * FROM location_settings WHERE id = 4");
$rowLocSetting = mysql_fetch_array($sqlLocSetting);
$locCon     = '';
if($rowLocSetting['location_status'] == 1){
    $locCon = ' AND is_for_sale = 1';
}
$dateNow = date("Y-m-d");
$tableName  = $locationGroupId."_group_totals";

$lotsField  = '';
$lotsIndex  = '';
$lotsColumn = '';
$lotsGroup  = '';
if($allowLots == true){
    $lotsField  = '`lots_number` VARCHAR(50) NOT NULL,';
    $lotsIndex  = 'KEY `lots_number` (`lots_number`),';
    $lotsColumn = ', lots_number';
    $lotsGroup  = ','.$tableName.'.lots_number';
}
$expField  = '';
$expIndex  = '';
$expColuom = '';
$expGroup  = '';
if($allowExpired == true){
    $expField  = '`expired_date` DATE NOT NULL,';
    $expIndex  = 'KEY `expired_date` (`expired_date`),';
    $expColuom = ',expired_date';
    $expGroup  = ','.$tableName.'.expired_date';
}
$columsName = "(".$tableName.".total_qty - ".$tableName.".total_order)";
$sGroup     = " GROUP BY products.id, ".$tableName.".product_id".$lotsGroup.$expGroup;
if(strtotime($orderDate) < strtotime($dateNow) ){
    /**
    * table MEMORY
    * default max_heap_table_size 16MB
    */
    $tableTmp = "sales_tmp_inventory_".$user['User']['id'];
    mysql_query("SET max_heap_table_size = 1024*1024*1024");
    mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT,
                      `date` date DEFAULT NULL,
                      `product_id` int(11) DEFAULT NULL,
                      ".$lotsField."
                      ".$expField."
                      `location_group_id` int(11) DEFAULT NULL,
                      `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `product_id` (`product_id`),
                      ".$lotsIndex."
                      ".$expIndex."
                      KEY `location_group_id` (`location_group_id`),
                      KEY `date` (`date`)
                    ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
    // Get Total Qty On Peroid
    $joinProducts = " INNER JOIN products ON";
    $tableDailyBbi = "";
    $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out)) AS total_qty, product_id".$lotsColumn.$expColuom;
    $conditionDailyBbi = " products.is_active = 1 AND date <='".$orderDate."' AND products.company_id = ".$companyId;
    $groupByDaily = "GROUP BY product_id".$lotsColumn.$expColuom;
    // List Location
    $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id = '.$locationGroupId.$locCon.' GROUP BY id');
    if(@mysql_num_rows($queryLocationList)){
        if(mysql_num_rows($queryLocationList) == 1){
            while($dataLocationList=mysql_fetch_array($queryLocationList)){
                // Stock Daily Bigging
                $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
            }
        }else{
            $locId = 1;
            $tmpLocationId = 0;
            while($dataLocationList=mysql_fetch_array($queryLocationList)){
                if($locId == 1){
                    // Stock Daily Bigging
                    $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
                }else{
                    // Stock Daily Ending
                    $tableDailyBbi .= " UNION ALL SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE ".$conditionDailyBbi." ".$groupByDaily;
                }
                $tmpLocationId = $dataLocationList['location_id'];
                $locId++;
            }
        }
    }
    // SQL 
    $tableName  = $tableTmp;
    $sumEnding  = $tableName.".total_qty";
    $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, product_id".$lotsColumn.$expColuom." FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id".$lotsColumn.$expColuom;
    $queryTotal = mysql_query($sqlCmtDailyBiginning);
    while($dataTotal = mysql_fetch_array($queryTotal)){
        $lotsValue  = '';
        if($allowLots == true){
            $lotsValue  = ", '".$dataTotal['lots_number']."'";
        }
        $expValue  = '';
        if($allowExpired == true){
            $expValue  = ", '".$dataTotal['expired_date']."'";
        }
        mysql_query("INSERT INTO $tableTmp (
                            date,
                            product_id,
                            location_group_id,
                            total_qty
                            ".$lotsColumn."
                            ".$expColuom."
                        ) VALUES (
                            '" . $orderDate . "',
                            " . $dataTotal['product_id'] . ",
                            " . $locationGroupId . ",
                            " . $dataTotal['qty'] . "
                            " . $lotsValue . "
                            " . $expValue . "
                        )");
    }
    /** F-ID: 1100
    * Compare Total Qty in Pass and Current Date (sumEnding = Total Qty In Pass, columCurrent = Total Qty in Current)
    * IF PASS < CURRENT TotalQty = PASS
    * ELSE PASS >= CURRENT TotalQty = CURRENT
    */
    $lotsGroup  = '';
    if($allowLots == true){
        $lotsGroup  = ','.$tableName.'.lots_number';
    }
    $expGroup  = '';
    if($allowExpired == true){
        $expGroup  = ','.$tableName.'.expired_date';
    }
    $columsName = "IFNULL(".$sumEnding.",0)";
    $sGroup     = " GROUP BY products.id, ".$tableName.".product_id".$lotsGroup.$expGroup;
}
if($allowLots == true && $allowExpired == true){
    $stockIndex = 5;
    $aColumns = array('CONCAT_WS("|",products.id,IFNULL(products.price_uom_id, 0),products.is_packet,IFNULL(u.abbr, "Packet"))', 
                  'products.code', 
                  'products.name', 
                  $tableName.'.lots_number', 
                  $tableName.'.expired_date',
                  'SUM('.$columsName.') AS total_qty');
} else if($allowLots == true){
    $stockIndex = 4;
    $aColumns = array('CONCAT_WS("|",products.id,IFNULL(products.price_uom_id, 0),products.is_packet,IFNULL(u.abbr, "Packet"))', 
                  'products.code', 
                  'products.name', 
                  $tableName.'.lots_number', 
                  'SUM('.$columsName.') AS total_qty');
} else if($allowExpired == true){
    $stockIndex = 4;
    $aColumns = array('CONCAT_WS("|",products.id,IFNULL(products.price_uom_id, 0),products.is_packet,IFNULL(u.abbr, "Packet"))', 
                  'products.code', 
                  'products.name', 
                  $tableName.'.expired_date', 
                  'SUM('.$columsName.') AS total_qty');
} else {
    $stockIndex = 3;
    $aColumns = array('CONCAT_WS("|",products.id,IFNULL(products.price_uom_id, 0),products.is_packet,IFNULL(u.abbr, "Packet"))', 
                  'products.code', 
                  'products.name',
                  'SUM('.$columsName.') AS total_qty');
}

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "products.id";

/* DB table to use */
if ($category) {
    if(strtotime($orderDate) < strtotime($dateNow) ){
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_group_id = ".$locationGroupId." AND ".$tableName.".date = '".$orderDate."' INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) LEFT JOIN uoms AS u ON u.id = products.price_uom_id"; 
    }else{
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1".$locCon.") INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) LEFT JOIN uoms AS u ON u.id = products.price_uom_id";
    }
} else {
    if(strtotime($orderDate) < strtotime($dateNow) ){
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_group_id = ".$locationGroupId." AND ".$tableName.".date = '".$orderDate."' LEFT JOIN uoms AS u ON u.id = products.price_uom_id INNER JOIN product_pgroups ON product_pgroups.product_id = products.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
    }else{
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1".$locCon.") LEFT JOIN uoms AS u ON u.id = products.price_uom_id INNER JOIN product_pgroups ON product_pgroups.product_id = products.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
    }
}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
 * no need to edit below this line
 */

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
            mysql_real_escape_string($_GET['iDisplayLength']);
}


/*
 * Ordering
 */
if (isset($_GET['iSortCol_0'])) {
    $sOrder = "ORDER BY  ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
                                " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }
}


/*
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ($_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($aColumns); $i++) {
        if($i == 1 || $i == 2){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 1; $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if($i == 1 || $i == 2){
            if ($sWhere == "") {
                $sWhere = "WHERE ";
            } else {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

/* Customize condition */
$condition = "products.is_active = 1 AND ((products.price_uom_id IS NOT NULL AND products.is_packet = 0) OR (products.price_uom_id IS NULL AND products.is_packet = 1)) AND ((is_not_for_sale = 0 AND period_from IS NULL AND period_to IS NULL) OR (is_not_for_sale = 0 AND period_from <= '".$orderDate."' AND period_to >= '".$orderDate."') OR (is_not_for_sale = 1 AND period_from IS NOT NULL AND period_to IS NOT NULL AND '".$orderDate."' NOT BETWEEN period_from AND period_to))";
if ($companyId) {
    $condition .= " AND products.company_id=" . $companyId;
}

if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}
/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $sGroup
        $sOrder
        $sLimit
";

$rResult = mysql_query($sQuery) or die(mysql_error());

/* Data set length after filtering */
$sQuery = "
        SELECT FOUND_ROWS()
";
$rResultFilterTotal = mysql_query($sQuery) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

/* Total data set length */
$sQuery = "
        SELECT COUNT(" . $sIndexColumn . ")
        FROM   $sTable
";
$rResultTotal = mysql_query($sQuery) or die(mysql_error());
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];


/*
 * Output
 */
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

$index = $_GET['iDisplayStart'];
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $totalStock = $aRow[$stockIndex];
    $totalOrder = 0;
    $filedArray = explode("|",$aRow[0]);
    $smallLabel = "";
    $smallUom   = 1;
    $attr = 'lot="" exp=""';
    $conditionStock = '';
    $conditionOrder = '';
    if($allowLots == true && $allowExpired == true){
        $attr = ' lot="'.$aRow[3].'" exp="'.$aRow[4].'"';
        $conditionStock = " AND lots_number = '".$aRow[3]."' AND expired_date = '".$aRow[4]."'";
        $conditionOrder = " AND sor.lots_number = '".$aRow[3]."' AND sor.expired_date = '".$aRow[4]."'";
    } else if($allowLots == true){
        $attr = ' lot="'.$aRow[3].'" exp="0000-00-00"';
        $conditionStock = " AND lots_number = '".$aRow[3]."'";
        $conditionOrder = " AND sor.lots_number = '".$aRow[3]."'";
    } else if($allowExpired == true){
        $attr = ' lot="0" exp="'.$aRow[3].'"';
        $conditionStock = " AND expired_date = '".$aRow[3]."'";
        $conditionOrder = " AND sor.expired_date = '".$aRow[3]."'";
    }
    // Check Packet
    if($filedArray[2] == 0){
        if($filedArray[1] != ''){
            $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$filedArray[1]."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$filedArray[1]." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$filedArray[1].")
                                ORDER BY conversion ASC");
            while($data=mysql_fetch_array($query)){
                $smallLabel = $data['abbr'];
                $smallUom   = $data['conversion'];
            }
        }
        if(strtotime($orderDate) < strtotime($dateNow) ){
            $sqlCurrentStock = mysql_query("SELECT SUM(total_qty - total_order) AS total_qty FROM ".$locationGroupId."_group_totals WHERE product_id = ".$filedArray[0].$conditionStock.";");
            if(mysql_num_rows($sqlCurrentStock)){
                $rowCurrentStock = mysql_fetch_array($sqlCurrentStock);
                if($rowCurrentStock[0] < $totalStock){
                    $totalStock = $rowCurrentStock[0];
                }
            } else {
                $totalStock = 0;
            }
        }
    }
    
    if(!empty($saleOderId)){
        $sql = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = ".$filedArray[0]." AND sor.sales_order_id = ".$saleOderId." AND sor.location_group_id = ".$locationGroupId." AND date = '".$orderDate."'".$conditionOrder." GROUP BY sor.product_id");
        if(mysql_num_rows($sql)){
            $rowOrder   = mysql_fetch_array($sql);
            $totalOrder = $rowOrder['total_order'];
        }
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            if(($totalStock + $totalOrder) > 0 || $allowStockMinus == true){
                $totalSelect = ($totalStock + $totalOrder);
                if($allowStockMinus == true){
                    $totalSelect = 1000000;
                }
                $row[] = '<input type="radio" value="'.htmlspecialchars($aRow[1], ENT_QUOTES, 'UTF-8').'"'.$attr.' class="'.$totalSelect.'" name="chkProductSales" />';
            }else{
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'products.code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'SUM('.$columsName.') AS total_qty') {
            $row[] = showTotalQty(($totalStock + $totalOrder), $filedArray[3], $smallUom, $smallLabel);
        } else if ($aColumns[$i] == $tableName.".lots_number") {
            if($aRow[$i] != '' && $aRow[$i] != '0'){
                $row[] = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == $tableName.".expired_date") {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00'){
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'products.name') {
            /* General output */
            $row[] = $aRow[$i];
        }
        
    }
    $output['aaData'][] = $row;
}
if(strtotime($orderDate) < strtotime($dateNow) ){
    // DROP Tmp Sale Table
    mysql_query("DROP TABLE `".$tableTmp."`;");
}
echo json_encode($output);
?>