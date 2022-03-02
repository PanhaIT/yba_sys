<?php
// Authentication
$this->element('check_access');
$allowAdd  = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTOTable;
    var tabTOId  = $(".ui-tabs-selected a").attr("href");
    var tabTOReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var length = 10;
        var tabIndex = 1;
        if($.cookie('TrDisLength')!=null){
            length = $.cookie('TrDisLength');
        }
        if($.cookie('TrTabIndex')!=null){
            tabIndex = $.cookie('TrTabIndex');
        }
        if($.cookie('TrStatus')!=null){
            $("#changeStatusTO").val($.cookie('TrStatus'));
        }
        var dateFrom = $("#changeDateFromTO").val().toString().split("/")[2]+"-"+$("#changeDateFromTO").val().toString().split("/")[1]+"-"+$("#changeDateFromTO").val().toString().split("/")[0];
        var dateTo   = $("#changeDateToTO").val().toString().split("/")[2]+"-"+$("#changeDateToTO").val().toString().split("/")[1]+"-"+$("#changeDateToTO").val().toString().split("/")[0];
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTOTable = $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": length,
            "iTabIndex": tabIndex,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+dateFrom+"/"+dateTo+"/"+$("#changeFromWarehouseTO").val()+"/"+$("#changeToWarehouseTO").val()+"/"+$("#changeStatusTO").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewTransferOrder").click(function(event){
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
                if($allowEdit) {
                ?>
                $(".btnEditTransferOrder").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });
                <?php
                }
                if($allowDelete){
                ?>
                $(".btnDeleteTransferOrder").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_DELETE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTOTable.fnDraw(false);
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
                if($allowPrint){
                ?>
                $(".btnPrintTOInvoice").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr("rel");
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceTO" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_TRANSFER_ORDER; ?></span></button></div> ');
                    $(".printInvoiceTO").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printReceiptResult){
                                w=window.open();
                                w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                w.document.write(printReceiptResult);
                                w.document.close();
                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            }
                        });
                    });
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        close: function(){
                            $(this).dialog({close: function(){}});
                            $(this).dialog("close");
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
                <?php
                }
                if($allowApprove){
                ?>
                $(".btnApproveTransferOrder").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_APPROVE; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_APPROVE; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/approve/" + id+"/1",
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTOTable.fnDraw(false);
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
                            '<?php echo ACTION_REJECT; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/approve/" + id+"/2",
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTOTable.fnDraw(false);
                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
                <?php 
                    if($allowAging){
                ?>
                $(".btnAgingTransferOrder").click(function(event){
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
                ?>
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });
        
        $("#<?php echo $tblName; ?>_length select").change(function(){
            $.cookie('TrDisLength', $(this).val(), { expires: 7, path: "/" });
        });
        
        $("#<?php echo $tblName; ?>_paginate span:not([id])").click(function(){
            $.cookie('TrTabIndex', $(this).val(), { expires: 7, path: "/" });
        });
        <?php if($allowAdd){ ?>
        $(".btnAddTransferOrder").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        <?php } ?>
        
        var dateTOs = $("#changeDateFromTO, #changeDateToTO").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "changeDateFromTO" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dateTOs.not( this ).datepicker( "option", option, date );
            }
        });
        
        $(".btnRefreshTransferOrder").unbind("click").click(function(){
            filterTransferOrder();
        });
    });
    
    function filterTransferOrder(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="10" class="first dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        var dateFrom = $("#changeDateFromTO").val().toString().split("/")[2]+"-"+$("#changeDateFromTO").val().toString().split("/")[1]+"-"+$("#changeDateFromTO").val().toString().split("/")[0];
        var dateTo   = $("#changeDateToTO").val().toString().split("/")[2]+"-"+$("#changeDateToTO").val().toString().split("/")[1]+"-"+$("#changeDateToTO").val().toString().split("/")[0];
        var Tablesetting = oTOTable.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+dateFrom+"/"+dateTo+"/"+$("#changeFromWarehouseTO").val()+"/"+$("#changeToWarehouseTO").val()+"/"+$("#changeStatusTO").val();
        oCache.iCacheLower = -1;
        oTOTable.fnDraw(false);
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddTransferOrder">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_TRANSFER_ORDER_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshTransferOrder">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo REPORT_FROM; ?> :
            <input type="text" id="changeDateFromTO" style="width: 115px; height: 25px;" readonly="readonly" value="<?php echo date("d/m/Y"); ?>" />
            <?php echo REPORT_TO; ?> :
            <input type="text" id="changeDateToTO" style="width: 115px; height: 25px;" readonly="readonly" value="<?php echo date("d/m/Y"); ?>" />
            <?php echo TABLE_FROM_WAREHOUSE; ?> :
            <select id="changeFromWarehouseTO" style="width: 170px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $locationSq=mysql_query("SELECT loc.id as id, loc.name as name FROM location_groups as loc WHERE loc.is_active=1 AND loc.id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")");
                while($dataLoc=mysql_fetch_array($locationSq)){
                ?>
                <option value="<?php echo $dataLoc['id']; ?>"><?php echo $dataLoc['name']; ?></option>
                <?php } ?>
            </select>
            <?php echo TABLE_TO_WAREHOUSE; ?> :
            <select id="changeToWarehouseTO" style="width: 170px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php
                $locationSq=mysql_query("SELECT loc.id as id, loc.name as name FROM location_groups as loc WHERE loc.is_active=1 AND loc.id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")");
                while($dataLoc=mysql_fetch_array($locationSq)){
                ?>
                <option value="<?php echo $dataLoc['id']; ?>"><?php echo $dataLoc['name']; ?></option>
                <?php } ?>
            </select>
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusTO" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1"><?php echo "Issued"; ?></option>
                <option value="2"><?php echo "Partial"; ?></option>
                <option value="3"><?php echo "Fulfilled"; ?></option>
                <option value="0"><?php echo "Void"; ?></option>
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
                    <th style="width: 160px !important;"><?php echo TABLE_TO_NUMBER; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_TO_DATE; ?></th>
                    <th><?php echo TABLE_FROM_WAREHOUSE; ?></th>
                    <th><?php echo TABLE_TO_WAREHOUSE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TOTAL_COST; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_BALANCE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_APPROVED; ?></th>
                    <th style="width: 100px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="10" class="first dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddTransferOrder">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_TRANSFER_ORDER_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>