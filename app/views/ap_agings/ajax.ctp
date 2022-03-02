<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Disabed Submit Button
        $(".btnSaveApAging").removeAttr('disabled');
        
        $("#ApAgingForm").validationEngine('detach');
        $("#ApAgingForm").validationEngine('attach');
        // Check Bf Save
        $(".btnSaveApAging").unbind("click");
        $(".btnSaveApAging").click(function(){
            if(checkBfSaveReceivePaymentAp() == true){
                return true;
            }else{
                confirmCheckPaidReceivePaymentAp();
                return false;
            }
        });
        $("#ApAgingForm").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
        $("#ApAgingForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#ApAgingDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".ApAgingDueDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveApAging").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/PABJ",
                    beforeSend: function(){

                    },
                    success: function(result){
                        $("#ApAgingReference").val(result);
                    }
                });
                $("#ApAgingDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveApAging").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("button[type=submit]", $("#ApAgingForm")).removeAttr('disabled');
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
                    $("#ApAgingReference").val("");
                    $("#ApAgingNote").val("");
                    loadTableApAging();
                    if(parseFloat(result) > 0){
                        createSysAct('Pay Bill Journal', 'Add', 1, '');
                        // alert message
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printPV"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button>');
                        $(".printPV").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/'; ?>ap_agings/printInvoice/"+result,
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
                        createSysAct('Pay Bill Journal', 'Add', 2, result);
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
        $(".ApAgingAmountUs").focus(function(){
            if($(this).val()==0){
                $(this).val("");
            }
        });
        $(".ApAgingAmountUs").keyup(function(){
            calcApAging();
        });
        $(".ApAgingAmountUs").blur(function(){
            calcApAging();
            if($(this).val()>0){
                $(this).closest("tr").find(".ap_aging_is_paid").attr('checked','checked');
            }else{
                $(this).closest("tr").find(".ap_aging_is_paid").removeAttr('checked');
            }
        });
        $(".ap_aging_is_paid").change(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".ApAgingAmountUs").val(Number($(this).closest("tr").find(".txtAmountPaid").text()));
            }else{
                $(this).closest("tr").find(".ApAgingAmountUs").val(0);
            }
            calcApAging();
        });
        // prevent enter key
        $(".ApAgingAmountUs").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ApAgingBalance").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ApAgingMemo").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
    });
    function calcApAging(){
        total=0;
        $(".ApAgingAmountUs").delay(10).each(function(){
            total+=Number($(this).val());
            amountPaid=Number($(this).closest("tr").find(".txtAmountPaid").text());
            paid=Number($(this).val());
            if(amountPaid>paid){
                balance=amountPaid-paid;
                $(this).closest("tr").find(".ApAgingBalance").val((Math.ceil(balance*10000000000000000)/10000000000000000).toFixed(2));
            }else{
                $(this).val((amountPaid).toFixed(2));
                $(this).closest("tr").find(".ApAgingBalance").val(0);
            }
        });
        $("#totalPayApAging").text((Math.ceil(total*10000000000000000)/10000000000000000).toFixed(2));
    }
    function confirmCheckPaidReceivePaymentAp(){
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
    function checkBfSaveReceivePaymentAp(){
        var formName     = "#CustomerPaymentForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".ApAgingAmountUs").val() == undefined){
                return false;
            }else{
                var result = false;
                $(".ap_aging_is_paid").each(function(){
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
            <th><?php echo TABLE_VENDOR; ?></th>
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
        $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Payable')");
        while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
            $arrCoAIdList[]=$dataCoAIdList['id'];
        }
        if(sizeof($arrCoAIdList)!=0){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableName = "general_ledger_detail_ap" . $user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `main_gl_id` int(11) DEFAULT NULL,
                              `chart_account_id` int(11) DEFAULT NULL,
                              `company_id` int(11) DEFAULT NULL,
                              `debit` double DEFAULT NULL,
                              `vendor_id` bigint(20) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `main_gl_id` (`main_gl_id`),
                              KEY `chart_account_id` (`chart_account_id`),
                              KEY `company_id` (`company_id`),
                              KEY `vendor_id` (`vendor_id`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableName") or die(mysql_error());
            // Insert SUM(Credit)
            $queryCoa = mysql_query("SELECT IFNULL(SUM(debit),0) AS debit, `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `vendor_id`
                                     FROM general_ledgers gl2 INNER JOIN general_ledger_details gld2 ON gl2.id=gld2.general_ledger_id
                                     WHERE gl2.is_approve=1 AND gl2.is_active=1
                                        AND company_id=" . $companyId . " AND branch_id = " .$branchId."
                                        ".($vendorId!=0?' AND vendor_id='.$vendorId:'')."
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND sales_order_id IS NULL
                                        AND credit_memo_id IS NULL
                                        AND purchase_order_id IS NULL
                                        AND purchase_return_id IS NULL
                                        AND debit>0
                                     GROUP BY `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `vendor_id`") or die(mysql_error());
            while ($dataCoa = mysql_fetch_array($queryCoa)) {
                mysql_query("INSERT INTO ".$tableName." (
                                        main_gl_id,
                                        chart_account_id,
                                        company_id,
                                        debit,
                                        vendor_id
                                    ) VALUES (
                                        " . (!is_null($dataCoa['main_gl_id']) ? $dataCoa['main_gl_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                                        '" . $dataCoa['debit'] . "',
                                        " . (!is_null($dataCoa['vendor_id']) ? $dataCoa['vendor_id'] : "NULL") . "
                                    )") or die(mysql_error());
            }
            // Query AP Aging
            $query=mysql_query("SELECT gld.id,
                                    DATE_FORMAT(date,'%d/%m/%Y') AS date, reference,
                                    (SELECT name FROM vendors WHERE id=vendor_id) AS vendor_name,
                                    company_id,
                                    vendor_id,
                                    chart_account_id,
                                    credit,
                                    IFNULL((SELECT IFNULL(SUM(debit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND vendor_id = gld.vendor_id), 0) AS amount_paid
                                FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_approve=1 AND is_active=1
                                    AND company_id=" . $companyId . " AND branch_id = " .$branchId."
                                    ".($vendorId!=0?' AND vendor_id='.$vendorId:'')."
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND sales_order_id IS NULL
                                    AND credit_memo_id IS NULL
                                    AND purchase_order_id IS NULL
                                    AND purchase_return_id IS NULL
                                    AND credit>0
                                    AND credit-(IFNULL((SELECT IFNULL(SUM(debit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND vendor_id = gld.vendor_id), 0))>0.001
                                ORDER BY date");
            if(mysql_num_rows($query)){
                while($data=mysql_fetch_array($query)){
                    $rnd = rand();
                    $totalAmount+=$data['credit'];
                    $amountDue=$data['credit']-$data['amount_paid'];
                    $balance+=$amountDue;
        ?>
        <tr>
            <td class="first">
                <?php echo $index++; ?>
                <input type="hidden" name="id[]" value="<?php echo $data['id']; ?>" />
                <input type="hidden" name="company_id_ajax[]" value="<?php echo $data['company_id']; ?>" />
                <input type="hidden" name="branch_id_ajax[]" value="<?php echo $data['branch_id']; ?>" />
                <input type="hidden" name="vendor_id_ajax[]" value="<?php echo $data['vendor_id']; ?>" />
                <input type="hidden" name="ap_id[]" value="<?php echo $data['chart_account_id']; ?>" />
            </td>
            <td><?php echo $data['date']; ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td><?php echo $data['vendor_name']; ?></td>
            <td><?php echo number_format($data['credit'],2,".",""); ?></td>
            <td class="txtAmountPaid">
                <input type="hidden" name="amount_due[]" value="<?php echo number_format($amountDue,2,'.',''); ?>" />
                <?php echo number_format($amountDue,2,'.',''); ?>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="ApAgingAmountUs<?php echo $rnd; ?>" name="amount_us[]" class="ApAgingAmountUs validate[required,custom[number]]" value="0" style="width: 120px;" />
                </div>
            </td>
            <td><input type="text" id="ApAgingBalance<?php echo $rnd; ?>" name="balance_us[]" class="ApAgingBalance" value="" style="width: 120px;" readonly="readonly" /></td>
            <td><input type="text" id="ApAgingMemo<?php echo $rnd; ?>" name="memo[]" class="ApAgingMemo" value="" style="width: 120px;" /></td>
            <td>
                <input type="checkbox" class="ap_aging_is_paid" />
            </td>
        </tr>
            <?php } ?>
        <tr>
            <td class="first" colspan="4" style="text-align: right;font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
            <td><?php echo number_format($totalAmount,2); ?></td>
            <td><?php echo number_format($balance,2); ?></td>
            <td id="totalPayApAging"></td>
            <td id="totalBalanceApAging"></td>
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