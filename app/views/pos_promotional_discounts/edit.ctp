<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen({width: 350});
        $("#PosPromotionalDiscountEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PosPromotionalDiscountEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePosPromotionalDiscount").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPosPromotionalDiscount").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Branch Currency', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Branch Currency', 'Edit', 1, '');
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
        $(".btnBackPosPromotionalDiscount").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePosPromotionalDiscount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPosPromotionalDiscount">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('PosPromotionalDiscount', array('inputDefaults' => array('div' => false, 'label' => false))); 
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(MENU_POS_PROMOTIONAL_DISCOUNT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="PosPromotionalDiscountBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[PosPromotionalDiscount][branch_id]" id="PosPromotionalDiscountBranchId" class="validate[required]">
                        <?php
                        if(count($branches) != 1){
                        ?>
                        <option value="" dcur=""><?php echo INPUT_SELECT; ?></option>
                        <?php 
                        }
                        foreach($branches AS $branch){  
                        ?>
                        <option <?php if($branch['Branch']['id'] == $this->data['PosPromotionalDiscount']['branch_id']){ ?>selected="selected"<?php } ?> value="<?php echo $branch['Branch']['id']; ?>" dcur="<?php echo $branch['Branch']['currency_center_id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="PosPromotionalDiscountPromotionTypeId"><?php echo TABLE_PROMOTION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('promotion_type_id', array('selected' => $promotionTypesSellected, 'label' => false,'id'=>'PosPromotionalDiscountPromotionTypeId', 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width:350px;')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSavePosPromotionalDiscount"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>