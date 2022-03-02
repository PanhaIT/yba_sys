<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackVendorGroup").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableVendorGroup.fnDraw(false);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" class="info">
    <tr>
        <th><?php echo TABLE_NAME; ?></th>
        <td><?php echo $vgroup['Vgroup']['name']; ?></td>
    </tr>
    <tr>
        <th><?php echo MENU_VENDOR; ?></th>
        <td>
            <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                <ul class="chzn-choices" style="border: none; background: none;">
                    <?php
                    foreach ($vendors AS $vendor) {
                        echo "<li class='search-choice' ><span>{$vendor['Vendor']['vendor_code']} ({$vendor['Vendor']['name']})</span></li>";
                    }
                    ?>
                </ul>
            </div>
        </td>
    </tr>
</table>