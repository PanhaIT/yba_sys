<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        <?php 
        if(count($companies) > 1){
        ?>
        $("#PriceTypeCompanyId").chosen({ width: 410});
        <?php
        }
        ?>
        $("#PriceTypeOrdering").autoNumeric({mDec: 0, aSep: ','});
        $("#PriceTypeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PriceTypeAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#PriceTypeOrdering").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                <?php 
                if(count($companies) > 1){
                ?>
                if($("#PriceTypeCompanyId").val() == "" || $("#PriceTypeCompanyId").val() == null){
                    alertSelectCompanyPriceType();
                    return false;
                }
                <?php
                }
                ?>
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePriceType").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPriceType").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Price Type', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Price Type', 'Add', 1, '');
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
        $(".btnBackPriceType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePriceType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    <?php 
    if(count($companies) > 1){
    ?>
    function alertSelectCompanyPriceType(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SELECT_COMPANY; ?></p>');
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
                    $(".btnSavePriceType").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    <?php
    }
    ?>
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPriceType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('PriceType'); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[PriceType][company_id][]" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_PRICE_TYPE_INFO); ?></legend>
    <table>
        <?php
        if(count($companies) > 1){
        ?>
        <tr>
            <td style="width: 15%;"><label for="PriceTypeCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td colspan="3">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('company_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 424px;')); ?>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td style="width: 15%;"><label for="PriceTypeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
            <td><label for="PriceTypeOrdering"><?php echo TABLE_ORDERING; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('ordering', array('class'=>'validate[required]', 'style' => 'width: 30%;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 15%;"><label for="PriceTypeSetTo"><?php echo TABLE_SET_TO; ?> <span class="red">*</span> :</label></td>
            <td colspan="3">
                <div class="inputContainer" style="width: 100%;">
                    <select style="width: 140px;" id="PriceTypeSetTo" class="validate[required]" name="data[PriceType][is_set]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo TABLE_AS_AMOUNT; ?></option>
                        <option value="2"><?php echo TABLE_AS_PERCENT; ?></option>
                        <option value="3"><?php echo TABLE_AS_ADD_ON; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 15%;"><label for="PriceTypeApplyTo"><?php echo TABLE_APPLY_TO; ?> :</label></td>
            <td colspan="3">
                <div class="inputContainer" style="width: 100%;">
                    <select name="apply_to" style="width: 140px;" id="PriceTypeApplyTo">
                        <option value="0"><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo MENU_POS; ?></option>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSavePriceType">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSavePriceType"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>