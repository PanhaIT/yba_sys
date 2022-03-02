<?php
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE is_active = 1 LIMIT 1)");
$rowSym = mysql_fetch_array($sqlSym);
$rnd       = rand();
$tblName   = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/open_bill" . $user['User']['id'] . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnPrintOpenBill").click(function(event){
            event.preventDefault();
            var url = '';
            if($(this).attr("trans_type")=="Purchase Bill"){
                url = "<?php echo $this->base . '/purchase_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else{
                url = "<?php echo $this->base . '/purchase_returns'; ?>/printInvoice/"+$(this).attr("rel");
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
            window.open("<?php echo $this->webroot; ?>public/report/open_bill<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_OPEN_BILL . '</b><br /><br />';
    $excelContent .= MENU_REPORT_OPEN_BILL."\n\n";
    if($_POST['date']!='') {
        $msg .= TABLE_DATE.': '.$_POST['date'];
        $excelContent .= TABLE_DATE.': '.$_POST['date']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_DATE."\t".TABLE_CODE."\t".TABLE_DUE_DATE."\t".GENERAL_AGING."\t".TABLE_OPEN_BALANCE." (".$rowSym[0].")";
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_CODE; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_DUE_DATE; ?></th>
            <th style="width: 80px !important;"><?php echo GENERAL_AGING; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_OPEN_BALANCE; ?> (<?php echo $rowSym[0]; ?>)</th>
        </tr>
        <?php
        $date = dateConvert(str_replace("|||", "/", $data[0]));
        // general condition
        $post = implode(',', $_POST);
        $col  = explode(",", $post);
        $condition = 'status > 0';
        if ($data[0] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . $date . '" >= DATE(order_date)';
        }
        if ($data[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'vendor_id IN (SELECT vendor_id FROM vendor_vgroups WHERE vgroup_id=' . $data[1] . ')';
        }
        if ($data[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'vendor_id=' . $data[2];
        }
        $index = 1;
        $oldVendorId     = '';
        $oldVendorName   = '';
        $subTotalBalance = 0;
        $totalBalance    = 0;
        $grandTotalBalance = 0;
        $query=mysql_query("SELECT
                                'Purchase Bill' AS trans_type,
                                vendors.id AS vendor_id,
                                purchase_orders.id AS id,
                                purchase_orders.order_date AS order_date,
                                purchase_orders.po_code AS purchase_code,
                                CONCAT_WS(' - ', vendors.vendor_code, vendors.name) AS vendor_name,
                                purchase_orders.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=pvs.exchange_rate_id),0)) FROM pvs WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE pv_id=pvs.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND purchase_order_id=purchase_orders.id AND is_void=0),0) + IFNULL((SELECT SUM(total_cost) FROM invoice_pbc_with_pbs WHERE status=1 AND purchase_order_id=purchase_orders.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM purchase_returns WHERE id=invoice_pbc_with_pbs.purchase_return_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) AS balance,
                                IF(purchase_orders.balance > 0 AND DATEDIFF('".$date."',order_date)>0, DATEDIFF('".$date."',order_date), '-') AS aging
                            FROM purchase_orders
                                LEFT JOIN vendors ON vendors.id = purchase_orders.vendor_id
                            WHERE "
                                . $condition
                                . " AND purchase_orders.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=pvs.exchange_rate_id),0)) FROM pvs WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE pv_id=pvs.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND purchase_order_id=purchase_orders.id AND is_void=0),0) + IFNULL((SELECT SUM(total_cost) FROM invoice_pbc_with_pbs WHERE status=1 AND purchase_order_id=purchase_orders.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM purchase_returns WHERE id=invoice_pbc_with_pbs.purchase_return_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) > 0.001"
                                . "
                            UNION
                            SELECT
                                'Purchase Return' AS trans_type,
                                vendors.id AS vendor_id,
                                purchase_returns.id AS id,
                                purchase_returns.order_date AS order_date,
                                purchase_returns.pr_code AS purchase_code,
                                CONCAT_WS(' - ', vendors.vendor_code, vendors.name) AS vendor_name,
                                (purchase_returns.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=purchase_return_receipts.exchange_rate_id),0)) FROM purchase_return_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=purchase_return_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND purchase_return_id=purchase_returns.id AND is_void=0),0) + IFNULL((SELECT SUM(total_cost) FROM invoice_pbc_with_pbs WHERE status=1 AND purchase_return_id=purchase_returns.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM purchase_orders WHERE id=invoice_pbc_with_pbs.purchase_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0))*-1 AS balance,
                                IF(purchase_returns.balance>0 AND DATEDIFF('".$date."',order_date)>0,DATEDIFF('".$date."',order_date),'-') AS aging
                            FROM purchase_returns
                                LEFT JOIN vendors ON vendors.id = purchase_returns.vendor_id
                            WHERE "
                                . $condition
                                . " AND purchase_returns.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=purchase_return_receipts.exchange_rate_id),0)) FROM purchase_return_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=purchase_return_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND purchase_return_id=purchase_returns.id AND is_void=0),0) + IFNULL((SELECT SUM(total_cost) FROM invoice_pbc_with_pbs WHERE status=1 AND purchase_return_id=purchase_returns.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM purchase_orders WHERE id=invoice_pbc_with_pbs.purchase_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) > 0.001"
                                . "
                            ORDER BY vendor_name,order_date") or die(mysql_error());
        while($data=mysql_fetch_array($query)){
            if($data['vendor_id'] != $oldVendorId){ 
                if($oldVendorName!=''){ 
                    $excelContent .= "\n".'Total '.$oldVendorName."\t\t\t\t\t\t".number_format($subTotalBalance, 2); ?>
            <tr>
                <td class="first" colspan="6">Total <?php echo $oldVendorName; ?></td>
                <td style="text-align: right;"><?php echo number_format($subTotalBalance, 2); ?></td>
            </tr>
            <?php
                    $index=1;
                    $subTotalBalance=0;
                }
                $excelContent .= "\n".$data['vendor_name']; ?>
            <tr><td class="first" colspan="7" style="font-weight: bold;"><?php echo $data['vendor_name']; ?></td></tr>
            <?php 
            } 
            $excelContent .= "\n".$index."\t".$data['trans_type']."\t".dateShort($data['order_date'])."\t".$data['purchase_code']."\t".dateShort($data['order_date'])."\t".$data['aging']."\t".number_format($data['balance'], 2); ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><a href="" class="btnPrintOpenBill" rel="<?php echo $data['id']; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['purchase_code']; ?></a></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td style="text-align: center;"><?php echo $data['aging']; ?></td>
                <td style="text-align: right;"><?php echo number_format($data['balance'], 2); ?></td>
            </tr>

            <?php
            $subTotalBalance   += $data['balance'];
            $totalBalance      += $data['balance'];
            $grandTotalBalance += $data['balance'];
            $oldVendorId   = $data['vendor_id'];
            $oldVendorName = $data['vendor_name'];
        } 
        if(mysql_num_rows($query)){ 
            $excelContent .= "\n".'Total '.$oldVendorName."\t\t\t\t\t\t".number_format($subTotalBalance, 2); ?>
        <tr>
            <td class="first" colspan="6">Total <?php echo $oldVendorName; ?></td>
            <td style="text-align: right;"><?php echo number_format($subTotalBalance, 2); ?></td>
        </tr>
        <?php 
        }  
        $excelContent .= "\n\n".'Grand Total Amount'."\t\t\t\t\t\t".number_format($grandTotalBalance, 2); ?>
        <tr><td colspan="7">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="6" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalBalance, 2); ?></td>
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