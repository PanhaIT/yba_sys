<style type="text/css" media="screen">
    .titleHeader{
        vertical-align: top; 
        padding-bottom:3px !important; 
        padding-top:3px !important;
        padding-right: 2px !important;
        font-size: 12px;
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
        font-size: 12px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 25px !important;
    }
    div.footerTablePrint {display: block; width: 100%; position: fixed; bottom: 2px; font-size: 12px; text-align: center;} 
    @font-face {   
        font-family: 'Moul'; 
        src: local('Moul'), url(../fonts/khmer-Moul.woff2) format('woff2'), url(../fonts/khmer-Moul.woff2) format('woff2');
    }
</style>
<style type="text/css" media="print">
    #footerTablePrint { width: 100%; position: fixed; bottom: 0px; }
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
        font-size: 12px;
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
        font-size: 12px;
        color: #000;
    }
    .titleHeaderHeight{
        height: 25px !important;
    }
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    #footerTablePrint { width: 100%; position: fixed; bottom: 0px; }
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
    //debug($salesOrder);
    include("includes/function.php");
    $vatInvoice = '';
    if ($salesOrder['SalesOrder']['vat_percent'] > 0) {
        $vatInvoice = $salesOrder['Company']['vat_number'];
    }
    $display = "";
    $resultVAT = "VAT Included";
    if ($salesOrder['SalesOrder']['vat_percent'] <= 0) {
        $display = "display:none;";
        $resultVAT = "VAT Excluded";
    }
    ?>
    <table style="width:100%;">
        <tr style="">
            <td style="width:20%; vertical-align: top; padding:5px 10px 0px 0px;"><img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $salesOrder['Company']['photo']; ?>" style="max-width: 230px;"/></td>
            <td style="border-width:3px;  width: 80%; vertical-align: top;">
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top; font-family: 'Moul'; font-size: 17px; line-height: 25px;">
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
                            ទូរស័ព្ទលេខ (Mobile): (855) <?php echo $salesOrder['Branch']['telephone']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">
                            Email: <?php echo $salesOrder['Branch']['email_address']; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div style="clear: both;"></div></td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%; <?php if ($salesOrder['Branch']['name_other'] == '') { ?> margin-top: -40px; <?php } ?>">
        <tbody>
            <tr>
                <td style="">
                    <table cellpadding="0" cellspacing="0" style="margin-top: 5px; height: 100px; width: 100%; vertical-align: top;">
                        <tr>
                            <td style="border-width:3px; width:50%;font-size: 12px; padding-top: 9px; padding-bottom: 8px; vertical-align: top;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            អតិថិជន <span style="font-size: 16px; font-weight: bold;">Customer</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">ឈ្មោះក្រុមហ៊ុន : <?php echo $salesOrder['Customer']['name_kh']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Company Name : <?php echo $salesOrder['Customer']['name']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
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
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Address  :  
                                                <?php 
                                                if($salesOrder['Customer']['type'] == 1){
                                                    if($salesOrder['Customer']['house_no'] != ''){
                                                        echo TABLE_NO.": ".$salesOrder['Customer']['house_no'].", ";
                                                    }
                                                    if($salesOrder['Customer']['street_id'] != ''){
                                                        $sqlSt = mysql_query("SELECT * FROM streets WHERE id = ".$salesOrder['Customer']['street_id']);
                                                        $rowSt = mysql_fetch_array($sqlSt);
                                                        echo TABLE_STREET.": ".$rowSt['name'].", ";
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
                                                        echo TABLE_VILLAGE.": ".$rowAddress['v_name'].", ";
                                                    }
                                                    if($rowAddress['c_name'] != ''){
                                                        echo TABLE_COMMUNE.": ".$rowAddress['c_name'].", ";
                                                    }
                                                    if($rowAddress['d_name'] != ''){
                                                        echo TABLE_DISTRICT.": ".$rowAddress['d_name'].", ";
                                                    }
                                                    if($rowAddress['p_name'] != ''){
                                                        echo TABLE_PROVINCE.": ".$rowAddress['p_name'].", ";
                                                    }
                                                } else {
                                                    echo $salesOrder['Customer']['address']; 
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 4px;">
                                            <span style="font-size:11px;"><?php echo TABLE_VATTIN_KH; ?></span><span style="font-size:11px; padding-left: 5px; text-transform: uppercase;">(<?php echo TABLE_VATTIN_EN; ?>)</span>  : 
                                            <span style="font-size:10px; padding-top: 8px;">
                                                <?php
                                                if ($salesOrder['Customer']['vat'] != "") {
                                                    $vatCustomerConvert = str_split($salesOrder['Customer']['vat']);
                                                    $countVarCustmoer = count($vatCustomerConvert);
                                                    for ($i = 0; $i < $countVarCustmoer; $i++) {
                                                        if ($i == 0) {
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px; margin-top:1px !mportant;'>" . $vatCustomerConvert[$i] . "</span>";
                                                        } else if ($i == 4) {
                                                            echo "<span> - </span>";
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px; margin-top:1px !mportant; margin-left: 2px;'>" . $vatCustomerConvert[$i] . "</span>";
                                                        } else {
                                                            echo "<span style='border: 1px solid #00afc1; padding: 1px 2px;  margin-top:1px !mportant; margin-left: 2px;'>" . $vatCustomerConvert[$i] . "</span>";
                                                        }
                                                    }
                                                }
                                                ?>  
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">ទូរស័ព្ទ (Phone)  :  <?php echo $salesOrder['Customer']['main_number']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">សំគាល់ (Memo)  :  <?php echo $salesOrder['SalesOrder']['memo']; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:1%;"></td>
                            <td style="border-width:3px;  width:49%; font-size: 12px; vertical-align: top;padding-left: 5px; padding-top: 0px;">
                                <table style="width: 100%; padding: 0;border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td colspan="2" style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            វិ​ក័​យ​ប័ត្រ <span style="font-size: 16px; font-weight: bold;">INVOICE</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខវិ​ក័​យ​ប័ត្រ​ Invoice Nº
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $salesOrder['SalesOrder']['so_code'];
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%;font-size: 12px; padding-top: 5px;">
                                            កាលបរិច្ចេទ Date
                                        </td>
                                        <td style="width:60%; font-size: 12px; vertical-align: top;">
                                            <div style="font-size: 12px; padding-top: 8px; vertical-align: top; margin-top: 3px;">
                                                <?php
                                                $created = explode(" ", $salesOrder['SalesOrder']['created']);
                                                echo dateShort($salesOrder['SalesOrder']['order_date'], "d/m/Y")." ".$created[1];
                                                ?>  
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខកុងត្រា Contract Nº
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខ អតប (VAT TIN)
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $salesOrder['Company']['vat_number'];
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            Due date
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">

                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            Created By
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php echo $salesOrder['User']['first_name']." ".$salesOrder['User']['last_name']; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <!-- <td style="border:none; vertical-align:top; padding:5px 5px 0px 0px; width:5%;">
                                <div id="qrCodeInvoiceSR" style="float:right;"></div>
                            </td> -->
                        </tr>
                    </table>
                    <div style="height: 5px"></div>
                    <table class="table_print" style="margin-top: 0px; border: none;">
                        <tr>
                            <th class="first titleHeaderTable" style="line-height: 15px; width: 5%;"><?php echo 'ល.រ'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'បាកូដ'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px; "><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;" colspan="2"><?php echo 'បរិមាណ '; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'ឯកតាគិត'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%; line-height: 15px; "><?php echo 'ថ្លៃឯកតា'; ?></th>
                            <!--<th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'បញ្ចុះតម្លៃ'; ?></th>-->
                            <th class="titleHeaderTable" style="width: 12%;line-height: 15px; "><?php echo 'ថ្លៃទំនិញ'; ?></th>
                        </tr>
                        <tr>
                            <th class="first titleHeaderTable" style="  border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; width: 5%;"><?php echo 'No.'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'BARCODE'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; "><?php echo 'NAME OF PRODUCT'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 8%; line-height: 15px; "><?php echo 'QTY'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 8%; line-height: 15px; "><?php echo 'F.O.C'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'UoM'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 12%; line-height: 15px; "><?php echo 'UNIT PRICE'; ?></th>
                            <!--<th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'DISCOUNT'; ?></th>-->
                            <th class="titleHeaderTable" style=" border-bottom-width:3px; border-bottom-style:double;    width: 12%;line-height: 15px; "><?php echo 'AMOUNT'; ?></th>
                        </tr>
                        <?php
                        $index = 0;
                        $productNameKh = '';
                        $totalDis = 0;
                        if (!empty($salesOrderDetails)) {
                            foreach ($salesOrderDetails as $salesOrderDetail) {
                                // Check Name With Customer
                                $conversion  = $salesOrderDetail['SalesOrderDetail']['conversion'];
                                $productCode = $salesOrderDetail['Product']['code'];
                                $productName = $salesOrderDetail['Product']['name']."/".$conversion;
                                $productNameKh = $salesOrderDetail['Product']['name_kh'];
                                $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$salesOrderDetail['Product']['id']." AND uom_id = ".$salesOrderDetail['SalesOrderDetail']['qty_uom_id']);
                                if(mysql_num_rows($sqlSku)){
                                    $rowSku = mysql_fetch_array($sqlSku);
                                    $productCode = $rowSku[0];
                                }
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>      
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $productCode; ?></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px; line-height: 15px;">
                                        <?php if ($productNameKh != '') {
                                            echo $productNameKh ."/".$conversion. '<br>';
                                        } ?><?php echo $productName; ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderDetail['SalesOrderDetail']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderDetail['SalesOrderDetail']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding:0px 0px 0px 0px;"><?php echo $salesOrderDetail['Uom']['name']; ?></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($salesOrderDetail['SalesOrderDetail']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                            $totalDis += $salesOrderDetail['SalesOrderDetail']['discount_amount'];
                                            echo number_format($salesOrderDetail['SalesOrderDetail']['discount_amount'], 2); 
                                        ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($salesOrderDetail['SalesOrderDetail']['total_price'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($salesOrderServices)) {
                            foreach ($salesOrderServices AS $salesOrderService) {
                        ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $salesOrderService['Service']['code']; ?></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                       
                                        <?php
                                        echo $salesOrderService['Service']['name'];
                                        if (trim($salesOrderService['SalesOrderService']['note']) != "") {
                                            echo '<span style="margin-left:10px; font-size:12px;">' . nl2br($salesOrderService['SalesOrderService']['note']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderService['SalesOrderService']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderService['SalesOrderService']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($salesOrderService['SalesOrderService']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                            $totalDis += $salesOrderService['SalesOrderService']['discount_amount'];
                                            echo number_format($salesOrderService['SalesOrderService']['discount_amount'], 2); 
                                        ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                <?php echo number_format($salesOrderService['SalesOrderService']['total_price'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($salesOrderMiscs)) {
                            foreach ($salesOrderMiscs AS $salesOrderMisc) {
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                        
                                        <?php
                                        echo $salesOrderMisc['SalesOrderMisc']['description'];
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderMisc['SalesOrderMisc']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($salesOrderMisc['SalesOrderMisc']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($salesOrderMisc['SalesOrderMisc']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                            $totalDis += $salesOrderMisc['SalesOrderMisc']['discount_amount'];
                                            echo number_format($salesOrderMisc['SalesOrderMisc']['discount_amount'], 2); 
                                        ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                <?php echo number_format($salesOrderMisc['SalesOrderMisc']['total_price'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        $i = 12;
                        $row = $i - $index;
                        for ($z = 1; $z < $row; $z++) {
                            ?>
                            <tr class="rowListDN">
                                <td style="text-align: center; height: 25px;font-size: 12px; border-bottom:1px solid !important;  padding-top:0px; padding-bottom:0px;border-bottom: none;"></td>                                  
                                <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="text-align: center; font-size: 12px;  height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>                                   
                                <td style="text-align: right; font-size: 12px; padding-top:0px ;height: 25px;  padding-bottom:0px;border-bottom: none; border:1px solid !important; display: none;"></td>
                                <td style="text-align: right; font-size: 12px; border-bottom:1px solid !important; padding-top:0px; height: 25px;  padding-bottom:0px;border-bottom: none;"> </td>
                            </tr>        
                            <?php
                            $i++;
                        }
                        $rowspan = 3;
                        if ($totalDis > 0 || $salesOrder['SalesOrder']['discount'] > 0) {
                            $rowspan = 4;
                        } else {
                            $rowspan = 3;
                        }
                        ?>   
                        <tr class="rowListDN">
                            <td style="text-align: center; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>                                  
                            <td style="font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="text-align: center; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>                                   
                            <td style="text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px; display: none;"></td>
                            <td style="text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"> </td>
                        </tr>   
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                សរុបតម្លៃមិនរួមបញ្ចូល អតប/Total Exclude VAT (USD):
                            </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($salesOrder['SalesOrder']['total_amount'] + $totalDis), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">បញ្ជុះតម្លៃ/Discount (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($totalDis + $salesOrder['SalesOrder']['discount']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">សរុបតម្លៃមិនទាន់ដកប្រាក់កក់/Grand Total Before Deposit (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($salesOrder['SalesOrder']['total_amount'] - $salesOrder['SalesOrder']['discount'] + $salesOrder['SalesOrder']['total_vat']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">ប្រាក់កក់/Deposit (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($salesOrder['SalesOrder']['total_deposit']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">សរុបតម្លៃមិនរួមទាំង​អតប/Grand Total Exclude VAT (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($salesOrder['SalesOrder']['total_amount'] + $salesOrder['SalesOrder']['total_vat'] - ($salesOrder['SalesOrder']['discount'] + $salesOrder['SalesOrder']['total_deposit'])), 2); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 50px; font-size: 12px; text-align: right;">
                    <table style="width: 100%;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="height: 130px; font-size: 10px; text-align: right;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 33%; vertical-align: bottom; text-align: center; height: 130px;">
                                            <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold; font-family: 'Calibri'">
                                                <span style='font-size: 12px; font-weight: bold;'>Prepared by</span> <br /> &nbsp;
                                            </div>
                                        </td>
                                        <td style="width: 34%; vertical-align: bottom; text-align: center;">
                                            <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold; font-family: 'Calibri'">
                                                <span style='font-size: 12px; font-weight: bold;'>Authorized Signature:</span> <br /> &nbsp;
                                            </div>
                                        </td>
                                        <td style="width: 33%; vertical-align: bottom; text-align: center;">
                                            <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold; font-family: 'Calibri'">
                                                <span style='font-size: 12px; font-weight: bold;'>Customer Approval</span> <br />
                                                (Name, Date & Signature)
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width:100%; text-align: left; margin-top: 10px; margin-bottom: 10px;">
                                    <tr>
                                        <td style="width:5%; font-size: 12px; font-weight: bold;">Note</td>
                                        <td style="width:1%; font-size: 12px; font-weight: bold;">:</td>
                                        <td style="width:89%; font-size: 12px; ">Invoice not Paid Acording to The Above Due Date Will Carry an Interest of 1.5% Per Month. If you any problem in the amount.</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
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
    $(document).ready(function () {
        $(document).dblclick(function () {
            window.close();
        });
        // $("#qrCodeInvoiceSR").qrcode({
        //     width: 80,
        //     height: 80,
        //     text: "<?php echo $salesOrder['SalesOrder']['so_code'];?>"
        // });
        $("#btnDisappearPrint").click(function () {
            $("#footerTablePrint").show();
            $("#footerTablePrint").css("width", "100%");
            //Default printing if jsPrintsetup is not available
            try{
                jsPrintSetup.print();
                window.close();
            }catch(err){
                //Default printing if jsPrintsetup is not available
                var title = "<?php echo $salesOrder['Customer']['name'].'_'.$salesOrder['SalesOrder']['so_code'].'('.$salesOrder['SalesOrder']['created'].')';?>";
                try {
                  // same-origin frame
                  document.title = window.parent.document.title = title;
                  print();
                } catch (e) { // cross-origin frame
                  // we could instead grab the current content of the page
                  // but for the demo, location.href will do
                  var p = window.open(location.href);
                  p.onload = function() {
                    // no more frame so we don't care ;-)
                    p.document.title = "<?php echo $salesOrder['Customer']['name'].'_'.$salesOrder['SalesOrder']['so_code'].'('.$salesOrder['SalesOrder']['created'].')';?>";
                    // a bit more hack to close the popup once printed...
                    function closePopup() {
                      p.close();
                    }
                    if ('onafterprint' in p) {
                      // FF and IE
                      p.onafterprint = closePopup
                    } else {
                      // webkit don't support onafterprint
                      var mediaQueryList = p.matchMedia('print');
                      mediaQueryList.addListener(mqlListener);

                      function mqlListener(mql) {
                        if (!mql.matches) {
                          closePopup();
                          mediaQueryList.removeListener(mqlListener);
                        }
                      }
                    }
                  }
                  // we're ready
                  p.print();
                  p.close();
                };
            }
        });
        $("#btnExportExcelSalesInvoice").click(function () {
            window.open("<?php echo $this->webroot; ?>public/report/sales_invoice_vat_<?php echo $user['User']['id']; ?>.csv", "_self");
        });
    });
</script>
