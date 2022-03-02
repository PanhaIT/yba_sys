<?php
include("includes/function.php");
$importForm = array();
$sqlModule  = mysql_query("SELECT * FROM s_module_settings WHERE s_module_type_setting_id = ".$id." ORDER BY ordering ASC;");
if(mysql_num_rows($sqlModule)){
    while ($rowModule = mysql_fetch_array($sqlModule)) {
?>
    <h3><?php echo $rowModule['name']; ?></h3>
<?php
        $sqlSetting = mysql_query("SELECT * FROM s_module_detail_settings WHERE s_module_setting_id = " . $rowModule['id'] . " AND is_active > 0 ORDER BY ordering ASC");
        while ($rowSetting = mysql_fetch_array($sqlSetting)) {
            $colspan = 0;
            if($rowSetting['is_use_date'] == 0 && $rowSetting['is_use_integer'] == 0 && $rowSetting['is_use_chart_account'] == 0){
               $colspan = 2; 
            }
?>
    <p>
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td style="width: 25%; padding-left: 10px; <?php if($rowSetting['is_active'] == 2){ ?>color: red;<?php } ?>" colspan="<?php echo $colspan; ?>">
                    <?php
                    if($rowSetting['is_use_checked'] == 1){
                    ?>
                    <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" align="absmiddle" class="checkSettingBoxloader" style="display: none;" />
                    <input type="checkbox" class="checkSettingBox" <?php if($rowSetting['is_checked'] == 1){ ?>checked="checked"<?php } ?> <?php if($rowSetting['is_active'] == 2){ ?>disabled="disabled"<?php } ?> id="checkSetting<?php echo $rowSetting['id']; ?>" is-mod="<?php echo $rowSetting['is_has_module']; ?>" is-id="<?php echo $rowSetting['id']; ?>" /><label for="checkSetting<?php echo $rowSetting['id']; ?>"><?php echo $rowSetting['name']; ?></label>
                    <?php
                    } else {
                        echo $rowSetting['name'];
                    }
                    if($rowSetting['is_has_module'] == 1){
                        $display = '';
                        if($rowSetting['is_use_checked'] == 1 && $rowSetting['is_checked'] == 0){
                            $display = ' display: none;';
                        }
                        if($rowSetting['module_controller'] == 'uoms'){
                    ?>
                    <div style="height: 5px;"></div>
                    <a class="ajaxSettingMenu" style="margin-left: 5px; color: #008cbd;<?php echo $display; ?>" href="<?php echo $this->webroot; ?><?php echo $rowSetting['module_controller']; ?>/<?php echo $rowSetting['module_view']; ?>">[ <?php echo $rowSetting['module_description']; ?> ]</a>
                    <div style="height: 5px;"></div>
                    <a class="ajaxSettingMenu" style="margin-left: 5px; color: #008cbd;<?php echo $display; ?>" href="<?php echo $this->webroot; ?>uom_conversions/index">[ UoM Conversion List ]</a>
                    <?php
                        } else {
                    ?>
                    <a class="ajaxSettingMenu" style="color: #008cbd;<?php echo $display; ?>" href="<?php echo $this->webroot; ?><?php echo $rowSetting['module_controller']; ?>/<?php echo $rowSetting['module_view']; ?>">[ <?php echo $rowSetting['module_description']; ?> ]</a>
                    <?php
                        }
                    }
                    if($rowSetting['is_import'] == 1){
                    ?>
                        <a href="<?php echo $this->base . '/settings'; ?>/downloadTemplate/<?php echo $rowSetting['template']; ?>" target="_blank" class="ajaxSettingDownload" style="color: #008cbd;">[ Download Template ]</a>
                    <?php
                    }
                    ?>
                </td>
                <?php
                if($rowSetting['is_use_date'] == 1 || $rowSetting['is_use_integer'] == 1 || $rowSetting['is_use_chart_account'] == 1 || $rowSetting['is_import'] == 1 || $rowSetting['is_use_currency'] == 1){
                ?>
                <td>
                    <?php
                    if ($rowSetting['is_use_date'] == 1) {
                        $dateClass = 'dateSettingMenu';
                        $showLast  = '';
                        if($rowSetting['id'] == 34){
                            $dateClass = ' dateLockTransaction';
                            if($rowSetting['date_value'] != '' && $rowSetting['date_value'] != '0000-00-00'){
                                $showLast  = ' Last Update: <span id="lockTransactionUpdate">'.dateShort($rowSetting['date_value']).'<span>';
                            } else {
                                $showLast  = ' Last Update: <span id="lockTransactionUpdate"><span>';
                            }
                        }
                    ?>
                        <input type="text" id="dateSettingMenu<?php echo $rowSetting['id']; ?>" is-id="<?php echo $rowSetting['id']; ?>" style="width: 120px; height: 25px;" <?php if($rowSetting['is_active'] == 2){ ?>disabled=""<?php } ?> placeholder="<?php echo TABLE_DATE; ?>" class="<?php echo $dateClass; ?>" />
                        <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" align="absmiddle" class="checkSettingBoxloader" style="display: none;" />
                    <?php
                        echo $showLast;
                    }
                    if ($rowSetting['is_use_integer'] == 1) {
                    ?>
                        <input type="text" class="SettingMenuDecimal" is-id="<?php echo $rowSetting['id']; ?>" is-type="<?php echo $rowSetting['name']; ?>" id="integerSettingMenu<?php echo $rowSetting['id']; ?>" value="<?php echo $rowSetting['value']; ?>" <?php if($rowSetting['is_active'] == 2){ ?>disabled=""<?php } ?> style="width: 40px; height: 25px;" />
                        <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" align="absmiddle" class="checkSettingBoxloader" style="display: none;" />
                    <?php
                    }
                    if($rowSetting['is_use_chart_account'] == 1){
                        $filter = "";
                        $chartAccountId = "";
                    ?>
                        <select id="SettingMenuChartAccountId<?php echo $rowSetting['id']; ?>" <?php if($rowSetting['is_active'] == 2){ ?>disabled=""<?php } ?> style="width:200px; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $query[0]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE (chart_accounts.parent_id IS NULL OR chart_accounts.parent_id = 0) AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                            while($data[0]=mysql_fetch_array($query[0])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                            ?>
                            <option value="<?php echo $data[0]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[0]['id']==$chartAccountId?'selected="selected"':''; ?>><?php echo $data[0]['name']; ?></option>
                                <?php
                                $query[1]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE chart_accounts.parent_id=".$data[0]['id']." AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                                while($data[1]=mysql_fetch_array($query[1])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                                ?>
                                <option value="<?php echo $data[1]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[1]['id']==$chartAccountId?'selected="selected"':''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                    <?php
                                    $query[2]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE chart_accounts.parent_id=".$data[1]['id']." AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                                    while($data[2]=mysql_fetch_array($query[2])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                                    ?>
                                    <option value="<?php echo $data[2]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[2]['id']==$chartAccountId?'selected="selected"':''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                        <?php
                                        $query[3]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE chart_accounts.parent_id=".$data[2]['id']." AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                                        while($data[3]=mysql_fetch_array($query[3])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                        ?>
                                        <option value="<?php echo $data[3]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[3]['id']==$chartAccountId?'selected="selected"':''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                            <?php
                                            $query[4]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE chart_accounts.parent_id=".$data[3]['id']." AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                                            while($data[4]=mysql_fetch_array($query[4])){
                                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                            ?>
                                            <option value="<?php echo $data[4]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[4]['id']==$chartAccountId?'selected="selected"':''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                                <?php
                                                $query[5]=mysql_query("SELECT chart_accounts.id, CONCAT(chart_accounts.account_codes,' - ',chart_accounts.account_description) AS name FROM chart_accounts WHERE chart_accounts.parent_id=".$data[4]['id']." AND chart_accounts.is_active=1 ".$filter." ORDER BY chart_accounts.account_codes");
                                                while($data[5]=mysql_fetch_array($query[5])){
                                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                                ?>
                                                <option value="<?php echo $data[5]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data[5]['id']==$chartAccountId?'selected="selected"':''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    <?php
                    }
                    if($rowSetting['is_use_currency'] == 1){
                        $checkT = mysql_query("SELECT id FROM system_activities WHERE module IN ('Purchase Bill', 'Sales Invoice', 'Sales Return', 'Bill Return', 'Inventory Adjustment', 'Point Of Sales') LIMIT 1");
                    ?>
                        <select class="SettingMenuBaseCurrency" is-id="<?php echo $rowSetting['id']; ?>" id="SettingMenuCurrencyId<?php echo $rowSetting['id']; ?>" <?php if($rowSetting['is_active'] == 2 || mysql_num_rows($checkT)){ ?>disabled=""<?php } ?> style="width:200px; height: 30px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $sqlCurrency=mysql_query("SELECT id, name FROM currencies WHERE is_active = 1 ORDER BY name");
                            while($rowCurrency = mysql_fetch_array($sqlCurrency)){
                            ?>
                            <option value="<?php echo $rowCurrency['id']; ?>" <?php echo $rowCurrency['id']==$rowSetting['value']?'selected="selected"':''; ?>><?php echo $rowCurrency['name']; ?></option>
                            <?php } ?>
                        </select>
                        <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" align="absmiddle" class="checkSettingBoxloader" style="display: none;" />
                    <?php
                    }
                    if($rowSetting['is_import'] == 1){
                        $importForm[] = $rowSetting['id'];
                    ?>
                        <form id="formImportFile<?php echo $rowSetting['id']; ?>" action="<?php echo $this->webroot; ?>settings/import/<?php echo str_replace(".xls", "", $rowSetting['template']); ?>" dialog="<?php echo str_replace(".xls", "", $rowSetting['template']); ?>">
                            <input type="file" name="file_import" id="importFileSetting<?php echo $rowSetting['id']; ?>" class="importFileSetting" />
                        </form>
                    <?php
                    }
                    ?>
                </td>
                <?php
                }
                ?>
            </tr>
        </table>
    </p>
<?php
        }
    }
?>
<br />
<div style="clear: both;"></div>
<?php
}
if(!empty($importForm)){
?>
<script type="text/javascript">
    var titleImport;
    $(document).ready(function () {
        <?php
        foreach($importForm AS $form){
        ?>
        $("#formImportFile<?php echo $form; ?>").ajaxFormUnbind()
        $("#formImportFile<?php echo $form; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray    = new Array(".xls");
                allowSubmit = false;
                titleImport = $("#formImportFile<?php echo $form; ?>").attr('dialog');
                file = $("#importFileSetting<?php echo $form; ?>").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    dialogAlertSetting('<?php echo DIALOG_INFORMATION; ?>', '<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>', 'auto',  'auto');
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $("#importFileSetting<?php echo $form; ?>").val('');
                if(result != '<?php echo MESSAGE_DATA_INVALID; ?>'){
                    $("#dialog").html(result);
                    $("#dialog").dialog({
                        title: 'Import '+titleImport,
                        resizable: false,
                        modal: true,
                        width: 1200,
                        height: 500,
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                $(this).dialog("close");
                                var fileImport = $("#fileImportName").val();
                                var dataPost   = "";
                                if(titleImport == 'adjustment'){
                                    $("#adjustmentDateImport").datepicker("option", "dateFormat", "yy-mm-dd");
                                    dataPost = "branch_id="+$("#branchIdImport").val()+"&location_group_id="+$("#locationGroupIdImport").val()+"&location_id="+$("#locationIdImport").val()+"&adjust="+$("#adjustmentAsImport").val()+"&date="+$("#adjustmentDateImport").val();
                                }
                                $.ajax({
                                    type: "POST",
                                    url: "<?php echo $this->base . '/settings'; ?>/convertImportToDb/"+fileImport+"/"+titleImport,
                                    data: dataPost,
                                    beforeSend: function () {
                                        $("#dialog").html("<img src='<?php echo $this->webroot; ?>img/ajax-loader.gif' alt='loading!' />").dialog({
                                            title: '<?php echo DIALOG_IMPORTING; ?>',
                                            resizable: false,
                                            modal: true,
                                            width: 'auto',
                                            height: 130,
                                            position:'center',
                                            closeOnEscape: false,
                                            open: function(event, ui){
                                                $(".ui-dialog-titlebar-close").hide();
                                                $(".ui-dialog-buttonpane").hide();
                                            }
                                        });
                                    },
                                    success: function (result) {
                                        $("#dialog").dialog("close");
                                        alert(result);
                                    }
                                });
                            },
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    if(titleImport == 'adjustment'){
                        $("#adjustmentDateImport").datepicker({
                            dateFormat:'dd/mm/yy',
                            changeMonth: true,
                            changeYear: true
                        }).unbind("blur");
                    }
                } else {
                    dialogAlertSetting('Import '+titleImport, result, 'auto', 'auto');
                }
            }
        });
        
        $("#importFileSetting<?php echo $form; ?>").unbind("change").change(function(){
            $("#formImportFile<?php echo $form; ?>").submit();
        });
        <?php
        }
        ?>
    });
</script>
<?php
}
?>