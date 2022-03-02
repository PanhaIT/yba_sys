<script type="text/javascript">
    $(document).ready(function(){
        $("#refreshChangeCost").unbind("click").click(function(){
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/resultChangeCost/",
                beforeSend: function(){
                    $("#refreshChangeCost").hide();
                    $("#loadingChangeCost").show();
                },
                success: function(msg){
                    $("#refreshChangeCost").show();
                    $("#loadingChangeCost").hide();
                    if(msg.result != ''){
                        var contents = msg.result;
                        var update   = msg.update;
                        $("#lastUpdateChangeCost").text(update);
                        $("#resultChangeCost").html(contents);
                    }
                }
            });
        });
    });
</script>
<table style="width: 100%;">
    <tr>
        <td>Last Update: <span id="lastUpdateChangeCost"><?php echo date("d/m/Y H:i:s"); ?></span></td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
    <tr>
        <th class="first" style="width: 10%;"><?php echo TABLE_NO; ?></th>
        <th style="width: 20%;"><?php echo TABLE_SKU; ?></th>
        <th style="width: 37%;"><?php echo TABLE_PRODUCT_NAME; ?></th>
        <th style="width: 15%;"></th>
        <th style="width: 18%;"></th>
    </tr>
    <tbody id="resultChangeCost">
    <?php
    $dateNow = date("Y-m-d");
    $sqlHis = mysql_query("SELECT products.code, products.name, product_unit_cost_histories.old_cost, product_unit_cost_histories.new_cost FROM product_unit_cost_histories INNER JOIN products ON products.id = product_unit_cost_histories.product_id WHERE DATE(product_unit_cost_histories.created) = '".$dateNow."' ORDER BY product_unit_cost_histories.created DESC LIMIT 15");
    if(mysql_num_rows($sqlHis)){
        $index = 1;
        while($rowHis = mysql_fetch_array($sqlHis)){
    ?>
        <tr>
            <td class="first"><?php echo $index; ?></td>
            <td><?php echo $rowHis['code']; ?></td>
            <td><?php echo $rowHis['name']; ?></td>
            <td><?php echo number_format($rowHis['old_cost'], 2); ?></td>
            <?php
            if($rowHis['new_cost'] > $rowHis['old_cost']){
                $img = 'up.png';
                $color = 'color: #0a0;';
            } else if($rowHis['old_cost'] > $rowHis['new_cost']){
                $img = 'down.png';
                $color = 'color: red;';
            } else {
                $img = '';
                $color = '';
            }
            ?>
            <td style="<?php echo $color; ?>">
                <?php 
                echo number_format($rowHis['new_cost'], 2);
                if($img != ''){
                    echo '<img src="' . $this->webroot . 'img/button/'.$img.'" style="margin-left: 5px;" />';
                }
                ?>
            </td>
        </tr>
    <?php
            $index++;
        }
    } else {
    ?>
        <tr>
            <td colspan="5" class="first"><?php echo TABLE_NO_RECORD; ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>