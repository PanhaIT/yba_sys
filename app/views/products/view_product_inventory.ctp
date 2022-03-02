<?php 
    // Function
    include('includes/function.php');
?>
<script type="text/javascript">  
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<table cellpadding="5" class="table" style="width: 510px;">
    <thead>
        <tr>
            <th class="first" style="width: 300px !important;"><?php echo TABLE_LOCATION; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_QTY_IN_STOCK; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $warehouse = '';
        $sqlWare = mysql_query("SELECT products.small_val_uom, uoms.abbr AS mainUom, products.small_val_uom, products.price_uom_id, location_groups.name AS ware_name, locations.name AS loc_name, SUM(total_qty) AS total_qty FROM product_inventories INNER JOIN products ON products.id = product_inventories.product_id INNER JOIN uoms ON uoms.id = products.price_uom_id INNER JOIN location_groups ON location_groups.id = product_inventories.location_group_id INNER JOIN locations ON locations.id = product_inventories.location_id WHERE product_inventories.location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].") AND product_id = ".$productId." GROUP BY product_inventories.location_group_id, product_inventories.location_id");
        while($rowWare = mysql_fetch_array($sqlWare)){
            if($warehouse != $rowWare['ware_name']){
        ?>
        <tr>
            <td colspan="2" class="first" style="font-weight: bold; font-size: 14px;"><?php echo $rowWare['ware_name']; ?></td>
        </tr>
        <?php
                $warehouse = $rowWare['ware_name'];
            }
        ?>
        <tr>
            <td class="first"><?php echo $rowWare['loc_name']; ?></td>
            <td><?php echo displayQtyByUoM($rowWare['total_qty'], $rowWare['price_uom_id'], $rowWare['small_val_uom'], $rowWare['mainUom']); ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>