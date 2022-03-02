<?php
include("includes/function.php");
?>
<style type="text/css" media="screen">
    @font-face {
        font-family: 'MC-sans';
        src: url('../fonts/Microsoft-Sans-Serif.ttf');
    }
    @font-face {
        font-family: 'arialbd';
        src: url('../fonts/arialbd.ttf');
    }
    body {
        background: #888;
    }
</style>
<style type="text/css" media="print">
    @font-face {
        font-family: 'MC-sans';
        src: url('../fonts/Microsoft-Sans-Serif.ttf');
    }
    div.print_doc { width:100%; height: auto; padding: 0px; background: none;}
    #divPrintSetup { display: none;}
</style>
<div style="width: 100%; margin-top: 10px; margin-bottom: 10px; text-align: center; background: #fff; padding: 5px;" id="divPrintSetup">
    <?php
    if(!empty($product) && $printAll == 0){
    ?>
    <label for="Total">UoM :</label>
    <select id="uomSelect" style="width: 100px; height: 30px;">
        <?php
        $sqlUom = mysql_query("SELECT id,name,abbr, 1 AS conversion FROM uoms WHERE id=".$product['Product']['price_uom_id']."
                               UNION
                               SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id'].")
                               ORDER BY conversion ASC");
        while($rowUom = mysql_fetch_array($sqlUom)){
            $selected = '';
            $price    = 0;
            $mainPrice = 0;
            $decimalPrice = '00';
            if($rowUom['id'] == $product['Product']['price_uom_id']){
                $selected = ' selected="selected"';
                $code = $product['Product']['barcode'];
            } else {
                $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$product['Product']['id']." AND uom_id = ".$rowUom['id']." LIMIT 1;");
                if(mysql_num_rows($sqlSku)){
                    $rowSku = mysql_fetch_array($sqlSku);
                    $code   = $rowSku[0];
                } else {
                    $code   = $product['Product']['barcode'];
                }
            }
            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$product['Product']['id']." AND product_prices.uom_id =".$rowUom['id']);
            if(mysql_num_rows($sqlPrice)){
                while($rowPrice = mysql_fetch_array($sqlPrice)){
                    $unitCost = $rowPrice['unit_cost'] /  $data['conversion'];
                    if($rowPrice['set_type'] == 1){
                        $price = $rowPrice['amount'];
                    }else if($rowPrice['set_type'] == 2){
                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                        $price = $unitCost + $percent;
                    }else if($rowPrice['set_type'] == 3){
                        $price = $unitCost + $rowPrice['add_on'];
                    }
                }
                $priceSplit = split ("\.", number_format($price, 2)); 
                if(!empty($priceSplit[1])){
                    $mainPrice    = $priceSplit[0];
                    $decimalPrice = $priceSplit[1];
                } else {
                    $mainPrice    = $price;
                    $decimalPrice = '00';
                }
            }
            $conversion = $product['Product']['small_val_uom'] / $rowUom['conversion'];
        ?>
        <option barcode="<?php echo $code; ?>" main-price="<?php echo $mainPrice; ?>" decimal-price="<?php echo $decimalPrice; ?>" conversion="<?php echo $conversion; ?>" value="<?php echo $rowUom['id']; ?>"<?php echo $selected; ?>><?php echo $rowUom['name']; ?></option>
        <?php
        }
        ?>
    </select>
    <label for="Width">Width :</label> <input type="text" value="275" id="Width" style="width: 100px; height: 25px; margin-right: 10px;" />
    <label for="Total">Total :</label> <input type="text" value="24" id="Total" style="width: 100px; height: 25px; margin-right: 10px;" />
    <label for="Column">Column :</label> <input type="text" value="4" id="Column" style="width: 100px; height: 25px; margin-right: 10px;" />
    <label for="Row">Row :</label> <input type="text" value="6" id="Row" style="width: 100px; height: 25px;" />
    <?php
    }
    ?>
    <input type="button" value="<?php echo ACTION_PRINT; ?>" id="btnDisappearPrint" style="width: 160px; height: 50px; font-size: 14px;">
