<?php 
$this->element('check_access');
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
$uploadPhoto  = "uploadPhoto".rand();
$displayPhoto = "displayPhoto".rand();
$btnRemoveUploadPhoto = "btnRemoveUploadPhoto".rand();
$loadingImage = "loadingImage".rand();
$removeImage = "removeImage".rand();
$labelDragDrop = "labelDragDrop".rand();
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
<script type="text/javascript">
    $(document).ready(function(){
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        var user = $('.companyMemberOf,.branchMemberOf').bootstrapDualListbox({
            moveOnSelect: false
        });
        $("#UserEditProfileUserForm").ajaxForm({
            dataType: "JSON",
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/editProfileUser/<?php echo $this->data['User']['id'];?>",
            data: $("#UserEditProfileUserForm").serialize(),
            beforeSerialize: function(formData, formOptions) {  
                if($("#UserGroupId").val() == null || $("#UserGroupId").val() == ""){
                    alertSelectRequireField('user_group');
                    return false;
                }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading").show();
                $(".option_save").hide();
            },
            success: function(result) {
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
                $(".btnBackUser").click();
                // alert message
                Swal.fire({
                    icon: iconType,
                    title: smsAlert
                });
            }
        });
        choicesSelect('#UserGroupId,#UserSex,#UserNationalities');
        backEventModule(oTableUser,"btnBackUser");
    });
    
    function alertSelectRequireField(type){
        var bodyMessage="";
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
<body>
<?php
echo $this->Form->create('User', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->hidden('id', array('value'=>$this->data['User']['id'])); 
echo $this->Form->hidden('sys_code');
?>
<div class="app form-body">
    <div class="page-title">
        <div class="row">
            <input type="hidden" id="serviceId" name="data[Service][id]" />
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
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_FIRST_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['User']['first_name']; ?></td>
                                        <th><?php echo TABLE_LAST_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['User']['last_name']; ?></td>
                                        <th><?php echo TABLE_SEX;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['User']['sex']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_ADDRESS;?> :</th>
                                        <td colspan="3" class="text-bold-500"><?php echo $this->data['User']['address']; ?></td>
                                        <th><?php echo TABLE_TELEPHONE;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['User']['telephone']; ?> </td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_DOB;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['User']['dob']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_EMAIL;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['User']['email']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_NATIONALITY; ?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $nationality; ?></td>
                                    </tr>
                                </tbody>
                            </table>
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
                                    <?php echo $this->Form->password('password', array('class' => 'form-control','placeholder' => USER_PASSWORD ,'style' => 'width:100%;','value'=>'')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_CONFIRM_PASSWORD; ?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->password('confirm_password', array('class' => 'form-control','placeholder' => USER_CONFIRM_PASSWORD ,'style' => 'width:100%;','value'=>'')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo USER_GROUP; ?></label><label class="require-label">*</label>
                                    <?php
                                    $groupId=null;
                                    $queryGroupId=mysql_query("SELECT group_id FROM user_groups WHERE user_id=".$this->data['User']['id']);
                                    while($dataGroupId=mysql_fetch_array($queryGroupId)){
                                        $groupId[]=$dataGroupId['group_id'];
                                    }
                                    ?>
                                    <?php echo $this->Form->input('group_id', array('selected' => $groupId,'class'=>'choices form-select multiple-remove', 'multiple' => 'multiple', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
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
            <button  class="btn btn-primary btnBackUser text-btnback-footer" is-breadcrumb="0">
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
</div>
<?php echo $this->Form->end(); ?>
