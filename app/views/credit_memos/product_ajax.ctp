<?php

include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$dateNow = date("Y-m-d");
$columsName = $locationId."_inventory_totals.total_qty";
$tableName = $locationId."_inventory_totals";
$conditionDate = "";
$aColumns = array('CONCAT_WS("|-|",products.id,IFNULL(products.price_uom_id, 0),IFNULL(u.abbr, ""),products.is_packet)', 'products.code', 'products.name', 'IFNULL(u.name, "Packet")', 'IFNULL('.$columsName.',0)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "products.id";

/* DB table to use */
if ($category) {
    $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN uoms AS u ON u.id = products.price_uom_id LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_id = ".$locationId.$conditionDate." INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";

} else {
    $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN uoms AS u ON u.id = products.price_uom_id LEFT JOIN ".$tableName." ON products.id = ".$tableName.".product_id AND ".$tableName.".location_id = ".$locationId.$conditionDate." INNER JOIN product_pgroups ON product_pgroups.product_id = products.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
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
$condition = "products.is_active = 1 AND ((products.price_uom_id IS NOT NULL AND products.is_packet = 0) OR (products.price_uom_id IS NULL AND products.is_packet = 1))";
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
$sGroup = " GROUP BY products.id, products.code, products.name ";
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
    $array = explode("|-|",$aRow[0]);
    $smallUom = 1;
    $smallUomLabel = "";
    // Check Packet
    if($array[3] == 0){
        $qry = mysql_query("SELECT value, (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = " . $array[1] . " AND is_small_uom = 1 AND is_active = 1") or die(mysql_error());
        while (@$d = mysql_fetch_array($qry)) {
            $smallUom = $d['value'];
            $smallUomLabel = $d['abbr'];
        }
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            $row[] = '<input type="radio" value="' . htmlspecialchars($aRow[1], ENT_QUOTES, 'UTF-8') . '" name="chkProduct" />';
        } else if ($aColumns[$i] == 'products.code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'IFNULL('.$columsName.',0)') {
            $row[] = showTotalQty($aRow[$i], $array[2], $smallUom, $smallUomLabel);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>