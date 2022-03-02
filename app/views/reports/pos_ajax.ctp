<?php
// Function
include('includes/function.php');
/**
 * Export to Excel
 */
$filename="public/report/invoice_pos" . $user['User']['id'] . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$excelContent .= MENU_REPORT_POS."\n";
if($data[1]!='') {
    $excelContent .= REPORT_FROM.': '.str_replace("|||", "/", $data[1]);
}
if($data[2]!='') {
    $excelContent .= ' '.REPORT_TO.': '.str_replace("|||", "/", $data[1]);
}
$excelContent .= "\n".TABLE_NO."\t".TABLE_INVOICE_DATE."\t".TABLE_INVOICE_CODE."\tPayment Method\t".TABLE_TOTAL_AMOUNT."\t".TABLE_BANK_CHARGE."\t".GENERAL_BALANCE."\t".GENERAL_PAID."\t".GENERAL_PAID."\t".TABLE_CHANGE."\t".TABLE_CHANGE."\t".TABLE_CREATED_BY;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("sales_orders.id",
    "sales_orders.created",
    "sales_orders.so_code",
    "pos_pay_methods.name",
    "sales_orders.total_amount-IFNULL(sales_orders.discount,0)+IFNULL(sales_orders.total_vat,0)",
    "IFNULL(sales_orders.`bank_charge_amount`,0)",
    "sales_orders.`balance`",
    "sales_order_receipts.`amount_us`",
    "sales_order_receipts.`amount_other`",
    "IFNULL(sales_order_receipts.`change`,0)",
    "IFNULL(sales_order_receipts.`change_other`,0)",
    "CONCAT_WS(users.first_name, ' ',users.last_name)",
    "currency_centers.symbol",
    "(SELECT symbol FROM currency_centers WHERE id = sales_order_receipts.currency_center_id)");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.order_date";

/* DB table to use */
$sTable = " sales_orders
            LEFT JOIN sales_order_receipts ON sales_order_receipts.sales_order_id = sales_orders.id
            LEFT JOIN pos_pay_methods ON pos_pay_methods.id = sales_orders.pos_pay_method_id
            INNER JOIN currency_centers ON currency_centers.id = sales_orders.currency_center_id
            INNER JOIN users ON users.id = sales_orders.created_by
            LEFT JOIN customers ON customers.id = sales_orders.customer_id ";

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

    $sOrder = str_replace("sales_orders.order_date asc", "sales_orders.order_date asc, sales_orders.id asc", $sOrder);
    $sOrder = str_replace("sales_orders.order_date desc", "sales_orders.order_date desc, sales_orders.id desc", $sOrder);
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
$condition = 'sales_orders.is_pos=1';
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(sales_orders.order_date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(sales_orders.order_date)';
}
$condition != '' ? $condition .= ' AND ' : '';
if ($data[3] == '') {
    $condition .= 'sales_orders.status > -1';
} else {
    $condition .= 'sales_orders.status=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.company_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.branch_id=' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.location_group_id=' . $data[6];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.created_by=' . $data[7];
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.pos_pay_method_id=' . $data[8];
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
            $excelContent .= "\n".$index;
        } else if ($aColumns[$i] == 'sales_orders.created') {
            if ($aRow[$i] != '0000-00-00 00:00:00') {
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
                $excelContent .= "\t".dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($i == 2) {
            $row[] = '<a href="" class="btnPrintInvoicePos" rel="' . $aRow[0] . '">' . $aRow[$i] . '</a>';
            $excelContent .= "\t".$aRow[$i];
        } else if ($i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 9) {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[12];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[12];
        } else if ($i == 8 || $i == 10) {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[13];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[13];
        } else if ($i == 12 || $i == 13) {    
            
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

echo json_encode($output);
?>