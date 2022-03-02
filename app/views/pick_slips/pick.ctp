<?php
include("includes/function.php");
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".savePickSlip").unbind("click").click(function(){
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_SAVE; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                pprsition:'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_YES; ?>': function() {
                        $("#PickSlipPickForm").submit();
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        });

        $("#PickSlipPickForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePickSlip").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".savePickSlip").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Pick Slip', 'Pick', 2, result.responseText);
                $(".btnBackPickSlip").click();
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $("meta[http-equiv='refresh']").attr('content','0');
                            $(this).dialog("close");
                        }
                    }
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPickSlip").click();
                createSysAct('Pick Slip', 'Pick', 1, '');
                var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoicePickSlip/";
                if(result.error == "1"){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                } else {
                    // $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive reprintInvoiceSales" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtReprintInvoiceSales"><?php echo ACTION_PRINT_DELIVERY_NOTE; ?></span></button></div>');
                }
                $(".reprintInvoiceSales").click(function(){
                    $.ajax({
                        type: "POST",
                        url: url+result.id,
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

                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
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
        });
        // Action Button Back
        $(".btnBackPickSlip").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePickSlip.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
    });
</script>
<br />
<?php echo $this->Form->create('PickSlip', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" name="data[id]" value="<?php echo $delivery['Delivery']['id']; ?>" />
<fieldset>
    <legend><?php __(MENU_PICK_SLIP); ?></legend>
    <div style="float: right; width:30px;">
        </div>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $delivery['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $salesOrder['LocationGroup']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($salesOrder['SalesOrder']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_BRANCH; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $delivery['Branch']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"></td>
                    <td style="font-size: 12px;"></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_SHIP_TO ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($delivery['Delivery']['ship_to']); ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($salesOrder['SalesOrder']['memo']); ?></td>
                </tr>
            </table>
        </div>
    <?php
            if (!empty($deliveryDetails)) {
    ?>
        <div>
            <fieldset>
                <legend><?php echo TABLE_PRODUCT; ?></legend>
                <table class="table" >
                    <tr>
                        <th class="first"><?php echo TABLE_NO; ?></th>
                        <th><?php echo TABLE_BARCODE; ?></th>
                        <th><?php echo TABLE_NAME; ?></th>
                        <th><?php echo TABLE_TOTAL_QTY; ?></th>
                        <!-- <th><?php echo TABLE_F_O_C; ?></th> -->
                        <th><?php echo TABLE_UOM; ?></th>
                        <th style="<?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                        <th style="<?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    </tr>
                <?php
                $index = 0;
                $uom   = '';
                foreach ($deliveryDetails as $deliveryDetail) {
                    $sqlUom = mysql_query("SELECT name,abbr FROM uoms WHERE is_active=1 AND id='".$deliveryDetail['Product']['price_uom_id']."' ");
                    $rowUom = mysql_fetch_array($sqlUom);
                    $uom    = $rowUom['name'];
                ?>
                <tr>
                    <td class="first" style="text-align: right;">
                        <?php echo ++$index; ?>
                    </td>
                    <td><?php echo $deliveryDetail['Product']['barcode']; ?></td>
                    <td><?php echo $deliveryDetail['Product']['name']; ?></td>
                    <td><?php echo $deliveryDetail['DeliveryDetail']['total_qty']; ?></td>
                    <!-- <td><?php //echo $deliveryDetail['DeliveryDetail']['qty_free']; ?></td> -->
                    <td><?php echo $uom; ?></td>
                    <td style="text-align: right; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo $deliveryDetail['DeliveryDetail']['lots_number']!='0'?$deliveryDetail['DeliveryDetail']['lots_number']:''; ?></td>
                    <td style="text-align: right; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php if($deliveryDetail['DeliveryDetail']['expired_date'] != '0000-00-00'){ echo dateShort($deliveryDetail['DeliveryDetail']['expired_date']); } ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </fieldset>
        <br/>
    </div>
    <?php
    }
    ?>
</fieldset>
<br />
<div class="footerSaveDelivery" style="">
    <div style="float: left; width: 26%;">
        <div class="buttons">
            <a href="#" class="positive btnBackPickSlip">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive savePickSlip" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSavePickSlip"><?php echo ACTION_PICK; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>