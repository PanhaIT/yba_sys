<?php 
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

include("includes/function.php");
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Disabed Submit Button
        $(".btnSavePayBill").removeAttr('disabled');
        // Form Validation
        $("#PayBillForm").validationEngine('detach');
        $("#PayBillForm").validationEngine('attach');
        $(".float").autoNumeric({mDec: <?php echo $rowOption[0] ?>, aSep: ','});
        // Check Bf Save
        $(".btnSavePayBill").unbind("click");
        $(".btnSavePayBill").click(function(){
            if(checkBfSavePayBill() == true){
                return true;
            }else{
                confirmCheckPaidPayBill();
                return false;
            }
        });
        $("#PayBillForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#PayBillDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".PayBillDueDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePayBill").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $("#PayBillDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $("#PayBillReference").val("");
                $("#PayBillNote").val("");
                $(".txtSavePayBill").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                loadTablePayBill();
                $("#PayBillBranchId").change();
                $("button[type=submit]", $("#PayBillForm")).removeAttr('disabled');
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Pay Bill', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Pay Bill', 'Add', 1, '');
                    // alert message
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
        
        $(".PayBillDueDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            minDate: 0
        }).unbind("blur");
        
        $(".PayBillAmountUs, .PayBillAmountOther, .PayBillDiscountUs, .PayBillDiscountOther").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val("");
            }
        });
        
        $(".PayBillAmountUs, .PayBillDiscountUs").keyup(function(){
            if($(this).attr("class") == "PayBillDiscountUs"){
                $(this).closest("tr").find(".pay_bill_is_paid").removeAttr('checked');
                $(".PayBillAmountUs, .PayBillAmountOther").val(0);
                // Check Discount Amount
                var amountPaid    = replaceNum($(this).closest("tr").find(".txtAmountPaidPayBill").text());
                var DiscountAmt   = replaceNum($(this).val());
                var DiscountOther = convertToMainCurrency(replaceNum($(this).closest("tr").find(".PayBillDiscountOther").val()));
                if(amountPaid < (DiscountAmt + DiscountOther)){
                    var disBalance = amountPaid - DiscountOther;
                    $(this).val(disBalance);
                }
            }
            calcPayBill();
        });
        
        $(".PayBillAmountOther, .PayBillDiscountOther").keyup(function(){
            if($("#exchangeRatePayBill").find("option:selected").val() != ''){
                if($(this).attr("class") == "PayBillDiscountOther"){
                    $(this).closest("tr").find(".pay_bill_is_paid").removeAttr('checked');
                    $(".PayBillAmountUs, .PayBillAmountOther").val(0);
                    // Check Discount Amount
                    var amountPaid    = replaceNum($(this).closest("tr").find(".txtAmountPaidPayBill").text());
                    var DiscountAmt   = replaceNum($(this).closest("tr").find(".PayBillDiscountUs").val());
                    var DiscountOther = convertToMainCurrency(replaceNum($(this).val()));
                    if(amountPaid < (DiscountAmt + DiscountOther)){
                        var disBalance = convertToOtherCurrency(amountPaid - DiscountAmt);
                        $(this).val(disBalance);
                    }
                }
                calcPayBill();
            } else {
                $(this).val("");
            }
        });
        
        $(".PayBillAmountUs, .PayBillAmountOther, .PayBillDiscountUs, .PayBillDiscountOther").blur(function(){
            if($(this).closest("tr").find(".PayBillAmountUs").val() > 0 || $(this).closest("tr").find(".PayBillAmountOther").val() > 0){
                $(this).closest("tr").find(".pay_bill_is_paid").attr('checked','checked');
            } else {
                $(this).closest("tr").find(".pay_bill_is_paid").removeAttr('checked');
            }
            if($(this).val() == ''){
                $(this).val('0');
            }
            calcPayBill();
        });
        
        $(".pay_bill_is_paid").change(function(){
            if($(this).is(':checked')){
                var totalBalance = replaceNum($(this).closest("tr").find(".txtAmountPaidPayBill").text()) - replaceNum($(this).closest("tr").find(".PayBillDiscountUs").val());
                $(this).closest("tr").find(".PayBillAmountUs").val(totalBalance);
            } else {
                $(this).closest("tr").find(".PayBillAmountUs").val(0);
            }
            calcPayBill();
        });
        
        // prevent enter key
        $(".PayBillAmountUs").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".PayBillBalance").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".PayBillDueDate").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        
        // Get Reference Code
        var mCode = $("#PayBillBranchId").find("option:selected").attr("mcode");
        $("#PayBillReference").val("<?php echo date("y"); ?>"+mCode);
        // Currency Payment Select
        $("#exchangeRatePayBill").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#PayBillAmountOther").removeAttr("readonly");
                $("#PayBillDiscountOther").removeAttr("readonly");
            } else {
                $("#PayBillAmountOther").val(0);
                $("#PayBillAmountOther").attr("readonly", true);
                $("#PayBillDiscountOther").val(0);
                $("#PayBillDiscountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbolPayBill").html(symbol);
            $("#exchangeRateIdPayBill").val(exRateId);  
            $(".PayBillBalance").each(function(){
                if($("#exchangeRatePayBill").find("option:selected").val() != ''){
                    var balance = replaceNum($(this).val());
                    var balanceOther = convertToOtherCurrency(balance);
                    $(this).closest("tr").find(".PayBillBalanceOther").val(converDicemalJS(balanceOther).toFixed(<?php echo $rowOption[0]; ?>));
                } else {
                    $(this).closest("tr").find(".PayBillBalanceOther").val(0);
                }
            });
        });
    });
    
    function confirmCheckPaidPayBill(){
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
    
    function calcPayBill(){        
        var totalPaid     = 0;  
        var totalPayUs    = 0;
        var totalPayOther = 0;
        var totalDisUs    = 0;
        var totalDisOther = 0;
        var totalBalance  = 0;
        var totalBalanceOther = 0;
        $(".PayBillAmountUs").delay(10).each(function(){
            var amountPaid    = replaceNum($(this).closest("tr").find(".txtAmountPaidPayBill").text());
            var paid          = replaceNum($(this).val());
            var amountOther   = replaceNum($(this).closest("tr").find(".PayBillAmountOther").val());
            var Discount      = replaceNum($(this).closest("tr").find(".PayBillDiscountUs").val());
            var DiscountOther = replaceNum($(this).closest("tr").find(".PayBillDiscountOther").val());
            var balance       = 0;
            var balanceOther  = 0;
            totalPaid         = converDicemalJS(paid + convertToMainCurrency(amountOther) + Discount + convertToMainCurrency(DiscountOther));     
            if(amountPaid > totalPaid){
                balance = converDicemalJS(amountPaid - totalPaid);
                if($("#exchangeRatePayBill").find("option:selected").val() != ''){
                    balanceOther = convertToOtherCurrency(balance);
                }
                $(this).closest("tr").find(".PayBillBalance").val(converDicemalJS(balance).toFixed(<?php echo $rowOption[0]; ?>));
                $(this).closest("tr").find(".PayBillBalanceOther").val(converDicemalJS(balanceOther).toFixed(<?php echo $rowOption[0]; ?>));
                $(this).closest("tr").find(".PayBillDueDate").show();
            } else {
                balance = converDicemalJS(amountPaid - (Discount + convertToMainCurrency(DiscountOther)));
                $(this).val((balance).toFixed(<?php echo $rowOption[0]; ?>));
                $(this).closest("tr").find(".PayBillAmountOther").val(0);
                $(this).closest("tr").find(".PayBillBalance").val(0);
                $(this).closest("tr").find(".PayBillBalanceOther").val(0);
                $(this).closest("tr").find(".PayBillDueDate").hide();
            }
            if(totalPaid==0){
                $(this).closest("tr").find(".PayBillDueDate").hide();
            }
            totalPayUs        += replaceNum($(this).val());
            totalPayOther     += replaceNum($(this).closest("tr").find(".PayBillAmountOther").val());
            totalDisUs        += replaceNum($(this).closest("tr").find(".PayBillDiscountUs").val());
            totalDisOther     += replaceNum($(this).closest("tr").find(".PayBillDiscountOther").val());
            totalBalance      += replaceNum($(this).closest("tr").find(".PayBillBalance").val());
            totalBalanceOther += replaceNum($(this).closest("tr").find(".PayBillBalanceOther").val());
        });
        $("#totalPayPayBill").text(converDicemalJS(totalPayUs).toFixed(<?php echo $rowOption[0]; ?>));
        $("#totalPayOtherPayBill").text(converDicemalJS(totalPayOther).toFixed(<?php echo $rowOption[0]; ?>));
        $("#totalDiscountPayBill").text(converDicemalJS(totalDisUs).toFixed(<?php echo $rowOption[0]; ?>));
        $("#totalDiscountOtherPayBill").text(converDicemalJS(totalDisOther).toFixed(<?php echo $rowOption[0]; ?>));
        $("#totalBalancePayBill").text(converDicemalJS(totalBalance).toFixed(<?php echo $rowOption[0]; ?>));
        $("#totalBalancePayBillOther").text(converDicemalJS(totalBalanceOther).toFixed(<?php echo $rowOption[0]; ?>));
    }
    
    function checkBfSavePayBill(){
        var formName     = "#PayBillForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".PayBillAmountUs").val() == undefined){
                return false;
            }else{
                var result = false;
                $(".pay_bill_is_paid").each(function(){
                    if($(this).is(':checked')){
                        result = true;
                    }
                });
                return result;
            }
        }
    }
    
    function convertToMainCurrency(val){
        var exchangeRate  = replaceNum($("#exchangeRatePayBill").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }
    
    function convertToOtherCurrency(val){
        var exchangeRate  = replaceNum($("#exchangeRatePayBill").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) * exchangeRate);
        }
        return amountConvert;
    }
