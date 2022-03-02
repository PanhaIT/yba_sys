<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$priceDecimal = 2;
$allowSO      = false;
$allowSaleRep = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (18, 40, 46) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 18){
        if($rowSetting['is_checked'] == 1){
            $allowSO = true;
        }
    } else if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    } else if($rowSetting['id'] == 46){
        if($rowSetting['is_checked'] == 1){
            $allowSaleRep = true;
        }
    }
}
include('includes/function.php');
$this->element('check_access');
echo $this->element('prevent_multiple_submit');
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
// Authentication
$allowEditInvDis  = checkAccess($user['User']['id'], $this->params['controller'], 'invoiceDiscount');
$allowAddCustomer = checkAccess($user['User']['id'], 'customers', 'quickAdd');
?>
<script type="text/javascript">
    var fieldRequireInvoice = ['SalesOrderLocationGroupId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
//        $('#dialog').dialog('destroy');
        clearOrderDetailSo();
        // Hide Branch
        $("#SalesOrderBranchId").filterOptions('com', '<?php echo $this->data['SalesOrder']['company_id']; ?>', '<?php echo $this->data['SalesOrder']['branch_id']; ?>');
        $("#SalesOrderLocationGroupId").chosen({ width: 200});
        $("#SalesOrderEditForm").validationEngine();
        $(".saveSales").click(function(){
            if(checkBfSaveSo() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_SAVE; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 300,
                    height: 'auto',
                    position:'center',
                    closeOnEscape: false,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").hide();
                    },
                    buttons: {
                        '<?php echo ACTION_NO; ?>': function() {
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_SAVE; ?>': function() {
                            // Action Click Save
                            $("#SalesOrderIsPreview").val('0');
                            $("#SalesOrderEditForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }else{
                return false;
            }
        });
        
        $(".saveSalesPreview").click(function(){
            if(checkBfSaveSo() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SAVE_BEFORE_PREVIEW; ?></p>');
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
                        '<?php echo ACTION_NO; ?>': function() {
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_YES; ?>': function() {
                            $("#SalesOrderIsPreview").val('1');
                            $("#SalesOrderEditForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });

        $(".float").autoNumeric({mDec: <?php echo $priceDecimal; ?>});

        $("#SalesOrderEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#SalesOrderIsPreview").val() == '1'){
                    $(".txtSaveSalesPreview").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSaveSales").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequireInvoice) == false){
                    alertSelectRequireField();
                    $(".saveSales").removeAttr('disabled');
                    return false;
                }
                var access  = true;
                var confirm = false;
                if(timeBarcodeSO == 2){
                    access = false;
                }else{
                    var totalAmount  = replaceNum($("#SalesOrderTotalAmount").val());
                    var cusLimitBal  = replaceNum($("#limitBalance").val());
                    var cusLimitInv  = replaceNum($("#limitInvoice").val());
                    var cusBalUsed   = replaceNum($("#totalBalanceUsed").val());
                    var cusInvUsed   = replaceNum($("#totalInvoiceUsed").val());
                    var cusTotalBal  = (totalAmount + cusBalUsed);
                    if((cusInvUsed > cusLimitInv && cusLimitInv > 0) || (cusTotalBal > cusLimitBal && cusLimitBal > 0)){
                        confirm = true;
                    }
                }
                if(confirm == true && $("#SalesOrderIsApprove").val() == 1){
                    // Set Approve Confirm
                    confirmConditionCustomer();
                    access  = false;
                }
                // Check Access Condition
                if(access == true){
                    $("#SalesOrderOrderDate, .startSales").datepicker("option", "dateFormat", "yy-mm-dd");
                    $(".float").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                    $(".floatQty").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                    $(".saveSales, .saveSalesPreview").attr("disabled", true);
                }else{
                    $(".saveSales").removeAttr('disabled');
                    return false;
                }
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Sales Invoice', 'Edit', 2, result.responseText);
                backSalesOrder();
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
                    errorSaveSO();
                }else if(result.error == "2"){
                    errorOutStock();
                    var listOutStock = result.listOutStock.split("-");
                    var obj = "";
                    $(".tblSOList").each(function(){
                        if($(this).find("input[name='product_id[]']").val() != ""){
                            obj = $(this);
                            $.each(listOutStock, function(i, val){
                                if(val != ""){
                                    if(obj.find("input[name='product_id[]']").val() == val.split("|")[0]){
                                        obj.css("background","#fc8b8b");
                                        obj.attr('data-stock',"Total qty can sale is "+val.split("|")[1]+" "+val.split("|")[2]+"");
                                        obj.find("input[name='inv_qtySO[]']").val(val.split("|")[1]);
                                    }
                                }
                            });
                        }
                    });
                    $(".tblSOList").mouseover(function(){
                        var text = $(this).attr('data-stock');
                        Tip(text);
                    });
                }else{
                    createSysAct('Sales Invoice', 'Edit', 1, '');
                    if($("#SalesOrderIsPreview").val() == '1'){
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
                        $("#SalesOrderPreviewId").val(result.id);
                        $(".txtSaveSalesPreview").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".saveSales, .saveSalesPreview").attr("disabled", false);
                        $("#SalesOrderOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        backSalesOrder();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoice" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button></div>');
                        $(".printInvoice").click(function(){
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
                    }
                }
            }
        });
        
        $(".searchCustomerSales").click(function(){
            if(checkOrderDate() == true && $("#SalesOrderCompanyId").val() != '' && $("#SalesOrderBranchId").val() != ''){
                searchAllCustomerSales();
            }
        });

        $(".deleteCustomerSales").click(function(){
            if($(".tblSOList").find(".product_id").val() == undefined){
                removeCustomerSales();
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CUSTOMER_ON_SALES; ?>";
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
                            removeCustomerSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $("#SalesOrderCustomerName").focus(function(){
            checkOrderDate();
        });
        
        $('#SalesOrderCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#SalesOrderCustomerName").autocomplete("<?php echo $this->base . "/customers/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            }
        }).result(function(event, value){
            var strName = value.toString().split(".*")[1].split("-");
            var customerId    = value.toString().split(".*")[0];
            var customerCode  = value.toString().split(".*")[2];
            var paymentTermId = value.toString().split(".*")[7];
            var customerDisc  = value.toString().split(".*")[10];
            $("#SalesOrderProduct").attr("disabled", false);
            $("#SalesOrderCustomerId").val(customerId);
            $("#SalesOrderCustomerName").val(customerCode+" - "+strName[1]);
            $("#SalesOrderCustomerName").attr("readonly","readonly");
            $("#SalesOrderPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
            $(".searchCustomerSales").hide();
            $(".deleteCustomerSales").show();
            // Discount
            if(customerDisc > 0){
                $("#SalesOrderDiscountPercent").val(customerDisc);
                $("#salesLabelDisPercent").text(customerDisc);
                $("#btnRemoveSalesTotalDiscount").show();
            } else {
                $("#SalesOrderDiscountPercent").val(0);
                $("#salesLabelDisPercent").text(0);
                $("#btnRemoveSalesTotalDiscount").hide();
            }
            if(value.toString().split(".*")[9] != ''){
                // Check Price Type Customer
                customerPriceTypeSales(value.toString().split(".*")[9], 0);
            }
            $.ajax({
                dataType: 'json',
                type: "POST",
                url: "<?php echo $this->base . '/sales_orders'; ?>/customerCondition/"+customerId+"/<?php echo $this->data['SalesOrder']['id']; ?>",
                beforeSend: function(){
                },
                success: function(msg){
                    if(msg.error == 0){
                        // Condition
                        var limitBalance = msg.limit_balance;
                        var limitInvoice = msg.limit_invoice;
                        var totalBalanceUsed = msg.balance_used;
                        var totalInvoiceUsed = msg.invoice_used;
                        // Set Condition
                        $("#limitBalance").val(limitBalance);
                        $("#limitInvoice").val(limitInvoice);
                        $("#totalBalanceUsed").val(totalBalanceUsed);
                        $("#totalInvoiceUsed").val(totalInvoiceUsed);
                    }else{
                        $(".deleteCustomerSales").click();
                    }
                }
            });
        });
        
        // Action Location Group
        $.cookie("SalesOrderLocationGroupId", $("#SalesOrderLocationGroupId").val(), {expires : 7,path    : '/'});
        $("#SalesOrderLocationGroupId").change(function(){
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie("SalesOrderLocationGroupId", $("#SalesOrderLocationGroupId").val(), {expires : 7,path    : '/'});
            }else{
                var question = "<?php echo MESSAGE_CONFRIM_CHANGE_LOCATION_GROUP; ?>";
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
                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie("SalesOrderLocationGroupId", $("#SalesOrderLocationGroupId").val(), {expires : 7,path    : '/'});
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesOrderLocationGroupId").val($.cookie("SalesOrderLocationGroupId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Action Order Date
        $('#SalesOrderOrderDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = "minDate", instance = $( this ).data( "datepicker" );
                var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );
                $(".startSales").not( this ).datepicker( "option", option, date );
            }
        }).unbind("blur");
        $("#SalesOrderOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#SalesOrderOrderDate").datepicker("option", "maxDate", 0);
        $.cookie("SalesOrderOrderDate", $("#SalesOrderOrderDate").val(), {expires : 7,path    : '/'});
        $("#SalesOrderOrderDate").change(function(){
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie("SalesOrderOrderDate", $("#SalesOrderOrderDate").val(), {expires : 7,path    : '/'});
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_CHANGE_DATE; ?>";
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
                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie("SalesOrderOrderDate", $("#SalesOrderOrderDate").val(), {expires : 7,path    : '/'});
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesOrderOrderDate").val($.cookie("SalesOrderOrderDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackSalesOrder").click(function(event){
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
                        backSalesOrder();
                    }
                }
            });
        });
        
        // Company Action
        $.cookie('companyIdSales', $("#SalesOrderCompanyId").val(), { expires: 7, path: "/" });
        $("#SalesOrderCompanyId").change(function(){
            var obj    = $(this);
            var vatCal = $(this).find("option:selected").attr("vat-opt");
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie('companyIdSales', obj.val(), { expires: 7, path: "/" });
                $("#SalesOrderVatCalculate").val(vatCal);
                $("#SalesOrderBranchId").filterOptions('com', obj.val(), '');
                $("#SalesOrderBranchId").change();
                resetFormSales();
                checkVatCompanySales('');
                checkChartAccountSales();
                changeInputCSSSales();
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
                            $.cookie('companyIdSales', obj.val(), { expires: 7, path: "/" });
                            $("#SalesOrderVatCalculate").val(vatCal);
                            $("#SalesOrderBranchId").filterOptions('com', obj.val(), '');
                            $("#SalesOrderBranchId").change();
                            resetFormSales();
                            checkVatCompanySales('');
                            checkChartAccountSales();
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesOrderCompanyId").val($.cookie("companyIdSales"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdSalesOrder', $("#SalesOrderBranchId").val(), { expires: 7, path: "/" });
        $("#SalesOrderBranchId").change(function(){
            var obj = $(this);
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie('branchIdSalesOrder', obj.val(), { expires: 7, path: "/" });
                branchChangeSalesOrder(obj);
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
                            $.cookie('branchIdSalesOrder', obj.val(), { expires: 7, path: "/" });
                            branchChangeSalesOrder(obj);
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesOrderBranchId").val($.cookie("branchIdSalesOrder"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        <?php
        if($allowAddCustomer){
        ?>
        $("#addCustomerSales").click(function(){
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
                                                createSysAct('Sales Invoice', 'Quick Add Customer', 2, result);
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
                                                createSysAct('Sales Invoice', 'Quick Add Customer', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Customer
                                                    $("#SalesOrderCustomerId").val(result.id);
                                                    $("#SalesOrderCustomerName").val(result.name);
                                                    $("#SalesOrderCustomerName").attr("readonly","readonly");
                                                    $("#SalesOrderPaymentTermId").find("option[value='"+result.term+"']").attr("selected", true);
                                                    $(".searchCustomerSales").hide();
                                                    $(".deleteCustomerSales").show();
                                                    if(result.price != ''){
                                                        // Check Price Type Customer
                                                        customerPriceTypeSales(result.price, 0);
                                                    }
                                                    $.ajax({
                                                        dataType: 'json',
                                                        type: "POST",
                                                        url: "<?php echo $this->base . '/sales_orders'; ?>/customerCondition/"+result.id+"/<?php echo $this->data['SalesOrder']['id']; ?>",
                                                        beforeSend: function(){
                                                        },
                                                        success: function(msg){
                                                            if(msg.error == 0){
                                                                // Condition
                                                                var limitBalance = msg.limit_balance;
                                                                var limitInvoice = msg.limit_invoice;
                                                                var totalBalanceUsed = msg.balance_used;
                                                                var totalInvoiceUsed = msg.invoice_used;
                                                                // Set Condition
                                                                $("#limitBalance").val(limitBalance);
                                                                $("#limitInvoice").val(limitInvoice);
                                                                $("#totalBalanceUsed").val(totalBalanceUsed);
                                                                $("#totalInvoiceUsed").val(totalInvoiceUsed);
                                                            }else{
                                                                $(".deleteCustomerSales").click();
                                                            }
                                                        }
                                                    });
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
        // Action Search Quotation
        $(".searchQuotationSales").click(function(){
            var locationGroup = $("#SalesOrderLocationGroupId").val();
            if(locationGroup != "" && $("#SalesOrderCompanyId").val() != "" && $("#SalesOrderBranchId").val() != ""){
                searchQuotationSales();
            }
        });
        
        // Action Delete Quotaion
        $(".deleteQuotationSales").click(function(){
            $("#SalesOrderQuotationId").val('');
            $("#SalesOrderQuotationNumber").val('');
            $("#SalesOrderQuotationNumber").removeAttr('readonly');
            $(".searchQuotationSales").show();
            $(".deleteQuotationSales").hide();
        });
        
        // Sales Rep
        $("#SalesOrderSalesRepName").autocomplete("<?php echo $this->base . "/employees/searchEmployee"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            }
        }).result(function(event, value){
            var employeeId   = value.toString().split(".*")[0];
            var employeeCode = value.toString().split(".*")[1];
            var employeeName = value.toString().split(".*")[2];
            var objId     = $(this).parent().find("input[type='hidden']").attr("id");
            var objName   = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            $("#"+objId).val(employeeId);
            $("#"+objName).val(employeeCode+"-"+employeeName);
            $("#"+objName).attr('readonly','readonly');
            $("."+btnSearch).hide();
            $("."+btnDelete).show();
        });
        
        $(".searchSalesRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            searchEmployeeSales(objId, objName, btnSearch, btnDelete);
        });
        
        $(".deleteSalesRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            removeEmployeeSales(objId, objName, btnSearch, btnDelete);
        });
        <?php if($allowSO == true){ ?>
        // Action Search Order
        $(".searchOrderSales").click(function(){
            var locationGroup = $("#SalesOrderLocationGroupId").val();
            if(locationGroup != ""){
                searchOrderSales();
            }
        });
        
        // Action Delete Order
        $(".deleteOrderSales").click(function(){
            $("#SalesOrderOrderId").val('');
            $("#SalesOrderOrderNumber").val('');
            $("#SalesOrderOrderNumber").removeAttr('readonly');
            $(".searchOrderSales").show();
            $(".deleteOrderSales").hide();
        });
        <?php } ?>
        // VAT Filter
        checkVatCompanySales('<?php echo $this->data['SalesOrder']['vat_setting_id']; ?>');
        // A/R Filter
        checkChartAccountSales();
        // Load Detail
        loadOrderDetailSO(1);
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
    });
    <?php if($allowSO == true){ ?>
    function searchOrderSales(){
        var companyId  = $("#SalesOrderCompanyId").val();
        var branchId   = $("#SalesOrderBranchId").val();
        var customerId = $("#SalesOrderCustomerId").val();
        if(companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/order/"+companyId+"/"+branchId+"/"+customerId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeSO == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_ORDER_INFO; ?>',
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
                                if($("input[name='chkOrder']:checked").val()){
                                    $("#SalesOrderOrderId").val($("input[name='chkOrder']:checked").val());
                                    $("#SalesOrderOrderNumber").val($("input[name='chkOrder']:checked").attr("rel"));
                                    $("#SalesOrderOrderNumber").attr('readonly', 'readonly');
                                    $(".searchOrderSales").hide();
                                    $(".deleteOrderSales").show();
                                    var orderId = $("input[name='chkOrder']:checked").val();
                                    var locationGroup = $("#SalesOrderLocationGroupId").val();
                                    var salesId    = 0;
                                    var discount   = $("input[name='chkOrder']:checked").attr("dis");
                                    var disPercent = $("input[name='chkOrder']:checked").attr("disp");
                                    // Insert Customer
                                    // Condition
                                    var limitBalance = $("input[name='chkOrder']:checked").attr("limit-balance");
                                    var limitInvoice = $("input[name='chkOrder']:checked").attr("limit-invoice");
                                    var totalBalanceUsed = $("input[name='chkOrder']:checked").attr("bal-used");
                                    var totalInvoiceUsed = $("input[name='chkOrder']:checked").attr("inv-used");
                                    // Customer
                                    var customerId     = $("input[name='chkOrder']:checked").attr('cus-id');
                                    var customerCode   = $("input[name='chkOrder']:checked").attr("cus-code");
                                    var customerNameEn = $("input[name='chkOrder']:checked").attr("name-us");
                                    var paymentTermId  = $("input[name='chkOrder']:checked").attr("term-id");
                                    // Set Customer
                                    $("#SalesOrderProduct").attr("disabled", false);
                                    $("#SalesOrderCustomerId").val(customerId);
                                    $("#SalesOrderCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#SalesOrderCustomerName").attr('readonly','readonly');
                                    $("#SalesOrderPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
                                    $(".searchCustomerSales").hide();
                                    $(".deleteCustomerSales").show();
                                    // Set Condition
                                    $("#limitBalance").val(limitBalance);
                                    $("#limitInvoice").val(limitInvoice);
                                    $("#totalBalanceUsed").val(totalBalanceUsed);
                                    $("#totalInvoiceUsed").val(totalInvoiceUsed);
                                    // Set Discount
                                    $("#SalesOrderDiscountUs").val(discount);
                                    $("#SalesOrderDiscountPercent").val(disPercent);
                                    if(disPercent > 0){
                                        $("#salesLabelDisPercent").html('('+disPercent+'%)');
                                    } else {
                                        $("#salesLabelDisPercent").html('');
                                    }
                                    if(discount > 0 || disPercent > 0){
                                        $("#btnRemoveSalesTotalDiscount").show();
                                    }
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkOrder']:checked").attr("ptype");
                                    var priceTypeSelected = $("input[name='chkOrder']:checked").attr("ptype-id");
                                    customerPriceTypeSales(priceTypeList, priceTypeSelected);
                                    // VAT 
                                    var vatSettingId = $("input[name='chkOrder']:checked").attr("vid");
                                    var vatCalculate = $("input[name='chkOrder']:checked").attr("vcal");
                                    var vatPercent   = $("input[name='chkOrder']:checked").attr("vper");
                                    $("#SalesOrderVatCalculate").val(vatCalculate);
                                    $("#SalesOrderVatSettingId").find("option[value='"+vatSettingId+"']").attr("selected", true);
                                    $("#SalesOrderVatPercent").val(vatPercent);
                                    var vatAccId     = $("#SalesOrderVatSettingId").find("option:selected").attr("acc");
                                    $("#SalesOrderVatChartAccountId").val(vatAccId);
                                    changeLblVatCalSales();
                                    // Deposit
                                    var deposit    = $("input[name='chkOrder']:checked").attr("deposit");
                                    $("#SalesOrderTotalDeposit").val(deposit);
                                    // Get Product From Request Stock
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromOrder/"+orderId+"/"+locationGroup+"/"+salesId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblSO").html('');
                                                // Insert Row List
                                                $("#tblSO").append(tr);
                                                // Event Key Table List
                                                checkEventSO();
                                                // Calculate Total Amount
                                                calcTotalAmountSales();
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
    <?php } ?>
    function removeEmployeeSales(objId, objName, btnSearch, btnDelete){
        $("#"+objId).val('');
        $("#"+objName).val('');
        $("#"+objName).removeAttr('readonly','readonly');
        $("."+btnSearch).show();
        $("."+btnDelete).hide();
    }
    
    function searchEmployeeSales(objId, objName, btnSearch, btnDelete){
        var companyId = $("#SalesOrderCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                timeBarcodeSO == 1;
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo TABLE_SALES_REP; ?>',
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
                            if($("input[name='chkEmployee']:checked").val()){
                                $("#"+objId).val($("input[name='chkEmployee']:checked").val());
                                $("#"+objName).val($("input[name='chkEmployee']:checked").attr("rel"));
                                $("#"+objName).attr('readonly','readonly');
                                $("."+btnSearch).hide();
                                $("."+btnDelete).show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function searchQuotationSales(){
        var companyId  = $("#SalesOrderCompanyId").val();
        var branchId   = $("#SalesOrderBranchId").val();
        var customerId = $("#SalesOrderCustomerId").val();
        if(companyId != "" && branchId != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/quotation/"+companyId+"/"+branchId+"/"+customerId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeSO == 1;
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
                                    $("#SalesOrderQuotationId").val($("input[name='chkQuotation']:checked").val());
                                    $("#SalesOrderQuotationNumber").val($("input[name='chkQuotation']:checked").attr("rel"));
                                    $("#SalesOrderQuotationNumber").attr('readonly', 'readonly');
                                    $(".searchQuotationSales").hide();
                                    $(".deleteQuotationSales").show();
                                    var quoteId = $("input[name='chkQuotation']:checked").val();
                                    var locationGroup = $("#SalesOrderLocationGroupId").val();
                                    var salesId    = 0;
                                    var discount   = $("input[name='chkQuotation']:checked").attr("dis");
                                    var disPercent = $("input[name='chkQuotation']:checked").attr("dis-per");
                                    // Insert Customer
                                    // Condition
                                    var limitBalance = $("input[name='chkQuotation']:checked").attr("limit-balance");
                                    var limitInvoice = $("input[name='chkQuotation']:checked").attr("limit-invoice");
                                    var totalBalanceUsed = $("input[name='chkQuotation']:checked").attr("bal-used");
                                    var totalInvoiceUsed = $("input[name='chkQuotation']:checked").attr("inv-used");
                                    // Customer
                                    var customerId     = $("input[name='chkQuotation']:checked").attr('cus-id');
                                    var customerCode   = $("input[name='chkQuotation']:checked").attr("cus-code");
                                    var customerNameEn = $("input[name='chkQuotation']:checked").attr("name-us");
                                    var paymentTermId  = $("input[name='chkQuotation']:checked").attr("term-id");
                                    // Set Customer
                                    $("#SalesOrderProduct").attr("disabled", false);
                                    $("#SalesOrderCustomerId").val(customerId);
                                    $("#SalesOrderCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#SalesOrderCustomerName").attr('readonly','readonly');
                                    $("#SalesOrderPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
                                    $(".searchCustomerSales").hide();
                                    $(".deleteCustomerSales").show();
                                    // Set Condition
                                    $("#limitBalance").val(limitBalance);
                                    $("#limitInvoice").val(limitInvoice);
                                    $("#totalBalanceUsed").val(totalBalanceUsed);
                                    $("#totalInvoiceUsed").val(totalInvoiceUsed);
                                    // Set Discount
                                    $("#SalesOrderDiscountUs").val(discount);
                                    $("#SalesOrderDiscountPercent").val(disPercent);
                                    if(disPercent > 0){
                                        $("#salesLabelDisPercent").html('('+disPercent+'%)');
                                    } else {
                                        $("#salesLabelDisPercent").html('');
                                    }
                                    if(discount > 0 || disPercent > 0){
                                        $("#btnRemoveSalesTotalDiscount").show();
                                    }
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkQuotation']:checked").attr("ptype");
                                    var priceTypeSelected = $("input[name='chkQuotation']:checked").attr("ptype-id");
                                    customerPriceTypeSales(priceTypeList, priceTypeSelected);
                                    // VAT 
                                    var vatSettingId = $("input[name='chkQuotation']:checked").attr("vid");
                                    var vatCalculate = $("input[name='chkQuotation']:checked").attr("vcal");
                                    var vatPercent   = $("input[name='chkQuotation']:checked").attr("vper");
                                    $("#SalesOrderVatCalculate").val(vatCalculate);
                                    $("#SalesOrderVatSettingId").find("option[value='"+vatSettingId+"']").attr("selected", true);
                                    $("#SalesOrderVatPercent").val(vatPercent);
                                    var vatAccId     = $("#SalesOrderVatSettingId").find("option:selected").attr("acc");
                                    $("#SalesOrderVatChartAccountId").val(vatAccId);
                                    changeLblVatCalSales();
                                    // Get Product From Request Stock
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromQuote/"+quoteId+"/"+locationGroup+"/"+salesId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblSO").html('');
                                                // Insert Row List
                                                $("#tblSO").append(tr);
                                                // Event Key Table List
                                                checkEventSO();
                                                // Calculate Total Amount
                                                calcTotalAmountSales();
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
    
    function removeCustomerSales(){
        $("#SalesOrderCustomerId").val("");
        $("#SalesOrderCustomerName").val("");
        $("#SalesOrderCustomerName").removeAttr("readonly");
        $(".searchCustomerSales").show();
        $(".deleteCustomerSales").hide();
        $("#typeOfPriceSO").filterOptions("comp", $("#SalesOrderCompanyId").val(), "0");
        $("#SalesOrderPaymentTermId").find("option[value='']").attr("selected", true);
    }
    
    function branchChangeSalesOrder(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#SalesOrderSoCode").val("<?php echo date("y"); ?>"+mCode);
        $("#SalesOrderCurrencyCenterId").val(currency);
        $(".lblSymbolSales").html(currencySymbol);
    }
    
    function getTotalDiscountSales(){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/sales_orders/invoiceDiscount"; ?>",
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo GENERAL_DISCOUNT; ?>',
                    resizable: false,
                    modal: true,
                    width: 450,
                    height: 180,
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            var totalDisAmt     = replaceNum($("#inputInvoiceDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputInvoiceDisPer").val());
                            $("#SalesOrderDiscountUs").val(totalDisAmt);
                            $("#SalesOrderDiscountPercent").val(totalDisPercent);
                            calcTotalAmountSales();
                            if(totalDisPercent > 0){
                                $("#salesLabelDisPercent").html('('+totalDisPercent+'%)');
                            } else {
                                $("#salesLabelDisPercent").html('');
                            }
                            if(totalDisAmt > 0 || totalDisPercent > 0){
                                $("#btnRemoveSalesTotalDiscount").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function changeLblVatCalSales(){
        var vatCal = $("#SalesOrderVatCalculate").val();
        $("#lblSalesOrderVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblSalesOrderVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblSalesOrderVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedSales(){
        var vatPercent = replaceNum($("#SalesOrderVatSettingId").find("option:selected").attr("rate"));
        var vatAccId   = replaceNum($("#SalesOrderVatSettingId").find("option:selected").attr("acc"));
        $("#SalesOrderVatPercent").val((vatPercent).toFixed(<?php echo $priceDecimal; ?>));
        $("#SalesOrderVatChartAccountId").val(vatAccId);
    }
    
    function checkVatCompanySales(selected){
        // VAT Filter
        $("#SalesOrderVatSettingId").filterOptions('com-id', $("#SalesOrderCompanyId").val(), selected);
    }
    
    function backSalesOrder(){
        oCache.iCacheLower = -1;
        oTableSalesOrder.fnDraw(false);
        $("#SalesOrderEditForm").validationEngine("hideAll");
        var rightPanel = $("#SalesOrderEditForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function resetFormSales(){
        // Customer
        $(".deleteCustomerSales").click();
    }
    
    function checkChartAccountSales(){
        // A/R Filter
        $("#SalesOrderChartAccountId").filterOptions('company_id', $("#SalesOrderCompanyId").val(), '');
        
        if($("#SalesOrderCompanyId").val() != ''){
            <?php
            if(!empty($arAccountId)){
            ?>
            $("#SalesOrderChartAccountId option[value='<?php echo $arAccountId; ?>']").attr('selected', true);
            <?php
            }
            ?>
        }
    }
    
    function confirmConditionCustomer(){
        var question = "<?php echo MESSAGE_CUSTOMER_INVOICE_HAVE_LIMIT_CONDITON." ".MESSAGE_CONFRIM_CUSTOMER_CONTINUE_SALE; ?>";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
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
                '<?php echo ACTION_OK; ?>': function() {
                    // Set Approve Confirm
                    $("#SalesOrderIsApprove").val(0);
                    // Action Click Save
                    $("#SalesOrderEditForm").submit();
                    $(this).dialog("close");
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function checkOrderDate(){
        if($("#SalesOrderOrderDate").val() == ''){
            $("#SalesOrderOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerSales(field, rules, i, options){
        if($("#SalesOrderCustomerId").val() == "" || $("#SalesOrderCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }

    function loadOrderDetailSO(reload){
        if($("#SalesOrderOrderDate").val() != ''){
            var salesId = 0;
            if(reload == 1){
                salesId = <?php echo $this->data['SalesOrder']['id']; ?>;
            }
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetail/"+salesId,
                beforeSend: function(){
                    if(salesId == 0){
                        $("#tblSO").html('');
                        $("#SalesOrderTotalAmount").val("0.00");
                        $("#SalesOrderDiscountPercent").val("0");
                        $("#SalesOrderSubTotalAmount").val("0.00");
                        $("#SalesOrderStatus").removeAttr("disabled");
                    }
                    $(".orderDetailSales").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".orderDetailSales").html(msg);
                    $(".footerFormSales").show();
                    <?php if($allowEditInvDis){ ?>
                    // Action Total Discount Amount
                    $("#SalesOrderDiscountUs").click(function(){
                        getTotalDiscountSales();
                    });
                    <?php } ?>

                    $("#btnRemoveSalesTotalDiscount").click(function(){
                        $("#SalesOrderDiscountUs").val(0);
                        $("#SalesOrderDiscountPercent").val(0);
                        $(this).hide();
                        $("#salesLabelDisPercent").html('');
                        calcTotalAmountSales();
                    });
                    
                    // Action VAT Status
                    $("#SalesOrderVatSettingId").change(function(){
                        checkVatSelectedSales();
                        calcTotalAmountSales();
                    });
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        } else {
            clearOrderDetailSo();
        }
        
    }

    function clearOrderDetailSo(){
        $(".orderDetailSales").html("");
        $(".footerFormSales").hide();
    }
    
    function searchAllCustomerSales(){
        var companyId = $("#SalesOrderCompanyId").val();
        if(companyId != '' && $("#SalesOrderBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeSO == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_CUSTOMER_MANAGEMENT_INFO; ?>',
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
                                if($("input[name='chkCustomer']:checked").val()){
                                    // Condition
                                    var limitBalance = $("input[name='chkCustomer']:checked").attr("limit-balance");
                                    var limitInvoice = $("input[name='chkCustomer']:checked").attr("limit-invoice");
                                    var totalBalanceUsed = $("input[name='chkCustomer']:checked").attr("bal-used");
                                    var totalInvoiceUsed = $("input[name='chkCustomer']:checked").attr("inv-used");
                                    // Customer
                                    var customerId     = $("input[name='chkCustomer']:checked").val();
                                    var customerCode   = $("input[name='chkCustomer']:checked").attr("rel");
                                    var customerNameEn = $("input[name='chkCustomer']:checked").attr("name-us");
                                    var paymentTermId  = $("input[name='chkCustomer']:checked").attr("term-id");
                                    var customerDisc   = $("input[name='chkCustomer']:checked").attr("discount");
                                    // Set Customer
                                    $("#SalesOrderPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
                                    $("#SalesOrderProduct").attr("disabled", false);
                                    $("#SalesOrderCustomerId").val(customerId);
                                    $("#SalesOrderCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#SalesOrderCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerSales").hide();
                                    $(".deleteCustomerSales").show();
                                    // Set Condition
                                    $("#limitBalance").val(limitBalance);
                                    $("#limitInvoice").val(limitInvoice);
                                    $("#totalBalanceUsed").val(totalBalanceUsed);
                                    $("#totalInvoiceUsed").val(totalInvoiceUsed);
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkCustomer']:checked").attr("ptype");
                                    if(priceTypeList != ''){
                                        customerPriceTypeSales(priceTypeList, 0);
                                    }
                                    // Discount
                                    if(customerDisc > 0){
                                        $("#SalesOrderDiscountPercent").val(customerDisc);
                                        $("#salesLabelDisPercent").text(customerDisc);
                                        $("#btnRemoveSalesTotalDiscount").show();
                                    } else {
                                        $("#SalesOrderDiscountPercent").val(0);
                                        $("#salesLabelDisPercent").text(0);
                                        $("#btnRemoveSalesTotalDiscount").hide();
                                    }
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function checkBfSaveSo(){
        $("#SalesOrderCustomerName").removeClass("validate[required]");
        $("#SalesOrderCustomerName").addClass("validate[required,funcCall[checkCustomerSales]]");
        var formName = "#SalesOrderEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            $("#SalesOrderCustomerName").removeClass("validate[required,funcCall[checkCustomerSales]]");
            $("#SalesOrderCustomerName").addClass("validate[required]");
            return false;
        }else{
            if($(".tblSOList").find(".product").val() == undefined){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an order first.</p>');
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
                            $(this).dialog("close");
                        }
                    }
                });
                $("#SalesOrderCustomerName").removeClass("validate[required,funcCall[checkCustomerSales]]");
                $("#SalesOrderCustomerName").addClass("validate[required]");
                return false;
            }else{
                return true;
            }
        }
    }
    
    function errorSaveSO(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            closeOnEscape: false,
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    var rightPanel=$("#SalesOrderEditForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableSalesOrder.fnDraw(false);
                }
            }
        });
    }
    
    function errorOutStock(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SOME_PRODUCT_OUT_OF_STOCK; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            closeOnEscape: false,
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".saveSales").removeAttr("disabled");
                    $(".txtSaveSales").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function changeInputCSSSales(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchCustomerSales").hide();
        $(".searchQuotationSales").hide();
        $(".searchOrderSales").hide();
        $(".searchSalesRep").hide();
        // Div for Search Product, Service, Misc
        $("#divSearchSales").css("visibility", "hidden");
        if($("#SalesOrderCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#SalesOrderCustomerName").val() == ''){
                $(".searchCustomerSales").show();
            }
            if($("#SalesOrderQuotationNumber").val() == ''){
                $(".searchQuotationSales").show();
            }
            if($("#SalesOrderOrderNumber").val() == ''){
                $(".searchOrderSales").show();
            }
            if($("#SalesOrderSalesRepName").val() == ''){
                $(".searchSalesRep").show();
            }
            // Div for Search Product, Service, Misc
            $("#divSearchSales").css("visibility", "visible");
        } else {
            $(".lblSymbolSales").html('');
        }
        // Label
        $("#SalesOrderEditForm").find("label").removeAttr("class");
        $("#SalesOrderEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'SalesOrderCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#SalesOrderEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#SalesOrderEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'SalesOrderCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolSales").removeClass(cssRemove);
        $(".lblSymbolSales").addClass(cssStyle);
        $(".lblSymbolSalesPercent").removeClass(cssRemove);
        $(".lblSymbolSalesPercent").addClass(cssStyle);
        // Input Readonly
        $("#SalesOrderCustomerName").attr("readonly", readonly);
        $("#SalesOrderSalesRepName").attr("readonly", readonly);
        $("#SalesOrderQuotationNumber").attr("readonly", readonly);
        $("#SalesOrderOrderNumber").attr("readonly", readonly);
        $("#SalesOrderMemo").attr("readonly", readonly);
        $("#SalesOrderQuotationNumber").attr("readonly", readonly);
        $("#searchProductUpcSales").attr("readonly", readonly);
        $("#searchProductSkuSales").attr("readonly", readonly);
        // Check Price Type With Company
        checkPriceTypeSales();
        // Put label VAT Calculate
        changeLblVatCalSales();
        // Check VAT Default
        getDefaultVatSales();
    }
    
    function checkPriceTypeSales(){
        // Price Type Filter
        $("#typeOfPriceSO").filterOptions('comp', $("#SalesOrderCompanyId").val(), '');
        
        if($("#SalesOrderCompanyId").val() == ''){
            $("#typeOfPriceSO").prepend('<option value="" comp=""><?php echo INPUT_SELECT;; ?></option>');
            $("#typeOfPriceSO option[value='']").attr("selected", true);
        } else {
            $("#typeOfPriceSO option[value='']").remove();
        }
    }
    
    function customerPriceTypeSales(priceTypeList, priceTypeSelected){
        var priceTypeShow = '';
        var priceType = "";
        if(priceTypeList != ''){
            var selected  = 0;
            priceType = priceTypeList.toString().split(",");
            $("#typeOfPriceSO option").each(function(){
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
        
        $("#typeOfPriceSO option").removeAttr("selected");
        $("#typeOfPriceSO option[value='"+priceTypeShow+"']").attr("selected", true);
        $.cookie("typePriceSO", $("#typeOfPriceSO").val(), {expires : 7, path : '/'});
        if(priceTypeSelected == "0"){
            changePriceTypeSO();
        }
    }
    
    function getDefaultVatSales(){
        var vatDefault = $("#SalesOrderCompanyId option:selected").attr("vat-d");
        $("#SalesOrderVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedSales();
    }
</script>
<?php echo $this->Form->create('SalesOrder', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<input type="hidden" value="1" id="SalesOrderIsApprove" name="data[SalesOrder][is_approve]" />
<input type="hidden" value="<?php echo $this->data['SalesOrder']['currency_center_id']; ?>" id="SalesOrderCurrencyCenterId" name="data[SalesOrder][currency_center_id]" />
<input type="hidden" id="limitBalance" />
<input type="hidden" id="limitInvoice" />
<input type="hidden" id="totalBalanceUsed" />
<input type="hidden" id="totalInvoiceUsed" />
<input type="hidden" value="<?php echo $this->data['SalesOrder']['id']; ?>" name="data[id]" id="SalesOrderPreviewId" />
<input type="hidden" value="0" id="SalesOrderIsPreview" />
<input type="hidden" value="<?php echo $this->data['SalesOrder']['vat_calculate']; ?>" name="data[SalesOrder][vat_calculate]" id="SalesOrderVatCalculate" />
<?php
$pType = "";
$sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$this->data['SalesOrder']['customer_id']." GROUP BY cgroup_id)");
if(mysql_num_rows($sqlPriceType)){
    $rowPriceType = mysql_fetch_array($sqlPriceType);
    $pType = $rowPriceType[0];
}
?>

<input type="hidden" id="priceTypeCustomerSales" value="<?php echo $pType; ?>" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderSalesOrder">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="SOTop">
    <legend><?php __(MENU_SALES_ORDER_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="saleInvoiceInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="SalesOrderOrderDate"><?php echo TABLE_INVOICE_DATE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="SalesOrderSoCode"><?php echo TABLE_INVOICE_CODE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="SalesOrderQuotationNumber"><?php echo MENU_QUOTATION; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php 
                                $dateOrder = dateShort($this->data['SalesOrder']['order_date']);
                                echo $this->Form->text('order_date', array('value' => $dateOrder, 'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:80%')); 
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('so_code', array('class' => 'validate[required]', 'style' => 'width:80%', 'readonly' => true)); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('quotation_id'); ?>
                                <?php 
                                echo $this->Form->text('quotation_number', array('style' => 'width:70%')); 
                                if($this->data['SalesOrder']['quotation_number'] != ''){
                                    $searchQuo = 'display: none;';
                                    $deleteQuo = '';
                                }else{
                                    $searchQuo = '';
                                    $deleteQuo = 'display: none;';
                                }
                                ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px; <?php echo $searchQuo; ?>" class="searchQuotationSales" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer; <?php echo $deleteQuo; ?>" class="deleteQuotationSales" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><?php if($allowSO == true){ ?><label for="SalesOrderOrderNumber"><?php echo MENU_ORDER; ?></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <?php if($allowSO == true){ ?>
                            <!-- Sales Order -->
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('order_id'); ?>
                                <?php 
                                echo $this->Form->text('order_number', array('style' => 'width:70%')); 
                                if($this->data['SalesOrder']['order_number'] != ''){
                                    $searchOrder = 'display: none;';
                                    $deleteOrder = '';
                                }else{
                                    $searchOrder = '';
                                    $deleteOrder = 'display: none;';
                                }
                                ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px; <?php echo $searchOrder; ?>" class="searchOrderSales" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer; <?php echo $deleteOrder; ?>" class="deleteOrderSales" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                            <?php } else { echo $this->Form->hidden('order_id'); echo $this->Form->hidden('order_number'); } ?>
                            <!-- Company -->
                            <div class="inputContainer" style="width:100%; display: none;">
                                <select name="data[SalesOrder][company_id]" id="SalesOrderCompanyId" class="validate[required]" style="width: 90%;">
                                    <?php
                                    if(count($companies) != 1){
                                    ?>
                                    <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($companies AS $company){
                                        $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 25 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                        $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                    ?>
                                    <option vat-d="<?php echo $rowVATDefault[0]; ?>" <?php if($company['Company']['id'] == $this->data['SalesOrder']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 16%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><?php if(count($branches) > 1){ ?><label for="SalesOrderBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[SalesOrder][branch_id]" id="SalesOrderBranchId" class="validate[required]" style="width: 90%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" <?php if($branch['Branch']['id'] == $this->data['SalesOrder']['branch_id']){ ?>selected="selected"<?php } ?> com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['inv_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 16%; vertical-align: top;" rowspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesOrderMemo"><?php echo TABLE_MEMO; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('memo', array('style' => 'width:90%; height: 70px;')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> 
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td colspan="2"><label for="SalesOrderCustomerName"><?php echo TABLE_CUSTOMER; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="SalesOrderPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="inputContainer" style="width:100%">
                                <?php
                                echo $this->Form->hidden('customer_id');
                                if($allowAddCustomer){
                                ?>
                                <div class="addnewSmall" style="float: left;">
                                    <?php echo $this->Form->text('customer_name', array('value' => $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name'], 'readonly' => true, 'class' => 'validate[required]', 'style' => 'width: 285px; border: none;')); ?>
                                    <img alt="<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addCustomerSales" onmouseover="Tip('<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                </div>
                                <?php
                                } else {
                                    echo $this->Form->text('customer_name', array('value' => $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name'], 'readonly' => true, 'class' => 'validate[required]', 'style' => 'width:320px'));
                                }
                                ?>
                                &nbsp;&nbsp;<img alt="<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchCustomerSales" onmouseover="Tip('<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" style="cursor: pointer; height: 22px;" class="deleteCustomerSales" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('payment_term_id', array('style' => 'width:90%;', 'label' => false, 'empty' => INPUT_SELECT, 'class' => 'validate[required]', 'div' => false)); ?>
                            </div> 
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><?php if($allowSaleRep == true){ ?><label for="SalesOrderSalesRep"><?php echo TABLE_SALES_REP; ?></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if($allowSaleRep == true){
                                $salesRepName  = "";
                                $sqlEmployee = mysql_query("SELECT id, name FROM employees WHERE id = ".$this->data['SalesOrder']['sales_rep_id']);
                                while($rowEmployee=mysql_fetch_array($sqlEmployee)){
                                    if($rowEmployee['id'] == $this->data['SalesOrder']['sales_rep_id']){
                                        $salesRepName = $rowEmployee['name'];
                                    }
                                }
                            ?>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('sales_rep_id'); ?>
                                <?php echo $this->Form->text('sales_rep_name', array('value' => $salesRepName, 'readonly' => true, 'style' => 'width:70%')); ?>
                                <img alt="Search" search="1" align="absmiddle" style="<?php if($salesRepName != ''){ ?>display: none;<?php } ?> cursor: pointer; width: 22px; height: 22px;" class="searchSalesRep" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" search="0" align="absmiddle" style="<?php if($salesRepName == ''){ ?>display: none;<?php } ?> cursor: pointer;" class="deleteSalesRep" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                            <?php
                            } else {
                                echo $this->Form->hidden('sales_rep_id');
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><?php if(count($locationGroups) > 1){ ?><label for="SalesOrderLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($locationGroups) == 1){ ?>display: none;<?php } ?>">
                                <?php echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'label' => false)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div class="orderDetailSales" style="margin-top: 5px; text-align: center;"></div>
<div class="footerFormSales" style="display: none;">
    <div style="float: left; width: 26%;">
        <div class="buttons">
            <a href="#" class="positive btnBackSalesOrder">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveSales" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveSales"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveSalesPreview" >
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSaveSalesPreview"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width: 72%;">
        <table style="width:100%">
            <tr>
                <td style="width:8%; text-align: right;"><label for="SalesOrderTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width:10%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('total_amount', array('value' => number_format($this->data['SalesOrder']['total_amount'], 2), 'readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; height:15px; font-size:12px; font-weight: bold')); ?> <span class="lblSymbolSales"><?php echo $this->data['CurrencyCenter']['symbol'] ?></span>
                    </div>
                </td>
                <td style="width:7%; text-align: right;"><label for="SalesOrderDiscountPercent"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width:16%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('discount_percent', array('value' => number_format($this->data['SalesOrder']['discount_percent'], 0), 'class' => 'float')); ?>
                        <?php echo $this->Form->text('discount_us', array('value' => number_format($this->data['SalesOrder']['discount'], 2), 'style' => 'width: 45%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float')); ?> <span class="lblSymbolSales"><?php echo $this->data['CurrencyCenter']['symbol'] ?></span>
                        <span id="salesLabelDisPercent"><?php if($this->data['SalesOrder']['discount_percent'] > 0){ echo '('.number_format($this->data['SalesOrder']['discount_percent'], 2).'%)'; } ?></span>
                        <?php if($allowEditInvDis){ ?><img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemoveSalesTotalDiscount" align="absmiddle" style="cursor: pointer; <?php if($this->data['SalesOrder']['discount'] <=0){ ?>display: none;<?php } ?>" onmouseover="Tip('Remove Discount')" /><?php } ?>
                    </div>
                </td>
                <td style="width:15%; text-align: right;">
                    <label for="SalesOrderVatSettingId" id="lblSalesOrderVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="SalesOrderVatSettingId" name="data[SalesOrder][vat_setting_id]" style="width: 70%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00" acc=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id, chart_account_id FROM vat_settings WHERE is_active = 1 AND type = 1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" acc="<?php echo $rowVat['chart_account_id']; ?>" <?php if($this->data['SalesOrder']['vat_setting_id'] == $rowVat['id']){ ?>selected="selected"<?php } ?>><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width:8%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('vat_chart_account_id', array('value'=> $this->data['SalesOrder']['vat_chart_account_id'])); ?>
                        <?php echo $this->Form->hidden('total_vat', array('class' => 'float')); ?>
                        <?php echo $this->Form->text('vat_percent', array('readonly' => true, 'style' => 'width: 45%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value' => number_format($this->data['SalesOrder']['vat_percent'], 2))); ?> <span class="lblSymbolSalesPercent">(%)</span>
                    </div>
                </td>
                <td style="width:6%; text-align: right;"><label for="SalesOrderSubTotalAmount"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width:12%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('sub_total_amount', array('value' => number_format($this->data['SalesOrder']['total_amount'] - $this->data['SalesOrder']['discount'] + $this->data['SalesOrder']['total_vat'], 2), 'readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; height:15px; font-size:12px; font-weight: bold')); ?> <span class="lblSymbolSales"><?php echo $this->data['CurrencyCenter']['symbol'] ?></span>
                    </div>
                </td>
                <td style="width: 6%; text-align: right;"><label for="SalesOrderTotalDeposit"><?php echo TABLE_DEPOSIT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_deposit', array('class' => 'float validate[required]', 'style' => 'width: 75%; font-size:12px; font-weight: bold', 'value' => number_format($this->data['SalesOrder']['total_deposit'], 2))); ?> <span class="lblSymbolSales"><?php echo $this->data['CurrencyCenter']['symbol'] ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>