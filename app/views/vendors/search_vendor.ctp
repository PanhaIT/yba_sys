<?php

if (!empty($vendors)) {
    foreach ($vendors as $vendor) {
        $queryNetDays = mysql_query('SELECT (SELECT net_days FROM payment_terms WHERE id=payment_term_id) FROM vendors WHERE id=' . $vendor['Vendor']['id']);
        $dataNetDays  = mysql_fetch_array($queryNetDays);
        $sqlCompany   = mysql_query("SELECT GROUP_CONCAT(company_id) AS company_id FROM vendor_companies WHERE vendor_id = ".$vendor['Vendor']['id']);
        $rowCompany   = mysql_fetch_array($sqlCompany);
        echo "{$vendor['Vendor']['id']}.*{$vendor['Vendor']['name']}.*{$vendor['Vendor']['vendor_code']}.*{$dataNetDays[0]}.*{$rowCompany[0]}\n";
    }
}
?>