<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#txtBackupDatabase").click(function(event){
            event.preventDefault();
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/backup/",
                data: "",
                beforeSend: function(){
                    $("#txtBackupDatabase").html("<?php echo ACTION_LOADING; ?>");
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result){
                    $("#txtBackupDatabase").html("<?php echo ACTION_BACKUP_DATABASE; ?>");
                    $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    window.open("<?php echo $this->webroot; ?>public/db/" + result, "_blank");
                }
            });
        });
    });
</script>
<h1 class="title"><?php echo ACTION_BACKUP_DATABASE; ?></h1>
<div class="buttons">
    <a href="" class="positive btnBackupDatabase">
        <img src="<?php echo $this->webroot; ?>img/button/receive.png" alt=""/>
        <span id="txtBackupDatabase"><?php echo ACTION_BACKUP_DATABASE; ?></span>
    </a>
</div>
<div style="clear: both;"></div>