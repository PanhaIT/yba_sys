<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var eGroupRequestEmployee = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen({width: 424});
        $("#EgroupEditForm").validationEngine();
        $("#EgroupEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                <?php
                if(count($companies) > 1){
                ?>
                listbox_selectall('employee_id', true);
                if($("#EgroupCompanyId_chzn").find(".chzn-choices").find(".search-choice").text() == ""){
                    alertSelectCompanyEgroup();
                    return false;
                }
                <?php
                }
                ?>
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackEmployeeGroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Egroup', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Egroup', 'Edit', 1, '');
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
        $(".searchEmployee").click(function(){
            if(eGroupRequestEmployee != null){
                eGroupRequestEmployee.abort();
            }
            var companyId = getCompanyListEgroup();
            if(companyId != ''){
                eGroupRequestEmployee = $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(msg){
                                                eGroupRequestEmployee = null;
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $("#dialog").html(msg).dialog({
                                                    title: '<?php echo MENU_EMPLOYEE_INFO; ?>',
                                                    resizable: false,
                                                    modal: true,
                                                    width: 800,
                                                    height: 500,
                                                    position:'center',
                                                    open: function(event, ui){
                                                        $(".ui-dialog-buttonpane").show();
                                                    },
                                                    buttons: {
                                                        '<?php echo ACTION_OK; ?>': function() {
                                                            $("input[name='chkEmployee']:checked").each(function(){
                                                                addSelect($(this).val());
                                                            });
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            }
                                        });
            }
        });
        
        $("#EgroupEmployee").autocomplete("<?php echo $this->base . "/employees/searchEmployee"; ?>", {
            width: 410,
            max: 10,
            highlight: false,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                if(checkCompanyEgroup(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            },
            formatResult: function(data, value) {
                if(checkCompanyEgroup(value.split(".*")[3])){
                    return value.split(".*")[1] + " - " + value.split(".*")[2];
                }
            }
        }).result(function(event, value){
            addSelect(value.toString());
            $(this).val('');
        });
        
        var addSelect =  function (value){
            var employeeId   = value.split(".*")[0];
            var employeeCode = value.split(".*")[1];
            var employeeName = value.split(".*")[2];
            var companyId    = value.split(".*")[3];
            if(!checkValueIfExist(employeeId) && checkCompanyEgroup(companyId)){
                $("#employee_id").append('<option value="'+employeeId+'" rel="'+employeeName+'" >'+employeeCode+ " - "+employeeName+'</option>');
            }
        };
        
        var checkValueIfExist = function(value){
            var result = false;
            $('#employee_id').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        };
        
        $("#btnMinusEmployeeGroup").click(function(){
            $('#employee_id option:selected').remove();
            return false;
        });
        
        $(".btnBackEmployeeGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableEmployeeGroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#EgroupCompanyId_chzn").click(function(){
            $('#employee_id option').remove();
        });
    });
    
    function checkCompanyEgroup(companyId){
        var companyReturn = false;
        $("#EgroupCompanyId_chzn").find(".chzn-choices").find(".search-choice").each(function(){
            var obj = $(this);
            $("#EgroupCompanyId").find("option").each(function(){
                if($(this).text() == obj.text()){
                    if($(this).val() == companyId){
                        companyReturn = true;
                    }
                }
            });
        });
        return companyReturn;
    }
    
    function getCompanyListEgroup(){
        var companySelect = '';
        var index = 0;
        $("#EgroupCompanyId_chzn").find(".chzn-choices").find(".search-choice").each(function(){
            var obj = $(this);
            $("#EgroupCompanyId").find("option").each(function(){
                if($(this).text() == obj.text()){
                    if(index > 0){
                        companySelect += ",";
                    }
                    companySelect += $(this).val();
                    index++;
                }
            });
        });
        return companySelect;
    }
    
    function alertSelectCompanyEgroup(){
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
                    $(".btnSaveEgroup").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackEmployeeGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Egroup'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Egroup][company_id][]" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_EMPLOYEE_GROUP_INFO); ?></legend>
    <table>
        <?php
        if(count($companies) > 1){
        ?>
        <tr>
            <th style="vertical-align: top;"><label for="EgroupCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></th>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('company_id', array('selected' => $companySellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 424px;')); ?>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <th><label for="EgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></th>
            <td><?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?></td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(GENERAL_MEMBER); ?></legend>
    <table>
        <tr>
            <th><?php echo MENU_EMPLOYEE; ?></th>
            <td>
                <?php echo $this->Form->text('employee', array('id' => 'EgroupEmployee')); ?>
                <img alt="Search" align="absmiddle" style="cursor: pointer;"class="searchEmployee" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">
                <select id="employee_id" name="data[Egroup][employee_id][]" multiple="multiple" style="width: 420px; height: 150px;">
                    <?php
                    $query = "SELECT DISTINCT employees.id, employees.employee_code, CONCAT(employees.name) AS fullname FROM employees
                                INNER JOIN employee_egroups ON employees.id = employee_egroups.employee_id
                                WHERE employees.is_active=1 AND employee_egroups.egroup_id=" . $this->data['Egroup']['id'];
                    $querySource = mysql_query($query);
                    while ($dataSource = mysql_fetch_array($querySource)) {
                    ?>
                        <option value="<?php echo $dataSource['id']; ?>" rel="<?php echo $dataSource['fullname']; ?>" ><?php echo $dataSource['employee_code'] . " - " . $dataSource['fullname']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">
                <div class="buttons">
                    <button type="submit" id="btnMinusEmployeeGroup" class="negative">
                        <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                        <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                    </button>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveEgroup">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>