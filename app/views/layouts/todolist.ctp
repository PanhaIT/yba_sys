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
        <title><?php __('Business Advisor'); ?></title>

        <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>

        <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/vendors/bootstrap-icons/bootstrap-icons.css">
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/vendors/sweetalert2/sweetalert2.min.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/sweetalert2/sweetalert2.all.min.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/resources/syntax/shCore.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/resources/syntax/shCore.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/resources/demo.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.min.css" />

        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-select.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-select.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery-3.5.1.js"></script>

        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/bootstrap-show-modal.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/mazer.js"></script>

        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery.dataTables.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/dataTables.bootstrap4.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/dataTables.bootstrap4.css"/>
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/dataTables.bootstrap4.min.css"/> -->
        
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery.cookie.min.js"></script>
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

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>assets/css/app_voler.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>assets/vendors/perfect-scrollbar/perfect-scrollbar.css"/>

        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/feather-icons/feather.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>assets/js/main.js"></script>
       
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/bootstrap-tab/css/all.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/bootstrap-tab/css/bootstrap-tabs-x-bs4.css"/>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap-tab/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap-tab/js/bootstrap-tabs-x.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot;?>js/bootstrap-duallistbox-4/src/bootstrap-duallistbox.css">
        <script src="<?php echo $this->webroot;?>js/bootstrap-duallistbox-4/src/jquery.bootstrap-duallistbox.js"></script>

        <link rel="stylesheet" href="<?php echo $this->webroot;?>assets/vendors/summernote/summernote-lite.min.css">
        <script src="<?php echo $this->webroot;?>assets/vendors/summernote/summernote-lite.min.js"></script>

        <style type="text/css">
        </style>
        <script type="text/javascript">
            var ajaxConnection;
            var isCheck = 1;
            var isBort  = 0;
            $(document).ready(function(){
                $("#progress,#connectStatus,#progress").html('');
            });

            function refreshTable() {
                $('.dataTable').each(function() {
                    dt = $(this).dataTable();
                    dt.fnDraw();
                })
            }

            function choicesSelect(fieldId){
                let choices = document.querySelectorAll(fieldId);
                let initChoice;
                for(let i=0; i<choices.length;i++) {
                    if (choices[i].classList.contains("multiple-remove")) {
                        initChoice = new Choices(choices[i],
                        {
                            delimiter: ',',
                            editItems: true,
                            maxItemCount: -1,
                            removeItemButton: true,
                        });
                    }else{
                        initChoice = new Choices(choices[i]);
                    }
                }
            }

            function backEventModule(oTable,fieldId){
                $("."+fieldId).unbind('click').click(function(event){
                    event.preventDefault();
                    // oTable.fnClearTable();
                    oTable.fnDraw(false);
                    var is_breadcrumb = $(this).attr("is-breadcrumb");
                    if(is_breadcrumb==1){
                        var rightPanel = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
                    }else{
                        var rightPanel = $(this).parent().parent().parent().parent().parent();
                    }
                    var leftPanel = rightPanel.parent().find(".leftPanel[active='1']");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.toggle("'slide', {direction: 'left' }, 2000");
                });
            }

            function backEventView(oTable,fieldId){
                $("."+fieldId).unbind('click').click(function(event){
                    event.preventDefault();
                    oTable.fnDraw(false);
                    // oTable.fnClearTable();
                    var is_breadcrumb = $(this).attr("is-breadcrumb");
                    if(is_breadcrumb == 1){
                        var rightPanel = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
                    }else{
                        var rightPanel = $(this).parent().parent().parent().parent();
                    }
                    var leftPanel = rightPanel.parent().find(".leftPanel");
                    rightPanel.hide();rightPanel.html("");
                    leftPanel.toggle("'slide', {direction: 'left' }, 2000");
                });
            }
        </script>
    </head>
    <body>
        <div class="ui-layout-center">
            <div id="main_page">
                <?php echo $this->Session->flash(); ?>
                <?php echo $content_for_layout; ?>
            </div>
        </div>
        <div id="progress"></div>
        <div id="connectStatus"></div>
    </body>
</html>