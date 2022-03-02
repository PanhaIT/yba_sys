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
    $msg = "Customer Consignment";
    $telTitle     = 'Tel: ';
    $companyTitle = $consignment['Branch']['name'];
    echo $this->element('/print/header-invoice', array('msg' => $msg, 'barcode' => $consignment['Consignment']['code'], 'address' => $consignment['Branch']['address'], 'telephone' => $telTitle.$consignment['Branch']['telephone'], 'logo' => $consignment['Company']['photo'], 'title' => $companyTitle, 'mail' => $consignment['Branch']['email_address']));
    ?>
    <div style="height: 10px"></div>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 15%;" class="titleHeader titleContent contentHeight"></td>
            <td style="width: 39%;" class="titleHeader"></td>
            <td style="width: 17%;" class="titleHeader titleContent">Customer Name:</td>
            <td class="titleHeader"><?php echo $consignment['Customer']['name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Consignment No:</td>
            <td class="titleHeader"><?php echo $consignment['Consignment']['code']; ?></td>
            <td class="titleHeader"></td>
            <td rowspan="2" class="titleHeader">
                <?php
                $addressTop = '';
                $addressBottom = '';
                if($consignment['Customer']['type'] == 1){
                    if($consignment['Customer']['province_id'] > 0){
                        $provinceId = $consignment['Customer']['province_id'];
                        $districtId = $consignment['Customer']['district_id']>0?$consignment['Customer']['district_id']:'0';
                        $communeId  = $consignment['Customer']['commune_id']>0?$consignment['Customer']['commune_id']:'0';
                        $villageId  = $consignment['Customer']['village_id']>0?$consignment['Customer']['village_id']:'0';
                        $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$consignment['Customer']['province_id']}");    
                        $rowAddress = mysql_fetch_array($sqlAddress);
                    }else{
                        $rowAddress['p_name'] = '';
                        $rowAddress['d_name'] = '';
                        $rowAddress['c_name'] = '';
                        $rowAddress['v_name'] = '';
                    }
                    $house = $consignment['Customer']['house_no']!=''?$consignment['Customer']['house_no'].",":'';
                    $street = '';
                    if($consignment['Customer']['street_id'] != ''){
                        $sqlStreet = mysql_query("SELECT name FROM streets WHERE id = ".$consignment['Customer']['street_id']);
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
                    $addressTop =  $consignment['Customer']['address'];
                }
                echo $addressTop."<br />";
                echo $addressBottom;
                ?>
            </td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Date</td>
            <td class="titleHeader"><?php echo dateShort($consignment['Consignment']['date'], "d/M/Y"); ?></td>
            <td class="titleHeader"></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent"></td>
            <td class="titleHeader marginTop10"></td>
            <td class="titleHeader titleContent">ATTN:</td>
            <td class="titleHeader marginTop10"><?php echo $consignment['CustomerContact']['contact_name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight"></td>
            <td class="titleHeader"></td>
            <td class="titleHeader titleContent">H/P:</td>
            <td class="titleHeader"><?php echo $consignment['CustomerContact']['contact_telephone']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight"></td>
            <td class="titleHeader"></td>
            <td class="titleHeader titleContent">E-mail:</td>
            <td class="titleHeader"><?php echo $consignment['CustomerContact']['contact_email']; ?></td>
        </tr>
    </table>
    <div style="height: 10px"></div>
    <div>
        <?php
            if (!empty($consignmentDetails)) {
        ?>
        <div>
            <table class="table_print" style="border: none;">
                    <tr>
                        <th style="width: 7%;" class="titleHeaderTable titleHeaderHeight">No.</th>
                        <th style="width: 10%;" class="titleHeaderTable">SKU</th>
                        <th class="titleHeaderTable">DESCRIPTION</th>
                        <th style="width: 7%;" class="titleHeaderTable">QTY</th>
                        <th style="width: 9%;" class="titleHeaderTable">UoM</th>
                        <th style="width: 9%;" class="titleHeaderTable">Unit Price</th>
                        <th style="width: 15%;" class="titleHeaderTable">Total Price</th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($consignmentDetails as $consignmentDetail) {
                        // Check Name With Customer
                        $productName = $consignmentDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$consignmentDetail['Product']['id']." AND customer_id = ".$consignment['Customer']['id']." ORDER BY created DESC LIMIT 1");
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
                                echo $consignmentDetail['Product']['code']; 
                                ?>
                            </td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $productName; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($consignmentDetail['ConsignmentDetail']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $consignmentDetail['Uom']['abbr'];
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $consignment['CurrencyCenter']['symbol']; ?></span>
                                <?php 
                                echo number_format($consignmentDetail['ConsignmentDetail']['unit_price'], 2);
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $consignment['CurrencyCenter']['symbol']; ?></span>
                                <?php echo number_format($consignmentDetail['ConsignmentDetail']['total_price'], 2); ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                        <tr>
                            <td colspan="5" style="padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="text-align: right; text-transform: uppercase; font-size: 10px; font-weight: bold; height: 20px; padding-top: 0px; padding-bottom: 0px;">TOTAL</td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;"><span style="float: left; width: 12px; font-size: 11px;"><?php echo $consignment['CurrencyCenter']['symbol']; ?></span><?php echo number_format($consignment['Consignment']['total_amount'], 2); ?></td>
                        </tr>
            </table>
        </div>
        <?php
                }
        ?>
        <br />
        <div style="font-size: 12px; font-weight: bold;"><b style="font-size: 11px; font-weight: bold;">Note:</b> <?php echo $consignment['Consignment']['note']; ?></div>
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