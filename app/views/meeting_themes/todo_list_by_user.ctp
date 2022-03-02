<?php 
$this->element('check_access');
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http : //www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http : //www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function(){
        backEventView(oTableMeetingTheme,"btnBackMeetingTheme");
        $("#tab-user-todolist").find("li a").unbind("click").click(function(){
            obj = $(this);
            var tabID = obj.attr('user_id');
            $('.nav_user_list').each(function() {
                navList = $(this);
                navList.find("a").attr("class","user_nav_link");
                if(navList.find("a").attr('user_id') == tabID){
                    navList.find("a").attr("class","user_nav_link active");
                }
            });
            $('.tab-pane-todolist').each(function() {
                var index = $(this).attr('id').split('_');
                $(this).attr("class","tab-pane-todolist fade");
                $(this).css({'display':'none'});
                if(index[1] == tabID){
                    $(this).attr("class","tab-pane-todolist fade in active show");
                    $(this).css({'display':''});
                    var obj = $(this);
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/todoList/" + tabID,
                        beforeSend: function(){
                            $(".loading").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="text-align: center;" />');
                        },
                        success: function(result){
                            $(".loading").html('');
                            obj.html('');
                            obj.html(result);
                        }
                    });
                }
            });
        });
        $("#tab-user-todolist").find("li a:first").click();
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <ul id="tab-user-todolist" class="nav nav-tabs tab-user-todolist" role="tablist">
            <?php
            foreach($users AS $key => $value){
            ?>
            <li class="nav_user_list">
                <a class="user_nav_link" user_id="<?php echo $value['User']['id'];?>" id="user-tab_1" href="#user-tab_1" role="tab" data-bs-toggle="tab">
                    <span><?php echo $value['User']['first_name'].' '.$value['User']['last_name'];?></span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
    <div class="row">
        <div class="col" id="tab-content-todolist" class="tab-content-todolist" style="margin-top:15px;">
            <?php
            foreach($users AS $key => $value){
            ?>
            <div class="col-sm tab-pane-todolist fade" user_id="<?php echo $value['User']['id'];?>" id="tab_<?php echo $value['User']['id'];?>" role="tabpanel" style="display:none;"></div>
            <?php } ?>
        </div>
    </div>
    <div style="z-index : 100;padding : 1px 15px 5px 0px;margin : 0px 0px 0px 0px; bottom : 0px;position : fixed;width:100%; height : 50px;">
        <a style="color : white;">
            <button  class="btn btn-primary btnBackMeetingTheme text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink : href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

