<?php
$this->element('check_access');
$allowAdd = checkAccess($user['User']['id'], $this->params['controller'], 'add');
$tblName = "tbl" . rand(); 
$sqlCom = mysql_query("SELECT id, name FROM companies WHERE is_active = 1 AND id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].");");
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#branchExchangeRate").filterOptions('com', '0', '');
        $(".btnAddExchangeRate").click(function(event){
            event.preventDefault();
            var leftPanel=$(this).parent().parent().parent();
            var rightPanel=leftPanel.parent().find(".rightPanel");
            leftPanel.hide("slide", { direction: "left" }, 500, function() {
                rightPanel.show();
            });
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/add/");
        });
        $("#companyExchangeRate").change(function(){
            $("#branchExchangeRate").filterOptions('com', $(this).val(), '');
            $("#branchExchangeRate").change();
        });
        $("#branchExchangeRate").change(function(){
            loadExchnage();
        });
        <?php
        if(mysql_num_rows($sqlCom) == 1){
        ?>
        $("#companyExchangeRate").change();
        <?php
        }
        ?>
    });
    
    function loadExchnage(){
        var companyId = $("#companyExchangeRate").find("option:selected").val();
        var branchId  = $("#branchExchangeRate").find("option:selected").val();
        if(companyId != '' && branchId != ''){
            $.ajax({
                type: "POST",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/ajax"; ?>/"+branchId,
                beforeSend: function() {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $("#divExchange").html('<tr><td colspan="10" class="dataTables_empty first"><?php echo TABLE_LOADING; ?></td></tr>');
                },
                success: function(result) {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#divExchange").html(result);
                    <?php
                    if($allowAdd){
                    ?>
                    keyEventExchange();
                    <?php
                    }
                    ?>
                }
            });
        }
    }
    <?php
    if($allowAdd){
    ?>
    function keyEventExchange(){
        
        $(".btnViewExchangeHistory").unbind("keyup").unbind("focus").unbind("blur").unbind("click");
        $(".rateSell, .rateChange, .ratePurchase").unbind("keyup").unbind("focus").unbind("blur");
        $(".rateSell, .rateChange, .ratePurchase").focus(function(){
            var value = replaceNum($(this).val());
            if(value == '0'){
                $(this).val('');
            }
        });
        
        $(".rateSell, .rateChange, .ratePurchase").blur(function(){
            var value = $(this).val();
            if(value == ''){
                $(this).val('0');
            }
        });
        
        $(".btnViewExchangeHistory").click(function(event){            
            event.preventDefault();
            var comId = $(this).attr('com-id');
            var currencyCenterId = $(this).attr('currency-center-id');
            $.ajax({
                type: "POST",
                url:    "<?php echo $this->base . "/".$this->params['controller']."/view"; ?>/"+ comId + "/" +currencyCenterId,
                beforeSend: function() {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result) {
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
                    $("#dialog").html(result);
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_CONFIRMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: '90%',
                        height: '500',
                        position: 'center',
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
        });
        
        $(".btnEditExchangeRate").click(function(event){
            event.preventDefault();
            $(this).closest("tr").find(".btnSaveExchangeRate").show();
            $(this).closest("tr").find(".btnEditExchangeRate").hide();
            $(this).closest("tr").find(".rateSell").attr("readonly", false);
            $(this).closest("tr").find(".rateChange").attr("readonly", false);
            $(this).closest("tr").find(".ratePurchase").attr("readonly", false);
        });
        
        $(".btnSaveExchangeRate").click(function(event){
            event.preventDefault();
            $(this).closest("tr").find(".btnSaveExchangeRate").hide();
            $(this).closest("tr").find(".lblSaveExchangeRate").show();
            saveExchange($(this));
        });
    }
    
    function saveExchange(obj){
        var comCurrencyId = obj.attr("rel");
        var rateSell      = obj.closest("tr").find(".rateSell").val();
        var rateChange    = obj.closest("tr").find(".rateChange").val();
        var ratePurchase  = obj.closest("tr").find(".ratePurchase").val();
        $.ajax({
            type: "POST",
            url:    "<?php echo $this->base . "/".$this->params['controller']."/add"; ?>/"+comCurrencyId,
            data:   "rate_sell="+rateSell+"&rate_change="+rateChange+"&rate_purchase="+ratePurchase,
            beforeSend: function() {
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner.gif');
                obj.closest("tr").find(".rateSell").attr("readonly", true);
                obj.closest("tr").find(".rateChange").attr("readonly", true);
            },
            success: function() {
                // alert message
                createSysAct('Exchange Rate', 'Add', 1, '');
                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                obj.closest("tr").find(".lblSaveExchangeRate").hide();
                obj.closest("tr").find(".btnEditExchangeRate").show();
            }
        });
    }
    <?php
    }
    ?>
</script>
<div class="leftPanel">
    <div style="padding: 5px;border: 1px dashed #bbbbbb;">
        <div style="float:left;">
            <?php echo TABLE_COMPANY; ?> :
            <select id="companyExchangeRate" style="width:200px; height: 30px;">
                <?php
                if(mysql_num_rows($sqlCom) != 1){
                ?>
                <option value=""><?php echo INPUT_SELECT; ?></option>
                <?php
                }
                while($rowCom = mysql_fetch_array($sqlCom)){
                ?>
                <option value="<?php echo $rowCom['id']; ?>"><?php echo $rowCom['name']; ?></option>
                <?php
                }
                ?>
            </select>
            &nbsp;&nbsp;&nbsp;
            <?php echo MENU_BRANCH; ?> :
            <select id="branchExchangeRate" style="width:200px; height: 30px;">
                <?php
                $sqlBranch = mysql_query("SELECT id, name, company_id FROM branches WHERE is_active = 1 AND id IN (SELECT branch_id FROM user_branches WHERE user_id = ".$user['User']['id'].");");
                if(mysql_num_rows($sqlBranch) != 1){
                ?>
                <option value="" com=""><?php echo INPUT_SELECT; ?></option>
                <?php
                }
                while($rowBranch = mysql_fetch_array($sqlBranch)){
                ?>
                <option value="<?php echo $rowBranch['id']; ?>" com="<?php echo $rowBranch['company_id']; ?>"><?php echo $rowBranch['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div id="dynamic">
        <table id="<?php echo $tblName; ?>" class="table" cellspacing="0">
            <thead>
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo TABLE_COMPANY; ?></th>
                    <th><?php echo REPORT_FROM; ?></th>
                    <th><?php echo TABLE_RATE; ?></th>
                    <th><?php echo REPORT_TO; ?></th>
                    <th><?php echo TABLE_RATE_FOR_SELL; ?></th>
                    <th><?php echo TABLE_RATE_FOR_CHANGE; ?></th>
                    <th><?php echo TABLE_RATE_FOR_PURCHASE; ?></th>
                    <?php if($allowAdd){ ?> 
                    <th><?php echo ACTION_ACTION; ?></th>
                    <?php } ?>
                    <th><?php echo TABLE_MODIFIED; ?></th>
                </tr>
            </thead>
            <tbody id="divExchange"></tbody>
        </table>
    </div>
    <br />
</div>
<div class="rightPanel"></div>