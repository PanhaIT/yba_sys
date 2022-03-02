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
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-select.js"></script>
<script type="text/javascript">
    $(document).ready(function(){   
        $("#progress,#connectStatus").html('');
        eventWindow();
    });
    function eventWindow(){
        var fullscreenElement = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
        var fullScreen = 0;
        var headerHeight = parseInt($('.header_todolist').height()); 
        var tooterHeight = parseInt($('.tooterHeight').height()); 
        window=jQuery(window).height();
        maintContent = window.innerHeight-(headerHeight+tooterHeight);
        heightContent = maintContent+'px';
        $(".mainContent").css({'height':heightContent});
        $(window).trigger('resize');
        document.addEventListener("fullscreenChange", function () {
            if (fullscreenElement != null) {
                window.location.reload();
            } else {
                window.location.reload();     
            }
        });
        $(document).unbind("keydown").keydown(function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 122) {
                window.location.reload();
                // fullScreen = (fullScreen == 1) ? 0 : 1;
            }
        });
    }
</script>

<div id="app">
    <div id="sidebar" class='active'>
        <input type="hidden" class="eventKeyWindow" value="">
        <?php echo $this->element('menu_todolist') ;?>
    </div>
    <div id="main" height="100%">
        <div class="col header_todolist" style="height:120px;">
            <div class="col" style="height:70px;">
                <?php echo $this->element('header_todolist') ;?>
            </div>
            <div class="col" style="background-color: #eff0f6;height:50px;padding:5px 5px 5px 5px;">
                <ul id="tab-list" class="nav nav-tabs" role="tablist">
                    <li class="nav-item active">
                        <a name="Dashboard" module="Dashboard" class="nav-link active" id="tab_1" href="#tab_1" role="tab" data-bs-toggle="tab">
                            <table><tr><td><svg class="icon-svg-tab bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>grid" /></svg></td><td><span>Dashboard</span></td></tr></table>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="container-fluid mainContent" style="margin-top:0px; overflow-y: scroll; width:100%;">
            <div id="tab-content" class="tab-content">
                <div class="tab-pane fade in active show content" id="tab_1" role="tabpanel"></div>
            </div>
        </div>
        <div height="50px" class="tooterHeight">
            <footer class="footer navbar navbar-header navbar-expand navbar-light" style="height:50px;bottom:0px;width:100%;background-color: #eff0f6;">
                <label class="sidebar-toggler" href="#"></label>
                <button class="btn navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedFooter" aria-controls="navbarSupportedFooter" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedFooter">
                    <ul class="navbar-nav d-flex align-items-center navbar-light ms-auto" style="color:#404972;">
                        <li>© Copyright 2022 YBA. All rights reserved. V.1.0.0</li>
                    </ul>
                </div>
            </footer>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/feather-icons/feather.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/main.js"></script>
