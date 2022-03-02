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
$filename="public/report/sales_by_item_last_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(12)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(14)").css("text-align", "right");

        $(".btnPrintSalesByItemReport").click(function(event){
            event.preventDefault();
            var url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
            if($(this).attr("trans_type")=="Invoice"){
                url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("trans_type")=="POS"){
                url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_item_last_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_ITEM_BY_LAST_SOLD_DATE . '</b><br />';
    $excelContent .= MENU_REPORT_ITEM_BY_LAST_SOLD_DATE."\n";
    if($_POST['branch_id']!='') {
        $sqlBrn = mysql_query("SELECT name FROM branches WHERE id = ".$_POST['branch_id']);
        $rowBrn = mysql_fetch_array($sqlBrn);
        $msg .= '<br/>'.MENU_BRANCH.': '.$rowBrn['name'];
        $excelContent .= "\n".MENU_BRANCH.': '.$rowBrn['name'];
    }
    $mainUomLabel = "";
    $smallUomVal  = "";
    $uomId = "";
    if($_POST['product_id']!='') {
        $sqlPro = mysql_query("SELECT CONCAT_WS(' - ',products.code,products.name), products.price_uom_id, products.small_val_uom, uoms.abbr FROM products INNER JOIN uoms ON uoms.id = products.price_uom_id WHERE products.id = ".$_POST['product_id']);
        $rowPro = mysql_fetch_array($sqlPro);
        $msg .= '<br/>'.TABLE_PRODUCT.': '.$rowPro[0];
        $excelContent .= "\n".TABLE_PRODUCT.': '.$rowPro[0];
        $uomId = $rowPro['price_uom_id'];
        $mainUomLabel = $rowPro['abbr'];
        $smallUomVal  = $rowPro['small_val_uom'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_BRANCH."\t".TABLE_DATE."\t".TABLE_CODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_INVOICE_CODE."\t".TABLE_CUSTOMER."\t".TABLE_LOCATION_GROUP."\t".TABLE_QTY."\t".TABLE_UOM."\t".TABLE_PRICE."\t".GENERAL_DISCOUNT."\t".GENERAL_AMOUNT;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 60px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 170px !important;"><?php echo TABLE_BRANCH; ?></th>
            <th style="width: 70px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 70px !important;"><?php echo TABLE_CODE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
            <th><?php echo TABLE_CUSTOMER; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 70px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 90px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 90px !important;"><?php echo TABLE_PRICE; ?></th>
            <th style="width: 90px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
            <th style="width: 110px !important;"><?php echo GENERAL_AMOUNT; ?></th>
        </tr>
        <?php
        // general condition
        $condition = '';
        if ($_POST['branch_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales.branch_id=' . $_POST['branch_id'];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if ($_POST['product_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'product_id=' . $_POST['product_id'];
        }
        if ($_POST['customer_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales.customer_id=' . $_POST['customer_id'];
        }
        // declare
        $grandTotalAmount=0;
        $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="13" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrCustomer=array();
        $arrLocation=array();
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $totalQty=0;
        $subTotalAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                sales.id,
                                IF(is_pos=1,'POS','Invoice') AS trans_type,
                                sales_order_details.product_id,
                                products.code AS product_code,
                                products.name AS product_name,
                                branches.name AS branch_name,
                                sales.order_date,
                                sales.so_code AS code,
                                (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                (SELECT name FROM location_groups WHERE id = location_group_id) AS location_name,
                                sales_order_details.qty AS qty_price,
                                (sales_order_details.qty + sales_order_details.qty_free) AS qty,
                                sales_order_details.conversion,
                                sales_order_details.qty_uom_id AS product_uom_id,
                                uoms.name AS qty_uom_name,
                                sales_order_details.unit_price,
                                sales_order_details.discount_amount,
                                sales_order_details.total_price
                            FROM sales_orders AS sales
                                INNER JOIN branches ON branches.id = sales.branch_id
                                INNER JOIN sales_order_details ON sales.id=sales_order_details.sales_order_id
                                INNER JOIN products ON products.id = sales_order_details.product_id
                                INNER JOIN uoms ON uoms.id = sales_order_details.qty_uom_id
                            WHERE "
                                . $condition
                                . "
                            UNION ALL
                            SELECT
                                sales.id,
                                'Credit Memo' AS trans_type,
                                credit_memo_details.product_id,
                                products.code AS product_code,
                                products.name AS product_name,
                                branches.name AS branch_name,
                                sales.order_date,
                                sales.cm_code AS code,
                                (SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=customer_id) AS customer_name,
                                (SELECT name FROM location_groups WHERE id = location_group_id) AS location_name,
                                credit_memo_details.qty*-1 AS qty_price,
                                ((credit_memo_details.qty + credit_memo_details.qty_free) * -1) AS qty,
                                credit_memo_details.conversion,
                                credit_memo_details.qty_uom_id AS product_uom_id,
                                uoms.name AS qty_uom_name,
                                credit_memo_details.unit_price,
                                (credit_memo_details.discount_amount*-1) AS discount_amount,
                                credit_memo_details.total_price
                            FROM credit_memos AS sales
                                INNER JOIN branches ON branches.id = sales.branch_id
                                INNER JOIN credit_memo_details ON sales.id=credit_memo_details.credit_memo_id
                                INNER JOIN products ON products.id = credit_memo_details.product_id
                                INNER JOIN uoms ON uoms.id = credit_memo_details.qty_uom_id
                            WHERE "
                                . $condition
                                . "
                            ORDER BY order_date DESC");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrCustomer[] = $data['customer_name'];
            $arrLocation[] = $data['location_name'];
            $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$data['product_id']." AND uom_id = ".$data['product_uom_id']);
            if(mysql_num_rows($sqlSku)){
                $rowSku = mysql_fetch_array($sqlSku);
                $productCode = $rowSku[0];
            } else {
                $sqlSku = mysql_query("SELECT barcode FROM products WHERE id = ".$data['product_id']);
                $rowSku = mysql_fetch_array($sqlSku);
                $productCode = $rowSku[0];
            }
            
            $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['branch_name']."\t".dateShort($data['order_date'])."\t'".$productCode."\t".$data['product_name']."\t".$data['code']."\t".$data['customer_name']."\t".$data['location_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".number_format($data['unit_price'], 2)."\t".number_format($data['discount_amount'], 2)."\t".number_format(($data['qty_price'] * $data['unit_price']) - $data['discount_amount'], 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td><?php echo $data['branch_name']; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $productCode; ?></td>
                <td><?php echo $data['product_name']; ?></td>
                <td><a href="" class="btnPrintSalesByItemReport" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                <td><?php echo $data['customer_name']; ?></td>
                <td><?php echo $data['location_name']; ?></td>
                <td><?php echo number_format($data['qty'], 2); ?></td>
                <td><?php echo $data['qty_uom_name']; ?></td>
                <td><?php echo number_format($data['unit_price'], 2); ?></td>
                <td><?php echo number_format($data['discount_amount'], 2); ?></td>
                <td><?php echo number_format(($data['qty_price'] * $data['unit_price']) - $data['discount_amount'], 2); ?></td>
            </tr>
            <?php
            // Total Qty
            $totalQty    += ($data['qty'] * $data['conversion']);
            $totalAmount += ($data['qty_price'] * $data['unit_price']) - $data['discount_amount'];
        }
        $excelContent .= "\n\n".'Total'."\t\t\t\t\t\t\t\t\t\t".number_format($totalQty, 0)."\t\t\t\t\t".number_format($grandTotalAmount, 2); ?>
        <tr><td colspan="11">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="9" style="font-size: 14px;">Total</td>
            <td colspan="2" style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo displayQtyByUoM($totalQty, $uomId, $smallUomVal, $mainUomLabel); ?></td>
            <td colspan="2"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount, 2); ?></td>
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