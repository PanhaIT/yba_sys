<?php
$allowLots    = false;
$allowExpired = false;
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 39) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 39){
        $costDecimal = $rowSetting['value'];
    }
}

include("includes/function.php");
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
$this->element('check_access');
$rand = rand();
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowPrintInvoice = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: <?php echo $costDecimal ?>, aSep: ','});
        $("#PurchaseOrderAmountUs, #PurchaseOrderAmountOther, .paidPurchaseBill").unbind('keyup').unbind('click');
        
        $("#PurchaseOrderAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PurchaseOrderAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $("#PurchaseOrderAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#PurchaseOrderPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Purchase Bill', 'Aging', 2, result.responseText);
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
                        $(".btnBackSalesOrder").dblclick();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $("meta[http-equiv='refresh']").attr('content','0');
                            $(this).dialog("close");
                        }
                    }
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Purchase Bill', 'Aging', 1, '');
                $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceipt" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_PURCHASE_BILL_RECEIPT; ?></span></button></div> ');
                $(".printReceipt").click(function(){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceiptOne/"+result.sr_id,
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
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:['center'],
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        var rightPanel=$(".btnBackPurchaseOrder").parent().parent().parent();
                        var leftPanel=rightPanel.parent().find(".leftPanel");
                        rightPanel.hide("slide", { direction: "right" }, 500, function(){
                            leftPanel.show();
                            rightPanel.html("");
                        });
                        leftPanel.html("<?php echo ACTION_LOADING; ?>");
                        leftPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/");
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        $(".btnPrintReceipt").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceipt/"+$(this).attr("rel"),
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
        
        $(".btnPrintReceiptOne").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceiptOne/"+$(this).attr("rel"),
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

        $(".btnPrintInvoice").click(function(event){
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
        
        $(".paidPayBill").click(function(){
            var formName = "#PurchaseOrderAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if(parseFloat(replaceNum($("#PurchaseOrderBalanceUs").val())) == <?php echo $purchaseOrder['PurchaseOrder']['balance']; ?>){
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
        $("#PurchaseOrderPayDate").val(now.toString('dd/MM/yyyy'));
        $("#PurchaseOrderPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($purchaseOrder['PurchaseOrder']['order_date'])); ?>'
        }).unbind("blur");

        $('#PurchaseOrderAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($purchaseOrder['PurchaseOrder']['order_date'])); ?>'
        }).unbind("blur");
        
        $(".btnDeleteRp<?php echo $rand; ?>").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var poId = $(this).attr('name');
            voidReceiptPB(id, name, poId);
        });
        
        $("#PurchaseOrderAmountUs, #PurchaseOrderAmountOther").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#PurchaseOrderAmountUs, #PurchaseOrderAmountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });

        $("#PurchaseOrderAmountUs, #PurchaseOrderAmountOther").keyup(function(){
            calculateReceiptPbBalance();
        });

        // hide coa that not belong to the company
        $(".pb_aging_coa_id option").show();
        $(".pb_aging_coa_id option").each(function(){
            if($(this).attr("company_id")){
                var companyId=$(this).attr("company_id").split(",");
                if(companyId.indexOf("<?php echo $purchaseOrder['PurchaseOrder']['company_id']; ?>")==-1){
                    $(this).hide();
                }
            }
        });

        $(".btnBackPurchaseOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oPOTable.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            $("#"+PbTableName).find("tbody").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        
        $("#exchangeRatePB").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#PurchaseOrderAmountOther").removeAttr("readonly");
            } else {
                $("#PurchaseOrderAmountOther").val(0);
                $("#PurchaseOrderAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbolPB").html(symbol);
            $("#PurchaseOrderExchangeRateId").val(exRateId);
            calculateReceiptPbBalance();
        });
        
    });

    function calculateReceiptPbBalance(){
        var totalAmount = replaceNum('<?php echo $purchaseOrder['PurchaseOrder']['balance']; ?>');
        var amount      = replaceNum($("#PurchaseOrderAmountUs").val());
        var amountOther = replaceNum($("#PurchaseOrderAmountOther").val());

        // Obj
        var balance      = $("#PurchaseOrderBalanceUs");
        var balanceOther = $("#PurchaseOrderBalanceOther");

        var totalPaid = amount + convertToMainCurrencyPB(amountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#PurchaseOrderAmountUs").val(totalAmount);
            $("#PurchaseOrderAmountOther").val(0);
        }
        var totalBalance = converDicemalJS(totalAmount - totalPaid);        
        if(totalBalance > 0){
            $(".DivPurchaseOrderAging").show();
            $("#spanPurchaseOrderAging").html("*");
            $("#PurchaseOrderAging").addClass("validate[required]");
        }else{
            $(".DivPurchaseOrderAging").hide();
            $("#spanPurchaseOrderAging").html("");
            $("#PurchaseOrderAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(<?php echo $costDecimal; ?>));
        balanceOther.val(convertToOtherCurrencyPB(totalBalance).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseOrderBalanceUs, #PurchaseOrderBalanceOther").priceFormat({
            centsLimit: <?php echo $costDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrencyPB(val){
        var exchangeRate  = replaceNum($("#exchangeRatePB").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrencyPB(val){
        var exchangeRate = replaceNum($("#exchangeRatePB").find("option:selected").attr("ratesale"));
        return converDicemalJS(replaceNum(val) * exchangeRate);
    }
    
    function voidReceiptPB(id, name, poId){
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
                        url: "<?php echo $this->base . '/'. $this->params['controller']; ?>/voidReceipt/" + id,
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            var panel = $("#PurchaseOrderAgingForm").parent();
                            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + poId);
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Purchase Bill', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Purchase Bill', 'Void Receipt', 1, '');
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            var panel = $("#PurchaseReturnAgingForm").parent();
                            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + $("#PurchaseReturnId").val());
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
        <a href="" class="positive btnBackPurchaseOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('PurchaseOrder'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_PURCHASE_ORDER_MANAGEMENT_INFO); ?></legend>
    <div style="float: right; width:30px;">
        <?php
        if ($allowPrintInvoice) {
            echo "<a href='#' class='btnPrintInvoice' rel='{$purchaseOrder['PurchaseOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT_PURCHASE_BILL_RECEIPT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
        }
        ?>
    </div>
    <div>
        <table style="width: 100%;" cellpadding="5" cellspacing="0">
            <tr>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;">
                    <?php echo $purchaseOrder['Branch']['name']; ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PB_NUMBER; ?> :</td>
                <td style="width: 20%; font-size: 12px;">
                    <?php echo $purchaseOrder['PurchaseOrder']['po_code']; ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                <td style="width: 28%; font-size: 12px;">
                    <?php echo dateShort($purchaseOrder['PurchaseOrder']['order_date']); ?>
                </td>
            </tr>
            <tr>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['Vendor']['name']; ?>
                </td>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['LocationGroup']['name']; ?>
                </td>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['Location']['name']; ?>
                </td>
            </tr>
            <tr>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_MEMO; ?> :</td>
                <td colspan="5" style="font-size: 12px;">
                    <?php echo $purchaseOrder['PurchaseOrder']['note']; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
        if (!empty($purchaseOrderDetails)) {
            $totalCol = 10;
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_PRODUCT; ?></legend>
                    <table class="table" >
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th><?php echo TABLE_BARCODE; ?></th>
                            <th style="width: 20%;"><?php echo TABLE_NAME ?></th>
                            <th><?php echo TABLE_NOTE; ?></th>
                            <th <?php if($allowLots == false){ --$totalCol; ?>style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO ?></th>
                            <th <?php if($allowExpired == false){ --$totalCol; ?>style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE ?></th>
                            <th><?php echo TABLE_QTY ?></th>
                            <th><?php echo TABLE_UOM; ?></th>
                            <th><?php echo SALES_ORDER_UNIT_PRICE.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                            <th><?php echo GENERAL_DISCOUNT.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                            <th><?php echo SALES_ORDER_TOTAL_PRICE.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                foreach ($purchaseOrderDetails as $purchaseOrderDetail) {
                    $discount       = $purchaseOrderDetail['PurchaseOrderDetail']['discount_amount'];
                    $totalDiscount += $discount;
                    $totalPrice    += ( $purchaseOrderDetail['PurchaseOrderDetail']['total_cost'] - $discount);
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseOrderDetail['Product']['code']; ?></td>
                        <td><?php echo $purchaseOrderDetail['Product']['name']; ?></td>
                        <td><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['note']; ?></td>
                        <td <?php if($allowLots == false){ ?>style="display: none;"<?php } ?>><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['lots_number']; ?></td>
                        <td <?php if($allowExpired == false){ ?>style="display: none;"<?php } ?>><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['date_expired']!=""?dateShort($purchaseOrderDetail['PurchaseOrderDetail']['date_expired']):""; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty'], 0); ?></td>
                        <td><?php echo $purchaseOrderDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['unit_cost'], 6); ?></td>
                        <td style="text-align: right"><?php echo number_format($discount, $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format(($purchaseOrderDetail['PurchaseOrderDetail']['total_cost']-$discount), $costDecimal); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $costDecimal); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
            }
    ?>
    <?php
    if(!empty($purchaseOrderServices)){
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_SERVICE; ?></legend>
                <table class="table" >
                    <tr>
                        <th class="first"><?php echo TABLE_NO; ?></th>
                        <th><?php echo TABLE_NAME ?></th>
                        <th><?php echo TABLE_NOTE; ?></th>
                        <th><?php echo TABLE_QTY ?></th>
                        <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalPrice = 0;
                    $totalDiscount = 0;
                    foreach ($purchaseOrderServices as $purchaseOrderService) {                        
                        $unit_price  = number_format($purchaseOrderService['PurchaseOrderService']['unit_cost'], 6);
                        $discount    = $purchaseOrderService['PurchaseOrderService']['discount_amount'];
                        $total_price = number_format($purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount, $costDecimal);
                        $totalPrice += $purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount;
                        ?>
                        <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo $purchaseOrderService['Service']['name']; ?></td>
                            <td><?php echo $purchaseOrderService['PurchaseOrderService']['note']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty'], 0); ?></td>
                            <td style="text-align: right"><?php echo $unit_price; ?></td>
                            <td style="text-align: right"><?php echo $discount; ?></td>
                            <td style="text-align: right"><?php echo $total_price; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="first" colspan="6" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                        <td style="text-align: right" ><?php echo number_format($totalPrice, $costDecimal); ?></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($purchaseOrder['PurchaseOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($purchaseOrder['PurchaseOrder']['discount_percent'],  2); ?>%)<?php } ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['discount_amount']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseOrder['PurchaseOrder']['vat_percent'], $costDecimal); ?> %)</b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_vat']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount'] - $purchaseOrder['PurchaseOrder']['discount_amount'] + $purchaseOrder['PurchaseOrder']['total_vat']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    $poId = $purchaseOrder['PurchaseOrder']['purchase_request_id']!=''?$purchaseOrder['PurchaseOrder']['purchase_request_id']:0;
    $sqlDept = mysql_query("SELECT date, reference, IFNULL(total_deposit,0) AS total_deposit, note FROM general_ledgers WHERE ((apply_to_id = ".$purchaseOrder['PurchaseOrder']['id']." AND deposit_type = 3) OR (apply_to_id = ".$poId." AND deposit_type = 2)) AND is_active = 1");
    if(mysql_num_rows($sqlDept)){
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_DEPOSIT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first" style="font-size: 11px;"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_TOTAL_DEPOSIT.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_CREATED; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; width: 25%;"><?php echo GENERAL_DESCRIPTION; ?></th>
                </tr>
                <?php
                $index = 0;
                while($rowDept = mysql_fetch_array($sqlDept)) {
                ?>
                    <tr><td class="first" style="text-align: center; font-size: 11px;"><?php echo ++$index; ?></td>
                        <td style="font-size: 11px;"><?php echo $rowDept['reference']; ?></td>
                        <td style="font-size: 11px;"><?php echo number_format($rowDept['total_deposit'], $costDecimal); ?></td>
                        <td style="font-size: 11px;"><?php echo dateShort($rowDept['date']); ?></td>
                        <td style="font-size: 11px;"><?php echo $rowDept['note']; ?></td>
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
    <?php
            if (!empty($purchaseOrderReceipts)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo GENERAL_PAID; ?> &nbsp;
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='btnPrintReceipt' rel='{$purchaseOrder['PurchaseOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                        }
                        ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_DATE ?></th>
                                <th><?php echo TABLE_CODE ?></th>
                                <th><?php echo GENERAL_EXCHANGE_RATE ?></th>
                                <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                                <th colspan="2" style="width: 25%;"><?php echo GENERAL_PAID; ?></th>
                                <th><?php echo GENERAL_BALANCE; ?></th>
                                <th style="width:10%;"></th>
                            </tr>
                <?php
                $index = 0;
                $leght = count($purchaseOrderReceipts);
                foreach ($purchaseOrderReceipts as $purchaseOrderReceipt) {
                ?>
                    <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($purchaseOrderReceipt['Pv']['pay_date'])); ?></td>
                        <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo $html->link($purchaseOrderReceipt['Pv']['pv_code'], array("action" => "#"), array("class" => "btnPrintReceiptOne", "rel" => $purchaseOrderReceipt['Pv']['id']));
                        } else {
                            $purchaseOrderReceipt['Pv']['pv_code'];
                        }
                        ?>
                    </td>
                    <td style="text-align: right;">1 <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($purchaseOrderReceipt['ExchangeRate']['rate_purchase'], 9); ?> <?php echo $purchaseOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['total_amount'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['amount_us'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['amount_other'], $costDecimal); ?> <?php echo $purchaseOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['balance'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='btnPrintReceiptOne' rel='{$purchaseOrderReceipt['Pv']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                        }
                        if($index == $leght){
                            echo "&nbsp; <a href='{$purchaseOrderReceipt['Pv']['pv_code']}' name='{$purchaseOrderReceipt['Pv']['purchase_order_id']}' class='btnDeleteRp$rand' rel='{$purchaseOrderReceipt['Pv']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
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
                if($purchaseOrder['PurchaseOrder']['balance'] == 0){
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
                        <input type="hidden" name="data[PurchaseOrder][exchange_rate_id]" id="PurchaseOrderExchangeRateId" />
                        <?php
                        $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_purchase FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$purchaseOrder['PurchaseOrder']['branch_id']);
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
                                    <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?> =</td>
                                    <td style="font-size: 12px;"><?php echo number_format($rowCurrency['rate_purchase'], 9); ?> <?php echo $rowCurrency['symbol']; ?></td>
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
                    <td><label for="PurchaseOrderPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('pay_date', array('style' => 'text-align:left; width: 120px;', 'readonly' => true)); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="PurchaseOrderAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                    <td>
                        <?php
                        echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                        echo $this->Form->hidden('total_amount', array('value' => $purchaseOrder['PurchaseOrder']['balance']));
                        ?> (<?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?>)
                    </td>
                </tr>
                <tr style="display: none;">
                    <td>
                        <select name="data[PurchaseOrder][currency_center_id]" id="exchangeRatePB" style="width: 150px;">
                            <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_purchase FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$purchaseOrder['PurchaseOrder']['branch_id']);
                            while($rowCurSelect = mysql_fetch_array($sqlCurSelect)){
                            ?>
                            <option value="<?php echo $rowCurSelect['id']; ?>" symbol="<?php echo $rowCurSelect['symbol']; ?>" exrate="<?php echo $rowCurSelect['exchange_rate_id']; ?>" ratesale="<?php echo $rowCurSelect['rate_purchase']; ?>"><?php echo $rowCurSelect['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <?php echo $this->Form->text('amount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0, 'readonly' => true)); ?>
                        <span class="paidOtherCurrencySymbolPB"></span>
                    </td>
                </tr>
                <tr class="DivPurchaseOrderAging">
                    <td style="vertical-align: top">
                        <label for="PurchaseOrderAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanPurchaseOrderAging">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('aging', array('style' => 'width: 120px;')); ?>
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
                            <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?>
                        </td>
                        <td>
                            <span class="paidOtherCurrencySymbolPB"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="first"> 
                            <?php echo $this->Form->text('balance_us', array('class' => 'float', 'style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($purchaseOrder['PurchaseOrder']['balance'], $costDecimal))); ?> 
                        </td>
                        <td> 
                            <input type="text" name="data[PurchaseOrder][balance_other]" id="PurchaseOrderBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    </fieldset>
    <br />
    <div class="buttons"<?php echo $styleDisplay; ?>>
        <button type="submit" class="positive paidPurchaseBill" >
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>