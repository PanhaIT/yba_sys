<?php
    $sqlSettingUomDeatil = mysql_query("SELECT uom_detail_option, calculate_cogs FROM setting_options");
    $rowSettingUomDetail = mysql_fetch_array($sqlSettingUomDeatil);
    $small_label = "";
    $sqlUomSm    = mysql_query("SELECT abbr FROM uoms WHERE id = (SELECT to_uom_id FROM uom_conversions WHERE from_uom_id = {$salesOrderDetail['Product']['price_uom_id']} AND is_small_uom = 1 LIMIT 1)");
    if(mysql_num_rows($sqlUomSm)){
        $rowUomSm = mysql_fetch_array($sqlUomSm);
        $small_label = $rowUomSm['abbr'];
    }
    $sqlUomMain = mysql_query("SELECT abbr FROM uoms WHERE id = {$salesOrderDetail['Product']['price_uom_id']};");
    $rowUomMain = mysql_fetch_array($sqlUomMain);
    $main_uom   = $rowUomMain['abbr'];
    if($small_label == ''){
        $small_label = $main_uom;
    }
    $totalDn    = (($salesOrderDetail['SalesOrderDetail']['qty'] + $salesOrderDetail['SalesOrderDetail']['qty_free']) * $salesOrderDetail['SalesOrderDetail']['conversion']);
    $tblName = "tbl" . rand(); 
?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var totalOrder = converDicemalJS(<?php echo $totalDn; ?>);
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": 10000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/pickProductAjax/<?php echo $salesOrderDetail['SalesOrderDetail']['product_id']; ?>/<?php echo $locationGroupId; ?>/<?php echo $small_label; ?>",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                <?php if($rowSettingUomDetail[0] == 0){ ?>
                $("#<?php echo $tblName; ?> tr").find("th:eq(1)").hide();
                $("#<?php echo $tblName; ?> tr").find("td:eq(1)").hide();
                <?php } ?>
                $(".float").autoNumeric({mDec: 0});
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $("input[name='chkPickProduct[]']").click(function(){
                    var thisCheck = $(this);
                    var totalStock = parseFloat($(this).closest("tr").find(".qtyInventory").val());
                    if (thisCheck.is (':checked')){
                        if(parseFloat(getTotalPaid()) < parseFloat(totalOrder)){
                            thisCheck.closest("tr").find(".qtyPick").val(totalStock);
                            thisCheck.closest("tr").find(".qtyPick").removeAttr('readonly');
                        }
                        else{
                            thisCheck.removeAttr('checked');
                        }
                    }else{
                        thisCheck.closest("tr").find(".qtyPick").attr('readonly','readonly');
                        thisCheck.closest("tr").find(".qtyPick").val(0);
                    }
                    checkValidPaid($(this).closest("tr").find(".qtyPick"));
                });
                $(".qtyPick").focus(function(){
                    var val  = $(this).val();
                    var attr = $(this).attr('readonly');
                    if(attr == false){
                        if(val == "0"){
                            $(this).val("");
                        }
                    }
                });
                $(".qtyPick").blur(function(){
                    checkValidPaid($(this));
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
    });
    
    function getTotalPaid(){
        var totalOrderPick = 0;
        $(".qtyPick").each(function(){
            totalOrderPick += parseFloat($(this).val()==''?0:$(this).val());
        });
        return totalOrderPick;
    }
    
    function checkValidPaid(current){
        var currentVal = parseFloat(current.val());
        var totalStock = parseFloat(current.closest("tr").find(".qtyInventory").attr("default-total-qty"));
        var val = 0;
        if(parseFloat(getTotalPaid()) > parseFloat(totalOrder)){
            val = converDicemalJS(currentVal - (converDicemalJS(parseFloat(getTotalPaid()) - parseFloat(totalOrder))));
            if(val > totalStock){
                current.val(totalStock);
            }else{
                current.val(val);
            }
        }else{
            if(currentVal > totalStock){
                current.val(totalStock);
            }
        }
        putTotalBalance();
    }
    
    function putTotalBalance(){
        var totalBalance = converDicemalJS(totalOrder - parseFloat(getTotalPaid()));
        $("#total_order").html(totalBalance);
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:left;">
        <b><?php echo TABLE_PRODUCT; ?>:</b> <?php echo $salesOrderDetail['Product']['code']." ".$salesOrderDetail['Product']['name']; ?> 
        <br/><b>Total Order: <span id="total_order"><?php echo $totalDn; ?></span> <?php echo $small_label; ?></b> 
    </div>
    <div style="clear: both;"></div>
</div>
<center style="color: #C00; font-size: 14px; display:none" id="warningPickProduct">Please check box and fill in quality for pick product!</center>
<br />
<div id="dynamic">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_LOTS_NO; ?></th>
                <th><?php echo TABLE_EXPIRED_DATE; ?></th>
                <th><?php echo TABLE_LOCATION; ?></th>
                <th><?php echo TABLE_TOTAL_QTY; ?></th>
                <th><?php echo TABLE_QTY_PICK; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>