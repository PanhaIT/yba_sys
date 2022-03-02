<?php 
echo $this->element('prevent_multiple_submit'); 
$icsUsed = false;
$sqlCheckPgroupUse = mysql_query("SELECT id FROM inventories WHERE product_id IN (SELECT product_id FROM product_pgroups WHERE pgroup_id = ".$this->data['Pgroup']['id'].") LIMIT 1"); 
if(mysql_num_rows($sqlCheckPgroupUse)){
    $icsUsed = true;
}
$sqlCompany = mysql_query("SELECT GROUP_CONCAT(company_id) FROM pgroup_companies WHERE pgroup_id = ".$this->data['Pgroup']['id']);
$rowCompany = mysql_fetch_array($sqlCompany);
?>
<script type="text/javascript">
    var pGroupRequestProduct = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#PgroupParentId, #PgroupDepartmentId").chosen({width: 410});
        $("#PgroupEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#PgroupEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('pgroupProductId', true);
                listbox_selectall('userPgroupSelected', true);
                if($("#PgroupUserApply").val() == 1){
                    if($("#userPgroupSelected").val() == null){
                        alertSelectUserPgroup();
                        return false;
                    }
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePgroup").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackPgroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Pgroup', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Pgroup', 'Edit', 1, '');
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

        $(".searchPgroupProduct").click(function(){
            if(pGroupRequestProduct != null){
                pGroupRequestProduct.abort();
            }
            var companyId = $("#PgroupCompanyId").val();
            if(companyId != null && companyId != ""){
                pGroupRequestProduct = $.ajax({
                                            type:   "POST",
                                            url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/product/"+companyId,
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(msg){
                                                pGroupRequestProduct = null;
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                                $("#dialog").html(msg).dialog({
                                                    title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
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
                                                            $("input[name='chkProduct']:checked").each(function(){
                                                                addSelect($(this).val().toString());
                                                            });
                                                            $(this).dialog("close");
                                                        }
                                                    }
                                                });
                                            }
                                        });
            }
        });

        $("#PgroupProduct").autocomplete("<?php echo $this->base . "/pgroups/searchPgroupProduct"; ?>", {
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
            var product_id    = value.split(".*")[0];
            var product_code  = value.split(".*")[1];
            var product_value = value.split(".*")[2];
            var companyId     = value.split(".*")[3];
            if(!checkValueIfExist(product_id)){
                $("#pgroupProductId").append('<option com="'+ companyId +'" value="'+product_id+'" rel="'+product_value+'" >'+product_code+" - "+product_value+'</option>');
            }
        };

        var checkValueIfExist = function(value){
            var result = false;
            $('#pgroupProductId').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        };

        $("#pgroupProductId").dblclick(function() {
            var id = $(this).attr('value');
            var name = $(this).find("option:selected").attr('rel');
            $("#tabs").tabs("add", "<?php echo $this->base; ?>/products/view/" + id, name);
        });

        $("#PgroupMinus").click(function(){
            $('#pgroupProductId option:selected').remove();
            return false;
        });

        $(".btnBackPgroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePgroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // User Apply Change
        $("#PgroupUserApply").change(function(){
            var val = $(this).val();
            if(val == 1){
                $("#formUserApplyPgroup").show();
            } else {
                $("#formUserApplyPgroup").hide();
                $("#userPgroupSelected").find("option").attr("selected", true);
                listbox_moveacross('userPgroupSelected', 'userPgroup');
            }
        });
    });
    
    function removeProductPgroup(){
        $(".pgroupProducts option").each(function(){
            $(this).remove();
        });
    }
    
    function alertSelectUserPgroup(){
        $(".btnSavePgroup").removeAttr('disabled');
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
        <a href="" class="positive btnBackPgroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php 
echo $this->Form->create('Pgroup', array('inputDefaults' => array('div' => false, 'label' => false))); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Pgroup][company_id]" id="PgroupCompanyId" />
<?php
}
?>
<div>
    <fieldset>
        <legend><?php __(MENU_PRODUCT_GROUP_MANAGEMENT_INFO); ?></legend>
        <table style="height: 100px;">
            <tr>
                <td><label for="PgroupDepartmentId"><?php echo MENU_DEPARTMENT; ?> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->input('department_id', array('style' => 'width: 400px', 'empty' => INPUT_SELECT, 'label' => false, 'div' => false)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;"><label for="PgroupParentId"><?php echo TABLE_SUB_OF_GROUP; ?>:</label></td>
                <td>
                    <select id="PgroupParentId" name="data[Pgroup][parent_id]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $sqlParent = mysql_query("SELECT id, name, (SELECT GROUP_CONCAT(company_id) FROM pgroup_companies WHERE pgroup_id = pgroups.id) AS company_id FROM pgroups WHERE is_active = 1 AND id != ".$this->data['Pgroup']['id']);
                        while($rowParent = mysql_fetch_array($sqlParent)){
                        ?>
                        <option com="<?php echo $rowParent['company_id']; ?>" value="<?php echo $rowParent['id']; ?>" <?php if($rowParent['id'] == $this->data['Pgroup']['parent_id']){ ?>selected="selected"<?php } ?>><?php echo $rowParent['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="PgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 400px')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="PgroupUserApply"><?php echo TABLE_USER_APPLY; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[Pgroup][user_apply]" id="PgroupUserApply" style="width: 180px;">
                            <option value="0" <?php if($this->data['Pgroup']['user_apply'] == 0){ ?>selected="selected"<?php } ?>><?php echo TABLE_ALL; ?></option>
                            <option value="1" <?php if($this->data['Pgroup']['user_apply'] == 1){ ?>selected="selected"<?php } ?>><?php echo TABLE_CUSTOMIZE; ?></option>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div style="clear: both;"></div>
<br />
<table cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="width: 50%;">
            <fieldset>
                <legend><?php __(TABLE_PRODUCT." ".GENERAL_MEMBER); ?></legend>
                <table>
                    <tr>
                        <th style="width: 70px;"><?php echo TABLE_PRODUCT; ?></th>
                        <td>
                            <?php echo $this->Form->text('product', array('id' => 'PgroupProduct')); ?>
                            <img alt="Search" align="absmiddle" style="cursor: pointer;"class="searchPgroupProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="vertical-align: top;">
                            <select class="pgroupProducts" id="pgroupProductId" name="data[Pgroup][product_id][]" multiple="multiple" style="width: 420px; height: 150px;">
                                <?php
                                $sql = "SELECT DISTINCT products.id, products.code, products.name FROM products
                                            INNER JOIN product_pgroups ON product_pgroups.product_id = products.id
                                            WHERE products.is_active=1 AND product_pgroups.pgroup_id=" . $this->data['Pgroup']['id'];
                                $querySource = mysql_query($sql);
                                while ($dataSource = mysql_fetch_array($querySource)) {
                                ?>
                                    <option value="<?php echo $dataSource['id']; ?>" rel="<?php echo $dataSource['name']; ?>" ><?php echo $dataSource['code'] . " - " . $dataSource['name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="vertical-align: top;">
                            <div class="buttons">
                                <button type="submit" id="PgroupMinus" class="negative">
                                    <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                                    <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td style="width: 50%; <?php if($this->data['Pgroup']['user_apply'] == 0){ ?>display: none;<?php } ?>" id="formUserApplyPgroup">
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
                            <select id="userPgroup" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_pgroups WHERE pgroup_id=".$this->data['Pgroup']['id'].")");
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userPgroup', 'userPgroupSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userPgroupSelected', 'userPgroup')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userPgroupSelected" name="data[Pgroup][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_pgroups.user_id) AS full_name FROM user_pgroups WHERE pgroup_id = ".$this->data['Pgroup']['id']);
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
    <button type="submit" class="positive btnSavePgroup">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSavePgroup"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>