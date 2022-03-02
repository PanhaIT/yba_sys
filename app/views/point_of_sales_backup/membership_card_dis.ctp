<style>
    .pointInfo{
        font-weight:bold;
    }
</style>

<script type="text/javascript">
    
    $(document).ready(function(){
        $("#msgAlert").text('');
        // Focus Card
        $("#cardNumber").select().focus();
        $("#rewardAmount").autoNumeric({mDec: 3});
        // Scan Card
        $("#cardNumber").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                var series = $("#cardNumber").val();                
                if(series != ""){
                    $("#msgAlert").text('');
                    $("#lblCheckCardDiscount").text('<?php echo ACTION_LOADING; ?>');
                    // check discount in local
                    checkCardAccess(series, $(this));
                } else {
                    return false;
                }
            }
        });
        $("#rewardAmount").blur(function(){
            var rewardAmount = replaceNum($("#rewardAmount").val());
            var PointOfSaleTotalAmountUs = replaceNum($("#PointOfSaleSubTotalAmountUsDisplay").val());
            if(rewardAmount>PointOfSaleTotalAmountUs){
                rewardAmount = PointOfSaleTotalAmountUs;
                $("#rewardAmount").val(rewardAmount).select().focus();
            }
        });
        $("#rewardAmount").click(function(){
            $("#rewardAmount").select().focus();
        });
        // Check Card
        $("#checkCardProcess").unbind("click").click(function(){
            var series = $("#cardNumber").val();
            var promoIdTypeId = $("#applyPromotionalPoint").val();
            if(series != ""){
                $("#msgAlert").text('');
                $("#lblCheckCardDiscount").text('<?php echo ACTION_LOADING; ?>');
                /// check discount in local
                checkCardAccess(series, $(this),promoIdTypeId);
            }
        });
        // Change Promotion
        $("#applyPromotionalPoint").change(function(){
            if($(this).val()==1){
                $("#rowRewardAmount").hide();
            }else{
                $("#rowRewardAmount").show();
            }
        });
    });
    
    function checkCardAccess(series, obj, promoIdTypeId){
        // check discount on local membership
        if(series != ''){
            $.ajax({
                dataType: 'json',
                type: "POST",
                data: "account="+series,
                url: "<?php echo $this->base; ?>/point_of_sales/checkMembershipCard/<?php echo $customerId;?>",
                success: function(result){
                    $("#lblCheckCardDiscount").text('<?php echo TABLE_VALIDITY; ?>');
                    if(result.status == '1'){
                        var branchId       = $("#PointOfSaleBranchId").find("option:selected").val();
                        var discount       = result.discount;
                        var cardId         = result.card_id;
                        var cardNum        = result.number;
                        var cusId          = result.customer_id;
                        var cusName        = result.name;
                        var cusTel         = result.telephone;
                        var cusOtherTel    = result.other_telephone;
                        var startDate      = result.card_date_start;
                        var endDate        = result.card_date_end;
                        var pointInDollar  = result.point_in_pollar;
                        var exchangePoint  = result.exchange_point;
                        var totalPoint     = result.total_point;

                        var rewardAmount = $("#rewardAmount").val();

                        // Card & Customer Information
                        $("#msgAlert").text('');
                        $("#cardNumber").attr("readonly", true);
                        $("#cardId").val(cardId);        
                        $(".cardCusId").val(cardNum);
                        $(".customerName").val(cusName);
                        $("#customerPOSId").val(cusId);
                        $("#PointOfSaleCustomerNameLabel").html(cusName);
                        $("#MembershipCardId").val(cardId);
                        $("#MembershipCardCode").val(cardNum);
                        $("#customerInfo").html(cusName);

                        // Button
                        $("#btnVoidDisByCard,.btnRemoveDiscountPos").show();
                        $("#btnMembershipCard,#checkCardProcess,#divPromotional,#btnMembershipCardCode,#rowRewardAmount").hide();
                        $("#PointOfSaleDiscountUs, #PointOfSaleDiscountPer").attr("readonly", true);
                        if(replaceNum($("#PointOfSaleTotalAmountUs").val())>0){
                            if(promoIdTypeId==1){
                                // Promotional Save Point
                                $("#PromoPoinStart").val(startDate);
                                $("#PromoPoinEnd").val(endDate);
                                $("#PointOfSaleDiscountPer").val(discount);
                                $("#CardDiscount").val(discount);
                                $("#applyPointInformation").hide();
                                $("#divCardDiscount").show();
                            }else{
                                // Promotional Apply Point
                                $("#MembershipTotalPoint").val(replaceNum(totalPoint).toFixed(2));
                                $("#MembershipPointInDollar").val(replaceNum(pointInDollar).toFixed(2));
                                $("#MembershipExchangePoint").val(replaceNum(exchangePoint).toFixed(2));
                               
                                
                                $("#totalPointMemShip").text(replaceNum(totalPoint).toFixed(2));
                                $("#pointInDollarMemShip").text(replaceNum(pointInDollar).toFixed(2));
                                $("#exchangePointMemShip").text(replaceNum(exchangePoint).toFixed(2));

                                $("#applyPointInformation").show();
                                $("#divCardDiscount").hide();

                                if(cardId!=""){
                                    var totalPointAsDollar  = replaceNum(totalPoint)*replaceNum(pointInDollar)/replaceNum(exchangePoint);
                                    if(rewardAmount>totalPointAsDollar){
                                        rewardAmount=totalPointAsDollar;
                                    }
                                    var rewardAmountAsPoint = replaceNum(rewardAmount)*replaceNum(exchangePoint)/replaceNum(pointInDollar);
                                    remainPoint = totalPoint-rewardAmountAsPoint;
                                    remainPointAsDollar = replaceNum(remainPoint)*replaceNum(pointInDollar)/replaceNum(exchangePoint);
                                    $("#remainPointText").text(replaceNum(remainPoint).toFixed(2));
                                    $("#rewardAmountApply").val(replaceNum(rewardAmount).toFixed(2));
                                    $("#rewardAmountInfo").text(replaceNum(rewardAmount).toFixed(2));
                                    $("#rewardAmountAsPointInfo").text(replaceNum(rewardAmountAsPoint).toFixed(2));
                                    $("#totalPointConvertToDollar").text(replaceNum(totalPointAsDollar).toFixed(2));
                                    $("#remainPointAsDollar").text(replaceNum(remainPointAsDollar).toFixed(2));
                                }
                                // if(cardId!=""){
                                //     var PointOfSaleTotalAmountUs = replaceNum($("#PointOfSaleSubTotalAmountUsDisplay").val());
                                //     var totalPointAsDollar  = replaceNum(totalPoint)*replaceNum(pointInDollar)/replaceNum(exchangePoint);
                                //     var remainAmountMemShip = 0;
                                //     if(totalPointAsDollar>=PointOfSaleTotalAmountUs){
                                //         remainAmountMemShip = replaceNum(totalPointAsDollar-PointOfSaleTotalAmountUs);
                                //         totalPointAsDollar  = PointOfSaleTotalAmountUs;
                                //     }
                                //     remainPoint = replaceNum(remainAmountMemShip)*replaceNum(exchangePoint)/replaceNum(pointInDollar);
                                //     $("#PointOfSaleExchangePointInDollar").text(replaceNum(totalPointAsDollar).toFixed(2));
                                //     $("#remainPointText").text(replaceNum(remainPoint).toFixed(2));
                                //     $("#totalPointConvertToDollar").text(replaceNum(totalPointAsDollar).toFixed(2));
                                // }
                            }
                        }
                    }else{
                        $("#msgAlert").text(result.info);
                        $("#cardNumber").select().focus();
                        $("#cardId").val("");
                        $(".cardCusId").val("");
                        $(".customerName").val(""); 
                        $(".customerTel").val(""); 
                        $(".customerOtherTel").val(""); 
                        $(".btnRemoveDiscountPos").hide(); 
                        $("#PromoPoinStart").val('');
                        $("#PromoPoinEnd").val('');
                        $("#customerPOSId").val(1);
                        $("#PointOfSaleCustomerNameLabel").html('General Customer');
                        // Button
                        $("#btnMembershipCard").show();
                        $("#btnVoidDisByCard").hide();
                    }
                    getTotalAmount();
                }
            });
        }else {
            $("#lblCheckCardDiscount").text('<?php echo TABLE_VALIDITY; ?>');
            $("#msgAlert").text('Invalid Token & Auth Code');
            $("#cardNumber").select().focus();
        }
    }    
