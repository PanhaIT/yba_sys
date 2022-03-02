<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableReorderLevel;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableReorderLevel = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewReorderLevelAjax/1",//$("#filterWarehouseReorderLevel").val()
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#refreshReorderLevel").show();
                $("#loadingReorderLevel").hide();
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        
        $("#filterWarehouseReorderLevel").unbind("change").change(function(){
            $("#refreshReorderLevel").hide();
            $("#loadingReorderLevel").show();
            var Tablesetting = oTableReorderLevel.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewReorderLevelAjax/"+$("#filterWarehouseReorderLevel").val();
            oCache.iCacheLower = -1;
            oTableReorderLevel.fnDraw(false);
        });
        
        $("#refreshReorderLevel").unbind("click").click(function(){
            $("#refreshReorderLevel").hide();
            $("#loadingReorderLevel").show();
            var Tablesetting = oTableReorderLevel.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewReorderLevelAjax/"+$("#filterWarehouseReorderLevel").val();
            oCache.iCacheLower = -1;
            oTableReorderLevel.fnDraw(false);
        });
    });
</script>
<div id="dvViewReorderLevel" style="width: 100%; margin: 0 auto">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"><?php echo TABLE_NO; ?></th>
                <th><?php echo TABLE_BARCODE; ?></th>
                <th><?php echo TABLE_NAME; ?></th>
                <th><?php echo GENERAL_REORDER_LEVEL; ?></th>
                <th><?php echo TABLE_QTY_ON_HAND; ?></th>
                <th><?php echo TABLE_UOM; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>