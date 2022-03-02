<div class="print_doc">
    <?php
    include("includes/function.php");
    $msg = "<b style='font-size: 22px; font-weight: 600;'>វិក័យប័ត្រ<b><br/><b style='font-size: 15px; font-weight: 600;'>RECEIPT</b>";
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $transferOrder['TransferOrder']['to_code'], 'address' => $transferOrder['Branch']['address'], 'telephone' => $transferOrder['Branch']['telephone'], 'logo' => $transferOrder['Company']['photo'], 'title' => $transferOrder['Branch']['name']));
    ?>
    <div style="height: 30px"></div>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; width: 9%;"><?php echo MENU_BRANCH; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $this->data['Branch']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"></td>
            <td style="font-size: 12px; width: 20%;"></td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_NUMBER; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $this->data['TransferOrder']['to_code']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php echo TABLE_FROM_WAREHOUSE; ?>  :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $fromLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>        
            <td style="font-size: 12px;"><?php echo TABLE_TO_WAREHOUSE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $toLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo dateShort($this->data['TransferOrder']['order_date']); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_MEMO; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;" colspan="5">
                <div class="inputContainer">
                    <?php echo nl2br($this->data['TransferOrder']['note']); ?>
                </div>
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
                    <th style="width: 100px !important;">AVG Cost</th>
                    <th style="width: 100px !important;">Total</th>
                </tr>
                <?php
                $index = 0;
                if (!empty($transferOrderDetails)) {
                    foreach ($transferOrderDetails as $transferOrderDetail) {
                ?>
                        <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                            <td><?php echo $transferOrderDetail['Product']['code'] . ' - ' . $transferOrderDetail['Product']['name']; ?></td>
                            <td style="text-align: center;white-space: nowrap;">
                                <?php 
                                    echo number_format($transferOrderDetail['TransferOrderDetail']['qty'], 0); 
                                    $queryUom = mysql_query("SELECT name FROM uoms WHERE id = '".$transferOrderDetail['TransferOrderDetail']['qty_uom_id']."'");
                                    if(mysql_num_rows($queryUom)){
                                        $dataUom = mysql_fetch_array($queryUom);
                                        echo " ".$dataUom['name'];
                                    }
                                ?> 
                            </td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format($transferOrderDetail['TransferOrderDetail']['unit_cost'], 2); ?></td>
                            <td style="text-align: right"><span style="float: left; width: 12px; font-size: 11px;">$</span><?php echo number_format(($transferOrderDetail['TransferOrderDetail']['total_cost']), 2); ?></td>
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
                foreach ($transferOrderReceipts as $transferOrderReceipt) {
                    $paid += $transferOrderReceipt['TransferOrderReceipt']['amount_us'];
                    $paidKh += $transferOrderReceipt['TransferOrderReceipt']['amount_other'];
                ?>
                    <tr><td class="first" style="text-align: right;"><?php echo++$index; ?></td>
                        <td><?php echo date("d/m/Y", strtotime($transferOrderReceipt['TransferOrderReceipt']['pay_date'])); ?></td>
                        <td><?php echo $transferOrderReceipt['TransferOrderReceipt']['receipt_code']; ?></td>
                        <td style="text-align: right;">1 $ = <?php echo number_format($transferOrderReceipt['ExchangeRate']['rate_to_sell'], 9); ?> $</td>
                        <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['amount_us'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['amount_other'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['balance'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['change'], 2); ?></td>
                    </tr>
                <?php
                $balance = $transferOrderReceipt['TransferOrderReceipt']['amount_us'];
                }
                ?>
            </table>
        </div>
        <div style="float: right; width: 180px">
            <table align="right">
                <tr>
                    <td class="first" style="border-bottom: none; width: 100px; border-left: none; text-align: right;" colspan="5"><?php echo TABLE_TOTAL_AMOUNT; ?></td>
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($transferOrder['TransferOrder']['total_cost'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
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
                    <td style="text-align: right;width: 100px;"><b><?php echo number_format($transferOrderReceipt['TransferOrderReceipt']['balance'], 2); ?></b> <?php echo TABLE_CURRENCY_DEFAULT_BIG; ?></td>
                </tr>
            </table>
        </div>
        <div style="clear:both;"></div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <?php
                if ($transferOrder['TransferOrder']['balance'] > 0 && $sr['TransferOrderReceipt']['due_date'] != '' && $sr['TransferOrderReceipt']['due_date'] != '0000-00-00') {
                    echo GENERAL_AGING . " : " . date("d/m/Y", strtotime($sr['TransferOrderReceipt']['due_date']));
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