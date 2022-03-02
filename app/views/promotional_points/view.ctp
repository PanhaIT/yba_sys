<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackPromotionalPoint").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePromotionalPoint.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPromotionalPoint">
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
            <td style="width: 24%; font-size: 12px;"><?php echo $this->data['PromotionalPoint']['description']; ?></td>
            <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_DATE; ?> :</td> 
            <td style="width: 23%; font-size: 12px;"><?php echo dateShort($this->data['PromotionalPoint']['date']); ?></td>
            <td style="width: 10%; font-size: 12px; text-transform: uppercase;"><?php echo TABLE_BRANCH; ?> :</td> 
            <td style="width: 23%; font-size: 12px;"><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_START_DATE; ?> :</td> 
            <td style="font-size: 12px;">
                <?php 
                $dateStart = '';
                if($this->data['PromotionalPoint']['start'] != '' && $this->data['PromotionalPoint']['start'] != '0000-00-00'){
                    $dateStart = dateShort($this->data['PromotionalPoint']['start']);
                }
                echo $dateStart; ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_END_DATE; ?> :</td> 
            <td style="font-size: 12px;">
                <?php 
                $dateEnd = '';
                if($this->data['PromotionalPoint']['end'] != '' && $this->data['PromotionalPoint']['end'] != '0000-00-00'){
                    $dateEnd = dateShort($this->data['PromotionalPoint']['end']);
                }
                echo $dateEnd; ?>
            </td>
            <td style="font-size: 12px; text-transform: uppercase;"><?php echo TABLE_CODE; ?> :</td> 
            <td style="font-size: 12px;"><?php echo $this->data['PromotionalPoint']['code']; ?></td>
        </tr>
        <tr>
            <td style="font-size: 12px;"><?php echo MENU_PGROUP_MANAGEMENT; ?> :</td>
            <td colspan="3"  style="font-size: 12px;">
                <?php 
                if(!empty($this->data['PromotionalPoint']['id'])){
                    $sqlPgroup = mysql_query("SELECT GROUP_CONCAT(pg.name,'') AS pgroup FROM promotional_point_pgroups ppg INNER JOIN pgroups pg ON pg.id=ppg.pgroup_id WHERE ppg.promotional_point_id='".$this->data['PromotionalPoint']['id']."' AND pg.is_active=1");
                    if(mysql_num_rows($sqlPgroup)){
                        $rowPgroup=mysql_fetch_array($sqlPgroup);
                        echo $rowPgroup['pgroup'];
                    }
                }
                ?>
            </td>
            <td colspan="2" style="font-size: 12px;">1$ = <?php echo $this->data['PromotionalPoint']['total_point']; ?> point(s)</td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><?php echo TABLE_NOTE; ?> :</td>
            <td colspan="5">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo nl2br($this->data['PromotionalPoint']['note']); ?>
                </div>
            </td>
        </tr>
    </table>
    <br/>
    <table class="table" cellspacing="0" style="padding:0px; width:99%;">
        <tr>
            <th class="first" style="width: 15%;"><?php echo TABLE_NO; ?></th>
            <th style="width:30%;"><?php echo TABLE_PRODUCT; ?></th>
            <th style="width:15%;"><?php echo TABLE_UOM; ?></th>
            <th style="width:10%;"><?php echo 'Discount Amount'; ?></th>
            <th style="width:10%;"><?php echo 'Discount Percent'; ?> (%)</th>
        </tr>
        <?php
            $grandTotalQty = 0;
            $grandTotalAmount = 0;
            if(!empty($promotionPointDetails)){
                $index = 1;
                foreach($promotionPointDetails AS $promotionPointDetail){
                    $productRequestName = '';
                    if(!empty($promotionPointDetail['PromotionalPointDetail']['product_request_id'])){
                        $sqlProRequest = mysql_query("SELECT CONCAT_WS(' - ',code,name) FROM products WHERE id = ".$promotionPointDetail['PromotionalPointDetail']['product_request_id']);
                        $rowProRequest = mysql_fetch_array($sqlProRequest);
                        $productRequestName = $rowProRequest[0];
                    }
                    $totalSales  = 0;
                    $totalAmount = 0;
        ?>
        <tr class="listBodyPromotionalPoint">
            <td class="first"><?php echo $index; ?></td>
            <td>
                <?php echo $productRequestName; ?>
            </td>
            <td>
                <?php 
                if(!empty($promotionPointDetail['PromotionalPointDetail']['uom_request'])){
                    $sqlUom = mysql_query("SELECT abbr FROM uoms WHERE id = ".$promotionPointDetail['PromotionalPointDetail']['uom_request']);
                    $rowUom = mysql_fetch_array($sqlUom);
                    echo $rowUom[0];
                }
                ?>
            </td>
            <td>
                <?php echo number_format($promotionPointDetail['PromotionalPointDetail']['discount_amount'], 2); ?>
            </td>
            <td>
                <?php echo number_format($promotionPointDetail['PromotionalPointDetail']['discount_percent'], 2); ?>
            </td>
        </tr>
        <?php
                $grandTotalQty    += $totalSales;
                $grandTotalAmount += $totalAmount;
                $index++;
            }
        ?>
        <tr>
            <td colspan="5" class="first">Total</td>
        </tr>
        <?php
        }
        ?>
    </table>
</fieldset>