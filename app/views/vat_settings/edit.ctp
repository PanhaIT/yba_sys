<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        refreshModuleVat();
        $("#VatSettingEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $(".float").autoNumeric({mDec: 2, aSep: ',', mNum: 2});
        $("#VatSettingEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('VatSettingApply', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVATSetting").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVatSetting").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Vat Setting', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Vat Setting', 'Edit', 1, '');
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
        
        $(".btnBackVatSetting").click(function(event){
            event.preventDefault();
            $("#VatSettingEditForm").validationEngine("hideAll");
            oCache.iCacheLower = -1;
            oTableVatSetting.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#VatSettingType").change(function(){
            checkModuleApplyVat();
        });
        
    });
    
    function refreshModuleVat(){
        var vatType = $("#VatSettingType").val();
        $("#VatSettingApplyTo").filterOptions('value', '', '');
        if(vatType == 1){
            $("#VatSettingApplyTo option").each(function(){
                if($(this).val() != 48 && $(this).val() != 21 && $(this).val() != 41){
                    $("#VatSettingApplyTo").showHideDropdownOptions($(this).val(), true);
                }
            });
        } else if(vatType == 2) {
            $("#VatSettingApplyTo option").each(function(){
                if($(this).val() == 48 || $(this).val() == 21 || $(this).val() == 41){
                    $("#VatSettingApplyTo").showHideDropdownOptions($(this).val(), true);
                }
            });
        }
        $("#VatSettingApply option").each(function(){
            $("#VatSettingApplyTo option[value='"+$(this).val()+"']").remove();
        });
    }
    
    function checkModuleApplyVat(){
        var vatType = $("#VatSettingType").val();
        $('#VatSettingApply option').attr("selected", true);
        listbox_moveacross('VatSettingApply', 'VatSettingApplyTo');
        $("#VatSettingApplyTo").find("option").removeAttr("selected");
        $("#VatSettingApplyTo").filterOptions('value', '', '');
        if(vatType == 1){
            $("#VatSettingApplyTo option").each(function(){
                if($(this).val() != 48 && $(this).val() != 21 && $(this).val() != 41){
                    $("#VatSettingApplyTo").showHideDropdownOptions($(this).val(), true);
                }
            });
        } else if(vatType == 2) {
            $("#VatSettingApplyTo option").each(function(){
                if($(this).val() == 48 || $(this).val() == 21 || $(this).val() == 41){
                    $("#VatSettingApplyTo").showHideDropdownOptions($(this).val(), true);
                }
            });
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVatSetting">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('VatSetting', array('inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[VatSetting][company_id]" id="VatSettingCompanyId" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_VAT_SETTING_INFO); ?></legend>
    <table cellpadding="5" cellspacing="0">
        <tr>
            <td><label for="VatSettingType"><?php echo TABLE_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[VatSetting][type]" id="VatSettingType" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['VatSetting']['type'] == 1){ ?>selected="selected"<?php } ?>>Sales</option>
                        <option value="2" <?php if($this->data['VatSetting']['type'] == 2){ ?>selected="selected"<?php } ?>>Purchase</option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VatSettingName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('name', array('class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="VatSettingVatPercent"><?php echo TABLE_PERCENT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('vat_percent', array('class' => 'validate[required] float', 'style' => 'width: 80px;')); ?> (%)
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="VatSettingApplyTo"><?php echo TABLE_DEFAULT_FOR; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select id="VatSettingApplyTo" multiple="multiple" style="width: 200px; height: 150px; float: left;">
                        <option value="34"><?php echo MENU_POS; ?></option>
                        <option value="68"><?php echo MENU_QUOTATION; ?></option>
                        <option value="69"><?php echo MENU_ORDER; ?></option>
                        <option value="25"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?></option>
                        <option value="40"><?php echo MENU_CREDIT_MEMO_MANAGEMENT; ?></option>
                        <option value="48"><?php echo MENU_PURCHASE_REQUEST_MANAGEMENT; ?></option>
                        <option value="21"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?></option>
                        <option value="41"><?php echo MENU_PURCHASE_RETURN_MANAGEMENT; ?></option>
                    </select>
                    <div style="width: 20px; float: left; margin-left: 5px;">
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('VatSettingApplyTo', 'VatSettingApply')" />
                        <br /><br />
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('VatSettingApply', 'VatSettingApplyTo')" />
                    </div>
                    <select id="VatSettingApply" name="data[VatSetting][apply_to][]" multiple="multiple" style="width: 200px; height: 150px; float: left;">
                        <?php
                        $queryModule = mysql_query("SELECT apply_to FROM vat_modules WHERE vat_setting_id = ".$this->data['VatSetting']['id']." AND is_active = 1");
                        while($dataModule = mysql_fetch_array($queryModule)){
                        ?>
                        <?php if($dataModule['apply_to'] == 34){ ?><option value="34"><?php echo MENU_POS; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 68){ ?><option value="68"><?php echo MENU_QUOTATION; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 69){ ?><option value="69"><?php echo MENU_ORDER; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 25){ ?><option value="25"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 40){ ?><option value="40"><?php echo MENU_CREDIT_MEMO_MANAGEMENT; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 48){ ?><option value="48"><?php echo MENU_PURCHASE_REQUEST_MANAGEMENT; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 21){ ?><option value="21"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?></option><?php } ?>
                        <?php if($dataModule['apply_to'] == 41){ ?><option value="41"><?php echo MENU_PURCHASE_RETURN_MANAGEMENT; ?></option><?php } ?>
                        <?php } ?>
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
        <span class="txtSaveVATSetting"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>