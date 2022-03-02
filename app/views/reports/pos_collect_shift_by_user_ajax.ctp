<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('CONCAT_WS("|*|", shift_collects.id, (SELECT symbol FROM currency_centers WHERE id = branches.currency_center_id))', 
                  'shift_collects.code', 
                  'shift_collects.date', 
                  '(SELECT name FROM employees WHERE id = shift_collects.employee_id)', 
                  'shift_collects.total_cash_collect + (shift_collects.total_cash_collect_other / (SELECT rate_to_sell FROM exchange_rates WHERE id = (SELECT exchange_rate_id FROM shifts WHERE shift_collect_id = id LIMIT 01)))',
                  'shift_collects.total_register + (shift_collects.total_register_other / (SELECT rate_to_sell FROM exchange_rates WHERE id = (SELECT exchange_rate_id FROM shifts WHERE shift_collect_id = id LIMIT 01)))',
                  'shift_collects.total_adj + (shift_collects.total_adj_other / (SELECT rate_to_sell FROM exchange_rates WHERE id = (SELECT exchange_rate_id FROM shifts WHERE shift_collect_id = id LIMIT 01)))',
                  'shift_collects.total_sales',
                  'shift_collects.total_spread',
                  '(SELECT CONCAT(first_name, " ",last_name) FROM users WHERE id = shift_collects.created_by)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "shift_collects.id";

/* DB table to use */
$sTable = "shift_collects INNER JOIN branches ON branches.id = shift_collects.branch_id";

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

    $sOrder = str_replace("date asc", "date asc, id asc", $sOrder);
    $sOrder = str_replace("date desc", "date desc, id desc", $sOrder);
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
    for ($i = 0; $i < (count($aColumns) - 1); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 1); $i++) {
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
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(shift_collects.date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(shift_collects.date)';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shift_collects.company_id=' . $data[3];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shift_collects.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shift_collects.branch_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shift_collects.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shift_collects.created_by=' . $data[5];
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
            $resultVal = explode("|*|", $aRow[0]);  
            $row[] = ++$index;
        } else if($i == 2){
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00 00:00:00'){
                $row[] = dateShort($aRow[$i], "d/m/y H:i:s");
            } else {
                $row[] = '';
            }
        } else if($i == 4){
            $row[] = '<span class="btnShiftCollect" symbolMain ="'.$resultVal[1].'" totalActure="'.$aRow[4].'" totalRegis="'.$aRow[5].'" totalAdj="'.$aRow[6].'" totalSales="'.$aRow[7].'" totalSpread="'.$aRow[8].'">' .  $resultVal[1]." ".number_format($aRow[$i], 2) . '</a>';
        } else if($i == 5 || $i == 6 || $aColumns[$i] == 'shift_collects.total_sales' || $aColumns[$i] == 'shift_collects.total_spread'){            
            $row[] = $resultVal[1]." ".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);

?>