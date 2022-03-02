<?php

/**
 * 0 - Customer ID
 * 1 - Customer Full Name
 * 2 - Customer Code
 * 3 - Customer Payment Term
 */
if (!empty($customers)) {
    foreach ($customers as $customer) {
        $addressLabel = "";
        if($customer['Customer']['house_no'] != ''){
            $addressLabel .= TABLE_NO.$customer['Customer']['house_no'].", ";
        }
        if($customer['Customer']['street'] != ''){
            $addressLabel .= TABLE_STREET.$customer['Customer']['street'].", ";
        }
        if($string[0] > 0){
            $provinceId = $customer['Customer']['province_id']>0?$customer['Customer']['province_id']:0;
            $districtId = $customer['Customer']['district_id']>0?$customer['Customer']['district_id']:0;
            $communeId  = $customer['Customer']['commune_id']>0?$customer['Customer']['commune_id']:0;
            $villageId  = $customer['Customer']['village_id']>0?$customer['Customer']['village_id']:0;
            $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$provinceId}");    
            $rowAddress = mysql_fetch_array($sqlAddress);
            if(!empty($rowAddress['v_name'])){
                $addressLabel .= $rowAddress['v_name'].", ";
            }
            if(!empty($rowAddress['c_name'])){
                $addressLabel .= $rowAddress['c_name'].", ";
            }
            if(!empty($rowAddress['d_name'])){
                $addressLabel .= $rowAddress['d_name'].", ";
            }
            if(!empty($rowAddress['p_name'])){
                $addressLabel .= $rowAddress['p_name'];
            }
        }
        $sqlCompany   = mysql_query("SELECT GROUP_CONCAT(company_id) AS company_id FROM customer_companies WHERE customer_id = ".$customer['Customer']['id']);
        $rowCompany   = mysql_fetch_array($sqlCompany);
        $pType = "";
        $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$customer['Customer']['id']." GROUP BY cgroup_id) GROUP BY price_type_id");
        if(mysql_num_rows($sqlPriceType)){
            $rowPriceType = mysql_fetch_array($sqlPriceType);
            $pType = $rowPriceType[0];
        }
        echo "{$customer['Customer']['id']}.*{$customer['Customer']['name_kh']}-({$customer['Customer']['name']}).*{$customer['Customer']['customer_code']}.*{$dataNetDays[0]}.*{$customer['Customer']['contact_telephone']}.*{$addressLabel}.*{$customer['Customer']['photo']}.*{$customer['Customer']['payment_term_id']}.*{$rowCompany[0]}.*{$pType}\n";
    }
}
?>