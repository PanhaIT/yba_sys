<?php
include("includes/function.php");
$sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
$rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script> 
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#PurchaseReceiveReceiveAllForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("input[name='purchase_qty[]']").keypress(function(){
            
        });
        $(".expired_date, .date_receive_one").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy'
        }).unbind("blur");
        $("#PurchaseReceiveReceiveAllForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveReceiveAll").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                $(".expired_date, .date_receive_one").datepicker("option", "dateFormat", "yy-mm-dd");
                $("#date_receive_all").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            success: function(result) {
                loadDetail();
                $(".expired_date, .date_receive_one").datepicker("option", "dateFormat", "dd/mm/yy");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.id != 0){
                    createSysAct('Purchase Receive', 'Receive', 1, '');
                    $("#dialog").html('<div class="buttons"><button type="submit" class="positive printInvoiceReceive" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtPrintInvoice"><?php echo ACTION_PRINT_PURCHASE_RECEIVE; ?></span></button></div> ');
                    $(".printInvoiceReceive").click(function(){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/printInvoice/"+result.id,
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
                }else{
                    createSysAct('Purchase Receive', 'Receive', 2, result);
                    $("#dialog").html('<p><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?></p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    position: 'center',
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
        
        $(".qty_product_po").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                $(this).closest("tr").next().find(".qty_product_po").select().focus();
                return false;
            }
        });
    });
</script>
<?php
function checkReceive($productId, $uom_id, $order_id) {
    $sql = mysql_query ("SELECT id FROM `purchase_receives` as pr WHERE pr.purchase_order_id = " . $order_id . " and pr.product_id = " . $productId . " and pr.qty_uom_id = " . $uom_id . " LIMIT 1");
    if (@$num = mysql_num_rows($sql)) {
        return 1;
    } else {
        return 0;
    }
}

function countReceive($productId, $uom_id, $order_id) {
    $total = 0;
    $sql = mysql_query("SELECT pr.qty as qty FROM `purchase_receives` as pr WHERE pr.purchase_order_id = " . $order_id . " and pr.product_id = " . $productId . " and pr.qty_uom_id = " . $uom_id . " and pr.status = 1");
    while (@$r = mysql_fetch_array($sql)) {
        $total += $r['qty'];
    }
    return $total;
}

