<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], 'quotations', 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$allowClose = checkAccess($user['User']['id'], $this->params['controller'], 'close');
$allowOpen = checkAccess($user['User']['id'], $this->params['controller'], 'open');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableQuotation;
    var tabQuoteId  = $(".ui-tabs-selected a").attr("href");
    var tabQuotReg = '';
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableQuotation = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeCustomerIdQuotation").val()+"/"+$("#changeStatusQuotation").val()+"/"+$("#changeApproveQuotation").val()+"/"+$("#changeDateQuotation").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewQuotation").unbind("click").click(function(event) {
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
                $(".btnPrintInvoiceQuotation").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceQuote" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?></span></button><button type="submit" class="positive printInvoiceQuoteNoHead" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span><?php echo ACTION_PRINT_QUOTATION; ?> No Header</span></button></div>');
                    $(".printInvoiceQuote").unbind("click").click(function(){
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
                    $(".printInvoiceQuoteNoHead").unbind("click").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+id+"/1",
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
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
                <?php
                }
                if($allowEdit){
                ?>
                $(".btnEditQuotation").unbind("click").click(function(event) {
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
                if($allowVoid){
                ?>
                $(".btnVoidQuotation").unbind("click").click(function(event) {
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    voidQuotation(id, name);
                });
                <?php
                }
                if($allowClose){
                ?>
                $(".btnCloseQuotation").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
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
                                        oTableQuotation.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_CLOSED; ?>'){
                                            createSysAct('Quotation', 'Close', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Quotation', 'Close', 1, '');
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
                });
                <?php
                }
                if($allowOpen){
                ?>
                $(".btnOpenQuotation").unbind("click").click(function(event){
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
                                        oTableQuotation.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Quotation', 'Open', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Quotation', 'Open', 1, '');
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
                });
                <?php
                }
                if($allowApprove){
                ?>
                $(".btnApproveQuotation").unbind("click").click(function(event){
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
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/approve/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableQuotation.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                            createSysAct('Quotation', 'Approve', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Quotation', 'Approve', 1, '');
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
                });
                <?php
                }
                ?>
                // User Share
                $(".btnShareOptQuotation").unbind("click").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var saveSOpt  = $(this).attr('ssaopt');
                    var quoteSOpt = $(this).attr('shopt');
                    var quoteUser = $(this).attr('shusr');
                    var quoteEcpt = $(this).attr('shuect');
                    $.ajax({
                        type:   "POST",
                        url:    "<?php echo $this->base . "/dashboards/share"; ?>/",
                        data:   "option="+quoteSOpt+"&user="+quoteUser+"&except="+quoteEcpt+"&save="+saveSOpt,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            $("#dialog").html(msg).dialog({
                                title: '<?php echo TABLE_SHARE_OPTION; ?> '+name,
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                position:'center',
                                closeOnEscape: true,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show(); 
                                    $(".ui-dialog-titlebar-close").show();
                                },
                                buttons: {
                                    '<?php echo ACTION_OK; ?>': function() {
                                        var shareOption  = $("#ShareOption").val();
                                        var userSelected = $("#userShareSelected").val();
                                        var saveShareOpt = $("input[name='saveOption']:checked").val();
                                        var shareUser    = '';
                                        var shareUserExt = '';
                                        var userShareId  = '';
                                        if(shareOption != ''){
                                            if(shareOption == 3){
                                                shareUser = userSelected;
                                            } else if(shareOption == 4){
                                                shareUserExt = userSelected;
                                            } 
                                            // Save Share Option
                                            $.ajax({
                                                type: "POST",
                                                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveShareQuote/" + id,
                                                data:   "saveOpt="+saveShareOpt+"&shareId="+userShareId+"&shareOpt="+shareOption+"&shareUser="+shareUser+"&shareEct="+shareUserExt,
                                                beforeSend: function() {
                                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                                },
                                                success: function(result) {
                                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                    oCache.iCacheLower = -1;
                                                    oTableQuotation.fnDraw(false);
                                                    // alert message
                                                    if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                                        createSysAct('Quotation', 'Share Option Dashboard', 2, result);
                                                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                                    }else {
                                                        createSysAct('Quotation', 'Share Option Dashboard', 1, '');
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
                                            if(saveShareOpt == 2){
                                                // Save Share Option All Transaction
                                                $.ajax({
                                                    type: "POST",
                                                    url:    "<?php echo $this->base . "/dashboards/shareSave"; ?>/",
                                                    data:   "mtid=68&sp="+shareOption+"&susr="+shareUser+"&suect="+shareUserExt,
                                                    beforeSend: function() {
                                                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                                    },
                                                    success: function(result) {
                                                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                        userShareId = result;
                                                    }
                                                });
                                            }
                                        }
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    });
                });
                $(".btnHistoryQuotation").unbind("click").click(function(event){
                    event.preventDefault();
                    var code = $(this).attr("name");
                    $.ajax({
                        type: "POST",
                        url:  "<?php echo $this->base.'/'.$this->params['controller']; ?>/history/" + code,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(msg){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            $("#dialog").html(msg).dialog({
                                title: '<?php echo ACTION_VIEW_HISTORY; ?> '+code,
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 500,
                                position:'center',
                                closeOnEscape: true,
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show(); 
                                    $(".ui-dialog-titlebar-close").show();
                                },
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                    });
                });
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [0],
                    "bSortable": false, "aTargets": [-1]
                }],
            "aaSorting": [[0, "desc"]]
        });
        <?php
        if($allowAdd){
        ?>
        $(".btnAddQuotation").unbind("click").click(function(event) {
            event.preventDefault();
            var leftPanel = $(this).parent().parent().parent();
            var rightPanel = leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", {direction: "left"}, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        <?php
        }
        ?>
        $('#changeDateQuotation').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#clearDateQuotation").unbind("click").click(function(){
            $("#changeDateQuotation").val('');
            resetFilterQuotation();
        });
        
        $("#changeDateQuotation, #changeStatusQuotation, #changeApproveQuotation").unbind("change").change(function(){
            resetFilterQuotation();
        });
        
        $("#changeCusQuotation").autocomplete("<?php echo $this->base ."/".$this->params['controller']. "/searchCustomer"; ?>", {
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
            $("#changeCustomerIdQuotation").val(value.toString().split(".*")[0]);
            $("#changeCusQuotation").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]).attr("readonly", true);
            $("#clearCusQuotation").show();
            resetFilterQuotation();
        });
        
        $("#clearCusQuotation").unbind("click").click(function(){
            $("#changeCustomerIdQuotation").val("all");
            $("#changeCusQuotation").val("");
            $("#changeCusQuotation").removeAttr("readonly");
            $("#clearCusQuotation").hide();
            resetFilterQuotation();
        });
        
        $(".btnRefreshQuotation").unbind("click").click(function(){
            resetFilterQuotation();
        });
        
    });
    
    function resetFilterQuotation(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDateQuotation").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTableQuotation.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeCustomerIdQuotation").val()+"/"+$("#changeStatusQuotation").val()+"/"+$("#changeApproveQuotation").val()+"/"+$("#changeDateQuotation").val();
        oCache.iCacheLower = -1;
        oTableQuotation.fnDraw(false);
        $("#changeDateQuotation").datepicker("option", "dateFormat", "dd/mm/yy");
    }
    <?php
    if($allowVoid){
    ?>
    function voidQuotation(id, name) {
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
                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/delete/" + id,
                        data: "",
                        beforeSend: function() {
                            $("#dialog").dialog("close");
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                        },
                        success: function(result) {
                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                            oCache.iCacheLower = -1;
                            oTableQuotation.fnDraw(false);
                            // Alert message
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Quotation', 'Delete', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Quotation', 'Delete', 1, '');
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
                <a href="" class="positive btnAddQuotation">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt="" />
                    <?php echo MENU_QUOTATION_ADD; ?>
                </a>
            </div>
            <?php } ?>
            <div class="buttons" style="float: right; margin-left: 10px;">
                <button type="button" class="positive btnRefreshQuotation">
                    <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                    <?php echo ACTION_REFRESH; ?>
                </button>
            </div>
            <div style="float:right;">
                <label for="changeDateQuotation"><?php echo TABLE_DATE; ?> :</label>
                <input type="text" id="changeDateQuotation" style="width: 115px; height: 25px;" readonly="readonly" /> 
                <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateQuotation" />
                <label for="changeCusQuotation"><?php echo TABLE_CUSTOMER; ?> :</label>
                <input type="hidden" id="changeCustomerIdQuotation" value="all" />
                <input type="text" id="changeCusQuotation" style="width: 250px; height: 25px;" />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Customer')" id="clearCusQuotation" />
                <label for="changeStatusQuotation"><?php echo TABLE_STATUS; ?> :</label>
                <select id="changeStatusQuotation" style="width: 130px; height: 30px;">
                    <option value="all"><?php echo TABLE_ALL; ?></option>
                    <option value="0"><?php echo ACTION_OPEN; ?></option>
                    <option value="1"><?php echo ACTION_CLOSE; ?></option>
                </select>
                <label for="changeApproveQuotation"><?php echo ACTION_APPROVE; ?> :</label>
                <select id="changeApproveQuotation" style="width: 130px; height: 30px;">
                    <option value="all"><?php echo TABLE_ALL; ?></option>
                    <option value="0"><?php echo ACTION_NO; ?></option>
                    <option value="1"><?php echo ACTION_YES; ?></option>
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
                    <th style="width: 150px !important;"><?php echo TABLE_QUOTATION_DATE; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_QUOTATION_NUMBER; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_CUSTOMER_NUMBER; ?></th>
                    <th style="width: 150px !important;"><?php echo TABLE_CUSTOMER_NAME; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TOTAL_DEPOSIT; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_CLOSE; ?></th>
                    <th style="width: 80px !important;"><?php echo ACTION_APPROVE; ?></th>
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
    <?php if ($allowAdd) { ?>
        <div style="padding: 5px;border: 1px dashed #bbbbbb;">
            <div class="buttons">
                <a href="" class="positive btnAddQuotation">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                    <?php echo MENU_QUOTATION_ADD; ?>
                </a>
            </div>
            <div style="clear: both;"></div>
        </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>