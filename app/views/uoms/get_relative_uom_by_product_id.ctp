<?php
$qry = mysql_query("SELECT price_uom_id FROM products WHERE id=".$_GET['id']);
$row = mysql_fetch_array($qry);

$query=mysql_query("    SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$row['price_uom_id']."
                        UNION
                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$row['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$row['price_uom_id'].")
                        ORDER BY conversion ASC");
$i = 1;
$length = mysql_num_rows($query);
while($data=mysql_fetch_array($query)){?>
<option data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" rel="<?php echo $data['name'];?>" data-item="<?php if($data['id'] == $row['price_uom_id']){ echo "first"; }else{ echo "other";} ?>" <?php if($data['id'] == $row['price_uom_id']){ ?> selected="selected" <?php }?> value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
<?php
$i++;
} ?>