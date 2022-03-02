<?php
$allowLots    = false;
$allowExpired = false;
$priceDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    }
}
include("includes/function.php");
$this->element('check_access');
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackCreditMemo").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableCreditMemo.fnDraw(false);
        });
        $(".btnPrintReceiptCM").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceipt/"+$(this).attr("rel"),
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
        <?php
        $queryHasReceipt  = mysql_query("SELECT id FROM credit_memo_receipts WHERE credit_memo_id=" . $this->data['CreditMemo']['id'] . " AND is_void = 0");
        $queryHasApplyInv = mysql_query("SELECT id FROM credit_memo_with_sales WHERE credit_memo_id=" . $this->data['CreditMemo']['id'] . " AND status > 0");
        if($allowReceive && $this->data['CreditMemo']['status'] == 1){
        ?>
        $(".btnReceiveCreditMemo").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackCreditMemo").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['CreditMemo']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $this->data['CreditMemo']['status'] == 1 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)){
        ?>
        $(".btnDeleteCreditMemo").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['CreditMemo']['id']; ?>';
            var name = '<?php echo $this->data['CreditMemo']['cm_code']; ?>';
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui) {
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_VOID; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/void/" + id,
                            data: "",
                            beforeSend: function() {
                                $("#dialog").dialog("close");
                                obj.attr("disabled", true);
                                obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                            },
                            success: function(result) {
                                $(".btnBackCreditMemo").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Credit Memo', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Credit Memo', 'Delete', 1, '');
                                    // alert message
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
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
        });
        <?php
        }
        if($allowPrint && $this->data['CreditMemo']['status'] > 0){
        ?>
        $(".btnPrintInvoiceCreditMemo").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['CreditMemo']['id']; ?>';
            var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/" + id;
            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function() {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printResult) {
                    w = window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
        <?php
        }
        if($allowEdit && $this->data['CreditMemo']['status'] == 1 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)){
        ?>
        $(".btnEditCreditMemo").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackCreditMemo").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['CreditMemo']['id']; ?>");
        });
        <?php
        }
        if($allowAging && $this->data['CreditMemo']['status'] > 0){
        ?>
        $(".btnAgingCreditMemo").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackCreditMemo").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/<?php echo $this->data['CreditMemo']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewCreditMemo(){
        var rightPanel = $("#viewLayoutCreditMemo").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['CreditMemo']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCreditMemo">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['CreditMemo']['status'] == 1 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['CreditMemo']['status'] == 1 && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasApplyInv)){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && $this->data['CreditMemo']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
                <span><?php echo ACTION_RECEIVE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowAging && $this->data['CreditMemo']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnAgingCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/aging.png" alt=""/>
                <span><?php echo TABLE_PAY; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['CreditMemo']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <span><?php echo ACTION_PRINT; ?></span>
            </a>
        </div>
        <?php
        }
        ?>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CREDIT_MEMO_MANAGEMENT_INFO); ?></legend>
    <div>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="font-size: 12px; width: 9%;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['Branch']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"><?php echo TABLE_LOCATION; ?> :</td>
                <td style="font-size: 12px; width: 15%;"><?php echo $this->data['Location']['name']; ?></td>
                <td style="font-size: 12px; width: 9%;"></td>
                <td style="font-size: 12px;"></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo CREDIT_MEMO_CODE; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['CreditMemo']['cm_code']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                <td style="font-size: 12px;"><?php echo dateShort($this->data['CreditMemo']['order_date']); ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['CreditMemo']['invoice_code']; ?></td>
                <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?></td>
                <td style="font-size: 12px; vertical-align: top;" colspan="5"><?php echo nl2br($this->data['CreditMemo']['note']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($creditMemoDetails)) {
        $totalCol = 11;
        ?>
        <div>
            <fieldset>
                <legend><?php echo TABLE_PRODUCT; ?></legend>
                <table class="table" >
                    <tr>
                        <th class="first"><?php echo TABLE_NO; ?></th>
                        <th><?php echo TABLE_BARCODE; ?></th>
                        <th><?php echo TABLE_NAME ?></th>
                        <th><?php echo TABLE_NOTE; ?></th>
                        <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                        <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                        <th><?php echo TABLE_QTY; ?></th>
                        <th><?php echo TABLE_F_O_C; ?></th>
                        <th><?php echo TABLE_UOM; ?></th>
                        <th><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                        <th><?php echo GENERAL_DISCOUNT; ?></th>
                        <th><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalPrice = 0;
                    foreach ($creditMemoDetails as $creditMemoDetail) {
                        // Check Name With Customer
                        $productName = $creditMemoDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$creditMemoDetail['Product']['id']." AND customer_id = ".$this->data['Customer']['id']." ORDER BY created DESC LIMIT 1");
                        if(@mysql_num_rows($sqlProCus)){
                            $rowProCus = mysql_fetch_array($sqlProCus);
                            $productName = $rowProCus['name'];
                        }
                        $unit_price = number_format($creditMemoDetail['CreditMemoDetail']['unit_price'], $priceDecimal);
                        $discount = $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                        $total_price = number_format($creditMemoDetail['CreditMemoDetail']['total_price'] - $discount, $priceDecimal);
                        $totalPrice += $creditMemoDetail['CreditMemoDetail']['total_price'] - $discount;
                        ?>
                        <tr>
                            <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $creditMemoDetail['Product']['barcode']; ?></td>
                            <td><?php echo $productName; ?></td>
                            <td><?php echo $creditMemoDetail['CreditMemoDetail']['note']; ?></td>
                            <td <?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $creditMemoDetail['CreditMemoDetail']['lots_number']; ?></td>
                            <td <?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                                <?php 
                                if($creditMemoDetail['CreditMemoDetail']['expired_date'] != '' && $creditMemoDetail['CreditMemoDetail']['expired_date'] != '0000-00-00'){
                                    echo dateShort($creditMemoDetail['CreditMemoDetail']['expired_date']);
                                }
                                ?>
                            </td>
                            <td style="text-align: right"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty'], 0); ?></td>
                            <td style="text-align: right"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty_free'], 0); ?></td>
                            <td><?php echo $creditMemoDetail['Uom']['name']; ?></td>
                            <td style="text-align: right"><?php echo $unit_price; ?></td>
                            <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                            <td style="text-align: right"><?php echo $total_price; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                        <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <?php
    }
    ?>
    <?php
    if (!empty($creditMemoServices)) {
        ?>
        <div>
            <fieldset>
                <legend><?php echo TABLE_SERVICE; ?></legend>
                <table class="table" >
                    <tr>
                        <th class="first"><?php echo TABLE_NO; ?></th>
                        <th><?php echo TABLE_NAME ?></th>
                        <th><?php echo TABLE_NOTE; ?></th>
                        <th><?php echo TABLE_QTY ?></th>
                        <th><?php echo SALES_ORDER_UNIT_PRICE ?></th>
                        <th><?php echo GENERAL_DISCOUNT; ?></th>
                        <th><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalPrice = 0;
                    foreach ($creditMemoServices as $creditMemoService) {
                        $unit_price = number_format($creditMemoService['CreditMemoService']['unit_price'], $priceDecimal);
                        $discount = $creditMemoService['CreditMemoService']['discount_amount'];
                        $total_price = number_format($creditMemoService['CreditMemoService']['total_price'] - $discount, $priceDecimal);
                        $totalPrice += $creditMemoService['CreditMemoService']['total_price'] - $discount;
                        ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $creditMemoService['Service']['name']; ?></td>
                            <td><?php echo $creditMemoService['CreditMemoService']['note']; ?></td>
                            <td style="text-align: right"><?php echo number_format($creditMemoService['CreditMemoService']['qty'], $priceDecimal); ?></td>
                            <td style="text-align: right"><?php echo $unit_price; ?></td>
                            <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                            <td style="text-align: right"><?php echo $total_price; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="first" colspan="6" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                        <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
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
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['CreditMemo']['total_amount'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            if ($this->data['CreditMemo']['discount'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($this->data['CreditMemo']['discount_percent'] > 0){ ?>(<?php echo number_format($this->data['CreditMemo']['discount_percent'],  2); ?>%)<?php } ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['CreditMemo']['discount'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            ?>
            <?php
            if ($this->data['CreditMemo']['mark_up'] > 0) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_MARK_UP; ?></b></td>
                    <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['CreditMemo']['mark_up'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
            }
            if($this->data['CreditMemo']['total_vat'] > 0){
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right"><b style="font-size: 17px;"><?php echo TABLE_VAT ?> (<?php echo number_format($this->data['CreditMemo']['vat_percent'], $priceDecimal); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['CreditMemo']['total_vat'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
                }
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['CreditMemo']['total_amount'] + $this->data['CreditMemo']['mark_up'] + $this->data['CreditMemo']['total_vat'] - $this->data['CreditMemo']['discount'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($cmWsales)) {
    ?>
    <div>
        <fieldset>
            <legend>Apply To Invoice</legend>
            <table class="table" >
                <tr>
                    <th class="first"></th>
                    <th><?php echo TABLE_APPLY_DATE; ?></th>
                    <th><?php echo TABLE_INVOICE_CODE; ?></th>
                    <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?> </th>
                    <th><?php echo GENERAL_PAID; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach ($cmWsales as $cmWsale) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo dateShort($cmWsale['CreditMemoWithSale']['apply_date']); ?></td>
                    <td style="text-align: right;"><?php echo $cmWsale['SalesOrder']['so_code']; ?></td>
                    <td style="text-align: right;">
                        <?php
                        $sql = mysql_query("SELECT * FROM customers WHERE id = " . $cmWsale['SalesOrder']['customer_id'] . " AND is_active = 1 LIMIT 1");
                        $customer = mysql_fetch_array($sql);
                        echo $customer['customer_code'] . "-" . $customer['name_kh'] . " (" . $customer['name'].")";
                        ?>
                    </td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['SalesOrder']['total_amount'] - ($cmWsale['SalesOrder']['discount']), $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemoWithSale']['total_price'], $priceDecimal).' '.$this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><a href="" class="btnVoidCmWSale" rel="<?php echo $cmWsale['CreditMemoWithSale']['id']; ?>" name="<?php echo $cmWsale['SalesOrder']['so_code']; ?>"><img alt="Void" onmouseover="Tip('Void')" src="<?php echo $this->webroot; ?>img/button/delete.png" /></a></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </fieldset>
    </div>
    <?php
    }
    if (!empty($creditMemoReceipts)) {
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
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?> </th>
                    <th colspan="2" style="width: 25%;"><?php echo GENERAL_PAID; ?></th>
                    <th><?php echo GENERAL_BALANCE; ?> </th>
                    <th style="width:10%;"></th>
                </tr>
                <?php
                $index = 0;
                foreach ($creditMemoReceipts as $creditMemoReceipt) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo date("d/m/Y", strtotime($creditMemoReceipt['CreditMemoReceipt']['pay_date'])); ?></td>
                    <td>
                        <?php
                        echo $creditMemoReceipt['CreditMemoReceipt']['receipt_code'];
                        ?>
                    </td>
                    <td style="text-align: right;">1 <?php echo $this->data['CurrencyCenter']['symbol']; ?> = <?php echo number_format($creditMemoReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $creditMemoReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['total_amount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_us'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_other'], $priceDecimal); ?> <?php echo $creditMemoReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['balance'], $priceDecimal); ?></td>
                    <td>
                        <?php
                        if ($allowPrintReceipt) {
                            echo "<a href='#' class='btnPrintReceiptCM' rel='{$creditMemoReceipt['CreditMemoReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
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
    ?>
</fieldset>    