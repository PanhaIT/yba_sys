<?php
$this->element('check_access');
$allowEdit  = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid  = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
include("includes/function.php");
$rand = rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
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
        <?php
        if($allowReceive && $this->data['Consignment']['status'] == 1){
        ?>
        $(".btnReceiveConsignment").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackConsignment").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['Consignment']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $this->data['Consignment']['status'] == 1){
        ?>
        $(".btnDeleteConsignment").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['Consignment']['id']; ?>';
            var name = '<?php echo $this->data['Consignment']['code']; ?>';
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
                                $(".btnBackConsignment").click();
                                // Alert message
                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                    createSysAct('Consignment', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Consignment', 'Delete', 1, '');
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
        if($allowPrint && $this->data['Consignment']['status'] > 0){
        ?>
        $(".btnPrintInvoiceConsignment").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['Consignment']['id']; ?>';
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
        if($allowEdit && $this->data['Consignment']['status'] == 1){
        ?>
        $(".btnEditConsignment").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackConsignment").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['Consignment']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewConsignment(){
        var rightPanel = $("#viewLayoutConsignment").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['Consignment']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackConsignment">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['Consignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['Consignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && $this->data['Consignment']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/hand.png" alt=""/>
                <span><?php echo ACTION_PICK; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['Consignment']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceConsignment">
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
                    <th><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
                    <th><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></th>
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
                        <td style="text-align: right"><?php echo $unit_price; ?></td>
                        <td style="text-align: right"><?php echo number_format($subTotal, 2); ?></td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" colspan="7" style="text-align: right" ><b><?php echo TABLE_TOTAL ?></b></td>
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
                <td class="first" style="border-bottom: none; border-left: none;text-align: right; width: 90%;"><b style="font-size: 17px;"><?php echo TABLE_TOTAL; ?></b></td>
                <td style="text-align: right; font-size: 17px;"><?php echo number_format($this->data['Consignment']['total_amount'], 2); ?> <?php echo $this->data['CurrencyCenter']['symbol']; ?></td>
            </tr>
        </table>
    </div>
</fieldset>    