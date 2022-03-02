<?php
$ua = $_SERVER['HTTP_USER_AGENT'];
$checker = array(
        'iphone'=>preg_match('/iPhone|iPod|iPad/', $ua),
        'blackberry'=>preg_match('/BlackBerry/', $ua),
        'android'=>preg_match('/Android/', $ua),
);
if ($checker['iphone']) {
?>
<style type="text/css">
    @font-face {
        font-family: Kh-Battambang, Arial, Helvetica, sans-serif;
        font-size: 12px;
        font-style: normal;
        font-weight: normal;
        font-stretch: normal;
        src: url("<?php echo $this->webroot; ?>fonts/Kh-Battambang.eot");
    }
</style>
<?php }else{ ?>
<!--[if IE]>
<style type="text/css">
    @font-face {
        font-family: Kh-Battambang, Arial, Helvetica, sans-serif;
        font-size: 12px;
        font-style: normal;
        font-weight: normal;
        font-stretch: normal;
        src: url("<?php echo $this->webroot; ?>fonts/Kh-Battambang.eot");
    }
</style>
<![endif]-->
<!--[if !IE]>-->
<style type="text/css">
    @font-face {
        font-family: Kh-Battambang, Arial, Helvetica, sans-serif;
        font-size: 12px;
        font-style: normal;
        font-weight: normal;
        font-stretch: normal;
        src:    url('<?php echo $this->webroot; ?>fonts/Kh-Battambang.eot'); /* IE9 Compat Modes */
        src:    url('<?php echo $this->webroot; ?>fonts/Kh-Battambang.eot?iefix') format('eot'), /* IE6-IE8 */
                url('<?php echo $this->webroot; ?>fonts/Kh-Battambang.woff') format('woff'), /* Modern Browsers */
                url('<?php echo $this->webroot; ?>fonts/Kh-Battambang.ttf') format('truetype'), /* Safari, Android, iOS */
                url('<?php echo $this->webroot; ?>fonts/Kh-Battambang.svg#Kh-Battambang') format('svg'); /* Legacy iOS */

    }
</style>
<!--<![endif]-->
<?php } ?>