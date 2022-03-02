<?php
include("includes/function.php");

$tblName = "tbl" . rand();

// A/R CoA List
$arrCoAIdList = array();
$queryCoAIdList=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Receivable')");
while($dataCoAIdList=mysql_fetch_array($queryCoAIdList)){
    $arrCoAIdList[]=$dataCoAIdList['id'];
}
/**
 * table MEMORY
 * default max_heap_table_size 16MB
 */
$date = date('Y-m-d');
$tableName = "general_ledger_detail_cus_view" . $user['User']['id'];
mysql_query("SET max_heap_table_size = 1024*1024*1024");
mysql_query("CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `date` date DEFAULT NULL,
                  `chart_account_id` int(11) DEFAULT NULL,
                  `company_id` int(11) DEFAULT NULL,
                  `location_id` int(11) DEFAULT NULL,
                  `debit` double DEFAULT NULL,
                  `credit` double DEFAULT NULL,
                  `customer_id` bigint(20) DEFAULT NULL,
                  `vendor_id` bigint(20) DEFAULT NULL,
                  `employee_id` bigint(20) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `chart_account_id` (`chart_account_id`),
                  KEY `company_id` (`company_id`),
                  KEY `location_id` (`location_id`),
                  KEY `date` (`date`)
                ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
mysql_query("TRUNCATE $tableName");
$queryCoa = mysql_query("   SELECT SUM(debit),SUM(credit),chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id
                            FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                            WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "' AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ") AND customer_id IS NOT NULL AND customer_id = ".$customer['Customer']['id']."
                            GROUP BY chart_account_id,company_id,location_id,customer_id,vendor_id,employee_id");
while ($dataCoa = mysql_fetch_array($queryCoa)) {
    mysql_query("INSERT INTO $tableName (
                            date,
                            chart_account_id,
                            company_id,
                            location_id,
                            debit,
                            credit,
                            customer_id,
                            vendor_id,
                            employee_id
                        ) VALUES (
                            '" . $date . "',
                            " . (!is_null($dataCoa['chart_account_id']) ? $dataCoa['chart_account_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['company_id']) ? $dataCoa['company_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['location_id']) ? $dataCoa['location_id'] : "NULL") . ",
                            '" . $dataCoa['SUM(debit)'] . "',
                            '" . $dataCoa['SUM(credit)'] . "',
                            " . (!is_null($dataCoa['customer_id']) ? $dataCoa['customer_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['vendor_id']) ? $dataCoa['vendor_id'] : "NULL") . ",
                            " . (!is_null($dataCoa['employee_id']) ? $dataCoa['employee_id'] : "NULL") . "
                        )");
}
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var oTableSalesCustomer;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTableSalesCustomer = $("#<?php echo $tblName; ?>").dataTable({
            "aaSorting": [[6, 'DESC']],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/salesAjax/<?php echo $customer['Customer']['id']; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                
                $(".btnPrintViewCustomerRecord").click(function(event){
                    event.preventDefault();
                    var url = "<?php echo $this->base . '/credit_memos'; ?>/printInvoice/"+$(this).attr("rel");
                    if($(this).attr("trans_type")=="Invoice"){
                        url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+$(this).attr("rel");
                    } else if($(this).attr("trans_type")=="POS"){
                        url = "<?php echo $this->base . '/point_of_sales'; ?>/printReceipt/"+$(this).attr("rel");
                    } else if($(this).attr("trans_type")=="Receive Payment"){
                        url = "<?php echo $this->base . '/receive_payments'; ?>/printReceipt/"+$(this).attr("rel");
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
                $(".btnPrintInvMulti").click(function(event){
                    event.preventDefault();
                    if($(".btnPrintInvMulti").attr("rel") != ""){
                        var relId = $(".btnPrintInvMulti").attr("rel").split(","); 
                        for(var i = 0; i < relId.length; i++){
                            url = "<?php echo $this->base . '/sales_orders'; ?>/printInvoice/"+relId[i];
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
                        }
                    }else{
                        $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Don\'t have invoice seleted.</p>');
                        $("#dialog").dialog({
                            title: '<?php echo DIALOG_WARNING; ?>',
                            resizable: false,
                            modal: true,
                            width: 'auto',
                            height: 'auto',
                            open: function(event, ui){
                                $(".ui-dialog-buttonpane").show();
                            },
                            buttons: {
                                '<?php echo ACTION_CLOSE; ?>': function() {
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }
                });
                $(".btnCheckInvoice").click(function(){
                    checkedInvoice();
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1, 3 ]
            }]
        });
        $(".btnBackCustomer").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCustomer.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $(".checkMultiInvoice").click(function(){
            $(".btnCheckInvoice").prop('checked', $(this).prop('checked'));
            checkedInvoice();
        });
    });
    
    function checkedInvoice(){
        $(".btnPrintInvMulti").attr("rel", "");
        var relId   = $(".btnPrintInvMulti").attr("rel");
        if(relId == ""){
            $(".btnCheckInvoice").each(function(){
                var invId   = $(".btnPrintInvMulti").attr("rel");
                var checked = $(this).prop("checked");
                if(checked == true){
                    var rel = $(this).attr("rel");
                    if(invId == ""){
                        $(".btnPrintInvMulti").attr("rel", rel);
                    }else{
                        $(".btnPrintInvMulti").attr("rel", (invId+","+rel));
                    }
                }
            }); 
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCustomer">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CUSTOMER_MANAGEMENT_INFO); ?></legend>
    <div style="width: 44%; vertical-align: top; float: left;">
        <table cellpadding="5" style="width: 100%;">
            <tr>
                <td style="width: 40%;"><?php echo TABLE_CUSTOMER_NUMBER; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['customer_code']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_NAME; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['name']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend><?php echo TABLE_ADDRESS; ?></legend>
                        <table cellpadding="3" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_NO; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $customer['Customer']['house_no']; ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_STREET; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php 
                                        echo $customer['Street']['name']; 
                                        ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_PROVINCE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php 
                                        if($customer['Customer']['province_id'] > 0){
                                            $provinceId = $customer['Customer']['province_id'];
                                            $districtId = $customer['Customer']['district_id']>0?$customer['Customer']['district_id']:'0';
                                            $communeId  = $customer['Customer']['commune_id']>0?$customer['Customer']['commune_id']:'0';
                                            $villageId  = $customer['Customer']['village_id']>0?$customer['Customer']['village_id']:'0';
                                            $sqlAddress = mysql_query("SELECT p.name AS p_name, d.name AS d_name, c.name AS c_name, v.name AS v_name FROM provinces AS p LEFT JOIN districts AS d ON d.province_id = p.id AND d.id = {$districtId} LEFT JOIN communes AS c ON c.district_id = d.id AND c.id = {$communeId} LEFT JOIN villages AS v ON v.commune_id = c.id AND v.id = {$villageId} WHERE p.id = {$customer['Customer']['province_id']}");    
                                            $rowAddress = mysql_fetch_array($sqlAddress);
                                        }else{
                                            $rowAddress['p_name'] = '';
                                            $rowAddress['d_name'] = '';
                                            $rowAddress['c_name'] = '';
                                            $rowAddress['v_name'] = '';
                                        }
                                        echo $rowAddress['p_name'];
                                        ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_DISTRICT; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['d_name']; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><?php echo TABLE_COMMUNE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['c_name']; ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><?php echo TABLE_VILLAGE; ?></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $rowAddress['v_name']; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['main_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_MOBILE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['mobile_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE_ALT; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['other_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_EMAIL; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['email']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_FAX; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['fax']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_PAYMENT_TERMS; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['PaymentTerm']['name']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_LIMIT_CREDIT; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['limit_balance']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_LIMIT_NUMBER_INVOICE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['limit_total_invoice']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo GENERAL_DISCOUNT; ?> (%):</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo number_format($customer['Customer']['discount'], 2); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="width: 30%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td style="width: 40%;"><?php echo TABLE_GROUP; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php  
                        $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) FROM cgroups WHERE id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$customer['Customer']['id'].")");
                        $rowGroup = mysql_fetch_array($sqlGroup);
                        echo $rowGroup[0];
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><?php echo TABLE_NAME_IN_KHMER; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['name_kh']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_VAT; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['vat']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_LOCATION_GROUP; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        $sqlWarehouse = mysql_query("SELECT name FROM location_groups WHERE customer_id = ".$customer['Customer']['id']." LIMIT 1;");
                        $rowWarehouse = mysql_fetch_array($sqlWarehouse);
                        echo $rowWarehouse[0]; 
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['Customer']['note']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_BALANCE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        $sqlBalance = mysql_query('SELECT FORMAT((SELECT SUM(debit) - SUM(credit) AS amount FROM ' . $tableName . ' WHERE chart_account_id IN (' . implode(",", $arrCoAIdList) . ') AND customer_id = '.$customer['Customer']['id'].'),2) AS balance');
                        $rowBalance = mysql_fetch_array($sqlBalance);
                        if(!empty($rowBalance[0])){
                            echo $rowBalance[0]." $";
                        } else {
                            echo "0.00 $";
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_CREATED; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo dateShort($customer['Customer']['created'], "d/m/Y H:i:s"); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_CREATED_BY; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $customer['User']['first_name']." ".$customer['User']['last_name']; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="width: 25%; vertical-align: top; float: right;">
        <table style="width: 100%">
            <tr>
                <td colspan="2" style="text-align: center;">
                    <?php 
                    if($customer['Customer']['photo'] != ''){
                        $photo = "public/customer_photo/".$customer['Customer']['photo'];
                    }else{
                        $photo = "img/button/no-images.png";
                    }
                    ?>
                    <img id="photoDisplayCustomer" alt="" src="<?php echo $this->webroot; ?><?php echo $photo; ?>" style=" max-width: 250px; max-height: 250px;" />
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_REPORT_SALES_TRANSACTION); ?></legend>
    <br />
    <div id="dynamic">
        <div class="buttons">
            <a href="" class="positive btnPrintInvMulti" rel="">
                <img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/>
                <?php echo ACTION_PRINT; ?>
            </a>
        </div>
        <div style="clear: both;"></div>
        <br>
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 120px !important;"><input type="checkbox" class="checkMultiInvoice"> <?php echo TABLE_CODE; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_DEPOSIT; ?></th>
                    <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</fieldset>
