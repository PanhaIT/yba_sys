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

        <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>

        <!-- <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/vendors/perfect-scrollbar/perfect-scrollbar.css"> -->
        
        <!-- <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/css/app.css"> -->
        
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/vendors/bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/vendors/sweetalert2/sweetalert2.min.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/sweetalert2/sweetalert2.all.min.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/resources/syntax/shCore.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/resources/syntax/shCore.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/resources/demo.js"></script>

        <!--Slim Boot Bootstrap Select jquery-3.2.1.slim.min.js & bootstrap.min.css & bootstrap.bundle.min.js & -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.min.css" />

        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-select.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-select.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery-3.5.1.js"></script>
        <!--Datatable-->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery.dataTables.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/dataTables.bootstrap4.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/dataTables.bootstrap4.css"/>
        
        <!--Confirm Modal-->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-show-modal.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/mazer.js"></script>
        <!-- <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/main.js"></script>     -->
        
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery.cookie.min.js"></script>
        <!-- <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/app.js"></script> -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style/style.css"/>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-datepicker.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-datetimepicker.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-datetimepicker.js"></script>
        
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/filepond/css/filepond.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/filepond/css/filepond-plugin-image-preview.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/filepond/js/filepond.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/filepond/js/filepond-plugin-image-crop.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/filepond/js/filepond-plugin-image-preview.js"></script>

        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery.form.min.js"></script>
        
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>assets/vendors/toastify/toastify.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/vendors/toastify/toastify.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>assets/vendors/choices.js/choices.min.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/vendors/choices.js/choices.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/pages/form-element-select.js"></script>


        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>ssets/vendors/perfect-scrollbar/perfect-scrollbar.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>assets/css/app.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/feather-icons/feather.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/vendors.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/main.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/app.js"></script> -->
        
        <style type="text/css">
           
            /* body{
                overflow: hidden;
            }
            .ui-tabs-panel{overflow-y: scroll;}
            .key {
                min-width: 18px;
                height: 18px;
                margin: 2px;
                padding: 2px;
                text-align: center;
                font: 14px/18px sans-serif;
                color: #777;
                background: #EFF0F2;
                border-top: 1px solid #F5F5F5;
                text-shadow: 0px 1px 0px #F5F5F5;
                -webkit-box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                -moz-box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                box-shadow: inset 0 0 25px #eee, 0 1px 0 #c3c3c3, 0 2px 0 #c9c9c9, 0 2px 3px #333;
                display: inline-block;
                -moz-border-radius: 1px;
                border-radius: 1px;
            }
            h1 .key {
                width: 42px;
                height: 40px;
                font: 15px/40px sans-serif;
                -moz-border-radius: 5px;
                border-radius: 5px;
            } */
        </style>
        <script type="text/javascript">
            var ajaxConnection;
            var isCheck = 1;
            var isBort  = 0;
            function refreshTable() {
                $('.dataTable').each(function() {
                    dt = $(this).dataTable();
                    dt.fnDraw();
                })
            }
            
            $(document).ready(function(){
                $("#progress,#connectStatus,#progress").html('');
                // Check Product Cache
                // if (localStorage.getItem("products") == null || localStorage.getItem("products") == '[]' || localStorage.getItem("products") == '') {
                //     getProductCache();
                // } else {
                //     // Check Connection
                //     checkConnectionPOS();
                // }
                if($.cookie('showStock') != null ) {
                    $("#showStock").attr("checked", true);
                }else{
                    $("#showStock").attr("checked", false);
                }
                $("#showStock").click(function(){
                    $.cookie("showStock", 1, {
                        expires : 5,
                        path    : '/'
                    });
                });
            });
        </script>
    </head>
    <body>
        <div class="ui-layout-center">
            <div id="main_page">
                <?php echo $this->Session->flash(); ?>
                <?php echo $content_for_layout; ?>
            </div>
        </div>
        <div id="progress">
            <?php //echo TABLE_POS_PROCESSING; ?>
        </div>
        <div id="connectStatus"></div>
        <div id="printLayoutPOS" style="display: none;">
            <?php echo $this->element('print/print_pos'); ?>
        </div>
        <div id="dialog" title=""></div>
        <div id="dialogConfirm" title=""></div>
        <div id="addProductDialog"></div>
        <div id="dialog1" title=""></div>
        <div id="dialog2" title=""></div>
        <div id="dialog3" title=""></div>
        <div id="dialog4" title=""></div>
        <div id="dialogModal" title=""></div>
    </body>
</html>