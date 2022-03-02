<?php
$this->element('check_access');
$tblName = "tbl" . rand(); 
$uploadPhoto  = "uploadPhoto".rand();
$displayPhoto = "displayPhoto".rand();
$loadingImage = "loadingImage".rand();
$labelDragDrop = "labelDragDrop".rand();
$photoNameHidden = "photoNameHidden".rand();
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<script type="text/javascript">
    $(document).ready(function(){
        $("#CompanyAddCompanyForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addCompany",
            data: $("#CompanyAddCompanyForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                if($("#CompanyCompanyCategoryId").val() == null || $("#CompanyCompanyCategoryId").val() == ""){
                    alertSelectRequireField('category');
                    return false;
                }
                if($("#CompanyVatCalculate").val() == null || $("#CompanyVatCalculate").val() == ""){
                    alertSelectRequireField('vat_calculate');
                    return false;
                }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading").show();
                $(".option_save").hide();
            },
            error: function (result) { },
            success: function(result) {
                $(".option_loading").hide();
                $(".option_save").show();
                $(".btnBackCompany").click();
                // alert message
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });
        FilePond.registerPlugin(
            FilePondPluginImagePreview
        );
        choicesSelect('#CompanyVatCalculate,#CompanyCompanyCategoryId,#CompanyCurrencyId,#CompanyBranchTypeId,#CompanyCountryId');
        backEventModule(oTableCompany,"btnBackCompany");
        smartImageLoader();
    });
    
    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='category'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_CATEGORY;?>";
        }else if(type=='vat_calculate'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_VAT_CALCULATE;?>";
        }
        $.showConfirm({
            title: "<?php echo TABLE_COMPANY_MANAGEMENT_INFO;?>",
            body: bodyMessage,
            textFalse: "<?php echo TABLE_CANCEL;?>",
            textTrue: "<?php echo TABLE_OK;?>",
            onSubmit: function(result) {
                if(result){}
            },
            onDispose: function() {}
        });
    }

    function getHeight(length, ratio) {
        var height = ((length)/(Math.sqrt((Math.pow(ratio, 2)+1))));
        return Math.round(height);
    }

    function getWidth(length, ratio) {
        var width = ((length)/(Math.sqrt((1)/(Math.pow(ratio, 2)+1))));
        return Math.round(width);
    }

    function smartImageLoader(){
        FilePond.create(document.querySelector('.<?php echo $uploadPhoto;?>'), {
            allowImagePreview: true,
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    // We ignore the metadata property and only send the file
                    const formData = new FormData();
                    formData.append(fieldName, file, file.name);//fieldName=image, file=object file, file.name= image name
                    const request = new XMLHttpRequest();
                    // you can change it by your client api key
                    request.open('POST', '<?php echo $this->base.'/'.$this->params['controller']; ?>/uploadPhoto');
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);//e.lengthComputable=true/false,e.total=file size, e.loaded=time loading
                    };
                    request.onload = function() {
                        if (request.status >= 200 && request.status < 300) {
                            load(request.responseText);
                        } else {
                            error('oh no');
                        }
                    };
                    request.onreadystatechange = function() {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                let result      = JSON.parse(this.response);
                                let response    = JSON.parse(this.responseText);
                                let resetImgUrl = '<?php echo $this->webroot;?>img/136x155x300.png';
                                let photoFolder = 'public/company_photo/tmp/';
                                let imageUrl    = '<?php echo $this->webroot; ?>'+photoFolder+result.name;
                                let ratio       = (2/3);
                                let maxWithd    = 136;
                                let maxHeight   = 155;
                                let height = getHeight(300,ratio);
                                let width  = getWidth(height,ratio);
                                if(height>maxHeight){
                                    height = maxHeight;
                                }
                                if(width>maxWithd){
                                    width  = maxWithd;
                                }
                                $("#<?php echo $labelDragDrop;?>").hide();
                                $('#<?php echo $photoNameHidden; ?>').val(result.name);//set photo employee for submit to controller
                                //remove photo tmp
                                $(".filepond--file-action-button,.filepond--action-revert-item-processing").click(function(){
                                    var moduleId='';
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removePhotoTmp",
                                        data: 'photo='+result.name+'&module_id='+moduleId,
                                        beforeSend: function(){
                                        },
                                        success: function(result){
                                            $("#<?php echo $labelDragDrop;?>").show();
                                            $('#<?php echo $photoNameHidden; ?>').val('');
                                        }
                                    });
                                });
                                Toastify({
                                    text: "Success uploaded",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#4fbe87",
                                }).showToast();
                                console.log(response);
                            } else {
                                Toastify({
                                    text: "Failed uploading",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#ff0000",
                                }).showToast();
                                console.log("Error", this.statusText);
                            }
                        }
                    };
                    request.send(formData);
                }
            }
        });
        $(".filepond--credits").html('');
    }
