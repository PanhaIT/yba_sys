<script type="text/javascript">
    $(document).ready(function(){
        $("#inputOrderDisAmt, #inputOrderDisPer").autoNumeric({mDec: 2, aSep: ','});
        $("#inputOrderDisAmt, #inputOrderDisPer").focus(function(){
            if($(this).val() == '0'){
                $(this).val('');
            }
        });
        
        $("#inputOrderDisAmt, #inputOrderDisPer").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
            if($(this).attr("id") == 'inputOrderDisAmt'){
                $("#inputOrderDisPer").val(0);
            } else {
                $("#inputOrderDisAmt").val(0);
            }
        });
    });
</script>
<table cellpadding="4" cellspacing="0" style="width: 330px;">
    <tr>
        <td style="width: 40%;">Discount Amount: </td>
        <td>
            <input type="text" id="inputOrderDisAmt" style="width: 90%;" value="0" /> $
        </td>
    </tr>
    <tr>
        <td>Discount Percent: </td>
        <td>
            <input type="text" id="inputOrderDisPer" style="width: 90%;" value="0" /> %
        </td>
    </tr>
</table>
