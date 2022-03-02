<?php 
    // Function
    include('includes/function.php');
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableViewProductExpireDate;
    $(document).ready(function(){
        $("#refreshProductExpireDate").unbind("click").click(function(){
            var Tablesetting = oTableViewProductExpireDate.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewProductExpireDateAjax";
            oCache.iCacheLower = -1;
            oTableViewProductExpireDate.fnDraw(false);
        });
        // Prevent Key Enter
        preventKeyEnter();
        $("#labelProductExpireDate td:first-child").addClass('first');
        oTableViewProductExpireDate = $("#labelProductExpireDate").dataTable({
            "aLengthMenu": [[10, 25, 50, 100, 1000000], [10, 25, 50, 100, "All"]],
            "iDisplayLength": 10,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/viewProductExpireDateAjax",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {     
                $("#labelProductExpireDate td:first-child").addClass('first');
                $("#labelProductExpireDate td:last-child").css("white-space", "nowrap");
                return sPre;
            },
            "fnDrawCallback": function(oSettings, json) {
                $("#labelProductExpireDate .colspanParent").parent().attr("colspan", 5);
                $("#labelProductExpireDate .colspanParent").parent().next().remove();
                $("#labelProductExpireDate .colspanParent").parent().next().remove();
                $("#labelProductExpireDate .colspanParent").parent().next().remove();
                $("#labelProductExpireDate .colspanParent").parent().next().remove();
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [0],
                    "bSortable": false, "aTargets": [-1]
                }],
            "aaSorting": [[5, "asc"], [0, "asc"]]
        });
    });
</script>
<table cellpadding="5" class="table tblAlert" style="margin-bottom: 10px;" id="labelProductExpireDate">
    <thead>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_CODE; ?></th>
            <th><?php echo TABLE_NAME; ?></th>
            <th style="width: 150px !important;"><?php echo TABLE_QTY_IN_STOCK; ?></th>
            <th style="width: 150px !important;"><?php echo TABLE_EXPIRED_DATE; ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
        </tr>
    </tbody>
</table>