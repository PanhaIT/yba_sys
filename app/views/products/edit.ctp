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

$checkICS = 0;
$sqlCheckPgroupUse = mysql_query("SELECT id FROM inventories WHERE product_id = ".$this->data['Product']['id']." LIMIT 1"); 
if(mysql_num_rows($sqlCheckPgroupUse)){
    $checkICS = 1;
}
$checkStock = mysql_query("SELECT IFNULL(SUM(total_qty), 0) AS total FROM product_inventories WHERE product_id = ".$this->data['Product']['id']);
$rowStock   = mysql_fetch_array($checkStock);
include("includes/function.php");
// Authentication
$this->element('check_access');
$allowDelete    = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowSetCost   = checkAccess($user['User']['id'], $this->params['controller'], 'setCost');
$allowSetPrice  = checkAccess($user['User']['id'], $this->params['controller'], 'productPrice');
$allowAddPgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addPgroup');
$allowAddUoM    = checkAccess($user['User']['id'], $this->params['controller'], 'addUom');
$allowAddBrand  = checkAccess($user['User']['id'], $this->params['controller'], 'addBrand');
$allowAddCountry   = checkAccess($user['User']['id'], $this->params['controller'], 'addCountry');
$frmName = "frm" . rand();
$frmNameMain = "frmMain" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
echo $this->element('prevent_multiple_submit'); 

$productUseReady = 0;
$inventoryTotal['qty'] = 0;
$queryProductInUsed = mysql_query("SELECT SUM(qty) AS qty FROM inventories WHERE product_id=" . $this->data['Product']['id']." GROUP BY product_id");

// Quotation
$queryQuotationProductInUsed = mysql_query("SELECT count(quotations.id) FROM quotations INNER JOIN quotation_details ON quotations.id = quotation_details.quotation_id WHERE quotations.status >= 1 AND quotation_details.product_id=" . $this->data['Product']['id']." GROUP BY quotation_details.product_id");
//Order
$queryOrderProductInUsed = mysql_query("SELECT count(sales_orders.id) FROM sales_orders INNER JOIN sales_order_details ON sales_orders.id = sales_order_details.sales_order_id WHERE sales_orders.status >= 1 AND sales_order_details.product_id=" . $this->data['Product']['id']." GROUP BY sales_order_details.product_id");
// Credit Memo
$queryCMProductInUsed = mysql_query("SELECT count(sales_returns.id) FROM sales_returns INNER JOIN sales_return_details ON sales_returns.id = sales_return_details.sales_return_id WHERE sales_returns.status >= 1 AND sales_return_details.product_id=" . $this->data['Product']['id']." GROUP BY sales_return_details.product_id");
// Transfer Order
$queryRequestStockProductInUsed = mysql_query("SELECT count(request_stocks.id) FROM request_stocks INNER JOIN request_stock_details ON request_stocks.id = request_stock_details.request_stock_id WHERE request_stocks.status >= 1 AND request_stock_details.product_id=" . $this->data['Product']['id']." GROUP BY request_stock_details.product_id");
// Inventory Adjustment
$queryInvAdjProductInUsed = mysql_query("SELECT count(inventory_adjustments.id) FROM inventory_adjustments INNER JOIN inventory_adjustment_details ON inventory_adjustments.id = inventory_adjustment_details.inv_adj_id WHERE inventory_adjustments.status >= 1 AND inventory_adjustment_details.product_id=" . $this->data['Product']['id']." GROUP BY inventory_adjustment_details.product_id");

// Purchase Request
$queryPOProductInUsed = mysql_query("SELECT count(purchase_orders.id) FROM purchase_orders INNER JOIN purchase_order_details ON purchase_orders.id = purchase_order_details.purchase_order_id WHERE purchase_orders.status >= 1 AND purchase_order_details.product_id=" . $this->data['Product']['id']." GROUP BY purchase_order_details.product_id");
// Purchase Bill
$queryPBProductInUsed = mysql_query("SELECT count( purchase_bills.id) FROM purchase_bills INNER JOIN purchase_bill_details ON  purchase_bills.id = purchase_bill_details.purchase_bill_id WHERE  purchase_bills.status >= 1 AND purchase_bill_details.product_id=" . $this->data['Product']['id']." GROUP BY purchase_bill_details.product_id");


