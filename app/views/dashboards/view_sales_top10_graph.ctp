<?php
include("includes/function.php");
$datas  = array();
$dateInput = getDateByDateRange($dateRange);

$isEmpty = 0;
$totalSalesTop10 = 0;
$condition  = "invoice.status > 0 AND '".$dateInput[0]."' <= DATE(invoice.order_date) AND '".$dateInput[1]."' >= DATE(invoice.order_date)";
$condition .= ' AND invoice.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
if($branch != 'all'){
    $condition .= " AND invoice.branch_id = ".$branch;
} else {
    $condition .= " AND invoice.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})";
}
$sqlTrans  = mysql_query("SELECT
                            sales_invoice_details.product_id AS p_id,
                            CONCAT_WS(' ', products.code, products.name) AS product_name,
                            SUM((sales_invoice_details.qty + sales_invoice_details.qty_free) * sales_invoice_details.conversion) AS total_qty
                            FROM sales_invoices AS invoice
                            INNER JOIN sales_invoice_details ON invoice.id = sales_invoice_details.sales_invoice_id
                            INNER JOIN products ON products.id = sales_invoice_details.product_id
                            WHERE ". $condition . "
                            GROUP BY sales_invoice_details.product_id
                            UNION ALL
                            SELECT
                            sales_return_details.product_id AS p_id,
                            CONCAT_WS(' ', products.code, products.name) AS product_name,
                            SUM((sales_return_details.qty + sales_return_details.qty_free) * sales_return_details.conversion) * -1 AS total_qty
                            FROM sales_returns AS invoice
                            INNER JOIN sales_return_details ON invoice.id = sales_return_details.sales_return_id
                            INNER JOIN products ON products.id = sales_return_details.product_id
                            WHERE ". $condition . "
                            GROUP BY sales_return_details.product_id");
if(mysql_num_rows($sqlTrans)){
    $items = array();
    while($rowTrans = mysql_fetch_array($sqlTrans)){
        if (array_key_exists($rowTrans['p_id'], $items)) {
            $items[$rowTrans['p_id']]['total'] += $rowTrans['total_qty'];
        } else {
            $items[$rowTrans['p_id']]['name']  = mysql_real_escape_string($rowTrans['product_name']);
            $items[$rowTrans['p_id']]['total'] = $rowTrans['total_qty'];
        }
    }
    arraySortBy('total', $items, 'desc');
    $index = 0;
    foreach($items AS $value){
        if($index == 10){
            break;
        }
        if($value['total'] > 0){
            $datas[$index]['name']  = $value['name'];
            $datas[$index]['total'] = $value['total'];
            $index++;
        }
    }
} else {
    $isEmpty = 1;
}
if(empty($datas)){
    for($i=0; $i<1; $i++){
        $datas[$i]['name']  = 'SalesTop10';
        $datas[$i]['total'] = 1;
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if(!empty($datas)){
        ?>
        Highcharts.chart('dvViewSalesTop10Graph', {
            chart: {
                type: 'pie'
            },
            title: {
                text: '<b style="font-size: 15px;">Sales Top 10 Items</b>'
            },
            subtitle: {
                text: '<?php echo preg_replace("/(?<=[a-zA-Z])(?=[A-Z])/", " ", $dateRange); ?>'
            },
            plotOptions: {
                pie: {
                    <?php
                    if($isEmpty == 0){
                    ?>
                    allowPointSelect: true,
                    cursor: 'pointer',
                    <?php
                    }
                    ?>
                    dataLabels: {
                        <?php
                        if($isEmpty == 1){
                        ?>   
                        enabled: false,
                        <?php
                        } else {
                        ?>
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: 'black'
                        }
                        <?php
                        }
                        ?>
                    }
                }
            },
            tooltip: {
                headerFormat: '',
                <?php
                if($isEmpty == 1){
                ?>   
                pointFormat: '0'
                <?php
                } else {
                ?>
                pointFormat: '{point.name}: <b>{point.percentage:.1f} %</b>'
                <?php
                }
                ?>
            },
            series: [{
                name: 'SalesTop10',
                data: [
                <?php
                $j = 1;
                $lengthVal = count($datas);
                foreach($datas AS $data){
                    if($j > 1 && $j <= $lengthVal){
                        echo ",";
                    }
                ?>
                {
                    name: "<?php echo $data['name']; ?>",
                    y: <?php echo $data['total']; ?>
                }
                <?php
                    $j++;
                }
                ?>
                ],
                size: '80%',
                innerSize: '60%'
            }]
        });
        <?php
        }
        ?>
        $("#filterSalesTop10").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewSalesTop10Graph/"+$("#filterBranchSalesTop10").val()+"/"+$("#filterSalesTop10").val(),
                beforeSend: function(){
                    $("#refreshSalesTop10").hide();
                    $("#loadingSalesTop10").show();
                    $("#salesTop10View").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshSalesTop10").show();
                    $("#loadingSalesTop10").hide();
                    $("#salesTop10View").html(result);
                }
            });
        });
        
        $("#refreshSalesTop10").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewSalesTop10Graph/"+$("#filterBranchSalesTop10").val()+"/"+$("#filterSalesTop10").val(),
                beforeSend: function(){
                    $("#refreshSalesTop10").hide();
                    $("#loadingSalesTop10").show();
                    $("#salesTop10View").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshSalesTop10").show();
                    $("#loadingSalesTop10").hide();
                    $("#salesTop10View").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewSalesTop10Graph" style="width: 100%; height: 350px; margin: 0 auto"></div>