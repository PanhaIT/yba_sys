<?php

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array("sales_orders.id",
    "(SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1))",
    "sales_orders.order_date",
    "sales_orders.so_code",
    "sales_orders.memo",
    "CONCAT_WS(' ', customers.customer_code, ' - ', customers.name)",
    "IF(balance>0 AND DATEDIFF(now(),due_date)>0,DATEDIFF(now(),due_date),'.')",
    "sales_orders.total_amount-IFNULL(sales_orders.discount,0)+IFNULL(sales_orders.total_vat,0)",
    "sales_orders.balance",
    "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END",
    "currency_centers.symbol");

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "sales_orders.order_date";

/* DB table to use */
$sTable = " sales_orders INNER JOIN customers ON customers.id = sales_orders.customer_id INNER JOIN currency_centers ON currency_centers.id = sales_orders.currency_center_id";

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

    $sOrder = str_replace("(SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) asc", "(SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) asc, sales_orders.order_date asc, sales_orders.id asc", $sOrder);
    $sOrder = str_replace("(SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) desc", "(SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) desc, sales_orders.order_date desc, sales_orders.id desc", $sOrder);
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
    for ($i = 0; $i < count($aColumns) - 2; $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 2; $i++) {
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
$condition = 'is_pos IN (0,2)';
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= DATE(order_date)';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= DATE(order_date)';
}
$condition != '' ? $condition .= ' AND ' : '';
if ($data[3] == '') {
    $condition .= 'status!=0';
} else {
    $condition .= 'status=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id=' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id=' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'location_group_id=' . $data[6];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '((sales_orders.is_pos IN (0, 1) AND sales_orders.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')) OR sales_orders.is_pos = 2)';
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id=' . $data[7] . ')';
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'customer_id=' . $data[8];
}
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'sales_orders.created_by=' . $data[9];
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

$index = 0;
$tmpId = '$';
$tmpName = '';
$amount = 0;
$balance = 0;
$amountTotal = 0;
$balanceTotal = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    if ($index != 0 && $aRow[1] != $tmpId) {
        $index = 0;
        $rowTotal = array();
        $rowTotal[] = '<b class="colspanParent"><b class="colspanParent"><img class="btnPlusMinusInvoiceByRep" rel="' . $tmpId . '" alt="" style="cursor: pointer;" src="' . $this->webroot . 'img/plus.gif" /> Total ' . $tmpName . '</b>';
        for ($i = 0; $i < count($aColumns) - 6; $i++) {
            $rowTotal[] = '';
        }
        $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
        $rowTotal[] = '<b>' . number_format($balance, 2) . '</b>';
        $rowTotal[] = '';
        $rowTotal[] = '';
        $output['aaData'][] = $rowTotal;
    }
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if ($aRow[1] == $tmpId) {
                $row[] = '<b class="rowInvoiceByRep" rel="' . $aRow[1] . '">' . ++$index . '</b>';
            } else {
                if (!is_null($aRow[1])) {
                    $tmpName = $aRow[1];
                } else {
                    $tmpName = '';
                }
                $row[] = '<b class="rowInvoiceByRep colspanParent" rel="' . $aRow[1] . '">' . $tmpName . '</b>';
                for ($j = 0; $j < count($aColumns) - 2; $j++) {
                    $row[] = '';
                }
                $output['aaData'][] = $row;
                $row = array();
                $row[] = '<b class="rowInvoiceByRep" rel="' . $aRow[1] . '">' . ++$index . '</b>';
            }
        } else if ($i == 1) {

        } else if ($aColumns[$i] == 'sales_orders.order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
                $saleOrderDate = $aRow[$i];
            } else {
                $row[] = '';
            }
        } else if ($aColumns[$i] == 'sales_orders.so_code') {
            $row[] = '<a href="" class="btnPrintCustomerInvoiceByRep" rel="' . $aRow[0] . '">' . $aRow[$i] . '</a>';
        } else if ($aColumns[$i] == "CASE sales_orders.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfilled' END") {
            $row[] = $aRow[$i];
        } else if ($i == 7) {
            $row[] = $aRow[10]." ".number_format($aRow[$i], 2);
            if ($aRow[1] == $tmpId) {
                $amount += $aRow[$i];
            } else {
                $amount = $aRow[$i];
            }
            $amountTotal += $aRow[$i];
        } else if ($i == 8) {
            $row[] = $aRow[10]." ".number_format($aRow[$i], 2);
            if ($aRow[1] == $tmpId) {
                $balance += $aRow[$i];
            } else {
                $balance = $aRow[$i];
            }
            $balanceTotal += $aRow[$i];
        } else if ($i == 10) {
        } else if ($aColumns[$i] != '') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}
if (mysql_num_rows($rResult)) {
    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent"><b class="colspanParent"><img class="btnPlusMinusInvoiceByRep" rel="' . $tmpId . '" alt="" style="cursor: pointer;" src="' . $this->webroot . 'img/plus.gif" /> Total ' . $tmpName . '</b>';
    for ($i = 0; $i < count($aColumns) - 6; $i++) {
        $rowTotal[] = '';
    }
    $rowTotal[] = '<b>' . number_format($amount, 2) . '</b>';
    $rowTotal[] = '<b>' . number_format($balance, 2) . '</b>';
    $rowTotal[] = '';
    $rowTotal[] = '';
    $output['aaData'][] = $rowTotal;

    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">GRAND TOTAL</b>';
    for ($i = 0; $i < count($aColumns) - 6; $i++) {
        $rowTotal[] = '';
    }
    $rowTotal[] = '<b>' . number_format($amountTotal, 2) . '</b>';
    $rowTotal[] = '<b>' . number_format($balanceTotal, 2) . '</b>';
    $rowTotal[] = '';
    $rowTotal[] = '';
    $output['aaData'][] = $rowTotal;
}

echo json_encode($output);
?>