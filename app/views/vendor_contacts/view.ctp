<script type="text/javascript">
    $(document).ready(function() {
        $(".btnBackVendorContact").click(function(event) {
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVendorContact.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();
            rightPanel.html("");
            leftPanel.show("slide", {direction: "left"}, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorContact">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_VENDOR_CONTACT_INFO); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 10%;"><?php echo TABLE_VENDOR; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['Vendor']['vendor_code']." - ".$this->data['Vendor']['name']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_CONTACT_NAME; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['VendorContact']['contact_name']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_SEX; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['VendorContact']['sex']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_CONTACT_TEL; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['VendorContact']['contact_telephone']; ?>
                </div>
            </td>
        </tr> 
        <tr>
            <td><?php echo TABLE_CONTACT_EMAIL; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['VendorContact']['contact_email']; ?>
                </div>
            </td>
        </tr> 
        <tr>
            <td><?php echo TABLE_NOTE; ?> :</td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->data['VendorContact']['note']; ?>
                </div>
            </td>
        </tr> 
        
    </table>
    <div style="clear: both;"></div>
</fieldset>