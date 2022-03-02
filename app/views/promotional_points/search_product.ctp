<?php

if (!empty($products)) {
    foreach ($products as $product) {
        echo  "{$product['Product']['id']}.*{$product['Product']['code']}.*{$product['Product']['name']}.*{$product['Product']['company_id']}\n";
    }
}
?>