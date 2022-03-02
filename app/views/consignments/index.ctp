<?php
// Authentication
$this->element('check_access');
$allowAdd  = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowReprint = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$allowVoid    = checkAccess($user['User']['id'], $this->params['controller'], 'void');
$allowReceive = checkAccess($user['User']['id'], $this->params['controller'], 'receive');
$tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableConsignment;
    var tabConsignmentId  = $(".ui-tabs-selected a").attr("href");
    var tabConsignmentReg = '';
    $(document).ready(function(){
        oTableConsignment = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewConsignment").click(function(event){
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
                $(".btnVoidConsignment").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    voidConsignment(id, name);
                });
                <?php 
                }
                if ($allowEdit) {
                ?>
                $(".btnEditConsignment").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel  = $(".btnEditConsignment").parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel = leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/"+id);
                    
                });
                <?php 
                }
                if ($allowReceive) {
                ?>
                // Action Approve
                $(".btnConsignmentReceive").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/receive/" + id);
                });
                <?php 
                }
                if ($allowReprint) {
                ?>
                // Action Reprint Invoice
                $(".btnReprintInvoice").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . "consignments"; ?>/printInvoice/"+id,
                        beforeSend: function(){
                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printInvoiceResult){
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                            w = window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printInvoiceResult);
                            w.document.close();
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
                "bSortable": false, "aTargets": [ -1 ]
            }],
            "aaSorting": [[ 0, "desc" ]]
        });
        
        
        $('#changeDateCusConsign').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");

        $("#changeStatusSO, #changeDateCusConsign").change(function(){
            resetFilterCusConsign();
        });
        
        $("#clearDateCusConsign").click(function(){
            $('#changeDateCusConsign').val('');
            resetFilterCusConsign();
        });
        
        $("#changeCusCusConsign").autocomplete("<?php echo $this->base ."/reports/searchCustomer"; ?>", {
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
            $("#changeCustomerIdCusConsign").val(value.toString().split(".*")[0]);
            $("#changeCusCusConsign").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]).attr("readonly", true);
            $("#clearCusCusConsign").show();
            resetFilterCusConsign();
        });
        
        $("#clearCusCusConsign").click(function(){
            $("#changeCustomerIdCusConsign").val("all");
            $("#changeCusCusConsign").val("");
            $("#changeCusCusConsign").removeAttr("readonly");
            $("#clearCusCusConsign").hide();
            resetFilterCusConsign();
        });
        <?php 
        if ($allowAdd) {
        ?>
        $(".btnAddConsignment").click(function(event){
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
    
    function resetFilterCusConsign(){
        $("#changeDateCusConsign").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTableConsignment.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeCustomerIdCusConsign").val()+"/"+$("#changeStatusSO").val()+"/"+$("#changeDateCusConsign").val();
        oCache.iCacheLower = -1;
        oTableConsignment.fnDraw(false);
        $("#changeDateCusConsign").datepicker("option", "dateFormat", "dd/mm/yy");
    }
    <?php
    if ($allowVoid) {
    ?>
    function voidConsignment(id, name){
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
                            oTableConsignment.fnDraw(false);
                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                createSysAct('Consignment', 'Void', 2, result);
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                            }else {
                                createSysAct('Consignment', 'Void', 1, '');
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
                <a href="" class="positive btnAddConsignment">
                    <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt="" />
                <?php echo MENU_CUSTOMER_CONSIGNMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <div style="float:right;">
            <input type="hidden" id="changeCompanySO" value="1" />
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDateCusConsign" style="width: 115px; height: 20px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDateCusConsign" />
            <label for="changeCusCusConsign"><?php echo TABLE_CUSTOMER; ?> :</label>
            <input type="hidden" id="changeCustomerIdCusConsign" value="all" />
            <input type="text" id="changeCusCusConsign" style="width: 250px; height: 20px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Customer')" id="clearCusCusConsign" />
            <?php echo TABLE_STATUS; ?> :
            <select id="changeStatusSO" style="width: 130px; height: 25px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="1">Issued</option>
                <option value="2">Fulfilled</option>
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
                    <th style="width: 120px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_CONSIGNMENT_CODE; ?></th>
                    <th><?php echo TABLE_CUSTOMER_NAME; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                    <th style="width: 120px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
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
            <a href="" class="positive btnAddConsignment">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_CUSTOMER_CONSIGNMENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>