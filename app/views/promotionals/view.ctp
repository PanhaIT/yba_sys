<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPromotional").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotional.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotional">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PROMOTINO_PACK_INFO); ?></legend>
    <table width="100%" cellpadding="5" cellspacing="0">
        <tr>
            <td style="width: 15%; font-size: 12px;"><?php echo GENERAL_DESCRIPTION; ?> :</td>
            <td style="width: 24%; font-size: 12px;"><?php echo $this->data['Promotional']['description']; ?></td>
            <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_DATE; ?> :</td> 
            <td style="width: 23%; font-size: 12px;"><?php echo dateShort($this->data['Promotional']['date']); ?></td>
            <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_BRANCH; ?> :</td> 
            <td style="width: 23%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_START_DATE; ?> :</td> 
            <td style="font-size: 12px;">
                <?php 
                $dateStart = '';
                if($this->data['Promotional']['start'] != '' && $this->data['Promotional']['start'] != '0000-00-00'){
                    $dateStart = dateShort($this->data['Promotional']['start']);
                }
                echo $dateStart; ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_END_DATE; ?> :</td> 
            <td style="font-size: 12px;">
                <?php 
                $dateEnd = '';
                if($this->data['Promotional']['end'] != '' && $this->data['Promotional']['end'] != '0000-00-00'){
                    $dateEnd = dateShort($this->data['Promotional']['end']);
                }
                echo $dateEnd; ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_CODE; ?> :</td> 
            <td style="font-size: 12px;"><?php echo $this->data['Promotional']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php echo TABLE_CUSTOMER_GROUP; ?> :</td>
            <td style="font-size: 12px;">
                <?php 
                if($this->data['Cgroup']['id'] != ''){
                    echo $this->data['Cgroup']['name']; 
                } else {
                    echo TABLE_ALL;
                }
                ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_CUSTOMER; ?> :</td> 
            <td style="font-size: 12px;">
                <?php 
                if($this->data['Customer']['id'] != ''){
                    echo $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name']; 
                } else {
                    echo TABLE_ALL;
                }
                ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"></td> 
            <td style="font-size: 12px;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
            <td colspan="5">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo nl2br($this->data['Promotional']['note']); ?>
                </div>
            </td>
        </tr>
    </table>
    <br/>
    <table class="table" cellspacing="0" style="padding:0px; width:99%;">
        <!-- <tr>
            <th class="first" style="width: 15%;"><?php echo TABLE_NO; ?></th>
            <th style="width:30%;"><?php echo TABLE_PRODUCT; ?></th>
            <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
            <th style="width:10%;"><?php echo 'Discount Amount'; ?></th>
            <th style="width:10%;"><?php echo 'Discount Percent'; ?> (%)</th>
            <th style="width:15%;"><?php echo 'Total Sales Qty'; ?></th>
            <th style="width:15%;"><?php echo 'Total Sales Amount'; ?> ($)</th>
        </tr> -->
        <tr>
            <th class="first" colspan="4" style="text-align:center;"><?php echo TABLE_PRODUCT; ?></th>
            <th colspan="6" style="text-align:center;"><?php echo MENU_PRODUCT_PROMOTION_INFO; ?></th>
        </tr>
        <tr>
            <th class="first" style="width:4%;"><?php echo TABLE_NO;?></th>
            <th style="width:15%;"><?php echo TABLE_PRODUCT;?></th>
            <th style="width:5%;"><?php echo TABLE_QTY;?></th>
            <th style="width:9%;"><?php echo TABLE_UOM;?></th>
            <th style="width:15%;"><?php echo TABLE_PRODUCT;?></th>
            <th style="width:5%;"><?php echo TABLE_F_O_C;?></th>
            <th style="width:9%;"><?php echo TABLE_UOM;?></th>
            <th style="width:8%;"><?php echo TABLE_DIS_AMOUNT;?></th>
            <th style="width:8%;"><?php echo TABLE_DIS_PERCENT;?></th>
            <th style="width:7%;"><?php echo TABLE_UNIT_PRICE_SHORT;?>($)</th>
        </tr>
        <?php
            $grandTotalQty = 0;
            $grandTotalAmount = 0;
            if(!empty($promotionDetails)){
                $index = 1;
                foreach($promotionDetails AS $promotionDetail){
                    $productRequestName = '';
                    $productPromoName = '';
                    $uomRequest = 0;
                    $uomPromo = 0;
                    if(!empty($promotionDetail['PromotionalDetail']['product_request_id'])){
                        $sqlProRequest = mysql_query("SELECT CONCAT_WS(' - ',code,name) FROM products WHERE id = ".$promotionDetail['PromotionalDetail']['product_request_id']);
                        $rowProRequest = mysql_fetch_array($sqlProRequest);
                        $productRequestName = $rowProRequest[0];
                    }
                    if(!empty($promotionDetail['PromotionalDetail']['product_promo_id'])){
                        $sqlProPromo = mysql_query("SELECT CONCAT_WS(' - ',code,name), price_uom_id FROM products WHERE id = ".$promotionDetail['PromotionalDetail']['product_promo_id']);
                        $rowProPromo = mysql_fetch_array($sqlProPromo);
                        $productPromoName = $rowProPromo[0];
                        $uomPromo = $rowProPromo[1];
                    }
                    $totalSales  = 0;
                    $totalAmount = 0;
                    $sqlSales = mysql_query("SELECT SUM(sales_order_details.qty) AS total_qty, SUM(sales_order_details.total_price) AS total_amount FROM sales_order_details INNER JOIN sales_orders ON sales_orders.id = sales_order_details.sales_order_id AND sales_orders.status > 0 WHERE sales_order_details.promotional_id = ".$this->data['Promotional']['id']." AND sales_order_details.product_id = ".$promotionDetail['PromotionalDetail']['product_request_id']);
                    $rowSales = mysql_fetch_array($sqlSales);
                    if($rowSales['total_qty'] > 0){
                        $totalSales = $rowSales['total_qty'];
                    }
                    if($rowSales['total_amount'] > 0){
                        $totalAmount = $rowSales['total_amount'];
                    }
        ?>
        <tr class="listBodyPromotional">
            <td class="first"><?php echo $index; ?></td>
            <td>
                <?php echo $productRequestName; ?>
            </td>
            <td><?php echo number_format($promotionDetail['PromotionalDetail']['qty_request'], 0); ?></td>
            <td>
                <?php 
                if(!empty($promotionDetail['PromotionalDetail']['uom_request'])){
                    $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$promotionDetail['PromotionalDetail']['uom_request']);
                    $rowUom = mysql_fetch_array($sqlUom);
                    echo $rowUom[0];
                }
                ?>
            </td>
            <td>
                <?php echo $productPromoName; ?>
            </td>
            <td style="padding:0px; text-align: center; width:5%"><?php echo number_format($promotionDetail['PromotionalDetail']['qty_promo'], 0); ?></td>
            <td style="padding:0px; text-align: center; width:9%">
                <?php
                if(!empty($promotionDetail['PromotionalDetail']['uom_promo'])){
                    $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$promotionDetail['PromotionalDetail']['uom_promo']);
                    $rowUom = mysql_fetch_array($sqlUom);
                    echo $rowUom[0];
                }
                ?>
            </td>
            <td>
                <?php echo number_format($promotionDetail['PromotionalDetail']['discount_amount'], 2); ?>
            </td>
            <td>
                <?php echo number_format($promotionDetail['PromotionalDetail']['discount_percent'], 2); ?>
            </td>
            <td><?php echo number_format($promotionDetail['PromotionalDetail']['unit_price'], 2); ?></td>
            <!-- <td>
                <?php echo number_format($totalSales, 0); ?>
            </td>
            <td>
                <?php echo number_format($totalAmount, 2); ?>
            </td> -->
        </tr>
        <?php
                $grandTotalQty    += $totalSales;
                $grandTotalAmount += $totalAmount;
                $index++;
            }
        ?>
        <!-- <tr>
            <td colspan="9" class="first">Total</td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($grandTotalQty, 0); ?></td>
            <td style="font-size: 14px; font-weight: bold;"><?php echo number_format($grandTotalAmount, 0); ?></td>
        </tr> -->
        <?php
        }
        ?>
    </table>
</fieldset>