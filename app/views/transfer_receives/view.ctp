<?php
include("includes/function.php");
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
        $(".btnReprintInvoiceTOReceive").click(function(event){
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
    <legend><?php __(MENU_TO_RECEIVE_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_COMPANY; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $order['Company']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_NUMBER; ?> :</td>
            <td style="font-size: 12px; width: 20%;">
                <div class="inputContainer">
                    <?php echo $order['TransferOrder']['to_code']; ?>
                </div>
            </td>
            <td style="font-size: 12px; width: 9%;"><?php echo TABLE_TO_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo dateShort($order['TransferOrder']['order_date']); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php echo TABLE_FROM_WAREHOUSE; ?>  :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $fromLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>        
            <td style="font-size: 12px;"><?php echo TABLE_TO_WAREHOUSE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php echo $toLocationGroups['LocationGroup']['name']; ?>
                </div>
            </td>
            <td style="font-size: 12px;"><?php echo TABLE_FULFILLMENT_DATE; ?> :</td>
            <td style="font-size: 12px;">
                <div class="inputContainer">
                    <?php 
                    if($order['TransferOrder']['fulfillment_date']!='0000-00-00')
                        echo dateShort($order['TransferOrder']['fulfillment_date']); 
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_REQUEST_STOCK_NUMBER; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php echo $order['RequestStock']['code']; ?>
                </div>
            </td>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_TYPE; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php 
                    if($order['TransferOrder']['type'] == 1){
                        echo TABLE_TRANSFER;
                    } else {
                        echo TABLE_CONSIGNMENT; 
                    }
                    ?>
                </div>
            </td>
            <td style="font-size: 12px; vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
            <td style="font-size: 12px; vertical-align: top;">
                <div class="inputContainer">
                    <?php echo nl2br($order['TransferOrder']['note']); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<?php
foreach($resultDetails AS $resultDetail){
?>
<div id="bodyDetail<?php echo $rand; ?>" style="margin-top:10px;">
<fieldset style="width:98%">
    <legend><?php __(MENU_TO_RECEIVE_MANAGEMENT_INFO); ?></legend>
    <div style="float: right; width:30px;">
        <?php
            echo "<a href='#' class='btnReprintInvoiceTOReceive' rel='{$resultDetail['TransferOrder']['id']}' ><img alt='Print'  onmouseover='Tip(\"" . ACTION_PRINT_TRANSFER_RECEIVE . "\")'  src='{$this->webroot}img/button/printer.png' /></a>";
        ?>
    </div>
    <table cellpadding="5" width="100%">
        <tr>
            <td width="15%"><b><?php echo TABLE_DATE; ?> :</b></td>
            <td width="20%"><?php echo dateShort($resultDetail['TransferReceiveResult']['date']); ?></td>
            <td width="15%"><b><?php echo TABLE_CODE; ?> :</b></td>
            <td><?php echo $resultDetail['TransferReceiveResult']['code']; ?></td>
        </tr>
    </table>
    <fieldset style="width:98%; margin-bottom: 10px;">
        <legend><?php __(MENU_INFO_ITEM_RECEIVE); ?></legend>
        <table cellpadding="5" style="width:100%" class="table">
            <tr>
                <th class="first" style="width:8%"><?php echo TABLE_BARCODE; ?></th>
                <th style="width:8%"><?php echo TABLE_SKU; ?></th>
                <th style="width:30%"><?php echo TABLE_PRODUCT_NAME; ?></th>
                <th style="width:7%"><?php echo TABLE_QTY_TRANSFER; ?></th>
                <th style="width:9%"><?php echo TABLE_QTY_RECEIVE; ?></th>
                <th style="width:10%"><?php echo TABLE_UOM; ?></th>
                <th style="width:10%"><?php echo TABLE_LOTS_NO; ?></th>
                <th style="width:10%"><?php echo TABLE_EXPIRED_DATE; ?></th>
            </tr>
            <?php
            $sqlReceive = mysql_query("SELECT p.barcode AS barcode, p.code AS code, p.name AS name, (SELECT qty FROM transfer_order_details WHERE id = pr.transfer_order_detail_id) AS qty_doc, pr.qty AS qty_receive, u.abbr AS uom_name, pr.lots_number AS lots_number, pr.expired_date AS date_expired FROM transfer_receives AS pr INNER JOIN products AS p ON p.id = pr.product_id INNER JOIN uoms AS u ON u.id = pr.qty_uom_id WHERE pr.transfer_receive_result_id = {$resultDetail['TransferReceiveResult']['id']} AND pr.status = 1;");
            while($rowReceive = mysql_fetch_array($sqlReceive)){
            ?>
            <tr>
                <td class="first"><?php echo $rowReceive['barcode']; ?></td>
                <td><?php echo $rowReceive['code']; ?></td>
                <td><?php echo $rowReceive['name']; ?></td>
                <td><?php echo $rowReceive['qty_doc']; ?></td>
                <td><?php echo $rowReceive['qty_receive']; ?></td>
                <td><?php echo $rowReceive['uom_name']; ?></td>
                <td><?php echo $rowReceive['lots_number']; ?></td>
                <td><?php echo dateShort($rowReceive['date_expired']); ?></td>
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