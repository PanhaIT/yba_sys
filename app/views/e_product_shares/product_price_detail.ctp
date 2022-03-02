<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<table class="table" cellspacing="0">            
    <thead>                
        <tr>
            <th class="first" style="width: 20%;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 40%;"><?php echo TABLE_BRFORE_PRICE; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 40%;"><?php echo TABLE_SELL_PRICE; ?> (<?php echo $symbol; ?>)</th>
        </tr>
        <?php
        $query = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=" . $products['Product']['price_uom_id'] . "
                              UNION
                              SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . " AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=" . $products['Product']['price_uom_id'] . ")
                              ORDER BY conversion ASC");
        while($rowUom = mysql_fetch_array($query)){
            $beforePrice = 0;
            $currentPrice = 0;
            $sqlUomPrice = mysql_query("SELECT before_price, sell_price FROM e_product_prices WHERE product_id = ".$products['Product']['id']." AND uom_id = ".$rowUom['id']." LIMIT 1");
            if(mysql_num_rows($sqlUomPrice)){
                $rowUomPrice = mysql_fetch_array($sqlUomPrice);
                $beforePrice = $rowUomPrice['before_price'];
                $currentPrice = $rowUomPrice['sell_price'];
            }
        ?>
        <tr>
            <td class="first">
                <input type="hidden" value="<?php echo $rowUom['id']; ?>" name="uom[]" />
                <?php echo $rowUom['abbr']; ?>
            </td>
            <td style="font-weight: bold;"><?php echo number_format($beforePrice, 2); ?></td>
            <td style="font-weight: bold;"><?php echo number_format($currentPrice, 2); ?></td>
        </tr>
        <?php
        }
        ?>
    </thead>
</table>
<br />
<fieldset>
    <legend><?php echo MENU_LAST_SET_PRICE_HISTORY; ?></legend>
    <table class="table" cellspacing="0">            
    <thead>                
        <tr>
            <th class="first" style="width: 5%;"><?php echo TABLE_NO; ?></th>
            <th style="width: 15%;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 10%;"><?php echo TABLE_BRFORE_PRICE; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 10%;"><?php echo TABLE_SELL_PRICE; ?> (<?php echo $symbol; ?>)</th>
            <th style="width: 20%;"><?php echo TABLE_CREATED; ?></th>
            <th><?php echo TABLE_CREATED_BY; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlHistory = mysql_query("SELECT uoms.name, history.before_price, history.sell_price, history.created, CONCAT(users.first_name,' ',users.last_name) AS created_by FROM e_product_price_histories AS history INNER JOIN uoms ON uoms.id = history.uom_id INNER JOIN users ON users.id = history.created_by WHERE history.product_id = ".$products['Product']['id']." ORDER BY history.id ASC LIMIT 10");
        if(mysql_num_rows($sqlHistory)){
            $index = 0;
            while($rowHis = mysql_fetch_array($sqlHistory)){
        ?>
        <tr>
            <td class="first"><?php echo ++$index; ?></td>
            <td><?php echo $rowHis['name']; ?></td>
            <td style="font-weight: bold;"><?php echo number_format($rowHis['before_price'], 2); ?></td>
            <td style="font-weight: bold;"><?php echo number_format($rowHis['sell_price'], 2); ?></td>
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
            <td class="first" colspan="6"><?php echo TABLE_NO_RECORD; ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
</fieldset>