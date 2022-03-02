<?php
include("includes/function.php");
$img = $salesOrder['Company']['photo'];
mysql_query("UPDATE `sales_orders` SET `is_print`= 0 WHERE  `id`=" . $salesOrder['SalesOrder']['id'] . " LIMIT 1;");
?>
<style type="text/css" media="screen">
    div#header_waiting { display: none;}
    div.wrap-print-slip { width:310px;}
    div#print-footer {display: none;} 
    b{font-size:12px;}
</style> 
<style type="text/css" media="print">
    div#header_waiting { width:100%; text-align: center; margin: 0px auto; display: block; padding-bottom: 20px; padding-top: 0px; page-break-after: always}
    div.wrap-print-slip { width:100%; }
    #btnDisappearPrint { display: none; }
    div#print-footer {display: block; margin-top: 10px; width:100%} 
</style>

<div class="print_doc" style="width: 350px;">
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; text-align: center;">
                <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $img; ?>" style="height: 40px;" />
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center;">
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top; text-align: center;">
                            <div style="font-size: 18px; font-weight: bold; text-align: center;">S Liquor</div>
                            <div style="font-size: 12px; text-align: center;">
                                <?php
                                echo nl2br($salesOrder['Branch']['address']);
                                ?>
                            </div>
                            <div style="font-size: 12px; text-align: center;">
                                Tel: <?php echo $salesOrder['Branch']['telephone']; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; white-space: nowrap; font-size: 17px; font-weight: bold;">
                <?php echo "Receipt"; ?>
            </td>
        </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 70px; font-size: 12px; line-height: 20px;">Invoice#: </td>
            <td style="width: 120px; font-size: 12px;"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
            <td style="text-align: right; font-size: 12px;"><?php echo dateShort($salesOrder['SalesOrder']['created'], 'd/m/Y H:i:s'); ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px; line-height: 25px;">Served By: </td>
            <td style="font-size: 12px;">
                <?php echo $salesOrder['User']['first_name']." ".$salesOrder['User']['last_name']; ?>
            </td>

            <td style="font-size: 12px; line-height: 25px;">Customer: </td>
            <td style="font-size: 12px;">
                <?php echo $salesOrder['Customer']['name']; ?>
            </td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width:100%; border-top: 0px solid black;">
            <tr>
                <th style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 12px; font-weight: bold; width: 40px; text-align: left; border-bottom:1px solid black">
                    <span style="display:block; font-size: 12px;">Qty</span>
                </th>
                <th style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 12px; font-weight: bold; text-align: left; border-bottom:1px solid black">
                    <span style="display:block; font-size: 12px;">Description</span>
                </th>
                <th style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 12px; font-weight: bold; text-align: left; border-bottom:1px solid black">
                    <span style="display:block; font-size: 12px;">Price</span>
                </th>
                <th style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 12px; font-weight: bold; text-align: left; border-bottom:1px solid black">
                    <span style="display:block; font-size: 12px;">Dis</span>
                </th>
                <th style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 12px; font-weight: bold; width: 55px; text-align: right; border-bottom:1px solid black">
                    <span style="display:block; font-size: 12px;">Total</span>
                </th>
            </tr>
            <?php
            $discount =0;
            if (!empty($salesOrderDetails)) {
                $index = 0;
                foreach ($salesOrderDetails as $salesOrderDetail) {
                    if($salesOrderDetail['SalesOrderDetail']['qty'] > 0){
                        $discount += $salesOrderDetail['SalesOrderDetail']['discount_amount'];
            ?>
                <tr>
                    <td style="border: none; font-size: 12px; text-align: left; vertical-align:top; line-height: 20px; padding-top: 3px;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty'], 0);  $index++;?></td>
                    <td style="border: none; font-size: 12px; padding-top: 3px;">
                        <?php echo $salesOrderDetail['Product']['name']; ?>
                    </td>
                    <td style="border: none; font-size: 12px; padding-top: 3px;">
                        <?php echo number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 2); ?>
                    </td>
                    <td style="border: none; font-size: 12px; padding-top: 3px;">
                        <?php echo number_format($salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); ?>
                    </td>
                    <td style="border: none; font-size: 12px; text-align: right; vertical-align:top; padding-top: 3px;"><div style="width:10px; float: left; font-size:12px; margin-left: 5px;">$</div><?php echo number_format($salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); ?></td>
                </tr>
            <?php
                    }
                    if($salesOrderDetail['SalesOrderDetail']['qty_free'] > 0){
            ?>
                <tr>
                    <td style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: left;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty_free'], 0); ?></td>
                    <td style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; font-size: 12px;"><?php echo $salesOrderDetail['Product']['name']; ?></td>
                    <td style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                    <td style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                    <td style="border-bottom:1px solid; padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                </tr>
            <?php
                    }
                }
            }
            if (!empty($salesOrderServices)) {
                $index = 0;
                foreach ($salesOrderServices as $salesOrderService) {
                    if($salesOrderService['SalesOrderService']['qty'] > 0){
                        $discount += $salesOrderService['SalesOrderService']['discount_amount'];
            ?>
                <tr>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: left;"><?php echo number_format($salesOrderService['SalesOrderService']['qty'], 0); $index++; ?></td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px;">
                        <?php echo $salesOrderService['Service']['name']; ?>
                    </td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px;">
                        <?php echo number_format($salesOrderService['SalesOrderService']['unit_price'], 2); ?>
                    </td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px;">
                        <?php echo number_format($salesOrderService['SalesOrderService']['discount_amount'], 2); ?>
                    </td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:11px; margin-left: 5px;">$</div><?php echo number_format($salesOrderService['SalesOrderService']['total_price'] - $salesOrderService['SalesOrderService']['discount_amount'], 2); ?></td>
                </tr>
            <?php
                    }
                    if($salesOrderService['SalesOrderService']['qty_free'] > 0){
            ?>
                <tr>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: left;"><?php echo number_format($salesOrderService['SalesOrderService']['qty_free'], 0); ?></td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px;"><?php echo $salesOrderService['Service']['name']; ?></td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                    <td style="border-bottom:1px solid;padding-bottom: 0px; padding-top: 0px; font-size: 12px; text-align: right;">*Free*</td>
                </tr>
            <?php
                    }
                }
            }
            ?>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%;" >
        <tr>
            <td style="width: 45%; padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Sub Total</td>
            <td style="width: 25%; padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'] + $discount, 2); ?></span></td>
            <td style="width: 30%; padding-top: 0px; padding-bottom: 0px; text-align: right;"></td>
        </tr>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Discount</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px;"><?php echo number_format($salesOrder['SalesOrder']['discount'] + $discount, 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"></td>
        </tr>
        <?php
        if($salesOrder['SalesOrder']['total_vat'] > 0){
        ?>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">VAT</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px;"><?php echo number_format($salesOrder['SalesOrder']['total_vat'], 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"></td>
        </tr>
        <?php
        }
        if($salesOrder['SalesOrder']['bank_charge_amount'] > 0){
        ?>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Bank Charge</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px;"><?php echo number_format($salesOrder['SalesOrder']['bank_charge_amount'], 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"></td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Total</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrder['SalesOrder']['total_amount'] - $salesOrder['SalesOrder']['discount'] + $salesOrder['SalesOrder']['total_vat'] + $salesOrder['SalesOrder']['bank_charge_amount'], 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">៛</div><span style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrder['SalesOrder']['total_amount_kh'], 0); ?></span></td>
        </tr>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Received</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><span style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">៛</div><span style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], 0); ?></span></td>
        </tr>
        <tr>
            <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; line-height: 30px;">Change</td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;" class="printMainCurrency">$</div><span id="printTotalChangeMain" style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change'], 2); ?></span></td>
            <td style="padding-top: 0px; padding-bottom: 0px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;" class="printOtherCurrency">៛</div><span id="printTotalChangeOther" style="font-size: 16px; font-weight: bold;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change_other'], 0); ?></span></td>
        </tr>
        <?php
        if(($salesOrder['SalesOrder']['discount'] + $discount) > 0){
        ?>
        <tr>
            <td style="padding: 0px 1px 0px 1px; font-size: 14px; border:1px solid #bdc3c7; border-right:0px; border-left:0px; line-height: 30px;">ចំនេញ / You have saved: </td>
            <td style="padding: 0px 1px 0px 1px; font-size: 24px; border:1px solid #bdc3c7; border-left:0px;  border-right:0px; text-align: right; font-weight: bold;" colspan="2"><div style="width:5px; float: left; font-size: 20px; margin-left: 5px;">$</div><?php echo number_format($salesOrder['SalesOrder']['discount'] + $discount, 2); ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
    <div style="clear:both;"></div>
    <div id="print-footer" style="margin-top: 10px; font-size:10px; margin-bottom: 100px; padding: 0px;">
        <table style="width:100%; text-align:center;">
            <?php 
                if($salesOrder['Branch']['acc_num']!="" || $salesOrder['Branch']['acc_num']!=null){
                    ?>
            <tr>
                <td style="width:49%; text-align:right; font-size:10px;">Account Number</td>
                <td style="width:2%;  font-size:10px;">: </td>
                <td style="width:49%; text-align:left; font-size:10px;"><?php echo $salesOrder['Branch']['acc_num']; ?></td>
            </tr>
            <?php
            }
            if($salesOrder['Branch']['acc_holder']!="" || $salesOrder['Branch']['acc_holder']!=null){
            ?>
            <tr>
                <td style="width:49%; text-align:right; font-size:10px;">Account Holder</td>
                <td style="width:2%;  font-size:10px;">: </td>
                <td style="width:49%; text-align:left; font-size:10px;"><?php echo $salesOrder['Branch']['acc_holder']; ?></td>
            </tr>
            <?php 
            }
            if($salesOrder['Branch']['bank']!="" || $salesOrder['Branch']['bank']!=null){
            ?>
            <tr>
                <td style="width:49%; text-align:right; font-size:10px;">Bank</td>
                <td style="width:2%;  font-size:10px;">: </td>
                <td style="width:49%; text-align:left; font-size:10px;"><?php echo $salesOrder['Branch']['bank']; ?></td>
            </tr>
            <?php }?>
        </table>
        <div style="font-size:9px; text-align: center; line-height: 20px;">
            Print: <?php echo date("d/m/Y H:i:s"); ?>
            <br/>Goods sold are not returnable.
            <br/>Thank you. Please come again.
            <span style="display:block; font-size: 10px;">Powered by Simplify Accounting System</span>
        </div>
    </div>
    <div style="clear:both"></div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    window.print();
    window.close();
});
</script>