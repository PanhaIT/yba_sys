<?php 
$this->element('check_access');
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
$dateMaxDob = date("d/m/Y", strtotime(date("Y-m-d", strtotime($dateNow)) . " -180 month"));
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function(){
        $("#EmployeeTypeEditForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/edit/<?php echo $this->data['EmployeeType']['id'];?>",
            data: $("#EmployeeTypeEditForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                $(".option_loading,.spinner").show();
                $(".option_save,.spinner_placeholder").hide();
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading").show();
                $(".option_save").hide();
            },
            success: function(result) {
                $(".spinner_placeholder").show();
                $(".option_loading,.spinner").hide();
                $(".btnBackEmployeeType").click();
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });
        backEventModule(oTableEmployeeType,"btnBackEmployeeType");
    });
</script>
<?php
echo $this->Form->create('EmployeeType', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->hidden('id', array('value'=>$this->data['EmployeeType']['id'])); 
echo $this->Form->hidden('sys_code');
?>
<div class="app form-body">
    <div class="page-title">
        <div class="row"></div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_EMPLOYEE_TYPE;?></h4>
                <p class="text-subtitle text-muted">Please fill information below.</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackEmployeeType" is-breadcrumb="1"><?php echo MENU_EMPLOYEE_TYPE;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-header">
                <label class="card-title"><?php echo MENU_EMPLOYEE_TYPE_INFO;?></label>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="first-name-icon">Name</label><label class="require-label">*</label>
                                <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => 'Name' ,'style' => '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackEmployeeType text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveEmployeeType text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div>
<?php echo $this->Form->end(); ?>
