<?php
include("includes/function.php");

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], 'customers', 'view');
$allowEdit = checkAccess($user['User']['id'], 'customers', 'edit');
$allowDelete = checkAccess($user['User']['id'], 'customers', 'delete');

// A/R CoA List
$arrCoAIdList = array();
$queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
    $arrCoAIdList[]=$dataCoAIdList['id'];
}

/**
 * table MEMORY
 * default max_heap_table_size 16MB
 */
$date = date('Y-m-d');
$tableName = "general_ledger_detail_cus" . $user['User']['id'];
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `date` date DEFAULT NULL,
                  `chart_account_id` int(11) DEFAULT NULL,
                  `company_id` int(11) DEFAULT NULL,
                  `location_id` int(11) DEFAULT NULL,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `customer_id` bigint(20) DEFAULT NULL,
                  `vendor_id` bigint(20) DEFAULT NULL,
                  `employee_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `chart_account_id` (`chart_account_id`),
                  KEY `company_id` (`company_id`),
                  KEY `location_id` (`location_id`),
                  KEY `date` (`date`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName") or die(mysql_error());
$queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id
                            FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                            WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "' AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ") AND gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") AND customer_id IS NOT NULL AND customer_id!=''
                            GROUP BY chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id") or die(mysql_error());
while ($dataCoa = mysql_fetch_array($queryCoa)) {
    mysql_query("INSERT INTO $tableName (
                            date,
                            chart_account_id,
                            company_id,
                            location_id,
                            debit,
                            credit,
                            customer_id,
                            vendor_id,
                            employee_id
                        ) VALUES (
                            '" . $date . "',
                            " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['location_id']) ? $dataCoa['location_id'] : "NULL") . ",
                            '" . $dataCoa['SUM(debit)'] . "',
                            '" . $dataCoa['SUM(credit)'] . "',
                            " . (!is_null($dataCoa['customer_id']) ? $dataCoa['customer_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['vendor_id']) ? $dataCoa['vendor_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['employee_id']) ? $dataCoa['employee_id'] : "NULL") . "
                        )") or die(mysql_error());
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variablestom
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('customers.id', 'customers.customer_code', 'customers.name', 'customers.name_kh', 'customers.main_number', 'customers.discount', 'FORMAT((SELECT SUM(debit)-SUM(credit) AS amount FROM ' . $tableName . ' WHERE chart_account_id IN (' . implode(",", $arrCoAIdList) . ') AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].') AND customer_id = customers.id),2)', 'customers.created', 'CONCAT_WS(" ",users.first_name,users.last_name)');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "customers.id";

/* DB table to use */
$sTable = "customers LEFT JOIN users ON users.id = customers.created_by";

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
$conditionUser = " AND customers.id IN (SELECT customer_id FROM customer_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";

$condition = "customers.is_active=1".$conditionUser;
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
        } else if ($aColumns[$i] == 'customers.discount') {
            if($aRow[$i] > 0){
                $row[] = number_format($aRow[$i], 2);
            } else {
                $row[] = 0;
            }
        } else if ($aColumns[$i] == 'customers.created') {
            if($aRow[$i] != "" && $aRow[$i] != "0000-00-00 00:00:00"){
                $row[] = dateShort($aRow[$i], "d/m/Y H:i:s");
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewCustomer" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit ? '<a href="" class="btnEditCustomer" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete && $aRow[0] > 1 ? '<a href="" class="btnDeleteCustomer" rel="' . $aRow[0] . '" name="' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>