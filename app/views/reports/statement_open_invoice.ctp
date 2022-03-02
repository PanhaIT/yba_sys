<?php
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
include('includes/function.php');
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnPrintInvoiceStatement").unbind("click").click(function(event){
            event.preventDefault();
            var url = '';
            if($(this).attr("trans_type")=="Invoice"){
                url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else{
                url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
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
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_STATEMENT . '</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <table class="table_solid" style="min-width: 300px;">
        <tr>
            <td>To:</td>
        </tr>
        <tr>
            <td style="height: 50px;">
                <?php
                $queryCustomer=mysql_query("SELECT id,CONCAT_WS(' ',customer_code,'-',name) FROM customers WHERE id=" . $_POST['customer_id']);
                $dataCustomer=mysql_fetch_array($queryCustomer);
                echo $dataCustomer[1];
                ?>
            </td>
        </tr>
    </table>
    <br />
    <table class="table_solid" style="width: 100%;">
        <tr>
            <th class="first" style="width: 100px !important;"><?php echo TABLE_NO; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_DATE; ?></th>
            <th><?php echo TABLE_CODE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_DUE_DATE; ?></th>
            <th style="width: 160px !important;"><?php echo GENERAL_AGING; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_OPEN_BALANCE; ?> ($)</th>
        </tr>
        <?php
        $index     = 1;
        $grandTotalBalance = 0;
        $date      = dateConvert($_POST['date_to']);
        $condition = 'status > 0';
        if ($_POST['date_to'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . $date . '" >= DATE(order_date)';
        }
        if ($_POST['customer_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'customer_id=' . $_POST['customer_id'];
        }
        $queryTransaction = mysql_query("SELECT
                                'Invoice' AS trans_type,
                                customers.id AS customer_id,
                                sales_orders.id AS id,
                                sales_orders.order_date AS order_date,
                                sales_orders.so_code AS invoice_code,
                                CONCAT_WS(' - ', customers.customer_code, customers.name) AS customer_name,
                                sales_orders.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=sales_order_receipts.exchange_rate_id),0)) FROM sales_order_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE sales_order_receipt_id=sales_order_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND sales_order_id=sales_orders.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND sales_order_id=sales_orders.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM credit_memos WHERE id=credit_memo_with_sales.credit_memo_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) AS balance,
                                IF(balance>0 AND DATEDIFF('".$date."',order_date)>0,DATEDIFF('".$date."',order_date),'-') AS aging,
                                payment_terms.net_days AS term
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
                                'Sales Return' AS trans_type,
                                customers.id AS customer_id,
                                credit_memos.id AS id,
                                credit_memos.order_date AS order_date,
                                credit_memos.cm_code AS invoice_code,
                                CONCAT_WS(' - ', customers.customer_code, customers.name) AS customer_name,
                                (credit_memos.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=credit_memo_receipts.exchange_rate_id),0)) FROM credit_memo_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=credit_memo_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND credit_memo_id=credit_memos.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND credit_memo_id=credit_memos.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM sales_orders WHERE id=credit_memo_with_sales.sales_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0))*-1 AS balance,
                                IF(balance>0 AND DATEDIFF('".$date."',order_date)>0,DATEDIFF('".$date."',order_date),'-') AS aging,
                                '0' AS term
                            FROM credit_memos
                                LEFT JOIN customers ON customers.id = credit_memos.customer_id
                            WHERE "
                                . str_replace('created_by','credit_memos.created_by',$condition)
                                . " AND credit_memos.balance + IFNULL((SELECT SUM(IFNULL(amount_us,0)+IFNULL(amount_other/(SELECT rate_to_sell FROM exchange_rates WHERE id=credit_memo_receipts.exchange_rate_id),0)) FROM credit_memo_receipts WHERE DATE_SUB(DATE(IF(pay_date IS NULL,(SELECT date FROM general_ledgers WHERE credit_memo_receipt_id=credit_memo_receipts.id),pay_date)), INTERVAL 1 DAY) >= '" . $date . "' AND credit_memo_id=credit_memos.id AND is_void=0),0) + IFNULL((SELECT SUM(total_price) FROM credit_memo_with_sales WHERE status=1 AND credit_memo_id=credit_memos.id AND DATE_SUB(DATE(IF(apply_date IS NULL,(SELECT order_date FROM sales_orders WHERE id=credit_memo_with_sales.sales_order_id),apply_date)), INTERVAL 1 DAY) >= '" . $date . "'),0) > 0.001"
                                . "
                            ORDER BY customer_name,order_date") or die(mysql_error());
        while($data=mysql_fetch_array($queryTransaction)){
            if($data['term'] > 0){
                $dueDate = date('d/m/Y', strtotime($data['order_date']. ' + 1 days'));
            } else {
                $dueDate = dateShort($data['order_date']);
            }
        ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td style="text-align: center;"><?php echo dateShort($data['order_date']); ?></td>
                <td><a href="" class="btnPrintInvoiceStatement" rel="<?php echo $data['id']; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['invoice_code']; ?></a></td>
                <td style="text-align: center;"><?php echo $dueDate; ?></td>
                <td style="text-align: center;"><?php echo $data['aging']; ?></td>
                <td style="text-align: right;"><?php echo number_format($data['balance'], 2); ?></td>
            </tr>
        <?php
            $grandTotalBalance += $data['balance'];
        }
        ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="6" style="font-size: 14px; text-align: right;">Total Balance</td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalBalance,2); ?></td>
        </tr>
    </table>
    <br />
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div style="clear: both;"></div>