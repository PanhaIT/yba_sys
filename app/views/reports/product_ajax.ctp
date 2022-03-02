<?php

// Authentication
$this->element('check_access');
$allowViewCost = checkAccess($user['User']['id'], 'products', 'viewCost');

// Function
include('includes/function.php');

/**
 * export to excel
 */
$filename = "public/report/global_inventory_" . $user['User']['id'] . ".csv";
$fp = fopen($filename, "wb");
$excelContent = MENU_PRODUCT_INVENTORY . "\n\n";
if ($data[1] != '') {
    $excelContent .= 'Beginning: ' . str_replace('|||', '/', $data[1]);
}
if ($data[2] != '') {
    $excelContent .= ' Ending: ' . str_replace('|||', '/', $data[2]);
}
if ($data[4] != '' || $data[5] != '' || $data[6] != '' || $data[7] != '') {
    $excelContent .= "\n\n";
}
if ($data[4] != '') {
    $queryLabel = mysql_query("SELECT name FROM location_groups WHERE id=" . $data[4]);
    $dataLabel = mysql_fetch_array($queryLabel);
    $excelContent .= TABLE_LOCATION_GROUP . ': ' . $dataLabel[0] . ' ';
}
if ($data[5] != '') {
    $queryLabel = mysql_query("SELECT name FROM locations WHERE id=" . $data[5]);
    $dataLabel = mysql_fetch_array($queryLabel);
    $excelContent .= TABLE_LOCATION . ': ' . $dataLabel[0] . ' ';
}
if ($data[7] != '') {
    $queryLabel = mysql_query("SELECT name FROM pgroups WHERE id=" . $data[7]);
    $dataLabel = mysql_fetch_array($queryLabel);
    $excelContent .= MENU_PRODUCT_GROUP_MANAGEMENT . ': ' . $dataLabel[0] . ' ';
}
if ($data[8] != '') {
    $queryLabel = mysql_query("SELECT name FROM products WHERE id=" . $data[8]);
    $dataLabel = mysql_fetch_array($queryLabel);
    $excelContent .= TABLE_PRODUCT . ': ' . $dataLabel[0] . ' ';
}
if (!$allowViewCost) {
    $excelContent .= "\n\n" . TABLE_NO . "\t" . TABLE_BARCODE . "\t" . TABLE_NAME . "\t" . TABLE_UOM . "\t" . "Beginning Qty" . "\t" . "Ending Qty" . "\t" . "Qty Order" . "\t" . TABLE_QTY_AVAILABLE;
    $space = 7;
} else {
    $excelContent .= "\n\n" . TABLE_NO . "\t" . TABLE_BARCODE . "\t" . TABLE_NAME . "\t" . TABLE_UOM . "\t" . TABLE_LAST_COST . " ($)" . "\t Beginning Qty" . "\t" . "Ending Qty" . "\t" . "Qty Order" . "\t" . TABLE_QTY_AVAILABLE;
    $space = 6;
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$tableDaily = "";
$fieldDaily = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in + total_ven_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out + total_ven_consign_out)) AS total_qty, product_id";
$conditionDaily = "date";
$groupByDaily = "GROUP BY product_id";
$tableInventory = "";
$conditionGetLocation = "";
// Total Order
$fieldOrder = "SUM(total_order) AS total_order";
$tableOrder = "inventory_totals";
$conOrder   = "";
// Get Location Group
if ($data[4] != '') {
    $conditionGetLocation .= " = ".$data[4];
    $tableOrder = $data[4]."_group_total_details";
    $conOrder   = " AND date<='".dateConvert(str_replace("|||", "/", $data[2]))."'";
}else{
    $conditionGetLocation .= " IN (SELECT user_location_groups.location_group_id FROM user_location_groups INNER JOIN location_groups ON location_groups.id = user_location_groups.location_group_id AND location_groups.location_group_type_id != 1 WHERE user_location_groups.user_id = ".$user['User']['id']." GROUP BY user_location_groups.location_group_id)";
}
// Get Location 
if($data[5] != ''){
    $tableOrder = $data[5]."_inventory_total_details";
    $conOrder   = " AND date<='".dateConvert(str_replace("|||", "/", $data[2]))."'";
    $conditionGetLocation .= " AND id = ".$data[5];
}

