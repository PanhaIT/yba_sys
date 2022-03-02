<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackPgroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePgroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPgroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PRODUCT_GROUP_MANAGEMENT_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <td><?php echo TABLE_SUB_OF_GROUP; ?> :</td>
            <td>
                <?php 
                if(!empty($pgroup['Pgroup']['parent_id'])){
                    $sqlSub = mysql_query("SELECT name FROM pgroups WHERE id =".$pgroup['Pgroup']['parent_id']);
                    $rowSub = mysql_fetch_array($sqlSub);
                    echo $rowSub[0]; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_NAME; ?> :</td>
            <td><?php echo $pgroup['Pgroup']['name']; ?></td>
        </tr>
        <tr>
            <td><?php echo TABLE_PRODUCT; ?> :</td>
            <td>
                <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                    <ul class="chzn-choices" style="border: none; background: none;">
                        <?php
                        foreach ($products AS $product) {
                            echo "<li class='search-choice' ><span>{$product['Product']['code']} ({$product['Product']['name']})</span></li>";
                        }
                        ?>
                    </ul>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo MENU_ICS_MANAGEMENT; ?> :</td>
            <td>
                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                    <?php
                    $invAsset = 'Default';
                    $invCOGS  = 'Default';
                    $saleInc  = 'Default';
                    $sqlICS = mysql_query("SELECT pgroup_accounts.account_type_id, CONCAT_WS(' - ', chart_accounts.account_codes, chart_accounts.account_description) FROM pgroup_accounts INNER JOIN chart_accounts ON chart_accounts.id = pgroup_accounts.chart_account_id WHERE pgroup_accounts.pgroup_id = ".$pgroup['Pgroup']['id']);
                    while($rowICS = mysql_fetch_array($sqlICS)){
                        if($rowICS[0] == 1){
                            $invAsset = $rowICS[1];
                        }
                        if($rowICS[0] == 2){
                            $invCOGS = $rowICS[1];
                        }
                        if($rowICS[0] == 8){
                            $saleInc = $rowICS[1];
                        }
                    }
                    ?>
                    <tr>
                        <td style="width: 15%;">Inventory Asset Account :</td>
                        <td><?php echo $invAsset; ?></td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">COGS Account :</td>
                        <td><?php echo $invCOGS; ?></td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">Sales Income :</td>
                        <td><?php echo $saleInc; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?php echo MENU_USERS; ?> :</td>
            <td>
                <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                    <?php
                    if(!empty($users)){
                    ?>
                    <ul class="chzn-choices" style="border: none; background: none;">
                        <?php
                        foreach ($users AS $user) {
                            echo "<li class='search-choice' ><span>{$user['User']['first_name']} ({$user['User']['last_name']})</span></li>";
                        }
                        ?>
                    </ul>
                    <?php
                    } else {
                        echo TABLE_ALL;
                    }
                    ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>