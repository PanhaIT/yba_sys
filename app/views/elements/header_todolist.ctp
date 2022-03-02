<?php
$sqlCom = mysql_query("SELECT id FROM companies WHERE is_active = 1;");
?>
<script type="text/javascript">
    $(document).ready(function(){
        
    });
</script>
<nav class="navbar navbar-header navbar-expand navbar-light" style="margin-top:0px;border-bottom: 0px solid #181c32;">
    <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
    <button class="btn navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse container-fluid" id="navbarSupportedContent">
        <ul class="navbar-nav d-flex align-items-center navbar-light ms-auto">
            <li style="padding-right:10px;"> 
                <label style="color:#555;"><?php echo GENERAL_WELCOME; ?> </label>&nbsp;&nbsp;
                <label style="color:#404972;"><?php echo $user['User']['first_name'].' '.$user['User']['last_name']; ?> </label>
            </li>
            <li>
                <div class="d-none d-md-block d-lg-inline-block" style="color:#b3d1ff;">
                    [<a href="#" class="icon-svg-logout">
                        <svg class="icon-svg-username bi" style="width:16px; height:16px;" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>box-arrow-left" /></svg>&nbsp;<?php echo $html->link(GENERAL_LOG_OUT,array('controller'=>'users','action'=>'logout', 'id' => 'actionLogout')); ?>
                    </a>]&nbsp; |&nbsp;
                    <span class="spinner_placeholder"><img src="<?php echo $this->webroot;?>img/layout/spinner-placeholder.gif"></span>
                    <span class="spinner" style="display:none;"><img src="<?php echo $this->webroot;?>img/layout/spinner.gif"></span>
                </div>
            </li>
        </ul>
    </div>
</nav>