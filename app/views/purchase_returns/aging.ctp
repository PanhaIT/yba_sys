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
$this->element('check_access');
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowPrintInvoice = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$rand = rand();
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen();
        $(".float").autoNumeric({mDec: <?php echo $costDecimal; ?>, aSep: ','});
        $("#PurchaseReturnAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PurchaseReturnAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $(".PurchaseReturnAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#PurchaseReturnPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
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
                createSysAct('Bill Return', 'Add', 2, result.responseText);
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
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Bill Return', 'Aging', 1, '');
                $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceipt<?php echo $rand; ?>" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintReceipt"><?php echo ACTION_PRINT_RECEIPT; ?></span></button></div> ');
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
                        $(".btnBackPurchaseReturn").click();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $("#btnApplyPB").click(function(){
            $("#PurchaseReturnAmountUs, #PurchaseReturnAmountOther").val(0);
            calculateReceiptBRBalance();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/invoice/<?php echo $purchaseReturn['PurchaseReturn']['company_id']; ?>/<?php echo $purchaseReturn['PurchaseReturn']['branch_id']; ?>/<?php echo $purchaseReturn['PurchaseReturn']['ap_id']; ?>/<?php echo $purchaseReturn['PurchaseReturn']['vendor_id']; ?>/"+replaceNum($("#PurchaseReturnBalanceUs").val())+"/"+$(this).attr('pr-id'),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PURCHASE_RETURN_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 990,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".dataTables_length").hide();
                            $(".dataTables_paginate").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var formName = "#applyInvoicePBCForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(validateBack){
                                    if($("input[name='chkInvoicePO[]']:checked").val() != undefined){
                                        $("#invoiceDateApplyPBC").datepicker("option", "dateFormat", "yy-mm-dd");
                                        var i = 1;
                                        var parameter = "";
                                        $("input[name='chkInvoicePO[]']:checked").each(function(){
                                            var val = $(this).val();
                                            var price = $(this).closest("tr").find("input[name='invoice_price_pbc[]']").val();
                                            if(i == 1){
                                                parameter += "purchase_order[]="+val;
                                            }else{
                                                parameter += "&purchase_order[]="+val;
                                            }
                                            parameter += "&invoice_price_pbc[]="+price;
                                            i++;
                                        });
                                        $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/applyToInvoice/<?php echo $purchaseReturn['PurchaseReturn']['id']; ?>" + "/"+replaceNum($("#PurchaseReturnBalanceUs").val())+"/"+$("#invoiceDateApplyPBC").val(),
                                            data: parameter,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $(".btnBackPurchaseReturn").click();
                                                if(result != ''){
                                                    createSysAct('Bill Return', 'Apply To PB', 2, result);
                                                } else {
                                                    createSysAct('Bill Return', 'Apply To PB', 1, '');
                                                }
                                            }
                                        });
                                    }
                                    $(this).dialog("close");
                                }
                            }
                        }
                    });
                }
            });
        });
        
        $(".btnVoidPbcWPo").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('name');
            var obj = $(this);
            $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete apply invoice code <b>' + name + '</b>?</p>');
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
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $(".paid").attr("disabled","disabled");
                        obj.closest("tr").remove();
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/deletePbcWPo/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            error: function (result) {
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                createSysAct('Bill Return', 'Void Apply PB', 2, result.responseText);
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
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                if(result.result == 1){
                                    createSysAct('Bill Return', 'Void Apply PB', 1, '');
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                } else if(result.result == 2) {
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                }
                                var panel = $("#PurchaseReturnAgingForm").parent();
                                panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + $("#PurchaseReturnId").val());
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
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
        
        $(".printReceipt<?php echo $rand; ?>, .btnPrintReceipt<?php echo $rand; ?>").click(function(event){
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

        $(".printInvoice<?php echo $rand; ?>, .btnPrintInvoice<?php echo $rand; ?>").click(function(event){
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
        
        $(".paid").click(function(){
            var formName = "#PurchaseReturnAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if($("#PurchaseReturnBalanceUs").val() == <?php echo $purchaseReturn['PurchaseReturn']['balance']; ?>){
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
        $("#PurchaseReturnPayDate").val(now.toString('dd/MM/yyyy'));
        $("#PurchaseReturnPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($purchaseReturn['PurchaseReturn']['order_date'])); ?>'
        }).unbind("blur");
        
        $('.PurchaseReturnAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($purchaseReturn['PurchaseReturn']['order_date'])); ?>'
        }).unbind("blur");
        
        $(".btnDeleteRp<?php echo $rand; ?>").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var prId = $(this).attr('name');
            voidReceiptPR(id, name, prId);
        });
        
        $("#PurchaseReturnAmountUs, #PurchaseReturnAmountOther").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $("#PurchaseReturnAmountUs, #PurchaseReturnAmountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });

        $("#PurchaseReturnAmountUs, #PurchaseReturnAmountOther").live("keyup", function(){
            calculateReceiptBRBalance();
        });

        $(".btnBackPurchaseReturn").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePurchaseReturn.fnDraw(false);
            var rightPanel = $("#PurchaseReturnAgingForm").parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        
        $("#exchangeRateBR").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#PurchaseReturnAmountOther").removeAttr("readonly");
            } else {
                $("#PurchaseReturnAmountOther").val(0);
                $("#PurchaseReturnAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbolBR").html(symbol);
            $("#PurchaseReturnExchangeRateId").val(exRateId);
            calculateReceiptBRBalance();
        });
    });

    function calculateReceiptBRBalance(){
        var totalAmount =  replaceNum('<?php echo $purchaseReturn['PurchaseReturn']['balance']; ?>');
        var amount      =  replaceNum($("#PurchaseReturnAmountUs").val());
        var amountOther =  replaceNum($("#PurchaseReturnAmountOther").val());

        // Obj
        var balance      = $("#PurchaseReturnBalanceUs");
        var balanceOther = $("#PurchaseReturnBalanceOther");

        var totalPaid = amount + convertToMainCurrencyBR(amountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#PurchaseReturnAmountUs").val(totalAmount);
            $("#PurchaseReturnAmountOther").val(0);
        }
        var totalBalance = totalAmount - totalPaid;
        if(totalBalance.toFixed(<?php echo $costDecimal; ?>)>0){
            $(".DivPurchaseReturnAging").show();
            $("#spanPurchaseReturnAging").html("*");
            $("#PurchaseReturnAging").addClass("validate[required]");
        }else{
            $(".DivPurchaseReturnAging").hide();
            $("#spanPurchaseReturnAging").html("");
            $("#PurchaseReturnAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(<?php echo $costDecimal; ?>));
        balanceOther.val(convertToOtherCurrencyBR(totalBalance).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseReturnBalanceUs, #PurchaseReturnBalanceOther").priceFormat({
            centsLimit: <?php echo $costDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrencyBR(val){
        var exchangeRate  = replaceNum($("#exchangeRateBR").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrencyBR(val){
        var exchangeRate = replaceNum($("#exchangeRateBR").find("option:selected").attr("ratesale"));
        return converDicemalJS(replaceNum(val) * exchangeRate);
    }
    
    function voidReceiptPR(id, name, prId){
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
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/voidReceipt/" + id,
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            var panel = $("#PurchaseReturnAgingForm").parent();
                            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + prId);
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                createSysAct('Bill Return', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Bill Return', 'Void Receipt', 1, '');
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
        <a href="" class="positive btnBackPurchaseReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('PurchaseReturn'); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->hidden('location_id', array('value' => $purchaseReturn['PurchaseReturn']['location_id'])); ?>
<fieldset>
    <legend><?php __(MENU_PURCHASE_RETURN_MANAGEMENT_INFO); ?></legend>
    <div style="float: right; width:30px;">
        <?php
        if ($allowPrintInvoice) {
            echo "<a href='#' class='btnPrintInvoice$rand' rel='{$purchaseReturn['PurchaseReturn']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT_BILL_RETURN . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
        }
        ?>
    </div>
    <div>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="width: 25%; font-size: 12px;"><?php echo $purchaseReturn['Branch']['name']; ?></td>
                <td style="width: 10%; font-size: 12px; text-transform: uppercase;"></td>
                <td style="width: 25%; font-size: 12px;"></td>
                <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_DATE; ?> :</td>
                <td style="font-size: 12px;"><?php echo dateShort($purchaseReturn['PurchaseReturn']['order_date']); ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px;"><?php echo $purchaseReturn['LocationGroup']['name']; ?></td>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_LOCATION; ?> :</td>
                <td ><?php echo $purchaseReturn['Location']['name']; ?></td>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo PURCHASE_RETURN_CODE; ?> :</td>
                <td style="font-size: 12px;"><?php echo $purchaseReturn['PurchaseReturn']['pr_code']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_VENDOR; ?> :</td>
                <td style="font-size: 12px;"><?php echo $purchaseReturn['Vendor']['vendor_code']." - ".$purchaseReturn['Vendor']['name']; ?></td>
                <td style="font-size: 12px; text-transform: uppercase;"></td>
                <td style="font-size: 12px;" colspan="3"></td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_MEMO; ?> :</td>
                <td style="vertical-align: top; font-size: 12px;" cospan="5"><?php echo nl2br($purchaseReturn['Vendor']['note']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($purchaseReturnDetails)) {
        $totalCol = 9;
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                    <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($purchaseReturnDetails as $purchaseReturnDetail) {
                    $totalPrice += $purchaseReturnDetail['PurchaseReturnDetail']['total_price'];
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseReturnDetail['Product']['barcode']; ?></td>
                        <td><?php echo $purchaseReturnDetail['Product']['name']; ?></td>
                        <td><?php echo $purchaseReturnDetail['PurchaseReturnDetail']['note']; ?></td>
                        <td<?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $purchaseReturnDetail['PurchaseReturnDetail']['lots_number']; ?></td>
                        <td<?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                            <?php 
                            if($purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] != '' && $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] != '0000-00-00'){
                                echo dateShort($purchaseReturnDetail['PurchaseReturnDetail']['expired_date']);
                            }
                            ?>
                        </td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['qty'], 0); ?></td>
                        <td><?php echo $purchaseReturnDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['unit_price'], $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['total_price'], $costDecimal); ?></td>
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
            if (!empty($purchaseReturnServices)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_SERVICE; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE; ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($purchaseReturnServices as $purchaseReturnService) {
                    $totalPrice += $purchaseReturnService['PurchaseReturnService']['total_price'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseReturnService['Service']['name']; ?></td>
                        <td><?php echo $purchaseReturnService['PurchaseReturnService']['note']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['qty'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['unit_price'], $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['total_price'], $costDecimal); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="5" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_SUB_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseReturn['PurchaseReturn']['vat_percent'], $costDecimal); ?> %)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_vat'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'] + $purchaseReturn['PurchaseReturn']['total_vat'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($pbcWPos)) {
        ?>
        <div>
            <fieldset>
                <legend>Apply To Invoice</legend>
                <table class="table" >
                    <tr>
                        <th class="first"></th>
                        <th><?php echo TABLE_ORDER_DATE; ?></th>
                        <th><?php echo TABLE_PURCHASE_BILL_CODE; ?></th>
                        <th><?php echo TABLE_VENDOR; ?></th>
                        <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                        <th><?php echo GENERAL_PAID; ?></th>
                        <th><?php echo TABLE_DATE; ?></th>
                        <th><?php echo ACTION_ACTION; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($pbcWPos as $pbcWPo) {
                        $totalPrice += $pbcWPo['InvoicePbcWithPb']['total_cost'];
                        ?>
                        <tr>
                            <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo dateShort($pbcWPo['PurchaseOrder']['order_date']); ?></td>
                            <td style="text-align: right;"><?php echo $pbcWPo['PurchaseOrder']['po_code']; ?></td>
                            <td style="text-align: right;">
                                <?php
                                $sql = mysql_query("SELECT * FROM vendors WHERE id = " . $pbcWPo['PurchaseOrder']['vendor_id'] . " AND is_active = 1 LIMIT 1");
                                $customer = mysql_fetch_array($sql);
                                echo $customer['vendor_code'] . "-" . $customer['name'];
                                ?>
                            </td>
                            <td style="text-align: right;"><?php echo number_format($pbcWPo['PurchaseOrder']['total_amount'], $costDecimal); ?></td>
                            <td style="text-align: right;"><?php echo number_format($pbcWPo['InvoicePbcWithPb']['total_cost'], $costDecimal); ?></td>
                            <td style="text-align: right;"><?php echo dateShort($pbcWPo['InvoicePbcWithPb']['apply_date']); ?></td>
                            <td style="text-align: right;"><a href="" class="btnVoidPbcWPo" rel="<?php echo $pbcWPo['InvoicePbcWithPb']['id']; ?>" name="<?php echo $pbcWPo['PurchaseOrder']['po_code']; ?>"><img alt="Void" onmouseover="Tip('Void')" src="<?php echo $this->webroot; ?>img/button/delete.png" /></a></td>
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
    if (!empty($purchaseReturnReceipts)) {
        ?>
        <div>
            <fieldset>
                <legend><?php echo GENERAL_PAID; ?></legend>
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
                    $leght = count($purchaseReturnReceipts);
                    foreach ($purchaseReturnReceipts as $purchaseReturnReceipt) {
                        ?>
                        <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo date("d/m/Y", strtotime($purchaseReturnReceipt['PurchaseReturnReceipt']['created'])); ?></td>
                            <td>
                                <?php
                                if ($allowPrintReceipt) {
                                    echo $html->link($purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code'], array("action" => "#"), array("class" => "printReceipt$rand", "rel" => $purchaseReturnReceipt['PurchaseReturnReceipt']['id']));
                                } else {
                                    $purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code'];
                                }
                                ?>
                            </td>
                            <td style="text-align: right;">1 <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?> = <?php echo number_format($purchaseReturnReceipt['ExchangeRate']['rate_to_sell'], $costDecimal); ?> <?php echo $purchaseReturnReceipt['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['total_amount'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_us'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_other'], $costDecimal); ?> <?php echo $purchaseReturnReceipt['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['balance'], $costDecimal); ?> <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?></td>
                            <td>
                                <?php
                                if ($allowPrintReceipt) {
                                    echo "<a href='#' class='btnPrintReceipt$rand' rel='{$purchaseReturnReceipt['PurchaseReturnReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                                }
                                if ($index == $leght) {
                                    echo "&nbsp; <a href='{$purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code']}' name='{$purchaseReturnReceipt['PurchaseReturnReceipt']['purchase_return_id']}' class='btnDeleteRp$rand' rel='{$purchaseReturnReceipt['PurchaseReturnReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
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
    <?php if ($purchaseReturn['PurchaseReturn']['balance'] > 0) { ?>
        <div>
            <div style="float: left;">
                <table style="width: 250px;">
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="data[PurchaseReturn][exchange_rate_id]" id="PurchaseReturnExchangeRateId" />
                            <?php
                            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$purchaseReturn['PurchaseReturn']['branch_id']);
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
                                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?> =</td>
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
                        <td colspan="2" style="text-align: center;"><input type="button" id="btnApplyPB" value="Apply to an PB" style="height: 30px;" pr-id="<?php echo $purchaseReturn['PurchaseReturn']['id']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="PurchaseReturnPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('pay_date', array('style' => 'text-align:center; width: 120px;', 'readonly' => true, 'class' => 'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="PurchaseReturnAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                        <td>
                            <?php
                            echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                            echo $this->Form->hidden('total_amount', array('value' => $purchaseReturn['PurchaseReturn']['balance']));
                            ?>  (<?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?>)
                        </td>
                    </tr>
                    <tr style="display: none;">
                        <td>
                            <select name="data[PurchaseReturn][currency_center_id]" id="exchangeRateBR" style="width: 150px;">
                                <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                                <?php 
                                $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$purchaseReturn['PurchaseReturn']['branch_id']);
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
                            <span class="paidOtherCurrencySymbolBR"></span>
                        </td>
                    </tr>
                    <tr class="DivPurchaseReturnAging">
                        <td style="vertical-align: top"><label for="PurchaseReturnAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanPurchaseReturnAging">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('aging', array('id' => 'PurchaseReturnAging' . $rand, 'class' => 'PurchaseReturnAging', 'style' => 'width: 120px;')); ?>
                            </div>
                            <div style="clear:both;"></div>
                        </td>
                    </tr>

                </table>
            </div>
            <div style="clear: both;"></div>
            <div style="float: right;">
                <table align="center" style="width:200px;" class="table" cellspacing="0" >
                    <tr>
                        <th class="first" colspan="2">
                            <?php echo GENERAL_BALANCE; ?>
                        </th>
                    </tr>
                    <tr>
                        <td class="first"> 
                            <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?>
                        </td>
                        <td>     
                            <span class="paidOtherCurrencySymbolBR"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="first"> <?php echo $this->Form->text('balance_us', array('style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($purchaseReturn['PurchaseReturn']['balance'], $costDecimal), 'class' => 'float')); ?> </td>
                        <td> 
                            <input type="text" name="data[PurchaseReturn][balance_other]" id="PurchaseReturnBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    <?php } ?>
</fieldset>
<?php if ($purchaseReturn['PurchaseReturn']['balance'] > 0) { ?>
    <br />
    <div class="buttons">
        <button type="submit" class="positive paid" >
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
<?php } ?>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>