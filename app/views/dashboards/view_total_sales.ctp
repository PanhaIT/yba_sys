<?php
include("includes/function.php");
$condtion = "sales.company_id IN (SELECT company_id FROM user_companies WHERE user_id = {$user['User']['id']}) AND ";
if($branch != 'all'){
    $condtion .= "sales.branch_id = ".$branch." AND ";
} else {
    $condtion .= "sales.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']}) AND ";
}
$xAxis = "";
$symbol = '$';
$datas = array();
if($group == 1){
    $dateInput = getDateByDateRange($dateRange);
    $dateLists = listDays($dateInput[0], $dateInput[1]);
    $i = 1;
    $count = count($dateLists);
    foreach($dateLists AS $dateList){
        if($i > 1 && $i <= $count){
            $xAxis .= ",";
        }
        $name = date("d/M", strtotime($dateList));
        $xAxis .= "'{$name}'";
        $datas['total_sales'][$dateList] = 0;
        $i++;
    }
//    $sqlTrans = mysql_query("SELECT SUM((sales.s_total_amount - sales.s_total_discount + sales.s_total_vat) + (sales.p_total_amount - sales.p_total_discount + sales.p_total_vat) - (sales.c_total_amount - sales.c_total_discount + sales.c_total_mark_up + sales.c_total_vat)) AS sales, sales.date FROM report_sales_by_days AS sales WHERE {$condtion}sales.date >= '{$dateInput[0]}' AND sales.date <= '{$dateInput[1]}' GROUP BY sales.date;");
//    if(mysql_num_rows($sqlTrans)){
//        while($rowTrans = mysql_fetch_array($sqlTrans)){
//            $dateTrans = $rowTrans['date'];
//            $datas['total_sales'][$dateTrans] = $rowTrans['sales'];
//        }
//    }
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            sales.order_date AS date
                            FROM sales_invoices AS sales
                            WHERE ".$condtion."sales.order_date >= '{$dateInput[0]}' AND sales.order_date <= '{$dateInput[1]}' AND sales.status > 0 GROUP BY sales.order_date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            sales.order_date AS date
                            FROM sales_returns AS sales
                            WHERE ".$condtion."sales.order_date >= '{$dateInput[0]}' AND sales.order_date <= '{$dateInput[1]}' AND sales.status > 0 GROUP BY sales.order_date
                            ORDER BY date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            if (array_key_exists($dateTrans, $datas['total_sales'])){
                $datas['total_sales'][$dateTrans] += $rowTrans['total_amount'];
            } else {
                $datas['total_sales'][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
} else if ($group == 2){
    for($i = 1; $i <= 12; $i++){
        if($i > 1 && $i <= 12){
            $xAxis .= ",";
        }
        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
        $dateList = date("Y")."-".$month."-01";
        $name = date("M", strtotime($dateList));
        $xAxis .= "'{$name}'";
        $datas['total_sales'][$month] = 0;
    }
    $dateInput = date("Y");
//    $sqlTrans = mysql_query("SELECT SUM((sales.s_total_amount - sales.s_total_discount + sales.s_total_vat) + (sales.p_total_amount - sales.p_total_discount + sales.p_total_vat) - (sales.c_total_amount - sales.c_total_discount + sales.c_total_mark_up + sales.c_total_vat)) AS sales, sales.month AS date FROM report_sales_by_months AS sales WHERE {$condtion}sales.year = '{$dateInput}' GROUP BY sales.month;");
//    if(mysql_num_rows($sqlTrans)){
//        while($rowTrans = mysql_fetch_array($sqlTrans)){
//            $dateTrans = str_pad($rowTrans['date'], 2, '0', STR_PAD_LEFT);
//            $datas['total_sales'][$dateTrans] = $rowTrans['sales'];
//        }
//    }
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            MONTH(sales.order_date) AS date
                            FROM sales_invoices AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            MONTH(sales.order_date) AS date
                            FROM sales_returns AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY date
                            ORDER BY date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = str_pad($rowTrans['date'], 2, '0', STR_PAD_LEFT);
            if (array_key_exists($dateTrans, $datas['total_sales'])){
                $datas['total_sales'][$dateTrans] += $rowTrans['total_amount'];
            } else {
                $datas['total_sales'][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
} else if($group == 3){
    for($i = 1; $i <= 4; $i++){
        if($i > 1 && $i <= 4){
            $xAxis .= ",";
        }
        switch (strtolower($i)) {
            case 1:
                $name = TABLE_QUARTER_ONE;
                break;
            case 2:
                $name = TABLE_QUARTER_TWO;
                break;
            case 3:
                $name = TABLE_QUARTER_THREE;
                break;
            case 4:
                $name = TABLE_QUARTER_FOUR;
                break;
            default:
                $name = "";
        }
        $xAxis .= "'{$name}'";
        $datas['total_sales'][$i] = 0;
    }
    $dateInput = date("Y");
//    $sqlTrans = mysql_query("SELECT SUM((sales.s_total_amount - sales.s_total_discount + sales.s_total_vat) + (sales.p_total_amount - sales.p_total_discount + sales.p_total_vat) - (sales.c_total_amount - sales.c_total_discount + sales.c_total_mark_up + sales.c_total_vat)) AS sales, sales.month AS date FROM report_sales_by_months AS sales WHERE {$condtion}sales.year = '{$dateInput}' GROUP BY sales.month;");
//    if(mysql_num_rows($sqlTrans)){
//        while($rowTrans = mysql_fetch_array($sqlTrans)){
//            if($rowTrans['date'] >= 1 && $rowTrans['date'] <= 3){
//                $dateTrans = 1;
//            } else if($rowTrans['date'] >= 4 && $rowTrans['date'] <= 6){
//                $dateTrans = 2;
//            } else if($rowTrans['date'] >= 7 && $rowTrans['date'] <= 9){
//                $dateTrans = 3;
//            } else if($rowTrans['date'] >= 10 && $rowTrans['date'] <= 12){
//                $dateTrans = 4;
//            }
//            $datas['total_sales'][$dateTrans] += $rowTrans['sales'];
//        }
//    }
    
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            MONTH(sales.order_date) AS date
                            FROM sales_invoices AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            MONTH(sales.order_date) AS date
                            FROM sales_returns AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY date
                            ORDER BY date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            if($rowTrans['date'] >= 1 && $rowTrans['date'] <= 3){
                $dateTrans = 1;
            } else if($rowTrans['date'] >= 4 && $rowTrans['date'] <= 6){
                $dateTrans = 2;
            } else if($rowTrans['date'] >= 7 && $rowTrans['date'] <= 9){
                $dateTrans = 3;
            } else if($rowTrans['date'] >= 10 && $rowTrans['date'] <= 12){
                $dateTrans = 4;
            }
            if (array_key_exists($dateTrans, $datas['total_sales'])){
                $datas['total_sales'][$dateTrans] += $rowTrans['total_amount'];
            } else {
                $datas['total_sales'][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
} else if($group == 4){
    $sqlYearStart = mysql_query("SELECT YEAR(date) FROM inventories GROUP BY YEAR(date) ORDER BY YEAR(date) ASC LIMIT 1;");
    if(mysql_num_rows($sqlYearStart)){
        $rowYearStart = mysql_fetch_array($sqlYearStart);
        $start = $rowYearStart[0];
    } else {
        $start = date("Y");
    }
    $yearNow = date("Y");
    for($i = $start; $i <= $yearNow; $i++){
        if($i > $start && $i <= $yearNow){
            $xAxis .= ",";
        }
        $name = $i;
        $xAxis .= "'{$name}'";
        $datas['total_sales'][$i] = 0;
    }
//    $sqlTrans = mysql_query("SELECT SUM((sales.s_total_amount - sales.s_total_discount + sales.s_total_vat) + (sales.p_total_amount - sales.p_total_discount + sales.p_total_vat) - (sales.c_total_amount - sales.c_total_discount + sales.c_total_mark_up + sales.c_total_vat)) AS sales, sales.year AS date FROM report_sales_by_months AS sales WHERE {$condtion}sales.year >= '{$start}' AND sales.year <= '{$yearNow}' GROUP BY sales.year;");
//    if(mysql_num_rows($sqlTrans)){
//        while($rowTrans = mysql_fetch_array($sqlTrans)){
//            $dateTrans = $rowTrans['date'];
//            $datas['total_sales'][$dateTrans] = $rowTrans['sales'];
//        }
//    }
    
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            YEAR(sales.order_date) AS date
                            FROM sales_invoices AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) >= '{$start}' AND YEAR(sales.order_date) <= '{$yearNow}' AND sales.status > 0 GROUP BY date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            YEAR(sales.order_date) AS date
                            FROM sales_returns AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) >= '{$start}' AND YEAR(sales.order_date) <= '{$yearNow}' AND sales.status > 0 GROUP BY date
                            ORDER BY date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            if (array_key_exists($dateTrans, $datas['total_sales'])){
                $datas['total_sales'][$dateTrans] += $rowTrans['total_amount'];
            } else {
                $datas['total_sales'][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if(!empty($datas)){
        ?>
        $('#dvViewTotalSales').highcharts({
            chart: {
                type: '<?php echo $chart; ?>'
            },
            title: {
                text: '<b style="font-size: 15px;"><?php echo TABLE_TOTAL_SALES." ".date("Y"); ?></b>'
            },
            xAxis: {
                categories: [<?php echo $xAxis; ?>]
            },
            yAxis: {
                title: {
                    text: '<?php echo GENERAL_AMOUNT; ?> ($)'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>'+'<td style="padding:0"><b>{point.y:.2f} <?php echo $symbol; ?></b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [
                <?php
                $l=1;
                $lengthAct = count($datas);
                foreach($datas AS $key => $data){
                    $name = $key;
                    if($l > 1 && $l <= $lengthAct){
                        echo ",";
                    }
                ?>
                {
                name: '<?php echo $name; ?>',
                data: [<?php
                    $j = 1;
                    ksort($data);
                    $lengthVal = count($data);
                    foreach($data AS $val){
                        if($j > 1 && $j <= $lengthVal){
                            echo ",";
                        }
                        echo $val;
                        $j++;
                    }
                    ?>]}
                <?php
                    $l++;
                }
                ?>
            ]
        });
        <?php
        }
        ?>
        $("#filterTotalSales, #groupTotalSales, #chartTotalSales").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewTotalSales/"+$("#filterBranchTotalSales").val()+"/"+$("#filterTotalSales").val()+"/"+$("#groupTotalSales").val()+"/"+$("#chartTotalSales").val(),
                beforeSend: function(){
                    $("#refreshTotalSales").hide();
                    $("#loadingTotalSales").show();
                    $("#TotalSalesView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshTotalSales").show();
                    $("#loadingTotalSales").hide();
                    $("#TotalSalesView").html(result);
                }
            });
        });
        
        $("#refreshTotalSales").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewTotalSales/"+$("#filterBranchTotalSales").val()+"/"+$("#filterTotalSales").val()+"/"+$("#groupTotalSales").val()+"/"+$("#chartTotalSales").val(),
                beforeSend: function(){
                    $("#refreshTotalSales").hide();
                    $("#loadingTotalSales").show();
                    $("#TotalSalesView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshTotalSales").show();
                    $("#loadingTotalSales").hide();
                    $("#TotalSalesView").html(result);
                }
            });
        });
    });
</script>
<input type="hidden" value="" id="fromTotalSales" />
<input type="hidden" value="" id="toTotalSales" />
<div id="dvViewTotalSales" style="width: 100%; height: 300px; margin: 0 auto"></div>