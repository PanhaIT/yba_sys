<?php
$this->element('check_access');
$tblName = "tbl" . rand(); 
$uploadPhoto  = "uploadPhoto".rand();
$displayPhoto = "displayPhoto".rand();
$loadingImage = "loadingImage".rand();
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
        $("#VendorAddVendorForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addVendor",
            data: $("#VendorAddVendorForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                if($("#VendorVgroupId").val() == null || $("#VendorVgroupId").val() == ""){
                    alertSelectRequireField('vendor_group');
                    return false;
                }
                if($("#VendorCompanyId").val() == null || $("#VendorCompanyId").val() == ""){
                    alertSelectRequireField('company');
                    return false;
                }
                if($("#VendorPaymentTermId").val() == null || $("#VendorPaymentTermId").val() == ""){
                    alertSelectRequireField('payment_term');
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
                $(".btnBackVendor").click();
                // alert message
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });

        smartImageLoader();
        choicesSelect('#VendorCountryId,#VendorPaymentTermId,#VendorVgroupId,#VendorCompanyId');
        backEventModule(oTableVendor,"btnBackVendor");
    });
    
    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='company'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_COMPANY_NAME;?>";
        }else if(type=='vendor_group'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_VENDOR_GROUP;?>";
        }else if(type=='payment_term'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_PAYMENT_TERM;?>";
        }
        $.showConfirm({
            title: "<?php echo MENU_VENDOR_MANAGEMENT_INFO;?>",
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
        FilePond.create(document.querySelector('#<?php echo $uploadPhoto;?>'), {
            allowImagePreview: false,
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    // We ignore the metadata property and only send the file
                    const formData = new FormData();
                    formData.append(fieldName, file, file.name);//fieldName=image, file=object file, file.name= image name
                    const request = new XMLHttpRequest();
                    // you can change it by your client api key
                    request.open('POST', '<?php echo $this->base.'/'.$this->params['controller']; ?>/uploadVendor');
                    request.upload.onprogress = (e) => {
                        $('#<?php echo $displayPhoto;?>').css({'background-image':'url("")'});
                        $("#<?php echo $loadingImage;?>").show();
                        progress(e.lengthComputable, e.loaded, e.total);//e.lengthComputable=true/false,e.total=file size, e.loaded=time loading
                    };
                    request.onload = function() {
                        if (request.status >= 200 && request.status < 300) {
                            $("#<?php echo $loadingImage;?>").hide();
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
                                let photoFolder = 'public/vendor_photo/tmp/thumbnail/';
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
                                $('#employeePhotoId').val(result.name);//set photo employee for submit to controller
                                $('#<?php echo $displayPhoto;?>').css({'background-image':'url("")'});
                                $('#<?php echo $displayPhoto;?>').css({'background-image':'url('+imageUrl+ ')','width':width,'height':height ,'backgroundRepeat':'no-repeat','margin':'0px 0px 0px 0px','padding':'0px 0px 0px 0px'})
                                //remove photo tmp
                                $(".filepond--file-action-button,.filepond--action-revert-item-processing").click(function(){
                                    var employeeId='';
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removePhotoTmpVendor",
                                        data: 'photo='+result.name+'&employee_id='+employeeId,
                                        beforeSend: function(){
                                            $("#<?php echo $loadingImage;?>").show();
                                        },
                                        success: function(result){
                                            var maxWithd  = 136;
                                            var maxHeight = 155;
                                            $("#<?php echo $loadingImage;?>").hide();
                                            $('#employeePhotoId').val('');
                                            $('#<?php echo $displayPhoto;?>').css({'background-image':'url('+resetImgUrl+ ')','width':width,'height':height ,'backgroundRepeat':'no-repeat','margin':'0px 0px 0px 0px','padding':'0px 0px 0px 0px'})
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
<?php echo $this->Form->create('Vendor', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
            <input type="hidden" value="" name="data[Vendor][photo]" id="employeePhotoId"/>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_VENDOR;?></h4>
                <p class="text-subtitle text-muted">Please fill information below.</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackVendor" is-breadcrumb="1"><?php echo TABLE_VENDOR;?></a></li>
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
                    <label class="card-title">Vendor Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_VENDOR_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_VENDOR_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_VENDOR_NUMBER;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('vendor_code', array('value' => $code,'class' => 'form-control','required'=>true, 'placeholder' => TABLE_VENDOR_NUMBER ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_GROUP; ?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('vgroup_id', array('empty' => INPUT_SELECT,'required'=>'required', 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_PAYMENT_TERMS; ?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('payment_term_id', array('empty' => INPUT_SELECT,'required'=>'required', 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-md-4" id="<?php echo $displayPhoto;?>" style="background-repeat:no-repeat; text-align: center;vertical-align: middle; height:155px; width:136px; background-image:url('<?php echo $this->webroot;?>img/136x155x300.png'); "><img id="<?php echo $loadingImage;?>" src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; margin-top:58px; text-align: center;vertical-align: middle; display:none;" /></div>
                                    <div class="col" style="text-align:center;">
                                        <input type="file" name="image" id="<?php echo $uploadPhoto;?>">
                                        <label class="labelDragDrop">Drag & Drop or Browse</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_NOTE; ?></label>
                                            <?php echo $this->Form->textarea('note', array('class' => 'form-control', 'placeholder' => TABLE_NOTE ,'style' => 'height:127px;')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_COMPANY; ?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('company_id', array('empty' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_COUNTRY; ?></label>
                                            <?php echo $this->Form->input('country_id', array('empty' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
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
                    <label class="card-title">Contact Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="mobile-id-icon"><?php echo TABLE_TELEPHONE_WORK; ?></label><label class="require-label">*</label>
                                    <div class="position-relative">
                                        <?php echo $this->Form->text('work_telephone', array('class' => 'mobile-id-icon','class' => 'form-control','required'=>true, 'placeholder' => TABLE_TELEPHONE_WORK ,'style' => '')); ?>
                                        <div class="form-control-icon">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="mobile-id-icon"><?php echo TABLE_TELEPHONE_OTHER; ?></label>
                                    <div class="position-relative">
                                        <?php echo $this->Form->text('other_number', array('class' => 'mobile-id-icon','class' => 'form-control','required'=>true, 'placeholder' => TABLE_TELEPHONE_OTHER ,'style' => '')); ?>
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
                                    <label for="first-name-icon"><?php echo TABLE_ADDRESS; ?></label>
                                    <?php echo $this->Form->textarea('address', array('class' => 'form-control', 'placeholder' => TABLE_ADDRESS ,'style' => '')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid" style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;float:left; width:100%; height:50px;">
        <a style="color:white;">
            <button class="btn btn-primary btnBackVendor text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveVendor text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

