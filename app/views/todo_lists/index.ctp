<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
    #main_page{
        margin:0px 0px 0px 0px;
        padding:0px 0px 0px 0px;
        width:100%;
    }
    .shareTodolist {
        max-width: 1100px;
        margin: 18.75rem auto;
        margin-top: 100px;
    }
    .startDate,.endDate {
        width:120px;
    }
    .approveDate{
        width:200px;
    }
    </style>
</head>
<?php
    $this->element('check_access');
    $allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
    $tblName = "tbl" . rand(); 
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-select.js"></script>
<script type="text/javascript">
    var oTableTodoList;
    $(document).ready(function(){
        $("#progress,#connectStatus").html('');
        oTableTodoList=$("#<?php echo $tblName;?>").dataTable({
            "scrollY": "57vh",
            "scrollCollapse": true,
            "processing": true,
            "serverSide": true,
            "autoWidth":false,
            "ordering":true,
            "sAjaxSource": "<?php echo $this->base . '/'. $this->params['controller'];?>/ajax/"+$("#priorityId").val()+"/"+$("#progresseId").val()+"/"+$("#customerId").val()+"/"+$("#serviceId").val()+"/"+$(".startDate").val()+"/"+$(".endDate").val(),
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                $(".btnViewTodoList").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });

                $(".btnCloneTodoList").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var cloneId = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addTodoList/" + cloneId);
                });

                $(".btnEditTodoList").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });

                $(".btnShareTodoList").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var owner = $(this).attr('owner');
                    if(id!=''){
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/shareTodolist/"+id+"/"+owner,
                            beforeSend: function(){},
                            success: function(result){
                                $.showConfirm({
                                    title: "<?php echo MENU_TODO_LIST;?>",
                                    body: result,
                                    modalDialogClass: "shareTodolist",
                                    textFalse: "<?php echo ACTION_CANCEL;?>",
                                    textTrue: "<?php echo ACTION_SAVE;?>",
                                    onSubmit: function(result) {
                                        if(result){
                                            var formName = "#UserShareTodolistForm";
                                            $.ajax({
                                                dataType: "json",
                                                type: "POST",
                                                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/shareTodolist/"+id+"/"+owner,
                                                data: $("#UserShareTodolistForm").serialize()+"&todo_list_id="+id,
                                                beforeSend: function(){},
                                                success: function(result){
                                                    var smsAlert='';
                                                    if(result.error==0){
                                                        smsAlert='<?php echo MESSAGE_DATA_HAS_BEEN_SAVED;?>';
                                                        iconType = "success";
                                                    }else if(result.error==0){
                                                        smsAlert='<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED;?>';
                                                        iconType = "error";
                                                    }
                                                    // alert message
                                                    Swal.fire({
                                                        icon: iconType,
                                                        title: smsAlert
                                                    });
                                                }
                                            });
                                        }
                                    }
                                });
                                var user = $('.userShareTodolist').bootstrapDualListbox({
                                    moveOnSelect: true
                                });
                                $(".shareTodolist .bootstrap-duallistbox-container .filter").css({'width':'100%'});
                                $(".btn-group, .btn-group-vertical").css({'display':'none'});
                            }
                        });
                    }
                });

                $(".btnDeleteTodoList").click(function(event){
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $.showConfirm({
                        title: "<?php echo MENU_TODO_LIST;?>",
                        body: "Are you sure want to delete TodoList?",
                        textFalse: "<?php echo TABLE_CANCEL;?>",
                        textTrue: "<?php echo TABLE_OK;?>",
                        onSubmit: function(result) {
                            if(result){
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){},
                                    success: function(result){
                                        oTableTodoList.fnDraw(false);
                                        Swal.fire({
                                            icon: "success",
                                            title: result
                                        })
                                    }
                                });
                            }
                        },
                        onDispose: function() {}
                    });
                });

                $(".btnApproveTodoList").click(function(event){
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    if(id!=""){
                        $.showConfirm({
                            title: "<?php echo MENU_TODO_LIST;?>",
                            body: '<table style="width:100%;text-align:center;"><tr><td style="text-align:right;width:40%;">Approve Date : </td><td style="text-align:left;width:35%;"><input type="text" readonly="readonly" required="required" class="form-control approveDate" placeholder="approve date" value=""></td><td style="width:30%;"><a href="#" style="text-align:left;float:left;" class="btn-remove-item btnClearApproveDate" data-bs-toggle="tooltip" data-bs-placement="left" title="Clear Date"><svg class="icon-svg-item"><use xlink:href="<?php echo $this->webroot;?>assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a></td></tr></table>',
                            textFalse: "<?php echo ACTION_CANCEL;?>",
                            textTrue: "<?php echo ACTION_SAVE;?>",
                            onSubmit: function(result) {
                                if(result && $(".approveDate").val()!=""){
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/approve/" + id,
                                        data: "approve_date="+$(".approveDate").val(),
                                        beforeSend: function(){},
                                        success: function(result){
                                            oTableTodoList.fnDraw(false);
                                            Swal.fire({
                                                icon: "success",
                                                title: result
                                            })
                                        }
                                    });
                                }
                            },
                            onDispose: function() {}
                        });
                        $(".btnClearApproveDate").click(function(){
                            $(".approveDate").val('');
                        });
                        $('.approveDate').datepicker({
                            format: 'yyyy-mm-dd',
                            orientation: "bottom",
                            autoclose: 1,
                            todayHighlight: 1
                        });
                    }
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });    

        $('#priorityId,#progresseId,#customerId,#serviceId,.startDate,.endDate').change(function(){
            var Tablesetting = oTableTodoList.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#priorityId").val()+"/"+$("#progresseId").val()+"/"+$("#customerId").val()+"/"+$("#serviceId").val()+"/"+$(".startDate").val()+"/"+$(".endDate").val();
            oTableTodoList.fnDraw(false);
        });

        $('.btnAddTodoList').unbind('click').click(function (event) {
            event.preventDefault();
            var cloneId='';
            var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel[active='1']");
            leftPanel.toggle("'slide',{direction:'left'},5000",function(){rightPanel.show()});
            $("#connectStatus").html('');
            rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addTodoList/"+cloneId);
        });

        $('.btnClearFromToDate').unbind('click').click(function (event) {
            $('[data-bs-toggle="tooltip"]').tooltip('hide');
            $('.startDate,.endDate').val('');
            var Tablesetting = oTableTodoList.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#priorityId").val()+"/"+$("#progresseId").val()+"/"+$("#customerId").val()+"/"+$("#serviceId").val()+"/"+$(".startDate").val()+"/"+$(".endDate").val();
            oTableTodoList.fnDraw(false);
        });
        
        $('.startDate,.endDate').datepicker({
            format: 'yyyy-mm-dd',
            orientation: "bottom",
            autoclose: 1,
            todayHighlight: 1
        });
    });
