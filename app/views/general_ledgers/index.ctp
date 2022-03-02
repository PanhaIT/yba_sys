<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'add');
$allowWriteChecks=checkAccess($user['User']['id'], $this->params['controller'], 'writeChecks');
$allowEnterBills=checkAccess($user['User']['id'], $this->params['controller'], 'enterBills');
$allowMakeDeposits=checkAccess($user['User']['id'], $this->params['controller'], 'makeDeposits');

$rnd = rand();
$company = "company".$rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$status = "status" . $rnd;
$btnGo = "btnGo" . $rnd;
?>
<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    var oTableGeneralLedger;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // close conflict tab(s)
        $('#tabs a').not("[href=#]").each(function() {
            if($.data(this, 'href.tabs')=="<?php echo $this->base; ?>/general_ledgers/indexAll"){
                $("#tabs").tabs("remove", $(this).attr("href"));
            }
        });
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
        oTableGeneralLedger = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajax/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(6)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");
                $("#<?php echo $tblName; ?> td:nth-child(11)").css("text-align", "center");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td").css("vertical-align", "top");
                $("#<?php echo $btnGo; ?>").removeAttr('disabled');
                $(".btnViewJournalEntry").click(function(event){
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
                $(".btnNoteJournalEntry").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var note = $(this).attr('note').replace(/{dblquote}/g,'"');
                    $("#dialog").html("<textarea style='width:350px; height: 200px;' id='NoteJournalEntryAll'>" + note + "</textarea>").dialog({
                        title: '<?php echo TABLE_NOTE; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveNote/" + id + "/" + $("#NoteJournalEntryAll").val(),
                                    data: "",
                                    beforeSend: function(){
                                        $("#dialog").dialog("close");
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                                    },
                                    success: function(result){
                                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                        oCache.iCacheLower = -1;
                                        oTableGeneralLedgerAll.fnDraw(false);
                                    }
                                });
                            }
                        }
                    });
                });
                $(".btnEditJournalEntry").click(function(event){
                    event.preventDefault();
                    var id   = $(this).attr('rel');
                    var isDp = $(this).attr('is-dp');
                    var url  = 'edit';
                    if(isDp != 0){
                        url  = 'editMakeDeposit';
                    }
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+url+"/"+ id);
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
                                        oTableGeneralLedger.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                            createSysAct('Journal Entry', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Journal Entry', 'Delete', 1, '');
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
                var totalDebit = 0;
                var totalCrebit = 0;
                $("#<?php echo $tblName; ?> tr:gt(0)").each(function(){
                    totalDebit += Number($(this).find("td:eq(9)").text().replace(/,/g, ""));
                    totalCrebit += Number($(this).find("td:eq(10)").text().replace(/,/g, ""));
                });
                $('#<?php echo $tblName; ?> > tbody:last').append('<tr><td class="first" style="text-align: left;font-weight: bold;" colspan="9"><?php echo strtoupper(TABLE_TOTAL); ?></td><td class="formatCurrency" style="text-align: right;">' + (totalDebit) + '</td><td class="formatCurrency" style="text-align: right;">' + (totalCrebit) + '</td><td></td><td></td></tr>');
                $('.formatCurrency').formatCurrency({colorize:true});
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-3,-4,-5,-6 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
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
        $(".btnWriteChecks").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/writeChecks/");
        });
        $(".btnEnterBills").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/enterBills/");
        });
        $(".btnMakeDeposits").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/makeDeposits/");
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
                $("#<?php echo $btnGo; ?>").attr('disabled','disabled');
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                var Tablesetting = oTableGeneralLedger.fnSettings();
                $("#<?php echo $dateFrom; ?>").val($.trim($("#<?php echo $dateFrom; ?>").val()));
                $("#<?php echo $dateTo; ?>").val($.trim($("#<?php echo $dateTo; ?>").val()));
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "yy-mm-dd");
                Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/" + $("#<?php echo $dateFrom; ?>").val() + "/" + $("#<?php echo $dateTo; ?>").val() + "/" + $("#<?php echo $status; ?>").val()+ "/" + $("#<?php echo $company; ?>").val();
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "dd/mm/yy");
                oCache.iCacheLower = -1;
                oTableGeneralLedger.fnDraw(false);
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
    <?php if($allowAdd || $allowWriteChecks || $allowEnterBills || $allowMakeDeposits){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddJournalEntry">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowWriteChecks){ ?>
        <div class="buttons">
            <a href="" class="positive btnWriteChecks">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_WRITE_CHECKS; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowEnterBills){ ?>
        <div class="buttons">
            <a href="" class="positive btnEnterBills">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ENTER_BILLS; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowMakeDeposits){ ?>
        <div class="buttons">
            <a href="" class="positive btnMakeDeposits">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_MAKE_DEPOSITS; ?>
            </a>
        </div>
        <?php } ?>
        <div style="float:right;">
            <table>
                <td><label for="<?php echo $company; ?>"><?php echo TABLE_COMPANY; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <select id="<?php echo $company; ?>" style="width: 130px;">
                           <option value="all">All</option>
                           <?php
                           $sql = mysql_query("SELECT id, name FROM companies WHERE id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") ORDER BY name ASC");
                           while($row=mysql_fetch_array($sql)){
                           ?>
                           <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                           <?php
                           }
                           ?>
                        </select>
                    </div>
                </td>
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
                        <select id="<?php echo $status; ?>" name="status" style="width: 130px;">
                            <option value="all"><?php echo TABLE_ALL; ?></option>
                            <option value="0">Not Approve</option>
                            <option value="1">Approve</option>
                        </select>
                    </div>
                </td>
                <td><input type="button" id="<?php echo $btnGo; ?>" value="Go" /></td>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table_report">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 60px !important;">Creator</th>
                    <th style="width: 60px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_REFERENCE; ?></th>
                    <th style="width: 40px !important;">Adj</th>
                    <th style="width: 50px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_ACCOUNT; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_CLASS; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_DEBIT; ?></th>
                    <th style="width: 120px !important;"><?php echo GENERAL_CREDIT; ?></th>
                    <th style="width: 50px !important;">Approve</th>
                    <th style="width: 80px !important;"><?php echo ACTION_ACTION; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="12" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
    <br />
    <?php if($allowAdd || $allowWriteChecks || $allowEnterBills || $allowMakeDeposits){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddJournalEntry">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowWriteChecks){ ?>
        <div class="buttons">
            <a href="" class="positive btnWriteChecks">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_WRITE_CHECKS; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowEnterBills){ ?>
        <div class="buttons">
            <a href="" class="positive btnEnterBills">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ENTER_BILLS; ?>
            </a>
        </div>
        <?php } ?>
        <?php if($allowMakeDeposits){ ?>
        <div class="buttons">
            <a href="" class="positive btnMakeDeposits">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_MAKE_DEPOSITS; ?>
            </a>
        </div>
        <?php } ?>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>