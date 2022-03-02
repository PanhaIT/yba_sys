<?php
include('includes/function.php');
$rnd = rand();
$tblName = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
/**
 * Export to Excel
 */
$filename="public/report/sales_top_vendor" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
?>
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
        
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_top_vendor<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $post = implode(',', $_POST);
    $col  = explode(",", $post);
    $condition = '';
    if ($col[1] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= '"' . dateConvert($col[1]) . '" <= DATE(invoice.order_date)';
    }
    if ($col[2] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= '"' . dateConvert($col[2]) . '" >= DATE(invoice.order_date)';
    }
    $condition != '' ? $condition .= ' AND ' : '';
    if ($col[3] == '') {
        $condition .= 'invoice.status!=0';
    } else {
        $condition .= 'invoice.status=' . $col[3];
    }
    if ($col[4] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.company_id=' . $col[4];
    }else{
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
    }
    if ($col[5] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.branch_id=' . $col[5];
    }else{
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
    }
    if ($col[6] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice_detail.product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = '.$col[6].')';
    }

    if ($col[7] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice_detail.product_id = '.$col[7];
    }
    $break  = $col[8];
    $sortBy = $col[9];
    $viewBy = $col[10];
    $lblBy = "Total Invoice";
    if($viewBy == 1){
        $msgSort = 'Top';
    } else {
        $msgSort = 'Bottom';
    }
    if($sortBy == 2){
        $lblBy = " Amounts";
    }
    $msg = '<b style="font-size: 16px;">Sales '.$msgSort.' '.$break.' Customers By '.$lblBy.'</b><br /><br />';
    $excelContent .= 'Sales '.$msgSort.' '.$break.' Customers By '.$lblBy."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_VENDOR_NUMBER."\t".TABLE_VENDOR_NAME."\t".TABLE_TOTAL_INVOICE."\t".TABLE_TOTAL_AMOUNT." ($)";
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_VENDOR_NUMBER; ?></th>
            <th style="width: 300px !important;"><?php echo TABLE_VENDOR_NAME; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_TOTAL_INVOICE; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
        </tr>
        <?php
        $index = 0;
        $vendors = array();
        $query=mysql_query("SELECT
                            'Purchase' AS mod_type,
                            invoice.vendor_id AS ven_id,
                            SUM(invoice_detail.total_cost - invoice_detail.discount_amount) AS total_amount,
                            1 AS total_invoice
                            FROM purchase_orders AS invoice
                            INNER JOIN purchase_order_details AS invoice_detail ON invoice.id = invoice_detail.purchase_order_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, invoice.vendor_id
                            UNION ALL
                            SELECT
                            'Return' AS mod_type,
                            invoice.vendor_id AS ven_id,
                            SUM(invoice_detail.total_price) * -1 AS total_amount,
                            -1 AS total_invoice
                            FROM purchase_returns AS invoice
                            INNER JOIN purchase_return_details AS invoice_detail ON invoice.id = invoice_detail.purchase_return_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, invoice.vendor_id");
        while($data=mysql_fetch_array($query)){
            if (array_key_exists($data['ven_id'], $vendors)) {
                $vendors[$data['ven_id']]['total_invoice'] += $data['total_invoice'];
                $vendors[$data['ven_id']]['total_amount'] += $data['total_amount'];
            } else {
                $vendors[$data['ven_id']]['ven_id'] = $data['ven_id'];
                $vendors[$data['ven_id']]['total_invoice'] = $data['total_invoice'];
                $vendors[$data['ven_id']]['total_amount'] = $data['total_amount'];
            }
        }
        // View By Value ASC/DESC
        if($sortBy == 1){
           $nameSort = 'total_invoice'; 
        } else {
           $nameSort = 'total_amount'; 
        }
        if($viewBy == 1){ // DESC
            arraySortBy($nameSort, $vendors, 'desc');
        } else { // ASC
            arraySortBy($nameSort, $vendors);
        }
        foreach($vendors AS $value){
            if($index == $break){
                break;
            }
            if($value['total_invoice'] > 0 && $value['total_amount'] > 0){
                $sqlCustomer = mysql_query("SELECT vendor_code, name FROM vendors WHERE id = ".$value['ven_id']);
                $rowCustomer = mysql_fetch_array($sqlCustomer);
                $excelContent .= "\n".++$index."\t".$rowCustomer['vendor_code']."\t".$rowCustomer['name']."\t".number_format($value['total_invoice'], 0)."\t".number_format($value['total_amount'], 2);
        ?>
        <tr>
            <td><?php echo $index; ?></td>
            <td><?php echo $rowCustomer['vendor_code']; ?></td>
            <td><?php echo $rowCustomer['name']; ?></td>
            <td style="text-align: center;"><?php echo number_format($value['total_invoice'], 0); ?></td>
            <td style="text-align: right;"><?php echo number_format($value['total_amount'], 2); ?></td>
        </tr>
        <?php
            }
        }
        ?>
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