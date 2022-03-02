<?php
include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

$aColumns = array('CONCAT_WS("||",sales_orders.id,sales_orders.customer_id,sales_orders.company_id,sales_orders.balance)', 'sales_orders.so_code', 'CONCAT_WS(" - ",customers.customer_code,customers.name)', 'sales_orders.order_date');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.id";

/* DB table to use */
$sTable = "`sales_orders` INNER JOIN customers ON customers.id = sales_orders.customer_id";

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
$condition = "sales_orders.status > 0";
if(!empty($companyId)){
    $condition .= ' AND sales_orders.company_id = '.$companyId;
} else {
    $condition .= ' AND sales_orders.company_id = 0';
}
if(!empty($glId)){
    $condition .= ' AND (sales_orders.balance + IFNULL((SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = '.$glId.' AND apply_to_id = sales_orders.id AND deposit_type = 5),0)) > 0';
} else {
    $condition .= ' AND sales_orders.balance > 0';
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
    $totalRemain = 0;
    if(!empty($glId)){
        $sqlGl = mysql_query("SELECT IFNULL(total_deposit,0) FROM general_ledgers WHERE id = ".$glId." AND apply_to_id = ".$record[0]." AND deposit_type = 5");
        if(mysql_num_rows($sqlGl)){
            $rowGl = mysql_fetch_array($sqlGl);
            $totalRemain = $rowGl[0];
        }
    }
    $totalAmount = $record[3] + $totalRemain;
    for ($i = 0; $i < count($aColumns); $i++) {               
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            $row[] = '<input type="radio" name="chkApplyMakeDeposit" amt="' .$totalAmount. '" com-id="' .$record[2]. '" suply-id="' .$record[1]. '" suply-name="'.$aRow[2].'" value="' .$record[0]. '" date="' .dateShort($aRow[3]). '" app-code="' .$aRow[1]. '" />';                                    
        } else if ($aColumns[$i] == 'sales_orders.order_date') {
            $row[] = dateShort($aRow[$i]);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>