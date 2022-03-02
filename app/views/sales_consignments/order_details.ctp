<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowaddServiceSO = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowAddMisc = checkAccess($user['User']['id'], $this->params['controller'], 'miscellaneous');
$allowProductDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowEditPrice = checkAccess($user['User']['id'], $this->params['controller'], 'editUnitPrice');
?>
<script type="text/javascript">
    var rowTableSO    =  $("#OrderListSO");
    var rowIndexSales = 0;
    var timeSalesConginment = 1;
    var invTotalQtySales = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OrderListSO").remove();
        var waitForFinalEventSO = (function () {
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
        if(tabSalesReg != tabSalesId){
            $("a[href='"+tabSalesId+"']").click(function(){
                if($(".orderDetailSales").html() != '' && $(".orderDetailSales").html() != null){
                    waitForFinalEventSO(function(){
                        refreshScreenSO();
                        resizeFormTitleSO();
                        resizeFornScrollSO();
                    }, 500, "Finish");
                }
            });
            tabSalesReg = tabSalesId;
        }
        
        waitForFinalEventSO(function(){
            refreshScreenSO();
            resizeFormTitleSO();
            resizeFornScrollSO();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabSalesReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventSO(function(){
                    refreshScreenSO();
                    resizeFormTitleSO();
                    resizeFornScrollSO();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderSalesConsignment").click(function(){
            var SalesConsignmentCompanyId       = $("#SalesConsignmentCompanyId").val();
            var SalesConsignmentBranchId        = $("#SalesConsignmentBranchId").val();
            var SalesConsignmentLocationGroupId = $("#SalesConsignmentLocationGroupId").val();
            var SalesConsignmentOrderDate       = $("#SalesConsignmentOrderDate").val();
            var SalesConsignmentCustomerName    = $("#SalesConsignmentCustomerName").val();
            var SalesConsignmentChartAccountId  = $("#SalesConsignmentChartAccountId").val();
            var SalesConsignmentConsignmentId   = $("#SalesConsignmentConsignmentId").val();
            
            if(SalesConsignmentCompanyId == "" || SalesConsignmentBranchId == "" || SalesConsignmentLocationGroupId == "" || SalesConsignmentOrderDate == "" || SalesConsignmentCustomerName == "" || SalesConsignmentChartAccountId == "" || SalesConsignmentConsignmentId == ""){
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
                    $("#SOTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#SOTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabSalesReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventSO(function(){
                        resizeFornScrollSO();
                    }, 500, "Finish");
                }
            }   
        });
        
        $("#SalesConsignmentDiscountUs").keyup(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
            if(replaceNum($("#SalesConsignmentDiscountUs").val()) > replaceNum($("#SalesConsignmentTotalAmount").val())){
                $("#SalesConsignmentDiscountUs").val($("#SalesConsignmentTotalAmount").val());
            }
            calcTotalAmountSales();
        });
        
        $("#SalesConsignmentDiscountUs").focus(function(){
            if($(this).val() == "0.000" || $(this).val() == "0" || $(this).val() == "0.00"){
                $(this).val('');
            }
        });
        
        $("#SalesConsignmentDiscountUs").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        $(".addServiceSO").click(function(){
            searchAllServiceSO();
        });

        $(".addMiscellaneousSO").click(function(){
            searchAllMiscSO();
        });
        // Change Price Type
        $("#typeOfPriceSO").change(function(){
            if($(".tblSOList").find(".product_id").val() == undefined){
                changePriceTypeSO();
            } else {
                var question = "<?php echo MESSAGE_CONFIRM_CHANGE_PRICE_TYPE; ?>";
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
                            changePriceTypeSO();
                            $.cookie("typePriceSO", $("#typeOfPriceSO").val(), {expires : 7, path : '/'});
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#typeOfPriceSO").val($.cookie('typePriceSO'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Check CSS Sales
        changeInputCSSSales();
    });
    
    function changePriceTypeSO(){
        if($(".product").val() != undefined && $(".product").val() != ''){
            var priceType  = parseFloat(replaceNum($("#typeOfPriceSO").val()));
            $(".tblSOList").each(function(){
                if($(this).find("input[name='product_id[]']").val() != ''){
                    var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(2));
                    funcCheckCondiSales($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchAllServiceSO(){
        if($("#SalesConsignmentCompanyId").val()=="" || $("#SalesConsignmentBranchId").val() == "" || $("#SalesConsignmentLocationGroupId").val() == "" || $("#SalesConsignmentCustomerId").val() == ""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_LOCATION_CUSTOMER; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_WARNING; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
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
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/service"; ?>/"+$("#SalesConsignmentCompanyId").val()+"/"+$("#SalesConsignmentBranchId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeSalesConginment == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_ADD_SERVICE; ?>',
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
                                var formName = "#ServiceServiceForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    addNewServiceSO();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllMiscSO(){
        if($("#SalesConsignmentCompanyId").val() == "" || $("#SalesConsignmentBranchId").val() == "" || $("#SalesConsignmentLocationGroupId").val() == "" || $("#SalesConsignmentCustomerId").val() == ""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_LOCATION_CUSTOMER; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_WARNING; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
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
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/miscellaneous"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeSalesConginment == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_ADD_NEW_MISCELLANEOUS; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 'auto',
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var formName = "#MiscellaneousMiscellaneousForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    addNewMiscSO();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function resizeFormTitleSO(){
        var screen = 16;
        var widthList = $("#bodyList").width();
        $("#tblSOHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblSOHeader").css('padding','0px');
        $("#tblSOHeader").css('margin-top','5px');
        $("#tblSOHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollSO(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#SOTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#SOTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderSalesConsignment").height();
        var formFooter   = $(".footerFormSales").height();
        var formSearch   = $("#searchFormSales").height();
        var tableHeader  = $("#tblSOHeader").height();
        var screenRemain = 238;
        var getHeight    = windowHeight - (formHeader + btnHeader + formFooter + formSearch + tableHeader + screenRemain);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyList").css('height',getHeight);
        $("#bodyList").css('padding','0px');
        $("#bodyList").css('width','100%');
        $("#bodyList").css('overflow-x','hidden');
        $("#bodyList").css('overflow-y','scroll');
    }
    
    function refreshScreenSO(){
        $("#tblSOHeader").removeAttr('style');
    }
    
    function keyEventSO(){
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
        $(".product, .btnProductSaleInfo, .qty, .qty_free, .unit_price, .qty_uom_id, .total_price, .btnDiscountSO, .btnRemoveDiscountSO, .btnRemoveSO, .noteAddSO").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 3, aSep: ','});
        $(".floatQty").priceFormat();
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#SalesConsignmentCustomerId").val();
            if(productId != '' && customerId != ''){
                $.ajax({
                    type:   "POST",
                    url:    "<?php echo $this->base . "/products/setProductWithCustomer/"; ?>"+productId+"/"+customerId,
                    data:   "data[name]="+proName,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(msg){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    }
                });
            }
        });
        
        $(".qty, .unit_price, .total_price, .qty_free").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        $(".qty, .unit_price, .total_price, .qty_free").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qty, .qty_free").keyup(function(){
            var conversion = $(this).closest("tr").find(".conversion").val();
            var qty        = replaceNum($(this).closest("tr").find(".qty").val()) + replaceNum($(this).closest("tr").find(".qty_free").val());
            var totalOrder = replaceNum(converDicemalJS(qty * conversion));
            $(this).closest("tr").find(".totalQtyOrderSales").val(totalOrder);
            funcCheckCondiSales($(this));
        });
        
        $(".unit_price").keyup(function(){
            funcCheckCondiSales($(this));
        });
        
        $(".qty").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").find(".unit_price").select().focus();
                return false;
            }
        });
        
        $(".unit_price").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if($(this).val() != ""){
                    if($(this).closest("tr").find(".qty_uom_id").find("option").val() != "none"){
                        $(this).closest("tr").find(".qty_uom_id").select().focus();
                    }else{
                        $("#searchProductSkuSales").select().focus();
                    }
                }else{
                    $(this).select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#searchProductSkuSales").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){   
            var productId     = replaceNum($(this).closest("tr").find("input[name='product_id[]']").val());
            if(productId != "" && productId > 0){
                var priceType     = $("#typeOfPriceSO").val();
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum($(this).find("option[data-sm='1']").attr("conversion"));
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price-uom-"+priceType));
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(2));
                $(this).closest("tr").find(".conversion").val(conversion);
            }
            funcCheckCondiSales($(this));
        });
        
        $(".total_price").keyup(function(){
            var discount      = 0;
            var unitPrice     = 0;
            var qty           = replaceNum($(this).closest("tr").find(".qty").val());
            var totalPrice    = replaceNum($(this).val());
            var discountPercent = replaceNum($(this).closest("tr").find("input[name='discount_percent[]']").val());
            var discountAmount  = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val());
            if(qty > 0){
                if(discountAmount != 0 && discountAmount != ''){
                    unitPrice = converDicemalJS((totalPrice + discountAmount) / qty);
                    discount  = discountAmount;
                }else if (discountPercent != 0 && discountPercent != ''){
                    unitPrice = converDicemalJS(( converDicemalJS((converDicemalJS(totalPrice * 100)) / (converDicemalJS(100 - discountPercent))) ) / qty);
                    discount  = converDicemalJS(( converDicemalJS((converDicemalJS(totalPrice * 100)) / (converDicemalJS(100 - discountPercent))) ) * (converDicemalJS(discountPercent / 100)) );
                }else{
                    unitPrice = converDicemalJS(totalPrice / qty);
                }
                $(this).closest("tr").find(".discount").val(discount.toFixed(2));
                $(this).closest("tr").find(".unit_price").val((unitPrice).toFixed(2));
            }else{
                $(this).val(0);
            }
            funcCheckCondiSales($(this));
        });
        
        $(".btnDiscountSO").click(function(){
            addNewDiscountSO($(this).closest("tr"));
        });
        $(".btnRemoveDiscountSO").click(function(){
            removeDiscountSO($(this).closest("tr"));
        });

        $(".btnRemoveSO").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
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
                        currentTr.remove();
                        calcTotalAmountSales();
                        sortNuTableSO();
                        $(this).dialog("close");
                    }
                }
            });
            
        });
        
        $(".noteAddSO").click(function(){
            addNoteSO($(this));
        });
        
        // Button Show Information
        $(".btnProductSaleInfo").click(function(){
            showProductInfoSales($(this));
        });
        
        moveRowSO();
    }
    
    function checkEventSO(){
        keyEventSO();
        $(".tblSOList").unbind("click");
        $(".tblSOList").click(function(){
            keyEventSO();
        });
    }

    function addNewServiceSO(){
        rowIndexSales = Math.floor((Math.random() * 100000) + 1);
        var tr =rowTableSO.clone(true);
        // Service Information
        var serviceId    = $("#ServiceServiceId").val();
        var serviceCode  = $("#ServiceServiceId").find("option:selected").attr('scode');
        var serviceName  = $("#ServiceServiceId").find("option:selected").attr('abbr');
        var servicePrice = $("#ServiceUnitPrice").val();
        var serviceUomId = $("#ServiceServiceId").find("option:selected").attr('suom');
        tr.removeAttr("style").removeAttr("id");

        tr.find("td:eq(0)").html(rowIndexSales);
        tr.find(".totalQtySales").attr("id", "inv_qty_"+rowIndexSales);
        tr.find(".lblSKU").html(serviceCode);
        tr.find("input[name='product_id[]']").attr("id", "product_id_"+rowIndexSales);
        tr.find("input[name='service_id[]']").attr("id", "service_id_"+rowIndexSales).val(serviceId);
        tr.find("input[name='product[]']").attr("id", "product_"+rowIndexSales).val(serviceName).attr("readonly", false);
        tr.find("input[name='qty[]']").attr("id", "qty_"+rowIndexSales).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+rowIndexSales).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+rowIndexSales).val(servicePrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='total_price_bf_dis[]']").attr("id", "total_price_bf_dis_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='discount[]']").attr("id", "discount_"+rowIndexSales).val(0);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales);
        tr.find("input[name='note[]']").attr("id", "note_"+rowIndexSales);
        tr.find(".btnProductSaleInfo").hide();
        if(serviceUomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales).find("option[value='"+serviceUomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+serviceUomId+"']").hide();
        }
        $("#tblSO").append(tr);
        sortNuTableSO();
        checkEventSO();
        calcTotalAmountSales();
        $("#tblSO").find("tr:last").find(".qty").select().focus();
    }

    function addNewMiscSO(){
        rowIndexSales = Math.floor((Math.random() * 100000) + 1);
        var tr = rowTableSO.clone(true);
        tr.removeAttr("style").removeAttr("id");

        // Misc Information
        var productName         = $("#MiscellaneousDescription").val();
        var productPrice        = $("#MiscellaneousUnitPrice").val();
        tr.find("td:eq(0)").html(rowIndexSales);
        tr.find(".totalQtySales").attr("id", "inv_qty_"+rowIndexSales);
        tr.find("input[name='product_id[]']").attr("id", "product_id_"+rowIndexSales);
        tr.find("input[name='service_id[]']").attr("id", "service_id_"+rowIndexSales);
        tr.find("input[name='product[]']").attr("id", "product_"+rowIndexSales).val(productName);
        tr.find("input[name='qty[]']").attr("id", "qty_"+rowIndexSales).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+rowIndexSales).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+rowIndexSales).val(productPrice);
        tr.find("input[name='total_price_bf_dis[]']").attr("id", "total_price_bf_dis_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='discount[]']").attr("id", "discount_"+rowIndexSales).val(0);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales);
        tr.find("input[name='note[]']").attr("id", "note_"+rowIndexSales);
        tr.find(".btnProductSaleInfo").hide();
        $("#tblSO").append(tr);
        
        sortNuTableSO();
        checkEventSO();
        calcTotalAmountSales();
        $("#tblSO").find("tr:last").find(".qty").select().focus();
    }

    function moveRowSO(){
        $(".btnMoveDownSO, .btnMoveUpSO").unbind('click');
        $(".btnMoveDownSO").click(function () {
            var rowToMove = $(this).parents('tr.tblSOList:first');
            var next = rowToMove.next('tr.tblSOList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableSO();
        });

        $(".btnMoveUpSO").click(function () {
            var rowToMove = $(this).parents('tr.tblSOList:first');
            var prev = rowToMove.prev('tr.tblSOList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableSO();
        });
        
        sortNuTableSO();
    }

    function removeDiscountSO(tr){
        tr.find("input[name='discount_id[]']").val("");
        tr.find("input[name='discount_amount[]']").val(0);
        tr.find("input[name='discount_percent[]']").val(0);
        tr.find("input[name='discount[]']").val(0);
        tr.find(".btnRemoveDiscountSO").css("display", "none");
        funcCheckCondiSales(tr.find("input[name='discount_id[]']"));
    }

    function checkExistingRecordSO(productId){
        var isFound = false;
        $("#tblSO").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function funcCheckCondiSales(current){
        var condition     = true;
        var tr            = current.closest("tr");
        var productId     = tr.find("input[name='product_id[]']").val();
        var qty           = replaceNum(tr.find("input[name='qty[]']").val());
        var qtyOrder      = replaceNum(getTotalQtyOrderSales(productId));
        var totalQtySales = replaceNum(tr.find(".totalQtySales").val());
        var unitPrice     = replaceNum(tr.find("input[name='unit_price[]']").val());
        var discountPercent = replaceNum(tr.find("input[name='discount_percent[]']").val());
        var discountAmount  = replaceNum(tr.find("input[name='discount_amount[]']").val());
        var discount        = 0;
        var totalPriceBfDis = 0;
        var totalPrice      = 0;
        var qtyFree         = replaceNum(tr.find("input[name='qty_free[]']").val());
        var priceType       = $("#typeOfPriceSO").val();
        var unitCost        = replaceNum(tr.find("select[name='qty_uom_id[]']").find("option:selected").attr("cost-uom-"+priceType));
        // Check Product With Qty Order And Total Qty Sale
        if(productId != ""){
            if(qtyOrder > totalQtySales){
                condition = false;
                qty = 0;
                qtyFree = 0;
                $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_OUT_OF_STOCK; ?></p>').dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
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
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            tr.find("input[name='qty[]']").select().focus();
                            $(this).dialog("close");
                        }
                    }
                });
            }
        }
        // Calculate Discount & Total Price
        if(condition == true){
            totalPriceBfDis = converDicemalJS(qty * unitPrice);
            if(discountPercent != '' && discountPercent > 0){
                discount        = replaceNum(converDicemalJS((totalPriceBfDis * discountPercent) / 100).toFixed(2));
            }else{
                discount        = discountAmount;
            }
            totalPrice = converDicemalJS(totalPriceBfDis - discount);
        }
        // Assign Value to Qty, Discount & Total Price
        tr.find("input[name='qty[]']").val(qty);
        tr.find("input[name='qty_free[]']").val(qtyFree);
        tr.find("input[name='discount_amount[]']").val(discount);
        tr.find("input[name='discount[]']").val(discount);
        tr.find("input[name='total_price_bf_dis[]']").val((totalPriceBfDis).toFixed(2));
        tr.find("input[name='total_price[]']").val((totalPrice).toFixed(2));
        tr.find("input[name='unit_cost[]']").val(unitCost);
        if(replaceNum(unitCost) > replaceNum(unitPrice)){
            tr.find(".priceDownSales").show();
            tr.find(".unit_price").css("color", "red");
        } else {
            tr.find(".priceDownSales").hide();
            tr.find(".unit_price").css("color", "#000");
        }
        // Calculate Total Amount
        calcTotalAmountSales();
    }
    
    function addNewDiscountSO(tr){
        if($("#SalesConsignmentCompanyId").val() != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/discount"; ?>/"+$("#SalesConsignmentCompanyId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo SALES_ORDER_SELECT_DISCOUNT; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 550,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var discountTr = $("input[name='chkDiscount']:checked").closest("tr");
                                if(discountTr != "" && discountTr != undefined){
                                    tr.find("input[name='discount_id[]']").val(discountTr.find("input[name='chkDiscount']").val());
                                    tr.find("input[name='discount_amount[]']").val(discountTr.find("input[name='salesOrderDiscountAmount']").val());
                                    tr.find("input[name='discount_percent[]']").val(discountTr.find("input[name='salesOrderDiscountPercent']").val());
                                    tr.find("input[name='discount[]']").css("display", "inline");
                                    tr.find(".btnRemoveDiscountSO").css("display", "inline");
                                    funcCheckCondiSales(tr.find("input[name='discount_id[]']"));
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function calcTotalAmountSales(){
        var totalSubAmount   = 0;
        var totalVat         = 0;
        var totalAmount      = 0;
        var totalVatPercent  = replaceNum($("#SalesConsignmentVatPercent").val());
        var totalDiscount    = replaceNum($("#SalesConsignmentDiscountUs").val());
        var totalDisPercent  = replaceNum($("#SalesConsignmentDiscountPercent").val());
        var vatCal           = $("#SalesConsignmentVatCalculate").val();
        var totalBfDis       = 0;
        var totalAmtCalVat   = 0;
        $(".tblSOList").find(".total_price").each(function(){
            if(replaceNum($(this).val()) != '' || $(this).val() != undefined ){
                totalSubAmount += replaceNum($(this).val());
            }
        });
        if(isNaN(totalAmount)){
            $("#SalesConsignmentTotalAmount").val(0.00);
            $("#SalesConsignmentDiscountUs").val(0.00);
            $("#SalesConsignmentSubTotalAmount").val(0.00);
            $("#SalesConsignmentVatPercent").val(10);
            $("#SalesConsignmentTotalVat").val(0.00);
        }else{
            if(totalDisPercent > 0){
                totalDiscount  = replaceNum(converDicemalJS((totalSubAmount * totalDisPercent) / 100).toFixed(2));
            }
            totalAmtCalVat = replaceNum(converDicemalJS(totalSubAmount - totalDiscount));
            // Check VAT Calculate Before Discount, Free, Mark Up
            if(vatCal == 1){
                $(".tblSOList").each(function(){
                    var qty   = replaceNum($(this).find(".qty").val()) + replaceNum($(this).find(".qty_free").val());
                    var price = replaceNum($(this).find(".unit_price").val());
                    totalBfDis += replaceNum(converDicemalJS(qty * price));
                });
                totalAmtCalVat = totalBfDis;
            }
            totalVat = replaceNum(converDicemalJS(converDicemalJS(totalAmtCalVat * totalVatPercent) / 100).toFixed(2));
            totalAmount = converDicemalJS((totalSubAmount - totalDiscount) + totalVat);
            $("#SalesConsignmentTotalAmount").val((totalSubAmount).toFixed(2));
            $("#SalesConsignmentDiscountUs").val((totalDiscount).toFixed(2));
            $("#SalesConsignmentTotalVat").val((totalVat).toFixed(2));
            $("#SalesConsignmentSubTotalAmount").val((totalAmount).toFixed(2));
        }
    }
    
    function sortNuTableSO(){
        var sort = 1;
        $(".tblSOList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOrderSales(id){
        var totalProduct=0;
        $("input[name='product_id[]']").each(function(){
            if($(this).val() == id){
                totalProduct += replaceNum($(this).closest("tr").find(".totalQtyOrderSales").val());
            }
        });
        return totalProduct;
    }
    
    function addNoteSO(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteComment'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_NOTE; ?>',
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
                    note.val($("#noteComment").val());
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function showOriginalNameSales(puc, sku, name){
        var orgName = '';
        orgName += 'PUC: '+puc;
        orgName += '<br/><br/>SKU: '+puc;
        orgName += '<br/><br/>Name: '+name;
        return orgName;
    }
    
    function showProductInfoSales(currentTr){
        var customerId = $("#SalesConsignmentCustomerId").val();
        var productId  = currentTr.closest("tr").find(".product_id").val();
        if(productId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/productHistory"; ?>/"+productId+"/"+customerId,
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
<div class="inputContainer" style="width:100%" id="searchFormSales">
    <table width="100%">
        <tr>
            <td style="text-align: left;" id="divSearchSales">
            <?php
                if ($allowaddServiceSO) {
            ?>
            <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServiceSO" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" />
            <?php
                }
            ?>
            <?php
                if ($allowAddMisc) {
            ?>
            &nbsp;&nbsp;<img alt="<?php echo SALES_ORDER_ADD_MISCELLANEOUS; ?>" style="cursor: pointer; height: 20px;" align="absmiddle" class="addMiscellaneousSO" onmouseover="Tip('<?php echo SALES_ORDER_ADD_MISCELLANEOUS; ?>')" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" />
            <?php
                }
            ?>    
            </td>
            <td style="text-align:right; width: 450px;">
                <div style="width:100%; float: right;">
                    <label for="typeOfPriceSO"><?php echo TABLE_PRICE_TYPE; ?> :</label> &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceSO" name="data[SalesConsignment][price_type_id]" style="height: 30px; width: 50%">
                        <?php
                        $sqlPrice = mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM price_type_companies WHERE price_type_id = price_types.id) AS company_id FROM price_types WHERE is_active = 1 AND is_ecommerce = 0 AND id IN (SELECT price_type_id FROM price_type_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") GROUP BY price_type_companies.price_type_id) ORDER BY ordering ASC");
                        while($row = mysql_fetch_array($sqlPrice)){
                        ?>
                        <option value="<?php echo $row['id']; ?>" comp="<?php echo $row['company_id']; ?>"><?php echo $row['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>
<table id="tblSOHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%">
    <tr>
        <th class="first" style="width:4%;"><?php echo TABLE_NO ?></th>
        <th style="width:9%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:9%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:16%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:7%;"><?php echo TABLE_QTY ?></th>
        <th style="width:7%;"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:10%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:11%;"><?php echo SALES_ORDER_UNIT_PRICE ?></th>
        <th style="width:9%;"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:11%;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblSO" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListSO" class="tblSOList" style="visibility: hidden;">
            <td class="first" style="width:4%; text-align: center; padding: 0px;"></td>
            <td style="width:9%; text-align: left; padding: 5px;"><span class="lblUPC"></span></td>
            <td style="width:9%; text-align: left; padding: 5px;"><span class="lblSKU"></span></td>
            <td style="width:16%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">
                    <input type="hidden" class="totalQtySales" />
                    <input type="hidden" class="totalQtyOrderSales" />
                    <input type="hidden" name="product_id[]" class="product_id" />
                    <input type="hidden" name="service_id[]" />
                    <input type="hidden" name="discount_id[]" />
                    <input type="hidden" name="discount_amount[]" value="0" />
                    <input type="hidden" name="discount_percent[]" value="0" />
                    <input type="hidden" name="conversion[]" class="conversion" value="1" />
                    <input type="hidden" name="small_uom_val[]" class="small_uom_val" value="1" />
                    <input type="hidden" name="note[]" id="note" class="note" />
                    <input type="hidden" class="orgProName" />
                    <input type="text" id="product" readonly="readonly" name="product[]" class="product validate[required]" style="width: 75%;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductSaleInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="width:7%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:60%;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:7%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" style="width:60%;" class="floatQty qty_free" />
                </div>
            </td>
            <td style="width:10%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php 
                        foreach($uoms as $uom){
                        ?>
                        <option conversion="1" value="<?php echo $uom['Uom']['id']; ?>"><?php echo $uom['Uom']['name']; ?></option>
                        <?php 
                        } 
                        ?>
                    </select>
                </div>
            </td>
            <td style="width: 11%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />
                    <input type="text" id="unit_price" value="0" name="unit_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> style="width:60%;" class="float unit_price validate[required]" />
                    <img alt="<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>" src="<?php echo $this->webroot . 'img/button/down.png'; ?>" style="display: none;" class="priceDownSales" align="absmiddle" onmouseover="Tip('<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>')" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%;">
                    <?php
                    if ($allowProductDiscount) {
                        ?>
                        <input type="text" class="discount btnDiscountSO float" name="discount[]" style="width: 60%;" readonly="readonly" />
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                        <?php
                    }else{
                    ?>
                        <input type="hidden" class="discount btnDiscountSO float" name="discount[]" style="width: 60%;" readonly="readonly" />
                    <?php
                    }
                    ?>
                </div>
            </td>
            <td style="white-space: nowrap;vertical-align: top; width:11%;">
                <div class="inputContainer" style="width:100%;">
                    <input type="hidden" id="total_price_bf_dis" value="0" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                    <input type="text" name="total_price[]" value="0" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> style="width:80%" class="total_price float" />
                </div>
            </td>
            <td style="width:7%;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
    </table>
</div>