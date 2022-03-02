<?php
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('id', 
                  'to_code', 
                  'order_date', 
                  '(SELECT name FROM location_groups WHERE id=from_location_group_id)', 
                  '(SELECT name FROM location_groups WHERE id=to_location_group_id)', 
                  'total_cost',
                  'balance',
                  'status', 
                  'is_approve');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

/* DB table to use */
$sTable = "transfer_orders";

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
if($status != 'all'){
    $condition = "status = ".$status;
}else{
    $condition = "status >= 0";
}

$condition .= " AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";

if($allowViewByUser){
    $condition .= " AND created_by =".$user['User']['id'];
}

if($fromWarehouse != 'all'){
    $condition .= " AND from_location_group_id = '".$fromWarehouse."'";
} else {
    $condition .= " AND from_location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")";
}

if($toWarehouse != 'all'){
    $condition .= " AND to_location_group_id = '".$toWarehouse."'";
}

if($dateFrom != 'all'){
    $condition .= " AND order_date >= '".$dateFrom."'";
}

if($dateTo != 'all'){
    $condition .= " AND order_date <= '".$dateTo."'";
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
        } else if ($aColumns[$i] == 'order_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'status') {
            switch($aRow[$i]){
                case 0:
                    $row[] =  'Void';
                    break;
                case 1:
                    $row[] =  'Issued';
                    break;
                case 2:
                    $row[] =  'Partial';
                    break;
                case 3:
                    $row[] =  'Fulfilled';
                    break;
            }
        } else if ($aColumns[$i] == 'is_approve') {
            switch($aRow[$i]){
                case 0:
                    $row[] =  'Confirm';
                    break;
                case 1:
                    $row[] =  'Approved';
                    break;
                case 2:
                    $row[] =  'Reject';
                    break;
                case 3:
                    $row[] =  'Auto';
                    break;
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $queryHasReceipt = mysql_query("SELECT id FROM transfer_order_receipts WHERE transfer_order_id=" . $aRow[0] . " AND is_void = 0");
    $row[] =
            ($allowView ? '<a href="" class="btnViewTransferOrder" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPrint &&  $aRow[7] > 0 ? '<a href="" class="btnPrintTOInvoice" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT_TRANSFER_ORDER . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '').
            ($allowApprove &&  $aRow[7] == 1 && $aRow[8] == 0 ? '<a href="" class="btnApproveTransferOrder" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Approve" onmouseover="Tip(\'' . ACTION_APPROVE . '\')" src="' . $this->webroot . 'img/button/approved.png" /></a> ' : '') .
            ($allowAging && $aRow[5] > 0? '<a href="" class="btnAgingTransferOrder" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Aging" onmouseover="Tip(\'' . TABLE_PAY . '\')" src="' . $this->webroot . 'img/button/aging.png" /></a> ' : '') .
            ($allowEdit && !mysql_num_rows($queryHasReceipt) && $aRow[7] > 0 && ($aRow[8] == 1 || $aRow[8] == 3) ? '<a href="" class="btnEditTransferOrder" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete  &&  $aRow[7] > 0 && ($aRow[8] == 1 || $aRow[8] == 3) ? '<a href="" class="btnDeleteTransferOrder" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>