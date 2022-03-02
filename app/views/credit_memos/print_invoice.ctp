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
    //debug($creditMemo);
    include("includes/function.php");
    $vatInvoice = '';
    if ($creditMemo['CreditMemo']['vat_percent'] > 0) {
        $vatInvoice = $creditMemo['Company']['vat_number'];
    }
    $display = "";
    $resultVAT = "VAT Included";
    if ($creditMemo['CreditMemo']['vat_percent'] <= 0) {
        $display = "display:none;";
        $resultVAT = "VAT Excluded";
    }
    ?>
    <table style="width:100%;">
        <tr style="">
            <td style="width:40%; vertical-align: top;"><img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $creditMemo['Company']['photo']; ?>" style="max-width: 280px;"/></td>
            <td style="border-width:3px;  width: 80%; vertical-align: top;">
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top; font-family: 'Moul'; font-size: 17px; line-height: 25px;">
                            <?php // echo $creditMemo['Branch']['name_other']; ?>អេស លីកហ្គ័រ<br />
                            <span style="font-weight: bold; font-size: 17px; font-weight: bold; font-family: cambria;">
                                <?php //echo $creditMemo['Branch']['name']; ?>S Liquor
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            អាសយដ្ឋាន: <?php echo $creditMemo['Branch']['address']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ទូរស័ព្ទលេខ (Mobile): (855) <?php echo $creditMemo['Branch']['telephone']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Email: <?php echo $creditMemo['Branch']['email_address']; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div style="clear: both;"></div></td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" style="width: 100%; <?php if ($creditMemo['Branch']['name_other'] == '') { ?> margin-top: -40px; <?php } ?>">
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
                                            <span style="font-size:11px;">ឈ្មោះក្រុមហ៊ុន : <?php echo $creditMemo['Customer']['name_kh']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Company Name : <?php echo $creditMemo['Customer']['name']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">អាសយដ្ឋាន  :  <?php echo $creditMemo['Customer']['address_other']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">Address  :  <?php echo $creditMemo['Customer']['address']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:100%; font-size: 12px; padding-top: 4px;">
                                            <span style="font-size:11px;"><?php echo TABLE_VATTIN_KH; ?></span><span style="font-size:11px; padding-left: 5px; text-transform: uppercase;">(<?php echo TABLE_VATTIN_EN; ?>)</span>  : 
                                            <span style="font-size:10px; padding-top: 8px;">
                                                <?php
                                                if ($creditMemo['Customer']['vat'] != "") {
                                                    $vatCustomerConvert = str_split($creditMemo['Customer']['vat']);
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
                                            <span style="font-size:11px;">ទូរស័ព្ទ (Phone)  :  <?php echo $creditMemo['Customer']['main_number']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px; padding-top: 5px;">
                                            <span style="font-size:11px;">សំគាល់ (Memo)  :  <?php echo $creditMemo['CreditMemo']['note']; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:1%;"></td>
                            <td style="border-width:3px;  width:40%; font-size: 12px; vertical-align: top;padding-left: 5px; padding-top: 0px;">
                                <table style="width: 100%; padding: 0;border:none; line-height: 15px; vertical-align: top;">
                                    <tr>
                                        <td colspan="2" style="font-family: 'Moul'; font-size: 14px; font-weight: bold; width: 38%;">
                                            ប័ណ្ណឥណទាន <span style="font-size: 16px; font-weight: bold;">Credit Note</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខប័ណ្ណឥណទាន ​​(CN) :
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $creditMemo['CreditMemo']['cm_code'];
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%;font-size: 12px; padding-top: 5px;">
                                            លេខវិ​ក័​យ​ប័ត្រ  Invoice Nº :
                                        </td>
                                        <td style="width:60%; font-size: 12px; vertical-align: top;">
                                            <div style="font-size: 12px; padding-top: 8px; vertical-align: top; margin-top: 3px;">
                                                <?php
                                                echo $creditMemo['CreditMemo']['invoice_code'];
                                                ?>  
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%;font-size: 12px; padding-top: 5px;">
                                            កាលបរិច្ចេទ Date :
                                        </td>
                                        <td style="width:60%; font-size: 12px; vertical-align: top;">
                                            <div style="font-size: 12px; padding-top: 8px; vertical-align: top; margin-top: 3px;">
                                                <?php
                                                $created = explode(" ", $creditMemo['CreditMemo']['created']);
                                                echo dateShort($creditMemo['CreditMemo']['order_date'], "d/m/Y")." ".$created[1];
                                                ?>  
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខកុងត្រា Contract Nº :
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            លេខ អតប (VAT TIN) :
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $creditMemo['Company']['vat_number'];
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            Due date :
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">

                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:40%; font-size: 12px;">
                                            Created By :
                                        </td>
                                        <td style="width: 60%; vertical-align: top; padding-top: 5px;">
                                            <div style="margin-top: -2px;  font-size: 12px; font-weight: bold;">
                                                <?php
                                                echo $creditMemo['User']['first_name']." ".$creditMemo['User']['last_name'];
                                                ?>
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
                            <th class="titleHeaderTable" style="line-height: 15px; "><?php echo 'ឈ្មោះផលិតផល'; ?></th>
                            <th class="titleHeaderTable" style="line-height: 15px;" colspan="2"><?php echo 'បរិមាណ '; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'ឯកតាគិត'; ?></th>
                            <th class="titleHeaderTable" style="width: 12%; line-height: 15px; "><?php echo 'ថ្លៃឯកតា'; ?></th>
                            <th class="titleHeaderTable" style="width: 10%; line-height: 15px; "><?php echo 'បញ្ចុះតម្លៃ'; ?></th>
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
                            <th class="titleHeaderTable" style="border-bottom-width:3px; border-bottom-style:double;  width: 10%; line-height: 15px; "><?php echo 'DISCOUNT'; ?></th>
                            <th class="titleHeaderTable" style=" border-bottom-width:3px; border-bottom-style:double;    width: 12%;line-height: 15px; "><?php echo 'AMOUNT'; ?></th>
                        </tr>
                        <?php
                        $index = 0;
                        $productNameKh = '';
                        if (!empty($creditMemoDetails)) {
                            foreach ($creditMemoDetails as $creditMemoDetail) {
                                // Check Name With Customer
                                $conversion  = $creditMemoDetail['CreditMemoDetail']['conversion'];
                                $productCode = $creditMemoDetail['Product']['code'];
                                $productName = $creditMemoDetail['Product']['name']."/".$conversion;
                                $productNameKh = $creditMemoDetail['Product']['name_kh'];
                                $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = " . $creditMemoDetail['Product']['id'] . " AND uom_id = " . $creditMemoDetail['CreditMemoDetail']['qty_uom_id']);
                                if (mysql_num_rows($sqlSku)) {
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
                                        echo number_format($creditMemoDetail['CreditMemoDetail']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($creditMemoDetail['CreditMemoDetail']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding:0px 0px 0px 0px;"><?php echo $creditMemoDetail['Uom']['name']; ?></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($creditMemoDetail['CreditMemoDetail']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($creditMemoDetail['CreditMemoDetail']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                        <?php echo number_format($creditMemoDetail['CreditMemoDetail']['total_price'] - $creditMemoDetail['CreditMemoDetail']['discount_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($creditMemoServices)) {
                            foreach ($creditMemoServices AS $creditMemoService) {
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;"><?php echo $creditMemoService['Service']['code']; ?></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                       
                                        <?php
                                        echo $creditMemoService['Service']['name'];
                                        if (trim($creditMemoService['CreditMemoService']['note']) != "") {
                                            echo '<span style="margin-left:10px; font-size:12px;">' . nl2br($creditMemoService['CreditMemoService']['note']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($creditMemoService['CreditMemoService']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($creditMemoService['CreditMemoService']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($creditMemoService['CreditMemoService']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($creditMemoService['CreditMemoService']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                <?php echo number_format($creditMemoService['CreditMemoService']['total_price'] - $creditMemoService['CreditMemoService']['discount_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($creditMemoMiscs)) {
                            foreach ($creditMemoMiscs AS $creditMemoMisc) {
                                ?>
                                <tr class="rowListDN">
                                    <td style="  text-align: center; font-size: 12px; height: 25px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php echo ++$index; ?>
                                    </td>
                                    <td></td>
                                    <td style="font-size: 12px; padding-top: 0px; padding-bottom: 0px;">                                        
                                        <?php
                                        echo $creditMemoMisc['CreditMemoMisc']['description'];
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($creditMemoMisc['CreditMemoMisc']['qty'], 0);
                                        ?>
                                    </td>
                                    <td style="text-align: center; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <?php
                                        echo number_format($creditMemoMisc['CreditMemoMisc']['qty_free'], 0);
                                        ?>
                                    </td>
                                    <td></td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($creditMemoMisc['CreditMemoMisc']['unit_price'], 2); ?>
                                    </td>
                                    <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span><?php echo number_format($creditMemoMisc['CreditMemoMisc']['discount_amount'], 2); ?>
                                    </td>
                                    <td style="  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                        <span style="float: left; width: 12px; font-size: 12px;">$</span>
                                <?php echo number_format($creditMemoMisc['CreditMemoMisc']['total_price'] - $creditMemoMisc['CreditMemoMisc']['discount_amount'], 2); ?>
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
                                <td style="text-align: right; font-size: 12px; padding-top:0px ;height: 25px;  padding-bottom:0px;border-bottom: none; border:1px solid !important; "></td>
                                <td style="text-align: right; font-size: 12px; border-bottom:1px solid !important; padding-top:0px; height: 25px;  padding-bottom:0px;border-bottom: none;"> </td>
                            </tr>        
                            <?php
                            $i++;
                        }
                        $rowspan = 3;
                        if ($creditMemo['CreditMemo']['discount'] > 0) {
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
                            <td style=" text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"></td>
                            <td style="   text-align: right; font-size: 12px;padding-top: 0px; padding-bottom: 0px;"> </td>
                        </tr>   
                        <tr>
                            <td colspan="8" style=" line-height: 18px;  text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px;">
                                សរុបតម្លៃមិនរួមបញ្ចូល អតប/Total Exclude VAT (USD):
                            </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($creditMemo['CreditMemo']['total_amount']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">បញ្ជុះតម្លៃ/Discount (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span><?php echo number_format(($creditMemo['CreditMemo']['discount']), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style=" line-height: 18px;  text-align: right; font-size: 12px;height: 15px; padding-top: 0px; padding-bottom: 0px;">ប្រាក់កក់/Deposit (USD):</td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px;font-weight: bold;">$</span>0.00
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="  line-height: 18px;  text-align: right; font-size: 12px; height: 15px; padding-top: 4px; padding-bottom: 0px;">សរុបតម្លៃមិនរួមទាំង​អតប/Grand Total Exclude VAT (USD): </td>
                            <td style="text-align: right; font-size: 12px; padding-top: 0px; padding-bottom: 0px; font-weight: bold;">
                                <span style="float: left; width: 12px; font-size: 12px; font-weight: bold;">$</span><?php echo number_format(($creditMemo['CreditMemo']['total_amount'] - $creditMemo['CreditMemo']['discount'] + $creditMemo['CreditMemo']['total_vat']), 2); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="height: 50px; font-size: 12px; text-align: right;">
                    <div style=" margin-top: 20px;"  id="footerTablePrint">
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
                                                <div style=" margin: 0px auto; width: 70%; border-top: 1px solid #000; text-align: center; font-size: 10px; font-weight: bold;">
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
