<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowExport=checkAccess($user['User']['id'], $this->params['controller'], 'exportExcel');
?>
<?php $tblName = "tblSR" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTablePickSlip;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTablePickSlip = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/"+$("#changeBranchPickSlipSR").val()+"/"+$("#changeCustomerIdPickSlipSR").val()+"/"+$("#changeStatusPickSlipSR").val()+"/"+$("#changeDatePickSlipSR").val(),
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnViewPickSlipSR").click(function(event){
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
                $(".btnPickPickSlipSR").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/pick/" + id);
                });
                // Action Reprint Invoice
                $(".btnPrintPickSlipSR").unbind("click").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var url = "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoicePickSlip/";
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive reprintInvoiceSales" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtReprintInvoiceSales"><?php echo ACTION_PRINT_DELIVERY_NOTE; ?></span></button></div>');
                    $(".reprintInvoiceSales").click(function(){
                        $.ajax({
                            type: "POST",
                            url: url+id,
                            beforeSend: function(){
                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                            },
                            success: function(printInvoiceResult){
                                w = window.open();
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
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1 ]
            }]
        });

        $('#changeDatePickSlipSR').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");

        $("#changeBranchPickSlipSR, #changeCustomerIdPickSlipSR, #changeStatusPickSlipSR, #changeDatePickSlipSR").unbind("change").change(function(){
            resetFilterPickSlip();
        });
        
        $("#clearDatePickSlipSR").unbind("click").click(function(){
            $('#changeDatePickSlipSR').val('');
            resetFilterPickSlip();
        });
        
        $("#changeCusPickSlipSR").autocomplete("<?php echo $this->base ."/reports/searchCustomer"; ?>", {
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
            $("#changeCustomerIdPickSlipSR").val(value.toString().split(".*")[0]);
            $("#changeCusPickSlipSR").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]).attr("readonly", true);
            $("#clearCusPickSlipSR").show();
            resetFilterPickSlip();
        });

        $("#clearCusPickSlipSR").unbind("click").click(function(){
            $("#changeCustomerIdPickSlipSR").val("all");
            $("#changeCusPickSlipSR").val("");
            $("#changeCusPickSlipSR").removeAttr("readonly");
            $("#clearCusPickSlipSR").hide();
            resetFilterPickSlip();
        });

        $(".btnRefreshPickSlipSR").unbind("click").click(function(){
            resetFilterPickSlip();
        });

        $("#changeStatusPickSlipSRSR").unbind("change").change(function(){
            resetFilterPickSlip();
        });
    });

    function resetFilterPickSlip(){
        $("#<?php echo $tblName; ?>").find("tbody").html('<tr><td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td></tr>');
        $("#changeDatePickSlipSR").datepicker("option", "dateFormat", "yy-mm-dd");
        var Tablesetting = oTablePickSlip.fnSettings();
        Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#changeBranchPickSlipSR").val()+"/"+$("#changeCustomerIdPickSlipSR").val()+"/"+$("#changeStatusPickSlipSR").val()+"/"+$("#changeDatePickSlipSR").val();
        oCache.iCacheLower = -1;
        oTablePickSlip.fnDraw(false);
        $("#changeDatePickSlipSR").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons" style="float: right; margin-left: 10px;">
            <button type="button" class="positive btnRefreshPickSlipSR">
                <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                <?php echo ACTION_REFRESH; ?>
            </button>
        </div>
        <div style="float:right;">
            <?php echo TABLE_BRANCH; ?> :
            <select id="changeBranchPickSlipSR" style="height: 33px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <?php 
                    $queryBrachFilter = mysql_query("SELECT id, name FROM branches WHERE id IN (SELECT branch_id FROM user_branches WHERE user_id = '".$user['User']['id']."')");
                    if(mysql_num_rows($queryBrachFilter)){
                        while($dataBranchFilter = mysql_fetch_array($queryBrachFilter)){
                ?>
                <option value="<?php echo $dataBranchFilter['id']; ?>"><?php echo $dataBranchFilter['name']; ?></option>
                <?php
                        }
                    }
                ?>
            </select>&nbsp;
            <?php echo TABLE_DATE; ?> :
            <input type="text" id="changeDatePickSlipSR" style="width: 115px; height: 25px;" readonly="readonly" /> <img alt="" src="<?php echo $this->webroot; ?>img/button/clear.png" style="cursor: pointer;" onmouseover="Tip('Clear Date')" id="clearDatePickSlipSR" />&nbsp;
            <label for="changeCusPickSlipSR"><?php echo TABLE_CUSTOMER; ?> :</label>
            <input type="hidden" id="changeCustomerIdPickSlipSR" value="all" />
            <input type="text" id="changeCusPickSlipSR" style="width: 250px; height: 25px;" />
            <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Customer')" id="clearCusPickSlipSR" />&nbsp;
            <label for="changeStatusPickSlipSR"><?php echo TABLE_STATUS; ?> :</label>
            <select id="changeStatusPickSlipSR" style="width: 130px; height: 30px;">
                <option value="all"><?php echo TABLE_ALL; ?></option>
                <option value="2" selected=""><?php echo 'Issued'; ?></option>
                <option value="3"><?php echo 'Picked'; ?></option>
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
                    <th><?php echo TABLE_INVOICE_DATE; ?></th>
                    <th><?php echo TABLE_INVOICE_CODE; ?></th>
                    <th><?php echo TABLE_SALES_ORDER_DATE; ?></th>
                    <th><?php echo TABLE_SALES_ORDER_CODE; ?></th>
                    <th><?php echo TABLE_CREATED; ?></th>
                    <th><?php echo TABLE_CREATED_BY; ?></th>
                    <th><?php echo TABLE_STATUS; ?></th>
                    <th><?php echo ACTION_ACTION; ?></th>
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
</div>
<div class="rightPanel"></div>