<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Action Reprint Invoice
        $(".btnReprintInvoicePOS").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var url = "<?php echo $this->base . '/' . "point_of_sales"; ?>/printReceipt/";
            $.ajax({
                type: "POST",
                url: url+id,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w = window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
    });
</script>
<table cellpadding="3" cellspacing="0" class="table" style="width: 950px;">
    <tr>
        <th style="width: 70px !important; text-align: center;" class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 190px !important; text-align: center;"><?php echo SALES_ORDER_DATE; ?></th>
        <th style="width: 160px !important; text-align: center;"><?php echo TABLE_INVOICE_CODE; ?></th>
        <th style="text-align: center;"><?php echo PRICING_RULE_CUSTOMER; ?></th>
        <th style="width: 180px !important; text-align: center;"><?php echo TABLE_TOTAL_AMOUNT; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
        <th style="width: 140px !important; text-align: center;"><?php echo GENERAL_BALANCE; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
        <th style="width: 80px !important; text-align: center;"><?php echo TABLE_TYPE; ?></th>
        <th></th>
    </tr>
    <?php
    $index = 0;
    $sqlPos = mysql_query("SELECT id, order_date, so_code, total_amount, balance FROM sales_orders WHERE order_date = '".date("Y-m-d")."' AND status = 2 AND is_pos = 1 AND created_by = ".$user['User']['id']);
    if(@mysql_num_rows($sqlPos)){
        $totalAmount = 0;
        while($row = mysql_fetch_array($sqlPos)){
            $totalAmount += $row['total_amount'];
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo dateShort($row['order_date']); ?></td>
        <td><?php echo $row['so_code']; ?></td>
        <td>General Customer</td>
        <td><?php echo $row['total_amount']; ?></td>
        <td><?php echo $row['balance']; ?></td>
        <td>POS</td>
        <td>
            <a href="#" class="btnReprintInvoicePOS" rel="<?php echo $row['id']; ?>"><img alt="Reprint Invoice" onmouseover="Tip('<?php echo ACTION_REPRINT_INVOICE; ?>')" src="<?php echo $this->webroot; ?>'img/button/printer.png" /></a>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="8" class="first" style="text-align: center;"><?php echo GENERAL_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>

