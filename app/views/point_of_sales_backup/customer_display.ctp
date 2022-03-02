<style type="text/css">
    #posCusDisplay{
        -webkit-border-radius: 6px;
        -moz-border-radius: 6px;
        border-radius: 6px;
        -webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
        -moz-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
        background-color: #f3f9e8;
        width: 1024px;
        margin: 5px auto;
    }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        window.setInterval(displayHandler, 500);
        function displayHandler() {
            $("#branchName").html('');
            $("#listItemBuy").html('<tr><td colspan="4" style="text-align: center;">No Item</td></tr>');
            $("#subTotal").html('0');
            $("#discount").html('0');
            $("#bankCharge").html('0');
            $("#total").html('0');
            $("#totalOther").html('0');
            $(".mainCurrency").html('');
            $(".otherCurrency").html('');
            $("#exchangeRate").html('');
            if(localStorage.getItem("tabPOSSelected") != null && localStorage.getItem("tabPOSSelected") != ''){
                posSelect = localStorage.getItem("tabPOSSelected");
                if(localStorage.getItem("tabPOSDisplay"+posSelect) != null && localStorage.getItem("tabPOSDisplay"+posSelect) != '[]' && localStorage.getItem("tabPOSDisplay"+posSelect) != ''){
                    var display = localStorage.getItem("tabPOSDisplay"+posSelect);
                    if(display != ""){
                        var data = JSON.parse(display);
                        var itemList = '';
                        var branchName = '';
                        var subTotal = 0;
                        var discount = 0;
                        var bankCharge = 0;
                        var total    = 0;
                        var totalOther = 0;
                        var mainCurrency  = '';
                        var otherCurrency = '';
                        var exchangeRate  = '';
                        var thxDialog     = '';
                        $.each(data, function (index, value) {
                            branchName = value.branch;
                            subTotal   = numberWithCommas(value.subtotal);
                            discount   = numberWithCommas(value.discount);
                            bankCharge = numberWithCommas(value.bankCharge);
                            total      = numberWithCommas(value.total);
                            totalOther = numberWithCommas(value.totalother);
                            mainCurrency  = value.mainsym;
                            otherCurrency = value.othersym;
                            exchangeRate  = value.exrate;
                            thxDialog     = value.thxNum;
                            $.each(value.item, function (i, val) {
                                itemList += '<tr>';
                                itemList += '<td style="width: 40%;"><div class="inputContainer" style="width: 100%;">'+displayText(val.name, 16)+'</div></td>';
                                itemList += '<td style="width: 20%; text-align: center;">'+val.qty+'</td>';
                                itemList += '<td style="width: 20%; text-align: right;">'+val.price+'</td>';
                                itemList += '<td style="width: 20%; text-align: right;">'+val.total+'</td>';
                                itemList += '</tr>';
                                if(replaceNum(val.disc) > 0){
                                    itemList += '<tr>';
                                    itemList += '<td style="width: 40%;">Discount</td>';
                                    itemList += '<td style="width: 20%; text-align: center;"></td>';
                                    itemList += '<td style="width: 20%; text-align: right;"> -'+val.disc+'</td>';
                                    itemList += '<td style="width: 20%; text-align: right;"></td>';
                                    itemList += '</tr>';
                                }
                            });
                        });
                        if(itemList == ''){
                            $("#listItemBuy").html('<tr><td colspan="4" style="text-align: center;">No Item</td></tr>');
                        } else {
                            $("#listItemBuy").html(itemList);
                        }
                        $("#branchName").html(branchName);
                        $("#subTotal").html(subTotal);
                        $("#discount").html(discount);
                        $("#bankCharge").html(bankCharge);
                        $("#total").html(total);
                        $("#totalOther").html(totalOther);
                        $(".mainCurrency").html(mainCurrency);
                        $(".otherCurrency").html(otherCurrency);
                        $("#exchangeRate").html(exchangeRate);
                        if(thxDialog != "" && thxDialog == "1"){
                            var question = "អរគុណ ! Thanks You.";
                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 18px 0;"></span>'+question+'</p>');
                            $("#dialog").dialog({
                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                resizable: false,
                                modal: true,
                                width: 'auto',
                                height: 'auto',
                                position:'center',
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show();
                                    $(".ui-dialog-titlebar-close").hide();
                                },
                                buttons: {
                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        } else {
                            $("#dialog").dialog("close");
                        }
                    }
                }
            }
        }
        // Slideshow
        $("#slidershow").responsiveSlides({
            maxwidth: 800,
            speed: 800
        });
    });
    
    function displayText(string, limit){
        var result = string;
        if (string.length > limit) {
            result = string.substr(0, limit-1)+'...';
        }
        return result;
    }
    
    function replaceNum(str){
        if(str != "" && str != undefined && str != null){
            var str = parseFloat(str.toString().replace(/,/g,""));
        }else{
            var str = 0;
        }
        return str;
    }
    
    function numberWithCommas(number) {
        var parts = number.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }
