<style type="text/css">
    #tblGL input[type=text] {
        width: 100px;
    }
    #tblGL select {
        width: 120px;
    }
</style>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/function.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // begin clone process
        $(".coa").html($(".coaClone").html());
        $(".coa").each(function(){
            var randomNumber=Math.floor(Math.random()*1000000)+1000;
            $(this).find(".chart_account_id").attr("id", "chart_account_id"+randomNumber);
            $(this).find(".chart_account_id").val($(this).attr("val"));
        });
        $(".class").html($(".classClone").html());
        $(".class").each(function(){
            var randomNumber=Math.floor(Math.random()*1000000)+1000;
            $(this).find(".class_id").attr("id", "class_id"+randomNumber);
            $(this).find(".class_id").val($(this).attr("val"));
        });
        // end clone process
        $("#GeneralLedgerDate").val(convertDate($("#GeneralLedgerDate").val()));
        $(".btnBackJournalEntry").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackJournalEntry">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('GeneralLedger'); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_JOURNAL_ENTRY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="GeneralLedgerDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('date', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="GeneralLedgerReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('reference'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="GeneralLedgerIsAdj"><?php echo GENERAL_ADJUSTING_ENTRY; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->checkbox('is_adj'); ?></td>
        </tr>
    </table>
</fieldset>
<br />
<table id="tblGL" class="table" cellspacing="0">
    <tr>
        <th class="first" style="width: 1000px;"><?php echo TABLE_COMPANY; ?></th>
        <th><?php echo TABLE_ACCOUNT; ?></th>
        <th><?php echo GENERAL_DEBIT; ?> ($)</th>
        <th><?php echo GENERAL_CREDIT; ?> ($)</th>
        <th><?php echo TABLE_MEMO; ?></th>
        <th><?php echo TABLE_NAME; ?></th>
        <th><?php echo TABLE_CLASS; ?></th>
    </tr>
    <?php
    $index=1;
    $queryGeneralLedgerDetail=mysql_query("SELECT * FROM general_ledger_details WHERE general_ledger_id=".$this->data['GeneralLedger']['id']." ORDER BY id");
    while($dataGeneralLedgerDetail=mysql_fetch_array($queryGeneralLedgerDetail)){
    ?>
    <tr>
        <td class="first">
            <div class="inputContainer">
                <?php echo $this->Form->input('company_id', array('empty' => INPUT_SELECT, 'id' => 'company_id'.$index, 'name' => 'company_id[]', 'value' => $dataGeneralLedgerDetail['company_id'],  'class' => 'company_id validate[required]', 'label' => false)); ?>
            </div>
        </td>
        <td>
            <div class="inputContainer coa" val="<?php echo $dataGeneralLedgerDetail['chart_account_id']; ?>">

            </div>
        </td>
        <td>
            <div class="inputContainer">
                <input type="text" id="debit<?php echo $index; ?>" name="debit[]" class="debit validate[required,custom[number]] number" value="<?php echo $dataGeneralLedgerDetail['debit']; ?>" />
            </div>
        </td>
        <td>
            <div class="inputContainer">
                <input type="text" id="credit<?php echo $index; ?>" name="credit[]" class="credit validate[required,custom[number]] number" value="<?php echo $dataGeneralLedgerDetail['credit']; ?>" />
            </div>
        </td>
        <td>
            <div class="inputContainer">
                <input type="text" id="memo<?php echo $index; ?>" name="memo[]" class="memo validate[required]" value="<?php echo str_replace(array("'",'"',"\\"),array("&rsquo;","&rsquo;&rsquo;","&#92;"),$dataGeneralLedgerDetail['memo']); ?>" onmouseover="Tip('<?php echo str_replace(array("'",'"',"\\"),array("&rsquo;","&rsquo;&rsquo;","&#92;"),$dataGeneralLedgerDetail['memo']); ?>')" />
            </div>
        </td>
        <td>
            <div class="inputContainer">
                <?php echo $this->Form->input('choice', array('empty' => INPUT_SELECT,'options'=>array('Customer' => 'Customer', 'Vendor' => 'Vendor', 'Employee' => 'Employee', 'Other' => 'Other'), 'id' => 'choice'.$index, 'name' => 'choice[]', 'value' => ($dataGeneralLedgerDetail['customer_id']!=''?'Customer':($dataGeneralLedgerDetail['vendor_id']!=''?'Vendor':($dataGeneralLedgerDetail['employee_id']!=''?'Employee':($dataGeneralLedgerDetail['other_id']!=''?'Other':'')))), 'class' => 'choice', 'style' => ($dataGeneralLedgerDetail['vendor_id']!='' || $dataGeneralLedgerDetail['customer_id']!='' || $dataGeneralLedgerDetail['employee_id']!='' || $dataGeneralLedgerDetail['other_id']!=''?'display: none;':''), 'label' => false)); ?>
                <?php echo $this->Form->input('vendor_id', array('empty' => INPUT_SELECT, 'id' => 'vendor_id'.$index, 'name' => 'vendor_id[]', 'value' => $dataGeneralLedgerDetail['vendor_id'], 'class' => 'vendor_id', 'style' => ($dataGeneralLedgerDetail['vendor_id']==''?'display: none;':''), 'label' => false)); ?>
                <?php echo $this->Form->input('customer_id', array('empty' => INPUT_SELECT, 'id' => 'customer_id'.$index, 'name' => 'customer_id[]', 'value' => $dataGeneralLedgerDetail['customer_id'], 'class' => 'customer_id', 'style' => ($dataGeneralLedgerDetail['customer_id']==''?'display: none;':''), 'label' => false)); ?>
                <?php echo $this->Form->input('employee_id', array('empty' => INPUT_SELECT, 'id' => 'employee_id'.$index, 'name' => 'employee_id[]', 'value' => $dataGeneralLedgerDetail['employee_id'], 'class' => 'employee_id', 'style' => ($dataGeneralLedgerDetail['employee_id']==''?'display: none;':''), 'label' => false)); ?>
                <?php echo $this->Form->input('other_id', array('empty' => INPUT_SELECT, 'id' => 'other_id'.$index, 'name' => 'other_id[]', 'value' => $dataGeneralLedgerDetail['other_id'], 'class' => 'other_id', 'style' => ($dataGeneralLedgerDetail['other_id']==''?'display: none;':''), 'label' => false)); ?>
            </div>
        </td>
        <td>
            <div class="inputContainer class" val="<?php echo $dataGeneralLedgerDetail['class_id']; ?>">

            </div>
        </td>
    </tr>
    <?php
    $index++;
    } ?>
</table>
<?php echo $this->Form->end(); ?>
<div class="coaClone" style="display: none;">
    <select id="chart_account_id" name="chart_account_id[]" class="chart_account_id validate[required]">
        <option value=""></option>
        <?php
        $query[0]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 ORDER BY account_codes");
        while($data[0]=mysql_fetch_array($query[0])){
            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
        ?>
        <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
            <?php
            $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 ORDER BY account_codes");
            while($data[1]=mysql_fetch_array($query[1])){
                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
            ?>
            <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                <?php
                $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 ORDER BY account_codes");
                while($data[2]=mysql_fetch_array($query[2])){
                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                ?>
                <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                    <?php
                    $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 ORDER BY account_codes");
                    while($data[3]=mysql_fetch_array($query[3])){
                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                    ?>
                    <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                        <?php
                        $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 ORDER BY account_codes");
                        while($data[4]=mysql_fetch_array($query[4])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                        ?>
                        <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                            <?php
                            $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 ORDER BY account_codes");
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
<div class="classClone" style="display: none;">
    <select id="class_id" name="class_id[]" class="class_id">
        <option value=""><?php echo INPUT_SELECT; ?></option>
        <?php
        $query[0]=mysql_query("SELECT id,name FROM classes WHERE ISNULL(parent_id) AND is_active=1 ORDER BY name");
        while($data[0]=mysql_fetch_array($query[0])){
            $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[0]['id']);
        ?>
        <option value="<?php echo $data[0]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?>><?php echo $data[0]['name']; ?></option>
            <?php
            $query[1]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[0]['id']." AND is_active=1 ORDER BY name");
            while($data[1]=mysql_fetch_array($query[1])){
                $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[1]['id']);
            ?>
            <option value="<?php echo $data[1]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                <?php
                $query[2]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[1]['id']." AND is_active=1 ORDER BY name");
                while($data[2]=mysql_fetch_array($query[2])){
                    $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[2]['id']);
                ?>
                <option value="<?php echo $data[2]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                    <?php
                    $query[3]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[2]['id']." AND is_active=1 ORDER BY name");
                    while($data[3]=mysql_fetch_array($query[3])){
                        $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[3]['id']);
                    ?>
                    <option value="<?php echo $data[3]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                        <?php
                        $query[4]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[3]['id']." AND is_active=1 ORDER BY name");
                        while($data[4]=mysql_fetch_array($query[4])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[4]['id']);
                        ?>
                        <option value="<?php echo $data[4]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                            <?php
                            $query[5]=mysql_query("SELECT id,name FROM classes WHERE parent_id=".$data[4]['id']." AND is_active=1 ORDER BY name");
                            while($data[5]=mysql_fetch_array($query[5])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM classes WHERE is_active=1 AND parent_id=".$data[5]['id']);
                            ?>
                            <option value="<?php echo $data[5]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </select>
</div>