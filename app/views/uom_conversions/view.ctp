<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackUomConversion").click(function(event){
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
        <a href="" class="positive btnBackUomConversion">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" class="info">
    <tr>
        <th><?php __(UOM_FROM); ?></th>
        <td><?php echo $uomList[$uomConversion['UomConversion']['from_uom_id']]; ?></td>
    </tr>
    <tr>
        <th><?php __(UOM_TO); ?></th>
        <td><?php echo $uomList[$uomConversion['UomConversion']['to_uom_id']]; ?></td>
    </tr>
    <tr>
        <th><?php __(UOM_VALUE); ?></th>
        <td><?php echo $uomConversion['UomConversion']['value']; ?></td>
    </tr>
</table>