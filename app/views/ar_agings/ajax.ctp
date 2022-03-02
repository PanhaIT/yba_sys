<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Disabed Submit Button
        $(".btnSaveArAging").removeAttr('disabled');
        
        $("#ArAgingForm").validationEngine('detach');
        $("#ArAgingForm").validationEngine('attach');
        // Check Bf Save
        $(".btnSaveArAging").unbind("click");
        $(".btnSaveArAging").click(function(){
            if(checkBfSaveReceivePayment() == true){
                return true;
            }else{
                confirmCheckPaidReceivePayment();
                return false;
            }
        });
        $("#ArAgingForm").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
        $("#ArAgingForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#ArAgingDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".ArAgingDueDate").datepicker("option", "dateFormat", "yy-mm-dd");
                
                if($("#totalPayArAging").text() == "0" || $("#totalPayArAging").text() == "0.00"){
                    
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveArAging").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/RPJ",
                    beforeSend: function(){

                    },
                    success: function(result){
                        $("#ArAgingReference").val(result);
                    }
                });
                $("#ArAgingDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveArAging").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("button[type=submit]", $("#ArAgingForm")).removeAttr('disabled');
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
                    $("#ArAgingReference").val("");
                    $("#ArAgingNote").val("");
                    loadTableArAging();
                    if(parseFloat(result) > 0){
                        createSysAct('Receive Payment Journal (Customer)', 'Add', 1, '');
                        // alert message
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printOR"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button>');
                        $(".printOR").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/'; ?>ar_agings/printInvoice/"+result,
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
                        createSysAct('Receive Payment Journal (Customer)', 'Add', 2, result);
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
        $(".ArAgingAmountUs, .ArAgingDiscountUs").focus(function(){
            if($(this).val()==0){
                $(this).val("");
            }
        });
        $(".ArAgingAmountUs, .ArAgingDiscountUs").keyup(function(){
            calcArAging();
        });
        $(".ArAgingAmountUs, .ArAgingDiscountUs").blur(function(){
            calcArAging();
            if($(this).val()>0){
                $(this).closest("tr").find(".ar_aging_is_paid").attr('checked','checked');
            }else{
                $(this).closest("tr").find(".ar_aging_is_paid").removeAttr('checked');
            }
        });
        $(".ar_aging_is_paid").change(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".ArAgingAmountUs").val(Number($(this).closest("tr").find(".txtAmountPaid").text()));
            }else{
                $(this).closest("tr").find(".ArAgingAmountUs").val(0);
            }
            calcArAging();
        });
        // prevent enter key
        $(".ArAgingAmountUs, .ArAgingDiscountUs").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ArAgingBalance, .ArAgingDiscountUs").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".ArAgingMemo").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
    });
    function calcArAging(){
        var total=0;
        var totalDiscount=0
        $(".ArAgingAmountUs").delay(10).each(function(){
            total+=Number($(this).val());
            totalDiscount+=Number($(".ArAgingDiscountUs").val());
            var amountPaid=Number($(this).closest("tr").find(".txtAmountPaid").text());
            var discount=Number($(this).closest("tr").find(".ArAgingDiscountUs").val());
            var paid=Number($(this).val()) + Number(discount);
            if(amountPaid>=paid){
                var balance=amountPaid-paid;
                $(this).closest("tr").find(".ArAgingBalance").val((Math.ceil(balance*10000000000000000)/10000000000000000).toFixed(2));
            }else{
                $(this).val((amountPaid).toFixed(2));
                $(this).closest("tr").find(".ArAgingDiscountUs").val(0);
                $(this).closest("tr").find(".ArAgingBalance").val(0);
            }
        });
        $("#totalPayArAging").text((Math.ceil(total*10000000000000000)/10000000000000000).toFixed(2));
        $("#totalDiscountUsArAging").text((Math.ceil(totalDiscount*10000000000000000)/10000000000000000).toFixed(2));
    }
    function confirmCheckPaidReceivePayment(){
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
    function checkBfSaveReceivePayment(){
        var formName     = "#CustomerPaymentForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".ArAgingAmountUs").val() == undefined){
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
            <th><?php echo TABLE_CUSTOMER; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo TABLE_AMOUNT_DUE; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo GENERAL_PAID; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo GENERAL_DISCOUNT; ?> ($)</th>
            <th style="width: 100px !important;"><?php echo GENERAL_BALANCE; ?> ($)</th>
            <th style="width: 250px !important;"><?php echo TABLE_MEMO; ?></th>
            <th style="width: 50px !important;"></th>
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
            $tableName = "general_ledger_detail_ar" . $user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `main_gl_id` int(11) DEFAULT NULL,
                              `chart_account_id` int(11) DEFAULT NULL,
                              `company_id` int(11) DEFAULT NULL,
                              `credit` double DEFAULT NULL,
                              `customer_id` bigint(20) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `main_gl_id` (`main_gl_id`),
                              KEY `chart_account_id` (`chart_account_id`),
                              KEY `company_id` (`company_id`),
                              KEY `customer_id` (`customer_id`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableName") or die(mysql_error());
            // Insert SUM(Credit)
            $queryCoa = mysql_query("SELECT IFNULL(SUM(credit),0) AS credit, `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `customer_id`
                                     FROM general_ledgers gl2 INNER JOIN general_ledger_details gld2 ON gl2.id=gld2.general_ledger_id
                                     WHERE gl2.is_approve=1 AND gl2.is_active=1
                                        AND company_id=" . $companyId . " AND branch_id = " .$branchId."
                                        ".($cGroupId!=0?' AND customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id='.$cGroupId.')':'')."
                                        ".($customerId!=0?' AND customer_id='.$customerId:'')."
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND sales_order_id IS NULL
                                        AND credit_memo_id IS NULL
                                        AND purchase_order_id IS NULL
                                        AND purchase_return_id IS NULL
                                        AND credit>0
                                     GROUP BY `main_gl_id`, `chart_account_id`, `company_id`, `branch_id`, `customer_id`") or die(mysql_error());
            while ($dataCoa = mysql_fetch_array($queryCoa)) {
                mysql_query("INSERT INTO ".$tableName." (
                                        main_gl_id,
                                        chart_account_id,
                                        company_id,
                                        credit,
                                        customer_id
                                    ) VALUES (
                                        " . (!is_null($dataCoa['main_gl_id']) ? $dataCoa['main_gl_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                                        " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                                        '" . $dataCoa['credit'] . "',
                                        " . (!is_null($dataCoa['customer_id']) ? $dataCoa['customer_id'] : "NULL") . "
                                    )") or die(mysql_error());
            }
            // Query AR Aging
            $query=mysql_query("SELECT gld.id,
                                    DATE_FORMAT(date,'%d/%m/%Y') AS date, reference,
                                    CONCAT_WS(' ', cus.customer_code, '-', cus.name) AS customer_name,
                                    company_id,
                                    customer_id,
                                    chart_account_id,
                                    debit,
                                    IFNULL((SELECT IFNULL(SUM(credit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND customer_id = gld.customer_id), 0) AS amount_paid
                                FROM general_ledgers gl 
                                INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                INNER JOIN customers AS cus ON cus.id = gld.customer_id
                                WHERE gl.is_approve=1 AND gl.is_active=1
                                    AND gld.company_id=" . $companyId . "
                                    ".($cGroupId!=0?' AND gld.customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id='.$cGroupId.')':'')."
                                    ".($customerId!=0?' AND gld.customer_id='.$customerId:'')."
                                    AND gld.chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND gl.sales_order_id IS NULL
                                    AND gl.credit_memo_id IS NULL
                                    AND gl.purchase_order_id IS NULL
                                    AND gl.purchase_return_id IS NULL
                                    AND gld.debit>0
                                    AND gld.debit-IFNULL((SELECT IFNULL(SUM(credit),0) FROM ".$tableName." WHERE main_gl_id=gld.main_gl_id AND chart_account_id=gld.chart_account_id AND company_id = gld.company_id AND customer_id = gld.customer_id), 0)>0.001
                                ORDER BY gl.date");
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
                <input type="hidden" name="customer_id_ajax[]" value="<?php echo $data['customer_id']; ?>" />
                <input type="hidden" name="ar_id[]" value="<?php echo $data['chart_account_id']; ?>" />
            </td>
            <td><?php echo $data['date']; ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td><?php echo $data['customer_name']; ?></td>
            <td><?php echo number_format($data['debit'],2,".",""); ?></td>
            <td class="txtAmountPaid">
                <input type="hidden" name="amount_due[]" value="<?php echo number_format($amountDue,2,'.',''); ?>" />
                <?php echo number_format($amountDue,2,'.',''); ?>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="ArAgingAmountUs<?php echo $rnd; ?>" name="amount_us[]" class="ArAgingAmountUs validate[required,custom[number]]" value="0" style="width: 120px;" />
                </div>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="ArAgingDiscountUs<?php echo $rnd; ?>" name="discount_us[]" class="ArAgingDiscountUs validate[required,custom[number]]" value="0" style="width: 120px;" />
                </div>
            </td>
            <td><input type="text" id="ArAgingBalance<?php echo $rnd; ?>" name="balance_us[]" class="ArAgingBalance" value="" style="width: 120px;" readonly="readonly" /></td>
            <td><input type="text" id="ArAgingMemo<?php echo $rnd; ?>" name="memo[]" class="ArAgingMemo" value="" style="width: 120px;" /></td>
            <td>
                <input type="checkbox" class="ar_aging_is_paid" />
            </td>
        </tr>
            <?php } ?>
        <tr>
            <td class="first" colspan="4" style="text-align: right;font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
            <td><?php echo number_format($totalAmount,2); ?></td>
            <td><?php echo number_format($balance,2); ?></td>
            <td id="totalPayArAging"></td>
            <td id="totalDiscountUsArAging"></td>
            <td id="totalBalanceArAging"></td>
            <td></td>
            <td></td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td colspan="11" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
        </tr>
        <?php }} ?>
    </tbody>
</table>