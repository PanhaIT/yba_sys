<?php
$allowLots    = false;
$allowExpired = false;
$priceDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    }
}
include("includes/function.php");
$this->element('check_access');
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowPrintInvoice = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$rand = rand();
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#TransferOrderAmountUs, #TransferOrderAmountOther, #TransferOrderDiscountUs, #TransferOrderDiscountOther, .paidTransferOrder").unbind('keyup').unbind('click');
        
        $("#TransferOrderAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#TransferOrderAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $(".TransferOrderAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#TransferOrderPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Sales Invoice', 'Aging', 2, result.responseText);
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
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        $(".btnBackTransferOrder").click();
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
                createSysAct('Sales Invoice', 'Aging', 1, '');
                $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceipt<?php echo $rand; ?>" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintReceipt"><?php echo ACTION_RECEIPT; ?></span></button></div> ');
                $(".printReceipt<?php echo $rand; ?>").click(function(){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceipt/"+result.to_id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printReceiptResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printReceiptResult);
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
                    position:['center',100],
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        $(".btnBackTransferOrder").click();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        $(".printInvoiceReceiptAging").unbind("click").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceiptCurrent/"+$(this).attr("rel"),
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

        $(".btnPrintInvoiceAging").unbind("click").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+$(this).attr("rel"),
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
        
        $(".btnDeleteReceiptInvoiceAging").unbind("click").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var saleId = $(this).attr('name');
            voidReceiptSO(id, name, saleId);
        });
        
        $(".paidInvoiceAging").unbind("click").click(function(){
            var formName = "#TransferOrderAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if($("#TransferOrderBalanceUs").val() == <?php echo $transferOrder['TransferOrder']['balance']; ?>){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please paid first.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
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
                    return false
                }else{
                    return true;
                }
            }
        });

        var now = new Date();
        $("#TransferOrderPayDate").val(now.toString('dd/MM/yyyy'));
        $("#TransferOrderPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($transferOrder['TransferOrder']['order_date'])); ?>'
        }).unbind("blur");

        $('.TransferOrderAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($transferOrder['TransferOrder']['order_date'])); ?>'
        }).unbind("blur");
        
        $("#TransferOrderAmountUs, #TransferOrderAmountOther, #TransferOrderDiscountUs, #TransferOrderDiscountOther").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $("#TransferOrderAmountUs, #TransferOrderAmountOther, #TransferOrderDiscountUs, #TransferOrderDiscountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });
        
        $("#TransferOrderAmountUs, #TransferOrderDiscountUs").keyup(function(){
            calculateReceiptSoBalance();
        });
        
        $("#TransferOrderAmountOther, #TransferOrderDiscountOther").keyup(function(){
            if($("#exchangeRateSales").find("option:selected").val() == ""){
                $("#TransferOrderAmountOther, #TransferOrderDiscountOther").val(0);
            }
            calculateReceiptSoBalance();
        });
        
        $(".btnBackTransferOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTOTable.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        
        $("#exchangeRateSales").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#TransferOrderAmountOther").removeAttr("readonly");
            } else {
                $("#TransferOrderAmountOther").val(0);
                $("#TransferOrderAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbol").html(symbol);
            $("#salesOrderExchangeRateId").val(exRateId);
            calculateReceiptSoBalance();
        });
    });

    function calculateReceiptSoBalance(){
        var totalAmount   = replaceNum('<?php echo $transferOrder['TransferOrder']['balance']; ?>');
        var amount        = replaceNum($("#TransferOrderAmountUs").val());
        var amountOther   = replaceNum($("#TransferOrderAmountOther").val());
        var Discount      = replaceNum($("#TransferOrderDiscountUs").val());
        var DiscountOther = replaceNum($("#TransferOrderDiscountOther").val());

        // Obj
        var balance      = $("#TransferOrderBalanceUs");
        var balanceOther = $("#TransferOrderBalanceOther");

        var totalPaid = amount + convertToMainCurrency(amountOther) + Discount + convertToMainCurrency(DiscountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#TransferOrderAmountUs").val(totalAmount);
            $("#TransferOrderAmountOther").val(0);
            $("#TransferOrderDiscountUs").val(0);
            $("#TransferOrderDiscountOther").val(0);
        }
        var totalBalance = totalAmount - totalPaid;
        if(totalBalance > 0){
            $(".DivTransferOrderAging").show();
            $("#spanTransferOrderAging").html("*");
            $("#TransferOrderAging").addClass("validate[required]");
        }else{
            $(".DivTransferOrderAging").hide();
            $("#spanTransferOrderAging").html("");
            $("#TransferOrderAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(<?php echo $priceDecimal; ?>));
        balanceOther.val(convertToOtherCurrency(totalBalance).toFixed(<?php echo $priceDecimal; ?>));
        $("#TransferOrderBalanceUs, #TransferOrderBalanceOther").priceFormat({
            centsLimit: <?php echo $priceDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrency(val){
        var exchangeRate  = replaceNum($("#exchangeRateSales").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrency(val){
        var exchangeRate = replaceNum($("#exchangeRateSales").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) * exchangeRate);
        }
        return amountConvert;
    }
    
    function voidReceiptSO(id, name, saleId){
        $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position: 'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_VOID; ?>': function() {
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/transfer_orders'; ?>/voidReceipt/" + id,
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_INVALID; ?>'){
                                createSysAct('Sales Invoice', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Sales Invoice', 'Void Receipt', 1, '');
                                // alert message
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            $(".btnBackTransferOrder").click();
                            $("#dialog").dialog({
                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                position: 'center',
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
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTransferOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('TransferOrder'); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->hidden('company_id', array('value' => $transferOrder['TransferOrder']['company_id'])); ?>
<fieldset>
    <legend><?php __(MENU_TRANSFER_ORDER_MANAGEMENT_INFO); ?></legend>
        <div style="float: right; width:30px;">
        <?php
            if ($allowPrintInvoice) {
                echo "<a href='#' class='btnPrintInvoiceAging' rel='{$transferOrder['TransferOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . ' ' . SALES_ORDER_INVOICE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
            }
        ?>
        </div>
        <div>
            <table cellpadding="5" style="width: 100%;">
                <tr>
                    <td style="font-size: 12px; width: 9%;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="font-size: 12px; width: 20%;">
                        <div class="inputContainer">
                            <?php echo $this->data['Branch']['name']; ?>
                        </div>
                    </td>
                    <td style="font-size: 12px; width: 9%;"></td>
                    <td style="font-size: 12px; width: 20%;"></td>
                    <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_NUMBER; ?> :</td>
                    <td style="font-size: 12px; width: 20%;">
                        <div class="inputContainer">
                            <?php echo $this->data['TransferOrder']['to_code']; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_FROM_WAREHOUSE; ?>  :</td>
                    <td style="font-size: 12px;">
                        <div class="inputContainer">
                            <?php echo $fromLocationGroups['LocationGroup']['name']; ?>
                        </div>
                    </td>        
                    <td style="font-size: 12px;"><?php echo TABLE_TO_WAREHOUSE; ?> :</td>
                    <td style="font-size: 12px;">
                        <div class="inputContainer">
                            <?php echo $toLocationGroups['LocationGroup']['name']; ?>
                        </div>
                    </td>
                    <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_DATE; ?> :</td>
                    <td style="font-size: 12px;">
                        <div class="inputContainer">
                            <?php echo dateShort($this->data['TransferOrder']['order_date']); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="5">
                        <div class="inputContainer">
                            <?php echo nl2br($this->data['TransferOrder']['note']); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <?php
    if (!empty($transferOrderDetails)) {
        $totalCol = 8;
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                    <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                    <th>AVG Cost $</th>
                    <th>Total $</th>
                </tr>
                <?php
                $index = 0;
                $totalCost = 0;
                $subTotal = 0;
                foreach ($transferOrderDetails as $transferOrderDetail) {
                    $unit_cost = number_format($transferOrderDetail['TransferOrderDetail']['unit_cost'], 3);
                    $subTotal  = $transferOrderDetail['TransferOrderDetail']['total_cost'];
                    $totalCost += $subTotal;
                ?>
                <tr>
                    <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                    <td><?php echo $transferOrderDetail['Product']['code']; ?></td>
                    <td><?php echo $transferOrderDetail['Product']['name']; ?></td>
                    <td<?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $transferOrderDetail['TransferOrderDetail']['lots_number']; ?></td>
                    <td<?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <?php 
                        if($transferOrderDetail['TransferOrderDetail']['expired_date'] != '' && $transferOrderDetail['TransferOrderDetail']['expired_date'] != '0000-00-00'){
                            echo dateShort($transferOrderDetail['TransferOrderDetail']['expired_date']);
                        }
                        ?>
                    </td>
                    <td style="text-align: right"><?php echo $transferOrderDetail['TransferOrderDetail']['qty']; ?></td>
                    <td>
                        <?php 
                            $queryUom = mysql_query("SELECT name FROM uoms WHERE id = '".$transferOrderDetail['TransferOrderDetail']['qty_uom_id']."'");
                            if(mysql_num_rows($queryUom)){
                                $dataUom = mysql_fetch_array($queryUom);
                                echo $dataUom['name'];
                            }
                        ?>
                    </td>
                    <td style="text-align: right"><?php echo $unit_cost; ?></td>
                    <td style="text-align: right"><?php echo number_format($subTotal, $priceDecimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalCost, $priceDecimal); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
    }
    ?>
    <div>
        <table cellpadding="5" cellspacing="0" style="margin-top: 10px; width: 100%;">
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($transferOrder['TransferOrder']['total_cost'], $priceDecimal); ?> $</td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($transferOrderReceipts)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo GENERAL_PAID; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 90px;"><?php echo TABLE_DATE ?></th>
                    <th style="width: 90px;"><?php echo TABLE_CODE ?></th>
                    <th style="width: 150px;"><?php echo GENERAL_EXCHANGE_RATE ?></th>
                    <th style="width: 100px;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th colspan="2"><?php echo GENERAL_PAID; ?></th>
                    <th colspan="2"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th style="width: 100px;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width:10%;"></th>
                </tr>
                <?php
                $index = 0;
                $leght = count($transferOrderReceipts);
                foreach ($transferOrderReceipts as $transferOrderReceipt) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo date("d/m/Y", strtotime($transferOrderReceipt['TransferOrderReceipt']['pay_date'])); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo $html->link($transferOrderReceipt['TransferOrderReceipt']['receipt_code'], array("action" => "#"), array("class" => "printInvoiceReceiptAging", "rel" => $transferOrderReceipt['TransferOrderReceipt']['id']));
                        } else {
                            $transferOrderReceipt['TransferOrderReceipt']['receipt_code'];
                        }
                        ?>
                    </td>
                    <td style="text-align: right;">
                        1 $ = 
                        <?php 
                            $queryEx = mysql_query("SELECT rate_to_sell FROM exchange_rates WHERE id = '".$transferOrderReceipt['TransferOrderReceipt']['exchange_rate_id']."'");
                            if(mysql_num_rows($queryEx)){
                                $dataEx = mysql_fetch_array($queryEx);
                                echo number_format($dataEx['rate_to_sell'], 2); 
                            }
                        ?> $
                    </td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['total_amount'], $priceDecimal); ?> $</td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['amount_us'], $priceDecimal); ?> $</td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['amount_other'], $priceDecimal); ?> $</td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['discount_us'], $priceDecimal); ?> $</td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['discount_other'], $priceDecimal); ?> $</td>
                    <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['balance'], $priceDecimal); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='printInvoiceReceiptAging' rel='{$transferOrderReceipt['TransferOrderReceipt']['id']}'  ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                        }
                        if($index == $leght){
                            echo "&nbsp; <a href='{$transferOrderReceipt['TransferOrderReceipt']['receipt_code']}' name='{$transferOrderReceipt['TransferOrderReceipt']['transfer_order_id']}' class='btnDeleteReceiptInvoiceAging' rel='{$transferOrderReceipt['TransferOrderReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
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
    if($transferOrder['TransferOrder']['balance'] == 0){
        $styleDisplay = " style='display:none'";
    }else{
        $styleDisplay = "";
    }
    ?>
        <div<?php echo $styleDisplay; ?>>
            <div style="float: left;">
                <table style="width: 250px;">
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="data[TransferOrder][exchange_rate_id]" id="salesOrderExchangeRateId" />
                            <?php
                            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$transferOrder['TransferOrder']['branch_id']);
                            if(mysql_num_rows($sqlCurrency)){
                            ?>
                            <table class="table" cellspacing="0" >
                                <thead>
                                    <tr>
                                        <th class="first" style="width:100%;" colspan="2"><?php echo MENU_EXCHANGE_RATE_LIST; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while($rowCurrency = mysql_fetch_array($sqlCurrency)){
                                    ?>
                                    <tr>
                                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 $ =</td>
                                        <td style="font-size: 12px;"><?php echo number_format($rowCurrency['rate_to_sell'], 9); ?> <?php echo $rowCurrency['symbol']; ?></td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right;">
                <br />
                <table>
                    <tr>
                        <td><label for="TransferOrderPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('pay_date', array('style' => 'text-align:left; width: 120px;', 'readonly' => true)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="TransferOrderAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                        <td>
                        <?php
                        echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                        echo $this->Form->hidden('total_cost', array('value' => $transferOrder['TransferOrder']['balance']));
                        ?> ($)
                    </td>
                    <tr>
                        <td><label for="TransferOrderDiscountUs"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                        <td>
                            <?php echo $this->Form->text('discount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                            ($)
                        </td>
                    </tr>
                </tr>
                <tr style="display: none;">
                    <td>
                        <select name="data[TransferOrder][currency_center_id]" id="exchangeRateSales" style="width: 150px;">
                            <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$transferOrder['TransferOrder']['branch_id']);
                            while($rowCurSelect = mysql_fetch_array($sqlCurSelect)){
                            ?>
                            <option value="<?php echo $rowCurSelect['id']; ?>" symbol="<?php echo $rowCurSelect['symbol']; ?>" exrate="<?php echo $rowCurSelect['exchange_rate_id']; ?>" ratesale="<?php echo $rowCurSelect['rate_to_sell']; ?>"><?php echo $rowCurSelect['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <?php echo $this->Form->text('amount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0, 'readonly' => true)); ?>
                        <span class="paidOtherCurrencySymbol"></span>
                    </td>
                </tr>
                <tr>
                    <td><label for="TransferOrderDiscountOther"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                    <td>
                        <?php echo $this->Form->text('discount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                        <span class="paidOtherCurrencySymbol"></span>
                    </td>
                </tr>
                <tr class="DivTransferOrderAging">
                    <td style="vertical-align: top"><label for="TransferOrderAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanTransferOrderAging">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('aging', array('id' => 'TransferOrderAging' . $rand, 'class' => 'TransferOrderAging', 'style' => 'width: 120px;')); ?>
                        </div>
                        <div style="clear:both;"></div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
        <div style="float: right;">
            <table align="center" style="width:400px;" class="table" cellspacing="0" >
                <tr>
                    <th class="first" colspan="2">
                        <?php echo GENERAL_BALANCE; ?>
                        </th>
                    </tr>
                    <tr>
                        <td class="first" >
                            $
                        </td>
                        <td>
                            <span class="paidOtherCurrencySymbol"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="first"> 
                            <?php echo $this->Form->text('balance_us', array('class' => 'float', 'style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($transferOrder['TransferOrder']['balance'], $priceDecimal))); ?> 
                        </td>
                        <td> 
                            <input type="text" name="data[TransferOrder][balance_other]" id="TransferOrderBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    </fieldset>
    <br />
    <div class="buttons" <?php echo $styleDisplay; ?>>
        <button type="submit" class="positive paidTransferOrder" >
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>