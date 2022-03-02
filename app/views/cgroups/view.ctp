<?php 
$this->element('check_access');
$rnd = rand();
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
        backEventView(oTableCgroup,"btnBackCgroup");
        var user = $('.userCgroup').bootstrapDualListbox({
            moveOnSelect: false
        });
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackCgroup" is-breadcrumb="1"><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th><?php echo TABLE_NAME;?></th>
                                    <td class="text-bold-500"><?php echo $this->data['Cgroup']['name']; ?></td>
                                </tr>
                                <tr>
                                    <th class="border-bottom-cus"><?php echo MENU_COMPANY_MANAGEMENT;?></th>
                                    <td class="text-bold-500 border-bottom-cus">
                                    <?php 
                                        $sqlCompany=mysql_query("SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM companies c INNER JOIN cgroup_companies cc ON cc.company_id=c.id INNER JOIN cgroups cg ON cg.id=cc.cgroup_id WHERE c.is_active=1 AND cg.id='".$this->data['Cgroup']['id']."'");
                                        if(mysql_num_rows($sqlCompany)){
                                            $rowCompany = mysql_fetch_array($sqlCompany);
                                            echo $rowCompany[0];
                                        }
                                    ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo USER_USER_INFO;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <select name="data[Cgroup][user_id][]" multiple="multiple" class="userCgroup">
                                    <?php
                                    $selected ='';
                                    $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_Cgroups WHERE Cgroup_id ='" .$this->data['Cgroup']['id']. "')");
                                        while($dataSource = mysql_fetch_array($querySource)){
                                    ?>
                                            <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                    <?php 
                                        }
                                    $queryDestination = mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) full_name FROM users WHERE id = user_Cgroups.user_id) AS full_name FROM user_Cgroups WHERE Cgroup_id = ".$this->data['Cgroup']['id']);
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
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackCgroup text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

