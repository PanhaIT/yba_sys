<?php
include("includes/function.php");
$this->element('check_access');
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowPrintInvoice = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$rand = rand();
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#SalesConsignmentAmountUs, #SalesConsignmentAmountOther, #SalesConsignmentDiscountUs, #SalesConsignmentDiscountOther, .paidSalesConsignment").unbind('keyup').unbind('click');
        
        $("#SalesConsignmentAgingForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#SalesConsignmentAgingForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $(".SalesConsignmentAging").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#SalesConsignmentPayDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Sales Invoice', 'Aging', 2, result.responseText);
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
                        $(".btnBackSalesConsignment").click();
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
                createSysAct('Sales Invoice', 'Aging', 1, '');
                $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceipt<?php echo $rand; ?>" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintReceipt"><?php echo ACTION_RECEIPT; ?></span></button></div> ');
                $(".printReceipt<?php echo $rand; ?>").click(function(){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceipt/"+result.sr_id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printReceiptResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printReceiptResult);
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
                    position:['center',100],
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        $(".btnBackSalesConsignment").click();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        $(".printReceipt<?php echo $rand; ?>, .btnPrintReceipt<?php echo $rand; ?>").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceiptCurrent/"+$(this).attr("rel"),
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

        $(".printInvoice<?php echo $rand; ?>, .btnPrintInvoice<?php echo $rand; ?>").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+$(this).attr("rel"),
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
        
        $(".btnDeleteRp<?php echo $rand; ?>").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('href');
            var saleId = $(this).attr('name');
            voidReceiptSO(id, name, saleId);
        });
        
        $(".paidReceivePayment").click(function(){
            var formName = "#SalesConsignmentAgingForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                if($("#SalesConsignmentBalanceUs").val() == <?php echo $salesOrder['SalesOrder']['balance']; ?>){
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
                    return false
                }else{
                    return true;
                }
            }
        });

        var now = new Date();
        $("#SalesConsignmentPayDate").val(now.toString('dd/MM/yyyy'));
        $("#SalesConsignmentPayDate").datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($salesOrder['SalesOrder']['order_date'])); ?>'
        }).unbind("blur");

        $('.SalesConsignmentAging').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            minDate: '<?php echo date("d/m/Y", strtotime($salesOrder['SalesOrder']['order_date'])); ?>'
        }).unbind("blur");
        
        $("#SalesConsignmentAmountUs, #SalesConsignmentAmountOther, #SalesConsignmentDiscountUs, #SalesConsignmentDiscountOther").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $("#SalesConsignmentAmountUs, #SalesConsignmentAmountOther, #SalesConsignmentDiscountUs, #SalesConsignmentDiscountOther").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });
        
        $("#SalesConsignmentAmountUs, #SalesConsignmentDiscountUs").keyup(function(){
            calculateReceiptSoBalance();
        });
        
        $("#SalesConsignmentAmountOther, #SalesConsignmentDiscountOther").keyup(function(){
            if($("#exchangeRateSales").find("option:selected").val() == ""){
                $("#SalesConsignmentAmountOther, #SalesConsignmentDiscountOther").val(0);
            }
            calculateReceiptSoBalance();
        });
        
        // hide coa that not belong to the company
        $(".sales_aging_coa_id").filterOptions("company_id", "<?php echo $salesOrder['SalesOrder']['company_id']; ?>", "<?php echo $cashBankAccountId; ?>");
        
        $(".btnBackSalesConsignment").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                oCache.iCacheLower = -1;
                oTableSalesConsignment.fnDraw(false);
                leftPanel.show();
                rightPanel.html('');
            });
        });
        
        $("#exchangeRateSales").change(function(){
            var symbol   = $(this).find("option:selected").attr("symbol");
            var exRateId = $(this).find("option:selected").attr("exrate");
            if(symbol != ''){
                $("#SalesConsignmentAmountOther").removeAttr("readonly");
            } else {
                $("#SalesConsignmentAmountOther").val(0);
                $("#SalesConsignmentAmountOther").attr("readonly", true);
            }
            $(".paidOtherCurrencySymbol").html(symbol);
            $("#salesOrderExchangeRateId").val(exRateId);
            calculateReceiptSoBalance();
        });
    });

    function calculateReceiptSoBalance(){
        var totalAmount   = replaceNum('<?php echo $salesOrder['SalesOrder']['balance']; ?>');
        var amount        = replaceNum($("#SalesConsignmentAmountUs").val());
        var amountOther   = replaceNum($("#SalesConsignmentAmountOther").val());
        var Discount      = replaceNum($("#SalesConsignmentDiscountUs").val());
        var DiscountOther = replaceNum($("#SalesConsignmentDiscountOther").val());

        // Obj
        var balance      = $("#SalesConsignmentBalanceUs");
        var balanceOther = $("#SalesConsignmentBalanceOther");

        var totalPaid = amount + convertToMainCurrency(amountOther) + Discount + convertToMainCurrency(DiscountOther);
        if(totalPaid > totalAmount){
            totalPaid = totalAmount;
            $("#SalesConsignmentAmountUs").val(totalAmount);
            $("#SalesConsignmentAmountOther").val(0);
            $("#SalesConsignmentDiscountUs").val(0);
            $("#SalesConsignmentDiscountOther").val(0);
        }
        var totalBalance = totalAmount - totalPaid;
        if(totalBalance > 0){
            $(".DivSalesConsignmentAging").show();
            $("#spanSalesConsignmentAging").html("*");
            $("#SalesConsignmentAging").addClass("validate[required]");
        }else{
            $(".DivSalesConsignmentAging").hide();
            $("#spanSalesConsignmentAging").html("");
            $("#SalesConsignmentAging").removeClass("validate[required]");
        }
        balance.val(totalBalance.toFixed(2));
        balanceOther.val(convertToOtherCurrency(totalBalance).toFixed(2));
        $("#SalesConsignmentBalanceUs, #SalesConsignmentBalanceOther").priceFormat({
            centsLimit: 2,
            centsSeparator: '.'
        });
    }
    
    function convertToMainCurrency(val){
        var exchangeRate  = replaceNum($("#exchangeRateSales").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) / exchangeRate);
        }
        return amountConvert;
    }

    function convertToOtherCurrency(val){
        var exchangeRate = replaceNum($("#exchangeRateSales").find("option:selected").attr("ratesale"));
        var amountConvert = 0;
        if(exchangeRate > 0){
            amountConvert = converDicemalJS(replaceNum(val) * exchangeRate);
        }
        return amountConvert;
    }
    
    function voidReceiptSO(id, name, saleId){
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
                        url: "<?php echo $this->base . '/sales_orders'; ?>/voidReceipt/" + id,
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_INVALID; ?>'){
                                createSysAct('Sales Invoice', 'Void Receipt', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Sales Invoice', 'Void Receipt', 1, '');
                                // alert message
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            $(".btnBackSalesConsignment").click();
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
        <a href="" class="positive btnBackSalesConsignment">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('SalesConsignment'); ?>
<?php echo $this->Form->hidden('id', array('value' => $salesOrder['SalesOrder']['id'])); ?>
<?php echo $this->Form->hidden('company_id', array('value' => $salesOrder['SalesOrder']['company_id'])); ?>
<?php echo $this->Form->hidden('location_id', array('value' => $salesOrder['SalesOrder']['location_id'])); ?>
<fieldset>
    <legend><?php __(MENU_SALES_CONSIGNMENT_INFO); ?></legend>
        <div style="float: right; width:30px;">
        <?php
            if ($allowPrintInvoice) {
                echo "<a href='#' class='btnPrintInvoice$rand' rel='{$salesOrder['SalesOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . ' ' . SALES_ORDER_INVOICE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
            }
        ?>
        </div>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['SalesOrder']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONTACT_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['CustomerContact']['contact_name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['so_code']; ?></td>
                </tr>
                <?php
                $salesRepName  = "";
                $collectorName = "";
                $sqlEmployee = mysql_query("SELECT id, name FROM employees");
                while($rowEmployee=mysql_fetch_array($sqlEmployee)){
                    if($rowEmployee['id'] == $this->data['SalesOrder']['sales_rep_id']){
                        $salesRepName = $rowEmployee['name'];
                    }
                    if($rowEmployee['id'] == $this->data['SalesOrder']['collector_id']){
                        $collectorName = $rowEmployee['name'];
                    }
                }
                $charAccname = '';
                if(!empty($this->data['SalesOrder']['ar_id'])){
                    $sqlAr = mysql_query("SELECT CONCAT(account_codes, ' - ', account_description) FROM chart_accounts WHERE id = ".$this->data['SalesOrder']['ar_id']);
                    $rowAr = mysql_fetch_array($sqlAr);
                    $charAccname = $rowAr[0];
                }
                ?>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_QUOTATION_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['quotation_number']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_SALE_ORDER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['order_number']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_SALES_REP; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesRepName; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_COLLECTOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $collectorName; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;">A/R :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo $charAccname; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_CUSTOMER_PO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo $this->data['SalesOrder']['customer_po_number']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['SalesOrder']['memo']); ?></td>
                </tr>
            </table>
        </div>
    <?php
            if (!empty($salesOrderDetails)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo TABLE_PRODUCT; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_SKU; ?></th>
                                <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                                <th><?php echo TABLE_NOTE; ?></th>
                                <th><?php echo TABLE_QTY ?></th>
                                <th><?php echo TABLE_F_O_C; ?></th>
                                <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                                <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                            </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                $subTotal = 0;
                foreach ($salesOrderDetails as $salesOrderDetail) {
                    $unit_price = number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 3);
                    $discount = $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                    $subTotal = $salesOrderDetail['SalesOrderDetail']['total_price'] - $discount;
                    $totalDiscount += $discount;
                    $totalPrice += $subTotal;
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $salesOrderDetail['Product']['code']; ?></td>
                        <td><?php echo $salesOrderDetail['Product']['name']; ?></td>
                        <td><?php echo $salesOrderDetail['SalesOrderDetail']['note']; ?></td>
                        <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty']; ?></td>
                        <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty_free']; ?></td>
                        <td><?php echo $salesOrderDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo $unit_price; ?></td>
                        <td style="text-align: right"><?php echo number_format($discount, 2); ?></td>
                        <td style="text-align: right"><?php echo number_format($subTotal, 2); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="9" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
            }
            if (!empty($salesOrderServices)) {
    ?>

                <div>
                    <fieldset>
                        <legend><?php echo TABLE_SERVICE; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_NAME; ?></th>
                                <th><?php echo TABLE_QTY; ?></th>
                                <th><?php echo TABLE_F_O_C; ?></th>
                                <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo TABLE_NOTE; ?></th>
                                <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                            </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                $totalDiscount = 0;
                $subTotal = 0;
                foreach ($salesOrderServices as $salesOrderService) {
                    $unit_price = number_format($salesOrderService['SalesOrderService']['unit_price'], 3);
                    $discount = $salesOrderService['SalesOrderService']['discount_amount'];
                    $totalDiscount += $discount;
                    $subTotal = $salesOrderService['SalesOrderService']['total_price'] - $discount;
                    $totalPrice += $subTotal;
                ?>
                    <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo $salesOrderService['Service']['name']; ?></td>
                        <td><?php echo $salesOrderService['SalesOrderService']['note']; ?></td>
                        <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty']; ?></td>
                        <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty_free']; ?></td>
                        <td style="text-align: right;"><?php echo $unit_price; ?></td>
                        <td style="text-align: right"><?php echo number_format($discount, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($subTotal, 2); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
            }
            if (!empty($salesOrderMiscs)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo SALES_ORDER_MISCELLANEOUS; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_NAME ?></th>
                                <th><?php echo TABLE_NOTE; ?></th>
                                <th><?php echo TABLE_QTY ?></th>
                                <th><?php echo TABLE_F_O_C; ?></th>
                                <th><?php echo TABLE_UOM; ?></th>
                                <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                            </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                $totalDiscount = 0;
                $subTotal = 0;
                foreach ($salesOrderMiscs as $salesOrderMisc) {
                    $unit_price = number_format($salesOrderMisc['SalesOrderMisc']['unit_price'], 3);
                    $discount = $salesOrderMisc['SalesOrderMisc']['discount_amount'];
                    $totalDiscount += $discount;
                    $subTotal = $salesOrderMisc['SalesOrderMisc']['total_price'] - $discount;
                    $totalPrice += $subTotal;
                ?>
                    <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo $salesOrderMisc['SalesOrderMisc']['description']; ?></td>
                        <td><?php echo $salesOrderMisc['SalesOrderMisc']['note']; ?></td>
                        <td style="text-align: right;"><?php echo $salesOrderMisc['SalesOrderMisc']['qty']; ?></td>
                        <td style="text-align: right;"><?php echo $salesOrderMisc['SalesOrderMisc']['qty_free']; ?></td>
                        <td><?php echo $salesOrderMisc['Uom']['name']; ?></td>
                        <td style="text-align: right;"><?php echo $unit_price; ?></td>
                        <td style="text-align: right"><?php echo number_format($discount, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($subTotal, 2); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="8" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, 2); ?></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_SUB_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            if ($salesOrder['SalesOrder']['discount'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($salesOrder['SalesOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($salesOrder['SalesOrder']['discount_percent'],  2); ?>%)<?php } ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['discount'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            ?>
            <?php
            if($salesOrder['SalesOrder']['total_vat'] > 0){
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($salesOrder['SalesOrder']['vat_percent'], 2); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_vat'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'] + $salesOrder['SalesOrder']['total_vat'] - $salesOrder['SalesOrder']['discount'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($salesOrderWithCms)) {
    ?>
        <div>
                <fieldset>
                            <legend>Apply With Credit Memo</legend>
                            <table class="table" >
                                <tr>
                                    <th class="first"></th>
                                    <th><?php echo TABLE_APPLY_DATE; ?></th>
                                    <th><?php echo TABLE_INVOICE_CODE; ?></th>
                                    <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
                                    <th><?php echo TABLE_TOTAL_AMOUNT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                    <th><?php echo GENERAL_PAID; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                                </tr>
                    <?php
                    $index = 0;

                    foreach ($salesOrderWithCms as $cmWsale) {
                        $totalPrice += $cmWsale['CreditMemoWithSale']['total_price'];
                    ?>
                        <tr>
                            <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo dateShort($cmWsale['CreditMemoWithSale']['apply_date']); ?></td>
                            <td style="text-align: left;"><?php echo $cmWsale['CreditMemo']['cm_code']; ?></td>
                            <td style="text-align: left;">
                            <?php 
                            $sql = mysql_query("SELECT * FROM customers WHERE id = ".$cmWsale['SalesConsignment']['customer_id']." AND is_active = 1 LIMIT 1");
                            $customer = mysql_fetch_array($sql);
                            echo $customer['customer_code']."-".$customer['name_kh']." (".$customer['name'].")";
                            ?>
                            </td>
                            <td style="text-align: right;"><?php echo number_format($cmWsale['SalesConsignment']['total_amount'],2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemoWithSale']['total_price'], 2); ?></td>
                        </tr>
                    <?php
                    }
                    ?>

                </table>
            </fieldset>
        </div>
    <?php
    }
    ?>
    <?php
    $quoteId = $this->data['SalesOrder']['quotation_id']!=''?$this->data['SalesOrder']['quotation_id']:0;
    $sqlDept = mysql_query("SELECT date, reference, IFNULL(total_deposit,0) AS total_deposit, note FROM general_ledgers WHERE ((apply_to_id = ".$this->data['SalesOrder']['id']." AND deposit_type = 5) OR (apply_to_id = ".$quoteId." AND deposit_type = 4)) AND is_active = 1");
    if(mysql_num_rows($sqlDept)){
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_DEPOSIT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first" style="font-size: 11px;"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_TOTAL_DEPOSIT; ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_CREATED; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; width: 25%;"><?php echo GENERAL_DESCRIPTION; ?></th>
                </tr>
                <?php
                $index = 0;
                while($rowDept = mysql_fetch_array($sqlDept)) {
                ?>
                    <tr><td class="first" style="text-align: center; font-size: 11px;"><?php echo ++$index; ?></td>
                        <td style="font-size: 11px;"><?php echo $rowDept['reference']; ?></td>
                        <td style="font-size: 11px;"><?php echo number_format($rowDept['total_deposit'], 0); ?></td>
                        <td style="font-size: 11px;"><?php echo dateShort($rowDept['date']); ?></td>
                        <td style="font-size: 11px;"><?php echo $rowDept['note']; ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </fieldset>
    </div>
    <?php
    }
    ?>
    <?php
            if (!empty($salesOrderReceipts)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo GENERAL_PAID; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th style="width: 90px;"><?php echo TABLE_DATE ?></th>
                                <th style="width: 90px;"><?php echo TABLE_CODE ?></th>
                                <th style="width: 150px;"><?php echo GENERAL_EXCHANGE_RATE ?></th>
                                <th style="width: 100px;"><?php echo GENERAL_AMOUNT; ?></th>
                                <th colspan="2"><?php echo GENERAL_PAID; ?></th>
                                <th colspan="2"><?php echo GENERAL_DISCOUNT; ?></th>
                                <th style="width: 100px;"><?php echo GENERAL_BALANCE; ?></th>
                                <th style="width:10%;"></th>
                            </tr>
                <?php
                $index = 0;
                $leght = count($salesOrderReceipts);
                foreach ($salesOrderReceipts as $salesOrderReceipt) {
                ?>
                    <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($salesOrderReceipt['SalesOrderReceipt']['pay_date'])); ?></td>
                        <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo $html->link($salesOrderReceipt['SalesOrderReceipt']['receipt_code'], array("action" => "#"), array("class" => "printReceipt$rand", "rel" => $salesOrderReceipt['SalesOrderReceipt']['id']));
                        } else {
                            $salesOrderReceipt['SalesOrderReceipt']['receipt_code'];
                        }
                        ?>
                    </td>
                    <td style="text-align: right;">1 <?php echo $salesOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($salesOrderReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['total_amount'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], 2); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_us'], 2); ?> <?php echo $salesOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_other'], 2); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['balance'], 2); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='btnPrintReceipt$rand' rel='{$salesOrderReceipt['SalesOrderReceipt']['id']}'  ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
                        }
                        if($index == $leght){
                            echo "&nbsp; <a href='{$salesOrderReceipt['SalesOrderReceipt']['receipt_code']}' name='{$salesOrderReceipt['SalesOrderReceipt']['sales_order_id']}' class='btnDeleteRp$rand' rel='{$salesOrderReceipt['SalesOrderReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_VOID . "\")'  src='{$this->webroot}img/button/stop.png' /></a>";
                        }
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
                if($salesOrder['SalesOrder']['balance'] == 0){
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
                            <input type="hidden" name="data[SalesConsignment][exchange_rate_id]" id="salesOrderExchangeRateId" />
                            <?php
                            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$salesOrder['SalesOrder']['branch_id']);
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
                                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $salesOrder['CurrencyCenter']['symbol']; ?> =</td>
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
                        <td><label for="SalesConsignmentPayDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('pay_date', array('style' => 'text-align:left; width: 120px;', 'readonly' => true)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="SalesConsignmentChartAccountId"><?php echo SALES_ORDER_DEPOSIT_TO; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <?php
                            $filter="AND chart_account_type_id IN (1)";
                            ?>
                            <div class="inputContainer">
                                <select id="SalesConsignmentChartAccountId" name="data[SalesConsignment][chart_account_id]" class="sales_aging_coa_id validate[required]" style="width: 132px;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php
                                    $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
                                    while($data[0]=mysql_fetch_array($query[0])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                                    ?>
                                    <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[0]['id']==$cashBankAccountId?'selected="selected"':''; ?>><?php echo $data[0]['name']; ?></option>
                                        <?php
                                        $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
                                        while($data[1]=mysql_fetch_array($query[1])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                                        ?>
                                        <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[1]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                            <?php
                                            $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
                                            while($data[2]=mysql_fetch_array($query[2])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                            ?>
                                            <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[2]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                                <?php
                                                $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
                                                while($data[3]=mysql_fetch_array($query[3])){
                                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                                ?>
                                                <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[3]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                                    <?php
                                                    $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
                                                    while($data[4]=mysql_fetch_array($query[4])){
                                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                                    ?>
                                                    <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[4]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                        <?php
                                                        $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT chart_account_id FROM chart_account_companies WHERE company_id = ".$salesOrder['SalesOrder']['company_id'].") ".$filter." ORDER BY account_codes");
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
                        <td><label for="SalesConsignmentAmountUs"><?php echo GENERAL_PAID; ?>:</label></td>
                        <td>
                        <?php
                        echo $this->Form->text('amount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0));
                        echo $this->Form->hidden('total_amount', array('value' => $salesOrder['SalesOrder']['balance']));
                        ?> (<?php echo $salesOrder['CurrencyCenter']['symbol']; ?>)
                    </td>
                    <tr>
                        <td><label for="SalesConsignmentDiscountUs"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                        <td>
                            <?php echo $this->Form->text('discount_us', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                            (<?php echo $salesOrder['CurrencyCenter']['symbol']; ?>)
                        </td>
                    </tr>
                </tr>
                <tr>
                    <td>
                        <select name="data[SalesConsignment][currency_center_id]" id="exchangeRateSales" style="width: 150px;">
                            <option value="" symbol="" exrate="" ratesale=""><?php echo INPUT_SELECT; ?></option>
                            <?php 
                            $sqlCurSelect = mysql_query("SELECT currency_centers.id, currency_centers.name, currency_centers.symbol, branch_currencies.exchange_rate_id, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$salesOrder['SalesOrder']['branch_id']);
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
                        <span class="paidOtherCurrencySymbol"></span>
                    </td>
                </tr>
                <tr>
                    <td><label for="SalesConsignmentDiscountOther"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                    <td>
                        <?php echo $this->Form->text('discount_other', array('style' => 'width: 120px;', 'class' => 'float', 'value' => 0)); ?>
                        <span class="paidOtherCurrencySymbol"></span>
                    </td>
                </tr>
                <tr class="DivSalesConsignmentAging">
                    <td style="vertical-align: top"><label for="SalesConsignmentAging"><?php echo GENERAL_AGING; ?> <span class="red" id="spanSalesConsignmentAging">*</span> :</label></td>
                    <td>
                        <div class="inputContainer">
                            <?php echo $this->Form->text('aging', array('id' => 'SalesConsignmentAging' . $rand, 'class' => 'SalesConsignmentAging', 'style' => 'width: 120px;')); ?>
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
                            <?php echo $salesOrder['CurrencyCenter']['symbol']; ?>
                        </td>
                        <td>
                            <span class="paidOtherCurrencySymbol"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="first"> 
                            <?php echo $this->Form->text('balance_us', array('class' => 'float', 'style' => 'text-align:center; width: 200px;', 'readonly' => true, 'value' => number_format($salesOrder['SalesOrder']['balance'], 2))); ?> 
                        </td>
                        <td> 
                            <input type="text" name="data[SalesConsignment][balance_other]" id="SalesConsignmentBalanceOther" style="width: 200px;" class="float" readonly="readonly" value="0" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>
    </fieldset>
    <br />
    <div class="buttons" <?php echo $styleDisplay; ?>>
        <button type="submit" class="positive paidSalesConsignment" >
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>