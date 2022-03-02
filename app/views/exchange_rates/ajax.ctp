<?php
include("includes/function.php");
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$index = 0;
$sqlComCurrency = mysql_query("SELECT name, symbol FROM currency_centers WHERE id = ".$branch['Branch']['currency_center_id']);
$rowComCurrency = mysql_fetch_array($sqlComCurrency);
$sqlCurrency = mysql_query("SELECT branch_currencies.id AS id, branches.name AS com_name, currency_centers.name AS curr_name, currency_centers.symbol AS curr_symbol, branch_currencies.rate_to_sell AS rate_sell, branch_currencies.rate_to_change AS rate_change, branch_currencies.rate_purchase AS rate_purchase, branch_currencies.modified AS modified, branch_currencies.branch_id, branch_currencies.currency_center_id FROM branch_currencies INNER JOIN branches ON branches.id = branch_currencies.branch_id INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.is_active = 1 AND branch_currencies.branch_id = '".$branchId."' ORDER BY branch_currencies.id ASC");
if(mysql_num_rows($sqlCurrency)){    
    while($rowCurrency = mysql_fetch_array($sqlCurrency)){
?>
<tr>
    <td class="first"><?php echo ++$index; ?></td>
    <td><?php echo $rowCurrency['com_name']; ?></td>
    <td><?php echo $rowComCurrency['symbol']; ?></td>
    <td>1.00</td>
    <td><?php echo $rowCurrency['curr_symbol']; ?></td>
    <td><input type="text" id="rateSell<?php echo $index; ?>" class="rateSell" exrate="<?php echo $rowCurrency['id']; ?>" style="width: 90%;" value="<?php echo $rowCurrency['rate_sell']; ?>" readonly="readonly" /></td>
    <td><input type="text" id="rateChange<?php echo $index; ?>" class="rateChange" exrate="<?php echo $rowCurrency['id']; ?>" style="width: 90%;" value="<?php echo $rowCurrency['rate_change']; ?>" readonly="readonly" /></td>
    <td><input type="text" id="ratePurchase<?php echo $index; ?>" class="ratePurchase" exrate="<?php echo $rowCurrency['id']; ?>" style="width: 90%;" value="<?php echo $rowCurrency['rate_purchase']; ?>" readonly="readonly" /></td>
    <?php if($allowAdd){ ?> 
    <td>
        <a href="#" class="btnEditExchangeRate" rel="<?php echo $rowCurrency['id']; ?>"><img alt="Edit" onmouseover="Tip('<?php echo ACTION_EDIT; ?>')" src="<?php echo $this->webroot; ?>img/button/edit.png" /> <?php echo ACTION_EDIT; ?></a>
        <a href="#" class="btnSaveExchangeRate" rel="<?php echo $rowCurrency['id']; ?>" style="display: none;"><img alt="Save" onmouseover="Tip('<?php echo ACTION_EDIT; ?>')" src="<?php echo $this->webroot; ?>img/button/save.png" /> <?php echo ACTION_SAVE; ?></a>
        <span class="lblSaveExchangeRate" style="display: none;"><?php echo ACTION_LOADING; ?></span>
        <a href="#" class="btnViewExchangeHistory" com-id="<?php echo $rowCurrency['branch_id']; ?>" currency-center-id="<?php echo $rowCurrency['currency_center_id']; ?>" name="<?php echo $rowCurrency['curr_name']; ?> (<?php echo $rowCurrency['curr_symbol']; ?>)"><img alt="View" onmouseover="Tip(<?php echo ACTION_VIEW; ?>)" src="<?php echo $this->webroot; ?>img/button/view.png" /></a>
    </td>
    <?php } ?>
    <td><?php echo dateShort($rowCurrency['modified'], "d/m/Y H:i:s"); ?></td>
</tr>
<?php
    }
} else {
?>
<tr>
    <td colspan="10" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
</tr>
<?php
}
?>