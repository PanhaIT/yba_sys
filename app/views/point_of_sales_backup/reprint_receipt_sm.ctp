<?php
include("includes/function.php");
$img = $salesOrder['Company']['photo'];
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"></script>
<style type="text/css" media="screen">
    div#header_waiting { display: none;}
    div.wrap-print-slip { width:310px;}
    div#print-footer {display: none;} 
    b{font-size:12px;}
</style> 
<style type="text/css" media="print">
    div#header_waiting { width:100%; text-align: center; margin: 0px auto; display: block; padding-bottom: 20px; padding-top: 0px; page-break-after: always}
    div.wrap-print-slip { width:100%;}
    #btnDisappearPrint { display: none;}
    div#print-footer {display: block; margin-top: 10px; width:100%} 
</style>
<div class="print_doc" style="width: 350px;">
<?php
$msg = "វិក្ក័យប័ត្រ";
?>
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; text-align: center;">
            <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $img; ?>" style="height: 80px;" />
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center;">
            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; text-align: center;">
                        <div style="font-size: 18px; font-weight: bold; text-align: center;">
                            <?php
                            echo $salesOrder['Branch']['name'];
                            ?>
                        </div>
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
            <?php echo !empty($msg) ? $msg : ''; ?>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width: 17%; font-size: 12px;">លេខវិក្ក័យប័ត្រ:</td>
        <td style="width: 35%; font-size: 12px;"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
        <td style="width: 17%; font-size: 12px; text-align: right;">កាលបរិច្ឆេទ:</td>
        <td style="font-size: 12px;"><?php echo dateShort($salesOrder['SalesOrder']['order_date'], 'd/M/Y'); ?></td>
    </tr>
    <tr>
        <td style="width: 15%; font-size: 12px;">បេឡាករ:</td>
        <td style="font-size: 12px;"><?php echo $salesOrder['User']['first_name']." ".$salesOrder['User']['last_name']; ?></td>

        <td style="font-size: 12px; line-height: 25px;">Customer: </td>
        <td style="font-size: 12px;">
            <?php echo $salesOrder['Customer']['name']; ?>
        </td>
    </tr>
    <?php
    if($salesOrder['SalesOrder']['status'] == 0){
    ?>
    <tr>
        <td colspan="4" style="font-size: 13px; font-weight: bold; text-align: center;">
            TRANSACTION VOID
            <div style="font-size: 12px; text-align: center; width: 100%;">
                VOID DATE: <?php echo dateShort($salesOrder['SalesOrder']['modified'], 'd/M/Y H:i:s'); ?>
            </div>
            <div style="font-size: 12px; text-align: center; width: 100%;"> 
            <?php
            if(!empty($salesOrder['SalesOrder']['modified_by'])){
                $sqlV = mysql_query("SELECT * FROM users WHERE id = ".$salesOrder['SalesOrder']['modified_by']);
                if(mysql_num_rows($sqlV)){
                    $rowV = mysql_fetch_array($sqlV);
                    echo 'VOID BY: '.$rowV['first_name'].' '.$rowV['last_name'];
                }
            }
            ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
</table>
<table cellpadding="0" cellspacing="0" style="width:100%;" border="1">
        <tr>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 5%; text-align: center; border: 1px solid #000;" class="first">
                ល.រ
                <span style="display:block; font-size: 10px;">No.</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 43% !important; text-align: center; border: 1px solid #000;">
                ឈ្មោះ
                <span style="display:block; font-size: 10px;">Name</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 10%; text-align: center; border: 1px solid #000;">
                បរិមាណ
                <span style="display:block; font-size: 10px;">Qty</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 7%; text-align: center; border: 1px solid #000;">
                ខ្នាត
                <span style="display:block; font-size: 10px;">UoM</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 10%; text-align: center; border: 1px solid #000;">
                តំលៃ
                <span style="display:block; font-size: 10px;">Price</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 10%; text-align: center; border: 1px solid #000;">
                ចុះថ្លៃ
                <span style="display:block; font-size: 10px;">Dis</span>
            </th>
            <th style="padding-bottom: 0px; padding-top: 0px; color:#000; font-size: 11px; font-weight: bold; width: 10%; text-align: center; border: 1px solid #000;">
                សរុប
                <span style="display:block; font-size: 10px;">Total</span>
            </th>
        </tr>
        <?php
        $discount = 0;
        if (!empty($salesOrderDetails)) {
            $index = 0;
            foreach ($salesOrderDetails as $salesOrderDetail) {
                if($salesOrderDetail['SalesOrderDetail']['qty'] > 0){
                    $discount += $salesOrderDetail['SalesOrderDetail']['discount_amount'];
        ?>
            <tr>
                <td style="text-align: center; padding-bottom: 0px; padding-top: 0px; font-size: 11px;">
                    <?php echo ++$index; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px;"><?php echo $salesOrderDetail['Product']['name']; ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty'], 0); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;">
                    <?php echo $salesOrderDetail['Uom']['abbr']; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 2); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); ?></td>
            </tr>
        <?php
                }
                if($salesOrderDetail['SalesOrderDetail']['qty_free'] > 0){
        ?>
            <tr>
                <td style="text-align: center; padding-bottom: 0px; padding-top: 0px; font-size: 11px;">
                    <?php echo ++$index; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px;"><?php echo $salesOrderDetail['Product']['name']; ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty_free'], 0); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;">
                    <?php echo $salesOrderDetail['Uom']['abbr']; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
            </tr>
        <?php
                }
            }
        }
        ?>
            <?php
        if (!empty($salesOrderServices)) {
            $index = 0;
            foreach ($salesOrderServices as $salesOrderService) {
                if($salesOrderService['SalesOrderService']['qty'] > 0){
                    $discount += $salesOrderService['SalesOrderService']['discount_amount'];
        ?>
            <tr>
                <td style="text-align: center; padding-bottom: 0px; padding-top: 0px; font-size: 11px;">
                    <?php echo ++$index; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px;"><?php echo $salesOrderService['Service']['name']; ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"><?php echo number_format($salesOrderService['SalesOrderService']['qty'], 0); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderService['SalesOrderService']['unit_price'], 2); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderService['SalesOrderService']['discount_amount'], 2); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;"><?php echo number_format($salesOrderService['SalesOrderService']['total_price'] - $salesOrderService['SalesOrderService']['discount_amount'], 2); ?></td>
            </tr>
        <?php
                }
                if($salesOrderService['SalesOrderService']['qty_free'] > 0){
        ?>
            <tr>
                <td style="text-align: center; padding-bottom: 0px; padding-top: 0px; font-size: 11px;">
                    <?php echo ++$index; ?>
                </td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px;"><?php echo $salesOrderService['Service']['name']; ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"><?php echo number_format($salesOrderService['SalesOrderService']['qty_free'], 0); ?></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: center;"></td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
                <td style="padding-bottom: 0px; padding-top: 0px; font-size: 11px; text-align: right;">*Free*</td>
            </tr>
        <?php
                }
            }
        }
        ?>
