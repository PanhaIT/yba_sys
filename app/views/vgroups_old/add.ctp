<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var vGroupRequestVendor = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#VgroupAddForm").validationEngine();
        $("#VgroupAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('vendor_id', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVgroup").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVendorGroup").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Vgroup', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Vgroup', 'Add', 1, '');
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
        $(".searchVendor").click(function(){
            if(vGroupRequestVendor != null){
                vGroupRequestVendor.abort();
            }
            var companyId = $("#VgroupCompanyId").val();
            if(companyId != ''){
                vGroupRequestVendor = $.ajax({
                                        type:   "POST",
                                        url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/vendor/"+companyId,
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(msg){
                                            vGroupRequestVendor = null;
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            $("#dialog").html(msg).dialog({
                                                title: '<?php echo MENU_VENDOR_MANAGEMENT_INFO; ?>',
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
                                                        $("input[name='chkVendor']:checked").each(function(){
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
        
        $("#VgroupVendor").autocomplete("<?php echo $this->base . "/vendors/searchVendor"; ?>", {
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
            var vendorId    = value.split(".*")[0];
            var vendorCode  = value.split(".*")[1];
            var vendorName  = value.split(".*")[2];
            if(!checkValueIfExist(vendorId)){
                $("#vendor_id").append('<option value="'+vendorId+'" rel="'+vendorName+'" >'+vendorCode+" - "+vendorName+'</option>');
            }
        };
        
        var checkValueIfExist = function(value){
            var result = false;
            $('#vendor_id').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        };

        $("#btnMinusVendorGroup").click(function(){
            $('#vendor_id option:selected').remove();
            return false;
        });
        
        $(".btnBackVendorGroup").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableVendorGroup.fnDraw(false);
        });
        
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendorGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Vgroup'); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Vgroup][company_id]" id="VgroupCompanyId" />
<?php
}
?>
<fieldset>
    <legend><?php __(MENU_VENDOR_GROUP_MANAGEMENT); ?></legend>
    <table>
        <tr>
            <th style="vertical-align: top;"><label for="VgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></th>
            <td style="vertical-align: top;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_VENDOR); ?></legend>
    <table>
        <tr>
            <th><?php echo MENU_VENDOR; ?></th>
            <td>
                <?php echo $this->Form->text('employee', array('id' => 'VgroupVendor')); ?>
                <img alt="Search" align="absmiddle" style="cursor: pointer;"class="searchVendor" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">
                <select id="vendor_id" name="data[Vgroup][vendor_id][]" multiple="multiple" style="width: 420px; height: 150px;">
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">
                <div class="buttons">
                    <button type="submit" id="btnMinusVendorGroup" class="negative">
                        <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                        <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                    </button>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveVgroup">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveVgroup"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>