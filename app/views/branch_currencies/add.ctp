<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#BranchCurrencyAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BranchCurrencyAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBranchCurrency").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackBranchCurrency").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Branch Currency', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Branch Currency', 'Add', 1, '');
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
        $(".btnBackBranchCurrency").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranchCurrency.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#BranchCurrencyBranchId").change(function(){
            checkCurrencyBranch();
        });
        
        checkCurrencyBranch();
    });
    
    function checkCurrencyBranch(){
        var dCurrency = $("#BranchCurrencyBranchId").find("option:selected").attr("dcur");
        if(dCurrency != ''){
            $("#BranchCurrencyCurrencyId").find("option[value='"+dCurrency+"']").hide();
            $("#BranchCurrencyCurrencyId").attr("disabled", false);
        } else{
            $("#BranchCurrencyCurrencyId").find("option[value='']").attr("selected", true);
            $("#BranchCurrencyCurrencyId").attr("disabled", true);
        }
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranchCurrency">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('BranchCurrency', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<fieldset>
    <legend><?php __(MENU_BRANCH_CURRENCY_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="BranchCurrencyBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[BranchCurrency][branch_id]" id="BranchCurrencyBranchId" class="validate[required]">
                        <?php
                        if(count($branches) != 1){
                        ?>
                        <option value="" dcur=""><?php echo INPUT_SELECT; ?></option>
                        <?php 
                        }
                        foreach($branches AS $branch){  
                        ?>
                        <option value="<?php echo $branch['Branch']['id']; ?>" dcur="<?php echo $branch['Branch']['currency_id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BranchCurrencyCurrencyId"><?php echo MENU_CURRENCY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('currency_id', array('class' => 'validate[required]', 'empty' => INPUT_SELECT, 'disabled' => true)); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveBranchCurrency"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); 
?>