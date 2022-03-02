<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackCompany").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableCompany.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackCompany">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table cellpadding="0" style="width: 100%;">
    <tr>
        <td colspan="2"><img border="1" src="<?php echo $this->webroot; ?>public/company_photo/<?php echo $this->data['Company']['photo'] ?>" alt="<?php echo $this->data['Company']['photo'] ?>" title="<?php echo $this->data['Company']['name'] ?>" style="max-width: 250px; max-height: 250px;" /></td>
    </tr>
</table>
<br />
<fieldset>
    <legend><?php __(MENU_COMPANY_MANAGEMENT_INFO); ?></legend>
    <table style="width: 100%;" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_NAME); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Company']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_NAME_IN_KHMER); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Company']['name_other']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(MENU_CURRENCY); ?> :</th>
            <td style="font-size: 12px;"><?php echo $this->data['CurrencyCenter']['name']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;">VAT No :</th>
            <td style="font-size: 12px;"><?php echo $this->data['Company']['vat_number']; ?></td>
        </tr>
        <tr>
            <th style="font-size: 12px;">VAT Calculating :</th>
            <td style="font-size: 12px;">
                <?php 
                if($this->data['Company']['vat_calculate'] == 1){
                    echo TABLE_VAT_BEFORE_DISCOUNT; 
                } else {
                    echo TABLE_VAT_AFTER_DISCOUNT; 
                }
                ?>
            </td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_WEBSITE); ?> :</th>
            <td style="font-size: 12px;" colspan="5"><?php echo $this->data['Company']['website']; ?></td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_USER_MANAGEMENT_INFO); ?></legend>
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 10%;"><?php echo GENERAL_MEMBER; ?> :</td>
            <td>
                <?php
                $sqlUser = mysql_query("SELECT GROUP_CONCAT(CONCAT(first_name, ' ', last_name)) FROM users WHERE id IN (SELECT user_id FROM user_companies WHERE company_id = ".$this->data['Company']['id'].")");
                @$rowUser = mysql_fetch_array($sqlUser);
                echo @$rowUser[0];
                ?>
            </td>
        </tr>
    </table>
</fieldset>    