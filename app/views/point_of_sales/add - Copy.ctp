<?php 
$sqlCheckConfig = mysql_query("SELECT id FROM test");
if (!mysql_num_rows($sqlCheckConfig)) {
    echo 'path.php not config properly... Please recheck it again. <a href="">Refresh</a>';
    exit();
}
$allowLots    = false;
$allowExpired = false;
$priceDecimal = 2;
$allowShift   = 0;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40, 53) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 53){
        if($rowSetting['is_checked'] == 1){
            $allowShift = 1;
        }
    } else if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    }
}
echo $this->element('prevent_multiple_submit');
// Authentication
$this->element('check_access');
$allowAddProduct = checkAccess($user['User']['id'], 'point_of_sales', 'quickAddProduct');
$allowService    = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowChangeDate = checkAccess($user['User']['id'], $this->params['controller'], 'changeDate');
$allowInvoiceDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'changeDiscount');
$allowProductDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'productDiscount');
$allowAddNewCustomer  = checkAccess($user['User']['id'], 'point_of_sales', 'quickAddCustomer');
$allowEditPrice = checkAccess($user['User']['id'], 'point_of_sales', 'editPrice');
// Setting
$queryClosingDate = mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate = mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
$itemDisPermission = 0;
$invDisPermission  = 0;
if($allowProductDiscount == true){
    $itemDisPermission = 1;
}
if($allowInvoiceDiscount == true){
    $invDisPermission = 1;
}

?>
<style type="text/css">
    .ui-widget-header{
        background: #4d68a0;
        color: #ffffff;
    }
    .table th.first{
        border-left: 1px solid #bbbbbb;
    }
    .table th{
        background: #4d68a0;
        color: #ffffff;
        border-bottom: 1px solid #bbbbbb;
        border-right: 1px solid #bbbbbb;
        border-top: 1px solid #bbbbbb;
    }
    .table td{
        border-bottom: 1px solid #bbbbbb;
    }
    .table td.first{
        border-left: 1px solid #bbbbbb;
    }
    .table td.first{
        border-right: 1px solid #bbbbbb;
    }
    .table td{
        border-right: 1px solid #bbbbbb;
    }
    .paging_full_numbers span.paginate_active{
        background-color: #4d68a0;
        color: #ffffff;
    }
    
    /* The container <div> - needed to position the dropdown content */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    /* Dropdown Content (Hidden by Default) */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        margin-left: -47px;
        border: 1px solid #4d68a0;
    }

    /* Links inside the dropdown */
    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    /* Change color of dropdown links on hover */
    .dropdown-content a:hover {background-color: #f1f1f1}
    
    #bodyList{
        position: relative;
        background: #FFF;
        overflow: hidden;
    }
    
    #bodyList #backgroundLogo{
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -110px; 
        margin-top: -40px;
        width: 210px;
        height: 80px;
        height: auto;
        z-index: 1;
        opacity: 0.1;
    }
    
    #tblPOS{
        position: relative;
        z-index: 2;
    }
    
</style>
<script type="text/javascript">
    var itemDisPermission = '<?php echo $itemDisPermission; ?>';
    var invDisPermission  = '<?php echo $invDisPermission; ?>';
    var tabId = '<?php echo $tab; ?>';
            
    $(document).ready(function(){   
        // Javascript
        // Active
        window.addEventListener('focus', setTabPOSSelect);
        // Inactive
        window.addEventListener('blur', clearTabPOSSelect);
        // Action
        setTabPOSSelect();
        <?php
        if($tab != '0'){
        ?>
        var pageTitle = document.title;
        $(document).attr("title", "(<?php echo $tab; ?>) "+pageTitle);
        <?php
        }
        ?>
        $(".btnLogOut").mouseover(function () {
            $(this).find('img').css("width", "28px");
            $(this).find('div').css("font-weight", "Bold");
        });
        $(".btnLogOut").mouseout(function () {
            $(this).find('img').css("width", "26px");
            $(this).find('div').css("font-weight", "normal");
        });

        $(".btnLanguage").mouseover(function () {
            $(this).find('img').css("width", "28px");
            $(this).find('div').css("font-weight", "Bold");
        });
        $(".btnLanguage").mouseout(function () {
            $(this).find('img').css("width", "26px");
            $(this).find('div').css("font-weight", "normal");
        });

        $(".btnChangeShift").mouseover(function () {
            $(this).find('img').css("width", "28px");
            $(this).find('div').css("font-weight", "Bold");
        });
        $(".btnChangeShift").mouseout(function () {
            $(this).find('img').css("width", "26px");
            $(this).find('div').css("font-weight", "normal");
        });

        $(".btnSearchCustomer").mouseover(function () {
            $(this).find('img').css("width", "31px");
        });
        $(".btnSearchCustomer").mouseout(function () {
            $(this).find('img').css("width", "30px");
        });
        
        $(".ActScanCode, .ActNewTab").mouseover(function () {
            $(this).find('img').css("width", "31px");
            $(this).find("td:eq(1)").css("font-size", "16px");
        });
        
        $(".ActScanCode, .ActNewTab").mouseout(function () {
            $(this).find('img').css("width", "30px");
            $(this).find("td:eq(1)").css("font-size", "14px");
        });

        $(".btnReprintReceiptImg, .btnReprintReceiptLabel").mouseover(function () {
            $(".btnReprintReceiptImg").find('img').css("width", "33px");
            $(".btnReprintReceiptLabel").css('font-size', '15px');
        });
        $(".btnReprintReceiptImg, .btnReprintReceiptLabel").mouseout(function () {
            $(".btnReprintReceiptImg").find('img').css("width", "30px");
            $(".btnReprintReceiptLabel").css('font-size', '14px');
        });

        $(".btnReprintSalesHistoryImg, .btnReprintSalesHistoryLabel").mouseover(function () {
            $(".btnReprintSalesHistoryImg").find('img').css("width", "33px");
            $(".btnReprintSalesHistoryLabel").css('font-size', '15px');
        });
        $(".btnReprintSalesHistoryImg, .btnReprintSalesHistoryLabel").mouseout(function () {
            $(".btnReprintSalesHistoryImg").find('img').css("width", "30px");
            $(".btnReprintSalesHistoryLabel").css('font-size', '14px');
        });

        $("#paidShow").mouseover(function () {
            $(this).css('font-size', '22px');
        });
        $("#paidShow").mouseout(function () {
            $(this).css('font-size', '20px');
        });

        $(".addNewProduct").mouseover(function () {
            $(this).css('font-size', '17px');
        });
        $(".addNewProduct").mouseout(function () {
            $(this).css('font-size', '16px');
        });

        $(".clearOrderList").mouseover(function () {
            $(this).css('font-size', '17px');
        });
        $(".clearOrderList").mouseout(function () {
            $(this).css('font-size', '16px');
        });

        $(".btnPOSPay").mouseover(function () {
            $(this).css('color', '##4d68a0');
        });
        
        $(".btnPOSPay").mouseout(function () {
            $(this).css('color', '#FFF');
        });
        
        $(".dropdown").click(function(){
            keyEventBtnLanguage();
        });
        $("#changeLangEn").click(function(){
            clearCookie = true;
            window.open('<?php echo $this->base; ?>/users/lang/en', '_self');
        });
        $("#changeLangKh").click(function(){
            clearCookie = true;
            window.open('<?php echo $this->base; ?>/users/lang/kh', '_self');
        });
        $(document).click(function (e) {
            e.stopPropagation();
            var container = $(".dropdown");

            //check if the clicked area is dropDown or not
            if (container.has(e.target).length === 0) {
                $(".dropdown-content").hide();
                $(".dropdown-content").removeAttr("style");
                $(".dropdown-content").css("style", "font-weight: normal");
            }
        });
    });
    
    function setTabPOSSelect() {
        localStorage.setItem("tabPOSSelected", tabId);
        addItemDisplay('0');
        calculateTotalQtyDisplay();
    }

    function clearTabPOSSelect() {
        localStorage.setItem("tabPOSSelected", '');
    }
    
    function keyEventBtnLanguage(){
        $(".dropdown-content").unbind("click").unbind("keyup").unbind("keypress").unbind("change").unbind("blur").unbind("focus");
        var dropDown = $(".dropdown-content").attr("style").length;
        if(dropDown == 18){
            $(".dropdown-content").css("display", "block");
        }else{
            $(".dropdown-content").hide();
            $(".dropdown-content").removeAttr("style");
            $(".dropdown-content").css("style", "font-weight: normal");
        }
    }
