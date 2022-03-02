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
$isLot = 0;
$isExp = 0;
$price6  = 0;
$price12 = 0;
if(!empty($product)){
    $dateNow = date("Y-m-d");
    // Get Product Packet
    if($product['Product']['is_packet'] == 0){
        if(strtotime($dateOrder) < strtotime($dateNow) ){
            $sqlLocSetting = mysql_query("SELECT * FROM location_settings WHERE id = 4");
            $rowLocSetting = mysql_fetch_array($sqlLocSetting);
            $locCon     = '';
            if($rowLocSetting['location_status'] == 1){
                $locCon = ' AND is_for_sale = 1';
            }
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
                              `lots_number` VARCHAR(50) NOT NULL,
                              `expired_date` DATE NOT NULL,
                              `location_group_id` int(11) DEFAULT NULL,
                              `total_qty` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `product_id` (`product_id`),
                              KEY `lots_number` (`lots_number`),
                              KEY `expired_date` (`expired_date`),
                              KEY `location_group_id` (`location_group_id`),
                              KEY `date` (`date`)
                            ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            mysql_query("TRUNCATE $tableTmp") or die(mysql_error());
            // Get Total Qty On Peroid
            $joinProducts = " INNER JOIN products ON";
            $tableDailyBbi = "";
            $filedDailyBbi = "SUM((total_pb + total_cm + total_to_in + total_cycle + total_cus_consign_in) - (total_so + total_pbc + total_pos + total_to_out + total_cus_consign_out)) AS total_qty, product_id, lots_number, expired_date";
            $conditionDailyBbi = " products.is_active = 1 AND date <= '".$dateOrder."' AND lots_number = '".$lotsNumber."' AND products.id = ".$product['Product']['id'];
            $groupByDaily = "GROUP BY product_id, lots_number, expired_date";
            // List Location
            $queryLocationList = mysql_query('SELECT id AS location_id FROM locations WHERE location_group_id = '.$location_group_id.$locCon.' GROUP BY id');
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
            $sqlCmtDailyBiginning  = "SELECT SUM(total_qty) AS qty, product_id, lots_number, expired_date FROM (".$tableDailyBbi.") AS stockDaily GROUP BY product_id, lots_number, expired_date";
            $queryTotal = mysql_query($sqlCmtDailyBiginning);
            while($dataTotal = mysql_fetch_array($queryTotal)){
                mysql_query("INSERT INTO $tableTmp (
                                    date,
                                    product_id,
                                    lots_number,
                                    expired_date,
                                    location_group_id,
                                    total_qty
                                ) VALUES (
                                    '" . $dateOrder . "',
                                    " . $dataTotal['product_id'] . ",
                                    '" . $dataTotal['lots_number'] . "',
                                    '" . $dataTotal['expired_date'] . "',
                                    " . $location_group_id . ",
                                    " . $dataTotal['qty'] . "
                                )") or die(mysql_error());
            }
            // Get Total Qty Pass
            $sqlTotalPass   = mysql_query("SELECT SUM(total_qty) AS total_qty FROM ".$tableTmp." WHERE product_id = ".$product['Product']['id']." AND date = '".$dateOrder."' AND lots_number = '".$lotsNumber."' AND expired_date = '".$expiredDate."' AND location_group_id =".$location_group_id);
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
            $sql = mysql_query("SELECT sum(sor.qty) as total_order FROM `stock_orders` as sor WHERE sor.product_id = " . $product['Product']['id'] . " AND sor.sales_order_id = " . $saleOrderId . " AND sor.location_group_id = ".$location_group_id." AND date = '".$dateOrder."' AND lots_number = '".$lotsNumber."' AND expired_date = '".$expiredDate."' GROUP BY sor.product_id");
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
    $isLot = $product['Product']['is_lots'];
    $isExp = $product['Product']['is_expired_date'];
    if($product['Product']['small_val_uom'] == 1){
        $sqlQtyP = mysql_query("SELECT * FROM product_qty_prices WHERE product_id = ".$product['Product']['id']);
        if(mysql_num_rows($sqlQtyP)){
            while($rowQtyP = mysql_fetch_array($sqlQtyP)){
                if($rowQtyP['qty'] == 6){
                    $price6 = $rowQtyP['unit_price'];
                } else if($rowQtyP['qty'] == 12){
                    $price12 = $rowQtyP['unit_price'];
                }
            }
        }
    }
}
// Check Warehouse Allow Negative Stock
$sqlWareOpt = mysql_query("SELECT allow_negative_stock FROM location_groups WHERE id = ".$location_group_id);
$rowWareOpt = mysql_fetch_array($sqlWareOpt);
if($rowWareOpt['allow_negative_stock'] == 1){
    $totalQty = 1000000;
}
if($lotsNumber == '0'){
    $lotsNumber = '';
}
if($expiredDate == '0000-00-00'){
    $expiredDate = '';
}
$result = array();
$result['product_id']   = $productId;
$result['product_code'] = $productCode;
$result['product_barcode']  = $productBarcode;
$result['product_name']     = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
$result['product_cus_name'] = htmlspecialchars($productCusName, ENT_QUOTES, 'UTF-8');
$result['product_uom_id'] = $productUom;
$result['small_uom_val']  = $smallUom;
$result['lots_number']    = $lotsNumber;
$result['expired_date']   = $expiredDate;
$result['total_qty'] = $totalQty;
$result['packet']  = $packetList;
$result['is_lot']  = $isLot;
$result['is_exp']  = $isExp;
$result['price6']  = $price6;
$result['price12'] = $price12;
$result['promotion'] = $promotional;
echo json_encode($result);
?>