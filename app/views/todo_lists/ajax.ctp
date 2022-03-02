<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'editTodoList');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'deleteTodoList');
$allowViewByUser = checkAccess($user['User']['id'], $this->params['controller'], 'viewByUser');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'id',
    'customer',
    'task_name',
    'estimate_date',
    'date' ,
    'remark',
    'user_name',
    'priority', 
    'progress');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";
/* DB table to use */
$created_by = "";
$share_user_id = "";
if($allowViewByUser){
    $created_by = " AND tl.created_by = ".$user['User']['id'];
    $share_user_id = " AND ust.share_user_id = ".$user['User']['id'];
}

$sTable = "
    (SELECT 
    tl.id AS id, 
    c.id AS customer_id,
    c.customer_code AS customer_code,
    c.name AS customer_name,
    CONCAT_WS('|*|',IFNULL(c.customer_code,''),c.name) AS customer,
    tl.task_name AS task_name, 
    tl.estimate_date AS estimate_date, 
    tl.date AS date, 
    tl.remark AS remark,
    users.first_name AS first_name,
    users.last_name AS last_name,
    CONCAT_WS('|*|',IFNULL(users.first_name,''),users.last_name) AS user_name,
    pri.id AS priority_id,
    pri.name AS priority_name,
    CONCAT_WS('|*|',IFNULL(pri.id,''),pri.name,'','') AS priority, 
    pro.name AS progress_name,
    CONCAT_WS('|*|',IFNULL(pro.id,''),pro.name) AS progress, 
    tl.is_active AS is_active,
    tl.status AS status
    FROM todo_lists tl 
    INNER JOIN priorities pri ON pri.id = tl.priority_id 
    LEFT JOIN customers c ON c.id = tl.customer_id 
    INNER JOIN progresses pro ON pro.id = tl.status 
    INNER JOIN users ON users.id=tl.created_by
    WHERE tl.is_active=1 ".$created_by."

    UNION

    SELECT
    tl.id AS id,
    c.id AS customer_id,
    c.customer_code AS customer_code,
    c.name AS customer_name,
    CONCAT_WS('|*|',IFNULL(c.customer_code,''),c.name) AS customer,
    tl.task_name AS task_name, 
    tl.estimate_date AS estimate_date, 
    tl.date AS date, 
    tl.remark AS remark, 
    users.first_name AS first_name,
    users.last_name AS last_name,
    CONCAT_WS('|*|',IFNULL(users.first_name,''),users.last_name) AS user_name,
    pri.id AS priority_id,
    pri.name AS priority_name,
    CONCAT_WS('|*|',IFNULL(pri.id,''),pri.name,ust.share_user_id,ust.user_id) AS priority, 
    pro.name AS progress_name,
    CONCAT_WS('|*|',IFNULL(pro.id,''),pro.name) AS progress, 
    tl.is_active AS is_active,
    tl.status AS status
    FROM todo_lists tl 
    INNER JOIN priorities pri ON pri.id = tl.priority_id 
    LEFT JOIN customers c ON c.id = tl.customer_id 
    INNER JOIN progresses pro ON pro.id = tl.status 
    INNER JOIN user_share_todolists ust ON ust.user_id = tl.created_by AND todo_list_id=tl.id ".$share_user_id."
    INNER JOIN users ON users.id=tl.created_by
    ) AS todo_lists
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
        if($aColumns[$i] =='id' || $aColumns[$i] =='user_name' || $aColumns[$i] =='task_name' || $aColumns[$i] =='estimate_date' || $aColumns[$i] =='date' || $aColumns[$i] =='remark' || $aColumns[$i] == 'customer' ||  $aColumns[$i] == 'priority' || $aColumns[$i] == 'progress'){
            if($aColumns[$i] == 'customer'){
                $sWhere .= "customer_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                $sWhere .= "customer_code LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] == 'user_name'){
                $sWhere .= "first_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                $sWhere .= "last_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] == 'priority'){
                $sWhere .= "priority_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }else if($aColumns[$i] == 'progress'){
                $sWhere .= "progress_name LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
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
        if($aColumns[$i] =='id' || $aColumns[$i] =='user_name' || $aColumns[$i] =='task_name' || $aColumns[$i] =='estimate_date' || $aColumns[$i] =='date' || $aColumns[$i] =='remark' || $aColumns[$i] == 'customer' ||  $aColumns[$i] == 'priority' || $aColumns[$i] == 'progress'){
            if($aColumns[$i] == 'customer'){
                $sWhere .= "customer_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
                $sWhere .= "customer_code LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] == 'user_name'){
                $sWhere .= "first_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
                $sWhere .= "last_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] == 'priority'){
                $sWhere .= "priority_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else if($aColumns[$i] == 'progress'){
                $sWhere .= "progress_name LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }else{
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }
    }
}


