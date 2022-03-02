<?php 
$this->element('check_access');
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
$uploadPhoto  = "uploadPhoto".rand();
$displayPhoto = "displayPhoto".rand();
$btnRemoveUploadPhoto = "btnRemoveUploadPhoto".rand();
$loadingImage = "loadingImage".rand();
$removeImage = "removeImage".rand();
$labelDragDrop = "labelDragDrop".rand();
$dateMaxDob = date("d/m/Y", strtotime(date("Y-m-d", strtotime($dateNow)) . " -180 month"));
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<script type="text/javascript">
    $(document).ready(function(){
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        var user = $('.userCompany').bootstrapDualListbox({
            // nonSelectedListLabel: 'Non-selected',
            // selectedListLabel: 'Selected',
            // preserveSelectionOnMove: 'moved',
            moveOnSelect: false,
            // nonSelectedFilter: 'ion ([7-9]|[1][0-2])'
        });
        $("#user-add").click(function() {
            user.append('<option value="apples">Apples</option><option value="oranges" selected>Oranges</option>');
            user.bootstrapDualListbox('refresh');
        });
        $("#user-add-clear").click(function() {
            user.append('<option value="apples">Apples</option><option value="oranges" selected>Oranges</option>');
            user.bootstrapDualListbox('refresh', true);
        });

        $("#CompanyEditCompanyForm").ajaxForm({
            type: "POST",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/editCompany/<?php echo $this->data['Company']['id'];?>",
            data: $("#CompanyEditCompanyForm").serialize(),
            beforeSerialize: function(formData, formOptions) {  
                if($("#CompanyCompanyCategoryId").val() == null || $("#CompanyCompanyCategoryId").val() == ""){
                    alertSelectRequireField('category');
                    return false;
                }
                // if($("#CompanyVatCalculate").val() == null || $("#CompanyVatCalculate").val() == ""){
                //     alertSelectRequireField('vat_calculate');
                //     return false;
                // }
            },
            beforeSubmit: function (formData, formObject, formOptions) {
                $(".option_loading").show();
                $(".option_save").hide();
            },
            success: function(result) {
                $(".option_loading").hide();
                $(".option_save").show();
                $(".btnBackCompany").click();
                Swal.fire({
                    icon: "success",
                    title: result
                });
            }
        });
        $(".<?php echo $btnRemoveUploadPhoto;?>").click(function(){
            var moduleId     = $('#CompanyId').val();
            var photoName    = $('#CompanyPhotoOld').val();
            var newPhotoName = $('#<?php echo $photoNameHidden; ?>').val();
            $.showConfirm({
                title: "<?php echo TABLE_COMPANY_PHOTO;?>",
                body: "Are you sure want to delete photo?",
                textFalse: "<?php echo TABLE_CANCEL;?>",
                textTrue: "<?php echo TABLE_OK;?>",
                onSubmit: function(result) {
                    if(result){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removePhotoTmp",
                            data: 'photo='+photoName+'&module_id='+moduleId,
                            beforeSend: function(){
                                $("#<?php echo $loadingImage;?>").show();
                                $("#<?php echo $removeImage;?>").hide();
                            },
                            success: function(result){
                                $("#<?php echo $loadingImage;?>,.<?php echo $btnRemoveUploadPhoto;?>,#<?php echo $displayPhoto;?>").hide();
                                $("#<?php echo $removeImage;?>,#<?php echo $labelDragDrop;?>").show();
                                $('#<?php echo $uploadPhoto;?>').attr('class','<?php echo $uploadPhoto;?>');
                                smartImageLoader();
                            }
                        });
                    }
                },
                onDispose: function() {}
            });
        });
        FilePond.registerPlugin(
            FilePondPluginImagePreview
        );
        choicesSelect('#CompanyVatCalculate,#CompanyCompanyCategoryId');
        backEventModule(oTableCompany,"btnBackCompany");
        smartImageLoader();
    });

    function alertSelectRequireField(type){
        var bodyMessage="";
        if(type=='category'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_CATEGORY;?>";
        }else if(type=='vat_calculate'){
            bodyMessage="<?php echo TABLE_ALERT_SELECT_VAT_CALCULATE;?>";
        }
        $.showConfirm({
            title: "<?php echo TABLE_COMPANY_MANAGEMENT_INFO;?>",
            body: bodyMessage,
            textFalse: "<?php echo TABLE_CANCEL;?>",
            textTrue: "<?php echo TABLE_OK;?>",
            onSubmit: function(result) {
                if(result){}
            },
            onDispose: function() {}
        });
    }

    function getHeight(length, ratio) {
        var height = ((length)/(Math.sqrt((Math.pow(ratio, 2)+1))));
        return Math.round(height);
    }

    function getWidth(length, ratio) {
        var width = ((length)/(Math.sqrt((1)/(Math.pow(ratio, 2)+1))));
        return Math.round(width);
    }

    function smartImageLoader(){
        FilePond.create(document.querySelector('.<?php echo $uploadPhoto;?>'), {
            allowImagePreview: true,
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    // We ignore the metadata property and only send the file
                    const formData = new FormData();
                    formData.append(fieldName, file, file.name);//fieldName=image, file=object file, file.name= image name
                    const request = new XMLHttpRequest();
                    // you can change it by your client api key
                    request.open('POST', '<?php echo $this->base.'/'.$this->params['controller']; ?>/uploadPhoto');
                    request.upload.onprogress = (e) => {
                        $("#<?php echo $loadingImage;?>").show();
                        progress(e.lengthComputable, e.loaded, e.total);//e.lengthComputable=true/false,e.total=file size, e.loaded=time loading
                    };
                    request.onload = function() {
                        if (request.status >= 200 && request.status < 300) {
                            $(".<?php echo $btnRemoveUploadPhoto;?>,#<?php echo $loadingImage;?>").hide();
                            load(request.responseText);
                        } else {
                            error('oh no');
                        }
                    };
                    request.onreadystatechange = function() {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                let result      = JSON.parse(this.response);
                                let response    = JSON.parse(this.responseText);
                                let resetImgUrl = '<?php echo $this->webroot;?>img/136x155x300.png';
                                let photoFolder = 'public/company_photo/tmp/';
                                let imageUrl    = '<?php echo $this->webroot; ?>'+photoFolder+result.name;
                                let ratio       = (2/3);
                                let maxWithd    = 136;
                                let maxHeight   = 155;
                                let height = getHeight(300,ratio);
                                let width  = getWidth(height,ratio);
                                if(height>maxHeight){
                                    height = maxHeight;
                                }
                                if(width>maxWithd){
                                    width  = maxWithd;
                                }
                                $('#<?php echo $photoNameHidden; ?>').val(result.name);//set photo employee for submit to controller
                                //remove photo tmp
                                $(".filepond--file-action-button,.filepond--action-revert-item-processing").click(function(){
                                    var moduleId='';
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/removePhotoTmp",
                                        data: 'photo='+result.name+'&module_id='+moduleId,
                                        beforeSend: function(){
                                            $("#<?php echo $loadingImage;?>").show();
                                        },
                                        success: function(result){
                                            $("#<?php echo $loadingImage;?>").hide();
                                            $("#<?php echo $labelDragDrop;?>").show();
                                            $('#<?php echo $photoNameHidden; ?>').val('');
                                        }
                                    });
                                });
                                Toastify({
                                    text: "Success uploaded",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#4fbe87",
                                }).showToast();
                                console.log(response);
                            } else {
                                Toastify({
                                    text: "Failed uploading",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#ff0000",
                                }).showToast();
                                console.log("Error", this.statusText);
                            }
                        }
                    };
                    request.send(formData);
                }
            }
        });
        $(".filepond--credits").html('');
    }
