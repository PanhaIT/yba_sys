<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackBranchCurrency").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranchCurrency.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranchCurrency">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_BRANCH_CURRENCY_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(MENU_BRANCH); ?></th>
            <td><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(MENU_CURRENCY); ?></th>
            <td><?php echo $this->data['Currency']['name']; ?></td>
        </tr>
    </table>
</fieldset>