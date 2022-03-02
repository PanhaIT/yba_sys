<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackCustomerGroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCustomerGroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCustomerGroup">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table width="100%" class="info">
    <tr>
        <th><?php echo TABLE_NAME; ?></th>
        <td><?php echo $cgroup['Cgroup']['name']; ?></td>
    </tr>
    <tr>
        <th><?php echo TABLE_PRICE_TYPE; ?></th>
        <td>
            <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                <?php
                if(!empty($priceTypes)){
                ?>
                <ul class="chzn-choices" style="border: none; background: none;">
                    <?php
                    foreach ($priceTypes AS $priceType) {
                        echo "<li class='search-choice' ><span>{$priceType['PriceType']['name']}</span></li>";
                    }
                    ?>
                </ul>
                <?php
                } else {
                    echo TABLE_ALL;
                }
                ?>
            </div>
        </td>
    </tr>
    <tr>
        <th><?php echo MENU_CUSTOMER; ?></th>
        <td>
            <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                <ul class="chzn-choices" style="border: none; background: none;">
                    <?php
                    foreach ($customers AS $customer) {
                        echo "<li class='search-choice' ><span>{$customer['Customer']['customer_code']} ({$customer['Customer']['name']})</span></li>";
                    }
                    ?>
                </ul>
            </div>
        </td>
    </tr>
    <tr>
        <th><?php echo MENU_USERS; ?></th>
        <td>
            <div class="chzn-container  chzn-container-multi" style="width: 100%;">
                <?php
                if(!empty($users)){
                ?>
                <ul class="chzn-choices" style="border: none; background: none;">
                    <?php
                    foreach ($users AS $user) {
                        echo "<li class='search-choice' ><span>{$user['User']['first_name']} ({$user['User']['last_name']})</span></li>";
                    }
                    ?>
                </ul>
                <?php
                } else {
                    echo TABLE_ALL;
                }
                ?>
            </div>
        </td>
    </tr>
</table>