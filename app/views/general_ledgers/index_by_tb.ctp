<?php
// Authentication
$this->element('check_access');
$allowEditAll = checkAccess($user['User']['id'], $this->params['controller'], 'editAll');

$rnd = rand();
$body   = "body" . $rnd;
$oTable = "oTable" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$status = "status" . $rnd;
$createdBy = "createdBy" . $rnd;
$btnGo = "btnGo" . $rnd;
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // date
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $dateFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        <?php echo $oTable; ?> = $("#<?php echo $tblName; ?>").dataTable({
            "aLengthMenu": [[50, 100, 500, 1000, 5000, 10000, 1000000*1000000], [50, 100, 500, 1000, 5000, 10000, "All"]],
            "iDisplayLength": 1000000*1000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajaxByTb/<?php echo $chart_account_id; ?>/<?php echo $dateAsOf; ?>/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $customerId; ?>/<?php echo $vendorId; ?>/<?php echo $otherId; ?>/<?php echo $classId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(7)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                <?php
                if(!empty($title)){
                ?>
                var rowLength = $("#<?php echo $tblName; ?> th").length;
                $("#<?php echo $body; ?>").prepend('<tr><td colspan="'+rowLength+'"><span style="font-size: 15px; font-weight: bold;"><?php echo $title; ?></span></td></tr>');
                <?php
                }
                ?>
                $(".btnViewJournalEntry").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/view/" + id);
                });
                $(".btnPrintJournalEntry").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var url = '';
                    if($(this).attr('type')=='Check'){
                        url = "<?php echo $this->base . '/'; ?>general_ledgers/printCheck/"+id;
                    }else if($(this).attr('type')=='Deposit'){
                        url = "<?php echo $this->base . '/'; ?>general_ledgers/printDeposit/"+id;
                    }else{
                        url = "<?php echo $this->base . '/'; ?>general_ledgers/printJournal/"+id;
                    }
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
                $(".btnEditJournalEntry").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/<?php echo $allowEditAll?'editAll':'edit'; ?>/" + id);
                });
                $(".btnDeleteJournalEntry").click(function(event){
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
                                        <?php echo $oTable; ?>.fnDraw(false);
                                        // alert message
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
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2,-3,-4,-5,-6,-7 ]
            }],
            "aaSorting": [[ 1, "asc" ]]
        });
        $(".btnAddJournalEntry").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        $("#<?php echo $btnGo; ?>").click(function(event){
            if($("#<?php echo $dateFrom; ?>").val()=="" || $("#<?php echo $dateTo; ?>").val()==""){
                $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please select date from & date to!</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_WARNING; ?>',
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
            }else{
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                var Tablesetting = <?php echo $oTable; ?>.fnSettings();
                $("#<?php echo $dateFrom; ?>").val($.trim($("#<?php echo $dateFrom; ?>").val()));
                $("#<?php echo $dateTo; ?>").val($.trim($("#<?php echo $dateTo; ?>").val()));
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "yy-mm-dd");
                Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajaxByTb/<?php echo $chart_account_id; ?>/<?php echo $dateAsOf; ?>/<?php echo $companyId; ?>/<?php echo $customerId; ?>/<?php echo $vendorId; ?>/<?php echo $otherId; ?>/<?php echo $classId; ?>" + "/" + $("#<?php echo $dateFrom; ?>").val() + "/" + $("#<?php echo $dateTo; ?>").val() + "/" + $("#<?php echo $status; ?>").val() + "/" + $("#<?php echo $createdBy; ?>").val();
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "dd/mm/yy");
                oCache.iCacheLower = -1;
                <?php echo $oTable; ?>.fnDraw(false);
            }
        });
    });
</script>
<style type="text/css">
    #<?php echo $tblName; ?> th{
        vertical-align: top;
        padding: 10px;
    }
    #<?php echo $tblName; ?> td{
        vertical-align: top;
        padding: 10px;
    }
</style>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div style="float:right;">
            <table>
                <td><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td><label for="<?php echo $status; ?>"><?php echo TABLE_STATUS; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <select id="<?php echo $status; ?>" name="status">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <option value="0">Not Approve</option>
                            <option value="1">Approve</option>
                        </select>
                    </div>
                </td>
                <td><label for="<?php echo $createdBy; ?>"><?php echo TABLE_CREATED_BY; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <select id="<?php echo $createdBy; ?>" name="status">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <?php
                            $queryUser=mysql_query("SELECT id,CONCAT_WS(' ',first_name,last_name) AS full_name FROM users WHERE is_active=1 ORDER BY full_name");
                            while($dataUser=mysql_fetch_array($queryUser)){
                            ?>
                            <option value="<?php echo $dataUser['id']; ?>"><?php echo $dataUser['full_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </td>
                <td><input type="button" id="<?php echo $btnGo; ?>" value="Go" /></td>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 180px !important;"><?php echo TABLE_CREATED_BY; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 40px !important;"><?php echo TABLE_ADJUST; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_ACCOUNT; ?></th>
                    <th><?php echo TABLE_MEMO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_CLASS; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody id="<?php echo $body; ?>">
                <tr>
                    <td colspan="12" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="rightPanel"></div>