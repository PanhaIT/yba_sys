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
        backEventView(oTableCustomer,"btnBackCustomer");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_CUSTOMER;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VIEW_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCustomer" is-breadcrumb="1"><?php echo MENU_CUSTOMER;?></a></li>
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
                    <label class="card-title"><?php echo MENU_CUSTOMER_MANAGEMENT_INFO; ?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_NAME_IN_ENGLISH;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Customer']['name']; ?></td>
                                        <th><?php echo TABLE_CUSTOMER_NUMBER;?> : </th>
                                        <td class="text-bold-500">
                                            <?php echo $this->data['Customer']['customer_code']; ?>
                                        </td>
                                        <td rowspan="4" class="border-bottom-cus" style="text-align :  center">
                                            <?php
                                                $folderName = '';
                                                $dis   = '';
                                                $class ="imgbb-filepond";
                                                if($this->data["Customer"]['photo']!=''){
                                                    $folderName = 'public/customer_photo/';
                                                    $photo = $this->data["Customer"]['photo'];
                                                    $dis   = "display : none;";
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
                                        <th><?php echo TABLE_NAME_IN_KHMER;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Customer']['name_kh']; ?></td>
                                        <th><?php echo TABLE_GROUP;?> : </th>
                                        <td class="text-bold-500">
                                            <?php  
                                                $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) FROM cgroups WHERE id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = ".$this->data['Customer']['id'].")");
                                                $rowGroup = mysql_fetch_array($sqlGroup);
                                                echo $rowGroup[0];
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_PAYMENT_TERMS;?> : </th>
                                        <td class="text-bold-500">
                                            <?php echo $this->data['PaymentTerm']['name']; ?>
                                        </td>
                                        <th><?php echo TABLE_PAYMENT_EVERY; ?> : </th>
                                        <td class="text-bold-500"></td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_NOTE; ?> : </th>
                                        <td colspan="3" class="text-bold-500 border-bottom-cus"><?php echo $this->data['Customer']['note'];?></td>
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
                    <label class="card-title"><?php echo TABLE_CONTACT_INFORMATION; ?> : </label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th style="width:40%;"><?php echo TABLE_TELEPHONE; ?> : </th>
                                    <td class="text-bold-500"><?php echo $this->data['Customer']['main_number']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo TABLE_MOBILE; ?> : </th>
                                    <td class="text-bold-500"><?php echo $this->data['Customer']['mobile_number']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo TABLE_EMAIL; ?> : </th>
                                    <td class="text-bold-500"><?php echo $this->data['Customer']['email']; ?></td>
                                </tr>
                                <tr>
                                    <th style="width:40%;" class="border-bottom-cus"><?php echo TABLE_ADDRESS; ?> : </th>
                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Customer']['address']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="z-index : 100;padding : 1px 15px 5px 0px;margin : 0px 0px 0px 0px; bottom : 0px;position : fixed;width:100%; height : 50px;">
        <a style="color : white;">
            <button  class="btn btn-primary btnBackCustomer text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink : href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

