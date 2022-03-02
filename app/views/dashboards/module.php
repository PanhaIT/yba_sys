<?php

// Function
include('includes/function.php');

// Authentication
$this->element('check_access');
$moduleChart = checkAccess($user['User']['id'], 'reports', 'balanceSheet');
$moduleSales = checkAccess($user['User']['id'], 'sales_invoices', 'view');
$modulePurchase = checkAccess($user['User']['id'], 'purchase_bills', 'view');
$moduleTransfer = checkAccess($user['User']['id'], 'transfer_orders', 'view');
$moduleProduct = checkAccess($user['User']['id'], 'products', 'view');

if ($moduleChart) {
    // chart aged receivables
    $arDateRange1=0;
    $arDateRange2=0;
    $arDateRange3=0;
    $arDateRange4=0;
    $arrCoAIdList = array();
    $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
    while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
        $arrCoAIdList[]=$dataCoAIdList['id'];
    }
    if(sizeof($arrCoAIdList)!=0){
        $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_invoice_id) AS arr_sales_invoice_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 0 AND 30
                                    AND debit>0
                                    AND sales_return_id IS NULL");
        @$data1  = mysql_fetch_array($query1);
        $query2 = mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        sales_invoice_id IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(sales_return_id) AS arr_sales_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 0 AND 30
                                    AND credit>0
                                    AND sales_invoice_id IS NULL
                                    AND (sales_return_id IS NULL OR (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("  SELECT SUM(debit) AS amount
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND debit>0
                                        AND (
                                            sales_return_id IN (" . ($data3['arr_sales_return_id']!=""?$data3['arr_sales_return_id']:-1) . ")
                                            OR
                                            (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $arDateRange1=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_invoice_id) AS arr_sales_invoice_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 31 AND 60
                                    AND debit>0
                                    AND sales_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        sales_invoice_id IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(sales_return_id) AS arr_sales_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 31 AND 60
                                    AND credit>0
                                    AND sales_invoice_id IS NULL
                                    AND (sales_return_id IS NULL OR (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        sales_return_id IN (" . ($data3['arr_sales_return_id']!=""?$data3['arr_sales_return_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $arDateRange2=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_invoice_id) AS arr_sales_invoice_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 61 AND 90
                                    AND debit>0
                                    AND sales_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        sales_invoice_id IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(sales_return_id) AS arr_sales_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 61 AND 90
                                    AND credit>0
                                    AND sales_invoice_id IS NULL
                                    AND (sales_return_id IS NULL OR (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        sales_return_id IN (" . ($data3['arr_sales_return_id']!=""?$data3['arr_sales_return_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $arDateRange3=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_invoice_id) AS arr_sales_invoice_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) > 90
                                    AND debit>0
                                    AND sales_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        sales_invoice_id IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(sales_return_id) AS arr_sales_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) > 90
                                    AND credit>0
                                    AND sales_invoice_id IS NULL
                                    AND (sales_return_id IS NULL OR (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        sales_return_id IN (" . ($data3['arr_sales_return_id']!=""?$data3['arr_sales_return_id']:-1) . ")
                                        OR
                                        (SELECT sales_invoice_id FROM sales_returns WHERE id=sales_return_id) IN (" . ($data1['arr_sales_invoice_id']!=""?$data1['arr_sales_invoice_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $arDateRange4=!is_null($amount)?$amount:0;
    }

    // chart aged payables
    $apDateRange1=0;
    $apDateRange2=0;
    $apDateRange3=0;
    $apDateRange4=0;
    $arrCoAIdList = array();
    $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Payable')");
    while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
        $arrCoAIdList[]=$dataCoAIdList['id'];
    }
    if(sizeof($arrCoAIdList)!=0){
        $query1=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_bill_id) AS arr_purchase_bill_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 0 AND 30
                                    AND credit>0
                                    AND purchase_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        purchase_bill_id IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 0 AND 30
                                    AND debit>0
                                    AND purchase_bill_id IS NULL
                                    AND (purchase_return_id IS NULL OR (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $apDateRange1=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_bill_id) AS arr_purchase_bill_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 31 AND 60
                                    AND credit>0
                                    AND purchase_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        purchase_bill_id IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 31 AND 60
                                    AND debit>0
                                    AND purchase_bill_id IS NULL
                                    AND (purchase_return_id IS NULL OR (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $apDateRange2=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_bill_id) AS arr_purchase_bill_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 61 AND 90
                                    AND credit>0
                                    AND purchase_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        purchase_bill_id IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) BETWEEN 61 AND 90
                                    AND debit>0
                                    AND purchase_bill_id IS NULL
                                    AND (purchase_return_id IS NULL OR (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $apDateRange3=!is_null($amount)?$amount:0;

        $query1=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_bill_id) AS arr_purchase_bill_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) > 90
                                    AND credit>0
                                    AND purchase_return_id IS NULL");
        @$data1=mysql_fetch_array($query1);
        $query2=mysql_query("   SELECT SUM(debit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND debit>0
                                    AND (
                                        purchase_bill_id IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                        OR
                                        main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                    )");
        @$data2=mysql_fetch_array($query2);
        $query3=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND DATEDIFF(now(),date) > 90
                                    AND debit>0
                                    AND purchase_bill_id IS NULL
                                    AND (purchase_return_id IS NULL OR (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IS NULL)
                                    AND main_gl_id IS NULL");
        @$data3=mysql_fetch_array($query3);
        $query4=mysql_query("   SELECT SUM(credit) AS amount
                                FROM general_ledgers gl
                                    INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE is_active=1
                                    AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                    AND credit>0
                                    AND (
                                        purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                        OR
                                        (SELECT purchase_bill_id FROM purchase_returns WHERE id=purchase_return_id) IN (" . ($data1['arr_purchase_bill_id']!=""?$data1['arr_purchase_bill_id']:-1) . ")
                                    )");
        @$data4=mysql_fetch_array($query4);
        $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
        $amount=number_format($amount,2,".","");
        $apDateRange4=!is_null($amount)?$amount:0;
    }

    // Revenue Year to Date
    $sqlRevenue="SELECT ";
    $sqlCOGS="SELECT ";
    $sqlExpense="SELECT ";
    for($month=1;$month<=12;$month++){
        $sqlRevenue.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=11 || chart_account_type_id=14) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now()))-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=11 || chart_account_type_id=14) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now())),0),";
        $sqlCOGS.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=12) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now()))-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=12) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now())),0),";
        $sqlExpense.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=13 || chart_account_type_id=15) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now()))-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=13 || chart_account_type_id=15) AND gl.is_active=1 AND MONTH(date)=".$month." AND YEAR(date)=YEAR(now())),0),";
    }
    $sqlRevenue=substr($sqlRevenue,0,-1);
    $sqlCOGS=substr($sqlCOGS,0,-1);
    $sqlExpense=substr($sqlExpense,0,-1);
    $queryRevenue=mysql_query($sqlRevenue);
    $dataRevenue=mysql_fetch_array($queryRevenue);
    $queryCOGS=mysql_query($sqlCOGS);
    $dataCOGS=mysql_fetch_array($queryCOGS);
    $queryExpense=mysql_query($sqlExpense);
    $dataExpense=mysql_fetch_array($queryExpense);
    for($month=1;$month<=12;$month++){
        $totalRevenue[$month]=$dataRevenue[$month-1]*-1;
        $totalCOGS[$month]=$dataCOGS[$month-1];
        $totalGrossProfit[$month]=$totalRevenue[$month]-$totalCOGS[$month];
        $totalExpense[$month]=$dataExpense[$month-1];
        $totalProfitLoss[$month]=$totalGrossProfit[$month]-$totalExpense[$month];
    }
}

