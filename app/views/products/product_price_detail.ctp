<?php
$this->element('check_access');
// Authentication
$allowViewCost = checkAccess($user['User']['id'], $this->params['controller'], 'viewCost');

// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);

// Check Shop Share
$isEcommerce = 0;
$sqlShare = mysql_query("SELECT is_share FROM e_store_shares WHERE company_id = ".$products['Product']['company_id'] );
if(mysql_num_rows($sqlShare)){
    $rowShare = mysql_fetch_array($sqlShare);
    $isEcommerce = $rowShare['is_share'];
}

if($branchId == 0){
    $sqlC = mysql_query("SELECT id FROM product_prices WHERE branch_id = 0 LIMIT 1;");
    if(!mysql_num_rows($sqlC)){
        $sqlB = mysql_query("SELECT id FROM branches WHERE is_active = 1 LIMIT 1;");
        $rowB = mysql_fetch_array($sqlB);
        $branchId = $rowB['id'];
    }
}
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        $("#ProductPrice").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-layout-center"
        });
        $(".float").autoNumeric({aNeg: '-', mDec: 2});
        $(".priceDetail").each(function(){
            $(this).find("td.setPriceUOM").each(function(){
                var obj = $(this);
                changeSetType(obj);
            });
        });
        $(".setTypeSelect").change(function(){
            $(this).closest("tr").find("td.setPriceUOM").each(function(){
                var obj = $(this);
                changeSetType(obj);
            });
        });
        $(".amount, .percent, .add_on, .amount_before, #price6Qty, #price12Qty").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".amount, .percent, .add_on, .amount_before").blur(function(){
            if($(this).val() == ''){
                $(this).val('0.00');
            } 
            if($(this).attr('class') == 'amount float'){
                var sellPrice   = replaceNum($(this).val());
                var beforePrice = replaceNum($(this).closest("tr").find(".amount_before").val());
                if(beforePrice > 0){
                    if(sellPrice < beforePrice){
                        $(this).val(beforePrice);
                    }
                }
            }
        });
        
        $("#price6Qty, #price12Qty").blur(function(){
            if($(this).val() == ''){
                $(this).val('0.00');
            } 
        });
        
        $(".amount, .percent, .add_on").keyup(function(){
            var obj = $(this).closest("td");
            changeSetType(obj);
        });
    });
    
    function changeSetType(obj){
        var setType  = obj.closest("tr").find(".setTypeSelect").val();
        var unitCost = replaceNum(obj.find(".unit_cost").val());
        var unitPrice = 0;
        if(setType == 1){
            unitPrice = replaceNum(obj.find(".amount").val());
            obj.find(".amount").show();
            obj.find(".percent").hide();
            obj.find(".add_on").hide();
            obj.find(".percent").val('0.00');
            obj.find(".add_on").val('0.00');
            obj.find(".symbol").html("<?php echo $symbol; ?>");
        }else if(setType == 2){
            var pricePercent = converDicemalJS(converDicemalJS(unitCost * replaceNum(obj.find(".percent").val())) / 100);
            unitPrice = unitCost + pricePercent;
            obj.find(".amount").hide();
            obj.find(".percent").show();
            obj.find(".add_on").hide();
            obj.find(".amount").val('0.00');
            obj.find(".add_on").val('0.00');
            obj.find(".symbol").html("%");
        }else{
            unitPrice = converDicemalJS(unitCost + replaceNum(obj.find(".add_on").val()));
            obj.find(".amount").hide();
            obj.find(".percent").hide();
            obj.find(".add_on").show();
            obj.find(".amount").val('0.00');
            obj.find(".percent").val('0.00');
            obj.find(".symbol").html("<?php echo $symbol; ?>");
        }
        obj.find(".unit_price").html(replaceNum(unitPrice).toFixed(2));
    }
