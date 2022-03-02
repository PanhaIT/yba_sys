<?php

// Authentication
$this->element('check_access');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('po.id', "po.po_code", 'po.po_code', 'v.name', 'v.name', 'po.status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "po.id";

/* DB table to use */
$sTable = "purchase_orders as po INNER JOIN vendors as v ON po.vendor_id = v.id";

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
$condition = 'po.status > 0 AND po.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].') AND po.location_id IN (SELECT location_id FROM user_locations WHERE user_id = '.$user['User']['id'].')';
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
    $product = '';
    $description = '';
    $qty = '';
    $code = '';
    $j = 0;
    $spd = mysql_query("SELECT p.code as code, p.name as name, pd.note as note, pd.qty as qty, u.name as uom_name FROM `purchase_order_details` as pd INNER JOIN `products` as p ON pd.product_id = p.id INNER JOIN `uoms` as u ON pd.qty_uom_id = u.id WHERE pd.purchase_order_id = ".$aRow[0]);
    while($rpd = mysql_fetch_array($spd)){
        if($j ==0){
            $padding = '20px';
            $note_padding = '7px';
        }else{
            $padding = '35px';
            $note_padding = '35px';
        }
        $product .= "<div style='padding-left:15px; padding-top:".$padding."; display:block'>".$rpd['code'].'-'.$rpd['name']."</div>";
        $description .= "<div style='padding-top:".$note_padding."; display:block'>".$rpd['note']."</div>";
        $qty .= "<div style='padding-top:".$padding."; display:block'>".$rpd['qty'].' '.$rpd['uom_name']."</div>";
        $code .= "<div style='padding-top:10px; display:block'>".'<img class="barcode" alt="" src="'.$this->webroot.'barcodegen.1d-php5.v2.2.0/generate_barcode.php?str='.$rpd['code'].'" style="border:0px; width: 200px;" />'."</div>";
        $j ++;
    }
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if($i == 1){
            $row[] = "PO# <span style='color:#0066cc'>".$aRow[$i]."</span>".$product;
        } else if($i == 2){
            $row[] = '<img class="barcode" alt="" src="'.$this->webroot.'barcodegen.1d-php5.v2.2.0/generate_barcode.php?str='.$aRow[$i].'" style="border:0px; width: 200px;" />'.$description;
        } else if($i == 3){
            $row[] = "<div style='text-align:right'>Vendor:</div>".$qty;
        } else if($i == 4){
            $row[] = $aRow[$i].$code;
        } else if($i == 5){
            /* General output */
            switch ($aRow[$i]) {
                case 0:
                    $row[] = 'Void';
                    break;
                case 1:
                    $row[] = 'Issued';
                    break;
                case 2:
                    $row[] = 'Partial';
                    break;
                case 3:
                    $row[] = 'Fulfilled';
                    break;
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
   
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>