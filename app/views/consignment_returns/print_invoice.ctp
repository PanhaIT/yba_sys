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
    $msg = "Customer Consignment Return";
    $telTitle     = 'Tel: ';
    $companyTitle = $consignmentReturn['Branch']['name'];
    echo $this->element('/print/header-invoice', array('msg' => $msg, 'barcode' => $consignmentReturn['ConsignmentReturn']['code'], 'address' => $consignmentReturn['Branch']['address'], 'telephone' => $telTitle.$consignmentReturn['Branch']['telephone'], 'logo' => $consignmentReturn['Company']['photo'], 'title' => $companyTitle, 'mail' => $consignmentReturn['Branch']['email_address']));
    ?>
    <div style="height: 10px"></div>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 15%;" class="titleHeader titleContent contentHeight"></td>
            <td style="width: 39%;" class="titleHeader"></td>
            <td style="width: 17%;" class="titleHeader titleContent">Customer Name:</td>
            <td class="titleHeader"><?php echo $consignmentReturn['Customer']['name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Consignment Return No:</td>
            <td class="titleHeader"><?php echo $consignmentReturn['ConsignmentReturn']['code']; ?></td>
            <td class="titleHeader"></td>
            <td rowspan="2" class="titleHeader">
                <?php
                $addressTop = '';
                $addressBottom = '';
                if($consignmentReturn['Customer']['type'] == 1){
                    if($consignmentReturn['Customer']['province_id'] > 0){
                        $provinceId = $consignmentReturn['Customer']['province_id'];
                        $districtId = $consignmentReturn['Customer']['district_id']>0?$consignmentReturn['Customer']['district_id']:'0';
                        $communeId  = $consignmentReturn['Customer']['commune_id']>0?$consignmentReturn['Customer']['commune_id']:'0';
                        $villageId  = $consignmentReturn['Customer']['village_id']>0?$consignmentReturn['Customer']['village_id']:'0';
                        $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$consignmentReturn['Customer']['province_id']}");    
                        $rowAddress = mysql_fetch_array($sqlAddress);
                    }else{
                        $rowAddress['p_name'] = '';
                        $rowAddress['d_name'] = '';
                        $rowAddress['c_name'] = '';
                        $rowAddress['v_name'] = '';
                    }
                    $house = $consignmentReturn['Customer']['house_no']!=''?$consignmentReturn['Customer']['house_no'].",":'';
                    $street = '';
                    if($consignmentReturn['Customer']['street_id'] != ''){
                        $sqlStreet = mysql_query("SELECT name FROM streets WHERE id = ".$consignmentReturn['Customer']['street_id']);
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
                    $addressTop =  $consignmentReturn['Customer']['address'];
                }
                echo $addressTop."<br />";
                echo $addressBottom;
                ?>
            </td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight">Date</td>
            <td class="titleHeader"><?php echo dateShort($consignmentReturn['ConsignmentReturn']['date'], "d/M/Y"); ?></td>
            <td class="titleHeader"></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent"></td>
            <td class="titleHeader marginTop10"></td>
            <td class="titleHeader titleContent">ATTN:</td>
            <td class="titleHeader marginTop10"><?php echo $consignmentReturn['CustomerContact']['contact_name']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight"></td>
            <td class="titleHeader"></td>
            <td class="titleHeader titleContent">H/P:</td>
            <td class="titleHeader"><?php echo $consignmentReturn['CustomerContact']['contact_telephone']; ?></td>
        </tr>
        <tr>
            <td class="titleHeader titleContent contentHeight"></td>
            <td class="titleHeader"></td>
            <td class="titleHeader titleContent">E-mail:</td>
            <td class="titleHeader"><?php echo $consignmentReturn['CustomerContact']['contact_email']; ?></td>
        </tr>
    </table>
    <div style="height: 10px"></div>
    <div>
        <?php
            if (!empty($consignmentReturnDetails)) {
        ?>
        <div>
            <table class="table_print" style="border: none;">
                    <tr>
                        <th style="width: 7%;" class="titleHeaderTable titleHeaderHeight">No.</th>
                        <th style="width: 10%;" class="titleHeaderTable">SKU</th>
                        <th class="titleHeaderTable">DESCRIPTION</th>
                        <th style="width: 7%;" class="titleHeaderTable">QTY</th>
                        <th style="width: 9%;" class="titleHeaderTable">UoM</th>
                        <th style="width: 15%;" class="titleHeaderTable"><?php echo TABLE_LOTS_NO; ?></th>
                        <th style="width: 15%;" class="titleHeaderTable"><?php echo TABLE_EXPIRED_DATE; ?></th>
                    </tr>
                    <?php
                    $index = 0;
                    foreach ($consignmentReturnDetails as $consignmentReturnDetail) {
                        // Check Name With Customer
                        $productName = $consignmentReturnDetail['Product']['name'];
                        $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$consignmentReturnDetail['Product']['id']." AND customer_id = ".$consignmentReturn['Customer']['id']." ORDER BY created DESC LIMIT 1");
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
                                echo $consignmentReturnDetail['Product']['code']; 
                                ?>
                            </td>
                            <td style="font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $productName; ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo number_format($consignmentReturnDetail['ConsignmentReturnDetail']['qty'], 0);
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php 
                                echo $consignmentReturnDetail['Uom']['abbr'];
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php
                                $lotsNumber = '';
                                if($consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != '0' && $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != ''){
                                    $lotsNumber = $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'];
                                }
                                echo $lotsNumber;
                                ?>
                            </td>
                            <td style="text-align: right; font-size: 10px; padding-top: 0px; padding-bottom: 0px;">
                                <?php
                                $expriryDate = '';
                                if($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '' && $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '0000-00-00'){
                                    $expriryDate = dateShort($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']);
                                }
                                echo $expriryDate;
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
        <div style="font-size: 12px; font-weight: bold;"><b style="font-size: 11px; font-weight: bold;">Note:</b> <?php echo $consignmentReturn['ConsignmentReturn']['note']; ?></div>
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