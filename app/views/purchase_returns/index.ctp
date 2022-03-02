<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], 'purchase_returns', 'add');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$tblName = "tbl" . rand(); 
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTablePurchaseReturn;
    var tabBRId  = $(".ui-tabs-selected a").attr("href");
    var tabBRReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        var length = 10;
        var tabIndex = 1;
        if($.cookie('prDisLength')!=null){
            length = $.cookie('prDisLength');
        }
        if($.cookie('prTabIndex')!=null){
            tabIndex = $.cookie('prTabIndex');
        }
        if($.cookie('prStatus')!=null){
            $("#changeStatusBillReturn").val($.cookie('prStatus'));
        }
        if($.cookie('prType')!=null){
            $("#changeBalanceBillReturn").val($.cookie('prType'));
        }
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTablePurchaseReturn = $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": length,
            "iTabIndex": tabIndex,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeStatusBillReturn").val()+"/"+$("#changeBalanceBillReturn").val()+"/"+$("#changeVendorIdBillReturn").val()+"/"+$("#changeDateBillReturn").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewPurchaseReturn").unbind("click").click(function(event){
                    event.preventDefault();
                    var id    = $(this).attr('rel');
                    var isPos = $(this).attr('href');
                    var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    if(isPos==1){
                        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewPos/" + id);
                    }else{
                        rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                    }
                });
                <?php
                if($allowPrint){
                ?>
                $(".btnPrintInvoicePurchaseReturn").unbind("click").click(function(event){
                    event.preventDefault();
                    var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+$(this).attr("rel");
                    $.ajax({
                        type: "POST",
                        url: url,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printResult){
                            w=window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printResult);
                            w.document.close();
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        }
                    });
                });
                <?php
                }
                if($allowReceive){
                ?>
                $(".btnReceivePurchaseReturn").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/" + id);
                });
                <?php
                }
                if($allowEdit){
                ?>
                $(".btnEditPurchaseReturn").unbind("click").click(function(event){
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
                if($allowAging){
                ?>
                $(".btnAgingPurchaseReturn").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + id);
                });
                <?php
                }
                if($allowVoid){
                ?>
                $(".btnVoidPurchaseReturn").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    voidBillReturn(id, name);
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
        
        $("#<?php echo $tblName; ?>_length select").unbind("change").change(function(){
            $.cookie('prDisLength', $(this).val(), { expires: 7, path: "/" });
        });
        
        $("#<?php echo $tblName; ?>_paginate span:not([id])").unbind("click").click(function(){
            $.cookie('prTabIndex', $(this).val(), { expires: 7, path: "/" });
        });
        
        $('#changeDateBillReturn').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#changeDateBillReturn").unbind("change").change(function(){
            filterBillReturn();
        });
        
        $("#clearDatePO").unbind("click").click(function(){
            $('#changeDateBillReturn').val('');
            filterBillReturn();
        });

        $("#changeStatusBillReturn").unbind("change").change(function(){
            var valueId = $(this).val();
            $.cookie('prStatus', valueId, { expires: 7, path: "/" });
            filterBillReturn();
        });
        $("#changeBalanceBillReturn").unbind("change").change(function(){
            var valueId = $(this).val();
            $.cookie('prType', valueId, { expires: 7, path: "/" });
            filterBillReturn();
        });
        $("#changeVendorBillReturn").autocomplete("<?php echo $this->base ."/reports/searchVendor"; ?>", {
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
            $("#changeVendorIdBillReturn").val(value.toString().split(".*")[0]);
            $("#changeVendorBillReturn").val(value.toString().split(".*")[2]+" - "+value.toString().split(".*")[1]).attr("readonly","readonly");
            $("#clearVendorBillReturn").show();
            filterBillReturn();
        });
        
        $("#clearVendorBillReturn").unbind("click").click(function(){
            $("#changeVendorIdBillReturn").val("all");
            $("#changeVendorBillReturn").val("");
            $("#changeVendorBillReturn").removeAttr("readonly");
            $("#clearVendorBillReturn").hide();
            filterBillReturn();
        });
        
        $(".btnRefreshPurchaseReturn").unbind("click").click(function(){
            filterBillReturn();
        });
        
        <?php
        if($allowAdd){
        ?>
        $(".btnAddPurchaseReturn").unbind("click").click(function(event){
            event.preventDefault();
            var leftPanel  = $(this).parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
                leftPanel.html("");
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        <?php
        }
        ?>
    });
    <?php
    if($allowVoid){
    ?>
    function voidBillReturn(id, name){
        $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
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
                            oTablePurchaseReturn.fnDraw(false);
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Bill Return', 'Delete', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Bill Return', 'Delete', 1, '');
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
        
    function filterBillReturn(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="9" class="first dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDateBillReturn").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTablePurchaseReturn.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeStatusBillReturn").val()+"/"+$("#changeBalanceBillReturn").val()+"/"+$("#changeVendorIdBillReturn").val()+"/"+$("#changeDateBillReturn").val();
        oCache.iCacheLower = -1;
        oTablePurchaseReturn.fnDraw(false);
        $("#changeDateBillReturn").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if ($allowAdd) { ?>
        <div class="buttons">
                <a href="" class="positive btnAddPurchaseReturn">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt="" />
                <?php echo MENU_PURCHASE_RETURN_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshPurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateBillReturn" style="width: 115px; height: 25px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDatePO" />
            <label for="changeVendorBillReturn"><?php echo TABLE_VENDOR; ?> :</label>
            <input type="hidden" id="changeVendorIdBillReturn" value="all" />
            <input type="text" id="changeVendorBillReturn" style="width: 250px; height: 25px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Vendor')" id="clearVendorBillReturn" />
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusBillReturn" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1">Issued</option>
                <option value="2">Fulfilled</option>
                <option value="3">Partial</option>
                <option value="0">Void</option>
            </select>
            <?php echo GENERAL_TYPE; ?> :
            <select id="changeBalanceBillReturn" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1"><?php echo TABLE_INDEBTED; ?></option>
                <option value="2"><?php echo GENERAL_PAID; ?></option>
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
                    <th style="width: 120px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 120px !important;"><?php echo PURCHASE_RETURN_CODE; ?></th>
                    <th style="width: 180px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
                    <th><?php echo TABLE_VENDOR; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if ($allowAdd) { ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddPurchaseReturn">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_PURCHASE_RETURN_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>