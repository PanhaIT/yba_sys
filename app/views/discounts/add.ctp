<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#DiscountAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-layout-center"
        });
        $("#DiscountAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveDiscount").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackDiscount").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Discount', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Discount', 'Add', 1, '');
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
        $(".float").autoNumeric();
        $(".btnBackDiscount").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableDiscount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    function validate2fields(){
        if($("#DiscountPercent").val() =="" && $("#DiscountAmount").val() == ""){
            $("#DiscountPercent, #DiscountAmount").addClass("validate[required]");
            return "Please fill in one of them.";
        } else if($("#DiscountPercent").val() !="" && $("#DiscountAmount").val() != ""){
            $("#DiscountPercent, #DiscountAmount").addClass("validate[required]");
            return "Please fill in one of them.";
        }else{
            $("#DiscountPercent, #DiscountAmount").removeClass("validate[required]");
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackDiscount">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Discount', array('inputDefaults' => array('div' => false, 'label' => false))); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Discount][company_id]" id="DiscountCompanyId" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_DISCOUNT_INFORMATION); ?></legend>
    <table>
        <tr>
            <td><label for="DiscountName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="DiscountPercent"><?php echo TABLE_PERCENT; ?> <span class="blue">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('percent', array('class' => 'validate[funcCall[validate2fields]] float', 'style' => 'width: 100px')); ?> %
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="DiscountAmount"><?php echo GENERAL_AMOUNT; ?> <span class="blue">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('amount', array('class' => 'validate[funcCall[validate2fields]] float', 'style' => 'width: 100px')); ?> $
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="DiscountDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td><?php echo $this->Form->textarea('description'); ?></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveDiscount"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>