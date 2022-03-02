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
        backEventView(oTableMeetingNote,"btnBacMeetingNote");
        $('#MeetingNoteDesciption').summernote({
            tabsize: 2,
            height: 500,
        })
    });
</script>
<div class="app main-form">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h4><?php echo MENU_MEETING_NOTE;?></h4>
                <p class="text-subtitle text-muted"><?php echo MENU_VIEW_INFO;?></p>
            </div>
            <div class="col-12 col-md-6">
                <nav aria-label="breadcrumb" class='breadcrumb-header'>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link btnBacMeetingNote" is-breadcrumb="1"><?php echo MENU_MEETING_NOTE;?></a></li>
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
                    <label class="card-title"><?php echo GENERAL_DESCRIPTION;?></label>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row" id="MeetingNoteDesciption">
                            <?php echo $this->data['MeetingNote']['description']; ?>
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
                                        <th><?php echo TABLE_CODE;?> :</th>
                                        <td colspan="5" class="text-bold-500"><?php echo $this->data['MeetingNote']['code']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom-cus"><?php echo TABLE_EMPlOYEE_GROUP;?> :</th>
                                        <td colspan="5" class="text-bold-500 border-bottom-cus"><?php echo $this->data['Egroup']['name']; ?></td>
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
            <button  class="btn btn-primary btnBacMeetingNote text-btnback-footer" is-breadcrumb="0">
                <svg class="icon-svg-crud bi" fill="currentColor"><use xlink : href="<?php echo $this->webroot.PATH_ICON_BOOTSTRAP;?>arrow-left-short" /></svg>
                <label class="label_crud"><?php echo ACTION_BACK; ?></label>
            </button>
        </a>
    </div>
</div>

