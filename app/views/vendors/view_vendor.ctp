<?php 
$this->element('check_access');
// include("includes/function.php");
// Prevent Button Submit
// echo $this->element('prevent_multiple_submit'); 
$rnd = rand();
$frmName = "frm" . rand();
$dialogPhoto = "dialogPhoto" . rand();
$cropPhoto = "cropPhoto" . rand();
$photoNameHidden = "photoNameHidden" . rand();
$dateNow = date("Y")."-12"."-31";
?>
<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html  lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function(){
        backEventView(oTableVendor,"btnBackVendor");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_VENDOR;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VENDOR_MANAGEMENT_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackVendor" is-breadcrumb="1"><?php echo TABLE_VENDOR;?></a></li>
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
                    <label class="card-title"><?php echo MENU_VENDOR_MANAGEMENT_INFO; ?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_VENDOR_NAME;?></th>
                                        <td class="text-bold-500"><?php echo $this->data['Vendor']['name']; ?></td>
                                        <th><?php echo TABLE_GROUP;?></th>
                                        <td class="text-bold-500">
                                        <?php 
                                            $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) AS name FROM vgroups WHERE id IN (SELECT vgroup_id FROM vendor_vgroups WHERE vendor_id = {$this->data['Vendor']['id']})");
                                            $rowGroup = mysql_fetch_array($sqlGroup);
                                            echo $sqlGroup[0];
                                        ?>
                                        </td>
                                        <td rowspan="4" class="border-bottom-cus" style="text-align: center">
                                            <?php
                                                $folderName = '';
                                                $dis   = '';
                                                $class ="imgbb-filepond";
                                                if($this->data["Vendor"]['photo']!=''){
                                                    $folderName = 'public/vendor_photo/';
                                                    $photo = $this->data["Vendor"]['photo'];
                                                    $dis   = "display:none;";
                                                    $class = "";
                                                }else{
                                                    $folderName = 'img/';
                                                    $photo = '136x155x300.png';
                                                }
                                            ?>
                                            <img id="loadingImage" src="<?php echo $this->webroot.$folderName.$photo;?>" style="width:160px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_VENDOR_NUMBER;?></th>
                                        <td class="text-bold-500"><?php echo $this->data['Vendor']['vendor_code']; ?></td>
                                        <th><?php echo TABLE_PAYMENT_TERMS;?></th>
                                        <td class="text-bold-500">
                                            <?php 
                                                if(!empty($this->data['Vendor']['payment_term_id'])){
                                                    $sqlTerm = mysql_query("SELECT name FROM payment_terms WHERE id  = {$this->data['Vendor']['payment_term_id']}");
                                                    $rowTerm = mysql_fetch_array($sqlTerm);
                                                    echo $rowTerm[0];
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_COMPANY;?></th>
                                        <td class="text-bold-500">
                                        <?php 
                                            if(!empty($this->data['Vendor']['id'])){
                                                $sqlCompany = mysql_query("SELECT name FROM companies WHERE id  = (SELECT vendor_id FROM vendor_companies WHERE vendor_id={$this->data['Vendor']['id']})");
                                                $rowCompany = mysql_fetch_array($sqlCompany);
                                                echo $rowCompany[0];
                                            }
                                        ?>
                                        </td>
                                        <th><?php echo TABLE_COUNTRY; ?></th>
                                        <td class="text-bold-500"><?php echo $this->data['Country']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_NOTE; ?></th>
                                        <td colspan="3" class="text-bold-500 border-bottom-cus"><?php echo $this->data['Vendor']['note'];?></td>
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
                    <label class="card-title"><?php echo TABLE_CONTACT_INFORMATION; ?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th style="width:40%;"><?php echo TABLE_TELEPHONE_WORK; ?></th>
                                    <td class="text-bold-500"><?php echo $this->data['Vendor']['work_telephone']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo TABLE_TELEPHONE_OTHER; ?></th>
                                    <td class="text-bold-500"><?php echo $this->data['Vendor']['other_number']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo TABLE_EMAIL; ?></th>
                                    <td class="text-bold-500"><?php echo $this->data['Vendor']['email_address']; ?></td>
                                </tr>
                                <tr>
                                    <th style="width:40%;" class="border-bottom-cus"><?php echo TABLE_ADDRESS; ?></th>
                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Vendor']['address']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackVendor text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

