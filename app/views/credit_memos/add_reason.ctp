<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<br />
<?php echo $this->Form->create('Reason'); ?>
<table style="width: 100%;">
    <tr>
        <td><label for="ReasonName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'style' => 'width: 90%; height: 25px;')); ?>
            </div>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>