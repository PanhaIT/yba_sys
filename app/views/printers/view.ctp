<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackPrinter").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePrinter.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPrinter">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PRINTER_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(MENU_BRANCH); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <th ><?php __(TABLE_TYPE); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Printer']['type_id']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top; "><?php __(TABLE_PRINTER_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Printer']['printer_name']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top; "><?php __(TABLE_SILENT); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Printer']['silent']; ?></td>
        </tr>
    </table>
</fieldset>