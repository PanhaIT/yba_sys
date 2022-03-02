<?php 
$rnd = rand();
$frmName = "frm" . $rnd;
$dueDate = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$qty = "qty" . $rnd;
$locationGroup = "locationGroup" . $rnd;
$location  = "location" . $rnd;
$vendor    = "vendor" . $rnd;
$pgroup    = "pgroup" . $rnd;
$pgroupId  = "pgroupId".$rnd;
$pgroupDel = "pgroupDel".$rnd;
$product = "product" . $rnd;
$productId = "productId".$rnd;
$productDel = "productDel".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
$department = "department" . $rnd;
$sortItem = "sortItem" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            maxDate: 0,
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
            var url="<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/productResult";
            var isFormValidated=$("#<?php echo $frmName; ?>").validationEngine('validate');
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
        
        // Location Group
        $("#<?php echo $locationGroup; ?>").change(function(){
            resetLocation<?php echo $rnd; ?>();
        });
        // Reset Location
        resetLocation<?php echo $rnd; ?>();
    });
    
    function resetLocation<?php echo $rnd; ?>(){
        var locationGroup = $("#<?php echo $locationGroup; ?>").val();
        $("#<?php echo $location; ?>").filterOptions('location-group', locationGroup, '');
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_PRODUCT_INVENTORY; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date', 'style' => 'width: 90%;')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo 'Beginning'; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $dateTo; ?>"><?php echo 'Ending'; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $qty; ?>"><?php echo TABLE_QTY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $qty; ?>" name="qty" style="width: 90%;">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <option value="0">No</option>
                            <option value="1" selected="selected">Yes</option>
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
                <td style="width: 8%;"><label for="<?php echo $locationGroup; ?>"><?php echo TABLE_LOCATION_GROUP; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($locationGroup, $locationGroups, null, array('escape' => false, 'name' => 'location_group_id', 'empty' => TABLE_ALL, 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $location; ?>"><?php echo TABLE_LOCATION; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $location; ?>" name="location_id">
                            <option value="" location-group=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            foreach($locations AS $loc){
                            ?>
                            <option value="<?php echo $loc['Location']['id']; ?>" location-group="<?php echo $loc['Location']['location_group_id']; ?>"><?php echo $loc['Location']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $pgroup; ?>"><?php echo MENU_DEPARTMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer" style="width: 100%;">
                        <select id="<?php echo $department; ?>" name="department_id">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php 
                                $queryDepartment = mysql_query("SELECT id, name FROM departments WHERE is_active = 1");
                                if(mysql_num_rows($queryDepartment)){
                                    while($dataDepartment = mysql_fetch_array($queryDepartment)){
                            ?>
                            <option value="<?php echo $dataDepartment['id']; ?>"><?php echo $dataDepartment['name']; ?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                        
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $pgroup; ?>"><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="hidden" name="pgroup_id" id="<?php echo $pgroupId; ?>" />
                        <?php echo $this->Form->text($pgroup, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $pgroupDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $product; ?>"><?php echo TABLE_PRODUCT; ?>:</label></td>
                <td style="width: 25%;">
                    <div class="inputContainer">
                        <input type="hidden" name="product_id" id="<?php echo $productId; ?>" />
                        <?php echo $this->Form->text($product, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $productDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $vendor; ?>"><?php echo TABLE_VENDOR; ?>:</label></td>
                <td style="width: 20%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($vendor, $vendors, null, array('escape' => false, 'name' => 'vendor_id', 'empty' => TABLE_ALL, 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
                <td style="width: 5%;"><label for="<?php echo $sortItem; ?>"><?php echo TABLE_SORT_ITEM; ?>:</label></td>
                <td style="width: 20%;">
                    <div class="inputContainer">
                        <select id="<?php echo $sortItem; ?>" name="sort_item" style="width:50%;">
                            <option value="1">A-Z</option>
                            <option value="2">Z-A</option>
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