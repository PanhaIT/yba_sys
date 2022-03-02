<script type="text/javascript">
    var fingerInterval;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        clearInterval(fingerInterval);
        // Set Interval
        fingerInterval = setInterval(refreshServerFinger, 500);
        // Open Door
        $("#btnOpenDoor").unbind("click").click(function(){
            $.ajax({
                url: "http://192.168.10.10/apiAccessDoor.php"
            });
        });
    });
    
    function refreshServerFinger(){
        $.ajax({
            dataType: "json",
            type: "GET",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/viewFingerprintResult",
            success: function(result){
                if($("#divEmpFingerprint").html() == null){
                    clearInterval(fingerInterval);
                } else {
                    $("#divEmpFingerprint").html(result.body);
                }
                if(result.door == '1'){
                    $("#doorStatus").css("background", "blue");
                } else {
                    $("#doorStatus").css("background", "red");
                }
            }
        });
    }
</script>
<div class="leftPanel">
    <br />
    <div id="dynamic">
        <div style="text-align: center; font-size: 22px; font-weight: bold; margin-bottom: 5px;">
            Fingerprint Activity
            <div style="float: right; width: 100px; border: 1px solid #000; height: 20px; padding-top: 8px; margin-left: 10px; background: red; color: #fff;" id="doorStatus">Door</div>
            <div class="buttons" style="float: right;">
                <a href="" class="positive btnOpenDoor">
                    <img src="<?php echo $this->webroot; ?>img/button/door.png" alt=""/>
                    <?php echo 'Open Door'; ?>
                </a>
            </div>
            <div style="clear: both;"></div>
        </div>
        <table class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_CODE; ?></th>
                    <th><?php echo TABLE_NAME; ?></th>
                    <th><?php echo TABLE_TYPE; ?></th>
                    <th><?php echo TABLE_DATE; ?></th>
                </tr>
            </thead>
            <tbody id="divEmpFingerprint">
                <tr>
                    <td colspan="5" class="first dataTables_empty"><?php echo TABLE_LOADING; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br />
</div>
<div class="rightPanel"></div>