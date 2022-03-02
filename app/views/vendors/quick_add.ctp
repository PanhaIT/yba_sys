<?php 
// Authentication
$this->element('check_access');
$allowAddVgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addVgroup');
$allowAddTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'addTerm');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        <?php
        if($allowAddVgroup){
        ?>
        $("#VendorVgroupId").chosen({ width: 250, allow_add: true, allow_add_label: '<?php echo MENU_VENDOR_GROUP_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewVgroup' });
        $("#addNewVgroup").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/addVgroup/"; ?>",
                beforeSend: function(){
                    $("#VendorVgroupId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog1").html(msg);
                    $("#dialog1").dialog({
                        title: '<?php echo MENU_VENDOR_GROUP_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '200',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#VgroupAddVgroupForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/vendors/addVgroup",
                                        data: $("#VgroupAddVgroupForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Vendor', 'Quick Add Vgroup', 2, result);
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            $("#dialog1").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position:'center',
                                                closeOnEscape: true,
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Vendor', 'Quick Add Vgroup', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#VendorVgroupId").html(result.option);
                                                $("#VendorVgroupId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog1").dialog({
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
                                        }
                                    });
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#VendorVgroupId").chosen({ width: 250});
        <?php
        }
        if($allowAddTerm){
        ?>
        $("#VendorPaymentTermId").chosen({ width: 250, allow_add: true, allow_add_label: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewTerm' });
        $("#addNewTerm").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/addTerm/"; ?>",
                beforeSend: function(){
                    $("#VendorPaymentTermId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog1").html(msg);
                    $("#dialog1").dialog({
                        title: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '200',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#PaymentTermAddTermForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/vendors/addTerm",
                                        data: $("#PaymentTermAddTermForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Vendor', 'Quick Add Term', 2, result);
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            $("#dialog1").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position:'center',
                                                closeOnEscape: true,
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Vendor', 'Quick Add Term', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#VendorPaymentTermId").html(result.option);
                                                $("#VendorPaymentTermId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog1").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog1").dialog({
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
                                        }
                                    });
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#VendorPaymentTermId").chosen({ width: 250 });
        <?php
        }
        ?>
    });
</script>
<br />
<?php 
echo $this->Form->create('Vendor', array('inputDefaults' => array('div' => false, 'label' => false)));
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Vendor][company_id]" id="VendorCompanyId" />
<?php
}
?>
<table style="width: 100%;">
    <?php
    if(count($companies) > 1){
    ?>
    <tr>
        <td><label for="VendorCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('company_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;')); ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td><label for="VendorVendorCode"><?php echo TABLE_VENDOR_NUMBER; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('vendor_code', array('class' => 'validate[required]', 'value' => $code, 'style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td width="30%"><label for="VendorName"><?php echo TABLE_VENDOR_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="VendorVgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('vgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="VendorPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('payment_term_id', array('class' => 'validate[required]', 'empty' => INPUT_SELECT, 'label' => false)); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td width="30%"><label for="VendorCountryId"><?php echo TABLE_COUNTRY; ?> :</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->input('country_id', array('empty' => INPUT_SELECT, 'label' => false, 'style' => 'text-transform: uppercase;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="VendorWorkTelephone"><?php echo TABLE_TELEPHONE_WORK; ?>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('work_telephone', array('style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="VendorFaxNumber"><?php echo TABLE_FAX; ?>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('fax_number', array('style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="VendorEmail"><?php echo TABLE_EMAIL; ?>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('email_address', array('style' => 'width: 410px;', 'class' => 'validate[optional,custom[email]]')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;"><label for="VendorAddress"><?php echo TABLE_ADDRESS; ?>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->textarea('address', array('style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;"><label for="VendorNote"><?php echo TABLE_NOTE; ?>:</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->textarea('note', array('style' => 'width: 410px;')); ?>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>

