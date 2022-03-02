<?php
include("includes/function.php");
$this->element('check_access');
$allowPrint  = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowEdit   = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowVoid   = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowReceive  = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackVendorConsignment").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oVendorConsignment.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            $("#"+PbTableName).find("tbody").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        <?php
        if($allowReceive && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        $(".btnReceiveVendorConsignment").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackVendorConsignment").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $vendorConsignment['VendorConsignment']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        $(".btnDeleteVendorConsignment").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $vendorConsignment['VendorConsignment']['id']; ?>';
            var name = '<?php echo $vendorConsignment['VendorConsignment']['code']; ?>';
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
                                $(".btnBackVendorConsignment").click();
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
        if($allowPrint && $vendorConsignment['VendorConsignment']['status'] > 0){
        ?>
        $(".btnPrintInvoiceVendorConsignment").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $vendorConsignment['VendorConsignment']['id']; ?>';
            $(".printInvoiceVendorConsignment").click(function(){
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
        });
        <?php
        }
        if($allowEdit && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        $(".btnEditVendorConsignment").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackVendorConsignment").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $vendorConsignment['VendorConsignment']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewVendorConsignment(){
        var rightPanel = $("#viewLayoutVendorConsignment").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $vendorConsignment['VendorConsignment']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorConsignment">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteVendorConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditVendorConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && $vendorConsignment['VendorConsignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveVendorConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
                <span><?php echo ACTION_RECEIVE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $vendorConsignment['VendorConsignment']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceVendorConsignment">
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
    <legend><?php __(MENU_VENDOR_CONSIGNMENT_INFO); ?></legend>
    <div>
        <table style="width: 100%;" cellpadding="5" cellspacing="0">
            <tr>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_COMPANY; ?>:</td>
                <td style="font-size: 12px;"><?php echo $vendorConsignment['Company']['name']; ?></td>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo MENU_BRANCH; ?>:</td>
                <td style="font-size: 12px;"><?php echo $vendorConsignment['Branch']['name']; ?></td>
                <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td style="font-size: 12px;"><?php echo $vendorConsignment['LocationGroup']['name']; ?></td>
            </tr>
            <tr>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_CONSIGNMENT_CODE; ?> :</td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;">
                    <?php echo $vendorConsignment['VendorConsignment']['code']; ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_CONSIGNMENT_DATE; ?> :</td>
                <td style="width: 20%; font-size: 12px;">
                    <?php echo dateShort($vendorConsignment['VendorConsignment']['date'], 'd/M/Y'); ?>
                </td>
                <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
                <td style="width: 28%; font-size: 12px;">
                    <?php echo $vendorConsignment['Location']['name']; ?>
                </td>
            </tr>
            <tr>
                <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_NOTE; ?> :</td>
                <td colspan="5" style="font-size: 12px;">
                    <?php echo $vendorConsignment['VendorConsignment']['note']; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
        if (!empty($vendorConsignmentDetails)) {            
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_PRODUCT; ?></legend>
                    <table class="table" >
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th style="width: 20%;"><?php echo TABLE_NAME ?></th>
                            <th style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO ?></th>
                            <th><?php echo TABLE_EXPIRED_DATE ?></th>
                            <th><?php echo TABLE_NOTE; ?></th>
                            <th><?php echo TABLE_QTY ?></th>
                            <th><?php echo TABLE_UOM; ?></th>
                            <th><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                            <th><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                        </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($vendorConsignmentDetails as $vendorConsignmentDetail) {                                       
                    $totalPrice += $vendorConsignmentDetail['VendorConsignmentDetail']['total_cost'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $vendorConsignmentDetail['Product']['code'] . ' - ' . $vendorConsignmentDetail['Product']['name']; ?></td>
                        <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['lots_number']; ?></td>
                        <td><?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['date_expired']!=""?dateShort($vendorConsignmentDetail['VendorConsignmentDetail']['date_expired']):""; ?></td>
                        <td><?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['note']; ?></td>
                        <td style="text-align: right"><?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['qty'], 2); ?></td>
                        <td><?php echo $vendorConsignmentDetail['Uom']['name']; ?></td>
                        <td style="text-align: right"><?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['unit_cost'], 3); ?></td>
                        <td style="text-align: right"><?php echo number_format(($vendorConsignmentDetail['VendorConsignmentDetail']['total_cost']), 2); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="<?php if($rowSettingUomDetail[0] == 0){ echo 7; }else{ echo 8; }?>" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>                    
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b> :</td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format(($vendorConsignment['VendorConsignment']['total_amount']), 2); ?> <?php echo $vendorConsignment['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
</fieldset>    