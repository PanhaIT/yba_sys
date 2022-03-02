<?php
$this->element('check_access');
$allowEdit  = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid  = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackVendorConsignmentReturn").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVendorConsignmentReturn.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        <?php
        if($allowReceive && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        $(".btnReceiveVendorConsignmentReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackVendorConsignmentReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['VendorConsignmentReturn']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        $(".btnDeleteVendorConsignmentReturn").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['VendorConsignmentReturn']['id']; ?>';
            var name = '<?php echo $this->data['VendorConsignmentReturn']['code']; ?>';
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
                                $(".btnBackVendorConsignmentReturn").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('VendorConsignmentReturn', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('VendorConsignmentReturn', 'Delete', 1, '');
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
        if($allowPrint && $this->data['VendorConsignmentReturn']['status'] > 0){
        ?>
        $(".btnPrintInvoiceVendorConsignmentReturn").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['VendorConsignmentReturn']['id']; ?>';
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . "consignments"; ?>/printInvoice/"+id,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    w = window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                }
            });
        });
        <?php
        }
        if($allowEdit && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        $(".btnEditVendorConsignmentReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackVendorConsignmentReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['VendorConsignmentReturn']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewVendorConsignmentReturn(){
        var rightPanel = $("#viewLayoutVendorConsignmentReturn").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['VendorConsignmentReturn']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorConsignmentReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteVendorConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditVendorConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && $this->data['VendorConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveVendorConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/hand.png" alt=""/>
                <span><?php echo ACTION_PICK; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['VendorConsignmentReturn']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceVendorConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <span><?php echo ACTION_REPRINT_INVOICE; ?></span>
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
    <legend><?php __(MENU_VENDOR_CONSIGNMENT_RETURN_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 12%; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Location']['name']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Vendor']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo MENU_VENDOR_CONSIGNMENT; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['VendorConsignment']['code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['VendorConsignmentReturn']['date']); ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONSIGNMENT_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['VendorConsignmentReturn']['code']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="5"><?php echo nl2br($this->data['VendorConsignmentReturn']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
        if (!empty($vendorConsignmentReturnDetails)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_SKU; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO ?></th>
                    <th><?php echo TABLE_EXPIRED_DATE; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                $subTotal = 0;
                foreach ($vendorConsignmentReturnDetails as $vendorConsignmentReturnDetail) {
                    // Check Name With Customer
                    $productName = $vendorConsignmentReturnDetail['Product']['name'];
                    $productExp  = '';
                    if($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] != "" && $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] != "0000-00-00"){
                        $productExp = dateShort($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']);
                    }
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $vendorConsignmentReturnDetail['Product']['code']; ?></td>
                        <td><?php echo $productName; ?></td>
                        <td style="text-align: right; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']; ?></td>
                        <td style="text-align: right;"><?php echo $productExp; ?></td>
                        <td><?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['note']; ?></td>
                        <td style="text-align: right"><?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty']; ?></td>
                        <td><?php echo $vendorConsignmentReturnDetail['Uom']['name']; ?></td>
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