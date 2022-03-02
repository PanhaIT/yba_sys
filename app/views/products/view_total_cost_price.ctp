<?php
$dateNow = date("Y-m-d");
// Total Cost
$sqlCost = mysql_query("SELECT COUNT(id) FROM purchase_receives WHERE DATE(created) = '".$dateNow."' AND status = 1 GROUP BY product_id");
$rowCost = mysql_fetch_array($sqlCost);
$totalCost = $rowCost[0]>0?$rowCost[0]:0;
// Total Price
$sqlPrice = mysql_query("SELECT COUNT(id) FROM product_prices WHERE DATE(created) = '".$dateNow."' GROUP BY product_id");
$rowPrice = mysql_fetch_array($sqlPrice);
$totalPrice = $rowPrice[0]>0?$rowPrice[0]:0;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#dvViewTotalCostPrice').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Total Changing Cost & Price'
            },
            subtitle: {
                text: null
            },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Changing'
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Total: <b>{point.y:.1f}</b>'
            },
            series: [{
                name: 'Changing',
                data: [
                    ['Cost', <?php echo $totalCost; ?>],
                    ['Price', <?php echo $totalPrice; ?>]
                ],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.1f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
        
        $("#refreshTotalCostPrice").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewTotalCostPrice/",
                beforeSend: function(){
                    $("#refreshTotalCostPrice").hide();
                    $("#loadingTotalCostPrice").show();
                    $("#totalCostPriceView").html("Loading....");
                },
                success: function(result){
                    $("#refreshTotalCostPrice").show();
                    $("#loadingTotalCostPrice").hide();
                    $("#totalCostPriceView").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewTotalCostPrice" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>