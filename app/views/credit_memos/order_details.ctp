<?php
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
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
// Authentication
$this->element('check_access');
$allowAddService = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowProductDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowEditPrice  = checkAccess($user['User']['id'], $this->params['controller'], 'editUnitPrice');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
?>
<script type="text/javascript">
    var tblRowAddCM  = $("#OrderListAddCM");
    var indexRowCM   = 0;
    var searchCodeCM = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OrderListAddCM").remove();
        var waitForFinalEventAddCM = (function () {
          var timersAddCM = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timersAddCM[uniqueId]) {
              clearTimeout (timersAddCM[uniqueId]);
            }
            timersAddCM[uniqueId] = setTimeout(callback, ms);
          };
        })();
        
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabCreditMemoReg != tabCreditMemoId){
            $("a[href='"+tabCreditMemoId+"']").click(function(){
                if($(".orderDetailCreditMemo").html() != '' && $(".orderDetailCreditMemo").html() != null){
                    waitForFinalEventAddCM(function(){
                        refreshScreenAddCM();
                        resizeFormTitleAddCM();
                        resizeFornScrollAddCM();
                    }, 300, "Finish");
                }
            });
            tabCreditMemoReg = tabCreditMemoId;
        }
        
        waitForFinalEventAddCM(function(){
                refreshScreenAddCM();
                resizeFormTitleAddCM();
                resizeFornScrollAddCM();
        }, 300, "Finish");
        
        $(window).resize(function(){
            if(tabCreditMemoReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventAddCM(function(){
                    refreshScreenAddCM();
                    resizeFormTitleAddCM();
                    resizeFornScrollAddCM();
                }, 300, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderCreditMemo").click(function(){
            var CreditMemoCompanyId       = $("#CreditMemoCompanyId").val();
            var CreditMemoLocationGroupId = $("#CreditMemoLocationGroupId").val();
            var CreditMemoLocationId      = $("#CreditMemoLocationId").val();
            var CreditMemoOrderDate       = $("#CreditMemoOrderDate").val();
            var CreditMemoCustomerId      = $("#CreditMemoCustomerId").val();
            var CreditMemoReasonId        = $("#CreditMemoReasonId").val();
            
            if(CreditMemoCompanyId == "" || CreditMemoLocationGroupId == "" || CreditMemoLocationId == "" || CreditMemoOrderDate == "" || CreditMemoCustomerId == "" || CreditMemoReasonId == ""){
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
                    $("#SOTopAddCM").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#SOTopAddCM").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabCreditMemoReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventAddCM(function(){
                        resizeFornScrollAddCM();
                    }, 300, "Finish");
                }
            }
        });
        
        $("#CreditMemoDiscountPercent").keyup(function(){
            $("#CreditMemoMarkUp").val(0);
            if($(this).val() == ""){
                $(this).val(0);
            }
            var totalAmt = replaceNum($("#CreditMemoTotalAmount").val());
            var totalDis = (totalAmt * replaceNum($(this).val())) / 100;
            if(totalDis > totalAmt){
                $(this).val(100);
            }
            getTotalAmountCM();
        });

        $("#CreditMemoMarkUp").keyup(function(){
            $("#CreditMemoDiscountPercent").val(0);
            if($(this).val() == ""){
                $(this).val(0);
            }
            getTotalAmountCM();
        });
        
        $("#CreditMemoDiscountPercent, #CreditMemoMarkUp").focus(function(){
            if($(this).val() == "0.000" || $(this).val() == "0" || $(this).val() == "0.00"){
                $(this).val('');
            }
        });
        $("#CreditMemoDiscountPercent, #CreditMemoMarkUp").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
       
        $("#SearchProductSkuCM").autocomplete("<?php echo $this->base . "/credit_memos/searchProduct/"; ?>", {
            width: 400,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $("#SearchProductSkuCM").val(code);
            if(searchCodeCM == 1){
                searchCodeCM = 2;
                searchProductByCodeCM(code, '', 1);
            }
        });
        
        $("#SearchProductSkuCM").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchCodeCM == 1){
                    searchCodeCM = 2;
                    searchProductByCodeCM($(this).val(), '', 1);
                }
                return false;
            }
        });
        
        $(".searchProductListCM").click(function(){
            searchProductCM();
        });

        $(".addServiceCM").click(function(){
            searchServiceCM();
        });
        
        // Change Price Type
        $("#typeOfPriceCM").change(function(){
            if($(".tblCMList").find(".product_id").val() == undefined){
                changePriceTypeCM();
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
                            changePriceTypeCM();
                            $.cookie("typePriceCM", $("#typeOfPriceCM").val(), {expires : 7, path : '/'});
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#typeOfPriceCM").val($.cookie('typePriceCM'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        <?php
        if($allowAddProduct){
        ?>
        $("#addNewProductCM").click(function(){
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
        
        changeInputCSSCM();
    }); // End Document Ready
    
    function changePriceTypeCM(){
        if($(".product").val() != undefined && $(".product").val() != ''){
            var priceType  = parseFloat(replaceNum($("#typeOfPriceCM").val()));
            $(".tblCMList").each(function(){
                if($(this).find("input[name='product_id[]']").val() != ''){
                    var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(<?php echo $priceDecimal; ?>));
                    calculateTotalRowCM($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchProductCM(){
        if($("#CreditMemoCompanyId").val() == "" || $("#CreditMemoBranchId").val() == "" || $("#CreditMemoLocationId").val() == "" || $("#CreditMemoCustomerName").val() == ""){
            timeSearchAddCM = 1;
            alertSelectRequireField();
        }else{
            var dateOrder = $("#CreditMemoOrderDate").val().split("/")[2]+"-"+$("#CreditMemoOrderDate").val().split("/")[1]+"-"+$("#CreditMemoOrderDate").val().split("/")[0];
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/credit_memos/product/"; ?>" + $("#CreditMemoCompanyId").val()+"/"+$("#CreditMemoLocationId").val()+"/"+$("#CreditMemoBranchId").val()+ "/"+dateOrder,
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
                            $(".ui-dialog-titlebar-close").show();
                        },
                        close: function(){
                            timeSearchAddCM = 1;
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkProduct']:checked").val()){
                                    searchProductByCodeCM($("input[name='chkProduct']:checked").val(), '', 1);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchServiceCM(){
        if($("#CreditMemoCompanyId").val()=="" || $("#CreditMemoBranchId").val()=="" || $("#CreditMemoLocationId").val() == "" || $("#CreditMemoCustomerName").val() == ""){
            timeSearchAddCM = 1;
            alertSelectRequireField();
        }else{
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/credit_memos/service"; ?>/" + $("#CreditMemoCompanyId").val()+"/"+$("#CreditMemoBranchId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
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
                            $(".ui-dialog-titlebar-close").show();
                        },
                        close: function(){
                            timeSearchAddCM = 1;
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var formName = "#ServiceServiceForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    addServiceToListCM();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function resizeFormTitleAddCM(){
        var screen = 16;
        var widthList = $("#bodyListAddCM").width();
        $("#tblCMHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblCMHeader").css('padding','0px');
        $("#tblCMHeader").css('margin-top','5px');
        $("#tblCMHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollAddCM(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#SOTopAddCM').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#SOTopAddCM").height();
        }
        var btnHeader    = $("#btnHideShowHeaderCreditMemo").height();
        var formFooter   = $(".footerFormCreditMemo").height();
        var formSearch   = $("#searchFormCreditMemo").height();
        var tableHeader  = $("#tblSOHeader").height();
        var screenRemain = 268;
        var getHeight    = windowHeight - (formHeader + btnHeader + formFooter + formSearch + tableHeader + screenRemain);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyListAddCM").css('height',getHeight);
        $("#bodyListAddCM").css('padding','0px');
        $("#bodyListAddCM").css('width','100%');
        $("#bodyListAddCM").css('overflow-x','hidden');
        $("#bodyListAddCM").css('overflow-y','scroll');
    }
    
    function refreshScreenAddCM(){
        $("#tblCMHeader").removeAttr('style');
    }

    function searchProductByCodeCM(productCode, uomSelected, qtyOrder){
        if($("#CreditMemoCompanyId").val()=="" || $("#CreditMemoBranchId").val()=="" || $("#CreditMemoLocationId").val() == "" || $("#CreditMemoCustomerId").val() == ""){
            timeSearchAddCM = 1;
            searchCodeCM = 1;
            $("#SearchProductSkuCM").val('');
            alertSelectRequireField();
        }else{
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . "/credit_memos/searchProductByCode/"; ?>" + $("#CreditMemoCompanyId").val()+"/"+$("#CreditMemoBranchId").val()+"/"+$("#CreditMemoCustomerId").val(),
                data:   "data[code]=" + encodeURIComponent(productCode),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#SearchProductSkuCM").val("");
                    if(msg.product_id != ""){
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
                                    searchProductByCodeCM(productCode, uomSelected, qtyOrder);
                                }, time);
                                loop++;
                            });
                        }else{
                            addProductToListCM(uomSelected, qtyOrder, msg);
                        }
                    }else{
                        searchCodeCM = 1;
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TABLE_NO_PRODUCT; ?></p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: '300',
                            height: 'auto',
                            position:'center',
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
                }
            });
        }
    }
    
    function eventKeyCM(){
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
        $(".product, .qty, .unit_price, .qty_uom_id, .total_price, .btnDiscountCM, .btnRemoveDiscount, .btnRemoveCM, .noteAddCM, .qty_free, .btnProductCMInfo").unbind('click').unbind('keyup').unbind('keypress').unbind('change');
        $(".interger").autoNumeric({mDec: 0, aSep: ','});
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#CreditMemoCustomerId").val();
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
        
        $(".btnRemoveCM").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this item?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                position:'center',
                modal: true,
                width: '300',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                    $(".ui-dialog-titlebar-close").show();
                },
                buttons: {
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_OK; ?>': function() {
                        currentTr.remove();
                        getTotalAmountCM();
                        sortNuTableAddCM();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".qty, .qty_free, .unit_price, .total_price").focus(function(){
            if(replaceNum($(this).val()) == "0"){
                $(this).val("");
            }
        });
        
        $(".qty, .qty_free, .unit_price, .total_price").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
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
                        $("#SearchProductSkuCM").focus().select();
                    }
                }else{
                    $(this).select().focus();
                }
                return false;
            }
        });

        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#SearchProductSkuCM").select().focus();
                return false;
            }
        });

        $(".qty, .unit_price").keyup(function(){
            calculateTotalRowCM($(this));
        });
        
        $(".total_price").keyup(function(){
            var discount  = 0;
            var unitPrice = 0;
            var value     = replaceNum($(this).val());
            var qty       = replaceNum($(this).closest("tr").find(".qty").val());
            var discountAmount  = replaceNum($(this).closest("tr").find("input[name='discount_amount[]']").val());
            var discountPercent = replaceNum($(this).closest("tr").find("input[name='discount_percent[]']").val());
            if(qty > 0){
                if(discountAmount != 0 && discountAmount != ''){
                    unitPrice = converDicemalJS(( Number(value) + Number(discountAmount) ) / qty);
                } else if (discountPercent != 0 && discountPercent != ''){
                    unitPrice = converDicemalJS(( converDicemalJS((converDicemalJS(Number(value) * 100)) / (converDicemalJS(100 - discountPercent))) ) / qty);
                    discount = converDicemalJS(( converDicemalJS((converDicemalJS(Number(value) * 100)) / (converDicemalJS(100 - discountPercent))) ) * (converDicemalJS(discountPercent / 100)) );
                    $(this).closest("tr").find(".discount").val(discount.toFixed(<?php echo $priceDecimal; ?>));
                } else {
                    unitPrice = parseFloat(converDicemalJS(value / qty));
                }
                $(this).closest("tr").find(".unit_price").val((unitPrice).toFixed(<?php echo $priceDecimal; ?>));
            }else{
                $(this).val(0);
            }
            calculateTotalRowCM($(this));
        });
        
        $(".qty_uom_id").change(function(){
            var productId     = replaceNum($(this).closest("tr").find("input[name='product_id[]']").val());
            if(productId != "" && productId > 0){
                var priceType = $("#typeOfPriceCM").val();
                var value    = replaceNum($(this).val());
                var smallUom = replaceNum($(this).closest("tr").find("input[name='small_val_uom[]']").val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var conversion    = smallUom / uomConversion;
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price-uom-"+priceType));
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".cm_conversion").val(conversion);
                // Set Name
                var nameDef = $(this).closest("tr").find("input[name='product[]']").attr("data");
                var nameCon = nameDef+"/"+conversion;
                $(this).closest("tr").find("input[name='product[]']").val(nameCon);
            }
            calculateTotalRowCM($(this));
        });
        
        $(".btnDiscountCM").click(function(){
            if(!$(this).closest("tr").find(".is_free").is(":checked")){
                addNewDiscountCM($(this).closest("tr"));
            }
        });
        
        $(".btnRemoveDiscount").click(function(){
            removeDiscountCM($(this).closest("tr"));
        });
        
        $(".noteAddCM").click(function(){
            addNoteCM($(this));
        });
        
        $('.expired_date').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        // Button Show Information
        $(".btnProductCMInfo").click(function(){
            showProductInfoCM($(this));
        });
    }
    
    function checkEventCM(){
        eventKeyCM();
        $(".tblCMList").unbind("click");
        $(".tblCMList").click(function(){
            eventKeyCM();
        });
    }
    
    function removeDiscountCM(tr){
        tr.find("input[name='discount_id[]']").val("");
        tr.find("input[name='discount_amount[]']").val(0);
        tr.find("input[name='discount_percent[]']").val(0);
        tr.find("input[name='discount[]']").val(0);
        tr.find(".btnRemoveDiscount").css("display", "none");
        calculateTotalRowCM(tr);
    }

    function addServiceToListCM(){
        indexRowCM = Math.floor((Math.random() * 100000) + 1);
        var tr = tblRowAddCM.clone(true);
        // Service Information
        var serviceId    = $("#ServiceServiceId").val();
        var serviceCode  = $("#ServiceServiceId").find("option:selected").attr('scode');
        var serviceName  = $("#ServiceServiceId").find("option:selected").attr('abbr');
        var servicePrice = $("#ServiceUnitPrice").val();
        var serviceUomId = $("#ServiceServiceId").find("option:selected").attr('suom');
        var serviceType  = $("#ServiceServiceId").find("option:selected").attr('type');
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowCM);
        tr.find(".lblUPC").val(serviceCode);
        tr.find("input[name='product_id[]']").attr("id", "product_id_"+indexRowCM);
        tr.find("input[name='service_id[]']").attr("id", "service_id_"+indexRowCM).val(serviceId);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowCM).val(serviceName).attr("readonly", false);
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowCM).val(1);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowCM).val(servicePrice);
        tr.find("input[name='note[]']").attr("id", "note_"+indexRowCM);
        tr.find("input[name='h_total_price[]']").attr("id", "h_total_price_"+indexRowCM).val(servicePrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowCM).val(servicePrice);
        tr.find("input[name='discount_id[]']").attr("id", "discount_id_"+indexRowCM);
        tr.find("input[name='discount_amount[]']").attr("id", "discount_amount_"+indexRowCM);
        tr.find("input[name='discount_percent[]']").attr("id", "discount_percent_"+indexRowCM);
        tr.find("input[name='discount[]']").attr("id", "discount_"+indexRowCM).val("0.00");
        tr.find("input[name='lots_number[]']").attr("id", "lots_number_"+indexRowCM).hide();
        tr.find("input[name='expired_date[]']").attr("id", "expired_date_"+indexRowCM).hide();
        tr.find("input[name='expired_date[]']").removeAttr("class");
        tr.find("input[name='type[]']").attr("id", "typeCM_"+indexRowCM).val(serviceType);
        tr.find(".btnProductCMInfo").hide();
        if(serviceUomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowCM).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowCM).find("option[value='"+serviceUomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+serviceUomId+"']").hide();
        }
        if(serviceType == 2){
            tr.find(".divStartCM").show();
            tr.find(".startCM").attr("id", "startCM_"+indexRowCM).addClass("validate[required]");
        }
        $("#tblCM").append(tr);
        if(serviceType == 2){
            $('#startCM_'+indexRowCM).datepicker({
                dateFormat:'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                onSelect: function() {
                    $("#CreditMemoAddForm").validationEngine("hideAll");
                }
            }).unbind("blur");
            var minDate = $("#CreditMemoOrderDate").val();
            $('#startCM_'+indexRowCM).datepicker("option", "minDate", minDate);
        }
        sortNuTableAddCM();
        checkEventCM();
        getTotalAmountCM();
        $("#tblCM").find("tr:last").find(".qty").select().focus();
    }

    function addProductToListCM(uomSelected, qtyOrder, msg){
        indexRowCM = Math.floor((Math.random() * 100000) + 1);
        // Product Information
        var productId    = msg.product_id;
        var productSku   = msg.product_code;
        var productUpc   = msg.product_barcode;
        var productName  = msg.product_name;
        var productCusName = msg.product_cus_name;
        var productUomId   = msg.product_uom_id;
        var smallValUom  = msg.small_uom_val;
        var productExp   = msg.is_expired_date;
        var productLots  = msg.is_lots;
        var productInfo  = showOriginalNameCM(productUpc, productSku, productName);
        var tr           = tblRowAddCM.clone(true);
        var branchId     = $("#CreditMemoBranchId").val();
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowCM);
        tr.find(".lblUPC").val(productUpc);
        tr.find(".orgProName").val(productInfo);
        tr.find("input[name='product_id[]']").val(productId);
        tr.find("input[name='product[]']").attr("data", productCusName);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowCM).val(productCusName+"/"+smallValUom).removeAttr('readonly');
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowCM).val(qtyOrder).attr('readonly', true);
        tr.find("input[name='small_val_uom[]']").val(smallValUom);
        tr.find("input[name='cm_conversion[]']").val(smallValUom);
        tr.find("input[name='discount[]']").attr("id", "discount_"+indexRowCM).val("0.00");
        tr.find("input[name='note[]']").attr("id", "note_"+indexRowCM);
        tr.find("input[name='lots_number[]']").attr("id", "lots_number_"+indexRowCM);
        tr.find("input[name='expired_date[]']").attr("id", "expired_date_"+indexRowCM);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowCM);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowCM).html('<option value="">Please Select Uom</option>');
        if(productExp == 1){
            tr.find("input[name='expired_date[]']").addClass("validate[required]").val('');
        }else{
            tr.find("input[name='expired_date[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0000-00-00');
        }
        if(productLots == 1){
            tr.find("input[name='lots_number[]']").addClass("validate[required]").val('');
        }else{
            tr.find("input[name='lots_number[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0');
        }
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/uoms/getRelativeUom/"; ?>"+productUomId+"/all/"+productId+"/"+branchId,
            success: function(msg){
                var complete = false;
                tr.find("select[name='qty_uom_id[]']").html(msg).val(1);
                tr.find("select[name='qty_uom_id[]']").find("option").each(function(){
                    if($(this).attr("conversion") == 1){
                        if($(this).attr("conversion") == 1 && uomSelected == ''){
                            $(this).attr("selected", true);
                            // Price
                            var priceType     = $("#typeOfPriceCM").val();
                            var lblPrice      = "price-uom-"+priceType;
                            var productPrice  = parseFloat($(this).attr(lblPrice));
                            complete = true;
                        } else{
                            if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                                $(this).attr("selected", true);
                                // Price
                                var priceType     = $("#typeOfPriceCM").val();
                                var lblPrice      = "price-uom-"+priceType;
                                var productPrice  = parseFloat($(this).attr(lblPrice));
                                complete = true;
                            }
                        }
                        if(complete == true){
                            var totalPrice = converDicemalJS(productPrice * parseFloat(qtyOrder));
                            tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowCM).val(productPrice);
                            tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowCM).val(totalPrice);
                            tr.find("input[name='h_total_price[]']").val(totalPrice);
                            checkEventCM();
                            getTotalAmountCM();
                            tr.find("input[name='qty[]']").removeAttr('readonly');
                            tr.find("input[name='qty[]']").select().focus();
                        }
                        return false;
                    }
                });
            }
        });
        $("#tblCM").append(tr);
        sortNuTableAddCM();
        $("#loadProduct").html('');
        searchCodeCM = 1;
    }

    function checkExistingRecordAddCM(productId){
        var isFound = false;
        $("#tblCM").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function calculateTotalRowCM(obj){
        var tr  = obj.closest("tr");
        var qty = replaceNum(tr.find(".qty").val());
        var unitPrice       = replaceNum(tr.find(".unit_price").val());
        var discountAmount  = replaceNum(tr.find("input[name='discount_amount[]']").val());
        var discountPercent = replaceNum(tr.find("input[name='discount_percent[]']").val());
        var discount        = 0;
        var totalSubPrice   = converDicemalJS(qty * unitPrice);
        if(discountPercent != 0 && discountPercent != ''){
            discount = converDicemalJS((totalSubPrice * discountPercent) / 100);
        }else{
            discount = discountAmount;
        }
        var totalPrice = converDicemalJS(totalSubPrice - discount);
        tr.find(".discount").val((discount).toFixed(<?php echo $priceDecimal; ?>));
        tr.find(".h_total_price").val(totalSubPrice);
        // Set Unit Price If Action Not Unit Price
        if(obj.attr("class") != 'float unit_price'){
            tr.find(".unit_price").val((unitPrice).toFixed(<?php echo $priceDecimal; ?>));
        }
        // Set Total Price If Action Not Total Price
        if(obj.attr("class") != 'float total_price'){
            tr.find(".total_price").val((totalPrice).toFixed(<?php echo $priceDecimal; ?>));
        }
        getTotalAmountCM();
    }
    
    function addNewDiscountCM(tr){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/credit_memos/invoiceDiscount"; ?>",
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
                            tr.find("input[name='discount_id[]']").val(0);
                            tr.find("input[name='discount_amount[]']").val(totalDisAmt);
                            tr.find("input[name='discount_percent[]']").val(totalDisPercent);
                            tr.find("input[name='discount[]']").css("display", "inline");
                            tr.find(".btnRemoveDiscount").css("display", "inline");
                            if(parseFloat(discountTr.find("input[name='salesOrderDiscountPercent']").val()) > 0){
                                var qty        = replaceNum(tr.find(".qty").val());
                                var unitPrice  = replaceNum(tr.find(".unit_price").val());
                                var totalPrice = converDicemalJS(qty * unitPrice);
                                var discount   = converDicemalJS((totalPrice * replaceNum(discountTr.find("input[name='salesOrderDiscountPercent']").val())) / 100);
                                tr.find("input[name='discount[]']").val(discount);
                            }else{
                                tr.find("input[name='discount[]']").val(discountTr.find("input[name='salesOrderDiscountAmount']").val());
                            }
                            calculateTotalRowCM(tr.find("input[name='discount[]']"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function getTotalAmountCM(){
        var totalAmount      = 0;
        var totalVat         = 0;
        var totalDiscountAll = replaceNum($("#CreditMemoDiscount").val());
        var totalDiscountPer = replaceNum($("#CreditMemoDiscountPercent").val());
        var totalMarupAll    = replaceNum($("#CreditMemoMarkUp").val());
        var totalVatPercent  = replaceNum($("#CreditMemoVatPercent").val());
        var total            = 0;
        var vatCal           = $("#CreditMemoVatCalculate").val();
        var totalBfDis       = 0;
        var totalAmtCalVat   = 0;
        $(".tblCMList").find(".total_price").each(function(){
            if($.trim($(this).val()) != '' || $(this).val() != undefined ){
                totalAmount += parseFloat(replaceNum($(this).val()));
            }
        });
        
        if(isNaN(totalAmount)){
            $("#CreditMemoDiscount").val(0);
            $("#CreditMemoMarkUp").val(0);
            $("#CreditMemoTotalVat").val(0);
            $("#CreditMemoTotalAmount").val(0);
            $("#CreditMemoSubTotalAmount").val(0);
        }else{
            if(totalDiscountPer > 0){
                totalDiscountAll  = replaceNum(converDicemalJS((totalAmount * totalDiscountPer) / 100).toFixed(<?php echo $priceDecimal; ?>));
            }
            totalAmtCalVat = replaceNum(converDicemalJS(totalAmount - totalDiscountAll + totalMarupAll));
            // Check VAT Calculate Before Discount, Free, Mark Up
            if(vatCal == 1){
                $(".tblCMList").each(function(){
                    var qty   = replaceNum($(this).find(".qty").val()) + replaceNum($(this).find(".qty_free").val());
                    var price = replaceNum($(this).find(".unit_price").val());
                    totalBfDis += replaceNum(converDicemalJS(qty * price));
                });
                totalAmtCalVat = totalBfDis;
            }
            totalVat = replaceNum(converDicemalJS(converDicemalJS(totalAmtCalVat * totalVatPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
            total    = replaceNum(converDicemalJS(converDicemalJS(totalAmount - totalDiscountAll + totalMarupAll) + totalVat));
            $("#CreditMemoTotalAmount").val((totalAmount).toFixed(<?php echo $priceDecimal; ?>));
            $("#CreditMemoDiscount").val((totalDiscountAll).toFixed(<?php echo $priceDecimal; ?>));
            $("#CreditMemoTotalVat").val((totalVat).toFixed(<?php echo $priceDecimal; ?>));
            $("#CreditMemoSubTotalAmount").val((total).toFixed(<?php echo $priceDecimal; ?>));        
        }
    }
    
    function sortNuTableAddCM(){
        var sort = 1;
        $(".tblCMList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function addNoteCM(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteCommentCM'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_MEMO; ?>',
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
                    note.val($("#noteCommentCM").val());
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function showOriginalNameCM(puc, sku, name){
        var orgName = '';
        orgName += 'PUC: '+puc;
        orgName += '<br/><br/>SKU: '+puc;
        orgName += '<br/><br/>Name: '+name;
        return orgName;
    }
    
    function showProductInfoCM(currentTr){
        var info = currentTr.closest("tr").find(".orgProName");
        $("#dialog").html("<div style='width:350px; height: 140px; font-size: 16px;'>"+info.val()+"</div>").dialog({
            title: '<?php echo MENU_PRODUCT_NAME_MANAGEMENT_INFO; ?>',
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
    }
</script>
<div class="inputContainer" style="width:100%;" id="searchFormCreditMemo">
    <table style="width: 100%;">
        <tr>
            <td style="width: 410px;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="SearchProductSkuCM" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addNewProductCM" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="SearchProductSkuCM" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td style="width: 20%; text-align: left;" id="divSearchCreditMemo">
                <img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer;"class="searchProductListCM" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" /> 
                <?php
                if ($allowAddService) {
                ?>
                <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServiceCM" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" /> 
                <?php
                }
                ?>
            </td>
            <td style="text-align:right">
                <div style="width:100%; float: right;">
                    <label for="typeOfPriceCM"><?php echo TABLE_PRICE_TYPE; ?> :</label> &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceCM" name="data[CreditMemo][price_type_id]" style="height: 30px; width: 50%">
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
<table id="tblCMHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 99%">
    <tr>
        <th class="first" style="width:4%"><?php echo TABLE_NO; ?></th>
        <th style="width:10%"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:18%"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:9%"><?php echo TABLE_UOM; ?></th>
        <th style="width:7%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:6%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:6%"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:9%"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
        <th style="width:8%"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:9%"><?php echo TABLE_TOTAL_PRICE_SHORT; ?></th>
        <th style="width:4%"></th>
    </tr>
</table>
<div id="bodyListAddCM">
    <table id="tblCM" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListAddCM" class="tblCMList" style="visibility: hidden;" >
            <td class="first" style="width:4%; padding: 0px; height: 30px;"></td>
            <td style="width:10%; text-align: left; padding: 5px;">
                <input type="text" readonly="" style="width: 95%; height: 25px;" class="lblUPC" />
            </td>
            <td style="width:18%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" />
                    <input type="hidden" name="service_id[]" />
                    <input type="hidden" name="discount_id[]" />
                    <input type="hidden" name="discount_amount[]" value="0" />
                    <input type="hidden" name="discount_percent[]" value="0" />
                    <input type="hidden" name="cm_conversion[]" class="cm_conversion" value="1" />
                    <input type="hidden" name="small_val_uom[]" class="small_val_uom" value="1" />
                    <input type="hidden" name="note[]" id="note" readonly="readonly" class="note" />
                    <input type="hidden" name="type[]" class="typeCM" />
                    <input type="hidden" value="0" class="isReferenceCM" />
                    <input type="hidden" class="orgProName" />
                    <input type="text" id="product" data="" readonly="readonly" name="product[]" class="product validate[required]" style="width: 75%; height: 25px;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductCMInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
                <div class="inputContainer divStartCM" style="width:100%; margin-top: 3px; display: none;">
                    <input type="text" name="start[]" id="startCM" class="startCM" readonly="readonly" style="width: 75%; height: 25px;" placeholder="<?php echo TABLE_START_DATE; ?>" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id" style="width:80%; height: 25px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php 
                        foreach($uoms as $uom){
                        ?>
                        <option conversion="1" value="<?php echo $uom['Uom']['id']; ?>"><?php echo $uom['Uom']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
            <td style="width:7%; text-align: center; padding: 0px; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="lots_number" name="lots_number[]" style="width:90%; height: 25px;" class="lots_number" />
                </div>
            </td>
            <td style="width:10%; text-align: center; padding: 0px; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="expired_date" name="expired_date[]" style="width:90%; height: 25px;" class="expired_date" readonly="readonly" />
                </div>
            </td>
            <td style="width:6%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:70%; height: 25px;" class="qty interger" />
                </div>
            </td>
            <td style="width:6%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" value="0" style="width:70%; height: 25px;" class="qty_free interger" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="text" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> id="unit_price" name="unit_price[]" value="0" style="width:70%; height: 25px;" class="float unit_price" />
                </div>
            </td>
            <td style="width:8%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%;">
                    <?php
                    if ($allowProductDiscount) {
                    ?>
                        <input type="text" class="discount btnDiscountCM float" name="discount[]" style="width: 70%; height: 25px;" readonly="readonly" />
                        <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscount" align="absmiddle" style="cursor: pointer; display: none" onmouseover="Tip('Remove')" />
                        <?php
                    }else{
                    ?>
                        <input type="hidden" class="discount btnDiscountCM float" name="discount[]" />
                    <?php
                    }
                    ?>
                </div>
            </td>
            <td style="width:9%; text-align: center; padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" id="h_total_price" class="h_total_price float" name="h_total_price[]" />
                    <input type="text" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> name="total_price[]" value="0" style="width:85%; height: 25px;" class="float total_price" />
                </div>
            </td>
            <td style="width:4%">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveCM" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
    </table>
</div>