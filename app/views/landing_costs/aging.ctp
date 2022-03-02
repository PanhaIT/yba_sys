<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

include("includes/function.php");
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: <?php echo $rowOption[0]; ?>, aSep: ','});
        $("#LandingCostAmountUs, #LandingCostAmountOther, .paidLandingCost").unbind('keyup').unbind('click');
        
        $(".btnBackLandingCost").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableLandingCost.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        
        $("#LandingCostAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#LandingCostAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $("#LandingCostAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#LandingCostPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Landed Cost', 'Aging', 2, result.responseText);
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
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Landed Cost', 'Aging', 1, '');
                $("#dialog").html('<div class="buttons"><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></div> ');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:['center'],
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        var rightPanel=$(".btnBackLandingCost").parent().parent().parent();
                        var leftPanel=rightPanel.parent().find(".leftPanel");
                        rightPanel.hide("slide", { direction: "right" }, 500, function(){
                            leftPanel.show();
                            rightPanel.html("");
                        });
                        leftPanel.html("<?php echo ACTION_LOADING; ?>");
                        leftPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/");
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".paidPayBill").click(function(){
            var formName = "#LandingCostAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if(parseFloat(replaceNum($("#LandingCostBalanceUs").val())) == <?php echo $this->data['LandingCost']['balance']; ?>){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please paid first.</p>');
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
                    return false;
                }else{
                    return true;
                }
            }
        });

        var now = new Date();
        $("#LandingCostPayDate").val(now.toString('dd/MM/yyyy'));
        $("#LandingCostPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($this->data['LandingCost']['date'])); ?>'
        }).unbind("blur");

        $('#LandingCostAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($this->data['LandingCost']['date'])); ?>'
        }).unbind("blur");
        
        $(".btnDeleteReceiptLandingCost").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var poId = $(this).attr('name');
            voidReceiptLandingCost(id, name, poId);
        });
        
        $("#LandingCostAmountUs, #LandingCostAmountOther").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#LandingCostAmountUs, #LandingCostAmountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });

        $("#LandingCostAmountUs, #LandingCostAmountOther").keyup(function(){
            calculateReceiptBalanceLandingCost();
        });

        // hide coa that not belong to the company
        $(".landed_cost_aging_coa_id option").show();
        $(".landed_cost_aging_coa_id option").each(function(){
            if($(this).attr("company_id")){
                var companyId=$(this).attr("company_id").split(",");
                if(companyId.indexOf("<?php echo $this->data['LandingCost']['company_id']; ?>")==-1){
                    $(this).hide();
                }
            }
        });
        
        $("#exchangeRateLandingCost").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#LandingCostAmountOther").removeAttr("readonly");
            } else {
                $("#LandingCostAmountOther").val(0);
                $("#LandingCostAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbolLandingCost").html(symbol);
            $("#LandingCostExchangeRateId").val(exRateId);
            calculateReceiptBalanceLandingCost();
        });
    });
    
    function calculateReceiptBalanceLandingCost(){
        var totalAmount = replaceNum('<?php echo $this->data['LandingCost']['balance']; ?>');
        var amount      = replaceNum($("#LandingCostAmountUs").val());
        var amountOther = replaceNum($("#LandingCostAmountOther").val());

        // Obj
        var balance      = $("#LandingCostBalanceUs");
        var balanceOther = $("#LandingCostBalanceOther");

        var totalPaid = amount + convertToMainCurrencyLandingCost(amountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#LandingCostAmountUs").val(totalAmount);
            $("#LandingCostAmountOther").val(0);
        }
        var totalBalance = converDicemalJS(totalAmount - totalPaid);  
        if(totalBalance > 0){
            $(".divLandingCostAging").show();
            $("#spanAgingLandingCost").html("*");
            $("#LandingCostAging").addClass("validate[required]");
        }else{
            $(".divLandingCostAging").hide();
            $("#spanAgingLandingCost").html("");
            $("#LandingCostAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(2));
        balanceOther.val(convertToOtherCurrencyLandingCost(totalBalance).toFixed(<?php echo $rowOption[0]; ?>));
        $("#LandingCostBalanceUs, #LandingCostBalanceOther").priceFormat({
            centsLimit: <?php echo $rowOption[0]; ?>,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrencyLandingCost(val){
        var exchangeRate  = replaceNum($("#exchangeRateLandingCost").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrencyLandingCost(val){
        var exchangeRate = replaceNum($("#exchangeRateLandingCost").find("option:selected").attr("ratesale"));
        return converDicemalJS(replaceNum(val) * exchangeRate);
    }
    
    function voidReceiptLandingCost(id, name, poId){
        $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position: 'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_VOID; ?>': function() {
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/'. $this->params['controller']; ?>/voidReceipt/" + id,
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            var panel = $("#LandingCostAgingForm").parent();
                            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + poId);
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Landed Cost', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Landed Cost', 'Void Receipt', 1, '');
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            var panel = $("#PurchaseReturnAgingForm").parent();
                            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + $("#PurchaseReturnId").val());
                            $("#dialog").dialog({
                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                position: 'center',
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    });
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackLandingCost">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('LandingCost'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_LANDED_COST_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 12%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_CODE; ?> :</td>
                    <td style="width: 15%; font-size: 12px;"><?php echo $this->data['LandingCost']['code']; ?></td>
                    <td style="width: 15%; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['LandingCost']['date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Vendor']['vendor_code']." - ".$this->data['Vendor']['name']; ?></td>
                    <td style="font-size: 12px;">A/P :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['ChartAccount']['account_codes']." - ".$this->data['ChartAccount']['account_description']; ?></td>
                    <td style="font-size: 12px;"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['PurchaseOrder']['po_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_REFERENCE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['LandingCost']['reference']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo MENU_LANDED_COST_TYPE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['LandedCostType']['name']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['LandingCost']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
        if (!empty($landingCostDetails)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_SKU; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo TABLE_UNIT_COST ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo TABLE_LANDED_COST; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($landingCostDetails as $landingCostDetail) {
                    // Check Name With Customer
                    $productName = $landingCostDetail['Product']['name'];
                    $landedCost  = number_format($landingCostDetail['LandingCostDetail']['landed_cost'], $rowOption[0]);
                    $totalPrice += $landingCostDetail['LandingCostDetail']['landed_cost'];
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $landingCostDetail['Product']['barcode']; ?></td>
                        <td><?php echo $landingCostDetail['Product']['code']; ?></td>
                        <td><?php echo $productName; ?></td>
                        <td><?php echo $landingCostDetail['LandingCostDetail']['qty']; ?></td>
                        <td><?php echo $landingCostDetail['Uom']['abbr']; ?></td>
                        <td><?php echo number_format($landingCostDetail['LandingCostDetail']['unit_cost'], $rowOption[0]); ?></td>
                        <td style="text-align: right"><?php echo $landedCost; ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $rowOption[0]); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
    }
    ?>
    <div>
        <table cellpadding="5" cellspacing="0" style="margin-top: 10px; width: 100%;">
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['LandingCost']['total_amount'], $rowOption[0]); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
</fieldset>    
<?php
if (!empty($landingCostReceipts)) {
?>
    <div>
        <fieldset>
            <legend><?php echo GENERAL_PAID; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE ?></th>
                    <th><?php echo TABLE_CODE ?></th>
                    <th><?php echo GENERAL_EXCHANGE_RATE ?></th>
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th colspan="2" style="width: 25%;"><?php echo GENERAL_PAID; ?></th>
                    <th><?php echo GENERAL_BALANCE; ?></th>
                    <th></th>
                </tr>
    <?php
    $index = 0;
    $leght = count($landingCostReceipts);
    foreach ($landingCostReceipts as $landingCostReceipt) {
    ?>
        <tr>
        <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
        <td><?php echo date("d/m/Y", strtotime($landingCostReceipt['LandingCostReceipt']['pay_date'])); ?></td>
        <td><?php echo $landingCostReceipt['LandingCostReceipt']['code']; ?></td>
        <td style="text-align: right;">1 <?php echo $this->data['CurrencyCenter']['symbol']; ?> = <?php echo number_format($landingCostReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $landingCostReceipt['CurrencyCenter']['symbol']; ?></td>
        <td style="text-align: right;"><?php echo number_format($landingCostReceipt['LandingCostReceipt']['total_amount'], $rowOption[0]); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
        <td style="text-align: right;"><?php echo number_format($landingCostReceipt['LandingCostReceipt']['amount_us'], $rowOption[0]); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
        <td style="text-align: right;"><?php echo number_format($landingCostReceipt['LandingCostReceipt']['amount_other'], $rowOption[0]); ?> <?php echo $landingCostReceipt['CurrencyCenter']['symbol']; ?></td>
        <td style="text-align: right;"><?php echo number_format($landingCostReceipt['LandingCostReceipt']['balance'], $rowOption[0]); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
        <td>
            <?php
            echo "<a href='{$landingCostReceipt['LandingCostReceipt']['code']}' name='{$landingCostReceipt['LandingCostReceipt']['landing_cost_id']}' class='btnDeleteReceiptLandingCost' rel='{$landingCostReceipt['LandingCostReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
            ?>
        </td>
    </tr>
    <?php
        }
    ?>
    </table>
</fieldset>
</div>
<?php
    }
    if($this->data['LandingCost']['balance'] == 0){
        $styleDisplay = " style='display:none'";
    }else{
        $styleDisplay = "";
    }
?>
<div<?php echo $styleDisplay; ?>>
    <div style="float: left;">
        <table style="width: 250px;">
            <tr>
                <td colspan="2">
                    <input type="hidden" name="data[LandingCost][exchange_rate_id]" id="LandingCostExchangeRateId" />
                    <?php
                    $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$this->data['LandingCost']['branch_id']);
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
                                <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $this->data['CurrencyCenter']['symbol']; ?> =</td>
                                <td style="font-size: 12px;"><?php echo number_format($rowCurrency['rate_to_sell'], 9); ?> <?php echo $rowCurrency['symbol']; ?></td>
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
    </div>
    <div style="float: right;">
        <br />
        <table>
            <tr>
                <td><label for="LandingCostPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('pay_date', array('style' => 'text-align:left; width: 120px;', 'readonly' => true)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="LandingCostChartAccountId"><?php echo SALES_ORDER_DEPOSIT_TO; ?> <span class="red">*</span> :</label></td>
                <td>
                    <?php
                    $filter="AND chart_account_type_id IN (1)";
                    ?>
                    <div class="inputContainer">
                        <select id="LandingCostChartAccountId" name="data[LandingCost][chart_account_id]" class="landed_cost_aging_coa_id validate[required]" style="width: 132px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                            while($data[0]=mysql_fetch_array($query[0])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                            ?>
                            <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[0]['id']==$cashBankAccountId?'selected="selected"':''; ?>><?php echo $data[0]['name']; ?></option>
                                <?php
                                $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                                while($data[1]=mysql_fetch_array($query[1])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                                ?>
                                <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[1]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                    <?php
                                    $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                                    while($data[2]=mysql_fetch_array($query[2])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                    ?>
                                    <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[2]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                        <?php
                                        $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                                        while($data[3]=mysql_fetch_array($query[3])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                        ?>
                                        <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[3]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                            <?php
                                            $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                                            while($data[4]=mysql_fetch_array($query[4])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                            ?>
                                            <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[4]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                <?php
                                                $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$this->data['LandingCost']['company_id'].") ".$filter." ORDER BY account_codes");
                                                while($data[5]=mysql_fetch_array($query[5])){
                                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                                ?>
                                                <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[5]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="LandingCostAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                <td>
                    <?php
                    echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                    echo $this->Form->hidden('total_amount', array('value' => $this->data['LandingCost']['balance']));
                    ?> (<?php echo $this->data['CurrencyCenter']['symbol']; ?>)
                </td>
            </tr>
            <tr>
                <td>
                    <select name="data[LandingCost][currency_center_id]" id="exchangeRateLandingCost" style="width: 150px;">
                        <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                        <?php 
                        $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$this->data['LandingCost']['branch_id']);
                        while($rowCurSelect = mysql_fetch_array($sqlCurSelect)){
                        ?>
                        <option value="<?php echo $rowCurSelect['id']; ?>" symbol="<?php echo $rowCurSelect['symbol']; ?>" exrate="<?php echo $rowCurSelect['exchange_rate_id']; ?>" ratesale="<?php echo $rowCurSelect['rate_to_sell']; ?>"><?php echo $rowCurSelect['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <?php echo $this->Form->text('amount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0, 'readonly' => true)); ?>
                    <span class="paidOtherCurrencySymbolLandingCost"></span>
                </td>
            </tr>               
            <tr class="DivLandingCostAging">
                <td style="vertical-align: top">
                    <label for="LandingCostAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanLandingCostAging">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('aging', array('style' => 'width: 120px;')); ?>
                    </div>
                    <div style="clear:both;"></div>
                </td>
            </tr>
        </table>
    </div>
<div style="clear: both;"></div>
<div style="float: right;">
<table align="center" style="width:400px;" class="table" cellspacing="0" >
    <tr>
        <th class="first" colspan="2">
            <?php echo GENERAL_BALANCE; ?>
            </th>
        </tr>
        <tr>
            <td class="first" > 
                <?php echo $this->data['CurrencyCenter']['symbol']; ?>
            </td>
            <td>
                <span class="paidOtherCurrencySymbolLandingCost"></span>
            </td>
        </tr>
        <tr>
            <td class="first"> 
                <?php echo $this->Form->text('balance_us', array('class' => 'float', 'style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($this->data['LandingCost']['balance'], $rowOption[0]))); ?> 
            </td>
            <td> 
                <input type="text" name="data[LandingCost][balance_other]" id="LandingCostBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>
</div>
</fieldset>
<br />
<div class="buttons"<?php echo $styleDisplay; ?>>
<button type="submit" class="positive paidLandingCost" >
<img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
<span class="txtSave"><?php echo ACTION_SAVE; ?></span>
</button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>