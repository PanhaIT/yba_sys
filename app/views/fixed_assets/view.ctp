<?php 
    include('includes/function.php');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackFixedAsset").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackFixedAsset">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" class="info">
    <tr>
        <th><?php __(TABLE_COMPANY); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT name FROM companies WHERE id='".$fixedAsset['FixedAsset']['company_id']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __(TABLE_LOCATION); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT name FROM locations WHERE id='".$fixedAsset['FixedAsset']['location_id']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __(TABLE_VENDOR); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT name FROM vendors WHERE id='".$fixedAsset['FixedAsset']['vendor_id']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __(TABLE_CODE); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['fixed_asset_code']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_NAME); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['name']; ?></td>
    </tr>
    <tr>
        <th><?php __('Purchase order number'); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['purchase_order_number']; ?></td>
    </tr>
    <tr>
        <th><?php __('Serial number'); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['serial_number']; ?></td>
    </tr>
    <tr>
        <th><?php __('Warranty expires'); ?></th>
        <td><?php echo dateShort($fixedAsset['FixedAsset']['warranty_expires']); ?></td>
    </tr>
    <tr>
        <th><?php __('Asset account'); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id='".$fixedAsset['FixedAsset']['asset_account']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __('Accumulated depr/amort'); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id='".$fixedAsset['FixedAsset']['accum_account']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __('Depr/amort expense'); ?></th>
        <td>
            <?php
            $query=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id='".$fixedAsset['FixedAsset']['depr_account']."'");
            $data=mysql_fetch_array($query);
            echo $data[0];
            ?>
        </td>
    </tr>
    <tr>
        <th><?php __('Date placed in service'); ?></th>
        <td><?php echo dateShort($fixedAsset['FixedAsset']['date']); ?></td>
    </tr>
    <tr>
        <th><?php __('Cost or basis'); ?></th>
        <td><?php echo number_format($fixedAsset['FixedAsset']['cost'],2); ?> $</td>
    </tr>
    <tr>
        <th><?php __('Depreciation method'); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['depr_method']; ?></td>
    </tr>
    <tr>
        <th><?php __('Asset life'); ?></th>
        <td><?php echo number_format($fixedAsset['FixedAsset']['asset_life'], 0); ?> month(s)</td>
    </tr>
    <tr>
        <th><?php __('Salvage value'); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['salvage_value']; ?></td>
    </tr>
    <tr>
        <th><?php __('Business use percentage'); ?></th>
        <td><?php echo number_format($fixedAsset['FixedAsset']['business_use_percentage'], 0); ?> %</td>
    </tr>
    <tr>
        <th style="vertical-align: top;"><?php __(GENERAL_DESCRIPTION); ?></th>
        <td><?php echo $fixedAsset['FixedAsset']['description']; ?></td>
    </tr>
</table>