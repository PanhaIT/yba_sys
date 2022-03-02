<?php 
$rnd = rand();
$class = "class" . $rnd;
$frmName   = "frm" . $rnd;
$date      = "date" . $rnd;
$interval  = "interval" . $rnd;
$through   = "through" . $rnd;
$vgroup    = "vgroup" . $rnd;
$vgroupId  = "vgroupId" . $rnd;
$vgroupDel = "vgroupDel" . $rnd;
$vendor    = "vendor" . $rnd;
$vendorId  = "vendorId".$rnd;
$vendorDel = "vendorDel".$rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $interval; ?>, #<?php echo $through; ?>").autoNumeric({mDec: 0, aSep: ''});
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var now = new Date();
        $("#<?php echo $date; ?>").val(now.toString('dd/MM/yyyy'));
        $("#<?php echo $date; ?>").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#<?php echo $interval; ?>").blur(function(){
            if(Number($(this).val())>Number($("#<?php echo $through; ?>").val())){
                $("#<?php echo $through; ?>").val($(this).val());
            }
        });
        $("#<?php echo $through; ?>").blur(function(){
            if(Number($(this).val())<Number($("#<?php echo $interval; ?>").val())){
                $("#<?php echo $interval; ?>").val($(this).val());
            }
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated=$("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/accountPayableResult",
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(result);
                    }
                });
            }
        });
        // Button Show Hide
        $("#<?php echo $btnShowHide; ?>").click(function(){
            var text = $(this).text();
            var formFilter = $(".<?php echo $formFilter; ?>");
            if(text == "[<?php echo TABLE_SHOW; ?>]"){
                formFilter.show();
                $(this).text("[<?php echo TABLE_HIDE; ?>]");
            }else{
                formFilter.hide();
                $(this).text("[<?php echo TABLE_SHOW; ?>]");
            }
        });
        
        // Search Vendor
        $("#<?php echo $vendor; ?>").autocomplete("<?php echo $this->base . "/reports/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var pgroupId = value.toString().split(".*")[0];
            $("#<?php echo $vendorId; ?>").val(pgroupId);
            $("#<?php echo $vendorDel; ?>").show();
        });
        
        $("#<?php echo $vendorDel; ?>").click(function(){
            $("#<?php echo $vendorId; ?>").val('');
            $("#<?php echo $vendor; ?>").val('');
            $(this).hide();
        });
        
        // Search Vendor Group
        $("#<?php echo $vgroup; ?>").autocomplete("<?php echo $this->base . "/reports/searchVgroup"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1];
            }
        }).result(function(event, value){
            var pgroupId = value.toString().split(".*")[0];
            $("#<?php echo $vgroupId; ?>").val(pgroupId);
            $("#<?php echo $vgroupDel; ?>").show();
        });
        
        $("#<?php echo $vgroupDel; ?>").click(function(){
            $("#<?php echo $vgroupId; ?>").val('');
            $("#<?php echo $vgroup; ?>").val('');
            $(this).hide();
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_ACCOUNT_PAYABLE; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $date; ?>"><?php echo TABLE_DATE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $date; ?>" name="date" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $interval; ?>"><?php echo REPORT_INTERVAL; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $interval; ?>" name="interval" value="30" class="validate[required,custom[integer],min[1]]" />
                    </div>
                </td>
                <td style="width: 13%;"><label for="<?php echo $through; ?>"><?php echo REPORT_THROUGH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $through; ?>" name="through" value="90" class="validate[required,custom[integer],min[1]]" />
                    </div>
                </td>
                <td colspan="3">
                    <div class="buttons">
                        <button type="button" id="<?php echo $btnSearch; ?>" class="positive" style="width: 130px;">
                            <img src="<?php echo $this->webroot; ?>img/button/search.png" alt=""/>
                            <span id="<?php echo $btnSearchLabel; ?>"><?php echo GENERAL_SEARCH; ?></span>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 8%;"><label for="<?php echo $vendor; ?>"><?php echo TABLE_VENDOR; ?>:</label></td>
                <td style="width: 30%;">
                    <div class="inputContainer">
                        <input type="hidden" name="vendor_id" id="<?php echo $vendorId; ?>" />
                        <?php echo $this->Form->text($vendor, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $vendorDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $vgroup; ?>"><?php echo TABLE_VENDOR_GROUP; ?>:</label></td>
                <td style="width: 30%;">
                    <div class="inputContainer">
                        <input type="hidden" name="vgroup_id" id="<?php echo $vgroupId; ?>" />
                        <?php echo $this->Form->text($vgroup, array('escape' => false, 'name' => '', 'style' => 'width: 80%;')); ?>
                        <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" id="<?php echo $vgroupDel; ?>" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>