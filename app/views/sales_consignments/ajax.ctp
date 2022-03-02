<?php
header("Content-type: text/plain");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowReprint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$allowPick = checkAccess($user['User']['id'], $this->params['controller'], 'pick');

$isPos = false;
$status = 2;
$saleOrderDate = '';

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("sales_orders.id", 
    "sales_orders.order_date",
    "sales_orders.so_code",
    "sales_orders.consignment_code",
    "CONCAT_WS(' - ',customers.customer_code,CONCAT(customers.name_kh,' (',customers.name,')'))",
    "sales_orders.total_amount + IFNULL(sales_orders.total_vat,0) - IFNULL(sales_orders.discount,0)",
    "sales_orders.balance",
    "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END",
    "sales_orders.is_pos",
    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.order_date";

/* DB table to use */
$sTable = " sales_orders 
            LEFT JOIN customers ON customers.id = sales_orders.customer_id
            INNER JOIN currency_centers ON currency_centers.id = sales_orders.currency_center_id";

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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "sales_orders.id"){
                $sOrder .= "sales_orders.created " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
$condition = "is_pos =  2 AND (sales_orders.status >= 0 OR sales_orders.status = -2)";

if($allowViewByUser){
    $condition .= " AND sales_orders.created_by =".$user['User']['id'];
}

if (@$balance == 1) {
    $condition .= " AND sales_orders.balance > 0";
} else if (@$balance == 2) {
    $condition .= " AND sales_orders.balance <= 0";
}

if ($customer != "all") {
    $condition .= " AND customer_id = " . $customer;
}

if ($filterStatus != "all") {
    $condition .= " AND sales_orders.status='" . $filterStatus . "'";
}

if ($company != "all") {
    $condition .= " AND sales_orders.company_id='" . $company . "'";
}else{
    $condition .= " AND sales_orders.company_id IN (SELECT company_id FROM user_companies WHERE user_id='" . $user['User']['id'] . "' GROUP BY company_id)";
}

if ($date != "") {
    $condition .= " AND sales_orders.order_date='" . $date . "'";
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
        } else if ($aColumns[$i] == 'sales_orders.order_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i]);
                $saleOrderDate = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "CONCAT_WS(' ', customers.customer_code, '-', customers.firstname, customers.lastname)") {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'sales_orders.so_code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END") {
            $row[] = $aRow[$i];
            if (trim($aRow[$i]) == "Void") {
                $status = 0;
            } else if (trim($aRow[$i]) == "Issued") {
                $status = 1;
            } else if (trim($aRow[$i]) == "Fulfilled") {
                $status = 2;
            }else if(trim($aRow[$i]) == "Pending"){
                $status = -2;
            }
        } else if ($aColumns[$i] == 'sales_orders.is_pos') { // Check Is POS
            $row[] = "Consignment";
        } else if ($aColumns[$i] == 'sales_orders.total_amount + IFNULL(sales_orders.total_vat,0) - IFNULL(sales_orders.discount,0)') {
            $row[] = $aRow[9]." ".number_format($aRow[$i],2);
        } else if ($aColumns[$i] == 'sales_orders.balance') {
            $row[] = $aRow[9]." ".number_format($aRow[$i],2);
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
            if ($aColumns[$i] == "sales_orders.balance") {
                if ($aRow[$i] <= 0) {
                    $allowAging = false;
                }
            }
        }
    }
    $queryHasReceipt = mysql_query("SELECT id FROM sales_order_receipts WHERE sales_order_id=" . $aRow[0] . " AND is_void = 0");
    $queryHasCreditMemo = mysql_query("SELECT id FROM credit_memo_with_sales WHERE status > 0 AND sales_order_id=" . $aRow[0]);
    $invoiceStatus = 0;
    if($isPos == 1){
        $invoiceStatus = 1;
    } else {
        if($status == 1){
            $invoiceStatus = 1;
        }
    }
    $row[] =
            ($allowView ? '<a href="' . $isPos . '" class="btnViewSalesConsignment" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowReprint && $status != 0 ? '<a href="' . $isPos . '" class="btnReprintInvoice" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Reprint Invoice" onmouseover="Tip(\'' . ACTION_REPRINT_INVOICE . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '').
            ($allowEdit && $status == 1 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo)) ? '<a href="' . $isPos . '" class="btnEditSalesConsignment" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowPick && $status == 1 ? '<a href="' . $isPos . '" class="btnSalesConsignmentPick" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PICK . '\')" src="' . $this->webroot . 'img/button/hand.png" /></a> ' : '') .
            ($allowAging && $status > 0 && $isPos != 1 ? '<a href="' . $isPos . '" class="btnAgingSalesConsignment" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Aging" onmouseover="Tip(\'' . TABLE_PAY . '\')" src="' . $this->webroot . 'img/button/aging.png" /></a> ' : '') .
            ($allowVoid && $invoiceStatus == 1 && $status > 0 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo)) ? '<a href="' . $isPos . '" class="btnVoidSalesConsignment" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>