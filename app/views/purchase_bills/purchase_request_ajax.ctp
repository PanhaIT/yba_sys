<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);
include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

$aColumns = array('CONCAT_WS("||",purchase_requests.id,purchase_requests.vendor_id,IFNULL(purchase_requests.total_deposit,0),IFNULL(purchase_requests.vat_setting_id,0),IFNULL(purchase_requests.shipment_id,0))', 
                  'purchase_requests.pr_code', 
                  'CONCAT_WS(" - ",vendors.vendor_code,vendors.name)', 
                  'purchase_requests.order_date' , 
                  'purchase_requests.total_amount',
                  "currency_centers.symbol",
                  "purchase_requests.currency_center_id");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "purchase_requests.id";

/* DB table to use */
$sTable = "`purchase_requests` INNER JOIN vendors ON vendors.id = purchase_requests.vendor_id INNER JOIN currency_centers ON currency_centers.id = purchase_requests.currency_center_id";

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
    for ($i = 0; $i < (count($aColumns) - 2); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 2); $i++) {
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
$condition = "is_close = 0 AND status between 1 AND 2";
if($companyId != ''){
    $condition .= " AND company_id = ".$companyId;
} else {
    $condition .= " AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
}

if($branchId != ''){
    $condition .= " AND branch_id = ".$branchId;
} else {
    $condition .= " AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
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
$sGroup = "";
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
    $record = explode("||",$aRow[0]);
    $rateId = '';
    $rateSell = 0;
    $rateSym  = $aRow[5];
    $sqlRate  = mysql_query("SELECT exchange_rate_id, rate_to_sell FROM branch_currencies WHERE currency_center_id = ".$aRow[6]." AND branch_id = ".$branchId." LIMIT 1;");
    if(mysql_num_rows($sqlRate)){
        $rowRate  = mysql_fetch_array($sqlRate);
        $rateId   = $rowRate[0];
        $rateSell = $rowRate[1];
    }
    for ($i = 0; $i < count($aColumns); $i++) {               
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            $row[] = '<input type="radio" name="chkPurchaseRequestPO" rate-id="'.$rateId.'" rate="'.$rateSell.'" symbol="'.$rateSym.'" vendor-id="' .$record[1]. '" vendor-name="'.$aRow[2].'" value="' .$record[0]. '" date="' .dateShort($aRow[3]). '" po-code="' .$aRow[1]. '" deposit="' .$record[2]. '" vat-id="' .$record[3]. '" ship-id="' .$record[4]. '" class="chkPRPO" />';                                    
        } else if ($aColumns[$i] == 'purchase_requests.order_date') {
            $row[] = dateShort($aRow[$i]);
        } else if ($aColumns[$i] == 'purchase_requests.total_amount') {
            $row[] = number_format($aRow[$i], $rowOption[0])." ".$aRow[5];
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>