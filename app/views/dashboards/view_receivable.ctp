<?php
include("includes/function.php");
$symbol = '$';
$tableName = "dashboard_receivable";
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE `{$tableName}` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`company_id` INT(11) NULL DEFAULT NULL,
	`branch_id` INT(11) NULL DEFAULT NULL,
    `sales_invoice_id` INT(11) NULL DEFAULT NULL,
    `chart_account_id` INT(11) NULL DEFAULT NULL,
	`amount` DECIMAL(20,9) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `company` (`company_id`, `branch_id`),
	INDEX `filter` (`sales_invoice_id`, `chart_account_id`))
        ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName");
if($branch != 'all'){
    $condition = "AND gld.branch_id = ".$branch;
} else {
    $condition = "AND gld.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})";
}
$queryCoa = mysql_query("SELECT SUM(IFNULL(gld.debit, 0) - IFNULL(gld.credit, 0)) AS amount, chart_account_id, sales_invoice_id, company_id, branch_id
                         FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                         INNER JOIN chart_accounts ON chart_accounts.id = gld.chart_account_id AND chart_accounts.chart_account_type_id = 2
                         WHERE gl.is_approve=1 AND gl.is_active=1 AND gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = {$user['User']['id']}) ".$condition."
                         GROUP BY chart_account_id,sales_invoice_id,company_id,branch_id HAVING amount > 0");
while ($dataCoa = mysql_fetch_array($queryCoa)) {
    mysql_query("INSERT INTO $tableName (
                            chart_account_id,
                            sales_invoice_id,
                            company_id,
                            branch_id,
                            amount
                        ) VALUES (
                            " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['sales_invoice_id']) ? $dataCoa['sales_invoice_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['branch_id']) ? $dataCoa['branch_id'] : "NULL") . ",
                            " . $dataCoa['amount'] . ")");
}

$isEmpty = 0;
$totalOverDue = 0;
$totalReceivable = 0;
$sqlAR = mysql_query("SELECT SUM(IFNULL(amount, 0)) AS total FROM {$tableName} WHERE 1");
while($rowAR = mysql_fetch_array($sqlAR)){
    $totalReceivable += $rowAR['total'];
}
$sqlOver = mysql_query("SELECT ar.amount FROM {$tableName} AS ar INNER JOIN sales_invoices ON sales_invoices.id = ar.sales_invoice_id INNER JOIN payment_terms ON payment_terms.id = sales_invoices.payment_term_id WHERE DATE_ADD(sales_invoices.`order_date`, INTERVAL payment_terms.net_days DAY) < CURDATE();");
while($rowOver = mysql_fetch_array($sqlOver)){
    $totalOverDue += $rowOver[0];
}
$totalOutStanding = $totalReceivable - $totalOverDue;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#filterReceivable").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewReceivable/"+$("#filterBranchReceivable").val(),
                beforeSend: function(){
                    $("#refreshReceivable").hide();
                    $("#loadingReceivable").show();
                    $("#receivableView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-0px" />');
                },
                success: function(result){
                    $("#refreshReceivable").show();
                    $("#loadingReceivable").hide();
                    $("#receivableView").html(result);
                }
            });
        });
        
        $("#refreshReceivable").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewReceivable/"+$("#filterBranchReceivable").val(),
                beforeSend: function(){
                    $("#refreshReceivable").hide();
                    $("#loadingReceivable").show();
                    $("#receivableView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-0px" />');
                },
                success: function(result){
                    $("#refreshReceivable").show();
                    $("#loadingReceivable").hide();
                    $("#receivableView").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewReceivable" style="width: 98%; margin: 0px auto; font-size: 15px; height: 20px;">
    <?php echo TABLE_TOTAL_RECEIVABLES; ?> : <?php echo number_format($totalReceivable, 2)." ".$symbol; ?>
</div>
<br />
<table cellpadding="5" cellspacing="0" style="width: 98%; margin: 0px auto; height: 50px;">
    <tr>
        <td style="width: 50%; background-color: rgb(149,206,255); color: #fff; font-size: 15px;">
            <?php echo TABLE_OUTSTANDING; ?> <br /><br />
            <?php echo number_format($totalOutStanding, 2)." ".$symbol; ?>
        </td>
        <td style="width: 50%; background-color: rgb(247,163,92); color: #fff; font-size: 15px;">
            <?php echo TABLE_OVERDUE; ?> <br /><br />
            <?php echo number_format($totalOverDue, 2)." ".$symbol; ?>
        </td>
    </tr>
</table>
