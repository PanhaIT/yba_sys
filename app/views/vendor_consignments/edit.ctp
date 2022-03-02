<?php 
include("includes/function.php");
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
$sqlSettingUomDeatil  = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail  = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    var indexRowVendorConsignment = 0;
    var cloneRowVendorConsignment =  $("#detailVendorConsignment");
    var VendorConsignmentTimeCode   = 1;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#VendorConsignmentBranchId").filterOptions('com', '<?php echo $this->data['VendorConsignment']['company_id']; ?>', '<?php echo $this->data['VendorConsignment']['branch_id']; ?>');
        $("#VendorConsignmentBranchId").change();
        $("#VendorConsignmentLocationGroupId").chosen();
        // Remove Clone Row List
        $("#detailVendorConsignment").remove();
        
        var waitForFinalEventVendorConsignment = (function () {
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
        if(tabVendorConsignmentReg != tabVendorConsignmentId){
            $("a[href='"+tabVendorConsignmentId+"']").click(function(){
                if($("#bodyListVendorConsignment").html() != '' && $("#bodyListVendorConsignment").html() != null){
                    waitForFinalEventVendorConsignment(function(){
                        refreshScreenVendorConsignment();
                        resizeFormTitleVendorConsignment();
                        resizeFornScrollVendorConsignment();  
                    }, 500, "Finish");
                }
            });
            tabVendorConsignmentReg = tabVendorConsignmentId;
        }

        waitForFinalEventVendorConsignment(function(){
              refreshScreenVendorConsignment();
              resizeFormTitleVendorConsignment();
              resizeFornScrollVendorConsignment();  
            }, 500, "Finish");
            
        $(window).resize(function(){
            if(tabVendorConsignmentReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventVendorConsignment(function(){
                    refreshScreenVendorConsignment();
                    resizeFormTitleVendorConsignment();
                    resizeFornScrollVendorConsignment();  
                  }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderVendorConsignment").click(function(){
            var VendorConsignmentCompanyId       = $("#VendorConsignmentCompanyId").val();
            var VendorConsignmentBranchId        = $("#VendorConsignmentBranchId").val();
            var VendorConsignmentLocationGroupId = $("#VendorConsignmentLocationGroupId").val();
            var VendorConsignmentLocationId      = $("#VendorConsignmentLocationId").val();
            var VendorConsignmentDate            = $("#VendorConsignmentDate").val();
            var VendorConsignmentVendorId        = $("#VendorConsignmentVendorId").val();
            
            if(VendorConsignmentCompanyId == "" || VendorConsignmentBranchId == "" || VendorConsignmentLocationGroupId == "" || VendorConsignmentLocationId == "" || VendorConsignmentDate == "" || VendorConsignmentVendorId == ""){
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
                    $("#VendorConsignmentTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#VendorConsignmentTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabVendorConsignmentReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventVendorConsignment(function(){
                        resizeFornScrollVendorConsignment();
                    }, 500, "Finish");
                }
            }
        });
        
        // Form Validate
        $("#VendorConsignmentEditForm").validationEngine('detach');
        $("#VendorConsignmentEditForm").validationEngine('attach');
        
        $(".btnSaveVendorConsignment").click(function(){
            if(checkBfSaveVendorConsignment() == true){
                return true;
            }else{
                return false;
            }
        });
        
        $("#VendorConsignmentEditForm").ajaxForm({
            dataType: 'json',
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVendorConsignment").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $("#VendorConsignmentDate, .date_expired").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float, .qty").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#VendorConsignmentTotalAmount").val($("#VendorConsignmentTotalAmount").val().replace(/,/g,""));
            },
            error: function (result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                createSysAct('Vendor Consignment', 'Edit', 2, result.responseText);
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
                        backVendorConsignment();
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
                backVendorConsignment();
                if(result.code == "1"){
                    codeDialogVendorConsignment();
                }else if(result.code == "2"){
                    errorSaveVendorConsignment();
                }else{
                    createSysAct('Vendor Consignment', 'Edit', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceVendorConsignment" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_VENDOR_CONSIGNMENT; ?></span></button></div>');
                    $(".printInvoiceVendorConsignment").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.vendor_consignment_id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printInvoiceVendorConsignmentResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printInvoiceVendorConsignmentResult);
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
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
        // Check Location Group
        checkLocationByGroupVendorConsignment('<?php echo $this->data['VendorConsignment']['location_id']; ?>');
        $("#VendorConsignmentLocationGroupId").change(function(){
            checkLocationByGroupVendorConsignment('');
        });
        
        // Action Vendor
        $("#searchVendorVendorConsignment").click(function(){
            if(checkOrderDateVendorConsignment() == true && $("#VendorConsignmentCompanyId").val() != ''){
                searchVendorVendorConsignment();
            }
        });
        
        $("#deleteVendorVendorConsignment").click(function(){
            deleteVendorVendorConsignment();
        });
        
        $("#VendorConsignmentVendorName").focus(function(){
            checkOrderDateVendorConsignment();
        });
        
        $("#VendorConsignmentVendorName").keypress(function(e){
            if((e.which && e.which != 13) || e.keyCode != 13){
                $("#VendorConsignmentVendorId").val("");
            }
        });
        
        $("#VendorConsignmentVendorName").autocomplete("<?php echo $this->base . "/vendor_consignments/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyVendorConsignment(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyVendorConsignment(value.split(".*")[4])){
                    return value.split(".*")[2] + " - " + value.split(".*")[1];
                }
            }
        }).result(function(event, value){
            // Set Vendor
            $("#VendorConsignmentVendorId").val(value.toString().split(".*")[0]);
            $("#VendorConsignmentVendorName").val(value.toString().split(".*")[1]);
            $("#VendorConsignmentVendorName").attr('readonly', true);
            $("#searchVendorVendorConsignment").hide();
            $("#deleteVendorVendorConsignment").show();
        });
        // End Action Vendor
        
        // Action Scan/Search Product
        $("#VendorConsignmentSearchSKU").autocomplete("<?php echo $this->base . "/vendor_consignments/searchProduct/"; ?>", {
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
            $(".productVendorConsignment").val(code);
            if(VendorConsignmentTimeCode == 1){
                VendorConsignmentTimeCode = 2;
                serachProCodeVendorConsignment(code, '#VendorConsignmentSearchSKU', 1, 1, '');
            }
        });
        
        $("#VendorConsignmentSearchProduct").click(function(){
            searchProductVendorConsignment();
        });
        
        $("#VendorConsignmentSearchPUC").focus(function(){
            $(".btnSaveVendorConsignment").attr('disabled','disabled');
        });
        
        $("#VendorConsignmentSearchPUC").blur(function(){
            $(".btnSaveVendorConsignment").removeAttr('disabled');
        });
        
        $("#VendorConsignmentSearchPUC").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(VendorConsignmentTimeCode == 1){
                    VendorConsignmentTimeCode = 2;
                    serachProCodeVendorConsignment($(this).val(), '#VendorConsignmentSearchPUC', 2, 1, '');
                }
                return false;
            }
        });

        $("#VendorConsignmentSearchSKU").keypress(function(e){
            var code =null;
            var obj = $(this);
            code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13){
                if($("#VendorConsignmentCompanyId").val() ==""){
                    $(this).val('');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_COMPANY_FIRST; ?></p>');
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
                    if($(this).val() != ""){
                        if(VendorConsignmentTimeCode == 1){
                            VendorConsignmentTimeCode = 2;
                            serachProCodeVendorConsignment($(this).val(), '#VendorConsignmentSearchSKU', 1, 1, '');
                        }
                    }
                }
                return false;
            }
        });
        // End Action Scan/Search Product
        // Action Back
        $(".btnBackVendorConsignment").click(function(event){
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
                        backVendorConsignment();
                    }
                }
            });
        });

        $('#VendorConsignmentDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        $("#VendorConsignmentDate").datepicker("option", "maxDate", 0);
        // Action Company
        $.cookie('companyIdVendorConsignment', $("#VendorConsignmentCompanyId").val(), { expires: 7, path: "/" });
        $("#VendorConsignmentCompanyId").change(function(){
            var obj    = $(this);
            if($(".listBodyVendorConsignment").find(".product_id").val() == undefined){
                $.cookie('companyIdVendorConsignment', obj.val(), { expires: 7, path: "/" });
                $("#VendorConsignmentBranchId").filterOptions('com', obj.val(), '');
                $("#VendorConsignmentBranchId").change();
                resetFormVendorConsignment();
                changeInputCSSVendorConsignment();
            }else{
                var question = "<?php echo SALES_ORDER_CONFIRM_CHANGE_COMPANY; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
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
                        '<?php echo ACTION_OK; ?>': function() {
                            $.cookie('companyIdVendorConsignment', obj.val(), { expires: 7, path: "/" });
                            $("#VendorConsignmentBranchId").filterOptions('com', obj.val(), '');
                            $("#VendorConsignmentBranchId").change();
                            $("#tblVendorConsignment").html("");
                            resetFormVendorConsignment();
                            changeInputCSSVendorConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentCompanyId").val($.cookie("companyIdVendorConsignment"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Action Branch
        $.cookie('branchIdVendorConsignment', $("#VendorConsignmentBranchId").val(), { expires: 7, path: "/" });
        $("#VendorConsignmentBranchId").change(function(){
            var obj = $(this);
            if($(".listBodyVendorConsignment").find(".product_id").val() == undefined){
                $.cookie('branchIdVendorConsignment', obj.val(), { expires: 7, path: "/" });
                branchChangeVendorConsignment(obj);
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
                            $.cookie('branchIdVendorConsignment', obj.val(), { expires: 7, path: "/" });
                            branchChangeVendorConsignment(obj);
                            $("#tblVendorConsignment").html('');
                            calcTotalVendorConsignment();
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $("#VendorConsignmentBranchId").val($.cookie("branchIdVendorConsignment"));
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Event Key Row List
        checkEventVendorConsignment();
    });
    
    function resizeFormTitleVendorConsignment(){
        var screen = 16;
        var widthList = $("#bodyListVendorConsignment").width();
        var widthTitle = widthList - screen;
        $("#tblHeaderVendorConsignment").css('padding', '0px');
        $("#tblHeaderVendorConsignment").css('margin-top', '5px');
        $("#tblHeaderVendorConsignment").css('width', widthTitle);
    }
    
    function resizeFornScrollVendorConsignment(){
        var tabHeight = $(tabVendorConsignmentId).height();
        var formHeader = 0;
        if ($('#VendorConsignmentTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#VendorConsignmentTop").height();
        }
        var btnHeader   = $("#btnHideShowHeaderVendorConsignment").height();
        var formFooter  = $("#VendorConsignmentFooter").height();
        var formSearch  = $("#searchFormVendorConsignment").height();
        var tableHeader = $("#tblHeaderVendorConsignment").height();
        var widthList   = $("#bodyListVendorConsignment").width();
        var getHeight   = tabHeight - (formHeader + btnHeader + tableHeader + formSearch + formFooter);
        $("#bodyListVendorConsignment").css('height', getHeight);
        $("#bodyListVendorConsignment").css('padding', '0px');
        $("#bodyListVendorConsignment").css('width', widthList);
        $("#bodyListVendorConsignment").css('overflow-x', 'hidden');
        $("#bodyListVendorConsignment").css('overflow-y', 'scroll');
    }
    
    function refreshScreenVendorConsignment(){
        $("#tblHeaderVendorConsignment").removeAttr('style');
        var windowWidth  = $(window).width();
        if(windowWidth <= '1024'){
            $(".productVendorConsignmentCode").css('width','40%');
        }else{
            $(".productVendorConsignmentCode").css('width','50%');
        }
    }
    
    function calcTotalVendorConsignment(){
        var totalAmount    = 0;
        $(".listBodyVendorConsignment").find(".total_cost").each(function(){
            totalAmount += replaceNum($(this).val());
        });
        $("#VendorConsignmentTotalAmount").val((parseFloat(totalAmount)).toFixed(2));
    }
    
    function addProductVendorConsignment(productId, sku, puc, name, isExpired, uomList, unitCost, smallUomVal, defaultCost, qtyOrder, uomSelected){        
        // Get Index Row
        indexRowVendorConsignment = Math.floor((Math.random() * 100000) + 1);
        defaultCost = defaultCost>0?defaultCost:unitCost;
        var tr = cloneRowVendorConsignment.clone(true);        
        tr.removeAttr("style").removeAttr("id");          
        tr.find("td:eq(0)").html(indexRowVendorConsignment);
        tr.find("td .VendorConsignmentSKU").val(sku);
        tr.find("td .VendorConsignmentPUC").val(puc);
        tr.find("td .product_id").attr("id", "product_id"+indexRowVendorConsignment).val(productId);
        tr.find("td .product_name").attr("id", "product_name"+indexRowVendorConsignment).val(name);                   
        tr.find("td .small_uom_val").attr("id", "small_uom_val").val(smallUomVal);
        tr.find("td .qty_uom_id").attr("id", "qty_uom_id"+indexRowVendorConsignment).html(uomList);
        tr.find("td .qty").attr("id", "qty_"+indexRowVendorConsignment).val(qtyOrder);
        tr.find("td .is_expired").val(isExpired);
        tr.find("td .conversion").val(smallUomVal);
        tr.find("td .lots_number").attr("id", "lots_number"+indexRowVendorConsignment);
        tr.find("td .date_expired").attr("id", "date_expired"+indexRowVendorConsignment);
        tr.find("td .unit_cost").attr("id", "unit_cost"+indexRowVendorConsignment).val(Number(unitCost).toFixed(2)); 
        tr.find("td .total_cost").attr("id", "total_cost"+indexRowVendorConsignment).val(Number(unitCost).toFixed(2));
        if(isExpired == 1){
            tr.find("td input[name='date_expired[]']").removeAttr('class');
            tr.find("td input[name='date_expired[]']").attr('class', 'date_expired validate[required]');
        }else{
            tr.find("td input[name='date_expired[]']").removeAttr('class').css('visibility', 'hidden').val('0000-00-00');;
        }
        // Get Uom Selected
        if(uomSelected != ''){
            tr.find("td .qty_uom_id").find("option[value='"+uomSelected+"']").attr('selected', 'selected');
        }
        $("#tblVendorConsignment").append(tr);
        tr.find("td .qty").select().focus();
        
        VendorConsignmentTimeCode = 1;
        setIndexRowVendorConsignment();
        checkEventVendorConsignment();
        calcTotalVendorConsignment();
    }
    
    function setIndexRowVendorConsignment(){
        var sort = 1;
        $(".listBodyVendorConsignment").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function checkVendorVendorConsignment(field, rules, i, options){
        if($("#VendorConsignmentVendorId").val() == "" || $("#VendorConsignmentVendorName").val() == ""){
            return "* Invalid Vendor";
        }
    }
    
    function serachProCodeVendorConsignment(code, field, search, qtyOrder, uomSelected){
        if($("#VendorConsignmentCompanyId").val() == "" || $("#VendorConsignmentBranchId").val() == "" || $("#VendorConsignmentLocationId").val() == ""){
            $(field).val('');
            VendorConsignmentTimeCode = 1;
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
        }else {
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/vendor_consignments/searchProductCode/"; ?>"+ $("#VendorConsignmentCompanyId").val()+"/"+$("#VendorConsignmentBranchId").val()+"/"+code+"/"+search,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".btnSaveVendorConsignment").removeAttr('disabled');
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $(field).val('');
                    VendorConsignmentTimeCode = 1;
                    if(msg == '<?php echo TABLE_NO_PRODUCT; ?>'){
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
                        var data = msg;
                        var skuUomId = "all";                              
                        if(data){
                            $.ajax({
                                type: "GET",
                                url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+data.toString().split('--')[7]+"/"+skuUomId,
                                data: "",
                                beforeSend: function(){
                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                },                                                               
                                success: function(msg){
                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                    var product   = data.toString().split("--");
                                    var productId = product[0];
                                    var sku  = product[1];
                                    var puc  = product[2];
                                    var name = product[3];
                                    var isExpired   = product[4];
                                    var unitCost    = product[5];
                                    var smallUomVal = product[6];
                                    var defaultCost = 0;
                                    var packetList  = product[8];
                                    if(packetList != ''){
                                        var packet = packetList.toString().split("**");
                                        var loop = 1;
                                        var time = 0;
                                        $.each(packet,function(key, item){
                                            var items = item.toString().split("||");
                                            var productCode = items[0];
                                            var uomSelected = items[1];
                                            var qtyOrder    = items[2];
                                            if(loop > 1){
                                                time += 3500;
                                            }
                                            setTimeout(function () {
                                                serachProCodeVendorConsignment(productCode, field, search, qtyOrder, uomSelected);
                                            }, time);
                                            loop++;
                                        });
                                    }else{
                                        addProductVendorConsignment(productId, sku, puc, name, isExpired, msg, unitCost, smallUomVal, defaultCost, qtyOrder, uomSelected);
                                    }
                                }
                            });
                        }
                    }
                }
            });
        }
    }
    
    function deleteVendorVendorConsignment(){
        $("#VendorConsignmentVendorId").val("");
        $("#VendorConsignmentVendorName").val("");
        $("#VendorConsignmentVendorName").removeAttr("readonly");
        $("#deleteVendorVendorConsignment").hide();
        $("#searchVendorVendorConsignment").show();
    }
    
    function checkBfSaveVendorConsignment(){
        var formName     = "#VendorConsignmentEditForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if(($("#VendorConsignmentTotalAmount").val() == undefined && $("#VendorConsignmentTotalAmount").val() == "") || $(".listBodyVendorConsignment").find(".product_id").val() == undefined){
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
    
    function searchProductVendorConsignment(){
        if($("#VendorConsignmentCompanyId").val() == "" || $("#VendorConsignmentBranchId").val() == "" || $("#VendorConsignmentLocationId").val() == ""){
            VendorConsignmentTimeCode = 1;
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
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/vendor_consignments/product/"; ?>" + $("#VendorConsignmentCompanyId").val()+"/"+$("#VendorConsignmentBranchId").val()+"/"+$("#VendorConsignmentLocationId").val(),
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
                                    $.ajax({
                                        type: "GET",
                                        url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+data.attr('data').split('|-|')[6],
                                        success: function(msg){
                                            var record    = data.attr('data').split("|-|");
                                            var productId = data.val();
                                            var sku  = record[0];
                                            var puc  = record[1];
                                            var name = record[2];
                                            var isExpired   = record[3];
                                            var unitCost    = record[4];
                                            var smallUomVal = record[5];
                                            var defaultCost = 0;
                                            var packetList  = data.attr('packet');
                                            if(packetList != ''){
                                                var packet = packetList.toString().split("**");
                                                var loop = 1;
                                                var time = 0;
                                                $.each(packet,function(key, item){
                                                    var items = item.toString().split("||");
                                                    var productCode = items[0];
                                                    var uomSelected = items[1];
                                                    var qtyOrder    = items[2];
                                                    if(loop > 1){
                                                        time += 3500;
                                                    }
                                                    setTimeout(function () {
                                                        serachProCodeVendorConsignment(productCode, '#VendorConsignmentSearchSKU', 1, qtyOrder, uomSelected);
                                                    }, time);
                                                    loop++;
                                                });
                                            }else{
                                                addProductVendorConsignment(productId, sku, puc, name, isExpired, msg, unitCost, smallUomVal, defaultCost, 1, '');
                                            }
                                        }
                                    });
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    }
    
    function eventKeyRowVendorConsignment(){
        $(".qty, .unit_cost, .qty_uom_id, .total_cost, .btnRemoveVendorConsignment, .noteAddVendorConsignment").unbind('keypress').unbind('keyup').unbind('change').unbind('click');
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $(".qty").autoNumeric({mDec: 0, aSep: ','});
        
        $(".qty, .unit_cost").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $(".qty, .unit_cost").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
        
        $(".total_cost").keyup(function(){
            var value = replaceNum($(this).val());
            var qty   = replaceNum($(this).closest("tr").find(".qty").val());
            var unitPrice = parseFloat(converDicemalJS(value / qty));
            $(this).closest("tr").find(".unit_cost").val(unitPrice.toFixed(2));
            calcTotalVendorConsignment();
            
        });
        
        $(".qty, .unit_cost").keyup(function(){
            var qty         = replaceNum($(this).closest("tr").find("td .qty").val());
            var unitCost    = replaceNum($(this).closest("tr").find("td .unit_cost").val());
            var totalAmount = converDicemalJS(parseFloat(replaceNum(qty)) * unitCost);
            $(this).closest("tr").find("td .total_cost").val(totalAmount.toFixed(2));
            calcTotalVendorConsignment();
        });
        
        $(".qty").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $(this).closest("tr").find(".unit_cost").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                if(replaceNum($(this).val()) != "" && replaceNum($(this).val()) > 0){
                    $(".productVendorConsignment").select().focus();
                }
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){                                                
            var value         = replaceNum($(this).val());
            var smallUomVal   = parseFloat($(this).closest("tr").find(".small_uom_val").val());
            var uomConversion = converDicemalJS(smallUomVal / parseFloat(replaceNum($(this).find("option[value='"+value+"']").attr('conversion'))));
            var unitCost      = (parseFloat(converDicemalJS(replaceNum($(this).closest("tr").find(".defaltCost").val()) / parseFloat(replaceNum($(this).find("option[value='"+value+"']").attr('conversion')))))).toFixed(2);
            if($(this).closest("tr").find(".product_id").val() != ""){
                var totalAmount = parseFloat( converDicemalJS(unitCost * replaceNum($(this).closest("tr").find(".qty").val())));
                $(this).closest("tr").find(".conversion").val(uomConversion);
                $(this).closest("tr").find(".total_cost").val( (converDicemalJS(totalAmount)).toFixed(2) );                               
                calcTotalVendorConsignment();                
            }
        });
        
        $(".btnRemoveVendorConsignment").click(function(){
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
                        calcTotalVendorConsignment();
                        setIndexRowVendorConsignment();
                        $(this).dialog("close");
                    }
                }
            });
        });
        
        $(".noteAddVendorConsignment").click(function(){
            addNoteVendorConsignment($(this));
        });
        
        $('.date_expired').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
    }
    
    function checkEventVendorConsignment(){
        eventKeyRowVendorConsignment();
        $(".listBodyVendorConsignment").unbind("click");
        $(".listBodyVendorConsignment").click(function(){
            eventKeyRowVendorConsignment();
        });
    }
    
    function searchVendorVendorConsignment(){
        var companyId = $("#VendorConsignmentCompanyId").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . "/vendor_consignments/vendor/"; ?>"+companyId,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo MENU_VENDOR; ?>',
                    resizable: false,
                    modal: true,
                    width: 850,
                    height: 500,
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    close: function(){
                        VendorConsignmentTimeCode = 1;
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            
                             // calculate due_date
                                var data = $("input[name='chkVendor']:checked").val();
                                if(data){
                                    // Set Vendor
                                    $("#VendorConsignmentVendorId").val(data.split('-')[0]);
                                    $("#VendorConsignmentVendorName").val(data.split('-')[2]);
                                    $("#VendorConsignmentVendorName").attr('readonly', true);
                                    $("#searchVendorVendorConsignment").hide();
                                    $("#deleteVendorVendorConsignment").show();
                                }
                                VendorConsignmentTimeCode = 1;
                                $(this).dialog("close");
                        },
                        '<?php echo ACTION_CANCEL; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
    
    function branchChangeVendorConsignment(obj){
        var mCode  = obj.find("option:selected").attr("mcode");
        var currency = obj.find("option:selected").attr("currency");
        var currencySymbol = obj.find("option:selected").attr("symbol");
        $("#VendorConsignmentCode").val("<?php echo date("y"); ?>"+mCode);
        $("#VendorConsignmentCurrencyCenterId").val(currency);
        $(".lblSymbolVendorConsignment").html(currencySymbol);
    }
    
    function checkCompanyVendorConsignment(companyId){
        var companyReturn = false;
        var companyPut    = companyId.split(",");
        var companySelect = $("#VendorConsignmentCompanyId").val();
        if(companyPut.indexOf(companySelect) != -1){
            companyReturn = true;
        }
        return companyReturn;
    }
    
    function resetFormVendorConsignment(){
        // Vendor
        $("#deleteVendorVendorConsignment").click();
    }
    
    function reloadPageVendorConsignment(){
        var rightPanel = $(".btnBackVendorConsignment").parent().parent().parent().parent().parent();
        rightPanel.html("<?php echo ACTION_LOADING; ?>");
        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
    }
    
    function checkOrderDateVendorConsignment(){
        if($("#VendorConsignmentDate").val() == ""){
            $("#VendorConsignmentDate").focus();
            return false;
        }else{
            return true;
        }
    }
    
    function checkLocationByGroupVendorConsignment(selected){
        var locationGroup = $("#VendorConsignmentLocationGroupId").val();
        $("#VendorConsignmentLocationId").filterOptions('location-group', locationGroup, selected);
    }
    
    function codeDialogVendorConsignment(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
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
                $(".btnSaveVendorConsignment").removeAttr("disabled");
                $(".txtSaveVendorConsignment").html("<?php echo ACTION_SAVE; ?>");
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".btnSaveVendorConsignment").removeAttr("disabled");
                    $(".txtSaveVendorConsignment").html("<?php echo ACTION_SAVE; ?>");
                }
            }
        });
    }
    
    function errorSaveVendorConsignment(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
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
                backVendorConsignment();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function addNoteVendorConsignment(currentTr){
        var note = currentTr.closest("tr").find(".note");
        $("#dialog").html("<textarea style='width:350px; height: 200px;' id='noteCommentVendorConsignment'>"+note.val()+"</textarea>").dialog({
            title: '<?php echo TABLE_NOTE; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    note.val($("#noteCommentVendorConsignment").val());
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function backVendorConsignment(){
        oCache.iCacheLower = -1;
        oVendorConsignment.fnDraw(false);
        $("#VendorConsignmentEditForm").validationEngine("hideAll");
        var rightPanel = $("#VendorConsignmentEditForm").parent();
        var leftPanel  = rightPanel.parent().find(".leftPanel");
        $("#"+PbTableName).find("tbody").html('<tr><td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
        rightPanel.hide("slide", { direction: "right" }, 500, function(){
            leftPanel.show();
            rightPanel.html("");
        });
    }
    
    function changeInputCSSVendorConsignment(){
        var cssStyle  = 'inputDisable';
        var cssRemove = 'inputEnable';
        var readonly  = true;
        var disabled  = true;
        $(".searchVendor").hide();
        $("#divSearchVendorConsignment").css("visibility", "hidden");
        if($("#VendorConsignmentCompanyId").val() != ''){
            var currencySymbol = $("#VendorConsignmentCompanyId").find("option:selected").attr("symbol");
            cssStyle  = 'inputEnable';
            cssRemove = 'inputDisable';
            readonly  = false;
            disabled  = false;
            if($("#VendorConsignmentVendorName").val() == ''){
                $(".searchVendor").show();
            }
            $("#divSearchVendorConsignment").css("visibility", "visible");
            $(".lblSymbolVendorConsignment").html(currencySymbol);
            $("#companySymbolVendorConsignment").html(currencySymbol);
        } else {
            $(".lblSymbolVendorConsignment").html('');
            $("#companySymbolVendorConsignment").html('');
        } 
        // Label
        $("#VendorConsignmentEditForm").find("label").removeAttr("class");
        $("#VendorConsignmentEditForm").find("label").each(function(){
            var label = $(this).attr("for");
            if(label != 'VendorConsignmentCompanyId'){
                $(this).addClass(cssStyle);
            }
        });
        // Input & Select
        $("#VendorConsignmentEditForm").find("input").each(function(){
            $(this).removeClass(cssRemove);
            $(this).addClass(cssStyle);
        });
        $("#VendorConsignmentEditForm").find("select").each(function(){
            var selectId = $(this).attr("id");
            if(selectId != 'VendorConsignmentCompanyId'){
                $(this).removeClass(cssRemove);
                $(this).addClass(cssStyle);
                $(this).attr("disabled", disabled);
            }
        });
        $(".lblSymbolVendorConsignment").removeClass(cssRemove);
        $(".lblSymbolVendorConsignment").addClass(cssStyle);
        // Input Readonly
        $("#VendorConsignmentVendorName").attr("readonly", readonly);
        $("#VendorConsignmentNote").attr("readonly", readonly);
        $("#VendorConsignmentSearchPUC").attr("readonly", readonly);
        $("#VendorConsignmentSearchSKU").attr("readonly", readonly);
    }
</script>
<?php echo $this->Form->create('VendorConsignment'); ?>
<input type="hidden" value="<?php echo $this->data['VendorConsignment']['currency_center_id']; ?>" name="data[VendorConsignment][currency_center_id]" id="VendorConsignmentCurrencyCenterId" />
<div style="float: right; width: 165px; text-align: right; cursor: pointer;" id="btnHideShowHeaderVendorConsignment">
    [<span>Hide</span> Header Information <img alt="" align="absmiddle" style="width: 16px; height: 16px;" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />]
</div>
<div style="clear: both;"></div>
<div id="VendorConsignmentTop">
    <fieldset>
        <legend><?php __(MENU_VENDOR_CONSIGNMENT_INFO); ?></legend>
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 50%">
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%"><label for="VendorConsignmentCompanyId"><?php echo TABLE_COMPANY; ?></label> <span class="red">*</span></td>
                            <td style="width: 33%"><label for="VendorConsignmentBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span></label></td>
                            <td style="width: 33%"><label for="VendorConsignmentLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?></label> <span class="red">*</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <select name="data[VendorConsignment][company_id]" id="VendorConsignmentCompanyId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($companies) != 1){
                                        ?>
                                        <option vat-d="" value="" vat-opt=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($companies AS $company){
                                            $sqlVATDefault = mysql_query("SELECT vat_modules.vat_setting_id FROM vat_modules INNER JOIN vat_settings ON vat_settings.company_id = ".$company['Company']['id']." AND vat_settings.is_active = 1 AND vat_settings.id = vat_modules.vat_setting_id WHERE vat_modules.is_active = 1 AND vat_modules.apply_to = 21 GROUP BY vat_modules.vat_setting_id LIMIT 1");
                                            $rowVATDefault = mysql_fetch_array($sqlVATDefault);
                                        ?>
                                        <option vat-d="<?php echo $rowVATDefault[0]; ?>" <?php if($company['Company']['id'] == $this->data['VendorConsignment']['company_id']){ ?>selected="selected"<?php } ?> value="<?php echo $company['Company']['id']; ?>" vat-opt="<?php echo $company['Company']['vat_calculate']; ?>"><?php echo $company['Company']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <select name="data[VendorConsignment][branch_id]" id="VendorConsignmentBranchId" class="validate[required]" style="width: 75%;">
                                        <?php
                                        if(count($branches) != 1){
                                        ?>
                                        <option value="" com="" mcode="" currency="" symbol=""><?php echo INPUT_SELECT; ?></option>
                                        <?php
                                        }
                                        foreach($branches AS $branch){
                                        ?>
                                        <option <?php if($branch['Branch']['id'] == $this->data['VendorConsignment']['branch_id']){ ?>selected="selected"<?php } ?> value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['ven_consign_code']; ?>" currency="<?php echo $branch['Branch']['currency_center_id']; ?>" symbol="<?php echo $branch['CurrencyCenter']['symbol']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'style' => 'width:200px', 'label' => false)); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2">
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%"><label for="VendorConsignmentLocationId"><?php echo TABLE_LOCATION; ?></label> <span class="red">*</span></td>
                            <td><label for="VendorConsignmentNote"><?php echo TABLE_NOTE; ?></label></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">
                                <div class="inputContainer" style="width:100%">
                                    <select name="data[VendorConsignment][location_id]" id="VendorConsignmentLocationId" class="validate[required]" style="width: 75%;">
                                        <option value="" location-group="0"><?php echo INPUT_SELECT; ?></option>
                                    <?php 
                                    foreach($locations AS $location){
                                    ?>
                                        <option value="<?php echo $location['Location']['id']; ?>" <?php if($location['Location']['id'] == $this->data['VendorConsignment']['location_id']){ ?>selected="selected"<?php } ?> location-group="<?php echo $location['Location']['location_group_id']; ?>"><?php echo $location['Location']['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->input('note', array('style' => 'width:90%; height: 66px;', 'label' => false)); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr> 
            <tr>
                <td>
                    <table cellpadding="0" style="width: 100%">
                        <tr>
                            <td style="width: 34%;"><label for="VendorConsignmentVendorName"><?php echo TABLE_VENDOR; ?></label> <span class="red">*</span></td>
                            <td style="width: 33%;"><label for="VendorConsignmentCode"><?php echo TABLE_CONSIGNMENT_CODE; ?></label></td>
                            <td style="width: 33%;"><label for="VendorConsignmentDate"><?php echo TABLE_CONSIGNMENT_DATE; ?></label></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->hidden('vendor_id', array('value' => $this->data['Vendor']['id'])); ?>
                                    <?php echo $this->Form->text('vendor_name', array('value' => ($this->data['Vendor']['vendor_code'].'-'.$this->data['Vendor']['name']), 'class' => 'validate[required,funcCall[checkVendorVendorConsignment]]', 'style' => 'width:70%')); ?>
                                    &nbsp;&nbsp;<img alt="Search" align="absmiddle" style="cursor: pointer; width:22px; height: 22px; display: none;" id="searchVendorVendorConsignment" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                                    <img alt="Delete" align="absmiddle" id="deleteVendorVendorConsignment" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" style="cursor: pointer;" />
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('code', array('class' => 'validate[required]', 'style' => 'width:70%', 'readonly' => TRUE)); ?>
                                </div> 
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <?php echo $this->Form->text('date', array('value' => dateShort($this->data['VendorConsignment']['date']), 'class' => 'validate[required]', 'readonly' => TRUE, 'style' => 'width:70%')); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="inputContainer" style="width:100%" id="searchFormVendorConsignment">
    <table width="100%">
        <tr>
            <td style="width: 300px; text-align: left;">
                <input type="text" id="VendorConsignmentSearchPUC" style="width:90%; height: 15px;" placeholder="<?php echo TABLE_SCAN_ENTER_UPC; ?>" />
            </td>
            <td style="width: 300px; text-align: left;">
                <input type="text" id="VendorConsignmentSearchSKU" style="width:90%; height: 15px;" placeholder="<?php echo TABLE_SEARCH_SKU_NAME; ?>" />
            </td>
            <td id="divSearchVendorConsignment" style="width: 200px; text-align: left;">
                <img alt="Search" align="absmiddle" style="cursor: pointer;" id="purchaseSearchProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
            <td style="text-align: right;"> </td>
        </tr>
    </table>
</div>
<div id="hiddenUom" style="display: none"></div>
<table id="tblHeaderVendorConsignment" class="table" cellspacing="0" style="padding:0px;">
    <tr>
        <th class="first" style="width:4%"><?php echo TABLE_NO; ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:10%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:17%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:8%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width:8%;"><?php echo TABLE_EXP_DATE_SHORT; ?></th>
        <th style="width:5%;"><?php echo TABLE_QTY; ?></th>
        <th style="width:13%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:10%;"><?php echo TABLE_UNIT_COST; ?></th>
        <th style="width:10%;"><?php echo TABLE_TOTAL; ?></th>
        <th style="width:5%;"></th>
    </tr>
</table>
<div id="bodyListVendorConsignment" style="padding:0px;">
    <table id="tblVendorConsignment" class="table" cellspacing="0" style="padding:0px;">
        <tr id="detailVendorConsignment" class="listBodyVendorConsignment" style="visibility: hidden;">
            <td class="first" style="width:4%;"></td>
            <td style="width:10%;"><input type="text" class="VendorConsignmentPUC" value="" /></td>
            <td style="width:10%;"><input type="text" class="VendorConsignmentSKU"value="" /></td>
            <td style="width:17%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" class="product_id" id="product_id" />
                    <input type="hidden" name="note[]" class="note" id="note" />
                    <input type="text" name="product_name[]" class="product_name validate[required]" id="product_name" readonly="readonly" style="width: 85%" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddVendorConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="lots_number[]" id="lots_number" style="width:90%" class="lots_number" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" value="0" class="is_expired" />
                    <input type="text" name="date_expired[]" id="date_expired" style="width:90%" class="date_expired"​ readonly="" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:5%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty" name="qty[]" style="width:90%;" class="qty validate[required]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:13%;">
                <div class="inputContainer" style="width:100%">         
                    <input type="hidden" class="small_uom_val" name="small_uom_val[]"/> 
                    <input type="hidden" class="conversion" name="conversion[]"/>                                        
                    <select id="qty_uom_id" name="qty_uom_id[]" style="width:90%; height: 20px;" class="qty_uom_id validate[required]">
                        <?php
                        foreach ($uoms as $uom) {
                            echo "<option value='{$uom['Uom']['id']}' conversion='1'>{$uom['Uom']['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="unit_cost" name="unit_cost[]" class="unit_cost validate[required] float" style="width:90%" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%;">
                <input type="text" name="total_cost[]" id="total_cost" style="width:90%;" class="total_cost float" />
            </td>
            <td style="white-space:nowrap; padding:0px; text-align:center; width:5%;">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveVendorConsignment" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
        $index = 0;
        foreach($vendorConsignmentDetails AS $vendorConsignmentDetail){
        ?>
        <tr class="listBodyVendorConsignment">
            <td class="first" style="width:4%"><?php echo ++$index; ?></td>
            <td style="width:10%;"><span class="purchasePUC"><?php echo $vendorConsignmentDetail['Product']['barcode']; ?></span></td>
            <td style="width:10%;"><span class="purchaseSKU"><?php echo $vendorConsignmentDetail['Product']['code']; ?></span></td>
            <td style="width:17%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" name="product_id[]" value="<?php echo $vendorConsignmentDetail['Product']['id']; ?>" class="product_id" id="product_id" />
                    <input type="hidden" name="note[]" value="<?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['note']; ?>" class="note" id="note" />
                    <input type="text" name="product_name[]" value="<?php echo str_replace('"', '&quot;', $vendorConsignmentDetail['Product']['name']); ?>" class="product_name validate[required]" id="product_name_<?php echo $index; ?>" readonly="readonly" style="width: 85%" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddVendorConsignment" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="lots_number[]" value="<?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['lots_number']; ?>" id="lots_number_<?php echo $index; ?>" style="width:90%" class="lots_number" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:8%;">
                <div class="inputContainer" style="width:100%">
                    <input type="hidden" value="<?php echo $vendorConsignmentDetail['Product']['is_expired_date']; ?>" class="is_expired" />
                    <input type="<?php if($vendorConsignmentDetail['Product']['is_expired_date'] == 0){ ?>hidden<?php }else{ ?>text<?php } ?>" name="date_expired[]" value="<?php if($vendorConsignmentDetail['VendorConsignmentDetail']['date_expired'] != '' && $vendorConsignmentDetail['VendorConsignmentDetail']['date_expired'] != '0000-00-00'){ echo dateShort($vendorConsignmentDetail['VendorConsignmentDetail']['date_expired']); } ?>" id="date_expired_<?php echo $index; ?>" style="width:90%" <?php if($vendorConsignmentDetail['Product']['is_expired_date'] == 1){ ?>class="date_expired"<?php } ?> readonly="readonly" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:5%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" name="qty[]" value="<?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['qty']; ?>" style="width:90%;" class="qty validate[required]" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:13%;">
                <div class="inputContainer" style="width:100%">         
                    <input type="hidden" class="small_uom_val" name="small_uom_val[]" value="<?php echo $vendorConsignmentDetail['Product']['small_val_uom']; ?>" /> 
                    <input type="hidden" class="conversion" name="conversion[]" value="<?php echo $vendorConsignmentDetail['VendorConsignmentDetail']['conversion']; ?>" />                                        
                    <select id="qty_uom_id_<?php echo $index; ?>" name="qty_uom_id[]" style="width:90%; height: 20px;" class="qty_uom_id validate[required]">
                        <?php
                        $queryUom = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$vendorConsignmentDetail['Product']['price_uom_id']."
                                                UNION
                                                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$vendorConsignmentDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$vendorConsignmentDetail['Product']['price_uom_id'].")
                                                ORDER BY conversion ASC");
                        $k = 1;
                        $options = "";
                        $length = mysql_num_rows($queryUom);
                        while($dataUom=mysql_fetch_array($queryUom)){
                            if($length == $k){
                                $dataSm = 1;
                            }else{
                                $dataSm = 0;
                            }
                            if($dataUom['id'] == $vendorConsignmentDetail['Product']['price_uom_id']){
                                $dataItem = "first";
                            }else{
                                $dataItem = "other";
                            }
                            if($dataUom['id'] == $vendorConsignmentDetail['VendorConsignmentDetail']['qty_uom_id']){
                                $selected = 'selected="selected"';
                            }else{
                                $selected = '';
                            }
                            $options .='<option data-sm="'.$dataSm.'" data-item="'.$dataItem.'" value="'.$dataUom['id'].'" '.$selected.' conversion="'.$dataUom['conversion'].'">'.$dataUom['name'].'</option>';

                        $k++;
                        }
                        echo $options;
                        ?>
                    </select>
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="unit_cost_<?php echo $index; ?>" name="unit_cost[]" value="<?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['unit_cost'], 2); ?>" class="unit_cost validate[required] float" style="width:90%" />
                </div>
            </td>
            <td style="padding:0px; text-align: center; width:10%;">
                <input type="text" name="total_cost[]" value="<?php echo number_format($vendorConsignmentDetail['VendorConsignmentDetail']['total_cost'], 2); ?>" id="total_cost_<?php echo $index; ?>" style="width:90%" class="total_cost float" />
            </td>
            <td style="white-space:nowrap; padding:0px; text-align:center; width:5%;">
                <img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveVendorConsignment" style="cursor: pointer;" onmouseover="Tip('Remove')" />
            </td>
        </tr>
        <?php
        }
        ?>
    </table>
</div>
<div id="VendorConsignmentFooter">
    <div style="float: left; width: 15%;">
        <div class="buttons">
            <a href="#" class="positive btnBackVendorConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
        <div class="buttons">
            <button type="submit" class="positive btnSaveVendorConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                <span class="txtSaveVendorConsignment"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
    </div>
    <div style="float: right; width: 350px;">
        <table cellpadding="0" style="width:100%; padding: 0px; margin: 0px;">
            <tr>
                <td style="text-align: right;"><label for="VendorConsignmentTotalAmount"><?php echo TABLE_SUB_TOTAL; ?>:</label></td>
                <td style="width:250px;">
                    <?php echo $this->Form->text('total_amount', array('name'=>'data[VendorConsignment][total_amount]', 'readonly' => true, 'style' => 'width: 80%; height:15px; font-size:12px; font-weight: bold', 'value'=> number_format($this->data['VendorConsignment']['total_amount'], 2))); ?> <span class="lblSymbolVendorConsignment"><?php echo $this->data['CurrencyCenter']['symbol']; ?></span>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>