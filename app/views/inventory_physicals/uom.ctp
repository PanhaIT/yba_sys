<script type="text/javascript">
    var tblQtyInventoryPhysical = $("#cloneInventoryPhysical");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#cloneInventoryPhysical").remove();
        addNewQtyInventoryPhysical();
    });
    function addNewQtyInventoryPhysical(){
        var index;
        var tr = tblQtyInventoryPhysical.clone(true);
        if($(".cloneInventoryPhysical:last").find("input[name='data[qty][]']").attr("id") == undefined){
            index = 1;
        }else{
            index = parseInt($(".cloneInventoryPhysical:last").find("input[name='data[qty][]']").attr("id").split("_")[1]) + 1;
        }
        tr.removeAttr("style").removeAttr("id");
        tr.find("input[name='data[qty][]']").attr("id", "qtyInventoryPhysical_"+index).val(0);
        tr.find("input[name='data[qty_uom_id][]']").attr("id", "uomIdInventoryPhysical_"+index);
        tr.find(".btnAddNewInventoryPhysical").show();
        if(index == 1){
            tr.find(".btnRemoveNewInventoryPhysical").hide();
        }else{
            tr.find(".btnRemoveNewInventoryPhysical").show();
        }
        $("#bodyCloneInventoryPhysical").append(tr);
        evenKeyPup();
    }
    
    function evenKeyPup(){
        loadAutoCompleteOff();
        $(".btnAddNewInventoryPhysical, .btnRemoveNewInventoryPhysical, .floatQtyInventoryPhysicalUom, .qty_uom_cycle").unbind("click").unbind("change").unbind("keyup").unbind("blur").unbind("focus");
        $(".floatQtyInventoryPhysicalUom").autoNumeric({mDec: 3, aSep: '', aNeg: '-'});
        $(".floatQtyInventoryPhysicalUom").blur(function(){
            var val = $(this).val();
            if(val == ""){
                $(this).val(0);
            }
        });
        $(".floatQtyInventoryPhysicalUom").focus(function(){
            var val = $(this).val();
            if(val == 0){
                $(this).val("");
            }
        });
        
        $(".qty_uom_cycle").change(function(){
            var conversion = $(this).find("option:selected").attr('conversion');
            $(this).closest("tr").find(".conversion_cycle").val(conversion);
        });
        
        $(".btnAddNewInventoryPhysical").click(function(){
            addNewQtyInventoryPhysical();
        });
        $(".btnRemoveNewInventoryPhysical").click(function(){
            var currentTr = $(this).closest("tr");
            removeQtyInventoryPhysical(currentTr);
        });
    }
    
    function removeQtyInventoryPhysical(currentTr){
        currentTr.remove();
        var tblLength = $(".cloneInventoryPhysical").length;
        if(tblLength == 0 || tblLength == undefined){
            addNewQtyInventoryPhysical();
        }
    }
</script>
<fieldset>
    <legend>Total Qty</legend>
    <form>
        <?php
        include("includes/function.php");
        $qry = mysql_query("SELECT price_uom_id, small_val_uom FROM products WHERE id=" . $_POST['id']);
        $row = mysql_fetch_array($qry);
        $smallUom = 1;
        $smallUomLabel = "";
        $query = mysql_query("SELECT value, (SELECT abbr FROM uoms WHERE id = uom_conversions.to_uom_id) as abbr FROM uom_conversions WHERE from_uom_id = " . $row['price_uom_id'] . " AND is_small_uom = 1 AND is_active = 1");
        while (@$d = mysql_fetch_array($query)) {
            $smallUom = $d['value'];
            $smallUomLabel = $d['abbr'];
        }
        ?>
        <input type="hidden" value="<?php echo $row['small_val_uom']; ?>" name="conversion[]" />
        <table id="bodyCloneInventoryPhysical">
            <tr id="cloneInventoryPhysical" class="cloneInventoryPhysical">
                <td>
                    <input type="text" class="inputUom floatQtyInventoryPhysicalUom" style="width: 200px;"  />
                </td>
                <td>
                    <select name="data[qty_uom_id][]" class="qty_uom_cycle">
                        <?php
                        $qUom = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $row['price_uom_id'] . "
                                            UNION
                                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $row['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $row['price_uom_id'] . ")
                                            ORDER BY conversion ASC");
                        $i = 1;
                        $length = mysql_num_rows($qUom);
                        while ($data = mysql_fetch_array($qUom)) {
                            if ($i == $length) {
                                $conversion = 1;
                            } else if ($i == 1) {
                                $conversion = $data['conversion'] * $row['small_val_uom'];
                            } else {
                                $conversion = $row['small_val_uom'] / $data['conversion'];
                            }
                            ?>
                            <option data-sm="<?php if ($length == $i) { ?>1<?php } else { ?>0<?php } ?>" data-item="<?php
                        if ($data['id'] == $row['price_uom_id']) {
                            echo "first";
                        } else {
                            echo "other";
                        }
                            ?>" <?php if ($data['id'] == $row['price_uom_id']) { ?> selected="selected" <?php } ?> value="<?php echo $data['id']; ?>" conversion="<?php echo $conversion; ?>"><?php echo $data['abbr']; ?></option>
                                    <?php
                                    $i++;
                                }
                                ?>
                    </select>

                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveNewInventoryPhysical" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddNewInventoryPhysical" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                </td>
            </tr>
        </table>
    </form>
</fieldset>