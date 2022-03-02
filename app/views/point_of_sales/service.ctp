<style type="text/css">select{width: 250px;}</style>
<script type="text/javascript">
    $(document).ready(function(){        
        $(".chzn-select").chosen({ width: 250});
        $("#ServiceServiceId").unbind('change').change(function(){
            var sectionId = $(this).find("option:selected").attr("section");
            $("#ServiceUnitPrice").val($(this).find("option:selected").attr("price"));
            $("#ServiceCode").val($(this).find("option:selected").attr("code"));
            $("#ServiceUomId").val($(this).find("option:selected").attr("uom-name"));
            $("#ServiceUomSerId").val($(this).find("option:selected").attr("uom-id"));
            $("#ServiceSectionId").find("option").removeAttr('selected');
            $("#ServiceSectionId").find("option[value='"+sectionId+"']").attr('selected', true);
            $("#ServiceSectionId").trigger("chosen:updated");
        });
        
        $("#ServiceSectionId").unbind('change').change(function(){
            $("#ServiceServiceId").filterOptions('section', $(this).val(), '');
            $("#ServiceServiceId").trigger("chosen:updated");
            $("#ServiceUnitPrice").val(0);
            $("#ServiceCode").val('');
            $("#ServiceUomId").val('');
        });
    });
</script>
<?php echo $this->Form->create('Service', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" id="ServiceUomSerId" value="0" />
<table style="width: 100%;">
    <tr>
        <td style="width: 30%;"><?php echo TABLE_SECTION; ?><span class="red">*</span>:</td>
        <td><?php echo $this->Form->input('section_id', array('empty' => INPUT_SELECT, 'class' => 'chzn-select')); ?></td>
        <td style="width: 20%; text-align: center;"><label id="lblSection" style="color: red; display: none"> (*require)</label></td>
    </tr>
    <tr>
        <td><?php echo TABLE_SERVICE; ?> <span class="red">*</span>:</td><td><?php echo $this->Form->input('service_id', array('empty' => INPUT_SELECT, 'class' => 'chzn-select')); ?></td>
        <td style="text-align: center;"><label id="lblService" style="color: red; display: none"> (*require)</label></td>
    </tr>
    <tr>
        <td><?php echo TABLE_CODE; ?> <span class="red">*</span>:</td>
        <td><?php echo $this->Form->text('code', array('style'=>'width: 96%', 'class' => 'textAlignLeft')); ?></td>
        <td style="text-align: center;"><label id="lblCode" style="color: red; display: none"> (*require)</label></td>
    </tr>
    <tr>
        <td><?php echo TABLE_UOM; ?> <span class="red">*</span>:</td>
        <td><?php echo $this->Form->text('uom_id', array('style'=>'width: 96%', 'class' => 'textAlignLeft')); ?></td>
        <td style="text-align: center;"><label id="lblUom" style="color: red; display: none"> (*require)</label></td>
    </tr>
    <tr>
        <td><?php echo SALES_ORDER_UNIT_PRICE; ?> <span class="red">*</span>:</td>
        <td><?php echo $this->Form->text('unit_price', array('style'=>'width: 96%', 'class' => 'textAlignLeft')); ?></td>
        <td style="text-align: center;"><label id="lblUnitPrice" style="color: red; display: none"> (*require)</label></td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>