<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#inputInvoiceDisAmt").autoNumeric({mDec: <?php echo $rowOption[0]; ?>, aSep: ','});
        $("#inputInvoiceDisPer").autoNumeric({mDec: 2, aSep: ','});
        $("#inputInvoiceDisAmt, #inputInvoiceDisPer").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#inputInvoiceDisAmt, #inputInvoiceDisPer").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
            if($(this).attr("id") == 'inputInvoiceDisAmt'){
                $("#inputInvoiceDisPer").val(0);
            } else {
                $("#inputInvoiceDisAmt").val(0);
            }
        });
    });
</script>
<table cellpadding="4" cellspacing="0" style="width: 400px;">
    <tr>
        <td style="width: 30%;">Discount Amount: </td>
        <td>
            <input type="text" id="inputInvoiceDisAmt" style="width: 90%;" value="0" /> $
        </td>
    </tr>
    <tr>
        <td style="width: 30%;">Discount Percent: </td>
        <td>
            <input type="text" id="inputInvoiceDisPer" style="width: 90%;" value="0" /> %
        </td>
    </tr>
</table>
