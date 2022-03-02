<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackPriceType").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePriceType.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPriceType">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_PRICE_TYPE_INFO); ?></legend>
    <table width="100%" class="info">
        <tr>
            <th><?php __(TABLE_NAME); ?></th>
            <td><?php echo $price_type['PriceType']['name']; ?></td>
        </tr>
        <tr>
            <th><?php __(TABLE_SET_TO); ?></th>
            <td>
                <?php    
                    if($price_type['PriceType']['is_set'] != ""){
                        if($price_type['PriceType']['is_set'] == 1){
                            echo TABLE_AS_AMOUNT;
                        }else if($price_type['PriceType']['is_set'] == 2){
                            echo TABLE_AS_PERCENT;
                        }else{
                            echo TABLE_AS_ADD_ON;
                        }
                    }                    
                ?>
            </td>
        </tr>
    </table>
</fieldset>