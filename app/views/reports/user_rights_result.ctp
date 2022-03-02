<?php
include('includes/function.php');
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_USER_RIGHTS . '</b><br /><br />';
    if($_POST['user_id']!='') {
        $queryUser=mysql_query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=".$_POST['user_id']);
        $dataUser=mysql_fetch_array($queryUser);
        $msg .= USER_USER_NAME.': '.$dataUser[0];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first" style="width: 50%;">Module</th>
                    <th>Rights</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $queryType = mysql_query("SELECT id,name FROM module_types ORDER BY ordering");
                while ($dataType = mysql_fetch_array($queryType)) {
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;vertical-align: top;"><?php echo $dataType['name']; ?></td>
                    <td>
                        <?php
                        $queryModule = mysql_query("SELECT id,name,(SELECT COUNT(module_id) FROM permissions WHERE module_id=m.id AND group_id IN (SELECT group_id FROM user_groups WHERE user_id=" . $_POST['user_id'] . ")) AS chk FROM modules m WHERE module_type_id=" . $dataType['id'] . " ORDER BY ordering");
                        while ($dataModule = mysql_fetch_array($queryModule)) {
                            if($dataModule['chk'] != 0){
                                echo $dataModule['name'] . '<br />';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div style="clear: both;"></div>