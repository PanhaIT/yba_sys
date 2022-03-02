<?php
$this->element('check_access');
$tblName = "tbl" . rand(); 
$leftPanel = "leftPanel".rand();
$rightPanel = "rightPanel".rand();
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
        var user = $('.userEgroup').bootstrapDualListbox({
            moveOnSelect: false
        });
        $("#EgroupAddEgroupForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addEgroup",
            data: $("#EgroupAddEgroupForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                if($("#EgroupCompanyId").val() == null || $("#EgroupCompanyId").val() == ""){
                    alertSelectRequireField();
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
                $(".btnBackEgroup").click();
                // alert message
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });

        choicesSelect('#EgroupCompanyId');
        backEventModule(oTableEgroup,"btnBackEgroup");
    });
    
    function alertSelectRequireField(){
        bodyMessage="<?php echo TABLE_ALERT_SELECT_COMPANY;?>";
        $.showConfirm({
            title: "<?php echo TABLE_EMPLOYEE_GROUP_INFORMATION;?>",
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
<?php echo $this->Form->create('Egroup', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_EMPlOYEE_GROUP;?></h4>
                <p class="text-subtitle text-muted">Please fill information below.</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackEgroup" is-breadcrumb="1"><?php echo TABLE_EMPlOYEE_GROUP;?></a></li>
                        <li class="breadcrumb-item active breadcrumb-name" aria-current="page">Add new</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-header">
                <label class="card-title"><?php echo MENU_EMPLOYEE_GROUP_INFO;?></label>
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
                        <div class="col-12">
                            <div class="form-group">
                                <label for="first-name-icon">Company</label><label class="require-label">*</label>
                                <?php echo $this->Form->input('company_id', array('label' => false,'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'class' => 'choices form-select multiple-remove')); ?>
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
                            <select name="data[Egroup][user_id][]" multiple="multiple" class="userEgroup">
                                <?php
                                $selected ='';
                                $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1");
                                    while($dataSource = mysql_fetch_array($querySource)){
                                ?>
                                        <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
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
    <div style="z-index:100; padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button class="btn btn-primary btnBackEgroup text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveEgroup text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

