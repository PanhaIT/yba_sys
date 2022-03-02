<?php 
// Authentication
$this->element('check_access');
$allowAddCgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addCgroup');
$allowAddTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'addTerm');

echo $this->element('prevent_multiple_submit'); 
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Action Save
        $("#CustomerAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
               
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtCustomerSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCustomer").click();
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?></p>');
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
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCustomer">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CUSTOMER_MANAGEMENT_INFO); ?></legend>
    <input type="hidden" value="1" name="data[Customer][company_id]" id="CustomerCompanyId" />
    <div style="width: 40%; vertical-align: top; float: left;">
        <table style="width: 99%;">
            <tr>
                <td style="width: 40%;"><label for="CustomerCode"><?php echo TABLE_CUSTOMER_NUMBER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('customer_code', array('class' => 'validate[required]', 'style' => 'width: 90%;', 'value' => $code)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerName"><?php echo TABLE_NAME_IN_ENGLISH; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 33%;"><label for="CustomerCgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('cgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerMainNumberAdd"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('main_number', array('class' => 'validate[required]', 'id' => 'CustomerMainNumberAdd', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
        </table>
        <br />
        <div class="buttons">
            <button type="submit" class="positive btnSaveCustomer">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtCustomerSave"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
    </div>
</fieldset>
<div style="clear: both;"></div>