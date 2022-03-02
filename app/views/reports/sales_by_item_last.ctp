<?php 
$rnd = rand();
$frmName = "frm" . $rnd;
$branch  = "branch" . $rnd;
$product = "product" . $rnd;
$productId   = "productId".$rnd;
$productDel  = "productDel".$rnd;
$customer    = "customer" . $rnd;
$customerId  = "customerId".$rnd;
$customerDel = "customerDel".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var url = "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/salesByItemLastResult";
            var isFormValidated = $("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                $.ajax({
                    type: "POST",
                    url: url,
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                    }
                });
            }
        });
        // Button Show Hide
        $("#<?php echo $btnShowHide; ?>").click(function(){
            var text = $(this).text();
            var formFilter = $(".<?php echo $formFilter; ?>");
            if(text == "[<?php echo TABLE_SHOW; ?>]"){
                formFilter.show();
                $(this).text("[<?php echo TABLE_HIDE; ?>]");
            }else{
                formFilter.hide();
                $(this).text("[<?php echo TABLE_SHOW; ?>]");
            }
        });
        
        // Search Product
        $("#<?php echo $product; ?>").autocomplete("<?php echo $this->base . "/reports/searchProduct"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var productId = value.toString().split(".*")[0];
            $("#<?php echo $productId; ?>").val(productId);
            $("#<?php echo $productDel; ?>").show();
        });
        
        $("#<?php echo $productDel; ?>").click(function(){
            $("#<?php echo $productId; ?>").val('');
            $("#<?php echo $product; ?>").val('');
            $(this).hide();
        });
        
        // Search Customer
        $("#<?php echo $customer; ?>").autocomplete("<?php echo $this->base . "/reports/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var pgroupId = value.toString().split(".*")[0];
            $("#<?php echo $customerId; ?>").val(pgroupId);
            $("#<?php echo $customerDel; ?>").show();
        });
        
        $("#<?php echo $customerDel; ?>").click(function(){
            $("#<?php echo $customerId; ?>").val('');
            $("#<?php echo $customer; ?>").val('');
            $(this).hide();
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_REPORT_ITEM_BY_LAST_SOLD_DATE; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $product; ?>"><?php echo TABLE_PRODUCT; ?>:</label></td>
                <td style="width: 23%;">
                    <div class="inputContainer">
                        <input type="hidden" name="product_id" id="<?php echo $productId; ?>" />
                        <?php echo $this->Form->text($product, array('escape' => false, 'name' => '', 'style' => 'width: 80%;', 'placeholder' => TABLE_SEARCH_SKU_NAME, 'class' => 'validate[required]')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $productDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($branch, $branches, null, array('escape' => false, 'name' => 'branch_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $customer; ?>"><?php echo TABLE_CUSTOMER; ?>:</label></td>
                <td style="width: 23%;">
                    <div class="inputContainer">
                        <input type="hidden" name="customer_id" id="<?php echo $customerId; ?>" />
                        <?php echo $this->Form->text($customer, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $customerDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td>
                    <div class="buttons">
                        <button type="button" id="<?php echo $btnSearch; ?>" class="positive" style="width: 130px;">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span id="<?php echo $btnSearchLabel; ?>"><?php echo GENERAL_SEARCH; ?></span>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>