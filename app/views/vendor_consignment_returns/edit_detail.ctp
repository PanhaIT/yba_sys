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
        
        // Check Event Key
        checkEventVendorConsignmentReturn();
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
    <table id="tblVendorConsignmentReturn" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <?php
        $index = 0;
        $dateOrder = $vendorConsignmentReturn['VendorConsignmentReturn']['date'];
        if(!empty($vendorConsignmentReturnDetails)){
            foreach($vendorConsignmentReturnDetails AS $vendorConsignmentReturnDetail){
                // Total PB Receive
                $totalPB = 0;
                $sqlPB = mysql_query("SELECT SUM(purchase_receives.qty * purchase_receives.conversion) AS total_pb FROM purchase_receives INNER JOIN purchase_orders ON purchase_orders.id = purchase_receives.purchase_order_id AND purchase_orders.vendor_consignment_id = {$vendorConsignmentReturn['VendorConsignmentReturn']['vendor_consignment_id']} AND purchase_orders.status > 0 WHERE purchase_orders.location_group_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id']." AND purchase_orders.location_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_id']." AND purchase_receives.product_id = {$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id']} AND purchase_receives.lots_number = '".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']."' AND purchase_receives.date_expired = '".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']."'");
                if(mysql_num_rows($sqlPB)){
                    $rowPB = mysql_fetch_array($sqlPB);
                    $totalPB = $rowPB[0];
                }
                // Total Vendor Return Consignment
                $totalReturn = 0;
                $sqlReturn = mysql_query("SELECT SUM(qty * conversion) AS total_return FROM vendor_consignment_return_details INNER JOIN vendor_consignment_returns ON vendor_consignment_returns.id = vendor_consignment_return_details.vendor_consignment_return_id AND vendor_consignment_returns.status > 0 AND vendor_consignment_returns.location_group_id = ".$vendorConsignmentReturn['VendorConsignmentReturn']['location_group_id']." AND vendor_consignment_returns.location_id = ".$vendorConsignmentReturn['VendorConsignment']['location_id']." WHERE vendor_consignment_return_id != ".$vendorConsignmentReturn['VendorConsignmentReturn']['id']." AND  product_id = {$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['product_id']} AND lots_number = '".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']."' AND date_expired = '".$vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']."'");
                if(mysql_num_rows($sqlReturn)){
                    $rowReturn = mysql_fetch_array($sqlReturn);
                    $totalReturn = $rowReturn[0];
                }
                $totalOrder = ($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'] * $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']);
                $totalQtyVendorConsignmentReturn = ($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'] - $totalPB - $totalReturn)>0?($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order'] - $totalPB - $totalReturn):0;
                $isSmallSelected = 0;
                if($totalQtyVendorConsignmentReturn < $totalOrder){
                    $totalOrder = $totalQtyVendorConsignmentReturn;
                    $isSmallSelected = 1;
                }
                // Check Name With Customer
                $productName = $vendorConsignmentReturnDetail['Product']['name'];
        ?>
        <tr class="tblVendorConsignmentReturnList">
            <td class="first" style="width:5%; text-align: center; padding: 0px;"><?php echo ++$index; ?></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="<?php echo $vendorConsignmentReturnDetail['Product']['barcode']; ?>" /></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="<?php echo $vendorConsignmentReturnDetail['Product']['code']; ?>" /></td>
            <td style="width:23%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">
                    <input type="hidden" class="totalQtyVendorConsignmentReturn" value="<?php echo $totalQtyVendorConsignmentReturn; ?>" />
                    <input type="hidden" class="totalQtyOrderVendorConsignmentReturn" value="<?php echo $totalOrder; ?>" />
                    <input type="hidden" value="" name="default_order[]" value="<?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['default_order']; ?>" />
                    <input type="hidden" name="product_id[]" value="<?php echo $vendorConsignmentReturnDetail['Product']['id']; ?>" class="product_id" />
                    <input type="hidden" name="conversion[]" class="conversion" value="<?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['conversion']; ?>" />
                    <input type="hidden" name="small_uom_val[]" class="small_uom_val" value="<?php echo $vendorConsignmentReturnDetail['Product']['small_val_uom']; ?>" />
                    <input type="hidden" name="note[]" id="note" class="note" value="<?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['note']; ?>" />
                    <input type="text" id="product_<?php echo $index; ?>" name="product[]" value="<?php echo str_replace('"', '&quot;', $productName); ?>" class="product validate[required]" style="width: 90%;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="width: 11%; padding: 0px; text-align: center; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="lots_number_<?php echo $index; ?>" name="lots_number[]" value="<?php echo $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['lots_number']; ?>" readonly="readonly" style="width:90%;" class="lots_number" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%;">
                    <?php
                    $expDisplay = '';
                    $class = '';
                    if($vendorConsignmentReturnDetail['Product']['is_expired_date'] == 0){
                        $expDisplay = 'visibility: hidden;';
                        $dateExp = '0000-00-00';
                    } else {
                        $dateExp = '';
                        $class = 'class="date_expired validate[required]"';
                        if($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] != "" && $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired'] != "0000-00-00"){
                            $dateExp = dateShort($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['date_expired']);
                        }
                    }
                    ?>
                    <input type="text" id="date_expired_<?php echo $index; ?>" name="date_expired[]" value="<?php echo $dateExp; ?>" readonly="readonly" style="width:90%; <?php echo $expDisplay; ?>" <?php echo $class; ?> />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" name="qty[]" value="<?php echo number_format($vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty'], 0); ?>" style="width:90%;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:15%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id_<?php echo $index; ?>" style="width:90%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php
                        $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$vendorConsignmentReturnDetail['Product']['price_uom_id']."
                                            UNION
                                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$vendorConsignmentReturnDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$vendorConsignmentReturnDetail['Product']['price_uom_id'].")
                                            ORDER BY conversion ASC");
                        $i = 1;
                        $length = mysql_num_rows($query);
                        while($data=mysql_fetch_array($query)){
                            $selected = "";
                            if($data['id'] == $vendorConsignmentReturnDetail['VendorConsignmentReturnDetail']['qty_uom_id'] && $isSmallSelected == 0){   
                                $selected = ' selected="selected" ';
                            }
                            if($length == $i && $isSmallSelected == 1){
                                $selected = ' selected="selected" ';
                            }
                        ?>
                        <option <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $vendorConsignmentReturnDetail['Product']['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                        <?php 
                        $i++;
                        } 
                        ?>
                    </select>
                </div>
            </td>
            <td style="width:7%; white-space: nowrap;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownVendorConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
</div>