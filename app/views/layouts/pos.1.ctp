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
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" />
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/pos_style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/jquery.dataTables.min.css" media="print" /> -->

        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/jquery.dataTables.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/responsive.dataTables.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/dataTables.bootstrap5.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/responsive.bootstrap5.min.css" /> -->

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.css" />

        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.min.css.map" />
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap.css.map" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-grid.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-grid.css.map" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-grid.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/bootstrap/bootstrap-grid.min.css.map" /> -->

        
        <!--  Auto Complete -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/jquery.autocomplete.css" />
        <!-- Validator -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/validationEngine.jquery.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/validateEngine/css/template.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/jquery.countdown.css" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/shortcut.js"></script>

        <!-- jquery ui -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/development-bundle/themes/base/jquery.ui.all.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-ui-1.10.0.custom/js/jquery-ui-1.10.0.custom.min.js"></script>

        <!-- Validator -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine-<?php echo $this->Session->read('lang'); ?>.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/validateEngine/js/jquery.validationEngine.js"></script>

        <!-- Data Table -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/css/custom.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/DataTables-1.8.1/media/js/jquery.dataTables.min.<?php echo $this->Session->read('lang'); ?>.js"></script>

        <!-- Auto Numeric -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>

        <!-- date -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/date-en-US.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>
        
        <!--  Auto Complete -->
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.autocomplete.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.plugin.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.countdown.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.price_format-1.3.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.labelify.js"></script>
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/print_setup.js"></script>
        
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-nice-select-1.1.0/js/jquery.nice-select.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/jquery-nice-select-1.1.0/css/nice-select.css" />
        
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/dropdownjs/js/tether.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/dropdownjs/css/tether-theme-arrows.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/wz_tooltip_v4.js"></script>
        
        <!-- Choosen -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen_1.8.2/chosen.jquery.min.js"></script>    
        
        <!-- Easy Auto Complete -->
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/EasyAutocomplete-1.3.5/easy-autocomplete.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/EasyAutocomplete-1.3.5/easy-autocomplete.themes.min.css" />
        <script type="text/javascript" src="<?php echo $this->webroot; ?>js/EasyAutocomplete-1.3.5/jquery.easy-autocomplete.min.js"></script>
        
        <!-- JS Crop Photo -->
        <link rel="stylesheet" href="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/css/jquery.Jcrop.css" type="text/css" />
        <script src="<?php echo $this->webroot; ?>js/tapmodo-Jcrop-25f2e18/js/jquery.Jcrop.js" type="text/javascript"></script>
        

        <!-- <script type="text/javascript" src="<?php echo $this->webroot; ?>js/bootstrap/jquery-3.5.1.js"></script> -->

        <style type="text/css">
            body{
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
            }
        </style>
        <script type="text/javascript">
            var ajaxConnection;
            var isCheck = 1;
            var isBort  = 0;
            var waitForFinalConectionPOS = (function () {
                var timers = {};
                return function (callback, ms, uniqueId) {
                    if (!uniqueId) {
                      uniqueId = "Don't call this twice without a uniqueId";
                    }
                    if (timers[uniqueId]) {
                      clearTimeout (timers[uniqueId]);
                    }
                    timers[uniqueId] = setTimeout(callback, ms);
                };
            })();
            function replaceNum(str){
                if(str != "" && str != undefined && str != null){
                    var str = parseFloat(str.toString().replace(/,/g,""));
                }else{
                    var str = 0;
                }
                return str;
            }
            
            function convertToSeparator(string, decimal){
                var number = replaceNum(string).toFixed(decimal);
                return number.toString().trim().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
            
            function checkCurrencyDecimal(currencyId){
                var decimal = 2;
                if(currencyId == 2){
                    decimal = 0;
                }
                return decimal;
            }
            
            function converDicemalJS(value){
                return Math.round(parseFloat(value) * 1000000000)/1000000000;
            }
            
            function preventKeyEnter(){
                // Prevent Input Key Enter
                $("input[type='text']").keypress(function(e){
                    if((e.which && e.which == 13) || e.keyCode == 13){
                        return false;
                    }
                });
            }
            
            // Function Option Hide/Show
            $.fn.showHideDropdownOptions = function(value, canShowOption) { 
                $(this).find('option[value="' + value + '"]').map(function () {
                    return $(this).parent('span').length === 0 ? this : null;
                }).wrap('<span>').hide();

                if (canShowOption) {
                    $(this).find('option[value="' + value + '"]').unwrap().show();
                } else {
                    $(this).find('option[value="' + value + '"]').hide();
                }
            };

            // Function Option
            $.fn.filterOptions = function(objCompare, compare, selected) { 
                var object = $(this);
                // Hide by Filter
                object.find("option").removeAttr('selected');
                object.find("option").each(function(){
                    if($(this).val() != '' && $(this).val() != 'all'){
                        var value = $(this).val();
                        var compareId = $(this).attr(objCompare).split(",");
                        if(compareId.indexOf(compare)==-1){
                            object.showHideDropdownOptions(value, false);
                        } else {
                            object.showHideDropdownOptions(value, true);
                        }
                    }
                });
                // OPTION SELECTED
                object.find('option[value="'+selected+'"]').attr('selected', true);
            };
            
            function getProductCache(){
                $.ajax({
                    dataType: "json",
                    url: "<?php echo $this->base; ?>/dashboards/getProductCache",
                    data: "",
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Check Connection
                        checkConnectionPOS();
                    },
                    success: function(result){
                        if(jQuery.isEmptyObject(result)){
                            localStorage.setItem("products", "[]");
                        } else {
                            var modified = result.modified;
                            localStorage.setItem("modified", modified);
                            if(jQuery.isEmptyObject(result.Product)){
                                localStorage.setItem("products", "[]");
                            } else {
                                var objData  = JSON.stringify(result.Product);
                                localStorage.setItem("products", objData.toString());
                            }
                        }
                        waitForFinalConectionPOS(function(){
                            // Recheck Conection
                            checkConnectionPOS();
                        }, 2000, "Finish");
                    }
                });
            }
            
            function checkConnectionPOS(){
                var a = a||{};
                a.checkURL = window.location.href.replace('point_of_sales/add', 'users/connection');
                a.checkInterval = 10000;
                a.msgNot = "No Connection";
                a.msgCon = "Connected";
                getConnectionPOS(a);
            }

            function getConnectionPOS(a){
                var modified = "";
                if (localStorage.getItem("modified") != null && localStorage.getItem("modified") != '[]' && localStorage.getItem("modified") != '') {
                    modified = localStorage.getItem("modified");
                }
                ajaxConnection = $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: "data[modified]="+modified,
                    url: a.checkURL,
                    cache: !1,
                    error: function() {
                        if(isBort == 0){
                            isCheck = 0;
                            waitForFinalConectionPOS(function(){
                                // Recheck Conection
                                getConnectionPOS(a);
                            }, a.checkInterval, "Finish");
                        }
                    },
                    complete: function(){
                        if(isCheck == 0){
                            isCheck = 1;
                            $("#connectStatus").css('background', '#FF0000').text(a.msgNot).show();
                        }else{
                            $("#connectStatus").css('background', '#03C').text(a.msgCon).fadeOut(10000);
                        }
                    },
                    success: function(result){
                        if(jQuery.isEmptyObject(result)){
                            // Empty Result
                        } else {
                            var modified = result.modified;
                            localStorage.setItem("modified", modified);
                            if(jQuery.isEmptyObject(result.Product)){
                                // Empty Product
                            } else {
                                var objData  = JSON.stringify(result.Product);
                                localStorage.setItem("products", objData.toString());
                            }
                        }
                        waitForFinalConectionPOS(function(){
                            // Recheck Conection
                            getConnectionPOS(a);
                        }, a.checkInterval, "Finish");
                    }
                });
            }
            
            function generateBoxProductAutoSearch(icon, upc, sku, uom, name, price, symbol){
                var sellPrice  = replaceNum(price).toFixed(2);
                var layout = "<table class='easyAutoCompleteTable'><tr><td rowspan='3' class='easyAutoCompleteTableImg'><img src='<?php echo $this->webroot; ?>" + icon + "' style='max-width: 100%; max-height: 100%; padding: 0;' /></td><td class='easyAutoCompleteTableTdPadding'><?php echo TABLE_BARCODE; ?>: </td><td class='easyAutoCompleteTableTdContent' style='width: 150px;'>" + upc + "</td><td class='easyAutoCompleteTableTdPadding'><?php echo TABLE_SKU; ?>: </td><td class='easyAutoCompleteTableTdContent' style='width: 150px;'>" + sku + "</td><td class='easyAutoCompleteTableTdPadding'><?php echo TABLE_UOM; ?>: </td><td class='easyAutoCompleteTableTdContent' style='width: 150px;'>" + uom + "</td></tr><tr><td class='easyAutoCompleteTableTdPadding'><?php echo TABLE_NAME; ?>: </td><td colspan='3' class='easyAutoCompleteTableTdPaddingName'>" + name + "</td><td class='easyAutoCompleteTableTdPadding'><?php echo TABLE_PRICE; ?>: </td><td class='easyAutoCompleteTableTdContent' style='width: 150px;'>" + sellPrice +" "+ symbol + "</td></tr></table>";
                return layout;
            }
            
            function createSysAct(mod, act, status, bug){
                var bugSend = bug.toString().replace(/&nbsp;/g, "").replace(/&gt;/g, "$"); 
                $.ajax({
                    type:   "POST",
                    url:    "<?php echo $this->base . '/'; ?>users/createSysAct/"+mod+"/"+act+"/"+status,
                    data:   "bug="+bugSend
                });
            }
            
            function alertSelectRequireField(){
                $("#dialog4").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_INPUT_ALL_REQUIREMENT; ?></p>');
                $("#dialog4").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    closeOnEscape: false,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                        $(".ui-dialog-titlebar-close").hide();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                            $(".ui-dialog-titlebar-close").show();
                            $("#PointOfSaleCustomerNameSearch").focus();
                        }
                    }
                });
            }
            
            $(document).ready(function(){
                // Check Product Cache
                if (localStorage.getItem("products") == null || localStorage.getItem("products") == '[]' || localStorage.getItem("products") == '') {
                    getProductCache();
                } else {
                    // Check Connection
                    checkConnectionPOS();
                }
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
            <?php echo TABLE_POS_PROCESSING; ?>
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