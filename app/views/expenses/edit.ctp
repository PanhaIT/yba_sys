<?php
include("includes/function.php");

echo $this->element('prevent_multiple_submit'); 
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);

$this->element('check_access');
$allowAddVendor  = checkAccess($user['User']['id'], 'vendors', 'quickAdd');
?>
<script type="text/javascript">
    var rowTableListExpense = $("#tblExpenseRow");
    var indexRowExpense     = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#tblExpenseRow").remove();
        $("#ExpenseEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ExpenseEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                // check if total amount not equal to total credit
                var totalDebit=0;
                $(".amount").each(function(){
                    totalDebit += Number(replaceNum($(this).val()));
                });
                var totalCredit = replaceNum($("#ExpenseTotalAmount").val());
                if(totalDebit != totalCredit){
                    $("#ExpenseDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DEBIT_CREDIT; ?></p>');
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
                    $("button[type=submit]", $form).removeAttr('disabled');
                    return false;
                }else{
                    $("#ExpenseDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    var confirmSave = $("#ExpenseExpenseConfirmSave").val();
                    // Check Confirm Save
                    if(confirmSave == 0){
                        confirmSaveExpense();
                        $("#ExpenseDate").datepicker("option", "dateFormat", "dd/mm/yy");
                        $("button[type=submit]", $form).removeAttr('disabled');
                        return false;
                    }
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackExpense").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Expense', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Expense', 'Add', 1, '');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
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
            }
        });
        
        $("#ExpenseDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            minDate: '<?php echo $dataClosingDate[0]; ?>',
            maxDate: 0,
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        
        $(".btnBackExpense").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableExpense.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Vendor
        $("#ExpenseVendorName").autocomplete("<?php echo $this->base . "/reports/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var vendorId = value.toString().split(".*")[0];
            $("#ExpenseVendorId").val(vendorId);
            $("#ExpenseVendorName").show();
        });
        
        $("#deleteExpenseVendor").click(function(){
            $(this).hide();
            $("#ExpenseVendorId").hide();
            $("#ExpenseVendorName").val('');
        });
        
        <?php
        if($allowAddVendor){
        ?>
        $("#addVendorExpense").click(function(){
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
                        title: '<?php echo MENU_VENDOR_ADD; ?>',
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
                                                createSysAct('Expense', 'Quick Add Vendor', 2, result);
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
                                                createSysAct('Expense', 'Quick Add Vendor', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Vendor
                                                    $("#ExpenseVendorId").val(result.id);
                                                    $("#ExpenseVendorName").val(result.name);
                                                    $("#ExpenseVendorName").attr("readonly", true);
                                                    $("#ExpenseEditForm").validationEngine("hideAll");
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
        $(".tblExpenseRow").each(function(){
            var accId = $(this).find(".chart_account_id").attr("id");
            loadQuickAddType(accId);
        });
        eventKeyExpense();
    });
    
    function confirmSaveExpense(){
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_SAVE; ?></p>');
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
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                },
                '<?php echo ACTION_YES; ?>': function() {
                    $("#ExpenseExpenseConfirmSave").val(1);
                    $("#ExpenseEditForm").submit();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function calTotalAmountExpense(){
        var totalAmount = 0;
        $(".amount").each(function(){
            totalAmount += Number(replaceNum($(this).val()));
        });
        $("#ExpenseTotalAmount").val(totalAmount.toFixed(2));
    }
    
    function displayChartAccExpense(){
        // Chart Account Filter
        $(".chart_account_id").filterOptions('company_id', $("#ExpenseCompanyId").val(), '');
        $(".chart_account_id").trigger("chosen:updated");
    }
    
    function checkVendorExpense(field, rules, i, options){
        if(field.closest("tr").find(".choice").val()!="Vendor"){
            return "* Please Select Vendor";
        }
    }
    
    function cloneRowExpense(){
        indexRowExpense = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableListExpense.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .chart_account_id").attr("id", "chart_account_id_"+indexRowExpense);
        tr.find("td .amount").attr("id", "amount_"+indexRowExpense);
        tr.find("td .memo").attr("id", "memo_"+indexRowExpense);
        // Check Acc & Class With Company
        tr.find(".chart_account_id").filterOptions('company_id', $("#ExpenseCompanyId").val(), '');
        $("#tblGL").append(tr);
        var LenTr = parseInt($(".tblExpenseRow").length);
        if(LenTr == 1){
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnRemoveGL").hide();
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
        }else{
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnRemoveGL").show();
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
        }
        loadQuickAddType("chart_account_id_"+indexRowExpense);
        eventKeyExpense();
    }
    
    function eventKeyExpense(){
        $(".amount, .memo, .btnAddGL, .btnRemoveGL").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 5, aSep: ',', mNum: 15});
        
        $('.memo').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if($(this).closest("tr").next().length==0){
                    $(".btnAddGL:last").click();
                }
                $(this).closest("tr").next().find(".chart_account_id").focus().select();
                return false;
            }
        });
        
        $(".amount").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".amount").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
            calTotalAmountExpense();
        });
        
        $(".amount").keyup(function(){
            calTotalAmountExpense();
        });
        
        $('.amount').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).blur();
                $(this).closest("tr").find(".memo").focus().select();
                return false;
            }
        });
        
        $(".btnAddGL").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveGL").show();
            cloneRowExpense();
        });
        
        $(".btnRemoveGL").click(function(){
            var obj=$(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
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
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".tblExpenseRow").length);
                        if(lenTr == 1){
                            $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnRemoveGL").hide();
                        }
                        $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnAddGL").show();
                        $(this).dialog("close");
                        calTotalAmountExpense();
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function searchVendorExpense(obj){
        var companyId = $("#ExpenseCompanyId").val();
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
                        title: '<?php echo MENU_VENDOR; ?>',
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
                                if($("input[name='chkVendor']:checked").val()){
                                    obj.closest("tr").find(".vendor_id").val($("input[name='chkVendor']:checked").val().split('|||')[0]);
                                    obj.closest("tr").find(".vendor_name").val($("input[name='chkVendor']:checked").val().split('|||')[2]);
                                } else {
                                    if(obj.closest("tr").find(".vendor_id").val() == ''){
                                        $(".deleteName").click();
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
    
    function alertSelectCompanyExpense(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SELECT_COMPANY_FIRST; ?></p>');
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
                    $("#ProductCompanyId").select();
                }
            }
        });
    }
    
    function loadQuickAddType(objId){
        $("#"+objId).chosen({ width: 350, allow_add: true, allow_add_label: '<?php echo MENU_EXPENSE_TYPE_ADD; ?>', allow_add_id: 'addNewExType'+objId });
        $("#addNewExType"+objId).click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/expenses/addExpenseType/"; ?>",
                beforeSend: function(){
                    $("#"+objId).trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_EXPENSE_TYPE_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '200',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#ExpenseAddExpenseTypeForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addExpenseType",
                                        data: $("#ExpenseAddExpenseTypeForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Expense Type', 'Quick Add Expense Type', 2, result);
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
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Expense Type', 'Quick Add Expense Type', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#addNewExType"+objId).html(result.option);
                                                $("#addNewExType"+objId).trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
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
                    });
                }
            });
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackExpense">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Expense');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Expense][company_id]" id="ExpenseCompanyId" />
<?php
}
if(count($branches) == 1){
    $branchId = $branches[0]['Branch']['id'];
?>
<input type="hidden" value="<?php echo $branchId; ?>" name="data[Expense][branch_id]" id="ExpenseBranchId" />
<?php
}
?>
<input type="hidden" id="ExpenseExpenseConfirmSave" value="0" />
<fieldset>
    <legend><?php __(MENU_EXPENSE_INFO); ?></legend>
    <div class="inputContainer" style="float: right;">
        <table style="width: 400px;">
            <?php
            if(count($branches) > 1){
            ?>
            <tr>
                <td><label for="ExpenseBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[Expense][branch_id]" id="ExpenseBranchId" class="validate[required]">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['expense_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <td><label for="ExpenseNote"><?php echo TABLE_MEMO; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <textarea id="ExpenseNote" name="data[Expense][note]" style="width: 90%; height: 80px;"><?php echo $this->data['Expense']['note']; ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table>
        <tr>
            <td><label for="ExpenseDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width: 150px;', 'value' => dateShort($this->data['Expense']['date']))); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ExpenseReference"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('reference', array('class'=>'validate[required]', 'style' => 'width: 150px;', 'readonly' => true)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ExpenseVendorName"><?php echo TABLE_VENDOR; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    $disDelVendor = '';
                    if($this->data['Expense']['vendor_id'] == ''){
                        $disDelVendor = 'display: none;';
                    }
                    echo $this->Form->hidden('vendor_id'); 
                    if($allowAddVendor){
                    ?>
                    <div class="addnewSmall" style="float: left;">
                        <?php echo $this->Form->text('vendor_name', array('style' => 'width: 285px; border: none;', 'value' => $this->data['Vendor']['name'])); ?>
                        <img alt="<?php echo MENU_VENDOR_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addVendorExpense" onmouseover="Tip('<?php echo MENU_VENDOR_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                    </div>
                    <?php
                    } else {
                        echo $this->Form->text('vendor_name', array('style' => 'width:320px', 'value' => $this->data['Vendor']['name']));
                    }
                    ?>
                    <img alt="Delete" align="absmiddle" id="deleteExpenseVendor" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" style="<?php echo $disDelVendor; ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ExpenseTotalAmount"><?php echo GENERAL_AMOUNT; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="ExpenseTotalAmount" name="data[Expense][total_amount]" class="float" readonly="" value="<?php echo number_format($this->data['Expense']['total_amount'], 2); ?>" style="width: 150px;" /> ($)
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<table id="tblGL" class="table" cellspacing="0">
    <tr>
        <th class="first" style="width: 35%;"><?php echo MENU_EXPENSES_TYPE; ?></th>
        <th style="width: 15%;"><?php echo GENERAL_AMOUNT; ?> ($)</th>
        <th style="width: 40%;"><?php echo TABLE_MEMO; ?></th>
        <th></th>
    </tr>
    <tr id="tblExpenseRow" class="tblExpenseRow" style="visibility: hidden;">
        <td class="first">
            <div class="inputContainer" style="width: 100%;">
                <select name="chart_account_id[]" class="chart_account_id">
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    $query=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                    while($data=mysql_fetch_array($query)){
                    ?>
                    <option value="<?php echo $data['id']; ?>"><?php echo $data['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" name="amount[]" value="0" class="amount float" style="width: 90%; height: 25px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" name="memo[]" class="memo" style="width: 95%; height: 25px;" />
            </div>
        </td>
        <td style="white-space: nowrap;">
            <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddGL" style="cursor: pointer;" onmouseover="Tip('Add New')" />
            <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveGL" style="cursor: pointer;display: none;" onmouseover="Tip('Remove')" />
        </td>
    </tr>
    <?php
    foreach($expenseDeatils AS $expenseDeatil){
    ?>
    <tr class="tblExpenseRow">
        <td class="first">
            <div class="inputContainer" style="width: 100%;">
                <select name="chart_account_id[]" class="chart_account_id">
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    $query=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name, (SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                    while($data=mysql_fetch_array($query)){
                        $accCom = explode(",", $data['company_id']);
                        if(in_array($this->data['Expense']['company_id'], $accCom)){
                    ?>
                    <option value="<?php echo $data['id']; ?>" company_id="<?php echo $data['company_id']; ?>" <?php if($data['id'] == $expenseDeatil['ExpenseDetail']['chart_account_id']){ ?>selected=""<?php } ?>><?php echo $data['name']; ?></option>
                    <?php 
                        }
                    } ?>
                </select>
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" name="amount[]" value="<?php echo number_format($expenseDeatil['ExpenseDetail']['amount'], 2); ?>" class="amount float" style="width: 90%; height: 25px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" name="memo[]" value="<?php echo $expenseDeatil['ExpenseDetail']['note']; ?>" class="memo" style="width: 95%; height: 25px;" />
            </div>
        </td>
        <td style="white-space: nowrap;">
            <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddGL" style="cursor: pointer;" onmouseover="Tip('Add New')" />
            <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveGL" style="cursor: pointer;display: none;" onmouseover="Tip('Remove')" />
        </td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>