<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

if ($data[0] != '') {
    $conDate              = strtotime("-".($data[0])." months", strtotime(date("Y-m-01")));
    $countIntMonth        = strtotime("-".($data[0])." months", strtotime(date("Y-m-01")));
    if($data[0] == 0){
        $countIntMonthCurrent = strtotime("-0 months", strtotime(date("Y-m-1")));
    }else{
        $countIntMonthCurrent = strtotime("-1 months", strtotime(date("Y-m-1")));
    }
}else{
    $conDate  = strtotime("-0 months", strtotime(date("Y-m-01")));
}

$conDateNew           = date("Y-m-31", $conDate);
$countIntMonth        = date("Y-m-01", $countIntMonth);
$countIntMonthCurrent = date("Y-m-31", $countIntMonthCurrent);

$viewByGroup = 0;
if($data[1] != ""){
    $viewByGroup = 1;
}

$aColumns = array(
                    'c.id',
                    'customer_code', 
                    'name', 
                    'IFNULL((SELECT COUNT(id) FROM quotations WHERE customer_id = c.id AND status > 0 AND quotation_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT SUM(total_amount) FROM quotations WHERE customer_id = c.id AND status > 0 AND quotation_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT CONCAT(quotation_code, " ", DATE_FORMAT(quotation_date, "%d/%m/%Y")) FROM quotations WHERE customer_id = c.id AND status > 0 AND quotation_date < "'.$conDateNew.'" ORDER BY id DESC LIMIT 1), "0000-00-00")',                    
                    'IFNULL((SELECT COUNT(id) FROM orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT SUM(total_amount) FROM orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT CONCAT(order_code, " ", DATE_FORMAT(order_date, "%d/%m/%Y")) FROM orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" ORDER BY id DESC LIMIT 1), "0000-00-00")',                    
                    'IFNULL((SELECT COUNT(id) FROM sales_orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT SUM(total_amount) FROM sales_orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" GROUP BY customer_id), 0)', 
                    'IFNULL((SELECT CONCAT(so_code, "<br/>", DATE_FORMAT(order_date, "%d/%m/%Y")) FROM sales_orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" ORDER BY id DESC LIMIT 1), "0000-00-00")',    
                    '(SELECT TIMESTAMPDIFF(MONTH, IFNULL((SELECT order_date FROM sales_orders WHERE customer_id = c.id AND status > 0 AND order_date < "'.$conDateNew.'" ORDER BY id DESC LIMIT 1), "0000-00-00"), "'.date("Y-m-d").'"))',
                    'cg.cgroup_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "c.id";

/* DB table to use */
$sTable = "customers AS c INNER JOIN customer_cgroups AS cg ON c.id = cg.customer_id";

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
for ($i = 0; $i < count($aColumns) - 1; $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

$mounthCurrent = date("m") - 1;

/* Customize condition */
$condition = "c.is_active=1 AND (IFNULL((SELECT COUNT(id) FROM sales_orders WHERE customer_id = c.id AND status > 0 AND order_date > '".$countIntMonth."'  AND order_date < '".$countIntMonthCurrent."' GROUP BY customer_id), 0)) = 0";
//if ($data[1] != '') {
//    $condition != '' ? $condition .= ' AND ' : '';
//    $condition .= $data[1].' IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id=c.id)';
//}
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
if($viewByGroup == 1){
    $sOrder = "ORDER BY cg.cgroup_id ASC";
}
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
$tmpId = '';
$parentId   = '$';
$parentName = "";
$keyIndex   = 1;
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if($viewByGroup == 1){
                if ($aRow[13]  == $parentId) {
                    $row[] = ++$index;
                }else{
                    $index = 0;
                    if (!is_null($aRow[13])) {                    
                        $queryName = mysql_query("SELECT name FROM cgroups WHERE id = '".$aRow[13]."'");
                        $dataName  = mysql_fetch_array($queryName);                    
                        $parentName = $dataName[0];
                    } else {
                        $parentName = 'No Parent';
                    }
                    $row[] = '<b class="colspanParent" nameParentId="'.$aRow[13].'"> <img src="' . $this->webroot . 'img/minus.gif" class="imgHideShowRefer" style="cursor: pointer;" /> '.$parentName.'</b>';
                    for ($j = 0; $j < count($aColumns) - 1; $j++) {
                        $row[] = '<b class="colspanParentHidden"></b>';
                    } 
                    $output['aaData'][] = $row;
                    $row = array();                
                    $row[] = ++$index; 
                }
            }else{              
                $row[] = ++$index; 
            }
        } else if ($i == 1) {
            if($viewByGroup == 1){
                $row[] = '<b class="childRefer" actionChild="'.$aRow[13].'"></b>'.$aRow[$i];
            }else{
                $row[] = $aRow[$i];
            }
        } else if ($i == 4 || $i == 7 || $i == 10) {
            $row[] = number_format($aRow[$i], 3);
        } else if ($i == 5) {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($i == 8) {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($i == 11) {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $keyIndex++;
    $parentId = $aRow[13]; 
    $output['aaData'][] = $row;
    $tmpId = $aRow[0];
    
}

echo json_encode($output);
?>