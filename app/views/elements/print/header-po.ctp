<?php
$img = '';
if(!empty($logo)){
    $img = $logo;
}
?>
<table style="width: 100%;">
    <tr>
        <td style="text-align: center; width: 80%;">
            <div style="font-size: 18px; font-weight: bold; text-align: center;">
                អេស លីកហ្គ័រ
            </div>
        </td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <div style="font-size: 18px; font-weight: bold; text-align: center;">
                S Liquor
            </div>
        </td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <?php
            if(!empty($titleOther)){
            ?>
            <div style="font-size: 18px; font-weight: bold; text-align: center;">
                <?php
                echo $titleOther;
                ?>
            </div>
            <?php
            }
            ?>
        </td>
        <td style="width: 7%;">No :</td>
        <td><?php echo $code; ?></td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <?php
            if(!empty($title)){
            ?>
            <div style="font-size: 18px; font-weight: bold; text-align: center;">
                <?php
                echo $title;
                ?>
            </div>
            <?php
            }
            ?>
        </td>
        <td style="width: 7%;">Date :</td>
        <td><?php echo $date; ?></td>
    </tr>
</table>