<style type="text/css" media="screen">
    .titleHeader{
        vertical-align: top; 
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        padding-right: 2px !important;
        font-size: 10px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .contentHeight{
        height: 14px !important;
    }
    .marginTop10{
        padding-top: 10px !important;
    }
    .titleHeaderTable{
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        text-transform: uppercase; 
        font-size: 10px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 20px !important;
    }
</style>
<style type="text/css" media="print">
    .titleHeader{
        vertical-align: top; 
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        padding-right: 2px !important;
        font-size: 10px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .contentHeight{
        height: 14px !important;
    }
    .marginTop10{
        padding-top: 10px !important;
    }
    .titleHeaderTable{
        padding-bottom: 0px !important; 
        padding-top: 0px !important;
        text-transform: uppercase; 
        font-size: 10px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 20px !important;
    }
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width: 100%; position: fixed; bottom: 2px; font-size: 10px; text-align: center;} 
</style>
<div class="print_doc">
    <?php
    include("includes/function.php");
    $msg = "INVOICE";
    $vatInvoice = '';
    if($salesOrder['SalesOrder']['vat_percent'] > 0){
        $vatInvoice = $salesOrder['Company']['vat_number'];
    }
    $telTitle     = 'Tel: ';
    $companyTitle = $salesOrder['Branch']['name'];
    echo $this->element('/print/header-invoice', array('msg' => $msg, 'barcode' => $salesOrder['SalesOrder']['so_code'], 'vat' => $vatInvoice, 'address' => $salesOrder['Branch']['address'], 'telephone' => $telTitle.$salesOrder['Branch']['telephone'], 'logo' => $salesOrder['Company']['photo'], 'title' => $companyTitle, 'mail' => $salesOrder['Branch']['email_address']));
    ?>
    <div style="height: 10px"></div>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 15%;" class="titleHeader titleContent contentHeight"></td>
            <td style="width: 39%;" class="titleHeader"></td>
            <td style="width: 17%;" class="titleHeader titleContent">Name:</td>
            <td class="titleHeader"><?php echo $salesOrder['Customer']['name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Invoice No:</td>
            <td class="titleHeader"><?php echo $salesOrder['SalesOrder']['so_code']; ?></td>
            <td class="titleHeader"></td>
            <td rowspan="2" class="titleHeader">
                <?php
                $addressTop = '';
                $addressBottom = '';
                if($salesOrder['Customer']['type'] == 1){
                    if($salesOrder['Customer']['province_id'] > 0){
                        $provinceId = $salesOrder['Customer']['province_id'];
                        $districtId = $salesOrder['Customer']['district_id']>0?$salesOrder['Customer']['district_id']:'0';
                        $communeId  = $salesOrder['Customer']['commune_id']>0?$salesOrder['Customer']['commune_id']:'0';
                        $villageId  = $salesOrder['Customer']['village_id']>0?$salesOrder['Customer']['village_id']:'0';
                        $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$salesOrder['Customer']['province_id']}");    
                        $rowAddress = mysql_fetch_array($sqlAddress);
                    }else{
                        $rowAddress['p_name'] = '';
                        $rowAddress['d_name'] = '';
                        $rowAddress['c_name'] = '';
                        $rowAddress['v_name'] = '';
                    }
                    $house = $salesOrder['Customer']['house_no']!=''?$salesOrder['Customer']['house_no'].",":'';
                    $street = '';
                    if($salesOrder['Customer']['street_id'] != ''){
                        $sqlStreet = mysql_query("SELECT name FROM streets WHERE id = ".$salesOrder['Customer']['street_id']);
                        $rowStreet = mysql_fetch_array($sqlStreet);
                        $street = " ".$rowStreet[0].",";
                    }
                    $village  = $rowAddress['v_name']!=''?" ".$rowAddress['v_name'].",":'';
                    $commune  = $rowAddress['c_name']!=''?" ".$rowAddress['c_name'].",":'';
                    $district = $rowAddress['d_name']!=''?" ".$rowAddress['d_name'].",":'';
                    $province = $rowAddress['p_name']!=''?" ".$rowAddress['p_name']."":'';
                    $addressTop = $house.$street.$village;
                    $addressBottom = $commune.$district.$province;
                }else{
                    $addressTop =  $salesOrder['Customer']['address'];
                }
                echo $addressTop."<br />";
                echo $addressBottom;
                ?>
            </td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Date</td>
            <td class="titleHeader"><?php echo dateShort($salesOrder['SalesOrder']['order_date'], "d/M/Y"); ?></td>
            <td class="titleHeader"></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Credit Term:</td>
            <td class="titleHeader">
                <?php 
                if($salesOrder['SalesOrder']['payment_term_id'] != ''){
                    $sqlTerm = mysql_query("SELECT name FROM payment_terms WHERE id = ".$salesOrder['SalesOrder']['payment_term_id']);
                    $rowTerm = mysql_fetch_array($sqlTerm);
                    echo $rowTerm[0];
                }
                ?>
            </td>
            <td class="titleHeader titleContent"><?php if($salesOrder['SalesOrder']['total_vat'] > 0){ echo 'VAT No:'; } ?></td>
            <td class="titleHeader"><?php if($salesOrder['SalesOrder']['total_vat'] > 0){ echo $salesOrder['Customer']['vat']; } ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent marginTop10">P/O NO:</td>
            <td class="titleHeader marginTop10"><?php echo $salesOrder['SalesOrder']['customer_po_number']; ?></td>
            <td class="titleHeader titleContent marginTop10">ATTN:</td>
            <td class="titleHeader marginTop10"><?php echo $salesOrder['CustomerContact']['contact_name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Project:</td>
            <td class="titleHeader"><?php echo $salesOrder['SalesOrder']['project']; ?></td>
            <td class="titleHeader titleContent">H/P:</td>
            <td class="titleHeader"><?php echo $salesOrder['CustomerContact']['contact_telephone']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight"></td>
            <td class="titleHeader"></td>
            <td class="titleHeader titleContent">E-mail:</td>
            <td class="titleHeader"><?php echo $salesOrder['CustomerContact']['contact_email']; ?></td>
        </tr>
    </table>
    <div style="height: 10px"></div>
    <div>
        <?php
            if (!empty($salesOrderDetails)) {
        ?>
        <div>
            <table class="table_print" style="border: none;">
                    <tr>
                        <th style="width: 7%;" class="titleHeaderTable titleHeaderHeight">No.</th>
                        <th style="width: 10%;" class="titleHeaderTable">SKU</th>
                        <th class="titleHeaderTable">DESCRIPTION</th>
                        <th style="width: 7%;" class="titleHeaderTable">QTY</th>
                        <th style="width: 7%;" class="titleHeaderTable">F.O.C</th>
                        <th style="width: 9%;" class="titleHeaderTable">UoM</th>
                        <th style="width: 9%;" class="titleHeaderTable">Unit Price</th>
                        <th style="width: 9%;" class="titleHeaderTable">Discount</th>
                        <th style="width: 9%;" class="titleHeaderTable">Total Price</th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($salesOrderDetails as $salesOrderDetail) {
                        // Check Name With Customer
                        $productName = $salesOrderDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$salesOrderDetail['Product']['id']." AND customer_id = ".$salesOrder['Customer']['id']." ORDER BY created DESC LIMIT 1");
                        if(@mysql_num_rows($sqlProCus)){
                            $rowProCus = mysql_fetch_array($sqlProCus);
                            $productName = $rowProCus['name'];
                        }
                    ?>
                        <tr class="rowListDN">
                            <td style="text-align: center; font-size: 10px; height: 20px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo ++$index; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderDetail['Product']['code']; 
                                ?>
                            </td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $productName; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderDetail['SalesOrderDetail']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderDetail['SalesOrderDetail']['qty_free'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderDetail['Uom']['abbr'];
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 2);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderDetail['SalesOrderDetail']['discount_amount'], 2);
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php echo number_format($salesOrderDetail['SalesOrderDetail']['total_price'] - $salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); ?>
                            </td>
                        </tr>
                    <?php
                    }
                    if(!empty($salesOrderServices)){
                        foreach($salesOrderServices AS $salesOrderService){
                            $uomName = '';
                            if($salesOrderService['Service']['uom_id'] != ''){
                                $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$salesOrderService['Service']['uom_id']);
                                $rowUom = mysql_fetch_array($sqlUom);
                                $uomName = $rowUom[0];
                            }
                    ?>
                        <tr class="rowListDN">
                            <td style="text-align: center; font-size: 10px; height: 20px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo ++$index; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderService['Service']['code']; 
                                ?>
                            </td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderService['Service']['name']; 
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderService['SalesOrderService']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderService['SalesOrderService']['qty_free'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $uomName; 
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderService['SalesOrderService']['unit_price'], 2);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderService['SalesOrderService']['discount_amount'], 2);
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php echo number_format($salesOrderService['SalesOrderService']['total_price'] - $salesOrderService['SalesOrderService']['discount_amount'], 2); ?>
                            </td>
                        </tr>
                    <?php
                        }
                    }
                    if(!empty($salesOrderMiscs)){
                        foreach($salesOrderMiscs AS $salesOrderMisc){
                    ?>
                        <tr class="rowListDN">
                            <td style="text-align: center; font-size: 10px; height: 20px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo ++$index; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderMisc['SalesOrderMisc']['description']; 
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderMisc['SalesOrderMisc']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($salesOrderMisc['SalesOrderMisc']['qty_free'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $salesOrderMisc['Uom']['abbr'];
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderMisc['SalesOrderMisc']['unit_price'], 2);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($salesOrderMisc['SalesOrderMisc']['discount_amount'], 2);
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span>
                                <?php echo number_format($salesOrderMisc['SalesOrderMisc']['total_price'] - $salesOrderMisc['SalesOrderMisc']['discount_amount'], 2); ?>
                            </td>
                        </tr>
                    <?php
                        }
                    }
                    $rowSpan = 1;
                    if($salesOrder['SalesOrder']['discount'] > 0){
                        $rowSpan = $rowSpan + 1;
                    }
                    if($salesOrder['SalesOrder']['total_vat'] > 0){
                        $rowSpan = $rowSpan + 1;
                    }
                    ?>
                        <tr>
                            <td colspan="6" rowspan="<?php echo $rowSpan; ?>" style="vertical-align: top; padding-top: 0px !important; padding-bottom: 0px !important; height: 40px !important; background-color: #dedede !important;">
                                <table style="width: 98%; margin-top: 2px; margin-left: 2px; vertical-align: top;">
                                        <tr>
                                            <td style="font-size: 10px; padding-bottom: 5px; border: none; vertical-align: top; text-transform: uppercase; font-weight: bold;">
                                                <?php 
                                                $total = ($salesOrder['SalesOrder']['total_amount'] - $salesOrder['SalesOrder']['discount'] + $salesOrder['SalesOrder']['total_vat']);
                                                echo convertNumberToWords(number_format($total, 2)); 
                                                ?>
                                            </td>
                                        </tr>
                                </table>
                            </td>
                            <td colspan="2" style="text-align: right; text-transform: uppercase; font-size: 10px; font-weight: bold; height: 20px; padding-top: 0px; padding-bottom: 0px;">SUB TOTAL</td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($salesOrder['SalesOrder']['total_amount']), 2); ?></td>
                        </tr>
                        <?php
                        if($salesOrder['SalesOrder']['discount'] > 0){
                        ?>
                        <tr>
                            <td colspan="2" style="text-align: right; text-transform: uppercase; font-size: 10px; font-weight: bold; height: 20px; padding-top: 0px; padding-bottom: 0px;">DISCOUNT <?php if($salesOrder['SalesOrder']['discount_percent'] > 0){ echo "(".number_format($salesOrder['SalesOrder']['discount_percent'], 2)."%)"; } ?></td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($salesOrder['SalesOrder']['discount']), 2); ?></td>
                        </tr>
                        <?php
                        }
                        if($salesOrder['SalesOrder']['total_vat'] > 0){
                        ?>
                        <tr>
                            <td colspan="2" style="text-align: right; text-transform: uppercase; font-size: 10px; font-weight: bold; height: 20px; padding-top: 0px; padding-bottom: 0px;">VAT (<?php echo number_format($salesOrder['SalesOrder']['vat_percent'], 2) ?>%)</td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($salesOrder['SalesOrder']['total_vat']), 2); ?></td>
                        </tr>
                        <?php
                        }
                        ?>
                        <tr>
                            <td colspan="6" style="padding-top: 0px; padding-bottom: 0px;"></td>
                            <td colspan="2" style="text-align: right; text-transform: uppercase; font-size: 10px; font-weight: bold; height: 20px; padding-top: 0px; padding-bottom: 0px;">GRAND TOTAL</td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $salesOrder['CurrencyCenter']['symbol']; ?></span><?php echo number_format(($salesOrder['SalesOrder']['total_amount'] - $salesOrder['SalesOrder']['discount'] + $salesOrder['SalesOrder']['total_vat']), 2); ?></td>
                        </tr>
            </table>
        </div>
        <?php
                }
        ?>
        <br />
        <div style="font-size: 12px; font-weight: bold;"><b style="font-size: 11px; font-weight: bold;">Note:</b> <?php echo $salesOrder['SalesOrder']['memo']; ?></div>
        <br />
        <div style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 33%; vertical-align: bottom; text-align: center; height: 110px;">
                        <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
                            Prepared By:
                        </div>
                    </td>
                    <td style="width: 34%; vertical-align: bottom; text-align: center;">
                        <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
                            Approved By:
                        </div>
                    </td>
                    <td style="width: 33%; vertical-align: bottom; text-align: center;">
                        <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
                            Customer's Signature
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <br />
        <div style="float:left;width: 450px;">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' class='noprint'>
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
        $("#btnDisappearPrint").click(function(){
            window.print();
            window.close();
        });
    });
</script>