<script type="text/javascript">
    $(document).ready(function(){
        $(".btnBackMembershipCard").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableMembershipCard.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<?php 
include("includes/function.php");
?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackMembershipCard">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_MEMBERSHIP_CARD_MANAGEMENT_INFO); ?></legend>
        <table cellpadding="5" style="width: 100%;">
            <tr>
                <th style="width: 15%;"><?php echo TABLE_MEMBERSHIP_CARD_ID; ?> :</th>
                <td style="width: 35%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['MembershipCard']['card_id']; ?>
                    </div>
                </td>
                <th style="width: 15%;"><?php echo TABLE_COMPANY; ?> :</th>
                <td style="width: 35%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Company']['name']; ?>
                    </div>
                </td>
            </tr> 
            <tr>
                <th><?php echo TABLE_CUSTOMER_GROUP; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php                        
                        echo $membershipCard['Cgroup']['name']; 
                        ?>
                    </div>
                </td>
                <th><?php echo TABLE_SEX; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Customer']['sex']; ?>
                    </div>
                </td>
            </tr> 
            <tr>
                <th><?php echo TABLE_CUSTOMER; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Customer']['name']; ?>
                    </div>
                </td>
                <th><?php echo TABLE_TELEPHONE; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Customer']['main_number']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo TABLE_EMAIL; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Customer']['email']; ?>
                    </div>
                </td>
                <th><?php echo TABLE_DOB; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php if($membershipCard['Customer']['dob']!="0000-00-00"){ echo dateShort($membershipCard['Customer']['dob']);} ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo TABLE_ADDRESS; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['Customer']['address']; ?>
                    </div>
                </td>     
                <th><?php echo TABLE_DATE_START; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php if($membershipCard['MembershipCard']['card_date_start']!="0000-00-00"){ echo dateShort($membershipCard['MembershipCard']['card_date_start']);} ?>
                    </div>
                </td> 
            </tr>
            <tr>
                <th><?php echo TABLE_MEMBERSHIP_CARD_TYPE; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $membershipCard['TypeOfMembershipCard']['name']; ?>
                    </div>
                </td>    
                <th><?php echo TABLE_DATE_END; ?> :</th>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php if($membershipCard['MembershipCard']['card_date_end']!="0000-00-00"){ echo dateShort($membershipCard['MembershipCard']['card_date_end']);} ?>
                    </div>
                </td>               
            </tr>
        </table>
</fieldset>
<br />
<fieldset>
    <legend><?php echo MENU_CARD_DISCOUNT_INFO; ?></legend>
    <table id="discount-save" style="width: 100%;<?php if($membershipCard['MembershipCard']['type_of_membership_card_id']==2) { echo 'display:none;';}?>" cellpadding="5" >               
        <tr>
            <th style="width: 25%;"><label for=""><?php echo TABLE_ACCOUNT; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 25%;"><label for=""><?php echo GENERAL_DISCOUNT_PERCENT; ?> <span class="red">*</span> :</label></th> 
            <th style="width: 25%;"><label for=""><?php echo 'Setting Point(Buy)'; ?> <span class="red">*</span> :</label></th>                
            <th style="width: 25%;"><label for=""><?php echo 'Apply Point as Dollars'; ?> <span class="red">*</span> :</label></th>  
        <tr>
            <td style="width: 25%;">
                <?php                    
                    echo $membershipCard['ChartAccount']['account_codes'].' - '. $membershipCard['ChartAccount']['account_description'];                
                ?>               
            </td>
            <td style="width: 25%;">
                <?php 
                    echo $membershipCard['MembershipCard']['discount_percent'];                
                ?>
            </td>
            <td style="width: 25%;">
                <?php 
                    echo '1$ = '.$membershipCard['MembershipCard']['total_point'].' Point(s)';                
                ?>                
            </td>
            <td style="width: 25%;">
                <?php 
                    echo $membershipCard['MembershipCard']['exchange_point'].' Point(s) = '.$membershipCard['MembershipCard']['point_in_dollar'].' Dollar(s)';                
                ?>                
            </td>
        </tr>
    </table>    
    <table id="top-up-discount" style="width: 100%;<?php if($membershipCard['MembershipCard']['type_of_membership_card_id']==1) { echo 'display:none;';}?>" cellspacing="0">               
        <tr>
            <th style="width: 35%;"><label for=""><?php echo TABLE_ACCOUNT; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 35%;"><label for=""><?php echo GENERAL_CASH_DOLLAR; ?> <span class="red">*</span> :</label></th>            
            <th style="width: 30%;"><label for=""><?php echo GENERAL_DISCOUNT_PERCENT; ?> <span class="red">*</span> :</label></th>            
        </tr> 
        <tr>
            <td style="width: 35%;">
                <?php                    
                    echo $membershipCard['ChartAccount']['account_codes'].' - '. $membershipCard['ChartAccount']['account_description'];                
                ?>              
            </td>
            <td style="width: 35%;">
                <?php 
                    echo $membershipCard['MembershipCard']['amount_in_dollar'];                
                ?>                
            </td>
            <td style="width: 30%;">
                <?php 
                    echo $membershipCard['MembershipCard']['discount_percent'];                
                ?>                
            </td>
        </tr>
    </table>
</fieldset>
<br />