</script>
<fieldset>
    <legend><?php echo MENU_LAST_SET_PRICE_HISTORY; ?></legend>
    <table class="table" cellspacing="0">            
    <thead>                
        <tr>
            <th class="first" style="width: 4%;"><?php echo TABLE_NO; ?></th>
            <th style="width: 15%;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 7%;"><?php echo TABLE_UOM; ?></th>
            <?php
            if($allowViewCost){
            ?>
            <th style="width: 9%;"><?php echo TABLE_UNIT_COST; ?> (<?php echo $symbol; ?>)</th>
            <?php
            }
            ?>
            <th style="width: 9%;"><?php echo TABLE_AS_AMOUNT; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 9%;"><?php echo TABLE_AS_PERCENT; ?> (%)</th>
            <th style="width: 9%;"><?php echo TABLE_AS_ADD_ON; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 9%;"><?php echo TABLE_PRICE; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 13%;"><?php echo TABLE_CREATED; ?></th>
            <th><?php echo TABLE_CREATED_BY; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlHistory = mysql_query("SELECT price_types.name, history.unit_cost AS unit_cost, history.old_set_type AS set_type, history.uom_id, history.old_amount AS amount, history.old_percent AS percent, history.old_add_on AS add_on, history.created, CONCAT(users.first_name,' ',users.last_name) AS created_by, uoms.abbr FROM product_price_histories AS history INNER JOIN uoms ON uoms.id = history.uom_id INNER JOIN price_types ON price_types.id = history.price_type_id INNER JOIN users ON users.id = history.created_by WHERE history.product_id = ".$products['Product']['id']." AND history.branch_id = ".$branchId." ORDER BY history.id, history.created, price_types.id ASC LIMIT 20");
        if(mysql_num_rows($sqlHistory)){
            $index = 0;
            while($rowHis = mysql_fetch_array($sqlHistory)){
//                $sqlUom = mysql_query("SELECT value FROM uom_conversions WHERE from_uom_id = {$products['Product']['price_uom_id']} AND to_uom_id = {$rowHis['uom_id']}");
//                if(mysql_num_rows($sqlUom)){
//                    $rowUom = mysql_fetch_array($sqlUom);
//                    $uomVal = $rowUom[0];
//                }else{
//                    $uomVal = 1;
//                }
                $type = $rowHis['set_type'];
                $unitPrice = 0;
                $unitCost  = $rowHis['unit_cost'];
                if($type == 1){
                    $unitPrice = $rowHis['amount'];
                }else if($type == 2){
                    $percent = ($unitCost * $rowHis['percent']) / 100;
                    $unitPrice = $unitCost + $percent;
                }else if($type == 3){
                    $unitPrice = $unitCost + $rowHis['add_on'];
                }
        ?>
        <tr>
            <td class="first"><?php echo ++$index; ?></td>
            <td><?php echo $rowHis['name']; ?></td>
            <td><?php echo $rowHis['abbr']; ?></td>
            <?php
            if($allowViewCost){
            ?>
            <td><?php echo number_format($unitCost, $rowOption[0]); ?></td>
            <?php
            }
            ?>
            <td><?php echo number_format($rowHis['amount'], 2); ?></td>
            <td><?php echo number_format($rowHis['percent'], 2); ?></td>
            <td><?php echo number_format($rowHis['add_on'], 2); ?></td>
            <td style="font-weight: bold;"><?php echo number_format($unitPrice, 2); ?></td>
            <td>
                <?php 
                $date = explode(" ",$rowHis['created']);
                echo dateShort($date[0])." ".$date[1]; 
                ?>
            </td>
            <td>
                <?php echo $rowHis['created_by']; ?>
            </td>
        </tr>
        <?php
            }
        }else{
        ?>
        <tr>
            <td class="first" colspan="10"><?php echo TABLE_NO_RECORD; ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
</fieldset>
<br />
<table class="table" cellspacing="0">            
    <thead>                
        <tr>
            <th class="first" style="width: 10%;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 23%;"><?php echo TABLE_PRODUCT; ?></th>
            <?php
            if($allowViewCost){
            ?>
            <th style="width: 8%;"><?php echo TABLE_UNIT_COST; ?></th>
            <?php
            }
            ?>
            <th style="width: 12%;"><?php echo TABLE_LAST_SELLING_PRICE; ?></th>
            <?php
            $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $products['Product']['price_uom_id'] . "
                UNION
                SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . ")
                ORDER BY conversion ASC");
            $i = 0;
            $mainUomLabel = '';
            while ($data = mysql_fetch_array($query)) {
                $mainUomLabel = $data['abbr'];
            ?>
                <th style="text-align: center;">
                    <?php echo $this->Form->text('uom_id', array('value' => $data['id'], 'name' => 'data[uom_id][]', 'type' => 'hidden', 'label' => false)); ?>
                    <?php echo $data['abbr']; ?>
                </th>
            <?php
                $i++;
            }
            ?>
            <th style="width: 12%; text-align: center;"><?php echo TABLE_SET_AS; ?></th>
        </tr>
        <?php
        // Price Type System
        $k = 0;
        $condition ='';
        if($vendorId!=''){
            $condition .= " AND vendor_id='".$vendorId."' ";
        }
        if($colorId!=''){
            $condition .= " AND color_id='".$colorId."' ";
        }
        if($sizeId!=''){
            $condition .= " AND size_id='".$sizeId."' ";
        }
        $sqlPrice = mysql_query("SELECT id, name FROM price_types WHERE is_active = 1 AND id IN (SELECT price_type_id FROM price_type_companies WHERE company_id = ".$products['Product']['company_id'].") ORDER BY ordering ASC");
        while($rowPrice = mysql_fetch_array($sqlPrice)){
            $lastSelling = 0;
            $sqlLastPrice = mysql_query("SELECT unit_price, conversion FROM sales_order_details AS sd INNER JOIN sales_orders AS so ON so.id = sd.sales_order_id WHERE so.status > 0 AND so.company_id = ".$products['Product']['company_id']." AND so.branch_id = ".$branchId." AND sd.product_id = ".$products['Product']['id']." ORDER BY so.created DESC LIMIT 1");
            if(mysql_num_rows($sqlLastPrice)){
                $rowLastPrice = mysql_fetch_array($sqlLastPrice);
                $lastSelling = $rowLastPrice['unit_price'] / ($products['Product']['small_val_uom'] / $rowLastPrice['conversion']);
            }
            // E-Commerce
            if($rowPrice['id'] == 1){
                if($branch['Branch']['is_head'] == 1 && $isEcommerce == 1){
        ?>
        <tr class="priceDetail">
            <td class="first">
                <?php
                echo $rowPrice['name'];
                ?>
                <?php echo $this->Form->text('type_id', array('value' => $rowPrice['id'], 'name' => 'data[type_id][]', 'type' => 'hidden', 'label' => false)); ?>
            </td>
            <td>
                <?php echo $products['Product']['name']; ?>
                <?php echo $this->Form->text('product_id', array('class' => 'product_id', 'value' => $products['Product']['id'], 'name' => 'data[ProductPrice][product_id]', 'type' => 'hidden', 'label' => false)); ?>
            </td>
            <?php
            if($allowViewCost){
            ?>
            <td>
                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $symbol; ?></span>
                <?php
                echo number_format($products['Product']['unit_cost'], $rowOption[0]);
                ?>
            </td>
            <?php
            }
            ?>
            <td>
                <span style="float: left; width: 12px; font-size: 11px;"><?php echo $symbol; ?></span>
                <?php
                echo number_format($lastSelling, 2);
                ?>
            </td>
            <?php
            $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $products['Product']['price_uom_id'] . "
                                  UNION
                                 SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . ")
                                 ORDER BY conversion ASC");
            $j = 0;
            while ($data = mysql_fetch_array($query)) {
                $unitCost   = ($products['Product']['unit_cost'] / $data['conversion']);
                $oldAmt     = 0;
                $oldAmtBf   = 0;
                $sql = "SELECT amount, amount_before, set_type FROM product_prices WHERE product_id=" . $products['Product']['id'] . " AND branch_id = ".$branchId." AND price_type_id=" . $rowPrice['id'] . " AND uom_id=" . $data['id'].$condition;
                $qry = mysql_query($sql);
                if(mysql_num_rows($qry)){
                    $row = mysql_fetch_array($qry);
                    $oldAmt     = $row['amount'];
                    $oldAmtBf   = $row['amount_before'];
                }
                $unitPrice = $oldAmt;
            ?>
                <td align="center" class="setPriceUOM">
                    <input type="hidden" class="unit_cost" name="data[old_unit_cost][]" value="<?php echo $unitCost; ?>" />
                    <input type="hidden" value="<?php echo $oldAmtBf; ?>" name="data[old_amount_before][]" />
                    <input type="hidden" value="<?php echo $oldAmt; ?>" name="data[old_amount][]" />
                    <input type="hidden" value="0" name="data[old_percent][]" />
                    <input type="hidden" value="0" name="data[old_add_on][]" />
                    <input type="hidden" class="percent float" value="0" name="data[percent][]" id="percent_<?php echo $k; ?>_<?php echo $j; ?>" />
                    <input type="hidden" class="add_on float" value="0" name="data[add_on][]" id="add_on_<?php echo $k; ?>_<?php echo $j; ?>" />
                    <table cellpadding="0" cellspacing="0" style="width: 100%;" border="0">
                        <tr>
                            <td style="width: 50%; border: none;"><?php echo TABLE_BRFORE_PRICE; ?> (<?php echo $symbol; ?>)</td>
                            <td style="width: 50%; border: none;"><?php echo TABLE_SELL_PRICE; ?> (<?php echo $symbol; ?>)</td>
                        </tr>
                        <tr>
                            <td style="border: none;">
                                <input type="text" class="amount_before float" style="font-weight: bold; width: 90%;" value="<?php echo number_format($oldAmtBf, 2); ?>" name="data[amount_before][]" id="amount_before_<?php echo $k; ?>_<?php echo $j; ?>" />
                            </td>
                            <td style="border: none;">
                                <input type="text" class="amount float" style="font-weight: bold; width: 90%;" value="<?php echo number_format($oldAmt, 2); ?>" name="data[amount][]" id="amount_<?php echo $k; ?>_<?php echo $j; ?>" />
                                <input type="hidden" value="1" name="data[old_set_type][]" />
                                <input type="hidden" value="1" class="setTypeSelect" name="data[set_type][]" />
                            </td>
                        </tr>
                    </table>
                </td>
            <?php
                $j++;
            }
            ?>
                <td>
                    <?php echo TABLE_AS_AMOUNT; ?>
                </td>
        </tr>
        <?php      
                }
            // System Price
            } else {
        ?>
        <tr class="priceDetail">
                <td class="first">
                    <?php
                    echo $rowPrice['name'];
                    ?>
                    <?php echo $this->Form->text('type_id', array('value' => $rowPrice['id'], 'name' => 'data[type_id][]', 'type' => 'hidden', 'label' => false)); ?>
                </td>
                <td>
                    <?php echo $products['Product']['name']; ?>
                    <?php echo $this->Form->text('product_id', array('class' => 'product_id', 'value' => $products['Product']['id'], 'name' => 'data[ProductPrice][product_id]', 'type' => 'hidden', 'label' => false)); ?>
                </td>
                <?php
                if($allowViewCost){
                ?>
                <td>
                    <span style="float: left; width: 12px; font-size: 11px;"><?php echo $symbol; ?></span>
                    <?php
                    echo number_format($products['Product']['unit_cost'], $rowOption[0]);
                    ?>
                </td>
                <?php
                }
                ?>
                <td>
                    <span style="float: left; width: 12px; font-size: 11px;"><?php echo $symbol; ?></span>
                    <?php
                    echo number_format($lastSelling, 2);
                    ?>
                </td>
                <?php
                $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $products['Product']['price_uom_id'] . "
                                    UNION
                                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . ")
                                    ORDER BY conversion ASC");
                $j = 0;
                while ($data  = mysql_fetch_array($query)) {
                    $unitCost = ($products['Product']['unit_cost'] / $data['conversion']);
                    $sql = "SELECT amount, percent, add_on, set_type FROM product_prices WHERE product_id=" . $products['Product']['id'] . " AND branch_id = ".$branchId." AND price_type_id=" . $rowPrice['id'] . " AND uom_id=" . $data['id'].$condition;
                    $qry = mysql_query($sql);
                    if(mysql_num_rows($qry)){
                        $row = mysql_fetch_array($qry);
                        $oldAmt     = $row['amount'];
                        $oldPercent = $row['percent'];
                        $oldAddOn   = $row['add_on'];

                        if($oldAmt > 0 || $oldPercent > 0 || $oldAddOn > 0){
                            $setType = $row['set_type'];
                        }else{
                            $setType = 0;
                        }

                    }else{
                        $oldAmt     = 0;
                        $oldPercent = 0;
                        $oldAddOn   = 0;
                        $setType    = 0;
                    }

                    if($setType == 0 || $setType == 1){
                        $unitPrice = $oldAmt;
                    } else if($setType == 2){
                        $percent   = ($unitCost * $oldPercent) / 100;
                        $unitPrice = $unitCost + $percent;
                    } else {
                        $unitPrice = $unitCost + $oldAddOn;
                    }

                ?>
                    <td align="center" class="setPriceUOM">
                        <input type="hidden" class="unit_cost" name="data[old_unit_cost][]" value="<?php echo $unitCost; ?>" />
                        <input type="hidden" value="0" name="data[old_amount_before][]" />
                        <input type="hidden" value="<?php echo $oldAmt; ?>" name="data[old_amount][]" />
                        <input type="hidden" value="<?php echo $oldPercent; ?>" name="data[old_percent][]" />
                        <input type="hidden" value="<?php echo $oldAddOn; ?>" name="data[old_add_on][]" />
                        <input type="hidden" class="amount_before float" value="0" name="data[amount_before][]" />
                        <input type="text" class="amount float" style="font-weight: bold; width: 50%; display: none;" value="<?php echo number_format($oldAmt, 2); ?>" name="data[amount][]" id="amount_<?php echo $k; ?>_<?php echo $j; ?>" />
                        <input type="text" class="percent float" style="font-weight: bold; width: 50%; display: none;" value="<?php echo number_format($oldPercent, 2); ?>" name="data[percent][]" id="percent_<?php echo $k; ?>_<?php echo $j; ?>" />
                        <input type="text" class="add_on float" style="font-weight: bold; width: 50%; display: none;" value="<?php echo number_format($oldAddOn, 2); ?>" name="data[add_on][]" id="add_on_<?php echo $k; ?>_<?php echo $j; ?>" />
                        (<span class="symbol"></span>) = <span class="unit_price"><?php echo number_format($unitPrice, 2); ?></span><?php echo $symbol; ?>
                    </td>
                <?php
                    $j++;
                }
                ?>
                    <td>
                        <?php 
                            $rowSetToPriceType = 0;
                            if($setType == 0){
                                $querySetToPriceType = mysql_query("SELECT is_set FROM price_types WHERE id = ".$rowPrice['id']."");
                                if(mysql_num_rows($querySetToPriceType) > 0){
                                    $rowSetToPriceType    = mysql_fetch_array($querySetToPriceType);
                                }else{
                                    $rowSetToPriceType[0] = 1;
                                }

                            }
                        ?>
                        <input type="hidden" value="<?php echo $setType; ?>" name="data[old_set_type][]" />
                        <select style="width: 90%;" class="setTypeSelect" name="data[set_type][]" id="set_type_<?php echo $k; ?>">
                            <option value="1" <?php if((($setType == 0)?$rowSetToPriceType[0]:$setType) == 1){ ?>selected="selected"<?php } ?>><?php echo TABLE_AS_AMOUNT; ?></option>
                            <option value="2" <?php if((($setType == 0)?$rowSetToPriceType[0]:$setType) == 2){ ?>selected="selected"<?php } ?>><?php echo TABLE_AS_PERCENT; ?></option>
                            <option value="3" <?php if((($setType == 0)?$rowSetToPriceType[0]:$setType) == 3){ ?>selected="selected"<?php } ?>><?php echo TABLE_AS_ADD_ON; ?></option>
                        </select>
                    </td>
            </tr>
        <?php
                $k++;
            }
        }
        ?>
    </thead>
</table>
<br />
<?php
if($products['Product']['small_val_uom'] == 1){
    $price6  = 0;
    $price12 = 0;
    $sqlQtyP = mysql_query("SELECT * FROM product_qty_prices WHERE product_id = ".$products['Product']['id']);
    if(mysql_num_rows($sqlQtyP)){
        while($rowQtyP = mysql_fetch_array($sqlQtyP)){
            if($rowQtyP['qty'] == 6){
                $price6 = $rowQtyP['unit_price'];
            } else if($rowQtyP['qty'] == 12){
                $price12 = $rowQtyP['unit_price'];
            }
        }
    }
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tbody>
        <tr>
            <td style="width: 5%;"><label for="price6Qty"><?php echo TABLE_PRICE; ?> :</label></td>
            <td style="width: 20%; text-align: left;">
                <input type="text" name="data[price_6]" value="<?php echo number_format($price6, 2); ?>" id="price6Qty" class="float" style="width: 120px;" /> <?php echo $symbol; ?> in 6 <?php echo $mainUomLabel; ?>
            </td>
            <td style="width: 5%;"><label for="price12Qty"><?php echo TABLE_PRICE; ?> :</label></td>
            <td style="text-align: left;">
                <input type="text" name="data[price_12]" value="<?php echo number_format($price12, 2); ?>" id="price12Qty" class="float" style="width: 120px;" /> <?php echo $symbol; ?> in 12 <?php echo $mainUomLabel; ?>
            </td>
        </tr>
    </tbody>
</table>
<?php
}
?>