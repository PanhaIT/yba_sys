<?php 
echo $this->element('prevent_multiple_submit'); 
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
        $("#CompanyCompanyCategoryId").chosen({ width: 410});
        // Upload Image
        // From Action Upload Photo
        $("#<?php echo $frmName; ?>").ajaxForm({
            beforeSerialize: function($form, options) {
                extArray = new Array(".jpg",".gif",".png");
                allowSubmit = false;
                file = $("#CompanyPhoto").val();
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
                var photoFolder = "public/company_photo/tmp/";
                $("#photoDisplayCompany").attr("src", "<?php echo $this->webroot; ?>" + photoFolder + result);
                $("#<?php echo $photoNameHidden; ?>").val(result);
            }
        });
        // Action Photo Submit
        $("#CompanyPhoto").live('change', function(){
            $("#<?php echo $frmName; ?>").submit();
        });
        
        $("#CompanyEditForm").validationEngine();
        $("#CompanyEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                // if($("#CompanyPhotoOld").val() == ''){
                //     alertUploadPhotoCompany();
                //     return false;
                // }
                listbox_selectall('userCompanySelected', true);
                if($("#userCompanySelected").val() == null){
                    alertSelectUserCompany();
                    return false;
                }
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveCompany").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackCompany").click();
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Company', 'Add', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Company', 'Add', 1, '');
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
        $(".btnBackCompany").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCompany.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    function alertSelectUserCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
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
    
    function alertUploadPhotoCompany(){
        $(".btnSaveCompany").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">Please Upload Photo!</p>');
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
        <a href="" class="positive btnBackCompany">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<form id="<?php echo $frmName; ?>" action="<?php echo $this->base; ?>/companies/upload/" method="post" enctype="multipart/form-data">
    <table style="width: 100%">
        <tr>
            <td style="width: 7%;"><label for="CompanyPhoto"><?php echo TABLE_PHOTO; ?>:</label></td>
            <td valign="top"><input type="file" id="CompanyPhoto" name="photo" /></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <?php 
                if($this->data['Company']['photo'] != ''){
                    $photo = "public/company_photo/".$this->data['Company']['photo'];
                }else{
                    $photo = "img/button/no-images.png";
                }
                ?>
                <img id="photoDisplayCompany" alt="" src="<?php echo $this->webroot; ?><?php echo $photo; ?>" style=" max-width: 140px; max-height: 140px;" />
            </td>
        </tr>
    </table>
</form>
<br />
<?php 
echo $this->Form->create('Company'); 
echo $this->Form->input('id'); 
echo $this->Form->hidden('sys_code');
?>
<input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Company][new_photo]" />
<input type="hidden" id="CompanyPhotoOld" name="data[Company][old_photo]" value="<?php echo $this->data['Company']['photo']; ?>" />
<fieldset>
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td style="width: 120px;"><label for="CompanyCompanyCategoryId"><?php echo TABLE_CATEGORY; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->input('company_category_id', array('class'=>'validate[required]', 'selected' => $categorySellected, 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT)); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td style="width: 120px;"><label for="CompanyNameOther"><?php echo TABLE_NAME_IN_KHMER; ?> <span class="red">*</span> :</label></td>
            <td><?php echo $this->Form->text('name_other', array('class'=>'validate[required]')); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyCurrencyId"><?php echo TABLE_BASE_CURRENCY; ?> <span class="red">*</span> :</label></td>
            <td>
                <?php 
                // $sqlSales = mysql_query("SELECT id FROM sales_invoices WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlOrder = mysql_query("SELECT id FROM sales_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlQuote = mysql_query("SELECT id FROM quotations WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlCM    = mysql_query("SELECT id FROM sales_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlPO    = mysql_query("SELECT id FROM purchase_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlPB    = mysql_query("SELECT id FROM purchase_bills WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                // $sqlBR    = mysql_query("SELECT id FROM purchase_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                
                // if(!mysql_num_rows($sqlSales) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR)){
                //     echo $this->Form->input('currency_id', array('class'=>'validate[required]', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT)); 
                // } else {
                //     echo $this->data['Currency']['name'];
                // }
                ?>
            </td>
        </tr>
        <tr>
            <td><label for="CompanyVatNumber">VAT No :</label></td>
            <td><?php echo $this->Form->text('vat_number', array()); ?></td>
        </tr>
        <tr>
            <td><label for="CompanyVatCalculate">VAT Calculating <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php
                    if(!mysql_num_rows($sqlSales) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR)){
                    ?>
                    <select name="data[Company][vat_calculate]" id="CompanyVatCalculate" class="validate[required]">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="1" <?php if($this->data['Company']['vat_calculate'] == 1){ ?>selected="selected"<?php } ?>><?php echo TABLE_VAT_BEFORE_DISCOUNT; ?></option>
                        <option value="2" <?php if($this->data['Company']['vat_calculate'] == 2){ ?>selected="selected"<?php } ?>><?php echo TABLE_VAT_AFTER_DISCOUNT; ?></option>
                    </select>
                    <?php
                    } else {
                        if($this->data['Company']['vat_calculate'] == 1){ 
                            echo TABLE_VAT_BEFORE_DISCOUNT;
                        } else {
                            echo TABLE_VAT_AFTER_DISCOUNT;
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CompanyWebsite"><?php echo TABLE_WEBSITE; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('website', array('class' => 'validate[optional,custom[url]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="CompanyDescription"><?php echo GENERAL_DESCRIPTION; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description', array()); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="userCompany" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_companies WHERE company_id=".$this->data['Company']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userCompany', 'userCompanySelected')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userCompanySelected', 'userCompany')" />
            </td>
            <td style="vertical-align: top;">
                <select id="userCompanySelected" name="data[Company][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_companies.user_id) AS full_name FROM user_companies WHERE company_id = ".$this->data['Company']['id']);
                    while($dataDestination=mysql_fetch_array($queryDestination)){
                    ?>
                    <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive btnSaveCompany">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveCompany"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>