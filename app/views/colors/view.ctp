<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackColor").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableColor.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackColor">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_COLOR_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php echo TABLE_NAME; ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Color']['name']; ?></td>
        </tr>
    </table>
</fieldset>