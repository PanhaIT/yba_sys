<?php
$rnd = rand();
$frmName    = "frm" . $rnd;
$dueDate    = "dueDate" . $rnd;
$dateFrom   = "dateFrom" . $rnd;
$dateTo     = "dateTo" . $rnd;
$other      = "other" . $rnd;
$vendorCode = "vendorCode" . $rnd;
$company    = "company" . $rnd;
$province   = "province" . $rnd;
$district   = "district" . $rnd;
$commune = "commune" . $rnd;
$village = "village" . $rnd;
$createdBy = "createdBy" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch = "btnSearch" . $rnd;
$btnShowHide = "btnShowHide". $rnd;
$formFilter  = "formFilter".$rnd;
$result = "result" . $rnd;
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $frmName; ?>").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var dates = $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "<?php echo $dateFrom; ?>" ? "minDate" : "maxDate",
                instance = $( this ).data( "datepicker" );
                date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        $("#<?php echo $dueDate; ?>").change(function(){
            var date = getDateByDateRange($(this).val());
            $('#<?php echo $dateTo; ?>').datepicker( "option", "minDate", date[0]);
            $('#<?php echo $dateFrom; ?>').datepicker("setDate", date[0]);
            $('#<?php echo $dateTo; ?>').datepicker("setDate", date[1]);
        });
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated=$("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/vendorAddressListResult",
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(msg){
                        $("#<?php echo $btnSearch; ?>").removeAttr("disabled");
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo GENERAL_SEARCH; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        $("#<?php echo $result; ?>").html(msg);
                    }
                });
            }
        });

        $(".province").change(function(){
            if($(this).val()!=""){
                $(".district").val('');
                $(".district option[class!='']").hide();
                $(".district option[class='"  + $(this).val() + "']").show();
            }else{
                $(".district").val('');
                $(".commune").val('');
                $(".village").val('');
                $(".district option").show();
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
            comboRefesh(".district",".province");
        });
        $(".district").change(function(){
            if($(this).val()!=""){
                $(".province").val($(".district").find("option:selected").attr("class"));
                $(".commune").val('');
                $(".commune option[class!='']").hide();
                $(".commune option[class='"  + $(this).val() + "']").show();
            }else{
                $(".commune").val('');
                $(".village").val('');
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
            comboRefesh(".commune",".district");
        });
        $(".commune").change(function(){
            if($(this).val()!=""){
                var commune = $(this).val();
                $(".district").val($(".commune").find("option:selected").attr("class"));
                $(".province").val($(".district").find("option:selected").attr("class"));
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base . "/customers/getVillage"; ?>",
                    data: "data[commune][id]=" + commune,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(msg){
                        $(".village").html(msg);
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    }
                });
            }else{
                $(".village").val('');
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
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
    });
    function comboRefesh(obj,mainObj){
        selected=new Array();
        $(obj).each(function(){
            if($(this).val()!=''){
                selected.push($(this).val());
            }
        });
        $(obj).each(function(){
            if($(mainObj).val()!=''){
                $(this).find("option[class='"  + $(mainObj).val() + "']").show();
            }else{
                $(this).find("option").show();
            }
        });
    }
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_VENDOR_ADDRESS_LIST; ?> <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $dueDate; ?>"><?php echo REPORT_DUE_DATE; ?>:</label></td>
                <td style="width: 15%;"><?php echo $this->Form->select($dueDate, $dateRange, null, array('escape' => false, 'empty' => INPUT_SELECT)); ?></td>
                <td style="width: 8%;"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" />
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" />
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $vendorCode; ?>"><?php echo TABLE_CODE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $vendorCode; ?>" name="vendor_code" />
                    </div>
                </td>
                <td rowspan="3" valign="middle">
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
                <td style="width: 6%;"><label for="<?php echo $company; ?>"><?php echo TABLE_COMPANY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($company, $companies, null, array('escape' => false, 'name' => 'company_id', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $other; ?>"><?php echo TABLE_OTHER; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $other; ?>" name="other" />
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $province; ?>"><?php echo TABLE_CITY_PROVINCE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $province; ?>" class="province" name="province" >
                            <option value=""><?php echo INPUT_SELECT ?></option>
                            <?php
                            foreach ($provinces as $key => $value) {
                                echo "<option value='{$key}' >{$value}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $district; ?>"><?php echo TABLE_DISTRICT; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $district; ?>" class="district"  name="district">
                            <option value=""><?php echo INPUT_SELECT ?></option>
                            <?php
                            foreach ($districts as $value) {
                                echo "<option value='{$value['value']}' class='{$value['class']}' >{$value['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $commune; ?>"><?php echo TABLE_COMMUNE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $commune; ?>" class="commune" name="commune">
                            <option value=""><?php echo INPUT_SELECT ?></option>
                            <?php
                            foreach ($communes as $value) {
                                echo "<option value='{$value['value']}' class='{$value['class']}' >{$value['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $village; ?>"><?php echo TABLE_VILLAGE; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $village; ?>"  class="village"  name="village">
                            <option value=""><?php echo INPUT_SELECT ?></option>
                        </select>
                    </div>
                </td>
                <td style="width: 8%;"><label for="<?php echo $createdBy; ?>"><?php echo TABLE_CREATED_BY; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <select id="<?php echo $createdBy; ?>"  class="createdBy"  name="created_by">
                            <option value=""><?php echo INPUT_SELECT ?></option>
                            <?php
                            foreach ($users as $key => $value) {
                                echo "<option value='{$key}' >{$value}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>
</form>
<div id="<?php echo $result; ?>"></div>