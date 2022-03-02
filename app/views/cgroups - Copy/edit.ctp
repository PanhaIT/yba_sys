<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var cGroupRequestCustomer = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen({width: 420});
        $("#CgroupEditForm").validationEngine();
        $("#CgroupEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('customer_id', true);
                listbox_selectall('userCgroupSelected', true);
                if($("#CgroupUserApply").val() == 1){
                    if($("#userCgroupSelected").val() == null){
                        alertSelectUserCgroup();
                        return false;
                    }
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVendor").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCustomerGroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Cgroup', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Cgroup', 'Edit', 1, '');
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
        $(".searchCustomer").click(function(){
            if(cGroupRequestCustomer != null){
                cGroupRequestCustomer.abort();
            }
            var companyId = $("#CgroupCompanyId").val();
            if(companyId != ''){
                cGroupRequestCustomer = $.ajax({
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            cGroupRequestCustomer = null;
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            $("#dialog").html(msg).dialog({
                                                title: '<?php echo MENU_CUSTOMER_MANAGEMENT_INFO; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 800,
                                                height: 500,
                                                position:'center',
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_OK; ?>': function() {
                                                        $("input[name='chkCustomer']:checked").each(function(){
                                                            addSelect($(this).val());
                                                        });
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        }
                                    });
            }
        });
        
        $("#CgroupCustomer").autocomplete("<?php echo $this->base . "/cgroups/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            highlight: false,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            }
        }).result(function(event, value){
            addSelect(value.toString());
            $(this).val('');
        });
        
        var addSelect =  function (value){
            var customerId   = value.split(".*")[0];
            var customerCode = value.split(".*")[1];
            var customerName = value.split(".*")[2];
            if(!checkValueIfExist(customerId)){
                $("#customer_id").append('<option value="'+customerId+'" rel="'+customerName+'" >'+customerCode+" - "+customerName+'</option>');
            }
        };
        
        var checkValueIfExist = function(value){
            var result = false;
            $('#customer_id').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        };
        
        $("#btnMinusCustomerGroup").click(function(){
            $('#customer_id option:selected').remove();
            return false;
        });
        
        $(".btnBackCustomerGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCustomerGroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        // User Apply Change
        $("#CgroupUserApply").change(function(){
            var val = $(this).val();
            if(val == 1){
                $("#formUserApplyCgroup").show();
            } else {
                $("#formUserApplyCgroup").hide();
                $("#userCgroupSelected").find("option").attr("selected", true);
                listbox_moveacross('userCgroupSelected', 'userCgroup');
            }
        });
    });
    
    function alertSelectUserCgroup(){
        $(".btnSaveCgroup").removeAttr('disabled');
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
        <a href="" class="positive btnBackCustomerGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Cgroup'); 
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Cgroup][company_id]" id="CgroupCompanyId" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_CUSTOMER_GROUP_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <th style="width: 10%;"><label for="CgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></th>
            <td><?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 410px;')); ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th><label for="CgroupPriceType"><?php echo TABLE_PRICE_TYPE; ?> :</label></th>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('price_type', array('selected' => $priceTypeSellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => TABLE_ALL, 'class' => 'chzn-select')); ?>
                </div>
            </td>
            <th style="width: 10%;"><label for="CgroupUserApply"><?php echo TABLE_USER_APPLY; ?> :</label></th>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[Cgroup][user_apply]" id="CgroupUserApply" style="width: 180px;">
                        <option value="0" <?php if($this->data['Cgroup']['user_apply'] == 0){ ?>selected="selected"<?php } ?>><?php echo TABLE_ALL; ?></option>
                        <option value="1" <?php if($this->data['Cgroup']['user_apply'] == 1){ ?>selected="selected"<?php } ?>><?php echo TABLE_CUSTOMIZE; ?></option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-size: 12px; font-weight: bold;">Apply for Quotation, Sales Order, Sales Invoice and Credit Memo.</td>
            <td colspan="2"></td>
        </tr>
    </table>
</fieldset>
<br />
<table cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%;">
            <fieldset>
                <legend><?php __(GENERAL_MEMBER); ?></legend>
                <table>
                    <tr>
                        <th><?php echo MENU_CUSTOMER; ?></th>
                        <td>
                            <?php echo $this->Form->text('customer', array('id' => 'CgroupCustomer')); ?>
                            <img alt="Search" align="absmiddle" style="cursor: pointer;"class="searchCustomer" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="vertical-align: top;">
                            <select id="customer_id" name="data[Cgroup][customer_id][]" multiple="multiple" style="width: 420px; height: 150px;">
                                <?php
                                $query = "SELECT DISTINCT customers.id, customers.customer_code, name AS fullname FROM customers
                                            INNER JOIN customer_cgroups ON customers.id = customer_cgroups.customer_id
                                            WHERE customers.is_active=1 AND customer_cgroups.cgroup_id=" . $this->data['Cgroup']['id'];
                                $querySource = mysql_query($query);
                                while ($dataSource = mysql_fetch_array($querySource)) {
                                ?>
                                    <option value="<?php echo $dataSource['id']; ?>" rel="<?php echo $dataSource['fullname']; ?>" ><?php echo $dataSource['customer_code'] . " - " . $dataSource['fullname']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="vertical-align: top;">
                            <div class="buttons">
                                <button type="submit" id="btnMinusCustomerGroup" class="negative">
                                    <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                                    <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="width: 50%; <?php if($this->data['Cgroup']['user_apply'] == 0){ ?>display: none;<?php } ?>" id="formUserApplyCgroup">
            <fieldset>
                <legend><?php __(USER_USER_INFO); ?></legend>
                <table>
                    <tr>
                        <th>Available:</th>
                        <th></th>
                        <th>Members:</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <select id="userCgroup" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_cgroups WHERE cgroup_id=".$this->data['Cgroup']['id'].")");
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userCgroup', 'userCgroupSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userCgroupSelected', 'userCgroup')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userCgroupSelected" name="data[Cgroup][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_cgroups.user_id) AS full_name FROM user_cgroups WHERE cgroup_id = ".$this->data['Cgroup']['id']);
                                while($dataDestination=mysql_fetch_array($queryDestination)){
                                ?>
                                <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveCgroup">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveVendor"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>