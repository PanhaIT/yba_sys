<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'viewTodoList');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'editTodoList');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'deleteTodoList');
$viewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('tl.id','GROUP_CONCAT(c.customer_code,"-",c.name)','tl.task_name', 'tl.estimate_date','tl.date' , 'tl.remark','CONCAT_WS("|*|",IFNULL(pri.id,""),pri.name)', 'CONCAT_WS("|*|",IFNULL(pro.id,""),pro.name)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "tl.id";

/* DB table to use */
$sTable = "todo_lists tl 
INNER JOIN priorities pri ON pri.id = tl.priority_id 
INNER JOIN customers c ON c.id = tl.customer_id 
LEFT JOIN employees e ON e.id=tl.employee_id
INNER JOIN progresses pro ON pro.id = tl.status
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
        if($aColumns[$i] =='tl.task_name' || $aColumns[$i] =='tl.estimate_date' || $aColumns[$i] =='tl.date' || $aColumns[$i] =='tl.remark' || $aColumns[$i] == 'GROUP_CONCAT(c.customer_code,"-",c.name)' ||  $aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pri.id,""),pri.name)' || $aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pro.id,""),pro.name)'){
            if($aColumns[$i] == 'GROUP_CONCAT(c.customer_code,"-",c.name)'){
                $sWhere .= "c.name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                $sWhere .= "c.customer_code LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pri.id,""),pri.name)'){
                $sWhere .= "pri.name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pro.id,""),pro.name)'){
                $sWhere .= "pro.name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
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
        if($aColumns[$i] =='tl.task_name' || $aColumns[$i] =='tl.estimate_date' || $aColumns[$i] =='tl.date' || $aColumns[$i] =='tl.remark' ||  $aColumns[$i] == 'GROUP_CONCAT(c.customer_code,"-",c.name)' ||  $aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pri.id,""),pri.name)' || $aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pro.id,""),pro.name)'){
            if($aColumns[$i] == 'GROUP_CONCAT(c.customer_code,"-",c.name)'){
                $sWhere .= "c.name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
                $sWhere .= "c.customer_code LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pri.id,""),pri.name)'){
                $sWhere .= "pri.name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(pro.id,""),pro.name)'){
                $sWhere .= "pro.name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else{
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }
    }
}

/* Customize condition */
$conditionUser = '';
if($viewByUser){
    $conditionUser = " AND tl.created_by = ".$user['User']['id'];
}
$condition = "tl.is_active=1".$conditionUser;
if($TodoListPriorityId != "all"){
    $condition .= " AND pri.id = ".$TodoListPriorityId."";
}
if($TodoListProgresseId != "all"){
    $condition .= " AND tl.status = ".$TodoListProgresseId."";
}
if($TodoListCustomerId != "all"){
    $condition .= " AND c.id = ".$TodoListCustomerId."";
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
$sGroup="GROUP BY tl.id";
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
    $priorityArray = explode("|*|", $aRow[6]);
    $statusArray = explode("|*|", $aRow[7]);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($i==6) {
            $color = '';
            if($priorityArray[0]==1){
                $color = '808080;';
            }else if($priorityArray[0]==2){
                $color = 'f89500;';
            }else if($priorityArray[0]==3){
                $color = 'ff0000;';
            }
            $row[] = '<span class="text-priority" style="background-color:#'.$color.'">'.$priorityArray[1].'</span>';
        } else if ($i==7) {
            $color = '';
            if($statusArray[0]==1){
                $color = '808080;';
            }else if($statusArray[0]==2){
                $color = 'f89500;';
            }else if($statusArray[0]==3){
                $color = '008000;';
            }
            $row[] = '<table><tr><td><svg class="icon-svg-status" style="color:#'.$color.'"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#circle-fill" /></svg></td><td>'.$statusArray[1].'</td></tr></table>';
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
        ('<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-view-item btnViewTodoList"><svg data-bs-toggle="tooltip" data-bs-placement="left" title='. ACTION_VIEW .' class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#list-ul" /></svg></a>').
        ($statusArray[0]==1 || $statusArray[0]==2? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-share-item btnShareTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title='. ACTION_SHARE .' class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#share-fill" /></svg></a>':'').
        ($statusArray[0]==1 || $statusArray[0]==2? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-approve-item btnApproveTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title='. ACTION_APPROVE .' class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#check2-square" /></svg></a>':'').
        ($statusArray[0]==1 || $statusArray[0]==2? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-edit-item btnEditTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title='. ACTION_EDIT .' class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#pencil-square" /></svg></a>':'').
        ($statusArray[0]==1 || $statusArray[0]==2? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-remove-item btnDeleteTodoList margin-custom" data-bs-toggle="tooltip" data-bs-placement="left" title='. ACTION_DELETE .'><svg class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a><span id="basic"></span><span id="title"></span><span id="footer"></span>':'')
        ;
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>