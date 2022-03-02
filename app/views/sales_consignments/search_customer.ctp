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
        $pType = "";
        $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$customer['Customer']['id']." GROUP BY cgroup_id) GROUP BY price_type_id");
        if(mysql_num_rows($sqlPriceType)){
            $rowPriceType = mysql_fetch_array($sqlPriceType);
            $pType = $rowPriceType[0];
        }
        echo "{$customer['Customer']['id']}.*{$customer['Customer']['name_kh']}-({$customer['Customer']['name']}).*{$customer['Customer']['customer_code']}.*{$customer['Customer']['payment_term_id']}.*{$rowCompany[0]}.*{$pType}.*{$customer['LocationGroup']['id']}\n";
    }
}
?>