<?php
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    var intervalReceiveAll = '';
    function getDataReceiveAll(){
        var i =1;
        var data = "";
        var length = $(".ReceiveDetail").length;
        $(".ReceiveDetail").each(function(){
            if($(this).find("input[name='product_id[]']").val() != "" && $(this).find("input[name='product_id[]']").val() != null){
                $(this).find("input[name='qty_transfer[]']").val($(this).find("input[name='qty_transfer[]']").val().replace(/,/g,""));
                if(i > 1 && i <= length){
                    data += ",";
                }
                data += '{ "detail_id" : "'+$(this).find("input[name='detail_id[]']").val()+'","from_location_id" : "'+$(this).find("input[name='from_location_id[]']").val()+'","to_location_id" : "'+$(this).find("input[name='to_location_id[]']").val()+'","lots_number" : "'+$(this).find("input[name='lots_number[]']").val()+'","expired_date" : "'+$(this).find("input[name='expired_date[]']").val()+'","product_id" : "'+$(this).find("input[name='product_id[]']").val()+'","purchase_uom" : "'+$(this).find("input[name='purchase_uom[]']").val()+'", "qty_transfer": "'+$(this).find("input[name='qty_transfer[]']").val()+'" , "total_qty": "'+$(this).find("input[name='total_qty[]']").val()+'" , "uom_conversion": "'+$(this).find("input[name='uom_conversion[]']").val()+'"}';
                i++;
            }
        });
        return data;
    }
    
    function showDialogTO(msg){
        $("#dialog").html(msg);
        $("#dialog").dialog({
            title: '<?php echo DIALOG_WARNING; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function showDialogTOReceive(msg){
        $("#dialog").html(msg);
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION . " " . $this->data['TransferOrder']['to_code']; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function(){
                $(this).dialog({close: function(){}});
                loadToDetail();
                $(this).dialog("close");
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function checkStatusReceiveAllTO(id, interval){
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo $this->base . '/users'; ?>/checkReceiveAllTO/"+id,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                if(result.success == 1){
                    clearInterval(interval);
                    showDialogTOReceive('<div class="buttons"><button type="submit" class="positive printInvoiceReceive" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_TRANSFER_RECEIVE; ?></span></button></div> ');
                    $(".printInvoiceReceive").click(function(){
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
                }else if(result.success == 2){
                    clearInterval(interval);
                    showDialogTOReceive('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CANT_RECEIVE;?></p>');
                }else if(result.error == 1){
                    clearInterval(interval);
                    showDialogTOReceive('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                }else if(result.error == 2){
                    clearInterval(interval);
                    showDialogTOReceive('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SOME_PRODUCT_OUT_OF_STOCK; ?></p>');
                }
            }
        });
    }
    
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TransferReceiveAllForm").unbind("submit");
        $("#TransferReceiveAllForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TransferReceiveAllForm").submit(function(){
            var formName = "#TransferReceiveAllForm";
            var validateBack =$(formName).validationEngine("validate");
            if(validateBack){
                $("#date_receive_to").datepicker("option", "dateFormat", "yy-mm-dd");
                var dataToSend = '{ "detail" : ['+getDataReceiveAll()+'] }';
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $this->base; ?>/transfer_receives/addReceiveCrontab",
                    data: "data[transfer_order_id]="+$("#transfer_order_id").val()+"&data[order_date]="+$("#order_date").val()+"&data[receive_number]="+$("#transferReceiveNo").val()+"&data[receive_date]="+$("#transferReceiveDate").val()+"&json="+JSON.stringify(dataToSend),
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        $(".txtSaveTrReceiveAll").html("<?php echo ACTION_LOADING; ?>");
                        $(".btnReceiveTOAll").attr('disabled','disabled');
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        if(result.error == 1){
                            showDialogTOReceive('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                        }else if(result.is_process == 1){
                            showDialogTOReceive('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                        }else if(result.to_id != "" && result.to_id != undefined){
                            intervalReceiveAll = setInterval(function() {
                                checkStatusReceiveAllTO(result.to_id, intervalReceiveAll);
                            }, 2000);
                        }
                    }
                });
            }
            return false;
        });
        
        $(".btnTOReceive").click(function(){
            var record  = $(this).attr('name');
            var lotsNum = $(this).attr('lots-num');
            var expDate = $(this).attr('exp-date');
            var receive = $("#transferReceiveNo").val();
            var array   = record.split('|!|');
            var qty     = array[1];
            var name    = $(this).closest("tr").find("td.first").html();
            var width   = 500;
            var height  = 130;
            if(receive != ''){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/action/" + record,
                    data: "receive_num="+receive+"&lots_number="+lotsNum+"&exp_date="+expDate,
                    beforeSend: function(){
                        $("#dialog").html('<p style="text-align:center"><img alt="" src="<?php echo $this->webroot; ?>img/loading.gif" /></p>');
                    },
                    success: function(msg){
                        $("#dialog").dialog('option', 'height', height);
                        $("#dialog").dialog('option', 'position', ['center']);
                        $(".ui-dialog-titlebar").show();
                        $(".ui-dialog-buttonpane").show();
                        $("#dialog").html(msg);
                    }
                });
                $("#dialog").dialog({
                    title: "<?php echo TABLE_PRODUCT_RECEIVED; ?> " + name,
                    resizable: false,
                    modal: true,
                    width: width,
                    height: 'auto',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-titlebar").hide();
                        $(".ui-dialog-buttonpane").hide();
                    },
                    buttons: {
                        '<?php echo ACTION_SAVE; ?>': function() {
                            var qtyReceive = parseInt($("#qty").val());
                            if(qtyReceive > qty){
                                $("#dialog").dialog('option', 'height', '300');
                                $("#dialog").dialog('option', 'position', ['center']);
                                $("#error").show();
                                $("#error").html("<?php echo MESSAGE_CONFIRM_RECEIVE_TO; ?>");
                            }else{
                                $("#error").hide();
                                var validateBack = $("#frmReceiveTO").validationEngine("validate");
                                var post = $("#frmReceiveTO").serialize();
                                if(validateBack){
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/action/",
                                        data: post,
                                        beforeSend: function(){
                                            $("#dialog").dialog('option', 'position', ['center',140]);
                                            $("#dialog").dialog('option', 'height', 170);
                                            $(".ui-dialog-titlebar").hide();
                                            $(".ui-dialog-buttonpane").hide();
                                            $("#dialog").html('<p style="text-align:center"><img alt="" src="<?php echo $this->webroot; ?>img/loading.gif" /></p>');
                                        },
                                        success: function(result){
                                            loadToDetail();
                                            $(".ui-dialog-titlebar").show();
                                            $("#dialog").dialog('option', 'position', 'center');
                                            $("#dialog").dialog('option', 'width', '200');
                                            $(".ui-dialog-buttonpane").show();
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position: ['center'],
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }
                            }
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }else{
                showDialogTO("<?php echo MESSAGE_ENTER_TRANSFER_RECEIVE_NUMBER; ?>");
            }
        });
        
        $(".btnTOVoid").click(function(event){
            event.preventDefault();
            var array = $(this).attr('name');
            var name = $(this).closest("tr").find("td.first").html();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 300,
                height: 'auto',
                position: 'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/void/" + array,
                            data: "",
                            beforeSend: function(){
                                $(".ui-dialog-buttonpane").hide();
                                $(".ui-dialog-titlebar").hide();
                                $("#dialog").html('<p style="text-align:center"><img alt="" src="<?php echo $this->webroot; ?>img/loading.gif" /></p>');
                            },
                            success: function(result){
                                loadToDetail();
                                $(".ui-dialog-titlebar").show();
                                $(".ui-dialog-buttonpane").show();
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 300,
                                    height: 'auto',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
        
    });
</script>
<?php

function checkReceive($product_id, $to_id, $detail_id, $lots_number, $expiredDate) {
    if ($detail_id != null && $detail_id != "") {
        $sql = mysql_query("SELECT * FROM `transfer_receives` AS tr WHERE tr.transfer_order_id = " . $to_id . " AND product_id = " . $product_id . " AND lots_number = '".$lots_number."' AND expired_date = '".$expiredDate."' AND tr.transfer_order_detail_id = " . $detail_id . " AND tr.status != 0");
    } else {
        $sql = mysql_query("SELECT * FROM `transfer_receives` AS tr WHERE tr.transfer_order_id = " . $to_id . " AND product_id = " . $product_id . " AND lots_number = '".$lots_number."' AND expired_date = '".$expiredDate."' AND  tr.status != 0");
    }
    if (@$num = mysql_num_rows($sql)) {
        return 1;
    } else {
        return 0;
    }
}

function countReceive($product_id, $to_id, $detail_id, $lots_number, $expiredDate) {
    $total = 0;
    if ($detail_id != null && $detail_id != "") {
        $sql = mysql_query("SELECT tr.qty as qty FROM `transfer_receives` AS tr WHERE tr.transfer_order_id = " . $to_id . " AND product_id = " . $product_id . " AND lots_number = '".$lots_number."' AND expired_date = '".$expiredDate."' AND tr.transfer_order_detail_id = " . $detail_id . " AND tr.status = 1");
    } else {
        $sql = mysql_query("SELECT tr.qty as qty FROM `transfer_receives` AS tr WHERE tr.transfer_order_id = " . $to_id . " AND product_id = " . $product_id . " AND lots_number = '".$lots_number."' AND expired_date = '".$expiredDate."' AND  tr.status = 1");
    }

    while (@$r = mysql_fetch_array($sql)) {
        $total += $r['qty'];
    }
    return $total;
}

function checkQtyStock($produc_id, $location_id, $lots_number, $expiredDate) {
    $total = 0;
    $sql = mysql_query("SELECT total_qty FROM `".$location_id."_inventory_totals` WHERE product_id = " . $produc_id . " AND lots_number = '".$lots_number."' AND expired_date = '".$expiredDate."' AND location_id =" . $location_id);
    while (@$r = mysql_fetch_array($sql)) {
        $total = $r['total_qty'];
    }
    return $total;
}
?>

<input type="hidden" value="<?php echo $id; ?>" name="data[transfer_order_id]" id="transfer_order_id" />
<input type="hidden" value="<?php echo $this->data['TransferOrder']['order_date']; ?>" id="order_date" name="data[order_date]" />
<!-- Table List Purchase Order -->
<fieldset style="width:98%; margin-bottom: 10px;">
    <legend><?php __(MENU_INFO_ITEM_RECEIVE); ?></legend>
    <table cellpadding="5" style="width:100%" class="table">
        <tr>
            <th class="first" style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
            <th style="width:10%"><?php echo TABLE_SKU; ?></th>
            <th style="width:20%"><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width:10%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
            <th style="width:10%"><?php echo TABLE_EXPIRED_DATE; ?></th>
            <th style="width:10%"><?php echo TABLE_QTY_TRANSFER; ?></th>
            <th style="width:10%"><?php echo TABLE_QTY_RECEIVE; ?></th>
            <th style="width:10%"><?php echo TABLE_UOM; ?></th>
            <th style="width:6%"><?php echo TABLE_STATUS; ?></th>
            <th style="width:4%; display: none;"><?php echo ACTION_ACTION; ?></th>
        </tr>
        <?php
        $btnSave = false;
        $i = 1;
        $sql = mysql_query("SELECT td.product_id AS p_id, p.code AS code, p.barcode AS barcode, p.name AS name, td.qty AS qty, 
                        u.name AS u_name, u.id AS u_id, 
                        td.id AS detail_id, td.conversion AS conversion, td.lots_number AS lots_number, td.expired_date AS expired_date, td.location_from_id AS from_location, td.location_to_id AS to_location
                        FROM `transfer_orders` AS tr 
                        INNER JOIN `transfer_order_details` AS td ON td.transfer_order_id = tr.id  
                        INNER JOIN `products` AS p ON p.id = td.product_id 
                        INNER JOIN `uoms` AS u ON u.id = td.qty_uom_id WHERE tr.id = " . $id . " AND tr.status > 0");
        if (@$num = mysql_num_rows($sql)) {
            while ($row = mysql_fetch_array($sql)) {
                $remainQty = $row['qty']; // Get Qty From Trnasfer
                $break = false;
                if ($i % 2 == 0) {
                    $class = "class='even ReceiveDetail'";
                } else {
                    $class = "class='odd ReceiveDetail'";
                }
                $arrayUomId = array();
                $arrayValue = array();
                $valueUomTo = $row['conversion'];
                // Get total qty as small qty in inventory total
                $qtyStock = checkQtyStock($row['p_id'], $row['from_location'], $row['lots_number'], $row['expired_date']);
                // Check Receive
                if (checkReceive($row['p_id'], $id, $row['detail_id'], $row['lots_number'], $row['expired_date'])) {
                    $modified = true;
                    $total_receive = countReceive($row['p_id'], $id, $row['detail_id'], $row['lots_number'], $row['expired_date']); // Get total qty receive
                    $remain = $remainQty - $total_receive; // Get total qty after receive
                    $st = mysql_query("SELECT tr.qty as qty, tr.id as id, 
                                        tr.transfer_order_detail_id as detail_id
                                        FROM `transfer_receives` as tr
                                        WHERE tr.transfer_order_id = " . $id . " 
                                        and tr.product_id = " . $row['p_id'] . " and tr.status != 0");
                    while ($rt = mysql_fetch_array($st)) {
                        if ($rt['detail_id'] != null && $rt['detail_id'] != "") {
                            if ($rt['detail_id'] == $row['detail_id']) {
                                $sqStock = mysql_query("SELECT total_qty FROM ".$row['to_location']."_inventory_totals WHERE product_id = ".$row['p_id']." AND lots_number = '".$row['lots_number']."' AND expired_date = '".$row['expired_date']."'");
                                $qtyStock = mysql_fetch_array($sqStock);
                                $qtyReceive = ($rt['qty'] * $valueUomTo);
                                if($qtyStock[0] < $qtyReceive){
                                    $modified = false;
                                }
                                ?>
                                <tr <?php echo $class; ?>>
                                    <td class="first">
                                        <input type="hidden" value="<?php echo $valueUomTo; ?>" name="uom_conversion[]" />
                                        <input type="hidden" value="<?php echo $rt['qty']; ?>" name="total_qty[]" />
                                        <input type="hidden" value="<?php echo $row['p_id']; ?>" name="product_id[]" />
                                        <input type="hidden" value="<?php echo $row['u_id']; ?>" name="purchase_uom[]" />
                                        <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="detail_id[]" />
                                        <input type="hidden" value="<?php echo $row['from_location']; ?>" name="from_location_id[]" />
                                        <input type="hidden" value="<?php echo $row['to_location']; ?>" name="to_location_id[]" />
                                        <input type="hidden" value="<?php echo $row['lots_number']; ?>" name="lots_number[]" />
                                        <input type="hidden" value="<?php echo $row['expired_date']; ?>" name="expired_date[]" />
                                        <?php echo $row['barcode']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['code']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['name']; ?>
                                    </td>
                                    <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                        <?php echo $row['lots_number']; ?>
                                    </td>
                                    <td>
                                        <?php echo dateShort($row['expired_date']); ?>
                                    </td>
                                    <td>
                                        <?php echo $rt['qty']; ?>
                                    </td>
                                    <td>
                                        <?php echo $this->Form->hidden('qty_trnasfer', array('class' => 'qty_transfer', 'value' => 0, 'name' => 'qty_transfer[]')); ?>
                                    </td>
                                    <td><?php echo $row['u_name']; ?></td>
                                    <td id="status_<?php echo $i; ?>">Accepted</td>
                                    <td style="display: none;">
                                        <?php
                                        if ($modified) {
                                            ?>
                                            <a href="#" style="display: none;" class="btnTOVoid" name="<?php echo $id . '|!|' . $rt['id'] . '|!| |!|' . $row['p_id'] . '|!|' . $row['from_location'] . '|!|' . $row['to_location']; ?>" rel=""><img src="<?php echo $this->webroot; ?>img/button/delete.png" alt="" title="Void" /></a>
                                            <?php
                                        } else {
                                            echo TABLE_MODIFIED;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            }
                        } else {
                            $sqStock = mysql_query("SELECT total_qty FROM ".$row['to_location']."_inventory_totals WHERE product_id = ".$row['p_id']." AND lots_number = '".$row['lots_number']."' AND expired_date = '".$row['expired_date']."'");
                            $qtyStock = mysql_fetch_array($sqStock);
                            $qtyReceive = ($rt['qty'] * $valueUomTo);
                            if($qtyStock[0] < $qtyReceive){
                                $modified = false;
                            }
                            ?>
                            <tr <?php echo $class; ?>>
                                <td class="first">
                                    <input type="hidden" value="<?php echo $valueUomTo; ?>" name="uom_conversion[]" />
                                    <input type="hidden" value="<?php echo $rt['qty']; ?>" name="total_qty[]" />
                                    <input type="hidden" value="<?php echo $row['p_id']; ?>" name="product_id[]" />
                                    <input type="hidden" value="<?php echo $row['u_id']; ?>" name="purchase_uom[]" />
                                    <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="detail_id[]" />
                                    <input type="hidden" value="<?php echo $row['from_location']; ?>" name="from_location_id[]" />
                                    <input type="hidden" value="<?php echo $row['to_location']; ?>" name="to_location_id[]" />
                                    <input type="hidden" value="<?php echo $row['lots_number']; ?>" name="lots_number[]" />
                                    <input type="hidden" value="<?php echo $row['expired_date']; ?>" name="expired_date[]" />
                                    <?php echo $row['barcode']; ?>
                                </td>
                                <td>
                                    <?php echo $row['code']; ?>
                                </td>
                                <td>
                                    <?php echo $row['name']; ?>
                                </td>
                                <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                        <?php echo $row['lots_number']; ?>
                                </td>
                                <td>
                                    <?php echo dateShort($row['expired_date']); ?>
                                </td>
                                <td>
                                    <?php echo $rt['qty']; ?>
                                </td>
                                <td>
                                    <?php echo $this->Form->hidden('qty_trnasfer', array('class' => 'qty_transfer', 'value' => 0, 'name' => 'qty_transfer[]')); ?>
                                </td>
                                <td><?php echo $row['u_name']; ?></td>
                                <td id="status_<?php echo $i; ?>">Accepted</td>
                                <td style="display: none;">
                                    <?php
                                    if ($modified) {
                                        ?>
                                        <a href="#" style="display: none;" class="btnTOVoid" name="<?php echo $id . '|!|' . $rt['id'] . '|!| |!|' . $row['p_id'] . '|!|' . $row['from_location'] . '|!|' . $row['to_location']; ?>" rel=""><img src="<?php echo $this->webroot; ?>img/button/delete.png" alt="" title="Void" /></a>
                                        <?php
                                    } else {
                                        echo TABLE_MODIFIED;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        }
                    } // End while transfer receive
                    if ($remain > 0) {
                        if ($qtyStock >= ($remainQty * $valueUomTo)) { 
                            $btnSave = true;
                            $statusLable = 'Entered';
                        } else {
                            $statusLable = 'Out Stock';
                        }
                    ?>
                        <tr <?php echo $class; ?> <?php if($statusLable == 'Out Stock'){ ?>style="background: red; color: #fff;"<?php } ?>>
                            <td class="first">
                                <input type="hidden" value="<?php echo $valueUomTo; ?>" name="uom_conversion[]" />
                                <input type="hidden" value="<?php echo $remain; ?>" name="total_qty[]" />
                                <input type="hidden" value="<?php echo $row['p_id']; ?>" name="product_id[]" />
                                <input type="hidden" value="<?php echo $row['u_id']; ?>" name="purchase_uom[]" />
                                <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="detail_id[]" />
                                <input type="hidden" value="<?php echo $row['from_location']; ?>" name="from_location_id[]" />
                                <input type="hidden" value="<?php echo $row['to_location']; ?>" name="to_location_id[]" />
                                <input type="hidden" value="<?php echo $row['lots_number']; ?>" name="lots_number[]" />
                                <input type="hidden" value="<?php echo $row['expired_date']; ?>" name="expired_date[]" />
                                <?php echo $row['barcode']; ?>
                            </td>
                            <td>
                                <?php echo $row['code']; ?>
                            </td>
                            <td><?php echo $row['name']; ?></td>
                            <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                <?php echo $row['lots_number']; ?>
                            </td>
                            <td>
                                <?php echo dateShort($row['expired_date']); ?>
                            </td>
                            <td><?php echo $remain; ?></td>
                            <td>
                                <div class="inputContainer">
                                    <?php
                                    if ($qtyStock >= $remain) {
                                        echo $this->Form->input('qty_trnasfer', array('class' => 'qty_transfer', 'value' => $remain, 'name' => 'qty_transfer[]', 'readonly'=>'readonly', 'label' => false, 'class' => 'validate[required,min[0],max[' . $remain . '],custom[number]]', 'style' => 'width: 120px;'));
                                    } else {
                                        echo $this->Form->hidden('qty_trnasfer', array('class' => 'qty_transfer', 'value' => 0, 'name' => 'qty_transfer[]'));
                                    }
                                    ?>
                                </div>
                            </td>
                            <td><?php echo $row['u_name']; ?></td>
                            <td id="status_<?php echo $i; ?>">
                                <?php
                                echo $statusLable;
                                ?>
                            </td>
                            <td style="display: none;">
                                <?php
                                if ($qtyStock >= $remain) {
                                    $btnSave = true;
                                    ?>
                                    <a href="#" style="display: none;" name="<?php echo $row['p_id'] . "|!|" . $remain . "|!|" . $row['u_id'] . "|!|" . $row['from_location'] . "|!|" . $row['to_location'] . "|!|" . $id . "|!| |!|" . $row['u_name'] . "|!|" . $row['detail_id']."|!|".$row['conversion']; ?>" lots-num="<?php echo $row['lots_number']; ?>" exp-date="<?php echo $row['expired_date']; ?>" class="btnTOReceive"><img src="<?php echo $this->webroot; ?>img/button/receiving.png" alt="" title="Receive" /></a>
                                    <?php
                                } else {
                                    echo TABLE_SHORTED;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else { // End If have received
                        if ($qtyStock >= ($remainQty * $valueUomTo)) { 
                            $btnSave = true;
                            $statusLable = 'Entered';
                        } else {
                            $statusLable = 'Out Stock';
                        }
                    ?>
                    <tr <?php echo $class; ?> <?php if($statusLable == 'Out Stock'){ ?>style="background: red; color: #fff;"<?php } ?>>
                        <td class="first">
                            <input type="hidden" value="<?php echo $valueUomTo; ?>" name="uom_conversion[]" />
                            <input type="hidden" value="<?php echo $remainQty; ?>" name="total_qty[]" />
                            <input type="hidden" value="<?php echo $row['p_id']; ?>" name="product_id[]" />
                            <input type="hidden" value="<?php echo $row['u_id']; ?>" name="purchase_uom[]" />
                            <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="detail_id[]" />
                            <input type="hidden" value="<?php echo $row['from_location']; ?>" name="from_location_id[]" />
                            <input type="hidden" value="<?php echo $row['to_location']; ?>" name="to_location_id[]" />
                            <input type="hidden" value="<?php echo $row['lots_number']; ?>" name="lots_number[]" />
                            <input type="hidden" value="<?php echo $row['expired_date']; ?>" name="expired_date[]" />
                            <?php echo $row['barcode']; ?>
                        </td>
                        <td>
                            <?php echo $row['code']; ?>
                        </td>
                        <td><?php echo $row['name']; ?></td>
                        <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                            <?php echo $row['lots_number']; ?>
                        </td>
                        <td>
                            <?php 
                            if($row['expired_date'] != '' && $row['expired_date'] != '0000-00-00'){
                                echo dateShort($row['expired_date']); 
                            }
                            ?>
                        </td>
                        <td><?php echo $remainQty; ?></td>
                        <td>
                            <div class="inputContainer">
                                <?php
                                if ($qtyStock >= ($remainQty * $valueUomTo)) { // Conver Remain Qty as small qty
                                    echo $this->Form->input('qty_trnasfer', array('class' => 'qty_transfer validate[required,min[0],max[' . $remainQty . '],custom[number]]', 'readonly'=>'readonly', 'value' => $remainQty, 'name' => 'qty_transfer[]', 'label' => false, 'style' => 'width: 120px;'));
                                } else {
                                    echo $this->Form->hidden('qty_trnasfer', array('class' => 'qty_transfer', 'value' => 0, 'name' => 'qty_transfer[]'));
                                }
                                ?>
                            </div>
                        </td>
                        <td><?php echo $row['u_name']; ?></td>
                        <td id="status_<?php echo $i; ?>">
                            <?php echo $statusLable; ?>
                        </td>
                        <td style="display: none;">
                            <?php
                            if ($qtyStock >= ($remainQty * $valueUomTo)) { // Conver Remain Qty as small qty
                                $btnSave = true;
                                ?>
                                <a href="#" style="display: none;" name="<?php echo $row['p_id'] . "|!|" . $row['qty'] . "|!|" . $row['u_id'] . "|!|" . $row['from_location'] . "|!|" . $row['to_location'] . "|!|" . $id . "|!| |!|" . $row['u_name'] . "|!|" . $row['detail_id']."|!|".$row['conversion']; ?>" lots-num="<?php echo $row['lots_number']; ?>" exp-date="<?php echo $row['expired_date']; ?>" class="btnTOReceive"><img src="<?php echo $this->webroot; ?>img/button/receiving.png" alt="" title="Receive" /></a>
                                <?php
                            } else {
                                echo TABLE_SHORTED;
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }// End Check Transfer Receive
            } // End while transfer order
        } else { // Else Num
            ?>
            <tr>
                <td class="first odd" colspan="7"><?php echo TABLE_LOADING; ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
</fieldset>
<?php
if ($btnSave) {
    ?>
    <div class="buttons">
        <button type="submit" class="positive btnReceiveTOAll">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveTrReceiveAll"><?php echo ACTION_RECEIVE_ALL; ?></span>
        </button>
    </div>
    <?php
}
?>

