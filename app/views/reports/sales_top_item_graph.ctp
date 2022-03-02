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
        $condition .= 'invoice.customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id = '.$col[6].')';
    }

    if ($col[7] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.customer_id = '.$col[7];
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
        $items = array();
        $pie   = array();
        $totalRecords = 0;
        $lblSymbol = "";
        $lblBy = "Quantity";
        $query=mysql_query("SELECT
                            sales_order_details.product_id AS p_id,
                            SUM((sales_order_details.qty + sales_order_details.qty_free) * sales_order_details.conversion) AS total_qty,
                            SUM(sales_order_details.total_price - sales_order_details.discount_amount) AS total_amount
                            FROM sales_orders AS invoice
                            INNER JOIN sales_order_details ON invoice.id = sales_order_details.sales_order_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, sales_order_details.product_id
                            UNION ALL
                            SELECT
                            credit_memo_details.product_id AS p_id,
                            SUM((credit_memo_details.qty + credit_memo_details.qty_free) * credit_memo_details.conversion) * -1 AS total_qty,
                            SUM(credit_memo_details.total_price - credit_memo_details.discount_amount) * -1 AS total_amount
                            FROM credit_memos AS invoice
                            INNER JOIN credit_memo_details ON invoice.id = credit_memo_details.credit_memo_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, credit_memo_details.product_id");
        while($data=mysql_fetch_array($query)){
            if (array_key_exists($data['p_id'], $items)) {
                if($sortBy == 1){
                    $items[$data['p_id']]['total'] += $data['total_qty'];
                } else {
                    $items[$data['p_id']]['total'] += $data['total_amount'];
                }
            } else {
                if($sortBy == 1){
                    $items[$data['p_id']]['total'] = $data['total_qty'];
                } else {
                    $lblSymbol = " $";
                    $lblBy = " Amounts";
                    $items[$data['p_id']]['total'] = $data['total_amount'];
                }
            }
        }
        // Sort By Value ASC/DESC
        if($viewBy == 1){ // DESC
            arsort($items);
        } else { // ASC
            asort($items);
        }
        foreach($items AS $key => $value){
            if($index == $break){
                break;
            }
            if($value['total'] > 0){
                $sqlProduct = mysql_query("SELECT code, name FROM products WHERE id = ".$key);
                $rowProduct = mysql_fetch_array($sqlProduct);
                $pie[$index]['name'] = $rowProduct['code']." - ".$rowProduct['name']." (Total: ".number_format($value['total'], 2).$lblSymbol.")";
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
                    text: '<b style="font-size: 14px; font-weight: bold;"><?php echo "Sales ".$msgSort." ".$break." Items By ".$lblBy." (".REPORT_FROM.': '.$_POST['date_from']." to ".REPORT_TO.': '.$_POST['date_to'].")"; ?></b>'
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