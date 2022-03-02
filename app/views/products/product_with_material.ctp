<?php
$dialog = "dialog".rand();
$sql = mysql_query("SELECT * FROM product_with_materials WHERE product_id = ".$id);
?>
<script type="text/javascript">
    var tblProductMaterialEdit = $("#cloneProductMaterialEdit");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#cloneProductMaterialEdit").remove();
        <?php
        if(!@mysql_num_rows($sql)){
        ?>
        addNewProductMaterialEdit();
        <?php
        }else{
        ?>
        evenKeyProductMaterialEdit();
        <?php
        }
        ?>
    });
    function addNewProductMaterialEdit(){
        var index;
        var tr = tblProductMaterialEdit.clone(true);
        if($(".cloneProductMaterialEdit:last").find(".qtyInt").attr("id") == undefined){
            index = 1;
        }else{
            index = parseInt($(".cloneProductMaterialEdit:last").find(".qtyInt").attr("id").split("_")[1]) + 1;
        }
        tr.removeAttr("style").removeAttr("id");
        tr.find(".product_material_id_edit").val('');
        tr.find(".product_material_name_edit").attr("id", "product_material_name_edit_"+index).val('');
        tr.find(".qtyInt").attr("id", "qtyInt_"+index).val(0);
        tr.find(".product_material_uom_id_edit").attr("id", "product_material_uom_id_edit_"+index).html("<option><?php echo INPUT_SELECT; ?></option>");
        tr.find(".addProductMeterialEdit").show();
        if(index == 1){
            tr.find(".deleteProductMeterialEdit").hide();
        }else{
            tr.find(".deleteProductMeterialEdit").show();
        }
        $("#tableProductMaterialEdit").append(tr);
        evenKeyProductMaterialEdit();
    }
    
    function evenKeyProductMaterialEdit(){
        $(".addProductMeterialEdit, .deleteProductMeterialEdit, .searchProductMaterialEdit, .deleteProductMaterialNameEdit, .qtyInt, .product_material_uom_id_edit").unbind("click").unbind("change").unbind("keyup").unbind("blur").unbind("focus");
        $(".qtyInt").priceFormat({thousandsSeparator: '',allowNegative: true});
        $(".qtyInt").blur(function(){
            var val = $(this).val();
            if(val == "" || val == 0){
                $(this).val(1);
            }
        });
        $(".qtyInt").focus(function(){
            var val = $(this).val();
            if(val == 0 || val == ""){
                $(this).val(1);
            }
        });
        
        $(".searchProductMaterialEdit").click(function(){
            var obj = $(this);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/product",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#<?php echo $dialog; ?>").html(msg).dialog({
                        title: '<?php echo TABLE_PRODUCT; ?>',
                        resizable: false,
                        modal: true,
                        width: 850,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                if($("input[name='chkProductParent']:checked").val()){
                                    var id   = $("input[name='chkProductParent']:checked").val();
                                    var name = $("input[name='chkProductParent']:checked").attr('code')+" - "+$("input[name='chkProductParent']:checked").attr('abbr');
                                    var uom_id = $("input[name='chkProductParent']:checked").attr('uom_id');
                                    $.ajax({
                                        type: "GET",
                                        url: "<?php echo $this->base; ?>/uoms/getRelativeUom/"+uom_id,
                                        data: "",
                                        success: function(result){
                                            obj.closest("tr").find(".product_material_uom_id_edit").html(result);
                                            obj.closest("tr").find(".product_material_conversion_edit").val(1);
                                        }
                                    });
                                    obj.closest("tr").find(".product_material_id_edit").val(id);
                                    obj.closest("tr").find(".product_material_name_edit").val(name);
                                    obj.closest("tr").find(".qtyInt").val(1);
                                    obj.closest("tr").find(".searchProductMaterialEdit").hide();
                                    obj.closest("tr").find(".deleteProductMaterialNameEdit").show();
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        
        $(".deleteProductMaterialNameEdit").click(function(){
            var obj = $(this);
            obj.closest("tr").find(".searchProductMaterialEdit").show();
            obj.closest("tr").find(".deleteProductMaterialNameEdit").hide();
            obj.closest("tr").find(".product_material_id_edit").val('');
            obj.closest("tr").find(".product_material_name_edit").val('');
            obj.closest("tr").find(".product_material_uom_id_edit").html("<option><?php echo INPUT_SELECT; ?></option>");
        });
        
        $(".product_material_uom_id_edit").change(function(){
            var conversion = $(this).find("option:selected").attr('conversion');
            $(this).closest("tr").find(".product_material_conversion_edit").val(conversion);
        });
        
        $(".addProductMeterialEdit").click(function(){
            addNewProductMaterialEdit();
        });
        $(".deleteProductMeterialEdit").click(function(){
            var currentTr = $(this).closest("tr");
            removeProductMaterialEdit(currentTr);
        });
    }
    
    function removeProductMaterialEdit(currentTr){
        currentTr.remove();
        var tblLength = $(".cloneProductMaterialEdit").length;
        if(tblLength == 0 || tblLength == undefined){
            addNewProductMaterial();
        }
    }
</script>
<form id="productWithMaterialEdit">
<fieldset>
    <legend><?php __(TABLE_PRODUCT_MATERIAL); ?></legend>
    <table style="width:90%" id="tableProductMaterialEdit">
        <tr id="cloneProductMaterialEdit" class="cloneProductMaterialEdit">
            <td style="width: 10%;"><?php echo TABLE_PRODUCT; ?> :</td>
            <td>
                <input type="hidden" value="0" name="data[product_material_id_edit][]" class="product_material_id_edit" id="product_material_id_edit" />
                <input type="text" id="product_material_name_edit" class="product_material_name_edit validate[required]" style="width: 360px;" />
                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchProductMaterialEdit" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="deleteProductMaterialNameEdit" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
            </td>
            <td style="width: 5%;"><?php echo TABLE_QTY; ?> :</td>
            <td>
                <input type="text" name="data[product_material_qty_edit][]" id="product_material_qty_edit" class="product_material_qty_edit qtyInt validate[required]" value="0" style="width: 50px;" />
            </td>
            <td style="width: 5%;"><?php echo TABLE_UOM; ?> :</td>
            <td>
                <input type="hidden" name="data[product_material_conversion_edit][]" class="product_material_conversion_edit" value="0" />
                <select id="product_material_uom_id_edit" name="data[product_material_uom_id_edit][]" class="product_material_uom_id_edit validate[required]">
                    <option><?php echo INPUT_SELECT; ?></option>
                </select>
                <img alt="Add Product Material" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/plus.png" class="addProductMeterialEdit" style="cursor: pointer;" onmouseover="Tip('<?php echo ACTION_ADD; ?>')" />
                <img alt="Delete Product Material" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/delete.png" class="deleteProductMeterialEdit" style="display: none;" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" />
            </td>
        </tr>
        <?php
        if(@mysql_num_rows($sql)){
            $i=1;
            while($row=mysql_fetch_array($sql)){
                $s = mysql_query("SELECT CONCAT(code,' ',name) AS name, price_uom_id FROM products WHERE id = ".$row['product_material_id']);
                $r = mysql_fetch_array($s);
        ?>
        <tr class="cloneProductMaterialEdit">
            <td style="width: 10%;"><?php echo TABLE_PRODUCT; ?> :</td>
            <td>
                <input type="hidden" value="<?php echo $row['product_material_id']; ?>" name="data[product_material_id_edit][]" class="product_material_id_edit" id="product_material_id_edit_<?php echo $i; ?>" />
                <input type="text" value="<?php echo $r[0]; ?>" id="product_material_name_edit_<?php echo $i; ?>" class="product_material_name_edit validate[required]" style="width: 360px;" />
                <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px; display: none;" class="searchProductMaterialEdit" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="deleteProductMaterialNameEdit" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
            </td>
            <td style="width: 5%;"><?php echo TABLE_QTY; ?> :</td>
            <td>
                <input type="text" name="data[product_material_qty_edit][]" value="<?php echo $row['qty']; ?>" id="qtyInt_<?php echo $i; ?>" class="qtyInt validate[required]" value="0" style="width: 50px;" />
            </td>
            <td style="width: 5%;"><?php echo TABLE_UOM; ?> :</td>
            <td>
                <input type="hidden" name="data[product_material_conversion_edit][]" class="product_material_conversion_edit" value="<?php echo $row['conversion']; ?>" />
                <select id="product_material_uom_id_edit_<?php echo $i; ?>" name="data[product_material_uom_id_edit][]" class="product_material_uom_id_edit validate[required]">
                    <?php
                    $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$r['price_uom_id']."
                                        UNION
                                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$r['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$r['price_uom_id'].")
                                        ORDER BY conversion ASC");
                    $j = 1;
                    $length = mysql_num_rows($query);
                    while($data=mysql_fetch_array($query)){
                        $selected = "";
                        if($data['id'] == $row['qty_uom_id']){   
                            $selected = ' selected="selected" ';
                        }
                    ?>
                    <option <?php echo $selected; ?>data-sm="<?php if($length == $j){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $r['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                    <?php 
                    $j++;
                    } ?>
                </select>
                <?php
                if($i == 1){
                    $displayAdd = "";
                    $displayDelete = "display: none;";
                }else{
                    $displayAdd = "";
                    $displayDelete = "";
                }
                ?>
                <img alt="Add Product Material" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/plus.png" class="addProductMeterialEdit" style="cursor: pointer; <?php echo $displayAdd; ?>" onmouseover="Tip('<?php echo ACTION_ADD; ?>')" />
                <img alt="Delete Product Material" align="absmiddle" src="<?php echo $this->webroot; ?>img/button/delete.png" class="deleteProductMeterialEdit" style="cursor: pointer; <?php echo $displayDelete; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" />
            </td>
        </tr>
        <?php
                $i++;
            }
        }
        ?>
    </table>
</fieldset>
</form>
<div id="<?php echo $dialog; ?>"></div>

