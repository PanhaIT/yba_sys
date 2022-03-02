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
    var timeSearchOrder = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        clearOrderDetailOrder();
        loadOrderDetailOrder(1);
        loadAutoCompleteOff();
        // Hide Branch
        $("#OrderBranchId").filterOptions('com', '<?php echo $this->data['Order']['company_id']; ?>', '<?php echo $this->data['Order']['branch_id']; ?>');
        $("#OrderEditForm").validationEngine();
        $(".saveOrder").click(function(){
            if(checkBfSaveOrder() == true){
                $("#OrderIsPreview").val('0');
                return true;
            }else{
                return false;
            }
        });
        
        $(".savePreviewOrder").click(function(){
            if(checkBfSaveOrder() == true){
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
                            $("#OrderIsPreview").val(1);
                            $("#OrderEditForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });

        $("#OrderEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#OrderIsPreview").val() == '1'){
                    $(".txtSavePreviewOrder").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSaveOrder").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#OrderOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $(".saveOrder, .savePreviewOrder").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Order', 'Edit', 2, result.responseText);
                backOrder();
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
                    errorSaveOrder();
                }else{
                    createSysAct('Order', 'Edit', 1, '');
                    if($("#OrderIsPreview").val() == '1'){
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
                        $("#OrderPreviewId").val(result.id);
                        $(".txtSavePreviewOrder").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".saveOrder, .savePreviewOrder").attr("disabled", false);
                        $("#OrderOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        backOrder();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceOrder" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_SALES_ORDER; ?></span></button><button type="submit" class="positive printInvoiceOrderNoHead" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_SALES_ORDER; ?> No Header</span></button></div>');
                        $(".printInvoiceOrder").click(function(){
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
                        $(".printInvoiceOrderNoHead").click(function(){
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
        
        $("#OrderCustomerName").focus(function(){
            checkOrderDate();
        });
        
        $(".searchCustomerOrder").click(function(){
            if(checkOrderDate() == true && $("#OrderCompanyId").val() != ''){
                searchAllCustomerOrder();
            }
        });

        $("#OrderCustomerName").autocomplete("<?php echo $this->base ."/".$this->params['controller']. "/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyOrder(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyOrder(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            }
        }).result(function(event, value){
            $("#OrderProduct").attr("disabled", false);
            $("#OrderCustomerId").val(value.toString().split(".*")[0]);
            $("#OrderCustomerName").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]);
            $("#OrderCustomerName").attr("readonly","readonly");
            $(".searchCustomerOrder").hide();
            $(".deleteCustomerOrder").show();
            if(value.toString().split(".*")[4] != ''){
                // Check Price Type Customer
                customerPriceTypeOrder(value.toString().split(".*")[4], 0);
            }
        });

        $(".deleteCustomerOrder").click(function(){
            if($(".tblOrderList").find(".product_id").val() == undefined && $("#OrderQuotationNumber").val() == ""){
                removeCustomerOrder();
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CUSTOMER_ON_ORDER; ?>";
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
                            removeCustomerOrder();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        $('#OrderCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $('#OrderOrderDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#OrderOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#OrderOrderDate").datepicker("option", "maxDate", 0);
        
        $(".btnBackOrder").click(function(event){
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
                        backOrder();
                    }
                }
            });
        });
        
        // Company Action
        $.cookie('companyIdOrder', $("#OrderCompanyId").val(), { expires: 7, path: "/" });
        $("#OrderCompanyId").change(function(){
            var obj   = $(this);
            var vatCal = $(this).find("option:selected").attr("vat-opt");
            if($(".tblOrderList").find(".product_id").val() == undefined){
                $.cookie('companyIdOrder', obj.val(), { expires: 7, path: "/" });
                $("#OrderVatCalculate").val(vatCal);
                $("#OrderBranchId").filterOptions('com', obj.val(), '');
                $("#OrderBranchId").change();
                resetFormOrder();
                checkVatCompanyOrder();
                changeInputCSSOrder();
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
                            $.cookie('companyIdOrder', obj.val(), { expires: 7, path: "/" });
                            $("#OrderVatCalculate").val(vatCal);
                            $("#OrderBranchId").filterOptions('com', obj.val(), '');
                            $("#OrderBranchId").change();
                            $("#tblOrder").html('');
                            getTotalAmountOrder();
                            resetFormOrder();
                            checkVatCompanyOrder();
                            changeInputCSSOrder();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#OrderCompanyId").val($.cookie("companyIdOrder"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdOrder', $("#OrderBranchId").val(), { expires: 7, path: "/" });
        $("#OrderBranchId").change(function(){
            var obj = $(this);
            if($(".tblOrderList").find(".product_id").val() == undefined){
                $.cookie('branchIdOrder', obj.val(), { expires: 7, path: "/" });
                branchChangeOrder(obj);
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
                            $.cookie('branchIdOrder', obj.val(), { expires: 7, path: "/" });
                            branchChangeOrder(obj);
                            $("#tblOrder").html('');
                            // Total Discount
                            $("#btnRemoveOrderTotalDiscount").click();
                            getTotalAmountOrder();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#OrderBranchId").val($.cookie("branchIdOrder"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Search Order
        $(".searchQuotationOrder").click(function(){
            if($("#OrderCompanyId").val() != "" && $("#OrderBranchId").val() != ""){
                searchQuotationOrder();
            }
        });
        
        // Action Delete Order
        $(".deleteQuotationOrder").click(function(){
            $("#OrderQuotationId").val('');
            $("#OrderQuotationNumber").val('');
            $("#OrderQuotationNumber").removeAttr('readonly');
            $(".searchQuotationOrder").show();
            $(".deleteQuotationOrder").hide();
        });
        
        <?php
        if($allowAddCustomer){
        ?>
        $("#addCustomerOrder").click(function(){
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
        // VAT Filter
        checkVatCompanyOrder('<?php echo $this->data['Order']['vat_setting_id']; ?>');
    }); // End Document Ready
    
    function removeCustomerOrder(){
        $("#OrderCustomerId").val("");
        $("#OrderCustomerName").val("");
        $("#OrderCustomerName").removeAttr("readonly");
        $(".searchCustomerOrder").show();
        $(".deleteCustomerOrder").hide();
        $("#typeOfPriceOrder").filterOptions("comp", $("#OrderCompanyId").val(), "0");
        $(".deleteQuotationOrder").click();
    }
    
    function branchChangeOrder(obj){
        var mCode = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#OrderOrderCode").val('<?php echo date("y"); ?>'+mCode);
        $("#OrderCurrencyCenterId").val(currency);
        $(".lblSymbolOrder").html(currencySymbol);
    }
    
    function changeLblVatCalOrder(){
        var vatCal = $("#OrderVatCalculate").val();
        $("#lblOrderVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblOrderVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblOrderVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedOrder(){
        var vatPercent = replaceNum($("#OrderVatSettingId").find("option:selected").attr("rate"));
        $("#OrderVatPercent").val((vatPercent).toFixed(2));
    }
    
    function checkVatCompanyOrder(selected){
        // VAT Filter
        $("#OrderVatSettingId").filterOptions('com-id', $("#OrderCompanyId").val(), selected);
    }
    
    function backOrder(){
        $("#OrderAddForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oTableOrder.fnDraw(false);
        var rightPanel = $(".btnBackOrder").parent().parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function searchQuotationOrder(){
        var companyId  = $("#OrderCompanyId").val();
        var branchId   = $("#OrderBranchId").val();
        var customerId = $("#OrderCustomerId").val();
        if(companyId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/quotation/"+companyId+"/"+branchId+"/"+customerId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_QUOTATION_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 900,
                        height: 600,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkQuotation']:checked").val()){
                                    $("#OrderQuotationId").val($("input[name='chkQuotation']:checked").val());
                                    $("#OrderQuotationNumber").val($("input[name='chkQuotation']:checked").attr("rel"));
                                    $("#OrderQuotationNumber").attr('readonly', 'readonly');
                                    $(".searchQuotationOrder").hide();
                                    $(".deleteQuotationOrder").show();
                                    var quoteId = $("input[name='chkQuotation']:checked").val();
                                    // Customer
                                    var customerId     = $("input[name='chkQuotation']:checked").attr('cus-id');
                                    var customerCode   = $("input[name='chkQuotation']:checked").attr("cus-code");
                                    var customerNameEn = $("input[name='chkQuotation']:checked").attr("name-us");
                                    var customerCon    = $("input[name='chkQuotation']:checked").attr("cus-con");
                                    var totalDeposit   = $("input[name='chkQuotation']:checked").attr("deposit");
                                    $("#OrderTotalDeposit").val(totalDeposit);
                                    // Set Customer
                                    $("#OrderProduct").attr("disabled", false);
                                    $("#OrderCustomerId").val(customerId);
                                    $("#OrderCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#OrderCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerOrder").hide();
                                    $(".deleteCustomerOrder").show();
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkQuotation']:checked").attr("ptype");
                                    var priceTypeSelected = $("input[name='chkQuotation']:checked").attr("ptype-id");
                                    customerPriceTypeOrder(priceTypeList, priceTypeSelected);
                                    // Discount
                                    var discountAmt = replaceNum($("input[name='chkQuotation']:checked").attr("dis"));
                                    var discountPer = replaceNum($("input[name='chkQuotation']:checked").attr("disp"));
                                    $("#OrderDiscount").val(discountAmt);
                                    $("#OrderDiscountPercent").val(discountPer);
                                    if(discountPer > 0){
                                        $("#quoteLabelDisPercent").html('('+discountPer+'%)');
                                    } else {
                                        $("#quoteLabelDisPercent").html('');
                                    }
                                    if(discountAmt > 0 || discountPer > 0){
                                        $("#btnRemoveOrderTotalDiscount").show();
                                    }
                                    // VAT 
                                    var vatSettingId = $("input[name='chkQuotation']:checked").attr("vid");
                                    var vatCalculate = $("input[name='chkQuotation']:checked").attr("vcal");
                                    var varPercent   = $("input[name='chkQuotation']:checked").attr("vper");
                                    $("#OrderVatCalculate").val(vatCalculate);
                                    $("#OrderVatSettingId").find("option[value='"+vatSettingId+"']").attr("selected", true);
                                    $("#OrderVatPercent").val(varPercent);
                                    changeLblVatCalOrder();
                                    // Get Product From Request Stock
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromQuote/"+quoteId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblOrder").html('');
                                                // Insert Row List
                                                $("#tblOrder").append(tr);
                                                // Event Key Table List
                                                checkEventOrder();
                                                sortNuTableOrder();
                                                // Calculate Total Amount
                                                getTotalAmountOrder();
                                            }
                                        }
                                    });
                                }
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function getTotalDiscountOrder(){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/orders/invoiceDiscount"; ?>",
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo GENERAL_DISCOUNT; ?>',
                    resizable: false,
                    modal: true,
                    width: 350,
                    height: 180,
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            var totalDisAmt     = replaceNum($("#inputOrderDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputOrderDisPer").val());
                            $("#OrderDiscount").val(totalDisAmt);
                            $("#OrderDiscountPercent").val(totalDisPercent);
                            getTotalAmountOrder();
                            if(totalDisPercent > 0){
                                $("#quoteLabelDisPercent").html('('+totalDisPercent+'%)');
                            } else {
                                $("#quoteLabelDisPercent").html('');
                            }
                            if(totalDisAmt > 0 || totalDisPercent > 0){
                                $("#btnRemoveOrderTotalDiscount").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function checkCompanyOrder(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#OrderCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormOrder(){
        // Customer
        $(".deleteCustomerOrder").click();
        // Total Discount
        $("#btnRemoveOrderTotalDiscount").click();
        // Quotation
        $(".deleteQuotationOrder").click();
        // Note
        $("#OrderNote").val('');
    }
    
    function checkOrderDate(){
        if($("#OrderOrderDate").val() == ''){
            $("#OrderOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerOrder(field, rules, i, options){
        if($("#OrderCustomerId").val() == "" || $("#OrderCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }
    
    function searchAllCustomerOrder(){
        var companyId = $("#OrderCompanyId").val();
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
                                $("#OrderProduct").attr("disabled", false);
                                $("#OrderCustomerId").val($("input[name='chkCMCustomer']:checked").val());
                                $("#OrderCustomerName").val($("input[name='chkCMCustomer']:checked").attr("code")+" - "+$("input[name='chkCMCustomer']:checked").attr("rel"));
                                $("#OrderCustomerName").attr('readonly','readonly');
                                $(".searchCustomerOrder").hide();
                                $(".deleteCustomerOrder").show();
                                // Check Price Type
                                var priceTypeList = $("input[name='chkCMCustomer']:checked").attr("ptype");
                                if(priceTypeList != ''){
                                    customerPriceTypeOrder(priceTypeList, 0);
                                }
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }

    function loadOrderDetailOrder(type){
        var quoteId = 0;
        if(type == 1){
            quoteId = <?php echo $this->data['Order']['id']; ?>;
        }
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetails/"+quoteId,
            beforeSend: function(){
                $(".orderDetailOrder").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                if(quoteId == 0){
                    $("#tblOrder").html("");
                    $("#OrderTotalAmount").val('0.00');
                    $("#OrderDiscountPercent").val('0');
                    $("#OrderDiscount").val('0');
                    $("#OrderTotalAmountSummary").val('0.00');
                }
            },
            success: function(msg){
                $(".orderDetailOrder").html(msg);
                $(".footerSaveOrder").show();
                <?php if($allowEditInvDis){ ?>
                // Action Total Discount Amount
                $("#OrderDiscount").click(function(){
                    if($("#OrderCompanyId").val() != ''){
                        getTotalDiscountOrder();
                    }
                });
                $("#btnRemoveOrderTotalDiscount").click(function(){
                    $("#OrderDiscount").val(0);
                    $("#OrderDiscountPercent").val(0);
                    $(this).hide();
                    $("#quoteLabelDisPercent").html('');
                    getTotalAmountOrder();
                });
                <?php } ?>
                // Action VAT Status
                $("#OrderVatSettingId").change(function(){
                    checkVatSelectedOrder();
                    getTotalAmountOrder();
                });
            }
        });
    }

    function clearOrderDetailOrder(){
        $(".orderDetailOrder").html("");
        $(".footerSaveOrder").hide();
    }

    function checkBfSaveOrder(){
        var formName = "#OrderEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblOrderList").find(".product").val() == undefined){
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
    
    function errorSaveOrder(){
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
                oTableOrder.fnDraw(false);
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function codeDialogOrder(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
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
                $(".saveOrder").removeAttr("disabled");
                $(".txtSaveOrder").html("<?php echo ACTION_SAVE; ?>");
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".saveOrder").removeAttr("disabled");
                    $(".txtSaveOrder").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveDepositOrder(){
        $(".txtSaveOrder").html("<?php echo ACTION_SAVE; ?>");
        $(".saveOrder").removeAttr('disabled');
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
    
    function changeInputCSSOrder(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchCustomerOrder").hide();
        $(".searchQuotationOrder").hide();
        $("#divSearchOrder").css("visibility", "hidden");
        if($("#OrderCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#OrderCustomerName").val() == ''){
                $(".searchCustomerOrder").show();
            }
            if($("#OrderQuotationNumber").val() == ''){
                $(".searchQuotationOrder").show();
            }
            $("#divSearchOrder").css("visibility", "visible");
        } else {
            $(".lblSymbolOrder").html('');
        }
        // Label
        $("#OrderEditForm").find("label").removeAttr("class");
        $("#OrderEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'OrderCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#OrderEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#OrderEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'OrderCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolOrder").removeClass(cssRemove);
        $(".lblSymbolOrder").addClass(cssStyle);
        $(".lblSymbolOrderPercent").removeClass(cssRemove);
        $(".lblSymbolOrderPercent").addClass(cssStyle);
        // Input Readonly
        $("#OrderCustomerName").attr("readonly", readonly);
        $("#OrderNote").attr("readonly", readonly);
        $("#SearchProductPucOrder").attr("readonly", readonly);
        $("#SearchProductSkuOrder").attr("readonly", readonly);
        // Check Price Type With Company
        checkPriceTypeOrder();
        // Put label VAT Calculate
        changeLblVatCalOrder();
        // Check VAT Default
        getDefaultVatOrder();
    }
    
    function checkPriceTypeOrder(){
        // Price Type Filter
        $("#typeOfPriceOrder").filterOptions('comp', $("#OrderCompanyId").val(), '');
        if($("#OrderCompanyId").val() == ''){
            $("#typeOfPriceOrder").prepend('<option value="" comp=""><?php echo INPUT_SELECT; ?></option>');
            $("#typeOfPriceOrder option[value='']").attr("selected", true);
        } else {
            $("#typeOfPriceOrder option[value='']").remove();
        }
    }
    
    function customerPriceTypeOrder(priceTypeList, priceTypeSelected){
        var priceTypeShow = '';
        var priceType = "";
        if(priceTypeList != ''){
            var selected  = 0;
            priceType = priceTypeList.toString().split(",");
            $("#typeOfPriceOrder option").each(function(){
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
                    objType.hide();
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
        
        $("#typeOfPriceOrder option").removeAttr("selected");
        $("#typeOfPriceOrder option[value='"+priceTypeShow+"']").attr("selected", true);
        $.cookie("typePriceOrder", $("#typeOfPriceOrder").val(), {expires : 7,path    : '/'});
        if(priceTypeSelected == "0"){
            changePriceTypeOrder();
        }
    }
    
    function getDefaultVatOrder(){
        var vatDefault = $("#OrderCompanyId option:selected").attr("vat-d");
        $("#OrderVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedOrder();
    }
</script>
<?php echo $this->Form->create('Order', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['Order']['id']; ?>" name="data[id]" id="OrderPreviewId" />
<input type="hidden" value="<?php echo $this->data['Order']['vat_calculate']; ?>" name="data[Order][vat_calculate]" id="OrderVatCalculate" />
<input type="hidden" value="<?php echo $this->data['Order']['currency_center_id']; ?>" name="data[Order][currency_center_id]" id="OrderCurrencyCenterId" />
<input type="hidden" value="0" id="OrderIsPreview" />
<?php 
//echo $this->Form->hidden('old_total_deposit', array('value'=>$this->data['Order']['total_deposit'])); 
$pType = "";
$sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$this->data['Order']['customer_id']." GROUP BY cgroup_id) GROUP BY price_type_id");
if(mysql_num_rows($sqlPriceType)){
    $rowPriceType = mysql_fetch_array($sqlPriceType);
    $pType = $rowPriceType[0];
}
?>
<input type="hidden" id="priceTypeCustomerOrder" value="<?php echo $pType; ?>" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderOrder">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<table cellpadding="0" cellspacing="0" style="width: 100%;" id="orderHeaderForm">
    <tr>
        <td style="width: 50%;">
            <fieldset id="OrderInformation">
                <legend><?php __(MENU_ORDER_INFO); ?></legend>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="0" style="width: 100%;">
                                <tr>
                                    <td style="width: 33%"><label for="OrderOrderDate"><?php echo TABLE_SALE_ORDER_DATE; ?></label></td>
                                    <td style="width: 34%"><label for="OrderOrderCode"><?php echo TABLE_SALES_ORDER_NUMBER; ?> <span class="red">*</span></label></td>
                                    <td style="width: 33%"><label for="OrderQuotationNumber"><?php echo TABLE_QUOTATION_NUMBER; ?></label></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php echo $this->Form->text('order_date', array('value' => dateShort($this->data['Order']['order_date']),'readonly' => 'readonly', 'class' => 'validate[required]', 'style' => 'width:85%')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php echo $this->Form->text('order_code', array('class' => 'validate[required]', 'style' => 'width:85%', 'readonly' => true)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php 
                                            echo $this->Form->hidden('quotation_id'); 
                                            echo $this->Form->text('quotation_number', array('style' => 'width: 75%;')); 
                                            $btnSearchQuo = '';
                                            $btnDeleteQuo = 'display: none;';
                                            if($this->data['Order']['quotation_id'] != ''){
                                                $btnSearchQuo = 'display: none;';
                                                $btnDeleteQuo = '';
                                            }
                                            ?>
                                            <img alt="Search" align="absmiddle" style="<?php echo $btnSearchQuo; ?>cursor: pointer; width: 22px; height: 22px;" class="searchQuotationOrder" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                            <img alt="Delete" align="absmiddle" style="<?php echo $btnDeleteQuo; ?>cursor: pointer;" class="deleteQuotationOrder" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td rowspan="2" style="width: 50%;">
                            <table cellpadding="0" style="width: 100%;">
                                <tr>
                                    <td style="width: 33%"><?php if(count($branches) > 1){ ?><label for="OrderBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                                    <td style="width: 33%;"><?php if(count($locationGroups) > 1){ ?><label for="SalesOrderLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span><?php } ?></td>
                                    <td><label for="OrderNote"><?php echo TABLE_MEMO; ?></label></td>
                                </tr>
                                <tr>
                                    <td style=" vertical-align: top;">
                                        <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                            <select name="data[Order][branch_id]" id="OrderBranchId" class="validate[required]" style="width: 90%;">
                                                <?php
                                                if(count($branches) != 1){
                                                ?>
                                                <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                                <?php
                                                }
                                                foreach($branches AS $branch){
                                                ?>
                                                <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['so_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td style=" vertical-align: top;">
                                        <div class="inputContainer" style="width:100%; <?php if(count($locationGroups) == 1){ ?>display: none;<?php } ?>">
                                            <?php
                                            echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'label' => false)); 
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php echo $this->Form->input('note', array('label' => false, 'style' => 'width:95%; height: 65px;')); ?>
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
                                    <td><label for="OrderCustomerName"><?php echo TABLE_CUSTOMER; ?> <span class="red">*</span></label></td>
                                    <td style="width: 34%"><?php if(count($companies) > 1){ ?><label for="OrderCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label><?php } ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php
                                            echo $this->Form->hidden('customer_id');
                                            if($allowAddCustomer){
                                            ?>
                                            <div class="addnewSmall" style="float: left; width: 94%;">
                                                <?php echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width: 91%; border: none;', 'value' => $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name'])); ?>
                                                <img alt="<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addCustomerOrder" onmouseover="Tip('<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                            </div>
                                            <?php 
                                            } else {
                                                echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width:90%', 'value' => $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name']));
                                            }
                                            ?>
                                            <img alt="<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px; display: none;" class="searchCustomerOrder" onmouseover="Tip('<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                            <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="deleteCustomerOrder" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                            <select name="data[Order][company_id]" id="OrderCompanyId" class="validate[required]" style="width: 80%;">
                                                <?php
                                                if(count($companies) != 1){
                                                ?>
                                                <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                                <?php
                                                }
                                                foreach($companies AS $company){
                                                    $comSelected = '';
                                                    if($company['Company']['id'] == $this->data['Order']['company_id']){
                                                        $comSelected = 'selected="selected"';
                                                    }
                                                    $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 69 GROUP BY vat_modules.vat_setting_id LIMIT 1");
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
<div class="orderDetailOrder" style=" margin-top: 5px; text-align: center;"></div>
<div class="footerSaveOrder" style="">
    <div style="float: left; width: 26%;">
        <div class="buttons">
            <a href="#" class="positive btnBackOrder">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveOrder" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveOrder"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive savePreviewOrder" >
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSavePreviewOrder"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width:73%;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%; text-align: right;"><label for="OrderTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width: 10%;">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Order']['total_amount'], 2))); ?> <span class="lblSymbolOrder"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                    </div>
                </td>
                <td style="width: 7%; text-align: right;"><label for="OrderDiscount"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width: 16%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('discount_percent', array('class' => 'float', 'value' => number_format($this->data['Order']['discount_percent'], 0))); ?>
                        <?php echo $this->Form->text('discount', array('style' => 'width: 55%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'readonly' => true, 'value' => number_format($this->data['Order']['discount'], 2))); ?> <span class="lblSymbolOrder"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                        <span id="quoteLabelDisPercent"><?php if($this->data['Order']['discount_percent'] > 0){ echo '('.number_format($this->data['Order']['discount_percent'], 2).'%)'; } ?></span>
                        <?php if($allowEditInvDis){ ?><img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemoveOrderTotalDiscount" align="absmiddle" style="cursor: pointer; <?php if($this->data['Order']['discount'] <=0){ ?>display: none;<?php } ?>" onmouseover="Tip('Remove Discount')" /><?php } ?>
                    </div>
                </td>
                <td style="width: 16%; text-align: right;">
                    <label for="OrderVatSettingId" id="lblOrderVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="OrderVatSettingId" name="data[Order][vat_setting_id]" style="width: 70%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id FROM vat_settings WHERE is_active = 1 AND type = 1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" <?php if($this->data['Order']['vat_setting_id'] == $rowVat['id']){ ?>selected="selected"<?php } ?>><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 7%;">
                    <div class="inputContainer" style="width: 100%">
                        <input type="hidden" value="<?php echo $this->data['Order']['total_vat']; ?>" name="data[Order][total_vat]" id="OrderTotalVat" class="float" />
                        <?php echo $this->Form->text('vat_percent', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 45%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Order']['vat_percent'], 2))); ?> <span class="lblSymbolOrderPercent">(%)</span>
                    </div>
                </td>
                <td style="width: 6%; text-align: right;"><label for="OrderTotalAmountSummary"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width: 12%;">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount_summary', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Order']['total_amount'] - $this->data['Order']['discount'] + $this->data['Order']['total_vat'], 2))); ?> <span class="lblSymbolOrder"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                    </div>
                </td>
                <td style="width: 6%; text-align: right;"><label for="OrderTotalDeposit"><?php echo TABLE_DEPOSIT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_deposit', array('class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['Order']['total_deposit'], 2))); ?> <span class="lblSymbolOrder"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>