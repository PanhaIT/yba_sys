<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include("includes/function.php");
$config = getSysconfig();
if(!empty($config)){
    $name    = $config['title'];
    $nameKh  = $config['titleKh'];
    $start   = $config['start'];
}else{
    $name    = "";
    $nameKh  = "";
    $start   = "";
}
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".interger").autoNumeric({aDec: '.', mDec: 5});
        $("#productCostDecimal").unbind("focus").focus(function(){
            if($(this).val() == '0'){
                $(this).val('');
            }
        });
        $("#productCostDecimal").unbind("blur").blur(function(){
            if($(this).val() == ''){
                $(this).val('2');
            }
        });
        $("#frmSystemConfig").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#frmSystemConfig").ajaxForm({
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveSysCon").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                // alert message
                if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                    createSysAct('General Setting', 'Save', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                    $(".txtSaveSysCon").html("<?php echo ACTION_SAVE; ?>");
                    $(".btnSaveSysCon").removeAttr('disabled');
                }else {
                    createSysAct('General Setting', 'Save', 1, '');
                    // alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
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
        // COGS
        $('#COGSCalDate').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        // Recalculate
        $('#COGSRecalculate').click(function(event){
            event.preventDefault();
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to re-calculate COGS?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                open: function(event, ui) {
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_NO; ?>': function() {
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_YES; ?>': function() {
                        $("#COGSCalDate").datepicker("option", "dateFormat", "yy-mm-dd");
                        var date = $("#COGSCalDate").val();
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/saveRecalculate/" + date,
                            beforeSend: function() {
                                $("#COGSCalDate").datepicker("option", "dateFormat", "dd/mm/yy");
                                $('#COGSRecalculate').hide();
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result) {
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                $('#COGSRecalculate').show();
                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        });
                    },
                }
            });
        });
    });
