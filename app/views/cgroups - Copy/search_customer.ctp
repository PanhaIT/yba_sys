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
        echo "{$customer['Customer']['id']}.*{$customer['Customer']['customer_code']}.*{$customer['Customer']['name']}.*{$rowCompany[0]}\n";
    }
}
?>