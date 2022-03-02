<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        // $("#ProductPriceSize,#ProductPriceColor,#ProductPriceVendor,#ProductPriceBranch").chosen({ width: 250 });
        $(".btnSetProductPrice").unbind("click").click(function(){
            getProductProductPrice();
        });
        $("#ProductPriceSize,#ProductPriceColor,#ProductPriceVendor,#ProductPriceBranch").change(function(){
            getProductProductPrice();
        });
        $("#ProductPriceSize,#ProductPriceColor,#ProductPriceVendor,#ProductPriceBranch").change();
    });

    function getProductProductPrice(){
        var branchId = $("#ProductPriceBranch").val();
        if(branchId != ''){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/products'; ?>/productPriceDetail/" + branchId+"/<?php echo $products['Product']['id']; ?>",
                data: "vendor_id="+$("#ProductPriceVendor").val()+"&color_id="+$("#ProductPriceColor").val()+"&size_id="+$("#ProductPriceSize").val(),
                beforeSend: function(){
                    $("#productPriceDetail").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result){
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#productPriceDetail").html(result);
                }
            });
        } else {
            $("#productPriceDetail").html('<b style="font-size: 18px;"><?php echo MESSAGE_SELECT_BRANCH_TO_SHOW_PRICE_LIST; ?></b>');
        }
    }
</script>
<?php echo $this->Form->create('ProductPrice', array('id' => 'ProductPrice')); ?>
<div>
    <div id="dynamic">
        <fieldset>
            <legend><?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?></legend>
            <table style="width: 100%;">
                <tr>
                    <td rowspan="4" style="width: 25%;">
                        <img id="photoDisplay" alt="" <?php echo $products['Product']['photo'] != '' ? 'src="' . $this->webroot . 'public/product_photo/' . $products['Product']['photo'] . '"' : ''; ?> style="max-width: 200px; max-height: 200px;" />
                    </td>
                    <td style="width: 9%; vertical-align: top; height: 30px;"><?php echo TABLE_BARCODE; ?> :</td>
                    <td style="width: 25%; vertical-align: top;"><?php echo $products['Product']['barcode']; ?></td>
                    <td style="width: 9%; vertical-align: top;"><?php echo TABLE_SKU; ?> :</td>
                    <td style="vertical-align: top;"><?php echo $products['Product']['code']; ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; height: 30px;"><?php echo TABLE_PRODUCT_NAME; ?> :</td>
                    <td style="vertical-align: top;"><?php echo $products['Product']['name']; ?></td>
                    <td style="vertical-align: top;"><?php echo TABLE_COMPANY; ?> :</td>
                    <td style="vertical-align: top;">
                        <?php 
                            $sqlCom = mysql_query("SELECT name FROM companies WHERE id = ".$products['Product']['company_id']);
                            $rowCom = mysql_fetch_array($sqlCom);
                            echo $rowCom[0]; 
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; height: 30px;"><?php echo TABLE_UOM; ?> :</td>
                    <td style="vertical-align: top;">
                        <?php
                            $sqlUom = mysql_query("SELECT name FROM uoms WHERE id = ".$products['Product']['price_uom_id']);
                            $rowUom = mysql_fetch_array($sqlUom);
                            echo $rowUom[0]; 
                        ?>
                    </td>
                    <td style="vertical-align: top;"><?php echo TABLE_GROUP; ?> :</td>
                    <td style="vertical-align: top;">
                        <?php 
                            $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) FROM pgroups WHERE id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$products['Product']['id'].")");
                            $rowGroup = mysql_fetch_array($sqlGroup);
                            echo $rowGroup[0]; 
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><?php echo GENERAL_DESCRIPTION; ?> :</td>
                    <td style="vertical-align: top;">
                        <?php echo nl2br($products['Product']['description']); ?>
                    </td>
                    <td style="vertical-align: top;"><?php echo TABLE_SPEC; ?> :</td>
                    <td style="vertical-align: top;">
                        <?php echo nl2br($products['Product']['spec']); ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <table style="width: 100%; margin-top: 5px;">
            <tr>
                <td style="width:25%;"><label for=""><?php echo MENU_BRANCH; ?></label> :</td>
                <td style="width:25%;"><label for=""><?php echo 'Vendor'; ?></label> :</td>
                <td style="width:25%;"><label for=""><?php echo 'Size'; ?></label> :</td>
                <td style="width:25%;"><label for=""><?php echo 'Color'; ?></label> :</td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="inputContainer" style="width:100%;">
                        <select name="data[branch_id]" id="ProductPriceBranch" style="width: 250px;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="0"><?php echo TABLE_ALL; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width:100%;">
                        <select name="data[vendor_id]" id="ProductPriceVendor" class="" style="width: 250px;">
                            <?php if(count($vendors) > 1){ ?>
                                <option value="0"><?php echo TABLE_ALL; ?></option>
                            <?php }else if(count($vendors) == 0){ ?>
                                <option value="empty"><?php echo INPUT_EMPTY; ?></option>
                                <?php }
                                if(count($vendors) > 0){
                                    foreach($vendors AS $vendor){
                                    ?>
                                    <option value="<?php echo $vendor['Vendor']['id']; ?>"><?php echo $vendor['Vendor']['name']; ?></option>
                                    <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width:100%;">
                        <select name="data[size_id]" id="ProductPriceSize" class="" style="width: 250px;">
                            <?php if(count($sizes) > 1){ ?>
                                <option value="0"><?php echo TABLE_ALL; ?></option>
                            <?php }else if(count($sizes) == 0){ ?>
                                <option value="empty"><?php echo INPUT_EMPTY;?></option>
                                <?php }
                                if(count($sizes) > 0){
                                    foreach($sizes AS $size){
                                    ?>
                                    <option value="<?php echo $size['Size']['id']; ?>"><?php echo $size['Size']['name']; ?></option>
                                    <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="inputContainer" style="width:100%;">
                        <select name="data[color_id]" id="ProductPriceColor" class="" style="width: 250px;">
                            <?php if(count($colors) > 1){ ?>
                                <option value="0"><?php echo TABLE_ALL; ?></option>
                            <?php }else if(count($colors) == 0){ ?>
                                <option value="empty"><?php echo INPUT_EMPTY;?></option>
                                <?php }
                                if(count($colors) > 0){
                                    foreach($colors AS $color){
                                    ?>
                                    <option value="<?php echo $color['Color']['id']; ?>"><?php echo $color['Color']['name']; ?></option>
                                    <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="buttons" style="float: left; margin-left: 10px;">
                        <button type="button" class="positive btnSetProductPrice" style="width:100px;">
                            <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                            <?php echo ACTION_SET_PRICE; ?>
                        </button>
                    </div>
                </td>
            </tr> 
        </table>
        <br />
        <div id="productPriceDetail" style="text-align: center;"><b style="font-size: 18px;"><?php echo MESSAGE_SELECT_BRANCH_TO_SHOW_PRICE_LIST; ?></b></div>
    </div>
</div>
<?php echo $this->Form->end(); ?>