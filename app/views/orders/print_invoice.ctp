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
    //debug($this->data);
    include("includes/function.php");
    $vatInvoice = '';
    if ($this->data['Order']['vat_percent'] > 0) {
        $vatInvoice = $this->data['Company']['vat_number'];
    }
    $display = "";
    $resultVAT = "VAT Included";
    if ($this->data['Order']['vat_percent'] <= 0) {
        $display = "display:none;";
        $resultVAT = "VAT Excluded";
    }
    ?>
    <table style="width:100%;">
        <tr style="">
            <td style="width:40%; vertical-align: top;"><img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $this->data['Company']['photo']; ?>" style="max-width: 280px;"/></td>
            <td style="border-width:3px;  width: 80%; vertical-align: top;">
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top; font-family: 'Moul'; font-size: 17px; line-height: 25px;">
                            <?php // echo $this->data['Branch']['name_other']; ?>????????? ????????????????????????<br />
                            <span style="font-weight: bold; font-size: 17px; font-weight: bold; font-family: cambria;">
                                <?php //echo $this->data['Branch']['name']; ?>S Liquor
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ???????????????????????????: <?php echo $this->data['Branch']['address']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ????????????????????????????????? (Mobile): (855) <?php echo $this->data['Branch']['telephone']; ?>
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
    <table cellpadding="0" cellspacing="0" style="width: 100%; <?php if ($this->data['Branch']['name_other'] == '') { ?> margin-top: -40px; <?php } ?>">
        <tbody>
            <tr>
                <td style="">
                    <table cellpadding="0" cellspacing="0" style="margin-top: 5px; height: 100px; width: 100%; vertical-align: top;">
                        <tr>
                            <td style="border-width:3px;  width:55%;font-size: 12px; padding-top: 9px; padding-bottom: 8px; vertical-align: top;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            ????????????????????? <span style="font-size: 16px; font-weight: bold;">Customer</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">?????????????????????????????????????????? : <?php echo $this->data['Customer']['name_kh']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Company Name : <?php echo $this->data['Customer']['name']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">???????????????????????????  :  
                                                <?php 
                                                if($this->data['Customer']['type'] == 1){
                                                    if($this->data['Customer']['house_no'] != ''){
                                                        echo "?????????????????????: ".$this->data['Customer']['house_no'].", ";
                                                    }
                                                    if($this->data['Customer']['street_id'] != ''){
                                                        $sqlSt = mysql_query("SELECT * FROM streets WHERE id = ".$this->data['Customer']['street_id']);
                                                        $rowSt = mysql_fetch_array($sqlSt);
                                                        echo "????????????????????????: ".$rowSt['name'].", ";
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
                                                        echo "????????????: ".$rowAddress['v_name'].", ";
                                                    }
                                                    if($rowAddress['c_name'] != ''){
                                                        echo "?????????/?????????????????????: ".$rowAddress['c_name'].", ";
                                                    }
                                                    if($rowAddress['d_name'] != ''){
                                                        echo "???????????????/???????????????: ".$rowAddress['d_name'].", ";
                                                    }
                                                    if($rowAddress['p_name'] != ''){
                                                        echo "???????????????/???????????????: ".$rowAddress['p_name'].", ";
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
                                            <span style="font-size:11px;"><?php echo TABLE_VATTIN_KH; ?></span><span style="font-size:11px; padding-left: 5px; text-transform: uppercase;">(<?php echo TABLE_VATTIN_EN; ?>)</span>  : 
                                            <span style="font-size:10px; padding-top: 8px;">
                                                <?php
                                                if ($this->data['Customer']['vat'] != "") {
                                                    $vatCustomerConvert = str_split($this->data['Customer']['vat']);
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
                                            <span style="font-size:11px;">???????????????????????? (Phone)  :  <?php echo $this->data['Customer']['main_number']; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:1%;"></td>
                            <td style="border-width:3px;  width:40%; font-size: 12px; vertical-align: top;padding-left: 5px; padding-top: 0px;">
                                <table style="width: 100%; padding: 0;border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td colspan="2" style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            ??????????????????????????????????????? <span style="font-size: 16px; font-weight: bold;">SALE ORDER</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            ??????????????????????????????????????????????????? Invoice N??
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $this->data['Order']['order_code'];
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%;font-size: 12px; padding-top: 5px;">
                                            ????????????????????????????????? Date
                                        </td>
                                        <td style="width:60%; font-size: 12px; vertical-align: top;">
                                            <div style="font-size: 12px; padding-top: 8px; vertical-align: top; margin-top: 3px;">
                                                <?php
                                                $created = explode(" ", $this->data['Order']['created']);
                                                echo dateShort($this->data['Order']['order_date'], "d/m/Y")." ".$created[1];
                                                ?>  
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            ?????????????????????????????? Contract N??
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            ????????? ????????? (VAT TIN)
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
                            <th class="first titleHeaderTable" style="line-height: 15px; width: 5%;"><?php echo '???.???'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo '???????????????'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px; "><?php echo '?????????????????????????????????'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;" colspan="2"><?php echo '?????????????????? '; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo '?????????????????????'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%; line-height: 15px; "><?php echo '????????????????????????'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; display: none;"><?php echo '?????????????????????????????????'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%;line-height: 15px; "><?php echo '???????????????????????????'; ?></th>
                        </tr>
                        <tr>
                            <th class="first titleHeaderTable" style="  border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; width: 5%;"><?php echo 'No.'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'BARCODE'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  line-height: 15px; "><?php echo 'NAME OF PRODUCT'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 8%; line-height: 15px; "><?php echo 'QTY'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 8%; line-height: 15px; "><?php echo 'F.O.C'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'UoM'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 12%; line-height: 15px; "><?php echo 'UNIT PRICE'; ?></th>
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; display: none;"><?php echo 'DISCOUNT'; ?></th>
                            <th class="titleHeaderTable" style=" border-bottom-width:3px; border-bottom-style:double;    width: 12%;line-height: 15px; "><?php echo 'AMOUNT'; ?></th>
                        </tr>
                        <?php
                        $index = 0;
                        $productNameKh = '';
                        $totalDis = 0;
                        if (!empty($orderDetails)) {
                            foreach ($orderDetails as $orderDetail) {
                                // Check Name With Customer
                                $productCode = $orderDetail['Product']['code'];
                                $productName = $orderDetail['Product']['name'];
                                $productNameKh = $orderDetail['Product']['name_kh'];
                                $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$orderDetail['Product']['id']." AND uom_id = ".$orderDetail['OrderDetail']['qty_uom_id']);
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
                                            echo $productNameKh . '<br>';
                                        } ?><?php echo $productName; ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($orderDetail['OrderDetail']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($orderDetail['OrderDetail']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding:0px 0px 0px 0px;"><?php echo $orderDetail['Uom']['name']; ?></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($orderDetail['OrderDetail']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                        $totalDis += $orderDetail['OrderDetail']['discount_amount'];
                                        echo number_format($orderDetail['OrderDetail']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($orderDetail['OrderDetail']['total_price'] - $orderDetail['OrderDetail']['discount_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($orderServices)) {
                            foreach ($orderServices AS $orderService) {
                        ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $orderService['Service']['code']; ?></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                       
                                        <?php
                                        echo $orderService['Service']['name'];
                                        if (trim($orderService['OrderService']['note']) != "") {
                                            echo '<span style="margin-left:10px; font-size:12px;">' . nl2br($orderService['OrderService']['note']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($orderService['OrderService']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($orderService['OrderService']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($orderService['OrderService']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; display: none;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php 
                                        $totalDis += $orderService['OrderService']['discount_amount'];
                                        echo number_format($orderService['OrderService']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($orderService['OrderService']['total_price'] - $orderService['OrderService']['discount_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        $i = 14;
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
                        if ($totalDis > 0 || $this->data['Order']['discount'] > 0) {
                            $rowspan = 4;
                        } else {
                            $rowspan = 3;
                        }
                        ?>   
                        <tr class="rowListDN">
                            <td style="   text-align: center; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>                                  
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" font-size: 12px; height: 25px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style=" text-align: center; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>                                   
                            <td style=" text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px; display: none;"></td>
                            <td style="   text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"> </td>
                        </tr>   
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                ??????????????????????????????????????????????????????????????? ?????????/Total Exclude VAT (USD):
                            </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Order']['total_amount']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">?????????????????????????????????/Discount (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($this->data['Order']['discount']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">?????????????????????????????????????????????????????????????????????????????????/Grand Total Before Deposit (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Order']['total_amount'] - $this->data['Order']['discount'] + $this->data['Order']['total_vat']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">???????????????????????????/Deposit (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($this->data['Order']['total_deposit']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">?????????????????????????????????????????????????????????????????????/Grand Total Exclude VAT (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($this->data['Order']['total_amount'] - ($this->data['Order']['discount'] + $this->data['Order']['total_deposit']) + $this->data['Order']['total_vat']), 2); ?>
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
                <td style="height: 120px; font-size: 10px; text-align: right;">
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
    $(document).ready(function () {
        $(document).dblclick(function () {
            window.close();
        });
        $("#btnDisappearPrint").click(function () {
            $("#footerTablePrint").show();
            $("#footerTablePrint").css("width", "100%");
            //Default printing if jsPrintsetup is not available
            window.print();
            window.close();
        });
        $("#btnExportExcelSalesInvoice").click(function () {
            window.open("<?php echo $this->webroot; ?>public/report/sales_invoice_vat_<?php echo $user['User']['id']; ?>.csv", "_self");
        });
    });
</script>
