<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var tblOtherUom = $("#otherUom");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#otherUom").remove();
        $(".float").autoNumeric({aSep: ',', mNum: 10, mDec: 0});
        $("#UomConversionAddForm").validationEngine();
        $("#UomConversionAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            beforeSubmit: function(arr, $form, options) {
                $('#UomConversionAddForm').validationEngine('hideAll');
                $(".txtSaveUomConversion").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var rightPanel=$("#UomConversionAddForm").parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableUomConversion.fnDraw(false);
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('UoM Conversion', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('UoM Conversion', 'Add', 1, '');
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
        $("#UomConversionFromUomId").change(function(){
            var old = $("#oldHideFromUom").val();
            var val = $(this).val();
            var otherUom = $(".otherUom").html();
            $("#UomConversionToUomSmallId").find("option").show();
            if($(this).val() != ''){
                $("#UomConversionToUomSmallId").find("option[value='" + $(this).val() + "']").hide();
            }
            if(old != val && otherUom != null){
                dialogAlertRemoveOtherUom($(this), "#oldHideFromUom", old, val);
            }
        });
        $("#UomConversionToUomSmallId").change(function(){
            var old = $("#oldHideUomSmall").val();
            var val = $(this).val();
            var otherUom = $(".otherUom").html();
            $("#UomConversionFromUomId").find("option").show();
            if($(this).val() != ''){
                $("#UomConversionFromUomId").find("option[value='" + $(this).val() + "']").hide();
            }
            if(old != val && otherUom != null){
                dialogAlertRemoveOtherUom($(this), "#oldHideUomSmall", old, val);
            }
        });
        $(".btnBackUomConversion").click(function(event){
            event.preventDefault();
            $('#UomConversionAddForm').validationEngine('hideAll');
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableUomConversion.fnDraw(false);
        });
        $(".btnAddNewUomConversionOther").click(function(event){
            event.preventDefault();
            var mainUom  = $("#UomConversionFromUomId").val();
            var smallUom = $("#UomConversionToUomSmallId").val();  
            if(mainUom != '' && smallUom != ''){
                $(this).hide();
                addNewOtherUom();
            }else{
                dialogAlertSelectUom();
            }
        });
    });
    
    function addNewOtherUom(){
        var index;
        var tr = tblOtherUom.clone(true);
        var mainUom  = $("#UomConversionFromUomId").val();
        var smallUom = $("#UomConversionToUomSmallId").val();
        var allOtherUom = $(".otherConversionUom");
        if($(".otherUom:last").find("select[name='data[other_uom][]']").attr("id") == undefined){
            index = 1;
        }else{
            index = parseInt($(".otherUom:last").find("select[name='data[other_uom][]']").attr("id").split("_")[1]) + 1;
        }
        tr.removeAttr("style").removeAttr("id");
        tr.find("select[name='data[other_uom][]']").attr("id", "otherConversionUom_"+index).val();
        tr.find("input[name='data[other_value][]']").attr("id", "UomConversionValueOther_"+index).val('');
        tr.find(".hideOtherUom").attr("id", "hideOtherUom_"+index).val('');
        tr.find("select[name='data[other_uom][]']").find("option[value='" + mainUom + "']").hide();
        tr.find("select[name='data[other_uom][]']").find("option[value='" + smallUom + "']").hide();
        allOtherUom.each(function(){
            var val = $(this).find("option:selected").val();
            if(val != ''){
                tr.find("select[name='data[other_uom][]']").find("option[value='" + val + "']").hide();
            }
        });
        $("#tblOtherUom").append(tr);
        evenKeyPup();
        getIndexOtherUom();
    }
    
    function getIndexOtherUom(){
        var index = 1;
        $(".otherUom").each(function(){
            $(this).find("td:eq(0)").html(index);
            index++;
        });
    }
    
    function evenKeyPup(){
        $(".btnAddConOtherUom, .btnRemoveConOtherUom, .UomConversionValueOther, .otherConversionUom").unbind("click").unbind("change").unbind("keyup").unbind("blur").unbind("focus");
        $(".float").autoNumeric({aSep: ',', mNum: 10, mDec: 0});
        $(".UomConversionValueOther").blur(function(){
            var val = $(this).val();
            if(val == ""){
                $(this).val(0);
            }
        });
        $(".UomConversionValueOther").focus(function(){
            var val = $(this).val();
            if(val == 0){
                $(this).val("");
            }
        });
        
        $(".UomConversionValueOther").blur(function(){
            var valSmUom = replaceNum($("#UomConversionValueSmall").val());
            var val      = replaceNum($(this).val());
            var checkVal = converDicemalJS(valSmUom % val);
            if((val > valSmUom) || checkVal > 0){
                $(this).val(0);
                dialogAlertValOtherUom($(this));
            }
        });
        
        $(".otherConversionUom").change(function(){
            var oldUom = $(this).closest("tr").find(".hideOtherUom").val();
            var newUom = $(this).val();
            var id     = $(this).attr('id');
            $(this).closest("tr").find(".hideOtherUom").val(newUom);
            updateValOtherUom(oldUom, newUom, id);
        });
        
        $(".btnAddConOtherUom").click(function(){
            $(this).hide();
            $(this).closest('tr').find(".btnRemoveConOtherUom").show();
            addNewOtherUom();
        });
        $(".btnRemoveConOtherUom").click(function(){
            var currentTr = $(this).closest("tr");
            removeOtherUom(currentTr);
        });
    }
    
    function removeOtherUom(currentTr){
        currentTr.remove();
        var tblLength = $(".otherUom").length;
        if(tblLength == 0 || tblLength == undefined){
            $(".btnAddNewUomConversionOther").show();
        }
        $(".otherUom:last").find(".btnAddConOtherUom").show();
        getIndexOtherUom();
    }
    
    function updateValOtherUom(oldVal, newVal, id){
        $(".otherUom").each(function(){
            var objUom = $(this).find(".otherConversionUom");
            var uomId  = objUom.attr('id');
            if(uomId != id){
                if(newVal != ''){
                    objUom.find("option[value='" + newVal + "']").hide();
                }
                objUom.find("option[value='" + oldVal + "']").show();
            }
        });
    }
    
    function dialogAlertValOtherUom(obj){
        var question = "តំលៃត្រូវតូចជាង តំលៃនៃខ្នាតតូច នឹងចែកដាច់តំលៃនៃខ្នាតតូច!";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function (){
                
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    obj.select().focus();
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function dialogAlertSelectUom(){
        var question = "សូមជ្រើសរើសខ្នាតធំ និងខ្នាតតូច មុននឹងជ្រើសរើសខ្នាតផ្សេងៗ!";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function (){
                
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    
    function dialogAlertRemoveOtherUom(obj, hideObj, old, newVal){
        var question = "តើអ្នកពិតជាចង់ ប្តូរខ្នាតមែន?<br/> វានឹងលុបខ្នាតផ្សេងៗពេលអ្នកប្តូរ!";
        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            close: function (){
                
            },
            buttons: {
                '<?php echo ACTION_OK; ?>': function() {
                    $(".otherUom").remove();
                    $(".btnAddNewUomConversionOther").show();
                    $(hideObj).val(newVal);
                    $(this).dialog("close");
                },
                '<?php echo ACTION_CANCEL; ?>': function() {
                    obj.find("option[value='"+old+"']").attr("selected","selected");
                    $(this).dialog("close");
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackUomConversion">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('UomConversion'); ?>
<fieldset>
    <legend><?php __(MENU_UOM_CONVERSION_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="UomConversionFromUomId"><?php echo TABLE_MAIN_UOM; ?> <span class="red">*</span> :</label></td>
            <td colspan="3">
                <input type="hidden" id="oldHideFromUom" value="" />
                <?php echo $this->Form->input('from_uom_id', array('empty' => INPUT_SELECT, 'label' => false, 'options' => $uomListMain, 'class'=>'validate[required]')); ?></td>            
        </tr>
        <tr>
            <td><label for="UomConversionToUomSmallId"><?php echo TABLE_SMALL_UOM; ?> <span class="red">*</span> :</label></td>
            <td>
                <input type="hidden" id="oldHideUomSmall" value="" /> 
                <?php echo $this->Form->input('to_uom_id', array('id'=>'UomConversionToUomSmallId','empty' => INPUT_SELECT, 'label' => false, 'options' => $uomList, 'class'=>'validate[required]')); ?></td>
            <td><label for="UomConversionValueSmall"><?php echo TABLE_VOLUME; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('value', array('id'=>'UomConversionValueSmall','class'=>'validate[required] float', 'style'=>'width:120px')); ?></td>
        </tr>
    </table>
    <br/>
    <fieldset>
    <legend><?php __(TABLE_OTHER_UOM); ?></legend>
        <table id="tblOtherUom">
            <tr>
                <td colspan="5">
                    <div class="buttons">
                        <a href="#" class="positive btnAddNewUomConversionOther">
                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                            <?php echo ACTION_ADD_NEW_OTHER_UOM; ?>
                        </a>
                    </div>
                </td>
            </tr>
            <tr id="otherUom" class="otherUom">
                <td><?php echo TABLE_OTHER_UOM; ?> <span class="red">*</span> :</td>
                <td><?php echo $this->Form->input('other_uom_id', array('name'=>'data[other_uom][]', 'id'=>'otherConversionUom', 'class'=>'otherConversionUom validate[required]','empty' => INPUT_SELECT, 'label' => false, 'options' => $uomList)); ?></td>
                <td><?php echo TABLE_VOLUME; ?> <span class="red">*</span> :</td>
                <td>
                    <input type="hidden" class="hideOtherUom" value="" />
                    <?php echo $this->Form->text('value', array('name'=>'data[other_value][]', 'id'=>'UomConversionValueOther', 'class'=>'UomConversionValueOther validate[required] float', 'style'=>'width:120px')); ?></td>
                <td>
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveConOtherUom" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddConOtherUom" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                </td>
            </tr>
        </table>
    </fieldset>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveUomConversion"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>