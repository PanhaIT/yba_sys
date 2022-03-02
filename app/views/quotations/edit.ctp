<?php
$priceDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}

include("includes/function.php");
echo $this->element('prevent_multiple_submit');
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
// Authentication
$this->element('check_access');
$allowEditInvDis = checkAccess($user['User']['id'], $this->params['controller'], 'invoiceDiscount');
$allowAddCustomer  = checkAccess($user['User']['id'], 'customers', 'quickAdd');
?>
<script type="text/javascript">
    var timeSearchQuote = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        clearOrderDetailQuotation();
        loadOrderDetailQuotation(1);
        loadAutoCompleteOff();
        // Hide Branch
        $("#QuotationBranchId").filterOptions('com', '<?php echo $this->data['Quotation']['company_id']; ?>', '<?php echo $this->data['Quotation']['branch_id']; ?>');
        $("#QuotationEditForm").validationEngine();
        $(".saveQuotation").click(function(){
            if(checkBfSaveQuotation() == true){
                $("#QuotationIsPreview").val('0');
                return true;
            }else{
                return false;
            }
        });
        
        $(".savePreviewQuotation").click(function(){
            if(checkBfSaveQuotation() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SAVE_BEFORE_PREVIEW; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
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
                            $("#QuotationIsPreview").val(1);
                            $("#QuotationEditForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });

        $("#QuotationEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#QuotationIsPreview").val() == '1'){
                    $(".txtSavePreviewQuotation").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSaveQuotation").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#QuotationQuotationDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $(".saveQuotation, .savePreviewQuotation").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Quotation', 'Edit', 2, result.responseText);
                backQuotation();
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
                if(result.error == "1"){
                    errorSaveQuotation();
                }else{
                    createSysAct('Quotation', 'Edit', 1, '');
                    if($("#QuotationIsPreview").val() == '1'){
                        // Preview
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.id,
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
                        // Reset Normal
                        $("#QuotationPreviewId").val(result.id);
                        $(".txtSavePreviewQuotation").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".saveQuotation, .savePreviewQuotation").attr("disabled", false);
                        $("#QuotationQuotationDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        backQuotation();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceQuote" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?></span></button><button type="submit" class="positive printInvoiceQuoteNoHead" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?> No Header</span></button></div>');
                        $(".printInvoiceQuote").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.id,
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
                        $(".printInvoiceQuoteNoHead").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.id+"/1",
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
                }
            }
        });
        
        $("#QuotationCustomerName").focus(function(){
            checkQuotationDate();
        });
        
        $(".searchCustomerQuotation").click(function(){
            if(checkQuotationDate() == true && $("#QuotationCompanyId").val() != ''){
                searchAllCustomerQuotation();
            }
        });

        $("#QuotationCustomerName").autocomplete("<?php echo $this->base ."/".$this->params['controller']. "/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyQuote(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyQuote(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            }
        }).result(function(event, value){
            $("#QuotationProduct").attr("disabled", false);
            $("#QuotationCustomerId").val(value.toString().split(".*")[0]);
            $("#QuotationCustomerName").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]);
            $("#QuotationCustomerName").attr("readonly","readonly");
            $(".searchCustomerQuotation").hide();
            $(".deleteCustomerQuotation").show();
            if(value.toString().split(".*")[4] != ''){
                // Check Price Type Customer
                customerPriceTypeQuotation(value.toString().split(".*")[4], 0);
            }
        });

        $(".deleteCustomerQuotation").click(function(){
            if($(".tblQuotationList").find(".product_id").val() == undefined){
                removeCustomerQuotation();
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CUSTOMER; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            removeCustomerQuotation();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        $('#QuotationCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $('#QuotationQuotationDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#QuotationQuotationDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#QuotationQuotationDate").datepicker("option", "maxDate", 0);
        
        $(".btnBackQuotation").click(function(event){
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
                        backQuotation();
                    }
                }
            });
        });
        // Action Company
        $.cookie('companyIdQuotation', $("#QuotationCompanyId").val(), { expires: 7, path: "/" });
        $("#QuotationCompanyId").change(function(){
            var obj    = $(this);
            var vatCal = $(this).find("option:selected").attr("vat-opt");
            if($(".tblQuotationList").find(".product_id").val() == undefined && $("#QuotationCustomerName").val() == ''){
                $.cookie('companyIdQuotation', obj.val(), { expires: 7, path: "/" });
                $("#QuotationVatCalculate").val(vatCal);
                $("#QuotationBranchId").filterOptions('com', obj.val(), '');
                $("#QuotationBranchId").change();
                resetFormQuote();
                checkVatCompanyQuotation();
                changeInputCSSQuotation();
            }else{
                var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_COMPANY; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('companyIdQuotation', obj.val(), { expires: 7, path: "/" });
                            $("#QuotationVatCalculate").val(vatCal);
                            $("#QuotationBranchId").filterOptions('com', obj.val(), '');
                            $("#QuotationBranchId").change();
                            $("#tblQuotation").html('');
                            getTotalAmountQuotation();
                            resetFormQuote();
                            checkVatCompanyQuotation();
                            changeInputCSSQuotation();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#QuotationCompanyId").val($.cookie("companyIdQuotation"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdQuotation', $("#QuotationBranchId").val(), { expires: 7, path: "/" });
        $("#QuotationBranchId").change(function(){
            var obj = $(this);
            if($(".tblQuotationList").find(".product_id").val() == undefined){
                $.cookie('branchIdQuotation', obj.val(), { expires: 7, path: "/" });
                branchChangeQuotation(obj);
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_CHANGE_BRANCH; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('branchIdQuotation', obj.val(), { expires: 7, path: "/" });
                            branchChangeQuotation(obj);
                            $("#tblQuotation").html('');
                            // Total Discount
                            $("#btnRemoveQuotationTotalDiscount").click();
                            getTotalAmountQuotation();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#QuotationBranchId").val($.cookie("branchIdQuotation"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        <?php
        if($this->data['Quotation']['created_by'] == $user['User']['id']){
        ?>
        // User Share
        $(".btnShareQuotation").click(function(){
            var saveSOpt  = $("#QuotationShareSaveOption").val();
            var quoteSOpt = $("#QuotationShareOption").val();
            var quoteUser = $("#QuotationShareUser").val();
            var quoteEcpt = $("#QuotationShareExceptUser").val();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/dashboards/share"; ?>/",
                data:   "option="+quoteSOpt+"&user="+quoteUser+"&except="+quoteEcpt+"&save="+saveSOpt,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo TABLE_SHARE_OPTION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); 
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var shareOption  = $("#ShareOption").val();
                                var userSelected = $("#userShareSelected").val();
                                var saveShareOpt = $("input[name='saveOption']:checked").val();
                                if(shareOption != ''){
                                    $("#QuotationShareSaveOption").val(saveShareOpt);
                                    $("#QuotationShareOption").val(shareOption);
                                    if(shareOption == 3){
                                        $("#QuotationShareUser").val(userSelected);
                                        $("#QuotationShareExceptUser").val('');
                                    } else if(shareOption == 4){
                                        $("#QuotationShareUser").val('');
                                        $("#QuotationShareExceptUser").val(userSelected);
                                    } else {
                                        $("#QuotationShareUser").val('');
                                        $("#QuotationShareExceptUser").val('');
                                    }
                                    if(saveShareOpt == 2){
                                        $.ajax({
                                            type: "POST",
                                            url:    "<?php echo $this->base . "/dashboards/shareSave"; ?>/",
                                            data:   "mtid=68&sp="+shareOption+"&susr="+$("#QuotationShareUser").val()+"&suect="+$("#QuotationShareExceptUser").val(),
                                            beforeSend: function() {
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result) {
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $("#QuotationUserShareId").val(result);
                                            }
                                        });
                                    }
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        <?php
        }
        if($allowAddCustomer){
        ?>
        $("#addCustomerQuotation").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/customers/quickAdd/"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog3").html(msg);
                    $("#dialog3").dialog({
                        title: '<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '550',
                        height: '600',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#CustomerQuickAddForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    if($("#CustomerCgroupId").val() == null || $("#CustomerCgroupId").val() == ''){
                                        alertSelectRequireField();
                                    } else {
                                        $(this).dialog("close");
                                        $.ajax({
                                            dataType: 'json',
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/customers/quickAdd",
                                            data: $("#CustomerQuickAddForm").serialize(),
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            error: function (result) {
                                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                                createSysAct('Quotation', 'Quick Add Customer', 2, result);
                                                $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                $("#dialog1").dialog({
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
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                createSysAct('Quotation', 'Quick Add Customer', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Customer
                                                    $("#QuotationCustomerId").val(result.id);
                                                    $("#QuotationCustomerName").val(result.name);
                                                    $("#QuotationCustomerName").attr("readonly","readonly");
                                                    $(".searchCustomerQuotation").hide();
                                                    $(".deleteCustomerQuotation").show();
                                                    if(result.price != ''){
                                                        // Check Price Type Customer
                                                        customerPriceTypeQuotation(result.price, 0);
                                                    }
                                                } else  if (result.error == 1){
                                                    msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'; 
                                                } else  if (result.error == 2){
                                                    msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                                }
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                                $("#dialog").dialog({
                                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                                    resizable: false,
                                                    modal: true,
                                                    width: 'auto',
                                                    height: 'auto',
                                                    position: 'center',
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
                                    }
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        }
        ?>
        
        checkVatCompanyQuotation('<?php echo $this->data['Quotation']['vat_setting_id']; ?>');
    }); // End Document Ready
    
    function removeCustomerQuotation(){
        $("#QuotationCustomerId").val("");
        $("#QuotationCustomerName").val("");
        $("#QuotationCustomerName").removeAttr("readonly");
        $(".searchCustomerQuotation").show();
        $(".deleteCustomerQuotation").hide();
        $("#typeOfPriceQuotation").filterOptions("comp", $("#QuotationCompanyId").val(), "0");
    }
    
    function branchChangeQuotation(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#QuotationQuotationCode").val('<?php echo date("y"); ?>'+mCode);
        $("#QuotationCurrencyCenterId").val(currency);
        $(".lblSymbolQuotation").html(currencySymbol);
    }
    
    function changeLblVatCalQuotation(){
        var vatCal = $("#QuotationVatCalculate").val();
        $("#lblQuotationVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblQuotationVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblQuotationVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedQuutation(){
        var vatPercent = replaceNum($("#QuotationVatSettingId").find("option:selected").attr("rate"));
        $("#QuotationVatPercent").val((vatPercent).toFixed(<?php echo $priceDecimal; ?>));
    }
    
    function checkVatCompanyQuotation(selected){
        // VAT Filter
        $("#QuotationVatSettingId").filterOptions('com-id', $("#QuotationCompanyId").val(), selected);
    }
    
    function backQuotation(){
        $("#QuotationAddForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oTableQuotation.fnDraw(false);
        var rightPanel = $(".btnBackQuotation").parent().parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function getTotalDiscountQuote(){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/quotations/invoiceDiscount"; ?>",
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo GENERAL_DISCOUNT; ?>',
                    resizable: false,
                    modal: true,
                    width: 400,
                    height: 200,
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            var totalDisAmt     = replaceNum($("#inputQuotationDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputQuotationDisPer").val());
                            $("#QuotationDiscount").val(totalDisAmt);
                            $("#QuotationDiscountPercent").val(totalDisPercent);
                            getTotalAmountQuotation();
                            if(totalDisPercent > 0){
                                $("#quoteLabelDisPercent").html('('+totalDisPercent+'%)');
                            } else {
                                $("#quoteLabelDisPercent").html('');
                            }
                            if(totalDisAmt > 0 || totalDisPercent > 0){
                                $("#btnRemoveQuotationTotalDiscount").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function checkCompanyQuote(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#QuotationCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormQuote(){
        // Customer
        $(".deleteCustomerQuotation").click();
        // Total Discount
        $("#btnRemoveQuotationTotalDiscount").click();
        // Note
        $("#QuotationNote").val('');
    }
    
    function checkQuotationDate(){
        if($("#QuotationQuotationDate").val() == ''){
            $("#QuotationQuotationDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerQuotation(field, rules, i, options){
        if($("#QuotationCustomerId").val() == "" || $("#QuotationCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }
    
    function searchAllCustomerQuotation(){
        var companyId = $("#QuotationCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo MENU_CUSTOMER_MANAGEMENT_INFO; ?>',
                    resizable: false,
                    modal: true,
                    width: 850,
                    height: 500,
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            if($("input[name='chkCMCustomer']:checked").val()){
                                $("#QuotationProduct").attr("disabled", false);
                                $("#QuotationCustomerId").val($("input[name='chkCMCustomer']:checked").val());
                                $("#QuotationCustomerName").val($("input[name='chkCMCustomer']:checked").attr("code")+" - "+$("input[name='chkCMCustomer']:checked").attr("rel"));
                                $("#QuotationCustomerName").attr('readonly','readonly');
                                $(".searchCustomerQuotation").hide();
                                $(".deleteCustomerQuotation").show();
                                // Check Price Type
                                var priceTypeList = $("input[name='chkCMCustomer']:checked").attr("ptype");
                                if(priceTypeList != ''){
                                    customerPriceTypeQuotation(priceTypeList, 0);
                                }
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }

    function loadOrderDetailQuotation(type){
        var quoteId = 0;
        if(type == 1){
            quoteId = <?php echo $this->data['Quotation']['id']; ?>;
        }
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetails/"+quoteId,
            beforeSend: function(){
                $(".orderDetailQuotation").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                if(quoteId == 0){
                    $("#tblQuotation").html("");
                    $("#QuotationTotalAmount").val('0.00');
                    $("#QuotationDiscountPercent").val('0');
                    $("#QuotationDiscount").val('0');
                    $("#QuotationTotalAmountSummary").val('0.00');
                    $("#QuotationTotalDeposit").val('0.00');
                }
            },
            success: function(msg){
                $(".orderDetailQuotation").html(msg);
                $(".footerSaveQuotation").show();
                <?php if($allowEditInvDis){ ?>
                // Action Total Discount Amount
                $("#QuotationDiscount").click(function(){
                    if($("#QuotationCompanyId").val() != ''){
                        getTotalDiscountQuote();
                    }
                });
                <?php } ?>

                $("#btnRemoveQuotationTotalDiscount").click(function(){
                    $("#QuotationDiscount").val(0);
                    $("#QuotationDiscountPercent").val(0);
                    $(this).hide();
                    $("#quoteLabelDisPercent").html('');
                    getTotalAmountQuotation();
                });
                // VAT Setting Change
                $("#QuotationVatSettingId").change(function(){
                    checkVatSelectedQuutation();
                    getTotalAmountQuotation();
                });
            }
        });
    }

    function clearOrderDetailQuotation(){
        $(".orderDetailQuotation").html("");
        $(".footerSaveQuotation").hide();
    }

    function checkBfSaveQuotation(){
        var formName = "#QuotationEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblQuotationList").find(".product").val() == undefined){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an order first.</p>');
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
                return false;
            }else{
                return true;
            }
        }
    }
    
    function errorSaveQuotation(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                var rightPanel=$("#SalesOrderAddForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableQuotation.fnDraw(false);
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function errorSaveDepositQuote(){
        $(".txtSaveQuotation").html("<?php echo ACTION_SAVE; ?>");
        $(".saveQuotation").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_TOTAL_AMOUNT_LESS_THAN_TOTAL_DEPOSIT; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            pprsition:'center',
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
    
    function changeInputCSSQuotation(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchCustomerQuotation").hide();
        $("#divSearchQuotation").css("visibility", "hidden");
        if($("#QuotationCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#QuotationCustomerName").val() == ''){
                $(".searchCustomerQuotation").show();
            }
            $("#divSearchQuotation").css("visibility", "visible");
        } else {
            $(".lblSymbolQuotation").html('');
        }  
        // Label
        $("#QuotationEditForm").find("label").removeAttr("class");
        $("#QuotationEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'QuotationCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#QuotationEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#QuotationEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'QuotationCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolQuotation").removeClass(cssRemove);
        $(".lblSymbolQuotation").addClass(cssStyle);
        $(".lblSymbolQuotationPercent").removeClass(cssRemove);
        $(".lblSymbolQuotationPercent").addClass(cssStyle);
        // Input Readonly
        $("#QuotationCustomerName").attr("readonly", readonly);
        $("#QuotationNote").attr("readonly", readonly);
        $("#SearchProductPucQuotation").attr("readonly", readonly);
        $("#SearchProductSkuQuotation").attr("readonly", readonly);
        // Check Price Type With Company
        checkPriceTypeQuotation();
        // Put label VAT Calculate
        changeLblVatCalQuotation();
        // Check VAT Default
        getDefaultVatQuotation();
    }
    
    function checkPriceTypeQuotation(){
        // Price Type Filter
        $("#typeOfPriceQuotation").filterOptions('comp', $("#QuotationCompanyId").val(), '');
        if($("#QuotationCompanyId").val() == ''){
            $("#typeOfPriceQuotation").prepend('<option value="" comp=""><?php echo INPUT_SELECT;; ?></option>');
            $("#typeOfPriceQuotation option[value='']").attr("selected", true);
        } else {
            $("#typeOfPriceQuotation option[value='']").remove();
        }
    }
    
    function customerPriceTypeQuotation(priceTypeList, priceTypeSelected){
        var priceTypeShow = '';
        var priceType = "";
        if(priceTypeList != ''){
            var selected  = 0;
            priceType = priceTypeList.toString().split(",");
            $("#typeOfPriceQuotation option").each(function(){
                var hide = true;
                var id   = $(this).val();
                var objType = $(this);
                $.each(priceType,function(key, item){
                    var typeId = item.toString();
                    if(id == typeId){
                        hide = false;
                    }
                });
                if(hide == true){
                    $("#typeOfPriceQuotation").showHideDropdownOptions(id, false);
                } else {
                    if(selected == 0){
                        objType.attr("selected", true);
                        selected = 1;
                    }
                }
            });
        }
        
        if(priceTypeSelected != ""){
            priceTypeShow = priceTypeSelected;
        } else {
            priceTypeShow = priceType[0];
        }
        
        $("#typeOfPriceQuotation option").removeAttr("selected");
        $("#typeOfPriceQuotation option[value='"+priceTypeShow+"']").attr("selected", true);
        $.cookie("typePriceQuotation", $("#typeOfPriceQuotation").find("option:selected").val(), {expires : 7, path    : '/'});
        if(priceTypeSelected == "0"){
            changePriceTypeQuotation();
        }
    }
    
    function getDefaultVatQuotation(){
        var vatDefault = $("#QuotationCompanyId option:selected").attr("vat-d");
        $("#QuotationVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedQuutation();
    }
</script>
<?php echo $this->Form->create('Quotation', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['Quotation']['id']; ?>" name="data[id]" id="QuotationPreviewId" />
<input type="hidden" value="<?php echo $this->data['Quotation']['vat_calculate']; ?>" name="data[Quotation][vat_calculate]" id="QuotationVatCalculate" />
<input type="hidden" value="<?php echo $this->data['Quotation']['currency_center_id']; ?>" name="data[Quotation][currency_center_id]" id="QuotationCurrencyCenterId" />
<input type="hidden" value="<?php echo $this->data['Quotation']['share_save_option']; ?>" name="data[Quotation][share_save_option]" id="QuotationShareSaveOption" />
<input type="hidden" value="<?php echo $this->data['Quotation']['user_share_id']; ?>" name="data[Quotation][user_share_id]" id="QuotationUserShareId" />
<input type="hidden" value="<?php echo $this->data['Quotation']['share_option']; ?>" name="data[Quotation][share_option]" id="QuotationShareOption" />
<input type="hidden" value="<?php echo $this->data['Quotation']['share_user']; ?>" name="data[Quotation][share_user]" id="QuotationShareUser" />
<input type="hidden" value="<?php echo $this->data['Quotation']['share_except_user']; ?>" name="data[Quotation][share_except_user]" id="QuotationShareExceptUser" />
<input type="hidden" value="0" id="QuotationIsPreview" />
<?php 
echo $this->Form->hidden('old_total_deposit', array('value'=>$this->data['Quotation']['total_deposit'])); 
$pType = "";
$sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$this->data['Quotation']['customer_id']." GROUP BY cgroup_id) GROUP BY price_type_id");
if(mysql_num_rows($sqlPriceType)){
    $rowPriceType = mysql_fetch_array($sqlPriceType);
    $pType = $rowPriceType[0];
}
?>
<input type="hidden" id="priceTypeCustomerQuotation" value="<?php echo $pType; ?>" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderQuotation">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<table cellpadding="0" cellspacing="0" style="width: 100%;" id="quoteHeaderForm">
    <tr>
        <td style="width: 50%;">
            <fieldset id="QuotationInformation">
                <legend><?php __(MENU_QUOTATION_INFO); ?> <img alt="Share" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="btnShareQuotation" onmouseover="Tip('<?php echo TABLE_SHARE_OPTION; ?>')" src="<?php echo $this->webroot . 'img/button/share.png'; ?>" /></legend>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="0" style="width: 100%;">
                                <tr>
                                    <td style="width: 33%"><label for="QuotationQuotationDate"><?php echo TABLE_QUOTATION_DATE; ?></label></td>
                                    <td style="width: 33%"><label for="QuotationQuotationCode"><?php echo TABLE_QUOTATION_NUMBER; ?> <span class="red">*</span></label></td>
                                    <td style="width: 34%"><?php if(count($branches) > 1){ ?><label for="QuotationBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="inputContainer" style="width:100%;">
                                            <?php echo $this->Form->text('quotation_date', array('value' => dateShort($this->data['Quotation']['quotation_date']),'readonly' => 'readonly', 'class' => 'validate[required]', 'style' => 'width:85%')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%;">
                                            <?php echo $this->Form->text('quotation_code', array('class' => 'validate[required]', 'style' => 'width:85%', 'readonly' => true)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                            <select name="data[Quotation][branch_id]" id="QuotationBranchId" class="validate[required]" style="width: 90%;">
                                                <?php
                                                if(count($branches) != 1){
                                                ?>
                                                <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                                <?php
                                                }
                                                foreach($branches AS $branch){
                                                ?>
                                                <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['quote_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td rowspan="2" style="width: 50%;">
                            <table cellpadding="0" style="width: 100%;">
                                <tr>
                                    <td style="width: 33%;"></td>
                                    <td style="width: 33%;"></td>
                                    <td><label for="QuotationNote"><?php echo TABLE_MEMO; ?></label></td>
                                </tr>
                                <tr>
                                    <td style="width: 33%;"></td>
                                    <td style="width: 33%;"></td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php echo $this->Form->input('note', array('label' => false, 'style' => 'width:90%; height: 60px;')); ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr> 
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="0" style="width: 100%;">
                                <tr>
                                    <td colspan="2"><label for="QuotationCustomerName"><?php echo TABLE_CUSTOMER_NAME; ?> <span class="red">*</span></label></td>
                                    <td style="width: 32%"><?php if(count($companies) > 1){ ?><label for="QuotationCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label><?php } ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php
                                            echo $this->Form->hidden('customer_id');
                                            if($allowAddCustomer){
                                            ?>
                                            <div class="addnewSmall" style="float: left; width: 93%;">
                                                <?php echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width: 91%; border: none;', 'value' => $this->data['Customer']['name'])); ?>
                                                <img alt="<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addCustomerQuotation" onmouseover="Tip('<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                            </div>
                                            <?php 
                                            } else {
                                                echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width:90%', 'value' => $this->data['Customer']['name']));
                                            }
                                            ?>
                                            <img alt="<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px; display: none;" class="searchCustomerQuotation" onmouseover="Tip('<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                            <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="deleteCustomerQuotation" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                            <select name="data[Quotation][company_id]" id="QuotationCompanyId" class="validate[required]" style="width: 85%;">
                                                <?php
                                                if(count($companies) != 1){
                                                ?>
                                                <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                                <?php
                                                }
                                                foreach($companies AS $company){
                                                    $comSelected = '';
                                                    if($company['Company']['id'] == $this->data['Quotation']['company_id']){
                                                        $comSelected = 'selected="selected"';
                                                    }
                                                    $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 68 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                                    $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                                ?>
                                                <option vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>" <?php echo $comSelected; ?>><?php echo $company['Company']['name']; ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<div class="orderDetailQuotation" style=" margin-top: 5px; text-align: center;"></div>
<div class="footerSaveQuotation" style="">
    <div style="float: left; width: 26%;">
        <div class="buttons">
            <a href="#" class="positive btnBackQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveQuotation" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveQuotation"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive savePreviewQuotation" >
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSavePreviewQuotation"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width:73%;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%; text-align: right;"><label for="QuotationTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width: 10%;">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Quotation']['total_amount'], 2))); ?> <span class="lblSymbolQuotation"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                    </div>
                </td>
                <td style="width: 7%; text-align: right;"><label for="QuotationDiscount"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width: 16%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('discount_percent', array('class' => 'float', 'value' => number_format($this->data['Quotation']['discount_percent'], 0))); ?>
                        <?php echo $this->Form->text('discount', array('style' => 'width: 55%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'readonly' => true, 'value' => number_format($this->data['Quotation']['discount'], 2))); ?> <span class="lblSymbolQuotation"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                        <span id="quoteLabelDisPercent"><?php if($this->data['Quotation']['discount_percent'] > 0){ echo '('.number_format($this->data['Quotation']['discount_percent'], 2).'%)'; } ?></span>
                        <?php if($allowEditInvDis){ ?><img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemoveQuotationTotalDiscount" align="absmiddle" style="cursor: pointer; <?php if($this->data['Quotation']['discount'] <=0){ ?>display: none;<?php } ?>" onmouseover="Tip('Remove Discount')" /><?php } ?>
                    </div>
                </td>
                <td style="width: 16%; text-align: right;">
                    <label for="QuotationVatSettingId" id="lblQuotationVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="QuotationVatSettingId" name="data[Quotation][vat_setting_id]" style="width: 75%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id FROM vat_settings WHERE is_active = 1 AND type = 1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" <?php if($this->data['Quotation']['vat_setting_id'] == $rowVat['id']){ ?>selected="selected"<?php } ?>><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 7%;">
                    <div class="inputContainer" style="width: 100%">
                        <input type="hidden" value="<?php echo $this->data['Quotation']['total_vat']; ?>" name="data[Quotation][total_vat]" id="QuotationTotalVat" class="float" />
                        <?php echo $this->Form->text('vat_percent', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 50%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Quotation']['vat_percent'], 2))); ?> <span class="lblSymbolQuotation">(%)</span>
                    </div>
                </td>
                <td style="width: 6%; text-align: right;"><label for="QuotationTotalAmountSummary"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width: 12%;">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount_summary', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 80%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Quotation']['total_amount'] - $this->data['Quotation']['discount'] + $this->data['Quotation']['total_vat'], 2))); ?> <span class="lblSymbolQuotation"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                    </div>
                </td>
                <td style="width: 6%; text-align: right;"><label for="QuotationTotalDeposit"><?php echo TABLE_DEPOSIT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_deposit', array('class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value' => number_format($this->data['Quotation']['vat_percent'], 2))); ?> <span class="lblSymbolOrder"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>