<?php 
// Get Decimal
$sqlOption = mysql_query("SELECT product_cost_decimal FROM setting_options");
$rowOption = mysql_fetch_array($sqlOption);
// Form
echo $this->Form->create('Miscellaneous', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<table width="100%">
    <tr>
        <td style="width: 20%"><label for="MiscellaneousDescription"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->text('description', array('class' => 'validate[required]','style'=>'width:300px;')); ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 20%"><label for="MiscellaneousUnitPrice"><?php echo TABLE_PRICE; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->text('unit_price', array('class' => 'validate[required] float', 'style' => 'width:100px')); ?> $
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#MiscellaneousMiscellaneousForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-layout-center"
        });
        $(".float").autoNumeric({mDec: <?php echo $rowOption[0]; ?>, aSep: ','});
    });
</script>