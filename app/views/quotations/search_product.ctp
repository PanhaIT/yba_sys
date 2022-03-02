<?php

if (!empty($products)) {
    foreach ($products as $product) {
        echo "{$product['Product']['code']}.*{$product['Product']['name']}\n";
    }
}
?>