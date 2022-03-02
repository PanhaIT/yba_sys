<?php 
$currencySymbol = "";
if($branch['Branch']['pos_currency_id'] != ''){
    $sqlCurrencyOther = mysql_query("SELECT branch_currencies.currency_center_id, IFNULL(branch_currencies.rate_to_sell,0), IFNULL(branch_currencies.exchange_rate_id,0), currency_centers.symbol FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.id = ".$branch['Branch']['pos_currency_id']);
    if(mysql_num_rows($sqlCurrencyOther)){
        $rowCurrencyOther = mysql_fetch_array($sqlCurrencyOther);
        $currencySymbol = $rowCurrencyOther[3];
    }
}     
?>
<div id="dynamic">
    <table class="table" cellspacing="0">
        <thead>
            <tr>
                <th colspan="5" style="text-align: center;"><?php echo TABLE_TOTAL_ADJUST_INFO; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="first"><?php echo TABLE_NO; ?></td>
                <td style="width: 120px !important; text-align: center;"><?php echo TABLE_DATE; ?></td>
                <td style="width: 120px !important; text-align: center;"><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?> (<?php echo $branch['CurrencyCenter']['symbol']; ?>)</td>
                <td style="width: 120px !important; text-align: center;"><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?> (<?php echo $currencySymbol; ?>)</td>
                <td style="width: 120px !important; text-align: center;"><?php echo GENERAL_DESCRIPTION; ?></td>
            </tr>
            <?php
                $index = 1;
                $queryGetAdj = mysql_query("SELECT created, total_adj, total_adj_other, description FROM shift_adjusts WHERE shift_id = '".$shiftId."'");
                if(mysql_num_rows($queryGetAdj)){
                    while($dataGetAdj = mysql_fetch_array($queryGetAdj)){
            ?>
            <tr>
                <td class="first" style="text-align: center;"><?php echo $index++; ?></td>
                <td style="text-align: center;"><?php echo date("H:i:s", strtotime($dataGetAdj[0])); ?></td>
                <td style="text-align: right;"><?php echo number_format($dataGetAdj[1], 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($dataGetAdj[2], 0); ?></td>
                <td><?php echo $dataGetAdj[3]; ?></td>
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
            
        </tbody>
    </table>
</div>