<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackClass").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableClass.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackClass">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CLASS_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_COMPANY); ?></th>
            <td style="font-size: 12px;">
                <?php 
                $sqlCom = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM class_companies WHERE class_id = ".$class['Class']['id'].")");
                $rowCom = mysql_fetch_array($sqlCom);
                echo $rowCom[0]; 
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_NAME); ?></th>
            <td style="font-size: 12px;"><?php echo $class['Class']['name']; ?></td>
        </tr>
        <tr>
            <th style="vertical-align: top; font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?></th>
            <td style="font-size: 12px;"><?php echo nl2br($class['Class']['description']); ?></td>
        </tr>
    </table>
</fieldset>