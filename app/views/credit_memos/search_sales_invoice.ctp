<?php
include("includes/function.php");
if (!empty($salesOrders)) {
    foreach($salesOrders AS $salesOrder){
        $date = $salesOrder['SalesOrder']['order_date'];
        $invoiceDate = dateShort($date);
        echo "{$salesOrder['SalesOrder']['id']}.*{$salesOrder['SalesOrder']['so_code']}.*{$invoiceDate}.*{$salesOrder['Customer']['id']}.*{$salesOrder['Customer']['customer_code']}.*{$salesOrder['Customer']['name']}\n";
    }
}