</script>
<table cellpadding="3" cellspacing="0" style="width: 100%;">
    <tr>
        <td colspan="2" style="height: 30px; color: #058acf; font-size: 20px; text-align: center;" id="msgAlert"></td>
    </tr>
    <tr>
        <td style="width: 30%;"><?php echo TABLE_CODE;?> :</td>
        <td style="vertical-align: middle;">
            <input type="text" id="cardNumber" style="width: 85%; float: left; vertical-align: middle;" /> 
            <input type="hidden" id="cardId" style="width: 85%; float: left; vertical-align: middle;" /> 
        </td>
    </tr>
    <tr id="rowRewardAmount" style="display:none;">
        <td style="width: 30%;">Reward Amount :</td>
        <td style="vertical-align: middle;">
            <input type="text" id="rewardAmount" value="0" style="width: 85%; float: left; vertical-align: middle;" /> 
        </td>
    </tr>
    <tr id="divPromotional">
        <td style="width: 30%;">
            <label for="applyPromotionalPoint">Promotional : </label>
        </td>
        <td>
            <div class="container" style="float: left; vertical-align: middle; line-height: 24px;">
                <select name="" id="applyPromotionalPoint">
                    <option value="1">Save Point</option>
                    <option value="2">Apply Point</option>
                </select>
            </div>
            <div class="buttons" style="float: left; vertical-align: middle; margin-left:20px; line-height: 24px;">                
                <a href="#" class="positive" id="checkCardProcess" style="float: left; margin-left: 5px; line-height: 24px; vertical-align: middle;">       
                    <img alt="key" src="<?php echo $this->webroot . 'img/button/key.png'; ?>" />
                    <span style="font-size: 11px;" id="lblCheckCardDiscount"><?php echo TABLE_VALIDITY; ?></span>
                </a>
            </div>
            <div style="clear: both;"></div>
        </td>
    </tr>
    <tr id="divCardDiscount" style="display: none;">
        <td style="width: 30%;"><?php echo GENERAL_DISCOUNT_PERCENT;?> :</td>
        <td style="vertical-align: middle;">
            <input type="text" id="CardDiscount" style="width: 62%; float: left; vertical-align: middle;" readonly="" />
        </td>
    </tr>
    <tr id="applyPointInformation" style="display:none;">
        <td colspan="2">
            <fieldset>
                <legend style="font-weight:bold;">Point Information</legend>
                <table>
                    <tr>
                        <td style="">Total Point</td>
                        <td>:</td>
                        <td class="pointInfo"><span id="totalPointMemShip" style="font-weight:100;">0.00</span> [ <span id="totalPointConvertToDollar" class="pointInfo">0.00</span>$ ]</td>
                    </tr>
                    <tr>
                        <td style="">Reward Point</td>
                        <td>:</td>
                        <td class="pointInfo"><span id="rewardAmountAsPointInfo" style="font-weight:100;">0.00</span> [ <span id="rewardAmountInfo" class="pointInfo"></span>$ ]</td>
                    </tr>
                    <tr>
                        <td style="">Rmain Point</td>
                        <td>:</td>
                        <td class="pointInfo"><span id="remainPointText" style="font-weight:100;">0.00</span> [ <span id="remainPointAsDollar" class="pointInfo">0.00</span>$ ]</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="height: 30px; color: #058acf; font-size: 20px; text-align: center;">
            <table class="table">
                <tr>
                    <th class="first">Card Number</th>
                    <th>Total Point</th>
                    <th>Point Setting</th>
                    <th>Point As Dollar</th>
                </tr>
                <?php
                $dateNow  = date("Y-m-d");
                $total_point     = 0;
                $exchange_point  = 0;
                $point_in_dollar = 0;
                $convertPointToDollar = 0;
                $sqlMemberShip   = mysql_query("SELECT card_id,total_point,exchange_point,point_in_dollar FROM membership_cards WHERE is_active=1 AND customer_id='".$customerId."' AND DATE(card_date_start)<='".$dateNow."' AND DATE(card_date_end)>='".$dateNow."' ");
                while($rowMembership = mysql_fetch_array($sqlMemberShip)){
                    $total_point     = $rowMembership['total_point'];
                    $exchange_point  = $rowMembership['exchange_point'];
                    $point_in_dollar = $rowMembership['point_in_dollar'];
                    $convertPointToDollar = number_format($total_point*$point_in_dollar/$exchange_point,2);
                ?>
                <tr>
                    <td style="text-align:left;" class="first"><?php echo $rowMembership['card_id'];?></td>
                    <td style="text-align:left;"><?php echo $total_point;?></td>
                    <td style="text-align:left;"><?php echo $exchange_point.' point(s) = '.$point_in_dollar.'$';?></td>
                    <td style="text-align:right;"><span style="width:12px; float:left;">$</span><?php echo $convertPointToDollar;?></td>
                </tr>
                <?php } ?>
            </table>
        </td>
    </tr>
</table>

<div class="clear" style="padding-top: 15px;"></div>
<fieldset id="cusInfoCard" style="display: none;">
    <legend><?php __(MENU_CUSTOMER_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="3" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 35%;"><label><?php echo TABLE_CARD_ID;?></label>: </td>
            <td><input style="border: none;" readonly="readonly" class="cardCusId" value="" /></td>
        </tr>
        <tr>
            <td><label><?php echo TABLE_CUSTOMER_NAME;?></label>: </td>
            <td><input style="border: none;" readonly="readonly" class="customerName" value="" /></td>
        </tr>
        <tr>
            <td><label><?php echo TABLE_TELEPHONE;?></label>: </td>
            <td><input style="border: none;" readonly="readonly" class="customerTel" value="" /></td>
        </tr>
        <tr>
            <td><label><?php echo TABLE_TELEPHONE_OTHER;?></label>: </td>
            <td><input style="border: none;" readonly="readonly" class="customerOtherTel" value="" /></td>
        </tr>
    </table>
</fieldset>
<div style="clear:both;"></div>