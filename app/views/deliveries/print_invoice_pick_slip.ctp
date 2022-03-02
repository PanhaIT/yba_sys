<?php 
include("includes/function.php");
?>
<style type="text/css" media="print">
    #footerTablePrint { width: 100%; position: fixed; bottom: 0px; }
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
</style>
<div class="print_doc">
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <td style="height: 95px; vertical-align: bottom; padding-left: 40px;">
                    <img src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $salesOrder['Company']['photo']; ?>" style=" width: 130px; margin: 0px auto;" />
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td style=" text-align: center; font-size: 20px;">Delivery Note</td>
                        </tr>
                    </table>
                    <div style="height: 10px"></div>
                    <table cellpadding="5" width="100%">
                        <tr>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px; width: 8%;">To:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px; width: 57%;">
                                <?php echo $salesOrder['Customer']['name']; ?>
                            </td>
                            <td colspan="2" style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $salesOrder['Company']['name']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">Add:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
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
                                        $addressTop =  nl2br($salesOrder['Customer']['address']);
                                    }
                                    echo $addressTop."<br />";
                                    echo $addressBottom;
                                    ?>
                            </td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px; width: 8%;">Address:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;"><?php echo nl2br($salesOrder['Branch']['address']); ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php
                                $contactName = '';
                                $contactTel  = '';
                                if(!empty($salesOrder['Delivery']['customer_contact_id'])){
                                    $sqlContact = mysql_query("SELECT contact_name, contact_telephone FROM customer_contacts WHERE id = ".$salesOrder['Delivery']['customer_contact_id']);
                                    $rowContact  = mysql_fetch_array($sqlContact);
                                    $contactName = $rowContact['contact_name'];
                                    $contactTel  = $rowContact['contact_telephone'];
                                }
                                ?>
                                Attn:
                            </td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $contactName; ?>
                            </td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">DN Date:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;"><?php echo dateShort($salesOrder['Delivery']['date']); ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                Tel: 
                            </td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;"><?php echo $contactTel; ?></td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">DN Code:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;"><?php echo $salesOrder['Delivery']['code']; ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;">Ship To:</td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;"><?php echo $salesOrder['Delivery']['ship_to']; ?></td>
                            <td style="vertical-align: top; font-size: 11px; padding-top: 0px; padding-bottom: 0px;" colspan="2"></td>
                        </tr>
                    </table>
                    <br />
                    <table class="table_print">
                        <tr>
                            <th class="first" style="width:5%; font-size: 10px; padding-bottom: 0px; padding-top: 0px; height: 20px;">No</th>
                            <th style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Description</th>
                            <th style="width: 13%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Brand</th>
                            <th style="width: 10%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Code</th>
                            <th style="width:7%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Qty</th>
                            <th style="width:10%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">UoM</th>
                            <th style="width:10%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Price</th>
                            <th style="width:12%; font-size: 10px; padding-bottom: 0px; padding-top: 0px;">Misc</th>
                        </tr>
                    <?php
                    if(!empty($salesOrderDetails)){
                        $index = 0;
                        foreach($salesOrderDetails AS $salesOrderDetail){
                            $price = $salesOrderDetail['SalesOrderDetail']['unit_price'];
                            $sqlPgroups  = mysql_query("SELECT GROUP_CONCAT(name) FROM pgroups WHERE id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$salesOrderDetail['Product']['id'].")");
                            $rowPgroups  = mysql_fetch_array($sqlPgroups);
                    ?>
                        <tr>
                            <td class="first" style="font-size: 10px; padding-bottom: 0px; padding-top: 0px; text-align: center; height: 20px;"><?php echo ++$index; ?></td>
                            <td style="font-size: 10px; font-weight: bold; padding-bottom: 0px; padding-top: 0px;">
                                <?php echo $salesOrderDetail['Product']['name']; ?>
                            </td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;"><?php echo $rowPgroups[0]; ?></td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;"><?php echo $salesOrderDetail['Product']['code']; ?></td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px; text-align: center;">
                                <?php echo $salesOrderDetail['SalesOrderDetail']['qty']; ?>
                            </td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px; text-align: center;">
                                <?php echo $salesOrderDetail['Uom']['abbr']; ?>
                            </td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px; text-align: right;">
                                <span style="float: left; width: 12px; font-size: 11px;">$</span> <?php echo number_format($price, 2); ?>
                            </td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;"></td>
                        </tr>
                        <!-- Product Spec -->
                        <tr>
                            <td class="first" style="font-size: 10px; height: 20px; padding-bottom: 0px; padding-top: 0px; text-align: center;"></td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px; vertical-align: top;"><?php echo nl2br($salesOrderDetail['Product']['spec']); ?></td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;"></td>
                            <td style="font-size: 10px; padding-bottom: 0px; padding-top: 0px;"></td>
                            <td style="font-size: 10px; padding-bottom: 2px; padding-top: 2px; text-align: center;"></td>
                            <td style="font-size: 10px; padding-bottom: 2px; padding-top: 2px; text-align: center;"></td>
                            <td style="font-size: 10px; padding-bottom: 2px; padding-top: 2px; text-align: center;"></td>
                            <td style="font-size: 10px; padding-bottom: 2px; padding-top: 2px; text-align: center;"></td>
                        </tr>
                    <?php
                        }
                    }
                    ?>
                    </table>
                    <div style=" margin-top: 20px;">
                        <table style="width: 100%;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style=" vertical-align: top;">
                                    <table cellpadding="0" cellspacing="0" style="width: 200px;">
                                        <tr>
                                            <td style="text-align: left; font-size: 11px;">Acknowledged By </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 160px; text-align: left; vertical-align: bottom; font-size: 11px;">
                                                Name:<br/><br/>
                                                Date:&nbsp;&nbsp;&nbsp;........................................
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 33%; vertical-align: top;">
                                    <table cellpadding="0" cellspacing="0" style="width: 200px;">
                                        <tr>
                                            <td style="text-align: left; font-size: 11px;">Received By </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 160px; text-align: left; vertical-align: bottom; font-size: 11px;">
                                                Name:<br/><br/>
                                                Date:&nbsp;&nbsp;&nbsp;........................................
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 34%; text-align: right;" rowspan="2">
                                    <table cellpadding="0" cellspacing="0" style="width: 200px;">
                                        <tr>
                                            <td style="text-align: left; font-size: 11px;">Delivered By </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 160px; text-align: left; vertical-align: bottom; font-size: 11px;">
                                                Name:<br/><br/>
                                                Date:&nbsp;&nbsp;&nbsp;........................................
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style=" margin-top: 11px; font-size: 11px;">Delivery received Note: <?php echo $salesOrder['Delivery']['note']; ?></div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 90px;">
                    &nbsp;
                    <table id="footerTablePrint" style="display: none;">
                        <tr>
                            <td style="font-size: 11px; width: 20px; padding-top: 0px; padding-bottom: 0px;">
                                Tel/Fax: 
                            </td>
                            <td style="font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $salesOrder['Branch']['telephone']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px; vertical-align: top; padding-top: 0px; padding-bottom: 0px;">
                                Address: 
                            </td>
                            <td style="font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo nl2br($salesOrder['Branch']['address']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                Website: 
                            </td>
                            <td style="font-size: 11px; padding-top: 0px; padding-bottom: 0px;">
                                <?php echo $salesOrder['Company']['website']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
    <br />
    <div style="clear:both;"></div>
    <div style="float:left;width: 450px">
        <div>
            <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' class='noprint'>
        </div>
    </div>
    <div style="clear:both"></div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
        $("#btnDisappearPrint").click(function(){
            $("#footerTablePrint").show();
            $("#footerTablePrint").css("width", "100%");
            window.print();
            window.close();
        });
    });
</script>