<?php

$rnd = rand();
$printArea = "printArea" . $rnd;
$cloneCorner = "cloneCorner" . $rnd;
$cloneTop = "cloneTop" . $rnd;
$cloneLeft = "cloneLeft" . $rnd;
$originTable = "originTable" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$btnShowAll = "btnShowAll" . $rnd;
$btnHideAll = "btnHideAll" . $rnd;
$btnPlusMinus = "btnPlusMinus" . $rnd;

$monthName=array(DATE_JAN, DATE_FEB, DATE_MAR, DATE_APR, DATE_MAY, DATE_JUN, DATE_JUL, DATE_AUG, DATE_SEP, DATE_OCT, DATE_NOV, DATE_DEC);
$emptyCell='0';

include('includes/function.php');
if(!empty($_POST['company_id'])){
    $companyId = implode(",", $_POST['company_id']);
    $companyJorunal = $companyId;
}else{
    $companyId = "SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'];
    $companyJorunal = "all";
}
if(!empty($_POST['branch_id'])){
    $branchId = $_POST['branch_id'];
    $branchJorunal = $branchId;
} else {
    $branchId = "SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'];
    $branchJorunal = "all";
}

$dateFrom = dateConvert($_POST['date_from']);
$dateTo = dateConvert($_POST['date_to']);

/**
 * condition for date
 */
$condition='';
$conditionBS='';
if($_POST['date_from']!='') {
    $condition.=' AND "'.$dateFrom.'" <= DATE(date)';
}
if($_POST['date_to']!='') {
    $condition.=' AND "'.$dateTo.'" >= DATE(date)';
    $conditionBS.=' AND "'.$dateTo.'" >= DATE(date)';
}

/**
 * export to excel
 */
$filename="public/report/cash_flow.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

/**
 * Accumulated Depreciation Asset
 */
$arrAccumulatedDepreciationAsset=array();
$queryAccumulatedDepreciationAsset=mysql_query('SELECT general_ledger_id FROM general_ledger_details WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id IN (SELECT id FROM chart_account_groups WHERE is_depreciation=1))');
while($dataAccumulatedDepreciationAsset=mysql_fetch_array($queryAccumulatedDepreciationAsset)){
    $arrAccumulatedDepreciationAsset[]=$dataAccumulatedDepreciationAsset['general_ledger_id'];
}

