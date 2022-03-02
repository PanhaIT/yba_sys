<div class="print_doc">
    <?php
    $sqlCom = mysql_query("SELECT photo FROM companies WHERE id = ".$sr['PurchaseReturn']['company_id']);
    $rowCom = mysql_fetch_array($sqlCom);
    include("includes/function.php");
    $msg = ACTION_RECEIPT . ' <br /> ' . MENU_PURCHASE_RETURN_MANAGEMENT;
    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $sr['PurchaseReturnReceipt']['receipt_code'], 'logo' => $rowCom[0]));
    ?>
    <div style="height: 30px"></div>
    <table width="100%">
        <tr>
            <td></td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME . "/" . GENERAL_DESCRIPTION ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_QTY ?></th>
                    <th style="width: 140px !important;"><?php echo SALES_ORDER_UNIT_PRICE ?> <?php echo  TABLE_CURRENCY_DEFAULT; ?></th>
                    <th style="width: 140px !important;"><?php echo SALES_ORDER_TOTAL_PRICE; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($purchaseReturnDetails)) {
                    foreach ($purchaseReturnDetails as $purchaseReturnDetail) {
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseReturnDetail['Product']['code'] . ' - ' . $purchaseReturnDetail['Product']['name']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['qty'], 0) . ' ' . $purchaseReturnDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['unit_price'], 2); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnDetail['PurchaseReturnDetail']['total_price'], 2); ?></td>
                        </tr>
                <?php
                    }
                }
                if (!empty($purchaseReturnServices)) {
                    foreach ($purchaseReturnServices as $purchaseReturnService) {
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseReturnService['Service']['name']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseReturnService['PurchaseReturnService']['qty'], 0); ?></td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnService['PurchaseReturnService']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnService['PurchaseReturnService']['total_price'], 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                if (!empty($purchaseReturnMiscs)) {
                    foreach ($purchaseReturnMiscs as $purchaseReturnMisc) {
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseReturnMisc['PurchaseReturnMisc']['description']; ?></td>
                            <td style="text-align: center;white-space: nowrap;"><?php echo number_format($purchaseReturnMisc['PurchaseReturnMisc']['qty'], 0) . ' ' . $purchaseReturnMisc['Uom']['abbr']; ?> </td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnMisc['PurchaseReturnMisc']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right">
                                <div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div> <?php echo number_format($purchaseReturnMisc['PurchaseReturnMisc']['total_price'], 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
        <br />
        <div style="float:left; width: 630px;">
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
                $balance = 0;
                foreach ($purchaseReturnReceipts as $purchaseReturnReceipt) {
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($purchaseReturnReceipt['PurchaseReturnReceipt']['created'])); ?></td>
                        <td><?php echo $purchaseReturnReceipt['PurchaseReturnReceipt']['receipt_code']; ?></td>
                        <td style="text-align: right;">1 <?php echo $purchaseReturn['CurrencyCenter']['symbol']; ?> = <?php echo number_format($purchaseReturnReceipt['ExchangeRate']['rate_to_sell'], 2); ?> <?php echo $purchaseReturnReceipt['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_us'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['amount_other'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['balance'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($purchaseReturnReceipt['PurchaseReturnReceipt']['change'], 2); ?></td>
                    </tr>
                <?php
                $balance = $purchaseReturnReceipt['PurchaseReturnReceipt']['balance'];
                }
                ?>
            </table>
        </div>
        <div style="float: right; width: 250px">
            <table align="right">
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="5"><?php echo TABLE_SUB_TOTAL ?></td>
                    <td style="text-align: right"><b><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'], 2); ?></b><?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="5"><?php echo TABLE_VAT; ?> (<?php echo $purchaseReturn['PurchaseReturn']['vat_percent']; ?>%)</td>
                    <td style="text-align: right"><b><?php echo number_format($purchaseReturn['PurchaseReturn']['total_vat'], 2); ?></b><?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right;" colspan="5"><?php echo TABLE_TOTAL_AMOUNT; ?></td>
                    <td style="text-align: right"><b><?php echo number_format($purchaseReturn['PurchaseReturn']['total_amount'] + $purchaseReturn['PurchaseReturn']['total_vat'], 2); ?></b><?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_PAID; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format(($purchaseReturn['PurchaseReturn']['total_amount'] - $balance), 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_BALANCE; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($balance, 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
            </table>
        </div>
        <div style="clear:both;"></div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <?php
                if ($purchaseReturn['PurchaseReturn']['balance'] > 0 && $sr['PurchaseReturnReceipt']['due_date'] != '' && $sr['PurchaseReturnReceipt']['due_date'] != '0000-00-00') {
                    echo GENERAL_AGING . " : " . date("d/m/Y", strtotime($sr['PurchaseReturnReceipt']['due_date']));
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
                        <?php echo SALES_ORDER_RECEIVER; ?>
                    </td>
                </tr>
                <tr style="height: 40px">
                </tr>
                <tr>
                    <td style="text-align: center">
                        <?php echo "{$sr['User']['first_name']} {$sr['User']['last_name']}"; ?>
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