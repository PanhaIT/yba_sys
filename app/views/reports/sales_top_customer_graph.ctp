<?php
include('includes/function.php');
?>
<div>
    <?php
    $msg = '';
    $post = implode(',', $_POST);
    $col  = explode(",", $post);
    $condition = '';
    if ($col[1] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= '"' . dateConvert($col[1]) . '" <= DATE(invoice.order_date)';
    }
    if ($col[2] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= '"' . dateConvert($col[2]) . '" >= DATE(invoice.order_date)';
    }
    $condition != '' ? $condition .= ' AND ' : '';
    if ($col[3] == '') {
        $condition .= 'invoice.status!=0';
    } else {
        $condition .= 'invoice.status=' . $col[3];
    }
    if ($col[4] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.company_id=' . $col[4];
    }else{
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
    }
    if ($col[5] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.branch_id=' . $col[5];
    }else{
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
    }
    if ($col[6] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice_detail.product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = '.$col[6].')';
    }

    if ($col[7] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice_detail.product_id = '.$col[7];
    }
    $break  = $col[8];
    $sortBy = $col[9];
    $viewBy = $col[10];
    if($viewBy == 1){
        $msgSort = 'Top';
    } else {
        $msgSort = 'Bottom';
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
        $index = 0;
        $customers = array();
        $pie   = array();
        $totalRecords = 0;
        $lblSymbol = "";
        $lblBy = "Total Invoice";
        $query=mysql_query("SELECT
                            invoice.customer_id AS cus_id,
                            1 AS total_invoice,
                            SUM(invoice_detail.total_price - invoice_detail.discount_amount) AS total_amount
                            FROM sales_orders AS invoice
                            INNER JOIN sales_order_details AS invoice_detail ON invoice.id = invoice_detail.sales_order_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, invoice.customer_id
                            UNION ALL
                            SELECT
                            invoice.customer_id AS cus_id,
                            -1 AS total_invoice,
                            SUM(invoice_detail.total_price - invoice_detail.discount_amount) * -1 AS total_amount
                            FROM credit_memos AS invoice
                            INNER JOIN credit_memo_details AS invoice_detail ON invoice.id = invoice_detail.credit_memo_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, invoice.customer_id");
        while($data=mysql_fetch_array($query)){
            if (array_key_exists($data['cus_id'], $customers)) {
                if($sortBy == 1){
                    $customers[$data['cus_id']]['total'] += $data['total_invoice'];
                } else {
                    $customers[$data['cus_id']]['total'] += $data['total_amount'];
                }
            } else {
                if($sortBy == 1){
                    $customers[$data['cus_id']]['total'] = $data['total_invoice'];
                } else {
                    $lblSymbol = " $";
                    $lblBy = " Amounts";
                    $customers[$data['cus_id']]['total'] = $data['total_amount'];
                }
            }
        }
        // Sort By Value ASC/DESC
        if($viewBy == 1){ // DESC
            arsort($customers);
        } else { // ASC
            asort($customers);
        }
        foreach($customers AS $key => $value){
            if($index == $break){
                break;
            }
            if($value['total'] > 0){
                $sqlCustomer = mysql_query("SELECT customer_code, name FROM customers WHERE id = ".$key);
                $rowCustomer = mysql_fetch_array($sqlCustomer);
                $pie[$index]['name'] = $rowCustomer['customer_code']." - ".$rowCustomer['name']." (Total: ".number_format($value['total'], 2).$lblSymbol.")";
                $pie[$index]['y']    = $value['total'];
                $totalRecords       += $value['total'];
                $index++;
            }
        }
        ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#salesTopPie').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '<b style="font-size: 14px; font-weight: bold;"><?php echo "Sales ".$msgSort." ".$break." Customers By ".$lblBy." (".REPORT_FROM.': '.$_POST['date_from']." to ".REPORT_TO.': '.$_POST['date_to'].")"; ?></b>'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Percentage',
                    colorByPoint: true,
                    data: [
                        <?php
                        $percent = 0;
                        for($i = 0; $i < sizeof($pie); $i++) {
                            if($i == (sizeof($pie) - 1)){
                                $y = 100 - $percent;
                            } else {
                                $y = ($pie[$i]['y'] / $totalRecords) * 100;
                                $percent += replaceThousand(number_format($y, 2));
                            }
                        ?>
                        {
                            name: "<?php echo htmlspecialchars($pie[$i]['name']); ?>",
                            y: <?php echo replaceThousand(number_format($y, 2)); ?>
                        },
                        <?php
                        }
                        ?>
                          ]
                }]
            });
        });
    </script>
    <div id="salesTopPie" style="min-width: 500px; height: 500px; max-width: 1000px; margin: 0 auto"></div>
</div>
<div style="clear: both;"></div>