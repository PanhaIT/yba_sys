<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("orders.id", 
    "(SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id)",
    "rd.product_id",
    "orders.order_code");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "orders.id";

/* DB table to use */
$sTable = "orders INNER JOIN users ON users.id = orders.created_by INNER JOIN customers ON customers.id = orders.customer_id INNER JOIN currency_centers ON currency_centers.id = orders.currency_center_id INNER JOIN order_details as rd ON rd.order_id=orders.id";

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
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }

    $sOrder = str_replace("orders.order_date asc" ,  "orders.order_date asc,  orders.id asc", $sOrder);
    $sOrder = str_replace("orders.order_date desc",  "orders.order_date desc, orders.id desc", $sOrder);
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
        if($aColumns[$i] != "orders.order_date"){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
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
$condition = 'orders.status > 0';
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(order_date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(order_date)';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.company_id=' . $data[3];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.branch_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.customer_id=' . $data[5];
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.is_close=' . $data[6];
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.is_approve=' . $data[7];
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'orders.created_by=' . $data[8];
}
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'rd.product_id=' . $data[9];
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
        GROUP BY rd.product_id
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
    $k=1;
    $n=1;
    $row = array();
    $row[] = ++$index;
    $row[] = $aRow[1];
    $row[] = '';
    $row[] = '';
    $row[] = '';
    $row[] = '';
    $row[] = '';
    $row[] = '';
    $row[] = '';
    $output['aaData'][] = $row;
    $queryData=  mysql_query("SELECT orders.id, orders.order_date,orders.order_code,CONCAT(customers.name_kh, ' (',customers.name,')'),
    orders.is_close,
    orders.is_approve,
    CONCAT(users.first_name, ' ',users.last_name) as name,
    qty,(SELECT name FROM uoms WHERE id=qty_uom_id) as uomName,unit_price,total_price,
    currency_centers.symbol FROM orders 
    INNER JOIN users ON users.id = orders.created_by 
    INNER JOIN customers ON customers.id = orders.customer_id 
    INNER JOIN currency_centers ON currency_centers.id = orders.currency_center_id 
    INNER JOIN order_details as rd ON rd.order_id=orders.id WHERE rd.product_id=".$aRow[2]." AND ".$condition." ORDER BY orders.order_date DESC
    ");
    while ($data=  mysql_fetch_array($queryData)){
        $row1 = array();
        $row1[] = '';
        $row1[] = '';
        if ($data[1] != '0000-00-00') {
            $row1[] = '<span class="totalAmount" unit="'.$data['unit_price'].'" total="'.$data['total_price'].'">'.dateShort($data[1]).'</span>';
        } else {
            $row1[] = '';
        }
        $row1[] = $data[2];
        $row1[] = $data[3];
        $row1[] = $data['qty'];
        $row1[] = $data['uomName'];
        $row1[] = $data['unit_price'];
        $row1[] = $data['total_price'];
        $output['aaData'][] = $row1;
    }
}

echo json_encode($output);
?>