<?php
    $totalReceive = 0;
    $totalTransfer = 0;
    $sql = mysql_query("SELECT qty FROM `transfer_receives` WHERE transfer_order_id = ".$order_id." and status = 1");
    while(@$r=mysql_fetch_array($sql)){
        $totalReceive += $r['qty'];
    }
    $sql = mysql_query("SELECT qty FROM `transfer_order_details` WHERE transfer_order_id = ".$order_id);
    while(@$r=mysql_fetch_array($sql)){
        $totalTransfer += $r['qty'];
    }
?>
<script type="text/javascript">
    function checkPoint(val){
        var result = parseUniFloat(val);
        if(val.indexOf('.') != -1){
            if(val.split('.')[1] == "" || val.split('.')[1] == 0){
                result = parseUniFloat(val)+'.';
            }
        }
        return result;
    }
    
    $(document).ready(function(){
        $("#qty").keypress(function(e){
            if( e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57) && (e.which < 6112 || e.which > 6121) && e.which != 6100){
                return false;
            }else if(e.which == 46 || e.which == 6100){
                if($(this).val() == ""){
                    if(e.which == 46){
                        $(this).val("0");
                    }else{
                        $(this).val("០");
                    }
                }else{
                    if($(this).val().toString().indexOf('.') != -1){
                        return false;
                    }
                }
            }
        });
        
        $("#qty").keyup(function(){
            $(this).val($(this).val().replace(/\។/g,"."));
            if(!isNaN($(this).val()) && $(this).val() != ''){
                $(this).val(checkPoint($(this).val()));
            }else{
                $(this).val('0');
            }
        });
         
        $("#expired").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy'
        }).unbind("blur");
        
        $("#date_receive_act_to").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#date_receive_act_to").datepicker("option", "minDate", "<?php echo date("d/m/Y", strtotime($transferOrder['TransferOrder']['order_date'])); ?>");
        $("#date_receive_act_to").datepicker("option", "maxDate", "<?php echo date('d/m/Y'); ?>");
    });
</script>
<?php
    // Find Uom location to
    $sql = mysql_query("SELECT price_uom_id, small_val_uom FROM `products` WHERE id = ".$pro_id." LIMIT 1");
    while($r=mysql_fetch_array($sql)){
       $defaultUom = $r['price_uom_id'];
       $valueUomTo = $r['small_val_uom'];
    }
    $totalTransfer = 0;
    $totalTransferReceive = 0;
    // Get Total Qty Transfer Order
    $sql = mysql_query("SELECT sum(qty) FROM `transfer_order_details` WHERE transfer_order_id =".$order_id);
    while(@$t=mysql_fetch_array($sql)){
        $totalTransfer = $t[0];
    }
    
    // Get Total Receive Transfer Order
    $sql = mysql_query("SELECT sum(qty) FROM `transfer_receives` WHERE transfer_order_id =".$order_id." and status != 0");
    while(@$t=mysql_fetch_array($sql)){
        $totalTransferReceive = $t[0];
    }
    
    if(!@$totalTransferReceive){
        $totalTransferReceive = 0;
    }
?>
<p style="text-align: center; color:#C00; display:none;" id="error"></p>
<form method="post" id="frmReceiveTO">
<input type="hidden" value="<?php echo $from_location; ?>" name="data[from_location]" />
<input type="hidden" value="<?php echo $to_location; ?>" name="data[to_location]" />
<input type="hidden" value="<?php echo $conversion; ?>" name="data[value_uom]" />
<input type="hidden" value="<?php echo $totalReceive; ?>" name="data[qty_receive]" />
<input type="hidden" value="<?php echo $totalTransfer; ?>" name="data[qty_transfer]" />
<input type="hidden" name="data[TransferReceive][transfer_order_id]" value="<?php echo $order_id; ?>" />
<input type="hidden" name="data[product_id]" value="<?php echo $pro_id; ?>" />
<input type="hidden" name="data[TransferReceive][qty_uom_id]" value="<?php echo $uom_id; ?>" />
<input type="hidden" name="data[order_date]" value="<?php echo $transferOrder['TransferOrder']['order_date']; ?>" />
<input type="hidden" name="data[total_transfer]" value="<?php echo $totalTransfer; ?>" />
<input type="hidden" name="data[total_receive]" value="<?php echo $totalTransferReceive; ?>" />
<input type="hidden" name="data[lots_number]" value="<?php echo $lots_number; ?>" />
<input type="hidden" name="data[exp_date]" value="<?php echo $exp_date; ?>" />
<input type="hidden" name="data[from_location_group_id]" value="<?php echo $transferOrder['TransferOrder']['from_location_group_id']; ?>" />
<input type="hidden" name="data[to_location_group_id]" value="<?php echo $transferOrder['TransferOrder']['to_location_group_id']; ?>" />
<input type="hidden" name="data[receive_num]" value="<?php echo $receiveNo; ?>" />
<input type="hidden" value="<?php echo $detail_id; ?>" name="data[detail_id]" />
<table cellpadding="5" width="100%" style="margin-top: 5px;">
    <tr>
        <td>
            <label for="total"><?php echo TABLE_TOTAL_QTY; ?> :</label> </td>
        <td><input type="text" style="width:100px;" id="total" disabled="disabled" value="<?php echo $qty; ?>" class="validate[required]" /> <?php echo $uom_name; ?></td>
    </tr>
    <tr>
        <td>
            <label for="qty"><?php echo TABLE_QTY_ACCEPT; ?> <span class="red">*</span> :</label> </td>
        <td><input type="text" name="data[qty]" style="width:100px;" id="qty" value="0" class="validate[required]" /> <?php echo $uom_name; ?></td>
    </tr>
    <tr>
        <td>
            <label for="date_receive_act_to"><?php echo TABLE_DATE_RECEIVE; ?> <span class="red">*</span> :</label> </td>
        <td><input type="text" name="data[receive_date]" style="width:100px;" id="date_receive_act_to" value="" readonly="readonly" class="validate[required]" /></td>
    </tr>
</table>
</form>