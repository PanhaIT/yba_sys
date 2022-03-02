<?php
include('includes/function.php');
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        clearOrderDetailConsignmentReturn();
        // Hide Branch
        $("#ConsignmentReturnBranchId").filterOptions('com', '<?php echo $this->data['ConsignmentReturn']['company_id']; ?>', '<?php echo $this->data['ConsignmentReturn']['branch_id']; ?>');
        $("#ConsignmentReturnLocationGroupToId").chosen();
        $("#ConsignmentReturnEditForm").validationEngine();
        $(".saveConsignmentReturn").click(function(){
            if(checkBfSaveConsignmentReturn() == true){
                return true;
            }else{
                return false;
            }
        });
        $("#ConsignmentReturnEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveConsignmentReturn").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#ConsignmentReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".floatQty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Consignment Return', 'Edit', 2, result.responseText);
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
                        backConsignmentReturn();
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
                    codeDialogConsignmentReturn();
                }else if(result.code == "2"){
                    errorSaveConsignmentReturn();
                }else if(result.code == "3"){
                    errorOutStock();
                    var listOutStock = result.listOutStock.split("-");
                    var obj = "";
                    $(".tblConsignmentReturn").each(function(){
                        if($(this).find("input[name='product_id[]']").val() != ""){
                            obj = $(this);
                            $.each(listOutStock, function(i, val){
                                if(val != ""){
                                    if(obj.find("input[name='product_id[]']").val() == val.split("|")[0]){
                                        obj.css("background","#fc8b8b");
                                        obj.attr('data-stock',"Total qty can sale is "+val.split("|")[1]+" "+val.split("|")[2]+"");
                                        obj.find("input[name='inv_qtyConsignmentReturn[]']").val(val.split("|")[1]);
                                    }
                                }
                            });
                        }
                    });
                    $(".tblConsignmentReturn").mouseover(function(){
                        var text = $(this).attr('data-stock');
                        Tip(text);
                    });
                }else{
                    createSysAct('Consignment Return', 'Edit', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoice" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button></div>');
                    $(".printInvoice").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.consign_return_id,
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
                            backConsignmentReturn();
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
        
        $(".searchCustomerConsignmentReturn").click(function(){
            if(checkOrderDate() == true && $("#ConsignmentReturnCompanyId").val() != '' && $("#ConsignmentReturnBranchId").val() != ''){
                searchAllCustomerConsignmentReturn();
            }
        });

        $(".deleteCustomerConsignmentReturn").click(function(){
            if($(".tblConsignmentReturnList").find(".product_id").val() == undefined){
                removeCustomerConsignmentReturn();
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CUSTOMER_ON_CONSIGNMENT_RETURN; ?>";
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
                            removeCustomerConsignmentReturn();
                            $("#tblConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $("#ConsignmentReturnCustomerName").focus(function(){
            checkOrderDate();
        });
        
        $('#ConsignmentReturnCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#ConsignmentReturnCustomerName").autocomplete("<?php echo $this->base . "/consignment_returns/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyConsignmentReturn(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyConsignmentReturn(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            var strName = value.toString().split(".*")[1].split("-");
            var customerId = value.toString().split(".*")[0];
            var customerCode = value.toString().split(".*")[2];
            var warehouseId  = value.toString().split(".*")[3];
            $("#ConsignmentReturnProduct").attr("disabled", false);
            $("#ConsignmentReturnCustomerId").val(customerId);
            $("#ConsignmentReturnCustomerName").val(customerCode+" - "+strName[1]);
            $("#ConsignmentReturnCustomerName").attr("readonly","readonly");
            $(".searchCustomerConsignmentReturn").hide();
            $(".deleteCustomerConsignmentReturn").show();
            $("#ConsignmentReturnLocationGroupId").val(warehouseId);
            getCustomerContactConsignmentReturn(customerId);
        });
        
        // Action Order Date
        $('#ConsignmentReturnDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#ConsignmentReturnDate").datepicker("option", "maxDate", "<?php echo date("d/m/Y"); ?>");
        $.cookie("ConsignmentReturnDate", $("#ConsignmentReturnDate").val(), {expires : 7,path    : '/'});
        $("#ConsignmentReturnDate").change(function(){
            if($("#ConsignmentReturnCustomerName").val() == ""){
                $.cookie("ConsignmentReturnDate", $("#ConsignmentReturnDate").val(), {expires : 7,path    : '/'});
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_CHANGE_DATE_ON_CONSIGNMENT_RETURN; ?>";
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
                            $.cookie("ConsignmentReturnDate", $("#ConsignmentReturnDate").val(), {expires : 7,path    : '/'});
                            $(".deleteConsignmentReturnConsignment").click();
                            $("#tblConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentReturnDate").val($.cookie("ConsignmentReturnDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackConsignmentReturn").click(function(event){
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
                        backConsignmentReturn();
                    }
                }
            });
        });
        
        $(".searchConsignmentReturnConsignment").click(function(){
            searchConsignmentConsignmentReturn();
        });
        
        $(".deleteConsignmentReturnConsignment").click(function(){
            if($("#ConsignmentReturnCustomerName").val() == ""){
                removeConsignmentConsignmentReturn();
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CONSIGNMENT; ?>";
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
                            removeConsignmentConsignmentReturn();
                            $("#tblConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Company Action
        $.cookie('companyIdConsignmentReturn', $("#ConsignmentReturnCompanyId").val(), { expires: 7, path: "/" });
        $("#ConsignmentReturnCompanyId").change(function(){
            var obj    = $(this);
            if($(".tblConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie('companyIdConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                $("#ConsignmentReturnBranchId").filterOptions('com', obj.val(), '');
                $("#ConsignmentReturnBranchId").change();
                resetFormConsignmentReturn();
                changeInputCSSConsignmentReturn();
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
                            $.cookie('companyIdConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                            $("#ConsignmentReturnBranchId").filterOptions('com', obj.val(), '');
                            $("#ConsignmentReturnBranchId").change();
                            resetFormConsignmentReturn();
                            $("#tblConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentReturnCompanyId").val($.cookie("companyIdConsignmentReturn"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdConsignmentReturn', $("#ConsignmentReturnBranchId").val(), { expires: 7, path: "/" });
        $("#ConsignmentReturnBranchId").change(function(){
            var obj = $(this);
            if($(".tblConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie('branchIdConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                branchChangeConsignmentReturn(obj);
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
                            $.cookie('branchIdConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                            branchChangeConsignmentReturn(obj);
                            $("#tblConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#ConsignmentReturnBranchId").val($.cookie("branchIdConsignmentReturn"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Load Detail
        loadOrderDetailConsignmentReturn(1);
    });
    
    function removeCustomerConsignmentReturn(){
        $("#ConsignmentReturnCustomerId").val("");
        $("#ConsignmentReturnCustomerName").val("");
        $("#ConsignmentReturnCustomerName").removeAttr("readonly");
        $(".searchCustomerConsignmentReturn").show();
        $(".deleteCustomerConsignmentReturn").hide();
        $("#ConsignmentReturnLocationGroupId").val('');
        $(".deleteConsignmentReturnConsignment").click();
        $("#tblConsignmentReturn").html('');
    }
    
    function branchChangeConsignmentReturn(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        $("#ConsignmentReturnCode").val("<?php echo date("y"); ?>"+mCode);
    }
    
    function checkEmployeeType(type, typeName){
        var result = false;
        if(typeName == 'ConsignmentReturnConsignmentCode' && type == 1){
            result = true;
        } else if(typeName == 'ConsignmentReturnCollectorName' && type == 3){
            result = true;
        }
        return result;
    }
    
    function getCustomerContactConsignmentReturn(customerId){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getCustomerContact/"+customerId,
            beforeSend: function(){
                $("#ConsignmentReturnCustomerContactId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            },
            success: function(msg){
                $("#ConsignmentReturnCustomerContactId").html(msg);
            }
        });
    }
    
    function backConsignmentReturn(){
        oCache.iCacheLower = -1;
        oTableConsignmentReturn.fnDraw(false);
        $("#ConsignmentReturnEditForm").validationEngine("hideAll");
        var rightPanel = $("#ConsignmentReturnEditForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function checkCompanyConsignmentReturn(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#ConsignmentReturnCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormConsignmentReturn(){
        // Customer
        $(".deleteCustomerConsignmentReturn").click();
        // Employee
        $(".deleteConsignmentReturnConsignment").click();
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
                    $("#ConsignmentReturnIsApprove").val(0);
                    // Action Click Save
                    $("#ConsignmentReturnEditForm").submit();
                    $(this).dialog("close");
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function checkOrderDate(){
        if($("#ConsignmentReturnDate").val() == ''){
            $("#ConsignmentReturnDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkCustomerAddConsignmentReturn(field, rules, i, options){
        if($("#ConsignmentReturnCustomerId").val() == "" || $("#ConsignmentReturnCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }

    function loadOrderDetailConsignmentReturn(reload){
        if($("#ConsignmentReturnDate").val() != ''){
            var consignReturnId = 0;
            if(reload == 1){
                consignReturnId = <?php echo $this->data['ConsignmentReturn']['id']; ?>;
            }
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetail/"+consignReturnId,
                beforeSend: function(){
                    if(consignReturnId == 0){
                        $("#tblConsignmentReturn").html('');
                        $("#ConsignmentReturnTotalAmount").val("0.00");
                    }
                    $(".orderDetailConsignmentReturn").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".orderDetailConsignmentReturn").html(msg);
                    $(".footerFormConsignmentReturn").show();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        } else {
            clearOrderDetailConsignmentReturn();
        }
        
    }

    function clearOrderDetailConsignmentReturn(){
        $(".orderDetailConsignmentReturn").html("");
        $(".footerFormConsignmentReturn").hide();
    }
    
    function removeConsignmentConsignmentReturn(){
        $("#ConsignmentReturnConsignmentId").val('');
        $("#ConsignmentReturnConsignmentCode").val('');
        $("#ConsignmentReturnConsignmentCode").removeAttr('readonly','readonly');
        $(".searchConsignmentReturnConsignment").show();
        $(".deleteConsignmentReturnConsignment").hide();
    }
    
    function searchConsignmentConsignmentReturn(){
        var companyId = $("#ConsignmentReturnCompanyId").val();
        var branchId  = $("#ConsignmentReturnBranchId").val();
        var customerId = $("#ConsignmentReturnCustomerId").val();
        var locationGroupId = $("#ConsignmentReturnLocationGroupToId").val();
        if(companyId != "" && branchId != "" && customerId != "" && locationGroupId != ""){
            $("#ConsignmentReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var orderDate = $("#ConsignmentReturnDate").val();
            $("#ConsignmentReturnDate").datepicker("option", "dateFormat", "dd/mm/yy");
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/consignment/"+companyId+"/"+branchId+"/"+customerId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
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
                                if($("input[name='chkReturnConConsignment']:checked").val()){
                                    $("#ConsignmentReturnConsignmentId").val($("input[name='chkReturnConConsignment']:checked").val());
                                    $("#ConsignmentReturnConsignmentCode").val($("input[name='chkReturnConConsignment']:checked").attr("code"));
                                    $("#ConsignmentReturnConsignmentCode").attr("readonly","readonly");
                                    $(".searchConsignmentReturnConsignment").hide();
                                    $(".deleteConsignmentReturnConsignment").show();
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getConsignmentReturn/"+$("input[name='chkReturnConConsignment']:checked").val()+"/"+orderDate+"/<?php echo $this->data['ConsignmentReturn']['id']; ?>",
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblConsignmentReturn").html('');
                                                // Insert Row List
                                                $("#tblConsignmentReturn").append(tr);
                                                // Event Key Table List
                                                checkEventConsignmentReturn();
                                                // Sort
                                                sortNuTableConsignmentReturn();
                                            }
                                        }
                                    });
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        } else {
            alertSelectRequireField();
        }
    }
    
    function searchAllCustomerConsignmentReturn(){
        var companyId = $("#ConsignmentReturnCompanyId").val();
        if(companyId != '' && $("#ConsignmentReturnBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
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
                                    $("#ConsignmentReturnProduct").attr("disabled", false);
                                    $("#ConsignmentReturnCustomerId").val(customerId);
                                    $("#ConsignmentReturnCustomerName").val(customerCode+" - "+customerNameEn);
                                    $("#ConsignmentReturnCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerConsignmentReturn").hide();
                                    $(".deleteCustomerConsignmentReturn").show();
                                    $("#ConsignmentReturnLocationGroupId").val(warehouseId);
                                    // Get Customer Contact
                                    getCustomerContactConsignmentReturn(customerId);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function checkBfSaveConsignmentReturn(){
        var formName = "#ConsignmentReturnEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblConsignmentReturnList").find(".product").val() == undefined){
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
    
    function codeDialogConsignmentReturn(){
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
                    $(".saveConsignmentReturn").removeAttr("disabled");
                    $(".txtSaveConsignmentReturn").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveConsignmentReturn(){
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
                    var rightPanel=$("#ConsignmentReturnEditForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableConsignmentReturn.fnDraw(false);
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
                    $(".saveConsignmentReturn").removeAttr("disabled");
                    $(".txtSaveConsignmentReturn").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function changeInputCSSConsignmentReturn(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchConsignmentReturnConsignment").hide();
        if($("#ConsignmentReturnCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#ConsignmentReturnCustomerName").val() == ''){
                $(".searchCustomerConsignmentReturn").show();
            }
            if($("#ConsignmentReturnConsignmentCode").val() == ''){
                $(".searchConsignmentReturnConsignment").show();
            }
        } else {
            $(".lblSymbolConsignmentReturn").html('');
        }
        // Label
        $("#ConsignmentReturnEditForm").find("label").removeAttr("class");
        $("#ConsignmentReturnEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'ConsignmentReturnCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#ConsignmentReturnEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#ConsignmentReturnEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'ConsignmentReturnCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolConsignmentReturn").removeClass(cssRemove);
        $(".lblSymbolConsignmentReturn").addClass(cssStyle);
        // Input Readonly
        $("#ConsignmentReturnCustomerName").attr("readonly", readonly);
        $("#ConsignmentReturnConsignmentCode").attr("readonly", readonly);
        $("#ConsignmentReturnNote").attr("readonly", readonly);
        $("#searchProductUpcConsignmentReturn").attr("readonly", readonly);
        $("#searchProductSkuConsignmentReturn").attr("readonly", readonly);
    }
</script>
<?php echo $this->Form->create('ConsignmentReturn', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['ConsignmentReturn']['location_group_id']; ?>" id="ConsignmentReturnLocationGroupId" name="data[ConsignmentReturn][location_group_id]" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderConsignmentReturn">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="ConsignmentReturnTop">
    <legend><?php __(MENU_CUSTOMER_CONSIGNMENT_RETURN_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="consignmentReturnInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="ConsignmentReturnCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="ConsignmentReturnBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="ConsignmentReturnLocationGroupToId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[ConsignmentReturn][company_id]" id="ConsignmentReturnCompanyId" class="validate[required]" style="width: 70%;">
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
                                    <option vat-d="<?php echo $rowVATDefault[0]; ?>" <?php if($company['Company']['id'] == $this->data['ConsignmentReturn']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[ConsignmentReturn][branch_id]" id="ConsignmentReturnBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" <?php if($branch['Branch']['id'] == $this->data['ConsignmentReturn']['branch_id']){ ?>selected="selected"<?php } ?> com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['cus_consign_return_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                               <?php
                                echo $this->Form->input('location_group_to_id', array('empty' => INPUT_SELECT, 'label' => false, 'style' => 'width:200px')); 
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="ConsignmentReturnCode"><?php echo TABLE_CONSIGNMENT_RETURN_CODE; ?> <span class="red">*</span></label></td>
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
                        <td><label for="ConsignmentReturnNote"><?php echo TABLE_NOTE; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('note', array('style' => 'width:90%; height: 70px;')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> 
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <?php
                $consignmentCode  = "";
                if(!empty($this->data['ConsignmentReturn']['consignment_id'])){
                    $sqlConsignment = mysql_query("SELECT code FROM consignment_returns WHERE id = ".$this->data['ConsignmentReturn']['consignment_id']);
                    while($rowConsignment=mysql_fetch_array($sqlConsignment)){
                        $consignmentCode = $rowConsignment['code'];
                    }
                }
                ?>
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%;"><label for="ConsignmentReturnCustomerName"><?php echo TABLE_CUSTOMER_NAME; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="ConsignmentReturnCustomerContactId"><?php echo TABLE_CONTACT_NAME; ?></label></td>
                        <td style="width: 33%;"><label for="ConsignmentReturnConsignmentCode"><?php echo MENU_CONSIGNMENT; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('customer_name', array('value' => $this->data['Customer']['name'], 'readonly' => true, 'class' => 'validate[required,funcCall[checkCustomerAddConsignmentReturn]]', 'style' => 'width:70%')); ?>
                                <?php echo $this->Form->hidden('customer_id'); ?>
                                <img alt="Search" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchCustomerConsignmentReturn" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="deleteCustomerConsignmentReturn" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[ConsignmentReturn][customer_contact_id]" id="ConsignmentReturnCustomerContactId" style="width: 75%;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('consignment_id'); ?>
                                <?php echo $this->Form->text('consignment_code', array('value' => $consignmentCode, 'readonly' => true, 'style' => 'width:70%')); ?>
                                <img alt="Search" align="absmiddle" style="<?php if($consignmentCode != ''){ ?>display: none;<?php } ?> cursor: pointer; width: 22px; height: 22px;" class="searchConsignmentReturnConsignment" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="<?php if($consignmentCode == ''){ ?>display: none;<?php } ?> cursor: pointer;" class="deleteConsignmentReturnConsignment" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="ConsignmentReturnDate"><?php echo TABLE_CONSIGNMENT_RETURN_DATE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php 
                                $dateOrder = dateShort($this->data['ConsignmentReturn']['date']);
                                echo $this->Form->text('date', array('value' => $dateOrder, 'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:80%')); 
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div class="orderDetailConsignmentReturn" style="margin-top: 5px; text-align: center;"></div>
<div class="footerFormConsignmentReturn" style="display: none;">
    <div style="float: left; width: 35%;">
        <div class="buttons">
            <a href="#" class="positive btnBackConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveConsignmentReturn" >
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                <span class="txtSaveConsignmentReturn"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>