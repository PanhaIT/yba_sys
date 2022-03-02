<?php
// Authentication
include("includes/function.php");
$this->element('check_access');
$rand = rand();
//$allowAdd=checkAccess($user['User']['id'], 'purchase_receives', 'receive');
?>
<style type="text/css">
    .disabled{
        background: #f5f5f5;
        color: #565656;
        width: auto;
        height: auto;
        border: 1px #E2E4FF solid;
    }
    .disabled:hover{
        background: #f5f5f5;
        border-right: 1px #dedede solid;
        border-bottom: 1px #dedede solid;
        border-top: 1px #eeeeee solid;
        border-left: 1px #eeeeee solid;
        color: #565656;
        cursor: auto;
    }
</style>
<script type="text/javascript">
    var intervalReceiveAll;
    var id = "<?php echo $id; ?>";
    function loadToDetail(){
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/receiveDetail/"+id,
            data: "",
            beforeSend: function(){
                $("#receiveToDetail<?php echo $rand; ?>").hide();
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result){
                $("#receiveToDetail<?php echo $rand; ?>").show();
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#receiveToDetail<?php echo $rand; ?>").html(result);
            }
        });
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        loadToDetail();
        // Action Click Receive
        $("#tabs").bind("tabsselect", function(event, ui) {
            loadToDetail();
        });
        $("#transferReceiveDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        $("#transferReceiveDate").datepicker("option", "minDate", "<?php echo date("d/m/Y", strtotime($order['TransferOrder']['order_date'])); ?>");
        $("#transferReceiveDate").datepicker("option", "maxDate", "<?php echo date('d/m/Y'); ?>");
        $(".btnBackTOReceive").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                clearInterval(intervalReceiveAll);
                leftPanel.show();
                oCache.iCacheLower = -1;
                oReceiveTOTable.fnDraw(false);
                rightPanel.html("");
            });
        });
    });
</script>
<?php
    //debug($vendor);
?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackTOReceive">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="TransferReceiveAllForm" accept-charset="utf-8" method="post" action="<?php echo $this->webroot; ?>transfer_receives/receiveAll/">
<fieldset style="width:98%">
    <legend><?php __(MENU_TO_RECEIVE_MANAGEMENT_INFO); ?></legend>
<table cellpadding="5" width="100%">
    <tr>
        <td width="10%"><b><?php echo TABLE_TO_NUMBER; ?> :</b></td>
        <td><?php echo $order['TransferOrder']['to_code']; ?></td>
        <td width="10%"><b><?php echo TABLE_TO_DATE; ?> :</b></td>
        <td><?php echo date("d/m/Y", strtotime($order['TransferOrder']['order_date'])); ?></td>
        <td width="14%"><b><?php echo TABLE_TRANSFER_RECEIVE_NO; ?> <span class="red">*</span>:</b></td>
        <td><input type="text" style="width: 170px;" value="<?php echo $code; ?>" readonly="readonly" id="transferReceiveNo" class="validate[required]" name="data[transfer_receive_no]" /></td>
    </tr>
    <tr>
        <td><b><?php echo TABLE_FROM_WAREHOUSE; ?> :</b></td>
        <td>
        <?php 
            foreach($locationGroup as $loc){ 
               if($loc['LocationGroup']['id'] == $order['TransferOrder']['from_location_group_id']){
                   echo $loc['LocationGroup']['name'];
               }
            }
        ?>
        </td>
        <td><b><?php echo TABLE_FULFILLMENT_DATE; ?> :</b></td>
        <td>
        <?php
            if($order['TransferOrder']['fulfillment_date'] != null && $order['TransferOrder']['fulfillment_date'] != "" && $order['TransferOrder']['fulfillment_date'] != '0000-00-00'){
                echo date("d/m/Y", strtotime($order['TransferOrder']['fulfillment_date']));
            }
        ?>
        </td>
        <td width="14%"><b><?php echo TABLE_TRANSFER_RECEIVE_DATE; ?> <span class="red">*</span>:</b></td>
        <td><input type="text" style="width: 170px;" value="<?php echo date("d/m/Y"); ?>" readonly="readonly" id="transferReceiveDate" class="validate[required]" name="data[transfer_receive_date]" readonly="readonly" /></td>
    </tr>
    <tr>
        <td width="10%"><b><?php echo TABLE_TO_WAREHOUSE; ?> :</b></td>
        <td>
        <?php 
            foreach($locationGroup as $loc){ 
               if($loc['LocationGroup']['id'] == $order['TransferOrder']['to_location_group_id']){
                   echo $loc['LocationGroup']['name'];
               }
            }
        ?>
        </td>
    </tr>
</table>
</fieldset>
<div id="receiveToDetail<?php echo $rand; ?>" style="margin-top:10px;">
    
</div>
</form>
