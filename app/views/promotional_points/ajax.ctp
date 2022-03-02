<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowCancel = checkAccess($user['User']['id'], $this->params['controller'], 'cancel');
$allowApprove  = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowAdd  = checkAccess($user['User']['id'], $this->params['controller'], 'add');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('promotional_points.id', 
'IFNULL(branches.name, "All")', 
'promotional_points.date',
'promotional_points.code', 
'promotional_points.description', 
'CONCAT_WS("|*|",IFNULL(promotional_points.total_point,0),IFNULL(promotional_points.point_in_dollar,0))',
'promotional_points.start',
'promotional_points.end',
'promotional_points.status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "promotional_points.id";

/* DB table to use */
$sTable = "promotional_points LEFT JOIN branches ON branches.id = promotional_points.branch_id";

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
if($status != 'all'){
    if($status == 1){
        $condition = "promotional_points.status = 1";
    } else if($status == 2){
        $condition = "promotional_points.status = 0";
    } else if($status == 3){
        $condition = "promotional_points.status = 2 AND promotional_points.end > now()";
    } else if($status == 4){
        $condition = "promotional_points.status = 2 AND promotional_points.end < now()";
    }
} else {
    $condition = "(promotional_points.status >= 0 OR promotional_points.status = -2 OR promotional_points.status = -3)";
}
if($fromYear != 'all'){
    $condition .= " AND YEAR(promotional_points.start) = '".$fromYear."'";
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
    $explodeStr = explode("|*|", $aRow[5]);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($aColumns[$i] == 'promotional_points.date' || $aColumns[$i] == 'promotional_points.start' || $aColumns[$i] == 'promotional_points.end') {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00'){
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($i==5) {
            $row[] = $explodeStr[0].'point(s) = '.$explodeStr[1].' $';
        } else if ($aColumns[$i] == 'promotional_points.status') {
            $isExp = 0;
            if($aRow[$i] == 2 && strtotime($aRow[7]) < strtotime(date("Y-m-d"))){
                $row[] = 'Expired';
                $isExp = 1;
            } else {
                switch($aRow[$i]){
                    case -3:
                        $row[] =  'Disapprove';
                        break;
                    case -2:
                        $row[] =  'Cancel';
                        break;
                    case 0:
                        $row[] =  'Void';
                        break;
                    case 1:
                        $row[] =  'Request';
                        break;
                    case 2:
                        $row[] =  'Approved';
                        break;
                }
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="#" class="btnViewPromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            //($allowAdd ? '<a href="" class="btnClonePromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Clone" onmouseover="Tip(\'' . ACTION_CLONE . '\')" src="' . $this->webroot . 'img/button/cycle.png" /></a> ' : '') .
            ($allowEdit && $aRow[8] >= 1 ? '<a href="" class="btnEditPromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowApprove && $aRow[8] == 1 ? '<a href="#" class="btnApprovePromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="'.ACTION_APPROVE.'" onmouseover="Tip(\'' . ACTION_APPROVE . '\')" src="' . $this->webroot . 'img/button/approved.png" /></a> ' : '') .
            ($allowDelete && $aRow[8] == 1 ? '<a href="#" class="btnDeletePromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="'.ACTION_DELETE.'" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '') .
            ($allowCancel && $isExp == 0 && $aRow[8] == 2 ? '<a href="#" class="btnCancelPromotionalPoint" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="'.ACTION_CANCEL.'" onmouseover="Tip(\'' . ACTION_CANCEL . '\')" src="' . $this->webroot . 'img/button/stop.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>