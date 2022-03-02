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
$filename="public/report/purchase_by_item_detail_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");

        $(".btnPrintPurchaseByItem").click(function(event){
            event.preventDefault();
            if($(this).attr("trans_type")=="Bill"){
                var url = "<?php echo $this->base . '/'; ?>purchase_orders/printInvoice/"+$(this).attr("rel");
            }else{
                var url = "<?php echo $this->base . '/'; ?>purchase_returns/printInvoice/"+$(this).attr("rel");
            }
            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });

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
            window.open("<?php echo $this->webroot; ?>public/report/purchase_by_item_detail_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
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
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_DATE."\t".TABLE_INVOICE_CODE."\t".TABLE_VENDOR."\t".TABLE_LOCATION_GROUP."\t".TABLE_QTY."\t".TABLE_UOM."\t".TABLE_UNIT_COST."\t".GENERAL_DISCOUNT."\t".TABLE_TOTAL_COST;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
            <th><?php echo TABLE_INVOICE_CODE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_VENDOR; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 90px !important;"><?php echo TABLE_UNIT_COST; ?></th>
            <th style="width: 90px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
            <th style="width: 110px !important;"><?php echo TABLE_TOTAL_COST; ?></th>
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
        
        <?php $excelContent .= "\n".'Product'. "\t" . MENU_DEPARTMENT. "\t" . MENU_PRODUCT_GROUP_MANAGEMENT. "\t" . TABLE_SUB_OF_GROUP . "\t" . "Sub-Sub Catergory"; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="11" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrVendor=array();
        $arrLocation=array();
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $totalQty=0;
        $subTotalAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                purchase_orders.id,
                                'Bill' AS trans_type,
                                products.parent_id AS parent_id,
                                (SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=products.parent_id) AS parent_name,
                                purchase_order_details.product_id,
                                CONCAT_WS(' ',products.code,products.name) AS product_name,
                                purchase_orders.order_date AS order_date,
                                purchase_orders.po_code AS code,
                                (SELECT name FROM vendors WHERE id=purchase_orders.vendor_id) AS vendor_name,
                                (SELECT name FROM location_groups WHERE id=purchase_orders.location_group_id) AS location_group_name,
                                (purchase_order_details.qty + purchase_order_details.qty_free) AS qty,
                                purchase_order_details.qty AS qty_price,
                                purchase_order_details.conversion AS conversion,
                                (SELECT name FROM uoms WHERE id = purchase_order_details.qty_uom_id) AS qty_uom_name,
                                IFNULL((SELECT name FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = products.price_uom_id AND is_small_uom = 1 AND is_active = 1 LIMIT 1)), (SELECT name FROM uoms WHERE id = products.price_uom_id)) AS small_uom_name,
                                purchase_order_details.unit_cost AS unit_price,
                                purchase_order_details.discount_amount,
                                purchase_order_details.total_cost AS total_price
                            FROM purchase_orders
                                INNER JOIN purchase_order_details ON purchase_orders.id=purchase_order_details.purchase_order_id
                                INNER JOIN products ON products.id = purchase_order_details.product_id
                            WHERE "
                                . str_replace(array('company_id','created_by'),array('purchase_orders.company_id','purchase_orders.created_by'),$condition)
                                . ($col[7] != ''?' AND purchase_order_details.product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND products.parent_id =' . $col[8] :'')
                                . ($col[9] != ''?' AND purchase_order_details.product_id =' . $col[9] :'')
                                . "
                            UNION ALL
                            SELECT
                                purchase_returns.id,
                                'Credit' AS trans_type,
                                products.parent_id AS parent_id,
                                (SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=products.parent_id) AS parent_name,
                                purchase_return_details.product_id,
                                CONCAT_WS(' ',products.code,products.name) AS product_name,
                                purchase_returns.order_date AS order_date,
                                purchase_returns.pr_code AS code,
                                (SELECT name FROM vendors WHERE id=purchase_returns.vendor_id) AS vendor_name,
                                (SELECT name FROM location_groups WHERE id=purchase_returns.location_group_id) AS location_group_name,
                                (purchase_return_details.qty) * -1 AS qty,
                                (purchase_return_details.qty) * -1 AS qty_price,
                                purchase_return_details.conversion AS conversion,
                                (SELECT name FROM uoms WHERE id = purchase_return_details.qty_uom_id) AS qty_uom_name,
                                IFNULL((SELECT name FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = products.price_uom_id AND is_small_uom = 1 AND is_active = 1 LIMIT 1)), (SELECT name FROM uoms WHERE id = products.price_uom_id)) AS small_uom_name,
                                purchase_return_details.unit_price AS unit_price,
                                0 AS discount_amount,
                                (purchase_return_details.total_price * -1) AS total_price
                            FROM purchase_returns
                                INNER JOIN purchase_return_details ON purchase_returns.id=purchase_return_details.purchase_return_id
                                INNER JOIN products ON products.id = purchase_return_details.product_id
                            WHERE "
                                . str_replace(array('status=2','status=3','company_id','created_by'),array('FALSE','status=2','purchase_returns.company_id','purchase_returns.created_by'),$condition)
                                . ($col[7] != ''?' AND purchase_return_details.product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND products.parent_id=' . $col[8] :'')
                                . ($col[9] != ''?' AND purchase_return_details.product_id=' . $col[9] :'')
                                . "
                            ORDER BY parent_name,product_name,order_date");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrVendor[] = $data['vendor_name'];
            $arrLocation[] = $data['location_group_name'];
        ?>

            <?php if($data['product_id']!=$oldProductId){ ?>
            <?php if($oldProductName!=''){ ?>
            <?php $excelContent .= "\n".'Total '.$oldProductName."\t\t\t\t\t\t".$subTotalQty."\t".$oldUomName."\t\t\t".number_format($subTotalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldProductName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td><?php echo $oldUomName; ?></td><td></td><td></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php
            }
            $index=1;
            $subTotalQty=0;
            $subTotalAmount=0;
            ?>
            <?php $excelContent .= "\n".$data['product_name']."\t".getProductRelation($data['product_id'], 4)."\t".getProductRelation($data['product_id'], 3)."\t".getProductRelation($data['product_id'], 2)."\t".getProductRelation($data['product_id'], 1); ?>
            <tr><td class="first" colspan="11" style="font-weight: bold;"><?php echo $data['product_name']; ?></td></tr>
            <?php } ?>

            <?php $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['order_date']."\t".$data['code']."\t".$data['vendor_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".number_format($data['unit_price'],2)."\t".number_format($data['discount_amount'], 2)."\t".number_format(($data['qty_price'] * $data['unit_price']) - $data['discount_amount'], 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td><?php echo $data['order_date']; ?></td>
                <td><a href="" class="btnPrintPurchaseByItem" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                <td><?php echo $data['vendor_name']; ?></td>
                <td><?php echo $data['location_group_name']; ?></td>
                <td><?php echo $data['qty']; ?></td>
                <td><?php echo $data['qty_uom_name']; ?></td>
                <td><?php echo number_format($data['unit_price'],2); ?></td>
                <td><?php echo number_format($data['discount_amount'], 2); ?></td>
                <td><?php echo number_format(($data['qty_price'] * $data['unit_price']) - $data['discount_amount'], 2); ?></td>
            </tr>
            
            <?php
            $subTotalQty += $data['qty'] * $data['conversion'];
            $totalQty    += $data['qty'] * $data['conversion'];
            $subTotalAmount   += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
            $totalAmount      += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
            $grandTotalAmount += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
            $oldProductId   = $data['product_id'];
            $oldProductName = $data['product_name'];
            $oldUomName     = $data['small_uom_name'];
            ?>

        <?php } ?>

        <?php if(mysql_num_rows($query)){ ?>
        <?php $excelContent .= "\n".'Total '.$oldProductName."\t\t\t\t\t\t".$subTotalQty."\t".$oldUomName."\t\t\t".number_format($subTotalAmount,2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldProductName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td><?php echo $oldUomName; ?></td><td></td><td></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t\t\t\t".number_format($totalAmount,2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Total Product</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
        <?php } ?>

        <?php if($col[7] == '' && $col[8] == '' && $col[9] == ''){ ?>

            <?php $excelContent .= "\n\n".'Service'; ?>
            <tr><td colspan="11">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="11" style="font-size: 14px;">Service</td></tr>
            <?php
            $index=1;
            $oldServiceId='';
            $oldServiceName='';
            $subTotalQty=0;
            $totalQty=0;
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
                                    purchase_order_services.discount_amount,
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
                                    0 AS discount_amount,
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
                <?php $excelContent .= "\n".'Total '.$oldServiceName."\t\t\t\t\t\t".$subTotalQty."\t\t\t\t".number_format($subTotalAmount,2); ?>
                <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldServiceName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td colspan="3"></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
                <?php
                }
                $index=1;
                $subTotalQty=0;
                $subTotalAmount=0;
                ?>
                <?php $excelContent .= "\n".$data['service_name']; ?>
                <tr><td class="first" colspan="10" style="font-weight: bold;"><?php echo $data['service_name']; ?></td></tr>
                <?php } ?>

                <?php $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['order_date']."\t".$data['code']."\t".$data['vendor_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".number_format($data['unit_price'],2)."\t".number_format($data['discount_amount'],2)."\t".number_format(($data['qty'] * $data['unit_price']) - $data['discount_amount'],2); ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $data['trans_type']; ?></td>
                    <td><?php echo $data['order_date']; ?></td>
                    <td><a href="" class="btnPrintPurchaseByItem" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                    <td><?php echo $data['vendor_name']; ?></td>
                    <td><?php echo $data['location_group_name']; ?></td>
                    <td><?php echo $data['qty']; ?></td>
                    <td><?php echo $data['qty_uom_name']; ?></td>
                    <td><?php echo number_format($data['unit_price'],2); ?></td>
                    <td><?php echo number_format($data['discount_amount'],2); ?></td>
                    <td><?php echo number_format(($data['qty'] * $data['unit_price']) - $data['discount_amount'],2); ?></td>
                </tr>
                
                <?php
                $subTotalQty += $data['qty'];
                $totalQty    += $data['qty'];
                $subTotalAmount   += ($data['qty'] * $data['unit_price']) - $data['discount_amount'];
                $totalAmount      += ($data['qty'] * $data['unit_price']) - $data['discount_amount'];
                $grandTotalAmount += ($data['qty'] * $data['unit_price']) - $data['discount_amount'];
                $oldServiceId   = $data['service_id'];
                $oldServiceName = $data['service_name'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".'Total '.$oldServiceName."\t\t\t\t\t\t".$subTotalQty."\t\t\t\t".number_format($subTotalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldServiceName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td colspan="3"></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t\t\t\t\t\t\t\t\t".number_format($totalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Total Service</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
            <?php } ?>

            <?php $excelContent .= "\n\n".'Miscellaneous'; ?>
            <tr><td colspan="10">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Miscellaneous</td></tr>
            <?php
            $index=1;
            $oldMiscName='';
            $subTotalQty=0;
            $totalQty=0;
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
                <?php $excelContent .= "\n".'Total '.$oldMiscName."\t\t\t\t\t\t".$subTotalQty."\t\t\t".number_format($subTotalAmount,2); ?>
                <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldMiscName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td colspan="2"></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
                <?php
                }
                $index=1;
                $subTotalQty=0;
                $subTotalAmount=0;
                ?>
                <?php $excelContent .= "\n".$data['description']; ?>
                <tr><td class="first" colspan="10" style="font-weight: bold;"><?php echo $data['description']; ?></td></tr>
                <?php } ?>

                <?php $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['order_date']."\t".$data['code']."\t".$data['vendor_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".$data['unit_price']."\t".$data['total_price']; ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $data['trans_type']; ?></td>
                    <td><?php echo $data['order_date']; ?></td>
                    <td><a href="" class="btnPrintPurchaseByItem" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                    <td><?php echo $data['vendor_name']; ?></td>
                    <td><?php echo $data['location_group_name']; ?></td>
                    <td><?php echo $data['qty']; ?></td>
                    <td><?php echo $data['qty_uom_name']; ?></td>
                    <td><?php echo number_format($data['unit_price'],2); ?></td>
                    <td><?php echo number_format($data['total_price'],2); ?></td>
                </tr>
                
                <?php
                $subTotalQty+=$data['qty'];
                $totalQty+=$data['qty'];
                $subTotalAmount+=$data['total_price'];
                $totalAmount+=$data['total_price'];
                $grandTotalAmount+=$data['total_price'];
                $oldMiscName=$data['description'];
                ?>

            <?php } ?>

            <?php if(mysql_num_rows($query)){ ?>
            <?php $excelContent .= "\n".'Total '.$oldMiscName."\t\t\t\t\t\t".$subTotalQty."\t\t\t".number_format($subTotalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="6">Total <?php echo $oldMiscName; ?></td><td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td><td colspan="2"></td><td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td></tr>
            <?php $excelContent .= "\n".'Total Miscellaneous'."\t\t\t\t\t\t\t\t\t".number_format($totalAmount,2); ?>
            <tr style="font-weight: bold;"><td class="first" colspan="9" style="font-size: 14px;">Total Miscellaneous</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td></tr>
            <?php } ?>

        <?php } ?>

        <?php $excelContent .= "\n\n".'Grand Total Amount'."\t\t\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrVendor))."\t".sizeof(array_unique($arrLocation))."\t\t\t\t".number_format($grandTotalAmount,2); ?>
        <tr><td colspan="10">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="4" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrVendor)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
            <td colspan="3"></td>
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