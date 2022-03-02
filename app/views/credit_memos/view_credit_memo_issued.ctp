<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#refreshCreditMemoIssued").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewCreditMemoIssued/",
                beforeSend: function(){
                    $("#refreshCreditMemoIssued").hide();
                    $("#loadingCreditMemoIssued").show();
                    $("#creditMemoIssuedView").html("Loading....");
                },
                success: function(result){
                    $("#refreshCreditMemoIssued").show();
                    $("#loadingCreditMemoIssued").hide();
                    $("#creditMemoIssuedView").html(result);
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
        <th style="width: 23%;"><?php echo TABLE_CREDIT_MEMO_DATE; ?></th>
        <th><?php echo TABLE_CREDIT_MEMO_NUMBER; ?></th>
        <th style="width: 15%;"><?php echo TABLE_STATUS; ?></th>
    </tr>
    <?php
    $sqlHis = mysql_query("SELECT com.name AS com_name, credit_memos.cm_code AS code, credit_memos.order_date AS date, credit_memos.status AS status FROM credit_memos INNER JOIN companies AS com ON com.id = credit_memos.company_id AND com.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE credit_memos.status = 1 ORDER BY credit_memos.created DESC LIMIT 15");
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