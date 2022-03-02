<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowApply  = checkAccess($user['User']['id'], $this->params['controller'], 'applyPos');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('branch_currencies.id', 'branches.name', '(SELECT CONCAT(name," (",symbol," )") FROM currencies WHERE id = branches.currency_id)', 'CONCAT(currencies.name," (",currencies.symbol," )")', 'branch_currencies.is_pos_default');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "branch_currencies.id";

/* DB table to use */
$sTable = "branch_currencies INNER JOIN branches ON branches.id = branch_currencies.branch_id INNER JOIN currencies ON currencies.id = branch_currencies.currency_id";

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
$condition = "branch_currencies.is_active=1 AND branch_currencies.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
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
        } else if ($aColumns[$i] == 'branch_currencies.is_pos_default') {
            $applyTo = '';
            if($aRow[$i] == 1){
                $applyTo = 'POS';
            }
            $row[] = $applyTo;
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $sqlQuote = mysql_query("SELECT id FROM quotations WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlOrder = mysql_query("SELECT id FROM orders WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlSales = mysql_query("SELECT id FROM sales_orders WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlCM    = mysql_query("SELECT id FROM credit_memos WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlPO    = mysql_query("SELECT id FROM purchase_requests WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlPB    = mysql_query("SELECT id FROM purchase_orders WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $sqlBR    = mysql_query("SELECT id FROM purchase_returns WHERE currency_id = ".$aRow[0]." AND status > 0 LIMIT 1");
    $row[] =
            ($allowApply ? '<a href="" class="btnApplyPOSBranchCurrency" rel="'.$aRow[0].'" name="' . $aRow[3] . '"><img alt="'.ACTION_APPLY_TO_POS.'" onmouseover="Tip(\'' . ACTION_APPLY_TO_POS . '\')" src="' . $this->webroot . 'img/button/POS-Icon.png" style="width: 16px;" /></a> ' : '') .
            ($allowView ? '<a href="" class="btnViewBranchCurrency" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="' . ACTION_VIEW . '" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlSales) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR) ? '<a href="" class="btnEditBranchCurrency" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="' . ACTION_EDIT . '" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlSales) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR) ? '<a href="" class="btnDeleteBranchCurrency" rel="' . $aRow[0] . '" name="' . $aRow[3] . '"><img alt="' . ACTION_DELETE . '" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>