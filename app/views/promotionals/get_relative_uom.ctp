<?php
include("includes/function.php");
$query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomId."
                    UNION
                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomId.")
                    ORDER BY conversion ASC");
$i = 1;
$length = mysql_num_rows($query);
while($data=mysql_fetch_array($query)){
    $selected = "";
    $priceLbl = "";
    $costLbl  = "";
    if($data['id'] == $uomId){   
        $selected = ' selected="selected" ';
    }
    if(!empty($productId)){
        $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$productId." AND product_prices.branch_id = ".$branchId." AND product_prices.uom_id =".$data['id']);
        if(@mysql_num_rows($sqlPrice)){
            $price = 0;
            while($rowPrice = mysql_fetch_array($sqlPrice)){
                $unitCost = replaceThousand(number_format($rowPrice['unit_cost'] /  $data['conversion'], 2));
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
            $sqlPriceType = mysql_query("SELECT price_types.id FROM price_types INNER JOIN price_type_companies ON price_type_companies.price_type_id = price_types.id AND price_type_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE price_types.is_active = 1 GROUP BY price_types.id;");
            while($rowPriceType = mysql_fetch_array($sqlPriceType)){
                $priceLbl .= 'price-uom-'.$rowPriceType[0].'="0"';
                $costLbl  .= 'cost-uom-'.$rowPriceType[0].'="0"';
            }
        }
    }else{
        $sqlPriceType = mysql_query("SELECT price_types.id FROM price_types INNER JOIN price_type_companies ON price_type_companies.price_type_id = price_types.id AND price_type_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE price_types.is_active = 1 GROUP BY price_types.id;");
        while($rowPriceType = mysql_fetch_array($sqlPriceType)){
            $priceLbl .= 'price-uom-'.$rowPriceType[0].'="0"';
            $costLbl  .= 'cost-uom-'.$rowPriceType[0].'="0"';
        }
    }
    if($length == $i){
?>
<option <?php echo $priceLbl; ?> <?php echo $costLbl; ?> <?php //echo $selected; ?><?php if($length == $i){ ?>selected='selected'<?php } ?> data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $uomId){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
<?php 
    }
$i++;
} ?>