<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("sales_orders.id", 
                  "sales_orders.order_date", 
                  "sales_orders.so_code",
                  "CONCAT_WS(' - ',customers.customer_code,CONCAT(customers.name_kh,' (',customers.name,')'))", 
                  "sales_orders.total_amount-IFNULL(sales_orders.discount,0)", 
                  "sales_orders.balance",
                  "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END", 
                  "sales_orders.is_pos");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.id";

/* DB table to use */
$sTable = " sales_orders
            LEFT JOIN customers ON customers.id = sales_orders.customer_id ";


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

/* Customize condition */
$condition = "status>0 AND balance>0 AND ar_id=" . $chartAccountId . " AND company_id=" . $companyId . " AND branch_id=" . $branchId . " AND customer_id=" . $customerId;

if (isset($cg)) {
    $condition .= " AND customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id='" . $cg . "') ";
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
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = '<input type="checkbox" value="' . $aRow[0] . '" name="chkInvoice[]" data-name="' . $aRow[2] . '" />';
        } else if ($aColumns[$i] == 'sales_orders.order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == "CONCAT_WS(' - ',customers.customer_code,CONCAT(customers.name_kh,' (',customers.name,')'))") {
            if (trim($aRow[$i]) == '') {
                $row[] = 'General Customer';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'sales_orders.so_code') {
            $row[] = $aRow[$i];
        } else if ($aColumns[$i] == "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END") {
            $row[] = '<input type="text" name="invoice_price[]" value="0" class="float clkInvCheck" readonly="readonly" style="width:80px;" /><input type="hidden" value="'.$aRow[5].'" class="total_amount" />';
        } else if ($aColumns[$i] == 'sales_orders.is_pos') {
            $isPos = $aRow[$i];
            if ($isPos) {
                $row[] = "POS";
            } else {
                $row[] = "លក់ដុំ";
            }
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
            if ($aColumns[$i] == "sales_orders.balance") {
                if ($aRow[$i] <= 0) {
                    $allowAging = false;
                }
            }
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>