<?php
header("Content-type: text/plain");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
$columsName = "(".$tableName.".total_qty - ".$tableName.".total_order)";
$sGroup     = " GROUP BY products.id, ".$tableName.".product_id";

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
                      `location_group_id` int(11) DEFAULT NULL,
                      `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                      `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `product_id` (`product_id`),
                      KEY `location_group_id` (`location_group_id`),
                      KEY `date` (`date`)
                    ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
    // Get Total Qty On Peroid
    $joinProducts = " INNER JOIN products ON";
    $tableDailyBbi = "";
    $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_order + total_cus_consign_out)) AS total_qty, SUM(total_order) AS total_order, product_id";
    $conditionDailyBbi = " products.is_active = 1 AND date <='".$orderDate."' AND products.company_id = ".$companyId;
    $groupByDaily = "GROUP BY product_id";
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
    $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, SUM(total_order) AS total_order, product_id FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id";
    $queryTotal = mysql_query($sqlCmtDailyBiginning);
    while($dataTotal = mysql_fetch_array($queryTotal)){
        mysql_query("INSERT INTO $tableTmp (
                            date,
                            product_id,
                            location_group_id,
                            total_qty,
                            total_order
                        ) VALUES (
                            '" . $orderDate . "',
                            " . $dataTotal['product_id'] . ",
                            " . $locationGroupId . ",
                            " . $dataTotal['qty'] . ",
                            " . $dataTotal['total_order'] . "
                        )");
    }
    // SQL 
    $tableCurrent = $locationGroupId."_inventory_totals";
    $columCurrent = $tableCurrent.".total_qty";
    $tableName  = $tableTmp;
    $sumEnding  = $tableName.".total_qty";
    /** F-ID: 1100
    * Compare Total Qty in Pass and Current Date (sumEnding = Total Qty In Pass, columCurrent = Total Qty in Current)
    * IF PASS < CURRENT TotalQty = PASS
    * ELSE PASS >= CURRENT TotalQty = CURRENT
    */
    $columsName = "IF(IFNULL(".$columCurrent.",0) >= IFNULL(".$sumEnding.",0), IFNULL(".$sumEnding.",0), IFNULL(".$columCurrent.",0))";
    $sGroup     = " GROUP BY products.id, ".$tableName.".product_id ";
}

$aColumns = array('CONCAT_WS("|",products.id,IFNULL(products.price_uom_id, 0),products.is_packet)', 
                  'products.code', 
                  'products.name', 
                  'IFNULL(u.abbr, "Packet")', 
                  'IF(products.is_packet = 0,IFNULL(SUM('.$columsName.'),0),1) AS total_qty', 
                  $tableName.'.total_order');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "products.id";

/* DB table to use */
if ($category) {
    if(strtotime($orderDate) < strtotime($dateNow) ){
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableCurrent." ON products.id = ".$tableCurrent.".product_id AND ".$tableCurrent.".location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1".$locCon.") LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_group_id = ".$locationGroupId." AND ".$tableName.".date = '".$orderDate."' INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) LEFT JOIN uoms AS u ON u.id = products.price_uom_id"; 
    }else{
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1".$locCon.") INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) LEFT JOIN uoms AS u ON u.id = products.price_uom_id";
    }
} else {
    if(strtotime($orderDate) < strtotime($dateNow) ){
        $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableCurrent." ON products.id = ".$tableCurrent.".product_id AND ".$tableCurrent.".location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1".$locCon.") LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_group_id = ".$locationGroupId." AND ".$tableName.".date = '".$orderDate."' LEFT JOIN uoms AS u ON u.id = products.price_uom_id INNER JOIN product_pgroups ON product_pgroups.product_id = products.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
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
    $totalOrder = 0;
    $filedArray = explode("|",$aRow[0]);
    $small_label = "";
    $small_uom = 1;
    // Check Packet
    if($filedArray[2] == 0){
        $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$filedArray[1]."
                            UNION
                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$filedArray[1]." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$filedArray[1].")
                            ORDER BY conversion ASC");
        while($data=mysql_fetch_array($query)){
            $small_label = $data['abbr'];
            $small_uom = floatval($data['conversion']);
        }
    }
    if(!empty($saleOderId)){
        $sql = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = ".$filedArray[0]." AND sor.sales_order_id = ".$saleOderId." AND sor.location_group_id = ".$locationGroupId." AND date = '".$orderDate."' GROUP BY sor.product_id");
        if(mysql_num_rows($sql)){
            $r   = mysql_fetch_array($sql);
            $totalOrder = $r['total_order'];
        }
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            if(($aRow[4] + $totalOrder) > 0){
                $row[] = '<input type="radio" value="'.$aRow[1].'" class="'.($aRow[4] + $totalOrder).'" name="chkProduct" />';
            }else{
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'products.code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'IF(products.is_packet = 0,IFNULL(SUM('.$columsName.'),0),1) AS total_qty') {
            $row[] = showTotalQty(($aRow[$i] + $totalOrder), $aRow[3], $small_uom, $small_label);
        } else if ($aColumns[$i] == $tableName.".total_order") {
            $row[] = showTotalQty(($aRow[$i] - $totalOrder), $aRow[3], $small_uom, $small_label);
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