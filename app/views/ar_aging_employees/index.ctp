<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript">
    function loadTableArAgingEmployee(){
        if($("#ArAgingEmployeeCompanyId").val()!=""){
            $("#ArAgingEmployeeCompanyId").val()==""?companyId=0:companyId=$("#ArAgingEmployeeCompanyId").val();
            $("#ArAgingEmployeeBranchId").val()==""?branchId=0:branchId=$("#ArAgingEmployeeBranchId").val();
            $("#ArAgingEmployeeEGroupId").val()==""?eGroupId=0:eGroupId=$("#ArAgingEmployeeEGroupId").val();
            $("#ArAgingEmployeeEmployeeId").val()==""?employeeId=0:employeeId=$("#ArAgingEmployeeEmployeeId").val();
            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            $("#tblArAgingEmployee").load("<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+companyId+"/"+branchId+"/"+eGroupId+"/"+employeeId, function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
            });
        }else{
            // alert message
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please select company.</p>');
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
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#ArAgingEmployeeBranchId").filterOptions('com', '0', '');
        // chosen init
        $(".chzn-select").chosen();
        $.ajax({
            type: "POST",
            url: "<?php echo $this->base; ?>/users/smartcode/general_ledgers/reference/7/RPEJ",
            beforeSend: function(){

            },
            success: function(result){
                $("#ArAgingEmployeeReference").val(result);
            }
        });

        // egroup change
        $("#ArAgingEmployeeEGroupId").change(function(){
            if($(this).val()!=''){
                $(this).closest("tr").find("td #ArAgingEmployeeEmployeeId").val('');
                $(this).closest("tr").find("td #ArAgingEmployeeEmployeeId option:not(:first)").attr("disabled","disabled");
                $(this).closest("tr").find("td #ArAgingEmployeeEmployeeId option[group_id_list='"  + $(this).val() + "']").removeAttr("disabled");
            }else{
                $(this).closest("tr").find("td #ArAgingEmployeeEmployeeId option").removeAttr("disabled");
            }
            $(".chzn-select").trigger("liszt:updated");
        });

        $("#ArAgingEmployeeForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var now = new Date();
        $("#ArAgingEmployeeDate").val(now.toString('dd/MM/yyyy'));
        $("#ArAgingEmployeeDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#ArAgingEmployeeCompanyId").change(function(){
            filterChartAccArAgingEmployee();
            $("#ArAgingEmployeeBranchId").filterOptions('com', $(this).val(), '');
            $("#ArAgingEmployeeBranchId").change();
        });
        $("#btnLoadTableArAgingEmployee").click(function(){
            loadTableArAgingEmployee();
        });
        // Button Search Employee
        $(".searchArAgingEmployee").click(function(){
            if($("#ArAgingEmployeeCompanyId").val() != "" && $("#ArAgingEmployeeDate").val() != ""){
                searchAllEmployeeArAging();
            }
        });
        // Button Delete Employee
        $(".deleteArAgingEmployee").click(function(){
            $("#ArAgingEmployeeEmploeeId").val("");
            $("#ArAgingEmployeeEmploeeName").val("");
            $(".searchArAgingEmployee").show();
            $(".deleteArAgingEmployee").hide();
        });
    });
    function filterChartAccArAgingEmployee(){
        var companyId = $("#ArAgingEmployeeCompanyId").val();
        // Chart Account Filter
        $(".ar_aging_coa_id").filterOptions('company_id', companyId, '');
    }
    function checkEmployeeArAging(field, rules, i, options){
        if($("#ArAgingEmployeeEmploeeId").val() == "" || $("#ArAgingEmployeeEmploeeName").val() == ""){
            return "* Invalid Employee";
        }
    }
    function searchAllEmployeeArAging(){
        $("#ArAgingEmployeeDate").datepicker("option", "dateFormat", "yy-mm-dd");
        var companyId = $("#ArAgingEmployeeCompanyId").val();
        var orderDate = $("#ArAgingEmployeeDate").val();
        $.ajax({
            type:   "POST",
            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/employee/"+companyId+"/"+orderDate,
            beforeSend: function(){
                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                $("#ArAgingEmployeeDate").datepicker("option", "dateFormat", "dd/mm/yy");
            },
            success: function(msg){
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                $("#dialog").html(msg).dialog({
                    title: '<?php echo MENU_EMPLOYEE_INFO; ?>',
                    resizable: false,
                    modal: true,
                    width: 850,
                    height: 500,
                    position:'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_OK; ?>': function() {
                            if($("input[name='chkEmployee']:checked").val()){
                                $("#ArAgingEmployeeEmploeeId").val($("input[name='chkEmployee']:checked").val());
                                $("#ArAgingEmployeeEmploeeName").val($("input[name='chkEmployee']:checked").attr("rel"));
                                $(".searchArAgingEmployee").hide();
                                $(".deleteArAgingEmployee").show();
                            }
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }
</script>
<?php echo $this->Form->create('ArAgingEmployee', array ('id'=>'ArAgingEmployeeForm', 'url'=>'/ar_aging_employees/save/')); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div>
        <b style="font-size: 18px;"><?php echo MENU_RECEIVE_PAYMENTS_JOURNAL; ?></b>
    </div>
    <div>
        <div class="inputContainer">
            <table>
                <tr>
                    <td style="width: 10%;"><label for="ArAgingEmployeeCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                    <td><?php echo $this->Form->select('CompanyId', $companies, null, array('escape' => false, 'name' => 'company_id', 'class' => 'validate[required]', 'empty' => INPUT_SELECT)); ?></td>
                    <td><label for="ArAgingEmployeeBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                    <td>
                        <select name="branch_id" id="ArAgingEmployeeBranchId" class="validate[required]">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td><label for="ArAgingEmployeeEGroupId"><?php echo GENERAL_GROUP; ?>:</label></td>
                    <td><?php echo $this->Form->select('EGroupId', $employeeGroups, null, array('escape' => false, 'name' => 'egroup_id', 'class' => 'chzn-select', 'empty' => TABLE_ALL)); ?></td>
                    <td><label for="ArAgingEmployeeEmploeeName"><?php echo TABLE_EMPLOYEE; ?>:</label></td>
                    <td style="width: 25%;">
                        <input type="hidden" name="employee_id" id="ArAgingEmployeeEmploeeId" />
                        <input type="text" id="ArAgingEmployeeEmploeeName" readonly="readonly" class="validate[required,funcCall[checkEmployeeArAging]]" style="width: 75%;" />
                        <img alt="Search" align="absmiddle" style="cursor: pointer; width: 22px; height: 22px;" class="searchArAgingEmployee" onmouseover="Tip('<?php echo GENERAL_SEARCH." ".TABLE_EMPLOYEE; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                        <img alt="Delete" align="absmiddle" style="cursor: pointer; display: none;" class="deleteArAgingEmployee" onmouseover="Tip('<?php echo ACTION_DELETE." ".TABLE_EMPLOYEE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </td>
                    <td>
                        <input type="button" id="btnLoadTableArAgingEmployee" value="Go" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="tblArAgingEmployee">
    
</div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;padding-right: 10px;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ArAgingEmployeeDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => 'readonly')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ArAgingEmployeeReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label>
            <input type="hidden" id="tableName" name="tableName" value="general_ledgers" />
            <input type="hidden" id="fieldCurrentId" name="fieldCurrentId" value="" />
            <input type="hidden" id="fieldName" name="fieldName" value="reference" />
            <input type="hidden" id="fieldCondition" name="fieldCondition" value="is_active=1" />
            <?php echo $this->Form->text('reference', array('class' => 'validate[required]', 'style' => 'width: 100px;')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ArAgingEmployeeChartAccountId"><?php echo SALES_ORDER_DEPOSIT_TO; ?> <span class="red">*</span> :</label>
            <?php
            $filter="AND chart_account_type_id IN (1)";
            ?>
            <select id="ArAgingEmployeeChartAccountId" name="data[ArAgingEmployee][chart_account_id]" class="ar_aging_coa_id validate[required]">
                <option value=""><?php echo INPUT_SELECT; ?></option>
                <?php
                $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 ".$filter." ORDER BY account_codes");
                while($data[0]=mysql_fetch_array($query[0])){
                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                ?>
                <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[0]['id']==$cashBankAccountId?'selected="selected"':''; ?>><?php echo $data[0]['name']; ?></option>
                    <?php
                    $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                    while($data[1]=mysql_fetch_array($query[1])){
                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                    ?>
                    <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[1]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                        <?php
                        $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                        while($data[2]=mysql_fetch_array($query[2])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                        ?>
                        <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[2]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                            <?php
                            $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                            while($data[3]=mysql_fetch_array($query[3])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                            ?>
                            <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[3]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                <?php
                                $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                while($data[4]=mysql_fetch_array($query[4])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                ?>
                                <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[4]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                    <?php
                                    $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                    while($data[5]=mysql_fetch_array($query[5])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                    ?>
                                    <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[5]['id']==$cashBankAccountId?'selected="selected"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </select>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ArAgingEmployeeNote"><?php echo TABLE_NOTE; ?>:</label>
            <?php echo $this->Form->text('note', array('style' => 'width: 100px;')); ?>
        </div>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveArAgingEmployee">
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSaveArAgingEmployee"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>