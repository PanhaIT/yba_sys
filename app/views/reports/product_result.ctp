<?php
include('includes/function.php');
$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

// Authentication
$this->element('check_access');
$allowViewCost = checkAccess($user['User']['id'], 'products', 'viewCost');
$data = explode(",", str_replace("/", "|||", implode(',', $_POST)));
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/productAjax/<?php echo str_replace("/", "|||", implode(',', $_POST)); ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(4)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(5)").css("text-align", "right");
                <?php
                $colspan = 5;
                if(!$allowViewCost){
                    $colspan = 4;
                ?>
                $("#<?php echo $tblName; ?> th:nth-child(5)").hide();
                $("#<?php echo $tblName; ?> td:nth-child(5)").hide();
                <?php
                }
                ?>
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewQtyDetail").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr("rel");
                    var date = $(this).attr("date");
                    var status = $(this).attr("status");
                    var location = "<?php echo $data[5] != ''?$data[5]:0; ?>";
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/productViewQtyDetail/"+status+"/"+ id + "/" + date+"/"+location,
                        data: "",
                        beforeSend: function(){

                        },
                        success: function(result){
                            $("#dialog").html(result);
                            $("#dialog").dialog({
                                title: '<?php echo TABLE_QTY; ?>',
                                resizable: false,
                                modal: true,
                                width: '50%',
                                height: '500',
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
                });
                
                $(".btnViewQtyOrderDetail").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr("rel");
                    var locationGroup = "<?php echo $data[4] != ''?$data[4]:0; ?>";
                    var location = "<?php echo $data[5] != ''?$data[5]:0; ?>";
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/productViewQtyOrderDetail/"+id+"/"+ locationGroup + "/" + location,
                        data: "",
                        beforeSend: function(){

                        },
                        success: function(result){
                            $("#dialog").html(result);
                            $("#dialog").dialog({
                                title: '<?php echo TABLE_QTY_ORDER; ?>',
                                resizable: false,
                                modal: true,
                                width: '50%',
                                height: '500',
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
                });
                return sPre;
            },
            "fnDrawCallback": function(oSettings, json) {
                $("#<?php echo $tblName; ?> .colspanParent").parent().attr("colspan", <?php echo $colspan; ?>);
                $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
                $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
                $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
                $("#<?php echo $tblName; ?> .colspanParent").parent().next().remove();
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,1,2, 3,4,5,6,7,8 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
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
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/global_inventory_<?php echo $user['User']['id']; ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_PRODUCT_INVENTORY . '</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= 'Beginning'.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.'Ending'.': '.$_POST['date_to'];
    }
    if($_POST['location_group_id']!='' || $_POST['location_id']!='' || $_POST['pgroup_id']!='') {
        $msg .= '<br /><br />';
    }
    if($_POST['location_group_id']!='') {
        $query=mysql_query("SELECT name FROM location_groups WHERE id=".$_POST['location_group_id']);
        $data=mysql_fetch_array($query);
        $msg .= '<b>'.TABLE_LOCATION_GROUP.'</b>: '.$data[0];
    }
    if($_POST['location_id']!='') {
        $query=mysql_query("SELECT name FROM locations WHERE id=".$_POST['location_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <br/><b>'.TABLE_LOCATION.'</b>: '.$data[0];
    }
    if($_POST['pgroup_id']!='') {
        $query=mysql_query("SELECT name FROM pgroups WHERE id=".$_POST['pgroup_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <br/><b>'.MENU_PRODUCT_GROUP_MANAGEMENT.'</b>: '.$data[0];
    }
    if($_POST['product_id']!='') {
        $query=mysql_query("SELECT name FROM products WHERE id=".$_POST['product_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <br/><b>'.TABLE_PRODUCT.'</b>: '.$data[0];
    }
    $msg .= '<br /><br />';
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_BARCODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_UOM; ?></th>
                    <th style="width: 130px !important;"><?php echo TABLE_LAST_COST." ($)"; ?></th>
                    <th style="width: 120px !important;">Beginning Qty</th>
                    <th style="width: 120px !important;">Ending Qty</th>
                    <th style="width: 120px !important;">Qty Order</th>
                    <th style="width: 120px !important;"><?php echo TABLE_QTY_AVAILABLE; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
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
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>