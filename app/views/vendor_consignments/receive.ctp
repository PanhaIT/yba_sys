<?php
include("includes/function.php");
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#VendorConsignmentReceiveForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#VendorConsignmentReceiveForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {                
                $(".txtSaveRVendorConsignment").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error > 0){
                    errorPickVendorConsignment();
                }else{                    
                    $(".btnBackVendorConsignment").click();
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
    });
    
    function errorPickVendorConsignment(){
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
                    $(".btnBackVendorConsignment").click();
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorConsignment">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('VendorConsignment', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $vendorConsignment['VendorConsignment']['id']; ?>" name="data[vendor_consignment_id]" />
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
<br/>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
        <span class="txtSaveRVendorConsignment"><?php echo ACTION_RECEIVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>