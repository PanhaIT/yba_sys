<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<br />
<?php echo $this->Form->create('Cgroup'); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Cgroup][company_id]" id="CgroupCompanyId" />
<?php
}
?>
<table style="width: 100%;">
    <tr>
        <th><label for="CgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></th>
        <td><?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%; height: 25px;')); ?></td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>