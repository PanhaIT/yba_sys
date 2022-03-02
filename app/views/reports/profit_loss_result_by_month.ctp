<?php

$rnd = rand();
$printArea = "printArea" . $rnd;
$cloneCorner = "cloneCorner" . $rnd;
$cloneTop = "cloneTop" . $rnd;
$cloneLeft = "cloneLeft" . $rnd;
$originTable = "originTable" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

$monthName=array(DATE_JAN, DATE_FEB, DATE_MAR, DATE_APR, DATE_MAY, DATE_JUN, DATE_JUL, DATE_AUG, DATE_SEP, DATE_OCT, DATE_NOV, DATE_DEC);
$emptyCell='0';

include('includes/function.php');

/**
 * condition for date
 */
$daysInMonth = days_in_month($_POST['month'], $_POST['year']);
$month=$_POST['month'];
$year=$_POST['year'];
$_POST['date_from']='01/'.$monthName[$month-1].'/'.$year;
$_POST['date_to']=$daysInMonth.'/'.$monthName[$month-1].'/'.$year;

/**
 * export to excel
 */
$filename="public/report/profit_loss_by_month.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2glplbymonth").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                var chart_account_group_id=$(this).siblings("td:eq(0)").attr("chart_account_group_id");
                var year=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("year");
                var month=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("month");
                var day=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("day");
                if(chart_account_group_id && year && month && day){
                    $(this).css("cursor", "pointer");
                    $(this).click(function(){
                        $('#tabs ul li a').not("[href=#]").each(function(index) {
                            if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                $("#tabs").tabs("select", $(this).attr("href"));
                                var selIndex = $("#tabs").tabs("option", "selected");
                                $("#tabs").tabs("remove", selIndex);
                            }
                        });
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroup/period/" + chart_account_group_id + "/" + year + "/" + month + "/" + day, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                    });
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
                if(obj.scrollTop()>330){
                    $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>").css("top", Number(obj.scrollTop()-330));
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
        
        $("#<?php echo $btnPrint; ?>").click(function(){
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });
        
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/profit_loss_by_month.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_PROFIT_AND_LOSS . '</b><br /><br />';
    $excelContent .= MENU_PROFIT_AND_LOSS."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    if(!empty($_POST['branch_id']) || $_POST['customer_id']!='' || $_POST['vendor_id']!='' || $_POST['other_id']!='' || $_POST['class_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if(!empty($_POST['branch_id'])){
        $index = 0;
        $branchName = "";
        $query=mysql_query("SELECT name FROM branches WHERE id IN (".$_POST['branch_id'].")");
        while($data=mysql_fetch_array($query)){
            if($index > 0){
                $branchName .= " & ";
            }
            $branchName .= $data['name'];
            $index++;
        }
        $msg .= '<b>' . TABLE_BRANCH . '</b>: ' . $branchName;
        $excelContent .= TABLE_BRANCH . ": " . $branchName;
    }
    if($_POST['customer_id']!='') {
        $query=mysql_query("SELECT name FROM customers WHERE id=".$_POST['customer_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_CUSTOMER . '</b>: ' . $data[0];
        $excelContent .= " " . TABLE_CUSTOMER . ": " . $data[0];
    }
    if($_POST['vendor_id']!='') {
        $query=mysql_query("SELECT name FROM vendors WHERE id=".$_POST['vendor_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_VENDOR . '</b>: ' . $data[0];
        $excelContent .= " " . TABLE_VENDOR . ": " . $data[0];
    }
    if($_POST['other_id']!='') {
        $query=mysql_query("SELECT name FROM others WHERE id=".$_POST['other_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_OTHER . '</b>: ' . $data[0];
        $excelContent .= " " . TABLE_OTHER . ": " . $data[0];
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
                    <th class="first"></th>
                    <?php
                    $sql="SELECT ";
                    for($i=1;$i<=$daysInMonth;$i++){
                        $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND is_retained_earnings=0 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND DAYOFMONTH(date)=".$i." AND MONTH(date)=".$month." AND YEAR(date)=".$year.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND is_retained_earnings=0 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND DAYOFMONTH(date)=".$i." AND MONTH(date)=".$month." AND YEAR(date)=".$year."),0),";
                    ?>
                    <th style="text-align: center;" day="<?php echo $i; ?>" month="<?php echo $month; ?>" year="<?php echo $year; ?>">
                        <?php echo str_pad($i,2,"0",STR_PAD_LEFT) . '/' . $monthName[$month-1] . '/' . $year; ?>
                        <?php $excelContent .= "\t".str_pad($i,2,"0",STR_PAD_LEFT) . '/' . $monthName[$month-1] . '/' . $year; ?>
                    </th>
                    <?php
                    }
                    $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND is_retained_earnings=0 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 AND is_retained_earnings=0 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year."),0)";

                    $excelContent .= "\t".TABLE_TOTAL;
                    ?>
                    <th style="text-align: center;"><?php echo TABLE_TOTAL; ?></th>
                </tr>
                <?php
                for($i=0;$i<=$daysInMonth;$i++){
                    $totalRevenue[$i]=0;
                    $totalCOGS[$i]=0;
                    $totalGrossProfit[$i]=0;
                    $totalExpense[$i]=0;
                    $totalOtherRevenue[$i]=0;
                    $totalOtherExpense[$i]=0;
                    $totalProfitLoss[$i]=0;
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
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>"><?php echo $dataGroupIncome['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalRevenue[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Revenue</b></td>
                    <?php for($i=0;$i<=$daysInMonth;$i++){ ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalRevenue[$i]!=''?$totalRevenue[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalRevenue[$i]!=''?$totalRevenue[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
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
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>"><?php echo $dataGroupCOGS['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCOGS['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalCOGS[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Cost of Goods Sold";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Cost of Goods Sold</b></td>
                    <?php for($i=0;$i<=$daysInMonth;$i++){ ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalCOGS[$i]!=''?$totalCOGS[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCOGS[$i]!=''?$totalCOGS[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Gross Profit";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Gross Profit</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalGrossProfit[$i]=$totalRevenue[$i]-$totalCOGS[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalGrossProfit[$i]!=0?$totalGrossProfit[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalGrossProfit[$i]!=0?$totalGrossProfit[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
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
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Expenses</b></td>
                    <?php for($i=0;$i<=$daysInMonth;$i++){ ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalExpense[$i]!=''?$totalExpense[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalExpense[$i]!=''?$totalExpense[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Ordinary Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Ordinary Income</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
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
                    $query=mysql_query(str_replace("|||", $dataGroupOtherIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalOtherRevenue[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Other Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Revenue</b></td>
                    <?php for($i=0;$i<=$daysInMonth;$i++){ ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalOtherRevenue[$i]!=''?$totalOtherRevenue[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherRevenue[$i]!=''?$totalOtherRevenue[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
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
                    $query=mysql_query(str_replace("|||", $dataGroupOtherExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalOtherExpense[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Other Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Expenses</b></td>
                    <?php for($i=0;$i<=$daysInMonth;$i++){ ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalOtherExpense[$i]!=''?$totalOtherExpense[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherExpense[$i]!=''?$totalOtherExpense[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Other Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Other Income</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalProfitLoss[$i]=$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Earnings Before Interest & Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Interest & Tax</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
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
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Earnings Before Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Tax</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
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
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glplbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Profit/Loss for the Year";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Profit/Loss for the Year</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glplbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
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