<?php
    include("includes/function.php");
?>
<div class="print_doc">
    <?php
    $query = mysql_query("  SELECT *,
                                (SELECT name FROM companies WHERE id=company_id) AS company_name,
                                (SELECT CONCAT_WS(' · ',account_codes,account_description) FROM chart_accounts WHERE id=deposit_to) AS deposit_to
                            FROM ar_agings WHERE id=" . $id);
    $data = mysql_fetch_array($query);
    $msg = 'OFFICIAL RECEIPT';
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
                        <tr>
                            <td style="font-weight: bold;"><?php echo TABLE_ACCOUNT; ?>:</td>
                            <td><?php echo $data['deposit_to']; ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;"><?php echo TABLE_MEMO; ?>:</td>
                            <td><?php echo $data['note']; ?></td>
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
            <table class="table_print">
                <tr>
                    <th style="width: 20px !important;" class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 240px !important;"><?php echo TABLE_EMPLOYEE; ?></th>
                    <th><?php echo GENERAL_DESCRIPTION; ?></th>
                    <th style="width: 160px !important;"><?php echo GENERAL_AMOUNT_PAID; ?> ($)</th>
                </tr>
                <?php
                $index=1;
                $totalAmount=0;
                $queryDetail=mysql_query("  SELECT
                                                (SELECT name AS employee_name FROM employees WHERE id=employee_id) AS employee_name,
                                                paid,
                                                memo
                                            FROM ar_aging_details WHERE ar_aging_id=".$data['id']);
                while($dataDetail=mysql_fetch_array($queryDetail)){
                    $totalAmount+=$dataDetail['paid'];
                ?>
                <tr>
                    <td class="first" style="text-align: right;"><?php echo $index++; ?></td>
                    <td><?php echo $dataDetail['employee_name']; ?></td>
                    <td><?php echo $dataDetail['memo']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($dataDetail['paid'],2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="3" style="border-bottom: none; border-left: none;text-align: right;"><b>Grand Total</b></td>
                    <td style="text-align: right;"><?php echo number_format($totalAmount,2); ?></td>
                </tr>
            </table>
        </div>
        <br />
        <div>
            <table style="width: 100%">
                <tr>
                    <td style="font-size: 12px; font-weight: bold; height: 50px; vertical-align: top; width: 15%; text-decoration: underline">Amount In Words </td>
                    <td id="toword" style="width: 55%; vertical-align: top;">
                        
                    </td>
                    <td></td>
                </tr>
            </table>
            <table style="width: 100%;">
                <tr>
                    <td>Approved By..............................</td>
                    <td style="text-align: center;">Received By..............................</td>
                    <td style="text-align: right;">Paid By..............................</td>
                </tr>
            </table>
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
        $("#toword").text(toWords("<?php echo $totalAmount; ?>"));
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>