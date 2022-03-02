<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#OtherAging").autoNumeric();
        $("#OtherAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#OtherAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('otherCompanyMem', true);
                if($("#otherCompanyMem").val() == null){
                    alertSelectCompanyOther();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveOther").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackOther").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Other', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Other', 'Add', 1, '');
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
        $("#OtherAddressAdd").focus(function(){
            var value = "";
            if($(this).val() != ""){
                var arrayValue  = $(this).val().split(',');
                var value  = arrayValue[0]+"-"+arrayValue[1];
            }
            var pro_id = $("#AddressProvinceOtherAdd").val();
            var dis_id = $("#AddressDistrictOtherAdd").val();
            var com_id = $("#AddressCommuneOtherAdd").val();
            var vil_id = $("#AddressVillageOtherAdd").val();
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base."/customers/"; ?>address/address,"+value+","+pro_id+","+dis_id+","+com_id+","+vil_id,
                data: "",
                beforeSend: function(){
                    $("#dialog").html('<p style="text-align:center"><img alt="" src="<?php echo $this->webroot; ?>img/loading.gif" /></p>');
                },
                success: function(msg){
                    $(".ui-dialog-buttonpane").show();
                    $("#dialog").html(msg);
                    $("#dialog").dialog("option", "position", "center");
                }
            });
            $("#dialog").dialog({
                title: "<?php echo TABLE_ADDRESS; ?>",
                resizable: false,
                modal: true,
                width: 500,
                height: 'auto',
                position: 'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").hide();
                },
                buttons: {
                    '<?php echo ACTION_SAVE;?>': function() {
                        var formName = "#form_address";
                        var validateBack =$(formName).validationEngine("validate");
                        if(!validateBack){
                            return false;
                        }else{
                            var wordPro = "";
                            var wordDis = "";
                            var wordCom = "";
                            var wordVil = "";
                            var home = "";
                            var street = "";
                            if($("#home_no").val() != ''){
                                var home     = "<?php echo TABLE_HOME_NO; ?>"+$("#home_no").val();
                            }
                            if($("#street").val() != ""){
                                var street   = ",<?php echo TABLE_STREET; ?>"+$("#street").val();
                            }
                            
                            var province = $("#province_id").val();
                            var district = $("#district_id").val();
                            var commune  = $("#commune_id").val();
                            var village  = $("#village_id").val();
                            var textPro  = $("#province_id option[value='"+province+"']").html();
                            var textDis  = $("#district_id option[value='"+district+"']").html();
                            var textCom  = $("#commune_id option[value='"+commune+"']").html();
                            var textVil  = $("#village_id option[value='"+village+"']").html();
                            if(province != ''){
                                wordPro = ",<?php echo TABLE_PROVINCE; ?>"+textPro;
                            }
                            if(district != ''){
                                wordDis = ",<?php echo TABLE_DISTRICT; ?>"+textDis;
                            }
                            if(commune != ''){
                                wordCom = ",<?php echo TABLE_COMMUNE; ?>"+textCom;
                            }
                            if(village != ''){
                                wordVil = ",<?php echo TABLE_VILLAGE; ?>"+textVil;
                            }
                            var fullWord = home+street+wordVil+wordCom+wordDis+wordPro;
                            $("#OtherAddressAdd").val(fullWord);
                            $("#AddressProvinceOtherAdd").val(province);
                            $("#AddressDistrictOtherAdd").val(district);
                            $("#AddressCommuneOtherAdd").val(commune);
                            $("#AddressVillageOtherAdd").val(village);
                            $("#dialog").dialog("close");
                        }
                    }
                }
            });
        });
        $(".btnBackOther").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableOther.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function alertSelectCompanyOther(){
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
                    $(".btnSaveOther").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackOther">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Other', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<fieldset>
    <legend><?php __(MENU_OTHER_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="OtherOtherCode"><?php echo TABLE_CODE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('other_code', array('class' => 'validate[required]','value'=>$code)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td width="30%"><label for="OtherNameAdd"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'id' => 'OtherNameAdd')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="OtherBusinessNumberAdd"><?php echo TABLE_TELEPHONE_WORK; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('business_number', array( 'id' => 'OtherBusinessNumberAdd')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="OtherPersonalNumberAdd"><?php echo TABLE_TELEPHONE_PERSONAL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('personal_number', array('id' => 'OtherPersonalNumberAdd')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="OtherOtherNumberAdd"><?php echo TABLE_TELEPHONE_OTHER; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('other_number', array('id' => 'OtherOtherNumberAdd')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="OtherFaxNumberAdd"><?php echo TABLE_FAX; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('fax_number', array('id' => 'OtherFaxNumberAdd')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="OtherEmailAddressAdd"><?php echo TABLE_EMAIL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('email_address', array('id' => 'OtherEmailAddressAdd', 'class' => 'validate[optional,custom[email]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="OtherAddressAdd"><?php echo TABLE_ADDRESS; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <input type="hidden" value="" id="AddressProvinceOtherAdd" name="data[Other][province_id]" />
                    <input type="hidden" value="" id="AddressDistrictOtherAdd" name="data[Other][district_id]" />
                    <input type="hidden" value="" id="AddressCommuneOtherAdd" name="data[Other][commune_id]" />
                    <input type="hidden" value="" id="AddressVillageOtherAdd" name="data[Other][village_id]" />
                    <?php echo $this->Form->textarea('address', array('id' => 'OtherAddressAdd')); ?>
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
                <select id="otherCompanyAvble" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,name FROM companies WHERE is_active = 1 AND id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('otherCompanyAvble', 'otherCompanyMem')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('otherCompanyMem', 'otherCompanyAvble')" />
            </td>
            <td style="vertical-align: top;">
                <select id="otherCompanyMem" name="data[company_id][]" multiple="multiple" style="width: 300px; height: 200px;">

                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveOther">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveOther"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>