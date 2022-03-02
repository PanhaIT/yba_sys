<?php

// Authentication
$this->element('check_access');
//$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('shifts.id', 'shifts.shift_code', 'shifts.date_start', 'shifts.date_end', 'CONCAT(first_name, " ",last_name)', 'shifts.total_register', 'shifts.total_register_other', 'IFNULL(SUM(shift_adjusts.total_adj), 0)', 'IFNULL(SUM(shift_adjusts.total_adj_other), 0)', 'shifts.total_acture', 'shifts.total_acture_other', 'shifts.company_id', 'shifts.status', 'exchange_rates.currency_center_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "shifts.id";

/* DB table to use */
$sTable = "shifts INNER JOIN users ON users.id = shifts.created_by LEFT JOIN shift_adjusts ON shift_adjusts.shift_id = shifts.id INNER JOIN exchange_rates ON exchange_rates.id = shifts.exchange_rate_id";

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
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
                                " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
$condition = "shifts.status=2";
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
$groupBy = "GROUP BY shifts.id";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $groupBy
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
    $mainSymbol  = '';
    $otherSymbol = '';
    $currencySym = mysql_query("SELECT id, symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE id = ".$aRow[12].") OR id = ".$aRow[13]);
    while($rowSym = mysql_fetch_array($currencySym)){
        if($rowSym['id'] == $aRow[13]){
            $otherSymbol = $rowSym['symbol'];
        } else {
            $mainSymbol  = $rowSym['symbol'];
        }
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($i == 5 || $i == 7 || $i == 9) {
            $row[] = $mainSymbol." ".number_format($aRow[$i], 2);
        } else if ($i == 6 || $i == 8|| $i == 10) {
            $row[] = $otherSymbol." ".number_format($aRow[$i], 0);
        } else if ($aColumns[$i] == "shifts.status") {
            if (trim($aRow[$i]) == 1) {
                $status = "Open";
            } else if (trim($aRow[$i]) == 2) {
                $status = "Closed";
            }
            $row[] = $status;
        } else if ($aColumns[$i] == 'shifts.company_id' || $aColumns[$i] == 'exchange_rates.currency_center_id') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
//    $row[] =
//            ($allowView ? '<a href="" class="btnViewPosition" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
//            ($allowEdit ? '<a href="" class="btnEditPosition" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
//            ($allowDelete && $aRow[0] > 2 ? '<a href="" class="btnDeletePosition" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>