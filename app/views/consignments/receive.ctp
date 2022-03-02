<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ConsignmentReceiveForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ConsignmentReceiveForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {                
                $(".txtSaveRConsignment").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error > 0){
                    errorPickConsignment();
                }else{                    
                    $(".btnBackConsignment").click();
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
        $(".btnBackConsignment").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableConsignment.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        
        $(".btnPickConsignment").click(function(e){
            e.preventDefault();
            var consignmentDetailId = $(this).attr("rel");
            var locationGroupId    = <?php echo $this->data['Consignment']['location_group_id']; ?>;
            var productId          = $(this).attr("product");
            var objRow             = $(this).closest("tr");
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProduct/"+consignmentDetailId+"/"+locationGroupId,
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
                                if($("input[name='chkPickProductConsignment[]']:checked").val() != undefined){
                                    if(parseFloat($("#total_order").text()) == 0){
                                        $(this).dialog("close");
                                        var parameter = "";
                                        parameter += "&data[consignment_id]=<?php echo $this->data['Consignment']['id']; ?>";
                                        parameter += "&data[consignment_detail_id]="+consignmentDetailId;
                                        parameter += "&data[product_id]="+productId;
                                        $("input[name='chkPickProductConsignment[]']:checked").each(function(){
                                            var lotsNum = $(this).attr("lots");
                                            var expired = $(this).attr("expired");
                                            var qtyPick = $(this).closest("tr").find(".qtyPick").val();
                                            var locationId = $(this).attr("location-id");
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
                                                    objRow.find(".btnPickConsignment").hide();
                                                    objRow.find(".alertReadyPickConsignment").show();
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
                                                    $("#ConsignmentReceiveForm").parent().load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['Consignment']['id']; ?>");
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
                                        $("#warningPickProductConsignment").show();
                                    }
                                }else{
                                    $("#warningPickProductConsignment").show();
                                }
                            }
                        }
                    });
                }
            });
        });
        
    });
    
    function errorPickConsignment(){
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
                    $(".btnBackConsignment").click();
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackConsignment">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Consignment', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" value="<?php echo $this->data['Consignment']['id']; ?>" name="data[consignment_id]" />
<fieldset>
    <legend><?php __(MENU_CUSTOMER_CONSIGNMENT_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['LocationGroup']['name']; ?></td>
                    <td style="width: 12%; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['Consignment']['date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONTACT_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['CustomerContact']['contact_name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONSIGNMENT_CODE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Consignment']['code']; ?></td>
                </tr>
                <?php
                $salesRepName  = "";
                if(!empty($this->data['Consignment']['sales_rep_id'])){
                    $sqlEmployee = mysql_query("SELECT id, name FROM employees WHERE id = ".$this->data['Consignment']['sales_rep_id']);
                    while($rowEmployee=mysql_fetch_array($sqlEmployee)){
                        $salesRepName = $rowEmployee['name'];
                    }
                }
                ?>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_SALES_REP; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesRepName; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['Consignment']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
        if (!empty($consignmentDetails)) {
    ?>
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
                <th style="width: 10%;"><?php echo ACTION_ACTION; ?></th>
            </tr>
            <?php
            $index = 0;
            $totalPrice = 0;
            $subTotal = 0;
            foreach ($consignmentDetails as $consignmentDetail) {
                // Check Name With Customer
                $productName = $consignmentDetail['Product']['name'];
                $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$consignmentDetail['Product']['id']." AND customer_id = ".$this->data['Customer']['id']." ORDER BY created DESC LIMIT 1");
                if(@mysql_num_rows($sqlProCus)){
                    $rowProCus = mysql_fetch_array($sqlProCus);
                    $productName = $rowProCus['name'];
                }
                $unit_price = number_format($consignmentDetail['ConsignmentDetail']['unit_price'], 2);
                $subTotal  = $consignmentDetail['ConsignmentDetail']['total_price'];
                $totalPrice += $subTotal;
            ?>
                <tr>
                    <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                    <td><?php echo $consignmentDetail['Product']['code']; ?></td>
                    <td><?php echo $productName; ?></td>
                    <td><?php echo $consignmentDetail['ConsignmentDetail']['note']; ?></td>
                    <td style="text-align: right"><?php echo $consignmentDetail['ConsignmentDetail']['qty']; ?></td>
                    <td><?php echo $consignmentDetail['Uom']['name']; ?></td>
                    <td>
                        <?php
                        $sqlCheck = mysql_query("SELECT id FROM consignment_receives WHERE consignment_detail_id = ".$consignmentDetail['ConsignmentDetail']['id']);
                        if(!mysql_num_rows($sqlCheck)){
                        ?>
                            <a href="#" class="btnPickConsignment" product="<?php echo $consignmentDetail['ConsignmentDetail']['product_id']; ?>" rel="<?php echo $consignmentDetail['ConsignmentDetail']['id']; ?>"><img alt="Pick Product" onmouseover="Tip('Pick Product')" src="<?php echo $this->webroot; ?>img/button/hand.png" /></a>
                            <img alt="Pick Ready" class="alertReadyPickConsignment" onmouseover="Tip('Pick Ready')" style="display: none;" src="<?php echo $this->webroot; ?>img/button/active.png" />
                        <?php
                        }else{
                        ?>
                            <img alt="Pick Ready" class="alertReady" onmouseover="Tip('Pick Ready')" src="<?php echo $this->webroot; ?>img/button/active.png" />
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
    <?php
    }
    ?>
</fieldset>  
<br/>
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/hand.png" alt=""/>
        <span class="txtSaveRConsignment"><?php echo ACTION_PICK_ALL; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>