<?php echo $this->Form->create('Miscellaneous', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<table style="width: 100%;">
    <tr>
        <td style="width: 150px;"><label for="MiscellaneousDescription"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->text('description', array('class' => 'validate[required]')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 150px;"><label for="MiscellaneousUnitPrice"><?php echo TABLE_PRICE; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->text('unit_price', array('class' => 'validate[required] float', 'style' => 'width:100px')); ?>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#MiscellaneousMiscellaneousForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-layout-center"
        });
        $(".float").autoNumeric();
    });
</script>