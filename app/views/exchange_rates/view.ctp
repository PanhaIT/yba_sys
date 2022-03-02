<table width="100%" class="table" cellspacing="0">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo MENU_BRANCH; ?></th>
            <th><?php echo REPORT_FROM; ?></th>
            <th style="width: 100px;"><?php echo TABLE_RATE; ?></th>
            <th><?php echo REPORT_TO; ?></th>
            <th style="width: 300px;"><?php echo TABLE_RATE_FOR_SELL; ?></th>
            <th style="width: 300px;"><?php echo TABLE_RATE_FOR_CHANGE; ?></th>
            <th style="width: 300px;"><?php echo TABLE_RATE_FOR_PURCHASE; ?></th>
            <th><?php echo TABLE_MODIFIED; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            include("includes/function.php");
            $index = 0;
            if(!empty($this->data)){    
            foreach ($this->data as $exchangeRate) {
                $sqlComCurrency = mysql_query("SELECT name, symbol FROM currency_centers WHERE id = ".$exchangeRate['branches']['currency_center_id']);
                $rowComCurrency = mysql_fetch_array($sqlComCurrency);
                ?>
                    <tr>
                        <td class="first"><?php echo ++$index; ?></td>
                        <td><?php echo $exchangeRate['branches']['name']; ?></td>
                        <td><?php echo $rowComCurrency['symbol']; ?></td>
                        <td>1.00</td>
                        <td><?php echo $exchangeRate['currency_centers']['symbol']; ?></td>
                        <td><?php echo $exchangeRate['ExchangeRate']['rate_to_sell']; ?></td>
                        <td><?php echo $exchangeRate['ExchangeRate']['rate_to_change']; ?></td>
                        <td><?php echo $exchangeRate['ExchangeRate']['rate_purchase']; ?></td>
                        <td><?php echo dateShort($exchangeRate['ExchangeRate']['modified'], "d/m/Y H:i:s"); ?></td>            
                    </tr>
                <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="9" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>