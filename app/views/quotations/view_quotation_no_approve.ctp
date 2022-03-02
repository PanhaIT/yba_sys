<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#refreshQuotationNoApprove").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewQuotationNoApprove/",
                beforeSend: function(){
                    $("#refreshQuotationNoApprove").hide();
                    $("#loadingQuotationNoApprove").show();
                    $("#quotationNoApproveView").html("Loading....");
                },
                success: function(result){
                    $("#refreshQuotationNoApprove").show();
                    $("#loadingQuotationNoApprove").hide();
                    $("#quotationNoApproveView").html(result);
                }
            });
        });
    });
</script>
<table style="width: 100%;">
    <tr>
        <td>Last Update: <span id="lastUpdateChangeCost"><?php echo date("d/m/Y H:i:s"); ?></span></td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 30%;"><?php echo TABLE_COMPANY; ?></th>
        <th style="width: 23%;"><?php echo TABLE_QUOTATION_DATE; ?></th>
        <th><?php echo TABLE_QUOTATION_NUMBER; ?></th>
        <th style="width: 15%;"><?php echo TABLE_STATUS; ?></th>
    </tr>
    <?php
    $sqlHis = mysql_query("SELECT com.name AS com_name, quotations.quotation_code AS code, quotations.quotation_date AS date, quotations.status AS status FROM quotations INNER JOIN companies AS com ON com.id = quotations.company_id AND com.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE quotations.is_approve = 0 AND quotations.status = 1 ORDER BY quotations.created DESC LIMIT 15");
    if(mysql_num_rows($sqlHis)){
        $index = 1;
        while($rowHis = mysql_fetch_array($sqlHis)){
    ?>
    <tr>
        <td class="first"><?php echo $index; ?></td>
        <td><?php echo $rowHis['com_name']; ?></td>
        <td><?php echo dateShort($rowHis['date']); ?></td>
        <td><?php echo $rowHis['code']; ?></td>
        <td>
            No Approving
        </td>
    </tr>
    <?php
            $index++;
        }
    } else {
    ?>
    <tr>
        <td colspan="5" class="first"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>