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
$aColumns = array('CONCAT_WS("||",quo.id,cus.id,IFNULL(quo.discount,0),IFNULL(quo.discount_percent,0),IFNULL(quo.total_deposit,0),IFNULL(quo.price_type_id,0),quo.vat_setting_id,quo.vat_calculate,IFNULL(quo.vat_percent, 0),IFNULL(quo.customer_contact_id, 0))', 
                    'quo.quotation_code', 
                    'quo.quotation_date', 
                    'cus.name', 
                    'quo.total_amount', 
                    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "quo.id";

/* DB table to use */
$sTable = "quotations as quo INNER JOIN `customers` AS cus ON cus.id = quo.customer_id INNER JOIN `customer_cgroups` as gr ON cus.id = gr.customer_id INNER JOIN `cgroups` ON cgroups.id = gr.cgroup_id AND (cgroups.user_apply = 0 OR {$user['User']['id']} IN (SELECT user_id FROM user_cgroups WHERE cgroup_id = cgroups.id AND user_cgroups.user_id)) INNER JOIN currency_centers ON currency_centers.id = quo.currency_center_id";



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
$condition = "quo.status = 1 AND quo.is_close = 0 AND quo.is_approve = 1 AND quo.company_id =".$companyId." AND quo.branch_id =".$branchId;
if(!empty($customerId)){
    $condition .= " AND quo.customer_id = ".$customerId;
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
$groupBy = "GROUP BY quo.id";
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
$saleCon = "";
if($saleId > 0){
    $saleCon = " AND id != ".$saleId;
}
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $record = explode("||", $aRow[0]);
    $sqlCus = mysql_query("SELECT * FROM customers WHERE id = ".$record[1]);
    $rowCus = mysql_fetch_array($sqlCus);
    $limitBalance = $rowCus['limit_balance'];
    $limitInvoice = $rowCus['limit_total_invoice'];
    $totalInvUsed = 0;
    $totalAmoUsed = 0;
    if($limitBalance > 0 || $limitInvoice > 0){
        // Query Total Invoice & Total Amount Not Pay
        $queryCon = mysql_query("SELECT COUNT(id) AS total_invoice, SUM(total_amount) AS total_amount FROM sales_orders WHERE customer_id = ".$record[1]." AND status > 0 AND balance > 0".$saleCon." GROUP BY customer_id");
        $dataCon = mysql_fetch_array($queryCon);
        // Check Limit Invoice
        if(@mysql_num_rows($queryCon)){
            $totalInvUsed = $dataCon['total_invoice'];
            $totalAmoUsed = $dataCon['total_amount'];
        }
    }
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
            $row[] = '<input type="radio" value="'.$record[0].'" vid="'.$record[6].'" vper="'.number_format($record[8], 2).'" vcal="'.$record[7].'" cus-id="'.$record[1].'" cus-code="'.$rowCus['customer_code'].'" cus-con="'.$record[9].'" limit-balance="'.$limitBalance.'" limit-invoice="'.$limitInvoice.'" inv-used="'.($totalInvUsed + 1).'" bal-used="'.$totalAmoUsed.'" photo="'.$rowCus['photo'].'" name-kh="'.$rowCus['name_kh'].'" name-us="'.$rowCus['name'].'" term-id="'.$rowCus['payment_term_id'].'" name="chkQuotation" dis="'.$record[2].'"  dis-per="'.$record[3].'" deposit="'.$record[4].'" ptype-id="'.$record[5].'" rel="'.$aRow[1].'" ptype="'.$pType.'" />' ;
        } else if($i == 2){
            $row[] = dateShort($aRow[$i]);
        } else if($i == 4){
            $row[] = $aRow[5]." ".number_format($aRow[$i], 2);
        } else if($i == 5){
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>