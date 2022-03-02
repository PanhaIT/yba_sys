<?php
include("includes/function.php");

// Setting
$allowBarcode = false;
$costDecimal  = 2;
$salesDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (1, 39, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 1){
        if($rowSetting['is_checked'] == 1){
            $allowBarcode = true;
        }
    } else if($rowSetting['id'] == 39){
        $costDecimal = $rowSetting['value'];
    } else if($rowSetting['id'] == 40){
        $salesDecimal = $rowSetting['value'];
    }
}

// Authentication
$this->element('check_access');
$allowView     = checkAccess($user['User']['id'], $this->params['controller'], 'view');
$allowEdit     = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete   = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowSetPrice = checkAccess($user['User']['id'], $this->params['controller'], 'productPrice');
$allowViewCost = checkAccess($user['User']['id'], $this->params['controller'], 'viewCost');
// Get Symbol Currency
$sqlSym = mysql_query("SELECT symbol FROM companies INNER JOIN currencies ON currencies.id = companies.currency_id WHERE companies.is_active = 1 LIMIT 1");
$rowSym = mysql_fetch_array($sqlSym);
// Tmp Product Inventory
//$tableTmp = "product_inventory_tmp".$user['User']['id'];
//mysql_query("SET max_heap_table_size = 1024*1024*1024");
//mysql_query("CREATE TABLE `".$tableTmp."` (
//                    `product_id` INT(11) NOT NULL DEFAULT '0',
//                    `total_qty` DECIMAL(15,3) NULL DEFAULT NULL,
//                    PRIMARY KEY (`product_id`),
//                    INDEX `products` (`product_id`)
//            )
//            COLLATE='utf8_unicode_ci'
//            ENGINE=InnoDB;");
//mysql_query("TRUNCATE $tableTmp") or die(mysql_error());

//$sqlInv = mysql_query("SELECT SUM(total_qty) AS total_qty, product_id FROM product_inventories WHERE product_inventories.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].") GROUP BY product_id");
//while($rowInv = mysql_fetch_array($sqlInv)){
//    mysql_query("INSERT INTO `".$tableTmp."` (`product_id`, `total_qty`) VALUES (".$rowInv['product_id'].", ".$rowInv['total_qty'].");");
//}
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
if($allowViewCost){
    $indexParent = 10;
        $aColumns = array('id',
        'type',
        'parent_id',
        'sub_category_id',   
        //'sub_sub_category_id',    
        'name',
        'CONCAT_WS("|*|",IFNULL(code,""),is_active)',
        'uom',
        'unit_cost',
        'price',
        'group_name',
        'group_id',
        'uom_id',
        'uom_abbr',
        'small_val_uom'
    );
} else {
    $indexParent = 9;
        $aColumns = array('id',
        'type',
        'parent_id',
        'sub_category_id',
        //'sub_sub_category_id',    
        'name',
        'CONCAT_WS("|*|",IFNULL(code,""),is_active)',
        'uom',
        'price',
        'group_name',
        'group_id',
        'uom_id',
        'uom_abbr',
        'small_val_uom'
    );
}

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

