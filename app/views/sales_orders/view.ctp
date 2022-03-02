<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
$this->element('check_access');
$allowPrintReceipt = checkAccess($user['User']['id'], $this->params['controller'], 'printReceipt');
$allowEdit  = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid  = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
include("includes/function.php");
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
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
        $(".btnBackSalesOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableSalesOrder.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        <?php
        $queryHasReceipt    = mysql_query("SELECT id FROM sales_order_receipts WHERE sales_order_id=" . $this->data['SalesOrder']['id'] . " AND is_void = 0");
        $queryHasCreditMemo = mysql_query("SELECT id FROM credit_memo_with_sales WHERE status > 0 AND sales_order_id=" . $this->data['SalesOrder']['id']);
        $invoiceStatus = 0;
        $isPos = $this->data['SalesOrder']['is_pos'];
        if($isPos == 1){
            $invoiceStatus = 1;
        } else {
            if($this->data['SalesOrder']['status'] == 1){
                $invoiceStatus = 1;
            }
        }
        if($allowApprove && $this->data['SalesOrder']['status'] == -2){
        ?>
        $(".btnApproveSalesOrder").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['SalesOrder']['id']; ?>';
            var name = '<?php echo $this->data['SalesOrder']['so_code']; ?>';
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_APPROVE; ?> <b>' + name + '</b>?</p>');
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
                    '<?php echo ACTION_APPROVE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/approve/" + id,
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                obj.attr("disabled", true);
                                obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                            },
                            success: function(result){
                                refreshViewSalesOrder();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                    createSysAct('Sales Order', 'Approve', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Sales Order', 'Approve', 1, '');
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
        if($allowVoid && $invoiceStatus == 1 && $this->data['SalesOrder']['status'] > 0 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo))){
        ?>
        $(".btnDeleteSalesOrder").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['SalesOrder']['id']; ?>';
            var name = '<?php echo $this->data['SalesOrder']['so_code']; ?>';
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
                                $(".btnBackSalesOrder").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('SalesOrder', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('SalesOrder', 'Delete', 1, '');
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
        if($allowPrint && $this->data['SalesOrder']['status'] != 0){
        ?>
        $(".btnPrintInvoiceSalesOrder").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['SalesOrder']['id']; ?>';
            $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceSalesOrder" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_SALES_ORDER; ?></span></button><button type="submit" class="positive printInvoiceSalesOrderNoHead" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_SALES_ORDER; ?> No Header</span></button></div>');
            $(".printInvoiceSalesOrder").click(function(){
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
            $(".printInvoiceSalesOrderNoHead").click(function(){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id+"/1",
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
        if($allowEdit && $this->data['SalesOrder']['status'] == 1 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo))){
        ?>
        $(".btnEditSalesOrder").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackSalesOrder").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['SalesOrder']['id']; ?>");
        });
        <?php
        }
        if($allowAging && $this->data['SalesOrder']['status'] == 1 && $isPos != 1){
        ?>
        $(".btnAgingSalesOrder").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackSalesOrder").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/<?php echo $this->data['SalesOrder']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewSalesOrder(){
        var rightPanel = $("#viewLayoutSalesOrder").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['SalesOrder']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackSalesOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $invoiceStatus == 1 && $this->data['SalesOrder']['status'] > 0 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo))){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteSalesOrder">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['SalesOrder']['status'] == 1 && (!mysql_num_rows($queryHasReceipt) || $isPos) && (!mysql_num_rows($queryHasCreditMemo))){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditSalesOrder">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowApprove && $this->data['SalesOrder']['status'] == -2){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnApproveSalesOrder">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_APPROVE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowAging && $this->data['SalesOrder']['status'] == 1 && $isPos != 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnAgingSalesOrder">
                <img src="<?php echo $this->webroot; ?>img/button/aging.png" alt=""/>
                <span><?php echo TABLE_PAY; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['SalesOrder']['status'] != 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceSalesOrder">
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
    <legend><?php __(MENU_SALES_ORDER_MANAGEMENT_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 15%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 15%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"></td>
                    <td style="width: 15%; font-size: 12px;"></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['SalesOrder']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"></td>
                    <td style="font-size: 12px;"></td>
                    <td style="font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['so_code']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_CUSTOMER_PO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo $this->data['SalesOrder']['customer_po_number']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['SalesOrder']['memo']); ?></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                </tr>
                <?php
                if($this->data['SalesOrder']['status'] == 0){
                ?>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo 'Void Date'; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo dateShort($this->data['SalesOrder']['modified']); ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo 'Void By'; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3">
                        <?php
                        $sqlUser = mysql_query("SELECT CONCAT(first_name, ' ',last_name) As name FROM users WHERE id = ".$this->data['SalesOrder']['modified_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser[0];
                        ?>
                    </td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_CREATED; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo dateShort($this->data['SalesOrder']['created']); ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_CREATED_BY;?> :</td>
                    <td style="font-size: 12px; vertical-align: top;">
                        <?php
                        $sqlUser = mysql_query("SELECT CONCAT(first_name, ' ',last_name) As name FROM users WHERE id = ".$this->data['SalesOrder']['created_by']);
                        $rowUser = mysql_fetch_array($sqlUser);
                        echo $rowUser[0];
                        ?>
                    </td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                    <td style="font-size: 12px; vertical-align: top;"></td>
                </tr>
            </table>
        </div>
    <?php
    if (!empty($salesOrderDetails)) {
        $totalCol = 11;
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th<?php if($allowLots == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_LOTS_NO; ?></th>
                    <th<?php if($allowExpired == false){ --$totalCol; ?> style="display: none;"<?php } ?>><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_F_O_C; ?></th>
                    <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalDiscount = 0;
                $totalPrice = 0;
                $subTotal = 0;
                foreach ($salesOrderDetails as $salesOrderDetail) {
                    // Check Name With Customer
                    $productName = $salesOrderDetail['Product']['name'];
                    $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$salesOrderDetail['Product']['id']." AND customer_id = ".$this->data['Customer']['id']." ORDER BY created DESC LIMIT 1");
                    if(@mysql_num_rows($sqlProCus)){
                        $rowProCus = mysql_fetch_array($sqlProCus);
                        $productName = $rowProCus['name'];
                    }
                    $unit_price = number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], $priceDecimal);
                    $discount = $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                    $subTotal = $salesOrderDetail['SalesOrderDetail']['total_price'] - $discount;
                    $totalDiscount += $discount;
                    $totalPrice += $subTotal;
                ?>
                <tr>
                    <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                    <td><?php echo $salesOrderDetail['Product']['code']; ?></td>
                    <td><?php echo $productName; ?></td>
                    <td<?php if($allowLots == false){ ?> style="display: none;"<?php } ?>><?php echo $salesOrderDetail['SalesOrderDetail']['lots_number']; ?></td>
                    <td<?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <?php 
                        if($salesOrderDetail['SalesOrderDetail']['expired_date'] != '' && $salesOrderDetail['SalesOrderDetail']['expired_date'] != '0000-00-00'){
                            echo dateShort($salesOrderDetail['SalesOrderDetail']['expired_date']);
                        }
                        ?>
                    </td>
                    <td><?php echo $salesOrderDetail['SalesOrderDetail']['note']; ?></td>
                    <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty']; ?></td>
                    <td style="text-align: right"><?php echo $salesOrderDetail['SalesOrderDetail']['qty_free']; ?></td>
                    <td><?php echo $salesOrderDetail['Uom']['name']; ?></td>
                    <td style="text-align: right"><?php echo $unit_price; ?></td>
                    <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                    <td style="text-align: right"><?php echo number_format($subTotal, $priceDecimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php echo $totalCol; ?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal); ?></td>
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
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY; ?></th>
                    <th><?php echo TABLE_F_O_C; ?></th>
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo GENERAL_DISCOUNT; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                $totalDiscount = 0;
                $subTotal = 0;
                foreach ($salesOrderServices as $salesOrderService) {
                    $unit_price = number_format($salesOrderService['SalesOrderService']['unit_price'], $priceDecimal);
                    $discount = $salesOrderService['SalesOrderService']['discount_amount'];
                    $totalDiscount += $discount;
                    $subTotal = $salesOrderService['SalesOrderService']['total_price'] - $discount;
                    $totalPrice += $subTotal;
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo $salesOrderService['Service']['name']; ?></td>
                    <td><?php echo $salesOrderService['SalesOrderService']['note']; ?></td>
                    <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty']; ?></td>
                    <td style="text-align: right;"><?php echo $salesOrderService['SalesOrderService']['qty_free']; ?></td>
                    <td style="text-align: right;"><?php echo $unit_price; ?></td>
                    <td style="text-align: right"><?php echo number_format($discount, $priceDecimal); ?></td>
                    <td style="text-align: right;"><?php echo number_format($subTotal, $priceDecimal); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $priceDecimal); ?></td>
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
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['total_amount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            if ($this->data['SalesOrder']['discount'] > 0) {
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($this->data['SalesOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($this->data['SalesOrder']['discount_percent'],  2); ?>%)<?php } ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['discount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            }
            if($this->data['SalesOrder']['total_vat'] > 0){
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($this->data['SalesOrder']['vat_percent'], $priceDecimal); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['total_vat'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['total_amount'] + $this->data['SalesOrder']['total_vat'] - $this->data['SalesOrder']['discount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right;"><b style="font-size: 17px;"><?php echo TABLE_DEPOSIT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['SalesOrder']['total_deposit'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($salesOrderReceipts)) {
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
                    <th><?php echo GENERAL_AMOUNT; ?></th>
                    <th colspan="2" style="width: 20%;"><?php echo GENERAL_PAID; ?></th>
                    <th colspan="2" style="width: 20%;"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th colspan="2" style="width: 20%;"><?php echo TABLE_CHANGE; ?></th>
                    <th><?php echo GENERAL_BALANCE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach ($salesOrderReceipts as $salesOrderReceipt) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo date("d/m/Y", strtotime($salesOrderReceipt['SalesOrderReceipt']['created'])); ?></td>
                    <td>
                    <?php
                    if ($allowPrintReceipt) {
                        echo $html->link($salesOrderReceipt['SalesOrderReceipt']['receipt_code'], array("action" => "#"), array("class" => "printReceipt$rand", "rel" => $salesOrderReceipt['SalesOrderReceipt']['id']));
                    } else {
                        $salesOrderReceipt['SalesOrderReceipt']['receipt_code'];
                    }
                    ?>
                </td>
                    <td style="text-align: right;">1 <?php echo $this->data['CurrencyCenter']['symbol']; ?> = <?php echo number_format($salesOrderReceipt['ExchangeRate']['rate_to_sell'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['total_amount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_us'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['discount_other'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change_other'], $priceDecimal); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['balance'], $priceDecimal); ?></td>
                </tr>
                <?php
                    }
                ?>
            </table>
        </fieldset>
    </div>
    <?php
    }
    if (!empty($cmWsales)) {
    ?>
    <div>
        <fieldset>
            <legend>Apply To Sales Return</legend>
            <table class="table">
                <tr>
                    <th class="first"></th>
                    <th><?php echo TABLE_APPLY_DATE; ?></th>
                    <th><?php echo TABLE_CREDIT_MEMO_CODE; ?></th>
                    <th><?php echo TABLE_CUSTOMER; ?></th>
                    <th><?php echo TABLE_TOTAL_AMOUNT; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo GENERAL_PAID; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach ($cmWsales as $cmWsale) {
                ?>
                <tr>
                    <td class="first" style="text-align: right;" ><?php echo++$index; ?></td>
                    <td><?php echo dateShort($cmWsale['CreditMemoWithSale']['apply_date']); ?></td>
                    <td style="text-align: right;"><?php echo $cmWsale['CreditMemo']['cm_code']; ?></td>
                    <td style="text-align: right;">
                        <?php
                        $sql = mysql_query("SELECT * FROM customers WHERE id = " . $cmWsale['SalesOrder']['customer_id'] . " AND is_active = 1 LIMIT 1");
                        $customer = mysql_fetch_array($sql);
                        echo $customer['customer_code'] . " - " . $customer['name'];
                        ?>
                    </td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemo']['total_amount'] - ($cmWsale['CreditMemo']['discount']) + ($cmWsale['CreditMemo']['total_vat']), $priceDecimal); ?></td>
                    <td style="text-align: right;"><?php echo number_format($cmWsale['CreditMemoWithSale']['total_price'], $priceDecimal); ?></td>
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