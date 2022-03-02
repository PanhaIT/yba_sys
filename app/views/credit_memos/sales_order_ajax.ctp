<?php
include("includes/function.php");
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('CONCAT_WS("||",sales_orders.id,customers.id,sales_orders.price_type_id,IFNULL(sales_orders.discount, 0),IFNULL(sales_orders.discount_percent, 0),sales_orders.vat_setting_id,sales_orders.vat_calculate,IFNULL(sales_orders.vat_percent, 0),sales_orders.vat_chart_account_id)', 
                  'sales_orders.order_date', 
                  'sales_orders.so_code', 
                  'customers.customer_code', 
                  'customers.name', 
                  '(SELECT name FROM location_groups WHERE id = sales_orders.location_group_id)',
                  'sales_orders.total_amount + IFNULL(sales_orders.total_vat,0) - IFNULL(sales_orders.discount,0)',
                  'currency_centers.symbol');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.id";

/* DB table to use */
$sTable = "sales_orders 
           INNER JOIN customers ON customers.id = sales_orders.customer_id 
           INNER JOIN `customer_cgroups` as gr ON customers.id = gr.customer_id INNER JOIN `cgroups` ON cgroups.id = gr.cgroup_id AND (cgroups.user_apply = 0 OR {$user['User']['id']} IN (SELECT user_id FROM user_cgroups WHERE cgroup_id = cgroups.id AND user_cgroups.user_id))
           INNER JOIN currency_centers ON currency_centers.id = sales_orders.currency_center_id";



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
    for ($i = 0; $i < (count($aColumns) - 2); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < (count($aColumns) - 2); $i++) {
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

$condition = "sales_orders.status = 2 AND sales_orders.company_id = ".$companyId." AND sales_orders.branch_id = ".$branchId;
if(!empty($customerId)){
    $condition .= " AND sales_orders.customer_id = ".$customerId;
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
$groupBy = 'GROUP BY sales_orders.id';
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
    $record = explode("||", $aRow[0]);
    $pType = "";
    $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$record[1]." GROUP BY cgroup_id) GROUP BY price_type_id");
    if(mysql_num_rows($sqlPriceType)){
        $rowPriceType = mysql_fetch_array($sqlPriceType);
        $pType = $rowPriceType[0];
    }
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $index ++;
            $row[] = '<input type="radio" value="'.$record[0].'" dis="'.number_format($record[3], 2).'" disp="'.number_format($record[4], 2).'" vid="'.$record[5].'" vcal="'.$record[6].'" vper="'.number_format($record[7], 2).'" vacid="'.$record[8].'" name="chkCMSalesOrder" code="'.$aRow[2].'" cus-num="'.$aRow[3].'" cus-name="'.$aRow[4].'" cus-id="'.$record[1].'" rel="'.dateShort($aRow[1]).'" ptype-id="'.$record[2].'" ptype="'.$pType.'" />' ;
        } else if ($i == 1) {
            if($aRow[$i] != '' && $aRow[$i] != '0000-00-00'){
                $row[] = dateShort($aRow[$i]);
            }else{
                $row[] = "";
            }
        } else if ($aColumns[$i] == 'sales_orders.total_amount + IFNULL(sales_orders.total_vat,0) - IFNULL(sales_orders.discount,0)') {
            $row[] = $aRow[7]." ".number_format($aRow[$i],2);
        } else if ($i == 7) {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>