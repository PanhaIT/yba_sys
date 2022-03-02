<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$this->element('check_access');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".interger").autoNumeric({aDec: '.', mDec: 5});
        $("#frmSystemConfig").validationEngine();
        $(".btnSaveSysCon").click(function(){
            var validateBack = $("#frmSystemConfig").validationEngine("validate");
            if(!validateBack){
                $(this).removeAttr('disabled');
                return false;
            }else{
                $(this).attr('disabled', 'disabled');
                $(".txtSaveSysCon").html("Loading..");
            }
        });
    });
</script>
<form id="frmSystemConfig" action="<?php echo $this->base; ?>/users/systemConfig/" method="post" enctype="multipart/form-data">
    <h2 style="text-decoration: underline; font-size: 22px; text-align: center; width: 100%;">SYSTEM SETUP</h2>
    <fieldset>
        <legend>System Configuration</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="cogsType">COGS Type:</label> <span style="color: red;">*</span></td>
                <td>
                    <select id="cogsType" name="data[cogs_type]" class="validate[required]" style="width: 150px; height: 30px;">
                        <option value="">Please Select</option>
                        <option value="1">AVG</option>
                        <option value="2">FIFO</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="uomDetail">Show UOM Detail:</label> <span style="color: red;">*</span></td>
                <td>
                    <select id="uomDetail" name="data[uom_detail]" class="validate[required]" style="width: 150px; height: 30px;">
                        <option value="">Please Select</option>
                        <option value="1">Hide</option>
                        <option value="2">Show</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="systemNameKh">System Name Khmer:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_name_kh]" id="systemNameKh" style="width: 90%;" class="validate[required]" />
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="systemName">System Name:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_name]" id="systemName" style="width: 90%;" class="validate[required]" />
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="systemStart">System Start:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_start]" id="systemStart" style="width: 90%;" class="validate[required] interger" />
                </td>
            </tr>
            <tr>
                <td style="width: 30%;">System Logo Big:</td>
                <td>
                    <input type="file" id="systemPhotoBig" name="photo_big" />
                </td>
            </tr>
            <tr>
                <td style="width: 30%;">System Logo Small:</td>
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
                        <option value="0">All</option>
                        <option value="1">Not For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationBR">Bill Return:</label></td>
                <td>
                    <select id="locationBR" name="data[location_br]" style="width: 200px; height: 30px;">
                        <option value="0">All</option>
                        <option value="1">Not For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationPOS">POS:</label></td>
                <td>
                    <select id="locationPOS" name="data[location_pos]" style="width: 200px; height: 30px;">
                        <option value="0">All</option>
                        <option value="1">For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationSale">Sales Invoice:</label></td>
                <td>
                    <select id="locationSale" name="data[location_sale]" style="width: 200px; height: 30px;">
                        <option value="0">All</option>
                        <option value="1">For Sale</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="locationCM">Credit Memo:</label></td>
                <td>
                    <select id="locationCM" name="data[location_cm]" style="width: 200px; height: 30px;">
                        <option value="0">All</option>
                        <option value="1">Not For Sale</option>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Server Configuration</legend>
        <table cellpadding="5" cellspacing="0" style="width: 500px;">
            <tr>
                <td style="width: 30%;"><label for="systemLinkURL">Link URL:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_link_url]" value="http://localhost/" id="systemLinkURL" style="width: 90%;" class="validate[required]" />
                </td>
            </tr>
            <tr>
                <td style="width: 30%;"><label for="systemLinkURLSSL">LINK URL SSL:</label> <span style="color: red;">*</span></td>
                <td>
                    <input type="text" name="data[system_link_url_ssl]" value="--no-check-certificate" id="systemLinkURLSSL" style="width: 90%;" class="validate[required]" />
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