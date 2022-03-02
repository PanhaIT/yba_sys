<?php 
// Authentication
$this->element('check_access');
$allowAddVgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addVgroup');
$allowAddTerm   = checkAccess($user['User']['id'], $this->params['controller'], 'addTerm');

echo $this->element('prevent_multiple_submit'); 
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
?>
<script type="text/javascript">
    var jcrop_api='';
    var x,y,x2,y2,w,h;
    var obj;
    function showCoords(c)
    {
        x=c.x;
        y=c.y;
        x2=c.x2;
        y2=c.y2;
        w=c.w;
        h=c.h;
    };
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        <?php
        if($allowAddVgroup){
        ?>
        $("#VendorVgroupId").chosen({ width: 410, allow_add: true, allow_add_label: '<?php echo MENU_VENDOR_GROUP_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewVgroupVendor' });
        $("#addNewVgroupVendor").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/addVgroup/"; ?>",
                beforeSend: function(){
                    $("#VendorVgroupId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_VENDOR_GROUP_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '200',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#VgroupAddVgroupForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/vendors/addVgroup",
                                        data: $("#VgroupAddVgroupForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Vendor', 'Quick Add Vgroup', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position:'center',
                                                closeOnEscape: true,
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Vendor', 'Quick Add Vgroup', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#VendorVgroupId").html(result.option);
                                                $("#VendorVgroupId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position: 'center',
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
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#VendorVgroupId").chosen({width: 410});
        <?php
        }
        if($allowAddTerm){
        ?>
        $("#VendorPaymentTermId").chosen({ width: 200, allow_add: true, allow_add_label: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewTermVendor' });
        $("#addNewTermVendor").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/vendors/addTerm/"; ?>",
                beforeSend: function(){
                    $("#VendorPaymentTermId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>',
                        resizable: false,
                        modal: true,
                        width: '450',
                        height: '200',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            },
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#PaymentTermAddTermForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/vendors/addTerm",
                                        data: $("#PaymentTermAddTermForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Vendor', 'Quick Add Term', 2, result);
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position:'center',
                                                closeOnEscape: true,
                                                open: function(event, ui){
                                                    $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                                                },
                                                buttons: {
                                                    '<?php echo ACTION_CLOSE; ?>': function() {
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            createSysAct('Vendor', 'Quick Add Term', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#VendorPaymentTermId").html(result.option);
                                                $("#VendorPaymentTermId").trigger("chosen:updated");
                                                msg = '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>';
                                            } else if(result.error == 2){
                                                msg = '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>';
                                            }
                                            // Message Alert
                                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+msg+'</p>');
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position: 'center',
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
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        } else {
        ?>
        $("#VendorPaymentTermId").chosen({ width: 200 });
        <?php
        }
        ?>
        $("#VendorAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#VendorAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if($("#VendorVgroupId").val() == null || $("#VendorVgroupId").val() == '' || $("#VendorPaymentTermId").val() == '' || $("#VendorPaymentTermId").val() == ''){
                    alertSelectRequireField();
                    return false;
                }
                $("#divFromVendorProduct").html($("#vendorProductFrm").html());
                listbox_selectall('vendorProductId', true);
                $(".btnSaveVendor").attr("disabled", "disabled");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveVendor").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackVendor").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Vendor', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Vendor', 'Add', 1, '');
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
        // Action Save Vendor
        $(".btnSaveVendor").click(function(){
            $("#VendorAddForm").submit();
        });
        // Action Save Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#VendorPhoto").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please only upload files that end in types: <b>' + (extArray.join("  ")) + '</b>. Please select a new file to upload again.</p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position: 'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    return false;
                }
            },
            beforeSend: function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var photoFolder='';
                var photoName=result;
                photoFolder="public/vendor_photo/tmp/";
                $('#<?php echo $cropPhoto; ?>').attr("src", "<?php echo $this->webroot; ?>" + photoFolder + photoName + "?" + Math.random());
                if(jcrop_api==''){
                    $('#<?php echo $cropPhoto; ?>').Jcrop({
                        setSelect: [0,0,10000,10000],
                        allowSelect: false,
                        onChange:   showCoords,
                        onSelect:   showCoords
                    },function(){
                        jcrop_api = this;
                    });
                }else{
                    jcrop_api.setImage("<?php echo $this->webroot; ?>" + photoFolder + photoName);
                    jcrop_api.setSelect([0,0,10000,10000]);
                }
                $("#<?php echo $dialogPhoto; ?>").dialog({
                    title: 'Crop Image',
                    resizable: false,
                    modal: true,
                    width: '90%',
                    height: '400',
                    position: 'center',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        'Crop': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $this->base; ?>/vendors/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h,
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    $("#photoDisplay").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#<?php echo $photoNameHidden; ?>").val(result);
                                }
                            });
                        }
                    }
                });
            }
        });
        // Action Photo Submit
        $("#VendorPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        $("#VendorContactNote").blur(function(){
            var note = $("#VendorContactNote").val();
            $("input[name='data[Vendor][note]']").val(note);
        });
        
        $(".btnBackVendor").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
            oCache.iCacheLower = -1;
            oTableVendor.fnDraw(false);
        });
        
        $(".searchVendorProduct").click(function(){
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/product/1",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
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
                                if($("input[name='chkVendorProduct']:checked").val()){
                                    $("input[name='chkVendorProduct']:checked").each(function(){
                                        var tr = $(this).closest("tr");
                                        addSelect(tr.find("input[name='chkVendorProduct']").val());
                                    });
                                }
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });

        $("#VendorProduct").autocomplete("<?php echo $this->base . "/pgroups/searchProduct"; ?>", {
            width: 410,
            max: 10,
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
            var vendorProductId = value.split(".*")[0];
            var product_code = value.split(".*")[1];
            var product_value = value.split(".*")[2];
            if(!checkValueIfExist(vendorProductId)){
                $("#vendorProductId").append('<option value="'+ vendorProductId +'" rel="' + product_value +'" >'+ product_code + " - " + product_value +'</option>');
            }
        }

        var checkValueIfExist = function(value){
            var result = false;
            $('#vendorProductId').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        }

        $("#removeVendorProduct").click(function(){
            $('#vendorProductId option:selected').remove();
            return false;
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackVendor">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_VENDOR_MANAGEMENT_INFO); ?></legend>
    <div style="width: 49%; vertical-align: top; float: left;">
        <?php 
        echo $this->Form->create('Vendor', array('inputDefaults' => array('div' => false, 'label' => false)));
        if(count($companies) == 1){
            $companyId = key($companies);
        ?>
        <input type="hidden" value="<?php echo $companyId; ?>" name="data[Vendor][company_id]" id="VendorCompanyId" />
        <?php
        }
        ?>
        <input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Vendor][photo]" />
        <div id="divFromVendorProduct" style="display: none;"></div>
        <table style="width: 100%;">
            <tr>
                <td><label for="VendorVendorCode"><?php echo TABLE_VENDOR_NUMBER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('vendor_code', array('class' => 'validate[required]', 'value' => $code)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="30%"><label for="VendorNameAdd"><?php echo TABLE_VENDOR_NAME; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'id' => 'VendorNameAdd')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorVgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span> :</label></td>
                <td>
                    <input type="hidden" name="data[Vendor][note]" />
                    <div class="inputContainer">
                        <?php echo $this->Form->input('vgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->input('payment_term_id', array('class' => 'validate[required]', 'empty' => INPUT_SELECT, 'label' => false)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="30%"><label for="VendorCountryId"><?php echo TABLE_COUNTRY; ?> :</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->input('country_id', array('empty' => INPUT_SELECT, 'label' => false, 'style' => 'text-transform: uppercase;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorWorkTelephoneAdd"><?php echo TABLE_TELEPHONE_WORK; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('work_telephone', array( 'id' => 'VendorWorkTelephoneAdd')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorOtherNumberAdd"><?php echo TABLE_TELEPHONE_OTHER; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('other_number', array('id' => 'VendorOtherNumberAdd')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorFaxNumberAdd"><?php echo TABLE_FAX; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('fax_number', array('id' => 'VendorFaxNumberAdd')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="VendorEmailAddressAdd"><?php echo TABLE_EMAIL; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->text('email_address', array('id' => 'VendorEmailAddressAdd', 'class' => 'validate[optional,custom[email]]')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="VendorAddressAdd"><?php echo TABLE_ADDRESS; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <?php echo $this->Form->textarea('address', array('id' => 'VendorAddressAdd')); ?>
                    </div>
                </td>
            </tr>
        </table>
        <?php echo $this->Form->end(); ?>
    </div>
    <div style="vertical-align: top; float: right; width: 49%;">
        <form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/vendors/upload/" method="post" enctype="multipart/form-data">
        <table style="width: 100%">
            <tr>
                <td style="width: 25%;"><label for="VendorPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                <td valign="top"><input type="file" id="VendorPhoto" name="photo" /></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <img id="photoDisplay" alt="" src="<?php echo $this->webroot; ?>img/button/no-images.png" />
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="VendorContactNote"><?php echo TABLE_NOTE; ?>:</label></td>
                <td>
                    <div class="inputContainer">
                        <textarea id="VendorContactNote"></textarea>
                    </div>
                </td>
            </tr>
        </table>
        </form>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<br />
<fieldset>
    <legend><?php __(TABLE_PRODUCT." ".GENERAL_MEMBER); ?></legend>
    <form id="vendorProductFrm">
        <table style="width: 100%;">
            <tr>
                <th><?php echo TABLE_PRODUCT; ?></th>
                <td>
                    <?php echo $this->Form->text('product', array('id' => 'VendorProduct')); ?>
                    <img alt="Search" align="absmiddle" style="cursor: pointer;" class="searchVendorProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="vertical-align: top;">
                    <select id="vendorProductId" name="data[Vendor][product_id][]" multiple="multiple" style="width: 420px; height: 150px;">
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="vertical-align: top;">
                    <div class="buttons">
                        <button type="submit" id="removeVendorProduct" class="negative">
                            <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                            <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<br/>
<div class="buttons">
    <button type="submit" class="positive btnSaveVendor">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveVendor"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>