/* Customize condition */
$condition = "is_active=1";
if($TodoListPriorityId != "all"){
    $condition .= " AND priority_id = ".$TodoListPriorityId."";
}
if($TodoListProgresseId != "all"){
    $condition .= " AND status = ".$TodoListProgresseId."";
}
if($TodoListCustomerId != "all"){
    $condition .= " AND customer_id = ".$TodoListCustomerId."";
}
if($serviceId!='all'){
    $condition .= " AND id IN(SELECT todo_list_id FROM todo_list_details WHERE service_id='".$serviceId."')";
}
//$startDate=null,$endDate
if($startDate!=''){
    $condition .= " AND date >='".$startDate."' ";
}
if($endDate!=''){
    $condition .= " AND date <='".$endDate."' ";
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
$sGroup="";

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
    $cusArray  = explode("|*|", $aRow[1]);
    $userArray = explode("|*|", $aRow[6]);
    $priorityArray = explode("|*|", $aRow[7]);
    $statusArray   = explode("|*|", $aRow[8]);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($i==1) {
            $customer='';
            if($aRow[1]!=""){
                $customer=$cusArray[0].' '.$cusArray[1];
            }
            $row[] =$customer ;
        } else if ($i==2) {
            $color = '';
            if($priorityArray[0]==1){
                $color = '808080;';
            }else if($priorityArray[0]==2){
                $color = 'f89500;';
            }else if($priorityArray[0]==3){
                $color = 'ff0000;';
            }
            if($priorityArray[2]>0){
                $row[] = '<table><tr><td><svg class="icon-svg-status" style="color:#'.$color.'"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#share-fill" /></svg></td><td>'.$aRow[$i].'</td></tr></table>';
            }else{
                $row[] = $aRow[$i];
            }
        } else if ($i==6) {
            $row[] = $userArray[0].' '.$userArray[1];
        } else if ($i==7) {
            $color = '';
            if($priorityArray[0]==1){
                $color = '808080;';
            }else if($priorityArray[0]==2){
                $color = 'f89500;';
            }else if($priorityArray[0]==3){
                $color = 'ff0000;';
            }
            $row[] = '<span class="text-priority" style="background-color:#'.$color.'">'.$priorityArray[1].'</span>';
        } else if ($i==8) {
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
    $description ='';
    $querySource = mysql_query("SELECT description FROM user_share_todolists WHERE is_active=1  AND user_id ='".$user['User']['id']."' AND todo_list_id =" .$aRow[0]);
    if(mysql_num_rows($querySource)){
        $dataSource = mysql_fetch_array($querySource);
        $description = $dataSource[0];
    }
    $row[] =
        ($allowView?'<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-view-todolist btnCloneTodoList"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_CLONE .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#files" /></svg></a>':'').
        ($allowView?'<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-view-item btnViewTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_VIEW .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#info-circle-fill" /></svg></a>':'').
        (($statusArray[0]==1 || $statusArray[0]==2) && ($priorityArray[3]=="")? '<a href="#" rel="'.$aRow[0].'" owner="'.$priorityArray[3].'" name="'.$description.'" class="btn-share-item btnShareTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_SHARE .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#share-fill" /></svg></a>':'').
        ($statusArray[0]==1 || $statusArray[0]==2? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-approve-item btnApproveTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_APPROVE .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#check2-square" /></svg></a>':'').
        (($statusArray[0]==1 || $statusArray[0]==2) && ($priorityArray[3]=="")?'<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-edit-item btnEditTodoList margin-custom"><svg data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_EDIT .'" class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#pencil-square" /></svg></a>':'').
        (($statusArray[0]==1 || $statusArray[0]==2) && ($priorityArray[3]=="")? '<a href="#" rel="'.$aRow[0].'" name="'.$aRow[1].'" class="btn-remove-item btnDeleteTodoList margin-custom" data-bs-toggle="tooltip" data-bs-placement="left" title="'. ACTION_DELETE .'"><svg class="icon-svg-item"><use xlink:href="'.$this->webroot.'assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a><span id="basic"></span><span id="title"></span><span id="footer"></span>':'')
        ;
    $output['aaData'][] = $row;
}
echo json_encode($output);
?>