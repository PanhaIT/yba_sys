<script type="text/javascript">
    var tblOtherUom = $("#otherUom");
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#otherUom").remove();
        $(".float").autoNumeric({aSep: ',', mNum: 10, mDec: 0});
        $("#UomConverionSmallQuick, #UomConversionValueSmallQuick").removeClass('validate[required]');
        $(".conversionRequire").hide();
        $("#UomConverionSmallQuick").change(function(){
            var old = $("#oldHideUomSmall").val();
            var val = $(this).val();
            var otherUom = $(".otherUom").html();
            if(old != val && otherUom != null){
                dialogAlertRemoveOtherUom($(this), "#oldHideUomSmall", old, val);
            }
            if(val != ''){
                $("#UomConverionSmallQuick, #UomConversionValueSmallQuick").addClass('validate[required]');
                $(".conversionRequire").show();
            } else {
                $("#UomConverionSmallQuick, #UomConversionValueSmallQuick").removeClass('validate[required]');
                $(".conversionRequire").hide();
            }
        });
        $(".btnAddNewConversionOtherQuick").click(function(event){
            event.preventDefault();
            var smallUom = $("#UomConverionSmallQuick").val();  
            if(smallUom != ''){
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
        var smallUom = $("#UomConverionSmallQuick").val();
        var allOtherUom = $(".otherConversionUomQuick");
        if($(".otherUom:last").find("select[name='data[other_uom][]']").attr("id") == undefined){
            index = 1;
        }else{
            index = parseInt($(".otherUom:last").find("select[name='data[other_uom][]']").attr("id").split("_")[1]) + 1;
        }
        tr.removeAttr("style").removeAttr("id");
        tr.find("select[name='data[other_uom][]']").attr("id", "otherConversionUomQuick_"+index).val();
        tr.find("input[name='data[other_value][]']").attr("id", "UomConversionValueOtherQuick_"+index).val('');
        tr.find(".hideOtherUomQuick").attr("id", "hideOtherUomQuick_"+index).val('');
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
        $(".btnAddConOtherUomQuick, .btnRemoveConOtherUomQuick, .UomConversionValueOtherQuick, .otherConversionUomQuick").unbind("click").unbind("change").unbind("keyup").unbind("blur").unbind("focus");
        $(".float").autoNumeric({aSep: ',', mNum: 10, mDec: 0});
        $(".UomConversionValueOtherQuick").blur(function(){
            var val = $(this).val();
            if(val == ""){
                $(this).val(0);
            }
        });
        $(".UomConversionValueOtherQuick").focus(function(){
            var val = $(this).val();
            if(val == 0){
                $(this).val("");
            }
        });
        
        $(".UomConversionValueOtherQuick").blur(function(){
            var valSmUom = replaceNum($("#UomConversionValueSmallQuick").val());
            var val      = replaceNum($(this).val());
            var checkVal = converDicemalJS(valSmUom % val);
            if((val > valSmUom) || checkVal > 0){
                $(this).val(0);
                dialogAlertValOtherUom($(this));
            }
        });
        
        $(".otherConversionUomQuick").change(function(){
            var oldUom = $(this).closest("tr").find(".hideOtherUomQuick").val();
            var newUom = $(this).val();
            var id     = $(this).attr('id');
            $(this).closest("tr").find(".hideOtherUomQuick").val(newUom);
            updateValOtherUom(oldUom, newUom, id);
        });
        
        $(".btnAddConOtherUomQuick").click(function(){
            $(this).hide();
            $(this).closest('tr').find(".btnRemoveConOtherUomQuick").show();
            addNewOtherUom();
        });
        $(".btnRemoveConOtherUomQuick").click(function(){
            var currentTr = $(this).closest("tr");
            removeOtherUom(currentTr);
        });
    }
    
    function removeOtherUom(currentTr){
        currentTr.remove();
        var tblLength = $(".otherUom").length;
        if(tblLength == 0 || tblLength == undefined){
            $(".btnAddNewConversionOtherQuick").show();
        }
        $(".otherUom:last").find(".btnAddConOtherUomQuick").show();
        getIndexOtherUom();
    }
    
    function updateValOtherUom(oldVal, newVal, id){
        $(".otherUom").each(function(){
            var objUom = $(this).find(".otherConversionUomQuick");
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
        $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog2").dialog({
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
        $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog2").dialog({
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
        $("#dialog2").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+question+'</p>');
        $("#dialog2").dialog({
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
                    $(".btnAddNewConversionOtherQuick").show();
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
<br />
<?php echo $this->Form->create('Uom'); ?>
<fieldset style="width: 440px; float: left;">
    <legend><?php echo DIALOG_INFORMATION; ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <td style="width: 90px;"><label for="UomType"><?php echo GENERAL_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('type', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]', 'id' => 'UomType', 'name' => 'data[Uom][type]', 'style' => 'width: 260px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UomName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]', 'id' => 'UomName', 'name' => 'data[Uom][name]', 'style' => 'width: 250px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="UomAbbr"><?php echo GENERAL_ABBR; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('abbr', array('class'=>'validate[required]', 'id' => 'UomAbbr', 'name' => 'data[Uom][abbr]', 'style' => 'width: 250px')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="UomDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->textarea('description', array('id' => 'UomDescription', 'name' => 'data[Uom][description]', 'style' => 'width: 250px')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<fieldset style="width: 440px; float: right;">
    <legend><?php echo MENU_UOM_CONVERSION_MANAGEMENT; ?></legend>
    <table>
        <tr>
            <td><label for="UomConverionSmallQuick"><?php echo TABLE_SMALL_UOM; ?> <span class="red conversionRequire">*</span> :</label></td>
            <td>
                <input type="hidden" id="oldHideUomSmall" value="" /> 
                <?php echo $this->Form->input('to_uom_id', array('id'=>'UomConverionSmallQuick','empty' => INPUT_SELECT, 'label' => false, 'options' => $uomList, 'class'=>'validate[required]', 'name' => 'data[UomConversion][to_uom_id]')); ?></td>
            <td><label for="UomConversionValueSmallQuick"><?php echo TABLE_VOLUME; ?> <span class="red conversionRequire">*</span> :</label></td>
            <td><?php echo $this->Form->text('value', array('id'=>'UomConversionValueSmallQuick','class'=>'validate[required] float', 'style'=>'width:60px', 'name' => 'data[UomConversion][value]')); ?></td>
        </tr>
    </table>
    <br/>
    <fieldset>
    <legend><?php __(TABLE_OTHER_UOM); ?></legend>
        <table id="tblOtherUom">
            <tr>
                <td colspan="5">
                    <div class="buttons">
                        <a href="#" class="positive btnAddNewConversionOtherQuick">
                            <img src="<?php echo $this->webroot; ?>img/button/plus.png" alt=""/>
                            <?php echo ACTION_ADD_NEW_OTHER_UOM; ?>
                        </a>
                    </div>
                </td>
            </tr>
            <tr id="otherUom" class="otherUom">
                <td><?php echo TABLE_OTHER_UOM; ?> <span class="red conversionRequire">*</span> :</td>
                <td><?php echo $this->Form->input('other_uom_id', array('name'=>'data[other_uom][]', 'id'=>'otherConversionUomQuick', 'class'=>'otherConversionUomQuick validate[required]','empty' => INPUT_SELECT, 'label' => false, 'options' => $uomList)); ?></td>
                <td><?php echo TABLE_VOLUME; ?> <span class="red conversionRequire">*</span> :</td>
                <td>
                    <input type="hidden" class="hideOtherUomQuick" value="" />
                    <?php echo $this->Form->text('value', array('name'=>'data[other_value][]', 'id'=>'UomConversionValueOtherQuick', 'class'=>'UomConversionValueOtherQuick validate[required] float', 'style'=>'width:60px')); ?></td>
                <td>
                    <img alt="Remove" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemoveConOtherUomQuick" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    <img alt="Add" src="<?php echo $this->webroot . 'img/button/plus.png'; ?>" class="btnAddConOtherUomQuick" align="absmiddle" style="cursor: pointer;" onmouseover="Tip('Add')" />
                </td>
            </tr>
        </table>
    </fieldset>
</fieldset>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>