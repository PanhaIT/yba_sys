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
        var user = $('.userGroup').bootstrapDualListbox({
            moveOnSelect: false
        });
        $("#GroupAddForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/add",
            data: $("#GroupAddForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading,.spinner").show();
                $(".option_save,.spinner_placeholder").hide();
            },
            error: function (result) { },
            success: function(result) {
                $(".spinner_placeholder").show();
                $(".option_loading,.spinner").hide();
                $(".btnBackGroup").click();
                // alert message
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });

        backEventModule(oTableGroup,"btnBackGroup");

        $(".moduleType").click(function(){
            if($(".moduleTypeBody[title=" + $(this).attr("title") + "]").is(':visible')==false){
                $(this).find("svg.btn-dash-group").show();
                $(this).find("svg.btn-plus-group").hide();
            }else{
                $(this).find("svg.btn-dash-group").hide();
                $(this).find("svg.btn-plus-group").show();
            }
            $(".moduleTypeBody[title=" + $(this).attr("title") + "]").slideToggle();
        });
        
        $(".btnFullRights").change(function(){
            if($(this).is(":checked")){
                $(".moduleType_" + $(this).attr("moduleId")).attr('checked', true);
            } else {
                $(".moduleType_" + $(this).attr("moduleId")).attr('checked', false);
            }
        });
        
        $(".moduleCheck").unbind("click").click(function(){
            var alt = $(this).attr('alt');
            var num = parseInt($("input[alt='ModuleMain_"+alt+"']").attr('num'));
            var max = parseInt($("input[alt='ModuleMain_"+alt+"']").attr('max'));
            if($(this).is(":checked")){
                if((num - 1) == 0){
                    $("input[alt='ModuleMain_"+alt+"']").prop("indeterminate", false).attr('checked', true);
                } else {
                    $("input[alt='ModuleMain_"+alt+"']").prop("indeterminate", true);
                }
                $("input[alt='ModuleMain_"+alt+"']").attr('num', (num - 1));
            } else {
                if(max == (num + 1)){
                    $("input[alt='ModuleMain_"+alt+"']").prop("indeterminate", false).attr('checked', false);
                } else {
                    $("input[alt='ModuleMain_"+alt+"']").attr("checked", false).prop('indeterminate', true);
                }
                $("input[alt='ModuleMain_"+alt+"']").attr('num', (num + 1));
            }
        });

    });
    
    function alertSelectRequireField(){
        bodyMessage="<?php echo TABLE_ALERT_SELECT_COMPANY;?>";
        $.showConfirm({
            title: "<?php echo TABLE_GROUP;?>",
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
<?php echo $this->Form->create('Group', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_GROUP;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackGroup" is-breadcrumb="1"><?php echo TABLE_GROUP;?></a></li>
                        <li class="breadcrumb-item active breadcrumb-name" aria-current="page">Add new</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_GROUP_MANAGEMENT_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                    <?php echo $this->Form->textarea('name', array('class' => 'form-control','required'=>'required', 'placeholder' => 'Name' ,'style' => 'height:140px;')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo GENERAL_MEMBER;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[Group][user_id][]" multiple="multiple" class="userGroup">
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
    </div>

    <div class="row">
        <div class="col" style="">
            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo GENERAL_PERMISSION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <input type="hidden" name="module_id[]" value="1" />
                                <table class="responsive table-hover nowrap" width="100%" cellspacing="0" style="line-height:30px;">
                                <?php
                                $groupBy = '';
                                $queryType = mysql_query("SELECT id,name,group_by FROM module_types WHERE status = 1 ORDER BY ordering");
                                while ($dataType = mysql_fetch_array($queryType)){
                                    $rand = rand();
                                    $queryModule = mysql_query("SELECT * FROM modules WHERE module_type_id=" . $dataType['id'] . " AND status = 1 ORDER BY ordering");
                                    if($groupBy != $dataType['group_by']){
                                    ?>
                                    <tr>
                                        <td style="border:none;"><div style="font-size: 14px; font-weight: bold; margin-bottom: 0px; margin-top: 0px;"><?php echo $dataType['group_by']; ?></div></td>
                                    </tr>
                                    <?php
                                        $groupBy = $dataType['group_by'];
                                    }
                                    ?>
                                    <tr>
                                        <td style="border:none;">
                                            <a href="#" style="color:#333;">
                                                <div class="moduleType" style="font-size: 14px; float: left; width: 95%; margin-left: 10px;" title="<?php echo $rand; ?>">
                                                    <svg class="btn-plus-group bi btnPlusMinus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus-square-fill" /></svg>
                                                    <svg class="btn-dash-group bi btnPlusMinus" style="display:none;"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>dash-square-fill" /></svg>
                                                    &nbsp;<?php echo $dataType['name']; ?>
                                                </div>
                                                <input type="checkbox" class="form-check-input big-checkbox btnFullRights"  max="<?php echo mysql_num_rows($queryModule); ?>" num="<?php echo mysql_num_rows($queryModule); ?>" style="float: left; margin-left: 22px;" moduleId="<?php echo $dataType['id']; ?>" alt="ModuleMain_<?php echo $dataType['id']; ?>"  >
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">
                                            <div class="moduleTypeBody" style="padding-left: 10px; display: none;" title="<?php echo $rand; ?>">
                                                <table class="responsive table table-hover table-striped nowrap" width="100%" cellspacing="0" style="line-height:0px;margin-top:0px; margin-bottom:0px;">
                                                <?php
                                                while ($dataModule = mysql_fetch_array($queryModule)) {
                                                ?>
                                                    <tr>
                                                        <td style="font-size:14px;width:99%;"><svg class="btn-dash-group bi"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>dash-square" /></svg>&nbsp;&nbsp;<?php echo $dataModule['name']; ?></td>
                                                        <td style="font-size:14px;padding-top:5px; padding-bottom:5px;">
                                                            <div class="checkbox">
                                                                <input type="checkbox" class="form-check-input big-checkbox moduleType_<?php echo $dataType['id']; ?> moduleCheck"  name="module_id[]" alt="<?php echo $dataType['id']; ?>"  value="<?php echo $dataModule['id']; ?>">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index:100; padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button class="btn btn-primary btnBackGroup text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveGroup text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