function countReject($productId, $uom_id, $order_id) {
    $total = 0;
    $sql = mysql_query("SELECT pr.qty as qty FROM `purchase_receives` as pr WHERE pr.purchase_order_id = " . $order_id . " and pr.product_id = " . $productId . " and pr.qty_uom_id = " . $uom_id . " and pr.status = 2");
    while (@$r = mysql_fetch_array($sql)) {
        $total += $r['qty'];
    }
    return $total;
}
$remain="";
?>
    <input type="hidden" value="<?php echo $rowSettingUomDetail[1]; ?>" name="data[calculate_cogs]" />
    <input type="hidden" value="<?php echo $order['PurchaseOrder']['location_id']; ?>" name="data[location]" />
    <input type="hidden" value="<?php echo $order['PurchaseOrder']['vendor_id']; ?>" name="data[vendor]" />
    <input type="hidden" value="<?php echo $order['PurchaseOrder']['id']; ?>" name="data[purchase_order_id]" id="purchase_bill_id" />
    <input type="hidden" value="<?php echo $order['PurchaseOrder']['po_code']; ?>" name="data[po_code]" />
    <input type="hidden" value="<?php echo $order['PurchaseOrder']['order_date']; ?>" name="data[order_date]" id="pb_date" />    
    <!-- Table List Purchase Order -->
    <fieldset style="width:98%; margin-bottom: 10px;">
        <legend><?php __(MENU_INFO_ITEM_RECEIVE); ?></legend>
        <table cellpadding="5" style="width:100%" class="table">
            <tr>
                <th class="first" style="width:8%;"><?php echo TABLE_BARCODE; ?></th>
                <th style="width:8%;"><?php echo TABLE_SKU; ?></th>
                <th style="width:18%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
                <th style="width:7%;"><?php echo TABLE_QTY_DOC; ?></th>
                <th style="width:9%"><?php echo TABLE_QTY_RECEIVE; ?></th>
                <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
                <th style="width:9%;"><?php echo TABLE_DATE_RECEIVE; ?></th>
                <th style="width:9%; <?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>"><?php echo TABLE_LOTS_NO; ?></th>
                <th style="width:9%;"><?php echo TABLE_EXPIRED_DATE; ?></th>
                <th style="width:9%;"><?php echo TABLE_STATUS; ?></th>
            </tr>
            <?php            
            $btnSave = false;
            $i = 1;
            $j = 1;
            $cmt = "SELECT GROUP_CONCAT(pd.id) as detail_id,po.id as order_d_id, pd.default_cost as cost, 
            po.location_id as location, pd.purchase_order_id as order_id, 
            u.id as uom_id, pd.product_id as product_id, p.name as product, p.code as code, p.barcode as barcode, IF(p.unit_cost > 0,p.unit_cost,p.default_cost) as old_unit_cost, p.small_val_uom as small_val_uom, p.is_lots,
            SUM(pd.qty + pd.qty_free) as qty, pd.lots_number AS lots_number, pd.date_expired AS date_expired, u.name as uom, po.vendor_id as vendor_id, pd.conversion as conversion, p.is_expired_date, p.price_uom_id,pd.qty_uom_id
            FROM `purchase_orders` as po 
            INNER JOIN `purchase_order_details` as pd ON pd.purchase_order_id = po.id 
            INNER JOIN `products` as p ON p.id = pd.product_id 
            INNER JOIN `uoms` as u ON u.id = pd.qty_uom_id WHERE po.id = " . $id . " GROUP BY pd.product_id, pd.qty_uom_id, pd.lots_number, pd.date_expired";
            $sql = mysql_query($cmt);
            $mainQty = array();
            if (@$num = mysql_num_rows($sql)) {
                while ($row = mysql_fetch_array($sql)) {
                    if (array_key_exists($row['product_id'],$mainQty)){
                        $mainQty[$row['product_id']] += $row['qty'];
                    }else{
                        $mainQty[$row['product_id']] = $row['qty'];
                    }
                    $queryUom=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$row['price_uom_id']."
                                           UNION
                                           SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$row['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$row['price_uom_id'].")
                                           ORDER BY conversion ASC");
                    while($dataUom=mysql_fetch_array($queryUom)){
                        if($row['qty_uom_id']==$dataUom["id"]){
                            $conversion  = $dataUom['conversion'];
                        }
                    }                                        
                    
                    $valueUom = $row['conversion'];
                    $new_cost = $row['cost'];
                    $smallUom = $row['small_val_uom'];

                    if ($i % 2 == 0) {
                        $class = "class='even detail'";
                    } else {
                        $class = "class='odd detail'";
                    }
                    if (checkReceive($row['product_id'], $row['uom_id'], $id)) {
                        $remain = $mainQty[$row['product_id']] - (countReceive($row['product_id'], $row['uom_id'], $id) + countReject($row['product_id'], $row['uom_id'], $id));
                        $s = mysql_query("SELECT pr.received_date as received_date, pr.lots_number as lots_number, pr.date_expired as expired, pr.id as id, pr.qty as qty, pr.status as status FROM `purchase_receives` as pr WHERE pr.product_id = " . $row['product_id'] . " and pr.qty_uom_id = " . $row['uom_id'] . " and pr.purchase_order_id = " . $row['order_d_id'] . " AND pr.purchase_order_detail_id IN (".$row['detail_id'].") and pr.status != 0");
                        while ($r = mysql_fetch_array($s)) {
                            if ($i % 2 == 0) {
                                $class = "class='even detail'";
                            } else {
                                $class = "class='odd detail'";
                            }
                            $action = 2;
                            ?>
                            <tr <?php echo $class; ?>>
                                <td class="first">
                                    <?php echo $row['barcode']; ?>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo str_replace(",", "", number_format($row['old_unit_cost'], 3)); ?>" name="old_unit_cost[]" />
                                    <?php echo $row['code']; ?>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="purchase_detail_id[]" />
                                    <input type="hidden" value="<?php echo $valueUom; ?>" name="uom_conversion[]" />
                                    <input type="hidden" value="<?php echo $smallUom; ?>" name="small_uom[]" />
                                    <input type="hidden" value="<?php echo $r['qty']; ?>" name="total_qty[]" />
                                    <input type="hidden" value="<?php echo $new_cost; ?>" name="cost_inventory[]" />
                                    <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id[]" />
                                    <input type="hidden" value="<?php echo $row['cost']; ?>" name="product_cost[]" />
                                    <?php echo $row['product']; ?></td>
                                <td><?php echo $r['qty']; ?></td>
                                <td>
                                    <input type="hidden" value="0" name="purchase_qty[]" />
                                    <?php echo $r['qty']; ?>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo $row['uom_id']; ?>" name="purchase_uom[]" />
                                    <?php echo $row['uom']; ?>
                                </td>
                                <td>
                                    <input type="hidden" style="width:180px;" name="date_receive_one[]" />
                                    <?php
                                    if (@$r['received_date'] != null && @$r['received_date'] != "0000-00-00" && @$r['received_date'] != "") {
                                        echo dateShort($r['received_date']);
                                    }
                                    ?>
                                </td>
                                <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                    <input type="hidden" value="<?php echo $row['lots_number']; ?>" name="lots_number[]" />
                                    <?php echo $r['lots_number']; ?>
                                </td>
                                <td>
                                    <input type="hidden" style="width:180px;" name="expired_date_inventory[]" />
                                    <?php
                                    if (@$r['expired'] != null && @$r['expired'] != "0000-00-00" && @$r['expired'] != "") {
                                        echo dateShort($r['expired']);
                                    }
                                    ?>
                                </td>
                                <td id="status_<?php echo $i; ?>">
                                    <?php
                                    if ($r['status'] == 1) {
                                        echo 'Accepted';
                                    } else {
                                        echo 'Rejected';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $j++;
                        }
                        // Total Remain After Received
                        if ($remain > 0) {
                            $btnSave = true;
                            if ($i % 2 == 0) {
                                $class = "class='even detail'";
                            } else {
                                $class = "class='odd detail'";
                            }
                            ?>
                            <tr <?php echo $class; ?>>
                                <td class="first">
                                    <?php echo $row['barcode']; ?>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo str_replace(",", "", number_format($row['old_unit_cost'], 3)); ?>" name="old_unit_cost[]" />
                                    <?php echo $row['code']; ?>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="purchase_detail_id[]" />
                                    <input type="hidden" value="<?php echo $valueUom; ?>" name="uom_conversion[]" />
                                    <input type="hidden" value="<?php echo $smallUom; ?>" name="small_uom[]" />
                                    <input type="hidden" value="<?php echo $remain; ?>" name="total_qty[]" />
                                    <input type="hidden" value="<?php echo $new_cost; ?>" name="cost_inventory[]" />
                                    <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id[]" />
                                    <input type="hidden" value="<?php echo $row['cost']; ?>" name="product_cost[]" />
                                    <?php echo $row['product']; ?></td>
                                <td><?php echo $remain; ?></td>
                                <td>
                                    <div class="inputContainer">
                                        <input type="text" style="width:90%;" value="<?php echo $remain; ?>"  class="validate[required,min[0],max[<?php echo $remain; ?>],custom[number]] qty_product_po" name="purchase_qty[]" id="product<?php echo $i; ?>" />
                                    </div>
                                </td>
                                <td>
                                    <input type="hidden" value="<?php echo $row['uom_id']; ?>" name="purchase_uom[]" />
                                    <?php echo $row['uom']; ?>
                                </td>
                                <td>
                                    <div class="inputContainer" style="width:100%">
                                        <input type="text" style="width:90%;" id="date_receive_one_<?php echo $i; ?>" name="date_receive_one[]" class="date_receive_one" />
                                    </div>
                                </td>
                                <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                    <div class="inputContainer" style="width:100%">
                                        <span  style="width:30px;" class="red">*</span>&nbsp;&nbsp;&nbsp;<input type="text" style="width:80%;" value="<?php echo $row['lots_number']; ?>" id="lots_number<?php echo $i; ?>" name="lots_number[]" class="lots_number <?php if($rowSettingUomDetail[0] == 1){ ?>validate[required]<?php } ?>" />
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        if($row['is_expired_date']==1){    
                                            $expiryDate = ($row['date_expired'] != '' && $row['date_expired'] != '0000-00-00')?dateShort($row['date_expired']):'';
                                            echo '<div style="width:180px;">';
                                                echo '<span  style="width:30px;" class="red">*</span>&nbsp;&nbsp;&nbsp;';
                                                echo '<input type="text" style="width:90%;" id="expired_date_inventory_'.$i.'" value="'.$expiryDate.'" name="expired_date_inventory[]" class="expired_date validate[required]" />';
                                            echo '</div>';
                                        }else{
                                            echo '<input type="hidden" style="width:90%;" name="expired_date_inventory[]" />';
                                        }
                                    ?>                                    
                                </td>
                                <td id="status_<?php echo $i; ?>">Entered</td>
                            </tr>
                            <?php
                            $j--;
                        }
                    } else { // Else checkReceive
                        $btnSave = true;
                    ?>
                        <tr <?php echo $class; ?>>
                            <td class="first">
                                <?php echo $row['barcode']; ?>
                            </td>
                            <td>
                                <input type="hidden" value="<?php echo str_replace(",", "", number_format($row['old_unit_cost'], 3)); ?>" name="old_unit_cost[]" />
                                <?php echo $row['code']; ?>
                            </td>
                            <td>
                                <input type="hidden" value="<?php echo $row['detail_id']; ?>" name="purchase_detail_id[]" />
                                <input type="hidden" value="<?php echo $valueUom; ?>" name="uom_conversion[]" />
                                <input type="hidden" value="<?php echo $smallUom; ?>" name="small_uom[]" />
                                <input type="hidden" value="<?php echo $row['qty']; ?>" name="total_qty[]" />
                                <input type="hidden" value="<?php echo $new_cost; ?>" name="cost_inventory[]" />
                                <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id[]" />
                                <input type="hidden" value="<?php echo $row['cost']; ?>" name="product_cost[]" />
                                <?php echo $row['product']; ?></td>
                            <td><?php echo $row['qty']; ?></td>
                            <td>
                                <div class="inputContainer">
                                    <input type="text" style="width:90%;" value="<?php echo $row['qty']; ?>" class="validate[required,min[0],max[<?php echo $row['qty']; ?>],custom[number]] qty_product_po" name="purchase_qty[]" id="product<?php echo $i; ?>" />
                                </div>
                            </td>
                            <td>
                                <input type="hidden" value="<?php echo $row['uom_id']; ?>" name="purchase_uom[]" />
                                <?php echo $row['uom']; ?>
                            </td>
                            <td>
                                <div class="inputContainer" style="width:100%">
                                    <input type="text" style="width:90%;" id="date_receive_one_<?php echo $i; ?>" name="date_receive_one[]" class="date_receive_one" />
                                </div>
                            </td>
                            <td style="<?php if($rowSettingUomDetail[0] == 0){ ?>display: none;<?php } ?>">
                                <div class="inputContainer" style="width:100%">
                                    <span  style="width:30px;" class="red">*</span>&nbsp;&nbsp;&nbsp;
                                    <?php
                                    if($row['is_lots'] == 1){
                                    ?>
                                    <input type="text" style="width:80%;" value="<?php echo $row['lots_number']; ?>" id="lots_number<?php echo $i; ?>" name="lots_number[]" class="lots_number <?php if($rowSettingUomDetail[0] == 1){ ?>validate[required]<?php } ?>" readonly="readonly" />
                                    <?php
                                    } else {
                                    ?>
                                    <input type="hidden" style="width:80%;" value="<?php echo $row['lots_number']; ?>" id="lots_number<?php echo $i; ?>" name="lots_number[]" class="lots_number" />
                                    <?php
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    if($row['is_expired_date']==1){  
                                        $expiryDate = ($row['date_expired'] != '' && $row['date_expired'] != '0000-00-00')?dateShort($row['date_expired']):'';
                                        echo '<div style="width:180px;">';
                                            echo '<span  style="width:30px;" class="red">*</span>&nbsp;&nbsp;&nbsp;';
                                            echo '<input type="text" style="width:90%;" id="expired_date_inventory_'.$i.'" value="'.$expiryDate.'" name="expired_date_inventory[]" class="expired_date validate[required]" />';                                      
                                        echo '</div>';
                                    }else{
                                        echo '<input type="hidden" style="width:90%;" name="expired_date_inventory[]" />';
                                    }
                                ?>
                            </td>
                            <td id="status_<?php echo $i; ?>">Entered</td>
                        </tr>
                        <?php
                    } // End checkReceive
                    $i++;
                } // End while 
            } else { // Else Num
                ?>
                <tr>
                    <td class="first odd" colspan="10"><?php echo TABLE_LOADING; ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </fieldset>
    <?php
    if ($btnSave) {
    ?>
        <div class="buttons">
            <button type="submit" class="positive">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtSaveReceiveAll"><?php echo ACTION_RECEIVE; ?></span>
            </button>
        </div>
    <?php
    }
    ?>
