<?php
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowAddProduct = checkAccess($user['User']['id'], 'products', 'quickAdd');
?>
<script type="text/javascript">
    var tblRowInventoryPhysical =  $("#OrderListInventoryPhysical");
    var searchAction = 0;
    var rowIndexInventoryPhysical;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Table List Row
	$("#OrderListInventoryPhysical").remove();
        // Variable Delay Time
        var waitForFinalEventInventoryPhysical = (function () {
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
        if(tabInventoryPhysicalReg != tabInventoryPhysicalId){
            $("a[href='"+tabInventoryPhysicalId+"']").click(function(){
                if($("#bodyListInventoryPhysical").html() != '' && $("#bodyListInventoryPhysical").html() != null){
                    waitForFinalEventInventoryPhysical(function(){
                        refreshScreenInventoryPhysical();
                        resizeFormTitleInventoryPhysical();
                        resizeFormScrollInventoryPhysical();
                    }, 500, "Finish");
                }
            });
            tabInventoryPhysicalReg = tabInventoryPhysicalId;
        }
        
        // Calculate Form Table List
        waitForFinalEventInventoryPhysical(function(){
            refreshScreenInventoryPhysical();
            resizeFormTitleInventoryPhysical();
            resizeFormScrollInventoryPhysical();
        }, 500, "Finish");
        
        // Hide / Show Header
        $("#btnHideShowHeaderInventoryPhysical").click(function(){
            var InventoryPhysicalCompanyId       = $("#InventoryPhysicalCompanyId").val();
            var InventoryPhysicalDate            = $("#InventoryPhysicalDate").val();
            var InventoryPhysicalLocationGroupId = $("#InventoryPhysicalLocationGroupId").val();
            
            if(InventoryPhysicalCompanyId == "" || InventoryPhysicalDate == "" || InventoryPhysicalLocationGroupId == ""){
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
                    $("#topInventoryPhysical").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#topInventoryPhysical").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabInventoryPhysicalReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventInventoryPhysical(function(){
                        resizeFormScrollInventoryPhysical();
                    }, 500, "Finish");
                }
            }
        });
        
        // Action Form Submit
        $(".saveInventoryPhysical").click(function(){
            if(checkExistBeforeSaveInventoryPhysical() == true){
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
                        '<?php echo ACTION_SAVE; ?>': function() {
                            // Set Save Status
                            $("#saveExitInventoryPhysical").val(0);
                            // Action Click Save
                            $("#InventoryPhysicalForm").submit();
                            disableEnableSave(true);
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_SAVE_EXIT; ?>': function() {
                            // Set Save Status
                            $("#saveExitInventoryPhysical").val(1);
                            // Action Click Save
                            $("#InventoryPhysicalForm").submit();
                            disableEnableSave(true);
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
                return false;
            }
        });
        
        // Action Form Save
        $("#InventoryPhysicalForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequireInventoryPhysical) == false){
                    alertSelectRequireField();
                    $(".saveInventory").removeAttr('disabled');
                    return false;
                }
                // Change Format Date
                $("#InventoryPhysicalDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".expired_date").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                // Assign Label Loading
                $(".txtSaveInventoryPhysical").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                // Change Format Date
                $("#InventoryPhysicalDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".expired_date").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveInventoryPhysical").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                
                if(result == 'duplicate'){
                    disableEnableSave(false);
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>This code is already taken, please change the code and save again.</p>');
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
                }else if(result == 'error'){
                    var saveStatus = $("#saveExitInventoryPhysical").val();
                    if(saveStatus == 1){
                        backInventoryPhysical();
                    } else {
                        saveContinueInventoryPhysical();
                    }
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
                }else{
                    var saveStatus = $("#saveExitInventoryPhysical").val();
                    if(saveStatus == 1){
                        backInventoryPhysical();
                    } else {
                        saveContinueInventoryPhysical();
                    }
                    if(result.length > 20){
                        createSysAct('Inv InventoryPhysical', 'Add', 2, result);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    }else {
                        createSysAct('Inv InventoryPhysical', 'Add', 1, '');
                        // alert message
                        $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceInventoryPhysical"><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_PRINT_SALES_MIX; ?></span></button>');
                        $(".printInvoiceInventoryPhysical").click(function(){
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base . '/'; ?>inventory_physicals/printInvoice/"+result,
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },
                                success: function(printInvoiceResult){
                                    w=window.open();
                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                    w.document.write(printInvoiceResult);
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
                        closeOnEscape: false,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        close: function(event, ui){
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
        
        // Search Product SKU Auto Complete
        $("#InventoryPhysicalSKU").autocomplete("<?php echo $this->base . "/inventory_physicals/searchProduct/"; ?>", {
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
            if(searchAction == 0){
                searchAction = 1;
                searchProductInventoryPhysical(1, code);
            }
        });
        
        // Search Product SKU
        $("#InventoryPhysicalSKU").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(searchAction == 0){
                    searchAction = 1;
                    searchProductInventoryPhysical(1, $(this).val());
                }
                return false;
            }
        });
        
        // Action Input SKU & PUC Focus
        $("#InventoryPhysicalSKU").focus(function(){
            var companyId     = $("#InventoryPhysicalCompanyId").val();
            var branchId      = $("#InventoryPhysicalBranchId").val();
            var chartAccount  = $("#InventoryPhysicalChartAccountId").val();
            var locationGroup = $("#InventoryPhysicalLocationGroupId").val();
            var date          = $("#InventoryPhysicalDate").val();
            if(locationGroup == "" || date == "" || chartAccount == "" || companyId == "" || branchId == ""){
                // Alert Confirm Box
                alertWarningConfirmInventoryPhysical();
            }
        });
        
        // Search Product List
        $(".searchProductInventoryPhysical").click(function(e){
            if(searchAction == 0){
                searchAction = 1;
                searchProductInventoryPhysicalList();
            }
        });
        
        <?php
        if($allowAddProduct){
        ?>
        $("#addProductInventoryPhysical").click(function(){
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
        changeInputCSSInventoryPhysical();
    });
    
    // Resize Form Tittle Header
    function resizeFormTitleInventoryPhysical(){
        var screen = 16;
        var widthList = $("#bodyListInventoryPhysical").width();
        $("#tblInventoryPhysicalHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblInventoryPhysicalHeader").css('padding','0px');
        $("#tblInventoryPhysicalHeader").css('margin-top','5px');
        $("#tblInventoryPhysicalHeader").css('width',widthTitle);
    }
    
    // Resize Form Scroll Body
    function resizeFormScrollInventoryPhysical(){
        // Show Button Footer
        $(".tblInventoryPhysicalFooter").show();
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#topInventoryPhysical').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#topInventoryPhysical").height();
        }
        var btnHeader   = $("#btnHideShowHeaderInventoryPhysical").height();
        var formFooter  = $(".tblInventoryPhysicalFooter").height();
        var formSearch  = $("#searchFormInventoryPhysical").height();
        var tableHeader = $("#tblInventoryPhysicalHeader").height();
        var spaceRemain = 240;
        var getHeight   = windowHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter + spaceRemain);
        $("#bodyListInventoryPhysical").css('height',getHeight);
        $("#bodyListInventoryPhysical").css('padding','0px');
        $("#bodyListInventoryPhysical").css('width','100%');
        $("#bodyListInventoryPhysical").css('overflow-x','hidden');
        $("#bodyListInventoryPhysical").css('overflow-y','scroll');
    }
    
    // Refresh Screen Header
    function refreshScreenInventoryPhysical(){
        $("#tblInventoryPhysicalHeader").removeAttr('style');
    }
    
    // Search Product
    function searchProductInventoryPhysical(type, code){
        var companyId     = $("#InventoryPhysicalCompanyId").val();
        var branchId      = $("#InventoryPhysicalBranchId").val();
        var locationGroup = $("#InventoryPhysicalLocationGroupId").val();
        var date          = $("#InventoryPhysicalDate").val();
        var url           = '';
        $("#InventoryPhysicalSKU").val("");
        if(locationGroup != '' && date != '' && companyId != '' && branchId != ''){
            if(type == 1){
                url = "searchProductSku";
            }else{
                url = "searchProductPuc";
            }
            $.ajax({
                dataType: 'json',
                type:   "POST",
                url:    "<?php echo $this->base . "/inventory_physicals/"; ?>"+url+"/"+companyId+"/"+branchId+"/"+encodeURIComponent(code),
                data:   "location_group_id="+locationGroup,
                beforeSend: function(arr, $form, options) {
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    searchAction = 0;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    // Check Empty Object in JSON
                    if(jQuery.isEmptyObject(result)){
                        // Alert Confirm Code Invalid
                        alertConfirmValidCode();
                    }else{
                        // Add Product To List
                        addProductInventoryPhysical(result);
                    }
                }
            });
        }else{
            // Reset Search Action 
            searchAction = 0;
            // Alert Confirm Box
            alertWarningConfirmInventoryPhysical();
        }
    }
    
    // Search Product List
    function searchProductInventoryPhysicalList(){
        var companyId     = $("#InventoryPhysicalCompanyId").val();
        var branchId      = $("#InventoryPhysicalBranchId").val();
        var locationGroup = $("#InventoryPhysicalLocationGroupId").val();
        var date          = $("#InventoryPhysicalDate").val();
        if(locationGroup != '' && date != '' && companyId != '' && branchId != ''){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/inventory_physicals/product/"; ?>"+companyId+"/"+branchId+"/"+locationGroup,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    searchAction = 0;
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
                                if($("input[name='chkProductInventoryPhysical']:checked").val()){
                                    var code = $("input[name='chkProductInventoryPhysical']:checked").val();
                                    searchProductInventoryPhysical(1, code);
                                }
                            }
                        }
                    });
                }
            });
        }else{
            // Reset Search Action 
            searchAction = 0;
            // Alert Confirm Box
            alertWarningConfirmInventoryPhysical();
        }
    }
    
    // Add Product To Table List
    function addProductInventoryPhysical(result){
        // Get Row Index
        rowIndexInventoryPhysical = Math.floor((Math.random() * 100000) + 1);
        if(checkExistingProductInventoryPhysical(result.Product.id, '0', '0000-00-00') == false){
            // Product Information
            var productId      = result.Product.id;
            var productName    = result.Product.name;
            var productPUC     = result.Product.barcode;
            var smallUomVal    = result.Product.small_val_uom;
            var smallUomLabel  = result.Product.small_uom_label;
            var mainUomVal     = result.Product.price_uom_id;
            var isExpiredDate  = result.Product.is_expired_date;
            var isLots         = result.Product.is_lots;
            var mainUomName    = result.Uom.name;
            var mainUomAbbr    = result.Uom.abbr;
            var tr             = tblRowInventoryPhysical.clone(true);
            // Remove Row Attr ID
            tr.removeAttr("style").removeAttr("id");
            // Assign Value to INPUT or LABEL
            tr.find("td:eq(0)").html('');
            tr.find("input[name='product_id[]']").val(productId);
            tr.find(".product_name").val(productName);
            tr.find(".PUCLabel").val(productPUC);
            tr.find(".uom_label").html(mainUomName);
            tr.find(".uomMainId").val(mainUomVal);
            tr.find(".uomMainLabel").val(mainUomAbbr);
            tr.find(".smallUomInventoryPhysical").val(smallUomVal);
            tr.find(".smallUomLabelInventoryPhysical").val(smallUomLabel);
            // Assign ID
            tr.find(".product_name").attr("id", "productname_"+rowIndexInventoryPhysical);
            tr.find("input[name='lots_number[]']").attr("id", "lots_number_"+rowIndexInventoryPhysical).val(0);
            tr.find("input[name='expired_date[]']").attr("id", "expired_date_"+rowIndexInventoryPhysical);
            tr.find("select[name='location_id[]']").attr("id", "location_id_"+rowIndexInventoryPhysical);
            tr.find(".InventoryPhysicalQtyDifference").attr("id", "InventoryPhysicalQtyDifference_"+rowIndexInventoryPhysical);
            tr.find(".qtyOnHandLabel").attr("id", "qtyOnHandLabel_"+rowIndexInventoryPhysical);
            // Assign Validate Expired Date
            if(isExpiredDate == 1){
                tr.find("input[name='expired_date[]']").addClass("validate[required]").val('');
            } else {
                tr.find("input[name='expired_date[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0000-00-00');
            }
            // Assign Validate Lots
            if(isLots == 1){
                tr.find("input[name='lots_number[]']").addClass("validate[required]").val('');
            } else {
                tr.find("input[name='lots_number[]']").removeClass("validate[required]").css('visibility', 'hidden').val('0');
            }
            // Get Location
            getLocationInventoryPhysical(tr, result);
            // Append to Table List
            $("#tblInventoryPhysical").append(tr);
            setIndexRowInventoryPhysical();
            // Event Key Table List
            checkEventInventoryPhysical();
        }
    }
    
    function alertWarningConfirmInventoryPhysical(){
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
    }
    
    function checkExistingProductInventoryPhysical(productId, lotsNo, expiredDate){
        var isFound = false;
        $("#tblInventoryPhysical").find("tr").each(function(){
            var compareProId   = $(this).find("input[name='product_id[]']").val();
            var compareLotNo   = $(this).find("input[name='lots_number[]']").val();
            var compareExpDate = $(this).find("input[name='expired_date[]']").val();
            if(productId == compareProId && lotsNo == compareLotNo && expiredDate == compareExpDate){
                isFound = true;
            }
        });
        return isFound;
    }
    
    function getLocationInventoryPhysical(obj, result){
        var options = "";
        // Check Empty Object in JSON
        if(jQuery.isEmptyObject(result)){
            options += '<option value=""><?php echo GENERAL_NO_RECORD; ?></option>';
        }else{
            options += '<option value=""><?php echo INPUT_SELECT; ?></option>';
            $.each( result.Location, function( key, value ) {
                var location = value.toString().split("--");
                var id       = location[0];
                var name     = location[1];
                options += '<option value="'+id+'">'+name+'</option>';
            });
        }
        obj.find(".location_id").html(options);
    }
    
    function eventKeyInventoryPhysical(){
        $(".lots_number, .location_id, .InventoryPhysicalQtyDifference, .btnRemoveInventoryPhysical").unbind("click").unbind("change").unbind("blur").unbind("focus").unbind("keyup").unbind("keypress");
        // Action Lots Number Focus
        $(".lots_number").blur(function(){
            getTotalQtyOnHand($(this));
        });
        
        // Action Date Expired
        $(".expired_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            onSelect: function(dateText, inst) {
                getTotalQtyOnHand($(this));
            }
        });
        
        // Action Location Change
        $(".location_id").change(function(){
            getTotalQtyOnHand($(this));
        });
        
        // Action Input New Qty InventoryPhysical
        $(".InventoryPhysicalQtyDifference").click(function(){
            // Get Value From Select Row
            var objNewQty = $(this);
            var productId = $(this).closest("tr").find("input[name='product_id[]']").val();
            // Load UOM Form
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/uom",
                data:   "id=" + productId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_UOM_MANAGEMENT; ?>',
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                            $(".inputUom").keypress(function(e){
                                if((e.which && e.which == 13) || e.keyCode == 13){                                    
                                    return false;
                                }
                            });
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                var qtyInventoryPhysicalDiff = 0;
                                $(".cloneInventoryPhysical").each(function(){
                                    qtyInventoryPhysicalDiff += replaceNum($(this).find(".floatQtyInventoryPhysicalUom").val()) * replaceNum($(this).find(".qty_uom_cycle").find("option:selected").attr("conversion"));
                                });
                                objNewQty.closest("tr").find(".InventoryPhysicalQtyDifferenceHidden").val(qtyInventoryPhysicalDiff);
                                // Disabled Button Save
                                disableEnableSave(true);
                                // Calculate Tota Sales Mix
                                calculateTotalInventoryPhysical(objNewQty);
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        // Action Remove Row
        $(".btnRemoveInventoryPhysical").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_REMOVE_ITEM; ?></p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
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
                        setIndexRowInventoryPhysical();
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function checkEventInventoryPhysical(){
        eventKeyInventoryPhysical();
        $(".tblInventoryPhysicalList").unbind("click");
        $(".tblInventoryPhysicalList").click(function(){
            eventKeyInventoryPhysical();
        });
    }
    
    function setIndexRowInventoryPhysical(){
        var sort = 1;
        $(".tblInventoryPhysicalList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOnHand(obj){
        // Get Value From Object
        var productId   = obj.closest("tr").find("input[name='product_id[]']").val();
        var location    = obj.closest("tr").find("select[name='location_id[]']").val();
        var lotsNumber  = obj.closest("tr").find("input[name='lots_number[]']").val()!=''?obj.closest("tr").find("input[name='lots_number[]']").val():0;
        var adjDate     = $("#InventoryPhysicalDate").val().toString().split("/")[2]+"-"+$("#InventoryPhysicalDate").val().toString().split("/")[1]+"-"+$("#InventoryPhysicalDate").val().toString().split("/")[0];
        // Check Value
        if(location != '' && productId != '' && adjDate != ''){
            // Change Format Date
            var expired = "0000-00-00";
            if(obj.closest("tr").find("input[name='expired_date[]']").val() != "" && obj.closest("tr").find("input[name='expired_date[]']").val() != "0000-00-00"){
                expired = obj.closest("tr").find("input[name='expired_date[]']").val().toString().split("/")[2]+"-"+obj.closest("tr").find("input[name='expired_date[]']").val().toString().split("/")[1]+"-"+obj.closest("tr").find("input[name='expired_date[]']").val().toString().split("/")[0];
            }
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/inventory_physicals/"; ?>getTotalQtyOnHand/"+location+"/"+adjDate+"/"+lotsNumber+"/"+expired,
                data:   "product_id="+productId+"&inv_adj_id=",
                beforeSend: function(arr, $form, options) {
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    // Change Format Expired Date
                    $(".expired_date").datepicker("option", "dateFormat", "dd/mm/yy");
                    // Disable Input
                    obj.closest("tr").find(".lots_number").attr("readonly", true);
                    obj.closest("tr").find(".location_id").attr("disabled", true);
                    // Disabled Button Save
                    disableEnableSave(true);
                },
                success: function(totalQty){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    // Assign Total Qty On Hand
                    obj.closest("tr").find(".qtyInventoryPhysicalCurrent").val(totalQty);
                    // Enable Input
                    obj.closest("tr").find(".lots_number").removeAttr("readonly");
                    obj.closest("tr").find(".location_id").removeAttr("disabled");
                    // Calculate Tota Sales Mix
                    calculateTotalInventoryPhysical(obj);
                }
            });
        } else{
            // Assign Total Qty On Hand
            obj.closest("tr").find(".qtyInventoryPhysicalCurrent").val(0);
            // Calculate Tota Sales Mix
            calculateTotalInventoryPhysical(obj);
        }
    }
    
    function calculateTotalInventoryPhysical(obj){
        // Assign Variable and Get Value From Selected
        var qtyInventoryPhysicalDiff = obj.closest("tr").find(".InventoryPhysicalQtyDifferenceHidden").val();
        var labelMain  = obj.closest("tr").find(".uomMainLabel").val();
        var labelSmall = obj.closest("tr").find(".smallUomLabelInventoryPhysical").val();
        var uomSmall   = parseFloat(obj.closest("tr").find(".smallUomInventoryPhysical").val());
        var qtyCurrent = parseFloat(obj.closest("tr").find(".qtyInventoryPhysicalCurrent").val());
        var qtyOnHand  = calculateQtyDisplay(qtyCurrent, labelMain, labelSmall, uomSmall);
        obj.closest("tr").find(".qtyOnHandLabel").val(qtyOnHand);
        // Assign Total Qty As Label
        var qtyDiffDiplay = calculateQtyDisplay(qtyInventoryPhysicalDiff, labelMain, labelSmall, uomSmall);
        // Assign Total Qty To Table List
        obj.closest("tr").find(".InventoryPhysicalQtyDifference").val(qtyDiffDiplay);
        // Enable Button Save
        disableEnableSave(false);
    }
    
    // Disable / Enable Button Save
    function disableEnableSave(con){
        // True = Disable, False = Enable
        if(con == true){
            $(".saveInventory").attr('disabled', true);
        }else{
            $(".saveInventory").removeAttr('disabled');
        }
    }
    
</script>
<div class="inputContainer" style="width:100%; float: left;" id="searchFormInventoryPhysical">
    <table style="width: 100%;">
        <tr>
            <td style="width: 410px;">
                <?php
                if($allowAddProduct){
                ?>
                <div class="addnew">
                    <?php echo $this->Form->text('code', array('id' => 'InventoryPhysicalSKU', 'style' => 'width:360px; height:25px; border: none; background: none;', 'placeholder' => TABLE_SEARCH_SKU_NAME)); ?>
                    <img alt="<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>" align="absmiddle" style="cursor: pointer; width: 20px;" id="addProductInventoryPhysical" onmouseover="Tip('<?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?>')" src="<?php echo $this->webroot . 'img/button/plus-32.png'; ?>" />
                </div>
                <?php
                } else {
                    echo $this->Form->text('code', array('id' => 'InventoryPhysicalSKU', 'style' => 'width:400px; height:25px;', 'placeholder' => TABLE_SEARCH_SKU_NAME));
                }
                ?>
            </td>
            <td>
                <img alt="<?php echo TABLE_SHOW_PRODUCT_LIST; ?>" align="absmiddle" style="cursor: pointer; width: 25px; height: 25px;" class="searchProductInventoryPhysical" onmouseover="Tip('<?php echo TABLE_SHOW_PRODUCT_LIST; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
        </tr>
    </table>
</div>
<div style="clear: both;"></div>
<table id="tblInventoryPhysicalHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%;">
    <tr>
        <th class="first" style="width:3%"><?php echo TABLE_NO; ?></th>
        <th style="width:10%"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:16%"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:9%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:8%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:14%"><?php echo TABLE_LOCATION; ?></th>
        <th style="width:10%"><?php echo TABLE_UOM; ?></th>
        <th style="width:14%"><?php echo TABLE_ADJUST_QTY; ?></th>
        <th style="width:13%"><?php echo TABLE_QTY_ON_HAND; ?></th>
        <th style="width:3%;"></th>
    </tr>
</table>
<div id="bodyListInventoryPhysical">
    <table id="tblInventoryPhysical" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <tr id="OrderListInventoryPhysical" class="tblInventoryPhysicalList" style="visibility: hidden;">
            <td class="first" style="width: 3%;"></td>
            <td style="width: 10%;">
                <input type="text" class="PUCLabel" readonly="" style="width: 95%; height: 25px;" />
            </td>
            <td style="width: 16%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" />
                    <input type="text" style="width: 90%; height: 25px;" class="product_name" readonly="readonly" />
                </div>
            </td>
            <td style="width: 9%; <?php if($allowLots == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="lots_number[]" style="width: 90%; height: 25px;" class="lots_number" />
                </div>
            </td>
            <td style="width: 8%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="expired_date[]" style="width: 90%; height: 25px;" class="expired_date" readonly="readonly" />
                </div>
            </td>
            <td style="width: 14%;">
                <div class="inputContainer" style="width:100%">
                    <select style="width: 90%; height: 25px;" name="location_id[]" class="location_id validate[required]"></select>
                </div>
            </td>
            <td style="width: 10%;">
                <input type="hidden" class="uomMainId" />
                <input type="hidden" class="uomMainLabel" />
                <input type="hidden" class="smallUomInventoryPhysical" />
                <input type="hidden" class="smallUomLabelInventoryPhysical" />
                <span class="uom_label"></span>
            </td>
            <td style="width: 14%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="InventoryPhysicalQtyDifferenceHidden" name="qty_diff[]" value="0" />
                    <input type="text" class="InventoryPhysicalQtyDifference" value="0" readonly="readonly" style="width: 90%; height: 25px;" />
                </div>
            </td>
            <td style="width: 13%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" class="qtyInventoryPhysicalCurrent" name="total_qty[]" value="0" />
                    <input type="text" class="qtyOnHandLabel" value="0" readonly="readonly" style="width: 90%; height: 25px;" />
                </div>
            </td>
            <td style="width: 3%;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveInventoryPhysical" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
    </table>
</div>