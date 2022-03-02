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
        $("#SalesOrderAmountUs, #SalesOrderAmountOther, #SalesOrderDiscountUs, #SalesOrderDiscountOther, .paidSalesOrder").unbind('keyup').unbind('click');
        
        $("#SalesOrderAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#SalesOrderAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $(".SalesOrderAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#SalesOrderPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
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
                        $(".btnBackSalesOrder").click();
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
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceipt/"+result.sr_id,
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
                        $(".btnBackSalesOrder").click();
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
            var formName = "#SalesOrderAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if($("#SalesOrderBalanceUs").val() == <?php echo $salesOrder['SalesOrder']['balance']; ?>){
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
        $("#SalesOrderPayDate").val(now.toString('dd/MM/yyyy'));
        $("#SalesOrderPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($salesOrder['SalesOrder']['order_date'])); ?>'
        }).unbind("blur");

        $('.SalesOrderAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($salesOrder['SalesOrder']['order_date'])); ?>'
        }).unbind("blur");
        
        $("#SalesOrderAmountUs, #SalesOrderAmountOther, #SalesOrderDiscountUs, #SalesOrderDiscountOther").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $("#SalesOrderAmountUs, #SalesOrderAmountOther, #SalesOrderDiscountUs, #SalesOrderDiscountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });
        
        $("#SalesOrderAmountUs, #SalesOrderDiscountUs").keyup(function(){
            calculateReceiptSoBalance();
        });
        
        $("#SalesOrderAmountOther, #SalesOrderDiscountOther").keyup(function(){
            if($("#exchangeRateSales").find("option:selected").val() == ""){
                $("#SalesOrderAmountOther, #SalesOrderDiscountOther").val(0);
            }
            calculateReceiptSoBalance();
        });
        
        $(".btnBackSalesOrder").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                oCache.iCacheLower = -1;
                oTableSalesOrder.fnDraw(false);
                leftPanel.show();
                rightPanel.html('');
            });
        });
        
        $("#exchangeRateSales").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#SalesOrderAmountOther").removeAttr("readonly");
            } else {
                $("#SalesOrderAmountOther").val(0);
                $("#SalesOrderAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbol").html(symbol);
            $("#salesOrderExchangeRateId").val(exRateId);
            calculateReceiptSoBalance();
        });
    });

    function calculateReceiptSoBalance(){
        var totalAmount   = replaceNum('<?php echo $salesOrder['SalesOrder']['balance']; ?>');
        var amount        = replaceNum($("#SalesOrderAmountUs").val());
        var amountOther   = replaceNum($("#SalesOrderAmountOther").val());
        var Discount      = replaceNum($("#SalesOrderDiscountUs").val());
        var DiscountOther = replaceNum($("#SalesOrderDiscountOther").val());

        // Obj
        var balance      = $("#SalesOrderBalanceUs");
        var balanceOther = $("#SalesOrderBalanceOther");

        var totalPaid = amount + convertToMainCurrency(amountOther) + Discount + convertToMainCurrency(DiscountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#SalesOrderAmountUs").val(totalAmount);
            $("#SalesOrderAmountOther").val(0);
            $("#SalesOrderDiscountUs").val(0);
            $("#SalesOrderDiscountOther").val(0);
        }
        var totalBalance = totalAmount - totalPaid;
        if(totalBalance > 0){
            $(".DivSalesOrderAging").show();
            $("#spanSalesOrderAging").html("*");
            $("#SalesOrderAging").addClass("validate[required]");
        }else{
            $(".DivSalesOrderAging").hide();
            $("#spanSalesOrderAging").html("");
            $("#SalesOrderAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(<?php echo $priceDecimal; ?>));
        balanceOther.val(convertToOtherCurrency(totalBalance).toFixed(<?php echo $priceDecimal; ?>));
        $("#SalesOrderBalanceUs, #SalesOrderBalanceOther").priceFormat({
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
                        url: "<?php echo $this->base . '/sales_orders'; ?>/voidReceipt/" + id,
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
                            $(".btnBackSalesOrder").click();
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
        <a href="" class="positive btnBackSalesOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('SalesOrder'); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->hidden('company_id', array('value' => $salesOrder['SalesOrder']['company_id'])); ?>
<?php echo $this->Form->hidden('location_id', array('value' => $salesOrder['SalesOrder']['location_id'])); ?>
<fieldset>
    <legend><?php __(MENU_SALES_ORDER_MANAGEMENT_INFO); ?></legend>
        <div style="float: right; width:30px;">
        <?php
            if ($allowPrintInvoice) {
                echo "<a href='#' class='btnPrintInvoiceAging' rel='{$salesOrder['SalesOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . ' ' . SALES_ORDER_INVOICE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
            }
        ?>
        </div>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"></td>
                    <td style="width: 18%; font-size: 12px;"></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['SalesOrder']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"></td>
                    <td style="font-size: 12px;"></td>
                    <td style="font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['so_code']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_CUSTOMER_PO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo $this->data['SalesOrder']['customer_po_number']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['SalesOrder']['memo']); ?></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                </tr>
            </table>
        </div>
    <?php
    if (!empty($salesOrderDetails)) {
        $totalCol = 11;
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
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_F_O_C; ?></th>
                    <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                $subTotal = 0;
                foreach ($salesOrderDetails as $salesOrderDetail) {
                    $unit_price = number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 3);
                    $discount = $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                    $subTotal = $salesOrderDetail['SalesOrderDetail']['total_price'] - $discount;
                    $totalDiscount += $discount;
                    $totalPrice += $subTotal;
                ?>
                <tr>
                    <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                    <td><?php echo $salesOrderDetail['Product']['code']; ?></td>
                    <td><?php echo $salesOrderDetail['Product']['name']; ?></td>
                    <td<?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $salesOrderDetail['SalesOrderDetail']['lots_number']; ?></td>
                    <td<?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <?php 
                        if($salesOrderDetail['SalesOrderDetail']['expired_date'] != '' && $salesOrderDetail['SalesOrderDetail']['expired_date'] != '0000-00-00'){
                            echo dateShort($salesOrderDetail['SalesOrderDetail']['expired_date']);
                        }
                        ?>
                    </td>
                    <td><?php echo $salesOrderDetail['SalesOrderDetail']['note']; ?></td>
                    <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty']; ?></td>
                    <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty_free']; ?></td>
                    <td><?php echo $salesOrderDetail['Uom']['name']; ?></td>
                    <td style="text-align: right"><?php echo $unit_price; ?></td>
                    <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                    <td style="text-align: right"><?php echo number_format($subTotal, $priceDecimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
    }
    if (!empty($salesOrderServices)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_SERVICE; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_QTY; ?></th>
                    <th><?php echo TABLE_F_O_C; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                $totalDiscount = 0;
                $subTotal = 0;
                foreach ($salesOrderServices as $salesOrderService) {
                    $unit_price = number_format($salesOrderService['SalesOrderService']['unit_price'], 3);
                    $discount = $salesOrderService['SalesOrderService']['discount_amount'];
                    $totalDiscount += $discount;
                    $subTotal = $salesOrderService['SalesOrderService']['total_price'] - $discount;
                    $totalPrice += $subTotal;
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo $salesOrderService['Service']['name']; ?></td>
                    <td><?php echo $salesOrderService['SalesOrderService']['note']; ?></td>
                    <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty']; ?></td>
                    <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty_free']; ?></td>
                    <td style="text-align: right;"><?php echo $unit_price; ?></td>
                    <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotal, $priceDecimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal); ?></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_SUB_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            if ($salesOrder['SalesOrder']['discount'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($salesOrder['SalesOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($salesOrder['SalesOrder']['discount_percent'],  2); ?>%)<?php } ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['discount'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            ?>
            <?php
            if($salesOrder['SalesOrder']['total_vat'] > 0){
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($salesOrder['SalesOrder']['vat_percent'], $priceDecimal); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_vat'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'] + $salesOrder['SalesOrder']['total_vat'] - $salesOrder['SalesOrder']['discount'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_DEPOSIT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['total_deposit'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($salesOrderWithCms)) {
    ?>
    <div>
        <fieldset>
            <legend>Apply With Sales Return</legend>
                <table class="table">
                    <tr>
                        <th class="first"></th>
                        <th><?php echo TABLE_APPLY_DATE; ?></th>
                        <th><?php echo TABLE_CREDIT_MEMO_CODE; ?></th>
                        <th><?php echo TABLE_CUSTOMER; ?></th>
                        <th><?php echo TABLE_TOTAL_AMOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo GENERAL_PAID; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    </tr>
                    <?php
                    $index = 0;

                    foreach ($salesOrderWithCms as $cmWsale) {
                        $totalPrice += $cmWsale['CreditMemoWithSale']['total_price'];
                    ?>
                    <tr>
                        <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo dateShort($cmWsale['CreditMemoWithSale']['apply_date']); ?></td>
                        <td style="text-align: left;"><?php echo $cmWsale['CreditMemo']['cm_code']; ?></td>
                        <td style="text-align: left;">
                        <?php 
                        $sql = mysql_query("SELECT * FROM customers WHERE id = ".$cmWsale['SalesOrder']['customer_id']." AND is_active = 1 LIMIT 1");
                        $customer = mysql_fetch_array($sql);
                        echo $customer['customer_code']." - ".$customer['name'];
                        ?>
                        </td>
                        <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemo']['total_amount'] - ($cmWsale['CreditMemo']['discount']) + ($cmWsale['CreditMemo']['total_vat']), $priceDecimal); ?></td>
                        <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemoWithSale']['total_price'], $priceDecimal); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
            </table>
        </fieldset>
    </div>
    <?php
    }
    if (!empty($salesOrderReceipts)) {
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
                $leght = count($salesOrderReceipts);
                foreach ($salesOrderReceipts as $salesOrderReceipt) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo date("d/m/Y", strtotime($salesOrderReceipt['SalesOrderReceipt']['pay_date'])); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo $html->link($salesOrderReceipt['SalesOrderReceipt']['receipt_code'], array("action" => "#"), array("class" => "printInvoiceReceiptAging", "rel" => $salesOrderReceipt['SalesOrderReceipt']['id']));
                        } else {
                            $salesOrderReceipt['SalesOrderReceipt']['receipt_code'];
                        }
                        ?>
                    </td>
                    <td style="text-align: right;">1 <?php echo $salesOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($salesOrderReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['total_amount'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_us'], $priceDecimal); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_other'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['balance'], $priceDecimal); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='btnPrintReceipt$rand' rel='{$salesOrderReceipt['SalesOrderReceipt']['id']}'  ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                        }
                        if($index == $leght){
                            echo "&nbsp; <a href='{$salesOrderReceipt['SalesOrderReceipt']['receipt_code']}' name='{$salesOrderReceipt['SalesOrderReceipt']['sales_order_id']}' class='btnDeleteReceiptInvoiceAging' rel='{$salesOrderReceipt['SalesOrderReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
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
    if($salesOrder['SalesOrder']['balance'] == 0){
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
                            <input type="hidden" name="data[SalesOrder][exchange_rate_id]" id="salesOrderExchangeRateId" />
                            <?php
                            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$salesOrder['SalesOrder']['branch_id']);
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
                                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $salesOrder['CurrencyCenter']['symbol']; ?> =</td>
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
                        <td><label for="SalesOrderPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('pay_date', array('style' => 'text-align:left; width: 120px;', 'readonly' => true)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="SalesOrderAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                        <td>
                        <?php
                        echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                        echo $this->Form->hidden('total_amount', array('value' => $salesOrder['SalesOrder']['balance']));
                        ?> (<?php echo $salesOrder['CurrencyCenter']['symbol']; ?>)
                    </td>
                    <tr>
                        <td><label for="SalesOrderDiscountUs"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                        <td>
                            <?php echo $this->Form->text('discount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                            (<?php echo $salesOrder['CurrencyCenter']['symbol']; ?>)
                        </td>
                    </tr>
                </tr>
                <tr style="display: none;">
                    <td>
                        <select name="data[SalesOrder][currency_center_id]" id="exchangeRateSales" style="width: 150px;">
                            <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$salesOrder['SalesOrder']['branch_id']);
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
                    <td><label for="SalesOrderDiscountOther"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                    <td>
                        <?php echo $this->Form->text('discount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                        <span class="paidOtherCurrencySymbol"></span>
                    </td>
                </tr>
                <tr class="DivSalesOrderAging">
                    <td style="vertical-align: top"><label for="SalesOrderAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanSalesOrderAging">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('aging', array('id' => 'SalesOrderAging' . $rand, 'class' => 'SalesOrderAging', 'style' => 'width: 120px;')); ?>
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
                            <?php echo $salesOrder['CurrencyCenter']['symbol']; ?>
                        </td>
                        <td>
                            <span class="paidOtherCurrencySymbol"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="first"> 
                            <?php echo $this->Form->text('balance_us', array('class' => 'float', 'style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($salesOrder['SalesOrder']['balance'], $priceDecimal))); ?> 
                        </td>
                        <td> 
                            <input type="text" name="data[SalesOrder][balance_other]" id="SalesOrderBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    </fieldset>
    <br />
    <div class="buttons" <?php echo $styleDisplay; ?>>
        <button type="submit" class="positive paidSalesOrder" >
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>