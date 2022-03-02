<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$tblName = "tbl" . rand(); 
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".table td:first-child").addClass('first');
        $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/orderAjax/<?php echo $companyId; ?>/ <?php echo $branchId; ?>/<?php echo $customerId; ?>?sale_id=<?php echo $saleId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $(".table tr").click(function(){
                    $(this).find("input[name='chkOrder']").attr("checked", true);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
    });
</script>
<br />
<div id="dynamic">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_SALE_ORDER_NUMBER; ?></th>
                <th><?php echo TABLE_SALE_ORDER_DATE; ?></th>
                <th><?php echo TABLE_CUSTOMER; ?></th>
                <th><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                <th><?php echo TABLE_DEPOSIT; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>