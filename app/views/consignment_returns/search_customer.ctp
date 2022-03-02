<?php

/**
 * 0 - Customer ID
 * 1 - Customer Full Name
 * 2 - Customer Code
 * 3 - Customer Payment Term
 */
if (!empty($customers)) {
    foreach ($customers as $customer) {
        $sqlCompany   = mysql_query("SELECT GROUP_CONCAT(company_id) AS company_id FROM customer_companies WHERE customer_id = ".$customer['Customer']['id']);
        $rowCompany   = mysql_fetch_array($sqlCompany);
        $warehouseId  = "";
        $sqlWarehouse = mysql_query("SELECT id FROM location_groups WHERE customer_id = ".$customer['Customer']['id']);
        if(mysql_num_rows($sqlWarehouse)){
            $rowWarehouse = mysql_fetch_array($sqlWarehouse);
            $warehouseId = $rowWarehouse[0];
        }
        echo "{$customer['Customer']['id']}.*{$customer['Customer']['name_kh']}-({$customer['Customer']['name']}).*{$customer['Customer']['customer_code']}.*{$warehouseId}.*{$rowCompany[0]}\n";
    }
}
?>