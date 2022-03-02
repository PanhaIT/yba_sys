<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('CONCAT_WS("|*|", shifts.id, IFNULL(currency_centers.symbol, ""), branches.currency_center_id)', 
                  'shifts.shift_code', 
                  'shifts.date_start', 
                  'shifts.date_end', 
                  '(SELECT CONCAT(first_name, " ",last_name) FROM users WHERE id = shifts.created_by)', 
                  'shifts.total_register', 
                  'shifts.total_register_other', 
                  '(SELECT SUM(total_adj) FROM shift_adjusts WHERE shift_id = shifts.id GROUP BY shift_id)', 
                  '(SELECT SUM(total_adj_other) FROM shift_adjusts WHERE shift_id = shifts.id GROUP BY shift_id)', 
                  'shifts.total_acture', 
                  'shifts.total_acture_other',
                  'shifts.status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "shifts.id";

/* DB table to use */
$sTable = "shifts
          INNER JOIN branches ON branches.id = shifts.branch_id
          LEFT JOIN exchange_rates ON exchange_rates.id = shifts.exchange_rate_id
          LEFT JOIN currency_centers ON currency_centers.id = exchange_rates.currency_center_id";

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
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(shifts.date_start)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(shifts.date_end)';
}
$condition != '' ? $condition .= ' AND ' : '';
if ($data[3] == '') {
    $condition .= 'shifts.status > 0';
} else {
    $condition .= 'shifts.status=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shifts.company_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shifts.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shifts.branch_id=' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shifts.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'shifts.created_by=' . $data[6];
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
    $resultVal = explode("|*|", $aRow[0]);  
    $queryCurrency = mysql_query("SELECT symbol FROM currency_centers WHERE id = '".$resultVal[2]."'");
    $mainCurrency  = mysql_fetch_array($queryCurrency);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if($i == 2 || $i == 3){
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00 00:00:00'){
                $row[] = dateShort($aRow[$i], "d/m/y H:i:s");
            } else {
                $row[] = '';
            }
        } else if($aColumns[$i] == 'shifts.total_register'){
            $row[] = '<a href="#" class="btnShiftDetail" totalRegis="'.$aRow[5].'" totalRegisOther="'.$aRow[6].'" totalAdj="'.$aRow[7].'" totalAdjOther="'.$aRow[8].'" totalActure="'.$aRow[9].'" totalActureOther="'.$aRow[10].'">' .  $mainCurrency[0]." ".number_format($aRow[$i], 2) . '</a>';
        } else if($aColumns[$i] == '(SELECT SUM(total_adj) FROM shift_adjusts WHERE shift_id = shifts.id GROUP BY shift_id)' || $aColumns[$i] == 'shifts.total_acture'){
            $row[] = $mainCurrency[0]." ".number_format($aRow[$i], 2);
        } else if($aColumns[$i] == 'shifts.total_register_other' || $aColumns[$i] == '(SELECT SUM(total_adj_other) FROM shift_adjusts WHERE shift_id = shifts.id GROUP BY shift_id)' || $aColumns[$i] == 'shifts.total_acture_other'){
            $row[] = $resultVal[1]." ".number_format($aRow[$i], 0);
        } else if ($aColumns[$i] == 'shifts.status') {
            switch ($aRow[$i]) {
                case 1:
                    $row[] = TABLE_STATUS_OPEN;
                    break;
                case 2:
                    $row[] = TABLE_STATUS_CLOSE;
                    break;
                case 3:
                    $row[] = TABLE_STATUS_COLLECT;
                    break;
            }
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>