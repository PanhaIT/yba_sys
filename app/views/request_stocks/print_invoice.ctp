<?php 
    include("includes/function.php");
    $status = array('0'=>'Void','1'=>'Issued','2'=>'Fulfilled');
?>
<style type="text/css" media="screen">
    div.print-footer {display: none;}
</style> 
<style type="text/css" media="print">
    div.print_doc { width:100%;}
    #btnDisappearPrint { display: none;}
    div.print-footer {display: block; width:100%; } 
</style>
<div class="print_doc">
    <?php
    $msg = 'Stock Request';
    echo $this->element('/print/header', array('msg' => $msg, 'barcode' => $this->data['RequestStock']['code'], 'logo' => $this->data['Company']['photo']));
    ?>
    <div style="height: 10px"></div>
    <table width="100%">
        <tr>
            <td style="width: 15%; font-size: 10px; text-transform: uppercase;"><?php echo TABLE_REQUEST_STOCK_DATE; ?> :</td> 
            <td style="font-size: 10px;"><?php echo dateShort($this->data['RequestStock']['date']); ?></td>
            <td style="width: 15%; font-size: 10px; text-transform: uppercase;"><?php echo TABLE_REQUEST_STOCK_NUMBER; ?> :</td>
            <td style="font-size: 10px; text-align: left;"><?php echo $this->data['RequestStock']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 10px; text-transform: uppercase;"><?php echo TABLE_FROM_WAREHOUSE; ?> :</td> 
            <td style="font-size: 10px;"><?php echo $fromLocationGroups['LocationGroup']['name']; ?></td>
            <td style="font-size: 10px; text-transform: uppercase;"><?php echo TABLE_TO_WAREHOUSE; ?> :</td>
            <td style="font-size: 10px; text-align: left;"><?php echo $toLocationGroups['LocationGroup']['name']; ?></td>
        </tr>
    </table>
    <br />
    <div>
        <div>
            <table id="tblTO" class="table_print">
                <tr>
                    <th class="first" style="width:8%; font-size: 10px;"><?php echo TABLE_NO; ?></th>
                    <th style="width:15%; font-size: 10px;"><?php echo TABLE_SKU; ?></th>
                    <th style="width:15%; font-size: 10px;"><?php echo TABLE_BARCODE; ?></th>
                    <th style="width:35%; font-size: 10px;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                    <th style="width:12%; font-size: 10px;"><?php echo TABLE_QTY; ?></th>
                    <th style="width:15%; font-size: 10px;"><?php echo TABLE_UOM; ?></th>
                </tr>
                <?php
                    if(!empty($requestStockDetails)){
                        $index = 1;
                        foreach($requestStockDetails AS $requestStockDetail){
                ?>
                <tr class="listBodyRequestStock">
                    <td class="first" style="width:8%; font-size: 10px;"><?php echo $index; ?></td>
                    <td style="width:15%; font-size: 10px;"><?php echo $requestStockDetail['Product']['code']; ?></td>
                    <td style="width:15%; font-size: 10px;"><?php echo $requestStockDetail['Product']['barcode']; ?></td>
                    <td style="width:35%; font-size: 10px;">
                        <?php echo $requestStockDetail['Product']['name']; ?>
                    </td>
                    <td style="padding:0px; text-align: center; width:12%; font-size: 10px;">
                        <?php echo number_format($requestStockDetail['RequestStockDetail']['qty'], 0); ?>
                    </td>
                    <td style="padding:0px; text-align: center; width:15%; font-size: 10px;">
                        <?php echo $requestStockDetail['Uom']['abbr']; ?>
                    </td>
                </tr>
                <?php
                        $index++;
                    }
                }
                ?>
            </table>
        </div>
        <br />
        <div style="clear:both"></div>
        <div style="float:left;width: 450px">
            <div>
                <input type="button" value="<?php echo ACTION_PRINT; ?>" id='btnDisappearPrint' onClick='window.print();window.close();' class='noprint'>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
    <div style="width: 100%; position: fixed; bottom: 40px;" class="print-footer">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 10px;"><?php echo TABLE_ISSUED_BY;?>..............................</td>
                <td style="text-align: center; font-size: 10px;"><?php echo TABLE_STOCK_ISSUED_BY;?>..............................</td>
                <td style="text-align: right; font-size: 10px;"><?php echo TABLE_CHECKED_RECEIVE_BY;?>..............................</td>
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
    });
</script>