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
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen();
        $(".float").autoNumeric({mDec: <?php echo $priceDecimal; ?>, aSep: ','});
        $("#CreditMemoAmountUs, #CreditMemoAmountOther, .paidAgingCM").unbind('keyup').unbind('click');
        
        $(".btnVoidCmWSale").unbind('click').click(function(event){
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
                        $(".paidAgingCM").attr("disabled","disabled");
                        obj.closest("tr").remove();
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/deleteCmWSlae/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            error: function (result) {
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                createSysAct('Credit Memo', 'Void Apply Invoice', 2, result.responseText);
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
                                    createSysAct('Credit Memo', 'Void Apply Invoice', 1, '');
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                } else if(result.result == 2) {
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                }
                                var panel = $("#CreditMemoAgingForm").parent();
                                panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + $("#CreditMemoId").val());
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
        
        $("#CreditMemoAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#CreditMemoAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $(".CreditMemoAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#CreditMemoPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
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
                $(".btnBackCreditMemo").click();
                createSysAct('Credit Memo', 'Aging', 2, result.responseText);
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
                if(result.sr_id){
                    $(".btnBackCreditMemo").click();
                    createSysAct('Credit Memo', 'Aging', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceiptCreditMemoSave" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_RECEIPT; ?></span></button></div> ');
                    $(".printReceiptCreditMemoSave").click(function(){
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
                            $(".ui-dialog-titlebar-close").show();
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

        $("#btnApplyInvoice").unbind('click').click(function(event){
            event.preventDefault();
            $("#CreditMemoAmountUs, #CreditMemoAmountOther").val(0);
            calculateReceiptCmBalance();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/invoice/<?php echo $creditMemo['CreditMemo']['ar_id']; ?>/<?php echo $creditMemo['CreditMemo']['company_id']; ?>/<?php echo $creditMemo['CreditMemo']['branch_id']; ?>/<?php echo $creditMemo['CreditMemo']['customer_id']; ?>/"+$("#CreditMemoBalanceUs").val()+"/"+$(this).attr('cm-id'),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_SALES_ORDER_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 900,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                            $(".dataTables_length").hide();
                            $(".dataTables_paginate").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var formName = "#applyInvoiceCMForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(validateBack){
                                    if($("input[name='chkInvoice[]']:checked").val() != undefined){
                                        $("#invoiceDateApplyCM").datepicker("option", "dateFormat", "yy-mm-dd");
                                        var i = 1;
                                        var parameter = "";
                                        var date = $("#invoiceDateApplyCM").val();
                                        $("input[name='chkInvoice[]']:checked").each(function(){
                                            var val = $(this).val();
                                            var price = $(this).closest("tr").find("input[name='invoice_price[]']").val();
                                            if(i == 1){
                                                parameter += "sales_order[]="+val;
                                            }else{
                                                parameter += "&sales_order[]="+val;
                                            }
                                            parameter += "&invoice_price[]="+price;
                                            i++;
                                        });
                                        $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/applyToInvoice/<?php echo $creditMemo['CreditMemo']['id']; ?>" + "/"+replaceNum($("#CreditMemoBalanceUs").val())+"/"+date,
                                            data: parameter,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $(".btnBackCreditMemo").click();
                                                if(result != ''){
                                                    createSysAct('Credit Memo', 'Apply To Invoice', 2, result);
                                                } else {
                                                    createSysAct('Credit Memo', 'Apply To Invoice', 1, '');
                                                }
                                            }
                                        });
                                    }
                                    $(this).dialog("close");
                                }
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });

        $(".btnPrintReceiptPayCreditMemo").unbind("click").click(function(event){
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

        $(".btnPrintInvoiceCreditMemo").unbind("click").click(function(event){
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
        
        $(".paidAgingCM").click(function(){
            var formName = "#CreditMemoAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if($("#CreditMemoBalanceUs").val() == <?php echo $creditMemo['CreditMemo']['balance']; ?>){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please paid first.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }else{
                    return true;
                }
            }
        });

        var now = new Date();
        $("#CreditMemoPayDate").val(now.toString('dd/MM/yyyy'));
        $("#CreditMemoPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($creditMemo['CreditMemo']['order_date'])); ?>'
        }).unbind("blur");

        $('.CreditMemoAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($creditMemo['CreditMemo']['order_date'])); ?>'
        }).unbind("blur");
        
        $(".btnDeleteRpCreditMemo").unbind('click').click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var cmId = $(this).attr('name');
            voidReceiptCM(id, name, cmId);
        });
        
        $("#CreditMemoAmountUs, #CreditMemoAmountOther").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $("#CreditMemoAmountUs, #CreditMemoAmountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });

        $("#CreditMemoAmountUs, #CreditMemoAmountOther").keyup(function(){
            calculateReceiptCmBalance();
        });

        $(".btnBackCreditMemo").click(function(event){
            event.preventDefault();
            var rightPanel=$(".btnBackCreditMemo").parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableCreditMemo.fnDraw(false);
        });
        
        $("#exchangeRateCM").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#CreditMemoAmountOther").removeAttr("readonly");
            } else {
                $("#CreditMemoAmountOther").val(0);
                $("#CreditMemoAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbolCM").html(symbol);
            $("#CreditMemoExchangeRateId").val(exRateId);
            calculateReceiptCmBalance();
        });
        
    });
    
    

    function calculateReceiptCmBalance(){
        var totalAmount = replaceNum('<?php echo $creditMemo['CreditMemo']['balance']; ?>');
        var amount      = replaceNum($("#CreditMemoAmountUs").val());
        var amountOther = replaceNum($("#CreditMemoAmountOther").val());

        // Obj
        var balance      = $("#CreditMemoBalanceUs");
        var balanceOther = $("#CreditMemoBalanceOther");

        var totalPaid = amount + convertToMainCurrencyCM(amountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#CreditMemoAmountUs").val(totalAmount);
            $("#CreditMemoAmountOther").val(0);
        }
        var totalBalance = totalAmount - totalPaid;
        if(replaceNum(totalBalance.toFixed(<?php echo $priceDecimal; ?>))>0){
            $(".DivCreditMemoAging").show();
            $("#spanCreditMemoAging").html("*");
            $("#CreditMemoAging").addClass("validate[required]");
        }else{
            $(".DivCreditMemoAging").hide();
            $("#spanCreditMemoAging").html("");
            $("#CreditMemoAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(<?php echo $priceDecimal; ?>));
        balanceOther.val(convertToOtherCurrencyCM(totalBalance).toFixed(<?php echo $priceDecimal; ?>));
        $("#CreditMemoBalanceUs, #CreditMemoBalanceOther").priceFormat({
            centsLimit: <?php echo $priceDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrencyCM(val){
        var exchangeRate  = replaceNum($("#exchangeRateCM").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrencyCM(val){
        var exchangeRate = replaceNum($("#exchangeRateCM").find("option:selected").attr("ratesale"));
        return converDicemalJS(replaceNum(val) * exchangeRate);
    }
    
    function voidReceiptCM(id, name, cmId){
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
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                createSysAct('Credit Memo', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Credit Memo', 'Void Receipt', 1, '');
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            $(".btnBackCreditMemo").click();
                            // alert message
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
        <a href="" class="positive btnBackCreditMemo">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('CreditMemo'); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->hidden('company_id', array('value' => $creditMemo['CreditMemo']['company_id'])); ?>
<?php echo $this->Form->hidden('location_id', array('value' => $creditMemo['CreditMemo']['location_id'])); ?>
<fieldset>
    <legend><?php __(MENU_CREDIT_MEMO_MANAGEMENT_INFO); ?></legend>
    <div style="float: right; width:30px;">
        <?php
        if ($allowPrintInvoice) {
            echo "<a href='#' class='btnPrintInvoiceCreditMemo' rel='{$creditMemo['CreditMemo']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . ' ' . SALES_ORDER_INVOICE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
        }
        ?>
    </div>
    <div>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="font-size: 12px; width: 9%;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['Branch']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"><?php echo TABLE_LOCATION; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['Location']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"></td>
                <td style="font-size: 12px;"></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo CREDIT_MEMO_CODE; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['CreditMemo']['cm_code']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                <td style="font-size: 12px;"><?php echo dateShort($this->data['CreditMemo']['order_date']); ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['CreditMemo']['invoice_code']; ?></td>
                <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?></td>
                <td style="font-size: 12px; vertical-align: top;" colspan="5"><?php echo nl2br($this->data['CreditMemo']['note']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($creditMemoDetails)) {
        $totalCol = 11;
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_NAME ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                    <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_F_O_C; ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($creditMemoDetails as $creditMemoDetail) {
                    $unit_price = number_format($creditMemoDetail['CreditMemoDetail']['unit_price'], $priceDecimal);
                    $discount = $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                    $total_price = number_format($creditMemoDetail['CreditMemoDetail']['total_price'] - $discount, $priceDecimal);
                    $totalPrice += $creditMemoDetail['CreditMemoDetail']['total_price'] - $discount;
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo $creditMemoDetail['Product']['barcode']; ?></td>
                    <td><?php echo $creditMemoDetail['Product']['name']; ?></td>
                    <td><?php echo $creditMemoDetail['CreditMemoDetail']['note']; ?></td>
                    <td <?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $creditMemoDetail['CreditMemoDetail']['lots_number']; ?></td>
                    <td <?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <?php 
                        if($creditMemoDetail['CreditMemoDetail']['expired_date'] != '' && $creditMemoDetail['CreditMemoDetail']['expired_date'] != '0000-00-00'){
                            echo dateShort($creditMemoDetail['CreditMemoDetail']['expired_date']);
                        }
                        ?>
                    </td>
                    <td style="text-align: right"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty'], 0); ?></td>
                    <td style="text-align: right"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty_free'], 0); ?></td>
                    <td><?php echo $creditMemoDetail['Uom']['name']; ?></td>
                    <td style="text-align: right"><?php echo $unit_price; ?></td>
                    <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                    <td style="text-align: right"><?php echo $total_price; ?></td>
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
    if (!empty($creditMemoServices)) {
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
                        <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalPrice = 0;
                    $totalDiscount = 0;
                    foreach ($creditMemoServices as $creditMemoService) {
                        $unit_price = number_format($creditMemoService['CreditMemoService']['unit_price'], $priceDecimal);
                        $discount = $creditMemoService['CreditMemoService']['discount_amount'];
                        $total_price = number_format($creditMemoService['CreditMemoService']['total_price'] - $discount, $priceDecimal);
                        $totalPrice += $creditMemoService['CreditMemoService']['total_price'] - $discount;
                        ?>
                        <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo $creditMemoService['Service']['name']; ?></td>
                            <td><?php echo $creditMemoService['CreditMemoService']['note']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($creditMemoService['CreditMemoService']['qty'], $priceDecimal); ?></td>
                            <td style="text-align: right"><?php echo $unit_price; ?></td>
                            <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                            <td style="text-align: right"><?php echo $total_price; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="first" colspan="6" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
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
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($creditMemo['CreditMemo']['total_amount'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            if ($creditMemo['CreditMemo']['discount'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($creditMemo['CreditMemo']['discount_percent'] > 0){ ?>(<?php echo number_format($creditMemo['CreditMemo']['discount_percent'],  2); ?>%)<?php } ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($creditMemo['CreditMemo']['discount'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            ?>
            <?php
            if ($creditMemo['CreditMemo']['mark_up'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_MARK_UP; ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($creditMemo['CreditMemo']['mark_up'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            if($creditMemo['CreditMemo']['total_vat'] > 0){
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right"><b style="font-size: 17px;"><?php echo TABLE_VAT ?> (<?php echo number_format($creditMemo['CreditMemo']['vat_percent'], $priceDecimal); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($creditMemo['CreditMemo']['total_vat'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
                }
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($creditMemo['CreditMemo']['total_amount'] + $creditMemo['CreditMemo']['mark_up'] + $creditMemo['CreditMemo']['total_vat'] - $creditMemo['CreditMemo']['discount'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($cmWsales)) {
    ?>
    <div>
        <fieldset>
            <legend>Apply To Invoice</legend>
            <table class="table" >
                <tr>
                    <th class="first"></th>
                    <th><?php echo TABLE_APPLY_DATE; ?></th>
                    <th><?php echo TABLE_INVOICE_CODE; ?></th>
                    <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?> </th>
                    <th><?php echo GENERAL_PAID; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach ($cmWsales as $cmWsale) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo dateShort($cmWsale['CreditMemoWithSale']['apply_date']); ?></td>
                    <td style="text-align: right;"><?php echo $cmWsale['SalesOrder']['so_code']; ?></td>
                    <td style="text-align: right;">
                        <?php
                        $sql = mysql_query("SELECT * FROM customers WHERE id = " . $cmWsale['SalesOrder']['customer_id'] . " AND is_active = 1 LIMIT 1");
                        $customer = mysql_fetch_array($sql);
                        echo $customer['customer_code'] . "-" . $customer['name_kh'] . " (" . $customer['name'].")";
                        ?>
                    </td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['SalesOrder']['total_amount'] - ($cmWsale['SalesOrder']['discount']), $priceDecimal); ?></td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemoWithSale']['total_price'], $priceDecimal); ?></td>
                    <td style="text-align: right;"><a href="" class="btnVoidCmWSale" rel="<?php echo $cmWsale['CreditMemoWithSale']['id']; ?>" name="<?php echo $cmWsale['SalesOrder']['so_code']; ?>"><img alt="Void" onmouseover="Tip('Void')" src="<?php echo $this->webroot; ?>img/button/delete.png" /></a></td>
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
    if (!empty($creditMemoReceipts)) {
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
                        <th><?php echo TABLE_TOTAL_AMOUNT; ?> </th>
                        <th colspan="2" style="width: 25%;"><?php echo GENERAL_PAID; ?></th>
                        <th><?php echo GENERAL_BALANCE; ?> </th>
                        <th style="width:10%;"></th>
                    </tr>
                    <?php
                    $index = 0;
                    $leght = count($creditMemoReceipts);
                    foreach ($creditMemoReceipts as $creditMemoReceipt) {
                        ?>
                        <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo date("d/m/Y", strtotime($creditMemoReceipt['CreditMemoReceipt']['pay_date'])); ?></td>
                            <td>
                                <?php
                                if ($allowPrintReceipt) {
                                    echo $html->link($creditMemoReceipt['CreditMemoReceipt']['receipt_code'], array("action" => "#"), array("class" => "printReceipt", "rel" => $creditMemoReceipt['CreditMemoReceipt']['id']));
                                } else {
                                    $creditMemoReceipt['CreditMemoReceipt']['receipt_code'];
                                }
                                ?>
                            </td>
                            <td style="text-align: right;">1 <?php echo $creditMemo['CurrencyCenter']['symbol']; ?> = <?php echo number_format($creditMemoReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $creditMemoReceipt['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['total_amount'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_us'], $priceDecimal); ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_other'], $priceDecimal); ?> <?php echo $creditMemoReceipt['CurrencyCenter']['symbol']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['balance'], $priceDecimal); ?></td>
                            <td>
                                <?php
                                if ($allowPrintReceipt) {
                                    echo "<a href='#' class='btnPrintReceiptPayCreditMemo' rel='{$creditMemoReceipt['CreditMemoReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                                }
                                if ($index == $leght) {
                                    echo "&nbsp; <a href='{$creditMemoReceipt['CreditMemoReceipt']['receipt_code']}' name='{$creditMemoReceipt['CreditMemoReceipt']['credit_memo_id']}' class='btnDeleteRpCreditMemo' rel='{$creditMemoReceipt['CreditMemoReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
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
    if ($creditMemo['CreditMemo']['balance'] == 0) {
        $styleDisplay = " style='display:none'";
    } else {
        $styleDisplay = "";
    }
    ?>
    <div<?php echo $styleDisplay; ?>>
        <div style="float: left;">
            <table style="width: 250px;">
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="data[CreditMemo][exchange_rate_id]" id="CreditMemoExchangeRateId" />
                        <?php
                        $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$creditMemo['CreditMemo']['branch_id']);
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
                                    <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $creditMemo['CurrencyCenter']['symbol']; ?> =</td>
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
                    <td colspan="2" style="text-align: right;"><input type="button" id="btnApplyInvoice" value="Apply to an invoice" style="height: 30px;" cm-id="<?php echo $creditMemo['CreditMemo']['id']; ?>" /></td>
               </tr>
               <tr>
                   <td>&nbsp;</td>
               </tr>
                <tr>
                    <td><label for="CreditMemoPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('pay_date', array('style' => 'text-align:center; width: 120px;', 'readonly' => true)); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="CreditMemoAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                    <td>
                        <?php
                        echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                        echo $this->Form->hidden('total_amount', array('value' => $creditMemo['CreditMemo']['balance']));
                        ?> <?php echo $creditMemo['CurrencyCenter']['symbol']; ?>
                    </td>
                </tr>
                <tr style="display: none;">
                    <td>
                        <select name="data[CreditMemo][currency_center_id]" id="exchangeRateCM" style="width: 150px;">
                            <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$creditMemo['CreditMemo']['branch_id']);
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
                        <span class="paidOtherCurrencySymbolCM"></span>
                    </td>
                </tr>
                <tr class="DivCreditMemoAging">
                    <td style="vertical-align: top"><label for="CreditMemoAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanCreditMemoAging">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('aging', array('id' => 'CreditMemoAging' . $rand, 'class' => 'CreditMemoAging', 'style' => 'width: 120px;')); ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="remainLeft red" style="text-align: right;"></div>
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
                    <td class="first" > 
                        <?php echo $creditMemo['CurrencyCenter']['symbol']; ?>
                    </td>
                    <td>
                        <span class="paidOtherCurrencySymbolCM"></span>
                    </td>
                </tr>
                <tr>
                    <td class="first"> 
                        <?php echo $this->Form->text('balance_us', array('style' => 'text-align:center; width: 200px;' , 'class' => 'float', 'readonly' => true, 'value' => number_format($creditMemo['CreditMemo']['balance'], $priceDecimal))); ?> 
                    </td>
                    <td> 
                        <input type="text" name="data[CreditMemo][balance_other]" id="CreditMemoBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
</fieldset>
<br />
<div class="buttons" <?php echo $styleDisplay; ?>>
    <button type="submit" class="positive paidAgingCM" >
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>