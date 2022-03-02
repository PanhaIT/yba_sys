<?php

// Function
include('includes/function.php');

/**
 * export to excel
 */
$filename = "public/report/total_sales_" . $user['User']['id'] . ".csv";
$fp = fopen($filename, "wb");
$excelContent  = MENU_REPORT_INVOICE . "\n";
$excelContent .= "From: " . dateConvert(str_replace("|||", "/", $data[1])) . " To: " . dateConvert(str_replace("|||", "/", $data[2])) . "\n\n";
$excelContent .= TABLE_NO . "\t" . TABLE_TYPE . "\t" . TABLE_DATE . "\t" . TABLE_CODE . "\t" . PRICING_RULE_CUSTOMER . "\t" . TABLE_TOTAL_AMOUNT . "\t" . GENERAL_BALANCE . "\t" . GENERAL_AGING . "\t" . TABLE_STATUS; 

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
$conSales  = '';
$comCredit = '';
$conMod = '';
if ($data[1] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(module.order_date)';
}
if ($data[2] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(module.order_date)';
}
$conMod != '' ? $conMod .= ' AND ' : '';
if ($data[3] == '') {
    $conMod .= 'module.status >= 0';
} else {
    $conMod .= 'module.status=' . $data[3];
}
if ($data[4] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.company_id=' . $data[4];
}else{
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.branch_id=' . $data[5];
}else{
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.location_group_id=' . $data[6];
}else{
    // Sales
    $conMod != '' ? $conSales .= ' AND ' : '';
    $conSales .= '((module.is_pos IN (0, 1) AND module.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')) OR module.is_pos = 2)';
    // Credit Memo
    $conMod != '' ? $comCredit .= ' AND ' : '';
    $comCredit .= 'module.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
}
if ($data[7] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id=' . $data[7] . ')';
}
if ($data[8] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.customer_id=' . $data[8];
}
if ($data[9] != '') {
    $conMod != '' ? $conMod .= ' AND ' : '';
    $conMod .= 'module.created_by=' . $data[9];
}
/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("id",
    "mod_type",
    "order_date",
    "code",
    "customer_name",
    "total_amount",
    "balance",
    "aging",
    "status",
    "is_pos",
    "symbol",
    "time");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "order_date";

/* DB table to use */
$sTable = " (SELECT module.id AS id, 
            'Invoice' AS mod_type, 
            module.order_date AS order_date, 
            TIME(module.created) AS time,
            module.so_code AS code, 
            CONCAT_WS(' - ', customers.customer_code, customers.name) AS customer_name,
            module.total_amount-IFNULL(module.discount,0)+IFNULL(module.total_vat,0)+IFNULL(module.bank_charge_amount,0) AS total_amount,
            module.balance AS balance,
            IF(module.balance>0 AND DATEDIFF(now(),due_date)>0,DATEDIFF(now(),module.due_date),'.') AS aging,
            CASE module.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END AS status,
            module.is_pos AS is_pos,
            currency_centers.symbol AS symbol
            FROM sales_orders AS module
            LEFT JOIN customers ON customers.id = module.customer_id
            INNER JOIN currency_centers ON currency_centers.id = module.currency_center_id
            WHERE ".$conMod.$conSales."
            UNION ALL
            SELECT module.id AS id, 
            'CM' AS mod_type, 
            module.order_date AS order_date, 
            TIME(module.created) AS time, 
            module.cm_code AS code, 
            CONCAT_WS(' - ', customers.customer_code, customers.name) AS customer_name,
            ((module.total_amount-IFNULL(module.discount,0)+IFNULL(module.total_vat,0)) * -1) AS total_amount,
            (module.balance * -1) AS balance,
            IF(module.balance>0 AND DATEDIFF(now(),due_date)>0,DATEDIFF(now(),module.due_date),'.') AS aging,
            CASE module.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END AS status,
            4 AS is_pos,
            currency_centers.symbol AS symbol
            FROM credit_memos AS module
            LEFT JOIN customers ON customers.id = module.customer_id
            INNER JOIN currency_centers ON currency_centers.id = module.currency_center_id
            WHERE ".$conMod.$comCredit."
            ORDER BY order_date) AS sales";

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
    for ($i = 0; $i < (count($aColumns)-3); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns)-3); $i++) {
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
$condition = '1';

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
        } else if ($aColumns[$i] == 'order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i])." ".$aRow[11];
                $excelContent .= "\t".dateShort($aRow[$i])." ".$aRow[11];
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == "customer_name") {
            if (trim($aRow[$i]) == '') {
                $row[] = 'General Customer';
                $excelContent .= "\t General Customer";
            } else {
                $row[] = $aRow[$i];
                $excelContent .= "\t".$aRow[$i];
            }
        } else if ($aColumns[$i] == 'code') {
            $row[] = '<a href="" class="btnPrintTotalSalesReport" total="'.$aRow[5].'" balance="'.$aRow[6].'" rel="' . $aRow[0] . '" is_pos="' . $aRow[9] . '">' . $aRow[$i] . '</a>';
            $excelContent .= "\t".$aRow[$i];
        } else if ($aColumns[$i] == "status") {
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        } else if ($i == 5 || $i == 6) {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[10];
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'is_pos' && $aColumns[$i] == 'symbol' && $aColumns[$i] == 'time') {
            
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
            if($aColumns[$i] == 'is_pos' || $aColumns[$i] == 'symbol' || $aColumns[$i] == 'time'){
                $excelContent .= "\t";
            }else{
                $excelContent .= "\t".$aRow[$i];
            }
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>