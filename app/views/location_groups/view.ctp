<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackLocationGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableLocationGroup.fnDraw(false);
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
        <a href="" class="positive btnBackLocationGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset style=" width: 49%; float: left;">
    <legend><?php __(MENU_LOCATION_GROUP_MANAGEMENT_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 25%;"><?php __(MENU_WAREHOUSE_TYPE); ?> :</th>
            <td style="font-size: 12px;">
                <?php 
                $locationGroup['LocationGroupType']['name']
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_CODE); ?> :</th>
            <td style="font-size: 12px;"><?php echo $locationGroup['LocationGroup']['code']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $locationGroup['LocationGroup']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(GENERAL_DESCRIPTION); ?> :</th>
            <td style="font-size: 12px;"><?php echo $locationGroup['LocationGroup']['description']; ?></td>
        </tr>
    </table>
</fieldset>
<fieldset style=" width: 45%; float: right;">
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <?php
        $sqlUser = mysql_query("SELECT CONCAT(users.first_name,' ',users.last_name) FROM users INNER JOIN user_location_groups ON user_location_groups.user_id = users.id AND user_location_groups.location_group_id = ".$locationGroup['LocationGroup']['id']);
        while($rowUser = mysql_fetch_array($sqlUser)){
        ?>
        <tr>
            <th style="font-size: 12px; width: 15%;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $rowUser[0]; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
</fieldset>
<div style="clear: both;"></div>
<fieldset style=" width: 49%; float: left;">
    <legend><?php __(MENU_CLASS_MANAGEMENT_INFO); ?></legend>
    <table width="100%" class="table">
        <tr>
            <th class="first" style="width: 50%;"><?php echo TABLE_COMPANY; ?></th>
            <th><?php echo TABLE_CLASS; ?></th>
        </tr>
        <?php
        $sqlCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1;");
        while($rowCom = mysql_fetch_array($sqlCom)){
        ?>
        <tr>
            <td class="first">
                <?php echo $rowCom['name']; ?>
            </td>
            <td>
                <?php
                $sqlClass = mysql_query("SELECT classes.id, classes.name FROM classes INNER JOIN class_companies ON class_companies.class_id = classes.id WHERE class_companies.company_id = ".$rowCom['id']." AND is_active = 1;");
                while($rowClass = mysql_fetch_array($sqlClass)){
                    $sqlSelected = mysql_query("SELECT class_id FROM location_group_classese WHERE company_id = ".$rowCom['id']." AND location_group_id = ".$locationGroup['LocationGroup']['id']);
                    $rowSelected = mysql_fetch_array($sqlSelected);
                    if($rowSelected[0] == $rowClass['id']){
                        echo $rowClass['name'];
                    }
                }
                ?>
            </td>
        </tr>
        <?php
        }
        ?>
    </table>
</fieldset>
<div style="clear: both;"></div>
