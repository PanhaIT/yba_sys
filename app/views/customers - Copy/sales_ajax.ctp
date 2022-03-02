<?php
header("Content-type: text/plain");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$priceDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
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
$aColumns = array("sales_orders.id", 
    "sales_orders.type",
    "sales_orders.date",
    "sales_orders.code",
    "sales_orders.total_amount",
    "sales_orders.total_deposit",
    "sales_orders.balance",
    "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END",
    "sales_orders.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.id";

/* DB table to use */
$sTable = " (SELECT 
            sales.id AS id,
            IF(sales.is_pos=1,'POS','Invoice') AS type,
            sales.order_date AS date,
            sales.so_code AS code,
            (IFNULL(sales.total_amount, 0) + IFNULL(sales.total_vat,0) - IFNULL(sales.discount,0)) AS total_amount,
            sales.total_deposit,
            sales.balance,
            sales.status,
            currency_centers.symbol
            FROM sales_orders AS sales
            INNER JOIN currency_centers ON currency_centers.id = sales.currency_center_id
            WHERE sales.customer_id = '".$customerId."' AND sales.status >= 0
            UNION ALL
            SELECT 
            returns.id AS id,
            'Sales Return' AS type,
            returns.order_date AS date,
            returns.cm_code AS code,
            (IFNULL(returns.total_amount, 0) + IFNULL(returns.mark_up,0) + IFNULL(returns.total_vat,0) - IFNULL(returns.discount,0)) AS total_amount,
            '0' AS total_deposit,
            returns.balance,
            returns.status,
            currency_centers.symbol
            FROM credit_memos AS returns
            INNER JOIN currency_centers ON currency_centers.id = returns.currency_center_id
            WHERE returns.customer_id = '".$customerId."' AND returns.status >= 0
            UNION ALL
            SELECT 
            receive_payments.id AS id,
            'Receive Payment' AS type,
            receive_payments.date,
            receive_payments.reference AS code,
            receive_payment_details.paid AS total_amount,
            '0' AS total_deposit,
            '0' AS balance,
            '2' AS status,
            currency_centers.symbol
            FROM receive_payment_details
            INNER JOIN receive_payments ON receive_payments.id = receive_payment_details.receive_payment_id
            INNER JOIN users ON users.id = receive_payments.created_by
            INNER JOIN currency_centers ON currency_centers.id = (SELECT currency_center_id FROM companies WHERE id = receive_payments.company_id LIMIT 1)
            WHERE receive_payments.customer_id = '".$customerId."' AND receive_payment_details.is_void = 0 AND receive_payments.is_active = 1
            ORDER BY date, balance) AS sales_orders";

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
                $sOrder .= "sales_orders.date " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
        } else if ($aColumns[$i] == 'sales_orders.date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'sales_orders.code') {
            $checkBoxPrint = "";
            if($aRow[6] > 0){
                if($aRow[1] == 'Invoice'){
                    $checkBoxPrint = '<input type="checkbox" class="btnCheckInvoice" rel="'.$aRow[0].'">';
                }
            }
            $row[] = $checkBoxPrint.' <a href="#" class="btnPrintViewCustomerRecord" rel="'.$aRow[0].'" trans_type="'.$aRow[1].'">'.$aRow[$i].'</a>';
        } else if ($aColumns[$i] == "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' WHEN -2 THEN 'Pending' END") {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'sales_orders.type') { // Check Is POS
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'sales_orders.total_amount' || $aColumns[$i] == 'sales_orders.balance' || $aColumns[$i] == 'sales_orders.total_deposit') {
            $row[] = $aRow[8]." ".number_format($aRow[$i], $priceDecimal);
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