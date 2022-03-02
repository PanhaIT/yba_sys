<script type="text/javascript">
    $(document).ready(function () {
        $("#nav").find("li:has(span):not(:has(li))").hide();
        $(".dir").parent(":not(:has(ul))").find(".dir").removeAttr("class");
        $("#nav").find("li:has(span):has(li)").each(function(){
            if($(this).html().replace(/<li><\/li>/g,"").indexOf("<li>")==-1){
                $(this).hide();
            }
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
        
        $(".blank").unbind('click').click(function(event){
            event.preventDefault();
            window.open($(this).attr("href"));
        });
    });
</script>
<?php

if ($this->params['controller'] != 'users' || ($this->params['controller'] == 'users' && $this->params['action'] != 'login')) {
    $this->element('check_access');
    $str = '';
    if (!empty($menu)) {
        $tmp = '';
        foreach ($menu as $index => $menuItem) {
            $classDir = 'dir';
            if (empty($menuItem['submenu'])) {
                $classDir = '';
            }
            $tmp.='<li>';
            if ($menuItem['url'] != '') {
                $url = explode("/", $menuItem['url']);
                for ($i = 0; $i < sizeof($url); $i++) {
                    if ($url[$i] != '') {
                        $urlController = $url[$i];
                        $urlView = $url[$i + 1];
                        break;
                    }
                }
                if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                    $tmp.=$html->link(__($menuItem['text'], true), '/' . $menuItem['url'], array('class' => $classDir . ' ' . $menuItem['target'], 'escape' => false));
                }
            } else {
                $tmp.=$this->Html->tag('span', $menuItem['text'], array('class' => $classDir));
            }
            if (!empty($menuItem['submenu'])) {
                $subTmp = '';
                foreach ($menuItem['submenu'] as $subMenu) {
                    $classDir = 'dir';
                    if (empty($subMenu['submenu'])) {
                        $classDir = '';
                    }
                    $subTmp.='<li>';
                    if ($subMenu['url'] != '') {
                        $url = explode("/", $subMenu['url']);
                        for ($i = 0; $i < sizeof($url); $i++) {
                            if ($url[$i] != '') {
                                $urlController = $url[$i];
                                $urlView = $url[$i + 1];
                                break;
                            }
                        }
                        if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                            $subTmp.=$html->link(__($subMenu['text'], true), '/' . $subMenu['url'], array('class' => $classDir . ' ' . $subMenu['target'], 'escape' => false));
                        }
                    } else {
                        $subTmp.=$this->Html->tag('span', $subMenu['text'], array('class' => $classDir));
                    }
                    if (!empty($subMenu['submenu'])) {
                        $subSubTmp = '';
                        foreach ($subMenu['submenu'] as $subSubMenu) {
                            $url = explode("/", $subSubMenu['url']);
                            for ($i = 0; $i < sizeof($url); $i++) {
                                if ($url[$i] != '') {
                                    $urlController = $url[$i];
                                    $urlView = $url[$i + 1];
                                    break;
                                }
                            }
                            if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                                $subSubTmp.='<li>' . $html->link(__($subSubMenu['text'], true), '/' . $subSubMenu['url'], array('class' => $subSubMenu['target'], 'escape' => false)) . '</li>';
                            }
                        }
                        if ($subSubTmp != '') {
                            $subTmp.='<ul>' . $subSubTmp . '</ul>';
                        }
                    }
                    $subTmp.='</li>';
                }
                if (str_replace(array("<li>", "</li>"), "", $subTmp) != '') {
                    $tmp.='<ul>' . $subTmp . '</ul>';
                }
            }
            $tmp.='</li>';
        }
    }
    if (str_replace(array("<li>", "</li>"), "", $tmp) != '') {
        $str = '<ul id="nav" class="dropdown dropdown-horizontal">' . $tmp . '</ul>';
    }
}
echo $str;
?>