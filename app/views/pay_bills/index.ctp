<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript">
    function loadTablePayBill(){
        if($("#PayBillCompanyId").val()!="" && $("#PayBillBranchId").val()!=""){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#PayBillCompanyId").val()+"/"+$("#PayBillBranchId").val()+"/"+$("#PayBillVendorId").val(),
                data: "",
                beforeSend: function(){   
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    $("#btnLoadTablePayBill").val("<?php echo ACTION_LOADING; ?>").attr("disabled", true);
                },
                success: function(msg){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#btnLoadTablePayBill").val("<?php echo TABLE_SHOW; ?>").attr("disabled", false);
                    $("#tblPayBill").html(msg);
                }
            }); 
        }else{
            // Alert Message
            alertSelectRequireField();
        }
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#PayBillBranchId").filterOptions('com', '0', '');
        // chosen init
        $(".chzn-select").chosen();
        $("#PayBillForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        var now = new Date();
        $("#PayBillDate").val(now.toString('dd/MM/yyyy'));
        $("#PayBillDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#PayBillCompanyId").change(function(){
            $("#PayBillBranchId").filterOptions('com', $(this).val(), '');
            $("#PayBillBranchId").change();
        });
        $("#PayBillBranchId").change(function(){
            var mCode = $(this).find("option:selected").attr("mcode");
            $("#PayBillReference").val("<?php echo date("y"); ?>"+mCode);
        });
        $("#btnLoadTablePayBill").click(function(){
            loadTablePayBill();
        });
        
        // Search Vendor
        $("#PayBillVendorName").autocomplete("<?php echo $this->base ."/reports/searchVendor"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            },
            formatResult: function(data, value) {
                return value.split(".*")[2] + " - " + value.split(".*")[1];
            }
        }).result(function(event, value){
            $("#PayBillVendorId").val(value.toString().split(".*")[0]);
            $("#PayBillVendorName").val(value.toString().split(".*")[2]+" - "+value.toString().split(".*")[1]).attr("readonly", true);
            $("#clearVendorPayBill").show();
        });
        
        $("#clearVendorPayBill").click(function(){
            $("#PayBillVendorId").val("");
            $("#PayBillVendorName").val("");
            $("#PayBillVendorName").removeAttr("readonly");
            $("#clearVendorPayBill").hide();
        });
        
        <?php
        if(count($companies) == 1){
        ?>
        var companyId = $("#PayBillCompanyId").val();
        $("#PayBillBranchId").filterOptions('com', companyId, '');
        $("#PayBillBranchId").change();
        <?php
        }
        ?>
    });
</script>
<?php echo $this->Form->create('PayBill', array ('id'=>'PayBillForm', 'url'=>'/pay_bills/save/')); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div>
        <b style="font-size: 18px;"><?php echo MENU_PAY_BILLS; ?></b>
    </div>
    <div>
        <div class="inputContainer" style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 7%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>"><label for="PayBillCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                        <select name="company_id" id="PayBillCompanyId" class="validate[required]" style="width: 200px;">
                            <?php
                            if(count($companies) != 1){
                            ?>
                            <option value="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($companies AS $company){
                            ?>
                            <option value="<?php echo $company['Company']['id']; ?>"><?php echo $company['Company']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 7%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>"><label for="PayBillBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                        <select name="branch_id" id="PayBillBranchId" class="validate[required]" style="width: 200px;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['pay_bill_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="<?php if(count($branches) == 1){ ?>width: 7%;<?php } ?>"><label for="PayBillVendorId"><?php echo TABLE_VENDOR; ?>:</label></td>
                    <td style="<?php if(count($branches) == 1){ ?>width: 15%;<?php } ?>">
                        <input type="hidden" name="vendor_id" id="PayBillVendorId" />
                        <input type="text" style="width: 300px;" id="PayBillVendorName" placeholder="<?php echo TABLE_ALL; ?>" />
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/delete.png" style="cursor: pointer; display: none;" onmouseover="Tip('Clear Vendor')" id="clearVendorPayBill" />
                    </td>
                    <td><input type="button" id="btnLoadTablePayBill" value="<?php echo TABLE_SHOW; ?>" style="height: 30px; width: 70px; cursor: pointer;" /></td>
                </tr>
            </table>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="tblPayBill"></div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;padding-right: 10px;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="PayBillDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => 'readonly')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="PayBillReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('reference', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => TRUE)); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="PayBillNote"><?php echo TABLE_NOTE; ?>:</label>
            <?php echo $this->Form->text('note', array('style' => 'width: 450px;')); ?>
        </div>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSavePayBill" disabled="disabled">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSavePayBill"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>