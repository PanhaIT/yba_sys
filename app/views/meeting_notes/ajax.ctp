<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('met.id','met.code','CONCAT_WS("|*|",IFNULL(eg.id,""),eg.name)', 'met.description','met.created','GROUP_CONCAT(u.first_name," ",u.last_name)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "met.id";

/* DB table to use */
$sTable = "meeting_notes met INNER JOIN egroups eg ON eg.id = met.egroup_id INNER JOIN users u ON u.id=met.created_by";

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
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if($aColumns[$i]=='met.code' || $aColumns[$i] =='CONCAT_WS("|*|",IFNULL(eg.id,""),eg.name)' || $aColumns[$i] =='e.name' || $aColumns[$i] =='met.name' || $aColumns[$i] =='met.remark' || $aColumns[$i] == 'GROUP_CONCAT(u.first_name," ",u.last_name)'){
            if($aColumns[$i] =='GROUP_CONCAT("u.first_name"," ","u.last_name")'){
                $sWhere .= "u.first_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                $sWhere .= "u.last_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] =='CONCAT_WS("|*|",IFNULL(eg.id,""),eg.name)'){
                $sWhere .= "eg.name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else{
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }
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
        if($aColumns[$i]=='met.code' || $aColumns[$i] =='CONCAT_WS("|*|",IFNULL(eg.id,""),eg.name)' || $aColumns[$i] =='e.name' || $aColumns[$i] =='met.name' || $aColumns[$i] =='met.remark' || $aColumns[$i] == 'GROUP_CONCAT(u.first_name," ",u.last_name)'){
            if($aColumns[$i] =='GROUP_CONCAT("u.first_name"," ","u.last_name")'){
                $sWhere .= "u.first_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
                $sWhere .= "u.last_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] =='CONCAT_WS("|*|",IFNULL(eg.id,""),eg.name)'){
                $sWhere .= "eg.name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else{
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }
    }
}

/* Customize condition */
// $conditionUser = " AND met.created_by = ".$user['User']['id'];
$conditionUser = '';
$condition = "met.is_active=1".$conditionUser;
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
$sGroup="GROUP BY met.id";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM $sTable
        $sWhere
        $sGroup
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
    $egroupArray = explode("|*|", $aRow[2]);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            $row[] = ++$index;
        }else if ($i==2) {
            $row[] = $egroupArray[1];
        }else if ($i==3) {
            $row[] = '<div class="panel-heading">'.$aRow[$i].'</div>';
        }else if ($aColumns[$i] != '') {
            $row[] = $aRow[$i];
        }
    }
    $row[] =
        ($allowView? '<a href="#" rel="'.$aRow[0].'" class="btn-view-todolist btnCloneMeetingNote"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_CLONE .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#files" /></svg></a>':'').
        ($allowView? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-view-item btnViewMeetingNote margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_VIEW .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#list-ul" /></svg></a>':'').
        ($allowEdit? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-edit-item btnEditMeetingNote margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_EDIT .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#pencil-square" /></svg></a>':'').
        ($allowDelete? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-remove-item btnDeleteMeetingNote margin-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_DELETE .'"><svg class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a><span id="basic"></span><span id="title"></span><span id="footer"></span>':'')
        ;
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>