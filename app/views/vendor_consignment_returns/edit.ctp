<?php
include('includes/function.php');
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        loadOrderDetailVendorConsignmentReturn();
        // Hide Branch
        $("#VendorConsignmentReturnBranchId").filterOptions('com', '<?php echo $this->data['VendorConsignmentReturn']['company_id']; ?>', '<?php echo $this->data['VendorConsignmentReturn']['branch_id']; ?>');
        $("#VendorConsignmentReturnLocationGroupId").chosen();
        // Hide Location
        $("#VendorConsignmentReturnLocationId").filterOptions('location-group', '<?php echo $this->data['VendorConsignmentReturn']['location_group_id']; ?>', '<?php echo $this->data['VendorConsignmentReturn']['location_id']; ?>');
        $("#VendorConsignmentReturnEditForm").validationEngine();
        $(".saveVendorConsignmentReturn").click(function(){
            if(checkBfSaveVendorConsignmentReturn() == true){
                return true;
            }else{
                return false;
            }
        });
        $(".float").autoNumeric({mDec: 3});
        $("#VendorConsignmentReturnEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVendorConsignmentReturn").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#VendorConsignmentReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $(".floatQty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('VendorConsignmentReturn', 'Add', 2, result.responseText);
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
                        backVendorConsignmentReturn();
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
                    codeDialogVendorConsignmentReturn();
                }else if(result.code == "2"){
                    errorSaveVendorConsignmentReturn();
                }else if(result.code == "3"){
                    errorOutStock();
                    var listOutStock = result.listOutStock.split("-");
                    var obj = "";
                    $(".tblVendorConsignmentReturnList").each(function(){
                        if($(this).find("input[name='product_id[]']").val() != ""){
                            obj = $(this);
                            $.each(listOutStock, function(i, val){
                                if(val != ""){
                                    if(obj.find("input[name='product_id[]']").val() == val.split("|")[0]){
                                        obj.css("background","#fc8b8b");
                                        obj.attr('data-stock',"Total qty can sale is "+val.split("|")[1]+" "+val.split("|")[2]+"");
                                        obj.find("input[name='inv_qtyVendorConsignmentReturn[]']").val(val.split("|")[1]);
                                    }
                                }
                            });
                        }
                    });
                    $(".tblVendorConsignmentReturnList").mouseover(function(){
                        var text = $(this).attr('data-stock');
                        Tip(text);
                    });
                }else{
                    createSysAct('VendorConsignmentReturn', 'Add', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoice" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button></div>');
                    $(".printInvoice").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.vendor_consign_return_id,
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
                            backVendorConsignmentReturn();
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
        
        $(".searchVendorVendorConsignmentReturn").click(function(){
            if(checkOrderDate() == true && $("#VendorConsignmentReturnCompanyId").val() != '' && $("#VendorConsignmentReturnBranchId").val() != ''){
                searchAllVendorVendorConsignmentReturn();
            }
        });

        $(".deleteVendorVendorConsignmentReturn").click(function(){
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                removeVendorVendorConsignmentReturn();
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
                            removeVendorVendorConsignmentReturn();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $("#VendorConsignmentReturnVendorName").focus(function(){
            checkOrderDate();
        });
        
        $('#VendorConsignmentReturnVendorName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#VendorConsignmentReturnVendorName").autocomplete("<?php echo $this->base . "/vendor_consignment_returns/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyVendorConsignmentReturn(value.toString().split(".*")[8])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyVendorConsignmentReturn(value.toString().split(".*")[8])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            var vendorId = value.toString().split(".*")[0];
            var vendorName = value.toString().split(".*")[1];
            $("#VendorConsignmentReturnVendorId").val(vendorId);
            $("#VendorConsignmentReturnVendorName").val(vendorName);
            $("#VendorConsignmentReturnVendorName").attr("readonly","readonly");
            $(".searchVendorVendorConsignmentReturn").hide();
            $(".deleteVendorVendorConsignmentReturn").show();
        });
        
        $.cookie('VendorConsignmentReturnLocationGroupId', $("#VendorConsignmentReturnLocationGroupId").val(), { expires: 7, path: "/" });
        $("#VendorConsignmentReturnLocationGroupId").change(function(){
            var obj = $(this);
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie("VendorConsignmentReturnLocationGroupId", obj.val(), {expires : 7,path    : '/'});
                 checkLocationByGroupVendorConsignmentReturn();
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
                            $.cookie("VendorConsignmentReturnLocationGroupId", obj.val(), {expires : 7,path    : '/'});
                            checkLocationByGroupVendorConsignmentReturn();
                            $("#tblVendorConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentReturnLocationGroupId").val($.cookie("VendorConsignmentReturnLocationGroupId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Action Order Date
        $('#VendorConsignmentReturnDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#VendorConsignmentReturnDate").datepicker("option", "maxDate", 0);
        
        $.cookie("VendorConsignmentReturnDate", $("#VendorConsignmentReturnDate").val(), {expires : 7,path    : '/'});
        $("#VendorConsignmentReturnDate").change(function(){
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie("VendorConsignmentReturnDate", $("#VendorConsignmentReturnDate").val(), {
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
                            $.cookie("VendorConsignmentReturnDate", $("#VendorConsignmentReturnDate").val(), {expires : 7,path    : '/'});
                            $("#tblVendorConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentReturnDate").val($.cookie("VendorConsignmentReturnDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackVendorConsignmentReturn").click(function(event){
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
                        backVendorConsignmentReturn();
                    }
                }
            });
        });
        $(".searchVendorConsignmentReturnVendorConsignment").click(function(){
            searchVendorConsignmentVendorConsignmentReturn();
        });
        
        $(".deleteVendorConsignmentReturnVendorConsignment").click(function(){
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                removeVendorConsignmentVendorConsignmentReturn();
            }else{
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_VENDOR_CONSIGNMENT; ?>";
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
                            removeVendorConsignmentVendorConsignmentReturn();
                            $("#tblVendorConsignmentReturn").html('');
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
        $.cookie('companyIdVendorConsignmentReturn', $("#VendorConsignmentReturnCompanyId").val(), { expires: 7, path: "/" });
        $("#VendorConsignmentReturnCompanyId").change(function(){
            var obj    = $(this);
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie('companyIdVendorConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                $("#VendorConsignmentReturnBranchId").filterOptions('com', obj.val(), '');
                $("#VendorConsignmentReturnBranchId").change();
                resetFormVendorConsignmentReturn();
                checkVatCompanyVendorConsignmentReturn();
                changeInputCSSVendorConsignmentReturn();
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
                            $.cookie('companyIdVendorConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                            $("#VendorConsignmentReturnBranchId").filterOptions('com', obj.val(), '');
                            $("#VendorConsignmentReturnBranchId").change();
                            resetFormVendorConsignmentReturn();
                            $("#tblVendorConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentReturnCompanyId").val($.cookie("companyIdVendorConsignmentReturn"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdVendorConsignmentReturn', $("#VendorConsignmentReturnBranchId").val(), { expires: 7, path: "/" });
        $("#VendorConsignmentReturnBranchId").change(function(){
            var obj = $(this);
            if($(".tblVendorConsignmentReturnList").find(".product_id").val() == undefined){
                $.cookie('branchIdVendorConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                branchChangeVendorConsignmentReturn(obj);
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
                            $.cookie('branchIdVendorConsignmentReturn', obj.val(), { expires: 7, path: "/" });
                            branchChangeVendorConsignmentReturn(obj);
                            $("#tblVendorConsignmentReturn").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentReturnBranchId").val($.cookie("branchIdVendorConsignmentReturn"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    });
    
    function checkLocationByGroupVendorConsignmentReturn(){
        var locationGroup = $("#VendorConsignmentReturnLocationGroupId").val();
        $("#VendorConsignmentReturnLocationId").filterOptions('location-group', locationGroup, '');
    }
    
    function removeVendorVendorConsignmentReturn(){
        $("#VendorConsignmentReturnCustomerId").val("");
        $("#VendorConsignmentReturnVendorName").val("");
        $("#VendorConsignmentReturnVendorName").removeAttr("readonly");
        $(".searchVendorVendorConsignmentReturn").show();
        $(".deleteVendorVendorConsignmentReturn").hide();
        $(".deleteVendorConsignmentReturnVendorConsignment").click();
        $("#tblVendorConsignmentReturn").html('');
    } 
    
    function branchChangeVendorConsignmentReturn(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#VendorConsignmentReturnCode").val("<?php echo date("y"); ?>"+mCode);
        $("#VendorConsignmentReturnCurrencyCenterId").val(currency);
        $(".lblSymbolVendorConsignmentReturn").html(currencySymbol);
    }
    
    function backVendorConsignmentReturn(){
        oCache.iCacheLower = -1;
        oTableVendorConsignmentReturn.fnDraw(false);
        $("#VendorConsignmentReturnEditForm").validationEngine("hideAll");
        var rightPanel = $("#VendorConsignmentReturnEditForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function checkCompanyVendorConsignmentReturn(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#VendorConsignmentReturnCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormVendorConsignmentReturn(){
        // Customer
        $(".deleteVendorVendorConsignmentReturn").click();
    }
    
    function checkOrderDate(){
        if($("#VendorConsignmentReturnDate").val() == ''){
            $("#VendorConsignmentReturnDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkVendorVendorConsignmentReturn(field, rules, i, options){
        if($("#VendorConsignmentReturnCustomerId").val() == "" || $("#VendorConsignmentReturnVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }

    function loadOrderDetailVendorConsignmentReturn(){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetail/<?php echo $this->data['VendorConsignmentReturn']['id']; ?>",
            beforeSend: function(){
                $(".orderDetailVendorConsignmentReturn").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                $("#tblVendorConsignmentReturn").html('');
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".orderDetailVendorConsignmentReturn").html(msg);
                $(".footerFormVendorConsignmentReturn").show();
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
            }
        });
    }

    function clearOrderDetailVendorConsignmentReturn(){
        $(".orderDetailVendorConsignmentReturn").html("");
        $(".footerFormVendorConsignmentReturn").hide();
    }
    
    function removeVendorConsignmentVendorConsignmentReturn(){
        $("#VendorConsignmentReturnVendorConsignmentId").val('');
        $("#VendorConsignmentReturnVendorConsignment").val('');
        $("#VendorConsignmentReturnVendorConsignment").removeAttr('readonly','readonly');
        $(".searchVendorConsignmentReturnVendorConsignment").show();
        $(".deleteVendorConsignmentReturnVendorConsignment").hide();
    }
    
    function searchVendorConsignmentVendorConsignmentReturn(){
        var companyId = $("#VendorConsignmentReturnCompanyId").val();
        var branchId  = $("#VendorConsignmentReturnBranchId").val();
        var locationGroupId  = $("#VendorConsignmentReturnLocationGroupId").val();
        var locationId  = $("#VendorConsignmentReturnLocationId").val();
        var vendorId  = $("#VendorConsignmentReturnVendorId").val();
        if(companyId != "" && branchId != "" && vendorId != "" && locationGroupId != "" && locationId != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/vendorConsignment/"+companyId+"/"+branchId+"/"+locationGroupId+"/"+locationId+"/"+vendorId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_VENDOR_CONSIGNMENT_INFO; ?>',
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
                                if($("input[name='chkVendorConsignmentReturn']:checked").val()){
                                    $("#VendorConsignmentReturnDate").datepicker("option", "dateFormat", "yy-mm-dd");
                                    var orderDate = $("#VendorConsignmentReturnDate").val();
                                    $("#VendorConsignmentReturnDate").datepicker("option", "dateFormat", "dd/mm/yy");
                                    $("#VendorConsignmentReturnVendorConsignmentId").val($("input[name='chkVendorConsignmentReturn']:checked").val());
                                    $("#VendorConsignmentReturnVendorConsignment").val($("input[name='chkVendorConsignmentReturn']:checked").attr("code"));
                                    $("#VendorConsignmentReturnVendorConsignment").attr("readonly","readonly");
                                    $(".searchVendorConsignmentReturnVendorConsignment").hide();
                                    $(".deleteVendorConsignmentReturnVendorConsignment").show();
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getVendorConsignmentReturn/"+$("input[name='chkVendorConsignmentReturn']:checked").val()+"/"+orderDate,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Insert Row List
                                                $("#tblVendorConsignmentReturn").html(tr);
                                                // Event Key Table List
                                                checkEventVendorConsignmentReturn();
                                                // Sort
                                                sortNuTableVendorConsignmentReturn();
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
    
    function searchAllVendorVendorConsignmentReturn(){
        var companyId = $("#VendorConsignmentReturnCompanyId").val();
        if(companyId != '' && $("#VendorConsignmentReturnBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/vendor_consignment_returns/vendor/"; ?>"+companyId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_VENDOR; ?>',
                        resizable: false,
                        modal: true,
                        width: 850,
                        height: 500,
                        VendorConsignmentsition:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(){
                            timeBarcodeVendorConsignmentReturn = 1;
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                // calculate due_date
                                    var data = $("input[name='chkVendor']:checked").val();
                                    if(data){
                                        // Set Vendor
                                        $("#VendorConsignmentReturnVendorId").val(data.split('-')[0]);
                                        $("#VendorConsignmentReturnVendorName").val(data.split('-')[2]);
                                        $("#VendorConsignmentReturnVendorName").attr('readonly', true);
                                        $(".searchVendorVendorConsignmentReturn").hide();
                                        $(".deleteVendorVendorConsignmentReturn").show();
                                    }
                                    timeBarcodeVendorConsignmentReturn = 1;
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
    
    function checkBfSaveVendorConsignmentReturn(){
        var formName = "#VendorConsignmentReturnEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblVendorConsignmentReturnList").find(".product").val() == undefined){
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
    
    function codeDialogVendorConsignmentReturn(){
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
                    $(".saveVendorConsignmentReturn").removeAttr("disabled");
                    $(".txtSaveVendorConsignmentReturn").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveVendorConsignmentReturn(){
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
                    var rightPanel=$("#VendorConsignmentReturnEditForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableVendorConsignmentReturn.fnDraw(false);
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
                    $(".saveVendorConsignmentReturn").removeAttr("disabled");
                    $(".txtSaveVendorConsignmentReturn").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function changeInputCSSVendorConsignmentReturn(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchVendorVendorConsignmentReturn").hide();
        $(".searchVendorConsignmentReturnVendorConsignment").hide();
        if($("#VendorConsignmentReturnCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#VendorConsignmentReturnVendorName").val() == ''){
                $(".searchVendorVendorConsignmentReturn").show();
            }
            // Button Search
            if($("#VendorConsignmentReturnVendorConsignment").val() == ''){
                $(".searchVendorConsignmentReturnVendorConsignment").show();
            }
        } else {
            $(".lblSymbolVendorConsignmentReturn").html('');
        } 
        // Label
        $("#VendorConsignmentReturnEditForm").find("label").removeAttr("class");
        $("#VendorConsignmentReturnEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'VendorConsignmentReturnCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#VendorConsignmentReturnEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#VendorConsignmentReturnEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'VendorConsignmentReturnCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolVendorConsignmentReturn").removeClass(cssRemove);
        $(".lblSymbolVendorConsignmentReturn").addClass(cssStyle);
        // Input Readonly
        $("#VendorConsignmentReturnVendorName").attr("readonly", readonly);
        $("#VendorConsignmentReturnNote").attr("readonly", readonly);
        $("#searchProductUpcVendorConsignmentReturn").attr("readonly", readonly);
        $("#searchProductSkuVendorConsignmentReturn").attr("readonly", readonly);
    }
</script>
<?php echo $this->Form->create('VendorConsignmentReturn', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderVendorConsignmentReturn">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="VendorConsignmentReturnTop">
    <legend><?php __(MENU_CUSTOMER_CONSIGNMENT_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="saleInvoiceInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="VendorConsignmentReturnCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="VendorConsignmentReturnBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="VendorConsignmentReturnLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[VendorConsignmentReturn][company_id]" id="VendorConsignmentReturnCompanyId" class="validate[required]" style="width: 70%;">
                                    <?php
                                    if(count($companies) != 1){
                                    ?>
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($companies AS $company){
                                    ?>
                                    <option <?php if($company['Company']['id'] == $this->data['VendorConsignmentReturn']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <select name="data[VendorConsignmentReturn][branch_id]" id="VendorConsignmentReturnBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" <?php if($branch['Branch']['id'] == $this->data['VendorConsignmentReturn']['branch_id']){ ?>selected="selected"<?php } ?> com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['ven_consign_return_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
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
                        <td><label for="VendorConsignmentReturnLocationId"><?php echo TABLE_LOCATION; ?></label> <span class="red">*</span></td>
                    </tr>
                    <tr>
                        <td>
                           <div class="inputContainer" style="width:100%">
                                <select name="data[VendorConsignmentReturn][location_id]" id="VendorConsignmentReturnLocationId" class="validate[required]" style="width: 75%;">
                                    <option value="" location-group="0"><?php echo INPUT_SELECT; ?></option>
                                    <?php 
                                    foreach($locations AS $location){
                                    ?>
                                        <option value="<?php echo $location['Location']['id']; ?>" <?php if($location['Location']['id'] == $this->data['VendorConsignmentReturn']['location_id']){ ?>selected="selected"<?php } ?> location-group="<?php echo $location['Location']['location_group_id']; ?>"><?php echo $location['Location']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div> 
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;" rowspan="2" colspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="VendorConsignmentReturnNote"><?php echo TABLE_NOTE; ?></label></td>
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
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%;"><label for="VendorConsignmentReturnVendorName"><?php echo TABLE_VENDOR; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="VendorConsignmentReturnVendorConsignment"><?php echo MENU_VENDOR_CONSIGNMENT; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="VendorConsignmentReturnCode"><?php echo TABLE_CONSIGNMENT_CODE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php 
                                echo $this->Form->text('vendor_name', array('value' => $this->data['Vendor']['name'], 'class' => 'validate[required,funcCall[checkVendorVendorConsignmentReturn]]', 'style' => 'width:70%'));
                                echo $this->Form->hidden('vendor_id'); ?>
                                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchVendorVendorConsignmentReturn" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteVendorVendorConsignmentReturn" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php 
                                $consignmentCode  = "";
                                if(!empty($this->data['VendorConsignmentReturn']['vendor_consignment_id'])){
                                    $sqlVenConsignment = mysql_query("SELECT code FROM vendor_consignments WHERE id = ".$this->data['VendorConsignmentReturn']['vendor_consignment_id']);
                                    while($rowVenConsignment=mysql_fetch_array($sqlVenConsignment)){
                                        $consignmentCode = $rowVenConsignment['code'];
                                    }
                                }
                                echo $this->Form->hidden('vendor_consignment_id'); 
                                echo $this->Form->text('vendor_consignment', array('name' => '', 'class' => 'validate[required]', 'style' => 'width:70%', 'value'=> $consignmentCode)); ?>
                                <img alt="Search" search="1" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchVendorConsignmentReturnVendorConsignment" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" search="0" align="absmiddle" style="cursor: pointer;" class="deleteVendorConsignmentReturnVendorConsignment" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => true)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="VendorConsignmentReturnDate"><?php echo TABLE_CONSIGNMENT_DATE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php 
                                $dateOrder = dateShort($this->data['VendorConsignmentReturn']['date']);
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
<div class="orderDetailVendorConsignmentReturn" style="margin-top: 5px; text-align: center;"></div>
<div class="footerFormVendorConsignmentReturn" style="display: none;">
    <div style="float: left; width: 19%;">
        <div class="buttons">
            <a href="#" class="positive btnBackVendorConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveVendorConsignmentReturn" >
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                <span class="txtSaveVendorConsignmentReturn"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>