?>
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2glcf").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                var type=$(this).siblings("td:eq(0)").attr("type");
                var dateFrom=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateFrom");
                var dateTo=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateTo");
                var chart_account_group_id=$(this).siblings("td:eq(0)").attr("chart_account_group_id");
                var companyId='<?php echo $companyJorunal; ?>';
                var branchId='<?php echo $branchJorunal; ?>';
                if(chart_account_group_id){
                    if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            if(type=="as_of"){
                                $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroup/" + chart_account_group_id + "/" + dateTo + "/" + companyId + "/" + branchId +"?title=", "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                            }else{
                                $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroupDateRange/" + chart_account_group_id + "/" + dateFrom + "/" + dateTo + "/" + companyId + "/" + branchId +"?title=", "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                            }
                        });
                    }
                }
                // group expansion
                var chart_account_id=$(this).siblings("td:eq(0)").attr("chart_account_id");
                if(chart_account_id){
                    if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            if(type=="as_of"){
                                $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTb/" + chart_account_id + "/" + dateTo + "/" + companyId +"?title=", "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                            }else{
                                $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTbDateRange/" + chart_account_id + "/" + dateFrom + "/" + dateTo + "/" + companyId +"?title=", "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                            }
                        });
                    }
                }
            }
        });
        <?php
        if($_POST['empty'] == '1') {
        ?>
        // Hide empty row
        $("#<?php echo $printArea; ?> .table_report tr:gt(0)").each(function(){
            var attrChartAccountGroupId =$("td:eq(0)", this).attr('chart_account_group_id');
            var attrChartAccountId = $("td:eq(0)", this).attr('chart_account_id');
            if((typeof attrChartAccountGroupId !== 'undefined' && attrChartAccountGroupId !== false) || (typeof attrChartAccountId !== 'undefined' && attrChartAccountId !== false)){
                var notEmpty=0;
                $("td:gt(0)", this).each(function(){
                    if($(this).text()!="-"){
                        notEmpty=1;
                        return false;
                    }
                });
                if(notEmpty==0){
                    $(this).remove();
                }
            }
        });
        <?php
        }
        ?>

        // Clone
        var isCloneLeft=$("#<?php echo $originTable; ?> table").width()>$(".ui-layout-center").width();
        $("#<?php echo $cloneTop; ?>").html($("#<?php echo $originTable; ?>").html());
        $("#<?php echo $cloneTop; ?>").css("top", $("#<?php echo $originTable; ?>").css("top"));
        $("#<?php echo $cloneTop; ?>").css("left", $("#<?php echo $originTable; ?>").css("left"));
        if(!isCloneLeft){
            $("#<?php echo $cloneTop; ?>").css("width", $("#<?php echo $originTable; ?>").css("width"));
        }
        $("#<?php echo $cloneTop; ?>").css("height", $("#<?php echo $originTable; ?> tr:first-child th:first").outerHeight()+5);
        if(isCloneLeft){
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").html($("#<?php echo $originTable; ?>").html());
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("top", $("#<?php echo $originTable; ?>").css("top"));
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", $("#<?php echo $originTable; ?>").css("left"));
            $("#<?php echo $cloneCorner; ?>").css("width", $("#<?php echo $originTable; ?> tr:first-child th:first").outerWidth());
            $("#<?php echo $cloneCorner; ?>").css("height", $("#<?php echo $originTable; ?> tr:first-child th:first").outerHeight()+5);
            $("#<?php echo $cloneLeft; ?>").css("width", $("#<?php echo $originTable; ?> tr:first-child th:first").outerWidth());
        }
        // event scroll fire
        var timer;
        $(".ui-tabs-panel").scroll(function(){
            var obj=$(this);
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").hide();
            clearTimeout(timer);
            timer=setTimeout(function() {
                // scroll top
                if(obj.scrollTop()>275){
                    $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>").css("top", Number(obj.scrollTop()-275));
                }else{
                    $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>").css("top", $("#<?php echo $originTable; ?>").css("top"));
                }
                // scroll left
                if(isCloneLeft){
                    if($(".ui-layout-center").scrollLeft()==0){
                        var scrollLeft=obj.scrollLeft();
                        if(scrollLeft>20){
                            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", Number(scrollLeft-20));
                        }else{
                            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", $("#<?php echo $originTable; ?>").css("left"));
                        }
                    }else{
                        var scrollLeft=$(".ui-layout-center").scrollLeft();
                        if(scrollLeft>20){
                            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", Number(scrollLeft-32));
                        }else{
                            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", $("#<?php echo $originTable; ?>").css("left"));
                        }
                    }
                }
                $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").show();
            }, 100);
        });
        $(".ui-layout-center").scroll(function(){
            var obj=$(this);
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").hide();
            clearTimeout(timer);
            timer=setTimeout(function() {
                // scroll left
                if(isCloneLeft){
                    var scrollLeft=obj.scrollLeft();
                    if(scrollLeft>20){
                        $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", Number(scrollLeft-32));
                    }else{
                        $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneLeft; ?>").css("left", $("#<?php echo $originTable; ?>").css("left"));
                    }
                }
                $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").show();
            }, 100);
        });

        // Group expansion
        $("#<?php echo $printArea; ?> .group td:first-child").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $printArea; ?> .group td:first-child").css("cursor", "pointer");
        $("#<?php echo $printArea; ?> .groupDetail").css("background", "#EEE");
        $("#<?php echo $printArea; ?> .group td:first-child:not([mode='dr'])").click(function(){
            if($("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id=" + $(this).attr("chart_account_group_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id="+$(this).attr("chart_account_group_id")+"]").toggle();
        });
        $("#<?php echo $printArea; ?> .group td:first-child:[mode='dr']").click(function(){
            if($("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id_dr=" + $(this).attr("chart_account_group_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id_dr="+$(this).attr("chart_account_group_id")+"]").toggle();
        });
        // Buttion Show All Row
        $(".<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").show();
        });
        // Buttion Hide All Row
        $(".<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").hide();
        });
        // Button Print
        $("#<?php echo $btnPrint; ?>").click(function(){
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").hide();
            $(".<?php echo $btnShowAll; ?>").hide();
            $(".<?php echo $btnHideAll; ?>").hide();
            $(".<?php echo $btnPlusMinus; ?>").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".<?php echo $btnShowAll; ?>").show();
            $(".<?php echo $btnHideAll; ?>").show();
            $(".<?php echo $btnPlusMinus; ?>").show();
        });
        // Button Export
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/cash_flow.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_CASH_FLOW . '</b><br /><br />';
    $excelContent .= MENU_CASH_FLOW."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    if(!empty($_POST['company_id']) || $_POST['customer_id']!='' || $_POST['vendor_id']!='' || $_POST['other_id']!='' || $_POST['class_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if(!empty($_POST['company_id'])){
        $index = 0;
        $companyName = "";
        $query=mysql_query("SELECT name FROM companies WHERE id IN (".$companyId.")");
        while($data=mysql_fetch_array($query)){
            if($index > 0){
                $companyName .= " & ";
            }
            $companyName .= $data['name'];
            $index++;
        }
        $msg .= '<b>' . TABLE_COMPANY . '</b>: ' . $companyName."<br/>";
        $excelContent .= TABLE_COMPANY . ": " . $companyName."\n";
    }
    if(!empty($_POST['branch_id'])){
        $query=mysql_query("SELECT name FROM branches WHERE id = ".$branchId);
        $data=mysql_fetch_array($query);
        $msg .= '<b>' . TABLE_BRANCH . '</b>: ' . $data['name']."<br/>";
        $excelContent .= TABLE_BRANCH . ": " . $data['name']."\n";
    }
    if($_POST['customer_id']!='') {
        $query=mysql_query("SELECT CONCAT_WS(' ',firstname,lastname) FROM customers WHERE id=".$_POST['customer_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_CUSTOMER . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_CUSTOMER . ": " . $data[0]."\n";
    }
    if($_POST['vendor_id']!='') {
        $query=mysql_query("SELECT name FROM vendors WHERE id=".$_POST['vendor_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_VENDOR . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_VENDOR . ": " . $data[0]."\n";
    }
    if($_POST['other_id']!='') {
        $query=mysql_query("SELECT name FROM others WHERE id=".$_POST['other_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_OTHER . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_OTHER . ": " . $data[0]."\n";
    }
    if($_POST['class_id']!='') {
        $query=mysql_query("SELECT name FROM classes WHERE id=".$_POST['class_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_CLASS . '</b>: ' . $data[0];
        $excelContent .= " " . TABLE_CLASS . ": " . $data[0];
    }
    $msg .= '<br /><br />';
    $excelContent .= "\n\n";
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div style="position: relative;">
        <div id="<?php echo $cloneCorner; ?>" style="position: absolute;overflow: hidden;z-index: 1001;background: #FFF;"></div>
        <div id="<?php echo $cloneTop; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $cloneLeft; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $originTable; ?>">
            <table class="table_report">
                <tr>
                    <th class="first" style="text-align: left;">
                        <a href="" class="<?php echo $btnHideAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' onmouseover="Tip('Hide All')" /></a>
                        <a href="" class="<?php echo $btnShowAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/minus.gif' onmouseover="Tip('Show All')" /></a>
                    </th>
                    <?php
                    $count=0;
                    $incYear=0;
                    $d1=date_parse_from_format('d/m/Y', $_POST['date_from']);
                    $d2=date_parse_from_format('d/m/Y', $_POST['date_to']);
                    $sql="SELECT ";
                    $sqlDetail="SELECT ";
                    $sqlDebit="SELECT ";
                    $sqlDebitDetail="SELECT ";
                    $sqlCredit="SELECT ";
                    $sqlCreditDetail="SELECT ";
                    $sqlBS="SELECT ";
                    $sqlBSDetail="SELECT ";
                    if($_POST['columns']!=0){
                        for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                            $month=$i-($incYear*12);
                            $year=$d1['year']+$incYear;
                            $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlDebit.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlDebitDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlCredit.="IFNULL((SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlCreditDetail.="IFNULL((SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlBS.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$conditionBS.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$conditionBS."),0),";
                            $sqlBSDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$conditionBS.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$conditionBS."),0),";
                            if($year==$d1['year'] && $month==$d1['month']){
                                $bDay = $d1['day'];
                            }else{
                                $bDay = '01';
                            }
                            if($year==$d2['year'] && $month==$d2['month']){
                                $daysInMonth = $d2['day'];
                            }else{
                                $daysInMonth = days_in_month($month, $year);
                            }
                    ?>
                    <th style="text-align: center;" dateFrom="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo $bDay; ?>" dateTo="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo $daysInMonth; ?>">
                        <?php
                        echo $monthName[$month-1] . '/' . $year;
                        $excelContent .= "\t".$monthName[$month-1].'/'.$year;
                        if($i%12==0)$incYear++;
                        $count++;
                        ?>
                    </th>
                    <?php
                        }
                    }
                    $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlDebit.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlDebitDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlCredit.="IFNULL((SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlCreditDetail.="IFNULL((SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlBS.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$conditionBS.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$conditionBS."),0)";
                    $sqlBSDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$conditionBS.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$conditionBS."),0)";

                    $excelContent .= "\t".TABLE_TOTAL;
                    ?>
                    <th style="text-align: center;" dateFrom="<?php echo $d1['year']; ?>-<?php echo str_pad($d1['month'], 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d1['day'], 2, '0', STR_PAD_LEFT); ?>" dateTo="<?php echo $d2['year']; ?>-<?php echo str_pad($d2['month'], 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d2['day'], 2, '0', STR_PAD_LEFT); ?>"><?php echo TABLE_TOTAL; ?></th>
                </tr>
                <?php
                for($i=0;$i<=$count;$i++){
                    $totalDepreciation[$i]=0;
                    $totalCurrentAsset[$i]=0;
                    $totalCurrentLiability[$i]=0;
                    $totalOperatingActivity[$i]=0;

                    $totalFixedAsset[$i]=0;
                    $totalOtherAsset[$i]=0;
                    $totalInvestingActivity[$i]=0;

                    $totalLongTermLiability[$i]=0;
                    $totalEquity[$i]=0;
                    $totalFinancingActivity[$i]=0;

                    $totalActivity[$i]=0;
                    $totalBeginning[$i]=0;
                    $totalEnding[$i]=0;

                    $totalCashAndBank[$i]=0;
                }

                $excelContent .= "\n"."OPERATING ACTIVITIES"."\n"."Net Income";
                ?>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-left: 0px;border-right: 0px;"><i style="font-size: 18px;">OPERATING ACTIVITIES</i></td></tr>
                <tr>
                    <td class="first" style="white-space: nowrap;">Net Income</td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalRevenue[$i]=0;
                        $totalCOGS[$i]=0;
                        $totalGrossProfit[$i]=0;
                        $totalExpense[$i]=0;
                        $totalProfitLoss[$i]=0;
                    }
                    $sqlGroupIncome="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Income','Other Income')
                                        ORDER BY t.id";
                    $queryGroupIncome=mysql_query($sqlGroupIncome);
                    while($dataGroupIncome=mysql_fetch_array($queryGroupIncome)){
                        $query=mysql_query(str_replace("|||", $dataGroupIncome['id'], $sql));
                        $data=mysql_fetch_array($query);
                        for($i=0;$i<=$count;$i++){
                            if($data[$i]!=0 && $data[$i]!='')
                                $data[$i]*=-1;
                            $totalRevenue[$i]+=$data[$i];
                        }
                    }
                    $sqlGroupCOGS=" SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Cost of Goods Sold')
                                    ORDER BY t.id";
                    $queryGroupCOGS=mysql_query($sqlGroupCOGS);
                    while($dataGroupCOGS=mysql_fetch_array($queryGroupCOGS)){
                        $query=mysql_query(str_replace("|||", $dataGroupCOGS['id'], $sql));
                        $data=mysql_fetch_array($query);
                        for($i=0;$i<=$count;$i++){
                            $totalCOGS[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$count;$i++){
                            $totalGrossProfit[$i]=$totalRevenue[$i]-$totalCOGS[$i];
                        }
                    }
                    $sqlGroupExpense="  SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Expense','Other Expense')
                                        ORDER BY t.id";
                    $queryGroupExpense=mysql_query($sqlGroupExpense);
                    while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
                        $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                        $data=mysql_fetch_array($query);
                        for($i=0;$i<=$count;$i++){
                            $totalExpense[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$count;$i++){
                            $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                        }
                    }
                    for($i=0;$i<=$count;$i++){
                        $totalOperatingActivity[$i]+=$totalProfitLoss[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                $excelContent .= "\n"."Adjustment for non-cash incomes and expenses";
                ?>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;"><b>Adjustment for non-cash incomes and expenses</b></td></tr>
                <?php
                $sqlGroupDepreciation=" SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND g.is_depreciation=1
                                        ORDER BY t.id";
                $queryGroupDepreciation=mysql_query($sqlGroupDepreciation);
                while($dataGroupDepreciation=mysql_fetch_array($queryGroupDepreciation)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupDepreciation['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupDepreciation['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupDepreciation['id']; ?>"><?php echo $dataGroupDepreciation['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupDepreciation['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalDepreciation[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupDepreciation['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupDepreciation['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Changes in working capital";
                ?>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;"><b>Changes in working capital</b></td></tr>
                <?php
                $sqlGroupCurrentAsset=" SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Accounts receivable','Other Current Asset')
                                        ORDER BY t.id";
                $queryGroupCurrentAsset=mysql_query($sqlGroupCurrentAsset);
                while($dataGroupCurrentAsset=mysql_fetch_array($queryGroupCurrentAsset)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."(Increase)/Decrease in ".$dataGroupCurrentAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>">(Increase)/Decrease in <?php echo $dataGroupCurrentAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalCurrentAsset[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCurrentAsset['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupCurrentAsset['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $sqlGroupCurrentLiability=" SELECT g.id,g.name
                                            FROM chart_account_groups g
                                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                            WHERE g.is_active=1 AND t.name IN ('Accounts Payable','Credit Card','Other Current Liability')
                                            ORDER BY t.id";
                $queryGroupCurrentLiability=mysql_query($sqlGroupCurrentLiability);
                while($dataGroupCurrentLiability=mysql_fetch_array($queryGroupCurrentLiability)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."Increase/(Decrease) in ".$dataGroupCurrentLiability['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>">Increase/(Decrease) in <?php echo $dataGroupCurrentLiability['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentLiability['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalCurrentLiability[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCurrentLiability['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Net Cash from Operating Activities";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Cash from Operating Activities</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalOperatingActivity[$i]+=$totalDepreciation[$i]+$totalCurrentAsset[$i]+$totalCurrentLiability[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalOperatingActivity[$i]!=0?$totalOperatingActivity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOperatingActivity[$i]!=0?$totalOperatingActivity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;border-bottom: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."INVESTING ACTIVITIES";
                ?>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-left: 0px;border-right: 0px;"><i style="font-size: 18px;">INVESTING ACTIVITIES</i></td></tr>
                <?php
                $sqlGroupFixedAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Fixed Asset')
                                        ORDER BY t.id";
                $queryGroupFixedAsset=mysql_query($sqlGroupFixedAsset);
                while($dataGroupFixedAsset=mysql_fetch_array($queryGroupFixedAsset)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupFixedAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>"><?php echo $dataGroupFixedAsset['name']; ?></td>
                    <?php
                    $sqlFixedAsset=str_replace("|||", $dataGroupFixedAsset['id'], $sql);
                    if(sizeof($arrAccumulatedDepreciationAsset)!=0){
                        $sqlFixedAsset=str_replace("WHERE gld.chart_account_id IN", "WHERE gl.id NOT IN (" . implode(",", $arrAccumulatedDepreciationAsset) . ") AND gld.chart_account_id IN", $sqlFixedAsset);
                    }
                    $query=mysql_query($sqlFixedAsset);
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalFixedAsset[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupFixedAsset['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                } 
                $sqlGroupOtherAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Asset')
                                        ORDER BY t.id";
                $queryGroupOtherAsset=mysql_query($sqlGroupOtherAsset);
                while($dataGroupOtherAsset=mysql_fetch_array($queryGroupOtherAsset)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupOtherAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>"><?php echo $dataGroupOtherAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupOtherAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalOtherAsset[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherAsset['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Net Cash from Investing Activities";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Cash from Investing Activities</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalInvestingActivity[$i]=$totalFixedAsset[$i]+$totalOtherAsset[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalInvestingActivity[$i]!=0?$totalInvestingActivity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalInvestingActivity[$i]!=0?$totalInvestingActivity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;border-bottom: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."FINANCING ACTIVITIES";
                ?>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-left: 0px;border-right: 0px;"><i style="font-size: 18px;">FINANCING ACTIVITIES</i></td></tr>
                <?php
                $sqlGroupLongTermLiability="    SELECT g.id,g.name
                                                FROM chart_account_groups g
                                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                                WHERE g.is_active=1 AND t.name IN ('Long Term Liability')
                                                ORDER BY t.id";
                $queryGroupLongTermLiability=mysql_query($sqlGroupLongTermLiability);
                while($dataGroupLongTermLiability=mysql_fetch_array($queryGroupLongTermLiability)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."Borrowing";
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>">Borrowing</td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupLongTermLiability['id'], $sqlCredit));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalLongTermLiability[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupLongTermLiability['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlCreditDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                } 
                $sqlGroupLongTermLiability="    SELECT g.id,g.name
                                                FROM chart_account_groups g
                                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                                WHERE g.is_active=1 AND t.name IN ('Long Term Liability')
                                                ORDER BY t.id";
                $queryGroupLongTermLiability=mysql_query($sqlGroupLongTermLiability);
                while($dataGroupLongTermLiability=mysql_fetch_array($queryGroupLongTermLiability)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."Repayment";
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>" mode="dr">Repayment</td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupLongTermLiability['id'], $sqlDebit));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalLongTermLiability[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupLongTermLiability['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id_dr="<?php echo $dataGroupLongTermLiability['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDebitDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $sqlGroupEquity="   SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Equity')
                                    ORDER BY t.id";
                $queryGroupEquity=mysql_query($sqlGroupEquity);
                while($dataGroupEquity=mysql_fetch_array($queryGroupEquity)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupEquity['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>">
                    <td class="first" type="period" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>"><?php echo $dataGroupEquity['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupEquity['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalEquity[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupEquity['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>" style="display: none;">
                    <td class="first" type="period" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Net Cash from Financing Activities";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Cash from Financing Activities</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalFinancingActivity[$i]=$totalLongTermLiability[$i]+$totalEquity[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalFinancingActivity[$i]!=0?$totalFinancingActivity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalFinancingActivity[$i]!=0?$totalFinancingActivity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Cash Increase/(Decrease) for Period";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Cash Increase/(Decrease) for Period</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalActivity[$i]=$totalOperatingActivity[$i]+$totalInvestingActivity[$i]+$totalFinancingActivity[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalActivity[$i]!=0?$totalActivity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalActivity[$i]!=0?$totalActivity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                $excelContent .= "\n"."Cash & Bank at Beginning of Period";
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;"><b>Cash & Bank at Beginning of Period</b></td>
                    <?php
                    $totalFirstEnding=0;
                    $dFirstEnding=date_parse_from_format('d/m/Y', $_POST['date_from']);
                    if($dFirstEnding['month']!=1){
                        $month=$dFirstEnding['month']-1;
                        $year=$dFirstEnding['year'];
                    }else{
                        $month=12;
                        $year=$dFirstEnding['year']-1;
                    }
                    $sqlGroupCashAndBank="  SELECT g.id,g.name
                                            FROM chart_account_groups g
                                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                            WHERE g.is_active=1 AND t.name IN ('Cash and Bank')
                                            ORDER BY t.id";
                    $queryGroupCashAndBank=mysql_query($sqlGroupCashAndBank);
                    while($dataGroupCashAndBank=mysql_fetch_array($queryGroupCashAndBank)){
                        $sqlFirstEnding="SELECT IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND DATE(date)<=DATE_SUB(DATE('".$dateFrom."'),INTERVAL 1 DAY) ".$conditionBS.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND DATE(date)<=DATE_SUB(DATE('".$dateFrom."'),INTERVAL 1 DAY) ".$conditionBS."),0)";
                        $query=mysql_query(str_replace("|||", $dataGroupCashAndBank['id'], $sqlFirstEnding));
                        $data=mysql_fetch_array($query);
                        $totalFirstEnding+=$data[0];
                    }
                    $totalEnding[-1]=$totalFirstEnding;
                    if($_POST['columns']==0){
                        $i=0;
                        $totalBeginning[$i]=$totalEnding[$i-1];
                        $totalEnding[$i]=$totalActivity[$i]+$totalBeginning[$i];
                    }
                    for($i=0;$i<$count;$i++){
                        $totalBeginning[$i]=$totalEnding[$i-1];
                        $totalEnding[$i]=$totalActivity[$i]+$totalBeginning[$i];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalBeginning[$i]!=0?$totalBeginning[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalBeginning[$i]!=0?$totalBeginning[$i]:$emptyCell);
                    }
                    $totalEnding[$i]=$totalActivity[$i]+$totalBeginning[0];
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalBeginning[0]!=0?$totalBeginning[0]:'-'; ?></td>
                    <?php $excelContent .= "\t".($totalBeginning[0]!=0?$totalBeginning[0]:$emptyCell); ?>
                </tr>
                <?php
                $excelContent .= "\n"."Cash & Bank at End of Period";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Cash & Bank at End of Period</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalEnding[$i]!=0?$totalEnding[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalEnding[$i]!=0?$totalEnding[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n";
                $sqlGroupCashAndBank="  SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Cash and Bank')
                                        ORDER BY t.id";
                $queryGroupCashAndBank=mysql_query($sqlGroupCashAndBank);
                while($dataGroupCashAndBank=mysql_fetch_array($queryGroupCashAndBank)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupCashAndBank['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCashAndBank['id']; ?>">
                    <td class="first" type="as_of" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCashAndBank['id']; ?>"><?php echo $dataGroupCashAndBank['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCashAndBank['id'], $sqlBS));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalCashAndBank[$i]+=$data[$i];?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                        if(($data[$i]!=0 && $data[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCashAndBank['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCashAndBank['id']; ?>" style="display: none;">
                    <td class="first" type="as_of" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlBSDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glcf" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelContent .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Total per Statement of Financial Position";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total per Statement of Financial Position</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                    ?>
                    <td class="link2glcf" style="text-align: right;"><?php echo $totalCashAndBank[$i]!=0 && $totalCashAndBank[$i]!=''?$totalCashAndBank[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCashAndBank[$i]!=0 && $totalCashAndBank[$i]!=''?$totalCashAndBank[$i]:$emptyCell);
                    }
                    ?>
                </tr>
            </table>
        </div>
    </div>
</div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
    <button type="button" id="<?php echo $btnExport; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/csv.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

?>