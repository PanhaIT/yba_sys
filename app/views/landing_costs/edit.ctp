<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

include('includes/function.php');
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        clearOrderDetailLandingCost();
        // Hide Branch
        $("#LandingCostBranchId").filterOptions('com', '<?php echo $this->data['LandingCost']['company_id']; ?>', '<?php echo $this->data['LandingCost']['branch_id']; ?>');
        $("#LandingCostEditForm").validationEngine();
        $(".saveLandingCost").click(function(){
            if(checkBfSaveLandingCost() == true){
                return true;
            }else{
                return false;
            }
        });
        $("#LandingCostEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveLandingCost").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#LandingCostDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .floatQty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Landed Cost', 'Edit', 2, result.responseText);
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
                        backLandingCost();
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
                    codeDialogLandingCost();
                }else if(result.code == "2"){
                    errorSaveLandingCost();
                }else{
                    createSysAct('Landed Cost', 'Edit', 1, '');
                    $("#dialog").html('<p><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
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
                            backLandingCost();
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
        
        $(".searchVendorLandingCost").click(function(){
            if(checkOrderDate() == true && $("#LandingCostCompanyId").val() != '' && $("#LandingCostBranchId").val() != ''){
                searchAllVendorLandingCost();
            }
        });

        $(".deleteVendorLandingCost").click(function(){
            removeCustomerLandingCost();
        });
        
        $("#LandingCostVendorName").focus(function(){
            checkOrderDate();
        });
        
        $('#LandingCostVendorName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $("#LandingCostVendorName").autocomplete("<?php echo $this->base . "/landing_costs/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyLandingCost(value.toString().split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyLandingCost(value.toString().split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            var vendorId = value.toString().split(".*")[0];
            var vendorName = value.toString().split(".*")[2];
            $("#LandingCostVendorId").val(vendorId);
            $("#LandingCostVendorName").val(vendorName);
            $("#LandingCostVendorName").attr("readonly","readonly");
            $(".searchVendorLandingCost").hide();
            $(".deleteVendorLandingCost").show();
        });
        
        
        // Action Order Date
        $('#LandingCostDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#LandingCostDate").datepicker("option", "maxDate", "<?php echo date("d/m/Y"); ?>");
        
        $(".btnBackLandingCost").click(function(event){
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
                        backLandingCost();
                    }
                }
            });
        });
        
        $(".searchLandingCostPurchaseBill").click(function(){
            searchPurchaseBillLandingCost();
        });
        
        $(".deleteLandingCostPurchaseBill").click(function(){
            if($(".tblLandingCostList").find(".product_id").val() == undefined){
                removePurchaseBillLandingCost();
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
                            removePurchaseBillLandingCost();
                            $("#tblLandingCost").html('');
                            calcTotalAmountLandingCost();
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
        $.cookie('companyIdLandingCost', $("#LandingCostCompanyId").val(), { expires: 7, path: "/" });
        $("#LandingCostCompanyId").change(function(){
            var obj    = $(this);
            if($(".tblLandingCostList").find(".product_id").val() == undefined){
                $.cookie('companyIdLandingCost', obj.val(), { expires: 7, path: "/" });
                $("#LandingCostBranchId").filterOptions('com', obj.val(), '');
                $("#LandingCostBranchId").change();
                resetFormLandingCost();
                checkChartAccountLadingCost();
                changeInputCSSLandingCost();
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
                            $.cookie('companyIdLandingCost', obj.val(), { expires: 7, path: "/" });
                            $("#LandingCostBranchId").filterOptions('com', obj.val(), '');
                            $("#LandingCostBranchId").change();
                            resetFormLandingCost();
                            checkChartAccountLadingCost();
                            $("#tblLandingCost").html('');
                            calcTotalAmountLandingCost();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#LandingCostCompanyId").val($.cookie("companyIdLandingCost"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdLandingCost', $("#LandingCostBranchId").val(), { expires: 7, path: "/" });
        $("#LandingCostBranchId").change(function(){
            var obj = $(this);
            if($(".tblLandingCostList").find(".product_id").val() == undefined){
                $.cookie('branchIdLandingCost', obj.val(), { expires: 7, path: "/" });
                branchChangeLandingCost(obj);
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
                            $.cookie('branchIdLandingCost', obj.val(), { expires: 7, path: "/" });
                            branchChangeLandingCost(obj);
                            $("#tblLandingCost").html('');
                            calcTotalAmountLandingCost();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#LandingCostBranchId").val($.cookie("branchIdLandingCost"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Load A/P By Company
        checkChartAccountLadingCost();
        // Check INPUT CSS
        changeInputCSSLandingCost();
        // Load Detail
        loadOrderDetailLandingCost('<?php echo $this->data['LandingCost']['id']; ?>');
        
    });
    
    function checkChartAccountLadingCost(){
        // A/P Filter
        $("#LandingCostChartAccountId").filterOptions('company_id', $("#LandingCostCompanyId").val(), '');
        
        if($("#LandingCostCompanyId").val() != ''){
            <?php
            if(!empty($apAccountId)){
            ?>
            $(".landed_cost_coa_id option[value='<?php echo $apAccountId; ?>']").attr('selected', true);
            <?php
            }
            ?>
        }
    }
    
    function removeCustomerLandingCost(){
        $("#LandingCostVendorId").val("");
        $("#LandingCostVendorName").val("");
        $("#LandingCostVendorName").removeAttr("readonly");
        $(".searchVendorLandingCost").show();
        $(".deleteVendorLandingCost").hide();
    } 
    
    function branchChangeLandingCost(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#LandingCostCode").val("<?php echo date("y"); ?>"+mCode);
        $("#LandingCostCurrencyCenterId").val(currency);
        $(".lblSymbolLandingCost").html(currencySymbol);
    }
    
    function backLandingCost(){
        oCache.iCacheLower = -1;
        oTableLandingCost.fnDraw(false);
        $("#LandingCostEditForm").validationEngine("hideAll");
        var rightPanel = $("#LandingCostEditForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function checkCompanyLandingCost(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#LandingCostCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormLandingCost(){
        // Customer
        $(".deleteVendorLandingCost").click();
        // Employee
        $(".deleteLandingCostPurchaseBill").click();
    }
    
    function checkOrderDate(){
        if($("#LandingCostDate").val() == ''){
            $("#LandingCostDate").focus();
            return false;
        }else{
            return true;
        }
    }

    function checkVendorAddLandingCost(field, rules, i, options){
        if($("#LandingCostVendorId").val() == "" || $("#LandingCostVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }

    function loadOrderDetailLandingCost(id){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/editDetail/"+id,
            beforeSend: function(){
                $(".orderDetailLandingCost").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                $("#tblLandingCost").html('');
                if(id == 0){
                    $("#LandingCostTotalAmount").val("0.00");
                }
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".orderDetailLandingCost").html(msg);
                $(".footerFormLandingCost").show();
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
            }
        });
    }

    function clearOrderDetailLandingCost(){
        $(".orderDetailLandingCost").html("");
        $(".footerFormLandingCost").hide();
    }
    
    function removePurchaseBillLandingCost(){
        $("#LandingCostPurchaseOrderId").val('');
        $("#LandingCostPurchaseBill").val('');
        $("#LandingCostPurchaseBill").removeAttr('readonly','readonly');
        $(".searchLandingCostPurchaseBill").show();
        $(".deleteLandingCostPurchaseBill").hide();
    }
    
    function searchPurchaseBillLandingCost(){
        var companyId = $("#LandingCostCompanyId").val();
        var branchId  = $("#LandingCostBranchId").val();
        if(companyId != "" && branchId != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/purchaseBill/"+companyId+"/"+branchId,
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
                                if($("input[name='chkPurchaseBillLandedCost']:checked").val()){
                                    $("#LandingCostPurchaseOrderId").val($("input[name='chkPurchaseBillLandedCost']:checked").val());
                                    $("#LandingCostPurchaseBill").val($("input[name='chkPurchaseBillLandedCost']:checked").attr("code"));
                                    $("#LandingCostPurchaseBill").attr("readonly","readonly");
                                    $(".searchLandingCostPurchaseBill").hide();
                                    $(".deleteLandingCostPurchaseBill").show();
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getPurchaseLandingCost/"+$("input[name='chkPurchaseBillLandedCost']:checked").val(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblLandingCost").html('');
                                                // Insert Row List
                                                $("#tblLandingCost").append(tr);
                                                // Event Key Table List
                                                checkEventLandingCost();
                                                // Sort
                                                sortNuTableLandingCost();
                                                // Calculate Total Amount
                                                calcTotalAmountLandingCost();
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
        }
    }
    
    function searchAllVendorLandingCost(){
        var companyId = $("#LandingCostCompanyId").val();
        if(companyId != '' && $("#LandingCostBranchId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/landing_costs/vendor/"; ?>"+companyId,
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
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                // calculate due_date
                                    var data = $("input[name='chkVendor']:checked").val();
                                    if(data){
                                        // Set Vendor
                                        $("#LandingCostVendorId").val(data.split('-')[0]);
                                        $("#LandingCostVendorName").val(data.split('-')[2]);
                                        $("#LandingCostVendorName").attr('readonly', true);
                                        $(".searchVendorLandingCost").hide();
                                        $(".deleteVendorLandingCost").show();
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
    
    function checkBfSaveLandingCost(){
        var formName = "#LandingCostEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".tblLandingCostList").find(".product").val() == undefined){
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
    
    function codeDialogLandingCost(){
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
                    $(".saveLandingCost").removeAttr("disabled");
                    $(".txtSaveLandingCost").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveLandingCost(){
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
                    var rightPanel=$("#LandingCostEditForm").parent();
                    var leftPanel=rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.show("slide", { direction: "left" }, 500);
                    oCache.iCacheLower = -1;
                    oTableLandingCost.fnDraw(false);
                }
            }
        });
    }
    
    function changeInputCSSLandingCost(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchLandingCostPurchaseBill").hide();
        if($("#LandingCostCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#LandingCostVendorName").val() == ''){
                $(".searchVendorLandingCost").show();
            }
            if($("#LandingCostPurchaseBill").val() == ''){
                $(".searchLandingCostPurchaseBill").show();
            }
        } else {
            $(".lblSymbolLandingCost").html('');
        } 
        // Label
        $("#LandingCostEditForm").find("label").removeAttr("class");
        $("#LandingCostEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'LandingCostCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#LandingCostEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#LandingCostEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'LandingCostCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolLandingCost").removeClass(cssRemove);
        $(".lblSymbolLandingCost").addClass(cssStyle);
        // Input Readonly
        $("#LandingCostVendorName").attr("readonly", readonly);
        $("#LandingCostNote").attr("readonly", readonly);
    }
</script>
<?php echo $this->Form->create('LandingCost', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" id="LandingCostCurrencyCenterId" name="data[LandingCost][currency_center_id]" value="<?php echo $this->data['LandingCost']['currency_center_id']; ?>" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderLandingCost">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="LandingCostTop">
    <legend><?php __(MENU_LANDED_COST_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" id="consignmentReturnInformation">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="LandingCostCode"><?php echo TABLE_CODE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="LandingCostDate"><?php echo TABLE_DATE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><?php if(count($branches) > 1){ ?><label for="LandingCostBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => true)); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('date', array('value' => dateShort($this->data['LandingCost']['date']),'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:70%')); ?>
                            </div>
                        </td>
                        <td>
                           <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[LandingCost][branch_id]" id="LandingCostBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['landed_cost_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 17%; vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="LandingCostReference"><?php echo TABLE_REFERENCE; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('reference', array('style' => 'width:70%')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;" colspan="2" rowspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="LandingCostNote"><?php echo TABLE_MEMO; ?></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->textarea('note', array('style' => 'width:92%; height: 65px;')); ?>
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
                        <td style="width: 34%;"><label for="LandingCostVendorName"><?php echo TABLE_VENDOR; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><label for="LandingCostPurchaseBill"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%;"><?php if(count($companies) > 1){ ?><label for="LandingCostCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('vendor_name', array('class' => 'validate[required,funcCall[checkVendorAddLandingCost]]', 'style' => 'width:70%', 'value' => $this->data['Vendor']['name'])); ?>
                                <?php echo $this->Form->hidden('vendor_id'); ?>
                                <img alt="Search" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchVendorLandingCost" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="deleteVendorLandingCost" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('purchase_order_id'); ?>
                                <?php echo $this->Form->text('purchase_bill', array('style' => 'width:70%', 'value' => $this->data['PurchaseOrder']['po_code'])); ?>
                                <img alt="Search" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchLandingCostPurchaseBill" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="deleteLandingCostPurchaseBill" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div> 
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[LandingCost][company_id]" id="LandingCostCompanyId" class="validate[required]" style="width: 75%;">
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
                                    <option <?php if($this->data['LandingCost']['company_id'] == $company['Company']['id']){ ?>selected=""<?php } ?> vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td><label for="LandingCostLandedCostTypeId"><?php echo MENU_LANDED_COST_TYPE; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('landed_cost_type_id', array('class' => 'validate[required]', 'style' => 'width:80%;', 'label' => false, 'empty' => INPUT_SELECT)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div class="orderDetailLandingCost" style=" margin-top: 5px; text-align: center;">
</div>
<div class="footerFormLandingCost">
    <div style="float: left; width: 19%;">
        <div class="buttons">
            <a href="#" class="positive btnBackLandingCost">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive saveLandingCost" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveLandingCost"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width: 350px;">
        <table style="width:100%">
            <tr>
                <td style="text-align: right;"><label for="LandingCostTotalAmount"><?php echo TABLE_TOTAL_AMOUNT; ?>:</label></td>
                <td style="width: 250px;">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 85%; height:15px; font-size:12px; font-weight: bold', 'value' => number_format($this->data['LandingCost']['total_amount'], $rowOption[0]))); ?> <span class="lblSymbolLandingCost"></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>