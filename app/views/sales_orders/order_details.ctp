<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$allowLots    = false;
$allowExpired = false;
$priceDecimal = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 40){
        $priceDecimal = $rowSetting['value'];
    }
}
// Authentication
$this->element('check_access');
$allowaddServiceSO = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowProductDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowEditPrice  = checkAccess($user['User']['id'], $this->params['controller'], 'editUnitPrice');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
?>
<script type="text/javascript">
    var rowTableSO    =  $("#OrderListSO");
    var rowIndexSales = 0;
    var timeBarcodeSO = 1;
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
        $("#btnHideShowHeaderSalesOrder").click(function(){
            var SalesOrderCompanyId       = $("#SalesOrderCompanyId").val();
            var SalesOrderBranchId        = $("#SalesOrderBranchId").val();
            var SalesOrderLocationGroupId = $("#SalesOrderLocationGroupId").val();
            var SalesOrderOrderDate       = $("#SalesOrderOrderDate").val();
            var SalesOrderCustomerName    = $("#SalesOrderCustomerName").val();
            
            if(SalesOrderCompanyId == "" || SalesOrderBranchId == "" || SalesOrderLocationGroupId == "" || SalesOrderOrderDate == "" || SalesOrderCustomerName == ""){
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
                    }, 300, "Finish");
                }
            }   
        });
        
        $("#SalesOrderDiscountUs").keyup(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
            if(replaceNum($("#SalesOrderDiscountUs").val()) > replaceNum($("#SalesOrderTotalAmount").val())){
                $("#SalesOrderDiscountUs").val($("#SalesOrderTotalAmount").val());
            }
            calcTotalAmountSales();
        });
        
        $("#SalesOrderDiscountUs").focus(function(){
            if($(this).val() == "0.000" || $(this).val() == "0" || $(this).val() == "0.00"){
                $(this).val('');
            }
        });
        $("#SalesOrderDiscountUs").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
       
        $("#searchProductSkuSales").autocomplete("<?php echo $this->base . "/sales_orders/searchProduct/"; ?>", {
            width: 400,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $("#searchProductSkuSales").val(code);
            if(timeBarcodeSO == 1){
                timeBarcodeSO = 2;
                searchProductByCodeSales(code, '', 1, '', '');
            }
        });
        
        $("#searchProductSkuSales").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(timeBarcodeSO == 1){
                    timeBarcodeSO = 2;
                    searchProductByCodeSales($(this).val(), '', 1, '', '');
                }
                return false;
            }
        });
        
        $(".searchProductListSales").click(function(){
            searchAllProductSO();
        });

        $(".addServiceSO").click(function(){
            searchAllServiceSO();
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
        <?php
        if($allowAddProduct){
        ?>
        $("#addProductSales").click(function(){
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
        // Deposit
        $("#SalesOrderTotalDeposit").autoNumeric({mDec: 2, aSep: ','});
        $("#SalesOrderTotalDeposit").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#SalesOrderTotalDeposit").blur(function(){
            if($(this).val() == ''){
                $(this).val('0.00');
            } else if(replaceNum($(this).val()) > replaceNum($("#SalesOrderSubTotalAmount").val())){
                $(this).val($("#SalesOrderSubTotalAmount").val());
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
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(<?php echo $priceDecimal; ?>));
                    funcCheckCondiSales($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchAllProductSO(){
        if($("#SalesOrderCompanyId").val() == "" || $("#SalesOrderBranchId").val() == "" || $("#SalesOrderLocationGroupId").val() == "" || $("#SalesOrderCustomerId").val() == ""){
            alertSelectRequireField();
        }else{
            var dateOrder = $("#SalesOrderOrderDate").val().split("/")[2]+"-"+$("#SalesOrderOrderDate").val().split("/")[1]+"-"+$("#SalesOrderOrderDate").val().split("/")[0];
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/product/"; ?>" + $("#SalesOrderCompanyId").val()+"/"+$("#SalesOrderLocationGroupId").val()+"/"+$("#SalesOrderBranchId").val(),
                data:   "order_date="+dateOrder,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeSO == 1;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 500,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $(this).dialog("close");
                                if($("input[name='chkProductSales']:checked").val()){
                                    var qty   = parseFloat($("input[name='chkProductSales']:checked").attr('class'));
                                    var lot   = $("input[name='chkProductSales']:checked").attr('lot');
                                    var exp   = $("input[name='chkProductSales']:checked").attr('exp');
                                    var value = $("input[name='chkProductSales']:checked").val();
                                    if(qty > 0){
                                        if(timeBarcodeSO == 1){
                                            timeBarcodeSO = 2;
                                            searchProductByCodeSales(value, '', 1, lot, exp);
                                        }
                                    }else{
                                        $("#dialog").html('<p style="font-size: 16px;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_OUT_OF_STOCK; ?></p>');
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_WARNING; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 300,
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
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllServiceSO(){
        if($("#SalesOrderCompanyId").val()=="" || $("#SalesOrderBranchId").val() == "" || $("#SalesOrderLocationGroupId").val() == "" || $("#SalesOrderCustomerId").val() == ""){
            alertSelectRequireField();
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/service"; ?>/"+$("#SalesOrderCompanyId").val()+"/"+$("#SalesOrderBranchId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeSO == 1;
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
        var btnHeader    = $("#btnHideShowHeaderSalesOrder").height();
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

    function searchProductByCodeSales(productCode, uomSelected, qtyOrder, lotsNumber, expiredDate){
        if($("#SalesOrderCompanyId").val() == "" || $("#SalesOrderBranchId").val() == "" || $("#SalesOrderLocationGroupId").val() == "" || $("#SalesOrderCustomerId").val() == ""){
            $("#searchProductSkuSales").val('');
            timeBarcodeSO = 1;
            alertSelectRequireField();
        }else{
            var dateOrder = $("#SalesOrderOrderDate").val().split("/")[2]+"-"+$("#SalesOrderOrderDate").val().split("/")[1]+"-"+$("#SalesOrderOrderDate").val().split("/")[0];
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . "/sales_orders/searchProductByCode"; ?>/"+$("#SalesOrderCompanyId").val()+"/"+$("#SalesOrderCustomerId").val()+"/"+$("#SalesOrderBranchId").val(),
                data:   "data[code]=" + encodeURIComponent(productCode) +
                        "&data[order_date]=" + dateOrder +
                        "&data[lots_number]=" + lotsNumber +
                        "&data[expired_date]=" + expiredDate +
                        "&data[location_group_id]=" + $("#SalesOrderLocationGroupId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#searchProductSkuSales").val('');
                    if(parseFloat(msg.total_qty) >= parseFloat(qtyOrder) && parseFloat(msg.total_qty) > 0 && msg.product_id != "" && parseFloat(msg.product_id) > 0){
                        if(msg.packet != ''){
                            var packet = msg.packet.toString().split("--");
                            var loop = 1;
                            var time = 0;
                            $.each(packet,function(key, item){
                                var items = item.toString().split("||");
                                var productCode = items[0];
                                var uomSelected = items[1];
                                var qtyOrder    = items[2];
                                if(loop > 1){
                                    time += 300;
                                }
                                setTimeout(function () {
                                    searchProductByCodeSales(productCode, uomSelected, qtyOrder, '', '');
                                }, time);
                                loop++;
                            });
                        }else{
                            addProductSales(uomSelected, qtyOrder, msg);
                        }
                    }else if((msg.total_qty == 0 || parseFloat(msg.total_qty) < parseFloat(qtyOrder)) && msg.product_id != "" && parseFloat(msg.product_id) > 0){
                        timeBarcodeSO = 1;
                        $("#dialog").html('<p style="font-size: 16px;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_OUT_OF_STOCK; ?> : '+msg.product_barcode+' - '+msg.product_name+'</p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_WARNING; ?>',
                            resizable: false,
                            modal: true,
                            width: 300,
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
                        timeBarcodeSO = 1;
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TABLE_NO_PRODUCT; ?></p>');
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
    
    function keyEventSO(){
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
        $(".product, .btnProductSaleInfo, .qty, .qty_free, .unit_price, .qty_uom_id, .total_price, .btnDiscountSO, .btnRemoveDiscountSO, .btnRemoveSO, .noteAddSO, .lots_number").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: <?php echo $priceDecimal; ?>, aSep: ','});
        $(".floatQty").priceFormat();
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#SalesOrderCustomerId").val();
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
        
        $(".qty, .unit_price, .total_price, .qty_free").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qty, .unit_price, .total_price, .qty_free").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        $(".qty, .qty_free").keyup(function(){
            var conversion = $(this).closest("tr").find(".conversion").val();
            var qty        = replaceNum($(this).closest("tr").find(".qty").val()) + replaceNum($(this).closest("tr").find(".qty_free").val());
            var totalOrder = replaceNum(converDicemalJS(qty * conversion));
            $(this).closest("tr").find(".totalQtyOrderSales").val(totalOrder);
            if($(this).closest("tr").find("input[name='promotional_id[]']").val() != ""){ // Check Promotion
                var priceType = parseFloat(replaceNum($("#typeOfPriceSO").val()));
                var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                $(this).closest("tr").find(".unit_price").val(unitPrice);
            } else {
                if(replaceNum($(this).closest("tr").find(".price6").val()) > 0 || replaceNum($(this).closest("tr").find(".price12").val()) > 0){
                    if(replaceNum($(this).closest("tr").find(".qty").val()) >= 6 && replaceNum($(this).closest("tr").find(".qty").val()) < 12){
                        if(replaceNum($(this).closest("tr").find(".price6").val()) > 0){
                            var price6 = replaceNum($(this).closest("tr").find(".price6").val());
                            $(this).closest("tr").find(".unit_price").val(price6);
                        }
                    } else if (replaceNum($(this).closest("tr").find(".qty").val()) >= 12){
                        if(replaceNum($(this).closest("tr").find(".price12").val()) > 0){
                            var price12 = replaceNum($(this).closest("tr").find(".price12").val());
                            $(this).closest("tr").find(".unit_price").val(price12);
                        }
                    } else {
                        var priceType = parseFloat(replaceNum($("#typeOfPriceSO").val()));
                        var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                        $(this).closest("tr").find(".unit_price").val(unitPrice);
                    }
                }
            }
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
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".conversion").val(conversion);
                // Set Name
                var nameDef = $(this).closest("tr").find("input[name='product[]']").attr("data");
                var nameCon = nameDef+"/"+conversion;
                $(this).closest("tr").find("input[name='product[]']").val(nameCon);
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
                $(this).closest("tr").find(".discount").val(discount.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".unit_price").val((unitPrice).toFixed(<?php echo $priceDecimal; ?>));
            }else{
                $(this).val(0);
            }
            funcCheckCondiSales($(this));
        });
        
        $(".btnDiscountSO").click(function(){
            getItemDiscountSales($(this));
            // if($(this).closest("tr").find("input[name='promotional_id[]']").val() != ""){
            //     var chkPromotion  = false;
            //     var uomSelected   = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").val());
            //     var promotionCon  = $(this).closest("tr").find(".promotionalCon").val();
            //     var promotionProduct = $.parseJSON(promotionCon);
            //     $.each(promotionProduct , function(i, val) { 
            //         if(replaceNum(val.uom_id) == replaceNum(uomSelected)){
            //             chkPromotion = true;
            //         }
            //     });
            //     if(chkPromotion == false){
            //         getItemDiscountSales($(this));
            //     }
            // } else {
            //     getItemDiscountSales($(this));
            // }
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
        <?php
        if($allowLots == true){
        ?>
        $(".lots_number").click(function(){
            var productId = $(this).closest("tr").find("input[name='product_id[]']").val();
            var obj       = $(this);
            searchProductByExpSales(productId, obj);
        });
        <?php
        }
        if($allowExpired == true){
        ?>
        $(".date_expired").click(function(){
            var productId = $(this).closest("tr").find("input[name='product_id[]']").val();
            var obj       = $(this);
            searchProductByExpSales(productId, obj);
        });
        <?php
        }
        ?>
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
        var serviceType  = $("#ServiceServiceId").find("option:selected").attr('type');
        tr.removeAttr("style").removeAttr("id");

        tr.find("td:eq(0)").html(rowIndexSales);
        tr.find(".totalQtySales").attr("id", "inv_qty_"+rowIndexSales);
        tr.find(".lblSKU").val(serviceCode);
        tr.find("input[name='product_id[]']").attr("id", "product_id_"+rowIndexSales);
        tr.find("input[name='service_id[]']").attr("id", "service_id_"+rowIndexSales).val(serviceId);
        tr.find("input[name='product[]']").attr("id", "product_"+rowIndexSales).val(serviceName).attr("readonly", true);
        tr.find("input[name='qty[]']").attr("id", "qty_"+rowIndexSales).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+rowIndexSales).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+rowIndexSales).val(servicePrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='total_price_bf_dis[]']").attr("id", "total_price_bf_dis_"+rowIndexSales).val(tr.find("input[name='unit_price[]']").val());
        tr.find("input[name='discount[]']").attr("id", "discount_"+rowIndexSales).val(0);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales);
        tr.find("input[name='note[]']").attr("id", "note_"+rowIndexSales);
        tr.find("input[name='type[]']").attr("id", "typeSales_"+rowIndexSales).val(serviceType);
        tr.find(".btnProductSaleInfo").hide();
        tr.find(".lots_number").hide();
        tr.find(".expired_date").hide();
        if(serviceUomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales).html('<option value="2" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales).find("option[value='"+serviceUomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+serviceUomId+"']").hide();
        }
        if(serviceType == 2){
            tr.find(".divStartSales").show();
            tr.find(".startSales").attr("id", "startSales_"+rowIndexSales).addClass("validate[required]");
        }
        $("#tblSO").append(tr);
        if(serviceType == 2){
            $('#startSales_'+rowIndexSales).datepicker({
                dateFormat:'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                onSelect: function( selectedDate ) {
                    $("#SalesOrderAddForm").validationEngine("hideAll");
                }
            }).unbind("blur");
            var minDate = $("#SalesOrderOrderDate").val();
            $('#startSales_'+rowIndexSales).datepicker("option", "minDate", minDate);
        }
        sortNuTableSO();
        checkEventSO();
        calcTotalAmountSales();
        $("#tblSO").find("tr:last").find(".qty").select().focus();
    }

    function addProductSales(uomSelected, qtyOrder, msg){
        // Product Information
        var productId           = msg.product_id;
        var productSku          = msg.product_code;
        var productUpc          = msg.product_barcode;
        var productName         = msg.product_name;
        var productCusName      = msg.product_cus_name;
        var productPriceUomId   = msg.product_uom_id;
        var smallUomVal         = msg.small_uom_val;
        var productInfo         = showOriginalNameSales(productUpc, productSku, productName);
        var productIsLot        = msg.is_lot;
        var productIsExp        = msg.is_exp;
        var totalQty            = msg.total_qty;
        var lotsNumber          = msg.lots_number;
        var expiredDate         = msg.expired_date;
        var price6              = msg.price6;
        var price12             = msg.price12;
        var tr                  = rowTableSO.clone(true);
        var branchId            = $("#SalesOrderBranchId").val();
        var promotion           = msg.promotion;
        var promoDisAmount      = 0;
        var promoDisPercent     = 0;
        if(msg.promotion.promotion_id != "" && msg.promotion.promotion_id != undefined){
            $.each(promotion.product , function(i, val) { 
                if(replaceNum(val.uom_id) == replaceNum(uomSelected)){
                    promoDisAmount  = replaceNum(val.discount_amount);
                    promoDisPercent = replaceNum(val.discount_percent);
                }
            });
            tr.find(".promotionalCon").val(JSON.stringify(promotion.product));
            tr.find("input[name='promotional_id[]']").val(promotion.promotion_id);
        }
        
        rowIndexSales = Math.floor((Math.random() * 100000) + 1);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(rowIndexSales);
        tr.find("input[name='product_id[]']").attr("id", "product_id_"+rowIndexSales).val(productId);
        tr.find("input[name='service_id[]']").attr("id", "service_id_"+rowIndexSales);
        tr.find(".lblUPC").val(productUpc);
        tr.find(".lblSKU").val(productSku);
        tr.find(".orgProName").val(productInfo);
        tr.find("input[name='product[]']").attr("data", productCusName);
        tr.find("input[name='product[]']").attr("id", "product_"+rowIndexSales).val(productCusName+"/"+smallUomVal).removeAttr('readonly');
        tr.find("input[name='qty[]']").attr("id", "qty_"+rowIndexSales).val(qtyOrder).attr('readonly', true);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+rowIndexSales).val(0);
        tr.find("input[name='small_uom_val[]']").val(smallUomVal);
        tr.find(".totalQtyOrderSales").attr("id", "total_qty_"+rowIndexSales).val(smallUomVal);
        tr.find(".totalQtySales").attr("id", "inv_qty_"+rowIndexSales).val(totalQty);
        tr.find("input[name='discount[]']").attr("id", "discount_"+rowIndexSales).val(0);
        tr.find("input[name='note[]']").attr("id", "note_"+rowIndexSales);
        tr.find("input[name='conversion[]']").attr("id", "conversion_"+rowIndexSales).val(smallUomVal);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexSales).html('<option value="">Please Select Uom</option>');
        tr.find(".price6").val(price6);
        tr.find(".price12").val(price12);
        tr.find(".lots_number").attr("id", "lots_number"+rowIndexSales);
        tr.find(".expired_date").attr("id", "expired_date"+rowIndexSales);
        if(productIsLot == 1){
            tr.find("input[name='lots_number[]']").addClass("validate[required]").val(lotsNumber);
        }else{
            tr.find("input[name='lots_number[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0');
        }
        if(productIsExp == 1){
            tr.find("input[name='expired_date[]']").addClass("validate[required]").val(expiredDate);
        }else{
            tr.find("input[name='expired_date[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0000-00-00');
        }
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/uoms/getRelativeUom/"; ?>"+productPriceUomId+"/all/"+productId+"/"+branchId,
            success: function(msg){
                timeBarcodeSO = 1;
                var complete = false;
                tr.find("select[name='qty_uom_id[]']").html(msg).val(1);
                tr.find("select[name='qty_uom_id[]']").find("option").each(function(){
                    if($(this).attr("conversion") == 1 && uomSelected == ''){
                        $(this).attr("selected", true);
                        // Price
                        var priceType     = $("#typeOfPriceSO").val();
                        var lblPrice      = "price-uom-"+priceType;
                        var productPrice  = parseFloat($(this).attr(lblPrice));
                        complete = true;
                    } else{
                        if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                            $(this).attr("selected", true);
                            // Price
                            var priceType     = $("#typeOfPriceSO").val();
                            var lblPrice      = "price-uom-"+priceType;
                            var productPrice  = parseFloat($(this).attr(lblPrice));
                            complete = true;
                        }
                    }
                    if(complete == true){
                        var totalPrice = converDicemalJS(productPrice * parseFloat(qtyOrder));
                        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+rowIndexSales).val(productPrice);
                        tr.find("input[name='total_price_bf_dis[]']").attr("id", "total_price_bf_dis_"+rowIndexSales).val(totalPrice);
                        tr.find("input[name='total_price[]']").attr("id", "total_price_"+rowIndexSales).val(totalPrice);
                        // Promotion Discount
                        if(promoDisAmount > 0){
                            tr.find("input[name='discount_amount[]']").val(promoDisAmount);
                        }
                        if(promoDisPercent > 0){
                            tr.find("input[name='discount_percent[]']").val(promoDisPercent);
                        }
                        // Function Check
                        funcCheckCondiSales(tr.find("select[name='qty_uom_id[]']"));
                        checkEventSO();
                        tr.find("input[name='qty[]']").removeAttr('readonly');
                        tr.find("input[name='qty[]']").select().focus();
                    }
                    return false;
                });
            }
        });
        $("#tblSO").append(tr);
        sortNuTableSO();
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
        var lotsNumber    = tr.find("input[name='lots_number[]']").val();
        var expiredDate   = tr.find("input[name='expired_date[]']").val();
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
        if(lotsNumber == ''){
            lotsNumber = '0';
        }
        if(expiredDate == ''){
            expiredDate = '0000-00-00';
        }
        var qtyOrder        = replaceNum(getTotalQtyOrderSales(productId, lotsNumber, expiredDate));
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
            var promotionCon  = tr.find(".promotionalCon").val();
            var uomSelected   = replaceNum(tr.find("select[name='qty_uom_id[]']").find("option:selected").val());
            if(promotionCon != ""){
                var promoDisAmount   = 0;
                var promoDisPercent  = 0;
                var promotionProduct = $.parseJSON(promotionCon);
                $.each(promotionProduct , function(i, val) { 
                    if(replaceNum(val.uom_id) == uomSelected){
                        promoDisAmount  = replaceNum(val.discount_amount);
                        promoDisPercent = replaceNum(val.discount_percent);
                    }
                });
                if(promoDisPercent > 0){
                    tr.find("input[name='discount_percent[]']").val(promoDisPercent);
                    discount = replaceNum(converDicemalJS((totalPriceBfDis * promoDisPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
                } else {
                    discount = converDicemalJS(promoDisAmount * qty);
                }
            } else {
                if(discountPercent != '' && discountPercent > 0){
                    discount = replaceNum(converDicemalJS((totalPriceBfDis * discountPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
                }else{
                    discount = discountAmount;
                }
            }
            totalPrice = converDicemalJS(totalPriceBfDis - discount);
        }
        // Assign Value to Qty, Discount & Total Price
        tr.find("input[name='qty[]']").val(qty);
        tr.find("input[name='qty_free[]']").val(qtyFree);
        tr.find("input[name='discount_amount[]']").val(discount);
        tr.find("input[name='discount[]']").val(discount);
        tr.find("input[name='total_price_bf_dis[]']").val((totalPriceBfDis).toFixed(<?php echo $priceDecimal; ?>));
        tr.find("input[name='total_price[]']").val((totalPrice).toFixed(<?php echo $priceDecimal; ?>));
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
    
    function getItemDiscountSales(obj){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/sales_orders/invoiceDiscount"; ?>",
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
                            obj.closest("tr").find("input[name='discount_id[]']").val(0);
                            obj.closest("tr").find("input[name='discount_amount[]']").val(totalDisAmt);
                            obj.closest("tr").find("input[name='discount_percent[]']").val(totalDisPercent);
                            obj.closest("tr").find("input[name='discount[]']").css("display", "inline");
                            obj.closest("tr").find(".btnRemoveDiscountSO").css("display", "inline");
                            funcCheckCondiSales(obj);
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function calcTotalAmountSales(){
        var totalSubAmount   = 0;
        var totalVat         = 0;
        var totalAmount      = 0;
        var totalVatPercent  = replaceNum($("#SalesOrderVatPercent").val());
        var totalDiscount    = replaceNum($("#SalesOrderDiscountUs").val());
        var totalDisPercent  = replaceNum($("#SalesOrderDiscountPercent").val());
        var vatCal           = $("#SalesOrderVatCalculate").val();
        var totalBfDis       = 0;
        var totalAmtCalVat   = 0;
        $(".tblSOList").find(".total_price").each(function(){
            if(replaceNum($(this).val()) != '' || $(this).val() != undefined ){
                totalSubAmount += replaceNum($(this).val());
            }
        });
        if(isNaN(totalAmount)){
            $("#SalesOrderTotalAmount").val(0.00);
            $("#SalesOrderDiscountUs").val(0.00);
            $("#SalesOrderSubTotalAmount").val(0.00);
            $("#SalesOrderVatPercent").val(10);
            $("#SalesOrderTotalVat").val(0.00);
        }else{
            if(totalDisPercent > 0){
                totalDiscount  = replaceNum(converDicemalJS((totalSubAmount * totalDisPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
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
            totalVat = replaceNum(converDicemalJS(converDicemalJS(totalAmtCalVat * totalVatPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
            totalAmount = converDicemalJS((totalSubAmount - totalDiscount) + totalVat);
            $("#SalesOrderTotalAmount").val((totalSubAmount).toFixed(<?php echo $priceDecimal; ?>));
            $("#SalesOrderDiscountUs").val((totalDiscount).toFixed(<?php echo $priceDecimal; ?>));
            $("#SalesOrderTotalVat").val((totalVat).toFixed(<?php echo $priceDecimal; ?>));
            $("#SalesOrderSubTotalAmount").val((totalAmount).toFixed(<?php echo $priceDecimal; ?>));
        }
    }
    
    function sortNuTableSO(){
        var sort = 1;
        $(".tblSOList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOrderSales(id, lotsNumber, expiredDate){
        var totalProduct=0;
        $("input[name='product_id[]']").each(function(){
            var lot = $(this).closest("tr").find("input[name='lots_number[]']").val();
            var exp = $(this).closest("tr").find("input[name='expired_date[]']").val();
            if($(this).val() == id && lot == lotsNumber && exp == expiredDate){
                totalProduct += replaceNum($(this).closest("tr").find(".totalQtyOrderSales").val());
            }
        });
        return totalProduct;
    }
    
    function addNoteSO(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteComment'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_MEMO; ?>',
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
        var customerId = $("#SalesOrderCustomerId").val();
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
    
    // Search Product By Expiry Date
    function searchProductByExpSales(productId, obj){
        var locationGroupId = $("#SalesOrderLocationGroupId").val();
        var orderDate       = $("#SalesOrderOrderDate").val().toString().split("/")[2]+"-"+$("#SalesOrderOrderDate").val().toString().split("/")[1]+"-"+$("#SalesOrderOrderDate").val().toString().split("/")[0];
        if(locationGroupId != '' && orderDate != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/getProductByExp/"; ?>"+productId+"/"+locationGroupId+"/"+orderDate,
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
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $(this).dialog("close");
                                if($("input[name='chkProductByExpSales']:checked").val()){
                                    var totalQty = $("input[name='chkProductByExpSales']:checked").val();
                                    var proLot   = $("input[name='chkProductByExpSales']:checked").attr("lot");
                                    var proExp   = $("input[name='chkProductByExpSales']:checked").attr("exp");
                                    obj.closest("tr").find(".totalQtySales").val(totalQty);
                                    obj.closest("tr").find("input[name='lots_number[]']").val(proLot);
                                    obj.closest("tr").find("input[name='expired_date[]']").val(proExp);
                                    funcCheckCondiSales(obj);
                                }
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
            <td style="width: 410px;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="searchProductSkuSales" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductSales" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="searchProductSkuSales" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td style="width: 20%; text-align: left;" id="divSearchSales">
            <img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer;" class="searchProductListSales" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            <?php
                if ($allowaddServiceSO) {
            ?>
            <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServiceSO" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" />
            <?php
                }
            ?>   
            </td>
            <td style="text-align:right">
                <div style="width:100%; float: right;">
                    <label for="typeOfPriceSO"><?php echo TABLE_PRICE_TYPE; ?> :</label> &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceSO" name="data[SalesOrder][price_type_id]" style="height: 30px; width: 250px;">
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
        <th style="width:8%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:16%;"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:9%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:9%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:9%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXP_DATE_SHORT; ?></th>
        <th style="width:7%;"><?php echo TABLE_QTY ?></th>
        <th style="width:7%;"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:9%;"><?php echo SALES_ORDER_UNIT_PRICE ?></th>
        <th style="width:9%;"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:9%;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblSO" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListSO" class="tblSOList" style="visibility: hidden;">
            <td class="first" style="width:4%; text-align: center; padding: 0px;"></td>
            <td style="width:8%; text-align: left; padding: 5px;"><input type="text" readonly="" style="width: 95%; height: 25px;" class="lblSKU" /></td>
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
                    <input type="hidden" name="type[]" class="typeSales" />
                    <input type="hidden" name="promotional_id[]" class="promotional_id" />
                    <input type="hidden" class="promotionalCon" />
                    <input type="hidden" class="price6" />
                    <input type="hidden" class="price12" />
                    <input type="hidden" class="orgProName" />
                    <input type="hidden" name="order_detail_id[]" value="" />
                    <input type="text" id="product" data="" readonly="readonly" name="product[]" class="product validate[required]" style="width: 75%; height: 25px;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddSO" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductSaleInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
                <div class="inputContainer divStartSales" style="width:100%; margin-top: 3px; display: none;">
                    <input type="text" name="start[]" id="startSales" class="startSales" readonly="readonly" style="width: 75%; height: 25px;" placeholder="<?php echo TABLE_START_DATE; ?>" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id" style="width:80%; height: 25px;" name="qty_uom_id[]" class="qty_uom_id validate[required]">
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
            <td style="width:9%; text-align: left; padding: 5px; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="lots_number[]" style="width: 90%; height: 25px;" class="lots_number" readonly="" />
                </div>
            </td>
            <td style="width:9%; text-align: left; padding: 5px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="expired_date[]" style="width: 90%; height: 25px;" class="expired_date" readonly="" />
                </div>
            </td>
            <td style="width:7%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:80%; height: 25px;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:7%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" style="width:80%; height: 25px;" class="floatQty qty_free" />
                </div>
            </td>
            <td style="width: 9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />
                    <input type="text" id="unit_price" value="0" name="unit_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> style="width:60%; height: 25px;" class="float unit_price validate[required]" />
                    <img alt="<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>" src="<?php echo $this->webroot . 'img/button/down.png'; ?>" style="display: none;" class="priceDownSales" align="absmiddle" onmouseover="Tip('<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>')" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%;">
                    <?php
                    if ($allowProductDiscount) {
                        ?>
                        <input type="text" class="discount btnDiscountSO float" name="discount[]" style="width: 60%; height: 25px;" readonly="readonly" />
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountSO" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                        <?php
                    }else{
                    ?>
                        <input type="hidden" class="discount btnDiscountSO float" name="discount[]" readonly="readonly" />
                    <?php
                    }
                    ?>
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%;">
                    <input type="hidden" id="total_price_bf_dis" value="0" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                    <input type="text" name="total_price[]" value="0" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> style="width:80%; height: 25px;" class="total_price float" />
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