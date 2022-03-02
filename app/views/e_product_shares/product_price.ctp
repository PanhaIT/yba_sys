<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function() {
        // Prevent Key Enter
        preventKeyEnter();
        getDetail();
    });
    
    function getDetail(){
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/productPriceDetail/<?php echo $products['Product']['id']; ?>",
            data: "",
            beforeSend: function(){
                $("#productPriceDetail").html('<img alt="Loading" src="<?php echo $this->webroot; ?>img/ajax-loader.gif" />');
                $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result){
                $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#productPriceDetail").html(result);
            }
        });
    }
</script>
<?php echo $this->Form->create('ProductPrice', array('id' => 'ProductPrice')); ?>
<div>
    <div id="dynamic">
        <fieldset>
            <legend><?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?></legend>
            <table style="width: 100%;">
                <tr>
                    <td rowspan="4" style="width: 18%;">
                        <img id="photoDisplay" alt="" <?php echo $products['Product']['photo'] != '' ? 'src="' . $this->webroot . 'public/product_photo/' . $products['Product']['photo'] . '"' : ''; ?> style="max-width: 200px; max-height: 200px;" />
                    </td>
                    <td style="width: 15%; vertical-align: top; height: 30px;"><?php echo TABLE_BARCODE; ?> :</td>
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
        <div id="productPriceDetail" style="text-align: center;">
            
        </div>
    </div>
</div>
<?php echo $this->Form->end(); ?>