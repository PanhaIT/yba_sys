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
        <?php
        if($allowReceive && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        $(".btnReceiveConsignmentReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackConsignmentReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/<?php echo $this->data['ConsignmentReturn']['id']; ?>");
        });
        <?php
        }
        if($allowVoid && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        $(".btnDeleteConsignmentReturn").click(function(event) {
            event.preventDefault();
            var obj = $(this);
            var id = '<?php echo $this->data['ConsignmentReturn']['id']; ?>';
            var name = '<?php echo $this->data['ConsignmentReturn']['code']; ?>';
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
                                $(".btnBackConsignmentReturn").click();
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
        if($allowPrint && $this->data['ConsignmentReturn']['status'] > 0){
        ?>
        $(".btnPrintInvoiceConsignmentReturn").click(function(event) {
            event.preventDefault();
            var id = '<?php echo $this->data['ConsignmentReturn']['id']; ?>';
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
        if($allowEdit && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        $(".btnEditConsignmentReturn").click(function(event){
            event.preventDefault();
            // Back Dashboard
            var rightPanel = $(".btnBackConsignmentReturn").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['ConsignmentReturn']['id']; ?>");
        });
        <?php
        }
        ?>
    });
    
    function refreshViewConsignmentReturn(){
        var rightPanel = $("#viewLayoutConsignmentReturn").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/<?php echo $this->data['ConsignmentReturn']['id']; ?>");
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackConsignmentReturn">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowVoid && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <span><?php echo ACTION_VOID; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <span><?php echo ACTION_EDIT; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowReceive && $this->data['ConsignmentReturn']['status'] == 1){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnReceiveConsignmentReturn">
                <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
                <span><?php echo ACTION_RECEIVE; ?></span>
            </a>
        </div>
        <?php
        }
        if($allowPrint && $this->data['ConsignmentReturn']['status'] > 0){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnPrintInvoiceConsignmentReturn">
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