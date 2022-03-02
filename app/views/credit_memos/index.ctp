<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], 'credit_memos', 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowAging = checkAccess($user['User']['id'], $this->params['controller'], 'aging');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableCreditMemo;
    var tabCreditMemoId  = $(".ui-tabs-selected a").attr("href");
    var tabCreditMemoReg = '';
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableCreditMemo = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/" + $("#changeCustomerIdCM").val() + "/" + $("#changeStatusCM").val() + "/" + $("#changeBalanceCM").val() + "/" + $("#changeDateCM").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", {direction: "left"}, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                <?php
                if($allowPrint){
                ?>
                $(".btnPrintInvoiceCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/" + $(this).attr("rel");
                    $.ajax({
                        type: "POST",
                        url: url,
                        beforeSend: function() {
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printResult) {
                            w = window.open();
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
                $(".btnReceiveCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", {direction: "left"}, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/" + id);
                });
                <?php
                }
                if($allowEdit){
                ?>
                $(".btnEditCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", {direction: "left"}, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/" + id);
                });
                <?php
                }
                if($allowAging){
                ?>
                $(".btnAgingCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel = $(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", {direction: "left"}, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/aging/" + id);
                });
                <?php
                }
                if($allowVoid){
                ?>
                $(".btnVoidCreditMemo").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    voidCM(id, name);
                });
                <?php
                }
                ?>
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [0],
                    "bSortable": false, "aTargets": [0, -1]
                }],
            "aaSorting": [[1, "desc"]]
        });
        
        $('#changeDateCM').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#changeStatusCM, #changeBalanceCM, #changeDateCM").unbind("change").change(function() {
            loadFilterCreditMemo();
        });
        
        $("#clearDateCM").unbind("click").click(function() {
            $('#changeDateCM').val('');
            loadFilterCreditMemo();
        });
        
        $("#changeCustomerCM").autocomplete("<?php echo $this->base ."/reports/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            }
        }).result(function(event, value){
            $("#changeCustomerIdCM").val(value.toString().split(".*")[0]);
            $("#changeCustomerCM").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]).attr("readonly", true);
            $("#clearCustomerCM").show();
            loadFilterCreditMemo();
        });
        
        $("#clearCustomerCM").unbind("click").click(function(){
            $("#changeCustomerIdCM").val("all");
            $("#changeCustomerCM").val("");
            $("#changeCustomerCM").removeAttr("readonly");
            $("#clearCustomerCM").hide();
            loadFilterCreditMemo();
        });
        
        $(".btnRefreshCreditMemo").unbind("click").click(function(){
            loadFilterCreditMemo();
        });
        
        <?php
        if ($allowAdd) {
        ?>
        $(".btnAddCreditMemo").unbind("click").click(function(event) {
            event.preventDefault();
            var leftPanel = $(this).parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", {direction: "left"}, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        <?php } ?>
    });
    
    function loadFilterCreditMemo(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDateCM").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTableCreditMemo.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/" + $("#changeCustomerIdCM").val() + "/" + $("#changeStatusCM").val() + "/" + $("#changeBalanceCM").val() + "/" + $("#changeDateCM").val();
        oCache.iCacheLower = -1;
        oTableCreditMemo.fnDraw(false);
        $("#changeDateCM").datepicker("option", "dateFormat", "dd/mm/yy");
    }
    
    <?php
    if($allowVoid){
    ?>
    function voidCM(id, name) {
        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_VOID; ?> <b>' + name + '</b>?</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_CONFIRMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            open: function(event, ui) {
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_VOID; ?>': function() {
                    $.ajax({
                        type: "GET",
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/void/" + id,
                        data: "",
                        beforeSend: function() {
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result) {
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            oCache.iCacheLower = -1;
                            oTableCreditMemo.fnDraw(false);
                            // alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Credit Memo', 'Delete', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Credit Memo', 'Delete', 1, '');
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
            <a href="" class="positive btnAddCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt="" />
                <?php echo MENU_CREDIT_MEMO_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshCreditMemo">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateCM" style="width: 115px; height: 25px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateCM" />
            <label for="changeCustomerCM"><?php echo TABLE_CUSTOMER; ?> :</label>
            <input type="hidden" id="changeCustomerIdCM" value="all" />
            <input type="text" id="changeCustomerCM" style="width: 250px; height: 25px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Customer')" id="clearCustomerCM" />
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusCM" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1">Issued</option>
                <option value="2">Fulfilled</option>
                <option value="0">Void</option>
            </select>
            <?php echo GENERAL_TYPE; ?> :
            <select id="changeBalanceCM" style="width: 130px; height: 30px;">
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
                    <th style="width: 120px !important;"><?php echo TABLE_CREDIT_MEMO_NUMBER; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
                    <th><?php echo TABLE_CUSTOMER_NAME; ?></th>
                    <th style="width: 130px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if ($allowAdd) { ?>
        <div style="padding: 5px;border: 1px dashed #bbbbbb;">
            <div class="buttons">
                <a href="" class="positive btnAddCreditMemo">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                    <?php echo MENU_CREDIT_MEMO_MANAGEMENT_ADD; ?>
                </a>
            </div>
            <div style="clear: both;"></div>
        </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>