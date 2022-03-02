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
        $("#ServiceEditForm").ajaxForm({
            dataType: "JSON",
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/edit/<?php echo $this->data['Service']['id'];?>",
            data: $("#ServiceEditForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                if($("#ServiceServiceGroupId").val() == null || $("#ServiceServiceGroupId").val() == ""){
                    alertSelectRequireField('service_group');
                    return false;
                }
                if($("#ServiceBranchId").val() == null || $("#ServiceBranchId").val() == ""){
                    alertSelectRequireField('branch');
                    return false;
                }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading").show();
                $(".option_save").hide();
            },
            success: function(result) {
                $(".option_loading").hide();
                $(".option_save").show();
                $(".btnBackService").click();
                var smsAlert='';
                if(result.error==0){
                    smsAlert='<?php echo MESSAGE_DATA_HAS_BEEN_SAVED;?>';
                    iconType = "success";
                }else if(result.error==0){
                    smsAlert='<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED;?>';
                    iconType = "error";
                }
                $(".option_loading").hide();
                $(".option_save").show();
                $(".btnBackMeetingTheme").click();
                // alert message
                Swal.fire({
                    icon: iconType,
                    title: smsAlert
                });
            }
        });
        choicesSelect('#ServiceBranchId,#ServiceServiceGroupId');
        backEventModule(oTableService,"btnBackService");
    });
    
    function alertSelectRequireField(type){
        bodyMessage='';
        if(type=='service_group'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_SERVICE_GROUP; ?>";
        }else if(type=='branch'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_BRANCH; ?>";
        }
        $.showConfirm({
            title: "<?php echo MENU_SERVICE_INFORMATION;?>",
            body: bodyMessage,
            textFalse: "<?php echo TABLE_CANCEL;?>",
            textTrue: "<?php echo TABLE_OK;?>",
            onSubmit: function(result) {
                if(result){}
            },
            onDispose: function() {}
        });
    }
</script>
<?php
echo $this->Form->create('Service', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->hidden('id', array('value'=>$this->data['Service']['id'])); 
echo $this->Form->hidden('company_id', array('value'=>1)); 
echo $this->Form->hidden('sys_code');
?>
<div class="app form-body">
    <div class="page-title">
        <div class="row"></div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_SERVICE_MANAGEMENT;?></h4>
                <p class="text-subtitle text-muted">Please fill information below.</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackService" is-breadcrumb="1"><?php echo MENU_SERVICE_MANAGEMENT;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_SERVICE_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_CODE;?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->text('code', array('class' => 'form-control','readonly'=>true,'required'=>'required', 'placeholder' => TABLE_CODE ,'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo MENU_BRANCH;?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->input('branch_id', array('label' => false, 'data-placeholder' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo MENU_SECTION_LIST;?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->input('service_group_id', array('label' => false, 'data-placeholder' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => 'Name' ,'style' => '')); ?>
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
                    <label class="card-title"><?php echo TABLE_OTHER_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="TodoListPbCode"><?php echo TABLE_REMARK;?></label>
                                    <?php echo $this->Form->textarea('remark', array('class' => 'form-control','placeholder' => TABLE_REMARK ,'style' => 'height:205px;')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackService text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveService text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div>
<?php echo $this->Form->end(); ?>
