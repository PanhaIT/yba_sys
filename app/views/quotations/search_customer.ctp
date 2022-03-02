<?php
$sqlCus = mysql_query("SELECT customers.id, customers.customer_code, customers.name, GROUP_CONCAT(ccp.company_id) FROM customers INNER JOIN `customer_companies` AS ccp ON ccp.customer_id = customers.id AND ccp.company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") INNER JOIN `customer_cgroups` as gr ON customers.id = gr.customer_id INNER JOIN `cgroups` ON cgroups.id = gr.cgroup_id AND (cgroups.user_apply = 0 OR {$user['User']['id']} IN (SELECT user_id FROM user_cgroups WHERE cgroup_id = cgroups.id)) 
                       WHERE customers.is_active = 1 
                       AND (customers.name LIKE '%".$search."%'
                       OR customers.name_kh LIKE '%".$search."%'
                       OR customers.customer_code LIKE '%".$search."%'
                       OR customers.main_number LIKE '%".$search."%'
                       OR customers.email LIKE '%".$search."%') GROUP BY customers.id ORDER BY customers.customer_code LIMIT 30");
if(mysql_num_rows($sqlCus)){
    while($rowCus = mysql_fetch_array($sqlCus)){
        $pType = "";
        $sqlPriceType = mysql_query("SELECT GROUP_CONCAT(price_type_id) FROM cgroup_price_types WHERE cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$rowCus[0]." GROUP BY cgroup_id)");
        if(mysql_num_rows($sqlPriceType)){
            $rowPriceType = mysql_fetch_array($sqlPriceType);
            $pType = $rowPriceType[0];
        }
        echo "{$rowCus[0]}.*{$rowCus[1]}.*{$rowCus[2]}.*{$rowCus[3]}.*{$pType}\n";
    }
}
?>
