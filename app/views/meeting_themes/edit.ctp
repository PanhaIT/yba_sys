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
        var user = $('.userMeetingTheme').bootstrapDualListbox({
            moveOnSelect: false
        });
        $("#MeetingThemeEditForm").ajaxForm({
            dataType: "JSON",
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/edit/<?php echo $this->data['MeetingTheme']['id'];?>",
            data: $("#MeetingThemeEditForm").serialize(),
            beforeSerialize: function(formData, formOptions) {  
                if($("#MeetingThemeEgroupId").val() == null || $("#MeetingThemeEgroupId").val() == ""){
                    alertSelectRequireField('MeetingTheme');
                    return false;
                }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading,.spinner").show();
                $(".option_save,.spinner_placeholder").hide();
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
                $(".spinner_placeholder").show();
                $(".option_loading,.spinner").hide();
                $(".btnBackMeetingTheme").click();
                // alert message
                Swal.fire({
                    icon: iconType,
                    title: smsAlert
                });
            }
        });
        choicesSelect('#MeetingThemeEgroupId,#MeetingThemeEmployeeId');
        backEventModule(oTableMeetingTheme,"btnBackMeetingTheme");
    });

    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='MeetingTheme'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_EMPLOYEE_GROUP;?>";
        }
        $.showConfirm({
            title: "<?php echo MENU_MEETING_THEME;?>",
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
echo $this->Form->create('MeetingTheme', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->hidden('id', array('value'=>$this->data['MeetingTheme']['id'])); 
echo $this->Form->hidden('sys_code');
?>
<div class="app form-body">
    <div class="page-title">
        <div class="row">
           
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_MEETING_THEME;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackMeetingTheme" is-breadcrumb="1"><?php echo MENU_MEETING_THEME;?></a></li>
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
                    <label class="card-title"><?php echo TABLE_TODO_LIST_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row equal-heights">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="MeetingThemeName"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->textarea('name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_NAME ,'style' => 'height:122px;')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="MeetingThemeCode"><?php echo TABLE_CODE;?></label><label class="require-label">*</label>
                                                <?php echo $this->Form->text('code', array('class' => 'form-control','readonly'=>true,'required'=>'required', 'placeholder' => TABLE_CODE ,'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="MeetingThemeMeetingThemeId"><?php echo MENU_EMPLOYEE_GROUP;?></label><label class="require-label">*</label>
                                                <?php echo $this->Form->input('egroup_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="MeetingThemeNote"><?php echo TABLE_NOTE;?></label>
                                                <?php echo $this->Form->text('note', array('class' => 'form-control','readonly'=>true,'placeholder' => TABLE_NOTE ,'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="MeetingThemeEmployeeId"><?php echo TABLE_TEAM_LEADER;?></label>
                                                <?php echo $this->Form->input('employee_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo USER_USER_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[MeetingTheme][user_id][]" multiple="multiple" class="userMeetingTheme">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_meeting_themes WHERE meeting_theme_id ='" .$this->data['MeetingTheme']['id']. "')");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                    <?php 
                                        }
                                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) full_name FROM users WHERE id = user_meeting_themes.user_id) AS full_name FROM user_meeting_themes WHERE meeting_theme_id = ".$this->data['MeetingTheme']['id']);
                                        while($dataDestination = mysql_fetch_array($queryDestination)){
                                    ?>
                                            <option value="<?php echo $dataDestination['user_id']; ?>" selected="selected"><?php echo $dataDestination['full_name']; ?></option>
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
                    <label class="card-title"><?php echo TABLE_OTHER_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="MeetingThemeRemark"><?php echo TABLE_REMARK;?></label>
                                    <?php echo $this->Form->textarea('remark', array('class' => 'form-control','placeholder' => TABLE_REMARK ,'style' => 'height:120px;')); ?>
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
            <button  class="btn btn-primary btnBackMeetingTheme text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveMeetingTheme text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div>
<?php echo $this->Form->end(); ?>
