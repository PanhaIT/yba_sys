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
<body>
<script type="text/javascript">
    $(document).ready(function(){
        backEventView(oTableEmployee,"btnBackEmployee");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_EMPLOYEE;?></h4>
                <p class="text-subtitle text-muted">Employee information</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackEmployee" is-breadcrumb="1">Employee</a></li>
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
                    <label class="card-title">Personal Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th>Latin Name</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['name']; ?></td>
                                        <th>Sex</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['sex']; ?></td>
                                        <td rowspan="5" class="border-bottom-cus" style="text-align: center">
                                            <?php
                                                $folderName = '';
                                                $dis   = '';
                                                $class ="imgbb-filepond";
                                                if($this->data["Employee"]['photo']!=''){
                                                    $folderName = 'public/employee_photo/';
                                                    $photo = $this->data["Employee"]['photo'];
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
                                        <th>Khmer Name</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['name_kh']; ?></td>
                                        <th>Date of Birth</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['dob']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Employee ID</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['employee_code']; ?></td>
                                        <th>Employee Type</th>
                                        <td class="text-bold-500"><?php echo $this->data['EmployeeType']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Company</th>
                                        <td colspan="3" class="text-bold-500">
                                        <?php 
                                            $sqlCompany=mysql_query("SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM companies c INNER JOIN employee_companies ec ON ec.company_id=c.id INNER JOIN employees e ON e.id=ec.employee_id WHERE c.is_active=1 AND e.id='".$this->data['Employee']['id']."'");
                                            if(mysql_num_rows($sqlCompany)){
                                                $rowCompany = mysql_fetch_array($sqlCompany);
                                                echo $rowCompany[0];
                                            }
                                        ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus">Employee Group</th>
                                        <td colspan="3" class="text-bold-500 border-bottom-cus">
                                        <?php 
                                            $sqlEgroup=mysql_query("SELECT GROUP_CONCAT(eg.name SEPARATOR ', ') FROM egroups eg INNER JOIN employee_egroups emg ON emg.egroup_id=eg.id WHERE eg.is_active=1 AND emg.employee_id='".$this->data['Employee']['id']."'");
                                            if(mysql_num_rows($sqlEgroup)){
                                                $rowEmployeeGroup = mysql_fetch_array($sqlEgroup);
                                                echo $rowEmployeeGroup[0];
                                            }
                                        ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col" style="margin-top:13px;">
                            <div class="card">
                                <div class="card-header">
                                    <label class="card-title">Address Information</label>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <table class="table table-hover mb-0 view_item_list">
                                            <tbody>
                                                <tr>
                                                    <th>No</th>
                                                    <td class="text-bold-500"><?php echo $this->data['Employee']['house_no']; ?></td>
                                                    <th>Street</th>
                                                    <td class="text-bold-500"><?php echo $this->data['Employee']['street']; ?></td>
                                                    <th>Village</th>
                                                    <td class="text-bold-500"><?php echo $this->data['Employee']['village']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="border-bottom-cus">Commune</th>
                                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['commune']; ?></td>
                                                    <th class="border-bottom-cus">District</th>
                                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['district']; ?></td>
                                                    <th class="border-bottom-cus">Province/City</th>
                                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['province']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title">Contact Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th style="width:40%;">Mobile</th>
                                    <td class="text-bold-500"><?php echo $this->data['Employee']['personal_number']; ?></td>
                                </tr>
                                <tr>
                                    <th>Telephone</th>
                                    <td class="text-bold-500"><?php echo $this->data['Employee']['other_number']; ?></td>
                                </tr>
                                <tr>
                                    <th class="border-bottom-cus">Email</th>
                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['email']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title">Other Information</label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th style="width:40%;">Passport No.</th>
                                    <td class="text-bold-500"><?php echo $this->data['Employee']['passports']; ?></td>
                                </tr>
                                <tr>
                                    <th>Identity Card</th>
                                    <td class="text-bold-500"><?php echo $this->data['Employee']['identity_card']; ?></td>
                                </tr>
                                <tr>
                                    <th style="width:40%;">Position</th>
                                    <td class="text-bold-500"><?php echo $this->data['Position']['name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Salary</th>
                                    <td class="text-bold-500"><?php echo $this->data['Employee']['salary']; ?></td>
                                </tr>
                                <tr>
                                    <th>Work For Vendor</th>
                                    <td class="text-bold-500"><?php echo $this->data['Vendor']['name']; ?></td>
                                </tr>
                                <tr>
                                    <th class="border-bottom-cus">Note</th>
                                    <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['note']; ?></td>
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
            <button  class="btn btn-primary btnBackEmployee text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

