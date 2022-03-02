<?php
include("includes/function.php");
echo $this->element('prevent_multiple_submit'); 
echo $this->Form->create('PurchaseReturn', array('inputDefaults' => array('div' => false, 'label' => false))); 
echo $this->Form->hidden('pr_id',array('name'=>'data[pr_id]', 'value' => $purchase_returns['PurchaseReturn']['id']));
?>
<fieldset>
    <legend><?php __(MENU_PURCHASE_RETURN_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="10">
        <tr>
            <td width="15%"><?php echo PURCHASE_RETURN_CODE; ?> :</td>
            <td width="25%">
                <div class="inputContainer" style="width:100%">
                    <?php echo $purchase_returns['PurchaseReturn']['pr_code']; ?>
                </div>
            </td>
            <td width="15%"><?php echo TABLE_VENDOR; ?> :</td>
            <td width="25%">
                <div class="inputContainer" style="width:100%">
                    <?php echo $purchase_returns['Vendor']['vendor_code']."-".$purchase_returns['Vendor']['name']; ?>
                </div>
            </td>
            <td><?php echo TABLE_COMPANY; ?> :</td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $purchase_returns['Company']['name']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_ORDER_DATE; ?> :</td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo dateShort($purchase_returns['PurchaseReturn']['order_date']); ?>                    
                </div>
            </td>
            <td><?php echo TABLE_LOCATION_GROUP; ?> :</td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $purchase_returns['LocationGroup']['name']; ?>
                </div>
            </td>
            <td><?php echo TABLE_LOCATION; ?> :</td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $purchase_returns['Location']['name']; ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<?php
    if (!empty($purchaseReturnDetails)) {
?>
<div>
<fieldset>
    <legend><?php echo TABLE_PRODUCT; ?></legend>
    <table class="table" >
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_NAME ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_QTY ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 160px !important;"><?php echo ACTION_ACTION; ?></th>
        </tr>
<?php
    $index = 0;
    $productIds = array();
    foreach ($purchaseReturnDetails as $purchaseReturnDetail) {
?>
        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
            <td>
                <?php 
                echo $purchaseReturnDetail['Product']['name'];
                ?>
            </td>
            <td style="text-align: right">
            <?php 
                echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['qty'], 2); 
                if($purchaseReturnDetail['Product']['is_expired_date'] == 1){
                    $sql = mysql_query("SELECT id FROM purchase_return_receives WHERE product_id = ".$purchaseReturnDetail['Product']['id']." AND purchase_return_id = ".$purchaseReturnDetail['PurchaseReturn']['id']." AND purchase_return_detail_id = ".$purchaseReturnDetail['PurchaseReturnDetail']['id']);
                    if(!mysql_num_rows($sql)){
                        $productReceived = 1;
                    }else{
                        $productReceived = 0;   
                    }
                }else{
                    $productReceived = 1;
                }
                
                $qty = $purchaseReturnDetail['PurchaseReturnDetail']['qty'] * $purchaseReturnDetail['PurchaseReturnDetail']['conversion'];
                if($productReceived == 0){
                    $qty = 0;
                }
            ?>
            </td>
            <td><?php echo $purchaseReturnDetail['Uom']['name']; ?></td>
            <td>
                <?php
                if($productReceived == 1){
                ?>
                <a href="#" class="btnReceiveProductPBC" product="<?php echo $purchaseReturnDetail['PurchaseReturnDetail']['product_id']; ?>" rel="<?php echo $purchaseReturnDetail['PurchaseReturnDetail']['id']; ?>"><img alt="Receive Product" onmouseover="Tip('Receive Product')" src="<?php echo $this->webroot; ?>img/button/receiving.png" /></a>
                <img alt="Returned Ready" class="alertReadyProductPBC" onmouseover="Tip('Returned  Ready')" src="<?php echo $this->webroot; ?>img/button/active.png" style="display: none;" />
                <?php
                }else{
                ?>
                <img alt="Returned Ready" class="alertReadyProductPBC" onmouseover="Tip('Returned  Ready')" src="<?php echo $this->webroot; ?>img/button/active.png" />
                <?php
                }
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
<br/>
<div class="buttons">
    <a href="#" class="positive btnBackPurchaseReturn">
        <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
        <?php echo ACTION_BACK; ?>
    </a>
</div>
<div class="buttons">
    <button type="submit" class="positive pickPR">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveRPurchaseReturn"><?php echo ACTION_PICK; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#PurchaseReturnReceiveForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveRPurchaseReturn").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result == 'error'){
                    errorPickPR();
                }else{
                    $(".btnBackPurchaseReturn").dblclick();
                    if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                        createSysAct('Bill Return', 'Receive', 2, result);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        createSysAct('Bill Return', 'Receive', 1, '');
                        // alert message
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    }
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
        
        $(".btnBackPurchaseReturn").dblclick(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePurchaseReturn.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide("slide", { direction: "right" }, 500, function(){
                leftPanel.show();
                rightPanel.html("");
            });
        });
        
        $(".btnReceiveProductPBC").click(function(e){
            e.preventDefault();
            var billReturnDetailId = $(this).attr("rel");
            var locationGroupId    = <?php echo $purchase_returns['PurchaseReturn']['location_group_id']; ?>;
            var productId          = $(this).attr("product");
            var objRow             = $(this).closest("tr");
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProduct/"+billReturnDetailId+"/"+locationGroupId,
                data:   "",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 900,
                        height: 600,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".dataTables_length").hide();
                            $(".dataTables_paginate").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkPickProductBillReturn[]']:checked").val() != undefined){
                                    if(parseFloat($("#total_order").text()) == 0){
                                        $(this).dialog("close");
                                        var parameter = "";
                                        parameter += "&data[bill_return_id]=<?php echo $purchase_returns['PurchaseReturn']['id']; ?>";
                                        parameter += "&data[bill_return_detail_id]="+billReturnDetailId;
                                        parameter += "&data[product_id]="+productId;
                                        $("input[name='chkPickProductBillReturn[]']:checked").each(function(){
                                            var lotsNum = $(this).attr("lots");
                                            var expired = $(this).attr("expired");
                                            var qtyPick = $(this).closest("tr").find(".qtyPick").val();
                                            var locationId = $(this).attr("location-id");
                                            var uomId = $(this).attr("uom");
                                            parameter  += "&uom[]="+uomId;
                                            parameter  += "&qty_pick[]="+qtyPick;
                                            parameter  += "&location_id[]="+locationId;
                                            parameter  += "&expired_date[]="+expired;
                                            parameter  += "&lots_number[]="+lotsNum;
                                        });
                                        $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProductSave",
                                            data: parameter,
                                            dataType: "json",
                                            position:'center',
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(msg){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                if(msg.success == 1){
                                                    objRow.find(".btnReceiveProductPBC").hide();
                                                    objRow.find(".alertReadyProductPBC").show();
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                                    $("#dialog").dialog({
                                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                                        resizable: false,
                                                        modal: true,
                                                        position:'center',
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
                                                } else if(msg.ready == 1){
                                                    $("#PurchaseReturnReceiveForm").parent().load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/pickSlip/<?php echo $purchase_returns['PurchaseReturn']['id']; ?>");
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                                                    $("#dialog").dialog({
                                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                                        resizable: false,
                                                        modal: true,
                                                        position:'center',
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
                                    }else{
                                        $("#warningPickProductPBC").show();
                                    }
                                }else{
                                    $("#warningPickProductPBC").show();
                                }
                            }
                        }
                    });
                }
            });
        });
    });
    
    function errorPickPR(){
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
                    $(this).dialog("close");
                }
            }
        });
    }
</script>