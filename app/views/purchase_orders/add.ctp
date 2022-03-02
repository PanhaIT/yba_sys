<?php 
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 39 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $costDecimal = $rowSetting['value'];
}
    
include("includes/function.php");
$this->element('check_access');
$allowEditCost   = checkAccess($user['User']['id'], $this->params['controller'], 'editCost');
$allowDiscount   = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowAddService = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowEditTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'editTermsCondition');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
$allowAddVendor  = checkAccess($user['User']['id'], 'vendors', 'quickAdd');
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
?>
<script type="text/javascript">
    var indexRowPO  = 0;
    var rowClonePO  = $("#detailPO");
    var pprTimeCode = 1;
    var timestamp   = new Date().getTime();
    
    function resizeFormTitlePO(){
        var screen = 16;
        var widthList = $("#bodyListPO").width();
        $("#tblHeaderPO").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblHeaderPO").css('padding','0px');
        $("#tblHeaderPO").css('margin-top','5px');
        $("#tblHeaderPO").css('width',widthTitle);
    }
    
    function resizeFornScrollPO(){
        var windowHeight = $(tabPRId).height();
        var formHeader = 0;
        if ($('#divPOTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#divPOTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderPurchaseRequest").height();
        var formFooter   = $("#tblFooterPO").height();
        var formSearch   = $("#searchFormPO").height();
        var tableHeader  = $("#tblHeaderPO").height();
        var getHeight = windowHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter);
        $("#bodyListPO").css('height',getHeight);
        $("#bodyListPO").css('padding','0px');
        $("#bodyListPO").css('width','100%');
        $("#bodyListPO").css('overflow-x','hidden');
        $("#bodyListPO").css('overflow-y','scroll');
        checkOrderDatePO();
    }
    
    function refreshScreenPO(){
        $("#tblHeaderPO").removeAttr('style');
    }
    
    function calcTotalPO(){
        var totalAmount = 0;
        var totalVat    = 0;
        var vatPercent  = replaceNum($("#PurchaseRequestVatPercent").val());
        var total       = 0;
        var vatCal           = $("#PurchaseRequestVatCalculate").val();
        var totalBfDis       = 0;
        var totalAmtCalVat   = 0;
        $(".listBodyPO").find(".total_cost").each(function(){
            totalAmount += replaceNum($(this).val());
        });
        totalAmtCalVat = totalAmount;
        if(vatCal == 1){
            $(".listBodyPO").each(function(){
                var qty   = replaceNum($(this).find(".qty").val());
                var price = replaceNum($(this).find(".unit_cost").val());
                totalBfDis += replaceNum(converDicemalJS(qty * price));
            });
            totalAmtCalVat = totalBfDis;
        }
        totalVat = replaceNum(converDicemalJS((totalAmtCalVat * vatPercent) / 100).toFixed(<?php echo $costDecimal; ?>));
        total    = converDicemalJS(totalAmount + totalVat);
        $("#PurchaseRequestTotalAmount").val((parseFloat(totalAmount)).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseRequestTotalVat").val((parseFloat(totalVat)).toFixed(<?php echo $costDecimal; ?>));
        $("#PurchaseRequestTotalAmountAll").val((parseFloat(total)).toFixed(<?php echo $costDecimal; ?>));
    }
    
    function addProductPO(code, name, uom, product_id, unit_cost, smallUomValPro){
        var productId           = product_id;
        var productName         = name;
        var smallUomPro         = smallUomValPro;        
        indexRowPO = Math.floor((Math.random() * 100000) + 1);
        var tr = rowClonePO.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowPO);
        tr.find("td .product_id").attr("id", "product_id"+indexRowPO).val(productId);
        tr.find("td .service_id").attr("id", "service_id"+indexRowPO).val('');
        tr.find("td .product_code").attr("id", "product_code"+indexRowPO).val(code);
        tr.find("td .product_name").attr("data", productName);
        tr.find("td .product_name").attr("id", "product_name"+indexRowPO).val(productName+"/"+smallUomPro);
        tr.find("td .smallUomValPro").attr("id", "smallUomValPro").val(smallUomPro);
        tr.find("td .qty_uom_id").attr("id", "qty_uom_id"+indexRowPO).html(uom);
        tr.find("td .qty").attr("id", "qty_"+indexRowPO, "class", "validate[required,min[1]]").val(1);
        tr.find("td .qty_free").attr("id", "qty_free"+indexRowPO);
        tr.find("td .discountPO").attr("id", "discountPO"+indexRowPO);
        tr.find("td .unit_cost").attr("id", "unit_cost"+indexRowPO).val(Number(unit_cost).toFixed(<?php echo $costDecimal; ?>));
        tr.find("td .defaltCost").val(unit_cost);
        tr.find("td .tmp_unit_cost").val(unit_cost);
        tr.find("td .total_cost").attr("id", "total_cost"+indexRowPO).val(Number(unit_cost).toFixed(<?php echo $costDecimal; ?>));
        tr.find("td .h_total_cost").attr("id", "h_total_cost"+indexRowPO).val(unit_cost);
        tr.find("td .note").attr("id", "note"+indexRowPO).val("");
        tr.find("td .btnRemovePO").show();
        var conversion = parseInt(tr.find("td .qty_uom_id").find("option:selected").attr('conversion'));
        tr.find("td .prr_conversion").val(parseInt(smallUomPro / conversion));
        $("#tblPO").append(tr);
        
        $("#tblPO").find("tr:last").find("td .qty").select().focus();
        pprTimeCode = 1;
        checkEventPO();
        sortNuTablePO();
        calcTotalPO();
    }
    
    function addServicePO(service_id, name, unit_cost, serviceCode, uomId){
        indexRowPO       = Math.floor((Math.random() * 100000) + 1);
        var serviceID    = service_id;
        var productName  = name;
        var tr           = rowClonePO.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowPO);
        tr.find("td .product_id").attr("id", "product_id"+indexRowPO).val('');
        tr.find("td .btnProductInfo").remove();
        tr.find("td .service_id").attr("id", "service_id"+indexRowPO).val(serviceID);
        tr.find("td .product_name").attr("id", "product_name"+indexRowPO).val(productName);
        tr.find("td .smallUomValPro").attr("id", "smallUomValPro").val(1);
        tr.find("td .prr_conversion").val(1);
        tr.find("td .qty").attr("id", "qty_"+indexRowPO).val(1);
        tr.find("td .qty_free").attr("id", "qty_free"+indexRowPO);
        tr.find("td .discountPO").attr("id", "discountPO"+indexRowPO);
        tr.find("td .unit_cost").attr("id", "unit_cost"+indexRowPO).val(unit_cost);
        tr.find("td .defaltCost").val(unit_cost);
        tr.find("td .tmp_unit_cost").val(unit_cost);
        tr.find("td .total_cost").attr("id", "total_cost"+indexRowPO).val(unit_cost);
        tr.find("td .h_total_cost").attr("id", "h_total_cost"+indexRowPO).val(unit_cost);
        tr.find("td .note").attr("id", "note"+indexRowPO).val("");
        tr.find("td .btnRemovePO").show();
        if(uomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowPO).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowPO).find("option[value='"+uomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+uomId+"']").hide();
        }
        $("#tblPO").append(tr);
        tr.find("td .qty").select().focus();
        
        pprTimeCode = 1;
        checkEventPO();
        sortNuTablePO();
        calcTotalPO();
    }
    
    function clearFormPO(){
        $("#tblPO tr#detailPO").each(function(i){
            if($("#tblPO tr#detailPO").length == 1){
                $(this).find("td .product_id").val('');
                $(this).find("td .product_name").val('');
                $(this).find("td .qty").val('');
                $(this).find("td .unit_cost").val('');
                $(this).find("td .total_cost").val('0');
                $("#tblPO tr#detailPO").hide();
            }else{
                $(this).remove();
            }
        });
        $("#PurchaseRequestTotalAmount").val('0');
        calcTotalPO();
        pprTimeCode = 1;
    }
    
    function sortNuTablePO(){
        var sort = 1;
        $(".listBodyPO").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function checkVendorPO(field, rules, i, options){
        if($("#PurchaseRequestVendorId").val() == "" || $("#PurchaseRequestVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }
    
    function serachProCodePO(code, field){      
        if($("#PurchaseRequestCompanyId").val() == "" || $("#PurchaseRequestBranchId").val() == ""){
            pprTimeCode = 1;
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
                close: function(event, ui){
                    
                },
                buttons: {
                    '<?php echo ACTION_CLOSE; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        }else {
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/searchProductCode/"; ?>" +$("#PurchaseRequestCompanyId").val()+"/"+$("#PurchaseRequestBranchId").val()+"/"+encodeURIComponent(code)+"/2",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $(field).val('');                
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
                        pprTimeCode = 1;
                    }else{
                        var data = msg;
                        var record = $.parseJSON(data);
                        if(data){
                            $.ajax({
                                type: "GET",
                                url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+record[2],
                                data: "",
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(msg){                                    
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                    addProductPO(record[1],record[4], msg, record[0], record[3], record[5]);
                                }
                            });
                        }
                    }
                }
            });
        }
    }
    
    function searchAllServicePO(){
        if($("#PurchaseRequestCompanyId").val()=="" || $("#PurchaseRequestBranchId").val() == ""){
            pprTimeCode == 1;
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
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/service"; ?>/" + $("#PurchaseRequestCompanyId").val()+"/"+$("#PurchaseRequestBranchId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    pprTimeCode == 1;
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
                                    addServicePO($("#ServiceServiceId").val(),$("#ServiceServiceId").find("option:selected").html(),$("#ServiceUnitPrice").val(),$("#ServiceServiceId").find("option:selected").attr("scode"),$("#ServiceServiceId").find("option:selected").attr("suom"));
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function deleteVendorPO(){
        $("#PurchaseRequestVendorId").val("");
        $("#PurchaseRequestVendorName").val("");
        $("#PurchaseRequestVendorName").removeAttr("readonly");
        $("#deleteSearchVendorPO").hide();
        $("#searchVendorPO").show();
    }
    
    function checkBfSavePO(){
        var formName = "#PurchaseRequestAddForm";
        var validateBack =$(formName).validationEngine("validate");
        if(!validateBack){            
            return false;
        }else{
            if(($("#PurchaseRequestTotalAmount").val() == undefined && $("#PurchaseRequestTotalAmount").val() == "") || $(".listBodyPO").find(".product_id").val() == undefined){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an order first.</p>');
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
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }else{
                return true;
            }
        }
    }
    
    function searchProductPO(){    
        if($("#PurchaseRequestCompanyId").val() == "" || $("#PurchaseRequestBranchId").val() == "" || $("#PurchaseRequestVendorId").val() == ""){
            pprTimeCode = 1;
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_LOCATION; ?></p>');
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
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/product/"; ?>"+$("#PurchaseRequestCompanyId").val()+"/"+$("#PurchaseRequestBranchId").val()+"/"+$("#PurchaseRequestVendorId").val(),
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
                        pprsition:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var data   = $("input[name='chkProduct']:checked").val();
                                var record = $.parseJSON(data);
                                if(data){
                                    $.ajax({
                                        type: "GET",
                                        url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+record[1],
                                        data: "",
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){       
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            addProductPO($("input[name='chkProduct']:checked").attr('class'),$("input[name='chkProduct']:checked").attr('id'), msg, record[0], record[2], record[5]);
                                        }
                                    });
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function moveRowPO(){
        $(".btnMoveDownPO, .btnMoveUpPO").unbind('click');
        $(".btnMoveDownPO").click(function () {
            var rowToMove = $(this).parents('tr.listBodyPO:first');
            var next = rowToMove.next('tr.listBodyPO');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTablePO();
        });

        $(".btnMoveUpPO").click(function () {
            var rowToMove = $(this).parents('tr.listBodyPO:first');
            var prev = rowToMove.prev('tr.listBodyPO');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTablePO();
        });
    }
    
    function checkEventPO(){
        eventKeyRowPO();
        $(".listBodyPO").unbind("click");
        $(".listBodyPO").click(function(){
            eventKeyRowPO();
        });
    }
    
    function eventKeyRowPO(){
        loadAutoCompleteOff();
        $(".qty, .unit_cost, .qty_uom_id, .total_cost, .btnRemovePO, .noteAddPO, .btnDiscountPO, .btnRemoveDiscountPO, .btnProductInfo").unbind('keypress').unbind('keyup').unbind('change').unbind('click');
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
            var value     = replaceNum($(this).val());
            var unitPrice = 0;
            var qty   = replaceNum($(this).closest("tr").find(".qty").val());
            var discountAmount = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val());
            var discountPercent = $(this).closest("tr").find("input[name='discount_percent[]']").val();
            if(discountAmount != 0 && discountAmount != ''){
                unitPrice = converDicemalJS( (converDicemalJS( Number(value) + Number(discountAmount) )) / qty );
            }else if(discountPercent!=0 && discountPercent!=''){
                unitPrice = converDicemalJS( ( converDicemalJS((converDicemalJS(Number(value) * 100)) / (converDicemalJS(100 - discountPercent))) ) / qty );
                var discount = converDicemalJS((converDicemalJS( (converDicemalJS(Number(value) * 100))  / (converDicemalJS(100 - discountPercent)) ) ) * (converDicemalJS(discountPercent / 100)));
                $(this).closest("tr").find(".discountPO").val(discount.toFixed(<?php echo $costDecimal; ?>));
            }else{
                unitPrice = parseFloat(converDicemalJS(value / qty));
            }
            $(this).closest("tr").find(".h_total_cost").val(parseFloat(converDicemalJS(unitPrice * qty)).toFixed(<?php echo $costDecimal; ?>));
            $(this).closest("tr").find(".unit_cost").val(unitPrice.toFixed(<?php echo $costDecimal; ?>));
            calcTotalPO();
        });
        
        $(".qty, .unit_cost").keyup(function(){
            var qty = "";
            var conversion = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("conversion"));
            var unitCost   = replaceNum($(this).closest("tr").find("td .unit_cost").val());
            var tmpCost    = replaceNum($(this).closest("tr").find("td .tmp_unit_cost").val());
            if($(this).attr("class") == 'unit_cost validate[required] float'){
                if(unitCost != tmpCost){
                    $(this).closest("tr").find(".defaltCost").val(converDicemalJS(unitCost * conversion).toFixed(<?php echo $costDecimal; ?>));
                    $(this).closest("tr").find(".tmp_unit_cost").val(unitCost);
                }
            }
            if(replaceNum($(this).closest("tr").find("td .qty").val()) != ""){
                qty = replaceNum($(this).closest("tr").find("td .qty").val());
            }else{
                qty = 1;
            }
            var totalAmount = converDicemalJS(parseFloat(replaceNum(qty)) * replaceNum($(this).closest("tr").find("td .unit_cost").val()));
            var discount    = 0;
            if(parseFloat($(this).closest("tr").find("input[name='discount_percent[]']").val()) > 0){
                discount = parseFloat(converDicemalJS( (converDicemalJS(totalAmount * $(this).closest("tr").find("input[name='discount_percent[]']").val()))/100 ));
                $(this).closest("tr").find("input[name='discount[]']").val((discount).toFixed(<?php echo $costDecimal; ?>));
            }else{
                discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()));
            }
            var totalCost = converDicemalJS(parseFloat(totalAmount - discount).toFixed(<?php echo $costDecimal; ?>));       
            $(this).closest("tr").find("td .h_total_cost").val(totalAmount.toFixed(<?php echo $costDecimal; ?>));
            $(this).closest("tr").find("td .total_cost").val(converDicemalJS(parseFloat(totalCost)).toFixed(<?php echo $costDecimal; ?>));
            calcTotalPO();
        });
        
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
                    $(this).closest("tr").find(".qty_uom_id").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $("#productPurchaseOrder").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){            
            var value = replaceNum($(this).val());
            var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));            
            var smUomVal = replaceNum($(this).closest("tr").find(".smallUomValPro").val());
            var uomCon   = converDicemalJS(smUomVal / uomConversion);   
            
            var unit_price = (parseFloat(converDicemalJS(replaceNum($(this).closest("tr").find(".defaltCost").val())/uomConversion))).toFixed(<?php echo $costDecimal; ?>);
            if($(this).closest("tr").find(".product_id").val() != ""){
                $(this).closest("tr").find(".unit_cost").val(unit_price);
                $(this).closest("tr").find(".tmp_unit_cost").val(unit_price);
                var totalAmount = parseFloat( converDicemalJS(unit_price * replaceNum($(this).closest("tr").find(".qty").val())) );
                if($(this).closest("tr").find("input[name='discount_percent[]']").val() != ""){
                    $(this).closest("tr").find("input[name='discount[]']").val(parseFloat(converDicemalJS((converDicemalJS(totalAmount * replaceNum($(this).closest("tr").find("input[name='discount_percent[]']").val()))) / 100 )).toFixed(<?php echo $costDecimal; ?>));
                }else{
                    var discountAmount = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val()) > 0 ? replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val()) : 0;
                    $(this).closest("tr").find("input[name='discount[]']").val(discountAmount.toFixed(<?php echo $costDecimal; ?>));
                }
                var discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()>0?$(this).closest("tr").find("input[name='discount[]']").val():0 ));
                $(this).closest("tr").find(".prr_conversion").val(uomCon);
                
                $(this).closest("tr").find(".h_total_cost").val(totalAmount.toFixed(<?php echo $costDecimal; ?>));
                $(this).closest("tr").find(".total_cost").val( (converDicemalJS(totalAmount - discount)).toFixed(<?php echo $costDecimal; ?>) );
                // Set Name
                var nameDef = $(this).closest("tr").find(".product_name").attr("data");
                var nameCon = nameDef+"/"+uomCon;
                $(this).closest("tr").find(".product_name").val(nameCon);
                
                calcTotalPO();
            }
        });
        
        $(".btnDiscountPO").click(function(){
            var obj = $(this);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/invoiceDiscount"; ?>",
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
                                obj.closest("tr").find(".btnRemoveDiscountPO").css("display", "inline");
                                // Set Percent
                                if(percent > 0){
                                    obj.closest("tr").find(".lblDisPercent").text("("+converDicemalJS(percent).toFixed(2)+"%)");
                                } else {
                                    obj.closest("tr").find(".lblDisPercent").text("");
                                }
                                calcTotalPO();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });

        $(".btnRemoveDiscountPO").click(function(){
            var discount = parseFloat(replaceNum($(this).closest("tr").find("input[name='discount[]']").val()));
            var totalAmount = parseFloat(replaceNum($(this).closest("tr").find(".total_cost").val()));
            $(this).closest("tr").find("input[name='discount_id[]']").val("");
            $(this).closest("tr").find("input[name='discount_amount[]']").val(0);
            $(this).closest("tr").find("input[name='discount_percent[]']").val(0);
            $(this).closest("tr").find("input[name='discount[]']").val('0');
            $(this).closest("tr").find(".btnRemoveDiscountPO").css("display", "none");
            $(this).closest("tr").find(".lblDisPercent").text("");
            $(this).closest("tr").find(".total_cost").val(parseFloat(converDicemalJS(totalAmount + discount)).toFixed(<?php echo $costDecimal; ?>));
            calcTotalPO();
        });
        
        $(".btnRemovePO").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                pprsition:'center',
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
                        calcTotalPO();
                        sortNuTablePO();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        // Button Show Information
        $(".btnProductInfo").click(function(){
            showProductInfoPO($(this));
        });
        
        $(".noteAddPO").click(function(){
            addNotePO($(this));
        });
        
        moveRowPO();
    }
    
    function searchVendorPO(){
        var companyId = $("#PurchaseRequestCompanyId").val();
        if($("#PurchaseRequestCompanyId").val() ==""){
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
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/vendor/"; ?>"+companyId,
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
                        pprsition:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(){
                            pprTimeCode = 1;
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                // calculate due_date
                                var data = $("input[name='chkVendor']:checked").val();
                                if(data){
                                    $("#PurchaseRequestVendorId").val(data.split('-')[0]);
                                    $("#PurchaseRequestVendorName").val(data.split('-')[2]);
                                    $("#PurchaseRequestVendorName").attr('readonly', true);
                                    $("#searchVendorPO").hide();
                                    $("#deleteSearchVendorPO").show();
                                }
                                pprTimeCode = 1;
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        loadAutoCompleteOff();
        // Hide Branch
        $("#PurchaseRequestBranchId").filterOptions('com', '0', '');
        $("#detailPO").remove();
        var waitForFinalEventPO = (function () {
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
        if(tabPRReg != tabPRId){
            $("a[href='"+tabPRId+"']").click(function(){
                if($("#bodyListPO").html() != '' && $("#bodyListPO").html() != null){
                    waitForFinalEventPO(function(){
                        refreshScreenPO();
                        resizeFormTitlePO();
                        resizeFornScrollPO();  
                    }, 500, "Finish");
                }
            });
            tabPRReg = tabPRId;
        }

        waitForFinalEventPO(function(){
              refreshScreenPO();
              resizeFormTitlePO();
              resizeFornScrollPO();  
            }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabPRReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventPO(function(){
                    refreshScreenPO();
                    resizeFormTitlePO();
                    resizeFornScrollPO();  
                  }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderPurchaseRequest").click(function(){
            var PurchaseRequestCompanyId = $("#PurchaseRequestCompanyId").val();
            var PurchaseRequestBranchId  = $("#PurchaseRequestBranchId").val();
            var PurchaseRequestOrderDate = $("#PurchaseRequestOrderDate").val();
            var PurchaseRequestVendorId  = $("#PurchaseRequestVendorId").val();
            
            if(PurchaseRequestCompanyId == "" || PurchaseRequestBranchId == "" || PurchaseRequestOrderDate == "" || PurchaseRequestVendorId == ""){
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
                    $("#divPOTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#divPOTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabPRReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventPO(function(){
                        resizeFornScrollPO();
                    }, 300, "Finish");
                }
            }
        });
        
        // Form Validate
        $("#PurchaseRequestAddForm").validationEngine('detach');
        $("#PurchaseRequestAddForm").validationEngine('attach');
        
        $(".btnSavePurchaseOrder").click(function(){
            if(checkBfSavePO() == true){
                $("#PurchaseRequestIsPreview").val('0');
                return true;
            }else{
                return false;
            }
        });
        
        $(".btnPreviewPurchaseOrder").click(function(){
            if(checkBfSavePO() == true){
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
                            $("#PurchaseRequestIsPreview").val(1);
                            $("#PurchaseRequestAddForm").submit();
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        
        $("#PurchaseRequestAddForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                if($("#PurchaseRequestIsPreview").val() == '1'){
                    $(".txtPreviewPO").html("<?php echo ACTION_LOADING; ?>");
                } else {
                    $(".txtSavePO").html("<?php echo ACTION_LOADING; ?>");
                }
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#PurchaseRequestOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .qty, .qty_free").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#PurchaseRequestTotalAmount").val($("#PurchaseRequestTotalAmount").val().replace(/,/g,""));
                $(".btnSavePurchaseOrder, .btnPreviewPurchaseOrder").attr("disabled", true);
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Purchase Order', 'Add', 2, result.responseText);
                backPurchaseOrder();
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
                if(result.error == "2"){
                    errorSavePO();
                }else{
                    createSysAct('Purchase Order', 'Add', 1, '');
                    if($("#PurchaseRequestIsPreview").val() == '1'){
                        // Preview
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.po_id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printInvoiceResult){
                                var w = window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printInvoiceResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                        // Reset Normal
                        $("#PurchaseRequestPreviewId").val(result.po_id);
                        $("#PurchaseRequestPrCode").val(result.po_code);
                        $(".txtPreviewPO").html("<?php echo ACTION_SAVE_PREVIEW; ?>");
                        $(".btnSavePurchaseOrder, .btnPreviewPurchaseOrder").attr("disabled", false);
                        $("#PurchaseRequestOrderDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    } else {
                        renewPurchaseOrder();
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printPurchaseOrder" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT; ?></span></button></div> ');
                        $(".printPurchaseOrder").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.po_id,
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(printInvoiceResult){
                                    var w = window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write(printInvoiceResult);
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
                            pprsition:'center',
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
        
        $("#productPurchaseOrder").autocomplete("<?php echo $this->base . "/purchase_requests/searchProduct"; ?>", {
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
            $("#productPurchaseOrder").val(code);
            if(pprTimeCode == 1){
                pprTimeCode = 2;
                serachProCodePO(code,'#productPurchaseOrder');
            }
        });
        
        $("#searchVendorPO").click(function(){
            if(checkOrderDatePO() == true){
                searchVendorPO();
            }
        });
        
        $("#deleteSearchVendorPO").click(function(){
            deleteVendorPO();
        });
        
        $("#PurchaseRequestVendorName").focus(function(){
            checkOrderDatePO();
        });
        
        $("#PurchaseRequestVendorName").keypress(function(e){
            if((e.which && e.which != 13) || e.keyCode != 13){
                $("#PurchaseRequestVendorId").val("");
            }
        });
        
        $("#PurchaseRequestVendorName").autocomplete("<?php echo $this->base . "/purchase_requests/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyPO(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyPO(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            $("#PurchaseRequestVendorId").val(value.toString().split(".*")[0]);
            $("#PurchaseRequestVendorName").val(value.toString().split(".*")[1]);
            $("#PurchaseRequestVendorName").attr('readonly', true);
            $("#searchVendorPO").hide();
            $("#deleteSearchVendorPO").show();
        });

        $("#PurchaseRequestOrderDate, #PurchaseRequestExpectedDeliveryDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#PurchaseRequestOrderDate").datepicker("option", "minDate", "<?php echo $dataClosingDate[0]; ?>");
        $("#PurchaseRequestOrderDate").datepicker("option", "maxDate", 0);
        
        $("#searchProductPO").click(function(){
            searchProductPO();
        });
        
        $(".addServicePO").click(function(){
            searchAllServicePO();
        });
        
        $(".productCodePO").keyup(function(e){
            var currentTimestamp = new Date().getTime();
            var obj = $(this);
            if(currentTimestamp - timestamp < 50){
                if($(this).val().length >= 4 ){
                    if($("#PurchaseRequestCompanyId").val() ==""){
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
                            if(pprTimeCode == 1){
                               pprTimeCode = 2;
                               serachProCodePO($(this).val(),'.productCodePO')
                            }
                        }
                    }
                }
            }
            timestamp = currentTimestamp;
        });
        
        $(".productCodePO").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(pprTimeCode == 1){
                    pprTimeCode = 2;
                    serachProCodePO($(this).val(),'.productCodePO');
                }
                return false;
            }
        });

        $("#productPurchaseOrder").keypress(function(e){
            var code = null;
            var obj  = $(this);
            code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13){
                if($("#PurchaseRequestCompanyId").val() ==""){
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
                } else{
                
                    if($(this).val() != ""){
                        if(pprTimeCode == 1){
                            pprTimeCode = 2;                                                        
                            serachProCodePO($(this).val(),'#productPurchaseOrder');
                        }
                    }
                }
                return false;
            }
        });
        $(".btnBackPurchaseRequest").click(function(event){
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
                        backPurchaseOrder();
                    }
                }
            });
        });
        
        $("#PurchaseRequestCompanyId").change(function(){
            var obj    = $(this);
            var vatCal = $(this).find("option:selected").attr("vat-opt");
            if($(".listBodyPO").find(".product_id").val() == undefined){
                $.cookie('companyIdPurchaseRequest', obj.val(), { expires: 7, path: "/" });
                $("#PurchaseRequestVatCalculate").val(vatCal);
                $("#PurchaseRequestBranchId").filterOptions('com', obj.val(), '');
                $("#PurchaseRequestBranchId").change();
                checkVatCompanyPO();
                resetFormPO();
                changeInputCSSPO();
            }else{
                var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_COMPANY; ?>";
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
                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('companyIdPurchaseRequest', obj.val(), { expires: 7, path: "/" });
                            $("#tblPO").html('');
                            $("#PurchaseRequestVatCalculate").val(vatCal);
                            $("#PurchaseRequestBranchId").filterOptions('com', obj.val(), '');
                            $("#PurchaseRequestBranchId").change();
                            checkVatCompanyPO();
                            resetFormPO();
                            changeInputCSSPO();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseRequestCompanyId").val($.cookie("companyIdPurchaseRequest"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $("#PurchaseRequestBranchId").change(function(){
            var obj = $(this);
            if($(".listBodyPO").find(".product_id").val() == undefined){
                $.cookie('branchIdPurchaseRequest', obj.val(), { expires: 7, path: "/" });
                branchChangePurchaseRequest(obj);
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
                            $.cookie('branchIdPurchaseRequest', obj.val(), { expires: 7, path: "/" });
                            branchChangePurchaseRequest(obj);
                            $("#tblPO").html('');
                            calcTotalPO();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#PurchaseRequestBranchId").val($.cookie("branchIdPurchaseRequest"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Company Action
        if($.cookie('companyIdPurchaseRequest')!=null || $("#PurchaseRequestCompanyId").find("option:selected").val() != ''){
            if($.cookie('companyIdPurchaseRequest') != null){
                $("#PurchaseRequestCompanyId").val($.cookie('companyIdPurchaseRequest'));
            }
            var vatCal = $("#PurchaseRequestCompanyId").find("option:selected").attr("vat-opt");
            $("#PurchaseRequestVatCalculate").val(vatCal);
            checkVatCompanyPO();
            $("#PurchaseRequestBranchId").filterOptions('com', $("#PurchaseRequestCompanyId").val(), '');
            $("#PurchaseRequestBranchId").change();
        }
        
        // Action VAT Status
        $("#PurchaseRequestVatSettingId").change(function(){
            checkVatSelectedPO();
            calcTotalPO();
        });
        
        // Warehouse Change
        $("#PurchaseRequestLocationGroupId").unbind("change").change(function(){
            var ship = $(this).find("option:selected").attr('ship');
            $("#PurchaseRequestShipmentTo").val(ship);
        });
        
        // Button Change Info & Term
        <?php
        if($allowEditTerm){
        ?>
        $("#btnPOTermCon").click(function(){
            $("#POInformation").hide();
            $("#POTermCondition").show();
            $("#btnPOTermCon, #btnPOInfo").removeAttr('style');
            $("#btnPOTermCon").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
            $("#btnPOInfo").attr("style", "padding: 3px; background: #CCCCCC;");
        });
        
        $("#btnPOInfo").click(function(){
            $("#POInformation").show();
            $("#POTermCondition").hide();
            $("#btnPOTermCon, #btnPOInfo").removeAttr('style');
            $("#btnPOTermCon").attr("style", "padding: 3px; background: #CCCCCC;");
            $("#btnPOInfo").attr("style", "padding: 3px; background: #CCCCCC; font-weight: bold;");
        });
        <?php
        }
        if($allowAddVendor){
        ?>
        $("#addVendorPurchaseOrder").click(function(){
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
                                                    $("#PurchaseRequestVendorId").val(result.id);
                                                    $("#PurchaseRequestVendorName").val(result.name);
                                                    $("#PurchaseRequestVendorName").attr("readonly", true);
                                                    $("#searchVendorPO").hide();
                                                    $("#deleteSearchVendorPO").show();
                                                    $("#PurchaseRequestAddForm").validationEngine("hideAll");
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
        $("#addProductPurchaseOrder").click(function(){
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
        ?>
        resetFormPO();
        // Check CSS 
        changeInputCSSPO();
    });
    
    function branchChangePurchaseRequest(obj){
//        var mCode = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
//        $("#PurchaseRequestPrCode").val('<?php echo date("y"); ?>'+mCode);
        $("#PurchaseRequestCurrencyCenterId").val(currency);
        $(".lblSymbolPO").html(currencySymbol);
    }
    
    function changeLblVatCalPO(){
        var vatCal = $("#PurchaseRequestVatCalculate").val();
        $("#lblPurchaseRequestVatSettingId").unbind("mouseover");
        if(vatCal != ''){
            if(vatCal == 1){
                $("#lblPurchaseRequestVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_BEFORE_DISCOUNT; ?>');
                });
            } else {
                $("#lblPurchaseRequestVatSettingId").mouseover(function(){
                    Tip('<?php echo TABLE_VAT_AFTER_DISCOUNT; ?>');
                });
            }
        }
    }
    
    function checkVatSelectedPO(){
        var vatPercent = replaceNum($("#PurchaseRequestVatSettingId").find("option:selected").attr("rate"));
        $("#PurchaseRequestVatPercent").val((vatPercent).toFixed(2));
    }
    
    function checkVatCompanyPO(){
        // VAT Filter
        $("#PurchaseRequestVatSettingId").filterOptions('com-id', $("#PurchaseRequestCompanyId").val(), '');
    }
    
    function checkCompanyPO(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#PurchaseRequestCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormPO(){
        // Vendor
        $("#deleteSearchVendorPO").click();
    }
    
    function checkOrderDatePO(){
        if($("#PurchaseRequestOrderDate").val() == ""){
            $("#PurchaseRequestOrderDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    function errorSavePO(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
            close: function(){
                $(this).dialog({close: function(){}});
                $(this).dialog("close");
                backPurchaseOrder();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function addNotePO(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteCommentPO'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_NOTE; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            pprsition:'center',
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
    
    function backPurchaseOrder(){
        $("#PurchaseRequestAddForm").validationEngine("hideAll");
        oCache.iCacheLower = -1;
        oPRRTable.fnDraw(false);
        var rightPanel = $(".btnBackPurchaseRequest").parent().parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide( "slide", { direction: "right" }, 500, function() {
            leftPanel.show();
            rightPanel.html('');
        });
    }
    
    function renewPurchaseOrder(){
        var rightPanel = $(".btnBackPurchaseRequest").parent().parent().parent().parent().parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add");
    }
    
    function changeInputCSSPO(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchVendorPO").hide();
        $("#divSearchPO").css("visibility", "hidden");
        if($("#PurchaseRequestCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#PurchaseRequestVendorName").val() == ''){
                $(".searchVendorPO").show();
            }
            $("#divSearchPO").css("visibility", "visible");
        }   
        // Label
        $("#PurchaseRequestAddForm").find("label").removeAttr("class");
        $("#PurchaseRequestAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'PurchaseRequestCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#PurchaseRequestAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#PurchaseRequestAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'PurchaseRequestCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolPO").removeClass(cssRemove);
        $(".lblSymbolPO").addClass(cssStyle);
        $(".lblSymbolPOPercent").removeClass(cssRemove);
        $(".lblSymbolPOPercent").addClass(cssStyle);
        // Input Readonly
        $("#PurchaseRequestVendorName").attr("readonly", readonly);
        $("#PurchaseRequestCurrency").attr("readonly", readonly);
        $("#PurchaseRequestRefPurchaseRequest").attr("readonly", readonly);
        $("#PurchaseRequestNote").attr("readonly", readonly);
        $("#productPurchaseCode").attr("readonly", readonly);
        $("#productPurchase").attr("readonly", readonly);
        // Put label VAT Calculate
        changeLblVatCalPO();
        // Check VAT Default
        getDefaultVatPO();
    }
    
    function getDefaultVatPO(){
        var vatDefault = $("#PurchaseRequestCompanyId option:selected").attr("vat-d");
        $("#PurchaseRequestVatSettingId option[value='"+vatDefault+"']").attr("selected", true);
        checkVatSelectedPO();
    }
    
    function showProductInfoPO(currentTr){
        var vendorId  = $("#PurchaseRequestVendorId").val();
        var productId = currentTr.closest("tr").find(".product_id").val();
        if(productId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/purchase_requests/productHistory"; ?>/"+productId+"/"+vendorId,
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
<?php echo $this->Form->create('PurchaseRequest'); ?>
<input type="hidden" value="" name="data[PurchaseRequest][vat_calculate]" id="PurchaseRequestVatCalculate" />
<input type="hidden" value="" name="data[PurchaseRequest][currency_center_id]" id="PurchaseRequestCurrencyCenterId" />
<input type="hidden" value="" name="data[PurchaseRequest][preview_id]" id="PurchaseRequestPreviewId" />
<input type="hidden" value="0" id="PurchaseRequestIsPreview" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderPurchaseRequest">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<div id="divPOTop">
    <fieldset>
        <legend><?php __(MENU_PURCHASE_REQUEST_INFO); ?></legend>
        <table cellpadding="3" cellspacing="0" style="width: 100%;" id="POInformation">
            <tr>
                <td style="width: 50%">
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%"><label for="PurchaseRequestOrderDate"><?php echo TABLE_PO_DATE; ?> <span class="red">*</span></label></td>
                            <td style="width: 33%"><label for="PurchaseRequestPrCode"><?php echo TABLE_PO_NUMBER; ?></label></td>
                            <td style="width: 33%"><?php if(count($branches) > 1){ ?><label for="PurchaseRequestBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label><?php } ?></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('order_date', array('value' => date("d/m/Y"), 'class' => 'validate[required]', 'readonly' => 'readonly', 'style' => 'width:70%')); ?>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('pr_code', array('style' => 'width:70%', 'placeholder' => TABLE_AUTO_GENERATE)); ?>
                                </div>
                            </td>
                            <td style="vertical-align: top;">
                                <div class="inputContainer" style="width:100%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                                    <select name="data[PurchaseRequest][branch_id]" id="PurchaseRequestBranchId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($branches) != 1){
                                        ?>
                                        <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($branches AS $branch){
                                        ?>
                                        <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['po_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div> 
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2">
                    <table cellpadding="0" cellspacing="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%;"><label for="PurchaseRequestLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span></label></td>
                            <td style="width: 33%;"><label for="PurchaseRequestShipmentTo"><?php echo TABLE_SHIP_TO; ?></label></td>
                            <td style="width: 33%;"><label for="PurchaseRequestNote"><?php echo TABLE_MEMO; ?></label></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%;">
                                    <select name="data[PurchaseRequest][location_group_id]" id="PurchaseRequestLocationGroupId" class="validate[required]" style="width: 75%;">
                                        <option ship="" value=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        foreach($locationGroups AS $locationGroup){
                                        ?>
                                        <option ship="<?php echo $locationGroup['LocationGroup']['description']; ?>" value="<?php echo $locationGroup['LocationGroup']['id']; ?>"><?php echo $locationGroup['LocationGroup']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="inputContainer" style="width:100%; display: none;">
                                    <select name="data[PurchaseRequest][company_id]" id="PurchaseRequestCompanyId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($companies) != 1){
                                        ?>
                                        <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($companies AS $company){
                                            $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 48 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                            $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                        ?>
                                        <option vat-d="<?php echo $rowVATDefault[0]; ?>" value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td rowspan="3" style=" vertical-align: top;">
                                <?php echo $this->Form->input('shipment_to', array('style' => 'width:90%; height: 70px;', 'label' => false)); ?>
                            </td>
                            <td rowspan="3" style=" vertical-align: top;">
                                <?php echo $this->Form->input('note', array('style' => 'width:90%; height: 70px;', 'label' => false)); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 34%; padding-top: 14px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr> 
            <tr>
                <td>
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td colspan="2"><label for="PurchaseRequestVendorName"><?php echo TABLE_VENDOR; ?> <span class="red">*</span></label></td>
                            <td style="width: 33%"><label for="PurchaseRequestRefPurchaseRequest"><?php echo TABLE_REF_QUOTATION; ?></label></td>
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
                                        <img alt="<?php echo MENU_VENDOR_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 16px;" id="addVendorPurchaseOrder" onmouseover="Tip('<?php echo MENU_VENDOR_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
                                    </div>
                                    <?php
                                    } else {
                                        echo $this->Form->text('vendor_name', array('class' => 'validate[required]', 'style' => 'width:320px'));
                                    }
                                    ?>
                                    &nbsp;&nbsp; <img alt="<?php echo TABLE_SHOW_VENDOR_LIST; ?>" align="absmiddle" style="cursor: pointer; width:22px; height: 22px;" id="searchVendorPO" onmouseover="Tip('<?php echo TABLE_SHOW_VENDOR_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                    <img alt="<?php echo ACTION_REMOVE; ?>" align="absmiddle" id="deleteSearchVendorPO" onmouseover="Tip('<?php echo ACTION_REMOVE; ?>')" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" style="display:none; cursor: pointer; width:22px; height: 22px;" />
                                    <div style="clear: both;"></div>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('ref_quotation', array('style' => 'width:70%')); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="inputContainer" style="width:100%" id="searchFormPO">
    <table width="100%">
        <tr>
            <td style="width: 300px;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="productPurchaseOrder" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductPurchaseOrder" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="productPurchaseOrder" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td id="divSearchPO">
                <img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer;" id="searchProductPO" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                <?php
                if ($allowAddService) {
                ?>
                <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServicePO" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" /> 
                <?php
                }
                ?>
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>
<table id="tblHeaderPO" class="table" cellspacing="0" style="padding:0px; width:99%;">
    <tr>
        <th class="first" style="width:5%"><?php echo TABLE_NO; ?></th>
        <th style="width:10%"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:18%"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:12%"><?php echo TABLE_UOM; ?></t</th>
        <th style="width:8%"><?php echo TABLE_QTY; ?></th>
        <th style="width:8%"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:10%"><?php echo TABLE_UNIT_COST; ?></th>
        <th style="width:10%"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:10%"><?php echo TABLE_TOTAL_COST; ?></th>
        <th style="width:9%"></th>
    </tr>
</table>
<div id="bodyListPO">
    <table id="tblPO" class="table" cellspacing="0" style="padding:0px;">
        <tr id="detailPO" class="listBodyPO" style="visibility: hidden;">
            <td class="first" style="width:5%"></td>
            <td style="width:10%">
                <div class="inputContainer" style="width:100%">
                    <input type="text" style="width:90%; height: 25px;"  class="product_code" />
                </div>
            </td>
            <td style="width:18%">
                <div class="inputContainer" style="width:100%">                    
                    <input type="hidden" name="product_id[]" class="product_id" id="product_id" />
                    <input type="hidden" name="service_id[]" class="service_id" id="service_id" />
                    <input type="hidden" id="note" name="note[]" class="note" />
                    <input type="text" id="product_name" data="" name="product_name[]" readonly="readonly" class="product_name validate[required]" style="width: 75%; height: 25px;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddPO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:12%">
                <div class="inputContainer" style="width:100%">                                        
                    <input type="hidden" class="smallUomValPro" />
                    <input type="hidden" name="prr_conversion[]" class="prr_conversion" />                    
                    <select id="qty_uom_id" name="qty_uom_id[]" style="width:80%; height: 25px;" class="qty_uom_id validate[required]">
                        <?php
                        foreach ($uoms as $uom) {
                            echo "<option value='{$uom['Uom']['id']}' conversion='1'>{$uom['Uom']['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:75%; height: 25px;"  class="qty validate[required,min[1]]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" value="0" style="width:75%; height: 25px;"  class="qty_free" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="defaltCost" name="default_cost[]" />
                    <input type="hidden" class="tmp_unit_cost" value="0" />
                    <input type="text" id="unit_cost" name="unit_cost[]" <?php if(!$allowEditCost){ ?>readonly=""<?php } ?> class="unit_cost validate[required] float" style="width:80%; height: 25px;" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%;">
                <div class="inputContainer" style="width:100%">
                    <div style="white-space: nowrap; margin-top: 3px; width: 100%">
                        <input type="hidden" name="discount_id[]" />
                        <input type="hidden" name="discount_amount[]" />
                        <input type="hidden" name="discount_percent[]" />
                        <?php
                        if($allowDiscount){
                        ?>
                        <input type="text" name="discount[]" value="0" class="discountPO btnDiscountPO float" readonly="readonly" id="discountPO" style="width: 50%; height: 25px;" /><span class="lblDisPercent"></span>
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountPO" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                        <?php
                        }else{
                        ?>
                        <input type="hidden" name="discount[]" value="0" class="discountPO btnDiscountPO float" readonly="readonly" id="discountPO" />
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%">
                <input type="hidden" id="h_total_cost" class="h_total_cost float" name="h_total_cost[]" />
                <input type="text" name="total_cost[]" id="total_cost" <?php if(!$allowEditCost){ ?>readonly=""<?php } ?> style="width:80%; height: 25px;" class="total_cost float" />
                
            </td>
            <td style="white-space: nowrap; padding:0px; text-align: center; width:9%">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemovePO" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpPO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownPO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
    </table>
</div>
<div id="tblFooterPO">
    <div style="float: left; width: 28%;">
        <div class="buttons">
            <a href="#" class="positive btnBackPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSavePurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSavePO"><?php echo ACTION_SAVE_NEW; ?></span>
            </button>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnPreviewPurchaseOrder">
                <img src="<?php echo $this->webroot; ?>img/button/preview.png" alt=""/>
                <span class="txtPreviewPO"><?php echo ACTION_SAVE_PREVIEW; ?></span>
            </button>
        </div>
    </div>
    <div style="float: right; width:70%; vertical-align: bottom;" id="amountPaid">
        <table cellpadding="0" style="width:100%; padding: 0px; margin: 0px;">
            <tr>
                <td style="width:15%; text-align: right;"><label for="PurchaseRequestTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width:18%">
                    <?php echo $this->Form->text('total_amount', array('readonly' => true, 'style' => 'width: 80%; height:15px; font-size:12px; font-weight: bold', 'value' => '0.00')); ?> <span class="lblSymbolPO"></span>
                </td>
                <td style="width:23%; text-align: right;">
                    <label for="PurchaseRequestVatSettingId" id="lblPurchaseRequestVatSettingId"><?php echo TABLE_VAT; ?> <span class="red">*</span>:</label>
                    <select id="PurchaseRequestVatSettingId" name="data[PurchaseRequest][vat_setting_id]" style="width: 75%;" class="validate[required]">
                        <option com-id="" value="" rate="0.00"><?php echo INPUT_SELECT; ?></option>
                        <?php
                        // VAT
                        $sqlVat = mysql_query("SELECT id, name, vat_percent, company_id FROM vat_settings WHERE is_active = 1 AND type = 2 AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
                        while($rowVat = mysql_fetch_array($sqlVat)){
                        ?>
                        <option com-id="<?php echo $rowVat['company_id']; ?>" value="<?php echo $rowVat['id']; ?>" rate="<?php echo $rowVat['vat_percent']; ?>"><?php echo $rowVat['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
                <td style="width:10%">
                    <input type="hidden" value="0" id="PurchaseRequestTotalVat" name="data[PurchaseRequest][total_vat]" />
                    <?php echo $this->Form->text('vat_percent', array('readonly' => true, 'style' => 'width: 50%; height:15px; font-size:12px; font-weight: bold', 'value' => 0)); ?> <span class="lblSymbolPOPercent">(%)</span>
                </td>
                <td style="width:15%; text-align: right;"><label for="PurchaseRequestTotalAmountAll"><?php echo TABLE_TOTAL; ?>:</label></td>
                <td style="width:19%">
                    <?php echo $this->Form->text('total_amount_all', array('readonly' => true, 'style' => 'width: 80%; height:15px; font-size:12px; font-weight: bold' , 'value' => '0.00')); ?> <span class="lblSymbolPO"></span>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>