<?php 
$this->element('check_access');
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
<div class="app main-form">
    <div class="row">
        <div class="col-sm-8" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_TODO_LIST_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_CODE;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['TodoList']['code']; ?></td>
                                        <th><?php echo TABLE_DATE;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['TodoList']['date']; ?></td>
                                        <th><?php echo TABLE_ESTIMATE_DATE;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['TodoList']['estimate_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_TASK_NAME;?> :</th>
                                        <td colspan="3" class="text-bold-500"><?php echo $this->data['TodoList']['task_name']; ?></td>
                                        <th><?php echo TABLE_PRIORITY;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Priority']['name']; ?> </td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_STATUS;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Progresse']['name']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_START_DATE;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['TodoList']['start_date']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_END_DATE; ?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['TodoList']['end_date']; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_SUB_TASK;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <table class="table table-hover mb-0 view_item_list">
                                    <tbody>
                                        <tr>
                                            <th><?php echo TABLE_CUSTOMER;?></th>
                                            <th><?php echo TABLE_EMPLOYEE;?></th>
                                            <th><?php echo TABLE_REMARK;?></th>
                                        </tr>
                                        <?php 
                                        $serviceCode = '';
                                        $sectionName = '';
                                        $serviceName = '';
                                        foreach($todoListDetails AS $todoListDetail){
                                            if($todoListDetail['TodoListDetails']['service_id']!=""){
                                                $sqlService  = mysql_query("SELECT service_groups.name AS sectionName,services.code AS serviceCode,services.name AS serviceName FROM services INNER JOIN service_groups ON service_groups.id = services.service_group_id WHERE services.is_active=1 AND services.id=".$todoListDetail['TodoListDetails']['service_id']);
                                                $rowService  = mysql_fetch_array($sqlService);
                                                $serviceCode = $rowService['serviceCode'];
                                                $sectionName = $rowService['sectionName'];
                                                $serviceName = $rowService['serviceName'];
                                            }
                                        ?>
                                        <tr>
                                            <td class="text-bold-500"><?php echo $serviceCode; ?></td>
                                            <td class="text-bold-500"><?php echo $sectionName; ?></td>
                                            <td class="text-bold-500"><?php echo $serviceName; ?></td>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>   
        </div>
        <div class="col-sm-4" style="">
            <div class="card">
                <div class="card-header">
                    <label class="card-title"><?php echo TABLE_OTHER_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_CUSTOMER;?> : </th>
                                        <td class="text-bold-500"><?php echo $this->data['Customer']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo TABLE_EMPLOYEE;?> :</th>
                                        <td class="text-bold-500"><?php echo $this->data['Employee']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_REMARK;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['TodoList']['remark']; ?></td>
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

