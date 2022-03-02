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
    $dataDetail=mysql_fetch_array($queryDetail);
    $msg = 'DEPOSIT';
    echo $this->element('/print/header-barcode', array('msg' => $msg, 'barcode' => $data['reference']));
    ?>
    <div style="height: 30px"></div>
    <table width="100%">
        <tr>
            <td style="width: 600px; vertical-align: top">
                <div style="width:450px; border: 1px solid #000; padding: 5px;">
                    <table>
                        <tr>
                            <td style="font-weight: bold;"><?php echo TABLE_COMPANY; ?>:</td>
                            <td><?php echo $data['company_name']; ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Cash & Bank Account:</td>
                            <td><?php echo $dataDetail['chart_account_name']; ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Pay to the order of:</td>
                            <td>
                                <?php
                                if($dataDetail['customer_id']!=''){
                                    $queryName=mysql_query("SELECT CONCAT_WS(' ',customer_code,name) FROM customers WHERE id=" . $dataDetail['customer_id']);
                                    $dataName=mysql_fetch_array($queryName);
                                    echo $dataName[0];
                                }else if($dataDetail['vendor_id']!=''){
                                    $queryName=mysql_query("SELECT name FROM vendors WHERE id=" . $dataDetail['vendor_id']);
                                    $dataName=mysql_fetch_array($queryName);
                                    echo $dataName[0];
                                }else if($dataDetail['employee_id']!=''){
                                    $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataDetail['employee_id']);
                                    $dataName=mysql_fetch_array($queryName);
                                    echo $dataName[0];
                                }else if($dataDetail['other_id']!=''){
                                    $queryName=mysql_query("SELECT name FROM others WHERE id=" . $dataDetail['other_id']);
                                    $dataName=mysql_fetch_array($queryName);
                                    echo $dataName[0];
                                }
                                ?>
                            </td>
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
                        <th style="width: 80px !important;"><?php echo GENERAL_RECEIVE_FROM; ?></th>
                        <th style="width: 240px !important;"><?php echo GENERAL_FROM_ACCOUNT; ?></th>
                        <th style="width: 80px !important;"><?php echo GENERAL_AMOUNT; ?></th>
                        <th style="width: 80px !important;"><?php echo TABLE_MEMO; ?></th>
                        <th style="width: 80px !important;"><?php echo TABLE_CLASS; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $index=1;
                    $totalAmount=0;
                    while($dataDetail=mysql_fetch_array($queryDetail)){
                        $totalAmount+=$dataDetail['credit']!=0?$dataDetail['credit']:$dataDetail['debit']*-1;
                    ?>
                    <tr>
                        <td class="first" style="text-align: right;"><?php echo $index++; ?></td>
                        <td>
                            <?php
                            if($dataDetail['customer_id']!=''){
                                $queryName=mysql_query("SELECT CONCAT_WS(' ',customer_code,name,name_kh) FROM customers WHERE id=" . $dataDetail['customer_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }else if($dataDetail['vendor_id']!=''){
                                $queryName=mysql_query("SELECT name FROM vendors WHERE id=" . $dataDetail['vendor_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }else if($dataDetail['employee_id']!=''){
                                $queryName=mysql_query("SELECT name FROM employees WHERE id=" . $dataDetail['employee_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }else if($data['other_id']!=''){
                                $queryName=mysql_query("SELECT name FROM others WHERE id=" . $data['other_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }
                            ?>
                        </td>
                        <td><?php echo $dataDetail['chart_account_name']; ?></td>
                        <td style="text-align: right;"><?php echo $dataDetail['credit']!=0?number_format($dataDetail['credit'],2):number_format($dataDetail['debit']*-1,2); ?></td>
                        <td><?php echo $dataDetail['memo']; ?></td>
                        <td>
                            <?php
                            if($dataDetail['class_id']!=''){
                                $queryName=mysql_query("SELECT name FROM classes WHERE id=" . $dataDetail['class_id']);
                                $dataName=mysql_fetch_array($queryName);
                                echo $dataName[0];
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
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
        $("#toword").text(toWords("<?php echo $totalAmount; ?>"));
        $(document).dblclick(function(){
            window.close();
        });
    });
</script>