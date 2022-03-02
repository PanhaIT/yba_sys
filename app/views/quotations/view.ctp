<?php
$priceDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}

include("includes/function.php");
$this->element('check_access');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen = checkAccess($user['User']['id'], $this->params['controller'], 'open');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackQuotation").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableQuotation.fnDraw(false);
        });
        <?php
        if($allowClose && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        $(".btnCloseQuotation").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['Quotation']['id']; ?>';
            var name = '<?php echo $this->data['Quotation']['quotation_code']; ?>';
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
                                refreshViewQuotation();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                    createSysAct('Quotation', 'Close', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Quotation', 'Close', 1, '');
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
        if($allowOpen && $this->data['Quotation']['is_close'] == 1 && $this->data['Quotation']['status'] == 1){
        ?>
        $(".btnOpenQuotation").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['Quotation']['id']; ?>';
            var name = '<?php echo $this->data['Quotation']['quotation_code']; ?>';
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
                                refreshViewQuotation();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                    createSysAct('Quotation', 'Open', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Quotation', 'Open', 1, '');
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
        if($allowApprove && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        $(".btnApproveQuotation").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['Quotation']['id']; ?>';
            var name = '<?php echo $this->data['Quotation']['quotation_code']; ?>';
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
                                refreshViewQuotation();
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                    createSysAct('Quotation', 'Approve', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Quotation', 'Approve', 1, '');
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
        if($allowVoid && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        $(".btnDeleteQuotation").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['Quotation']['id']; ?>';
            var name = '<?php echo $this->data['Quotation']['quotation_code']; ?>';
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
                                $(".btnBackQuotation").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Quotation', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Quotation', 'Delete', 1, '');
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
        ?>
        $(".btnHistoryQuotation").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var code = '<?php echo $this->data['Quotation']['quotation_code']; ?>';
            $.ajax({
                type: "POST",
                url:  "<?php echo $this->base.'/'.$this->params['controller']; ?>/history/" + code,
                beforeSend: function(){
                    obj.attr("disabled", true);
                    obj.find('span').text('<?php echo ACTION_LOADING; ?>');
                },
                success: function(msg){
                    obj.attr("disabled", false);
                    obj.find('span').text('<?php echo ACTION_VIEW_HISTORY; ?>');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo ACTION_VIEW_HISTORY; ?> '+code,
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 500,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); 
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        <?php
        if($allowPrint && $this->data['Quotation']['status'] > 0){
        ?>
        $(".btnPrintInvoiceQuotation").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['Quotation']['id']; ?>';
            $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceQuote" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?></span></button><button type="submit" class="positive printInvoiceQuoteNoHead" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?> No Header</span></button></div>');
            $(".printInvoiceQuote").click(function(){
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
            $(".printInvoiceQuoteNoHead").click(function(){
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
        if($allowEdit && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        $(".btnEditQuotation").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackQuotation").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['Quotation']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewQuotation(){
        var rightPanel = $("#viewLayoutQuotation").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['Quotation']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;" id="viewLayoutQuotation">
    <div class="buttons">
        <a href="" class="positive btnBackQuotation">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowApprove && $this->data['Quotation']['is_approve'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnApproveQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/approved.png" alt=""/>
                <span><?php echo ACTION_APPROVE; ?></span>
            </a>
        </div>
        <?php
        } else {
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive">
                <img src="<?php echo $this->webroot; ?>img/button/lock.png" alt=""/>
                <span><?php echo TABLE_APPROVED; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowClose && $this->data['Quotation']['is_close'] == 0 && $this->data['Quotation']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnCloseQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/close.png" alt=""/>
                <span><?php echo ACTION_CLOSE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowOpen && $this->data['Quotation']['is_close'] == 1 && $this->data['Quotation']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnOpenQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/open.png" alt=""/>
                <span><?php echo ACTION_OPEN; ?></span>
            </a>
        </div>
        <?php
        } else if(!$allowOpen && $this->data['Order']['is_close'] == 1) {
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive">
                <img src="<?php echo $this->webroot; ?>img/button/lock.png" alt=""/>
                <span><?php echo TABLE_CLOSED; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['Quotation']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <span><?php echo ACTION_PRINT; ?></span>
            </a>
        </div>
        <?php
        }
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnHistoryQuotation">
                <img src="<?php echo $this->webroot; ?>img/button/history-icon.png" alt=""/>
                <span><?php echo ACTION_VIEW_HISTORY; ?></span>
            </a>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_QUOTATION_INFO); ?></legend>
    <div>
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                <td style="width: 25%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                <td style="width: 25%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                <td style="width: 10%; font-size: 12px;"><?php echo TABLE_QUOTATION_NUMBER; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Quotation']['quotation_code']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_CONTACT_NAME; ?> :</td>
                <td style="font-size: 12px;"><?php echo $this->data['CustomerContact']['contact_name']; ?></td>
                <td style="font-size: 12px;"><?php echo TABLE_QUOTATION_DATE; ?> :</td>
                <td style="font-size: 12px;"><?php echo dateShort($this->data['Quotation']['quotation_date']); ?></td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-size: 12px;"><?php echo TABLE_MEMO; ?> :</td>
                <td style="vertical-align: top; font-size: 12px;" colspan="6"><?php echo nl2br($this->data['Quotation']['note']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (!empty($quotationDetails)) {
    ?>
        <div>
            <fieldset>
                <legend><?php echo TABLE_PRODUCT; ?></legend>
                <table class="table" >
                    <tr>
                        <th class="first" style="font-size: 12px;"><?php echo TABLE_NO; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_SKU; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px; width: 17%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_QTY ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_F_O_C ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_UOM; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PRICE; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo POS_DISCOUNTS; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo GENERAL_AMOUNT; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($quotationDetails as $quotationDetail) {
                        // Check Name With Customer
                        $productName = $quotationDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$quotationDetail['Product']['id']." AND customer_id = ".$this->data['Customer']['id']." ORDER BY created DESC LIMIT 1");
                        if(@mysql_num_rows($sqlProCus)){
                            $rowProCus = mysql_fetch_array($sqlProCus);
                            $productName = $rowProCus['name'];
                        }
                    ?>
                        <tr><td class="first" style="text-align: center; font-size: 12px;"><?php echo ++$index; ?></td>
                            <td style="font-size: 12px;"><?php echo $quotationDetail['Product']['code']; ?></td>
                            <td style="font-size: 12px;"><?php echo $productName; ?></td>
                            <td style="font-size: 12px;"><?php echo number_format($quotationDetail['QuotationDetail']['qty'], 0); ?></td>
                            <td style="font-size: 12px;"><?php echo number_format($quotationDetail['QuotationDetail']['qty_free'], 0); ?></td>
                            <td style="font-size: 12px;"><?php echo $quotationDetail['Uom']['abbr']; ?></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationDetail['QuotationDetail']['unit_price'], $priceDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationDetail['QuotationDetail']['discount_amount'], $priceDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationDetail['QuotationDetail']['total_price'] - $quotationDetail['QuotationDetail']['discount_amount'], $priceDecimal); ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </fieldset>
        </div>
    <?php
    }
    if (!empty($quotationServices)) {
    ?>
        <div>
            <fieldset>
                <legend><?php echo TABLE_SERVICE; ?></legend>
                <table class="table">
                    <tr>
                        <th class="first" style="font-size: 12px;"><?php echo TABLE_NO; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_SKU; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px; width: 17%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_QTY ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_F_O_C ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_UOM; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PRICE; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo POS_DISCOUNTS; ?></th>
                        <th style="text-transform: uppercase; font-size: 12px;"><?php echo GENERAL_AMOUNT; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($quotationServices as $quotationService) {
                    ?>
                        <tr><td class="first" style="text-align: center; font-size: 12px;"><?php echo ++$index; ?></td>
                            <td style="font-size: 12px;"></td>
                            <td style="font-size: 12px;"><?php echo $quotationService['Service']['name']; ?></td>
                            <td style="font-size: 12px;"><?php echo number_format($quotationService['QuotationService']['qty'], 0); ?></td>
                            <td style="font-size: 12px;"><?php echo number_format($quotationService['QuotationService']['qty_free'], 0); ?></td>
                            <td style="font-size: 12px;"></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationService['QuotationService']['unit_price'], $priceDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationService['QuotationService']['discount_amount'], $priceDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px;"><?php echo number_format($quotationService['QuotationService']['total_price'] - $quotationService['QuotationService']['discount_amount'], $priceDecimal); ?></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_SUB_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Quotation']['total_amount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo GENERAL_DISCOUNT; ?> <?php if($this->data['Quotation']['discount_percent'] > 0){ ?>(<?php echo number_format($this->data['Quotation']['discount_percent'], $priceDecimal); ?>%)<?php } ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Quotation']['discount'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_VAT; ?> (<?php echo number_format($this->data['Quotation']['vat_percent'], $priceDecimal); ?>%)</b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Quotation']['total_vat'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Quotation']['total_amount'] - $this->data['Quotation']['discount'] + $this->data['Quotation']['total_vat'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
            <tr>
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL_DEPOSIT; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Quotation']['total_deposit'], $priceDecimal); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
</fieldset>    