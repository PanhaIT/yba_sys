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
if(!empty($product)){
    // Get Product Packet
    if($product['Product']['is_packet'] == 1){
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
$result['product_name'] = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
$result['product_cus_name'] = htmlspecialchars($productCusName, ENT_QUOTES, 'UTF-8');
$result['product_uom_id'] = $productUom;
$result['small_uom_val'] = $smallUom;
$result['packet'] = $packetList;
echo json_encode($result);
?>