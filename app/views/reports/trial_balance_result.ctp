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
/**
 * condition for date
 */
$condition='';
if($_POST['date_from']!='') {
    //$condition.=' AND "'.dateConvert($_POST['date_from']).'" <= DATE(date)';
}
if($_POST['date_to']!='') {
    $condition.=' AND "'.dateConvert($_POST['date_to']).'" >= DATE(date)';
}

/**
 * export to excel
 */
$filename="public/report/trial_balance.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2gl").each(function(){
            if(!isNaN($(this).text())){
                var parentIndex=parseInt((($(this).index()-3)/2)+3);
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                <?php
                if (@sizeof($_POST['company_id']) == 1 && !empty($_POST['company_id'])) {
                ?>
                var dateAsOf=$(this).parent().parent().find("tr:first th:eq("+parentIndex+")").attr("dateAsOf");
                
                var companyId='<?php echo $companyJorunal; ?>';
                var branchId='<?php echo $branchJorunal; ?>';
                var customerId='<?php echo $_POST['customer_id']!=''?$_POST['customer_id']:'null'; ?>';
                var vendorId='<?php echo $_POST['vendor_id']!=''?$_POST['vendor_id']:'null'; ?>';
                var otherId='<?php echo $_POST['other_id']!=''?$_POST['other_id']:'null'; ?>';
                var classId='<?php echo $_POST['class_id']!=''?$_POST['class_id']:'null'; ?>';

                var chart_account_id=$(this).siblings("td:eq(0)").attr("chart_account_id");
                if(chart_account_id && dateAsOf){
                    $(this).css("cursor", "pointer");
                    $(this).click(function(){
                        $('#tabs ul li a').not("[href=#]").each(function(index) {
                            if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                $("#tabs").tabs("select", $(this).attr("href"));
                                var selIndex = $("#tabs").tabs("option", "selected");
                                $("#tabs").tabs("remove", selIndex);
                            }
                        });
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTb/" + chart_account_id + "/" + dateAsOf + "/" + companyId + "/" + branchId + "/" + customerId + "/" + vendorId + "/" + otherId + "/" + classId+"?title=", "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                    });
                }
                <?php
                }
                ?>
            }
        });

        // hide empty row
        <?php if(!isset($_POST['displayEmptyData'])){ ?>
        $("#<?php echo $printArea; ?> .table_report tr:gt(1)").each(function(){
            var notEmpty=0;
            $("td:gt(2)", this).each(function(){
                if($(this).text()!="-"){
                    notEmpty=1;
                    return false;
                }
            });
            if(notEmpty==0){
                $(this).remove();
            }
        });
        <?php } ?>

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
            $("#<?php echo $cloneCorner; ?>").css("width", Number($("#<?php echo $originTable; ?> tr:first-child th:eq(0)").outerWidth()+$("#<?php echo $originTable; ?> tr:first-child th:eq(1)").outerWidth()+$("#<?php echo $originTable; ?> tr:first-child th:eq(2)").outerWidth()));
            $("#<?php echo $cloneCorner; ?>").css("height", $("#<?php echo $originTable; ?> tr:first-child th:first").outerHeight()+5);
            $("#<?php echo $cloneLeft; ?>").css("width", Number($("#<?php echo $originTable; ?> tr:first-child th:eq(0)").outerWidth()+$("#<?php echo $originTable; ?> tr:first-child th:eq(1)").outerWidth()+$("#<?php echo $originTable; ?> tr:first-child th:eq(2)").outerWidth()));
        }
        // event scroll fire
        var timer;
        $(".ui-tabs-panel").scroll(function(){
            var obj=$(this);
            $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>,#<?php echo $cloneLeft; ?>").hide();
            clearTimeout(timer);
            timer=setTimeout(function() {
                // scroll top
                if(obj.scrollTop()>260){
                    $("#<?php echo $cloneCorner; ?>,#<?php echo $cloneTop; ?>").css("top", Number(obj.scrollTop()-260));
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
            window.open("<?php echo $this->webroot; ?>public/report/trial_balance.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_TRIAL_BALANCE . '</b><br /><br />';
    $excelContent .= MENU_TRIAL_BALANCE."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
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
        $msg .= '<b>' . TABLE_COMPANY . '</b>: ' . $companyName;
        $excelContent .= TABLE_COMPANY . ": " . $companyName;
    }
    if(!empty($_POST['branch_id'])){
        $query=mysql_query("SELECT name FROM branches WHERE id = ".$branchId);
        $data=mysql_fetch_array($query);
        $msg .= '<b>' . TABLE_BRANCH . '</b>: ' . $data['name'];
        $excelContent .= TABLE_BRANCH . ": " . $data['name'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    
    $excelContent .= TABLE_ACCOUNT_CODE."\t".TABLE_ACCOUNT_DESCRIPTION."\t".TABLE_ACCOUNT_GROUP."\t";
    ?>
    <div style="position: relative;">
        <div id="<?php echo $cloneCorner; ?>" style="position: absolute;overflow: hidden;z-index: 1001;background: #FFF;"></div>
        <div id="<?php echo $cloneTop; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $cloneLeft; ?>" style="position: absolute;overflow: hidden;z-index: 1000;background: #FFF;"></div>
        <div id="<?php echo $originTable; ?>">
            <table class="table_report">
                <tr>
                    <th class="first" rowspan="2"><?php echo TABLE_ACCOUNT_CODE; ?></th>
                    <th rowspan="2"><?php echo TABLE_ACCOUNT_DESCRIPTION; ?></th>
                    <th rowspan="2"><?php echo TABLE_ACCOUNT_GROUP; ?></th>
                    <?php
                    $count=0;
                    $incYear=0;
                    $d1=date_parse_from_format('d/m/Y', $_POST['date_from']);
                    $d2=date_parse_from_format('d/m/Y', $_POST['date_to']);
                    $sql="SELECT c.id,account_codes,account_description,cg.name";
                    for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                        $month=$i-($incYear*12);
                        $year=$d1['year']+$incYear;
                        $sql.=",IFNULL((SELECT SUM(debit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=c.id AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition.")-(SELECT SUM(credit) FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE gld.chart_account_id=c.id AND gl.is_approve=1 AND gl.is_active=1 AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition."),0)";
                        if($year==$d2['year'] && $month==$d2['month']){
                            $daysInMonth = $d2['day'];
                        }else{
                            $daysInMonth = days_in_month($month, $year);
                        }
                    ?>
                    <th colspan="2" style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($daysInMonth, 2, '0', STR_PAD_LEFT); ?>">
                        <?php
                        echo $monthName[$month-1] . '/' . $year;
                        $excelContent .= $monthName[$month-1].'/'.$year."\t\t";
                        if($i%12==0)$incYear++;
                        $count++;
                        ?>
                    </th>
                    <?php
                    }
                    $sql.=" FROM chart_accounts c
                                INNER JOIN chart_account_types ct ON c.chart_account_type_id=ct.id
                                INNER JOIN chart_account_groups cg ON c.chart_account_group_id=cg.id
                            WHERE c.is_active=1
                            ORDER BY account_codes";
                    ?>
                </tr>
                <tr>
                    <?php
                    for($i=4;$i<=$count+3;$i++){
                        if($i==4){
                            $excelContent .= "\n\t\t\t".GENERAL_DEBIT."\t".GENERAL_CREDIT;
                        }else{
                            $excelContent .= "\t".GENERAL_DEBIT."\t".GENERAL_CREDIT;
                        }
                    ?>
                    <th><?php echo GENERAL_DEBIT; ?></th>
                    <th><?php echo GENERAL_CREDIT; ?></th>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                for($i=4;$i<=$count+3;$i++){
                    $totalDebit[$i]=0;
                    $totalCredit[$i]=0;
                }
                $query=mysql_query($sql);
                while($data=mysql_fetch_array($query)){
                    //if($data[$count+2]!=0){
                        $excelContent .= "\n".$data['account_codes']."\t".$data['account_description']."\t".$data['name'];
                    ?>
                    <tr>
                        <td class="first" chart_account_id="<?php echo $data['id']; ?>"><?php echo $data['account_codes']; ?></td>
                        <td style="white-space: nowrap;"><?php echo $data['account_description']; ?></td>
                        <td style="white-space: nowrap;"><?php echo $data['name']; ?></td>
                        <?php
                        for($i=4;$i<=$count+3;$i++){
                            if($data[$i]!=0 && $data[$i]>0){
                                $totalDebit[$i]+=$data[$i];
                            }else{
                                $totalCredit[$i]+=$data[$i];
                            }
                        ?>
                        <td class="link2gl" style="text-align: right;background: #EEF;"><?php echo $data[$i]!=0 && $data[$i]>0?$data[$i]:'-'; ?></td>
                        <td class="link2gl" style="text-align: right;background: #FEE;"><?php echo $data[$i]!=0 && $data[$i]<0?$data[$i]*-1:'-'; ?></td>
                        <?php
                            $excelContent .= "\t".($data[$i]!=0 && $data[$i]>0?$data[$i]:$emptyCell)."\t".($data[$i]!=0 && $data[$i]<0?$data[$i]*-1:$emptyCell);
                        }
                        ?>
                    </tr>
                    <?php
                    //}
                }
                $excelContent .= "\n".TABLE_TOTAL."\t\t";
                ?>
                <tr>
                    <td class="first" colspan="3" style="font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
                    <?php
                    for($i=4;$i<=$count+3;$i++){
                    ?>
                    <td class="link2gl" style="text-align: right;background: #EEF;font-weight: bold;"><?php echo $totalDebit[$i]!=0 && $totalDebit[$i]>0?$totalDebit[$i]:'-'; ?></td>
                    <td class="link2gl" style="text-align: right;background: #FEE;font-weight: bold;"><?php echo $totalCredit[$i]!=0 && $totalCredit[$i]<0?$totalCredit[$i]*-1:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalDebit[$i]!=0 && $totalDebit[$i]>0?$totalDebit[$i]:$emptyCell)."\t".($totalCredit[$i]!=0 && $totalCredit[$i]<0?$totalCredit[$i]*-1:$emptyCell);
                    }
                    ?>
                </tr>
            </table>
        </div>
    </div>
    <?php echo $this->element('report_footer'); ?>
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