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
$filename="public/report/sales_by_item_type_parent_pos_" . $this->Session->id(session_id()) . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_item_type_parent_pos_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_POS_BY_ITEM_TYPE . '</b><br /><br />';
    $excelContent .= MENU_REPORT_POS_BY_ITEM_TYPE."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_NAME."\t".TABLE_INVOICE_CODE."\t".TABLE_LOCATION_GROUP."\t".TABLE_QTY."\t".GENERAL_AMOUNT;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_NAME; ?></th>
            <th><?php echo TABLE_INVOICE_CODE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $condition = 'sales_orders.is_pos=1';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(sales_orders.order_date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(sales_orders.order_date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        if ($col[3] == '') {
            $condition .= 'sales_orders.status >= 0';
        } else {
            $condition .= 'sales_orders.status = ' . $col[3];
        }
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_orders.company_id=' . $col[4];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_orders.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[5] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_orders.location_group_id=' . $col[5];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_orders.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[9] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            if($col[9] == 1){
                $condition .= 'qty > 0';
            } else {
                $condition .= 'qty_free > 0';
            }
        }
        if ($col[10] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_orders.created_by=' . $col[10];
        }

        // declare
        $grandTotalQty=0;
        $grandTotalAmount=0;
        $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="6" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrLocation=array();
        $oldPgroupId='';
        $oldPgroupName='';
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $subTotalParentQty=0;
        $totalQty=0;
        $subTotalAmount=0;
        $subTotalParentAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                (SELECT pgroup_id FROM product_pgroups WHERE product_id=sales_order_details.product_id LIMIT 1) AS pgroup_id,
                                IFNULL((SELECT name FROM pgroups WHERE id=(SELECT pgroup_id FROM product_pgroups WHERE product_id=sales_order_details.product_id LIMIT 1)), 'No Type') AS pgroup_name,
                                product_id,
                                CONCAT_WS(' ',products.code,'-',products.name) AS product_name,
                                sales_orders.order_date AS order_date,
                                sales_orders.so_code AS code,
                                products.price_uom_id AS qty_uom_id,
                                (SELECT name FROM location_groups WHERE id=sales_orders.location_group_id) AS location_name,
                                sales_order_details.qty AS qty,
                                sales_order_details.qty_free AS qty_free,
                                sales_order_details.conversion AS conversion,
                                (SELECT name FROM uoms WHERE id=sales_order_details.qty_uom_id) AS qty_uom_name,
                                sales_order_details.unit_price AS unit_price,
                                sales_order_details.total_price AS total_price
                            FROM sales_orders 
                                INNER JOIN sales_order_details ON sales_orders.id=sales_order_details.sales_order_id
                                INNER JOIN products ON products.id = sales_order_details.product_id
                            WHERE "
                                . $condition
                                . ($col[6] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[6] . ')':'')
                                . ($col[7] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[7] :'')
                                . ($col[8] != ''?' AND product_id=' . $col[8] :'')
                                . "
                            ORDER BY pgroup_name,product_name,order_date");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrLocation[] = $data['location_name'];
            if($data['pgroup_id']!=$oldPgroupId){ 
                if($oldPgroupName!=''){ 
                    $excelContent .= "\n".$index."\t".$oldPgroupName."\t\t\t".$subTotalParentQty."\t".number_format($subTotalParentAmount, 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td colspan="3"><?php echo $oldPgroupName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalParentQty, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalParentAmount, 2); ?></td>
            </tr>
            <?php
                }
                $subTotalParentQty=0;
                $subTotalParentAmount=0;
            }
            if($col[9] == 1){
                $qtyPOS = $data['qty'];
            } else if($col[9] == 2){
                $qtyPOS = $data['qty_free'];
            } else {
                $qtyPOS = $data['qty_free'] + $data['qty'];
            }
            // Total Qty
            $subTotalQty += ($qtyPOS * $data['conversion']);
            $subTotalParentQty += ($qtyPOS * $data['conversion']);
            $totalQty += ($qtyPOS * $data['conversion']);
            $grandTotalQty += ($qtyPOS * $data['conversion']);
            // Total Amount
            $subTotalAmount += ($qtyPOS * $data['unit_price']);
            $subTotalParentAmount += ($qtyPOS * $data['unit_price']);
            $totalAmount+= ($qtyPOS * $data['unit_price']);
            $grandTotalAmount+= ($qtyPOS * $data['unit_price']);
            // Product Info
            $oldPgroupId=$data['pgroup_id'];
            $oldPgroupName=$data['pgroup_name'];
            $oldProductId=$data['product_id'];
            $oldProductName=$data['product_name'];
        }
        if(mysql_num_rows($query)){ 
            $excelContent .= "\n".$index."\t".$oldPgroupName."\t\t\t".$subTotalParentQty."\t".number_format($subTotalParentAmount, 2); ?>
        <tr>
            <td class="first"><?php echo $index++; ?></td>
            <td colspan="3"><?php echo $oldPgroupName; ?></td>
            <td style="text-align: center;"><?php echo number_format($subTotalParentQty, 2); ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalParentAmount, 2); ?></td>
        </tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t".$totalQty."\t".number_format($totalAmount, 2); ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="4" style="font-size: 14px;">Total Product</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalQty, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
        </tr>
        <?php } ?>

        <?php if($col[6] == '' && $col[7] == '' && $col[8] == ''){ 
            $excelContent .= "\n\n".'Service'; 
        ?>
            <tr><td colspan="6">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="6" style="font-size: 14px;">Service</td></tr>
            <?php
            $index=1;
            $oldServiceId='';
            $oldServiceName='';
            $subTotalQty=0;
            $totalQty=0;
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    service_id,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    order_date,
                                    so_code AS code,
                                    (SELECT name FROM locations WHERE id=location_id) AS location_name,
                                    qty,
                                    qty_free,
                                    NULL AS qty_uom_name,
                                    unit_price,
                                    total_price
                                FROM sales_orders
                                    INNER JOIN sales_order_services ON sales_orders.id=sales_order_services.sales_order_id
                                WHERE " . $condition . "
                                ORDER BY service_name,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[] = $data['code'];
                $arrLocation[] = $data['location_name'];
                if($data['service_id']!=$oldServiceId){ 
                    if($oldServiceName!=''){ 
                        $excelContent .= "\n".$index."\t".$oldServiceName."\t\t\t".$subTotalQty."\t".number_format($subTotalAmount, 2); ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td colspan="3"><?php echo $oldServiceName; ?></td>
                    <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
                </tr>
                <?php
                    }
                    $subTotalQty=0;
                    $subTotalAmount=0;
                }
                if($col[9] == 0){
                    $qtyPOS = $data['qty'];
                } else if($col[9] == 1){
                    $qtyPOS = $data['qty_free'];
                } else {
                    $qtyPOS = $data['qty_free'] + $data['qty'];
                }
                // Total Qty
                $subTotalQty    += $qtyPOS;
                $totalQty       += $qtyPOS;
                $grandTotalQty  += $qtyPOS;
                // Total Amount
                $subTotalAmount   += ($qtyPOS * $data['unit_price']);
                $totalAmount      += ($qtyPOS * $data['unit_price']);
                $grandTotalAmount += ($qtyPOS * $data['unit_price']);
                // Service Info
                $oldServiceId   = $data['service_id'];
                $oldServiceName = $data['service_name'];
             }
             if(mysql_num_rows($query)){ 
                 $excelContent .= "\n".$index."\t".$oldServiceName."\t\t\t".$subTotalQty."\t".number_format($subTotalAmount, 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td colspan="3"><?php echo $oldServiceName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
            </tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t\t\t".$totalQty."\t".number_format($totalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="4" style="font-size: 14px;">Total Service</td>
                <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalQty, 2); ?></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
            </tr>
        <?php 
            }    
        }
        $excelContent .= "\n\n".'Grand Total Amount'."\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrLocation))."\t".$grandTotalQty."\t".number_format($grandTotalAmount, 2); ?>
        <tr><td colspan="6">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="2" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalQty, 2); ?></td>
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