<?php
include("includes/function.php");
$this->element('check_access');
$allowPrintInvoice = checkAccess($user['User']['id'], $this->params['controller'], 'printInvoice');
$rand = rand();
?>
<style type="text/css">
    .disabled{
        background: #f5f5f5;
        color: #565656;
        width: auto;
        height: auto;
        border: 1px #E2E4FF solid;
    }
    .disabled:hover{
        background: #f5f5f5;
        border-right: 1px #dedede solid;
        border-bottom: 1px #dedede solid;
        border-top: 1px #eeeeee solid;
        border-left: 1px #eeeeee solid;
        color: #565656;
        cursor: auto;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPurchaseReceive").click(function(event){
            event.preventDefault();
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
            leftPanel.html("<?php echo ACTION_LOADING; ?>");
            leftPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/1");
        });
        $(".btnReprintInvoiceReceive").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+$(this).attr("rel"),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(printInvoiceResult){
                    w=window.open();
                    w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                    w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                    w.document.write(printInvoiceResult);
                    w.document.close();
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPurchaseReceive">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset style="width:98%">
    <legend><?php __(MENU_INFO_PURCHASE_ORDER); ?></legend>
    <?php
    $sqlAp = mysql_query("SELECT CONCAT(account_codes, ' - ', account_description) FROM chart_accounts WHERE id = ".$this->data['PurchaseOrder']['ap_id']);
    @$rowAp = mysql_fetch_array($sqlAp);
    ?>
    <table style="width: 100%;" cellpadding="5" cellspacing="0">
        <tr>
            <td style="font-size: 12px;"><?php echo TABLE_COMPANY; ?>:</td>
            <td style="font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
            <td style="font-size: 12px;">A/P:</td>
            <td style="font-size: 12px;" colspan="3"><?php echo @$rowAp[0]; ?></td>
        </tr>
        <tr>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PB_NUMBER; ?> :</td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;">
                <?php echo $this->data['PurchaseOrder']['po_code']; ?>
            </td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
            <td style="width: 20%; font-size: 12px;">
                <?php echo $this->data['PurchaseOrder']['invoice_code']; ?>
            </td>
            <td style="width: 15%; text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
            <td style="width: 28%; font-size: 12px;">
                <?php echo $this->data['LocationGroup']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PB_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo dateShort($this->data['PurchaseOrder']['order_date'], 'd/M/Y'); ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <?php if($this->data['PurchaseOrder']['invoice_date'] != '0000-00-00' && $this->data['PurchaseOrder']['invoice_date'] != ''){ echo dateShort($this->data['PurchaseOrder']['invoice_date'], 'd/M/Y'); } ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_LOCATION; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $this->data['Location']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PO_NUMBER; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $this->data['PurchaseRequest']['pr_code']; ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_PO_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $this->data['PurchaseRequest']['pr_code'] != '0000-00-00'?dateShort($this->data['PurchaseRequest']['order_date'], 'd/M/Y'):""; ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_VENDOR; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $this->data['Vendor']['name']; ?>
            </td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_SHIPMENT_BY; ?> :</td>
            <td style="font-size: 12px;">
                <?php echo $this->data['Shipment']['name']; ?>
            </td>
            <td style="text-transform: uppercase; font-size: 12px;"><?php echo TABLE_NOTE; ?> :</td>
            <td colspan="3" style="font-size: 12px;">
                <?php echo $this->data['PurchaseOrder']['note']; ?>
            </td>
        </tr>
    </table>
</fieldset>
<?php
foreach($resultDetails AS $resultDetail){
?>
<div id="bodyDetail<?php echo $rand; ?>" style="margin-top:10px;">
    <fieldset style="width:98%">
        <legend><?php __(MENU_PURCHASE_RECEIVE_MANAGEMENT); ?></legend>
        <div style="float: right; width:30px;">
            <?php
                echo "<a href='#' class='btnReprintInvoiceReceive' rel='{$resultDetail['PurchaseReceiveResult']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT . ' ' . SALES_ORDER_INVOICE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
            ?>
        </div>
        <table cellpadding="5" width="100%">
            <tr>
                <td width="15%"><b><?php echo TABLE_DATE; ?> :</b></td>
                <td width="20%"><?php echo dateShort($resultDetail['PurchaseReceiveResult']['date']); ?></td>
                <td width="15%"><b><?php echo TABLE_CODE; ?> :</b></td>
                <td><?php echo $resultDetail['PurchaseReceiveResult']['code']; ?></td>
            </tr>
        </table>
    <fieldset style="width:98%; margin-bottom: 10px;">
        <legend><?php __(MENU_INFO_ITEM_RECEIVE); ?></legend>
        <table cellpadding="5" style="width:100%" class="table">
            <tr>
                <th class="first" style="width:8%"><?php echo TABLE_BARCODE; ?></th>
                <th style="width:8%"><?php echo TABLE_SKU; ?></th>
                <th style="width:25%"><?php echo TABLE_PRODUCT_NAME; ?></th>
                <th style="width:7%"><?php echo TABLE_QTY_DOC; ?></th>
                <th style="width:9%"><?php echo TABLE_QTY_RECEIVE; ?></th>
                <th style="width:15%"><?php echo TABLE_UOM; ?></th>
                <th style="width:9%"><?php echo TABLE_DATE_RECEIVE; ?></th>
                <th style="width:10%"><?php echo TABLE_LOTS_NO; ?></th>
                <th style="width:10%"><?php echo TABLE_EXPIRED_DATE; ?></th>
            </tr>
            <?php
            $sqlReceive = mysql_query("SELECT p.barcode AS barcode, p.code AS code, p.name AS name, pr.purchase_order_detail_id AS purchase_order_detail_id, pr.qty AS qty_receive, u.abbr AS uom_name, pr.received_date AS received_date, pr.lots_number AS lots_number, pr.date_expired AS date_expired FROM purchase_receives AS pr INNER JOIN products AS p ON p.id = pr.product_id INNER JOIN uoms AS u ON u.id = pr.qty_uom_id WHERE pr.purchase_receive_result_id = {$resultDetail['PurchaseReceiveResult']['id']} AND pr.status = 1;");
            while($rowReceive = mysql_fetch_array($sqlReceive)){
                $sqlDetails = mysql_query("SELECT SUM(qty + qty_free) AS qty_doc FROM purchase_order_details WHERE id IN ({$rowReceive['purchase_order_detail_id']})");
                $rowDetails = mysql_fetch_array($sqlDetails);
            ?>
            <tr>
                <td class="first"><?php echo $rowReceive['barcode']; ?></td>
                <td><?php echo $rowReceive['code']; ?></td>
                <td><?php echo $rowReceive['name']; ?></td>
                <td><?php echo $rowDetails['qty_doc']; ?></td>
                <td><?php echo $rowReceive['qty_receive']; ?></td>
                <td><?php echo $rowReceive['uom_name']; ?></td>
                <td><?php echo dateShort($rowReceive['received_date']); ?></td>
                <td>
                    <?php 
                    if($rowReceive['lots_number'] != '0' && $rowReceive['lots_number'] != ''){
                        echo $rowReceive['lots_number']; 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($rowReceive['date_expired'] != '0000-00-00' && $rowReceive['date_expired'] != ''){
                        echo dateShort($rowReceive['date_expired']);
                    }
                    ?>
                </td>
            </tr>
            <?php
            }
            ?>
        </table>
    </fieldset>
</fieldset>
</div>
<?php
}
?>