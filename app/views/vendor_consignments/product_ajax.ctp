<?php

include('includes/function.php');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$columsName = $locationId."_"."inventory_totals.total_qty";
$tableName = $locationId."_"."inventory_totals";
$conditionDate = "";
$aColumns = array("CONCAT_WS('|-|',p.id,p.is_expired_date,p.small_val_uom,IFNULL(p.price_uom_id, 0),p.is_packet)",
                   'p.barcode',
                   'p.code',
                   'p.name',
                   'CONCAT_WS("|-|",IFNULL('.$columsName.',0),IFNULL((SELECT abbr FROM uoms WHERE id = p.price_uom_id),"Packet"))', 
                   "IF((SELECT count(id) FROM `inventories` WHERE product_id = p.id AND unit_cost > 0 LIMIT 1) > 0, (SELECT unit_cost FROM `inventories` WHERE product_id = p.id AND unit_cost > 0 ORDER BY `id` DESC LIMIT 1), IFNULL(p.default_cost, 0))");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
if(@$category){
    $sTable = "products as p INNER JOIN product_branches ON product_branches.product_id = p.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON p.id = ".$tableName.".product_id AND ".$tableName.".location_id = ".$locationId.$conditionDate." INNER JOIN product_pgroups ON product_pgroups.product_id = p.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
}else{
    $sTable = "products as p INNER JOIN product_branches ON product_branches.product_id = p.id AND product_branches.branch_id = ".$branchId." LEFT JOIN ".$tableName." ON p.id = ".$tableName.".product_id AND ".$tableName.".location_id = ".$locationId.$conditionDate." INNER JOIN product_pgroups ON product_pgroups.product_id = p.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
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
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

/* Customize condition */
$condition = "p.is_active = 1 AND ((p.price_uom_id IS NOT NULL AND p.is_packet = 0) OR (p.price_uom_id IS NULL AND p.is_packet = 1))";
if (@$companyId) {
    $condition .= " AND p.company_id=" . $companyId;
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
$sGroup = " GROUP BY p.id, p.code, p.name ";
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
    $array = explode('|-|',$aRow[0]);
    $qtyArray = explode('|-|',$aRow[4]);  
    $smallUom = 1;
    $smallUomLabel = "";
    $packetList = "";
    if($array[4] == 0){
        $qry = mysql_query("SELECT value, (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = ".$array[3]." AND is_small_uom = 1 AND is_active = 1");
        while (@$d = mysql_fetch_array($qry)) {
            $smallUom = $d['value'];
            $smallUomLabel = $d['abbr'];
        }
    }else{
        $index = 1;
        $sqlPacket = mysql_query("SELECT products.code, product_with_packets.qty_uom_id, product_with_packets.qty, product_with_packets.conversion FROM product_with_packets INNER JOIN products ON products.id = product_with_packets.packet_product_id WHERE product_with_packets.main_product_id = ".$array[0]);
        while($rowPacket = mysql_fetch_array($sqlPacket)){
            if($index > 1){
                $packetList .= "**";
            }
            $qtyOrder = $rowPacket['qty'];
            $packetList .= $rowPacket['code']."||".$rowPacket['qty_uom_id']."||".$qtyOrder;
            $index++;
        }
    }
    $unit_cost = round($aRow[5], 5);           
    $productPriceByUom="";
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            $proName = str_replace('"', '', $aRow[3]);
            $row[] = '<input type="radio" value="'.$array[0].'" packet="'.$packetList.'" data="'.$aRow[1].'|-|'.$aRow[2].'|-|'.$proName.'|-|'.$array[1].'|-|'.$aRow[5].'|-|'.$array[2].'|-|'.$array[3].'" name="chkProduct" />';
        } else if ($i == 4) {
            $row[] = showTotalQty($qtyArray[0], $qtyArray[1], $smallUom, $smallUomLabel);                                
        } else if ($i == 5) {
            $row[] = number_format($unit_cost, 2);                                  
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>