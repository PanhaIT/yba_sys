<?php $tblName = "tbl" . rand(); ?>
<?php
$queryClosingDate=mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate=mysql_fetch_array($queryClosingDate);
?>
<?php echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    function loadTableFixedAsset(){
        if($("#FixedAssetDate").val()!="" && $("#FixedAssetCompanyId").val()!="" && $("#FixedAssetBranchId").val()!=""){
            $("#FixedAssetDate").datepicker("option", "dateFormat", "yy-mm-dd");
            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            $("#tblFixedAsset").load("<?php echo $this->base . '/' . $this->params['controller']; ?>/postDetail/"+$("#FixedAssetCompanyId").val()+"/"+$("#FixedAssetBranchId").val()+"/"+$("#FixedAssetDate").val(), function() {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
            });
            $("#FixedAssetDate").datepicker("option", "dateFormat", "dd/mm/yy");
        }
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#FixedAssetBranchId").filterOptions('com', '0', '');
        // close conflict tab(s)
        $('#tabs a').not("[href=#]").each(function() {
            if($.data(this, 'href.tabs')=="<?php echo $this->base; ?>/sales_orders/index"){
                $("#tabs").tabs("remove", $(this).attr("href"));
            }
        });
        loadTableFixedAsset();
        $("#FixedAssetForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#FixedAssetDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            minDate: '<?php echo $dataClosingDate[0]; ?>',
            onSelect: function(dateText, inst) {
                loadTableFixedAsset();
            },
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#FixedAssetCompanyId").change(function(){
            loadChartAccAssetPost();
            $("#FixedAssetBranchId").filterOptions('com', $(this).val(), '');
            $("#FixedAssetBranchId").change();
            loadTableFixedAsset();
        });
        $(".btnBackFixedAsset").click(function(event){
            event.preventDefault();
            var rightPanel=$(this).parent().parent().parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
    });
    
    function loadChartAccAssetPost(){
        var companyId = $("#FixedAssetCompanyId").val();
        // Chart Account Filter
        $(".adj_account_coa_id").filterOptions('company_id', companyId, '');
    }
</script>
<?php echo $this->Form->create('FixedAsset', array ('id'=>'FixedAssetForm', 'url'=>'/fixed_assets/save/')); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;">
        <div class="buttons">
            <a href="" class="positive btnBackFixedAsset">
                <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
                <?php echo ACTION_BACK; ?>
            </a>
        </div>
    </div>
    <div style="float:right;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="FixedAssetDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => 'readonly')); ?>
        </div>
        <div class="inputContainer">
            <label for="FixedAssetCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->select('company_id', $companies, null, array('escape' => false, 'class' => 'validate[required]', 'empty' => INPUT_SELECT)); ?>
        </div>
        <div class="inputContainer">
            <label for="FixedAssetBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label>
            <select name="data[FixedAsset][branch_id]" id="FixedAssetBranchId" class="validate[required]">
                <?php
                if(count($branches) != 1){
                ?>
                <option value="" com=""><?php echo INPUT_SELECT; ?></option>
                <?php
                }
                foreach($branches AS $branch){
                ?>
                <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="tblFixedAsset">
    
</div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;padding-right: 10px;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="FixedAssetReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label>
            <input type="hidden" id="tableName" name="tableName" value="general_ledgers" />
            <input type="hidden" id="fieldCurrentId" name="fieldCurrentId" value="" />
            <input type="hidden" id="fieldName" name="fieldName" value="reference" />
            <input type="hidden" id="fieldCondition" name="fieldCondition" value="is_active=1" />
            <?php echo $this->Form->text('reference', array('class' => 'validate[required]', 'style' => 'width: 100px;')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="FixedAssetNote"><?php echo TABLE_NOTE; ?>:</label>
            <?php echo $this->Form->text('note', array('style' => 'width: 100px;')); ?>
        </div>
    </div>
    <div class="buttons">
        <button type="submit" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSaveFixedAsset"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>