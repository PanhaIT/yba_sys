<?php
include("includes/function.php");
$symbol = '$';
$datas  = array();
$dateInput = getDateByDateRange($dateRange);
if($branch != 'all'){
    $condition = "gld.branch_id = ".$branch;
} else {
    $condition = "gld.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})";
}
$isEmpty = 0;
$totalExpense = 0;
$sqlTrans = mysql_query("SELECT SUM(debit) AS amount, chart_accounts.account_description AS name  FROM general_ledger_details AS gld INNER JOIN general_ledgers ON general_ledgers.id = gld.general_ledger_id AND general_ledgers.is_active = 1 AND general_ledgers.date >= '".$dateInput[0]."' AND general_ledgers.date <= '".$dateInput[1]."' INNER JOIN chart_accounts ON chart_accounts.id = gld.chart_account_id AND chart_accounts.chart_account_type_id = 13 WHERE ".$condition." GROUP BY gld.chart_account_id;");
if(mysql_num_rows($sqlTrans)){
    $i = 0;
    while($rowTrans = mysql_fetch_array($sqlTrans)){
        $datas[$i]['name']   = mysql_real_escape_string($rowTrans['name']);
        $datas[$i]['amount'] = $rowTrans['amount'];
        $totalExpense += $rowTrans['amount'];
        $i++;
    }
} else {
    $isEmpty = 1;
}
if(empty($datas)){
    for($i=0; $i<1; $i++){
        $datas[$i]['name']   = 'Expense';
        $datas[$i]['amount'] = 1;
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        <?php
        if(!empty($datas)){
        ?>
        Highcharts.chart('dvViewExpenseGraph', {
            chart: {
                type: 'pie'
            },
            title: {
                text: '<b style="font-size: 15px;"><?php echo MENU_EXPENSE; ?></b><br/><b style="font-size: 13px;">By Total Amount : <?php echo number_format($totalExpense, 2)." ".$symbol; ?></b>'
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
                        format: '<b>{point.name}</b>: {point.y} <?php echo $symbol; ?>',
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
                pointFormat: '{point.name}: <b>{point.y} <?php echo $symbol; ?></b>'
                <?php
                }
                ?>
            },
            series: [{
                name: 'Expense',
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
                    name: '<?php echo $data['name']; ?>',
                    y: <?php echo $data['amount']; ?>
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
        $("#filterExpenseGraph").unbind("change").change(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewExpenseGraph/"+$("#filterBranchExpenseGraph").val()+"/"+$("#filterExpenseGraph").val(),
                beforeSend: function(){
                    $("#refreshExpenseGraph").hide();
                    $("#loadingExpenseGraph").show();
                    $("#expenseGraphView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshExpenseGraph").show();
                    $("#loadingExpenseGraph").hide();
                    $("#expenseGraphView").html(result);
                }
            });
        });
        
        $("#refreshExpenseGraph").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewExpenseGraph/"+$("#filterBranchExpenseGraph").val()+"/"+$("#filterExpenseGraph").val(),
                beforeSend: function(){
                    $("#refreshExpenseGraph").hide();
                    $("#loadingExpenseGraph").show();
                    $("#expenseGraphView").html('<img src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; position: absolute; top:50%; margin-top:-5px" />');
                },
                success: function(result){
                    $("#refreshExpenseGraph").show();
                    $("#loadingExpenseGraph").hide();
                    $("#expenseGraphView").html(result);
                }
            });
        });
    });
</script>
<div id="dvViewExpenseGraph" style="width: 100%; height: 350px; margin: 0 auto"></div>