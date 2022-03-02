<?php
// Setting
$allowBarcode = false;
$salesDecimal = 2;
$sqlSetting = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (1, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 1){
        if($rowSetting['is_checked'] == 1){
            $allowBarcode = true;
        } else if($rowSetting['is_checked'] == 40){
            $salesDecimal = $rowSetting['value'];
        }
    }
}
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE is_active = 1 LIMIT 1)");
$rowSym = mysql_fetch_array($sqlSym);
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackService").unbind('click').click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableProductDashBoard.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackService">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_SERVICE_INFORMATION); ?></legend>
    <table style="width: 50%;" cellpadding="5">
        <tr>
            <td style="width: 20%; font-weight: bold;"><?php __(TABLE_NAME); ?> :</td>
            <td><?php echo $this->data['Service']['name']; ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;"><?php __(MENU_PRODUCT_GROUP_MANAGEMENT); ?> :</td>
            <td><?php echo $this->data['Pgroup']['name']; ?></td>
        </tr>
        <tr<?php if($allowBarcode == FALSE){ ?> style="display: none;"<?php } ?>>
            <td style="font-weight: bold;"><?php __(TABLE_BARCODE); ?> :</td>
            <td><?php echo $this->data['Service']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;"><?php __(TABLE_UOM); ?> :</td>
            <td>
                <?php 
                if(!empty($this->data['Service']['uom_id'])){
                    $sqlUom = mysql_query("SELECT name FROM uoms WHERE id = ".$this->data['Service']['uom_id']);
                    $rowUom = mysql_fetch_array($sqlUom);
                    echo $rowUom[0];
                } 
                ?>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;"><?php __(TABLE_UNIT_PRICE); ?> :</td>
            <td><?php echo number_format($this->data['Service']['unit_price'], $salesDecimal).' '.$rowSym[0]; ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;"><?php __(GENERAL_DESCRIPTION); ?> :</td>
            <td><?php echo nl2br($this->data['Service']['description']); ?></td>
        </tr>
    </table>
</fieldset>