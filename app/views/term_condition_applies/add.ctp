<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowTermConditionApply = 0;
    var rowTermConditionApplyList  =  $("#rowTermConditionApply");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowTermConditionApply").remove();
        $("#TermConditionApplyAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#TermConditionApplyAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveTermConditionApply").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackTermConditionApply").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('TermConditionApply', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('TermConditionApply', 'Add', 1, '');
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
        // Clone Row Location List
        cloneTermApplyRow();
    });
    
    function cloneTermApplyRow(){
        if($(".rowTermConditionApply:last").find(".name").attr("id") == undefined){
            indexRowTermConditionApply = 1;
        }else{
            indexRowTermConditionApply = parseInt($(".rowTermConditionApply:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var tr    = rowTermConditionApplyList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .termConditionType").attr("id", "termConditionType"+indexRowTermConditionApply);
        tr.find("td .termConditionDefault").attr("id", "termConditionDefault"+indexRowTermConditionApply);
        $("#tblTermConditionApply").append(tr);
        var LenTr = parseInt($(".rowTermConditionApply").length);
        if(LenTr == 1){
            $("#tblTermConditionApply").find("tr:eq("+LenTr+")").find(".btnAddTermConditionApplyRow").show();
            $("#tblTermConditionApply").find("tr:eq("+LenTr+")").find(".btnRemoveTermConditionApplyRow").hide();
        }
        tr.find("td .name").focus();
        checkTermConditionType(tr);
        eventKeyRowTermApply();
    }
    
    function eventKeyRowTermApply(){
        $(".termConditionType, .btnAddTermConditionApplyRow, .btnRemoveTermConditionApplyRow").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        
        $(".termConditionType").change(function(){
            checkTermConditionType($(this));
        });
        
        $(".btnAddTermConditionApplyRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveTermConditionApplyRow").show();
            cloneTermApplyRow();
        });
        
        $(".btnRemoveTermConditionApplyRow").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowTermConditionApply").length);
                        if(lenTr == 1){
                            $("#tblTermConditionApply").find("tr:eq("+lenTr+")").find("td .btnRemoveTermConditionApplyRow").hide();
                        }
                        $("#tblTermConditionApply").find("tr:eq("+lenTr+")").find("td .btnAddTermConditionApplyRow").show();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function checkTermConditionType(obj){
        var termConTypeId = obj.val();
        obj.closest("tr").find(".termConditionDefault").filterOptions('value', '', '');
        if(termConTypeId != ""){
            obj.closest("tr").find(".termConditionDefault").filterOptions('term-type', termConTypeId, '');
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
<?php echo $this->Form->create('TermConditionApply'); ?>
<fieldset>
    <legend><?php __(MENU_TERM_CONDITION_APPLY_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="TermConditionApplyModuleTypeId"><?php echo TABLE_MODULE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[TermConditionApply][module_type_id]" id="TermConditionApplyModuleTypeId" class="TermConditionApplyModuleTypeId validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlMod = mysql_query("SELECT id, name FROM module_types WHERE id IN (25, 48, 68, 69, 91) ORDER BY ordering");
                        while($rowMod = mysql_fetch_array($sqlMod)){
                        ?>
                        <option value="<?php echo $rowMod['id']; ?>"><?php echo $rowMod['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
    <table id="tblTermConditionApply" class="table" style="width: 70%;">
        <tr>
            <th class="first" style="width: 35%;"><?php echo MENU_TERM_CONDITION_TYPE; ?> <span class="red">*</span></th>
            <th><?php echo MENU_TERM_CONDITION; ?> (Default)</th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowTermConditionApply" class="rowTermConditionApply" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <select name="term_condition_type[]" style="width: 90%; height: 20px;" id="termConditionType" class="termConditionType validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlTCT = mysql_query("SELECT id, name FROM term_condition_types WHERE is_active = 1");
                        while($rowTCT = mysql_fetch_array($sqlTCT)){
                        ?>
                        <option value="<?php echo $rowTCT['id']; ?>"><?php echo $rowTCT['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="term_condition_default[]" style="width: 90%; height: 20px;" id="termConditionDefault" class="termConditionDefault">
                        <option value="" term-type="0"><?php echo TABLE_ALL; ?></option>
                        <?php
                        $sqlTC = mysql_query("SELECT id, term_condition_type_id, name FROM term_conditions WHERE is_active = 1");
                        while($rowTC = mysql_fetch_array($sqlTC)){
                        ?>
                        <option value="<?php echo $rowTC['id']; ?>" term-type="<?php echo $rowTC['term_condition_type_id']; ?>"><?php echo $rowTC['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddTermConditionApplyRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveTermConditionApplyRow" style="cursor: pointer;" onmouseover="Tip('Remove')" />
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
<?php echo $this->Form->end(); 
?>