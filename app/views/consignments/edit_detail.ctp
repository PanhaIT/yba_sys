<script type="text/javascript">
    var rowTableConsignment    =  $("#OrderListConsignment");
    var rowIndexConsignment = 0;
    var timeBarcodeConsignment = 1;
    var invTotalQtyConsignment = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OrderListConsignment").remove();
        var waitForFinalEventConsignment = (function () {
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
        if(tabConsignmentReg != tabConsignmentId){
            $("a[href='"+tabConsignmentId+"']").click(function(){
                if($(".orderDetailConsignment").html() != '' && $(".orderDetailConsignment").html() != null){
                    waitForFinalEventConsignment(function(){
                        refreshScreenConsignment();
                        resizeFormTitleConsignment();
                        resizeFornScrollConsignment();
                    }, 500, "Finish");
                }
            });
            tabConsignmentReg = tabConsignmentId;
        }
        
        waitForFinalEventConsignment(function(){
            refreshScreenConsignment();
            resizeFormTitleConsignment();
            resizeFornScrollConsignment();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabConsignmentReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventConsignment(function(){
                    refreshScreenConsignment();
                    resizeFormTitleConsignment();
                    resizeFornScrollConsignment();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderConsignment").click(function(){
            var ConsignmentCompanyId       = $("#ConsignmentCompanyId").val();
            var ConsignmentBranchId        = $("#ConsignmentBranchId").val();
            var ConsignmentLocationGroupId = $("#ConsignmentLocationGroupId").val();
            var ConsignmentDate            = $("#ConsignmentDate").val();
            var ConsignmentCustomerName    = $("#ConsignmentCustomerName").val();
            
            if(ConsignmentCompanyId == "" || ConsignmentBranchId == "" || ConsignmentLocationGroupId == "" || ConsignmentDate == "" || ConsignmentCustomerName == ""){
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
                    $("#ConsignmentTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#ConsignmentTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabConsignmentReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventConsignment(function(){
                        resizeFornScrollConsignment();
                    }, 500, "Finish");
                }
            }
        });
        
        $("#searchProductUpcConsignment").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(timeBarcodeConsignment == 1){
                    timeBarcodeConsignment = 2;
                    searchProductByCodeConsignment($(this).val(), '', 1);
                }
                return false;
            }
        });
       
        $("#searchProductSkuConsignment").autocomplete("<?php echo $this->base . "/consignments/searchProduct/"; ?>", {
            width: 400,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[0]+"-"+value.split(".*")[1];
            }
        }).result(function(event, value){
            var code = value.toString().split(".*")[0];
            $("#searchProductSkuConsignment").val(code);
            if(timeBarcodeConsignment == 1){
                timeBarcodeConsignment = 2;
                searchProductByCodeConsignment(code, '', 1);
            }
        });
        
        $("#searchProductSkuConsignment").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(timeBarcodeConsignment == 1){
                    timeBarcodeConsignment = 2;
                    searchProductByCodeConsignment($(this).val(), '', 1);
                }
                return false;
            }
        });
        
        $(".searchProductListConsignment").click(function(){
            searchAllProductConsignment();
        });
        // Change Price Type
        $.cookie("typePriceConsignment", $("#typeOfPriceConsignment").val(), {expires : 7, path : '/'});
        $("#typeOfPriceConsignment").change(function(){
            if($(".tblConsignmentList").find(".product_id").val() == undefined){
                changePriceTypeConsignment();
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
                            changePriceTypeConsignment();
                            $.cookie("typePriceConsignment", $("#typeOfPriceConsignment").val(), {expires : 7, path : '/'});
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#typeOfPriceConsignment").val($.cookie('typePriceConsignment'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        // Event Action
        checkEventConsignment();
        <?php  
        if(!empty($consignment)) {
            $priceTypeId = $consignment['Consignment']['price_type_id'];
            $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$consignment['Consignment']['customer_id']." GROUP BY cgroup_id)");
            $rowPriceType = mysql_fetch_array($sqlPriceType);
        ?>
        // Check Price Type With Company
        checkPriceTypeConsignment();
        // Check Customer Price Type
        customerPriceTypeConsignment('<?php echo $rowPriceType[0]; ?>', <?php echo $priceTypeId; ?>);
        <?php
        } else {
            $priceTypeId = '';
        ?>
        changeInputCSSConsignment();
        <?php
        }
        ?>
    });
    
    function changePriceTypeConsignment(){
        if($(".product").val() != undefined && $(".product").val() != ''){
            var priceType  = parseFloat(replaceNum($("#typeOfPriceConsignment").val()));
            $(".tblConsignmentList").each(function(){
                if($(this).find("input[name='product_id[]']").val() != ''){
                    var unitPrice = replaceNum($(this).closest("tr").find(".qty_uom_id").find("option:selected").attr("price-uom-"+priceType));
                    $(this).find(".unit_price").val(converDicemalJS(unitPrice).toFixed(2));
                    funcCheckCondiConsignment($(this).find("input[name='product_id[]']"));
                }
            });
        }
    }
    
    function searchAllProductConsignment(){
        if($("#ConsignmentCompanyId").val() == "" || $("#ConsignmentBranchId").val() == "" || $("#ConsignmentLocationGroupId").val() == "" || $("#ConsignmentCustomerId").val() == ""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_LOCATION_CUSTOMER; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_WARNING; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
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
        }else{
            var dateOrder = $("#ConsignmentDate").val().split("/")[2]+"-"+$("#ConsignmentDate").val().split("/")[1]+"-"+$("#ConsignmentDate").val().split("/")[0];
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/consignments/product/"; ?>"+$("#ConsignmentCompanyId").val()+"/"+$("#ConsignmentLocationGroupId").val()+"/"+$("#ConsignmentBranchId").val()+"/<?php echo $consignment['Consignment']['id']; ?>",
                data:   "order_date="+dateOrder,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    timeBarcodeConsignment == 1;
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
                                if($("input[name='chkProduct']:checked").val()){
                                    var qty = parseFloat($("input[name='chkProduct']:checked").attr('class'));
                                    var value = $("input[name='chkProduct']:checked").val();
                                    if(qty > 0){
                                        if(timeBarcodeConsignment == 1){
                                            timeBarcodeConsignment = 2;
                                            searchProductByCodeConsignment(value, '', 1);
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
    
    function resizeFormTitleConsignment(){
        var screen = 16;
        var widthList = $("#bodyList").width();
        $("#tblConsignmentHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblConsignmentHeader").css('padding','0px');
        $("#tblConsignmentHeader").css('margin-top','5px');
        $("#tblConsignmentHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollConsignment(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#ConsignmentTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#ConsignmentTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderConsignment").height();
        var formFooter   = $(".footerFormConsignment").height();
        var formSearch   = $("#searchFormConsignment").height();
        var tableHeader  = $("#tblConsignmentHeader").height();
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
    
    function refreshScreenConsignment(){
        $("#tblConsignmentHeader").removeAttr('style');
    }

    function searchProductByCodeConsignment(productCode, uomSelected, qtyOrder){
        if($("#ConsignmentCompanyId").val() == "" || $("#ConsignmentBranchId").val() == "" || $("#ConsignmentLocationGroupId").val() == "" || $("#ConsignmentCustomerId").val() == ""){
            $("#searchProductUpcConsignment").val("");
            $("#searchProductSkuConsignment").val('');
            timeBarcodeConsignment = 1;
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
            var dateOrder = $("#ConsignmentDate").val().split("/")[2]+"-"+$("#ConsignmentDate").val().split("/")[1]+"-"+$("#ConsignmentDate").val().split("/")[0];
            $.ajax({
                dataType: "json",
                type:   "POST",
                url:    "<?php echo $this->base . "/consignments/searchProductByCode"; ?>/"+$("#ConsignmentCompanyId").val()+"/"+$("#ConsignmentCustomerId").val()+"/"+$("#ConsignmentBranchId").val()+"/<?php echo $consignment['Consignment']['id']; ?>",
                data:   "data[code]=" + productCode +
                        "&data[order_date]=" + dateOrder +
                        "&data[location_group_id]=" + $("#ConsignmentLocationGroupId").val(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#searchProductUpcConsignment").val("");
                    $("#searchProductSkuConsignment").val('');
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
                                    searchProductByCodeConsignment(productCode, uomSelected, qtyOrder);
                                }, time);
                                loop++;
                            });
                        }else{
                            addProductConsignment(uomSelected, qtyOrder, msg);
                        }
                    }else if((msg.total_qty == 0 || parseFloat(msg.total_qty) < parseFloat(qtyOrder)) && msg.product_id != "" && parseFloat(msg.product_id) > 0){
                        timeBarcodeConsignment = 1;
                        $("#dialog").html('<p style="font-size: 16px;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_OUT_OF_STOCK; ?> : '+$("#salesOrderProductSku").val()+' - '+$("#salesOrderProductName").val()+'</p>');
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
                        timeBarcodeConsignment = 1;
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
    
    function keyEventConsignment(){
        // Protect Browser Auto Complete QTY Input
        loadAutoCompleteOff();
        $(".product, .qty, .unit_price, .qty_uom_id, .total_price, .btnRemoveConsignment, .noteAddConsignment").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 3, aSep: ','});
        $(".floatQty").priceFormat();
        
        // Change Product Name With Customer
        $(".product").blur(function(){
            var proName    = $(this).val();
            var productId  = $(this).closest("tr").find("input[name='product_id[]']").val();
            var customerId = $("#ConsignmentCustomerId").val();
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
        
        $(".qty, .unit_price, .total_price").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        $(".qty, .unit_price, .total_price").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qty").keyup(function(){
            var conversion = $(this).closest("tr").find(".conversion").val();
            var qty        = replaceNum($(this).closest("tr").find(".qty").val());
            var totalOrder = replaceNum(converDicemalJS(qty * conversion));
            $(this).closest("tr").find(".totalQtyOrderConsignment").val(totalOrder);
            funcCheckCondiConsignment($(this));
        });
        
        $(".unit_price").keyup(function(){
            funcCheckCondiConsignment($(this));
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
                        $("#searchProductSkuConsignment").select().focus();
                    }
                }else{
                    $(this).select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#searchProductSkuConsignment").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){   
            var productId     = replaceNum($(this).closest("tr").find("input[name='product_id[]']").val());
            if(productId != "" && productId > 0){
                var priceType     = $("#typeOfPriceConsignment").val();
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum($(this).find("option[data-sm='1']").attr("conversion"));
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price-uom-"+priceType));
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(2));
                $(this).closest("tr").find(".conversion").val(conversion);
            }
            funcCheckCondiConsignment($(this));
        });
        
        $(".total_price").keyup(function(){
            var unitPrice     = 0;
            var qty           = replaceNum($(this).closest("tr").find(".qty").val());
            var totalPrice    = replaceNum($(this).val());
            if(qty > 0){
                unitPrice = converDicemalJS(totalPrice / qty);
                $(this).closest("tr").find(".unit_price").val((unitPrice).toFixed(2));
            }else{
                $(this).val(0);
            }
            funcCheckCondiConsignment($(this));
        });

        $(".btnRemoveConsignment").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_REMOVE_ORDER; ?></p>');
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
                        calcTotalAmountConsignment();
                        sortNuTableConsignment();
                        $(this).dialog("close");
                    }
                }
            });
            
        });
        
        $(".noteAddConsignment").click(function(){
            addNoteConsignment($(this));
        });
        
        moveRowConsignment();
    }
    
    function checkEventConsignment(){
        keyEventConsignment();
        $(".tblConsignmentList").unbind("click");
        $(".tblConsignmentList").click(function(){
            keyEventConsignment();
        });
    }
    
    function addProductConsignment(uomSelected, qtyOrder, msg){
        // Product Information
        var productId           = msg.product_id;
        var productSku          = msg.product_code;
        var productUpc          = msg.product_barcode;
        var productCusName      = msg.product_cus_name;
        var productPriceUomId   = msg.product_uom_id;
        var smallUomVal         = msg.small_uom_val;
        var totalQty            = msg.total_qty;
        var tr                  = rowTableConsignment.clone(true);
        var access              = true;
        var branchId            = $("#ConsignmentBranchId").val();
        
        if(access == true){
            rowIndexConsignment = Math.floor((Math.random() * 100000) + 1);
            tr.removeAttr("style").removeAttr("id");
            tr.find("td:eq(0)").html(rowIndexConsignment);
            tr.find("input[name='product_id[]']").attr("id", "product_id_"+rowIndexConsignment).val(productId);
            tr.find("input[name='service_id[]']").attr("id", "service_id_"+rowIndexConsignment);
            tr.find(".lblUPC").val(productUpc);
            tr.find(".lblSKU").val(productSku);
            tr.find("input[name='product[]']").attr("id", "product_"+rowIndexConsignment).val(productCusName).removeAttr('readonly');
            tr.find("input[name='qty[]']").attr("id", "qty_"+rowIndexConsignment).val(qtyOrder).attr('readonly', true);
            tr.find("input[name='small_uom_val[]']").val(smallUomVal);
            tr.find("input[name='note[]']").attr("id", "note_"+rowIndexConsignment);
            tr.find("input[name='conversion[]']").attr("id", "conversion_"+rowIndexConsignment).val(smallUomVal);
            tr.find("select[name='qty_uom_id[]']").attr("id", "qty_uom_id_"+rowIndexConsignment).html('<option value="">Please Select Uom</option>');
            tr.find(".totalQtyOrderConsignment").attr("id", "total_qty_"+rowIndexConsignment).val(smallUomVal);
            tr.find(".totalQtyConsignment").attr("id", "inv_qty_"+rowIndexConsignment).val(totalQty);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/uoms/getRelativeUom/"; ?>"+productPriceUomId+"/all/"+productId+"/"+branchId,
                success: function(msg){
                    timeBarcodeConsignment = 1;
                    var complete = false;
                    tr.find("select[name='qty_uom_id[]']").html(msg).val(1);
                    tr.find("select[name='qty_uom_id[]']").find("option").each(function(){
                        if($(this).attr("conversion") == 1 && uomSelected == ''){
                            $(this).attr("selected", true);
                            // Price
                            var priceType     = $("#typeOfPriceConsignment").val();
                            var lblPrice      = "price-uom-"+priceType;
                            var productPrice  = parseFloat($(this).attr(lblPrice));
                            complete = true;
                        } else{
                            if(parseFloat($(this).val()) == parseFloat(uomSelected)){
                                $(this).attr("selected", true);
                                // Price
                                var priceType     = $("#typeOfPriceConsignment").val();
                                var lblPrice      = "price-uom-"+priceType;
                                var productPrice  = parseFloat($(this).attr(lblPrice));
                                complete = true;
                            }
                        }
                        if(complete == true){
                            var totalPrice = converDicemalJS(productPrice * parseFloat(qtyOrder));
                            tr.find("input[name='unit_price[]']").attr("id", "unit_price_"+rowIndexConsignment).val(productPrice);
                            tr.find("input[name='total_price[]']").attr("id", "total_price_"+rowIndexConsignment).val(totalPrice);
                            // Function Check
                            funcCheckCondiConsignment(tr.find("select[name='qty_uom_id[]']"));
                            checkEventConsignment();
                            tr.find("input[name='qty[]']").removeAttr('readonly');
                            tr.find("input[name='qty[]']").select().focus();
                        }
                        return false;
                    });
                }
            });
            $("#tblConsignment").append(tr);
            sortNuTableConsignment();
        }
    }
    
    function moveRowConsignment(){
        $(".btnMoveDownConsignment, .btnMoveUpConsignment").unbind('click');
        $(".btnMoveDownConsignment").click(function () {
            var rowToMove = $(this).parents('tr.tblConsignmentList:first');
            var next = rowToMove.next('tr.tblConsignmentList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableConsignment();
        });

        $(".btnMoveUpConsignment").click(function () {
            var rowToMove = $(this).parents('tr.tblConsignmentList:first');
            var prev = rowToMove.prev('tr.tblConsignmentList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableConsignment();
        });
        
        sortNuTableConsignment();
    }

    function checkExistingRecordConsignment(productId){
        var isFound = false;
        $("#tblConsignment").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function funcCheckCondiConsignment(current){
        var condition     = true;
        var tr            = current.closest("tr");
        var productId     = tr.find("input[name='product_id[]']").val();
        var qty           = replaceNum(tr.find("input[name='qty[]']").val());
        var qtyOrder      = replaceNum(getTotalQtyOrderConsignment(productId));
        var totalQtyConsignment = replaceNum(tr.find(".totalQtyConsignment").val());
        var unitPrice     = replaceNum(tr.find("input[name='unit_price[]']").val());
        var totalPrice      = 0;
        // Check Product With Qty Order And Total Qty Sale
        if(productId != ""){
            if(qtyOrder > totalQtyConsignment){
                condition = false;
                qty = 0;
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
        // Calculate Total Price
        if(condition == true){
            totalPrice = converDicemalJS(qty * unitPrice);
        }
        // Assign Value to Qty & Total Price
        tr.find("input[name='qty[]']").val(qty);
        tr.find("input[name='total_price[]']").val((totalPrice).toFixed(2));
        // Calculate Total Amount
        calcTotalAmountConsignment();
    }
    
    function calcTotalAmountConsignment(){
        var totalAmount   = 0;
        $(".tblConsignmentList").find(".total_price").each(function(){
            if(replaceNum($(this).val()) != '' || $(this).val() != undefined ){
                totalAmount += replaceNum($(this).val());
            }
        });
        $("#ConsignmentTotalAmount").val((totalAmount).toFixed(2));
    }
    
    function sortNuTableConsignment(){
        var sort = 1;
        $(".tblConsignmentList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOrderConsignment(id){
        var totalProduct=0;
        $("input[name='product_id[]']").each(function(){
            if($(this).val() == id){
                totalProduct += (Number(replaceNum($(this).closest("tr").find(".totalQtyOrderConsignment").val()))>0)?Number(replaceNum($(this).closest("tr").find(".totalQtyOrderConsignment").val())):0;
            }
        });
        return totalProduct;
    }
    
    function addNoteConsignment(currentTr){
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
    
</script>
<div class="inputContainer" style="width:100%" id="searchFormConsignment">
    <table width="100%">
        <tr>
            <td style="width: 300px; text-align: left;">
                <?php echo $this->Form->text('product', array('id' => 'searchProductUpcConsignment', 'style' => 'width:90%; height:15px;', 'placeholder' => TABLE_SCAN_ENTER_UPC)); ?>
            </td>
            <td style="width: 300px; text-align: left;">
            <?php echo $this->Form->text('code', array('id' => 'searchProductSkuConsignment', 'style' => 'width:90%; height: 15px;', 'placeholder' => TABLE_SEARCH_SKU_NAME)); ?>
            </td>
            <td style="width: 20%; text-align: left;" id="divSearchConsignment">
            <img alt="Search" align="absmiddle" style="cursor: pointer;" class="searchProductListConsignment" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
            <td style="text-align:right">
                <div style="width:100%; float: right;">
                    <label for="typeOfPriceConsignment"><?php echo TABLE_PRICE_TYPE; ?> :</label> &nbsp;&nbsp;&nbsp; 
                    <select id="typeOfPriceConsignment" name="data[Consignment][price_type_id]" style="height: 30px; width: 200px">
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
<table id="tblConsignmentHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%">
    <tr>
        <th class="first" style="width:5%;"><?php echo TABLE_NO ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:10%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:23%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:8%;"><?php echo TABLE_QTY ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:11%;"><?php echo SALES_ORDER_UNIT_PRICE ?></th>
        <th style="width:11%;"><?php echo SALES_ORDER_TOTAL_PRICE; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblConsignment" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListConsignment" class="tblConsignmentList" style="visibility: hidden;">
            <td class="first" style="width:5%; text-align: center; padding: 0px;"></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="" /></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="" /></td>
            <td style="width:23%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">
                    <input type="hidden" class="totalQtyConsignment" />
                    <input type="hidden" class="totalQtyOrderConsignment" />
                    <input type="hidden" name="product_id[]" class="product_id" />
                    <input type="hidden" name="service_id[]" />
                    <input type="hidden" name="conversion[]" class="conversion" value="1" />
                    <input type="hidden" name="small_uom_val[]" class="small_uom_val" value="1" />
                    <input type="hidden" name="note[]" id="note" class="note" />
                    <input type="text" id="product" readonly="readonly" name="product[]" class="product validate[required]" style="width: 75%;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:60%;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:15%; padding: 0px; text-align: center">
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
            <td style="width: 11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="unit_price" value="0" name="unit_price[]" style="width:60%;" class="float unit_price validate[required]" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%;">
                    <input type="text" name="total_price[]" value="0" readonly="readonly" style="width:80%" class="total_price float" />
                </div>
            </td>
            <td style="width:7%; white-space: nowrap;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
        $index = 0;
        $dateNow   = date("Y-m-d");
        $dateOrder = $consignment['Consignment']['date'];
        // List Location
        $locCon = '';
        if($locSetting['LocationSetting']['location_status'] == 1){
            $locCon = ' AND is_for_sale = 1';
        }
        if((strtotime($dateOrder) < strtotime($dateNow)) && !empty($consignmentDetails)){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableTmp = "sales_edit_tmp_inventory_".$user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT,
                            `date` date DEFAULT NULL,
                            `product_id` int(11) DEFAULT NULL,
                            `location_group_id` int(11) DEFAULT NULL,
                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                            `total_order` DECIMAL(15,3) NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `product_id` (`product_id`),
                            KEY `location_group_id` (`location_group_id`),
                            KEY `date` (`date`)
                          ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
            // Get Total Qty On Peroid
            $joinProducts = " INNER JOIN products ON";
            $tableDailyBbi = "";
            $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_order + total_cus_consign_out)) AS total_qty, SUM(total_order) AS total_order, product_id";
            $conditionDailyBbi = " products.is_active = 1 AND date <= '".$dateOrder."' AND products.id IN (SELECT product_id FROM consignment_details WHERE consignment_id = ".$consignment['Consignment']['id'].")";
            $groupByDaily = "GROUP BY product_id";
            $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id = '.$consignment['Consignment']['location_group_id'].$locCon.' GROUP BY id');
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
            $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, SUM(total_order) AS total_order, product_id FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id";
            $queryTotal = mysql_query($sqlCmtDailyBiginning);
            while($dataTotal = mysql_fetch_array($queryTotal)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    location_group_id,
                                    total_qty,
                                    total_order
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    " . $consignment['Consignment']['location_group_id'] . ",
                                    " . $dataTotal['qty'] . ",
                                    " . $dataTotal['total_order'] . "
                                )") or die(mysql_error());
            }
        }
        if(!empty($consignmentDetails)){
            foreach($consignmentDetails AS $consignmentDetail){
                $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$consignment['Consignment']['location_group_id']}_group_totals WHERE product_id ={$consignmentDetail['Product']['id']} AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$consignment['Consignment']['location_group_id'].$locCon." GROUP BY id) GROUP BY product_id");
                $rowInv = mysql_fetch_array($sqlInv);
                $sqlInvOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = ".$consignmentDetail['Product']['id']." AND sor.consignment_id = ".$consignment['Consignment']['id']." AND sor.location_group_id = ".$consignment['Consignment']['location_group_id']." GROUP BY sor.product_id");
                $rowInvOrder = mysql_fetch_array($sqlInvOrder);
                $totalInventory = ($rowInv['total_qty'] + $rowInvOrder['total_order']);
                if((strtotime($dateOrder) < strtotime($dateNow)) && !empty($consignmentDetails)){
                    // Get Total Qty Pass
                    $sqlTotalPass   = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE product_id = ".$consignmentDetail['Product']['id']." AND date = '".$dateOrder."' AND location_group_id =".$consignment['Consignment']['location_group_id']);
                    $rowTotalPass   = mysql_fetch_array($sqlTotalPass);
                    /** F-ID: 1100
                    * Compare Total Qty in Pass and Current Date (rowTotalPass = Total Qty In Pass, totalInventory = Total Qty in Current)
                    * IF PASS < CURRENT TotalQty = PASS
                    * ELSE PASS >= CURRENT TotalQty = CURRENT
                    */
                   if($rowTotalPass['total_qty'] + $rowInvOrder['total_order'] < $totalInventory){
                       $totalInventory = $rowTotalPass['total_qty'];
                   }
                }
                $totalQtyConsignment = $totalInventory;
                // Check Name With Customer
                $productName = $consignmentDetail['Product']['name'];
                $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$consignmentDetail['Product']['id']." AND customer_id = ".$consignment['Consignment']['customer_id']." ORDER BY created DESC LIMIT 1");
                if(@mysql_num_rows($sqlProCus)){
                    $rowProCus = mysql_fetch_array($sqlProCus);
                    $productName = $rowProCus['name'];
                }
        ?>
        <tr class="tblConsignmentList">
            <td class="first" style="width:5%; text-align: center; padding: 0px;"><?php echo ++$index; ?></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="<?php echo $consignmentDetail['Product']['barcode']; ?>" /></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="<?php echo $consignmentDetail['Product']['code']; ?>" /></td>
            <td style="width:23%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">
                    <input type="hidden" class="totalQtyConsignment" value="<?php echo $totalQtyConsignment; ?>" />
                    <input type="hidden" class="totalQtyOrderConsignment" value="<?php echo $consignmentDetail['ConsignmentDetail']['qty'] * $consignmentDetail['ConsignmentDetail']['conversion']; ?>" />
                    <input type="hidden" name="product_id[]" value="<?php echo $consignmentDetail['Product']['id']; ?>" class="product_id" />
                    <input type="hidden" name="conversion[]" class="conversion" value="<?php echo $consignmentDetail['ConsignmentDetail']['conversion']; ?>" />
                    <input type="hidden" name="small_uom_val[]" class="small_uom_val" value="<?php echo $consignmentDetail['Product']['small_val_uom']; ?>" />
                    <input type="hidden" name="note[]" id="note" class="note" value="<?php echo $consignmentDetail['ConsignmentDetail']['note']; ?>" />
                    <input type="text" id="product_<?php echo $index; ?>" name="product[]" value="<?php echo str_replace('"', '&quot;', $productName); ?>" class="product validate[required]" style="width: 75%;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" name="qty[]" value="<?php echo number_format($consignmentDetail['ConsignmentDetail']['qty'], 0); ?>" style="width:60%;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:15%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id_<?php echo $index; ?>" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php
                        $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$consignmentDetail['Product']['price_uom_id']."
                                            UNION
                                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$consignmentDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$consignmentDetail['Product']['price_uom_id'].")
                                            ORDER BY conversion ASC");
                        $i = 1;
                        $length = mysql_num_rows($query);
                        while($data=mysql_fetch_array($query)){
                            $selected = "";
                            $priceLbl = "";
                            $costLbl  = "";
                            if($data['id'] == $consignmentDetail['ConsignmentDetail']['qty_uom_id']){   
                                $selected = ' selected="selected" ';
                            }
                            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$consignmentDetail['Product']['id']." AND product_prices.uom_id =".$data['id']);
                            if(mysql_num_rows($sqlPrice)){
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
                                }
                            }else{
                                $priceLbl .= 'price-uom-1="0" price-uom-2="0"';
                                $costLbl  .= 'cost-uom-1="0" cost-uom-2="0"';
                            }
                        ?>
                        <option <?php echo $costLbl; ?> <?php echo $priceLbl; ?> <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $consignmentDetail['Product']['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                        <?php 
                        $i++;
                        } 
                        ?>
                    </select>
                </div>
            </td>
            <td style="width: 11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="unit_price_<?php echo $index; ?>" name="unit_price[]" value="<?php echo number_format($consignmentDetail['ConsignmentDetail']['unit_price'], 2); ?>" style="width:60%;" class="float unit_price validate[required]" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%;">
                    <input type="text" id="total_price_<?php echo $index; ?>" name="total_price[]" value="<?php echo number_format($consignmentDetail['ConsignmentDetail']['total_price'], 2); ?>" readonly="readonly" style="width:80%" class="total_price float" />
                </div>
            </td>
            <td style="width:7%; white-space: nowrap;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
            }
            if(strtotime($dateOrder) < strtotime($dateNow)){
                // DROP Tmp Sale Table
                mysql_query("DROP TABLE `".$tableTmp."`;");
            }
        }
        ?>
    </table>
</div>