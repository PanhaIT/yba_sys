<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackBranchCurrency").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePosPromotionalDiscount.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranchCurrency">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_POS_PROMOTIONAL_DISCOUNT_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(MENU_BRANCH); ?></th>
            <td><?php echo $this->data['Branch']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_PROMOTION_TYPE); ?></th>
            <td>
            <?php
            $label = '';
            $promotionType=$this->data['PosPromotionalDiscount']['promotion_type_id'];
            if($promotionType==1){
                $label = 'Buy and free with the same/difference product';
            }else if($promotionType==2){
                $label = 'Buy more than 100$ get anyone product free';
            }else if($promotionType==3){
                $label = 'Buy more than 100$, get 3% off';
            }else if($promotionType==4){
                $label = 'Buy items with different price, choose 3 items with 20$';
            }
            echo $label;
            ?>
            </td>
        </tr>
    </table>
</fieldset>