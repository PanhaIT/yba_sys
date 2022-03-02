<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#TermConditionApplyEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TermConditionApplyEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTermConditionApply").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTermConditionApply").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('TermConditionApply', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('TermConditionApply', 'Edit', 1, '');
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
        $(".btnBackTermConditionApply").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableTermConditionApply.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#termConditionType").change(function(){
            checkTermConditionType($(this));
        });
    });
    
    function checkTermConditionType(obj){
        var termConTypeId = obj.val();
        $("#termConditionDefault").filterOptions('value', '', '');
        if(termConTypeId != ""){
            $("#termConditionDefault").filterOptions('term-type', termConTypeId, '');
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTermConditionApply">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('TermConditionApply'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(MENU_TERM_CONDITION_APPLY_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="TermConditionApplyModuleTypeId"><?php echo TABLE_MODULE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TermConditionApply][module_type_id]" id="TermConditionApplyModuleTypeId" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlMod = mysql_query("SELECT id, name FROM module_types WHERE id IN (25, 48, 68, 69, 91) ORDER BY ordering");
                        while($rowMod = mysql_fetch_array($sqlMod)){
                        ?>
                        <option value="<?php echo $rowMod['id']; ?>" <?php if($rowMod['id']== $this->data['TermConditionApply']['module_type_id']){ ?>selected="selected"<?php } ?>><?php echo $rowMod['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="termConditionType"><?php echo MENU_TERM_CONDITION_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[TermConditionApply][term_condition_type_id]" style="width: 90%; height: 30px;" id="termConditionType" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlTCT = mysql_query("SELECT id, name FROM term_condition_types WHERE is_active = 1");
                        while($rowTCT = mysql_fetch_array($sqlTCT)){
                        ?>
                        <option value="<?php echo $rowTCT['id']; ?>" <?php if($rowTCT['id']== $this->data['TermConditionApply']['term_condition_type_id']){ ?>selected="selected"<?php } ?>><?php echo $rowTCT['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="termConditionDefault"><?php echo MENU_TERM_CONDITION; ?> (Default) :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[TermConditionApply][term_condition_default_id]" style="width: 90%; height: 30px;" id="termConditionDefault">
                        <option value="" term-type="0"><?php echo TABLE_ALL; ?></option>
                        <?php
                        $sqlTC = mysql_query("SELECT id, term_condition_type_id, name FROM term_conditions WHERE is_active = 1");
                        while($rowTC = mysql_fetch_array($sqlTC)){
                        ?>
                        <option value="<?php echo $rowTC['id']; ?>" term-type="<?php echo $rowTC['term_condition_type_id']; ?>" <?php if($rowTC['id']== $this->data['TermConditionApply']['term_condition_default_id']){ ?>selected="selected"<?php } ?>><?php echo $rowTC['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveTermConditionApply"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>