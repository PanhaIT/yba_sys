<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#UserGroupId").chosen({ width: 424 });
        $("#UserAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#UserAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('userCompanySelected', true);
                listbox_selectall('userLocationGroupSelected', true);
                listbox_selectall('userBranchSelected', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var rightPanel=$("#UserAddForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableUser.fnDraw(false);
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
        
        $(".btnBackUser").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $(".userMoveCompany").click(function(){
            var companyId = '0';
            listbox_selectall('userCompanySelected', true);
            if($("#userCompanySelected").find("option:selected").val() != undefined){
                companyId = $("#userCompanySelected").val().toString();
            }
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getBranchByCompany/" + companyId,
                data: "",
                beforeSend: function(){
                    listbox_selectall('userBranchSelected', true);
                    listbox_moveacross('userBranchSelected', 'userBranch');
                    $("#userBranch").html('');
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(opt){
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#userBranch").append(opt);
                }
            });
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
<?php 
echo $this->Form->create('User');
if(!empty($changeUser['User']['id'])){
?>
<input type="hidden" name="data[User][exited_id]" value="<?php echo $changeUser['User']['id']; ?>" />
<fieldset>
    <legend><?php __('Existed User'); ?></legend>
    <table cellpadding="5" cellspacing="0">
        <tr>
            <td><?php echo TABLE_FIRST_NAME; ?> :</td>
            <td>
                <div class="inputContainer">
                    <?php echo $changeUser['User']['first_name']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php echo TABLE_LAST_NAME; ?> :</td>
            <td>
                <div class="inputContainer">
                    <?php echo $changeUser['User']['last_name']; ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<?php
} else {
?>
<input type="hidden" name="data[User][exited_id]" value="" />
<?php
}
?>
<fieldset>
    <?php
    if(!empty($changeUser['User']['id'])){
    ?>
    <legend><?php __('Move to New User Information'); ?></legend>
    <?php
    } else {
    ?>
    <legend><?php __(MENU_USER_MANAGEMENT_ADD); ?></legend>
    <?php
    }
    ?>
    <table>
        <tr>
            <td><label for="UserFirstName"><?php echo TABLE_FIRST_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('first_name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserLastName"><?php echo TABLE_LAST_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('last_name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserSex"><?php echo TABLE_SEX; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('sex', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserAddress"><?php echo TABLE_ADDRESS; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('address'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserTelephone"><?php echo TABLE_TELEPHONE; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserEmail"><?php echo TABLE_EMAIL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('email', array('class'=>'validate[optional,custom[email]]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div style="width: 48%; float: left;">
    <fieldset>
        <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
        <table>
            <tr>
                <th>Available:</th>
                <th></th>
                <th>Member of:</th>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <select id="userCompany" multiple="multiple" style="width: 280px; height: 200px;">
                        <?php
                        $querySource = mysql_query("SELECT id, name FROM companies WHERE is_active=1");
                        while($dataSource=mysql_fetch_array($querySource)){
                        ?>
                        <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td style="vertical-align: middle;">
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" class="userMoveCompany" onclick="listbox_moveacross('userCompany', 'userCompanySelected')" />
                    <br /><br />
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" class="userMoveCompany" onclick="listbox_moveacross('userCompanySelected', 'userCompany')" />
                </td>
                <td style="vertical-align: top;">
                    <select id="userCompanySelected" name="data[User][company_id][]" multiple="multiple" style="width: 280px; height: 200px;"></select>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div style="width: 48%; float: right; margin-right: 5px;">
    <fieldset>
        <legend><?php __(MENU_BRANCH_INFO); ?></legend>
        <table>
            <tr>
                <th>Available:</th>
                <th></th>
                <th>Member of:</th>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <select id="userBranch" multiple="multiple" style="width: 280px; height: 200px;">
                        <?php
                        $querySource=mysql_query("SELECT id, name FROM branches WHERE is_active = 1");
                        while($dataSource=mysql_fetch_array($querySource)){
                        ?>
                        <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td style="vertical-align: middle;">
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranch', 'userBranchSelected')" />
                    <br /><br />
                    <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranchSelected', 'userBranch')" />
                </td>
                <td style="vertical-align: top;">
                    <select id="userBranchSelected" name="data[User][branch_id][]" multiple="multiple" style="width: 280px; height: 200px;"></select>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div style="clear: both;"></div>
<br />
<fieldset>
    <legend><?php __(MENU_LOCATION_GROUP_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userLocationGroup" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id, name FROM location_groups WHERE is_active = 1");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userLocationGroup', 'userLocationGroupSelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" onclick="listbox_moveacross('userLocationGroupSelected', 'userLocationGroup')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userLocationGroupSelected" name="data[User][location_group_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(USER_LOGIN_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="UserUsername"><?php echo USER_USER_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('username', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserPassword"><?php echo USER_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('password', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserConfirmPassword"><?php echo USER_CONFIRM_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('confirm_password', array('class'=>'validate[required,equals[UserPassword]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserGroupId"><?php echo USER_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('group_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class'=>'chzn-select', 'style' => 'width: 424px;')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>