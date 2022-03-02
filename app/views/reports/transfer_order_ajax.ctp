<?php

// Function
include('includes/function.php');
/**
 * Export to Excel
 */
$filename="public/report/transfer_order" . $user['User']['id'] . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$excelContent .= MENU_REPORT_TRANSFER_ORDER."\n";
if($data[1]!='') {
    $excelContent .= REPORT_FROM.': '.str_replace("|||", "/", $data[1]);
}
if($data[2]!='') {
    $excelContent .= ' '.REPORT_TO.': '.str_replace("|||", "/", $data[2]);
}
$excelContent .= "\n".TABLE_NO."\t".TABLE_TO_DATE."\t".TABLE_TO_NUMBER."\t".MENU_BRANCH."\t".TABLE_FROM_WAREHOUSE."\t".TABLE_TO_WAREHOUSE."\t".TABLE_TOTAL_COST."\t".TABLE_STATUS;
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('id', 
                  'order_date', 
                  'to_code',
                  '(SELECT name FROM branches WHERE id=branch_id)',
                  '(SELECT name FROM location_groups WHERE id=from_location_group_id)', 
                  '(SELECT name FROM location_groups WHERE id=to_location_group_id)', 
                  'total_cost', 
                  'status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

/* DB table to use */
$sTable = "transfer_orders";

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
$condition = '';
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
    $condition .= 'status>-1';
} else {
    $condition .= 'status=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id = ' . $data[4];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id = ' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'from_location_group_id=' . $data[6];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'from_location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].') ';
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'to_location_group_id=' . $data[7];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'to_location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].') ';
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'created_by=' . $data[8];
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
$total = 0;
$index = $_GET['iDisplayStart'];
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
            $excelContent .= "\n".$index;
        } else if ($aColumns[$i] == 'to_code') {
            $row[] = '<a href="" class="btnPrintTOReport" rel="' . $aRow[0] . '" cost="'.$aRow[6].'">' . $aRow[$i] . '</a>';
            $excelContent .= "\t".$aRow[$i];
        } else if ($aColumns[$i] == 'order_date') {
            if ($aRow[$i] != '0000-00-00') {
                $row[] = dateShort($aRow[$i]);
                $excelContent .= "\t".dateShort($aRow[$i]);
            } else {
                $row[] = '';
                $excelContent .= "\t";
            }
        } else if ($aColumns[$i] == 'total_cost') {
            $row[] = number_format($aRow[$i], 2);
            $excelContent .= "\t".number_format($aRow[$i], 2);
            $total += $aRow[$i];
        } else if ($aColumns[$i] == 'status') {
            $status = "";
            switch ($aRow[$i]) {
                case 0:
                    $status = "Void";
                    break;
                case 1:
                    $status = "Issued";
                    break;
                case 2:
                    $status = "Partial";
                    break;
                case 3:
                    $status = "Fulfilled";
                    break;
            }
            $row[] = $status;
            $excelContent .= "\t".$status;
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t".$aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}
$excelContent .= "\n\t\t\t\t\t".TABLE_TOTAL."\t".number_format($total, 2);
$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

echo json_encode($output);
?>