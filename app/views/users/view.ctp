<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackUser").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableUser.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackUser">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_USER_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
    <tr>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_FIRST_NAME); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['first_name']; ?></td>
        <th style="width: 10%; font-size: 12px;"><?php __(TABLE_LAST_NAME); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['last_name']; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_SEX); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['sex']; ?></td>
        <th style="font-size: 12px;"><?php __(TABLE_DOB); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['dob']; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_ADDRESS); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['address']; ?></td>
        <th style="font-size: 12px;"><?php __(TABLE_TELEPHONE); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['telephone']; ?></td>
    </tr>
    <tr>
        <th style="font-size: 12px;"><?php __(TABLE_EMAIL); ?></th>
        <td style="font-size: 12px;"><?php echo $user['User']['email']; ?></td>
        <th style="font-size: 12px;"><?php __(TABLE_NATIONALITY); ?></th>
        <td style="font-size: 12px;"><?php echo $nationality; ?></td>
    </tr>
</table>
</fieldset>
<table cellpadding="5" style="width: 100%;">
    <tr>
        <td style="width: 49%;">
            <fieldset style="width: 100%;">
                <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
                <table cellpadding="5" style="width: 100%;">
                    <tr>
                        <td style="width: 15%;"><?php echo GENERAL_MEMBER; ?> :</td>
                        <td>
                            <?php
                            $sqlCom = mysql_query("SELECT GROUP_CONCAT(name) FROM companies WHERE id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")");
                            @$rowCom = mysql_fetch_array($sqlCom);
                            echo @$rowCom[0];
                            ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td>
            <fieldset style="width: 100%;">
                <legend><?php __(MENU_LOCATION_GROUP_MANAGEMENT_INFO); ?></legend>
                <table cellpadding="5" style="width: 100%;">
                    <tr>
                        <td style="width: 15%;"><?php echo GENERAL_MEMBER; ?> :</td>
                        <td>
                            <?php
                            $sqlLgroup = mysql_query("SELECT GROUP_CONCAT(name) FROM location_groups WHERE id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")");
                            @$rowLgroup = mysql_fetch_array($sqlLgroup);
                            echo @$rowLgroup[0];
                            ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>