<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Disabed Submit Button
        $(".btnSaveArAgingEmployee").removeAttr('disabled');
        
        $("#ArAgingEmployeeForm").validationEngine('detach');
        $("#ArAgingEmployeeForm").validationEngine('attach');    
        // Check Bf Save
        $(".btnSaveArAgingEmployee").unbind("click");
        $(".btnSaveArAgingEmployee").click(function(){
            if(checkBfSaveReceivePaymentEmployee() == true){
                return true;
            }else{
                confirmCheckPaidReceivePaymentEmployee();
                return false;
            }
        });
        $("#ArAgingEmployeeForm").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
        $("#ArAgingEmployeeForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#ArAgingEmployeeDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".ArAgingEmployeeDueDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveArAgingEmployee").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/RPEJ",
                    beforeSend: function(){

                    },
                    success: function(result){
                        $("#ArAgingEmployeeReference").val(result);
                    }
                });
                $("#ArAgingEmployeeDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveArAgingEmployee").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("button[type=submit]", $("#ArAgingEmployeeForm")).removeAttr('disabled');
                if(result=='duplicate'){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>This code is already taken, please change the code and save again.</p>');
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
                }else if(result=='error'){
                    // alert message
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
                    $("#ArAgingEmployeeReference").val("");
                    $("#ArAgingEmployeeNote").val("");
                    loadTableArAgingEmployee();
                    if(parseFloat(result) > 0){
                        createSysAct('Receive Payment Journal (Employee)', 'Add', 1, '');
                        // alert message
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printOREmployee"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button>');
                        $(".printOREmployee").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/'; ?>ar_aging_employees/printInvoice/"+result,
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
                    } else {
                        createSysAct('Receive Payment Journal (Employee)', 'Add', 2, result);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
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
            }
        });
        $(".ArAgingEmployeeAmountUs").focus(function(){
            if($(this).val()==0){
                $(this).val("");
            }
        });
        $(".ArAgingEmployeeAmountUs").keyup(function(){
            calcArAgingEmployee();
        });
        $(".ArAgingEmployeeAmountUs").blur(function(){
            calcArAgingEmployee();
            if($(this).val()>0){
                $(this).closest("tr").find(".ar_aging_is_paid").attr('checked','checked');
            }else{
                $(this).closest("tr").find(".ar_aging_is_paid").removeAttr('checked');
            }
        });
        $(".ar_aging_is_paid").change(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".ArAgingEmployeeAmountUs").val(Number($(this).closest("tr").find(".txtAmountPaid").text()));
            }else{
                $(this).closest("tr").find(".ArAgingEmployeeAmountUs").val(0);
            }
            calcArAgingEmployee();
        });
        // prevent enter key
        $(".ArAgingEmployeeAmountUs").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ArAgingEmployeeBalance").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ArAgingEmployeeMemo").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
    });
    function calcArAgingEmployee(){
        total=0;
        $(".ArAgingEmployeeAmountUs").delay(10).each(function(){
            total+=Number($(this).val());
            amountPaid=Number($(this).closest("tr").find(".txtAmountPaid").text());
            paid=Number($(this).val());
            if(amountPaid>paid){
                balance=amountPaid-paid;
                $(this).closest("tr").find(".ArAgingEmployeeBalance").val((Math.ceil(balance*10000000000000000)/10000000000000000).toFixed(2));
            }else{
                $(this).val((amountPaid).toFixed(2));
                $(this).closest("tr").find(".ArAgingEmployeeBalance").val(0);
            }
        });
        $("#totalPayArAgingEmployee").text((Math.ceil(total*10000000000000000)/10000000000000000).toFixed(2));
    }
    function confirmCheckPaidReceivePaymentEmployee(){
        var question = "<?php echo MESSAGE_CONFIRM_PAID_BEFORE_SAVE; ?>";
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
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    function checkBfSaveReceivePaymentEmployee(){
        var formName     = "#CustomerPaymentForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".ArAgingEmployeeAmountUs").val() == undefined){
                return false;
            }else{
                var result = false;
                $(".ar_aging_is_paid").each(function(){
                    if($(this).is(':checked')){
                        result = true;
                    }
                });
                return result;
            }
        }
    }