</script>
<!--Layout POS-->
<?php echo $this->Form->create('PointOfSale', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
    <input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" id="PointOfSaleCalculateCOGS" />    
    <input type="hidden" value="" id="PointOfSaleVatCalculate" />
    <input type="hidden" value="" id="PointOfSaleShiftRegisterId" />
    <input type="hidden" value="" id="PointOfSaleShiftRegisterCode" />
    <input type="hidden" value="" id="PointOfSaleShiftRegisterCreated" />
    <input type="hidden" value="" id="PointOfSaleCaseShiftRegister" />
    <input type="hidden" value="" id="PointOfSaleCaseShiftRegisterOther" />
    <input type="hidden" value="0" id="customerDisplayTotal" />
    <input type="hidden" value="0" id="customerDisplayTotalRiel" />
    <div class="posContent">
        <div class="posHeader">
            <table class="posTblHeader">
                <tr>
                    <td style="width: 10%;">
                        <?php if ($allowChangeDate && $allowShift == 0) { ?> 
                        <input type="text" name="data[PointOfSale][order_date]" id="posDate" value="<?php echo date('d/m/Y'); ?>" readonly="readonly" style="font-weight: normal;width: 230px;" /> 
                        <?php 
                        } else { 
                            $dayMap = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
                            $dayNum = date('w');
                            echo $dayMap[$dayNum].", ".date('d/m/Y'); 
                        ?> 
                        <input type="hidden" name="data[PointOfSale][order_date]" value="" />
                        <?php } ?>
                    </td>
                    <td rowspan="2" class="headerPadding">
                        <?php
                            if(count($companies) > 1){
                        ?>
                        <image src="<?php echo $this->webroot; ?>img/button/pos/Home.png" style="width: 25px;" />
                        <div><?php echo TABLE_COMPANY; ?></div>
                        <?php
                            }
                        ?>
                    </td>
                    <td rowspan="2" class="headerLeftWidth">
                        <input type="hidden" name="data[PointOfSale][currency_center_id]" value="0" id="PointOfSaleCurrencyCenterId" />
                        <input type="hidden" name="data[PointOfSale][exchange_rate_id]" value="0" id="PointOfSaleExchangeRateId" />
                        <select name="data[PointOfSale][company_id]" id="PointOfSaleCompanyId" <?php if(count($companies) == 1){ ?>style="visibility: hidden;"<?php } else { ?>class="boxSelect selectMenuSetting"<?php } ?>>
                            <?php
                            if(count($companies) != 1){
                            ?>
                            <option ptype="" vat-opt="" value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($companies AS $company){
                                $priceTypeId = '';
                                $sqlPType = mysql_query("SELECT price_type_id FROM pos_price_types WHERE company_id = ".$company['Company']['id']." AND is_active = 1 LIMIT 1;");
                                if(mysql_num_rows($sqlPType)){
                                    $rowPType = mysql_fetch_array($sqlPType);
                                    $priceTypeId = $rowPType[0];
                                }
                            ?>
                            <option currency="<?php echo $company['Company']['currency_center_id']; ?>" ptype="<?php echo $priceTypeId; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>" value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td rowspan="2" class="headerPadding">
                        <?php
                            if(count($branches) > 1){
                        ?>
                        <image src="<?php echo $this->webroot; ?>img/button/pos/brand.png" style="width: 25px;" />
                        <div><?php echo MENU_BRANCH; ?></div>
                        <?php
                            }
                        ?>
                    </td>
                    <td rowspan="2" class="headerLeftWidth">
                        <select name="data[PointOfSale][branch_id]" id="PointOfSaleBranchId" <?php if(count($branches) == 1){ ?>style="visibility: hidden;"<?php } else { ?>class="boxSelect selectMenuSetting"<?php } ?>>
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com="" mcode="" main-symbol="" currency="" symbol="" ex-id="" rate="" vat-d="" acc-num="" acc-holder="" bank=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                                $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 25 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                $exchangeId = '';
                                $currencyId = '';
                                $currencyRate = '';
                                $currencySymbol = '';
                                if($branch['Branch']['pos_currency_id'] != ''){
                                    $sqlCurrencyOther = mysql_query("SELECT branch_currencies.currency_center_id, IFNULL(branch_currencies.rate_to_sell,0), IFNULL(branch_currencies.exchange_rate_id,0), currency_centers.symbol FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.id = ".$branch['Branch']['pos_currency_id']." AND is_pos_default = 1");
                                    if(mysql_num_rows($sqlCurrencyOther)){
                                        $rowCurrencyOther = mysql_fetch_array($sqlCurrencyOther);
                                        $currencyId     = $rowCurrencyOther[0];
                                        $currencyRate   = $rowCurrencyOther[1];
                                        $exchangeId     = $rowCurrencyOther[2];
                                        $currencySymbol = $rowCurrencyOther[3];
                                    }
                                }
                            ?>
                            <option tel="<?php echo $branch['Branch']['telephone']; ?>" main-symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>" ex-id="<?php echo $exchangeId; ?>" currency="<?php echo $currencyId; ?>" rate="<?php echo $currencyRate; ?>" symbol="<?php echo $currencySymbol; ?>" vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['inv_code']; ?>" acc-num="<?php echo $branch['Branch']['acc_num']?>" acc-holder="<?php echo $branch['Branch']['acc_holder']?>" bank="<?php echo $branch['Branch']['bank']?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td rowspan="2" class="headerPadding">
                        <?php
                            if(count($locationGroups) > 1){
                        ?>
                        <image src="<?php echo $this->webroot; ?>img/button/pos/warehouse-icon.png" style="width: 25px;" />
                        <div class="headerPaddingWarehouse"><?php echo TABLE_LOCATION_GROUP; ?></div>
                        <?php
                            }
                        ?>
                    </td>
                    <td rowspan="2" class="headerLeftWidth">
                        <input type="hidden" name="data[PointOfSale][chart_account_id]" value="<?php echo $arAccountId; ?>" />
                        <select name="data[PointOfSale][location_group_id]" id="PointOfSaleLocationGroupId" <?php if(count($locationGroups) == 1){ ?>style="visibility: hidden;"<?php } else { ?>class="boxSelect selectMenuSetting"<?php } ?>>
                            <?php
                            if(count($locationGroups) != 1){
                            ?>
                            <option value="" ><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            ?>
                            <?php
                            foreach($locationGroups AS $locationGroupKey => $locationGroup){
                            ?>
                            <option value="<?php echo $locationGroupKey; ?>"><?php echo $locationGroup; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td rowspan="2" class="headerPadding">
                        <image src="<?php echo $this->webroot; ?>img/button/pos/user.png" style="width: 26px;" />
                    </td>
                    <td class="headerLeftUser">
                        <?php echo GENERAL_WELCOME; ?>
                    </td>
                    <?php 
                        if($allowShift == 1){
                    ?>
                    <td rowspan="2" class="headerIconRight btnChangeShift" style="cursor: pointer;">
                        <image src="<?php echo $this->webroot; ?>img/button/pos/change_shift.png" style="width: 26px;" />
                        <div style="white-space: nowrap;" id="btnChangeShiftLabel"><?php echo MENU_START_SHIFT; ?></div>
                    </td>
                    <?php
                        }
                    ?>                    
                    <td rowspan="2" class="headerIconRight btnLanguage" style="cursor: pointer;">
                        <div class="dropdown">
                            <image src="<?php echo $this->webroot; ?>img/button/pos/language.png" style="width: 26px;" />
                            <div class="dropbtn">
                                <?php echo MENU_LANGUAGE; ?>
                            </div>
                            <div class="dropdown-content">
                                <a href="" id="changeLangEn">
                                  <table style="width: 100%;">
                                      <tr>
                                          <td style="width: 30%;"><image src="<?php echo $this->webroot; ?>img/button/pos/lang_en.png" style="width: 26px;" /></td>
                                          <td>English</td>
                                      </tr>
                                  </table>
                                </a>
                                <a href="" id="changeLangKh" style="border-top: 1px solid #4d68a0;">
                                    <table style="width: 100%;">
                                      <tr>
                                          <td style="width: 30%;"><image src="<?php echo $this->webroot; ?>img/button/pos/lang_kh.png" style="width: 26px;" /></td>
                                          <td>ភាសាខ្មែរ</td>
                                      </tr>
                                  </table>
                                </a>
                            </div>
                        </div> 
                    </td>
                    <td rowspan="2" class="headerIconRight btnLogOut">
                        <div id="logOut" style="cursor: pointer;">
                            <image src="<?php echo $this->webroot; ?>img/button/pos/logout.png" style="width: 26px;" />
                            <div style="white-space: nowrap;">
                                <?php echo GENERAL_LOG_OUT; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="headerClock"><span id="closeClock"></span></td>
                    <td class="headerLeftUser" style="white-space: nowrap;">
                        <?php 
                            $queryUserName = mysql_query("SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE id = '".$user['User']['id']."'");
                            $username = mysql_fetch_array($queryUserName);
                            echo $username[0];
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="posContentBody">
            <div class="posContentLeft">
                <div class="posContentLeftPadding">
                    <div class="posContentLeftSearchProduct">
                        <table id="listTitleSearch">
                            <tr>
                                <td>
                                    <table style="wdith:100%;margin:0px; padding:0px;">
                                        <tr>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed; font-family:cambria;"><?php echo TABLE_TOTAL_ITEM; ?></td>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed;"><span class="amountItemProduct" style="font-family:cambria; font-weight:bold;">0</span></td>
                                            <td style="width:5%;"></td>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed; font-family:cambria;"><?php echo TABLE_TOTAL_QTY; ?></td>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed;"><span class="totalQtyItem" style="font-family:cambria; font-weight:bold;">0</span></td>
                                            <td style="width:5%;"></td>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed; font-family:cambria;"><?php echo TABLE_PROMO_POINT; ?></td>
                                            <td style="background-color:#e8eeff !important; padding:5px 5px 7px 5px; border:1px dashed;">
                                                <input type="hidden" id="totaPointItem"/>
                                                <span class="totaPointItem" style="font-family:cambria; font-weight:bold;">0</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 305px;">
                                    <input type="text" id="PointOfSaleBarcode" />
                                </td>
                                <?php
                                if($allowService){
                                ?>
                                <td style="width: 40px;">
                                    <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" class="addServicePOS" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" />
                                </td>
                                <?php
                                }
                                ?>
                                <td style="width: 231px;">
                                    <?php
                                    if($allowAddProduct){
                                    ?>
                                    <div class="addNewProduct" style="cursor: pointer; width: 180px; height: 30px; padding-top: 8px; text-transform: uppercase;"><?php echo 'Add Product'; ?> [F2]</div>
                                    <div class="clear:both;"></div>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td style="width: 145px;">
                                    <div class="clearOrderList" style="cursor: pointer; width: 140px; height: 30px; padding-top: 8px; text-transform: uppercase;"><?php echo TABLE_CLEAR_ORDER; ?></div>
                                    <div class="clear:both;"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="contentPosListProduct">
                        <?php echo $this->Form->hidden('tmp_id', array('value' => '')); ?>
                        <table id="listTitle" class="posLlistProduct">
                            <thead>
                                <tr>
                                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                                    <th style="width: 15%;"><?php echo POS_QTY; ?></th>
                                    <th style="width: 8%;"><?php  echo TABLE_F_O_C; ?></th>
                                    <th style="width: 18%;"><?php echo POS_UOMS; ?></th>
                                    <th style="width: 10%;"><?php echo POS_UNIT_PRICE; ?></th>
                                    <th style="width: 10%;"><?php echo POS_DISCOUNT; ?></th>
                                    <th style="width: 10%;"><?php echo POS_TOTAL_PRICES; ?></th>
                                    <th style="width: 5.5%;"></th>
                                </tr>
                            </thead>
                        </table>
                        <div id="bodyList">
                            <table id="tblPOS">
                                <tr id="rowProductPOS" class="listTable" style="visibility: hidden;">
                                    <td style="text-align: left; padding-left: 3px;">
                                        <input type="hidden" name="data[SalesOrderDetail][product_id][]" />
                                        <input type="hidden" name="data[SalesOrderDetail][service_id][]" />
                                        <input type="hidden" name="data[SalesOrderDetail][total_price][]" />
                                        <input type="hidden" name="data[SalesOrderDetail][discount_id][]" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][discount_amount][]" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][discount_percent][]" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][qty_order][]" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][conversion][]" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][promotional_id][]" value="" />
                                        <input type="hidden" class="promotionDisUom" value="0" />
                                        <input type="hidden" class="promotionDisPer" value="0" />
                                        <input type="hidden" class="promotionDisAmt" value="0" />
                                        <input type="hidden" class="productInStock"  value="0" />
                                        <input type="hidden" class="price6"  value="0" />
                                        <input type="hidden" class="price12" value="0" />
                                        <input type="hidden" name="data[SalesOrderDetail][product_name][]" value="" />
                                        <span class="productCode" style="display: block;">Code</span>
                                        <span class="productName" style="display: block;">Product Name</span>
                                        <span class="productLots" <?php if($allowLots == false){ ?>style="display: none;"<?php } ?>><input type="text" placeholder="<?php echo TABLE_LOTS_NO; ?>" id="productPOSLots" name="data[SalesOrderDetail][lots_number][]" class="lots_number" style="width: 90%; height: 13px; font-size: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #000; border-left: none; border-right: none; border-top: none;  border-radius: 0 0 0 0; background: none;" /></span>
                                        <span class="productExp" <?php if($allowExpired == false){ ?>style="display: none;"<?php } ?>><input type="text" placeholder="<?php echo TABLE_EXPIRED_DATE; ?>" id="productPOSExp" name="data[SalesOrderDetail][expired_date][]" class="expired_date" style="width: 90%; height: 13px; font-size: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #000; border-left: none; border-right: none; border-top: none;  border-radius: 0 0 0 0; background: none;" /></span>
                                    </td>
                                    <td style="width: 15%;">
                                        <image src="<?php echo $this->webroot; ?>img/button/pos/Action-remove-icon.png" style="cursor: pointer;" class="delQtyMore" />
                                        <input type="text" name="data[SalesOrderDetail][qty][]" value="0" style="width: 35%;" class="editQty" />
                                        <image src="<?php echo $this->webroot; ?>img/button/pos/blue-plus-icon-12.png" style="cursor: pointer;" class="addQtyMore" />
                                    </td>
                                    <td style="width: 8%;">
                                        <input type="text" name="data[SalesOrderDetail][qty_free][]" value="0" style="width: 80%;" class="qtyFree" />
                                    </td>
                                    <td style="width: 18%; padding-left: 10px;">
                                        <select class="editUomQty" name='data[SalesOrderDetail][qty_uom_id][]' style="text-align: center; width: 60%; vertical-align: middle;">
                                            <option value=""><?php echo INPUT_SELECT; ?></option>
                                        </select>
                                    </td>
                                    <td style="width: 10%;">
                                        <?php
                                        if($allowEditPrice){
                                        ?>
                                        <input type="text" name="data[SalesOrderDetail][unit_price][]" style="width: 90%; height: 20px;" class="editPrice float" />
                                        <span class="unitPrice" style="display: none;">0.00</span>
                                        <?php
                                        } else {
                                        ?>
                                        <input type="hidden" name="data[SalesOrderDetail][unit_price][]" />
                                        <span class="unitPrice">0.00</span>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                    <td style="color: blue; text-decoration: underline; width: 10%;">
                                        <span class="editDiscount lblDiscount" style="cursor: pointer; float: left; width: 80%; text-align: center;" onmouseover="Tip('Discount')">0.00</span>
                                        <img alt="Void" src="<?php echo $this->webroot . 'img/button/void.png'; ?>" class="btnRemoveDiscount" align="absmiddle" style="cursor: pointer; float: right; width: 15px; display: none;" onmouseover="Tip('Void')" />
                                        <div style="clear: both;"></div>
                                    </td>
                                    <td style="width: 10.5%;">
                                        <span class="productTotalPrice">0.00</span>
                                    </td>
                                    <td style="width: 5%;">
                                        <image src="<?php echo $this->webroot; ?>img/button/pos/remove-icon-png-25.png" class="removeTr" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                                    </td>
                                </tr>
                            </table>
                            <img id="backgroundLogo" src="<?php echo $this->webroot . 'img/logo.png'; ?>">
                        </div>
                    </div>
                    <div class="footerLeftTotal">
                        <table>
                            <tr>
                                <td style="width: 12%; vertical-align: middle;">
                                    Sub Total 
                                </td>
                                <td style="font-weight: bold; width: 30%;">
                                    <input type="text" value="0.00" id="PointOfSaleSubTotalAmountUsDisplay" readonly="readonly" style="font-weight: bold; border: none; width: 60%;"> (<span class="mainCurrency"></span>)
                                </td>
                                <td style="width: 10%;">VAT</td>
                                <td style="vertical-align: middle;">
                                    <input type="hidden" value=""  style="font-size: 22px;" class="float" id="PointOfSaleVatChartAccountId" />
                                    <input type="hidden" value="0" style="font-size: 22px;" class="float" id="PointOfSaleTotalVat" />
                                    <select id="PointOfSaleVatPercent" style="width: 70%;">
                                        <?php
                                        // VAT
                                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id, chart_account_id FROM vat_settings WHERE is_active = 1 AND type = 1 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                                        while($rowVat = mysql_fetch_array($sqlVat)){
                                        ?>
                                        <option value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" acc="<?php echo $rowVat['chart_account_id']; ?>"><?php echo $rowVat['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 10px; font-weight: bold;" id="PointOfSaleTotalVatDisplay">
                                    0.00
                                </td>
                                <td style="width: 25px; vertical-align: middle; font-weight: bold;">
                                    (<span class="mainCurrency"></span>)
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle; color: red;">Discount 
                                   
                                </td>
                                <td style="font-weight: bold;">
                                    <input type="hidden" name="data[PointOfSale][discount]" class="float" />
                                    <input type="hidden" name="data[PointOfSale][discount_percent]" class="float" />
                                    <input type="text" value="0.00" id="PointOfSaleDiscountUs" style="font-weight: bold; width:60%;" class="float" /> (<span class="mainCurrency"></span>)
                                </td>
                                <td rowspan="2" colspan="4">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="vertical-align: middle; font-size: 22px; font-weight: bold; text-transform: uppercase; height: 50px; width: 16%;">Total </td>
                                            <td style="vertical-align: middle; font-size: 22px; font-weight: bold;"><div style="vertical-align: middle; font-size: 22px; font-weight: bold; text-align: right; padding-right: 10px; width: 70%; float: left;" id="PointOfSaleGrandTotalAmountUsDisplay"></div>(<span class="mainCurrency"></span>)<div style="clear:both;"></div></td>
                                            <td style="vertical-align: middle; font-size: 22px; font-weight: bold; text-align: right; padding-right: 10px;" id="PointOfSaleGrandTotalAmountKhDisplay">
                                                0.00
                                            </td>
                                            <td style="width: 25px; vertical-align: middle; font-size: 22px; font-weight: bold;">
                                                (<span class="otherCurrency"></span>)
                                            </td>
                                        </tr>
                                        <tr class="dialogPaidOther">
                                            <td colspan="4" style="font-size: 11px;">
                                                Exchange: 1 <span class="mainCurrency" style="font-size: 11px;"></span> = <span class="exchangeOtherCurrency" style="font-size: 11px;"></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle; color: red;">Discount
                                    <input type="hidden" id="MembershipCardId" value=""/>
                                    <input type="hidden" id="MembershipCardCode" value="">

                                    <input type="hidden" id="MembershipTotalPoint" value="0.00"/>
                                    <input type="hidden" id="MembershipPointInDollar" value="0.00">
                                    <input type="hidden" id="MembershipExchangePoint" value="0.00"/>

                                    <input type="hidden" id="PromoPointTotalPointSetting" value="0.00">
                                    <input type="hidden" id="PointOfSalePromotionPointId" value="0.00">    

                                    <input type="hidden" id="PromoPoinStart" value="">
                                    <input type="hidden" id="PromoPoinEnd" value="">

                                    <input type="hidden" value="0.00" id="exchangePointInDollar">
                                    <input type="hidden" value="0.00" id="remainPointMemShip">
                                    <input type="hidden" value="0.00" id="promotionalType">
                                    <input type="hidden" value="0.00" id="rewardAmountApply"/> 

                                    <img src="<?php echo $this->webroot; ?>img/button/card.png" id="btnMembershipCardCode" style="width: 35px; float: right; cursor: pointer;" />
                                    <img src="<?php echo $this->webroot; ?>img/button/delete.png" id="btnVoidDisByCard" style="width: 16px; float: right; cursor: pointer; display: none;" />
                                </td>
                                <td style="font-weight: bold;">
                                    <input type="text" value="0.00" id="PointOfSaleDiscountPer" style="font-weight: bold; width:60%;" class="float" /> (%)
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="posContentRight">
                <div class="posContentRightPadding">
                    <div class="leftSearchProduct">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 50%; padding: 0px; height: 35px;">
                                    <table style="width: 100%;">
                                        <tr class="ActScanCode">
                                            <td style="width: 40px; padding: 0px;"><image src="<?php echo $this->webroot; ?>img/button/pos/product_list.png" /></td>
                                            <td style="padding: 0px; text-transform: uppercase; font-weight: bold; font-size: 14px;">Scan [F1]</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; padding: 0px;">
                                    <table style="width: 100%;">
                                        <tr class="ActNewTab">
                                            <td style="width: 40px; padding: 0px;"><image src="<?php echo $this->webroot; ?>img/button/pos/add-tab.png" /></td>
                                            <td style="padding: 0px; text-transform: uppercase; font-weight: bold; font-size: 14px;">New Tab [F4]</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="leftSearchCustomer">
                        <table>
                            <tr>
                                <td class="btnSearchCustomer">
                                    <?php
                                    if($allowAddNewCustomer){
                                    ?>
                                    <image src="<?php echo $this->webroot; ?>img/button/pos/add_customer.png" id="PointOfSaleCustomerName" style="cursor: pointer;" onmouseover="Tip('Add New Customer')" />
                                    <?php
                                    } else {
                                    ?>
                                    <image src="<?php echo $this->webroot; ?>img/button/pos/customer_list.png" style="cursor: pointer;" />
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <input type="hidden" value="1" id="customerPOSId" name="data[PointOfSale][customer_id]" />
                                    <input type="text" placeholder="Find Customer" id="PointOfSaleCustomerNameSearch" style="width: 90%;" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="leftLabelSearchCustomer">
                        <table>
                            <tr>
                                <td><image src="<?php echo $this->webroot; ?>img/button/pos/customer-blue.png" /></td>
                                <td id="PointOfSaleCustomerNameLabel">General Customer</td>
                            </tr>
                        </table>
                    </div>
                    <div class="rightPosProduct">
                        <table>
                            <tr>
                                <td>
                                    <image src="<?php echo $this->webroot; ?>img/button/no-images.png" class="photoProduct" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="leftPosPaid">
                        <table cellspacing="10" cellpadding="0">
                            <tr>
                                <td rowspan="2" id="paidShow">
                                    PAY [F9]
                                </td>
                                <td class="btnReprintReceipt">
                                    <table style="width: 100%;">
                                        <tr class="reprintPOS" style="cursor: pointer;">
                                            <td class="btnReprintReceiptImg">
                                                <image src="<?php echo $this->webroot; ?>img/button/pos/printer.png" />
                                            </td>
                                            <td class="btnReprintReceiptLabel">
                                                Reprint Receipt
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="btnReprintSalesHistory"> 
                                    <table style="width: 100%;">
                                        <tr class="salesPOS" style="cursor: pointer;">
                                            <td class="btnReprintSalesHistoryImg">
                                                <image src="<?php echo $this->webroot; ?>img/button/pos/Sidebar-Search-icon.png" />
                                            </td>
                                            <td class="btnReprintSalesHistoryLabel">
                                                Sales History
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <!--Paid-->
            <div style="display: none;" id="paidDialog">
                <table cellspacing="0" cellpadding="3" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td style="width: 20%;"></td>
                            <td style="text-align: center;">(<span class="mainCurrency"></span>)</td>
                            <td class="dialogPaidOther" style="width: 40%; text-align: center;">(<span class="otherCurrency"></span>)</td>
                        </tr>
                        <tr>
                            <td>Method :</td>
                            <td colspan="2">
                                <select id="PointOfSalePaymentMethod" style="width: 98%;">
                                    <?php
                                    $sqlMd = mysql_query("SELECT * FROM pos_pay_methods WHERE is_active = 1");
                                    while($rowMd = mysql_fetch_array($sqlMd)){
                                    ?>
                                    <option value="<?php echo $rowMd['id']; ?>" charge="<?php echo $rowMd['bank_charge']; ?>"><?php echo $rowMd['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td id="lblTotalAmount">Total :</td>
                            <td>
                                <input type="text" style="font-size: 22px; border: none; width: 90%;" readonly="readonly" id="PointOfSaleTotalAmountUs" />                
                            </td>
                            <td class="dialogPaidOther">
                                <input type="text" style="font-size: 22px; border: none; width: 90%;" readonly="readonly" id="PointOfSaleTotalAmountKh" />                
                            </td>
                        </tr>
                        <tr>
                            <td id="lblTotalAmount">Bank Charge :</td>
                            <td>
                                <input type="text" style="font-size: 22px; border: none; width: 90%;" readonly="readonly" id="PointOfSaleBankChargeAmount" value="0.00" />                
                            </td>
                            <td>
                                <input type="hidden" id="PointOfSaleBankChargePercent" value="0" />
                                <span id="PointOfSaleBankChargePercentLabel" style="font-size: 22px;"></span> 
                            </td>
                        </tr>
                        <tr>
                            <td>Paid :</td>
                            <td>
                                <input type="text" value="0" style="font-size: 22px; width: 90%;" class="float" id="PointOfSalePaidUs">
                            </td>
                            <td class="dialogPaidOther">
                                <input type="text" value="0" style="font-size: 22px; width: 90%;" class="float" id="PointOfSalePaidKh">
                            </td>
                        </tr>
                        <tr>
                            <td>Balance :</td>
                            <td>
                                <input type="text" style="font-size: 22px; width: 90%;" readonly="readonly" value="0" class="float red" id="PointOfSaleBalanceUs">
                            </td>
                            <td class="dialogPaidOther">
                                <input type="text" style="font-size: 22px; width: 90%;" readonly="readonly" value="0" class="float red" id="PointOfSaleBalanceKh">
                            </td>
                        </tr>
                        <tr>
                            <td>Change :</td>
                            <td>
                                <input type="text" style="font-size: 22px; width: 90%;" readonly="readOnly" value="0" class="float" id="PointOfSaleChangeUs">
                            </td>
                            <td class="dialogPaidOther">
                                <input type="text" style="font-size: 22px; width: 90%;" readonly="readOnly" value="0" class="float" id="PointOfSaleChangeKh">
                            </td>
                        </tr>
                        <tr class="dialogPaidOther">
                            <td></td>
                            <td id="convertCentToRielMain" colspan="2"></td>
                        </tr>
                        <tr class="dialogPaidOther">
                            <td></td>
                            <td id="convertCentToRiel" colspan="2"></td>
                        </tr>
                        <tr class="dialogPaidOther">
                            <td>Exchange: </td>
                            <td colspan="2">1 <span class="mainCurrency"></span> = <span class="exchangeOtherCurrency"></span></td>
                        </tr>
                        <tr>
                            <td>Memo :</td>
                            <td colspan="2">
                                <input type="text" style="font-size: 22px; width: 90%; text-align: left;" id="PointOfSaleMemo">
                                <span id="warningMemo" style="color: red; display: none;">Please input discount reason in memo</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <input type="button" value="<?php echo TABLE_POS_PAY; ?>" style="margin: 5px 5px; width: 120px; font-size: 16px;" class="btnPOSPay" id="btnPosPaid" />
                                <!--<input type="button" value="<?php echo TABLE_POS_NOT_PAY_NOW; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPay" id="btnPosNotPaidNow" />-->
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="clear: both;"></div>
            </div>
            <!--Dialog Add Customer-->
            <script>
                $(document).ready(function(){
                    $(".chzn-select").chosen();
                });
            </script>
            <!--Dialog Add Product-->
            <div style="display: none; color: red; font-weight: bold;" id="alertRequiredField">
                <?php echo MESSAGE_SELECT_FIELD_REQURIED; ?>
            </div>
            <!--Dialog Add Shift Register-->
            <div style="display: none;" id="addShiftRigisterDialog">
                <table cellspacing="0" cellpadding="3" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td id="alertOpenShift" style="color: red; display: none; text-align: center;" colspan="2">Your shift are not collect ready. So you cannot open other shift.</td>
                        </tr>
                        <tr class="openShiftRegisterRow">
                            <td style="width: 30%">
                                <?php echo TABLE_TOTAL_CASE_IN_REGISTER; ?>:
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalCaseRegisterAmount" value="0.00" style="width: 90%;" />
                                (<span class="mainCurrency"></span>)                            
                            </td>   
                        </tr>
                        <tr class="openShiftRegisterRow">
                            <td>
                                <?php echo TABLE_TOTAL_CASE_IN_REGISTER; ?>:
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalCaseRegisterAmountOther" value="0" style="width: 90%;" />
                                (<span class="otherCurrency"></span>)
                            </td>   
                        </tr>
                        <tr class="openShiftRegisterRow">
                            <td style="vertical-align: top;">
                                <?php echo GENERAL_DESCRIPTION; ?>:
                            </td>
                            <td>
                                <textarea rows="4" cols="47" style="width: 90%;" id="PointOfSaleShiftRegisterDescription"></textarea>
                            </td>                            
                        </tr>  
                        <tr class="openShiftRegisterRow">
                            <td style="width: 100%; padding-right: 30px;" colspan="2">
                                <input type="button" value="<?php echo MENU_START_SHIFT; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPay" id="btnSaveAddShiftRegister" />                               
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="clear: both;"></div>
            </div>
            <!--Dialog Add Adjust and Close Shift-->
            <div style="display: none;" id="AddAdjCloseShiftRigisterDialog">
                <div style="width: 100%; text-align: center;">
                    <table cellspacing="0" cellpadding="3" style="width: 100%;">                       
                        <tbody>
                            <tr>
                                <td style="width: 100%; text-align: center;">    
                                    <input type="button" value="<?php echo TABLE_CASE_MANAGEMENT; ?>" style="margin: 5px 0px; width: 200px; font-size: 16px;" class="btnPOSPayNormal" id="btnShowAdjShiftRegister" />                                
                                    <input type="button" value="<?php echo MENU_END_SHIFT; ?>" style="margin: 5px 0px; width: 200px; font-size: 16px;" class="btnPOSPayNormal" id="btnShowCloseShiftRegister" />        
                                    <div style="clear: both;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="table" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="first" colspan="3" style="text-align: center;"><?php echo MENU_TITLE_SHIFT; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="first" style="width: 20%;"><?php echo TABLE_CHANGE_SHIFT_CODE; ?>: </td>
                                <td style="width: 30%;">
                                    <span id="labelShiftRegisterCode" style="font-weight: bold;"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="first"><?php echo TABLE_SHIFT_USER_SALES; ?>: </td>
                                <td style="font-weight: bold;"> <?php echo $user['User']['username']; ?></td>                    
                            </tr>
                            <tr>
                                <td class="first"><?php echo TABLE_DATE_TIME_START; ?>: </td>
                                <td>
                                    <span id="labelShiftRegisterCreated" style="font-weight: bold;"></span>
                                </td>
                            </tr>                            
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="table" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="first" colspan="3" style="text-align: center;"><?php echo TABLE_CASE_SHIFT; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="first" style="text-align: center;"></td>
                                <td style="text-align: center;"><span class="mainCurrency"></span></td>
                                <td style="text-align: center;"><span class="otherCurrency"></span></td>
                            </tr>
                            <tr>
                                <td class="first">    
                                    <?php echo TABLE_SHORT_CASE_IN_REGISTER; ?>: 
                                </td>
                                <td style="text-align: right;">
                                    <span id="labelAdjCaseRegister" style="font-weight: bold;"></span>
                                </td>
                                <td style="text-align: right;">    
                                    <span id="labelAdjCaseRegisterOther" style="font-weight: bold; text-align: right;"></span> 
                                </td>                      
                            </tr>
                            <tr>
                                <td class="first">    
                                    <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?>: 
                                </td>
                                <td style="text-align: right;">
                                    <span id="labelTotalAdjCaseRegister" style="font-weight: bold;"></span>
                                </td>
                                <td style="text-align: right;">    
                                    <span id="labelTotalAdjCaseRegisterOther" style="font-weight: bold; text-align: right;"></span> 
                                </td>                     
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="getDataAdjShift" style="width: 100%;">
                    
                </div>
                <div style="clear: both;"></div>
            </div>
            <!--Dialog End Shift Register-->
            <div style="display: none;" id="endShiftRigisterDialog">
                <table cellspacing="0" cellpadding="3" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="first"></th>
                            <th style="text-align: center;">(<span class="mainCurrency"></span>)</th>
                            <th></th>
                            <th style="text-align: center;">(<span class="otherCurrency"></span>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width: 20%">
                                <?php echo TABLE_SHORT_CASE_IN_REGISTER; ?>:
                            </td>
                            <td style="width: 30%">
                                <input type="text" class="float textAlignLeft" id="labelCaseRegister" readonly="readonly" disabled="disabled" style="width: 90%; border: none;" />                        
                            </td>  
                            <td style="width: 20%">
                                <?php echo TABLE_SHORT_CASE_IN_REGISTER; ?>:
                            </td>
                            <td style="width: 30%">
                                <input type="text" class="float textAlignLeft" id="labelCaseRegisterOther" readonly="readonly" disabled="disabled" style="width: 90%; border: none;" />
                            </td>  
                        </tr>
                        <tr>
                            <td>
                                <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?>:
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="labelAdjRegister" readonly="readonly" disabled="disabled" style="width: 90%; border: none;" />                   
                            </td>   
                            <td>
                                <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?>:
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="labelAdjRegisterOther" readonly="readonly" disabled="disabled" style="width: 90%; border: none;" />
                            </td>  
                        </tr>
                        <tr>
                            <td>
                                <?php echo TABLE_TOTAL_ACTURE_REGISTER; ?>: 
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalActureEndRegisterAmount" value="0.00" style="width: 90%;" />                         
                            </td>   
                            <td>
                                <?php echo TABLE_TOTAL_ACTURE_REGISTER; ?>: 
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalActureEndRegisterAmountOther" value="0" style="width: 90%;" />
                            </td>  
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <?php echo GENERAL_DESCRIPTION; ?>: 
                            </td>
                            <td colspan="3">
                                <textarea rows="4" cols="47" style="width: 96%;" id="PointOfSaleEndShiftRegisterDescription"></textarea>
                            </td>                            
                        </tr>  
                        <tr>
                            <td style="width: 100%; padding-right: 30px;" colspan="4">
                                <input type="button" value="<?php echo ACTION_CANCEL; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPay" id="btnCloseEndShiftRegister" />                                
                                <input type="button" value="<?php echo MENU_END_SHIFT; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPay" id="btnSaveEndShiftRegister" />                                  
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="clear: both;"></div>
            </div>
            <!--Dialog add Adj Shift-->
            <div style="display: none;" id="AddAdjShiftRigisterDialog">
                <table cellspacing="0" cellpadding="3" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td style="width: 30%">
                                <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?>: 
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalAddAdjEndRegisterAmount" value="0.00" style="width: 90%;" />
                                (<span class="mainCurrency"></span>)                            
                            </td>   
                        </tr>
                        <tr>
                            <td>
                                <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?>:
                            </td>
                            <td>
                                <input type="text" class="float textAlignLeft" id="PointOfSaleTotalAddAdjEndRegisterAmountOther" value="0" style="width: 90%;" />
                                (<span class="otherCurrency"></span>)
                            </td>   
                        </tr>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <?php echo GENERAL_DESCRIPTION; ?>: <span class="red">*</span>
                            </td>
                            <td>
                                <textarea rows="4" cols="47" style="width: 90%;" id="PointOfSaleAddAdjShiftRegisterDescription"></textarea>
                            </td>                            
                        </tr>  
                        <tr>
                            <td style="width: 100%; padding-right: 30px;" colspan="2">        
                                <input type="button" value="<?php echo TABLE_TOTAL_PAY_OUT; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPayRed" id="btnPayOutAdjShiftRegister" />                                  
                                <input type="button" value="<?php echo TABLE_TOTAL_PAY_IN; ?>" style="margin: 5px 0px; width: 150px; font-size: 16px;" class="btnPOSPayGreen" id="btnPayInAdjShiftRegister" />
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="clear: both;"></div>
            </div>
            <!-- Processing -->
            <div style="display: none;" id="dialogProcessing"><p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p></div>
            <!-- Hidden Receive UOM -->
            <div style="display: none;">
                <input type="hidden" id="PointOfSaleQty" value="0" />
                <input type="hidden" id="PointOfSaleUnitPrice" value="0" />
                <select id="qty_uom_id"></select>
            </div>
            <!-- Reprint Form -->
            <div id="dialogReprintInvoice" style="display:none" title="Reprint Invoice">
                <table cellpadding="5" cellspacing="0">
                    <tr>
                        <td><?php echo TABLE_INVOICE_CODE; ?></td>
                        <td>
                            <div class="inputContainer">
                                <input type="text" id="invoiceCodeReprint" value="" style="width: 200px; text-align: left;" />
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php echo $this->Form->end(); ?> 
    <div id="frmLoginPermission" style="display: none;">
        <table style="width: 100%;" border="0" cellpadding="3" cellspacing="0">
            <tr>
                <td style="width: 25%;"><label for="loginPermissionUsername"><?php echo USER_USER_NAME; ?> :</label></td>
                <td>
                    <input type="text" id="loginPermissionUsername" style="width: 90%; text-align: left;" />
                </td>
            </tr>
            <tr>
                <td style="width: 25%;"><label for="loginPermissionPassword"><?php echo USER_PASSWORD; ?> :</label></td>
                <td>
                    <input type="password" id="loginPermissionPassword" style="width: 90%; text-align: left;" />
                </td>
            </tr>
        </table>
    </div>
<script type="text/javascript">
    var tabNumber = <?php echo $tab; ?>;
    var validNavigation = false;
    var posIndex;
    var productPacket = [];
    var isPaid = false;
    var actionEdit = 0;
    var heightScreen = $(window).height();
    var shortly = 0;
    var rowIndex;
    var firstReload = 0;
    var rowTablePOS =  $("#rowProductPOS");
    var layoutPrint = '';
    var memoRequired  = false;
    var waitForFinalEventPOS = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();
    var waitEnablePOS = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();
    var waitDialogPaid = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();
    var waitSearchCode = (function () {
        var timers = {};
        return function (callback, ms, uniqueId) {
            if (!uniqueId) {
                uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
                clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
        };
    })();
    
    function checkBrowserClose() {
        window.onbeforeunload = function() {
            if (!validNavigation) {
                var tabPOSDis = "tabPOSDisplay"+tabId;
                localStorage.removeItem(tabPOSDis);
                if(checkExistingRecord() == true){
                    return "You have made changes on this page that you have not yet confirmed. If you navigate away from this page you will lose your unsaved changes";
                }
            }
        }
    }
    
    function loadCompany() {
        if ($.cookie('companyId') != null) {
            $("#PointOfSaleCompanyId").val($.cookie('companyId'));
        }
        $.cookie('companyId', $("#PointOfSaleCompanyId").val(), {expires: 5,path: '/'});
        // Filter Branch
        var companyId = $("#PointOfSaleCompanyId").val();
        $("#PointOfSaleBranchId").filterOptions('com', companyId, '');
        checkVatBranchSales();
        checkCurrency();
    }
    
    function checkCurrency(){
        var mainCurrencySymbol  = $("#PointOfSaleBranchId").find("option:selected").attr("main-symbol");
        var otherCurrencySymbol = $("#PointOfSaleBranchId").find("option:selected").attr("symbol");
        var otherCurrencyRate   = $("#PointOfSaleBranchId").find("option:selected").attr("rate");
        var otherCurrencyId     = $("#PointOfSaleBranchId").find("option:selected").attr("currency");
        var exchangeRateId      = $("#PointOfSaleBranchId").find("option:selected").attr("ex-id");
        $(".mainCurrency").html(mainCurrencySymbol);
        $(".otherCurrency").html(otherCurrencySymbol);
        $(".exchangeOtherCurrency").html(otherCurrencyRate+" "+otherCurrencySymbol);
        $("#PointOfSaleCurrencyCenterId").val(otherCurrencyId);
        $("#PointOfSaleExchangeRateId").val(exchangeRateId);
        if(otherCurrencyId == '' || otherCurrencyId == '0'){
            $(".dialogPaidOther").hide();
        } else {
            $(".dialogPaidOther").show();
        }
    }
    
    function warningConfigSetting(){
        var question = "There are some setting not setup.<br/> 1- Set Warehouse for POS <br/> 2- Set Price Type for POS";
        $("#dialog").html('<p style="font-size: 15px;">'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_WARNING; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            closeOnEscape: false,
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    reloadPage();
                    $(this).dialog("close");
                }
            }
        });
    }

    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        //Empty Location Group 
        <?php
        if(empty($locationGroups)){
        ?>
        warningConfigSetting();
        <?php
        }
        ?>
        // Remove Row Product
        $("#rowProductPOS").remove();
        // Reset Customer
        $("#customerPOSId").val(1);
        $("#PointOfSaleCustomerNameLabel").val('General Customer');
        //Load Function
        closeTime();
        checkBrowserClose();
        refreshBrowser();
        loadCompany();
        // VAT Setting Change
        $("#PointOfSaleVatPercent").change(function(){
            checkVatSelectedSales();
            getTotalAmount();
        });
        // Set VAT Calculate
        var vatCal = $("#PointOfSaleCompanyId").find("option:selected").attr("vat-opt");
        $("#PointOfSaleVatCalculate").val(vatCal);
        // Check VAT Setting
        changeLblVatCalSales();
        checkPriceTypeCom();

        $(window).resize(function(){
            $(window).height();
            refreshBrowser();
        });

        $('document').bind('keypress', function(e) {
            if (e.keyCode == 116){
                validNavigation = true;
            }
        });

        $(".float").autoNumeric({mDec: 3});
        $(".integer").autoNumeric({mNum: 9,mDec:0});
        $(":text").labelify({ labelledClass: "labelHighlight" });
        $("#PointOfSaleAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PointOfSaleAddForm").ajaxForm();
        // Cookie Location Group
        if($.cookie('location_id')!=null){
            $("#PointOfSaleLocationGroupId").val($.cookie("location_id"));
        }
        //Change Location Group
        $("#PointOfSaleLocationGroupId").change(function(){
             if($(this).val() != ''){
                $(this).closest("td").find(".nice-select").removeAttr("style");
                $("#PointOfSaleBarcode").removeAttr("disabled");
            } else {
                $(this).closest("td").find(".nice-select").css("border", "2px solid red");
                $("#PointOfSaleBarcode").attr("disabled", true);
            }
            changeLocation();
        });
        // Change Company
        $("#PointOfSaleCompanyId").change(function() {
            $.cookie("PointOfSaleCompanyId", $(this).val(), {expires : 7,path    : '/'});
            if($(this).val() != ''){
                $(this).closest("td").find(".nice-select").removeAttr("style");
                $("#PointOfSaleBarcode").removeAttr("disabled");
            } else {
                $(this).closest("td").find(".nice-select").css("border", "2px solid red");
                $("#PointOfSaleBarcode").attr("disabled", true);
            }
            changeCompany();
        }); 
        // Cookie Branch
        if($.cookie('PointOfSaleBranchId')!=null){
            $("#PointOfSaleBranchId").val($.cookie('PointOfSaleBranchId'));
            checkCurrency();
        }
        // Change Branch
        $("#PointOfSaleBranchId").change(function() {
            $.cookie("PointOfSaleBranchId", $(this).val(), {expires : 7,path    : '/'});
            if($(this).val() != ''){
                $(this).closest("td").find(".nice-select").removeAttr("style");
                $("#PointOfSaleBarcode").removeAttr("disabled");
            } else {
                $(this).closest("td").find(".nice-select").css("border", "2px solid red");
                $("#PointOfSaleBarcode").attr("disabled", true);
            }
            checkVatBranchSales();
            checkCurrency();
        });
        
        // Nice Select
        $('.selectMenuSetting, #PointOfSaleAddCustomerCompanyId, #PointOfSaleAddCustomerType').niceSelect();
        
        // Action Add New Service
        $(".addServicePOS").click(function(){
            if($("#PointOfSaleCompanyId").val() == "" || $("#PointOfSaleBranchId").val() == "" || $("#PointOfSaleLocationGroupId").val() == ""){
                alertSelectRequireField();
            } else {
                loadService();
            }
        });
        
        <?php
        if($allowAddProduct){
        ?>
        // Action Add New Product
        $(".addNewProduct").click(function(){
            loadAddProduct();
        });
        <?php
        }
        ?>
        
        // Action Save Add Shift Register Dialog 
        $("#btnSaveAddShiftRegister").click(function(){
            SaveAddShiftRegister();
        });
        
        // Action Close Add Shift Register Dialog 
        $("#btnCloseAddShiftRegister").click(function(){
            $("#addShiftRigisterDialog").dialog("close");
            location.reload();
        });
        
        // Action Show Add Adj and Close Shift Register
        $(".btnChangeShift").click(function(){
            ShowAdjAndCloseShiftRegister();
        });
        
        // Action Show Adj Shift Register
        $("#btnShowAdjShiftRegister").click(function(){
            $("#AddAdjCloseShiftRigisterDialog").dialog("close");
            ShowAdjShiftRegister();
        });
        
        // Action Save End Shift Register Dialog 
        $("#btnSaveEndShiftRegister").click(function(){
            SaveEndShiftRegister();
        });
        
        // Action Save Adj Shift Register Dialog 
        $("#btnPayInAdjShiftRegister").click(function(){
            SaveAdjShiftRegister(1);
        });
        
        $("#btnPayOutAdjShiftRegister").click(function(){
            SaveAdjShiftRegister(2);
        });
        
        // Action Close Shift Register Dialog 
        $("#btnShowCloseShiftRegister").click(function(){
            $("#AddAdjCloseShiftRigisterDialog").dialog("close");
            ShowCloseShiftRegister();
        });
        
        // Action Close End Shift Register Dialog 
        $("#btnCloseEndShiftRegister").click(function(){
            $("#endShiftRigisterDialog").dialog("close");
        });
        
        // Action Log out
        $("#logOut").click(function(){
            logOut();
        });
        
        //Key Up Shift
        $("#PointOfSaleTotalCaseRegisterAmount, #PointOfSaleTotalAddAdjEndRegisterAmount, #PointOfSaleTotalActureEndRegisterAmount").focus(function(){
            if($(this).val() == "0.00"){
                $(this).val('');
            }
        });
        
        $("#PointOfSaleTotalCaseRegisterAmount, #PointOfSaleTotalAddAdjEndRegisterAmount, #PointOfSaleTotalActureEndRegisterAmount").blur(function(){
            if($(this).val() == ""){
                $(this).val("0.00");
            }
        });
        
        $("#PointOfSaleTotalCaseRegisterAmountOther, #PointOfSaleTotalAddAdjEndRegisterAmountOther, #PointOfSaleTotalActureEndRegisterAmountOther").focus(function(){
            if($(this).val() == "0"){
                $(this).val("");
            }
        });
        
        $("#PointOfSaleTotalCaseRegisterAmountOther, #PointOfSaleTotalAddAdjEndRegisterAmountOther, #PointOfSaleTotalActureEndRegisterAmountOther").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        <?php
        if($allowAddNewCustomer){
        ?>
        // Action Add Customer
        $("#PointOfSaleCustomerName").click(function(){
            loadAddCustomer();
        });
        <?php
        }
        ?>

        // Search Customer Name by Autocomplete
        $("#PointOfSaleCustomerNameSearch").autocomplete("<?php echo $this->base . "/customers/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            }
        }).result(function(event, value){
            var customerId    = value.toString().split(".*")[0];
            var customerName  = value.toString().split(".*")[1];
            var customerDisc  = value.toString().split(".*")[10];
            $("#PointOfSaleCustomerNameSearch").val('');
            $("#customerPOSId").val(customerId);
            $("#PointOfSaleCustomerNameLabel").text(customerName);
            $("#PointOfSaleDiscountPer").val(customerDisc);
            getTotalAmount();
        });
        
        $("#PointOfSaleDiscountUs, #PointOfSaleDiscountPer").focus(function(){
            if(invDisPermission == '1'){
                if(replaceNum($(this).val()) == 0){
                    $(this).val("");
                }
            } else {
                $("#PointOfSaleBarcode").focus();
                changePermissionLogin($(this), 'invpos');
            }
        });
        
        $("#PointOfSaleDiscountUs, #PointOfSaleDiscountPer").blur(function(){
            if($(this).val() == ""){
                $(this).val("0.00");
            }
            if($(this).attr("id") == "PointOfSaleDiscountUs"){
                var val      = replaceNum($(this).val());
                var subTotal = replaceNum($("#PointOfSaleSubTotalAmountUsDisplay").val());
                if(val > subTotal){
                    $(this).val(replaceNum(subTotal).toFixed(2));
                }
            } else {
                if(replaceNum($(this).val()) > 100){
                    $(this).val(100);
                }
            }
            getTotalAmount();
        });

        // Discount Total Us
        $("#PointOfSaleDiscountUs").keyup(function(e){
            if($(this).val() > 0){
                $("#PointOfSaleDiscountPer").val("0.00");
            }
        });

        // Discount Percent
        $("#PointOfSaleDiscountPer").keyup(function(e){
            if($(this).val() > 0){
                $("#PointOfSaleDiscountUs").val("0.00");
            }
        });

        // Action Btn Paid
        $("#btnPosPaid").click(function(){
            paid();
        });
        
        // Action Btn Not Paid
        $("#btnPosNotPaidNow").click(function(){
            notPaidNow();
        });

        // Action PAY
        $("#paidShow").click(function(){
            var formName = "#PointOfSaleAddForm";
            var validateBack = $(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            } else {
                calculateBalance();
                refreshBrowser();
                paidDilaog();
            }
        });
        
        $("#btnMembershipCardCode").click(function(){
            var customerId = $("#customerPOSId").val();
            if(customerId!=""){
                membershipDialog(customerId);
            }else{
                $("#dialog").html('<p style="color:red; font-size:14px;">Please select customer.</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    closeOnEscape: false,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").hide();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                            $(".ui-dialog-titlebar-close").show();
                        }
                    }
                });
            }
        });

        $("#btnVoidDisByCard").click(function(){
            resetVoidMembership();
            calculateBalance();
            getTotalAmount();
        });
        
        // Action Scan
        $(".ActScanCode").click(function(){
            $("#PointOfSaleBarcode").focus();
        });
        
        // Action New Tab
        $(".ActNewTab").click(function(){
            tabNumber = tabNumber + 1;
            window.open('<?php echo $this->base; ?>/point_of_sales/add/'+tabNumber, '_blank');
        });

        // Action Reprint
        $(".reprintPOS").click(function(){
            reprintInvoice();
        });

        // Action Sales Invoice
        $(".salesPOS").click(function(){
            salesInvoice();
        });

        // Action Clear Order List
        $(".clearOrderList").click(function(){
            if(checkExistingRecord() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove all ordering?</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: '300',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $(this).dialog("close");
                            $("#tblPOS").html('');
                            $(".amountItemProduct").text('0');
                            // Default
                            clearProduct();
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Search Product
        var options = {
            data: "storageProducts",
            getValue: function(element) {
                return element.sku+"-"+element.upc+"-"+element.name;
            },
            list: {
                match: {
                    enabled: true
                },
                onClickEvent: function() {
                    var code  = $("#PointOfSaleBarcode").getSelectedItemData().sku;
                    var uomId = $("#PointOfSaleBarcode").getSelectedItemData().uom_id;
                    var qty   = 1;
                    getProductByCode(code, uomId, qty,0,'','');
                    $("#PointOfSaleBarcode").val('');
		        },
                onKeyEnterEvent: function(){
                    var code  = $("#PointOfSaleBarcode").getSelectedItemData().sku;
                    var uomId = $("#PointOfSaleBarcode").getSelectedItemData().uom_id;
                    var qty   = 1;
                    getProductByCode(code, uomId, qty,0,'','');
                    $("#PointOfSaleBarcode").val('');
                },
                onShowListEvent: function(){
                    if($("#PointOfSaleCompanyId").val() == "" || $("#PointOfSaleBranchId").val() == "" || $("#PointOfSaleLocationGroupId").val() == ""){
                        alertSelectRequireField();
                    } else {
                        $("#eac-container-PointOfSaleBarcode").removeAttr("style").css("height", "300");
                        $("#eac-container-PointOfSaleBarcode").css("z-index", "3");
                    }
                },
                onHideListEvent: function(){
                    $("#eac-container-PointOfSaleBarcode").css("z-index", "-1");
                },
                maxNumberOfElements: 36
            },
            template: {
                type: "custom",
                method: function(value, item) {  
                    var symbol  = $("#PointOfSaleBranchId").find("option:selected").attr("main-symbol");
                    return generateBoxProductAutoSearch(item.icon, item.upc, item.sku, item.uom, item.name, item.price, symbol);
                }
            },
            highlightPhrase: false,
            placeholder: "<?php echo TABLE_SEARCH_UPC_SKU_NAME; ?>",
            height: 300
        };
        $("#PointOfSaleBarcode").easyAutocomplete(options);
        $("#PointOfSaleBarcode").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                var products = "";
                var scancode = $(this).val().toString();
                var isCheck  = false;
                var code     = "";
                var uomId    = "";
                var qty      = 1;
                if (localStorage.getItem("products") != null && localStorage.getItem("products") != '[]' && localStorage.getItem("products") != '') {
                    products = localStorage.getItem("products");
                }
                // Reset Value & Autocomplete
                $(this).val('');
                $("#eac-container-PointOfSaleBarcode").css("z-index", "-1");
                // Check Product
                if(products != ""){
                    var data = JSON.parse(products);
                    $.each(data, function (index, value) {
                        var sku = value.sku.toString();
                        var upc = value.upc.toString();
                        if(sku == scancode || upc == scancode){
                            code = sku;
                            uomId = value.uom_id.toString();
                            isCheck = true;
                            return false;
                        }
                    });
                }
                if(isCheck == true){
                    getProductByCode(code, uomId, qty,0,'','');
                } else {
                    alertInvalidCode();
                }
                return false;
            }
        });
        <?php
        if ($allowChangeDate) {
        ?>
        // Change Order Date
        $('#posDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#posDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#posDate").datepicker("option", "maxDate", 0);
        <?php
        }
        ?>
        // Default
        clearProduct();
        // Shortcut Key
        shortcutKey();
        // Check Vat Chart Account
        checkVatSelectedSales();
        // Check Field Requriement
        if($("#PointOfSaleCompanyId").val() == "" || $("#PointOfSaleBranchId").val() == "" || $("#PointOfSaleLocationGroupId").val() == ""){
            alertSelectRequireField();
            $("#PointOfSaleBarcode").attr("disabled", true);
            if($("#PointOfSaleCompanyId").val() == ""){
                $("#PointOfSaleCompanyId").closest("td").find(".nice-select").css("border", "2px solid red");
            }
            if($("#PointOfSaleBranchId").val() == ""){
                $("#PointOfSaleBranchId").closest("td").find(".nice-select").css("border", "2px solid red");
            }
            if($("#PointOfSaleLocationGroupId").val() == ""){
                $("#PointOfSaleLocationGroupId").closest("td").find(".nice-select").css("border", "2px solid red");
            }
        }
    });

    function resetVoidMembership(){
        $("#PointOfSaleDiscountUs").val(0).attr("readonly", false);
        $("#MembershipCardId").val('');
        $("#MembershipCardCode").val('');
        $("#PointOfSaleDiscountPer").val(0).attr("readonly", false);

        // Membership Car & Promotional Point
        $(".totaPointItem").text(0);
        $("#totaPointItem").val(0);
        $("#PointOfSalePromotionPointId").val('');
        $("#PromoPointTotalPointSetting").val(0);
        $("#PromoPoinStart").val('');
        $("#PromoPoinEnd").val('');

        $("#MembershipPointInDollar").val(0);
        $("#MembershipExchangePoint").val(0);
        $("#MembershipTotalPoint").val(0);
        $("#exchangePointInDollar").val(0);
        $("#MembershipTotalPoint").val(0);
        $("#remainPoint").hide();
        $("#remainPointMemShip").val(0);
        $("#promotionalType").val('');
        // Button
        $("#btnMembershipCardCode").show();
        $("#btnVoidDisByCard").hide();
    }
    
    function shortcutKey(){
        shortcut.add("F1",function() {
            $("#PointOfSaleBarcode").focus();
        });
        <?php
        if($allowAddProduct){
        ?>
        shortcut.add("F2",function() {
            loadAddProduct();
        });
        <?php
        }
        ?>
        shortcut.add("F4",function() {
            $(".ActNewTab").click();
        });
        shortcut.add("F9",function() {
            $("#paidShow").click();
        });
    }

    function closeTime() {
        var today=new Date();
        var h=today.getHours();
        var m=today.getMinutes();
        var s=today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);        
        $('#closeClock').text(h+":"+m+":"+s);        
        setTimeout(function(){closeTime()},500);
    }
    
    function checkTime(i) {
        if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
        return i;
    }
    
    function refreshBrowser(){
        waitForFinalEventPOS(function(){
            refreshScreen();
            resizeFormTitle();
            resizeFornScroll();
        }, 500, "Finish");
    }
    
    function resizeFormTitle() {
        var screen = 16;
        var widthList  = $("#bodyList").width();
        var widthTitle = $("#bodyList").width();
        $("#listTitle").css('width', widthList);
        if (!$.browser.safari) {
            widthTitle = widthList - screen;
        }
        $("#listTitle, #listTitleSearch").css('padding', '0px');
        $("#listTitle").css('width', widthTitle);
    }
    
    function resizeFornScroll(){
        //Left Side 32
        var heightWindow        = $(window).height();
        var headerHeight        = $(".posHeader").height();
        var headerSearchProduct = $(".headerTitleSearch").height();
        var headerThTable       = $(".posLlistProduct").height();
        var footerLeftTotal     = $(".footerLeftTotal").height();
        var getHeightLeft       = heightWindow - (headerHeight + headerSearchProduct + footerLeftTotal + headerThTable) - 100;

        //Right Side
        var heightProSearch = $(".leftSearchProduct").height();
        var heightCusSearch = $(".leftSearchCustomer").height();
        var heightPosPaid   = $(".leftPosPaid").height();
        
        var getHeightRight  = heightWindow - (headerHeight + heightProSearch + heightCusSearch + heightPosPaid) - 132;
        $(".posContentLeft").css("width", "75%");
        $(".posContentRight").css("width", "24.5%");
        $(".headerPaddingWarehouse").css("padding-left", "0px");
        $(".photoProduct").css("max-height", "150px");
        if($(window).width() <= 1024){
            getHeightRight  = heightWindow - (headerHeight + heightProSearch + heightCusSearch + heightPosPaid) - 132;
            $(".posContentLeft").css("width", "66%");
            $(".posContentRight").css("width", "33.5%");
            $(".headerPaddingWarehouse").css("padding-left", "10px");
            $(".photoProduct").css("max-height", "100px");
        }

        $("#bodyList").css('height',getHeightLeft);
        $("#bodyList").css('padding','0px');
        $("#bodyList").css('width','100%');
        $("#bodyList").css('overflow-x','hidden');
        $("#bodyList").css('overflow-y','scroll');
        $(".rightPosProduct").css('height',getHeightRight);
        resizeFormTitle();
    }
    
    function refreshScreen(){
        $("#listTitle").removeAttr('style');
        $("#bodyList").removeAttr('style');
    }
    
    function logOut(){
        $("#dialog").html('<?php echo MESSAGE_CONFIRM_LOG_OUT; ?>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    window.location.href = "<?php echo $this->base; ?>/users/logout";
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    <?php
    if($allowAddProduct){
    ?>
    function loadAddProduct(){
        var locationId = $("#PointOfSaleLocationGroupId").val();
        if(locationId != ''){
            $("#dialog").html('');
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/point_of_sales/quickAddProduct/"; ?>",
                beforeSend: function(){
                    $("#progress").show();
                },
                success: function(msg){
                    $("#progress").hide();
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '900',
                        height: '600',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#ProductQuickAddProductForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    <?php 
                                    if(count($branches) > 1){
                                    ?>
                                    listbox_selectall('productBranchSelected', true);
                                    <?php
                                    }
                                    ?>
                                    if($("#productBranchSelected").val() == null || $("#ProductPgroupId").val() == null || $("#ProductPgroupId").val() == '' || $("#ProductUomId").val() == null || $("#ProductUomId").val() == ''){
                                        alertSelectRequireField();
                                    } else {
                                        $(this).dialog("close");
                                        var dataPost = $("#ProductQuickAddProductForm").serialize()+"&"+$('#formBranchProductQuick').serialize();
                                        $.ajax({
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/point_of_sales/quickAddProduct",
                                            data: dataPost,
                                            beforeSend: function(){
                                                
                                            },
                                            success: function(result){
                                                getProductCache();
                                                // Message Alert
                                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                                                    createSysAct('POS', 'Quick Add Product', 2, result);
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                }else {
                                                    createSysAct('POS', 'Quick Add Product', 1, '');
                                                    // alert message
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                                }
                                                $("#dialog").dialog({
                                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                                    resizable: false,
                                                    modal: true,
                                                    width: 'auto',
                                                    height: 'auto',
                                                    position: 'center',
                                                    open: function(event, ui){
                                                        $(".ui-dialog-buttonpane").show();
                                                    },
                                                    buttons: {
                                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    }
                                }  
                            }
                        }
                    });
                }
            });
        }
    }
    <?php
    }
    if($allowAddNewCustomer){
    ?>
    function loadAddCustomer(){
        var companyId = $("#PointOfSaleCompanyId").val();
        if(companyId != ''){
            $("#dialog").html('');
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/point_of_sales/quickAddCustomer/"; ?>",
                beforeSend: function(){
                    $("#progress").show();
                },
                success: function(msg){
                    $("#progress").hide();
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_CUSTOMER_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '550',
                        height: '600',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#CustomerQuickAddCustomerForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/point_of_sales/quickAddCustomer",
                                        data: $("#CustomerQuickAddCustomerForm").serialize(),
                                        beforeSend: function(){
                                            $("#progress").show();
                                        },
                                        success: function(result){
                                            $("#progress").hide();
                                            // Message Alert
                                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                                                createSysAct('POS', 'Quick Add Customer', 2, result);
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            }else {
                                                createSysAct('POS', 'Quick Add Customer', 1, '');
                                                // alert message
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                            }
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position: 'center',
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }  
                            }
                        }
                    });
                }
            });
        }
    }
    <?php
    }
    ?>
    function loadService(){
        if($("#PointOfSaleCompanyId").val()=="" || $("#PointOfSaleBranchId").val()==""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_FIRST; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_WARNING; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                    $(".ui-dialog-titlebar-close").show();
                },
                close: function(event, ui){
                    
                },
                buttons: {
                    '<?php echo ACTION_CLOSE; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/point_of_sales/service"; ?>/"+ $("#PointOfSaleCompanyId").val()+"/"+ $("#PointOfSaleBranchId").val(),
                beforeSend: function(){
                    $("#progress").show();
                },
                error: function (result) {
                    $("#progress").hide();
                    createSysAct('POS', 'Load Service', 2, result.responseText);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONNECTION_LOSE; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                },
                success: function(msg){
                    $("#progress").hide();
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_ADD_SERVICE; ?>',
                        resizable: false,
                        modal: true,
                        width: '500',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var section = $("#ServiceSectionId option:selected" ).text();
                                var service = $("#ServiceServiceId option:selected" ).text();
                                if((section=='<?php echo INPUT_SELECT; ?>') || (service=='<?php echo INPUT_SELECT; ?>') || ($("#ServiceUnitPrice" ).val()=='')){
                                    if(section=='<?php echo INPUT_SELECT; ?>'){$("#lblSection").show();}
                                    if(service=='<?php echo INPUT_SELECT; ?>'){$("#lblService").show();}
                                    if($("#ServiceUnitPrice" ).val()==''){$("#lblUnitPrice").show();}
                                    return false;
                                }else{
                                    addNewService();
                                    $(this).dialog("close");
                                }
                            }
                        }
                    });
                }
            });
        }
    }
    
    function addNewService(){
        // Service Information
        var serviceId           = $("#ServiceServiceId").val();
        var serviceName         = $("#ServiceServiceId").find("option:selected").html();
        var serviceCode         = $("#ServiceCode").val();
        var uomName             = $("#ServiceUomId").val();
        var uomId               = $("#ServiceUomSerId").val();
        var servicePrice        = $("#ServiceUnitPrice").val();
        // Get Row Index
        if($(".listTable:last").find(".editQty").attr("id") == undefined){
            posIndex = 1;
        }else{
            posIndex = parseInt($(".listTable:last").find(".editQty").attr("id").split("_")[1]) + 1;
        }
        // Add Product To List
        var rowAdd  = rowTablePOS.clone(true);
        var name    = serviceName;
        var code    = serviceCode;
        var photo   = "";
        var uomList = "<option value='"+uomId+"'>"+uomName+"</option>";
        rowAdd.removeAttr("style").removeAttr("id");
        rowAdd.find(".productCode").html(code);
        rowAdd.find(".productName").html(name);
        rowAdd.find(".tmpDetailId").val(0);
        rowAdd.find("input[name='data[SalesOrderDetail][product_name][]']").val(name);
        rowAdd.find("input[name='data[SalesOrderDetail][product_id][]']").val('');
        rowAdd.find("input[name='data[SalesOrderDetail][service_id][]']").val(serviceId);
        rowAdd.find("input[name='data[SalesOrderDetail][unit_price][]']").val(servicePrice);
        rowAdd.find("input[name='data[SalesOrderDetail][total_price][]']").val(servicePrice);
        rowAdd.find(".productInStock").val(1000000);
        rowAdd.find(".editQty").attr('id', 'editQty_'+posIndex).val(1);
        rowAdd.find(".qtyFree").attr('id', 'qtyFree_'+posIndex).val(0);
        rowAdd.find(".editUomQty").attr('id', 'editUomQty_'+posIndex);
        rowAdd.find(".unitPrice").text(servicePrice);
        rowAdd.find(".productTotalPrice").text(servicePrice);
        rowAdd.find(".lblDiscount").text('0.00');
        rowAdd.find(".removeTr").attr('id', 'removeTr_'+posIndex);
        rowAdd.attr('id', 'listTable_'+posIndex).attr('service_id',serviceId);
        $("#tblPOS").prepend(rowAdd);
        
        //Load Get Amount Item
        loadRowIndex();
        //Load Photo
        if(photo != ""){
            $(".photoProduct").attr("src", "<?php echo $this->webroot; ?>/public/product_photo/"+photo+"");
        }else{
            $(".photoProduct").attr("src", "<?php echo $this->webroot; ?>img/button/no-images.png");
        }
        // Set UOM List
        rowAdd.find(".editUomQty").html(uomList);                
        // Set UOM Conversion
        rowAdd.find("input[name='data[SalesOrderDetail][conversion][]']").val(1);
        // Clear Product After Add
        clearProduct();
        // Hide Loading
        $("#progress").hide();
        // Load Event Key
        eventKeyPOS();
        // Save Product
        saveProduct(rowAdd);
    }
    
    function resetFormPOS(){
        $("#PointOfSaleTotalAmountUs").val("0.00");
        $("#PointOfSaleTotalAmountKh").val(0);
        $("#PointOfSaleDiscountUs").val("0.00");
        $("#PointOfSaleDiscountPer").val(0);
        $("#PointOfSalePaidUs").val("0.00");
        $("#PointOfSalePaidKh").val(0);
        $("#PointOfSaleBalanceUs").val("0.00");
        $("#PointOfSaleBalanceKh").val(0);
        $("#PointOfSaleChangeUs").val("0.00");
        $("#PointOfSaleChangeKh").val(0);
        // Label Total
        $("#PointOfSaleSubTotalAmountUsDisplay").val('0.00');
        $("#PointOfSaleTotalVatDisplay").text('0.00');
        $("#PointOfSaleGrandTotalAmountUsDisplay").text('0.00');
        $("#PointOfSaleGrandTotalAmountKhDisplay").text('0.00');
        // Customer
        $("#customerPOSId").val('');
        $("#PointOfSaleCustomerNameLabel").text("General Customer");
        $("#PointOfSaleCustomerNameSearch").val('');
        // Product Photo
        $(".photoProduct").attr("src", "<?php echo $this->webroot; ?>img/button/no-images.png");
        // Total Item
        $(".amountItemProduct").text('0');
        // Reset Table Chart
        $("#tblPOS").html('');
        $(".totaPointItem").text(0);
        $("#totaPointItem").val(0);
        $("#btnVoidDisByCard").hide();
        $("#btnMembershipCardCode").show();
        addItemDisplay('0');
        calculateTotalQtyDisplay();
    }
    
    function clearProduct(){
        $("#PointOfSaleBarcode").removeAttr('readonly');
        $("#PointOfSaleBarcode").val("");
        if(checkExistingRecord() == false){
            $("#PointOfSaleTotalAmountUs").val("0.00");
            $("#PointOfSaleTotalAmountKh").val(0);
            $("#PointOfSaleDiscountUs").val("0.00");
            $("#PointOfSaleDiscountPer").val(0);
            $("#PointOfSalePaidUs").val("0.00");
            $("#PointOfSalePaidKh").val(0);
            $("#PointOfSaleBalanceUs").val("0.00");
            $("#PointOfSaleBalanceKh").val(0);
            $("#PointOfSaleChangeUs").val("0.00");
            $("#PointOfSaleChangeKh").val(0);
            // Label Total
            $("#PointOfSaleSubTotalAmountUsDisplay").val('0.00');
            $("#PointOfSaleTotalVatDisplay").text('0.00');
            $("#PointOfSaleGrandTotalAmountUsDisplay").text('0.00');
            $("#PointOfSaleGrandTotalAmountKhDisplay").text('0.00');
        }
        waitSearchCode(function(){
            $("#PointOfSaleBarcode").focus();
        }, 200, "Finish");
    }
    
    function checkProductPOS(productId, action, uom_id, qty,product_promo_id,promoDetailId,childNo){
        var result = productId;
        var access = 1;
        if(action == 2){
            var result = $.parseJSON(productId);
            if(result.InventoryTotal.total_qty <= 0){
                var pname = result.Product.barcode+" - "+result.Product.name;
                alertEmpty(pname);
                access = 2;
            }
        }
        if(access == 1){
            if(result.InventoryTotal.total_qty <= 0){
                var pname = result.Product.barcode+" - "+result.Product.name;
                alertEmpty(pname);
            }else{
                // Add Product to List
                addProduct(result, qty, uom_id,product_promo_id,promoDetailId,childNo);
            }
        }
    }
    
    function alertEmpty(productName){
        $("#dialog").html('<?php echo MESSAGE_OUT_OF_STOCK; ?>');
        $("#dialog").dialog({
            title: productName,
            resizable: false,
            closeOnEscape: false,
            modal: true,
            width: 300,
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    clearProduct();
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function alertInvalidCode(){
        $("#dialog").html('<?php echo MESSAGE_DATA_INVALID; ?>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            closeOnEscape: false,
            modal: true,
            width: 300,
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function addProduct(productAdd, qtyInput, uomSelected,product_promo_id,promoDetailId,childNo){
        var checkExist   = false;
        var qtyFreeInput = 0;
        if(product_promo_id>0){
            qtyFreeInput=qtyInput;
            qtyInput=0;
        }
        // Get Row Index
        posIndex = Math.floor((Math.random() * 100000) + 1);
        if(productAdd != ''){
            // Check Product Exist
            // $(".listTable").each(function(){
            //     if($(this).find("input[name='data[SalesOrderDetail][product_id][]']").val() == productAdd.Product.id && $(this).find(".editUomQty").find("option:selected").val() == uomSelected && ($(this).find(".expired_date").val() == '0000-00-00' || $(this).find(".expired_date").val() == '')){
            //         $(this).find(".addQtyMore").click();
            //         checkExist = true;
            //         return false;
            //     }
            // });
            if(checkExist == false){
                // Add Product To List
                var rowAdd  = rowTablePOS.clone(true);
                var name    = productAdd.Product.name;
                var code    = productAdd.Product.barcode;
                var photo   = productAdd.Product.photo;
                var uomList = productAdd.Product.uom_list;
                var isLots  = productAdd.Product.is_lots;
                var isExp   = productAdd.Product.is_expired_date;
                var price6  = productAdd.Product.price6;
                var price12 = productAdd.Product.price12;
                var promotion        = productAdd.Product.promo;
                var promoDisAmount   = 0;
                var promoDisPercent  = 0;
                var allowEditQtyFree = 1;
                if(promotion != ""){
                    $.each(promotion.promotion , function(i, val) { 
                        if(parseFloat(val.uom_id) == parseFloat(uomSelected)){
                            promoDisAmount  = replaceNum(val.discount_amount);
                            promoDisPercent = replaceNum(val.discount_percent);
                        }
                        rowAdd.find(".promotionDisUom").val(val.uom_id);
                        rowAdd.find(".promotionDisPer").val(val.discount_percent);
                        rowAdd.find(".promotionDisAmt").val(val.discount_amount);
                    });
                    rowAdd.find("input[name='data[SalesOrderDetail][promotional_id][]']").val(promotion.promotion_id);
                }
                rowAdd.removeAttr("style").removeAttr("id");
                rowAdd.find(".tmpDetailId").val(0);
                rowAdd.find("input[name='data[SalesOrderDetail][product_name][]']").val(name);
                rowAdd.find("input[name='data[SalesOrderDetail][product_id][]']").val(productAdd.Product.id);
                rowAdd.find("input[name='data[SalesOrderDetail][service_id][]']").val('');
                rowAdd.find("input[name='data[SalesOrderDetail][unit_price][]']").val(0);
                rowAdd.find("input[name='data[SalesOrderDetail][total_price][]']").val(0);
                rowAdd.find(".productInStock").val(productAdd.InventoryTotal.total_qty);
                rowAdd.find(".price6").val(price6);
                rowAdd.find(".price12").val(price12);
                rowAdd.find(".editPrice").attr('id', 'editPrice_'+posIndex);
                rowAdd.find(".editUomQty").attr('id', 'editUomQty_'+posIndex);
                rowAdd.find(".unitPrice").text('0.00');
                rowAdd.find(".productTotalPrice").text('0.00');
                rowAdd.find(".lblDiscount").text('0.00');
                rowAdd.find(".expired_date").attr('id', 'productPOSExp'+posIndex);
                rowAdd.find(".lots_number").attr('id', 'productPOSLots'+posIndex);
                if(isExp == 1){
                    rowAdd.find(".productExp").show();
                    rowAdd.find(".expired_date").addClass('validate[required]');
                }else{
                    rowAdd.find(".productExp").hide();
                    rowAdd.find(".expired_date").removeClass('validate[required]').val('0000-00-00');
                }
                if(isLots == 1){
                    rowAdd.find(".productLots").show();
                    rowAdd.find(".lots_number").addClass('validate[required]');
                }else{
                    rowAdd.find(".productLots").hide();
                    rowAdd.find(".lots_number").removeClass('validate[required]').val('0');
                }
                // Promotion Discount
                if(promoDisAmount > 0){
                    rowAdd.find("input[name='data[SalesOrderDetail][discount_amount][]']").val(promoDisAmount);
                }
                if(promoDisPercent > 0){
                    rowAdd.find("input[name='data[SalesOrderDetail][discount_percent][]']").val(promoDisPercent);
                }
                if(promoDisPercent > 0 || promoDisAmount > 0){
                    rowAdd.find(".lblDiscount").removeClass("editDiscount");
                }
                if(product_promo_id>0){
                    rowAdd.find(".editQty").attr('id', 'editQty_'+posIndex).val(qtyInput).attr("readonly",true);
                    rowAdd.find(".qtyFree").attr('id', 'qtyFree_'+posIndex).val(qtyFreeInput).attr("readonly",true);
                    rowAdd.find(".removeTr").attr('id', 'removeTr_'+posIndex).hide();
                    rowAdd.find(".delQtyMore").attr('id', 'delQtyMore_'+posIndex).hide();
                    rowAdd.find(".addQtyMore").attr('id', 'addQtyMore_'+posIndex).hide();
                    rowAdd.find(".productCode").html(code+'<b style="color:green;">(Promotion)</b>');
                    rowAdd.attr('id','listTable_'+promoDetailId).attr('parent_id',product_promo_id);
                    rowAdd.find("td").css("background-color", "#ebf5fb");

                    $("#tblPOS tbody tr:nth-child("+childNo+")").after(rowAdd);
                }else{
                    rowAdd.find(".editQty").attr('id', 'editQty_'+posIndex).val(qtyInput);
                    rowAdd.find(".qtyFree").attr('id', 'qtyFree_'+posIndex).val(qtyFreeInput);
                    rowAdd.find(".productCode").html(code);
                    rowAdd.attr('id', 'listTable_'+posIndex).attr('product_id',productAdd.Product.id);
                    rowAdd.find(".removeTr").attr('id', 'removeTr_'+posIndex);

                    $("#tblPOS").prepend(rowAdd);
                    checkQtyPromotional(rowAdd,1,0);
                }
                //Load Get Amount Item
                loadRowIndex();
                //Load Photo
                if(photo != "" && photo != null){
                    $(".photoProduct").attr("src", "<?php echo $this->webroot; ?>public/product_photo/"+photo+"");
                }else{
                    $(".photoProduct").attr("src", "<?php echo $this->webroot; ?>img/button/no-images.png");
                }
                // Set UOM List
                rowAdd.find(".editUomQty").html(uomList);
                // Filter Uom By SKU
                rowAdd.find(".editUomQty").find("option").each(function(){
                    // if(parseInt(uomSelected) > 0){
                    //     if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                    //         $(this).attr("selected", true);
                    //     }
                    // }else{
                    //     if($(this).attr("data-item")=='first'){
                    //         $(this).attr("selected", true);
                    //     }
                    // }
                    if($(this).attr("uom-sm")==1){
                        $(this).attr("selected", true);
                    }
                });
                var value         = replaceNum(rowAdd.find(".editUomQty").find("option:selected").val());
                var uomConversion = replaceNum(rowAdd.find(".editUomQty").find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum(productAdd.Product.small_val_uom);
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                var unitPrice     = (parseFloat(rowAdd.find(".editUomQty").find("option:selected").attr("price"))).toFixed(2);
                var totalPrice    = (converDicemalJS(parseFloat(replaceNum(unitPrice)) * parseFloat(replaceNum(qtyInput)))).toFixed(2);
                // Set UOM Conversion
                rowAdd.find("input[name='data[SalesOrderDetail][conversion][]']").val(conversion);
                // Set Price
                rowAdd.find("input[name='data[SalesOrderDetail][unit_price][]']").val(unitPrice);
                rowAdd.find(".unitPrice").html(unitPrice);
                rowAdd.find("input[name='data[SalesOrderDetail][total_price][]']").val(totalPrice);
                rowAdd.find(".productTotalPrice").html(totalPrice);
                // Display Name
                rowAdd.find(".productName").html(name+"/"+conversion);
                // Clear Product After Add
                clearProduct();
                // Hide Loading
                $("#progress").hide();
                // Load Event Key
                eventKeyPOS();
                // Save Product
                saveProduct(rowAdd);
                addItemDisplay('0');
            } else {
                // Clear Product After Add
                clearProduct();
                // Hide Loading
                $("#progress").hide();
            }
        }
    }

    function checkQtyPromotional(obj,childNo,is_discount){
        var totalQtyFree   = 0;
        var productId      = obj.find("input[name='data[SalesOrderDetail][product_id][]']").val();
        var qty            = replaceNum(obj.find(".editQty").val());
        var qtyFree        = replaceNum(obj.find(".qtyFree").val());
        var branchId       = $("#PointOfSaleBranchId").val();
        var indexRand      = obj.closest("tr").attr('id').split("_")[1];
        if(productId != "" && branchId != ""){
            $.ajax({
                dataType:'json',
                type:'POST',
                url:"<?php echo $this->base . '/' . $this->params['controller']; ?>/checkPromoType/"+branchId,
                data:'',
                beforeSend: function(){
                    $("#progress").show();
                },
                success: function(result){
                    console.log(result);
                    $("#progress").hide();
                    var promotion_type_id = result.promotype.split(",");
                    for(var i=0;i<promotion_type_id.length;i++){
                        var promoTypeId = promotion_type_id[i];
                        $.ajax({
                            dataType:'json',
                            type:'POST',
                            url:"<?php echo $this->base . '/' . $this->params['controller']; ?>/checkQtyPromotional/"+branchId+'/'+productId+"/"+promoTypeId,
                            data:'',
                            beforeSend: function(){
                                $("#progress").show();
                            },
                            success: function(promotional){
                                $("#progress").hide();
                                var promotion_id       = promotional.promotion_id;
                                var promotion_type_id  = promotional.promotion_type_id;
                                if(promotion_id != "" && promotion_id>0){
                                    $.each(promotional.promotion , function(i, val) {
                                        var promoDetailId      = val.promo_detail_id;
                                        //Product request information
                                        var product_request_id = val.product_request_id;
                                        var product_promo_id   = val.product_promo_id;
                                        var qty_request        = val.qty_request;
                                        //Product promotion information
                                        var qty_promo          = val.qty_promo;
                                        var uom_promo          = val.uom_promo;
                                        var product_code_promo = val.product_code_promo;
                                        var discount_percent   = replaceNum(val.discount_percent).toFixed(2);
                                        var unit_price  	   = replaceNum(val.unit_price).toFixed(2);
                                        if(promotion_type_id==1){// buy and free the same/difference product
                                            (function(i){
                                                setTimeout(function(){
                                                    if(val.product_request_id==productId){
                                                        if(val.product_request_id==val.product_promo_id){// for product request and product promotion are the same.
                                                            if(qty>=qty_request){
                                                                totalQtyFree = replaceNum(qty*qty_promo/qty_request);
                                                                obj.find("input[name='data[SalesOrderDetail][qty_free][]']").val(replaceNum(totalQtyFree).toFixed(0)).attr("readonly",true);
                                                            }else{
                                                                obj.find("input[name='data[SalesOrderDetail][qty_free][]']").val(replaceNum(0).toFixed(0));
                                                            }
                                                        }else{// for product reqeust and promotion are difference.
                                                            $(".listTable").each(function(i){
                                                                var id = $(this).attr('id').split("_")[1];
                                                                if(promoDetailId==id){
                                                                    $(".listTable[id='listTable_" + id + "']").closest('tr').remove();
                                                                }
                                                            });
                                                            if(is_discount==0){
                                                                if(qty>=qty_request){
                                                                    totalQtyFree = replaceNum(qty*qty_promo/qty_request).toFixed(0);
                                                                    getProductByCode(product_code_promo, uom_promo, totalQtyFree,product_promo_id,promoDetailId,childNo);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }, 500);
                                            })(i);
                                        }
                                        if(promotion_type_id==2){// buy >= 100$ get one product free
                                            (function(i){
                                                setTimeout(function(){
                                                    var totalAmount = replaceNum($("#PointOfSaleGrandTotalAmountUsDisplay").text()).toFixed(2);
                                                    $(".listTable").each(function(i){
                                                        var id = $(this).attr('id').split("_")[1];
                                                        if(promoDetailId==id){
                                                            $(".listTable[id='listTable_" + id + "']").closest('tr').remove();
                                                        }
                                                    });
                                                    if(replaceNum(totalAmount)>=replaceNum(unit_price)){
                                                        var LenTr    = parseInt($(".listTable").length);// add on last row for item free
                                                        totalQtyFree = replaceNum(qty_promo).toFixed(0);
                                                        getProductByCode(product_code_promo, uom_promo, totalQtyFree,product_promo_id,promoDetailId,LenTr);
                                                    }
                                                }, 500);
                                            })(i);
                                        }
                                        if(promotion_type_id==3){// buy >= 100$ get discount % by menual input
                                            (function(i){
                                                setTimeout(function(){
                                                    var totalAmount = replaceNum($("#PointOfSaleGrandTotalAmountUsDisplay").text()).toFixed(2);
                                                    if(replaceNum(totalAmount)>=replaceNum(unit_price)){
                                                        $("#PointOfSaleDiscountPer").val(discount_percent).attr("readonly",true);
                                                        $("#PointOfSaleDiscountUs").val(replaceNum(0).toFixed(2)).attr("readonly",true);
                                                    }else{
                                                        $("#PointOfSaleDiscountPer,#PointOfSaleDiscountUs").val(replaceNum(0).toFixed(2)).removeAttr("readonly");
                                                    }
                                                    getTotalAmount();
                                                }, 500);
                                            })(i);
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            });
        }
    }
    
    function eventKeyPOS(){
        //Event Key Action On POS
        $(".removeTr, .editPrice, .editQty, .qtyFree, .lots_number, .editUomQty, .editDiscount, .btnRemoveDiscount, .addQtyMore, .delQtyMore").unbind("click").unbind("keyup").unbind("keypress").unbind("change").unbind("blur").unbind("focus");
        $(".float").autoNumeric({mDec: 3});
        $(".integer").autoNumeric({mNum: 9,mDec:0});

        // Action Qty & Edit Price Focus
        $(".editQty, .qtyFree, .editPrice").focus(function(){
            var value = replaceNum($(this).val());
            if(value == 0){
                $(this).val('');
            }
        });

        // Action Qty & Discount when blur
        $(".editQty, .qtyFree").blur(function(){
            var val = $(this).val();
            if(val == ''){
                $(this).val(0);
            }
            if($(this).closest("tr").find("input[name='data[SalesOrderDetail][promotional_id][]']").val() != ""){ // Check Promotion
                var unitPrice = replaceNum($(this).closest("tr").find(".editUomQty").find("option:selected").attr("price"));
                $(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val(unitPrice);
                $(this).closest("tr").find(".unitPrice").text(unitPrice);
            } else {
                if(replaceNum($(this).closest("tr").find(".price6").val()) > 0 || replaceNum($(this).closest("tr").find(".price12").val()) > 0){
                    if(replaceNum($(this).closest("tr").find(".editQty").val()) >= 6 && replaceNum($(this).closest("tr").find(".editQty").val()) < 12){
                        if(replaceNum($(this).closest("tr").find(".price6").val()) > 0){
                            var price6 = replaceNum($(this).closest("tr").find(".price6").val());
                            $(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val(price6);
                            $(this).closest("tr").find(".unitPrice").text(price6);
                        }
                    } else if (replaceNum($(this).closest("tr").find(".editQty").val()) >= 12){
                        if(replaceNum($(this).closest("tr").find(".price12").val()) > 0){
                            var price12 = replaceNum($(this).closest("tr").find(".price12").val());
                            $(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val(price12);
                            $(this).closest("tr").find(".unitPrice").text(price12);
                        }
                    } else {
                        var unitPrice = replaceNum($(this).closest("tr").find(".editUomQty").find("option:selected").attr("price"));
                        $(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val(unitPrice);
                        $(this).closest("tr").find(".unitPrice").text(unitPrice);
                    }
                }
            }
            var childNo   = 0;
            var indexRand = $(this).closest("tr").attr('id').split("_")[1];
            $(".listTable").each(function(i){
                var id = $(this).attr('id').split("_")[1];
                if(indexRand==id){
                    childNo = i;
                }
            });
            checkQtyPromotional($(this).closest("tr"),replaceNum(childNo + 1),0);
            saveProduct($(this).closest("tr"));
        });
        
        $(".editPrice").blur(function(){
            var childNo   = 0;
            var indexRand = $(this).closest("tr").attr('id').split("_")[1];
            $(".listTable").each(function(i){
                var id = $(this).attr('id').split("_")[1];
                if(indexRand==id){
                    childNo = i;
                }
            });
            checkQtyPromotional($(this).closest("tr"),replaceNum(childNo + 1),0);
            saveProduct($(this).closest("tr"));
        });

        // Discount By Item
        <?php
//      if($allowProductDiscount){
        ?>
        $(".editDiscount").click(function(){
            var totalPrice = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][total_price][]']").val());
            if(totalPrice > 0){
                if($(this).closest("tr").find("input[name='data[SalesOrderDetail][promotional_id][]']").val() == ""){
                    if(itemDisPermission == '1'){
                        $("#PointOfSaleBarcode").select().focus();
                        addNewDiscountPOS($(this).closest("tr"));
                    } else {
                        changePermissionLogin($(this).closest("tr"), 'itempos');
                    }
                }
            }
        });
        <?php
//      }
        ?>
        // Add Qty More
        $(".addQtyMore").unbind("click").click(function(){
            var qtyCurrent    = replaceNum($(this).closest("tr").find(".editQty").val());
            var qtyCurrentNew = 0;
            if(qtyCurrent >= 0){
                qtyCurrentNew = qtyCurrent + 1;
            }
            $(this).closest("tr").find(".editQty").val(qtyCurrentNew);
            $(this).closest("tr").find(".editQty").select().blur();
            saveProduct($(this).closest("tr"));
        });

        // Delete Qty More
        $(".delQtyMore").unbind("click").click(function(){
            var qtyCurrent    = replaceNum($(this).closest("tr").find(".editQty").val());
            var qtyCurrentNew = 0;
            if(qtyCurrent > 0){
                qtyCurrentNew = qtyCurrent - 1;
            }
            $(this).closest("tr").find(".editQty").val(qtyCurrentNew);
            $(this).closest("tr").find(".editQty").select().blur();
            saveProduct($(this).closest("tr"));
        });

        // Action Qty Free Focus
        $(".qtyFree").focus(function(){
            var value = $(this).val();
            if(value == 0){
                $(this).val('');
            }
        });

        //Change Uom
        $(".editUomQty").change(function(){   
            var obj       = $(this).closest("tr");
            var productId = obj.find("input[name='data[SalesOrderDetail][product_id][]']").val();
            var decimalMain  = checkCurrencyDecimal($("#PointOfSaleCompanyId").find("option:selected").attr("currency"));
            var sku = $(this).find("option:selected").attr("sku");
            if(productId != "" && productId > 0){
                var qty           = replaceNum(obj.find(".editQty").val());
                var qtyFree       = parseFloat(replaceNum($(this).closest("tr").find(".qtyFree").val()));
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum($(this).find("option[uom-sm='1']").attr("conversion"));
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                var qtyOrder      = (qty + qtyFree) * conversion;
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price"));
                obj.find(".unitPrice").text(convertToSeparator(unitPrice, decimalMain));
                obj.find("input[name='data[SalesOrderDetail][unit_price][]']").val(unitPrice);
                obj.find("input[name='data[SalesOrderDetail][conversion][]']").val(conversion);
                obj.find("input[name='data[SalesOrderDetail][qty_order][]']").val(qtyOrder);
                obj.find(".productCode").text(sku);
                // Name
                var nameDef = obj.find("input[name='data[SalesOrderDetail][product_name][]']").val();
                var nameCon = nameDef+"/"+conversion;
                obj.find(".productName").text(nameCon);
                saveProduct(obj);
            }
        });

        // Delete Row
        $(".removeTr").click(function(){
            var obj = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: '300',
                height: 'auto',
                position:'center',
                closeOnEscape: true,
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        // Remove Item Free
                        $(this).dialog("close");
                        var childNo   = 0;
                        var indexRand = obj.attr('id').split("_")[1];
                        $(".listTable").each(function(i){
                            var id = $(this).attr('id').split("_")[1];
                            if(indexRand==id){
                                childNo = i;
                            }
                        });
                        checkQtyPromotional(obj,replaceNum(childNo + 1),1);

                        var productId = obj.find("input[name='data[SalesOrderDetail][product_id][]']").val();
                        var qty       = obj.find('.editQty').val();
                        var uomId     = obj.find('.editUomQty').find('option:selected').val();
                        saveItemVoid(productId, qty, uomId);
                        removeRowProduct(obj);
                    }
                }
            });
        });
        
        // Remove Discount
        $(".btnRemoveDiscount").click(function(){
            var obj = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove discount?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: '300',
                height: 'auto',
                position:'center',
                closeOnEscape: true,
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        obj.find("input[name='data[SalesOrderDetail][discount_id][]']").val('');
                        obj.find("input[name='data[SalesOrderDetail][discount_percent][]']").val('0');
                        obj.find("input[name='data[SalesOrderDetail][discount_amount][]']").val('0');
                        obj.find(".lblDiscount").text('0.00');
                        obj.find(".btnRemoveDiscount").hide();
                        saveProduct(obj);
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $('.expired_date').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 2000);
                }, 10);
            }
        }).unbind("blur");
        
        // Action Expiry Date Change
        $(".expired_date").change(function(){
             getTotalQtyLotExp($(this));
        });
        
        // Action Lots Number blur
        $(".lots_number").blur(function(){
             getTotalQtyLotExp($(this));
        }); 
    }

    function getProductPromoPoint(startDate,endDate,branchId){
        $.ajax({
            dataType:'json',
            type:'POST',
            url:"<?php echo $this->base . '/' . $this->params['controller']; ?>/getProductPromoPoint/",
            data:'start='+startDate+'&end='+endDate+'&branch_id='+branchId,
            beforeSend: function(){
                $("#progress").show();
            },
            success: function(promotionalPoint){
                $("#progress").hide();
                var totalAmount     = 0;
                var qty             = 0;
                var unit_price      = 0;
                var promotion_id    = promotionalPoint.promotion_id;
                var total_point     = promotionalPoint.total_point;
                var pointInDollar   = promotionalPoint.point_in_dollar;
                if(promotion_id != ""){
                    $.each(promotionalPoint.product , function(i, val) {
                        if(val.product_id==''){
                            $("input[name='data[SalesOrderDetail][total_price][]']").each(function(){
                                var qty        = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][qty][]']").val());
                                var unit_price = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val());
                                totalAmount   += converDicemalJS(unit_price * qty);
                                totalAmount   -= replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][discount_amount][]']").val());
                            });
                        }else{
                            $("input[name='data[SalesOrderDetail][total_price][]']").each(function(){
                                var productId = $(this).closest("tr").find("input[name='data[SalesOrderDetail][product_id][]']").val();
                                if(productId==val.product_id){
                                    var qty        = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][qty][]']").val());
                                    var unit_price = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val());
                                    totalAmount   += converDicemalJS(unit_price * qty);
                                    totalAmount   -= replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][discount_amount][]']").val());
                                }
                            });
                        }
                    });
                    grandTotalPoint = replaceNum(total_point*totalAmount/pointInDollar);
                }
                $(".totaPointItem").text(grandTotalPoint);
                $("#totaPointItem").val(grandTotalPoint);
                $("#PointOfSalePromotionPointId").val(promotion_id);
                $("#PromoPointTotalPointSetting").val(total_point);
            }
        });
    }
    
    function changePermissionLogin(obj, module){
        $("#frmLoginPermission").dialog({
            title: '<?php echo USER_LOGIN_INFO; ?>',
            resizable: false,
            modal: true,
            width: 400,
            height: 230,
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                },
                '<?php echo ACTION_LOGIN; ?>': function() {
                    var user = $("#loginPermissionUsername").val();
                    var pwd  = $("#loginPermissionPassword").val();
                    if(user != '' && pwd != ''){
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "<?php echo $this->base . '/'; ?>users/checkDisPermission/"+module,
                            data: "username="+user+"&password="+pwd,
                            beforeSend: function(){
                                $("#loginPermissionUsername, #loginPermissionPassword").val('');
                                $("#frmLoginPermission").dialog("close");
                                // modal box - open
                                $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                                $("#dialogModal").dialog({
                                    title: '<?php echo DIALOG_LOADING; ?>',
                                    resizable: false,
                                    modal: true,
                                    closeOnEscape: false,
                                    width: 200,
                                    height: 100,
                                    open: function(event, ui){
                                        $(".ui-dialog-buttonpane").show();
                                        $(".ui-dialog-titlebar-close").hide();
                                    },
                                    close: function(event, ui){
                                        $(".ui-dialog-titlebar-close").show();
                                    }
                                });
                            },
                            success: function(result){
                                // modal box - close
                                $("#dialogModal").dialog("close");
                                if(result.access == '1'){
                                    if(module == 'itempos'){
                                        itemDisPermission = '1';
                                        addNewDiscountPOS(obj);
                                    } else if(module == 'invpos'){
                                        invDisPermission = '1';
                                        obj.focus();
                                    }
                                } else {
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>No Permission</p>');
                                    $("#dialog").dialog({
                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                        resizable: false,
                                        modal: true,
                                        width: 'auto',
                                        height: 'auto',
                                        open: function(event, ui){
                                            $(".ui-dialog-buttonpane").show();
                                        },
                                        buttons: {
                                            '<?php echo ACTION_CLOSE; ?>': function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
        });
    }
    
    function changeLocation(){
        var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_LOCATION; ?>";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            close: function(event, ui){
                refreshBrowser();
            },
            buttons: {
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                },
                '<?php echo ACTION_OK; ?>': function() {
                    $.cookie("location_id", $("#PointOfSaleLocationGroupId").val(), { expires : 5, path    : '/'});
                    location.reload();
                    $(this).dialog("close");
                }
            }
        });
    }

    function changeCompany() {
        var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_COMPANY; ?>";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>' + question + '</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position: 'center',
            open: function(event, ui) {
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            },
            close: function(event, ui) {
                $("#PointOfSaleCompanyId").val($.cookie('companyId'));
            },
            buttons: {
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                },
                '<?php echo ACTION_OK; ?>': function() {
                    $.cookie('companyId', $("#PointOfSaleCompanyId").val(), {expires: 5, path: '/'});
                    window.location.reload();
                    $(this).dialog("close");
                }
            }
        });
    }

    function checkExistingRecord(){
        var isFound = false;
        $("#tblPOS").find("tr").each(function(){
            if($(this).find("input[name='data[SalesOrderDetail][product_id][]']").val() != "" || $(this).find("input[name='data[SalesOrderDetail][service_id][]']").val() != ""){
                isFound = true;
            }
        });
        return isFound;
    }

    function removeRowProduct(currentTr){
        if(currentTr.find("input[name='data[SalesOrderDetail][qty][]']").val() != undefined){
            currentTr.remove();
            getTotalAmount();
            calculateBalance();
        }
        loadRowIndex();
    }
    
    function loadRowIndex(){
        var index = 0;
        $(".listTable").each(function(){
            index++;
        });
        //Get Amount Item Product
        if(index < 0){
            index = 0;
        }
        $(".amountItemProduct").text(replaceNum(index));
    }
    
    // Save Produuct
    function saveProduct(obj){
        var productId      = obj.find("input[name='data[SalesOrderDetail][product_id][]']").val();
        var qty            = replaceNum(obj.find(".editQty").val());
        var qtyFree        = replaceNum(obj.find(".qtyFree").val());
        var productExp     = obj.find(".expired_date").val();
        var totalOrder     = replaceNum(getTotalProductOrder(productId, productExp));
        var qtyInStock     = replaceNum(obj.find(".productInStock").val());
        var uomConversion  = replaceNum(obj.find(".editUomQty").find("option:selected").attr('conversion'));
        var uomSmall       = replaceNum(obj.find(".editUomQty").find("option[uom-sm='1']").attr("conversion"));
        var uomSelected    = replaceNum(obj.find(".editUomQty").find("option:selected").val());
        var conversion     = converDicemalJS(uomSmall / uomConversion);
        var qtyOrder       = converDicemalJS((qty+ qtyFree) * conversion);
        var discount       = 0;
        var discountPer    = 0;
        var unitPrice      = replaceNum(obj.find("input[name='data[SalesOrderDetail][unit_price][]']").val());
        var totalPrice     = 0;
        var totalPriceLbl  = 0;
        var checkStock     = true;
        var promotionId     = replaceNum(obj.find("input[name='data[SalesOrderDetail][promotional_id][]']").val());
        var promotionUomId  = replaceNum(obj.find(".promotionDisUom").val());
        var promotionDisPer = replaceNum(obj.find(".promotionDisPer").val());
        var promotionDisAMt = replaceNum(obj.find(".promotionDisAmt").val());
        if(productId != ''){
            if(totalOrder > qtyInStock){
                checkStock = false;
                qty = 0;
                qtyFree = 0;
                totalOrder = 0;
                $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_OUT_OF_STOCK; ?></p>').dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: '200',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            obj.find(".editQty").select().focus();
                            $(this).dialog("close");
                        }
                    }
                });
            }
        }
        if(checkStock ==  true){
            // Check Discount Amount AS promotion
            if(promotionId > 0){
                if(uomSelected == promotionUomId){
                    if(promotionDisPer > 0){
                        obj.find("input[name='data[SalesOrderDetail][discount_percent][]']").val(promotionDisPer);
                        discount   = converDicemalJS((converDicemalJS(qty * unitPrice) * promotionDisPer) / 100);
                    } else {
                        var promotionDis = converDicemalJS(promotionDisAMt * qty);
                        discount = converDicemalJS(promotionDis).toFixed(2);
                    }
                }
            } else {
                discountPer    = replaceNum(obj.find("input[name='data[SalesOrderDetail][discount_percent][]']").val());
                if(discountPer > 0){
                    discount   = converDicemalJS((converDicemalJS(qty * unitPrice) * discountPer) / 100);
                } else {
                    discount = replaceNum(converDicemalJS(obj.find("input[name='data[SalesOrderDetail][discount_amount][]']").val()).toFixed(2));
                }
            }
            totalPrice     = converDicemalJS(qty * unitPrice);
            totalPriceLbl  = converDicemalJS(qty * unitPrice) - discount;
        }
        obj.find("input[name='data[SalesOrderDetail][qty][]']").val(qty);
        obj.find("input[name='data[SalesOrderDetail][qty_free][]']").val(qtyFree);
        obj.find("input[name='data[SalesOrderDetail][qty_order][]']").val(qtyOrder);
        obj.find("input[name='data[SalesOrderDetail][unit_price][]']").val(unitPrice);
        obj.find("input[name='data[SalesOrderDetail][total_price][]']").val(totalPrice);
        obj.find("input[name='data[SalesOrderDetail][discount_amount][]']").val(discount);
        obj.find(".lblDiscount").text(converDicemalJS(discount).toFixed(2));
        // Label Total Amount
        obj.find(".productTotalPrice").html((totalPriceLbl).toFixed(2));
        getTotalAmount();
    }
    
    function converDicemalJS(value){
        return Math.round(parseFloat(value) * 1000000000000)/1000000000000;
    }
    
    function getProductByCode(barcode, uom_id, qty,product_promo_id,promoDetailId,childNo){	
        var locationId   = parseFloat($("#PointOfSaleLocationGroupId").val());
        var companyId    = parseFloat($("#PointOfSaleCompanyId").val());
        var branchId     = parseFloat($("#PointOfSaleBranchId").val());
        var priceTypeId  = parseFloat($("#PointOfSaleCompanyId").find("option:selected").attr("ptype"));
        if(barcode != '' && locationId > 0 && companyId > 0 && branchId > 0 && priceTypeId > 0){
            $.ajax({
                type:   'POST',
                dataType: 'json',
                url:    "<?php echo $this->base.'/'.$this->params['controller']; ?>/getProductByCode/"+barcode+"/"+locationId+"/"+companyId+"/"+branchId+"/"+priceTypeId,
                beforeSend: function(){
                    isBort = 1;
                    ajaxConnection.abort();
                    $("#PointOfSaleBarcode").attr('readonly', 'readonly');
                    $("#progress").show();
                },
                error: function (result) {
                    isBort = 0;
                    waitForFinalConectionPOS(function(){
                        // Recheck Conection
                        checkConnectionPOS();
                    }, 5000, "Finish");
                    $("#progress").hide();
                    createSysAct('POS', 'Scan Code', 2, result.responseText);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONNECTION_LOSE; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                },
                success: function(result){
                    isBort = 0;
                    waitForFinalConectionPOS(function(){
                        // Recheck Conection
                        checkConnectionPOS();
                    }, 5000, "Finish");
                    $("#progress").hide();
                    if(result.Product.id != ''){
                        if(result.Product.is_packet == 0){
                            if(result.InventoryTotal.total_qty > 0){
                                if(uom_id == ''){
                                    uom_id = result.Product.uom_id;
                                }
                                checkProductPOS(result, 1, uom_id, qty,product_promo_id,promoDetailId,childNo);
                            } else {
                                var pname = result.Product.barcode+" - "+result.Product.name;
                                alertEmpty(pname);
                            }
                        } else {
                            // Get Packet
                            var packet  = result.Product.packet;
                            var packets = packet.toString().split(",");
                            var time    = 0;
                            var loop    = 1;
                            $.each(packets,function(key, item){
                                if(loop > 0){
                                    time  += 300;
                                }
                                var items = item.toString().split("||");
                                var productCode = items[0];
                                var uom_id      = items[1];
                                var qty         = items[2];
                                var qtyOrder    = parseFloat(qty);
                                setTimeout(function () {
                                    getProductByCode(productCode, uom_id, qtyOrder,product_promo_id,promoDetailId,childNo);
                                }, time);
                                loop++;
                            });
                        }
                    }else if(result.Product.id == ''){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left;"></span>"<?php echo MESSAGE_DATA_INVALID; ?>"</p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            closeOnEscape: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            position:'center',
                            open: function(event, ui){
                                $(".ui-dialog-titlebar-close").hide();
                                $(".ui-dialog-buttonpane").show();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    clearProduct();
                                    $(".ui-dialog-titlebar-close").show();
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }
                }
            });
        }
    }
    
    function addNewDiscountPOS(tr){
        if($("#PointOfSaleCompanyId").val() != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/point_of_sales/discountByItem"; ?>",
                beforeSend: function(){
                    $("#progress").show();
                },
                error: function (result) {
                    $("#progress").hide();
                    createSysAct('POS', 'Discount Item', 2, result.responseText);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONNECTION_LOSE; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                },
                success: function(msg){
                    $("#progress").hide();
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_SELECT_DISCOUNT; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); 
                            $(".ui-dialog-titlebar-close").show();
                            $("#progress").hide();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {    
                                if($("#inputInvoiceDisAmt").val() > 0 || $("#inputInvoiceDisPer").val() > 0){
                                    var discountAmount  = replaceNum($("#inputInvoiceDisAmt").val());
                                    var discountPercent = replaceNum($("#inputInvoiceDisPer").val());
                                    var unitPrice       = replaceNum(tr.find("input[name='data[SalesOrderDetail][unit_price][]']").val());
                                    var qty             = replaceNum(replaceNum(tr.find(".editQty").val()));
                                    var totalPrice      = converDicemalJS(unitPrice * qty);
                                    var amount          = 0;
                                    if(discountAmount > 0){
                                        amount = discountAmount;
                                    }else{
                                        amount = converDicemalJS(converDicemalJS(totalPrice * discountPercent) / 100);
                                    }
                                    tr.find("input[name='data[SalesOrderDetail][discount_id][]']").val(0);
                                    tr.find("input[name='data[SalesOrderDetail][discount_percent][]']").val(discountPercent);
                                    tr.find("input[name='data[SalesOrderDetail][discount_amount][]']").val(amount);
                                    tr.find(".lblDiscount").text(amount.toFixed(2));
                                    tr.find(".btnRemoveDiscount").show();
                                    saveProduct(tr);
                                }
                                $(this).dialog("close");
                                $("#PointOfSaleBarcode").select().focus();
                            }
                        }
                    });
                }
            });
        }
    }
        
    function getTotalProductOrder(productId, productExp){
        var totalProduct=0;
        if(productExp == ''){
            productExp = '0000-00-00';
        }
        $("input[name='data[SalesOrderDetail][product_id][]']").each(function(){
            var exp = $(this).closest("tr").find(".expired_date").val();
            if(productId == $(this).val() && exp == productExp){
                var qty      = replaceNum($(this).closest("tr").find(".editQty").val());
                var qtyFree  = replaceNum($(this).closest("tr").find(".qtyFree").val());
                var uomConversion  = replaceNum($(this).closest("tr").find(".editUomQty").find("option:selected").attr('conversion'));
                var uomSmall       = replaceNum($(this).closest("tr").find(".editUomQty").find("option[uom-sm='1']").attr("conversion"));
                var conversion     = converDicemalJS(uomSmall / uomConversion);
                var qtyOrder       = converDicemalJS((qty+ qtyFree) * conversion);
                $(this).closest("tr").find("input[name='data[SalesOrderDetail][qty_order][]']").val(qtyOrder);
                totalProduct += replaceNum(qtyOrder);
            }
        });
        return totalProduct;
    }

    function calculateTotalQtyDisplay(){
        var totalOrder = 0;
        $("input[name='data[SalesOrderDetail][product_id][]']").each(function(){
            productExp = $(this).closest("tr").find(".expired_date").val();
            productId  = $(this).val();
            totalOrder += replaceNum(getTotalProductOrder(productId, productExp));
        });
        $(".totalQtyItem").text(totalOrder);
        if(totalOrder<=0){
            resetVoidMembership();
        }
    }
    
    function getTotalAmount(){
        var totalAmount      = 0;
        var totalAmountKh    = 0;
        var amount           = 0;
        var exchangeRate     = replaceNum($("#PointOfSaleBranchId").find("option:selected").attr("rate"));
        var otherCurrencyId  = replaceNum($("#PointOfSaleBranchId").find("option:selected").attr("currency"));
        var totalDiscount    = 0;
        var discountUs       = replaceNum($("#PointOfSaleDiscountUs").val());
        var discountPer      = replaceNum($("#PointOfSaleDiscountPer").val());
        var vatCal           = $("#PointOfSaleVatCalculate").val();
        var totalVatPer      = replaceNum($("#PointOfSaleVatPercent").find("option:selected").attr("rate"));
        var totalVat         = 0;
        var otherDecimal     = 2;

        var PromoPoinStart   = $("#PromoPoinStart").val();
        var PromoPoinEnd     = $("#PromoPoinEnd").val();
        var branchId         = $("#PointOfSaleBranchId").find("option:selected").val();
        var cardId           = $("#MembershipCardId").val();
        memoRequired         = false;
        $("input[name='data[SalesOrderDetail][total_price][]']").each(function(){
            var qty        = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][qty][]']").val());
            var unit_price = replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][unit_price][]']").val());
            totalAmount   += converDicemalJS(unit_price * qty);
            totalAmount   -= replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][discount_amount][]']").val());
            if(replaceNum($(this).closest("tr").find("input[name='data[SalesOrderDetail][discount_amount][]']").val()) > 0){
                memoRequired = true;
            }
        });
        if(discountPer > 0){                
            totalDiscount = replaceNum(converDicemalJS((discountPer * totalAmount) / 100).toFixed(2));
        } else {
            if(totalAmount >= discountUs){
                totalDiscount = discountUs;
            } else {
                totalDiscount = 0;
            }
        }

        // Start membership apply point
        if(cardId != "" && replaceNum($("#MembershipTotalPoint").val())>0){
            var rewardAmount        = $("#rewardAmountApply").val();
            var totalPoint          = $("#MembershipTotalPoint").val();
            var pointInDollar       = $("#MembershipPointInDollar").val();
            var exchangePoint       = $("#MembershipExchangePoint").val();
            var remainAmountMemShip = 0;
            // var totalDiscount       = replaceNum(totalPoint)*replaceNum(pointInDollar)/replaceNum(exchangePoint);
            var totalDiscount       = rewardAmount;
            if(totalDiscount>totalAmount){
                remainAmountMemShip = replaceNum(totalDiscount-totalAmount);
                totalDiscount       = totalAmount;
            }
            rewardAmountAsDollar    = replaceNum(rewardAmount)*replaceNum(exchangePoint)/replaceNum(pointInDollar);
            remainPoint             = totalPoint-rewardAmountAsDollar;
            // remainPoint = replaceNum(remainAmountMemShip)*replaceNum(exchangePoint)/replaceNum(pointInDollar);
            $("#exchangePointInDollar").val(replaceNum(totalDiscount).toFixed(2));
            $("#remainPointMemShip").val(replaceNum(remainPoint).toFixed(2));
            $("#PointOfSaleDiscountUs").attr("readonly",true);
        }
        // End membership apply point

        if(vatCal == 1){
            totalVat = replaceNum(converDicemalJS((totalAmount * totalVatPer) / 100).toFixed(2));
        } else {
            totalVat = replaceNum(converDicemalJS((((totalAmount - totalDiscount) * totalVatPer) / 100)).toFixed(2));
        }
        var total     = totalAmount - totalDiscount + totalVat;
        totalAmountKh = converDicemalJS(total * exchangeRate);
        amount = converDicemalJS(replaceNum(totalAmountKh) / 100);
        if(otherCurrencyId == 2){
            if(replaceNum(amount.toString().split(".")[1]) > 0){
                totalAmountKh = converDicemalJS((replaceNum(amount.toString().split(".")[0].replace(/,/g,"")) + 1) * 100);
            }else{
                totalAmountKh = converDicemalJS((replaceNum(amount.toString().split(".")[0].replace(/,/g,""))) * 100);
            }
            otherDecimal = 0;
        }

        if(totalAmount == 0){
            $("#PointOfSaleDiscountUs").val('0.00');
            $("#PointOfSaleDiscountPer").val(0);
        } else {
            if(discountPer == 0){
                $("#PointOfSaleDiscountUs").val(replaceNum(totalDiscount).toFixed(2));
            }
        }
        // Display
        $("#PointOfSaleTotalVatDisplay").text(totalVat.toFixed(2));
        $("#PointOfSaleSubTotalAmountUsDisplay").val(totalAmount.toFixed(2));
        $("#PointOfSaleGrandTotalAmountUsDisplay").text(total.toFixed(2));
        $("#PointOfSaleGrandTotalAmountKhDisplay").text(totalAmountKh.toFixed(otherDecimal));
        // POST Server
        $("input[name='data[PointOfSale][discount]']").val(totalDiscount);
        $("input[name='data[PointOfSale][discount_percent]']").val(discountPer);
        $("#PointOfSaleTotalVat").val(totalVat);
        $("#PointOfSaleTotalAmountUs").val(total.toFixed(2));
        $("#PointOfSaleTotalAmountKh").val(totalAmountKh.toFixed(otherDecimal));
        // Memo Required
        if(totalDiscount > 0){
            memoRequired = true;
        }
        if(memoRequired == true){
            $("#warningMemo").show();
        } else {
            $("#warningMemo").hide();
        }
        // Customer Display
        $("#customerDisplayTotal").val(converDicemalJS(total).toFixed(2));
        $("#customerDisplayTotalRiel").val(converDicemalJS(totalAmountKh).toFixed(0));
        addItemDisplay('0');
        calculateTotalQtyDisplay();
        if(total>0){
            if(PromoPoinStart!="" && PromoPoinEnd!="" && branchId!=""){
                getProductPromoPoint(PromoPoinStart,PromoPoinEnd,branchId);
            }
        }
    }
    
    function calculateBalance(){
        var exchangeRate     = replaceNum($("#PointOfSaleBranchId").find("option:selected").attr("rate"));
        var otherCurrencyId  = replaceNum($("#PointOfSaleBranchId").find("option:selected").attr("currency"));
        var otherCurrencySym = $("#PointOfSaleBranchId").find("option:selected").attr("symbol");
        var totalAmount = $("#PointOfSaleTotalAmountUs");
        var bankCharge  = replaceNum($("#PointOfSaleBankChargeAmount").val());
        var paidUs      = $("#PointOfSalePaidUs");
        var paidKh      = $("#PointOfSalePaidKh");
        var paidOther   = 0;

        if(exchangeRate > 0){
            paidOther   = converDicemalJS(replaceNum(paidKh.val()) / exchangeRate);
        }
        var totalPaid   = replaceNum(converDicemalJS((replaceNum(paidUs.val()) + paidOther)).toFixed(2));
        var balanceUs   = 0;
        var balanceKh   = 0;
        var changeUs    = 0;
        var changeKh    = 0;
        var amount      = 0;
        var changeMain  = 0;
        var changeCent  = 0;
        var changeMainToRiel = 0;
        var changeCentToRiel = 0;
        var total       = replaceNum(totalAmount.val()) + bankCharge;
        if(totalPaid < total){
            balanceUs = converDicemalJS((total - totalPaid));
        } else {
            if(replaceNum(paidUs.val()) > total){
                changeUs  = converDicemalJS(totalPaid - total);
                changeKh  = 0;
                // Check Other Currency ID = 2 (Riel), Calculate Cent
                if(otherCurrencyId == 2){
                    if(parseFloat(changeUs.toString().split(".")[1]) > 0){
                        // Main
                        changeMain = parseFloat(changeUs.toString().split(".")[0]);
                        changeMainToRiel = converDicemalJS(changeMain * exchangeRate);
                        changeMainToRiel = converDicemalJS(parseFloat(changeMainToRiel) / 100);
                        changeMainToRiel = converDicemalJS((parseFloat(changeMainToRiel.toString().split(".")[0])) * 100);
                        // Cent
                        changeCent = parseFloat("0."+changeUs.toString().split(".")[1]);
                        changeCentToRiel = converDicemalJS(changeCent * exchangeRate);
                        changeCentToRiel = converDicemalJS(parseFloat(changeCentToRiel) / 100);
                        changeCentToRiel = converDicemalJS((parseFloat(changeCentToRiel.toString().split(".")[0])) * 100);
                    }
                }
            } else {
                changeUs = 0;
                changeKh = 0;
                var balanceKhAfPaid = converDicemalJS((total - replaceNum(paidUs.val())) * exchangeRate);
                // Check Other Currency ID = 2 (Riel), Total Amount start from 100
                if(otherCurrencyId == 2){
                    balanceKhAfPaid     = converDicemalJS(parseFloat(replaceNum(balanceKhAfPaid)) / 100);
                    if(parseFloat(balanceKhAfPaid.toString().split(".")[1]) > 0){
                        balanceKhAfPaid = converDicemalJS((parseFloat(replaceNum(balanceKhAfPaid).toString().split(".")[0]) + 1) * 100);
                    }else{
                        balanceKhAfPaid = converDicemalJS((parseFloat(replaceNum(balanceKhAfPaid).toString().split(".")[0])) * 100);
                    }
                }
                if(replaceNum(paidKh.val()) > replaceNum(balanceKhAfPaid)){
                    changeKh = replaceNum(paidKh.val()) - replaceNum(balanceKhAfPaid);
                } else {
                    changeKh = replaceNum(balanceKhAfPaid) - replaceNum(paidKh.val());
                }
                if(otherCurrencyId == 2){
                    amount    = converDicemalJS(parseFloat(changeKh) / 100);
                    changeKh  = converDicemalJS((parseFloat(amount.toString().split(".")[0])) * 100);
                }
            }
            balanceUs = 0;
        }
        balanceKh = converDicemalJS(balanceUs * exchangeRate);
        if(otherCurrencyId == 2){
            amount = converDicemalJS(parseFloat(balanceKh) / 100);
            if(parseFloat(amount.toString().split(".")[1]) > 0){
                balanceKh = converDicemalJS((parseFloat(amount.toString().split(".")[0]) + 1) * 100);
            }else{
                balanceKh = converDicemalJS((parseFloat(amount.toString().split(".")[0])) * 100);
            }
        }
        $("#PointOfSaleBalanceUs").val(balanceUs.toFixed(2));
        $("#PointOfSaleBalanceKh").val(balanceKh.toFixed(0));
        $("#PointOfSaleChangeUs").val(changeUs.toFixed(2));
        $("#PointOfSaleChangeKh").val(changeKh.toFixed(0));
        // Show Cent to Riel
        if(changeCentToRiel > 0 && otherCurrencyId == 2){
            $("#convertCentToRielMain").html(changeMain + "$ = "+ changeMainToRiel + otherCurrencySym);
            $("#convertCentToRiel").html(changeCent + "$ = "+ changeCentToRiel + otherCurrencySym);
        }else{
            $("#convertCentToRielMain").html('');
            $("#convertCentToRiel").html('');
        }
        // Display Total 
        var totalRiel = converDicemalJS(total * exchangeRate);
        var amountR   = converDicemalJS(replaceNum(totalRiel) / 100);
        if(otherCurrencyId == 2){
            if(replaceNum(amountR.toString().split(".")[1]) > 0){
                totalRiel = converDicemalJS((replaceNum(amountR.toString().split(".")[0].replace(/,/g,"")) + 1) * 100);
            }else{
                totalRiel = converDicemalJS((replaceNum(amountR.toString().split(".")[0].replace(/,/g,""))) * 100);
            }
        }
        $("#customerDisplayTotal").val(converDicemalJS(total).toFixed(2));
        $("#customerDisplayTotalRiel").val(converDicemalJS(totalRiel).toFixed(0));
        addItemDisplay('0');
        calculateTotalQtyDisplay();
    }
    
    function paidDilaog(){
//        var totalBalance = $("#PointOfSaleGrandTotalAmountUsDisplay").text();
//        $("#PointOfSalePaidUs").val(totalBalance);
        $("#PointOfSalePaidUs").val("");
        $("#PointOfSalePaidKh").val(0);
        // Bank Charge
        $("#PointOfSalePaymentMethod").find("option").removeAttr("selected");
        $("#PointOfSaleBankChargeAmount").val("0.00");
        $("#PointOfSaleBankChargePercent").val(0);
        $("#PointOfSaleBankChargePercentLabel").text("");
        // Recalculate Total
        calculateBalance();
        // Unbind
        $("#PointOfSalePaidUs, #PointOfSalePaidKh, #PointOfSalePaymentMethod").unbind("change").unbind("blur").unbind("focus").unbind("keyup").unbind("keypress");
        $("#paidDialog").dialog({
            title: '<?php echo TABLE_POS_PAYMENT; ?>',
            resizable: false,
            closeOnEscape: false,
            modal: true,
            width: '500',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
                // Change Payment Method
                $("#PointOfSalePaymentMethod").change(function(){
                    var totalAmount   = replaceNum($("#PointOfSaleTotalAmountUs").val());
                    var chargePercent = replaceNum($(this).find("option:selected").attr("charge"));
                    var totalCharge   = replaceNum(converDicemalJS(converDicemalJS(totalAmount * chargePercent) / 100).toFixed(2));
                    // Set Value to Input
                    $("#PointOfSaleBankChargeAmount").val(totalCharge);
                    $("#PointOfSaleBankChargePercent").val(chargePercent);
                    if(chargePercent > 0){
                        $("#PointOfSaleBankChargePercentLabel").text("("+chargePercent+"%)");
                    } else {
                        $("#PointOfSaleBankChargePercentLabel").text("");
                    }
                    calculateBalance();
                });
                // Input Paid Blur
                $("#PointOfSalePaidUs, #PointOfSalePaidKh").blur(function(){
                    if($(this).val() == ""){
                        $(this).val("0.00");
                    }
                    calculateBalance();
                });

                $("#PointOfSalePaidUs").focus(function(){
                    var totalAmountKh  = replaceNum($("#PointOfSaleTotalAmountKh").val());
                    if(replaceNum($("#PointOfSalePaidKh").val()) == totalAmountKh){
                        $("#PointOfSalePaidKh").val(0);
                        $("#PointOfSalePaidUs").val(replaceNum($("#PointOfSaleTotalAmountUs").val()).toFixed(2));
                        calculateBalance();
                    }
                    $(this).select();
                });

                $("#PointOfSalePaidKh").focus(function(){
                    var totalAmount = replaceNum($("#PointOfSaleGrandTotalAmountUsDisplay").text());
                    if(replaceNum($("#PointOfSalePaidUs").val()) == totalAmount){
                        $("#PointOfSalePaidUs").val(0);
                        $("#PointOfSalePaidKh").val(replaceNum($("#PointOfSaleTotalAmountKh").val()));
                        calculateBalance();
                    }
                    $(this).select();
                });

                $("#PointOfSalePaidUs, #PointOfSalePaidKh").keyup(function(){
                    var exchangeRate  = replaceNum($("#PointOfSaleBranchId").find("option:selected").attr("rate"));
                    if($(this).attr("id") == 'PointOfSalePaidKh'){
                        if(exchangeRate == 0){
                            $(this).val(0);
                        }
                    }
                    calculateBalance();
                });

                $("#PointOfSalePaidUs").keypress(function(e){
                    // Key Down
                    if(e.keyCode == 40 || e.keyCode == 9){
                        var totalAmount = replaceNum($("#PointOfSaleGrandTotalAmountUsDisplay").text());
                        if(replaceNum($("#PointOfSalePaidUs").val()) == totalAmount){
                            $("#PointOfSalePaidUs").val(0);
                            $("#PointOfSalePaidKh").val(replaceNum($("#PointOfSaleTotalAmountKh").val()));
                            calculateBalance();
                        }
                        $("#PointOfSalePaidKh").select();
                    }
                    // Key Enter
                    if((e.which && e.which == 13) || e.keyCode == 13){
                        paid();
                        return false;
                    }
                });

                $("#PointOfSalePaidKh").keypress(function(e){
                    // Key Up
                    if(e.keyCode == 38){
                        var totalAmountKh  = replaceNum($("#PointOfSaleTotalAmountKh").val());
                        if(replaceNum($("#PointOfSalePaidKh").val()) == totalAmountKh){
                            $("#PointOfSalePaidKh").val(0);
                            $("#PointOfSalePaidUs").val(replaceNum($("#PointOfSaleTotalAmountUs").val()).toFixed(2));
                            calculateBalance();
                        }
                        $("#PointOfSalePaidUs").select();
                    }
                    // Key Enter
                    if((e.which && e.which == 13) || e.keyCode == 13){
                        paid();
                        return false;
                    }
                });
            }
        });
    }
    
    function SaveAddShiftRegister(){
        var companyIdAddPro              = $("#PointOfSaleCompanyId").val();
        var branchIdAddPro               = $("#PointOfSaleBranchId").val();
        var exchangeRateId               = $("#PointOfSaleBranchId").find("option:selected").attr("ex-id");
        var totalCaseRegisterAmount      = $("#PointOfSaleTotalCaseRegisterAmount").val();
        var totalCaseRegisterAmountOther = $("#PointOfSaleTotalCaseRegisterAmountOther").val();      
        var description                  = $("#PointOfSaleShiftRegisterDescription").val(); 
        if(companyIdAddPro == "" || branchIdAddPro == "" || totalCaseRegisterAmount == "" || totalCaseRegisterAmountOther == ""){
            loadRequiredFieldDialog();
        }else{
            //Data Post Add Customer
            var dataPostShiftReg = "data[Shift][company_id]="+companyIdAddPro+"&";
            dataPostShiftReg    += "data[Shift][branch_id]="+branchIdAddPro+"&";
            dataPostShiftReg    += "data[Shift][exchange_rate_id]="+exchangeRateId+"&";
            dataPostShiftReg    += "data[Shift][total_register]="+replaceNum(totalCaseRegisterAmount)+"&";
            dataPostShiftReg    += "data[Shift][total_register_other]="+replaceNum(totalCaseRegisterAmountOther)+"&";
            dataPostShiftReg    += "data[Shift][register_memo]="+description;
            
            //Close Dialog
            $("#addShiftRigisterDialog").dialog("close");
            
            //Save Product
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addShiftRegister/"+companyIdAddPro,
                data: dataPostShiftReg,
                beforeSend: function(){
                    showProgressLoading();
                },
                success: function(result){
                    closeProgessLoading();
                    // Alert Message
                    if(result == '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' || result == '<?php echo MESSAGE_DATA_INVALID; ?>'){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        // alert message and Rename Start Shift to End Shift
                        var res = result.split("|*|");
                        $("#btnChangeShiftLabel").text('<?php echo MENU_END_SHIFT; ?>');
                        $("#PointOfSaleShiftRegisterId").val(res[0]);
                        $("#PointOfSaleShiftRegisterCode").val(res[1]);
                        $("#PointOfSaleShiftRegisterCreated").val(res[2]);
                        $("#PointOfSaleCaseShiftRegister").val($("#PointOfSaleTotalCaseRegisterAmount").val());       
                        $("#PointOfSaleCaseShiftRegisterOther").val($("#PointOfSaleTotalCaseRegisterAmountOther").val());
                        $("#labelShiftRegisterCode").text($("#PointOfSaleShiftRegisterCode").val());
                        $("#labelShiftRegisterCreated").text($("#PointOfSaleShiftRegisterCreated").val());
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    }
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                resetDataAddShiftRegister();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function SaveEndShiftRegister(){
        var companyIdAddPro                = $("#PointOfSaleCompanyId").val();
        var branchIdAddPro                 = $("#PointOfSaleBranchId").val();
        var shiftId                        = $("#PointOfSaleShiftRegisterId").val();
        var totalActureRegisterAmount      = $("#PointOfSaleTotalActureEndRegisterAmount").val();
        var totalActureRegisterAmountOther = $("#PointOfSaleTotalActureEndRegisterAmountOther").val();   
        var description                    = $("#PointOfSaleEndShiftRegisterDescription").val(); 
        
        if(companyIdAddPro == "" || branchIdAddPro == "" || totalActureRegisterAmount == "" || totalActureRegisterAmountOther == ""){
            loadRequiredFieldDialog();
        }else{
            //Data Post Add Customer
            var dataPostEndShiftReg = "data[Shift][company_id]="+companyIdAddPro+"&";
            dataPostEndShiftReg    += "data[Shift][branch_id]="+branchIdAddPro+"&";
            dataPostEndShiftReg    += "data[Shift][total_acture]="+replaceNum(totalActureRegisterAmount)+"&";
            dataPostEndShiftReg    += "data[Shift][total_acture_other]="+replaceNum(totalActureRegisterAmountOther)+"&";
            dataPostEndShiftReg    += "data[Shift][close_shift_memo]="+description;
            
            //Close Dialog
            $("#endShiftRigisterDialog").dialog("close");
            
            //Save Product
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/endShiftRegister/"+companyIdAddPro+"/"+shiftId,
                data: dataPostEndShiftReg,
                beforeSend: function(){
                    showProgressLoading();
                },
                success: function(result){
                    closeProgessLoading();
                    // Alert Message
                    if(result == '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' || result == '<?php echo MESSAGE_DATA_INVALID; ?>'){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printShifeRegister" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintShifeRegister"><?php echo TABLE_PRINT_SHIFT; ?></span></button></div> '); 
                        $(".printShifeRegister").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . "point_of_sales"; ?>/printShift/"+shiftId,
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(printShiftResult){
                                    w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write(printShiftResult);
                                    w.document.close();
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                }
                            });
                        });
                    }
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                location.reload();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function SaveAdjShiftRegister(type){
        var shiftId                 = $("#PointOfSaleShiftRegisterId").val();
        var totalAdjustAmount       = replaceNum($("#PointOfSaleTotalAddAdjEndRegisterAmount").val());
        var totalAdjustAmountOther  = replaceNum($("#PointOfSaleTotalAddAdjEndRegisterAmountOther").val()); 
        var description             = $("#PointOfSaleAddAdjShiftRegisterDescription").val();
        var totalSale               = 0;
        
        if(totalAdjustAmount != "" || totalAdjustAmountOther != ""){
            totalSale = 1;
        }
        
        if(shiftId == "" || totalSale == 0 || description == ""){
            loadRequiredFieldDialog();
        }else{
            //Data Post Add Customer
            var dataPostAddAdjShiftReg = "data[ShiftAdjust][shift_id]="+shiftId+"&";
            dataPostAddAdjShiftReg    += "data[ShiftAdjust][total_adj]="+totalAdjustAmount+"&";
            dataPostAddAdjShiftReg    += "data[ShiftAdjust][total_adj_other]="+totalAdjustAmountOther+"&";
            dataPostAddAdjShiftReg    += "data[ShiftAdjust][description]="+description;
            
            //Close Dialog
            $("#AddAdjShiftRigisterDialog").dialog("close");

            //Save Adjust Shift Register
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/saveAdjShiftRegister/"+shiftId+"/"+type,
                data: dataPostAddAdjShiftReg,
                beforeSend: function(){
                    showProgressLoading();
                },
                success: function(result){
                    closeProgessLoading();
                    // Alert Message
                    if(result == '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' || result == '<?php echo MESSAGE_DATA_INVALID; ?>'){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        var res = result.split("|*|");
                        $("#labelTotalAdjCaseRegister").text(res[1]);
                        $("#labelTotalAdjCaseRegisterOther").text(res[2]);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    }
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                resetDataAddAdjShiftRegister();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function ShowAdjAndCloseShiftRegister(){    
        var shiftId           = $("#PointOfSaleShiftRegisterId").val();
        var caseRigister      = replaceNum($("#PointOfSaleCaseShiftRegister").val());
        var caseRigisterOther = replaceNum($("#PointOfSaleCaseShiftRegisterOther").val());
    
        //Get Data Adjust Shift Register
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/getDataAdjShiftRegister/"+shiftId,
            data: "",
            beforeSend: function(){
                showProgressLoading();
            },
            success: function(result){
                closeProgessLoading();
                $("#labelAdjCaseRegister").text(parseFloat(caseRigister).toFixed(2));
                $("#labelAdjCaseRegisterOther").text(parseFloat(caseRigisterOther).toFixed(0));  
                $("#AddAdjCloseShiftRigisterDialog").dialog({
                    title: '<?php echo MENU_TITLE_SHIFT; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: '500',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").show();
                    }
                });
                $("#getDataAdjShift").html(result);
            }
        });
    }
    
    function ShowAdjShiftRegister(){
        $("#AddAdjShiftRigisterDialog").dialog({
            title: '<?php echo MENU_ADD_ADJ_SHIFT_REGISTER; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            }
        });
    }
    
    function ShowCloseShiftRegister(){    
        var shiftId           = $("#PointOfSaleShiftRegisterId").val();
        var caseRigister      = $("#PointOfSaleCaseShiftRegister").val();
        var caseRigisterOther = $("#PointOfSaleCaseShiftRegisterOther").val();
        $("#labelCaseRegister").val(replaceNum(caseRigister).toFixed(2));
        $("#labelCaseRegisterOther").val(replaceNum(caseRigisterOther).toFixed(0));    
        
        //Check Adjust Shift Register
        $.ajax({
            dataType: "json",
            type: "POST",
            url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkAdjShiftRegister/"+shiftId,
            data: "",
            beforeSend: function(){
                showProgressLoading();
            },
            success: function(result){
                closeProgessLoading();                
                $("#labelAdjRegister").val(replaceNum(result.adjShift).toFixed(2));
                $("#labelAdjRegisterOther").val(replaceNum(result.adjShiftOther).toFixed(0));
                $("#PointOfSaleTotalActureEndRegisterAmount, #PointOfSaleTotalActureEndRegisterAmountOther").val('');
                $("#endShiftRigisterDialog").dialog({
                    title: '<?php echo MENU_END_SHIFT; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").show();
                    }
                });
            }
        });
    }
    
    function showProgressLoading(){
        // DialogProcessing
        $("#dialogProcessing").dialog({
            title: 'Loading...',
            resizable: false,
            modal: true,
            width: 'auto',
            height: '110',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            }
        });
    }
    
    function closeProgessLoading(){
        $("#dialogProcessing").dialog("close");
    }
    
    function loadRequiredFieldDialog(){
        $("#alertRequiredField").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function resetDataAddCustomer(){
        $("#PointOfSaleAddCustomerCgroup").val('').trigger('liszt:updated');
        $("#PointOfSalesAddCustomerName").val('');
        $("#PointOfSalesAddCustomerNameKh").val('');
        $("#PointOfSalesAddCustomerTelephone").val('');
    }
    
    function resetDataAddProduct(){
        $("#PointOfSalesAddProductBarCode").val('');
        $("#PointOfSalesAddProductCode").val('');
        $("#PointOfSalesAddProductName").val('');
        $("#PointOfSalesAddProductUnitCost").val('');
    }
    
    function resetDataAddShiftRegister(){
        $("#PointOfSaleTotalCaseRegisterAmount").val('');
        $("#PointOfSaleTotalCaseRegisterAmountOther").val('');
        $("#PointOfSaleShiftRegisterDescription").val('');
    }
    
    function resetDataAddAdjShiftRegister(){
        $("#PointOfSaleTotalAddAdjEndRegisterAmount").val('');
        $("#PointOfSaleTotalAddAdjEndRegisterAmountOther").val('');
        $("#PointOfSaleAddAdjShiftRegisterDescription").val('');
    }
    
    function resetDataEndShiftRegister(){
        $("#PointOfSaleTotalCaseEndRegisterAmount").val('');
        $("#PointOfSaleTotalCaseEndRegisterAmountOther").val('');
        $("#PointOfSaleTotalAdjEndRegisterAmount").val('');
        $("#PointOfSaleTotalAdjEndRegisterAmountOther").val('');
        $("#PointOfSaleEndShiftRegisterDescription").val('');
    }
    
    function paid(){
        if(checkExistingRecord() == true && replaceNum($("#PointOfSaleBalanceUs").val())==0 && memoRequired == false){
            savePaid(1);
        } else if (replaceNum($("#PointOfSaleBalanceUs").val()) > 0) {
            $("#PointOfSalePaidUs").focus().select();
        } else if (memoRequired == true) {
            if($("#PointOfSaleMemo").val() == ""){
                $("#PointOfSaleMemo").css("border-color", "red");
                $("#PointOfSaleMemo").focus().select();
            } else {
                savePaid(1);
            }
        } else {
            alertMakeOrder();
        }
    }
    
    function notPaidNow(){
        //Check Customer
        var customerId = $("#customerPOSId").val();        
        if(customerId > 1 && checkExistingRecord() == true){
            savePaid(2);
        } else if (customerId == 1) {
            var question = "<?php echo MESSAGE_PLEASE_SELECT_CUSTOMER; ?>";
            $("#dialog").html('<p style="color: red; font-weight: bold;"><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                position:'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                    $(".ui-dialog-titlebar-close").show();
                },
                buttons: {
                    '<?php echo ACTION_OK; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        } else {
            alertMakeOrder();
        }
    }
    
    function savePaid(type){
        if(!isPaid){
            isPaid = true;
            <?php
            if ($allowChangeDate) {
            ?>
            $("#posDate").datepicker("option", "dateFormat", "yy-mm-dd");
            <?php
            }
            ?>
            $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            var url   = $('#PointOfSaleAddForm').attr("action");
            var data1 = $('#PointOfSaleAddForm').serialize();
            var data2 = '&data[PointOfSales][total_amount]='+replaceNum($('#PointOfSaleSubTotalAmountUsDisplay').val());
            var data2 = data2 + '&data[PointOfSale][total_vat]='+replaceNum($('#PointOfSaleTotalVat').val());
            var data2 = data2 + '&data[PointOfSale][vat_percent]='+replaceNum($('#PointOfSaleVatPercent').find('option:selected').attr('rate'));
            var data2 = data2 + '&data[PointOfSale][vat_chart_account_id]='+replaceNum($('#PointOfSaleVatChartAccountId').val());
            var data2 = data2 + '&data[PointOfSale][vat_setting_id]='+replaceNum($('#PointOfSaleVatPercent').val());
            var data2 = data2 + '&data[PointOfSale][vat_calculate]='+replaceNum($('#PointOfSaleVatCalculate').val());
            var data2 = data2 + '&data[PointOfSale][total_be_paid]='+replaceNum($('#PointOfSaleTotalAmountUs').val());
            var data2 = data2 + '&data[PointOfSale][total_be_paid_kh]='+replaceNum($('#PointOfSaleTotalAmountKh').val());
            var data2 = data2 + '&data[PointOfSale][bank_charge_amount]='+replaceNum($('#PointOfSaleBankChargeAmount').val());
            var data2 = data2 + '&data[PointOfSale][bank_charge_percent]='+replaceNum($('#PointOfSaleBankChargePercent').val());
            var data2 = data2 + '&data[PointOfSale][pos_pay_method_id]='+$('#PointOfSalePaymentMethod').val();
            if(type == 1){ // Action Pay
                var data2 = data2 + '&data[PointOfSale][paid_us]='+replaceNum($('#PointOfSalePaidUs').val());
                var data2 = data2 + '&data[PointOfSale][paid_kh]='+replaceNum($('#PointOfSalePaidKh').val());
                var data2 = data2 + '&data[PointOfSale][balance_us]='+replaceNum($('#PointOfSaleBalanceUs').val());
                var data2 = data2 + '&data[PointOfSale][balance_kh]='+replaceNum($('#PointOfSaleBalanceKh').val());
                var data2 = data2 + '&data[PointOfSale][change_us]='+replaceNum($('#PointOfSaleChangeUs').val());
                var data2 = data2 + '&data[PointOfSale][change_kh]='+replaceNum($('#PointOfSaleChangeKh').val());
            } else { // Not Pay Now
                var data2 = data2 + '&data[PointOfSale][paid_us]=0';
                var data2 = data2 + '&data[PointOfSale][paid_kh]=0';
                var data2 = data2 + '&data[PointOfSale][balance_us]='+replaceNum($('#PointOfSaleTotalAmountUs').val());
                var data2 = data2 + '&data[PointOfSale][balance_kh]='+replaceNum($('#PointOfSaleTotalAmountKh').val());
                var data2 = data2 + '&data[PointOfSale][change_us]=0';
                var data2 = data2 + '&data[PointOfSale][change_kh]=0';
            }
            var data2 = data2 + '&data[PointOfSale][price_type_id]='+replaceNum($("#PointOfSaleCompanyId").find("option:selected").attr("ptype"));
            var data2 = data2 + '&data[PointOfSale][calculate_cogs]='+replaceNum($("#PointOfSaleCalculateCOGS").val());
            var data2 = data2 + '&data[PointOfSale][shift_id]='+replaceNum($("#PointOfSaleShiftRegisterId").val()); 
            var data2 = data2 + '&data[PointOfSale][memo]='+$("#PointOfSaleMemo").val(); 
            var data2 = data2 + '&data[PointOfSale][card_code]='+$("#MembershipCardCode").val(); 
            var data2 = data2 + '&data[PointOfSale][membership_card_id]='+$("#MembershipCardId").val(); 
            var data2 = data2 + '&data[PointOfSale][total_point]='+$("#totaPointItem").val(); 

            var data2 = data2 + '&data[PointOfSale][remain_point]='+$("#remainPointMemShip").val(); 
            var data2 = data2 + '&data[PointOfSale][promotional_type]='+$("#promotionalType").val(); 
            var data2 = data2 + '&data[PointOfSale][promotion_point_id]='+$("#PointOfSalePromotionPointId").val(); 
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    url,
                data:   data1+data2,
                beforeSerialize: function($form, options) {
                    $(".float, .integer").each(function(){
                        $(this).val($(this).val().replace(/,/g,""));
                    });
                },
                beforeSend: function(){
                    $("#progress").show();
                    $("#btnPosPaid").val("Loading...").attr("disabled", true);
                },
                error: function (result) {
                    $("#progress").hide();
                    $("#paidDialog").dialog("close");
                    $("#btnPosPaid").val("Pay").attr("disabled", false);
                    createSysAct('POS', 'Add', 2, result.responseText);
                    saveError104();
                },
                success: function(result){
                    alert(result.error);
                    $("#progress").hide();
                    $("#paidDialog").dialog("close");
                    $("#btnPosPaid").val("Pay").attr("disabled", false);
                    if(result.error == '4'){
                        var StrError = result.stock.toString().split("-");
                        var obj      = "";
                        $("#paidDialog").dialog("close");
                        $(".listTable").each(function(){
                            if($(this).find("input[name='data[SalesOrderDetail][product_id][]']").val() != ""){
                                obj = $(this);
                                $.each(StrError, function(i, val){
                                    if(val != ""){
                                        if(obj.find("input[name='data[SalesOrderDetail][product_id][]']").val() == val){
                                            obj.closest("tr").find("td").css("background","#fc8b8b");
                                        }
                                    }
                                });
                            }
                        });
                        $("#PointOfSalePaidUs").val(0);
                        $("#PointOfSalePaidKh").val(0);
                        saveError();
                        isPaid = false;
                    } else if(result.error == '0'){
                        // Customer Display
                        addItemDisplay('1');
                        
                        createSysAct('POS', 'Add', 1, '');
                        // Generate Item
                        generatePrintLayoutItem();
                        // Header
                        var decimalMain  = checkCurrencyDecimal($("#PointOfSaleCompanyId").find("option:selected").attr("currency"));
                        var decimalOther = checkCurrencyDecimal($("#PointOfSaleBranchId").find("option:selected").attr("currency"));
                        var companyLogo  = result.com_photo;
                        var savingAmt    = replaceNum($("input[name='data[PointOfSale][discount]']").val());
                        var itemDisc     = 0;
                        $(".listTable").each(function(){
                            savingAmt += replaceNum($(this).find(".lblDiscount").text());
                            itemDisc  += replaceNum($(this).find(".lblDiscount").text());
                        });
                        $("#printCompanyLogo").attr("src", "<?php echo $this->webroot; ?>public/company_photo/"+companyLogo);
                        $("#printBranchName").text($("#PointOfSaleBranchId").find("option:selected").html());
                        $("#printBranchAddress").text(result.branch_add);
                        $("#printBranchTel").text($("#PointOfSaleBranchId").find("option:selected").attr("tel"));

                        $("#printBranchAccNum").text($("#PointOfSaleBranchId").find("option:selected").attr("acc-num"));
                        $("#printBranchAccHolder").text($("#PointOfSaleBranchId").find("option:selected").attr("acc-holder"));
                        $("#printBranchBank").text($("#PointOfSaleBranchId").find("option:selected").attr("bank"));

                        if($("#PointOfSaleBranchId").find("option:selected").attr("acc-num")!=""){
                            $("#POSAccNum").show();
                        }else{
                            $("#POSAccNum").hide();
                        }

                        if($("#PointOfSaleBranchId").find("option:selected").attr("acc-holder")!=""){
                            $("#POSAccHolder").show();
                        }else{
                            $("#POSAccHolder").hide();
                        }

                        if($("#PointOfSaleBranchId").find("option:selected").attr("bank")!=""){
                            $("#POSBank").show();
                        }else{
                            $("#POSBank").hide();
                        }

                        $("#printInvoiceCode").text(result.inv_code);
                        $("#printInvoiceDate").text(result.inv_date);
                        $("#printUsername").text(result.username);
                       $("#printCustomerName").text($("#PointOfSaleCustomerNameLabel").text());
                        // Currency Symbol
                        $(".printMainCurrency").text($("#PointOfSaleBranchId").find("option:selected").attr("main-symbol"));
                        if($("#PointOfSaleBranchId").find("option:selected").attr("currency") != '' && $("#PointOfSaleBranchId").find("option:selected").attr("currency") != '0'){
                            $(".printOtherCurrency").text($("#PointOfSaleBranchId").find("option:selected").attr("symbol"));
                        } else {
                            $(".printOtherCurrency").text('');
                        }
                        // Foooter
                        $("#printSubTotalMain").text(convertToSeparator((replaceNum($("#PointOfSaleSubTotalAmountUsDisplay").val()) + itemDisc), decimalMain));
                        $("#printSubTotalDiscount").text(convertToSeparator((replaceNum($("input[name='data[PointOfSale][discount]']").val()) + replaceNum(itemDisc)), decimalMain));
//                        if($("input[name='data[PointOfSale][discount_percent]']").val() > 0){
//                            $("#printDiscountPercent").text('('+convertToSeparator($("input[name='data[PointOfSale][discount_percent]']").val(), 2)+'%)');
//                        } else {
//                            $("#printDiscountPercent").text('');
//                        }
                        // VAT
                        $("#printSubTotalVAT").text(convertToSeparator($("#PointOfSaleTotalVat").val(), decimalMain)); 
                        if(replaceNum($("#PointOfSaleTotalVat").val()) > 0){
                            $("#POSPrintVAT").show();
                        } else {
                            $("#POSPrintVAT").hide();
                        }
                        // Bank Charge
                        $("#printSubTotalBankCharge").text(convertToSeparator($("#PointOfSaleBankChargeAmount").val(), decimalMain)); 
                        if(replaceNum($("#PointOfSaleBankChargeAmount").val()) > 0){
                            $("#POSPrintBankCharge").show();
                        } else {
                            $("#POSPrintBankCharge").hide();
                        }
                        // Total
                        $("#printTotalMain").text(convertToSeparator($("#PointOfSaleTotalAmountUs").val(), decimalMain));
                        if($("#PointOfSaleBranchId").find("option:selected").attr("currency") != '' && $("#PointOfSaleBranchId").find("option:selected").attr("currency") != '0'){
                            $("#printTotalOther").text(convertToSeparator($("#PointOfSaleTotalAmountKh").val(), decimalOther));
                        } else {
                            $("#printTotalOther").text('');
                        }
                        if(savingAmt > 0){
                            $("#printSavingAmount").text(convertToSeparator(savingAmt, decimalMain)); 
                            $("#posSavingAmount").show();
                        } else {
                            $("#posSavingAmount").hide();
                        }
                        if(type == 1){
                            $("#printTotalReceiveMain").text(convertToSeparator($("#PointOfSalePaidUs").val(), decimalMain));
                            $("#printTotalChangeMain").text(convertToSeparator($("#PointOfSaleChangeUs").val(), decimalMain));
                            if($("#PointOfSaleBranchId").find("option:selected").attr("currency") != '' && $("#PointOfSaleBranchId").find("option:selected").attr("currency") != '0'){
                                $("#printTotalReceiveOther").text(convertToSeparator($("#PointOfSalePaidKh").val(), decimalOther));
                                $("#printTotalChangeOther").text(convertToSeparator($("#PointOfSaleChangeKh").val(), decimalOther));
                            } else {
                                $("#printTotalReceiveOther").text('');
                                $("#printTotalChangeOther").text('');
                            }
                            $(".payNow").show();
                        } else {
                            $(".payNow").hide();
                        }
                        // Print Date
                        $("#printDatePOS").text(result.print_date);
                        layoutPrint = $("#printLayoutPOS").html();
                        // Print Receipt
                        w = window.open();
                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                        w.document.write('<style type="text/css" media="screen">div.print-footer {display: none;}<\/style> ');
                        w.document.write('<style type="text/css" media="print">div.print_doc { width:100%;}div.print-footer {display: block; width:100%;}<\/style>');
                        w.document.write(layoutPrint);
                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"><\/script>');
                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"><\/script>');
                        w.document.write('<script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_pos.js"><\/script>');
                        w.document.close();
                        // Reset Data
                        resetFormPOS();
                        // Dialog Information
                        reloadPage();
                        isPaid = false;
                    } else {
                        saveError104();
                    }
                }
            });
        }
    }
    
    function alertMakeOrder(){
        var question = "<?php echo MESSAGE_MAKE_ORDER; ?>";
        $("#dialog").html('<p style="color: red; font-weight: bold;"><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function saveError(){
        var question = "There are some product out of stock. Please try again!";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            },
            close: function (){
                $("#progress").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $("#progress").hide();
                    $(this).dialog("close");
                }
            }
        });
    }
    function saveError104(){
        var question = "<?php echo MESSAGE_PROBLEM; ?>";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    resetFormPOS();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function reloadPage(){
        var question = "<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    function checkPriceTypeCom(){
        var priceTypeId = $("#PointOfSaleCompanyId").find("option:selected").attr("ptype");
//        var currencyId  = $("#PointOfSaleBranchId").find("option:selected").attr("currency");
//        var rateId      = $("#PointOfSaleBranchId").find("option:selected").attr("rate");        
        if(priceTypeId == ''){
            warningConfigSetting();
        }else{            
            //SSB
            var allowShift  = '<?php echo $allowShift; ?>';
            var companyId   = $("#PointOfSaleCompanyId").find("option:selected").val();
            var branchId    = $("#PointOfSaleBranchId").find("option:selected").val();
            if(allowShift == '1'){
                $.ajax({
                    dataType: 'json',
                    type:   'POST',
                    url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/checkStartShift/"+companyId+"/"+branchId,
                    beforeSend: function(){
                        showProgressLoading();
                    },
                    success: function(result){
                        closeProgessLoading();                        
                        if(result.status_shift == 0){
                            // Check Enable/Disable Cash Register
                            if(result.not_collect == 1){
                                $("#PointOfSaleTotalCaseRegisterAmount, #PointOfSaleTotalCaseRegisterAmountOther").attr('readonly', true).val(0);
                                $("#alertOpenShift").show();
                                $(".openShiftRegisterRow").hide();
                                $("#addShiftRigisterDialog").dialog({
                                    title: '<?php echo MENU_START_SHIFT; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position:'center',
                                    open: function(event, ui){
                                        $(".ui-dialog-buttonpane").show();
                                        $(".ui-dialog-titlebar-close").hide();
                                    },
                                    buttons: {
                                        '<?php echo GENERAL_LOG_OUT; ?>': function() {
                                            window.location.href = "<?php echo $this->base; ?>/users/logout";
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            } else {
                                $("#PointOfSaleTotalCaseRegisterAmount, #PointOfSaleTotalCaseRegisterAmountOther").attr('readonly', false).val('');
                                $("#alertOpenShift").hide();
                                $(".openShiftRegisterRow").show();
                                // Rename Change Shift to Start Shift
                                $("#btnChangeShiftLabel").text('<?php echo MENU_START_SHIFT; ?>');
                                $("#addShiftRigisterDialog").dialog({
                                    title: '<?php echo MENU_START_SHIFT; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position:'center',
                                    open: function(event, ui){
                                        $(".ui-dialog-buttonpane").show();
                                        $(".ui-dialog-titlebar-close").hide();
                                    }
                                });
                            }
                        }else{
                            $("#btnChangeShiftLabel").text('<?php echo MENU_END_SHIFT; ?>');
                            $("#PointOfSaleShiftRegisterId").val(result.status_shift);
                            $("#PointOfSaleShiftRegisterCode").val(result.shift_code);
                            $("#PointOfSaleShiftRegisterCreated").val(result.shift_created);
                            $("#PointOfSaleCaseShiftRegister").val(result.total_register);       
                            $("#PointOfSaleCaseShiftRegisterOther").val(result.total_register_other); 
                            $("#labelShiftRegisterCode").text($("#PointOfSaleShiftRegisterCode").val());
                            $("#labelShiftRegisterCreated").text($("#PointOfSaleShiftRegisterCreated").val());
                            $("#labelTotalAdjCaseRegister").text(result.total_adj);
                            $("#labelTotalAdjCaseRegisterOther").text(result.total_adj_other);
                        }
                    }
                });
            }            
        }
    }
    function checkVatBranchSales(){
        // Hide Price Type that not belong to the company
//        $("#PointOfSaleVatPercent option").show();
//        $("#PointOfSaleVatPercent option").each(function(){
//            if($(this).attr("branch-id")){
//                var companyId = $(this).attr("branch-id").split(",");
//                if(companyId.indexOf($("#PointOfSaleBranchId").val())==-1){
//                    $(this).hide();
//                }
//            }
//        });
    }
    function changeLblVatCalSales(){
        var vatCal = $("#PointOfSaleVatCalculate").val();
        $("#lblPointOfSaleVatSetting").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblPointOfSaleVatSetting").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblPointOfSaleVatSetting").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    function checkVatSelectedSales(){
        var vatAccId   = replaceNum($("#PointOfSaleVatPercent").find("option:selected").attr("acc"));
        $("#PointOfSaleVatChartAccountId").val(vatAccId);
    }
    function reprintInvoice(){
        $("#dialogReprintInvoice").dialog({
            title: 'Reprint Invoice',
            resizable: false,
            modal: true,
            width: 400,
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").show();
            },
            buttons: {
                '<?php echo ACTION_PRINT; ?>': function() {
                    var invoiceCode = $("#invoiceCodeReprint").val();
                    $("#invoiceCodeReprint").val('');
                    if(invoiceCode != ''){
                        $(this).dialog("close");
                        printInvoice(invoiceCode);
                    }else{
                        $("#invoiceCodeReprint").focus();    
                    }
                }
            }
        });
    }
    
    function printInvoice(invoiceCode){
        var branchId = $("#PointOfSaleBranchId").val();
        if(invoiceCode != '' && branchId != ''){
            $.ajax({
                type:   'POST',
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/reprintReceiptSm/"+invoiceCode+"/"+branchId,
                beforeSend: function(){
                    $("#progress").show();
                },
                success: function(result){
                    $("#progress").hide();
                    if(result == 'error'){
                        var question = "Invalid Invoice Code";
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: 250,
                            height: 'auto',
                            position:'center',
                            open: function(event, ui){
                                $(".ui-dialog-buttonpane").show();
                                $(".ui-dialog-titlebar-close").show();
                            },
                            close: function (){
                                $("#PointOfSaleBarcode").focus();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }else{
                        var w = window.open();
                        w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                        w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                        w.document.write(result);
                        w.document.close();
                    }
                }
            });
        }
    }

    function salesInvoice(){
        $.ajax({
            type:   'POST',
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewPosDaily/",
            beforeSend: function(){
                $("#progress").show();
            },
            success: function(result){
                $("#progress").hide();
                $("#dialog").html(result);
                $("#dialog").dialog({
                    title: 'Sales Invoice Histories',
                    resizable: false,
                    modal: true,
                    width: 980,
                    height: 450,
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").show();
                    },
                    close: function (){
                        $("#PointOfSaleBarcode").focus();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function generatePrintLayoutItem(){
        var layoutPrintItem = '';
        var i = 1;
        var decimalMain  = checkCurrencyDecimal($("#PointOfSaleCompanyId").find("option:selected").attr("currency"));
        $(".listTable").each(function(){
            var name   = $(this).find(".productName").text();
            var qty    = replaceNum($(this).find(".editQty").val());
            var foc    = replaceNum($(this).find(".qtyFree").val());
//            var uom    = $(this).find(".editUomQty").find("option:selected").html();
            var price  = replaceNum($(this).find("input[name='data[SalesOrderDetail][unit_price][]']").val()).toFixed(2);
            var disc   = replaceNum($(this).find(".lblDiscount").text()).toFixed(2);
            var total  = replaceNum($(this).find(".productTotalPrice").text()).toFixed(2);
            if(qty > 0) {
                layoutPrintItem += '<tr>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; text-align: left; vertical-align:top; line-height: 20px; padding-top: 3px;">'+qty+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">'+name+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">'+price+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">'+disc+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; text-align: right; vertical-align:top; padding-top: 3px;"><div style="width:10px; float: left; font-size:12px; margin-left: 5px;">$</div>'+convertToSeparator(total, decimalMain)+'</td>';
                layoutPrintItem += '</tr>';
                i++;
            }  
            if (foc > 0) {
                layoutPrintItem += '<tr>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; text-align: left; vertical-align:top; line-height: 20px; padding-top: 3px;">'+foc+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">'+name+'</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">Free</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; padding-top: 3px;">Free</td>';
                layoutPrintItem += '<td style="border: none; font-size: 12px; text-align: right; vertical-align:top; padding-top: 3px;">Free</td>';
                layoutPrintItem += '</tr>';
                i++;
            }
        });
        $("#printDetailItems").html(layoutPrintItem);
    }
    
    function getTotalQtyLotExp(obj){
        var locationGroupId = $("#PointOfSaleLocationGroupId").val();
        var productId  = obj.closest("tr").find("input[name='data[SalesOrderDetail][product_id][]']").val();
        var lotsNumber = obj.closest("tr").find(".lots_number").val()!=""?obj.closest("tr").find(".lots_number").val():'0';
        var expiryDate = '0000-00-00';
        if(obj.closest("tr").find(".expired_date").val()!=""){
            expiryDate = obj.closest("tr").find(".expired_date").val().toString().split("/")[2]+"-"+obj.closest("tr").find(".expired_date").val().toString().split("/")[1]+"-"+obj.closest("tr").find(".expired_date").val().toString().split("/")[0];
        }
        if(locationGroupId != "" && productId != "" && lotsNumber != "" && expiryDate != ""){
            $.ajax({
                dataType: 'json',
                type:   'POST',
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/getTotalQtyByLotExp/"+productId+"/"+locationGroupId+"/"+lotsNumber+"/"+expiryDate,
                beforeSend: function(){
                    $("#progress").show();
                },
                success: function(result){
                    $("#progress").hide();
                    obj.closest("tr").find(".productInStock").val(result.total);
                    saveProduct(obj.closest("tr"));
                }
            });
        }
    }
    
    function membershipDialog(customerId){
        $.ajax({
            type:   'POST',
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/membershipCardDis/"+customerId,
            beforeSend: function(){
                $("#progress").show();
            },
            success: function(result){
                $("#progress").hide();
                $("#dialog").html(result);
                $("#dialog").dialog({
                    title: '<?php echo MENU_MEMBERSHIP_CARD; ?>',
                    resizable: false,
                    modal: true,
                    width: '600',
                    height: 'auto',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            // var discountPercent = $("#CardDiscount").val();
                            // var cardId     = $("#cardId").val();
                            // var cardNumber = $("#cardNumber").val();
                            // if((cardId != '' || cardId != undefined) && (discountPercent != '' || discountPercent != undefined) && (cardNumber != '' || cardNumber != undefined)){
                            //     $("#PointOfSaleDiscountUs").val(0).attr("readonly", true);
                            //     $("#PointOfSaleDiscountPer").val(discountPercent).attr("readonly", true);
                            //     $("#MembershipCardId").val(cardId);
                            //     $("#MembershipCardCode").val(cardNumber);
                            //     // Button
                            //     $("#btnVoidDisByCard").show();
                            //     $("#btnMembershipCardCode").hide();
                            // } else {
                            //     $("#PointOfSaleDiscountUs, #PointOfSaleDiscountPer").val(0).attr("readonly", false);
                            //     $("#MembershipCardId").val('');
                            //     $("#MembershipCardCode").val('');
                            //     // Button
                            //     $("#btnMembershipCardCode").show();
                            //     $("#btnVoidDisByCard").hide();
                            // }
                            // calculateBalance();
                            // getTotalAmount();
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function addItemDisplay(thxNum){
        var itemPush = [];
        var list = [];
        var branchName = $("#PointOfSaleBranchId").find("option:selected").html();
        var mainSym    = $("#PointOfSaleBranchId").find("option:selected").attr("main-symbol");
        var otherSym   = $("#PointOfSaleBranchId").find("option:selected").attr("symbol");
        var exRate     = $("#PointOfSaleBranchId").find("option:selected").attr("rate");
        var subTotal   = $("#PointOfSaleSubTotalAmountUsDisplay").val();
        var discount   = $("#PointOfSaleDiscountUs").val();
        var bankCharge = $("#PointOfSaleBankChargeAmount").val();
        var total      = $("#customerDisplayTotal").val();
        var totalOther = $("#customerDisplayTotalRiel").val();
        $(".listTable").each(function(){
            if($(this).attr('id') == undefined){
                var itemName  = $(this).find(".productName").text();
                var itemQty   = $(this).find(".editQty").val();
                var itemPrice = $(this).find(".unitPrice").text();
                var itemDisc  = $(this).find("input[name='data[SalesOrderDetail][discount_amount][]']").val();
                var itemTotal = $(this).find(".productTotalPrice").text();
                list.push({
                    name: itemName, 
                    qty:  itemQty,
                    price:  itemPrice,
                    disc: itemDisc,
                    total:  itemTotal
                });
            }
        });
        itemPush.push({
            branch: branchName,
            mainsym: mainSym,
            othersym: otherSym,
            exrate: exRate,
            subtotal: subTotal, 
            discount:  discount,
            bankCharge:  bankCharge,
            total:  total,
            totalother:  totalOther,
            thxNum: thxNum,
            item:  list
        });
        // Add to Local Storage
        var tabPOSDis   = "tabPOSDisplay"+tabId;
        var itemDisplay = JSON.stringify(itemPush);
        localStorage.setItem(tabPOSDis, itemDisplay);
    }
    
    function saveItemVoid(productId, qty, uomId){
        if(productId != '' && qty != '' && uomId != ''){
            $.ajax({
                type: 'POST',
                url:  "<?php echo $this->base . '/' . $this->params['controller']; ?>/saveItemVoid/",
                data: "product_id="+productId+"&qty="+qty+"&uom_id="+uomId
            });
        }
    }
</script>