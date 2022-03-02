<script type="text/javascript">
    var processing = 0;
    var checkCard = 0;
    var cardNum   = '';
    var token     = '';
    var terminalCode = '';
    var url = window.location.protocol+"//"+window.location.host+"/MembershipAPIClient/";
    $(document).ready(function(){
        $("#msgAlert").text('');
        terminalCode = $("#terminalAPI").val();
        // Focus Card
        $("#CardNumber").select().focus();
        // Scan Card
        $("#CardNumber").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                var series = $("#CardNumber").val();
                if(series != "" && processing == 0){
                    processing = 1;
                    $("#msgAlert").text('');
                    $("#lblCheckCardDiscount").text('<?php echo 'Loading...'; ?>');
                    if(token == ''){
                        getTmnToken(series, $(this));
                    } else {
                        checkCardAccess(series, $(this));
                    }
                } else {
                    return false;
                }
            }
        });
        // Check Card
        $("#checkCardProcess").click(function(){
            var series = $("#CardNumber").val();
            if(series != "" && processing == 0){
                processing = 1;
                $("#msgAlert").text('');
                $("#lblCheckCardDiscount").text('<?php echo 'Loading...'; ?>');
                if(token == ''){
                    getTmnToken(series, $(this));
                } else {
                    checkCardAccess(series, $(this));
                }
            }
        });
    });
    
    function getTmnToken(series, obj){
        if($("#terminalAPI").val() != "" && $("#terminalAPI").val() != undefined){
            $.ajax({
                type: "POST",
                headers: {
                    "request":"token"
                },
                error: function (result) {
                    processing = 0;
                    $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
                    $("#msgAlert").text('Connection Lose');
                },
                url: url+terminalCode+"/",
                success: function(result){
                    var str  = decrypt(result);
                    var json = $.parseJSON(str);
                    if(json.status == '305' || json.status == '308' || json.status == '309' || json.status == '205'){
                        if(json.status == '305' || json.status == '308' || json.status == '309'){
                            $("#msgAlert").text(json.info);
                            processing = 0;
                            $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
                        } else {
                            token = json.token;
                            checkCardAccess(series, obj);
                        }
                    } else {
                        processing = 0;
                        $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
                        $("#msgAlert").text('Error Request Token');
                    }
                }
            });
        } else {
            processing = 0;
            $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
            $("#msgAlert").text('Invalid Teminal');
        }
    }
    
    function checkCardAccess(series, obj){
        if(token != '' && series != ''){
            var cardNumber = series.toString().replace(/%/g,"");
            cardNumber = cardNumber.toString().replace(/\?/g,"");
            var data = "account="+cardNumber+"&pay_type=2";
            var post = encrypt(data);
            $.ajax({
                type: "POST",
                data: "data="+post,
                headers: {
                    "request":"check",
                    "token":token
                },
                url: url+terminalCode+"/",
                success: function(result){
                    token = "";
                    var str  = decrypt(result);
                    var json = $.parseJSON(str);
                    processing = 0;
                    $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
                    if(json.status == '300' || json.status == '301' || json.status == '302' || json.status == '303' || json.status == '304' || json.status == '305' || json.status == '311' || json.status == '202' || json.status == '315'){
                        if(json.status == '202'){
                            var discount = json.discount;
                            var cardId   = json.card_id;
                            $("#divCardDiscount").show();
                            $("#CardDiscount").val(discount);
                            $("#CardId").val(cardId);
                            $("#checkCardProcess").hide();
                            $("#msgAlert").text('');
                            $("#CardNumber").attr("readonly", true);
                        } else {
                            $("#msgAlert").text(json.info);
                            $("#CardNumber").val('');
                        }
                    } else {
                        $("#msgAlert").text('Error Check');
                        $("#CardNumber").select().focus();
                    }
                }
            });
        } else {
            processing = 0;
            $("#lblCheckCardDiscount").text('<?php echo 'Check'; ?>');
            $("#msgAlert").text('Invalid Token & Auth Code');
            $("#CardNumber").select().focus();
        }
    }
    
    function encrypt(data){
        var result = '';
        var key = '<?php echo KEY_API; ?>';
        if(data != ''){
            result = Api.Ctr.encrypt(data, key, 256);
        }
        return result;
    }
    
    function decrypt(data){
        var result = '';
        var key = '<?php echo KEY_API; ?>';
        if(data != ''){
            result = Api.Ctr.decrypt(data, key, 256);
        }
        return result;
    }
</script>
<input type="hidden" id="terminalAPI" value="<?php echo TERMINAL_API; ?>" />
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td colspan="2" style="height: 30px; color: #058acf; font-size: 16px; text-align: center;" id="msgAlert"></td>
    </tr>
    <tr>
        <td style="width: 25%;">Card Number :</td>
        <td>
            <input type="text" id="CardNumber" style="width: 60%; float: left; text-align: left;" /> 
            <div class="buttons" style="float: right;">
                <a href="#" class="positive" id="checkCardProcess">
                    <img src="<?php echo $this->webroot; ?>img/button/active.png" alt="" />
                    <span id="lblCheckCardDiscount"><?php echo ACTION_CHECK; ?></span>
                </a>
            </div>
            <div style="clear: both;"></div>
        </td>
    </tr>
    <tr id="divCardDiscount" style="display: none;">
        <td style="width: 25%;">Discount :</td>
        <td>
            <input type="hidden" id="CardId" />
            <input type="text" id="CardDiscount" style="width: 60%; float: left; text-align: left;" readonly="" /> %
        </td>
    </tr>
</table>
<div style="clear:both;"></div>