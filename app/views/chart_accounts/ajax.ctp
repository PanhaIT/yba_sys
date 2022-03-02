<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowChangeStatus = checkAccess($user['User']['id'], $this->params['controller'], 'status');

/**
 * table MEMORY
 * default max_heap_table_size 16MB
 */
$date = date('Y-m-d');
$tableName = "general_ledger_detail_bs" . $user['User']['id'];
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
                  `other_id` bigint(20) DEFAULT NULL,
                  `class_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `chart_account_id` (`chart_account_id`),
                  KEY `company_id` (`company_id`),
                  KEY `location_id` (`location_id`),
                  KEY `date` (`date`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName") or die(mysql_error());
$queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id
                            FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                            WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "'
                            GROUP BY chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id") or die(mysql_error());
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
                            employee_id,
                            other_id,
                            class_id
                        ) VALUES (
                            '" . $date . "',
                            '" . ((empty($dataCoa['chart_account_id']))?0:$dataCoa['chart_account_id']) . "',
                            '" . ((empty($dataCoa['company_id']))?0:$dataCoa['company_id']) . "',
                            '" . ((empty($dataCoa['location_id']))?0:$dataCoa['location_id']) . "',
                            '" . ((empty($dataCoa['SUM(debit)']))?0:$dataCoa['SUM(debit)']) . "',
                            '" . ((empty($dataCoa['SUM(credit)']))?0:$dataCoa['SUM(credit)']) . "',
                            '" . ((empty($dataCoa['customer_id']))?0:$dataCoa['customer_id']) . "',
                            '" . ((empty($dataCoa['vendor_id']))?0:$dataCoa['vendor_id']) . "',
                            '" . ((empty($dataCoa['employee_id']))?0:$dataCoa['employee_id']) . "',
                            '" . ((empty($dataCoa['other_id']))?0:$dataCoa['other_id']) . "',
                            '" . ((empty($dataCoa['class_id']))?0:$dataCoa['class_id']) . "'
                        )") or die(mysql_error());
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(  'c.id',
                    'ct.name',
                    'CONCAT(
                        IF(parent_id IS NOT NULL,
                            IF((SELECT parent_id FROM chart_accounts WHERE id=c.parent_id) IS NOT NULL,
                                IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id)) IS NOT NULL,
                                    IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id))) IS NOT NULL,
                                        IF((SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=(SELECT parent_id FROM chart_accounts WHERE id=c.parent_id)))) IS NOT NULL,
                                            "                    ",
                                        "                "),
                                    "            "),
                                "        "),
                            "    "),
                        ""),
                        account_codes,
                        " · ",
                        account_description
                    )',
                    'cg.name',
                    '(SELECT SUM(debit) FROM ' . $tableName . ' WHERE chart_account_id=c.id)-(SELECT SUM(credit) FROM ' . $tableName . ' WHERE chart_account_id=c.id)',
                    'c.is_active');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "c.id";

/* DB table to use */
$sTable = "chart_accounts c INNER JOIN chart_account_types ct ON c.chart_account_type_id=ct.id INNER JOIN chart_account_groups cg ON c.chart_account_group_id=cg.id INNER JOIN chart_account_companies cac ON cac.chart_account_id = c.id AND cac.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";

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
            $sOrder .= "TRIM(" . $aColumns[intval($_GET['iSortCol_' . $i])] . ")
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
$condition = "c.is_active!=2";
if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}

/*
 * SQL queries
 * Get data to display
 */
$groupBy = "GROUP BY c.id";
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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            $row[] = ++$index;
        } else if ($i == 2) {
            $row[] = str_replace(" ", "&nbsp;", $aRow[$i]);
        } else if ($i == 4) {
            if($aRow[$i]!='' && $aRow[$i]!=0){
                if($aRow[1]=='Accounts Payable' || $aRow[1]=='Credit Card' || $aRow[1]=='Other Current Liability' || $aRow[1]=='Long Term Liability' || $aRow[1]=='Equity' || $aRow[1]=='Income' || $aRow[1]=='Other Income'){
                    $row[] = number_format($aRow[$i]*-1,2);
                }else{
                    $row[] = number_format($aRow[$i],2);
                }
            }else{
                if($aRow[$i]!=0){
                    $row[] = number_format($aRow[$i],2);
                }else{
                    $row[] = '';
                }
            }
        } else if ($i == 5) {
            $row[] = ($allowChangeStatus ? '<a href="" class="btnChangeStatusChartAccount" rel="' . $aRow[0] . '" name="' . $aRow[2] . '" status="' . $aRow[$i] . '"><img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" /></a>' : '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />');
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $row[] =
            ($allowView ? '<a href="" class="btnViewChartAccount" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
            ($allowEdit ? '<a href="" class="btnEditChartAccount" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
            ($allowDelete ? '<a href="" class="btnDeleteChartAccount" rel="' . $aRow[0] . '" name="' . $aRow[2] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
}

echo json_encode($output);
?>