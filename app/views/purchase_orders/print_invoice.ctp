<?php
    // Get Decimal
    $sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
    $rowOption = mysql_fetch_array($sqlOption);
    include("includes/function.php");
?>
<style>
    .bold{
        font-weight: bold;
    }
</style>
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
</style>
<div class="print_doc">
    <?php
    $titleOther = 'ប័ណ្ណបញ្ជាទិញ';
    $title = 'PURCHASE ORDER';
    $created = explode(" ", $purchaseRequest['PurchaseRequest']['created']);
    $poDate  = dateShort($purchaseRequest['PurchaseRequest']['order_date'])." ".$created[1];
    echo $this->element('/print/header-po', array('titleOther' => $titleOther, 'title' => $title, 'nameOther' => $purchaseRequest['Branch']['name_other'], 'name' => $purchaseRequest['Branch']['name'], 'code' => $purchaseRequest['PurchaseRequest']['pr_code'], 'date' => $poDate));
    ?>
    <div style="height: 12px"></div>
    <table width="100%">
        <tr>
            <td style="width: 25%; font-size: 12px;">ឈ្មោះអ្នកផ្គត់ផ្គង់(Supplier's Name) :</td>
            <td style="width: 50%; font-size: 12px;"><?php echo $purchaseRequest['Vendor']['name']; ?></td>
            <td style="font-size: 12px; vertical-align: top;">Shipping To :</td>
        </tr>
        <tr>
            <td style="font-size: 12px;">អាសយដ្ឋាន (Address) :</td>
            <td style="font-size: 12px;">
                <?php echo nl2br($purchaseRequest['Vendor']['address']); ?>
            </td>
            <td style="font-size: 12px; vertical-align: top;" rowspan="2"><?php echo nl2br($purchaseRequest['PurchaseRequest']['shipment_to']); ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;">តាមរយៈ (Att) :</td>
            <td style="font-size: 12px;"></td>
        </tr>
        <tr>
            <td style="font-size: 12px;">លេខទូសព្ទ (Phone Number) :</td>
            <td style="font-size: 12px;">
                <?php echo $purchaseRequest['Vendor']['work_telephone']; ?>
            </td>
            <td style="font-size: 12px;">ចំណាំ (Memo) : <?php echo $purchaseRequest['PurchaseRequest']['note']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;">ថ្ងៃខែបង្កើត (Created) :</td>
            <td style="font-size: 12px;">
                <?php echo dateShort($purchaseRequest['PurchaseRequest']['created'], "d/m/Y H:i:s"); ?>
            </td>
            <td style="font-size: 12px;">បង្កើតដោយ (Created By) : <?php echo $purchaseRequest['User']['first_name']." ".$purchaseRequest['User']['last_name']; ?></td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print" style="width: 100%;">
                <tr>
                    <th class="first" style="font-size: 11px; padding: 0px; height: 20px;">ល.រ</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">លេខកូដផលិតផល</th>
                    <th style="width: 35%; text-transform: uppercase; font-size: 11px; padding: 0px;">ឈ្មោះផលិតផល</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">ឯកត្តាគិត</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">បរិមាណ</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">ឥតគិតថ្លៃ</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">តម្លៃ</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">ចុះថ្លៃ</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">សរុបតម្លៃ</th>
                </tr>
                <tr>
                    <th class="first" style="font-size: 11px; padding: 0px; height: 20px;">No.</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">PRODUCT CODE</th>
                    <th style="width: 35%; text-transform: uppercase; font-size: 11px; padding: 0px;">NAME OF PRODUCT</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">UoM</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">QTY</th>
                    <th style="white-space: nowrap; text-transform: uppercase; font-size: 11px; padding: 0px;">F.O.C</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">PRICE</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">DISCOUNT</th>
                    <th style="text-transform: uppercase; font-size: 11px; padding: 0px;">AMOUNT</th>
                </tr>
                <?php
                $index = 0;
                if (!empty($purchaseRequestDetails)) {
                    foreach ($purchaseRequestDetails as $purchaseRequestDetail) {
                        $conversion  = $purchaseRequestDetail['PurchaseRequestDetail']['conversion'];
                        $productCode = $purchaseRequestDetail['Product']['code'];
                        $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$purchaseRequestDetail['Product']['id']." AND uom_id = ".$purchaseRequestDetail['PurchaseRequestDetail']['qty_uom_id']);
                        if(mysql_num_rows($sqlSku)){
                            $rowSku = mysql_fetch_array($sqlSku);
                            $productCode = $rowSku[0];
                        }
                ?>
                <tr>
                    <td class="first" style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px; height: 20px;"><?php echo++$index; ?></td>
                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $productCode; ?></td>
                    <td style="width: 35%; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $purchaseRequestDetail['Product']['name']."/".$conversion; ?></td>
                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $purchaseRequestDetail['Uom']['abbr']; ?> </td>
                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['qty'], 0); ?> </td>
                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['qty_free'], 0); ?> </td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['unit_cost'], $rowOption[0]); ?></td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                        <?php 
                        if($purchaseRequestDetail['PurchaseRequestDetail']['discount_percent'] > 0){
                            echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['discount_percent'], 2)." %"; 
                        } else {
                            echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['discount_amount'], $rowOption[0]); 
                        }
                        ?>
                    </td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format(($purchaseRequestDetail['PurchaseRequestDetail']['total_cost'] - $purchaseRequestDetail['PurchaseRequestDetail']['discount_amount']), $rowOption[0]); ?></td>
                </tr>
                <?php
                        
                    }
                }
                if (!empty($purchaseRequestServices)) {
                    foreach ($purchaseRequestServices as $purchaseRequestService) {
                        $uomName = '';
                        if($purchaseRequestService['Service']['uom_id'] != ''){
                            $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$purchaseRequestService['Service']['uom_id']);
                            $rowUom = mysql_fetch_array($sqlUom);
                            $uomName = $rowUom[0];
                        }
                ?>
                        <tr>
                            <td class="first" style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px; height: 20px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $purchaseRequestService['Service']['code']; ?></td>
                            <td style="width: 35%; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $purchaseRequestService['Service']['name']; ?></td>
                            <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $uomName; ?> </td>
                            <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['qty'], 0); ?> </td>
                            <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['qty_free'], 0); ?> </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequestService['PurchaseRequestService']['unit_cost'], $rowOption[0]); ?></td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                if($purchaseRequestService['PurchaseRequestService']['discount_percent'] > 0){
                                    echo number_format($purchaseRequestService['PurchaseRequestService']['discount_percent'], 2)." %"; 
                                } else {
                                    echo number_format($purchaseRequestService['PurchaseRequestService']['discount_amount'], $rowOption[0]); 
                                }
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format(($purchaseRequestService['PurchaseRequestService']['total_cost'] - $purchaseRequestService['PurchaseRequestService']['discount_amount']), $rowOption[0]); ?></td>
                        </tr>
                <?php
                        
                    }
                }
                $labelTotal = TABLE_TOTAL;
                if($purchaseRequest['PurchaseRequest']['total_vat'] > 0){
                    $labelTotal = TABLE_SUB_TOTAL;
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; height: 20px;" colspan="8"><b><?php echo TABLE_SUB_TOTAL; ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequest['PurchaseRequest']['total_amount'], $rowOption[0]); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
                if($purchaseRequest['PurchaseRequest']['total_vat'] > 0){
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; height: 20px;" colspan="8"><b><?php echo TABLE_VAT; ?> (<?php echo number_format($purchaseRequest['PurchaseRequest']['vat_percent'], 2); ?>%)</b></td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequest['PurchaseRequest']['total_vat'], $rowOption[0]); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; height: 20px;" colspan="8"><b><?php echo TABLE_TOTAL; ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo number_format($purchaseRequest['PurchaseRequest']['total_amount'] + $purchaseRequest['PurchaseRequest']['total_vat'], $rowOption[0]); ?> <?php echo $purchaseRequest['CurrencyCenter']['symbol']; ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </div>
        <?php
        $sqlTerm = mysql_query("SELECT term_conditions.name AS name FROM purchase_request_term_conditions INNER JOIN term_conditions ON term_conditions.id = purchase_request_term_conditions.term_condition_id WHERE purchase_request_term_conditions.purchase_request_id = ".$purchaseRequest['PurchaseRequest']['id']);                                    
        if(mysql_num_rows($sqlTerm)){
        ?>
        <!-- Term & Condition -->
        <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 5px;">
            <tr>
                <td style="font-size: 10px; font-weight: bold; vertical-align: top; border: none; padding: 0px; height: 15px;">Terms and Condition:</td>
            </tr>
            <?php
            while($rowTerm = mysql_fetch_array($sqlTerm)){
            ?>
            <tr>
                <td style="font-size: 10px; vertical-align: top; border: none; padding: 0px;">- <?php echo $rowTerm['name']; ?></td>
            </tr>
            <?php
            }
            ?>
        </table>
        <!-- Note -->
        <?php
        }
        ?>
        <div style=" margin-top: 20px;">
            <table style="width: 100%;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style=" vertical-align: top;">
                        <table cellpadding="0" cellspacing="0" style="width: 200px;">
                            <tr>
                                <td style="text-align: left; font-size: 11px;">Prepared by :</td>
                            </tr>
                            <tr>
                                <td style="height: 130px; text-align: left; vertical-align: bottom; font-size: 11px;">
                                    Procurement Manager
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="text-align: center; font-size: 11px;">Verify by:</td>
                            </tr>
                            <tr>
                                <td style="height: 130px; text-align: center; vertical-align: bottom; font-size: 11px;">
                                    Finance Manager
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 25%; text-align: right;" rowspan="2">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="text-align: center; font-size: 11px;">Approved by:</td>
                            </tr>
                            <tr>
                                <td style="height: 130px; text-align: center; vertical-align: bottom; font-size: 11px;">
                                    MD/CEO
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div style="clear:both"></div>
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