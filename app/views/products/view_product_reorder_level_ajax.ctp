<?php
// Function
include('includes/function.php');

// Authentication
$this->element('check_access');
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
                    'p.id', 
                    'p.code',
                    'p.name',
                    'p.price_uom_id',
                    '(reorder_level * p.small_val_uom)',
                    'pg.pgroup_id'
            );

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "p.id";

/* DB table to use */
$sTable = "products AS p LEFT JOIN product_pgroups AS pg ON pg.product_id = p.id";

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
$condition = "p.is_active=1 AND (SELECT SUM(i.qty) FROM inventories i
                            INNER JOIN locations lc ON lc.id=i.location_id
                            INNER JOIN user_locations ulc ON lc.id=ulc.location_id
                            WHERE i.product_id=p.id AND ulc.user_id = " .$user['User']['id']. "
                            GROUP BY i.product_id
                        ) <= (reorder_level * p.small_val_uom)";
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

$pgroupId   = '$';
$pgroupName = "";

while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */            
            if ($aRow[5] == $pgroupId) {
                $row[] = ++$index;
            }else{
                $index = 0;
                if (!is_null($aRow[5])) {
                    $queryPgroupName = mysql_query("SELECT name FROM pgroups WHERE id = '".$aRow[5]."'");
                    $dataPgroupName  = mysql_fetch_array($queryPgroupName);
                    $pgroupName = $dataPgroupName[0];
                } else {
                    $pgroupName = 'No Product Group';
                }
                
                $row[] = '<b class="colspanParent">' . $pgroupName . '</b>';
                for ($j = 0; $j < count($aColumns) - 1; $j++) {
                    $row[] = '';
                } 
                $output['aaData'][] = $row;
                $row = array();                
                $row[] = ++$index; 
            } 
            
            $queryQty = mysql_query("SELECT SUM(i.qty) AS qty FROM inventories i
                                INNER JOIN locations lc ON lc.id=i.location_id
                                INNER JOIN user_locations ulc ON lc.id=ulc.location_id
                                LEFT JOIN products p ON p.id = i.product_id
                                WHERE i.product_id=".$aRow[$i]." AND ulc.user_id = " .$user['User']['id']. "
                                GROUP BY i.product_id");
            $rowQty = mysql_fetch_array($queryQty);
            $qtyInventory = $rowQty['qty'];
            
        } else if($i == 3){   
            
            $queryUomAbbr = mysql_query("SELECT abbr FROM uoms INNER JOIN products ON products.price_uom_id = uoms.id WHERE uoms.id = ".$aRow[$i]." ");
            $rowUomAbbr   = mysql_fetch_array($queryUomAbbr);
            
            // Smallest Uom
            $queryUom=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$aRow[$i]."
                                UNION
                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$aRow[$i]." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$aRow[$i].")
                                ORDER BY conversion ASC");
            $small_label = "";
            $small_uom = 1;
            while($rowUom=mysql_fetch_array($queryUom)){
                $small_label = $rowUom['abbr'];
                $small_uom = floatval($rowUom['conversion']);
            }
            $row[] = showTotalQty($qtyInventory, $rowUomAbbr[0], $small_uom, $small_label);
            
        } else if($i == 4){
            $row[] = showTotalQty($aRow[$i], $rowUomAbbr[0], $small_uom, $small_label);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    
    $pgroupId = $aRow[5];    
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>