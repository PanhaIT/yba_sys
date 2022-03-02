<?php 
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
// Authentication
$this->element('check_access');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');

include("includes/function.php");
echo $this->element('prevent_multiple_submit'); 
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
<script type="text/javascript">
    var fieldRequireTransfer = ['TransferOrderFromLocationGroupId', 'TransferOrderToLocationGroupId'];
    var timeSearchAddTO = 0;
    var rowDeailTO      = $("#recordTODetail");
    var rowIndexTO;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#TransferOrderBranchId").filterOptions('com', '<?php echo $this->data['TransferOrder']['company_id']; ?>', '<?php echo $this->data['TransferOrder']['branch_id']; ?>');
        $("#TransferOrderFromLocationGroupId, #TransferOrderToLocationGroupId").chosen({ width: 275 });
        // Remove Row Detail
        $("#recordTODetail").remove();
        
        // Varaible Delay
        var delayTimeTOadd = (function () {
          var timers = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
              clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
          };
        })();
        
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabTOReg != tabTOId){
            $("a[href='"+tabTOId+"']").click(function(){
                if($("#bodyListTO").html() != '' && $("#bodyListTO").html() != null){
                    delayTimeTOadd(function(){
                        refreshScreenTOAdd();
                        resizeFormTitleTOAdd();
                        resizeFornScrollTO();  
                    }, 500, "Finish");
                }
            });
            tabTOReg = tabTOId;
        }
        
        // Calculate Form Table List
        delayTimeTOadd(function(){
              refreshScreenTOAdd();
              resizeFormTitleTOAdd();
              resizeFornScrollTO();  
        }, 500, "Finish");
        
        // Calculate Form Table List After Window Resize
        $(window).resize(function(){
            if(tabTOReg == $(".ui-tabs-selected a").attr("href")){
                delayTimeTOadd(function(){
                    refreshScreenTOAdd();
                    resizeFormTitleTOAdd();
                    resizeFornScrollTO();
                  }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderTransferOrder").click(function(){
            var TransferOrderCompanyId           = $("#TransferOrderCompanyId").val();
            var TransferOrderBranchId            = $("#TransferOrderBranchId").val();
            var TransferOrderFromLocationGroupId = $("#TransferOrderFromLocationGroupId").val();
            var TransferOrderToLocationGroupId   = $("#TransferOrderToLocationGroupId").val();
            var TransferOrderOrderDate           = $("#TransferOrderOrderDate").val();
            var TransferOrderToCode              = $("#TransferOrderToCode").val();
            
            if(TransferOrderCompanyId == "" || TransferOrderBranchId == "" || TransferOrderFromLocationGroupId == "" || TransferOrderToLocationGroupId == "" || TransferOrderOrderDate == "" || TransferOrderToCode == ""){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_WARNING; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }else{
                var label  = $(this).find("span").text();
                var action = '';
                var img    = '<?php echo $this->webroot . 'img/button/'; ?>';
                if(label == 'Hide'){
                    action = 'Show';
                    $("#headerTO").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#headerTO").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabTOReg == $(".ui-tabs-selected a").attr("href")){
                    delayTimeTOadd(function(){
                        resizeFornScrollTO();
                    }, 300, "Finish");
                }
            }            
        });
        
        $(".btnSaveTO").click(function(){
            if(checkBfSaveTO() == true){
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
                        '<?php echo ACTION_SAVE; ?>': function() {
                            // Action Click Save
                            $("#TransferOrderEditForm").submit();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }else{
                return false;
            }
        });
        
        // Form Validate
        $("#TransferOrderEditForm").validationEngine('detach');
        $("#TransferOrderEditForm").validationEngine('attach');
        
        // Action Form Save
        $("#TransferOrderEditForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequireTransfer) == false){
                    alertSelectRequireField();
                    $(".btnSaveTO").removeAttr('disabled');
                    return false;
                }
                // Change Format Date
                $("#TransferOrderOrderDate, #TransferOrderFulfillmentDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".qty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                // Assign Label Loading
                $(".txtSaveTO").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Transfer Order', 'Edit', 2, result.responseText);
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
            success: function(result) {
                // Change Format Date
                $("#TransferOrderOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveTO").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error_code == '1'){
                    disableEnableSave(false);
                    // Alert Message Code is aleady taken
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM." ".MESSAGE_PLEASE_CHANGE_AND_SAVE_AGAIN; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == '1'){
                    backTransferOrder();
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == '2'){
                    backTransferOrder();
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else{
                    createSysAct('Transfer Order', 'Edit', 1, '');
                    backTransferOrder();
                    // Alert message
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceTO"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_TRANSFER_ORDER; ?></span></button>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        closeOnEscape: false,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        close: function(event, ui){
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    $(".printInvoiceTO").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/'; ?>transfer_orders/printInvoice/"+result.id,
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
                }
            }
        });
        
        // Assign Date Picker to Order Date & Fulfillment Date
        var dates = $("#TransferOrderOrderDate, #TransferOrderFulfillmentDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var dateObj = $(this);
                if($(this).attr("id") == "TransferOrderOrderDate" && checkExistRecordListTO() == true){
                    var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_ORDER_DATE; ?>";
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
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
                                var option = dateObj.attr("id") == "TransferOrderOrderDate" ? "minDate" : "maxDate", instance = dateObj.data( "datepicker" );
                                date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
                                dates.not( dateObj ).datepicker( "option", option, date );
                                setCookie('TransferOrderOrderDate', dateObj.val());
                                clearTableListTO();
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                useCookie("#TransferOrderOrderDate", 'TransferOrderOrderDate');
                                $(this).dialog("close");
                            }
                        }
                    });
                }else{
                    var option = dateObj.attr("id") == "TransferOrderOrderDate" ? "minDate" : "maxDate", 
                    instance = dateObj.data( "datepicker" );
                    date = $.datepicker.parseDate(instance.settings.dateFormat || 
                    $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
                    dates.not( dateObj ).datepicker( "option", option, date );
                    setCookie('TransferOrderOrderDate', dateObj.val());
                }
            }
        }).unbind("blur");
        $("#TransferOrderOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#TransferOrderOrderDate").datepicker("option", "maxDate", 0);
        
        // Action Button Back
        $(".btnBackTransferOrder").click(function(event){
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
                        backTransferOrder();
                    }
                }
            });
        });
        
        // Action Change From/To Warehouse
        setCookie('TransferOrderFromLocationGroupId', $("#TransferOrderFromLocationGroupId").val());
        setCookie('TransferOrderToLocationGroupId', $("#TransferOrderToLocationGroupId").val());
        $("#TransferOrderFromLocationGroupId, #TransferOrderToLocationGroupId").change(function(){
            var obj = $(this);
            var id  = obj.attr('id');
            if(checkExistRecordListTO() == true){
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
                            if(id == "TransferOrderFromLocationGroupId"){
                                setCookie('TransferOrderFromLocationGroupId', obj.val());
                            }else{
                                setCookie('TransferOrderToLocationGroupId', obj.val());
                            }
                            clearTableListTO();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            if(id == "TransferOrderFromLocationGroupId"){
                                useCookie("#TransferOrderFromLocationGroupId", 'TransferOrderFromLocationGroupId');
                            }else{
                                useCookie("#TransferOrderToLocationGroupId", 'TransferOrderToLocationGroupId');
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }else{
                if(id == "TransferOrderFromLocationGroupId"){
                    setCookie('TransferOrderFromLocationGroupId', obj.val());
                }else{
                    setCookie('TransferOrderToLocationGroupId', obj.val());
                }
            }
        });
        
        // Search Product SKU Auto Complete
        $("#searchProductSKU").autocomplete("<?php echo $this->base . "/transfer_orders/searchProduct/"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            if(timeSearchAddTO == 0){
                timeSearchAddTO = 1;
                searchProductTO(code);
            }
        });
        // Search Product List
        $(".searchToProduct").click(function(){
            if(timeSearchAddTO == 0){
                timeSearchAddTO = 1;
                searchProductListTO();
            }
        });
        
        // Company Action
        $.cookie('companyIdTransferOrder', $("#TransferOrderCompanyId").val(), { expires: 7, path: "/" });
        $("#TransferOrderCompanyId").change(function(){
            var obj   = $(this);
            if($(".recordTODetail").find(".product_id").val() == undefined){
                $.cookie('companyIdTransferOrder', obj.val(), { expires: 7, path: "/" });
                $("#TransferOrderBranchId").filterOptions('com', obj.val(), '');
                $("#TransferOrderBranchId").change();
                changeInputCSSTransferOrder();
            } else {
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
                            $.cookie('companyIdTransferOrder', obj.val(), { expires: 7, path: "/" });
                            $("#TransferOrderBranchId").filterOptions('com', obj.val(), '');
                            $("#TransferOrderBranchId").change();
                            // Reload Page
                            $("#tblTO").html('');
                            changeInputCSSTransferOrder();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#TransferOrderCompanyId").val($.cookie('companyIdTransferOrder'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdTransferOrder', $("#TransferOrderBranchId").val(), { expires: 7, path: "/" });
        $("#TransferOrderBranchId").change(function(){
            var obj = $(this);
            if($(".recordTODetail").find(".product_id").val() == undefined){
                $.cookie('branchIdTransferOrder', obj.val(), { expires: 7, path: "/" });
                branchChangeTransferOrder(obj);
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
                            $.cookie('branchIdTransferOrder', obj.val(), { expires: 7, path: "/" });
                            branchChangeTransferOrder(obj);
                            // Reload Page
                            $("#tblTO").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#TransferOrderBranchId").val($.cookie("branchIdTransferOrder"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        <?php
        if($allowAddProduct){
        ?>
        $("#addProductTransfer").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/quickAdd/"; ?>",
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
                        width: '900',
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
                                var formName = "#ProductQuickAddForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    <?php 
                                    if(count($branches) > 1){
                                    ?>
                                    listbox_selectall('productBranchSelected', true);
                                    <?php
                                    }
                                    ?>
                                    if($("#productBranchSelected").val() == null || $("#ProductPgroupId").val() == null || $("#ProductPgroupId").val() == '' || $("#ProductUomId").val() == null || $("#ProductUomId").val() == ''){
                                        alertSelectRequireField();
                                    } else {
                                        $(this).dialog("close");
                                        var dataPost = $("#ProductQuickAddForm").serialize()+"&"+$('#formBranchProductQuick').serialize();
                                        $.ajax({
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/products/quickAdd",
                                            data: dataPost,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                // Message Alert
                                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                                                    createSysAct('Product', 'Quick Add', 2, result);
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                }else {
                                                    createSysAct('Product', 'Quick Add', 1, '');
                                                    // alert message
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                                }
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
        // Event Key Table List
        checkEventTO();
        changeInputCSSTransferOrder();
        loadAutoCompleteOff();
    }); // End Document Ready
    
    function branchChangeTransferOrder(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        $("#TransferOrderToCode").val("<?php echo date("y"); ?>"+mCode);
    }
    
    // Calculate Form Table List
    function resizeFormTitleTOAdd(){
        var screen = 16;
        var widthList = $("#bodyListTO").width();
        $("#formTitleTO").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#formTitleTO").css('padding','0px');
        $("#formTitleTO").css('margin-top','5px');
        $("#formTitleTO").css('width',widthTitle);
    }
    
    function resizeFornScrollTO(){
        var tabHeight = $(tabTOId).height();
        var formHeader = 0;
        if ($('#headerTO').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#headerTO").height();
        }
        var btnHeader   = $("#btnHideShowHeaderTransferOrder").height();
        var formFooter  = $("#footerTO").height();
        var formSearch  = $("#divSearchTransferOrder").height();
        var tableHeader = $("#formTitleTO").height();
        var spaceRemain = 20;
        var getHeight   = tabHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter + spaceRemain);
        $("#bodyListTO").css('height',getHeight);
        $("#bodyListTO").css('padding','0px');
        $("#bodyListTO").css('width','100%');
        $("#bodyListTO").css('overflow-x','hidden');
        $("#bodyListTO").css('overflow-y','scroll');
        checkOrderDateTO();
    }
    
    function refreshScreenTOAdd(){
        $("#formTitleTO").removeAttr('style');

    }
    
    // Action Search
    
    // Search Product
    function searchProductTO(code){
        var companyId         = $("#TransferOrderCompanyId").val();
        var branchId          = $("#TransferOrderBranchId").val();
        var locationGroupFrom = $("#TransferOrderFromLocationGroupId").val();
        var locationGroupTo   = $("#TransferOrderToLocationGroupId").val();
        $("#searchProductSKU").val("");
        if(locationGroupFrom != '' && locationGroupTo != '' && code != '' && companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/transfer_orders/"; ?>searchProductCode/"+companyId+"/"+branchId+"/"+encodeURIComponent(code)+"/"+locationGroupFrom+"/"+locationGroupTo,
                beforeSend: function(arr, $form, options) {
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    timeSearchAddTO = 0;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    if(result == ""){
                        alertConfirmValidCode();
                    }else{
                        // Condition
                        var records  = result.split("||");
                        var product  = records[0];
                        var location = records[1];
                        var uomList  = records[2];
                        var products        = $.parseJSON(product);
                        var optionLocation  = $.parseJSON(location);
                        var optionUom       = $.parseJSON(uomList);
                        // Add Product To Tabe List
                        addProductToListTO(products, optionLocation, optionUom, 0);
                    }
                }
            });
        }else{
            // Reset Search Action 
            timeSearchAddTO = 0;
            // Alert Confirm
            alertSelectRequireField();
        }
    }
    
    // Search Product List
    function searchProductListTO(){
        var companyId         = $("#TransferOrderCompanyId").val();
        var branchId          = $("#TransferOrderBranchId").val();
        var locationGroupFrom = $("#TransferOrderFromLocationGroupId").val();
        var locationGroupTo   = $("#TransferOrderToLocationGroupId").val();
        if(locationGroupFrom != '' && locationGroupTo != '' && companyId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/transfer_orders/product/"; ?>"+companyId+"/"+branchId+"/"+locationGroupFrom+"/"+locationGroupTo,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeSearchAddTO = 0;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $(this).dialog("close");
                                if($("input[name='chkProductTO']:checked").val()){
                                    if($("input[name='chkProductTO']:checked").val() > 0){
                                        var code = $("input[name='chkProductTO']:checked").val();
                                        searchProductTO(code);
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }else{
            // Reset Search Action 
            timeSearchAddTO = 0;
            // Alert Confirm
            alertSelectRequireField();
        }
    }
    
    // Check Order Date
    function checkOrderDateTO(){
        if($("#TransferOrderOrderDate").val() == ""){
            $("#TransferOrderOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    // Check Validate Before Save
    function checkBfSaveTO(){
        var formName = "#TransferOrderEditForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if(checkExistRecordListTO() == false){
                alertAddProductToListTO();
                return false;
            }else{
                return true;
            }
        }
    }
    // Check Exist Record in Table List
    function checkExistRecordListTO(){
        if($(".recordTODetail").find(".product_id").val() == undefined || $(".recordTODetail").find(".product_id").val() == ""){
            return false;
        }else{
            return true;
        }
    }
    
    // Clear Table List TO
    function clearTableListTO(){
        $("#tblTO").html("");
    }
    
    // Get Index Row
    function sortTableTO(){
        var sort = 1;
        $(".tblTOList tr").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function showProductTotalQtyList(productId, tr){
        var locationFrom = $("#TransferOrderFromLocationGroupId").val();
        if(locationFrom != '' && productId != ''){
            $("#TransferOrderOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var date = $("#TransferOrderOrderDate").val();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/transfer_orders/getProductTotalQty/"; ?>"+productId+"/"+locationFrom+"/<?php echo $this->data['TransferOrder']['id']; ?>",
                data:   "date="+date,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $("#TransferOrderOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                },
                success: function(msg){
                    tr.removeAttr('disabled');
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 1030,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $(this).dialog("close");
                                var access = false;
                                $("input[name='chkProductQtyTO']:checked").each(function(){
                                    var qty = replaceNum($(this).closest("tr").find(".inputQtyTo").val());
                                    if(qty > 0){
                                        // Get Location
                                        var locationFrom = replaceSlash(replaceDoubleQuote(tr.closest("tr").find(".location_from_id").html()));
                                        var locationTo   = replaceSlash(replaceDoubleQuote(tr.closest("tr").find(".location_to_id").html()));
                                        var uom          = replaceSlash(replaceDoubleQuote(tr.closest("tr").find(".qty_uom_id").html()));
                                        var locationJson = '{"LocationFrom":{"option": "'+locationFrom+'"}, "LocationTo":{"option": "'+locationTo+'"}}';
                                        var UomJson      = '{"Uom":{"option": "'+uom+'"}}';
                                        // Set Row
                                        var product         = $(this).val();
                                        var products        = $.parseJSON(product);
                                        var optionLocation  = $.parseJSON(locationJson);
                                        var optionUom       = $.parseJSON(UomJson);
                                        // Add Product To Tabe List
                                        addProductToListTO(products, optionLocation, optionUom, qty);
                                        access = true;
                                    }
                                });
                                if(access == true){
                                    // Remove Obj Selected
                                    tr.closest("tr").remove();
                                }else{
                                    // Uom Empty
                                    tr.closest("tr").find(".location_from_id").find("option[value='']").attr("selected", true);
                                    // Reset Qty & Total Qty
                                    tr.closest("tr").find(".qty").val(0);
                                    tr.closest("tr").find(".total_qty").val(0);
                                    tr.closest("tr").find(".lots_number").val('');
                                    tr.closest("tr").find(".expired_date").val('');
                                    tr.closest("tr").find(".total_qty_label").val('');
                                }
                            }
                        }
                    });
                }
            });
        }else{
            tr.removeAttr('disabled');
        }
    }
    
    function addProductToListTO(products, optionLocation, optionUom, qty){
        // Get Row Index
        rowIndexTO = Math.floor((Math.random() * 100000) + 1);
        
        // Product Information
        var productId           = products.Product.id;
        var productName         = products.Product.name;
        var productSKU          = products.Product.code;
        var productPUC          = products.Product.barcode;
        var smallUomVal         = products.Product.small_val_uom;
        var ExpiredDate         = "0000-00-00";
        var lotsNumber          = "0";
        var totalQty            = products.Product.total_qty;
        var totalQtyLabel       = products.Product.total_qty_label;
        var locationSelected    = products.Product.location_id;
        var tr                  = rowDeailTO.clone(true);
        var expDateLabel        = "";
        var lotsLabel           = "";
        var unitCost            = products.Product.unit_cost;
        
        // Remove Row Attr ID
        tr.removeAttr("style").removeAttr("id");
        // Assign Value to LABLE
        tr.find("td:eq(0)").html('');
        tr.find(".SKULabel").val(productSKU);
        tr.find(".PUCLabel").val(productPUC);
        // Assign Value to INPUT
        tr.find("input[name='product_id[]']").val(productId);
        tr.find(".productName").val(productName);
        tr.find(".total_qty").val(totalQty);
        tr.find(".total_qty_label").val(totalQtyLabel);
        tr.find(".smallUomVal").val(smallUomVal);
        tr.find(".conversion").val(smallUomVal);
        tr.find(".qty").val(qty);
        // Lots and Expiry
        if(products.Product.lots_number != '0' && products.Product.lots_number != ''){
            lotsNumber  = products.Product.lots_number;
            lotsLabel   = products.Product.lots_number;
        }
        if(products.Product.date_expired != '0000-00-00' && products.Product.date_expired != ''){
            ExpiredDate  = products.Product.date_expired;
            var expDate  = ExpiredDate.split("-");
            expDateLabel = ExpiredDate != ""?expDate[2]+"/"+expDate[1]+"/"+expDate[0]:"";
        }
        tr.find(".lotsNoLabel").html(lotsLabel);
        tr.find(".lots_number").val(lotsNumber);
        tr.find(".ExpDateLabel").html(expDateLabel);
        tr.find(".expired_date").val(ExpiredDate);
        // Assign ID
        tr.find(".productName").attr("id", "productName_"+rowIndexTO);
        tr.find(".location_from_id").attr("id", "location_from_id_"+rowIndexTO);
        tr.find(".location_to_id").attr("id", "location_to_id_"+rowIndexTO);
        tr.find(".qty").attr("id", "qty_"+rowIndexTO);
        tr.find(".qty_uom_id").attr("id", "qty_uom_id_"+rowIndexTO);
        tr.find(".unit_cost").attr("id", "unit_cost_"+rowIndexTO).val(unitCost);
        
        // Append to Table List
        $("#tblTO").append(tr);
        
        // Assign Location From / TO / UOM
        showLocationFromTO(locationSelected, optionLocation, tr);
        showLocationToTO(optionLocation, tr);
        showUomTO(optionUom, tr);
        
        // Event Key Table List
        checkEventTO();
        sortNuTableTO();
        // Check In Stock
        if(qty > 0){
            calculateStockTO(tr);
        }
    }
    
    function showLocationFromTO(locationSelected, options, obj){
        var option = options.LocationFrom.option;
        obj.find(".location_from_id").html(option).find("option[value='"+locationSelected+"']").attr("selected", "selected");
    }
    
    function showLocationToTO(options, obj){
        var option = options.LocationTo.option;
        obj.find(".location_to_id").html(option);
    }
    
    function showUomTO(options, obj){
        var option = options.Uom.option;
        obj.find(".qty_uom_id").html(option);
    }
    
    function eventKeyTO(){
        loadAutoCompleteOff();
        $(".location_from_id, .qty_uom_id, .qty, .unit_cost, .total_cost, .btnRemoveTO").unbind("click").unbind("change").unbind("blur").unbind("focus").unbind("keyup").unbind("keypress");
        $(".qty").autoNumeric({mDec: 0, aSep: ','});
        // Action Location From Change
        $(".location_from_id").click(function(){
            var productId = $(this).closest("tr").find(".product_id").val();
            var tr = $(this);
            var value = $(this).val();
            if(value != ""){
                $(this).attr('disabled', 'disabled');
                showProductTotalQtyList(productId, tr);
            }else{
                tr.closest("tr").find(".qty").val(0);
                tr.closest("tr").find(".total_qty").val(0);
                tr.closest("tr").find(".lots_number").val('');
                tr.closest("tr").find(".expired_date").val('');
                tr.closest("tr").find(".total_qty_label").val('');
            }
        });
        
        // Action Input Qty
        $(".qty").keyup(function(){
            var locationFrom = $(this).closest("tr").find(".location_from_id").val();
            if(locationFrom != ''){
                calculateStockTO($(this));
            }else{
                $(this).val(0);
                alertSelectLocationFromTO();
            }
            calculateStockTO($(this));
        });
        
        $(".qty").focus(function(){
            if($(this).val() == '0'){
                $(this).val("");
            }
            calculateStockTO($(this));
        });
        
        $(".qty").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
            calculateStockTO($(this));
        });
        
        // Action Location From Change
        $(".qty_uom_id").change(function(){
            calculateStockTO($(this));
        });
        
        $(".unit_cost").keyup(function(){
            calculateStockTO($(this));
        });
        
        // Action Remove Row
        $(".btnRemoveTO").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_REMOVE_ITEM; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                position:'center',
                modal: true,
                width: '300',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        currentTr.remove();
                        sortTableTO();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function checkEventTO(){
        eventKeyTO();
        $(".recordTODetail").unbind("click");
        $(".recordTODetail").click(function(){
            eventKeyTO();
        });
    }
    
    // Check In Stock
    function calculateStockTO(obj){
        var qty             = replaceNum(obj.closest("tr").find(".qty").val());
        var totalQty        = replaceNum(obj.closest("tr").find(".total_qty").val());
        var smallUomVal     = replaceNum(obj.closest("tr").find(".smallUomVal").val());
        var conUomSelected  = replaceNum(obj.closest("tr").find(".qty_uom_id").find("option:selected").attr("conversion"));
        var conversion      = converDicemalJS(smallUomVal / conUomSelected);
        var qtyTransfer     = converDicemalJS(qty * conversion);
        var unitCost        = replaceNum(obj.closest("tr").find(".unit_cost").val());
        var totalCost       = qty * unitCost;
        if(qtyTransfer > totalQty){
            obj.closest("tr").find(".qty").val(0);
            alertTransferMoreThanStock(obj);
        }
        // Assign Conversion To List
        obj.closest("tr").find(".conversion").val(conversion);
        obj.closest("tr").find(".total_cost").val(totalCost.toFixed(2));
        calulateTotalAmt();
    }
    
    // Disable / Enable Button Save
    function disableEnableSaveTO(con){
        // True = Disable, False = Enable
        if(con == true){
            $(".btnSaveTO").attr('disabled', true);
        }else{
            $(".btnSaveTO").removeAttr('disabled');
        }
    }
    
    
    // Alert Add Product To List
    function alertAddProductToListTO(){
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an transfer first.</p>');
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
    
    // Alert Select Location From First
    function alertSelectLocationFromTO(){
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please Select Location From First!</p>');
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
    
    // Alert Entered Qty More Than Stock
    function alertTransferMoreThanStock(obj){
        var productName = obj.closest("tr").find(".productName").val();
        var totalLabel  = obj.closest("tr").find(".total_qty_label").val();
        $("#dialog").html('<p><?php echo MESSAGE_ALERT_INPUT_MORE_THAN_STOCK; ?> '+totalLabel+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_WARNING; ?> '+productName,
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
                    obj.closest("tr").find(".qty").select().focus();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function sortNuTableTO(){
        var sort = 1;
        $(".recordTODetail").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function backTransferOrder(){
        $("#TransferOrderEditForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oTOTable.fnDraw(false);
        var rightPanel = $(".btnBackTransferOrder").parent().parent().parent().parent();
        var leftPanel  =  rightPanel.parent().find(".leftPanel");
        rightPanel.hide("slide", { direction: "right" }, 500, function(){
            leftPanel.show();
            rightPanel.html("");
        });
    }
    
    function changeInputCSSTransferOrder(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var disabled  = true;
        $(".searchToProduct").hide();
        if($("#TransferOrderCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            disabled  = false;
            $(".searchToProduct").show();
        }   
        // Label
        $("#TransferOrderEditForm").find("label").removeAttr("class");
        $("#TransferOrderEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'TransferOrderCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#TransferOrderEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#TransferOrderEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'TransferOrderCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
    }
    function calulateTotalAmt(){
        var totalAmt = 0;
        $(".total_cost").each(function(){
            var qty       = replaceNum($(this).closest("tr").find(".qty").val());
            var unitCost  = replaceNum($(this).closest("tr").find(".unit_cost").val());
            totalAmt     += converDicemalJS(qty * unitCost);
        });
        $("#txtTotalAmount").text(totalAmt.toFixed(2) + " $");
        $("#totalCostTo").val(totalAmt.toFixed(2));
    }
</script>
<?php echo $this->Form->create('TransferOrder',array('inputDefaults' => array('div'=>false))); ?>
<?php echo $this->Form->hidden('id'); ?>
<input type="hidden" id="totalCostTo" class="float" value="<?php echo number_format($this->data['TransferOrder']['total_cost'], 2) ?>" name="data[TransferOrder][total_cost]" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderTransferOrder">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="headerTO" style="margin-bottom:5px;">
    <legend><?php __(MENU_TRANSFER_ORDER_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 10%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>"><label for="TransferOrderCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td style="width: 23%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[TransferOrder][company_id]" id="TransferOrderCompanyId" class="validate[required]" style="width: 265px;">
                        <?php
                        if(count($companies) != 1){
                        ?>
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        }
                        foreach($companies AS $company){
                        ?>
                        <option <?php if($company['Company']['id'] == $this->data['TransferOrder']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>       
            <td style="width: 10%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>"><label for="TransferOrderBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td style="width: 23%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[TransferOrder][branch_id]" id="TransferOrderBranchId" class="validate[required]" style="width: 85%;">
                        <?php
                        if(count($branches) != 1){
                        ?>
                        <option value="" mcode=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        }
                        foreach($branches AS $branch){
                        ?>
                        <option <?php if($branch['Branch']['id'] == $this->data['TransferOrder']['branch_id']){ ?>selected="selected"<?php } ?> value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['to_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><label for="TransferOrderFromLocationGroupId"><?php echo TABLE_FROM_WAREHOUSE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->input('from_location_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'style'=>'width:265px')); ?>
                </div>
            </td>
            <td><label for="TransferOrderOrderDate"><?php echo TABLE_TO_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->text('order_date', array('value' => dateShort($this->data['TransferOrder']['order_date']), 'class'=>'validate[required]','readonly'=>'readonly','style'=>'width:90%')); ?>
                </div>
            </td>
            <td style="width:7%;"><label for="TransferOrderNote"><?php echo TABLE_MEMO; ?> <span class="red">*</span> :</label></td>
            <td style="vertical-align: top;" rowspan="2">
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->textarea('note', array('style'=>'width:95%; ; height: 60px;', 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="TransferOrderToLocationGroupId"><?php echo TABLE_TO_WAREHOUSE; ?> <span class="red">*</span> :</label></td>
            <td>
                <input type="hidden" id="defFromLoc" />
                <div class="inputContainer" style="width:100%;">
                    <?php echo $this->Form->input('to_location_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'style'=>'width:265px')); ?>
                </div>
            </td>
            <td><label for="TransferOrderToCode"><?php echo TABLE_TO_NUMBER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width:100%;">
                    <?php echo $this->Form->text('to_code', array('class'=>'validate[required]', 'style'=>'width:80%', 'readonly' => true)); ?>
                </div>
            </td>
            <td></td>
        </tr>
    </table>
</fieldset>
<table style="width: 60%;" id="divSearchTransferOrder">
    <tr>
        <td style="width: 410px;">
            <?php
            if($allowAddProduct){
            ?>
            <div class="addnew">
                <input type="text" id="searchProductSKU" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductTransfer" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
            </div>
            <?php
            } else {
            ?>
            <input type="text" id="searchProductSKU" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
            <?php
            }
            ?>
        </td>
        <td><img alt="Search" align="absmiddle" style="cursor: pointer;" class="searchToProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" /></td>
    </tr>
</table>
<table id="formTitleTO" class="table" cellspacing="0" style="padding:0px; width:99%;">
    <tr>
        <th class="first" style="width:4%;"><?php echo TABLE_NO; ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:20%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:10%;"><?php echo TABLE_LOCATION_FROM; ?></th>
        <th style="width:10%;"><?php echo TABLE_LOCATION_TO; ?></th>
        <th style="width:7%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:7%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:8%;"><?php echo TABLE_UNIT_COST; ?></th>
        <th style="width:8%;"><?php echo TABLE_TOTAL_COST; ?></th>
        <th style="width:4%;"></th>
    </tr>
</table>
<div id="bodyListTO">
    <table id="tblTO" class="table" cellspacing="0" style="padding:0px;">
        <tr class="recordTODetail" id="recordTODetail" style="visibility: hidden;">
            <td class="first" style="width:4%;"></td>
            <td style="width:10%;">
                <input type="text" readonly="" style="width: 95%; height: 25px;" class="PUCLabel" />
            </td>
            <td style="width:20%;">
                <input type="hidden" value="0" class="product_id" name="product_id[]" />
                <input type="text"   value=""  class="productName" style="width: 90%; height: 25px;" readonly="readonly" />
            </td>
            <td style="width:10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <input type="hidden" value="" class="lots_number" name="lots_number[]" />
                <span class="lotsNoLabel"></span>
            </td>
            <td style="width:10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <input type="hidden" value="" class="expired_date" name="expired_date[]" />
                <span class="ExpDateLabel"></span>
            </td>
            <td style="width:10%;">
                <select class="location_from_id validate[required]" name="location_from_id[]" style="width: 90%; height: 25px;"></select>
            </td>
            <td style="width:10%;">
                <select class="location_to_id validate[required]" name="location_to_id[]" style="width: 90%; height: 25px;"></select>
            </td>
            <td style="width:7%;">
                <input type="hidden" value="1" class="smallUomVal" />
                <input type="hidden" value="1" class="conversion" name="conversion[]" />
                <input type="hidden" value="0" class="total_qty" />
                <input type="hidden" value=""  class="total_qty_label" />
                <input type="text" value="0" name="qty[]" class="qty validate[required,min[1]]" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:7%;">
                <select class="qty_uom_id" name="qty_uom_id[]" style="width: 90%; height: 25px;"></select>
            </td>
            <td style="width:8%;">
                <input type="text" value="0.00" name="unit_cost[]" class="unit_cost float validate[required]" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:8%;">
                <input type="text" value="0.00" name="total_cost[]" class="total_cost float validate[required]" readonly="readonly" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:4%;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveTO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
        if(!empty($transferOrderDetails)){
            $index = 0;
            foreach($transferOrderDetails AS $transferOrderDetail){
        ?>
        <tr class="recordTODetail">
            <td class="first" style="width:4%;"><?php echo ++$index; ?></td>
            <td style="width:10%;">
                <input type="text" readonly="" style="width: 95%; height: 25px;" class="PUCLabel" value="<?php echo $transferOrderDetail['Product']['barcode']; ?>" />
            </td>
            <td style="width:20%;">
                <input type="hidden" value="<?php echo $transferOrderDetail['TransferOrderDetail']['product_id']; ?>" class="product_id" name="product_id[]" />
                <input type="text"   value="<?php echo str_replace('"', '&quot;', $transferOrderDetail['Product']['name']); ?>" id="productName_<?php echo $index; ?>"  class="productName" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <input type="hidden" value="<?php echo $transferOrderDetail['TransferOrderDetail']['lots_number']; ?>" class="lots_number" name="lots_number[]" />
                <span class="lotsNoLabel"><?php echo $transferOrderDetail['TransferOrderDetail']['lots_number']; ?></span>
            </td>
            <td style="width:10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <?php
                if($transferOrderDetail['TransferOrderDetail']['expired_date'] != "0000-00-00" && $transferOrderDetail['TransferOrderDetail']['expired_date'] != ""){
                    $expDate    = $transferOrderDetail['TransferOrderDetail']['expired_date'];
                    $expDateLbl = dateShort($transferOrderDetail['TransferOrderDetail']['expired_date']);
                }else{
                    $expDate    = "0000-00-00";
                    $expDateLbl = "";
                }
                ?>
                <input type="hidden" value="<?php echo $expDate; ?>" class="expired_date" name="expired_date[]" />
                <span class="ExpDateLabel"><?php echo $expDateLbl; ?></span>
            </td>
            <td style="width:10%;">
                <?php
                $optionFroms = '<option value="">'.INPUT_SELECT.'</option>';
                $sqlLocationFrom = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$this->data['TransferOrder']['from_location_group_id']} ORDER BY name");
                while($rowLoc = mysql_fetch_array($sqlLocationFrom)){
                    if($rowLoc['id'] == $transferOrderDetail['TransferOrderDetail']['location_from_id']){
                        $seleced = 'selected="selected"';
                    }else{
                        $seleced = "";
                    }
                    $optionFroms .= '<option value="'.$rowLoc['id'].'" '.$seleced.'>'.$rowLoc['name'].'</option>';
                }
                ?>
                <select class="location_from_id validate[required]" name="location_from_id[]" id="location_from_id_<?php echo $index; ?>" style="width: 90%; height: 25px;"><?php echo $optionFroms; ?></select>
            </td>
            <td style="width:10%;">
                <?php
                $optionTos = '<option value="">'.INPUT_SELECT.'</option>';
                $sqlLocationTo = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$this->data['TransferOrder']['to_location_group_id']} ORDER BY name");
                while($rowLoc = mysql_fetch_array($sqlLocationTo)){
                    if($rowLoc['id'] == $transferOrderDetail['TransferOrderDetail']['location_to_id']){
                        $seleced = 'selected="selected"';
                    }else{
                        $seleced = "";
                    }
                    $optionTos .= '<option value="'.$rowLoc['id'].'" '.$seleced.'>'.$rowLoc['name'].'</option>';
                }
                ?>
                <select class="location_to_id validate[required]" name="location_to_id[]" id="location_to_id_<?php echo $index; ?>" style="width: 90%; height: 25px;"><?php echo $optionTos; ?></select>
            </td>
            <td style="width:7%;">
                <?php
                $smallUomVal  = 1;
                $smallUomLbl  = "";
                $sqlUSm = mysql_query("SELECT value, (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) AS name FROM uom_conversions WHERE from_uom_id = ".$transferOrderDetail['Product']['price_uom_id']." AND is_small_uom = 1 ORDER BY id DESC LIMIT 1");
                if(@mysql_num_rows($sqlUSm)){
                    $rowUSm = mysql_fetch_array($sqlUSm);
                    $smallUomVal = $rowUSm['value'];
                    $smallUomLbl = $rowUSm['name'];
                }
                $sqlInv = mysql_query("SELECT SUM((inv.total_pb + total_to_in + total_cm + total_cycle) - (inv.total_so + inv.total_pos + inv.total_pbc + inv.total_to_out + inv.total_order)) AS total_qty, u.abbr AS abbr FROM {$transferOrderDetail['TransferOrderDetail']['location_from_id']}_inventory_total_details AS inv INNER JOIN products AS p ON p.id = inv.product_id INNER JOIN uoms AS u ON u.id = p.price_uom_id WHERE inv.product_id = {$transferOrderDetail['Product']['id']} AND inv.location_id = {$transferOrderDetail['TransferOrderDetail']['location_from_id']} AND inv.lots_number = '{$transferOrderDetail['TransferOrderDetail']['lots_number']}' AND inv.expired_date = '{$transferOrderDetail['TransferOrderDetail']['expired_date']}' AND inv.date <= '{$this->data['TransferOrder']['order_date']}' GROUP BY inv.product_id, inv.lots_number, inv.expired_date ORDER BY p.code");
                $row = mysql_fetch_array($sqlInv);
                // Get Order TO Self
                $sqlOrder = mysql_query("SELECT SUM(qty) AS qty FROM stock_orders WHERE transfer_order_id = ".$this->data['TransferOrder']['id']." AND product_id = ".$transferOrderDetail['Product']['id']." AND location_group_id = {$this->data['TransferOrder']['from_location_group_id']} AND location_id = {$transferOrderDetail['TransferOrderDetail']['location_from_id']} AND lots_number = '{$transferOrderDetail['TransferOrderDetail']['lots_number']}' AND expired_date = '{$transferOrderDetail['TransferOrderDetail']['expired_date']}' GROUP BY product_id");
                $rowOrder = mysql_fetch_array($sqlOrder);
                $totalOrder = $rowOrder[0];
                $totalQty   = $row['total_qty'] + $totalOrder;
                $uomMainLabel = $row['abbr'];
                ?>
                <input type="hidden" value="<?php echo $smallUomVal; ?>" class="smallUomVal" />
                <input type="hidden" value="<?php echo $transferOrderDetail['TransferOrderDetail']['conversion']; ?>" class="conversion" name="conversion[]" />
                <input type="hidden" value="<?php echo $totalQty; ?>" class="total_qty" />
                <input type="hidden" value="<?php echo showTotalQty($totalQty, $uomMainLabel, $smallUomVal, $smallUomLbl); ?>"  class="total_qty_label" />
                <input type="text"   value="<?php echo $transferOrderDetail['TransferOrderDetail']['qty']; ?>" name="qty[]" id="qty_<?php echo $index; ?>" class="qty validate[required,min[1]]" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:7%;">
                <?php
                    $options = "";
                    $uomId = $transferOrderDetail['Product']['price_uom_id'];
                    $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomId."
                                          UNION
                                          SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId.")
                                          ORDER BY conversion ASC");
                    $i = 1;
                    $length = mysql_num_rows($query);
                    while($data=mysql_fetch_array($query)){
                        $selected = "";
                        $isMain   = "other";
                        $isSmall  = 2;
                        if($data['id'] == $transferOrderDetail['TransferOrderDetail']['qty_uom_id']){   
                            $selected = ' selected="selected" ';
                        }
                        if($data['id'] == $uomId){
                            $isMain = "first";
                        }
                        // Check Is Small UOM
                        if($length == $i){
                            $isSmall = 1;
                        }
                        $options .= trim('<option '.$selected.' data-sm="'.$isSmall.'" data-item="'.$isMain.'" conversion="'.$data['conversion'].'" value="'.$data['id'].'">'.$data['name'].'</option>');
                        $i++;
                    }
                ?>
                <select class="qty_uom_id" name="qty_uom_id[]" id="qty_uom_id_<?php echo $index; ?>" style="width: 90%; height: 25px;"><?php echo $options; ?></select>
            </td>
            <td style="width:8%;">
                <input type="text" value="<?php echo $transferOrderDetail['TransferOrderDetail']['unit_cost']; ?>" name="unit_cost[]" id="unit_cost_<?php echo $index; ?>" class="unit_cost float validate[required]" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:8%;">
                <input type="text" value="<?php echo $transferOrderDetail['TransferOrderDetail']['total_cost']; ?>" name="total_cost[]" id="total_cost_<?php echo $index; ?>" class="total_cost float validate[required]" readonly="readonly" style="width: 90%; height: 25px;" />
            </td>
            <td style="width:4%;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveTO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
</div>
<div style="width:100%; padding: 0px;" id="footerTO">
    <div class="buttons">
        <a href="#" class="positive btnBackTransferOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveTO">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveTO"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <span style="float: right; font-size: 16px; font-weight: bold; padding-right: 8%;" id="txtTotalAmount"><?php echo number_format($this->data['TransferOrder']['total_cost'], 2) ?> $</span>
    <span style="float: right; font-size: 16px; font-weight: bold; padding-right: 2%;">Total Amount: </span>
    <div style="clear: both;"></div>
    <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>