<?php

$rnd = rand();
$body   = "body" . $rnd;
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
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajaxByGroupDateRange/<?php echo $chart_account_group_id; ?>/<?php echo $dateFrom; ?>/<?php echo $dateTo; ?>/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $customerId; ?>/<?php echo $vendorId; ?>/<?php echo $otherId; ?>/<?php echo $classId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                <?php
                if(!empty($title)){
                ?>
                var rowLength = $("#<?php echo $tblName; ?> th").length;
                $("#<?php echo $body; ?>").prepend('<tr><td colspan="'+rowLength+'"><span style="font-size: 15px; font-weight: bold;"><?php echo $title; ?></span></td></tr>');
                <?php
                }
                ?>
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
                    <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_BALANCE; ?></th>
                </tr>
            </thead>
            <tbody id="<?php echo $body; ?>">
                <tr>
                    <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>