<?php
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$user['User']['user_type'] = 1;
$isPos = false;
//$status = 1;
$saleOrderDate = '';

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("purchase_returns.id",
    "purchase_returns.order_date",
    "purchase_returns.pr_code",
    '(SELECT name FROM location_groups WHERE id=purchase_returns.location_group_id)',
    "vendors.name",
    "(purchase_returns.total_amount + purchase_returns.total_vat)",
    "purchase_returns.balance",
    "CASE purchase_returns.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN 3 THEN 'Partial' END",
    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "purchase_returns.order_date";

/* DB table to use */
$sTable = " purchase_returns
            LEFT JOIN vendors ON vendors.id = purchase_returns.vendor_id 
            INNER JOIN currency_centers ON currency_centers.id = purchase_returns.currency_center_id ";

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
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }

    $sOrder = str_replace("purchase_returns.order_date asc", "purchase_returns.order_date asc, purchase_returns.id asc", $sOrder);
    $sOrder = str_replace("purchase_returns.order_date desc", "purchase_returns.order_date desc, purchase_returns.id desc", $sOrder);
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
$condition = " purchase_returns.status >= 0 AND purchase_returns.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") AND purchase_returns.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].") AND purchase_returns.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id='" . $user['User']['id'] . "' GROUP BY location_group_id)";
if($allowViewByUser){
    $condition .= " AND purchase_returns.created_by =".$user['User']['id'];
}
if (@$balance == 1) {
    $condition .= " AND purchase_returns.balance > 0";
} else if (@$balance == 2) {
    $condition .= " AND purchase_returns.balance <= 0";
}

if ($filterStatus != "all") {
    $condition .= " AND purchase_returns.status='" . $filterStatus . "'";
}

if ($vendor != "all") {
    $condition .= " AND purchase_returns.vendor_id='" . $vendor . "'";
}

if ($date != "") {
    $condition .= " AND purchase_returns.order_date='" . $date . "'";
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
    $balance = 0;
    $total_amount = 0;
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 'purchase_returns.order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
                $saleOrderDate = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "vendors.name") {
            if (trim($aRow[$i]) == '') {
                $row[] = 'General Vendor';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'purchase_returns.pr_code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == "CASE purchase_returns.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN 3 THEN 'Partial' END") {
            $row[] = $aRow[$i];
            if (trim($aRow[$i]) == "Void") {
                $status = 0;
            } else if (trim($aRow[$i]) == "Issued") {
                $status = 1;
            } else if (trim($aRow[$i]) == "Fulfilled") {
                $status = 2;
            } else if (trim($aRow[$i]) == "Partial") {
                $status = 3;
            }
        } else if ($aColumns[$i] == '(purchase_returns.total_amount + purchase_returns.total_vat)') {
            $row[] = number_format($aRow[$i], $costDecimal)." ".$aRow[8];
            $total_amount = $aRow[$i];
        } else if ($aColumns[$i] == 'purchase_returns.balance') {
            $row[] = number_format($aRow[$i], $costDecimal)." ".$aRow[8];
            $balance = $aRow[$i];
        } else if ($i == 8) {
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
            if ($aColumns[$i] == "purchase_returns.balance") {
                if ($aRow[$i] <= 0) {
                    $allowAging = false;
                }
            }
        }
    }
    $queryHasReceipt  = mysql_query("SELECT id FROM purchase_return_receipts WHERE purchase_return_id=" . $aRow[0] . " AND is_void = 0");
    $queryHasApplyInv = mysql_query("SELECT id FROM invoice_pbc_with_pbs WHERE purchase_return_id=" . $aRow[0] . " AND status > 0");
    $row[] =
            ($allowView ? '<a href="' . $isPos . '" class="btnViewPurchaseReturn" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPrint && $status > 0 ? '<a href="' . $isPos . '" class="btnPrintInvoicePurchaseReturn" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT_BILL_RETURN . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .            
            ($allowEdit && $status == 2 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv) ? '<a href="' . $isPos . '" class="btnEditPurchaseReturn" rel="' . $aRow[0] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowAging && $status > 0 ? '<a href="' . $isPos . '" class="btnAgingPurchaseReturn" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Aging" onmouseover="Tip(\'' . TABLE_PAY . '\')" src="' . $this->webroot . 'img/button/aging.png" /></a> ' : '') .
            ($allowVoid && $status == 2 && ($aRow[5] == $aRow[6]) && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv) ? '<a href="' . $isPos . '" class="btnVoidPurchaseReturn" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'Void\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>

