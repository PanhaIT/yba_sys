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
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowClose  = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPurchaseBill").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oPOTable.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            $("#"+PbTableName).find("tbody").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        $(".btnPrintReceiptPB").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printReceiptOne/"+$(this).attr("rel"),
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
        $close = false;
        if($purchaseOrder['PurchaseOrder']['vendor_consignment_id'] != ''){
            $close = true;
        } else {
            $sqlProduct = mysql_query("SELECT id FROM purchase_order_details WHERE purchase_order_id = " . $purchaseOrder['PurchaseOrder']['id']);
            if(!mysql_num_rows($sqlProduct)){
                $close = true;
            }
        }
        if($allowClose && $purchaseOrder['PurchaseOrder']['status'] == 1 && $close == true){
        ?>
        $(".btnClosePurchaseOrder").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>';
            var name = '<?php echo $purchaseOrder['PurchaseOrder']['po_code']; ?>';
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CLOSE; ?> <b>' + name + '</b>?</p>');
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
                    '<?php echo ACTION_CLOSE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/close/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                obj.attr("disabled", true);
                                obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                            },
                            success: function(result){
                                refreshViewPurchaseBill();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                    createSysAct('Purchase Bill', 'Close', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Purchase Bill', 'Close', 1, '');
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
        $queryHasReceipt  = mysql_query("SELECT id FROM pvs WHERE purchase_order_id=" . $purchaseOrder['PurchaseOrder']['id'] . " AND is_void = 0");
        $queryHasReturn   = mysql_query("SELECT id FROM invoice_pbc_with_pbs WHERE status > 0 AND purchase_order_id=" . $purchaseOrder['PurchaseOrder']['id']);
        if($allowDelete && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $purchaseOrder['PurchaseOrder']['status'] == 1){
        ?>
        $(".btnDeletePurchaseOrder").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>';
            var name = '<?php echo $purchaseOrder['PurchaseOrder']['po_code']; ?>';
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
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/delete/" + id,
                            data: "",
                            beforeSend: function() {
                                $("#dialog").dialog("close");
                                obj.attr("disabled", true);
                                obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                            },
                            success: function(result) {
                                $(".btnBackPurchaseBill").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Purchase Bill', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Purchase Bill', 'Delete', 1, '');
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
        if($allowPrint && $purchaseOrder['PurchaseOrder']['status'] > 0){
        ?>
        $(".btnPrintInvoicePurchaseOrder").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>';
            $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoicePB" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_PURCHASE_BILL; ?></span></button><button type="submit" class="positive printInvoiceProPO" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_ONLY_PRODUCT; ?></span></button></div> ');
            $(".printInvoicePB").click(function(){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
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
            $(".printInvoiceProPO").click(function(){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoiceProduct/"+id,
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
                    }
                }
            });
        });
        <?php
        }
        if($allowEdit && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $purchaseOrder['PurchaseOrder']['status'] == 1){
        ?>
        $(".btnEditPurchaseOrder").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseBill").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>");
        });
        <?php
        }
        if($allowAging && $purchaseOrder['PurchaseOrder']['status'] > 0){
        ?>
        $(".btnAgingPurchaseOrder").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseBill").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewPurchaseBill(){
        var rightPanel = $("#viewLayoutPurchaseOrder").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $purchaseOrder['PurchaseOrder']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPurchaseBill">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowDelete && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $purchaseOrder['PurchaseOrder']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeletePurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && !mysql_num_rows($queryHasReceipt) && !mysql_num_rows($queryHasReturn) && $purchaseOrder['PurchaseOrder']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditPurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowClose && $purchaseOrder['PurchaseOrder']['status'] == 1 && $close == true){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnClosePurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/close.png" alt=""/>
                <span><?php echo ACTION_CLOSE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowAging && $purchaseOrder['PurchaseOrder']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnAgingPurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/aging.png" alt=""/>
                <span><?php echo TABLE_PAY; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $purchaseOrder['PurchaseOrder']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoicePurchaseOrder">
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
    <legend><?php __(MENU_PURCHASE_ORDER_MANAGEMENT_INFO); ?></legend>
    <div>
        <table style="width: 100%;" cellpadding="5" cellspacing="0">
            <tr>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;">
                    <?php echo $purchaseOrder['Branch']['name']; ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PB_NUMBER; ?> :</td>
                <td style="width: 20%; font-size: 12px;">
                    <?php echo $purchaseOrder['PurchaseOrder']['po_code']; ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                <td style="width: 28%; font-size: 12px;">
                    <?php echo dateShort($purchaseOrder['PurchaseOrder']['order_date']); ?>
                </td>
            </tr>
            <tr>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_INVOICE_NO; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['PurchaseOrder']['invoice_code']; ?>
                </td>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['LocationGroup']['name']; ?>
                </td>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['Location']['name']; ?>
                </td>
            </tr>
            <tr>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
                <td style="font-size: 12px;">
                    <?php echo $purchaseOrder['Vendor']['name']; ?>
                </td>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_MEMO; ?> :</td>
                <td colspan="3" style="font-size: 12px;">
                    <?php echo $purchaseOrder['PurchaseOrder']['note']; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
        if (!empty($purchaseOrderDetails)) {   
            $totalCol = 11;
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_PRODUCT; ?></legend>
                    <table class="table" >
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th><?php echo TABLE_BARCODE; ?></th>
                            <th style="width: 20%;"><?php echo TABLE_NAME ?></th>
                            <th><?php echo TABLE_NOTE; ?></th>
                            <th <?php if($allowLots == false){ --$totalCol; ?>style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO ?></th>
                            <th <?php if($allowExpired == false){ --$totalCol; ?>style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                            <th><?php echo TABLE_QTY; ?></th>
                            <th><?php echo TABLE_F_O_C; ?></th>
                            <th><?php echo TABLE_UOM; ?></th>
                            <th><?php echo TABLE_UNIT_COST.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                            <th><?php echo GENERAL_DISCOUNT.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                            <th><?php echo SALES_ORDER_TOTAL_PRICE.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                foreach ($purchaseOrderDetails as $purchaseOrderDetail) {                                       
                    $discount = $purchaseOrderDetail['PurchaseOrderDetail']['discount_amount'];                     
                    $totalDiscount += $discount;
                    $totalPrice += ( $purchaseOrderDetail['PurchaseOrderDetail']['total_cost'] - $discount);
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $purchaseOrderDetail['Product']['code']; ?></td>
                        <td><?php echo $purchaseOrderDetail['Product']['name']; ?></td>
                        <td><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['note']; ?></td>
                        <td <?php if($allowLots == false){ ?>style="display: none;"<?php } ?>><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['lots_number']; ?></td>
                        <td <?php if($allowExpired == false){ ?>style="display: none;"<?php } ?>><?php echo $purchaseOrderDetail['PurchaseOrderDetail']['date_expired']!=""?dateShort($purchaseOrderDetail['PurchaseOrderDetail']['date_expired']):""; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty_free'], 0); ?></td>
                        <td><?php echo $purchaseOrderDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['unit_cost'], 2); ?></td>
                        <td style="text-align: right"><?php echo number_format($discount, $costDecimal); ?></td>
                        <td style="text-align: right"><?php echo number_format(($purchaseOrderDetail['PurchaseOrderDetail']['total_cost']-$discount), $costDecimal); ?></td>
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
    if(!empty($purchaseOrderServices)){
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
                        <th><?php echo TABLE_F_O_C; ?></th>
                        <th><?php echo SALES_ORDER_UNIT_PRICE .' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo GENERAL_DISCOUNT .' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                        <th><?php echo SALES_ORDER_TOTAL_PRICE .' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    $totalPrice = 0;
                    $totalDiscount = 0;
                    foreach ($purchaseOrderServices as $purchaseOrderService) {
                       
                        $unit_price = number_format($purchaseOrderService['PurchaseOrderService']['unit_cost'], 2);
                        $discount = $purchaseOrderService['PurchaseOrderService']['discount_amount'];
                        $total_price = number_format($purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount, $costDecimal);
                        $totalPrice += $purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount;
                       
                        ?>
                        <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                            <td><?php echo $purchaseOrderService['Service']['name']; ?></td>
                            <td><?php echo $purchaseOrderService['PurchaseOrderService']['note']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty'], 0); ?></td>
                            <td style="text-align: right;"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty_free'], 0); ?></td>
                            <td style="text-align: right"><?php echo $unit_price; ?></td>
                            <td style="text-align: right"><?php echo $discount; ?></td>
                            <td style="text-align: right"><?php echo $total_price; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($purchaseOrder['PurchaseOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($purchaseOrder['PurchaseOrder']['discount_percent'],  2); ?>%)<?php } ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['discount_amount']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseOrder['PurchaseOrder']['vat_percent'], $costDecimal); ?> %)</b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_vat']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount'] - $purchaseOrder['PurchaseOrder']['discount_amount'] + $purchaseOrder['PurchaseOrder']['total_vat']), $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    $poId = $purchaseOrder['PurchaseOrder']['purchase_request_id']!=''?$purchaseOrder['PurchaseOrder']['purchase_request_id']:0;
    $sqlDept = mysql_query("SELECT date, reference, IFNULL(total_deposit,0) AS total_deposit, note FROM general_ledgers WHERE ((apply_to_id = ".$purchaseOrder['PurchaseOrder']['id']." AND deposit_type = 3) OR (apply_to_id = ".$poId." AND deposit_type = 2)) AND is_active = 1");
    if(mysql_num_rows($sqlDept)){
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_DEPOSIT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first" style="font-size: 11px;"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_TOTAL_DEPOSIT.' '.$purchaseOrder['CurrencyCenter']['symbol']; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_CREATED; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; width: 25%;"><?php echo GENERAL_DESCRIPTION; ?></th>
                </tr>
                <?php
                $index = 0;
                while($rowDept = mysql_fetch_array($sqlDept)) {
                ?>
                    <tr><td class="first" style="text-align: center; font-size: 11px;"><?php echo ++$index; ?></td>
                        <td style="font-size: 11px;"><?php echo $rowDept['reference']; ?></td>
                        <td style="font-size: 11px;"><?php echo number_format($rowDept['total_deposit'], $costDecimal); ?></td>
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
            if (!empty($purchaseOrderReceipts)) {
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
                                <th style="width:10%;"></th>
                            </tr>
                <?php
                $index = 0;
                $leght = count($purchaseOrderReceipts);
                foreach ($purchaseOrderReceipts as $purchaseOrderReceipt) {
                ?>
                    <tr><td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($purchaseOrderReceipt['Pv']['pay_date'])); ?></td>
                        <td>
                        <?php
                            echo $purchaseOrderReceipt['Pv']['pv_code'];
                        ?>
                    </td>
                    <td style="text-align: right;">1 <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($purchaseOrderReceipt['ExchangeRate']['rate_to_sell'], $costDecimal); ?> <?php echo $purchaseOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['total_amount'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['amount_us'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['amount_other'], $costDecimal); ?> <?php echo $purchaseOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($purchaseOrderReceipt['Pv']['balance'], $costDecimal); ?> <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></td>
                    <td>
                        <?php
                        echo "<a href='#' class='btnPrintReceiptPB' rel='{$purchaseOrderReceipt['Pv']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
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