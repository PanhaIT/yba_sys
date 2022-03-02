<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#refreshAdjIssued").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewAdjustmentIssued/"+$("#filterBranchAdjustmentIssued").val(),
                beforeSend: function(){
                    $("#refreshAdjIssued").hide();
                    $("#loadingAdjIssued").show();
                    $("#adjIssuedView").html("Loading....");
                },
                success: function(result){
                    $("#refreshAdjIssued").show();
                    $("#loadingAdjIssued").hide();
                    $("#adjIssuedView").html(result);
                }
            });
        });
    });
</script>
<table style="width: 100%;">
    <tr>
        <td>Last Update: <span id="lastUpdateAdjIssued"><?php echo date("d/m/Y H:i:s"); ?></span></td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <th style="width: 30%;"><?php echo TABLE_COMPANY; ?></th>
        <th style="width: 23%;"><?php echo TABLE_DATE; ?></th>
        <th><?php echo TABLE_ADJ_NO; ?></th>
        <th style="width: 15%;"><?php echo TABLE_STATUS; ?></th>
    </tr>
    <?php
    if($branch != 'all'){
        $condition = "AND cycle_products.branch_id = ".$branch;
    } else {
        $condition = "AND cycle_products.branch_id IN (SELECT branch_id FROM user_branches WHERE user_id = {$user['User']['id']})";
    }
    $sqlHis = mysql_query("SELECT com.name AS com_name, cycle_products.reference AS code, cycle_products.date AS date FROM cycle_products INNER JOIN companies AS com ON com.id = cycle_products.company_id AND com.id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].") WHERE cycle_products.status = 1 ".$condition." ORDER BY cycle_products.created DESC LIMIT 15");
    if(mysql_num_rows($sqlHis)){
        $index = 1;
        while($rowHis = mysql_fetch_array($sqlHis)){
    ?>
    <tr>
        <td class="first"><?php echo $index; ?></td>
        <td><?php echo $rowHis['com_name']; ?></td>
        <td><?php echo dateShort($rowHis['date']); ?></td>
        <td><?php echo $rowHis['code']; ?></td>
        <td>Issued</td>
    </tr>
    <?php
            $index++;
        }
    } else {
    ?>
    <tr>
        <td colspan="5" class="first" style="text-align: center;"><?php echo TABLE_NO_RECORD; ?></td>
    </tr>
    <?php
    }
    ?>
</table>