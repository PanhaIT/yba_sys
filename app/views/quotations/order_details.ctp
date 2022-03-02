<?php
$priceDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}

// Authentication
$this->element('check_access');
$allowaddService = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowEditPrice = checkAccess($user['User']['id'], $this->params['controller'], 'editPrice');
$allowDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
?>
<script type="text/javascript">
    var tblRowQuotation  = $("#OrderListQuotation");
    var indexRowQuotation   = 0;
    var searchCodeQuotation = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OrderListQuotation").remove();
        var waitForFinalEventQuotation = (function () {
          var timersQuotation = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timersQuotation[uniqueId]) {
              clearTimeout (timersQuotation[uniqueId]);
            }
            timersQuotation[uniqueId] = setTimeout(callback, ms);
          };
        })();
        
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabQuotReg != tabQuoteId){
            $("a[href='"+tabQuoteId+"']").click(function(){
                if($(".orderDetailQuotation").html() != '' && $(".orderDetailQuotation").html() != null){
                    waitForFinalEventQuotation(function(){
                        refreshScreenQuotation();
                        resizeFormTitleQuotation();
                        resizeFornScrollQuotation();
                    }, 500, "Finish");
                }
            });
            tabQuotReg = tabQuoteId;
        }
        
        waitForFinalEventQuotation(function(){
                refreshScreenQuotation();
                resizeFormTitleQuotation();
                resizeFornScrollQuotation();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabQuotReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventQuotation(function(){
                    refreshScreenQuotation();
                    resizeFormTitleQuotation();
                    resizeFornScrollQuotation();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderQuotation").click(function(){
            var customerId = $("#QuotationCustomerId").val();
            var companyId  = $("#QuotationCompanyId").val();
            var branchId   = $("#QuotationBranchId").val();
            var OrderDate  = $("#QuotationQuotationDate").val();
            
            if(customerId == "" || companyId == "" || OrderDate == "" || branchId == ""){
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
                    $("#quoteHeaderForm").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#quoteHeaderForm").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabQuotReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventQuotation(function(){
                        resizeFornScrollQuotation();
                    }, 300, "Finish");
                }
            }
        });
        
        $("#SearchProductSkuQuotation").autocomplete("<?php echo $this->base . "/quotations/searchProduct/"; ?>", {
            width: 400,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $("#SearchProductSkuQuotation").val(code);
            if(searchCodeQuotation == 1){
                searchCodeQuotation = 2;
                searchProductByCodeQuotation(code, '', 1);
            }
        });
        
        $("#SearchProductSkuQuotation").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchCodeQuotation == 1){
                    searchCodeQuotation = 2;
                    searchProductByCodeQuotation($(this).val(), '', 1);
                }
                return false;
            }
        });
        
        $("#SearchProductPucQuotation").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchCodeQuotation == 1){
                    searchCodeQuotation = 2;
                    searchProductByCodeQuotation($(this).val(), '', 1);
                }
                return false;
            }
        });
        // Search Product
        $(".searchProductListQuotation").click(function(){
            if(searchCodeQuotation == 1){
                searchCodeQuotation = 2;
                searchProductListQuotation();
            }
        });
        // Search Service
        $(".addServiceQuotation").click(function(){
            if(searchCodeQuotation == 1){
                searchCodeQuotation = 2;
                searchAllServiceQuotation();
            }
        });
        // Search Misc
        $(".addMiscellaneousQuotation").click(function(){
            if(searchCodeQuotation == 1){
                searchCodeQuotation = 2;
                searchAllMiscQuotation();
            }
        });
        
        // Change Price Type
        $("#typeOfPriceQuotation").change(function(){
            if($(".tblQuotationList").find(".product_id").val() == undefined){
                changePriceTypeQuotation();
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
                            changePriceTypeQuotation();
                            $.cookie("typePriceQuotation", $("#typeOfPriceQuotation").find("option:selected").val(), {expires : 7, path    : '/'});
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#typeOfPriceQuotation").val($.cookie('typePriceQuotation'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        <?php
        if($allowAddProduct){
        ?>
        $("#addProductQuotation").click(function(){
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
                
        $("#QuotationTotalDeposit").unbind("focus").focus(function(){
            if(replaceNum($(this).val()) == "0"){
                $(this).val("");
            }
        });
        
        $("#QuotationTotalDeposit").unbind("blur").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }else if(replaceNum($(this).val()) > replaceNum($("#QuotationTotalAmountSummary").val())){
                $(this).val($("#QuotationTotalAmountSummary").val());
            }
        });
        
        // Change Input CSS
        changeInputCSSQuotation();
        
    }); // End Document Ready
    
    function changePriceTypeQuotation(){
        if($(".product").val() != undefined && $(".product").val() != ''){
            var priceType  = parseFloat(replaceNum($("#typeOfPriceQuotation").find("option:selected").val()));
            $(".tblQuotationList").each(function(){
                if($(this).find("input[name='product_id[]']").val() != ''){
                    var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                    var unitCost  = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("cost-uom-"+priceType));
                    $(this).find(".unit_cost").val(converDicemalJS(unitCost).toFixed(<?php echo $priceDecimal; ?>));
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(<?php echo $priceDecimal; ?>));
                    calculateTotalRowQu($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchProductListQuotation(){
        searchCodeQuotation = 1;
        if($("#QuotationCustomerName").val() == "" || $("#QuotationCompanyId").val() == "" || $("#QuotationBranchId").val() == ""){
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
            var dateOrder = $("#QuotationQuotationDate").val().split("/")[2]+"-"+$("#QuotationQuotationDate").val().split("/")[1]+"-"+$("#QuotationQuotationDate").val().split("/")[0];
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/quotations/product/"; ?>"+$("#QuotationCompanyId").val()+"/"+$("#QuotationBranchId").val(),
                data:   "order_date="+dateOrder,
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
                                if($("input[name='chkProduct']:checked").val()){
                                    searchCodeQuotation = 2;
                                    searchProductByCodeQuotation($("input[name='chkProduct']:checked").val(), '', 1);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllServiceQuotation(){
        searchCodeQuotation = 1;
        if($("#QuotationCompanyId").val() == "" || $("#QuotationBranchId").val() == "" || $("#QuotationCustomerName").val() == ""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
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
                url:    "<?php echo $this->base . "/quotations/service"; ?>/"+$("#QuotationCompanyId").val()+"/"+$("#QuotationBranchId").val(),
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
                                    searchCodeQuotation = 2;
                                    addNewServiceQuotation();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllMiscQuotation(){
        searchCodeQuotation = 1;
        if($("#QuotationCompanyId").val() == "" || $("#QuotationBranchId").val() == "" || $("#QuotationCustomerName").val() == ""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
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
                url:    "<?php echo $this->base . "/quotations/miscellaneous"; ?>",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
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
                                    searchCodeQuotation = 2;
                                    addNewMiscQuotation();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function resizeFormTitleQuotation(){
        var screen = 16;
        var widthList = $("#bodyListQuotation").width();
        $("#tblQuotationHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblQuotationHeader").css('padding','0px');
        $("#tblQuotationHeader").css('margin-top','5px');
        $("#tblQuotationHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollQuotation(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#quoteHeaderForm').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#quoteHeaderForm").height();
        }
        var btnHeader    = $("#btnHideShowHeaderQuotation").height();
        var formFooter   = $(".footerSaveQuotation").height();
        var formSearch   = $(".quoteSearchForm").height();
        var tableHeader  = $("#tblQuotationHeader").height();
        var screenRemain = 265;
        var getHeight    = windowHeight - (formHeader + btnHeader + formFooter + formSearch + tableHeader + screenRemain);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyListQuotation").css('height',getHeight);
        $("#bodyListQuotation").css('padding','0px');
        $("#bodyListQuotation").css('width','100%');
        $("#bodyListQuotation").css('overflow-x','hidden');
        $("#bodyListQuotation").css('overflow-y','scroll');
    }
    
    function refreshScreenQuotation(){
        $("#tblQuotationHeader").removeAttr('style');
    }

    function searchProductByCodeQuotation(productCode, uomSelected, qtyOrder){
        if($("#QuotationCustomerId").val() == "" || $("#QuotationCompanyId").val() == "" || $("#QuotationBranchId").val() == ""){
            timeSearchQuotation = 1;
            searchCodeQuotation = 1;
            $("#SearchProductPucQuotation").val("");
            $("#SearchProductSkuQuotation").val('');
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
        }else{
            $("#QuotationQuotationDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var orderDate = $("#QuotationQuotationDate").val();
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . "/quotations/searchProductByCode/"; ?>"+$("#QuotationCompanyId").val()+"/"+$("#QuotationCustomerId").val()+"/"+$("#QuotationBranchId").val(),
                data:   "data[code]=" + productCode+"&order_date="+orderDate,
                beforeSend: function(){
                    $("#QuotationQuotationDate").datepicker("option", "dateFormat", "dd/mm/yy");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#SearchProductPucQuotation").val("");
                    $("#SearchProductSkuQuotation").val("");
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
                                    searchProductByCodeQuotation(productCode, uomSelected, qtyOrder, msg);
                                }, time);
                                loop++;
                            });
                        }else{
                            addProductToListQuotation(uomSelected, qtyOrder, msg);
                        }
                    }else{
                        searchCodeQuotation = 1;
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TABLE_NO_PRODUCT; ?></p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: '500',
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
            });
        }
    }
    
    function eventKeyQuotation(){
        loadAutoCompleteOff();
        $(".product, .qty, .qty_free, .unit_price, .qty_uom_id, .total_price, .discount, .btnRemoveDiscountQu, .btnRemoveQuotationList, .btnProductInfo").unbind('click').unbind('keyup').unbind('keypress').unbind('change');
        $(".interger").autoNumeric({mDec: 0, aSep: ','});
        $(".float").autoNumeric({mDec: <?php echo $priceDecimal; ?>, aSep: ','});
        
        $(".btnRemoveQuotationList").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this item?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                position:'center',
                modal: true,
                width: 'auto',
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
                        getTotalAmountQuotation();
                        sortNuTableQuotation();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".qty,.qty_free, .unit_price, .total_price").focus(function(){
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
                        $("#SearchProductSkuQuotation").focus().select();
                    }
                }else{
                    $(this).select().focus();
                }
                return false;
            }
        });

        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#SearchProductSkuQuotation").select().focus();
                return false;
            }
        });

        $(".qty,.qty_free, .unit_price").keyup(function(){
            calculateTotalRowQu($(this));
        });
        
        $(".total_price").keyup(function(){
            var unitPrice       = 0;
            var discount        = 0;
            var totalPrice      = replaceNum($(this).val());
            var totalBfDis      = 0;
            var qty             = replaceNum($(this).closest("tr").find(".qty").val());
            var qty_free        = replaceNum($(this).closest("tr").find(".qty_free").val());
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
                totalBfDis = converDicemalJS(unitPrice * qty);
                $(this).closest("tr").find(".discount").val(discount.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".unit_price").val((unitPrice).toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".total_price_bf_dis").val((totalBfDis).toFixed(<?php echo $priceDecimal; ?>));
            }else{
                $(this).val(0);
            }
            calculateTotalRowQu($(this));
        });
        
        $(".qty_uom_id").change(function(){
            if($(this).closest("tr").find(".product_id").val() != '' && replaceNum($(this).closest("tr").find(".product_id").val()) > 0){
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmVal      = replaceNum($(this).find("option[data-sm='1']").attr('conversion'));
                var conversion    = converDicemalJS(uomSmVal / uomConversion);
                var priceType     = replaceNum($("#typeOfPriceQuotation").find("option:selected").val());
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price-uom-"+priceType));
                var unitCost      = replaceNum($(this).find("option:selected").attr("cost-uom-"+priceType));
                $(this).closest("tr").find(".unit_cost").val(unitCost.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(<?php echo $priceDecimal; ?>));
                $(this).closest("tr").find(".conversion").val(conversion);
                // Set Name
                var nameDef = $(this).closest("tr").find("input[name='product[]']").attr("data");
                var nameCon = nameDef+"/"+conversion;
                $(this).closest("tr").find("input[name='product[]']").val(nameCon);
            }
            calculateTotalRowQu($(this));
        });
        
        // Action Discount
        $(".discount").click(function(){
            <?php
            if($allowDiscount){
            ?>
            getItemDiscountQu($(this));
            <?php
            }
            ?>
        });
        
        $(".btnRemoveDiscountQu").click(function(){
            removeDiscountQu($(this).closest("tr"));
        });
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#QuotationCustomerId").val();
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
        
        // Button Show Information
        $(".btnProductInfo").click(function(){
            showProductInfoQuotation($(this));
        });
        
        moveRowQuotation();
    }
    
    function moveRowQuotation(){
        $(".btnMoveDownQuotation, .btnMoveUpQuotation").unbind('click');
        $(".btnMoveDownQuotation").click(function () {
            var rowToMove = $(this).parents('tr.tblQuotationList:first');
            var next = rowToMove.next('tr.tblQuotationList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableQuotation();
        });

        $(".btnMoveUpQuotation").click(function () {
            var rowToMove = $(this).parents('tr.tblQuotationList:first');
            var prev = rowToMove.prev('tr.tblQuotationList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableQuotation();
        });
        
        sortNuTableQuotation();
    }
    
    function getItemDiscountQu(obj){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/quotations/invoiceDiscount"; ?>",
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
                            var totalDisAmt     = replaceNum($("#inputQuotationDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputQuotationDisPer").val());
                            if(obj.closest("tr").find("input[name='unit_price[]']").val() > 0){
                                obj.closest("tr").find("input[name='discount_id[]']").val(0);
                                obj.closest("tr").find("input[name='discount_amount[]']").val(totalDisAmt);
                                obj.closest("tr").find("input[name='discount_percent[]']").val(totalDisPercent);
                                obj.closest("tr").find("input[name='discount[]']").css("display", "inline");
                                obj.closest("tr").find(".btnRemoveDiscountQu").css("display", "inline");
                                calculateTotalRowQu(obj);
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function removeDiscountQu(tr){
        tr.find("input[name='discount_id[]']").val("");
        tr.find("input[name='discount_amount[]']").val(0);
        tr.find("input[name='discount_percent[]']").val(0);
        tr.find("input[name='discount[]']").val(0);
        tr.find(".btnRemoveDiscountQu").css("display", "none");
        calculateTotalRowQu(tr);
    }

    function addProductToListQuotation(uomSelected, qtyOrder, msg){
        indexRowQuotation = Math.floor((Math.random() * 100000) + 1);
        // Product Information
        var productId    = msg.product_id;
        var productPUC   = msg.product_barcode;
        var productSku   = msg.product_code;
        var productName  = msg.product_name;
        var proCusName   = msg.product_cus_name;
        var productUomId = msg.product_uom_id;
        var smallUomVal  = msg.small_uom_val;
        var tr           = tblRowQuotation.clone(true);
        var productInfo  = showOriginalNameQuotation(productPUC, productSku, productName);
        var branchId     = $("#QuotationBranchId").val();
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowQuotation);
        tr.find(".lblSKU").html(productSku);
        tr.find("input[name='product_id[]']").val(productId);
        tr.find(".orgProName").val(productInfo);
        tr.find("input[name='product[]']").attr("data", proCusName);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowQuotation).val(proCusName+"/"+smallUomVal).removeAttr('readonly');
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowQuotation).val(qtyOrder).attr('readonly', true);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowQuotation).val(qtyOrder).attr('readonly', true);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowQuotation).html('<option value="">Please Select Uom</option>');
        tr.find(".conversion").attr("id", "conversion_"+indexRowQuotation).val(smallUomVal);
        tr.find(".discount").attr("id", "discount_"+indexRowQuotation).val(0);
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/uoms/getRelativeUom/"; ?>"+productUomId+"/all/"+productId+"/"+branchId,
            success: function(msg){
                var complete = false;
                var productPrice = 0;
                var unitCost = 0;
                tr.find("select[name='qty_uom_id[]']").html(msg).val(1);
                tr.find("select[name='qty_uom_id[]']").find("option").each(function(){
                    if($(this).attr("conversion") == 1){
                        if($(this).attr("conversion") == 1 && uomSelected == ''){
                            $(this).attr("selected", true);
                            // Price
                            var priceType = $("#typeOfPriceQuotation").find("option:selected").val();
                            var lblPrice  = "price-uom-"+priceType;
                            productPrice  = parseFloat($(this).attr(lblPrice));
                            var lblCost   = "cost-uom-"+priceType;
                            unitCost      = parseFloat($(this).attr(lblCost));
                            complete = true;
                        } else{
                            if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                                $(this).attr("selected", true);
                                // Price
                                var priceType = $("#typeOfPriceQuotation").find("option:selected").val();
                                var lblPrice  = "price-uom-"+priceType;
                                productPrice  = parseFloat($(this).attr(lblPrice));
                                var lblCost   = "cost-uom-"+priceType;
                                unitCost      = parseFloat($(this).attr(lblCost));
                                complete = true;
                            }
                        }
                        if(complete == true){
                            var totalPrice = converDicemalJS(productPrice * parseFloat(qtyOrder));
                            tr.find("input[name='unit_cost[]']").val(unitCost);
                            tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowQuotation).val(productPrice);
                            tr.find("input[name='total_price_bf_dis[]']").val(totalPrice);
                            tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowQuotation).val(totalPrice);
                            checkEventQuotation();
                            getTotalAmountQuotation();
                            if(unitCost > productPrice){
                                tr.find(".priceDownQuotation").show();
                                tr.find(".unit_price").css("color", "red");
                            } else {
                                tr.find(".priceDownQuotation").hide();
                                tr.find(".unit_price").css("color", "#000");
                            }
                            tr.find("input[name='qty[]']").removeAttr('readonly');
                            tr.find("input[name='qty[]']").select().focus();
                            tr.find("input[name='qty_free[]']").removeAttr('readonly');
                            tr.find("input[name='qty_free[]']").select().focus();
                        }
                        return false;
                    }
                });
            }
        });
        $("#tblQuotation").append(tr);
        sortNuTableQuotation();
        searchCodeQuotation = 1;
    }
    
    function addNewServiceQuotation(){
        indexRowQuotation = Math.floor((Math.random() * 100000) + 1);
        // Service Information
        var serviceId    = $("#ServiceServiceId").val();
        var serviceCode  = $("#ServiceServiceId").find("option:selected").attr('scode');
        var serviceName  = $("#ServiceServiceId").find("option:selected").attr('abbr');
        var servicePrice = $("#ServiceUnitPrice").val();
        var serviceUomId = $("#ServiceServiceId").find("option:selected").attr('suom');
        var tr           = tblRowQuotation.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowQuotation);
        tr.find(".lblSKU").html(serviceCode);
        tr.find("input[name='product_id[]']").val('');
        tr.find("input[name='service_id[]']").val(serviceId);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowQuotation).val(serviceName);
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowQuotation).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowQuotation).val(1);
        tr.find(".conversion").attr("id", "conversion_"+indexRowQuotation).val(1);
        tr.find(".discount").attr("id", "discount_"+indexRowQuotation).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowQuotation).val(servicePrice);
        tr.find("input[name='total_price_bf_dis[]']").val(servicePrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowQuotation).val(servicePrice);
        tr.find(".btnProductInfo").hide();
        if(serviceUomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowQuotation).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowQuotation).find("option[value='"+serviceUomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+serviceUomId+"']").hide();
        }
        $("#tblQuotation").append(tr);
        $("#tblQuotation").find("tr:last").find(".qty").select().focus();
        $("#tblQuotation").find("tr:last").find(".qty_free").select().focus();
        sortNuTableQuotation();
        checkEventQuotation();
        getTotalAmountQuotation();
        searchCodeQuotation = 1;
    }
    
    function addNewMiscQuotation(){
        indexRowQuotation = Math.floor((Math.random() * 100000) + 1);
        // Service Information
        var miscName  = $("#MiscellaneousDescription").val();
        var miscPrice = $("#MiscellaneousUnitPrice").val();
        var tr        = tblRowQuotation.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowQuotation);
        tr.find(".lblSKU").html('');
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowQuotation).val(miscName);
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowQuotation).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowQuotation).val(1);
        tr.find(".conversion").attr("id", "conversion_"+indexRowQuotation).val(1);
        tr.find(".discount").attr("id", "discount_"+indexRowQuotation).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowQuotation).val(miscPrice);
        tr.find("input[name='total_price_bf_dis[]']").val(miscPrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowQuotation).val(miscPrice);
        tr.find(".btnProductInfo").hide();
        $("#tblQuotation").append(tr);
        $("#tblQuotation").find("tr:last").find(".qty").select().focus();
        $("#tblQuotation").find("tr:last").find(".qty_free").select().focus();
        sortNuTableQuotation();
        checkEventQuotation();
        getTotalAmountQuotation();
        searchCodeQuotation = 1;
    }

    function checkExistingRecordQuotation(productId){
        var isFound = false;
        $("#tblQuotation").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function calculateTotalRowQu(obj){
        var tr  = obj.closest("tr");
        var qty = replaceNum(tr.find(".qty").val());
        var unitCost   = replaceNum(tr.find(".unit_cost").val());
        var unitPrice  = replaceNum(tr.find(".unit_price").val());
        var discount   = 0;
        var disAmt     = replaceNum(tr.find("input[name='discount_amount[]']").val());
        var disPercent = replaceNum(tr.find("input[name='discount_percent[]']").val());
        var totalPrice = converDicemalJS(qty * unitPrice);
        var total      = 0;
        // Set Unit Price If Action Not Unit Price
        if(obj.attr("class") != 'float unit_price' && obj.attr("class") != 'float unit_price inputEnable'){
            tr.find(".unit_price").val((unitPrice).toFixed(<?php echo $priceDecimal; ?>));
        }
        // Set Total Price If Action Not Total Price
        if(obj.attr("class") != 'float total_price' && obj.attr("class") != 'float total_price inputEnable'){
            if(disPercent > 0){
                discount = converDicemalJS(converDicemalJS(totalPrice * disPercent) / 100);
            }else if(disAmt > 0){
                discount = disAmt;
            }
            total = converDicemalJS(totalPrice - discount);
            tr.find(".discount").val((discount).toFixed(<?php echo $priceDecimal; ?>));
            tr.find(".total_price_bf_dis").val((totalPrice).toFixed(<?php echo $priceDecimal; ?>));
            tr.find(".total_price").val((total).toFixed(<?php echo $priceDecimal; ?>));
        }
        if(tr.find(".product_id").val() != ''){
            if(unitCost > unitPrice){
                tr.find(".priceDownQuotation").show();
                tr.find(".unit_price").css("color", "red");
            } else {
                tr.find(".priceDownQuotation").hide();
                tr.find(".unit_price").css("color", "#000");
            }
        }
        getTotalAmountQuotation();
    }
    
    function getTotalAmountQuotation(){
        var totalAmount = 0;
        var totalVatPercent = replaceNum($("#QuotationVatPercent").val());
        var totalVat = 0;
        var totalDiscount    = replaceNum($("#QuotationDiscount").val());
        var totalDisPercent  = replaceNum($("#QuotationDiscountPercent").val());
        var total    = 0;
        var vatCal   = $("#QuotationVatCalculate").val();
        var totalBfDis = 0;
        var totalAmtCalVat = 0;
        $(".tblQuotationList").find(".total_price").each(function(){
            if($.trim($(this).val()) != '' || $(this).val() != undefined ){
                totalAmount += replaceNum($(this).val());
            }
        });
        if(totalDisPercent > 0){
            totalDiscount  = replaceNum(converDicemalJS((totalAmount * totalDisPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
        }
        totalAmtCalVat = replaceNum(converDicemalJS(totalAmount - totalDiscount));
        // Check VAT Calculate Before Discount, Free, Mark Up
        if(vatCal == 1){
            $(".tblQuotationList").each(function(){
                var qty   = replaceNum($(this).find(".qty").val());
                var price = replaceNum($(this).find(".unit_price").val());
                totalBfDis += replaceNum(converDicemalJS(qty * price));
            });
            totalAmtCalVat = totalBfDis;
        }
        totalVat = replaceNum(converDicemalJS(converDicemalJS(totalAmtCalVat * totalVatPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
        total = converDicemalJS((totalAmount - totalDiscount) + totalVat);
        
        $("#QuotationTotalAmount").val((totalAmount).toFixed(<?php echo $priceDecimal; ?>));
        $("#QuotationDiscount").val((totalDiscount).toFixed(<?php echo $priceDecimal; ?>));
        $("#QuotationTotalVat").val((totalVat).toFixed(<?php echo $priceDecimal; ?>));
        $("#QuotationTotalAmountSummary").val((total).toFixed(<?php echo $priceDecimal; ?>));
        $("#QuotationTotalAmount, #QuotationTotalAmountSummary").priceFormat({
            centsLimit: <?php echo $priceDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function sortNuTableQuotation(){
        var sort = 1;
        $(".tblQuotationList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function showOriginalNameQuotation(puc, sku, name){
        var orgName = '';
        orgName += 'PUC: '+puc;
        orgName += '<br/><br/>SKU: '+puc;
        orgName += '<br/><br/>Name: '+name;
        return orgName;
    }
    
    function checkEventQuotation(){
        eventKeyQuotation();
        $(".tblQuotationList").unbind("click");
        $(".tblQuotationList").click(function(){
            eventKeyQuotation();
        });
    }
    
    function showProductInfoQuotation(currentTr){
        var customerId = $("#QuotationCustomerId").val();
        var productId  = currentTr.closest("tr").find(".product_id").val();
        if(productId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/quotations/productHistory"; ?>/"+productId+"/"+customerId,
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
<div class="inputContainer" style="width:100%;" id="quoteSearchForm">
    <table style="width: 100%;">
        <tr>
            <td style="width: 410px;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="SearchProductSkuQuotation" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductQuotation" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="SearchProductSkuQuotation" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td style="width: 300px; text-align: left;" id="divSearchQuotation">
                <img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer;"class="searchProductListQuotation" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" /> 
                <?php
                    if ($allowaddService) {
                ?>
                <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServiceQuotation" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" />
                <?php
                    }
                ?>
            </td>
            <td style="text-align: right;">
                <div style="width:100%;">
                    <label for="typeOfPriceQuotation"><?php echo TABLE_PRICE_TYPE; ?> :</label> &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceQuotation" name="data[Quotation][price_type_id]" style="height: 30px; width: 250px;">
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
<table id="tblQuotationHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 99%">
    <tr>
        <th class="first" style="width:5%"><?php echo TABLE_NO; ?></th>
        <th style="width:14%"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:15%"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:12%"><?php echo TABLE_UOM; ?></th>
        <th style="width:6%"><?php echo TABLE_QTY; ?></th>
        <th style="width:6%"> <?php echo 'FOC'; ?> </th>
        <th style="width:11%"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
        <th style="width:11%"><?php echo GENERAL_DISCOUNT; ?></th>
        <th style="width:11%"><?php echo TABLE_TOTAL_PRICE_SHORT; ?></th>
        <th style="width:10%;"></th>
    </tr>
</table>
<div id="bodyListQuotation">
    <table id="tblQuotation" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListQuotation" class="tblQuotationList" style="visibility: hidden;">
            <td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;"></td>
            <td style="width:14%; text-align: left; padding-left: 5px;">
                <span class="lblSKU"></span>
            </td>
            <td style="width:15%; text-align: left; padding-left: 5px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" value="" class="product_id" />
                    <input type="hidden" name="service_id[]" value="" />
                    <input type="hidden" class="orgProName" />
                    <input type="text" id="product" data="" name="product[]" readonly="readonly" class="product validate[required]" style="width: 85%; height: 25px;" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="width:12%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" value="1" name="conversion[]" class="conversion" />
                    <select id="qty_uom_id" style="width:80%; height: 25px;" name="qty_uom_id[]" class="qty_uom_id validate[required]">
                        <?php 
                        foreach($uoms as $uom){
                        ?>
                        <option conversion="1" value="<?php echo $uom['Uom']['id']; ?>"><?php echo $uom['Uom']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
            <td style="width:6%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:70%; height: 25px;" class="qty interger" />
                </div>
            </td>
            <td style="width:6%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty_free[]" style="width:70%; height: 25px;" class="qty_free interger" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />
                    <input type="text" id="unit_price" name="unit_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="0" style="width:60%; height: 25px;" class="float unit_price" />
                    <img alt="<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>" src="<?php echo $this->webroot . 'img/button/down.png'; ?>" style="display: none;" class="priceDownQuotation" align="absmiddle" onmouseover="Tip('<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>')" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="discount_id[]" />
                    <input type="hidden" name="discount_amount[]" value="0" />
                    <input type="hidden" name="discount_percent[]" value="0" />
                    <input type="text" class="discount" name="discount[]" style="width: 60%; height: 25px;" readonly="readonly" />
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountQu" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                </div>
            </td>
            <td style="width:11%; text-align: center; padding: 0px;">
                <input type="hidden" value="0" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                <input type="text" id="total_price" name="total_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="0" style="width:84%; height: 25px;" class="float total_price" />
            </td>
            <td style="width:10%;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveQuotationList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpQuotation" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownQuotation" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
    </table>
</div>