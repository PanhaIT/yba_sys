<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
$sqlInfo = mysql_query("SELECT currency_centers.name AS currency, countries.name AS country_name FROM branches INNER JOIN currency_centers ON currency_centers.id = branches.currency_center_id INNER JOIN countries ON countries.id = branches.country_id WHERE branches.id = ".$this->data['Branch']['id']);
$rowInfo = mysql_fetch_array($sqlInfo);
?>
<fieldset>
    <legend><?php __(MENU_BRANCH_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME_IN_KHMER); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['name_other']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_TELEPHONE); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['telephone']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_EMAIL); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['email_address']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_FAX); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['fax_number']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_BASE_CURRENCY); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowInfo['currency']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_COUNTRY); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowInfo['country_name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_LONG); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['long']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_LAT); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['lat']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_ADDRESS); ?> :</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['Branch']['address']); ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_ADDRESS_IN_KHMER); ?> :</th>
            <td style="font-size: 12px;"><?php echo nl2br($this->data['Branch']['address_other']); ?></td>
        </tr>
    </table>
</fieldset>
<fieldset>
    <legend><?php __(TABLE_BANK_ACCOUNT_INFORMATION); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_ACCOUNT_NUMBER); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['acc_num']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_ACCOUNT_HOLDER); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['acc_holder']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_BANK); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['bank']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(MENU_ADDRESS); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['bank_address']; ?></td>
        </tr>
    </table>
</fieldset>