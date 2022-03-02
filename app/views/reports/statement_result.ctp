<?php

$rnd = rand();
$oTable = "oTable" . $rnd;
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;

include('includes/function.php');
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    var <?php echo $oTable; ?>;
    $(document).ready(function(){
        $("#statementAmountDue").text($("#statementAmountDue2").text());
        $(".formatCurrency").formatCurrency({colorize:true});
        $("#<?php echo $btnPrint; ?>").click(function(){
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_STATEMENT . '</b><br /><br />';
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <table class="table_solid" style="min-width: 300px;">
        <tr>
            <td>To:</td>
        </tr>
        <tr>
            <td style="height: 50px;">
                <?php
                $queryCustomer=mysql_query("SELECT id,CONCAT_WS(' ',customer_code,'-',name) FROM customers WHERE id=" . $_POST['customer_id']);
                $dataCustomer=mysql_fetch_array($queryCustomer);
                echo $dataCustomer[1];
                ?>
            </td>
        </tr>
    </table>
    <br />
    <table class="table_solid" style="width: 100%;">
        <tr>
            <td colspan="2" style="border-left: none;border-top: none;"></td>
            <th style="width: 200px;text-align: center;">Amount Due</th>
            <th style="width: 200px;text-align: center;">Amount Enc.</th>
        </tr>
        <tr>
            <td colspan="2" style="border-left: none;border-top: none;"></td>
            <td id="statementAmountDue" class="formatCurrency" style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <th style="text-align: center;width: 120px;">Date</th>
            <th style="text-align: center;">Transaction</th>
            <th style="text-align: center;">Amount</th>
            <th style="text-align: center;">Balance</th>
        </tr>
        <?php
        $displayBalanceForward = 0;
        $amount = 0;
        $queryTransaction=mysql_query(" SELECT date,type,reference,IF(debit>0,debit,credit*-1) AS amount
                                        FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE gl.is_approve=1 AND gl.is_active=1 AND gld.chart_account_id IN (SELECT id FROM chart_accounts WHERE chart_account_type_id=2)
                                            AND customer_id=" . $_POST['customer_id'] . "
                                            AND date <= '" . dateConvert($_POST['date_to']) . "'
                                        ORDER BY date") or die(mysql_error());
        while($dataTransaction=mysql_fetch_array($queryTransaction)){
            $amount += $dataTransaction['amount'];
            if (strtotime($dataTransaction['date']) < strtotime(dateConvert($_POST['date_from']))) {
                $displayBalanceForward=1;
            }else{
                if($displayBalanceForward == 1){
                    $displayBalanceForward = 0;
        ?>
        <tr>
            <td style="text-align: center;"><?php echo $_POST['date_from']; ?></td>
            <td>Balance forward</td>
            <td class="formatCurrency" style="text-align: right;"></td>
            <td class="formatCurrency" style="text-align: right;">$<?php echo $amount-$dataTransaction['amount']; ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td style="text-align: center;"><?php echo dateShort($dataTransaction['date']); ?></td>
            <td style="width: 50%;"><?php echo $dataTransaction['type']; ?> - <?php echo $dataTransaction['reference']; ?></td>
            <td class="formatCurrency" style="text-align: right;"><?php echo $dataTransaction['amount']; ?></td>
            <td class="formatCurrency" style="text-align: right;"><?php echo $amount; ?></td>
        </tr>
        <?php
            }
        }
        ?>
    </table>
    <br />
    <table class="table_solid" style="width: 100%;">
        <tr>
            <th style="text-align: center;">Current</th>
            <th style="text-align: center;">1 - 30</th>
            <th style="text-align: center;">31 - 60</th>
            <th style="text-align: center;">61 - 90</th>
            <th style="text-align: center;">> 90</th>
            <th style="text-align: center;">Amount Due</th>
        </tr>
        <?php
        $_POST['date'] = $_POST['date_to'];
        $_POST['interval'] = 30;
        $_POST['through'] = 90;

        $arrCoAIdList = array();
        $queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
        while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
            $arrCoAIdList[]=$dataCoAIdList['id'];
        }
        if(sizeof($arrCoAIdList)!=0){
            for($i=0;$i<ceil($_POST['through']/$_POST['interval'])+3;$i++){
                $total_col[$i]=0;
                $glIdListAll[$i]="";
            }

            $total_row=0;
            $colIndex=0;
        ?>
        <tr>
            <?php
            $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_order_id) AS arr_sales_order_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date='" . dateConvert($_POST['date']) . "'
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND credit_memo_receipt_id IS NULL");
            $data1=mysql_fetch_array($query1);
            $query2=mysql_query("   SELECT SUM(credit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND (
                                            sales_order_id IN (" . ($data1['arr_sales_order_id']!=""?$data1['arr_sales_order_id']:-1) . ")
                                            OR
                                            main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                        )");
            $data2=mysql_fetch_array($query2);
            $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(credit_memo_id) AS arr_credit_memo_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date='" . dateConvert($_POST['date']) . "'
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND sales_order_id IS NULL
                                        AND main_gl_id IS NULL");
            $data3=mysql_fetch_array($query3);
            $query4=mysql_query("   SELECT SUM(debit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND (
                                            credit_memo_id IN (" . ($data3['arr_credit_memo_id']!=""?$data3['arr_credit_memo_id']:-1) . ")
                                        )");
            $data4=mysql_fetch_array($query4);
            $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
            $amount=number_format($amount,2,".","");
            $total_row+=$amount;
            $total_col[$colIndex]+=$amount;

            $glIdList=explode(",", ($data1['arr_gl_id']!=''?$data1['arr_gl_id'].',':'').($data2['arr_gl_id']!=''?$data2['arr_gl_id'].',':'').($data3['arr_gl_id']!=''?$data3['arr_gl_id'].',':'').($data4['arr_gl_id']!=''?$data4['arr_gl_id'].',':''));
            $glIdListAll[$colIndex].=implode("-", $glIdList);
            ?>
            <td class="formatCurrency" style="text-align: center;" glIdList="<?php echo implode("-", $glIdList); ?>"><?php echo $amount!=0 && $amount!=''?$amount:'-'; ?></td>
            <?php
            for($i=0;$i<ceil($_POST['through']/$_POST['interval']);$i++){
                $from=$_POST['interval']*$i+1;
                $to=$_POST['interval']*($i+1)<=$_POST['through']?$_POST['interval']*($i+1):$_POST['through'];
                $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_order_id) AS arr_sales_order_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND customer_id=" . $dataCustomer['id'] . "
                                            AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) BETWEEN " . $from . " AND " . $to . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND debit>0
                                            AND credit_memo_receipt_id IS NULL");
                $data1=mysql_fetch_array($query1);
                $query2=mysql_query("   SELECT SUM(credit) AS amount,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND customer_id=" . $dataCustomer['id'] . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND credit>0
                                            AND (
                                                sales_order_id IN (" . ($data1['arr_sales_order_id']!=""?$data1['arr_sales_order_id']:-1) . ")
                                                OR
                                                main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                            )");
                $data2=mysql_fetch_array($query2);
                $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(credit_memo_id) AS arr_credit_memo_id,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND customer_id=" . $dataCustomer['id'] . "
                                            AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) BETWEEN " . $from . " AND " . $to . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND credit>0
                                            AND sales_order_id IS NULL
                                            AND main_gl_id IS NULL");
                $data3=mysql_fetch_array($query3);
                $query4=mysql_query("   SELECT SUM(debit) AS amount,
                                            GROUP_CONCAT(gl.id) AS arr_gl_id
                                        FROM general_ledgers gl
                                            INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1
                                            AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                            AND customer_id=" . $dataCustomer['id'] . "
                                            AND date<='" . dateConvert($_POST['date']) . "'
                                            AND debit>0
                                            AND (
                                                credit_memo_id IN (" . ($data3['arr_credit_memo_id']!=""?$data3['arr_credit_memo_id']:-1) . ")
                                            )");
                $data4=mysql_fetch_array($query4);
                $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
                $amount=number_format($amount,2,".","");
                $total_row+=$amount;
                $total_col[++$colIndex]+=$amount;

                $glIdList=explode(",", ($data1['arr_gl_id']!=''?$data1['arr_gl_id'].',':'').($data2['arr_gl_id']!=''?$data2['arr_gl_id'].',':'').($data3['arr_gl_id']!=''?$data3['arr_gl_id'].',':'').($data4['arr_gl_id']!=''?$data4['arr_gl_id'].',':''));
                $glIdListAll[$colIndex].=implode("-", $glIdList);
            ?>
            <td class="formatCurrency" style="text-align: center;" glIdList="<?php echo implode("-", $glIdList); ?>"><?php echo $amount!=0 && $amount!=''?$amount:'-'; ?></td>
            <?php
            }
            $query1=mysql_query("   SELECT SUM(debit) AS amount,GROUP_CONCAT(sales_order_id) AS arr_sales_order_id,GROUP_CONCAT(main_gl_id) AS arr_main_gl_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) > " . $_POST['through'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND credit_memo_receipt_id IS NULL");
            $data1=mysql_fetch_array($query1);
            $query2=mysql_query("   SELECT SUM(credit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND (
                                            sales_order_id IN (" . ($data1['arr_sales_order_id']!=""?$data1['arr_sales_order_id']:-1) . ")
                                            OR
                                            main_gl_id IN (" . ($data1['arr_main_gl_id']!=""?$data1['arr_main_gl_id']:-1) . ")
                                        )");
            $data2=mysql_fetch_array($query2);
            $query3=mysql_query("   SELECT SUM(credit) AS amount,GROUP_CONCAT(credit_memo_id) AS arr_credit_memo_id,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND DATEDIFF('" . dateConvert($_POST['date']) . "',date) > " . $_POST['through'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND credit>0
                                        AND sales_order_id IS NULL
                                        AND main_gl_id IS NULL");
            $data3=mysql_fetch_array($query3);
            $query4=mysql_query("   SELECT SUM(debit) AS amount,
                                        GROUP_CONCAT(gl.id) AS arr_gl_id
                                    FROM general_ledgers gl
                                        INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1
                                        AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ")
                                        AND customer_id=" . $dataCustomer['id'] . "
                                        AND date<='" . dateConvert($_POST['date']) . "'
                                        AND debit>0
                                        AND (
                                            credit_memo_id IN (" . ($data3['arr_credit_memo_id']!=""?$data3['arr_credit_memo_id']:-1) . ")
                                        )");
            $data4=mysql_fetch_array($query4);
            $amount=$data1['amount']+$data4['amount']-$data2['amount']-$data3['amount'];
            $amount=number_format($amount,2,".","");
            $total_row+=$amount;
            $total_col[++$colIndex]+=$amount;

            $glIdList=explode(",", ($data1['arr_gl_id']!=''?$data1['arr_gl_id'].',':'').($data2['arr_gl_id']!=''?$data2['arr_gl_id'].',':'').($data3['arr_gl_id']!=''?$data3['arr_gl_id'].',':'').($data4['arr_gl_id']!=''?$data4['arr_gl_id'].',':''));
            $glIdListAll[$colIndex].=implode("-", $glIdList);
            ?>
            <td class="formatCurrency" style="text-align: center;" glIdList="<?php echo implode("-", $glIdList); ?>"><?php echo $amount!=0 && $amount!=''?$amount:'-'; ?></td>
            <?php
            $total_col[++$colIndex]+=$total_row;
            ?>
            <td id="statementAmountDue2" class="formatCurrency" style="text-align: center;"><?php echo $total_row!=0 && $total_row!=''?$total_row:'-'; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
</div>
<div style="clear: both;"></div>
<br />
<div class="buttons">
    <button type="button" id="<?php echo $btnPrint; ?>" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
        <?php echo ACTION_PRINT; ?>
    </button>
</div>
<div style="clear: both;"></div>