<?php echo $this->element('prevent_multiple_submit'); ?>
<?php
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
?>
<script type="text/javascript">
    var rowTableJournalSuper =  $("#tblJournalSuperRow");
    var indexRowJournalSuper = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#GeneralLedgerBranchId").filterOptions('com', '0', '');
        var generalLedgerCompanyId="";
        if($.cookie('companyId')!=null){
            $("#GeneralLedgerCompanyId").val($.cookie('companyId'));
            generalLedgerCompanyId=$.cookie('companyId');
            $("#GeneralLedgerBranchId").filterOptions('com', $("#GeneralLedgerCompanyId").val(), '');
        }
        <?php
        if(count($companies) == 1){
        ?>
        $("#GeneralLedgerBranchId").filterOptions('com', $("#GeneralLedgerCompanyId").val(), '');
        <?php
        }
        ?>
        displayClassAcc();
        $("#tblJournalSuperRow").remove();
        $("#btnSmartCodeJournalSuperEntry").click(function(){
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
        
        $("#GeneralLedgerAddAllForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#GeneralLedgerAddAllForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                // check if total debit not equal to total credit
                var totalDebit=new Array();
                var totalCredit=new Array();
                $(".company_id").each(function(){
                    totalDebit[$(this).val()]=0;
                    totalCredit[$(this).val()]=0;
                });
                $(".debit").each(function(){
                    totalDebit[$(this).closest("tr").find(".company_id").val()]+=Number(replaceNum($(this).val()));
                });
                $(".credit").each(function(){
                    totalCredit[$(this).closest("tr").find(".company_id").val()]+=Number(replaceNum($(this).val()));
                });
                var notEqual=false;
                for (var key in totalDebit){
                    totalDebit[key]=totalDebit[key].toFixed(2);
                    totalCredit[key]=totalCredit[key].toFixed(2);
                    if(totalDebit[key]!=totalCredit[key]){
                        notEqual=true;
                    }
                }

                // a/r a/p count
                $countArAp=0;
                $(".chart_account_id").each(function(){
                   if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Receivable"){
                        $countArAp++;
                   }
                   if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Payable"){
                       $countArAp++;
                   }
                });

                if(notEqual){
                    $("#GeneralLedgerDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DEBIT_CREDIT_BY_COMPANY; ?></p>');
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
                    var confirmSave = $("#GeneralLedgerAddAllConfirmSave").val();
                    // Check Confirm Save
                    if(confirmSave == 0){
                        confirmSaveEntrySuperVisor();
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
                var rightPanel=$("#GeneralLedgerAddAllForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableGeneralLedgerAll.fnDraw(false);
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Journal Entry Supervisor', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Journal Entry Supervisor', 'Add', 1, '');
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
        $("#GeneralLedgerCompanyId").change(function(){
            var obj = $(this);
            $.cookie('companyId', obj.val(), { expires: 7, path: "/" });
            if(generalLedgerCompanyId != "" && obj.val() != generalLedgerCompanyId){
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
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            generalLedgerCompanyId=obj.val();
                            $("#GeneralLedgerBranchId").filterOptions('com', $("#GeneralLedgerCompanyId").val(), '');
                            $(".chart_account_id").val("");
                            $(".class_id").val("");
                            displayClassAcc();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }else{
                $("#GeneralLedgerBranchId").filterOptions('com', $("#GeneralLedgerCompanyId").val(), '');
                generalLedgerCompanyId=obj.val();
                displayClassAcc();
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
        if($.cookie('companyId')!=null){
            $(".company_id").val($.cookie('companyId'));
        }
        
        $(".btnBackJournalSuperEntry").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Clone Journal Post
        for ( var i = 0; i < 2; i++ ) {
            cloneTblJournalSuper();
        }
    });
    
    function displayClassAcc(){
        // Chart Account Filter
        $(".chart_account_id").filterOptions('company_id', $("#GeneralLedgerCompanyId").val(), '');
        // Class Filter
        $(".class_id").filterOptions('company', $("#GeneralLedgerCompanyId").val(), '');
    }
    
    function confirmSaveEntrySuperVisor(){
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
                    $("#GeneralLedgerAddAllConfirmSave").val(1);
                    $("#GeneralLedgerAddAllForm").submit();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function cloneTblJournalSuper(){
        indexRowJournalSuper = Math.floor((Math.random() * 100000) + 1);
        var tr    = rowTableJournalSuper.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .chart_account_id").val('');
        tr.find("td .debit").val('');
        tr.find("td .credit").val('');
        tr.find("td .memo").val('');

        tr.find("td .choice").show();
        tr.find("td .customer_id").hide();
        tr.find("td .vendor_id").hide();
        tr.find("td .employee_id").hide();
        tr.find("td .other_id").hide();
        tr.find("td .choice").val('');
        tr.find("td .customer_id").val('');
        tr.find("td .vendor_id").val('');
        tr.find("td .employee_id").val('');
        tr.find("td .other_id").val('');

        tr.find("td .class").val('');
        tr.find("td .btnRemoveGL").show();
        tr.find("td .btnAddGL").hide();

        tr.find("td .chart_account_id").attr("id", "chart_account_id"+indexRowJournalSuper);
        tr.find("td .debit").attr("id", "debit_"+indexRowJournalSuper);
        tr.find("td .credit").attr("id", "credit_"+indexRowJournalSuper);
        tr.find("td .memo").attr("id", "memo_"+indexRowJournalSuper);
        tr.find("td .choice").attr("id", "choice_"+indexRowJournalSuper);
        tr.find("td .customer_name").attr("id", "customer_name_"+indexRowJournalSuper);
        tr.find("td .vendor_name").attr("id", "vendor_name_"+indexRowJournalSuper);
        tr.find("td .employee_name").attr("id", "employee_name_"+indexRowJournalSuper);
        tr.find("td .other_name").attr("id", "other_name_"+indexRowJournalSuper);
        tr.find("td .class_id").attr("id", "class_id_"+indexRowJournalSuper);
        $("#tblGL").append(tr);
        var LenTr = parseInt($(".tblJournalSuperRow").length);
        if(LenTr == 1){
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").hide();
        }else{
            $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
        }
        eventKeyJournalSuper();
        tr.find("td .chart_account_id").focus();
        
    }
    
    function eventKeyJournalSuper(){
        $(".chart_account_id, .debit, .credit, .memo, .choice, .deleteName, .btnAddGL, .btnRemoveGL").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 2, aSep: ',', mNum: 15});
        $(".chart_account_id").chosen();
        $('.chart_account_id').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").find(".debit").focus().select();
                return false;
            }
        });
        $('.debit').keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).blur();
                $(this).closest("tr").find(".credit").focus().select();
                return false;
            }
        });
        $('.credit').keypress(function(e){
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
                $(this).closest("tr").next().find(".company_id").focus().select();
                return false;
            }
        });
        
        $(".chart_account_id").change(function(){
            // check if its type Accounts Receivable or Accounts Payable
            if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Receivable"){
                $(this).closest("tr").find("td .choice").attr("class","choice validate[required,funcCall[checkCustomerSuper]]");
                $(this).closest("tr").find("td .customer_id").attr("class","customer_id validate[required,funcCall[checkCustomerSuper]]");
                $(this).closest("tr").find("td .vendor_id").attr("class","vendor_id validate[required,funcCall[checkCustomerSuper]]");
                $(this).closest("tr").find("td .employee_id").attr("class","employee_id validate[required,funcCall[checkCustomerSuper]]");
                $(this).closest("tr").find("td .other_id").attr("class","other_id validate[required,funcCall[checkCustomerSuper]]");
            }else if($(this).find("option:selected").attr("chart_account_type_name")=="Accounts Payable"){
                $(this).closest("tr").find("td .choice").attr("class","choice validate[required,funcCall[checkVendorSuper]]");
                $(this).closest("tr").find("td .customer_id").attr("class","customer_id validate[required,funcCall[checkVendorSuper]]");
                $(this).closest("tr").find("td .vendor_id").attr("class","vendor_id validate[required,funcCall[checkVendorSuper]]");
                $(this).closest("tr").find("td .employee_id").attr("class","employee_id validate[required,funcCall[checkVendorSuper]]");
                $(this).closest("tr").find("td .other_id").attr("class","other_id validate[required,funcCall[checkVendorSuper]]");
            }else{
                $(this).closest("tr").find("td .choice").attr("class","choice");
                $(this).closest("tr").find("td .customer_id").attr("class","customer_id validate[required]");
                $(this).closest("tr").find("td .vendor_id").attr("class","vendor_id validate[required]");
                $(this).closest("tr").find("td .employee_id").attr("class","employee_id validate[required]");
                $(this).closest("tr").find("td .other_id").attr("class","other_id validate[required]");
            }
            $(this).closest("tr").find(".debit").focus().select();
        });
        $(".debit").keyup(function(){
            if($(this).val()!=0){
                $(this).closest("tr").find("td .credit").val(0);
            }
            calcTotalAmountSuper();
        });
        $(".credit").keyup(function(){
            if($(this).val()!=0){
                $(this).closest("tr").find("td .debit").val(0);
            }
            calcTotalAmountSuper();
        });
        $(".debit").blur(function(){
            if($.trim($(this).val())!=''){
                if(Number(replaceNum($(this).val())) < 0){
                    $(this).closest("tr").find(".credit").val(Number(replaceNum($(this).val())));
                    $(this).val(0);
                }
                calcTotalAmountSuper();
            }
        });
        $(".credit").blur(function(){
            if($.trim($(this).val())!=''){
                if(Number(replaceNum($(this).val())) < 0){
                    $(this).closest("tr").find(".debit").val(Number(replaceNum($(this).val())));
                    $(this).val(0);
                }
                calcTotalAmountSuper();
            }
        });
        $(".choice").change(function(){
            var companyId = $(this).closest("tr").find("td .company_id").val();
            if(companyId != ''){
                if($(this).val()=="Customer"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".customer_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchCustomerSuper($(this));
                    });
                }else if($(this).val()=="Vendor"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".vendor_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchVendorSuper($(this));
                    });
                }else if($(this).val()=="Employee"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".employee_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchEmployeeSuper($(this));
                    });
                }else if($(this).val()=="Other"){
                    $(this).hide("slide", { direction: "left" }, 500, function() {
                        $(this).closest("tr").find(".other_name").show();
                        $(this).closest("tr").find(".deleteName").show();
                        searchOtherSuper($(this));
                    });
                }
            }else{
                $(this).find("option[value='']").attr('selected','selected');
                alertSelectCompanyJournalSuper();
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
            cloneTblJournalSuper();
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
                        var lenTr = parseInt($(".tblJournalSuperRow").length);
                        if(lenTr == 1){
                            $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnRemoveGL").hide();
                        }
                        $("#tblGL").find("tr:eq("+lenTr+")").find("td .btnAddGL").show();
                        $(this).dialog("close");
                        calcTotalAmountSuper();
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        // Event Move Up Down
        moveRowGLSuper();
    }
    
    function searchCustomerSuper(obj){
        var companyId = obj.closest("tr").find(".company_id").val();
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
    function searchVendorSuper(obj){
        var companyId = obj.closest("tr").find(".company_id").val();
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
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    function searchEmployeeSuper(obj){
        var companyId = obj.closest("tr").find(".company_id").val();
        if(companyId != '' && $("#GeneralLedgerDate").val() != ''){
            $("#GeneralLedgerDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var orderDate = $("#GeneralLedgerDate").val();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId+"/"+orderDate,
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
    
    function searchOtherSuper(obj){
        var companyId = obj.closest("tr").find(".company_id").val();
        if(companyId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/other/"+companyId,
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
    function checkCustomerSuper(field, rules, i, options){
        if(field.closest("tr").find(".choice").val()!="Customer" && field.closest("tr").find(".choice").val()!="Employee"){
            return "* Please Select Customer or Employee";
        }
    }
    function checkVendorSuper(field, rules, i, options){
        if(field.closest("tr").find(".choice").val()!="Vendor"){
            return "* Please Select Vendor";
        }
    }
    function calcTotalAmountSuper(){
        var totalDebit=0;
        $(".debit").each(function(){
            totalDebit+=Number(replaceNum($(this).val()));
        });
        $("#totalDebit").text(totalDebit).formatCurrency({colorize:true});
        var totalCredit=0;
        $(".credit").each(function(){
            totalCredit+=Number(replaceNum($(this).val()));
        });
        $("#totalCredit").text(totalCredit).formatCurrency({colorize:true});
    }
    
    function alertSelectCompanyJournalSuper(){
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
    
    function moveRowGLSuper(){
        $(".btnMoveDownGL, .btnMoveUpGL").unbind('click');
        $(".btnMoveDownGL").click(function () {
            var rowToMove = $(this).parents('tr.tblJournalSuperRow:first');
            var next = rowToMove.next('tr.tblJournalSuperRow');
            if (next.length == 1) { next.after(rowToMove); }
            $("#tblGL").find("tr").find(".btnAddGL").hide();
            var LenTr = parseInt($(".tblJournalSuperRow").length);
            if(LenTr == 1){
                $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").hide();
            }else{
                $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
            }
        });

        $(".btnMoveUpGL").click(function () {
            var rowToMove = $(this).parents('tr.tblJournalSuperRow:first');
            var prev = rowToMove.prev('tr.tblJournalSuperRow');
            if (prev.length == 1) { prev.before(rowToMove); }
            $("#tblGL").find("tr").find(".btnAddGL").hide();
            var LenTr = parseInt($(".tblJournalSuperRow").length);
            if(LenTr == 1){
                $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").hide();
            }else{
                $("#tblGL").find("tr:eq("+LenTr+")").find(".btnAddGL").show();
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackJournalSuperEntry">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('GeneralLedger'); ?>
<input type="hidden" id="GeneralLedgerAddAllConfirmSave" value="0" />
<fieldset>
    <legend><?php __(MENU_JOURNAL_ENTRY_MANAGEMENT_INFO); ?></legend>
    <div class="inputContainer" style="float: right;">
        <table>
            <tr>
                <td><label for="GeneralLedgerCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php 
                        if(count($companies) != 1){
                            $empty = INPUT_SELECT;
                        } else {
                            $empty = false;
                        }
                        echo $this->Form->input('company_id', array('empty' => $empty, 'label' => false, 'id' => 'GeneralLedgerCompanyId', 'class' => 'validate[required]')); 
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="GeneralLedgerBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <select name="data[GeneralLedger][branch_id]" id="GeneralLedgerBranchId" class="validate[required]">
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
        </table>
    </div>
    <table>
        <tr>
            <td><label for="GeneralLedgerDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'readonly' => 'readonly')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="GeneralLedgerReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('reference', array('class'=>'validate[required]')); ?>
                    <img alt="" src="<?php echo $this->webroot . 'img/button/cycle.png'; ?>" id="btnSmartCodeJournalSuperEntry" style="cursor: pointer;" onmouseover="Tip('Smart Code')" />
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="GeneralLedgerIsAdj"><?php echo GENERAL_ADJUSTING_ENTRY; ?>:</label></td>
            <td><?php echo $this->Form->checkbox('is_adj'); ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="GeneralLedgerNote"><?php echo TABLE_NOTE; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('note', array('label' => false)); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<table id="tblGL" class="table" cellspacing="0">
    <tr>
        <th class="first"><?php echo TABLE_ACCOUNT; ?></th>
        <th style="width: 7%;"><?php echo GENERAL_DEBIT; ?> ($)</th>
        <th style="width: 7%;"><?php echo GENERAL_CREDIT; ?> ($)</th>
        <th style="width: 20%;"><?php echo TABLE_MEMO; ?></th>
        <th style="width: 15%;"><?php echo TABLE_NAME; ?></th>
        <th style="width: 15%;"><?php echo TABLE_CLASS; ?></th>
        <th style="width: 10%;"></th>
    </tr>
    
    <tr id="tblJournalSuperRow" class="tblJournalSuperRow" style="visibility: hidden;">
        <td class="first">
            <div class="inputContainer">
                <select id="chart_account_id" name="chart_account_id[]" class="chart_account_id validate[required]" style="width: 300px;">
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
                <input type="text" id="debit" name="debit[]" class="debit validate[required] float" style="height: 35px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer">
                <input type="text" id="credit" name="credit[]" class="credit validate[required] float" style="height: 35px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <input type="text" id="memo" name="memo[]" class="memo" style="width: 90%; height: 35px;" />
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('choice', array('empty' => INPUT_SELECT,'options'=>array('Customer' => 'Customer', 'Vendor' => 'Vendor', 'Employee' => 'Employee', 'Other' => 'Other'), 'id' => 'choice', 'name' => 'choice[]',  'class' => 'choice', 'label' => false)); ?>
                <input type="hidden" name="vendor_id[]" class="vendor_id" />
                <?php echo $this->Form->text('vendor_name', array('id' => 'vendor_name', 'class' => 'vendor_name validate[required]', 'style' => 'display: none;width: 70%;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="customer_id[]" class="customer_id" />
                <?php echo $this->Form->text('customer_name', array('id' => 'customer_name', 'class' => 'customer_name validate[required]', 'style' => 'display: none;width: 70%;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="employee_id[]" class="employee_id" />
                <?php echo $this->Form->text('employee_name', array('id' => 'employee_name', 'class' => 'employee_name validate[required]', 'style' => 'display: none;width: 70%;', 'readonly' => true, 'label' => false)); ?>
                <input type="hidden" name="other_id[]" class="other_id" />
                <?php echo $this->Form->text('other_name', array('id' => 'other_name', 'class' => 'other_name validate[required]', 'style' => 'display: none;width: 70%;', 'readonly' => true, 'label' => false)); ?>
                <img alt="Delete" align="absmiddle" class="deleteName" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" style="display: none;" />
            </div>
        </td>
        <td style="white-space: nowrap;">
            <div class="inputContainer" style="width: 100%;">
                <select id="class_id" name="class_id[]" class="class_id">
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
            <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpGL" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
            &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownGL" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddGL" style="cursor: pointer;" onmouseover="Tip('Add New')" />
            &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveGL" style="cursor: pointer;" onmouseover="Tip('Remove')" />
        </td>
    </tr>
    
</table>
<table class="table" cellspacing="0" style="width: 400px;">
    <tr>
        <th class="first" style="width: 50%;">Total Debit ($)</th>
        <th>Total Credit ($)</th>
    </tr>
    <tr>
        <td class="first" id="totalDebit">0</td>
        <td id="totalCredit">0</td>
    </tr>
</table>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>