<?php
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE is_active = 1 LIMIT 1)");
$rowSym = mysql_fetch_array($sqlSym);
$rnd = rand();
$tblName   = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/open_invoice_summary" . $user['User']['id'] . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/open_invoice_summary<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_OPEN_INVOICE . ' (Summary)</b><br /><br />';
    $excelContent .= MENU_REPORT_OPEN_INVOICE."\n\n";
    if($_POST['date']!='') {
        $msg .= TABLE_DATE.': '.$_POST['date'];
        $excelContent .= TABLE_DATE.': '.$_POST['date']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_CODE."\t".TABLE_CUSTOMER_NAME."\t".TABLE_OPEN_BALANCE." (".$rowSym[0].")";
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_CODE; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_CUSTOMER_NAME; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_OPEN_BALANCE; ?> (<?php echo $rowSym[0]; ?>)</th>
        </tr>
        <?php
        $date = dateConvert(str_replace("|||", "/", $data[0]));
        // General condition
        $post = implode(',', $_POST);
        $col  = explode(",", $post);
        $condition = 'status > 0';
        if ($data[0] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . $date . '" >= DATE(order_date)';
        }
        if ($data[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'customer_id IN (SELECT customer_id FROM customer_cgroups WHERE customer_id=' . $data[1] . ')';
        }
        if ($data[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'customer_id=' . $data[2];
        }
        $grandTotalBalance = 0;
        $query=mysql_query("SELECT
                                sales_orders.id AS id,
                                customers.id AS customer_id,
                                customers.customer_code,
                                customers.name AS customer_name,
                                sales_orders.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=sales_order_receipts.exchange_rate_id),0)) FROM sales_order_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE sales_order_receipt_id=sales_order_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND sales_order_id=sales_orders.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND sales_order_id=sales_orders.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM credit_memos WHERE id=credit_memo_with_sales.credit_memo_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) AS balance
                            FROM sales_orders
                                LEFT JOIN customers ON customers.id = sales_orders.customer_id
                                LEFT JOIN payment_terms ON payment_terms.id = sales_orders.payment_term_id
                            WHERE "
                                . str_replace('created_by','sales_orders.created_by',$condition)
                                . " AND sales_orders.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=sales_order_receipts.exchange_rate_id),0)) FROM sales_order_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE sales_order_receipt_id=sales_order_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND sales_order_id=sales_orders.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND sales_order_id=sales_orders.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM credit_memos WHERE id=credit_memo_with_sales.credit_memo_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) > 0.001"
                                . " AND is_pos IN (0,2)"
                                . "
                            UNION
                            SELECT
                                credit_memos.id AS id,
                                customers.id AS customer_id,
                                customers.customer_code,
                                customers.name AS customer_name,
                                (credit_memos.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=credit_memo_receipts.exchange_rate_id),0)) FROM credit_memo_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=credit_memo_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND credit_memo_id=credit_memos.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND credit_memo_id=credit_memos.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM sales_orders WHERE id=credit_memo_with_sales.sales_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0))*-1 AS balance
                            FROM credit_memos
                                LEFT JOIN customers ON customers.id = credit_memos.customer_id
                            WHERE "
                                . str_replace('created_by','credit_memos.created_by',$condition)
                                . " AND credit_memos.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=credit_memo_receipts.exchange_rate_id),0)) FROM credit_memo_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=credit_memo_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND credit_memo_id=credit_memos.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND credit_memo_id=credit_memos.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM sales_orders WHERE id=credit_memo_with_sales.sales_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) > 0.001"
                                . "
                            ORDER BY customer_id,customer_code,customer_name") or die(mysql_error());
        $customerRecords = array();
        while($data=mysql_fetch_array($query)){
            if (array_key_exists($data['customer_id'], $customerRecords)){
                $customerRecords[$data['customer_id']]['balance'] += $data['balance'];
            } else {
                $customerRecords[$data['customer_id']]['code'] = $data['customer_code'];
                $customerRecords[$data['customer_id']]['name'] = $data['customer_name'];
                $customerRecords[$data['customer_id']]['balance'] = $data['balance'];
            }
        } 
        $i = 0;
        foreach($customerRecords  AS $customerRecord){
            $excelContent .= "\n".++$i."\t".$customerRecord['code']."\t".$customerRecord['name']."\t".number_format($customerRecord['balance'], 2);
            $grandTotalBalance += $customerRecord['balance'];
        ?>
        <tr style="font-weight: bold;">
            <td class="first"><?php echo $i; ?></td>
            <td><?php echo $customerRecord['code']; ?></td>
            <td><?php echo $customerRecord['name']; ?></td>
            <td style="text-align: right;"><?php echo number_format($customerRecord['balance'], 2); ?></td>
        </tr>
        <?php
        }
        $excelContent .= "\n\n".'Grand Total Balance'."\t\t\t\t\t\t".number_format($grandTotalBalance,2); ?>
        <tr><td colspan="4">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="3" style="font-size: 14px;">Grand Total Balance</td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalBalance,2); ?></td>
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