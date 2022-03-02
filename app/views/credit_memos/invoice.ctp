<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/pipeline.js"></script>
<script type="text/javascript">
    var totalPaidCM = converDicemalJS(<?php echo $balance; ?>);
    $(document).ready(function(){
        $("#applyInvoiceCMForm").validationEngine();
        $(".table td:first-child").addClass('first');
        var oTableInvoice = $("#<?php echo $tblName; ?>").dataTable({
            "iDisplayLength": 10000000,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo $this->base . '/' . $this->params['controller']; ?>/invoiceAjax/<?php echo $chartAccountId; ?>/<?php echo $companyId; ?>/<?php echo $branchId; ?>/<?php echo $customerId; ?>/",
            "fnServerData": fnDataTablesPipeline,
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                $(".float").autoNumeric({mDec: 3});
                $("#dialog").dialog("option", "position", "center");
                $(".table td:first-child").addClass('first');
                $(".table td:last-child").css("white-space", "nowrap");
                $("input[name='chkInvoice[]']").click(function(){
                    var thisCheck = $(this);
                    var totalAmount = parseFloat($(this).closest("tr").find(".total_amount").val());
                    if (thisCheck.is (':checked')){
                        if(parseFloat(getTotalPaid()) < parseFloat(totalPaidCM)){
                            thisCheck.closest("tr").find("input[name='invoice_price[]']").val(totalAmount);
                            thisCheck.closest("tr").find("input[name='invoice_price[]']").removeAttr('readonly');
                        }else{
                            thisCheck.removeAttr('checked');
                        }
                    }else{
                        thisCheck.closest("tr").find("input[name='invoice_price[]']").attr('readonly','readonly');
                        thisCheck.closest("tr").find("input[name='invoice_price[]']").val(0);
                    }
                    checkValidPaid($(this).closest("tr").find("input[name='invoice_price[]']"));
                });
                $("input[name='invoice_price[]']").focus(function(){
                    var val  = $(this).val();
                    var attr = $(this).attr('readonly');
                    if(attr == false){
                        if(val == "0"){
                            $(this).val("");
                        }
                    }
                });
                $("input[name='invoice_price[]']").blur(function(){
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
        $("#changeCusGroup").change(function(){
            var valueId = $(this).val();
            var Tablesetting = oTableInvoice.fnSettings();
            Tablesetting.sAjaxSource = "<?php echo $this->base . '/' . $this->params['controller']; ?>/invoiceAjax/<?php echo $chartAccountId; ?>/<?php echo $companyId; ?>/<?php echo $customerId; ?>/"+valueId;
            oCache.iCacheLower = -1;
            oTableInvoice.fnDraw(false);
        });
        $('#invoiceDateApplyCM').datepicker({
            dateFormat:'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
    });
    
    function getTotalPaid(){
        var totalPaid = 0;
        $("input[name='invoice_price[]']").each(function(){
            totalPaid += parseFloat($(this).val());
        });
        return totalPaid;
    }
    
    function checkValidPaid(current){
        var currentVal = parseFloat(current.val());
        var totalAmount = parseFloat(current.closest("tr").find(".total_amount").val());
        var val = 0;
        if(parseFloat(getTotalPaid()) > parseFloat(totalPaidCM)){
            val = converDicemalJS(currentVal - (converDicemalJS(parseFloat(getTotalPaid()) - parseFloat(totalPaidCM))));
            if(val > totalAmount){
                current.val(totalAmount);
            }else{
                current.val(val);
            }
        }else{
            if(currentVal > totalAmount){
                current.val(totalAmount);
            }
        }
        putTotalBalance();
    }
    
    function putTotalBalance(){
        var totalBalance = converDicemalJS(totalPaidCM - getTotalPaid());
        $("#total_balance_cm").html(totalBalance);
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb; margin-bottom: 5px;">
    <div style="float:left;">
        <b>Credit Memo Total Balance: <span id="total_balance_cm"><?php echo $this->data['CreditMemo']['balance']; ?></span> $</b> 
    </div>
    <div style="float:right;">
        <form id="applyInvoiceCMForm" style="float: left;">
            <?php echo TABLE_DATE; ?> <span class="red">*</span> :
            <input type="text" id="invoiceDateApplyCM" style="width:150px;" readonly="readonly" class="validate[required]" />
        </form>
        <?php echo GENERAL_GROUP; ?> :
        <select id="changeCusGroup" style="width:150px;">
            <option value=""><?php echo TABLE_ALL; ?></option>
            <?php
            $queryCg=mysql_query("SELECT * FROM cgroups WHERE is_active=1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))");
            while($dataCg=mysql_fetch_array($queryCg)){
            ?>
            <option value="<?php echo $dataCg['id']; ?>"><?php echo $dataCg['name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="dynamic">
    <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
        <thead>
            <tr>
                <th class="first"></th>
                <th><?php echo TABLE_INVOICE_DATE; ?></th>
                <th><?php echo TABLE_INVOICE_CODE; ?></th>
                <th><?php echo TABLE_CUSTOMER; ?></th>
                <th><?php echo TABLE_TOTAL; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                <th><?php echo GENERAL_BALANCE; ?> <?php echo TABLE_CURRENCY_DEFAULT; ?></th>
                <th><?php echo GENERAL_PAID; ?></th>
                <th><?php echo TABLE_TYPE; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="dataTables_empty"><?php echo TABLE_LOADING; ?></td>
            </tr>
        </tbody>
    </table>
</div>