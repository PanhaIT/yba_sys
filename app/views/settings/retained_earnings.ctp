<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#btnSmartCodeRetainedEarnings").click(function(){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/" + $("#SettingReference").val().toUpperCase(),
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#SettingReference").val(result);
                }
            });
        });
        $("#SettingRetainedEarningsForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#SettingRetainedEarningsForm").ajaxForm({
            beforeSerialize: function($form, options) {
                $("#SettingDate").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".txtSave").html("<?php echo ACTION_SAVE; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#SettingDate").datepicker("option", "dateFormat", "dd/mm/yy");
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Account Closing Date', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Account Closing Date', 'Add', 1, '');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                            // empty data
                            $("#SettingCompanyId").val("");
                            $("#SettingReference").val("");

                            $("#net_profit").val(0);

                            $("#SettingRetainedEarnings").val("");
                            $("#RetainedEarningsAccountDr").text("0.00");
                            $("#RetainedEarningsAccountCr").text("0.00");
                            $("#RetainedEarningsAccountMemo").val("");
                            $("#RetainedEarningsAccountClassId").val("");

                            $("#SettingIncomeSummaryAccount").val("");
                            $("#IncomeSummaryAccountDr").text("0.00");
                            $("#IncomeSummaryAccountCr").text("0.00");
                            $("#IncomeSummaryAccountMemo").val("");
                            $("#IncomeSummaryAccountClassId").val("");
                        }
                    }
                });
            }
        });
        $("#SettingDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $('#SettingDate').datepicker("setDate", new Date("<?php echo date('m/d/Y'); ?>"));
        $('#SettingDate').change(function(){
            if($('#SettingCompanyId').val()!=""){
                getNetIncome();
            }
        });
        $('#SettingCompanyId').change(function(){
            if($('#SettingCompanyId').val()!=""){
                getNetIncome();
            }
        });
    });
    function getNetIncome(){
        $("#SettingDate").datepicker("option", "dateFormat", "yy-mm-dd");
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/getNetIncome/" + $("#SettingDate").val() + "/" + $('#SettingCompanyId').val(),
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                $("button[type=submit]", $("#SettingRetainedEarningsForm")).attr('disabled', 'disabled');
                $("button[type=submit] .txtSave", $("#SettingRetainedEarningsForm")).text("<?php echo ACTION_LOADING; ?>");
            },
            success: function(result){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("button[type=submit]", $("#SettingRetainedEarningsForm")).removeAttr('disabled');
                $("button[type=submit] .txtSave", $("#SettingRetainedEarningsForm")).text("<?php echo ACTION_SAVE; ?>");
                $("#net_profit").val(result);
                if(result>0){
                    $("#RetainedEarningsAccountDr").text(Number(Math.abs(0)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#RetainedEarningsAccountCr").text(Number(Math.abs(result)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#IncomeSummaryAccountDr").text(Number(Math.abs(result)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#IncomeSummaryAccountCr").text(Number(Math.abs(0)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                }else{
                    $("#RetainedEarningsAccountDr").text(Number(Math.abs(result)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#RetainedEarningsAccountCr").text(Number(Math.abs(0)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#IncomeSummaryAccountDr").text(Number(Math.abs(0)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                    $("#IncomeSummaryAccountCr").text(Number(Math.abs(result)).toFixed(2)).formatCurrency({colorize:true, symbol:''});
                }
            }
        });
        $("#SettingDate").datepicker("option", "dateFormat", "dd/mm/yy");
    }
</script>
<?php echo $this->Form->create('Setting');?>
<input type="hidden" id="net_profit" name="net_profit" value="0" />
<fieldset>
    <legend><?php __(MENU_ACCOUNT_CLOSING_DATE_MANAGEMENT); ?></legend>
    <div class="inputContainer">
        <table>
            <tr>
                <td><label for="SettingDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <input type="text" id="SettingDate" name="data[Setting][date]" class="validate[required]" style="width: 188px;" readonly="readonly" />
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="SettingCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->input('company_id', array('empty' => INPUT_SELECT, 'label' => false, 'id' => 'SettingCompanyId', 'class' => 'validate[required]')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="SettingReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <input type="hidden" id="tableName" name="tableName" value="general_ledgers" />
                        <input type="hidden" id="fieldCurrentId" name="fieldCurrentId" value="" />
                        <input type="hidden" id="fieldName" name="fieldName" value="reference" />
                        <input type="hidden" id="fieldCondition" name="fieldCondition" value="is_active=1" />
                        <?php echo $this->Form->text('reference', array('class'=>'validate[required]')); ?>
                        <img alt="" src="<?php echo $this->webroot . 'img/button/cycle.png'; ?>" id="btnSmartCodeRetainedEarnings" style="cursor: pointer;" onmouseover="Tip('Smart Code')" />
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
    <br />
    <table class="table_report">
        <tr>
            <th colspan="2">Accounts</th>
            <th>Debit</th>
            <th>Credit</th>
            <th style="width: 300px;">Memo</th>
            <th>Class</th>
        </tr>
        <tr>
            <td><label for="SettingRetainedEarnings">Retained Earnings Account <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select id="SettingRetainedEarnings" name="data[Setting][retained_earnings]" class="validate[required]" style="width: 300px;">
                        <option value=""><?php echo isset($productId)?'Use Default':INPUT_SELECT; ?></option>
                        <?php
                        $filter='AND chart_account_type_id IN (10)';
                        $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 ".$filter." ORDER BY account_codes");
                        while($data[0]=mysql_fetch_array($query[0])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                        ?>
                        <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
                            <?php
                            $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                            while($data[1]=mysql_fetch_array($query[1])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                            ?>
                            <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                <?php
                                $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                while($data[2]=mysql_fetch_array($query[2])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                ?>
                                <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                    <?php
                                    $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                    while($data[3]=mysql_fetch_array($query[3])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                    ?>
                                    <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                        <?php
                                        $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                        while($data[4]=mysql_fetch_array($query[4])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                        ?>
                                        <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                            <?php
                                            $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                            while($data[5]=mysql_fetch_array($query[5])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                            ?>
                                            <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </td>
            <td id="RetainedEarningsAccountDr" style="text-align: right;">0.00</td>
            <td id="RetainedEarningsAccountCr" style="text-align: right;">0.00</td>
            <td style="text-align: center;">
                <input type="text" id="RetainedEarningsAccountMemo" name="data[Setting][retained_earnings_memo]" value="" style="width: 300px;" />
            </td>
            <td style="text-align: center;">
                <div class="inputContainer">
                    <select id="RetainedEarningsAccountClassId" name="data[Setting][retained_earnings_class_id]">
                        <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            $query[0]=mysql_query("SELECT id,name FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data[0]=mysql_fetch_array($query[0])){?>
                            <option value="<?php echo $data[0]['id']; ?>"><?php echo $data[0]['name']; ?></option>
                                <?php
                                $query[1]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                while($data[1]=mysql_fetch_array($query[1])){?>
                                <option value="<?php echo $data[1]['id']; ?>" style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                    <?php
                                    $query[2]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                    while($data[2]=mysql_fetch_array($query[2])){?>
                                    <option value="<?php echo $data[2]['id']; ?>" style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                        <?php
                                        $query[3]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                        while($data[3]=mysql_fetch_array($query[3])){?>
                                        <option value="<?php echo $data[3]['id']; ?>" style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                            <?php
                                            $query[4]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                            while($data[4]=mysql_fetch_array($query[4])){?>
                                            <option value="<?php echo $data[4]['id']; ?>" style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                <?php
                                                $query[5]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                                while($data[5]=mysql_fetch_array($query[5])){?>
                                                <option value="<?php echo $data[5]['id']; ?>" style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="SettingIncomeSummaryAccount">Income Summary Account <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <select id="SettingIncomeSummaryAccount" name="data[Setting][income_summary_account]" class="validate[required]" style="width: 300px;">
                        <option value=""><?php echo isset($productId)?'Use Default':INPUT_SELECT; ?></option>
                        <?php
                        $filter='AND chart_account_type_id IN (11,13,14,15)';
                        $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 ".$filter." ORDER BY account_codes");
                        while($data[0]=mysql_fetch_array($query[0])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                        ?>
                        <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
                            <?php
                            $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                            while($data[1]=mysql_fetch_array($query[1])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                            ?>
                            <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                <?php
                                $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                while($data[2]=mysql_fetch_array($query[2])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                ?>
                                <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                    <?php
                                    $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                    while($data[3]=mysql_fetch_array($query[3])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                    ?>
                                    <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                        <?php
                                        $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                        while($data[4]=mysql_fetch_array($query[4])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                        ?>
                                        <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                            <?php
                                            $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                            while($data[5]=mysql_fetch_array($query[5])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                            ?>
                                            <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </td>
            <td id="IncomeSummaryAccountDr" style="text-align: right;">0.00</td>
            <td id="IncomeSummaryAccountCr" style="text-align: right;">0.00</td>
            <td style="text-align: center;">
                <input type="text" id="IncomeSummaryAccountMemo" name="data[Setting][income_summary_memo]" value="" style="width: 300px;" />
            </td>
            <td style="text-align: center;">
                <div class="inputContainer">
                    <select id="IncomeSummaryAccountClassId" name="data[Setting][income_summary_class_id]">
                        <option value=""><?php echo TABLE_ALL; ?></option>
                            <?php
                            $query[0]=mysql_query("SELECT id,name FROM classes WHERE ISNULL(parent_id) AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                            while($data[0]=mysql_fetch_array($query[0])){?>
                            <option value="<?php echo $data[0]['id']; ?>"><?php echo $data[0]['name']; ?></option>
                                <?php
                                $query[1]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                while($data[1]=mysql_fetch_array($query[1])){?>
                                <option value="<?php echo $data[1]['id']; ?>" style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                    <?php
                                    $query[2]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                    while($data[2]=mysql_fetch_array($query[2])){?>
                                    <option value="<?php echo $data[2]['id']; ?>" style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                        <?php
                                        $query[3]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                        while($data[3]=mysql_fetch_array($query[3])){?>
                                        <option value="<?php echo $data[3]['id']; ?>" style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                            <?php
                                            $query[4]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                            while($data[4]=mysql_fetch_array($query[4])){?>
                                            <option value="<?php echo $data[4]['id']; ?>" style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                <?php
                                                $query[5]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 AND id IN (SELECT class_id FROM class_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY name");
                                                while($data[5]=mysql_fetch_array($query[5])){?>
                                                <option value="<?php echo $data[5]['id']; ?>" style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button id="btnSubmitRetainedEarnings" type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>



