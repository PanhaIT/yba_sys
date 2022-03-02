<?php
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}

// Check Permission
$this->element('check_access');
$allowAddVendor   = checkAccess($user['User']['id'], 'vendors', 'quickAdd');

// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
$queryClosingDate = mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate  = mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#PurchaseReturnBranchId").filterOptions('com', '0', '');
        $("#PurchaseReturnLocationGroupId").chosen({ width: 200});
        clearOrderDetailAddPR();
        // Form Validate
        $("#PurchaseReturnAddForm").validationEngine('detach');
        $("#PurchaseReturnAddForm").validationEngine('attach');

        $(".btnSavePBC").click(function(){
            if(checkBfSavePR() == true){
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
                            $("#PurchaseReturnIsPreview").val('0');
                            // Action Click Save
                            $("#PurchaseReturnAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        
        $(".btnSavePreviewPBC").click(function(){
            if(checkBfSavePR() == true){
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
                            // Set Preview
                            $("#PurchaseReturnIsPreview").val('1');
                            // Form Submit
                            $("#PurchaseReturnAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        

        $("#PurchaseReturnAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#PurchaseReturnIsPreview").val() == '1'){
                    $(".txtSavePreviewPBC").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSavePBC").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#PurchaseReturnOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .qty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#PurchaseReturnTotalAmount").val($("#PurchaseReturnTotalAmount").val().replace(/,/g,""));
                $(".btnSavePBC, .btnSavePreviewPBC").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Bill Return', 'Add', 2, result.responseText);
                backBillReturn();
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
                    errorSavePR();
                }else{
                    createSysAct('Bill Return', 'Add', 1, '');
                    if($("#PurchaseReturnIsPreview").val() == '1'){
                        // Preview
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printInvoiceResult){
                                var w = window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printInvoiceResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                        // Reset Normal
                        $("#PurchaseReturnPreviewId").val(result.id);
                        $(".txtSavePreviewPBC").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".btnSavePBC, .btnSavePreviewPBC").attr("disabled", false);
                        $("#PurchaseReturnOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        saveContiunePurchaseReturn();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceBR" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_BILL_RETURN; ?></span></button></div> ');
                        $(".printInvoiceBR").click(function(){
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
        
        // Action Branch
        $("#PurchaseReturnBranchId").change(function(){
            var obj = $(this);
            if($(".tblPurchaseReturnList").find(".product_id").val() == undefined){
                $.cookie('branchIdBillReturn', obj.val(), { expires: 7, path: "/" });
                branchChangeBillReturn(obj);
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
                            $.cookie('branchIdBillReturn', obj.val(), { expires: 7, path: "/" });
                            branchChangeBillReturn(obj);
                            $("#tblPurchaseReturn").html('');
                            getTotalAmountPR();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseReturnBranchId").val($.cookie("branchIdBillReturn"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Company
        if($.cookie('companyIdPurchaseReturn')!=null || $("#PurchaseReturnCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdPurchaseReturn') != null){
                $("#PurchaseReturnCompanyId").val($.cookie('companyIdPurchaseReturn'));
            }
            var vatCal = $("#PurchaseReturnCompanyId").find("option:selected").attr("vat-opt");
            $("#PurchaseReturnVatCalculate").val(vatCal);
            $("#PurchaseReturnBranchId").filterOptions('com', $("#PurchaseReturnCompanyId").val(), '');
            $("#PurchaseReturnBranchId").change();
        }
        // Action Vendor
        $(".searchVendorAddPR").click(function(){
            if(checkOrderDateBR() == true && $("#PurchaseReturnCompanyId").val() != ""){
                searchVendorPR();
            }
        });
        
        $(".deletePurchaseReturnVendor").click(function(){
            $(this).hide();
            $(".searchVendorAddPR").show();
            $("#PurchaseReturnVendorId").val("");
            $("#PurchaseReturnVendorName").val("");
            $("#PurchaseReturnTotalAmount").val("0");
            $("#PurchaseReturnVendorName").removeAttr("readonly");
        });
        
        $("#PurchaseReturnVendorName").focus(function(){
            checkOrderDateBR();
        });
        
        $('#PurchaseReturnVendorName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });

        $("#PurchaseReturnVendorName").autocomplete("<?php echo $this->base."/".$this->params['controller']."/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.toString().split(".*")[2] + " - " + value.toString().split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.toString().split(".*")[2] + " - " + value.toString().split(".*")[1];
            }
        }).result(function(event, value){
            $(".deletePurchaseReturnVendor").show();
            $(".searchVendorAddPR").hide();
            $("#PurchaseReturnVendorId").val(value.toString().split(".*")[0]);
            $("#PurchaseReturnVendorName").val(value.toString().split(".*")[2] + " - " + value.toString().split(".*")[1]);
            $("#PurchaseReturnVendorName").attr('readonly','readonly');
        });

        // Action Location Group
        if($.cookie('locationGroupId')!=null){
            $("#PurchaseReturnLocationGroupId").val($.cookie('locationGroupId'));
            // Check Location With Location Group
            checkLocationByGroupBR();
        }
        
        $("#PurchaseReturnLocationGroupId").change(function(){
            var obj = $(this);
            if($(".tblPurchaseReturnList").find(".product").val() == undefined){
                $.cookie('locationGroupId', obj.val(), { expires: 7, path: "/" });
                $("#PurchaseReturnTotalAmount").val("0");
                // Check Location With Location Group
                checkLocationByGroupBR();
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
                            $.cookie('locationGroupId', obj.val(), { expires: 7, path: "/" });
                            $("#tblPurchaseReturn").html('');
                            getTotalAmountPR();
                            // Check Location With Location Group
                            checkLocationByGroupBR();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseReturnLocationGroupId").val($.cookie("locationGroupId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        if($.cookie('locationId')!=null){
            $("#PurchaseReturnLocationId").val($.cookie('locationId'));
        }
        $("#PurchaseReturnLocationId").change(function(){
            var obj = $(this);
            if($(".tblPurchaseReturnList").find(".product").val() == undefined){
                $.cookie('locationId', obj.val(), { expires: 7, path: "/" });
            }else{
                var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_LOCATION; ?>";
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
                            $.cookie('locationId', obj.val(), { expires: 7, path: "/" });
                            $("#tblPurchaseReturn").html('');
                            getTotalAmountPR();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseReturnLocationId").val($.cookie("locationId"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $('#PurchaseReturnOrderDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#PurchaseReturnOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#PurchaseReturnOrderDate").datepicker("option", "maxDate", 0);
        
        $("#PurchaseReturnOrderDate").change(function(){
            if($(".tblPurchaseReturnList").find(".product_id").val() == undefined){
                $.cookie("PurchaseReturnOrderDate", $("#PurchaseReturnOrderDate").val(), {expires:7, path:'/'});
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
                            $.cookie("PurchaseReturnOrderDate", $("#PurchaseReturnOrderDate").val(), {expires:7, path:'/'});
                            $("#tblPurchaseReturn").html('');
                            getTotalAmountPR();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseReturnOrderDate").val($.cookie("PurchaseReturnOrderDate"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackPurchaseReturn").click(function(event){
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
                        backBillReturn();
                    }
                }
            });
        });
        // Action Search Purchase Bill
        $(".searchPurchaseBillPR").click(function(){
            if($("#PurchaseReturnCompanyId").val() != "" && $("#PurchaseReturnBranchId").val() != ""){
                searchPurchaseBillPR();
            }
        });
        
        // Action Delete Purchase Bill
        $(".deletePurchaseBillBR").click(function(){
            $("#PurchaseReturnPurchaseOrderId").val('');
            $("#PurchaseReturnPurchaseOrderName").val('');
            $("#PurchaseReturnPurchaseOrderName").removeAttr('readonly');
            $(".searchPurchaseBillPR").show();
            $(".deletePurchaseBillBR").hide();
        });
        
        <?php
        if($allowAddVendor){
        ?>
        $("#addVendorPurchaseReturn").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/quickAdd/"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog3").html(msg);
                    $("#dialog3").dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '700',
                        height: '600',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#VendorQuickAddForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    if($("#VendorVgroupId").val() == null || $("#VendorVgroupId").val() == '' || $("#VendorPaymentTermId").val() == '' || $("#VendorPaymentTermId").val() == ''){
                                        alertSelectRequireField();
                                    } else {
                                        $(this).dialog("close");
                                        $.ajax({
                                            dataType: 'json',
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/vendors/quickAdd",
                                            data: $("#VendorQuickAddForm").serialize(),
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            error: function (result) {
                                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                                createSysAct('Purchase Return', 'Quick Add Vendor', 2, result);
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
                                                createSysAct('Purchase Return', 'Quick Add Vendor', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Vendor
                                                    $(".deletePurchaseReturnVendor").show();
                                                    $(".searchVendorAddPR").hide();
                                                    $("#PurchaseReturnVendorId").val(result.id);
                                                    $("#PurchaseReturnVendorName").val(result.name);
                                                    $("#PurchaseReturnVendorName").attr('readonly','readonly');
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
        
        // Reset Vendor
        $(".deletePurchaseReturnVendor").click();
        // Filter VAT
        checkVatCompanyBR();
        // Filter Location
        checkLocationByGroupBR();
        // Provet Input
        changeInputCSSBR();
        // Load Detail
        loadOrderDetailPurchaseReturn();
        loadAutoCompleteOff();
    }); // End Document Ready
    
    function searchPurchaseBillPR(){
        var companyId  = $("#PurchaseReturnCompanyId").val();
        var branchId   = $("#PurchaseReturnBranchId").val();
        var vendorId   = $("#PurchaseReturnVendorId").val();
        if(companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/purchaseBill/"+companyId+"/"+branchId+"/"+vendorId,
                data:   "sale_id=0",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PURCHASE_ORDER_MANAGEMENT_INFO; ?>',
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
                                if($("input[name='chkPurchaseBill']:checked").val()){
                                    $("#PurchaseReturnPurchaseOrderId").val($("input[name='chkPurchaseBill']:checked").val());
                                    $("#PurchaseReturnPurchaseOrderName").val($("input[name='chkPurchaseBill']:checked").attr("rel"));
                                    $("#PurchaseReturnPurchaseOrderName").attr('readonly', 'readonly');
                                    $(".searchPurchaseBillPR").hide();
                                    $(".deletePurchaseBillBR").show();
                                    var billId = $("input[name='chkPurchaseBill']:checked").val();
                                    // Vendor
                                    var vendorId     = $("input[name='chkPurchaseBill']:checked").attr('ven-id');
                                    var vendorCode   = $("input[name='chkPurchaseBill']:checked").attr("ven-code");
                                    var vendorName   = $("input[name='chkPurchaseBill']:checked").attr("ven-name");
                                    // Set Vendor
                                    $("#PurchaseReturnVendorId").val(vendorId);
                                    $("#PurchaseReturnVendorName").val(vendorCode+" - "+vendorName);
                                    $("#PurchaseReturnVendorName").attr('readonly','readonly');
                                    $(".searchVendorAddPR").hide();
                                    $(".deletePurchaseReturnVendor").show();
                                    // VAT 
                                    var vatSettingId = $("input[name='chkPurchaseBill']:checked").attr("vid");
                                    var vatCalculate = $("input[name='chkPurchaseBill']:checked").attr("vcal");
                                    var varPercent   = $("input[name='chkPurchaseBill']:checked").attr("vper");
                                    $("#PurchaseReturnVatCalculate").val(vatCalculate);
                                    $("#PurchaseReturnVatSettingId").find("option[value='"+vatSettingId+"']").attr("selected", true);
                                    $("#PurchaseReturnVatPercent").val(varPercent);
                                    changeLblVatCalOrder();
                                    // Get Product From Purchase Bill
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromBill/"+billId,
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
    
    function branchChangeBillReturn(obj){
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#PurchaseReturnCurrencyCenterId").val(currency);
        $(".lblSymbolBR").html(currencySymbol);
    }
    
    function changeLblVatCalBR(){
        var vatCal = $("#PurchaseReturnVatCalculate").val();
        $("#lblPurchaseReturnVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblPurchaseReturnVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblPurchaseReturnVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedBR(){
        var vatPercent = replaceNum($("#PurchaseReturnVatSettingId").find("option:selected").attr("rate"));
        var vatAccId   = replaceNum($("#PurchaseReturnVatSettingId").find("option:selected").attr("acc"));
        $("#PurchaseReturnVatPercent").val((vatPercent).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseReturnVatChartAccountId").val(vatAccId);
    }
    
    function checkVatCompanyBR(){
        // VAT Filter
        $("#PurchaseReturnVatSettingId").filterOptions('com-id', $("#PurchaseReturnCompanyId").val(), '');
    }
    
    function backBillReturn(){
        $("#PurchaseReturnAddForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oTablePurchaseReturn.fnDraw(false);
        var rightPanel = $(".btnBackPurchaseReturn").parent().parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide( "slide", { direction: "right" }, 500, function() {
            leftPanel.show();
            rightPanel.html('');
        });
        leftPanel.html("<?php echo ACTION_LOADING; ?>");
        leftPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/");
    }
    
    function saveContiunePurchaseReturn(){
        $("#PurchaseReturnAddForm").validationEngine("hideAll");
        var rightPanel = $("#PurchaseReturnAddForm").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
    }
    
    function checkLocationByGroupBR(){
        var locationGroup = $("#PurchaseReturnLocationGroupId").val();
        $("#PurchaseReturnLocationId").filterOptions('location-group', locationGroup, '');
    }
    
    function checkOrderDateBR(){
        if($("#PurchaseReturnOrderDate").val() == ""){
            $("#PurchaseReturnOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    function calcBillReturn(){
        var totalAmount = 0;
        $(".total_price").each(function(){
            totalAmount += replaceNum($(this).val());
        });
        $("#PurchaseReturnTotalAmount").val((parseFloat(totalAmount)).toFixed(3));
    }
    
    function checkVendorBR(field, rules, i, options){
        if($("#PurchaseReturnVendorId").val() == "" || $("#PurchaseReturnVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }
    
    function searchVendorPR(){
        var companyId = $("#PurchaseReturnCompanyId").val();
        if(companyId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/vendor/"+companyId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_VENDOR_MANAGEMENT_INFO; ?>',
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
                                // calculate due_date
                                var data = $("input[name='chkVendor']:checked").val();
                                if(data){
                                    $(".deletePurchaseReturnVendor").show();
                                    $(".searchVendorAddPR").hide();
                                    $("#PurchaseReturnVendorId").val(data.split('|||')[0]);
                                    $("#PurchaseReturnVendorName").val(data.split('|||')[1] + " - " + data.split('|||')[2]);
                                    $("#PurchaseReturnVendorName").attr('readonly','readonly');
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }

    function loadOrderDetailPurchaseReturn(){
        $(".footerPurchaseReturn").hide();
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/orderDetails",
            beforeSend: function(){
                $(".orderDetailPurchaseReturn").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                $("#tblPurchaseReturn").html('');
                $("#PurchaseReturnTotalAmount").val("0.00");
                $("#PurchaseReturnTotalVat").val("0");
                $("#PurchaseReturnSubTotalAmount").val("0.00");
                $("#PurchaseReturnStatus").attr("disabled");
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".orderDetailPurchaseReturn").html(msg);
                // Action VAT Status
                $("#PurchaseReturnVatSettingId").unbind("change");
                $("#PurchaseReturnVatSettingId").change(function(){
                    checkVatSelectedBR();
                    getTotalAmountPR();
                });
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
            }
        });
    }

    function clearOrderDetailAddPR(){
        $(".orderDetailPurchaseReturn").html("");
        $(".footerPurchaseReturn").hide();
        $("#PurchaseReturnTotalAmount").val("0.00");
        $("#PurchaseReturnTotalVat").val("0");
        $("#PurchaseReturnSubTotalAmount").val("0.00");
    }
    
    function checkBfSavePR(){
        $("#PurchaseReturnVendorName").removeClass("validate[required]");
        $("#PurchaseReturnVendorName").addClass("validate[required,funcCall[checkVendorBR]]");
        var formName = "#PurchaseReturnAddForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            $("#PurchaseReturnVendorName").removeClass("validate[required,funcCall[checkVendorBR]]");
            $("#PurchaseReturnVendorName").addClass("validate[required]");
            return false;
        }else{
            if($(".tblPurchaseReturnList").find(".product").val() == undefined){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_MAKE_ORDER;?></p>');
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
                $("#PurchaseReturnVendorName").removeClass("validate[required,funcCall[checkVendorBR]]");
                $("#PurchaseReturnVendorName").addClass("validate[required]");
                return false;
            }else{
                return true;
            }
        }
    }
    
    function errorSavePR(){
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
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    backBillReturn();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function changeInputCSSBR(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchVendorAddPR").hide();
        $("#divSearchBR").css("visibility", "hidden");
        if($("#PurchaseReturnCompanyId").val() != ''){
            var currencySymbol = $("#PurchaseReturnCompanyId").find("option:selected").attr("symbol");
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#PurchaseReturnVendorName").val() == ''){
                $(".searchVendorAddPR").show();
            }
            $("#divSearchBR").css("visibility", "visible");
            $(".lblSymbolBR").html(currencySymbol);
        } else {
            $(".lblSymbolBR").html('');
        }   
        // Label
        $("#PurchaseReturnAddForm").find("label").removeAttr("class");
        $("#PurchaseReturnAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'PurchaseReturnCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#PurchaseReturnAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#PurchaseReturnAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'PurchaseReturnCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolBR").removeClass(cssRemove);
        $(".lblSymbolBR").addClass(cssStyle);
        $(".lblSymbolBRPercent").removeClass(cssRemove);
        $(".lblSymbolBRPercent").addClass(cssStyle);
        // Input Readonly
        $("#PurchaseReturnVendorName").attr("readonly", readonly);
        $("#PurchaseReturnNote").attr("readonly", readonly);
        $("#PurchaseReturnCodeProduct").attr("readonly", readonly);
        // Put label VAT Calculate
        changeLblVatCalBR();
        // Check VAT Default
        getDefaultVatBR();
    }
    
    function getDefaultVatBR(){
        var vatDefault = $("#PurchaseReturnCompanyId option:selected").attr("vat-d");
        $("#PurchaseReturnVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedBR();
    }
</script>
<?php echo $this->Form->create('PurchaseReturn', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<input type="hidden" value="" name="data[PurchaseReturn][vat_calculate]" id="PurchaseReturnVatCalculate" />
<input type="hidden" value="" name="data[PurchaseReturn][currency_center_id]" id="PurchaseReturnCurrencyCenterId" />
<input type="hidden" value="" name="data[PurchaseReturn][preview_id]" id="PurchaseReturnPreviewId" />
<input type="hidden" value="0" id="PurchaseReturnIsPreview" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderBillReturn">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="BRTop">
    <legend><?php __(MENU_PURCHASE_RETURN_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td rowspan="2" style="width: 50%">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="PurchaseReturnOrderDate"><?php echo TABLE_DATE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="PurchaseReturnPrCode"><?php echo PURCHASE_RETURN_CODE; ?></label></td>
                        <td style="width: 33%"></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <input type="hidden" id="tmp_date" name="tmp_date" />
                                <?php echo $this->Form->text('order_date', array('value' => date("d/m/Y"),'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:75%')); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('pr_code', array('style' => 'width:70%', 'placeholder' => TABLE_AUTO_GENERATE)); ?>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2"><label for="PurchaseReturnVendorName"><?php echo TABLE_VENDOR; ?> <span class="red">*</span></label></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="inputContainer" style="width:100%">
                                <?php
                                echo $this->Form->hidden('vendor_id');
                                if($allowAddVendor){
                                ?>
                                <div class="addnewSmall" style="float: left;">
                                    <?php echo $this->Form->text('vendor_name', array('class' => 'validate[required]', 'style' => 'width: 285px; border: none;')); ?>
                                    <img alt="<?php echo MENU_VENDOR_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addVendorPurchaseReturn" onmouseover="Tip('<?php echo MENU_VENDOR_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                </div>
                                <?php
                                } else {
                                    echo $this->Form->text('vendor_name', array('class' => 'validate[required]', 'style' => 'width:320px'));
                                }
                                ?>
                                &nbsp;&nbsp;<img alt="<?php echo TABLE_SHOW_VENDOR_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchVendorAddPR" onmouseover="Tip('<?php echo TABLE_SHOW_VENDOR_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" style="cursor: pointer; height: 22px; display: none;" class="deletePurchaseReturnVendor" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" />
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </td>
            <td rowspan="2" style=" vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%;"><?php if(count($companies) > 1){ ?><label for="PurchaseReturnCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label><?php } ?></td>
                        <td style="width: 33%;"><?php if(count($locationGroups) > 1){ ?><label for="PurchaseReturnLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></label><?php } ?></td>
                        <td><label for="PurchaseReturnNote"><?php echo TABLE_MEMO;?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[PurchaseReturn][company_id]" id="PurchaseReturnCompanyId" class="validate[required]" style="width: 80%;">
                                    <?php
                                    if(count($companies) != 1){
                                    ?>
                                    <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($companies AS $company){
                                        $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 41 GROUP BY vat_modules.vat_setting_id LIMIT 1");
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
                            <div class="inputContainer" style="width:100%; <?php if(count($locationGroups) == 1){ ?>display: none;<?php } ?>">
                                <?php 
                                $emptyWare = INPUT_SELECT;
                                if(count($locationGroups) == 1){
                                    $emptyWare = false;
                                }
                                echo $this->Form->input('location_group_id', array('empty' => $emptyWare, 'label' => false)); ?>
                            </div> 
                        </td>
                        <td rowspan="3">
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('note', array('style' => 'width:90%; height: 60px;', 'class' => 'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php if(count($branches) > 1){ ?><label for="PurchaseReturnBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                        <td><?php if(count($locations) > 1){ ?><label for="PurchaseReturnLocationId"><?php echo TABLE_LOCATION; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[PurchaseReturn][branch_id]" id="PurchaseReturnBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['br_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($locations) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[PurchaseReturn][location_id]" id="PurchaseReturnLocationId" class="validate[required]" style="width: 80%;">
                                <?php
                                if(count($locations) > 1){
                                ?>
                                    <option value="" location-group="0"><?php echo INPUT_SELECT; ?></option>
                                <?php 
                                }
                                foreach($locations AS $location){
                                ?>
                                    <option value="<?php echo $location['Location']['id']; ?>" location-group="<?php echo $location['Location']['location_group_id']; ?>"><?php echo $location['Location']['name']; ?></option>
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
<div class="orderDetailPurchaseReturn" style=" margin-top: 5px; text-align: center;"></div>
<div class="footerPurchaseReturn" style="">
    <div style="float: left; width: 28%;">
        <div class="buttons">
            <a href="#" class="positive btnBackPurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePBC" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSavePBC"><?php echo ACTION_SAVE_NEW; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePreviewPBC" >
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSavePreviewPBC"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right;  width:70%">
        <table style="width:100%">
            <tr>
                <td style="width: 5%;"></td>
                <td style="width: 17%;"></td>
                <td style="width: 7%;"><label for="PurchaseReturnTotalAmount"><?php echo TABLE_SUB_TOTAL; ?> :</label></td>
                <td style="width:17%">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'validate[required] float', 'style' => 'width: 85%; height:15px; font-size:12px; font-weight: bold', 'value'=>'0.00')); ?> <span class="lblSymbolBR"></span>
                    </div>
                </td>
                <td style="text-align: right;">
                    <label for="PurchaseReturnVatSettingId" id="lblPurchaseReturnVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="PurchaseReturnVatSettingId" name="data[PurchaseReturn][vat_setting_id]" style="width: 75%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id, chart_account_id FROM vat_settings WHERE is_active = 1 AND type = 2 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" acc="<?php echo $rowVat['chart_account_id']; ?>"><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width:10%">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->hidden('vat_chart_account_id', array('class' => 'float', 'value' => '')); ?>
                        <?php echo $this->Form->hidden('total_vat', array('class' => 'float', 'value' => '0')); ?>
                        <?php echo $this->Form->text('vat_percent', array('readonly' => true, 'class' => 'validate[required] float', 'style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'value' => 0)); ?> <span class="lblSymbolBRPercent">(%)</span>
                    </div>
                </td>
                <td style="width:7%; text-align: right;"><label for="PurchaseReturnSubTotalAmount"><?php echo TABLE_TOTAL; ?> :</label></td>
                <td style="width:17%">
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('sub_total_amount', array('readonly' => true, 'class' => 'validate[required] float', 'style' => 'width: 85%; height:15px; font-size:12px; font-weight: bold', 'value'=>'0.00')); ?> <span class="lblSymbolBR"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
    <div id="loadProductPR" style="display: none"></div>
</div>
<?php echo $this->Form->end(); ?>