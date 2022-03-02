<?php

// Function
include('includes/function.php');

/**
 * export to excel
 */
$filename = "public/report/purchase_bill_" . $user['User']['id'] . ".csv";
$fp = fopen($filename, "wb");
$excelContent  = MENU_PURCHASE_INVOICE . "\n";
$excelContent .= "From: " . dateConvert(str_replace("|||", "/", $data[1])) . " To: " . dateConvert(str_replace("|||", "/", $data[2])) . "\n\n";
$excelContent .= TABLE_NO . "\t" . TABLE_DATE . "\t" . TABLE_PB_NUMBER . "\t" . TABLE_LOCATION_GROUP . "\t" . TABLE_LOCATION . "\t" . TABLE_VENDOR . "\t" . GENERAL_AMOUNT . "\t" . GENERAL_BALANCE. "\t" . TABLE_STATUS; 

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('pr.id', 
    'pr.order_date',
    "pr.po_code",
    'location_groups.name',
    'loc.name',
    'v.name',
    'pr.total_amount + IFNULL(pr.total_vat,0)',
    'pr.balance',
    'pr.status',
    "currency_centers.symbol",
    "TIME(pr.created)");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "pr.id";

/* DB table to use */
$sTable = " purchase_orders as pr
            INNER JOIN `location_groups` ON pr.location_group_id = location_groups.id
            INNER JOIN `locations` as loc ON pr.location_id = loc.id
            INNER JOIN `vendors` as v ON pr.vendor_id = v.id
            INNER JOIN currency_centers ON currency_centers.id = pr.currency_center_id";

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
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }

    $sOrder = str_replace("pr.order_date asc", "pr.order_date asc, pr.id asc", $sOrder);
    $sOrder = str_replace("pr.order_date desc", "pr.order_date desc, pr.id desc", $sOrder);
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
    for ($i = 0; $i < count($aColumns) - 2; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 2; $i++) {
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
$condition = '';
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(order_date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(order_date)';
}
$condition != '' ? $condition .= ' AND ' : '';
if ($data[3] == '') {
    $condition .= 'status > -1';
} else {
    $condition .= 'status=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id = ' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'pr.location_group_id=' . $data[6];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'pr.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'vendor_id=' . $data[7];
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'pr.created_by=' . $data[8];
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
        } else if ($aColumns[$i] == 'pr.order_date' || $aColumns[$i] == 'pr.due_date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i])." ".$aRow[10];
                $excelContent .= "\t".dateShort($aRow[$i])." ".$aRow[10];
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == "pr.po_code") {
            $row[] = '<a href="" class="btnPrintPurchaseReport" rel="'.$aRow[0].'" total="'.$aRow[6].'" balance="'.$aRow[7].'">' . $aRow[$i] . '</a>';
            $excelContent .= "\t".$aRow[$i];
        } else if ($i == 6 || $i == 7) {
            $row[] = number_format($aRow[$i], 2)." ".$aRow[9];
            $excelContent .= "\t".number_format($aRow[$i], 2)." ".$aRow[9];
        } else if ($aColumns[$i] == 'pr.status') {
            switch ($aRow[$i]) {
                case 0:
                    $row[] = 'Void';
                    $excelContent .= "\tVoid";
                    break;
                case 1:
                    $row[] = 'Issued';
                    $excelContent .= "\tIssued";
                    break;
                case 2:
                    $row[] = 'Partial';
                    $excelContent .= "\tPartial";
                    break;
                case 3:
                    $row[] = 'Fulfilled';
                    $excelContent .= "\tFulfilled";
                    break;
            }
        } else if ($aColumns[$i] == 'currency_centers.symbol' || $aColumns[$i] == 'TIME(pr.created)') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>