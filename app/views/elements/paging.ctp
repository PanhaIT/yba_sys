<?php
$dot_time = 0;
if ($total > 1) {
?>
    <div style="float:right;padding-left:10px" class="noprint">
    <?php
    if ($total < (($number * 3) + 1 )) {
        for ($i = 1; $i <= $total; $i++) {
    ?>
            <a class="<?php echo $pager; ?>" <?php echo ($_POST['page'] == $i) ? 'style="color:#FF0000"' : ''; ?> href="" rel="<?php echo $i; ?>"><?php echo $i; ?></a>
    <?php
        }
    } else {
        for ($i = 1; $i <= $total; $i++) {
            if (($i < ($number + 1)) || (($i + $number) > $total) || (($_POST['page'] + ($number / 2) + 1) > $i) && (($_POST['page'] - ($number / 2)) < $i)) {
                $dot_time = 0;
    ?>
                <a class="<?php echo $pager; ?>" <?php echo ($_POST['page'] == $i) ? 'style="color:#FF0000"' : ''; ?> href="" rel="<?php echo $i; ?>"><?php echo $i; ?></a>
    <?php
            } else {
                if (++$dot_time == 1) {
                    echo $dots;
                }
            }
        }
    }
    ?>
</div>
<?php } ?>