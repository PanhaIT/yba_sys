<?php
$rnd = rand();
$tbl = "tbl" . $rnd;
$btnPlusMinus = "btnPlusMinus" . $rnd;
$btnShowAll = "btnShowAll" . $rnd;
$btnHideAll = "btnHideAll" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#FixedAssetForm").validationEngine('detach');
        $("#FixedAssetForm").validationEngine('attach');
        $("#FixedAssetForm").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
        $("#FixedAssetForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#FixedAssetDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveFixedAsset").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $("#FixedAssetDate").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".txtSaveFixedAsset").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("button[type=submit]", $("#FixedAssetForm")).removeAttr('disabled');
                if(result=='duplicate'){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>This code is already taken, please change the code and save again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result=='error'){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else{
                    oCache.iCacheLower = -1;
                    oTableFixedAsset.fnDraw(false);
                    $(".btnBackFixedAsset").click();
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(event, ui){
                            $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
        // group expansion
        $("#<?php echo $tbl; ?> .group td:first-child").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $tbl; ?> .group td:first-child").css("cursor", "pointer");
        $("#<?php echo $tbl; ?> .group td:first-child").click(function(){
            if($("#<?php echo $tbl; ?> .groupDetail[chart_account_id=" + $(this).attr("chart_account_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $tbl; ?> .groupDetail[chart_account_id="+$(this).attr("chart_account_id")+"]").toggle();
        });
        $(".<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $tbl; ?> .groupDetail").show();
        });
        $(".<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $tbl; ?> .groupDetail").hide();
        });
    });
</script>
<style type="text/css">
    tr.highlight:hover {
        background-color: #F0F0F0;
    }
    form table#<?php echo $tbl; ?> input[type=text] {
        padding: 0px;
        width: 100%;
        letter-spacing: 0.05em;
        background-image: none;
        border: 0px;
    }