?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/Highcharts-2.2.1/js/highcharts.js"></script>
<script type="text/javascript">
    var chartAgedReceivables;
    var chartAgedPayables;
    var chartRevenue;
    $(document).ready(function(){
        <?php if ($moduleChart) { ?>
        // chart aged receivables
        chartAgedReceivables = new Highcharts.Chart({
            chart: {
                renderTo: 'aged_receivables',
                type: 'column'
            },
            title: {
                text: '<?php echo MENU_AGED_RECEIVABLES; ?>'
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: [
                    ''
                ]
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            tooltip: {
                formatter: function() {
                    return Highcharts.numberFormat(this.y, 2, '.');
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
                series: [{
                name: '0-30',
                color: '#22b24c',
                data: [<?php echo $arDateRange1; ?>]
    
            }, {
                name: '31-60',
                color: '#0072bc',
                data: [<?php echo $arDateRange2; ?>]
    
            }, {
                name: '61-90',
                color: '#fff200',
                data: [<?php echo $arDateRange3; ?>]
    
            }, {
                name: 'Over 90 days',
                color: '#f86a46',
                data: [<?php echo $arDateRange4; ?>]
    
            }]
        });
        // chart aged payables
        chartAgedPayables = new Highcharts.Chart({
            chart: {
                renderTo: 'aged_payables',
                type: 'column'
            },
            title: {
                text: '<?php echo MENU_AGED_PAYABLES; ?>'
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: [
                    ''
                ]
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            tooltip: {
                formatter: function() {
                    return Highcharts.numberFormat(this.y, 2, '.');
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
                series: [{
                name: '0-30',
                color: '#22b24c',
                data: [<?php echo $apDateRange1; ?>]

            }, {
                name: '31-60',
                color: '#0072bc',
                data: [<?php echo $apDateRange2; ?>]

            }, {
                name: '61-90',
                color: '#fff200',
                data: [<?php echo $apDateRange3; ?>]

            }, {
                name: 'Over 90 days',
                color: '#f86a46',
                data: [<?php echo $apDateRange4; ?>]

            }]
        });
        // chart revenue year to date
        chartRevenue = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'line',
                marginRight: 130,
                marginBottom: 25
            },
            title: {
                text: '<?php echo MENU_REVENUE_YEAR_TO_DATE; ?>',
                x: -20 //center
            },
            xAxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            yAxis: {
                title: {
                    text: 'Amount (US$)'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                formatter: function() {
                        return '<b>'+ this.series.name + '</b><br/>' + this.x + ': ' + Highcharts.numberFormat(this.y, 2, '.');
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
                name: 'Total Revenue',
                data: [
                    <?php echo $totalRevenue[1]; ?>,
                    <?php echo $totalRevenue[2]; ?>,
                    <?php echo $totalRevenue[3]; ?>,
                    <?php echo $totalRevenue[4]; ?>,
                    <?php echo $totalRevenue[5]; ?>,
                    <?php echo $totalRevenue[6]; ?>,
                    <?php echo $totalRevenue[7]; ?>,
                    <?php echo $totalRevenue[8]; ?>,
                    <?php echo $totalRevenue[9]; ?>,
                    <?php echo $totalRevenue[10]; ?>,
                    <?php echo $totalRevenue[11]; ?>,
                    <?php echo $totalRevenue[12]; ?>
                ],
                color: '#000000'
            }, {
                name: 'COGS',
                data: [
                    <?php echo $totalCOGS[1]; ?>,
                    <?php echo $totalCOGS[2]; ?>,
                    <?php echo $totalCOGS[3]; ?>,
                    <?php echo $totalCOGS[4]; ?>,
                    <?php echo $totalCOGS[5]; ?>,
                    <?php echo $totalCOGS[6]; ?>,
                    <?php echo $totalCOGS[7]; ?>,
                    <?php echo $totalCOGS[8]; ?>,
                    <?php echo $totalCOGS[9]; ?>,
                    <?php echo $totalCOGS[10]; ?>,
                    <?php echo $totalCOGS[11]; ?>,
                    <?php echo $totalCOGS[12]; ?>
                ],
                color: '#dece47'
            }, {
                name: 'Gross Profit',
                data: [
                    <?php echo $totalGrossProfit[1]; ?>,
                    <?php echo $totalGrossProfit[2]; ?>,
                    <?php echo $totalGrossProfit[3]; ?>,
                    <?php echo $totalGrossProfit[4]; ?>,
                    <?php echo $totalGrossProfit[5]; ?>,
                    <?php echo $totalGrossProfit[6]; ?>,
                    <?php echo $totalGrossProfit[7]; ?>,
                    <?php echo $totalGrossProfit[8]; ?>,
                    <?php echo $totalGrossProfit[9]; ?>,
                    <?php echo $totalGrossProfit[10]; ?>,
                    <?php echo $totalGrossProfit[11]; ?>,
                    <?php echo $totalGrossProfit[12]; ?>
                ],
                color: '#0072bc'
            }, {
                name: 'Expense',
                data: [
                    <?php echo $totalExpense[1]; ?>,
                    <?php echo $totalExpense[2]; ?>,
                    <?php echo $totalExpense[3]; ?>,
                    <?php echo $totalExpense[4]; ?>,
                    <?php echo $totalExpense[5]; ?>,
                    <?php echo $totalExpense[6]; ?>,
                    <?php echo $totalExpense[7]; ?>,
                    <?php echo $totalExpense[8]; ?>,
                    <?php echo $totalExpense[9]; ?>,
                    <?php echo $totalExpense[10]; ?>,
                    <?php echo $totalExpense[11]; ?>,
                    <?php echo $totalExpense[12]; ?>
                ],
                color: '#f86a46'
            }, {
                name: 'Net Income',
                data: [
                    <?php echo $totalProfitLoss[1]; ?>,
                    <?php echo $totalProfitLoss[2]; ?>,
                    <?php echo $totalProfitLoss[3]; ?>,
                    <?php echo $totalProfitLoss[4]; ?>,
                    <?php echo $totalProfitLoss[5]; ?>,
                    <?php echo $totalProfitLoss[6]; ?>,
                    <?php echo $totalProfitLoss[7]; ?>,
                    <?php echo $totalProfitLoss[8]; ?>,
                    <?php echo $totalProfitLoss[9]; ?>,
                    <?php echo $totalProfitLoss[10]; ?>,
                    <?php echo $totalProfitLoss[11]; ?>,
                    <?php echo $totalProfitLoss[12]; ?>
                ],
                color: '#22b24c'
            }]
        });
        <?php } ?>
        $(".btnPrintSalesOrderInvoice").click(function(event){
            event.preventDefault();
            var isPos = $(this).attr("href");
            var url  = "";
            if($(this).closest("tr").find("td:last").text()=='POS'){
                url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
            }else{
                url = "<?php echo $this->base . '/sales_invoices'; ?>/printInvoice/"+$(this).attr("rel");
            }
            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
        $(".btnPrintPurchaseOrderInvoice").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/'; ?>purchase_bills/printInvoice/"+$(this).attr("rel"),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
        $(".btnPrintTOInvoice").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base ?>/transfer_orders/printReceipt/"+$(this).attr("rel"),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
    });
</script>
<?php if ($moduleChart) { ?>
<div style="width:49%; float: left;">
    <h1 class="title"><?php echo MENU_AGED_RECEIVABLES; ?></h1>
    <div id="aged_receivables" style="width: 450px; height: 300px; margin: 0 auto"></div>
</div>
<div style="width:49%; float: right;">
    <h1 class="title"><?php echo MENU_AGED_PAYABLES; ?></h1>
    <div id="aged_payables" style="width: 450px; height: 300px; margin: 0 auto"></div>
</div>
<div style="clear: both;"></div>
<h1 class="title"><?php echo MENU_REVENUE_YEAR_TO_DATE; ?></h1>
<div id="container" style="width: 900px; height: 400px; margin: 0 auto"></div>
<br />
<?php } ?>
<?php if ($moduleSales) { ?>
<h1 class="title"><?php echo MENU_CUSTOMERS_WHO_OWE_MONEY; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 100px !important;"><?php echo SALES_ORDER_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
        <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_TYPE; ?></th>
        <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
        <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?> ($)</th>
        <th style="width: 100px !important;"><?php echo REPORT_DUE_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_REMAINING; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS 
                            sales_invoices.id,
                            sales_invoices.order_date AS order_date,
                            sales_invoices.so_code AS code,
                            CONCAT_WS(' ', customers.firstname, customers.lastname) AS customer_name,
                            CASE sales_invoices.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfiled' END AS status,
                            sales_invoices.is_pos AS is_pos,
                            sales_invoices.total_amount-IFNULL(sales_invoices.discount,0)+IFNULL(sales_invoices.mark_up,0) AS total_amount,
                            sales_invoices.balance AS balance,
                            IFNULL((SELECT due_date FROM sales_order_receipts WHERE sales_invoice_id=sales_invoices.id ORDER BY id DESC LIMIT 1),sales_invoices.due_date) AS due_date,
                            DATEDIFF(IFNULL((SELECT due_date FROM sales_order_receipts WHERE sales_invoice_id=sales_invoices.id ORDER BY id DESC LIMIT 1),sales_invoices.due_date),CURDATE()) AS remaining
                        FROM sales_invoices
                            LEFT JOIN customers ON customers.id = sales_invoices.customer_id
                        WHERE  sales_invoices.balance > 0 AND sales_invoices.status > 0
                        ORDER BY  sales_invoices.so_code DESC LIMIT 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo $r['order_date']; ?></td>
        <td><a href="" class="btnPrintSalesOrderInvoice" rel="<?php echo $r['id']; ?>"><?php echo $r['code']; ?></a></td>
        <td><?php echo $r['customer_name']; ?></td>
        <td><?php echo $r['status']; ?></td>
        <td>
            <?php
                if($r['is_pos']){
                    echo "POS";
                } else{
                    echo "លក់ដុំ";
                }
            ?>
        </td>
        <td style="text-align: right;"><?php echo number_format($r['total_amount'],2); ?></td>
        <td style="text-align: right;"><?php echo number_format($r['balance'],2); ?></td>
        <td><?php echo $r['due_date']; ?></td>
        <td><?php echo $r['remaining']; ?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="10" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<?php } ?>
<?php if ($modulePurchase) { ?>
<h1 class="title"><?php echo MENU_VENDORS_TO_PAY; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_ORDER_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_PO_NUMBER; ?></th>
        <th style="width: 200px !important;"><?php echo TABLE_LOCATION; ?></th>
        <th><?php echo TABLE_VENDOR; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
        <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
        <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?> ($)</th>
        <th style="width: 100px !important;"><?php echo REPORT_DUE_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_REMAINING; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS 
                            pr.id,
                            pr.order_date AS order_date,
                            pr.po_code AS code,
                            loc.name AS loc_name,
                            v.name AS v_name,
                            pr.status AS status,
                            pr.total_amount AS total_amount,
                            pr.balance AS balance,
                            IFNULL((SELECT due_date FROM pvs WHERE purchase_bill_id=pr.id ORDER BY id DESC LIMIT 1),pr.due_date) AS due_date,
                            DATEDIFF(IFNULL((SELECT due_date FROM pvs WHERE purchase_bill_id=pr.id ORDER BY id DESC LIMIT 1),pr.due_date),CURDATE()) AS remaining
                        FROM purchase_bills AS pr 
                            INNER JOIN `locations` AS loc ON pr.location_id = loc.id
                            INNER JOIN `vendors` AS v ON pr.vendor_id = v.id
                        WHERE  pr.balance > 0 AND pr.status > 0 ORDER BY pr.id DESC LIMIT 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo $r['order_date']; ?></td>
        <td><a href="" class="btnPrintPurchaseOrderInvoice" rel="<?php echo $r['id']; ?>"><?php echo $r['code']; ?></a></td>
        <td><?php echo $r['loc_name']; ?></td>
        <td><?php echo $r['v_name']; ?></td>
        <td style="text-align: right;"><?php echo number_format($r['total_amount'],2); ?></td>
        <td style="text-align: right;"><?php echo number_format($r['balance'],2); ?></td>
        <td>
            <?php 
            switch ($r['status']) {
                case 1:
                    echo 'Issued';
                    break;
                case 2:
                    echo 'Partial';
                    break;
                case 3:
                    echo 'Fulfilled';
                    break;
            }
            ?>
        </td>
        <td><?php echo $r['due_date']; ?></td>
        <td><?php echo $r['remaining']; ?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="10" class="dataTables_empty"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<?php } ?>
<?php if ($moduleSales) { ?>
<h1 class="title"><?php echo MENU_OPEN_sales_invoices; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 100px !important;"><?php echo SALES_ORDER_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
        <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_TYPE; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                            sales_invoices.id,
                            sales_invoices.order_date AS order_date,
                            sales_invoices.so_code AS code,
                            CONCAT_WS(' ', customers.firstname, customers.lastname) AS customer_name,
                            CASE sales_invoices.status WHEN 0 THEN 'Void' WHEN 1 THEN 'Issued' WHEN 2 THEN 'Fulfiled' END AS status,
                            sales_invoices.is_pos AS is_pos
                        FROM sales_invoices
                            LEFT JOIN customers ON customers.id = sales_invoices.customer_id
                        WHERE  sales_invoices.status IN (1)
                        ORDER BY  sales_invoices.so_code DESC LIMIT 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo $r['order_date']; ?></td>
        <td><a href="" class="btnPrintSalesOrderInvoice" rel="<?php echo $r['id']; ?>"><?php echo $r['code']; ?></a></td>
        <td><?php echo $r['customer_name']; ?></td>
        <td><?php echo $r['status']; ?></td>
        <td>
            <?php
                if($r['is_pos']){
                    echo "POS";
                } else{
                    echo "លក់ដុំ";
                }
            ?>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="6" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<?php } ?>
<?php if ($modulePurchase) { ?>
<h1 class="title"><?php echo MENU_OPEN_purchase_bills; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_ORDER_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_PO_NUMBER; ?></th>
        <th style="width: 200px !important;"><?php echo TABLE_LOCATION; ?></th>
        <th><?php echo TABLE_VENDOR; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                            pr.id,
                            pr.order_date AS order_date,
                            pr.po_code AS code,
                            loc.name AS loc_name,
                            v.name AS v_name,
                            pr.status AS status
                        FROM purchase_bills AS pr
                            INNER JOIN `locations` AS loc ON pr.location_id = loc.id
                            INNER JOIN `vendors` AS v ON pr.vendor_id = v.id
                        WHERE  pr.status IN (1,2) ORDER BY pr.id DESC LIMIT 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo $r['order_date']; ?></td>
        <td><a href="" class="btnPrintPurchaseOrderInvoice" rel="<?php echo $r['id']; ?>"><?php echo $r['code']; ?></a></td>
        <td><?php echo $r['loc_name']; ?></td>
        <td><?php echo $r['v_name']; ?></td>
        <td>
            <?php
            switch ($r['status']) {
                case 1:
                    echo 'Issued';
                    break;
                case 2:
                    echo 'Partial';
                    break;
                case 3:
                    echo 'Fulfilled';
                    break;
            }
            ?>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="9" class="dataTables_empty"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<?php } ?>
<?php if ($moduleTransfer) { ?>
<h1 class="title"><?php echo OPEN_TRANSFER_ORDERS; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_TO_NUMBER; ?></th>
        <th><?php echo UOM_FROM; ?></th>
        <th><?php echo UOM_TO; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_ORDER_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_FULFILLMENT_DATE; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                            id,
                            to_code,
                            (SELECT name FROM locations WHERE id=from_location_id) AS loc_from,
                            (SELECT name FROM locations WHERE id=to_location_id) AS loc_to,
                            order_date,
                            fulfillment_date,
                            status
                        FROM transfer_orders
                        WHERE status != 0 AND status!=3
                        ORDER BY id DESC LIMIT 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><a href="" class="btnPrintTOInvoice" rel="<?php echo $r['id']; ?>"><?php echo $r['to_code']; ?></a></td>
        <td><?php echo $r['loc_from']; ?></td>
        <td><?php echo $r['loc_to']; ?></td>
        <td><?php echo dateShort($r['order_date']); ?></td>
        <td><?php echo dateShort($r['fulfillment_date']); ?></td>
        <td>
            <?php
                switch($r['status']){
                    case 1:
                        echo 'Issued';
                        break;
                    case 2:
                        echo 'Partial';
                        break;
                    case 3:
                        echo 'Fulfilled';
                        break;
                }
            ?>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="7" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<br />
<?php } ?>
<?php if ($moduleProduct) { ?>
<h1 class="title"><?php echo MENU_PRODUCT_REORDER; ?></h1>
<table cellpadding="5" class="table" style="margin-bottom: 10px;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 200px !important;"><?php echo TABLE_CODE; ?></th>
        <th><?php echo TABLE_NAME; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_UOM; ?></th>
        <th style="width: 100px !important;"><?php echo TABLE_QTY_IN_STOCK; ?></th>
        <th style="width: 100px !important;"><?php echo GENERAL_REORDER_LEVEL; ?></th>
    </tr>
    <?php
    $sql = mysql_query("SELECT SQL_CALC_FOUND_ROWS
                            p.id,
                            p.code AS code,
                            p.name AS name,
                            (SELECT name FROM uoms WHERE id=p.price_uom_id) AS uom,
                            (SELECT SUM(i.qty) FROM inventories i
                                INNER JOIN locations lc ON lc.id=i.location_id
                                INNER JOIN user_locations ulc ON lc.id=ulc.location_id
                                WHERE i.product_id=p.id AND ulc.user_id = " .$user['User']['id']. "
                                GROUP BY i.product_id
                            ) AS qty, reorder_level
                        FROM   products p
                        WHERE p.is_active=1 AND (SELECT SUM(i.qty) FROM inventories i
                                                    INNER JOIN locations lc ON lc.id=i.location_id
                                                    INNER JOIN user_locations ulc ON lc.id=ulc.location_id
                                                    WHERE i.product_id=p.id AND ulc.user_id = " .$user['User']['id']. "
                                                    GROUP BY i.product_id
                                                ) <= reorder_level
                                            AND " . (!empty($companies) ? "company_id IN (" . implode(",", array_keys($companies)) . ")" : "0"). "
                        ORDER BY  p.id ASC
                        LIMIT 0, 10");
    if(@$num=mysql_num_rows($sql)){
        $index = 0;
        while($r=mysql_fetch_array($sql)){
    ?>
    <tr>
        <td class="first"><?php echo ++$index; ?></td>
        <td><?php echo $r['code']; ?></td>
        <td><?php echo $r['name']; ?></td>
        <td><?php echo $r['uom']; ?></td>
        <td><?php echo $r['qty']; ?></td>
        <td><?php echo $r['reorder_level']; ?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="6" class="dataTables_empty first"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<?php } ?>