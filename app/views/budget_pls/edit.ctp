<?php
$rnd = rand();
$printArea = "printArea" . $rnd;
$btnShowAll = "btnShowAll" . $rnd;
$btnHideAll = "btnHideAll" . $rnd;
$btnPlusMinus = "btnPlusMinus" . $rnd;
?>
<?php echo $this->element('prevent_multiple_submit'); ?>
<?php $monthName=array(DATE_JAN, DATE_FEB, DATE_MAR, DATE_APR, DATE_MAY, DATE_JUN, DATE_JUL, DATE_AUG, DATE_SEP, DATE_OCT, DATE_NOV, DATE_DEC); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#BudgetPlEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#BudgetPlEditForm").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSave").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                var rightPanel=$("#BudgetPlEditForm").parent().parent();
                var leftPanel=rightPanel.parent().find(".leftPanel");
                rightPanel.hide();rightPanel.html("");
                leftPanel.show("slide", { direction: "left" }, 500);
                oCache.iCacheLower = -1;
                oTableBudgetPl.fnDraw(false);
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('Budget Plan', 'Edit', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                }else {
                    createSysAct('Budget Plan', 'Edit', 1, '');
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                }
                $("#dialog").dialog({
                    title: '<?php echo DIALOG_INFORMATION; ?>',
                    resizable: false,
                    modal: true,
                    width: 'auto',
                    height: 'auto',
                    open: function(event, ui){
                        $(".ui-dialog-buttonpane").show();
                    },
                    buttons: {
                        '<?php echo ACTION_CLOSE; ?>': function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });

        // group expansion
        $("#<?php echo $printArea; ?> .group td:first-child").prepend("<img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' class='<?php echo $btnPlusMinus; ?>' /> ");
        $("#<?php echo $printArea; ?> .group td:first-child").css("cursor", "pointer");
        $("#<?php echo $printArea; ?> .groupDetail").css("background", "#EEE");
        $("#<?php echo $printArea; ?> .group td:first-child").click(function(){
            if($("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id=" + $(this).attr("chart_account_group_id") + "]").is(':visible')==false){
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            }else{
                $("img.<?php echo $btnPlusMinus; ?>", this).attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            }
            $("#<?php echo $printArea; ?> .groupDetail[chart_account_group_id="+$(this).attr("chart_account_group_id")+"]").toggle();
        });
        $("#<?php echo $btnShowAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/minus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").show();
        });
        $("#<?php echo $btnHideAll; ?>").click(function(event){
            event.preventDefault();
            $("img.<?php echo $btnPlusMinus; ?>").attr("src", "<?php echo $this->webroot; ?>img/plus.gif");
            $("#<?php echo $printArea; ?> .groupDetail").hide();
        });

        // init calc
        for(p=1;p<=12;p++){
            calcIncomeDetail(p);
            calcCogsDetail(p);
            calcExpenseDetail(p);
            calcIncomeOtherDetail(p);
            calcExpenseOtherDetail(p);
            calcExpenseInterestDetail(p);
            calcExpenseTaxDetail(p);
        }
        
        $(".btnBackBudgetPl").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $(".ui-tabs-panel").scroll(function() {
            var legend    = parseInt($("#header_<?php echo $rnd; ?>").height());
            var filter    = parseInt($("#info_<?php echo $rnd; ?>").height());
            var tableHead = parseInt($("#table_<?php echo $rnd; ?>").find("tr:eq(0)").height());
            var boxSearch = legend + filter + tableHead + 65;
            dynamicHeaderSetting();
            if(boxSearch < $(this).scrollTop()){
                $("#<?php echo $rnd; ?>_head").show();
            }else{
                $("#<?php echo $rnd; ?>_head").hide();
            }
        });
    });
    function dynamicHeaderSetting(){
        var heigthNorth = $(".ui-layout-north").height();
        var heigthResizer = $(".ui-layout-resizer").height();
        var tabNav       = $(".ui-tabs-nav").height();
        var HeigthTop    = parseInt(heigthNorth + heigthResizer + tabNav) + 39;
        var headClone = $("#table_<?php echo $rnd; ?>").find("tr:eq(0)").html();
        var tableWidth = $("#table_<?php echo $rnd; ?>").width();
        $("#<?php echo $rnd; ?>_head").css("top",HeigthTop+"px");
        $("#<?php echo $rnd; ?>_head").css("width",tableWidth);
        $("#<?php echo $rnd; ?>_head").find("tr:eq(0)").html(headClone);
        $("#<?php echo $rnd; ?>_head").find("tr:eq(0)").find(".first").html('');
    }
    function calcIncome(index){
        setTimeout(function() {
            var totalRow=0;
            $(".income"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalIncome"+index).val(totalRow);

            $(".income13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .income"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".income13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalIncome13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcIncomeDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".incomeDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".income"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".income"+index).val(totalRow);
            });

            $(".incomeDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .incomeDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcIncome(index);
        }, 100);
    }
    function calcCogs(index){
        setTimeout(function() {
            var totalRow=0;
            $(".cogs"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalCogs"+index).val(totalRow);

            $(".cogs13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .cogs"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".cogs13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalCogs13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcCogsDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".cogsDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".cogs"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".cogs"+index).val(totalRow);
            });

            $(".cogsDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .cogsDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcCogs(index);
        }, 100);
    }
    function calcExpense(index){
        setTimeout(function() {
            var totalRow=0;
            $(".expense"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpense"+index).val(totalRow);

            $(".expense13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expense"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".expense13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpense13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcExpenseDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".expenseDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".expense"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".expense"+index).val(totalRow);
            });

            $(".expenseDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcExpense(index);
        }, 100);
    }
    function calcIncomeOther(index){
        setTimeout(function() {
            var totalRow=0;
            $(".incomeOther"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalIncomeOther"+index).val(totalRow);

            $(".incomeOther13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .incomeOther"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".incomeOther13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalIncomeOther13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcIncomeOtherDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".incomeOtherDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".incomeOther"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".incomeOther"+index).val(totalRow);
            });

            $(".incomeOtherDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .incomeOtherDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcIncomeOther(index);
        }, 100);
    }
    function calcExpenseOther(index){
        setTimeout(function() {
            var totalRow=0;
            $(".expenseOther"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseOther"+index).val(totalRow);

            $(".expenseOther13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseOther"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".expenseOther13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseOther13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcExpenseOtherDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".expenseOtherDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".expenseOther"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".expenseOther"+index).val(totalRow);
            });

            $(".expenseOtherDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseOtherDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcExpenseOther(index);
        }, 100);
    }
    function calcExpenseInterest(index){
        setTimeout(function() {
            var totalRow=0;
            $(".expenseInterest"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseInterest"+index).val(totalRow);

            $(".expenseInterest13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseInterest"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".expenseInterest13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseInterest13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcExpenseInterestDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".expenseInterestDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".expenseInterest"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".expenseInterest"+index).val(totalRow);
            });

            $(".expenseInterestDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseInterestDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcExpenseInterest(index);
        }, 100);
    }
    function calcExpenseTax(index){
        setTimeout(function() {
            var totalRow=0;
            $(".expenseTax"+index).each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseTax"+index).val(totalRow);

            $(".expenseTax13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseTax"+i).val());
                }
                $(this).val(totalCol);
            });

            var totalRow=0;
            $(".expenseTax13").each(function(){
                totalRow+=Number($(this).val());
            });
            $("#totalExpenseTax13").val(totalRow);

            calcTotal(index);
        }, 100);
    }
    function calcExpenseTaxDetail(index){
        setTimeout(function() {
            var totalRow=0;
            var chart_account_group_id;
            $(".expenseTaxDetail"+index).each(function(){
                if($(this).closest("tr").attr("chart_account_group_id")!=chart_account_group_id){
                    totalRow=0;
                }
                totalRow+=Number($(this).val());
                chart_account_group_id=$(this).closest("tr").attr("chart_account_group_id");
                $(".expenseTax"+index).closest("tr[chart_account_group_id="+chart_account_group_id+"]").find(".expenseTax"+index).val(totalRow);
            });

            $(".expenseTaxDetail13").each(function(){
                var totalCol=0;
                for(i=1;i<=12;i++){
                    totalCol+=Number($(this).closest("tr").find("td .expenseTaxDetail"+i).val());
                }
                $(this).val(totalCol);
            });

            calcExpenseTax(index);
        }, 100);
    }
    function calcTotal(index){
        $("#grossProfit"+index).val(Number($("#totalIncome"+index).val())-Number($("#totalCogs"+index).val()));
        $("#profitLossBeforeInterestTax"+index).val(Number($("#grossProfit"+index).val())-Number($("#totalExpense"+index).val())+Number($("#totalIncomeOther"+index).val())-Number($("#totalExpenseOther"+index).val()));
        $("#profitLossBeforeTax"+index).val(Number($("#profitLossBeforeInterestTax"+index).val())-Number($("#totalExpenseInterest"+index).val()));
        $("#profitLoss"+index).val(Number($("#profitLossBeforeTax"+index).val())-Number($("#totalExpenseTax"+index).val()));
        $("#grossProfit13").val(Number($("#totalIncome13").val())-Number($("#totalCogs13").val()));
        $("#profitLossBeforeInterestTax13").val(Number($("#grossProfit13").val())-Number($("#totalExpense13").val())+Number($("#totalIncomeOther13").val())-Number($("#totalExpenseOther13").val()));
        $("#profitLossBeforeTax13").val(Number($("#profitLossBeforeInterestTax13").val())-Number($("#totalExpenseInterest13").val()));
        $("#profitLoss13").val(Number($("#profitLossBeforeTax13").val())-Number($("#totalExpenseTax13").val()));
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackBudgetPl">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="<?php echo $printArea; ?>">
<?php echo $this->Form->create('BudgetPl', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<?php echo $this->Form->input('id'); ?>
<fieldset>
    <legend><?php __(MENU_BUDGET_PLAN_PL_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="BudgetPlCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('company_id', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BudgetPlYear"><?php echo REPORT_FOR_YEAR; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('year', array('class'=>'validate[required,custom[integer]]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="BudgetPlName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="BudgetPlDescription"><?php echo GENERAL_DESCRIPTION; ?>:</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->textarea('description'); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset>
    <legend><?php __(MENU_PROFIT_AND_LOSS); ?></legend>
    <table class="table" cellspacing="0">
        <tr>
            <th class="first">
                <a href="" id="<?php echo $btnHideAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/plus.gif' onmouseover="Tip('Hide All')" /></a>
                <a href="" id="<?php echo $btnShowAll; ?>"><img alt='' src='<?php echo $this->webroot; ?>img/minus.gif' onmouseover="Tip('Show All')" /></a>
            </th>
            <?php for($i=0;$i<12;$i++){ ?>
            <th style="text-align: center;"><?php echo $monthName[$i]; ?></th>
            <?php } ?>
            <th style="text-align: center;"><?php echo TABLE_TOTAL; ?></th>
        </tr>
        <?php
        $sqlGroupIncome="   SELECT g.id,g.name
                            FROM chart_account_groups g
                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                            WHERE g.is_active=1 AND t.name IN ('Income')
                            ORDER BY t.id";
        $queryGroupIncome=mysql_query($sqlGroupIncome);
        while($dataGroupIncome=mysql_fetch_array($queryGroupIncome)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupIncome['id']; ?>" />
                <?php echo $dataGroupIncome['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupIncome['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] income<?php echo $i; ?>" onkeyup="calcIncome(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="income<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupIncome['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupIncome['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] incomeDetail<?php echo $i; ?>" onkeyup="calcIncomeDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="incomeDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Total Revenue</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="totalIncome<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="totalIncome<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>
        <?php
        $sqlGroupCOGS=" SELECT g.id,g.name
                        FROM chart_account_groups g
                            INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                        WHERE g.is_active=1 AND t.name IN ('Cost of Goods Sold')
                        ORDER BY t.id";
        $queryGroupCOGS=mysql_query($sqlGroupCOGS);
        while($dataGroupCOGS=mysql_fetch_array($queryGroupCOGS)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupCOGS['id']; ?>" />
                <?php echo $dataGroupCOGS['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupCOGS['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] cogs<?php echo $i; ?>" onkeyup="calcCogs(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="cogs<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupCOGS['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupCOGS['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] cogsDetail<?php echo $i; ?>" onkeyup="calcCogsDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="cogsDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Cost of Goods Sold</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="totalCogs<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="totalCogs<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Gross Profit</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="grossProfit<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="grossProfit<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>
        <?php
        $sqlGroupExpense="  SELECT g.id,g.name
                            FROM chart_account_groups g
                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                            WHERE g.is_active=1 AND t.name IN ('Expense') AND g.is_depreciation NOT IN (2,3)
                            ORDER BY t.id";
        $queryGroupExpense=mysql_query($sqlGroupExpense);
        while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupExpense['id']; ?>" />
                <?php echo $dataGroupExpense['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupExpense['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expense<?php echo $i; ?>" onkeyup="calcExpense(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expense<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseDetail<?php echo $i; ?>" onkeyup="calcExpenseDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Total Expenses</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="totalExpense<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="totalExpense<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>




        <?php
        $sqlGroupOtherIncome="  SELECT g.id,g.name
                                FROM chart_account_groups g
                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                WHERE g.is_active=1 AND t.name IN ('Other Income')
                                ORDER BY t.id";
        $queryGroupOtherIncome=mysql_query($sqlGroupOtherIncome);
        while($dataGroupOtherIncome=mysql_fetch_array($queryGroupOtherIncome)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupOtherIncome['id']; ?>" />
                <?php echo $dataGroupOtherIncome['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupOtherIncome['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] incomeOther<?php echo $i; ?>" onkeyup="calcIncomeOther(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="incomeOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherIncome['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherIncome['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] incomeOtherDetail<?php echo $i; ?>" onkeyup="calcIncomeOtherDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="incomeOtherDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Total Other Revenue</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="totalIncomeOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="totalIncomeOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>
        <?php
        $sqlGroupOtherExpense=" SELECT g.id,g.name
                                FROM chart_account_groups g
                                    INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                                WHERE g.is_active=1 AND t.name IN ('Other Expense') AND g.is_depreciation NOT IN (2,3)
                                ORDER BY t.id";
        $queryGroupOtherExpense=mysql_query($sqlGroupOtherExpense);
        while($dataGroupOtherExpense=mysql_fetch_array($queryGroupOtherExpense)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupOtherExpense['id']; ?>" />
                <?php echo $dataGroupOtherExpense['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupOtherExpense['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseOther<?php echo $i; ?>" onkeyup="calcExpenseOther(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupOtherExpense['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupOtherExpense['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseOtherDetail<?php echo $i; ?>" onkeyup="calcExpenseOtherDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseOtherDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Total Other Expenses</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="totalExpenseOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="totalExpenseOther<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <tr><td colspan="14" style="border-right: 0px;">&nbsp;</td></tr>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;"><b>Earnings Before Interest & Tax</b></td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="profitLossBeforeInterestTax<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="profitLossBeforeInterestTax<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        $sqlGroupExpense="  SELECT g.id,g.name
                            FROM chart_account_groups g
                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                            WHERE g.is_active=1 AND t.name IN ('Expense','Other Expense') AND g.is_depreciation IN (2)
                            ORDER BY t.id";
        $queryGroupExpense=mysql_query($sqlGroupExpense);
        while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupExpense['id']; ?>" />
                <?php echo $dataGroupExpense['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupExpense['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseInterest<?php echo $i; ?>" onkeyup="calcExpenseInterest(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseInterest<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseInterestDetail<?php echo $i; ?>" onkeyup="calcExpenseInterestDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseInterestDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;">
                <?php for($i=1;$i<=12;$i++){ ?>
                <input type="hidden" value="0" id="totalExpenseInterest<?php echo $i; ?>" />
                <?php } ?>
                <input type="hidden" value="0" id="totalExpenseInterest<?php echo $i; ?>" />
                <b>Earnings Before Tax</b>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="profitLossBeforeTax<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="profitLossBeforeTax<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        $sqlGroupExpense="  SELECT g.id,g.name
                            FROM chart_account_groups g
                                INNER JOIN chart_account_types t ON g.chart_account_type_id=t.id
                            WHERE g.is_active=1 AND t.name IN ('Expense','Other Expense') AND g.is_depreciation IN (3)
                            ORDER BY t.id";
        $queryGroupExpense=mysql_query($sqlGroupExpense);
        while($dataGroupExpense=mysql_fetch_array($queryGroupExpense)){
        ?>
        <tr class="group" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
            <td class="first" style="white-space: nowrap;" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>">
                <input type="hidden" name="chart_account_group_id[]" value="<?php echo $dataGroupExpense['id']; ?>" />
                <?php echo $dataGroupExpense['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <input type="text" value="" id="g<?php echo $dataGroupExpense['id']; ?>m<?php echo $i; ?>" name="gm<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseTax<?php echo $i; ?>" onkeyup="calcExpenseTax(<?php echo $i; ?>)" style="width: 45px;" readonly="readonly" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseTax<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php
        // group expansion
        $sqlGroupDetail="SELECT id,CONCAT_WS(' ',account_codes,'·',account_description) AS name FROM chart_accounts WHERE is_active=1 AND chart_account_group_id=" . $dataGroupExpense['id'] . " AND id NOT IN (SELECT parent_id FROM chart_accounts WHERE is_active=1 AND parent_id IS NOT NULL) ORDER BY account_codes";
        $queryGroupDetail=mysql_query($sqlGroupDetail);
        while($dataGroupDetail=mysql_fetch_array($queryGroupDetail)){
        ?>
        <tr class="groupDetail" chart_account_group_id="<?php echo $dataGroupExpense['id']; ?>" style="display: none;">
            <td class="first" style="white-space: nowrap;padding-left: 25px;" chart_account_id="<?php echo $dataGroupDetail['id']; ?>">
                <input type="hidden" name="chart_account_id[]" value="<?php echo $dataGroupDetail['id']; ?>" />
                <?php echo $dataGroupDetail['name']; ?>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td>
                <div class="inputContainer">
                    <?php
                    $queryCell=mysql_query("SELECT m".$i." FROM budget_pl_details WHERE budget_pl_id=".$this->data['BudgetPl']['id']." AND chart_account_id=".$dataGroupDetail['id']);
                    $dataCell=mysql_fetch_array($queryCell);
                    ?>
                    <input type="text" value="<?php echo $dataCell[0]; ?>" id="c<?php echo $dataGroupDetail['id']; ?>m<?php echo $i; ?>" name="m<?php echo $i; ?>[]" class="number validate[optional,custom[number]] expenseTaxDetail<?php echo $i; ?>" onkeyup="calcExpenseTaxDetail(<?php echo $i; ?>)" style="width: 45px;" />
                </div>
            </td>
            <?php } ?>
            <td><input type="text" value="0" class="expenseTaxDetail<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr style="background: #f4ffab;">
            <td class="first" style="white-space: nowrap;">
                <?php for($i=1;$i<=12;$i++){ ?>
                <input type="hidden" value="0" id="totalExpenseTax<?php echo $i; ?>" />
                <?php } ?>
                <input type="hidden" value="0" id="totalExpenseTax<?php echo $i; ?>" />
                <b>Profit/Loss for the Year</b>
            </td>
            <?php for($i=1;$i<=12;$i++){ ?>
            <td><input type="text" value="0" id="profitLoss<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
            <?php } ?>
            <td><input type="text" value="0" id="profitLoss<?php echo $i; ?>" style="width: 45px;" readonly="readonly" /></td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
        <span class="txtSave"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>
</div>