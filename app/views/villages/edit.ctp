<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#VillageEditForm").validationEngine();
        $("#VillageEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVillage").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVillage").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Village', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Village', 'Edit', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
        $("#VillageProvinceId").live("change", function(){
            var province_id = $(this).val();
            if(province_id == ''){
                $("#VillageDistrictId").find("option").show();
                $("#VillageCommuneId").find("option").show();
            }else{
                $("#VillageDistrictId").find("option").each(function(){
                    if($(this).attr("class")== province_id){
                        $(this).show();
                    }else{
                        $(this).hide();
                    }
                });
            }
            $("#VillageDistrictId").val("");
        });
        $("#VillageDistrictId").live("change", function(){
            var district_id = $(this).val();
            var province_id = $(this).find("option:selected").attr("class");
            $("#VillageProvinceId").val(province_id);
            $("#VillageCommuneId").find("option").each(function(){
                if($(this).attr("class")== district_id){
                    $(this).show();
                }else{
                    if($(this).val()!=''){
                        $(this).hide();
                    }
                }
            });
            $("#VillageCommuneId").val("");
        });
        $("#VillageCommuneId").find("option").each(function(){
            if($(this).val()!=''){
                $(this).hide();
            }
        });
        $("#VillageDistrictId").val($("#VillageCommuneId").find("option:selected").attr("class"));
        $("#VillageProvinceId").val($("#VillageDistrictId").find("option:selected").attr("class"));
        $(".btnBackVillage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVillage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVillage">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Village');
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<fieldset>
    <legend><?php __(VILLAGE_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="VillageProvinceId"><?php echo TABLE_PROVINCE; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('province_id', array('empty' => INPUT_SELECT, 'label' => false, 'class' => 'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="VillageDistrictId"><?php echo TABLE_DISTRICT; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('district_id', array('empty' => INPUT_SELECT, 'label' => false, 'class' => 'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="VillageCommuneId"><?php echo TABLE_COMMUNE; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('commune_id', array('empty' => INPUT_SELECT, 'label' => false, 'class' => 'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="VillageName"><?php echo TABLE_VILLAGE; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveVillage"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>