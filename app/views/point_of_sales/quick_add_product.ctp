<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal, uom_detail_option FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

include("includes/function.php");
// Authentication
$this->element('check_access');
$allowSetCost   = checkAccess($user['User']['id'], $this->params['controller'], 'setCost');
$allowAddPgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addPgroup');
$allowAddUoM    = checkAccess($user['User']['id'], $this->params['controller'], 'addUom');
$allowAddBrand  = checkAccess($user['User']['id'], $this->params['controller'], 'addBrand');
$frmNameMain  = "frmMain" . rand();
$dialogPhoto  = "dialogPhoto" . rand();
$cropPhoto    = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
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
    var specialChars = [62,33,36,64,35,37,94,38,42,40,41,95,45,43,61,47,96,126];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ProductUnitCost").autoNumeric({mDec: <?php echo $rowOption[0]; ?>, aSep: ','});
        <?php
        if($allowAddBrand){
        ?>
        $("#ProductBrandId").chosen({ width: 280, allow_add: true, allow_add_label: '<?php echo MENU_BRAND_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewBrandProduct' });
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
        $("#ProductBrandId").chosen({width: 280});
        <?php
        }
        if($allowAddPgroup){
        ?>
        $("#ProductPgroupId").chosen({ width: 280 ,allow_add: true, allow_add_label: '<?php echo MENU_PRODUCT_GROUP_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewPgroup' });
        $("#addNewPgroup").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addPgroup/"; ?>",
                beforeSend: function(){
                    $("#ProductPgroupId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog1").html(msg);
                    $("#dialog1").dialog({
                        title: '<?php echo MENU_PRODUCT_GROUP_MANAGEMENT_ADD; ?>',
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
                                var formName = "#PgroupAddPgroupForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/products/addPgroup",
                                        data: $("#PgroupAddPgroupForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add Pgroup', 2, result);
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
                                            createSysAct('Products', 'Quick Add Pgroup', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#ProductPgroupId").html(result.option);
                                                $("#ProductPgroupId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog1").dialog({
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
        $("#ProductPgroupId").chosen({width: 280});
        <?php
        }
        if($allowAddUoM){
        ?>
        $("#ProductUomId").chosen({ width: 200, allow_add: true, allow_add_label: '<?php echo MENU_UOM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewUoM' });
        $("#addNewUoM").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addUom/"; ?>",
                beforeSend: function(){
                    $("#ProductUomId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog1").html(msg);
                    $("#dialog1").dialog({
                        title: '<?php echo MENU_UOM_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '1000',
                        height: '500',
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
                                        url: "<?php echo $this->base; ?>/products/addUom",
                                        data: $("#UomAddUomForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add UoM', 2, result);
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
                                            createSysAct('Products', 'Quick Add UoM', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#ProductUomId").html(result.option);
                                                $("#ProductUomId").trigger("chosen:updated");
                                                $("#ProductUomId").change();
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog1").dialog({
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
        $("#ProductUomId").chosen({ width: 200 });
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
                    height: '600',
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
        
        $("#ProductUomId").change(function(){
            var companyId     = $("#ProductCompanyId").val();
            var val = $(this).val();
            var obj = $(this);
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
        
        resetFormProduct();
        onBlurSkuUomPro();
        changeInputCSSProduct();
        <?php 
        if(count($companies) == 1 && COUNT($branches) > 1){
        ?>
        getBranchProduct();
        <?php
        }
        ?>
    });
    <?php 
    if(count($companies) == 1 && COUNT($branches) > 1){
    ?>
    function getBranchProduct(){
        var companyId = $("#ProductCompanyId").val();
        if(companyId != ''){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base . '/users'; ?>/getBranchByCompany/" + companyId,
                data: "",
                beforeSend: function(){
                    listbox_selectall('productBranchSelected', true);
                    listbox_moveacross('productBranchSelected', 'productBranch');
                    $("#productBranch").html('');
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(opt){
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#productBranch").append(opt);
                    // Auto Select Branch
                    listbox_selectall('productBranch', true);
                    listbox_moveacross('productBranch', 'productBranchSelected');
                }
            });
        }
    }
    <?php
    }
    ?>
    function resetFormProduct(){
        <?php
        if(!$allowSetCost){
        ?>
        $("#ProductUnitCost").val(0);
        <?php
        }
        ?>
        $("#ProductIsExpiredDate").find("option[value='0']").attr("selected", true);
        $("#ProductUomId").find("option[value='']").attr("selected", true);
        // Textarea
        $("#ProductAddForm").find("textarea").val('');
        // UOM DETAIL
        $("#dvSkuUomPro").html('');
        // SYMBOL CHECK CODE
        $(".availableSkuUomPro").hide();
        $(".noneAvailableSkuUomPro").hide();
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
        // Input & Select
        $("#ProductAddForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
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
</script>
<br />
<?php 
echo $this->Form->create('Product', array('inputDefaults' => array('label' => false))); 
if(!$allowSetCost){
?>
<input type="hidden" value="0" name="data[Product][unit_cost]" id="ProductUnitCost" />
<?php
}
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Product][company_id]" id="ProductCompanyId" />
<?php
}
if(COUNT($branches) == 1){
    $branchId = key($branches);
?>
<input type="hidden" id="productBranchSelected" name="data[Product][branch_id][]" value="<?php echo $branchId; ?>" />
<?php
}
?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Product][photo]" />
<div style="width: 54%; float: left;">
    <table width="100%" cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 15%;"><label for="ProductName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;"><?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 270px; height: 25px; text-align: left;')); ?></div>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;"><label for="ProductPgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span>:</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('pgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
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
                    <?php echo $this->Form->input('barcode', array('class' => 'validate[required]', 'div' => false, 'style' => 'width: 270px; text-align: left;')); ?>
                    <?php echo $this->Form->hidden('code', array('class' => 'validate[required]', 'div' => false)); ?>
                    <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                    <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('<?php echo MESSAGE_UPC_EXIST_IN_SYSTEM; ?>')" class="availableSkuUomPro" style="display:none;" /> 
                    <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" style="display:none;" />
                </div>
                <div class="inputContainer availableSkuUomPro" style="width: 100%; color: red; display: none;">
                    <?php echo MESSAGE_UPC_EXIST_IN_SYSTEM; ?>
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
                <?php echo $this->Form->text('unit_cost', array('class' => 'validate[required]', 'style' => 'width: 270px; text-align: left;')); ?>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td><label for="ProductUomId"><?php echo TABLE_UOM; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('uom_id', array('name' => 'data[Product][price_uom_id]', 'empty' => INPUT_SELECT, 'label' => false, 'class' => 'validate[required]', 'div' => false)); ?>
                    <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" id="loadUomPro" style="display:none;" />
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" id="dvSkuUomPro">

            </td>
        </tr>
        <tr>
            <td><label for="ProductIsExpiredDate"><?php echo TABLE_HAS_EXPIRED_DATE; ?>:</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[Product][is_expired_date]" id="ProductIsExpiredDate" style="width: 200px;">
                        <option value="0"><?php echo ACTION_NO; ?></option>
                        <option value="1"><?php echo ACTION_YES; ?></option>
                    </select>
                    <input type="hidden" value="0" name="data[Product][is_packet]" id="ProductIsPacket" />
                    <div style="clear: both;"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="ProductDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td><?php echo $this->Form->textarea('description', array('style' => 'width: 270px;')); ?></td>
        </tr>
    </table>
</div>
<?php echo $this->Form->end(); ?>
<div style="width: 44%; float: right;">
    <form id="<?php echo $frmNameMain; ?>" action="<?php echo $this->base; ?>/products/upload" method="post" enctype="multipart/form-data">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center;">
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/no-images.png" id="mainPhotoDisplay" style="max-width: 200px; max-height: 200px;" />
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <input type="file" name="photoMain" id="ProductMainPhoto" />
                </td>
            </tr>
        </table>
    </form>
    <?php 
    if(count($branches) > 1){
    ?>
    <fieldset id="formBranchProductQuick">
        <legend><?php __(TABLE_APPLY_WITH_BRANCH); ?></legend>
        <table>
            <tr>
                <th><label for="productBranch">Available:</label></th>
                <th></th>
                <th><label for="productBranchSelected">Member of:</label></th>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <select id="productBranch" multiple="multiple" style="width: 160px; height: 200px;"></select>
                </td>
                <td style="vertical-align: middle;">
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('productBranch', 'productBranchSelected')" />
                    <br /><br />
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" onclick="listbox_moveacross('productBranchSelected', 'productBranch')" />
                </td>
                <td style="vertical-align: top;">
                    <select id="productBranchSelected" name="data[Product][branch_id][]" multiple="multiple" style="width: 160px; height: 200px;"></select>
                </td>
            </tr>
        </table>
    </fieldset>
    <?php
    }
    ?>
</div>
<br />
<div style="clear: both;"></div>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>