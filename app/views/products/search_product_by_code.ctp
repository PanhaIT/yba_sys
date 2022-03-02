<?php
//echo $javascript->object($result);
require_once "includes/pricing_rule.php";

if (!empty($product)) {
?>
    <input type="hidden" value="1" id="qtyOfProduct" />
    <input type="hidden" id="salesOrderProductName" value="<?php echo $product['Product']['code'] . " - " . $product['Product']['name']; ?>" />
    <input type="hidden" id="salesOrderProductId" value="<?php echo $product['Product']['id']; ?>" />
    <input type="hidden" id="salesOrderPricingRuleId" />
    <input type="hidden" id="salesOrderPricingRuleMinQty" />
    <input type="hidden" id="salesOrderPricingRuleMaxQty" />
    <input type="hidden" id="salesOrderProductPrice" value="<?php echo $product['Product']['price']; ?>" />
    <input type="hidden" id="salesOrderProductNewPrice" value="<?php echo $product['Product']['price']; ?>" />
    <input type="hidden" id="salesOrderProductPriceUomId" value="<?php echo $product['Product']['price_uom_id']; ?>" />
    <input type="hidden" id="salesOrderInventoryQty" value="<?php echo $product[0]['qty']; ?>" />
<?php
    if (!empty($pricingRules)) {

        echo '<input type="hidden" value="' . count($pricingRules) . '" id="qtyOfPricingRule" />';
?>
        <style type="text/css">
            li{
                list-style: none;
                margin-bottom: 10px;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function(){
                PricingRuleLoad();
                $('.rowPricingRuleList').click(function(){
                    $('.rowPricingRuleList').css("backgroundColor", "white");
                    $(this).css("backgroundColor", "#F8F8F8");
                    $(this).find("input[type=radio]").attr("checked", true);
                    $(".rowPricingRuleDescription").html($(this).find("div.descriptionOfPricingRule").html());
                    $("#salesOrderProductNewPrice").val($(this).find("input[name='salesOrderPricingRuleNewPrice']").val());
                    $("#salesOrderPricingRuleId").val($(this).find("input[name='salesOrderPricingRuleId']").val());
                    $("#salesOrderPricingRuleMinQty").val($(this).find("input[name='salesOrderPricingRuleMinQty']").val());
                    $("#salesOrderPricingRuleMaxQty").val($(this).find("input[name='salesOrderPricingRuleMaxQty']").val());
                });

                function PricingRuleLoad(){
                    $(".first-row").css("backgroundColor", "#F8F8F8").find("input[type=radio]").attr("checked", true);
                    $(".rowPricingRuleDescription").html($(".first-row").find("div.descriptionOfPricingRule").html());
                    $("#salesOrderProductNewPrice").val($(".first-row").find("input[name='salesOrderPricingRuleNewPrice']").val());
                    $("#salesOrderPricingRuleId").val($(".first-row").find("input[name='salesOrderPricingRuleId']").val());
                    $("#salesOrderPricingRuleMinQty").val($(".first-row").find("input[name='salesOrderPricingRuleMinQty']").val());
                    $("#salesOrderPricingRuleMaxQty").val($(".first-row").find("input[name='salesOrderPricingRuleMaxQty']").val());
                }
                $("#chkIsPricingRule").change(function(){
                    if($(this).attr("checked")){
                        $("#salesOrderProductNewPrice").val(<?php echo $product['Product']['price']; ?>);
                        $("#salesOrderPricingRuleId").val("");
                        $("#salesOrderPricingRuleMinQty").val("");
                        $("#salesOrderPricingRuleMaxQty").val("");
                        $("#pricingRuleWrap").hide();
                    }else{
                        var trCurrent = $(".chkPricingRuleSelect:checked").closest("tr");
                        $("#salesOrderProductNewPrice").val(trCurrent.find("input[name='salesOrderPricingRuleNewPrice']").val());
                        $("#salesOrderPricingRuleId").val(trCurrent.find("input[name='salesOrderPricingRuleId']").val());
                        $("#salesOrderPricingRuleMinQty").val(trCurrent.find("input[name='salesOrderPricingRuleMinQty']").val());
                        $("#salesOrderPricingRuleMaxQty").val(trCurrent.find("input[name='salesOrderPricingRuleMaxQty']").val());
                        $("#pricingRuleWrap").show();
                    }
                });
            })
        </script>
        <div>
            <div style="float:right;">( <?php echo PRICING_RULE_NORMALL_PRICE . ": " . $product['Product']['price']; ?> $ )<input type="checkbox" id="chkIsPricingRule" value="1" /></div>
            <h1>Pricing Rule List of Product <?php echo getProductNameByProductId($product['Product']['id']); ?></h1>
        </div>
        <div id="pricingRuleWrap">
            <table class="table" style="width:774px;" cellspacing="0" >
                <thead>
                    <tr>
                        <th class="first" style="width:50px"></th>
                        <th style="width:250px"><?php echo TABLE_NAME; ?></th>
                        <th style="width:250px"><?php echo GENERAL_DESCRIPTION; ?></th>
                        <th style="width:180px"><?php echo SALES_ORDER_NEW_PRICE; ?> ($)</th>
                    </tr>
                </thead>
            </table>
            <div style="height: 150px; overflow: auto; width: 774px;" >
                <table class="table" style="width: 774px;" cellspacing="0" >
                    <tbody>
                <?php
                $i = 0;
                foreach ($pricingRules as $pricingRule) {
                    $i++;
                ?>
                    <tr class="<?php
                    if ($i == 1) {
                        echo 'first-row';
                    }
                ?> rowPricingRuleList">
                    <td class="first" style="width:45px; <?php
                    if ($i == 1) {
                        echo 'border-top:1px solid #C1DAD7;';
                    }
                ?>
                        " >
                        <input type="radio" name="chkPricingRuleSelect" value="<?php echo $pricingRule['PricingRule']['id']; ?>" />
                    </td>

                    <td style="width:230px; <?php
                        if ($i == 1) {
                            echo 'border-top:1px solid #C1DAD7;';
                        }
                        ?> ">
                        <?php echo $pricingRule['PricingRule']['name'] ?></td>
                    <td style="width:230px;  <?php
                        if ($i == 1) {
                            echo 'border-top:1px solid #C1DAD7;';
                        }
                        ?>"><?php echo $pricingRule['PricingRule']['description'] ?></td>
                    <td style="text-align:right;width:160px;  <?php
                        if ($i == 1) {
                            echo 'border-top:1px solid #C1DAD7;';
                        }
                        ?>">
                        <input type="hidden" name="salesOrderPricingRuleId" value="<?php echo $pricingRule['PricingRule']['id']; ?>" />
                        <input type="hidden" name="salesOrderPricingRuleNewPrice" value="<?php echo round(getNewPrice($pricingRule, $product['Product']['id']), 2); ?>" />
                        <input type="hidden" name="salesOrderPricingRuleMinQty" value="<?php echo $pricingRule['PricingRule']['min_qty']; ?>" />
                        <input type="hidden" name="salesOrderPricingRuleMaxQty" value="<?php echo $pricingRule['PricingRule']['max_qty']; ?>" />
                        <?php
                        echo round(getNewPrice($pricingRule, $product['Product']['id']), 2) . " $";
                        ?>
                        <div class="descriptionOfPricingRule" style="display:none;">
                            <h3><?php echo "$i . {$pricingRule['PricingRule']['name']}"; ?></h3>
                            <?php if (!empty($pricingRule['PricingRule']['description'])) {
                            ?>
                                <li><?php echo $pricingRule['PricingRule']['description']; ?> </li>
                            <?php } ?>
                            <li>
                                <?php
                                if (empty($pricingRule['PricingRule']['apply_to_customer_id']) && empty($pricingRule['PricingRule']['apply_to_cgroup_id'])) {
                                    echo "Apply to Everyone";
                                } else if (!empty($pricingRule['PricingRule']['apply_to_customer_id'])) {
                                    echo "Apply to Customer: " . getCustomerNameByCustomerId($pricingRule['PricingRule']['apply_to_customer_id']);
                                } else if (!empty($pricingRule['PricingRule']['apply_to_cgroup_id'])) {
                                    echo "Apply to Customer Group: " . getCustomerGroupNameByCustomerGroupId($pricingRule['PricingRule']['apply_to_cgroup_id']);
                                }
                                ?>
                            </li>
                            <li>
                                <?php
                                if (empty($pricingRule['PricingRule']['apply_to_product_id']) && empty($pricingRule['PricingRule']['apply_to_pgroup_id']) && empty($pricingRule['PricingRule']['apply_to_product_parent_id'])) {
                                    echo "Apply to all products";
                                } else if (!empty($pricingRule['PricingRule']['apply_to_product_id'])) {
                                    echo "Apply to product: " . getProductNameByProductId($pricingRule['PricingRule']['apply_to_product_id']);
                                } else if (!empty($pricingRule['PricingRule']['apply_to_pgroup_id'])) {
                                    echo "Apply to Product Group: " . getProductGroupNameByProductGroupId($pricingRule['PricingRule']['apply_to_pgroup_id']);
                                } else if (!empty($pricingRule['PricingRule']['apply_to_product_parent_id'])) {
                                    echo "Apply to Product Tree: " . getProductNameByProductId($pricingRule['PricingRule']['apply_to_product_parent_id']);
                                }
                                ?>
                            </li>
                            <li>

                                <?php
                                if (!empty($pricingRule['PricingRule']['markdown'])) {
                                    echo "<div>";
                                    echo "Markdown: " . $pricingRule['PricingRule']['markdown'] . "% ";
                                    echo "</div>";
                                    echo "<div>";
                                    echo " Relative to ";
                                    echo getRelativeToNameById($pricingRule['PricingRule']['relative_to_id']);
                                    echo "</div>";
                                } else if (!empty($pricingRule['PricingRule']['markup'])) {
                                    echo "<div>";
                                    echo "Markup: " . $pricingRule['PricingRule']['markup'] . "% ";
                                    echo "</div>";
                                    echo "<div>";
                                    echo " Relative to ";
                                    echo getRelativeToNameById($pricingRule['PricingRule']['relative_to_id']);
                                    echo "</div>";
                                } else if (!empty($pricingRule['PricingRule']['margin'])) {
                                    echo "<div>";
                                    echo "Margin: " . $pricingRule['PricingRule']['margin'] . "% ";
                                    echo "</div>";
                                    echo "<div>";
                                    echo " Relative to ";
                                    echo getRelativeToNameById($pricingRule['PricingRule']['relative_to_id']);
                                    echo "</div>";
                                } else if (!empty($pricingRule['PricingRule']['percent'])) {
                                    echo "<div>";
                                    echo "Percent: " . $pricingRule['PricingRule']['percent'] . "% ";
                                    echo "</div>";
                                    echo "<div>";
                                    echo " Relative to ";
                                    echo getRelativeToNameById($pricingRule['PricingRule']['relative_to_id']);
                                    echo "</div>";
                                } else if (!empty($pricingRule['PricingRule']['amount'])) {
                                    echo "<div>";
                                    echo "Amount: " . $pricingRule['PricingRule']['amount'];
                                    echo "</div>";
                                    echo "<div>";
                                    echo " Relative to ";
                                    echo getRelativeToNameById($pricingRule['PricingRule']['relative_to_id']);
                                    echo "</div>";
                                } else if (!empty($pricingRule['PricingRule']['fixed_price'])) {
                                    echo "Fixed Price: " . $pricingRule['PricingRule']['fixed_price'] . "$ ";
                                }
                                ?>
                            </li>
                            <?php if ($pricingRule['PricingRule']['apply_to_date'] == 1) {
                            ?>
                                    <li>
                                        Between: <?php echo date("d/m/Y", strtotime($pricingRule['PricingRule']['start_date'])); ?> To <?php echo date("d/m/Y", strtotime($pricingRule['PricingRule']['end_date'])); ?>
                                    </li>
                            <?php } ?>
                            <?php if ($pricingRule['PricingRule']['apply_to_qty'] == 1) {
                            ?>
                                    <li>
                                <?php echo $pricingRule['PricingRule']['min_qty']; ?> < Quantity < <?php echo $pricingRule['PricingRule']['max_qty']; ?>
                                </li>
                            <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php
                            }
                ?>
                        </tbody>
                    </table>
                </div>
                <table>
                    <tfoot>
                        <tr>
                            <td class="rowPricingRuleDescription" colspan="5"></td>
                            <td>
                        </tr>
                    </tfoot>
                </table>
            </div>
<?php
                        } else {
                            echo '<input type="hidden" value="0" id="qtyOfPricingRule" />';
                        }
                    } else {
                        echo '<div>No Product in stock.</div>';
                        echo '<input type="hidden" value="0" id="qtyOfProduct" />';
                    }
?>
