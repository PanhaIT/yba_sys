<?php 
include("includes/function.php");
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Disabed Submit Button
        $(".btnSaveShiftCollect").removeAttr('disabled');
        // Form Validation
        $("#ShiftCollectForm").validationEngine('detach');
        $("#ShiftCollectForm").validationEngine('attach');
        // Check Bf Save
        $(".btnSaveShiftCollect").unbind("click");
        $(".btnSaveShiftCollect").click(function(){
            var formName = "#ShiftCollectForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            } else {
                if($("#ShiftCollectCompanyId").val() == "" || $("#ShiftCollectBranchId").val() == "" || $("#ShiftCollectUserId").val() == "" || $("#ShiftCollectEmployeeId").val() == ""){
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            }
        });
        $("#ShiftCollectForm").ajaxForm({
            dataType: 'json',
            beforeSerialize: function($form, options) {
                $("#ShiftCollectDate").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveShiftCollect").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // Reset Form and Refresh Data
                $("#ShiftCollectDate").val("");
                $("#ShiftCollectNote").val("");
                $(".txtSaveShiftCollect").html("<?php echo ACTION_SAVE; ?>");
                $("#ShiftCollectBranchId").change();
                $("button[type=submit]", $("#ShiftCollectForm")).removeAttr('disabled');
                loadTablePaymentCustomer();
                // Alert Message
                $("#dialog").html('<div class="buttons"><button type="submit" class="positive reprintShiftCollects" ><img src="<?php echo $this->webroot; ?>img/button/printer.png" alt=""/><span class="txtReprintInvoiceSales"><?php echo ACTION_REPRINT_INVOICE; ?></span></button></div>');
                $(".reprintShiftCollects").click(function() {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base . '/' . "shift_collects"; ?>/printShiftCollect/"+result.shift_id,
                        beforeSend: function() {
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                        },
                        success: function(printInvoiceResult) {
                            w = window.open();
                            w.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
                            w.document.write('<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/style.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/table.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/button.css" /><link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>css/print.css" media="print" />');
                            w.document.write(printInvoiceResult);
                            w.document.close();
                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        }
                    });
                });
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        // Get Reference Code
        var mCode = $("#CustomerPaymentBranchId").find("option:selected").attr("mcode");
        $("#CustomerPaymentReference").val("<?php echo date("y"); ?>"+mCode);
    });
