<?php

/**
 * 0 - Employee ID
 * 1 - Employee Code 
 * 2 - Employee Name
 */
if (!empty($employees)) {
    foreach ($employees as $employee) {
        $sqlCompany   = mysql_query("SELECT GROUP_CONCAT(company_id) AS company_id FROM employee_companies WHERE employee_id = ".$employee['Employee']['id']);
        $rowCompany   = mysql_fetch_array($sqlCompany);
        echo "{$employee['Employee']['id']}.*{$employee['Employee']['employee_code']}.*{$employee['Employee']['name']}.*{$rowCompany[0]}.*{$employee['Employee']['is_show_in_sales']}\n";
    }
}
?>