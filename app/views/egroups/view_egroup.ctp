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
        backEventView(oTableEgroup,"btnBackEgroup");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo TABLE_EMPlOYEE_GROUP;?></h4>
                <p class="text-subtitle text-muted">Egroup information</p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackEgroup" is-breadcrumb="1"><?php echo TABLE_EMPlOYEE_GROUP;?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-header">
                <label class="card-title"><?php echo MENU_EMPLOYEE_GROUP_INFO;?></label>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <table class="table table-hover mb-0 view_item_list">
                            <tbody>
                                <tr>
                                    <th>Name</th>
                                    <td class="text-bold-500"><?php echo $this->data['Egroup']['name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Company</th>
                                    <td class="text-bold-500">
                                    <?php 
                                        $sqlCompany=mysql_query("SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM companies c INNER JOIN employee_companies ec ON ec.company_id=c.id INNER JOIN employees e ON e.id=ec.employee_id WHERE c.is_active=1 AND e.id='".$this->data['Egroup']['id']."'");
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
    </div>
    <div style="z-index:100;padding:1px 15px 5px 0px;margin:0px 0px 0px 0px; bottom:0px;position:fixed;width:100%; height:50px;">
        <a style="color:white;">
            <button  class="btn btn-primary btnBackEgroup text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink:href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

