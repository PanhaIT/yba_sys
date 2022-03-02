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
        backEventView(oTableUser,"btnBackCompany");
        var user = $('.companyMemberOf,.branchMemberOf').bootstrapDualListbox({
            moveOnSelect: false
        });
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_USER_MANAGEMENT;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VIEW_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCompany" is-breadcrumb="1"><?php echo MENU_USER_MANAGEMENT;?></a></li>
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
                    <label class="card-title"><?php echo MENU_USER_MANAGEMENT_ADD;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_FIRST_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $user['User']['first_name']; ?></td>
                                        <th><?php echo TABLE_LAST_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $user['User']['last_name']; ?></td>
                                        <th><?php echo TABLE_SEX;?> : </th>
                                        <td class="text-bold-500"><?php echo $user['User']['sex']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_ADDRESS;?> :</th>
                                        <td colspan="3" class="text-bold-500"><?php echo $user['User']['address']; ?></td>
                                        <th><?php echo TABLE_TELEPHONE;?> : </th>
                                        <td class="text-bold-500"><?php echo $user['User']['telephone']; ?> </td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_DOB;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $user['User']['dob']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_EMAIL;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $user['User']['email']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_NATIONALITY; ?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $nationality; ?></td>
                                    </tr>
                                </tbody>
                            </table>
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
                                <select name="data[User][branch_id][]" multiple="multiple" class="branchMemberOf">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id,name FROM branches WHERE name IS NOT NULL AND is_active=1 AND id NOT IN (SELECT user_id FROM user_branches WHERE user_id ='" .$id. "')");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                                    <?php 
                                        }
                                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT name FROM branches WHERE name IS NOT NULL AND id = user_branches.user_id) AS name FROM user_branches WHERE user_id = ".$id);
                                        while($dataDestination = mysql_fetch_array($queryDestination)){
                                    ?>
                                            <option value="<?php echo $dataDestination['user_id']; ?>" selected="selected"><?php echo $dataDestination['name']; ?></option>
                                    <?php 
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_COMPANY_MANAGEMENT_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[User][company_id][]" multiple="multiple" class="companyMemberOf">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id,name FROM companies WHERE name IS NOT NULL AND is_active=1 AND id NOT IN (SELECT user_id FROM user_companies WHERE user_id ='" .$user['User']['id']. "')");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['name']; ?></option>
                                    <?php 
                                        }
                                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT name FROM companies WHERE name IS NOT NULL AND id = user_companies.company_id) AS name FROM user_companies WHERE user_id = ".$user['User']['id']);
                                        while($dataDestination = mysql_fetch_array($queryDestination)){
                                    ?>
                                            <option value="<?php echo $dataDestination['user_id']; ?>" selected="selected"><?php echo $dataDestination['name']; ?></option>
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
                    <label class="card-title"><?php echo USER_LOGIN_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th style="width:40%;"><?php echo USER_USER_NAME;?> : </th>
                                        <td class="text-bold-500"><?php echo $user['User']['username']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo USER_PASSWORD;?> :</th>
                                        <td class="text-bold-500">***********</td>
                                    </tr>
                                    <tr>
                                        <th><?php echo USER_CONFIRM_PASSWORD;?> :</th>
                                        <td class="text-bold-500">***********</td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo USER_GROUP;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus">
                                            <?php
                                            $queryGroupId=mysql_query("SELECT GROUP_CONCAT(DISTINCT g.name SEPARATOR ',') AS userGroup FROM groups g INNER JOIN user_groups ug ON ug.group_id=g.id WHERE ug.user_id=".$user['User']['id']);
                                            $dataGroup=mysql_fetch_array($queryGroupId);
                                            echo $dataGroup['userGroup'];
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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

