<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>
            <?php __('Neak Srok - System Configuration'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/logo.ico" />

        <!-- general stylesheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/login.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>

        <!-- jquery ui -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/jquery-ui-1.8.14.custom.min-en.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.closable.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.paging.js"></script>
        
        <!-- autoNumeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>
        
        <!-- validator -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/posabsolute-jQuery-Validation-Engine-25e4691/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/posabsolute-jQuery-Validation-Engine-25e4691/css/template.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/posabsolute-jQuery-Validation-Engine-25e4691/js/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/posabsolute-jQuery-Validation-Engine-25e4691/js/jquery.validationEngine.js"></script>
    </head>
    <body>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
        <center>
            <table style="height: 100%;">
                <tr>
                    <td style="vertical-align: middle;">
                        <div>
                            <?php echo $content_for_layout; ?>
                            <div class="clear"></div>
                        </div>
                        <div id="div_footer">
                            <div style="padding: 2px;"></div>
                            Powered by Udaya Technology Co., Ltd.
                            <div style="padding: 2px;"></div>
                            <center><a href="http://www.udaya-tech.com/" target="_blank" style="text-decoration: none;"><div style="width: 85px;" onmouseover="Tip('<center><img alt=&quot;&quot; src=&quot;<?php echo $this->webroot; ?>img/udaya.png&quot; style=\'padding: 10px;\' /><table><tr><th style=\'text-align: left;\'>Tel/Fax:</th><td style=\'white-space: nowrap;\'>023 881 887/081 881 887</td></tr><tr><th style=\'text-align: left;\'>Email:</th><td style=\'white-space: nowrap;\'>info@udaya-tech.com</td></tr></table></center>', ABOVE, true, CENTERMOUSE, true)"><div style="float: left;padding: 5px;background: #008cbd;color: #FFF;width: 20px;font-weight: bold;">UT</div><div style="float: left;background: #FFF;font-size: 5px;">&nbsp;</div><div style="float: left;padding: 5px;background: #ea8326;color: #FFF;width: 40px;">Acc</div></div></a></center>
                        </div>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>