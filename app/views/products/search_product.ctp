<?php

/**
 * 0 - Product ID
 * 1 - Product Name
 * 2 - Product Code
 * 3 - Unit Cost
 * 4 - Unit Price
 * 5 - UoM
 */
if (!empty($products)) {
    foreach ($products as $product) {
        $queryLastCost = mysql_query("SELECT unit_cost FROM inventories WHERE product_id=" . $product['Product']['id'] . " ORDER BY date DESC LIMIT 1");
        $dataLastCost = mysql_fetch_array($queryLastCost);
        echo "{$product['Product']['id']}.*{$product['Product']['name']}.*{$product['Product']['code']}.*{$dataLastCost['unit_cost']}.*{$product['Product']['price']}.*{$product['Product']['price_uom_id']}\n";
    }
}
?>