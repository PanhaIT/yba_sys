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
$filename="public/report/request_by_item_detail_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");

        $(".btnPrintRequestByItemReport").click(function(event){
            event.preventDefault();
            url = "<?php echo $this->base; ?>/request_stocks/printReceipt/" + $(this).attr("rel");
            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
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
            window.open("<?php echo $this->webroot; ?>public/report/request_by_item_detail_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_REQUEST_STOCK_BY_ITEM . '</b><br /><br />';
    $excelContent .= MENU_REPORT_REQUEST_STOCK_BY_ITEM."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_REQUEST_STOCK_DATE."\t".TABLE_REQUEST_STOCK_DATE."\t".TABLE_FROM_WAREHOUSE."\t".TABLE_TO_WAREHOUSE."\t".TABLE_QTY."\t".TABLE_UOM;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_REQUEST_STOCK_DATE; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_REQUEST_STOCK_CODE; ?></th>
            <th><?php echo TABLE_FROM_WAREHOUSE; ?></th>
            <th><?php echo TABLE_TO_WAREHOUSE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
        </tr>
        <?php
        // general condition
        $col = implode(',', $_POST);
        $col = explode(",", $col);
        $condition = 'status!=-1';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        if ($col[3] == '') {
            $condition .= 'status!=0';
        } else {
            $condition .= 'status=' . $col[3];
        }
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'company_id = ' . $col[4];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[5] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'branch_id = ' . $col[5];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[6] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'from_location_group_id=' . $col[6];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'from_location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[7] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'to_location_group_id=' . $col[7];
        }else{
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'to_location_group_id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = '.$user['User']['id'].')';
        }
        if ($col[11] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'created_by=' . $col[11];
        }
        $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrLocationFrom=array();
        $arrLocationTo=array();
        $oldProductId='';
        $oldProductName='';
        $subTotalQty=0;
        $subTotalAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                request_stocks.id,
                                product_id,
                                (SELECT CONCAT_WS(' ',code,name) FROM products WHERE id=product_id) AS product_name,
                                date,
                                code,
                                (SELECT name FROM location_groups WHERE id=from_location_group_id) AS location_from_name,
                                (SELECT name FROM location_groups WHERE id=to_location_group_id) AS location_to_name,
                                qty,
                                qty_uom_id,
                                conversion,
                                (SELECT name FROM uoms WHERE id=qty_uom_id) AS qty_uom_name
                            FROM request_stocks
                                INNER JOIN request_stock_details ON request_stocks.id=request_stock_details.request_stock_id
                            WHERE "
                                . $condition
                                . ($col[8] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $col[8] . ')':'')
                                . ($col[9] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $col[9] :'')
                                . ($col[10] != ''?' AND product_id=' . $col[10] :'')
                                . "
                            ORDER BY product_name,date");
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrLocationFrom[] = $data['location_from_name'];
            $arrLocationTo[] = $data['location_to_name'];
            if($data['product_id']!=$oldProductId){ 
                if($oldProductName!=''){ 
                    $excelContent .= "\n".'Total '.$oldProductName."\t\t\t\t\t".number_format($subTotalQty, 0); ?>
            <tr style="font-weight: bold;">
                <td class="first" colspan="5">Total <?php echo $oldProductName; ?></td>
                <td style="text-align: center;"><?php echo number_format($subTotalQty, 0); ?></td>
                <td></td>
            </tr>
            <?php
                }
                $index=1;
                $subTotalQty=0;
                $excelContent .= "\n".$data['product_name']; 
            ?>
            <tr><td class="first" colspan="7" style="font-weight: bold;"><?php echo $data['product_name']; ?></td></tr>
            <?php 
            } 
            $excelContent .= "\n".$index."\t".dateShort($data['date'])."\t".$data['code']."\t".$data['location_from_name']."\t".$data['location_to_name']."\t".$data['qty']."\t".$data['qty_uom_name']; ?>
            <tr>
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo dateShort($data['date']); ?></td>
                <td><a href="" class="btnPrintRequestByItemReport" rel="<?php echo $data[0]; ?>"><?php echo $data['code']; ?></a></td>
                <td><?php echo $data['location_from_name']; ?></td>
                <td><?php echo $data['location_to_name']; ?></td>
                <td><?php echo $data['qty']; ?></td>
                <td><?php echo $data['qty_uom_name']; ?></td>
            </tr>
        <?php
            $subTotalQty += ($data['qty'] * $data['conversion']);
            $oldProductId=$data['product_id'];
            $oldProductName=$data['product_name'];
        }
        if(mysql_num_rows($query)){
            $excelContent .= "\n".'Total '.$oldProductName."\t\t\t\t\t".number_format($subTotalQty,0); ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="5">Total <?php echo $oldProductName; ?></td>
            <td style="text-align: center;"><?php echo number_format($subTotalQty,0); ?></td>
            <td></td>
        </tr>
        <?php 
        } 
        $excelContent .= "\n\n".''."\t\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrLocationFrom))."\t".sizeof(array_unique($arrLocationTo)); ?>
        <tr><td colspan="10">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="3" style="font-size: 14px;"></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocationFrom)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocationTo)); ?></td>
            <td></td>
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