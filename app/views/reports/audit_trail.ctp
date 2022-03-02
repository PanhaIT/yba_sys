<?php 
$rnd = rand();
$frmName = "frm" . $rnd;
$dueDate = "dueDate" . $rnd;
$dateFrom = "dateFrom" . $rnd;
$dateTo = "dateTo" . $rnd;
$company  = "company" . $rnd;
$branch   = "branch" . $rnd;
$createdBy = "createdBy" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$btnClear = "btnClear" . $rnd;
$result = "result" . $rnd;
$withPos = "withPos" . $rnd;
$withSaleOrder = "withSaleOrder" . $rnd;
$creditMemo = "creditMemo" . $rnd;
$delivery = "delivery" . $rnd;
$receivePayment = "receivePayment" . $rnd;
$adjustment  = "adjustment" . $rnd;
$transfer  = "transfer" . $rnd;
$purchaseOrder  = "purchaseOrder" . $rnd;
$purchaseBill  = "purchaseBill" . $rnd;
$purchaseReceive  = "purchaseReceive" . $rnd;
$billReturn  = "billReturn" . $rnd;
$payBill  = "payBill" . $rnd;
$allVoid  = "allVoid" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        // chosen init
        $(".chzn-select").chosen();
        
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
            var url="<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/auditTrailResult";
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
        
        $("#<?php echo $btnClear; ?>").click(function(){
            $("input[type='checkbox']").attr("checked", false);
            $("#<?php echo $result; ?>").html("");
            $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").val("");
            $("#<?php echo $dueDate; ?>").val("");
            $("#<?php echo $createdBy; ?>").val("");
        });
        
        $("#<?php echo $payBill; ?>,#<?php echo $billReturn; ?>,#<?php echo $purchaseReceive; ?>,#<?php echo $purchaseBill; ?>,#<?php echo $purchaseOrder; ?>,#<?php echo $transfer; ?>,#<?php echo $adjustment; ?>,#<?php echo $receivePayment; ?>,#<?php echo $delivery; ?>,#<?php echo $creditMemo; ?>,#<?php echo $withSaleOrder; ?>,#<?php echo $withPos; ?>").click(function(){
            $("#<?php echo $allVoid; ?>").attr("checked", false);
        });
                
        $("#<?php echo $allVoid; ?>").click(function(){
            if($("#<?php echo $allVoid; ?>").is(":checked")){
                $("input[type='checkbox']").attr("checked", false);
                $("#<?php echo $allVoid; ?>").attr("checked", true);
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
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_AUDIT_TRAIL; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
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
                <td style="width: 8%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                
                <td style="width: 8%;"><label for="<?php echo $createdBy; ?>"><?php echo TABLE_CREATED_BY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $createdBy; ?>" class="createdBy"  name="created_by">
                            <option value=""><?php echo TABLE_ALL ?></option>
                            <?php
                            foreach ($users as $key => $value) {
                                echo "<option value='{$key}' >{$value}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $company; ?>"><?php echo TABLE_COMPANY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($branch, $branches, null, array('escape' => false, 'name' => 'branch_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $withSaleOrder; ?>" name="saleOrder" type="checkbox" /> <label for="<?php echo $withSaleOrder; ?>"><?php echo MENU_SALES_ORDER_MANAGEMENT; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $withPos; ?>" name="withPos" type="checkbox" /> <label for="<?php echo $withPos; ?>"><?php echo MENU_POS;?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $creditMemo; ?>" name="creditMemo" type="checkbox" /> <label for="<?php echo $creditMemo; ?>"><?php echo MENU_CREDIT_MEMO_MANAGEMENT;?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $delivery; ?>" name="delivery" type="checkbox" /> <label for="<?php echo $delivery; ?>"><?php echo MENU_DELIVERY_MANAGEMENT;?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $receivePayment; ?>" name="receivePayment" type="checkbox" /> <label for="<?php echo $receivePayment; ?>"><?php echo MENU_RECEIVE_PAYMENTS;?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $billReturn; ?>" name="billReturn" type="checkbox" /> <label for="<?php echo $billReturn; ?>"><?php echo MENU_PURCHASE_RETURN_MANAGEMENT; ?></label>
                </td>                
                <td></td>
                <td rowspan="2">
                    <div class="buttons">
                        <button type="button" id="<?php echo $btnSearch; ?>" class="positive">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span id="<?php echo $btnSearchLabel; ?>"><?php echo GENERAL_SEARCH; ?></span>
                        </button>
                    </div>
                    <div class="buttons">
                        <button type="button" style="margin-top:5px; padding-right:18px;" id="<?php echo $btnClear; ?>" class="positive">
                            <img src="<?php echo $this->webroot; ?>img/button/clear.png" alt=""/>
                            <?php echo ACTION_CLEAR; ?>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $adjustment; ?>" name="adjustment" type="checkbox" /> <label for="<?php echo $adjustment; ?>"><?php echo MENU_INVENTORY_ADJUSTMENT;?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $transfer; ?>" name="transfer" type="checkbox" /> <label for="<?php echo $transfer; ?>"><?php echo MENU_TRANSFER_ORDER_MANAGEMENT; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $purchaseOrder; ?>" name="purchaseOrder" type="checkbox" /> <label for="<?php echo $purchaseOrder; ?>"><?php echo MENU_PURCHASE_REQUEST_MANAGEMENT; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $purchaseBill; ?>" name="purchaseBill" type="checkbox" /> <label for="<?php echo $purchaseBill; ?>"><?php echo MENU_PURCHASE_ORDER_MANAGEMENT; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $purchaseReceive; ?>" name="purchaseReceive" type="checkbox" /> <label for="<?php echo $purchaseReceive; ?>"><?php echo MENU_PURCHASE_RECEIVE_MANAGEMENT; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $payBill; ?>" name="payBill" type="checkbox" /> <label for="<?php echo $payBill; ?>"><?php echo MENU_PAY_BILLS; ?></label>
                </td>
                <td>
                    <input style="float: left; width: 30px;" id="<?php echo $allVoid; ?>" name="allVoid" type="checkbox" /> <label for="<?php echo $allVoid; ?>"><?php echo ACTION_VOID; ?></label>
                </td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>