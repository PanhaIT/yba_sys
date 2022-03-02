<?php 
$rnd = rand();
$frmName  = "frm" . $rnd;
$dueDate  = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo  = "dateTo" . $rnd;
$status  = "status" . $rnd;
$company = "company" . $rnd;
$branch  = "branch" . $rnd;
$pgroup  = "pgroup" . $rnd;
$pgroupId  = "pgroupId".$rnd;
$pgroupDel = "pgroupDel".$rnd;
$product   = "product" . $rnd;
$productId = "productId".$rnd;
$productDel = "productDel".$rnd;
$show = "show".$rnd;
$item = "item".$rnd;
$view = "view".$rnd;
$sort = "sort".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $item; ?>").autoNumeric({mDec: 0, aSep: ','});
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $dateFrom; ?>" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated=$("#<?php echo $frmName; ?>").validationEngine('validate');
            var url = '';
            if($("#<?php echo $show; ?>").val() == 1){
                url = 'salesTopCustomerResult';
            } else {
                url = 'salesTopCustomerGraph';
            }
            if(isFormValidated){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/"+url,
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
        
        // Search Product Group
        $("#<?php echo $pgroup; ?>").autocomplete("<?php echo $this->base . "/reports/searchPgroup"; ?>", {
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
            $("#<?php echo $pgroupId; ?>").val(pgroupId);
            $("#<?php echo $pgroupDel; ?>").show();
        });
        
        $("#<?php echo $pgroupDel; ?>").click(function(){
            $("#<?php echo $pgroupId; ?>").val('');
            $("#<?php echo $pgroup; ?>").val('');
            $(this).hide();
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
            var pgroupId = value.toString().split(".*")[0];
            $("#<?php echo $productId; ?>").val(pgroupId);
            $("#<?php echo $productDel; ?>").show();
        });
        
        $("#<?php echo $productDel; ?>").click(function(){
            $("#<?php echo $productId; ?>").val('');
            $("#<?php echo $product; ?>").val('');
            $(this).hide();
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_SALES_TOP_CUSTOMER; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 6%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $status; ?>"><?php echo TABLE_STATUS; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $status; ?>" name="status">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="1">Issued</option>
                            <option value="2" selected="selected">Fulfilled</option>
                            <option value="0">Void</option>
                        </select>
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
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $company; ?>"><?php echo TABLE_COMPANY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($branch, $branches, null, array('escape' => false, 'name' => 'branch_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 9%;"><label for="<?php echo $pgroup; ?>"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?>:</label></td>
                <td style="width: 20%;">
                    <div class="inputContainer">
                        <input type="hidden" name="pgroup_id" id="<?php echo $pgroupId; ?>" />
                        <?php echo $this->Form->text($pgroup, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $pgroupDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 7%;"><label for="<?php echo $product; ?>"><?php echo TABLE_PRODUCT; ?>:</label></td>
                <td style="width: 20%;">
                    <div class="inputContainer">
                        <input type="hidden" name="product_id" id="<?php echo $productId; ?>" />
                        <?php echo $this->Form->text($product, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $productDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $item; ?>"><?php echo TABLE_ITEM_SHOW; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" name="item_show" id="<?php echo $item; ?>" style="width: 90%;" value="10" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $show; ?>"><?php echo TABLE_DISPLAY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $show; ?>">
                            <option value="1">List Records</option>
                            <option value="2">Pie Chart</option>
                        </select>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $view; ?>"><?php echo TABLE_SORT_BY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $view; ?>" name="view_by">
                            <option value="1">Total Invoice</option>
                            <option value="2">Total Amount</option>
                        </select>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $sort; ?>"><?php echo TABLE_VIEW_BY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $sort; ?>" name="sort_by">
                            <option value="1">Top</option>
                            <option value="2">Bottom</option>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>