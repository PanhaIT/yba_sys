<?php 
$this->element('check_access');
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
        backEventView(oTableMeetingTheme,"btnBackMeetingTheme");
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_MEETING_THEME;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VIEW_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBackMeetingTheme" is-breadcrumb="1"><?php echo MENU_MEETING_THEME;?></a></li>
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
                    <label class="card-title"><?php echo TABLE_MEETING_THEME_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th><?php echo TABLE_NAME;?> :</th>
                                        <td colspan="5" class="text-bold-500"><?php echo $this->data['MeetingTheme']['name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_CODE; ?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['MeetingTheme']['code']; ?></td>
                                        <th class="border-bottom-cus"><?php echo MENU_EMPLOYEE_GROUP;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Egroup']['name']; ?></td>
                                        <th class="border-bottom-cus"><?php echo TABLE_TEAM_LEADER;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['Employee']['name']; ?></td>
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
                    <label class="card-title"><?php echo TABLE_OTHER_INFORMATION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-hover mb-0 view_item_list">
                                <tbody>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_REMARK;?> : </th>
                                        <td class="text-bold-500 border-bottom-cus"><?php echo $this->data['MeetingTheme']['remark']; ?></td>
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
            <button  class="btn btn-primary btnBackMeetingTheme text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink : href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

