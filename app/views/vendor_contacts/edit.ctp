<?php 
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    var fieldRequireVendorContact = ['VendorContactVendorId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();    
        $("#VendorContactVendorId").chosen({ width: 320 });
        // Action Save
        $("#VendorContactEditForm").validationEngine();
        $("#VendorContactEditForm").ajaxForm({
            beforeSerialize: function($form, options) {                
                if(checkRequireField(fieldRequireVendorContact) == false){
                    alertSelectRequireField();
                    $(".btnSaveVendorContact").removeAttr('disabled');
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtVendorContactSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVendorContact").click();
                // Alert Message
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
       
        // Action Back
        $(".btnBackVendorContact").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVendorContact.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
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
<?php 
echo $this->Form->create('VendorContact', array('inputDefaults' => array('div' => false, 'label' => false))); 
echo $this->Form->hidden('id');
echo $this->Form->hidden('sys_code');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Vgroup][company_id]" id="VgroupCompanyId" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_VENDOR_CONTACT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="VendorContactVendorId"><?php echo TABLE_VENDOR_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('vendor_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VendorContactTitle"><?php echo TABLE_TITLE_PERSON; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('title', array('style' => 'width: 30%;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VendorContactContactName"><?php echo TABLE_CONTACT_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('contact_name', array('style' => 'width: 99%;', 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VendorContactSex"><?php echo TABLE_SEX; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('sex', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VendorContactContactTelephone"><?php echo TABLE_CONTACT_TEL; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('contact_telephone', array('style' => 'width: 99%;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VendorContactContactEmail"><?php echo TABLE_CONTACT_EMAIL; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('contact_email', array('style' => 'width: 99%;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="VendorContactNote"><?php echo TABLE_NOTE; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->textarea('note', array('style' => 'width: 99%;')); ?>
                </div>
            </td>
        </tr>
    </table>
    <br />
    <div class="buttons">
        <button type="submit" class="positive btnSaveVendorContact">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtVendorContactSave"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
</fieldset>
<?php echo $this->Form->end(); ?>