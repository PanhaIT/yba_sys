<?php include('includes/function.php'); ?>
<?php echo $this->element('prevent_multiple_submit'); ?>
<style type="text/css">
    .reconcileTbl tr:hover {
        background-color: #F0F0F0;
    }
</style>
<script type="text/javascript">
    <?php
    $queryBeginningBalance=mysql_query("SELECT SUM(debit)-SUM(credit)
                                        FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                        WHERE is_active=1 AND is_approve=1 AND is_reconcile=1 AND company_id='" . $companyId . "' AND branch_id='" . $branchId . "' AND chart_account_id=" . $coaId);
    $dataBeginningBalance=mysql_fetch_array($queryBeginningBalance);
    ?>
    var BB=Number("<?php echo $dataBeginningBalance[0]; ?>");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ReconcileBB").html(BB);
        $("#ReconcileServiceCharge").val("");
        $("#ReconcileInterestEarn").val("");
        $("#ReconcileEB").val("");
        $("#ReconcileCB").text("");
        $("#ReconcileDiffHidden").val("");
        $("#ReconcileDiff").text("");
        $("#ReconcileForm").validationEngine('detach');
        $("#ReconcileForm").validationEngine('attach');
        $("#ReconcileForm").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
        $("#ReconcileForm").ajaxForm({
            beforeSerialize: function($form, options) {
                
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveReconcile").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".txtSaveReconcile").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                loadTableReconcile();
                $("button[type=submit]", $("#ReconcileForm")).removeAttr('disabled');
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Reconcile', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Reconcile', 'Add', 1, '');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
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
        $("#reconcileChkDebit").click(function(){
            $('.reconcileChkDebit').attr('checked', $(this).attr('checked'));
            $("#reconcileTotalDebit").text(reconcileCalcDebit().toFixed(2));
            reconcileCalcDiff();
        });
        $("#reconcileChkCredit").click(function(){
            $('.reconcileChkCredit').attr('checked', $(this).attr('checked'));
            $("#reconcileTotalCredit").text(reconcileCalcCredit().toFixed(2));
            reconcileCalcDiff();
        });
        $(".reconcileChkDebit").click(function(){
            $("#reconcileTotalDebit").text(reconcileCalcDebit().toFixed(2));
            reconcileCalcDiff();
        });
        $(".reconcileChkCredit").click(function(){
            $("#reconcileTotalCredit").text(reconcileCalcCredit().toFixed(2));
            reconcileCalcDiff();
        });
        $("#ReconcileServiceCharge").keyup(function(){
            reconcileCalcDiff();
        });
        $("#ReconcileInterestEarn").keyup(function(){
            reconcileCalcDiff();
        });
        $("#ReconcileEB").keyup(function(){
            reconcileCalcDiff();
        });
    });
    function reconcileCalcDebit(){
        var total=0;
        $('.reconcileChkDebit').each(function(){
            if($(this).attr('checked')){
                total+=Number($(this).closest("tr").find("td:last-child").text().replace(/,/g, ""));
            }
        });
        return total;
    }
    function reconcileCalcCredit(){
        var total=0;
        $('.reconcileChkCredit').each(function(){
            if($(this).attr('checked')){
                total+=Number($(this).closest("tr").find("td:last-child").text().replace(/,/g, ""));
            }
        });
        return total;
    }
    function reconcileCalcDiff(){
        var totalDebit=$("#reconcileTotalDebit").text();
        var totalCredit=$("#reconcileTotalCredit").text();
        var serviceCharge=$("#ReconcileServiceCharge").val();
        var interestEarn=$("#ReconcileInterestEarn").val();

        $("#ReconcileCB").text(Number(BB)+Number(totalDebit)-Number(totalCredit)-Number(serviceCharge)+Number(interestEarn));
        $("#ReconcileDiffHidden").val(Number($("#ReconcileEB").val())-Number($("#ReconcileCB").text()));
        $("#ReconcileDiff").text(Number($("#ReconcileEB").val())-Number($("#ReconcileCB").text()));
        
        if($("#ReconcileServiceCharge").val()!="" && $("#ReconcileServiceCharge").val()!="0"){
            $("#ReconcileServiceChargeDate").attr("class",'class="validate[required]"');
            $("#ReconcileServiceChargeChartAccountId").attr("class",'class="validate[required]"');
        }else{
            $("#ReconcileServiceChargeDate").attr("class",'class="validate[optional]"');
            $("#ReconcileServiceChargeChartAccountId").attr("class",'class="validate[optional]"');
        }
        if($("#ReconcileInterestEarn").val()!="" && $("#ReconcileInterestEarn").val()!="0"){
            $("#ReconcileInterestEarnDate").attr("class",'class="validate[required]"');
            $("#ReconcileInterestEarnChartAccountId").attr("class",'class="validate[required]"');
        }else{
            $("#ReconcileInterestEarnDate").attr("class",'class="validate[optional]"');
            $("#ReconcileInterestEarnChartAccountId").attr("class",'class="validate[optional]"');
        }
        if($("#ReconcileDiffHidden").val()!="" && $("#ReconcileDiffHidden").val()!="0"){
            $("#ReconcileDiffChartAccountId").attr("class",'class="validate[required]"');
        }else{
            $("#ReconcileDiffChartAccountId").attr("class",'class="validate[optional]"');
        }
    }
</script>
<table style="width: 100%;">
    <tr>
        <td style="padding: 10px;width: 50%;vertical-align: top;">
            <table class="table_report reconcileTbl">
                <tr>
                    <th style="width: 20px !important;"><input type="checkbox" id="reconcileChkDebit" /></th>
                    <th style="width: 100px !important;">Date</th>
                    <th style="width: 100px !important;">Ref</th>
                    <th>Name</th>
                    <th style="width: 160px !important;">Amount</th>
                </tr>
                <?php
                $query=mysql_query("SELECT gld.id,date,reference,debit
                                    FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1 AND is_approve=1 AND is_reconcile=0 AND debit>0 " . ($hideFutureValue=="true"?"AND date<='" . $date . "'":"") . " AND company_id='" . $companyId . "' AND branch_id='" . $branchId . "' AND chart_account_id=" . $coaId);
                while($data=mysql_fetch_array($query)){
                ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="hidden" name="general_ledger_detail_id[]" value="<?php echo $data['id']; ?>" />
                        <input type="checkbox" name="is_reconcile[]" class="reconcileChkDebit" />
                    </td>
                    <td><?php echo dateShort($data['date']); ?></td>
                    <td><?php echo $data['reference']; ?></td>
                    <td></td>
                    <td style="text-align: right;"><?php echo number_format($data['debit'],2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="4" style="font-weight: bold;">TOTAL</td>
                    <td id="reconcileTotalDebit" style="font-weight: bold;text-align: right;">0.00</td>
                </tr>
            </table>
        </td>
        <td style="padding: 10px;border-left: 1px solid #aaa;vertical-align: top;">
            <table class="table_report reconcileTbl">
                <tr>
                    <th style="width: 20px !important;"><input type="checkbox" id="reconcileChkCredit" /></th>
                    <th style="width: 100px !important;">Date</th>
                    <th style="width: 100px !important;">Ref</th>
                    <th>Name</th>
                    <th style="width: 160px !important;">Amount</th>
                </tr>
                <?php
                $query=mysql_query("SELECT gld.id,date,reference,credit
                                    FROM general_ledgers gl INNER JOIN general_ledger_details gld ON gl.id=gld.general_ledger_id
                                    WHERE is_active=1 AND is_approve=1 AND is_reconcile=0 AND credit>0 " . ($hideFutureValue=="true"?"AND date<='" . $date . "'":"") . " AND company_id='" . $companyId . "' AND branch_id='" . $branchId . "' AND chart_account_id=" . $coaId);
                while($data=mysql_fetch_array($query)){
                ?>
                <tr>
                    <td style="text-align: center;">
                        <input type="hidden" name="general_ledger_detail_id[]" value="<?php echo $data['id']; ?>" />
                        <input type="checkbox" name="is_reconcile[]" class="reconcileChkCredit" />
                    </td>
                    <td><?php echo dateShort($data['date']); ?></td>
                    <td><?php echo $data['reference']; ?></td>
                    <td></td>
                    <td style="text-align: right;"><?php echo number_format($data['credit'],2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="4" style="font-weight: bold;">TOTAL</td>
                    <td id="reconcileTotalCredit" style="font-weight: bold;text-align: right;">0.00</td>
                </tr>
            </table>
        </td>
    </tr>
</table>