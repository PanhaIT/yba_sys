<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackCurrency").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCurrency.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCurrency">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CURRENCY_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td><?php echo $this->data['Currency']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_SYMBOL); ?></th>
            <td><?php echo $this->data['Currency']['symbol']; ?></td>
        </tr>
    </table>
</fieldset>