</div>
<div class="print_doc" style="width: 1145px; min-height: 775px; background: #fff; padding-top: 5px; padding-left: 5px; text-align: center;">
    <?php
    $items = array();
    if(!empty($product) && $printAll == 0){
        for($i=0; $i < 24; $i++){
            $items[$i]['id'] = $product['Product']['id'];
            $items[$i]['name'] = $product['Product']['name'];
            $items[$i]['code'] = $product['Product']['barcode'];
            $items[$i]['uom_label'] = $product['Product']['small_val_uom'];
            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$product['Product']['id']." AND product_prices.branch_id = 2 AND product_prices.uom_id =".$product['Product']['price_uom_id']);
            $price = 0;
            if(mysql_num_rows($sqlPrice)){
                while($rowPrice = mysql_fetch_array($sqlPrice)){
                    $unitCost = replaceThousand(number_format($rowPrice['unit_cost'], 2));
                    if($rowPrice['set_type'] == 1){
                        $price = $rowPrice['amount'];
                    }else if($rowPrice['set_type'] == 2){
                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                        $price = $unitCost + $percent;
                    }else if($rowPrice['set_type'] == 3){
                        $price = $unitCost + $rowPrice['add_on'];
                    }
                }
            }
            $items[$i]['price'] = $price;
        }
    } else if(!empty($product) && $printAll == 1){
        $i = 0;
        foreach($product AS $p){
            $items[$i]['id']   = $p['Product']['id'];
            $items[$i]['name'] = $p['Product']['name'];
            $items[$i]['code'] = $p['Product']['barcode'];
            $items[$i]['uom_label'] = $p['Product']['small_val_uom'];
            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.price_type_id, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.product_id =".$p['Product']['id']." AND product_prices.branch_id = 2 AND product_prices.uom_id =".$p['Product']['price_uom_id']);
            $price = 0;
            if(mysql_num_rows($sqlPrice)){
                while($rowPrice = mysql_fetch_array($sqlPrice)){
                    $unitCost = replaceThousand(number_format($rowPrice['unit_cost'], 2));
                    if($rowPrice['set_type'] == 1){
                        $price = $rowPrice['amount'];
                    }else if($rowPrice['set_type'] == 2){
                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                        $price = $unitCost + $percent;
                    }else if($rowPrice['set_type'] == 3){
                        $price = $unitCost + $rowPrice['add_on'];
                    }
                }
            }
            $items[$i]['price'] = $price;
            $i++;
        }
    } else {
        $sqlCk = mysql_query("SELECT products.id, products.price_uom_id, products.barcode, products.name, products.small_val_uom FROM user_print_product INNER JOIN products ON products.id = user_print_product.product_id WHERE user_id = ".$user['User']['id']);
        $i=0;
        while($rowCk = mysql_fetch_array($sqlCk)){
            $items[$i]['id']   = $rowCk['id'];
            $items[$i]['name'] = $rowCk['name'];
            $items[$i]['code'] = $rowCk['barcode'];
            $items[$i]['uom_label'] = $rowCk['small_val_uom'];
            $sqlPrice = mysql_query("SELECT products.unit_cost, product_prices.amount, product_prices.percent, product_prices.add_on, product_prices.set_type FROM product_prices INNER JOIN products ON products.id = product_prices.product_id WHERE product_prices.price_type_id = 3 AND product_prices.product_id =".$rowCk['id']." AND product_prices.branch_id = 2 AND product_prices.uom_id =".$rowCk['price_uom_id']);
            $price = 0;
            if(mysql_num_rows($sqlPrice)){
                while($rowPrice = mysql_fetch_array($sqlPrice)){
                    $unitCost = replaceThousand(number_format($rowPrice['unit_cost'], 2));
                    if($rowPrice['set_type'] == 1){
                        $price = $rowPrice['amount'];
                    }else if($rowPrice['set_type'] == 2){
                        $percent = ($unitCost * $rowPrice['percent']) / 100;
                        $price = $unitCost + $percent;
                    }else if($rowPrice['set_type'] == 3){
                        $price = $unitCost + $rowPrice['add_on'];
                    }
                }
            }
            $items[$i]['price'] = $price;
            $i++;
        }
    }
    if(!empty($items)){
        $i = 0;
        $row = 0;
        $first = 0;
        foreach($items AS $item){
            $i++;
            $break = '';
            $firstId = '';
            if($row == 6){
                $row = 0;
                $break = 'page-break-before: always;';
            }
            $priceSplit = split ("\.", number_format($item['price'], 2)); 
            if(!empty($priceSplit[1])){
                $mainPrice = $priceSplit[0];
                $sencPrice = $priceSplit[1];
            } else {
                $mainPrice = $item['price'];
                $sencPrice = '00';
            }
            $pgroupName = '';
            $departmentName = '';
            $sqlPg = mysql_query("SELECT department_id, name FROM pgroups WHERE id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$item['id'].") LIMIT 1");
            if(mysql_num_rows($sqlPg)){
                $rowPg = mysql_fetch_array($sqlPg);
                $pgroupName = $rowPg['name'];
                if(!empty($rowPg['department_id'])){
                    $sqlD = mysql_query("SELECT name FROM departments WHERE id = ".$rowPg['department_id']);
                    $rowD = mysql_fetch_array($sqlD);
                    $departmentName = $rowD['name'];
                }
            }
            if($first == 0){
                $firstId = 'id="itemLabelPrintOne"';
            }
    ?>
    <div style="width: 275px; height: 117px; margin-bottom: 5px; margin-right: 5px; border: 1px solid #f5effd; padding: 1px; float: left;<?php echo $break; ?>" class="itemLabelPrint" <?php echo $firstId; ?>>
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="height: 35px; width: 39px;">
                    <img src="<?php echo $this->webroot; ?>img/super-retail-logo-s.png" style="height: 35px; width: 37px;" />
<!--                    <input type="hidden" class="qrCodeItem" value="<?php echo $item['code']; ?>" />
                    <div class="cardQRCode" style="width: 35px; height: 35px; float: right;"></div>
                    <div style="clear: both;"></div>-->
                </td>
                <td style="text-align: right; font-size: 14px; font-family: 'arialbd'; vertical-align: top; font-weight: bold; line-height: 15px;">
                    <?php echo $item['name']; ?><span class="lblConversion" style="font-size: 14px; font-family: 'arialbd'; font-weight: bold;">/<?php echo $item['uom_label']; ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="padding: 0px; margin: 0px; width: 42%; float: left;">
                        <div style="width: 110px; padding-left: 0px; padding-top: 3px; margin: 0px;" class="divItemImgBarcode"> 
                            <img src="<?php echo $this->webroot; ?>barcodegen.1d-php5.v2.2.0/generate_barcode.php?str=<?php echo $item['code']; ?>" style="width: 110px; height: 20px;" class="itemBarcode" />
                        </div>
                        <div style="width: 110px; padding-left: 0px; text-align: center; font-size: 9px; font-weight: bold; font-family: 'Arial';" class="divItemBarcode">
                            <?php echo $item['code']; ?>
                        </div>
                    </div>
                    <div style="padding: 0px; margin: 0px; width: 57%; float: right; text-align: right; font-size: 11px; font-family: 'Arial';">
                        <?php echo $pgroupName; ?><br/>
                        <?php echo $departmentName; ?>
                    </div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="vertical-align: top; margin: 0px; padding: 0px; height: 48px;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td style="vertical-align: top; text-align: right; font-weight: bold; font-family: 'MC-sans'; font-size: 35px;" class="mainPrice"><?php echo $mainPrice; ?></td>
                            <td style="width: 37px;">
                                <div style="width: 100%; text-align: center; vertical-align: top;">
                                    <div style="font-size: 24px; padding: 0px; font-family: 'MC-sans';" class="decimalPrice">.<?php echo $sencPrice; ?></div>
                                    <span style="font-size: 12px; font-family: 'Arial'; margin-left: 5px;">each</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <?php
            if($i == 4){
                $row++;
                $i = 0;
    ?>
    <!-- <div style="clear: both;"></div> -->
    <?php
            }
            $first++;
        }
    }
    ?>
    <!-- <div style="clear: both;"></div> -->
