<?php
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE is_active = 1 LIMIT 1)");
$rowSym = mysql_fetch_array($sqlSym);

$rnd = rand();
$oTable = "oTable" . $rnd;
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajaxByAging/<?php echo $type; ?>/<?php echo $typeId; ?>/<?php echo $dateType; ?>/<?php echo $date; ?>/<?php echo $from; ?>/<?php echo $to; ?>/<?php echo $through; ?>/<?php echo $glIdList; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2,-3,-4,-5,-6,-7 ]
            }],
            "aaSorting": [[ 1, "asc" ]]
        });
    });
</script>
<style type="text/css">
    #<?php echo $tblName; ?> th{
        vertical-align: top;
        padding: 10px;
    }
    #<?php echo $tblName; ?> td{
        vertical-align: top;
        padding: 10px;
    }
</style>
<div class="leftPanel">
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 180px !important;"><?php echo TABLE_CREATED_BY; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_MEMO; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT .' ('.$rowSym[0].')'; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_BALANCE .' ('.$rowSym[0].')'; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>