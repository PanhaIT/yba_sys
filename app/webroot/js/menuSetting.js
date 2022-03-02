;(function($, window, document, undefined){
    $.fn.makeMenu = function(options){
        var opts = $.extend({
			url : '',
                        load : 'Loading',
                        finish : 'Save'
		}, options);
        var interval = {};
                
        function setDefaultOpt(obj, objTime, event){
            var defCheck = obj.find('.defaultCheckSetting').val();
            var defTime  = obj.find('.defaultTimeSetting').val();
            // Set Checkbox & Time as default
            if(defCheck == 2){ // ON
                obj.find('.settingCheck').attr('checked', true);
                stopAutoRefresh(event);
                startAutoRefresh(event, defTime, obj);
            } else { // OFF
                obj.find('.settingCheck').attr('checked', false);
                stopAutoRefresh(event);
            }
            obj.find('.'+objTime).val(defTime);
            // Set Checkbox Style
            obj.find('.settingCheck').bootstrapToggle('destroy');
            obj.find('.settingCheck').bootstrapToggle();
        }
        
        function setNewOpt(obj, auto, time){
            obj.find('.defaultCheckSetting').val(auto);
            obj.find('.defaultTimeSetting').val(time);
        }
        
        function startAutoRefresh(event, time, obj){
            var second = time * 1000;
            interval[event] = setInterval(function(){
                                obj.closest("h1.title").find(".refreshDashboard").click();
                             }, second);
        }
        
        function stopAutoRefresh(event){
            $.each( interval, function( key, value ) {
                if(key == event){
                    clearInterval(value);
                }
            });
        }
        
        return this.each(function () {
            var actionClick = $(this);
            var intName     = actionClick.attr('id');
            var menuDrop    = actionClick.attr('id')+'Menu';
            var windowWidth = $(window).width();
            var imgSrc      = actionClick.attr('src').replace('setting-active.png', '').replace('setting-inactive.png', '');
            var objTime     = actionClick.attr('id')+'TimeRefresh';
            var objBtn      = actionClick.attr('id')+'Save';
            var objBtnLbl   = actionClick.attr('id')+'TxtSave';
            // Check Function Default
            setDefaultOpt($('#'+menuDrop), objTime, intName);
            // Set Menu Show Default
            $('#'+menuDrop).find('.selectSetting').val(0);
            // Set Number to Time
            $('.'+objTime).autoNumeric({mDec: 0, aSep: ','});
            // Unbind
            actionClick.unbind('click').unbind('mouseover').unbind('mouseout');
            $('.'+objTime).unbind('click').unbind('blur').unbind('focus');
            $('.'+objBtn).unbind('click');
            
            actionClick.click(function () {
                var position    = $(this).offset().left;
                var positionAll = position + 240;
                var moveLeft    = 0;
                // Set Position for Menu
                if(positionAll > windowWidth){
                    moveLeft = position - 230;
                    $('#'+menuDrop).find('.divMenu').css('left', moveLeft);
                } else {
                    moveLeft = position - 10;
                    $('#'+menuDrop).find('.divMenu').css('left', moveLeft);
                }
                // Check Select Setting
                if($('#'+menuDrop).find('.selectSetting').val() == 0){
                    var obj = $('#'+menuDrop);
                    setDefaultOpt(obj, objTime, intName);
                    // Set Click Action
                    $('#'+menuDrop).find('.selectSetting').val(1);
                    actionClick.attr('src', imgSrc+'setting-active.png');
                } else {
                    // Set Click Action
                    $('#'+menuDrop).find('.selectSetting').val(0);
                }
                $('#'+menuDrop).fadeToggle('fast');
            });
            
            actionClick.bind('mouseover', function(){
                actionClick.attr('src', imgSrc+'setting-active.png');
            });

            actionClick.bind('mouseout', function(){
                if($('#'+menuDrop).find('.selectSetting').val() == 0){
                    actionClick.attr('src', imgSrc+'setting-inactive.png');
                }
            });
            
            // Event Time Refresh
            $('.'+objTime).bind('focus', function () {
                var value = $(this).val();
                if(value == '0' || value == '0.00'){
                    $(this).val('');
                }
            });
            
            $('.'+objTime).bind('blur', function () {
                var value = $(this).val();
                if(value == '' || value == '0' || value == '0.00'){
                    $(this).val(30);
                } else if(parseFloat(value) < 30){
                    $(this).val(30);
                }
            });
            
            // Event Key Button Save
            $('.'+objBtn).bind('click', function () {
                var auto = 1;
                var time = $('#'+menuDrop).find('.'+objTime).val();
                var obj  = $('#'+menuDrop);
                if($('#'+menuDrop).find('.settingCheck').is(':checked')){
                    auto = 2;
                }
                $.ajax({
                    dataType: 'json',
                    type:   'POST',
                    url:    opts.url+"/"+auto+"/"+time,
                    beforeSend: function(){
                        $(this).attr('disabled', true);
                        $("."+objBtnLbl).text(opts.load);
                    },
                    success: function(msg){
                        $(this).attr('disabled', false);
                        $("."+objBtnLbl).text(opts.finish);
                        if(msg.result == 1){
                            setNewOpt(obj, auto, time);
                            $('#'+menuDrop).find('.saveCompleted').show();
                            $('#'+menuDrop).find('.saveCompleted').fadeOut(5000);
                            if(auto == 1){ // OFF
                                stopAutoRefresh(intName);
                            } else { // ON
                                startAutoRefresh(intName, time, obj);
                            }
                        } else {
                            setDefaultOpt(actionClick, objTime, intName);
                            $('#'+menuDrop).find('.saveFailed').show();
                            $('#'+menuDrop).find('.saveFailed').fadeOut(5000);
                        }
                    }
                });
            });
            
            // Action Hide
            $('#'+menuDrop).closest("h1.title").find(".minimizeDashboard").click(function(){
                var objMin = $(this);
                var auto = $('#'+menuDrop).find('.defaultCheckSetting').val();
                var time = $('#'+menuDrop).find('.defaultTimeSetting').val();
                var name = objMin.closest("div").find('.dashboardName').text();
                var confirm = objMin.closest("div").attr("confirm");
                var dialog  = objMin.closest("div").attr("dialog");
                var cancel  = objMin.closest("div").attr("cancel");
                var hide    = objMin.closest("div").attr("hide");
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span> '+confirm+' <b>' + name + '</b>?</p>');
                $("#dialog").dialog({
                    title: dialog,
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        hide: function() {
                            objMin.closest("div").hide();
                            objMin.closest("div").attr('display', 2);
                            $.ajax({
                                dataType: 'json',
                                type:   'GET',
                                url:    opts.url+"/"+auto+"/"+time+"/2",
                                success: function(msg){
                                    if(msg.result == 0){
                                        objMin.closest("div").show();
                                        objMin.closest("div").attr('display', 1);
                                    }
                                }
                            });
                            $(this).dialog("close");
                        },
                        cancel: function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
            
            // Document Click
            $(document).bind('click', function (e) {
                var $target;
                $target = $(e.target);
                if (!$target.closest('#'+menuDrop).length && !$target.closest(actionClick).length) {
                    $('#'+menuDrop).hide();
                    $('#'+menuDrop).find('.selectSetting').val(0);
                    actionClick.attr('src', imgSrc+'setting-inactive.png');
                }
            });
        });
    };
})(jQuery, window, document);