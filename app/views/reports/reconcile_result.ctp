<?php

$rnd = rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

// Function
include('includes/function.php');
if(!empty($_POST['statement_ending_date'])){
    $queryReconcile=mysql_query("SELECT id,date FROM reconciles WHERE id=" . $_POST['statement_ending_date']);
    $dataReconcile=mysql_fetch_array($queryReconcile);
}

?>
<style type="text/css">
    tr.highlight:hover {
        background-color: #F0F0F0;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
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
    $msg = '<b style="font-size: 18px;">' . MENU_JOURNAL_ENTRY_MANAGEMENT_RECONCILE . '</b><br /><br />';
    $query=mysql_query("SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id='" . $_POST['reconcile_coa_id'] . "'");
    $data=mysql_fetch_array($query);
    $msg .= $data[0] . ', Period Ending ' . dateShort(@$dataReconcile['date']);
    echo $this->element('/print/header-report',array('msg'=>$msg));
    ?>
    <br />
    <table class="table_report">
        <tr>
            <th>Type</th>
            <th>Date</th>
            <th>Reference</th>
            <th>Name</th>
            <th>Clear</th>
            <th>Amount</th>
            <th>Balance</th>
        </tr>
        <?php
        if(!empty($_POST['statement_ending_date'])){
            $queryBeginningBalance=mysql_query("SELECT SUM(debit)-SUM(credit)
                                                FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                                WHERE is_active=1
                                                    AND is_approve=1
                                                    AND company_id='" . $_POST['company_id'] . "'
                                                    AND branch_id='" . $_POST['branch_id'] . "'
                                                    AND chart_account_id='" . $_POST['reconcile_coa_id'] . "'

                                                    AND is_reconcile=1
                                                    AND reconcile_id!='" . $dataReconcile['id'] . "'
                                                    AND (SELECT date FROM reconciles WHERE id=reconcile_id)<=(SELECT date FROM reconciles WHERE id='" . $dataReconcile['id'] . "')");
            $dataBeginningBalance=mysql_fetch_array($queryBeginningBalance);
        ?>
        <tr class="highlight">
            <td colspan="6" style="font-weight: bold;">Beginning Balance</td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($dataBeginningBalance[0],2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <?php
        $totalClearedTransaction=0;
        $totalUnclearedTransaction=0;
        $totalNewTransaction=0;

        $clearedChecksPayments=0;
        $clearedDepositsCredits=0;
        $conditionCleared=" AND is_active=1
                            AND is_approve=1
                            AND company_id='" . $_POST['company_id'] . "'
                            AND branch_id='" . $_POST['branch_id'] . "'
                            AND chart_account_id='" . $_POST['reconcile_coa_id'] . "'

                            AND is_reconcile=1
                            AND reconcile_id='" . $dataReconcile['id'] . "'";
        $queryClearedChecksPayments=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE credit>0 " . $conditionCleared);
        $queryClearedDepositsCredits=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE debit>0 " . $conditionCleared);

        $unclearedChecksPayments=0;
        $unclearedDepositsCredits=0;
        $conditionUncleared="   AND is_active=1
                                AND is_approve=1
                                AND company_id='" . $_POST['company_id'] . "'
                                AND branch_id='" . $_POST['branch_id'] . "'
                                AND chart_account_id='" . $_POST['reconcile_coa_id'] . "'

                                AND (
                                    reconcile_id IS NULL
                                    OR (
                                        is_reconcile=1
                                        AND reconcile_id!='" . $dataReconcile['id'] . "'
                                        AND date<='" . $dataReconcile['date'] . "'
                                        AND (SELECT date FROM reconciles WHERE id=reconcile_id)>(SELECT date FROM reconciles WHERE id='" . $dataReconcile['id'] . "')
                                    )
                                )
                                AND date<='" . $dataReconcile['date'] . "'";
        $queryUnclearedChecksPayments=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE credit>0 " . $conditionUncleared);
        $queryUnclearedDepositsCredits=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE debit>0 " . $conditionUncleared);

        $newChecksPayments=0;
        $newDepositsCredits=0;
        $conditionNew=" AND is_active=1
                        AND is_approve=1
                        AND company_id='" . $_POST['company_id'] . "'
                        AND branch_id='" . $_POST['branch_id'] . "'
                        AND chart_account_id='" . $_POST['reconcile_coa_id'] . "'

                        AND (
                            reconcile_id IS NULL
                            OR (
                                is_reconcile=1
                                AND reconcile_id!='" . $dataReconcile['id'] . "'
                                AND date<='" . $dataReconcile['date'] . "'
                                AND (SELECT date FROM reconciles WHERE id=reconcile_id)>(SELECT date FROM reconciles WHERE id='" . $dataReconcile['id'] . "')
                            )
                        )
                        AND date>'" . $dataReconcile['date'] . "'";
        $queryNewChecksPayments=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE credit>0 " . $conditionNew);
        $queryNewDepositsCredits=mysql_query("SELECT * FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id WHERE debit>0 " . $conditionNew);
        ?>


        
        <?php if(mysql_num_rows($queryClearedChecksPayments) || mysql_num_rows($queryClearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 40px;">Cleared Transactions</td>
        </tr>
        <?php if(mysql_num_rows($queryClearedChecksPayments)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Checks and Payments</td>
        </tr>
        <?php while($dataClearedChecksPayments=mysql_fetch_array($queryClearedChecksPayments)){ ?>
        <tr class="highlight">
            <td><?php echo $dataClearedChecksPayments['type']; ?></td>
            <td><?php echo dateShort($dataClearedChecksPayments['date']); ?></td>
            <td><?php echo $dataClearedChecksPayments['reference']; ?></td>
            <td>
                <?php
                if($dataClearedChecksPayments['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataClearedChecksPayments['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedChecksPayments['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataClearedChecksPayments['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedChecksPayments['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataClearedChecksPayments['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedChecksPayments['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataClearedChecksPayments['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataClearedChecksPayments['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataClearedChecksPayments['credit']*-1,2); ?></td>
            <td style="text-align: right;"><?php echo number_format($clearedChecksPayments+=$dataClearedChecksPayments['credit']*-1,2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Checks and Payments</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($clearedChecksPayments,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($clearedChecksPayments,2); ?></td>
        </tr>
        <?php } ?>
        <?php if(mysql_num_rows($queryClearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Deposits and Credits</td>
        </tr>
        <?php while($dataClearedDepositsCredits=mysql_fetch_array($queryClearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td><?php echo $dataClearedDepositsCredits['type']; ?></td>
            <td><?php echo dateShort($dataClearedDepositsCredits['date']); ?></td>
            <td><?php echo $dataClearedDepositsCredits['reference']; ?></td>
            <td>
                <?php
                if($dataClearedDepositsCredits['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataClearedDepositsCredits['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedDepositsCredits['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataClearedDepositsCredits['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedDepositsCredits['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataClearedDepositsCredits['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataClearedDepositsCredits['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataClearedDepositsCredits['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataClearedDepositsCredits['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataClearedDepositsCredits['debit'],2); ?></td>
            <td style="text-align: right;"><?php echo number_format($clearedDepositsCredits+=$dataClearedDepositsCredits['debit'],2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Deposits and Credits</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($clearedDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($clearedDepositsCredits,2); ?></td>
        </tr>
        <?php } ?>
        <tr><td>&nbsp;</td></tr>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 40px;">Total Cleared Transactions</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalClearedTransaction=$clearedChecksPayments+$clearedDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalClearedTransaction=$clearedChecksPayments+$clearedDepositsCredits,2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;">Cleared Balance</td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($totalClearedTransaction,2); ?></td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($dataBeginningBalance[0]+$totalClearedTransaction,2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <?php } ?>


        
        <?php if(mysql_num_rows($queryUnclearedChecksPayments) || mysql_num_rows($queryUnclearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 40px;">Uncleared Transactions</td>
        </tr>
        <?php if(mysql_num_rows($queryUnclearedChecksPayments)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Checks and Payments</td>
        </tr>
        <?php while($dataUnclearedChecksPayments=mysql_fetch_array($queryUnclearedChecksPayments)){ ?>
        <tr class="highlight">
            <td><?php echo $dataUnclearedChecksPayments['type']; ?></td>
            <td><?php echo dateShort($dataUnclearedChecksPayments['date']); ?></td>
            <td><?php echo $dataUnclearedChecksPayments['reference']; ?></td>
            <td>
                <?php
                if($dataUnclearedChecksPayments['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataUnclearedChecksPayments['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedChecksPayments['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataUnclearedChecksPayments['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedChecksPayments['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataUnclearedChecksPayments['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedChecksPayments['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataUnclearedChecksPayments['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataUnclearedChecksPayments['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataUnclearedChecksPayments['credit']*-1,2); ?></td>
            <td style="text-align: right;"><?php echo number_format($unclearedChecksPayments+=$dataUnclearedChecksPayments['credit']*-1,2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Checks and Payments</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($unclearedChecksPayments,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($unclearedChecksPayments,2); ?></td>
        </tr>
        <?php } ?>
        <?php if(mysql_num_rows($queryUnclearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Deposits and Credits</td>
        </tr>
        <?php while($dataUnclearedDepositsCredits=mysql_fetch_array($queryUnclearedDepositsCredits)){ ?>
        <tr class="highlight">
            <td><?php echo $dataUnclearedDepositsCredits['type']; ?></td>
            <td><?php echo dateShort($dataUnclearedDepositsCredits['date']); ?></td>
            <td><?php echo $dataUnclearedDepositsCredits['reference']; ?></td>
            <td>
                <?php
                if($dataUnclearedDepositsCredits['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataUnclearedDepositsCredits['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedDepositsCredits['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataUnclearedDepositsCredits['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedDepositsCredits['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataUnclearedDepositsCredits['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataUnclearedDepositsCredits['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataUnclearedDepositsCredits['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataUnclearedDepositsCredits['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataUnclearedDepositsCredits['debit'],2); ?></td>
            <td style="text-align: right;"><?php echo number_format($unclearedDepositsCredits+=$dataUnclearedDepositsCredits['debit'],2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Deposits and Credits</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($unclearedDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($unclearedDepositsCredits,2); ?></td>
        </tr>
        <?php } ?>
        <tr><td>&nbsp;</td></tr>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 40px;">Total Uncleared Transactions</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalUnclearedTransaction=$unclearedChecksPayments+$unclearedDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalUnclearedTransaction=$unclearedChecksPayments+$unclearedDepositsCredits,2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <?php } ?>


        
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;">Register Balance as of <?php echo dateShort($dataReconcile['date']); ?></td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($totalClearedTransaction+$totalUnclearedTransaction,2); ?></td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($dataBeginningBalance[0]+$totalClearedTransaction+$totalUnclearedTransaction,2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>


        
        <?php if(mysql_num_rows($queryNewChecksPayments) || mysql_num_rows($queryNewDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 40px;">New Transactions</td>
        </tr>
        <?php if(mysql_num_rows($queryNewChecksPayments)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Checks and Payments</td>
        </tr>
        <?php while($dataNewChecksPayments=mysql_fetch_array($queryNewChecksPayments)){ ?>
        <tr class="highlight">
            <td><?php echo $dataNewChecksPayments['type']; ?></td>
            <td><?php echo dateShort($dataNewChecksPayments['date']); ?></td>
            <td><?php echo $dataNewChecksPayments['reference']; ?></td>
            <td>
                <?php
                if($dataNewChecksPayments['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataNewChecksPayments['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewChecksPayments['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataNewChecksPayments['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewChecksPayments['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataNewChecksPayments['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewChecksPayments['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataNewChecksPayments['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataNewChecksPayments['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataNewChecksPayments['credit']*-1,2); ?></td>
            <td style="text-align: right;"><?php echo number_format($newChecksPayments+=$dataNewChecksPayments['credit']*-1,2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Checks and Payments</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($newChecksPayments,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($newChecksPayments,2); ?></td>
        </tr>
        <?php } ?>
        <?php if(mysql_num_rows($queryNewDepositsCredits)){ ?>
        <tr class="highlight">
            <td colspan="7" style="font-weight: bold;padding-left: 80px;">Deposits and Credits</td>
        </tr>
        <?php while($dataNewDepositsCredits=mysql_fetch_array($queryNewDepositsCredits)){ ?>
        <tr class="highlight">
            <td><?php echo $dataNewDepositsCredits['type']; ?></td>
            <td><?php echo dateShort($dataNewDepositsCredits['date']); ?></td>
            <td><?php echo $dataNewDepositsCredits['reference']; ?></td>
            <td>
                <?php
                if($dataNewDepositsCredits['customer_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',customer_code,name) FROM customers WHERE id=" . $dataNewDepositsCredits['customer_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewDepositsCredits['vendor_id']!=''){
                    $queryName=mysql_query("SELECT CONCAT_WS(' - ',vendor_code,name) FROM vendors WHERE id=" . $dataNewDepositsCredits['vendor_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewDepositsCredits['employee_id']!=''){
                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataNewDepositsCredits['employee_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }else if($dataNewDepositsCredits['other_id']!=''){
                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataNewDepositsCredits['other_id']);
                    $dataNane=mysql_fetch_array($queryName);
                    echo $dataNane[0];
                }
                ?>
            </td>
            <td style="text-align: center;"><img alt="" src="<?php echo $this->webroot; ?>img/button/<?php echo $dataNewDepositsCredits['is_reconcile']==1?'active':'inactive'; ?>.png" /></td>
            <td style="text-align: right;"><?php echo number_format($dataNewDepositsCredits['debit'],2); ?></td>
            <td style="text-align: right;"><?php echo number_format($newDepositsCredits+=$dataNewDepositsCredits['debit'],2); ?></td>
        </tr>
        <?php } ?>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 80px;">Total Deposits and Credits</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($newDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($newDepositsCredits,2); ?></td>
        </tr>
        <?php } ?>
        <tr><td>&nbsp;</td></tr>
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;padding-left: 40px;">Total New Transactions</td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalNewTransaction=$newChecksPayments+$newDepositsCredits,2); ?></td>
            <td style="text-align: right;border-bottom: 1px solid #000;"><?php echo number_format($totalNewTransaction=$newChecksPayments+$newDepositsCredits,2); ?></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <?php 
            }
        ?>


        
        <tr class="highlight">
            <td colspan="5" style="font-weight: bold;">Ending Balance</td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($totalClearedTransaction+$totalUnclearedTransaction+$totalNewTransaction,2); ?></td>
            <td style="font-weight: bold;text-align: right;"><?php echo number_format($dataBeginningBalance[0]+$totalClearedTransaction+$totalUnclearedTransaction+$totalNewTransaction,2); ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
    <?php echo $this->element('report_footer'); ?>
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