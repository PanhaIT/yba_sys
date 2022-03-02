<?php
$priceDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 40 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    $priceDecimal = $rowSetting['value'];
}
include("includes/function.php");

// Authentication
$this->element('check_access');
$allowaddService = checkAccess($user['User']['id'], $this->params['controller'], 'service');
$allowAddMisc = checkAccess($user['User']['id'], $this->params['controller'], 'miscellaneous');
$allowEditPrice = checkAccess($user['User']['id'], $this->params['controller'], 'editPrice');
$allowDiscount = checkAccess($user['User']['id'], $this->params['controller'], 'discount');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
?>
<script type="text/javascript">
    var tblRowOrder  = $("#OrderListOrder");
    var indexRowOrder   = 0;
    var searchCodeOrder = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OrderListOrder").remove();
        checkEventOrder();
        var waitForFinalEventOrder = (function () {
          var timersOrder = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timersOrder[uniqueId]) {
              clearTimeout (timersOrder[uniqueId]);
            }
            timersOrder[uniqueId] = setTimeout(callback, ms);
          };
        })();
        
        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabOrderReg != tabOrderId){
            $("a[href='"+tabOrderId+"']").click(function(){
                if($(".orderDetailOrder").html() != '' && $(".orderDetailOrder").html() != null){
                    waitForFinalEventOrder(function(){
                        refreshScreenOrder();
                        resizeFormTitleOrder();
                        resizeFornScrollOrder();
                    }, 500, "Finish");
                }
            });
            tabOrderReg = tabOrderId;
        }
        
        waitForFinalEventOrder(function(){
                refreshScreenOrder();
                resizeFormTitleOrder();
                resizeFornScrollOrder();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabOrderReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventOrder(function(){
                    refreshScreenOrder();
                    resizeFormTitleOrder();
                    resizeFornScrollOrder();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderOrder").click(function(){
            var OrderCompanyId = $("#OrderCompanyId").val();
            var OrderBranchId  = $("#OrderBranchId").val();
            var OrderOrderDate = $("#OrderOrderDate").val();
            var OrderCustomerId = $("#OrderCustomerId").val();
            
            if(OrderCompanyId == "" || OrderOrderDate == "" || OrderCustomerId == "" || OrderBranchId == ""){
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
                    $("#orderHeaderForm").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#orderHeaderForm").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabOrderReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventOrder(function(){
                        resizeFornScrollOrder();
                    }, 500, "Finish");
                }
            }
        });
        
        $("#SearchProductSkuOrder").autocomplete("<?php echo $this->base . "/orders/searchProduct/"; ?>", {
            width: 400,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $("#SearchProductSkuOrder").val(code);
            if(searchCodeOrder == 1){
                searchCodeOrder = 2;
                searchProductByCodeOrder(code, '', 1);
            }
        });
        
        $("#SearchProductSkuOrder").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchCodeOrder == 1){
                    searchCodeOrder = 2;
                    searchProductByCodeOrder($(this).val(), '', 1);
                }
                return false;
            }
        });
        
        $("#SearchProductPucOrder").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchCodeOrder == 1){
                    searchCodeOrder = 2;
                    searchProductByCodeOrder($(this).val(), '', 1);
                }
                return false;
            }
        });
        
        // Search Product
        $(".searchProductListOrder").click(function(){
            if(searchCodeOrder == 1){
                searchCodeOrder = 2;
                searchProductListOrder();
            }
        });
        // Search Service
        $(".addServiceOrder").click(function(){
            if(searchCodeOrder == 1){
                searchCodeOrder = 2;
                searchAllServiceOrder();
            }
        });
        // Search Misc
        $(".addMiscellaneousOrder").click(function(){
            if(searchCodeOrder == 1){
                searchCodeOrder = 2;
                searchAllMiscOrder();
            }
        });
        
        // Change Price Type
        $.cookie("typePriceOrder", $("#typeOfPriceOrder").val(), {expires : 7,path    : '/'});
        $("#typeOfPriceOrder").change(function(){
            if($(".tblOrderList").find(".product_id").val() == undefined){
                changePriceTypeOrder();
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
                            changePriceTypeOrder();
                            $.cookie("typePriceOrder", $("#typeOfPriceOrder").val(), {expires : 7,path    : '/'});
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#typeOfPriceOrder").val($.cookie('typePriceOrder'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        <?php
        if($allowAddProduct){
        ?>
        $("#addProductOrder").click(function(){
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
        if(!empty($order)) {
            $priceTypeId = $order['Order']['price_type_id'];
            $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$order['Order']['customer_id']." GROUP BY cgroup_id)");
            $rowPriceType = mysql_fetch_array($sqlPriceType);
        ?>
        // Check Price Type With Company
        checkPriceTypeOrder();
        // Put label VAT Calculate
        changeLblVatCalOrder();
        // Check Customer Price Type
        customerPriceTypeOrder('<?php echo $rowPriceType[0]; ?>', <?php echo $priceTypeId; ?>);
        <?php
        } else {
            $priceTypeId = '';
        ?>
        // Change Input CSS
        changeInputCSSOrder();
        <?php
        }
        ?>
        // Deposit
        $("#OrderTotalDeposit").autoNumeric({mDec: 2, aSep: ','});
        $("#OrderTotalDeposit").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#OrderTotalDeposit").blur(function(){
            if($(this).val() == ''){
                $(this).val('0.00');
            } else if(replaceNum($(this).val()) > replaceNum($("#OrderTotalAmountSummary").val())){
                $(this).val($("#OrderTotalAmountSummary").val());
            }
        });
    }); // End Document Ready
    
    function changePriceTypeOrder(){
        if($(".product").val() != undefined && $(".product").val() != ''){
            var priceType  = parseFloat(replaceNum($("#typeOfPriceOrder").val()));
            $(".tblOrderList").each(function(){
                if($(this).find("input[name='product_id[]']").val() != ''){
                    var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(<?php echo $priceDecimal; ?>));
                    calculateTotalRowOrder($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchProductListOrder(){
        searchCodeOrder = 1;
        if($("#OrderCustomerName").val() == "" || $("#OrderCompanyId").val() == "" || $("#OrderBranchId").val() == ""){
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
            var dateOrder = $("#OrderOrderDate").val().split("/")[2]+"-"+$("#OrderOrderDate").val().split("/")[1]+"-"+$("#OrderOrderDate").val().split("/")[0];
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/orders/product/"; ?>"+$("#OrderCompanyId").val()+"/"+$("#OrderLocationGroupId").val()+"/"+$("#OrderBranchId").val()+"/<?php echo $order['Order']['id']; ?>",
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
                                    searchCodeOrder = 2;
                                    searchProductByCodeOrder($("input[name='chkProduct']:checked").val(), '', 1);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllServiceOrder(){
        searchCodeOrder = 1;
        if($("#OrderCompanyId").val() == "" || $("#OrderCustomerName").val() == "" || $("#OrderBranchId").val() == ""){
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
                url:    "<?php echo $this->base . "/orders/service"; ?>/"+$("#OrderCompanyId").val()+"/"+$("#OrderBranchId").val(),
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
                                    searchCodeOrder = 2;
                                    addNewServiceOrder();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function searchAllMiscOrder(){
        searchCodeOrder = 1;
        if($("#OrderCompanyId").val() == "" || $("#OrderCustomerName").val() == "" || $("#OrderBranchId").val() == ""){
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
                url:    "<?php echo $this->base . "/orders/miscellaneous"; ?>",
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
                                    searchCodeOrder = 2;
                                    addNewMiscOrder();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function resizeFormTitleOrder(){
        var screen = 16;
        var widthList = $("#bodyListOrder").width();
        $("#tblOrderHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblOrderHeader").css('padding','0px');
        $("#tblOrderHeader").css('margin-top','5px');
        $("#tblOrderHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollOrder(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#orderHeaderForm').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#orderHeaderForm").height();
        }
        var btnHeader    = $("#btnHideShowHeaderOrder").height();
        var formFooter   = $(".footerSaveOrder").height();
        var formSearch   = $("#orderSearchForm").height();
        var tableHeader  = $("#tblOrderHeader").height();
        var screenRemain = 223;
        var getHeight    = windowHeight - (formHeader + btnHeader + formFooter + formSearch + tableHeader + screenRemain);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyListOrder").css('height',getHeight);
        $("#bodyListOrder").css('padding','0px');
        $("#bodyListOrder").css('width','100%');
        $("#bodyListOrder").css('overflow-x','hidden');
        $("#bodyListOrder").css('overflow-y','scroll');
    }
    
    function refreshScreenOrder(){
        $("#tblOrderHeader").removeAttr('style');
    }

    function searchProductByCodeOrder(productCode, uomSelected, qtyOrder){
        if($("#OrderCustomerId").val() == "" || $("#OrderCompanyId").val() == "" || $("#OrderBranchId").val() == ""){
            timeSearchOrder = 1;
            searchCodeOrder = 1;
            $("#SearchProductPucOrder").val("");
            $("#SearchProductSkuOrder").val('');
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
            $("#OrderOrderDate").datepicker("option", "dateFormat", "yy-mm-dd");
            var orderDate = $("#OrderOrderDate").val();
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . "/orders/searchProductByCode/"; ?>"+$("#OrderCompanyId").val()+"/"+$("#OrderCustomerId").val()+"/"+$("#OrderBranchId").val()+"/<?php echo $order['Order']['id']; ?>",
                data:   "data[code]="+productCode+"&data[order_date]="+orderDate+"&data[location_group_id]="+$("#OrderLocationGroupId").val()+"&data[lots_number]=&data[expired_date]=",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#SearchProductPucOrder").val("");
                    $("#SearchProductSkuOrder").val("");
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
                                    searchProductByCodeOrder(productCode, uomSelected, qtyOrder, msg);
                                }, time);
                                loop++;
                            });
                        }else{
                            addProductToListOrder(uomSelected, qtyOrder, msg);
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
                        searchCodeOrder = 1;
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
    
    function eventKeyOrder(){
        loadAutoCompleteOff();
        $(".product, .qty, .unit_price, .qty_uom_id, .total_price, .discount, .btnRemoveDiscountOrder, .btnRemoveOrderList, .btnProductInfo").unbind('click').unbind('keyup').unbind('keypress').unbind('change');
        $(".interger").autoNumeric({mDec: 0, aSep: ','});
        $(".float").autoNumeric({mDec: <?php echo $priceDecimal; ?>, aSep: ','});
        
        $(".btnRemoveOrderList").click(function(){
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
                        getTotalAmountOrder();
                        sortNuTableOrder();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".qty, .qty_free, .unit_price, .total_price").focus(function(){
            if(replaceNum($(this).val()) == "0"){
                $(this).val("");
            }
            calculateTotalRowOrder($(this));
        });
        
        $(".qty, .qty_free").blur(function(){
            if($(this).val() == ""){
                $(this).val("0");
            }
            var tr            = $(this);
            var productId     = tr.closest("tr").find("input[name='product_id[]']").val();
            var totalQtySales = replaceNum(tr.closest("tr").find(".totalQtySO").val());
            var qtyOrder      = replaceNum(getTotalQtyOrderSO(productId));
            if(productId != ""){
                if(qtyOrder > totalQtySales){
                    tr.closest("tr").find("input[name='qty[]']").val(0);
                    tr.closest("tr").find("input[name='qty_free[]']").val(0);
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
                                tr.closest("tr").find("input[name='qty[]']").select().focus();
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
            calculateTotalRowOrder(tr);
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
                        $("#SearchProductSkuOrder").focus().select();
                    }
                }else{
                    $(this).select().focus();
                }
                return false;
            }
        });

        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#SearchProductSkuOrder").select().focus();
                return false;
            }
        });
        
        $(".total_price").keyup(function(){
            var unitPrice       = 0;
            var discount        = 0;
            var totalPrice      = replaceNum($(this).val());
            var totalBfDis      = 0;
            var qty             = replaceNum($(this).closest("tr").find(".qty").val());
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
            calculateTotalRowOrder($(this));
        });
        
        $(".qty_uom_id").change(function(){
            if($(this).closest("tr").find(".product_id").val() != '' && replaceNum($(this).closest("tr").find(".product_id").val()) > 0){
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmVal      = replaceNum($(this).find("option[data-sm='1']").attr('conversion'));
                var conversion    = converDicemalJS(uomSmVal / uomConversion);
                var priceType     = replaceNum($("#typeOfPriceOrder").val());
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
            calculateTotalRowOrder($(this));
        });
        
        // Action Discount
        $(".discount").click(function(){
            <?php
            if($allowDiscount){
            ?>
            getItemDiscountOrder($(this));
            <?php
            }
            ?>
        });
        
        $(".btnRemoveDiscountOrder").click(function(){
            removeDiscountQu($(this).closest("tr"));
        });
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#OrderCustomerId").val();
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
            showProductInfoOrder($(this));
        });
        
        moveRowOrder();
    }
    
    function moveRowOrder(){
        $(".btnMoveDownOrderList, .btnMoveUpOrderList").unbind('click');
        $(".btnMoveDownOrderList").click(function () {
            var rowToMove = $(this).parents('tr.tblOrderList:first');
            var next = rowToMove.next('tr.tblOrderList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableOrder();
        });

        $(".btnMoveUpOrderList").click(function () {
            var rowToMove = $(this).parents('tr.tblOrderList:first');
            var prev = rowToMove.prev('tr.tblOrderList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableOrder();
        });
        
        sortNuTableOrder();
    }
    
    function getItemDiscountOrder(obj){
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/orders/invoiceDiscount"; ?>",
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
                            var totalDisAmt     = replaceNum($("#inputOrderDisAmt").val());
                            var totalDisPercent = replaceNum($("#inputOrderDisPer").val());
                            obj.closest("tr").find("input[name='discount_id[]']").val(0);
                            obj.closest("tr").find("input[name='discount_amount[]']").val(totalDisAmt);
                            obj.closest("tr").find("input[name='discount_percent[]']").val(totalDisPercent);
                            obj.closest("tr").find("input[name='discount[]']").css("display", "inline");
                            obj.closest("tr").find(".btnRemoveDiscountOrder").css("display", "inline");
                            calculateTotalRowOrder(obj);
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
        tr.find(".btnRemoveDiscountOrder").css("display", "none");
        calculateTotalRowOrder(tr);
    }

    function addProductToListOrder(uomSelected, qtyOrder, msg){
        indexRowOrder = Math.floor((Math.random() * 100000) + 1);
        // Product Information
        var productId    = msg.product_id;
        var productPUC   = msg.product_barcode;
        var productSku   = msg.product_code;
        var productName  = msg.product_name;
        var proCusName   = msg.product_cus_name;
        var productUomId = msg.product_uom_id;
        var smallUomVal  = msg.small_uom_val;
        var totalQty     = msg.total_qty;
        var tr           = tblRowOrder.clone(true);
        var productInfo  = showOriginalNameOrder(productPUC, productSku, productName);
        var branchId     = $("#OrderBranchId").val();
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowOrder);
        tr.find(".lblSKU").html(productSku);
        tr.find("input[name='product_id[]']").val(productId);
        tr.find(".orgProName").val(productInfo);
        tr.find("input[name='product[]']").attr("data", proCusName);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowOrder).val(proCusName+"/"+smallUomVal).removeAttr('readonly');
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowOrder).val(qtyOrder).attr('readonly', true);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowOrder).val(0);
        tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowOrder).html('<option value="">Please Select Uom</option>');
        tr.find(".conversion").attr("id", "conversion_"+indexRowOrder).val(smallUomVal);
        tr.find(".discount").attr("id", "discount_"+indexRowOrder).val(0);
        tr.find(".totalQtySO").attr("id", "inv_qty_"+indexRowOrder).val(totalQty);
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
                            var priceType     = $("#typeOfPriceOrder").val();
                            var lblPrice      = "price-uom-"+priceType;
                            productPrice  = parseFloat($(this).attr(lblPrice));
                            var lblCost       = "cost-uom-"+priceType;
                            unitCost      = parseFloat($(this).attr(lblCost));
                            complete = true;
                        } else{
                            if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                                $(this).attr("selected", true);
                                // Price
                                var priceType     = $("#typeOfPriceOrder").val();
                                var lblPrice      = "price-uom-"+priceType;
                                productPrice  = parseFloat($(this).attr(lblPrice));
                                var lblCost       = "cost-uom-"+priceType;
                                unitCost      = parseFloat($(this).attr(lblCost));
                                complete = true;
                            }
                        }
                        if(complete == true){
                            var totalPrice = converDicemalJS(productPrice * parseFloat(qtyOrder));
                            tr.find("input[name='unit_cost[]']").val(unitCost);
                            tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowOrder).val(productPrice);
                            tr.find("input[name='total_price_bf_dis[]']").val(totalPrice);
                            tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowOrder).val(totalPrice);
                            checkEventOrder();
                            getTotalAmountOrder();
                            if(unitCost > productPrice){
                                tr.find(".priceDownOrder").show();
                                tr.find(".unit_price").css("color", "red");
                            } else {
                                tr.find(".priceDownOrder").hide();
                                tr.find(".unit_price").css("color", "#000");
                            }
                            tr.find("input[name='qty[]']").removeAttr('readonly');
                            tr.find("input[name='qty[]']").select().focus();
                        }
                        return false;
                    }
                });
            }
        });
        $("#tblOrder").append(tr);
        sortNuTableOrder();
        searchCodeOrder = 1;
    }
    
    function addNewServiceOrder(){
        indexRowOrder = Math.floor((Math.random() * 100000) + 1);
        // Service Information
        var serviceId    = $("#ServiceServiceId").val();
        var serviceCode  = $("#ServiceServiceId").find("option:selected").attr('scode');
        var serviceName  = $("#ServiceServiceId").find("option:selected").attr('abbr');
        var servicePrice = $("#ServiceUnitPrice").val();
        var serviceUomId = $("#ServiceServiceId").find("option:selected").attr('suom');
        var tr           = tblRowOrder.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowOrder);
        tr.find(".lblSKU").html(serviceCode);
        tr.find("input[name='product_id[]']").val('');
        tr.find("input[name='service_id[]']").val(serviceId);
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowOrder).val(serviceName);
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowOrder).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowOrder).val(0);
        tr.find(".conversion").attr("id", "conversion_"+indexRowOrder).val(1);
        tr.find(".discount").attr("id", "discount_"+indexRowOrder).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowOrder).val(servicePrice);
        tr.find("input[name='total_price_bf_dis[]']").val(servicePrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowOrder).val(servicePrice);
        tr.find(".btnProductInfo").hide();
        if(serviceUomId == ''){
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowOrder).html('<option value="1" conversion="1" selected="selected">None</option>').css('visibility', 'hidden');
        } else {
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+indexRowOrder).find("option[value='"+serviceUomId+"']").attr("selected", true);
            tr.find("select[name='qty_uom_id[]']").find("option[value!='"+serviceUomId+"']").hide();
        }
        $("#tblOrder").append(tr);
        $("#tblOrder").find("tr:last").find(".qty").select().focus();
        sortNuTableOrder();
        checkEventOrder();
        getTotalAmountOrder();
        searchCodeOrder = 1;
    }
    
    function addNewMiscOrder(){
        indexRowOrder = Math.floor((Math.random() * 100000) + 1);
        // Service Information
        var miscName  = $("#MiscellaneousDescription").val();
        var miscPrice = $("#MiscellaneousUnitPrice").val();
        var tr        = tblRowOrder.clone(true);
        
        tr.removeAttr("style").removeAttr("id");
        tr.find("td:eq(0)").html(indexRowOrder);
        tr.find(".lblSKU").html('');
        tr.find("input[name='product[]']").attr("id", "product_"+indexRowOrder).val(miscName);
        tr.find("input[name='qty[]']").attr("id", "qty_"+indexRowOrder).val(1);
        tr.find("input[name='qty_free[]']").attr("id", "qty_free_"+indexRowOrder).val(0);
        tr.find(".conversion").attr("id", "conversion_"+indexRowOrder).val(1);
        tr.find(".discount").attr("id", "discount_"+indexRowOrder).val(0);
        tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+indexRowOrder).val(miscPrice);
        tr.find("input[name='total_price_bf_dis[]']").val(miscPrice);
        tr.find("input[name='total_price[]']").attr("id", "total_price_"+indexRowOrder).val(miscPrice);
        tr.find(".btnProductInfo").hide();
        $("#tblOrder").append(tr);
        $("#tblOrder").find("tr:last").find(".qty").select().focus();
        sortNuTableOrder();
        checkEventOrder();
        getTotalAmountOrder();
        searchCodeOrder = 1;
    }

    function checkExistingRecordOrder(productId){
        var isFound = false;
        $("#tblOrder").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function calculateTotalRowOrder(obj){
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
                tr.find(".priceDownOrder").show();
                tr.find(".unit_price").css("color", "red");
            } else {
                tr.find(".priceDownOrder").hide();
                tr.find(".unit_price").css("color", "#000");
            }
        }
        getTotalAmountOrder();
    }
    
    function getTotalAmountOrder(){
        var totalAmount = 0;
        var totalVatPercent  = replaceNum($("#OrderVatPercent").val());
        var totalVat = 0;
        var totalDiscount    = replaceNum($("#OrderDiscount").val());
        var totalDisPercent  = replaceNum($("#OrderDiscountPercent").val());
        var total    = 0;
        var vatCal   = $("#OrderVatCalculate").val();
        var totalBfDis = 0;
        var totalAmtCalVat = 0;
        $(".tblOrderList").find(".total_price").each(function(){
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
            $(".tblOrderList").each(function(){
                var qty   = replaceNum($(this).find(".qty").val()) + replaceNum($(this).find(".qty_free").val());
                var price = replaceNum($(this).find(".unit_price").val());
                totalBfDis += replaceNum(converDicemalJS(qty * price));
            });
            totalAmtCalVat = totalBfDis;
        }
        totalVat = replaceNum(converDicemalJS(converDicemalJS(totalAmtCalVat * totalVatPercent) / 100).toFixed(<?php echo $priceDecimal; ?>));
        total = converDicemalJS((totalAmount - totalDiscount) + totalVat);
        
        $("#OrderTotalAmount").val((totalAmount).toFixed(<?php echo $priceDecimal; ?>));
        $("#OrderDiscount").val((totalDiscount).toFixed(<?php echo $priceDecimal; ?>));
        $("#OrderTotalVat").val((totalVat).toFixed(<?php echo $priceDecimal; ?>));
        $("#OrderTotalAmountSummary").val((total).toFixed(<?php echo $priceDecimal; ?>));
        $("#OrderTotalAmount, #OrderTotalAmountSummary").priceFormat({
            centsLimit: <?php echo $priceDecimal; ?>,
            centsSeparator: '.'
        });
    }
    
    function sortNuTableOrder(){
        var sort = 1;
        $(".tblOrderList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function checkEventOrder(){
        eventKeyOrder();
        $(".tblOrderList").unbind("click");
        $(".tblOrderList").click(function(){
            eventKeyOrder();
        });
    }
    
    function showOriginalNameOrder(puc, sku, name){
        var orgName = '';
        orgName += 'PUC: '+puc;
        orgName += '<br/><br/>SKU: '+puc;
        orgName += '<br/><br/>Name: '+name;
        return orgName;
    }
    
    function showProductInfoOrder(currentTr){
        var customerId = $("#OrderCustomerId").val();
        var productId  = currentTr.closest("tr").find(".product_id").val();
        if(productId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/orders/productHistory"; ?>/"+productId+"/"+customerId,
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
<div class="inputContainer" style="width:100%;" id="orderSearchForm">
    <table style="width: 100%;">
        <tr>
            <td style="width: 300px; text-align: left;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <input type="text" id="SearchProductSkuOrder" style="width:360px; height: 25px; border: none; background: none;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductOrder" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                ?>
                <input type="text" id="SearchProductSkuOrder" style="width:90%; height: 25px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
                <?php
                }
                ?>
            </td>
            <td style="width: 15%; text-align: left;" id="divSearchOrder">
                <img alt="Search" align="absmiddle" style="cursor: pointer;"class="searchProductListOrder" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" /> 
                <?php
                    if ($allowaddService) {
                ?>
                <img alt="<?php echo SALES_ORDER_ADD_SERVICE; ?>" style="cursor: pointer;" align="absmiddle" class="addServiceOrder" onmouseover="Tip('<?php echo SALES_ORDER_ADD_SERVICE; ?>')" src="<?php echo $this->webroot . 'img/button/service.png'; ?>" />
                <?php
                    }
                ?>
            </td>
            <td style="text-align:right">
                <div style="width:100%;">
                    <?php echo TABLE_PRICE_TYPE; ?> : &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceOrder" name="data[Order][price_type_id]" style="height: 30px; width: 250px;">
                        <?php
                        $sqlPrice = mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM price_type_companies WHERE price_type_id = price_types.id) AS company_id FROM price_types WHERE is_active = 1 AND is_ecommerce = 0 AND id IN (SELECT price_type_id FROM price_type_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") GROUP BY price_type_companies.price_type_id) ORDER BY ordering ASC");
                        while($row = mysql_fetch_array($sqlPrice)){
                        ?>
                        <option value="<?php echo $row['id']; ?>" comp="<?php echo $row['company_id']; ?>" <?php if($priceTypeId == $row['id']){ ?>selected="selected"<?php } ?>><?php echo $row['name']; ?></option>
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
<table id="tblOrderHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 99%">
    <tr>
        <th class="first" style="width:5%"><?php echo TABLE_NO; ?></th>
        <th style="width:11%"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:20%"><?php echo GENERAL_DESCRIPTION; ?></th>
        <th style="width:13%"><?php echo TABLE_UOM; ?></th>
        <th style="width:8%"><?php echo TABLE_QTY; ?></th>
        <th style="width:8%"><?php echo TABLE_F_O_C; ?></th>
        <th style="width:9%"><?php echo SALES_ORDER_UNIT_PRICE; ?></th>
        <th style="width:9%"><?php echo POS_DISCOUNTS; ?></th>
        <th style="width:9%"><?php echo TABLE_TOTAL_PRICE_SHORT; ?></th>
        <th style="width:8%"></th>
    </tr>
</table>
<div id="bodyListOrder">
    <table id="tblOrder" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListOrder" class="tblOrderList" style="visibility: hidden;">
            <td class="first" style="width:5%; text-align: center; padding: 0px; height: 30px;"></td>
            <td style="width:11%; text-align: left; padding: 5px;">
                <span class="lblSKU"></span>
            </td>
            <td style="width:20%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="totalQtySO" />
                    <input type="hidden" name="product_id[]" value="" class="product_id" />
                    <input type="hidden" name="service_id[]" value="" />
                    <input type="hidden" class="orgProName" />
                    <input type="text" id="product" data="" name="product[]" readonly="readonly" class="product validate[required]" style="width: 85%; height: 25px;" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="width:13%; padding: 0px; text-align: center">
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
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:80%; height: 25px;" class="qty interger" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free" name="qty_free[]" style="width:80%; height: 25px;" class="qty_free interger" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />
                    <input type="text" id="unit_price" name="unit_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="0" style="width:60%; height: 25px;" class="float unit_price" />
                    <img alt="<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>" src="<?php echo $this->webroot . 'img/button/down.png'; ?>" style="display: none;" class="priceDownOrder" align="absmiddle" onmouseover="Tip('<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>')" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="discount_id[]" />
                    <input type="hidden" name="discount_amount[]" value="0" />
                    <input type="hidden" name="discount_percent[]" value="0" />
                    <input type="text" class="discount" name="discount[]" style="width: 60%; height: 25px;" readonly="readonly" />
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountOrder" align="absmiddle" style="cursor: pointer; display: none;" onmouseover="Tip('Remove')" />
                </div>
            </td>
            <td style="width:9%; text-align: center; padding: 0px;">
                <input type="hidden" value="0" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                <input type="text" id="total_price" name="total_price[]" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="0" style="width:84%; height: 25px;" class="float total_price" />
            </td>
            <td style="width:8%">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
        $index     = 0;
        $dateNow   = date("Y-m-d");
        $dateOrder = $order['Order']['order_date'];
        // List Location
        $locCon = '';
        if((strtotime($dateOrder) < strtotime($dateNow)) && !empty($orderDetails)){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableTmp = "order_edit_tmp_inventory_".$user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT,
                            `date` date DEFAULT NULL,
                            `product_id` int(11) DEFAULT NULL,
                            `lots_number` VARCHAR(50) NOT NULL,
                            `expired_date` DATE NOT NULL,
                            `location_group_id` int(11) DEFAULT NULL,
                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `product_id` (`product_id`),
                            KEY `lots_number` (`lots_number`),
                            KEY `expired_date` (`expired_date`),
                            KEY `location_group_id` (`location_group_id`),
                            KEY `date` (`date`)
                          ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
            // Get Total Qty On Peroid
            $joinProducts = " INNER JOIN products ON";
            $tableDailyBbi = "";
            $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out)) AS total_qty, product_id, lots_number, expired_date";
            $conditionDailyBbi = " products.is_active = 1 AND date <= '".$dateOrder."' AND products.id IN (SELECT product_id FROM sales_order_details WHERE sales_order_id = ".$order['Order']['id'].")";
            $groupByDaily = "GROUP BY product_id, lots_number, expired_date";
            $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id = '.$order['Order']['location_group_id'].$locCon.' GROUP BY id');
            if(@mysql_num_rows($queryLocationList)){
                if(mysql_num_rows($queryLocationList) == 1){
                    while($dataLocationList=mysql_fetch_array($queryLocationList)){
                        // Stock Daily Bigging
                        $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
                    }
                }else{
                    $locId = 1;
                    $tmpLocationId = 0;
                    while($dataLocationList=mysql_fetch_array($queryLocationList)){
                        if($locId == 1){
                            // Stock Daily Bigging
                            $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
                        }else{
                            // Stock Daily Ending
                            $tableDailyBbi .= " UNION ALL SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE ".$conditionDailyBbi." ".$groupByDaily;
                        }
                        $tmpLocationId = $dataLocationList['location_id'];
                        $locId++;
                    }
                }
            }
            // Insert
            $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, product_id, lots_number, expired_date FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id, lots_number, expired_date";
            $queryTotal = mysql_query($sqlCmtDailyBiginning);
            while($dataTotal = mysql_fetch_array($queryTotal)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    location_group_id,
                                    lots_number,
                                    expired_date,
                                    total_qty
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    " . $order['Order']['location_group_id'] . ",
                                    '" . $dataTotal['lots_number'] . "',
                                    '" . $dataTotal['expired_date'] . "',
                                    " . $dataTotal['qty'] . "
                                )") or die(mysql_error());
            }
        }
        if(!empty($orderDetails)){
            foreach($orderDetails AS $orderDetail){
                $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$order['Order']['location_group_id']}_group_totals WHERE lots_number ='0' AND expired_date ='0000-00-00' AND product_id ={$orderDetail['Product']['id']} AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$order['Order']['location_group_id'].$locCon." GROUP BY id) GROUP BY product_id");
                $rowInv = mysql_fetch_array($sqlInv);
                $totalOrder = ($orderDetail['OrderDetail']['qty'] + $orderDetail['OrderDetail']['qty_free']) * $orderDetail['OrderDetail']['conversion'];
                $totalInventory = $rowInv['total_qty'];
                if((strtotime($dateOrder) < strtotime($dateNow))){
                    // Get Total Qty Pass
                    $sqlTotalPass = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE lots_number = '0' AND expired_date = '0000-00-00' AND product_id = ".$orderDetail['Product']['id']." AND date = '".$dateOrder."' AND location_group_id =".$order['Order']['location_group_id']);
                    $rowTotalPass = mysql_fetch_array($sqlTotalPass);
                    /** F-ID: 1100
                    * Compare Total Qty in Pass and Current Date (rowTotalPass = Total Qty In Pass, totalInventory = Total Qty in Current)
                    * IF PASS < CURRENT Total Qty = PASS
                    * ELSE PASS >= CURRENT Total Qty = CURRENT
                    */
                   if($rowTotalPass['total_qty'] < $totalInventory){
                       $totalInventory = $rowTotalPass['total_qty'];
                   }
                }
                $totalQtySales = $totalInventory;
