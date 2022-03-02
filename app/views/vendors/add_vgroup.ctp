<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var vGroupRequestVendor = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<br />
<?php echo $this->Form->create('Vgroup');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Vgroup][company_id]" id="VgroupCompanyId" />
<?php
}
?>
<table style="width: 100%;">
    <?php
    if(count($companies) > 1){
    ?>
    <tr>
        <th><label for="VgroupCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></th>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->input('company_id', array('label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 95%; height: 25px;')); ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <th><label for="VgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></th>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>