<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".integer").autoNumeric({mDec: 0, aSep: ''});
    });
</script>
<br />
<?php echo $this->Form->create('PaymentTerm'); ?>
<table style="width: 100%;">
    <tr>
        <td style="width: 20%;"><label for="PaymentTermName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'style' => 'width: 90%; height: 25px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="PaymentTermName"><?php echo TABLE_NET_DAYS; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('net_days', array('class'=>'validate[required] integer', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>