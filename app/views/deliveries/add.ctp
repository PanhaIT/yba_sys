<script type="text/javascript">
    $(document).ready(function(){
        $("#DeliveryAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#btnGetSaleDNList").click(function(){
            loadSaleDelivery();
        });
        
        $("#DeliveryCgroups").change(function(){
            getCustomerByCgroup();
        });
        
        $("#DeliveryAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtDeliverySave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                var result = false;
                $(".checkSO").each(function(){
                    if($(this).attr("checked") == true){
                        result = true;
                    }
                });
                
                if(result == true){
                    $(".checkSO").each(function(){
                        if($(this).attr("checked") == false){
                            var saleOrder = $(this).closest("tr").find("input[name='data[Delivery][sales_order_id][]']").val();
                            $(this).closest("tr").find("input[name='data[Delivery][product_id][]']").remove();
                            $(this).closest("tr").find("input[name='data[Delivery][sales_order_id][]']").remove();
                            $(this).closest("tr").find("input[name='data[Delivery][qty][]']").remove();
                            $(this).closest("tr").find("input[name='data[Delivery][sales_order_detail_id][]']").remove();
                            $("input[name='data[Delivery][sales_order_id][]']").each(function(){
                                if($(this).val() == saleOrder){
                                    $(this).closest("tr").find("input[name='data[Delivery][product_id][]']").remove();
                                    $(this).closest("tr").find("input[name='data[Delivery][qty][]']").remove();
                                    $(this).closest("tr").find("input[name='data[Delivery][sales_order_detail_id][]']").remove();
                                    $(this).closest("tr").find("input[name='data[Delivery][sales_order_id][]']").remove();
                                }
                            });
                        }
                    });
                }
                if(result ==  true){
                    $("#DeliveryDate").datepicker("option", "dateFormat", "yy-mm-dd");
                }
                return result;
            }, 
            success: function(result) {
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                if(result == 'error' || result == '0'){
                    errorSaveDNSO();
                }else{
                    var deliveryId = $.trim(result);
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printReceiptDN" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?></span></button><button type="submit" class="positive printInvoiceBarcodeDN" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE_BARCODE; ?></span></button><button type="submit" class="positive printReceiptParentDN" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_INVOICE; ?> Total Parent</span></button><?php if($allowPrintSaleFree){ ?><button type="submit" class="positive printDNSaleFree" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo INVOICE_DELIVERY_NOTE_SALE_FREE; ?></span></button><?php } ?></div> ');
                    $(".printReceiptDN").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ; ?>/deliveries/printDelivery/"+deliveryId,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
                    $(".printInvoiceBarcodeDN").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ; ?>/deliveries/printDeliveryBarcode/"+deliveryId,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
                    $(".printReceiptParentDN").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ; ?>/deliveries/printDeliveryParent/"+deliveryId,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
                     $(".printDNSaleFree").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ; ?>/deliveries/printDeliverySaleFree/"+deliveryId,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
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
                        close: function(){
                            $(this).dialog({close: function(){}});
                            $(this).dialog("close");
                            var rightPanel=$("#DeliveryAddForm").parent();
                            var leftPanel=rightPanel.parent().find(".leftPanel");
                            rightPanel.hide();rightPanel.html("");
                            leftPanel.show("slide", { direction: "left" }, 500);
                            oCache.iCacheLower = -1;
                            oTableDelivery.fnDraw(false);
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
        
        $(".btnBackDelviery").click(function(event){
            event.preventDefault();
            $(this).dialog("close");
            var rightPanel=$("#DeliveryAddForm").parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableDelivery.fnDraw(false);
        });
        
        $('#DeliveryDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
    });
    
    function errorSaveDNSO(){
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
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                var rightPanel=$("#DeliveryAddForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableDelivery.fnDraw(false);
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function loadSaleDelivery(){
        var user = "<?php echo $user['User']['id']; ?>";
        var cgroup = $("#DeliveryCgroups").val();
        var customer = $("#DeliveryCustomerId").val();
        var company  = $("#DeliveryCompanyId").val();
        var location = $("#DeliveryLocationId").val();
        if(company != "" && location != ""){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getDeliveryList/"+location+"/"+user+"/"+company+"/"+cgroup+"/"+customer,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#listSoDelivery").html(result);
                    if($(".tableDelivery tr").length == 1){
                        $(".btndelivery").hide();
                    }else{
                        $(".btndelivery").show();
                    }
                }
            });
        }
    }
    
    function getCustomerByCgroup(){
        var cgroup = $("#DeliveryCgroups").val();
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getCustomerByCgroup/"+cgroup,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#DeliveryCustomerId").html(result);
                loadSaleDelivery();
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackDelviery">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Delivery', array('url' => array('controller' => 'deliveries', 'action' => 'delivery'), 'inputDefaults' => array('div' => false, 'label' => false))); ?>
 <fieldset>
    <legend><?php echo SALES_ORDER_DELIVERY_NOTE; ?></legend>
    <table width="100%">
        <tr>
            <td>
                <label for="DeliveryDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            </td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->text('date', array('style'=>'width:190px', 'class'=>'validate[required]', 'readonly' => true)); ?>
                </div>
            </td>
            <td>
                <label for="DeliveryCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label>
            </td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->input('company_id', array('empty' => INPUT_SELECT)); ?>
                </div>
            </td>
            <td>
                <label for="DeliveryLocationId"><?php echo TABLE_LOCATION; ?> <span class="red">*</span> :</label>
            </td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <?php echo $this->Form->input('location_id', array('empty' => INPUT_SELECT)); ?>
                    <input type="button" value="GO" id="btnGetSaleDNList" />
                </div>
                
            </td>
        </tr>
        <tr>
            <td>
                <label for="DeliveryCgroups"><?php echo TABLE_CUSTOMER_GROUP; ?>:</label>
            </td>
            <td>
                <?php echo $this->Form->input('cgroups', array('empty' => INPUT_SELECT)); ?>
            </td>
            <td>
                <label for="DeliveryCustomerId"><?php echo TABLE_CUSTOMER; ?>:</label>
            </td>
            <td>
                <?php echo $this->Form->input('customer_id', array('empty' => INPUT_SELECT)); ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="DeliveryNote"><?php echo TABLE_NOTE; ?>:</label>
            </td>
            <td colspan="0">
                <?php echo $this->Form->text('note', array('style' => 'width: 200px;')); ?>
            </td>
        </tr>
    </table>
</fieldset>
<br/>
<div id="listSoDelivery">
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="submit" class="positive btndelivery" style="display: none;" >
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtDeliverySave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>


