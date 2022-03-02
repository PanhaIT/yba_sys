<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPosPayMethod").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePosPayMethod.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPosPayMethod">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_POS_PAY_METHOD_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['PosPayMethod']['name']; ?></td>
        </tr>
        <tr>
            <th style="width: 10%; font-size: 12px;"><?php __(TABLE_BANK_CHARGE); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['PosPayMethod']['bank_charge'], 2); ?> (%)</td>
        </tr>
    </table>
</fieldset>