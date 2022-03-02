<?php

// Function
include('includes/function.php');
$filename="public/report/product_avg_cost_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$excelContent = MENU_PRODUCT_AVERAGE_COST . "\n\n";
if($data[0]!='') {
    $query = mysql_query("SELECT name FROM companies WHERE id=".$data[0]);
    $com   = mysql_fetch_array($query);
    $excelContent  .= "\n".TABLE_COMPANY.': '.$com[0];
}
if($data[1]!='') {
    $query  = mysql_query("SELECT name FROM pgroups WHERE id=".$data[1]);
    $pgroup = mysql_fetch_array($query);
    $excelContent   .= "\n".MENU_PRODUCT_GROUP_MANAGEMENT.': '.$pgroup[0];
}
if($data[2]!='') {
    $query   = mysql_query("SELECT username FROM users WHERE id=".$data[2]);
    $created = mysql_fetch_array($query);
    $excelContent    .= "\n".TABLE_CREATED_BY.': '.$created[0];
}
$excelContent .= "\n".TABLE_NO."\t".TABLE_SKU."\t".TABLE_BARCODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_UOM."\t".TABLE_LAST_COST."\t".TABLE_AVERAGE_COST;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'p.id',
    'p.code',
    'p.barcode',
    'p.name',
    'uoms.name',
    'p.unit_cost',
    '(SELECT avg_cost FROM inventory_valuations WHERE is_active=1 AND pid=p.id ORDER BY date DESC,created DESC,id DESC LIMIT 1)',
    'CONCAT_WS(" ", users.first_name, users.last_name)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
$sTable = "products p INNER JOIN uoms ON uoms.id=p.price_uom_id INNER JOIN users ON users.id = p.created_by";

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
    $condition .= 'p.id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ' . $data[1].')';
}

if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.created_by=' . $data[2];
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
$tmpId = '';
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
            $excelContent .= "\n".$index;
        } else if ($i == 5 || $i == 6) {
            $row[] = number_format($aRow[$i], 2);
            $excelContent .= "\t".number_format($aRow[$i], 2);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
    $tmpId = $aRow[0];
}
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
echo json_encode($output);
?>