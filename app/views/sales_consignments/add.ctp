<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include('includes/function.php');
$this->element('check_access');
echo $this->element('prevent_multiple_submit');
$queryClosingDate = mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate  = mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
// Authentication
$allowEditTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'editTermsCondition');
$allowEditInvDis = checkAccess($user['User']['id'], $this->params['controller'], 'invoiceDiscount');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        clearOrderDetailSo();
        // Hide Branch
        $("#SalesConsignmentBranchId").filterOptions('com', '0', '');
        $("#SalesConsignmentAddForm").validationEngine();
        $(".saveSales").click(function(){
            if(checkBfSaveSo() == true){
                return true;
            }else{
                return false;
            }
        });

        $(".float").autoNumeric({mDec: 3});

        $("#SalesConsignmentAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveSales").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                var access  = true;
                var confirm = false;
                if(timeSalesConginment == 2){
                    access = false;
                }else{
                    var totalAmount  = replaceNum($("#SalesConsignmentTotalAmount").val());
                    var cusLimitBal  = replaceNum($("#limitBalance").val());
                    var cusLimitInv  = replaceNum($("#limitInvoice").val());
                    var cusBalUsed   = replaceNum($("#totalBalanceUsed").val());
                    var cusInvUsed   = replaceNum($("#totalInvoiceUsed").val());
                    var cusTotalBal  = (totalAmount + cusBalUsed);
                    if((cusInvUsed > cusLimitInv && cusLimitInv > 0) || (cusTotalBal > cusLimitBal && cusLimitBal > 0)){
                        confirm = true;
                    }
                }
                if(confirm == true && $("#SalesConsignmentIsApprove").val() == 1){
                    // Set Approve Confirm
                    confirmConditionCustomer();
                    access  = false;
                }
                // Check Access Condition
                if(access == true){
                    $("#SalesConsignmentOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    $(".float").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                    $(".floatQty").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                }else{
                    $(".saveSales").removeAttr('disabled');
                    return false;
                }
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Sales Invoice', 'Add', 2, result.responseText);
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
                        backSalesConsignment();
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
                if(result.code == "1"){
                    codeDialogSO();
                }else if(result.code == "2"){
                    errorSaveSO();
                }else if(result.code == "3"){
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
                    createSysAct('Sales Invoice', 'Add', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoice" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button></div>');
                    $(".printInvoice").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.so_id,
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
                        close: function(){
                            $(this).dialog({close: function(){}});
                            $(this).dialog("close");
                            backSalesConsignment();
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
        });
        
        $(".searchCustomerSales").click(function(){
            if(checkOrderDate() == true && $("#SalesConsignmentCompanyId").val() != '' && $("#SalesConsignmentBranchId").val() != ''){
                searchAllCustomerSales();
            }
        });

        $(".deleteCustomerSales").click(function(){
            if($(".tblSOList").find(".product_id").val() == undefined && $("#SalesConsignmentConsignmentCode").val() == ""){
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
        
        $("#SalesConsignmentCustomerName").focus(function(){
            checkOrderDate();
        });
        
        $('#SalesConsignmentCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#SalesConsignmentCustomerNumber, #SalesConsignmentCustomerName").autocomplete("<?php echo $this->base . "/sales_consignments/searchCustomer"; ?>", {
            width: 410,
            max: 20,
            scroll: true,
            scrollHeight: 400,
            formatItem: function(data, i, n, value) {
                if(checkCompanySales(value.toString().split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanySales(value.toString().split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            var strName       = value.toString().split(".*")[1].split("-");
            var customerId    = value.toString().split(".*")[0];
            var customerCode  = value.toString().split(".*")[2];
            var paymentTermId = value.toString().split(".*")[3];
            var locationGroupId = value.toString().split(".*")[6];
            $("#SalesConsignmentCustomerId").val(customerId);
            $("#SalesConsignmentCustomerNumber").val(customerCode);
            $("#SalesConsignmentCustomerNumber").attr("readonly","readonly");
            $("#SalesConsignmentCustomerName").val(strName[1]);
            $("#SalesConsignmentCustomerName").attr("readonly","readonly");
            $("#SalesConsignmentPaymentTermId").find("option").removeAttr("selected");
            $("#SalesConsignmentPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
            $(".searchCustomerSales").hide();
            $(".deleteCustomerSales").show();
            // Reset Consignment
            $(".deleteConsignmentSales").click();
            getCustomerContactSales(customerId);
            if(value.toString().split(".*")[5] != ''){
                // Check Price Type Customer
                customerPriceTypeSales(value.toString().split(".*")[5], 0);
            }
            // Set Location Group
            $("#SalesConsignmentLocationGroupId").val(locationGroupId);
            $.ajax({
                dataType: 'json',
                type: "POST",
                url: "<?php echo $this->base . '/sales_orders'; ?>/customerCondition/"+customerId,
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
                        removeCustomerSales();
                    }
                }
            });
        });
        
        $("#SalesConsignmentSalesRepName, #SalesConsignmentCollectorName").autocomplete("<?php echo $this->base . "/employees/searchEmployee"; ?>", {
            width: 410,
            max: 20,
            scroll: true,
            scrollHeight: 400,
            formatItem: function(data, i, n, value) {
                if(checkCompanySales(value.toString().split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanySales(value.toString().split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
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
        
        // Action Search Consignment
        $(".searchConsignmentSales").click(function(){
            searchConsignmentSales();
        });
        
        // Action Delete Consignment
        $(".deleteConsignmentSales").click(function(){
            $("#SalesConsignmentConsignmentId").val('');
            $("#SalesConsignmentConsignmentCode").val('');
            $("#SalesConsignmentConsignmentCode").removeAttr('readonly');
            $(".searchConsignmentSales").show();
            $(".deleteConsignmentSales").hide();
        });
        
        // Action Location Group
        if($.cookie('SalesConsignmentLocationGroupId')!=null){
            $("#SalesConsignmentLocationGroupId").val($.cookie('SalesConsignmentLocationGroupId'));
        }
        
        $("#SalesConsignmentLocationGroupId").change(function(){
            var obj = $(this);
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie("SalesConsignmentLocationGroupId", obj.val(), {expires : 7,path    : '/'});
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
                            $.cookie("SalesConsignmentLocationGroupId", obj.val(), {expires : 7,path    : '/'});
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesConsignmentLocationGroupId").val($.cookie("SalesConsignmentLocationGroupId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Action Order Date
        $('#SalesConsignmentOrderDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#SalesConsignmentOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#SalesConsignmentOrderDate").datepicker("option", "maxDate", 0);
        
        if($.cookie('SalesConsignmentOrderDate')!=null){
            $("#SalesConsignmentOrderDate").val($.cookie('SalesConsignmentOrderDate'));
        }
        
        $("#SalesConsignmentOrderDate").change(function(){
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie("SalesConsignmentOrderDate", $("#SalesConsignmentOrderDate").val(), {
                    expires : 7,
                    path    : '/'
                });
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
                            $.cookie("SalesConsignmentOrderDate", $("#SalesConsignmentOrderDate").val(), {expires : 7,path    : '/'});
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesConsignmentOrderDate").val($.cookie("SalesConsignmentOrderDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackSalesConsignment").click(function(event){
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
                        backSalesConsignment();
                    }
                }
            });
        });
        
        $(".searchSalesRep, .searchSalesDelivery, .searchSalesCollector").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            searchEmployeeSo(objId, objName, btnSearch, btnDelete);
        });
        
        $(".deleteSalesRep, .deleteSalesDelivery, .deleteSalesCollector").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            removeEmployeeSo(objId, objName, btnSearch, btnDelete);
        });
        
        $("#SalesConsignmentCompanyId").change(function(){
            var obj    = $(this);
            var vatCal = $(this).find("option:selected").attr("vat-opt");
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie('companyIdSales', obj.val(), { expires: 7, path: "/" });
                $("#SalesConsignmentVatCalculate").val(vatCal);
                $("#SalesConsignmentBranchId").filterOptions('com', obj.val(), '');
                $("#SalesConsignmentBranchId").change();
                resetFormSales();
                checkVatCompanySales();
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
                            $("#SalesConsignmentVatCalculate").val(vatCal);
                            $("#SalesConsignmentBranchId").filterOptions('com', obj.val(), '');
                            $("#SalesConsignmentBranchId").change();
                            resetFormSales();
                            checkVatCompanySales();
                            checkChartAccountSales();
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesConsignmentCompanyId").val($.cookie("companyIdSales"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $("#SalesConsignmentBranchId").change(function(){
            var obj = $(this);
            if($(".tblSOList").find(".product_id").val() == undefined){
                $.cookie('branchIdSalesConsignment', obj.val(), { expires: 7, path: "/" });
                branchChangeSalesConsignment(obj);
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
                            $.cookie('branchIdSalesConsignment', obj.val(), { expires: 7, path: "/" });
                            branchChangeSalesConsignment(obj);
                            $("#tblSO").html('');
                            // Total Discount
                            $("#btnRemoveSalesTotalDiscount").click();
                            calcTotalAmountSales();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#SalesConsignmentBranchId").val($.cookie("branchIdSalesConsignment"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Company Action
        if($.cookie('companyIdSales') != null || $("#SalesConsignmentCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdSales') != null){
                $("#SalesConsignmentCompanyId").val($.cookie('companyIdSales'));
            }
            var vatCal = $("#SalesConsignmentCompanyId").find("option:selected").attr("vat-opt");
            $("#SalesConsignmentVatCalculate").val(vatCal);
            $("#SalesConsignmentBranchId").filterOptions('com', $("#SalesConsignmentCompanyId").val(), '');
            $("#SalesConsignmentBranchId").change();
        }
        // Button Change Info & Term
        <?php
        if($allowEditTerm){
        ?>
        $("#btnSalesInvoiceTermCon").click(function(){
            $("#saleInvoiceInformation").hide();
            $("#salesTermCondition").show();
            $("#btnSalesInvoiceTermCon, #btnSalesInvoiceInfo").removeAttr('style');
            $("#btnSalesInvoiceTermCon").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
            $("#btnSalesInvoiceInfo").attr("style", "padding: 3px; background: #CCCCCC;");
        });
        
        $("#btnSalesInvoiceInfo").click(function(){
            $("#saleInvoiceInformation").show();
            $("#salesTermCondition").hide();
            $("#btnSalesInvoiceTermCon, #btnSalesInvoiceInfo").removeAttr('style');
            $("#btnSalesInvoiceTermCon").attr("style", "padding: 3px; background: #CCCCCC;");
            $("#btnSalesInvoiceInfo").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
        });
        <?php
        }
        ?>
        // Reset Form
        resetFormSales();
        // VAT Filter
        checkVatCompanySales();
        // A/R Filter
        checkChartAccountSales();
        // Load Detail
        loadOrderDetailSO();
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
    });
    
    function removeCustomerSales(){
        $("#SalesConsignmentCustomerId").val("");
        $("#SalesConsignmentCustomerNumber").val("");
        $("#SalesConsignmentCustomerNumber").removeAttr("readonly");
        $("#SalesConsignmentCustomerName").val("");
        $("#SalesConsignmentCustomerName").removeAttr("readonly");
        $(".searchCustomerSales").show();
        $(".deleteCustomerSales").hide();
        $("#deleteConsignmentSales").click();
        $("#deleteOrderSales").click();
        $("#typeOfPriceSO").filterOptions("comp", $("#SalesConsignmentCompanyId").val(), "0");
    } 
    
    function branchChangeSalesConsignment(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#SalesConsignmentSoCode").val("<?php echo date("y"); ?>"+mCode);
        $("#SalesConsignmentCurrencyCenterId").val(currency);
        $(".lblSymbolSales").html(currencySymbol);
    }
    
    function getCustomerContactSales(customerId){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getCustomerContact/"+customerId,
            beforeSend: function(){
                $("#SalesConsignmentCustomerContactId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            },
            success: function(msg){
                $("#SalesConsignmentCustomerContactId").html(msg);
            }
        });
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
                            var totalDisAmt     = replaceNum($("#inputInvoiceDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputInvoiceDisPer").val());
                            $("#SalesConsignmentDiscountUs").val(totalDisAmt);
                            $("#SalesConsignmentDiscountPercent").val(totalDisPercent);
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
        var vatCal = $("#SalesConsignmentVatCalculate").val();
        $("#lblSalesConsignmentVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblSalesConsignmentVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblSalesConsignmentVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedSales(){
        var vatPercent = replaceNum($("#SalesConsignmentVatSettingId").find("option:selected").attr("rate"));
        var vatAccId   = replaceNum($("#SalesConsignmentVatSettingId").find("option:selected").attr("acc"));
        $("#SalesConsignmentVatPercent").val((vatPercent).toFixed(2));
        $("#SalesConsignmentVatChartAccountId").val(vatAccId);
    }
    
    function checkVatCompanySales(){
        // VAT Filter
        $("#SalesConsignmentVatSettingId").filterOptions('com-id', $("#SalesConsignmentCompanyId").val(), '');
    }
    
    function backSalesConsignment(){
        oCache.iCacheLower = -1;
        oTableSalesConsignment.fnDraw(false);
        $("#SalesConsignmentAddForm").validationEngine("hideAll");
        var rightPanel = $("#SalesConsignmentAddForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function checkCompanySales(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#SalesConsignmentCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormSales(){
        // Customer
        $(".deleteCustomerSales").click();
        // Employee
        $(".deleteSalesRep, .deleteSalesDelivery, .deleteSalesCollector").click();
        // Quotation
        $(".deleteConsignmentSales").click();
        // Sale Order
        $(".deleteOrderSales").click();
    }
    
    function checkChartAccountSales(){
        // A/R Filter
        $("#SalesConsignmentChartAccountId").filterOptions('company_id', $("#SalesConsignmentCompanyId").val(), '');
        
        if($("#SalesConsignmentCompanyId").val() != ''){
            <?php
            if(!empty($arAccountId)){
            ?>
            $("#SalesConsignmentChartAccountId option[value='<?php echo $arAccountId; ?>']").attr('selected', true);
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
                    $("#SalesConsignmentIsApprove").val(0);
                    // Action Click Save
                    $("#SalesConsignmentAddForm").submit();
                    $(this).dialog("close");
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function checkOrderDate(){
        if($("#SalesConsignmentOrderDate").val() == ''){
            $("#SalesConsignmentOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerAddSO(field, rules, i, options){
        if($("#SalesConsignmentCustomerId").val() == "" || $("#SalesConsignmentCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }
    
    function checkCollectorSO(field, rules, i, options){
        if($("#SalesConsignmentCollectorId").val() == "" || $("#SalesConsignmentCollectorName").val() == ""){
            return "* Invalid Collector";
        }
    }

    function loadOrderDetailSO(){
        if($("#SalesConsignmentOrderDate").val() != ''){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/orderDetails/",
                beforeSend: function(){
                    $(".orderDetailSales").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $("#tblSO").html('');
                    $("#SalesConsignmentTotalAmount").val("0.00");
                    $("#SalesConsignmentDiscountPercent").val("0");
                    $("#SalesConsignmentSubTotalAmount").val("0.00");
                    $("#SalesConsignmentStatus").removeAttr("disabled");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".orderDetailSales").html(msg);
                    $(".footerFormSales").show();
                    <?php if($allowEditInvDis){ ?>
                    // Action Total Discount Amount
                    $("#SalesConsignmentDiscountUs").click(function(){
                        getTotalDiscountSales();
                    });
                    <?php } ?>

                    $("#btnRemoveSalesTotalDiscount").click(function(){
                        $("#SalesConsignmentDiscountUs").val(0);
                        $("#SalesConsignmentDiscountPercent").val(0);
                        $(this).hide();
                        $("#salesLabelDisPercent").html('');
                        calcTotalAmountSales();
                    });
                    
                    // Action VAT Status
                    $("#SalesConsignmentVatSettingId").change(function(){
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
    
    function removeEmployeeSo(objId, objName, btnSearch, btnDelete){
        $("#"+objId).val('');
        $("#"+objName).val('');
        $("#"+objName).removeAttr('readonly','readonly');
        $("."+btnSearch).show();
        $("."+btnDelete).hide();
    }
    
    function searchEmployeeSo(objId, objName, btnSearch, btnDelete){
        var companyId = $("#SalesConsignmentCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                timeSalesConginment == 1;
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo MENU_EMPLOYEE_INFO; ?>',
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
    
    function searchAllCustomerSales(){
        var companyId = $("#SalesConsignmentCompanyId").val();
        if(companyId != '' && $("#SalesConsignmentBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeSalesConginment == 1;
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
                                    // Set Customer
                                    $("#SalesConsignmentPaymentTermId").find("option").removeAttr("selected");
                                    $("#SalesConsignmentPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
                                    $("#SalesConsignmentCustomerId").val(customerId);
                                    $("#SalesConsignmentCustomerNumber").val(customerCode);
                                    $("#SalesConsignmentCustomerNumber").attr('readonly','readonly');
                                    $("#SalesConsignmentCustomerName").val(customerNameEn);
                                    $("#SalesConsignmentCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerSales").hide();
                                    $(".deleteCustomerSales").show();
                                    // Reset SO & Quotation
                                    $(".deleteOrderSales").click();
                                    $(".deleteConsignmentSales").click();
                                    getCustomerContactSales(customerId);
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
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchConsignmentSales(){
        var companyId  = $("#SalesConsignmentCompanyId").val();
        var branchId   = $("#SalesConsignmentBranchId").val();
        var customerId = $("#SalesConsignmentCustomerId").val();
        if(companyId != "" && branchId != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/consignment/"+companyId+"/"+branchId+"/"+customerId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeSalesConginment = 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_CUSTOMER_CONSIGNMENT_INFO; ?>',
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
                                if($("input[name='chkConsignment']:checked").val()){
                                    $("#SalesConsignmentConsignmentId").val($("input[name='chkConsignment']:checked").val());
                                    $("#SalesConsignmentConsignmentCode").val($("input[name='chkConsignment']:checked").attr("rel"));
                                    $("#SalesConsignmentConsignmentCode").attr('readonly', 'readonly');
                                    $(".searchConsignmentSales").hide();
                                    $(".deleteConsignmentSales").show();
                                    var consignmentId = $("input[name='chkConsignment']:checked").val();
                                    var locationGroup = $("input[name='chkConsignment']:checked").attr("location-group");
                                    // Set Location Group
                                    $("#SalesConsignmentLocationGroupId").val(locationGroup);
                                    // Customer
                                    var customerId     = $("input[name='chkConsignment']:checked").attr('cus-id');
                                    var customerCode   = $("input[name='chkConsignment']:checked").attr("cus-code");
                                    var customerNameEn = $("input[name='chkConsignment']:checked").attr("name-us");
                                    var paymentTermId  = $("input[name='chkConsignment']:checked").attr("term-id");
                                    // Set Customer
                                    $("#SalesConsignmentPaymentTermId").find("option").removeAttr("selected");
                                    $("#SalesConsignmentPaymentTermId").find("option[value='"+paymentTermId+"']").attr("selected", true);
                                    $("#SalesConsignmentCustomerId").val(customerId);
                                    $("#SalesConsignmentCustomerNumber").val(customerCode);
                                    $("#SalesConsignmentCustomerNumber").attr('readonly','readonly');
                                    $("#SalesConsignmentCustomerName").val(customerNameEn);
                                    $("#SalesConsignmentCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerSales").hide();
                                    $(".deleteCustomerSales").show();
                                    getCustomerContactSales(customerId);
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkConsignment']:checked").attr("ptype");
                                    var priceTypeSelected = $("input[name='chkConsignment']:checked").attr("ptype-id");
                                    customerPriceTypeSales(priceTypeList, priceTypeSelected);
                                    // Get Product From Request Stock
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromConsignment/"+consignmentId+"/"+locationGroup,
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
    
    function checkBfSaveSo(){
        var formName = "#SalesConsignmentAddForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
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
                return false;
            }else{
                return true;
            }
        }
    }

    function shortcutKeySo(){
        $("#dialogShortcutKeysSO").dialog({
            title: '<?php echo TABLE_SHORTCUT_KEY; ?>',
            resizable: false,
            modal: true,
            width: '750',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function codeDialogSO(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
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
                    var rightPanel=$("#SalesConsignmentAddForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableSalesConsignment.fnDraw(false);
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
        $(".searchConsignmentSales").hide();
        $(".searchSalesRep").hide();
        $(".searchSalesCollector").hide();
        // Div for Search Product, Service, Misc
        $("#divSearchSales").css("visibility", "hidden");
        if($("#SalesConsignmentCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#SalesConsignmentCustomerName").val() == ''){
                $(".searchCustomerSales").show();
            }
            if($("#SalesConsignmentConsignmentCode").val() == ''){
                $(".searchConsignmentSales").show();
            }
            if($("#SalesConsignmentSalesRepName").val() == ''){
                $(".searchSalesRep").show();
            }
            if($("#SalesConsignmentCollectorName").val() == ''){
                $(".searchSalesCollector").show();
            }
            // Div for Search Product, Service, Misc
            $("#divSearchSales").css("visibility", "visible");
        } else {
            $(".lblSymbolSales").html('');
        } 
        // Label
        $("#SalesConsignmentAddForm").find("label").removeAttr("class");
        $("#SalesConsignmentAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'SalesConsignmentCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#SalesConsignmentAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#SalesConsignmentAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'SalesConsignmentCompanyId'){
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
        $("#SalesConsignmentCustomerName").attr("readonly", readonly);
        $("#SalesConsignmentSalesRepName").attr("readonly", readonly);
        $("#SalesConsignmentCollectorName").attr("readonly", readonly);
        $("#SalesConsignmentCustomerPoNumber").attr("readonly", readonly);
        $("#SalesConsignmentProject").attr("readonly", readonly);
        $("#SalesConsignmentMemo").attr("readonly", readonly);
        $("#SalesConsignmentConsignmentCode").attr("readonly", readonly);
        $("#SalesConsignmentOrderNumber").attr("readonly", readonly);
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
        $("#typeOfPriceSO").filterOptions('comp', $("#SalesConsignmentCompanyId").val(), '');
        
        if($("#SalesConsignmentCompanyId").val() == ''){
            $("#typeOfPriceSO").prepend('<option value="" comp=""><?php echo INPUT_SELECT; ?></option>');
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
        changePriceTypeSO();
    }
    
    function getDefaultVatSales(){
        var vatDefault = $("#SalesConsignmentCompanyId option:selected").attr("vat-d");
        $("#SalesConsignmentVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedSales();
    }
</script>
<?php echo $this->Form->create('SalesConsignment', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<input type="hidden" name="data[total_deposit]" id="SalesConsignmentTotalDeposit" />
<input type="hidden" value="1" id="SalesConsignmentIsApprove" name="data[SalesConsignment][is_approve]" />
<input type="hidden" value="0" id="SalesConsignmentCurrencyCenterId" name="data[SalesConsignment][currency_center_id]" />
<input type="hidden" id="limitBalance" />
<input type="hidden" id="limitInvoice" />
<input type="hidden" id="totalBalanceUsed" />
<input type="hidden" id="totalInvoiceUsed" />
<input type="hidden" value="" name="data[SalesConsignment][vat_calculate]" id="SalesConsignmentVatCalculate" />
<input type="hidden" value="" name="data[SalesConsignment][location_group_id]" id="SalesConsignmentLocationGroupId" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderSalesConsignment">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="SOTop">
    <legend><a href="#" id="btnSalesInvoiceInfo" style="padding: 3px; background: #CCCCCC; font-weight: bold;"><?php __(MENU_SALES_ORDER_MANAGEMENT_INFO); ?></a> <?php if($allowEditTerm){ ?>| <a href="#" id="btnSalesInvoiceTermCon" style="padding: 3px; background: #CCCCCC;"><?php __(TABLE_TERM_AND_CONDITION); ?></a><?php } ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="saleInvoiceInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="SalesConsignmentCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="SalesConsignmentBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="SalesConsignmentSoCode"><?php echo TABLE_INVOICE_CODE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[SalesConsignment][company_id]" id="SalesConsignmentCompanyId" class="validate[required]" style="width: 75%;">
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
                                    <option vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                           <div class="inputContainer" style="width:100%">
                                <select name="data[SalesConsignment][branch_id]" id="SalesConsignmentBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['inv_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                               <?php echo $this->Form->text('so_code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => true)); ?>
                           </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesConsignmentOrderDate"><?php echo TABLE_INVOICE_DATE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('order_date', array('value' => date("d/m/Y"),'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:85%')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 16%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesConsignmentConsignmentCode"><?php echo MENU_CONSIGNMENT; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('consignment_id'); ?>
                                <?php echo $this->Form->text('consignment_code', array('style' => 'width:70%', 'class' => 'validate[required]')); ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchConsignmentSales" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteConsignmentSales" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 16%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%;">
                                
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
                        <td style="width: 34%"><label for="SalesConsignmentCustomerNumber"><?php echo TABLE_CUSTOMER_NUMBER; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="SalesConsignmentCustomerName"><?php echo TABLE_CUSTOMER_NAME; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="SalesConsignmentCustomerContactId"><?php echo TABLE_CONTACT_NAME; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('customer_number', array('class' => 'validate[required]', 'style' => 'width:70%')); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('customer_name', array('class' => 'validate[required,funcCall[checkCustomerAddSO]]', 'style' => 'width:70%')); ?>
                                <?php echo $this->Form->hidden('customer_id'); ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchCustomerSales" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteCustomerSales" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                           <div class="inputContainer" style="width:100%">
                                <select name="data[SalesConsignment][customer_contact_id]" id="SalesConsignmentCustomerContactId" style="width: 75%;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                </select>
                            </div> 
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesOrderPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('payment_term_id', array('style' => 'width:90%', 'label' => false, 'empty' => INPUT_SELECT, 'class' => 'validate[required]', 'div' => false)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 16%; vertical-align: top;" rowspan="2" colspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesConsignmentMemo"><?php echo TABLE_NOTE; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->textarea('memo', array('style' => 'width:90%; height: 70px;')); ?>
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
                        <td style="width: 34%"><label for="SalesConsignmentCustomerPoNumber"><?php echo TABLE_CUSTOMER_PO; ?></label></td>
                        <td style="width: 33%"><label for="SalesConsignmentSalesRep"><?php echo TABLE_SALES_REP; ?></label></td>
                        <td style="width: 33%"><label for="SalesConsignmentCollectorName"><?php echo TABLE_COLLECTOR; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                           <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('customer_po_number', array('style' => 'width:70%')); ?>
                           </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('sales_rep_id'); ?>
                                <?php echo $this->Form->text('sales_rep_name', array('style' => 'width:70%')); ?>
                                <img alt="Search" search="1" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchSalesRep" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" search="0" align="absmiddle" style="display: none; cursor: pointer;" class="deleteSalesRep" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                               <?php echo $this->Form->hidden('collector_id'); ?>
                               <?php echo $this->Form->text('collector_name', array('style' => 'width:70%')); ?>
                               <img alt="Search" search="1" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchSalesCollector" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                               <img alt="Delete" search="0" align="absmiddle" style="display: none; cursor: pointer;" class="deleteSalesCollector" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="SalesConsignmentChartAccountId">A/R <span class="red">*</span> :</label></td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            $filter="AND chart_account_type_id IN (2)";
                            ?>
                            <div class="inputContainer">
                                <select id="SalesConsignmentChartAccountId" name="data[SalesConsignment][chart_account_id]" class="sales_order_coa_id validate[required]" style="width:90%; height: 30px;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE (parent_id IS NULL OR parent_id = 0) AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                    while($data[0]=mysql_fetch_array($query[0])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                                    ?>
                                    <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[0]['id']==$arAccountId?'selected="selected"':''; ?>><?php echo $data[0]['name']; ?></option>
                                        <?php
                                        $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                        while($data[1]=mysql_fetch_array($query[1])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                                        ?>
                                        <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[1]['id']==$arAccountId?'selected="selected"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                            <?php
                                            $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                            while($data[2]=mysql_fetch_array($query[2])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                            ?>
                                            <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[2]['id']==$arAccountId?'selected="selected"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                                <?php
                                                $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                                while($data[3]=mysql_fetch_array($query[3])){
                                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                                ?>
                                                <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[3]['id']==$arAccountId?'selected="selected"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                                    <?php
                                                    $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                                    while($data[4]=mysql_fetch_array($query[4])){
                                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                                    ?>
                                                    <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[4]['id']==$arAccountId?'selected="selected"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                        <?php
                                                        $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ".$filter." ORDER BY account_codes");
                                                        while($data[5]=mysql_fetch_array($query[5])){
                                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                                        ?>
                                                        <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[5]['id']==$arAccountId?'selected="selected"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table id="salesTermCondition" cellpadding="0" cellspacing="0" style="width: 100%; display: none;">
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style=" vertical-align: top; height: 155px;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <?php
                                $termForms = array();
                                $sqlTermApply = mysql_query("SELECT * FROM term_condition_applies WHERE is_active = 1 AND module_type_id = 25 GROUP BY module_type_id, term_condition_type_id ORDER BY id");
                                $i=0;
                                $j=0;
                                if(mysql_num_rows($sqlTermApply)){
                                    while($rowTermApply = mysql_fetch_array($sqlTermApply)){
                                        $termForms[$j][$i] = $rowTermApply['term_condition_type_id']."||".$rowTermApply['term_condition_default_id'];
                                        $i++;
                                        if($i==3){
                                            $i=0;
                                            $j++;
                                        }
                                    }
                                }
                                foreach($termForms AS $termForm){
                                    @$termData1 = explode("||", $termForm[0]);
                                    @$termData2 = explode("||", $termForm[1]);
                                    @$termData3 = explode("||", $termForm[2]);
                                    $termTypeId1 = '';
                                    $termTypeId2 = '';
                                    $termTypeId3 = '';
                                    $title1 = '';
                                    $title2 = '';
                                    $title3 = '';
                                    $input1 = '';
                                    $input2 = '';
                                    $input3 = '';
                                    if(!empty($termForm[0])){
                                        $termTypeId1 = $termData1[0];
                                        $title1  = getTermType($termData1[0]);
                                        $input1  = getTermOption($termData1[0], $termData1[1]);
                                    }
                                    if(!empty($termForm[1])){
                                        $termTypeId2 = $termData2[0];
                                        $title2  = getTermType($termData2[0]);
                                        $input2  = getTermOption($termData2[0], $termData2[1]);
                                    }
                                    if(!empty($termForm[2])){
                                        $termTypeId3 = $termData3[0];
                                        $title3  = getTermType($termData3[0]);
                                        $input3  = getTermOption($termData3[0], $termData3[1]);
                                    }
                                ?>
                                <tr>
                                    <td style="width: 33%"><label for="TermCondition<?php echo $termTypeId1; ?>"><?php echo $title1; ?></label></td>
                                    <td style="width: 34%"><label for="TermCondition<?php echo $termTypeId2; ?>"><?php echo $title2; ?></label></td>
                                    <td style="width: 33%"><label for="TermCondition<?php echo $termTypeId3; ?>"><?php echo $title3; ?></label></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php
                                            if(!empty($termTypeId1)){
                                            ?>
                                            <input type="hidden" name="term_condition_type_id[]" value="<?php echo $termTypeId1; ?>" />
                                            <select name="term_condition_id[]" id="TermCondition<?php echo $termTypeId1; ?>" style="width: 90%;">
                                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                                <?php echo $input1; ?>
                                            </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php
                                            if(!empty($termTypeId2)){
                                            ?>
                                            <input type="hidden" name="term_condition_type_id[]" value="<?php echo $termTypeId2; ?>" />
                                            <select name="term_condition_id[]" id="TermCondition<?php echo $termTypeId2; ?>" style="width: 90%;">
                                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                                <?php echo $input2; ?>
                                            </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inputContainer" style="width:100%">
                                            <?php
                                            if(!empty($termTypeId3)){
                                            ?>
                                            <input type="hidden" name="term_condition_type_id[]" value="<?php echo $termTypeId3; ?>" />
                                            <select name="term_condition_id[]" id="TermCondition<?php echo $termTypeId3; ?>" style="width: 90%;">
                                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                                <?php echo $input3; ?>
                                            </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div class="orderDetailSales" style=" margin-top: 5px; text-align: center;">
</div>
<div class="footerFormSales">
    <div style="float: left; width: 19%;">
        <div class="buttons">
            <a href="#" class="positive btnBackSalesConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveSales" >
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                <span class="txtSaveSales"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width: 80%;">
        <table style="width:100%">
            <tr>
                <td style="width:10%; text-align: right;"><label for="SalesConsignmentTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width:15%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; height:15px; font-size:12px; font-weight: bold', 'value' => '0.00')); ?> <span class="lblSymbolSales"></span>
                    </div>
                </td>
                <td style="width:8%; text-align: right;"><label for="SalesConsignmentDiscountUs"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width:18%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('discount_percent', array('class' => 'float', 'value'=>'0')); ?>
                        <?php echo $this->Form->text('discount_us', array('style' => 'width: 45%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value'=>'0', 'readonly' => true)); ?> <span class="lblSymbolSales"></span>
                        <span id="salesLabelDisPercent"></span>
                        <?php if($allowEditInvDis){ ?><img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemoveSalesTotalDiscount" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove Discount')" /><?php } ?>
                    </div>
                </td>
                <td style="width:17%; text-align: right;">
                    <label for="SalesConsignmentVatSettingId" id="lblSalesConsignmentVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="SalesConsignmentVatSettingId" name="data[SalesConsignment][vat_setting_id]" style="width: 75%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00" acc=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id, chart_account_id FROM vat_settings WHERE is_active = 1 AND type = 1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" acc="<?php echo $rowVat['chart_account_id']; ?>"><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width:9%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('vat_chart_account_id', array('value' => '')); ?>
                        <?php echo $this->Form->hidden('total_vat', array('class' => 'float', 'value' => 0)); ?>
                        <?php echo $this->Form->text('vat_percent', array('value' => 0, 'readonly' => true, 'style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float')); ?> <span class="lblSymbolSalesPercent">(%)</span>
                    </div>
                </td>
                <td style="width:5%; text-align: right;"><label for="SalesConsignmentSubTotalAmount"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width:15%;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('sub_total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 75%; height:15px; font-size:12px; font-weight: bold', 'value'=>'0')); ?> <span class="lblSymbolSales"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>