// List Location 
$queryLocationList = mysql_query('SELECT id FROM locations WHERE location_group_id '. $conditionGetLocation.' GROUP BY id');
if(@mysql_num_rows($queryLocationList)){
    if(mysql_num_rows($queryLocationList) == 1){
        while($dataLocationList=mysql_fetch_array($queryLocationList)){
            // Stock Daily
            $tableDaily .= "SELECT SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in + total_ven_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out + total_ven_consign_out)) AS total_qty FROM ".$dataLocationList['id']."_inventory_total_details WHERE product_id = p.id AND ".$conditionDaily." ".$groupByDaily;

            // Inventory Total
            $tableInventory .= "(SELECT SUM(total_qty) AS total_qty FROM ".$dataLocationList['id']."_inventory_totals WHERE ".$dataLocationList['id']."_inventory_totals.product_id = p.id GROUP BY ".$dataLocationList['id']."_inventory_totals.product_id)";
        }
        $selectDaily = "(".$tableDaily.")";
        $selectInventory = $tableInventory;
    }else{
        $locId = 1;
        $tmpLocationId = 0;
        while($dataLocationList=mysql_fetch_array($queryLocationList)){
            if($locId == 1){
                // Stock Daily
                $tableDaily .= "SELECT ".$fieldDaily." FROM ".$dataLocationList['id']."_inventory_total_details WHERE ".$conditionDaily." ".$groupByDaily;

                // Inventory Total
                $tableInventory .= "SELECT product_id, SUM(total_qty) AS total_qty FROM ".$dataLocationList['id']."_inventory_totals GROUP BY ".$dataLocationList['id']."_inventory_totals.product_id";
            }else{
                // Stock Daily
                $tableDaily .= " UNION ALL SELECT ".$fieldDaily." FROM ".$dataLocationList['id']."_inventory_total_details WHERE ".$conditionDaily." ".$groupByDaily;

                // Inventory Total
                $tableInventory .= " UNION ALL SELECT product_id, SUM(total_qty) AS total_qty FROM ".$dataLocationList['id']."_inventory_totals GROUP BY ".$dataLocationList['id']."_inventory_totals.product_id";
            }
            $tmpLocationId = $dataLocationList['id'];
            $locId++;
        }
        $selectDaily = "(SELECT SUM(total_qty) FROM (".$tableDaily.") AS total_qty WHERE product_id = p.id)";
        $selectInventory = "(SELECT SUM(total_qty) FROM (".$tableInventory.") AS inventory WHERE product_id = p.id)";
    }
}

$dateFrom = "date<'".dateConvert(str_replace("|||", "/", $data[1]))."'";
$dateTo   = "date<='".dateConvert(str_replace("|||", "/", $data[2]))."'";
$tableDailyBeginning = str_replace("date", $dateFrom, $selectDaily);
$tableDailyEnding    = str_replace("date", $dateTo, $selectDaily);
$aColumns = array(
    'p.id',
    "pgroups.name",
    'p.barcode',
    'p.name',
    'p.price_uom_id',
    'IFNULL(p.unit_cost,0)',
    'IFNULL('.$tableDailyBeginning.',0)',
    'IF("' . (dateConvert(str_replace("|||", "/", $data[2]))) . '"=DATE(NOW()),
        IFNULL(
            '.$selectInventory.',0),
        IFNULL(
            '.$tableDailyEnding.',0)
    )',
    '(SELECT '.$fieldOrder.' FROM '.$tableOrder.' WHERE product_id=p.id'.$conOrder.')');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
$sTable = "products p INNER JOIN product_pgroups pg ON p.id = pg.product_id INNER JOIN pgroups ON pgroups.id = pg.pgroup_id";

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
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }

    $sOrder = str_replace("(SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=p.id)) asc", "(SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=p.id)) asc, p.code asc", $sOrder);
    $sOrder = str_replace("(SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=p.id)) desc", "(SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=p.id)) desc, p.code desc", $sOrder);
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
for ($i = 0; $i < count($aColumns) - 5; $i++) {
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
$condition = "p.is_active=1 AND p.is_packet = 0 AND p.id IN (SELECT product_id FROM product_branches WHERE branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']."))";
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    if ($data[3] == '0') {
        $condition .= '(IFNULL('.$tableDailyBeginning.',0)=0
        AND
                        IF("' . (dateConvert(str_replace("|||", "/", $data[2]))) . '"=DATE(NOW()),
                            IFNULL(
                                '.$selectInventory.',0),
                            IFNULL(
                                '.$tableDailyEnding.',0)
                        )=0)';
    } else {
        $condition .= '(IFNULL('.$tableDailyBeginning.',0)!=0
        OR
                        IF("' . (dateConvert(str_replace("|||", "/", $data[2]))) . '"=DATE(NOW()),
                            IFNULL(
                                '.$selectInventory.',0),
                            IFNULL(
                                '.$tableDailyEnding.',0)
                        )!=0)';
    }
}

