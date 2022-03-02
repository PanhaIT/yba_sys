<?php
//include("includes/function.php");
$sqlCom = mysql_query("SELECT id FROM companies WHERE is_active = 1;");
?>
<script type="text/javascript">
    var tabName="";
    var waitForFinalConection = (function () {
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
    
    function convertToSeparator(string){
        return string.toString().trim().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }
    
    function checkConnection(){
        var a = a||{};
        a.checkURL = window.location.href.replace('dashboards/index', 'users/connection');
        a.checkInterval = 10000;
        a.msgNot = "No Connection";
        a.msgCon = "Connected";
        getConnection(a);
    }
    
    function getConnection(a){
        var isCheck = 1;
        var modified = "";
        if (localStorage.getItem("modified") != null && localStorage.getItem("modified") != '[]' && localStorage.getItem("modified") != '') {
            modified = localStorage.getItem("modified");
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            data: "data[modified]="+modified,
            url: a.checkURL,
            cache: !1,
            error: function() {
                isCheck = 0;
                waitForFinalConection(function(){
                    // Recheck Conection
                    getConnection(a);
                }, a.checkInterval, "Finish");
            },
            complete: function(){
                if(isCheck == 0){
                    isCheck = 1;
                    $("#connectWarning").css('background', '#FF0000').text(a.msgNot).show();
                }else{
                    $("#connectWarning").css('background', '#03C').text(a.msgCon).fadeOut(10000);
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
                waitForFinalConection(function(){
                    // Recheck Conection
                    getConnection(a);
                }, a.checkInterval, "Finish");
            }
        });
//        setTimeout( function(){getConnection(a);},a.checkInterval);
    }
    
    function preventKeyEnter(){
        // Prevent Input Key Enter
        $("input[type='text']").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
    }
    
    function clearTmpTabs(){
        $("#tabs").tabs( "remove" ,$("#tabs").tabs("length")-1);
    }
    
    function replaceSlash(string){
        if(string != ""){
           string = string.toString().trim().replace(/\//g, '\\/');
        }else{
           string = "";
        }
        return string;
    }
    
    function replaceDoubleQuote(string){
        if(string != ""){
           string = string.toString().trim().replace(/"/g, '\\"');
        }else{
           string = "";
        }
        return string;
    }
    
    function replaceNum(str){
        if(str != "" && str != undefined && str != null){
            var str = parseFloat(str.toString().replace(/,/g,""));
        }else{
            var str = 0;
        }
        return str;
    }
    
    function converDicemalJS(value){
        return Math.round(parseFloat(value) * 1000000000)/1000000000;
    }
    
    function converDicemalRound(value){
        value = converDicemalJS(value * 1000);
        if(value.toString().match(/\./)){
            value = value.toString().split(".")[0];
        }
        value = converDicemalJS(parseFloat(value) / 1000);
        return value;
    }
    
    function calculateQtyDisplay(totalQty, labelMain, labelSmall, uomSmall){
        var totalRemain = "";
        var totalMain   = parseInt(parseInt(totalQty) / parseInt(uomSmall));
        var checkRemain = parseInt(parseInt(totalQty) % parseInt(uomSmall));
        if(checkRemain != 0){
            totalRemain = (parseInt(totalQty) - parseInt((totalMain * uomSmall)))+""+labelSmall;
        }
        return totalMain+""+labelMain+" "+totalRemain;
    }
    
    function checkFieldRecord(val){
        var result = true;
        if(val == "" || val == undefined || val == null){
            result = false;
        }
        return result;
    }
    
    // Set Cookie
    function setCookie(cookie, val){
        $.cookie(cookie, val, { expires: 7, path: "/" });
    }
    
    // Use Cookie
    function useCookie(obj, cookie){
        $(obj).val($.cookie(cookie));
    }
    // Alert Confirm Valid Code
    function alertConfirmValidCode(){
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_INVALID; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_WARNING; ?>',
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
    
    function createSysAct(mod, act, status, bug){
        var bugSend = bug.toString().replace(/&nbsp;/g, "").replace(/&gt;/g, "$"); 
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/'; ?>users/createSysAct/"+mod+"/"+act+"/"+status,
            data:   "bug="+bugSend
        });
    }
    
    function checkRequireField(fields){
        var result = true;
        if(fields.length > 0){
            $.each(fields, function(key, value) {
                if($("#"+value).val() == "" || $("#"+value).val() ==  null){
                    result = false;
                }
            });
        } else {
            result = false;
        }
        return result;
    }
    
    function checkRequireFieldMulti(fields){
        var result = true;
        if(fields.length > 0){
            $.each(fields, function(key, value) {
                if($("#"+value+"_chzn").find(".chzn-choices").find(".search-choice").find("span").text() ==  ""){
                    result = false;
                }
            });
        } else {
            result = false;
        }
        return result;
    }
    
    function alertSelectRequireField(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_INPUT_ALL_REQUIREMENT; ?></p>');
        $("#dialog").dialog({
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
                }
            }
        });
    }
    
    function getProductCache(){
        $.ajax({
            dataType: "json",
            url: "<?php echo $this->base; ?>/dashboards/getProductCache",
            data: "",
            error: function (jqXHR, textStatus, errorThrown) {
                // Check Connection
                checkConnection();
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
                    waitForFinalConection(function(){
                        // Check Conection
                        checkConnection();
                    }, 2000, "Finish");
                }
            }
        });
    }
    
    function refreshMenu(){
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/dashboards'; ?>/refreshMenu/",
            success: function (result) {
                $("#headerDivMenu").html(result);
            }
        });
    }
    
    $(document).ready(function(){
        // Check Product Cache
        if (localStorage.getItem("products") == null || localStorage.getItem("products") == '[]' || localStorage.getItem("products") == '') {
            getProductCache();
        } else {
            // Check Connection
            checkConnection();
        }
        // Check Company not yet create
        <?php
        if(!mysql_num_rows($sqlCom)){
        ?>
        var url   = "<?php echo $this->base?>/companies/add";
        var found = false;
        if(tabName != "<?php echo MENU_COMPANY_MANAGEMENT; ?>"){
            tabName = "<?php echo MENU_COMPANY_MANAGEMENT; ?>";
            $('#tabs a').not("[href=#]").each(function() {
                if("<?php echo MENU_COMPANY_MANAGEMENT; ?>" == "<?php echo MENU_DASHBOARD; ?>"){
                    found=true;
                    $("#tabs").tabs("select", 0);
                }else if(url == $.data(this, 'href.tabs')){
                    found=true;
                    $("#tabs").tabs("select", url);
                }
            });
            if(found==false){
                $("#tabs").tabs("add", url, "<?php echo MENU_COMPANY_MANAGEMENT; ?>");
            }
        }
        <?php
        }
        ?>
        $("#lang").change(function(){
            clearCookie = true;
            window.open('<?php echo $this->base; ?>/users/lang/' + $(this).val(), '_self');
        });
        
        $(".ajax").unbind('click').click(function(event){
            event.preventDefault();
            var obj=$(this);
            var found=false;
            if(tabName!=$(this).text()){
                tabName=$(this).text();
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
        $("#btnViewWarning").click(function(){
            
        });
        // Action Logout
        $("#actionLogout").click(function(){
            $("#showWarning").hide();
        });
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
    });
</script>
<table style="width: 100%;height: 100%;" cellspacing="0">
    <tr style="vertical-align: top; height: 33px;">
        <td rowspan="2" style="width: 160px;"><img alt="" src="<?php echo $this->webroot; ?>img/logo.png" style="height:65%;position: absolute; top: 8px; left: 15px; max-height: 70px; max-width: 150px;" /></td>
        <td style="text-align: left;vertical-align: top;padding-left: 10px;" id="headerDivMenu">
            <?php echo $this->element('menu'); ?>
        </td>
        <td style="text-align: right;vertical-align: top;">
            <?php echo GENERAL_WELCOME; ?> <?php echo $html->link($user['User']['first_name'].' '.$user['User']['last_name'],array('controller'=>'users','action'=>'profile'),array('class' => 'ajax')); ?>
            [ <?php echo $html->link(GENERAL_LOG_OUT,array('controller'=>'users','action'=>'logout', 'id' => 'actionLogout')); ?> ]
        </td>
    </tr>
    <tr style="background: url(<?php echo $this->webroot; ?>img/layout/line.gif);background-repeat: repeat-x;">
        <td style="vertical-align: top;">
            <div style="width: 49%; float: left; color: red; font-size: 14px;">
                <?php
                $expRemain = dateDiff(date("Y-m-d"), $user['User']['expired']);
                if($expRemain <= 15){
                    if($expRemain == 1){
                        $day = "in ".$expRemain." day";
                    } else if($expRemain == 0){
                        $day = "today";
                    }else{
                        $day = "in ".$expRemain." days";
                    }
                    echo "The current user will be expired ".$day.". <br/><span style='font-weight: normal; color:#3a99b3;'>Please contact us 023/081 881 887.</span>";
                }
                ?>
            </div>
            <div id="showWarning" style="width: 49%; float: right;">
                <div style="color: #fff; font-size: 16px; text-align: center; width: 100%; background: #FF0000; display: none;" id="connectWarning">No Connection</div>
            </div>
            <div style="clear: both;"></div>
            <div style="height: 10px;"></div>
        </td>
        <td style="vertical-align: top;">
            <table style="float: right;">
                <tr>
                    <td>
                        <select id="lang" class="chzn-select" style="width: 150px;">                            
                            <option value="kh" <?php echo $this->Session->read('lang')=='kh'?'selected="selected"':''; ?>>Khmer</option>
                            <option value="en" <?php echo $this->Session->read('lang')=='en'?'selected="selected"':''; ?>>English</option>
                        </select>
                    </td>
                    <td><img alt="" src="<?php echo $this->webroot; ?>img/layout/toolbox-divider.gif" align="absmiddle" /></td>
                    <td><img alt="" src="<?php echo $this->webroot; ?>img/button/safety.png" align="absmiddle" id="btnViewWarning" style="cursor: pointer; display: none;" /></td>
                    <td><img alt="" src="<?php echo $this->webroot; ?>img/layout/toolbox-divider.gif" align="absmiddle" /></td>
                    <td><img alt="" src="<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif" align="absmiddle" class="loader" /></td>
                </tr>
            </table>
        </td>
    </tr>
</table>