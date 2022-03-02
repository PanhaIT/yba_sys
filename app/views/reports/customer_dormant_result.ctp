<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/customerDormantAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(4)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(13)").css("text-align", "center");
                return sPre;
            },
            "fnDrawCallback": function(oSettings, json) {
                $("#<?php echo $tblName; ?> .colspanParent").parent().attr("colspan", 13);
                $("#<?php echo $tblName; ?> .colspanParentHidden").parent().css("display", "none");
                $("#<?php echo $tblName; ?> .colspanParent").closest("tr").find("td").each(function(){
                    var idRefer = $(this).find("b").attr("nameParentId");
                    if(idRefer!=""){
                        $(this).attr("actionParentRefer", idRefer); 
                    }  
                });
                $("#<?php echo $tblName; ?> .childRefer").closest("tr").find("td").each(function(){
                    var idChildRefer = $(this).find("b").attr('actionChild');
                    if(idChildRefer != undefined){
                         $(this).attr("class", "actionHideShowRefer"); 
                         $(this).attr("actionChildRefer", idChildRefer); 
                    }
                });
                $("#<?php echo $tblName; ?> .colspanParent").closest("tr").find("td").click(function(){
                    var actionHideShow   = $(this).attr('actionParentRefer');
                    var imgHideShowRefer = $(this).find(".imgHideShowRefer");
                    $(".actionHideShowRefer").each(function(){
                       var referName = $(this).attr("actionchildrefer");                        
                       if(replaceNum(actionHideShow) == replaceNum(referName)){
                           var display = $(this).closest("tr").attr("style"); 
                           if(display == 'display: none;'){
                               $(this).closest("tr").removeAttr("style", 'display: none;');
                               imgHideShowRefer.removeAttr("src");
                               imgHideShowRefer.attr("src", '<?php echo $this->webroot; ?>img/minus.gif');
                           }else{
                               $(this).closest("tr").attr("style", 'display: none;');
                               imgHideShowRefer.removeAttr("src");
                               imgHideShowRefer.attr("src", '<?php echo $this->webroot; ?>img/plus.gif');
                           }
                       }
                    });
                });
            },
            
            <?php 
                if($_POST['customer_group'] != ""){
            ?>
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0 , -1 , -2 , -3 , -4 , -5 , -6 , -7 , -8 , -9 , -10 , -11 , -12]
            }],
            <?php
                }else{
            ?>
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0 ]
            }],
            <?php
                }
            ?>
            
            "aaSorting": [[ 1, "asc" ]]
        });
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
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_DORMANT_CUSTOMER . '</b><br /><br />';
    if($_POST['month_num']!='') {
        $msg .= REPORT_CUSTOMER_HAVE_NOT_PURCHASED_WITHIN.' '.$_POST['month_num'].' '.REPORT_MONTHS;
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" style="width: 100%;">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo REPORT_LAST_QO_INVOIE; ?></th>
                    <th><?php echo REPORT_LAST_QO_AMOUNT; ?></th>
                    <th><?php echo REPORT_LAST_QO_DATE; ?></th>
                    <th><?php echo REPORT_LAST_SO_INVOIE; ?></th>
                    <th><?php echo REPORT_LAST_SO_AMOUNT; ?></th>
                    <th><?php echo REPORT_LAST_SO_DATE; ?></th>
                    <th><?php echo REPORT_LAST_PURCHASE_INVOIE; ?></th>
                    <th><?php echo REPORT_LAST_PURCHASE_AMOUNT; ?></th>
                    <th><?php echo REPORT_LAST_PURCHASE_DATE; ?></th>
                    <th><?php echo TABLE_MONTH; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="13" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
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