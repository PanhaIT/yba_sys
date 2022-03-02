<script type="text/javascript">
    var rowIndexConsignmentReturn = 0;
    var invTotalQtyConsignmentReturn = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var waitForFinalEventConsignmentReturn = (function () {
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
        if(tabConsignmentReturnReg != tabConsignmentReturnId){
            $("a[href='"+tabConsignmentReturnId+"']").click(function(){
                if($(".orderDetailConsignmentReturn").html() != '' && $(".orderDetailConsignmentReturn").html() != null){
                    waitForFinalEventConsignmentReturn(function(){
                        refreshScreenConsignmentReturn();
                        resizeFormTitleConsignmentReturn();
                        resizeFornScrollConsignmentReturn();
                    }, 500, "Finish");
                }
            });
            tabConsignmentReturnReg = tabConsignmentReturnId;
        }
        
        waitForFinalEventConsignmentReturn(function(){
            refreshScreenConsignmentReturn();
            resizeFormTitleConsignmentReturn();
            resizeFornScrollConsignmentReturn();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabConsignmentReturnReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventConsignmentReturn(function(){
                    refreshScreenConsignmentReturn();
                    resizeFormTitleConsignmentReturn();
                    resizeFornScrollConsignmentReturn();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderConsignmentReturn").click(function(){
            var ConsignmentReturnCompanyId       = $("#ConsignmentReturnCompanyId").val();
            var ConsignmentReturnBranchId        = $("#ConsignmentReturnBranchId").val();
            var ConsignmentReturnLocationGroupId = $("#ConsignmentReturnLocationGroupId").val();
            var ConsignmentReturnDate            = $("#ConsignmentReturnDate").val();
            var ConsignmentReturnCustomerName    = $("#ConsignmentReturnCustomerName").val();
            
            if(ConsignmentReturnCompanyId == "" || ConsignmentReturnBranchId == "" || ConsignmentReturnLocationGroupId == "" || ConsignmentReturnDate == "" || ConsignmentReturnCustomerName == ""){
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
                    $("#ConsignmentReturnTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#ConsignmentReturnTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabConsignmentReturnReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventConsignmentReturn(function(){
                        resizeFornScrollConsignmentReturn();
                    }, 500, "Finish");
                }
            }   
        });
        // Check CSS ConsignmentReturn
        changeInputCSSConsignmentReturn();
    });
    
    function resizeFormTitleConsignmentReturn(){
        var screen = 16;
        var widthList = $("#bodyList").width();
        $("#tblConsignmentReturnHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblConsignmentReturnHeader").css('padding','0px');
        $("#tblConsignmentReturnHeader").css('margin-top','5px');
        $("#tblConsignmentReturnHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollConsignmentReturn(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#ConsignmentReturnTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#ConsignmentReturnTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderConsignmentReturn").height();
        var formFooter   = $(".footerFormConsignmentReturn").height();
        var formSearch   = $("#searchFormConsignmentReturn").height();
        var tableHeader  = $("#tblConsignmentReturnHeader").height();
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
    
    function refreshScreenConsignmentReturn(){
        $("#tblConsignmentReturnHeader").removeAttr('style');
    }
    
    function keyEventConsignmentReturn(){
        $(".qty, .qty_uom_id, .btnRemoveConsignmentReturn, .noteAddConsignmentReturn").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".floatQty").autoNumeric({mDec: 0, aSep: ','});
        
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
            $(this).closest("tr").find(".totalQtyOrderConsignmentReturn").val(totalOrder);
            funcCheckCondiConsignmentReturn($(this));
        });
        
        $(".qty").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").find(".unit_price").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $("#searchProductSkuConsignmentReturn").select().focus();
                return false;
            }
        });
        
        $(".qty_uom_id").change(function(){   
            var productId     = replaceNum($(this).closest("tr").find("input[name='product_id[]']").val());
            if(productId != "" && productId > 0){
                var priceType     = $("#typeOfPriceConsignmentReturn").val();
                var value         = replaceNum($(this).val());
                var uomConversion = replaceNum($(this).find("option[value='"+value+"']").attr('conversion'));
                var uomSmall      = replaceNum($(this).find("option[data-sm='1']").attr("conversion"));
                var conversion    = converDicemalJS(uomSmall / uomConversion);
                var unitPrice     = replaceNum($(this).find("option:selected").attr("price-uom-"+priceType));
                $(this).closest("tr").find(".unit_price").val(unitPrice.toFixed(2));
                $(this).closest("tr").find(".conversion").val(conversion);
            }
            funcCheckCondiConsignmentReturn($(this));
        });
        
        $(".btnRemoveConsignmentReturn").click(function(){
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
                        sortNuTableConsignmentReturn();
                        $(this).dialog("close");
                    }
                }
            });
            
        });
        
        $(".noteAddConsignmentReturn").click(function(){
            addNoteConsignmentReturn($(this));
        });
        
        moveRowConsignmentReturn();
    }
    
    function checkEventConsignmentReturn(){
        keyEventConsignmentReturn();
        $(".tblConsignmentReturnList").unbind("click");
        $(".tblConsignmentReturnList").click(function(){
            keyEventConsignmentReturn();
        });
    }
    
    function moveRowConsignmentReturn(){
        $(".btnMoveDownConsignmentReturn, .btnMoveUpConsignmentReturn").unbind('click');
        $(".btnMoveDownConsignmentReturn").click(function () {
            var rowToMove = $(this).parents('tr.tblConsignmentReturnList:first');
            var next = rowToMove.next('tr.tblConsignmentReturnList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableConsignmentReturn();
        });

        $(".btnMoveUpConsignmentReturn").click(function () {
            var rowToMove = $(this).parents('tr.tblConsignmentReturnList:first');
            var prev = rowToMove.prev('tr.tblConsignmentReturnList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableConsignmentReturn();
        });
        
        sortNuTableConsignmentReturn();
    }

    function checkExistingRecordConsignmentReturn(productId){
        var isFound = false;
        $("#tblConsignmentReturn").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }

    function funcCheckCondiConsignmentReturn(current){
        var tr            = current.closest("tr");
        var productId     = tr.find("input[name='product_id[]']").val();
        var qtyOrder      = replaceNum(getTotalQtyOrderConsignmentReturn(productId));
        var totalQtyConsignmentReturn = replaceNum(tr.find(".totalQtyConsignmentReturn").val());
        // Check Product With Qty Order And Total Qty Sale
        if(productId != ""){
            if(qtyOrder > totalQtyConsignmentReturn){
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
    }
    
    function sortNuTableConsignmentReturn(){
        var sort = 1;
        $(".tblConsignmentReturnList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function getTotalQtyOrderConsignmentReturn(id){
        var totalProduct=0;
        $("input[name='product_id[]']").each(function(){
            if($(this).val() == id){
                totalProduct += replaceNum($(this).closest("tr").find(".totalQtyOrderConsignmentReturn").val());
            }
        });
        return totalProduct;
    }
    
    function addNoteConsignmentReturn(currentTr){
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
<table id="tblConsignmentReturnHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%">
    <tr>
        <th class="first" style="width:5%;"><?php echo TABLE_NO ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:10%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:23%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:8%;"><?php echo TABLE_QTY ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:11%;"><?php echo TABLE_LOTS_NO ?></th>
        <th style="width:11%;"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblConsignmentReturn" class="table" cellspacing="0" style="padding: 0px; width:100%"></table>
</div>