<?php
    include("includes/function.php");
    $sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option FROM setting_options");
    $rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
</style>
<div class="print_doc">
    <?php
    $msg = MENU_VENDOR_CONSIGNMENT;
    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $vendorConsignment['VendorConsignment']['code'], 'logo' => $vendorConsignment['Company']['photo']));
    ?>
    <div style="height: 20px"></div>
    <table style="width: 100%;" cellpadding="5" cellspacing="0">
        <tr>
            <td style="width: 20%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_CONSIGNMENT_CODE; ?> :</td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;">
                <?php echo $vendorConsignment['VendorConsignment']['code']; ?>
            </td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
            <td style="width: 20%; font-size: 12px;">
                <?php echo $vendorConsignment['LocationGroup']['name']; ?>
            </td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
            <td style="width: 28%; font-size: 12px;">
                <?php echo $vendorConsignment['Location']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_CONSIGNMENT_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo dateShort($vendorConsignment['VendorConsignment']['date'], 'd/M/Y'); ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $vendorConsignment['Vendor']['name']; ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"></td>
            <td style="font-size: 12px;"></td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_NOTE; ?> :</td>
            <td colspan="5" style="font-size: 12px;">
                <?php echo $vendorConsignment['VendorConsignment']['note']; ?>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print">
                <tr>
                    <th class="first" style="text-transform: uppercase; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 10%; text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo TABLE_SKU; ?></th>
                    <th style="width: 35%; text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo TABLE_QTY; ?></th>
                    <th style="text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo TABLE_UOM; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo TABLE_UNIT_COST; ?></th>
                    <th style="width: 15%; text-transform: uppercase; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo GENERAL_AMOUNT; ?></th>
                </tr>
                <?php
                $index = 0;
                if (!empty($vendorConsignmentDetails)) {
                    foreach ($vendorConsignmentDetails as $vendorConsignmentDetail) {
                ?>
                        <tr><td class="first" style="text-align: center; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;"><?php echo++$index; ?></td>
                            <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $vendorConsignmentDetail['Product']['code']; ?></td>
                            <td style="font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $vendorConsignmentDetail['Product']['name']; ?></td>
                            <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['qty'], 0); ?> </td>
                            <td style="text-align: center; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo $vendorConsignmentDetail['Uom']['abbr']; ?> </td>
                            <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['unit_cost'], 2); ?></td>
                            <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format(($vendorConsignmentDetail['VendorConsignmentDetail']['total_cost']), 2); ?></td>
                        </tr>
                <?php
                        
                    }
                }
                ?>
                <tr>
                    <td class="first" style="border-bottom: none; border-left: none;text-align: right; font-size: 12px; height: 20px; padding-bottom: 0px; padding-top: 0px;" colspan="6"><b><?php echo TABLE_TOTAL; ?></b></td>
                    <td style="text-align: right; font-size: 12px; padding-bottom: 0px; padding-top: 0px;"><?php echo number_format(($vendorConsignment['VendorConsignment']['total_amount']), 2); ?> <?php echo $vendorConsignment['CurrencyCenter']['symbol']; ?></td>
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