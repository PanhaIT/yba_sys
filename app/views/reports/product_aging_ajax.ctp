<?php

// Function
include('includes/function.php');

$filename="public/report/product_exp_aging_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$excelContent = MENU_PRODUCT_AGING . "\n\n";
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
$excelContent .= "\n".TABLE_NO."\t".TABLE_SKU."\t".TABLE_BARCODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_EXPIRED_DATE."\t".TABLE_QTY."\t".TABLE_CREATED."\t".REPORT_DAYS;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('p.id', 'code', 'barcode', 'name', 'date_expired', 'SUM(qty)', 'date', 'CONCAT(DATEDIFF(date_expired,CURDATE())," day(s)")', 'p.price_uom_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
if ($data[1] != '') {
    $sTable = "products p INNER JOIN inventories i ON p.id=i.product_id INNER JOIN product_pgroups pg ON p.id = pg.product_id AND pg.pgroup_id = $data[1] ";
} else {
    $sTable = "products p INNER JOIN inventories i ON p.id=i.product_id";
}

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

/* Customize condition */
$condition = "p.is_active=1";
if ($data[0] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.company_id=' . $data[0];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
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
$groupBy = " GROUP BY p.id, date_expired HAVING SUM(qty) > 0";
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
$tmpId = '';
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $smallUom = mysql_query("SELECT abbr FROM uoms WHERE id = IFNULL((SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = ".$aRow[8]." AND is_small_uom = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1), ".$aRow[8].")");
    $rowUom = mysql_fetch_array($smallUom);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
            $excelContent .= "\n".$index;
        } else if ($aColumns[$i] == 'date_expired' || $aColumns[$i] == 'date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
                $excelContent .= "\t".dateShort($aRow[$i]);
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 'SUM(qty)') {
            $row[] = number_format($aRow[$i],2)." ".$rowUom[0];
            $excelContent .= "\t".number_format($aRow[$i],2)." ".$rowUom[0];
        } else if ($aColumns[$i] == 'p.price_uom_id') {
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