<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ConsignmentReturnReceiveForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ConsignmentReturnReceiveForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {                
                $(".txtSaveRConsignmentReturn").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error > 0){
                    errorPickConsignmentReturn();
                }else{                    
                    $(".btnBackConsignmentReturn").click();
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
        $(".btnBackConsignmentReturn").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableConsignmentReturn.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
    });
    
    function errorPickConsignmentReturn(){
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
                    $(".btnBackConsignmentReturn").click();
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackConsignmentReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('ConsignmentReturn', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['ConsignmentReturn']['id']; ?>" name="data[consignment_return_id]" />
<fieldset>
    <legend><?php __(MENU_CUSTOMER_CONSIGNMENT_RETURN_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 12%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 15%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 15%; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['ConsignmentReturn']['date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONTACT_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['CustomerContact']['contact_name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONSIGNMENT_RETURN_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['ConsignmentReturn']['code']; ?></td>
                </tr>
                <?php
                $consignmentCode  = "";
                if(!empty($this->data['ConsignmentReturn']['consignment_id'])){
                    $sqlConsignment = mysql_query("SELECT code FROM consignments WHERE id = ".$this->data['ConsignmentReturn']['consignment_id']);
                    while($rowConsignment=mysql_fetch_array($sqlConsignment)){
                        $consignmentCode = $rowConsignment['code'];
                    }
                }
                ?>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CONSIGNMENT_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $consignmentCode; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['ConsignmentReturn']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
        if (!empty($consignmentReturnDetails)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_SKU; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th><?php echo TABLE_NOTE; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
                    <th><?php echo TABLE_LOTS_NO ?></th>
                    <th><?php echo TABLE_EXPIRED_DATE; ?></th>
                </tr>
                <?php
                $index = 0;
                foreach ($consignmentReturnDetails as $consignmentReturnDetail) {
                    // Check Name With Customer
                    $productName = $consignmentReturnDetail['Product']['name'];
                    $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$consignmentReturnDetail['Product']['id']." AND customer_id = ".$this->data['Customer']['id']." ORDER BY created DESC LIMIT 1");
                    if(@mysql_num_rows($sqlProCus)){
                        $rowProCus = mysql_fetch_array($sqlProCus);
                        $productName = $rowProCus['name'];
                    }
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $consignmentReturnDetail['Product']['code']; ?></td>
                        <td><?php echo $productName; ?></td>
                        <td><?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['note']; ?></td>
                        <td style="text-align: right"><?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['qty']; ?></td>
                        <td><?php echo $consignmentReturnDetail['Uom']['name']; ?></td>
                        <td style="text-align: right">
                            <?php
                            $lotsNumber = '';
                            if($consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != '0' && $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != ''){
                                $lotsNumber = $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'];
                            }
                            echo $lotsNumber;
                            ?>
                        </td>
                        <td style="text-align: right">
                            <?php
                            $expriryDate = '';
                            if($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '' && $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '0000-00-00'){
                                $expriryDate = dateShort($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']);
                            }
                            echo $expriryDate;
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
<br/>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
        <span class="txtSaveRConsignmentReturn"><?php echo ACTION_RECEIVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>