<?php
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowPrint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowApprove = checkAccess($user['User']['id'], $this->params['controller'], 'approve');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableInvAdj;
    var tabInvAdjId  = $(".ui-tabs-selected a").attr("href");
    var tabInvAdjReg = '';
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableInvAdj = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                <?php
                if($allowPrint){
                ?>
                $(".btnPrintInvAdj").unbind("click").click(function(event){
                    event.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/'; ?>inv_adjs/printInvoice/"+$(this).attr("rel"),
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
                if($allowEdit){
                ?>
                $(".btnEditInvAdj").unbind("click").click(function(event){
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
                $(".btnDeleteInvAdj").unbind("click").click(function(event){
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
                                    url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/delete/" + id,
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableInvAdj.fnDraw(false);
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
                $(".btnApproveInvAdj").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var invAdjTimer;
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DO_YOU_TO_APPROVE; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_NO; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_YES; ?>': function() {
                                $(this).dialog("close");
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: "<?php echo $this->base . '/'; ?>inv_adjs/approve/",
                                    data: "cycle_product_id=" + id,
                                    beforeSend: function(){
                                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        // modal box - open
                                        $("#dialogModal").html('<p style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" /></p>');
                                        $("#dialogModal").dialog({
                                            title: '<?php echo DIALOG_LOADING; ?>',
                                            resizable: false,
                                            modal: true,
                                            closeOnEscape: false,
                                            width: 180,
                                            height: 80,
                                            open: function(event, ui){
                                                $(".ui-dialog-buttonpane").show();
                                                $(".ui-dialog-titlebar-close").hide();
                                            },
                                            close: function(event, ui){
                                                $(".ui-dialog-titlebar-close").show();
                                            },
                                            buttons: {

                                            }
                                        });
                                    },
                                    success: function(result){
                                        if(result.error == 1){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            $("#dialogModal").dialog("close");
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>path.php not config properly... Please recheck it again.</p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        } else {
                                            invAdjTimer = setInterval(function(){
                                                $.ajax({
                                                    type: "POST",
                                                    url: "<?php echo $this->base . '/'; ?>users/checkInvAdj/" + result.id,
                                                    data: "",
                                                    beforeSend: function(){

                                                    },
                                                    success: function(cycleProductStatus){
                                                        if(cycleProductStatus==0){
                                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                            // modal box - close
                                                            $("#dialogModal").dialog("close");
                                                            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Out of Stock</p>');
                                                            $("#dialog").dialog({
                                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                                resizable: false,
                                                                modal: true,
                                                                width: 'auto',
                                                                height: 'auto',
                                                                open: function(event, ui){
                                                                    $(".ui-dialog-buttonpane").show();
                                                                },
                                                                buttons: {
                                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                                        $(this).dialog("close");
                                                                    }
                                                                }
                                                            });
                                                            clearInterval(invAdjTimer);
                                                        }else if(cycleProductStatus==2){
                                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                            // modal box - close
                                                            $("#dialogModal").dialog("close");
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "<?php echo $this->base . '/'; ?>inv_adjs/printInvoice/"+id,
                                                                beforeSend: function(){
                                                                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                                                },
                                                                success: function(printInvoiceResult){
                                                                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                                    w=window.open();
                                                                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                                                                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                                                                    w.document.write(printInvoiceResult);
                                                                    w.document.close();
                                                                    oCache.iCacheLower = -1;
                                                                    oTableInvAdj.fnDraw(false);
                                                                }
                                                            });
                                                            clearInterval(invAdjTimer);
                                                        }
                                                    }
                                                });
                                            },5*1000);
                                        }
                                    }
                                });
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
                "bSortable": false, "aTargets": [ 0,-1 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
        });
        <?php if($allowAdd){ ?>
        $(".btnAddInvAdj").unbind("click").click(function(event){
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
                
        $("#changeCompanyInvAdj").children("select option[value='']").attr('value','all');
        $("#changeWarehouseInvAdj").children("select option[value='']").attr('value','all');

        $('#changeDateInvAdj').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#changeStatusInvAdj, #changeWarehouseInvAdj, #changeDateInvAdj").unbind("change").change(function(){
            filterInvAdj();
        });
        
        $("#clearDateInvAdj").unbind("click").click(function(){
            $('#changeDateInvAdj').val('');
            filterInvAdj();
        });
        
        $(".btnRefreshInvAdj").unbind("click").click(function(){
            filterInvAdj();
        });
        
    });
    
    function filterInvAdj(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDateInvAdj").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTableInvAdj.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeWarehouseInvAdj").val()+"/"+$("#changeStatusInvAdj").val()+"/"+$("#changeDateInvAdj").val();
        oCache.iCacheLower = -1;
        oTableInvAdj.fnDraw(false);
        $("#changeDateInvAdj").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddInvAdj">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_INVENTORY_ADJUSTMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshInvAdj">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateInvAdj" style="width: 115px; height: 25px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateInvAdj" />
            <?php echo TABLE_LOCATION_GROUP; ?> :
            <?php echo $this->Form->select('location_group_id', $locationGroups, null, array('escape' => false, 'id' => 'changeWarehouseInvAdj', 'empty' => TABLE_ALL, 'style' => 'width: 170px; height: 30px;')); ?>
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusInvAdj" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="0">Void</option>
                <option value="1">Issued</option>
                <option value="2">Fulfilled</option>
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
                    <th style="width: 160px !important;"><?php echo TABLE_ADJ_NO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
                    <th style="width: 160px !important;"><?php echo TABLE_CREATED; ?></th>
                    <th><?php echo TABLE_CREATED_BY; ?></th>
                    <th style="width: 100px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 100px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>
            </tbody>
        </table>
    </div>
    <br />
	
    <br />
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddInvAdj">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_INVENTORY_ADJUSTMENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>