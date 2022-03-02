<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

$this->element('check_access');
$allowEdit  = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowVoid  = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
include("includes/function.php");
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackLandingCost").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableLandingCost.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        <?php
        if($allowAging && $this->data['LandingCost']['status'] > 0){
        ?>
        $(".btnReceiveLandingCost").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackLandingCost").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/<?php echo $this->data['LandingCost']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $this->data['LandingCost']['status'] == 1){
        ?>
        $(".btnDeleteLandingCost").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['LandingCost']['id']; ?>';
            var name = '<?php echo $this->data['LandingCost']['code']; ?>';
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
                                $(".btnBackLandingCost").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Consignment Return', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Consignment Return', 'Delete', 1, '');
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
        if($allowEdit && $this->data['LandingCost']['status'] == 1){
        ?>
        $(".btnEditLandingCost").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackLandingCost").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['LandingCost']['id']; ?>");
        });
        <?php
        }
        if($allowClose){
        ?>
        $(".btnLandingCostClose").click(function(event){
            event.preventDefault();
            var id = $(this).attr('rel');
            var name = $(this).attr('name');
            $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
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
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                oCache.iCacheLower = -1;
                                oTableLandingCost.fnDraw(false);
                                // alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                    createSysAct('Landed Cost', 'Close', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Landed Cost', 'Close', 1, '');
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
    });
    
    function refreshViewLandingCost(){
        var rightPanel = $("#viewLayoutLandingCost").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['LandingCost']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackLandingCost">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['LandingCost']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteLandingCost">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['LandingCost']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditLandingCost">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowAging && $this->data['LandingCost']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveLandingCost">
                <img src="<?php echo $this->webroot; ?>img/button/aging.png" alt=""/>
                <span><?php echo TABLE_PAY; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowClose && $this->data['LandingCost']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnLandingCostClose">
                <img src="<?php echo $this->webroot; ?>img/button/close.png" alt=""/>
                <span><?php echo ACTION_CLOSE; ?></span>
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
    <legend><?php __(MENU_LANDED_COST_INFO); ?></legend>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 12%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo MENU_BRANCH; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_CODE; ?> :</td>
                    <td style="width: 15%; font-size: 12px;"><?php echo $this->data['LandingCost']['code']; ?></td>
                    <td style="width: 15%; font-size: 12px;"><?php echo TABLE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['LandingCost']['date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['Vendor']['vendor_code']." - ".$this->data['Vendor']['name']; ?></td>
                    <td style="font-size: 12px;">A/P :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['ChartAccount']['account_codes']." - ".$this->data['ChartAccount']['account_description']; ?></td>
                    <td style="font-size: 12px;"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['PurchaseOrder']['po_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_REFERENCE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['LandingCost']['reference']; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo MENU_LANDED_COST_TYPE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['LandedCostType']['name']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($this->data['LandingCost']['note']); ?></td>
                </tr>
            </table>
        </div>
    <?php
        if (!empty($landingCostDetails)) {
    ?>
    <div>
        <fieldset>
            <legend><?php echo TABLE_PRODUCT; ?></legend>
            <table class="table" >
                <tr>
                    <th class="first"><?php echo TABLE_NO ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_SKU; ?></th>
                    <th><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th><?php echo TABLE_QTY ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo TABLE_UNIT_COST ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo TABLE_LANDED_COST; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                </tr>
                <?php
                $index = 0;
                $totalPrice = 0;
                foreach ($landingCostDetails as $landingCostDetail) {
                    $productName = $landingCostDetail['Product']['name'];
                    $landedCost  = number_format($landingCostDetail['LandingCostDetail']['landed_cost'], $rowOption[0]);
                    $totalPrice += $landingCostDetail['LandingCostDetail']['landed_cost'];
                ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo $landingCostDetail['Product']['barcode']; ?></td>
                        <td><?php echo $landingCostDetail['Product']['code']; ?></td>
                        <td><?php echo $productName; ?></td>
                        <td><?php echo $landingCostDetail['LandingCostDetail']['qty']; ?></td>
                        <td><?php echo $landingCostDetail['Uom']['abbr']; ?></td>
                        <td><?php echo number_format($landingCostDetail['LandingCostDetail']['unit_cost'], $rowOption[0]); ?></td>
                        <td style="text-align: right"><?php echo $landedCost; ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
                    <td style="text-align: right" ><?php echo number_format($totalPrice, $rowOption[0]); ?></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['LandingCost']['total_amount'], $rowOption[0]); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
</fieldset>    