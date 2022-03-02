<script type="text/javascript">
    $(document).ready(function () {
        eventClickModule();
    });

    function eventClickModule(){
        $(".sidebar-link").unbind('click').click(function(event){
            event.preventDefault();
            var obj     = $(this);
            var id      = obj.attr("id");
            var tabName = obj.find("span").text();
            var link    = obj.attr("module");
            var icon    = obj.find("svg").attr("icon");
            var tabNameExists = false;
            var tabNum = 1;
            $('#tab-list li a').each(function() {
                $(this).attr("class","nav-link");
                if(tabName=="<?php echo MENU_DASHBOARD;?>"){
                    tabNameExists = true;
                }
                if ($(this).attr("name") == tabName) {
                    $(this).attr("class","nav-link active").click();
                    var tabID = $(this).attr('id').split('_');
                    $('.tab-pane').each(function() {
                        var index = $(this).attr('id').split('_');
                        $(this).find("div[class='leftPanel']").attr('active',0);
                        $(this).find("div[class='rightPanel']").attr('active',0);   
                        $(this).attr("class","tab-pane fade");
                        if(index[1] == tabID[1]){
                            $(this).attr("class","tab-pane fade in active show");
                            $(this).find("div[class='leftPanel']").attr('active',1);
                            $(this).find("div[class='rightPanel']").attr('active',1);             
                        }
                    });
                    tabNameExists = true;
                }
                tabNum+=1;
            });
            if (!tabNameExists){
                if(tabNum>=9){
                    $('#tab-list li a,.tab-pane').each(function() {
                        var id = $(this).attr('id');
                        if(id.split('_')[1]>1){
                            $(this).parents('li').remove();
                            $(this).remove();
                        }
                    });
                }
                addTabModule(id,tabName,link,icon);
            }
        });
    }

    function addTabModule(id,tabName,link,icon){
        $("#tab-list li").attr("class","nav-item");
        $("#tab-list li a").attr("class","nav-link");
        var tabID  = $("#tab-list").find('li').length+1;
        var button = '<svg id="tab_' + tabID + '" class="close icon-svg-tabremove bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>x" /></svg>'
        $('#tab-list').append(
            $('<li class="active nav-item"><a name="'+tabName+'" module="'+link+'" class="nav-link active" id="tab_' + tabID + '" href="#tab_' + tabID + '" role="tab" data-bs-toggle="tab"><table><tr><td><svg class="icon-svg-tab bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>'+icon+'" /></svg></td><td><span>' + tabName + '</span></td><td> '+button+'</td></tr></table></i></a></li>')
        );
        $.ajax({
            type: "POST",
            url: link,
            beforeSend: function(){
                $(".spinner").show();
                $(".spinner_placeholder").hide();
            },
            success: function(result){
                $(".spinner_placeholder").show();
                $(".spinner").hide();
                $('#tab-content').append($('<div class="tab-pane fade" role="tabpanel" id="tab_' + tabID + '">' + result + '</div>'));
                onSelectTabContent();
                showTabContent();
            }
        });
        showTabContent();
        removeModuleTab();
        onSelectTabContent();
    }

    function onSelectTabContent(){
        $("#tab-list").find("li a").unbind("click").click(function(){
            var tabID = $(this).attr('id').split('_');
            $('.tab-pane').each(function() {
                var index = $(this).attr('id').split('_');
                $(this).attr("class","tab-pane fade");
                $(this).find("div[class='leftPanel']").attr('active',0);
                $(this).find("div[class='rightPanel']").attr('active',0);     
                if(index[1] == tabID[1]){
                    $(this).attr("class","tab-pane fade in active show");
                    $(this).find("div[class='leftPanel']").attr('active',1);
                    $(this).find("div[class='rightPanel']").attr('active',1);
                }
            });
        });
    }

    function removeModuleTab(){
        $('#tab-list').unbind("click").on('click', '.close', function() {
            var tabID = $(this).parents('a').attr('href');
            $(this).parents('li').remove();
            $(tabID).remove();
            var button='<svg id="tab_' + tabID + '"  class="close icon-svg-tabremove bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>x" /></svg>'
            resetTab(tabID,button);
        });
    }

    function resetTab(tabID,button){
        var tabs=$("#tab-list li:not(:first)");
        var len=1
        $(tabs).each(function(k,v){
            len++;
        });
        tabID--;
        var total = $("#tab-list").find('li').length;
        $("#tab-list").find('li').each(function(index) {
            if (index === total - 1) {
                // this is the last one
                $(this).find('a').attr("class","nav-link active").click();
                $(this).click();
            }
        });
        showTabContent();
    }

    function showTabContent(){
        var totalDiv = $('.tab-pane').length;
        $('.tab-pane').each(function(index) {
            $(this).attr("class","tab-pane fade");
            $(this).find("div[class='leftPanel']").attr('active',0);
            $(this).find("div[class='rightPanel']").attr('active',0);
            if (index === totalDiv - 1) {
                // this is the last one
                $(this).attr("class","tab-pane fade in active show");
                $(this).find("div[class='leftPanel']").attr('active',1);
                $(this).find("div[class='rightPanel']").attr('active',1);
            }
        });
    }
