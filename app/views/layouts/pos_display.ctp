<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$config = getSysconfig();
if(!empty($config)){
    $start = $config['start'];
}else{
    $start = "";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <?php echo $this->element('embed_font'); ?>

        <title><?php __('Neak Srok - POS'); ?></title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/logo.ico" />

        <!-- General stylesheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos_style.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        
        <!-- jquery ui -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/js/jquery-ui-1.10.0.custom.min.js"></script>

        <!-- jquery slideshow -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/photo_slide/responsiveslides.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/photo_slide/responsiveslides.min.js"></script>
        
        <style type="text/css">
            body{
                overflow: hidden;
            }
        </style>
    </head>
    <body style="background-color: #76c3fc;">
        <div class="ui-layout-center">
            <?php echo $content_for_layout; ?>
        </div>
        <div id="dialog" title=""></div>
    </body>
</html>