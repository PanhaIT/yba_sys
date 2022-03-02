<?php
include("includes/function.php");
$packetList = '';
$productName = '';
$productCusName = '';
$productCode = '';
$productBarcode = '';
$productUom = '';
$productId  = '';
$smallUom   = 0;
$totalQty = 0;
if(!empty($product)){
    $dateNow = date("Y-m-d");
    // Get Product Packet
    if($product['Product']['is_packet'] == 0){
        if(strtotime($dateOrder) < strtotime($dateNow) ){
            /**
            * table MEMORY
            * default max_heap_table_size 16MB
            */
            $tableTmp = "sales_tmp_inventory_".$user['User']['id'];
            mysql_query("SET max_heap_table_size = 1024*1024*1024");
            mysql_query("CREATE TABLE IF NOT EXISTS `$tableTmp` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `date` date DEFAULT NULL,
                              `product_id` int(11) DEFAULT NULL,
                              `location_group_id` int(11) DEFAULT NULL,
                              `total_qty` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `product_id` (`product_id`),
                              KEY `location_group_id` (`location_group_id`),
                              KEY `date` (`date`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
            // Get Total Qty On Peroid
            $joinProducts = " INNER JOIN products ON";
            $tableDailyBbi = "";
            $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out + total_order)) AS total_qty, product_id";
            $conditionDailyBbi = " products.is_active = 1 AND date <= '".$dateOrder."' AND products.id = ".$product['Product']['id'];
            $groupByDaily = "GROUP BY product_id";
            // List Location
            $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id = '.$location_group_id.' AND is_for_sale = 1 GROUP BY id');
            if(@mysql_num_rows($queryLocationList)){
                if(mysql_num_rows($queryLocationList) == 1){
                    while($dataLocationList=mysql_fetch_array($queryLocationList)){
                        // Stock Daily Bigging
                        $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
                    }
                }else{
                    $locId = 1;
                    $tmpLocationId = 0;
                    while($dataLocationList=mysql_fetch_array($queryLocationList)){
                        if($locId == 1){
                            // Stock Daily Bigging
                            $tableDailyBbi .= "SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE".$conditionDailyBbi." ".$groupByDaily;
                        }else{
                            // Stock Daily Ending
                            $tableDailyBbi .= " UNION ALL SELECT ".$filedDailyBbi." FROM ".$dataLocationList['location_id']."_inventory_total_details".$joinProducts." products.id = ".$dataLocationList['location_id']."_inventory_total_details.product_id WHERE ".$conditionDailyBbi." ".$groupByDaily;
                        }
                        $tmpLocationId = $dataLocationList['location_id'];
                        $locId++;
                    }
                }
            }
            // Insert
            $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, product_id FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id";
            $queryTotal = mysql_query($sqlCmtDailyBiginning);
            while($dataTotal = mysql_fetch_array($queryTotal)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    location_group_id,
                                    total_qty
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    " . $location_group_id . ",
                                    " . $dataTotal['qty'] . "
                                )") or die(mysql_error());
            }
            // Get Total Qty Pass
            $sqlTotalPass   = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE product_id = ".$product['Product']['id']." AND date = '".$dateOrder."' AND location_group_id =".$location_group_id);
            $rowTotalPass   = mysql_fetch_array($sqlTotalPass);
            $totalInventory = $product[0]['total_qty']>0?$product[0]['total_qty']:0;
            /** F-ID: 1100
             * Compare Total Qty in Pass and Current Date (rowTotalPass = Total Qty In Pass, totalInventory = Total Qty in Current)
             * IF PASS < CURRENT TotalQty = PASS
             * ELSE PASS >= CURRENT TotalQty = CURRENT
             */
            if($rowTotalPass['total_qty'] < $totalInventory){
                $totalInventory = $rowTotalPass['total_qty'];
            }
            // DROP Tmp Sale Table
            mysql_query("DROP TABLE `".$tableTmp."`;");
        }else{
            $totalInventory = $product[0]['total_qty']>0?$product[0]['total_qty']:0;
        }
        // Get Sales In Order
        $total_order = 0;
        if (!empty($saleOrderId)) {
            $sql = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = " . $product['Product']['id'] . " AND sor.sales_order_id = " . $saleOrderId . " AND sor.location_group_id = ".$location_group_id." AND date = '".$dateOrder."' GROUP BY sor.product_id");
            while (@$r = mysql_fetch_array($sql)) {
                $total_order = $r['total_order'];
            }
        }
        // Get Total QTY Inventory
        $totalQty = $totalInventory + $total_order;
    }else{
        $totalQty = 1;
        $index = 1;
        $sqlPacket = mysql_query("SELECT products.code, product_with_packets.qty_uom_id, product_with_packets.qty, product_with_packets.conversion FROM product_with_packets INNER JOIN products ON products.id = product_with_packets.packet_product_id WHERE product_with_packets.main_product_id = ".$product['Product']['id']);
        while($rowPacket = mysql_fetch_array($sqlPacket)){
            if($index > 1){
                $packetList .= "--";
            }
            $qtyOrder = $rowPacket['qty'];
            $packetList .= $rowPacket['code']."||".$rowPacket['qty_uom_id']."||".$qtyOrder;
            $index++;
        }
    }
    // Check Name With Customer
    $productName = str_replace('"', '&quot;', $product['Product']['name']);
    $sqlProCus   = mysql_query("SELECT name FROM product_with_customers WHERE product_id = ".$product['Product']['id']." AND customer_id = ".$customerId." ORDER BY created DESC LIMIT 1");
    if(@mysql_num_rows($sqlProCus)){
        $rowProCus = mysql_fetch_array($sqlProCus);
        $productCusName = str_replace('"', '&quot;', $rowProCus['name']);
    } else {
        $productCusName = $productName;
    }
    $productCode = htmlspecialchars($product['Product']['code'], ENT_QUOTES, 'UTF-8');
    $productBarcode = htmlspecialchars($product['Product']['barcode'], ENT_QUOTES, 'UTF-8');
    $productId = $product['Product']['id'];
    $productUom = $product['Product']['price_uom_id'];
    $smallUom = $product['Product']['small_val_uom'];
}
$result = array();
$result['product_id'] = $productId;
$result['product_code'] = $productCode;
$result['product_barcode'] = $productBarcode;
$result['product_name'] = $productName;
$result['product_cus_name'] = $productCusName;
$result['product_uom_id'] = $productUom;
$result['small_uom_val'] = $smallUom;
$result['total_qty'] = $totalQty;
$result['packet'] = $packetList;
echo json_encode($result);
?>