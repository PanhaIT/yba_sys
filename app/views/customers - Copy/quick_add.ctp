<?php 
// Authentication
$this->element('check_access');
$allowAddCgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addCgroup');
$allowAddTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'addTerm');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#CustomerDiscount").autoNumeric({mDec: 2, aSep: ','});
        <?php
        if($allowAddCgroup){
        ?>
        $("#CustomerCgroupId").chosen({ width: 360, allow_add: true, allow_add_label: '<?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewCgroup' });
        $("#addNewCgroup").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/customers/addCgroup/"; ?>",
                beforeSend: function(){
                    $("#CustomerCgroupId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog1").html(msg);
                    $("#dialog1").dialog({
                        title: '<?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_ADD; ?>',
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
                                var formName = "#CgroupAddCgroupForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/customers/addCgroup",
                                        data: $("#CgroupAddCgroupForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Customer', 'Quick Add Vgroup', 2, result);
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
                                            createSysAct('Customer', 'Quick Add Vgroup', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#CustomerCgroupId").html(result.option);
                                                $("#CustomerCgroupId").trigger("chosen:updated");
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
        $("#CustomerCgroupId").chosen({ width: 360 });
        <?php
        }
        if($allowAddTerm){
        ?>
        $("#CustomerPaymentTermId").chosen({ width: 360, allow_add: true, allow_add_label: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewTerm' });
        $("#addNewTerm").click(function(){
            $.ajax({
                type: "GET",
                url:  "<?php echo $this->base . "/customers/addTerm/"; ?>",
                beforeSend: function(){
                    $("#CustomerPaymentTermId").trigger("chosen:close");
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
                                        url: "<?php echo $this->base; ?>/customers/addTerm",
                                        data: $("#PaymentTermAddTermForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Customer', 'Quick Add Term', 2, result);
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
                                            createSysAct('Customer', 'Quick Add Term', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#CustomerPaymentTermId").html(result.option);
                                                $("#CustomerPaymentTermId").trigger("chosen:updated");
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
        $("#CustomerPaymentTermId").chosen({ width: 360 });
        <?php
        }
        ?>
        
        // Customer Name
        $("#CustomerName").blur(function(){
            $("#CustomerNameKh").val($(this).val());
        });
        
        $("#CustomerNameKh").focus(function(){
            $(this).select().focus();
        });
        
        // Discount 
        $("#CustomerDiscount").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        $("#CustomerDiscount").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });
    });
</script>
<br />
<?php 
echo $this->Form->create('Customer', array('inputDefaults' => array('div' => false, 'label' => false))); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Customer][company_id]" id="CustomerCompanyId" />
<?php
}
?>
<table style="width: 99%;">
    <tr>
        <td><label for="CustomerCode"><?php echo TABLE_CUSTOMER_NUMBER; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('customer_code', array('class' => 'validate[required]', 'style' => 'width: 90%;', 'value' => $code)); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerName"><?php echo TABLE_NAME_IN_ENGLISH; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerNameKh"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name_kh', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 25%;"><label for="CustomerCgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('cgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerMainNumberAdd"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('main_number', array('class' => 'validate[required]', 'id' => 'CustomerMainNumberAdd', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerEmailAdd"><?php echo TABLE_EMAIL; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('email', array('id' => 'CustomerEmailAdd', 'class' => 'validate[optional,custom[email]]', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?>:</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('payment_term_id', array('empty' => INPUT_NONE, 'label' => false, 'style' => 'width: 93%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerDiscount"><?php echo GENERAL_DISCOUNT; ?> (%):</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('discount', array('value' => 0, 'label' => false, 'style' => 'width: 93%;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="CustomerAddress"><?php echo TABLE_ADDRESS; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->textarea('address', array('style' => 'width: 90%; height: 80px;')); ?>
            </div>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>