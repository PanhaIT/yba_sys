<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackVillage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVillage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVillage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" cellpadding="5">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_VILLAGE); ?></th>
        <td style="font-size: 12px;"><?php echo $village['Village']['name']; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_COMMUNE); ?></th>
        <td style="font-size: 12px;"><?php echo $commune; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_DISTRICT); ?></th>
        <td style="font-size: 12px;"><?php echo $district; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_PROVINCE); ?></th>
        <td style="font-size: 12px;"><?php echo $province; ?></td>
    </tr>
</table>