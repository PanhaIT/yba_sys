<?php
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowReprint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$status = 0;

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("vendor_consignment_returns.id", 
    "vendor_consignment_returns.date",
    "vendor_consignment_returns.code",
    "vendor_consignments.code",
    "CONCAT_WS(' - ',vendors.vendor_code,vendors.name)",
    "vendor_consignment_returns.status");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "vendor_consignment_returns.date";

/* DB table to use */
$sTable = " vendor_consignment_returns 
            INNER JOIN vendor_consignments ON vendor_consignments.id = vendor_consignment_returns.vendor_consignment_id
            LEFT JOIN vendors ON vendors.id = vendor_consignment_returns.vendor_id";

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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "vendor_consignment_returns.id"){
                $sOrder .= "vendor_consignment_returns.created " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
$condition = "vendor_consignment_returns.status >= 0 AND vendor_consignment_returns.company_id IN (SELECT company_id FROM user_companies WHERE user_id='" . $user['User']['id'] . "' GROUP BY company_id) AND vendor_consignment_returns.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id='" . $user['User']['id'] . "' GROUP BY branch_id) AND vendor_consignment_returns.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id='" . $user['User']['id'] . "' GROUP BY location_group_id)";

if($allowViewByUser){
    $condition .= " AND vendor_consignment_returns.created_by = " . $user['User']['id'];
}

if ($vendor != "all") {
    $condition .= " AND vendor_consignment_returns.vendor_id = " . $vendor;
}

if ($filterStatus != "all") {
    $condition .= " AND vendor_consignment_returns.status='" . $filterStatus . "'";
}

if ($date != "") {
    $condition .= " AND vendor_consignment_returns.date='" . $date . "'";
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
        } else if ($aColumns[$i] == 'vendor_consignment_returns.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "vendor_consignment_returns.status") {
            switch ($aRow[$i]) {
                case 0:
                    $row[] = 'Void';
                    break;
                case 1:
                    $row[] = 'Issued';
                    break;
                case 2:
                    $row[] = 'Fulfilled';
                    break;
            }
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="#" class="btnViewVendorConsignmentReturn" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowReprint && $aRow[5] > 0 ? '<a href="#" class="btnReprintInvoice" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Reprint Invoice" onmouseover="Tip(\'' . ACTION_REPRINT_INVOICE . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '').
            ($allowEdit && $aRow[5] == 1 ? '<a href="#" class="btnEditVendorConsignmentReturn" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowReceive && $aRow[5] == 1 ? '<a href="#" class="btnVendorConsignmentReturnReceive" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PICK . '\')" src="' . $this->webroot . 'img/button/hand.png" /></a> ' : '') .
            ($allowVoid && $aRow[5] == 1 ? '<a href="#" class="btnVoidVendorConsignmentReturn" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>