<?php
$allowLots    = false;
$allowExpired = false;
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 39) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 39){
        $costDecimal = $rowSetting['value'];
    }
}
include("includes/function.php");
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
</style>
<div class="print_doc">
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 20px; font-weight: bold;">
                អេស លីកហ្គ័រ
            </td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 18px; font-weight: bold;">
                S Liquor
            </td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                វិក័យប័ត្រទិញ/ ប័ណ្ណបញ្ចូល ទំនិញ (ស្តុក) 
            </td>
            <td style="text-align: left; width: 10%;">GRN No:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['PurchaseOrder']['po_code']; ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                PURCHASE BILL/ GOODS RECEIVED NOTE 
            </td>
            <td style="text-align: left; width: 10%;">Date:</td>
            <td style="white-space: nowrap;">
                <?php 
                $created = explode(" ", $purchaseOrder['PurchaseOrder']['created']);
                echo dateShort($purchaseOrder['PurchaseOrder']['order_date'], "d/M/Y"); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                ឈ្មោះអ្នកផ្គត់ផ្គង់(Supplier's Name): <?php echo $purchaseOrder['Vendor']['name']; ?>
            </td>
            <td style="text-align: left; width: 10%;">PO No:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['PurchaseRequest']['pr_code']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                អាសយដ្ឋាន (Address): <?php echo $purchaseOrder['Vendor']['address']; ?>
            </td>
            <td style="text-align: left; width: 10%;">PO Date:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['PurchaseRequest']['order_date'] != '0000-00-00'?dateShort($purchaseOrder['PurchaseRequest']['order_date'], 'd/M/Y'):""; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                លេខទូសព្ទ (Phone Number): <?php echo $purchaseOrder['Vendor']['work_telephone']; ?>
            </td>
            <td style="text-align: left; width: 10%;">Invoice No:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['PurchaseOrder']['invoice_code']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px; vertical-align: top;">
                បរិយាយ (Memo): <?php echo nl2br($purchaseOrder['PurchaseOrder']['note']); ?>
            </td>
            <td style="text-align: left; width: 10%;">Warehouse:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['LocationGroup']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 10%;">Created: <?php echo dateShort($purchaseOrder['PurchaseOrder']['created'], "d/m/Y H:i:s"); ?></td>
            <td style="text-align: left; width: 10%;">Location:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseOrder['Location']['name']; ?>
            </td>
        </tr>
        <tr>
            <td>Created By: <?php echo $purchaseOrder['User']['first_name']." ".$purchaseOrder['User']['last_name']; ?></td>
            <td style="text-align: left; width: 10%;"></td>
            <td style="white-space: nowrap;">
                
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first" style="text-transform: uppercase; font-size: 11px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'ល.រ'; ?></th>
                    <th style="width: 10%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'លេខកូដផលិតផល'; ?></th>
                    <th style="width: 35%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'ឯកត្តាគិត'; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;" colspan="2"><?php echo 'បរិមាណ '; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'តម្លៃ'; ?></th>
                    <th style="width: 13%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'បញ្ចុះតម្លៃ'; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'ថ្លៃទំនិញ'; ?></th>
                </tr>
                <tr>
                    <th class="first" style="text-transform: uppercase; font-size: 11px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'No.'; ?></th>
                    <th style="width: 10%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'PRODUCT CODE'; ?></th>
                    <th style="width: 35%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'NAME OF PRODUCT'; ?></th>
                    <th style="width: 8%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'UoM'; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'QTY'; ?></th>
                    <th style="text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'FOC'; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'COST'; ?></th>
                    <th style="width: 13%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'DISCOUNT'; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 11px; padding-bottom: 0px; padding-top: 0px;"><?php echo 'AMOUNT'; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($purchaseOrderDetails)) {
                    foreach ($purchaseOrderDetails as $purchaseOrderDetail) {
                        $discount    = $purchaseOrderDetail['PurchaseOrderDetail']['discount_amount'];
                        $conversion  = $purchaseOrderDetail['PurchaseOrderDetail']['conversion'];
                        $productCode = $purchaseOrderDetail['Product']['code'];
                        $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$purchaseOrderDetail['Product']['id']." AND uom_id = ".$purchaseOrderDetail['PurchaseOrderDetail']['qty_uom_id']);
                        if(mysql_num_rows($sqlSku)){
                            $rowSku = mysql_fetch_array($sqlSku);
                            $productCode = $rowSku[0];
                        }
                ?>
                <tr>
                    <td class="first" style="text-align: center; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo++$index; ?></td>
                    <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $productCode; ?></td>
                    <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $purchaseOrderDetail['Product']['name']."/".$conversion; ?></td>
                    <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $purchaseOrderDetail['Uom']['abbr']; ?> </td>
                    <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty'], 0); ?> </td>
                    <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty_free'], 0); ?> </td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['unit_cost'], $costDecimal); ?></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($discount,  $costDecimal); ?></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format(($purchaseOrderDetail['PurchaseOrderDetail']['total_cost'] - $discount),  $costDecimal); ?></td>
                </tr>
                <?php
                        
                    }
                }
                if (!empty($purchaseOrderServices)) {
                    foreach ($purchaseOrderServices as $purchaseOrderService) {
                        $uomName = '';
                        if($purchaseOrderService['Service']['uom_id'] != ''){
                            $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$purchaseOrderService['Service']['uom_id']);
                            $rowUom = mysql_fetch_array($sqlUom);
                            $uomName = $rowUom[0];
                        }
                        $discount = $purchaseOrderService['PurchaseOrderService']['discount_amount'];
                ?>
                        <tr><td class="first" style="text-align: center; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $purchaseOrderService['Service']['code']; ?></td>
                            <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $purchaseOrderService['Service']['name']; ?></td>
                            <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $uomName; ?></td>
                            <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty'], 0); ?> </td>
                            <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty_free'], 0); ?> </td>
                            <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($purchaseOrderService['PurchaseOrderService']['unit_cost'],  $costDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($discount,  $costDecimal); ?></td>
                            <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format(($purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount),  $costDecimal); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;" colspan="8"><b><?php echo 'សរុប/ Grandal'; ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;"><?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount']),  $costDecimal); ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;" colspan="8"><b><?php echo 'សរុបចុះថ្លៃ/ Total​ Discount'; ?> <?php if($purchaseOrder['PurchaseOrder']['discount_percent'] > 0){ ?>(<?php echo number_format($purchaseOrder['PurchaseOrder']['discount_percent'], 2); ?>%)<?php } ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;"><?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($purchaseOrder['PurchaseOrder']['discount_amount']),  $costDecimal); ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;" colspan="8"><b><?php echo 'អ.ត.ប/ VAT'; ?> (<?php echo number_format($purchaseOrder['PurchaseOrder']['vat_percent'], 2); ?>%)</b></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;"><?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_vat']),  $costDecimal); ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;" colspan="8"><b><?php echo 'សរុបរូម/ Grand Total'; ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><span style="float: left; width: 12px; font-size: 12px;"><?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($purchaseOrder['PurchaseOrder']['total_amount'] - $purchaseOrder['PurchaseOrder']['discount_amount'] + $purchaseOrder['PurchaseOrder']['total_vat']),  $costDecimal); ?></td>
                </tr>
            </table>
            <table style="width: 100%; margin-top: 10px;">
                <tr>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Delivery By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Received By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Prepared By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Verify By:</td>
                </tr>
                <tr>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                </tr>
                <tr>
                    <td style="text-align: center; font-size: 10px;">Suppliers</td>
                    <td style="text-align: center; font-size: 10px;">Stock Controller</td>
                    <td style="text-align: center; font-size: 10px;">Warehouse Manager</td>
                    <td style="text-align: center; font-size: 10px;">Accountant</td>
                </tr>
            </table>
        </div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>