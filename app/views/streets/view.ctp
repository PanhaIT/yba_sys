<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackStreet").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableStreet.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackStreet">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_STREET_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td><?php echo $this->data['Street']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(GENERAL_ABBR); ?></th>
            <td><?php echo $this->data['Street']['abbr']; ?></td>
        </tr>
    </table>
</fieldset>