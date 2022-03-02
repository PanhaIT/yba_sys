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
    var indexrowSubTask = 0;
    var rowSubTaskList  = $("#rowSubTask");
    $(document).ready(function(){
        $("#rowSubTask").remove();
        $("#TodoListAddTodoListForm").ajaxForm({
            dataType: "JSON",
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/addTodoList/<?php echo $cloneId;?>",
            data: $("#TodoListAddTodoListForm").serialize(),
            beforeSerialize: function(formData, formOptions) {
                // if($("#TodoListCustomerId").val() == null || $("#TodoListCustomerId").val() == ""){
                //     alertSelectRequireField('customer');
                //     return false;
                // }
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
                $(".btnBackTodoList").click();
                // alert message
                Swal.fire({
                    icon: iconType,
                    title: smsAlert
                });
            }
        });
        $("#TodoListCode").val('TDL<?php echo date("y"); ?>-');
        $('#TodoListStartDate,#TodoListEndDate,#TodoListEstimateDate').datetimepicker({
            format: 'yyyy-mm-dd H:i',
            orientation: "bottom",
            autoclose: 1,
            todayHighlight: 1
        });
        $('#TodoListDate').datepicker({
            format: 'yyyy-mm-dd',
            orientation: "bottom",
            autoclose: 1,
            todayHighlight: 1
        });
        choicesSelect('#TodoListPriorityId,#TodoListProgresseId,#TodoListCustomerId,#TodoListCompanyId,#TodoListEmployeeId');
        backEventModule(oTableTodoList,"btnBackTodoList");
        cloneSubTaskRow();
        var lenTr = parseInt($(".rowSubTask").length);
        $(".btnRemoveRowSubTask").show();
        $(".btnAddRowSubTask").hide();
        if(lenTr == 1){
            $("#tblSubTask").find("tr:eq("+lenTr+")").find("td .btnRemoveRowSubTask").hide();
            $("#tblSubTask").find("tr:eq("+lenTr+")").find("td .btnAddRowSubTask").show();
        }else{
            $("#tblSubTask").find("tr:last").find("td .btnRemoveRowSubTask").show();
            $("#tblSubTask").find("tr:last").find("td .btnAddRowSubTask").show();
        }
        eventTodoList();
    });
    
    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='customer'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_CUSTOMER;?>";
        }
        $.showConfirm({
            title: "<?php echo MENU_TODO_LIST;?>",
            body: bodyMessage,
            textFalse: "<?php echo TABLE_CANCEL;?>",
            textTrue: "<?php echo TABLE_OK;?>",
            onSubmit: function(result) {
                if(result){}
            },
            onDispose: function() {}
        });
    }

    function cloneSubTaskRow(){
        if($(".TodoListServiceId:last[name='service_id[]']").attr("id") == undefined){
            indexrowSubTask = 1;
        }else{
            indexrowSubTask = parseInt($(".TodoListServiceId:last[name='service_id[]']").attr("id").split("_")[1])+1;
        }
        var tr = rowSubTaskList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .TodoListServiceId").attr("id", "TodoListServiceId_"+indexrowSubTask);
        tr.find("td .TodoListServiceCode").attr("id", "TodoListServiceCode_"+indexrowSubTask);
        tr.find("td .TodoListSectionName").attr("id", "TodoListSectionName_"+indexrowSubTask);
        $("#tblSubTask").append(tr);
        var LenTr = parseInt($(".rowSubTask").length);
        if(LenTr == 1){
            $("#tblSubTask").find("tr:eq("+LenTr+")").find(".btnAddRowSubTask").show();
            $("#tblSubTask").find("tr:eq("+LenTr+")").find(".btnRemoveRowSubTask").hide();
        }
        eventTodoList();
    }

   function eventTodoList(){
        $(".btnAddRowSubTask, .btnRemoveRowSubTask").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".btnAddRowSubTask").unbind('click').click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveRowSubTask").show();
            cloneSubTaskRow();
        });
        $(".btnRemoveRowSubTask").unbind('click').click(function(){
            var obj = $(this);
            obj.closest("tr").remove();
            var lenTr = parseInt($(".rowSubTask").length);
            if(lenTr == 1){
                $("#tblSubTask").find("tr:eq("+lenTr+")").find("td .btnRemoveRowSubTask").hide();
            }
            $("#tblSubTask").find("tr:eq("+lenTr+")").find("td .btnAddRowSubTask").show();
            setIndexSubTask();
        });
        $(".TodoListServiceId").each(function(event){
            var id = $(this).attr("id").split("_")[1];
            $('#TodoListServiceId_'+id).change(function(){
                if($(this).val()!=""){
                    $.ajax({
                        dataType: "JSON",
                        type: "GET",
                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/getServiceGroup/" + $(this).val(),
                        data: "",
                        beforeSend: function(){},
                        success: function(result){
                            $("#TodoListServiceCode_"+id).val(result.service_code);
                            $("#TodoListSectionName_"+id).val(result.section_name);
                        }
                    });
                }else{
                    $("#TodoListServiceCode_"+id).val('');
                    $("#TodoListSectionName_"+id).val('');
                }
            });
        });
        setIndexSubTask();
    }

    function setIndexSubTask(){
        var sort = 1;
        $(".rowSubTask").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
</script>
<?php echo $this->Form->create('TodoList', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));?>
<div id="app form-body">
    <div class="page-title">
        <div class="row">
           
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_TODO_LIST;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackTodoList" is-breadcrumb="1"><?php echo MENU_TODO_LIST;?></a></li>
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
                    <label class="card-title"><?php echo TABLE_TODO_LIST_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="TodoListPbCode"><?php echo TABLE_CODE;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('code', array('class' => 'form-control','readonly'=>true,'required'=>'required', 'placeholder' => TABLE_CODE ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="TodoListPbCode"><?php echo TABLE_DATE;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('date', array('value' =>date('Y-m-d'),'class' => 'form-control','required'=>true,'readonly'=>true, 'placeholder' => TABLE_DATE ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label for="TodoListPbCode"><?php echo TABLE_ESTIMATE_DATE;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('estimate_date', array('class' => 'form-control','readonly'=>true,'required'=>true, 'placeholder' => TABLE_ESTIMATE_DATE ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row equal-heights">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="TodoListPbCode"><?php echo TABLE_TASK_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->textarea('task_name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_TASK_NAME ,'style' => 'height:120px;')); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="TodoListPbCode"><?php echo TABLE_PRIORITY;?></label><label class="require-label">*</label>
                                                <?php echo $this->Form->input('priority_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="TodoListPbCode"><?php echo TABLE_START_DATE;?></label>
                                                <?php echo $this->Form->text('start_date', array('class' => 'form-control','required'=>true,'readonly'=>true, 'placeholder' => TABLE_START_DATE ,'style' => '')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="TodoListPbCode"><?php echo TABLE_STATUS;?></label><label class="require-label">*</label>
                                                <?php echo $this->Form->input('progresse_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="TodoListPbCode"><?php echo TABLE_END_DATE;?></label>
                                                <?php echo $this->Form->text('end_date', array('class' => 'form-control','readonly'=>true,'required'=>true, 'placeholder' => TABLE_END_DATE ,'style' => '')); ?>
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
                    <label class="card-title"><?php echo TABLE_SUB_TASK;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <table id="tblSubTask" class="table nowrap" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th><?php echo TABLE_NO; ?></th>
                                            <th><?php echo TABLE_SERVICE_CODE; ?></th>
                                            <th><?php echo MENU_SECTION_MANAGEMENT; ?></th>
                                            <th><?php echo MENU_SERVICE_LIST; ?></th>
                                            <th><?php echo ACTION_ACTION; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="rowSubTask" class="rowSubTask" style="visibility: hidden;">
                                            <td></td>
                                            <td>
                                                <?php echo $this->Form->text('service_code', array('id'=>'TodoListServiceCode','class' => 'TodoListServiceCode form-control','readonly'=>true, 'placeholder' => TABLE_SERVICE_CODE ,'style' => 'width:100%;')); ?>
                                            </td>
                                            <td>
                                                <?php echo $this->Form->text('section_name', array('id'=>'TodoListSectionName','class' => 'TodoListSectionName form-control','readonly'=>true,  'placeholder' => MENU_SECTION_MANAGEMENT ,'style' => 'width:100%;')); ?>
                                            </td>
                                            <td>
                                                <?php echo $this->Form->input('service_id', array('id'=>'TodoListServiceId','name' => 'service_id[]','class'=>'TodoListServiceId form-select', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => 'width:100%;')); ?>
                                            </td>
                                            <td style="text-align:left;">
                                                <a href="#" style="margin-left:5px;" class="btn-remove-multi btnRemoveRowSubTask">
                                                    <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>dash-square-fill" /></svg>
                                                </a>
                                                <a href="#" class="btn-plus-multi btnAddRowSubTask">
                                                    <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus-square-fill" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                        $serviceCode = '';
                                        $sectionName = '';
                                        $indexRow = 1;
                                        if(!empty($cloneId) && !empty($todoListDetails)){
                                            foreach($todoListDetails AS $todoListDetail){
                                                if($todoListDetail['TodoListDetails']['service_id']!=""){
                                                    $sqlService  = mysql_query("SELECT service_groups.name AS sectionName,services.code AS serviceCode FROM services 
                                                    INNER JOIN service_groups ON service_groups.id = services.service_group_id 
                                                    WHERE services.is_active=1 AND services.id=".$todoListDetail['TodoListDetails']['service_id']);
                                                    $rowService  = mysql_fetch_array($sqlService);
                                                    $serviceCode = $rowService['serviceCode'];
                                                    $sectionName = $rowService['sectionName'];
                                                }
                                            ?>
                                            <tr class="rowSubTask">
                                                <td></td>
                                                <td>
                                                    <?php echo $this->Form->text('service_code', array('value' => $serviceCode,'id'=>'TodoListServiceCode_'.$indexRow,'class' => 'TodoListServiceCode form-control','readonly'=>true, 'placeholder' => TABLE_SERVICE_CODE ,'style' => 'width:100%;')); ?>
                                                </td>
                                                <td>
                                                    <?php echo $this->Form->text('section_name', array('value' => $sectionName,'id'=>'TodoListSectionName_'.$indexRow,'class' => 'TodoListSectionName form-control','readonly'=>true,  'placeholder' => MENU_SECTION_MANAGEMENT ,'style' => 'width:100%;')); ?>
                                                </td>
                                                <td>
                                                    <?php echo $this->Form->input('service_id', array('value' => $todoListDetail['TodoListDetails']['service_id'],'id'=>'TodoListServiceId_'.$indexRow,'name' => 'service_id[]','class'=>'TodoListServiceId form-select', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => 'width:100%;')); ?>
                                                </td>
                                                <td style="text-align:left;">
                                                    <a href="#" style="margin-left:5px;" class="btn-remove-multi btnRemoveRowSubTask">
                                                        <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>dash-square-fill" /></svg>
                                                    </a>
                                                    <a href="#" class="btn-plus-multi btnAddRowSubTask">
                                                        <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus-square-fill" /></svg>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                            $indexRow++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 

            <!-- <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_SUB_TASK;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <table id="tblSubTask" class="table nowrap" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th><?php echo TABLE_NO; ?></th>
                                            <th><?php echo TABLE_SERVICE_CODE; ?></th>
                                            <th><?php echo MENU_SECTION_MANAGEMENT; ?></th>
                                            <th><?php echo MENU_SERVICE_LIST; ?></th>
                                            <th><?php echo ACTION_ACTION; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="rowSubTask" class="rowSubTask" style="visibility: hidden;">
                                            <td></td>
                                            <td>
                                                <?php echo $this->Form->text('service_code', array('id'=>'TodoListServiceCode','class' => 'TodoListServiceCode form-control','readonly'=>true, 'placeholder' => TABLE_SERVICE_CODE ,'style' => 'width:100%;')); ?>
                                            </td>
                                            <td>
                                                <?php echo $this->Form->text('section_name', array('id'=>'TodoListSectionName','class' => 'TodoListSectionName form-control','readonly'=>true,  'placeholder' => MENU_SECTION_MANAGEMENT ,'style' => 'width:100%;')); ?>
                                            </td>
                                            <td>
                                                <?php echo $this->Form->input('service_id', array('id'=>'TodoListServiceId','name' => 'service_id[]','class'=>'TodoListServiceId form-select', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => 'width:100%;')); ?>
                                            </td>
                                            <td style="text-align:left;">
                                                <a href="#" style="margin-left:5px;" class="btn-remove-multi btnRemoveRowSubTask">
                                                    <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>dash-square-fill" /></svg>
                                                </a>
                                                <a href="#" class="btn-plus-multi btnAddRowSubTask">
                                                    <svg class="icon-svg-crud bi  text-btnback-footer" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus-square-fill" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    -->
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
                                    <label for="first-name-icon"><?php echo TABLE_CUSTOMER; ?></label>
                                    <?php echo $this->Form->input('customer_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_EMPLOYEE; ?></label>
                                    <?php echo $this->Form->input('employee_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT, 'style' => '')); ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="TodoListPbCode"><?php echo TABLE_REMARK;?></label>
                                    <?php echo $this->Form->textarea('remark', array('class' => 'form-control','placeholder' => TABLE_REMARK ,'style' => 'height:207px;')); ?>
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
            <button class="btn btn-primary btnBackTodoList text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveTodoList text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div><!--Start Div App-->
<?php echo $this->Form->end(); ?>

