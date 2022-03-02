<?php 
$allowStockMinus = false;
$sqlSetting      = mysql_query("SELECT * FROM s_module_detail_settings WHERE id = 22 AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['is_checked'] == 1){
        $allowStockMinus = true;
    }
}
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#LocationGroupEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#LocationGroupEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('lgd', true);
                if($("#lgd").val() == null){
                    alertSelectUserLocationGroup();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveLocationGroup").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackLocationGroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Warehouse', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Warehouse', 'Edit', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".btnBackLocationGroup").click(function(event){
            event.preventDefault();
            $('#LocationGroupAddForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTableLocationGroup.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();
            rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
    });
    
    function alertSelectUserLocationGroup(){
        $(".btnSaveLocationGroup").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_SELECT_USER; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".btnSaveVendor").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
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
<?php 
echo $this->Form->create('LocationGroup');
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <fieldset style=" height: 258px;">
                <legend><?php __(MENU_LOCATION_GROUP_MANAGEMENT_INFO); ?></legend>
                <table>
                    <tr>
                        <td style="width: 25%;"><label for="LocationGroupLocationGroupType"><?php echo MENU_WAREHOUSE_TYPE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('location_group_type_id', array('class' => 'validate[required]', 'style' => 'width: 410px;', 'label' => false, 'empty' => INPUT_SELECT)); ?>
                            </div>
                        </td>
                    </tr>
                    <tr id="customerLocationGroupFrm">
                        <td><label for="LocationGroupCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('code', array('class' => 'validate[required]', 'style' => 'width: 400px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="LocationGroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 400px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    if($allowStockMinus == true){
                    ?>
                    <tr>
                        <td><label for="LocationGroupAllowNegativeStock"><?php echo TABLE_ALLOW_NEGATIVE_STOCK; ?> (Sales) <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <select name="data[LocationGroup][allow_negative_stock]" id="LocationGroupAllowNegativeStock" style="width: 90px;">
                                    <option value="0" <?php if($this->data['LocationGroup']['allow_negative_stock'] == 0){ ?>selected=""<?php } ?>><?php echo ACTION_NO; ?></option>
                                    <option value="1" <?php if($this->data['LocationGroup']['allow_negative_stock'] == 1){ ?>selected=""<?php } ?>><?php echo ACTION_YES; ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td style="vertical-align: top;"><label for="LocationGroupDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
                        <td>
                            <div class="inputContainer" style="width: 100%;">
                                <?php echo $this->Form->input('description', array('style' => 'width: 400px;', 'label' => false)); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="vertical-align: top;">
            <fieldset>
                <legend><?php __(USER_USER_INFO); ?></legend>
                <table>
                    <tr>
                        <th>Available:</th>
                        <th></th>
                        <th>Members:</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <select id="lgs" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_location_groups WHERE location_group_id=".$this->data['LocationGroup']['id'].")");
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('lgs', 'lgd')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('lgd', 'lgs')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="lgd" name="data[LocationGroup][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_location_groups.user_id) AS full_name FROM user_location_groups WHERE location_group_id = ".$this->data['LocationGroup']['id']);
                                while($dataDestination=mysql_fetch_array($queryDestination)){
                                ?>
                                <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveLocationGroup">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveLocationGroup"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>