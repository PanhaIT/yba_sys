<?php 
$allowLots    = false;
$allowExpired = false;
$allowPO      = false;
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 12, 39) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 12){
        if($rowSetting['is_checked'] == 1){
            $allowPO = true;
        }
    } else if($rowSetting['id'] == 39){
//        $costDecimal = $rowSetting['value'];
    }
}

// Check Permission
$this->element('check_access');
$allowAddService  = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowDiscount    = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowEditInvDis  = checkAccess($user['User']['id'], $this->params['controller'], 'invoiceDiscount');
$allowAddProduct  = checkAccess($user['User']['id'], 'products', 'quickAdd');
$allowAddVendor   = checkAccess($user['User']['id'], 'vendors', 'quickAdd');
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
$queryClosingDate = mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate  = mysql_fetch_array($queryClosingDate);
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    var indexRowPB   = 0;
    var cloneRowPB   = $("#detailPB");
    var pbTimeCode   = 1;
    
    function resizeFormTitlePB(){
        var screen = 16;
        var widthList = $("#bodyListPB").width();
        var widthTitle = widthList - screen;
        $("#tblHeaderPB").css('padding', '0px');
        $("#tblHeaderPB").css('margin-top', '5px');
        $("#tblHeaderPB").css('width', widthTitle);
    }
    
    function resizeFornScrollPB(){
        var tabHeight = $(tabPBId).height();
        var formHeader = 0;
        if ($('#PBTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#PBTop").height();
        }
        var btnHeader   = $("#btnHideShowHeaderPurchaseBill").height();
        var formFooter  = $("#POFooter").height();
        var formSearch  = $("#searchFormPB").height();
        var tableHeader = $("#tblHeaderPB").height();
        var widthList   = $("#bodyListPB").width();
        var getHeight   = tabHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter);
        $("#bodyListPB").css('height', getHeight);
        $("#bodyListPB").css('padding', '0px');
        $("#bodyListPB").css('width', widthList);
        $("#bodyListPB").css('overflow-x', 'hidden');
        $("#bodyListPB").css('overflow-y', 'scroll');
    }
    
    function refreshScreenPB(){
        $("#tblHeaderPB").removeAttr('style');
        var windowWidth  = $(window).width();
        if(windowWidth <= '1024'){
            $(".productPOCode").css('width','40%');
        }else{
            $(".productPOCode").css('width','50%');
        }
    }
    
    function calcTotalPB(){
        var totalSubAmount = 0;
        var totalVat       = 0;
        var totalAmount    = 0;
        var totalDiscount    = replaceNum($("#PurchaseBillDiscountAmount").val());
        var totalDisPercent  = replaceNum($("#PurchaseBillDiscountPercent").val());
        var totalVatPercent  = replaceNum($("#PurchaseBillVatPercent").val());
        var vatCal           = $("#PurchaseBillVatCalculate").val();
        var totalBfDis       = 0;
        var totalAmtCalVat   = 0;
        $(".listBodyPB").find(".total_cost").each(function(){
            totalSubAmount += replaceNum($(this).val());
        });
        if(totalDisPercent > 0){
            totalDiscount  = replaceNum(converDicemalJS((totalSubAmount * totalDisPercent) / 100).toFixed(<?php echo $costDecimal; ?>));
        }
        totalAmtCalVat = replaceNum(converDicemalJS(totalSubAmount - totalDiscount));
        if(vatCal == 1){
            $(".listBodyPB").each(function(){
                var qty   = replaceNum($(this).find(".qty").val()) + replaceNum($(this).find(".qty_free").val());
                var price = replaceNum($(this).find(".unit_cost").val());
                totalBfDis += replaceNum(converDicemalJS(qty * price));
            });
            totalAmtCalVat = totalBfDis;
        }
        totalVat = replaceNum(converDicemalJS((totalAmtCalVat * totalVatPercent) / 100).toFixed(<?php echo $costDecimal; ?>));
        totalAmount = converDicemalJS(replaceNum(totalSubAmount - totalDiscount) + replaceNum(totalVat));
        $("#PurchaseBillTotalAmount").val((parseFloat(totalSubAmount)).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseBillDiscountAmount").val((parseFloat(totalDiscount)).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseBillTotalVat").val((parseFloat(totalVat)).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseBillGrandTotalAmount").val((parseFloat(totalAmount)).toFixed(<?php echo $costDecimal; ?>));
    }
    
    function addServicePB(service_id, name, unit_cost, serviceCode, uomId){
        indexRowPB = Math.floor((Math.random() * 100000) + 1);
        var serviceID           = service_id;
        var productName         = name;
        var tr = cloneRowPB.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowPB);
        tr.find("td .purchaseUPC").val(serviceCode);
        tr.find("td .product_id").attr("id", "product_id"+indexRowPB).val('');
        tr.find("td .btnProductInfo").remove();
        tr.find("td .service_id").attr("id", "service_id"+indexRowPB).val(serviceID);
        tr.find("td .product_name").attr("id", "product_name"+indexRowPB).val(productName);
        tr.find("td .qty").attr("id", "qty_"+indexRowPB).val(1);
        tr.find("td .unit_cost").attr("id", "unit_cost"+indexRowPB).val(unit_cost);
        tr.find("td .defaltCost").val(unit_cost);
        tr.find("td .total_cost").attr("id", "total_cost"+indexRowPB).val(unit_cost);
        tr.find("td .h_total_cost").attr("id", "h_total_cost"+indexRowPB).val(unit_cost);
        tr.find("td .note").attr("id", "note"+indexRowPB).val("");
        tr.find("td .discountPB").attr("id", "discountPB"+indexRowPB).val(0);
        tr.find("td .lots_number").attr("id", "lots_number"+indexRowPB).removeAttr('class').css('visibility', 'hidden');
        tr.find("td .date_expired").attr("id", "date_expired"+indexRowPB).removeAttr('class').css('visibility', 'hidden');
        if(uomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowPB).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowPB).find("option[value='"+uomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+uomId+"']").hide();
        }
        $("#tblPB").append(tr);
        tr.find("td .qty").select().focus();
        
        pbTimeCode = 1;
        setIndexRowPB();
        checkEventPB();
        calcTotalPB();
        
    }
    
    function addProductPB(productId, sku, puc, name, isExpired, uomList, unitCost, smallUomVal, defaultCost, qtyOrder, uomSelected, isLots){        
        // Get Index Row
        indexRowPB = Math.floor((Math.random() * 100000) + 1);
        defaultCost = defaultCost>0?defaultCost:unitCost;
        var tr = cloneRowPB.clone(true);        
        tr.removeAttr("style").removeAttr("id");          
        tr.find("td:eq(0)").html(indexRowPB);
        tr.find("td .purchasePUC").val(puc);
        tr.find("td .product_id").attr("id", "product_id"+indexRowPB).val(productId);
        tr.find("td .product_name").attr("data", name);
        tr.find("td .product_name").attr("id", "product_name"+indexRowPB).val(name+"/"+smallUomVal);                   
        tr.find("td .small_uom_val_pb").attr("id", "small_uom_val_pb").val(smallUomVal);
        tr.find("td .qty_uom_id").attr("id", "qty_uom_id"+indexRowPB).html(uomList);
        tr.find("td .qty").attr("id", "qty_"+indexRowPB).val(qtyOrder);
        tr.find("td .qty_free").attr("id", "qty_free_"+indexRowPB).val(0);
        tr.find("td .defaltCost").val(defaultCost);
        tr.find("td .pb_conversion").val(smallUomVal);
        tr.find("td .lots_number").attr("id", "lots_number"+indexRowPB);
        tr.find("td .date_expired").attr("id", "date_expired"+indexRowPB);
        tr.find("td .discountPB").attr("id", "discountPB"+indexRowPB);
        tr.find("td .unit_cost").attr("id", "unit_cost"+indexRowPB).val(Number(unitCost).toFixed(<?php echo $costDecimal; ?>)); 
        tr.find("td .total_cost").attr("id", "total_cost"+indexRowPB).val(Number(unitCost).toFixed(<?php echo $costDecimal; ?>));
        tr.find("td .h_total_cost").attr("id", "h_total_cost"+indexRowPB).val(Number(unitCost).toFixed(<?php echo $costDecimal; ?>));
        if(isExpired == 1){
            tr.find("td input[name='date_expired[]']").addClass("validate[required]").val('');
        }else{
            tr.find("td input[name='date_expired[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0000-00-00');
        }
        if(isLots == 1){
            tr.find("td input[name='lots_number[]']").addClass("validate[required]").val('');
        }else{
            tr.find("td input[name='lots_number[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0');
        }
        // Get Uom Selected
        if(uomSelected != ''){
            tr.find("td .qty_uom_id").find("option[value='"+uomSelected+"']").attr('selected', 'selected');
        }
        $("#tblPB").append(tr);
        tr.find("td .qty").select().focus();
        
        pbTimeCode = 1;
        setIndexRowPB();
        checkEventPB();
        calcTotalPB();
    }
    
    function setIndexRowPB(){
        var sort = 1;
        $(".listBodyPB").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function checkVendorPB(field, rules, i, options){
        if($("#PurchaseBillVendorId").val() == "" || $("#PurchaseBillVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }
    
    function serachProCodePB(code, field, search, qtyOrder, uomSelected){
        if($("#PurchaseBillCompanyId").val() == "" || $("#PurchaseBillBranchId").val() == "" || $("#PurchaseBillLocationId").val() == ""){
            $(field).val('');
            pbTimeCode = 1;
            alertSelectRequireField();
        }else {
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/searchProductCode/"; ?>"+ $("#PurchaseBillCompanyId").val()+"/"+$("#PurchaseBillBranchId").val()+"/"+encodeURIComponent(code)+"/"+search,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".btnSavePurchaseBill").removeAttr('disabled');
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $(field).val('');
                    pbTimeCode = 1;
                    if(msg == '<?php echo TABLE_NO_PRODUCT; ?>'){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TABLE_NO_PRODUCT; ?></p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_WARNING; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            open: function(event, ui){
                                $(".ui-dialog-buttonpane").show();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    $(field).focus();
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }else{
                        var data = msg;
                        var skuUomId = "all";
                        var product  = $.parseJSON(data);
                        if(data){
                            $.ajax({
                                type: "GET",
                                url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+product[7]+"/"+skuUomId,
                                data: "",
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },                                                               
                                success: function(msg){
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                    var productId = product[0];
                                    var sku  = product[1];
                                    var puc  = product[2];
                                    var name = product[3];
                                    var isExpired   = product[4];
                                    var isLots      = product[9];
                                    var unitCost    = product[5];
                                    var smallUomVal = product[6];
                                    var defaultCost = 0;
                                    var packetList  = product[8];
                                    if(packetList != ''){
                                        var packet = packetList.toString().split("**");
                                        var loop = 1;
                                        var time = 0;
                                        $.each(packet,function(key, item){
                                            var items = item.toString().split("||");
                                            var productCode = items[0];
                                            var uomSelected = items[1];
                                            var qtyOrder    = items[2];
                                            if(loop > 1){
                                                time += 3500;
                                            }
                                            setTimeout(function () {
                                                serachProCodePB(productCode, field, search, qtyOrder, uomSelected);
                                            }, time);
                                            loop++;
                                        });
                                    }else{
                                        addProductPB(productId, sku, puc, name, isExpired, msg, unitCost, smallUomVal, defaultCost, qtyOrder, uomSelected, isLots);
                                    }
                                }
                            });
                        }
                    }
                }
            });
        }
    }
    
    function deleteVendorPB(){
        $("#PurchaseBillVendorId").val("");
        $("#PurchaseBillVendorName").val("");
        $("#PurchaseBillVendorName").removeAttr("readonly");
        $("#deleteSearchVendorPB").hide();
        $("#searchVendor").show();
    }
    
    function checkBfSavePB(){
        $("#PurchaseBillVendorName").removeClass("validate[required]");
        $("#PurchaseBillVendorName").addClass("validate[required,funcCall[checkVendorPB]]");
        var formName     = "#PurchaseBillAddForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            $("#PurchaseBillVendorName").removeClass("validate[required,funcCall[checkVendorPB]]");
            $("#PurchaseBillVendorName").addClass("validate[required]");
            return false;
        }else{
            if(($("#PurchaseBillTotalAmount").val() == undefined && $("#PurchaseBillTotalAmount").val() == "") || $(".listBodyPB").find(".product_id").val() == undefined){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an order first.</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
                $("#PurchaseBillVendorName").removeClass("validate[required,funcCall[checkVendorPB]]");
                $("#PurchaseBillVendorName").addClass("validate[required]");
                return false;
            }else{
                return true;
            }
        }
    }
    
    function searchProductPB(){
        if($("#PurchaseBillCompanyId").val() == "" || $("#PurchaseBillBranchId").val() == "" || $("#PurchaseBillLocationId").val() == "" || $("#PurchaseBillVendorId").val() == ""){
            pbTimeCode = 1;
            alertSelectRequireField();
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/product/"; ?>" + $("#PurchaseBillCompanyId").val()+"/"+$("#PurchaseBillBranchId").val()+"/"+$("#PurchaseBillLocationId").val()+"/"+$("#PurchaseBillVendorId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 550,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var data = $("input[name='chkProductPB']:checked");
                                if(data){
                                    var code = data.val();
                                    serachProCodePB(code, '#purchaseSearchSKU', 1, 1, '');
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function eventKeyRowPB(){
        loadAutoCompleteOff();
        $(".qty, .qty_free, .unit_cost, .qty_uom_id, .total_cost, .btnRemovePB, .noteAddPB, .btnDiscountPB, .btnRemoveDiscountPB, .btnProductInfo").unbind('keypress').unbind('keyup').unbind('change').unbind('click');
        $(".float").autoNumeric({mDec: <?php echo $costDecimal; ?>, aSep: ','});
        $(".qty, .qty_free").autoNumeric({mDec: 0, aSep: ','});
        
        $(".qty, .qty_free, .unit_cost").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qty, .qty_free, .unit_cost").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
        
        $(".total_cost").keyup(function(){
            var value = parseFloat(replaceNum($(this).val())=="" ? 0 : replaceNum($(this).val()));
            var unit_price = 0;
            var qty = parseFloat(replaceNum($(this).closest("tr").find(".qty").val())=="" ? 0 : replaceNum($(this).closest("tr").find(".qty").val()));
            var discountAmount = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val());
            var discountPercent = $(this).closest("tr").find("input[name='discount_percent[]']").val();
            
            if(discountAmount != 0 && discountAmount != ''){
                unit_price = converDicemalJS( (converDicemalJS( Number(value) + Number(discountAmount) )) / qty );
            }else if(discountPercent!=0 && discountPercent!=''){
                unit_price = converDicemalJS( ( converDicemalJS((converDicemalJS(Number(value) * 100)) / (converDicemalJS(100 - discountPercent))) ) / qty );
                var discount = converDicemalJS((converDicemalJS( (converDicemalJS(Number(value) * 100))  / (converDicemalJS(100 - discountPercent)) ) ) * (converDicemalJS(discountPercent / 100)));
                $(this).closest("tr").find(".discountPB").val(discount.toFixed(<?php echo $costDecimal; ?>));
            }else{
                unit_price = parseFloat(converDicemalJS(value / qty));
            }
            
            $(this).closest("tr").find(".h_total_cost").val(parseFloat(converDicemalJS(unit_price * qty)).toFixed(<?php echo $costDecimal; ?>));
            $(this).closest("tr").find(".unit_cost").val(unit_price.toFixed(6));
            calcTotalPB();
            
        });
        
        $(".qty, .qty_free").keyup(function(){
            var qty = replaceNum($(this).closest("tr").find("td .qty").val());
            var unitCost    = parseFloat(replaceNum($(this).closest("tr").find("td .unit_cost").val()!=""?$(this).closest("tr").find("td .unit_cost").val():0));
            var totalAmount = converDicemalJS(parseFloat(replaceNum(qty)) * unitCost);
            var discount    = 0;
            if(parseFloat($(this).closest("tr").find("input[name='discount_percent[]']").val()) > 0){
                discount = parseFloat(converDicemalJS( (converDicemalJS(totalAmount * $(this).closest("tr").find("input[name='discount_percent[]']").val()))/100 ));
                $(this).closest("tr").find("input[name='discount[]']").val((discount).toFixed(<?php echo $costDecimal; ?>));
            }else{
                discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()));
            }
            $(this).closest("tr").find("td .h_total_cost").val(totalAmount.toFixed(<?php echo $costDecimal; ?>));
            var totalCost = converDicemalJS(parseFloat(totalAmount - discount).toFixed(<?php echo $costDecimal; ?>));            
            $(this).closest("tr").find("td .total_cost").val(totalCost.toFixed(<?php echo $costDecimal; ?>));
            calcTotalPB();
        });
        
//        $(".unit_cost").keyup(function(){
//            var qty         = $(this).closest("tr").find("td .qty").val()!=""?replaceNum($(this).closest("tr").find("td .qty").val()):0;
//            var unitCost    = parseFloat(replaceNum($(this).closest("tr").find("td .unit_cost").val()!=""?$(this).closest("tr").find("td .unit_cost").val():0));
//            var totalAmount = converDicemalJS(parseFloat(replaceNum(qty)) * unitCost);
//            var discount    = 0;
//            if(parseFloat($(this).closest("tr").find("input[name='discount_percent[]']").val()) > 0){
//                discount = parseFloat(converDicemalJS( (converDicemalJS(totalAmount * $(this).closest("tr").find("input[name='discount_percent[]']").val()))/100 ));
//                $(this).closest("tr").find("input[name='discount[]']").val((discount).toFixed(<?php echo $costDecimal; ?>));
//            }else{
//                discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()));
//            }
//            $(this).closest("tr").find("td .h_total_cost").val(totalAmount.toFixed(<?php echo $costDecimal; ?>));
//            var totalCost = converDicemalJS(parseFloat(totalAmount - discount).toFixed(<?php echo $costDecimal; ?>));            
//            $(this).closest("tr").find("td .total_cost").val(totalCost.toFixed(<?php echo $costDecimal; ?>));
//            calcTotalPB();
//        });
        
        $(".qty").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $(this).closest("tr").find(".unit_cost").select().focus();
                }
                return false;
            }
        });
        
        $(".unit_cost").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $(this).closest("tr").find(".total_cost").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $(".productPO").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){                                                
            var value         = replaceNum($(this).val());
            var smallUomVal   = parseFloat($(this).closest("tr").find(".small_uom_val_pb").val());
            var uomConversion = converDicemalJS(smallUomVal / parseFloat(replaceNum($(this).find("option[value='"+value+"']").attr('conversion'))));
            var unit_price    = (parseFloat(converDicemalJS(replaceNum($(this).closest("tr").find(".defaltCost").val()) / parseFloat(replaceNum($(this).find("option[value='"+value+"']").attr('conversion')))))).toFixed(<?php echo $costDecimal; ?>);
            
            if($(this).closest("tr").find(".product_id").val() != ""){
                $(this).closest("tr").find(".unit_cost").val(unit_price); 
                var totalAmount = parseFloat( converDicemalJS(unit_price * replaceNum($(this).closest("tr").find(".qty").val())));
                if($(this).closest("tr").find("input[name='discount_percent[]']").val() != ""){
                    $(this).closest("tr").find("input[name='discount[]']").val(parseFloat(converDicemalJS((converDicemalJS(totalAmount * replaceNum($(this).closest("tr").find("input[name='discount_percent[]']").val()))) / 100 )).toFixed(<?php echo $costDecimal; ?>));
                }else{
                    var discountAmount = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val()) > 0 ? replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val()) : 0;
                    $(this).closest("tr").find("input[name='discount[]']").val(discountAmount.toFixed(<?php echo $costDecimal; ?>));
                }
                var discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()>0?$(this).closest("tr").find("input[name='discount[]']").val():0 ));
                $(this).closest("tr").find(".pb_conversion").val(uomConversion);
                $(this).closest("tr").find(".h_total_cost").val(totalAmount.toFixed(<?php echo $costDecimal; ?>));
                $(this).closest("tr").find(".total_cost").val( (converDicemalJS(totalAmount - discount)).toFixed(<?php echo $costDecimal; ?>) );                               
                // Set Name
                var nameDef = $(this).closest("tr").find(".product_name").attr("data");
                var nameCon = nameDef+"/"+uomConversion;
                $(this).closest("tr").find(".product_name").val(nameCon);
                calcTotalPB();                
            }
            
        });
        
        $(".btnRemovePB").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                position:'center',
                modal: true,
                width: '300',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        currentTr.remove();
                        calcTotalPB();
                        setIndexRowPB();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".btnDiscountPB").click(function(){
            var obj = $(this);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/invoiceDiscount"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo GENERAL_DISCOUNT; ?>',
                        resizable: false,
                        modal: true,
                        width: 450,
                        height: 180,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); 
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                obj.closest("tr").find("input[name='discount_id[]']").val(0);
                                var amount  = replaceNum($("#inputInvoiceDisAmt").val());
                                var percent = replaceNum($("#inputInvoiceDisPer").val());
                                var percenAmount = 0;
                                var totalUnit = parseFloat(replaceNum(obj.closest("tr").find(".unit_cost").val()));
                                if(amount){
                                    obj.closest("tr").find("input[name='discount_amount[]']").val(Number(amount).toFixed(<?php echo $costDecimal; ?>));
                                    obj.closest("tr").find("input[name='discount[]']").val(Number(amount).toFixed(<?php echo $costDecimal; ?>));
                                    obj.closest("tr").find(".total_cost").val(parseFloat( converDicemalJS(converDicemalJS(replaceNum(obj.closest("tr").find("input[name='qty[]']").val()) * totalUnit) - amount) ).toFixed(<?php echo $costDecimal; ?>));
                                }else{
                                    percenAmount = parseFloat( (converDicemalJS(percent * replaceNum(obj.closest("tr").find("input[name='qty[]']").val()) * totalUnit)) /100 );
                                    obj.closest("tr").find("input[name='discount_percent[]']").val(percent);
                                    obj.closest("tr").find("input[name='discount_amount[]']").val(percenAmount.toFixed(<?php echo $costDecimal; ?>));
                                    obj.closest("tr").find("input[name='discount[]']").val(percenAmount.toFixed(<?php echo $costDecimal; ?>));
                                    obj.closest("tr").find(".total_cost").val(parseFloat(converDicemalJS((converDicemalJS(replaceNum(obj.closest("tr").find("input[name='qty[]']").val())*totalUnit)) - percenAmount)).toFixed(<?php echo $costDecimal; ?>));
                                }
                                obj.closest("tr").find("input[name='discount[]']").css("display", "inline");
                                obj.closest("tr").find(".btnRemoveDiscountPB").css("display", "inline");
                                calcTotalPB();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
            
        $(".btnRemoveDiscountPB").click(function(){
            var discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()));
            var totalAmount = parseFloat(replaceNum($(this).closest("tr").find(".total_cost").val()));
            $(this).closest("tr").find("input[name='discount_id[]']").val("");
            $(this).closest("tr").find("input[name='discount_amount[]']").val(0);
            $(this).closest("tr").find("input[name='discount_percent[]']").val(0);
            $(this).closest("tr").find("input[name='discount[]']").val('0');
            $(this).closest("tr").find(".btnRemoveDiscountPB").css("display", "none");
            $(this).closest("tr").find(".total_cost").val(parseFloat(converDicemalJS(totalAmount + discount)).toFixed(<?php echo $costDecimal; ?>));
            calcTotalPB();
        });
        
        $(".noteAddPB").click(function(){
            addNotePB($(this));
        });
        
        // Button Show Information
        $(".btnProductInfo").click(function(){
            showProductInfoPB($(this));
        });
        <?php
        if($allowExpired == true){
        ?>
        $('.date_expired').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        <?php
        }
        ?>
    }
    
    function checkEventPB(){
        eventKeyRowPB();
        $(".listBodyPB").unbind("click");
        $(".listBodyPB").click(function(){
            eventKeyRowPB();
        });
    }
    
    function searchVendorPB(){
        var companyId = $("#PurchaseBillCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/purchase_bills/vendor/"; ?>"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo MENU_VENDOR; ?>',
                    resizable: false,
                    modal: true,
                    width: 850,
                    height: 500,
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        pbTimeCode = 1;
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            // calculate due_date
                            var data = $("input[name='chkVendor']:checked").val();
                            if(data){
                                // Set Vendor
                                $("#PurchaseBillVendorId").val(data.split('-')[0]);
                                $("#PurchaseBillVendorName").val(data.split('-')[1]+" - "+data.split('-')[2]);
                                $("#PurchaseBillVendorName").attr('readonly', true);
                                $("#searchVendor").hide();
                                $("#deleteSearchVendorPB").show();
                            }
                            pbTimeCode = 1;
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        loadAutoCompleteOff();
        // Hide Branch
        $("#PurchaseBillLocationGroupId").chosen({width: 190});
        // Remove Clone Row List
        $("#detailPB").remove();
        
        var waitForFinalEventPB = (function () {
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
        
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabPBReg != tabPBId){
            $("a[href='"+tabPBId+"']").click(function(){
                if($("#bodyListPB").html() != '' && $("#bodyListPB").html() != null){
                    waitForFinalEventPB(function(){
                        refreshScreenPB();
                        resizeFormTitlePB();
                        resizeFornScrollPB();  
                    }, 500, "Finish");
                }
            });
            tabPBReg = tabPBId;
        }

        waitForFinalEventPB(function(){
              refreshScreenPB();
              resizeFormTitlePB();
              resizeFornScrollPB();  
            }, 500, "Finish");
            
        $(window).resize(function(){
            if(tabPBReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventPB(function(){
                    refreshScreenPB();
                    resizeFormTitlePB();
                    resizeFornScrollPB();  
                  }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderPurchaseBill").click(function(){
            var PurchaseBillCompanyId       = $("#PurchaseBillCompanyId").val();
            var PurchaseBillBranchId        = $("#PurchaseBillBranchId").val();
            var PurchaseBillLocationGroupId = $("#PurchaseBillLocationGroupId").val();
            var PurchaseBillLocationId      = $("#PurchaseBillLocationId").val();
            var PurchaseBillOrderDate       = $("#PurchaseBillOrderDate").val();
            var PurchaseBillVendorId        = $("#PurchaseBillVendorId").val();
            
            if(PurchaseBillCompanyId == "" || PurchaseBillBranchId == "" || PurchaseBillLocationGroupId == "" || PurchaseBillLocationId == "" || PurchaseBillOrderDate == "" || PurchaseBillVendorId == ""){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_WARNING; ?>',
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
            }else{
                var label  = $(this).find("span").text();
                var action = '';
                var img    = '<?php echo $this->webroot . 'img/button/'; ?>';
                if(label == 'Hide'){
                    action = 'Show';
                    $("#PBTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#PBTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabPBReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventPB(function(){
                        resizeFornScrollPB();
                    }, 300, "Finish");
                }
            }
        });
        
        // Form Validate
        $("#PurchaseBillAddForm").validationEngine('detach');
        $("#PurchaseBillAddForm").validationEngine('attach');
        
        $(".btnSavePurchaseBill").click(function(){
            if(checkBfSavePB() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_SAVE; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 300,
                    height: 'auto',
                    position:'center',
                    closeOnEscape: false,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").hide();
                    },
                    buttons: {
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_YES; ?>': function() {
                            // Set Preview
                            $("#PurchaseBillIsPreview").val('0');
                            // Action Click Save
                            $("#PurchaseBillAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        
        $(".btnSavePreviewPurchaseBill").click(function(){
            if(checkBfSavePB() == true){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SAVE_BEFORE_PREVIEW; ?></p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    pprsition:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_YES; ?>': function() {
                            // Set Preview
                            $("#PurchaseBillIsPreview").val('1');
                            // Form Submit
                            $("#PurchaseBillAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        
        $("#PurchaseBillAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#PurchaseBillIsPreview").val() == '1'){
                    $(".txtSavePreviewPB").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSavePB").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#PurchaseBillOrderDate, .date_expired").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .qty, .floatCost").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#PurchaseBillTotalAmount").val($("#PurchaseBillTotalAmount").val().replace(/,/g,""));
                $(".btnSavePurchaseBill, .btnSavePreviewPurchaseBill").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Purchase Bill', 'Add', 2, result.responseText);
                backPurchaseBill();
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
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
                            $("meta[http-equiv='refresh']").attr('content','0');
                            $(this).dialog("close");
                        }
                    }
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.code == "1"){
                    codeDialogPB();
                }else if(result.code == "2"){
                    errorSavePB();
                }else{
                    createSysAct('Purchase Bill', 'Add', 1, '');
                    if($("#PurchaseBillIsPreview").val() == '1'){
                        // Preview
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.po_id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printInvoicePBResult){
                                var w = window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printInvoicePBResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                        // Reset Normal
                        $("#PurchaseBillPreviewId").val(result.po_id);
                        $(".txtSavePreviewPB").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".btnSavePurchaseBill, .btnSavePreviewPurchaseBill").attr("disabled", false);
                        $("#PurchaseBillOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        saveContiunePurchaseBill();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoicePB" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_PURCHASE_BILL; ?></span></button><button type="submit" class="positive printInvoiceProPB" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_ONLY_PRODUCT; ?></span></button></div> ');
                        $(".printInvoicePB").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.po_id,
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(printInvoicePBResult){
                                    var w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write(printInvoicePBResult);
                                    w.document.close();
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                }
                            });
                        });
                        $(".printInvoiceProPB").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoiceProduct/"+result.po_id,
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(printInvoicePBResult){
                                    var w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write(printInvoicePBResult);
                                    w.document.close();
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                }
                            });
                        });

                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            position:'center',
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
            }
        });
        
        if($.cookie('PurchaseBillLocationGroupId')!=null || $("#PurchaseBillLocationGroupId").find("option:selected").val() != ''){
            if($.cookie('companyIdTransferOrder') != null){
                $("#PurchaseBillLocationGroupId").val($.cookie('PurchaseBillLocationGroupId'));
            }
            checkLocationByGroupPB();
        }
        
        $("#PurchaseBillLocationGroupId").change(function(){
            var obj = $(this);
            $.cookie('PurchaseBillLocationGroupId', obj.val(), { expires: 7, path: "/" });
            checkLocationByGroupPB();
        });
        
        <?php
        if(count($locationGroups) == 1){
        ?>
        checkLocationByGroupPB();
        <?php
        }
        ?>
        // Action Vendor
        
        $("#searchVendor").click(function(){
            if(checkOrderDatePB() == true && $("#PurchaseBillCompanyId").val() != ''){
                searchVendorPB();
            }
        });
        
        $("#deleteSearchVendorPB").click(function(){
            deleteVendorPB();
        });
        
        $("#PurchaseBillVendorName").focus(function(){
            checkOrderDatePB();
        });
        
        $("#PurchaseBillVendorName").keypress(function(e){
            if((e.which && e.which != 13) || e.keyCode != 13){
                $("#PurchaseBillVendorId").val("");
            }
        });
        
        $("#PurchaseBillVendorName").autocomplete("<?php echo $this->base . "/purchase_bills/searchVendor"; ?>", {
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
            // Set Vendor
            $("#PurchaseBillVendorId").val(value.toString().split(".*")[0]);
            $("#PurchaseBillVendorName").val(value.toString().split(".*")[2] + " - " + value.toString().split(".*")[1]);
            $("#PurchaseBillVendorName").attr('readonly', true);
            $("#searchVendor").hide();
            $("#deleteSearchVendorPB").show();
        });
        // End Action Vendor
        
        // Action Scan/Search Product
        $("#purchaseSearchSKU").autocomplete("<?php echo $this->base . "/purchase_bills/searchProduct/"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $(".productPO").val(code);
            if(pbTimeCode == 1){
                pbTimeCode = 2;
                serachProCodePB(code, '#purchaseSearchSKU', 1, 1, '');
            }
        });
        
        $("#purchaseSearchProduct").click(function(){
            searchProductPB();
        });

        $("#purchaseSearchSKU").keypress(function(e){
            var code =null;
            var obj = $(this);
            code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13){
                if($("#PurchaseBillCompanyId").val() ==""){
                    $(this).val('');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_FIRST; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_WARNING; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
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
                    if($(this).val() != ""){
                        if(pbTimeCode == 1){
                            pbTimeCode = 2;
                            serachProCodePB($(this).val(), '#purchaseSearchSKU', 1, 1, '');
                        }
                    }
                }
                return false;
            }
        });
        
        // End Action Scan/Search Product
        
        $(".btnBackPurchaseBill").click(function(event){
            event.preventDefault();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_WANT_TO_BACK; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_YES; ?>': function() {
                        $(this).dialog("close");
                        backPurchaseBill();
                    }
                }
            });
        });

        $('#PurchaseBillOrderDate, #PurchaseBillInvoiceDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#PurchaseBillOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#PurchaseBillOrderDate").datepicker("option", "maxDate", 0);
        
        $(".addServicePB").click(function(){
            searchAllServicePB();
        });
        
        // Action Branch
        $("#PurchaseBillBranchId").change(function(){
            var obj = $(this);
            if($(".listBodyPB").find(".product_id").val() == undefined){
                $.cookie('branchIdPurchaseBill', obj.val(), { expires: 7, path: "/" });
                branchChangePurchaseBill(obj);
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_CHANGE_BRANCH; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('branchIdPurchaseBill', obj.val(), { expires: 7, path: "/" });
                            branchChangePurchaseBill(obj);
                            $("#tblPB").html('');
                            calcTotalPB();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseBillBranchId").val($.cookie("branchIdPurchaseBill"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Company
        if($.cookie('companyIdPB') != null || $("#PurchaseBillCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdSales') != null){
                $("#PurchaseBillCompanyId").val($.cookie('companyIdPB'));
            }
            var vatCal = $("#PurchaseBillCompanyId").find("option:selected").attr("vat-opt");
            $("#PurchaseBillVatCalculate").val(vatCal);
            checkVatCompanyPB();
            $("#PurchaseBillBranchId").filterOptions('com', $("#PurchaseBillCompanyId").val(), '');
            $("#PurchaseBillBranchId").change();
        }
        // Action VAT Status
        $("#PurchaseBillVatSettingId").change(function(){
            checkVatSelectedPB();
            calcTotalPB();
        });
        
        <?php if($allowEditInvDis){ ?>
        // Action Total Discount Amount
        $("#PurchaseBillDiscountAmount").click(function(){
            getTotalDiscountPB();
        });
        
        
        $("#btnRemovePBTotalDiscount").click(function(){
            $("#PurchaseBillDiscountAmount").val(0);
            $("#PurchaseBillDiscountPercent").val(0);
            $(this).hide();
            $("#PBLabelDisPercent").html('');
            calcTotalPB();
        });
        <?php } ?>
        
        <?php
        if($allowAddVendor){
        ?>
        $("#addVendorPurchaseBill").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/quickAdd/"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog3").html(msg);
                    $("#dialog3").dialog({
                        title: '<?php echo MENU_VENDOR_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '700',
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
                                var formName = "#VendorQuickAddForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    if($("#VendorVgroupId").val() == null || $("#VendorVgroupId").val() == '' || $("#VendorPaymentTermId").val() == '' || $("#VendorPaymentTermId").val() == ''){
                                        alertSelectRequireField();
                                    } else {
                                        $(this).dialog("close");
                                        $.ajax({
                                            dataType: 'json',
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/vendors/quickAdd",
                                            data: $("#VendorQuickAddForm").serialize(),
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            error: function (result) {
                                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                                createSysAct('Purchase Bill', 'Quick Add Vendor', 2, result);
                                                $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                $("#dialog1").dialog({
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
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                createSysAct('Purchase Bill', 'Quick Add Vendor', 1, '');
                                                var msg = '';
                                                if(result.error == 0){
                                                    msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                                    // Set Vendor
                                                    $("#PurchaseBillVendorId").val(result.id);
                                                    $("#PurchaseBillVendorName").val(result.name);
                                                    $("#PurchaseBillVendorName").attr("readonly", true);
                                                    $("#searchVendor").hide();
                                                    $("#deleteSearchVendorPB").show();
                                                    $("#PurchaseBillAddForm").validationEngine("hideAll");
                                                } else  if (result.error == 1){
                                                    msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'; 
                                                } else  if (result.error == 2){
                                                    msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                                }
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
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
        });
        <?php
        }
        if($allowAddProduct){
        ?>
        $("#addProductPurchaseBill").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/quickAdd/"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog3").html(msg);
                    $("#dialog3").dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '900',
                        height: '600',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#ProductQuickAddForm";
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
                                        var dataPost = $("#ProductQuickAddForm").serialize()+"&"+$('#formBranchProductQuick').serialize();
                                        $.ajax({
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/products/quickAdd",
                                            data: dataPost,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                // Message Alert
                                                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                                                    createSysAct('Product', 'Quick Add', 2, result);
                                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                }else {
                                                    createSysAct('Product', 'Quick Add', 1, '');
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
        });
        <?php
        }
        if($allowPO == true){ ?>    
        $("#searchPONumber").click(function(){
            searchPurchaseRequest();
        });
        
        $("#deletePONumber").click(function(){
            removePurchaseRequest();
        });
        <?php
        }
        ?>
        resetFormPB();
        checkVatCompanyPB();
        checkOrderDatePB();
        changeInputCSSPB();
    });
    <?php if($allowPO == true){ ?>
    function searchPurchaseRequest(){
        var companyId = $("#PurchaseBillCompanyId").val();
        var branchId  = $("#PurchaseBillBranchId").val();
        if(companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/purchaseRequest/"; ?>"+companyId+"/"+branchId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PURCHASE_REQUEST; ?>',
                        resizable: false,
                        modal: true,
                        width: 850,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(){
                            pbTimeCode = 1;
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var data = $("input[name='chkPurchaseRequestPO']:checked").val();
                                if(data){
                                    var id     = $("input[name='chkPurchaseRequestPO']:checked").val();
                                    var poCode = $("input[name='chkPurchaseRequestPO']:checked").attr('po-code');
                                    var poDate = $("input[name='chkPurchaseRequestPO']:checked").attr('date');
                                    var vendorId = $("input[name='chkPurchaseRequestPO']:checked").attr('vendor-id');
                                    var vendor   = $("input[name='chkPurchaseRequestPO']:checked").attr('vendor-name');
                                    var totalDpt = $("input[name='chkPurchaseRequestPO']:checked").attr('deposit');
                                    var vatId    = $("input[name='chkPurchaseRequestPO']:checked").attr('vat-id');
                                    var shipId   = $("input[name='chkPurchaseRequestPO']:checked").attr('ship-id');
                                    // Set Purchase Order
                                    $("#PurchaseBillPurchaseRequestId").val(id);                        
                                    $("#PurchaseBillPoNo").val(poCode);
                                    $("#PurchaseBillPoDate").val(poDate);
                                    $("#PurchaseBillPoNo").attr('readonly', 'readonly');
                                    $("#searchPONumber").hide();
                                    $("#deletePONumber").show();
                                    // Set Vendor
                                    $("#PurchaseBillVendorId").val(vendorId);
                                    $("#PurchaseBillVendorName").val(vendor);
                                    $("#PurchaseBillVendorName").attr('readonly', true);
                                    $("#searchVendor").hide();
                                    $("#deleteSearchVendorPB").show();
                                    // Shipment
                                    $("#PurchaseBillShipmentId").val(shipId);
                                    // Update Deposit
                                    $("#PurchaseBillTotalDeposit").val(totalDpt);
                                    // Vat Setting
                                    $("#PurchaseBillVatSettingId").val(vatId);
                                    $.ajax({
                                        dataType: 'json',
                                        type: "POST",
                                        url:    "<?php echo $this->base . "/purchase_bills/getProductsFromPO/"; ?>"+id,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(msg.result != '0' && msg.result != ''){
                                                var rowList = msg.result;
                                                $("#tblPB").append(rowList);
                                                setIndexRowPB();
                                                checkEventPB();
                                                calcTotalPB();
                                            }
                                        }
                                    });
                                }
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function removePurchaseRequest(){
        $("#PurchaseBillPurchaseRequestId").val('');                        
        $("#PurchaseBillPoNo").val('');
        $("#PurchaseBillPoDate").val('');
        $("#PurchaseBillPoNo").removeAttr('readonly');
        $("#searchPONumber").show();
        $("#deletePONumber").hide();
        // Reset Total Deposit
        $("#PurchaseBillTotalDeposit").val(0);
    }
    <?php } ?>

    function branchChangePurchaseBill(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#PurchaseBillPoCode").val("GRN-"+mCode+"-<?php echo date("ym"); ?>-");
        $("#PurchaseBillCurrencyId").val(currency);
        $(".lblSymbolPB").html(currencySymbol);
    }
    
    function getTotalDiscountPB(){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/purchase_bills/invoiceDiscount"; ?>",
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo GENERAL_DISCOUNT; ?>',
                    resizable: false,
                    modal: true,
                    width: 450,
                    height: 180,
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            var totalDisAmt     = replaceNum($("#inputInvoiceDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputInvoiceDisPer").val());
                            $("#PurchaseBillDiscountAmount").val(totalDisAmt);
                            $("#PurchaseBillDiscountPercent").val(totalDisPercent);
                            calcTotalPB();
                            if(totalDisPercent > 0){
                                $("#PBLabelDisPercent").html('('+totalDisPercent+'%)');
                            } else {
                                $("#PBLabelDisPercent").html('');
                            }
                            if(totalDisAmt > 0 || totalDisPercent > 0){
                                $("#btnRemovePBTotalDiscount").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function changeLblVatCalPB(){
        var vatCal = $("#PurchaseBillVatCalculate").val();
        $("#lblPurchaseBillVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblPurchaseBillVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblPurchaseBillVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedPB(){
        var vatPercent = replaceNum($("#PurchaseBillVatSettingId").find("option:selected").attr("rate"));
        var vatAccId   = replaceNum($("#PurchaseBillVatSettingId").find("option:selected").attr("acc"));
        $("#PurchaseBillVatPercent").val((vatPercent).toFixed(2));
        $("#PurchaseBillVatChartAccountId").val(vatAccId);
    }
    
    function checkVatCompanyPB(){
        // VAT Filter
        $("#PurchaseBillVatSettingId").filterOptions('com-id', $("#PurchaseBillCompanyId").val(), '');
    }
    
    function resetFormPB(){
        // Vendor
        $("#deleteSearchVendorPB").click();
        // Purchase Order
        $("#deletePONumber").click();
    }
    
    function reloadPagePB(){
        var rightPanel = $(".btnBackPurchaseBill").parent().parent().parent().parent().parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
    }
    
    function checkOrderDatePB(){
        if($("#PurchaseBillOrderDate").val() == ""){
            $("#PurchaseBillOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    function searchAllServicePB(){
        if($("#PurchaseBillCompanyId").val()=="" || $("#PurchaseBillBranchId").val()==""){
            pbTimeCode == 1;
            alertSelectRequireField();
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/service"; ?>/"+$("#PurchaseBillCompanyId").val()+"/"+$("#PurchaseBillBranchId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    pbTimeCode == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_ADD_SERVICE; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var formName = "#ServiceServiceForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    addServicePB($("#ServiceServiceId").val(),$("#ServiceServiceId").find("option:selected").attr("abbr"),$("#ServiceUnitPrice").val(),$("#ServiceServiceId").find("option:selected").attr("scode"),$("#ServiceServiceId").find("option:selected").attr("suom"));
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function checkLocationByGroupPB(){
        var locationGroup = $("#PurchaseBillLocationGroupId").val();
        $("#PurchaseBillLocationId").filterOptions('location-group', locationGroup, '');
    }
    
    function codeDialogPB(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                $(".btnSavePurchaseBill").removeAttr("disabled");
                $(".txtSavePB").html("<?php echo ACTION_SAVE; ?>");
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".btnSavePurchaseBill").removeAttr("disabled");
                    $(".txtSavePB").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSavePB(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                backPurchaseBill();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function addNotePB(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteCommentPO'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_MEMO; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    note.val($("#noteCommentPO").val());
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function backPurchaseBill(){
        oCache.iCacheLower = -1;
        oPBTable.fnDraw(false);
        $("#PurchaseBillAddForm").validationEngine("hideAll");
        var rightPanel = $("#PurchaseBillAddForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        $("#"+PbTableName).find("tbody").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
        rightPanel.hide("slide", { direction: "right" }, 500, function(){
            leftPanel.show();
            rightPanel.html("");
        });
    }
    
    function saveContiunePurchaseBill(){
        $("#PurchaseBillAddForm").validationEngine("hideAll");
        var rightPanel = $("#PurchaseBillAddForm").parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
    }
    
    function changeInputCSSPB(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchVendor").hide();
        $(".searchPONumber").hide();
        $("#divSearchPB").css("visibility", "hidden");
        if($("#PurchaseBillCompanyId").val() != ''){
            var currencySymbol = $("#PurchaseBillCompanyId").find("option:selected").attr("symbol");
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#PurchaseBillVendorName").val() == ''){
                $(".searchVendor").show();
            }
            if($("#PurchaseBillPoNo").val() == ''){
                $(".searchPONumber").show();
            }
            $("#divSearchPB").css("visibility", "visible");
            $(".lblSymbolPB").html(currencySymbol);
            $("#companySymbolPurchase").html(currencySymbol);
        } else {
            $(".lblSymbolPB").html('');
            $("#companySymbolPurchase").html('');
        } 
        // Label
        $("#PurchaseBillAddForm").find("label").removeAttr("class");
        $("#PurchaseBillAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'PurchaseBillCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#PurchaseBillAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#PurchaseBillAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'PurchaseBillCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolPB").removeClass(cssRemove);
        $(".lblSymbolPB").addClass(cssStyle);
        $(".lblSymbolPBPercent").removeClass(cssRemove);
        $(".lblSymbolPBPercent").addClass(cssStyle);
        // Input Readonly
        $("#PurchaseBillVendorName").attr("readonly", readonly);
        $("#PurchaseBillPoNo").attr("readonly", readonly);
        $("#PurchaseBillNote").attr("readonly", readonly);
        $("#purchaseSearchSKU").attr("readonly", readonly);
        // Put label VAT Calculate
        changeLblVatCalPB();
        // Check VAT Default
        getDefaultVatPB();
    }
    
    function getDefaultVatPB(){
        var vatDefault = $("#PurchaseBillCompanyId option:selected").attr("vat-d");
        $("#PurchaseBillVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedPB();
    }
    
    function showProductInfoPB(currentTr){
        var vendorId  = $("#PurchaseBillVendorId").val();
        var productId = currentTr.closest("tr").find(".product_id").val();
        if(productId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_bills/productHistory"; ?>/"+productId+"/"+vendorId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 1200,
                        height: 550,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); 
                            $(".ui-dialog-titlebar-close").show();
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
</script>
<?php echo $this->Form->create('PurchaseBill'); ?>
<input type="hidden" value="" name="data[PurchaseBill][preview_id]" id="PurchaseBillPreviewId" />
<input type="hidden" value="0" id="PurchaseBillIsPreview" />
<input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
<input type="hidden" name="data[total_deposit]" id="PurchaseBillTotalDeposit" />
<input type="hidden" value="" name="data[PurchaseBill][vat_calculate]" id="PurchaseBillVatCalculate" />
<input type="hidden" value="" name="data[PurchaseBill][currency_id]" id="PurchaseBillCurrencyId" />
<input type="hidden" value="" name="data[PurchaseBill][exchange_rate_id]" id="PurchaseBillExchangeRateId" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderPurchaseBill">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<div id="PBTop">
    <fieldset>
        <legend><?php __(MENU_PURCHASE_ORDER_MANAGEMENT_INFO); ?></legend>
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 50%">
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%"><label for="PurchaseBillOrderDate"><?php echo TABLE_PB_DATE; ?></label> <span class="red">*</span></td>
                            <td style="width: 33%"><label for="PurchaseBillPoCode"><?php echo TABLE_PB_NUMBER; ?></label> <span class="red">*</span></td>
                            <td style="width: 33%"><?php if($allowPO == true){ ?><label for="PurchaseBillPoNo"><?php echo TABLE_PO_NUMBER; ?></label><?php } ?></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('order_date', array('value' => date("d/m/Y"), 'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:70%')); ?>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('po_code', array('style' => 'width:70%', 'class' => 'validate[required]', 'readonly' => TRUE)); ?>
                                </div>
                            </td>
                            <td>
                                <?php if($allowPO == true){ ?>
                                <div class="inputContainer" style="width:100%;">
                                    <input type="hidden" name="data[PurchaseBill][purchase_request_id]" id="PurchaseBillPurchaseRequestId" />
                                    <?php echo $this->Form->text('po_no', array('style' => 'width:70%')); ?>
                                    &nbsp;&nbsp; <img alt="Search" align="absmiddle" style="cursor: pointer; width:22px; height: 22px;" id="searchPONumber" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                    <img alt="Delete" align="absmiddle" id="deletePONumber" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" style="display:none; cursor: pointer;" />
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 35%;">
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 50%"><?php if(count($companies) > 1){ ?><label for="PurchaseBillCompanyId"><?php echo TABLE_COMPANY; ?></label> <span class="red">*</span><?php } ?></td>
                            <td><?php if(count($locationGroups) > 1){ ?><label for="PurchaseBillLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?></label> <span class="red">*</span><?php } ?></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                                    <select name="data[PurchaseBill][company_id]" id="PurchaseBillCompanyId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($companies) != 1){
                                        ?>
                                        <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($companies AS $company){
                                            $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 21 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                            $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                        ?>
                                        <option vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%; <?php if(count($locationGroups) == 1){ ?>display: none;<?php } ?>">
                                    <?php 
                                    $emptyWare = INPUT_SELECT;
                                    if(count($locationGroups) == 1){
                                        $emptyWare = false;
                                    }
                                    echo $this->Form->input('location_group_id', array('empty' => $emptyWare, 'label' => false)); 
                                    ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2" style="vertical-align: top;">
                    <table cellpadding="0" style="width: 100%;">
                        <tr>
                            <td><label for="PurchaseBillNote"><?php echo TABLE_MEMO; ?></label></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->input('note', array('style' => 'width:90%; height: 65px;', 'label' => false)); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr> 
            <tr>
                <td>
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td colspan="2"><label for="PurchaseBillVendorName"><?php echo TABLE_VENDOR; ?></label> <span class="red">*</span></td>
                            <td style="width: 33%"><label for="PurchaseBillInvoiceCode"><?php echo TABLE_INVOICE_CODE; ?></label></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="inputContainer" style="width:100%">
                                    <?php
                                    echo $this->Form->hidden('vendor_id');
                                    if($allowAddVendor){
                                    ?>
                                    <div class="addnewSmall" style="float: left;">
                                        <?php echo $this->Form->text('vendor_name', array('class' => 'validate[required]', 'style' => 'width: 285px; border: none;')); ?>
                                        <img alt="<?php echo MENU_VENDOR_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addVendorPurchaseBill" onmouseover="Tip('<?php echo MENU_VENDOR_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                    </div>
                                    <?php
                                    } else {
                                        echo $this->Form->text('vendor_name', array('class' => 'validate[required]', 'style' => 'width:320px'));
                                    }
                                    ?>
                                    &nbsp;&nbsp;<img alt="<?php echo TABLE_SHOW_VENDOR_LIST; ?>" align="absmiddle" style="cursor: pointer; width:22px; height: 22px;" id="searchVendor" onmouseover="Tip('<?php echo TABLE_SHOW_VENDOR_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                    <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" id="deleteSearchVendorPB" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" style="display:none; cursor: pointer; height: 22px;" />
                                    <div style="clear: both;"></div>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('invoice_code', array('style' => 'width:70%;', 'label' => false)); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top;">
                    <table cellpadding="0" style="width: 100%;">
                        <tr>
                            <td style="width: 50%;"><?php if(count($branches) > 1){ ?><label for="PurchaseBillBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                            <td><?php if(count($locations) > 1){ ?><label for="PurchaseBillLocationId"><?php echo TABLE_LOCATION; ?></label> <span class="red">*</span><?php } ?></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                    <select name="data[PurchaseBill][branch_id]" id="PurchaseBillBranchId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($branches) != 1){
                                        ?>
                                        <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($branches AS $branch){
                                        ?>
                                        <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['pb_code']; ?>" currency="<?php echo $branch['Branch']['currency_id']; ?>" symbol="<?php echo $branch['Currency']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%; <?php if(count($locations) == 1){ ?>display: none;<?php } ?>">
                                    <select name="data[PurchaseBill][location_id]" id="PurchaseBillLocationId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($locations) != 1){
                                        ?>
                                        <option value="" location-group="0"><?php echo INPUT_SELECT; ?></option>
                                        <?php 
                                        }
                                        foreach($locations AS $location){
                                        ?>
                                            <option value="<?php echo $location['Location']['id']; ?>" location-group="<?php echo $location['Location']['location_group_id']; ?>"><?php echo $location['Location']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="inputContainer" style="width:100%" id="searchFormPB">
    <table width="100%">
        <tr>
            <td style="width: 400px; text-align: left;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="purchaseSearchSKU" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductPurchaseBill" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="purchaseSearchSKU" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td id="divSearchPB" style="width: 200px; text-align: left;">
                &nbsp;&nbsp;<img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer;" id="purchaseSearchProduct" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                <?php
                if ($allowAddService) {
                ?>
                <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServicePB" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" /> 
                <?php
                }
                ?>
            </td>
            <td style="text-align: right;">
                <div id="exchangePurchase" style="display: none;">Exchange: 1<span id="companySymbolPurchase"></span> = <span id="divRatePurchase"></span></div>
            </td>
        </tr>
    </table>
</div>
<div id="hiddenUom" style="display: none"></div>
<table id="tblHeaderPB" class="table" cellspacing="0" style="padding:0px;">
    <tr>
        <th class="first" style="width:4%"><?php echo TABLE_NO; ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:17%;"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:9%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:8%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:8%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXP_DATE_SHORT; ?></th>
        <th style="width:5%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:5%;"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:8%;"><?php echo TABLE_UNIT_COST; ?></th>
        <th style="width:7%;"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:7%;"><?php echo TABLE_TOTAL; ?></th>
        <th style="width:5%;"></th>
    </tr>
</table>
<div id="bodyListPB" style="padding:0px;">
    <table id="tblPB" class="table" cellspacing="0" style="padding:0px;">
        <tr id="detailPB" class="listBodyPB" style="visibility: hidden;">
            <td class="first" style="width:4%"></td>
            <td style="width:10%"><input type="text" readonly="" style="width: 95%; height: 25px;" class="purchasePUC" /></td>
            <td style="width:17%">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" id="product_id" />
                    <input type="hidden" name="service_id[]" class="service_id" id="service_id" />
                    <input type="hidden" name="max_order[]" class="max_order" />
                    <input type="hidden" name="note[]" class="note" id="note" />
                    <input type="text" data="" name="product_name[]" class="product_name validate[required]" id="product_name" readonly="readonly" style="width: 75%; height: 25px;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddPB" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:9%;">
                <div class="inputContainer" style="width:100%">         
                    <input type="hidden" class="small_uom_val_pb" name="small_uom_val_pb[]"/> 
                    <input type="hidden" class="pb_conversion" name="pb_conversion[]"/>                                        
                    <select id="qty_uom_id" name="qty_uom_id[]" style="width:80%; height: 25px;" class="qty_uom_id validate[required]">
                        <?php
                        foreach ($uoms as $uom) {
                            echo "<option value='{$uom['Uom']['id']}' conversion='1'>{$uom['Uom']['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="lots_number[]" id="lots_number" style="width:80%; height: 25px;" class="lots_number" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="date_expired[]" id="date_expired" style="width:80%; height: 25px;" class="date_expired" readonly="" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:5%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:80%; height: 25px;" class="qty validate[required]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:5%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" style="width:80%; height: 25px;" class="qty_free" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="defaltCost" />
                    <input type="text" id="unit_cost" name="unit_cost[]" class="unit_cost validate[required] float" style="width:80%; height: 25px;" readonly="" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:7%;">
                <div class="inputContainer" style="width:100%">
                    <div style="white-space: nowrap; margin-top: 3px; width: 100%">
                        <input type="hidden" name="discount_id[]" />
                        <input type="hidden" name="discount_amount[]" />
                        <input type="hidden" name="discount_percent[]" />
                        <?php
                        if($allowDiscount){
                        ?>
                        <input type="text" name="discount[]" value="0" class="discountPB btnDiscountPB float" readonly="readonly" id="discountPB" style="width: 70%; height: 25px;" />
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountPB" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                        <?php
                        }else{
                        ?>
                        <input type="hidden" name="discount[]" value="0" class="discountPB btnDiscountPB float" readonly="readonly" id="discountPB" />
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:7%;">
                <input type="hidden" id="h_total_cost" class="h_total_cost float" name="h_total_cost[]" />
                <input type="text" name="total_cost[]" id="total_cost" style="width:80%; height: 25px;" class="total_cost float" />
            </td>
            <td style="white-space:nowrap; padding:0px; text-align:center; width:5%;">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemovePB" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
    </table>
</div>
<div id="POFooter">
    <div style="float: left; width: 28%;">
        <div class="buttons">
            <a href="#" class="positive btnBackPurchaseBill">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePurchaseBill">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSavePB"><?php echo ACTION_SAVE_NEW ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePreviewPurchaseBill">
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtSavePreviewPB"><?php echo ACTION_SAVE_PREVIEW ?></span>
            </button>
        </div>
    </div>
    <div style="float: right; width:70%; vertical-align: bottom;">
        <table cellpadding="0" style="width:100%; padding: 0px; margin: 0px;">
            <tr>
                <td style="width:7%; text-align: right;"><label for="PurchaseBillTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width:11%">
                    <?php echo $this->Form->text('total_amount', array('name'=>'data[PurchaseBill][total_amount]', 'readonly' => true, 'style' => 'width: 80%; height:15px; font-size:12px; font-weight: bold', 'value'=> '0.00')); ?> <span class="lblSymbolPB"></span>
                </td>
                <td style="width:6%; text-align: right;"><label for="PurchaseBillTotalAmount"><?php echo GENERAL_DISCOUNT; ?>:</label></td>
                <td style="width:17%">
                    <div class="inputContainer" style="width:100%">
                        <?php echo $this->Form->hidden('discount_percent', array('class' => 'float', 'value'=>'0')); ?>
                        <?php echo $this->Form->text('discount_amount', array('style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'class' => 'float', 'value'=>'0', 'readonly' => true)); ?> <span class="lblSymbolPB"></span>
                        <span id="PBLabelDisPercent"></span>
                        <?php if($allowEditInvDis){ ?><img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" id="btnRemovePBTotalDiscount" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove Discount')" /><?php } ?>
                    </div>
                </td>
                <td style="width:14%; text-align: right;">
                    <label for="PurchaseBillVatSettingId" id="lblPurchaseBillVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="PurchaseBillVatSettingId" name="data[PurchaseBill][vat_setting_id]" style="width: 75%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id, chart_account_id FROM vat_settings WHERE is_active = 1 AND type = 2 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>" acc="<?php echo $rowVat['chart_account_id']; ?>"><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width:7%">
                    <input type="hidden" value="" name="data[PurchaseBill][vat_chart_account_id]" id="PurchaseBillVatChartAccountId" />
                    <input type="hidden" value="0" name="data[PurchaseBill][total_vat]" id="PurchaseBillTotalVat" />
                    <?php echo $this->Form->text('vat_percent', array('name'=>'data[PurchaseBill][vat_percent]', 'readonly' => true, 'style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'value'=> 0)); ?> <span class="lblSymbolPBPercent">(%)</span>
                </td>
                <td style="width:7%; text-align: right;"><label for="PurchaseBillGrandTotalAmount"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width:11%">
                    <?php echo $this->Form->text('grand_total_amount', array('readonly' => true, 'style' => 'width: 80%; height:15px; font-size:12px; font-weight: bold', 'value'=> '0.00')); ?> <span class="lblSymbolPB"></span>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>