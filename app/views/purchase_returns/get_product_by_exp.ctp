<?php
$allowLots    = false;
$allowExpired = false;
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $allowExpired = true;
        }
    }
}
include("includes/function.php");
$rowTable = "";
$return   = false;
$uomSmallVal   = 1;
$uomSmallLabel = "";
$mainUomName   = "";
// Calculate Location From & Product
$i = 1;
$sqlInv = mysql_query("SELECT p.price_uom_id AS price_uom_id, p.id AS id, p.small_val_uom AS small_val_uom, p.code AS code, p.barcode AS barcode, p.name AS name, inv.lots_number AS lots_number, inv.date_expired AS date_expired, SUM(inv.qty) AS total_qty, u.abbr AS abbr FROM inventories AS inv INNER JOIN products AS p ON p.id = inv.product_id INNER JOIN uoms AS u ON u.id = p.price_uom_id WHERE inv.is_active = 1 AND inv.product_id = {$productId} AND inv.location_id = {$locationId} AND inv.date <= '".$orderDate."' GROUP BY inv.product_id, inv.lots_number, inv.date_expired ORDER BY p.code");
if(mysql_num_rows($sqlInv)){    
    while($row = mysql_fetch_array($sqlInv)){
        if($row['total_qty'] > 0){
            $sqlUomSm = mysql_query("SELECT abbr FROM uoms WHERE id = (SELECT id FROM uom_conversions WHERE from_uom_id = {$row['price_uom_id']} AND is_small_uom = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1)");
            if(mysql_num_rows($sqlUomSm)){
                $rowUomSm = mysql_fetch_array($sqlUomSm);
                $uomSmallLabel = $rowUomSm['abbr'];
            }
            $uomSmallVal = $row['small_val_uom'];
            $mainUomName = $row['abbr'];
            // Get Total Order
            $sqlOrder = mysql_query("SELECT qty FROM stock_orders WHERE product_id = ".$row['id']." AND expired_date = '".$row['date_expired']."' AND location_id = {$locationId} AND date <= '".$orderDate."' GROUP BY product_id");
            $rowOrder = mysql_fetch_array($sqlOrder);
            $totalOrder = $rowOrder['qty']!=''?$rowOrder['qty']:0;
            // Get Row Table
            $lotsDis = ' style="display: none;"';
            if($allowLots == true){
                $lotsDis = '';
            }
            $expDis = ' style="display: none;"';
            if($allowExpired == true){
                $expDis = '';
            }
            $rowTable .= "<tr style='cursor: pointer;'>";
            $rowTable .= "<td class='first'><input type='radio' value='".($row['total_qty'] - $totalOrder)."' lot='".$row['lots_number']."' exp='".$row['date_expired']."' name='chkProductByExpPR' /></td>";
            $rowTable .= "<td>".$row['barcode']."</td>";
            $rowTable .= "<td>".$row['code']."</td>";
            $rowTable .= "<td>".$row['name']."</td>";
            $rowTable .= "<td".$lotsDis.">".$row['lots_number']."</td>";
            $rowTable .= "<td".$expDis.">".$row['date_expired']."</td>";
            $rowTable .= "<td>".showTotalQty($row['total_qty'], $mainUomName, $uomSmallVal, $uomSmallLabel)."</td>";
            $rowTable .= "<td>".showTotalQty($totalOrder, $mainUomName, $uomSmallVal, $uomSmallLabel)."</td>";
            $rowTable .= "</tr>";
            $i++;
        }
    }
    $return   = true;
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#tableShowExpSales tr").click(function(){
            $(this).find("input[name='chkProductByExpPR']").attr("checked", true);
        });
    });
</script>
<br/>
<table class="table" id="tableShowExpSales" style="width: 1000px;">
    <tr>
        <th class="first" style="width: 5%;"></th>
        <th style="width: 8%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width: 8%;"><?php echo TABLE_SKU; ?></th>
        <th style=""><?php echo TABLE_NAME; ?></th>
        <th style="width: 10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width: 10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXP_DATE_SHORT; ?></th>
        <th style="width: 12%;"><?php echo TABLE_TOTAL_QTY; ?></th>
        <th style="width: 9%;"><?php echo TABLE_TOTAL_ORDER; ?></th>
        <th style="width: 8%;"><?php echo TABLE_UOM; ?></th>
    </tr>
    <?php
    if($return ==  true){
        echo $rowTable;
    }else{
    ?>
    <tr>
        <td class="first" colspan="9" style="text-align: center;"><?php echo MESSAGE_OUT_OF_STOCK; ?></td>
    </tr>
    <?php
    }
    ?>
</table>

