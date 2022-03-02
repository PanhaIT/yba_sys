<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
$dateMaxDob = date("d/m/Y", strtotime(date("Y-m-d", strtotime($dateNow)) . " -180 month"));
?>
<script type="text/javascript">
    var jcrop_api='';
    var x,y,x2,y2,w,h;
    var obj;
    function showCoords(c)
    {
        x=c.x;
        y=c.y;
        x2=c.x2;
        y2=c.y2;
        w=c.w;
        h=c.h;
    };
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        generalEmployeeCode('EMP');
        $(".chzn-select").chosen({width: 260});
        $("#EmployeeAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        // Action Save
        $("#EmployeeAddForm").ajaxForm({
            beforeSerialize: function($form, options) { 
                if($("#EmployeeCompanyId").val() == "" || $("#EmployeeCompanyId").val() == null){
                    alertSelectCompanyEmp();
                    return false;
                }
                $('#EmployeeDob, #EmployeeStartWorkingDate, #EmployeeTerminationDate').datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtEmployeeSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackEmployee").click();
                // Alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Employee', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Employee', 'Add', 1, '');
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
        // Action Save Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#EmployeePhoto").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // Alert Message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var photoFolder='';
                var photoName=result;
                photoFolder="public/employee_photo/tmp/";
                $('#<?php echo $cropPhoto; ?>').attr("src", "<?php echo $this->webroot; ?>" + photoFolder + photoName + "?" + Math.random());
                if(jcrop_api==''){
                    $('#<?php echo $cropPhoto; ?>').Jcrop({
                        setSelect: [0,0,10000,10000],
                        allowSelect: false,
                        onChange:   showCoords,
                        onSelect:   showCoords
                    },function(){
                        jcrop_api = this;
                    });
                }else{
                    jcrop_api.setImage("<?php echo $this->webroot; ?>" + photoFolder + photoName);
                    jcrop_api.setSelect([0,0,10000,10000]);
                }
                $("#<?php echo $dialogPhoto; ?>").dialog({
                    title: 'Crop Image',
                    resizable: false,
                    modal: true,
                    width: '90%',
                    height: '400',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        'Crop': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/employees/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h,
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    $("#photoDisplayEmployee").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#<?php echo $photoNameHidden; ?>").val(result);
                                }
                            });
                        }
                    }
                });
            }
        });
        
        // Action Photo Submit
        $("#EmployeePhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        // Action Back
        $(".btnBackEmployee").click(function(event){
            event.preventDefault();
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableEmployee.fnDraw(false);
        });
        // Action Address
        // Province
        $(".province").click(function(){
            if($(this).val()!=""){
                $(".district").val('');
                $(".district option[class!='']").hide();
                $(".district option[class='"  + $(this).val() + "']").show();
            }else{
                $(".district").val('');
                $(".commune").val('');
                $(".village").val('');
                $(".district option").show();
                $(".commune option").show();
                $(".village option").show();
            }
            comboRefesh(".district",".province");
        });
        // District
        $(".district").change(function(){
            if($(this).val()!=""){
                $(".province").val($(".district").find("option:selected").attr("class"));
                $(".commune").val('');
                $(".commune option[class!='']").hide();
                $(".commune option[class='"  + $(this).val() + "']").show();
            }else{
                $(".commune").val('');
                $(".village").val('');
                $(".commune option").show();
                $(".village option").show();
            }
            comboRefesh(".commune",".district");
        });
        // Commune
        $(".commune").change(function(){
            if($(this).val()!=""){
                $(".village").val('');
                $(".village option[class!='']").hide();
                $(".village option[class='"  + $(this).val() + "']").show();
            }else{
                $(".village").val('');
                $(".village option").show();
            }
        });
        
        $('#EmployeeDob').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0"
        }).unbind("blur");
        
        $("#EmployeeDob").datepicker("option", "minDate", "01/01/1950");
        $("#EmployeeDob").datepicker("option", "maxDate", "<?php echo $dateMaxDob; ?>");
        
        $('#EmployeeStartWorkingDate, #EmployeeTerminationDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: "-40:+0"
        }).unbind("blur");
    });
    
    function generalEmployeeCode(code){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base; ?>/users/smartcode/employees/employee_code/7/" + code.toString().toUpperCase()+"/"+2,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#EmployeeEmployeeCode").val(result.replace("<?php echo date("y"); ?>", ""));
            }
        });
    }
    
    function alertSelectCompanyEmp(){
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
                    $(".btnSaveEmployee").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackEmployee">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_EMPLOYEE_INFO); ?></legend>
    <?php echo $this->Form->create('Employee', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
    <input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Employee][photo]" />
    <div style="width: 35%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="2">
            <tr>
                <td><label for="EmployeeCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('company_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 260px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 40%;"><label for="EmployeeCode"><?php echo TABLE_EMPLOYEE_NUMBER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('employee_code', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeSex"><?php echo TABLE_SEX; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('sex', array('empty' => INPUT_SELECT, 'class' => 'validate[required]', 'label' => false, 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeDob"><?php echo TABLE_DATE_OF_BIRTH; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('dob', array('id' => 'EmployeeDob', 'style' => 'width: 90%;', 'readonly' => true)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeePersonalNumber"><?php echo TABLE_TELEPHONE_PERSONAL; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('personal_number', array('id' => 'EmployeePersonalNumber', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeOtherNumber"><?php echo TABLE_TELEPHONE_OTHER; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('other_number', array('id' => 'EmployeeOtherNumber', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeEmail"><?php echo TABLE_EMAIL; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('email', array('id' => 'EmployeeEmail', 'class' => 'validate[optional,custom[email]]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend><?php echo TABLE_ADDRESS; ?></legend>
                        <table cellpadding="3" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="width: 18%;"><label for="EmployeeHouseNo"><?php echo TABLE_NO; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->text('house_no', array('style' => 'width: 86%;')); ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><label for="EmployeeStreetId"><?php echo TABLE_STREET; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('street_id', array('empty' => INPUT_SELECT, 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><label for="EmployeeProvinceId"><?php echo TABLE_PROVINCE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('province_id', array('empty' => INPUT_SELECT, 'class'=>'province', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><label for="EmployeeDistrictId"><?php echo TABLE_DISTRICT; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('district_id', array('empty' => INPUT_SELECT, 'class'=>'district', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><label for="EmployeeCommuneId"><?php echo TABLE_COMMUNE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('commune_id', array('empty' => INPUT_SELECT, 'class'=>'commune', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><label for="EmployeeVillageId"><?php echo TABLE_VILLAGE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('village_id', array('empty' => INPUT_SELECT,  'class'=>'village', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
        <br />
        <div class="buttons">
            <button type="submit" class="positive btnSaveEmployee">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtEmployeeSave"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
    </div>
    <div style="width: 35%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="2">
            <tr>
                <td style="width: 40%;"><label for="EmployeeEgroupId"><?php echo USER_GROUP; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('egroup_id', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 280px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="EmployeeNameKh"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('name_kh', array('class' => 'validate[required]', 'style' => 'width: 99%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeStartWorkingDate"><?php echo TABLE_START_WORKING_DATE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('start_working_date', array('style' => 'width: 99%;', 'readonly' => true)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeTerminationDate"><?php echo TABLE_TERMINATION_DATE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('termination_date', array('style' => 'width: 99%;', 'readonly' => true)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeePositionId"><?php echo TABLE_POSITION; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('position_id', array('empty' => INPUT_SELECT, 'style' => 'width: 99%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeSalary"><?php echo TABLE_SALARY; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('salary', array('style' => 'width: 99%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeWorkForVendorId"><?php echo TABLE_WORK_FOR_VENDOR; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[Employee][work_for_vendor_id]" id="EmployeeWorkForVendorId" style="width: 99%;">
                            <option code="" value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            foreach($vendors AS $vendor){
                            ?>
                            <option code="<?php echo $vendor['Vendor']['vendor_code']; ?>" value="<?php echo $vendor['Vendor']['id']; ?>"><?php echo $vendor['Vendor']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="EmployeeIsShowInSales"><?php echo TABLE_TYPE; ?> <span class="red">*</span>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[Employee][is_show_in_sales]" id="EmployeeIsShowInSales" class="validate[required]" style="width: 99%;">
                            <!--<option value=""><?php echo INPUT_SELECT; ?></option>-->
                            <!--<option value="0">Office</option>-->
                            <option value="1">Sale Rep</option>
                            <!--<option value="2">Delivery</option>-->
                            <!--<option value="3">Collector</option>-->
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="EmployeeNote"><?php echo TABLE_NOTE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->textarea('note', array('style' => 'width: 99%; height: 145px;')); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php echo $this->Form->end(); ?>
    <div style="width: 29%; vertical-align: top; float: right;">
        <form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/employees/upload/" method="post" enctype="multipart/form-data">
            <table style="width: 100%;" cellpadding="3">
                <tr>
                    <td style="width: 25%; text-align: right;"><label for="EmployeePhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                    <td valign="top"><input type="file" id="EmployeePhoto" name="photo" /></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <img id="photoDisplayEmployee" alt="" src="<?php echo $this->webroot; ?>img/button/no-images.png" style=" max-width: 250px; max-height: 250px;" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<div style="clear: both;"></div>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>