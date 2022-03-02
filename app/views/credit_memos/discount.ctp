
<style type="text/css">
    li{
        list-style: none;
        margin-bottom: 10px;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        loadDiscount();
        $(".rowDiscount").click(function(){
            $(".rowDiscount").css("backgroundColor", "white").find("input[type=radio]").attr("checked", false);
            $(this).css("backgroundColor", "#F8F8F8").find("input[type=radio]").attr("checked", true);
        });
        
    });
    function loadDiscount(){
        $(".first-row").css("backgroundColor", "#F8F8F8").find("input[type=radio]").attr("checked", true);
    }
</script>
<div>
    <h1><?php echo MENU_DISCOUNT_INFORMATION ?> </h1>
</div>
<br/>
<div style="width: 100%; padding: 0px; margin: 0px; " >
    <table class="table" style="" cellspacing="0" >
        <thead>
            <tr>
                <th class="first" style="width:8%"></th>
                <th style="width:23%"><?php echo TABLE_NAME; ?></th>
                <th style="width:23%"><?php echo GENERAL_DESCRIPTION; ?></th>
                <th style="width:23%"><?php echo TABLE_PERCENT; ?> (%)</th>
                <th style="width:23%"><?php echo GENERAL_AMOUNT; ?> <? echo TABLE_CURRENCY_DEFAULT; ?></th>
            </tr>
        </thead>
        <tbody style="width: 774px; ">
            <?php
            $i = 0;
            foreach ($discounts as $discount) {
                $i++;
            ?>
                <tr class="<?php
                if ($i == 1) {
                    echo "first-row";
                }
            ?> rowDiscount" >
                <td class="first" style="width:8%; <?php
                if ($i == 1) {
                    echo 'border-top:1px solid #C1DAD7;';
                }
            ?>">
                    <input type="radio" name="chkDiscount" value="<?php echo $discount['Discount']['id']; ?>" />
                    <input type="hidden" name="salesOrderDiscountAmount" value="<?php echo $discount['Discount']['amount']!=""?$discount['Discount']['amount']:0; ?>" />
                    <input type="hidden" name="salesOrderDiscountPercent" value="<?php echo $discount['Discount']['percent']!=""?$discount['Discount']['percent']:0; ?>" />
                </td>
                <td style="width:23%; <?php
                    if ($i == 1) {
                        echo 'border-top:1px solid #C1DAD7;';
                    }
                    ?> "><?php echo $discount['Discount']['name']; ?></td>
                <td style="width:23%;<?php
                    if ($i == 1) {
                        echo 'border-top:1px solid #C1DAD7;';
                    }
                    ?>"><?php echo $discount['Discount']['description']; ?></td>
                <td style="width:23%; <?php
                    if ($i == 1) {
                        echo 'border-top:1px solid #C1DAD7;';
                    }
                    ?>"><?php echo $discount['Discount']['percent']; ?></td>
                <td style="width:23%; <?php
                    if ($i == 1) {
                        echo 'border-top:1px solid #C1DAD7;';
                    }
                    ?>"><?php echo $discount['Discount']['amount']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
