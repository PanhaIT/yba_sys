<?php

$rnd = rand();
$tblName = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/inventory_activity_parent_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/inventory_activity_parent_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_PRODUCT_INVENTORY_ACTIVITY . '</b><br /><br />';
    $excelContent .= MENU_PRODUCT_INVENTORY_ACTIVITY."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    if($_POST['location_group_id']!='' || $_POST['location_id']!='' || $_POST['pgroup_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if($_POST['location_group_id']!='') {
        $query=mysql_query("SELECT name FROM location_groups WHERE id=".$_POST['location_group_id']);
        $data=mysql_fetch_array($query);
        $msg .= '<b>'.TABLE_LOCATION_GROUP.'</b>: '.$data[0];
        $excelContent .= TABLE_LOCATION_GROUP.': '.$data[0];
    }
    if($_POST['location_id']!='') {
        $query=mysql_query("SELECT name FROM locations WHERE id=".$_POST['location_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>'.TABLE_LOCATION.'</b>: '.$data[0];
        $excelContent .= TABLE_LOCATION.': '.$data[0];
    }
    if($_POST['pgroup_id']!='') {
        $query=mysql_query("SELECT name FROM pgroups WHERE id=".$_POST['pgroup_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <br/><b>'.GENERAL_TYPE.'</b>: '.$data[0];
        $excelContent .= GENERAL_TYPE.': '.$data[0];
    }
    $msg .= '<br /><br />';
    $excelContent .= "\n\n";
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_PRODUCT."\t".TABLE_INVOICE_CODE."\t".TABLE_NAME."\t".TABLE_LOCATION."\t".TABLE_QTY."\t".TABLE_UOM."\t".GENERAL_AMOUNT;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $conPGroup  = "";
        $conParent  = "";
        $conProduct = "";
        $conCompany = "";
        $conVendor  = "";
        $conditionGetLocation = "";
        $condition  = '';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(order_date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(order_date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'status = 2';
        
        // Get Location Group
        if ($col[3] != '') {
            $conditionGetLocation .= " = ".$col[3];
        }else{
            $conditionGetLocation .= " IN (SELECT user_location_groups.location_group_id FROM user_location_groups INNER JOIN location_groups ON location_groups.id = user_location_groups.location_group_id AND location_groups.location_group_type_id != 1 WHERE user_location_groups.user_id = ".$user['User']['id']." GROUP BY user_location_groups.location_group_id)";
        }
        
        // Get Location
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'location_id=' . $col[4];
            $conditionGetLocation .= " AND id = ".$col[4];
        }
        
        // Get Product Group
        if($col[5] != ""){
            // Get Product
            if($col[7] != ""){
                $conProduct = " AND product_id = ".$col[7];
            }
            $conPGroup = " AND products.id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ".$col[5].$conProduct.")";
        }
        
        // Get Product Parent
        if($col[7] != ""){
            $conParent = " AND products.parent_id =".$col[7];
        }
        
        // Get Product
        if($col[7] != ""){
            if($conPGroup == ""){
                $conPGroup = " AND products.id IN (".$col[7].")";
            }
        }
        
        $joinProducts = " INNER JOIN products ON";
        $joinUom      = " INNER JOIN uoms ON uoms.id = products.price_uom_id";
        $tableDaily = "";
        $filedDaily = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in + total_ven_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out + total_ven_consign_out)) AS total_qty, uoms.name AS u_name, products.price_uom_id AS qty_uom_id, products.parent_id AS parent_id, CONCAT_WS(' ',products.code,products.name) AS product_name, products.id AS product_id, (SELECT CONCAT(' ',code,name) FROM products WHERE id = products.parent_id LIMIT 1) AS parent_name";
        $conditionDaily = " products.is_active = 1 AND date ".$conPGroup.$conVendor.$conParent.$conCompany;
        $groupByDaily = "GROUP BY product_id";
        $tableInventory = "";
        $conditionInventory = "";
        $groupByInventory = "";
        // List Location 
        $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id' . $conditionGetLocation . ' GROUP BY id');
        if(@mysql_num_rows($queryLocationList)){
            if(mysql_num_rows($queryLocationList) == 1){
                while($dataLocationList=mysql_fetch_array($queryLocationList)){
                    // Stock Daily
                    $tableDaily .= "SELECT ".$filedDaily." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id".$joinUom." WHERE".$conditionDaily." ".$groupByDaily;

                    // Inventory Total
                    $conditionInventory = " products.is_active = 1 ".$conPGroup.$conParent.$conCompany;
                    $groupByInventory = " GROUP BY ".$dataLocationList['location_id']."_inventory_totals.product_id";
                    $tableInventory .= "SELECT total_qty AS qty, uoms.name AS u_name, products.price_uom_id AS qty_uom_id, products.parent_id AS parent_id, products.code AS product_code, products.name AS product_name, products.id AS product_id, (SELECT CONCAT(' ',code,name) FROM products WHERE id = products.parent_id LIMIT 1) AS parent_name FROM ".$dataLocationList['location_id']."_inventory_totals".$joinProducts." (products.id = ".$dataLocationList['location_id']."_inventory_totals.product_id)".$joinUom." WHERE ".$conditionInventory.$groupByInventory;
                }
            }else{
                $locId = 1;
                $tmpLocationId = 0;
                while($dataLocationList=mysql_fetch_array($queryLocationList)){
                    if($locId == 1){
                        // Stock Daily
                        $tableDaily .= "SELECT ".$filedDaily." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id".$joinUom." WHERE ".$conditionDaily." ".$groupByDaily;

                        // Inventory Total
                        $conditionInventory = " products.is_active = 1 ".$conPGroup.$conParent.$conCompany;
                        $groupByInventory = " GROUP BY ".$dataLocationList['location_id']."_inventory_totals.product_id";
                        $tableInventory .= "SELECT total_qty AS qty, uoms.name AS u_name, products.price_uom_id AS qty_uom_id, products.parent_id AS parent_id, products.code AS product_code, products.name AS product_name, products.id AS product_id, (SELECT CONCAT(' ',code,name) FROM products WHERE id = products.parent_id LIMIT 1) AS parent_name FROM ".$dataLocationList['location_id']."_inventory_totals".$joinProducts." (products.id = ".$dataLocationList['location_id']."_inventory_totals.product_id)".$joinUom." WHERE ".$conditionInventory.$groupByInventory;
                    }else{
                        // Stock Daily
                        $tableDaily .= " UNION ALL SELECT ".$filedDaily." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id".$joinUom." WHERE ".$conditionDaily." ".$groupByDaily;

                        // Inventory Total
                        $conditionInventory = " products.is_active = 1 ".$conPGroup.$conParent.$conCompany;
                        $groupByInventory = " GROUP BY ".$dataLocationList['location_id']."_inventory_totals.product_id";
                        $tableInventory .= " UNION ALL SELECT total_qty AS qty, uoms.name AS u_name, products.price_uom_id AS qty_uom_id, products.parent_id AS parent_id, products.code AS product_code, products.name AS product_name, products.id AS product_id, (SELECT CONCAT(' ',code,name) FROM products WHERE id = products.parent_id LIMIT 1) AS parent_name FROM ".$dataLocationList['location_id']."_inventory_totals".$joinProducts." (products.id = ".$dataLocationList['location_id']."_inventory_totals.product_id)".$joinUom." WHERE ".$conditionInventory.$groupByInventory;
                    }
                    $tmpLocationId = $dataLocationList['location_id'];
                    $locId++;
                }
            }
            $dateTo   = "DATE(date)<='".dateConvert($col[2])."'";
            $tableDailyEnding    = str_replace("date", $dateTo, $tableDaily);

            $sqlCmtInventory = "SELECT SUM(qty) AS qty, u_name, qty_uom_id, IFNULL(parent_id,0) AS parent_id, product_id, product_name, IFNULL(parent_name,'No Parent') AS parent_name FROM (".$tableInventory.") AS inventory GROUP BY product_id";
            $sqlCmtDaily     = "SELECT SUM(total_qty) AS qty, u_name, qty_uom_id, IFNULL(parent_id,0) AS parent_id, product_id, product_name, IFNULL(parent_name,'No Parent') AS parent_name FROM (".$tableDailyEnding.") AS stockDaily GROUP BY product_id";
        } else {
            $sqlCmtInventory = '';
            $sqlCmtDaily = '';
        }
        ?>

        <?php $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="4" style="font-size: 14px;">Product</td>
        </tr>
        <?php
        $index=1;
        $oldParentId='';
        $oldParentName='';
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $subTotalParentQty=0;
        $totalQty=0;
        $dateNow = date("Y-m-d");
        if($sqlCmtInventory != '' && $sqlCmtDaily != ''){
            if(strtotime($dateNow) == strtotime($dateTo)){
                $sql = $sqlCmtInventory;
            }else{
                $sql = $sqlCmtDaily;
            }
            $query=mysql_query($sql);
            while($data=mysql_fetch_array($query)){
                if($data['product_id']!=$oldProductId){
                    if($oldProductName!=''){

                    }
                    $subTotalQty=0;
                } 
                // Smallest Uom
                $s=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$data['qty_uom_id']."
                            UNION
                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$data['qty_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$data['qty_uom_id'].")
                            ORDER BY conversion ASC");
                $small_label = "";
                $small_uom = 1;
                while($r=mysql_fetch_array($s)){
                    $small_label = $r['abbr'];
                    $small_uom = floatval($r['conversion']);
                }
                if($data['parent_id'] > 0){
                    $sqlParent = mysql_query("SELECT CONCAT_WS(' ',code,name) AS name FROM products WHERE id = ".$data['parent_id']);
                    $rowParent = mysql_fetch_array($sqlParent);
                    $parentName = $rowParent['name'];
                }else{
                    $parentName = 'No Parent';
                }
                if($data['parent_id']!=$oldParentId){
                    if($oldParentName!=''){
                       $excelContent .= "\n".$index."\t".$oldParentName."\t".number_format($subTotalParentQty,0)."\t\t"; 
            ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $oldParentName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalParentQty,0); ?></td>
                <td></td>
            </tr>
        <?php
                    }
                    $subTotalParentQty=0;
                }
                $subTotalQty+=$data['qty'];
                $subTotalParentQty+=$data['qty'];
                $totalQty+=$data['qty'];
                $oldParentId=$data['parent_id'];
                $oldParentName=$parentName;
                $oldProductId=$data['product_id'];
                $oldProductName=$data['product_name'];
            
            } 
        }
        if(@mysql_num_rows($query)){ 
            $excelContent .= "\n".$index."\t".$oldParentName."\t".number_format($subTotalParentQty,0)."\t"; ?>
        <tr>
            <td class="first"><?php echo $index++; ?></td>
            <td><?php echo $oldParentName; ?></td>
            <td style="text-align: center;"><?php echo number_format($subTotalParentQty,0); ?></td>
            <td></td>
        </tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t".number_format($totalQty,0)."\t"; ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="2" style="font-size: 14px;">Total Product</td>
            <td style="text-align: center;font-size: 14px;"><?php echo number_format($totalQty,0); ?></td>
            <td></td>
        </tr>
        <?php 
        } 
        if($col[5] == '' && $col[7] == '' && $col[7] == ''){ ?>

            <?php $excelContent .= "\n\n".'Service'; ?>
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="first" colspan="5" style="font-size: 14px;">Service</td>
            </tr>
            <?php
            $index=1;
            $oldServiceId='';
            $oldServiceName='';
            $subTotalQty=0;
            $totalQty=0;
            $query=mysql_query("SELECT
                                    service_id,
                                    order_date,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    qty
                                FROM purchase_orders
                                    INNER JOIN purchase_order_services ON purchase_orders.id=purchase_order_services.purchase_order_id
                                WHERE "
                                    . $condition
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    service_id,
                                    order_date,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    qty*-1 AS qty
                                FROM purchase_returns
                                    INNER JOIN purchase_return_services ON purchase_returns.id=purchase_return_services.purchase_return_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    service_id,
                                    order_date,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    qty*-1 AS qty
                                FROM sales_orders
                                    INNER JOIN sales_order_services ON sales_orders.id=sales_order_services.sales_order_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    service_id,
                                    order_date,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    qty
                                FROM credit_memos
                                    INNER JOIN credit_memo_services ON credit_memos.id=credit_memo_services.credit_memo_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                ORDER BY service_name,order_date");
            while($data=mysql_fetch_array($query)){
            ?>

                <?php if($data['service_id']!=$oldServiceId){ ?>
                <?php if($oldServiceName!=''){ ?>
                <?php $excelContent .= "\n".$index."\t".$oldServiceName."\t".number_format($subTotalQty,0)."\t"; ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $oldServiceName; ?></td>
                    <td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td>
                    <td></td>
                </tr>
                <?php
                }
                $subTotalQty=0;
                ?>
                <?php } ?>
                
                <?php
                $subTotalQty+=$data['qty'];
                $totalQty+=$data['qty'];
                $oldServiceId=$data['service_id'];
                $oldServiceName=$data['service_name'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldServiceName."\t".number_format($subTotalQty,0)."\t"; ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $oldServiceName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td>
                <td></td>
            </tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t".number_format($totalQty,0)."\t"; ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="2" style="font-size: 14px;">Total Service</td>
                <td style="text-align: center;"><?php echo number_format($totalQty,0); ?></td>
                <td></td>
            </tr>
            <?php } ?>

            <?php $excelContent .= "\n\n".'Miscellaneous'; ?>
            <tr><td colspan="5">&nbsp;</td></tr>
            <tr style="font-weight: bold;">
                <td class="first" colspan="5" style="font-size: 14px;">Miscellaneous</td>
            </tr>
            <?php
            $index=1;
            $oldMiscName='';
            $subTotalQty=0;
            $totalQty=0;
            $query=mysql_query("SELECT
                                    order_date,
                                    description,
                                    qty
                                FROM purchase_orders
                                    INNER JOIN purchase_order_miscs ON purchase_orders.id=purchase_order_miscs.purchase_order_id
                                WHERE "
                                    . $condition
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    order_date,
                                    description,
                                    qty*-1 AS qty
                                FROM purchase_returns
                                    INNER JOIN purchase_return_miscs ON purchase_returns.id=purchase_return_miscs.purchase_return_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    order_date,
                                    description,
                                    qty*-1 AS qty
                                FROM sales_orders
                                    INNER JOIN sales_order_miscs ON sales_orders.id=sales_order_miscs.sales_order_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                UNION ALL
                                SELECT
                                    order_date,
                                    description,
                                    qty
                                FROM credit_memos
                                    INNER JOIN credit_memo_miscs ON credit_memos.id=credit_memo_miscs.credit_memo_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . ($col[3] != ''?' AND location_group_id=' . $col[3] :' AND location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')')
                                    . "
                                ORDER BY description,order_date");
            while($data=mysql_fetch_array($query)){
            ?>

                <?php if($data['description']!=$oldMiscName){ ?>
                <?php if($oldMiscName!=''){ ?>
                <?php $excelContent .= "\n".$index."\t".$oldMiscName."\t".number_format($subTotalQty,0)."\t"; ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $oldMiscName; ?></td>
                    <td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td>
                    <td></td>
                </tr>
                <?php
                }
                $subTotalQty=0;
                ?>
                <?php } ?>

                <?php
                $subTotalQty+=$data['qty'];
                $totalQty+=$data['qty'];
                $oldMiscName=$data['description'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldMiscName."\t".number_format($subTotalQty,0)."\t"; ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $oldMiscName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td>
                <td></td>
            </tr>
            <?php $excelContent .= "\n".'Total Miscellaneous'."\t\t".number_format($totalQty,0)."\t"; ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="2" style="font-size: 14px;">Total Miscellaneous</td>
                <td style="text-align: center;"><?php echo number_format($totalQty,0); ?></td>
                <td></td>
            </tr>
            <?php } ?>

        <?php } ?>
    </table>
</div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

?>