<?php 
$sqlSales = mysql_query("SELECT id FROM sales_invoices WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlOrder = mysql_query("SELECT id FROM sales_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlQuote = mysql_query("SELECT id FROM quotations WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlCM    = mysql_query("SELECT id FROM sales_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlPO    = mysql_query("SELECT id FROM purchase_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlPB    = mysql_query("SELECT id FROM purchase_bills WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$sqlBR    = mysql_query("SELECT id FROM purchase_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
$branchUsed = 0;
if(mysql_num_rows($sqlSales) || mysql_num_rows($sqlOrder) || mysql_num_rows($sqlQuote) || mysql_num_rows($sqlCM) || mysql_num_rows($sqlPO) || mysql_num_rows($sqlPB) || mysql_num_rows($sqlBR)){
    $branchUsed = 1;
}
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    var fieldRequire = ['BranchCountryId'];
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#BranchCountryId").chosen({width: 250});
        $("#BranchEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BranchEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if(checkRequireField(fieldRequire) == false){
                    alertSelectRequireField();
                    $(".btnSaveBranch").removeAttr('disabled');
                    return false;
                }
                listbox_selectall('userBranchSelected', true);
                if($("#userBranchSelected").val() == null){
                    alertSelectUserBranch();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveBranch").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackBranch").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Branch', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Branch', 'Edit', 1, '');
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
        $(".btnBackBranch").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableBranch.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        $('#BranchWorkStart, #BranchWorkEnd').timepicker();
    });
    
    function alertSelectUserBranch(){
        $(".btnSaveBranch").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_COMFIRM_SELECT_USER; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBranch">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Branch');
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
?>
<table cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <fieldset>
                <legend><?php __(MENU_BRANCH_INFO); ?></legend>
                <table>
                    <tr>
                        <td style="width: 120px;"><label for="BranchCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php 
                                if($branchUsed == 0){
                                    $emptySelect = INPUT_SELECT;
                                    if(COUNT($companies) == 1){
                                        $emptySelect = false;
                                    }
                                    echo $this->Form->input('company_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;'));
                                } else {
                                    echo $this->data['Company']['name'];
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchBranchTypeId"><?php echo MENU_BRANCH_TYPE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('branch_type_id', array('class'=>'validate[required]', 'empty' => INPUT_SELECT, 'label' => false, 'style' => 'width: 250px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchNameOther"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('name_other', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchTelephone"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('telephone', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchFaxNumber"><?php echo TABLE_FAX; ?>:</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('fax_number', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchEmailAddress"><?php echo TABLE_EMAIL; ?>:</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('email_address', array('class' => 'validate[optional,custom[email]]')); ?>
                            </div>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td><label for="BranchCurrencyCenterId"><?php //echo TABLE_BASE_CURRENCY; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php 
//                                if($branchUsed == 0){
//                                    echo $this->Form->input('currency_center_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 250px;'));
//                                } else {
//                                    echo $this->data['CurrencyCenter']['name'];
//                                }
                                ?>
                            </div>
                        </td>
                    </tr> -->
                    <tr>
                        <td><label for="BranchWorkStart"><?php echo TABLE_WORKING_HOUR; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('work_start', array('class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_START, 'value' => date("H:i",  strtotime($this->data['Branch']['work_start'])))); ?>
                                <?php echo $this->Form->text('work_end', array('class'=>'validate[required]', 'style' => 'width: 170px;', 'placeholder' => TABLE_TIME_END, 'value' => date("H:i",  strtotime($this->data['Branch']['work_end'])))); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchCountryId"><?php echo TABLE_COUNTRY; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->input('country_id', array('class'=>'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'style' => 'width: 265px;')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchLong"><?php echo TABLE_LONG; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('long', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="BranchLat"><?php echo TABLE_LAT; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('lat', array()); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="BranchAddress"><?php echo TABLE_ADDRESS; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->textarea('address', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label for="BranchAddressOther"><?php echo TABLE_ADDRESS_IN_KHMER; ?> <span class="red">*</span> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->textarea('address_other', array('class'=>'validate[required]')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="vertical-align: top;">
            <fieldset style="height: 415px;">
                <legend><?php __(USER_USER_INFO); ?></legend>
                <table>
                    <tr>
                        <th>Available:</th>
                        <th></th>
                        <th>Members:</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <select id="userBranch" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_branches WHERE branch_id=".$this->data['Branch']['id'].")");
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userBranch', 'userBranchSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userBranchSelected', 'userBranch')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userBranchSelected" name="data[Branch][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE is_active= 1 AND id = user_branches.user_id) AS full_name FROM user_branches WHERE branch_id = ".$this->data['Branch']['id']);
                                while($dataDestination=mysql_fetch_array($queryDestination)){
                                    if($dataDestination['user_id'] != ''){
                                ?>
                                <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                                <?php 
                                    }
                                } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset style="height: 160px;">
                <legend><?php __(TABLE_BANK_ACCOUNT_INFORMATION); ?></legend>
                <table>
                    <tr>
                        <td style="width: 120px;"><label for="BranchAccNum"><?php echo TABLE_ACCOUNT_NUMBER; ?>  :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('acc_num', array('class'=>'')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchAccHolder"><?php echo TABLE_ACCOUNT_HOLDER; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('acc_holder', array('class'=>'')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchBank"><?php echo TABLE_BANK; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->text('bank', array('class'=>'')); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 120px;"><label for="BranchBankAddress"><?php echo MENU_ADDRESS; ?> :</label></td>
                        <td>
                            <div class="inputContainer">
                                <?php echo $this->Form->textarea('bank_address',  array('class'=>'','style'=>'height: 40px;')); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br />
<fieldset>
    <legend><?php __(TABLE_MODULE_CODE); ?></legend>
    <table cellpadding="5" cellspacing="0" width="100%">
        <tr>
            <td style="width: 17%;"><label for="adjCode"><?php echo TABLE_ADJ_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['adj_code']; ?>" name="data[ModuleCodeBranch][adj_code]" id="adjCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="toCode"><?php echo TABLE_TO_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['to_code']; ?>" name="data[ModuleCodeBranch][to_code]" id="toCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="posCode"><?php echo TABLE_POS_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['pos_code']; ?>" name="data[ModuleCodeBranch][pos_code]" id="posCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="invCode"><?php echo TABLE_INVOICE_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['inv_code']; ?>" name="data[ModuleCodeBranch][inv_code]" id="invCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="dnCode"><?php echo TABLE_DELIVERY_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['dn_code']; ?>" name="data[ModuleCodeBranch][dn_code]" id="dnCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="cmCode"><?php echo TABLE_CREDIT_MEMO_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['cm_code']; ?>" name="data[ModuleCodeBranch][cm_code]" id="cmCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="quoteCode"><?php echo TABLE_QUOTATION_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['quote_code']; ?>" name="data[ModuleCodeBranch][quote_code]" id="quoteCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="soCode"><?php echo TABLE_SALES_ORDER_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['so_code']; ?>" name="data[ModuleCodeBranch][so_code]" id="soCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="poCode"><?php echo TABLE_PURCHASE_ORDER_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['po_code']; ?>" name="data[ModuleCodeBranch][po_code]" id="poCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="pbCode"><?php echo TABLE_PURCHASE_BILL_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['pb_code']; ?>" name="data[ModuleCodeBranch][pb_code]" id="pbCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="brCode"><?php echo TABLE_BILL_RETURN_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['br_code']; ?>" name="data[ModuleCodeBranch][br_code]" id="brCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="expenseCode"><?php echo TABLE_EXPENSE_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['expense_code']; ?>" name="data[ModuleCodeBranch][expense_code]" id="expenseCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
        <tr>
            <td style="width: 17%;"><label for="invRepCode"><?php echo TABLE_INVOICE_RECEIPT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['inv_rep_code']; ?>" name="data[ModuleCodeBranch][inv_rep_code]" id="invRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="cmRepCode"><?php echo TABLE_CREDIT_MEMO_RECEIPT_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['cm_rep_code']; ?>" name="data[ModuleCodeBranch][cm_rep_code]" id="cmRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="pbRepCode"><?php echo TABLE_PURCHASE_RECEITP_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['pb_rep_code']; ?>" name="data[ModuleCodeBranch][pb_rep_code]" id="pbRepCode" style="width: 90%;" class="validate[required]" /></td>
            <td style="width: 17%;"><label for="brRepCode"><?php echo TABLE_BILL_RETURN_RECEITP_CODE; ?></label> <span class="red">*</span></td>
            <td><input type="text" value="<?php echo $moduleCode['ModuleCodeBranch']['br_rep_code']; ?>" name="data[ModuleCodeBranch][br_rep_code]" id="brRepCode" style="width: 90%;" class="validate[required]" /></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveBranch">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveBranch"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>