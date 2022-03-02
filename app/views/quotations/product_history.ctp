<?php 
$tblName = "tbl" . rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/productHistoryAjax/<?php echo $product['Product']['id']; ?>/<?php echo $customerId; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $("#<?php echo $tblName; ?> td:first-child").addClass('first');
                $("#<?php echo $tblName; ?> td:nth-child(3)").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> td:last-child").css("white-space", "nowrap");
                $("#<?php echo $tblName; ?> tr").click(function(){
                    $(this).find("input[name='chkProduct']").attr("checked", true);
                });
                return sPre;
            },
            "aoColumnDefs": [{
                    "sType": "numeric", "aTargets": [ 0 ],
                    "bSortable": false, "aTargets": [ 0,-1,-2,-3,-4 ]
                }],
            "aaSorting": [[1, "desc"]]
        });
    });
</script>
<br />
<div id="dynamic">
    <div style="width: 35%; float: left; vertical-align: top;">
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <?php
            if($product['Product']['photo'] != ''){
                $photo = 'public/product_photo/'.$product['Product']['photo'];
            } else {
                $photo = 'img/button/no-images.png';
            }
            ?>
            <tr>
                <td colspan="2">
                    <img alt="" src="<?php echo $this->webroot.$photo; ?>" style="max-width: 150px; max-height: 150px;" />
                </td>
            </tr>
            <?php
            $sqlUom = mysql_query("SELECT name FROM uoms WHERE id = ".$product['Product']['price_uom_id']);
            $rowUom = mysql_fetch_array($sqlUom);
            ?>
            <tr>
                <td style="width: 30%;"><?php echo TABLE_SKU; ?> :</td>
                <td style="font-size: 12px;"><?php echo $product['Product']['code']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo TABLE_BARCODE; ?> :</td>
                <td style="font-size: 12px;"><?php echo $product['Product']['barcode']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo TABLE_UOM; ?> :</td>
                <td style="font-size: 12px;"><?php echo $rowUom[0]; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;"><?php echo TABLE_PRODUCT_NAME; ?> :</td>
                <td style="font-size: 12px;"><?php echo $product['Product']['name']; ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;" colspan="2"><?php echo GENERAL_DESCRIPTION; ?> :</td>
            </tr>
            <tr>
                <td style="font-size: 12px;" colspan="2"><?php echo nl2br($product['Product']['description']); ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;" colspan="2"><?php echo TABLE_SPEC; ?> :</td>
            </tr>
            <tr>
                <td style="font-size: 12px;" colspan="2"><?php echo nl2br($product['Product']['spec']); ?></td>
            </tr>
        </table>
    </div>
    <div style="width: 64%; float: right; vertical-align: top;">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0" style="vertical-align: top;">
            <thead>
                <tr>
                    <th class="first"></th>
                    <th><?php echo TABLE_QUOTATION_DATE; ?></th>
                    <th><?php echo TABLE_QUOTATION_CODE; ?></th>
                    <th><?php echo TABLE_QTY; ?></th>
                    <th><?php echo TABLE_UOM; ?></th>
                    <th><?php echo TABLE_UNIT_PRICE_SHORT; ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>