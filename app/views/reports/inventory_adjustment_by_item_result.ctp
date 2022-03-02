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
$filename="public/report/inventory_adjustment_by_item_" . $this->Session->id(session_id()) . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/inventory_adjustment_by_item_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_PRODUCT_INVENTORY_ADJUSTMENT_BY_ITEM . '</b><br /><br />';
    $excelContent .= MENU_PRODUCT_INVENTORY_ADJUSTMENT_BY_ITEM."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_NAME."\t".TABLE_REFERENCE."\t".TABLE_CREATED_BY."\t".TABLE_LOCATION."\t".TABLE_QTY."\t".TABLE_UOM."\t".TABLE_TOTAL_COST;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_NAME; ?></th>
            <th><?php echo TABLE_REFERENCE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_CREATED_BY; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_TOTAL_COST; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $condition = '';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(cycle_products.date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(cycle_products.date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        if ($col[3] == '') {
            $condition .= 'cycle_products.status > -1';
        } else {
            $condition .= 'cycle_products.status =' . $col[3];
        }
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'cycle_products.company_id=' . $col[4];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= "cycle_products.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")";
        }
        if ($col[5] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'cycle_products.branch_id=' . $col[5];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= "cycle_products.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].")";
        }
        if ($col[6] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'cycle_products.location_group_id=' . $col[6];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= "cycle_products.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")";
        }
        if ($col[10] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'cycle_products.created_by=' . $col[10];
        }

        // declare
        $grandTotalAmount=0;
        ?>

        <?php $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="8" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrCreator=array();
        $arrLocation=array();
        $oldParentId='';
        $oldParentName='';
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $subTotalParentQty=0;
        $totalQty=0;
        $subTotalAmount=0;
        $subTotalParentAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                cycle_products.id,
                                'Inv Adj' AS trans_type,
                                (SELECT parent_id FROM products WHERE id=product_id) AS parent_id,
                                (SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=cycle_product_details.product_id)) AS parent_name,
                                product_id,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                date,
                                reference AS code,
                                (SELECT CONCAT_WS(' ',first_name,last_name) FROM users WHERE id=created_by) AS creator_name,
                                (SELECT name FROM location_groups WHERE id=cycle_products.location_group_id) AS location_name,
                                qty_difference AS qty,
                                (SELECT price_uom_id FROM products WHERE id=product_id) AS qty_uom_id,
                                (SELECT name FROM uoms WHERE id=(SELECT price_uom_id FROM products WHERE id=product_id)) AS qty_uom_name,
                                cycle_product_details.unit_cost AS unit_price
                            FROM cycle_products
                                INNER JOIN cycle_product_details ON cycle_products.id=cycle_product_details.cycle_product_id
                            WHERE "
                                . $condition
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . "
                            ORDER BY parent_name,product_name,date") or die(mysql_error());
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrCreator[] = $data['creator_name'];
            $arrLocation[] = $data['location_name'];
        ?>

            <?php if($data['product_id']!=$oldProductId){ ?>
            <?php if($oldProductName!=''){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldProductName."\t\t\t\t".$subTotalQty."\t".$dataSmallestUom['name']."\t".number_format($subTotalAmount,2); ?>
            <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldProductName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td><?php echo $dataSmallestUom['name']; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php
            }
            $subTotalQty=0;
            $subTotalAmount=0;
            ?>
            <?php } ?>

            <?php
            // Smallest Uom
            $querySmallestUom=mysql_query(" SELECT name,1 AS conversion FROM uoms WHERE id=" . $data['qty_uom_id'] . "
                                            UNION
                                            SELECT name,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=uoms.id AND to_uom_id=" . $data['qty_uom_id'] . ") AS conversion FROM uoms WHERE id IN (SELECT from_uom_id FROM uom_conversions WHERE is_active=1 AND to_uom_id=" . $data['qty_uom_id'] . ")
                                            UNION
                                            SELECT name,(SELECT 1/value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $data['qty_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $data['qty_uom_id'] . ")
                                            ORDER BY conversion ASC LIMIT 1");
            $dataSmallestUom=mysql_fetch_array($querySmallestUom);

            // Price UoM
            $queryProductUom=mysql_query("  SELECT name,1 AS conversion FROM uoms WHERE id=(SELECT price_uom_id FROM products WHERE id=" . $data['product_id'] . ")
                                            UNION
                                            SELECT name,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=uoms.id AND to_uom_id=(SELECT price_uom_id FROM products WHERE id=" . $data['product_id'] . ")) AS conversion FROM uoms WHERE id IN (SELECT from_uom_id FROM uom_conversions WHERE is_active=1 AND to_uom_id=(SELECT price_uom_id FROM products WHERE id=" . $data['product_id'] . "))
                                            UNION
                                            SELECT name,(SELECT 1/value FROM uom_conversions WHERE is_active=1 AND from_uom_id=(SELECT price_uom_id FROM products WHERE id=" . $data['product_id'] . ") AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=(SELECT price_uom_id FROM products WHERE id=" . $data['product_id'] . "))
                                            ORDER BY conversion ASC LIMIT 1");
            $dataProductUom=mysql_fetch_array($queryProductUom);
            $data['unit_price']=($data['unit_price']*$dataProductUom['conversion']);
            $data['total_price']=$data['unit_price']*$data['qty'];
            ?>

            <?php if($data['parent_id']!=$oldParentId){ ?>
            <?php if($oldParentName!=''){ ?>
            <?php $excelContent .= "\n".'Total '.$oldParentName."\t\t\t\t\t\t\t".number_format($subTotalParentAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="7">Total <?php echo $oldParentName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalParentAmount,2); ?></td></tr>
            <?php
            }
            $index=1;
            $subTotalParentQty=0;
            $subTotalParentAmount=0;
            ?>
            <?php $excelContent .= "\n".$data['parent_name']; ?>
            <tr><td class="first" colspan="8" style="font-weight: bold;"><?php echo $data['parent_name']; ?></td></tr>
            <?php } ?>
            
            <?php
            $subTotalQty+=$data['qty'];
            $subTotalParentQty+=$data['qty'];
            $totalQty+=$data['qty'];
            $subTotalAmount+=$data['total_price'];
            $subTotalParentAmount+=$data['total_price'];
            $totalAmount+=$data['total_price'];
            $grandTotalAmount+=$data['total_price'];
            $oldParentId=$data['parent_id'];
            $oldParentName=$data['parent_name'];
            $oldProductId=$data['product_id'];
            $oldProductName=$data['product_name'];
            ?>

        <?php } ?>

        <?php if(mysql_num_rows($query)){ ?>
        <?php $excelContent .= "\n".$index."\t".$oldProductName."\t\t\t\t".$subTotalQty."\t".$dataSmallestUom['name']."\t".number_format($subTotalAmount,2); ?>
        <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldProductName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td><?php echo $dataSmallestUom['name']; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
        <?php $excelContent .= "\n".'Total '.$oldParentName."\t\t\t\t\t\t\t".number_format($subTotalParentAmount,2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="7">Total <?php echo $oldParentName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalParentAmount,2); ?></td></tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t\t".number_format($totalAmount,2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="7" style="font-size: 14px;">Total Product</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
        <?php } ?>

        <?php $excelContent .= "\n\n".'Grand Total Amount'."\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrCreator))."\t".sizeof(array_unique($arrLocation))."\t\t\t".number_format($grandTotalAmount,2); ?>
        <tr><td colspan="8">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="2" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCreator)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
            <td colspan="2"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmount,2); ?></td>
        </tr>
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