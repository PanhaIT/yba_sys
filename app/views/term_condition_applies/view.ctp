<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackTermConditionApply").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTermConditionApply.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTermConditionApply">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_TERM_CONDITION_APPLY_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_MODULE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['ModuleType']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(MENU_TERM_CONDITION_TYPE); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['TermConditionType']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(MENU_TERM_CONDITION); ?> (Default)</th>
            <td style="font-size: 12px;"><?php echo $this->data['TermCondition']['name']; ?></td>
        </tr>
    </table>
</fieldset>