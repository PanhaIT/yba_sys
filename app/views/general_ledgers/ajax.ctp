<?php

// Authentication
$this->element('check_access');
$allowView = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');

// Function
include('includes/function.php');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'CONCAT(gl.id,"||",gl.deposit_type)',
    '(SELECT CONCAT_WS(" ",last_name,first_name) FROM users WHERE id = gl.created_by)',
    'date',
    'reference',
    'is_adj',
    'type',
    '(SELECT CONCAT(account_codes," · ",account_description) FROM chart_accounts WHERE id=gld.chart_account_id)',
    'memo',
    '(SELECT name FROM classes WHERE id=gld.class_id)',
    'debit',
    'credit',
    'is_approve',
    'note');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "gl.id";

/* DB table to use */
$sTable = "general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id";

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

    $sOrder = str_replace("date asc", "date asc, gl.id asc", $sOrder);
    $sOrder = str_replace("date desc", "date desc, gl.id desc", $sOrder);
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
$condition = "gl.is_active = 1 AND is_sys=0 AND gl.created_by=" . $user['User']['id'] . " AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
if(!is_null($filterDateFrom) && $filterDateFrom != ""){
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . $filterDateFrom . '" <= gl.date';
}
if(!is_null($filterDateTo) && $filterDateTo != ""){
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= '"' . $filterDateTo . '" >= gl.date';
}
if(!is_null($filterStatus) && $filterStatus != 'all'){
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gl.is_approve=' . $filterStatus;
}
if(!is_null($company) && $company != 'all'){
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= 'gld.company_id=' . $company;
} else {
    $condition != '' ? $condition .= ' AND ' : '';
    $condition .= "gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
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
$tmpId = '';
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $record = explode("||", $aRow[0]);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            /* Special output formatting */
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                $row[] = ++$index;
            }
        } else if ($aColumns[$i] == 'date') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                if ($aRow[$i] != '0000-00-00') {
                    $row[] = dateShort($aRow[$i]);
                } else {
                    $row[] = '';
                }
            }
        } else if ($aColumns[$i] == 'reference') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'is_adj') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                if ($aRow[$i] == 1) {
                    $row[] = '<img alt="Edit" src="' . $this->webroot . 'img/button/tick.png" />';
                } else {
                    $row[] = '';
                }
            }
        } else if ($aColumns[$i] == '(SELECT name FROM companies WHERE id=gld.company_id)') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'type') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                $row[] = $aRow[$i];
            }
        } else if ($aColumns[$i] == 'memo') {
            $row[] = mb_strlen($aRow[$i]) > 50 ? mb_substr($aRow[$i], 0, 50) . "..." : $aRow[$i];
        } else if ($aColumns[$i] == 'debit' || $aColumns[$i] == 'credit') {
            $row[] = number_format($aRow[$i], 2);
        } else if ($aColumns[$i] == 'is_approve') {
            if ($record[0] == $tmpId) {
                $row[] = '';
            } else {
                $row[] = '<img alt="' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '" onmouseover="Tip(\'' . ($aRow[$i] == 1 ? TABLE_ACTIVE : TABLE_INACTIVE) . '\')" src="' . $this->webroot . 'img/button/' . ($aRow[$i] == 1 ? 'active' : 'inactive') . '.png" />';
            }
        } else if ($aColumns[$i] == 'note') {
            
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    if ($record[0] == $tmpId) {
        $row[] = '';
    } else {
        $row[] =
                ($allowView ? '<a href="" class="btnViewJournalEntry" rel="' . $record[0] . '" name="' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : '') .
                ($allowView ? '<a href="" class="btnPrintJournalEntry" rel="' . $record[0] . '" type="' . $aRow[5] . '"><img alt="Print" onmouseover="Tip(\'' . ACTION_PRINT . '\')" src="' . $this->webroot . 'img/button/printer.png" /></a> ' : '') .
                ($allowView && trim($aRow[10]) != '' ? '<a href="" class="btnNoteJournalEntry" rel="' . $record[0] . '" note="' . str_replace('"', "{dblquote}", trim($aRow[12])) . '"><img alt="Note" onmouseover="Tip(\'' . TABLE_NOTE . '\')" src="' . $this->webroot . 'img/button/note.png" /></a> ' : '') .
                ($allowEdit ? '<a href="" class="btnEditJournalEntry" rel="' . $record[0] . '" is-dp="'.$record[1].'" name="' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : '') .
                ($allowDelete ? '<a href="" class="btnDeleteJournalEntry" rel="' . $record[0] . '" name="' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    }
    $output['aaData'][] = $row;
    $tmpId = $record[0];
}

echo json_encode($output);
?>