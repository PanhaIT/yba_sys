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
$filename="public/report/audit_trail" . $this->Session->id(session_id()) . ".csv";
$fp=fopen($filename,"wb");
$excelContent = '';

?>
<script type="text/javascript" src="<?php echo $this->webroot.'js/jquery.formatCurrency-1.4.0.min.js'; ?>"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:nth-child(7)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(8)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(9)").css("text-align", "center");
        $("#<?php echo $tblName; ?> td:nth-child(10)").css("text-align", "right");

        $(".btnPrintAuditTrial").click(function(event){
            event.preventDefault();
            if($(this).attr("trans_type")=="Invoice"){
                var url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
            }else if($(this).attr("trans_type")=="POS"){
                url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
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
            window.open("<?php echo $this->webroot; ?>public/report/audit_trail<?php echo $this->Session->id(session_id()); ?>.csv", "_blank");
        });
    });
</script>
<div id="<?php echo $printArea; ?>">
    <?php
    $msg = '<b style="font-size: 18px;">' . MENU_AUDIT_TRAIL . '</b><br /><br />';
    $excelContent .= MENU_AUDIT_TRAIL."\n\n";
    if($_POST['date_from']!='') {
        $msg .= REPORT_FROM.': '.$_POST['date_from'];
        $excelContent .= REPORT_FROM.': '.$_POST['date_from'];
    }
    if($_POST['date_to']!='') {
        $msg .= ' '.REPORT_TO.': '.$_POST['date_to'];
        $excelContent .= ' '.REPORT_TO.': '.$_POST['date_to']."\n\n";
    }
    echo $this->element('/print/header-report',array('msg'=>$msg));
    $excelContent .= TABLE_NO."\t".TABLE_TYPE."\t".TABLE_DATE."\t".TABLE_CODE."\t".TABLE_STATUS."\t".TABLE_CREATED."\t".TABLE_LAST_MODIFIED."\t".TABLE_CREATED_BY."\t".TABLE_LAST_MODIFIED_BY."\t".GENERAL_AMOUNT;
    ?>
    
    <table id="<?php echo $tblName; ?>" class="table_report">
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_TYPE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_DATE; ?></th>
            <th style="width: 100px !important;"><?php echo TABLE_CODE; ?></th>
            <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_CREATED; ?></th>
            <th style="width: 140px !important;"><?php echo TABLE_LAST_MODIFIED; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_CREATED_BY; ?></th>
            <th style="width: 120px !important;"><?php echo TABLE_LAST_MODIFIED_BY; ?></th>
            <th style="width: 120px !important;"><?php echo GENERAL_AMOUNT; ?></th>
        </tr>
        
        <?php
        $index = 1;
        $sql = "";
        $totalAmount = 0;
        $condition = "";
        if($_POST['company_id'] != ''){
            $condition .= ' AND company_id = '.$_POST['company_id'];
        } else {
            $condition .= ' AND company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')';
        }
        if($_POST['branch_id'] != ''){
            $condition .= ' AND branch_id = '.$_POST['branch_id'];
        } else {
            $condition .= ' AND branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = '.$user['User']['id'].')';
        }
        // Sale order block
        if(isset($_POST['saleOrder']) && $_POST['saleOrder']=="on"){
            $sql  = "SELECT so_code, total_amount, discount, discount_percent, total_vat, order_date, so.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.created_by AND users.is_active=1) created_name, so.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.modified_by AND users.is_active=1) modified_name, so.status FROM sales_orders so";
            $sql .= " WHERE so.is_pos=0 AND so.order_date >= '".dateConvert($_POST['date_from'])."' AND so.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND so.created_by='".$_POST['created_by']."'" : "";
            $sql .= " ORDER BY so_code, created ASC";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $amount = 0;
            $status = "";
            while($data = mysql_fetch_array($exc)){
                // status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status =  "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['so_code'], $arrayPbCode)){
                        $status =  "Prior";
                    }else{
                        $status =  "Latest";
                    }
                }
            if(!in_array($data['so_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"]-$data["total_amount"]+$data["total_vat"];
            $excelContent .= "\n".$index."\tInvoice\t".dateShort($data['order_date'])."\t".$data['so_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Invoice"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['so_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['so_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php
                $totalAmount = $totalAmount + $amount;
            }
        }
        ?>
        
        <?php
        // POS block
        if(isset($_POST['withPos']) && $_POST['withPos']=="on"){
            $sql  = "SELECT so_code, total_amount, discount, discount_percent, total_vat, order_date, so.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.created_by AND users.is_active=1) created_name, so.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.modified_by AND users.is_active=1) modified_name, so.status FROM sales_orders so";
            $sql .= " WHERE so.is_pos=1 AND so.order_date >= '".dateConvert($_POST['date_from'])."' AND so.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND so.created_by='".$_POST['created_by']."'" : "";
            $sql .= " ORDER BY so_code, created ASC";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy ="";
            $amount = 0;
            $status = "";
            while($data = mysql_fetch_array($exc)){
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['so_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['so_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"] - $data["discount"] +$data["total_vat"];
            $excelContent .= "\n".$index."\tInvoice POS\t".dateShort($data['order_date'])."\t".$data['so_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Invoice POS"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['so_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['so_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php    
                $totalAmount = $totalAmount + $amount;
            }
        }
        ?>
        
        <?php
        // Credit memo block
        if(isset($_POST['creditMemo']) && $_POST['creditMemo']=="on"){
            $sql  = "SELECT cm_code, total_amount, discount, mark_up, total_vat, order_date, cm.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=cm.created_by AND users.is_active=1) created_name, cm.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=cm.modified_by AND users.is_active=1) modified_name, cm.status FROM credit_memos cm";
            $sql .= " WHERE cm.order_date >= '".dateConvert($_POST['date_from'])."' AND cm.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND cm.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";                    
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['cm_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['cm_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"] - $data["discount"] + $data["mark_up"] + $data["total_vat"];
            $excelContent .= "\n".$index."\tCredit Memo\t".dateShort($data['order_date'])."\t".$data['cm_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Credit Memo"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['cm_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['cm_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php    
                $totalAmount = $totalAmount + $amount;
            }
        }
        ?>
        
        <?php
        // Delivery block
        if(isset($_POST['delivery']) && $_POST['delivery']=="on"){
            $sql  = "SELECT code, date, dv.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=dv.created_by AND users.is_active=1) created_name, dv.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=dv.modified_by AND users.is_active=1) modified_name, dv.status FROM deliveries dv";
            $sql .= " WHERE dv.date >= '".dateConvert($_POST['date_from'])."' AND dv.date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND dv.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $excelContent .= "\n".$index."\tDelivery\t".dateShort($data['date'])."\t".$data['code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name'];
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Delivery"; ?></td>
            <td><?php echo dateShort($data['date']); ?></td>
            <td><?php echo $data['code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td></td>
        </tr>
        <?php    
            }
        }
        ?>
        
        <?php
        // Receive payment block
        if(isset($_POST['receivePayment']) && $_POST['receivePayment']=="on"){
            $sql  = "SELECT reference, date, (SELECT SUM(paid) FROM receive_payment_details WHERE receive_payment_id=rp.id) AS total_amount, rp.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=rp.created_by AND users.is_active=1) created_name, rp.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=rp.modified_by AND users.is_active=1) modified_name, rp.is_active FROM receive_payments rp";
            $sql .= " WHERE rp.date >= '".dateConvert($_POST['date_from'])."' AND rp.date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND rp.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['is_active']==-1){
                    $status = "Modified";
                }else if($data['is_active']==0){
                    $status = "Void";
                }else if($data['is_active']==1 OR $data['is_active']==2){
                    if(!in_array($data['reference'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['reference'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $excelContent .= "\n".$index."\tReceive Payment\t".dateShort($data['date'])."\t".$data['reference']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($data['total_amount'],2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Receive Payment"; ?></td>
            <td><?php echo dateShort($data['date']); ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['reference'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($data['total_amount'],2); ?></td>
        </tr>
        <?php    
                $totalAmount = $totalAmount + $data['total_amount'];
            }
        }
        ?>
        
        <?php
        // Adjustment block
        if(isset($_POST['adjustment']) && $_POST['adjustment']=="on"){
            $sql  = "SELECT reference, date, adj.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=adj.created_by AND users.is_active=1) created_name, adj.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=adj.modified_by AND users.is_active=1) modified_name, adj.status FROM cycle_products adj";
            $sql .= " WHERE adj.date >= '".dateConvert($_POST['date_from'])."' AND adj.date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND adj.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $status = "";
            $createdBy = "";
            $arrayPbCode = array();
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==0){
                    $status = "Void";
                }else{
                    $status = "Prior";
                }
            if(!in_array($data['reference'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $excelContent .= "\n".$index."\tAdjustment\t".dateShort($data['date'])."\t".$data['reference']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name'];
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Adjustment"; ?></td>
            <td><?php echo dateShort($data['date']); ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['reference'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td></td>
        </tr>
        <?php    
            }
        }
        ?>
        
        <?php
        // Transfer order block
        if(isset($_POST['transfer']) && $_POST['transfer']=="on"){
            $sql  = "SELECT to_code, order_date, tf.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=tf.created_by AND users.is_active=1) created_name, tf.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=tf.modified_by AND users.is_active=1) modified_name, tf.status FROM transfer_orders tf";
            $sql .= " WHERE tf.order_date >= '".dateConvert($_POST['date_from'])."' AND tf.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND tf.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['to_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['to_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $excelContent .= "\n".$index."\tTransfer Order\t".dateShort($data['order_date'])."\t".$data['to_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name'];
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Transfer Order"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['to_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['to_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td></td>
        </tr>
        <?php    
            }
        }
        ?>
        
        <?php
        // Purchase order block.
        if(isset($_POST['purchaseOrder']) && $_POST['purchaseOrder']=="on"){
            $sql  = "SELECT pr_code, total_amount, total_vat, order_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_requests pr";
            $sql .= " WHERE pr.order_date >= '".dateConvert($_POST['date_from'])."' AND pr.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $amount = 0;
            $status = "";
            while($data = mysql_fetch_array($exc)){
                // status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==3){
                    if(!in_array($data['pr_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['pr_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"]+$data["total_vat"];
            $excelContent .= "\n".$index."\tPurchase Order\t".dateShort($data['order_date'])."\t".$data['pr_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Purchase Order"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['pr_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['pr_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php                 
                $totalAmount = $totalAmount + $amount;
            }        
        }?>
        
        <?php
        // Purchase bill block.
        if(isset($_POST['purchaseBill']) && $_POST['purchaseBill']=="on"){
            $sql  = "SELECT po_code, total_amount, discount_amount, total_vat, order_date, po.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=po.created_by AND users.is_active=1) created_name, po.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=po.modified_by AND users.is_active=1) modified_name, po.status FROM purchase_orders po";
            $sql .= " WHERE po.order_date >= '".dateConvert($_POST['date_from'])."' AND po.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND po.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $amount = 0;
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==3){
                    if(!in_array($data['po_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['po_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"]-$data["discount_amount"]+$data["total_vat"];
            $excelContent .= "\n".$index."\tPurchase Bill\t".dateShort($data['order_date'])."\t".$data['po_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Purchase Bill"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['po_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['po_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php 
                $totalAmount = $totalAmount + $amount;            
            }        
        }?>
        
        <?php
        // Purchase receive block.
        if(isset($_POST['purchaseReceive']) && $_POST['purchaseReceive']=="on"){
            $sql  = "SELECT purchase_receive_results.code AS receive_code, total_amount, discount_amount, total_vat, received_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_receives pr";
            $sql .= " INNER JOIN purchase_orders po ON po.id=pr.purchase_order_id INNER JOIN purchase_receive_results ON purchase_receive_results.id = pr.purchase_receive_result_id";
            $sql .= " WHERE pr.received_date >= '".dateConvert($_POST['date_from'])."' AND pr.received_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $amount = 0;
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==3){
                    if(!in_array($data['receive_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['receive_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"]-$data["discount_amount"]+$data["total_vat"];
            $excelContent .= "\n".$index."\tPurchase Receive\t".dateShort($data['received_date'])."\t".$data['receive_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Purchase Receive"; ?></td>
            <td><?php echo dateShort($data['received_date']); ?></td>
            <td><?php echo $data['receive_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['receive_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php 
                $totalAmount = $totalAmount + $amount;
            }
        }?>
        
        <?php
        // Bill Return block.
        if(isset($_POST['billReturn']) && $_POST['billReturn']=="on"){
            $sql  = "SELECT pr_code, total_amount, total_vat, order_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_returns pr";
            $sql .= " WHERE pr.order_date >= '".dateConvert($_POST['date_from'])."' AND pr.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['status']==-1){
                    $status = "Modified";
                }else if($data['status']==0){
                    $status = "Void";
                }else if($data['status']==1 OR $data['status']==2){
                    if(!in_array($data['pr_code'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['pr_code'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $amount = $data["total_amount"]+$data["total_vat"];
            $excelContent .= "\n".$index."\tBill Return\t".dateShort($data['order_date'])."\t".$data['pr_code']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($amount,2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Bill Return"; ?></td>
            <td><?php echo dateShort($data['order_date']); ?></td>
            <td><?php echo $data['pr_code']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['pr_code'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($amount,2); ?></td>
        </tr>
        <?php 
                $totalAmount = $totalAmount + $amount;
            } 
        }?>
        
        <?php
        // Pay bill block
        if(isset($_POST['payBill']) && $_POST['payBill']=="on"){
            $sql  = "SELECT reference, date, (SELECT SUM(paid) FROM pay_bill_details WHERE pay_bill_id=pb.id) AS total_amount, pb.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pb.created_by AND users.is_active=1) created_name, pb.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pb.modified_by AND users.is_active=1) modified_name, pb.is_active FROM pay_bills pb";
            $sql .= " WHERE pb.date >= '".dateConvert($_POST['date_from'])."' AND pb.date <= '".dateConvert($_POST['date_to'])."'".$condition;
            (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pb.created_by='".$_POST['created_by']."'" : "";
            $exc = mysql_query($sql) or die(mysql_error());
            $arrayPbCode = array();
            $createdBy = "";
            $status = "";
            while($data = mysql_fetch_array($exc)){
                //status
                if($data['is_active']==-1){
                    $status = "Modified";
                }else if($data['is_active']==0){
                    $status = "Void";
                }else if($data['is_active']==1 OR $data['is_active']==2){
                    if(!in_array($data['reference'], $arrayPbCode)){
                        $status = "Prior";
                    }else{
                        $status = "Latest";
                    }
                }
            if(!in_array($data['reference'], $arrayPbCode)){
                $createdBy = $data['created_name'];
            }
            $excelContent .= "\n".$index."\tPay Bill\t".dateShort($data['date'])."\t".$data['reference']."\t".$status."\t".$data['created']."\t".$data['modified']."\t".$createdBy."\t".$data['modified_name']."\t".number_format($data['total_amount'],2);    
        ?>
        <tr>
            <td><?php echo $index++; ?></td>
            <td><?php echo "Pay Bill"; ?></td>
            <td><?php echo dateShort($data['date']); ?></td>
            <td><?php echo $data['reference']; ?></td>
            <td>
            <?php
                echo $status;
                $arrayPbCode[] = $data['reference'];
            ?>
            </td>
            <td><?php echo $data['created']; ?></td>
            <td><?php echo $data['modified']; ?></td>
            <td><?php echo $createdBy; ?></td>
            <td><?php echo $data['modified_name']; ?></td>
            <td><?php echo number_format($data['total_amount'],2); ?></td>
        </tr>
        <?php 
                $totalAmount = $totalAmount + $data['total_amount'];
            } 
        }?>   
        
        
        <?php
        // All void block
        if(isset($_POST['allVoid']) && $_POST['allVoid']=="on"){
        ?>
            <?php
                // Sale order and POS in void block
                $sql  = "SELECT so_code, total_amount, discount, discount_percent, total_vat, order_date, so.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.created_by AND users.is_active=1) created_name, so.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=so.modified_by AND users.is_active=1) modified_name, so.status FROM sales_orders so";
                $sql .= " WHERE so.status=0 AND so.order_date >= '".dateConvert($_POST['date_from'])."' AND so.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND so.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $amount = 0;
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['reference'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $amount = $data["total_amount"]-$data["total_amount"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tInvoice\t".dateShort($data['order_date'])."\t".$data['so_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Invoice"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['so_code']; ?></td>
                <td><?php echo "Void";?></td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php
                    $arrayPbCode[] = $data['so_code'];
                    $totalAmount = $totalAmount + $data['total_amount'];
                }
            ?>
            
            <?php
            // Credit memo in void block
                $sql  = "SELECT cm_code, total_amount, discount, mark_up, total_vat, order_date, cm.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=cm.created_by AND users.is_active=1) created_name, cm.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=cm.modified_by AND users.is_active=1) modified_name, cm.status FROM credit_memos cm";
                $sql .= " WHERE cm.status=0 AND cm.order_date >= '".dateConvert($_POST['date_from'])."' AND cm.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND cm.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $amount = 0;
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['cm_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $amount = $data["total_amount"]-$data["discount"]+$data["mark_up"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tCredit Memo\t".dateShort($data['order_date'])."\t".$data['cm_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Credit Memo"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['cm_code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php    
                    $arrayPbCode[] = $data['cm_code'];
                    $totalAmount = $totalAmount + $amount;
                }
            ?>
            
            <?php
                // Delivery in void block
                $sql  = "SELECT code, date, dv.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=dv.created_by AND users.is_active=1) created_name, dv.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=dv.modified_by AND users.is_active=1) modified_name, dv.status FROM deliveries dv";
                $sql .= " WHERE dv.status=0 AND dv.date >= '".dateConvert($_POST['date_from'])."' AND dv.date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND dv.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $excelContent .= "\n".$index."\tDelivery\t".dateShort($data['date'])."\t".$data['code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name'];    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Delivery"; ?></td>
                <td><?php echo dateShort($data['date']); ?></td>
                <td><?php echo $data['code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td></td>
            </tr>
            <?php    
                    $arrayPbCode[] = $data['code'];
                }
            ?>
            
            <?php
            // Receive payment in void block
                $sql  = "SELECT reference, date, (SELECT SUM(paid) FROM receive_payment_details WHERE receive_payment_id=rp.id) AS total_amount, rp.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=rp.created_by AND users.is_active=1) created_name, rp.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=rp.modified_by AND users.is_active=1) modified_name, rp.is_active FROM receive_payments rp";
                $sql .= " WHERE rp.is_active=0 AND rp.date >= '".dateConvert($_POST['date_from'])."' AND rp.date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND rp.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['reference'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $excelContent .= "\n".$index."\tReceive Payment\t".dateShort($data['date'])."\t".$data['reference']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($data['total_amount'],2);    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Receive Payment"; ?></td>
                <td><?php echo dateShort($data['date']); ?></td>
                <td><?php echo $data['reference']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($data['total_amount'],2); ?></td>
            </tr>
            <?php    
                    $arrayPbCode[] = $data['reference'];
                    $totalAmount = $totalAmount + $data['total_amount'];
                }
            ?>
            
            <?php
            // Adjustment in void block
                $sql  = "SELECT reference, date, adj.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=adj.created_by AND users.is_active=1) created_name, adj.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=adj.modified_by AND users.is_active=1) modified_name, adj.status FROM cycle_products adj";
                $sql .= " WHERE adj.status=0 AND adj.date >= '".dateConvert($_POST['date_from'])."' AND adj.date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND adj.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['reference'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $excelContent .= "\n".$index."\tAdjustment\t".dateShort($data['date'])."\t".$data['reference']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($data['total_amount'],2);    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Adjustment"; ?></td>
                <td><?php echo dateShort($data['date']); ?></td>
                <td><?php echo $data['reference']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td></td>
            </tr>
            <?php    
                    $arrayPbCode[] = $data['reference'];
                }
            ?>
            
            <?php
            // Transfer order in void block
                $sql  = "SELECT to_code, order_date, tf.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=tf.created_by AND users.is_active=1) created_name, tf.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=tf.modified_by AND users.is_active=1) modified_name, tf.status FROM transfer_orders tf";
                $sql .= " WHERE tf.status=0 AND tf.order_date >= '".dateConvert($_POST['date_from'])."' AND tf.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND tf.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['to_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $excelContent .= "\n".$index."\tTransfer Order\t".dateShort($data['order_date'])."\t".$data['to_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name'];    
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Transfer Order"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['to_code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td></td>
            </tr>
            <?php    
                    $arrayPbCode[] = $data['to_code'];
                }
            ?>
            
            <?php
            // Purchase order in void block.
                $sql  = "SELECT pr_code, total_amount, total_vat, order_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_requests pr";
                $sql .= " WHERE pr.status=0 AND pr.order_date >= '".dateConvert($_POST['date_from'])."' AND pr.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $amount = 0;
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['pr_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $amount = $data["total_amount"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tPurchase Order\t".dateShort($data['order_date'])."\t".$data['pr_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Purchase Order"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['pr_code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php            
                    $arrayPbCode[] = $data['pr_code'];     
                    $totalAmount = $totalAmount + $amount;
            }?>
            
            <?php
                // Purchase bill in void block.
                $sql  = "SELECT po_code, total_amount, discount_amount, total_vat, order_date, po.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=po.created_by AND users.is_active=1) created_name, po.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=po.modified_by AND users.is_active=1) modified_name, po.status FROM purchase_orders po";
                $sql .= " WHERE po.status=0 AND po.order_date >= '".dateConvert($_POST['date_from'])."' AND po.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND po.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $amount = 0;
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['po_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $amount = $data["total_amount"]-$data["discount_amount"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tPurchase Bill\t".dateShort($data['order_date'])."\t".$data['po_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Purchase Bill"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['po_code']; ?></td>
                <td>
                <?php echo "Void"; ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php 
                    $arrayPbCode[] = $data['po_code'];  
                    $totalAmount = $totalAmount + $amount;     
            }?>
            
            <?php
                // Purchase receive in void block.
                $sql  = "SELECT purchase_receive_results.code AS receive_code, total_amount, discount_amount, total_vat, received_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_receives pr";
                $sql .= " INNER JOIN purchase_orders po ON po.id=pr.purchase_order_id INNER JOIN purchase_receive_results ON purchase_receive_results.id = pr.purchase_receive_result_id";
                $sql .= " WHERE pr.status=0 AND pr.received_date >= '".dateConvert($_POST['date_from'])."' AND pr.received_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());                
                $amount = 0;
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['receive_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }                
                $amount = $data["total_amount"]-$data["discount_amount"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tPurchase Receive\t".dateShort($data['received_date'])."\t".$data['receive_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Purchase Receive"; ?></td>
                <td><?php echo dateShort($data['received_date']); ?></td>
                <td><?php echo $data['receive_code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php 
                    $arrayPbCode[] = $data['receive_code'];
                    $totalAmount = $totalAmount + $amount;
            }?>
            
            <?php
            // Bill Return in void block.
                $sql  = "SELECT pr_code, total_amount, total_vat, order_date, pr.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.created_by AND users.is_active=1) created_name, pr.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pr.modified_by AND users.is_active=1) modified_name, pr.status FROM purchase_returns pr";
                $sql .= " WHERE pr.status=0 AND pr.order_date >= '".dateConvert($_POST['date_from'])."' AND pr.order_date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pr.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['pr_code'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $amount = $data["total_amount"]+$data["total_vat"];
                $excelContent .= "\n".$index."\tBill Return\t".dateShort($data['order_date'])."\t".$data['pr_code']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($amount,2);
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Bill Return"; ?></td>
                <td><?php echo dateShort($data['order_date']); ?></td>
                <td><?php echo $data['pr_code']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($amount,2); ?></td>
            </tr>
            <?php 
                    $arrayPbCode[] = $data['pr_code'];
                    $totalAmount = $totalAmount + $data['total_amount'];
            }?>
        
            <?php
                // Pay bill in void block
                $sql  = "SELECT reference, date, (SELECT SUM(paid) FROM pay_bill_details WHERE pay_bill_id=pb.id) AS total_amount, pb.created, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pb.created_by AND users.is_active=1) created_name, pb.modified, (SELECT CONCAT(users.first_name,' ',users.last_name) FROM users WHERE id=pb.modified_by AND users.is_active=1) modified_name, pb.is_active FROM pay_bills pb";
                $sql .= " WHERE pb.is_active=0 AND pb.date >= '".dateConvert($_POST['date_from'])."' AND pb.date <= '".dateConvert($_POST['date_to'])."'".$condition;
                (isset($_POST['created_by']) && $_POST['created_by']!='')? $sql .= " AND pb.created_by='".$_POST['created_by']."'" : "";
                $exc = mysql_query($sql) or die(mysql_error());
                $arrayPbCode = array();
                while($data = mysql_fetch_array($exc)){
                if(!in_array($data['reference'], $arrayPbCode)){
                    $createdBy = $data['created_name'];
                }
                $excelContent .= "\n".$index."\tPay Bill\t".dateShort($data['date'])."\t".$data['reference']."\tVoid\t".$data['created']."\t".$data['modified']."\t".$data['created_name']."\t".$data['modified_name']."\t".number_format($data['total_amount'],2);
            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo "Pay Bill"; ?></td>
                <td><?php echo dateShort($data['date']); ?></td>
                <td><?php echo $data['reference']; ?></td>
                <td>
                <?php
                    echo "Void";
                ?>
                </td>
                <td><?php echo $data['created']; ?></td>
                <td><?php echo $data['modified']; ?></td>
                <td><?php echo $data['created_name']; ?></td>
                <td><?php echo $data['modified_name']; ?></td>
                <td><?php echo number_format($data['total_amount'],2); ?></td>
            </tr>
            <?php 
                    $arrayPbCode[] = $data['reference'];
                    $totalAmount = $totalAmount + $data['total_amount'];
                } 
            ?>
        <?php }?>
        <?php $excelContent .= "\nTOTAL\t\t\t\t\t\t\t\t\t".number_format($totalAmount,2) ;?>
        <tr style="font-weight: bold; background: #F4FFAB;">
            <td colspan="9">TOTAL</td>
            <td align="right"><?php echo number_format($totalAmount,2);?></td>
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
        <img src="<?php echo $this->webroot; ?>img/button/approved.png" alt=""/>
        <?php echo ACTION_EXPORT_TO_EXCEL; ?>
    </button>
</div>
<div style="clear: both;"></div>
<?php

$excelContent = chr(255).chr(254).@mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
fwrite($fp,$excelContent);
fclose($fp);

?>