</style>
<table id="<?php echo $tbl; ?>" class="table_report">
    <tr>
        <th rowspan="2" style="width: 30px;">
            <a href="" class="<?php echo $btnHideAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' onmouseover="Tip('Hide All')" /></a>
            <a href="" class="<?php echo $btnShowAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/minus.gif' onmouseover="Tip('Show All')" /></a>
        </th>
        <th rowspan="2">Accounts</th>
        <th colspan="2">Balance</th>
        <th colspan="3">Journal Entry</th>
    </tr>
    <tr>
        <th>Accum Depr</th>
        <th>Last Posted</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Memo</th>
    </tr>
    <?php
    $queryAccounts=mysql_query("SELECT DISTINCT depr_account FROM fixed_assets WHERE is_active=1 AND is_depre=1 AND company_id='".$companyId."' AND branch_id='".$branchId."' AND date <= DATE('".$date."') ORDER BY date") or die(mysql_error());
    while($dataAccounts=mysql_fetch_array($queryAccounts)){
    ?>
    <tr class="group highlight" chart_account_id="<?php echo $dataAccounts[0]; ?>">
        <td colspan="2" chart_account_id="<?php echo $dataAccounts[0]; ?>">
            <input type="hidden" name="chart_account_id[]" value="<?php echo $dataAccounts[0]; ?>" />
            <input type="hidden" id="drDeprAccountHidden<?php echo $dataAccounts[0]; ?>" name="debit[]" value="0" />
            <input type="hidden" id="crDeprAccountHidden<?php echo $dataAccounts[0]; ?>" name="credit[]" value="0" />
            <?php
            $queryCoaName=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id=".$dataAccounts[0]);
            $dataCoaName=mysql_fetch_array($queryCoaName);
            echo $dataCoaName[0];
            ?>
        </td>
        <td id="totalDeprAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td style="text-align: right;">
            <?php
            $queryLastPosted=mysql_query("  SELECT SUM(debit)-SUM(credit) AS amount
                                            FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                            WHERE is_approve=1 AND is_active=1 AND is_depreciated=1
                                                AND chart_account_id='".$dataAccounts[0]."'
                                                AND company_id='".$companyId."' AND branch_id='".$branchId."'
                                                AND date <= DATE('".$date."')");
            $dataLastPosted=mysql_fetch_array($queryLastPosted);
            echo number_format($dataLastPosted['amount'],2);
            ?>
        </td>
        <td id="drDeprAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td id="crDeprAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td style="text-align: center;"><input type="text" name="memo[]" value="" /></td>
    </tr>
    <?php
    $totalDeprAccount[$dataAccounts[0]]=0;
    $queryFixedAsset=mysql_query("SELECT *,DATEDIFF('".$date."',date) AS accum_day FROM fixed_assets WHERE is_active=1 AND is_depre=1 AND company_id='".$companyId."' AND branch_id='".$branchId."' AND date <= DATE('".$date."') AND depr_account=".$dataAccounts[0]);
    while($dataFixedAsset=mysql_fetch_array($queryFixedAsset)){
    ?>
        <input type="hidden" name="fixed_asset_id[]" value="<?php echo $dataFixedAsset['id']; ?>" />
        <input type="hidden" name="depr_method[]" value="<?php echo $dataFixedAsset['depr_method']; ?>" />
        <td></td>
        <td><?php echo $dataFixedAsset['name']; ?></td>
        <td style="text-align: right;">
            <?php
            $newDateAccumDay = $dataFixedAsset['accum_day'];
            
            $result = 0;
            $date1 = $dataFixedAsset['date'];
            $date2 = date('Y-m-d', strtotime('+' . $dataFixedAsset['asset_life'] . ' month', strtotime($dataFixedAsset['date'])));
            $totalDeprDay = abs(strtotime($date2) - strtotime($date1)) / 3600 / 24;
            
            if($dataFixedAsset['accum_day'] > $totalDeprDay){
                $dataFixedAsset['accum_day'] = $totalDeprDay;
            }
            $totalCost = (($dataFixedAsset['cost'] * ($dataFixedAsset['business_use_percentage'] / 100)) - $dataFixedAsset['salvage_value']);
            if ($dataFixedAsset['depr_method'] == 'SLM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY id DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'];
                $deprPerday      = (($dataFixedAsset['asset_life']) / 12)*365;
                
                //Condition Total By AmountByPerDay  
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = ($bookValue - $salvageValue) / $deprPerday;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                $isDepreEnd = 0;
                $result = $accumDepr;
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                    $isDepreEnd = 1;
                }else{
                    $result = $accumDepr;
                    if ($result >= $totalCost) {
                        $result = $totalCost;
                    }
                }
            } else if ($dataFixedAsset['depr_method'] == 'DBM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY id DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'] - $dataFixedAsset['cost_remain'];
                $deprPerday      = ($dataFixedAsset['asset_life'] / 12)*365;
                $numberRate      = 1.5;
                $deprRate        = (1/$deprPerday)*$numberRate;
                
                //Condition Total By AmountByPerDay
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = $bookValue * $deprRate;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                }else{
                    $result = $accumDepr;
                    if ($result > $totalCost) {
                        $result = $totalCost;
                    }
                }
            } else if ($dataFixedAsset['depr_method'] == 'DDBM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY date_post DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'] - $dataFixedAsset['cost_remain'];
                $deprPerday      = ($dataFixedAsset['asset_life'] / 12)*365;
                $numberRate      = 2;
                $deprRate        = (1/$deprPerday)*$numberRate;
                
                //Condition Total By AmountByPerDay
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = $bookValue * $deprRate;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                }else{
                    $result = $accumDepr;
                    if ($result > $totalCost) {
                        $result = $totalCost;
                    }
                }
            }
            echo number_format($result,2);
            $totalDeprAccount[$dataAccounts[0]] += $result;
            ?>     
            <input type="hidden" name="amount_post[]" value="<?php echo number_format($result,2); ?>" />
            <script type="text/javascript">
                $(document).ready(function(){
                    $("#totalDeprAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format($totalDeprAccount[$dataAccounts[0]],2); ?>');
                    $("#drDeprAccount<?php echo $dataAccounts[0]; ?>").text('');
                    $("#drDeprAccountHidden<?php echo $dataAccounts[0]; ?>").val(0);
                    $("#crDeprAccount<?php echo $dataAccounts[0]; ?>").text('');
                    $("#crDeprAccountHidden<?php echo $dataAccounts[0]; ?>").val(0);
                    <?php
                    $drDeprAccount=$totalDeprAccount[$dataAccounts[0]];
                    if($drDeprAccount>=0){
                    ?>
                    $("#drDeprAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format(abs($drDeprAccount),2); ?>');
                    $("#drDeprAccountHidden<?php echo $dataAccounts[0]; ?>").val('<?php echo abs($drDeprAccount); ?>');
                    <?php }else{ ?>
                    $("#crDeprAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format(abs($drDeprAccount),2); ?>');
                    $("#crDeprAccountHidden<?php echo $dataAccounts[0]; ?>").val('<?php echo abs($drDeprAccount); ?>');
                    <?php } ?>
                });
            </script>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <?php } ?>
    <?php } ?>
    <?php
    $queryAccounts=mysql_query("SELECT DISTINCT accum_account FROM fixed_assets WHERE is_active=1 AND is_depre=1 AND company_id='".$companyId."' AND branch_id='".$branchId."' AND date <= DATE('".$date."') ORDER BY date") or die(mysql_error());
    while($dataAccounts=mysql_fetch_array($queryAccounts)){
    ?>
    <tr class="group highlight" chart_account_id="<?php echo $dataAccounts[0]; ?>">
        <td colspan="2" chart_account_id="<?php echo $dataAccounts[0]; ?>">
            <input type="hidden" name="chart_account_id[]" value="<?php echo $dataAccounts[0]; ?>" />
            <input type="hidden" id="drAccumAccountHidden<?php echo $dataAccounts[0]; ?>" name="debit[]" value="0" />
            <input type="hidden" id="crAccumAccountHidden<?php echo $dataAccounts[0]; ?>" name="credit[]" value="0" />
            <?php
            $queryCoaName=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id=".$dataAccounts[0]);
            $dataCoaName=mysql_fetch_array($queryCoaName);
            echo $dataCoaName[0];
            ?>
        </td>
        <td id="totalAccumAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td style="text-align: right;">
            <?php
            $queryLastPosted=mysql_query("  SELECT SUM(debit)-SUM(credit) AS amount
                                            FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                            WHERE is_approve=1 AND is_active=1 AND is_depreciated=1
                                                AND chart_account_id='".$dataAccounts[0]."'
                                                AND company_id='".$companyId."' AND branch_id='".$branchId."'
                                                AND date <= DATE('".$date."')");
            $dataLastPosted=mysql_fetch_array($queryLastPosted);
            echo number_format($dataLastPosted['amount'],2);
            ?>
        </td>
        <td id="drAccumAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td id="crAccumAccount<?php echo $dataAccounts[0]; ?>" style="text-align: right;"></td>
        <td style="text-align: center;"><input type="text" name="memo[]" value="" /></td>
    </tr>
    <?php
    $totalAccumAccount[$dataAccounts[0]]=0;
    $queryFixedAsset=mysql_query("SELECT *,DATEDIFF('".$date."',date) AS accum_day FROM fixed_assets WHERE is_active=1 AND is_depre=1 AND company_id='".$companyId."' AND branch_id='".$branchId."' AND date <= DATE('".$date."') AND accum_account=".$dataAccounts[0]);
    while($dataFixedAsset=mysql_fetch_array($queryFixedAsset)){
    ?>
    <tr class="groupDetail highlight" chart_account_id="<?php echo $dataAccounts[0]; ?>" style="display: none;">
        <td></td>
        <td><?php echo $dataFixedAsset['name']; ?></td>
        <td style="text-align: right;">
            <?php
            $newDateAccumDay = $dataFixedAsset['accum_day'];
            
            $result = 0;
            $date1 = $dataFixedAsset['date'];
            $date2 = date('Y-m-d', strtotime('+' . $dataFixedAsset['asset_life'] . ' month', strtotime($dataFixedAsset['date'])));
            $totalDeprDay = abs(strtotime($date2) - strtotime($date1)) / 3600 / 24;
            if($dataFixedAsset['accum_day'] > $totalDeprDay){
                $dataFixedAsset['accum_day'] = $totalDeprDay;
            }
            $totalCost = (($dataFixedAsset['cost'] * ($dataFixedAsset['business_use_percentage'] / 100)) - $dataFixedAsset['salvage_value']);
            if ($dataFixedAsset['depr_method'] == 'SLM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY id DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'];
                $deprPerday      = (($dataFixedAsset['asset_life']) / 12)*365;
                
                //Condition Total By AmountByPerDay  
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = ($bookValue - $salvageValue) / $deprPerday;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                $isDepreEnd = 0;
                $result = $accumDepr;
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                    $isDepreEnd = 1;
                }else{
                    $result = $accumDepr;
                    if ($result >= $totalCost) {
                        $result = $totalCost;
                    }
                }
            } else if ($dataFixedAsset['depr_method'] == 'DBM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY id DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'] - $dataFixedAsset['cost_remain'];
                $deprPerday      = ($dataFixedAsset['asset_life'] / 12)*365;
                $numberRate      = 1.5;
                $deprRate        = (1/$deprPerday)*$numberRate;
                
                //Condition Total By AmountByPerDay
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = $bookValue * $deprRate;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                }else{
                    $result = $accumDepr;
                    if ($result > $totalCost) {
                        $result = $totalCost;
                    }
                }
            } else if ($dataFixedAsset['depr_method'] == 'DDBM') {
                //Last Date Post
                $queryLastDateFixedAsset = mysql_query("SELECT DATEDIFF('".$date."',date_post) AS last_accum_day FROM fixed_asset_amounts WHERE fixed_asset_id = ".$dataFixedAsset['id']." ORDER BY date_post DESC LIMIT 01");
                if(mysql_num_rows($queryLastDateFixedAsset) > 0){
                    $rowLastDateFixedAsset  =  mysql_fetch_array($queryLastDateFixedAsset);
                    $newDateAccumDay        =  $rowLastDateFixedAsset['last_accum_day'];
                }
                
                //Variable Standby
                $accumDepr = 0;
                $salvageValue    = $dataFixedAsset['salvage_value'];
                $bookValueRemain = $dataFixedAsset['cost_remain'];
                $bookValueOld    = $dataFixedAsset['cost'];
                $bookValue       = $dataFixedAsset['cost'] - $dataFixedAsset['cost_remain'];
                $deprPerday      = ($dataFixedAsset['asset_life'] / 12)*365;
                $numberRate      = 2;
                $deprRate        = (1/$deprPerday)*$numberRate;
                
                //Condition Total By AmountByPerDay
                for ($day = 0; $day < $newDateAccumDay; $day++) {
                    $costPerDay = $bookValue * $deprRate;
                    $accumDepr += $costPerDay;
                }
                
                //Condtion BookValueOld
                if(($bookValueRemain + $accumDepr + $salvageValue) > $bookValueOld){
                    $result = ($bookValueOld - $bookValueRemain) - $salvageValue;
                }else{
                    $result = $accumDepr;
                    if ($result > $totalCost) {
                        $result = $totalCost;
                    }
                }
            }
            $result*=-1;
            echo number_format($result,2);
            $totalAccumAccount[$dataAccounts[0]] += $result;
            ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $("#totalAccumAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format($totalAccumAccount[$dataAccounts[0]],2); ?>');
                    $("#drAccumAccount<?php echo $dataAccounts[0]; ?>").text('');
                    $("#drAccumAccountHidden<?php echo $dataAccounts[0]; ?>").val(0);
                    $("#crAccumAccount<?php echo $dataAccounts[0]; ?>").text('');
                    $("#crAccumAccountHidden<?php echo $dataAccounts[0]; ?>").val(0);
                    <?php
                    $crDeprAccount=$totalAccumAccount[$dataAccounts[0]];
                    $crDeprAccount*=-1;
                    if($crDeprAccount>=0){
                    ?>
                    $("#crAccumAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format(abs($crDeprAccount),2); ?>');
                    $("#crAccumAccountHidden<?php echo $dataAccounts[0]; ?>").val('<?php echo abs($crDeprAccount); ?>');
                    <?php }else{ ?>
                    $("#drAccumAccount<?php echo $dataAccounts[0]; ?>").text('<?php echo number_format(abs($crDeprAccount),2); ?>');
                    $("#drAccumAccountHidden<?php echo $dataAccounts[0]; ?>").val('<?php echo abs($crDeprAccount); ?>');
                    <?php } ?>
                });
            </script>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <?php } ?>
    <?php } ?>
</table>