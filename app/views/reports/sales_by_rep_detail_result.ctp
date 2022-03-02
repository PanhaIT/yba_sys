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
$filename="public/report/sales_by_rep_detail_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");

        $(".btnPrintSalesByRep").click(function(event){
            event.preventDefault();
            if($(this).attr("trans_type")=="Invoice"){
                var url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else{
                var url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_rep_detail_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_SALES_ORDER_BY_REP . '</b><br /><br />';
    $excelContent .= MENU_REPORT_SALES_ORDER_BY_REP."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_DATE."\t".TABLE_INVOICE_CODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_CUSTOMER."\t".TABLE_LOCATION_GROUP."\t".TABLE_QTY."\t".TABLE_UOM."\t".TABLE_PRICE."\t".GENERAL_AMOUNT;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
            <th><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_CUSTOMER; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_PRICE; ?></th>
            <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $condition = 'is_pos=0';
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
            $condition .= 'status!=0';
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
        if ($col[11] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'created_by=' . $col[11];
        }
        if ($_POST['cgroup_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= $_POST['cgroup_id'] . ' IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_cgroups.customer_id=tbl.customer_id)';
        }
        if ($_POST['customer_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.customer_id=' . $_POST['customer_id'];
        }
    
        // declare
        $grandTotalAmount=0;
        $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="11" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrProduct=array();
        $arrCustomer=array();
        $arrLocation=array();
        $oldCustomerGroupId='';
        $oldCustomerGroupName='';
        $subTotalAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                sales_orders.id,
                                'Invoice' AS trans_type,
                                (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                order_date,
                                so_code AS code,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                ". ($col[10] != ''?($col[10] != '1'?'qty':'qty_free'):'qty')." AS qty,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_price,
                                total_price
                            FROM sales_orders 
                                INNER JOIN sales_order_details ON sales_orders.id=sales_order_details.sales_order_id
                            WHERE "
                                . str_replace('tbl.','sales_orders.',$condition)
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . "
                            UNION ALL
                            SELECT
                                credit_memos.id,
                                'Credit Memo' AS trans_type,
                                (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                order_date,
                                cm_code AS code,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                ". ($col[10] != ''?($col[10] != '1'?'(qty*-1)':'(qty_free * -1)'):'((qty + qty_free) * -1)')." AS qty,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_price,
                                total_price
                            FROM credit_memos
                                INNER JOIN credit_memo_details ON credit_memos.id=credit_memo_details.credit_memo_id
                            WHERE "
                                . str_replace(array('is_pos=0 AND','tbl.'),array('','credit_memos.'),$condition)
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . "
                            ORDER BY cgroup_name, order_date");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrProduct[] = $data['product_name'];
            $arrCustomer[] = $data['customer_name'];
            $arrLocation[] = $data['location_group_name'];
            if($data['cgroup_id']!=$oldCustomerGroupId){ 
                if($oldCustomerGroupName!=''){ 
                    $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
            </tr>
            <?php
                }
                $index=1;
                $subTotalAmount=0;
                $excelContent .= "\n".$data['cgroup_name']; ?>
            <tr>
                <td class="first" colspan="11" style="font-weight: bold;"><?php echo $data['cgroup_name']; ?></td>
            </tr>
            <?php 
            }
            $excelContent .= "\n".$index."\t".$data['trans_type']."\t".dateShort($data['order_date'])."\t".$data['code']."\t".$data['product_name']."\t".$data['customer_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".$data['unit_price']."\t".($data['qty'] * $data['unit_price']); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><a href="" class="btnPrintSalesByRep" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                <td><?php echo $data['product_name']; ?></td>
                <td><?php echo $data['customer_name']; ?></td>
                <td><?php echo $data['location_group_name']; ?></td>
                <td><?php echo number_format($data['qty'], 2); ?></td>
                <td><?php echo $data['qty_uom_name']; ?></td>
                <td><?php echo number_format($data['unit_price'], 2); ?></td>
                <td><?php echo number_format(($data['qty'] * $data['unit_price']), 2); ?></td>
            </tr>
            
            <?php
            $subTotalAmount   += ($data['qty'] * $data['unit_price']);
            $totalAmount      += ($data['qty'] * $data['unit_price']);
            $grandTotalAmount += ($data['qty'] * $data['unit_price']);
            $oldCustomerGroupId   = $data['cgroup_id'];
            $oldCustomerGroupName = $data['cgroup_name'];
        } 
        if(mysql_num_rows($query)){ 
            $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td></tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t\t\t\t\t".number_format($totalAmount, 2); ?>
        <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Total Product</td><td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td></tr>
        <?php } ?>

        <?php if($col[7] == '' && $col[8] == '' && $col[9] == ''){ ?>

            <?php $excelContent .= "\n\n".'Service'; ?>
            <tr><td colspan="11">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="11" style="font-size: 14px;">Service</td></tr>
            <?php
            $index=1;
            $oldCustomerGroupId='';
            $oldCustomerGroupName='';
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    sales_orders.id,
                                    'Invoice' AS trans_type,
                                    (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                    (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                    order_date,
                                    so_code AS code,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    ". ($col[10] != ''?($col[10] != '1'?'qty':'qty_free'):'qty')." AS qty,
                                    NULL AS qty_uom_name,
                                    unit_price,
                                    total_price
                                FROM sales_orders
                                    INNER JOIN sales_order_services ON sales_orders.id=sales_order_services.sales_order_id
                                WHERE "
                                    . str_replace('tbl.','sales_orders.',$condition)
                                    . "
                                UNION ALL
                                SELECT
                                    credit_memos.id,
                                    'Credit Memo' AS trans_type,
                                    (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                    (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                    order_date,
                                    cm_code AS code,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    ". ($col[10] != ''?($col[10] != '1'?'(qty*-1)':'(qty_free * -1)'):'((qty + qty_free) * -1)')." AS qty,
                                    NULL AS qty_uom_name,
                                    unit_price,
                                    total_price
                                FROM credit_memos
                                    INNER JOIN credit_memo_services ON credit_memos.id=credit_memo_services.credit_memo_id
                                WHERE "
                                    . str_replace(array('is_pos=0 AND','tbl.'),array('','credit_memos.'),$condition)
                                    . "
                                ORDER BY cgroup_name,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[] = $data['code'];
                $arrProduct[] = $data['service_name'];
                $arrCustomer[] = $data['customer_name'];
                $arrLocation[] = $data['location_group_name'];
                if($data['cgroup_id']!=$oldCustomerGroupId){ 
                    if($oldCustomerGroupName!=''){ 
                        $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
                <tr style="font-weight: bold;">
                    <td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
                </tr>
                <?php
                    }
                    $index=1;
                    $subTotalAmount=0;
                    $excelContent .= "\n".$data['cgroup_name']; ?>
                <tr>
                    <td class="first" colspan="11" style="font-weight: bold;"><?php echo $data['cgroup_name']; ?></td>
                </tr>
                <?php 
                } 
                $excelContent .= "\n".$index."\t".$data['trans_type']."\t".dateShort($data['order_date'])."\t".$data['code']."\t".$data['service_name']."\t".$data['customer_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".$data['unit_price']."\t".number_format(($data['qty'] * $data['unit_price']), 2); ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $data['trans_type']; ?></td>
                    <td><?php echo dateShort($data['order_date']); ?></td>
                    <td><a href="" class="btnPrintSalesByRep" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                    <td><?php echo $data['service_name']; ?></td>
                    <td><?php echo $data['customer_name']; ?></td>
                    <td><?php echo $data['location_group_name']; ?></td>
                    <td><?php echo number_format($data['qty'], 2); ?></td>
                    <td><?php echo $data['qty_uom_name']; ?></td>
                    <td><?php echo number_format($data['unit_price'], 2); ?></td>
                    <td><?php echo number_format(($data['qty'] * $data['unit_price']), 2); ?></td>
                </tr>
            <?php
                $subTotalAmount   += ($data['qty'] * $data['unit_price']);
                $totalAmount      += ($data['qty'] * $data['unit_price']);
                $grandTotalAmount += ($data['qty'] * $data['unit_price']);
                $oldCustomerGroupId   = $data['cgroup_id'];
                $oldCustomerGroupName = $data['cgroup_name'];
            }
            if(mysql_num_rows($query)){ 
                $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
            </tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t\t\t\t\t\t\t\t\t".number_format($totalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="10" style="font-size: 14px;">Total Service</td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
            </tr>
            <?php 
            } 
            $excelContent .= "\n\n".'Miscellaneous'; ?>
            <tr><td colspan="11">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="11" style="font-size: 14px;">Miscellaneous</td></tr>
            <?php
            $index=1;
            $oldCustomerGroupId='';
            $oldCustomerGroupName='';
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    sales_orders.id,
                                    'Invoice' AS trans_type,
                                    (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                    (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=sales_orders.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                    order_date,
                                    so_code AS code,
                                    description,
                                    (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    ". ($col[10] != ''?($col[10] != '1'?'qty':'qty_free'):'qty')." AS qty,
                                    (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                    unit_price,
                                    total_price
                                FROM sales_orders
                                    INNER JOIN sales_order_miscs ON sales_orders.id=sales_order_miscs.sales_order_id
                                WHERE "
                                    . str_replace('tbl.','sales_orders.',$condition)
                                    . "
                                UNION ALL
                                SELECT
                                    credit_memos.id,
                                    'Credit Memo' AS trans_type,
                                    (SELECT id FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_id,
                                    (SELECT name FROM cgroups WHERE id=(SELECT cgroup_id FROM customer_cgroups WHERE customer_id=credit_memos.customer_id AND cgroup_id IN (SELECT id FROM cgroups WHERE is_active=1) LIMIT 1)) AS cgroup_name,
                                    order_date,
                                    cm_code AS code,
                                    description,
                                    (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                    (SELECT name FROM location_groups WHERE id=location_group_id) AS location_group_name,
                                    ". ($col[10] != ''?($col[10] != '1'?'(qty*-1)':'(qty_free * -1)'):'((qty + qty_free) * -1)')." AS qty,
                                    (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                    unit_price,
                                    total_price
                                FROM credit_memos
                                    INNER JOIN credit_memo_miscs ON credit_memos.id=credit_memo_miscs.credit_memo_id
                                WHERE "
                                    . str_replace(array('is_pos=0 AND','tbl.'),array('','credit_memos.'),$condition)
                                    . "
                                ORDER BY cgroup_name,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[] = $data['code'];
                $arrProduct[] = $data['description'];
                $arrCustomer[] = $data['customer_name'];
                $arrLocation[] = $data['location_group_name'];
            ?>

                <?php if($data['cgroup_id']!=$oldCustomerGroupId){ ?>
                <?php if($oldCustomerGroupName!=''){ ?>
                <?php $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
                <tr style="font-weight: bold;"><td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td><td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td></tr>
                <?php
                }
                $index=1;
                $subTotalAmount=0;
                ?>
                <?php $excelContent .= "\n".$data['cgroup_name']; ?>
                <tr><td class="first" colspan="11" style="font-weight: bold;"><?php echo $data['cgroup_name']; ?></td></tr>
                <?php } ?>

                <?php $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['order_date']."\t".$data['code']."\t".$data['description']."\t".$data['customer_name']."\t".$data['location_group_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".$data['unit_price']."\t".number_format(($data['qty'] * $data['unit_price']), 2); ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td><?php echo $data['trans_type']; ?></td>
                    <td><?php echo $data['order_date']; ?></td>
                    <td><a href="" class="btnPrintSalesByRep" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                    <td><?php echo $data['description']; ?></td>
                    <td><?php echo $data['customer_name']; ?></td>
                    <td><?php echo $data['location_group_name']; ?></td>
                    <td><?php echo number_format($data['qty'], 2); ?></td>
                    <td><?php echo $data['qty_uom_name']; ?></td>
                    <td><?php echo number_format($data['unit_price'], 2); ?></td>
                    <td><?php echo number_format(($data['qty'] * $data['unit_price']), 2); ?></td>
                </tr>
                
                <?php
                $subTotalAmount   += ($data['qty'] * $data['unit_price']);
                $totalAmount      += ($data['qty'] * $data['unit_price']);
                $grandTotalAmount += ($data['qty'] * $data['unit_price']);
                $oldCustomerGroupId   = $data['cgroup_id'];
                $oldCustomerGroupName = $data['cgroup_name'];
            }
            if(mysql_num_rows($query)){ 
                $excelContent .= "\n".'Total '.$oldCustomerGroupName."\t\t\t\t\t\t\t\t\t\t".number_format($subTotalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="10">Total <?php echo $oldCustomerGroupName; ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
            </tr>
            <?php $excelContent .= "\n".'Total Miscellaneous'."\t\t\t\t\t\t\t\t\t\t".number_format($totalAmount, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="10" style="font-size: 14px;">Total Miscellaneous</td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
            </tr>
        <?php 
            } 
        }
        $excelContent .= "\n\n".'Grand Total Amount'."\t\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrProduct))."\t".sizeof(array_unique($arrCustomer))."\t".sizeof(array_unique($arrLocation))."\t\t\t\t".number_format($grandTotalAmount, 2); ?>
        <tr><td colspan="11">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="3" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrProduct)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCustomer)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
            <td colspan="3"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmount, 2); ?></td>
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