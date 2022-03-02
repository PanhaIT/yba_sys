<?php 
$this->element('check_access');
// include("includes/function.php");
// Prevent Button Submit
// echo $this->element('prevent_multiple_submit'); 
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$uploadPhoto  = "uploadPhoto".rand();
$displayPhoto = "displayPhoto".rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http : //www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http : //www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function(){
        backEventView(oTableCompany,"btnBackCompany");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_COMPANY;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VIEW_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCompany" is-breadcrumb="1"><?php echo TABLE_COMPANY;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
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
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Company']['name']; ?></td>
                                        <th><?php echo TABLE_NAME_IN_KHMER;?> : </th>
                                        <td class="text-bold-500">
                                            <?php echo $this->data['Company']['name_other']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>VAT No :</th>
                                        <td class="text-bold-500"><?php echo $this->data['Company']['vat_number']; ?></td>
                                        <th><?php echo TABLE_WEBSITE;?> : </th>
                                        <td class="text-bold-500">
                                            <?php echo $this->data['Company']['website']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_BASE_CURRENCY;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Currency']['name']; ?></td>
                                        <th>VAT Calculating : </th>
                                        <td class="text-bold-500">
                                            <?php 
                                            if($this->data['Company']['vat_calculate'] == 1){
                                                echo TABLE_VAT_BEFORE_DISCOUNT; 
                                            } else {
                                                echo TABLE_VAT_AFTER_DISCOUNT; 
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_NOTE; ?> : </th>
                                        <td colspan="3" class="text-bold-500 border-bottom-cus"><?php echo $this->data['Company']['description'];?></td>
                                    </tr>
                                </tbody>
                            </table>                            
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
                                $photo = '#';
                                $dis   = "display:none;";
                                if($this->data["Company"]['photo']!=''){
                                    $photo = $this->webroot.'public/company_photo/'.$this->data["Company"]['photo'];
                                    $dis   = '';
                                }
                            ?>
                            <div class="col container" style="text-align:center;width:100%;position: relative;">
                                <img src="<?php echo $photo;?>" style="vertical-align: middle;width:100%;height:auto;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index : 100;padding : 1px 15px 5px 0px;margin : 0px 0px 0px 0px; bottom : 0px;position : fixed;width:100%; height : 50px;">
        <a style="color : white;">
            <button  class="btn btn-primary btnBackCompany text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink : href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

