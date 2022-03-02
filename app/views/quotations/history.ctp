<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function() {
        $(".btnPrintHistoryQuotation").click(function(){
            var id = $(this).attr("row-id");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w=window.open();
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
<table class="table" cellspacing="0" style="width: 1200px;">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_QUOTATION_DATE; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_QUOTATION_NUMBER; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_CUSTOMER_NUMBER; ?></th>
            <th><?php echo TABLE_CUSTOMER_NAME; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_CREATED; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_CREATED_BY; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlHistory = mysql_query("SELECT quotations.id AS id, quotations.quotation_date AS quotation_date, quotations.quotation_code AS quotation_code, quotations.created AS created, quotations.created_by AS created_by, quotations.edited AS edited, quotations.edited_by AS edited_by, customers.customer_code, customers.name AS customer_name, currency_centers.symbol AS symbol, (quotations.total_amount - IFNULL(quotations.discount, 0) + IFNULL(quotations.total_vat, 0)) AS total_amount FROM quotations INNER JOIN customers ON customers.id = quotations.customer_id INNER JOIN currency_centers ON currency_centers.id = quotations.currency_center_id WHERE quotations.quotation_code = '{$quotationCode}' ORDER BY quotations.id");
        if(mysql_num_rows($sqlHistory)){
            $index = 0;
            while($rowHistory = mysql_fetch_array($sqlHistory)){
                $dateCreated = $rowHistory['created'];
                $userId = $rowHistory['created_by'];
                if($index > 0){
                    $dateCreated = $rowHistory['edited'];
                    $userId = $rowHistory['edited_by'];
                }
                $sqlUsers = mysql_query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = ".$userId);
                $rowUsers = mysql_fetch_array($sqlUsers);
        ?>
        <tr>
            <td style=" text-align: center;" class="first"><?php echo ++$index; ?></td>
            <td style=" text-align: left;"><?php echo dateShort($rowHistory['quotation_date']); ?></td>
            <td style=" text-align: left;"><a href="#" class="btnPrintHistoryQuotation" row-id="<?php echo $rowHistory['id']; ?>"><?php echo $rowHistory['quotation_code']; ?></a></td>
            <td style=" text-align: left;"><?php echo $rowHistory['customer_code']; ?></td>
            <td style=" text-align: left;"><?php echo $rowHistory['customer_name']; ?></td>
            <td style=" text-align: left;"><?php echo $rowHistory['symbol']." ".number_format($rowHistory['total_amount'], 2); ?></td>
            <td style=" text-align: left;"><?php echo dateShort($dateCreated, "d/m/Y H:i:s"); ?></td>
            <td style=" text-align: left;"><?php echo $rowUsers[0]; ?></td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="6" class="first"><?php echo TABLE_NO_RECORD; ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
