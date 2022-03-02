<?php
include("includes/function.php");
$rowTable = "";
$return   = false;
$uomSmallVal   = 1;
$uomSmallLabel = "";
$mainUomName   = "";
$allowLots    = false;
$allowExpired = false;
$disLots      = 'display: none;';
$disExpired   = 'display: none;';
$sqlSetting   = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (6, 7) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 6){
        if($rowSetting['is_checked'] == 1){
            $disLots = '';
            $allowLots = true;
        }
    } else if($rowSetting['id'] == 7){
        if($rowSetting['is_checked'] == 1){
            $disExpired = '';
            $allowExpired = true;
        }
    }
}
// Calculate Location From & Product
$i = 1;
$sqlLocation = mysql_query("SELECT id, name FROM locations WHERE is_active = 1 AND location_group_id = {$locationGroupFrom} ORDER BY name");
while($rowLocation = mysql_fetch_array($sqlLocation)){
    $sqlInv = mysql_query("SELECT p.unit_cost, p.price_uom_id AS price_uom_id, p.id AS id, p.small_val_uom AS small_val_uom, p.code AS code, p.barcode AS barcode, p.name AS name, inv.lots_number AS lots_number, inv.expired_date AS date_expired, SUM((inv.total_pb + total_to_in + total_cm + total_cycle + total_cus_consign_in) - (inv.total_so + inv.total_pos + inv.total_pbc + inv.total_to_out + total_cus_consign_out + inv.total_order)) AS total_qty, u.abbr AS abbr FROM {$rowLocation['id']}_inventory_total_details AS inv INNER JOIN products AS p ON p.id = inv.product_id INNER JOIN uoms AS u ON u.id = p.price_uom_id WHERE inv.product_id = {$id} AND inv.date <= '{$date}' GROUP BY inv.product_id, inv.lots_number, inv.expired_date HAVING total_qty > 0 ORDER BY p.code");
    if(@mysql_num_rows($sqlInv)){    
        while($row = mysql_fetch_array($sqlInv)){
            $sqlUomSm = mysql_query("SELECT abbr FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = {$row['price_uom_id']} AND is_small_uom = 1 AND is_active = 1 ORDER BY id DESC LIMIT 1)");
            if(mysql_num_rows($sqlUomSm)){
                $rowUomSm = mysql_fetch_array($sqlUomSm);
                $uomSmallLabel = $rowUomSm['abbr'];
            }
            $totalOrder  = 0;
            $uomSmallVal = $row['small_val_uom'];
            $mainUomName = $row['abbr'];
            if(!empty($transferId)){
                $sqlOrder = mysql_query("SELECT SUM(qty) AS qty FROM stock_orders WHERE transfer_order_id = ".$transferId." AND product_id = ".$row['id']." AND location_group_id = {$locationGroupFrom} AND location_id = {$rowLocation['id']} AND lots_number = '{$row['lots_number']}' AND expired_date = '{$row['date_expired']}' AND date = '".$date."' GROUP BY product_id");
                $rowOrder = mysql_fetch_array($sqlOrder);
                $totalOrder = $rowOrder[0];
            }
            // Get Product Information
            $product  = array();
            $product['Product']['id']               = $row['id'];
            $product['Product']['code']             = htmlspecialchars($row['code'], ENT_QUOTES, 'UTF-8');
            $product['Product']['barcode']          = htmlspecialchars($row['barcode'], ENT_QUOTES, 'UTF-8');
            $product['Product']['name']             = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $product['Product']['lots_number']      = htmlspecialchars($row['lots_number'], ENT_QUOTES, 'UTF-8');
            $product['Product']['date_expired']     = $row['date_expired'];
            $product['Product']['total_qty']        = ($row['total_qty'] + $totalOrder);
            $product['Product']['location_id']      = $rowLocation['id'];
            $product['Product']['small_val_uom']    = $row['small_val_uom'];
            $product['Product']['total_qty_label']  = showTotalQty(($row['total_qty'] + $totalOrder), $mainUomName, $uomSmallVal, $uomSmallLabel);
            $product['Product']['unit_cost']        = $row['unit_cost'];
            // Get Row Table
            $lotsNumber = "";
            $dateExp    = "";
            if($row['lots_number'] != 0 && $row['lots_number'] != ""){
                $lotsNumber = $row['lots_number'];
            }
            if($row['date_expired'] != "0000-00-00" && $row['date_expired'] != ""){
                $dateExp = dateShort($row['date_expired']);
            }
            $rowTable .= "<tr style='cursor: pointer;'>";
            $rowTable .= "<td class='first'><input type='checkbox' value='".json_encode($product)."' name='chkProductQtyTO' /></td>";
            $rowTable .= "<td>".$row['barcode']."</td>";
            $rowTable .= "<td>".$row['code']."</td>";
            $rowTable .= "<td>".$row['name']."</td>";
            $rowTable .= "<td style='".$disLots."'>".$lotsNumber."</td>";
            $rowTable .= "<td style='".$disExpired."'>".$dateExp."</td>";
            $rowTable .= "<td>".$rowLocation['name']."</td>";
            $rowTable .= "<td>".showTotalQty(($row['total_qty'] + $totalOrder), $mainUomName, $uomSmallVal, $uomSmallLabel)."</td>";
            $rowTable .= '<td><input type="text" style="width: 95%;" total-qty="'.($row['total_qty'] + $totalOrder).'" value="0" class="inputQtyTo" id="inputQtyTo_'.$i.'" /></td>';
            $rowTable .= "<td>".$row['abbr']."</td>";
            $rowTable .= "</tr>";
            $i++;
        }
        $return   = true;
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".inputQtyTo").autoNumeric({mDec: 0, aSep: ','});
        $(".inputQtyTo").keyup(function(){
            var row = $(this).closest("tr");
            var val = replaceNum($(this).val());
            if(val > 0 && val != ''){
                row.find("input[name='chkProductQtyTO']").attr('checked', 'checked');
            }else{
                row.find("input[name='chkProductQtyTO']").removeAttr('checked');
            }
        });
        
        $(".inputQtyTo").focus(function(){
            if($(this).val() == '0'){
                $(this).val("");
            }
        });
        
        $(".inputQtyTo").blur(function(){
            var totalQty = replaceNum($(this).attr('total-qty'));
            var qty      = replaceNum($(this).val());
            if($(this).val() == ""){
                $(this).val(0);
            }
            if(totalQty < qty){
                $(this).val(totalQty);
            }
        });
    });
</script>
<br/>
<table class="table" id="tableShowTotalQtyTO" style="width: 1000px;">
    <tr>
        <th class="first" style="width: 5%;"></th>
        <th style="width: 8%;"><?php echo TABLE_BARCODE; ?></th>
        <th style="width: 8%;"><?php echo TABLE_SKU; ?></th>
        <th style=""><?php echo TABLE_NAME; ?></th>
        <th style="width: 10%; <?php if($allowLots == false){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
        <th style="width: 10%; <?php if($allowExpired == false){ ?>display: none;<?php } ?>"><?php echo TABLE_EXPIRED_DATE; ?></th>
        <th style="width: 10%;"><?php echo TABLE_LOCATION; ?></th>
        <th style="width: 12%;"><?php echo TABLE_TOTAL_QTY; ?></th>
        <th style="width: 9%;"><?php echo TABLE_QTY; ?></th>
        <th style="width: 8%;"><?php echo TABLE_UOM; ?></th>
    </tr>
    <?php
    if($return ==  true){
        echo $rowTable;
    }else{
    ?>
    <tr>
        <td class="first" colspan="10" style="text-align: center;"><?php echo MESSAGE_OUT_OF_STOCK; ?></td>
    </tr>
    <?php
    }
    ?>
</table>