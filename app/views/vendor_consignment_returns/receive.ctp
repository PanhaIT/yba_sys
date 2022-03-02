<?php
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#VendorConsignmentReturnReceiveForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#VendorConsignmentReturnReceiveForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {                
                $(".txtSaveRVendorConsignmentReturn").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error > 0){
                    errorPickVendorConsignmentReturn();
                }else{                    
                    $(".btnBackVendorConsignmentReturn").click();
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
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
    });
    
    function errorPickVendorConsignmentReturn(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_INVALID; ?></p>');
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
                    $(".btnBackVendorConsignmentReturn").click();
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorConsignmentReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('VendorConsignmentReturn', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['VendorConsignmentReturn']['id']; ?>" name="data[vendor_consignment_return_id]" />
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
<br/>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/hand.png" alt=""/>
        <span class="txtSaveRVendorConsignmentReturn"><?php echo ACTION_PICK; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>