<?php 
$rnd = rand();
$frmName = "frm" . $rnd;
$dueDate = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$company = "company" . $rnd;
$branch  = "branch" . $rnd;
$customer = "customer" . $rnd;
$customerId = "customerId".$rnd;
$customerDel = "customerDel".$rnd;
$vendor = "vendor" . $rnd;
$vendorId = "vendorId".$rnd;
$vendorDel = "vendorDel".$rnd;
$other = "other" . $rnd;
$class = "class" . $rnd;
$isAdj = "isAdj" . $rnd;
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
            if(isFormValidated){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/generalLedgerResult",
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
        
        // Search Vendor
        $("#<?php echo $vendor; ?>").autocomplete("<?php echo $this->base . "/reports/searchVendor"; ?>", {
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
            $("#<?php echo $vendorId; ?>").val(productId);
            $("#<?php echo $vendorDel; ?>").show();
        });
        
        $("#<?php echo $vendorDel; ?>").click(function(){
            $("#<?php echo $vendorId; ?>").val('');
            $("#<?php echo $vendor; ?>").val('');
            $(this).hide();
        });
        
        // Company Change
        $("#<?php echo $company; ?>").change(function(){
            var companyId = $(this).val();
            if(companyId != ''){
                $("#<?php echo $class; ?>").filterOptions('com', companyId, '');
            } else {
                $("#<?php echo $class; ?>").find("option").removeAttr('selected');
                $("#<?php echo $class; ?>").find('option').map(function () {
                    return $(this).parent('span').length === 0 ? this : null;
                }).wrap('<span>').hide();
                $("#<?php echo $class; ?>").find("option").unwrap().show();
                $("#<?php echo $class; ?>").find('option[value=""]').attr('selected', true);
            }
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_JOURNAL_ENTRY_MANAGEMENT; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT, 'name' => 'due_date')); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
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
                <td style="width: 6%;"><label for="<?php echo $company; ?>"><?php echo TABLE_COMPANY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td>
                    <div class="buttons">
                        <button type="button" id="<?php echo $btnSearch; ?>" class="positive">
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
                <td style="width: 8%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($branch, $branches, null, array('escape' => false, 'name' => 'branch_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $customer; ?>"><?php echo TABLE_CUSTOMER; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="hidden" name="customer_id" id="<?php echo $customerId; ?>" />
                        <?php echo $this->Form->text($customer, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $customerDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $vendor; ?>"><?php echo TABLE_VENDOR; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="hidden" name="vendor_id" id="<?php echo $vendorId; ?>" />
                        <?php echo $this->Form->text($vendor, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $vendorDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $other; ?>"><?php echo TABLE_OTHER; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($other, $others, null, array('escape' => false, 'name' => 'other_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $class; ?>"><?php echo TABLE_CLASS; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $class; ?>" name="class_id">
                            <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            $sqlClass=mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM class_companies WHERE class_id = classes.id) AS company FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data=mysql_fetch_array($sqlClass)){?>
                            <option value="<?php echo $data['id']; ?>" com="<?php echo $data['company']; ?>"><?php echo $data['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $isAdj; ?>"><?php echo GENERAL_ADJUSTING_ENTRY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->input('isAdj', array('empty' => TABLE_ALL,'options'=>array('0' => 'No', '1' => 'Yes'), 'id' => $isAdj, 'name' => $isAdj, 'label' => false)); ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>