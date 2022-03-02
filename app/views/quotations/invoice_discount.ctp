<script type="text/javascript">
    $(document).ready(function(){
        $("#inputQuotationDisAmt, #inputQuotationDisPer").autoNumeric({mDec: 2, aSep: ','});
        $("#inputQuotationDisAmt, #inputQuotationDisPer").focus(function(){
            if($(this).val() == '0'){
                $(this).val('');
            }
        });
        
        $("#inputQuotationDisAmt, #inputQuotationDisPer").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
            if($(this).attr("id") == 'inputQuotationDisAmt'){
                $("#inputQuotationDisPer").val(0);
            } else {
                $("#inputQuotationDisAmt").val(0);
            }
        });
    });
</script>
<table cellpadding="4" cellspacing="0" style="width: 350px;">
    <tr>
        <td style="width: 40%;">Discount Amount: </td>
        <td>
            <input type="text" id="inputQuotationDisAmt" style="width: 90%; height:25px;" value="0" /> $
        </td>
    </tr>
    <tr>
        <td>Discount Percent: </td>
        <td>
            <input type="text" id="inputQuotationDisPer" style="width: 90%; height:25px;" value="0" /> %
        </td>
    </tr>
</table>
