<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView  = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowPick  = checkAccess($user['User']['id'], $this->params['controller'], 'pick');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoicePickSlip');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('deliveries.id', 'sales_orders.order_date', 'sales_orders.so_code', 'orders.order_date', 'orders.order_code', 'orders.created', 'CONCAT_WS(" ", users.first_name, users.last_name)', 'deliveries.status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "deliveries.id";

/* DB table to use */
$sTable = "deliveries INNER JOIN sales_orders ON sales_orders.delivery_id = deliveries.id LEFT JOIN orders ON orders.id = sales_orders.order_id LEFT JOIN users ON users.id = orders.created_by";

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
$condition = "deliveries.status IN (2, 3) AND deliveries.type = 1";
if($changeDatePickSlipSR != 'all'){
    $condition .= " AND sales_orders.order_date = '".$changeDatePickSlipSR."'";
}
if($changeBranchPickSlipSR != 'all'){
    $condition .= " AND sales_orders.branch_id = ".$changeBranchPickSlipSR;
}
if($changeCustomerIdPickSlipSR != 'all'){
    $condition .= " AND sales_orders.customer_id = ".$changeCustomerIdPickSlipSR;
}
if($changeStatusPickSlipSR != 'all'){
    $condition .= " AND deliveries.status = ".$changeStatusPickSlipSR;
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
        } else if ($aColumns[$i] == 'sales_orders.order_date' || $aColumns[$i] == 'orders.order_date') {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00'){
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'orders.created') {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00 00:00:00'){
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'deliveries.status') {
            if($aRow[$i] == 2){
                $row[] = "Issued";
            } else {
                $row[] = "Picked";
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            //($aRow[7] == 3? '<a href="" class="btnViewPickSlipSR" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPrint && $aRow[7] == 3? '<a href="" class="btnPrintPickSlipSR" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Print Pick Slip" onmouseover="Tip(\'' . ACTION_PRINT_PICK_SLIP . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .
            ($allowPick && $aRow[7] == 2? '<a href="" class="btnPickPickSlipSR" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Pick" onmouseover="Tip(\'' . ACTION_PICK . '\')" src="' . $this->webroot . 'img/button/hand.png" /></a> ' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>