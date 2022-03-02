<?php
include("includes/function.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackExpense").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableExpense.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackExpense">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<fieldset>
    <legend><?php __(MENU_EXPENSE_INFO); ?></legend>
    <table width="100%" cellpadding="5">
        <tr>
            <th style="font-size: 12px; width: 10%;"><?php __(TABLE_DATE); ?></th>
            <td style="font-size: 12px;"><?php echo dateShort($this->data['Expense']['date']); ?></td>
            <th style="font-size: 12px; width: 10%;"></th>
            <td style="font-size: 12px;"></td>
        </tr>
        <tr>
            <th style="font-size: 12px;"><?php __(TABLE_REFERENCE); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Expense']['reference']; ?></td>
            <th style="font-size: 12px;"></th>
            <td style="font-size: 12px;"></td>
        </tr>
        <tr>
            <th ><?php __(TABLE_TOTAL_AMOUNT); ?></th>
            <td style="font-size: 12px;"><?php echo number_format($this->data['Expense']['total_amount'], 2); ?> ($)</td>
            <th style="vertical-align: top; "><?php __(TABLE_MEMO); ?></th>
            <td style="font-size: 12px;"><?php echo $this->data['Expense']['note']; ?></td>
        </tr>
    </table>
</fieldset>
<table id="tblGL" class="table" cellspacing="0">
    <tr>
        <th class="first"><?php echo TABLE_ACCOUNT; ?></th>
        <th style="width: 15%;"><?php echo GENERAL_AMOUNT; ?> ($)</th>
        <th style="width: 40%;"><?php echo TABLE_MEMO; ?></th>
    </tr>
    <?php
    foreach($expenseDeatils AS $expenseDeatil){
    ?>
    <tr>
        <td class="first">
            <div class="inputContainer" style="width: 100%;">
                <?php
                $query = mysql_query("SELECT id,CONCAT(account_codes,' · ',account_description) AS name FROM chart_accounts WHERE id = ".$expenseDeatil['ExpenseDetail']['chart_account_id']."");
                $data  = mysql_fetch_array($query);
                echo $data['name']; 
                ?>
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo number_format($expenseDeatil['ExpenseDetail']['amount'], 2); ?>
            </div>
        </td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $expenseDeatil['ExpenseDetail']['note']; ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
</table>