<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%;} 
</style>
<div class="print_doc">
    <?php
    $sqlCom = mysql_query("SELECT photo FROM companies WHERE id = ".$this->data['PurchaseOrder']['company_id']);
    $rowCom = mysql_fetch_array($sqlCom);
    include("includes/function.php");
    $msg = MENU_PURCHASE_RECEIVE_MANAGEMENT;
    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $this->data['PurchaseReceiveResult']['code'], 'logo' => $rowCom[0]));
    ?>
    <div style="height: 10px"></div>
    <table width="100%">
        <tr>
            <td style="width: 17%; text-transform: uppercase; font-size: 10px;"><?php echo TABLE_PB_RECEIVE_NUMBER; ?> :</td>
            <td style="width: 10%; font-size: 10px;"><?php echo $this->data['PurchaseReceiveResult']['code']; ?></td>
            <td style="width: 15%; text-transform: uppercase; font-size: 10px;"><?php echo TABLE_INVOICE_CODE; ?> :</td>
            <td style="width: 10%; font-size: 10px;"><?php echo $this->data['PurchaseOrder']['invoice_code']; ?></td>
            <td style="width: 17%; text-transform: uppercase; font-size: 10px;"><?php echo TABLE_PB_NUMBER; ?> :</td>
            <td style="width: 10%; font-size: 10px;"><?php echo $this->data['PurchaseOrder']['po_code']; ?></td>
            <td style="width: 10%; text-transform: uppercase; font-size: 10px;"><?php echo TABLE_LOCATION_GROUP; ?> :</td>
            <td style="width: 10%; font-size: 10px;">
                <?php 
                $sqlLocGroup = mysql_query("SELECT name FROM location_groups WHERE id = {$this->data['PurchaseOrder']['location_group_id']}");
                if(mysql_num_rows($sqlLocGroup)){
                    $rowLocGroup = mysql_fetch_array($sqlLocGroup);
                    echo $rowLocGroup[0];
                }
                ?>
            </td>
        </tr>
        <tr>
            <td style="text-transform: uppercase; font-size: 10px;"><?php echo TABLE_PB_RECEIVE_DATE; ?> :</td>
            <td style="font-size: 10px;"><?php echo dateShort($this->data['PurchaseReceiveResult']['date'], 'd/M/Y'); ?></td>
            <td style="text-transform: uppercase; font-size: 10px;"><?php echo TABLE_INVOICE_DATE; ?> :</td>
            <td style="font-size: 10px;"><?php if($this->data['PurchaseOrder']['invoice_date'] != '0000-00-00' && $this->data['PurchaseOrder']['invoice_date'] != ''){ echo dateShort($this->data['PurchaseOrder']['invoice_date'], 'd/M/Y'); } ?></td>
            <td style="text-transform: uppercase; font-size: 10px;"><?php echo TABLE_SHIPMENT_BY; ?> :</td>
            <td style="font-size: 10px;">
                <?php 
                if($this->data['PurchaseOrder']['shipment_id'] > 0){
                    $sqlShipment = mysql_query("SELECT name FROM shipments WHERE id = {$this->data['PurchaseOrder']['shipment_id']}");
                    if(mysql_num_rows($sqlShipment)){
                        $rowShipment = mysql_fetch_array($sqlShipment);
                        echo $rowShipment[0];
                    }
                }
                ?>
            </td>
            <td style="text-transform: uppercase; font-size: 10px;"><?php echo TABLE_LOCATION; ?> :</td>
            <td style="font-size: 10px;">
                <?php 
                if($this->data['PurchaseOrder']['location_id'] > 0){
                    $sqlLocation = mysql_query("SELECT name FROM locations WHERE id = {$this->data['PurchaseOrder']['location_id']}");
                    if(mysql_num_rows($sqlLocation)){
                        $rowLocation = mysql_fetch_array($sqlLocation);
                        echo $rowLocation[0];
                    }
                }
                ?>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table class="table_print" style="border: none;">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th style="text-transform: uppercase; width: 15%; font-size: 12px;"><?php echo TABLE_SKU; ?></th>
                    <th style="text-transform: uppercase; width: 30%; font-size: 12px;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th style="text-transform: uppercase; width: 10%; font-size: 12px;"><?php echo TABLE_QTY; ?></th>
                    <th style="text-transform: uppercase; width: 15%; font-size: 12px;"><?php echo TABLE_UOM; ?></th>
                    <th style="text-transform: uppercase; width: 13%; font-size: 12px;"><?php echo TABLE_LOTS_NO; ?></th>
                    <th style="text-transform: uppercase; width: 13%; font-size: 12px;"><?php echo TABLE_EXPIRED_DATE; ?></th>
                </tr>
                <?php
                $index = 0;
                $sqlReceive = mysql_query("SELECT p.barcode AS barcode, p.code AS code, p.name AS name, pr.purchase_order_detail_id AS purchase_order_detail_id, pr.qty AS qty_actual, u.abbr AS uom_name, pr.received_date AS received_date, pr.lots_number AS lots_number, pr.date_expired AS date_expired FROM purchase_receives AS pr INNER JOIN products AS p ON p.id = pr.product_id INNER JOIN uoms AS u ON u.id = pr.qty_uom_id WHERE pr.purchase_receive_result_id = {$this->data['PurchaseReceiveResult']['id']} AND pr.status = 1;");
                while($rowReceive = mysql_fetch_array($sqlReceive)){
                ?>
                    <tr>
                        <td class="first" style="text-align: left; font-size: 10px;"><?php echo++$index; ?></td>
                        <td style="font-size: 12px;"><?php echo $rowReceive['code']; ?></td>
                        <td style="font-size: 12px;"><?php echo $rowReceive['name']; ?></td>
                        <td style="font-size: 12px; text-align: center;"><?php echo number_format($rowReceive['qty_actual'], 0); ?></td>
                        <td style="font-size: 12px; text-align: center;"><?php echo $rowReceive['uom_name']; ?></td>
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
        </div>
        <br />
        <div style="float:left; width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
    <div style="width: 100%; position: fixed; bottom: 40px;" class="print-footer">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 10px; width: 50%; height: 50px; vertical-align: top; text-transform: uppercase;"><?php echo TABLE_PREPARE_BY;?></td>
                <td style="font-size: 10px; width: 50%; height: 50px; vertical-align: top; text-transform: uppercase;"><?php echo TABLE_CHECKED_RECEIVE_BY;?></td>
            </tr>
        </table>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).dblclick(function(){
            window.close();
        });
        $("#btnDisappearPrint").click(function(){
            $("#footerPrint").show();
            window.print();
            window.close();
        });
    });
</script>