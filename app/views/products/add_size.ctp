<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<br />
<?php echo $this->Form->create('Size'); ?>
<table style="width: 100%;" cellpadding="5">
    <tr>
        <td><label for="SizeName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('id' => 'SizeName', 'class' => 'validate[required]', 'name' => 'data[Size][name]', 'style' => 'width: 280px; height: 30px;')); ?>
            </div>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>