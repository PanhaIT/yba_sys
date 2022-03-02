<?php
// Authentication
$this->element('check_access');
//$allowReceive=checkAccess($user['User']['id'], 'purchase_receives', 'index');
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oReceiveTOTable;
    // Get Index From Tab
    var index<?php echo $tblName; ?>Tab  = $('#tabs .ui-tabs-selected').index();
    var tab<?php echo $tblName; ?>Name = $("#tabs li").eq(index<?php echo $tblName; ?>Tab).find('a').attr('href');
    var tab<?php echo $tblName; ?>Select =  $("a[href='"+tab<?php echo $tblName; ?>Name+"']");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var length = 10;
        var tabIndex = 1;
//        tab<?php echo $tblName; ?>Select.unbind('click');
        if($.cookie('TreceiveDisLength')!=null){
            length = $.cookie('TreceiveDisLength');
        }
        if($.cookie('TreceiveTabIndex')!=null){
            tabIndex = $.cookie('TreceiveTabIndex');
        }
        if($.cookie('TreceiveStatus')!=null){
            $("#changeStatusTR").val($.cookie('TreceiveStatus'));
        }
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oReceiveTOTable = $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": length,
            "iTabIndex": tabIndex,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#changeStatusTR").val()+"/"+$("#changeFromWarehouseTR").val()+"/"+$("#changeToWareReceiveTR").val()+"/"+$("#changeDateTR").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnReceiveTransfer").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide( "slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/" + id);
                });
                $(".btnTOReceiveView").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide( "slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        
        //Call Function When Selected Tab
//        tab<?php echo $tblName; ?>Select.bind("click", function(){
//            oCache.iCacheLower = -1;
//            oReceiveTOTable.fnDraw(false);
//        });
        
        $("#<?php echo $tblName; ?>_length select").change(function(){
            $.cookie('TreceiveDisLength', $(this).val(), { expires: 7, path: "/" });
        });
        
        $("#<?php echo $tblName; ?>_paginate span:not([id])").click(function(){
            $.cookie('TreceiveTabIndex', $(this).val(), { expires: 7, path: "/" });
        });
        
        $('#changeDateTR').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#changeDateTR, #changeFromWarehouseTR, #changeToWareReceiveTR, #changeStatusTR").change(function(){
            filterTransferReceive();
        });
        
        $("#clearDateReceiveTO").click(function(){
            $('#changeDateTR').val('');
            filterTransferReceive();
        });
    });
    
    function filterTransferReceive(){
        $("#changeDateTR").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oReceiveTOTable.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeStatusTR").val()+"/"+$("#changeFromWarehouseTR").val()+"/"+$("#changeToWareReceiveTR").val()+"/"+$("#changeDateTR").val();
        oCache.iCacheLower = -1;
        oReceiveTOTable.fnDraw(false);
        $("#changeDateTR").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<div class="leftPanel">
    <div style="border: 1px dashed #bbbbbb; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px; padding-right: 5px;">
        <div style="float:right;">
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateTR" style="width: 115px; height: 20px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateReceiveTO" />
            <?php echo TABLE_FROM_WAREHOUSE; ?> :
            <select id="changeFromWarehouseTR" style="width: 170px; height: 25px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlFromWare =mysql_query("SELECT loc.id as id, loc.name as name FROM location_groups as loc WHERE loc.is_active=1");
                while($rowFromWare=mysql_fetch_array($sqlFromWare)){
                ?>
                <option value="<?php echo $rowFromWare['id']; ?>"><?php echo $rowFromWare['name']; ?></option>
                <?php } ?>
            </select>
            <?php echo TABLE_TO_WAREHOUSE; ?> :
            <select id="changeToWareReceiveTR" style="width: 170px; height: 25px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $sqlToWare=mysql_query("SELECT loc.id as id, loc.name as name FROM location_groups as loc WHERE loc.is_active=1 AND loc.id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")");
                while($rowToWare=mysql_fetch_array($sqlToWare)){
                ?>
                <option value="<?php echo $rowToWare['id']; ?>"><?php echo $rowToWare['name']; ?></option>
                <?php } ?>
            </select>
            <?php echo TABLE_STATUS; ?> : 
            <select id="changeStatusTR" style="width: 130px; height: 25px;">
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
                    <th style="width: 160px !important;"><?php echo TABLE_TO_NUMBER; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_TO_DATE; ?></th>
                    <th><?php echo TABLE_FROM_WAREHOUSE; ?></th>
                    <th><?php echo TABLE_TO_WAREHOUSE; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 100px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>