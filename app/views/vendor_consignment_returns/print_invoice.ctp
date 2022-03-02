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
    $msg = "Vendor Consignment Return";
    $telTitle     = 'Tel: ';
    $companyTitle = $vendorConsignmentReturn['Branch']['name'];
    echo $this->element('/print/header-invoice', array('msg' => $msg, 'barcode' => $vendorConsignmentReturn['VendorConsignmentReturn']['code'], 'address' => $vendorConsignmentReturn['Branch']['address'], 'telephone' => $telTitle.$vendorConsignmentReturn['Branch']['telephone'], 'logo' => $vendorConsignmentReturn['Company']['photo'], 'title' => $companyTitle, 'mail' => $vendorConsignmentReturn['Branch']['email_address']));
    ?>
    <div style="height: 10px"></div>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 15%;" class="titleHeader titleContent contentHeight"></td>
            <td style="width: 39%;" class="titleHeader"></td>
            <td style="width: 17%;" class="titleHeader titleContent">Vendor Name:</td>
            <td class="titleHeader"><?php echo $vendorConsignmentReturn['Vendor']['name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Consignment No:</td>
            <td class="titleHeader"><?php echo $vendorConsignmentReturn['VendorConsignmentReturn']['code']; ?></td>
            <td class="titleHeader"></td>
            <td rowspan="2" class="titleHeader">
                <?php
                $addressTop = nl2br($vendorConsignmentReturn['Vendor']['address']);
                echo $addressTop;
                ?>
            </td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Date</td>
            <td class="titleHeader"><?php echo dateShort($vendorConsignmentReturn['VendorConsignmentReturn']['date'], "d/M/Y"); ?></td>
            <td class="titleHeader"></td>
        </tr>
    </table>
    <div style="height: 10px"></div>
    <div>
        <?php
            if (!empty($vendorConsignmentReturnDetails)) {
        ?>
        <div>
            <table class="table_print" style="border: none;">
                    <tr>
                        <th style="width: 7%;" class="titleHeaderTable titleHeaderHeight">No.</th>
                        <th style="width: 10%;" class="titleHeaderTable">SKU</th>
                        <th class="titleHeaderTable">DESCRIPTION</th>
                        <th style="width: 9%;" class="titleHeaderTable">Lots Number</th>
                        <th style="width: 9%;" class="titleHeaderTable">Expiry Date</th>
                        <th style="width: 7%;" class="titleHeaderTable">QTY</th>
                        <th style="width: 9%;" class="titleHeaderTable">UoM</th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($vendorConsignmentReturnDetails as $vendorConsignmentReturnDetail) {
                        // Check Name With Vendor
                        $productName = $vendorConsignmentReturnDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$vendorConsignmentReturnDetail['Product']['id']." AND customer_id = ".$vendorConsignmentReturn['Vendor']['id']." ORDER BY created DESC LIMIT 1");
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
                                echo $vendorConsignmentReturnDetail['Product']['code']; 
                                ?>
                            </td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $productName; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number'];
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $vendorConsignmentReturnDetail['Uom']['abbr'];
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
            </table>
        </div>
        <?php
                }
        ?>
        <br />
        <div style="font-size: 12px; font-weight: bold;"><b style="font-size: 11px; font-weight: bold;">Note:</b> <?php echo $vendorConsignmentReturn['VendorConsignmentReturn']['note']; ?></div>
        <br />
        <div style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 33%; vertical-align: bottom; text-align: center; height: 110px;">
                        <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
                            Delivery By:
                        </div>
                    </td>
                    <td style="width: 34%; vertical-align: bottom; text-align: center;">
                        
                    </td>
                    <td style="width: 33%; vertical-align: bottom; text-align: center;">
                        <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
                            Vendor's Signature
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