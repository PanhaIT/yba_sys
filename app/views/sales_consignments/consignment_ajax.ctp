<?php
include("includes/function.php");
header("Content-type: text/plain");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('consign.id', 
                    'consign.code', 
                    'consign.date', 
                    'cus.name', 
                    'consign.total_amount', 
                    "currency_centers.symbol",
                    "cus.id",
                    "IFNULL(consign.price_type_id,0)",
                    "consign.location_group_to_id");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "consign.id";

/* DB table to use */
$sTable = "consignments as consign INNER JOIN `customers` AS cus ON cus.id = consign.customer_id INNER JOIN `customer_cgroups` as gr ON cus.id = gr.customer_id INNER JOIN `cgroups` ON cgroups.id = gr.cgroup_id AND (cgroups.user_apply = 0 OR {$user['User']['id']} IN (SELECT user_id FROM user_cgroups WHERE cgroup_id = cgroups.id AND user_cgroups.user_id)) INNER JOIN currency_centers ON currency_centers.id = consign.currency_center_id";



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
    for ($i = 0; $i < (count($aColumns) - 5); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 5); $i++) {
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
$condition = "consign.status = 2 AND consign.company_id =".$companyId." AND consign.branch_id =".$branchId;
if(!empty($customerId)){
    $condition .= " AND consign.customer_id = ".$customerId;
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
$groupBy = "GROUP BY consign.id";
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
    $sqlCus = mysql_query("SELECT * FROM customers WHERE id = ".$aRow[6]);
    $rowCus = mysql_fetch_array($sqlCus);
    $pType  = "";
    $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$aRow[6]." GROUP BY cgroup_id) GROUP BY price_type_id");
    if(mysql_num_rows($sqlPriceType)){
        $rowPriceType = mysql_fetch_array($sqlPriceType);
        $pType = $rowPriceType[0];
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index ++;
            $row[] = '<input type="radio" value="'.$aRow[0].'" location-group="'.$aRow[8].'" cus-id="'.$aRow[6].'" cus-code="'.$rowCus['customer_code'].'" limit-balance="0" limit-invoice="0" inv-used="0" bal-used="0" name-kh="'.$rowCus['name_kh'].'" name-us="'.$rowCus['name'].'" term-id="'.$rowCus['payment_term_id'].'" name="chkConsignment" ptype-id="'.$aRow[7].'" rel="'.$aRow[1].'" ptype="'.$pType.'" />' ;
        } else if($i == 2){
            $row[] = dateShort($aRow[$i]);
        } else if($i == 4){
            $row[] = $aRow[5]." ".number_format($aRow[$i], 2);
        } else if($i == 5 || $i == 6 || $i == 7 || $i == 8){
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>