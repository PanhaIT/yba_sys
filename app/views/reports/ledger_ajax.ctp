<?php

// Authentication
$this->element('check_access');
$allowViewAll = checkAccess($user['User']['id'], 'general_ledgers', 'viewAll');

// Function
include('includes/function.php');
// Tmp Condition
$tmpGlCondition = "";
$tmpCondition   = "";
if ($data[3] != '') {
    $tmpCondition .= ' AND general_ledger_details.chart_account_id=' . $data[3];
}
if ($data[4] != '') {
    if ($data[4] != 0) {
        $tmpCondition .= ' AND general_ledger_details.company_id=' . $data[4];
    } else {
        $tmpCondition .= ' AND general_ledger_details.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
    }
}else{
    $tmpCondition .= ' AND general_ledger_details.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $tmpCondition .= ' AND general_ledger_details.branch_id=' . $data[5];
}else{
    $tmpCondition .= ' AND general_ledger_details.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $tmpCondition .= ' AND general_ledger_details.customer_id=' . $data[6];
}
if ($data[7] != '') {
    $tmpCondition .= ' AND general_ledger_details.vendor_id=' . $data[7];
}
if ($data[8] != '') {
    $tmpCondition .= ' AND general_ledger_details.other_id=' . $data[8];
}
if ($data[9] != '') {
    $tmpCondition .= ' AND general_ledger_details.class_id=' . $data[9];
}
if ($data[10] != '') {
    $tmpGlCondition .= ' AND general_ledgers.is_adj=' . $data[10];
}
// Tmp General Ledger
$tableName = "general_ledger_tmp_lg" . $user['User']['id'];
mysql_query("DROP TABLE ".$tableName);
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE `".$tableName."` (
                    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                    `sys_code` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                    `credit_memo_with_sale_id` INT(11) NULL DEFAULT NULL,
                    `invoice_pbc_with_pbs_id` INT(11) NULL DEFAULT NULL,
                    `sales_order_id` BIGINT(20) NULL DEFAULT NULL,
                    `sales_order_receipt_id` BIGINT(20) NULL DEFAULT NULL,
                    `credit_memo_id` BIGINT(20) NULL DEFAULT NULL,
                    `credit_memo_receipt_id` BIGINT(20) NULL DEFAULT NULL,
                    `purchase_order_id` BIGINT(20) NULL DEFAULT NULL,
                    `pv_id` BIGINT(20) NULL DEFAULT NULL,
                    `purchase_return_id` BIGINT(20) NULL DEFAULT NULL,
                    `purchase_return_receipt_id` BIGINT(20) NULL DEFAULT NULL,
                    `ar_ap_gl_id` BIGINT(20) NULL DEFAULT NULL,
                    `cycle_product_id` BIGINT(20) NULL DEFAULT NULL,
                    `receive_payment_id` BIGINT(20) NULL DEFAULT NULL,
                    `pay_bill_id` BIGINT(20) NULL DEFAULT NULL,
                    `ar_aging_id` BIGINT(20) NULL DEFAULT NULL,
                    `ap_aging_id` BIGINT(20) NULL DEFAULT NULL,
                    `apply_to_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Purchase Order; Purchase Bill; Quote; Sales Invoice',
                    `landing_cost_id` BIGINT(20) NULL DEFAULT NULL,
                    `landing_cost_receipt_id` BIGINT(20) NULL DEFAULT NULL,
                    `expense_id` BIGINT(20) NULL DEFAULT NULL,
                    `other_income_id` BIGINT(20) NULL DEFAULT NULL,
                    `inventory_physical_id` BIGINT(20) NULL DEFAULT NULL,
                    `apply_reference` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Purchase Order; Purchase Bill; Quote; Sales Invoice Code' COLLATE 'utf8_unicode_ci',
                    `receive_from_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Vendor or Customer Id',
                    `receive_from_name` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Vendor or Customer Name' COLLATE 'utf8_unicode_ci',
                    `date` DATE NULL DEFAULT NULL,
                    `reference` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
                    `total_deposit` DECIMAL(15,3) NULL DEFAULT NULL,
                    `note` TEXT NULL COLLATE 'utf8_unicode_ci',
                    `created` DATETIME NULL DEFAULT NULL,
                    `created_by` BIGINT(20) NULL DEFAULT NULL,
                    `modified` DATETIME NULL DEFAULT NULL,
                    `modified_by` BIGINT(20) NULL DEFAULT NULL,
                    `is_sys` TINYINT(4) NULL DEFAULT '0',
                    `is_adj` TINYINT(4) NULL DEFAULT '0',
                    `is_approve` TINYINT(4) NULL DEFAULT '1',
                    `is_depreciated` TINYINT(4) NULL DEFAULT '0',
                    `is_retained_earnings` TINYINT(4) NULL DEFAULT '0',
                    `deposit_type` TINYINT(4) NULL DEFAULT '0' COMMENT '0:Journal; 1: Normal; 2: Purchase Order; 3: Purchase Bill; 4: Quote; 5: Invoice',
                    `is_active` TINYINT(4) NULL DEFAULT '1',
                    PRIMARY KEY (`id`),
                    INDEX `key_filter` (`sales_order_id`, `sales_order_receipt_id`, `credit_memo_id`, `credit_memo_receipt_id`, `purchase_order_id`, `pv_id`),
                    INDEX `key_filter_second` (`purchase_return_id`, `purchase_return_receipt_id`, `ar_ap_gl_id`, `cycle_product_id`, `receive_payment_id`, `pay_bill_id`, `ar_aging_id`, `ap_aging_id`),
                    INDEX `key_filter_third` (`apply_to_id`, `receive_from_id`, `date`, `reference`, `is_approve`, `deposit_type`, `is_active`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB;");
mysql_query("TRUNCATE `".$tableName."`;");

// Tmp General Ledger Detail
$tblGlDeail = "general_ledger_detail_tmp_lg" . $user['User']['id'];
mysql_query("DROP TABLE ".$tblGlDeail);
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE `".$tblGlDeail."` (
                    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                    `general_ledger_id` BIGINT(20) NULL DEFAULT NULL,
                    `main_gl_id` BIGINT(20) NULL DEFAULT NULL,
                    `chart_account_id` INT(11) NULL DEFAULT NULL,
                    `company_id` INT(11) NULL DEFAULT NULL,
                    `branch_id` INT(11) NULL DEFAULT NULL,
                    `location_group_id` INT(11) NULL DEFAULT NULL,
                    `location_id` INT(11) NULL DEFAULT NULL,
                    `product_id` BIGINT(20) NULL DEFAULT NULL,
                    `service_id` INT(11) NULL DEFAULT NULL,
                    `is_free` TINYINT(4) NOT NULL DEFAULT '0',
                    `inventory_valuation_id` BIGINT(20) NULL DEFAULT NULL,
                    `inventory_valuation_is_debit` TINYINT(4) NULL DEFAULT NULL,
                    `type` VARCHAR(50) NULL DEFAULT 'General Journal' COLLATE 'utf8_unicode_ci',
                    `debit` DECIMAL(20,9) NULL DEFAULT '0.000000000',
                    `credit` DECIMAL(20,9) NULL DEFAULT '0.000000000',
                    `memo` TEXT NULL COLLATE 'utf8_unicode_ci',
                    `customer_id` BIGINT(20) NULL DEFAULT NULL,
                    `vendor_id` BIGINT(20) NULL DEFAULT NULL,
                    `employee_id` BIGINT(20) NULL DEFAULT NULL,
                    `other_id` BIGINT(20) NULL DEFAULT NULL,
                    `class_id` BIGINT(20) NULL DEFAULT NULL,
                    `is_reconcile` TINYINT(4) NULL DEFAULT '0',
                    `reconcile_id` BIGINT(20) NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `key_filter_second` (`location_group_id`, `location_id`, `product_id`, `service_id`, `inventory_valuation_id`),
                    INDEX `key_filter_third` (`customer_id`, `vendor_id`, `employee_id`, `other_id`, `class_id`),
                    INDEX `key_filter` (`general_ledger_id`, `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB;");
mysql_query("TRUNCATE `".$tblGlDeail."`;");

// Insert GL
mysql_query("INSERT INTO ".$tableName." SELECT * FROM general_ledgers WHERE is_active = 1 AND is_approve = 1 AND date >= '".dateConvert(str_replace("|||", "/", $data[1]))."' AND date <= '".dateConvert(str_replace("|||", "/", $data[2]))."'".$tmpGlCondition);
// Insert GL Detail
mysql_query("INSERT INTO ".$tblGlDeail." SELECT * FROM general_ledger_details WHERE general_ledger_id IN (SELECT id FROM general_ledgers WHERE is_active = 1 AND is_approve = 1 AND date >= '".dateConvert(str_replace("|||", "/", $data[1]))."' AND date <= '".dateConvert(str_replace("|||", "/", $data[2]))."'".$tmpGlCondition.")".$tmpCondition.";");

// Tmp Chart Account
$tblAccount = "chart_account_gl_tmp" . $user['User']['id'];
mysql_query("DROP TABLE ".$tblAccount);
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE `".$tblAccount."` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `debit` DECIMAL(20,9) NULL DEFAULT '0.000000000',
                    `credit` DECIMAL(20,9) NULL DEFAULT '0.000000000',
                    `chart_account_id` INT(11) NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `chart_account_id` (`chart_account_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB;");
mysql_query("TRUNCATE `".$tblAccount."`;");
// Insert Chart Account
mysql_query("INSERT INTO `".$tblAccount."` (`debit`, `credit`, `chart_account_id`) SELECT SUM(general_ledger_details.debit) AS debit, SUM(general_ledger_details.credit) AS credit, general_ledger_details.chart_account_id AS chart_account_id FROM general_ledger_details INNER JOIN general_ledgers ON general_ledgers.id = general_ledger_details.general_ledger_id AND general_ledgers.is_approve=1 AND general_ledgers.is_active=1 AND general_ledgers.date < '" . dateConvert(str_replace("|||", "/", $data[1])) . "'".$tmpGlCondition." WHERE ".$tmpCondition." GROUP BY general_ledger_details.chart_account_id");

/**
 * export to excel
 */
$filename="public/report/ledger_" . $user['User']['id'] . ".csv";
$fp=fopen($filename,"wb");
$excelContent = MENU_GENERAL_LEDGER . "\n\n";
if($data[1]!='') {
    $excelContent .= REPORT_FROM . ': ' . str_replace('|||','/',$data[1]);
}
if($data[2]!='') {
    $excelContent .= ' '.REPORT_TO . ': ' . str_replace('|||','/',$data[2]);
}
$excelContent .= "\n\n".TABLE_NO."\t".TABLE_DATE."\t".TABLE_CREATED_BY."\t".TABLE_REFERENCE."\t".TABLE_ADJUST."\t".TABLE_TYPE."\tAccount Code\t".GENERAL_DESCRIPTION."\t".TABLE_CLASS."\t".GENERAL_DEBIT."\t".GENERAL_CREDIT."\t".GENERAL_BALANCE;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'gl.id',
    'CONCAT(chart_accounts.account_codes," · ",chart_accounts.account_description)',
    'date',
    '(SELECT CONCAT(first_name," ",last_name) FROM users WHERE id=gl.created_by)',
    'reference',
    'is_adj',
    'type',
    'chart_accounts.account_codes',
    'memo',
    '(SELECT name FROM classes WHERE id=gld.class_id)',
    'debit',
    'credit',
    'gl.is_active',
    'gld.chart_account_id');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "gl.id";

/* DB table to use */
$sTable = "`".$tableName."` AS gl INNER JOIN `".$tblGlDeail."` AS gld ON gl.id=gld.general_ledger_id INNER JOIN chart_accounts ON chart_accounts.id = gld.chart_account_id";

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

    $sOrder = str_replace('CONCAT(chart_accounts.account_codes," · ",chart_accounts.account_description) asc', 'CONCAT(chart_accounts.account_codes," · ",chart_accounts.account_description) asc, gl.date asc, gl.id asc', $sOrder);
    $sOrder = str_replace('CONCAT(chart_accounts.account_codes," · ",chart_accounts.account_description) desc', 'CONCAT(chart_accounts.account_codes," · ",chart_accounts.account_description) desc, gl.date desc, gl.id desc', $sOrder);
    $sOrder = str_replace('ORDER BY ','ORDER BY gld.company_id,',$sOrder);
    
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
$condition = "gl.is_approve=1 AND gl.is_active=1";
if ($data[1] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[1])) . '" <= gl.date';
}
if ($data[2] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . dateConvert(str_replace("|||", "/", $data[2])) . '" >= gl.date';
}
if ($data[3] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.chart_account_id=' . $data[3];
}
if ($data[4] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    if ($data[4] != 0) {
        $condition .= 'gld.company_id=' . $data[4];
    } else {
        $condition .= 'gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
    }
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
}
if ($data[5] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.branch_id=' . $data[5];
}else{
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
}
if ($data[6] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.customer_id=' . $data[6];
}
if ($data[7] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.vendor_id=' . $data[7];
}
if ($data[8] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.other_id=' . $data[8];
}
if ($data[9] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.class_id=' . $data[9];
}
if ($data[10] != '') {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gl.is_adj=' . $data[10];
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
$bbi = 0;
$amountDr = 0;
$amountCr = 0;
$amountDrTotal = 0;
$amountCrTotal = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    if ($index != 0 && $aRow[1] != $tmpId) {
        $index = 0;
        $rowTotal = array();
        $rowTotal[] = '<b class="colspanParent">Total ' . $tmpName . '</b>';
        $excelContent .= "\n" . $tmpName;
        for ($i = 0; $i < count($aColumns) - 6; $i++) {
            $rowTotal[] = '';
            $excelContent .= "\t";
        }
        $rowTotal[] = '<b>' . number_format($amountDr, 2) . '</b>';
        $excelContent .= "\t" . number_format($amountDr, 2);
        $rowTotal[] = '<b>' . number_format($amountCr, 2) . '</b>';
        $excelContent .= "\t" . number_format($amountCr, 2);
        $rowTotal[] = '<b>' . number_format($bbi + $amountDr - $amountCr, 2) . '</b>';
        $excelContent .= "\t" . number_format($bbi + $amountDr - $amountCr, 2);
        $output['aaData'][] = $rowTotal;
    }
    $chart_account_id = $aRow[13];
    $queryBBI = mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM ".$tblAccount." WHERE chart_account_id='" . $chart_account_id . "'),0)-IFNULL((SELECT SUM(credit) FROM ".$tblAccount." WHERE chart_account_id='" . $chart_account_id . "'),0)") or die(mysql_error());
    $dataBBI = mysql_fetch_array($queryBBI);
    $bbi = $dataBBI[0];
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if ($aRow[1] == $tmpId) {
                $row[] = '<b>' . ++$index . '</b><input type="hidden" value="' . $aRow[0] . '" class="link2glGeneralLedger" />';
                $excelContent .= "\n" . $index;
            } else {
                if (!is_null($aRow[1])) {
                    $tmpName = $aRow[1];
                } else {
                    $tmpName = '';
                }
                $row[] = '<b class="colspanParent">' . $tmpName . '</b>';
                $excelContent .= "\n" . $tmpName;
                for ($j = 0; $j < count($aColumns) - 4; $j++) {
                    $row[] = '';
                    $excelContent .= "\t";
                }
                $row[] = '<b>' . number_format($bbi, 2) . '</b>';
                $excelContent .= "\t" . number_format($bbi, 2);
                $output['aaData'][] = $row;
                $row = array();
                $row[] = '<b>' . ++$index . '</b><input type="hidden" value="' . $aRow[0] . '" class="link2glGeneralLedger" />';
                $excelContent .= "\n" . $index;
            }
        } else if ($i == 1) {
            
        } else if ($aColumns[$i] == 'date') {
            if ($aRow[0] == $tmpId) {
                $row[] = '';
                $excelContent .= "\t";
            } else {
                if ($aRow[$i] != '0000-00-00') {
                    $row[] = dateShort($aRow[$i]);
                    $excelContent .= "\t" . dateShort($aRow[$i]);
                } else {
                    $row[] = '';
                    $excelContent .= "\t";
                }
            }
        } else if ($aColumns[$i] == 'created_by') {
            if ($aRow[0] == $tmpId) {
                $row[] = '';
                $excelContent .= "\t";
            } else {
                $row[] = $aRow[$i];
                $excelContent .= "\t" . trim($aRow[$i]);
            }
        } else if ($aColumns[$i] == 'reference') {
            if ($aRow[0] == $tmpId) {
                $row[] = '';
                $excelContent .= "\t";
            } else {
                $row[] = trim($aRow[$i]);
                $excelContent .= "\t" . $aRow[$i];
            }
        } else if ($aColumns[$i] == 'is_adj') {
            if ($aRow[0] == $tmpId) {
                $row[] = '';
                $excelContent .= "\t";
            } else {
                if ($aRow[$i] == 1) {
                    $row[] = '<img alt="Edit" src="' . $this->webroot . 'img/button/tick.png" />';
                    $excelContent .= "\t" . "Adjust";
                } else {
                    $row[] = '';
                    $excelContent .= "\t";
                }
            }
        } else if ($aColumns[$i] == 'type') {
            if ($aRow[0] == $tmpId) {
                $row[] = '';
                $excelContent .= "\t";
            } else {
                $row[] = $aRow[$i];
                $excelContent .= "\t" . trim($aRow[$i]);
            }
        } else if ($aColumns[$i] == 'debit') {
            $row[] = $aRow[$i] != 0 ? number_format($aRow[$i], 2) : '';
            $excelContent .= "\t" . number_format($aRow[$i], 2);
            if ($aRow[1] == $tmpId) {
                $amountDr += $aRow[$i];
            } else {
                $amountDr = $aRow[$i];
            }
            $amountDrTotal += $aRow[$i];
        } else if ($aColumns[$i] == 'credit') {
            $row[] = $aRow[$i] != 0 ? number_format($aRow[$i], 2) : '';
            $excelContent .= "\t" . number_format($aRow[$i], 2);
            if ($aRow[1] == $tmpId) {
                $amountCr += $aRow[$i];
            } else {
                $amountCr = $aRow[$i];
            }
            $amountCrTotal += $aRow[$i];
        } else if ($aColumns[$i] == 'gl.is_active') {
            $row[] = number_format($bbi + $amountDr - $amountCr, 2);
            $excelContent .= "\t" . number_format($bbi + $amountDr - $amountCr, 2);
        } else if ($aColumns[$i] == 'gld.chart_account_id') {

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
    $excelContent .= "\n" . $tmpName;
    for ($i = 0; $i < count($aColumns) - 6; $i++) {
        $rowTotal[] = '';
        $excelContent .= "\t";
    }
    $rowTotal[] = '<b>' . number_format($amountDr, 2) . '</b>';
    $excelContent .= "\t" . number_format($amountDr, 2);
    $rowTotal[] = '<b>' . number_format($amountCr, 2) . '</b>';
    $excelContent .= "\t" . number_format($amountCr, 2);
    $rowTotal[] = '<b>' . number_format($bbi + $amountDr - $amountCr, 2) . '</b>';
    $excelContent .= "\t" . number_format($bbi + $amountDr - $amountCr, 2);
    $output['aaData'][] = $rowTotal;
}

echo json_encode($output);

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);
?>