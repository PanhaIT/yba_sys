<?php
$priceDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
//$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
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
$aColumns = array("credit_memos.id", 
    "credit_memos.order_date",
    "credit_memos.cm_code",
    "credit_memos.invoice_code",
    "(SELECT name FROM location_groups WHERE id = credit_memos.location_group_id)",
    "CONCAT_WS(' - ',customers.customer_code,CONCAT(customers.name_kh,' (',customers.name,')'))",
    "credit_memos.total_amount-IFNULL(credit_memos.discount,0)+IFNULL(credit_memos.mark_up,0)+IFNULL(credit_memos.total_vat,0)",
    "credit_memos.balance",
    "CASE credit_memos.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END",
    "currencies.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "credit_memos.order_date";

/* DB table to use */
$sTable = " credit_memos
            LEFT JOIN customers ON customers.id = credit_memos.customer_id INNER JOIN currencies ON currencies.id = credit_memos.currency_id";

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

    $sOrder = str_replace("credit_memos.order_date asc", "credit_memos.order_date asc, credit_memos.id asc", $sOrder);
    $sOrder = str_replace("credit_memos.order_date desc", "credit_memos.order_date desc, credit_memos.id desc", $sOrder);
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
$condition = "credit_memos.status >= 0 AND credit_memos.company_id IN (SELECT company_id FROM user_companies WHERE user_id='" . $user['User']['id'] . "' GROUP BY company_id) AND credit_memos.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id']." GROUP BY location_group_id) AND credit_memos.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']." GROUP BY branch_id)";
if($allowViewByUser){
    $condition .= " AND credit_memos.created_by =".$user['User']['id'];
}
if ($balance == 1) {
    $condition .= " AND credit_memos.balance > 0";
} else if ($balance == 2) {
    $condition .= " AND credit_memos.balance <= 0";
}

if ($customer != "all") {
    $condition .= " AND credit_memos.customer_id = ".$customer;
}

if ($filterStatus != "all") {
    $condition .= " AND credit_memos.status='" . $filterStatus . "'";
}

if ($date != "") {
    $condition .= " AND credit_memos.order_date='" . $date . "'";
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
        } else if ($aColumns[$i] == 'credit_memos.order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
                $saleOrderDate = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "credit_memos.cm_code") {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == "CASE credit_memos.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END") {
            $row[] = $aRow[$i];
            if (trim($aRow[$i]) == "Void") {
                $status = 0;
            } else if (trim($aRow[$i]) == "Issued") {
                $status = 1;
            } else if (trim($aRow[$i]) == "Fulfilled") {
                $status = 2;
            }
        } else if ($i == 6 || $i == 7) {
            $row[] = $aRow[9]." ".number_format($aRow[$i], $priceDecimal);
        } else if ($i == 9) {
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $queryHasReceipt  = mysql_query("SELECT id FROM credit_memo_receipts WHERE credit_memo_id=" . $aRow[0] . " AND is_void = 0");
    $queryHasApplyInv = mysql_query("SELECT id FROM credit_memo_with_sales WHERE credit_memo_id=" . $aRow[0] . " AND status > 0");
    $row[] =
            ($allowView ? '<a href="' . $isPos . '" class="btnViewCreditMemo" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPrint && $status > 0 ? '<a href="' . $isPos . '" class="btnPrintInvoiceCreditMemo" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT_CREDIT_MEMO . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .
            ($allowEdit && $status == 2 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv) ? '<a href="' . $isPos . '" class="btnEditCreditMemo" rel="' . $aRow[0] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
//            ($allowReceive && $status == 1 ? '<a href="' . $isPos . '" class="btnReceiveCreditMemo" rel="' . $aRow[0] . '"><img alt="Receive" onmouseover="Tip(\'' . ACTION_RECEIVE . '\')" src="' . $this->webroot . 'img/button/receive.png" /></a> ' : '') .
            ($allowAging && $status > 0 ? '<a href="' . $isPos . '" class="btnAgingCreditMemo" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Aging" onmouseover="Tip(\'' . TABLE_PAY . '\')" src="' . $this->webroot . 'img/button/aging.png" /></a> ' : '') .
            ($allowVoid && $status == 2 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv) ? '<a href="' . $isPos . '" class="btnVoidCreditMemo" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'Void\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>