</script>
<table style="width: 300px; float: left;">
    <tr>
        <td colspan="2">
            <input type="hidden" name="data[CustomerPayment][exchange_rate_id]" id="salesOrderExchangeRateIdReceive" />
            <?php
            $sqlMainCurrency = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_center_id FROM companies WHERE id = ".$companyId.")");
            $rowMainCurrency = mysql_fetch_array($sqlMainCurrency);
            $sqlCurrency = mysql_query("SELECT currency_centers.name, currency_centers.symbol, branch_currencies.rate_to_sell FROM branch_currencies INNER JOIN currency_centers ON currency_centers.id = branch_currencies.currency_center_id WHERE branch_currencies.branch_id = ".$branchId);
            if(mysql_num_rows($sqlCurrency)){
            ?>
            <table class="table" cellspacing="0" >
                <thead>
                    <tr>
                        <th class="first" style="width:100%;" colspan="2"><?php echo MENU_EXCHANGE_RATE_LIST; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($rowCurrency = mysql_fetch_array($sqlCurrency)){
                    ?>
                    <tr>
                        <td class="first" style="text-align:center; font-size: 12px; width: 25%;">1 <?php echo $rowMainCurrency[0]; ?> =</td>
                        <td style="font-size: 12px;"><?php echo number_format($rowCurrency['rate_to_sell'], 9); ?> <?php echo $rowCurrency['symbol']; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
            }
            ?>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<table class="table" cellspacing="0">
    <thead>
        <tr>
            <th class="first" colspan="11" style="text-align: center; font-size: 15px; font-weight: bold;"><?php echo MENU_TITLE_SHIFT; ?></th>
        </tr>
        <tr>
            <th class="first"><?php echo TABLE_NO; ?></th>
            <th><?php echo TABLE_CHANGE_SHIFT_CODE; ?></th>
            <th><?php echo TABLE_DATE_TIME_START; ?></th>
            <th><?php echo TABLE_DATE_TIME_END; ?></th>
            <th><?php echo TABLE_USER_SALE; ?></th>
            <th><?php echo TABLE_TOTAL_ACTURE_REGISTER; ?></th>
            <th><?php echo TABLE_TOTAL_ACTURE_REGISTER; ?></th>
            <th><?php echo TABLE_SHORT_CASE_IN_REGISTER; ?></th>
            <th><?php echo TABLE_SHORT_CASE_IN_REGISTER; ?></th>
            <th><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?></th>
            <th><?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $index=1;
        $totalAmount=0;
        $balance=0;
        $query=mysql_query("SELECT shifts.id,
                                   shifts.shift_code,
                                   shifts.date_start,
                                   shifts.date_end,
                                   shifts.created_by,
                                   CONCAT(users.first_name, ' ',users.last_name) AS user_name,
                                   IFNULL(shifts.total_register, 0) AS total_register,
                                   IFNULL(shifts.total_register_other, 0) AS total_register_other,
                                   SUM(IFNULL(shift_adjusts.total_adj, 0)) AS total_adj,
                                   SUM(IFNULL(shift_adjusts.total_adj_other, 0)) AS total_adj_other,
                                   IFNULL(shifts.total_acture, 0) AS total_acture,
                                   IFNULL(shifts.total_acture_other, 0) AS total_acture_other
                            FROM shifts 
                            LEFT JOIN exchange_rates ON exchange_rates.id = shifts.exchange_rate_id
                            LEFT JOIN shift_adjusts ON shift_adjusts.shift_id = shifts.id
                            INNER JOIN users ON users.id = shifts.created_by
                            WHERE shifts.status = 2
                                AND shifts.company_id=".$companyId." AND shifts.branch_id=".$branchId."
                                ".($userId!=0?' AND shifts.created_by='.$userId:'')."
                            GROUP BY shifts.id
                            ORDER BY shifts.date_start");
        if(mysql_num_rows($query)){
            $totalRegister      = 0;
            $totalRegisterOther = 0;
            $totalAdj           = 0;
            $totalAdjOther      = 0;
            $totalActure        = 0;
            $totalActureOther   = 0;
            while($data=mysql_fetch_array($query)){
                $totalRegister      += $data['total_register'];
                $totalRegisterOther += $data['total_register_other'];
                $totalAdj           += $data['total_adj'];
                $totalAdjOther      += $data['total_adj_other'];
                $totalActure        += $data['total_acture'];
                $totalActureOther   += $data['total_acture_other'];
        ?>
        <tr>
            <td class="first">
                <?php echo $index++; ?>
                <input type="hidden" name="id[]" value="<?php echo $data['id']; ?>" />
            </td>
            <td><?php echo $data['shift_code']; ?></td>
            <td><?php echo dateShort($data['date_start'], "d/m/Y H:i:s"); ?></td>
            <td><?php echo dateShort($data['date_end'], "d/m/Y H:i:s"); ?></td>
            <td><?php echo $data['user_name']; ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_acture'], 2); ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_acture_other'], 0); ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_register'], 2); ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_register_other'], 0); ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_adj'], 2); ?></td>
            <td style='text-align: right;'><?php echo number_format($data['total_adj_other'], 0); ?></td>
        </tr>
            <?php } ?>
        <tr>
            <td class="first" colspan="5" style="text-align: right;font-weight: bold;"><?php echo TABLE_TOTAL; ?></td>
            <td style='text-align: right;'><?php echo number_format($totalActure,2); ?></td>
            <td style='text-align: right;'><?php echo number_format($totalActureOther, 0); ?></td>
            <td style='text-align: right;'><?php echo number_format($totalRegister,2); ?></td>
            <td style='text-align: right;'><?php echo number_format($totalRegisterOther, 0); ?></td>
            <td style='text-align: right;'><?php echo number_format($totalAdj,2); ?></td>
            <td style='text-align: right;'><?php echo number_format($totalAdjOther, 0); ?></td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td colspan="14" class="dataTables_empty first"><?php echo TABLE_NO_MATCHING_RECORD; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php 