</script>
<div style="width: 1024px; height: 680px; position: absolute; top: 50%; left: 50%; margin-left: -512px; margin-top: -340px;" id="posCusDisplay">
    <div id="screenAdv" style="width: 1024px; display: none;">
        <img alt="" src="<?php echo $this->webroot . 'img/default-adv.png'; ?>" style="position: absolute; top: 50%; left: 50%; margin-left: -512px; margin-top: -200px;" />
    </div>
    <div id="posList" style="margin: 0px; padding: 0px;">
        <div style="float: left; border: none; margin: 2px; width: 657px; height: 100%">
            <div style="width: 100%; height: 50px; padding-top: 10px;">
                <img alt="" src="<?php echo $this->webroot . 'img/super-retail-logo.png'; ?>" style="height: 50px;" />
                <div style=" float: right; width: 400px; text-align: center; font-size: 32px;" id="branchName"></div>
                <div class="clear:both;"></div>
            </div>
            <div id="divSlide" style="width: 100%;">
                <!-- Slideshow 1 -->
                <?php
                $sqlSlide = mysql_query("SELECT * FROM slide_displays WHERE is_active = 1");
                if(mysql_num_rows($sqlSlide)){
                ?>
                <ul class="rslides" id="slidershow">
                    <?php
                    while($row = mysql_fetch_array($sqlSlide)){
                    ?>
                    <li><img src="<?php echo $this->webroot . 'public/slide_show/'.$row['photo']; ?>" alt="<?php echo $row['name']; ?>" style=" height: 500px;"></li>
                    <?php
                    }
                    ?>
                </ul>
                <?php
                }
                ?>
            </div>
            <div style="width: 100%; height: 50px; padding-left: 10px;">
                <img alt="" src="<?php echo $this->webroot . 'img/default-adv.png'; ?>" style="height: 50px;" />
                <span style=" display: block; font-size: 10px;">Mobile: 093 881 887 / 077 787 702</span>
                <span style=" display: block; font-size: 10px;">Tel/Fax: 023 881 887 / 081 881 887</span>
            </div>
        </div>
        <div style="width: 350px; height: 100%; margin: 0px auto; float: right;">
            <div id="receiptList" style="margin-top: 10px; width: 340px; background: #FFF;">
                <table cellpadding="5" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="font-size: 16px; font-weight: bold; text-align: center;">RECEIPT</td>
                    </tr>
                    <tr>
                        <td>
                            <table cellpadding="2" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <th style="width: 40%;"><?php echo GENERAL_DESCRIPTION; ?></th>
                                    <th style="width: 20%; text-align: center;"><?php echo TABLE_QTY; ?></th>
                                    <th style="width: 20%; text-align: center;"><?php echo TABLE_PRICE; ?></th>
                                    <th style="width: 20%; text-align: center;"><?php echo TABLE_TOTAL; ?></th>
                                </tr>
                                <tr>
                                    <td colspan="4" style="border-top: 1px solid #000;"></td>
                                </tr>
                            </table>
                            <div style="padding: 0px; margin: 0px; width: 100%; height: 430px;">
                                <table cellpadding="2" cellspacing="0" style="width: 100%;" id="listItemBuy">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            <div style="padding: 0px; margin: 0px; width: 100%; border-top: 1px solid #000;">
                                <table cellpadding="2" cellspacing="0" style="width: 100%;">
                                    <tr>
                                        <td style="width: 110px; text-align: right;">SUB TOTAL :</td>
                                        <td style="text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;" class="mainCurrency"></div><span id="subTotal">0</span></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 110px; text-align: right;">DISCOUNT :</td>
                                        <td style="text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;" class="mainCurrency"></div><span id="discount">0</span></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 110px; text-align: right;">BANK CHARGE :</td>
                                        <td style="text-align: right;"><div style="width:10px; float: left; font-size:14px; margin-left: 5px;" class="mainCurrency"></div><span id="bankCharge">0</span></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 110px; text-align: right; font-size: 25px; font-weight: bold;">TOTAL :</td>
                                        <td style="text-align: right; font-size: 25px; font-weight: bold;"><div style="width:10px; float: left; font-size:25px; margin-left: 5px;" class="mainCurrency"></div><span style="font-size: 25px; font-weight: bold;" id="total">0</span></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 110px; text-align: right; font-size: 12px; font-weight: bold;">OR</td>
                                        <td style="text-align: right; font-size: 25px; font-weight: bold;"><div style="width:10px; float: left; font-size:25px; margin-left: 5px;" class="otherCurrency"></div><span style="font-size: 25px; font-weight: bold;" id="totalOther">0</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Exchange: 1<span class="mainCurrency"></span> = <span id="exchangeRate"></span><span class="otherCurrency"></span></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

