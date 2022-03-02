<?php
$sqlSym = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE is_active = 1 LIMIT 1)");
$rowSym = mysql_fetch_array($sqlSym);
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/account_payable.csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $printArea; ?> .table_report td:not(:nth-child(1))").each(function(){
            
            if(!isNaN($(this).text())){
                $(this).text(Number($(this).text()).toFixed(6)).formatCurrency({colorize:true, symbol: '<?php echo $rowSym[0]; ?>'});
                var typeId=$(this).siblings("td:eq(0)").attr("typeId");
                if(typeId){
                    $(this).css("cursor", "pointer");
                    var dateType=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("dateType");
                    var date=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("date");
                    var from=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("from");
                    var to=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("to");
                    var through=$(this).parent().parent().find("tr:first th:eq("+$(this).index()+")").attr("through");
                    $(this).click(function(){
                        $('#tabs ul li a').not("[href=#]").each(function(index) {
                            if($(this).text().indexOf(jQuery.trim("<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>"))!=-1){
                                $("#tabs").tabs("select", $(this).attr("href"));
                                var selIndex = $("#tabs").tabs("option", "selected");
                                $("#tabs").tabs("remove", selIndex);
                            }
                        });
                        $("#tabs").tabs("add", "<?php echo $this->base; ?>/general_ledgers/indexByAging/vendor/" + typeId + "/" + dateType + "/" + date + "/" + from + "/" + to + "/" + through + "/" + $(this).attr("glIdList"), "<?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?>");
                    });
                }
            }
        });
        
        // hide toal empty row
        $("#<?php echo $printArea; ?> .table_report tr.listAPAgin").each(function(){            
            if($(this).find(".totalAPAgin").text()=="-" || $(this).find(".totalAPAgin").text()=="($0.00)"){ 	
                $(this).css("display", "none");
            }
            
        });

        // hide empty row
        $("#<?php echo $printArea; ?> .table_report tr:gt(0)").each(function(){
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
        });

        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/account_payable.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_ACCOUNT_PAYABLE . '</b><br /><br />';
    $excelContent .= MENU_ACCOUNT_PAYABLE."\n\n";
    $msg .= TABLE_DATE . ': ' . $_POST['date'] . '<br /><br />';
    $excelContent .= TABLE_DATE.": " . $_POST['date'] . "\n\n";
    echo $this->element('/print/header-report',array('msg'=>$msg));

    $excelContent .= TABLE_VENDOR."\t"."Current";
    ?>
    <table class="table_report">
        <tr>
            <th><?php echo TABLE_VENDOR; ?></th>
            <?php 
            for($i=0;$i<ceil($_POST['through']/$_POST['interval']);$i++){
                $from = $_POST['interval']*$i;
                $to   = $_POST['interval']*($i+1)<=$_POST['through']?$_POST['interval']*($i+1):$_POST['through'];
            ?>
            <th style="text-align: center; width: 140px !important;" dateType="between" date="<?php echo dateConvert($_POST['date']); ?>" from="<?php echo $from; ?>" to="<?php echo $to; ?>" through="null"><?php echo $from; ?> - <?php echo $to; ?></th>
            <?php 
                $excelContent .= "\t".$from." - ".$to;
                $totalF[$from] = 0;
                $glIdListAll[$from] = '';
            }
            $totalF[$_POST['through']] = 0;
            $glIdListAll[$_POST['through']] = '';
            $totalF['total'] = 0;
            $excelContent .= "\t"."> ".$_POST['through'];
            $excelContent .= "\t".TABLE_TOTAL;
            ?>
            <th style="text-align: center; width: 140px !important;" dateType="through" date="<?php echo dateConvert($_POST['date']); ?>" from="null" to="null" through="<?php echo $_POST['through']; ?>">> <?php echo $_POST['through']; ?></th>
            <th style="text-align: center; width: 140px !important;" dateType="null" date="<?php echo dateConvert($_POST['date']); ?>" from="null" to="null" through="null"><?php echo TABLE_TOTAL; ?></th>
        </tr>
        <?php
        $arrCoAIdList = array();
        $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Payable')");
        while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
            $arrCoAIdList[]=$dataCoAIdList['id'];
        }
        if(sizeof($arrCoAIdList)!=0){
            /* Customize condition */
            $condition = 'is_active=1';
            if($_POST['vgroup_id'] != ''){
                $condition != '' ? $condition .= ' AND ' : '';
                $condition .= 'id IN (SELECT vendor_id FROM vendor_vgroups WHERE vgroup_id=' . $_POST['vgroup_id'] . ')';
            }
            if($_POST['vendor_id'] != ''){
                $condition != '' ? $condition .= ' AND ' : '';
                $condition .= 'id=' . $_POST['vendor_id'];
            }
            $queryVendor=mysql_query("SELECT id,name AS vendor_name FROM vendors WHERE  " . $condition." AND id IN (SELECT vendor_id FROM vendor_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name ASC");
            while($dataVendor=mysql_fetch_array($queryVendor)){
                $totalCol=0;
                $excelContent .= "\n".$dataVendor['vendor_name'];
        ?>
        <tr class="listAPAgin">
            <td style="white-space: nowrap;" typeId="<?php echo $dataVendor['id']; ?>"><?php echo $dataVendor['vendor_name']; ?></td>
            <?php
            for($i=0;$i<ceil($_POST['through']/$_POST['interval']);$i++){
                $from = $_POST['interval']*$i;
                $to   = $_POST['interval']*($i+1)<=$_POST['through']?$_POST['interval']*($i+1):$_POST['through'];
                $query1 = mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_order_id) AS arr_purchase_order_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                            AND vendor_id = " . $dataVendor['id'] . "
                                            AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) BETWEEN " . $from . " AND " . $to . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND credit>0
                                            AND purchase_return_receipt_id IS NULL");
                $data1  = mysql_fetch_array($query1);
                $query2 = mysql_query("   SELECT SUM(debit) AS amount,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                            AND vendor_id=" . $dataVendor['id'] . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND debit>0
                                            AND (
                                                purchase_order_id IN (" . ($data1['arr_purchase_order_id']!=""?$data1['arr_purchase_order_id']:-1) . ")
                                                OR
                                                main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                            )");
                $data2  = mysql_fetch_array($query2);
                $query3 = mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                            AND vendor_id=" . $dataVendor['id'] . "
                                            AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) BETWEEN " . $from . " AND " . $to . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND debit>0
                                            AND purchase_order_id IS NULL
                                            AND main_gl_id IS NULL");
                $data3  = mysql_fetch_array($query3);
                $query4 = mysql_query("   SELECT SUM(credit) AS amount,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                            AND vendor_id=" . $dataVendor['id'] . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND credit>0
                                            AND (
                                                purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                            )");
                $data4  = mysql_fetch_array($query4);
                $amount = $data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
                $total  = number_format($amount,2,".","");
                $totalCol+=$total;
                $totalF[$from]+=$total;

                $excelContent .= "\t".($total!=0 && $total!=''?$total:'-');

                $glIdList = explode(",", ($data1['arr_gl_id']!=''?$data1['arr_gl_id'].',':'').($data2['arr_gl_id']!=''?$data2['arr_gl_id'].',':'').($data3['arr_gl_id']!=''?$data3['arr_gl_id'].',':'').($data4['arr_gl_id']!=''?$data4['arr_gl_id'].',':''));
                $glIdListAll[$from] .= implode("-", $glIdList);
            ?>
            <td style="text-align: right;" glIdList="<?php echo implode("-", $glIdList); ?>"><?php echo $total!=0 && $total!=''?$total:'-'; ?></td>
            <?php
            }
            $query1 = mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(purchase_order_id) AS arr_purchase_order_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                        AND vendor_id=" . $dataVendor['id'] . "
                                        AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) > " . $_POST['through'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND purchase_return_receipt_id IS NULL");
            $data1  = mysql_fetch_array($query1);
            $query2 = mysql_query("   SELECT SUM(debit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                        AND vendor_id=" . $dataVendor['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND (
                                            purchase_order_id IN (" . ($data1['arr_purchase_order_id']!=""?$data1['arr_purchase_order_id']:-1) . ")
                                            OR
                                            main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                        )");
            $data2  = mysql_fetch_array($query2);
            $query3 = mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(purchase_return_id) AS arr_purchase_return_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                        AND vendor_id=" . $dataVendor['id'] . "
                                        AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) > " . $_POST['through'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND purchase_order_id IS NULL
                                        AND main_gl_id IS NULL");
            $data3  = mysql_fetch_array($query3);
            $query4 = mysql_query("   SELECT SUM(credit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")
                                        AND vendor_id=" . $dataVendor['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND (
                                            purchase_return_id IN (" . ($data3['arr_purchase_return_id']!=""?$data3['arr_purchase_return_id']:-1) . ")
                                        )");
            $data4  = mysql_fetch_array($query4);
            $amount = $data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
            $total  = number_format($amount,2,".","");
            $totalCol += $total;
            $totalF[$_POST['through']] += $total;

            $excelContent .= "\t".($total!=0 && $total!=''?$total:'-');

            $glIdList=explode(",", ($data1['arr_gl_id']!=''?$data1['arr_gl_id'].',':'').($data2['arr_gl_id']!=''?$data2['arr_gl_id'].',':'').($data3['arr_gl_id']!=''?$data3['arr_gl_id'].',':'').($data4['arr_gl_id']!=''?$data4['arr_gl_id'].',':''));
            $glIdListAll[$_POST['through']].=implode("-", $glIdList);
            ?>
            <td style="text-align: right;" glIdList="<?php echo implode("-", $glIdList); ?>"><?php echo $total!=0 && $total!=''?$total:'-'; ?></td>
            <?php
            $totalF['total'] += $totalCol;

            $excelContent .= "\t".($totalCol!=0 && $totalCol!=''?$totalCol:'-');
            ?>
            <td class="totalAPAgin" style="text-align: right;"><?php echo $totalCol!=0 && $totalCol!=''?$totalCol:'-'; ?></td>
        </tr>
        <?php
            }
        }
        $excelContent .= "\n"."Total";
        ?>
        <tr>
            <td class="first" style="white-space: nowrap;" typeId="all"><b>Total</b></td>
            <?php 
            for($i=0;$i<ceil($_POST['through']/$_POST['interval']);$i++){ 
                $from = $_POST['interval']*$i;
            ?>
            <td style="text-align: right;" glIdList="<?php echo $glIdListAll[$from]; ?>"><?php echo isset($totalF[$from]) && $totalF[$from]!=0 && $totalF[$from]!=''?$totalF[$from]:'-'; ?></td>
            <?php
                $excelContent .= "\t".(isset($totalF[$from]) && $totalF[$from]!=0 && $totalF[$from]!=''?$totalF[$from]:'-');
            }
            // Through
            ?>
            <td style="text-align: right;" glIdList="<?php echo $glIdListAll[$_POST['through']]; ?>"><?php echo isset($totalF[$_POST['through']]) && $totalF[$_POST['through']]!=0 && $totalF[$_POST['through']]!=''?$totalF[$_POST['through']]:'-'; ?></td>
            <?php
            $excelContent .= "\t".(isset($totalF[$_POST['through']]) && $totalF[$_POST['through']]!=0 && $totalF[$_POST['through']]!=''?$totalF[$_POST['through']]:'-');
            // Total
            ?>
            <td style="text-align: right;"><?php echo isset($totalF['total']) && $totalF['total']!=0 && $totalF['total']!=''?$totalF['total']:'-'; ?></td>
            <?php
            $excelContent .= "\t".(isset($totalF['total']) && $totalF['total']!=0 && $totalF['total']!=''?$totalF['total']:'-');
            ?>
        </tr>
    </table>
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