</script>
<table style="width: 300px; float: left;">
    <tr>
        <td colspan="2">
            <input type="hidden" name="data[PayBill][exchange_rate_id]" id="exchangeRateIdPayBill" />
            <?php
            $sqlMainCurrency = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE id = ".$companyId.")");
            $rowMainCurrency = mysql_fetch_array($sqlMainCurrency);
            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_purchase FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id AND currency_centers.is_active = 1 WHERE branch_currencies.branch_id = ".$branchId);
            if(mysql_num_rows($sqlCurrency)){
            ?>
            <table class="table" cellspacing="0" >
                <thead>
                    <tr>
                        <th class="first" style="width:100%;" colspan="2"><?php echo MENU_EXCHANGE_RATE_LIST; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($rowCurrency = mysql_fetch_array($sqlCurrency)){
                    ?>
                    <tr>
                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $rowMainCurrency[0]; ?> =</td>
                        <td style="font-size: 12px;"><?php echo number_format($rowCurrency['rate_purchase'], 9); ?> <?php echo $rowCurrency['symbol']; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
            }
            ?>
        </td>
    </tr>
</table>
<table style="width: 400px; float: right;">
    <tr>
        <td>
            <label for="exchangeRatePayBill"><?php echo TABLE_PAID_WITH_OTHER_CURRENCY; ?></label> :
        </td>
        <td>
            <select name="data[PayBill][currency_center_id]" id="exchangeRatePayBill" style="width: 200px;">
                <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                <?php 
                $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_purchase FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id AND currency_centers.is_active = 1 WHERE branch_currencies.branch_id = ".$branchId);
                while($rowCurSelect = mysql_fetch_array($sqlCurSelect)){
                ?>
                <option value="<?php echo $rowCurSelect['id']; ?>" symbol="<?php echo $rowCurSelect['symbol']; ?>" exrate="<?php echo $rowCurSelect['exchange_rate_id']; ?>" ratesale="<?php echo $rowCurSelect['rate_purchase']; ?>"><?php echo $rowCurSelect['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<table class="table" cellspacing="0">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 90px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_BILL_NUMBER; ?></th>
            <th><?php echo TABLE_VENDOR; ?></th>
            <th style="width: 100px !important;"><?php echo GENERAL_AMOUNT; ?></th>
            <th style="width: 110px !important;"><?php echo GENERAL_PAID; ?></th>
            <th style="width: 110px !important;"><?php echo GENERAL_PAID; ?></th>
            <th style="width: 100px !important;"><?php echo POS_DISCOUNTS; ?></th>
            <th style="width: 100px !important;"><?php echo POS_DISCOUNTS; ?></th>
            <th style="width: 110px !important;"><?php echo GENERAL_BALANCE; ?></th>
            <th style="width: 110px !important;"><?php echo GENERAL_BALANCE; ?></th>
            <th style="width: 120px !important;"><?php echo GENERAL_AGING; ?></th>
            <th style="width: 50px !important;"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $index=1;
        $totalAmount=0;
        $balance=0;
        $query=mysql_query("SELECT pr.id, 
                                pr.order_date,
                                pr.po_code,
                                v.name AS vendor_name,
                                pr.total_amount + IFNULL(pr.total_vat,0) - IFNULL(pr.discount_amount,0) AS total_amount,
                                pr.balance,
                                currency_centers.symbol
                            FROM purchase_orders pr INNER JOIN vendors v ON pr.vendor_id = v.id
                            INNER JOIN currency_centers ON currency_centers.id = pr.currency_center_id
                            WHERE status>0 AND balance>0
                                AND pr.company_id=".$companyId." AND pr.branch_id=".$branchId."
                                ".($vendorId!=""?' AND pr.vendor_id='.$vendorId:'')."
                            ORDER BY order_date");
        if(mysql_num_rows($query)){
            while($data=mysql_fetch_array($query)){
                $rnd = rand();
                $totalAmount+=$data['total_amount'];
                $balance+=$data['balance'];
        ?>
        <tr>
            <td class="first">
                <?php echo $index++; ?>
                <input type="hidden" name="id[]" value="<?php echo $data['id']; ?>" />
            </td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['po_code']; ?></td>
            <td><?php echo $data['vendor_name']; ?></td>
            <td class="txtAmountPaidPayBill">
                <input type="hidden" name="amount_due[]" value="<?php echo $data['balance']; ?>" />
                <?php echo number_format($data['balance'], $rowOption[0])." ".$data['symbol']; ?>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="PayBillAmountUs<?php echo $rnd; ?>" name="amount_us[]" class="PayBillAmountUs float validate[required]" value="0" style="width: 70px;" /> <?php echo $data['symbol']; ?>
                </div>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="PayBillAmountOther<?php echo $rnd; ?>" name="amount_other[]" class="PayBillAmountOther float" value="0" style="width: 70px;" /> <span class="paidOtherCurrencySymbolPayBill"></span>
                </div>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="PayBillDiscountUs<?php echo $rnd; ?>" name="discount_us[]" class="PayBillDiscountUs float" value="0" style="width: 60px;" /> <?php echo $data['symbol']; ?>
                </div>
            </td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="PayBillDiscountOther<?php echo $rnd; ?>" name="discount_other[]" class="PayBillDiscountOther float" value="0" style="width: 60px;" /> <span class="paidOtherCurrencySymbolPayBill"></span>
                </div>
            </td>
            <td><input type="text" id="PayBillBalance<?php echo $rnd; ?>" name="balance_us[]" class="PayBillBalance float" value="<?php echo number_format($data['balance'], $rowOption[0]); ?>" style="width: 70px;" readonly="readonly" /> <?php echo $data['symbol']; ?></td>
            <td><input type="text" id="PayBillBalanceOther<?php echo $rnd; ?>" class="PayBillBalanceOther float" value="0" style="width: 70px;" readonly="readonly" /> <span class="paidOtherCurrencySymbolPayBill"></td>
            <td>
                <div class="inputContainer">
                    <input type="text" id="PayBillDueDate<?php echo $rnd; ?>" name="due_date[]" class="PayBillDueDate" value="" style="width: 98%;display: none;" readonly="readonly" />
                </div>
            </td>
            <td>
                <input type="checkbox" class="pay_bill_is_paid" />
            </td>
        </tr>
            <?php } ?>
        <tr>
            <td class="first" colspan="4" style="text-align: right;font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
            <td><?php echo number_format($balance, $rowOption[0]); ?></td>
            <td id="totalPayPayBill"></td>
            <td id="totalPayOtherPayBill"></td>
            <td id="totalDiscountPayBill"></td>
            <td id="totalDiscountOtherPayBill"></td>
            <td id="totalBalancePayBill"></td>
            <td id="totalBalancePayBillOther"></td>
            <td></td>
            <td></td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td colspan="13" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>