</script>
<table class="table" cellspacing="0">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_REFERENCE; ?></th>
            <th><?php echo TABLE_EMPLOYEE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo TABLE_AMOUNT_DUE; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo GENERAL_PAID; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo GENERAL_BALANCE; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo TABLE_MEMO; ?></th>
            <th style="width: 100px !important;"><?php echo GENERAL_PAID; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $index=1;
        $totalAmount=0;
        $balance=0;
        $arrCoAIdList = array();
        $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
        while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
            $arrCoAIdList[]=$dataCoAIdList['id'];
        }
        if(sizeof($arrCoAIdList)!=0){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableName = "general_ledger_detail_ar_emp" . $user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `main_gl_id` int(11) DEFAULT NULL,
                              `chart_account_id` int(11) DEFAULT NULL,
                              `company_id` int(11) DEFAULT NULL,
                              `credit` double DEFAULT NULL,
                              `employee_id` bigint(20) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `main_gl_id` (`main_gl_id`),
                              KEY `chart_account_id` (`chart_account_id`),
                              KEY `company_id` (`company_id`),
                              KEY `employee_id` (`employee_id`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableName") or die(mysql_error());
            // Insert SUM(Credit)
            $queryCoa = mysql_query("SELECT IFNULL(SUM(credit),0) AS credit, `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `employee_id`
                                     FROM general_ledgers gl2 INNER JOIN general_ledger_details gld2 ON gl2.id=gld2.general_ledger_id
                                     WHERE gl2.is_approve=1 AND gl2.is_active=1
                                        AND company_id=" . $companyId . " AND branch_id = " .$branchId."
                                        ".($eGroupId!=0?' AND employee_id IN (SELECT employee_id FROM employee_egroups WHERE egroup_id='.$eGroupId.')':'')."
                                        ".($employeeId!=0?' AND employee_id='.$employeeId:'')."
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND sales_order_id IS NULL
                                        AND credit_memo_id IS NULL
                                        AND purchase_order_id IS NULL
                                        AND purchase_return_id IS NULL
                                        AND credit>0
                                     GROUP BY `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `employee_id`") or die(mysql_error());
            while ($dataCoa = mysql_fetch_array($queryCoa)) {
                mysql_query("INSERT INTO ".$tableName." (
                                        main_gl_id,
                                        chart_account_id,
                                        company_id,
                                        credit,
                                        employee_id
                                    ) VALUES (
                                        " . (!is_null($dataCoa['main_gl_id']) ? $dataCoa['main_gl_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                                        '" . $dataCoa['credit'] . "',
                                        " . (!is_null($dataCoa['employee_id']) ? $dataCoa['employee_id'] : "NULL") . "
                                    )") or die(mysql_error());
            }
            // Query AR Aging Employee
            $query=mysql_query("SELECT gld.id,
                                    DATE_FORMAT(date,'%d/%m/%Y') AS date, reference,
                                    (SELECT name FROM employees WHERE id=employee_id) AS employee_name,
                                    company_id,
                                    employee_id,
                                    chart_account_id,
                                    debit,
                                    IFNULL((SELECT IFNULL(SUM(credit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND employee_id = gld.employee_id), 0) AS amount_paid
                                FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_approve=1 AND is_active=1
                                    AND company_id=" . $companyId . " AND branch_id = " .$branchId."
                                    ".($eGroupId!=0?' AND employee_id IN (SELECT employee_id FROM employee_egroups WHERE egroup_id='.$eGroupId.')':'')."
                                    AND employee_id IS NOT NULL
                                    ".($employeeId!=0?' AND employee_id='.$employeeId:'')."
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND sales_order_id IS NULL
                                    AND credit_memo_id IS NULL
                                    AND purchase_order_id IS NULL
                                    AND purchase_return_id IS NULL
                                    AND debit>0
                                    AND debit-(IFNULL((SELECT IFNULL(SUM(credit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND employee_id = gld.employee_id), 0))>0.001
                                ORDER BY date");
            if(mysql_num_rows($query)){
                while($data=mysql_fetch_array($query)){
                    $rnd = rand();
                    $totalAmount+=$data['debit'];
                    $amountDue=$data['debit']-$data['amount_paid'];
                    $balance+=$amountDue;
        ?>
        <tr>
            <td class="first">
                <?php echo $index++; ?>
                <input type="hidden" name="id[]" value="<?php echo $data['id']; ?>" />
                <input type="hidden" name="company_id_ajax[]" value="<?php echo $data['company_id']; ?>" />
                <input type="hidden" name="branch_id_ajax[]" value="<?php echo $data['branch_id']; ?>" />
                <input type="hidden" name="employee_id_ajax[]" value="<?php echo $data['employee_id']; ?>" />
                <input type="hidden" name="ar_id[]" value="<?php echo $data['chart_account_id']; ?>" />
            </td>
            <td><?php echo $data['date']; ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td><?php echo $data['employee_name']; ?></td>
            <td><?php echo number_format($data['debit'],2,".",""); ?></td>
            <td class="txtAmountPaid">
                <input type="hidden" name="amount_due[]" value="<?php echo number_format($amountDue,2,'.',''); ?>" />
                <?php echo number_format($amountDue,2,'.',''); ?>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="ArAgingEmployeeAmountUs<?php echo $rnd; ?>" name="amount_us[]" class="ArAgingEmployeeAmountUs validate[required,custom[number]]" value="0" style="width: 120px;" />
                </div>
            </td>
            <td><input type="text" id="ArAgingEmployeeBalance<?php echo $rnd; ?>" name="balance_us[]" class="ArAgingEmployeeBalance" value="" style="width: 120px;" readonly="readonly" /></td>
            <td><input type="text" id="ArAgingEmployeeMemo<?php echo $rnd; ?>" name="memo[]" class="ArAgingEmployeeMemo" value="" style="width: 120px;" /></td>
            <td>
                <input type="checkbox" class="ar_aging_is_paid" />
            </td>
        </tr>
            <?php } ?>
        <tr>
            <td class="first" colspan="4" style="text-align: right;font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
            <td><?php echo number_format($totalAmount,2); ?></td>
            <td><?php echo number_format($balance,2); ?></td>
            <td id="totalPayArAgingEmployee"></td>
            <td id="totalBalanceArAgingEmployee"></td>
            <td></td>
            <td></td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td colspan="10" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
        </tr>
        <?php }} ?>
    </tbody>
</table>