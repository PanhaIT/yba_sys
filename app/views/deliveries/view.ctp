<?php
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Action Button Back
        $(".btnBackDelivery").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableDelivery.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
        });
        $(".btnReprintInvoicePickSlip").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoicePickSlip/"+$(this).attr("rel"),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="#" class="positive btnBackDelivery">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_DELIVERY_MANAGEMENT); ?></legend>
    <div style="float: right; width:30px;">
            <?php
                echo "<a href='#' class='btnReprintInvoicePickSlip' rel='{$salesOrder['SalesOrder']['delivery_id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT_DELIVERY_NOTE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
            ?>
        </div>
    <div style="float: right; width:30px;">
        </div>
        <div>
            <table style="width: 100%;" cellpadding="5">
                <tr>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $salesOrder['Company']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $salesOrder['LocationGroup']['name']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
                    <td style="width: 18%; font-size: 12px;"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
                    <td style="width: 10%; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
                    <td style="font-size: 12px;"><?php echo dateShort($this->data['SalesOrder']['order_date']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['Customer']['customer_code']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['Customer']['name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CONTACT_NAME; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['CustomerContact']['contact_name']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_PO; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $this->data['SalesOrder']['customer_po_number']; ?></td>
                </tr>
                <?php
                $salesRepName  = "";
                $collectorName = "";
                $sqlEmployee = mysql_query("SELECT id, name FROM employees");
                while($rowEmployee=mysql_fetch_array($sqlEmployee)){
                    if($rowEmployee['id'] == $this->data['SalesOrder']['sales_rep_id']){
                        $salesRepName = $rowEmployee['name'];
                    }
                    if($rowEmployee['id'] == $this->data['SalesOrder']['collector_id']){
                        $collectorName = $rowEmployee['name'];
                    }
                }
                $sqlAr = mysql_query("SELECT CONCAT(account_codes, ' - ', account_description) FROM chart_accounts WHERE id = ".$salesOrder['SalesOrder']['ar_id']);
                @$rowAr = mysql_fetch_array($sqlAr);
                ?>
                <tr>
                    <td style="font-size: 12px;"><?php echo TABLE_QUOTATION_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['SalesOrder']['quotation_number']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_SALE_ORDER_NUMBER; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesOrder['SalesOrder']['order_number']; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_SALES_REP; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $salesRepName; ?></td>
                    <td style="font-size: 12px;"><?php echo TABLE_COLLECTOR; ?> :</td>
                    <td style="font-size: 12px;"><?php echo $collectorName; ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;">A/R :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo @$rowAr[0]; ?></td>
                    <td style="font-size: 12px; vertical-align: top;">Project :</td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo $salesOrder['SalesOrder']['project']; ?></td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($salesOrder['SalesOrder']['memo']); ?></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; vertical-align: top;">DN <?php echo TABLE_CONTACT_NAME ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3">
                        <?php 
                        $dnContact = '';
                        if(!empty($salesOrder['Delivery']['customer_contact_id'])){
                            $sqlContact = mysql_query("SELECT contact_name FROM customer_contacts WHERE id = ".$salesOrder['Delivery']['customer_contact_id']);
                            $rowContact = mysql_fetch_array($sqlContact);
                            $dnContact  = $rowContact[0];
                        }
                        echo $dnContact; 
                        ?>
                    </td>
                    <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_SHIP_TO ?> :</td>
                    <td style="font-size: 12px; vertical-align: top;" colspan="3"><?php echo nl2br($salesOrder['Delivery']['ship_to']); ?></td>
                </tr>
            </table>
        </div>
    <?php
            if (!empty($deliveryDetails)) {
    ?>
                <div>
                    <fieldset>
                        <legend><?php echo TABLE_PRODUCT; ?></legend>
                        <table class="table" >
                            <tr>
                                <th class="first"><?php echo TABLE_NO; ?></th>
                                <th><?php echo TABLE_SKU; ?></th>
                                <th><?php echo TABLE_BARCODE; ?></th>
                                <th><?php echo TABLE_NAME; ?></th>
                                <th><?php echo TABLE_LOCATION; ?></th>
                                <th style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                                <th><?php echo TABLE_EXPIRED_DATE; ?></th>
                                <th><?php echo TABLE_QTY ?></th>
                            </tr>
                <?php
                $index = 0;
                foreach ($deliveryDetails as $deliveryDetail) {
                ?>
                    <tr class="rowListDN">
                        <td class="first" style="text-align: right;">
                            <?php echo++$index; ?>
                        </td>
                        <td><?php echo $deliveryDetail['Product']['code']; ?></td>
                        <td><?php echo $deliveryDetail['Product']['barcode']; ?></td>
                        <td><?php echo $deliveryDetail['Product']['name']; ?></td>
                        <td><?php echo $deliveryDetail['Location']['name']; ?></td>
                        <td style="text-align: right; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo $deliveryDetail['DeliveryDetail']['lots_number']; ?></td>
                        <td style="text-align: right"><?php if($deliveryDetail['DeliveryDetail']['expired_date'] != '0000-00-00'){ echo dateShort($deliveryDetail['DeliveryDetail']['expired_date']); } ?></td>
                        <td>
                            <?php 
                            $small_label = "";
                            $small_uom   = $deliveryDetail['Product']['small_val_uom'];
                            $sqlUomSm    = mysql_query("SELECT abbr FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = {$deliveryDetail['Product']['price_uom_id']} AND is_small_uom = 1 LIMIT 1)");
                            if(mysql_num_rows($sqlUomSm)){
                                $rowUomSm    = mysql_fetch_array($sqlUomSm);
                                $small_label = $rowUomSm['abbr'];
                            }
                            $sqlUomMain = mysql_query("SELECT abbr FROM uoms WHERE id = {$deliveryDetail['Product']['price_uom_id']};");
                            $rowUomMain = mysql_fetch_array($sqlUomMain);
                            $main_uom   = $rowUomMain['abbr'];
                            echo showTotalQty($deliveryDetail['DeliveryDetail']['total_qty'], $main_uom, $small_uom, $small_label);
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