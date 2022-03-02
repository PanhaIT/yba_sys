<?php 
include("includes/function.php");
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    var indexRowRs   = 0;
    var cloneRowRs   = $("#detailRequestStock");
    var timeCodeRs   = 1;
    var fieldRequireReqStock = ['RequestStockFromLocationGroupId', 'RequestStockToLocationGroupId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#RequestStockBranchId").filterOptions('com', '<?php echo $this->data['RequestStock']['company_id']; ?>', '<?php echo $this->data['RequestStock']['branch_id']; ?>');
        $("#RequestStockFromLocationGroupId, #RequestStockToLocationGroupId").chosen();
        // Remove Clone Row List
        $("#detailRequestStock").remove();
        
        var waitForFinalEventRStock = (function () {
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
        if(tabRStockReg != tabRStockId){
            $("a[href='"+tabRStockId+"']").click(function(){
                if($("#bodyListRequestStock").html() != '' && $("#bodyListRequestStock").html() != null){
                    waitForFinalEventRStock(function(){
                        refreshScreenRs();
                        resizeFormTitleRs();
                        resizeFornScrollRs();  
                    }, 500, "Finish");
                }
            });
            tabRStockReg = tabRStockId;
        }

        waitForFinalEventRStock(function(){
              refreshScreenRs();
              resizeFormTitleRs();
              resizeFornScrollRs();  
            }, 500, "Finish");
            
        $(window).resize(function(){
            if(tabRStockReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventRStock(function(){
                    refreshScreenRs();
                    resizeFormTitleRs();
                    resizeFornScrollRs();  
                  }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderRequestStock").click(function(){
            var RequestStockCompanyId           = $("#RequestStockCompanyId").val();
            var RequestStockBranchId            = $("#RequestStockCompanyId").val();
            var RequestStockDate                = $("#RequestStockDate").val();
            var RequestStockFromLocationGroupId = $("#RequestStockFromLocationGroupId").val();
            var RequestStockCode                = $("#RequestStockCode").val();
            var RequestStockToLocationGroupId   = $("#RequestStockToLocationGroupId").val();
            
            if(RequestStockCompanyId == "" || RequestStockBranchId == "" || RequestStockDate == "" || RequestStockFromLocationGroupId == "" || RequestStockCode == "" || RequestStockToLocationGroupId == ""){
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
                    $("#RequestStockTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#RequestStockTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabRStockReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventRStock(function(){
                        resizeFornScrollRs();
                    }, 300, "Finish");
                }
            }
        });
        
        // Form Validate
        $("#RequestStockEditForm").validationEngine('detach');
        $("#RequestStockEditForm").validationEngine('attach');
        
        $(".btnSaveRequestStock").click(function(){
            if(checkBfSaveRs() == true){
                return true;
            }else{
                return false;
            }
        });
        
        $("#RequestStockEditForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveRequestStock").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequireReqStock) == false){
                    alertSelectRequireField();
                    $(".btnSaveRequestStock").removeAttr('disabled');
                    return false;
                }
                $("#RequestStockDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".qty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Request Stock', 'Edit', 2, result.responseText);
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
                    close: function(){
                        $(this).dialog({close: function(){}});
                        $(this).dialog("close");
                        $(".btnBackSalesOrder").dblclick();
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
                if(result.error == 0){
                    createSysAct('Request Stock', 'Edit', 1, '');
                    backRequestStock();
                    var id = result.id;
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceReqST" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_REQUEST_STOCK; ?></span></button></div> ');
                    $(".printInvoiceReqST").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
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
                        close: function(){
                            $(this).dialog({close: function(){}});
                            $(this).dialog("close");
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == 1){
                    backRequestStock();
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED ?></p>');
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
                }else if(result.error == 2){
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM ?></p>');
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
        
        $("#RequestStockDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $(".btnBackRequestStock").click(function(event){
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
                        backRequestStock();
                    }
                }
            });
        });
        
        // Action Scan/Search Product
        $("#requestStockSearchSKU").autocomplete("<?php echo $this->base . "/request_stocks/searchProduct/"; ?>", {
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
            if(timeCodeRs == 1 && $("#RequestStockCompanyId").val() != ""){
                timeCodeRs = 2;
                serachRSProductCode(code, '#requestStockSearchSKU', 1);
            }
        });
        
        $("#requestStockSearchProduct").click(function(){
            searchProductRS();
        });
        
        $("#requestStockSearchUPC").focus(function(){
            $(".btnSaveRequestStock").attr('disabled','disabled');
        });
        
        $("#requestStockSearchUPC").blur(function(){
            $(".btnSaveRequestStock").removeAttr('disabled');
        });
        
        $("#requestStockSearchUPC").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(timeCodeRs == 1 && $("#RequestStockCompanyId").val() != ""){
                    timeCodeRs = 2;
                    serachRSProductCode($(this).val(), '#requestStockSearchUPC', 2);
                }
                return false;
            }
        });

        $("#requestStockSearchSKU").keypress(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13){
                if($(this).val() != ""){
                    if(timeCodeRs == 1 && $("#RequestStockCompanyId").val() != ""){
                        timeCodeRs = 2;
                        serachRSProductCode($(this).val(), '#requestStockSearchSKU', 1);
                    }
                }
                return false;
            }
        });
        
        // Company Action
        $.cookie('companyIdRequestStock', $("#RequestStockCompanyId").val(), { expires: 7, path: "/" });
        $("#RequestStockCompanyId").change(function(){
            var obj   = $(this);
            if($(".listBodyRequestStock").find(".product_id").val() == undefined){
                $.cookie('companyIdRequestStock', obj.val(), { expires: 7, path: "/" });
                $("#RequestStockBranchId").filterOptions('com', obj.val(), '');
                $("#RequestStockBranchId").change();
                changeInputCSSRequestStock();
            } else {
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
                        $(".ui-dialog-buttonpane").show(); 
                        $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('companyIdRequestStock', obj.val(), { expires: 7, path: "/" });
                            $("#RequestStockBranchId").filterOptions('com', obj.val(), '');
                            $("#RequestStockBranchId").change();
                            // Reload Page
                            $("#tblRequestStock").html('');
                            changeInputCSSRequestStock();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#RequestStockCompanyId").val($.cookie('companyIdRequestStock'));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdRequestStock', $("#RequestStockBranchId").val(), { expires: 7, path: "/" });
        $("#RequestStockBranchId").change(function(){
            var obj = $(this);
            if($(".listBodyRequestStock").find(".product_id").val() == undefined){
                $.cookie('branchIdRequestStock', obj.val(), { expires: 7, path: "/" });
                branchChangeRequestStock(obj);
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
                            $.cookie('branchIdRequestStock', obj.val(), { expires: 7, path: "/" });
                            branchChangeRequestStock(obj);
                            // Reload Page
                            $("#tblRequestStock").html('');
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#RequestStockBranchId").val($.cookie("branchIdRequestStock"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Event Key 
        checkEventReqStock();
        changeInputCSSRequestStock();
    });
    
    function branchChangeRequestStock(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        $("#RequestStockCode").val("<?php echo date("y"); ?>"+mCode);
    }
    
    function resizeFormTitleRs(){
        var screen = 16;
        var widthList = $("#bodyListRequestStock").width();
        $("#tblRequestStockHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblRequestStockHeader").css('padding','0px');
        $("#tblRequestStockHeader").css('margin-top','5px');
        $("#tblRequestStockHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollRs(){
        var tabHeight = $(tabRStockReg).height();
        var formHeader = 0;
        if ($('#RequestStockTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#RequestStockTop").height();
        }
        var btnHeader   = $("#btnHideShowHeaderRequestStock").height();
        var formFooter  = $("#requestStockFooter").height();
        var formSearch  = $("#searchFormRS").height();
        var tableHeader = $("#tblRequestStockHeader").height();
        var getHeight   = tabHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter);
        $("#bodyListRequestStock").css('height',getHeight);
        $("#bodyListRequestStock").css('padding','0px');
        $("#bodyListRequestStock").css('width','100%');
        $("#bodyListRequestStock").css('overflow-x','hidden');
        $("#bodyListRequestStock").css('overflow-y','scroll');
    }
    
    function refreshScreenRs(){
        $("#tblRequestStockHeader").removeAttr('style');
    }
    
    function checkBfSaveRs(){
        var formName     = "#RequestStockEditForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if($(".listBodyRequestStock").find(".product_id").val() == undefined){
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
                return false;
            }else{
                return true;
            }
        }
    }
    
    function serachRSProductCode(code, field, search){
        if($("#RequestStockCompanyId").val() != "" && $("#RequestStockBranchId").val() != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/request_stocks/searchProductCode/"; ?>"+$("#RequestStockCompanyId").val()+"/"+$("#RequestStockBranchId").val()+"/"+code,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $(".btnSaveRequestStock").removeAttr('disabled');
                    $(field).val('');
                    timeCodeRs = 1;
                    if(msg == 1){
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
                        // Add Products
                        var products  = $.parseJSON(msg);
                        var productId = products.Product.id;
                        var sku  = products.Product.code;
                        var upc  = products.Product.barcode;
                        var name = products.Product.name;
                        var uom_id = products.Product.price_uom_id;
                        addProductReqStock(productId, sku, upc, name, uom_id);
                    }
                }
            });
        }else{
            timeCodeRs = 1;
        }
    }
    
    function searchProductRS(){
        if($("#RequestStockCompanyId").val() != "" && $("#RequestStockBranchId").val() != ""){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/request_stocks/product/"; ?>"+$("#RequestStockCompanyId").val()+"/"+$("#RequestStockBranchId").val(),
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
                                var data = $("input[name='chkProduct']:checked");
                                if(data){
                                    var productId = $("input[name='chkProduct']:checked").val();
                                    var sku  = $("input[name='chkProduct']:checked").attr("sku");
                                    var upc  = $("input[name='chkProduct']:checked").attr("upc");
                                    var name = $("input[name='chkProduct']:checked").attr("label");
                                    var uom_id = $("input[name='chkProduct']:checked").attr("uom-id");
                                    addProductReqStock(productId, sku, upc, name, uom_id);
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function addProductReqStock(productId, sku, upc, name, uom_id){        
        // Get Index Row
        indexRowRs = Math.floor((Math.random() * 100000) + 1);
        
        var tr = cloneRowRs.clone(true);        
        tr.removeAttr("style").removeAttr("id");          
        tr.find("td:eq(0)").html('');
        tr.find("td .requestStockSKU").html(sku);
        tr.find("td .requestStockPUC").html(upc);
        tr.find("td .product_id").attr("id", "product_id"+indexRowRs).val(productId);
        tr.find("td .product_name").attr("id", "product_name"+indexRowRs).val(name);                   
        tr.find("td .qty_uom_id").attr("id", "qty_uom_id"+indexRowRs).html("<option value=''></option>");
        tr.find("td .qty").attr("id", "qty_"+indexRowRs).val(0);
        // Get UOM
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+uom_id,
            data: "",
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },                                                               
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                tr.find("td .qty_uom_id").html(msg);
                var conversion = tr.find(".qty_uom_id").find("option[data-sm='1']").attr("conversion");
                tr.find(".conversion").val(conversion);
            }
        });
        
        $("#tblRequestStock").append(tr);
        tr.find("td .qty").select().focus();
        
        timeCodeRs = 1;
        setIndexRowRS();
        checkEventReqStock();
    }
    
    function setIndexRowRS(){
        var sort = 1;
        $(".listBodyRequestStock").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function eventKeyRowRS(){
        loadAutoCompleteOff();
        $(".qty, .qty_uom_id, .btnRemoveRequestStock").unbind('keypress').unbind('keyup').unbind('change').unbind('click');
        $(".qty").autoNumeric({mDec: 0, aSep: ','});
        
        $(".qty_uom_id").change(function(){
            var smallUomVal = replaceNum($(this).find("option[data-sm='1']").attr('conversion'));
            var uomCon      = replaceNum($(this).find("option:selected").attr('conversion'));
            var conversion  = converDicemalJS(smallUomVal / uomCon);
            $(this).closest("tr").find(".conversion").val(conversion);
        });
        
        $(".btnRemoveRequestStock").click(function(){
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
                        setIndexRowRS();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function checkEventReqStock(){
        eventKeyRowRS();
        $(".listBodyRequestStock").unbind("click");
        $(".listBodyRequestStock").click(function(){
            eventKeyRowRS();
        });
    }
    
    function backRequestStock(){
        $('#RequestStockEditForm').validationEngine('hideAll');
        oCache.iCacheLower = -1;
        oTableRequestStock.fnDraw(false);
        var rightPanel = $(".btnBackRequestStock").parent().parent().parent().parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        rightPanel.hide();rightPanel.html("");
        leftPanel.show("slide", { direction: "left" }, 500);
    }
    
    function changeInputCSSRequestStock(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var disabled  = true;
        $("#requestStockSearchProduct").hide();
        if($("#RequestStockCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            disabled  = false;
            $("#requestStockSearchProduct").show();
        }   
        // Label
        $("#RequestStockEditForm").find("label").removeAttr("class");
        $("#RequestStockEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'RequestStockCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#RequestStockEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#RequestStockEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'RequestStockCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
    }
    
</script>
<?php echo $this->Form->create('RequestStock'); ?>
<?php echo $this->Form->hidden('id', array('name' => 'data[id]')); ?>
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderRequestStock">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<div id="RequestStockTop">
    <fieldset>
        <legend><?php __(MENU_REQUEST_STOCK_INFO); ?></legend>
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%;"><label for="RequestStockCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                <td style="width: 45%;">
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[RequestStock][company_id]" id="RequestStockCompanyId" class="validate[required]" style="width: 55%;">
                            <?php
                            if(count($companies) != 1){
                            ?>
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($companies AS $company){
                            ?>
                            <option <?php if($company['Company']['id'] == $this->data['RequestStock']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 15%;"><label for="RequestStockCode"><?php echo TABLE_REQUEST_STOCK_NUMBER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('code', array('class'=>'validate[required]', 'style' => 'width: 50%;', 'readonly' => true)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="RequestStockBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[RequestStock][branch_id]" id="RequestStockBranchId" class="validate[required]" style="width: 55%;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option <?php if($branch['Branch']['id'] == $this->data['RequestStock']['branch_id']){ ?>selected="selected"<?php } ?> value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['request_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td><label for="RequestStockFromLocationGroupId"><?php echo TABLE_FROM_WAREHOUSE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('from_location_group_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 300px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="RequestStockDate"><?php echo TABLE_REQUEST_STOCK_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('date', array('value' => dateShort($this->data['RequestStock']['date']), 'class'=>'validate[required]', 'style' => 'width: 70%;')); ?>
                    </div>
                </td>
                <td><label for="RequestStockToLocationGroupId"><?php echo TABLE_TO_WAREHOUSE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('to_location_group_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 300px;')); ?>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="inputContainer" style="width:100%" id="searchFormRS">
    <table width="100%">
        <tr>
            <td style="width: 300px;">
                <input type="text" id="requestStockSearchUPC" style="width: 90%; height: 15px;" placeholder="<?php echo TABLE_SCAN_ENTER_UPC; ?>" />
            </td>
            <td style="width: 300px;">
                <input type="text" id="requestStockSearchSKU" style="width: 90%; height: 15px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
            </td>
            <td>
                <img alt="Search" align="absmiddle" style="cursor: pointer;" id="requestStockSearchProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
        </tr>
    </table>
</div>
<table id="tblRequestStockHeader" class="table" cellspacing="0" style="padding:0px; width:99%;">
    <tr>
        <th class="first" style="width:8%"><?php echo TABLE_NO; ?></th>
        <th style="width:15%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:15%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:25%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:15%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyListRequestStock">
    <table id="tblRequestStock" class="table" cellspacing="0" style="padding:0px;">
        <tr id="detailRequestStock" class="listBodyRequestStock" style="visibility: hidden;">
            <td class="first" style="width:8%"></td>
            <td style="width:15%"><span class="requestStockPUC"></span></td>
            <td style="width:15%"><span class="requestStockSKU"></span></td>
            <td style="width:25%">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" id="product_id" />
                    <input type="text" class="product_name validate[required]" id="product_name" readonly="readonly" style="width: 90%" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:15%">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:80%;" class="qty validate[required,min[1]]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:15%">
                <div class="inputContainer" style="width:100%"> 
                    <input type="hidden" name="conversion[]" class="conversion" value="1" />
                    <select id="qty_uom_id" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id validate[required]"></select>
                </div>
            </td>
            <td style="white-space:nowrap; padding:0px; text-align:center; width:7%">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRequestStock" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
        if(!empty($requestStockDetails)){
            $index = 1;
            foreach($requestStockDetails AS $requestStockDetail){
        ?>
        <tr class="listBodyRequestStock">
            <td class="first" style="width:8%"><?php echo $index; ?></td>
            <td style="width:15%"><span class="requestStockPUC"><?php echo $requestStockDetail['Product']['barcode']; ?></span></td>
            <td style="width:15%"><span class="requestStockSKU"><?php echo $requestStockDetail['Product']['code']; ?></span></td>
            <td style="width:25%">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" id="product_id_<?php echo $index; ?>" value="<?php echo $requestStockDetail['Product']['id']; ?>" />
                    <input type="text" class="product_name validate[required]" id="product_name_<?php echo $index; ?>" value="<?php echo str_replace('"', '&quot;', $requestStockDetail['Product']['name']); ?>" readonly="readonly" style="width: 90%" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:15%">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" name="qty[]" value="<?php echo $requestStockDetail['RequestStockDetail']['qty']; ?>" style="width:80%;" class="qty validate[required,min[1]]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:15%">
                <div class="inputContainer" style="width:100%"> 
                    <?php
                    $options = "";
                    // Get UOM List
                    $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$requestStockDetail['Product']['price_uom_id']."
                                        UNION
                                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$requestStockDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$requestStockDetail['Product']['price_uom_id'].")
                                        ORDER BY conversion ASC");
                    $i = 1;
                    $length = mysql_num_rows($query);
                    while($data=mysql_fetch_array($query)){
                        $selected = "";
                        $isMain   = "other";
                        if($data['id'] == $requestStockDetail['RequestStockDetail']['qty_uom_id']){   
                            $selected = ' selected="selected" ';
                        }
                        if($data['id'] == $requestStockDetail['Product']['price_uom_id']){
                            $isMain = "first";
                        }
                        if($length == $i){
                            $dataSm = 1;
                        }else{
                            $dataSm = 0;
                        }
                        $options .= '<option '.$selected.' data-sm="'.$dataSm.'" data-item="'.$isMain.'" conversion="'.$data['conversion'].'" value="'.$data['id'].'">'.$data['name'].'</option>';
                        $i++;
                    }
                    ?>
                    <input type="hidden" name="conversion[]" value="<?php echo $requestStockDetail['RequestStockDetail']['conversion']; ?>" class="conversion" value="1" />
                    <select id="qty_uom_id_<?php echo $index; ?>" name="qty_uom_id[]" style="width:80%; height: 20px;" class="qty_uom_id validate[required]"><?php echo $options; ?></select>
                </div>
            </td>
            <td style="white-space:nowrap; padding:0px; text-align:center; width:7%">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveRequestStock" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
                $index++;
            }
        }
        ?>
    </table>
</div>
<div id="requestStockFooter">
    <div class="buttons">
        <a href="#" class="positive btnBackRequestStock">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveRequestStock">
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSaveRequestStock"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>