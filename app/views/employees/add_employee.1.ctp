<?php
$this->element('check_access');
$tblName = "tbl" . rand(); 
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
        alert(1);
    //     $("#EmployeeAddEmployeeForm").ajaxForm({
    //         type: "POST",
    //         url: "<?php //echo $this->base.'/'.$this->params['controller']; ?>/addEmployee",
    //         data: $("#EmployeeAddEmployeeForm").serialize(),
    //         beforeSerialize: function(formData, formOptions) {
    //             if($("#EmployeeCompanyId").val() == null || $("#EmployeeCompanyId").val() == ""){
    //                 alertSelectRequireField('company');
    //                 return false;
    //             }
    //             if($("#EmployeeEgroupId").val() == null || $("#EmployeeEgroupId").val() == ""){
    //                 alertSelectRequireField('employee_group');
    //                 return false;
    //             }
    //             if($("#EmployeeSex").val() == null || $("#EmployeeSex").val() == ""){
    //                 alertSelectRequireField('sex');
    //                 return false;
    //             }
    //             if($("#EmployeeEmployeeTypeId").val() == null || $("#EmployeeEmployeeTypeId").val() == ""){
    //                 alertSelectRequireField('employee_type');
    //                 return false;
    //             }
    //         },
    //         beforeSubmit: function (formData, formObject, formOptions) {
    //             $(".option_loading").show();
    //             $(".option_save").hide();
    //         },
    //         error: function (result) { },
    //         success: function(result) {
    //             $(".option_loading").hide();
    //             $(".option_save").show();
    //             $(".btnBackEmployee").click();
    //             // alert message
    //             Swal.fire({
    //                 icon: "success",
    //                 title: result
    //             });
    //         }
    //     });

    //     smartImageLoader();
    //     choicesSelect('#EmployeeCompanyId,#EmployeeEgroupId,#EmployeeSex,#EmployeeEmployeeTypeId,#EmployeePositionId,#EmployeeWorkForVendorId');
    //     backEventModule(oTableEmployee,"btnBackEmployee");

    //     $('.date_of_birth').datepicker({
    //         format: 'yyyy-mm-dd',
    //         orientation: "bottom",
    //         autoclose: 1,
    //         todayHighlight: 1
    //     });
    // });
    
    // function alertSelectRequireField(type){
    //     var bodyMessage="";
    //     if(type=='company'){
    //         bodyMessage="<?php echo TABLE_ALET_SELECT_COMPANY_NAME;?>";
    //     }else if(type=='employee_group'){
    //         bodyMessage="<?php echo TABLE_ALET_SELECT_EMPLOYEE_GROUP;?>";
    //     }else if(type=='sex'){
    //         bodyMessage="<?php echo TABLE_ALET_SELECT_SEX;?>";
    //     }else if(type=='employee_type'){
    //         bodyMessage="<?php echo TABLE_ALET_SELECT_EMPLOYEE_TYPE;?>";
    //     }
    //     $.showConfirm({
    //         title: "<?php echo TABLE_EMPLOYEE_INFORMATION;?>",
    //         body: bodyMessage,
    //         textFalse: "<?php echo TABLE_CANCEL;?>",
    //         textTrue: "<?php echo TABLE_OK;?>",
    //         onSubmit: function(result) {
    //             if(result){}
    //         },
    //         onDispose: function() {}
    //     });
    // }
    // function getHeight(length, ratio) {
    //     var height = ((length)/(Math.sqrt((Math.pow(ratio, 2)+1))));
    //     return Math.round(height);
    // }
    // function getWidth(length, ratio) {
    //     var width = ((length)/(Math.sqrt((1)/(Math.pow(ratio, 2)+1))));
    //     return Math.round(width);
    // }
    // function smartImageLoader(){
    //     FilePond.create(document.querySelector('.imgbb-filepond'), {
    //         allowImagePreview: false,
    //         server: {
    //             process: (fieldName, file, metadata, load, error, progress, abort) => {
    //                 // We ignore the metadata property and only send the file
    //                 const formData = new FormData();
    //                 formData.append(fieldName, file, file.name);//fieldName=image, file=object file, file.name= image name
    //                 const request = new XMLHttpRequest();
    //                 // you can change it by your client api key
    //                 request.open('POST', '<?php echo $this->base.'/'.$this->params['controller']; ?>/uploadEmployee');
    //                 request.upload.onprogress = (e) => {
    //                     $('#photo_display_employee').css({'background-image':'url("")'});
    //                     $("#loadingImage").show();
    //                     progress(e.lengthComputable, e.loaded, e.total);//e.lengthComputable=true/false,e.total=file size, e.loaded=time loading
    //                 };
    //                 request.onload = function() {
    //                     if (request.status >= 200 && request.status < 300) {
    //                         $("#loadingImage").hide();
    //                         load(request.responseText);
    //                     } else {
    //                         error('oh no');
    //                     }
    //                 };
    //                 request.onreadystatechange = function() {
    //                     if (this.readyState == 4) {
    //                         if (this.status == 200) {
    //                             let result      = JSON.parse(this.response);
    //                             let response    = JSON.parse(this.responseText);
    //                             let resetImgUrl = '<?php echo $this->webroot;?>img/136x155x300.png';
    //                             let photoFolder = 'public/employee_photo/tmp/thumbnail/';
    //                             let imageUrl    = '<?php echo $this->webroot; ?>'+photoFolder+result.name;
    //                             let ratio       = (2/3);
    //                             let maxWithd    = 136;
    //                             let maxHeight   = 155;
    //                             let height = getHeight(300,ratio);
    //                             let width  = getWidth(height,ratio);
    //                             if(height>maxHeight){
    //                                 height = maxHeight;
    //                             }
    //                             if(width>maxWithd){
    //                                 width  = maxWithd;
    //                             }
    //                             $('#employeePhotoId').val(result.name);//set photo employee for submit to controller
    //                             $('#photo_display_employee').css({'background-image':'url("")'});
    //                             $('#photo_display_employee').css({'background-image':'url('+imageUrl+ ')','width':width,'height':height ,'backgroundRepeat':'no-repeat','margin':'0px 0px 0px 0px','padding':'0px 0px 0px 0px'})
    //                             //remove photo tmp
    //                             $(".filepond--file-action-button,.filepond--action-revert-item-processing").click(function(){
    //                                 var employeeId='';
    //                                 $.ajax({
    //                                     type: "POST",
    //                                     url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removePhotoTmpEmployee",
    //                                     data: 'photo='+result.name+'&employee_id='+employeeId,
    //                                     beforeSend: function(){
    //                                         $("#loadingImage").show();
    //                                     },
    //                                     success: function(result){
    //                                         var maxWithd  = 136;
    //                                         var maxHeight = 155;
    //                                         $("#loadingImage").hide();
    //                                         $('#employeePhotoId').val('');
    //                                         $('#photo_display_employee').css({'background-image':'url('+resetImgUrl+ ')','width':width,'height':height ,'backgroundRepeat':'no-repeat','margin':'0px 0px 0px 0px','padding':'0px 0px 0px 0px'})
    //                                     }
    //                                 });
    //                             });
    //                             Toastify({
    //                                 text: "Success uploaded",
    //                                 duration: 3000,
    //                                 close: true,
    //                                 gravity: "top",
    //                                 position: "right",
    //                                 backgroundColor: "#4fbe87",
    //                             }).showToast();
    //                             console.log(response);
    //                         } else {
    //                             Toastify({
    //                                 text: "Failed uploading",
    //                                 duration: 3000,
    //                                 close: true,
    //                                 gravity: "top",
    //                                 position: "right",
    //                                 backgroundColor: "#ff0000",
    //                             }).showToast();
    //                             console.log("Error", this.statusText);
    //                         }
    //                     }
    //                 };
    //                 request.send(formData);
    //             }
    //         }
    //     });
    //     $(".filepond--credits").html('');
    // }