if (mysql_num_rows($queryProductInUsed) || 
    mysql_num_rows($queryQuotationProductInUsed) || 
    mysql_num_rows($queryOrderProductInUsed) || 
    mysql_num_rows($queryCMProductInUsed) || 
    mysql_num_rows($queryRequestStockProductInUsed) || 
    mysql_num_rows($queryInvAdjProductInUsed) || 
    mysql_num_rows($queryPOProductInUsed) || 
    mysql_num_rows($queryPBProductInUsed)) {
    $productUseReady = 1;
}
?>
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
        $("#cloneProductPacket").remove();
        $("#ProductDefaultCost").autoNumeric({mDec: <?php echo $costDecimal; ?>, aSep: ','});
        $(".interger").autoNumeric({aDec: '.', mDec: 5, aSep: ','});
        $("#ProductDepartmentId").css({ width: 350 });
        $("#ProductCategory,#ProductPtypeId,#ProductBrandId").chosen({ width: 350 });
        $("#ProductVendorId,#ProductSizeId,#ProductColorId").chosen({ width: 410 });
        getCategory(1);
        
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
        ?>
        
          //quick add brand
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
                                                $("#ProductBrandId").html(result.option);//ProductBrandId_chosen

                                                // $(".chosen-choices").append('<li class="search-choice"><span>Mackbook M1 Max 2022</span><a class="search-choice-close" data-option-array-index="7"></a></li>');
                                                // $(".chosen-select").append('<li class="result-selected" style="" data-option-array-index="7">Mackbook M1 Max 2022</li>');
                                                
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

        //quick add product type
        $("#addNewTypeProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addProductType/"; ?>",
                beforeSend: function(){
                    $("#ProductBrandId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_PTYPE_MANAGEMENT_ADD; ?>',
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
                                var formName = "#PtypeAddProductTypeForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addProductType",
                                        data: $("#PtypeAddProductTypeForm").serialize(),
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
                                            createSysAct('Products', 'Quick Add Product Type', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#ProductPtypeId").html(result.option);//ProductBrandId_chosen
                                                $("#ProductPtypeId").trigger("chosen:updated");
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

        //Quick Add Size
        $("#addNewSizeProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addSize/"; ?>",
                beforeSend: function(){
                    $("#ProductSizeId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_SIZE_MANAGEMENT_ADD; ?>',
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
                                var formName = "#SizeAddSizeForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addSize",
                                        data: $("#SizeAddSizeForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add Size', 2, result);
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
                                            createSysAct('Products', 'Quick Add Product Size', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#ProductSizeId").html(result.option);
                                                $("#ProductSizeId").trigger("chosen:updated");
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

        //Quick Add Product Color
        $("#addNewColorProduct").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/products/addColor/"; ?>",
                beforeSend: function(){
                    $("#ProductColorId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_SIZE_MANAGEMENT_ADD; ?>',
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
                                var formName = "#ColorAddColorForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/addColor",
                                        data: $("#ColorAddColorForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Products', 'Quick Add Size', 2, result);
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
                                            createSysAct('Products', 'Quick Add Product Color', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Brand
                                                $("#ProductColorId").html(result.option);
                                                $("#ProductColorId").trigger("chosen:updated");
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
        if($allowAddUoM && $productUseReady == 0){
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
        } else if($productUseReady == 0) {
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
        $("#ProductEditForm").validationEngine();
        // Form Action Save
        $("#ProductEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                // if($("#ProductPgroupId").val() == null || $("#ProductPgroupId").val() == ""){
                //     alertSelectGroupProduct();
                //     return false;
                // }
                if($("#ProductSubCategory").val() == null || $("#ProductSubCategory").val() == ""){
                    alertSelectGroupProduct();
                    return false;
                }
                <?php
                if($productUseReady == 0) {
                ?>
                if($("#ProductPriceUomId").val() == null || $("#ProductPriceUomId").val() == ""){
                    alertSelectUoMProduct();
                    return false;
                }
                <?php
                }
                ?>
                $(".interger, #ProductDefaultCost").each(function(){
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
                    createSysAct('Products', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Products', 'Edit', 1, '');
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

        $("#applyVariants").unbind("click").click(function(){
            var obj = $(this);
            var ProductVendorId = $("#ProductVendorId").val();
            var ProductColorId  = $("#ProductSizeId").val();
            var ProductSizeId   = $("#ProductColorId").val();
            var product_id      = '<?php echo $this->data['Product']['id'];?>';
            if((ProductVendorId != '' && ProductVendorId!=null) && (ProductColorId != '' && ProductColorId != null) && (ProductSizeId !="" && ProductSizeId != null)){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . '/products'; ?>/variantsProduct",
                    data: 'product_id='+product_id+'&vendor_id='+$("#ProductVendorId").val()+'&color_id='+$("#ProductColorId").val()+'&size_id='+$("#ProductSizeId").val(),
                    beforeSend: function () {
                        $(".variantsDetail").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="top: 50%; left:50%; position: center;" />');
                    },
                    success: function (result) {
                        $(".variantsDetail").html('');
                        if(obj.is(':checked')){
                            obj.attr('checked',true);
                            $(".divPhotoOther").show();
                            $(".variantsDetail").html(result);
                        }else{
                            $(".variantsDetail").html('');
                        }
                        updateVariantNote();
                        clickUploadPhoto();
                        removeRowTable();
                        removePhotoByOne();
                    }
                });
            }else{
                $(".variantsDetail").html('');
                $("#applyVariants").attr('checked',false);
                alertSelectRequireField();
                return false;
            }
        });
        
        $("#ProductVendorId,#ProductSizeId,#ProductColorId").live('change',function(){
            var dataItem = 0;
            $("#loadMultiPhoto").html('');
            $("#tblMutiPhoto").find(".ProductPhotoMultiData").each(function(){
                if($(this).val() != ""){
                    dataItem++;
                }
            });
            if(dataItem>0){
                $.ajax({
                    dataType: "json",
                    type: "POST",
                    url: "<?php echo $this->base; ?>/products/removeMultiPhoto",
                    data: $(".<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $(".variantsDetail").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="top: 50%; left:50%; position: center;" />');
                    },
                    success: function(result){
                        $(".variantsDetail").html('');
                        $("#applyVariants").attr('checked',false);
                        // if(result.error == 1){
                        //     createSysAct('Image', 'Delete', 1, 'Error');
                        //     $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                        // }else {
                        //     createSysAct('Image', 'Delete', 0, 'Sucesss Delete');
                        //     // alert message
                        //     $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?></p>');
                        // }
                        // // alert message
                        // $("#dialog").dialog({
                        //     title: '<?php echo DIALOG_INFORMATION; ?>',
                        //     resizable: false,
                        //     modal: true,
                        //     width: 'auto',
                        //     height: 'auto',
                        //     position: 'center',
                        //     buttons: {
                        //         '<?php echo ACTION_CLOSE; ?>': function() {
                        //             $(this).dialog("close");
                        //         }
                        //     }
                        // });
                    }
                });
            }else{
                $(".variantsDetail").html('');
                $("#applyVariants").attr('checked',false);
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
        
        <?php
        if($productUseReady == 0) {
        ?>
        $("#ProductPriceUomId").change(function(){
            var companyId     = $("#ProductCompanyId").val();
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
        <?php
        }
        ?>
        $("#ProductBarcode").blur(function(){
            var companyId     = $("#ProductCompanyId").val();
            var puc           = $(this);
            var productId     = $("#ProductId").val();
            var imgLoad       = $(this).closest("tr").find(".loadSkuUomPro");
            var available     = $(this).closest("tr").find(".availableSkuUomPro");
            var noneAvailable = $(this).closest("tr").find(".noneAvailableSkuUomPro");
            if(companyId != ''){
                if(puc.val() != ''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkPucEdit/"+companyId+"/"+puc.val()+"/"+productId,
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
        <?php
        if($allowDelete){
        ?>
        $(".btnDeleteProduct").click(function(event){
            event.preventDefault();
            var name = "<?php echo $this->data['Product']['code'].' - '.$this->data['Product']['name']; ?>";
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                position: 'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/<?php echo $this->data['Product']['id']; ?>",
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                $(".btnBackProduct").click();
                                if(result != '<?php echo MESSAGE_DATA_HAVE_CHILD; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                    createSysAct('Products', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Products', 'Delete', 1, '');
                                    // alert message
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                // alert message
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position: 'center',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
        <?php
        }
        if($allowSetPrice){
        ?>
        $(".btnSetPriceProduct").click(function(event){
            event.preventDefault();
            var obj = $(this);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/products/productPrice/".$this->data['Product']['id']; ?>",
                beforeSend: function(){
                    obj.find("img").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    obj.find("img").attr('src', '<?php echo $this->webroot; ?>img/button/salary.png');
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo ACTION_SET_PRICE; ?>',
                        resizable: false,
                        modal: true,
                        width: '95%',
                        height: '570',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#ProductPrice";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/productPrice",
                                        data: $(":input").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                                createSysAct('Products', 'Set Price', 2, result);
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            }else {
                                                createSysAct('Products', 'Set Price', 1, '');
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
                    });
                }
            });
        });
        <?php
        }
        ?>
            
        $(".btnViewProductHistory").click(function(event){            
            event.preventDefault();
            var productId = $(this).attr('rel');
            $.ajax({
                type: "POST",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/viewProductHistory"; ?>/"+ productId,
                beforeSend: function() {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result) {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html(result);
                    $("#dialog").dialog({
                        title: '<?php echo TABLE_PRODUCT_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: '90%',
                        height: '500',
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
        });   
        
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
        
        // $(".hidePhotoOther").click(function(){
        //     $(".divPhotoOther").hide();
        //     $(".hidePhotoOther").hide();
        //     $(".showPhotoOther").show();
        // });
        
        // $(".showCatalog").click(function(){
        //     $(".divCatalog").show();
        //     $(".showCatalog").hide();
        // });
        
        // $(".showMainPhoto").click(function(){
        //     $(".divMainPhoto").show();
        //     $(".showMainPhoto").hide();
        // });
        
        // $(".hideMainPhoto").click(function(){
        //     $(".divMainPhoto").hide();
        //     $(".showMainPhoto").show();
        // });
        
        // $(".hideCatalog").click(function(){
        //     $(".divCatalog").hide();
        //     $(".showCatalog").show();
        // });
        
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
                        height: '650',
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
                var photoName = result;
                var variantId = 0;
                var vendorId  = 0;
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
                    height: '650',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        'Crop': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/products/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h+"&vendor_id="+vendorId+"&variant_id="+variantId+"&product_id=<?php echo $this->data['Product']['id'];?>",
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    $("#mainPhotoDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#<?php echo $photoNameHidden; ?>").val(result);
                                    $("#mainPhotoLabel").html(result);
                                    $("#ProductMainPhoto").hide();
                                    $("#btnRemoveMainPhoto").show();
                                    removeMainPhoto();
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
        updateVariantNote();
        onBlurSkuUomPro();
        changeInputCSSProduct();
        eventKeyMultiPhoto(0);
        clickUploadPhoto();
        removeMainPhoto();
        
        $("#loadMultiPhoto").html('');
    });

    function getCategory(departmentId){
        $("#ProductDepartmentId").unbind("change").change(function(){
            // var departmentId = $(this).val();
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
                        $("#ProductCategory").html(result).trigger('chosen:updated');
                    }
                });
            } else {
                $("#ProductCategory").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            }
        });
    }

    function loadMultiPhoto(){
        var item     = 0;
        var dataItem = 0;
        var dataMultiPhoto = "";
        var dataMultiColor = "";
        var dataMultiSize = "";
        var dataMultiNote = "";
        var url = '<?php echo $this->base; ?>';
        //Photo
        $("#loadMultiPhoto").html('');
        $("#tblMutiPhoto").find(".ProductPhotoMultiData").each(function(){
           dataMultiPhoto += "<input type='hidden' name='data[photo][]' value='"+$(this).val()+"' />";
           item++;
           if($(this).val() != ""){
               dataItem++;
           }
        });
        $("#loadMultiPhoto").html(dataMultiPhoto);
        if(item == 0){
            $("#variantsDetail").html("");
            $("#applyVariants").attr('checked',false);
            $(".<?php echo $frmName; ?>").removeAttr("action");
            $(".<?php echo $frmName; ?>").attr("action", url+"/products/upload/0");
        }
    }

    function removeMainPhoto(){
        $("#btnRemoveMainPhoto").unbind("click").click(function(){
            var newPhoto  = $("#<?php echo $photoNameHidden; ?>").val();
            var oldPhoto  = $("#oldMainPhoto").val();
            var vendorId  = 0;
            var colorId   = 0;
            var sizeId    = 0;
            if(newPhoto !="" || oldPhoto !="" ){
                if(newPhoto ==""){
                    photo = oldPhoto;
                }else{
                    photo = newPhoto;
                }
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure want to delete photo?</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position: 'center',
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_DELETE; ?>': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/products/removePhoto/<?php echo $this->data['Product']['id'];?>",
                                data: "photo="+photo+'&vendor_id='+vendorId+"&color_id="+colorId+"&size_id="+sizeId,
                                beforeSend: function(){
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                },
                                success: function(result){
                                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                    $("#mainPhotoLabel").html('');//show label main photo
                                    $("#ProductMainPhoto").val('').show();//hide upload file
                                    $("#btnRemoveMainPhoto").hide();//show button remove photo
                                    $("#<?php echo $photoNameHidden; ?>").val('');//reset photo hidden empty 
                                    $("#mainPhotoDisplay").attr('src','');
                                    $("#mainPhotoDisplay").attr("src", "<?php echo $this->webroot; ?>img/button/no-images.png").css({"width": "80", "height": "80"});
                                    if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                        createSysAct('Image', 'Delete', 2, result);
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                    }else {
                                        createSysAct('Image', 'Delete', 1, '');
                                        // alert message
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                    }
                                    // alert message
                                    $("#dialog").dialog({
                                        title: '<?php echo DIALOG_INFORMATION; ?>',
                                        resizable: false,
                                        modal: true,
                                        width: 'auto',
                                        height: 'auto',
                                        position: 'center',
                                        buttons: {
                                            '<?php echo ACTION_CLOSE; ?>': function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            });
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }

    function removeRowTable(){
        $(".btnRemoveRowPhoto").each(function(){
            rel = $(this).attr("rel");
            $("#btnRemoveRowPhoto_"+rel).unbind("click").click(function(){
                var obj = $(this);
                var id = obj.attr("rel");
                var photo =  $("#ProductPhotoMultiData_"+id).val();
                var is_row = 1;
                if(photo!=""){
                    removeMultiPhoto(obj,id,is_row);
                }else{
                    removeCurrentRowTable(id,obj);
                }
            });
        });
    }

    function removePhotoByOne(){
        $(".btnRemoveMultiPhoto").each(function(){
            rel = $(this).attr("rel");
            $("#btnRemoveMultiPhoto_"+rel).unbind("click").click(function(){
                obj = $(this);
                var id = obj.attr("rel");
                var is_row = 0;
                removeMultiPhoto(obj,id,is_row);
            });
        });
    }

    function removeCurrentRowTable(id,obj){
        var currentTr = obj.closest("tr");
        if(id!='' && id!=null){
            var photoName   = '';
            var vendorId    = $("#variant_vendor_"+id).val();
            var colorId     = $("#variant_color_"+id).val();
            var sizeId      = $("#variant_size_"+id).val();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/products/removePhotoTmp",
                data: "photo="+photoName+"&vendor_id="+vendorId+"&color_id="+colorId+"&size_id="+sizeId+"&product_id=<?php echo $this->data['Product']['id'];?>",
                beforeSend: function(){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                }
            });
        }
        currentTr.remove();
        loadMultiPhoto();
    }

    function removeMultiPhoto(obj,id,is_row){
        var rel         = id;
        var photoName   = $("#ProductPhotoMultiData_"+id).val();
        var vendorId    = $("#variant_vendor_"+id).val();
        var colorId     = $("#variant_color_"+id).val();
        var sizeId      = $("#variant_size_"+id).val();
        if(photoName!="" && vendorId!="" && colorId!="" && sizeId!=""){
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure want to delete photo?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                position: 'center',
                buttons: {
                    '<?php echo ACTION_CLOSE; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ?>/products/removePhoto/<?php echo $this->data['Product']['id'];?>",
                            data: "photo="+photoName+"&vendor_id="+vendorId+"&color_id="+colorId+"&size_id="+sizeId,
                            beforeSend: function(){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                $("#ProductPhotoMultiData_"+rel).val("");//Photo value hidden
                                $("#tblPhotoDisplay_"+rel).hide();//Show table td image
                                $("#ProductPhoto_"+rel).val("").show();//File upload image
                                $("#labelPhoto_"+rel).html("").hide();//Label image
                                $("#btnRemoveMultiPhoto_"+rel).hide();//Btn remove image
                                if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                    createSysAct('Image', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Image', 'Delete', 1, '');
                                    // alert message
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                // alert message
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position: 'center',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                                if(is_row==1){
                                    removeCurrentRowTable(rel,obj);
                                }
                                clickUploadPhoto();
                                loadMultiPhoto();
                            }
                        });
                        $(this).dialog("close");
                    }
                }
            });
        }
    }

    function clickUploadPhoto(){
        $("#tblMutiPhoto").find(".ProductPhoto").each(function(){
            var rel  = $(this).attr("rel");
            var access = 1;
            $("#ProductPhoto_"+rel).unbind("change").live('change', function(){
                //Set Rel Multi Photo
                var url = '<?php echo $this->base; ?>';
                if(rel == 0){
                    rel = "";
                }
                // prevent multi submit form upload image on the same time
                if($(this).find(".ProductPhotoMultiData").val()!=''){
                    $(".<?php echo $frmName; ?>").removeAttr("action");
                    $(".<?php echo $frmName; ?>").attr("action", url+"/products/upload/"+((rel=="")?0:rel));
                    $(".<?php echo $frmName; ?>").submit();
                    eventKeyMultiPhoto(rel);
                    // access = 0;
                }
            });
        });
    }
    
    function updateVariantNote(){
        $(".variant_note").each(function(){
            var rel        = $(this).attr("rel");
            var productId  = '<?php echo $this->data['Product']['id'];?>';
            var vendorId   = $("#variant_vendor_"+rel).val();
            var colorId    = $("#variant_color_"+rel).val();
            var sizeId     = $("#variant_size_"+rel).val();
            $("#variant_note_"+rel).blur(function(){
                if(productId!='' && vendorId>0 && colorId>0 && sizeId>0 && $(this).val()!=''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/products'; ?>/updateVariantNote",
                        data: 'variant_note='+$(this).val()+'&product_id='+productId+"&vendor_id="+vendorId+"&color_id="+colorId+"&size_id="+sizeId,
                        beforeSend: function () {
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function (result) {
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        }
                    });
                }
            });
        });
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
                        height: '650',
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
                var relNew      = explodeMultiPhoto[1];
                var photoFolder = '';
                var photoName   = explodeMultiPhoto[0];

                var vendorId    = $("#variant_vendor_"+relNew).val();
                var colorId     = $("#variant_color_"+relNew).val();
                var sizeId      = $("#variant_size_"+relNew).val();

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
                    height: '650',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        'Crop': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/products/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h+ "&variant_id=" + relNew+"&vendor_id="+vendorId+"&color_id="+colorId+"&size_id="+sizeId+ "&product_id=<?php echo $this->data['Product']['id'];?>",
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    photoFolder="public/product_photo/tmp/";
                                    
                                    var imageSet = '<img class="photoDisplay_'+relNew+'" class="photoDisplay" alt="" src="<?php echo $this->webroot . 'public/product_photo/tmp/'; ?>'+result+'" style=" width:80px; height:80px;" />' ;
                                    $("#tblPhotoDisplay_"+relNew).html(imageSet);
                                    
                                    $("#ProductPhotoMultiData_"+relNew).val("");//Photo value hidden
                                    $("#ProductPhotoMultiData_"+relNew).val(result);//Photo value hidden
                                    $("#photoDisplay_"+relNew).attr("src", "");
                                    $("#photoDisplay_"+relNew).attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#tblPhotoDisplay_"+relNew).show();//Show table td image
                                    $("#btnRemoveMultiPhoto_"+relNew).show();//Btn remove image
                                    $("#ProductPhoto_"+relNew).hide();//File upload image
                                    $("#labelPhoto_"+relNew).html(result).show();//Label image
                                    loadMultiPhoto();
                                }
                            });
                        }
                    }
                });
            }
        });
        removeRowTable();
        removePhotoByOne();
    }
    
    // function addMultiPhoto(){
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
    
    function resetFormProduct(){
        $("#ProductEditForm").find("input[value!='<?php echo INPUT_SELECT; ?>']").val('');
        // SELECT
        $("#ProductIsLots").find("option[value='0']").attr("selected", true);
        $("#ProductIsExpiredDate").find("option[value='0']").attr("selected", true);
        <?php
        if($productUseReady == 0) {
        ?>
        $("#ProductPriceUomId").find("option[value='']").attr("selected", true);
        <?php
        }
        ?>
        // Textarea
        $("#ProductEditForm").find("textarea").val('');
        // INPUT FILE
        $("#divProductPhoto").html(divPhotoUpload);
        // UOM DETAIL
        $("#dvSkuUomPro").html('');
        // SYMBOL CHECK CODE
        $(".availableSkuUomPro").hide();
        $(".noneAvailableSkuUomPro").hide();
        // Product Group
        $("#ProductPgroupId_chzn").find(".chzn-choices").find(".search-choice-close").click();
    }
    
    function onBlurSkuUomPro(){
        $(".skuUomPro").unbind("blur").unbind("keyup").unbind("keypress").unbind("focus");
        $(".skuUomPro").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){                                    
                return false;
            }
        });
        $(".skuUomPro").blur(function(){
            var companyId     = $("#ProductCompanyId").val();
            var sku           = $(this);
            var proSku        = $(this).attr("sku-uom");
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
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkSkuUomEdit/"+companyId+"/"+sku.val()+"/0/"+proSku,
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
        $("#ProductEditForm").find("label").removeAttr("class");
        $("#ProductEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'ProductCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        $("label[for='ProductPhoto']").removeAttr("class");
        $("label[for='ProductPhoto']").addClass(cssStyle);
        // Input & Select
        $("#ProductEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#ProductPhoto").removeClass(cssRemove);
        $("#ProductPhoto").addClass(cssStyle);
        $("#ProductPhoto").attr("disabled", disabled);
        $("#ProductFileCatalog").attr("disabled", disabled);
        $("#ProductEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'ProductCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        // Read Only
        $("#ProductEditForm").find("input").attr("readonly", disabled);
        $("#ProductEditForm").find("textarea").attr("readonly", disabled);
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
<div class="divMainPhoto" style="width: 100%; float: left;">
    <form id="<?php echo $frmNameMain; ?>" action="<?php echo $this->base; ?>/products/upload" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend><span class="hideMainPhoto" style="cursor: pointer;"><?php __(TABLE_HIDE_MAIN_PHOTO); ?></span></legend>
            <table>
                <tr>
                    <td colspan="2">
                        <?php 
                            $mainPhoto = $this->data['Product']['photo'];
                            $disply='display:none;';
                            $pathFolder = 'img/button/no-images.png';
                            if(!empty($mainPhoto)){
                                $disply='';
                                $pathFolder = 'public/product_photo/'.$mainPhoto;
                            }
                        ?>
                        <img alt="" id="mainPhotoDisplay" src="<?php echo $this->webroot. $pathFolder;?>" style="width: 80px; height: 80px;" />
                    </td>
                    <td valign="top">
                        <label id="mainPhotoLabel"><?php echo  $mainPhoto?></label>
                        <img id="btnRemoveMainPhoto" class="btnRemoveMainPhoto" rel="" alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" align="absmiddle" style="cursor: pointer;text-align:left;<?php echo $disply;?>" onmouseover="Tip('Remove')" />
                        <input type="file" name="photoMain" id="ProductMainPhoto" style="<?php if(!empty($mainPhoto)){ ?>display:none;<?php } ?>" />
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>
</div>
<div style="clear:both;"></div>
<div class="divPhotoOther" style="width: 100%; float: left; margin-top:5px;">
    <form class="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/products/upload/0" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend><?php __(TABLE_VARIANTS_DETAIL); ?></legend>
            <div class="variantsDetail" id="variantsDetail">
                <table id="tblMutiPhoto" class="table">
                    <tr>
                        <th style="width:13%;" class="first">Vendor</th>
                        <th style="width:12%;">Size</th>
                        <th style="width:12%;">Color</th>
                        <th style="width:30%;">Note</th>
                        <th style="">Photo</th>
                        <th style="width:100px;">Action</th>
                    </tr>
                    <?php
                    $vendorName  = '';
                    $sizeName    = '';
                    $colorName   = '';
                    $indexPhoto  = 0;
                    $vendorId    = '';
                    $colorId     = '';
                    $sizeId      = '';
                    $photoVariant= '';
                    $note        = '';
                    $id          = '';
                    $sqlVariant = mysql_query("SELECT * FROM product_variants WHERE product_id = '".$this->data['Product']['id']."' ");
                    if(mysql_num_rows($sqlVariant)){
                        while($rowVariant=mysql_fetch_array($sqlVariant)){
                            $idVariant = $rowVariant['id'];
                            $vendorId  = $rowVariant['vendor_id'];
                            $colorId   = $rowVariant['color_id'];
                            $sizeId    = $rowVariant['size_id'];
                            $photoVariant = $rowVariant['photo'];
                            $note      = $rowVariant['note'];
                            $sqlVendor = mysql_query("SELECT name FROM vendors WHERE id={$vendorId} ORDER BY id ASC");
                            if(mysql_num_rows($sqlVendor)){
                                $rowVendor  = mysql_fetch_array($sqlVendor);
                                $vendorName = $rowVendor[0];
                            }
                            $sqlColor = mysql_query("SELECT name FROM colors WHERE id={$colorId} ORDER BY id ASC");
                            if(mysql_num_rows($sqlColor)){
                                $rowColor  = mysql_fetch_array($sqlColor);
                                $colorName = $rowColor[0];
                            }
                            $sqlSize = mysql_query("SELECT name FROM sizes WHERE id={$sizeId} ORDER BY id ASC");
                            if(mysql_num_rows($sqlSize)){
                                $rowSize  = mysql_fetch_array($sqlSize);
                                $sizeName = $rowSize[0];
                            }
                    ?>
                    <tr class="tblMutiPhotoList">
                        <td class="first">
                            <?php echo $vendorName;?>
                            <input name="data[Product][vendor_id][]" type="hidden" value="<?php echo $vendorId;?>" id="variant_vendor_<?php echo $idVariant;?>" class="variant_vendor"/>
                        </td>
                        <td>
                            <?php echo $sizeName;?>
                            <input name="data[Product][size_id][]" type="hidden" value="<?php echo $sizeId;?>" id="variant_size_<?php echo $idVariant;?>" class="variant_size"/>
                        </td>
                        <td>
                            <?php echo $colorName;?>
                            <input name="data[Product][color_id][]" type="hidden" value="<?php echo $colorId;?>" id="variant_color_<?php echo $idVariant;?>" class="variant_color"/>
                        </td>
                        <td>
                            <?php echo $this->Form->textarea('note', array('value'=>$note,'name'=>'data[Product][note][]','class'=>'variant_note','rel'=>$idVariant,'id'=>'variant_note_'.$idVariant,'style' => 'width:95%; height:35px;')); ?>
                        </td>
                        <td style="text-align:left; padding:0px 0px 0px 0px;">
                            <table style="width:100%;border:none; margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;text-align:left;">
                                <tr>
                                    <td style="border:none;text-align:left; width:85px; <?php if($photoVariant == ''){?>display:none;<?php } ?> padding:0px 0px 0px 0px;" id="tblPhotoDisplay_<?php echo $idVariant;?>">
                                        <img class="photoDisplay_<?php echo $idVariant;?>" class="photoDisplay" alt="" <?php echo $photoVariant != '' ? 'src="' . $this->webroot . 'public/product_photo/' . $photoVariant . '"' : ''; ?> src="<?php echo $this->webroot . 'public/product_photo/<?php echo $photoVariant;?>'; ?>" style=" width:80px; height:80px;" />
                                    </td>
                                    <td style="border:none;text-align:left; padding:0px 0px 0px 0px;">
                                        <span id="labelPhoto_<?php echo $idVariant;?>"><?php echo $photoVariant;?></span>
                                        <img id="btnRemoveMultiPhoto_<?php echo $idVariant;?>" rel="<?php echo $idVariant;?>" alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveMultiPhoto" align="absmiddle" style="cursor: pointer;text-align:left;<?php if($photoVariant == ''){?>display:none;<?php }?>" onmouseover="Tip('Remove')" />
                                    
                                        <input type="file" id="ProductPhoto_<?php echo $idVariant;?>" class="ProductPhoto" rel="<?php echo $idVariant;?>" name="photo_<?php echo $idVariant;?>" style="text-align:left; <?php if($photoVariant != ''){?>display:none;<?php } ?>"/>
                                        <input name="data[Product][photo][]" type="hidden" id="ProductPhotoMultiData_<?php echo $idVariant;?>" class="ProductPhotoMultiData" value="<?php echo $photoVariant;?>"/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align:center;">
                            <img id="btnRemoveRowPhoto_<?php echo $idVariant;?>" class="btnRemoveRowPhoto"  rel="<?php echo $idVariant;?>" alt="Remove" src="<?php echo $this->webroot . 'img/button/pos/remove-icon-png-25.png'; ?>" align="absmiddle" style="cursor: pointer; width:25px; height:25px;" onmouseover="Tip('Remove')" />
                        </td>
                    </tr>
                        <?php
                        $indexPhoto++;
                        }
                    }else{
                    ?>
                    <tr class="tblMutiPhotoList">
                        <td class="first" colspan="6" style="text-align:center;">
                            No Record Variant Detail
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </fieldset>
    </form>



    <!-- <form class="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/products/upload/0" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend><?php __(TABLE_VARIANTS_DETAIL); ?></legend>
            <div class="variantsDetail" id="variantsDetail">

            </div>
        </fieldset>
    </form> -->
</div>
<?php 
echo $this->Form->create('Product', array('inputDefaults' => array('label' => false))); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code'); 
$indexPhoto = 0;
$queryProductPhoto = mysql_query("SELECT * FROM product_photos WHERE product_id = '".$this->data['Product']['id']."'");
if(mysql_num_rows($queryProductPhoto)){
    while($rowProductPhoto = mysql_fetch_array($queryProductPhoto)){
?>
        <input type="hidden" name="data[old_photo][]" value="<?php echo $rowProductPhoto['photo']; ?>" />
<?php
    }
}
$departmentId = '';
$category     = '';
$subCategory  = '';
$subSubCate   = '';
$sqlDep=mysql_query("SELECT department_id, id, parent_id FROM pgroups WHERE id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$this->data['Product']['id'].") LIMIT 1");
if(mysql_num_rows($sqlDep)){
    $rowDep = mysql_fetch_array($sqlDep);
    $departmentId = $rowDep['department_id'];
    $subSubCate   = $rowDep['id'];//2, 288
    if($rowDep['parent_id']){
        // echo 'departmentId='.$departmentId.'****$category='.$category.'****$subCategory='.$subCategory;
        $sqlS = mysql_query("SELECT id, parent_id FROM pgroups WHERE id = ".$subSubCate);
        $rowS = mysql_fetch_array($sqlS);
        $subCategory = $rowS['id'];
        if($rowS['parent_id']){
            $sqlC = mysql_query("SELECT id FROM pgroups WHERE id = ".$rowS['parent_id']);
            $rowC = mysql_fetch_array($sqlC);
            $category = $rowC['id'];
        }
    }
}

?>
<input type="hidden" name="data[Product][old_photo]" id="oldMainPhoto" value="<?php echo $this->data['Product']['photo']; ?>" />
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Product][new_photo]" />
<div id="loadMultiPhoto"></div>
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
                                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 80%;')); ?>
                            </div>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td style="width: 30%;"><label for="ProductDepartmentId"><?php echo MENU_DEPARTMENT; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('department_id', array('selected' => $departmentId, 'label' => false, 'name' => '')); ?>
                            </div>
                        </td>
                    </tr> -->
                    <tr>
                        <td style="width: 30%;"><label for="ProductCategory"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="" id="ProductCategory" style="width: 350px;" class="validate[required]">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php 
                                    $sqlC = mysql_query("SELECT * FROM pgroups WHERE parent_id IS NULL AND department_id = ".$departmentId." AND is_active = 1 ORDER BY name ASC;");
                                    while($rowC = mysql_fetch_array($sqlC)){
                                    ?>
                                    <option value="<?php echo $rowC['id']; ?>" <?php if($rowC['id'] == $category){ ?>selected=""<?php } ?>><?php echo $rowC['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductSubCategory"><?php echo 'Sub Category'; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="data[Product][pgroup_id]" id="ProductSubCategory" style="width: 350px;" class="validate[required]">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php 
                                    if($subCategory != ''){
                                        $sqlS = mysql_query("SELECT * FROM pgroups WHERE id = ".$subCategory." AND is_active = 1 ORDER BY name ASC;");
                                        while($rowS = mysql_fetch_array($sqlS)){
                                    ?>
                                    <option value="<?php echo $rowS['id']; ?>" <?php if($rowS['id'] == $subCategory){ ?>selected=""<?php } ?>><?php echo $rowS['name']; ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td style="width: 30%;"><label for="ProductPgroupId"><?php echo 'Sub-Sub Category'; ?> <span class="red">*</span>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <select name="data[Product][pgroup_id]" id="ProductPgroupId" style="width: 350px;">
                                    <option value=""><?php echo INPUT_SELECT; ?></option>
                                    <?php 
                                    if($subCategory != ""){
                                        $sqlPg = mysql_query("SELECT * FROM pgroups WHERE parent_id = ".$subCategory." AND is_active = 1 ORDER BY name ASC;");
                                        while($rowPg = mysql_fetch_array($sqlPg)){
                                    ?>
                                    <option value="<?php echo $rowPg['id']; ?>" <?php if($rowPg['id'] == $subSubCate){ ?>selected=""<?php } ?>><?php echo $rowPg['name']; ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr> -->
                    <tr>
                        <td style="width: 30%;"><label for="ProductBrandId"><?php echo TABLE_BRAND; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <table>
                                    <tr>
                                        <td>
                                            <?php echo $this->Form->input('brand_id', array('selected' => $productBrandSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'id' => 'ProductBrandId',  'class' => 'chzn-select', 'style' => 'width: 350px;')); ?>
                                        </td>
                                        <td>
                                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" onmouseover="Tip('<?php echo MENU_BRAND_MANAGEMENT_ADD; ?>')" id="addNewBrandProduct" style="width:16px;height:16px;" />    
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductPtypeId"><?php echo 'Product Type'; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <table>
                                    <tr>
                                        <td>
                                            <?php echo $this->Form->input('ptype_id', array('selected' => $pTypesSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 350px;')); ?>
                                        </td>
                                        <td>
                                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" onmouseover="Tip('<?php echo MENU_BRAND_MANAGEMENT_ADD; ?>')" id="addNewTypeProduct" style="width:16px;height:16px;" />    
                                        </td>
                                    </tr>
                                </table>
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
                        <td><label for="ProductBarcode"><?php echo TABLE_BARCODE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('barcode', array('div' => false, 'style' => 'width: 80%;', 'class' => 'validate[required]')); ?>
                                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                                <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('UPC have existed!')" class="availableSkuUomPro" style="display:none;" /> 
                                <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" />
                            </div>
                            <div class="inputContainer availableSkuUomPro" style="width: 100%; color: red; display: none;">
                                <?php echo MESSAGE_UPC_EXIST_IN_SYSTEM; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
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
                        <td><label for="ProductDefaultCost"><?php echo TABLE_UNIT_COST; ?> ($) <span class="red">*</span> :</label></td>
                        <td style="height: 30px;">
                            <div class="inputContainer" style="width: 100%;">
                                <?php
                                if ($productUseReady == 0) {
                                    echo $this->Form->text('default_cost', array('class' => 'validate[required] float', 'value' => number_format($this->data['Product']['default_cost'], $costDecimal), 'style' => 'width: 80%;'));
                                } else {
                                    echo number_format($this->data['Product']['unit_cost'], $costDecimal);
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td><label for="ProductPriceUomId"><?php echo TABLE_SKU; ?> <span class="red">*</span> :</label></td>
                        <td style="height: 30px;">
                            <?php
                            $sql = mysql_query("SELECT name FROM uoms WHERE id = " . $this->data['Product']['price_uom_id']);
                            $uom = mysql_fetch_array($sql);
                            if ($productUseReady == 0) {
                            ?>
                                <div class="inputContainer" style="width: 100%;">
                                    <?php echo $this->Form->input('price_uom_id', array('options' => $uoms, 'name' => 'data[Product][price_uom_id]', 'empty' => INPUT_SELECT, 'label' => false, 'div' => false, 'style' => 'width: 200px;', 'id' => 'ProductPriceUomId')); ?>
                                    <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" id="loadUomPro" style="display:none;" />
                                </div>
                            <?php
                            } else {
                                echo $uom[0];
                                echo '&nbsp;&nbsp;&nbsp;';
                                echo '<a href="" class="btnViewProductHistory" rel="' . $this->data['Product']['id'] . '" name="' . $this->data['Product']['name'] . '"><img alt="View" onmouseover="Tip(\'' . ACTION_VIEW . '\')" src="' . $this->webroot . 'img/button/view.png" /></a> ';
                            
                            ?>
                                <input type="hidden" name="data[Product][price_uom_id]" value="<?php echo $this->data['Product']['price_uom_id']; ?>" id="ProductPriceUomId" />
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" id="dvSkuUomPro">
                            <?php
                            $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $this->data['Product']['price_uom_id'] . "
                                                    UNION
                                                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $this->data['Product']['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $this->data['Product']['price_uom_id'] . ")
                                                    ORDER BY conversion ASC");
                            $index = 1;
                            if (mysql_num_rows($query) > 1) {
                            ?>
                            <fieldset>
                                <legend><?php __(TABLE_CODE . " of " . TABLE_UOM); ?></legend>
                                <table style="width:90%">
                                    <?php
                                    while ($data = mysql_fetch_array($query)) {
                                        if($data['id'] != $this->data['Product']['price_uom_id']){
                                            $value = "";
                                            $id = "";
                                            $s = mysql_query("SELECT id, sku FROM product_with_skus WHERE product_id = " . $this->data['Product']['id'] . " AND uom_id=" . $data['id']);
                                            if (mysql_num_rows($s)) {
                                                $skuUom = mysql_fetch_array($s);
                                                $id = $skuUom[0];
                                                $value = $skuUom[1];
                                            }
                                    ?>
                                        <tr>
                                            <td style="width: 15%;"><label for="skuUomPro_<?php echo $data['id']; ?>"><?php echo TABLE_CODE; ?></label> :</td>
                                            <td>
                                                <input type="hidden" value="<?php echo $data['id']; ?>" name="data[sku_uom][]" />
                                                <input type="text" sku-uom="<?php echo $id; ?>" value="<?php echo $value; ?>" name="data[sku_uom_value][]" id="skuUomPro_<?php echo $data['id']; ?>" class="skuUomPro" style="width: 60%;" /> of <?php echo $data['name']; ?>
                                                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                                                <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('SKU of this uom have existed!')" class="availableSkuUomPro" style="display:none;" /> 
                                                <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" />
                                            </td>
                                        </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </fieldset>
                            <?php
                            }
                            ?>
                        </td>
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
                                <table>
                                    <tr>
                                        <td>
                                        <?php echo $this->Form->input('vendor_id', array('selected' => $vendorsSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                                        </td>
                                        <!-- <td>
                                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" onmouseover="Tip('<?php echo MENU_VENDOR_ADD; ?>')" id="addVendorProduct" style="width:16px;height:16px;" />    
                                        </td> -->
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductSizeId"><?php echo TABLE_SIZE; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                            <table>
                                    <tr>
                                        <td>
                                            <?php echo $this->Form->input('size_id', array('selected' => $sizesSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                                        </td>
                                        <td>
                                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" onmouseover="Tip('<?php echo MENU_SIZE_MANAGEMENT_ADD; ?>')" id="addNewSizeProduct" style="width:16px;height:16px;" />    
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductColorId"><?php echo TABLE_COLOR; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <table>
                                    <tr>
                                        <td>
                                        <?php echo $this->Form->input('color_id', array('selected' => $colorsSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 93%;')); ?>
                                        </td>
                                        <td>
                                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" onmouseover="Tip('<?php echo MENU_COLOR_ADD; ?>')" id="addNewColorProduct" style="width:16px;height:16px;" />    
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;"><label for="ProductApplyVariant"><?php echo TABLE_APPLY_VARIANTS; ?> :</label></td>
                        <td>
                        <input type="checkbox" <?php if($indexPhoto>0){ ?> checked="checked" <?php } ?> id="applyVariants" class="applyVariants">
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset style="">
                <legend><?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?></legend>
                <table width="90%" cellpadding="5">
                    <tr>
                        <td><label for="ProductPackingSize"><?php echo TABLE_PACKING_SIZE; ?>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->text('packing_size', array('class' => 'interger', 'style' => 'width: 190px;', 'div' => false)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr <?php if($allowLost == false){ ?> style="display: none;"<?php } ?>>
                        <td style="width: 30%;"><label for="ProductIsLots"><?php echo TABLE_TRACK_LOT_SERIES; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php
                                if($rowStock[0] == 0){
                                ?>
                                <select name="data[Product][is_lots]" id="ProductIsLots">
                                    <option value="0" <?php if($this->data['Product']['is_lots'] == 0){ ?>selected="selected"<?php } ?>><?php echo ACTION_NO; ?></option>
                                    <option value="1" <?php if($this->data['Product']['is_lots'] == 1){ ?>selected="selected"<?php } ?>><?php echo ACTION_YES; ?></option>
                                </select>
                                <?php
                                } else {
                                    if($this->data['Product']['is_lots'] == 0){
                                        echo ACTION_NO;
                                    } else {
                                        echo ACTION_YES;
                                    }
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr <?php if($allowExpired == false){ ?> style="display: none;"<?php } ?>>
                        <td style="width: 30%;"><label for="ProductIsExpiredDate"><?php echo TABLE_TRACK_EXPIRY_DATE; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php
                                if($rowStock[0] == 0){
                                ?>
                                <select name="data[Product][is_expired_date]" id="ProductIsExpiredDate">
                                    <option value="0" <?php if($this->data['Product']['is_expired_date'] == 0){ ?>selected="selected"<?php } ?>><?php echo ACTION_NO; ?></option>
                                    <option value="1" <?php if($this->data['Product']['is_expired_date'] == 1){ ?>selected="selected"<?php } ?>><?php echo ACTION_YES; ?></option>
                                </select>
                                <?php
                                } else {
                                    if($this->data['Product']['is_expired_date'] == 0){
                                        echo ACTION_NO;
                                    } else {
                                        echo ACTION_YES;
                                    }
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ProductReorderLevel"><?php echo GENERAL_REORDER_LEVEL; ?>:</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->text('reorder_level', array('class' => 'interger', 'style' => 'width: 190px;', 'div' => false)); ?> <span id="ProductReorderLevelUoM"><?php echo $uom[0]; ?></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="ProductSpec"><?php echo TABLE_SPEC; ?> :</label></td>
                        <td><?php echo $this->Form->textarea('spec', array('style' => 'height: 52px;')); ?></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="ProductDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
                        <td style="vertical-align: top;"><?php echo $this->Form->textarea('description', array('style' => 'height: 52px;')); ?></td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
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
    <div style="float:right;">
         <?php
        if($allowDelete){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteProduct">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <?php echo ACTION_DELETE; ?>
            </a>
        </div>
        <?php
        }
        if($allowSetPrice){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnSetPriceProduct">
                <img src="<?php echo $this->webroot; ?>img/button/salary.png" alt=""/>
                <?php echo ACTION_SET_PRICE; ?>
            </a>
        </div>
        <?php
        }
        ?>
    </div>
    <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>