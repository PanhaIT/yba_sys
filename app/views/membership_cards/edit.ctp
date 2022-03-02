<?php
echo $this->element('prevent_multiple_submit');
$rnd = rand();
$frmName = "frm" . rand();
?>
<script type="text/javascript">
    var selected;
    var indexRowVendor = 0;
    var jcrop_api = '';
    var x, y, x2, y2, w, h;
    var obj;
    $(document).ready(function() {
        checkLocationWithCompany();
        // Prevent Key Enter
        preventKeyEnter();
        if ($("#MembershipCardCardDateStart").val() != "" && $("#MembershipCardCardDateEnd").val() != "") {
            var days = getDateDiff($("#MembershipCardCardDateEnd").val(), $("#MembershipCardCardDateStart").val());
            $("#MembershipCardTotalDate").val(days);
        }
        $("#MembershipCardEditForm").validationEngine();
        // Action Save
        $("#MembershipCardEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                if ($("#MembershipCardCompanyId").val() == "") {
                    alertSelectCompanyCus();
                    return false;
                }
                $(".float").each(function() {
                    $(this).val($(this).val().replace(/,/g, ""));
                });
                $("#MembershipCardDob").datepicker("option", "dateFormat", "yy-mm-dd");
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtMembershipCardSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackMembershipCard").click();
                // Alert Message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>' + result + '</p>');
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui) {
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
        
        $(".float").autoNumeric();
        $("#MembershipCardTypeOfMembershipCardId").change(function() {
            var value = $(this).val();
            if (value == '1') {                
                $("#discount-save").show();
                $("#top-up-discount").hide();
                $("#MembershipCardAmountInDollar").val("");
                $("#MembershipCardDiscountPercent").val("");
            } else if (value == '2') {
                $("#discount-save").hide();
                $("#top-up-discount").show();
                $("#MembershipCardDiscountPercent").val("");
                $("#MembershipCardPointInDollar").val("");
                $("#MembershipCardPointPercent").val("");
                
            }else{
                $("#discount-save").hide();
                $("#top-up-discount").hide();
            }
        });

        $("#MembershipCardCompanyId").change(function() {
            checkLocationWithCompany();
        });       

        $("#MembershipCardCgroupId").click(function() {
            if ($("#MembershipCardCompanyId").val() == "") {
                alertSelectCompanyCus();
                return false;
            } else {
                if($(this).val() != ""){
                    checkCustomerWithCgroup();
                } 
            }
        });
        
        $("#MembershipCardDob").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            yearRange: '-100:-0',
            maxDate: 0,
            beforeShow: function() {
                setTimeout(function() {
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#MembershipCardDob").change(function() {
            var now = (new Date()).getFullYear();
            var age = now - $("#MembershipCardDob").val().split("/")[2];
            $('#MembershipCardAge').val(age);
        });
        $("#MembershipCardAge").keyup(function() {
            var now = (new Date()).getFullYear();
            var age = parseUniInt($("#MembershipCardAge").val());
            var year = now - age;
            if ($("#MembershipCardDob").val() != '') {
                var dob = $("#MembershipCardDob").val().substr(0, 6) + year;
            } else {
                var dob = '01/01/' + year;
            }
            $('#MembershipCardDob').val(dob);
        });

        var dates = $("#MembershipCardCardDateStart, #MembershipCardCardDateEnd").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
                    changeYear: true,
                    beforeShow: function() {
                        setTimeout(function() {
                            $("#ui-datepicker-div").css("z-index", 1000);
                        }, 10);
                    },
            onSelect: function(selectedDate) {
                var option = this.id == "MembershipCardCardDateStart" ? "minDate" : "maxDate",
                        instance = $(this).data("datepicker");
                date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings);
                dates.not(this).datepicker("option", option, date);
                if ($("#MembershipCardCardDateStart").val() != "" && $("#MembershipCardCardDateEnd").val() != "") {
                    var days = getDateDiff($("#MembershipCardCardDateEnd").val(), $("#MembershipCardCardDateStart").val());
                    $("#MembershipCardTotalDate").val(days);
                }
            }

        }).unbind("blur");
        $(".date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            yearRange: '-100:-0',
            beforeShow: function() {
                setTimeout(function() {
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");

        // Action Back
        $(".btnBackMembershipCard").click(function(event) {
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMembershipCard.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent();
            var leftPanel = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();
            rightPanel.html("");
            leftPanel.show("slide", {direction: "left"}, 500);
        });
        
        $(".deleteCustomer").click(function(){
            $(".searchCustomer").show();
            $(".deleteCustomer").hide();
            $("#MembershipCardCustomerId").val('');
            $("#MembershipCardSex").val('');
            $("#MembershipCardEmail").val('');
            $("#MembershipCardAddress").val('');
            $("#MembershipCardMainNumberAdd").val('');
            $("#MembershipCardDob").val('');
            $("#MembershipCardAge").val('');
            $("#MembershipCardName").val('');
        });
        $(".searchCustomer").click(function(){
            var companyId     = $("#MembershipCardCompanyId").val();
            var customerGroup     = $("#MembershipCardCgroupId").val();
            if(companyId != ''){
                $.ajax({
                    type:   "POST",
                    url:    "<?php echo $this->base . '/' . $this->params['controller']; ?>/customer/"+companyId+"/"+customerGroup,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(msg){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        $("#dialog").html(msg).dialog({
                            title: '<?php echo PRODUCT_PARENT; ?>',
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
                                    if($("input[name='chkCustomer']:checked").val()){
                                        // Customer
                                        var customerId     = $("input[name='chkCustomer']:checked").val();
                                        var customerCode   = $("input[name='chkCustomer']:checked").attr("rel");
                                        var customerNameEn = $("input[name='chkCustomer']:checked").attr("name-us");
                                        var customerSex = $("input[name='chkCustomer']:checked").attr("sex");
                                        var customerPhone = $("input[name='chkCustomer']:checked").attr("main-phone");
                                        var customerEmail = $("input[name='chkCustomer']:checked").attr("email");
                                        var customerDob = $("input[name='chkCustomer']:checked").attr("dob");
                                        var customerAddress = $("input[name='chkCustomer']:checked").attr("address");
                                        
                                        $("#MembershipCardCustomerId").val(customerId);
                                        $("#MembershipCardEmail").val(customerEmail);
                                        $("#MembershipCardAddress").val(customerAddress);
                                        $("#MembershipCardMainNumberAdd").val(customerPhone);
                                        $("#MembershipCardName").val(customerCode+"-"+customerNameEn);
                                        
                                        
                                        if (customerSex != "") {
                                            $("#MembershipCardSex").find("option").each(function() {
                                                if ($(this).val() == customerSex) {
                                                    $(this).attr("selected", true);
                                                }
                                            });
                                        }
                                        if (customerDob != "") {
                                            $("#MembershipCardDob").val(customerDob);
                                            var now = (new Date()).getFullYear();
                                            var age = now - $("#MembershipCardDob").val().split("-", 1);
                                            $('#MembershipCardAge').val(age);
                                            var date = customerDob.split("-");
                                            var customerDob =  date[2]+'/'+date[1]+'/'+date[0];
                                            $("#MembershipCardDob").val(customerDob);
                                            
                                        }
                                        
                                        $(".searchCustomer").hide();
                                        $(".deleteCustomer").show();
                                    }
                                    $(this).dialog("close");
                                }
                            }
                        });
                    }
                });
            }else{
                alertSelectCompanyPro();
            }
        });
    });
    
    function alertSelectCompanyPro(){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SELECT_COMPANY_FIRST; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $("#ProductCompanyId").select();
                }
            }
        });
    }   
    
    function showCoords(c)
    {
        x = c.x;
        y = c.y;
        x2 = c.x2;
        y2 = c.y2;
        w = c.w;
        h = c.h;
    }
    function comboRefesh() {
        selected = new Array();
        $("#MembershipCardCgroupId").each(function() {
            if ($(this).val() != '') {
                selected.push($(this).val());
            }
        });
    }
    function checkLocationWithCompany() {
        $(".cgroup option").each(function() {
            if ($(this).attr("company_id")) {
                var companyId = $(this).attr("company_id").split(",");
                if (companyId.indexOf($("#MembershipCardCompanyId").val()) == -1) {
                    $(this).removeAttr('selected');
                    $(this).hide();
                } else {
                    $(this).show();
                }
            } else {
                $(this).removeAttr('selected');
                $(this).hide();
            }
        });
    }
    function checkCustomerWithCgroup() {
        $(".customer option").each(function() {
            if ($(this).attr("cgroup_id")) {
                var companyId = $(this).attr("cgroup_id").split(",");
                if (companyId.indexOf($("#MembershipCardCgroupId").val()) == -1) {
                    $(this).removeAttr('selected');
                    $(this).hide();
                } else {
                    $(this).show();
                }
            } else {
                $(this).removeAttr('selected');
                $(this).hide();
            }
        });
    }

    function getDateDiff(time1, time2) {
        var str1 = time1.split('-');
        var str2 = time2.split('-');
        var t1 = new Date(str1[0], str1[1] - 1, str1[2]);
        var t2 = new Date(str2[0], str2[1] - 1, str2[2]);

        var diffMS = t1 - t2;
        console.log(diffMS + ' ms');

        var diffS = diffMS / 1000;
        console.log(diffS + ' ');

        var diffM = diffS / 60;
        console.log(diffM + ' minutes');

        var diffH = diffM / 60;
        console.log(diffH + ' hours');

        var diffD = diffH / 24;
        console.log(diffD + ' days');

        return diffD;
    }

    function alertSelectCompanyCus() {
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_SELECT_COMPANY; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position: 'center',
            open: function(event, ui) {
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".btnSaveMembershipCard").removeAttr('disabled');
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMembershipCard">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('MembershipCard'); ?>     
<?php echo $this->Form->input('id', array('value' => $this->data['MembershipCard']['id'], 'type' => 'hidden')); ?>
<?php
$age= '';
$dob = "";
if($this->data['Customer']['dob']!="0000-00-00") {
   $dob = date("d/m/Y", strtotime($this->data['Customer']['dob']));
   list($y,$m,$d) = split('-',date($this->data['Customer']['dob']));
   list($ycur,$mcur,$dcur) = split('-',date('Y-m-d'));
   $age = $ycur - $y;
}
?>
<fieldset>
    <legend><?php __(MENU_MEMBERSHIP_CARD_MANAGEMENT_INFO); ?></legend>   
    <table style="width: 100%;">
        <tr>
            <td style="width: 14%;"><label for="MembershipCardCardId"><?php echo TABLE_MEMBERSHIP_CARD_ID; ?> <span class="red">*</span> :</label></td>
            <td style="width: 35%;">
                <div class="inputContainer" style="width: 100%;">
                    <input type="hidden" id="tableName" name="tableName" value="membership_cards" />
                    <input type="hidden" id="fieldCurrentId" name="fieldCurrentId" value="<?php echo $this->data['MembershipCard']['id'];?>" />
                    <input type="hidden" id="fieldName" name="fieldName" value="card_id" />
                    <input type="hidden" id="fieldCondition" name="fieldCondition" value="is_active=1" />
                    <?php echo $this->Form->text('card_id', array('class' => 'validate[required]', 'style' => 'width: 64%;')); ?>
                </div>
            </td>
            <td style="width: 15%;"><label for="MembershipCardCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td style="width: 35%;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('company_id', array('label' => false, 'empty' => INPUT_SELECT, 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 295px;')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="MembershipCardCgroupId"><?php echo TABLE_CUSTOMER_GROUP; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <select id="MembershipCardCgroupId" class="cgroup" style="width: 295px;" data-placeholder="<?php echo INPUT_SELECT; ?>" name="data[MembershipCard][cgroup_id]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        foreach ($cgroups as $cgroup) {
                            $queryCustomerGroupName = mysql_query("SELECT (SELECT GROUP_CONCAT(company_id) FROM cgroup_companies WHERE cgroup_id = cgroups.id GROUP BY cgroup_id) AS company_id,name,id FROM cgroups WHERE is_active=1 AND id=" . $cgroup['Cgroup']['id']);
                            if (mysql_num_rows($queryCustomerGroupName)) {
                                $dataCustomerGroupName = mysql_fetch_array($queryCustomerGroupName);
                                ?>
                                <option <?php if($cgroup['Cgroup']['id']==$dataCustomerGroupName['id']) { echo 'selected="selected"';}?>  value="<?php echo $cgroup['Cgroup']['id']; ?>" company_id="<?php echo $dataCustomerGroupName['company_id']; ?>"><?php echo $dataCustomerGroupName['name']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>                   
                </div>
            </td>
            <td><label for="MembershipCardSex"><?php echo TABLE_SEX; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('sex', array('empty' => INPUT_SELECT, 'selected' => $this->data['Customer']['sex'], 'label' => false, 'class' => 'validate[required]')); ?>
                </div>
            </td>                        
        </tr>
        <tr>            
            <td><label for="MembershipCardCustomerId"><?php echo TABLE_CUSTOMER; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <input type="hidden" id="MembershipCardCustomerId" name="data[MembershipCard][customer_id]" value="<?php echo $this->data['Customer']['id'];?>">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]', 'id' => 'MembershipCardName', 'style' => 'width: 64%;', 'value' => $this->data['Customer']['name'])); ?>                    
                    <img alt="Search" align="absmiddle" style="display: none; cursor: pointer; width: 22px; height: 22px;" class="searchCustomer" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                    <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="deleteCustomer" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                </div>                                
            </td>  
            <td style="width: 15%;"><label for="MembershipCardMainNumberAdd"><?php echo TABLE_TELEPHONE; ?> <span class="red">*</span>:</label></td>
            <td style="width: 35%;">
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('main_number', array('class' => 'validate[required]', 'value' => $this->data['Customer']['main_number'], 'id' => 'MembershipCardMainNumberAdd', 'style' => 'width: 64%;')); ?>
                </div>
            </td>        
        </tr>
        <tr>

        </tr>
        <tr>
            <td><label for="MembershipCardEmail"><?php echo TABLE_EMAIL; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('email', array('class' => 'validate[optional,custom[email]]', 'style' => 'width: 64%;', 'value' => $this->data['Customer']['email'])); ?>
                </div>
            </td>
            <td><label for="MembershipCardDob"><?php echo TABLE_DOB; ?>:</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('dob', array('readonly' => true, 'style' => 'width: 64%;', 'value' => $dob)); ?>                    
                    <span>
                        <?php echo TABLE_AGE; ?>
                        <?php echo $this->Form->text('age', array('class' => 'validate[optional,max[150]', 'style' => 'width:30px;', 'value' => $age)); ?>
                    </span>
                </div>
            </td>
        </tr>
        <tr>            
            <td><label for="MembershipCardAddress"><?php echo TABLE_ADDRESS; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('address', array('style' => 'width: 64%;', 'value' => $this->data['Customer']['address'])); ?> 
                </div>             
            </td>
        </tr>
        <tr>

            <td><label for="MembershipCardTypeOfMembershipCardId"><?php echo TABLE_MEMBERSHIP_CARD_TYPE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->input('type_of_membership_card_id', array('class' => 'validate[required]', 'label' => false, 'empty' => INPUT_SELECT, 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 295px;')); ?>
                </div>
            </td>
        </tr>        
    </table>
    <table style="width: 100%;">
        <tr>            
            <td style="width: 14.1%;"><label for="MembershipCardCardDateStart"><?php echo TABLE_DATE_START; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('card_date_start', array('class' => 'validate[required]', 'style' => 'width: 284px;')); ?> 
                </div>             
            </td>
            <td><label for="MembershipCardCardDateEnd"><?php echo TABLE_DATE_END; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('card_date_end', array('class' => 'validate[required]', 'style' => 'width: 284px;')); ?> 
                </div>             
            </td>
            <td><label for="MembershipCardTotalDate"><?php echo TABLE_DAYS; ?> :</label></td>
            <td>
                <div class="inputContainer" style="width: 100%;">
                    <?php echo $this->Form->text('total_date', array('style' => 'width: 284px;', 'readonly' => true)); ?> 
                </div>             
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php echo MENU_CARD_DISCOUNT_INFO; ?></legend>
    <table id="discount-save" style="width: 100%;<?php if($this->data['MembershipCard']['type_of_membership_card_id']==2) { echo 'display:none;';}?>" cellspacing="0">               
        <tr>
            <th style="width: 25%;"><label for=""><?php echo TABLE_ACCOUNT; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 25%;"><label for=""><?php echo GENERAL_DISCOUNT_PERCENT; ?> <span class="red">*</span> :</label></th> 
            <th style="width: 25%;"><label for=""><?php echo TABLE_TOTAL_POINT; ?> <span class="red">*</span> :</label></th>                
            <th style="width: 25%;"><label for=""><?php echo 'Apply Point as Dollars'; ?> <span class="red">*</span> :</label></th>  
        </tr> 
        <tr>
            <td style="width: 25%;">
                <?php
                $filter = "AND chart_account_type_id IN (15)";
                ?>
                <div class="inputContainer">
                    <select id="MembershipCardChartAccountId" name="data[MembershipCard][account_id1]" class="sales_order_coa_id validate[required]" style="width:90%; height: 30px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $query[0] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE is_active=1 " . $filter . " ORDER BY account_codes");
                        while ($data[0] = mysql_fetch_array($query[0])) {
                            $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[0]['id']);
                            ?>
                            <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[0]['id'] == $arAccountId ? 'selected="selected"' : ''; ?>><?php echo $data[0]['name']; ?></option>
                            <?php
                            $query[1] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[0]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                            while ($data[1] = mysql_fetch_array($query[1])) {
                                $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[1]['id']);
                                ?>
                                <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[1]['id'] == $arAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                <?php
                                $query[2] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[1]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                while ($data[2] = mysql_fetch_array($query[2])) {
                                    $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[2]['id']);
                                    ?>
                                    <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[2]['id'] == $arAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                    <?php
                                    $query[3] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[2]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                    while ($data[3] = mysql_fetch_array($query[3])) {
                                        $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[3]['id']);
                                        ?>
                                        <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[3]['id'] == $arAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                        <?php
                                        $query[4] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[3]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                        while ($data[4] = mysql_fetch_array($query[4])) {
                                            $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[4]['id']);
                                            ?>
                                            <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[4]['id'] == $arAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                            <?php
                                            $query[5] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[4]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                            while ($data[5] = mysql_fetch_array($query[5])) {
                                                $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[5]['id']);
                                                ?>
                                                <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[5]['id'] == $arAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>                
            </td>
            <td style="width: 25%;">
                <?php echo $this->Form->text('discount_percent', array('name' => 'data[MembershipCard][discount_percent1]', 'class' => 'validate[required,max[100]] float', 'style' => 'width:80%', 'maxlength' => '3')); ?>
            </td>
            <td style="width: 25%;">
                <?php echo $this->Form->text('total_point', array('class' => 'float','value'=>$this->data['MembershipCard']['total_point'], 'readonly' =>true, 'style' => 'width:80px;')); ?> Point(s)
            </td>
            <td style="width: 25%;">
                <?php echo $this->Form->text('exchange_point', array('class' => 'validate[required,max[100]] float','style' => 'width:80px;', 'maxlength' => '3')); ?> Point(s) = <?php echo $this->Form->text('point_in_dollar', array('class' => 'validate[required] float','style' => 'width:80px;')); ?> Dollar(s)
            </td>
        </tr>
    </table>
    
    <table id="top-up-discount" style="width: 100%;<?php if($this->data['MembershipCard']['type_of_membership_card_id']==1) { echo 'display:none;';}?>" cellspacing="0">               
        <tr>
            <th style="width: 35%;"><label for=""><?php echo TABLE_ACCOUNT; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 35%;"><label for=""><?php echo GENERAL_CASH_DOLLAR; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 30%;"><label for=""><?php echo GENERAL_DISCOUNT_PERCENT; ?> <span class="red">*</span> :</label></th>            
        </tr> 
        <tr>
            <td style="width: 35%;">
                <?php
                $filter = "AND chart_account_type_id IN (8)";
                ?>
                <div class="inputContainer">
                    <select id="MembershipCardChartAccountId" name="data[MembershipCard][account_id2]" class="sales_order_coa_id validate[required]" style="width:90%; height: 30px;">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <?php
                        $query[0] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE is_active=1 " . $filter . " ORDER BY account_codes");
                        while ($data[0] = mysql_fetch_array($query[0])) {
                            $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[0]['id']);
                            ?>
                            <option value="<?php echo $data[0]['id']; ?>" chart_account_type_name="<?php echo $data[0]['chart_account_type_name']; ?>" company_id="<?php echo $data[0]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[0]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?>><?php echo $data[0]['name']; ?></option>
                            <?php
                            $query[1] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[0]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                            while ($data[1] = mysql_fetch_array($query[1])) {
                                $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[1]['id']);
                                ?>
                                <option value="<?php echo $data[1]['id']; ?>" chart_account_type_name="<?php echo $data[1]['chart_account_type_name']; ?>" company_id="<?php echo $data[1]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[1]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                                <?php
                                $query[2] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[1]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                while ($data[2] = mysql_fetch_array($query[2])) {
                                    $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[2]['id']);
                                    ?>
                                    <option value="<?php echo $data[2]['id']; ?>" chart_account_type_name="<?php echo $data[2]['chart_account_type_name']; ?>" company_id="<?php echo $data[2]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[2]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                    <?php
                                    $query[3] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[2]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                    while ($data[3] = mysql_fetch_array($query[3])) {
                                        $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[3]['id']);
                                        ?>
                                        <option value="<?php echo $data[3]['id']; ?>" chart_account_type_name="<?php echo $data[3]['chart_account_type_name']; ?>" company_id="<?php echo $data[3]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[3]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                        <?php
                                        $query[4] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[3]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                        while ($data[4] = mysql_fetch_array($query[4])) {
                                            $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[4]['id']);
                                            ?>
                                            <option value="<?php echo $data[4]['id']; ?>" chart_account_type_name="<?php echo $data[4]['chart_account_type_name']; ?>" company_id="<?php echo $data[4]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[4]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                            <?php
                                            $query[5] = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name,(SELECT name FROM chart_account_types WHERE id=chart_accounts.chart_account_type_id) AS chart_account_type_name,(SELECT GROUP_CONCAT(company_id) FROM chart_account_companies WHERE chart_account_id=chart_accounts.id) AS company_id FROM chart_accounts WHERE parent_id=" . $data[4]['id'] . " AND is_active=1 " . $filter . " ORDER BY account_codes");
                                            while ($data[5] = mysql_fetch_array($query[5])) {
                                                $queryIsNotLastChild = mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=" . $data[5]['id']);
                                                ?>
                                                <option value="<?php echo $data[5]['id']; ?>" chart_account_type_name="<?php echo $data[5]['chart_account_type_name']; ?>" company_id="<?php echo $data[5]['company_id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild) ? 'disabled="disabled"' : ''; ?> <?php echo $data[5]['id'] == $arAccountTopId ? 'selected="selected"' : ''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>                
            </td>
            <td style="width: 35%;">
                <?php echo $this->Form->text('amount_in_dollar', array('class' => 'validate[required] float', 'style' => 'width:80%')); ?>
            </td>
            <td style="width: 30%;">
                <?php echo $this->Form->text('discount_percent', array('name' => 'data[MembershipCard][discount_percent2]', 'class' => 'validate[required,max[100]] float', 'style' => 'width:80%', 'maxlength' => '3')); ?>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveMembershipCard">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtMembershipCardSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>