if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'pgroups.department_id =' . $data[6];
}

if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    // Category
    $sqlSC      = mysql_query("SELECT IFNULL(GROUP_CONCAT(id), 0) FROM pgroups WHERE parent_id = ".$data[7]);
    $rowSC      = mysql_fetch_array($sqlSC);
    $parentId   = $rowSC[0];
    $condition .= 'pgroups.id IN (SELECT id FROM pgroups WHERE parent_id IN ('.$parentId.'))';
}

if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.id=' . $data[8];
}

if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'p.id IN (SELECT product_id FROM product_vendors WHERE vendor_id = ' . $data[9]. ')';
}

$sortItem = 'ASC';
if ($data[10] != '') {
    if($data[10]==1){
        $sortItem = 'ASC';
    }else{
        $sortItem = 'DESC';
    }
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
$sOrder="";
$sOrder=" ORDER BY pgroups.name ASC,p.name ".$sortItem ;
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
$totalParentQtyBeg = 0;
$totalParentQtyEnd = 0;
$totalParentQtyOrder = 0;
$totalQtyBeg = 0;
$totalQtyEnd = 0;
$totalQtyOrder = 0;
$totalQtyAvailable = 0;

$grandTotalQtyAvailable=0;
$grandTotalQtyOrder=0; 
$grandTotalQtyEnd=0; 
$grandTotalQtyBeg=0;
while ($aRow = mysql_fetch_array($rResult)) {
    // Smallest Uom
    $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$aRow[4]."
                        UNION
                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$aRow[4]." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$aRow[4].")
                        ORDER BY conversion ASC");
    $small_label = "";
    $small_uom = 1;
    while($r=mysql_fetch_array($query)){
        $small_label = $r['abbr'];
        $small_uom = floatval($r['conversion']);
    }
    if ($index != 0 && $aRow[1] != $tmpId) {
        $index = 0;
        $rowTotal = array();
        $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
        $excelContent .= "\n" . "Total " . $tmpName;
        for ($i = 0; $i < count($aColumns) - ($space - 1); $i++) {
            $rowTotal[] = '';
        }
        for ($i = 0; $i < count($aColumns) - $space; $i++) {
            $excelContent .= "\t";
        }
        $rowTotal[] = '<b>' . number_format($totalParentQtyBeg, 2) . '</b>';
        $excelContent .= "\t" . number_format($totalParentQtyBeg, 2);
        $rowTotal[] = '<b>' . number_format($totalParentQtyEnd, 2) . '</b>';
        $excelContent .= "\t" . number_format($totalParentQtyEnd, 2);
        $rowTotal[] = '<b>' . number_format($totalParentQtyOrder, 2) . '</b>';
        $excelContent .= "\t" . number_format($totalParentQtyOrder, 2);
        $rowTotal[] = '<b>' . number_format($totalQtyAvailable, 2) . '</b>';
        $excelContent .= "\t" . number_format($totalQtyAvailable, 2);
        $rowTotal[] = '';
        $excelContent .= "\t";
        $output['aaData'][] = $rowTotal;

        $totalQtyAvailable=0;
        $totalQtyOrder=0; 
        $totalQtyEnd=0; 
        $totalQtyBeg=0;
    }
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if ($aRow[1] == $tmpId) {
                $row[] = '<b>' . ++$index . '</b>';
                $excelContent .= "\n" . $index;
            } else {
                if (!is_null($aRow[1])) {
                    $tmpName = $aRow[1];
                } else {
                    $tmpName = 'No Parent';
                }
                $row[] = '<b class="colspanParent">' . $tmpName . '</b>';
                $excelContent .= "\n" . $tmpName;
                for ($j = 0; $j < count($aColumns) - 1; $j++) {
                    $row[] = '';
                    $excelContent .= "\t";
                }
                $output['aaData'][] = $row;
                $row = array();
                $row[] = '<b>' . ++$index . '</b>';
                $excelContent .= "\n" . $index;
            }
        } else if ($i == 1) {
            $row[] = $aRow[2];
            $excelContent .= "\t" . $aRow[2];
        } else if ($i == 2) {
            $row[] = $aRow[3];
            $excelContent .= "\t" . $aRow[3];
        } else if ($i == 3) {
            $row[] = $small_label;
            $excelContent .= "\t" . $small_label;
        } else if ($i == 4) {
            $row[] = number_format($aRow[5], 2);
            if ($allowViewCost) {
                $excelContent .= "\t" . number_format($aRow[5], 2);
            }
        } else if ($i == 5) {
            if ($aRow[1] == $tmpId) {
                $totalParentQtyBeg += $aRow[6];
            } else {
                $totalParentQtyBeg = $aRow[6];
            }
            $totalQtyBeg += $aRow[6];
            $grandTotalQtyBeg += $aRow[6];
            $row[] = '<a href="" class="btnViewQtyDetail" status="1" rel="' . $aRow[0] . '" date="' . (dateConvert(str_replace("|||", "/", $data[1]))) . '">' . number_format($aRow[6], 2) . '</a>';
            $excelContent .= "\t" . number_format($aRow[6], 2);
        } else if ($i == 6) {
            if ($aRow[1] == $tmpId) {
                $totalParentQtyEnd += $aRow[7];
            } else {
                $totalParentQtyEnd = $aRow[7];
            }
            $totalQtyEnd += $aRow[7];
            $grandTotalQtyEnd += $aRow[7]; 
            $row[] = '<a href="" class="btnViewQtyDetail" status="2" rel="' . $aRow[0] . '" date="' . (dateConvert(str_replace("|||", "/", $data[2]))) . '">' . number_format($aRow[7], 2) . '</a>';
            $excelContent .= "\t" . number_format($aRow[7], 2);
        } else if ($i == 7) {
            if ($aRow[1] == $tmpId) {
                $totalParentQtyOrder += $aRow[8];
            } else {
                $totalParentQtyOrder = $aRow[8];
            }
            $totalQtyOrder += $aRow[8];
            $grandTotalQtyOrder += $aRow[8];
            $row[] = '<a href="" class="btnViewQtyOrderDetail" rel="' . $aRow[0] . '">' . number_format($aRow[8], 2) . '</a>';
            $excelContent .= "\t" . number_format($aRow[8], 2);
        } else if ($i == 8) {
            $totalAvailable = ($aRow[7] - $aRow[8]);
            $totalQtyAvailable += $totalAvailable;
            $grandTotalQtyAvailable += $totalAvailable;
            $row[] = number_format($totalAvailable, 2);
            $excelContent .= "\t" . number_format($totalAvailable, 2);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t" . trim($aRow[$i]);
        }
    }
    $output['aaData'][] = $row;
    $tmpId = $aRow[1];
}
if (mysql_num_rows($rResult)) {
    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
    $excelContent .= "\n" . "Total " . $tmpName;
    for ($i = 0; $i < count($aColumns) - 5; $i++) {
        $rowTotal[] = '';
    }
    for ($i = 0; $i < count($aColumns) - $space; $i++) {
        $excelContent .= "\t";
    }
    $rowTotal[] = '<b>' . number_format($totalParentQtyBeg, 2) . '</b>';
    $excelContent .= "\t" . number_format($totalParentQtyBeg, 2);
    $rowTotal[] = '<b>' . number_format($totalParentQtyEnd, 2) . '</b>';
    $excelContent .= "\t" . number_format($totalParentQtyEnd, 2);
    $rowTotal[] = '<b>' . number_format($totalParentQtyOrder, 2) . '</b>';
    $excelContent .= "\t" . number_format($totalParentQtyOrder, 2);
    $rowTotal[] = '<b>' . number_format($totalQtyAvailable, 2) . '</b>';
    $excelContent .= "\t" . number_format($totalQtyAvailable, 2);
    $rowTotal[] = '';
    $excelContent .= "\t";
    $output['aaData'][] = $rowTotal;

    $rowTotal = array();
    $rowTotal[] = '<b class="colspanParent">GRAND TOTAL</b>';
    $excelContent .= "\n" . "GRAND TOTAL";
    for ($i = 0; $i < count($aColumns) - 5; $i++) {
        $rowTotal[] = '';
    }
    for ($i = 0; $i < count($aColumns) - $space; $i++) {
        $excelContent .= "\t";
    }
    $rowTotal[] = '<b>' . number_format($grandTotalQtyBeg, 2) . '</b>';
    $excelContent .= "\t" . number_format($grandTotalQtyBeg, 2);
    $rowTotal[] = '<b>' . number_format($grandTotalQtyEnd, 2) . '</b>';
    $excelContent .= "\t" . number_format($grandTotalQtyEnd, 2);
    $rowTotal[] = '<b>' . number_format($grandTotalQtyOrder, 2) . '</b>';
    $excelContent .= "\t" . number_format($grandTotalQtyOrder, 2);
    $rowTotal[] = '<b>' . number_format($grandTotalQtyAvailable, 2) . '</b>';
    $excelContent .= "\t" . number_format($grandTotalQtyAvailable, 2);
    $rowTotal[] = '';
    $excelContent .= "\t";
    $output['aaData'][] = $rowTotal;
}

echo json_encode($output);

$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>