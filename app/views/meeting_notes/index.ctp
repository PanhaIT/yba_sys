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
    .shareMeetingNote {
        max-width: 1100px;
        margin: 18.75rem auto;
        margin-top: 100px;
    }
    .panel-heading{
        display: inline-block;
        max-width: 200px;
        max-height: 25px;
        white-space: nowrap;
        overflow: hidden !important;
        text-overflow: ellipsis;
    }
    </style>
</head>
<?php
    $this->element('check_access');
    $allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
    $tblName = "tbl" . rand(); 
?>
<script type="text/javascript">
    var oTableMeetingNote;
    $(document).ready(function(){
        $("#progress,#connectStatus").html('');
        oTableMeetingNote=$("#<?php echo $tblName;?>").dataTable({
            "scrollY": "57vh",
            "scrollCollapse": true,
            "processing": true,
            "serverSide": true,
            "autoWidth":true,
            "ordering":true,
            "sAjaxSource": "<?php echo $this->base . '/'. $this->params['controller'];?>/ajax",
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                $(".btnViewMeetingNote").click(function(event){
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

                $(".btnCloneMeetingNote").click(function(event){
                    event.preventDefault();
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel[active='1']");
                    leftPanel.toggle("'slide', {direction: 'left' }, 5000",function(){rightPanel.show()});
                    $("#connectStatus").html('');
                    rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/" + id);
                });

                $(".btnEditMeetingNote").click(function(event){
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

                $(".btnDeleteMeetingNote").click(function(event){
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $.showConfirm({
                        title: "<?php echo MENU_TODO_LIST;?>",
                        body: "Are you sure want to delete Meeting Note?",
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
                                        oTableMeetingNote.fnDraw(false);
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
            }],
            "aaSorting": [[0, "desc"]]
        }); 
        $('.btnAddMeetingNote').unbind('click').click(function (event) {
            event.preventDefault();
            var id='';
            var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel[active='1']");
            leftPanel.toggle("'slide',{direction:'left'},2000",function(){rightPanel.show()});
            $("#connectStatus").html('');
            rightPanel.html('<img class="progress_loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif"/>');
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/"+id);
        });
    });
</script>
<div class="leftPanel">
    <div style="border: 1px dashed #bbbbbb; width:100%; margin-top:1.5rem;">
        <div class="row">
            <div class="col filter-margin">
                <div class="col-sm" style="float:left;">
                    <div class="col">
                        <a><button class="btn btn-primary btnAddMeetingNote"><svg class="icon-svg-custom bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>plus" /></svg><label class="label_crud"><?php echo TABLE_ADD_NEW_MEETING_NOTE; ?></label></button></a>
                    </div>
                </div>
                <div class="col-sm" style="float:right;"></div>
            </div>
        </div>
    </div>
    <div class="content" style="width:100%;height:100%;padding:10px 0px 0px 0px;">
        <table id="<?php echo $tblName;?>" class="display table table-hover table-striped nowrap" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo MENU_EMPLOYEE_GROUP; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                    <th><?php echo TABLE_CREATED_BY; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="rightPanel"></div>