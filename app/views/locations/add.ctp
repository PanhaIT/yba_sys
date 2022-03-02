<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    var indexRowLocation = 0;
    var rowLocationList  =  $("#rowLocation");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#rowLocation").remove();
        $("#LocationAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#LocationAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('d', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveLocation").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackLocation").click();
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
        $(".btnBackLocation").click(function(event){
            event.preventDefault();
            $('#LocationAddForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTableLocation.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Clone Row Location List
        cloneLocatinRow();
    });
    
    function cloneLocatinRow(){
        if($(".rowLocation:last").find(".name").attr("id") == undefined){
            indexRowLocation = 1;
        }else{
            indexRowLocation = parseInt($(".rowLocation:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var trTop = $(".rowLocation:last");
        var tr    = rowLocationList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .name").val(trTop.find(".name").val());
        tr.find("td .name").attr("id", "name_"+indexRowLocation);
        tr.find("td .aisle").attr("id", "aisle_"+indexRowLocation).val(trTop.find(".aisle").val());
        tr.find("td .bay").attr("id", "bay_"+indexRowLocation).val(trTop.find(".bay").val());
        tr.find("td .bin").attr("id", "bin_"+indexRowLocation).val(trTop.find(".bin").val());
        tr.find("td .level").attr("id", "level_"+indexRowLocation).val(trTop.find(".level").val());
        tr.find("td .direction").attr("id", "direction_"+indexRowLocation).val(trTop.find(".direction").val());
        tr.find("td .color").attr("id", "color_"+indexRowLocation).val(trTop.find(".color").val());
        tr.find("td .is_for_sale").attr("id", "is_for_sale_"+indexRowLocation);
        $("#tblLocation").append(tr);
        var LenTr = parseInt($(".rowLocation").length);
        if(LenTr == 1){
            $("#tblLocation").find("tr:eq("+LenTr+")").find(".btnAddLocationRow").show();
            $("#tblLocation").find("tr:eq("+LenTr+")").find(".btnRemoveLocation").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowLocation();
    }
    
    function eventKeyRowLocation(){
        $(".name, .aisle, .bay, .bin, .level, .direction, .btnAddLocationRow, .btnRemoveLocation").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        
        $(".aisle, .bay, .bin, .level, .direction").blur(function(){
            var locationName = $(this).closest("tr").find(".aisle").val()+"-"+$(this).closest("tr").find(".bay").val()+"-L"+$(this).closest("tr").find(".level").val()+"-"+$(this).closest("tr").find(".bin").val()+"-"+$(this).closest("tr").find(".direction").val();
            $(this).closest("tr").find(".name").val(locationName);
        });
        
        $(".name").blur(function(){
//            var curId   = $(this).attr('id');
//            var curName = $(this).val();
//            var ready   = false;
//            var obj     = $(this);
//            $(".name").each(function(){
//                var id   = $(this).attr('id');
//                var name = $(this).val();
//                if(id != curId){
//                    if(curName == name && curName != ''){
//                        ready = true;
//                    }
//                }
//            });
//            if(ready == true){
//                $("#dialog").html('<p><?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?></p>');
//                $("#dialog").dialog({
//                    title: '<?php echo DIALOG_WARNING; ?>',
//                    resizable: false,
//                    modal: true,
//                    width: 'auto',
//                    height: 'auto',
//                    open: function(event, ui){
//                        $(".ui-dialog-buttonpane").show();
//                    },
//                    buttons: {
//                        '<?php echo ACTION_CLOSE; ?>': function() {
//                            $(this).dialog("close");
//                            obj.select().focus();
//                        }
//                    }
//                });
//            }
        });
        
        $(".name").keypress(function(e){
            if((e.which && e.which == 13) || e.keyCode == 13){
                return false;
            }
        });
        
        $(".btnAddLocationRow").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveLocation").show();
            cloneLocatinRow();
        });
        $(".btnRemoveLocation").click(function(){
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
                        var lenTr = parseInt($(".rowLocation").length);
                        if(lenTr == 1){
                            $("#tblLocation").find("tr:eq("+lenTr+")").find("td .btnRemoveLocation").hide();
                        }
                        $("#tblLocation").find("tr:eq("+lenTr+")").find("td .btnAddLocationRow").show();
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
        <a href="" class="positive btnBackLocation">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Location'); ?>
<fieldset>
    <legend><?php __(MENU_LOCATION_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="LocationLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
    <table id="tblLocation" class="table" style="width: 100%;">
        <tr>
            <th class="first" style="width: 30%;"><?php echo TABLE_LOCATION; ?> <span class="red">*</span></th>
            <th><?php echo TABLE_AISLE; ?></th>
            <th><?php echo TABLE_BAY; ?></th>
            <th><?php echo TABLE_BIN; ?></th>
            <th><?php echo TABLE_LEVEL; ?></th>
            <th><?php echo TABLE_DIRECTION; ?></th>
            <th style="width: 12%;"><?php echo TABLE_COLOR; ?></th>
            <th><?php echo TABLE_FOR_SALE; ?></th>
            <th><?php echo ACTION_ACTION; ?></th>
        </tr>
        <tr id="rowLocation" class="rowLocation" style="visibility: hidden;">
            <td class="first">
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="name[]" style="width: 90%;" id="name" class="name validate[required]" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="aisle[]" style="width: 90%;" id="aisle" class="aisle" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="bay[]" style="width: 90%;" id="bay" class="bay" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="bin[]" style="width: 90%;" id="bin" class="bin" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="text" name="level[]" style="width: 90%;" id="level" class="level" />
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="direction[]" id="direction" class="direction" style="width: 90%;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="R">R</option>
                        <option value="N">N</option>
                        <option value="L">L</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="color[]" id="color" class="color" style="width: 90%;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="red">Red</option>
                        <option value="orange">Orange</option>
                        <option value="yellow">Yellow</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="is_for_sale[]" style="width: 90%; height: 20px;" id="is_for_sale" class="is_for_sale validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1"><?php echo ACTION_YES; ?></option>
                        <option value="0"><?php echo ACTION_NO; ?></option>
                    </select>
                </div>
            </td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddLocationRow" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveLocation" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset style="display: none;">
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Members:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="s" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
//                    $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1");
//                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php //echo $dataSource['id']; ?>"><?php //echo $dataSource['full_name']; ?></option>
                    <?php 
//                    } 
                    ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('s', 'd')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('d', 's')" />
            </td>
            <td style="vertical-align: top;">
                <select id="d" name="data[Location][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveLocation"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>