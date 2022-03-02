<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackRequestStock").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableRequestStock.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        <?php
        if($allowDelete && $this->data['RequestStock']['status'] == 1){
        ?>
        $(".btnDeleteRequestStock").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['RequestStock']['id']; ?>';
            var name = '<?php echo $this->data['RequestStock']['code']; ?>';
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
                                $(".btnBackRequestStock").click();
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
        if($allowPrint && $this->data['RequestStock']['status'] > 0){
        ?>
        $(".btnPrintInvoiceRequestStock").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['RequestStock']['id']; ?>';
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
        if($allowEdit && $this->data['RequestStock']['status'] == 1){
        ?>
        $(".btnEditRequestStock").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackRequestStock").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['RequestStock']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewRequestStock(){
        var rightPanel = $("#viewLayoutRequestStock").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['RequestStock']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackRequestStock">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowDelete && $this->data['RequestStock']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteRequestStock">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['RequestStock']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditRequestStock">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['RequestStock']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceRequestStock">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <span><?php echo ACTION_PRINT_REQUEST_STOCK; ?></span>
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
    <legend><?php __(MENU_REQUEST_STOCK_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <td style="font-size: 12px;"><?php __(TABLE_COMPANY); ?> :</td>
            <td style="font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
            <td style="font-size: 12px;"><?php __(MENU_BRANCH); ?> :</td>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px; width: 15%;"><?php __(TABLE_REQUEST_STOCK_DATE); ?> :</td>
            <td style="font-size: 12px; width: 25%;"><?php echo dateShort($this->data['RequestStock']['date']); ?></td>
            <td style="font-size: 12px; width: 15%;"><?php __(TABLE_REQUEST_STOCK_NUMBER); ?> :</td>
            <td style="font-size: 12px;"><?php echo $this->data['RequestStock']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php __(TABLE_FROM_WAREHOUSE); ?> :</td>
            <td style="font-size: 12px;"><?php echo $fromLocationGroups['LocationGroup']['name']; ?></td>
            <td style="font-size: 12px;"><?php __(TABLE_TO_WAREHOUSE); ?> :</td>
            <td style="font-size: 12px;"><?php echo $toLocationGroups['LocationGroup']['name']; ?></td>
        </tr>
    </table>
</fieldset>
<br/>
<table class="table" cellspacing="0" style="padding:0px; width:99%;">
    <tr>
        <th class="first" style="width:8%"><?php echo TABLE_NO; ?></th>
        <th style="width:15%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:15%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:32%;"><?php echo TABLE_PRODUCT; ?></th>
        <th style="width:15%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
    </tr>
    <?php
        if(!empty($requestStockDetails)){
            $index = 1;
            foreach($requestStockDetails AS $requestStockDetail){
    ?>
    <tr class="listBodyRequestStock">
        <td class="first" style="width:8%"><?php echo $index; ?></td>
        <td style="width:15%"><span class="requestStockSKU"><?php echo $requestStockDetail['Product']['code']; ?></span></td>
        <td style="width:15%"><span class="requestStockPUC"><?php echo $requestStockDetail['Product']['barcode']; ?></span></td>
        <td style="width:32%">
            <div class="inputContainer" style="width:100%">
                <?php echo $requestStockDetail['Product']['name']; ?>
            </div>
        </td>
        <td style="padding:0px; text-align: center; width:15%">
            <div class="inputContainer" style="width:100%">
                <?php echo $requestStockDetail['RequestStockDetail']['qty']; ?>
            </div>
        </td>
        <td style="padding:0px; text-align: center; width:15%">
            <div class="inputContainer" style="width:100%"> 
                <?php echo $requestStockDetail['Uom']['abbr']; ?>
            </div>
        </td>
    </tr>
    <?php
            $index++;
        }
    }
    ?>
</table>
<?php
if(!empty($this->data['RequestStock']['transfer_order_id'])){
?>
<fieldset>
    <legend><?php __(MENU_TO_RECEIVE_MANAGEMENT); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_COMPANY; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $order['Company']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_NUMBER; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $order['TransferOrder']['to_code']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo dateShort($order['TransferOrder']['order_date']); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php echo TABLE_FROM_WAREHOUSE; ?>  :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $fromLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>        
            <td style="font-size: 12px;"><?php echo TABLE_TO_WAREHOUSE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $toLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px;"><?php echo TABLE_FULFILLMENT_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php 
                    if($order['TransferOrder']['fulfillment_date']!='0000-00-00')
                        echo dateShort($order['TransferOrder']['fulfillment_date']); 
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_REQUEST_STOCK_NUMBER; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $order['RequestStock']['code']; ?>
                </div>
            </td>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_TYPE; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php 
                    if($order['TransferOrder']['type'] == 1){
                        echo TABLE_TRANSFER;
                    } else {
                        echo TABLE_CONSIGNMENT; 
                    }
                    ?>
                </div>
            </td>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php echo nl2br($order['TransferOrder']['note']); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<?php
    foreach($resultDetails AS $resultDetail){
?>
<div id="bodyDetail<?php echo $rand; ?>" style="margin-top:10px;">
<fieldset style="width:98%">
    <legend><?php __(MENU_TO_RECEIVE_MANAGEMENT_INFO); ?></legend>
    <div style="float: right; width:30px;">
        <?php
            echo "<a href='#' class='btnReprintInvoiceTOReceive' rel='{$resultDetail['TransferOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT_TRANSFER_RECEIVE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
        ?>
    </div>
    <table cellpadding="5" width="100%">
        <tr>
            <td width="15%"><b><?php echo TABLE_DATE; ?> :</b></td>
            <td width="20%"><?php echo dateShort($resultDetail['TransferReceiveResult']['date']); ?></td>
            <td width="15%"><b><?php echo TABLE_CODE; ?> :</b></td>
            <td><?php echo $resultDetail['TransferReceiveResult']['code']; ?></td>
        </tr>
    </table>
    <fieldset style="width:98%; margin-bottom: 10px;">
        <legend><?php __(MENU_INFO_ITEM_RECEIVE); ?></legend>
        <table cellpadding="5" style="width:100%" class="table">
            <tr>
                <th class="first" style="width:8%"><?php echo TABLE_BARCODE; ?></th>
                <th style="width:8%"><?php echo TABLE_SKU; ?></th>
                <th style="width:30%"><?php echo TABLE_PRODUCT_NAME; ?></th>
                <th style="width:7%"><?php echo TABLE_QTY_TRANSFER; ?></th>
                <th style="width:9%"><?php echo TABLE_QTY_RECEIVE; ?></th>
                <th style="width:10%"><?php echo TABLE_UOM; ?></th>
                <th style="width:10%"><?php echo TABLE_LOTS_NO; ?></th>
                <th style="width:10%"><?php echo TABLE_EXPIRED_DATE; ?></th>
            </tr>
            <?php
            $sqlReceive = mysql_query("SELECT p.barcode AS barcode, p.code AS code, p.name AS name, (SELECT qty FROM transfer_order_details WHERE id = pr.transfer_order_detail_id) AS qty_doc, pr.qty AS qty_receive, u.abbr AS uom_name, pr.lots_number AS lots_number, pr.expired_date AS date_expired FROM transfer_receives AS pr INNER JOIN products AS p ON p.id = pr.product_id INNER JOIN uoms AS u ON u.id = pr.qty_uom_id WHERE pr.transfer_receive_result_id = {$resultDetail['TransferReceiveResult']['id']} AND pr.status = 1;");
            while($rowReceive = mysql_fetch_array($sqlReceive)){
            ?>
            <tr>
                <td class="first"><?php echo $rowReceive['barcode']; ?></td>
                <td><?php echo $rowReceive['code']; ?></td>
                <td><?php echo $rowReceive['name']; ?></td>
                <td><?php echo $rowReceive['qty_doc']; ?></td>
                <td><?php echo $rowReceive['qty_receive']; ?></td>
                <td><?php echo $rowReceive['uom_name']; ?></td>
                <td><?php echo $rowReceive['lots_number']; ?></td>
                <td><?php echo dateShort($rowReceive['date_expired']); ?></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </fieldset>
</fieldset>
</div>
<?php
    }
}
?>