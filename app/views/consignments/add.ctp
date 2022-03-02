<?php
include('includes/function.php');
echo $this->element('prevent_multiple_submit');
// Authentication
$this->element('check_access');
$allowEditTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'editTermsCondition');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        clearOrderDetailConsignment();
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
        // Hide Branch
        $("#ConsignmentBranchId").filterOptions('com', '0', '');
        $("#ConsignmentLocationGroupId").chosen();
        $("#ConsignmentAddForm").validationEngine();
        $(".saveConsignment").click(function(){
            if(checkBfSaveConsignment() == true){
                return true;
            }else{
                return false;
            }
        });
        $(".float").autoNumeric({mDec: 3});
        $("#ConsignmentAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveConsignment").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#ConsignmentDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $(".floatQty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Consignment', 'Add', 2, result.responseText);
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
                        backConsignment();
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
                if(result.code == "1" || result.code == "4"){
                    codeDialogConsignment();
                }else if(result.code == "2"){
                    errorSaveConsignment();
                }else if(result.code == "3"){
                    errorOutStock();
                    var listOutStock = result.listOutStock.split("-");
                    var obj = "";
                    $(".tblConsignmentList").each(function(){
                        if($(this).find("input[name='product_id[]']").val() != ""){
                            obj = $(this);
                            $.each(listOutStock, function(i, val){
                                if(val != ""){
                                    if(obj.find("input[name='product_id[]']").val() == val.split("|")[0]){
                                        obj.css("background","#fc8b8b");
                                        obj.attr('data-stock',"Total qty can sale is "+val.split("|")[1]+" "+val.split("|")[2]+"");
                                        obj.find("input[name='inv_qtyConsignment[]']").val(val.split("|")[1]);
                                    }
                                }
                            });
                        }
                    });
                    $(".tblConsignmentList").mouseover(function(){
                        var text = $(this).attr('data-stock');
                        Tip(text);
                    });
                }else{
                    createSysAct('Consignment', 'Add', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoice" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button></div>');
                    $(".printInvoice").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.consign_id,
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
                            backConsignment();
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
        
        $(".searchCustomerConsignment").click(function(){
            if(checkOrderDate() == true && $("#ConsignmentCompanyId").val() != '' && $("#ConsignmentBranchId").val() != ''){
                searchAllCustomerConsignment();
            }
        });

        $(".deleteCustomerConsignment").click(function(){
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                removeCustomerConsignment();
            } else {
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
                            removeCustomerConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $("#ConsignmentCustomerName").focus(function(){
            checkOrderDate();
        });
        
        $('#ConsignmentCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#ConsignmentCustomerName").autocomplete("<?php echo $this->base . "/consignments/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyConsignment(value.toString().split(".*")[8])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyConsignment(value.toString().split(".*")[8])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            var strName = value.toString().split(".*")[1].split("-");
            var customerId = value.toString().split(".*")[0];
            var customerCode = value.toString().split(".*")[2];
            $("#ConsignmentProduct").attr("disabled", false);
            $("#ConsignmentCustomerId").val(customerId);
            $("#ConsignmentCustomerName").val(customerCode+" - "+strName[1]);
            $("#ConsignmentCustomerName").attr("readonly","readonly");
            $(".searchCustomerConsignment").hide();
            $(".deleteCustomerConsignment").show();
            getCustomerContactConsignment(customerId);
            if(value.toString().split(".*")[9] != ''){
                // Check Price Type Customer
                customerPriceTypeConsignment(value.toString().split(".*")[9], 0);
            }
        });
        
        $("#ConsignmentSalesRepName").autocomplete("<?php echo $this->base . "/employees/searchEmployee"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyConsignment(value.toString().split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyConsignment(value.toString().split(".*")[3])){
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
        
        // Action Location Group
        if($.cookie('ConsignmentLocationGroupId')!=null){
            $("#ConsignmentLocationGroupId").val($.cookie('ConsignmentLocationGroupId'));
        }
        
        $("#ConsignmentLocationGroupId").change(function(){
            var obj = $(this);
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                $.cookie("ConsignmentLocationGroupId", obj.val(), {expires : 7,path    : '/'});
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
                            $.cookie("ConsignmentLocationGroupId", obj.val(), {expires : 7,path    : '/'});
                            $("#tblConsignment").html('');
                            calcTotalAmountConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentLocationGroupId").val($.cookie("ConsignmentLocationGroupId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Action Order Date
        $('#ConsignmentDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#ConsignmentDate").datepicker("option", "maxDate", 0);
        
        if($.cookie('ConsignmentDate')!=null){
            $("#ConsignmentDate").val($.cookie('ConsignmentDate'));
        }
        
        $("#ConsignmentDate").change(function(){
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                $.cookie("ConsignmentDate", $("#ConsignmentDate").val(), {
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
                            $.cookie("ConsignmentDate", $("#ConsignmentDate").val(), {expires : 7,path    : '/'});
                            $("#tblConsignment").html('');
                            calcTotalAmountConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentDate").val($.cookie("ConsignmentDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackConsignment").click(function(event){
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
                        backConsignment();
                    }
                }
            });
        });
        
        $(".searchConsignmentRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            searchEmployeeConsignment(objId, objName, btnSearch, btnDelete);
        });
        
        $(".deleteConsignmentRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            removeEmployeeConsignment(objId, objName, btnSearch, btnDelete);
        });
        // Company Action
        $("#ConsignmentCompanyId").change(function(){
            var obj    = $(this);
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                $.cookie('companyIdConsignment', obj.val(), { expires: 7, path: "/" });
                $("#ConsignmentBranchId").filterOptions('com', obj.val(), '');
                $("#ConsignmentBranchId").change();
                resetFormConsignment();
                checkVatCompanyConsignment();
                changeInputCSSConsignment();
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
                            $.cookie('companyIdConsignment', obj.val(), { expires: 7, path: "/" });
                            $("#ConsignmentBranchId").filterOptions('com', obj.val(), '');
                            $("#ConsignmentBranchId").change();
                            resetFormConsignment();
                            $("#tblConsignment").html('');
                            calcTotalAmountConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentCompanyId").val($.cookie("companyIdConsignment"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $("#ConsignmentBranchId").change(function(){
            var obj = $(this);
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                $.cookie('branchIdConsignment', obj.val(), { expires: 7, path: "/" });
                branchChangeConsignment(obj);
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
                            $.cookie('branchIdConsignment', obj.val(), { expires: 7, path: "/" });
                            branchChangeConsignment(obj);
                            $("#tblConsignment").html('');
                            calcTotalAmountConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentBranchId").val($.cookie("branchIdConsignment"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Check Company Select
        if($.cookie('companyIdConsignment') != null || $("#ConsignmentCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdConsignment') != null){
                $("#ConsignmentCompanyId").val($.cookie('companyIdConsignment'));
            }
            $("#ConsignmentBranchId").filterOptions('com', $("#ConsignmentCompanyId").val(), '');
            $("#ConsignmentBranchId").change();
        }
        // Button Change Info & Term
        <?php
        if($allowEditTerm){
        ?>
        $("#btnConsignmentInvoiceTermCon").click(function(){
            $("#saleInvoiceInformation").hide();
            $("#salesTermCondition").show();
            $("#btnConsignmentInvoiceTermCon, #btnConsignmentInvoiceInfo").removeAttr('style');
            $("#btnConsignmentInvoiceTermCon").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
            $("#btnConsignmentInvoiceInfo").attr("style", "padding: 3px; background: #CCCCCC;");
        });
        
        $("#btnConsignmentInvoiceInfo").click(function(){
            $("#saleInvoiceInformation").show();
            $("#salesTermCondition").hide();
            $("#btnConsignmentInvoiceTermCon, #btnConsignmentInvoiceInfo").removeAttr('style');
            $("#btnConsignmentInvoiceTermCon").attr("style", "padding: 3px; background: #CCCCCC;");
            $("#btnConsignmentInvoiceInfo").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
        });
        <?php
        }
        ?>
        // Reset Form
        resetFormConsignment();
        // Load Detail
        loadOrderDetailConsignment();
        
    });
    
    function removeCustomerConsignment(){
        $("#ConsignmentCustomerId").val("");
        $("#ConsignmentCustomerName").val("");
        $("#ConsignmentCustomerName").removeAttr("readonly");
        $(".searchCustomerConsignment").show();
        $(".deleteCustomerConsignment").hide();
        $("#typeOfPriceConsignment").filterOptions("comp", $("#ConsignmentCompanyId").val(), "0");
    } 
    
    function branchChangeConsignment(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#ConsignmentCode").val("<?php echo date("y"); ?>"+mCode);
        $("#ConsignmentCurrencyCenterId").val(currency);
        $(".lblSymbolConsignment").html(currencySymbol);
    }
    
    function getCustomerContactConsignment(customerId){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getCustomerContact/"+customerId,
            beforeSend: function(){
                $("#ConsignmentCustomerContactId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            },
            success: function(msg){
                $("#ConsignmentCustomerContactId").html(msg);
            }
        });
    }
    
    function backConsignment(){
        oCache.iCacheLower = -1;
        oTableConsignment.fnDraw(false);
        $("#ConsignmentAddForm").validationEngine("hideAll");
        var rightPanel = $("#ConsignmentAddForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function checkCompanyConsignment(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#ConsignmentCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormConsignment(){
        // Customer
        $(".deleteCustomerConsignment").click();
        // Employee
        $(".deleteConsignmentRep").click();
    }
    
    function checkOrderDate(){
        if($("#ConsignmentDate").val() == ''){
            $("#ConsignmentDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerAddConsignment(field, rules, i, options){
        if($("#ConsignmentCustomerId").val() == "" || $("#ConsignmentCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }

    function loadOrderDetailConsignment(){
        if($("#ConsignmentDate").val() != ''){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/orderDetails/",
                beforeSend: function(){
                    $(".orderDetailConsignment").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $("#tblConsignment").html('');
                    $("#ConsignmentTotalAmount").val("0.00");
                    $("#ConsignmentStatus").removeAttr("disabled");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".orderDetailConsignment").html(msg);
                    $(".footerFormConsignment").show();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        } else {
            clearOrderDetailConsignment();
        }
    }

    function clearOrderDetailConsignment(){
        $(".orderDetailConsignment").html("");
        $(".footerFormConsignment").hide();
    }
    
    function removeEmployeeConsignment(objId, objName, btnSearch, btnDelete){
        $("#"+objId).val('');
        $("#"+objName).val('');
        $("#"+objName).removeAttr('readonly','readonly');
        $("."+btnSearch).show();
        $("."+btnDelete).hide();
    }
    
    function searchEmployeeConsignment(objId, objName, btnSearch, btnDelete){
        var companyId = $("#ConsignmentCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                timeBarcodeConsignment == 1;
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
    
    function searchAllCustomerConsignment(){
        var companyId = $("#ConsignmentCompanyId").val();
        if(companyId != '' && $("#ConsignmentBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeConsignment == 1;
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
                                    // Customer
                                    var customerId     = $("input[name='chkCustomer']:checked").val();
                                    var customerCode   = $("input[name='chkCustomer']:checked").attr("rel");
                                    var customerNameEn = $("input[name='chkCustomer']:checked").attr("name-us");
                                    // Set Customer
                                    $("#ConsignmentProduct").attr("disabled", false);
                                    $("#ConsignmentCustomerId").val(customerId);
                                    $("#ConsignmentCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#ConsignmentCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerConsignment").hide();
                                    $(".deleteCustomerConsignment").show();
                                    // Get Customer Contact
                                    getCustomerContactConsignment(customerId);
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkCustomer']:checked").attr("ptype");
                                    if(priceTypeList != ''){
                                        customerPriceTypeConsignment(priceTypeList, 0);
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
    
    function checkBfSaveConsignment(){
        var formName = "#ConsignmentAddForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblConsignmentList").find(".product").val() == undefined){
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
    
    function codeDialogConsignment(){
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
                    $(".saveConsignment").removeAttr("disabled");
                    $(".txtSaveConsignment").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveConsignment(){
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
                    var rightPanel=$("#ConsignmentAddForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableConsignment.fnDraw(false);
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
                    $(".saveConsignment").removeAttr("disabled");
                    $(".txtSaveConsignment").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function changeInputCSSConsignment(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchCustomerConsignment").hide();
        $(".searchConsignmentRep").hide();
        // Div for Search Product, Service, Misc
        $("#divSearchConsignment").css("visibility", "hidden");
        if($("#ConsignmentCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#ConsignmentCustomerName").val() == ''){
                $(".searchCustomerConsignment").show();
            }
            if($("#ConsignmentSalesRepName").val() == ''){
                $(".searchConsignmentRep").show();
            }
            // Div for Search Product, Service, Misc
            $("#divSearchConsignment").css("visibility", "visible");
        } else {
            $(".lblSymbolConsignment").html('');
        } 
        // Label
        $("#ConsignmentAddForm").find("label").removeAttr("class");
        $("#ConsignmentAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'ConsignmentCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#ConsignmentAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#ConsignmentAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'ConsignmentCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolConsignment").removeClass(cssRemove);
        $(".lblSymbolConsignment").addClass(cssStyle);
        // Input Readonly
        $("#ConsignmentCustomerName").attr("readonly", readonly);
        $("#ConsignmentNote").attr("readonly", readonly);
        $("#searchProductUpcConsignment").attr("readonly", readonly);
        $("#searchProductSkuConsignment").attr("readonly", readonly);
        // Check Price Type With Company
        checkPriceTypeConsignment();
    }
    
    function checkPriceTypeConsignment(){
        // Price Type Filter
        $("#typeOfPriceConsignment").filterOptions('comp', $("#ConsignmentCompanyId").val(), '');
        
        if($("#ConsignmentCompanyId").val() == ''){
            $("#typeOfPriceConsignment").prepend('<option value="" comp=""><?php echo INPUT_SELECT; ?></option>');
            $("#typeOfPriceConsignment option[value='']").attr("selected", true);
        } else {
            $("#typeOfPriceConsignment option[value='']").remove();
        }
    }
    
    function customerPriceTypeConsignment(priceTypeList, priceTypeSelected){
        var priceTypeShow = '';
        var priceType = "";
        if(priceTypeList != ''){
            var selected  = 0;
            priceType = priceTypeList.toString().split(",");
            $("#typeOfPriceConsignment option").each(function(){
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
        
        $("#typeOfPriceConsignment option").removeAttr("selected");
        $("#typeOfPriceConsignment option[value='"+priceTypeShow+"']").attr("selected", true);
        $.cookie("typePriceConsignment", $("#typeOfPriceConsignment").val(), {expires : 7, path : '/'});
        changePriceTypeConsignment();
    }
</script>
<?php echo $this->Form->create('Consignment', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="0" id="ConsignmentCurrencyCenterId" name="data[Consignment][currency_center_id]" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderConsignment">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="ConsignmentTop">
    <legend><a href="#" id="btnConsignmentInvoiceInfo" style="padding: 3px; background: #CCCCCC; font-weight: bold;"><?php __(MENU_CUSTOMER_CONSIGNMENT_INFO); ?></a> <?php if($allowEditTerm){ ?>| <a href="#" id="btnConsignmentInvoiceTermCon" style="padding: 3px; background: #CCCCCC;"><?php __(TABLE_TERM_AND_CONDITION); ?></a><?php } ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="saleInvoiceInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="ConsignmentCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="ConsignmentBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="ConsignmentLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[Consignment][company_id]" id="ConsignmentCompanyId" class="validate[required]" style="width: 75%;">
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
                                <select name="data[Consignment][branch_id]" id="ConsignmentBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['cus_consign_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                               <?php
                                echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'style' => 'width:200px')); 
                                ?>
                           </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="ConsignmentCode"><?php echo TABLE_CONSIGNMENT_CODE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => true)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;" rowspan="2" colspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="ConsignmentNote"><?php echo TABLE_NOTE; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('note', array('style' => 'width:90%; height: 70px;')); ?>
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
                        <td style="width: 34%;"><label for="ConsignmentCustomerName"><?php echo TABLE_CUSTOMER_NAME; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="ConsignmentCustomerContactId"><?php echo TABLE_CONTACT_NAME; ?></label></td>
                        <td style="width: 33%;"><label for="ConsignmentConsignmentRep"><?php echo TABLE_SALES_REP; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('customer_name', array('class' => 'validate[required,funcCall[checkCustomerAddConsignment]]', 'style' => 'width:70%')); ?>
                                <?php echo $this->Form->hidden('customer_id'); ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchCustomerConsignment" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteCustomerConsignment" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[Consignment][customer_contact_id]" id="ConsignmentCustomerContactId" style="width: 75%;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                </select>
                            </div> 
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('sales_rep_id'); ?>
                                <?php echo $this->Form->text('sales_rep_name', array('style' => 'width:70%')); ?>
                                <img alt="Search" search="1" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchConsignmentRep" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" search="0" align="absmiddle" style="display: none; cursor: pointer;" class="deleteConsignmentRep" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="ConsignmentDate"><?php echo TABLE_CONSIGNMENT_DATE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('date', array('value' => date("d/m/Y"),'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:70%')); ?>
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
                        <td style=" vertical-align: top; height: 103px;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <?php
                                $termForms = array();
                                $sqlTermApply = mysql_query("SELECT * FROM term_condition_applies WHERE is_active = 1 AND module_type_id = 91 GROUP BY module_type_id, term_condition_type_id ORDER BY id");
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
<div class="orderDetailConsignment" style=" margin-top: 5px; text-align: center;">
</div>
<div class="footerFormConsignment">
    <div style="float: left; width: 19%;">
        <div class="buttons">
            <a href="#" class="positive btnBackConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveConsignment" >
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                <span class="txtSaveConsignment"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width: 350px;">
        <table style="width:100%">
            <tr>
                <td style="text-align: right;"><label for="ConsignmentTotalAmount"><?php echo TABLE_TOTAL_AMOUNT; ?>:</label></td>
                <td style="width: 250px;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 85%; height:15px; font-size:12px; font-weight: bold', 'value' => '0.00')); ?> <span class="lblSymbolConsignment"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>