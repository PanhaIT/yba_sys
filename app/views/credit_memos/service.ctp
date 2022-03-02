<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ServiceServiceId").change(function(){
            $("#ServiceSectionId").val($(this).find("option:selected").attr("class"));
            $("#ServiceUnitPrice").val($(this).find("option:selected").attr("price"));
        });
        $("#ServiceSectionId").change(function(){
            $("#ServiceServiceId").find("option").each(function(){
                if($(this).attr("class") == $("#ServiceSectionId").val()){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
        });

        var services = [];
        $("#tblSO").find("input[name='service_id[]']").each(function(){
            if($(this).val() != '' || $(this).val() != undefined){
                services.push($(this).val());
            }
        });

        $("#ServiceServiceId").find("option").each(function(){
            if($(this).val()!=''){
                if(in_array($(this).val(), services)){
                    $(this).remove();
                }
            }
        });
    });
    function in_array(needle, haystack)
    {
        for(var key in haystack)
        {
            if(needle === haystack[key])
            {
                return true;
            }
        }
        return false;
    }

</script>
<?php echo $this->Form->create('Service', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<table style="width: 100%;">
    <tr>
        <td style="width: 150px;"><label for="ServiceSectionId"><?php echo TABLE_SECTION; ?> <span class="red">*</span> :</label></td>
        <td>
            <?php echo $this->Form->input('section_id', array('empty' => INPUT_SELECT, 'class' => 'validate[required]')); ?>
        </td>
    </tr>
    <tr>
        <td style="width: 150px;"><label for="ServiceServiceId"><?php echo TABLE_SERVICE; ?> <span class="red">*</span> :</label></td>
        <td>
            <?php echo $this->Form->input('service_id', array('empty' => INPUT_SELECT, 'class' => 'validate[required]')); ?>
        </td>
    </tr>
    <tr>
        <td style="width: 150px;"><label for="ServiceUnitPrice"><?php echo SALES_ORDER_UNIT_PRICE; ?> <span class="red">*</span> :</label></td>
        <td>
            <?php echo $this->Form->text('unit_price', array('class' => 'validate[required]', 'readonly' => false)); ?>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>