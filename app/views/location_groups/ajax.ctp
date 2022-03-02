<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowWarehouseStock = checkAccess($user['User']['id'], $this->params['controller'], 'viewProductWarehouse');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('location_groups.id', 'location_groups.code', 'location_groups.name', 'location_groups.allow_negative_stock', 'location_groups.name');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "location_groups.id";

/* DB table to use */
$sTable = "location_groups INNER JOIN location_group_types ON location_group_types.id = location_groups.location_group_type_id";

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
$condition = "location_groups.is_active = 1 AND location_group_type_id != 1 AND location_groups.id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")";
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
    
    //Check Total Product
    $dataProduct[0] = 0;
    $queryProduct = mysql_query("SELECT COUNT(product_id) FROM ".$aRow[0]."_group_totals INNER JOIN products ON products.id = ".$aRow[0]."_group_totals.product_id WHERE products.is_active = 1");
    if(mysql_num_rows($queryProduct)){
        $dataProduct  = mysql_fetch_array($queryProduct);
    }

    //Check Total Product Avaible
    $dataProAva[0] = 0;
    $queryProAva = mysql_query("SELECT COUNT(product_id) FROM ".$aRow[0]."_group_totals INNER JOIN products ON products.id = ".$aRow[0]."_group_totals.product_id WHERE products.is_active = 1 AND ".$aRow[0]."_group_totals.total_qty > 0");
    if(mysql_num_rows($queryProAva)){
        $dataProAva  = mysql_fetch_array($queryProAva);
    }
    
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if($i == 3){
            if($aRow[$i] == 1){
                $row[] = ACTION_YES;
            } else {
                $row[] = ACTION_NO;
            }
        } else if($i == 4){
            if($dataProduct[0] > 0){
                $row[] = ($dataProAva[0] * 100) / $dataProduct[0]." %";
            } else {
                $row[] = "0%";
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    // Check Exist in location
    $sqlCheck = mysql_query("SELECT id FROM locations WHERE location_group_id = ".$aRow[0]." AND is_active = 1 LIMIT 1;");
    $row[] =
            ($allowWarehouseStock ? '<a href="" class="btnViewProductLocationGroup" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW_ALL_PRODUCT_LOCATION . '\')" src="' . $this->webroot . 'img/button/health.png" /></a> ' : '') .
            ($allowView ? '<a href="" class="btnViewLocationGroup" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit ? '<a href="" class="btnEditLocationGroup" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete && !mysql_num_rows($sqlCheck) ? '<a href="" class="btnDeleteLocationGroup" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>