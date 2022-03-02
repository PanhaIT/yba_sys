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
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajaxById/<?php echo $general_ledger_id; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                var totalDebit  = 0;
                var totalCrebit = 0;
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    totalDebit += Number($(this).find("td:eq(6)").text().replace(/,/g, ""));
                    totalCrebit += Number($(this).find("td:eq(7)").text().replace(/,/g, ""));
                });
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="text-align: left;font-weight: bold;" colspan="6"><?php echo strtoupper(TABLE_TOTAL); ?></td><td class="formatCurrency" style="text-align: right;">' + (totalDebit) + '</td><td class="formatCurrency" style="text-align: right;">' + (totalCrebit) + '</td></tr>');
                $('.formatCurrency').formatCurrency({colorize:true});
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
                    <th style="width: 120px !important;"><?php echo GENERAL_DEBIT .' ('.$rowSym[0].')'; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_CREDIT .' ('.$rowSym[0].')'; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>