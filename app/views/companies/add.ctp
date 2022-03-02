<?php 
echo $this->element('prevent_multiple_submit'); 
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    var tabCompanyId = $(".ui-tabs-selected a").attr("href");
    var fieldRequire = ['BranchCountryId'];
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
        $("#CompanyCompanyCategoryId").chosen({width: 410});
        $(".chosenBranch").chosen({width: 265});
        // Upload Image
        // From Action Upload Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpg",".gif",".png");
                allowSubmit = false;
                file = $("#CompanyPhoto").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
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
                var photoFolder = "public/company_photo/tmp/";
                $("#photoCompanyDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + result);
                $("#<?php echo $photoNameHidden; ?>").val(result);
            }
        });
        // Action Submit Upload
        $("#CompanyPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        $("#CompanyAddForm").validationEngine();
        $("#CompanyAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequire) == false){
                    alertSelectRequireField();
                    $(".btnSaveCompany").removeAttr('disabled');
                    return false;
                }
                if($("#<?php echo $photoNameHidden; ?>").val() == ''){
                    alertUploadPhotoCompany();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveCompany").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#tabs").tabs("remove", tabCompanyId);
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Company', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Company', 'Add', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
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
        $('#BranchWorkStart, #BranchWorkEnd').timepicker();
    });
    function alertSelectUserCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_SELECT_USER; ?></p>');
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
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function alertUploadPhotoCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">Please Upload Photo!</p>');
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
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<br />
<form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/companies/upload/" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><?php __(TABLE_PHOTO); ?></legend>
        <table>
            <tr>
                <td colspan="2">
                    <img id="photoCompanyDisplay" alt="" style=" max-width: 140px; max-height: 140px;" />
                </td>
            </tr>
            <tr>
                <td><label for="CompanyPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                <td valign="top"><input type="file" id="CompanyPhoto" name="photo" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<br />
<?php echo $this->Form->create('Company'); ?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Company][photo]" />
<fieldset>
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 120px;"><label for="CompanyCompanyCategoryId"><?php echo TABLE_CATEGORY; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('company_category_id', array('class'=>'validate[required]', 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 420px;')); ?></td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="CompanyName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="CompanyNameOther"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name_other', array('class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyCurrencyId"><?php echo TABLE_BASE_CURRENCY; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('currency_id', array('class'=>'validate[required]', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;')); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyVatNumber">VAT No :</label></td>
            <td><?php echo $this->Form->text('vat_number', array()); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyVatCalculate">VAT Calculating <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[Company][vat_calculate]" id="CompanyVatCalculate" class="validate[required]" style="width: 250px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo TABLE_VAT_BEFORE_DISCOUNT; ?></option>
                        <option value="2"><?php echo TABLE_VAT_AFTER_DISCOUNT; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CompanyWebsite"><?php echo TABLE_WEBSITE; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('website', array('class' => 'validate[optional,custom[url]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="CompanyDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description', array()); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_BRANCH_HEAD); ?></legend>
    <table>
        <tr>
            <td style="width: 120px;"><label for="BranchBranchTypeId"><?php echo MENU_BRANCH_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('branch_type_id', array('name' => 'data[Branch][branch_type_id]', 'class'=>'validate[required]', 'id' => 'BranchBranchTypeId', 'empty' => INPUT_SELECT, 'options' => $branchTypes, 'label' => false, 'style' => 'width: 250px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="BranchName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('name' => 'data[Branch][name]', 'class'=>'validate[required]', 'id' => 'BranchName')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="BranchNameOther"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name_other', array('name' => 'data[Branch][name_other]', 'class'=>'validate[required]', 'id' => 'BranchNameOther')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('telephone', array('name' => 'data[Branch][telephone]', 'id' => 'BranchTelephone', 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchFaxNumber"><?php echo TABLE_FAX; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('fax_number', array('name' => 'data[Branch][fax_number]', 'id' => 'BranchFaxNumber')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchEmailAddress"><?php echo TABLE_EMAIL; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('email_address', array('name' => 'data[Branch][email_address]', 'id' => 'BranchEmailAddress', 'class' => 'validate[optional,custom[email]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchWorkStart"><?php echo TABLE_WORKING_HOUR; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('work_start', array('name' => 'data[Branch][work_start]', 'id' => 'BranchWorkStart', 'class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_START)); ?>
                    <?php echo $this->Form->text('work_end', array('name' => 'data[Branch][work_end]', 'id' => 'BranchWorkEnd', 'class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_END)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchCountryId"><?php echo TABLE_COUNTRY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('country_id', array('name' => 'data[Branch][country_id]', 'class'=>'validate[required] chosenBranch', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;', 'id' => 'BranchCountryId')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchLong"><?php echo TABLE_LONG; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('long', array('name' => 'data[Branch][long]', 'id' => 'BranchLong')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchLat"><?php echo TABLE_LAT; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('lat', array('name' => 'data[Branch][lat]', 'id' => 'BranchLat')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="BranchAddress"><?php echo TABLE_ADDRESS; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address', array('class'=>'validate[required]', 'name' => 'data[Branch][address]', 'id' => 'BranchAddress')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="BranchAddressOther"><?php echo TABLE_ADDRESS_IN_KHMER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('address_other', array('class'=>'validate[required]', 'name' => 'data[Branch][address_other]', 'id' => 'BranchAddressOther')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(TABLE_MODULE_CODE); ?></legend>
    <table cellpadding="5" cellspacing="0" width="100%">
        <tr>
            <td style="width: 17%;"><label for="adjCode"><?php echo TABLE_ADJ_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][adj_code]" id="adjCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="toCode"><?php echo TABLE_TO_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][to_code]" id="toCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="posCode"><?php echo TABLE_POS_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][pos_code]" id="posCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="popRepCode"><?php echo TABLE_POS_RECEIPT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][pos_rep_code]" id="popRepCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="invCode"><?php echo TABLE_INVOICE_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][inv_code]" id="invCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="invRepCode"><?php echo TABLE_INVOICE_RECEIPT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][inv_rep_code]" id="invRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="dnCode"><?php echo TABLE_DELIVERY_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][dn_code]" id="dnCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="receivePayCode"><?php echo TABLE_RECEIVE_PAYMENT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][receive_pay_code]" id="receivePayCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="cmCode"><?php echo TABLE_CREDIT_MEMO_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][cm_code]" id="cmCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="cmRepCode"><?php echo TABLE_CREDIT_MEMO_RECEIPT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][cm_rep_code]" id="cmRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="poCode"><?php echo TABLE_PURCHASE_ORDER_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][po_code]" id="poCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="payBillCode"><?php echo TABLE_PAY_BILL_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][pay_bill_code]" id="payBillCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="pbCode"><?php echo TABLE_PURCHASE_BILL_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][pb_code]" id="pbCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="pbRepCode"><?php echo TABLE_PURCHASE_RECEITP_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][pb_rep_code]" id="pbRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="brCode"><?php echo TABLE_BILL_RETURN_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][br_code]" id="brCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="brRepCode"><?php echo TABLE_BILL_RETURN_RECEITP_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" name="data[ModuleCodeBranch][br_rep_code]" id="brRepCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveCompany">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveCompany"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>