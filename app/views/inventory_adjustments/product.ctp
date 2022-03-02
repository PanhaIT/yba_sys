<?php 
$tblName = "tbl" . rand(); 
$rand    = rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<style type="text/css">
    .table tr:hover{
        background-color: #ECFFB3;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        var oTable<?php echo $rand; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": 50,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/productAjax/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $locationGroup; ?>/"+$("#changeCategory").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $(".dataTables_filter input").focus();
                $("#dialog, #<?php echo $tblName; ?> tr", document).keydown(function(e) {
                    if (e.keyCode == 40) { // down
                        // if no radio buttons are checked, select the first one
                        if (!$("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:checked").length){
                            $("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:first").attr("checked",true);
                            // otherwise, select the next one
                        }else{
                            $("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:checked").closest("tr").next().find("input").attr("checked",true);
                        }
                    } else if (e.keyCode == 38) { // up
                        // if no radio buttons are checked, select the last one
                        if (!$("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:checked").length) {
                            $("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:last").attr("checked",true);

                            // otherwise, select the previous one
                        }else{ 

                            $("#<?php echo $tblName; ?> td input:radio[name='chkProductInvAdj']:checked").closest("tr").prev().find("input").attr("checked",true);
                        }
                    }
                    
                    //return false;
                }); 
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> tr").css("cursor", "pointer");
                $("#<?php echo $tblName; ?> tr").click(function(){
                    $(this).find("input[name='chkProductInvAdj']").attr("checked", true);
                    if($("input[name='chkProductInvAdj']:checked").val()){
                        $("#dialog").dialog("close");
                        var code = $("input[name='chkProductInvAdj']:checked").val();
                        searchProduct(1, code);
                    }
                });
                $(".dataTables_length label").css('font-size','14px');
                $(".dataTables_length select").css('height','30px');
                $(".dataTables_length option").css('font-size','14px');
                $(".dataTables_filter label").css('font-size','14px');
                $(".dataTables_filter input").css('height','20px');
                $(".dataTables_info").css('font-size','14px');
                $(".dataTables_paginate span").css('font-size','12px');
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [ 0 ],
                    "bSortable": false, "aTargets": [ 0,-1 ]
                }]
        });
        $("#changeCategory").change(function(){
            var valueId = $(this).val();
            var Tablesetting = oTable<?php echo $rand; ?>.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/productAjax/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $locationGroup; ?>/"+valueId;
            oCache.iCacheLower = -1;
            oTable<?php echo $rand; ?>.fnDraw(false);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:right;">
        <?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?>:
        <select id="changeCategory" style="width:150px; font-size: 12px; height: 30px">
            <option value="" style="font-size: 12px;"><?php echo INPUT_SELECT; ?></option>
            <?php
            $queryPgroup = mysql_query("SELECT * FROM `pgroups` WHERE is_active=1 AND (user_apply = 0 OR (user_apply = 1 AND id IN (SELECT pgroup_id FROM user_pgroups WHERE user_id = ".$user['User']['id']."))) AND id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
            while ($r = mysql_fetch_array($queryPgroup)) {
            ?>
            <option value="<?php echo $r['id']; ?>" style="font-size: 12px;" <?php if($category == $r['id']){ ?>selected="selected"<?php } ?>><?php echo $r['name']; ?></option>
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
                <th style="text-align: center;" class="first"></th>
                <th style="text-align: center;"><?php echo TABLE_BARCODE; ?></th>
                <th style="text-align: center; width: 300px !important;"><?php echo TABLE_NAME; ?></th>
                <th style="text-align: center;"><?php echo TABLE_UOM; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>