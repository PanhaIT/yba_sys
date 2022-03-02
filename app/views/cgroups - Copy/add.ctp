<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var cGroupRequestCustomer = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen({width: 420});
        $("#CgroupAddForm").validationEngine();        
        $("#CgroupAddForm").ajaxForm({
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
                    createSysAct('Cgroup', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Cgroup', 'Add', 1, '');
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
<?php echo $this->Form->create('Cgroup'); 
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
                    <?php echo $this->Form->input('price_type', array('label' => false, 'multiple' => 'multiple', 'data-placeholder' => TABLE_ALL, 'class' => 'chzn-select')); ?>
                </div>
            </td>
            <th style="width: 10%;"><label for="CgroupUserApply"><?php echo TABLE_USER_APPLY; ?> :</label></th>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select name="data[Cgroup][user_apply]" id="CgroupUserApply" style="width: 180px;">
                        <option value="0"><?php echo TABLE_ALL; ?></option>
                        <option value="1"><?php echo TABLE_CUSTOMIZE; ?></option>
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
        <td style="width: 50%; display: none;" id="formUserApplyCgroup">
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
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1");
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
                            <select id="userCgroupSelected" name="data[Cgroup][user_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
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