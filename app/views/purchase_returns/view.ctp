<?php
$allowLots    = false;
$allowExpired = false;
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 39) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 39){
        $costDecimal = $rowSetting['value'];
    }
}
include("includes/function.php");
$this->element('check_access');
$allowPrint  = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowEdit   = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging  = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive  = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPurchaseReturn").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePurchaseReturn.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        $(".btnPrintReceipt<?php echo $rand; ?>").click(function(event){
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
        <?php
        if($allowReceive && ($this->data['PurchaseReturn']['status'] == 1 || $this->data['PurchaseReturn']['status'] == 3)){
        ?>
        $(".btnReceivePurchaseReturn").click(function(event){
            event.preventDefault();
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['PurchaseReturn']['id']; ?>");
        });
        <?php
        }
        $queryHasReceipt  = mysql_query("SELECT id FROM purchase_return_receipts WHERE purchase_return_id=" . $this->data['PurchaseReturn']['id'] . " AND is_void = 0");
        $queryHasReturn   = mysql_query("SELECT id FROM invoice_pbc_with_pbs WHERE status > 0 AND purchase_return_id=" . $this->data['PurchaseReturn']['id']);
        if($allowDelete && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $this->data['PurchaseReturn']['status'] == 1){
        ?>
        $(".btnDeletePurchaseReturn").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['PurchaseReturn']['id']; ?>';
            var name = '<?php echo $this->data['PurchaseReturn']['pr_code']; ?>';
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
                                $(".btnBackPurchaseReturn").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Bill Return', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Bill Return', 'Delete', 1, '');
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
        if($allowPrint && $this->data['PurchaseReturn']['status'] > 0){
        ?>
        $(".btnPrintInvoicePurchaseReturn").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['PurchaseReturn']['id']; ?>';
            var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id;
            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printResult){
                    w=window.open();
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
        if($allowEdit && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $this->data['PurchaseReturn']['status'] == 1){
        ?>
        $(".btnEditPurchaseReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['PurchaseReturn']['id']; ?>");
        });
        <?php
        }
        if($allowAging && $this->data['PurchaseReturn']['status'] > 0){
        ?>
        $(".btnAgingPurchaseReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/<?php echo $this->data['PurchaseReturn']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewBillReturn(){
        var rightPanel = $("#viewLayoutPurchaseReturn").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['PurchaseReturn']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPurchaseReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowDelete && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $this->data['PurchaseReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeletePurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $this->data['PurchaseReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditPurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && ($this->data['PurchaseReturn']['status'] == 1 || $this->data['PurchaseReturn']['status'] == 3)){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceivePurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
                <span><?php echo ACTION_PICK; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowAging && $this->data['PurchaseReturn']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnAgingPurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/aging.png" alt=""/>
                <span><?php echo TABLE_PAY; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['PurchaseReturn']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoicePurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <span><?php echo ACTION_PRINT_BILL_RETURN; ?></span>
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
    <legend><?php __(MENU_PURCHASE_RETURN_MANAGEMENT_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 25%; font-size: 12px;"><?php echo $purchaseReturn['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px; text-transform: uppercase;"></td>
                    <td style="width: 25%; font-size: 12px;"></td>
                    <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($purchaseReturn['PurchaseReturn']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $purchaseReturn['LocationGroup']['name']; ?></td>
                    <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_LOCATION; ?> :</td>
                    <td ><?php echo $purchaseReturn['Location']['name']; ?></td>
                    <td style="font-size: 12px; text-transform: uppercase;"><?php echo PURCHASE_RETURN_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $purchaseReturn['PurchaseReturn']['pr_code']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_VENDOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $purchaseReturn['Vendor']['vendor_code']." - ".$purchaseReturn['Vendor']['name']; ?></td>
                    <td style="font-size: 12px; text-transform: uppercase;"></td>
                    <td style="font-size: 12px;" colspan="3"></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_MEMO; ?> :</td>
                    <td style="vertical-align: top; font-size: 12px;" cospan="5"><?php echo nl2br($purchaseReturn['Vendor']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
    if (!empty($purchaseReturnDetails)) {
        $totalCol = 9;
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                    <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($purchaseReturnDetails as $purchaseReturnDetail) {
                    $totalPrice += $purchaseReturnDetail['PurchaseReturnDetail']['total_price'];
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseReturnDetail['Product']['barcode']; ?></td>
                        <td><?php echo $purchaseReturnDetail['Product']['name']; ?></td>
                        <td><?php echo $purchaseReturnDetail['PurchaseReturnDetail']['note']; ?></td>
                        <td<?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $purchaseReturnDetail['PurchaseReturnDetail']['lots_number']; ?></td>
                        <td<?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                            <?php 
                            if($purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] != '' && $purchaseReturnDetail['PurchaseReturnDetail']['expired_date'] != '0000-00-00'){
                                echo dateShort($purchaseReturnDetail['PurchaseReturnDetail']['expired_date']);
                            }
                            ?>
                        </td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['qty'], 0); ?></td>
                        <td><?php echo $purchaseReturnDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['unit_price'], $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['total_price'], $costDecimal); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $costDecimal); ?></td>
                </tr>
            </table>
        </fieldset>
    </div>
    <?php
            }
    ?>
    <?php
            if (!empty($purchaseReturnServices)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_SERVICE; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($purchaseReturnServices as $purchaseReturnService) {
                    $totalPrice += $purchaseReturnService['PurchaseReturnService']['total_price'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseReturnService['Service']['name']; ?></td>
                        <td><?php echo $purchaseReturnService['PurchaseReturnService']['note']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['qty'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['unit_price'], $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['total_price'], $costDecimal); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="5" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $costDecimal); ?></td>
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
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseReturn['PurchaseReturn']['vat_percent'], $costDecimal); ?> %)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_vat'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'] + $purchaseReturn['PurchaseReturn']['total_vat'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
            if (!empty($purchaseReturnReceipts)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo GENERAL_PAID; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_DATE ?></th>
                                <th><?php echo TABLE_CODE ?></th>
                                <th class="hidden"><?php echo GENERAL_EXCHANGE_RATE ?></th>
                                <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                                <th><?php echo GENERAL_PAID; ?></th>
                                <th class="hidden"><?php echo GENERAL_PAID; ?></th>
                                <th><?php echo GENERAL_BALANCE; ?></th>
                                <th style="width:50px;"></th>
                            </tr>
                <?php
                $index = 0;
                foreach ($purchaseReturnReceipts as $purchaseReturnReceipt) {
                ?>
                    <tr>
                        <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($purchaseReturnReceipt['PurchaseReturnReceipt']['created'])); ?></td>
                        <td><?php echo $purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code']; ?></td>
                        <td class="hidden" style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['ExchangeRate']['riel'], $costDecimal); ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['total_amount'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_us'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                        <td class="hidden" style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_kh'], $costDecimal); ?> <?php echo $purchaseReturnReceipt['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['balance'], $costDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                        <td>
                            <?php
                            if($allowPrintReceipt){
                                echo "<a href='#' class='btnPrintReceipt$rand' rel='{$purchaseReturnReceipt['PurchaseReturnReceipt']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
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