</script>
<div class="leftPanel">
    <div style="border: 1px dashed #bbbbbb; width:100%; margin-top:1.5rem;">
        <div class="row">
            <div class="col filter-margin">
                <div class="col-sm" style="float:left;">
                    <div class="col">
                        <a><button class="btn btn-primary btnAddTodoList"><svg class="icon-svg-custom bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus" /></svg><label class="label_crud"><?php echo TABLE_ADD_NEW_TODO_LIST; ?></label></button></a>
                    </div>
                </div>
                <div class="col-sm" style="float:right;">
                    <div class="col">
                        <table class="tblTodolist">
                            <tr>
                                <td class="label-filter"><label for="startDate">Date</label></td>
                                <td>
                                   <input type="text" readonly="readonly" class="form-control startDate" placeholder="from date" value="">
                                </td>
                                <td>
                                   <input type="text" readonly="readonly" class="form-control endDate" placeholder="to date" value="" style="margin-left:10px;">
                                </td>
                                <td><a href="#" class="btn-remove-item btnClearFromToDate" data-bs-toggle="tooltip" data-bs-placement="left" title="Clear Date"><svg class="icon-svg-item"><use xlink:href="<?php echo $this->webroot;?>assets/vendors/bootstrap-icons/bootstrap-icons.svg#trash-fill" /></svg></a></td>
                                <td class="label-filter"><label for="serviceId"><?php echo MENU_SERVICE_MANAGEMENT;?></label></td>
                                <td>
                                    <select id="serviceId" class="form-select" style="width:250px;">
                                        <option value="all"><?php echo INPUT_ALL;?></option>
                                        <?php
                                        foreach($services AS $key=>$value){
                                        ?>
                                        <option value="<?php echo $value['Service']['id'];?>"><?php echo $value['Service']['name'];?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="label-filter"><label for="customerId"><?php echo TABLE_CUSTOMER;?></label></td>
                                <td>
                                    <select id="customerId" class="form-select" style="width:200px;">
                                        <option value="all"><?php echo INPUT_ALL;?></option>
                                        <?php
                                        foreach($customers AS $key=>$value){
                                        ?>
                                        <option value="<?php echo $value['Customer']['id'];?>"><?php echo $value['Customer']['customer_code'].'-'.$value['Customer']['name'];?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="label-filter"><label for="priorityId"><?php echo TABLE_PRIORITY;?></label></td>
                                <td>
                                    <select id="priorityId" class="form-select" style="width:100px;">
                                        <option value="all"><?php echo INPUT_ALL;?></option>
                                        <?php
                                        foreach($priorities AS $key=>$value){
                                        ?>
                                        <option value="<?php echo $key;?>"><?php echo $value;?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="label-filter"><label for="progresseId"><?php echo TABLE_STATUS;?></label></td>
                                <td>
                                    <select id="progresseId" class="form-select" style="width:100px;">
                                        <option value="all"><?php echo INPUT_ALL;?></option>
                                        <?php
                                        foreach($progresses AS $key=>$value){
                                        ?>
                                        <option value="<?php echo $key;?>"><?php echo $value;?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content" style="width:100%;height:100%;padding:10px 0px 0px 0px;">
        <table id="<?php echo $tblName;?>" class="display table table-hover table-striped nowrap" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CUSTOMER; ?></th>
                    <th><?php echo TABLE_TASK_NAME; ?></th>
                    <th><?php echo TABLE_ESTIMATE_DATE; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                    <th><?php echo TABLE_REMARK; ?></th>
                    <th><?php echo TABLE_CREATED_BY; ?></th>
                    <th><?php echo TABLE_PRIORITY; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="rightPanel"></div>