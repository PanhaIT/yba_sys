<?php
include("includes/function.php");
$sqlSymbol = mysql_query("SELECT symbol FROM currency_centers WHERE id IN (SELECT currency_center_id FROM companies WHERE id = 1)");
$rowSymbol = mysql_fetch_array($sqlSymbol);
$symbol    = $rowSymbol[0];
$rnd       = rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$group     = $_POST['view_by'];
$branch    = $_POST['branch'];
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".dataTables_length").hide();
            $(".dataTables_filter").hide();
            $(".dataTables_paginate").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".dataTables_length").show();
            $(".dataTables_filter").show();
            $(".dataTables_paginate").show();
        });
        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/total_sales_summary<?php echo $group. $user['User']['id'] ?>.csv", "_blank");
        });
    });
</script>
<?php
$condtion  = "";
if($branch != ''){
    $sqlBranch = mysql_query("SELECT id, name FROM branches WHERE id = ".$branch);
    $condtion .= "sales.branch_id = ".$branch." AND ";
} else {
    $condtion .= "sales.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']}) AND ";
    $sqlBranch = mysql_query("SELECT id, name FROM branches WHERE id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})");
}
$dateHeader = array();
$datas = array();
if($group == 1){
    for($i = 1; $i <= 12; $i++){
        $month        = str_pad($i, 2, '0', STR_PAD_LEFT);
        $dateHeader[] = $month;
    }
    $dateInput = $_POST['year'];
    $sqlTrans  = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            MONTH(sales.order_date) AS date,
                            sales.branch_id AS branch
                            FROM sales_orders AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY branch, date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            MONTH(sales.order_date) AS date,
                            sales.branch_id AS branch
                            FROM credit_memos AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) = '{$dateInput}' AND sales.status > 0 GROUP BY branch, date
                            ORDER BY branch, date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = str_pad($rowTrans['date'], 2, '0', STR_PAD_LEFT);
            if (array_key_exists($rowTrans['branch'], $datas)){
                if (array_key_exists($dateTrans, $datas[$rowTrans['branch']])){
                    $datas[$rowTrans['branch']][$dateTrans] += $rowTrans['total_amount'];
                } else {
                    $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
                }
            } else {
                $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
} else if ($group == 2){
    $sqlYearStart = mysql_query("SELECT YEAR(date) FROM inventories GROUP BY YEAR(date) ORDER BY YEAR(date) ASC LIMIT 1;");
    if(mysql_num_rows($sqlYearStart)){
        $rowYearStart = mysql_fetch_array($sqlYearStart);
        $start = $rowYearStart[0];
    } else {
        $start = date("Y");
    }
    $yearNow = date("Y");
    for($i = $start; $i <= $yearNow; $i++){
        $dateHeader[] = $i;
    }
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            YEAR(sales.order_date) AS date,
                            sales.branch_id AS branch
                            FROM sales_orders AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) >= '{$start}' AND YEAR(sales.order_date) <= '{$yearNow}' AND sales.status > 0 GROUP BY branch, date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            YEAR(sales.order_date) AS date,
                            sales.branch_id AS branch
                            FROM credit_memos AS sales
                            WHERE ".$condtion."YEAR(sales.order_date) >= '{$start}' AND YEAR(sales.order_date) <= '{$yearNow}' AND sales.status > 0 GROUP BY branch, date
                            ORDER BY branch, date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            if (array_key_exists($rowTrans['branch'], $datas)){
                if (array_key_exists($dateTrans, $datas[$rowTrans['branch']])){
                    $datas[$rowTrans['branch']][$dateTrans] += $rowTrans['total_amount'];
                } else {
                    $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
                }
            } else {
                $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
} else {
    $dateLists = listDays($_POST['date_from'], $_POST['date_to']);
    $i = 1;
    $count = count($dateLists);
    foreach($dateLists AS $dateList){
        $name = date("d/M", strtotime($dateList));
        $dateHeader[] = $dateList;
    }
    $sqlTrans = mysql_query("SELECT
                            SUM(sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)+IFNULL(sales.bank_charge_amount,0)) AS total_amount,
                            sales.order_date AS date,
                            sales.branch_id AS branch
                            FROM sales_orders AS sales
                            WHERE ".$condtion."sales.order_date >= '{$_POST['date_from']}' AND sales.order_date <= '{$_POST['date_to']}' AND sales.status > 0 GROUP BY branch, date
                            UNION ALL
                            SELECT 
                            SUM(((sales.total_amount-IFNULL(sales.discount,0)+IFNULL(sales.total_vat,0)) * -1)) AS total_amount,
                            sales.order_date AS date,
                            sales.branch_id AS branch
                            FROM credit_memos AS sales
                            WHERE ".$condtion."sales.order_date >= '{$_POST['date_from']}' AND sales.order_date <= '{$_POST['date_to']}' AND sales.status > 0 GROUP BY branch, date
                            ORDER BY branch, date");
    if(mysql_num_rows($sqlTrans)){
        while($rowTrans = mysql_fetch_array($sqlTrans)){
            $dateTrans = $rowTrans['date'];
            if (array_key_exists($rowTrans['branch'], $datas)){
                if (array_key_exists($dateTrans, $datas[$rowTrans['branch']])){
                    $datas[$rowTrans['branch']][$dateTrans] += $rowTrans['total_amount'];
                } else {
                    $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
                }
            } else {
                $datas[$rowTrans['branch']][$dateTrans] = $rowTrans['total_amount'];
            }
        }
    }
}
$title = MENU_REPORT_INVOICE." (Summary)";
if(!empty($_POST['year']) && $group == 1){
    $title .= " Year: ".$_POST['year'];
}
echo $this->element('/print/header-report',array('msg' => $title));
$filename = "public/report/total_sales_summary".$group. $user['User']['id'] . ".csv";
$fp = fopen($filename, "wb");
$excelContent = $title;
$excelContent .= "\n\n".TABLE_NO."\t" . TABLE_BRANCH;
$totalTblWidth = (COUNT($dateHeader) * 120) + 500;
?>
<br />
<div id="<?php echo $printArea; ?>">
    <div id="dynamic">
        <table class="table" style="width: <?php echo $totalTblWidth; ?>px;">
            <thead>
                <tr>
                    <th class="first" style="width: 50px;"><?php echo TABLE_NO; ?></th>
                    <th style="width: 300px;"><?php echo TABLE_BRANCH; ?></th>
                    <?php
                    foreach($dateHeader AS $header){
                        if($group == 1){
                            $dateList = date("Y")."-".$header."-01";
                            $name = date("M", strtotime($dateList));
                        } else if($group == 3){
                            $name = date("d/M/Y", strtotime($header));
                        } else {
                            $name = $header;
                        }
                        $excelContent .= "\t".$name;
                    ?>
                    <th style="width: 120px !important; text-align: right;"><?php echo $name; ?></th>
                    <?php
                    }
                    ?>
                    <th style="width: 150px !important; text-align: right;">Sub-Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $excelContent .= "\tSub-Total";
                if(empty($datas)){
                ?>
                <tr>
                    <td colspan="<?php echo COUNT($dateHeader) + 3; ?>" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
                <?php
                } else {
                    $j=0;
                    $totalFooter = 0;
                    $totalDate   = array();
                    while($branch = mysql_fetch_array($sqlBranch)){
                ?>
                <tr>
                    <td class="first">
                        <?php 
                        echo ++$j; 
                        $excelContent .= "\n".$j;
                        ?>
                    </td>
                    <td>
                        <?php 
                        echo $branch['name']; 
                        $excelContent .= "\t".$branch['name'];
                        ?>
                    </td>
                    <?php
                    $totalSale = 0;
                    foreach($dateHeader AS $header){
                        if($j == 1){
                            $totalDate[$header] = 0;
                        }
                        if(!empty($datas[$branch['id']])){
                            if(!empty($datas[$branch['id']][$header])){
                                $amount = $datas[$branch['id']][$header];
                                $excelContent .= "\t ".number_format($amount, 2)." ".$symbol;
                                $totalSale += $amount;
                                $totalDate[$header] += $amount;
                    ?>
                    <td style="text-align: right;"><?php echo number_format($amount, 2)." ".$symbol; ?></td>
                    <?php                
                            } else {
                                $excelContent .= "\t0.00 ".$symbol;
                    ?>
                    <td style="text-align: right;"><?php echo "0.00 ".$symbol; ?></td>
                    <?php                 
                            }
                        } else {
                            $excelContent .= "\t0.00 ".$symbol;
                    ?>
                    <td style="text-align: right;"><?php echo "0.00 ".$symbol; ?></td>
                    <?php        
                        }
                    }
                    $totalFooter  += $totalSale;
                    $excelContent .= "\t ".number_format($totalSale, 2)." ".$symbol;
                    ?>
                    <td style="text-align: right;"><?php echo number_format($totalSale, 2)." ".$symbol; ?></td>
                </tr>
                <?php
                    }
                    $excelContent .= "\n\tTotal";
                ?>
                <tr>
                    <td style="border: none; font-size: 14px; font-weight: bold;" colspan="2">Total</td>
                    <?php
                    foreach($dateHeader AS $header){
                        $excelContent .= "\t ".number_format($totalDate[$header], 2)." ".$symbol;
                    ?>
                    <td style="border: none; text-align: right; font-size: 14px; font-weight: bold;"><?php echo number_format($totalDate[$header], 2)." ".$symbol; ?></td>
                    <?php
                    }
                    $excelContent .= "\t ".number_format($totalFooter, 2)." ".$symbol;
                    ?>
                    <td style="border: none; text-align: right; font-size: 14px; font-weight: bold;"><?php echo number_format($totalFooter, 2)." ".$symbol; ?></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp, $excelContent);
fclose($fp);
?>
<div style="clear: both;"></div>
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