if($displayPro == '1'){
    $sTable = " (SELECT 
                    p.id AS id,
                    'Product' AS type,
                    p.name AS name,
                    departments.id AS group_id,
                    departments.name AS group_name,
                    pgroups.id AS parent_id,
                    pgroups.id AS sub_category_id,

                    p.barcode AS code,
                    uoms.id AS uom_id,
                    uoms.name AS uom,
                    uoms.abbr AS uom_abbr,
                    p.unit_cost AS unit_cost,
                    p.unit_price AS price,
                    p.small_val_uom AS small_val_uom,
                    p.created AS created,
                    p.is_active AS is_active
                FROM 
                    products AS p
                LEFT JOIN 
                    uoms ON uoms.id = p.price_uom_id 
                INNER JOIN 
                    product_pgroups pg ON p.id = pg.product_id
                INNER JOIN 
                    pgroups ON pgroups.id = pg.pgroup_id
                LEFT JOIN 
                    departments ON departments.id = pgroups.department_id
                GROUP BY
                    p.id
                ORDER BY type, code) AS product";
} else if($displayPro == '2'){
    $sTable = " (SELECT 
                    services.id AS id,
                    'Service' AS type,
                    services.name AS name,
                    pgroups.id AS group_id,
                    pgroups.name AS group_name,
                    pgroups.id AS parent_id,
                    '' AS sub_category_id,
             
                    services.code AS code,
                    uoms.id AS uom_id,
                    uoms.name AS uom,
                    uoms.abbr AS uom_abbr,
                    '0' AS unit_cost,
                    services.unit_price AS price,
                    '1' AS small_val_uom,
                    services.created AS created,
                    services.is_active AS is_active
                FROM 
                    services
                LEFT JOIN 
                    uoms ON uoms.id = services.uom_id
                INNER JOIN 
                    pgroups ON pgroups.id = services.service_group_id
                ORDER BY type, code) AS product";
} else {
    $sTable = " (SELECT 
                    p.id AS id,
                    'Product' AS type,
                    p.name AS name,
                    departments.id AS group_id,
                    departments.name AS group_name,
                    pgroups.id AS parent_id,
                    pgroups.id AS sub_category_id,
               
                    p.barcode AS code,
                    uoms.id AS uom_id,
                    uoms.name AS uom,
                    uoms.abbr AS uom_abbr,
                    p.unit_cost AS unit_cost,
                    p.unit_price AS price,
                    p.small_val_uom AS small_val_uom,
                    p.created AS created,
                    p.is_active AS is_active
                FROM 
                    products AS p
                LEFT JOIN 
                    uoms ON uoms.id = p.price_uom_id 
                INNER JOIN 
                    product_pgroups pg ON p.id = pg.product_id
                INNER JOIN 
                    pgroups ON pgroups.id = pg.pgroup_id
                LEFT JOIN 
                    departments ON departments.id = pgroups.department_id
                GROUP BY p.id
                UNION ALL
                SELECT 
                    services.id AS id,
                    'Service' AS type,
                    services.name AS name,
                    pgroups.id AS group_id,
                    pgroups.name AS group_name,
                    pgroups.id AS parent_id,
                    '' AS sub_category_id,
                    
                    services.code AS code,
                    uoms.id AS uom_id,
                    uoms.name AS uom,
                    uoms.abbr AS uom_abbr,
                    '0' AS unit_cost,
                    services.unit_price AS price,
                    '1' AS small_val_uom,
                    services.created AS created,
                    services.is_active AS is_active
                FROM 
                    services
                LEFT JOIN 
                    uoms ON uoms.id = services.uom_id
                INNER JOIN 
                    pgroups ON pgroups.id = services.service_group_id
                ORDER BY type, code) AS product";
}

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
            if($aColumns[intval($_GET['iSortCol_' . $i])] == "id"){
                $sOrder .= "group_name ASC, created ". mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
            }else{
                $sOrder .= "group_name ASC, ". $aColumns[intval($_GET['iSortCol_' . $i])] . " " . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
            }
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
    for ($i = 0; $i < count($aColumns) - 6; $i++) {
        if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(code,""),is_active)' || $aColumns[$i] == 'name' || $aColumns[$i] == 'uom'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
        }
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns) - 6; $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if($aColumns[$i] == 'CONCAT_WS("|*|",IFNULL(code,""),is_active)' || $aColumns[$i] == 'name' || $aColumns[$i] == 'uom'){
            $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

/* Customize condition */
$condition = "is_active!=2";

if($category != 'all'){
    // Category
    $sqlSC      = mysql_query("SELECT IFNULL(GROUP_CONCAT(id), 0) FROM pgroups WHERE parent_id = ".$category);
    $rowSC      = mysql_fetch_array($sqlSC);
    $parentId   = $rowSC[0];
    if($subCategory != "all"){
        $parentId = $subCategory;
    }
    $condition .= " AND parent_id IN (SELECT id FROM pgroups WHERE id IN (".$parentId."))";//conditionNew
    // if($subsubCategory != "all"){
    //     $condition .= " AND parent_id = ".$subsubCategory."";
    // }else{
    //     $condition .= $conditionNew;
    // }
}

// if($department != 'all'){
//     $condition .= " AND group_id = ".$department;
// }

if (!eregi("WHERE", $sWhere)) {
    $sWhere .= "WHERE " . $condition;
} else {
    $sWhere .= "AND " . $condition;
}
/*
 * SQL queries
 * Get data to display
 */
echo 
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
";
exit;
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
$lenght     = 0;
$index      = $_GET['iDisplayStart'];
$unit_cost  = 0;
$grouoId    = '$';
$grouoName  = "";
$productChecked = "";
$totalStock = 0;
while ($aRow = mysql_fetch_array($rResult)) {
    $row = array();
    $parcket = 0;
    $fileCatalog = '';
    $array = explode("|*|", $aRow[5]);
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if ($i == 0) {
            /* Special output formatting */             
            if ($aRow[$indexParent]  == $grouoId) {
                $row[] = ++$index;
            }else{
                $index = 0;
                if (!is_null($aRow[$indexParent])) {
                    $indexGp   = ($indexParent - 1);
                    $grouoName = $aRow[$indexGp];
                } else {
                    $grouoName = 'No Group';
                }

                $row[] = '<b class="colspanParent" style="font-size: 16px;">' . $grouoName . '</b>';
                for ($j = 0; $j < count($aColumns) - 1; $j++) {
                    $row[] = '<b class="colspanParentHidden"></b>';
                } 
                $output['aaData'][] = $row;
                $row = array();                
                $row[] = ++$index; 
            }
        } else if ($aColumns[$i] == 'unit_cost') {
            $row[] = number_format($aRow[$i], $costDecimal)." ".$rowSym[0];
        } else if ($aColumns[$i] == 'parent_id') {
            if($aRow[$i] != ''){
                $sqlC = mysql_query("SELECT (SELECT name FROM pgroups AS s WHERE s.id = p.parent_id LIMIT 01) FROM pgroups AS p WHERE p.id = (SELECT parent_id FROM pgroups WHERE id = '".$aRow[$i]."') LIMIT 01");
                $rowC = mysql_fetch_array($sqlC);
                $row[] = $rowC[0];
            } else {
                $row[] = "No Category";
            }
        } else if ($aColumns[$i] == 'sub_category_id') {
            if($aRow[$i] != ''){
                $sqlC = mysql_query("SELECT name FROM pgroups AS p WHERE p.id = (SELECT parent_id FROM pgroups WHERE id = '".$aRow[$i]."') LIMIT 01");
                $rowC = mysql_fetch_array($sqlC);
                $row[] = $rowC[0];
            } else {
                $row[] = "No Sub Category";
            }
        } 
        // else if ($aColumns[$i] == 'sub_sub_category_id') {
        //     if($aRow[$i] != ''){
        //         $sqlC = mysql_query("SELECT name FROM pgroups WHERE id = '".$aRow[$i]."'");
        //         $rowC = mysql_fetch_array($sqlC);
        //         $row[] = $rowC[0];
        //     } else {
        //         $row[] = "No Sub-Sub Category";
        //     }
        // } 
        else if ($i==5) {
            $row[] = $array[0];
        } else if ($aColumns[$i] == 'price') {
            if($aRow[1] == 'Product'){
                $labelPrice = number_format($aRow[$i], $salesDecimal)." ".$rowSym[0];
                if($allowSetPrice &&  $array[1] != 3){
                    $labelPrice .= ' <a href="#" class="setProductPrice" data="'.$aRow[0].'">['.ACTION_SET_PRICE.']</a>';
                }
            } else {
                if($allowSetPrice){
                    $labelPrice = '<input type="text" class="setServicePrice" style="width: 70%;" value="'.number_format($aRow[$i], $salesDecimal).'" data="' . $aRow[0] . '" /> '.$rowSym[0];
                } else {
                    $labelPrice = number_format($aRow[$i], $salesDecimal)." ".$rowSym[0];
                }
            }
            $row[] = $labelPrice;
//        } else if ($aColumns[$i] == 'total_qty') {
//            if($aRow[1] == 'Product'){
//                $totalStock = $aRow[$i];
//                $row[] = '<a href="#" class="viewInventoryProduct" data="'.$aRow[0].'">'.displayQtyByUoM($aRow[$i], $aRow[($indexParent + 1)], $aRow[($indexParent + 3)], $aRow[($indexParent + 2)]).'</a>';
//            } else {
//                $row[] = $aRow[$i];
//            }
        } else if ($aColumns[$i] == 'group_name' || $aColumns[$i] == 'group_id' || $aColumns[$i] == 'uom_id' || $aColumns[$i] == 'uom_abbr' || $aColumns[$i] == 'small_val_uom') {
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $grouoId = $aRow[$indexParent]; 
    $productChecked = '';
    $queryUserPrintProduct = mysql_query("SELECT product_id FROM `user_print_product` WHERE `user_id` = ".$user['User']['id']." AND product_id = ".$aRow[0]."");
    if(mysql_num_rows($queryUserPrintProduct) > 0){
        $productChecked = 'checked="checked"';
    }
    $sqlStock = mysql_query("SELECT SUM(IFNULL(qty,0)) FROM inventories WHERE product_id = ". $aRow[0]);
    $rowStock = mysql_fetch_array($sqlStock);
    $totalStock = $rowStock[0];
    $row[] =
            ($allowView ? '<img alt="' . ($array[1] == 3 ? TABLE_INACTIVE : TABLE_ACTIVE) . '" class="btnActiveInactiveProduct" onmouseover="Tip(\'' . ($array[1] == 3 ? TABLE_INACTIVE : TABLE_ACTIVE) . '\')" rel="' . $aRow[0] . '" name="' . $aRow[5] . '" is-active="'.$array[1].'" style="cursor: pointer;" src="' . $this->webroot . 'img/button/' . ($array[1] == 3 ? 'stop' : 'active') . '.png" />' : '').
            ($allowBarcode && $aRow[1] == 'Product' ? '<input type="checkbox" style="cursor: pointer;" rel="' . $aRow[0] . '" class="btnCheckPrintBarcodeProduct" '.$productChecked.' />' : '') .
            ($allowView ? ' <a href="" class="btnViewProductView" rel="' . $aRow[0] . '" name="' . $aRow[4] . ' - ' . $aRow[1] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ' : ' ') .
            ($allowEdit && $array[1] != 3 ? ' <a href="" class="btnEditProductView" rel="' . $aRow[0] . '" name="' . $aRow[4] . ' - ' . $aRow[1] . '"><img alt="Edit" onmouseover="Tip(\'' . ACTION_EDIT . '\')" src="' . $this->webroot . 'img/button/edit.png" /></a> ' : ' ') .
            ($allowBarcode ? ' <a href="" class="btnPrintBarcodeProduct" rel="' . $aRow[0].'"><img alt="Print Product" onmouseover="Tip(\'' . ACTION_PRINT_BARCODE . '\')" src="' . $this->webroot . 'img/button/print-barcode.png" /></a>' : '').
            ($allowDelete && $totalStock == 0 && $array[1] != 3 ? ' <a href="" class="btnDeleteProductView" rel="' . $aRow[0] . '" name="' . $aRow[4] . ' - ' . $aRow[1] . '"><img alt="Delete" onmouseover="Tip(\'' . ACTION_DELETE . '\')" src="' . $this->webroot . 'img/button/delete.png" /></a>' : '');
    $output['aaData'][] = $row;
    $lenght++;
}

echo json_encode($output);
?>