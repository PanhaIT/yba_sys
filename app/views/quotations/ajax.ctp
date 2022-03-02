<?php
$priceDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen = checkAccess($user['User']['id'], $this->params['controller'], 'open');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("quotations.id", 
    "quotations.quotation_date",
    "quotations.quotation_code",
    "customers.customer_code",
    "customers.name",
    "quotations.total_amount - IFNULL(quotations.discount,0) + IFNULL(quotations.total_vat,0)",
    "quotations.total_deposit",
    "quotations.is_close",
    "quotations.is_approve",
    "quotations.status",
    "quotations.share_save_option",
    "quotations.share_option",
    "quotations.share_user",
    "quotations.share_except_user",
    "quotations.created_by",
    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "quotations.id";

/* DB table to use */
$sTable = "quotations INNER JOIN customers ON customers.id = quotations.customer_id INNER JOIN currency_centers ON currency_centers.id = quotations.currency_center_id";

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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "quotations.id"){
                $sOrder .= "quotations.created " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
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
    for ($i = 0; $i < (count($aColumns) - 10); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 10); $i++) {
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
$condition = "quotations.status > 0 AND quotations.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") AND quotations.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
if($allowViewByUser){
    $condition .= " AND ((quotations.share_option = 1 AND quotations.created_by =".$user['User']['id'].") OR (quotations.share_option = 2) OR (quotations.share_option = 3 AND (FIND_IN_SET('".$user['User']['id']."', quotations.share_user) > 0 OR (quotations.created_by =".$user['User']['id']."))) OR (quotations.share_option = 4 AND !FIND_IN_SET('".$user['User']['id']."', quotations.share_except_user)))";
}

if($customer != 'all'){
    $condition .= " AND quotations.customer_id = ".$customer;
}

if($status != 'all'){
    $condition .= " AND quotations.is_close = ".$status;
}

if($approve != 'all'){
    $condition .= " AND quotations.is_approve = ".$approve;
}

if($date != ''){
    $condition .= " AND quotations.quotation_date = '".$date."'";
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
        } else if ($aColumns[$i] == "quotations.quotation_date") {
            if ($aRow[$i] != '0000-00-00' && $aRow[$i] != '') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'quotations.is_close') {
            $actionButton = '';
            if($aRow[7] == 0 && $aRow[9] > 0 && $allowClose){
                $actionButton = 'btnCloseQuotation';
            } else if($aRow[7] == 1 && $aRow[9] > 0 && $allowOpen){
                $actionButton = 'btnOpenQuotation';
            }
            $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" class="'.$actionButton.'" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
        } else if ($aColumns[$i] == 'quotations.is_approve') {
            $actionApprove = "btnApproveQuotation";
            $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" class="'.($aRow[$i] == 0 && $allowApprove ? $actionApprove : "").'" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
        } else if ($i == 9 || $i == 10 || $i == 11 || $i == 12 || $i == 13 || $i == 14 || $i == 15) {
        } else if ($i == 5 || $i == 6) {
            $row[] = $aRow[15]." ".number_format($aRow[$i], $priceDecimal);
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    
    $row[] =
            ($aRow[14] == $user['User']['id'] ? '<a href="#" class="btnShareOptQuotation" shopt="' . $aRow[11] . '" ssaopt="' . $aRow[10] . '" shusr="' . $aRow[12] . '" shuect="' . $aRow[13] . '" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Share Option" style="width: 14px; height: 14px;" onmouseover="Tip(\'' . TABLE_SHARE_OPTION . '\')" src="' . $this->webroot . 'img/button/share.png" /></a> &nbsp;' : '').
            ($allowView ? '<a href="#" class="btnViewQuotation" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> &nbsp;' : '') .
            ($allowPrint && $aRow[9] > 0 ? '<a href="#" class="btnPrintInvoiceQuotation" rel="' . $aRow[0] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> &nbsp;' : '') .
            '<a href="#" class="btnHistoryQuotation" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View History" onmouseover="Tip(\'' . ACTION_VIEW_HISTORY . '\')" src="' . $this->webroot . 'img/button/history-icon.png" /></a> &nbsp;'.
            ($allowEdit && $aRow[9] == 1 && $aRow[7] == 0 && $aRow[8] == 0 ? '<a href="#" class="btnEditQuotation" rel="' . $aRow[0] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> &nbsp;' : '') .
            ($allowVoid && $aRow[9] == 1 && $aRow[7] == 0 && $aRow[8] == 0 ? '<a href="#" class="btnVoidQuotation" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Void" onmouseover="Tip(\'Void\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>