<?php
    // Get Decimal
    $sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
    $rowOption = mysql_fetch_array($sqlOption);
    include("includes/function.php");
?>
<div class="print_doc">
    <?php
    $sqlCom = mysql_query("SELECT photo FROM companies WHERE id = ".$sr['PurchaseOrder']['company_id']);
    $rowCom = mysql_fetch_array($sqlCom);
    $sqlBranch = mysql_query("SELECT name, telephone, address FROM branches WHERE id = ".$sr['PurchaseOrder']['branch_id']);
    $rowBranch = mysql_fetch_array($sqlBranch);
    $msg = ACTION_RECEIPT . ' <br /> ' . MENU_PURCHASING_RECEIPT;
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $sr['Pv']['pv_code'], 'title' => $rowBranch['name'], 'address' => $rowBranch['address'], 'telephone' => $rowBranch['telephone'], 'logo' => $rowCom[0]));
    ?>
    <div style="height: 30px"></div>
    <table width="100%">
        <tr>
            <td></td>
        </tr>
    </table>
    <br />
    <div>
        <br/>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_NAME . "/" . GENERAL_DESCRIPTION; ?></th>
                    <th style="white-space: nowrap;"><?php echo TABLE_QTY; ?></th>
                    <th style="white-space: nowrap;"><?php echo TABLE_F_O_C; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_UNIT_COST; ?></th>
                    <th style="width: 100px !important;"><?php echo GENERAL_DISCOUNT; ?></th>
                    <th style="width: 100px !important;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($purchaseOrderDetails)) {
                    foreach ($purchaseOrderDetails as $purchaseOrderDetail) {
                        $discount = $purchaseOrderDetail['PurchaseOrderDetail']['discount_amount'];
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseOrderDetail['Product']['code'] . ' - ' . $purchaseOrderDetail['Product']['name']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty'], 0) . ' ' . $purchaseOrderDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['qty_free'], 0) . ' ' . $purchaseOrderDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseOrderDetail['PurchaseOrderDetail']['unit_cost'], $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($discount, $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format(($purchaseOrderDetail['PurchaseOrderDetail']['total_cost'] - $discount), $rowOption[0]); ?></td>
                        </tr>
                <?php
                    }
                }
                if (!empty($purchaseOrderServices)) {
                    foreach ($purchaseOrderServices as $purchaseOrderService) {
                        $discount = $purchaseOrderService['PurchaseOrderService']['discount_amount'];
                ?>
                        <tr>
                            <td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseOrderService['Service']['name']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty'], 0); ?> </td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderService['PurchaseOrderService']['qty_free'], 0); ?> </td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseOrderService['PurchaseOrderService']['unit_cost'], $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($discount, $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format(($purchaseOrderService['PurchaseOrderService']['total_cost'] - $discount), $rowOption[0]); ?></td>
                        </tr>
                <?php
                    }
                }
              
                if (!empty($purchaseOrderMiscs)) {
                    foreach ($purchaseOrderMiscs as $purchaseOrderMisc) {
                        $discount = $purchaseOrderMisc['PurchaseOrderMisc']['discount_amount'];
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $purchaseOrderMisc['PurchaseOrderMisc']['description']; ?></td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderMisc['PurchaseOrderMisc']['qty'], 0) . ' ' . $purchaseOrderMisc['Uom']['abbr']; ?> </td>
                            <td style="text-align: center"><?php echo number_format($purchaseOrderMisc['PurchaseOrderMisc']['qty_free'], 0) . ' ' . $purchaseOrderMisc['Uom']['abbr']; ?> </td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($purchaseOrderMisc['PurchaseOrderMisc']['unit_cost'], $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format($discount, $rowOption[0]); ?></td>
                            <td style="text-align: right"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;">$</div><?php echo number_format(($purchaseOrderMisc['PurchaseOrderMisc']['total_cost'] - $discount), $rowOption[0]); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
       <br />
       <table class="table_print">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_DATE ?></th>
                    <th><?php echo TABLE_CODE ?></th>
                    <th><?php echo GENERAL_EXCHANGE_RATE ?> (R)</th>
                    <th><?php echo GENERAL_PAID . TABLE_CURRENCY_DEFAULT ?></th>
                    <th><?php echo GENERAL_PAID; ?> (R)</th>
                    <th><?php echo GENERAL_BALANCE . TABLE_CURRENCY_DEFAULT ?></th>
                </tr>
                <?php
                $index = 0;
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($sr['Pv']['pay_date'])); ?></td>
                        <td><?php echo $sr['Pv']['pv_code']; ?></td>
                        <td style="text-align: right;">1 <?php echo $purchaseOrder['CurrencyCenter']['symbol']; ?> = <?php echo number_format($sr['ExchangeRate']['rate_to_sell'], 9); ?> <?php echo $sr['CurrencyCenter']['symbol']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($sr['Pv']['amount_us'], $rowOption[0]); ?></td>
                        <td style="text-align: right;"><?php echo number_format($sr['Pv']['amount_other'], $rowOption[0]); ?></td>
                        <td style="text-align: right;"><?php echo number_format($sr['Pv']['balance'], $rowOption[0]); ?></td>
                    </tr>
       </table>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
    <br/>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>