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
    var oTableEmployee;
    $(document).ready(function(){   
        $("#progress,#connectStatus").html('');
        oTableEmployee=$("#<?php echo $tblName;?>").dataTable({
            "scrollY": "57vh",
            "scrollCollapse": true,
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "ordering": true,
            "sAjaxSource": "<?php echo $this->base . '/'. $this->params['controller'];?>/ajaxEmployee",
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $(".btnViewEmployee").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewEmployee/" + id);
                });
                $(".btnEditEmployee").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/editEmployee/" + id);
                });

                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                $(".btnDeleteEmployee").click(function(event){
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $.showConfirm({
                        title: "<?php echo TABLE_EMPLOYEE_INFORMATION;?>",
                        body: "Are you sure want to delete employee?",
                        textFalse: "<?php echo TABLE_CANCEL;?>",
                        textTrue: "<?php echo TABLE_OK;?>",
                        onSubmit: function(result) {
                            if(result){
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/deleteEmployee/" + id,
                                    data: "",
                                    beforeSend: function(){},
                                    success: function(result){
                                        oTableEmployee.fnDraw(false);
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
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        $('.btnAddEmployee').unbind('click').click(function (event) {
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
            leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
            $("#connectStatus").html('');
            rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addEmployee");
            $("#selectpickerId").selectpicker({width: 800});
            $("#selectpickerId").selectpicker('refresh').trigger("change");
        });
    });
</script>
<div class="leftPanel">
    <div class="" style="border: 1px dashed #bbbbbb; width:100%; margin-top:1.5rem;">
        <a><button class="btn btn-primary btnAddEmployee text-btn-plus"><svg class="icon-svg-custom bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus" /></svg><label class="label_crud"><?php echo MENU_EMPLOYEE_MANAGEMENT_ADD; ?></label></button></a>
    </div>
    <div class="content" style="width:100%;height:100%;padding:10px 0px 0px 0px;">
        <table id="<?php echo $tblName;?>" class="display table table-hover table-striped nowrap" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo TABLE_NO; ?></th>
                    <th ><?php echo TABLE_EMPLOYEE_PROFILE_PHOTO; ?></th>
                    <th ><?php echo TABLE_EMPlOYEE_GROUP; ?></th>
                    <th ><?php echo TABLE_EMPLOYEE_NUMBER; ?></th>
                    <th ><?php echo TABLE_NAME; ?></th>
                    <th ><?php echo TABLE_TELEPHONE; ?></th>
                    <th ><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="rightPanel"></div>