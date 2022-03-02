<?php
$notAllowEdit = 0;
?>
<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ChartAccountEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#ChartAccountEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveChartAccount").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackChartAccount").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Chart Account', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Chart Account', 'Edit', 1, '');
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
        $("#ChartAccountChartAccountGroupId").live("change", function(){
            var province_id = $(this).find("option:selected").attr("class");
            $("#ChartAccountChartAccountTypeId").val(province_id);
        });
        $("#ChartAccountChartAccountTypeId").live("change", function(){
            var province_id = $(this).val();
            $("#ChartAccountChartAccountGroupId").find("option").each(function(){
                if($(this).attr("class")== province_id){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
            $("#ChartAccountChartAccountGroupId").val("");
        });
        $(".btnBackChartAccount").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableChartAccount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackChartAccount">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('ChartAccount');  
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<input type="hidden" name="data[ChartAccount][company_id][]" value="1" />
<fieldset>
    <legend><?php __(MENU_CHART_OF_ACCOUNT_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="ChartAccountChartAccountTypeId"><?php echo TABLE_ACCOUNT_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('chart_account_type_id', array('label' => false, 'class'=>'validate[required]','disabled'=>($notAllowEdit?true:false))); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ChartAccountChartAccountGroupId"><?php echo TABLE_ACCOUNT_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('chart_account_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]','disabled'=>($notAllowEdit?true:false))); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ChartAccountParentId"><?php echo ACCOUNT_PARENT; ?>:</label></td>
            <td>
                <select id="ChartAccountParentId" name="data[ChartAccount][parent_id]">
                    <option value=""></option>
                    <?php
                    $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                    while($data[0]=mysql_fetch_array($query[0])){?>
                    <option value="<?php echo $data[0]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[0]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id']?'disabled="disabled"':'' ?>><?php echo $data[0]['name']; ?></option>
                        <?php
                        $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                        while($data[1]=mysql_fetch_array($query[1])){?>
                        <option value="<?php echo $data[1]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[1]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id'] || $this->data['ChartAccount']['id']==$data[1]['id']?'disabled="disabled"':'' ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                            <?php
                            $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                            while($data[2]=mysql_fetch_array($query[2])){?>
                            <option value="<?php echo $data[2]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[2]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id'] || $this->data['ChartAccount']['id']==$data[1]['id'] || $this->data['ChartAccount']['id']==$data[2]['id']?'disabled="disabled"':'' ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                <?php
                                $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                                while($data[3]=mysql_fetch_array($query[3])){?>
                                <option value="<?php echo $data[3]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[3]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id'] || $this->data['ChartAccount']['id']==$data[1]['id'] || $this->data['ChartAccount']['id']==$data[2]['id'] || $this->data['ChartAccount']['id']==$data[3]['id']?'disabled="disabled"':'' ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                    <?php
                                    $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                                    while($data[4]=mysql_fetch_array($query[4])){?>
                                    <option value="<?php echo $data[4]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[4]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id'] || $this->data['ChartAccount']['id']==$data[1]['id'] || $this->data['ChartAccount']['id']==$data[2]['id'] || $this->data['ChartAccount']['id']==$data[3]['id'] || $this->data['ChartAccount']['id']==$data[4]['id']?'disabled="disabled"':'' ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                        <?php
                                        $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 AND chart_account_type_id = 13 ORDER BY account_codes");
                                        while($data[5]=mysql_fetch_array($query[5])){?>
                                        <option value="<?php echo $data[5]['id']; ?>" <?php echo $this->data['ChartAccount']['parent_id']==$data[5]['id']?'selected="selected"':'' ?> <?php echo $this->data['ChartAccount']['id']==$data[0]['id'] || $this->data['ChartAccount']['id']==$data[1]['id'] || $this->data['ChartAccount']['id']==$data[2]['id'] || $this->data['ChartAccount']['id']==$data[3]['id'] || $this->data['ChartAccount']['id']==$data[4]['id'] || $this->data['ChartAccount']['id']==$data[5]['id']?'disabled="disabled"':'' ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="ChartAccountAccountCode"><?php echo TABLE_ACCOUNT_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('account_codes', array('class'=>'validate[required,custom[integer]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="ChartAccountAccountDescription"><?php echo TABLE_ACCOUNT_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('account_description', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="ChartAccountManual"><?php echo TABLE_MANUAL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('manual'); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<!--<fieldset>
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="s" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,name FROM companies WHERE is_active=1 AND id NOT IN (SELECT company_id FROM chart_account_companies WHERE chart_account_id=".$this->data['ChartAccount']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('s', 'd')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('d', 's')" />
            </td>
            <td style="vertical-align: top;">
                <select id="d" name="data[ChartAccount][company_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryDestination=mysql_query("SELECT DISTINCT company_id,(SELECT name FROM companies WHERE id=chart_account_companies.company_id) AS company_name FROM chart_account_companies WHERE chart_account_id=".$this->data['ChartAccount']['id']);
                    while($dataDestination=mysql_fetch_array($queryDestination)){
                    ?>
                    <option value="<?php echo $dataDestination['company_id']; ?>"><?php echo $dataDestination['company_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />-->
<div class="buttons">
    <button type="submit" class="positive btnSaveChartAcc">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveChartAccount"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>