$queryTotalShift=mysql_query("SELECT
                                    IFNULL(exchange_rates.rate_to_sell, 0) AS rates,
                                    SUM(IFNULL(shifts.total_register, 0)) AS total_register,
                                    SUM(IFNULL(shifts.total_register_other, 0)) AS total_register_other,
                                    SUM(IFNULL(total_adj, 0))AS total_adj,
                                    SUM(IFNULL(total_adj_other, 0)) AS total_adj_other,
                                    SUM(IFNULL(shifts.total_acture, 0)) AS total_acture,
                                    SUM(IFNULL(shifts.total_acture_other, 0)) AS total_acture_other,
                                    SUM(IFNULL(shifts.total_sales, 0)) AS total_sales
                             FROM shifts 
                             LEFT JOIN exchange_rates ON exchange_rates.id = shifts.exchange_rate_id
                             LEFT JOIN shift_adjusts ON shift_adjusts.shift_id = shifts.id
                             WHERE shifts.status = 2 AND shifts.company_id=".$companyId." AND shifts.branch_id=".$branchId."
                                 ".($userId!=0?' AND shifts.created_by='.$userId:''));
if(mysql_num_rows($queryTotalShift)){
    $dataTotalShift     = mysql_fetch_array($queryTotalShift);
    $color              = "";
    $totalSpread        = 0;
    $totalSpreadOther   = 0;
    $totalRegisterOther = 0;
    $totalAdjustOther   = 0;
    $totalAcutreOther   = 0;
    if($dataTotalShift['rates'] > 0){
        $totalRegisterOther = ($dataTotalShift['total_register_other'] / $dataTotalShift['rates']);
    }
    if($dataTotalShift['rates'] > 0){
        $totalAdjustOther   = ($dataTotalShift['total_adj_other'] / $dataTotalShift['rates']);
    }
    if($dataTotalShift['rates'] > 0){
        $totalAcutreOther   = ($dataTotalShift['total_acture_other'] / $dataTotalShift['rates']);
    }
    $totalSalesAll      = $dataTotalShift['total_register'] + $totalRegisterOther + $dataTotalShift['total_adj'] + $totalAdjustOther + $dataTotalShift['total_sales'];
    if($dataTotalShift['total_acture'] != $totalSalesAll){
        $totalSpread    = ($dataTotalShift['total_acture'] + $totalAcutreOther) - $totalSalesAll;
        $color          = "color: red;";
    }
?>
<table style="width: 600px;" cellspacing="0">
    <tr>
        <td>
            <table class="table" cellspacing="0" >
                <thead>
                    <tr>
                        <th class="first" colspan="11" style="text-align: center; font-size: 15px; font-weight: bold;"><?php echo MENU_SHIFT_COLLECT; ?></th>
                    </tr>
                    <tr>
                        <th class="first" style='width: 30%;'></th>
                        <th style='text-align: right;'>$</th>
                        <th style='text-align: right;'>៛</th>
                        <th style='text-align: right;'><?php echo TABLE_TOTAL." ".TABLE_BASE_CURRENCY; ?> $</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="first">- <?php echo TABLE_TOTAL_ACTURE_REGISTER; ?></td>
                        <td style='text-align: right;'>
                            <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_acture'], 2) ?>" name="data[ShiftCollect][total_cash_collect]">
                            <?php echo number_format($dataTotalShift['total_acture'], 2) ?>
                        </td>
                        <td style='text-align: right;'>
                            <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_acture_other'], 2) ?>" name="data[ShiftCollect][total_cash_collect_other]">
                            <?php echo number_format($dataTotalShift['total_acture_other'], 0) ?>
                        </td>
                        <td style='text-align: right;'>
                            <?php 
                            echo number_format($dataTotalShift['total_acture'] + $totalAcutreOther, 2) 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="first">- <?php echo TABLE_SHORT_CASE_IN_REGISTER; ?></td>
                        <td style='text-align: right;'>
                            <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_register'], 2) ?>" name="data[ShiftCollect][total_register]">
                            <?php echo number_format($dataTotalShift['total_register'], 2) ?>
                        </td>
                        <td style='text-align: right;'>
                            <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_register_other'], 2) ?>" name="data[ShiftCollect][total_register_other]">
                            <?php echo number_format($dataTotalShift['total_register_other'], 0) ?>
                        </td>
                        <td style='text-align: right;'><?php echo number_format($dataTotalShift['total_register'] + $totalRegisterOther, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="first">- <?php echo TABLE_TOTAL_ADJUST_END_REGISTER; ?></td>
                        <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_adj'], 2) ?>" name="data[ShiftCollect][total_adj]">
                        <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_adj_other'], 2) ?>" name="data[ShiftCollect][total_adj_other]">
                        <td style='text-align: right;'><?php echo number_format($dataTotalShift['total_adj'], 2) ?></td>
                        <td style='text-align: right;'><?php echo number_format($dataTotalShift['total_adj_other'], 0) ?></td>
                        <td style='text-align: right;'><?php echo number_format($dataTotalShift['total_adj'] + $totalAdjustOther, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="first">- <?php echo TABLE_TOTAL_SALES_END_REGISTER; ?></td>
                        <td style='text-align: right;'>
                            <input type="hidden" class="float" value="<?php echo number_format($dataTotalShift['total_sales'], 2) ?>" name="data[ShiftCollect][total_sales]">
                            <?php echo number_format($dataTotalShift['total_sales'], 2) ?>
                        </td>
                        <td style='text-align: right;'>0</td>
                        <td style='text-align: right;'><?php echo number_format($dataTotalShift['total_sales'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="first" style="<?php echo $color; ?>" colspan="3">- <?php echo TABLE_TOTAL_SPREAD_REGISTER; ?></td>
                        <td style="<?php echo $color; ?> text-align: right;">
                            <input type="hidden" class="float" value="<?php echo number_format($totalSpread, 2) ?>" name="data[ShiftCollect][total_spread]">
                            <?php echo number_format($totalSpread, 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
<?php
}
?>