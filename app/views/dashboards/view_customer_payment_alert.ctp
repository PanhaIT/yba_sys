<?php 
include("includes/function.php");
?>
<script type="text/javascript">
    var oTableReorderLevel;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        
        $("#filterCustomerPaymentAlert").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewCustomerPaymentAlert/"+$("#filterCustomerPaymentAlert").val(),
                beforeSend: function(){
                    $("#refreshCustomerPaymentAlert").hide();
                    $("#loadingCustomerPaymentAlert").show();
                    $("#customerPaymentAlertView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshCustomerPaymentAlert").show();
                    $("#loadingCustomerPaymentAlert").hide();
                    $("#customerPaymentAlertView").html(result);
                }
            });
        });
        
        $("#refreshCustomerPaymentAlert").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewCustomerPaymentAlert/"+$("#filterCustomerPaymentAlert").val(),
                beforeSend: function(){
                    $("#refreshCustomerPaymentAlert").hide();
                    $("#loadingCustomerPaymentAlert").show();
                    $("#customerPaymentAlertView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshCustomerPaymentAlert").show();
                    $("#loadingCustomerPaymentAlert").hide();
                    $("#customerPaymentAlertView").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewReorderLevel" style="width: 100%; margin: 0 auto">
    <table class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"><?php echo TABLE_NO; ?></th>
                <th><?php echo TABLE_INVOICE_DATE; ?></th>
                <th><?php echo TABLE_INVOICE_CODE; ?></th>
                <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                <th><?php echo GENERAL_BALANCE; ?></th>
                <th><?php echo REPORT_DUE_DATE; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 0;
            $sqlInv = mysql_query("SELECT so.*, pt.net_days FROM sales_invoices AS so INNER JOIN payment_terms AS pt ON pt.id = so.payment_term_id WHERE so.status > 0 AND so.balance > 0");
            while($rowInv = mysql_fetch_array($sqlInv)){
                $dateNow  = date("Y-m-d");
                $dueDate  = date('Y-m-d', strtotime($rowInv['order_date']. ' + '.$rowInv['net_days'].' days'));
                $totalAmount = $rowInv['total_amount'] + $rowInv['total_vat'] - $rowInv['discount'];
                if($rowInv['balance'] != $totalAmount){
                    $sqlReceipt = mysql_query("SELECT due_date FROM sales_order_receipts WHERE sales_order_id = ".$rowInv['id']." AND is_void = 0 ORDER BY id DESC LIMIT 1;");
                    $rowReceipt = mysql_fetch_array($sqlReceipt);
                    $dueDate    = $rowReceipt[0];
                }
                $dateInv = date_create($dueDate);
                $dateCur = date_create($dateNow);
                $diff    = date_diff($dateCur,$dateInv);
                $aging   = (int) $diff->format("%R%a");
                if($aging == 1){
            ?>
            <tr>
                <td class="first"><?php echo ++$index; ?></td>
                <td><?php echo dateShort($rowInv['order_date']); ?></td>
                <td><?php echo $rowInv['so_code']; ?></td>
                <td><?php echo number_format($rowInv['total_amount'] - $rowInv['discount'] + $rowInv['total_vat'], 2); ?></td>
                <td><?php echo number_format($rowInv['balance'], 2); ?></td>
                <td><?php echo dateShort($dueDate); ?></td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>