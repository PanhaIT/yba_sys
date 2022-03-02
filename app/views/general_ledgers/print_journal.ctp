<?php
    include("includes/function.php");
?>
<style type="text/css">
    .table_report th{
        vertical-align: top;
        padding: 10px;
    }
    .table_report td{
        vertical-align: top;
        padding: 10px;
    }
</style>
<div class="print_doc">
    <?php
    $query = mysql_query("  SELECT id,
                                date,
                                reference,
                                (SELECT name FROM companies WHERE id=(SELECT company_id FROM general_ledger_details WHERE general_ledger_id=general_ledgers.id LIMIT 1)) AS company_name
                            FROM general_ledgers WHERE id=" . $id);
    $data = mysql_fetch_array($query);
    $msg = 'JOURNAL';
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $data['reference']));
    ?>
    <div style="height: 30px"></div>
    <table width="100%">
        <tr>
            <td style="width: 600px; vertical-align: top">
                <div style="width:300px; border: 1px solid #000; padding: 5px;">
                    <table>
                        <tr>
                            <td style="font-weight: bold;"><?php echo TABLE_COMPANY; ?>:</td>
                            <td><?php echo $data['company_name']; ?></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td align="right" style="vertical-align: top;">
                <?php echo TABLE_DATE ?>: <b><?php echo date("d/m/Y", strtotime($data['date'])); ?></b>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_report">
                <thead>
                    <tr>
                        <th style="width: 20px !important;" class="first"><?php echo TABLE_NO; ?></th>
                        <th style="width: 240px !important;"><?php echo TABLE_ACCOUNT; ?></th>
                        <th style="width: 240px !important;"><?php echo GENERAL_DESCRIPTION; ?></th>
                        <th style="width: 80px !important;"><?php echo TABLE_CLASS; ?></th>
                        <th style="width: 80px !important;"><?php echo GENERAL_DEBIT; ?></th>
                        <th style="width: 80px !important;"><?php echo GENERAL_CREDIT; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $index=1;
                    $totalDebit=0;
                    $totalCredit=0;
                    $totalAmount=0;
                    $queryDetail=mysql_query("  SELECT
                                                    (SELECT CONCAT(account_codes,' · ',account_description) FROM chart_accounts WHERE id=chart_account_id) AS chart_account_name,
                                                    debit,
                                                    credit,
                                                    memo,
                                                    customer_id,
                                                    vendor_id,
                                                    employee_id,
                                                    other_id,
                                                    class_id
                                                FROM general_ledger_details WHERE general_ledger_id=".$data['id']);
                    while($dataDetail=mysql_fetch_array($queryDetail)){
                        $totalDebit+=$dataDetail['debit'];
                        $totalCredit+=$dataDetail['credit'];
                        $totalAmount+=$dataDetail['debit'];
                    ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo $index++; ?></td>
                        <td><?php echo $dataDetail['chart_account_name']; ?></td>
                        <td><?php echo $dataDetail['memo']; ?></td>
                        <td style="white-space: nowrap;">
                            <?php
                            if($dataDetail['class_id']!=''){
                                $queryName=mysql_query("SELECT name FROM classes WHERE id=" . $dataDetail['class_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }
                            ?>
                        </td>
                        <td style="text-align: right;"><?php echo $dataDetail['debit']!=0?number_format($dataDetail['debit'],2):''; ?></td>
                        <td style="text-align: right;"><?php echo $dataDetail['credit']!=0?number_format($dataDetail['credit'],2):''; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="first" colspan="2"></td>
                        <td colspan="2" style="font-weight: bold;">TOTAL</td>
                        <td style="text-align: right;border-top: 1px solid #000;border-bottom: 3px double #000;"><?php echo number_format($totalDebit,2); ?></td>
                        <td style="text-align: right;border-top: 1px solid #000;border-bottom: 3px double #000;"><?php echo number_format($totalCredit,2); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td><br />&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div>
            <table style="width: 100%">
                <tr>
                    <td style="font-size: 12px; font-weight: bold; height: 50px; vertical-align: top; width: 15%; text-decoration: underline">Amount In Words </td>
                    <td id="toword" style="width: 55%; vertical-align: top;">

                    </td>
                    <td></td>
                </tr>
            </table>
            <?php echo $this->element('report_footer'); ?>
        </div>
        <div style="clear:both"></div>
        <br />
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<?php echo $javascript->link('toword/toword_'.$this->Session->read('lang')); ?>
<script type="text/javascript">
    $(document).ready(function(){
        var string = toWords("<?php echo $totalAmount; ?>");
        $("#toword").text(string.charAt(0).toUpperCase() + string.slice(1) + " only");
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>