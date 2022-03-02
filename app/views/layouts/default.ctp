<?php
/**
 * Copyright UDAYA Technology Co,.LTD (http://www.udaya-tech.com)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
include("includes/function.php");
$start = 2018;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <?php echo $this->element('embed_font'); ?>

        <title>
            <?php __('Neak Srok'); ?>
        </title>

        <!-- icon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $this->webroot; ?>img/logo.ico" />

        <!-- Style Sheet -->
        <!-- General Style Sheet -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/report.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />
        <!-- Jquery UI -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <!-- Layout -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/layout.css" />
        <!-- Menu -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/dropdown/dropdown.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/dropdown/themes/flickr.com/default.ultimate.css" />
        <!-- Validate -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />
        <!-- Data Table -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/css/custom.css" />
        <!-- Choosen -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.css" />
        <!-- Tooltip -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/atooltip.css" />
        <!--  Auto Complete -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/jquery.autocomplete.css" />
        <!-- JS Crop Photo -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/css/jquery.Jcrop.css" type="text/css" />
        <!-- Mini Select 
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/minimalect/jquery.minimalect.min.css" type="text/css" media="screen" />
        -->
        <!-- Check box Style -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/checkboxStyle/bootstrap2-toggle.css" />
        <!-- Tour -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/tours/introjs.css" />
        <!-- Time Picker -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.css" />
        <!-- On/Off -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/OnOff/css/on-off-switch.css" />
        
        <!-- Jquery Script -->
        <!-- Jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/shortcut.js"></script>
        <!-- Jquery UI -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/jquery-ui-1.8.14.custom.min-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.closable.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.8.14.custom/js/ui.tabs.paging.js"></script>
        <!-- Layout -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/jquery.layout.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.layout.all-1.2.0/jquery.layout.state.js"></script>
        <!-- Menu -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.dropdown.js"></script>
        <!-- Validator -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>
        <!-- Data Table -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/js/jquery.dataTables.min.<?php echo $this->Session->read('lang'); ?>.js"></script>
        <!-- Choosen -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.jquery.min.js"></script>
        <!-- autoNumeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>
        <!-- Price Format -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.price_format-1.3.js"></script>
        <!-- input mask for number - support unicode -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/uninums.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.caret.1.02.min.js"></script>
        <!-- Tooltip -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.atooltip.js"></script>
        <!-- Date -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/date-en-US.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>
        <!-- Ajax Form -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
        <!--  Auto Complete -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.autocomplete.min.js"></script>
        <!-- JS Crop Photo -->
        <script src="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/js/jquery.Jcrop.js" type="text/javascript"></script>
        <!-- List Box -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/listbox.js"></script>
        <!-- Format Currency -->
        <script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
        <!-- To Word -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/toword/toword_<?php echo $this->Session->read('lang'); ?>.js"></script>
        <!-- High Chart -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/HighChart/highcharts.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/HighChart/exporting.js"></script>
        <!-- Check box Style -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/checkboxStyle/bootstrap2-toggle.min.js"></script>
        <!-- Menu Setting -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/menuSetting.js"></script>
        <!-- Tour -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/tours/intro.js"></script>
        <!-- Time Picker -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/timePicker/jquery-ui-timepicker.js"></script>
        <!-- On/Off -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/OnOff/js/on-off-switch.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/OnOff/js/on-off-switch-onload.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>


        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/demo_style.css" /> -->
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/smart_wizard.css" /> -->
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/smart_wizard_vertical.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.smartWizard.js"></script> -->

        <style type="text/css">
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
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {
                $("input.integer").live("keydown", function (e) {
                    var key = e.charCode || e.keyCode || 0;
                    // allow backspace, tab, shift, home & end, delete, arrows, numbers and keypad numbers ONLY
                    return (key == 8 || key == 9 || key == 16 || (key >= 35 && key <= 36) || key == 46 || (key >= 37 && key <= 40) || (key >= 48 && key <= 57) || (key >= 96 && key <= 105));
                });
                $("input.integer").live("keyup", function (e) {
                    var key = e.charCode || e.keyCode || 0;
                    if($(this).val()!='' && key != 16 && !(key >= 35 && key <= 36)){
                        currentCursorPosition=$(this).caret().end;
                        $(this).val(parseUniInt($(this).val()));
                        $(this).caret({start:currentCursorPosition,end:currentCursorPosition});
                    }
                });
                $("input.number").live("keydown", function (e) {
                    var key = e.charCode || e.keyCode || 0;
                    // allow backspace, tab, shift, home & end, delete, arrows, numbers and keypad numbers ONLY
                    return (key == 8 || key == 9 || key == 16 || (key >= 35 && key <= 36) || key == 46 || (key >= 37 && key <= 40) || (key >= 48 && key <= 57) || (key >= 96 && key <= 105) || key == 190);
                });
                $("input.number").live("keyup", function (e) {
                    var key = e.charCode || e.keyCode || 0;
                    if(key == 190){
                        currentCursorPosition=$(this).caret().end;
                        $(this).caret({start:currentCursorPosition-1,end:currentCursorPosition});
                        $(this).val($(this).caret().replace("."));
                        $(this).caret({start:currentCursorPosition,end:currentCursorPosition});
                    }
                    if(key == 110){
                        currentCursorPosition=$(this).caret().end;
                        $(this).caret({start:currentCursorPosition,end:currentCursorPosition});
                        $(this).val($(this).caret().replace("."));
                        $(this).caret({start:currentCursorPosition+1,end:currentCursorPosition+1});
                    }
                    if($(this).val()!='' && key != 16 && !(key >= 35 && key <= 36) && key != 48 && key != 96 && key != 190 && key != 110){
                        currentCursorPosition=$(this).caret().end;
                        $(this).val(parseUniFloat($(this).val()));
                        $(this).caret({start:currentCursorPosition,end:currentCursorPosition});
                    }
                });
            });
        </script>
        <script type="text/javascript">
            // DEFAULT LAYOUT SETTINGS
            var myDefaultSettings = {
                initClosed: false,
                north__size: 80,
                resizable: false
            }

            var clearCookie = false;
            var titles = [];
            var hrefs = [];
            var selectedTabIndex=0;

            $(document).ready(function () {
                // detech if firebug exists
                if (window.console && (window.console.firebug || window.console.exception)) {
                    //window.open('<?php echo $this->base; ?>/users/logout/', '_self');
                }

                // bind save() to window.onunload
                $(window).unload(function(){
                    // save layout state
                    layoutState.save('myLayout');
                    // save tabs to cookie
                    $('#tabs a').each(function() {
                        var title = $(this).text();
                        var href = $.data(this, 'href.tabs');
                        if(href!=undefined){
                            titles.push(title);
                            hrefs.push(href);
                        }
                    });
                    if(clearCookie==true){
                        $.cookie('cookieTitle', null, { expires: 7, path: "/" });
                        $.cookie('cookieHref', null, { expires: 7, path: "/" });
                        $.cookie('cookieTabIndex', null, { expires: 7, path: "/" });
                    }else{
                        $.cookie('cookieTitle', titles, { expires: 7, path: "/" });
                        $.cookie('cookieHref', hrefs, { expires: 7, path: "/" });
                        $.cookie('cookieTabIndex', selectedTabIndex, { expires: 7, path: "/" });
                    }
                });

                // load layout state
                myLayout = $('body').layout(
                    myDefaultSettings
                    //$.extend(myDefaultSettings, layoutState.load('myLayout'))
                );

                // chosen init
                $(".chzn-select").chosen();

                // menu
                $(".menu_item").mouseover(function(){
                    $(this).addClass('selected');
                    $(this).siblings().removeClass('selected');
                });
                $(".menu_item").mouseout(function(){
                    $(this).removeClass('selected');
                });

                // detech screen
                var contentId="#main_page";
                setInterval(function() {
                    // overflow
                    myLayout.allowOverflow('north');
                    // content height
                    var tabHeight = $(".ui-layout-center").height() - 70;
                    $('.ui-tabs-panel').height(tabHeight);
                }, 1000);

                // detech key(s)
                shortcut.add("Ctrl+Left",function() {
                    $("#tabs").tabs("select", selectedTabIndex-1);
                });
                shortcut.add("Ctrl+Right",function() {
                    $("#tabs").tabs("select", selectedTabIndex+1);
                });
                shortcut.add("Ctrl+Delete",function() {
                    if(selectedTabIndex!=0){
                        $("#tabs").tabs("remove", selectedTabIndex);
                    }
                });

                // tab init
                $("#tabs").tabs({
                    cache: true,
                    spinner: '<?php echo ACTION_LOADING; ?>',
                    closable: true,
                    closableClick: function(event, ui) {},
                    remove: function(event, ui) {}
                });

                // tab paging
                $("#tabs").tabs('paging', {
                    cycle: false,
                    follow: false,
                    followOnSelect: true,
                    prevButton: '<span class="ui-icon ui-icon-carat-1-w"></span>',
                    nextButton: '<span class="ui-icon ui-icon-carat-1-e"></span>'
                });

                // tab sort
                $("#tabList").sortable({
                    delay: 1000,
                    axis: "x",
                    items: "li:not(.main_page,.ui-tabs-paging-prev,.ui-tabs-paging-next)",
                    update: function() {}
                });

                // when tab loaded
                $("#tabs").bind("tabsload", function(event, ui) {
                    tabName="";
                    tabMenuName="";
                    // if no auth
                    if($("#"+ui.panel.id).text()=="No Authentication"){
                        $("#tabs").tabs("remove", ui.index);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>No Authentication</p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_INFORMATION; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            open: function(event, ui){
                                $(".ui-dialog-buttonpane").show();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }
                });

                // when tab closed
                $("#tabs").bind("tabsremove", function(event, ui) {
                    tabName="";
                    tabMenuName="";
                });

                // when tab added
                $("#tabs").bind("tabsadd", function(event, ui) {
                    $("#tabs").tabs("select",ui.index);
                });

                // when tab selected
                $("#tabs").bind("tabsselect", function(event, ui) {
                    // save selected tab index
                    selectedTabIndex=ui.index;
                    // detech screen
                    contentId="#"+ui.panel.id;
                });

                // when tab removed
                $("#tabs").bind("tabsremove", function(event, ui) {
                    var tmp = $('#tabs ul li.ui-tabs-selected a').attr("href");
                    $("#tabs").tabs("select", 0);
                    $("#tabs").tabs("select", tmp);
                });

                // get tab(s) from saved cookie
//                if($.cookie('cookieTitle')!=null && $.cookie('cookieTitle').length!=0){
//                    var cookieTitle = $.cookie('cookieTitle').split(",");
//                    var cookieHref = $.cookie('cookieHref').split(",");
//                    for(var i=0; i<cookieTitle.length; i++) {
//                        $("#tabs").tabs("add", cookieHref[i], cookieTitle[i]);
//                    }
//                    $("#tabs").tabs("select", Number($.cookie('cookieTabIndex')));
//                }

                $(".blank").click(function(event){
                    event.preventDefault();
                    window.open($(this).attr("href"));
                });
            });
        </script>
    </head>
    <body>
        <div class="ui-layout-north">
            <?php echo $this->element('header'); ?>
        </div>
        <div class="ui-layout-center">
            <div id="tabs">
                <ul id="tabList">
                    <li class="main_page"><a href="#main_page"><?php __($title_for_layout); ?></a></li>
                </ul>
                <div id="main_page">
                    <?php echo $this->Session->flash(); ?>
                    <?php echo $content_for_layout; ?>
                    <?php echo $this->element('sql_dump'); ?>
                </div>
            </div>
        </div>
        <div class="ui-layout-south">
            <div style="float: left;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <img alt="" src="<?php echo $this->webroot; ?>img/logo_s.png" style="height:65%;position: absolute; top: 8px; left: 15px; max-height: 70px; max-width: 150px;" />
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right;">&copy; Copyright <?php echo $start; ?><?php echo date("Y") != $start ? "-" . date("Y") : ""; ?> Neak Srok. All rights reserved. V.1.0.0</div>
        </div>
        <div id="dialog" title=""></div>
        <div id="dialog1" title=""></div>
        <div id="dialog2" title=""></div>
        <div id="dialog3" title=""></div>
        <div id="dialog4" title=""></div>
        <div id="dialogModal" title=""></div>
    </body>
</html>