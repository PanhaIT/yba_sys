<?php

$rnd = rand();
$tblName = "tbl" . rand();
$printArea = "printArea" . $rnd;
$btnPrint = "btnPrint" . $rnd;
$btnExport = "btnExport" . $rnd;
$btnShowAll = "btnShowAll" . $rnd;
$btnHideAll = "btnHideAll" . $rnd;
$btnPlusMinus = "btnPlusMinus" . $rnd;

include('includes/function.php');

/**
 * export to excel
 */
$filename="public/report/sales_by_salesman_detail_" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "right");
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");

        $(".btnPrintSalesByCustomer").click(function(event){
            event.preventDefault();
            if($(this).attr("trans_type")=="Invoice"){
                var url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else{
                var url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
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

        // group expansion

        $(".subTotalCgroupQtySrc").each(function(){
            $(".subTotalCgroupQtyDes[rep_id="+$(this).attr("rep_id")+"]").text($(this).text());
        });
        $(".subTotalCgroupUomSrc").each(function(){
            $(".subTotalCgroupUomDes[rep_id="+$(this).attr("rep_id")+"]").text($(this).text());
        });
        $(".subTotalCgroupAmountSrc").each(function(){
            $(".subTotalCgroupAmountDes[rep_id="+$(this).attr("rep_id")+"]").text($(this).text());
        });

        $(".subTotalAmountSrc").each(function(){
            $(".subTotalAmountDes[customer_id="+$(this).attr("customer_id")+"]").text($(this).text());
        });
        $(".subTotalQtySrc").each(function(){
            $(".subTotalQtyDes[customer_id="+$(this).attr("customer_id")+"]").text($(this).text());
        });
        $(".subTotalUomSrc").each(function(){
            $(".subTotalUomDes[customer_id="+$(this).attr("customer_id")+"]").text($(this).text());
        });
        // Hide/Show Customer Rep
        $("#<?php echo $printArea; ?> .cgroup td:nth-child(1)").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $printArea; ?> .cgroup td:nth-child(1)").css("cursor", "pointer");
        $("#<?php echo $printArea; ?> .cgroupDetail:not(.nobg)").css("background", "#f4ffab");
        $("#<?php echo $printArea; ?> .cgroup td:nth-child(1)").click(function(){
            if($("#<?php echo $printArea; ?> .customer[rep_id="+$(this).attr("rep_id")+"]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
                $("#<?php echo $printArea; ?> .cgroupDetail[rep_id="+$(this).attr("rep_id")+"] td img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
                $("#<?php echo $printArea; ?> .customerDetail[rep_id="+$(this).attr("rep_id")+"]").hide();
            }
            $("#<?php echo $printArea; ?> .cgroupDetail[rep_id="+$(this).attr("rep_id")+"]").toggle();
        });

        $("#<?php echo $printArea; ?> .customer td:nth-child(2)").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $printArea; ?> .customer td:nth-child(2)").css("cursor", "pointer");
        $("#<?php echo $printArea; ?> .customerDetail:not(.nobg)").css("background", "#EEE");
        $("#<?php echo $printArea; ?> .customer td:nth-child(2)").click(function(){
            if($("#<?php echo $printArea; ?> .customerDetail[customer_id=" + $(this).attr("customer_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $printArea; ?> .customerDetail[customer_id="+$(this).attr("customer_id")+"]").toggle();
        });

        $(".<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $printArea; ?> .customerDetail").show();
        });
        $(".<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $printArea; ?> .customerDetail").hide();
        });

        $("#<?php echo $btnPrint; ?>").click(function(){
            $(".<?php echo $btnShowAll; ?>").hide();
            $(".<?php echo $btnHideAll; ?>").hide();
            $(".<?php echo $btnPlusMinus; ?>").hide();
            w=window.open();
            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
            w.document.write($("#<?php echo $printArea; ?>").html());
            w.document.close();
            w.print();
            w.close();
            $(".<?php echo $btnShowAll; ?>").show();
            $(".<?php echo $btnHideAll; ?>").show();
            $(".<?php echo $btnPlusMinus; ?>").show();
        });

        $("#<?php echo $btnExport; ?>").click(function(){
            window.open("<?php echo $this->webroot; ?>public/report/sales_by_salesman_detail_<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_REPORT_SALES_BY_REP . '</b><br /><br />';
    $excelContent .= MENU_REPORT_SALES_BY_REP."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    if($_POST['is_free'] == '1'){
        $tableQty = 'Qty Free';
    } else if($_POST['is_free'] == '0') {
        $tableQty = 'Qty';
    } else {
        $tableQty = 'Qty + Free';
    }
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_DATE."\t".TABLE_INVOICE_CODE."\t".TABLE_PRODUCT_NAME."\t".TABLE_LOCATION."\t".$tableQty."\t".TABLE_UOM."\t".TABLE_PRICE."\t".GENERAL_AMOUNT;
    ?>
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_INVOICE_CODE; ?></th>
            <th><?php echo TABLE_PRODUCT_NAME; ?></th>
            <th style="width: 160px !important;"><?php echo TABLE_LOCATION_GROUP; ?></th>
            <th style="width: 80px !important;"><?php echo $tableQty; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_UOM; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_PRICE; ?></th>
            <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
        </tr>
        <?php
        $condition = 'tbl.is_pos=0';
        if ($_POST['date_from'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($_POST['date_from']) . '" <= DATE(tbl.order_date)';
        }
        if ($_POST['date_to'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= '"' . dateConvert($_POST['date_to']) . '" >= DATE(tbl.order_date)';
        }
        $condition != '' ? $condition .= ' AND ' : '';
        if ($_POST['status'] == '') {
            $condition .= 'tbl.status>-1';
        } else {
            $condition .= 'tbl.status=' . $_POST['status'];
        }
        if ($_POST['company_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.company_id=' . $_POST['company_id'];
        } else {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if ($_POST['branch_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.branch_id=' . $_POST['branch_id'];
        } else {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        if ($_POST['location_group_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.location_group_id=' . $_POST['location_group_id'];
        }
        if ($_POST['created_by'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.created_by=' . $_POST['created_by'];
        }
        if ($_POST['salesman'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'sales_rep_id=' . $_POST['salesman'];
        }
        if ($_POST['cgroup_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= $_POST['cgroup_id'] . ' IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = tbl.customer_id)';
        }
        if ($_POST['customer_id'] != '') {
            $condition != '' ? $condition .= ' AND ' : '';
            $condition .= 'tbl.customer_id = '.$_POST['customer_id'];
        }
        
        $grandTotalAmount=0;
        ?>

        <?php $excelContent .= "\n".'Product'; ?>
        <tr style="font-weight: bold;"><td class="first" colspan="10" style="font-size: 14px;">Product</td></tr>
        <?php
        $index=1;
        $arrCode=array();
        $arrProduct=array();
        $arrLocation=array();
        $oldCgroupId='';
        $oldCgroupName='';
        $oldCustomerId='';
        $oldCustomerName='';
        $subTotalQty=0;
        $subTotalCgroupQty=0;
        $subTotalCgroupTypeQty=0;
        $totalQty=0;
        $subTotalAmount=0;
        $subTotalCgroupAmount=0;
        $subTotalCgroupTypeAmount=0;
        $totalAmount=0;
        $query=mysql_query("SELECT
                                sales_orders.id,
                                'Invoice' AS trans_type,
                                sales_orders.sales_rep_id AS rep_id,
                                CONCAT_WS(' ',employees.employee_code,employees.name) AS rep_name,
                                sales_orders.customer_id,
                                CONCAT_WS(' ',customers.customer_code,customers.name) AS customer_name,
                                sales_orders.order_date AS order_date,
                                sales_orders.so_code AS code,
                                CONCAT_WS(' ',products.code,products.name) AS product_name,
                                (SELECT name FROM location_groups WHERE id = sales_orders.location_group_id) AS location_name,
                                sales_order_details.qty AS qty,
                                sales_order_details.qty_free AS qty_free,
                                sales_order_details.conversion AS conversion,
                                products.price_uom_id AS qty_uom_id,
                                uoms.abbr AS qty_uom_name,
                                sales_order_details.unit_price AS unit_price,
                                ".($_POST['is_free']=='1'?'0 AS total_price':'sales_order_details.total_price AS total_price')."
                            FROM sales_orders
                                INNER JOIN sales_order_details ON sales_orders.id=sales_order_details.sales_order_id
                                INNER JOIN customers ON customers.id = sales_orders.customer_id
                                INNER JOIN products ON products.id = sales_order_details.product_id
                                INNER JOIN uoms ON uoms.id = products.price_uom_id
                                INNER JOIN employees ON employees.id = sales_orders.sales_rep_id
                            WHERE "
                                . str_replace('tbl.','sales_orders.',$condition)
                                . ($_POST['pgroup_id'] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $_POST['pgroup_id'] . ')':'')
                                . ($_POST['parent_id'] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $_POST['parent_id'] :'')
                                . ($_POST['product_id'] != ''?' AND product_id=' . $_POST['product_id'] :'')
                                . ($_POST['is_free'] != ''?($_POST['is_free'] != '1'?' AND sales_order_details.qty > 0':' AND sales_order_details.qty_free > 0'):'')
                                . "
                            UNION ALL
                            SELECT
                                credit_memos.id,
                                'Credit Memo' AS trans_type,
                                sales_orders.sales_rep_id AS rep_id,
                                CONCAT_WS(' ',employees.employee_code,employees.name) AS rep_name,
                                credit_memos.customer_id,
                                CONCAT_WS(' ',customers.customer_code,customers.name) AS customer_name,
                                credit_memos.order_date AS order_date,
                                credit_memos.cm_code AS code,
                                CONCAT_WS(' ',products.code,products.name) AS product_name,
                                (SELECT name FROM location_groups WHERE id = credit_memos.location_group_id) AS location_name,
                                credit_memo_details.qty * -1 AS qty,
                                credit_memo_details.qty_free * -1 AS qty_free,
                                credit_memo_details.conversion AS conversion,
                                products.price_uom_id AS qty_uom_id,
                                uoms.abbr AS qty_uom_name,
                                credit_memo_details.unit_price * -1 AS unit_price,
                                ".($_POST['is_free']=='1'?'0 AS total_price':'credit_memo_details.total_price * -1 AS total_price')."
                            FROM credit_memos
                                INNER JOIN credit_memo_details ON credit_memos.id = credit_memo_details.credit_memo_id
                                INNER JOIN sales_orders ON sales_orders.id = credit_memos.sales_order_id
                                INNER JOIN customers ON customers.id = credit_memos.customer_id
                                INNER JOIN products ON products.id = credit_memo_details.product_id
                                INNER JOIN uoms ON uoms.id = products.price_uom_id
                                INNER JOIN employees ON employees.id = sales_orders.sales_rep_id
                            WHERE "
                                . str_replace(array('tbl.is_pos=0 AND','tbl.'),array('','credit_memos.'),$condition)
                                . ($_POST['pgroup_id'] != ''?' AND product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id=' . $_POST['pgroup_id'] . ')':'')
                                . ($_POST['parent_id'] != ''?' AND (SELECT parent_id FROM products WHERE id=product_id)=' . $_POST['parent_id'] :'')
                                . ($_POST['product_id'] != ''?' AND product_id=' . $_POST['product_id'] :'')
                                . ($_POST['is_free'] != ''?($_POST['is_free'] != '1'?' AND credit_memo_details.qty > 0':' AND credit_memo_details.qty_free > 0'):'')
                                . "
                            ORDER BY rep_name,customer_name,order_date") or die(mysql_error());
        while($data=mysql_fetch_array($query)){
            $arrCode[] = $data['code'];
            $arrProduct[] = $data['product_name'];
            $arrLocation[] = $data['location_name'];
            if($data['customer_id']!=$oldCustomerId){ 
                if($oldCustomerName!=''){ 
                    $excelContent .= "\n\t".'Total '.$oldCustomerName."\t\t\t\t\t".$subTotalQty."\t".$dataSmallestUom['name']."\t\t".number_format($subTotalAmount,2); ?>
            <tr class="customerDetail nobg" rep_id="<?php echo $oldCgroupId; ?>" customer_id="<?php echo $oldCustomerId; ?>" style="font-weight: bold;display: none;">
                <td class="first"></td>
                <td colspan="5">Total <?php echo $oldCustomerName; ?></td>
                <td class="subTotalQtySrc" customer_id="<?php echo $oldCustomerId; ?>" style="text-align: center;"><?php echo $subTotalQty; ?></td>
                <td class="subTotalUomSrc" customer_id="<?php echo $oldCustomerId; ?>"><?php echo $dataSmallestUom['name']; ?></td>
                <td></td>
                <td class="subTotalAmountSrc" customer_id="<?php echo $oldCustomerId; ?>" style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td>
            </tr>
            <?php
                }
            $index=1;
            $subTotalQty=0;
            $subTotalAmount=0;
            if($data['rep_id']!=$oldCgroupId){
                if($oldCgroupName!=''){ 
                    $excelContent .= "\n".'Total '.$oldCgroupName."\t\t\t\t\t\t".$subTotalCgroupQty."\t".$dataSmallestUom['name']."\t\t".number_format($subTotalCgroupAmount,2); ?>
            <tr class="cgroupDetail nobg" rep_id="<?php echo $oldCgroupId; ?>" style="font-weight: bold;">
                <td class="first" colspan="6">Total <?php echo $oldCgroupName; ?></td>
                <td class="subTotalCgroupQtySrc" rep_id="<?php echo $oldCgroupId; ?>" style="text-align: center;"><?php echo $subTotalCgroupQty; ?></td>
                <td class="subTotalCgroupUomSrc" rep_id="<?php echo $oldCgroupId; ?>"><?php echo $dataSmallestUom['name']; ?></td>
                <td></td>
                <td class="subTotalCgroupAmountSrc" rep_id="<?php echo $oldCgroupId; ?>" style="text-align: right;"><?php echo number_format($subTotalCgroupAmount,2); ?></td>
            </tr>
            <?php
                }
            $index=1;
            $subTotalCgroupQty=0;
            $subTotalCgroupAmount=0;
            $excelContent .= "\n".$data['rep_name']; ?>
            <tr class="cgroup cgroupTypeDetail">
                <td class="first" colspan="6" rep_id="<?php echo $data['rep_id']; ?>" style="font-weight: bold;"><?php echo $data['rep_name']; ?></td>
                <td class="subTotalCgroupQtyDes" rep_id="<?php echo $data['rep_id']; ?>" style="text-align: center;"></td>
                <td class="subTotalCgroupUomDes" rep_id="<?php echo $data['rep_id']; ?>"></td>
                <td></td>
                <td class="subTotalCgroupAmountDes" rep_id="<?php echo $data['rep_id']; ?>" style="text-align: right;"></td>
            </tr>
            <?php 
            } 
            $excelContent .= "\n\t".$data['customer_name']; ?>
            <tr class="customer cgroupDetail" rep_id="<?php echo $data['rep_id']; ?>" style="display: none;">
                <td class="first"></td>
                <td colspan="5" customer_id="<?php echo $data['customer_id']; ?>" style="font-weight: bold;"><?php echo $data['customer_name']; ?></td>
                <td class="subTotalQtyDes" customer_id="<?php echo $data['customer_id']; ?>" style="text-align: center;"></td>
                <td class="subTotalUomDes" customer_id="<?php echo $data['customer_id']; ?>"></td>
                <td></td>
                <td class="subTotalAmountDes" customer_id="<?php echo $data['customer_id']; ?>" style="text-align: right;"></td>
            </tr>
            <?php 
            } 
            $excelContent .= "\n".$index."\t".$data['trans_type']."\t".$data['order_date']."\t".$data['code']."\t".$data['product_name']."\t".$data['location_name']."\t".$data['qty']."\t".$data['qty_uom_name']."\t".$data['unit_price']."\t".$data['total_price']; ?>
            <tr class="customerDetail" rep_id="<?php echo $data['rep_id']; ?>" customer_id="<?php echo $data['customer_id']; ?>" style="display: none;">
                <td class="first"><?php echo $index++; ?></td>
                <td><?php echo $data['trans_type']; ?></td>
                <td><?php echo $data['order_date']; ?></td>
                <td><a href="" class="btnPrintSalesByCustomer" rel="<?php echo $data[0]; ?>" trans_type="<?php echo $data['trans_type']; ?>"><?php echo $data['code']; ?></a></td>
                <td><?php echo $data['product_name']; ?></td>
                <td><?php echo $data['location_name']; ?></td>
                <td><?php echo $data['qty']; ?></td>
                <td><?php echo $data['qty_uom_name']; ?></td>
                <td><?php echo number_format($data['unit_price'],2); ?></td>
                <td><?php echo number_format($data['total_price'],2); ?></td>
            </tr>

            <?php
            // Smallest Uom
            $querySmallestUom = mysql_query("SELECT abbr AS name FROM uoms WHERE id = IFNULL((SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = ".$data['qty_uom_id']." AND is_active = 1 AND is_small_uom = 1 LIMIT 1), ".$data['qty_uom_id'].")");
            $dataSmallestUom  = mysql_fetch_array($querySmallestUom);
            $subTotalQty           += ($data['qty'] * $data['conversion']);
            $subTotalCgroupQty     += ($data['qty'] * $data['conversion']);
            $subTotalCgroupTypeQty += ($data['qty'] * $data['conversion']);
            $totalQty              += ($data['qty'] * $data['conversion']);
            $subTotalAmount           += $data['total_price'];
            $subTotalCgroupAmount     += $data['total_price'];
            $subTotalCgroupTypeAmount += $data['total_price'];
            $totalAmount              += $data['total_price'];
            $grandTotalAmount         += $data['total_price'];
            $oldCgroupId=$data['rep_id'];
            $oldCgroupName=$data['rep_name'];
            $oldCustomerId=$data['customer_id'];
            $oldCustomerName=$data['customer_name'];
        } 
        if(mysql_num_rows($query)){ 
            $excelContent .= "\n\t".'Total '.$oldCustomerName."\t\t\t\t\t".$subTotalQty."\t".$dataSmallestUom['name']."\t\t".number_format($subTotalAmount,2); ?>
        <tr class="customerDetail nobg" rep_id="<?php echo $oldCgroupId; ?>" customer_id="<?php echo $oldCustomerId; ?>" style="font-weight: bold;display: none;">
            <td class="first"></td>
            <td colspan="5">Total <?php echo $oldCustomerName; ?></td>
            <td style="text-align: center;"><?php echo $subTotalQty; ?></td>
            <td><?php echo $dataSmallestUom['name']; ?></td>
            <td></td>
            <td style="text-align: right;"><?php echo number_format($subTotalAmount,2); ?></td>
        </tr>
        <?php $excelContent .= "\n".'Total '.$oldCgroupName."\t\t\t\t\t\t".$subTotalCgroupQty."\t".$dataSmallestUom['name']."\t\t".number_format($subTotalCgroupAmount,2); ?>
        <tr class="cgroupDetail nobg" rep_id="<?php echo $oldCgroupId; ?>" style="font-weight: bold;">
            <td class="first" colspan="6">Total <?php echo $oldCgroupName; ?></td>
            <td class="subTotalCgroupQtySrc" rep_id="<?php echo $oldCgroupId; ?>" style="text-align: center;"><?php echo $subTotalCgroupQty; ?></td>
            <td class="subTotalCgroupUomSrc" rep_id="<?php echo $oldCgroupId; ?>"><?php echo $dataSmallestUom['name']; ?></td>
            <td></td>
            <td class="subTotalCgroupAmountSrc" rep_id="<?php echo $oldCgroupId; ?>" style="text-align: right;"><?php echo number_format($subTotalCgroupAmount,2); ?></td>
        </tr>
        <?php $excelContent .= "\n".'Total Product'."\t\t\t\t\t\t".$totalQty."\t".$dataSmallestUom['name']."\t\t".number_format($totalAmount,2); ?>
        <tr style="font-weight: bold;">
            <td class="first" colspan="6" style="font-size: 14px;">Total Product</td>
            <td style="text-align: center;"><?php echo $totalQty; ?></td>
            <td><?php echo $dataSmallestUom['name']; ?></td>
            <td></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($totalAmount,2); ?></td>
        </tr>
        <?php 
        } 
        $excelContent .= "\n\n".'Grand Total Amount'."\t\t\t".sizeof(array_unique($arrCode))."\t".sizeof(array_unique($arrProduct))."\t".sizeof(array_unique($arrLocation))."\t\t\t\t".number_format($grandTotalAmount,2); ?>
        <tr><td colspan="10">&nbsp;</td></tr>
        <tr style="font-weight: bold;">
            <td class="first" colspan="3" style="font-size: 14px;">Grand Total Amount</td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrCode)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrProduct)); ?></td>
            <td style="text-align: center;font-size: 14px;text-decoration: underline;"><?php echo sizeof(array_unique($arrLocation)); ?></td>
            <td colspan="3"></td>
            <td style="text-align: right;font-size: 14px;text-decoration: underline;"><?php echo number_format($grandTotalAmount,2); ?></td>
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