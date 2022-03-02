<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTermConditionType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTermConditionType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTermConditionType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_TERM_CONDITION_TYPE_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TermConditionType']['name']; ?></td>
        </tr>
    </table>
</fieldset>