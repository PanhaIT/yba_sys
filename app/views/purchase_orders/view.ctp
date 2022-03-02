<?php
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}

include("includes/function.php");
$this->element('check_access');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen = checkAccess($user['User']['id'], $this->params['controller'], 'open');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPurchaseRequest").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oPRRTable.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        <?php
        if($allowClose && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        $(".btnClosePurchaseRequest").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>';
            var name = '<?php echo $purchaseRequest['PurchaseRequest']['pr_code']; ?>';
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
                                refreshViewPurchaseOrder();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                    createSysAct('Purchase Order', 'Close', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Purchase Order', 'Close', 1, '');
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
        if($allowOpen && $purchaseRequest['PurchaseRequest']['is_close'] == 1 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        $(".btnOpenPurchaseRequest").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>';
            var name = '<?php echo $purchaseRequest['PurchaseRequest']['pr_code']; ?>';
            $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_OPEN; ?> <b>' + name + '</b>?</p>');
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
                    '<?php echo ACTION_OPEN; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/open/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                obj.attr("disabled", true);
                                obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                refreshViewPurchaseOrder();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                    createSysAct('Purchase Order', 'Open', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Purchase Order', 'Open', 1, '');
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
        if($allowVoid && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        $(".btnDeletePurchaseRequest").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>';
            var name = '<?php echo $purchaseRequest['PurchaseRequest']['pr_code']; ?>';
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
                                $(".btnBackPurchaseRequest").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Purchase Order', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Purchase Order', 'Delete', 1, '');
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
        if($allowPrint && $purchaseRequest['PurchaseRequest']['status'] > 0){
        ?>
        $(".btnPrintInvoicePurchaseRequest").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>';
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
        <?php
        }
        if($allowEdit && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        $(".btnEditPurchaseRequest").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackPurchaseRequest").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewPurchaseOrder(){
        var rightPanel = $("#viewLayoutPurchaseRequest").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPurchaseRequest">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeletePurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowClose && $purchaseRequest['PurchaseRequest']['is_close'] == 0 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnClosePurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/close.png" alt=""/>
                <span><?php echo ACTION_CLOSE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowOpen && $purchaseRequest['PurchaseRequest']['is_close'] == 1 && $purchaseRequest['PurchaseRequest']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnOpenPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/open.png" alt=""/>
                <span><?php echo ACTION_OPEN; ?></span>
            </a>
        </div>
        <?php
        } else if(!$allowOpen && $purchaseRequest['PurchaseRequest']['is_close'] == 1) {
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive">
                <img src="<?php echo $this->webroot; ?>img/button/lock.png" alt=""/>
                <span><?php echo TABLE_CLOSED; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $purchaseRequest['PurchaseRequest']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoicePurchaseRequest">
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
    <legend><?php __(MENU_PURCHASE_REQUEST_INFO); ?></legend>
    <div>
        <table width="100%" cellpadding="5">
            <tr>
                <td style="width: 16%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_COMPANY; ?>:</td>
                <td style="font-size: 12px;"><?php echo $purchaseRequest['Company']['name']; ?></td>
                <td style="width: 7%; font-size: 12px; text-transform: uppercase;"><?php echo MENU_BRANCH; ?>:</td>
                <td style="font-size: 12px;"><?php echo $purchaseRequest['Branch']['name']; ?></td>
                <td rowspan="3" style="width: 7%; font-size: 12px; text-transform: uppercase; vertical-align: top;"><?php echo TABLE_SHIP_TO; ?>:</td>
                <td rowspan="3" style="font-size: 12px; vertical-align: top;"><?php echo $purchaseRequest['PurchaseRequest']['shipment_to']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_VENDOR; ?>:</td>
                <td style="font-size: 12px;"><?php echo $purchaseRequest['Vendor']['name']; ?></td>
                <td style="width: 11%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_PO_NUMBER; ?>:</td>
                <td style="font-size: 12px;"><?php echo $purchaseRequest['PurchaseRequest']['pr_code']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_ADDRESS; ?>:</td>
                <td style="font-size: 12px;">
                    <?php echo nl2br($purchaseRequest['Vendor']['address']) ?>
                </td>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_PO_DATE; ?>:</td>
                <td style="font-size: 12px;"><?php echo dateShort($purchaseRequest['PurchaseRequest']['order_date'], "M-d-Y"); ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_REF_QUOTATION; ?>:</td>
                <td style="font-size: 12px;"><?php echo $purchaseRequest['PurchaseRequest']['ref_quotation']; ?></td>
                <td style="font-size: 12px; vertical-align: top; text-transform: uppercase;">
                <?php echo TABLE_CURRENCY; ?> :
                </td>
                <td style="font-size: 12px; vertical-align: top;">
                    <?php
                    echo $purchaseRequest['CurrencyCenter']['name'];
                    ?>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: top; text-transform: uppercase;"><?php echo TABLE_MEMO; ?>:</td>
                <td style="font-size: 12px; vertical-align: top;" colspan="4">
                    <?php 
                    echo nl2br($purchaseRequest['PurchaseRequest']['note']);
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
        if (!empty($purchaseRequestDetails)) {
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_PRODUCT; ?></legend>
                    <table class="table" >
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th><?php echo TABLE_NAME ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_NOTE; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_QTY; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_F_O_C; ?></th>
                            <th style="width: 120px !important;"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                            <th style="width: 120px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
                            <th style="width: 120px !important;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                        </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                foreach ($purchaseRequestDetails as $purchaseRequestDetail) {
                    $totalPrice += ($purchaseRequestDetail['PurchaseRequestDetail']['total_cost'] - $purchaseRequestDetail['PurchaseRequestDetail']['discount_amount']);
                ?>
                    <tr class="selectViewPR" style="cursor: pointer">
                        <td class="first" style="text-align: right;">
                            <?php echo++$index; ?>
                        </td>
                        <td><?php echo $purchaseRequestDetail['Product']['code'] . ' - ' . $purchaseRequestDetail['Product']['name']; ?></td>
                        <td><?php echo $purchaseRequestDetail['PurchaseRequestDetail']['note']; ?></td>
                        <td style="white-space: nowrap"><?php echo $purchaseRequestDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['qty'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['qty_free'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['unit_cost'], $costDecimal); ?></td>
                        <td style="text-align: right">
                            <?php 
                            echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['discount_amount'], $costDecimal); 
                            if($purchaseRequestDetail['PurchaseRequestDetail']['discount_percent'] > 0){
                                echo " (".number_format($purchaseRequestDetail['PurchaseRequestDetail']['discount_percent'], 2)."%)"; 
                            }
                            ?>
                        </td>
                        <td style="text-align: right"><?php echo number_format(($purchaseRequestDetail['PurchaseRequestDetail']['total_cost'] - $purchaseRequestDetail['PurchaseRequestDetail']['discount_amount']), $costDecimal); ?></td>
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
        if (!empty($purchaseRequestServices)) {
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_SERVICE; ?></legend>
                    <table class="table" >
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th><?php echo TABLE_NAME ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_NOTE; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_QTY; ?></th>
                            <th style="width: 120px !important;"><?php echo TABLE_F_O_C; ?></th>
                            <th style="width: 120px !important;"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                            <th style="width: 120px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
                            <th style="width: 120px !important;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                        </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                foreach ($purchaseRequestServices as $purchaseRequestService) {
                    $totalPrice += ( $purchaseRequestService['PurchaseRequestService']['total_cost'] - $purchaseRequestService['PurchaseRequestService']['discount_amount']);
                    $uomName = '';
                    if($purchaseRequestService['Service']['uom_id'] != ''){
                        $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$purchaseRequestService['Service']['uom_id']);
                        $rowUom = mysql_fetch_array($sqlUom);
                        $uomName = $rowUom[0];
                    }
                ?>
                    <tr class="selectViewPR" style="cursor: pointer">
                        <td class="first" style="text-align: right;">
                            <?php echo++$index; ?>
                        </td>
                        <td><?php echo $purchaseRequestService['Service']['code'] . ' - ' . $purchaseRequestService['Service']['name']; ?></td>
                        <td><?php echo $purchaseRequestService['PurchaseRequestService']['note']; ?></td>
                        <td style="white-space: nowrap"><?php echo $uomName; ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['qty'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['qty_free'], 0); ?></td>
                        <td style="text-align: right"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['unit_cost'], $costDecimal); ?></td>
                        <td style="text-align: right">
                            <?php 
                            echo number_format($purchaseRequestService['PurchaseRequestService']['discount_amount'], $costDecimal); 
                            if($purchaseRequestService['PurchaseRequestService']['discount_percent'] > 0){
                                echo " (".number_format($purchaseRequestService['PurchaseRequestService']['discount_percent'], 2)."%)"; 
                            }
                            ?>
                        </td>
                        <td style="text-align: right"><?php echo number_format(($purchaseRequestService['PurchaseRequestService']['total_cost'] - $purchaseRequestService['PurchaseRequestService']['discount_amount']), $costDecimal); ?></td>
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
    <div>
        <table cellpadding="5" cellspacing="0" style="margin-top: 10px; width: 100%;">
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseRequest['PurchaseRequest']['total_amount']), $costDecimal); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseRequest['PurchaseRequest']['vat_percent'], 2); ?> %)</b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseRequest['PurchaseRequest']['total_vat']), $costDecimal); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($purchaseRequest['PurchaseRequest']['total_amount'] + $purchaseRequest['PurchaseRequest']['total_vat']), $costDecimal); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    $sqlDept = mysql_query("SELECT date, reference, IFNULL(total_deposit,0) AS total_deposit, note FROM general_ledgers WHERE apply_to_id = ".$purchaseRequest['PurchaseRequest']['id']." AND deposit_type = 2 AND is_active != 2");
    if(mysql_num_rows($sqlDept)){
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_DEPOSIT; ?></legend>
            <table class="table">
                <tr>
                    <th class="first" style="font-size: 11px;"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px;"><?php echo TABLE_TOTAL_DEPOSIT . TABLE_CURRENCY_DEFAULT; ?></th>
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
</fieldset>    