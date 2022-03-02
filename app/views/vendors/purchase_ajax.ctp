<?php
header("Content-type: text/plain");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}

$isPos = false;
$status = 2;

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("purchase_orders.id", 
    "purchase_orders.type",
    "purchase_orders.date",
    "purchase_orders.code",
    "purchase_orders.total_amount",
    "purchase_orders.balance",
    "CASE purchase_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 3 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END",
    "purchase_orders.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "purchase_orders.id";

/* DB table to use */
$sTable = " (SELECT 
            purchases.id AS id,
            'Purchase' AS type,
            purchases.order_date AS date,
            purchases.po_code AS code,
            (IFNULL(purchases.total_amount, 0) + IFNULL(purchases.total_vat,0) - IFNULL(purchases.discount_amount,0)) AS total_amount,
            purchases.balance,
            purchases.status,
            currency_centers.symbol
            FROM purchase_orders AS purchases
            INNER JOIN currency_centers ON currency_centers.id = purchases.currency_center_id
            WHERE purchases.vendor_id = '".$vendorId."' AND purchases.status >= 0
            UNION ALL
            SELECT 
            returns.id AS id,
            'Purchase Return' AS type,
            returns.order_date AS date,
            returns.pr_code AS code,
            (IFNULL(returns.total_amount, 0) + IFNULL(returns.total_vat,0)) AS total_amount,
            returns.balance,
            returns.status,
            currency_centers.symbol
            FROM purchase_returns AS returns
            INNER JOIN currency_centers ON currency_centers.id = returns.currency_center_id
            WHERE returns.vendor_id = '".$vendorId."' AND returns.status >= 0
            ORDER BY date, balance) AS purchase_orders";

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
 * no need to aging below this line
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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "purchase_orders.id"){
                $sOrder .= "purchase_orders.date " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
            } else {
                $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
            }
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
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
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
$condition = "1";

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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 'purchase_orders.date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'purchase_orders.code') {
            $row[] = '<a href="#" class="btnPrintViewVendorRecord" rel="'.$aRow[0].'" trans_type="'.$aRow[1].'">'.$aRow[$i].'</a>';
        } else if ($aColumns[$i] == "CASE purchase_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 3 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END") {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'purchase_orders.type') { // Check Is POS
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'purchase_orders.total_amount' || $aColumns[$i] == 'purchase_orders.balance' || $aColumns[$i] == 'purchase_orders.total_deposit') {
            $row[] = $aRow[7]." ".number_format($aRow[$i], $costDecimal);
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
            
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>