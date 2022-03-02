<?php
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#DeliveryPickSlipForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveSales").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error == 1){
                    var msg = "<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>";
                    showDialogDN(msg, 1);
                }else if(result.error == 0){ // Success
                    createSysAct('Delivery Note', 'Add', 1, '');
                    var msg = '<div class="buttons"><button type="submit" class="positive printDeliveryNote"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_DELIVERY_NOTE; ?></span></button></div>';
                    showDialogDN(msg, 1);
                    $(".printDeliveryNote").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoicePickSlip/"+result.delivery_id,
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
                }else{
                    createSysAct('Delivery Note', 'Add', 2, result);
                    var msg = "<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>";
                    showDialogDN(msg, 1);
                }
            }
        });
        // Action Button Back
        $(".btnBackDelivery").click(function(event){
            event.preventDefault();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_BACK; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_YES; ?>': function() {
                        $(this).dialog("close");
                        backDelivery();
                    }
                }
            });
        });
        // Pick All
        $(".btnPickSlip").click(function(event){
            event.preventDefault();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_PICK_DELIVERY_AUTO; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_YES; ?>': function() {
                        $("#DeliveryPickSlipForm").submit();
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        });
        
        $(".btnPickProductDN").click(function(e){
            e.preventDefault();
            var salesOrderDetailId = $(this).attr("rel");
            var locationGroupId    = <?php echo $salesOrder['SalesOrder']['location_group_id']; ?>;
            var productId          = $(this).attr("product");
            var productCost        = $(this).attr("product-cost");
            var objRow             = $(this).closest("tr");
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProduct/"+salesOrderDetailId+"/"+locationGroupId,
                data:   "",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 900,
                        height: 600,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".dataTables_length").hide();
                            $(".dataTables_paginate").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkPickProduct[]']:checked").val() != undefined){
                                    if(parseFloat($("#total_order").text()) == 0){
                                        $(this).dialog("close");
                                        var parameter = "";
                                        parameter += "&data[sales_order_id]=<?php echo $salesOrder['SalesOrder']['id']; ?>";
                                        parameter += "&data[sales_order_detail_id]="+salesOrderDetailId;
                                        parameter += "&data[product_cost]="+productCost;
                                        parameter += "&data[calculate_cogs]=<?php echo $rowSettingUomDetail[1]; ?>";
                                        parameter += "&data[product_id]="+productId;
                                        $("input[name='chkPickProduct[]']:checked").each(function(){
                                            var lotsNum = $(this).attr("lots");
                                            var expired = $(this).attr("expired");
                                            var qtyPick = $(this).closest("tr").find(".qtyPick").val();
                                            var locationId = $(this).attr("location-id");
                                            parameter  += "&qty_pick[]="+qtyPick;
                                            parameter  += "&location_id[]="+locationId;
                                            parameter  += "&expired_date[]="+expired;
                                            parameter  += "&lots_number[]="+lotsNum;
                                        });
                                        $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProductSave",
                                            data: parameter,
                                            dataType: "json",
                                            position:'center',
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(msg){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                if(msg.success == 1){
                                                    objRow.find(".btnPickProductDN").hide();
                                                    objRow.find(".alertReady").show();
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                                    $("#dialog").dialog({
                                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                                        resizable: false,
                                                        modal: true,
                                                        position:'center',
                                                        width: 'auto',
                                                        height: 'auto',
                                                        open: function(event, ui){
                                                            $(".ui-dialog-buttonpane").show();
                                                        },
                                                        buttons: {
                                                            '<?php echo ACTION_CLOSE; ?>': function() {
                                                                $(this).dialog("close");
                                                            }
                                                        }
                                                    });
                                                } else if(msg.ready == 1){
                                                    $("#DeliveryPickSlipForm").parent().load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/pickSlip/<?php echo $id; ?>");
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                                    $("#dialog").dialog({
                                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                                        resizable: false,
                                                        modal: true,
                                                        position:'center',
                                                        width: 'auto',
                                                        height: 'auto',
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
                                            }
                                        });
                                    }else{
                                        $("#warningPickProduct").show();
                                    }
                                }else{
                                    $("#warningPickProduct").show();
                                }
                            }
                        }
                    });
                }
            });
        });
    });
    
    function showDialogDN(msg, isBack){
        $("#dialog").html('<b>'+msg+'</b>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            closeOnEscape: false,
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show(); 
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    if(isBack == 1){
                        backDelivery();
                    }
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function backDelivery(){
        oCache.iCacheLower = -1;
        oTableDelivery.fnDraw(false);
        var rightPanel = $("#DeliveryPickSlipForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide( "slide", { direction: "right" }, 500, function() {
            leftPanel.show();
            rightPanel.html('');
        });
    }
</script>
<?php echo $this->Form->create('Delivery', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $salesOrder['SalesOrder']['id']; ?>" name="data[sales_order_id]" />
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<br />
<fieldset>
    <legend><?php __(MENU_DELIVERY_MANAGEMENT); ?></legend>
    <div style="float: right; width:30px;">
        </div>
        <div>
            <table style="width: 100%;">
                <tr>
                    <th style="width: 8%;"><?php echo TABLE_INVOICE_CODE; ?>: </th>
                    <td style="width: 12%;"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
                    <th style="width: 8%;"><?php echo TABLE_INVOICE_DATE; ?>: </th>
                    <td style="width: 22%;"><?php echo dateShort($salesOrder['SalesOrder']['order_date']); ?></td>
                    <th style="width: 8%;"><?php echo TABLE_CUSTOMER_NUMBER; ?>: </th>
                    <td style="width: 12%;"><?php echo $salesOrder['Customer']['customer_code']; ?></td>
                    <th style="width: 12%;"><?php echo TABLE_CUSTOMER_NAME; ?>: </th>
                    <td><?php echo $salesOrder['Customer']['name']; ?></td>
                </tr>
                <tr>
                    <th><?php echo TABLE_LOCATION_GROUP; ?>: </th>
                    <td><?php echo $salesOrder['LocationGroup']['name']; ?></td>
                    <th style="vertical-align: top;"></th>
                    <td style="vertical-align: top;"></td>
                    <th><?php echo TABLE_CONTACT_NAME ?>: </th>
                    <td>
                        <select name="data[Delivery][customer_contact_id]" style="width: 95%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlCusCt = mysql_query("SELECT id, contact_name FROM customer_contacts WHERE customer_id = ".$salesOrder['SalesOrder']['customer_id']);
                            while($rowCusCt = mysql_fetch_array($sqlCusCt)){
                                $ctSelected = '';
                                if($rowCusCt['id'] == $salesOrder['SalesOrder']['customer_contact_id']){
                                    $ctSelected = 'selected="selected"';
                                }
                            ?>
                            <option value="<?php echo $rowCusCt['id']; ?>" <?php echo $ctSelected; ?>><?php echo $rowCusCt['contact_name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <th style="vertical-align: top;"></th>
                    <td style="vertical-align: top;"></td>
                </tr>
                <tr>
                    <th style="vertical-align: top;"><?php echo TABLE_NOTE; ?>: </th>
                    <td style="vertical-align: top;" colspan="3"><?php echo $this->Form->input('note', array('style' => 'width:300px;')); ?></td>
                    <th style="vertical-align: top;"><?php echo TABLE_SHIP_TO; ?>: </th>
                    <td style="vertical-align: top;" colspan="3"><?php echo $this->Form->input('ship_to', array('style' => 'width:300px;')); ?></td>
                </tr>
            </table>
        </div>
    <?php
            if (!empty($salesOrderDetails)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo TABLE_PRODUCT; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_SKU; ?></th>
                                <th><?php echo TABLE_BARCODE; ?></th>
                                <th><?php echo TABLE_NAME; ?></th>
                                <th><?php echo TABLE_QTY; ?></th>
                                <th><?php echo ACTION_ACTION; ?></th>
                            </tr>
                <?php
                $index = 0;
                foreach ($salesOrderDetails as $salesOrderDetail) {
                ?>
                    <tr class="rowListDN">
                        <td class="first" style="text-align: right;">
                            <?php echo++$index; ?>
                        </td>
                        <td><?php echo $salesOrderDetail['Product']['code']; ?></td>
                        <td><?php echo $salesOrderDetail['Product']['barcode']; ?></td>
                        <td><?php echo $salesOrderDetail['Product']['name']; ?></td>
                        <td>
                            <?php 
                            $small_label = "";
                            $small_uom   = $salesOrderDetail['Product']['small_val_uom'];
                            $sqlUomSm    = mysql_query("SELECT abbr FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = {$salesOrderDetail['Product']['price_uom_id']} AND is_small_uom = 1 LIMIT 1)");
                            if(mysql_num_rows($sqlUomSm)){
                                $rowUomSm    = mysql_fetch_array($sqlUomSm);
                                $small_label = $rowUomSm['abbr'];
                            }
                            $sqlUomMain = mysql_query("SELECT abbr FROM uoms WHERE id = {$salesOrderDetail['Product']['price_uom_id']};");
                            $rowUomMain = mysql_fetch_array($sqlUomMain);
                            $main_uom   = $rowUomMain['abbr'];
                            $totalDn    = ($salesOrderDetail['SalesOrderDetail']['qty'] + $salesOrderDetail['SalesOrderDetail']['qty_free']) * $salesOrderDetail['SalesOrderDetail']['conversion'];
                            echo showTotalQty($totalDn, $main_uom, $small_uom, $small_label);
                            ?>
                        </td>
                        <td>
                            <?php
                            $sqlCheckDn = mysql_query("SELECT id FROM delivery_details WHERE sales_order_detail_id = ".$salesOrderDetail['SalesOrderDetail']['id']);
                            if(!mysql_num_rows($sqlCheckDn)){
                            ?>
                                <a href="#" class="btnPickProductDN" product-cost="<?php echo $salesOrderDetail['Product']['unit_cost']; ?>" product="<?php echo $salesOrderDetail['SalesOrderDetail']['product_id']; ?>" rel="<?php echo $salesOrderDetail['SalesOrderDetail']['id']; ?>"><img alt="Pick Product" onmouseover="Tip('Pick Product')" src="<?php echo $this->webroot; ?>img/button/hand.png" /></a>
                                <img alt="Pick Ready" class="alertReady" onmouseover="Tip('Pick Ready')" style="display: none;" src="<?php echo $this->webroot; ?>img/button/active.png" />
                            <?php
                            }else{
                            ?>
                                <img alt="Pick Ready" class="alertReady" onmouseover="Tip('Pick Ready')" src="<?php echo $this->webroot; ?>img/button/active.png" />
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </fieldset>
    </div>
    <?php
            }
    ?>
</fieldset>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="#" class="positive btnBackDelivery">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnPickSlip" >
            <img src="<?php echo $this->webroot; ?>img/button/hand.png" alt=""/>
            <span class="txtSaveSales"><?php echo ACTION_PICK_ALL; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>