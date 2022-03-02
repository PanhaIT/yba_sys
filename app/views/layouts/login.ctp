<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$start = 2018;
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>
            <?php __('Neak Srok - Login'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/logo.ico" />

        <!-- general stylesheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/login.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>

        <!-- jquery ui -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/development-bundle/themes/base/jquery.ui.all.css" />

        <!-- validator -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>
        
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
    </head>
    <body style="background-color: #76c3fc;">
        <!-- <div style="width: 1006px; height: 546px; position: absolute; top: 50%; left: 50%; margin-left: -503px; margin-top: -273px;" id="loginForm">
            <div style="float: left; border: none; margin: 4px; width: 657px; height: 537px;">
                <img alt="" id="secret" align="left" style="border: 0;" src="<?php echo $this->webroot; ?>img/login-img.png" />
            </div>
            <div style="width: 340px; height: 540px; margin: 0px auto; float: right;">
                <div style="width: 100%; height: 505px;">
                    <?php echo $content_for_layout; ?>
                </div>
                <div style="width: 100%; text-align: center; display:none;">
                    © <?php echo $start; ?><?php echo date("Y") != $start ? "-" . date("Y") : ""; ?> Neak Srok. All rights reserved.<br/>
                    Version 1.0.0
                </div>
            </div>
            <div class="clear"></div>
        </div> -->

        
    </body>
</html>