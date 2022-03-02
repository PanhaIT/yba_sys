<?php
include('includes/function.php');
$rnd = rand();
$tblName = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
/**
 * Export to Excel
 */
$filename="public/report/sales_by_item_" . $this->Session->id(session_id()) . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_item_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_SALES_ORDER_BY_ITEM . '</b><br />';
    $msg .= '<b style="font-size: 16px;">' . TABLE_VIEW_BY . ': '.TABLE_ITEM_SUMMARY.'</b><br /><br />';
    $excelContent .= MENU_REPORT_SALES_ORDER_BY_ITEM."\n";
    $excelContent .= TABLE_VIEW_BY.": ".TABLE_ITEM_SUMMARY."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    if($_POST['company_id']!='') {
        $sqlCom = mysql_query("SELECT name FROM companies WHERE id = ".$_POST['company_id']);
        $rowCom = mysql_fetch_array($sqlCom);
        $msg .= '<br/>'.MENU_COMPANY_MANAGEMENT.': '.$rowCom['name'];
        $excelContent .= "\n".MENU_COMPANY_MANAGEMENT.': '.$rowCom['name'];
    }
    if($_POST['branch_id']!='') {
        $sqlBrn = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch_id']);
        $rowBrn = mysql_fetch_array($sqlBrn);
        $msg .= '<br/>'.MENU_BRANCH.': '.$rowBrn['name'];
        $excelContent .= "\n".MENU_BRANCH.': '.$rowBrn['name'];
    }
    if($_POST['customer_id']!='') {
        $sqlCus = mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id = ".$_POST['customer_id']);
        $rowCus = mysql_fetch_array($sqlCus);
        $msg .= '<br/>'.MENU_CUSTOMER.': '.$rowCus[0];
        $excelContent .= "\n".MENU_CUSTOMER.': '.$rowCus[0];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_CODE."\t".TABLE_PRODUCT. "\t" . MENU_DEPARTMENT. "\t" . MENU_PRODUCT_GROUP_MANAGEMENT. "\t" . TABLE_SUB_OF_GROUP . "\t" . "Sub-Sub Catergory"."\t".TABLE_INVOICE."\t".TABLE_CUSTOMER."\t".TABLE_LOCATION_GROUP."\t".TABLE_QTY."\t".TABLE_UOM."\t".GENERAL_DISCOUNT."\t".GENERAL_AMOUNT."\tAmount Credit\tAmount Cash";
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_CODE; ?></th>
            <th><?php echo TABLE_PRODUCT; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_INVOICE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_CUSTOMER; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 100px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
            <th style="width: 100px !important;"><?php echo GENERAL_AMOUNT; ?></th>
            <th style="width: 100px !important;"><?php echo 'Amount Credit'; ?></th>
            <th style="width: 100px !important;"><?php echo 'Amount Cash'; ?></th>
        </tr>
        <?php
        // general condition
        $vendorId = "";
        if(!empty($_POST['vendor_id'])){
            $vendorId = implode(",", $_POST['vendor_id']);
        }
        
        // Remove POST Vendor
        if(isset($_POST['vendor_id'])){
            unset($_POST['vendor_id']);
        }
        
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        if($_POST['withPos'] == ""){
            $condition = '';
        } else if ($_POST['withPos'] == "1") {
            $condition = 'is_pos=0';
        } else {
            $condition = 'is_pos=1';
        }
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
            if($col[10] == 1){
                $condition .= 'qty > 0';
            } else {
                $condition .= 'qty_free > 0';
            }
        }
        if ($col[11] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'created_by=' . $col[11];
        }
        if ($col[13] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'customer_id=' . $col[13];
        }

        // declare
        $grandTotalAmount=0;
        $grandTotalDiscount=0;
        $grandTotalAmountCredit=0;
        $grandTotalAmountCash=0;
        $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="9" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrCustomer=array();
        $arrLocation=array();
        $oldCustomer=array();
        $oldWarehouse=array();
        $oldParentId='';
        $oldParentName='';
        $oldProductId='';
        $oldProductName='';
        $subTotalInv=0;
        $subTotalQty=0;
        $subTotalParentQty=0;
        $subTotalDiscount=0;
        $subTotalAmount=0;
        $subTotalAmountCredit=0;
        $subTotalAmountCash=0;
        $subTotalParentAmount=0;
        $totalQty=0;
        $totalAmount=0;
        $totalDiscount=0;
        $totalAmountCredit=0;
        $totalAmountCash=0;
        $query=mysql_query("SELECT
                                IF(is_pos=1,'POS','Invoice') AS trans_type,
                                (SELECT parent_id FROM products WHERE id=product_id) AS parent_id,
                                IFNULL((SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=sales_order_details.product_id)),'No Parent') AS parent_name,
                                product_id,
                                (SELECT code FROM products WHERE id=product_id) AS product_code,
                                (SELECT name FROM products WHERE id=product_id) AS product_name,
                                 order_date,
                                so_code AS code,
                                customer_id,
                                location_group_id,
                                ". ($col[10] != ''?($col[10] == '1'?'qty':'0'):'qty')." AS qty_price,
                                ". ($col[10] != ''?($col[10] == '1'?'qty':'qty_free'):'(qty+qty_free)')." AS qty,
                                conversion,
                                (SELECT price_uom_id FROM products WHERE id = sales_order_details.product_id) AS qty_uom_id,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_price,
                                ". ($col[10] != ''?($col[10] == '1'?'discount_amount':'0'):'discount_amount')." AS discount_amount,
                                total_price
                            FROM sales_orders
                                INNER JOIN sales_order_details ON sales_orders.id=sales_order_details.sales_order_id
                            WHERE "
                                . $condition
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . ($vendorId != ''?' AND product_id IN (SELECT product_id FROM product_vendors WHERE vendor_id IN ('.$vendorId.'))':'')
                                . "
                            UNION ALL
                            SELECT
                                'Credit Memo' AS trans_type,
                                (SELECT parent_id FROM products WHERE id=product_id) AS parent_id,
                                IFNULL((SELECT CONCAT_WS(' ',code,'-',name) FROM products WHERE id=(SELECT parent_id FROM products WHERE id=credit_memo_details.product_id)),'No Parent') AS parent_name,
                                product_id,
                                (SELECT code FROM products WHERE id=product_id) AS product_code,
                                (SELECT name FROM products WHERE id=product_id) AS product_name,order_date,
                                cm_code AS code,
                                customer_id,
                                location_group_id,
                                ". ($col[10] != ''?($col[10] == '1'?'qty*-1':'0'):'qty*-1')." AS qty_price,
                                ". ($col[10] != ''?($col[10] == '1'?'(qty*-1)':'(qty_free * -1)'):'((qty + qty_free) * -1)')." AS qty,
                                conversion,
                                (SELECT price_uom_id FROM products WHERE id = credit_memo_details.product_id) AS qty_uom_id,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name,
                                unit_price,
                                ". ($col[10] != ''?($col[10] == '1'?'(discount_amount*-1)':'0'):'(discount_amount*-1)')." AS discount_amount,
                                total_price
                            FROM credit_memos
                                INNER JOIN credit_memo_details ON credit_memos.id=credit_memo_details.credit_memo_id
                            WHERE "
                                . str_replace(array('is_pos=0 AND', 'is_pos=1 AND'),array('', ''),$condition)
                                . ($col[7] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[7] . ')':'')
                                . ($col[8] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[8] :'')
                                . ($col[9] != ''?' AND product_id=' . $col[9] :'')
                                . ($vendorId != ''?' AND product_id IN (SELECT product_id FROM product_vendors WHERE vendor_id IN ('.$vendorId.'))':'')
                                . "
                            ORDER BY parent_name, product_name");
        while($data=mysql_fetch_array($query)){
            $arrCode[$data['code']] = 1;
            $arrCustomer[$data['customer_id']] = 1;
            $arrLocation[$data['location_group_id']] = 1;
            $small_label = "";
            // Smallest Uom
            $s = mysql_query("SELECT IFNULL((SELECT abbr FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = ".$data['qty_uom_id']." AND is_small_uom = 1 AND is_active = 1)), (SELECT abbr FROM uoms WHERE id = ".$data['qty_uom_id']."))");
            while($r=mysql_fetch_array($s)){
                $small_label = $r[0];
            }
            if($data['product_id']!=$oldProductId){
                if($oldProductName!=''){
                    $excelContent .= "\n".$index."\t'".$oldProductCode."\t".$oldProductName."\t".getProductRelation($oldProductId, 4)."\t".getProductRelation($oldProductId, 3)."\t".getProductRelation($oldProductId, 2)."\t".getProductRelation($oldProductId, 1)."\t".$subTotalInv."\t".sizeof($oldCustomer)."\t".sizeof($oldWarehouse)."\t".number_format($subTotalQty, 2)."\t".$oldUomName."\t".number_format($subTotalDiscount, 2)."\t".number_format($subTotalAmount, 2)."\t".number_format($subTotalAmountCredit, 2)."\t".number_format($subTotalAmountCash, 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $oldProductCode; ?></td>
                <td><?php echo $oldProductName; ?></td>
                <td style="text-align: center;"><?php echo $subTotalInv; ?></td>
                <td style="text-align: center;"><?php echo sizeof($oldCustomer); ?></td>
                <td style="text-align: center;"><?php echo sizeof($oldWarehouse); ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
                <td style="text-align: center;"><?php echo $oldUomName; ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalDiscount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmountCredit, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmountCash, 2); ?></td>
            </tr>
            <?php
                }
                $oldCustomer  = array();
                $oldWarehouse = array();
                $subTotalInv = 0;
                $subTotalQty = 0;
                $subTotalDiscount = 0;
                $subTotalAmount   = 0;
                $subTotalAmountCredit = 0;
                $subTotalAmountCash   = 0;
            }
            $oldCustomer[$data['customer_id']] = 1;
            $oldWarehouse[$data['location_group_id']] = 1;
            $subTotalInv += 1;
            // Total Qty
            $subTotalQty       += $data['qty']*$data['conversion'];
            $subTotalParentQty += $data['qty']*$data['conversion'];
            $totalQty          += $data['qty']*$data['conversion'];
            // Total Amount
            $subTotalDiscount     += $data['discount_amount'];
            $subTotalAmount       += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            $subTotalParentAmount += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            if($data['trans_type'] == 'POS'){
                $subTotalAmountCash   += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                $totalAmountCash      += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                $grandTotalAmountCash += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            } else {
                $subTotalAmountCredit += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                $totalAmountCredit    += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                $grandTotalAmountCredit += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            }
            $totalAmount      += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            $totalDiscount    += $data['discount_amount'];
            $grandTotalAmount += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
            $grandTotalDiscount += $data['discount_amount'];
            // Product Info
            $oldParentId    = $data['parent_id'];
            $oldParentName  = $data['parent_name'];
            $oldProductId   = $data['product_id'];
            $oldProductCode = $data['product_code'];
            $oldProductName = $data['product_name'];
            $oldUomName     = $small_label;
        } 
        if(mysql_num_rows($query)){ 
            $excelContent .= "\n".$index."\t".$oldProductCode."\t".$oldProductName."\t".getProductRelation($oldProductId, 4)."\t".getProductRelation($oldProductId, 3)."\t".getProductRelation($oldProductId, 2)."\t".getProductRelation($oldProductId, 1)."\t".$subTotalInv."\t".sizeof($oldCustomer)."\t".sizeof($oldWarehouse)."\t".number_format($subTotalQty, 2)."\t".$oldUomName."\t".number_format($subTotalDiscount, 2)."\t".number_format($subTotalAmount, 2)."\t".number_format($subTotalAmountCredit, 2)."\t".number_format($subTotalAmountCash, 2); ?>
        <tr>
            <td class="first"><?php echo $index++; ?></td>
            <td><?php echo $oldProductCode; ?></td>
            <td><?php echo $oldProductName; ?></td>
            <td style="text-align: center;"><?php echo $subTotalInv; ?></td>
            <td style="text-align: center;"><?php echo sizeof($oldCustomer); ?></td>
            <td style="text-align: center;"><?php echo sizeof($oldWarehouse); ?></td>
            <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
            <td style="text-align: center;"><?php echo $oldUomName; ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalDiscount, 2); ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalAmountCredit, 2); ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalAmountCash, 2); ?></td>
        </tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t\t\t\t\t".number_format($totalQty, 2)."\t\t".number_format($totalDiscount, 2)."\t".number_format($totalAmount, 2)."\t".number_format($totalAmountCredit, 2)."\t".number_format($totalAmountCash, 2); ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="6" style="font-size: 14px;">Total Product</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalQty, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalDiscount, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmountCredit, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmountCash, 2); ?></td>
        </tr>
        <?php } ?>

        <?php 
        if($col[7] == '' && $col[8] == '' && $col[9] == ''){ 
            $excelContent .= "\n\n".'Service'; ?>
            <tr><td colspan="9">&nbsp;</td></tr>
            <tr style="font-weight: bold;"><td class="first" colspan="9" style="font-size: 14px;">Service</td></tr>
            <?php
            $index=1;
            $oldServiceId='';
            $oldServiceName='';
            $subTotalQty=0;
            $totalQty=0;
            $subTotalAmount=0;
            $totalAmount=0;
            $query=mysql_query("SELECT
                                    IF(is_pos=1,'POS','Invoice') AS trans_type,
                                    service_id,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    order_date,
                                    so_code AS code,
                                    customer_id,
                                    location_group_id,
                                    ". ($col[10] != ''?($col[10] == '1'?'qty':'0'):'qty')." AS qty_price,
                                    ". ($col[10] != ''?($col[10] == '1'?'qty':'qty_free'):'(qty + qty_free)')." AS qty,
                                    NULL AS qty_uom_name,
                                    unit_price,
                                    ". ($col[10] != ''?($col[10] == '1'?'discount_amount':'0'):'discount_amount')." AS discount_amount,
                                    total_price
                                FROM sales_orders
                                    INNER JOIN sales_order_services ON sales_orders.id=sales_order_services.sales_order_id
                                WHERE " . $condition . "
                                UNION ALL
                                SELECT
                                    'Credit Memo' AS trans_type,
                                    service_id,
                                    (SELECT name FROM services WHERE id=service_id) AS service_name,
                                    order_date,
                                    cm_code AS code,
                                    customer_id,
                                    location_group_id,
                                    ". ($col[10] != ''?($col[10] == '1'?'qty*-1':'0'):'qty*-1')." AS qty_price,
                                    ". ($col[10] != ''?($col[10] == '1'?'(qty*-1)':'(qty_free * -1)'):'((qty + qty_free) * -1)')." AS qty,
                                    NULL AS qty_uom_name,
                                    unit_price,
                                    ". ($col[10] != ''?($col[10] == '1'?'(discount_amount*-1)':'0'):'(discount_amount*-1)')." AS discount_amount,
                                    total_price
                                FROM credit_memos
                                    INNER JOIN credit_memo_services ON credit_memos.id=credit_memo_services.credit_memo_id
                                WHERE " . str_replace(array('is_pos=0 AND', 'is_pos=1 AND'),array('', ''),$condition) . "
                                ORDER BY service_name,order_date");
            while($data=mysql_fetch_array($query)){
                $arrCode[$data['code']] = 1;
                $arrCustomer[$data['customer_id']] = 1;
                $arrLocation[$data['location_group_id']] = 1;
                if($data['service_id']!=$oldServiceId){ 
                    if($oldServiceName!=''){ 
                        $excelContent .= "\n".$index."\t\t".$oldServiceName."\t\t\t\t\t".$subTotalInv."\t".sizeof($oldCustomer)."\t".sizeof($oldWarehouse)."\t".number_format($subTotalQty, 2)."\t".$oldUomName."\t".number_format($subTotalDiscount, 2)."\t".number_format($subTotalAmount, 2)."\t".number_format($subTotalAmountCredit, 2)."\t".number_format($subTotalAmountCash, 2); ?>
                <tr>
                    <td class="first"><?php echo $index++; ?></td>
                    <td colspan="2"><?php echo $oldServiceName; ?></td>
                    <td style="text-align: center;"><?php echo $subTotalInv; ?></td>
                    <td style="text-align: center;"><?php echo sizeof($oldCustomer); ?></td>
                    <td style="text-align: center;"><?php echo sizeof($oldWarehouse); ?></td>
                    <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalDiscount, 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalAmountCredit, 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotalAmountCash, 2); ?></td>
                </tr>
                <?php
                    }
                    $oldCustomer  = array();
                    $oldWarehouse = array();
                    $subTotalInv = 0;
                    $subTotalQty = 0;
                    $subTotalDiscount = 0;
                    $subTotalAmount   = 0;
                    $subTotalAmountCredit = 0;
                    $subTotalAmountCash   = 0;
                }
                $oldCustomer[$data['customer_id']] = 1;
                $oldWarehouse[$data['location_group_id']] = 1;
                $subTotalInv += 1;
                // Total Qty
                $subTotalQty    += $data['qty'];
                $totalQty       += $data['qty'];
                $subTotalAmount   += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
                $subTotalDiscount += $data['discount_amount'];
                if($data['trans_type'] == 'POS'){
                    $subTotalAmountCash   += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                    $totalAmountCash      += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                    $grandTotalAmountCash += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                } else {
                    $subTotalAmountCredit += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                    $totalAmountCredit    += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                    $grandTotalAmountCredit += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                }
                $totalAmount      += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
                $grandTotalAmount += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
                $totalDiscount    += $data['discount_amount'];
                $grandTotalAmount += ($data['qty_price']*$data['unit_price']) - $data['discount_amount'];
                $grandTotalDiscount += $data['discount_amount'];
                $oldServiceId   = $data['service_id'];
                $oldServiceName = $data['service_name'];
            }
            if(mysql_num_rows($query)){ 
                $excelContent .= "\n".$index."\t\t".$oldServiceName."\t\t\t\t\t".$subTotalInv."\t".sizeof($oldCustomer)."\t".sizeof($oldWarehouse)."\t".number_format($subTotalQty, 2)."\t".$oldUomName."\t".number_format($subTotalDiscount, 2)."\t".number_format($subTotalAmount, 2)."\t".number_format($subTotalAmountCredit, 2)."\t".number_format($subTotalAmountCash, 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td colspan="2"><?php echo $oldServiceName; ?></td>
                <td style="text-align: center;"><?php echo $subTotalInv; ?></td>
                <td style="text-align: center;"><?php echo sizeof($oldCustomer); ?></td>
                <td style="text-align: center;"><?php echo sizeof($oldWarehouse); ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty, 2); ?></td>
                <td style="text-align: center;"></td>
                <td style="text-align: right;"><?php echo number_format($subTotalDiscount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmountCredit, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalAmountCash, 2); ?></td>
            </tr>
            <?php $excelContent .= "\n".'Total Service'."\t\t\t\t\t\t\t\t\t\t".number_format($totalQty, 2)."\t\t".number_format($totalDiscount, 2)."\t".number_format($totalAmount, 2)."\t".number_format($totalAmountCredit, 2)."\t".number_format($totalAmountCash, 2); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="6" style="font-size: 14px;">Total Service</td>
                <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalQty, 2); ?></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalDiscount, 2); ?></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmountCredit, 2); ?></td>
                <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmountCash, 2); ?></td>
            </tr>
        <?php 
            }
        } 
        $excelContent .= "\n\n".'Grand Total Amount'."\t\t\t\t\t\t\t".sizeof($arrCode)."\t".sizeof($arrCustomer)."\t".sizeof($arrLocation)."\t\t\t".number_format($grandTotalDiscount, 2)."\t".number_format($grandTotalAmount, 2)."\t".number_format($grandTotalAmountCredit, 2)."\t".number_format($grandTotalAmountCash, 2); ?>
        <tr><td colspan="9">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="3" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof($arrCode); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof($arrCustomer); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof($arrLocation); ?></td>
            <td colspan="2"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalDiscount, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmount, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmountCredit, 2); ?></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmountCash, 2); ?></td>
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