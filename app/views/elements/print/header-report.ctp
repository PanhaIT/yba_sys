<table style="width: 100%;">
    <tr>
        <td style="vertical-align: middle; text-align: left; width: 33%;">
            <?php
            $sqlC = mysql_query("SELECT photo FROM companies WHERE id = 1");
            if(mysql_num_rows($sqlC)){
                $rowC = mysql_fetch_array($sqlC);
            ?>
            <img alt="" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $rowC[0]; ?>" style="height: 60px;" />
            <?php
            }
            ?>
        </td>
        <td style="vertical-align: top; text-align: center; width: 34%;">
            <div style="font-size: 15px; font-weight: 600; margin-top: 20px; text-transform: uppercase;"><?php echo !empty($msg) ? $msg : ''; ?></div>
        </td>
        <td style="vertical-align: top; text-align: right; white-space: nowrap;"></td>
    </tr>
</table>