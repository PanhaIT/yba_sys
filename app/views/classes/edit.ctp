<?php

// check if class and it's child already in used
$classList=array();
$classList[]=$this->data['Class']['id'];
$queryChild[0]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$this->data['Class']['id']);
if(mysql_num_rows($queryChild[0])){
    while($dataChild[0]=mysql_fetch_array($queryChild[0])){
        $classList[]=$dataChild[0]['id'];
        $queryChild[1]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[0]['id']);
        if(mysql_num_rows($queryChild[1])){
            while($dataChild[1]=mysql_fetch_array($queryChild[1])){
                $classList[]=$dataChild[1]['id'];
                $queryChild[2]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[1]['id']);
                if(mysql_num_rows($queryChild[2])){
                    while($dataChild[2]=mysql_fetch_array($queryChild[2])){
                        $classList[]=$dataChild[2]['id'];
                        $queryChild[3]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[2]['id']);
                        if(mysql_num_rows($queryChild[3])){
                            while($dataChild[3]=mysql_fetch_array($queryChild[3])){
                                $classList[]=$dataChild[3]['id'];
                                $queryChild[4]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[3]['id']);
                                if(mysql_num_rows($queryChild[4])){
                                    while($dataChild[4]=mysql_fetch_array($queryChild[4])){
                                        $classList[]=$dataChild[4]['id'];
                                        $queryChild[5]=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$dataChild[4]['id']);
                                        if(mysql_num_rows($queryChild[5])){
                                            while($dataChild[5]=mysql_fetch_array($queryChild[5])){
                                                $classList[]=$dataChild[5]['id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
$queryGl=mysql_query("SELECT gl.id FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE is_active=1 AND class_id IN (" . implode(",", $classList) . ")");
$notAllowEdit=mysql_num_rows($queryGl);

?>
<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ClassEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ClassEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('classCompanyMemEdit', true);
                if($("#classCompanyMemEdit").val() == null){
                    alertSelectCompanyClass();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackClass").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Class', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Class', 'Edit', 1, '');
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
        $(".btnBackClass").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableClass.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function alertSelectCompanyClass(){
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
                    $(".btnSaveClass").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackClass">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php
echo $this->Form->create('Class'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(MENU_CLASS_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="ClassParentId"><?php echo CLASS_PARENT; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <select id="ClassParentId" name="data[Class][parent_id]" <?php echo $notAllowEdit?'disabled="disabled"':''; ?>>
                        <option value=""></option>
                        <?php
                        $query[0]=mysql_query("SELECT id,name FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                        while($data[0]=mysql_fetch_array($query[0])){?>
                        <option value="<?php echo $data[0]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[0]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id']?'disabled="disabled"':'' ?>><?php echo $data[0]['name']; ?></option>
                            <?php
                            $query[1]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data[1]=mysql_fetch_array($query[1])){?>
                            <option value="<?php echo $data[1]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[1]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id'] || $this->data['Class']['id']==$data[1]['id']?'disabled="disabled"':'' ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                <?php
                                $query[2]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                while($data[2]=mysql_fetch_array($query[2])){?>
                                <option value="<?php echo $data[2]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[2]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id'] || $this->data['Class']['id']==$data[1]['id'] || $this->data['Class']['id']==$data[2]['id']?'disabled="disabled"':'' ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                    <?php
                                    $query[3]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                    while($data[3]=mysql_fetch_array($query[3])){?>
                                    <option value="<?php echo $data[3]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[3]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id'] || $this->data['Class']['id']==$data[1]['id'] || $this->data['Class']['id']==$data[2]['id'] || $this->data['Class']['id']==$data[3]['id']?'disabled="disabled"':'' ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                        <?php
                                        $query[4]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                        while($data[4]=mysql_fetch_array($query[4])){?>
                                        <option value="<?php echo $data[4]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[4]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id'] || $this->data['Class']['id']==$data[1]['id'] || $this->data['Class']['id']==$data[2]['id'] || $this->data['Class']['id']==$data[3]['id'] || $this->data['Class']['id']==$data[4]['id']?'disabled="disabled"':'' ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                            <?php
                                            $query[5]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                            while($data[5]=mysql_fetch_array($query[5])){?>
                                            <option value="<?php echo $data[5]['id']; ?>" <?php echo $this->data['Class']['parent_id']==$data[5]['id']?'selected="selected"':'' ?> <?php echo $this->data['Class']['id']==$data[0]['id'] || $this->data['Class']['id']==$data[1]['id'] || $this->data['Class']['id']==$data[2]['id'] || $this->data['Class']['id']==$data[3]['id'] || $this->data['Class']['id']==$data[4]['id'] || $this->data['Class']['id']==$data[5]['id']?'disabled="disabled"':'' ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ClassName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="ClassDescription"><?php echo GENERAL_DESCRIPTION; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description'); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
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
                <select id="classCompanyAvbleEdit" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,name FROM companies WHERE is_active = 1 AND id NOT IN (SELECT company_id FROM class_companies WHERE class_id = ".$this->data['Class']['id'].") AND id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('classCompanyAvbleEdit', 'classCompanyMemEdit')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('classCompanyMemEdit', 'classCompanyAvbleEdit')" />
            </td>
            <td style="vertical-align: top;">
                <select id="classCompanyMemEdit" name="data[company_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryDestination=mysql_query("SELECT DISTINCT company_id,(SELECT name FROM companies WHERE id=class_companies.company_id) AS company_name FROM class_companies WHERE company_id NOT IN (SELECT id FROM companies WHERE is_active !=1 ) AND class_id=".$this->data['Class']['id']);
                    while($dataDestination=mysql_fetch_array($queryDestination)){
                    ?>
                    <option value="<?php echo $dataDestination['company_id']; ?>"><?php echo $dataDestination['company_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveClass">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>