<?php
include("includes/function.php");
$tblName = "tbl" . rand();

// A/P CoA List
$arrCoAIdList = array();
$queryCoAIdList = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND chart_account_type_id IN (SELECT id FROM chart_account_types WHERE name='Accounts Payable')");
while ($dataCoAIdList = mysql_fetch_array($queryCoAIdList)) {
    $arrCoAIdList[] = $dataCoAIdList['id'];
}

/**
 * table MEMORY
 * default max_heap_table_size 16MB
 */
$date = date('Y-m-d');
$tableName = "general_ledger_detail_ven_view" . $user['User']['id'];
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
                            WHERE gl.is_approve=1 AND gl.is_active=1 AND date <= '" . $date . "' AND chart_account_id IN (" . implode(",", $arrCoAIdList) . ") AND vendor_id IS NOT NULL AND vendor_id = ".$this->data['Vendor']['id']."
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
<script type="text/javascript">
    var oTablePurchaseVendor;
    $(document).ready(function(){
        $("#<?php echo $tblName; ?> td:first-child").addClass('first');
        oTablePurchaseVendor = $("#<?php echo $tblName; ?>").dataTable({
            "aaSorting": [[5, 'DESC']],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/purchaseAjax/<?php echo $this->data['Vendor']['id']; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $(".btnPrintViewVendorRecord").click(function(event){
                    event.preventDefault();
                    var url = "<?php echo $this->base . '/purchase_returns'; ?>/printInvoice/"+$(this).attr("rel");
                    if($(this).attr("trans_type")=="Purchase"){
                        url = "<?php echo $this->base . '/purchase_orders'; ?>/printInvoice/"+$(this).attr("rel");
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
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ -1 ]
            }]
        });
        
        $(".btnBackVendor").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableVendor.fnDraw(false);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendor">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_VENDOR_MANAGEMENT_INFO); ?></legend>
    <div style="width: 49%; vertical-align: top; float: left;">
        <table style="width: 100%;" cellpadding="5">
            <tr>
                <td><label for="VendorVgroupId"><?php echo USER_GROUP; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php 
                        $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM vgroups WHERE id IN (SELECT vgroup_id FROM vendor_vgroups WHERE vendor_id = {$this->data['Vendor']['id']})");
                        $rowGroup = mysql_fetch_array($sqlGroup);
                        echo $sqlGroup[0];
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_CODE; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['vendor_code']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="30%"><?php echo TABLE_NAME; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['name']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="30%"><?php echo TABLE_COUNTRY; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php 
                        if(!empty($this->data['Vendor']['country_id'])){
                            $sqlContry = mysql_query("SELECT name FROM countries WHERE id  = {$this->data['Vendor']['country_id']}");
                            $rowContry = mysql_fetch_array($sqlContry);
                            echo $rowContry[0];
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE_WORK; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['work_telephone']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_TELEPHONE_OTHER; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['other_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_FAX; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['fax_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_EMAIL; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['email_address']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_ADDRESS; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php 
                        echo $this->data['Vendor']['address'];
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_PAYMENT_TERMS; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php 
                        if(!empty($this->data['Vendor']['payment_term_id'])){
                            $sqlTerm = mysql_query("SELECT name FROM payment_terms WHERE id  = {$this->data['Vendor']['payment_term_id']}");
                            $rowTerm = mysql_fetch_array($sqlTerm);
                            echo $rowTerm[0];
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_BALANCE; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        $sqlBalance = mysql_query('SELECT FORMAT((SELECT SUM(debit)-SUM(credit) AS amount FROM ' . $tableName . ' WHERE chart_account_id IN (' . implode(",", $arrCoAIdList) . ') AND vendor_id = '.$this->data['Vendor']['id'].')*-1,2) AS balance');
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
                        <?php echo dateShort($this->data['Vendor']['created'], "d/m/Y H:i:s"); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_CREATED_BY; ?> :</td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->data['User']['first_name']." ".$this->data['User']['last_name']; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="vertical-align: top; float: right; width: 49%;">
        <table style="width: 100%" cellpadding="5">
            <tr>
                <td colspan="2" style="text-align: center;">
                    <?php 
                    if($this->data['Vendor']['photo'] != ''){
                        $photo = "public/vendor_photo/".$this->data['Vendor']['photo'];
                    }else{
                        $photo = "img/button/no-images.png";
                    }
                    ?>
                    <img id="photoDisplay" alt="" src="<?php echo $this->webroot; ?><?php echo $photo; ?>" />
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->data['Vendor']['note']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo TABLE_PRODUCT; ?> :</td>
                <td>
                    <div class="inputContainer">
                        <?php
                        $sql = "SELECT DISTINCT products.id, products.code, products.name FROM products
                                INNER JOIN product_vendors ON product_vendors.product_id = products.id
                                WHERE products.is_active=1 AND product_vendors.vendor_id=" . $this->data['Vendor']['id'];
                        $querySource = mysql_query($sql);
                        while ($dataSource = mysql_fetch_array($querySource)) {
                            echo $dataSource['code'] . " - " . $dataSource['name']."<br/>";
                        } 
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_REPORT_PURCHASE_TRANSACTION); ?></legend>
    <br />
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_TYPE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_DATE; ?></th>
                    <th style="width: 120px !important;"><?php echo TABLE_CODE; ?></th>
                    <th style="width: 140px !important;"><?php echo TABLE_TOTAL_AMOUNT; ?></th>
                    <th style="width: 140px !important;"><?php echo GENERAL_BALANCE; ?></th>
                    <th style="width: 80px !important;"><?php echo TABLE_STATUS; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</fieldset>