<?php

$rnd       = rand();
$tblName   = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint  = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/inventory_activity_detail_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnPrintInventoryActivity").click(function(event){
            event.preventDefault();
            var url = '';
            if($(this).attr("m-type")=="Bill"){
                url = "<?php echo $this->base . '/'; ?>purchase_orders/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Credit"){
                url = "<?php echo $this->base . '/'; ?>purchase_returns/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="POS"){
                url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Sale"){
                url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Credit Memo"){
                url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Inv Adj"){
                url = "<?php echo $this->base . '/inv_adjs'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Transfer In" || $(this).attr("m-type")=="Transfer Out"){
                url = "<?php echo $this->base; ?>/transfer_receives/printInvoice/" + $(this).attr("rel");
            }else if($(this).attr("m-type")=="Customer Consignment In"){
                url = "<?php echo $this->base . '/consignments'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Customer Consignment Out"){
                url = "<?php echo $this->base . '/consignment_returns'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Vendor Consignment"){
                url = "<?php echo $this->base . '/vendor_consignments'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("m-type")=="Vendor Return Consignment"){
                url = "<?php echo $this->base . '/vendor_consignment_returns'; ?>/printInvoice/"+$(this).attr("rel");
            }
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
            window.open("<?php echo $this->webroot; ?>public/report/inventory_activity_detail_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_INVENTORY_CONSIGNMENT . '</b><br /><br />';
    $excelContent .= MENU_INVENTORY_CONSIGNMENT."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to'];
    }
    if($_POST['location_group_id']!='' || $_POST['pgroup_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if($_POST['location_group_id']!='' || $_POST['pgroup_id']!='') {
        $msg .= '<br /><br />';
        $excelContent .= "\n\n";
    }
    if($_POST['pgroup_id']!='') {
        $query=mysql_query("SELECT name FROM pgroups WHERE id=".$_POST['pgroup_id']);
        $data=mysql_fetch_array($query);
        $msg .= ' <br/><b>'.GENERAL_TYPE.'</b>: '.$data[0];
        $excelContent .= GENERAL_TYPE.': '.$data[0];
    }
    $excelContent .= "\n".TABLE_ITEM_DETAIL;
    $msg .= '<br /><br />';
    $excelContent .= "\n\n";
    echo $this->element('/print/header-report',array('msg'=>$msg, 'action' => TABLE_ITEM_DETAIL));
    $excelLots = "\t".TABLE_LOTS_NO;
    $rowSpan   = 10;
    if($rowSettingUomDetail[0] == 0){
        $excelLots = '';
        $rowSpan   = 9;
    }
    $excelContent .= TABLE_NO."\t".TABLE_SKU."\t".TABLE_TYPE."\t".TABLE_DATE."\tModule Code\tCustomer/Vendor Name\t".TABLE_LOCATION_GROUP."\t".TABLE_LOCATION.$excelLots."\t".TABLE_EXP_DATE_SHORT."\t".TABLE_QTY."\t".TABLE_UOM;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report" style="border-collapse: collapse;" border="1" cellpadding="3">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 110px !important;"><?php echo TABLE_SKU; ?></th>
            <th style="width: 170px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 110px !important;">Module Code</th>
            <th>Customer/Vendor Name</th>
            <th style="width: 110px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 110px !important;"><?php echo TABLE_LOCATION; ?></th>
            <th style="width: 95px !important; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
            <th style="width: 95px !important;"><?php echo TABLE_EXP_DATE_SHORT; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_QTY; ?></th>
            <th style="width: 90px !important;"><?php echo TABLE_UOM; ?></th>
        </tr>
        <?php
        $dataReceive = implode(',', $_POST);
        $col         = explode(",", $dataReceive);
        $condition   = '';
        if ($col[1] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[1]) . '" <= DATE(inventories.date)';
        }
        if ($col[2] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($col[2]) . '" >= DATE(inventories.date)';
        }
        if ($col[3] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'inventories.location_group_id = ' . $col[3];
        } else {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'inventories.location_group_id IN (SELECT location_groups.id FROM location_groups INNER JOIN customer_companies ON customer_companies.customer_id = location_groups.customer_id AND customer_companies.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].') WHERE location_groups.location_group_type_id = 1 AND location_groups.customer_id > 0 GROUP BY location_groups.id)';
        }
        if ($col[4] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'inventories.product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ' . $col[4]. ')';
        }
        if ($col[5] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '(SELECT parent_id FROM products WHERE id=inventories.product_id) = ' . $col[5];
        }
        if ($col[6] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'inventories.product_id = ' . $col[6];
        }
        $totalQty=0;
        $sql = "SELECT 
                cycle_product_id,
                sales_order_id,
                point_of_sales_id,
                credit_memo_id,
                purchase_order_id,
                transfer_order_id,
                consignment_id,
                consignment_return_id,
                vendor_consignment_id,
                vendor_consignment_return_id,
                inventories.type AS type,
                customers.name AS cus_name,
                vendors.name AS ven_name,
                products.code AS pro_code,
                products.name AS pro_name,
                locations.name AS loc_name,
                location_groups.name AS loc_g_name,
                qty,
                inventories.date AS date,
                lots_number,
                date_expired,
                uoms.abbr AS uom_name,
                products.price_uom_id AS price_uom_id
                FROM inventories 
                LEFT JOIN customers ON customers.id = inventories.customer_id
                LEFT JOIN vendors ON vendors.id = inventories.vendor_id
                INNER JOIN products ON products.id = inventories.product_id
                INNER JOIN uoms ON uoms.id = products.price_uom_id
                INNER JOIN locations ON locations.id = inventories.location_id
                INNER JOIN location_groups ON location_groups.id = inventories.location_group_id
                WHERE ".$condition." ORDER BY inventories.id ASC";
        $query = mysql_query($sql);
        $index = 0;
        while($data = mysql_fetch_array($query)){
            
            $queryUom=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$data['price_uom_id']."
                            UNION
                            SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$data['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$data['price_uom_id'].")
                            ORDER BY conversion ASC");
            while($dataUom=mysql_fetch_array($queryUom)){
                $small_label = $dataUom['abbr'];
            }
            
            
        ?>
        <tr>
            <td style="text-align: center;">
                <?php 
                $excelContent .= "\n".++$index;
                echo $index; 
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                $excelContent .= "\t".$data['pro_code'];
                echo $data['pro_code'];
                ?>
            </td>
            <td style="text-align: left;">
                <?php
                $excelContent .= "\t".$data['type'];
                echo $data['type'];
                ?>
            </td>
            <td style="text-align: center;">
                <?php 
                if($data['date'] != '' && $data['date'] != '0000-00-00'){
                    $excelContent .= "\t".dateShort($data['date']);
                    echo dateShort($data['date']); 
                } else {
                    $excelContent .= "\t";
                }
                ?>
            </td>
            <td style="text-align: left;">
                <?php
                if($data['type'] == 'Purchase'){
                    $sqlMod = "SELECT id, code FROM purchase_receive_results WHERE purchase_order_id = ".$data['purchase_order_id']." LIMIT 1";
                } else if($data['type'] == 'Credit Memo'){
                    $sqlMod = "SELECT id, cm_code AS code FROM credit_memos WHERE id = ".$data['credit_memo_id'];
                } else if($data['type'] == 'Sale'){
                    $sqlMod = "SELECT id, so_code AS code FROM sales_orders WHERE id = ".$data['sales_order_id'];
                } else if($data['type'] == 'POS'){
                    $sqlMod = "SELECT id, so_code AS code FROM sales_orders WHERE id = ".$data['point_of_sales_id'];
                } else if($data['type'] == 'Inv Adj'){
                    $sqlMod = "SELECT id, reference AS code FROM cycle_products WHERE id = ".$data['cycle_product_id'];
                } else if($data['type'] == 'Transfer In'){
                    $sqlMod = "SELECT id, code FROM transfer_receive_results WHERE transfer_order_id = ".$data['transfer_order_id']." LIMIT 1";
                } else if($data['type'] == 'Transfer Out'){
                    $sqlMod = "SELECT id, code FROM transfer_receive_results WHERE transfer_order_id = ".$data['transfer_order_id']." LIMIT 1";
                } else if($data['type'] == 'Customer Consignment In'){
                    $sqlMod = "SELECT id, code FROM consignments WHERE id = ".$data['consignment_id']." LIMIT 1";
                } else if($data['type'] == 'Customer Consignment Out'){
                    $sqlMod = "SELECT id, code FROM consignment_returns WHERE id = ".$data['consignment_return_id']." LIMIT 1";
                } else if($data['type'] == 'Vendor Consignment'){
                    $sqlMod = "SELECT id, code FROM vendor_consignments WHERE id = ".$data['vendor_consignment_id']." LIMIT 1";
                } else if($data['type'] == 'Vendor Return Consignment'){
                    $sqlMod = "SELECT id, code FROM vendor_consignment_returns WHERE id = ".$data['vendor_consignment_return_id']." LIMIT 1";
                }
                $qMod = mysql_query($sqlMod);
                $row  = mysql_fetch_array($qMod);
                $excelContent .= "\t".$row['code'];
                ?>
                <a href="#" class="btnPrintInventoryActivity" m-type="<?php echo $data['type']; ?>" rel="<?php echo $row['id']; ?>"><?php echo $row['code']; ?></a>
            </td>
            <td style="text-align: left;">
                <?php
                $customerName = $data['cus_name'];
                $vendorName = $data['ven_name'];
                $excelContent .= "\t".$customerName.' '.$vendorName;
                echo $customerName.' '.$vendorName;
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                $excelContent .= "\t".$data['loc_g_name'];
                echo $data['loc_g_name'];
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                $excelContent .= "\t".$data['loc_name'];
                echo $data['loc_name'];
                ?>
            </td>
            <td style="text-align: center; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                <?php
                if($rowSettingUomDetail[0] == 1){
                    if($data['lots_number'] != "0"){
                        $excelContent .= "\t".$data['lots_number'];
                        echo $data['lots_number'];
                    } else {
                        $excelContent .= "\t";
                    }
                }
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                if($data['date_expired'] != '' && $data['date_expired'] != '0000-00-00'){
                    $excelContent .= "\t".dateShort($data['date_expired']);
                    echo dateShort($data['date_expired']);
                } else {
                    $excelContent .= "\t";
                }
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                $totalQty += $data['qty'];
                $excelContent .= "\t".number_format($data['qty'], 2);
                echo number_format($data['qty'], 2);
                ?>
            </td>
            <td style="text-align: center;">
                <?php
                //$excelContent .= "\t".$data['uom_name'];
                //echo $data['uom_name'];
                $excelContent .= "\t".$small_label;
                echo $small_label;
                
                ?>
            </td>
        </tr>
        <?php 
        } 
        $showCol = "";
        if($excelLots != ''){
            $showCol = "\t";
        }
        $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t\t\t\t".$showCol.number_format($totalQty, 2)."\t"; ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="<?php echo $rowSpan; ?>" style="font-size: 14px;">Total Product</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalQty, 2); ?></td>
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