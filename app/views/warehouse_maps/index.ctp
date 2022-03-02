<?php
// Authentication
$this->element('check_access');
?>
<style type="text/css">
    .viewLocation:hover {
        text-decoration: underline;
    }
    .btnViewLocationProductView:hover{
        text-decoration: underline;
    }
</style>
<script type="text/javascript">   
    $(document).ready(function(){
        $(".viewLocation").click(function(){
            var id = $(this).attr("rel");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewLocationDetail/"+id,
                data: '',
                beforeSend: function(){
                    $("#viewLocationDetail").html("<?php echo ACTION_LOADING; ?>");
                },
                success: function(result){
                    $("#viewLocationDetail").html(result);
                }
            });
        });
        
        $(".btnViewProductView").click(function(event){
            event.preventDefault();
            var id = $(this).attr("rel");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewProductWarehouse/"+id,
                data: '',
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(result).dialog({
                        title: name,
                        resizable: false,
                        modal: true,
                        width: 800,
                        height: 500,
                        position:'center',
                        closeOnEscape: true,
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show(); $(".ui-dialog-titlebar-close").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });        
    });
</script>
<div class="legend">
    <div class="legend_title" style="font-size: 15px; font-weight: bold; padding-left: 10px;">
        <?php echo MENU_LOCATION_GROUP_MANAGEMENT_INFO; ?>
        <div style="clear: both;"></div>
    </div>
    <div style="height: 30px;"></div>
    <div style="width: 100%;">
        <?php 
            $index = 0;
            $queryWareouse = mysql_query("SELECT id, name FROM location_groups WHERE is_active = 1 AND location_group_type_id != 1 AND id IN (SELECT location_group_id FROM user_location_groups WHERE user_id = ".$user['User']['id'].")");
            while($dataWareouse = mysql_fetch_array($queryWareouse)){
                // Get Total Product
                $totalStockIn    = 0;
                $totalStockOut   = 0;
                $totalInPercent  = 0;
                $totalOutPercent = 0;

                // Get Total In Stock of Product
                $queryProQty = mysql_query("SELECT product_id FROM ".$dataWareouse[0]."_group_totals GROUP BY product_id HAVING SUM(total_qty) > 0");
                if(mysql_num_rows($queryProQty)){
                    $totalStockIn  = mysql_num_rows($queryProQty);
                }

                // Get Total Out Stock of Product
                $queryProOut = mysql_query("SELECT product_id FROM ".$dataWareouse[0]."_group_totals GROUP BY product_id HAVING SUM(total_qty) = 0");
                if(mysql_num_rows($queryProOut)){
                    $totalStockOut  = mysql_num_rows($queryProOut);
                }
                $total = $totalStockIn + $totalStockOut;
                if($totalStockIn > 0){
                    $totalInPercent = ($totalStockIn/$total) * 100;
                }
                if($totalStockOut > 0){
                    if($totalInPercent > 0){
                        $totalOutPercent = 100 - $totalInPercent;
                    } else {
                        $totalOutPercent = 100;
                    }
                }
        ?>
        <table style="width: 200px; float: left; margin-right: 10px; border: 2px solid #13258c; padding: 0px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center; padding: 0px; height: 30px; font-weight: bold; cursor: pointer; width: 100%;">
                    <div class="viewLocation" rel="<?php echo $dataWareouse[0]; ?>" onmouseover="Tip('<?php echo ACTION_VIEW_ALL_LOCATION_WAREHOUSE; ?>')" style="width: 90%; float: left"><?php echo $dataWareouse['name']; ?></div>
                    <div class="btnViewProductView" rel="<?php echo $dataWareouse['id']; ?>" style="width: 7%; float: right; padding-right: 5px;"><a href=""><img alt="View" onmouseover="Tip('<?php echo ACTION_VIEW_ALL_PRODUCT_WAREHOUSE; ?>')" src="<?php echo $this->webroot; ?>img/button/view.png" /></a></div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; width: 200px; padding: 0px;">
                    <?php
                    if($total > 0){
                    ?>
                        <?php 
                            if($totalInPercent > 0){
                        ?>
                        <div style="float: left; width: <?php echo $totalInPercent; ?>%;  border-top: 2px solid #13258c; background-color: blue; padding-top: 10px; padding-bottom: 10px; padding-left: 0px; padding-right: 0px; color: #FFF; font-weight: bold;"><?php echo $totalInPercent; ?> %</div>
                        <?php
                            }
                        ?> 
                        <?php 
                            if($totalOutPercent > 0){
                        ?>
                        <div style="float: left; width: <?php echo $totalOutPercent; ?>%; border-top: 2px solid #13258c; background-color: red;  padding-top: 10px; padding-bottom: 10px; padding-left: 0px; padding-right: 0px; color: #FFF; font-weight: bold;"><?php echo $totalOutPercent; ?> %</div>
                        <?php
                            }
                        ?>                
                    <?php
                    } else {
                    ?>
                        <div style="width: 100%; background-color: #eee; padding-top: 10px; border-top: 2px solid #13258c; padding-bottom: 10px; padding-left: 0px; padding-right: 0px; color: red; font-weight: bold;">No Product</div>
                    <?php
                    }
                    ?>
                    <div class="clear:both"></div>
                </td>
            </tr>
        </table>
        <?php
                $index++;
            }
        ?>
    </div>
    <div style="clear: both;"></div>
    <div style="height: 30px;"></div>
</div>
<div id="viewLocationDetail" class="legend">
    
</div>



