<?php
$query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomId."
                    UNION
                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId.")
                    ORDER BY conversion ASC");
if(mysql_num_rows($query) && mysql_num_rows($query) > 1){
?>
<fieldset>
    <legend><?php __(TABLE_CODE." of ".TABLE_UOM); ?></legend>
    <table style="width:90%">
        <?php
        $index = 1;
        while($data=mysql_fetch_array($query)){
            if($data['id'] != $uomId){
        ?>
        <tr>
            <td style="width: 15%;"><label for="skuUomPro_<?php echo $data['id']; ?>"><?php echo TABLE_CODE; ?></label> :</td>
            <td>
                <input type="hidden" value="<?php echo $data['id']; ?>" name="data[sku_uom][]" />
                <input type="text" sku-uom="0" name="data[sku_uom_value][]" id="skuUomPro_<?php echo $data['id']; ?>" class="skuUomPro" style="width: 60%;" /> of <?php echo $data['name']; ?>
                <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadSkuUomPro" style="display:none;" />
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" class="availableSkuUomPro" style="display:none;" /> 
                <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableSkuUomPro" style="display:none;" />
                <span class="red lblAvailable" style="display:none;">SKU of this uom have existed!</span>
            </td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
</fieldset>
<?php
}
?>