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
        $(".btnLogin").click(function(){
            var formName = "#UserLoginForm";
            if($("#UserUsername").val() == null || $("#UserUsername").val() == ""){
                alertSelectRequireField('username');
                return false;
            }
            if($("#UserPassword").val() == null || $("#UserPassword").val() == ""){
                alertSelectRequireField('password');
                return false;
            }
            if($("#UserUsername").val() != '' && $("#UserPassword").val() != ""){
                $(".option_loading").show();
                $(".option_save").hide();
                $(formName).submit();
            }
        });
    });

    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='username'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_USERNAME;?>";
        }
        if(type=='password'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_PASSWORD;?>";
        }
        $.showConfirm({
            title: "<?php echo TABLE_LOGIN_INFORMATION;?>",
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
<?php echo $this->Form->create('User', array('action' => 'login')); ?>
<input type="hidden" id="lat" name="data[User][lat]" />
<input type="hidden" id="long" name="data[User][long]" />
<input type="hidden" id="accuracy" name="data[User][accuracy]" />

<div class="content" style="">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $this->webroot;?>img/undraw_remotely_2j6y.svg" alt="Image" class="img-fluid">
            </div>
            <div class="col-md-6 contents">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <h3>Sign In</h3>
                            <p class="mb-4" style="color: #b3b3b3;">Username and password are required. Please fill information user login below.</p>
                        </div>
                        <div class="mb-4">
                            <div class="form-group has-icon-left">
                                <div class="position-relative">
                                    <?php echo $this->Form->text('username', array('autocomplete' =>'off','required' => 'required','class' => 'form-control','placeholder' => USER_USER_NAME ,'style' => 'width:100%; font-size:16px;','value'=>'')); ?>
                                    <div class="form-control-icon">
                                        <svg class="icon-svg-username bi" style="width:26px; height:26px;margin-left: -5px;"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-check-fill" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-group has-icon-left">
                                <div class="position-relative">
                                    <?php echo $this->Form->password('password', array('autocomplete' =>'off','required' => 'required','class' => 'form-control','placeholder' => USER_PASSWORD ,'style' => 'width:100%; font-size:16px;','value'=>'')); ?>
                                    <div class="form-control-icon">
                                        <svg class="icon-svg-password bi" style="width:26px; height:26px;margin-left: -5px;" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>lock-fill" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4" style="text-align:center;">
                            <button type="submit" class="btn btn-primary btnLogin" style="width:100%;">
                                <span class="option_save"><label style="text-align:center;font-size:18px;"><?php echo ACTION_LOGIN; ?></label></span>
                                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label style="text-align:center;font-size:16px;"><?php echo ACTION_LOADING; ?></label></span>
                            </button>
                        </div>
                        <div style="text-align:center;color:red;">
                            <label style="color:red;"><?php echo $this->Session->flash(); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->Form->end(); ?>
<style>
.form-control {
    display: block;
    width: 100%;
    padding: .810rem .75rem;
        padding-left: 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
.form-group[class*="has-icon-"].has-icon-left .form-control {
    padding-left: 3rem;
}
.form-group[class*="has-icon-"] .form-control-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    padding: 0 .6rem;
    color: #999;
}

* {
    font-family: "Roboto", sans-serif;
}

p {
    /* color: #b3b3b3; */
    font-weight: 300;
}

.content {
    padding: 7rem 0;
}

h2 {
    font-size: 20px;
}

@media (max-width: 991.98px) {
    .content .bg {
        height: 500px;
    }
}

.content .contents,
.content .bg {
    width: 50%;
}

@media (max-width: 1199.98px) {
    .content .contents,
    .content .bg {
        width: 100%;
    }
}

.content .contents .form-group,
.content .bg .form-group {
    overflow: hidden;
    margin-bottom: 0;
    padding: 15px 15px;
    border-bottom: none;
    position: relative;
    background: #edf2f5;
    border-bottom: 1px solid #e6edf1;
}

.content .contents .form-group label,
.content .bg .form-group label {
    position: absolute;
    top: 50%;
    -webkit-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    transform: translateY(-50%);
    -webkit-transition: .3s all ease;
    -o-transition: .3s all ease;
    transition: .3s all ease;
}

.content .contents .form-group input,
.content .bg .form-group input {
    background: transparent;
}

.content .contents .form-group.first,
.content .bg .form-group.first {
    border-top-left-radius: 7px;
    border-top-right-radius: 7px;
}

.content .contents .form-group.last,
.content .bg .form-group.last {
    border-bottom-left-radius: 7px;
    border-bottom-right-radius: 7px;
}

.content .contents .form-group label,
.content .bg .form-group label {
    font-size: 12px;
    display: block;
    margin-bottom: 0;
    color: #b3b3b3;
}

.content .contents .form-group.focus,
.content .bg .form-group.focus {
    background: #fff;
}

.content .contents .form-group.field--not-empty label,
.content .bg .form-group.field--not-empty label {
    margin-top: -20px;
}

.content .contents .form-control,
.content .bg .form-control {
    border: none;
    padding: 0;
    font-size: 20px;
    border-radius: 0;
}

.content .contents .form-control:active,
.content .contents .form-control:focus,
.content .bg .form-control:active,
.content .bg .form-control:focus {
    outline: none;
    -webkit-box-shadow: none;
    box-shadow: none;
}

.content .bg {
    background-size: cover;
    background-position: center;
}

.content a {
    color: #888;
    text-decoration: underline;
}

.content .btn {
    height: 54px;
    padding-left: 30px;
    padding-right: 30px;
}

.content .forgot-pass {
    position: relative;
    top: 2px;
    font-size: 14px;
}

.social-login a {
    text-decoration: none;
    position: relative;
    text-align: center;
    color: #fff;
    margin-bottom: 10px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: inline-block;
}

.social-login a span {
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
}

.social-login a:hover {
    color: #fff;
}

.social-login a.facebook {
    background: #3b5998;
}

.social-login a.facebook:hover {
    background: #344e86;
}

.social-login a.twitter {
    background: #1da1f2;
}

.social-login a.twitter:hover {
    background: #0d95e8;
}

.social-login a.google {
    background: #ea4335;
}

.social-login a.google:hover {
    background: #e82e1e;
}

.control {
    display: block;
    position: relative;
    padding-left: 30px;
    margin-bottom: 15px;
    cursor: pointer;
    font-size: 14px;
}

.control .caption {
    position: relative;
    top: .2rem;
    color: #888;
}

.control input {
    position: absolute;
    z-index: -1;
    opacity: 0;
}

.control__indicator {
    position: absolute;
    top: 2px;
    left: 0;
    height: 20px;
    width: 20px;
    background: #e6e6e6;
    border-radius: 4px;
}

.control--radio .control__indicator {
    border-radius: 50%;
}

.control:hover input~.control__indicator,
.control input:focus~.control__indicator {
    background: #ccc;
}

.control input:checked~.control__indicator {
    background: #6c63ff;
}

.control:hover input:not([disabled]):checked~.control__indicator,
.control input:checked:focus~.control__indicator {
    background: #847dff;
}

.control input:disabled~.control__indicator {
    background: #e6e6e6;
    opacity: 0.9;
    pointer-events: none;
}

.control__indicator:after {
    font-family: 'icomoon';
    content: '\e5ca';
    position: absolute;
    display: none;
    font-size: 16px;
    -webkit-transition: .3s all ease;
    -o-transition: .3s all ease;
    transition: .3s all ease;
}

.control input:checked~.control__indicator:after {
    display: block;
    color: #fff;
}

.control--checkbox .control__indicator:after {
    top: 50%;
    left: 50%;
    margin-top: -1px;
    -webkit-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
}

.control--checkbox input:disabled~.control__indicator:after {
    border-color: #7b7b7b;
}

.control--checkbox input:disabled:checked~.control__indicator {
    background-color: #7e0cf5;
    opacity: .2;
}

</style>