</script>
<form id="frmSystemConfig" action="<?php echo $this->base; ?>/general_settings/save/" method="post" enctype="multipart/form-data">
    <input type="hidden" name="data[system_start]" value="<?php echo $start; ?>" />
    <fieldset>
        <legend>System Configuration</legend>
        <?php
        $sqlSys = mysql_query("SELECT * FROM setting_options WHERE 1;");
        $rowSys = mysql_fetch_array($sqlSys);
        $purchase   = 0;
        $billReturn = 0;
        $invoice    = 0;
        $creditMemo = 0;
        $pos = 0;
        $sqlLocOpt = mysql_query("SELECT * FROM location_settings");
        while($rowLocOpt = mysql_fetch_array($sqlLocOpt)){
            if($rowLocOpt['modules'] == 'PB'){
                $purchase = $rowLocOpt['location_status'];
            } else if($rowLocOpt['modules'] == 'BR'){
                $billReturn = $rowLocOpt['location_status'];
            } else if($rowLocOpt['modules'] == 'POS'){
                $pos = $rowLocOpt['location_status'];
            } else if($rowLocOpt['modules'] == 'Sales'){
                $invoice = $rowLocOpt['location_status'];
            } else if($rowLocOpt['modules'] == 'CM'){
                $creditMemo = $rowLocOpt['location_status'];
            }
        }
        ?>
        <table cellpadding="5" cellspacing="0" style="width: 100%;">
            <?php if($rowSys['uom_detail_option'] == 0){ ?>
            <tr style="display: none;">
                <td style="width: 15%;"><label for="uomDetail">Lots/Series of Product:</label></td>
                <td>
                    <select id="uomDetail" name="data[uom_detail]" style="width: 210px; height: 30px;">
                        <option value="1" <?php if($rowSys['uom_detail_option'] == 0){ ?>selected=""<?php } ?>>Hide</option>
                        <option value="2" <?php if($rowSys['uom_detail_option'] == 1){ ?>selected=""<?php } ?>>Show</option>
                    </select>
                    <span style="color: red;">If you change lots/series of product to show, after change it will disable for editing.</span>
                </td>
            </tr>
            <?php
            } else {
            ?>
            <tr style="display: none;">
                <td style="width: 15%;"><label for="uomDetail">Show Lots/Series of Product:</label></td>
                <td>
                    Show
                </td>
            </tr>
            <?php
            }
            ?>
            <tr>
                <td style="width: 15%;"><label for="systemNameKh">System Name Khmer:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_name_kh]" id="systemNameKh" style="width: 200px;" class="validate[required]" value="<?php echo $nameKh; ?>" />
                </td>
            </tr>
            <tr>
                <td><label for="systemName">System Name:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_name]" id="systemName" style="width: 200px;" class="validate[required]" value="<?php echo $name; ?>" />
                </td>
            </tr>
            <tr>
                <td>System Logo Big:</td>
                <td>
                    <input type="file" id="systemPhotoBig" name="photo_big" />
                </td>
            </tr>
            <tr>
                <td>System Logo Small:</td>
                <td>
                    <input type="file" id="systemPhotoSmall" name="photo_small" />
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Modules with Location Configuration</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="locationPB">Purchase Bill:</label></td>
                <td>
                    <select id="locationPB" name="data[location_pb]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($purchase == 0){ ?>selected="selected"<?php } ?>>All</option>
                        <option value="1" <?php if($purchase == 1){ ?>selected="selected"<?php } ?>>Not For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationBR">Bill Return:</label></td>
                <td>
                    <select id="locationBR" name="data[location_br]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($billReturn == 0){ ?>selected="selected"<?php } ?>>All</option>
                        <option value="1" <?php if($billReturn == 1){ ?>selected="selected"<?php } ?>>Not For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationPOS">POS:</label></td>
                <td>
                    <select id="locationPOS" name="data[location_pos]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($pos == 0){ ?>selected="selected"<?php } ?>>All</option>
                        <option value="1" <?php if($pos == 1){ ?>selected="selected"<?php } ?>>For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationSale">Sales Invoice:</label></td>
                <td>
                    <select id="locationSale" name="data[location_sale]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($invoice == 0){ ?>selected="selected"<?php } ?>>All</option>
                        <option value="1" <?php if($invoice == 1){ ?>selected="selected"<?php } ?>>For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationCM">Credit Memo:</label></td>
                <td>
                    <select id="locationCM" name="data[location_cm]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($creditMemo == 0){ ?>selected="selected"<?php } ?>>All</option>
                        <option value="1" <?php if($creditMemo == 1){ ?>selected="selected"<?php } ?>>Not For Sale</option>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>
    <?php
    $POSShift = 0;
    $sqlPOSShift = mysql_query("SELECT id FROM shifts WHERE status = 1 OR status = 2 LIMIT 1");
    if(mysql_num_rows($sqlPOSShift)){
        $POSShift = 1;
    }
    $Delivery = 0;
    $sqlSales = mysql_query("SELECT id FROM sales_orders WHERE status = 1 LIMIT 1");
    if(mysql_num_rows($sqlSales)){
        $Delivery = 1;
    }
    ?>
    <fieldset>
        <legend>POS</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="shiftPOS">Shift Enable:</label></td>
                <td>
                    <?php
                    if($POSShift == 0){
                    ?>
                    <select id="shiftPOS" name="data[shift_pos]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($rowSys['shift'] == 0){ ?>selected=""<?php } ?>>No</option>
                        <option value="1" <?php if($rowSys['shift'] == 1){ ?>selected=""<?php } ?>>Yes</option>
                    </select>
                    <?php
                    } else {
                    ?>
                    Yes
                    <input type="hidden" name="data[shift_pos]" value="1" />
                    <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><label for="shiftPOSPrinter">Printer Name:</label></td>
                <td>
                    <input type="text" name="data[pos_printer]" id="shiftPOSPrinter" style="width: 190px; height: 15px;" />
                    <input type="checkbox" name="data[pos_print_silent]" value="1" id="shiftPOSPrintSilent" /> <label for="shiftPOSPrintSilent">Silent</label>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Sales/Invoice</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="DeliveryEnable">Delivery Enable:</label></td>
                <td>
                    <?php
                    if($Delivery == 0){
                    ?>
                    <select id="DeliveryEnable" name="data[allow_delivery]" style="width: 200px; height: 30px;">
                        <option value="0" <?php if($rowSys['allow_delivery'] == 0){ ?>selected=""<?php } ?>>No</option>
                        <option value="1" <?php if($rowSys['allow_delivery'] == 1){ ?>selected=""<?php } ?>>Yes</option>
                    </select>
                    <?php
                    } else {
                    ?>
                    Yes
                    <input type="hidden" name="data[allow_delivery]" value="1" />
                    <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Product Cost Decimal</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="productCostDecimal">Decimal:</label></td>
                <td>
                    <input type="text" name="data[product_decimal]" value="2" id="productCostDecimal" style="width: 100px; height: 15px;" class="interger" />
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>COGS Calculation</legend>
        <table cellpadding="5" cellspacing="0" style="width: 800px;" class="table">
            <tr>
                <th class="first" style="width: 25%;">Date Calculate</th>
                <th style="width: 25%;">Date Start</th>
                <th style="width: 25%;">Date End</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 10%;">Action</th>
            </tr>
            <?php
            $sqlTrack = mysql_query("SELECT * FROM tracks WHERE id = 1");
            $rowTrack = mysql_fetch_array($sqlTrack);
            ?>
            <tr>
                <td class="first">
                    <input type="text" id="COGSCalDate" value="<?php echo dateShort($rowTrack['val']); ?>" style="width: 70%; height: 25px; border: none;" />
                </td> 
                <td>
                    <?php 
                    if($rowTrack['date_start'] != '' && $rowTrack['date_start'] != '0000-00-00'){
                        echo dateShort($rowTrack['date_start'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($rowTrack['date_end'] != '' && $rowTrack['date_end'] != '0000-00-00'){
                        echo dateShort($rowTrack['date_end'], "d/m/Y H:i:s"); 
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($rowTrack['is_recalculate_process'] == 1){
                        echo 'Processing';
                    } else {
                        echo 'Stop';
                    }
                    ?>
                </td>
                <td>
                    <img onmouseover="Tip('Re-Caculate')" src="<?php echo $this->webroot; ?>img/button/refresh-active.png" id="COGSRecalculate" style="width: 18px; cursor: pointer;" />
                </td>
            </tr>
        </table>
    </fieldset>
    <br />
    <div class="buttons">
        <button type="submit" class="positive btnSaveSysCon">
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSaveSysCon"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</form>