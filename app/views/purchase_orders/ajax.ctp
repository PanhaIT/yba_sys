<?php
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen = checkAccess($user['User']['id'], $this->params['controller'], 'open');

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('pr.id',
    'pr.order_date',
    "pr.pr_code",
    'v.name',
    'pr.total_amount + IFNULL(pr.total_vat, 0)',
    'pr.total_deposit',
    'pr.status',
    'pr.is_close',
    'currency_centers.symbol');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "pr.id";

/* DB table to use */
$sTable = "purchase_requests as pr INNER JOIN `vendors` as v ON pr.vendor_id = v.id INNER JOIN currency_centers ON currency_centers.id = pr.currency_center_id";

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
$condition = "pr.status >= 0 AND pr.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") AND pr.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";

if ($date != "") {
    $condition .= " AND pr.order_date = '" . $date . "'";
}

if ($filterStatus != "all") {
    $condition .= " AND pr.status = '" . $filterStatus . "'";
}

if ($filterClose != "all") {
    $condition .= " AND pr.is_close = '" . $filterClose . "'";
}

if ($vendor != "all") {
    $condition .= " AND pr.vendor_id = '" . $vendor . "'";
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
        } else if ($i == 2) {
            $row[] = $aRow[2];
        } else if ($aColumns[$i] == 'pr.order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'pr.status') {
            switch ($aRow[$i]) {
                case 0:
                    $row[] = 'Void';
                    break;
                case 1:
                    $row[] = 'Issued';
                    break;
                case 2:
                    $row[] = 'Partial';
                    break;
                case 3:
                    $row[] = 'Fulfilled';
                    break;
            }
        } else if ($aColumns[$i] == 'pr.total_amount + IFNULL(pr.total_vat, 0)' || $aColumns[$i] == 'pr.total_deposit') {
            $row[] = $aRow[8]." ".number_format($aRow[$i], $costDecimal);
        } else if ($aColumns[$i] == 'pr.is_close') {
            $actionOpen  = "";
            $actionClose = "";
            if($allowClose){
                $actionClose = "btnClosePurchaseRequest";
            }
            if($allowOpen){
                $actionOpen = "btnOpenPurchaseRequest";
            }
            $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" class="'.($aRow[7] == 0 && $aRow[6] > 0 ? $actionClose : $actionOpen).'" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
        } else if ($aColumns[$i] == 'currency_centers.symbol') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    // Action (Delete) Filter By is_close = 0, status = 1, total_deposit = 0
    $row[] =
            ($allowView ? '<a href="" class="btnViewPurchaseRequest" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="' . ACTION_VIEW . '" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowPrint && $aRow[6] > 0 ? '<a href="" class="btnPrintInvoicePO" rel="' . $aRow[0] . '"><img alt="' . ACTION_PRINT . '" onmouseover="Tip(\'' . ACTION_PRINT . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .
            ($allowEdit && $aRow[6] == 1 && $aRow[7] == 0 ? '<a href="" class="btnEditPurchaseRequest" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="' . ACTION_EDIT . '" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete && $aRow[6] == 1 && $aRow[7] == 0 && $aRow[5] == 0 ? '<a href="" class="btnDeletePurchaseRequest" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="' . ACTION_VOID . '" onmouseover="Tip(\'' . ACTION_VOID . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a> ' : '');
    ;
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>