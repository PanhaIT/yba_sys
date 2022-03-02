<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');

/**
 * export to excel
 */
$filename = "public/report/fixed_asset_" . $user['User']['id'] . ".csv";
$fp = fopen($filename, "wb");
$excelContent = MENU_FIXED_ASSET_MANAGEMENT;
$excelContent .= "\n\n" . TABLE_NO . "\t" . "Date Acquired" . "\t" . TABLE_COMPANY . "\t" . TABLE_LOCATION . "\t" . TABLE_NAME . "\t" . "Cost" . "\t" . "Life" . "\t" . "Method" . "\t" . "Salvage" . "\t" . "%" . "\t" . "Depreciation";

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'id',
    'date',
    '(SELECT name FROM companies WHERE id=company_id)',
    '(SELECT name FROM locations WHERE id=location_id)',
    'CONCAT_WS(" ",fixed_asset_code,"-",name)',
    'cost',
    'asset_life',
    'depr_method',
    'salvage_value',
    'business_use_percentage',
    'CONCAT_WS("|*|",DATEDIFF(now(),date),cost_remain)'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

/* DB table to use */
$sTable = "fixed_assets";

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
$condition = "is_active=1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id='" . $user['User']['id'] . "' GROUP BY company_id) AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id']." GROUP BY branch_id)";
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
            $row[] = ++$index;
            $excelContent .= "\n" . $index;
        } else if ($aColumns[$i] == 'CONCAT_WS("|*|",DATEDIFF(now(),date),cost_remain)') {
            
            $explodeStr = explode("|*|", $aRow[10]);
            
            $date1 = $aRow['date'];
            $date2 = date('Y-m-d', strtotime('+' . $aRow['asset_life'] . ' month', strtotime($aRow['date'])));
            $totalDeprDay = abs(strtotime($date2) - strtotime($date1)) / 3600 / 24;
            if ($explodeStr[0] > $totalDeprDay) {
                $explodeStr[0] = $totalDeprDay;
            }
            $totalCost = (($aRow['cost'] * ($aRow['business_use_percentage'] / 100)) - $aRow['salvage_value']);
            if ($aRow['depr_method'] == 'SLM') {
                $accumDepr = $explodeStr[1];
                $row[] = number_format($accumDepr, 2);
                $excelContent .= "\t" . number_format($accumDepr, 2);
            } else if ($aRow['depr_method'] == 'DBM') {
                $accumDepr = $explodeStr[1];
                $row[] = number_format($accumDepr, 2);
                $excelContent .= "\t" . number_format($accumDepr, 2);
            } else if ($aRow['depr_method'] == 'DDBM') {
                $accumDepr = $explodeStr[1];
                $row[] = number_format($accumDepr, 2);
                $excelContent .= "\t" . number_format($accumDepr, 2);
            } else {
                $row[] = 'None';
                $excelContent .= "\t" . "None";
            }
        } else if ($aColumns[$i] == 'cost') {
            $row[] = number_format($aRow[$i], 2);
            $excelContent .= "\t" . number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'cost') {
            $row[] = number_format($aRow[$i], 2);
            $excelContent .= "\t" . number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'asset_life') {
            $row[] = number_format($aRow[$i], 0);
            $excelContent .= "\t" . number_format($aRow[$i], 0);
        } else if ($aColumns[$i] == 'business_use_percentage') {
            $row[] = number_format($aRow[$i], 0);
            $excelContent .= "\t" . number_format($aRow[$i], 0);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
            $excelContent .= "\t" . trim($aRow[$i]);
        }
    }
    $queryIsInUsed = mysql_query("SELECT id FROM fixed_assets WHERE is_in_used=1 AND id=" . $aRow[0]);
    $row[] =
            ($allowView ? '<a href="" class="btnViewFixedAsset" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a>' : '') .
            ($allowEdit && !mysql_num_rows($queryIsInUsed) ? '<a href="" class="btnEditFixedAsset" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a>' : '') .
            ($allowDelete && !mysql_num_rows($queryIsInUsed) ? '<a href="" class="btnDeleteFixedAsset" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);

$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>