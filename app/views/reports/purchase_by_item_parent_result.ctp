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
$filename="public/report/purchase_by_item_parent_" . $this->Session->id(session_id()) . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/purchase_by_item_parent_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_PURCHASE_BY_ITEM . '</b><br /><br />';
    $excelContent .= MENU_PURCHASE_BY_ITEM."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_NAME."\t".TABLE_INVOICE_CODE."\t".TABLE_VENDOR."\t".TABLE_LOCATION_GROUP."\t".TABLE_TOTAL_COST;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_NAME; ?></th>
            <th><?php echo TABLE_INVOICE_CODE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_VENDOR; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_TOTAL_COST; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $condition = '';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(order_date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(order_date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        if ($col[3] == '') {
            $condition .= 'status > -1';
        } else {
            $condition .= 'status=' . $col[3];
        }
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'company_id=' . $col[4];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[5] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'branch_id=' . $col[5];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[6] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'location_group_id=' . $col[6];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[10] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'created_by=' . $col[10];
        }

        // declare
        $grandTotalAmount=0;
        ?>

        <?php $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="7" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrVendor=array();
        $arrLocation=array();
        $oldParentId='';
        $oldParentName='';
        $oldProductId='';
        $oldProductName='';
        $subTotalAmount=0;
        $subTotalParentAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                purchase_orders.id,
                                'Bill' AS trans_type,
                                (SELECT parent_id FROM products WHERE id=product_id) AS parent_id,
                                (SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=purchase_order_details.product_id)) AS parent_name,
                                product_id,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                order_date,
                                po_code AS code,
                                (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                (qty + qty_free) AS qty,
                                qty_uom_id,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_cost AS unit_price,
                                total_cost AS total_price
                            FROM purchase_orders
                                INNER JOIN purchase_order_details ON purchase_orders.id=purchase_order_details.purchase_order_id
                            WHERE "
                                . $condition
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . "
                            UNION ALL
                            SELECT
                                purchase_returns.id,
                                'Credit' AS trans_type,
                                (SELECT parent_id FROM products WHERE id=product_id) AS parent_id,
                                (SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=purchase_return_details.product_id)) AS parent_name,
                                product_id,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                order_date,
                                pr_code AS code,
                                (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                (qty + qty_free)*-1 AS qty,
                                qty_uom_id,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_price AS unit_price,
                                total_price*-1 AS total_price
                            FROM purchase_returns
                                INNER JOIN purchase_return_details ON purchase_returns.id=purchase_return_details.purchase_return_id
                            WHERE "
                                . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . "
                            ORDER BY parent_name,product_name,order_date");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrVendor[] = $data['vendor_name'];
            $arrLocation[] = $data['location_group_name'];
        ?>

            <?php if($data['product_id']!=$oldProductId){ ?>
            <?php
            if($oldProductName!=''){
                
            }
            $subTotalAmount=0;
            ?>
            <?php } ?>

            <?php if($data['parent_id']!=$oldParentId){ ?>
            <?php if($oldParentName!=''){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldParentName."\t\t\t\t".number_format($subTotalParentAmount,2); ?>
            <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldParentName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalParentAmount,2); ?></td></tr>
            <?php
            }
            $subTotalParentAmount=0;
            ?>
            <?php } ?>
            
            <?php
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
        <?php $excelContent .= "\n".$index."\t".$oldParentName."\t\t\t\t".number_format($subTotalParentAmount,2); ?>
        <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldParentName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalParentAmount,2); ?></td></tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t".number_format($totalAmount,2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="5" style="font-size: 14px;">Total Product</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
        <?php } ?>

        <?php if($col[7] == '' && $col[8] == '' && $col[9] == ''){ ?>

            <?php $excelContent .= "\n\n".'Service'; ?>
            <tr><td colspan="7">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="7" style="font-size: 14px;">Service</td></tr>
            <?php
            $index=1;
            $oldServiceId='';
            $oldServiceName='';
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    purchase_orders.id,
                                    'Bill' AS trans_type,
                                    service_id,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    order_date,
                                    po_code AS code,
                                    (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    qty,
                                    NULL AS qty_uom_name,
                                    unit_cost AS unit_price,
                                    total_cost AS total_price
                                FROM purchase_orders
                                    INNER JOIN purchase_order_services ON purchase_orders.id=purchase_order_services.purchase_order_id
                                WHERE "
                                    . $condition
                                    . "
                                UNION ALL
                                SELECT
                                    purchase_returns.id,
                                    'Credit' AS trans_type,
                                    service_id,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    order_date,
                                    pr_code AS code,
                                    (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    qty*-1 AS qty,
                                    NULL AS qty_uom_name,
                                    unit_price AS unit_price,
                                    total_price*-1 AS total_price
                                FROM purchase_returns
                                    INNER JOIN purchase_return_services ON purchase_returns.id=purchase_return_services.purchase_return_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . "
                                ORDER BY service_name,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[] = $data['code'];
                $arrVendor[] = $data['vendor_name'];
                $arrLocation[] = $data['location_group_name'];
            ?>

                <?php if($data['service_id']!=$oldServiceId){ ?>
                <?php if($oldServiceName!=''){ ?>
                <?php $excelContent .= "\n".$index."\t".$oldServiceName."\t\t\t\t".number_format($subTotalAmount,2); ?>
                <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldServiceName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
                <?php
                }
                $subTotalAmount=0;
                ?>
                <?php } ?>
                
                <?php
                $subTotalAmount+=$data['total_price'];
                $totalAmount+=$data['total_price'];
                $grandTotalAmount+=$data['total_price'];
                $oldServiceId=$data['service_id'];
                $oldServiceName=$data['service_name'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldServiceName."\t\t\t\t".number_format($subTotalAmount,2); ?>
            <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldServiceName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t\t\t\t".number_format($totalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="5" style="font-size: 14px;">Total Service</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
            <?php } ?>

            <?php $excelContent .= "\n\n".'Miscellaneous'; ?>
            <tr><td colspan="7">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="7" style="font-size: 14px;">Miscellaneous</td></tr>
            <?php
            $index=1;
            $oldMiscName='';
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    purchase_orders.id,
                                    'Bill' AS trans_type,
                                    description,
                                    order_date,
                                    po_code AS code,
                                    (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    qty,
                                    (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                    unit_cost AS unit_price,
                                    total_cost AS total_price
                                FROM purchase_orders
                                    INNER JOIN purchase_order_miscs ON purchase_orders.id=purchase_order_miscs.purchase_order_id
                                WHERE "
                                    . $condition
                                    . "
                                UNION ALL
                                SELECT
                                    purchase_returns.id,
                                    'Credit' AS trans_type,
                                    description,
                                    order_date,
                                    pr_code AS code,
                                    (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    qty*-1 AS qty,
                                    (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                    unit_price AS unit_price,
                                    total_price*-1 AS total_price
                                FROM purchase_returns
                                    INNER JOIN purchase_return_miscs ON purchase_returns.id=purchase_return_miscs.purchase_return_id
                                WHERE "
                                    . str_replace(array('status=2','status=3'),array('FALSE','status=2'),$condition)
                                    . "
                                ORDER BY description,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[] = $data['code'];
                $arrVendor[] = $data['vendor_name'];
                $arrLocation[] = $data['location_group_name'];
            ?>

                <?php if($data['description']!=$oldMiscName){ ?>
                <?php if($oldMiscName!=''){ ?>
                <?php $excelContent .= "\n".$index."\t".$oldMiscName."\t\t\t\t".number_format($subTotalAmount,2); ?>
                <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldMiscName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
                <?php
                }
                $subTotalAmount=0;
                ?>
                <?php } ?>

                <?php
                $subTotalAmount+=$data['total_price'];
                $totalAmount+=$data['total_price'];
                $grandTotalAmount+=$data['total_price'];
                $oldMiscName=$data['description'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".$index."\t".$oldMiscName."\t\t\t\t".number_format($subTotalAmount,2); ?>
            <tr><td class="first"><?php echo $index++; ?></td><td colspan="4"><?php echo $oldMiscName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php $excelContent .= "\n".'Total Miscellaneous'."\t\t\t\t\t".number_format($totalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="5" style="font-size: 14px;">Total Miscellaneous</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
            <?php } ?>

        <?php } ?>

        <?php $excelContent .= "\n\n".'Grand Total Amount'."\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrVendor))."\t".sizeof(array_unique($arrLocation))."\t".number_format($grandTotalAmount,2); ?>
        <tr><td colspan="7">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="2" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrVendor)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
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