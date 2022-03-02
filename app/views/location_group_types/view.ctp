<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackLocationGroupType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableLocationGroupType.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();
            rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackLocationGroupType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset style=" width: 98%;">
    <legend><?php __(MENU_WAREHOUSE_TYPE_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 15%;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['LocationGroupType']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['LocationGroupType']['description']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_ALLOW_NEGATIVE_STOCK); ?> :</th>
            <td style="font-size: 12px;">
                <?php 
                if($this->data['LocationGroupType']['allow_negative_stock'] == 0){
                    echo ACTION_NO;
                } else {
                    echo ACTION_YES;
                }
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_ALLOW_TRANSFER_CONFIRM); ?> :</th>
            <td style="font-size: 12px;">
                <?php 
                if($this->data['LocationGroupType']['stock_tranfer_confirm'] == 0){
                    echo ACTION_NO;
                } else {
                    echo ACTION_YES;
                }
                ?>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>
