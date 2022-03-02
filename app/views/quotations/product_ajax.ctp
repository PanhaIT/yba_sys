<?php

include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('products.id', 'products.code', 'products.name', 'IFNULL(u.name, "Packet")', 'products.price_uom_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "products.id";

/* DB table to use */
if ($category) {
    $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN uoms AS u ON u.id = products.price_uom_id INNER JOIN product_pgroups ON product_pgroups.product_id = products.id AND product_pgroups.pgroup_id = ".$category." INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
} else {
    $sTable = "products INNER JOIN product_branches ON product_branches.product_id = products.id AND product_branches.branch_id = ".$branchId." LEFT JOIN uoms AS u ON u.id = products.price_uom_id INNER JOIN product_pgroups ON product_pgroups.product_id = products.id INNER JOIN pgroups ON pgroups.id = product_pgroups.pgroup_id AND (pgroups.user_apply = 0 OR (pgroups.user_apply = 1 AND pgroups.id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id'].")))";
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
$condition = "products.is_active = 1 AND ((products.price_uom_id IS NOT NULL AND products.is_packet = 0) OR (products.price_uom_id IS NULL AND products.is_packet = 1)) AND ((is_not_for_sale = 0 AND period_from IS NULL AND period_to IS NULL) OR (is_not_for_sale = 0 AND period_from <= '".$orderDate."' AND period_to >= '".$orderDate."') OR (is_not_for_sale = 1 AND period_from IS NOT NULL AND period_to IS NOT NULL AND '".$orderDate."' NOT BETWEEN period_from AND period_to))";
if(!empty($companyId)){
    $condition .= " AND products.company_id=".$companyId;
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
$sGroup = " GROUP BY products.id ";
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
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
    $sqlLastQuote = mysql_query("SELECT quotation_details.unit_price, uoms.abbr FROM quotation_details INNER JOIN quotations ON quotations.id = quotation_details.quotation_id AND quotations.status > 0 INNER JOIN uoms ON uoms.id = quotation_details.qty_uom_id WHERE quotation_details.product_id = ".$aRow[0]." ORDER BY quotation_details.id DESC LIMIT 1");
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index++;
            $row[] = '<input type="radio" value="'.htmlspecialchars($aRow[1], ENT_QUOTES, 'UTF-8').'" name="chkProduct" />';
        } else if ($aColumns[$i] == 'products.code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == 'products.price_uom_id') {
            if(mysql_num_rows($sqlLastQuote)){
                $rowQuote  = mysql_fetch_array($sqlLastQuote);
                $lastPrice = number_format($rowQuote[0], 2)." $ / ".$rowQuote[1];
            } else {
                $lastPrice = "0.00 $";
            }
            $row[] = $lastPrice;
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>