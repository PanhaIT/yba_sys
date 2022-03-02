<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        $("#GroupEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#GroupEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('userGroupMember', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // Refresh Menu
                refreshMenu();
                // Back
                var rightPanel = $("#GroupEditForm").parent();
                var leftPanel = rightPanel.parent().find(".leftPanel");
                rightPanel.hide();
                rightPanel.html("");
                leftPanel.show("slide", {direction: "left"}, 500);
                oCache.iCacheLower = -1;
                oTableGroup.fnDraw(false);
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>' + result + '</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui) {
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
//        $(".moduleTypeBody").slideUp();
        $(".moduleType").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='btnPlusMinus' /> ");
        
        $("#btnShowAll").unbind('click').click(function(event){
            event.preventDefault();
            $("img.btnPlusMinus").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $(".moduleTypeBody").slideDown();
        });
        
        $("#btnHideAll").unbind('click').click(function(event){
            event.preventDefault();
            $("img.btnPlusMinus").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $(".moduleTypeBody").slideUp();
        });
        
        $(".moduleType").unbind('click').click(function(){
            if($(".moduleTypeBody[title=" + $(this).attr("title") + "]").is(':visible')==false){
                $("img.btnPlusMinus", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.btnPlusMinus", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $(".moduleTypeBody[title=" + $(this).attr("title") + "]").slideToggle();
        });
        
        $(".module").mouseover(function(){
            $(this).css("background", "#f4ffab");
        });
        
        $(".module").mouseout(function(){
            $(this).css("background", "none");
        });
        
        $(".btnFullRights").unbind('change').change(function(){
            if($(this).is(":checked")){
                $(".moduleType" + $(this).attr("moduleId")).attr('checked', true);
            } else {
                $(".moduleType" + $(this).attr("moduleId")).attr('checked', false);
            }
        });
        
        $(".moduleCheck").unbind("click").click(function(){
            var alt = $(this).attr('alt');
            var num = replaceNum($("input[alt='ModuleMain"+alt+"']").attr('num'));
            var max = replaceNum($("input[alt='ModuleMain"+alt+"']").attr('max'));
            if($(this).is(":checked")){
                if((num - 1) == 0){
                    $("input[alt='ModuleMain"+alt+"']").prop("indeterminate", false).attr('checked', true);
                } else {
                    $("input[alt='ModuleMain"+alt+"']").prop("indeterminate", true);
                }
                $("input[alt='ModuleMain"+alt+"']").attr('num', (num - 1));
            } else {
                if(max == (num + 1)){
                    $("input[alt='ModuleMain"+alt+"']").prop("indeterminate", false).attr('checked', false);
                } else {
                    $("input[alt='ModuleMain"+alt+"']").attr("checked", false).prop('indeterminate', true);
                }
                $("input[alt='ModuleMain"+alt+"']").attr('num', (num + 1));
            }
        });
        
        $(".btnBackGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableGroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Check Box Module Rigth
        // $(".btnFullRights").each(function(){
        //     var obj = $(this);
        //     var alt = obj.attr('moduleId');
        //     var num = replaceNum(obj.attr('num'));
        //     var max = replaceNum(obj.attr('max'));
        //     var checked = 0;
        //     $("input[alt='"+alt+"']").each(function(){
        //         if($(this).is(":checked")){
        //             checked++;
        //             num--;
        //         }
        //     });
        //     if(checked > 0){
        //         if(checked == max){
        //             obj.attr('checked', true);
        //         } else {
        //             obj.prop('indeterminate', true);
        //         }
        //     }
        //     obj.attr('num', num);
        // });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Group'); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->hidden('sys_code'); ?>
<fieldset>
    <legend><?php __(MENU_GROUP_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="GroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(GENERAL_MEMBER); ?></legend>
    <table>
        <tr>
            <th>Available Users:</th>
            <th></th>
            <th>Member of Group:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userGroupAvailable" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_groups WHERE group_id=" . $this->data['Group']['id'] . ")");
                    while ($dataSource = mysql_fetch_array($querySource)) {
                        ?>
                        <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userGroupAvailable', 'userGroupMember')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userGroupMember', 'userGroupAvailable')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userGroupMember" name="data[Group][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=user_groups.user_id) AS full_name FROM user_groups WHERE user_id NOT IN (SELECT id FROM users WHERE is_active!=1) AND group_id=" . $this->data['Group']['id']);
                    while ($dataDestination = mysql_fetch_array($queryDestination)) {
                        ?>
                        <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(GENERAL_PERMISSION); ?> (<a href="" id="btnShowAll">show all</a> | <a href="" id="btnHideAll">hide all</a>)</legend>
    <input type="hidden" name="module1" value="1" />
    <?php
    $groupBy = '';
    $queryType = mysql_query("SELECT id,name,group_by FROM module_types WHERE status = 1 ORDER BY ordering");
    while ($dataType = mysql_fetch_array($queryType)) {
        $rand = rand();
        $queryModule = mysql_query("SELECT id, name, (SELECT COUNT(module_id) FROM permissions WHERE module_id=m.id AND group_id=" . $this->data['Group']['id'] . ") AS chk FROM modules m WHERE m.status = 1 AND module_type_id=" . $dataType['id'] . " ORDER BY ordering");
        if($groupBy != $dataType['group_by']){
        ?>
        <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px; margin-top: 10px;"><?php echo $dataType['group_by']; ?></div>
        <?php
            $groupBy = $dataType['group_by'];
        }
        ?>
        <div>
            <div class="moduleType" style="font-size: 12px; float: left; width: 95%; margin-left: 10px;" title="<?php echo $rand; ?>"><?php echo $dataType['name']; ?></div><input type="checkbox" max="<?php echo mysql_num_rows($queryModule); ?>" num="<?php echo mysql_num_rows($queryModule); ?>" style="float: left; margin-left: 22px;" class="btnFullRights" moduleId="<?php echo $dataType['id']; ?>" alt="ModuleMain<?php echo $dataType['id']; ?>" />
            <div style="clear: both;"></div>
        </div>
        <div class="moduleTypeBody" style="padding-left: 10px; display: none;" title="<?php echo $rand; ?>">
            <?php
            while ($dataModule = mysql_fetch_array($queryModule)) {
                ?>
                <div class="module">
                    <div style="float: left;"><?php echo $dataModule['name']; ?></div>
                    <div style="float: right;"><input type="checkbox" name="module<?php echo $dataModule['id']; ?>" class="moduleType<?php echo $dataType['id']; ?> moduleCheck" alt="<?php echo $dataType['id']; ?>" <?php echo $dataModule['id'] == 1 || $dataModule['chk'] != 0 ? 'checked="checked"' : '' ?> /></div>
                    <div style="clear: both;"></div>
                </div>
            <?php } ?>
        </div>
        <?php
    }
    ?>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>