</script>
<body>
<?php
echo $this->Form->create('Company', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
echo $this->Form->hidden('id', array('value'=>$this->data['Company']['id'])); 
echo $this->Form->hidden('sys_code');
?>
<div class="app form-body">
    <div class="page-title">
        <div class="row">
            <input type="hidden" id="<?php echo $photoNameHidden; ?>" name="data[Company][new_photo]" />
            <input type="hidden" id="CompanyPhotoOld" name="data[Company][old_photo]" value="<?php echo $this->data['Company']['photo']; ?>" />
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_COMPANY;?></h4>
                <p class="text-subtitle text-muted"><?php echo TABLE_FILL_INFORMATION;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCompany" is-breadcrumb="1"><?php echo TABLE_COMPANY;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title">Company Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_NAME;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name', array('class' => 'form-control','required'=>'required', 'placeholder' => TABLE_NAME ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_NAME_IN_KHMER;?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('name_other', array('class' => 'form-control','required'=>true, 'placeholder' => TABLE_NAME_IN_KHMER ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon">VAT No</label><label class="require-label">*</label>
                                            <?php echo $this->Form->text('vat_number', array('class' => 'form-control', 'placeholder' => 'VAT No' ,'style' => '')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_WEBSITE; ?></label>
                                            <?php echo $this->Form->text('website', array('class' => 'form-control', 'placeholder' => TABLE_WEBSITE ,'style' => '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="first-name-icon"><?php echo TABLE_BASE_CURRENCY; ?></label><label class="require-label">*</label><br>
                                    <?php 
                                        $sqlSales = mysql_query("SELECT id FROM sales_invoices WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlOrder = mysql_query("SELECT id FROM sales_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlQuote = mysql_query("SELECT id FROM quotations WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlCM    = mysql_query("SELECT id FROM sales_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlPO    = mysql_query("SELECT id FROM purchase_orders WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlPB    = mysql_query("SELECT id FROM purchase_bills WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        $sqlBR    = mysql_query("SELECT id FROM purchase_returns WHERE company_id = ".$this->data['Company']['id']." AND status > 0 LIMIT 1");
                                        
                                        if(!mysql_num_rows($sqlSales) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR)){
                                            echo $this->Form->input('currency_id', array('class'=>'choices form-select multiple-remove', 'label' => false, 'div' => false, 'empty' => INPUT_SELECT)); 
                                        } else {
                                            echo $this->data['Currency']['name'];
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="first-name-icon">VAT Calculating</label><br>
                                    <?php
                                    if(!mysql_num_rows($sqlSales) && !mysql_num_rows($sqlOrder) && !mysql_num_rows($sqlQuote) && !mysql_num_rows($sqlCM) && !mysql_num_rows($sqlPO) && !mysql_num_rows($sqlPB) && !mysql_num_rows($sqlBR)){
                                    ?>
                                    <select name="data[Company][vat_calculate]" id="CompanyVatCalculate" class="choices form-select multiple-remove">
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_CATEGORY; ?></label><label class="require-label">*</label>
                                            <?php echo $this->Form->input('company_category_id', array('selected' => $categorySellected, 'empty' => INPUT_SELECT,'required'=>'required','multiple' => 'multiple', 'class' => 'choices form-select multiple-remove')); ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="first-name-icon"><?php echo TABLE_NOTE; ?></label>
                                            <?php echo $this->Form->textarea('description', array('class' => 'form-control', 'placeholder' => TABLE_NOTE ,'style' => 'height:80px;')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo USER_USER_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[Company][user_id][]" multiple="multiple" class="userCompany">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_companies WHERE company_id=".$this->data['Company']['id'].")");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                    <?php 
                                        }
                                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) full_name FROM users WHERE id = user_companies.user_id) AS full_name FROM user_companies WHERE company_id = ".$this->data['Company']['id']);
                                        while($dataDestination = mysql_fetch_array($queryDestination)){
                                    ?>
                                            <option value="<?php echo $dataDestination['user_id']; ?>" selected="selected"><?php echo $dataDestination['full_name']; ?></option>
                                    <?php 
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_COMPANY_PHOTO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <?php
                                $folderName = '';
                                $dis   = '';
                                $photo = '';
                                $class = $uploadPhoto;
                                if($this->data["Company"]['photo']!=''){
                                    $folderName = 'public/company_photo/';
                                    $photo = $this->webroot.$folderName.$this->data["Company"]['photo'];
                                    $dis   = "display:none;";
                                    $class = "";
                                }
                            ?>
                            <div class="col container" style="text-align:center;width:100%;position: relative;">
                                <input type="file" name="image" class="<?php echo $class;?>" id="<?php echo $uploadPhoto;?>" style="<?php echo $dis;?>">
                                <label id="<?php echo $labelDragDrop;?>" class="labelDragDrop" style="<?php echo $dis;?>">Drag & Drop or Browse</label>
                                <a href="#" style="<?php if($this->data["Company"]['photo']==''){?> display:none; <?php } ?>" class="remove-photo-upload <?php echo $btnRemoveUploadPhoto;?>">
                                    <svg id="<?php echo $removeImage;?>"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>x-circle" /></svg>
                                    <img id="<?php echo $loadingImage;?>" src="<?php echo $this->webroot; ?>img/cycle_loading.gif" style="display:none;"/>
                                </a>
                                <img id="<?php echo $displayPhoto;?>" src="<?php echo $photo;?>" style="width:100%;height:auto; text-align: center;vertical-align: middle;"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackCompany text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
        <button type="reset" class="btn btn-primary text-btn-cus" >
            <svg style="" class="icon-svg-reset bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-counterclockwise" /></svg>
            <label class="label_crud"><?php echo 'Reset'; ?></label>
        </button>
        <a style="color:white;">
            <button type="submit" class="btn btn-primary btnSaveCompany text-btn-cus" style="">
                <span class="option_save"><svg class="icon-svg-save bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>save" /></svg><label class="label_crud label_save"><?php echo ACTION_SAVE; ?></label></span>
                <span class="option_loading" style="display:none;"><img src="<?php echo $this->webroot;?>assets/vendors/svg-white-loaders/oval.svg" class="icon_loading"><label class="label_crud"><?php echo ACTION_LOADING; ?></label></span>
            </button>
        </a>
    </div>
</div>
<?php echo $this->Form->end(); ?>