</div>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery-1.4.4.min.js"></script>
<!-- autoNumeric -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/autoNumeric-1.6.2.js"></script>
<!-- QR Code -->
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.qrcode.min.js"></script>
<script type="text/javascript">
    var labelPrint = [];
    var layout = $("#itemLabelPrintOne").html();
    var style  = $("#itemLabelPrintOne").attr("style");
    function replaceNum(str){
        if(str != "" && str != undefined && str != null){
            var str = parseFloat(str.toString().replace(/,/g,""));
        }else{
            var str = 0;
        }
        return str;
    }
    $(document).ready(function() {
//        var qrCode = $(".qrCodeItem").val();
//        $('.cardQRCode').qrcode({
//            width	: "35",
//            height	: "35",
//            text	: qrCode
//        });
        
        $("#Width, #Column, #Row, #Total").autoNumeric({mDec: 0, aSep: ','});
        <?php
        if(!empty($product) && $printAll == 0){
        ?>
        $("#Width, #Column, #Row, #Total").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $("#Column, #Row, #Total").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
            recalculateLayout();
        });
        
        $("#Width").blur(function(){
            if($(this).val() == '' || replaceNum($(this).val()) < 275){
                $(this).val(275);
            }
            recalculateLayout();
        });
        
        $("#uomSelect").change(function(){
            recalculateLayout();
        });
        
        <?php
        } else {
        ?>
        $(".itemLabelPrint").each(function(){
            var layoutPrint = $(this).html();
            labelPrint.push(layoutPrint);
        });
        <?php
        }
        ?>
                
        $(document).dblclick(function() {
            window.close();
        });
        
        $("#btnDisappearPrint").click(function() {
            window.print();
            window.close();
        });
    });
    
    function recalculateLayout(){
        var width  = replaceNum($("#Width").val());
        var col    = replaceNum($("#Column").val());
        var row    = replaceNum($("#Row").val());
        var total  = replaceNum($("#Total").val());
        var print  = "";
        var i;
        var colDis = 0;
        var rowDis = 0;
        var conversion = $("#uomSelect").find("option:selected").attr("conversion");
        var barcode    = $("#uomSelect").find("option:selected").attr("barcode");
        var mainPrice  = $("#uomSelect").find("option:selected").attr("main-price");
        var decimalPrice  = $("#uomSelect").find("option:selected").attr("decimal-price");
        for (i = 0; i < total; i++) {
            colDis++;
            var pBreak = '';
            if(rowDis == row){
                pBreak = 'page-break-before: always;';
            }
            print += '<div style="'+style+' '+pBreak+'" class="itemLabelPrint">'+layout+'</div>';
            if(colDis == col){
                rowDis++;
                colDis = 0;
                print += '<div style="clear: both;"></div>';
            }
        } 
        print += '<div style="clear: both;"></div>';
        // Add Print Layout
        $(".print_doc").html(print);
        $(".itemLabelPrint").css("width", width+"px");
        $(".lblConversion").text('/'+conversion);
        $(".divItemBarcode").text(barcode);
        $(".qrCodeItem").val(barcode);
        $(".itemBarcode").attr("src", "<?php echo $this->webroot; ?>barcodegen.1d-php5.v2.2.0/generate_barcode.php?str="+barcode);
        $(".mainPrice").text(mainPrice);
        $(".decimalPrice").text("."+decimalPrice);
//        var qrCode = $(".qrCodeItem").val();
//        $('.cardQRCode').qrcode({
//            width	: "35",
//            height	: "35",
//            text	: qrCode
//        });
    }
</script>