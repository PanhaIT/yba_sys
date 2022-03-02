<?php
$priceDecimal = 2;
$allowSaleRep = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (40, 46) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    } else if($rowSetting['id'] == 46){
        if($rowSetting['is_checked'] == 1){
            $allowSaleRep = true;
        }
    }
}
// Authentication
$this->element('check_access');
$allowAddCustomer = checkAccess($user['User']['id'], 'customers', 'quickAdd');

// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    var timeSearchCM = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $('#dialog').dialog('destroy');
        clearOrderDetailAddCM();
        <?php
        if($allowAddCustomer){
        ?>
        $("#addNewCustomerCM").click(function(){
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
                                                createSysAct('Sales Return', 'Quick Add Customer', 2, result);
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
                                                createSysAct('Sales Return', 'Quick Add Customer', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Customer
                                                    $("#CreditMemoCustomerId").val(result.id);
                                                    $("#CreditMemoCustomerName").val(result.name);
                                                    $("#CreditMemoCustomerName").attr("readonly","readonly");
                                                    $(".searchCustomerAddCM").hide();
                                                    $(".deleteCreditMemoCustomer").show();
                                                    if(result.price != ''){
                                                        // Check Price Type Customer
                                                        customerPriceTypeCM(result.price, 0);
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
        // Hide Branch
        $("#CreditMemoBranchId").filterOptions('com', '0', '');
        $("#CreditMemoLocationGroupId").chosen({width: 200});
        $("#CreditMemoAddForm").validationEngine();
        $(".btnSaveCreditMemo").click(function(){
            if(checkBfSaveCM() == true){
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
                            // Set Preview
                            $("#CreditMemoIsPreview").val('0');
                            // Action Click Save
                            $("#CreditMemoAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }else{
                return false;
            }
        });
        
        $(".btnSavePreviewCreditMemo").click(function(){
            if(checkBfSaveCM() == true){
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
                            $("#CreditMemoIsPreview").val(1);
                            $("#CreditMemoAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });

        $("#CreditMemoAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#CreditMemoIsPreview").val() == '1'){
                    $(".txtSavePreviewCM").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSaveCM").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#CreditMemoOrderDate, .expired_date, .startCM").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .interger").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $(".btnSaveCreditMemo, .btnSavePreviewCreditMemo").attr("disabled", true);
                if($("#CreditMemoPreviewId").val() != ''){
                    var mCode  = $("#CreditMemoBranchId").find("option:selected").attr("mcode");
                    $("#CreditMemoCmCode").val("<?php echo date("y"); ?>"+mCode);
                }
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Credit Memo', 'Add', 2, result.responseText);
                backCreditMemo();
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
                    errorSaveCM();
                }else{
                    createSysAct('Credit Memo', 'Add', 1, '');
                    if($("#CreditMemoIsPreview").val() == '1'){
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
                        $("#CreditMemoPreviewId").val(result.id);
                        $("#CreditMemoCmCode").val(result.code);
                        $(".txtSavePreviewCM").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".btnSaveCreditMemo, .btnSavePreviewCreditMemo").attr("disabled", false);
                        $("#CreditMemoOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        saveContiuneCreditMemo();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceCM" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_CREDIT_MEMO; ?></span></button></div> ');
                        $(".printInvoiceCM").click(function(){
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
                                $(".ui-dialog-titlebar-close").show();
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
        
        $("#CreditMemoInvoiceCode").autocomplete("<?php echo $this->base . "/credit_memos/searchSalesInvoice"; ?>", {
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
            var invoiceId  = value.toString().split(".*")[0];
            var invoiceCode = value.toString().split(".*")[1];
            var customerId = value.toString().split(".*")[3];
            var customerCode = value.toString().split(".*")[4];
            var customerName = value.toString().split(".*")[5];
            // Invoice
            $("#CreditMemoCreditMemoId").val(invoiceId);
            $("#CreditMemoInvoiceCode").val(invoiceCode);
            $("#CreditMemoInvoiceCode").attr("readonly","readonly");
            $(".searchInvoiceCodeCM").hide();
            $(".deleteInvoiceCodeCM").show();
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromSales/"+invoiceId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    if(msg.error == 0){
                        var tr = msg.result;
                        // Empty Row List
                        $("#tblCM").html('');
                        // Insert Row List
                        $("#tblCM").append(tr);
                        // Check Serivec Monthly Start Date
                        checkDateServiceCM();
                        // Event Key Table List
                        checkEventCM();
                        // Calculate Total Amount
                        getTotalAmountCM();
                    }
                }
            });
            // Customer
            $("#CreditMemoCustomerId").val(customerId);
            $("#CreditMemoCustomerCode").val(customerCode);
            $("#CreditMemoCustomerName").val(customerName);
            $("#CreditMemoCustomerName").attr("readonly","readonly");
            $(".searchCustomerAddCM").hide();
            $(".deleteCreditMemoCustomer").show();
        });
        
        $(".searchInvoiceCodeCM").click(function(){
            searchCreditMemoCM();
        });

        $(".deleteInvoiceCodeCM").click(function(){            
            $("#CreditMemoSalesOrderId").val("");
            $("#CreditMemoCreditMemoId").val("");
            $("#CreditMemoInvoiceCode").val("");
            $("#CreditMemoInvoiceCode").removeAttr("readonly");
            $(".searchInvoiceCodeCM").show();
            $(".deleteInvoiceCodeCM").hide();
        });
        
        $("#CreditMemoCustomerName").focus(function(){
            checkOrderDateCMAdd();
        });
        
        $(".searchCustomerAddCM").click(function(){
            if(checkOrderDateCMAdd() == true && $("#CreditMemoCompanyId").val() != ''){
                searchAllCustomerCM();
            }
        });

        $("#CreditMemoCustomerName").autocomplete("<?php echo $this->base . "/customers/searchCustomer"; ?>", {
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
            $("#CreditMemoProduct").attr("disabled", false);
            $("#CreditMemoCustomerId").val(value.toString().split(".*")[0]);
            $("#CreditMemoCustomerName").val(value.toString().split(".*")[2]+" - "+value.toString().split(".*")[1]);
            $("#CreditMemoCustomerName").attr("readonly","readonly");
            $(".searchCustomerAddCM").hide();
            $(".deleteCreditMemoCustomer").show();
            if(value.toString().split(".*")[9] != ''){
                // Check Price Type Customer
                customerPriceTypeCM(value.toString().split(".*")[9], 0);
            }
        });

        $(".deleteCreditMemoCustomer").click(function(){
            if($(".tblCMList").find(".product_id").val() == undefined && $("#CreditMemoInvoiceCode").val() == ""){
                removeCustomerCM();
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_REMOVE_CUSTOMER_ON_CM; ?>";
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
                            removeCustomerCM();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        if($.cookie('CreditMemoLocationGroupId')!=null){
            $("#CreditMemoLocationGroupId").val($.cookie('CreditMemoLocationGroupId'));
            checkLocationByGroupCM();
        }
        
        $("#CreditMemoLocationGroupId").change(function(){
            var obj = $(this);
            $.cookie('CreditMemoLocationGroupId', obj.val(), { expires: 7, path: "/" });
            checkLocationByGroupCM();
        });

        if($.cookie('locationId')!=null){
            $("#CreditMemoLocationId").val($.cookie('locationId'));
        }
        $("#CreditMemoLocationId").change(function(){
            var obj = $(this);
            $.cookie('locationId', obj.val(), { expires: 7, path: "/" });
        });

        $('#CreditMemoCustomerName').keypress(function(e){
            if(e.keyCode == 13){
                return false;
            }
        });
        
        $('#CreditMemoOrderDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = "minDate", instance = $( this ).data( "datepicker" );
                var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );
                $(".startCM").not( this ).datepicker( "option", option, date );
            }
        }).unbind("blur");
        
        $("#CreditMemoOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#CreditMemoOrderDate").datepicker("option", "maxDate", 0);
        
        $(".btnBackCreditMemo").click(function(event){
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
                        backCreditMemo();
                    }
                }
            });
        });
        
        $("#CreditMemoTaxRange").change(function(){
            changeTaxCM();
        });
        
        // Action Branch
        $("#CreditMemoBranchId").change(function(){
            var obj = $(this);
            if($(".tblCMList").find(".product_id").val() == undefined){
                $.cookie('branchIdCM', obj.val(), { expires: 7, path: "/" });
                branchChangeCreditMemo(obj);
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
                            $.cookie('branchIdCM', obj.val(), { expires: 7, path: "/" });
                            branchChangeCreditMemo(obj);
                            $("#tblCMList").html('');
                            // Total Discount
                            $("#btnRemoveCMTotalDiscount").click();
                            getTotalAmountCM();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#CreditMemoBranchId").val($.cookie("branchIdCM"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Company Action
        if($.cookie('companyIdCM')!=null || $("#CreditMemoCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdCM') != null){
                $("#CreditMemoCompanyId").val($.cookie('companyIdCM'));
            }
            var vatCal = $("#CreditMemoCompanyId").find("option:selected").attr("vat-opt");
            $("#CreditMemoVatCalculate").val(vatCal);
            $("#CreditMemoBranchId").filterOptions('com', $("#CreditMemoCompanyId").val(), '');
            $("#CreditMemoBranchId").change();
        }
        
        // Sales Rep
        $("#CreditMemoSalesRepName").autocomplete("<?php echo $this->base . "/employees/searchEmployee"; ?>", {
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
        
        $(".searchCMRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            searchEmployeeCM(objId, objName, btnSearch, btnDelete);
        });
        
        $(".deleteCMRep").click(function(){
            var objId   = $(this).parent().find("input[type='hidden']").attr("id");
            var objName = $(this).parent().find("input[type='text']").attr("id");
            var btnSearch = $(this).parent().find("img[search='1']").attr("class");
            var btnDelete = $(this).parent().find("img[search='0']").attr("class");
            removeEmployeeCM(objId, objName, btnSearch, btnDelete);
        });
        
        // Reset Form
        resetFormCM();
        // VAT Filter
        checkVatCompanyCM();
        // Location Filter
        checkLocationByGroupCM();
        // Load Detail
        loadOrderDetailCM();
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
    }); // End Document Ready
    
    function removeEmployeeCM(objId, objName, btnSearch, btnDelete){
        $("#"+objId).val('');
        $("#"+objName).val('');
        $("#"+objName).removeAttr('readonly','readonly');
        $("."+btnSearch).show();
        $("."+btnDelete).hide();
    }
    
    function searchEmployeeCM(objId, objName, btnSearch, btnDelete){
        var companyId = $("#CreditMemoCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                searchCodeCM == 1;
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
    
    function checkDateServiceCM(){
        $(".tblCMList").each(function(){
            if($(this).find("input[name='service_id[]']").val() != '' && $(this).find(".typeCM").val() == 2 && $(this).find(".isReferenceCM").val() == 1){
                var startId = $(this).find(".startCM").attr('id');
                $('#'+startId).datepicker({
                    dateFormat:'dd/mm/yy',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function() {
                        $("#CreditMemoAddForm").validationEngine("hideAll");
                    }
                }).unbind("blur");
                var minDate = $("#CreditMemoOrderDate").val();
                $('#'+startId).datepicker("option", "minDate", minDate);
            }
        });
    }
    
    function removeCustomerCM(){
        $("#CreditMemoCustomerId").val("");
        $("#CreditMemoCustomerCode").val("");
        $("#CreditMemoCustomerName").val("");
        $("#CreditMemoCustomerName").removeAttr("readonly");
        $(".searchCustomerAddCM").show();
        $(".deleteCreditMemoCustomer").hide();
        $("#typeOfPriceCM").filterOptions("comp", $("#CreditMemoCompanyId").val(), "0");
    }
    
    function branchChangeCreditMemo(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#CreditMemoCmCode").val("<?php echo date("y"); ?>"+mCode);
        $("#CreditMemoCurrencyCenterId").val(currency);
        $(".lblSymbolCM").html(currencySymbol);
    }
    
    function getTotalDiscountCM(){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/credit_memos/invoiceDiscount"; ?>",
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
                            $("#CreditMemoDiscount").val(totalDisAmt);
                            $("#CreditMemoDiscountPercent").val(totalDisPercent);
                            getTotalAmountCM();
                            if(totalDisPercent > 0){
                                $("#CMLabelDisPercent").html('('+totalDisPercent+'%)');
                            } else {
                                $("#CMLabelDisPercent").html('');
                            }
                            if(totalDisAmt > 0 || totalDisPercent > 0){
                                $("#btnRemoveCMTotalDiscount").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function changeLblVatCalCM(){
        var vatCal = $("#CreditMemoVatCalculate").val();
        $("#lblCreditMemoVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblCreditMemoVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblCreditMemoVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedCM(){
        var vatPercent = replaceNum($("#CreditMemoVatSettingId").find("option:selected").attr("rate"));
        var vatAccId   = replaceNum($("#CreditMemoVatSettingId").find("option:selected").attr("acc"));
        $("#CreditMemoVatPercent").val((vatPercent).toFixed(<?php echo $priceDecimal; ?>));
        $("#CreditMemoVatChartAccountId").val(vatAccId);
    }
    
    function checkVatCompanyCM(){
        // VAT Filter
        $("#CreditMemoVatSettingId").filterOptions('com-id', $("#CreditMemoCompanyId").val(), '');
    }
    
    function backCreditMemo(){
        $("#CreditMemoAddForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oTableCreditMemo.fnDraw(false);
        var rightPanel = $(".btnBackCreditMemo").parent().parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function saveContiuneCreditMemo(){
        $("#CreditMemoAddForm").validationEngine("hideAll");
        var rightPanel = $("#CreditMemoAddForm").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
    }
    
    function checkCompanyCM(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#CreditMemoCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormCM(){
        // Customer
        $(".deleteCreditMemoCustomer").click();
        // Invoice
        $(".deleteInvoiceCodeCM").click();
    }
    
    function checkOrderDateCMAdd(){
        if($("#CreditMemoOrderDate").val() == ''){
            $("#CreditMemoOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    function checkLocationByGroupCM(){
        var locationGroup = $("#CreditMemoLocationGroupId").val();
        $("#CreditMemoLocationId").filterOptions('location-group', locationGroup, '');
    }
    
    
    function changeTaxCM(){
        var taxRange           = parseFloat(replaceNum($("#CreditMemoTaxRange").find('option:selected').val()));
        var tax_id             = parseFloat(replaceNum($("#CreditMemoTaxRange").find('option:selected').attr('data-id')));
        var tax_chart_acc_id   = parseFloat(replaceNum($("#CreditMemoTaxRange").find('option:selected').attr('chart-account-id')));
        $('#lblTaxRange').html(taxRange);
        $('#CreditMemoTotalTax').val((taxRange).toFixed(3));
        $('#tax_id').val(tax_id);
        $('#tax_chart_account_id').val(tax_chart_acc_id);
        calcCM();
    }
    
    function calcCM(){
        var totalSubAmount = replaceNum($("#CreditMemoTotalTax").val());
        var totalDis    = replaceNum($("#CreditMemoTotalTax").val());
        var totalMarkup = replaceNum($("#CreditMemoTotalTax").val());
        var totalTax    = replaceNum($("#CreditMemoTotalTax").val());
        var totalAmount = totalSubAmount + totalMarkup + totalTax - totalDis;
        $("#CreditMemoTotalAmount").val((totalAmount).toFixed(3));
    }

    function checkCustomerAddCM(field, rules, i, options){
        if($("#CreditMemoCustomerId").val() == "" || $("#CreditMemoCustomerName").val() == ""){
            return "* Invalid Customer";
        }
    }
    
    function searchAllCustomerCM(){
        var companyId = $("#CreditMemoCompanyId").val();
        if(companyId != ''){
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
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkCMCustomer']:checked").val()){
                                    $("#CreditMemoProduct").attr("disabled", false);
                                    $("#CreditMemoCustomerId").val($("input[name='chkCMCustomer']:checked").val());
                                    $("#CreditMemoCustomerName").val($("input[name='chkCMCustomer']:checked").attr("code")+" - "+$("input[name='chkCMCustomer']:checked").attr("rel"));
                                    $("#CreditMemoCustomerName").attr('readonly','readonly');
                                    $(".searchCustomerAddCM").hide();
                                    $(".deleteCreditMemoCustomer").show();
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkCMCustomer']:checked").attr("ptype");
                                    if(priceTypeList != ''){
                                        customerPriceTypeCM(priceTypeList, 0);
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
    
    function searchCreditMemoCM(){
        var customerId = $("#CreditMemoCustomerId").val();
        var companyId  = $("#CreditMemoCompanyId").val();
        var branchId   = $("#CreditMemoBranchId").val();
        if(companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/salesOrder/"+companyId+"/"+branchId+"/"+customerId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_SALES_ORDER_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 1000,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkCMSalesOrder']:checked").val()){
                                    $("#CreditMemoCreditMemoId").val($("input[name='chkCMSalesOrder']:checked").val());
                                    $("#CreditMemoInvoiceCode").val($("input[name='chkCMSalesOrder']:checked").attr("code"));
                                    $("#CreditMemoInvoiceCode").attr("readonly","readonly");
                                    $(".searchInvoiceCodeCM").hide();
                                    $(".deleteInvoiceCodeCM").show();
                                    // Customer
                                    $("#CreditMemoCustomerId").val($("input[name='chkCMSalesOrder']:checked").attr("cus-id"));
                                    $("#CreditMemoCustomerCode").val($("input[name='chkCMSalesOrder']:checked").attr("cus-num"));
                                    $("#CreditMemoCustomerName").val($("input[name='chkCMSalesOrder']:checked").attr("cus-name"));
                                    $("#CreditMemoCustomerName").attr("readonly","readonly");
                                    $(".searchCustomerAddCM").hide();
                                    $(".deleteCreditMemoCustomer").show();
                                    // Get Product From Sales Invoice
                                    var salesId = $("input[name='chkCMSalesOrder']:checked").val();
                                    // Set Discount
                                    var discount   = $("input[name='chkCMSalesOrder']:checked").attr("dis");
                                    var disPercent = $("input[name='chkCMSalesOrder']:checked").attr("disp");
                                    $("#CreditMemoDiscount").val(discount);
                                    $("#CreditMemoDiscountPercent").val(disPercent);
                                    if(disPercent > 0){
                                        $("#CMLabelDisPercent").html('('+disPercent+'%)');
                                    } else {
                                        $("#CMLabelDisPercent").html('');
                                    }
                                    if(discount > 0 || disPercent > 0){
                                        $("#btnRemoveCMTotalDiscount").show();
                                    }
                                    // Check Price Type
                                    var priceTypeList = $("input[name='chkCMSalesOrder']:checked").attr("ptype");
                                    var priceTypeSelected = $("input[name='chkCMSalesOrder']:checked").attr("ptype-id");
                                    customerPriceTypeCM(priceTypeList, priceTypeSelected);
                                    // VAT 
                                    var vatSettingId = $("input[name='chkCMSalesOrder']:checked").attr("vid");
                                    var vatCalculate = $("input[name='chkCMSalesOrder']:checked").attr("vcal");
                                    var varPercent   = $("input[name='chkCMSalesOrder']:checked").attr("vper");
                                    var varAccId     = $("input[name='chkCMSalesOrder']:checked").attr("vacid");
                                    $("#CreditMemoVatCalculate").val(vatCalculate);
                                    $("#CreditMemoVatSettingId").find("option[value='"+vatSettingId+"']").attr("selected", true);
                                    $("#CreditMemoVatPercent").val(varPercent);
                                    $("#CreditMemoVatChartAccountId").val(varAccId);
                                    $("#CreditMemoSalesOrderId").val(salesId);
                                    changeLblVatCalCM();
                                    $.ajax({
                                        dataType: "json",
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductFromSales/"+salesId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.error == 0){
                                                var tr = msg.result;
                                                // Empty Row List
                                                $("#tblCM").html('');
                                                // Insert Row List
                                                $("#tblCM").append(tr);
                                                // Check Serivec Monthly Start Date
                                                checkDateServiceCM();
                                                // Event Key Table List
                                                checkEventCM();
                                                // Sort
                                                sortNuTableAddCM();
                                                // Calculate Total Amount
                                                getTotalAmountCM();
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

    function loadOrderDetailCM(){
        if($("#CreditMemoOrderDate").val() != ''){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/orderDetails/",
                beforeSend: function(){
                    $(".orderDetailCreditMemo").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $("#tblCM").html('');
                    $("#CreditMemoTotalAmount").val('0.00');
                    $("#CreditMemoDiscount").val('0.00');
                    $("#CreditMemoDiscountPercent").val('0');
                    $("#CreditMemoMarkUp").val('0.00');
                    $("#CreditMemoSubTotalAmount").val('0.00');
                    $("#CreditMemoStatus").attr("disabled", true);
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".orderDetailCreditMemo").html(msg);
                    $(".footerFormCreditMemo").show();
                    // Action Total Discount Amount
                    $("#CreditMemoDiscount").click(function(){
                        getTotalDiscountCM();
                    });


                    $("#btnRemoveCMTotalDiscount").click(function(){
                        $("#CreditMemoDiscount").val(0);
                        $("#CreditMemoDiscountPercent").val(0);
                        $(this).hide();
                        $("#CMLabelDisPercent").html('');
                        getTotalAmountCM();
                    });
                    // Action VAT Status
                    $("#CreditMemoVatSettingId").change(function(){
                        checkVatSelectedCM();
                        getTotalAmountCM();
                    });
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        }else{
            clearOrderDetailAddCM();
        }
    }

    function clearOrderDetailAddCM(){
        $(".orderDetailCreditMemo").html("");
        $(".footerFormCreditMemo").hide();
        $(".deleteCreditMemoCustomer").hide();
    }

    function checkBfSaveCM(){
        $("#CreditMemoCustomerName").removeClass("validate[required]");
        $("#CreditMemoCustomerName").addClass("validate[required,funcCall[checkCustomerAddCM]]");
        var formName = "#CreditMemoAddForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){
            $("#CreditMemoCustomerName").removeClass("validate[required,funcCall[checkCustomerAddCM]]");
            $("#CreditMemoCustomerName").addClass("validate[required]");
            return false;
        }else{
            if($(".tblCMList").find(".product").val() == undefined){
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
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
                $("#CreditMemoCustomerName").removeClass("validate[required,funcCall[checkCustomerAddCM]]");
                $("#CreditMemoCustomerName").addClass("validate[required]");
                return false;
            }else{
                return true;
            }
        }
    }
    
    function errorSaveCM(){
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
                $(".ui-dialog-titlebar-close").show();
            },
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                var rightPanel=$("#CreditMemoAddForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableCreditMemo.fnDraw(false);
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function changeInputCSSCM(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        // Button Search
        $(".searchCustomerAddCM").hide();
        $(".searchInvoiceCodeCM").hide();
        // Div for Search Product, Service, Misc
        $("#divSearchCreditMemo").css("visibility", "hidden");
        if($("#CreditMemoCompanyId").val() != ''){
            var currencySymbol = $("#CreditMemoCompanyId").find("option:selected").attr("symbol");
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            // Button Search
            if($("#CreditMemoCustomerName").val() == ''){
                $(".searchCustomerAddCM").show();
            }
            if($("#CreditMemoInvoiceCode").val() == ''){
                $(".searchInvoiceCodeCM").show();
            }
            // Div for Search Product, Service, Misc
            $("#divSearchCreditMemo").css("visibility", "visible");
            $(".lblSymbolCM").html(currencySymbol);
        } else {
            $(".lblSymbolCM").html('');
        } 
        // Label
        $("#CreditMemoAddForm").find("label").removeAttr("class");
        $("#CreditMemoAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'CreditMemoCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#CreditMemoAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#CreditMemoAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'CreditMemoCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolCM").removeClass(cssRemove);
        $(".lblSymbolCM").addClass(cssStyle);
        $(".lblSymbolCMPercent").removeClass(cssRemove);
        $(".lblSymbolCMPercent").addClass(cssStyle);
        // Input Readonly
        $("#CreditMemoCustomerName").attr("readonly", readonly);
        $("#CreditMemoInvoiceCode").attr("readonly", readonly);
        $("#CreditMemoNote").attr("readonly", readonly);
        $("#SearchProductPucCM").attr("readonly", readonly);
        $("#SearchProductSkuCM").attr("readonly", readonly);
        // Check Price Type With Company
        checkPriceTypeCM();
        // Put label VAT Calculate
        changeLblVatCalCM();
        // Check VAT Default
        getDefaultVatCM();
    }
    
    function checkPriceTypeCM(){
        // Price Type Filter
        $("#typeOfPriceCM").filterOptions('comp', $("#CreditMemoCompanyId").val(), '');
        if($("#CreditMemoCompanyId").val() == ''){
            $("#typeOfPriceCM").prepend('<option value="" comp=""><?php echo INPUT_SELECT;; ?></option>');
            $("#typeOfPriceCM option[value='']").attr("selected", true);
        } else {
            $("#typeOfPriceCM option[value='']").remove();
        }
    }
    
    function customerPriceTypeCM(priceTypeList, priceTypeSelected){
        var priceTypeShow = '';
        var priceType = "";
        if(priceTypeList != ''){
            var selected  = 0;
            priceType = priceTypeList.toString().split(",");
            $("#typeOfPriceCM option").each(function(){
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
        $("#typeOfPriceCM option").removeAttr("selected");
        $("#typeOfPriceCM option[value='"+priceTypeShow+"']").attr("selected", true);
        $.cookie("typePriceCM", $("#typeOfPriceCM").val(), {expires : 7, path : '/'});
        changePriceTypeCM();
    }
    
    function getDefaultVatCM(){
        var vatDefault = $("#CreditMemoCompanyId option:selected").attr("vat-d");
        $("#CreditMemoVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedCM();
    }
</script>
<?php echo $this->Form->create('CreditMemo', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<input type="hidden" value="" name="data[CreditMemo][vat_calculate]" id="CreditMemoVatCalculate" />
<input type="hidden" value="" name="data[CreditMemo][currency_center_id]" id="CreditMemoCurrencyCenterId" />
<input type="hidden" value="" name="data[CreditMemo][preview_id]" id="CreditMemoPreviewId" />
<input type="hidden" value="0" id="CreditMemoIsPreview" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderCreditMemo">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<fieldset id="SOTopAddCM">
    <legend><?php __(MENU_CREDIT_MEMO_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 50%">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 34%"><label for="CreditMemoOrderDate"><?php echo TABLE_DATE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="CreditMemoCmCode"><?php echo CREDIT_MEMO_CODE; ?> <span class="red">*</span></label></td>
                        <td style="width: 33%"><label for="CreditMemoInvoiceCode"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?> </label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('order_date', array('value' => date("d/m/Y"), 'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:70%')); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('cm_code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => true)); ?>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->text('invoice_code', array('style' => 'width:70%')); ?>
                                <?php echo $this->Form->hidden('sales_order_id'); ?>
                                <img alt="<?php echo TABLE_SHOW_INVOICE_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchInvoiceCodeCM" onmouseover="Tip('<?php echo TABLE_SHOW_INVOICE_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" align="absmiddle" style="cursor: pointer; display: none;" class="deleteInvoiceCodeCM" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 34%;">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 50%"><?php if(count($companies) > 1){ ?><label for="CreditMemoCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span></label><?php } ?></td>
                        <td style="width: 50%"><?php if(count($locationGroups) > 1){ ?><label for="CreditMemoLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[CreditMemo][company_id]" id="CreditMemoCompanyId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($companies) != 1){
                                    ?>
                                    <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($companies AS $company){
                                        $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 40 GROUP BY vat_modules.vat_setting_id LIMIT 1");
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
                    </tr>
                </table>
            </td>
            <td rowspan="2">
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width: 50%"><label for="CreditMemoNote"><?php echo TABLE_MEMO; ?> <span class="red">*</span></label></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->input('note', array('label' => false, 'style' => 'width:90%; height: 60px;', 'class' => 'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> 
        <tr>
            <td>
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td colspan="2"><label for="CreditMemoCustomerName"><?php echo TABLE_CUSTOMER; ?> <span class="red">*</span></label></td>
                        <td style="width:33%"><?php if($allowSaleRep == true){ ?><label for="CreditMemoSalesRep"><?php echo TABLE_SALES_REP; ?></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="inputContainer" style="width:100%">
                                <?php
                                echo $this->Form->hidden('customer_id');
                                if($allowAddCustomer){
                                ?>
                                <div class="addnewSmall" style="float: left;">
                                    <?php echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width: 285px; border: none;')); ?>
                                    <img alt="<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addNewCustomerCM" onmouseover="Tip('<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                </div>
                                <?php
                                } else {
                                    echo $this->Form->text('customer_name', array('class' => 'validate[required]', 'style' => 'width:320px;'));
                                }
                                ?>
                                &nbsp;&nbsp;<img alt="<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchCustomerAddCM" onmouseover="Tip('<?php echo TABLE_SHOW_CUSTOMER_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" style="cursor: pointer; height: 22px; display: none;" class="deleteCreditMemoCustomer" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" />
                            </div>
                        </td>
                        <td>
                            <?php if($allowSaleRep == true){ ?>
                            <div class="inputContainer" style="width:100%">
                                <?php echo $this->Form->hidden('sales_rep_id'); ?>
                                <?php echo $this->Form->text('sales_rep_name', array('style' => 'width:70%')); ?>
                                <img alt="Search" search="1" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchCMRep" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                <img alt="Delete" search="0" align="absmiddle" style="display: none; cursor: pointer;" class="deleteCMRep" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
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
            <td>
                <table cellpadding="0" style="width: 100%">
                    <tr>
                        <td style="width:50%;"><?php if(count($branches) > 1){ ?><label for="CreditMemoBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                        <td style="width:50%"><?php if(count($locations) > 1){ ?><label for="CreditMemoLocationId"><?php echo TABLE_LOCATION; ?> <span class="red">*</span></label><?php } ?></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[CreditMemo][branch_id]" id="CreditMemoBranchId" class="validate[required]" style="width: 75%;">
                                    <?php
                                    if(count($branches) != 1){
                                    ?>
                                    <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    }
                                    foreach($branches AS $branch){
                                    ?>
                                    <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['cm_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="inputContainer" style="width:100%; <?php if(count($locations) == 1){ ?>display: none;<?php } ?>">
                                <select name="data[CreditMemo][location_id]" id="CreditMemoLocationId" class="validate[required]" style="width: 75%;">
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
<div class="orderDetailCreditMemo" style=" margin-top: 5px; text-align: center;"></div>
<div class="footerFormCreditMemo" style="">
    <div style="float: left; width: 26%; margin-top: 35px;">
        <div class="buttons">
            <a href="#" class="positive btnBackCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSaveCreditMemo" >
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveCM"><?php echo ACTION_SAVE_NEW; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePreviewCreditMemo" >
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSavePreviewCM"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="float: right; width:72%;">
        <table style="width: 100%;">
            <tr>
                
                <td style="width: 10%;"><label for="CreditMemoTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%">
                        <?php echo $this->Form->text('total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 78%; font-size:12px; font-weight: bold', 'value'=>'0')); ?> <span class="lblSymbolCM"></span>
                    </div>
                </td>
                <td style="width: 10%;"><label for="CreditMemoDiscount"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width: 25%;">
                    <?php 
                    echo $this->Form->hidden('discount_percent', array('class' => 'float', 'value'=>'0', 'readonly' => true));
                    echo $this->Form->text('discount', array('style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value'=>'0', 'readonly' => true)); 
                    ?> <span class="lblSymbolCM">($)</span>
                    <span id="CMLabelDisPercent"></span>
                    <img alt="Remove Discount" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemoveCMTotalDiscount" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove Discount')" />
                </td>
                <td style="width: 10%;"><label for="CreditMemoMarkUp"><?php echo TABLE_MARK_UP; ?>:</label></td>
                <td style="width: 25%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('mark_up', array('style' => 'width: 78%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value'=>'0')); ?> <span class="lblSymbolCM"></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="CreditMemoVatSettingId" id="lblCreditMemoVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="CreditMemoVatSettingId" name="data[CreditMemo][vat_setting_id]" style="width: 55%;" class="validate[required]">
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
                    <input type="hidden" value="" id="CreditMemoVatChartAccountId" name="data[CreditMemo][vat_chart_account_id]" />
                    <input type="hidden" name="data[CreditMemo][total_vat]" id="CreditMemoTotalVat" class="float" value="0" />
                    <?php echo $this->Form->text('vat_percent', array('style' => 'width: 15%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value' => 0)); ?> <span class="lblSymbolCMPercent">(%)</span>
                </td>
                <td><label for="CreditMemoSubTotalAmount"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td>
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->text('sub_total_amount', array('readonly' => true, 'class' => 'float validate[required]', 'style' => 'width: 78%; height:15px; font-size:12px; font-weight: bold', 'value'=>'0')); ?> <span class="lblSymbolCM"></span>
                    </div>
                </td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>