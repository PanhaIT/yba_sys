<?php
$img = '';
if(!empty($logo)){
    $img = $logo;
}
?>
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; text-align: left; width: 33%;">
                <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $img; ?>" style="height: 90px;" />
        </td>
        <td style="vertical-align: top; text-align: center; width: 34%;">
                <div style="font-size: 20px; font-weight: bold; margin-top: 20px; text-transform: uppercase;"><?php echo !empty($msg) ? $msg : ''; ?></div>
        </td>
        <td style="vertical-align: top; text-align: right; white-space: nowrap;">
            <div style="margin-top: 20px; width: 100%;">
                <img class="barcode" alt="" src="<?php echo $this->webroot; ?>barcodegen.1d-php5.v2.2.0/generate_barcode.php?str=<?php echo $barcode; ?>" style="border:0px; padding: 0px; margin: 0px; width: 180px; height: 50px;" />
            </div>
        </td>
    </tr>
</table>