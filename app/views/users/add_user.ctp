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
        var user = $('.companyMemberOf,.branchMemberOf').bootstrapDualListbox({
            moveOnSelect: false
        });
        $("#UserAddUserForm").ajaxForm({
            dataType: "JSON",
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addUser",
            data: $("#UserAddUserForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                if($("#UserSex").val() == null || $("#UserSex").val() == ""){
                    alertSelectRequireField('sex');
                    return false;
                }
                if($("#UserGroupId").val() == null || $("#UserGroupId").val() == ""){
                    alertSelectRequireField('user_group');
                    return false;
                }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading,.spinner").show();
                $(".option_save,.spinner_placeholder").hide();
            },
            error: function (result) { },
            success: function(result) {
                var smsAlert='';
                if(result.error==0){
                    smsAlert='<?php echo MESSAGE_DATA_HAS_BEEN_SAVED;?>';
                    iconType = "success";
                }else if(result.error==0){
                    smsAlert='<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED;?>';
                    iconType = "error";
                }
                $(".spinner_placeholder").show();
                $(".option_loading,.spinner").hide();
                $(".btnBackUser").click();
                // alert message
                Swal.fire({
                    icon: iconType,
                    title: smsAlert
                });
            }
        });
        $('#UserDob').datepicker({
            format: 'yyyy-mm-dd',
            orientation: "bottom",
            autoclose: 1,
            todayHighlight: 1
        });
        choicesSelect('#UserGroupId,#UserSex,#UserNationalityId');
        backEventModule(oTableUser,"btnBackUser");
    });
    
    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='sex'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_SEX;?>";
        }
        if(type=='user_group'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_USER_GROUP;?>";
        }
        $.showConfirm({
            title: "<?php echo MENU_USER_MANAGEMENT;?>",
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
<?php echo $this->Form->create('User', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
           
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_USER_MANAGEMENT;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackUser" is-breadcrumb="1"><?php echo MENU_USER_MANAGEMENT;?></a></li>
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
                    <label class="card-title"><?php echo MENU_USER_MANAGEMENT_ADD;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="UserFirstName"><?php echo TABLE_FIRST_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('first_name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_FIRST_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="UserLastName"><?php echo TABLE_LAST_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('last_name', array('class' => 'form-control','required'=>true, 'placeholder' => TABLE_LAST_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="UserSex"><?php echo TABLE_SEX;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('sex', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row equal-heights">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="UserAddress"><?php echo TABLE_ADDRESS;?></label>
                                            <?php echo $this->Form->textarea('address', array('class' => 'form-control', 'placeholder' => TABLE_ADDRESS ,'style' => 'height:120px;')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="UserTelephone"><?php echo TABLE_TELEPHONE;?></label>
                                                <?php echo $this->Form->text('telephone', array('class' => 'form-control','placeholder' => TABLE_TELEPHONE ,'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="UserDob"><?php echo TABLE_DOB;?></label>
                                                <?php echo $this->Form->text('dob', array('class' => 'form-control','placeholder' => TABLE_DOB ,'style' => '')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="UserEmail"><?php echo TABLE_EMAIL;?></label>
                                                <?php echo $this->Form->text('email', array('class' => 'form-control', 'placeholder' => TABLE_EMAIL ,'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="UserNatiionality"><?php echo TABLE_NATIONALITY;?></label>
                                                <?php echo $this->Form->input('nationality_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                            </div>
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
                    <label class="card-title"><?php echo USER_USER_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[User][branch_id][]" multiple="multiple" class="branchMemberOf">
                                    <?php
                                    $selected ='';
                                    $querySource=mysql_query("SELECT id, name FROM branches WHERE is_active = 1");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                                    <?php 
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_COMPANY_MANAGEMENT_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[User][company_id][]" multiple="multiple" class="companyMemberOf">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id, name FROM companies WHERE is_active=1");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                                    <?php 
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo USER_LOGIN_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_USER_NAME; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->text('username', array('class' => 'form-control','placeholder' => USER_USER_NAME ,'style' => 'width:100%;')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_PASSWORD; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->password('password', array('class' => 'form-control','placeholder' => USER_PASSWORD ,'style' => 'width:100%;')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_CONFIRM_PASSWORD; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->password('confirm_password', array('class' => 'form-control','placeholder' => USER_CONFIRM_PASSWORD ,'style' => 'width:100%;')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_GROUP; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->input('group_id', array('class'=>'choices form-select multiple-remove', 'multiple' => 'multiple', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
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
            <button class="btn btn-primary btnBackUser text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveUser text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

