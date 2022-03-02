<?php
// include("includes/function.php");

// Authentication
$this->element('check_access');
// $allowView = checkAccess($user['User']['id'], 'customers', 'view');
// $allowEdit = checkAccess($user['User']['id'], 'customers', 'edit');
// $allowDelete = checkAccess($user['User']['id'], 'customers', 'delete');


header("Content-type: text/plain");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('s.id','s.code','c.name','b.name','sg.name','s.name');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "s.id";

/* DB table to use */
$sTable = "services s 
INNER JOIN service_groups sg ON sg.id=s.service_group_id 
INNER JOIN companies c ON c.id=s.company_id
INNER JOIN service_branches sb ON sb.service_id = s.id
INNER JOIN branches b oN b.id=sb.branch_id
";

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
$sOrder="";
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
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if($aColumns[$i] == 's.code' || $aColumns[$i] == 'b.name' ||  $aColumns[$i] == 'c.name' ||  $aColumns[$i] == 's.name' ||  $aColumns[$i] == 'sg.name'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 1; $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if($aColumns[$i] == 's.code' || $aColumns[$i] == 'b.name' ||  $aColumns[$i] == 'c.name' ||  $aColumns[$i] == 's.name' ||  $aColumns[$i] == 'sg.name'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

/* Customize condition */
$condition = "s.is_active=1";
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */

$groupBy = "";

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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        }else{
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
    ('<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-view-item btnViewService"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_VIEW .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#list-ul" /></svg></a>').
    ('<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-edit-item btnEditService margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_EDIT .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#pencil-square" /></svg></a>').
    ('<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-remove-item btnDeleteService margin-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_DELETE .'"><svg class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a><span id="basic"></span><span id="title"></span><span id="footer"></span>')
    ;
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>