</script>
<div id="sidebar" class=''>
    <div class="sidebar-wrapper active">
        <div class="">
            <img src="<?php echo $this->webroot;?>img/company_logo.png" style="width:240px; height:120px;" alt="" srcset="">
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class='sidebar-title'><?php echo MENU_DASHBOARD;?></li>
                <li class="sidebar-item">
                    <a href="#" module="#" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="grid"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>grid" /></svg>
                        <span><?php echo MENU_DASHBOARD;?></span>
                    </a>
                </li>
                <li class='sidebar-title'><?php echo MENU_TODO_LIST;?></li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/todo_lists/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="card-checklist"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>card-checklist" /></svg>
                        <span><?php echo MENU_TODO_LIST;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/meeting_themes/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="window"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>window" /></svg>
                        <span><?php echo MENU_MEETING_THEME;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/meeting_notes/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="card-text"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>card-text" /></svg>
                        <span><?php echo MENU_MEETING_NOTE;?></span>
                    </a>
                </li>
                <li class='sidebar-title'>Setting</li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/companies/indexCompany" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="house-door"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>house-door" /></svg>
                        <span><?php echo MENU_COMPANY_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/employees/indexEmployee" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-plus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-plus" /></svg>
                        <span><?php echo MENU_EMPLOYEE;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/egroups/indexEgroup" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="people"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>people" /></svg>
                        <span><?php echo MENU_EMPLOYEE_GROUP;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/employee_types/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-badge"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-badge" /></svg>
                        <span><?php echo MENU_EMPLOYEE_TYPE;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/vgroups/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="people"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>people" /></svg>
                        <span><?php echo MENU_VENDOR_GROUP_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/vendors/indexVendor" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-plus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-plus" /></svg>
                        <span><?php echo MENU_VENDOR;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/cgroups/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="people"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>people" /></svg>
                        <span><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/customers/indexCustomer" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-plus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-plus" /></svg>
                        <span><?php echo MENU_CUSTOMER;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/users/indexUser" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-plus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-plus" /></svg>
                        <span><?php echo MENU_USER_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/groups/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="person-plus"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>person-plus" /></svg>
                        <span><?php echo MENU_GROUP_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/service_groups/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="journal-album"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>journal-album" /></svg>
                        <span><?php echo MENU_SECTION_MANAGEMENT;?></span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" module="<?php echo $this->base;?>/services/index" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="journal-text"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>journal-text" /></svg>
                        <span><?php echo MENU_SERVICE_MANAGEMENT;?></span>
                    </a>
                </li>
                <!-- <li class="sidebar-item">
                    <a href="#" module="#"  target="_blank" class='sidebar-link'>
                        <svg class="bi icon-menu" icon="diagram-3"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>diagram-3" /></svg>
                        <span>Organization Chat</span>
                    </a>
                </li> -->
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
