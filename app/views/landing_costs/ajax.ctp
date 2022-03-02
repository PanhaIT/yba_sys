<?php
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$status = 0;

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("landing_costs.id", 
    "landing_costs.date",
    "landing_costs.code",
    "purchase_orders.po_code",
    "CONCAT_WS(' - ',vendors.vendor_code,vendors.name)",
    "landed_cost_types.name",
    "landing_costs.total_amount",
    "landing_costs.balance",
    "CASE landing_costs.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Open' WHEN 2 THEN 'Closed' END",
    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "landing_costs.date";

/* DB table to use */
$sTable = " landing_costs 
            INNER JOIN purchase_orders ON purchase_orders.id = landing_costs.purchase_order_id
            INNER JOIN vendors ON vendors.id = landing_costs.vendor_id 
            INNER JOIN landed_cost_types ON landed_cost_types.id = landing_costs.landed_cost_type_id 
            INNER JOIN currency_centers ON currency_centers.id = landing_costs.currency_center_id";

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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "landing_costs.id"){
                $sOrder .= "landing_costs.created " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
$condition = "landing_costs.status >= 0 AND landing_costs.company_id IN (SELECT company_id FROM user_companies WHERE user_id='" . $user['User']['id'] . "' GROUP BY company_id) AND landing_costs.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id='" . $user['User']['id'] . "' GROUP BY branch_id)";

if($allowViewByUser){
    $condition .= " AND landing_costs.created_by =".$user['User']['id'];
}

if ($vendor != "all") {
    $condition .= " AND vendor_id = " . $vendor;
}

if ($filterStatus != "all") {
    $condition .= " AND landing_costs.status='" . $filterStatus . "'";
}

if ($date != "") {
    $condition .= " AND landing_costs.date='" . $date . "'";
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
        } else if ($aColumns[$i] == 'landing_costs.date') {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "CASE landing_costs.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Open' WHEN 2 THEN 'Closed' END") {
            $row[] = $aRow[$i];
            if (trim($aRow[$i]) == "Void") {
                $status = 0;
            } else if (trim($aRow[$i]) == "Open") {
                $status = 1;
            } else if (trim($aRow[$i]) == "Closed") {
                $status = 2;
            }
        } else if ($aColumns[$i] == 'landing_costs.total_amount' || $aColumns[$i] == 'landing_costs.balance') {
            $row[] = $aRow[9]." ".number_format($aRow[$i],2);
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $queryHasReceipt  = mysql_query("SELECT id FROM landing_cost_receipts WHERE landing_cost_id=" . $aRow[0] . " AND is_void = 0");
    $row[] =
            ($allowView ? '<a href="#" class="btnViewLandingCost" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit && $status == 1 && !mysql_num_rows($queryHasReceipt) ? '<a href="#" class="btnEditLandingCost" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowAging && $status > 0 ? '<a href="#" class="btnLandingCostAging" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . TABLE_PAY . '\')" src="' . $this->webroot . 'img/button/aging.png" /></a> ' : '') .
            ($allowClose && $status == 1 ? '<a href="#" class="btnLandingCostClose" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_CLOSE . '\')" src="' . $this->webroot . 'img/button/close.png" /></a> ' : '') .
            ($allowVoid && $status == 1 && !mysql_num_rows($queryHasReceipt) ? '<a href="#" class="btnVoidLandingCost" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>