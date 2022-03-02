<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnCheckConnection").click(function(event){
            event.preventDefault();
            var obj = $(this);
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/checkConnection",
                cache: !1,
                timeout: 10000,
                beforeSend: function(arr, $form, options) {
                    obj.attr('disabled', true);
                    obj.find("span").html("<?php echo ACTION_LOADING; ?>");
                },
                error: function() {
                    $("#connectionStatus").text('Disconnect');
                },
                success: function(result){
                    obj.attr('disabled', false);
                    obj.find("span").html("<?php echo ACTION_CHECK; ?>");
                    if(result == 1){
                        $("#connectionStatus").text('Connected');
                    } else {
                        $("#connectionStatus").text('Disconnect');
                    }
                }
            });
        });
        
        $(".btnCheckBnCareConnection").click(function(event){
            event.preventDefault();
            var obj = $(this);
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/checkMainConnection",
                cache: !1,
                timeout: 10000,
                beforeSend: function(arr, $form, options) {
                    obj.attr('disabled', true);
                    obj.find("span").html("<?php echo ACTION_LOADING; ?>");
                },
                error: function() {
                    $("#connectionBnCareStatus").text('Disconnect');
                },
                success: function(result){
                    obj.attr('disabled', false);
                    obj.find("span").html("<?php echo ACTION_CHECK; ?>");
                    if(result == 1){
                        $("#connectionBnCareStatus").text('Connected');
                    } else {
                        $("#connectionBnCareStatus").text('Disconnect');
                    }
                }
            });
        });
        
        $(".btnRefreshSYNC").click(function(event){
            event.preventDefault();
            var panel = $("#divSYNCMonitor").parent();
            panel.html("<?php echo ACTION_LOADING; ?>");
            panel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/index/");
        });
        
        $(".btnCheckConnection").click();
    });
