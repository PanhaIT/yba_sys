<?php
// Authentication
$this->element('check_access');
$allowAdd  = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowPay  = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableLandingCost;
    var tabLandingCostId  = $(".ui-tabs-selected a").attr("href");
    var tabLandingCostReg = '';
    $(document).ready(function(){
        oTableLandingCost = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewLandingCost").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                <?php
                if ($allowVoid) {
                ?>
                $(".btnVoidLandingCost").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    voidLandingCost(id, name);
                });
                <?php 
                }
                if ($allowEdit) {
                ?>
                $(".btnEditLandingCost").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(".btnEditLandingCost").parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/"+id);
                    
                });
                <?php 
                }
                if ($allowPay) {
                ?>
                // Action Pay
                $(".btnLandingCostAging").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + id);
                });
                <?php 
                }
                if($allowClose){
                ?>
                $(".btnLandingCostClose").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CLOSE; ?> <b>' + name + '</b>?</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/close/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableLandingCost.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                            createSysAct('Landed Cost', 'Close', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Landed Cost', 'Close', 1, '');
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                        }
                                        $("#dialog").dialog({
                                            title: '<?php echo DIALOG_INFORMATION; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 'auto',
                                            buttons: {
                                                '<?php echo ACTION_CLOSE; ?>': function() {
                                                    $(this).dialog("close");
                                                }
                                            }
                                        });
                                    }
                                });
                            },
                            '<?php echo ACTION_CANCEL; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
                <?php
                }
                ?>
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0, -1 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
        });
        
        
        $('#changeDateLandingCost').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");

        $("#changeVendorIdLandingCost, #changeStatusLandingCost, #changeDateLandingCost").change(function(){
            resetFilterLandingCost();
        });
        
        $("#clearDateLandingCost").click(function(){
            $('#changeDateLandingCost').val('');
            resetFilterLandingCost();
        });
        
        $("#changeVendorLandingCost").autocomplete("<?php echo $this->base ."/reports/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            }
        }).result(function(event, value){
            $("#changeVendorIdLandingCost").val(value.toString().split(".*")[0]);
            $("#changeVendorLandingCost").val(value.toString().split(".*")[2]+" - "+value.toString().split(".*")[1]).attr("readonly","readonly");
            $("#clearVendorLandingCost").show();
            resetFilterLandingCost();
        });
        
        $("#clearVendorLandingCost").click(function(){
            $("#changeVendorIdLandingCost").val("all");
            $("#changeVendorLandingCost").val("");
            $("#changeVendorLandingCost").removeAttr("readonly");
            $("#clearVendorLandingCost").hide();
            resetFilterLandingCost();
        });
        <?php 
        if ($allowAdd) {
        ?>
        $(".btnAddLandingCost").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        <?php
        }
        ?>
    });
    
    function resetFilterLandingCost(){
        $("#changeDateLandingCost").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTableLandingCost.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeVendorIdLandingCost").val()+"/"+$("#changeStatusLandingCost").val()+"/"+$("#changeDateLandingCost").val();
        oCache.iCacheLower = -1;
        oTableLandingCost.fnDraw(false);
        $("#changeDateLandingCost").datepicker("option", "dateFormat", "dd/mm/yy");
    }
    <?php
    if ($allowVoid) {
    ?>
    function voidLandingCost(id, name){
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_VOID; ?>': function() {
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/void/" + id,
                        data: "",
                        beforeSend: function(){
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result){
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            oCache.iCacheLower = -1;
                            oTableLandingCost.fnDraw(false);
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Landed Cost', 'Void', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Landed Cost', 'Void', 1, '');
                                // alert message
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                            }
                            $("#dialog").dialog({
                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    });
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    <?php 
    }
    ?>
</script>
<div class="leftPanel">
    
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php 
        if ($allowAdd) {
        ?>
        <div class="buttons">
                <a href="" class="positive btnAddLandingCost">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt="" />
                <?php echo MENU_LANDED_COST_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div style="float:right;">
            <input type="hidden" id="changeCompanySO" value="1" />
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateLandingCost" style="width: 115px; height: 20px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateLandingCost" />
            <label for="changeVendorLandingCost"><?php echo TABLE_VENDOR; ?> :</label>
            <input type="hidden" id="changeVendorIdLandingCost" value="all" />
            <input type="text" id="changeVendorLandingCost" style="width: 250px; height: 20px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Vendor')" id="clearVendorLandingCost" />
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusLandingCost" style="width: 130px; height: 25px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1">Open</option>
                <option value="2">Closed</option>
                <option value="0">Void</option>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_CODE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_PURCHASE_BILL_CODE; ?></th>
                    <th><?php echo TABLE_VENDOR_NAME; ?></th>
                    <th style="width: 140px !important;"><?php echo MENU_LANDED_COST_TYPE; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php 
       if ($allowAdd) {
    ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddLandingCost">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_LANDED_COST_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>