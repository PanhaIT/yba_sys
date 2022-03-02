<?php
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);
?>
<script type="text/javascript">
    var rowIndexLandingCost = 0;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var waitForFinalEventLandingCost = (function () {
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
        if(tabLandingCostReg != tabLandingCostId){
            $("a[href='"+tabLandingCostId+"']").click(function(){
                if($(".orderDetailLandingCost").html() != '' && $(".orderDetailLandingCost").html() != null){
                    waitForFinalEventLandingCost(function(){
                        refreshScreenLandingCost();
                        resizeFormTitleLandingCost();
                        resizeFornScrollLandingCost();
                    }, 500, "Finish");
                }
            });
            tabLandingCostReg = tabLandingCostId;
        }
        
        waitForFinalEventLandingCost(function(){
            refreshScreenLandingCost();
            resizeFormTitleLandingCost();
            resizeFornScrollLandingCost();
        }, 500, "Finish");
        
        $(window).resize(function(){
            if(tabLandingCostReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventLandingCost(function(){
                    refreshScreenLandingCost();
                    resizeFormTitleLandingCost();
                    resizeFornScrollLandingCost();
                }, 500, "Finish");
            }
        });
        
        // Hide / Show Header
        $("#btnHideShowHeaderLandingCost").click(function(){
            var LandingCostCompanyId  = $("#LandingCostCompanyId").val();
            var LandingCostBranchId   = $("#LandingCostBranchId").val();
            var LandingCostDate       = $("#LandingCostDate").val();
            var LandingCostVendorName = $("#LandingCostVendorName").val();
            var LandingCostApId       = $("#LandingCostApId").val();
            var LandingCostLandType   = $("#LandingCostLandedCostTypeId").val();
            
            if(LandingCostCompanyId == "" || LandingCostBranchId == "" || LandingCostApId == "" || LandingCostLandType == "" || LandingCostDate == "" || LandingCostVendorName == ""){
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
                    $("#LandingCostTop").hide();
                    img += 'arrow-down.png';
                } else {
                    action = 'Hide';
                    $("#LandingCostTop").show();
                    img += 'arrow-up.png';
                }
                $(this).find("span").text(action);
                $(this).find("img").attr("src", img);
                if(tabLandingCostReg == $(".ui-tabs-selected a").attr("href")){
                    waitForFinalEventLandingCost(function(){
                        resizeFornScrollLandingCost();
                    }, 500, "Finish");
                }
            }   
        });
        // Check CSS LandingCost
        changeInputCSSLandingCost();
        // Check Char Account
        checkChartAccountLadingCost();
    });
    
    function resizeFormTitleLandingCost(){
        var screen = 16;
        var widthList = $("#bodyList").width();
        $("#tblLandingCostHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblLandingCostHeader").css('padding','0px');
        $("#tblLandingCostHeader").css('margin-top','5px');
        $("#tblLandingCostHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollLandingCost(){
        var windowHeight = $(window).height();
        var formHeader = 0;
        if ($('#LandingCostTop').is(':hidden')) {
            formHeader = 0;
        } else {
            formHeader = $("#LandingCostTop").height();
        }
        var btnHeader    = $("#btnHideShowHeaderLandingCost").height();
        var formFooter   = $(".footerFormLandingCost").height();
        var formSearch   = $("#searchFormLandingCost").height();
        var tableHeader  = $("#tblLandingCostHeader").height();
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
    
    function refreshScreenLandingCost(){
        $("#tblLandingCostHeader").removeAttr('style');
    }
    
    function keyEventLandingCost(){
        $(".landedCost, .btnRemoveLandingCost").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".float").autoNumeric({mDec: <?php echo $rowOption[0]; ?>, aSep: ','});
        
        $(".landedCost").blur(function(){
            if($(this).val() == ""){
                $(this).val(0);
            }
            calcTotalAmountLandingCost();
        });
        
        $(".landedCost").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".landedCost").keyup(function(){
            calcTotalAmountLandingCost();
        });
        
        $(".btnRemoveLandingCost").click(function(){
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
                        calcTotalAmountLandingCost();
                        sortNuTableLandingCost();
                        $(this).dialog("close");
                    }
                }
            });
            
        });
        
        moveRowLandingCost();
    }
    
    function checkEventLandingCost(){
        keyEventLandingCost();
        $(".tblLandingCostList").unbind("click");
        $(".tblLandingCostList").click(function(){
            keyEventLandingCost();
        });
    }
    
    function moveRowLandingCost(){
        $(".btnMoveDownLandingCost, .btnMoveUpLandingCost").unbind('click');
        $(".btnMoveDownLandingCost").click(function () {
            var rowToMove = $(this).parents('tr.tblLandingCostList:first');
            var next = rowToMove.next('tr.tblLandingCostList');
            if (next.length == 1) { next.after(rowToMove); }
            sortNuTableLandingCost();
        });

        $(".btnMoveUpLandingCost").click(function () {
            var rowToMove = $(this).parents('tr.tblLandingCostList:first');
            var prev = rowToMove.prev('tr.tblLandingCostList');
            if (prev.length == 1) { prev.before(rowToMove); }
            sortNuTableLandingCost();
        });
        
        sortNuTableLandingCost();
    }

    function checkExistingRecordLandingCost(productId){
        var isFound = false;
        $("#tblLandingCost").find("tr").each(function(){
            if(productId == $(this).find("input[name='product_id[]']").val()){
                isFound = true;
            }
        });
        return isFound;
    }
    
    function calcTotalAmountLandingCost(){
        var totalAmount   = 0;
        $(".tblLandingCostList").find(".landedCost").each(function(){
            if(replaceNum($(this).val()) != '' || $(this).val() != undefined ){
                totalAmount += replaceNum($(this).val());
            }
        });
        $("#LandingCostTotalAmount").val((totalAmount).toFixed(<?php echo $rowOption[0]; ?>));
    }
    
    function sortNuTableLandingCost(){
        var sort = 1;
        $(".tblLandingCostList").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
</script>
<div style="clear: both;"></div>
<table id="tblLandingCostHeader" class="table" cellspacing="0" style="margin-top: 5px; padding:0px; width: 100%">
    <tr>
        <th class="first" style="width:5%;"><?php echo TABLE_NO ?></th>
        <th style="width:10%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width:10%;"><?php echo TABLE_SKU; ?></th>
        <th style="width:23%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width:8%;"><?php echo TABLE_QTY ?></th>
        <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
        <th style="width:11%;"><?php echo TABLE_UNIT_COST ?></th>
        <th style="width:11%;"><?php echo TABLE_LANDED_COST; ?></th>
        <th style="width:7%;"></th>
    </tr>
</table>
<div id="bodyList">
    <table id="tblLandingCost" class="table" cellspacing="0" style="padding: 0px; width:100%">
        
    </table>
</div>