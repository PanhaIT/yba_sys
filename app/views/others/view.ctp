<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackOther").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableOther.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOther">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" class="info">
    <tr>
        <th><?php __(TABLE_NAME); ?></th>
        <td><?php echo $other['Other']['name']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_TELEPHONE_WORK); ?></th>
        <td><?php echo $other['Other']['business_number']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_TELEPHONE_PERSONAL); ?></th>
        <td><?php echo $other['Other']['personal_number']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_TELEPHONE_OTHER); ?></th>
        <td><?php echo $other['Other']['other_number']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_FAX); ?></th>
        <td><?php echo $other['Other']['fax_number']; ?></td>
    </tr>
    <tr>
        <th><?php __(TABLE_EMAIL); ?></th>
        <td><?php echo $other['Other']['email_address']; ?></td>
    </tr>
    <tr>
        <th style="vertical-align: top;"><?php __(TABLE_ADDRESS); ?></th>
        <td><?php echo $other['Other']['address']; ?></td>
    </tr>
</table>