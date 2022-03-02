<script type="text/javascript">
    var tabMenuName = "";
    var purchaseDecimal;
    var salesDecimal;
    var reportDecimal;
    $(document).ready(function () {
        // Menu Click
        $(".tabSettingMenu").unbind("click").click(function () {
            var id = $(this).attr("data");
            $(".tabSettingMenu").removeClass("active");
            $(this).addClass("active");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/settings'; ?>/configSetting/" + id,
                beforeSend: function () {
                    $(".tabcontent").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="top: 50%; left:50%; position: absolute;" />');
                },
                success: function (result) {
                    $(".tabcontent").html(result);
                    callFuncSetting();
                }
            });
        });
        
        // Load First Menu
        var id = $("#tabSettingMenuFirst").attr('data');
        if(id != '' && id != 'undefinded'){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/settings'; ?>/configSetting/" + id,
                beforeSend: function () {
                    $(".tabcontent").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" style="top: 50%; left:50%; position: absolute;" />');
                },
                success: function (result) {
                    $(".tabcontent").html(result);
                    callFuncSetting();
                }
            });
        }
    });

    function callFuncSetting() {
        // Prevent Key Enter
        preventKeyEnter();
        // Integer
        $(".SettingMenuDecimal").autoNumeric({mDec: 0, aSep: ','});
        
        $('.dateSettingMenu, .dateLockTransaction').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $(".dateLockTransaction").focus(function(){
            $.cookie("dateLockCookie", $(this).val(), {expires : 7,path    : '/'});
        });
        
        $(".dateLockTransaction").change(function(){
            if($(this).val() != ''){
                var obj  = $(this);
                var id   = $(this).attr("is-id");
                var date = $(this).val().toString().split("/")[2]+"-"+$(this).val().toString().split("/")[1]+"-"+$(this).val().toString().split("/")[0];
                var question = "<?php echo MESSAGE_DO_YOU_WANT_TO_SAVE; ?>";
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_CONFIRMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position:'center',
                    closeOnEscape: true,
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                    },
                    buttons: {
                        '<?php echo ACTION_NO; ?>': function() {
                            $(".dateLockTransaction").val($.cookie("dateLockCookie"));
                            $(this).dialog("close");
                        },
                        '<?php echo ACTION_OK; ?>': function() {
                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: "<?php echo $this->base . '/settings'; ?>/saveLockTransaction/"+id+"/"+date,
                                beforeSend: function () {
                                    obj.attr("disabled", true);
                                    obj.closest("td").find(".checkSettingBoxloader").show();
                                },
                                success: function (result) {
                                    obj.attr("disabled", false);
                                    obj.closest("td").find(".checkSettingBoxloader").hide();
                                    if(result.msg == '0'){
                                        $(".dateLockTransaction").val($.cookie("dateLockCookie"));
                                        dialogAlertSetting('<?php echo DIALOG_INFORMATION; ?>', '<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>', 'auto',  'auto');
                                    } else {
                                        $.cookie("dateLockCookie", $(this).val(), {expires : 7,path    : '/'});
                                        $("#lockTransactionUpdate").text(obj.val());
                                    }
                                }
                            });
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        
        $(".ajaxSettingMenu").unbind("click").click(function(event){
            event.preventDefault();
            var obj = $(this);
            var found = false;
            if(tabMenuName!=$(this).text()){
                tabMenuName=$(this).text();
                $('#tabs a').not("[href=#]").each(function() {
                    if(obj.text()=="<?php echo MENU_DASHBOARD; ?>"){
                        found=true;
                        $("#tabs").tabs("select", 0);
                    }else if(obj.attr("href")==$.data(this, 'href.tabs')){
                        found=true;
                        $("#tabs").tabs("select", $(this).attr("href"));
                    }
                });
                if(found==false){
                    $("#tabs").tabs("add", $(this).attr("href"), $(this).text());
                }
            }
        });
        
        $(".checkSettingBox").unbind("click").click(function(){
            var isMod = $(this).attr("is-mod");
            var id    = $(this).attr("is-id");
            var value = 0;
            var obj   = $(this);
            if ($(this).is(':checked')) {
                value = 1;
            }
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "<?php echo $this->base . '/settings'; ?>/saveCheckBox/"+id+"/"+value,
                beforeSend: function () {
                    obj.hide();
                    obj.closest("td").find(".checkSettingBoxloader").show();
                },
                success: function (result) {
                    obj.show();
                    obj.closest("td").find(".checkSettingBoxloader").hide();
                    if(result.msg == 1){
                        if(id == '12' || id == '18' || id == '20'){
                            refreshMenu();
                        }
                        if(isMod == 1 && result.val == 1){
                            obj.attr('checked', true);
                            obj.closest("td").find(".ajaxSettingMenu").show();
                        } else {
                            obj.closest("td").find(".ajaxSettingMenu").hide();
                        }
                    } else {
                        obj.removeAttr('checked');
                        obj.closest("td").find(".ajaxSettingMenu").hide();
                    }
                }
            });
        });
        
        $(".SettingMenuBaseCurrency").unbind("change").change(function(){
            var id    = $(this).attr("is-id");
            var value = $(this).find("option:selected").val();
            var obj   = $(this);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "<?php echo $this->base . '/settings'; ?>/saveBaseCurrencySetting/"+id+"/"+value,
                beforeSend: function () {
                    obj.attr("disabled", true);
                    obj.closest("td").find(".checkSettingBoxloader").show();
                },
                success: function (result) {
                    obj.attr("disabled", false);
                    obj.closest("td").find(".checkSettingBoxloader").hide();
                }
            });
        });
        
        $(".SettingMenuDecimal").unbind("focus").focus(function(){
            var type = $(this).attr("is-type");
            if(type == 'Purchases' || type == 'AVG Cost'){
                purchaseDecimal = replaceNum($(this).val());
            } else if(type == 'Sales'){
                salesDecimal = replaceNum($(this).val());
            } else {
                reportDecimal = replaceNum($(this).val());
            }
        });
        
        $(".SettingMenuDecimal").unbind("blur").blur(function(){
            if($(this).val() == '' || $(this).val() == 0){
                $(this).val(2);
            }
            var id     = $(this).attr("is-id");
            var value  = replaceNum($(this).val());
            var type   = $(this).attr("is-type");
            var obj    = $(this);
            var maxVal = 2;
            var preVal = reportDecimal;
            if(type == 'Purchases' || type == 'AVG Cost'){
                maxVal = 9;
                preVal = purchaseDecimal;
            } else if(type == 'Sales'){
                maxVal = 3;
                preVal = salesDecimal;
            }
            if(value > maxVal && type != 'AVG Cost'){
                value = maxVal;
                $(this).val(maxVal);
                
            }
            if(value != preVal){
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?php echo $this->base . '/settings'; ?>/saveDecimalSetting/"+id+"/"+value,
                    beforeSend: function () {
                        obj.attr("disabled", true);
                        obj.closest("td").find(".checkSettingBoxloader").show();
                    },
                    success: function (result) {
                        obj.attr("disabled", false);
                        obj.closest("td").find(".checkSettingBoxloader").hide();
                    }
                });
            }
        });
    }
    
    function dialogAlertSetting(title, result, width, height){
        $("#dialog").html(result);
        $("#dialog").dialog({
            title: title,
            resizable: false,
            modal: true,
            width: width,
            height: height,
            position: 'center',
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
    
    
</script>
<div class="tabLeft">
    <?php
    $i = 1;
    $sqlSettingMenu = mysql_query("SELECT * FROM s_module_type_settings WHERE is_active = 1 ORDER BY ordering ASC");
    while ($rowSettingMenu = mysql_fetch_array($sqlSettingMenu)) {
        $checkPermission = mysql_query("SELECT id FROM permissions WHERE module_id = ".$rowSettingMenu['module_id']." AND group_id IN (SELECT group_id FROM user_groups WHERE user_id = ".$user['User']['id'].") LIMIT 1");
        if(@mysql_num_rows($checkPermission)){
            $active = '';
            $firstMenu = '';
            if ($i == 1) {
                $active = 'active';
                $firstMenu = ' id="tabSettingMenuFirst"';
            }
    ?>
    <button <?php echo $firstMenu; ?> class="tabSettingMenu <?php echo $active; ?>" data="<?php echo $rowSettingMenu['id']; ?>"><?php echo $rowSettingMenu['name']; ?></button>
    <?php
            $i++;
        }
    }
    ?>
</div>
<div class="tabcontent"></div>
<br />
<div style="clear: both;"></div>