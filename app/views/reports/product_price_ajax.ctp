<?php

// Function
include('includes/function.php');
$filename="public/report/product_price_list_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$excelContent = MENU_PRODUCT_PRICE . "\n\n";
if($data[0]!='') {
    $query = mysql_query("SELECT name FROM companies WHERE id=".$data[0]);
    $com   = mysql_fetch_array($query);
    $excelContent  .= "\n".TABLE_COMPANY.': '.$com[0];
}
if($data[1]!='') {
    $query = mysql_query("SELECT name FROM branches WHERE id=".$data[1]);
    $com   = mysql_fetch_array($query);
    $excelContent  .= "\n".MENU_BRANCH.': '.$com[0];
}
if($data[2]!='') {
    $query  = mysql_query("SELECT name FROM pgroups WHERE id=".$data[2]);
    $pgroup = mysql_fetch_array($query);
    $excelContent   .= "\n".MENU_PRODUCT_GROUP_MANAGEMENT.': '.$pgroup[0];
}
if($data[3]!='') {
    $query   = mysql_query("SELECT username FROM users WHERE id=".$data[3]);
    $created = mysql_fetch_array($query);
    $excelContent    .= "\n".TABLE_CREATED_BY.': '.$created[0];
}
$excelContent .= "\n".TABLE_NO."\t".TABLE_SKU."\t".TABLE_BARCODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_UOM;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */


/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('p.id', 'p.code', 'p.barcode', 'p.name', 'uoms.abbr', 'p.price_uom_id', 'p.unit_cost');
// Menu Price List
$sqlPList = mysql_query("SELECT id, name FROM price_types WHERE is_active = 1 ORDER BY ordering = 1");
$countList = mysql_num_rows($sqlPList) + 2;
while($rowPList = mysql_fetch_array($sqlPList)){
    $aColumns[] = "'price-".$rowPList[0]."'";
    $excelContent .= "\t".$rowPList[1];
}
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
$sTable = "products p INNER JOIN uoms ON uoms.id = p.price_uom_id";

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
    for ($i = 0; $i < count($aColumns) - $countList; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - $countList; $i++) {
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
$condition = "p.is_active=1";
if ($data[0] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.company_id=' . $data[0];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.id IN (SELECT product_id FROM product_branches WHERE branch_id = ' . $data[1] . ')';
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.id IN (SELECT product_id FROM product_branches WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].'))';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = '.$data[2].')';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.created_by=' . $data[3];
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
$groupBy = 'GROUP BY p.id';
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
            $excelContent .= "\n".$index;
        } else if ($aColumns[$i] == 'p.price_uom_id' || $aColumns[$i] == 'p.unit_cost' ) {
        } else if ($i > 6) {
            $explode = explode("-",$aRow[$i]);
            $unitCost = $aRow[6];
            $unitPrice = 0;
            if ($data[1] != '') {
                $sqlPrice = mysql_query("SELECT * FROM product_prices WHERE product_id = ".$aRow[0]." AND branch_id = ".$data[1]." AND price_type_id = ".$explode[1]." AND uom_id = ".$aRow[5]." LIMIT 1;");
            } else {
                $sqlPrice = mysql_query("SELECT * FROM product_prices WHERE product_id = ".$aRow[0]." AND price_type_id = ".$explode[1]." AND uom_id = ".$aRow[5]." LIMIT 1;");
            }
            $rowPrice = mysql_fetch_array($sqlPrice);
            if($rowPrice['set_type'] == 1){
                $unitPrice = $rowPrice['amount'];
            } else if($rowPrice['set_type'] == 2){
                $percent   = ($unitCost * $rowPrice['percent']) / 100;
                $unitPrice = $unitCost + $percent;
            } else {
                $unitPrice = $unitCost + $rowPrice['add_on'];
            }
            $row[] = number_format($unitPrice, 2);
            $excelContent .= "\t".number_format($unitPrice, 2);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
echo json_encode($output);
?>