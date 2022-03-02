<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var totalPaidPbc = converDicemalJS(<?php echo $balance; ?>);
    $(document).ready(function(){
        $("#applyInvoicePBCForm").validationEngine();
        $(".table td:first-child").addClass('first');
        var oTableInvoicePR = $("#<?php echo $tblName; ?>").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/invoiceAjax/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $chartAccountId; ?>/<?php echo $vendorId; ?>/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $("input[name='chkInvoicePO[]']").click(function(){
                    var thisCheck = $(this);
                    var totalAmount = parseFloat($(this).closest("tr").find(".total_balance_pbc").val());
                    if (thisCheck.is (':checked')){
                        if(parseFloat(getTotalPaid()) < parseFloat(totalPaidPbc)){
                            thisCheck.closest("tr").find("input[name='invoice_price_pbc[]']").val(totalAmount);
                            thisCheck.closest("tr").find("input[name='invoice_price_pbc[]']").removeAttr('readonly');
                        }else{
                            thisCheck.removeAttr('checked');
                        }
                    }else{
                        thisCheck.closest("tr").find("input[name='invoice_price_pbc[]']").attr('readonly','readonly');
                        thisCheck.closest("tr").find("input[name='invoice_price_pbc[]']").val(0);
                    }
                    checkValidPaid($(this).closest("tr").find("input[name='invoice_price_pbc[]']"));
                });
                $("input[name='invoice_price_pbc[]']").focus(function(){
                    var val  = $(this).val();
                    var attr = $(this).attr('readonly');
                    if(attr == false){
                        if(val == "0"){
                            $(this).val("");
                        }
                    }
                });
                $("input[name='invoice_price_pbc[]']").blur(function(){
                    checkValidPaid($(this));
                });
                return sPre;
            },
            "aoColumnDefs": [{
                "sType": "numeric", "aTargets": [ 0 ],
                "bSortable": false, "aTargets": [ 0,-1,-2 ]
            }],
            "aaSorting": [[ 2, "asc" ]]
        });
        $('#invoiceDateApplyPBC').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
    });
    function getTotalPaid(){
        var totalPaid = 0;
        $("input[name='invoice_price_pbc[]']").each(function(){
            totalPaid += parseFloat($(this).val());
        });
        return totalPaid;
    }
    
    function checkValidPaid(current){
        var currentVal = parseFloat(current.val());
        var totalBalance = parseFloat(current.closest("tr").find(".total_balance_pbc").val());
        var val = 0;
        if(parseFloat(getTotalPaid()) > parseFloat(totalPaidPbc)){
            val = converDicemalJS(currentVal - (converDicemalJS(parseFloat(getTotalPaid()) - parseFloat(totalPaidPbc))));
            if(val > totalBalance){
                current.val(totalBalance);
            }else{
                current.val(val);
            }
        }else{
            if(currentVal > totalBalance){
                current.val(totalBalance);
            }
        }
        putTotalBalance();
    }
    
    function putTotalBalance(){
        var totalBalance = converDicemalJS(totalPaidPbc - getTotalPaid());
        $("#total_balance_pbc").html(totalBalance);
    }
</script>
<div id="dynamic">
    <div style="float:left;">
        <b><?php echo BILL_RETURN_TOTAL_BALANCE; ?>: <span id="total_balance_pbc"><?php echo $this->data['PurchaseReturn']['balance']; ?></span> $</b> 
    </div>
    <div style="float:right;">
        <form id="applyInvoicePBCForm" style="float: left;">
            <?php echo TABLE_DATE; ?> <span class="red">*</span> :
            <input type="text" id="invoiceDateApplyPBC" style="width:150px;" readonly="readonly" class="validate[required]" />
        </form>
    </div>
    <div class="clear"></div>
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"></th>
                <th style="width: 10%;"><?php echo TABLE_ORDER_DATE; ?></th>
                <th style="width: 12%;"><?php echo TABLE_PO_NUMBER; ?></th>
                <th style="width: 12%;"><?php echo TABLE_LOCATION; ?></th>
                <th style="width: 10%;"><?php echo TABLE_VENDOR; ?></th>
                <th style="width: 10%;"><?php echo TABLE_TOTAL_AMOUNT; ?> <? echo TABLE_CURRENCY_DEFAULT; ?></th>
                <th style="width: 10%;"><?php echo GENERAL_BALANCE; ?> <? echo TABLE_CURRENCY_DEFAULT; ?></th>
                <th><?php echo GENERAL_PAID; ?></th>
                <th style="width: 20%;"><?php echo TABLE_STATUS; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="9" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>