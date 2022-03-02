<?php
$rand = rand();
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/listbox.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen/chosen.css" />
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/harvesthq-chosen-v0.9.1/chosen/chosen.jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("input[name='data[chkClose][]']").click(function(){
            if($(this).is(":checked")){
                $(this).closest("tr").find(".is_close").val(1);
            }else{
                $(this).closest("tr").find(".is_close").val(0);
            }
        });
    });
</script>
<?php echo $this->Form->create(); ?>
<fieldset>
    <legend><?php __(TABLE_CODE . ': ' . $purchaseRequest['PurchaseRequest']['pr_code']); ?></legend>
    <div>
        <!-- status == 1 close all & status == 2 no close all -->
        <input type="hidden" value="1" id="statusPRPO" name="data[closeAll]" />
        <input type="hidden" value="<?php echo $purchaseRequest['PurchaseRequest']['id']; ?>" name="data[id]" id="PRPOid" />
        <table class="info" style="width: 600px;">
            <tr>
                <th><?php echo PRICING_RULE_CUSTOMER; ?></th>
                <td><?php echo $vendor['Vendor']['name']; ?></td>
                <th><?php echo TABLE_CODE; ?></th>
                <td><?php echo $vendor['Vendor']['vendor_code']; ?></td>
            </tr>
            <tr>
                <th><?php echo SALES_ORDER_DATE; ?></th>
                <td><?php echo date('d/m/Y', strtotime($purchaseRequest['PurchaseRequest']['order_date'])); ?></td>
                <th><?php echo TABLE_LOCATION; ?></th>
                <td><?php echo $purchaseRequest['Location']['name']; ?></td>
            </tr>
        </table>
    </div>
    <?php
        if (!empty($purchaseRequestDetails)) {
    ?>
            <div>
                <fieldset>
                    <legend><?php echo TABLE_PRODUCT; ?></legend>
                    <table class="table tblPRDPO">
                        <tr>
                            <th class="first"><?php echo TABLE_NO; ?></th>
                            <th style="width: 30%;"><?php echo TABLE_NAME ?></th>
                            <th><?php echo TABLE_QTY_PO; ?></th>
                            <th><?php echo TABLE_QTY_PB; ?></th>
                            <th><?php echo TABLE_UOM; ?></th>
                            <th><?php echo TABLE_CLOSE; ?></th>
                        </tr>
                <?php
                $index = 0;
                foreach ($purchaseRequestDetails as $purchaseRequestDetail) {
                    $qtyPB = "";
                    $conversion="";                    
                    $sql = mysql_query("SELECT sum(qty) As qty, qty_uom_id FROM po_pb_details WHERE purchase_request_detail_id = ".$purchaseRequestDetail['PurchaseRequestDetail']['id']." AND is_active = 1 GROUP BY conversion ORDER BY conversion DESC");
                    if(@mysql_num_rows($sql)){
                        while($r=mysql_fetch_array($sql)){
                            $query = mysql_query ("SELECT name FROM uoms WHERE id = '". $r['qty_uom_id']."' AND is_active = 1");
                            while ($row = mysql_fetch_array($query)) {
                                  $qtyPB .= $r['qty'].' '.$row['name'].' ';                                  
                            }
                            $conversion = $row['conversion'];
                            
                        }
                    }
                ?>
                        <tr>
                            <td class="first" style="text-align: left;">
                            <input type="hidden" class="is_close" value="<?php if($purchaseRequestDetail['PurchaseRequestDetail']['is_close'] == 1){ echo 1; }else{ echo 0; } ?>" name="data[is_close][]"/>
                            <input type="hidden" value="<?php echo $purchaseRequestDetail['PurchaseRequestDetail']['id']; ?>" class="get_id_prdid" name="data[get_id][]" />
                            <?php echo++$index; ?>
                            </td>
                            <td><?php echo $purchaseRequestDetail['Product']['code'] . ' - ' . $purchaseRequestDetail['Product']['name']; ?></td>
                            <td style="text-align: left"><?php echo number_format($purchaseRequestDetail['PurchaseRequestDetail']['qty'], 2); ?></td>
                            <td id="<?php echo $purchaseRequestDetail['PurchaseRequestDetail']['id']; ?>" class="clsPRDPO"><?php echo $qtyPB; ?></td>
                            <td style="text-align: left"><?php echo $purchaseRequestDetail['Uom']['name']; ?></td>
                            <td>
                                <input type="checkbox" value="<?php echo $purchaseRequestDetail['PurchaseRequestDetail']['id']; ?>" <?php if($purchaseRequestDetail['PurchaseRequestDetail']['is_close'] == 1){ ?>checked="checked"<?php } ?> class="chkPRDPO" name="data[chkClose][]" />
                            </td>
                        </tr>
                <?php
                }
                ?>
            </table>
        </fieldset>
    </div>
    <?php
            }
    ?>
</fieldset>    
<?php echo $this->Form->end(); ?>