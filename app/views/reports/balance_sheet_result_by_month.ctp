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
$filename="public/report/balance_sheet_by_month.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2glbsbymonth").each(function(){
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
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroup/as_of/" + chart_account_group_id + "/" + year + "/" + month + "/" + day, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
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
            window.open("<?php echo $this->webroot; ?>public/report/balance_sheet_by_month.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_BALANCE_SHEET . '</b><br /><br />';
    $excelContent .= MENU_BALANCE_SHEET."\n\n";
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
                    <th class="first"></th>
                    <?php
                    $sql="SELECT ";
                    for($i=1;$i<=$daysInMonth;$i++){
                        $sql.="IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$i.",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND gl.is_approve=1 AND gl.is_active=1 ".($_POST['company_id']!=''?($_POST['company_id']!=0?'AND company_id='.$_POST['company_id']:'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')'):'AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')." ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$i.",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                    ?>
                    <th style="text-align: center;" day="<?php echo $i; ?>" month="<?php echo $month; ?>" year="<?php echo $year; ?>">
                        <?php echo str_pad($i,2,"0",STR_PAD_LEFT) . '/' . $monthName[$month-1] . '/' . $year; ?>
                        <?php $excelContent .= "\t".str_pad($i,2,"0",STR_PAD_LEFT) . '/' . $monthName[$month-1] . '/' . $year; ?>
                    </th>
                    <?php
                    }
                    $sql=substr($sql,0,-1);
                    ?>
                </tr>
                <?php
                for($i=0;$i<=$daysInMonth-1;$i++){
                    $totalCurrentAsset[$i]=0;
                    $totalFixedAsset[$i]=0;
                    $totalOtherAsset[$i]=0;
                    $totalAsset[$i]=0;
                    $totalCurrentLiability[$i]=0;
                    $totalLongTermLiability[$i]=0;
                    $totalLiability[$i]=0;
                    $totalEquity[$i]=0;
                    $totalLiabilityAndEquity[$i]=0;
                }
                $sqlGroupCurrentAsset=" SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Cash and Bank','Accounts receivable','Other Current Asset')
                                        ORDER BY t.id";
                $queryGroupCurrentAsset=mysql_query($sqlGroupCurrentAsset);
                while($dataGroupCurrentAsset=mysql_fetch_array($queryGroupCurrentAsset)){
                    $excelContent .= "\n".$dataGroupCurrentAsset['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>"><?php echo $dataGroupCurrentAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalCurrentAsset[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Current Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Current Asset</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalCurrentAsset[$i]!=''?$totalCurrentAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCurrentAsset[$i]!=''?$totalCurrentAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupFixedAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Fixed Asset')
                                        ORDER BY t.id";
                $queryGroupFixedAsset=mysql_query($sqlGroupFixedAsset);
                $excelContent .= "\n";
                while($dataGroupFixedAsset=mysql_fetch_array($queryGroupFixedAsset)){
                    $excelContent .= "\n".$dataGroupFixedAsset['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>"><?php echo $dataGroupFixedAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupFixedAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalFixedAsset[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Fixed Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Fixed Asset</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalFixedAsset[$i]!=''?$totalFixedAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalFixedAsset[$i]!=''?$totalFixedAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Asset')
                                        ORDER BY t.id";
                $queryGroupOtherAsset=mysql_query($sqlGroupOtherAsset);
                $excelContent .= "\n";
                while($dataGroupOtherAsset=mysql_fetch_array($queryGroupOtherAsset)){
                    $excelContent .= "\n".$dataGroupOtherAsset['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>"><?php echo $dataGroupOtherAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupOtherAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalOtherAsset[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Other Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Asset</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalOtherAsset[$i]!=''?$totalOtherAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherAsset[$i]!=''?$totalOtherAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Asset</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalAsset[$i]=$totalCurrentAsset[$i]+$totalFixedAsset[$i]+$totalOtherAsset[$i];
                    ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalAsset[$i]!=0?$totalAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalAsset[$i]!=0?$totalAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupCurrentLiability=" SELECT g.id,g.name
                                            FROM chart_account_groups g
                                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                            WHERE g.is_active=1 AND t.name IN ('Accounts Payable','Credit Card','Other Current Liability')
                                            ORDER BY t.id";
                $queryGroupCurrentLiability=mysql_query($sqlGroupCurrentLiability);
                $excelContent .= "\n";
                while($dataGroupCurrentLiability=mysql_fetch_array($queryGroupCurrentLiability)){
                    $excelContent .= "\n".$dataGroupCurrentLiability['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>"><?php echo $dataGroupCurrentLiability['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentLiability['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalCurrentLiability[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Current Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Current Liability</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalCurrentLiability[$i]!=''?$totalCurrentLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCurrentLiability[$i]!=''?$totalCurrentLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupLongTermLiability="    SELECT g.id,g.name
                                                FROM chart_account_groups g
                                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                                WHERE g.is_active=1 AND t.name IN ('Long Term Liability')
                                                ORDER BY t.id";
                $queryGroupLongTermLiability=mysql_query($sqlGroupLongTermLiability);
                $excelContent .= "\n";
                while($dataGroupLongTermLiability=mysql_fetch_array($queryGroupLongTermLiability)){
                    $excelContent .= "\n".$dataGroupLongTermLiability['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>"><?php echo $dataGroupLongTermLiability['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupLongTermLiability['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalLongTermLiability[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Long Term Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Long Term Liability</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalLongTermLiability[$i]!=''?$totalLongTermLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalLongTermLiability[$i]!=''?$totalLongTermLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Liability</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalLiability[$i]=$totalCurrentLiability[$i]+$totalLongTermLiability[$i];
                    ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalLiability[$i]!=0?$totalLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalLiability[$i]!=0?$totalLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupEquity="   SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Equity')
                                    ORDER BY t.id";
                $queryGroupEquity=mysql_query($sqlGroupEquity);
                $excelContent .= "\n";
                while($dataGroupEquity=mysql_fetch_array($queryGroupEquity)){
                    $excelContent .= "\n".$dataGroupEquity['name'];
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>"><?php echo $dataGroupEquity['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupEquity['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalEquity[$i]+=$data[$i];?>
                        <td class="link2glbsbymonth" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($data[$i]!=0 && $data[$i]!=''?$data[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Profit/Loss for the Period";
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;">Profit/Loss for the Period</td>
                    <?php
                    for($i=0;$i<=$daysInMonth-1;$i++){
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
                        for($i=0;$i<=$daysInMonth-1;$i++){
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
                        for($i=0;$i<=$daysInMonth-1;$i++){
                            $totalCOGS[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$daysInMonth-1;$i++){
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
                        for($i=0;$i<=$daysInMonth-1;$i++){
                            $totalExpense[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$daysInMonth-1;$i++){
                            $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                        }
                    }
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalEquity[$i]+=$totalProfitLoss[$i];
                    ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <?php
                $excelContent .= "\n"."Total Equity";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Equity</b></td>
                    <?php for($i=0;$i<=$daysInMonth-1;$i++){ ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalEquity[$i]!=''?$totalEquity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalEquity[$i]!=''?$totalEquity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $daysInMonth+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Liability & Equity";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Liability & Equity</b></td>
                    <?php
                    for($i=0;$i<=$daysInMonth-1;$i++){
                        $totalLiabilityAndEquity[$i]=$totalLiability[$i]+$totalEquity[$i];
                    ?>
                    <td class="link2glbsbymonth" style="text-align: right;"><?php echo $totalLiabilityAndEquity[$i]!=0?$totalLiabilityAndEquity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalLiabilityAndEquity[$i]!=0?$totalLiabilityAndEquity[$i]:$emptyCell);
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