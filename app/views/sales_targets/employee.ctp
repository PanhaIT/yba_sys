<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$tblName = "tbl" . rand(); 
$rand    = rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".table td:first-child").addClass('first');
        var oTable<?php echo $rand; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/employeeAjax/<?php echo $companyId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $(".table tr").click(function(){
                    $(this).find("input[name='chkEmployee']").attr("checked", true);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
        $("#changeSOEmployeeGroup").change(function(){
            var valueId = $(this).val();
            var Tablesetting = oTable<?php echo $rand; ?>.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/employeeAjax/<?php echo $companyId; ?>/"+valueId;
            oCache.iCacheLower = -1;
            oTable<?php echo $rand; ?>.fnDraw(false);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:right;">
        <?php echo GENERAL_GROUP; ?>:
        <select id="changeSOEmployeeGroup" style="width:150px;">
            <option value=""><?php echo TABLE_ALL; ?></option>
            <?php
            $queryPgroup = mysql_query("SELECT * FROM egroups WHERE is_active=1 AND id IN (SELECT egroup_id FROM egroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
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
<br />
<div id="dynamic">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_EMPLOYEE_NUMBER; ?></th>
                <th><?php echo TABLE_NAME_IN_KHMER; ?></th>
                <th><?php echo TABLE_NAME; ?></th>
                <th><?php echo TABLE_SEX; ?></th>
                <th><?php echo TABLE_TELEPHONE; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>