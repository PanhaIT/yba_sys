<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackBudgetPl").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBudgetPl">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<?php

$_POST['date_from']='01/01/'.$budgetPl['BudgetPl']['year'];
$_POST['date_to']='31/12/'.$budgetPl['BudgetPl']['year'];
$_POST['columns']=1;
$_POST['company_id']='';
$_POST['vendor_id']='';
$_POST['customer_id']='';
$_POST['class_id']='';

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

include('includes/function.php');

$dateFrom = dateConvert($_POST['date_from']);
$dateTo = dateConvert($_POST['date_to']);

/**
 * condition for date
 */
$condition='';
if($_POST['date_from']!='') {
    $condition.=' AND "'.$dateFrom.'" <= DATE(date)';
}
if($_POST['date_to']!='') {
    $condition.=' AND "'.$dateTo.'" >= DATE(date)';
}

/**
 * export to excel
 */
$filename="public/report/profit_loss.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // format budget
        $(".budget").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
            }
        });

        // btn link to general ledger
        $(".link2gl").each(function(){
            if(!isNaN($(this).text())){
                var parentIndex=parseInt((($(this).index()-1)/4)+1);
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                var year=$(this).parent().parent().find("tr:first th:eq("+parentIndex+")").attr("year");
                var month=$(this).parent().parent().find("tr:first th:eq("+parentIndex+")").attr("month");
                var dateFrom=$(this).attr("date_from");
                var dateTo=$(this).attr("date_to");
                var chart_account_group_id=$(this).siblings("td:eq(0)").attr("chart_account_group_id");
                if(chart_account_group_id){
                    if(year && month){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroup/period/" + chart_account_group_id + "/" + year + "/" + month, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }else if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroupDateRange/" + chart_account_group_id + "/" + dateFrom + "/" + dateTo, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }
                }
                // group expansion
                var chart_account_id=$(this).siblings("td:eq(0)").attr("chart_account_id");
                if(chart_account_id){
                    if(year && month){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTb/period/" + chart_account_id + "/" + year + "/" + month, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }else if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTbDateRange/" + chart_account_id + "/" + dateFrom + "/" + dateTo, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }
                }
            }
        });

        // hide empty row
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

        // clone
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
                if(obj.scrollTop()>180){
                    $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>").css("top", Number(obj.scrollTop()-180));
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

        // group expansion
        $("#<?php echo $printArea; ?> .group td:first-child").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $printArea; ?> .group td:first-child").css("cursor", "pointer");
        $("#<?php echo $printArea; ?> .groupDetail").css("background", "#EEE");
        $("#<?php echo $printArea; ?> .group td:first-child").click(function(){
            if($("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id=" + $(this).attr("chart_account_group_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id="+$(this).attr("chart_account_group_id")+"]").toggle();
        });
        $(".<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").show();
        });
        $(".<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").hide();
        });

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

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/profit_loss.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . $budgetPl['BudgetPl']['name'] . '</b><br /><br />';
    $excelContent .= $budgetPl['BudgetPl']['name']."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <div style="position: relative;">
        <div id="<?php echo $cloneCorner; ?>" style="position: absolute;overflow: hidden;z-index: 1001;background: #FFF;"></div>
        <div id="<?php echo $cloneTop; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $cloneLeft; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $originTable; ?>">
            <table class="table_report">
                <tr>
                    <th class="first" style="text-align: left;" rowspan="2">
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
                    if($_POST['columns']!=0){
                        for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                            $month=$i-($incYear*12);
                            $year=$d1['year']+$incYear;
                            $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                            $sqlDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                    ?>
                    <th colspan="4" style="text-align: center;" month="<?php echo $month; ?>" year="<?php echo $year; ?>">
                        <?php
                        echo $monthName[$month-1] . '/' . $year;
                        $excelContent .= "\t".$monthName[$month-1].'/'.$year."\t\t\t";
                        if($i%12==0)$incYear++;
                        $count++;
                        ?>
                    </th>
                    <?php
                        }
                    }
                    $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=||| AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IS NULL'):'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";

                    $excelContent .= "\t".TABLE_TOTAL;
                    ?>
                    <th colspan="4" style="text-align: center;"><?php echo TABLE_TOTAL; ?></th>
                </tr>
                <tr>
                    <?php
                    for($i=1;$i<=$count+1;$i++){
                        if($i==1){
                            $excelContent .= "\n\t".REPORT_ACTUAL."\t".REPORT_BUDGET."\t".REPORT_DOLLAR_OVER_BUDGET."\t".REPORT_PERCENT_OF_BUDGET;
                        }else{
                            $excelContent .= "\t".REPORT_ACTUAL."\t".REPORT_BUDGET."\t".REPORT_DOLLAR_OVER_BUDGET."\t".REPORT_PERCENT_OF_BUDGET;
                        }
                    ?>
                    <th style="white-space: nowrap;min-width: 100px !important;"><?php echo REPORT_ACTUAL; ?></th>
                    <th style="white-space: nowrap;min-width: 100px !important;"><?php echo REPORT_BUDGET; ?></th>
                    <th style="white-space: nowrap;min-width: 100px !important;"><?php echo REPORT_DOLLAR_OVER_BUDGET; ?></th>
                    <th style="white-space: nowrap;min-width: 100px !important;"><?php echo REPORT_PERCENT_OF_BUDGET; ?></th>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                for($i=0;$i<=$count;$i++){
                    $totalRevenue[$i]=0;
                    $totalRevenueBudget[$i]=0;
                    $totalCOGS[$i]=0;
                    $totalCOGSBudget[$i]=0;
                    $totalGrossProfit[$i]=0;
                    $totalGrossProfitBudget[$i]=0;
                    $totalExpense[$i]=0;
                    $totalExpenseBudget[$i]=0;
                    $totalOtherRevenue[$i]=0;
                    $totalOtherRevenueBudget[$i]=0;
                    $totalOtherExpense[$i]=0;
                    $totalOtherExpenseBudget[$i]=0;
                    $totalProfitLoss[$i]=0;
                    $totalProfitLossBudget[$i]=0;
                }
                $sqlGroupIncome="   SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Income')
                                    ORDER BY t.id";
                $queryGroupIncome=mysql_query($sqlGroupIncome);
                while($dataGroupIncome=mysql_fetch_array($queryGroupIncome)){
                    $excelContent .= "\n".$dataGroupIncome['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>"><?php echo $dataGroupIncome['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalRevenue[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupIncome['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalRevenueBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupIncome['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Total Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Revenue</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalRevenue[$i]!='' || $totalRevenueBudget[$i]!=''?$totalRevenue[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalRevenueBudget[$i]!=''?$totalRevenueBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalRevenueBudget[$i]!=''?$totalRevenue[$i]-$totalRevenueBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalRevenueBudget[$i]!=''?number_format(@($totalRevenue[$i]/$totalRevenueBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalRevenue[$i]!='' || $totalRevenueBudget[$i]!=''?$totalRevenue[$i]:'-');
                        $excelContent .= "\t".($totalRevenueBudget[$i]!=''?$totalRevenueBudget[$i]:'-');
                        $excelContent .= "\t".($totalRevenueBudget[$i]!=''?$totalRevenue[$i]-$totalRevenueBudget[$i]:'-');
                        $excelContent .= "\t".($totalRevenueBudget[$i]!=''?number_format(@($totalRevenue[$i]/$totalRevenueBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupCOGS=" SELECT g.id,g.name
                                FROM chart_account_groups g
                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                WHERE g.is_active=1 AND t.name IN ('Cost of Goods Sold')
                                ORDER BY t.id";
                $queryGroupCOGS=mysql_query($sqlGroupCOGS);
                $excelContent .= "\n";
                while($dataGroupCOGS=mysql_fetch_array($queryGroupCOGS)){
                    $excelContent .= "\n".$dataGroupCOGS['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>"><?php echo $dataGroupCOGS['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupCOGS['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalCOGS[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupCOGS['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalCOGSBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCOGS['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Cost of Goods Sold";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Cost of Goods Sold</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalCOGS[$i]!='' || $totalCOGSBudget[$i]!=''?$totalCOGS[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalCOGSBudget[$i]!=''?$totalCOGSBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalCOGSBudget[$i]!=''?$totalCOGS[$i]-$totalCOGSBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalCOGSBudget[$i]!=''?number_format(@($totalCOGS[$i]/$totalCOGSBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCOGS[$i]!='' || $totalCOGSBudget[$i]!=''?$totalCOGS[$i]:'-');
                        $excelContent .= "\t".($totalCOGSBudget[$i]!=''?$totalCOGSBudget[$i]:'-');
                        $excelContent .= "\t".($totalCOGSBudget[$i]!=''?$totalCOGS[$i]-$totalCOGSBudget[$i]:'-');
                        $excelContent .= "\t".($totalCOGSBudget[$i]!=''?number_format(@($totalCOGS[$i]/$totalCOGSBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Gross Profit";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Gross Profit</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalGrossProfit[$i]=$totalRevenue[$i]-$totalCOGS[$i];
                        $totalGrossProfitBudget[$i]=$totalRevenueBudget[$i]-$totalCOGSBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalGrossProfit[$i]!='' || $totalGrossProfitBudget[$i]!=''?$totalGrossProfit[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalGrossProfitBudget[$i]!=''?$totalGrossProfitBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalGrossProfitBudget[$i]!=''?$totalGrossProfit[$i]-$totalGrossProfitBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalGrossProfitBudget[$i]!=''?number_format(@($totalGrossProfit[$i]/$totalGrossProfitBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalGrossProfit[$i]!='' || $totalGrossProfitBudget[$i]!=''?$totalGrossProfit[$i]:'-');
                        $excelContent .= "\t".($totalGrossProfitBudget[$i]!=''?$totalGrossProfitBudget[$i]:'-');
                        $excelContent .= "\t".($totalGrossProfitBudget[$i]!=''?$totalGrossProfit[$i]-$totalGrossProfitBudget[$i]:'-');
                        $excelContent .= "\t".($totalGrossProfitBudget[$i]!=''?number_format(@($totalGrossProfit[$i]/$totalGrossProfitBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupExpense="  SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Expense') AND g.is_depreciation NOT IN (2,3)
                                    ORDER BY t.id";
                $queryGroupExpense=mysql_query($sqlGroupExpense);
                $excelContent .= "\n";
                while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
                    $excelContent .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupExpense['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalExpenseBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Total Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Expenses</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalExpense[$i]!='' || $totalExpenseBudget[$i]!=''?$totalExpense[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalExpenseBudget[$i]!=''?$totalExpenseBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalExpenseBudget[$i]!=''?$totalExpense[$i]-$totalExpenseBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalExpenseBudget[$i]!=''?number_format(@($totalExpense[$i]/$totalExpenseBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalExpense[$i]!='' || $totalExpenseBudget[$i]!=''?$totalExpense[$i]:'-');
                        $excelContent .= "\t".($totalExpenseBudget[$i]!=''?$totalExpenseBudget[$i]:'-');
                        $excelContent .= "\t".($totalExpenseBudget[$i]!=''?$totalExpense[$i]-$totalExpenseBudget[$i]:'-');
                        $excelContent .= "\t".($totalExpenseBudget[$i]!=''?number_format(@($totalExpense[$i]/$totalExpenseBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Ordinary Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Ordinary Income</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                        $totalProfitLossBudget[$i]=$totalGrossProfitBudget[$i]-$totalExpenseBudget[$i]+$totalOtherRevenueBudget[$i]-$totalOtherExpenseBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherIncome="  SELECT g.id,g.name
                                        FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Income')
                                        ORDER BY t.id";
                $queryGroupOtherIncome=mysql_query($sqlGroupOtherIncome);
                while($dataGroupOtherIncome=mysql_fetch_array($queryGroupOtherIncome)){
                    $excelContent .= "\n\n".$dataGroupOtherIncome['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>"><?php echo $dataGroupOtherIncome['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupOtherIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalOtherRevenue[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupOtherIncome['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalOtherRevenueBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherIncome['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Total Other Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Revenue</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalOtherRevenue[$i]!='' || $totalOtherRevenueBudget[$i]!=''?$totalOtherRevenue[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalOtherRevenueBudget[$i]!=''?$totalOtherRevenueBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalOtherRevenueBudget[$i]!=''?$totalOtherRevenue[$i]-$totalOtherRevenueBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalOtherRevenueBudget[$i]!=''?number_format(@($totalOtherRevenue[$i]/$totalOtherRevenueBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherRevenue[$i]!='' || $totalOtherRevenueBudget[$i]!=''?$totalOtherRevenue[$i]:'-');
                        $excelContent .= "\t".($totalOtherRevenueBudget[$i]!=''?$totalOtherRevenueBudget[$i]:'-');
                        $excelContent .= "\t".($totalOtherRevenueBudget[$i]!=''?$totalOtherRevenue[$i]-$totalOtherRevenueBudget[$i]:'-');
                        $excelContent .= "\t".($totalOtherRevenueBudget[$i]!=''?number_format(@($totalOtherRevenue[$i]/$totalOtherRevenueBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherExpense=" SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Expense') AND g.is_depreciation NOT IN (2,3)
                                        ORDER BY t.id";
                $queryGroupOtherExpense=mysql_query($sqlGroupOtherExpense);
                $excelContent .= "\n";
                while($dataGroupOtherExpense=mysql_fetch_array($queryGroupOtherExpense)){
                    $excelContent .= "\n".$dataGroupOtherExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>"><?php echo $dataGroupOtherExpense['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupOtherExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalOtherExpense[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupOtherExpense['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalOtherExpenseBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Total Other Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Expenses</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalOtherExpense[$i]!='' || $totalOtherExpenseBudget[$i]!=''?$totalOtherExpense[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalOtherExpenseBudget[$i]!=''?$totalOtherExpenseBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalOtherExpenseBudget[$i]!=''?$totalOtherExpense[$i]-$totalOtherExpenseBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalOtherExpenseBudget[$i]!=''?number_format(@($totalOtherExpense[$i]/$totalOtherExpenseBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherExpense[$i]!='' || $totalOtherExpenseBudget[$i]!=''?$totalOtherExpense[$i]:'-');
                        $excelContent .= "\t".($totalOtherExpenseBudget[$i]!=''?$totalOtherExpenseBudget[$i]:'-');
                        $excelContent .= "\t".($totalOtherExpenseBudget[$i]!=''?$totalOtherExpense[$i]-$totalOtherExpenseBudget[$i]:'-');
                        $excelContent .= "\t".($totalOtherExpenseBudget[$i]!=''?number_format(@($totalOtherExpense[$i]/$totalOtherExpenseBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Other Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Other Income</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                        $totalProfitLossBudget[$i]=$totalOtherRevenueBudget[$i]-$totalOtherExpenseBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count*4+5; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Earnings Before Interest & Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Interest & Tax</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                        $totalProfitLossBudget[$i]=$totalGrossProfitBudget[$i]-$totalExpenseBudget[$i]+$totalOtherRevenueBudget[$i]-$totalOtherExpenseBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                $sqlGroupExpense="  SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Expense','Other Expense') AND g.is_depreciation IN (2)
                                    ORDER BY t.id";
                $queryGroupExpense=mysql_query($sqlGroupExpense);
                while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
                    $excelContent .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupExpense['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalExpenseBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Earnings Before Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Tax</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                        $totalProfitLossBudget[$i]=$totalGrossProfitBudget[$i]-$totalExpenseBudget[$i]+$totalOtherRevenueBudget[$i]-$totalOtherExpenseBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                $sqlGroupExpense="  SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Expense','Other Expense') AND g.is_depreciation IN (3)
                                    ORDER BY t.id";
                $queryGroupExpense=mysql_query($sqlGroupExpense);
                while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
                    $excelContent .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $accActual=0;
                    $accActualTotal=0;
                    $accBudget=0;
                    $accBudgetTotal=0;
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];
                        if($i<12){
                            $queryCell=mysql_query("SELECT SUM(m".($i+1).") FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=".$dataGroupExpense['id'].")");
                            $dataCell=mysql_fetch_array($queryCell);
                            $accActual=$data[$i];
                            $accActualTotal+=$accActual;
                            $accBudget=$dataCell[0];
                            $accBudgetTotal+=$accBudget;
                            $accBudgetOver=$accActual-$accBudget;
                            $accBudgetOverPercent=@($accActual/$accBudget)*100;
                        }else{
                            $accBudget=$accBudgetTotal;
                            $accBudgetOver=$accActualTotal-$accBudgetTotal;
                            $accBudgetOverPercent=@($accActualTotal/$accBudgetTotal)*100;
                        }
                        $totalExpenseBudget[$i]+=$accBudget;
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?$accBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($data[$i]!=0 && $data[$i]!='') || ($accBudget!=0 && $accBudget!='')?$data[$i]:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudget:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?$accBudgetOver:'-');
                        $excelContent .= "\t".($accBudget!=0 && $accBudget!=''?number_format($accBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                // group expansion
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $excelContent .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $accDetailActual=0;
                    $accDetailActualTotal=0;
                    $accDetailBudget=0;
                    $accDetailBudgetTotal=0;
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($i<12){
                            $queryCell=mysql_query("SELECT m".($i+1)." FROM budget_pl_details WHERE budget_pl_id=".$budgetPl['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                            $dataCell=mysql_fetch_array($queryCell);
                            $accDetailActual=$dataDetail[$i];
                            $accDetailActualTotal+=$accDetailActual;
                            $accDetailBudget=$dataCell[0];
                            $accDetailBudgetTotal+=$accDetailBudget;
                            $accDetailBudgetOver=$accDetailActual-$accDetailBudget;
                            $accDetailBudgetOverPercent=@($accDetailActual/$accDetailBudget)*100;
                        }else{
                            $accDetailBudget=$accDetailBudgetTotal;
                            $accDetailBudgetOver=$accDetailActualTotal-$accDetailBudgetTotal;
                            $accDetailBudgetOverPercent=@($accDetailActualTotal/$accDetailBudgetTotal)*100;
                        }
                    ?>
                        <td class="link2gl" date_from="<?php echo $dateFrom; ?>" date_to="<?php echo $dateTo; ?>" style="text-align: right;"><?php echo ($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-'; ?></td>
                        <td class="budget" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-'; ?></td>
                        <td class="budget_percent" style="text-align: right;"><?php echo $accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".(($dataDetail[$i]!=0 && $dataDetail[$i]!='') || ($accDetailBudget!=0 && $accDetailBudget!='')?$dataDetail[$i]:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudget:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?$accDetailBudgetOver:'-');
                        $excelContent .= "\t".($accDetailBudget!=0 && $accDetailBudget!=''?number_format($accDetailBudgetOverPercent,2).'%':'-');
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?php
                }
                $excelContent .= "\n"."Profit/Loss for the Year";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Profit/Loss for the Year</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                        $totalProfitLossBudget[$i]=$totalGrossProfitBudget[$i]-$totalExpenseBudget[$i]+$totalOtherRevenueBudget[$i]-$totalOtherExpenseBudget[$i];
                    ?>
                    <td class="link2gl" style="text-align: right;"><?php echo $totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-'; ?></td>
                    <td class="budget_percent" style="text-align: right;"><?php echo $totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!='' || $totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?$totalProfitLoss[$i]-$totalProfitLossBudget[$i]:'-');
                        $excelContent .= "\t".($totalProfitLossBudget[$i]!=''?number_format(@($totalProfitLoss[$i]/$totalProfitLossBudget[$i])*100,2).'%':'-');
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
        <img src="<?php echo $this->webroot; ?>img/button/approved.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

?>