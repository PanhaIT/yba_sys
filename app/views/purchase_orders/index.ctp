<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowAdd  = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid  = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen  = checkAccess($user['User']['id'], $this->params['controller'], 'open');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oPRRTable;
    var tabPRId  = $(".ui-tabs-selected a").attr("href");
    var tabPRReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // close conflict tab(s)
        $('#tabs a').not("[href=#]").each(function() {
            if($.data(this, 'href.tabs')=="<?php echo $this->base; ?>/purchase_orders/index" || $.data(this, 'href.tabs')=="<?php echo $this->base; ?>/purchase_returns/index" || $.data(this, 'href.tabs')=="<?php echo $this->base; ?>/sales_orders/index" || $.data(this, 'href.tabs')=="<?php echo $this->base; ?>/credit_memos/index"){
                $("#tabs").tabs("remove", $(this).attr("href"));
            }
        });
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oPRRTable = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#changeVendorIdPurchaseRequest").val()+"/"+$("#changeStatusPO").val()+"/"+$("#changeClosePO").val()+"/"+$("#changeDate").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewPurchaseRequest").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(".btnAddPurchaseRequest").parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                <?php
                if($allowEdit){
                ?>
                $(".btnEditPurchaseRequest").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel  =  $(".btnAddPurchaseRequest").parent().parent().parent();
                    var rightPanel =  leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/"+id);
                    
                });
                <?php
                }
                if($allowVoid){
                ?>
                $(".btnDeletePurchaseRequest").unbind("click").click(function(event){
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
                                        oPRRTable.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                            createSysAct('Purchase Order', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Purchase Order', 'Delete', 1, '');
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
                if($allowClose){
                ?>
                $(".btnClosePurchaseRequest").unbind("click").click(function(event){
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
                                        oPRRTable.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                            createSysAct('Purchase Order', 'Close', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Purchase Order', 'Close', 1, '');
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
                if($allowOpen){
                ?>
                $(".btnOpenPurchaseRequest").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_OPEN; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_OPEN; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/open/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oPRRTable.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Purchase Order', 'Open', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Purchase Order', 'Open', 1, '');
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
                if($allowOpen){
                ?>
                $(".btnPrintInvoicePO").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr("rel");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printInvoiceResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printInvoiceResult);
                            w.document.close();
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
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
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
            
        });
        
        $("#<?php echo $tblName; ?>_paginate span:not([id])").unbind("click").click(function(){
            $.cookie('poTabIndex', $(this).val(), { expires: 7, path: "/" });
        });                
        
        $('#changeDate').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#changeDate").unbind("change").change(function(){
            changeFilterPO();
        });
        
        $("#clearDatePO").unbind("click").click(function(){
            $('#changeDate').val('');
            changeFilterPO();
        });
        
        $("#changeStatusPO").unbind("change").change(function(){
            changeFilterPO();
        });
        
        $("#changeClosePO").unbind("change").change(function(){
            changeFilterPO();
        });
        
        $("#changeVendorPurchaseRequest").autocomplete("<?php echo $this->base ."/reports/searchVendor"; ?>", {
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
            $("#changeVendorIdPurchaseRequest").val(value.toString().split(".*")[0]);
            $("#changeVendorPurchaseRequest").val(value.toString().split(".*")[2]+" - "+value.toString().split(".*")[1]).attr("readonly","readonly");
            $("#clearVendorPurchaseRequest").show();
            changeFilterPO();
        });
        
        $("#clearVendorPurchaseRequest").unbind("click").click(function(){
            $("#changeVendorIdPurchaseRequest").val("all");
            $("#changeVendorPurchaseRequest").val("");
            $("#changeVendorPurchaseRequest").removeAttr("readonly");
            $("#clearVendorPurchaseRequest").hide();
            changeFilterPO();
        });
        
        $(".btnRefreshPurchaseRequest").unbind("click").click(function(){
            changeFilterPO();
        });
        
        <?php if($allowAdd){ ?>
        $(".btnAddPurchaseRequest").unbind("click").click(function(event){
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
    });
    
    function changeFilterPO(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="10" class="first dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDate").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oPRRTable.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeVendorIdPurchaseRequest").val()+"/"+$("#changeStatusPO").val()+"/"+$("#changeClosePO").val()+"/"+$("#changeDate").val(),
        oCache.iCacheLower = -1;
        oPRRTable.fnDraw(false);
        $("#changeDate").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_PURCHASE_REQUEST_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDate" style="width: 115px; height: 25px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDatePO" />
            <label for="changeVendorPurchaseRequest"><?php echo TABLE_VENDOR; ?> :</label>
            <input type="hidden" id="changeVendorIdPurchaseRequest" value="all" />
            <input type="text" id="changeVendorPurchaseRequest" style="width: 250px; height: 25px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Vendor')" id="clearVendorPurchaseRequest" />
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusPO" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1">Issued</option>
                <option value="0">Void</option>
            </select>
            <?php echo TABLE_CLOSE; ?> :
            <select id="changeClosePO" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1"><?php echo ACTION_YES; ?></option>
                <option value="0"><?php echo ACTION_NO; ?></option>
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
                    <th style="width: 120px !important;"><?php echo TABLE_PO_DATE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_PO_NUMBER; ?></th>
                    <th><?php echo TABLE_VENDOR; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_DEPOSIT; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_CLOSE; ?></th>
                    <th style="width: 120px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddPurchaseRequest">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_PURCHASE_REQUEST_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>