</script>

<?php echo $this->Form->create('Company', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
            <input type="hidden" value="" name="data[Company][photo]" id="<?php echo $photoNameHidden; ?>"/>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_COMPANY;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCompany" is-breadcrumb="1"><?php echo TABLE_COMPANY;?></a></li>
                        <li class="breadcrumb-item active breadcrumb-name" aria-current="page">Add new</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title">Company Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_NAME_IN_KHMER;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name_other', array('class' => 'form-control','required'=>true, 'placeholder' => TABLE_NAME_IN_KHMER ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode">VAT No</label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('vat_number', array('class' => 'form-control', 'placeholder' => 'VAT No' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_WEBSITE; ?></label>
                                            <?php echo $this->Form->text('website', array('class' => 'form-control', 'placeholder' => TABLE_WEBSITE ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyPbCode"><?php echo TABLE_BASE_CURRENCY; ?></label><label class="require-label">*</label><br>
                                    <?php echo $this->Form->input('currency_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyPbCode">VAT Calculating</label><br>
                                    <select name="data[Company][vat_calculate]" id="CompanyVatCalculate" class="choices form-select multiple-remove">
                                        <option value=""><?php echo INPUT_SELECT; ?></option>
                                        <option value="1"><?php echo TABLE_VAT_BEFORE_DISCOUNT; ?></option>
                                        <option value="2"><?php echo TABLE_VAT_AFTER_DISCOUNT; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_CATEGORY; ?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('company_category_id', array('empty' => INPUT_SELECT,'required'=>'required','multiple' => 'multiple', 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_NOTE; ?></label>
                                            <?php echo $this->Form->textarea('description', array('class' => 'form-control', 'placeholder' => TABLE_NOTE ,'style' => 'height:80px;')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_BRANCH_HEAD; ?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name', array('name' => 'data[Branch][name]','class' => 'form-control','required'=>'required', 'placeholder' => TABLE_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo MENU_BRANCH_TYPE;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('branch_type_id', array('name' => 'data[Branch][branch_type_id]','class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyPbCode"><?php echo TABLE_NAME_IN_KHMER;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name_other', array('name' => 'data[Branch][name_other]','class' => 'form-control', 'placeholder' => TABLE_NAME_IN_KHMER ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="CompanyCountryId"><?php echo TABLE_COUNTRY; ?></label><label class="require-label">*</label><br>
                                            <?php echo $this->Form->input('country_id', array('name' => 'data[Branch][country_id]','class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyPbCode"><?php echo TABLE_WORKING_HOUR; ?></label>
                                    <?php echo $this->Form->text('work_start', array('name' => 'data[Branch][work_start]','class' => 'form-control', 'placeholder' => 'Work Start' ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyWorkEnd">&nbsp;</label>
                                    <?php echo $this->Form->text('work_end', array('name' => 'data[Branch][work_end]','class' => 'form-control', 'placeholder' => 'Work End' ,'style' => '')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyPbCode"><?php echo TABLE_LONG; ?></label>
                                    <?php echo $this->Form->text('long', array('name' => 'data[Branch][long]','class' => 'form-control', 'placeholder' => TABLE_LONG ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="CompanyWorkEnd"><?php echo TABLE_LAT; ?></label>
                                    <?php echo $this->Form->text('lat', array('name' => 'data[Branch][lat]','class' => 'form-control', 'placeholder' => TABLE_LAT ,'style' => '')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_COMPANY_PHOTO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col container" style="text-align:center;width:100%;position: relative;">
                                <input type="file" name="image" class="<?php echo $uploadPhoto;?>" id="<?php echo $uploadPhoto;?>">
                                <label id="<?php echo $labelDragDrop;?>" class="labelDragDrop">Drag & Drop or Browse</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_BRANCH_CONTACT_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="mobile-id-icon"><?php echo TABLE_TELEPHONE; ?></label><label class="require-label">*</label>
                                    <div class="position-relative">
                                        <?php echo $this->Form->text('telephone', array('class' => 'mobile-id-icon','class' => 'form-control','required'=>true, 'placeholder' => TABLE_TELEPHONE ,'style' => '')); ?>
                                        <div class="form-control-icon">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="mobile-id-icon"><?php echo TABLE_FAX; ?></label>
                                    <div class="position-relative">
                                        <?php echo $this->Form->text('fax_number', array('class' => 'mobile-id-icon','class' => 'form-control','required'=>true, 'placeholder' => TABLE_FAX ,'style' => '')); ?>
                                        <div class="form-control-icon">
                                            <i class="bi bi-telephone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="email-id-icon"><?php echo TABLE_EMAIL; ?></label>
                                    <div class="position-relative">
                                        <?php echo $this->Form->text('email_address', array('class' => 'email-id-icon','class' => 'form-control','required'=>true, 'placeholder' => TABLE_EMAIL ,'style' => '')); ?>
                                        <div class="form-control-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_ADDRESS; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->textarea('address', array('class' => 'form-control', 'placeholder' => TABLE_ADDRESS ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_ADDRESS_IN_KHMER; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->textarea('address_other', array('class' => 'form-control', 'placeholder' => TABLE_ADDRESS_IN_KHMER ,'style' => '')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="col">
        <div class="card card-custom">
            <div class="card-header">
                <label class="card-title"><?php echo TABLE_MODULE_CODE;?></label>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyAdjCode"><?php echo TABLE_ADJ_CODE; ?></label>
                                <?php echo $this->Form->text('adj_code', array('class' => 'form-control', 'placeholder' => TABLE_ADJ_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyToCode"><?php echo TABLE_TO_CODE; ?></label>
                                <?php echo $this->Form->text('to_code', array('class' => 'form-control', 'placeholder' => TABLE_TO_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPosCode"><?php echo TABLE_POS_CODE; ?></label>
                                <?php echo $this->Form->text('pos_code', array('class' => 'form-control', 'placeholder' => TABLE_POS_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPosRepCode"><?php echo TABLE_POS_RECEIPT_CODE; ?></label>
                                <?php echo $this->Form->text('pos_rep_code', array('class' => 'form-control', 'placeholder' => TABLE_POS_RECEIPT_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyInvCode"><?php echo TABLE_INVOICE_CODE; ?></label>
                                <?php echo $this->Form->text('inv_code', array('class' => 'form-control', 'placeholder' => TABLE_INVOICE_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyInvRepCode"><?php echo TABLE_INVOICE_RECEIPT_CODE; ?></label>
                                <?php echo $this->Form->text('inv_rep_code', array('class' => 'form-control', 'placeholder' => TABLE_INVOICE_RECEIPT_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyDnCode"><?php echo TABLE_DELIVERY_CODE; ?></label>
                                <?php echo $this->Form->text('dn_code', array('class' => 'form-control', 'placeholder' => TABLE_DELIVERY_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyReceivePayCode"><?php echo TABLE_RECEIVE_PAYMENT_CODE; ?></label>
                                <?php echo $this->Form->text('receive_pay_code', array('class' => 'form-control', 'placeholder' => TABLE_RECEIVE_PAYMENT_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyCmCode"><?php echo TABLE_CREDIT_MEMO_CODE; ?></label>
                                <?php echo $this->Form->text('cm_code', array('class' => 'form-control', 'placeholder' => TABLE_CREDIT_MEMO_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyCmRepCode"><?php echo TABLE_CREDIT_MEMO_RECEIPT_CODE; ?></label>
                                <?php echo $this->Form->text('cm_rep_code', array('class' => 'form-control', 'placeholder' => TABLE_CREDIT_MEMO_RECEIPT_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPoCode"><?php echo TABLE_PURCHASE_ORDER_CODE; ?></label>
                                <?php echo $this->Form->text('po_code', array('class' => 'form-control', 'placeholder' => TABLE_PURCHASE_ORDER_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPayBillCode"><?php echo TABLE_PAY_BILL_CODE; ?></label>
                                <?php echo $this->Form->text('pay_bill_code', array('class' => 'form-control', 'placeholder' => TABLE_PAY_BILL_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPbCode"><?php echo TABLE_PURCHASE_BILL_CODE; ?></label>
                                <?php echo $this->Form->text('pb_code', array('class' => 'form-control', 'placeholder' => TABLE_PURCHASE_BILL_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyPbRepCode"><?php echo TABLE_PURCHASE_RECEITP_CODE; ?></label>
                                <?php echo $this->Form->text('pb_rep_code', array('class' => 'form-control', 'placeholder' => TABLE_PURCHASE_RECEITP_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyBrCode"><?php echo TABLE_BILL_RETURN_CODE; ?></label>
                                <?php echo $this->Form->text('br_code', array('class' => 'form-control', 'placeholder' => TABLE_BILL_RETURN_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="CompanyBrRepCode"><?php echo TABLE_BILL_RETURN_RECEITP_CODE; ?></label>
                                <?php echo $this->Form->text('br_rep_code', array('class' => 'form-control', 'placeholder' => TABLE_BILL_RETURN_RECEITP_CODE ,'style' => '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <div class="container-fluid" style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;float:left; width:100%; height:50px;">
        <a style="color:white;">
            <button class="btn btn-primary btnBackCompany text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveCompany text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

