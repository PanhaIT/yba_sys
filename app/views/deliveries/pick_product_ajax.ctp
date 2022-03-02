<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */
// Get Location Setting
$sqlLocSetting = mysql_query("SELECT * FROM location_settings WHERE id = 4");
$rowLocSetting = mysql_fetch_array($sqlLocSetting);
$locCon     = '';
if($rowLocSetting['location_status'] == 1){
    $locCon = ' AND is_for_sale = 1';
}

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("Inventory.product_id", "Inventory.lots_number", "Inventory.expired_date", "(SELECT name FROM locations WHERE id = Inventory.location_id)", "IFNULL(Inventory.total_qty, 0)", "Inventory.location_id");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "Inventory.product_id";

/* DB table to use */
$sTable = " ".$locationGroupId."_group_totals AS Inventory";


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
for ($i = 0; $i < count($aColumns) - 1; $i++) {
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
$condition = "Inventory.product_id =".$productId." AND Inventory.location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId.$locCon.")";
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
$group_by = "GROUP BY Inventory.location_id, Inventory.lots_number, Inventory.expired_date HAVING IFNULL(Inventory.total_qty, 0) > 0";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $group_by
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
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $totalOrder = 0;
    $sqlOrder = mysql_query("SELECT SUM(qty) FROM stock_orders WHERE sales_order_id IS NULL AND consignment_id IS NULL AND product_id = ".$productId." AND location_group_id = ".$locationGroupId." AND location_id = ".$aRow[5]." AND lots_number = '".$aRow[1]."' AND expired_date = '".$aRow[2]."'");
    if(mysql_num_rows($sqlOrder)){
        $rowOrder = mysql_fetch_array($sqlOrder);
        $totalOrder = $rowOrder[0];
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = '<input type="checkbox" location-id="'.$aRow[5].'" value="'.$aRow[0].'" expired="'.$aRow[2].'" lots="'.$aRow[1].'" name="chkPickProduct[]" />';
        } else if ($aColumns[$i] == "Inventory.lots_number") {
            if($aRow[$i] == '0'){
                $row[] = '';
            }else{
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == "Inventory.expired_date") {
            if($aRow[$i] == '0000-00-00'){
                $row[] = '';
            }else{
                $row[] = dateShort($aRow[$i]);
            }
        } else if ($aColumns[$i] == 'IFNULL(Inventory.total_qty, 0)') {
            $row[] = ($aRow[$i] - $totalOrder)." ".$smallUomLabel;
        } else if ($aColumns[$i] == 'Inventory.location_id') {
            $row[] = "<input type='hidden' class='qtyInventory' default-total-qty='".$aRow[4]."' value='".($aRow[4])."' /><input type='text' class='qtyPick float' id='qtyPick_".$aRow[0]."' readonly='readonly' value='0' /> ".$smallUomLabel;
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;    
}

echo json_encode($output);