</script>
<fieldset style="width: 620px;" id="divSYNCMonitor">
    <legend><?php __(MENU_SYNC_MONITORING); ?></legend>
    <div style="clear: both;"></div>
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;">
                <?php echo TABLE_INTERNET_CONNECTION; ?> : <span style="width: 90px;" id="connectionStatus"></span>
            </td>
            <td style="width: 150px; text-align: right;">
                <div class="buttons">
                    <a href="#" class="positive btnCheckConnection" style="float: right;">
                        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
                        <span><?php echo ACTION_CHECK; ?></span>
                    </a>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
    </table>
    <br />
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;"><?php echo TABLE_SYNC_INFORMATION; ?></td>
            <td style="text-align: right; width: 120px; height: 40px;">
                <div class="buttons">
                    <a href="#" class="positive btnRefreshSYNC" style="float: right;">
                        <img src="<?php echo $this->webroot; ?>img/button/refresh-active.png" alt=""/>
                        <span><?php echo ACTION_REFRESH; ?></span>
                    </a>
                </div>
                <div style="clear: both;"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #000;">
                <table class="table" style="width: 100%;">
                    <tr>
                        <th class="first" style="width: 14%;"><?php echo TABLE_NAME; ?></th>
                        <th style="width: 23%;"><?php echo TABLE_DATE_START ?></th>
                        <th style="width: 23%;"><?php echo TABLE_DATE_END ?></th>
                        <th><?php echo TABLE_DURATION ?> (s)</th>
                        <th style="width: 15%;"><?php echo TABLE_STATUS ?></th>
                    </tr>
                    <?php
                    $totalWillReceive = 0;
                    $sqlSync = mysql_query("SELECT * FROM ".SYNC_SYSTEM."processes WHERE 1;");
                    while($rowSync = mysql_fetch_array($sqlSync)){
                        $totalWillReceive = $rowSync['total_will_receive'];
                    ?>
                    <tr>
                        <td class="first">
                            <?php
                            if($rowSync['sync_script'] == 'sync'){
                                echo 'Send';
                            } else {
                                echo 'Receive';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['start']) && $rowSync['start'] != '0000-00-00'){
                                echo dateShort($rowSync['start'], "d/m/Y H:i:s");
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['end']) && $rowSync['end'] != '0000-00-00'){
                                echo dateShort($rowSync['end'], "d/m/Y H:i:s");
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if(!empty($rowSync['start']) && !empty($rowSync['end'])){
                                $duration = strtotime($rowSync['end']) - strtotime($rowSync['start']);
                                echo $duration;
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($rowSync['is_processing'] == 1){
                                echo 'PROCESSING';
                            } else {
                                echo 'STOP';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
    <br />
    <?php
    $sqlSend = mysql_query("SELECT COUNT(id) AS count, MAX(created) AS created FROM ".SYNC_SYSTEM."sends WHERE status = 1 ORDER BY id DESC LIMIT 1;");
    $rowSend = mysql_fetch_array($sqlSend);
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;"><?php echo TABLE_DATA_WAITING_SEND; ?></td>
            <td style="text-align: right; width: 120px;"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; height: 30px;" colspan="2">
                <?php echo TABLE_TOTAL; ?> : <?php echo number_format($rowSend['count'], 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_LAST_UPDATE; ?> :
                <?php 
                if(!empty($rowSend['created']) && !empty($rowSend['created'])){
                    echo dateShort($rowSend['created'], "d/m/Y H:i:s");
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
    $sqlSent = mysql_query("SELECT COUNT(id) AS count, MAX(created) AS created FROM ".SYNC_SYSTEM."sends WHERE status = 2 ORDER BY id DESC LIMIT 1;");
    $rowSent = mysql_fetch_array($sqlSent);
    $sqlSentToday = mysql_query("SELECT COUNT(id) AS count FROM ".SYNC_SYSTEM."sends WHERE status = 1 AND DATE(created) = '".date('Y-m-d')."' ORDER BY id DESC LIMIT 1;");
    $rowSentToday = mysql_fetch_array($sqlSentToday);
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;"><?php echo TABLE_DATA_HAS_BEEN_SENT; ?></td>
            <td style="text-align: right; width: 120px;"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; height: 30px;" colspan="2">
                <?php echo TABLE_TODAY; ?> : <?php echo number_format($rowSentToday['count'], 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_TOTAL; ?> : <?php echo number_format($rowSent['count'], 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_LAST_UPDATE; ?> :
                <?php 
                if(!empty($rowSent['created']) && !empty($rowSent['created'])){
                    echo dateShort($rowSent['created'], "d/m/Y H:i:s");
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
    $sqlReceive = mysql_query("SELECT COUNT(id) AS count, MAX(created) AS created FROM ".SYNC_SYSTEM."receives WHERE 1 ORDER BY id DESC LIMIT 1;");
    $rowReceive = mysql_fetch_array($sqlReceive);
    $sqlReceiveToday = mysql_query("SELECT COUNT(id) AS count FROM ".SYNC_SYSTEM."receives WHERE 1 AND DATE(created) = '".date('Y-m-d')."' ORDER BY id DESC LIMIT 1;");
    $rowReceiveToday = mysql_fetch_array($sqlReceiveToday);
    ?>
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;"><?php echo TABLE_DATA_RECEIVE; ?></td>
            <td style="text-align: right; width: 120px;"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000; height: 30px;" colspan="2">
                <?php echo TABLE_TOTAL_WILL_BE_RECEIVE; ?> : <?php echo number_format($totalWillReceive, 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_TODAY; ?> : <?php echo number_format($rowReceiveToday['count'], 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_TOTAL_RECEIVED; ?> : <?php echo number_format($rowReceive['count'], 0); ?>
            </td>
        </tr>
        <tr>
            <td style="height: 30px;" colspan="2">
                <?php echo TABLE_LAST_UPDATE; ?> :
                <?php 
                if(!empty($rowReceive['created']) && !empty($rowReceive['created'])){
                    echo dateShort($rowReceive['created'], "d/m/Y H:i:s");
                }
                ?>
            </td>
        </tr>
    </table>
    <br />
    <table cellpadding="0" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="font-size: 12px; font-weight: bold;" colspan="2"><?php echo TABLE_CLIENT_INFORMATION; ?></td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #000;">
                <table class="table" style="width: 100%;">
                    <tr>
                        <th class="first" style="width: 35%;"><?php echo TABLE_NAME; ?></th>
                        <th style="width: 19%;"><?php echo TABLE_TOTAL_SENT; ?></th>
                        <th style="width: 21%;"><?php echo TABLE_TOTAL_RECEIVED; ?></th>
                        <th style="width: 25%;"><?php echo TABLE_LAST_UPDATE; ?></th>
                    </tr>
                    <?php
                    $sqlClient = mysql_query("SELECT * FROM ".SYNC_SYSTEM."s_t_ps WHERE 1;");
                    while($rowClient = mysql_fetch_array($sqlClient)){
                    ?>
                    <tr>
                        <td class="first"><?php echo $rowClient['slave_code']; ?></td>
                        <td><?php echo number_format($rowClient['total_sent'], 0); ?></td>
                        <td><?php echo number_format($rowClient['total_received'], 0); ?></td>
                        <td><?php echo dateShort($rowClient['last_update'], "d/m/Y H:i:s"); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
<div style="clear: both;"></div>