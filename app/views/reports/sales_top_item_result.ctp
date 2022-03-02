<?php

$rnd = rand();
$tblName = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/sales_top_item" . $this->Session->id(session_id()) . ".csv";
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
            window.open("<?php echo $this->webroot; ?>public/report/sales_top_item<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
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
        $condition .= 'invoice.customer_id IN (SELECT customer_id FROM customer_cgroups WHERE cgroup_id = '.$col[6].')';
    }

    if ($col[7] != '') {
        $condition != '' ? $condition .= ' AND ' : '';
        $condition .= 'invoice.customer_id = '.$col[7];
    }
    $break  = $col[8];
    $sortBy = $col[9];
    $viewBy = $col[10];
    $lblBy = "Quantity";
    if($viewBy == 1){
        $msgSort = 'Top';
    } else {
        $msgSort = 'Bottom';
    }
    if($sortBy == 2){
        $lblBy = " Amounts";
    }
    $msg = '<b style="font-size: 18px;">Sales '.$msgSort.' '.$break.' Items By '.$lblBy.'</b><br /><br />';
    $excelContent .= 'Sales '.$msgSort.' '.$break.' Items By '.$lblBy."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_SKU."\t".TABLE_PRODUCT_NAME."\t".TABLE_TOTAL_INVOICE."\t".TABLE_TOTAL_QTY."\t".TABLE_AVG_COST_AMOUNT." ($)\t".TABLE_TOTAL_AMOUNT." ($)";
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_SKU; ?></th>
            <th style="width: 300px !important;"><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_TOTAL_INVOICE; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_TOTAL_QTY; ?></th>
            <th style="width: 200px !important;"><?php echo TABLE_AVG_COST_AMOUNT; ?> ($)</th>
            <th style="width: 200px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?> ($)</th>
        </tr>
        <?php
        $index = 0;
        $items = array();
        $query=mysql_query("SELECT
                            'Invoice' AS mod_type,
                            sales_order_details.product_id AS p_id,
                            SUM((sales_order_details.qty + sales_order_details.qty_free) * sales_order_details.conversion) AS total_qty,
                            SUM(sales_order_details.total_price - sales_order_details.discount_amount) AS total_amount,
                            1 AS total_invoice
                            FROM sales_orders AS invoice
                            INNER JOIN sales_order_details ON invoice.id = sales_order_details.sales_order_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, sales_order_details.product_id
                            UNION ALL
                            SELECT
                            'CM' AS mod_type,
                            credit_memo_details.product_id AS p_id,
                            SUM((credit_memo_details.qty + credit_memo_details.qty_free) * credit_memo_details.conversion) * -1 AS total_qty,
                            SUM(credit_memo_details.total_price - credit_memo_details.discount_amount) * -1 AS total_amount,
                            -1 AS total_invoice
                            FROM credit_memos AS invoice
                            INNER JOIN credit_memo_details ON invoice.id = credit_memo_details.credit_memo_id
                            WHERE ". $condition . "
                            GROUP BY invoice.id, credit_memo_details.product_id");
        while($data=mysql_fetch_array($query)){
            if (array_key_exists($data['p_id'], $items)) {
                $items[$data['p_id']]['total_invoice'] += $data['total_invoice'];
                $items[$data['p_id']]['total'] += $data['total_qty'];
                $items[$data['p_id']]['total_amount'] += $data['total_amount'];
            } else {
                $items[$data['p_id']]['p_id'] = $data['p_id'];
                $items[$data['p_id']]['total_invoice'] = $data['total_invoice'];
                $items[$data['p_id']]['total'] = $data['total_qty'];
                $items[$data['p_id']]['total_amount'] = $data['total_amount'];
            }
        }
        // View By Value ASC/DESC
        if($sortBy == 1){
           $nameSort = 'total'; 
        } else {
           $nameSort = 'total_amount'; 
        }
        if($viewBy == 1){ // DESC
            arraySortBy($nameSort, $items, 'desc');
        } else { // ASC
            arraySortBy($nameSort, $items);
        }
        foreach($items AS $value){
            if($index == $break){
                break;
            }
            if($value['total'] > 0){
                $sqlProduct = mysql_query("SELECT code, name, small_val_uom, price_uom_id FROM products WHERE id = ".$value['p_id']);
                $rowProduct = mysql_fetch_array($sqlProduct);
                $sqlAvg = mysql_query("SELECT avg_cost FROM inventory_valuations WHERE pid = ".$value['p_id']." AND date >= '".dateConvert($col[1])."' AND date <= '".dateConvert($col[2])."' ORDER BY id DESC LIMIT 1");
                $rowAvg = mysql_fetch_array($sqlAvg);
                $sqlSmall = mysql_query("SELECT abbr FROM uoms WHERE id = IFNULL((SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = ".$rowProduct['price_uom_id']." AND is_active = 1 AND is_small_uom = 1 ORDER BY id DESC LIMIT 1),".$rowProduct['price_uom_id'].")");
                $rowSmall = mysql_fetch_array($sqlSmall);
                $totalAvg = ($value['total'] / $rowProduct['small_val_uom']) * $rowAvg['avg_cost'];
                $excelContent .= "\n".++$index."\t".$rowProduct['code']."\t".$rowProduct['name']."\t".number_format($value['total_invoice'], 0)."\t".number_format($value['total'], 2)." ".$rowSmall['abbr']."\t".number_format($totalAvg, 2)."\t".number_format($value['total_amount'], 2);
        ?>
        <tr>
            <td><?php echo $index; ?></td>
            <td><?php echo $rowProduct['code']; ?></td>
            <td><?php echo $rowProduct['name']; ?></td>
            <td style="text-align: center;"><?php echo number_format($value['total_invoice'], 0); ?></td>
            <td style="text-align: center;"><?php echo number_format($value['total'], 2)." ".$rowSmall['abbr']; ?></td>
            <td style="text-align: right;"><?php echo number_format($totalAvg, 2); ?></td>
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