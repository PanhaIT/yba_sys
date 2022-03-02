<?php
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    var rowIndexVendorConsignmentReturn = 0;
    var timeBarcodeVendorConsignmentReturn = 1;
    var invTotalQtyVendorConsignmentReturn = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var waitForFinalEventVendorConsignmentReturn = (function () {
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
        if(tabVendorConsignmentReturnReg != tabVendorConsignmentReturnId){
            $("a[href='"+tabVendorConsignmentReturnId+"']").click(function(){
                if($(".orderDetailVendorConsignmentReturn").html() != '' && $(".orderDetailVendorConsignmentReturn").html() != null){
                    waitForFinalEventVendorConsignmentReturn(function(){
                        refreshScreenVendorConsignmentReturn();
                        resizeFormTitleVendorConsignmentReturn();
                        resizeFornScrollVendorConsignmentReturn();
                    }, 500, "Finish");
                }
            });
            tabVendorConsignmentReturnReg = tabVendorConsignmentReturnId;
        }
        
        waitForFinalEventVendorConsignmentReturn(function(){
            refreshScreenVendorConsignmentReturn();
            resizeFormTitleVendorConsignmentReturn();
            resizeFornScrollVendorConsignmentReturn();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabVendorConsignmentReturnReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventVendorConsignmentReturn(function(){
                    refreshScreenVendorConsignmentReturn();
                    resizeFormTitleVendorConsignmentReturn();
                    resizeFornScrollVendorConsignmentReturn();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderVendorConsignmentReturn").click(function(){
            var VendorConsignmentReturnCompanyId       = $("#VendorConsignmentReturnCompanyId").val();
            var VendorConsignmentReturnBranchId        = $("#VendorConsignmentReturnBranchId").val();
            var VendorConsignmentReturnLocationGroupId = $("#VendorConsignmentReturnLocationGroupId").val();
            var VendorConsignmentReturnLocationId      = $("#VendorConsignmentReturnLocationId").val();
            var VendorConsignmentReturnDate            = $("#VendorConsignmentReturnDate").val();
            var VendorConsignmentReturnVendorName      = $("#VendorConsignmentReturnVendorName").val();
            var VendorConsignmentReturnConsignment     = $("#VendorConsignmentReturnVendorConsignment").val();
            
            if(VendorConsignmentReturnCompanyId == "" || VendorConsignmentReturnBranchId == "" || VendorConsignmentReturnLocationGroupId == "" || VendorConsignmentReturnDate == "" || VendorConsignmentReturnVendorName == "" || VendorConsignmentReturnLocationId == "" || VendorConsignmentReturnConsignment == ""){
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
                    $("#VendorConsignmentReturnTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#VendorConsignmentReturnTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabVendorConsignmentReturnReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventVendorConsignmentReturn(function(){
                        resizeFornScrollVendorConsignmentReturn();
                    }, 300, "Finish");
                }
            }   
        });
        
        // Check CSS VendorConsignmentReturn
        changeInputCSSVendorConsignmentReturn();
    });
    
    function resizeFormTitleVendorConsignmentReturn(){
        var screen = 16;
        var widthList = $("#bodyList").width();
        $("#tblVendorConsignmentReturnHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblVendorConsignmentReturnHeader").css('padding','0px');
        $("#tblVendorConsignmentReturnHeader").css('margin-top','5px');
        $("#tblVendorConsignmentReturnHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollVendorConsignmentReturn(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#VendorConsignmentReturnTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#VendorConsignmentReturnTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderVendorConsignmentReturn").height();
        var formFooter   = $(".footerFormVendorConsignmentReturn").height();
        var tableHeader  = $("#tblVendorConsignmentReturnHeader").height();
        var screenRemain = 238;
        var getHeight    = windowHeight - (formHeader + btnHeader + formFooter + tableHeader + screenRemain);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyList").css('height',getHeight);
        $("#bodyList").css('padding','0px');
        $("#bodyList").css('width','100%');
        $("#bodyList").css('overflow-x','hidden');
        $("#bodyList").css('overflow-y','scroll');
    }
    
    function refreshScreenVendorConsignmentReturn(){
        $("#tblVendorConsignmentReturnHeader").removeAttr('style');
    }

    function keyEventVendorConsignmentReturn(){
        $(".qty, .qty_uom_id, .btnRemoveVendorConsignmentReturn, .noteAddVendorConsignmentReturn").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: 3, aSep: ','});
        $(".floatQty").priceFormat();
        
        $(".qty").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
        });
        
        $(".qty").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qty").keyup(function(){
            var conversion = $(this).closest("tr").find(".conversion").val();
            var qty        = replaceNum($(this).closest("tr").find(".qty").val());
            var totalOrder = replaceNum(converDicemalJS(qty * conversion));
            $(this).closest("tr").find(".totalQtyOrderVendorConsignmentReturn").val(totalOrder);
            funcCheckCondiVendorConsignmentReturn($(this));
        });
        
        $(".qty").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").find(".unit_price").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#searchProductSkuVendorConsignmentReturn").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){   
            var productId     = replaceNum($(this).closest("tr").find("input[name='product_id[]']").val());
            if(productId != "" && productId > 0){
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum($(this).find("option[data-sm='1']").attr("conversion"));
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                $(this).closest("tr").find(".conversion").val(conversion);
            }
            funcCheckCondiVendorConsignmentReturn($(this));
        });

        $(".btnRemoveVendorConsignmentReturn").click(function(){
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
                        sortNuTableVendorConsignmentReturn();
                        $(this).dialog("close");
                    }
                }
            });
            
        });
        
        $(".noteAddVendorConsignmentReturn").click(function(){
            addNoteVendorConsignmentReturn($(this));
        });
        
        moveRowVendorConsignmentReturn();
    }
    
    function checkEventVendorConsignmentReturn(){
        keyEventVendorConsignmentReturn();
        $(".tblVendorConsignmentReturnList").unbind("click");
        $(".tblVendorConsignmentReturnList").click(function(){
            keyEventVendorConsignmentReturn();
        });
    }

    function moveRowVendorConsignmentReturn(){
        $(".btnMoveDownVendorConsignmentReturn, .btnMoveUpVendorConsignmentReturn").unbind('click');
        $(".btnMoveDownVendorConsignmentReturn").click(function () {
            var rowToMove = $(this).parents('tr.tblVendorConsignmentReturnList:first');
            var next = rowToMove.next('tr.tblVendorConsignmentReturnList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableVendorConsignmentReturn();
        });

        $(".btnMoveUpVendorConsignmentReturn").click(function () {
            var rowToMove = $(this).parents('tr.tblVendorConsignmentReturnList:first');
            var prev = rowToMove.prev('tr.tblVendorConsignmentReturnList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableVendorConsignmentReturn();
        });
        
        sortNuTableVendorConsignmentReturn();
    }

    function checkExistingRecordVendorConsignmentReturn(productId){
        var isFound = false;
        $("#tblVendorConsignmentReturn").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function funcCheckCondiVendorConsignmentReturn(current){
        var tr            = current.closest("tr");
        var productId     = tr.find("input[name='product_id[]']").val();
        var qty           = replaceNum(tr.find("input[name='qty[]']").val());
        var qtyOrder      = replaceNum(getTotalQtyOrderVendorConsignmentReturn(productId));
        var totalQtyVendorConsignmentReturn = replaceNum(tr.find(".totalQtyVendorConsignmentReturn").val());
        // Check Product With Qty Order And Total Qty Sale
        if(productId != ""){
            if(qtyOrder > totalQtyVendorConsignmentReturn){
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
        // Assign Value to Qty & Total Price
        tr.find("input[name='qty[]']").val(qty);
    }
    
    function sortNuTableVendorConsignmentReturn(){
        var sort = 1;
        $(".tblVendorConsignmentReturnList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOrderVendorConsignmentReturn(id){
        var totalProduct=0;
        $("input[name='product_id[]']").each(function(){
            if($(this).val() == id){
                totalProduct += replaceNum($(this).closest("tr").find(".totalQtyOrderVendorConsignmentReturn").val());
            }
        });
        return totalProduct;
    }
    
    function addNoteVendorConsignmentReturn(currentTr){
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
<div style="clear: both;"></div>
<table id="tblVendorConsignmentReturnHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%">
    <tr>
        <th class="first" style="width:5%;"><?php echo TABLE_NO ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:10%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:23%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:11%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO ?></th>
        <th style="width:11%;"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:8%;"><?php echo TABLE_QTY ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblVendorConsignmentReturn" class="table" cellspacing="0" style="padding: 0px; width:100%"></table>
</div>