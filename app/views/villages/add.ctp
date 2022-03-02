<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowVillage = 0;
    var rowVillageList  =  $("#rowVillage");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowVillage").remove();
        $("#VillageAddForm").validationEngine();
        $("#VillageAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVillage").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVillage").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Village', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Village', 'Add', 1, '');
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
        $(".btnBackVillage").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableVillage.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        // Clone Row Village List
        cloneCommuneRow();
    });
    
    function cloneCommuneRow(){
        if($(".rowVillage:last").find(".name").attr("id") == undefined){
            indexRowVillage = 1;
        }else{
            indexRowVillage = parseInt($(".rowVillage:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var tr    = rowVillageList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .name").val('');
        tr.find("td .name").attr("id", "name_"+indexRowVillage);
        $("#tblVillage").append(tr);
        var LenTr = parseInt($(".rowVillage").length);
        if(LenTr == 1){
            $("#tblVillage").find("tr:eq("+LenTr+")").find(".btnAddVillageRow").show();
            $("#tblVillage").find("tr:eq("+LenTr+")").find(".btnRemoveVillage").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowLocation();
    }
    
    function eventKeyRowLocation(){
        $(".name, .btnAddVillageRow, .btnRemoveVillage").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".name").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".btnAddVillageRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveVillage").show();
            cloneCommuneRow();
        });
        $(".btnRemoveVillage").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowVillage").length);
                        if(lenTr == 1){
                            $("#tblVillage").find("tr:eq("+lenTr+")").find("td .btnRemoveVillage").hide();
                        }
                        $("#tblVillage").find("tr:eq("+lenTr+")").find("td .btnAddVillageRow").show();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
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
<?php echo $this->Form->create('Village'); ?>
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
    </table>
    <table id="tblVillage" class="table" style="width: 50%;">
        <tr>
            <th class="first" style="width: 70%;"><?php echo TABLE_VILLAGE; ?></th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowVillage" class="rowVillage" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="name[]" style="width: 90%;" id="name" class="name validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddVillageRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveVillage" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
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