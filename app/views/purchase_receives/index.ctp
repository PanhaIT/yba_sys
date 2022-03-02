<?php
// Authentication
$this->element('check_access');
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oReceivePOTable;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();       
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oReceivePOTable = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeLocationGroupReceive").val()+"/"+$("#changeLocationReceive").val()+"/"+$("#changeStatusReceivePO").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnReceiveOrder").click(function(event){
                    event.preventDefault();
                    $("#tabs").tabs();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide( "slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                        leftPanel.html('');
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/" + id);
                });
                $(".btnReceiveView").click(function(event){
                    event.preventDefault();
                    $("#tabs").tabs();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide( "slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                        leftPanel.html('');
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [ 0 ],
                    "bSortable": false, "aTargets": [ 0,-1 ]
                }],
            "aaSorting": [[ 1, "desc" ]]
        });            
        
        $("#changeLocationGroupReceive").change(function(){
            checkLocationByGroupPBReceiveDB();
            var Tablesetting = oReceivePOTable.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeLocationGroupReceive").val()+"/"+$("#changeLocationReceive").val()+"/"+$("#changeStatusReceivePO").val(),
            oCache.iCacheLower = -1;
            oReceivePOTable.fnDraw(false);
            $("#changeDate").datepicker("option", "dateFormat", "dd/mm/yy");
        });
        $("#changeLocationReceive").change(function(){
            var Tablesetting = oReceivePOTable.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeLocationGroupReceive").val()+"/"+$("#changeLocationReceive").val()+"/"+$("#changeStatusReceivePO").val()
            oCache.iCacheLower = -1;
            oReceivePOTable.fnDraw(false);
            $("#changeDate").datepicker("option", "dateFormat", "dd/mm/yy");
        });
        
        $("#changeStatusReceivePO").change(function(){
            var valueId = $(this).val();
            $.cookie('pReceiveStatus', valueId, { expires: 7, path: "/" });
            var Tablesetting = oReceivePOTable.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeLocationGroupReceive").val()+"/"+$("#changeLocationReceive").val()+"/"+valueId,
            oCache.iCacheLower = -1;
            oReceivePOTable.fnDraw(false);
        });  
        checkLocationByGroupPBReceiveDB();
    });
    
    function checkLocationByGroupPBReceiveDB(){
        var locationGroup = $("#changeLocationGroupReceive").val();
        // Location Filter
        $("#changeLocationReceive").filterOptions('location-group', locationGroup, 'all');
    }
</script>
<?php if (!$time) { ?>
<div class="leftPanel" id="selectConentPOR">
<?php } ?>
    <div style="border: 1px dashed #bbbbbb; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px; padding-right: 5px;">
        <div style="float:right;">            
            <?php echo TABLE_LOCATION_GROUP; ?> :
            <?php echo $this->Form->select('location_group_id', $locationGroups, null, array('escape' => false, 'id' => 'changeLocationGroupReceive', 'style' => 'width: 130px; height: 25px;', 'empty' => array('all' => TABLE_ALL))); ?>
            <?php echo TABLE_LOCATION; ?> :
            <select id="changeLocationReceive" style="width: 130px; height: 25px;">
                <option value="all" location-group="0"><?php echo TABLE_ALL; ?></option>
                <?php 
                foreach($locations AS $location){
                ?>
                    <option value="<?php echo $location['Location']['id']; ?>" location-group="<?php echo $location['Location']['location_group_id']; ?>"><?php echo $location['Location']['name']; ?></option>
                <?php
                }
                ?>
            </select>
            <?php echo TABLE_STATUS; ?> : 
            <select id="changeStatusReceivePO" style="width: 130px; height: 25px;">
                <option value="all"><?php echo "All"; ?></option>
                <option value="1"><?php echo "Issued"; ?></option>
                <option value="2"><?php echo "Partial"; ?></option>
                <option value="3"><?php echo "Fulfilled"; ?></option>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_PO_NUMBER; ?></th>
                    <th><?php echo MENU_VENDOR; ?></th>
                    <th><?php echo TABLE_LOCATION_GROUP; ?></th>
                    <th><?php echo TABLE_DATE_ORDER; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php if (!$time) { ?>
</div>
<div class="rightPanel"></div>
<?php } ?>