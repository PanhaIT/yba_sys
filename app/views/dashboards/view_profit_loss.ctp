<?php
include("includes/function.php");
$symbol = '$';
$dataRes = array();
$dataExs = array();
$dataPLs = array();
$xAxis = '';
for($i = 1; $i <= 12; $i++){
    if($i > 1 && $i <= 12){
        $xAxis .= ",";
    }
    $month    = str_pad($i, 2, '0', STR_PAD_LEFT);
    $dateList = date("Y")."-".$month."-01";
    $name   = date("M", strtotime($dateList));
    $xAxis .= "'{$name}'";
    $dataRes[$month] = 0;
    $dataExs[$month] = 0;
    $dataPLs[$month] = 0;
}

$tableName = "dashboard_profit_loss";
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE `{$tableName}` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`date` DATE NULL DEFAULT NULL,
	`company_id` INT(11) NULL DEFAULT NULL,
	`branch_id` INT(11) NULL DEFAULT NULL,
	`chart_account_id` INT(11) NULL DEFAULT NULL,
	`debit` DECIMAL(20,9) NULL DEFAULT NULL,
	`credit` DECIMAL(20,9) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`date`),
	INDEX `company` (`company_id`, `branch_id`),
	INDEX `chart_account_id` (`chart_account_id`))
        ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;;");
mysql_query("TRUNCATE $tableName");

if($branch != 'all'){
    $condition = "AND gld.branch_id = ".$branch;
} else {
    $condition = "AND gld.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})";
}

