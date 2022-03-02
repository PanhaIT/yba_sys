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
    div.footerTablePrint {display: block; width: 100%; font-size: 12px; text-align: center;} 
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
    //debug($quotation);
    include("includes/function.php");
    $vatInvoice = '';
    if ($this->data['Quotation']['vat_percent'] > 0) {
        $vatInvoice = $this->data['Company']['vat_number'];
    }
    $display = "";
    $resultVAT = "VAT Included";
    if ($this->data['Quotation']['vat_percent'] <= 0) {
        $display = "display:none;";
        $resultVAT = "VAT Excluded";
    }
    ?>
    <table style="width:100%;">
        <tr style="">
            <td style="width:40%; vertical-align: top;"><img alt="" src="<?php echo $this->webroot;?>public/company_photo/<?php echo $this->data['Company']['photo'];?>" style="max-width: 280px;"/></td>
            <td style="border-width:3px;  width: 80%; vertical-align: top;">
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top; font-family: 'Moul'; font-size: 17px; line-height: 25px;">
                            <?php // echo $this->data['Branch']['name_other']; ?>អេស លីកហ្គ័រ<br />
                            <span style="font-weight: bold; font-size: 17px; font-weight: bold; font-family: cambria;">
                                <?php //echo $this->data['Branch']['name']; ?>S Liquor
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            អាសយដ្ឋាន: <?php echo $this->data['Branch']['address']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ទូរស័ព្ទលេខ (Mobile): (855) <?php echo $this->data['Branch']['telephone']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Email: <?php echo $this->data['Branch']['email_address']; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div style="clear: both;"></div></td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%; <?php if($this->data['Branch']['name_other'] == ''){?> margin-top: -40px; <?php }?>">
        <tbody>
            <tr>
                <td style="">
                    <table cellpadding="0" cellspacing="0" style="margin-top: 5px; height: 100px; width: 100%; vertical-align: top;">
                        <tr>
                            <td style="border-width:3px;  width:55%;font-size: 12px; padding-top: 9px; padding-bottom: 8px; vertical-align: top;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            អតិថិជន <span style="font-size: 16px; font-weight: bold;">Customer</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">ឈ្មោះក្រុមហ៊ុន : <?php echo $this->data['Customer']['name_kh']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Company Name : <?php echo $this->data['Customer']['name']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">អាសយដ្ឋាន  :  
                                                <?php 
                                                if($this->data['Customer']['type'] == 1){
                                                    if($this->data['Customer']['house_no'] != ''){
                                                        echo "លេខផ្ទះ: ".$this->data['Customer']['house_no'].", ";
                                                    }
                                                    if($this->data['Customer']['street_id'] != ''){
                                                        $sqlSt = mysql_query("SELECT * FROM streets WHERE id = ".$this->data['Customer']['street_id']);
                                                        $rowSt = mysql_fetch_array($sqlSt);
                                                        echo "ផ្លូវលេខ: ".$rowSt['name'].", ";
                                                    }
                                                    if($this->data['Customer']['province_id'] > 0){
                                                        $provinceId = $this->data['Customer']['province_id'];
                                                        $districtId = $this->data['Customer']['district_id']>0?$this->data['Customer']['district_id']:'0';
                                                        $communeId  = $this->data['Customer']['commune_id']>0?$this->data['Customer']['commune_id']:'0';
                                                        $villageId  = $this->data['Customer']['village_id']>0?$this->data['Customer']['village_id']:'0';
                                                        $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$this->data['Customer']['province_id']}");    
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
                                                    echo $this->data['Customer']['address']; 
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Address  :  
                                                <?php 
                                                if($this->data['Customer']['type'] == 1){
                                                    if($this->data['Customer']['house_no'] != ''){
                                                        echo TABLE_NO.": ".$this->data['Customer']['house_no'].", ";
                                                    }
                                                    if($this->data['Customer']['street_id'] != ''){
                                                        $sqlSt = mysql_query("SELECT * FROM streets WHERE id = ".$this->data['Customer']['street_id']);
                                                        $rowSt = mysql_fetch_array($sqlSt);
                                                        echo TABLE_STREET.": ".$rowSt['name'].", ";
                                                    }
                                                    if($this->data['Customer']['province_id'] > 0){
                                                        $provinceId = $this->data['Customer']['province_id'];
                                                        $districtId = $this->data['Customer']['district_id']>0?$this->data['Customer']['district_id']:'0';
                                                        $communeId  = $this->data['Customer']['commune_id']>0?$this->data['Customer']['commune_id']:'0';
                                                        $villageId  = $this->data['Customer']['village_id']>0?$this->data['Customer']['village_id']:'0';
                                                        $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$this->data['Customer']['province_id']}");    
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
                                                    echo $this->data['Customer']['address']; 
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 4px;">
                                            <span style="font-size:11px;"><?php echo TABLE_VATTIN_KH;?></span><span style="font-size:11px; padding-left: 5px; text-transform: uppercase;">(<?php echo TABLE_VATTIN_EN;?>)</span>  : 
                                            <span style="font-size:10px; padding-top: 8px;">
                                            <?php 
                                                if($this->data['Customer']['vat'] != ""){
                                                    $vatCustomerConvert = str_split($this->data['Customer']['vat']);
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
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">ទូរស័ព្ទ (Phone)  :  <?php echo $this->data['Customer']['main_number']; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:1%;"></td>
                            <td style="border-width:3px;  width:40%; font-size: 12px; vertical-align: top;padding-left: 5px; padding-top: 0px;">
                                <table style="width: 100%; padding: 0;border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td colspan="2" style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            សម្រង់តម្លៃ <span style="font-size: 16px; font-weight: bold;">QUOTATION</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខ ​​(QT)
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php 
                                                echo $this->data['Quotation']['quotation_code']; 
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
                                                $created = explode(" ", $this->data['Quotation']['created']);
                                                echo dateShort($this->data['Quotation']['quotation_date'], "d/m/Y")." ".$created[1];
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
                                                echo $this->data['Company']['vat_number']; 
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
                                            <div style="margin-top: -2px;  font-size: 12px;">
                                                <?php echo $this->data['User']['first_name']." ".$this->data['User']['last_name']; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div style="height: 5px"></div>
                    <table class="table_print" style="margin-top: 0px; border: none;">
                        <tr>
                            <th class="first titleHeaderTable" style="line-height: 15px; width: 5%;"><?php echo 'ល.រ'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'បាកូដ'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;"><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;"><?php echo 'បរិមាណ'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;"><?php echo 'បរិមាណ'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'ឯកតាគិត'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%; line-height: 15px; "><?php echo 'ថ្លៃឯកតា'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; display: none;"><?php echo 'បញ្ចុះតម្លៃ'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%;line-height: 15px; "><?php echo 'ថ្លៃទំនិញ'; ?></th>
                        </tr>
                        <tr>
                            <th class="first titleHeaderTable" style="  border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; width: 5%;"><?php echo 'No.'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'BARCODE'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; "><?php echo 'NAME OF PRODUCT'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 12%; line-height: 15px; "><?php echo 'QTY'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 12%; line-height: 15px; "><?php echo 'FOC'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'UoM'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 12%; line-height: 15px; "><?php echo 'UNIT PRICE'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; display: none;"><?php echo 'DISCOUNT'; ?></th>
                            <th class="titleHeaderTable" style=" border-bottom-width:3px; border-bottom-style:double;    width: 12%;line-height: 15px; "><?php echo 'AMOUNT'; ?></th>
                        </tr>
                        <?php
                        $index = 0;
                        $productNameKh = '';   
                        $totalDis = 0;
                        if (!empty($quotationDetails)) {
                            foreach ($quotationDetails as $quotationDetail) {
                                // Check Name With Customer
                                $productName = $quotationDetail['Product']['name'];
                                $productNameKh = $quotationDetail['Product']['name_kh'];
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>      
                                   <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $quotationDetail['Product']['barcode'];?></td>
                                   <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px; line-height: 15px;">
                                        <?php if($productNameKh != ''){echo $productNameKh.'<br>';}?><?php echo $productName;?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($quotationDetail['QuotationDetail']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($quotationDetail['QuotationDetail']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding:0px 0px 0px 0px;"><?php echo $quotationDetail['Uom']['name']; ?></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($quotationDetail['QuotationDetail']['unit_price'], 2);?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                        $totalDis += $quotationDetail['QuotationDetail']['discount_amount'];
                                        echo number_format($quotationDetail['QuotationDetail']['discount_amount'], 2);?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($quotationDetail['QuotationDetail']['total_price'] - $quotationDetail['QuotationDetail']['discount_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($quotationServices)) {
                            foreach ($quotationServices AS $quotationService) {
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                       
                                        <?php
                                        echo $quotationService['Service']['name'];
                                        if (trim($quotationService['QuotationService']['note']) != "") {
                                            echo '<span style="margin-left:10px; font-size:12px;">' . nl2br($quotationService['QuotationService']['note']) . '</span>';
                                        }                                       
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($quotationService['QuotationService']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($quotationService['QuotationService']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;"></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($quotationService['QuotationService']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                        $totalDis += $quotationService['QuotationService']['discount_amount'];
                                        echo number_format($quotationService['QuotationService']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($quotationService['QuotationService']['total_price'] - $quotationService['QuotationService']['discount_amount'], 2); ?>
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
                            <td style="text-align: center; height: 25px;font-size: 12px; border-bottom:1px solid !important;  padding-top:0px; padding-bottom:0px;border-bottom: none;"></td>                                  
                            <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                            <td style="font-size: 12px; height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                            <td style="text-align: center; font-size: 12px;  height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>     
                            <td style="text-align: center; font-size: 12px;  height: 25px;  padding-top:0px; padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>                                  
                            <td style="text-align: right; font-size: 12px; padding-top:0px ;height: 25px;  padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                            <td style="text-align: right; font-size: 12px; padding-top:0px ;height: 25px;  padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                            <td style="text-align: right; font-size: 12px; padding-top:0px ;height: 25px;  padding-bottom:0px;border-bottom: none; border:1px solid !important; display: none;"></td>
                            <td style="text-align: right; font-size: 12px; border-bottom:1px solid !important; padding-top:0px; height: 25px;  padding-bottom:0px;border-bottom: none;"> </td>
                        </tr>        
                        <?php $i++;}
                        $rowspan = 3;
                        if($totalDis > 0 || $this->data['Quotation']['discount'] > 0){
                            $rowspan = 4;
                        }else{
                            $rowspan = 3;
                        }
                        ?>   
                        <tr class="rowListDN">
                            <td style="   text-align: center; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>                                  
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" text-align: center; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>        
                            <td style=" text-align: center; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>                                  
                            <td style=" text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px; display: none;"></td>
                            <td style="   text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"> </td>
                        </tr>   
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                សរុបតម្លៃមិនរួមបញ្ចូល អតប/Total Exclude VAT (USD):
                            </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Quotation']['total_amount']), 2);?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">បញ្ជុះតម្លៃ/Discount (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format($this->data['Quotation']['discount'], 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">សរុបតម្លៃមិនទាន់ដកប្រាក់កក់/Grand Total Before Deposit (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Quotation']['total_amount'] - $this->data['Quotation']['discount'] + $this->data['Quotation']['total_vat']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">ប្រាក់កក់/Deposit (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($this->data['Quotation']['total_deposit']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">សរុបតម្លៃមិនរួមទាំង​អតប/Grand Total Exclude VAT (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Quotation']['total_amount'] - ($this->data['Quotation']['discount'] + $this->data['Quotation']['total_deposit']) + $this->data['Quotation']['total_vat']), 2); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 40px;">
                    
                </td>
            </tr>
        </tfoot>
    </table>
    <div id="footerTablePrint">
        <table style="width: 100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="height: 130px; font-size: 10px; text-align: right;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 49%; vertical-align: bottom; text-align: center; height: 130px;">
                                <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold; font-family: 'Calibri'">
                                    <span style='font-size: 12px; font-weight: bold;'>Prepared by</span> <br /> &nbsp;
                                </div>
                            </td>
                            <td style="width: 49%; vertical-align: bottom; text-align: center;">
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
                            <td style="width:89%; font-size: 12px; ">Invoice not Paid According to The Above Due Date Will Carry an Interest of 1.5% Per Month. If you any problem in the amount.</td>
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
<script type="text/javascript">
    $(document).ready(function() {
        $(document).dblclick(function() {
            window.close();
        });
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
