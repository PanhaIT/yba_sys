<div class="print_doc">
    <?php
    include("includes/function.php");
    $msg = ACTION_RECEIPT . ' <br /> ' . MENU_CREDIT_MEMO_MANAGEMENT;
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $sr['CreditMemoReceipt']['receipt_code'], 'title' => $sr['Branch']['name'], 'address' => $sr['Branch']['address'], 'telephone' => $sr['Branch']['telephone'], 'logo' => $creditMemo['Company']['photo']));
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
                    <th class="first" style="width: 6%;"><?php echo TABLE_NO; ?></th>
                    <th style=""><?php echo TABLE_CODE; ?></th>
                    <th style="width: 30%"><?php echo TABLE_NAME . "/" . GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 10%;"><?php echo TABLE_QTY; ?></th>
                    <th style="width: 10%;"><?php echo TABLE_F_O_C; ?></th>
                    <th style="width: 11%;"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
                    <th style="width: 11%;"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th style="width: 11%;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($creditMemoDetails)) {
                    foreach ($creditMemoDetails as $creditMemoDetail) {
                        $discount = $creditMemoDetail['CreditMemoDetail']['discount_amount'];
                ?>
                        <tr>
                            <td class="first" style="text-align: center; font-size: 12px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px;"><?php echo $creditMemoDetail['Product']['code']; ?></td>
                            <td style="font-size: 12px;"><?php echo $creditMemoDetail['Product']['name']; ?></td>
                            <td style="text-align: center;white-space: nowrap; font-size: 12px;"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty'], 2) . ' ' . $creditMemoDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: center;white-space: nowrap; font-size: 12px;"><?php echo number_format($creditMemoDetail['CreditMemoDetail']['qty_free'], 2) . ' ' . $creditMemoDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($creditMemoDetail['CreditMemoDetail']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($discount, 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format(($creditMemoDetail['CreditMemoDetail']['total_price'] - $discount), 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                if (!empty($creditMemoServices)) {
                    foreach ($creditMemoServices as $creditMemoService) {
                        $discount = $creditMemoService['CreditMemoService']['discount_amount'];
                ?>
                        <tr>
                            <td class="first" style="text-align: center; font-size: 12px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px;"></td>
                            <td style="font-size: 12px;"><?php echo $creditMemoService['Service']['name']; ?></td>
                            <td style="text-align: center; font-size: 12px;"><?php echo number_format($creditMemoService['CreditMemoService']['qty'], 0); ?></td>
                            <td style="text-align: center; font-size: 12px;"><?php echo number_format($creditMemoService['CreditMemoService']['qty_free'], 0); ?></td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($creditMemoService['CreditMemoService']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($discount, 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format(($creditMemoService['CreditMemoService']['total_price'] - $discount), 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                if (!empty($creditMemoMiscs)) {
                    foreach ($creditMemoMiscs as $creditMemoMisc) {
                        $discount = $creditMemoMisc['CreditMemoMisc']['discount_amount'];
                ?>
                        <tr>
                            <td class="first" style="text-align: center; font-size: 12px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px;"></td>
                            <td style="font-size: 12px;"><?php echo $creditMemoMisc['CreditMemoMisc']['description']; ?></td>
                            <td style="text-align: center;white-space: nowrap; font-size: 12px;"><?php echo number_format($creditMemoMisc['CreditMemoMisc']['qty'], 0) . ' ' . $creditMemoMisc['Uom']['abbr']; ?> </td>
                            <td style="text-align: center;white-space: nowrap; font-size: 12px;"><?php echo number_format($creditMemoMisc['CreditMemoMisc']['qty_free'], 0) . ' ' . $creditMemoMisc['Uom']['abbr']; ?> </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($creditMemoMisc['CreditMemoMisc']['unit_price'], 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($discount, 2); ?>
                            </td>
                            <td style="text-align: right; font-size: 12px;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format(($creditMemoMisc['CreditMemoMisc']['total_price'] - $discount), 2); ?>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 14px;" colspan="7"><b><?php echo TABLE_SUB_TOTAL; ?></b></td>
                    <td style="text-align: right; font-size: 14px;"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($creditMemo['CreditMemo']['total_amount'], 2); ?></td>
                </tr>
                <?php
                if($creditMemo['CreditMemo']['total_vat'] > 0){
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right" colspan="7"><b><?php echo TABLE_VAT ?> (<?php echo number_format($creditMemo['CreditMemo']['vat_percent'], 2); ?>%)</b></td>
                    <td style="text-align: right; font-size: 14px;"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($creditMemo['CreditMemo']['total_vat'], 2); ?></td>
                </tr>
                <?php
                }
                if($creditMemo['CreditMemo']['discount'] > 0){
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 14px;" colspan="7"><b><?php echo GENERAL_DISCOUNT; ?></b></td>
                    <td style="text-align: right; font-size: 14px;"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($creditMemo['CreditMemo']['discount'], 2); ?></td>
                </tr>
                <?php
                }
                ?>
                <?php
                if($creditMemo['CreditMemo']['mark_up'] > 0){
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 14px;" colspan="7"><b><?php echo TABLE_MARK_UP; ?></b></td>
                    <td style="text-align: right; font-size: 14px;"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($creditMemo['CreditMemo']['mark_up'], 2); ?></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 14px;" colspan="7"><b><?php echo TABLE_TOTAL_AMOUNT; ?></b></td>
                    <td style="text-align: right; font-size: 14px;"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($creditMemo['CreditMemo']['total_amount']+$creditMemo['CreditMemo']['total_vat']+$creditMemo['CreditMemo']['mark_up']-$creditMemo['CreditMemo']['discount'], 2); ?></td>
                </tr>
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
                foreach ($creditMemoReceipts as $creditMemoReceipt) {
                    $paid += $creditMemoReceipt['CreditMemoReceipt']['amount_us'];
                    $paidKh += $creditMemoReceipt['CreditMemoReceipt']['amount_other'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($creditMemoReceipt['CreditMemoReceipt']['pay_date'])); ?></td>
                        <td><?php echo $creditMemoReceipt['CreditMemoReceipt']['receipt_code']; ?></td>
                        <td style="text-align: right;">1 <?php echo $creditMemo['CurrencyCenter']['symbol']; ?> = <?php echo number_format($creditMemoReceipt['ExchangeRate']['rate_to_sell'], 2); ?> <?php echo $creditMemoReceipt['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_us'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['amount_other'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['balance'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($creditMemoReceipt['CreditMemoReceipt']['change'], 2); ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        </div>
        <div style="float: right; width: 180px">
            <table align="right">
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_SUB_TOTAL; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['total_amount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <?php
                if ($creditMemo['CreditMemo']['total_vat'] > 0 && !empty($creditMemo['CreditMemo']['total_vat'])) {
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_VAT; ?> (<?php echo number_format($creditMemo['CreditMemo']['vat_percent'], 2); ?>%)</td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['total_vat'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <?php
                }
                if ($creditMemo['CreditMemo']['discount'] > 0) {
                    ?>
                    <tr>
                        <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_DISCOUNT; ?></td>
                        <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['discount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                    </tr>
                    <?php
                }
                ?>
                <?php
                if ($creditMemo['CreditMemo']['mark_up'] > 0) {
                    ?>
                    <tr>
                        <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_MARK_UP; ?></td>
                        <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['mark_up'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                    </tr>
                    <?php
                }?>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_TOTAL_AMOUNT; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['total_amount']+$creditMemo['CreditMemo']['total_vat']+$creditMemo['CreditMemo']['mark_up']-$creditMemo['CreditMemo']['discount'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_PAID; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($paid, 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_PAID; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($paidKh,0); ?></b> R</td>
                </tr>
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo GENERAL_BALANCE; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($creditMemo['CreditMemo']['balance'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG;?></td>
                </tr>
            </table>
        </div>
        <div style="clear:both;"></div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <?php
                if ($creditMemo['CreditMemo']['balance'] > 0 && $sr['CreditMemoReceipt']['due_date'] != '' && $sr['CreditMemoReceipt']['due_date'] != '0000-00-00') {
                    echo GENERAL_AGING . " : " . date("d/m/Y", strtotime($sr['CreditMemoReceipt']['due_date']));
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