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
$emptyCell='0';

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
$date = dateConvert($_POST['date_to']);
$tableName = "general_ledger_detail_bs" . $user['User']['id'];
mysql_query("DROP TABLE `".$tableName."`;");
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
if($_POST['columns']==0){
    $queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),chart_account_id,company_id,branch_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id
                                FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "'
                                GROUP BY chart_account_id,company_id,branch_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id") or die(mysql_error());
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
                                '" . $date . "',
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
}else{
    $queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),date,chart_account_id,company_id,branch_id,location_id,customer_id,vendor_id,employee_id,other_id,class_id
                                FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "'
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
$filename="public/report/balance_sheet.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        // btn link to general ledger
        $(".link2glbs").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
                var dateAsOf   = $(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateAsOf");
                var companyId  = '<?php echo $companyJorunal; ?>';
                var branchId   = '<?php echo $branchJorunal; ?>';
                var customerId = '<?php echo $_POST['customer_id']!=''?$_POST['customer_id']:'null'; ?>';
                var vendorId   = '<?php echo $_POST['vendor_id']!=''?$_POST['vendor_id']:'null'; ?>';
                var otherId    = 'null';
                var classId    = '<?php echo $_POST['class_id']!=''?$_POST['class_id']:'null'; ?>';

                var chart_account_group_id=$(this).siblings("td:eq(0)").attr("chart_account_group_id");
                if(chart_account_group_id && dateAsOf){
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
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByGroup/" + chart_account_group_id + "/" + dateAsOf + "/" + companyId + "/" + branchId + "/" + customerId + "/" + vendorId + "/" + otherId + "/" + classId + "?title="+title, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                    });
                }
                // group expansion
                var chart_account_id=$(this).siblings("td:eq(0)").attr("chart_account_id");
                if(chart_account_id && dateAsOf){
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
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByTb/" + chart_account_id + "/" + dateAsOf + "/" + companyId + "/" + customerId + "/" + vendorId + "/" + otherId + "/" + classId + "?title="+title, "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
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
        var arrFixedAssetParent = new Array();
        $("#<?php echo $printArea; ?> .table_report tr.totalCoaParentTitle").each(function(){
            obj=$(this);
            $(this).children(".totalCoaParentValue").each(function(){
                if($(this).text()>0){
                    arrFixedAssetParent.push(obj.attr("parent_id"));
                }
            });
        });
        $("#<?php echo $printArea; ?> .table_report tr.totalCoaParentTitle").each(function(){
            if($.inArray($(this).attr("parent_id"), arrFixedAssetParent) == -1){
                $(this).remove();
            }
        });
        $(".totalCoaParentValue").each(function(){
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true});
            }
        });

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
            window.open("<?php echo $this->webroot; ?>public/report/balance_sheet.csv", "_blank");
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
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    if(!empty($_POST['branch_id']) !='' || $_POST['customer_id']!='' || $_POST['vendor_id']!='' || $_POST['class_id']!='') {
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
        $msg .= ' <b>' . TABLE_CUSTOMER . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_CUSTOMER . ": " . $data[0]."\n";
    }
    if($_POST['vendor_id']!='') {
        $query=mysql_query("SELECT name FROM vendors WHERE id=".$_POST['vendor_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_VENDOR . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_VENDOR . ": " . $data[0]."\n";
    }
    if($_POST['class_id']!='') {
        $query=mysql_query("SELECT name FROM classes WHERE id=".$_POST['class_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <b>' . TABLE_CLASS . '</b>: ' . $data[0]."<br/>";
        $excelContent .= " " . TABLE_CLASS . ": " . $data[0]."\n";
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
                    if($_POST['columns']==0){
                        $d1=date_parse_from_format('d/m/Y', $_POST['date_to']);
                    }else{
                        $d1=date_parse_from_format('d/m/Y', $_POST['date_from']);
                    }
                    $d2=date_parse_from_format('d/m/Y', $_POST['date_to']);
                    $sql="SELECT ";
                    $sqlDetail="SELECT ";
                    switch($_POST['columns']){
                        case 0:
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                                $month=$i-($incYear*12);
                                $year=$d1['year']+$incYear;
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year." ".$condition."),0),";
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d2['day'], 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo $monthName[$month-1] . '/' . $year;
                                $excelContent .= "\t".$monthName[$month-1].'/'.$year;
                                if($i%12==0)$incYear++;
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                        case 1://By Day
                            $startDate = dateConvert($_POST['date_from']);
                            $endDate = dateConvert($_POST['date_to']);
                            while (strtotime($startDate) <= strtotime($endDate)) {
                                $d=date_parse_from_format('Y-m-d', $startDate);
                                $month=$d['month'];
                                $year=$d['year'];
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$d['day'].",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$d['day'].",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$d['day'].",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(MONTH(date)=".$month." AND YEAR(date)=".$year.",DAYOFMONTH(date)<=".$d['day'].",1) AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($d['day'], 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo $d['day'] . '/' . $monthName[$month-1] . '/' . $year;
                                $excelContent .= "\t".$d['day'].'/'.$monthName[$month-1].'/'.$year;
                                $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                        case 2://By Week
                            
                            break;
                        case 3://By Month
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i++){
                                $month=$i-($incYear*12);
                                $year=$d1['year']+$incYear;
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                if($year==$d2['year'] && $month==$d2['month']){
                                    $daysInMonth = $d2['day'];
                                }else{
                                    $daysInMonth = days_in_month($month, $year);
                                }
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($daysInMonth, 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo $monthName[$month-1] . '/' . $year;
                                $excelContent .= "\t".$monthName[$month-1].'/'.$year;
                                if($i%12==0)$incYear++;
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                        case 4://By Quarter
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=3){
                                $month=$i-($incYear*12);
                                if($month<=3){
                                    $month=3;
                                }else if($month<=6){
                                    $month=6;
                                }else if($month<=9){
                                    $month=9;
                                }else{
                                    $month=12;
                                }
                                $year=$d1['year']+$incYear;
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                if($year==$d2['year'] && $d2['month']>=$month-2 && $d2['month']<=$month){
                                    $month = $d2['month'];
                                    $daysInMonth = $d2['day'];
                                }else{
                                    $daysInMonth = days_in_month($month, $year);
                                }
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($daysInMonth, 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo ($month<=3?'1st QTR':($month<=6?'2nd QTR':($month<=9?'3rd QTR':'4th QTR'))) . '/' . $year;
                                $excelContent .= "\t".($month<=3?'1st QTR':($month<=6?'2nd QTR':($month<=9?'3rd QTR':'4th QTR'))).'/'.$year;
                                if($month==12)$incYear++;
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                        case 5://By Semester
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=6){
                                $month=$i-($incYear*12);
                                if($month<=6){
                                    $month=6;
                                }else{
                                    $month=12;
                                }
                                $year=$d1['year']+$incYear;
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                if($year==$d2['year'] && $d2['month']>=$month-5 && $d2['month']<=$month){
                                    $month = $d2['month'];
                                    $daysInMonth = $d2['day'];
                                }else{
                                    $daysInMonth = days_in_month($month, $year);
                                }
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($daysInMonth, 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo ($month<=6?'1st Half':'2nd Half') . '/' . $year;
                                $excelContent .= "\t".($month<=3?'1st QTR':($month<=6?'2nd QTR':($month<=9?'3rd QTR':'4th QTR'))).'/'.$year;
                                if($month==12)$incYear++;
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                        case 6://By Year
                            for($i=$d1['month'];$i<=$d2['month']+($d2['year']-$d1['year'])*12;$i+=12){
                                $month=$i-($incYear*12);
                                $month=12;
                                $year=$d1['year']+$incYear;
                                $sql.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_group_id=|||) AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                $sqlDetail.="IFNULL((SELECT SUM(debit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year.")-(SELECT SUM(credit) FROM $tableName WHERE chart_account_id=||| AND company_id IN (".$companyId.") AND branch_id IN (".$branchId.") ".($_POST['customer_id']!=''?'AND customer_id='.$_POST['customer_id']:'')." ".($_POST['vendor_id']!=''?'AND vendor_id='.$_POST['vendor_id']:'')." ".($_POST['class_id']!=''?'AND class_id='.$_POST['class_id']:'')." AND IF(YEAR(date)=".$year.",MONTH(date)<=".$month.",1) AND YEAR(date)<=".$year."),0),";
                                if($year==$d2['year']){
                                    $month = $d2['month'];
                                    $daysInMonth = $d2['day'];
                                }else{
                                    $daysInMonth = days_in_month($month, $year);
                                }
                                ?>
                                <th style="text-align: center;" dateAsOf="<?php echo $year; ?>-<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>-<?php echo str_pad($daysInMonth, 2, '0', STR_PAD_LEFT); ?>">
                                <?php
                                echo $year;
                                $excelContent .= "\t".$year;
                                if($month==12)$incYear++;
                                $count++;
                                ?>
                                </th>
                            <?php
                            }
                            $sql=substr($sql,0,-1);
                            $sqlDetail=substr($sqlDetail,0,-1);
                            break;
                    }
                    ?>
                </tr>
                <?php
                for($i=0;$i<=$count-1;$i++){
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
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupCurrentAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>"><?php echo $dataGroupCurrentAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        $totalCurrentAsset[$i]+=$data[$i];?>
                        <td class="link2glbs" style="text-align: right;" title="<?php echo $dataGroupCurrentAsset['name']; ?>"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                    $excelContentTmp .= "\n"."    ".$dataGroupDetail['name'];
                ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCurrentAsset['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){?>
                        <td class="link2glbs" title="<?php echo $dataGroupCurrentAsset['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Current Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Current Asset</b></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalCurrentAsset[$i]!=''?$totalCurrentAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCurrentAsset[$i]!=''?$totalCurrentAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupFixedAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Fixed Asset')
                                        ORDER BY t.id";
                $queryGroupFixedAsset=mysql_query($sqlGroupFixedAsset);
                $excelContent .= "\n";
                while($dataGroupFixedAsset=mysql_fetch_array($queryGroupFixedAsset)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupFixedAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>"><?php echo $dataGroupFixedAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupFixedAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        $totalFixedAsset[$i]+=$data[$i];?>
                        <td class="link2glbs" title="<?php echo $dataGroupFixedAsset['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                $oldParentId='';
                $oldParentName='';
                $subTotal=array();
                for($i=0;$i<=$count-1;$i++){
                    $subTotal[$i]=0;
                }

                $isParentRowNotEmpty=0;
                $excelParentContentTmp='';
                
                $sqlGroupDetail="SELECT id,(SELECT id FROM chart_accounts WHERE id=coa.parent_id) AS parent_id,(SELECT CONCAT_WS(' ',account_codes,'·',account_description) FROM chart_accounts WHERE id=coa.parent_id) AS parent_name,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts coa WHERE is_active=1 AND chart_account_group_id=" . $dataGroupFixedAsset['id'] . " ORDER BY parent_name,account_codes";
                $queryGroupDetail=mysql_query($sqlGroupDetail);
                while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n"."        ".$dataGroupDetail['name'];
                ?>
                <?php 
                if ($dataGroupDetail['parent_name']!=$oldParentName) {
                    $excelParentContentTmp .= "\n"."    Total ".$oldParentName;
                ?>
                <tr class="groupDetail totalCoaParentTitle" parent_id="<?php echo $oldParentId; ?>" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;">Total <?php echo $oldParentName; ?></td>
                    <?php 
                    for($i=0;$i<=$count-1;$i++){
                        $excelParentContentTmp .= "\t".($subTotal[$i]!=0 && $subTotal[$i]!=''?$subTotal[$i]:$emptyCell);
                    ?>
                    <td class="totalCoaParentValue" style="text-align: right;"><?php echo $subTotal[$i]!=0 && $subTotal[$i]!=''?$subTotal[$i]:'-';$subTotal[$i]=0; ?></td>
                    <?php
                    }
                    if($isParentRowNotEmpty==1){
                        $excelContent .= $excelParentContentTmp;
                    }
                    ?>
                </tr>
                <?php } ?>
                <?php
                if ($dataGroupDetail['parent_name']!=$oldParentName) {
                    $isParentRowNotEmpty=0;
                    $excelParentContentTmp = "\n"."    ".$dataGroupDetail['parent_name'];
                ?>
                <tr class="groupDetail totalCoaParentTitle" parent_id="<?php echo $dataGroupDetail['parent_id']; ?>" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;"><?php echo $dataGroupDetail['parent_name']; ?></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td style="text-align: right;"></td>
                    <?php } ?>
                </tr>
                <?php } ?>
                <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 50px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){?>
                        <td class="link2glbs" title="<?php echo $dataGroupFixedAsset['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
                    <?php
                        $subTotal[$i] += $dataDetail[$i];
                        $excelContentTmp .= "\t".($dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:$emptyCell);
                        if(($dataDetail[$i]!=0 && $dataDetail[$i])!=''){
                            $isRowNotEmpty=1;
                            $isParentRowNotEmpty=1;
                        }
                    }
                    if($isRowNotEmpty==1){
                        $excelParentContentTmp .= $excelContentTmp;
                    }
                    ?>
                </tr>
                <?php
                    $oldParentId=$dataGroupDetail['parent_id'];
                    $oldParentName=$dataGroupDetail['parent_name'];
                }
                $excelParentContentTmp .= "\n"."    Total ".$oldParentName;
                ?>
                <tr class="groupDetail totalCoaParentTitle" parent_id="<?php echo $oldParentId; ?>" chart_account_group_id="<?php echo $dataGroupFixedAsset['id']; ?>" style="display: none;">
                    <td class="first" style="white-space: nowrap;padding-left: 25px;">Total <?php echo $oldParentName; ?></td>
                    <?php
                    for($i=0;$i<=$count-1;$i++){
                        $excelParentContentTmp .= "\t".($subTotal[$i]!=0 && $subTotal[$i]!=''?$subTotal[$i]:$emptyCell);
                    ?>
                    <td class="totalCoaParentValue" style="text-align: right;"><?php echo $subTotal[$i]!=0 && $subTotal[$i]!=''?$subTotal[$i]:'-';$subTotal[$i]=0; ?></td>
                    <?php
                    }
                    if($isParentRowNotEmpty==1){
                        $excelContent .= $excelParentContentTmp;
                    }
                    ?>
                </tr>
                <?php
                }
                $excelContent .= "\n"."Total Fixed Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Fixed Asset</b></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalFixedAsset[$i]!=''?$totalFixedAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalFixedAsset[$i]!=''?$totalFixedAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupOtherAsset="   SELECT g.id,g.name
                                        FROM chart_account_groups g
                                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                        WHERE g.is_active=1 AND t.name IN ('Other Asset')
                                        ORDER BY t.id";
                $queryGroupOtherAsset=mysql_query($sqlGroupOtherAsset);
                $excelContent .= "\n";
                while($dataGroupOtherAsset=mysql_fetch_array($queryGroupOtherAsset)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupOtherAsset['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherAsset['id']; ?>"><?php echo $dataGroupOtherAsset['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupOtherAsset['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        $totalOtherAsset[$i]+=$data[$i];?>
                        <td class="link2glbs" title="<?php echo $dataGroupOtherAsset['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){?>
                        <td class="link2glbs" title="<?php echo $dataGroupOtherAsset['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Other Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Other Asset</b></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalOtherAsset[$i]!=''?$totalOtherAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalOtherAsset[$i]!=''?$totalOtherAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Asset";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Asset</b></td>
                    <?php
                    for($i=0;$i<=$count-1;$i++){
                        $totalAsset[$i]=$totalCurrentAsset[$i]+$totalFixedAsset[$i]+$totalOtherAsset[$i];
                    ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalAsset[$i]!=0?$totalAsset[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalAsset[$i]!=0?$totalAsset[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupCurrentLiability=" SELECT g.id,g.name
                                            FROM chart_account_groups g
                                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                            WHERE g.is_active=1 AND t.name IN ('Accounts Payable','Credit Card','Other Current Liability')
                                            ORDER BY t.id";
                $queryGroupCurrentLiability=mysql_query($sqlGroupCurrentLiability);
                $excelContent .= "\n";
                while($dataGroupCurrentLiability=mysql_fetch_array($queryGroupCurrentLiability)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupCurrentLiability['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCurrentLiability['id']; ?>"><?php echo $dataGroupCurrentLiability['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupCurrentLiability['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalCurrentLiability[$i]+=$data[$i];?>
                        <td class="link2glbs" title="<?php echo $dataGroupCurrentLiability['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glbs" title="<?php echo $dataGroupCurrentLiability['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Current Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Current Liability</b></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalCurrentLiability[$i]!=''?$totalCurrentLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalCurrentLiability[$i]!=''?$totalCurrentLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupLongTermLiability="    SELECT g.id,g.name
                                                FROM chart_account_groups g
                                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                                WHERE g.is_active=1 AND t.name IN ('Long Term Liability')
                                                ORDER BY t.id";
                $queryGroupLongTermLiability=mysql_query($sqlGroupLongTermLiability);
                $excelContent .= "\n";
                while($dataGroupLongTermLiability=mysql_fetch_array($queryGroupLongTermLiability)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupLongTermLiability['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupLongTermLiability['id']; ?>"><?php echo $dataGroupLongTermLiability['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupLongTermLiability['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalLongTermLiability[$i]+=$data[$i];?>
                        <td class="link2glbs" title="<?php echo $dataGroupLongTermLiability['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glbs" title="<?php echo $dataGroupLongTermLiability['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Total Long Term Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Long Term Liability</b></td>
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalLongTermLiability[$i]!=''?$totalLongTermLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalLongTermLiability[$i]!=''?$totalLongTermLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Liability";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Liability</b></td>
                    <?php
                    for($i=0;$i<=$count-1;$i++){
                        $totalLiability[$i]=$totalCurrentLiability[$i]+$totalLongTermLiability[$i];
                    ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalLiability[$i]!=0?$totalLiability[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalLiability[$i]!=0?$totalLiability[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $sqlGroupEquity="   SELECT g.id,g.name
                                    FROM chart_account_groups g
                                        INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                    WHERE g.is_active=1 AND t.name IN ('Equity')
                                    ORDER BY t.id";
                $queryGroupEquity=mysql_query($sqlGroupEquity);
                $excelContent .= "\n";
                while($dataGroupEquity=mysql_fetch_array($queryGroupEquity)){
                    $isRowNotEmpty=0;
                    $excelContentTmp = '';
                    $excelContentTmp .= "\n".$dataGroupEquity['name'];
                ?>
                <tr class="group" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>">
                    <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupEquity['id']; ?>"><?php echo $dataGroupEquity['name']; ?></td>
                    <?php
                    $query=mysql_query(str_replace("|||", $dataGroupEquity['id'], $sql));
                    $data=mysql_fetch_array($query);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $data[$i]!='')
                            $data[$i]*=-1;
                        $totalEquity[$i]+=$data[$i];?>
                        <td class="link2glbs" title="<?php echo $dataGroupEquity['name']; ?>" style="text-align: right;"><?php echo $data[$i]!=0 && $data[$i]!=''?$data[$i]:'-'; ?></td>
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
                    <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>"><?php echo $dataGroupDetail['name']; ?></td>
                    <?php
                    $queryDetail=mysql_query(str_replace("|||", $dataGroupDetail['id'], $sqlDetail));
                    $dataDetail=mysql_fetch_array($queryDetail);
                    for($i=0;$i<=$count-1;$i++){
                        if($data[$i]!=0 && $dataDetail[$i]!='')
                            $dataDetail[$i]*=-1;?>
                        <td class="link2glbs" title="<?php echo $dataGroupEquity['name']; ?>" style="text-align: right;"><?php echo $dataDetail[$i]!=0 && $dataDetail[$i]!=''?$dataDetail[$i]:'-'; ?></td>
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
                $excelContent .= "\n"."Profit/Loss for the Period";
                ?>
                <tr>
                    <td class="first" style="white-space: nowrap;">Profit/Loss for the Period</td>
                    <?php
                    for($i=0;$i<=$count-1;$i++){
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
                        for($i=0;$i<=$count-1;$i++){
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
                        for($i=0;$i<=$count-1;$i++){
                            $totalCOGS[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$count-1;$i++){
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
                        for($i=0;$i<=$count-1;$i++){
                            $totalExpense[$i]+=$data[$i];
                        }
                        for($i=0;$i<=$count-1;$i++){
                            $totalProfitLoss[$i]=$totalGrossProfit[$i]-$totalExpense[$i];
                        }
                    }
                    for($i=0;$i<=$count-1;$i++){
                        $totalEquity[$i]+=$totalProfitLoss[$i];
                    ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalProfitLoss[$i]!=0?$totalProfitLoss[$i]:'-'; ?></td>
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
                    <?php for($i=0;$i<=$count-1;$i++){ ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalEquity[$i]!=''?$totalEquity[$i]:'-'; ?></td>
                    <?php
                        $excelContent .= "\t".($totalEquity[$i]!=''?$totalEquity[$i]:$emptyCell);
                    }
                    ?>
                </tr>
                <tr><td colspan="<?php echo $count+2; ?>" style="border-right: 0px;">&nbsp;</td></tr>
                <?php
                $excelContent .= "\n\n"."Total Liability & Equity";
                ?>
                <tr style="background: #f4ffab;">
                    <td class="first" style="white-space: nowrap;"><b>Total Liability & Equity</b></td>
                    <?php
                    for($i=0;$i<=$count-1;$i++){
                        $totalLiabilityAndEquity[$i]=$totalLiability[$i]+$totalEquity[$i];
                    ?>
                    <td class="link2glbs" title="" style="text-align: right;"><?php echo $totalLiabilityAndEquity[$i]!=0?$totalLiabilityAndEquity[$i]:'-'; ?></td>
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