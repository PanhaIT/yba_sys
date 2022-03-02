<?php 
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$this->element('check_access');
include("includes/function.php");

$displayProduct = "displayProduct".rand();
$changeDepartmentProductView = "changeDepartmentProductView".rand();
$changeCategoryProductView = "changeCategoryProductView".rand();
$ProductCategory = "ProductCategory".rand();
$ProductSubCategory = "ProductSubCategory".rand();

// Authentication
$allowAdd      = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowViewCost = checkAccess($user['User']['id'], $this->params['controller'], 'viewCost');
$allowExport   = checkAccess($user['User']['id'], $this->params['controller'], 'exportExcel');
$allowSetPrice = checkAccess($user['User']['id'], $this->params['controller'], 'productPrice');
$tblName       = "tbl" . rand(); 
$allowBarcode  = false;
$sqlSetting    = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 1 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 1){
        if($rowSetting['is_checked'] == 1){
            $allowBarcode = true;
        }
    }
}
?>
<style>
    .text_colsan{
        display: table-cell;
    }
</style>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableProductDashBoard;
    var data = "all";
    var tabProductId  = $(".ui-tabs-selected a").attr("href");
    function calcDataTableHeight() {
        var tableHeight = $(window).height() - ($(".ui-layout-north").height() + $(".ui-layout-south").height() + $(".ui-tabs-nav").height() + $("#divHeader").height() + 37 + 22 + 56 + 110.3);
        return tableHeight;
    }
    $(document).ready(function(){
        // Prevent Key Enter
        // preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');      
        oTableProductDashBoard = $("#<?php echo $tblName; ?>").dataTable({
            "aaSorting": [[0, 'DESC']],
            "bProcessing": true,
            "scrollX": "200vh",
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#<?php echo $changeCategoryProductView; ?>").val()+"/"+$("#<?php echo $displayProduct; ?>").val()+"/"+$("#<?php echo $changeDepartmentProductView; ?>").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Create ID for Action
                setIdActionProduct();
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');                
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(1)").css("width", "4%");
                $("#<?php echo $tblName; ?> td:nth-child(2)").css("width", "7%");
                $("#<?php echo $tblName; ?> td:nth-child(3)").css("width", "20%");
                $("#<?php echo $tblName; ?> tr").click(function(){
                    changeBackgroupProduct();
                    $(this).closest("tr").css('background','#eeeca9');
                });
                // Double Click View
                $("#<?php echo $tblName; ?> tr").unbind("dblclick").dblclick(function(){
                    var id   = $(this).find(".btnViewProductView").attr('rel');
                    var type = $(this).closest("tr").find("td:eq(1)").text();
                    if(type == 'Service' || type == 'Product'){
                        var url  = "view";
                        if(type == 'Service'){
                            url  = "viewService";
                        }
                        var leftPanel  = $("#dashboardProduct");
                        var rightPanel = leftPanel.parent().find(".rightPanel");
                        leftPanel.hide("slide", { direction: "left" }, 500, function() {
                            rightPanel.show();
                        });
                        rightPanel.html("<?php echo ACTION_LOADING; ?>");
                        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+url+"/" + id);
                    }
                });
                
                $(".btnViewProductView").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var type = $(this).closest("tr").find("td:eq(1)").text();
                    var url  = "view";
                    if(type == 'Service'){
                        url  = "viewService";
                    }
                    var leftPanel  = $("#dashboardProduct");
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+url+"/" + id);
                });
                
                $(".btnEditProductView").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var type = $(this).closest("tr").find("td:eq(1)").text();
                    var url  = "edit";
                    if(type == 'Service'){
                        url  = "editService";
                    }
                    var leftPanel  = $("#dashboardProduct");
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+url+"/" + id);
                });
                
                $(".btnDeleteProductView").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var type = $(this).closest("tr").find("td:eq(1)").text();
                    var url  = "delete";
                    if(type == 'Service'){
                        url  = "deleteService";
                    }
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
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/"+url+"/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableProductDashBoard.fnDraw(false);
                                        if(result != '<?php echo MESSAGE_DATA_HAVE_CHILD; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                            createSysAct(type, 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct(type, 'Delete', 1, '');
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

                $(".btnActiveInactiveProduct").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var isActive = $(this).attr('is-active');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_OPEN; ?> <b>' + name + '</b>?</p>');
                    if(isActive==1){
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CLOSE; ?> <b>' + name + '</b>?</p>');
                    }
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
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    dataType: "json",
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/activeInactiveProduct/"+id+"/"+isActive,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableProductDashBoard.fnDraw(false);
                                        // alert message
                                       if(result.error == 0) {
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                                        }else if(result.error == 1) {
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else if(result.error == 2){
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>This product have stock, So It can not close.</p>');
                                        }
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_INFORMATION; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 'auto',
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
                
                $(".viewInventoryProduct").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('data');
                    var code = $(this).closest("tr").find("td:eq(3)").text();
                    var name = $(this).closest("tr").find("td:eq(1)").text();
                    if(id != ''){
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewProductInventory/" + id,
                            data: "",
                            beforeSend: function(){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                // alert message
                                $("#dialog").html(result);
                                $("#dialog").dialog({
                                    title: code+" - "+name,
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
                    }
                });
                <?php
                if($allowSetPrice){
                ?>
                $(".setProductPrice").unbind("click").click(function(){
                    var id = $(this).attr('data');
                    $.ajax({
                        type:   "POST",
                        url:    "<?php echo $this->base . "/products/productPrice/"; ?>"+id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            $("#dialog").html(msg);
                            $("#dialog").dialog({
                                title: '<?php echo ACTION_SET_PRICE; ?>',
                                resizable: false,
                                modal: true,
                                width: '95%',
                                height: '700',
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
                                                    oCache.iCacheLower = -1;
                                                    oTableProductDashBoard.fnDraw(false);
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
                
                $(".setServicePrice").unbind("focus").focus(function(){
                    if(replaceNum($(this).val()) == 0){
                        $(this).val('');
                    }
                });
                
                $(".setServicePrice").unbind("blur").blur(function(){
                    if($(this).val() == ""){
                        $(this).val('0');
                    }
                    var id   = $(this).attr('data');
                    var code = $(this).closest("tr").find("td:eq(3)").text();
                    var name = $(this).closest("tr").find("td:eq(1)").text();
                    var obj  = $(this);
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveServicePrice/" + id,
                        data: "price="+$(this).val(),
                        beforeSend: function(){
                            obj.attr("disabled", true);
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            obj.attr("disabled", false);
                            // Alert message
                            $("#dialog").html(result);
                            $("#dialog").dialog({
                                title: code+" - "+name,
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
                });
                <?php
                }
                ?>
                                
                // Check Print Barcode
                $(".btnCheckPrintBarcodeProduct").unbind('change').change(function(event){
                    event.preventDefault();
                    var id     = $(this).attr('rel');
                    var check  = $(this).is(":checked");
                    $.ajax({
                        type:   "POST",
                        url:    "<?php echo $this->base . "/products/printProductByCheck/"; ?>"+id+"/"+((check == true)?0:1),
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        }
                    });
                });
                
                // Print Item Barcode
                $(".btnPrintBarcodeProduct").unbind('click').click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printItemBarcodeTemplate" act="1"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewProduct"><?php echo TABLE_TEMPLATE; ?> 1</span></button><button type="submit" class="positive printItemBarcodeTemplate" act="2"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewService"><?php echo TABLE_TEMPLATE; ?> 2</span></button><button type="submit" class="positive printItemBarcodeTemplate" act="3"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewService"><?php echo TABLE_TEMPLATE; ?> 3</span></button></div>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_SELECT_TEMPLATE; ?>',
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
                    $(".printItemBarcodeTemplate").unbind('click').click(function(){
                         var template = $(this).attr("act");
                         printItemBarcode(template, id, '2');
                    });
                });
                return sPre;
            },
            "fnDrawCallback": function(oSettings, json) {
                $("#<?php echo $tblName; ?> .colspanParent").parent().attr("colspan", 11);
                $("#<?php echo $tblName; ?> .colspanParentHidden").parent().css("display", "none");
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1 ]
            }]
        });
        
        $(".btnAddProduct").unbind("click").click(function(event){
            event.preventDefault();
            $("#dialog").html('<div class="buttons"><button type="submit" class="positive addNewProduct" ><img src="<?php echo $this->webroot; ?>img/button/product.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewProduct"><?php echo MENU_PRODUCT_MANAGEMENT_ADD; ?></span></button><button type="submit" class="positive addNewService" ><img src="<?php echo $this->webroot; ?>img/button/service.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewService"><?php echo MENU_ADD_SERVICE; ?></span></button></div>');
            $(".addNewProduct").click(function(){
                $("#dialog").dialog("close");
                var leftPanel  = $("#dashboardProduct");
                var rightPanel = leftPanel.parent().find(".rightPanel");
                leftPanel.hide("slide", { direction: "left" }, 500, function() {
                    rightPanel.show();
                });
                rightPanel.html("<?php echo ACTION_LOADING; ?>");
                rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
            });
            $(".addNewService").click(function(){
                $("#dialog").dialog("close");
                var leftPanel  = $("#dashboardProduct");
                var rightPanel = leftPanel.parent().find(".rightPanel");
                leftPanel.hide("slide", { direction: "left" }, 500, function() {
                    rightPanel.show();
                });
                rightPanel.html("<?php echo ACTION_LOADING; ?>");
                rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addService/");
            });
            $("#dialog").dialog({
                title: '<?php echo DIALOG_SELECT_TYPE; ?>',
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
        });
        
        $(".btnRefreshProduct").unbind("click").click(function(){
            $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="11" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            var Tablesetting = oTableProductDashBoard.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#<?php echo $changeCategoryProductView; ?>").val()+"/"+$("#<?php echo $displayProduct; ?>").val()+"/"+$("#<?php echo $changeDepartmentProductView; ?>").val();
            oCache.iCacheLower = -1;
            oTableProductDashBoard.fnDraw(false);
        });
        
        $("#displayProduct, #<?php echo $changeCategoryProductView; ?>").unbind("change").change(function(){
            $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="11" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
            var Tablesetting = oTableProductDashBoard.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#<?php echo $changeCategoryProductView; ?>").val()+"/"+$("#<?php echo $displayProduct; ?>").val()+"/"+$("#<?php echo $changeDepartmentProductView; ?>").val();
            oCache.iCacheLower = -1;
            oTableProductDashBoard.fnDraw(false);
        });
        
        $("#<?php echo $changeDepartmentProductView; ?>").unbind("change").change(function(){
            $("#<?php echo $changeCategoryProductView; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            $("#<?php echo $ProductCategory; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            // $("#<?php echo $ProductSubCategory; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            var departmentId = $(this).val();
            $("#<?php echo $changeCategoryProductView; ?>").find("option[value='all']").attr("selected", true);
            if(departmentId != "all"){
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
                        $("#<?php echo $changeCategoryProductView; ?>").html(result);
                        $("#<?php echo $changeCategoryProductView; ?>").find("option[value='']").attr("value", "all");
                        $("#<?php echo $changeCategoryProductView; ?>").find("option[value='all']").text('<?php echo TABLE_ALL; ?>');
                    }
                });
            } 
            loadFilterAjax();
        });
        
        // Category
        $("#<?php echo $changeCategoryProductView; ?>").unbind("change").change(function(){
            $("#<?php echo $ProductCategory; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            // $("#<?php echo $ProductSubCategory; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            var categoryId = $(this).val();
            if(categoryId != "all"){
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
                        $("#<?php echo $ProductCategory; ?>").html(result);
                        $("#<?php echo $ProductCategory; ?>").find("option[value='']").attr("value", "all");
                        $("#<?php echo $ProductCategory; ?>").find("option[value='all']").text('<?php echo TABLE_ALL; ?>');
                    }
                });
            } 
            loadFilterAjax();
        });
        
        // Sub Category
        $("#<?php echo $ProductCategory; ?>").unbind("change").change(function(){
            // $("#<?php echo $ProductSubCategory; ?>").html('<option value="all"><?php echo TABLE_ALL; ?></option>');
            // var subCategoryId = $(this).val();
            // if(subCategoryId != "all"){
            //     $.ajax({
            //         type: "GET",
            //         url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getSubCategory/"+subCategoryId,
            //         beforeSend: function(){
            //             $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            //         },
            //         error: function (result) {
            //             $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
            //         },
            //         success: function(result){
            //             $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
            //             $("#<?php echo $ProductSubCategory; ?>").html(result);
            //             $("#<?php echo $ProductSubCategory; ?>").find("option[value='']").attr("value", "all");
            //             $("#<?php echo $ProductSubCategory; ?>").find("option[value='all']").text('<?php echo TABLE_ALL; ?>');
            //         }
            //     });
            // }
            loadFilterAjax();
        });
        
        $("#<?php echo $ProductSubCategory; ?>").unbind("change").change(function(){
            loadFilterAjax();
        });
        
        $(".btnExportProduct").unbind("click").click(function(){
            var typeShow = $("#<?php echo $displayProduct; ?>").val();
            var department = $("#<?php echo $changeDepartmentProductView; ?>").val();
            var category = $("#<?php echo $changeCategoryProductView; ?>").val();
            var subProduct = $("#<?php echo $ProductCategory; ?>").val();
            var subSubProduct = '';//$("#<?php echo $ProductSubCategory; ?>").val()
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/exportExcel/"+typeShow+"/"+department+"/"+category+"/"+subProduct+"/"+subSubProduct,
                data: "action=export",
                beforeSend: function(){
                    $(".btnExportProduct").attr('disabled','disabled');
                    $(".btnExportProduct").find('img').attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(){
                    $(".btnExportProduct").removeAttr('disabled');
                    $(".btnExportProduct").find('img').attr("src", "<?php echo $this->webroot; ?>img/button/csv.png");
                    window.open("<?php echo $this->webroot; ?>public/report/product_export.csv", "_blank");
                }
            });
        });
        
        $(".btnPrintBarcodeMultiProduct").unbind('click').click(function(){
            $("#dialog").html('<p><input type="radio" value="1" name="checkBarcodePrint" id="checkBarcodePrintSelected" checked="" /> <label for="checkBarcodePrintSelected">Product Selected</label><input type="radio" value="2" name="checkBarcodePrint" id="checkBarcodePrintAll" /> <label for="checkBarcodePrintAll">Product All</label></p><div class="buttons"><button type="submit" class="positive printBarcodeTemplate" act="1"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewProduct"><?php echo TABLE_TEMPLATE; ?> 1</span></button><button type="submit" class="positive printBarcodeTemplate" act="2"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewService"><?php echo TABLE_TEMPLATE; ?> 2</span></button><button type="submit" class="positive printBarcodeTemplate" act="3"><img src="<?php echo $this->webroot; ?>img/button/barcode.png" style="width: 20px; height: 20px;" /> <span class="txtaddNewService"><?php echo TABLE_TEMPLATE; ?> 3</span></button></div>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_SELECT_TEMPLATE; ?>',
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
            $(".printBarcodeTemplate").unbind('click').click(function(){
                var checkFilter = $("input[name='checkBarcodePrint']:checked").val();
                var template = $(this).attr("act");
                printItemBarcode(template, 'all', checkFilter);
            });
        });
        
        $(".btnPrintAllBarcodeCheckProduct").unbind("click").click(function(){
            $("#dialog").html('Do you want to reset all product selected?');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                position:'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_YES; ?>': function() {
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/printProductByCheck/clearData",
                            data: "action=export",
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(){
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                oCache.iCacheLower = -1;
                                oTableProductDashBoard.fnDraw(false);
                            }
                        });
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    });
    
    function changeBackgroupProduct(){
        $("#<?php echo $tblName; ?> tbody tr").each(function(){
                $(this).removeAttr('style');
        });
    }
    
    function setIdActionProduct(){
        var action = new Array('btnViewProductView', 'btnEditProductView', 'btnDeleteProductView', 'setProductPrice', 'viewInventoryProduct');
        $.each( action, function( key, value ) {
            $("#<?php echo $tblName; ?> tr:eq(0)").find('.'+value).attr('id', value);
        });
    }
    
    function printItemBarcode(template, item, filter){
        var category = $("#<?php echo $changeCategoryProductView; ?>").val();
        $.ajax({
            type: "GET",
            url:  "<?php echo $this->base . "/products/printBarcodeTmp"; ?>"+template+"/"+item+"/"+filter+"/"+category,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(printLayout){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                w = window.open();
                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                w.document.write(printLayout);
                w.document.close();
            }
        });
    }
    
    function loadFilterAjax(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="11" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
        var Tablesetting = oTableProductDashBoard.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#<?php echo $changeCategoryProductView; ?>").val()+"/"+$("#<?php echo $displayProduct; ?>").val()+"/"+$("#<?php echo $changeDepartmentProductView; ?>").val()+"/"+$("#<?php echo $ProductCategory; ?>").val();
        oCache.iCacheLower = -1;
        oTableProductDashBoard.fnDraw(false);
    }
</script>
<div class="leftPanel" id="dashboardProduct">
    <div style="padding-top: 3px; padding-bottom: 3px; padding-left: 5px; padding-right: 5px; border: 1px dashed #bbbbbb; margin-bottom: 5px;" id="divHeader">
        <?php 
            if($allowAdd){
        ?>
        <div class="buttons">
            <a href="" class="positive btnAddProduct">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_PRODUCT_SERVICE_ADD; ?>
            </a>
        </div>
        <?php 
            } 
            if($allowExport){ 
        ?>
        <div class="buttons">
            <button type="button" class="positive btnExportProduct">
                <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
                <?php echo ACTION_EXPORT_TO_EXCEL; ?>
            </button>
        </div>
        <?php } ?>
        <div class="buttons">
            <button type="button" class="positive btnPrintBarcodeMultiProduct">
                <img src="<?php echo $this->webroot; ?>img/button/print-barcode.png" alt=""/>
                <?php echo ACTION_PRINT_MULTI_BARCODE; ?>
            </button>
        </div>
        <div class="buttons">
            <button type="button" class="positive btnPrintAllBarcodeCheckProduct">
                <?php echo ACTION_RESET_ALL; ?>
            </button>
        </div>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshProduct">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="clear: both;"></div>
        <div style="padding-top: 5px;">
            <label for="<?php echo $displayProduct; ?>"><?php echo TABLE_SHOW; ?> </label>: 
            <select id="<?php echo $displayProduct; ?>" style="width: 100px; height: 30px;">
                <option value="all" selected="selected"><?php echo TABLE_ALL; ?></option>
                <option value="1"><?php echo TABLE_PRODUCT; ?></option>
                <option value="2"><?php echo TABLE_SERVICE; ?></option>
            </select>	
            &nbsp;&nbsp;&nbsp;
            <label for="<?php echo $changeDepartmentProductView; ?>"><?php echo MENU_DEPARTMENT; ?></label>:
            <select id="<?php echo $changeDepartmentProductView; ?>" style="width: 200px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $queryDep = mysql_query("SELECT * FROM `departments` WHERE is_active = 1 ORDER BY name");
                while($dataDep = mysql_fetch_array($queryDep)){
                ?>
                <option value="<?php echo $dataDep['id']; ?>"><?php echo $dataDep['name']; ?></option>
                <?php
                }
                ?>
            </select>
            &nbsp;&nbsp;&nbsp;
            <label for="<?php echo $changeCategoryProductView; ?>"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?></label>:
            <select id="<?php echo $changeCategoryProductView; ?>" style="width: 200px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
            </select>
            &nbsp;&nbsp;&nbsp;
            <label for="<?php echo $ProductCategory; ?>">Sub Product</label>:
            <select name="" id="<?php echo $ProductCategory; ?>" style="width: 200px; height: 30px;" class="validate[required]">
                <option value="all"><?php echo TABLE_ALL; ?></option>
            </select>
            <!-- &nbsp;&nbsp;&nbsp;
            <label for="<?php echo $ProductSubCategory; ?>">Sub-Sub Product</label>:
            <select name="" id="<?php echo $ProductSubCategory; ?>" style="width: 200px; height: 30px;" class="validate[required]">
                <option value="all"><?php echo TABLE_ALL; ?></option>
            </select> -->
        </div>
    </div>
    <div id="dynamic" style="height: 100%">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" style="width: 100%;">
            <thead>
                <tr>
                    <th class="first" style="width: 4%;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 7%;"><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?></th>
                    <th>Sub Category</th>
                    <!-- <th>Sub-Sub Category</th> -->
                    <th style="width: 45%;"><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_BARCODE; ?></th>
                    <th style="width: 10%;"><?php echo TABLE_UOM; ?></th>
                    <!--<th><?php echo TABLE_QTY; ?></th>-->
                    <?php
                    if($allowViewCost){
                    ?>
                    <th><?php echo TABLE_UNIT_COST; ?></th>
                    <?php
                    }
                    ?>
                    <th style="width: 20%;"><?php echo TABLE_PRICE; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="10" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
</div>
<div class="rightPanel"></div>