</table>
<table cellpadding="0" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 36%; padding-top: 0px; padding-bottom: 0px; font-size: 11px;"><span style="font-size: 12px;">សរុប</span> / Sub Total</td>
        <td style="width: 31%; padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrder['SalesOrder']['total_amount'], 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="width: 33%; padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;"><?php echo $otherSymbolCur; ?></div><?php echo number_format($salesOrder['SalesOrder']['total_amount_kh'], 0); ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;"><span style="font-size: 12px;">បញ្ចុះតំលៃ</span> / Discount</td>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrder['SalesOrder']['discount'] + $discount, 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"></td>
        <?php
        }
        ?>
    </tr>
    <?php
    if($salesOrder['SalesOrder']['total_vat'] > 0){
    ?>
    <tr>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;">VAT</td>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrder['SalesOrder']['total_vat'], 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"></td>
        <?php
        }
        ?>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;"><span style="font-size: 12px;">សរុបចុងក្រោយ</span> / Total</td>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 16px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['total_amount'], 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 16px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;"><?php echo $otherSymbolCur; ?></div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['total_amount_other'], 0); ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;"><span style="font-size: 12px;">ប្រាក់ទទួល</span> / Received</td>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 12px; text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;"><?php echo $otherSymbolCur; ?></div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], 2); ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;"><span style="font-size: 12px;">ប្រាក់អាប់</span> / Change</td>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 16px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change'], 2); ?></td>
        <?php
        if($otherSymbolCur != ''){
        ?>
        <td style="padding-top: 0px; padding-bottom: 0px; font-size: 16px; text-align: right; font-weight: bold;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;"><?php echo $otherSymbolCur; ?></div><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change_other'], 2); ?></td>
        <?php
        }
        ?>
    </tr>
    <?php
        if(($salesOrder['SalesOrder']['discount'] + $discount) > 0){
        ?>
        <tr>
            <td colspan="2" style="padding: 0px 1px 0px 1px; font-size: 14px; border:1px solid #bdc3c7; border-right:0px; border-left:0px; line-height: 30px;">ចំនេញ / You have saved: </td>
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
        <span style="display:block; font-size: 10px;">អរគុណ សូមអញ្ចើញមកម្តងទៀត។</span>
        Thank you. Please come again.
        <span style="display:block; font-size: 10px;">បង្កើតឡើងដោយ udaya-tech.com/</span>
    </div>
</div>
<div style="clear:both"></div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    window.print();
    window.close();
});
</script>