//                if($rowWareOpt['allow_negative_stock'] == 1){
//                    $totalQtySales = 1000000;
//                }
                // Check Name With Customer
                $productName = $orderDetail['Product']['name'];
                $sqlProCus = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$orderDetail['OrderDetail']['product_id']." AND customer_id = ".$order['Order']['customer_id']." ORDER BY created DESC LIMIT 1");
                if(mysql_num_rows($sqlProCus)){
                    $rowProCus   = mysql_fetch_array($sqlProCus);
                    $productName = $rowProCus['name'];
                }
        ?>
        <tr class="tblOrderList">
            <td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;"><?php echo ++$index; ?></td>
            <td style="width:11%; text-align: left; padding: 5px;">
                <span class="lblSKU"><?php echo $orderDetail['Product']['code']; ?></span>
            </td>
            <td style="width:20%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="totalQtySO" value="<?php echo ($totalQtySales + $totalOrder); ?>" />
                    <input type="hidden" name="product_id[]" value="<?php echo $orderDetail['OrderDetail']['product_id']; ?>" class="product_id" />
                    <input type="hidden" name="service_id[]" value="" />
                    <input type="hidden" class="orgProName" value="<?php echo "PUC: ".htmlspecialchars($orderDetail['Product']['barcode'], ENT_QUOTES, 'UTF-8')."<br/><br/>SKU: ".htmlspecialchars($orderDetail['Product']['code'], ENT_QUOTES, 'UTF-8')."<br/><br/>Name: ".htmlspecialchars($orderDetail['Product']['name'], ENT_QUOTES, 'UTF-8'); ?>" />
                    <input type="text" id="product_<?php echo $index; ?>" data="<?php echo str_replace('"', '&quot;', $productName); ?>" value="<?php echo str_replace('"', '&quot;', $productName)."/".$orderDetail['OrderDetail']['conversion']; ?>" name="product[]" class="product validate[required]" style="width: 85%; height: 25px;" />
                    <img alt="Information" src="<?php echo $this->webroot . 'img/button/view.png'; ?>" class="btnProductInfo" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Information')" />
                </div>
            </td>
            <td style="width:13%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="conversion[]" class="conversion" value="<?php echo $orderDetail['OrderDetail']['conversion']; ?>" />
                    <select id="qty_uom_id_<?php echo $index; ?>" style="width:80%; height: 25px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php
                        $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$orderDetail['Product']['price_uom_id']."
                                            UNION
                                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$orderDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$orderDetail['Product']['price_uom_id'].")
                                            ORDER BY conversion ASC");
                        $i = 1;
                        $length = mysql_num_rows($query);
                        $costSelected = 0;
                        while($data=mysql_fetch_array($query)){
                            $selected = "";
                            $priceLbl = "";
                            $costLbl  = "";
                            if($data['id'] == $orderDetail['OrderDetail']['qty_uom_id']){   
                                $selected = ' selected="selected" ';
                            }
                            // Get Price
                            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$orderDetail['Product']['id']." AND product_prices.uom_id =".$data['id']);
                            if(@mysql_num_rows($sqlPrice)){
                                $price = 0;
                                while($rowPrice = mysql_fetch_array($sqlPrice)){
                                    $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                                    if($rowPrice['set_type'] == 1){
                                        $price = $rowPrice['amount'];
                                    }else if($rowPrice['set_type'] == 2){
                                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                                        $price = $unitCost + $percent;
                                    }else if($rowPrice['set_type'] == 3){
                                        $price = $unitCost + $rowPrice['add_on'];
                                    }
                                    $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                                    $costLbl  .= 'cost-uom-'.$rowPrice['price_type_id'].'="'.$unitCost.'" ';
                                    if($data['id'] == $orderDetail['OrderDetail']['qty_uom_id'] && $rowPrice['price_type_id'] == $priceTypeId){
                                        $costSelected = $unitCost;
                                    }
                                }
                            }else{
                                $priceLbl .= 'price-uom-1="0" price-uom-2="0"';
                                $costLbl  .= 'cost-uom-1="0" cost-uom-2="0"';
                            }
                        ?>
                        <option <?php echo $costLbl; ?> <?php echo $priceLbl; ?> <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $orderDetail['Product']['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                        <?php 
                        $i++;
                        } ?>
                    </select>
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" value="<?php echo $orderDetail['OrderDetail']['qty']; ?>" name="qty[]" style="width:70%; height: 25px;" class="qty interger" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free_<?php echo $index; ?>" value="<?php echo $orderDetail['OrderDetail']['qty_free']; ?>" name="qty_free[]" style="width:80%; height: 25px;" class="qty_free interger" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="<?php echo number_format($costSelected, 2); ?>" />
                    <input type="text" id="unit_price_<?php echo $index; ?>" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="<?php echo number_format($orderDetail['OrderDetail']['unit_price'], 2); ?>" name="unit_price[]" style="width:60%; height: 25px; <?php if($orderDetail['OrderDetail']['unit_price'] < $costSelected){ ?>color: red;<?php } ?>" class="float unit_price" />
                    <img alt="<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>" src="<?php echo $this->webroot . 'img/button/down.png'; ?>" <?php if($orderDetail['OrderDetail']['unit_price'] >= $costSelected){ ?>style="display: none;"<?php } ?> class="priceDownOrder" align="absmiddle" onmouseover="Tip('<?php echo MESSAGE_UNIT_PRICE_LESS_THAN_UNIT_COST; ?>')" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="discount_id[]" value="<?php echo $orderDetail['OrderDetail']['discount_id']; ?>" />
                    <input type="hidden" name="discount_amount[]" value="<?php echo $orderDetail['OrderDetail']['discount_amount']; ?>" />
                    <input type="hidden" name="discount_percent[]" value="<?php echo $orderDetail['OrderDetail']['discount_percent']; ?>" />
                    <input type="text" id="discount_<?php echo $index; ?>" class="discount" name="discount[]" value="<?php echo number_format($orderDetail['OrderDetail']['discount_amount'], 2); ?>" style="width: 60%; height: 25px;" readonly="readonly" />
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountOrder" align="absmiddle" style="cursor: pointer; <?php if(empty($orderDetail['OrderDetail']['discount_id'])){ ?> display: none;<?php } ?>" onmouseover="Tip('Remove')" />
                </div>
            </td>
            <td style="width:9%; text-align: center; padding: 0px;">
                <input type="hidden" value="<?php echo number_format($orderDetail['OrderDetail']['total_price'], 2); ?>" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                <input type="text" id="total_price_<?php echo $index; ?>" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="<?php echo number_format($orderDetail['OrderDetail']['total_price'] - $orderDetail['OrderDetail']['discount_amount'], 2); ?>" name="total_price[]" style="width:84%; height: 25px;" class="float total_price" />
            </td>
            <td style="width:8%">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveOrderList" id="btnRemoveOrderList_<?php echo $index; ?>" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
            }
        }
        if(!empty($orderServices)){
            foreach($orderServices AS $orderService){
                $uomName = 'None';
                $uomVal  = 1;
                if($orderService['Service']['uom_id'] != ''){
                    $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$orderService['Service']['uom_id']);
                    $rowUom = mysql_fetch_array($sqlUom);
                    $uomName = $rowUom[0];
                    $uomVal  = $orderService['Service']['uom_id'];
                }
        ?>
        <tr class="tblOrderList">
            <td class="first" style="width:5%; text-align: center;padding: 0px; height: 30px;"><?php echo ++$index; ?></td>
            <td style="width:11%; text-align: left; padding: 5px;">
                <span class="lblSKU"><?php echo $orderService['Service']['code']; ?></span>
            </td>
            <td style="width:20%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" value="" class="product_id" />
                    <input type="hidden" name="service_id[]" value="<?php echo $orderService['OrderService']['service_id']; ?>" />
                    <input type="hidden" class="orgProName" />
                    <input type="text" id="product_<?php echo $index; ?>" value="<?php echo str_replace('"', '&quot;', $orderService['Service']['name']); ?>" readonly="readonly" name="product[]" class="product validate[required]" style="width: 85%; height: 25px;" />
                </div>
            </td>
            <td style="width:13%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="conversion[]" class="conversion" value="1" />
                    <select id="qty_uom_id_<?php echo $index; ?>" style="width:80%; height: 20px; <?php if($uomName == 'None'){ ?>visibility: hidden;<?php } ?>" name="qty_uom_id[]" class="qty_uom_id">
                        <option value="<?php echo $uomVal; ?>" conversion="1" selected="selected"><?php echo $uomName;?></option>
                    </select>
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" value="<?php echo $orderService['OrderService']['qty']; ?>" name="qty[]" style="width:70%; height: 25px;" class="qty interger" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_free_<?php echo $index; ?>" value="<?php echo $orderService['OrderService']['qty_free']; ?>" name="qty_free[]" style="width:80%; height: 25px;" class="qty_free interger" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="float unit_cost" name="unit_cost[]" value="0" />
                    <input type="text" id="unit_price_<?php echo $index; ?>" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="<?php echo number_format($orderService['OrderService']['unit_price'], 2); ?>" name="unit_price[]" style="width:70%; height: 25px;" class="float unit_price" />
                </div>
            </td>
            <td style="width:9%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="discount_id[]" value="<?php echo $orderService['OrderService']['discount_id']; ?>" />
                    <input type="hidden" name="discount_amount[]" value="<?php echo $orderService['OrderService']['discount_amount']; ?>" />
                    <input type="hidden" name="discount_percent[]" value="<?php echo $orderService['OrderService']['discount_percent']; ?>" />
                    <input type="text" id="discount_<?php echo $index; ?>" class="discount" name="discount[]" value="<?php echo number_format($orderService['OrderService']['discount_amount'], 2); ?>" style="width: 60%; height: 25px;" readonly="readonly" />
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveDiscountOrder" align="absmiddle" style="cursor: pointer; <?php if(empty($orderService['OrderService']['discount_id'])){ ?> display: none;<?php } ?>" onmouseover="Tip('Remove')" />
                </div>
            </td>
            <td style="width:9%; text-align: center; padding: 0px;">
                <input type="hidden" value="<?php echo number_format($orderService['OrderService']['total_price'], 2); ?>" class="total_price_bf_dis float" name="total_price_bf_dis[]" />
                <input type="text" id="total_price_<?php echo $index; ?>" <?php if(!$allowEditPrice){ ?>readonly="readonly"<?php } ?> value="<?php echo number_format($orderService['OrderService']['total_price'] - $orderService['OrderService']['discount_amount'], 2); ?>" name="total_price[]" style="width:84%; height: 25px;" class="float total_price" />
            </td>
            <td style="width:8%">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveOrderList" id="btnRemoveOrderList_<?php echo $index; ?>" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownOrderList" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
</div>