<?php 
$rnd       = rand();
$frmName   = "frm" . $rnd;
$dateFrom  = "dateFrom" . $rnd;
$dateTo    = "dateTo" . $rnd;
$year      = "year" . $rnd;
$status    = "status" . $rnd;
$branch    = "branch" . $rnd;
$columns   = "columns" . $rnd;
$btnSearchLabel = "txtBtnSearch". $rnd;
$btnSearch      = "btnSearch" . $rnd;
$btnShowHide    = "btnShowHide". $rnd;
$formFilter     = "formFilter".$rnd;
$result         = "result" . $rnd;
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
        $("#<?php echo $btnSearch; ?>").click(function(){
            var isFormValidated=$("#<?php echo $frmName; ?>").validationEngine('validate');
            if(isFormValidated){
                $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "yy-mm-dd");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/totalPurchaseSummaryResult",
                    data: $("#<?php echo $frmName; ?>").serialize(),
                    beforeSend: function(){
                        $("#<?php echo $btnSearch; ?>").attr("disabled", true);
                        $("#<?php echo $btnSearchLabel; ?>").html("<?php echo ACTION_LOADING; ?>");
                        $(".loader").attr("src","<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").datepicker("option", "dateFormat", "dd/mm/yy");
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
        
        $("#<?php echo $columns; ?>").change(function(){
            var id = $(this).val();
            $("#<?php echo $dateFrom; ?>, #<?php echo $dateTo; ?>").val("");
            if(id == "3"){
                $(".divDateSalesSummary").show();
                $(".divYearSalesSummary").hide();
            } else if(id == "1"){
                $(".divDateSalesSummary").hide();
                $(".divYearSalesSummary").show();
            } else {
                $(".divDateSalesSummary, .divYearSalesSummary").hide();
            }
        });
    });
</script>
<form id="<?php echo $frmName; ?>" action="" method="post">
<div class="legend">
    <div class="legend_title">
        <?php echo MENU_REPORT_TOTAL_PURCHASE; ?> (Summary) <span class="btnShowHide" id="<?php echo $btnShowHide; ?>">[<?php echo TABLE_HIDE; ?>]</span>
        <div style="clear: both;"></div>
    </div>
    <div class="legend_content <?php echo $formFilter; ?>">
        <table style="width: 100%;">
            <tr>
                <td style="width: 6%;"><label for="<?php echo $columns; ?>"><?php echo TABLE_VIEW_BY; ?>:</label></td>
                <td style="width: 15%;">
                    <select id="<?php echo $columns; ?>" name="view_by">
                        <option value="1" selected=""><?php echo TABLE_MONTH; ?></option>
                        <option value="2"><?php echo TABLE_YEAR; ?></option>
                        <option value="3"><?php echo TABLE_DAYS; ?></option>
                    </select>
                </td>
                <td style="width: 6%; display: none;" class="divDateSalesSummary"><label for="<?php echo $dateFrom; ?>"><?php echo REPORT_FROM; ?>:</label></td>
                <td style="width: 15%; display: none;" class="divDateSalesSummary">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateFrom; ?>" name="date_from" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 6%; display: none;" class="divDateSalesSummary"><label for="<?php echo $dateTo; ?>"><?php echo REPORT_TO; ?>:</label></td>
                <td style="width: 15%; display: none;" class="divDateSalesSummary">
                    <div class="inputContainer">
                        <input type="text" id="<?php echo $dateTo; ?>" name="date_to" class="validate[required]" />
                    </div>
                </td>
                <td style="width: 6%;" class="divYearSalesSummary"><label for="<?php echo $year; ?>"><?php echo TABLE_YEAR; ?>:</label></td>
                <td style="width: 15%;" class="divYearSalesSummary">
                    <div class="inputContainer">
                        <select name="year" id="<?php echo $year; ?>" style="width: 90%;">
                            <?php
                            for($i=2018; $i<2041; $i++){
                                $selected = '';
                                if(date("Y") == $i){
                                    $selected = 'selected="selected"';
                                }
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td style="width: 6%;"><label for="<?php echo $branch; ?>"><?php echo MENU_BRANCH; ?>:</label></td>
                <td style="width: 15%;">
                    <div class="inputContainer">
                        <?php echo $this->Form->select($branch, $branches, null, array('escape' => false, 'name' => 'branch', 'empty' => TABLE_ALL)); ?>
                    </div>
                </td>
                <td>
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
</div>
</form>
<div id="<?php echo $result; ?>"></div>