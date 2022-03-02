<?php
// Authentication
$this->element('check_access');
$allowAdd=checkAccess($user['User']['id'], $this->params['controller'], 'addAll');

$rnd = rand();
$company = "company" . $rnd;
$class = "class" . $rnd;
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
    var oTableGeneralLedgerAll;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // close conflict tab(s)
        $('#tabs a').not("[href=#]").each(function() {
            if($.data(this, 'href.tabs')=="<?php echo $this->base; ?>/general_ledgers/index"){
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
        oTableGeneralLedgerAll = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base.'/'.$this->params['controller']; ?>/ajaxAll/",
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
                $(".btnViewJournalEntryAll").click(function(event){
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
                $(".btnPrintJournalEntryAll").click(function(event){
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
                $(".btnNoteJournalEntryAll").click(function(event){
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
                $(".btnEditJournalEntryAll").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var leftPanel=$(this).parent().parent().parent().parent().parent().parent().parent();
                    var rightPanel=leftPanel.parent().find(".rightPanel");
                    leftPanel.hide("slide", { direction: "left" }, 500, function() {
                        rightPanel.show();
                    });
                    rightPanel.html("<?php echo ACTION_LOADING; ?>");
                    rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/editAll/" + id);
                });
                $(".btnDeleteJournalEntryAll").click(function(event){
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
                                        oTableGeneralLedgerAll.fnDraw(false);
                                        // alert message
                                        if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                                            createSysAct('Journal Entry Supervisor', 'Delete', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                        }else {
                                            createSysAct('Journal Entry Supervisor', 'Delete', 1, '');
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
                $(".btnChangeStatusJournal").click(function(event){
                    event.preventDefault();
                    var id = $(this).attr('rel');
                    var name = $(this).attr('name');
                    var status = $(this).attr('status')==1?0:1;
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_CHANGE_STATUS; ?> <b>' + name + '</b>?</p>');
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
                            '<?php echo ACTION_OK; ?>': function() {
                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/status/" + id + "/" + status,
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
                "bSortable": false, "aTargets": [ 0,-1,-3,-4,-5,-6,-7 ]
            }],
            "aaSorting": [[ 1, "desc" ]]
        });
        $(".btnAddJournalEntryAll").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/addAll/");
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
                var Tablesetting = oTableGeneralLedgerAll.fnSettings();
                $("#<?php echo $dateFrom; ?>").val($.trim($("#<?php echo $dateFrom; ?>").val()));
                $("#<?php echo $dateTo; ?>").val($.trim($("#<?php echo $dateTo; ?>").val()));
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "yy-mm-dd");
                Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajaxAll/" + $("#<?php echo $dateFrom; ?>").val() + "/" + $("#<?php echo $dateTo; ?>").val() + "/" + $("#<?php echo $status; ?>").val() + "/" + $("#<?php echo $createdBy; ?>").val()+ "/" + $("#<?php echo $company; ?>").val()+ "/" + $("#<?php echo $class; ?>").val();
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "dd/mm/yy");
                oCache.iCacheLower = -1;
                oTableGeneralLedgerAll.fnDraw(false);
            }
        });
    });
    // when tab selected
    var index<?php echo $tblName; ?>Tab  = $('#tabs .ui-tabs-selected').index();
    var tab<?php echo $tblName; ?>Name = $("#tabs li").eq(index<?php echo $tblName; ?>Tab).find('a').attr('href');
    var tab<?php echo $tblName; ?>Select =  $("a[href='"+tab<?php echo $tblName; ?>Name+"']");
    tab<?php echo $tblName; ?>Select.bind("click", function(){
        oCache.iCacheLower = -1;
        oTableGeneralLedgerAll.fnDraw(false);
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
        <?php if($allowAdd){ ?>
        <div class="buttons">
            <a href="" class="positive btnAddJournalEntryAll">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ADD; ?>
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
                <td><label for="<?php echo $class; ?>"><?php echo TABLE_CLASS; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <select id="<?php echo $class; ?>" style="width: 130px;">
                            <option value="all">All</option>
                            <?php
                            $query[0]=mysql_query("SELECT id, name FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data[0]=mysql_fetch_array($query[0])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[0]['id']);
                            ?>
                            <option value="<?php echo $data[0]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
                                <?php
                                $query[1]=mysql_query("SELECT id, name FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                while($data[1]=mysql_fetch_array($query[1])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[1]['id']);
                                ?>
                                <option value="<?php echo $data[1]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                    <?php
                                    $query[2]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                    while($data[2]=mysql_fetch_array($query[2])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[2]['id']);
                                    ?>
                                    <option value="<?php echo $data[2]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                        <?php
                                        $query[3]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                        while($data[3]=mysql_fetch_array($query[3])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[3]['id']);
                                        ?>
                                        <option value="<?php echo $data[3]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                            <?php
                                            $query[4]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                            while($data[4]=mysql_fetch_array($query[4])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[4]['id']);
                                            ?>
                                            <option value="<?php echo $data[4]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                <?php
                                                $query[5]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                                while($data[5]=mysql_fetch_array($query[5])){
                                                    $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) AND parent_id=".$data[5]['id']);
                                                ?>
                                                <option value="<?php echo $data[5]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
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
    <?php if($allowAdd){ ?>
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div class="buttons">
            <a href="" class="positive btnAddJournalEntryAll">
                <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT_ADD; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php } ?>
</div>
<div class="rightPanel"></div>