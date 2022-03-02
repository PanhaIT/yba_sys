<?php echo $this->element('prevent_multiple_submit'); ?>
<?php $monthName=array(DATE_JAN, DATE_FEB, DATE_MAR, DATE_APR, DATE_MAY, DATE_JUN, DATE_JUL, DATE_AUG, DATE_SEP, DATE_OCT, DATE_NOV, DATE_DEC); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        $("#SalesTargetAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#SalesTargetAddForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackSalesTarget").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Budget Plan', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Budget Plan', 'Add', 1, '');
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
        
        $(".btnBackSalesTarget").click(function(event){
            event.preventDefault();
             var rightPanel = $("#SalesTargetAddForm").parent();
            var leftPanel   = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableSalesTarget.fnDraw(false);
        });
        
        $(".float").focus(function(){
            if($(this).val() == '0' || $(this).val() == '0.00'){
                $(this).val('');
            }
        });
        
        $(".float").blur(function(){
            if($(this).val() == ''){
                $(this).val('0.00');
            }
        });
        
        $(".float").keyup(function(){
            sumTotalTarget();
        });
    });
    
    function checkEmployeeTarget(field, rules, i, options){
        if($("#SalesTargetEmployeeId").val() == "" || $("#SalesTargetEmployeeName").val() == ""){
            return "* Invalid Sales Rep";
        }
    }
    
    function sumTotalTarget(){
        var totalTarget = 0;
        $(".float").each(function(){
            totalTarget += replaceNum($(this).val());
        });
        $("#totalTarget").text((totalTarget).toFixed(2));
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackSalesTarget">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('SalesTarget', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<fieldset>
    <legend><?php __(MENU_SALES_TARTGET_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="SalesTargetCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    if(count($companies) == 1){
                        $empty = false;
                    } else {
                        $empty = INPUT_SELECT;
                    }
                    echo $this->Form->input('company_id', array('empty' => $empty, 'class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="SalesTargetYear"><?php echo REPORT_FOR_YEAR; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[SalesTarget][year]" id="SalesTargetYear" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php for($i=2014;$i<2051;$i++){ ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="SalesTargetEmployeeName"><?php echo TABLE_SALES_REP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <input type="hidden" id="SalesTargetEmployeeId" name="data[SalesTarget][employee_id]" />
                    <?php echo $this->Form->text('employee_name', array('class'=>'validate[required,funcCall[checkEmployeeTarget]]', 'style' => 'width: 250px;')); ?>
                    <img alt="Search Sales Rep" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchSalesRepTarget" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                    <img alt="Delete Sales Rep" align="absmiddle" style="display: none; cursor: pointer;" class="deleteSalesRepTarget" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="SalesTargetDescription"><?php echo GENERAL_DESCRIPTION; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description'); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(TABLE_TARGET); ?></legend>
    <table class="table" cellspacing="0">
        <tr>
            <?php 
            for($i=0;$i<12;$i++){ 
                if($i == 0){
                    $class = 'first';
                } else {
                    $class = '';
                } 
            ?>
            <th style="text-align: center; width: 6%;" class="<?php echo $class; ?>"><?php echo $monthName[$i]; ?></th>
            <?php } ?>
            <th style="text-align: center; width: 6%;"><?php echo TABLE_TOTAL; ?></th>
        </tr>
        <tr>
            <?php 
            for($i=0;$i<12;$i++){ 
                $mIndex = $i + 1;
                if($i == 0){
                    $class = 'first';
                } else {
                    $class = '';
                }
            ?>
            <td style="text-align: center; width: 6%;" class="<?php echo $class; ?>">
                <?php echo $this->Form->input('m'.$mIndex, array('class'=>'validate[required]', 'style' => 'width: 95%;', 'class' => 'float', 'value' => '0.00')); ?>
            </td>
            <?php } ?>
            <td style="text-align: center;" id="totalTarget">0.00</td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>