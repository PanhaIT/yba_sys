<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    #main_page{
        margin:0px 0px 0px 0px;
        padding:0px 0px 0px 0px;
        width:100%;
    }
    .viewTodoListDialog {
        max-width: 1500px;
        margin: 18.75rem auto;
        margin-top: 100px;
    }
</style>
<?php
    $this->element('check_access');
    $allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
    $tblName = "tblMeetingTheme" . rand(); 
?>
<script type="text/javascript">
    var oTableTodoList;
    $(document).ready(function(){
        $("#progress,#connectStatus").html('');
        oTableTodoList=$("#<?php echo $tblName;?>").dataTable({
            "scrollY": "57vh",
            "scrollCollapse": true,
            "processing": true,
            "serverSide": true,
            "autoWidth":true,
            "ordering":true,
            "sAjaxSource": "<?php echo $this->base . '/'. $this->params['controller'];?>/todoListAjax/<?php echo $userId;?>",
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $(".btnViewTodoListMeetingTheme").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewTodoList/" + id,
                        data: "",
                        beforeSend: function(){},
                        success: function(result){
                            $.showConfirm({
                                title: "<?php echo MENU_TODO_LIST;?>",
                                body: result,
                                modalDialogClass: "viewTodoListDialog",
                                textFalse: "<?php echo TABLE_CANCEL;?>",
                                textTrue: "<?php echo TABLE_OK;?>"
                            });
                        }
                    });
                });
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                $(".btnApproveTodoListMeetingTheme").click(function(event){
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
                                        url: "<?php echo $this->base.'/'; ?>todo_lists/approve/" + id,
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
    });
</script>
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
