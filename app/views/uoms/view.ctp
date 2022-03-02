<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackUom").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableUom.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackUom">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_UOM_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(GENERAL_TYPE); ?></th>
            <td style="font-size: 12px;"><?php echo $uom['Uom']['type']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $uom['Uom']['name']; ?></td>
        </tr>
        <tr>
            <th ><?php __(GENERAL_ABBR); ?></th>
            <td style="font-size: 12px;"><?php echo $uom['Uom']['abbr']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top; "><?php __(GENERAL_DESCRIPTION); ?></th>
            <td style="font-size: 12px;"><?php echo $uom['Uom']['description']; ?></td>
        </tr>
    </table>
</fieldset>
<?php
$queryUomConversion=mysql_query("SELECT (SELECT name FROM uoms WHERE id=from_uom_id) AS from_uom_name,(SELECT name FROM uoms WHERE id=to_uom_id) AS to_uom_name,value FROM uom_conversions WHERE from_uom_id='" . $uom['Uom']['id'] . "' OR to_uom_id='" . $uom['Uom']['id'] . "'");
if(mysql_num_rows($queryUomConversion)){
?>
<br />
<fieldset>
    <legend><?php __(MENU_UOM_CONVERSION_MANAGEMENT_INFO); ?></legend>
    <table width="100%" class="info">
        <?php while($dataUomConversion=mysql_fetch_array($queryUomConversion)){ ?>
        <tr>
            <td>1 <?php echo $dataUomConversion['from_uom_name']; ?> = <?php echo $dataUomConversion['value']; ?> <?php echo $dataUomConversion['to_uom_name']; ?></td>
        </tr>
        <?php } ?>
    </table>
</fieldset>
<?php } ?>