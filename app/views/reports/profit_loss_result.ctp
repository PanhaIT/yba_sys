<?php
include('includes/function.php');

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
$emptyCell = '0';
$companyId = '1';
$companyJorunal = '1';
if(!empty($_POST['branch_id'])){
    $branchId = implode(",", $_POST['branch_id']);
    $branchJorunal = $branchId;
}else{
    $branchId = "SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'];
    $branchJorunal = "all";
}
/**
 * table MEMORY
 * default max_heap_table_size 16MB
 */
$dateFrom = dateConvert($_POST['date_from']);
$dateTo = dateConvert($_POST['date_to']);
$tableName = "general_ledger_detail_pl" . $user['User']['id'];
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `date` date DEFAULT NULL,
                  `chart_account_id` int(11) DEFAULT NULL,
                  `company_id` int(11) DEFAULT NULL,
                  `branch_id` int(11) DEFAULT NULL,
                  `location_id` int(11) DEFAULT NULL,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `customer_id` bigint(20) DEFAULT NULL,
                  `vendor_id` bigint(20) DEFAULT NULL,
                  `employee_id` bigint(20) DEFAULT NULL,
                  `other_id` bigint(20) DEFAULT NULL,
                  `class_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `chart_account_id` (`chart_account_id`),
                  KEY `company_id` (`company_id`),
                  KEY `location_id` (`location_id`),
                  KEY `date` (`date`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName") or die(mysql_error());
$queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),date,chart_account_id,company_id,branch_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id
                            FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                            WHERE gl.is_approve=1 AND gl.is_active=1 AND gld.company_id IN (".$companyId.") AND gld.branch_id IN (".$branchId.") AND is_retained_earnings=0 AND date >= '" . $dateFrom . "' AND date <= '" . $dateTo . "'
                            GROUP BY date,chart_account_id,company_id,branch_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id") or die(mysql_error());
while ($dataCoa = mysql_fetch_array($queryCoa)) {
    mysql_query("INSERT INTO $tableName (
                            date,
                            chart_account_id,
                            company_id,
                            branch_id,
                            location_id,
                            debit,
                            credit,
                            customer_id,
                            vendor_id,
                            employee_id,
                            other_id,
                            class_id
                        ) VALUES (
                            '" . $dataCoa['date'] . "',
                            " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['branch_id']) ? $dataCoa['branch_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['location_id']) ? $dataCoa['location_id'] : "NULL") . ",
                            '" . $dataCoa['SUM(debit)'] . "',
                            '" . $dataCoa['SUM(credit)'] . "',
                            " . (!is_null($dataCoa['customer_id']) ? $dataCoa['customer_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['vendor_id']) ? $dataCoa['vendor_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['employee_id']) ? $dataCoa['employee_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['other_id']) ? $dataCoa['other_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['class_id']) ? $dataCoa['class_id'] : "NULL") . "
                        )") or die(mysql_error());
}

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
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2glpl").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                var dateFrom=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateFrom");
                var dateTo=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateTo");

                var companyId='<?php echo $companyJorunal; ?>';
                var branchId='<?php echo $branchJorunal; ?>';
                var customerId='<?php echo $_POST['customer_id']!=''?$_POST['customer_id']:'null'; ?>';
                var vendorId='<?php echo $_POST['vendor_id']!=''?$_POST['vendor_id']:'null'; ?>';
                var otherId='<?php echo $_POST['other_id']!=''?$_POST['other_id']:'null'; ?>';
                var classId='<?php echo $_POST['class_id']!=''?$_POST['class_id']:'null'; ?>';

                var chart_account_group_id=$(this).siblings("td:eq(0)").attr("chart_account_group_id");
                if(chart_account_group_id){
                    if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            var title    = $(this).attr('title');
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroupDateRange/" + chart_account_group_id + "/" + dateFrom + "/" + dateTo + "/" + companyId + "/" + branchId + "/" + customerId + "/" + vendorId + "/" + otherId + "/" + classId+"?title="+title, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }
                }
                // group expansion
                var chart_account_id=$(this).siblings("td:eq(0)").attr("chart_account_id");
                if(chart_account_id){
                    if(dateFrom && dateTo){
                        $(this).css("cursor", "pointer");
                        $(this).click(function(){
                            var title    = $(this).attr('title');
                            $('#tabs ul li a').not("[href=#]").each(function(index) {
                                if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                    $("#tabs").tabs("select", $(this).attr("href"));
                                    var selIndex = $("#tabs").tabs("option", "selected");
                                    $("#tabs").tabs("remove", selIndex);
                                }
                            });
                            $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTbDateRange/" + chart_account_id + "/" + dateFrom + "/" + dateTo + "/" + companyId + "/" + customerId + "/" + vendorId + "/" + otherId + "/" + classId+"?title="+title, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                        });
                    }
                }
            }
        });
        <?php
        if($_POST['empty'] == '1') {
        ?>
        // Hide Empty row
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

        // Hide Empty Column
        for(i=1;i<$("#<?php echo $printArea; ?> .table_report th").length;i++){
            var isEmpty=true;
            for(j=1;j<$("#<?php echo $printArea; ?> .table_report tr").length;j++){
                obj=$("#<?php echo $printArea; ?> .table_report tr:eq(" + j + ") td:nth(" + i + ")");
                if(obj.text()!='-' && obj.text()!=''){
                    isEmpty=false;
                }
            }
            if(isEmpty==true){
                $("#<?php echo $printArea; ?> .table_report th:nth(" + i + ")").hide();
                for(j=1;j<$("#<?php echo $printArea; ?> .table_report tr").length;j++){
                    $("#<?php echo $printArea; ?> .table_report tr:eq(" + j + ") td:nth(" + i + ")").hide();
                }
            }
        }
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
        // Event Scroll Fire
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
        
        // Event Scroll Layout Center
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

        // Group Expansion
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
        // Button Show All Row
        $(".<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").show();
        });
        // Button Hide All Row
        $(".<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").hide();
        });
        // Action Button Print
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
        // Event Button Export
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/profit_loss.csv", "_blank");
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
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    if(!empty($_POST['branch_id']) || $_POST['customer_id']!='' || $_POST['vendor_id']!='' || $_POST['other_id']!='' || $_POST['class_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if(!empty($_POST['branch_id'])){
        $index = 0;
        $branchName = "";
        $query=mysql_query("SELECT name FROM branches WHERE id IN (".$branchId.")");
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
                    switch($_POST['columns']){
                        case 1://By Day
                            $startDate = dateConvert($_POST['date_from']);
                            $endDate = dateConvert($_POST['date_to']);
                            while (strtotime($startDate) <= strtotime($endDate)) {
                                $d=date_parse_from_format('Y-m-d', $startDate);
                                $month=$d['month'];
                                $year=$d['year'];
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND date='".$startDate."' ".$condition."),0),";
                                    ?>
                                    <th style="text-align: center;" dateFrom="<?php echo $year; ?>-<?php echo $month; ?>-<?php echo $d['day']; ?>" dateTo="<?php echo $year; ?>-<?php echo $month; ?>-<?php echo $d['day']; ?>">
                                        <?php
                                        echo $d['day'] . '/' . $monthName[$month-1] . '/' . $year;
                                        $excelContent .= "\t".$d['day'].'/'.$monthName[$month-1].'/'.$year;
                                        $count++;
                                        ?>
                                    </th>
                                    <?php
                                }
                                $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                            }
                            break;
                        case 2://By Week

                            break;
                        case 3://By Month
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                                $month=$i-($incYear*12);
                                $year=$d1['year']+$incYear;
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND MONTH(date)=".$month." AND YEAR(date)=".$year." ".$condition."),0),";
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
                            break;
                        case 4://By Quarter
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=3){
                                $month=$i-($incYear*12);
                                $quarter=0;
                                if($month<=3){
                                    $quarter=1;
                                    $bMonth=1;
                                    $eMonth=3;
                                }else if($month<=6){
                                    $quarter=2;
                                    $bMonth=4;
                                    $eMonth=6;
                                }else if($month<=9){
                                    $quarter=3;
                                    $bMonth=7;
                                    $eMonth=9;
                                }else{
                                    $quarter=4;
                                    $bMonth=10;
                                    $eMonth=12;
                                }
                                $year=$d1['year']+$incYear;
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date)=".$quarter." AND YEAR(date)=".$year." ".$condition."),0),";
                                    if($year==$d1['year'] && $d1['month']>=$eMonth-2 && $d1['month']<=$eMonth){
                                        $bDay = $d1['day'];
                                    }else{
                                        $bDay = '01';
                                    }
                                    if($year==$d2['year'] && $d2['month']>=$eMonth-2 && $d2['month']<=$eMonth){
                                        $eMonth = $d2['month'];
                                        $daysInMonth = $d2['day'];
                                    }else{
                                        $daysInMonth = days_in_month($eMonth, $year);
                                    }
                                    ?>
                                    <th style="text-align: center;" dateFrom="<?php echo $year; ?>-<?php echo str_pad($bMonth, 2, '0', STR_PAD_LEFT); ?>-<?php echo $bDay; ?>" dateTo="<?php echo $year; ?>-<?php echo str_pad($eMonth, 2, '0', STR_PAD_LEFT); ?>-<?php echo $daysInMonth; ?>">
                                        <?php
                                        echo ($quarter==1?'1st QTR':($quarter==2?'2nd QTR':($quarter==3?'3rd QTR':'4th QTR'))) . '/' . $year;
                                        $excelContent .= "\t".($quarter==1?'1st QTR':($quarter==2?'2nd QTR':($quarter==3?'3rd QTR':'4th QTR'))).'/'.$year;
                                        if($quarter==4)$incYear++;
                                        $count++;
                                        ?>
                                    </th>
                                    <?php
                                }
                            }
                            break;
                        case 5://By Semester
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=6){
                                $month=$i-($incYear*12);
                                $quarter='';
                                if($month<=6){
                                    $quarter='1,2';
                                    $bMonth=1;
                                    $eMonth=6;
                                }else{
                                    $quarter='3,4';
                                    $bMonth=7;
                                    $eMonth=12;
                                }
                                $year=$d1['year']+$incYear;
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND QUARTER(date) IN (".$quarter.") AND YEAR(date)=".$year." ".$condition."),0),";
                                    if($year==$d1['year'] && $d1['month']>=$eMonth-5 && $d1['month']<=$eMonth){
                                        $bDay = $d1['day'];
                                    }else{
                                        $bDay = '01';
                                    }
                                    if($year==$d2['year'] && $d2['month']>=$eMonth-5 && $d2['month']<=$eMonth){
                                        $eMonth = $d2['month'];
                                        $daysInMonth = $d2['day'];
                                    }else{
                                        $daysInMonth = days_in_month($eMonth, $year);
                                    }
                                    ?>
                                    <th style="text-align: center;" dateFrom="<?php echo $year; ?>-<?php echo str_pad($bMonth, 2, '0', STR_PAD_LEFT); ?>-<?php echo $bDay; ?>" dateTo="<?php echo $year; ?>-<?php echo str_pad($eMonth, 2, '0', STR_PAD_LEFT); ?>-<?php echo $daysInMonth; ?>">
                                        <?php
                                        echo ($quarter=='1,2'?'1st Half':'2nd Half') . '/' . $year;
                                        $excelContent .= "\t".($quarter=='1,2'?'1st Half':'2nd Half').'/'.$year;
                                        if($quarter=='3,4')$incYear++;
                                        $count++;
                                        ?>
                                    </th>
                                    <?php
                                }
                            }
                            break;
                        case 6://By Year
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=12){
                                $year=$d1['year']+$incYear;
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND YEAR(date)=".$year." ".$condition."),0),";
                                    if($year==$d1['year']){
                                        $bMonth = $d1['month'];
                                        $bDay = $d1['day'];
                                    }else{
                                        $bMonth = '01';
                                        $bDay = '01';
                                    }
                                    if($year==$d2['year']){
                                        $eMonth = $d2['month'];
                                        $eDay = $d2['day'];
                                    }else{
                                        $eMonth = '12';
                                        $eDay = '31';
                                    }
                                    ?>
                                    <th style="text-align: center;" dateFrom="<?php echo $year; ?>-<?php echo $bMonth; ?>-<?php echo $bDay; ?>" dateTo="<?php echo $year; ?>-<?php echo $eMonth; ?>-<?php echo $eDay; ?>">
                                        <?php
                                        echo $year;
                                        $excelContent .= "\t".$year;
                                        $incYear++;
                                        $count++;
                                        ?>
                                    </th>
                                    <?php
                                }
                            }
                            break;
                        case 7://By Class
                            $queryClass=mysql_query("SELECT id,name FROM classes WHERE is_active=1");
                            while($dataClass=mysql_fetch_array($queryClass)){
                                $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition."),0)");
                                $dataCol=mysql_fetch_array($queryCol);
                                if($dataCol[0]>0){
                                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition."),0),";
                                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND (class_id=".$dataClass['id']." OR location_id IN (SELECT id FROM locations WHERE class_id=".$dataClass['id'].")) ".$condition."),0),";
                            ?>
                            <th style="text-align: center;white-space: nowrap;">
                                <?php
                                echo $dataClass['name'];
                                $excelContent .= "\t".$dataClass['name'];
                                $count++;
                                ?>
                            </th>
                            <?php
                                }
                            }
                            $queryCol=mysql_query("SELECT IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition.")+(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id>10) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition."),0)");
                            $dataCol=mysql_fetch_array($queryCol);
                            if($dataCol[0]>0){
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND ((class_id IS NULL AND location_id IS NULL) OR location_id NOT IN (SELECT id FROM locations WHERE class_id IS NOT NULL)) ".$condition."),0),";
                            ?>
                            <th style="text-align: center;white-space: nowrap;">
                                <?php
                                echo 'Unclassified';
                                $excelContent .= "\t".'Unclassified';
                                $count++;
                                ?>
                            </th>
                            <?php
                            }
                            break;
                    }
                    $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";
                    $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['other_id']!=''?'AND other_id='.$_POST['other_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." ".$condition."),0)";

                    $excelContent .= "\t".TABLE_TOTAL;
                    ?>
                    <th style="text-align: center;" dateFrom="<?php echo $d1['year']; ?>-<?php echo str_pad($d1['month'], 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d1['day'], 2, '0', STR_PAD_LEFT); ?>" dateTo="<?php echo $d2['year']; ?>-<?php echo str_pad($d2['month'], 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d2['day'], 2, '0', STR_PAD_LEFT); ?>"><?php echo TABLE_TOTAL; ?></th>
                </tr>
                <?php
                for($i=0;$i<=$count;$i++){
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
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupIncome['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>"><?php echo $dataGroupIncome['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalRevenue[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupIncome['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupIncome['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glpl" title="<?php echo $dataGroupIncome['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Revenue</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalRevenue[$i]!=''?$totalRevenue[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalRevenue[$i]!=''?$totalRevenue[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupCOGS=" SELECT g.id,g.name
                                FROM chart_account_groups g
                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                WHERE g.is_active=1 AND t.name IN ('Cost of Goods Sold')
                                ORDER BY t.id";
                $queryGroupCOGS=mysql_query($sqlGroupCOGS);
                $excelContent .= "\n";
                while($dataGroupCOGS=mysql_fetch_array($queryGroupCOGS)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupCOGS['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>"><?php echo $dataGroupCOGS['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCOGS['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalCOGS[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupCOGS['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCOGS['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glpl" title="<?php echo $dataGroupCOGS['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Cost of Goods Sold";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Cost of Goods Sold</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalCOGS[$i]!=''?$totalCOGS[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCOGS[$i]!=''?$totalCOGS[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Gross Profit";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Gross Profit</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalGrossProfit[$i]=$totalRevenue[$i]-$totalCOGS[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalGrossProfit[$i]!=0?$totalGrossProfit[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalGrossProfit[$i]!=0?$totalGrossProfit[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupExpense="  SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Expense') AND g.is_depreciation NOT IN (2,3)
                                    ORDER BY t.id";
                $queryGroupExpense=mysql_query($sqlGroupExpense);
                $excelContent .= "\n";
                while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Expenses</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalExpense[$i]!=''?$totalExpense[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalExpense[$i]!=''?$totalExpense[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Ordinary Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Ordinary Income</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherIncome="  SELECT g.id,g.name
                                        FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Income')
                                        ORDER BY t.id";
                $queryGroupOtherIncome=mysql_query($sqlGroupOtherIncome);
                while($dataGroupOtherIncome=mysql_fetch_array($queryGroupOtherIncome)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n\n".$dataGroupOtherIncome['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>"><?php echo $dataGroupOtherIncome['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupOtherIncome['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalOtherRevenue[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupOtherIncome['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherIncome['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glpl" title="<?php echo $dataGroupOtherIncome['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Other Revenue";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Revenue</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalOtherRevenue[$i]!=''?$totalOtherRevenue[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherRevenue[$i]!=''?$totalOtherRevenue[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherExpense=" SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Expense') AND g.is_depreciation NOT IN (2,3)
                                        ORDER BY t.id";
                $queryGroupOtherExpense=mysql_query($sqlGroupOtherExpense);
                $excelContent .= "\n";
                while($dataGroupOtherExpense=mysql_fetch_array($queryGroupOtherExpense)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupOtherExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>"><?php echo $dataGroupOtherExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupOtherExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalOtherExpense[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupOtherExpense['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glpl" title="<?php echo $dataGroupOtherExpense['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Other Expenses";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Expenses</b></td>
                    <?php for($i=0;$i<=$count;$i++){ ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalOtherExpense[$i]!=''?$totalOtherExpense[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherExpense[$i]!=''?$totalOtherExpense[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Net Other Income";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Net Other Income</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Earnings Before Interest & Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Interest & Tax</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
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
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Earnings Before Tax";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Earnings Before Tax</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
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
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupExpense['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>"><?php echo $dataGroupExpense['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupExpense['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count;$i++){
                        $totalExpense[$i]+=$data[$i];?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " ORDER BY account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count;$i++){?>
                        <td class="link2glpl" title="<?php echo $dataGroupExpense['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Profit/Loss for the Year";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Profit/Loss for the Year</b></td>
                    <?php
                    for($i=0;$i<=$count;$i++){
                        $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i]+$totalOtherRevenue[$i]-$totalOtherExpense[$i];
                    ?>
                    <td class="link2glpl" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
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