<?php
include("includes/function.php");
// Authentication
$this->element('check_access');
$rand = rand();
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
    function loadDetail(){
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/receiveDetail/<?php echo $id; ?>",
            data: "",
            beforeSend: function(){
                $("#bodyDetail<?php echo $rand; ?>").hide();
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result){
                $("#bodyDetail<?php echo $rand; ?>").show();
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#bodyDetail<?php echo $rand; ?>").html(result);
            }
        });
    }
  
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        loadDetail();
        $(".btnBackPurchaseReceive").click(function(event){
            event.preventDefault();
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide( "slide", { direction: "right" }, 500, function() {
                leftPanel.show();
                rightPanel.html('');
            });
            leftPanel.html("<?php echo ACTION_LOADING; ?>");
            leftPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/1");
        });
        $("#date_receive_all").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy'
       }).unbind("blur");
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackPurchaseReceive">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="PurchaseReceiveReceiveAllForm" accept-charset="utf-8" method="post" action="<?php echo $this->webroot; ?>purchase_receives/receiveAll/">
<fieldset style="width:98%">
    <legend><?php __(MENU_INFO_PURCHASE_ORDER); ?></legend>
    <table cellpadding="5" width="100%">
        <tr>
            <td width="15%"><b><?php echo TABLE_PB_NUMBER; ?> :</b></td>
            <td width="20%"><?php echo $order['PurchaseOrder']['po_code']; ?></td>
            <td width="15%"><b><?php echo TABLE_PB_DATE; ?> :</b></td>
            <td><?php echo dateShort($order['PurchaseOrder']['order_date']); ?></td>
            <td width="10%"><b><?php echo MENU_VENDOR; ?> :</b></td>
            <td><?php echo $order['Vendor']['name']; ?></td>
        </tr>
        <tr>
            <td><b><?php echo TABLE_PB_RECEIVE_NUMBER; ?> :</b></td>
            <td><?php echo $code; ?></td>
            <td><b><?php echo TABLE_LOCATION_GROUP; ?> :</b></td>
            <td><?php echo $order['LocationGroup']['name']; ?></td>
            <td><b><?php echo TABLE_LOCATION; ?> :</b></td>
            <td><?php echo $order['Location']['name']; ?></td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td><b><?php echo TABLE_DATE_RECEIVE_APPLY_ALL; ?> :</b> <span class="red">*</span></td>
            <td>
                <div class="inputContainer" style="width:100%">
                    <input type="text" name="date_receive_all" value="<?php echo date("d/m/Y"); ?>" readonly="readonly" style="width:150px;" id="date_receive_all" class="validate[required]" />
                </div>
            </td>
            <td colspan="4"></td>
        </tr>
    </table>
</fieldset>
<div id="bodyDetail<?php echo $rand; ?>" style="margin-top:10px;">

</div>
</form>