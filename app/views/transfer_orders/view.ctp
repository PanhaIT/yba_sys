<?php
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
// Authentication
$this->element('check_access');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
include('includes/function.php');
$colSpan = 8;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTransferOrder").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTOTable.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        <?php
        if($allowDelete && $this->data['TransferOrder']['status'] == 1){
        ?>
        $(".btnDeleteTransferOrder").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['TransferOrder']['id']; ?>';
            var name = '<?php echo $this->data['TransferOrder']['to_code']; ?>';
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
                                $(".btnBackTransferOrder").click();
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
        if($allowPrint && $this->data['TransferOrder']['status'] > 0){
        ?>
        $(".btnPrintInvoiceTransferOrder").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['TransferOrder']['id']; ?>';
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
        if($allowEdit && $this->data['TransferOrder']['status'] == 1){
        ?>
        $(".btnEditTransferOrder").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackTransferOrder").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['TransferOrder']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewTransferOrder(){
        var rightPanel = $("#viewLayoutTransferOrder").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['TransferOrder']['id']; ?>");
    }
</script>
<?php //debug($toLocations); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTransferOrder">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowDelete && $this->data['TransferOrder']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteTransferOrder">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_DELETE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['TransferOrder']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditTransferOrder">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['TransferOrder']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceTransferOrder">
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
    <legend><?php __(MENU_TRANSFER_ORDER_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; width: 9%;"><?php echo MENU_BRANCH; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $this->data['Branch']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"></td>
            <td style="font-size: 12px; width: 20%;"></td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_NUMBER; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $this->data['TransferOrder']['to_code']; ?>
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
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo dateShort($this->data['TransferOrder']['order_date']); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;" colspan="5">
                <div class="inputContainer">
                    <?php echo nl2br($this->data['TransferOrder']['note']); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<table id="tblTO" class="table" cellspacing="0">
    <tr>
        <th class="first" style="width:4%;"><?php echo TABLE_NO; ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:24%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:10%; <?php if($allowLots == false){ ?>display: none;<?php } else { $colSpan++; } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:11%; <?php if($allowExpired == false){ ?>display: none;<?php } else { $colSpan++; } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:11%;"><?php echo TABLE_LOCATION_FROM; ?></th>
        <th style="width:11%;"><?php echo TABLE_LOCATION_TO; ?></th>
        <th style="width:7%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:12%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:12%;"><?php echo 'AVG'; ?></th>
        <th style="width:12%;"><?php echo TABLE_TOTAL; ?></th>
    </tr>
<?php
    $totalCost = 0;
    if(!empty($transferOrderDetails)){
        $index = 0;
        foreach($transferOrderDetails AS $transferOrderDetail){
?>
    <tr class="recordTODetail">
        <td class="first" style="width:4%;"><?php echo ++$index; ?></td>
        <td style="width:10%;">
            <?php echo $transferOrderDetail['Product']['barcode']; ?>
        </td>
        <td style="width:24%;">
            <?php echo $transferOrderDetail['Product']['name']; ?>
        </td>
        <td style="width:10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
            <?php 
            if($transferOrderDetail['TransferOrderDetail']['lots_number'] != '0' && $transferOrderDetail['TransferOrderDetail']['lots_number'] != ''){
                echo $transferOrderDetail['TransferOrderDetail']['lots_number'];
            }
            ?>
        </td>
        <td style="width:11%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
            <?php 
            if($transferOrderDetail['TransferOrderDetail']['expired_date'] != "0000-00-00" && $transferOrderDetail['TransferOrderDetail']['expired_date'] != ""){
                $expDateLbl = dateShort($transferOrderDetail['TransferOrderDetail']['expired_date']);
            }else{
                $expDateLbl = "";
            }
            echo $expDateLbl; 
            ?>
        </td>
        <td style="width:11%;">
            <?php
            $sqlLocationFrom = mysql_query("SELECT name FROM locations WHERE id = {$transferOrderDetail['TransferOrderDetail']['location_from_id']} ORDER BY name");
            $rowLocationFrom = mysql_fetch_array($sqlLocationFrom);
            echo $rowLocationFrom[0];
            ?>
        </td>
        <td style="width:11%;">
            <?php
            $sqlLocationTo = mysql_query("SELECT name FROM locations WHERE id = {$transferOrderDetail['TransferOrderDetail']['location_to_id']} ORDER BY name");
            $rowLocationTo = mysql_fetch_array($sqlLocationTo);
            echo $rowLocationTo[0];
            ?>
        </td>
        <td style="width:7%;">
            <?php echo $transferOrderDetail['TransferOrderDetail']['qty']; ?>
        </td>
        <td style="width:12%;">
            <?php
            $uomId = $transferOrderDetail['TransferOrderDetail']['qty_uom_id'];
            $query = mysql_query("SELECT abbr FROM uoms WHERE id=".$uomId." ORDER BY name ASC");
            $row   = mysql_fetch_array($query);
            echo $row[0];
            ?>
        </td>
        <td style="text-align: right;">
            <?php
            if($transferOrderDetail['TransferOrderDetail']['unit_cost'] > 0){
                echo number_format($transferOrderDetail['TransferOrderDetail']['unit_cost'], 2)." $";
            }else{
                $conversion = $transferOrderDetail['Product']['small_val_uom'] / $transferOrderDetail['TransferOrderDetail']['conversion'];
                $sqlAvg = mysql_query("SELECT avg_cost FROM inventory_valuations WHERE pid = ".$transferOrderDetail['Product']['id']." AND avg_cost IS NOT NULL ORDER BY id DESC LIMIT 1");
                $rowAvg = mysql_fetch_array($sqlAvg);
                $avgCost = $rowAvg[0] / $conversion;
                echo number_format($avgCost, 2);
            }
            ?>
        </td>
        <td style="text-align: right;">
            <?php
            if($transferOrderDetail['TransferOrderDetail']['unit_cost'] > 0){
                $totalCost += $transferOrderDetail['TransferOrderDetail']['total_cost'];
                echo number_format($transferOrderDetail['TransferOrderDetail']['total_cost'], 2)." $";
            }else{
                $totalCost += $avgCost * $transferOrderDetail['TransferOrderDetail']['qty'];
                echo number_format($avgCost * $transferOrderDetail['TransferOrderDetail']['qty'], 2)." $";
            }
            ?>
        </td>
    </tr>
<?php
    }
}
?>
    <tr>
        <td class="first" colspan="<?php echo $colSpan; ?>" style="text-align: right;">Total</td>
        <td style="text-align: right;">
            <?php echo number_format($totalCost, 2)." $"; ?>
        </td>
    </tr>
</table>