</script>
<br />
<?php 
echo $this->Form->create('Employee', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical','inputDefaults' => array('div' => false, 'label' => false)));
?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
            <input type="hidden" value="" name="data[Employee][photo]" id="employeePhotoId"/>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_EMPLOYEE;?></h4>
                <p class="text-subtitle text-muted">Please fill information below.</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackEmployee" is-breadcrumb="1">Employee</a></li>
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
                    <label class="card-title">Personal Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Latin Name</label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => 'Latin Name' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Khmer Name</label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name_kh', array('class' => 'form-control','required'=>true, 'placeholder' => 'Khmer Name' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Sex</label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('sex', array('empty' => INPUT_SELECT,'required'=>'required', 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Date of Birth</label>
                                            <?php echo $this->Form->text('dob', array('class' => 'form-control date_of_birth', 'placeholder' => 'Date of Birth' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-md-4" id="photo_display_employee" style="background-repeat:no-repeat; text-align: center;vertical-align: middle; height:155px; width:136px; background-image:url('<?php echo $this->webroot;?>img/136x155x300.png'); "><img id="loadingImage" src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="width: 32px; margin-top:58px; text-align: center;vertical-align: middle; display:none;" /></div>
                                    <div class="col" style="text-align:center;">
                                        <input type="file" name="image" class="imgbb-filepond">
                                        <label class="label_image">Drag & Drop or Browse</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Company</label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('company_id', array('label' => false,'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Employee Group</label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('egroup_id', array('label' => false,'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Employee ID</label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('employee_code', array('class' => 'form-control','required'=>true, 'placeholder' => 'Employee ID' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">Employee Type</label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('employee_type_id', array('empty' => INPUT_SELECT,'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="col" style="margin-top:13px;">
                            <div class="card">
                                <div class="card-header">
                                    <label class="card-title">Address Information</label>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 col-md-4">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">No</label>
                                                            <?php echo $this->Form->text('house_no', array('class' => 'form-control', 'placeholder' => 'No' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">Commune</label>
                                                            <?php echo $this->Form->text('commune', array('class' => 'form-control', 'placeholder' => 'Commune' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">Street</label>
                                                            <?php echo $this->Form->text('street', array('class' => 'form-control', 'placeholder' => 'Street' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">District</label>
                                                            <?php echo $this->Form->text('district', array('class' => 'form-control', 'placeholder' => 'District' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">Village</label>
                                                            <?php echo $this->Form->text('village', array('class' => 'form-control', 'placeholder' => 'Village' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="first-name-icon">Province/City</label>
                                                            <?php echo $this->Form->text('province', array('class' => 'form-control', 'placeholder' => 'Province/City' ,'style' => '')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
                                    <label for="mobile-id-icon">Mobile</label><label class="require-label">*</label>
                                    <div class="position-relative">
                                        <input name="data[Employee][personal_number]" type="text" required="required" class="form-control" placeholder="Mobile" id="mobile-id-icon">
                                        <div class="form-control-icon">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="mobile-id-icon">Telephone</label>
                                    <div class="position-relative">
                                        <input name="data[Employee][other_number]" type="text" class="form-control" placeholder="Telephone" id="mobile-id-icon">
                                        <div class="form-control-icon">
                                            <i class="bi bi-telephone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="email-id-icon">Email</label>
                                    <div class="position-relative">
                                        <input name="data[Employee][email]" type="text" class="form-control" placeholder="Email" id="email-id-icon">
                                        <div class="form-control-icon">
                                            <i class="bi bi-envelope"></i>
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
                    <label class="card-title">Other Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon">Passport No.</label>
                                    <?php echo $this->Form->text('passports', array('class' => 'form-control', 'placeholder' => 'Passport' ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon">Identity Card</label>
                                    <?php echo $this->Form->text('identity_card', array('class' => 'form-control', 'placeholder' => 'Identity Card' ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="first-name-icon">Position</label>
                                    <?php echo $this->Form->input('position_id', array('empty' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon">Salary</label>
                                    <?php echo $this->Form->text('salary', array('class' => 'form-control', 'placeholder' => 'Salary' ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon-left">
                                    <label for="first-name-icon">Work For Vendor</label>
                                    <?php echo $this->Form->input('work_for_vendor_id', array('name'=>'data[Employee][work_for_vendor_id]','empty' => INPUT_SELECT,'class' => 'choices form-select multiple-remove', 'label' => false)); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon">Note</label>
                                    <?php echo $this->Form->textarea('note', array('class' => 'form-control', 'placeholder' => 'Note' ,'style' => '')); ?>
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
            <button class="btn btn-primary btnBackEmployee text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveEmployee text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

