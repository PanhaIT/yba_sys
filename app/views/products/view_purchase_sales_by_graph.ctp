<?php
include("includes/function.php");
$xAxis = "";
$datas = array();
$symbol = '$';
$xName = TABLE_TOTAL_VALUES;
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
        $datas['purchase'][$dateList] = 0;
        $datas['sales'][$dateList] = 0;
        $i++;
    }
    $sqlTrans = mysql_query("SELECT SUM(total_amount_purchase) AS purchase, SUM(total_amount_sales) AS sales, date FROM inventory_total_by_dates WHERE product_id = {$productId} AND date >= '{$dateInput[0]}' AND date <= '{$dateInput[1]}' GROUP BY date;");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            $datas['purchase'][$dateTrans] = $rowTrans['purchase'];
            $datas['sales'][$dateTrans] = $rowTrans['sales'];
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
        $datas['purchase'][$month] = 0;
        $datas['sales'][$month] = 0;
    }
    $dateInput = date("Y");
    $sqlTrans = mysql_query("SELECT SUM(total_amount_purchase) AS purchase, SUM(total_amount_sales) AS sales, MONTH(date) AS date FROM inventory_total_by_dates WHERE product_id = {$productId} AND YEAR(date) = '{$dateInput}' GROUP BY MONTH(date);");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = str_pad($rowTrans['date'], 2, '0', STR_PAD_LEFT);
            $datas['purchase'][$dateTrans] = $rowTrans['purchase'];
            $datas['sales'][$dateTrans] = $rowTrans['sales'];
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
        $datas['purchase'][$i] = 0;
        $datas['sales'][$i] = 0;
    }
    $dateInput = date("Y");
    $sqlTrans = mysql_query("SELECT SUM(total_amount_purchase) AS purchase, SUM(total_amount_sales) AS sales, MONTH(date) AS date FROM inventory_total_by_dates WHERE product_id = {$productId} AND YEAR(date) = '{$dateInput}' GROUP BY MONTH(date);");
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
            $datas['purchase'][$dateTrans] += $rowTrans['purchase'];
            $datas['sales'][$dateTrans] += $rowTrans['sales'];
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
        $datas['purchase'][$i] = 0;
        $datas['sales'][$i] = 0;
    }
    $sqlTrans = mysql_query("SELECT SUM(total_amount_purchase) AS purchase, SUM(total_amount_sales) AS sales, YEAR(date) AS date FROM inventory_total_by_dates WHERE product_id = {$productId} AND YEAR(date) >= '{$start}' AND YEAR(date) <= '{$yearNow}' GROUP BY YEAR(date);");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            $datas['purchase'][$dateTrans] = $rowTrans['purchase'];
            $datas['sales'][$dateTrans] = $rowTrans['sales'];
        }
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if(!empty($datas)){
        ?>
        $('#dvViewPurchaseSalesGraph').highcharts({
            chart: {
                type: '<?php echo $chart; ?>'
            },
            title: {
                text: '<?php echo TABLE_TOTAL_PURCHASE_SALES_BY_AMOUNT; ?>',
                x: -20 //center
            },
            xAxis: {
                categories: [<?php echo $xAxis; ?>]
            },
            yAxis: {
                title: {
                    text: '<?php echo $xName; ?>'
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
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
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
                    switch (strtolower($key)) {
                        case "purchase":
                            $name = TABLE_TOTAL_PURCHASE;
                            break;
                        case "sales":
                            $name = TABLE_TOTAL_SALES;
                            break;
                        default:
                            $name = "";
                    }
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
                        echo replaceThousand(number_format($val, 2));
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
        $("#filterPurchaseSalesGraph, #groupPurchaseSalesGraph, #chartPurchaseSalesGraph").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewPurchaseSalesByGraph/<?php echo $productId; ?>/"+$("#filterPurchaseSalesGraph").val()+"/"+$("#groupPurchaseSalesGraph").val()+"/"+$("#chartPurchaseSalesGraph").val(),
                beforeSend: function(){
                    $("#refreshPurchaseSalesGraph").hide();
                    $("#loadingPurchaseSalesGraph").show();
                    $("#viewPurchaseSalesGraph").html("Loading....");
                },
                success: function(result){
                    $("#refreshPurchaseSalesGraph").show();
                    $("#loadingPurchaseSalesGraph").hide();
                    $("#viewPurchaseSalesGraph").html(result);
                }
            });
        });
        
        $("#refreshPurchaseSalesGraph").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewPurchaseSalesByGraph/<?php echo $productId; ?>/"+$("#filterPurchaseSalesGraph").val()+"/"+$("#groupPurchaseSalesGraph").val()+"/"+$("#chartPurchaseSalesGraph").val(),
                beforeSend: function(){
                    $("#refreshPurchaseSalesGraph").hide();
                    $("#loadingPurchaseSalesGraph").show();
                    $("#viewPurchaseSalesGraph").html("Loading....");
                },
                success: function(result){
                    $("#refreshPurchaseSalesGraph").show();
                    $("#loadingPurchaseSalesGraph").hide();
                    $("#viewPurchaseSalesGraph").html(result);
                }
            });
        });
    });
</script>
<input type="hidden" value="" id="fromPurchaseSalesGraph" />
<input type="hidden" value="" id="toPurchaseSalesGraph" />
<div id="dvViewPurchaseSalesGraph" style="width: 100%; height: 400px; margin: 0 auto"></div>