<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".table td:first-child").addClass('first');
        $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/customerAjax/<?php echo $companyId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $(".table tr").click(function(){
                    $(this).find("input[name='chkCustomer']").attr("checked", true);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
        $("#changeSOCustomerGroup").change(function(){
            var valueId = $(this).val();
            var Tablesetting = oSOCustomerTable.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/customerAjax/<?php echo $companyId; ?>/"+valueId;
            oCache.iCacheLower = -1;
            oSOCustomerTable.fnDraw(false);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:right;">
        <?php echo GENERAL_GROUP; ?>:
        <select id="changeSOCustomerGroup" style="width:150px;">
            <option value=""><?php echo TABLE_ALL; ?></option>
            <?php
            $queryPgroup = mysql_query("SELECT * FROM `cgroups` WHERE is_active = 1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
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
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_CODE; ?></th>
                <th><?php echo TABLE_NAME_IN_KHMER; ?></th>
                <th><?php echo TABLE_NAME; ?></th>
                <th><?php echo TABLE_SEX; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>