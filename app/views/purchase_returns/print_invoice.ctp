<div class="print_doc">
    <?php
    include("includes/function.php");
    ?>
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
                ប័ណ្ណបង្វិល ទំនិញ
            </td>
            <td style="text-align: left; width: 10%;">GRN No:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseReturn['PurchaseReturn']['pr_code']; ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; text-align: center; width: 75%; font-size: 15px; font-weight: bold;">
                GOODS RETURN NOTE 
            </td>
            <td style="text-align: left; width: 10%;">Date:</td>
            <td style="white-space: nowrap;">
                <?php 
                $created = explode(" ", $purchaseReturn['PurchaseReturn']['created']);
                echo dateShort($purchaseReturn['PurchaseReturn']['order_date'], "d/M/Y"); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                ឈ្មោះអ្នកផ្គត់ផ្គង់(Supplier's Name): <?php echo $purchaseReturn['Vendor']['name']; ?>
            </td>
            <td style="text-align: left; width: 10%;">PI No:</td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                អាសយដ្ឋាន (Address): <?php echo $purchaseReturn['Vendor']['address']; ?>
            </td>
            <td style="text-align: left; width: 10%;">PI Date:</td>
            <td style="white-space: nowrap;"></td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px;">
                លេខទូសព្ទ (Phone Number): <?php echo $purchaseReturn['Vendor']['work_telephone']; ?>
            </td>
            <td style="text-align: left; width: 10%;">Warehouse:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseReturn['LocationGroup']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px; vertical-align: top;">
                បរិយាយ (Memo): <?php echo nl2br($purchaseReturn['PurchaseReturn']['note']); ?>
            </td>
            <td style="text-align: left; width: 10%;">Location:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseReturn['Location']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left; width: 75%; font-size: 11px; vertical-align: top;">
                Created: <?php echo dateShort($purchaseReturn['PurchaseReturn']['created'], "d/m/Y H:i:s"); ?>
            </td>
            <td style="text-align: left; width: 10%;">Created By:</td>
            <td style="white-space: nowrap;">
                <?php echo $purchaseReturn['User']['first_name']." ".$purchaseReturn['User']['last_name']; ?>
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
                if (!empty($purchaseReturnDetails)) {
                    foreach ($purchaseReturnDetails as $purchaseReturnDetail) {
                        $conversion  = $purchaseReturnDetail['PurchaseReturnDetail']['conversion'];
                        $productCode = $purchaseReturnDetail['Product']['code'];
                        $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$purchaseReturnDetail['Product']['id']." AND uom_id = ".$purchaseReturnDetail['PurchaseReturnDetail']['qty_uom_id']);
                        if(mysql_num_rows($sqlSku)){
                            $rowSku = mysql_fetch_array($sqlSku);
                            $productCode = $rowSku[0];
                        }
                ?>
                        <tr>
                            <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $productCode; ?></td>
                            <td><?php echo $purchaseReturnDetail['Product']['name']."/".$conversion; ?></td>
                            <td><?php echo $purchaseReturnDetail['Uom']['abbr']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['qty'], 0); ?> </td>
                            <td style="text-align: center"><?php echo '0'; ?> </td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['unit_price'], 2); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo ''; ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format(($purchaseReturnDetail['PurchaseReturnDetail']['total_price']), 2); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
                <?php
                if (!empty($purchaseReturnServices)) {
                    foreach ($purchaseReturnServices as $purchaseReturnService) {
                        $uomName = '';
                        if($purchaseReturnService['Service']['uom_id'] != ''){
                            $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$purchaseReturnService['Service']['uom_id']);
                            $rowUom = mysql_fetch_array($sqlUom);
                            $uomName = $rowUom[0];
                        }
                ?>
                        <tr>
                            <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseReturnService['Service']['code']; ?></td>
                            <td><?php echo $purchaseReturnService['Service']['name']; ?></td>
                            <td style="text-align: center"><?php echo $uomName; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['qty'], 0); ?></td>
                            <td style="text-align: center"><?php echo '0'; ?></td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnService['PurchaseReturnService']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo '0'; ?>
                            </td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnService['PurchaseReturnService']['total_price'], 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="8"><b><?php echo 'សរុប/ Grandal'; ?></b></td>
                    <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="8"><b><?php echo 'សរុបចុះថ្លៃ/ Total​ Discount'; ?></b></td>
                    <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo '0'; ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="8"><b><?php echo 'អ.ត.ប/ VAT'; ?> (<?php echo number_format($purchaseReturn['PurchaseReturn']['vat_percent'], 2); ?>%)</b></td>
                    <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseReturn['PurchaseReturn']['total_vat'], 2); ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;" colspan=8"><b><?php echo 'សរុបរូម/ Grand Total'; ?></b></td>
                    <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'] + $purchaseReturn['PurchaseReturn']['total_vat'], 2); ?></td>
                </tr>
            </table>
            <table style="width: 100%; margin-top: 10px;">
                <tr>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Prepared By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Delivery By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Checked By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Received By:</td>
                    <td style="text-align: center; font-size: 10px; width: 25%;">Verify By:</td>
                </tr>
                <tr>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                    <td style="height: 100px; text-align: center; vertical-align: bottom;"><hr style="width: 90%;" /></td>
                </tr>
                <tr>
                    <td style="text-align: center; font-size: 10px;">Stock Controller</td>
                    <td style="text-align: center; font-size: 10px;">Deliver</td>
                    <td style="text-align: center; font-size: 10px;">Warehouse Manager</td>
                    <td style="text-align: center; font-size: 10px;">Suppliers</td>
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