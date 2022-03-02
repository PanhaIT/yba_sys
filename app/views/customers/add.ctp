<?php 
// Authentication
$this->element('check_access');
$allowAddCgroup = checkAccess($user['User']['id'], $this->params['controller'], 'addCgroup');
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
    var indexRowCusContact = 0;
    var rowCusContactList  =  $("#rowCusContact");
    $(document).ready(function(){
        $("#rowCusContact").remove();
        // Prevent Key Enter
        preventKeyEnter();
        $("#CustomerLimitBalance, #CustomerDiscount").autoNumeric({mDec: 2, aSep: ','});
        $("#CustomerLimitTotalInvoice").autoNumeric({mDec: 0, aSep: ','});
        $("#CustomerPaymentEvery").autoNumeric({mDec: 0, aSep: '', mNum: 2});
        
        <?php
        if($allowAddCgroup){
        ?>
        $("#CustomerCgroupId").chosen({ width: 275, allow_add: true, allow_add_label: '<?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewCgroupCustomer' });
        $("#addNewCgroupCustomer").click(function(){
            $.ajax({
                type:   "GET",
                url:    "<?php echo $this->base . "/customers/addCgroup/"; ?>",
                beforeSend: function(){
                    $("#CustomerCgroupId").trigger("chosen:close");
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_ADD; ?>',
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
                                var formName = "#CgroupAddCgroupForm";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        dataType: "json",
                                        type: "POST",
                                        url: "<?php echo $this->base; ?>/customers/addCgroup",
                                        data: $("#CgroupAddCgroupForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Customer', 'Quick Add Vgroup', 2, result);
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
                                            createSysAct('Customer', 'Quick Add Vgroup', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#CustomerCgroupId").html(result.option);
                                                $("#CustomerCgroupId").trigger("chosen:updated");
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
        $("#CustomerCgroupId").chosen({width: 275});
        <?php
        }
        if($allowAddTerm){
        ?>
        $("#CustomerPaymentTermId").chosen({ width: 274, allow_add: true, allow_add_label: '<?php echo MENU_PAYMENT_TERM_MANAGEMENT_ADD; ?>', allow_add_id: 'addNewTermCustomer' });
        $("#addNewTermCustomer").click(function(){
            $.ajax({
                type: "GET",
                url:  "<?php echo $this->base . "/customers/addTerm/"; ?>",
                beforeSend: function(){
                    $("#CustomerPaymentTermId").trigger("chosen:close");
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
                                        url: "<?php echo $this->base; ?>/customers/addTerm",
                                        data: $("#PaymentTermAddTermForm").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        error: function (result) {
                                            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                            createSysAct('Customer', 'Quick Add Term', 2, result);
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
                                            createSysAct('Customer', 'Quick Add Term', 1, '');
                                            var msg = '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>';
                                            if(result.error == 0){
                                                // Update Pgroup
                                                $("#CustomerPaymentTermId").html(result.option);
                                                $("#CustomerPaymentTermId").trigger("chosen:updated");
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
        $("#CustomerPaymentTermId").chosen({width: 274});
        <?php
        }
        ?>
        
        $("#CustomerType").change(function(){
            var value = $(this).val();
            if(value == '1'){
                $("#addressCountry").show();
                $("#addressOverSea").hide();
            }else if(value == '2'){
                $("#addressCountry").hide();
                $("#addressOverSea").show();
            }else{
                $("#addressCountry").hide();
                $("#addressOverSea").hide();
            }
        });
        
        // Action Focus & Blur
        $("#CustomerLimitBalance, #CustomerLimitTotalInvoice, #CustomerPaymentEvery, #CustomerDiscount").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        $("#CustomerLimitBalance, #CustomerLimitTotalInvoice, #CustomerPaymentEvery, #CustomerDiscount").blur(function(){
            if($(this).val() == ''){
                $(this).val(0);
            }
        });
        
        $("#CustomerAddForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        // Action Save
        $("#CustomerAddForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if($("#CustomerCgroupId").val() == "" || $("#CustomerCgroupId").val() == null){
                    alertSelectGroupCus();
                    return false;
                }
                $(".float").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
                $("#CustomerLimitBalance").val($("#CustomerLimitBalance").val().replace(/,/g,""));
                $("#CustomerLimitTotalInvoice").val($("#CustomerLimitTotalInvoice").val().replace(/,/g,""));
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtCustomerSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCustomer").click();
                // Alert Message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM; ?>'){
                    createSysAct('Customer', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Customer', 'Add', 1, '');
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
        // Action Save Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".bmp",".jpg",".gif",".tif",".png");
                allowSubmit = false;
                file = $("#CustomerPhoto").val();
                if (!file) return;
                while (file.indexOf("\\") != -1)
                    file = file.slice(file.indexOf("\\") + 1);
                ext = file.slice(file.indexOf(".")).toLowerCase();
                for (var i = 0; i < extArray.length; i++) {
                    if (extArray[i] == ext) { allowSubmit = true; break; }
                }
                if (!allowSubmit){
                    // Alert Message
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
                photoFolder="public/customer_photo/tmp/";
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
                                url: "<?php echo $this->base; ?>/customers/cropPhoto",
                                data: "photoFolder=" + photoFolder.replace(/\//g,"|||") + "&photoName=" + photoName + "&x=" + x + "&y=" + y + "&x2=" + x2 + "&y2=" + y2 + "&w=" + w + "&h=" + h,
                                beforeSend: function(){
                                    $("#<?php echo $dialogPhoto; ?>").dialog("close");
                                },
                                success: function(result){
                                    $("#photoDisplayCustomer").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + "thumbnail/" + result);
                                    $("#<?php echo $photoNameHidden; ?>").val(result);
                                }
                            });
                        }
                    }
                });
            }
        });
        // Action Photo Submit
        $("#CustomerPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        // Action Back
        $(".btnBackCustomer").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCustomer.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        // Action Address
        // Province
        $(".province").change(function(){
            if($(this).val()!=""){
                $(".district").val('');
                $(".district option[class!='']").hide();
                $(".district option[class='"  + $(this).val() + "']").show();
            }else{
                $(".district").val('');
                $(".commune").val('');
                $(".village").val('');
                $(".district option").show();
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
        });
        // District
        $(".district").change(function(){
            if($(this).val()!=""){
                $(".province").val($(".district").find("option:selected").attr("class"));
                $(".commune").val('');
                $(".commune option[class!='']").hide();
                $(".commune option[class='"  + $(this).val() + "']").show();
            }else{
                $(".commune").val('');
                $(".village").val('');
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
        });
        // Commune
        $(".commune").change(function(){
            if($(this).val()!=""){
                var commune = $(this).val();
                $(".district").val($(".commune").find("option:selected").attr("class"));
                $(".province").val($(".district").find("option:selected").attr("class"));
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base."/customers/getVillage"; ?>",
                    data: "data[commune][id]=" + commune,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(msg){
                        $(".village").html(msg);
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    }
                });
            }else{
                $(".village").val('');
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
        });
        
        // Customer Name
        $("#CustomerName").blur(function(){
            $("#CustomerNameKh").val($(this).val());
        });
        
        $("#CustomerNameKh").focus(function(){
            $(this).select().focus();
        });
        
        cloneCusContactRow();
    });
    
    function alertSelectGroupCus(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_GROUP; ?></p>');
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
                    $(".btnSaveCustomer").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
    
    function cloneCusContactRow(){
        if($(".rowCusContact:last").find(".name").attr("id") == undefined){
            indexRowCusContact = 1;
        }else{
            indexRowCusContact = parseInt($(".rowCusContact:last").find(".name").attr("id").split("_")[1]) + 1;
        }
        var trTop = $(".rowCusContact:last");
        var tr    = rowCusContactList.clone(true);
        tr.removeAttr("style").removeAttr("id");
        tr.find("td .customer_contact_name").val(trTop.find(".customer_contact_name").val());
        tr.find("td .customer_contact_name").attr("id", "customer_contact_name_"+indexRowCusContact);
        $("#tblCusContact").append(tr);
        var LenTr = parseInt($(".rowCusContact").length);
        if(LenTr == 1){
            $("#tblCusContact").find("tr:eq("+LenTr+")").find(".btnAddCusContact").show();
            $("#tblCusContact").find("tr:eq("+LenTr+")").find(".btnRemoveCusContact").hide();
        }
        tr.find("td .name").focus();
        eventKeyRowCusContact();
    }
    
    function eventKeyRowCusContact(){
        $(".customer_contact_name, .btnAddCusContact, .btnRemoveCusContact").unbind('click').unbind('keyup').unbind('keypress').unbind('change').unbind('blur');
        
        $(".btnAddCusContact").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemoveCusContact").show();
            cloneCusContactRow();
        });
        $(".btnRemoveCusContact").click(function(){
            var obj = $(this);
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete the selected item(s)?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        obj.closest("tr").remove();
                        var lenTr = parseInt($(".rowCusContact").length);
                        if(lenTr == 1){
                            $("#tblCusContact").find("tr:eq("+lenTr+")").find("td .btnRemoveCusContact").hide();
                        }
                        $("#tblCusContact").find("tr:eq("+lenTr+")").find("td .btnAddCusContact").show();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCustomer">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_CUSTOMER_MANAGEMENT_INFO); ?></legend>
    <?php 
    echo $this->Form->create('Customer', array('inputDefaults' => array('div' => false, 'label' => false))); 
    if(count($companies) == 1){
        $companyId = key($companies);
    ?>
    <input type="hidden" value="<?php echo $companyId; ?>" name="data[Customer][company_id]" id="CustomerCompanyId" />
    <?php
    }
    ?>
    <input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Customer][photo]" />
    <div style="width: 40%; vertical-align: top; float: left;">
        <table style="width: 99%;">
            <tr>
                <td style="width: 40%;"><label for="CustomerCode"><?php echo TABLE_CUSTOMER_NUMBER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('customer_code', array('class' => 'validate[required]', 'style' => 'width: 90%;', 'value' => $code)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerName"><?php echo TABLE_NAME_IN_ENGLISH; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerNameKh"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('name_kh', array('class' => 'validate[required]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 33%;"><label for="CustomerCgroupId"><?php echo TABLE_GROUP; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('cgroup_id', array('label' => false, 'empty' => INPUT_SELECT)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 40%;"><label for="CustomerType"><?php echo TABLE_CUSTOMER_ADDRESS; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <select name="data[Customer][type]" id="CustomerType" class="validate[required]" style="width: 93%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <option value="1"><?php echo TABLE_CAMBODIA; ?></option>
                            <option value="2"><?php echo TABLE_CUSTOMER_OVERSEA; ?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr id="addressOverSea" style="display: none;">
                <td colspan="2">
                    <fieldset>
                        <legend><?php echo TABLE_ADDRESS; ?></legend>
                        <div class="inputContainer" style="width: 100%;">
                            <?php echo $this->Form->textarea('address', array('style' => 'width: 90%; height: 80px;')); ?>
                        </div>
                    </fieldset>
                </td>
            </tr>
            <tr id="addressCountry" style="display: none;">
                <td colspan="2">
                    <fieldset>
                        <legend><?php echo TABLE_ADDRESS; ?></legend>
                        <table cellpadding="3" cellspacing="0" style="width: 100%;">
                            <tr>
                                <td style="width: 20%;"><label for="CustomerHouseNo"><?php echo TABLE_NO; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->text('house_no', array('style' => 'width: 86%;')); ?>
                                    </div>
                                </td>
                                <td style="width: 10%;"><label for="CustomerStreet"><?php echo TABLE_STREET; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('street_id', array('empty' => INPUT_SELECT, 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><label for="CustomerProvinceId"><?php echo TABLE_PROVINCE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <select name="data[Customer][province_id]" class="province" id="CustomerProvinceId" style="width: 95%;">
                                            <option value="" abbr=""><?php echo INPUT_SELECT; ?></option>
                                            <?php
                                            foreach($provinces AS $province){
                                            ?>
                                            <option value="<?php echo $province['Province']['id']; ?>" abbr="<?php echo $province['Province']['abbr']; ?>"><?php echo $province['Province']['name']; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </td>
                                <td style="width: 12%;"><label for="CustomerDistrictId"><?php echo TABLE_DISTRICT; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('district_id', array('empty' => INPUT_SELECT, 'class'=>'district', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 18%;"><label for="CustomerCommuneId"><?php echo TABLE_COMMUNE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('commune_id', array('empty' => INPUT_SELECT, 'class'=>'commune', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                                <td style="width: 12%;"><label for="CustomerVillageId"><?php echo TABLE_VILLAGE; ?></label></td>
                                <td>
                                    <div class="inputContainer" style="width: 100%;">
                                        <?php echo $this->Form->input('village_id', array('empty' => INPUT_SELECT,  'class'=>'village', 'label'=>false, 'style' => 'width: 95%;')); ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerMainNumberAdd"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('main_number', array('class' => 'validate[required]', 'id' => 'CustomerMainNumberAdd', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerMobileNumberAdd"><?php echo TABLE_MOBILE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('mobile_number', array('id' => 'CustomerMobileNumberAdd', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerOtherNumberAdd"><?php echo TABLE_TELEPHONE_ALT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('other_number', array('id' => 'CustomerOtherNumberAdd', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerEmailAdd"><?php echo TABLE_EMAIL; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('email', array('id' => 'CustomerEmailAdd', 'class' => 'validate[optional,custom[email]]', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerFaxAdd"><?php echo TABLE_FAX; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('fax', array('id' => 'CustomerFaxAdd', 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerPaymentTermId"><?php echo TABLE_PAYMENT_TERMS; ?>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('payment_term_id', array('empty' => INPUT_NONE, 'label' => false)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerPaymentEvery"><?php echo TABLE_PAYMENT_EVERY; ?>:</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('payment_every', array('value' => 0, 'empty' => INPUT_NONE, 'label' => false, 'style' => 'width: 90%;')); ?>
                    </div>
                </td>
            </tr>
        </table>
        <br />
        <div class="buttons">
            <button type="submit" class="positive btnSaveCustomer">
                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                <span class="txtCustomerSave"><?php echo ACTION_SAVE; ?></span>
            </button>
        </div>
    </div>
    <div style="width: 30%; vertical-align: top; float: left;">
        <table style="width: 100%;">
            <tr>
                <td><label for="CustomerVat"><?php echo TABLE_VAT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('vat', array('style' => 'width: 250px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerLimitBalance"><?php echo TABLE_LIMIT_CREDIT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('limit_balance', array('value' => 0, 'style' => 'width: 250px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerLimitTotalInvoice"><?php echo TABLE_LIMIT_NUMBER_INVOICE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('limit_total_invoice', array('value' => 0, 'style' => 'width: 250px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="CustomerDiscount"><?php echo GENERAL_DISCOUNT; ?> (%):</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('discount', array('value' => 0, 'style' => 'width: 250px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="CustomerNote"><?php echo TABLE_NOTE; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->textarea('note', array('style' => 'width: 250px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table id="tblCusContact" class="table" style="width: 100%;">
                        <tr>
                            <th colspan="4" class="first" style="text-align: center;">Customer with Shipment</th>
                        </tr>
                        <tr>
                            <th class="first" style="width: 50%;"><?php echo TABLE_NAME; ?> <span class="red">*</span></th>
                            <th><?php echo ACTION_ACTION; ?></th>
                        </tr>
                        <tr id="rowCusContact" class="rowCusContact" style="visibility: hidden;">
                            <td class="first">
                                <div class="inputContainer" style="width: 100%;">
                                    <input type="text" name="customer_contact_name[]" style="width: 90%;" id="customer_contact_name" class="customer_contact_name validate[required]" />
                                </div>
                            </td>
                            <td>
                                <div class="inputContainer" style="width: 100%;">
                                    <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddCusContact" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                                    &nbsp; <img alt="" src="<?php echo $this->webroot.'img/button/cross.png'; ?>" class="btnRemoveCusContact" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <?php echo $this->Form->end(); ?>
    <div style="width: 29%; vertical-align: top; float: right;">
        <form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/customers/upload/" method="post" enctype="multipart/form-data">
            <table style="width: 100%">
                <tr>
                    <td style="width: 25%;"><label for="CustomerPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
                    <td valign="top"><input type="file" id="CustomerPhoto" name="photo" /></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <img id="photoDisplayCustomer" alt="" src="<?php echo $this->webroot; ?>img/button/no-images.png" style=" max-width: 250px; max-height: 250px;" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<div style="clear: both;"></div>
<div id="<?php echo $dialogPhoto; ?>" style="display: none;">
    <img id="<?php echo $cropPhoto; ?>" alt="" />
</div>