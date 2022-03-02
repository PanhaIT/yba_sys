<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#UserProfileForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#UserProfileForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".txtSave").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        $( "#UserDob" ).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            yearRange: '-100:-0'
        });
    });
</script>
<?php 
echo $this->Form->create('User');
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(GENERAL_MY_PROFILE); ?></legend>
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
                    <?php echo $this->Form->input('sex', array('empty' => 'Please Select', 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserDob"><?php echo TABLE_DOB; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('dob'); ?>
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
                    <?php echo $this->Form->text('email'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserNationality"><?php echo TABLE_NATIONALITY; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('nationality', array('label' => false, 'default' => 36)); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
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
            <td><label for="UserOldPassword"><?php echo USER_OLD_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('old_password', array('class'=>'validate[required]','value'=>'')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UserPassword"><?php echo USER_NEW_PASSWORD; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->password('password', array('class'=>'validate[required]', 'value' => '')); ?>
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
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>