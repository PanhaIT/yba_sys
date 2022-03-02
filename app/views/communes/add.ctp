<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowCommune = 0;
    var rowCommuneList  =  $("#rowCommune");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowCommune").remove();
        $("#CommuneAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#CommuneAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveCommune").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCommune").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Commune', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Commune', 'Add', 1, '');
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
        $("#CommuneDistrictId").live("change", function(){
            var province_id = $(this).find("option:selected").attr("class");
            $("#CommuneProvinceId").val(province_id);
        });
        $("#CommuneProvinceId").live("change", function(){
            var province_id = $(this).val();
            $("#CommuneDistrictId").find("option").each(function(){
                if($(this).attr("class")== province_id){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
            $("#CommuneDistrictId").val("");
        });
        $(".btnBackCommune").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCommune.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        // Clone Row Commune List
        cloneCommuneRow();
    });
    
    function cloneCommuneRow(){
        if($(".rowCommune:last").find(".name").attr("id") == undefined){
            indexRowCommune = 1;
        }else{
            indexRowCommune = parseInt($(".rowCommune:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var tr    = rowCommuneList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .name").val('');
        tr.find("td .name").attr("id", "name_"+indexRowCommune);
        $("#tblCommune").append(tr);
        var LenTr = parseInt($(".rowCommune").length);
        if(LenTr == 1){
            $("#tblCommune").find("tr:eq("+LenTr+")").find(".btnAddCommuneRow").show();
            $("#tblCommune").find("tr:eq("+LenTr+")").find(".btnRemoveCommune").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowLocation();
    }
    
    function eventKeyRowLocation(){
        $(".name, .btnAddCommuneRow, .btnRemoveCommune").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".name").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".btnAddCommuneRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveCommune").show();
            cloneCommuneRow();
        });
        $(".btnRemoveCommune").click(function(){
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
                        var lenTr = parseInt($(".rowCommune").length);
                        if(lenTr == 1){
                            $("#tblCommune").find("tr:eq("+lenTr+")").find("td .btnRemoveCommune").hide();
                        }
                        $("#tblCommune").find("tr:eq("+lenTr+")").find("td .btnAddCommuneRow").show();
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
        <a href="" class="positive btnBackCommune">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Commune'); ?>
<fieldset>
    <legend><?php __(COMMUNE_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="CommuneProvinceId"><?php echo TABLE_PROVINCE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('province_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CommuneDistrictId"><?php echo TABLE_DISTRICT; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('district_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
    <table id="tblCommune" class="table" style="width: 50%;">
        <tr>
            <th class="first" style="width: 70%;"><?php echo TABLE_COMMUNE; ?></th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowCommune" class="rowCommune" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="name[]" style="width: 90%;" id="name" class="name validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddCommuneRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveCommune" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveCommune"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>