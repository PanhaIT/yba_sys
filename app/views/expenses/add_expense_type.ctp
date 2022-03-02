<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
    });
</script>
<br />
<?php echo $this->Form->create('ExpenseType'); ?>
<table style="width: 100%;" cellpadding="5">
    <tr>
        <td><label for="ExpenseTypeAccountDescription"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('account_description', array('id' => 'ExpenseTypeAccountDescription', 'class' => 'validate[required]', 'name' => 'data[ExpenseType][account_description]', 'style' => 'width: 280px; height: 30px;')); ?>
            </div>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>