<?php
$db = ConnectionManager::getDataSource('default');
mysql_select_db($db->config['database']);
$sqlCheckConfig = mysql_query("SELECT id FROM test");
if (!@mysql_num_rows($sqlCheckConfig)) {
    echo 'path.php not config properly... Please recheck it again. <a href="">Refresh</a>';
    exit();
}
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Set Cache
        localStorage.setItem("products", "[]");
        localStorage.setItem("modified", "");
        // Set Form Validate
        $("#UserLoginForm").validationEngine();
        // clear cookie
        $.cookie('cookieTitle', null, { expires: 7, path: "/" });
        $.cookie('cookieHref', null, { expires: 7, path: "/" });
        $.cookie('cookieTabIndex', null, { expires: 7, path: "/" });
        // Check & Focus
        if($("#UserUsername").val() != ''){
            $("#UserPassword").focus();
        } else {
            $("#UserUsername").focus();
        }
        
        $(".btnLogin").click(function(){
            var formName = "#UserLoginForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                $(this).val('Loading...').attr('disabled', true);
                $(formName).submit();
            }
        });
    });
</script>
<?php echo $this->Form->create('User', array('action' => 'login')); ?>
<input type="hidden" id="lat" name="data[User][lat]" />
<input type="hidden" id="long" name="data[User][long]" />
<input type="hidden" id="accuracy" name="data[User][accuracy]" />


<div id="app form-body">
    <div class="row">
        <div class="col-sm-8" style=""></div>
        <div class="col-sm-4" style="">
            <div class="col-12">
                <div class="form-group">
                    <label for="first-name-icon"><?php echo TABLE_CUSTOMER; ?></label><label class="require-label">*</label>
                    <?php echo $this->Form->text('code', array('class' => 'form-control','readonly'=>true,'required'=>'required', 'placeholder' => TABLE_CODE ,'style' => '')); ?>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="first-name-icon"><?php echo TABLE_EMPLOYEE; ?></label>
                    <?php echo $this->Form->text('code', array('class' => 'form-control','readonly'=>true,'required'=>'required', 'placeholder' => TABLE_CODE ,'style' => '')); ?>
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
</div>



<!-- <table cellpadding="8" cellspacing="0" id="login" style="width: 100%;">
    <tr>
        <td style="text-align: center; padding-top: 5px;">
            <img alt="" src="<?php echo $this->webroot; ?>img/logo.png" style="height:65%; max-height: 100px; max-width: 220px; margin-top: 5px;" />
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"><?php echo $this->Session->flash(); ?></td>
    </tr>
    <tr>
        <th class="title" style="color: red;">User Login</th>
    </tr>
    <tr>
        <td style="text-align: center;">
            <input id="UserUsername" placeholder="Username" class="validate[required]" type="text" name="data[User][username]" />
        </td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <input id="UserPassword" placeholder="Password" class="validate[required]" type="password" name="data[User][password]" />
        </td>
    </tr>
    <?php if ($log >= 3) { ?>
    <tr>
        <td style="text-align: center;">
            <div style="width: 300px; display: inline-block; height: 70px;">
                <img alt="" id="secret" align="left" style="border: 0;" src="captcha/securimage_show_example.php?sid=<?php echo md5(time()) ?>" />
                <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_as3" align="middle">
                    <param name="allowScriptAccess" value="sameDomain" />
                    <param name="allowFullScreen" value="false" />
                    <param name="movie" value="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" />
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#ffffff" />
                    <param name="wmode" value="transparent" />
                    <embed src="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_as3" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent" />
                </object>
                <br />
                <a style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('secret').src = 'captcha/securimage_show_example.php?sid=' + Math.random(); return false"><img src="<?php $this->webroot; ?>captcha/images/refresh.png" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" /></a>
            </div>
            <div class="clearer"></div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center;"><?php echo $form->text('code', array('placeholder' => 'Security Code', 'class' => 'validate[required]', 'value' => '')); ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td style="text-align: center;">
            <input type="submit" name="loginbtn" class="flatbtn-blu btnLogin" value="Log In" tabindex="3">
        </td>
    </tr>
</!--> -->
<?php echo $this->Form->end(); ?>