$queryCoa = mysql_query("SELECT SUM(IFNULL(debit, 0)) AS debit,SUM(IFNULL(credit, 0)) AS credit, date, chart_account_id, company_id, branch_id
                         FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                         WHERE gl.is_approve=1 AND gl.is_active=1 AND gld.company_id IN (SELECT company_id FROM user_companies WHERE user_id = {$user['User']['id']}) ".$condition." AND YEAR(date) = '" . date("Y") . "'
                         GROUP BY date,chart_account_id,company_id,branch_id");
while ($dataCoa = mysql_fetch_array($queryCoa)) {
    mysql_query("INSERT INTO $tableName (
                            date,
                            chart_account_id,
                            company_id,
                            branch_id,
                            debit,
                            credit
                        ) VALUES (
                            '" . $dataCoa['date'] . "',
                            " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['branch_id']) ? $dataCoa['branch_id'] : "NULL") . ",
                            " . $dataCoa['debit'] . ",
                            " . $dataCoa['credit'] . ")");
}

$isEmpty = 0;
$sqlOtherIncome = mysql_query("SELECT SUM(IFNULL(g.credit, 0) - IFNULL(g.debit, 0)) AS income, MONTH(g.date) AS month FROM {$tableName} AS g INNER JOIN chart_accounts ON chart_accounts.id = g.chart_account_id AND chart_accounts.chart_account_type_id = 14 WHERE 1 GROUP BY MONTH(date)");
while($rowOtherIncome = mysql_fetch_array($sqlOtherIncome)){
    $month = str_pad($rowOtherIncome['month'], 2, '0', STR_PAD_LEFT);
    $dataRes[$month] += $rowOtherIncome['income'];
    $dataPLs[$month] += $rowOtherIncome['income'];
}
$sqlRevenue = mysql_query("SELECT SUM(IFNULL(g.credit, 0) - IFNULL(g.debit, 0)) AS income, MONTH(g.date) AS month FROM {$tableName} AS g INNER JOIN chart_accounts ON chart_accounts.id = g.chart_account_id AND chart_accounts.chart_account_type_id = 11 WHERE 1 GROUP BY MONTH(date)");
while($rowRevenue = mysql_fetch_array($sqlRevenue)){
    $month = str_pad($rowRevenue['month'], 2, '0', STR_PAD_LEFT);
    $dataRes[$month] += $rowRevenue['income'];
    $dataPLs[$month] += $rowRevenue['income'];
}
$sqlCogs = mysql_query("SELECT SUM(IFNULL(g.debit, 0) - IFNULL(g.credit, 0)) AS cogs, MONTH(g.date) AS month FROM {$tableName} AS g INNER JOIN chart_accounts ON chart_accounts.id = g.chart_account_id AND chart_accounts.chart_account_type_id = 12 WHERE 1 GROUP BY MONTH(date)");
while($rowCogs = mysql_fetch_array($sqlCogs)){
    $month = str_pad($rowCogs['month'], 2, '0', STR_PAD_LEFT);
    $dataRes[$month] -= $rowCogs['cogs'];
    $dataPLs[$month] -= $rowCogs['cogs'];
}
$sqlExpense = mysql_query("SELECT SUM(IFNULL(g.debit, 0) - IFNULL(g.credit, 0)) AS expense, MONTH(g.date) AS month FROM {$tableName} AS g INNER JOIN chart_accounts ON chart_accounts.id = g.chart_account_id AND chart_accounts.chart_account_type_id = 13 WHERE 1 GROUP BY MONTH(date)");
while($rowExpense = mysql_fetch_array($sqlExpense)){
    $month = str_pad($rowExpense['month'], 2, '0', STR_PAD_LEFT);
    $dataExs[$month] += $rowExpense['expense'];
    $dataPLs[$month] -= $rowExpense['expense'];
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if(!empty($dataPLs)){
        ?>
        Highcharts.chart('dvViewProfitLoss', {
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: '<b style="font-size: 15px;"><?php echo MENU_PROFIT_AND_LOSS." ".date("Y"); ?></b>'
            },
            xAxis: [{
                categories: [<?php echo $xAxis; ?>]
            }],
            yAxis: [
            { // Primary yAxis
                labels: {
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                title: {
                    text: 'Amount ($)',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                }
            }],
            tooltip: {
                shared: true
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: [{
                name: 'Revenue',
                type: 'column',
                data: [<?php
                    $j = 1;
                    ksort($dataRes);
                    $lengthVal = count($dataRes);
                    foreach($dataRes AS $val){
                        if($j > 1 && $j <= $lengthVal){
                            echo ",";
                        }
                        echo replaceThousand(number_format($val, 2));
                        $j++;
                    }
                    ?>],
                tooltip: {
                    valueSuffix: ' $'
                },
                color: Highcharts.getOptions().colors[10]
            },
            {
                name: 'Expense',
                type: 'column',
                data: [<?php
                    $ex = 1;
                    ksort($dataExs);
                    $lengthEx = count($dataExs);
                    foreach($dataExs AS $val){
                        if($ex > 1 && $ex <= $lengthEx){
                            echo ",";
                        }
                        echo replaceThousand(number_format($val, 2));
                        $ex++;
                    }
                    ?>],
                tooltip: {
                    valueSuffix: ' $'
                },
                color: '#BF0B23'
            },
            {
                name: 'Profit Loss',
                type: 'spline',
                data: [<?php
                    $pl = 1;
                    ksort($dataPLs);
                    $lengthPL = count($dataPLs);
                    foreach($dataPLs AS $val){
                        if($pl > 1 && $pl <= $lengthPL){
                            echo ",";
                        }
                        echo replaceThousand(number_format($val, 2));
                        $pl++;
                    }
                    ?>],
                tooltip: {
                    valueSuffix: ' $'
                },
                color: '#01ae01'
            }]
        });
        <?php
        }
        ?>
        $("#filterProfitLoss").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewProfitLoss/"+$("#filterProfitLoss").val(),
                beforeSend: function(){
                    $("#refreshProfitLoss").hide();
                    $("#loadingProfitLoss").show();
                    $("#profitLossView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshProfitLoss").show();
                    $("#loadingProfitLoss").hide();
                    $("#profitLossView").html(result);
                }
            });
        });
        
        $("#refreshProfitLoss").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewProfitLoss/"+$("#filterProfitLoss").val(),
                beforeSend: function(){
                    $("#refreshProfitLoss").hide();
                    $("#loadingProfitLoss").show();
                    $("#profitLossView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshProfitLoss").show();
                    $("#loadingProfitLoss").hide();
                    $("#profitLossView").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewProfitLoss" style="width: 100%; height: 345px; margin: 0 auto"></div>