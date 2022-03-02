<?php 
echo $this->element('prevent_multiple_submit');
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
?>
<style type="text/css">
    .inputList{
        width: 80% !important;
        height: 30px;
    }
</style>
<script type="text/javascript">
    var rowTableListMakeDeposits =  $("#tblMakeDepositsRow");
    var indexRowMakeDeposits     = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#GeneralLedgerMakeDepositsFormBranchId").filterOptions('com', '0', '');
        displayClassAcc();
        $("#tblMakeDepositsRow").remove();
        $("#btnSmartCodeJournalEntry").click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/" + $("#GeneralLedgerReference").val().toUpperCase(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#GeneralLedgerReference").val(result);
                }
            });
        });
        
        $("#GeneralLedgerMakeDepositsForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#GeneralLedgerMakeDepositsForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                // check if total debit not equal to total credit
                var totalDebit=0;
                $(".debit").each(function(){
                    totalDebit+=Number(replaceNum($(this).val()));
                });
                var totalCredit=0;
                $(".credit").each(function(){
                    totalCredit+=Number(replaceNum($(this).val()));
                });
                totalDebit  = totalDebit.toFixed(2);
                totalCredit = converDicemalJS(totalCredit + replaceNum($("#credit1").val())).toFixed(2);

                // a/r a/p count
                $countArAp=0;
                $(".tblMakeDepositsRow").find(".chart_account_id").each(function(){
                   if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Receivable"){
                        $countArAp++;
                   }
                   if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Payable"){
                       $countArAp++;
                   }
                });

                if(totalDebit != totalCredit){
                    $("#GeneralLedgerDate").datepicker("option", "dateFormat", "dd/mm/yy");
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
                }else if($countArAp>1){
                    $("#GeneralLedgerDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_AR_AP_MORE_THAN_ONE; ?></p>');
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
                    $("#GeneralLedgerDate").datepicker("option", "dateFormat", "yy-mm-dd");
                    var confirmSave = $("#GeneralLedgerMakeDepositConfirmSave").val();
                    // Check Confirm Save
                    if(confirmSave == 0){
                        confirmSaveMakeDeposit();
                        $("#GeneralLedgerDate").datepicker("option", "dateFormat", "dd/mm/yy");
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
                var rightPanel=$("#GeneralLedgerMakeDepositsForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableGeneralLedger.fnDraw(false);
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Make Deposit', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Make Deposit', 'Add', 1, '');
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
        
        $("#GeneralLedgerDate").datepicker({
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
        
        var generalLedgerCompanyId="";
        if($.cookie('companyMakeDepositId')!=null){
            $("#GeneralLedgerMakeDepositsFormCompanyId").val($.cookie('companyMakeDepositId'));
            generalLedgerCompanyId = $.cookie('companyMakeDepositId');
            $("#GeneralLedgerMakeDepositsFormBranchId").filterOptions('com', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
        }
        <?php
        if(count($companies) == 1){
        ?>
        $("#GeneralLedgerMakeDepositsFormBranchId").filterOptions('com', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
        <?php
        }
        ?>
        $("#GeneralLedgerMakeDepositsFormCompanyId").change(function(){
            var obj = $(this);
            $.cookie('companyMakeDepositId', obj.val(), { expires: 7, path: "/" });
            if(generalLedgerCompanyId!="" && obj.val()!=generalLedgerCompanyId){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you change the company name all your data entry will be reset, proceed?</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(event, ui){
                        obj.val(generalLedgerCompanyId);
                        $.cookie('companyMakeDepositId', obj.val(), { expires: 7, path: "/" });
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $("#GeneralLedgerMakeDepositsFormBranchId").filterOptions('com', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
                            generalLedgerCompanyId=obj.val();
                            $(".chart_account_id").val("");
                            $(".class_id").val("");
                            $("#makeDepositsEndingBalance").text(0);
                            // Reset Class & Account
                            displayClassAcc();
                            // Reset Apply To
                            $("#GeneralLedgerDepositType").val(1);
                            $("#GeneralLedgerDepositType").change();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }else{
                $("#GeneralLedgerMakeDepositsFormBranchId").filterOptions('com', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
                generalLedgerCompanyId = obj.val();
                // Reset Class & Account
                displayClassAcc();
                // Reset Apply To
                $("#GeneralLedgerDepositType").val(1);
                $("#GeneralLedgerDepositType").change();
            }
        });
        
        $("#chartAccMakeDeposit").change(function(){
            if($(this).val()!=""){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getBalance/" + $(this).val() + "/" + $("#GeneralLedgerMakeDepositsFormCompanyId").val(),
                    data: "",
                    beforeSend: function(){

                    },
                    success: function(result){
                        $("#makeDepositsEndingBalance").text(result);
                    }
                });
            }
        });
        
        $(".btnBackJournalEntry").click(function(event){
            event.preventDefault();
            confirmBackMakeDeposit();
        });
        
        // Check Choose Apply To
        $("#GeneralLedgerDepositType").change(function(){
            var applyTo = $(this).val();
            var searchApply = $(".GeneralLedgerSearchApply");
            $(".deleteApplyDeposit").click();
            if(applyTo == 1){
                $(".divApplyTo").hide();
                $(".divApplyTo").find("td:eq(0) label").removeAttr('for').text('');
                searchApply.removeAttr('id').removeAttr('class').attr('class', 'GeneralLedgerSearchApply');
                // Reset Receive From in Table
                changeReceiveByApply(0, '', '');
            } else if(applyTo == 2){
                $(".divApplyTo").show();
                $(".divApplyTo").find("td:eq(0) label").removeAttr('for').attr('for', 'GLSearchPO').text('<?php echo MENU_PURCHASE_REQUEST; ?>');
                searchApply.removeAttr('id').removeAttr('class').attr('id', 'GLSearchPO').attr('class', 'GeneralLedgerSearchApply validate[required]');
                searchGLApplyTo('GLSearchPO', 1);
            } else if(applyTo == 3){
                $(".divApplyTo").show();
                $(".divApplyTo").find("td:eq(0) label").removeAttr('for').attr('for', 'GLSearchPB').text('<?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?>');
                searchApply.removeAttr('id').removeAttr('class').attr('id', 'GLSearchPB').attr('class', 'GeneralLedgerSearchApply validate[required]');
                searchGLApplyTo('GLSearchPB', 2);
            } else if(applyTo == 4){
                $(".divApplyTo").show();
                $(".divApplyTo").find("td:eq(0) label").removeAttr('for').attr('for', 'GLSearchQuote').text('<?php echo MENU_QUOTATION; ?>');
                searchApply.removeAttr('id').removeAttr('class').attr('id', 'GLSearchQuote').attr('class', 'GeneralLedgerSearchApply validate[required]');
                searchGLApplyTo('GLSearchQuote', 3);
            } else if(applyTo == 5){
                $(".divApplyTo").show();
                $(".divApplyTo").find("td:eq(0) label").removeAttr('for').attr('for', 'GLSearchInvoice').text('<?php echo MENU_SALES_ORDER_MANAGEMENT; ?>');
                searchApply.removeAttr('id').removeAttr('class').attr('id', 'GLSearchInvoice').attr('class', 'GeneralLedgerSearchApply validate[required]');
                searchGLApplyTo('GLSearchInvoice', 4);
            }
        });
        
        // Action Button Search Pop Up Apply To
        $(".searchApplyDeposit").click(function(){
            var applyToId = replaceNum($("#GeneralLedgerDepositType").val());
            var applyType = applyToId - 1;
            searchPopUpGLApplyTo(applyType);
        });
        
        // Action Button Clear Apply To Content
        $(".deleteApplyDeposit").click(function(){
            $("#GeneralLedgerApplyToId").val('');
            $(".GeneralLedgerSearchApply").val('');
            $("#supplyId").val('');
            $("#supplyName").val('');
            $(".searchApplyDeposit").show();
            $(".deleteApplyDeposit").hide();
            $("#totalBalanceApplyDeposit").val(0);
            $("#credit1").val(0);
            $("#toword").text(toWords(replaceNum($("#credit1").val()).toString()));
            // Reset Receive From in Table
            changeReceiveByApply(0, '', '');
        });
        
        cloneRowMakeDeposits();
        
        $("#credit1").keyup(function(){
            var totalDpt = replaceNum($(this).val());
            var totalApp = replaceNum($("#totalBalanceApplyDeposit").val());
            if($("#GeneralLedgerDepositType").val() != 1){
                if(totalDpt > totalApp){
                    $(this).val(totalApp);
                }
            }
            $("#toword").text(toWords(replaceNum($("#credit1").val()).toString()));
        });
        
        $("#credit1").blur(function(){
            $("#credit1").keyup();
        });
    });
    
    // Change Receive From By Apply
    function changeReceiveByApply(applyType, supplyId, supplyName){
        $(".tblMakeDepositsRow").each(function(){
            // Hide
            $(this).find(".choice").hide();
            $(this).find(".employee_name").hide();
            $(this).find(".other_name").hide();
            // Show
            if(applyType == 1 || applyType == 2){
                $(this).find(".customer_name").hide();
                $(this).find(".vendor_name").show();
                // Set Value
                $(this).find(".vendor_id").val(supplyId);
                $(this).find(".vendor_name").val(supplyName);
                $(this).find(".deleteName").hide();
            } else if(applyType == 3 || applyType == 4) {
                $(this).find(".vendor_name").hide();
                $(this).find(".customer_name").show();
                // Set Value
                $(this).find(".customer_id").val(supplyId);
                $(this).find(".customer_name").val(supplyName);
                $(this).find(".deleteName").hide();
            } else {
                $(this).find(".choice").show();
                $(this).find(".customer_name").hide();
                $(this).find(".vendor_name").hide();
                $(this).find(".vendor_id").val(supplyId);
                $(this).find(".customer_id").val(supplyName);
            }
        });
    }
    
    // Search Apply To
    function searchGLApplyTo(searchId, applyType){
        var url = '';
        if(applyType == 1){
            url = 'searchPurchaseOrder';
        } else if(applyType == 2){
            url = 'searchPurchaseBill';
        } else if(applyType == 3){
            url = 'searchQuote';
        } else if(applyType == 4){
            url = 'searchSalesInvoice';
        }
        $("#"+searchId).unautocomplete();
        $("#"+searchId).autocomplete("<?php echo $this->base."/".$this->params['controller']."/"; ?>"+url, {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyMakeDeposit(value.split(".*")[4])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyMakeDeposit(value.split(".*")[4])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            }
        }).result(function(event, value){
            var applyId   = value.toString().split(".*")[0];
            var applyCode = value.toString().split(".*")[2];
            var Amount    = value.toString().split(".*")[3];
            var supId     = value.toString().split(".*")[5];
            var supName   = value.toString().split(".*")[6];
            $("#supplyId").val(supId);
            $("#supplyName").val(supName);
            $("#GeneralLedgerApplyToId").val(applyId);
            $("#credit1").val(Amount);
            $("#totalBalanceApplyDeposit").val(Amount);
            $("#toword").text(toWords(replaceNum($("#credit1").val()).toString()));
            $(this).val(applyCode);
            // Change Button
            $(".searchApplyDeposit").hide();
            $(".deleteApplyDeposit").show();
            // Set Receive From
            changeReceiveByApply(applyType, supId, supName);
        });
    }
    
    function searchPopUpGLApplyTo(applyType){
        var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        var url = '';
        var title = '';
        if(applyType == 1){
            url = 'purchaseRequest';
            title = '<?php echo MENU_PURCHASE_REQUEST; ?>';
        } else if(applyType == 2){
            url = 'purchaseBill';
            title = '<?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?>';
        } else if(applyType == 3){
            url = 'quotation';
            title = '<?php echo MENU_QUOTATION; ?>';
        } else if(applyType == 4){
            url = 'salesInvoice';
            title = '<?php echo MENU_SALES_ORDER_MANAGEMENT; ?>';
        }
        if(companyMankeDepositId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/"+url+"/"+companyMankeDepositId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: title,
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
                                if($("input[name='chkApplyMakeDeposit']:checked").val()){
                                    var object    = $("input[name='chkApplyMakeDeposit']:checked");
                                    var companyId = object.attr('com-id');
                                    if(checkCompanyMakeDeposit(companyId) == true){
                                        var applyId   = object.attr('value');
                                        var applyCode = object.attr('app-code');
                                        var Amount    = object.attr('amt');
                                        var supId     = object.attr('suply-id');
                                        var supName   = object.attr('suply-name');
                                        $("#supplyId").val(supId);
                                        $("#supplyName").val(supName);
                                        $("#GeneralLedgerApplyToId").val(applyId);
                                        $("#totalBalanceApplyDeposit").val(Amount);
                                        $("#credit1").val(Amount);
                                        $("#toword").text(toWords(replaceNum($("#credit1").val()).toString()));
                                        $(".GeneralLedgerSearchApply").val(applyCode);
                                        // Change Button
                                        $(".searchApplyDeposit").hide();
                                        $(".deleteApplyDeposit").show();
                                        // Set Receive From
                                        changeReceiveByApply(applyType, supId, supName);
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
    
    function checkCompanyMakeDeposit(companyMankeDepositId){
        var companyReturn = false;
        var companyPut    = companyMankeDepositId.split(",");
        var companySelect = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function backMakeDeposit(){
        var rightPanel = $(".btnBackJournalEntry").parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function confirmSaveMakeDeposit(){
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
                '<?php echo ACTION_YES; ?>': function() {
                    $("#GeneralLedgerMakeDepositConfirmSave").val(1);
                    $("#GeneralLedgerMakeDepositsForm").submit();
                    $(this).dialog("close");
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function confirmBackMakeDeposit(){
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_BACK; ?></p>');
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
                    backMakeDeposit();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function calTotalAmountMakeDeposit(){
        var totalAmount = 0;
        var applyType   = $("#GeneralLedgerDepositType").val();
        $(".debit").each(function(){
            totalAmount += Number(replaceNum($(this).val()));
        });
        if(applyType == 1){
            $("#credit1").val(totalAmount.toFixed(2));
        }
        $("#toword").text(toWords(replaceNum($("#credit1").val()).toString()));
    }
    
    function displayClassAcc(){
        // Chart Account Filter
        $("#chartAccMakeDeposit").filterOptions('company_id', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
        // Chart Account All Filter
        $(".tblMakeDepositsRow").find(".chart_account_id").filterOptions('company_id', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
        // Class Filter
        $(".tblMakeDepositsRow").find(".class_id").filterOptions('company', $("#GeneralLedgerMakeDepositsFormCompanyId").val(), '');
    }
    function checkCustomer(field, rules, i, options){
        if(field.closest("tr").find(".choice").val()!="Customer" && field.closest("tr").find(".choice").val()!="Employee"){
            return "* Please Select Customer or Employee";
        }
    }
    function checkVendor(field, rules, i, options){
        if(field.closest("tr").find(".choice").val()!="Vendor"){
            return "* Please Select Vendor";
        }
    }
    function cloneRowMakeDeposits(){
        indexRowMakeDeposits = Math.floor((Math.random() * 100000) + 1);
        
        var tr    = rowTableListMakeDeposits.clone(true);
        tr.removeAttr("style").removeAttr("id");
        
        tr.find("td .chart_account_id").val('');
        tr.find("td .debit").val('');
        tr.find("td .credit").val('');
        tr.find("td .memo").val('');

        tr.find("td .choice").val('');
        tr.find("td .choice").show();

        tr.find("td .customer_id").val('');
        tr.find("td .vendor_id").val('');
        tr.find("td .employee_id").val('');
        tr.find("td .other_id").val('');

        tr.find("td .customer_name").hide();
        tr.find("td .vendor_name").hide();
        tr.find("td .employee_name").hide();
        tr.find("td .other_name").hide();
        tr.find("td .deleteName").hide();

        tr.find("td .class").val('');
        tr.find("td .btnRemoveGL").show();
        tr.find("td .btnAddGL").hide();

        tr.find("td .chart_account_id").attr("id", "chart_account_id_"+indexRowMakeDeposits);
        tr.find("td .debit").attr("id", "debit_"+indexRowMakeDeposits);
        tr.find("td .credit").attr("id", "credit_"+indexRowMakeDeposits);
        tr.find("td .memo").attr("id", "memo_"+indexRowMakeDeposits);
        tr.find("td .choice").attr("id", "choice_"+indexRowMakeDeposits);
        tr.find("td .customer_name").attr("id", "customer_name_"+indexRowMakeDeposits);
        tr.find("td .vendor_name").attr("id", "vendor_name_"+indexRowMakeDeposits);
        tr.find("td .employee_name").attr("id", "employee_name_"+indexRowMakeDeposits);
        tr.find("td .other_name").attr("id", "other_name_"+indexRowMakeDeposits);
        tr.find("td .class_id").attr("id", "class_id_"+indexRowMakeDeposits);
        $("#tblGL").append(tr);
        var LenTr = parseInt($(".tblMakeDepositsRow").length);
        if(LenTr == 1){
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnRemoveGL").hide();
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
        }else{
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
        }
        // Check Receive By Apply
        var supplyId   = $("#supplyId").val();
        var supplyName = $("#supplyName").val();
        var applyToId = replaceNum($("#GeneralLedgerDepositType").val());
        var applyType = applyToId - 1;
        changeReceiveByApply(applyType, supplyId, supplyName);
        checkEventMakeDeposit();
        tr.find("td .chart_account_id").focus();
        // Check Acc & Class With Company
        displayClassAcc();
    }
    function eventKeyMakeDeposits(){
        $(".chart_account_id, .debit, .credit, .memo, .choice, .deleteName, .btnAddGL, .btnRemoveGL").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 2, aSep: ',', mNum: 15});
        $('.chart_account_id').chosen();
        $('.chart_account_id').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").find(".debit").focus().select();
                return false;
            }
        });
        $('.debit').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).blur();
                $(this).closest("tr").find(".memo").focus().select();
                return false;
            }
        });
        $('.memo').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if($(this).closest("tr").next().length==0){
                    $(".btnAddGL:last").click();
                }
                $(this).closest("tr").next().find(".chart_account_id").focus().select();
                return false;
            }
        });
        $(".chart_account_id").change(function(){
            // check if its type Accounts Receivable or Accounts Payable
            if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Receivable"){
                $(this).closest("tr").find("td .choice").attr("class","choice validate[required,funcCall[checkCustomer]]");
                $(this).closest("tr").find("td .customer_name").attr("class","customer_name validate[required,funcCall[checkCustomer]] inputList");
                $(this).closest("tr").find("td .vendor_name").attr("class","vendor_name validate[required,funcCall[checkCustomer]] inputList");
                $(this).closest("tr").find("td .employee_name").attr("class","employee_name validate[required,funcCall[checkCustomer]] inputList");
                $(this).closest("tr").find("td .other_name").attr("class","other_name validate[required,funcCall[checkCustomer]] inputList");
            }else if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Payable"){
                $(this).closest("tr").find("td .choice").attr("class","choice validate[required,funcCall[checkVendor]]");
                $(this).closest("tr").find("td .customer_name").attr("class","customer_name validate[required,funcCall[checkVendor]] inputList");
                $(this).closest("tr").find("td .vendor_name").attr("class","vendor_name validate[required,funcCall[checkVendor]] inputList");
                $(this).closest("tr").find("td .employee_name").attr("class","employee_name validate[required,funcCall[checkVendor]] inputList");
                $(this).closest("tr").find("td .other_name").attr("class","other_name validate[required,funcCall[checkVendor]] inputList");
            }else{
                $(this).closest("tr").find("td .choice").attr("class","choice");
                $(this).closest("tr").find("td .customer_name").attr("class","customer_name validate[required] inputList");
                $(this).closest("tr").find("td .vendor_name").attr("class","vendor_name validate[required] inputList");
                $(this).closest("tr").find("td .employee_name").attr("class","employee_name validate[required] inputList");
                $(this).closest("tr").find("td .other_name").attr("class","other_name validate[required] inputList");
            }
            $(this).closest("tr").find(".debit").focus().select();
        });
        
        $(".debit").keyup(function(){
            if($(this).val()!=0){
                $(this).closest("tr").find("td .credit").val(0);
            }
            calTotalAmountMakeDeposit();
        });
        $(".debit").blur(function(){
            if($.trim($(this).val())!=''){
                calTotalAmountMakeDeposit();
            }
        });
        $(".credit").keyup(function(){
            if($(this).val()!=0){
                $(this).closest("tr").find("td .debit").val(0);
            }
        });
        
        $(".choice").change(function(){
            var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
            if(companyMankeDepositId != ''){
                if($(this).val()=="Customer"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".customer_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchCustomerGeneralLedgerAdd($(this));
                    });
                }else if($(this).val()=="Vendor"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".vendor_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchVendorGeneralLedgerAdd($(this));
                    });
                }else if($(this).val()=="Employee"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".employee_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchEmployeeGeneralLedgerAdd($(this));
                    });
                }else if($(this).val()=="Other"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".other_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchOtherGeneralLedgerAdd($(this));
                    });
                }
            }else{
                $(this).find("option[value='']").attr('selected','selected');
                alertSelectCompanyMakeDeposits();
            }
        });
        
        $(".deleteName").click(function(){
            $(this).hide('');

            $(this).closest("tr").find(".choice").val('');
            $(this).closest("tr").find(".choice").show();

            $(this).closest("tr").find(".customer_id").val('');
            $(this).closest("tr").find(".vendor_id").val('');
            $(this).closest("tr").find(".employee_id").val('');
            $(this).closest("tr").find(".other_id").val('');

            $(this).closest("tr").find(".customer_name").val('');
            $(this).closest("tr").find(".vendor_name").val('');
            $(this).closest("tr").find(".employee_name").val('');
            $(this).closest("tr").find(".other_name").val('');

            $(this).closest("tr").find(".customer_name").hide();
            $(this).closest("tr").find(".vendor_name").hide();
            $(this).closest("tr").find(".employee_name").hide();
            $(this).closest("tr").find(".other_name").hide();
        });
        $(".btnAddGL").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveGL").show();
            cloneRowMakeDeposits();
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
                        var lenTr = parseInt($(".tblMakeDepositsRow").length);
                        if(lenTr == 1){
                            $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnRemoveGL").hide();
                        }
                        $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnAddGL").show();
                        $(this).dialog("close");
                        calTotalAmountMakeDeposit();
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function searchCustomerGeneralLedgerAdd(obj){
        var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        if(companyMankeDepositId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyMankeDepositId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_CUSTOMER_MANAGEMENT_INFO; ?>',
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
                                if($("input[name='chkCustomer']:checked").val()){
                                    obj.closest("tr").find(".customer_id").val($("input[name='chkCustomer']:checked").val());
                                    obj.closest("tr").find(".customer_name").val($("input[name='chkCustomer']:checked").attr("rel"));
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    function searchVendorGeneralLedgerAdd(obj){
        var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        if(companyMankeDepositId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/vendor/"+companyMankeDepositId,
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
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    function searchEmployeeGeneralLedgerAdd(obj){
        var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        if(companyMankeDepositId != '' && $("#GeneralLedgerDate").val() != ''){
            $("#GeneralLedgerDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var orderDate = $("#GeneralLedgerDate").val();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyMankeDepositId+"/"+orderDate,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $("#GeneralLedgerDate").datepicker("option", "dateFormat", "dd/mm/yy");
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_EMPLOYEE; ?>',
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
                                if($("input[name='chkEmployee']:checked").val()){
                                    obj.closest("tr").find(".employee_id").val($("input[name='chkEmployee']:checked").val().split('|||')[0]);
                                    obj.closest("tr").find(".employee_name").val($("input[name='chkEmployee']:checked").val().split('|||')[2]);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    function searchOtherGeneralLedgerAdd(obj){
        var companyMankeDepositId = $("#GeneralLedgerMakeDepositsFormCompanyId").val();
        if(companyMankeDepositId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/other/"+companyMankeDepositId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_OTHER; ?>',
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
                                if($("input[name='chkOther']:checked").val()){
                                    obj.closest("tr").find(".other_id").val($("input[name='chkOther']:checked").val().split('|||')[0]);
                                    obj.closest("tr").find(".other_name").val($("input[name='chkOther']:checked").val().split('|||')[2]);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    function alertSelectCompanyMakeDeposits(){
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
    
    function checkEventMakeDeposit(){
        eventKeyMakeDeposits();
        $(".tblMakeDepositsRow").unbind("click");
        $(".tblMakeDepositsRow").click(function(){
            eventKeyMakeDeposits();
        });
    }
</script>
<br />
<?php echo $this->Form->create('GeneralLedger'); ?>
<input type="hidden" id="GeneralLedgerMakeDepositConfirmSave" value="0" />
<fieldset>
    <legend><?php __(MENU_JOURNAL_ENTRY_MANAGEMENT_MAKE_DEPOSITS); ?></legend>
    <div style="float: right; width: 35%; text-align: right;">
        <table style="width: 100%; float: right;">
            <tr>
                <td style="width: 35%;"><label for="GeneralLedgerMakeDepositsFormCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                <td style="text-align: left;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        if(count($companies) != 1){
                            $empty = INPUT_SELECT;
                        } else {
                            $empty = false;
                        }
                        echo $this->Form->input('company_id', array('empty' => $empty, 'id' => 'GeneralLedgerMakeDepositsFormCompanyId', 'class' => 'validate[required]', 'label' => false, 'style' => 'width: 80%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="GeneralLedgerMakeDepositsFormBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td style="text-align: left;">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[GeneralLedger][branch_id]" id="GeneralLedgerMakeDepositsFormBranchId" class="validate[required]" style="width: 80%;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="GeneralLedgerDepositType"><?php echo TABLE_APPLY_TO; ?> :</label></td>
                <td style="text-align: left;">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[GeneralLedger][deposit_type]" id="GeneralLedgerDepositType" style="width: 80%;">
                            <option value="1"><?php echo INPUT_SELECT; ?></option>
                            <option value="2"><?php echo MENU_PURCHASE_REQUEST; ?></option>
                            <option value="3"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?></option>
                            <option value="4"><?php echo MENU_QUOTATION; ?></option>
                            <option value="5"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr class="divApplyTo" style="display: none;">
                <td><label></label></td>
                <td style="text-align: left;">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="hidden" name="data[GeneralLedger][receive_from_id]" id="supplyId" />
                        <input type="hidden" name="data[GeneralLedger][receive_from_name]" id="supplyName" />
                        <input type="hidden" name="data[GeneralLedger][apply_to_id]" id="GeneralLedgerApplyToId" />
                        <input type="text" name="data[GeneralLedger][apply_reference]" class="GeneralLedgerSearchApply" style="width: 75%;" />
                        <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchApplyDeposit" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteApplyDeposit" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
            </tr>
            <tr class="divApplyTo" style="display: none;">
                <td><?php echo GENERAL_BALANCE; ?> : </td>
                <td style="text-align: left;">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="text" style="width: 75%;" id="totalBalanceApplyDeposit" readonly="" value="0" />
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table style="float: left; width: 64%;">
        <tr>
            <td style="width: 15%;"><label for="GeneralLedgerDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td style="width: 50%;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width: 90%;')); ?>
                </div>
            </td>
            <td></td>
        </tr>
        <tr>
            <td><label for="GeneralLedgerReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('reference', array('class'=>'validate[required]' , 'style' => 'width: 90%;')); ?>
                    <img alt="" src="<?php echo $this->webroot . 'img/button/cycle.png'; ?>" id="btnSmartCodeJournalEntry" style="cursor: pointer;" onmouseover="Tip('Smart Code')" />
                </div>
            </td>
            <td></td>
        </tr>
        <tr>
            <td><label for="chartAccMakeDeposit"><?php echo SALES_ORDER_DEPOSIT_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select id="chartAccMakeDeposit" name="chart_account_id[]" class="validate[required]" style="width: 50%; float: left;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (1) ORDER BY account_codes");
                        while($data[0]=mysql_fetch_array($query[0])){
                        ?>
                        <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>"><?php echo $data[0]['name']; ?></option>
                        <?php } ?>
                    </select>
                    <div style="padding-top: 12px; width: 49%; float: right;">
                        Ending Balance:&nbsp;&nbsp;&nbsp;&nbsp;<span id="makeDepositsEndingBalance">0</span>
                    </div>
                </div>
            </td>
            <td></td>
        </tr>
        <tr>
            <td><label for="credit1"><?php echo GENERAL_AMOUNT; ?> ($) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" id="credit1" name="debit[]" class="validate[required] float" style="width: 90%;" />
                    <input type="hidden" id="debit1" name="credit[]" value="0" class="debit" />
                    <input type="hidden" id="vendor_id1" name="vendor_id[]" value="" class="vendor_id" />
                    <input type="hidden" id="customer_id1" name="customer_id[]" value="" class="customer_id" />
                    <input type="hidden" id="employee_id1" name="employee_id[]" value="" class="employee_id" />
                    <input type="hidden" id="other_id1" name="other_id[]" value="" class="other_id" />
                    <input type="hidden" id="class_id1" name="class_id[]" value="" class="class_id" />
                </div>
            </td>
            <td id="toword"></td>
        </tr>
        <tr>
            <td><label for="memo1"><?php echo TABLE_MEMO; ?>:</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" id="memo1" name="memo[]" value="Deposit" class="memo" style="width: 90%;" />
                </div>
            </td>
            <td></td>
        </tr>
    </table>
    <div style="clear: both;"></div>
</fieldset>
<br />
<table id="tblGL" class="table" cellspacing="0">
    <tr>
        <th class="first" style="width: 20%;"><?php echo GENERAL_RECEIVE_FROM; ?></th>
        <th style="width: 20%;"><?php echo GENERAL_FROM_ACCOUNT; ?></th>
        <th style="width: 10%;"><?php echo GENERAL_AMOUNT; ?> ($)</th>
        <th><?php echo TABLE_MEMO; ?></th>
        <th style="width: 15%;"><?php echo TABLE_CLASS; ?></th>
        <th></th>
    </tr>
    <tr id="tblMakeDepositsRow" class="tblMakeDepositsRow" style="visibility: hidden;">
        <td class="first">
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('choice', array('empty' => INPUT_SELECT, 'options'=>array('Customer' => 'Customer', 'Vendor' => 'Vendor', 'Employee' => 'Employee', 'Other' => 'Other'), 'id' => 'choice2', 'name' => 'choice[]',  'class' => 'choice', 'label' => false, 'div' => false, 'style' => 'width: 90%;')); ?>
                <input type="hidden" name="vendor_id[]" class="vendor_id" />
                <?php echo $this->Form->text('vendor_name', array('id' => 'vendor_name2', 'class' => 'vendor_name validate[required] inputList', 'style' => 'display: none;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="customer_id[]" class="customer_id" />
                <?php echo $this->Form->text('customer_name', array('id' => 'customer_name2', 'class' => 'customer_name validate[required] inputList', 'style' => 'display: none;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="employee_id[]" class="employee_id" />
                <?php echo $this->Form->text('employee_name', array('id' => 'employee_name2', 'class' => 'employee_name validate[required] inputList', 'style' => 'display: none;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="other_id[]" class="other_id" />
                <?php echo $this->Form->text('other_name', array('id' => 'other_name2', 'class' => 'other_name validate[required] inputList', 'style' => 'display: none;', 'readonly' => true, 'label' => false)); ?>
                <img alt="Delete" align="absmiddle" class="deleteName" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" style="display: none;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <select id="chart_account_id2" name="chart_account_id[]" class="chart_account_id validate[required]" style="width: 250px;"> 
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE is_active=1 ORDER BY account_codes");
                    while($data[0]=mysql_fetch_array($query[0])){
                    ?>
                    <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>"><?php echo $data[0]['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
        <td>
            <div class="inputContainer">
                <input type="hidden" id="credit2" name="debit[]" value="0" class="credit" />
                <input type="text" id="debit2" name="credit[]" class="debit validate[required] float" style="width: 90%; height: 30px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" id="memo2" name="memo[]" class="memo" style="width: 90%; height: 30px;" />
            </div>
        </td>
        <td style="white-space: nowrap;">
            <div class="inputContainer" style="width: 100%;">
                <select id="class_id2" name="class_id[]" class="class_id" style="width: 90%;">
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    $query[0]=mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                    while($data[0]=mysql_fetch_array($query[0])){
                        $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[0]['id']);
                    ?>
                    <option company="<?php echo $data[0]['company']; ?>" value="<?php echo $data[0]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
                        <?php
                        $query[1]=mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                        while($data[1]=mysql_fetch_array($query[1])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[1]['id']);
                        ?>
                        <option company="<?php echo $data[1]['company']; ?>" value="<?php echo $data[1]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                            <?php
                            $query[2]=mysql_query("SELECT id,name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data[2]=mysql_fetch_array($query[2])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[2]['id']);
                            ?>
                            <option company="<?php echo $data[2]['company']; ?>" value="<?php echo $data[2]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                <?php
                                $query[3]=mysql_query("SELECT id,name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                while($data[3]=mysql_fetch_array($query[3])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[3]['id']);
                                ?>
                                <option company="<?php echo $data[3]['company']; ?>" value="<?php echo $data[3]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                    <?php
                                    $query[4]=mysql_query("SELECT id,name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                    while($data[4]=mysql_fetch_array($query[4])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[4]['id']);
                                    ?>
                                    <option company="<?php echo $data[4]['company']; ?>" value="<?php echo $data[4]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                        <?php
                                        $query[5]=mysql_query("SELECT id,name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                        while($data[5]=mysql_fetch_array($query[5])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[5]['id']);
                                        ?>
                                        <option company="<?php echo $data[5]['company']; ?>" value="<?php echo $data[5]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
        </td>
        <td style="white-space: nowrap;">
            <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddGL" style="cursor: pointer;" onmouseover="Tip('Add New')" />
            <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveGL" style="cursor: pointer;display: none;" onmouseover="Tip('Remove')" />
        </td>
    </tr>
</table>
<br />
<div class="buttons">
    <a href="" class="positive btnBackJournalEntry">
        <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
        <?php echo ACTION_BACK; ?>
    </a>
</div>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>