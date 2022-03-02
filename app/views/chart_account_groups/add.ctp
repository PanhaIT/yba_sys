<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ChartAccountGroupAddForm").validationEngine();
        $("#ChartAccountGroupAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveChartAccountGroup").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackChartAccountGroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Chart Account Group', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Chart Account Group', 'Add', 1, '');
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
        $("#ChartAccountGroupIsDepreciation").hide();
        $("#ChartAccountGroupChartAccountTypeId").change(function(){
            if($("#ChartAccountGroupChartAccountTypeId").val()==13){
                $("#ChartAccountGroupIsDepreciation").show();
            }else{
                $("#ChartAccountGroupIsDepreciation").hide();
            }
        });
        $(".btnBackChartAccountGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableChartAccountGroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackChartAccountGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('ChartAccountGroup'); ?>
<fieldset>
    <legend><?php __(MENU_CHART_OF_ACCOUNT_GROUP_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="ChartAccountGroupChartAccountTypeId"><?php echo GENERAL_TYPE; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('chart_account_type_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="ChartAccountGroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?></td>
            <td><?php echo $this->Form->input('is_depreciation', array('label' => false, 'options' => $expenseTypes)); ?></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveChartAccountGroup"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>