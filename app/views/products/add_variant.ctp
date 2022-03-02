<?php
// Setting
$allowBarcode = false;
$allowLost    = false;
$allowExpired = false;
$costDecimal  = 2;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (1, 6, 7, 39) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 1){
        if($rowSetting['is_checked'] == 1){
            $allowBarcode = true;
        }
    } else if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLost = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    } else if($rowSetting['id'] == 39){
        $costDecimal = $rowSetting['value'];
    }
}



include("includes/function.php");
// Authentication
$this->element('check_access');
$allowSetCost    = checkAccess($user['User']['id'], $this->params['controller'], 'setCost');
$allowAddPgroup  = checkAccess($user['User']['id'], $this->params['controller'], 'addPgroup');
$allowAddUoM     = checkAccess($user['User']['id'], $this->params['controller'], 'addUom');
$allowAddBrand   = checkAccess($user['User']['id'], $this->params['controller'], 'addBrand');
$allowAddCountry   = checkAccess($user['User']['id'], $this->params['controller'], 'addCountry');
$frmName         = "frm" . rand();
$frmNameMain     = "frmMain" . rand();
$dialogPhoto     = "dialogPhoto" . rand();
$cropPhoto       = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    
    //Multi Photo
    var rowTableMultiPhoto    =  $("#OrderListMutiPhoto");
    var rowIndexMultiPhoto    = 0;
    var timeBarcodeMultiPhoto = 1;    
    
    var jcrop_api='';
    var x,y,x2,y2,w,h;
    var obj;
    function showCoords(c)
    {
        x=c.x;
        y=c.y;
        x2=c.x2;
        y2=c.y2;
        w=c.w;
        h=c.h;
    };
    var divPhotoUpload   = $("#divProductPhoto").html();
    var specialChars     = [62,33,36,64,35,37,94,38,42,40,41,95,45,43,61,47,96,126];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ProductUnitCost").autoNumeric({mDec: <?php echo $costDecimal; ?>, aSep: ','});
        $(".interger").autoNumeric({aDec: '.', mDec: 8, aSep: ','});
        $("#ProductDepartmentId").chosen({ width: 350 });
        $("#ProductVendorId,#ProductSizeId,#ProductColorId").chosen({ width: 410 });
        $("#ProductDepartmentId").unbind("change").change(function(){
            var departmentId = $(this).val();
            $("#ProductSubCategory").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            $("#ProductPgroupId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            if(departmentId != ""){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getCategory/"+departmentId,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    error: function (result) {
                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#ProductCategory").html(result);
                    }
                });
            } else {
                $("#ProductCategory").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            }
        });
        // Category
        $("#ProductCategory").unbind("change").change(function(){
            var categoryId = $(this).val();
            $("#ProductPgroupId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            if(categoryId != ""){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getSubCategory/"+categoryId,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    error: function (result) {
                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#ProductSubCategory").html(result);
                    }
                });
            } else {
                $("#ProductSubCategory").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            }
        });
        // Sub Category
        $("#ProductSubCategory").unbind("change").change(function(){
            var categoryId = $(this).val();
            if(categoryId != ""){
                $.ajax({
                    type: "GET",
                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getSubCategory/"+categoryId,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    error: function (result) {
                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#ProductPgroupId").html(result);
                    }
                });
            } else {
                $("#ProductPgroupId").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            }
        });
        <?php
        if($allowAddCountry){
        ?>
        $("#ProductCountryId").chosen({ width: 350, allow_add: true, allow_add_label: '<?php echo MENU_COUNTRY_ADD; ?>', allow_add_id: 'addNewCountryProduct' });
        $("#addNewCountryProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addCountry/"; ?>",
                beforeSend: function(){
                    $("#ProductCountryId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_COUNTRY_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '300',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#CountryAddCountryForm";
                                var validateBack = $(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addCountry",
                                        data: $("#CountryAddCountryForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add Country', 2, result);
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
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Products', 'Quick Add Country', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#ProductCountryId").html(result.option);
                                                $("#ProductCountryId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
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
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#ProductCountryId").chosen({width: 350});
        <?php
        }
        if($allowAddBrand){
        ?>
        $("#ProductBrandId").chosen({ width: 350, allow_add: true, allow_add_label: '<?php echo MENU_BRAND_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewBrandProduct' });
        $("#addNewBrandProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addBrand/"; ?>",
                beforeSend: function(){
                    $("#ProductBrandId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_BRAND_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '300',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#BrandAddBrandForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addBrand",
                                        data: $("#BrandAddBrandForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add Brand', 2, result);
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
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Products', 'Quick Add Brand', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#ProductBrandId").html(result.option);
                                                $("#ProductBrandId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
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
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#ProductBrandId").chosen({width: 350});
        <?php
        }
        if($allowAddUoM){
        ?>
        $("#ProductPriceUomId").chosen({ width: 200, allow_add: true, allow_add_label: '<?php echo MENU_UOM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewUoMProduct' });
        $("#addNewUoMProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addUom/"; ?>",
                beforeSend: function(){
                    $("#ProductPriceUomId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_UOM_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '1000',
                        height: '400',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName     = "#UomAddUomForm";
                                var validateBack = $(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addUom",
                                        data: $("#UomAddUomForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add UoM', 2, result);
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
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Products', 'Quick Add UoM', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#ProductPriceUomId").html(result.option);
                                                $("#ProductPriceUomId").trigger("chosen:updated");
                                                $("#ProductPriceUomId").change();
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
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
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#ProductPriceUomId").chosen({width: 200});
        <?php
        }
        ?>
        // Protect Input
        $("#ProductBarcode, #ProductCode").keypress(function(event) {
            if($.inArray(event.which,specialChars) != -1) {
                event.preventDefault();
            }
        });
        $("#ProductAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        // Form Action Save
        $("#ProductAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if($("#ProductPgroupId").val() == null || $("#ProductPgroupId").val() == ""){
                    alertSelectGroupProduct();
                    return false;
                }
                if($("#ProductPriceUomId").val() == null || $("#ProductPriceUomId").val() == ""){
                    alertSelectUoMProduct();
                    return false;
                }
                $(".interger, #ProductUnitCost").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveProduct").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Products', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Products', 'Add', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $(".btnBackProduct").click();
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position: 'center',
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
        
        $(".btnBackProduct").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableProductDashBoard.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#ProductPriceUomId").change(function(){
            var companyId = $("#ProductCompanyId").val();
            var val = $(this).val();
            var obj = $(this);
            var uom = obj.find("option[value='"+$(this).val()+"']").html();
            // Reset Reorder Level UoM
            $("#ProductReorderLevelUoM").html('');
            if(companyId != ''){
                if(val != ''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/getSkuUom/"+val,
                        data: '',
                        beforeSend: function(){
                            $(".btnSavePro").hide();
                            obj.attr('disabled',true);
                            $("#loadUomPro").show();
                        },
                        success: function(result){
                            $("#loadUomPro").hide(); 
                            obj.removeAttr('disabled');
                            if(result != 'Error Select Uom'){
                                $("#dvSkuUomPro").html(result);
                                onBlurSkuUomPro();
                            }
                            $(".btnSavePro").show();
                        }
                    });
                    $("#ProductReorderLevelUoM").html(uom);
                }else{
                    $("#dvSkuUomPro").html('');
                }
            }else{
                obj.find("option[value='']").attr("selected","selected");
            }
        });
        
        $("#ProductBarcode").blur(function(){
            var companyId     = $("#ProductCompanyId").val();
            var puc           = $(this);
            var imgLoad       = $(this).closest("tr").find(".loadSkuUomPro");
            var available     = $(this).closest("tr").find(".availableSkuUomPro");
            var noneAvailable = $(this).closest("tr").find(".noneAvailableSkuUomPro");
            if(companyId != ''){
                if(puc.val() != ''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkPuc/"+companyId+"/"+puc.val(),
                        data: '',
                        beforeSend: function(){
                            imgLoad.show();
                            noneAvailable.hide();
                            available.hide();
                            $(".btnSavePro").hide();
                        },
                        success: function(result){
                            imgLoad.hide(); 
                            if(result == 'available'){
                                noneAvailable.hide();
                                available.show();
                                puc.select().focus();
                            }else if(result == 'not available'){
                                noneAvailable.show();
                                available.hide();
                            }else if(result == 'Error UPC'){
                                puc.val('');
                            }
                            $(".btnSavePro").show();
                        }
                    });
                }
            }else{
                puc.val("");
            }
        });
        
        $("#ProductCode").blur(function(){
            var companyId     = $("#ProductCompanyId").val();
            var sku           = $(this);
            var imgLoad       = $(this).closest("tr").find(".loadSkuUomPro");
            var available     = $(this).closest("tr").find(".availableSkuUomPro");
            var noneAvailable = $(this).closest("tr").find(".noneAvailableSkuUomPro");
            var checkSku      = true;
            if(companyId != ''){
                if ($('.skuUomPro').length) {
                    $(".skuUomPro").each(function(){
                        var obj = $(this);
                        if(obj.val() == sku.val()){
                            checkSku = false;
                            return false;
                        }
                    });
                }
                if(checkSku == true && sku.val() != ''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkSkuUom/"+companyId+"/"+sku.val(),
                        data: '',
                        beforeSend: function(){
                            imgLoad.show();
                            noneAvailable.hide();
                            available.hide();
                            $(".btnSavePro").hide();
                        },
                        success: function(result){
                            imgLoad.hide(); 
                            if(result == 'available'){
                                noneAvailable.hide();
                                available.show();
                                sku.select().focus();
                            }else if(result == 'not available'){
                                noneAvailable.show();
                                available.hide();
                            }else if(result == 'Error Sku'){
                                sku.val('');
                            }
                            $(".btnSavePro").show();
                        }
                    });
                }else{
                    noneAvailable.hide();
                    available.show();
                    sku.val('');
                }
            }else{
                sku.val("");
            }
        });
        
        $("#applyVariants").click(function(){
            var obj = $(this);
            var ProductVendorId = $("#ProductVendorId").val();
            var ProductColorId  = $("#ProductColorId").val();
            var ProductSizeId   = $("#ProductSizeId").val();
            if(ProductVendorId != '' && ProductColorId != '' && ProductSizeId != ''){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/products'; ?>/variantsProduct/",
                    data: 'vendor_id='+$("#ProductVendorId").val()+'&color_id='+$("#ProductColorId").val()+'&size_id='+$("#ProductSizeId").val(),
                    beforeSend: function () {
                        $(".variantsDetail").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="top: 50%; left:50%; position: center;" />');
                    },
                    success: function (result) {
                        $(".variantsDetail").html('');
                        $(".variantsDetail").html(result);

                        clickUploadPhoto();
                        loadMultiPhoto();

                        if(obj.is(':checked')){
                            obj.attr('checked',true);
                            applyClickVariantsDetail();
                        }else{
                            $(".divPhotoOther").hide();
                            $(".hidePhotoOther").hide();
                            $(".showPhotoOther").show();
                        }
                    }
                });
           
            }else{
                $(".variantsDetail").html('');
                alertSelectRequireField();
                return false;
            }
        });

        // $(".btnAddMultiPhoto").show();
        // $(".btnAddMultiPhoto").click(function(){
        //     if($(this).closest("tr").find("td .ProductPhotoMultiData").val() != ""){
        //         addMultiPhoto(); 
        //     }
        // });
        
        // $(".showPhotoOther").click(function(){
        //     $(".divPhotoOther").show();
        //     $(".hidePhotoOther").show();
        //     $(".showPhotoOther").hide();
        // });

        $(".hidePhotoOther").click(function(){
            $(".divPhotoOther").hide();
            $(".hidePhotoOther").hide();
            $(".showPhotoOther").show();
        });
        
        $(".showCatalog").click(function(){
            $(".divCatalog").show();
            $(".showCatalog").hide();
        });
        
        $(".showMainPhoto").click(function(){
            $(".divMainPhoto").show();
            $(".showMainPhoto").hide();
        });
        
        $(".hideMainPhoto").click(function(){
            $(".divMainPhoto").hide();
            $(".showMainPhoto").show();
        });
        
        $(".hideCatalog").click(function(){
            $(".divCatalog").hide();
            $(".showCatalog").show();
        });
        
        // From Action Upload Photo
        $("#<?php echo $frmNameMain; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#ProductMainPhoto").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
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
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var photoFolder='';
                var photoName=result;
                photoFolder="public/product_photo/tmp/";
                $('#<?php echo $cropPhoto; ?>').attr("src", "<?php echo $this->webroot; ?>" + photoFolder + photoName + "?" + Math.random());
                if(jcrop_api==''){
                    $('#<?php echo $cropPhoto; ?>').Jcrop({
                        setSelect: [0,0,10000,10000],
                        allowSelect: false,
                        onChange:   showCoords,
                        onSelect:   showCoords
                    },function(){
                        jcrop_api = this;
                    });
                }else{
                    jcrop_api.setImage("<?php echo $this->webroot; ?>" + photoFolder + photoName);
                    jcrop_api.setSelect([0,0,10000,10000]);
                }
                $("#<?php echo $dialogPhoto; ?>").dialog({
                    title: 'Crop Image',
                    resizable: false,
                    modal: true,
                    width: '90%',
                    height: '400',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        'Crop': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/products/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h,
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    $("#mainPhotoDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#<?php echo $photoNameHidden; ?>").val(result);
                                }
                            });
                        }
                    }
                });
            }
        });
        
        $("#ProductMainPhoto").live('change', function(){
            $("#<?php echo $frmNameMain; ?>").submit();
        });
        resetFormProduct();
        onBlurSkuUomPro();
        changeInputCSSProduct();
        eventKeyMultiPhoto(0);
    });
    

    function applyClickVariantsDetail(){
        $(".divPhotoOther").show();
        $(".hidePhotoOther").show();
        $(".showPhotoOther").hide();
    }

    function alertSelectRequireField(){
        $(".btnSavePro").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_REQUIRED_FIELD; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }

    function leaveAStepCallback(obj, context){
        // To check and enable finish button if needed
        if (context.fromStep >= 2) {
            $('#wizard1').smartWizard('enableFinish', true);
        }
        return true;
    }

    function onFinishCallback(){
        alert('Finish Called');
    }

    function eventKeyMultiPhoto(rel){
        //Remove Photo in Crop
        $('#<?php echo $cropPhoto; ?>').removeAttr("src");
        
        // From Action Upload Photo
        $(".<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $(".ProductPhoto").val();
                
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                    ext  = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
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
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                //Explode Multi Photo
                var explodeMultiPhoto = result.split("|*|");
                var rel = explodeMultiPhoto[1];
                alert(explodeMultiPhoto);
                // var photoFolder = '';
                // var photoName   = explodeMultiPhoto[0];
                // photoFolder="public/product_photo/tmp/";
                // $('#<?php echo $cropPhoto; ?>').attr("src", "<?php echo $this->webroot; ?>" + photoFolder + photoName + "?" + Math.random());
                // if(jcrop_api==''){
                //     $('#<?php echo $cropPhoto; ?>').Jcrop({
                //         setSelect: [0,0,10000,10000],
                //         allowSelect: false,
                //         onChange:   showCoords,
                //         onSelect:   showCoords
                //     },function(){
                //         jcrop_api = this;
                //     });
                // }else{
                //     jcrop_api.setImage("<?php echo $this->webroot; ?>" + photoFolder + photoName);
                //     jcrop_api.setSelect([0,0,10000,10000]);
                // }
                // $("#<?php echo $dialogPhoto; ?>").dialog({
                //     title: 'Crop Image',
                //     resizable: false,
                //     modal: true,
                //     width: '90%',
                //     height: '400',
                //     position: 'center',
                //     open: function(event, ui){
                //         $(".ui-dialog-buttonpane").show();
                //     },
                //     buttons: {
                //         'Crop': function() {
                //             $.ajax({
                //                 type: "POST",
                //                 url: "<?php echo $this->base; ?>/products/cropPhoto",
                //                 data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h,
                //                 beforeSend: function(){
                //                     $("#<?php echo $dialogPhoto; ?>").dialog("close");
                //                 },
                //                 success: function(result){
                //                     //Set Rel Multi Photo
                //                     if(rel == 0){
                //                         rel = "";
                //                     }
                //                     $("#photoDisplay_"+rel).attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                //                     $("#ProductPhotoMultiData_"+rel).val(result);
                //                     loadMultiPhoto();
                //                 }
                //             });
                //         }
                //     }
                // });
            }
        });
        
        clickUploadPhoto();
        // $(".btnnRemoveMultiPhoto").click(function(){
        //     btnnRemoveMultiPhoto($(this));
        // });
        loadMultiPhoto();
    }

    function clickUploadPhoto(){
        $("#tblMutiPhoto").find(".ProductPhoto").each(function(){
            var rel = $(this).attr("rel");
            $("#ProductPhoto_"+rel).live('change', function(){
                //Set Rel Multi Photo
                var url = '<?php echo $this->base; ?>';
                if(rel == 0){
                    rel = "";
                }
                
                $(".<?php echo $frmName; ?>").removeAttr("action");
                // $(".<?php echo $frmName; ?>").attr("action", url+"/products/upload/"+((rel=="")?0:rel));
                $(".<?php echo $frmName; ?>").attr("action", url+"/products/upload/"+rel);
                $(".<?php echo $frmName; ?>").submit();
                // eventKeyMultiPhoto(rel);
            });
        });

        // $(".ProductPhoto").click(function(){
        //     //Set Rel Multi Photo
        //     var rel = $(this).attr("rel");
        //     var url = '<?php echo $this->base; ?>';
        //     if(rel == 0){
        //         rel = "";
        //     }
        //     $("#ProductPhoto_"+rel).live('change', function(){
        //         $(".<?php echo $frmName; ?>").removeAttr("action");
        //         $(".<?php echo $frmName; ?>").attr("action", url+"/products/upload/"+((rel=="")?0:rel));
        //         $(".<?php echo $frmName; ?>").submit();
        //     });
        // });
    }
    
    // function addMultiPhoto(){
    //     // ProductPhoto
    //     var tr  = rowTableMultiPhoto.clone(true);
    //     rowIndexMultiPhoto = parseInt($(".tblMutiPhotoList:last").find(".ProductPhoto").attr("rel")) + 1;
    //     tr.removeAttr("style").removeAttr("id");
        
    //     tr.find(".photoDisplay").removeAttr("id").removeAttr("id");
    //     tr.find(".ProductPhoto").removeAttr("id").removeAttr("id");
    //     tr.find(".ProductPhotoMultiData").removeAttr("id").removeAttr("id");
        
    //     tr.find(".photoDisplay").attr("id", "photoDisplay"+rowIndexMultiPhoto);
    //     tr.find(".photoDisplay").removeAttr("src");
    //     tr.find(".ProductPhoto").attr("id", "ProductPhoto"+rowIndexMultiPhoto).val("");
    //     tr.find(".ProductPhotoMultiData").attr("id", "ProductPhotoMultiData"+rowIndexMultiPhoto).val("");
        
    //     tr.find(".ProductPhoto").attr("rel", rowIndexMultiPhoto);
    //     $("#tblMutiPhoto").append(tr);

    //     eventKeyMultiPhoto(rowIndexMultiPhoto);
    // }
    
    // function btnnRemoveMultiPhoto(obj){
    //     var currentTr = obj.closest("tr");
    //     currentTr.remove();
    //     loadMultiPhoto();
    // }
    
    function loadMultiPhoto(){
        var item     = 0;
        var dataItem = 0;
        var dataMultiPhoto = "";
        $("#loadMultiPhoto").html('');
        $("#tblMutiPhoto").find(".ProductPhotoMultiData").each(function(){
           dataMultiPhoto += "<input type='hidden' name='data[photo][]' value='"+$(this).val()+"' />";
           item++;
           if($(this).val() != ""){
               dataItem++;
           }
        });
        $("#loadMultiPhoto").html(dataMultiPhoto);

        // if(item == 1){
        //     if(dataItem == 0){
        //         $(".tblMutiPhotoList").find(".btnAddMultiPhoto").show();
        //     }else{
        //         $(".tblMutiPhotoList").find(".btnAddMultiPhoto").show();
        //     }
        //     $(".tblMutiPhotoList").find(".btnnRemoveMultiPhoto").hide();
        // }else{
        //     $(".tblMutiPhotoList").find(".btnAddMultiPhoto").hide();
        //     $(".tblMutiPhotoList:last").find(".btnAddMultiPhoto").show();
        //     $(".tblMutiPhotoList").find(".btnnRemoveMultiPhoto").show();
        // }
    }
    
    function resetFormProduct(){
        // SELECT
        $("#productIsClone").val(0);
        <?php
        if(!$allowSetCost){
        ?>
        $("#ProductUnitCost").val(0);
        <?php
        }
        ?>
        $("#ProductIsLots").find("option[value='0']").attr("selected", true);
        $("#ProductIsExpiredDate").find("option[value='0']").attr("selected", true);
        $("#ProductPriceUomId").find("option[value='']").attr("selected", true);
        // Textarea
        $("#ProductAddForm").find("textarea").val('');
        // INPUT FILE
        $("#divProductPhoto").html(divPhotoUpload);
        // UOM DETAIL
        $("#dvSkuUomPro").html('');
        // SYMBOL CHECK CODE
        $(".availableSkuUomPro").hide();
        $(".noneAvailableSkuUomPro").hide();
        // PRODUCT GROUP
        $("#ProductPgroupId_chzn").find(".chzn-choices").find(".search-choice-close").click();
    }
    
    function onBlurSkuUomPro(){
        $(".skuUomPro").unbind("blur").unbind("keyup").unbind("keypress");
        $(".skuUomPro").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){                                    
                return false;
            }
        });
        $(".skuUomPro").blur(function(){
            var id            = $(this).attr('id');
            var companyId     = $("#ProductCompanyId").val();
            var sku           = $(this);
            var skuMain       = $("#ProductCode").val();
            var imgLoad       = $(this).closest("tr").find(".loadSkuUomPro");
            var available     = $(this).closest("tr").find(".availableSkuUomPro");
            var noneAvailable = $(this).closest("tr").find(".noneAvailableSkuUomPro");
            var checkSkuUOm   = true;
            $(".skuUomPro").each(function(){
                var obj = $(this);
                if(obj.attr('id') != sku.attr('id') && obj.val() == sku.val()){
                    checkSkuUOm = false;
                    return false;
                }
            });
            
            if(sku.val() != '' && sku.val() != skuMain && checkSkuUOm == true){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkSkuUom/"+companyId+"/"+sku.val(),
                    data: '',
                    beforeSend: function(){
                        imgLoad.show();
                        noneAvailable.hide();
                        available.hide();
                        $(".btnSavePro").hide();
                    },
                    success: function(result){
                        imgLoad.hide(); 
                        if(result == 'available'){
                            noneAvailable.hide();
                            available.show();
                            sku.select().focus();
                        }else if(result == 'not available'){
                            noneAvailable.show();
                            available.hide();
                        }else if(result == 'Error Sku'){
                            sku.val('');
                        }
                        $(".btnSavePro").show();
                    }
                });
            }else if(sku.val() != ''){
                noneAvailable.hide();
                available.show();
                sku.val('');
            }
        });
    }
    
    function changeInputCSSProduct(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var disabled  = true;
        if($("#ProductCompanyId").val() != ''){
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            disabled  = false;
        } 
        // Label
        $("#ProductAddForm").find("label").removeAttr("class");
        $("#ProductAddForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'ProductCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        $("label[for='ProductPhoto']").removeAttr("class");
        $("label[for='ProductPhoto']").addClass(cssStyle);
        // Input & Select
        $("#ProductAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#ProductPhoto").removeClass(cssRemove);
        $("#ProductPhoto").addClass(cssStyle);
        $("#ProductPhoto").attr("disabled", disabled);
        $("#ProductAddForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'ProductCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        // Read Only
        $("#ProductAddForm").find("input").attr("readonly", disabled);
        $("#ProductAddForm").find("textarea").attr("readonly", disabled);
    }
    
    function alertSelectGroupProduct(){
        $(".btnSavePro").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_GROUP; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function alertSelectUoMProduct(){
        $(".btnSavePro").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_UOM; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }

    function callFuncSetting() {
        // Prevent Key Enter
        preventKeyEnter();
        // Integer
        $(".SettingMenuDecimal").autoNumeric({mDec: 0, aSep: ','});
        
        $('.dateSettingMenu, .dateLockTransaction').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $(".dateLockTransaction").focus(function(){
            $.cookie("dateLockCookie", $(this).val(), {expires : 7,path    : '/'});
        });
    }
    
    function dialogAlertSetting(title, result, width, height){
        $("#dialog").html(result);
        $("#dialog").dialog({
            title: title,
            resizable: false,
            modal: true,
            width: width,
            height: height,
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
</script>
<div class="divMainPhoto" style="width: 50%; float: left;">
    <form id="<?php echo $frmNameMain; ?>" action="<?php echo $this->base; ?>/products/upload" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend><span class="hideMainPhoto" style="cursor: pointer;"><?php __(TABLE_HIDE_MAIN_PHOTO); ?></span> &nbsp; <span class="showPhotoOther" style="border-left: 1px solid #000;">&nbsp;</span> <span class="showPhotoOther" style="padding: 5px; cursor: pointer;"><?php echo TABLE_VARIANTS_DETAIL; ?></span> &nbsp; <span class="showCatalog" style="border-left: 1px solid #000; display: none;">&nbsp;</span> <span class="showCatalog" style="padding: 5px; cursor: pointer; display: none;"><?php echo TABLE_SHOW_CATALOG; ?></span></legend>
            <table>
                <tr>
                    <td colspan="2">
                        <img alt="" id="mainPhotoDisplay" style="width: 170px; height: 120px;" />
                    </td>
                    <td valign="top">
                        <input type="file" name="photoMain" id="ProductMainPhoto" />
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>
</div>
<div class="divPhotoOther" style="width: 50%; float: left;">
    <form class="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/products/upload/1" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend><?php __(TABLE_VARIANTS_DETAIL); ?>&nbsp; <span style="border-left: 1px solid #000; display:none;"></span><span class="hidePhotoOther" style="padding: 5px; cursor: pointer;"><?php //echo TABLE_HIDE_OTHER_PHOTO; ?></span></legend>
            <div class="variantsDetail" id="variantsDetail">
            
            </div>
        </fieldset>
    </form>
</div>
<div style="clear:both;"></div>
<?php echo $this->Form->create('Product', array('inputDefaults' => array('label' => false))); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Product][photo]" />
<div id="loadMultiPhoto"></div>
<?php
if(!$allowSetCost){
?>
<input type="hidden" value="0" name="data[Product][unit_cost]" id="ProductUnitCost" />
<?php
}
?>
<table cellpadding="0" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <fieldset style="min-height: 440px;">
                <legend><?php __(MENU_PRODUCT_MANAGEMENT_INFO); ?>&nbsp; <span class="showMainPhoto" style="border-left: 1px solid #000; display: none;"></span><span class="showMainPhoto" style="padding: 5px; cursor: pointer; display: none;"><?php echo TABLE_SHOW_MAIN_PHOTO; ?></span></legend>
                <table width="100%" cellpadding="5">
                    <tr>
                        <td><label for="ProductName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 80%; height: 25px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductDepartmentId"><?php echo MENU_DEPARTMENT; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->input('department_id', array('label' => false, 'empty' => INPUT_SELECT, 'name' => '')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductCategory"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="" id="ProductCategory" style="width: 350px;" class="validate[required]">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductSubCategory"><?php echo 'Sub Category'; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="" id="ProductSubCategory" style="width: 350px;" class="validate[required]">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductPgroupId"><?php echo 'Sub-Sub Category'; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->input('pgroup_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 350px;', 'class' => 'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductCountryId"><?php echo TABLE_COUNTRY; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->input('country_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductBrandId"><?php echo TABLE_BRAND; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->input('brand_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductBarcode"><?php echo TABLE_BARCODE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('barcode', array('div' => false, 'style' => 'width: 80%;', 'class' => 'validate[required]')); ?>
                                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                                <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('<?php echo MESSAGE_UPC_EXIST_IN_SYSTEM; ?>')" class="availableSkuUomPro" style="display:none;" /> 
                                <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" style="display:none;" />
                            </div>
                            <div class="inputContainer availableSkuUomPro" style="width: 100%; color: red; display: none;">
                                <?php echo MESSAGE_UPC_EXIST_IN_SYSTEM; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductCode"><?php echo TABLE_SKU; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('code', array('div' => false, 'style' => 'width: 80%;', 'class' => 'validate[required]')); ?>
                                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                                <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('<?php echo MESSAGE_SKU_EXIST_IN_SYSTEM; ?>')" class="availableSkuUomPro" style="display:none;" /> 
                                <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" style="display:none;" />
                            </div>
                            <div class="inputContainer availableSkuUomPro" style="width: 100%; color: red; display: none;">
                                <?php echo MESSAGE_SKU_EXIST_IN_SYSTEM; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    if($allowSetCost){
                    ?>
                    <tr>
                        <td><label for="ProductUnitCost"><?php echo TABLE_UNIT_COST; ?> ($) <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->text('unit_cost', array('class' => 'validate[required]', 'style' => 'width: 80%;')); ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td><label for="ProductPriceUomId"><?php echo TABLE_UOM; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('price_uom_id', array('options' => $uoms, 'name' => 'data[Product][price_uom_id]', 'empty' => INPUT_SELECT, 'label' => false, 'class' => 'validate[required]', 'div' => false)); ?>
                                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" id="loadUomPro" style="display:none;" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" id="dvSkuUomPro"></td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="width: 50%; vertical-align: top;">
            <fieldset style="">
                <legend><?php echo MENU_VARIANTS_PRODUCT_MANAGEMENT_INFO; ?></legend>
                <table width="90%" cellpadding="5">
                    <tr>
                        <td style="width: 30%;"><label for="ProductVendorId"><?php echo TABLE_VENDOR; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('vendor_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductSizeId"><?php echo TABLE_SIZE; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('size_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductColorId"><?php echo TABLE_COLOR; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('color_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductApplyVariant"><?php echo TABLE_APPLY_VARIANTS; ?> :</label></td>
                        <td>
                            <input type="checkbox" id="applyVariants" class="applyVariants">
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset style="">
                <legend><?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?></legend>
                <table width="90%" cellpadding="5">
                    <tr <?php if($allowLost == false){ ?> style="display: none;"<?php } ?>>
                        <td style="width: 30%;"><label for="ProductIsLots"><?php echo TABLE_TRACK_LOT_SERIES; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="data[Product][is_lots]" id="ProductIsLots">
                                    <option value="0"><?php echo ACTION_NO; ?></option>
                                    <option value="1"><?php echo ACTION_YES; ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr <?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <td style="width: 30%;"><label for="ProductIsExpiredDate"><?php echo TABLE_TRACK_EXPIRY_DATE; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="data[Product][is_expired_date]" id="ProductIsExpiredDate">
                                    <option value="0"><?php echo ACTION_NO; ?></option>
                                    <option value="1"><?php echo ACTION_YES; ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductPackingSize"><?php echo TABLE_PACKING_SIZE; ?>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->text('packing_size', array('class' => 'interger', 'style' => 'width: 190px;', 'div' => false)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductReorderLevel"><?php echo GENERAL_REORDER_LEVEL; ?>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->text('reorder_level', array('class' => 'interger', 'style' => 'width: 190px;', 'div' => false)); ?> <span id="ProductReorderLevelUoM"></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="ProductSpec"><?php echo TABLE_SPEC; ?> :</label></td>
                        <td><?php echo $this->Form->textarea('spec', array('style' => 'height: 80px;')); ?></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="ProductDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
                        <td><?php echo $this->Form->textarea('description', array('style' => 'height: 80px;')); ?></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackProduct">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSavePro">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveProduct"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); 
?>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>


