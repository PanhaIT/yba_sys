<div class="print_doc">
    <?php
    include("includes/function.php");
    $msg = "<b style='font-size: 22px; font-weight: 600;'>វិក័យប័ត្រ<b><br/><b style='font-size: 15px; font-weight: 600;'>RECEIPT</b>";
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $salesOrder['SalesOrder']['so_code'], 'address' => $salesOrder['Branch']['address'], 'telephone' => $salesOrder['Branch']['telephone'], 'logo' => $salesOrder['Company']['photo'], 'title' => $salesOrder['Branch']['name']));
    ?>
    <div style="height: 30px"></div>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 17%; border-left: 1px solid #000; border-top: 1px solid #000; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; text-transform: uppercase; font-size: 11px;">Sold To:</td>
            <td style="width: 33%; border-top: 1px solid #000;"></td>
            <td colspan="2" style="width: 25%; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-left: 1px solid #000; border-right: 1px solid #000; border-top: 1px solid #000; text-transform: uppercase; font-size: 11px;"></td>
            <td style="width: 12%; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-top: 1px solid #000; text-transform: uppercase; padding-left: 3px; font-size: 11px;">Invoice No:</td>
            <td style="font-size: 11px; border-right: 1px solid #000; border-top: 1px solid #000;"></td>
        </tr>
        <tr>
            <td style="font-size: 11px; text-transform: uppercase; border-left: 1px solid #000; padding-top: 3px; padding-bottom: 3px; padding-left: 3px;">Customer ID:</td>
            <td style="font-size: 12px;"><?php echo $salesOrder['Customer']['customer_code'];  ?></td>
            <td style="font-size: 12px; border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; vertical-align: top; padding-left: 2px; padding-top: 2px;" rowspan="8" colspan="2"></td>
            <td colspan="2" style="font-size: 15px; text-transform: uppercase; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-right: 1px solid #000;">
                <?php 
                echo $salesOrder['SalesOrder']['so_code']; 
                ?>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px; border-left: 1px solid #000; text-transform: uppercase; padding-bottom: 3px; padding-left: 3px;">Customer Name:</td>
            <td style="font-size: 15px;"><?php if($salesOrder['Customer']['name_kh'] != ''){ echo $salesOrder['Customer']['name_kh']; }  ?></td>
            <td style="font-size: 11px; text-transform: uppercase; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-top: 1px solid #000; border-right: 1px solid #000;" colspan="2">Invoice Date:</td>
        </tr>
        <tr>
            <td style="font-size: 11px; border-left: 1px solid #000; text-transform: uppercase; padding-bottom: 3px; padding-left: 3px;"></td>
            <td style="font-size: 15px;"><?php echo $salesOrder['Customer']['name'];  ?></td>
            <td colspan="2" style="font-size: 15px; border-right: 1px solid #000; border-bottom: 1px solid #000;"><?php echo dateShort($salesOrder['SalesOrder']['order_date'], "d/M/Y"); ?></td>
        </tr>
        <tr>
            <td style="font-size: 11px; border-left: 1px solid #000; text-transform: uppercase; padding-bottom: 3px; padding-left: 3px;">Address:</td>
            <td></td>
            <td colspan="2" style="font-size: 11px; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-right: 1px solid #000;">DN NO:</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 13px; border-left: 1px solid #000; padding-bottom: 3px; padding-left: 3px;">
                <?php 
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
                echo $house.$street.$village.$commune.$district.$province;
                ?>
            </td>
            <td colspan="2" style="font-size: 12px; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-right: 1px solid #000;"><?php echo $salesOrder['Delivery']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 11px; border-left: 1px solid #000; text-transform: uppercase; padding-bottom: 3px; padding-left: 3px;">Tel:</td>
            <td style="font-size: 13px;"><?php echo $salesOrder['Customer']['main_number'];  ?></td>
            <td style="font-size: 11px; border-top: 1px solid #000; text-transform: uppercase; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-right: 1px solid #000;" colspan="2">DN DATE:</td>
        </tr>
        <tr>
            <td style="font-size: 11px; border-left: 1px solid #000; border-bottom: 1px solid #000; text-transform: uppercase; padding-bottom: 3px; padding-left: 3px;">Customer VAT TIN:</td>
            <td style="font-size: 12px; border-bottom: 1px solid #000;"><?php echo $salesOrder['Customer']['vat'];  ?></td>
            <td style="font-size: 12px; border-right: 1px solid #000; padding-top: 3px; padding-bottom: 3px; padding-left: 3px; border-bottom: 1px solid #000;" colspan="2"><?php echo dateShort($salesOrder['Delivery']['date'], "d/M/Y"); ?></td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="margin-top: 3px; width: 100%;">
        <tr>
            <td style="width: 16%; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">CUSTOMER PO:</td>
            <td style="width: 16%; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">SALES REP:</td>
            <td style="width: 16%; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">DELIVERED BY:</td>
            <td style="width: 16%; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">COLLECTOR:</td>
            <td style="width: 16%; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">PAYMENT TERM:</td>
            <td style="border-bottom: 1px solid #000; text-transform: uppercase; font-size: 11px; text-align: center;">DUE DATE:</td>
        </tr>
        <tr>
            <td style="padding-top: 3px; padding-bottom: 3px; border-left: 1px solid #000;  border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php echo $salesOrder['SalesOrder']['customer_po_number']; ?>
            </td>
            <td style="padding-top: 3px; padding-bottom: 3px; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php 
                if($salesOrder['SalesOrder']['sales_rep_id'] > 0){
                    $sqlDelivery = mysql_query("SELECT employee_code FROM employees WHERE id = {$salesOrder['SalesOrder']['sales_rep_id']}");
                    if(mysql_num_rows($sqlDelivery)){
                        $rowDelivery = mysql_fetch_array($sqlDelivery);
                        echo $rowDelivery[0];
                    }
                }
                ?>
            </td>
            <td style="padding-top: 3px; padding-bottom: 3px; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php 
                if($salesOrder['SalesOrder']['deliver_id'] > 0){
                    $sqlDelivery = mysql_query("SELECT employee_code FROM employees WHERE id = {$salesOrder['SalesOrder']['deliver_id']}");
                    if(mysql_num_rows($sqlDelivery)){
                        $rowDelivery = mysql_fetch_array($sqlDelivery);
                        echo $rowDelivery[0];
                    }
                }
                ?>
            </td>
            <td style="padding-top: 3px; padding-bottom: 3px; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php 
                if($salesOrder['SalesOrder']['collector_id'] > 0){
                    $sqlDelivery = mysql_query("SELECT employee_code FROM employees WHERE id = {$salesOrder['SalesOrder']['collector_id']}");
                    if(mysql_num_rows($sqlDelivery)){
                        $rowDelivery = mysql_fetch_array($sqlDelivery);
                        echo $rowDelivery[0];
                    }
                }
                ?>
            </td>
            <td style="padding-top: 3px; padding-bottom: 3px; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php 
                $term = 0;
                if($salesOrder['SalesOrder']['payment_term_id'] > 0){
                    $sqlTerm = mysql_query("SELECT name FROM payment_terms WHERE id = {$salesOrder['SalesOrder']['payment_term_id']}");
                    if(mysql_num_rows($sqlTerm)){
                        $rowTerm = mysql_fetch_array($sqlTerm);
                        $term = $rowTerm[0];
                    }
                }
                echo $term;
                ?>
            </td>
            <td style="padding-top: 3px; padding-bottom: 3px; border-right: 1px solid #000; border-bottom: 1px solid #000; font-size: 14px; text-align: center;">
                <?php 
                if($term > 0){
                    $dueDate = date('Y-m-d', strtotime($salesOrder['SalesOrder']['order_date']. ' + '.$term.' days'));
                    echo dateShort($dueDate, 'd/M/Y');
                }else{
                    echo date("d/M/Y");
                }
                ?>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME . "/" . GENERAL_DESCRIPTION ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_QTY; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_F_O_C; ?></th>
                    <th style="width: 100px !important;"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                    <th style="width: 100px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th style="width: 100px !important;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($salesOrderDetails)) {
                    foreach ($salesOrderDetails as $salesOrderDetail) {
                        $discount = $salesOrderDetail['Discount']['amount'] + ($salesOrderDetail['Discount']['percent'] * $salesOrderDetail['SalesOrderDetail']['total_price']) / 100;
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $salesOrderDetail['Product']['code'] . ' - ' . $salesOrderDetail['Product']['name']; ?></td>
                            <td style="text-align: center;white-space: nowrap;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty'], 0) . ' ' . $salesOrderDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: center;white-space: nowrap;"><?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty_free'], 0) . ' ' . $salesOrderDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($discount, 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format(($salesOrderDetail['SalesOrderDetail']['total_price'] - $discount), 2); ?></td>
                        </tr>
                <?php
                    }
                }
                if (!empty($salesOrderServices)) {
                    foreach ($salesOrderServices as $salesOrderService) {
                        $discount = $salesOrderService['Discount']['amount'] + ($salesOrderService['Discount']['percent'] * $salesOrderService['SalesOrderService']['total_price']) / 100;
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $salesOrderService['Service']['name']; ?></td>
                            <td style="text-align: center"><?php echo number_format($salesOrderService['SalesOrderService']['qty'], 0); ?> </td>
                            <td style="text-align: center"><?php echo number_format($salesOrderService['SalesOrderService']['qty_free'], 0); ?> </td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($salesOrderService['SalesOrderService']['unit_price'], 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($discount, 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format(($salesOrderService['SalesOrderService']['total_price'] - $discount), 2); ?></td>
                        </tr>
                <?php
                    }
                }
                if (!empty($salesOrderMiscs)) {
                    foreach ($salesOrderMiscs as $salesOrderMisc) {
                        $discount = $salesOrderMisc['Discount']['amount'] + ($salesOrderMisc['Discount']['percent'] * $salesOrderMisc['SalesOrderMisc']['total_price']) / 100;
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $salesOrderMisc['SalesOrderMisc']['description']; ?></td>
                            <td style="text-align: center;white-space: nowrap;"><?php echo number_format($salesOrderMisc['SalesOrderMisc']['qty'], 0) . ' ' . $salesOrderMisc['Uom']['name']; ?> </td>
                            <td style="text-align: center;white-space: nowrap;"><?php echo number_format($salesOrderMisc['SalesOrderMisc']['qty_free'], 0) . ' ' . $salesOrderMisc['Uom']['name']; ?> </td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($salesOrderMisc['SalesOrderMisc']['unit_price'], 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($discount, 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format(($salesOrderMisc['SalesOrderMisc']['total_price'] - $discount), 2); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
        <br />
        <div style="float:left; width: 700px;">
            <table class="table_print">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE ?></th>
                    <th><?php echo TABLE_CODE ?></th>
                    <th><?php echo GENERAL_EXCHANGE_RATE ?> <?php echo TABLE_CURRENCY_KH; ?></th>
                    <th><?php echo GENERAL_PAID; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                    <th><?php echo GENERAL_PAID; ?> <?php echo TABLE_CURRENCY_KH; ?></th>
                    <th><?php echo GENERAL_BALANCE; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                    <th><?php echo TABLE_CHANGE; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                </tr>
                <?php
                $index = 0;
                $paid = 0;
                $paidKh = 0;
                foreach ($salesOrderReceipts as $salesOrderReceipt) {
                    $paid += $salesOrderReceipt['SalesOrderReceipt']['amount_us'];
                    $paidKh += $salesOrderReceipt['SalesOrderReceipt']['amount_other'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($salesOrderReceipt['SalesOrderReceipt']['pay_date'])); ?></td>
                        <td><?php echo $salesOrderReceipt['SalesOrderReceipt']['receipt_code']; ?></td>
                        <td style="text-align: right;">1 <?php echo $salesOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($salesOrderReceipt['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $salesOrderReceipt['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_us'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['amount_other'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['balance'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['change'], 2); ?></td>
                    </tr>
                <?php
                $balance = $salesOrderReceipt['SalesOrderReceipt']['amount_us'];
                }
                ?>
            </table>
        </div>
        <div style="float: right; width: 180px">
            <table align="right">
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_TOTAL_AMOUNT; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($salesOrder['SalesOrder']['total_amount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
                <?php
                if($salesOrder['SalesOrder']['total_vat'] > 0 && !empty($salesOrder['SalesOrder']['total_vat'])){
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_VAT; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($salesOrder['SalesOrder']['total_vat'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_DISCOUNT; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($salesOrder['SalesOrder']['discount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_TOTAL; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($salesOrder['SalesOrder']['total_vat']+$salesOrder['SalesOrder']['total_amount']-$salesOrder['SalesOrder']['discount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_PAID; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($paid, 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($paidKh,0); ?></b> R</td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_BALANCE; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($salesOrderReceipt['SalesOrderReceipt']['balance'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
            </table>
        </div>
        <div style="clear:both;"></div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <?php
                if ($salesOrder['SalesOrder']['balance'] > 0 && $sr['SalesOrderReceipt']['due_date'] != '' && $sr['SalesOrderReceipt']['due_date'] != '0000-00-00') {
                    echo GENERAL_AGING . " : " . date("d/m/Y", strtotime($sr['SalesOrderReceipt']['due_date']));
                }
                ?>
            </div>
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="float:right; width: 150px;">
            <table>
                <tr>
                    <td style="text-align: center">
                        Received By:
                    </td>
                </tr>
                <tr style="height: 70px">
                </tr>
                <tr>
                    <td style="text-align: center">
                        ..................................
                    </td>
                </tr>
            </table>
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