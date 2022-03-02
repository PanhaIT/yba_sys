<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowDistrict = 0;
    var rowDistrictList  =  $("#rowDistrict");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowDistrict").remove();
        $("#DistrictAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#DistrictAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveDistrict").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackDistrict").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('District', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('District', 'Add', 1, '');
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
        $(".btnBackDistrict").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableDistrict.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        // Clone Row District List
        cloneDistrictRow();
    });
    
    function cloneDistrictRow(){
        if($(".rowDistrict:last").find(".name").attr("id") == undefined){
            indexRowDistrict = 1;
        }else{
            indexRowDistrict = parseInt($(".rowDistrict:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var tr    = rowDistrictList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .name").val('');
        tr.find("td .name").attr("id", "name_"+indexRowDistrict);
        $("#tblDistrict").append(tr);
        var LenTr = parseInt($(".rowDistrict").length);
        if(LenTr == 1){
            $("#tblDistrict").find("tr:eq("+LenTr+")").find(".btnAddDistrictRow").show();
            $("#tblDistrict").find("tr:eq("+LenTr+")").find(".btnRemoveDistrict").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowLocation();
    }
    
    function eventKeyRowLocation(){
        $(".name, .btnAddDistrictRow, .btnRemoveDistrict").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        $(".name").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        $(".btnAddDistrictRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveDistrict").show();
            cloneDistrictRow();
        });
        $(".btnRemoveDistrict").click(function(){
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
                        var lenTr = parseInt($(".rowDistrict").length);
                        if(lenTr == 1){
                            $("#tblDistrict").find("tr:eq("+lenTr+")").find("td .btnRemoveDistrict").hide();
                        }
                        $("#tblDistrict").find("tr:eq("+lenTr+")").find("td .btnAddDistrictRow").show();
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
        <a href="" class="positive btnBackDistrict">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('District'); ?>
<fieldset>
    <legend><?php __(DISTRICT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="DistrictProvinceId"><?php echo TABLE_PROVINCE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('province_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
    <table id="tblDistrict" class="table" style="width: 50%;">
        <tr>
            <th class="first" style="width: 70%;"><?php echo TABLE_DISTRICT; ?></th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowDistrict" class="rowDistrict" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="name[]" style="width: 90%;" id="name" class="name validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddDistrictRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveDistrict" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSaveDistrict"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>