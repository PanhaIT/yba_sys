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
        // Event Action
        checkEventConsignmentReturn();
        <?php  
        if(empty($consignmentReturn)) {
        ?>
        changeInputCSSConsignmentReturn();
        <?php
        }
        ?>
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
                totalProduct += (Number(replaceNum($(this).closest("tr").find(".totalQtyOrderConsignmentReturn").val()))>0)?Number(replaceNum($(this).closest("tr").find(".totalQtyOrderConsignmentReturn").val())):0;
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
    <table id="tblConsignmentReturn" class="table" cellspacing="0" style="padding: 0px; width:100%">
        <?php
        $index = 0;
        $dateNow   = date("Y-m-d");
        $dateOrder = $consignmentReturn['ConsignmentReturn']['date'];
        // List Location
        $locCon = '';
        if((strtotime($dateOrder) < strtotime($dateNow)) && !empty($consignmentReturnDetails)){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableTmp = "consignment_return_tmp_inventory_".$user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT,
                            `date` date DEFAULT NULL,
                            `product_id` int(11) DEFAULT NULL,
                            `location_group_id` int(11) DEFAULT NULL,
                            `lots_number` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
                            `expired_date` DATE NOT NULL,
                            `total_qty` DECIMAL(15,3) NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            KEY `product_id` (`product_id`),
                            KEY `location_group_id` (`location_group_id`),
                            KEY `date` (`date`)
                          ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            $sqlStock   = mysql_query("SELECT SUM(qty), location_group_id, lots_number, date_expired FROM inventories WHERE inventories.date <= '".$dateOrder."' AND inventories.location_group_id = {$consignmentReturn['ConsignmentReturn']['location_group_id']} AND inventories.product_id IN (SELECT product_id FROM consignment_return_details WHERE consignment_return_id = ".$consignmentReturn['ConsignmentReturn']['id'].") GROUP BY product_id, location_group_id, lots_number, date_expired");
            while($dataTotal = mysql_fetch_array($sqlStock)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    location_group_id,
                                    lots_number,
                                    expired_date,
                                    total_qty
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    " . $dataTotal['location_group_id'] . ",
                                    '" . $dataTotal['lots_number'] . "',
                                    '" . $dataTotal['date_expired'] . "',
                                    " . $dataTotal['qty'] . "
                                )") or die(mysql_error());
            }
        }
        if(!empty($consignmentReturnDetails)){
            foreach($consignmentReturnDetails AS $consignmentReturnDetail){
                $sqlInv = mysql_query("SELECT SUM(IFNULL(total_qty,0) - IFNULL(total_order,0)) AS total_qty FROM {$consignmentReturn['ConsignmentReturn']['location_group_id']}_group_totals WHERE product_id ={$consignmentReturnDetail['Product']['id']} AND lots_number = '{$consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']}' AND expired_date = '{$consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']}' AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$consignmentReturn['ConsignmentReturn']['location_group_id']." GROUP BY id) GROUP BY product_id");
                $rowInv = mysql_fetch_array($sqlInv);
                $sqlInvOrder = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = ".$consignmentReturnDetail['Product']['id']." AND sor.lots_number = '{$consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']}' AND sor.expired_date = '{$consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']}' AND sor.consignment_return_id = ".$consignmentReturn['ConsignmentReturn']['id']." AND sor.location_group_id = ".$consignmentReturn['ConsignmentReturn']['location_group_id']." GROUP BY sor.product_id");
                $rowInvOrder = mysql_fetch_array($sqlInvOrder);
                $totalInventory = ($rowInv['total_qty'] + $rowInvOrder['total_order']);
                if((strtotime($dateOrder) < strtotime($dateNow)) && !empty($consignmentReturnDetails)){
                    // Get Total Qty Pass
                    $sqlTotalPass   = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE product_id = ".$consignmentReturnDetail['Product']['id']." AND lots_number = '{$consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']}' AND expired_date = '{$consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']}' AND date = '".$dateOrder."' AND location_group_id =".$consignmentReturn['ConsignmentReturn']['location_group_id']);
                    $rowTotalPass   = mysql_fetch_array($sqlTotalPass);
                    /** F-ID: 1100
                    * Compare Total Qty in Pass and Current Date (rowTotalPass = Total Qty In Pass, totalInventory = Total Qty in Current)
                    * IF PASS < CURRENT TotalQty = PASS
                    * ELSE PASS >= CURRENT TotalQty = CURRENT
                    */
                   if($rowTotalPass['total_qty'] + $rowInvOrder['total_order'] < $totalInventory){
                       $totalInventory = $rowTotalPass['total_qty'];
                   }
                }
                $totalQtyConsignmentReturn = $totalInventory;
                // Check Name With Customer
                $productName = $consignmentReturnDetail['Product']['name'];
        ?>
        <tr class="tblConsignmentReturnList">
            <td class="first" style="width:5%; text-align: center; padding: 0px;"><?php echo ++$index; ?></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblUPC" value="<?php echo $consignmentReturnDetail['Product']['barcode']; ?>" /></td>
            <td style="width:10%; text-align: left; padding: 5px;"><input type="text" readonly="" class="lblSKU" value="<?php echo $consignmentReturnDetail['Product']['code']; ?>" /></td>
            <td style="width:23%; text-align: left; padding: 5px;">
                <div class="inputContainer" style="width:100%; padding: 0px; margin: 0px;">
                    <input type="hidden" class="totalQtyConsignmentReturn" value="<?php echo $totalQtyConsignmentReturn; ?>" />
                    <input type="hidden" class="totalQtyOrderConsignmentReturn" value="<?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['qty'] * $consignmentReturnDetail['ConsignmentReturnDetail']['conversion']; ?>" />
                    <input type="hidden" name="product_id[]" value="<?php echo $consignmentReturnDetail['Product']['id']; ?>" class="product_id" />
                    <input type="hidden" name="conversion[]" class="conversion" value="<?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['conversion']; ?>" />
                    <input type="hidden" name="small_uom_val[]" class="small_uom_val" value="<?php echo $consignmentReturnDetail['Product']['small_val_uom']; ?>" />
                    <input type="hidden" name="note[]" id="note" class="note" value="<?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['note']; ?>" />
                    <input type="text" id="product_<?php echo $index; ?>" name="product[]" value="<?php echo str_replace('"', '&quot;', $productName); ?>" class="product validate[required]" style="width: 75%;" />
                    <img alt="Note" src="<?php echo $this->webroot . 'img/button/note.png'; ?>" class="noteAddConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Note')" />
                </div>
            </td>
            <td style="width:8%; text-align: center;padding: 0px;">
                <div class="inputContainer" style="width:100%">
                    <input type="text" id="qty_<?php echo $index; ?>" name="qty[]" value="<?php echo number_format($consignmentReturnDetail['ConsignmentReturnDetail']['qty'], 0); ?>" style="width:60%;" class="floatQty qty" />
                </div>
            </td>
            <td style="width:15%; padding: 0px; text-align: center">
                <div class="inputContainer" style="width:100%">
                    <select id="qty_uom_id_<?php echo $index; ?>" style="width:80%; height: 20px;" name="qty_uom_id[]" class="qty_uom_id validate[required]" >
                        <?php
                        $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$consignmentReturnDetail['Product']['price_uom_id']."
                                            UNION
                                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$consignmentReturnDetail['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$consignmentReturnDetail['Product']['price_uom_id'].")
                                            ORDER BY conversion ASC");
                        $i = 1;
                        $length = mysql_num_rows($query);
                        while($data=mysql_fetch_array($query)){
                            $selected = "";
                            $priceLbl = "";
                            $costLbl  = "";
                            if($data['id'] == $consignmentReturnDetail['ConsignmentReturnDetail']['qty_uom_id']){   
                                $selected = ' selected="selected" ';
                            }
                            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$consignmentReturnDetail['Product']['id']." AND product_prices.uom_id =".$data['id']);
                            if(mysql_num_rows($sqlPrice)){
                                $price = 0;
                                while($rowPrice = mysql_fetch_array($sqlPrice)){
                                    $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                                    if($rowPrice['set_type'] == 1){
                                        $price = $rowPrice['amount'];
                                    }else if($rowPrice['set_type'] == 2){
                                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                                        $price = $unitCost + $percent;
                                    }else if($rowPrice['set_type'] == 3){
                                        $price = $unitCost + $rowPrice['add_on'];
                                    }
                                    $priceLbl .= 'price-uom-'.$rowPrice['price_type_id'].'="'.$price.'" ';
                                    $costLbl  .= 'cost-uom-'.$rowPrice['price_type_id'].'="'.$unitCost.'" ';
                                }
                            }else{
                                $priceLbl .= 'price-uom-1="0" price-uom-2="0"';
                                $costLbl  .= 'cost-uom-1="0" cost-uom-2="0"';
                            }
                        ?>
                        <option <?php echo $costLbl; ?> <?php echo $priceLbl; ?> <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $consignmentReturnDetail['Product']['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                        <?php 
                        $i++;
                        } 
                        ?>
                    </select>
                </div>
            </td>
            <td style="width: 11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%">
                    <?php
                    $lotsNumber = '';
                    if($consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != '0' && $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'] != ''){
                        $lotsNumber = $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number'];
                    }
                    echo $lotsNumber;
                    ?>
                    <input type="hidden" name="lots_number[]" value="<?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['lots_number']; ?>" />
                </div>
            </td>
            <td style="width:11%; padding: 0px; text-align: center;">
                <div class="inputContainer" style="width:100%;">
                    <?php
                    $expriryDate = '';
                    if($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '' && $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date'] != '0000-00-00'){
                        $expriryDate = dateShort($consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']);
                    }
                    echo $expriryDate;
                    ?>
                    <input type="hidden" name="expired_date[]" value="<?php echo $consignmentReturnDetail['ConsignmentReturnDetail']['expired_date']; ?>" />
                </div>
            </td>
            <td style="width:7%; white-space: nowrap;">
                <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                &nbsp; <img alt="Up" src="<?php echo $this->webroot . 'img/button/move_up.png'; ?>" class="btnMoveUpConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Up')" />
                &nbsp; <img alt="Down" src="<?php echo $this->webroot . 'img/button/move_down.png'; ?>" class="btnMoveDownConsignmentReturn" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Down')" />
            </td>
        </tr>
        <?php
            }
            if(strtotime($dateOrder) < strtotime($dateNow)){
                // DROP Tmp Sale Table
                mysql_query("DROP TABLE `".$tableTmp."`;");
            }
        }
        ?>
    </table>
</div>