<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#LocationGroupTypeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#LocationGroupTypeAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveLocationGroupType").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackLocationGroupType").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Warehouse Type', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Warehouse Type', 'Add', 1, '');
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
        $(".btnBackLocationGroupType").click(function(event){
            event.preventDefault();
            $('#LocationGroupTypeAddForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTableLocationGroupType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#CheckLocationGroupTypeAllowNegativeStock").click(function(){
            var isCheck = 0;
            if($(this).is(":checked")) {
                isCheck = 1;
            }
            $("#LocationGroupTypeAllowNegativeStock").val(isCheck);
        });
        
        $("#CheckLocationGroupTypeStockTransferConfirm").click(function(){
            var isCheck = 0;
            if($(this).is(":checked")) {
                isCheck = 1;
            }
            $("#LocationGroupTypeStockTransferConfirm").val(isCheck);
        });
        
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackLocationGroupType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('LocationGroupType'); ?>
<input type="hidden" name="data[LocationGroupType][allow_negative_stock]" id="LocationGroupTypeAllowNegativeStock" value="0" />
<input type="hidden" name="data[LocationGroupType][stock_tranfer_confirm]" id="LocationGroupTypeStockTransferConfirm" value="0" />
<fieldset style=" height: 238px;">
    <legend><?php __(MENU_WAREHOUSE_TYPE_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="LocationGroupTypeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 400px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="LocationGroupTypeDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('description', array('style' => 'width: 400px;', 'label' => false)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="checkbox" id="CheckLocationGroupTypeAllowNegativeStock" /><label for="CheckLocationGroupTypeAllowNegativeStock"><?php echo TABLE_ALLOW_NEGATIVE_STOCK; ?> (Sales)</label>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveLocationGroupType">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveLocationGroupType"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>