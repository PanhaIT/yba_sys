<?php
$config = getSysconfig();
if(!empty($config)){
    $title   = $config['title'];
    $titleKh = $config['titleKh'];
}else{
    $title   = "";
    $titleKh = "";
}
?>
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; text-align: center; width: 100%;">
            <img alt="" src="<?php echo $this->webroot; ?>/public/company_photo/<?php echo !empty($logo) ? $logo : ''; ?>" style="height: 90px;" />
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; width: 100%;">
            <div style="font-size: 12px; font-weight: bold;"><?php echo !empty($title) ? $title : ''; ?></div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; width: 100%;">
            <div style="font-size: 12px; font-weight: bold;"><?php echo !empty($telephone) ? $telephone : ''; ?></div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; width: 100%;">
            <div style="font-size: 12px; font-weight: bold;"><?php echo !empty($address) ? $address : ''; ?></div>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; width: 100%;">
            <div style="font-size: 12px; font-weight: bold;"><?php echo !empty($msg) ? $msg : ''; ?></div>
        </td>
    </tr>
</table>