<?php 
$tblName = "tbl" . rand(); 
$rand    = rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var oTable<?php echo $rand; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/productAjax/<?php echo $companyId; ?>/<?php echo $branchId; ?>?order_date=<?php echo $orderDate; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(3)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> tr").click(function(){
                    $(this).find("input[name='chkProduct']").attr("checked", true);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [ 0 ],
                    "bSortable": false, "aTargets": [ 0,-1 ]
                }]
        });
        $("#changeCategorySaleOrderSelectProduct").change(function(){
            var valueId = $(this).val();
            var Tablesetting = oTable<?php echo $rand; ?>.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/productAjax/<?php echo $companyId; ?>/<?php echo $branchId; ?>/"+valueId+"?order_date=<?php echo $orderDate; ?>";
            oCache.iCacheLower = -1;
            oTable<?php echo $rand; ?>.fnDraw(false);
        });
    });
</script>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:right;">
        <?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?>:
        <select id="changeCategorySaleOrderSelectProduct" style="width:150px;">
            <option value=""><?php echo TABLE_ALL; ?></option>
            <?php
            $queryPgroup = mysql_query("SELECT * FROM `pgroups` WHERE is_active=1 AND (user_apply = 0 OR (user_apply = 1 AND id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) AND id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
            while($dataPgroup=mysql_fetch_array($queryPgroup)){
            ?>
            <option value="<?php echo $dataPgroup['id']; ?>"><?php echo $dataPgroup['name']; ?></option>
            <?php
            }
            ?>
        </select>
    </div>
    <div style="clear: both;"></div>
</div>
<div id="dynamic">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_CODE; ?></th>
                <th><?php echo TABLE_NAME; ?></th>
                <th><?php echo TABLE_UOM; ?></th>
                <th><?php echo TABLE_LAST_QUOTE_PRICE; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>