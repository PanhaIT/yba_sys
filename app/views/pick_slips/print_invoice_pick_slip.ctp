<style type="text/css" media="screen">
    .titleHeader{
        vertical-align: top; 
        padding-bottom:3px !important; 
        padding-top:3px !important;
        padding-right: 2px !important;
        font-size: 11px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .contentHeight{
        height: 25px !important;
    }
    .marginTop10{
        padding-top: 11px !important;
    }
    .titleHeaderTable{
        padding-bottom:3px !important; 
        padding-top:3px !important;
        font-size: 11px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 25px !important;
    }
    div.footerTablePrint {display: block; width: 100%; font-size: 11px; text-align: center;} 
    @font-face {   
        font-family: 'Moul'; 
        src: local('Moul'), url(../fonts/khmer-Moul.woff2) format('woff2'), url(../fonts/khmer-Moul.woff2) format('woff2');
    }
</style>
<style type="text/css" media="print">
    #footerTablePrint { width: 100%; }
    table.table_print {
        border-collapse: separate;
        border :  0px solid #000000;
        border-spacing: 0;
        width: 100%;
        border-color:  #000000 ;
    }
    .titleHeader{
        vertical-align: top; 
        padding-bottom:3px !important; 
        padding-top:3px !important;
        padding-right: 2px !important;
        font-size: 11px;
    }
    .titleContent{
        font-weight: bold;
        text-align: right;
    }
    .contentHeight{
        height: 25px !important;
    }
    .marginTop10{
        padding-top: 11px !important;
    }
    .titleHeaderTable{
        padding-bottom:3px !important; 
        padding-top:3px !important;
        font-size: 11px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 25px !important;
    }
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    #footerTablePrint { width: 100%; }
    @font-face {   
        font-family: 'Moul'; 
        src: local('Moul'), url(../fonts/khmer-Moul.woff2) format('woff2'), url(../fonts/khmer-Moul.woff2) format('woff2');
    }
    @page
    {
        /*this affects the margin in the printer settings*/  
        margin: 20px 20px 0 20px;
    }
</style>
<div class="print_doc">
    <?php
    include("includes/function.php");
    $vatInvoice = '';
    $display    = "";
    ?>
    <table style="width:100%;">
        <tr style="">
            <td style="width:55%; vertical-align: top; padding:5px 0px 0px 0px;"><img alt="" src="<?php echo $this->webroot;?>public/company_photo/<?php echo $salesOrder['Company']['photo'];?>" style="max-width: 230px;"/></td>
            <td style="border-width:3px;  width: 45%; vertical-align: top;">
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top; font-family: 'Moul'; font-size: 17px; line-height: 25px; font-weight: bold; ">
                            <?php echo $salesOrder['Branch']['name_other']; ?><br />
                            <span style="font-weight: bold; font-size: 17px; font-weight: bold; font-family: cambria;">
                                <?php echo $salesOrder['Branch']['name']; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">
                            អាសយដ្ឋាន: <?php echo $salesOrder['Branch']['address']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">
                            ទូរស័ព្ទលេខ (Mobile) : (855) <?php echo $salesOrder['Branch']['telephone']; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div style="clear: both;"></div></td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%; vertical-align: top;">
        <tbody>
            <tr>
                <td style="">
                    <table cellpadding="0" cellspacing="0" style="margin-top: 5px; line-height: 20px; width: 100%; vertical-align: top;">
                        <tr>
                            <td style="border-width:3px;  width:55%;font-size: 11px; padding-top: 9px; padding-bottom: 8px; vertical-align: top;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:none; line-height: 15px; vertical-align: top; margin-top:-30px;">
                                    <tr>
                                        <td style="font-family: 'Moul'; font-size: 11px; font-weight: bold; width: 50%; padding-bottom:10px;">
                                            ដឹកជញ្ជូនទៅ <span style="font-size: 13px; font-weight: bold;">SalesOrder  To</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            អតិថិជន <span style="font-size: 16px; font-weight: bold;">Customer</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">ឈ្មោះក្រុមហ៊ុន : <?php echo $salesOrder['Customer']['name_kh']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">Company Name : <?php echo $salesOrder['Customer']['name']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">អាសយដ្ឋាន  :  
                                                <?php
                                                if($salesOrder['Customer']['type'] == 1){
                                                    if($salesOrder['Customer']['house_no'] != ''){
                                                        echo "លេខផ្ទះ: ".$salesOrder['Customer']['house_no'].", ";
                                                    }
                                                    if($salesOrder['Customer']['street_id'] != ''){
                                                        $sqlSt = mysql_query("SELECT * FROM streets WHERE id = ".$salesOrder['Customer']['street_id']);
                                                        $rowSt = mysql_fetch_array($sqlSt);
                                                        echo "ផ្លូវលេខ: ".$rowSt['name'].", ";
                                                    }
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
                                                    if($rowAddress['v_name'] != ''){
                                                        echo "ភូមិ: ".$rowAddress['v_name'].", ";
                                                    }
                                                    if($rowAddress['c_name'] != ''){
                                                        echo "ឃុំ/សង្កាត់: ".$rowAddress['c_name'].", ";
                                                    }
                                                    if($rowAddress['d_name'] != ''){
                                                        echo "ខ័ណ្ឌ/ស្រុក: ".$rowAddress['d_name'].", ";
                                                    }
                                                    if($rowAddress['p_name'] != ''){
                                                        echo "ខេត្ត/ក្រុង: ".$rowAddress['p_name'].", ";
                                                    }
                                                } else {
                                                    echo $salesOrder['Customer']['address']; 
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">Address :
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
                                                    }
                                                    echo nl2br($addressTop);
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 11px; padding-top: 4px;">
                                            <span style="font-size:11px;"><?php echo TABLE_VATTIN_KH;?></span><span style="font-size:11px; padding-left: 5px; text-transform: uppercase;">(<?php echo TABLE_VATTIN_EN;?>)</span>  : 
                                            <span style="font-size:10px; padding-top: 8px;">
                                            <?php 
                                                if($salesOrder['Customer']['vat'] != ""){
                                                    $vatCustomerConvert = str_split($salesOrder['Customer']['vat']);
                                                    $countVarCustmoer   = count($vatCustomerConvert);
                                                    for($i = 0; $i< $countVarCustmoer; $i++){
                                                        if($i == 0){
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px; margin-top:1px !mportant;'>".$vatCustomerConvert[$i]."</span>";
                                                        }else if($i == 4){
                                                            echo "<span> - </span>";
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px; margin-top:1px !mportant; margin-left: 2px;'>".$vatCustomerConvert[$i]."</span>";
                                                        }else{
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px;  margin-top:1px !mportant; margin-left: 2px;'>".$vatCustomerConvert[$i]."</span>";
                                                        }
                                                    }
                                                }
                                            ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">ទូរស័ព្ទ (Phone)  :  <?php echo $salesOrder['Customer']['main_number']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 11px; padding-top: 5px;">
                                            <span style="font-size:11px;">Memo  :  <?php echo $salesOrder['SalesOrder']['memo']; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="border-width:3px;  width:45%; font-size: 11px; vertical-align: top;padding-left: 5px; padding-top: 0px;">
                                <table style="width: 100%; padding: 0;border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td colspan="2" style="font-family: 'Moul'; font-size: 16px; font-weight: bold; width: 90%;">
                                            ចំណាំការដឹកជញ្ជូន <span style="font-size: 16px; font-weight: bold;">DELIVERY NOTE</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%; font-size: 11px;">
                                            លេខ ​​(DN)
                                        </td>
                                        <td style="width: 50%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 11px; font-weight: bold;">
                                                <?php 
                                                echo $delivery['Delivery']['code']; 
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%; font-size: 11px;">
                                            វិក័យប័ត្រ ​​(Invoice No)
                                        </td>
                                        <td style="width: 50%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 11px;">
                                                <?php echo $salesOrder['SalesOrder']['so_code']; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%;font-size: 11px; padding-top: 5px;">
                                            កាលបរិច្ចេទ Date
                                        </td>
                                        <td style="width:50%; font-size: 11px; vertical-align: top;">
                                            <div style="font-size: 11px; padding-top: 8px; vertical-align: top; margin-top: 3px;">
                                            <?php 
                                                $created = explode(" ", $delivery['Delivery']['created']);
                                                echo dateShort($delivery['Delivery']['date'], "d/m/Y")." ".$created[1];
                                            ?>  
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <!-- <td style="width:10%; vertical-align:top;">
                                <div id="qrCodeDelivery" style="float:right;"></div>
                            </td> -->
                        </tr>
                    </table>
                    <div style="height: 5px"></div>
                    <table class="table_print" style="margin-top: 0px; border: none;">
                        <tr>
                            <th class="first titleHeaderTable" style="width: 5%;"><?php echo 'ល.រ'; ?></th>
                            <th class="titleHeaderTable" style=""><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                            <th class="titleHeaderTable" style=""><?php echo 'បរិមាណ'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%;"><?php echo 'ឯកតាគិត'; ?></th>
                        </tr>
                        <tr>
                            <th class="first titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;width: 5%;"><?php echo 'No.'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;"><?php echo 'NAME OF PRODUCT'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double; width: 12%;"><?php echo 'QTY'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double; width: 12%;"><?php echo 'UOM'; ?></th>
                        </tr>
                        <?php
                        $index = 0;
                        $productNameKh = '';   
                        $totalDis  = 0;
                        $unitPrice = 0;
                        if(!empty($salesOrderDetails)){
                            foreach($salesOrderDetails as $salesOrderDetail){
                                ?>
                                <tr>
                                    <td class="first" style="font-size: 11px; padding-bottom: 3px; padding-top: 3px; text-align: center; height: 20px;"><?php echo ++$index; ?></td>
                                    <td style="font-size: 11px; font-weight: bold; padding-bottom: 3px; padding-top: 3px;">
                                        <?php echo $salesOrderDetail['Product']['name']; ?>
                                    </td>
                                    <td style="font-size: 11px; padding-bottom: 3px; padding-top: 3px; text-align: center;">
                                        <?php echo number_format($salesOrderDetail['SalesOrderDetail']['qty']+$salesOrderDetail['SalesOrderDetail']['qty_free'],0); ?>
                                    </td>
                                    <td style="font-size: 11px; padding-bottom: 3px; padding-top: 3px; text-align: center;">
                                        <?php echo $salesOrderDetail['Uom']['abbr']; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        $i = 14;
                        $row = $i - $index;
                        for($z = 1;$z<$row;$z++){
                        ?>
                        <tr class="rowListDN">
                            <td style="text-align: center; height: 20px;font-size: 11px; border-bottom:1px solid !important;  padding-top:3px;"><?php echo ++$index; ?></td>                                  
                            <td style="font-size: 11px; height: 20px;  padding-top:3px; padding-bottom:3px; border:1px solid !important; "></td>
                            <td style="text-align: right; font-size: 11px; padding-top:3px ;height: 20px;  padding-bottom:3px; border:1px solid !important;"></td>
                            <td style="text-align: right; font-size: 11px; border-bottom:1px solid !important; padding-top:3px; height: 20px;  padding-bottom:3px;"> </td>
                        </tr>
                        <?php $i++;
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 10px;"></td>
            </tr>
        </tfoot>
    </table>
    <div id="footerTablePrint">
        <table style="width: 100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <table style="width:100%; text-align: left; margin-top: 0px; margin-bottom: 10px; height:0px;">
                        <tr>
                            <td style="width:5%; font-size: 11px; font-weight: bold;">បានទទួលទំនិញត្រឹមត្រូវ: / Goods received in good order</td>
                        </tr>
                        <tr>
                            <td style="width:89%; font-size: 11px; ">
                                <div style="height:40px;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width:100%;"><div style="border:1px solid;"></div></td>
            </tr>
            <tr>
                <td style="font-size: 11px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 25%; vertical-align: bottom; text-align: left;">
                                <div style='font-size: 11px;'>For And On Behalf Of</div> <br />
                                <div style=" margin: 50px 0px 0px 0px; width: 70%;border-top: 1px solid #000; text-align: left; font-size: 11px; font-weight: bold; font-family: 'Calibri'; vertical-align:bottom;">
                                   Authorized signature(s)
                                </div>
                            </td>
                            <td style="width: 75%; vertical-align: bottom; text-align: left;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <br />
    <div style="width: 450px;">
        <div style="float:left;">
            <input type="button" value="<?php echo ACTION_PRINT; ?>" style="height: 75px;" id='btnDisappearPrint' class='noprint' />
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<!-- <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script> -->
<script type="text/javascript">
    $(document).ready(function() {
        $(document).dblclick(function() {
            window.close();
        });
        // $("#qrCodeDelivery").qrcode({
        //     width: 80,
        //     height: 80,
        //     text: "<?php echo $salesOrder['SalesOrder']['so_code'];?>"
        // });
        $("#btnDisappearPrint").click(function() {
            $("#footerTablePrint").show();
            $("#footerTablePrint").css("width", "100%");
            //Default printing if jsPrintsetup is not available
            window.print();
            window.close();  
        });
        $("#btnExportExcelSalesInvoice").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_invoice_vat_<?php echo $user['User']['id']; ?>.csv", "_self");
        });
    });
</script>
