<?php
if (!empty($products)) {
    foreach ($products as $product) {
        echo "{$product['Product']['barcode']}.*{$product['Product']['name']}\n";
    }
}
?>