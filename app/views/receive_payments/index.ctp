<?php 
$queryClosingDate = mysql_query("SELECT DATE_FORMAT(date,'%d/%m/%Y') FROM account_closing_dates ORDER BY id DESC LIMIT 1");
$dataClosingDate  = mysql_fetch_array($queryClosingDate);
$tblName = "tbl" . rand(); ?>
<script type="text/javascript">
    function loadTablePaymentCustomer(){
        if($("#CustomerPaymentCompanyId").val()!="" && $("#CustomerPaymentBranchId").val()!="" && $("#CustomerPaymentCustomerId").val()!=""){
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+$("#CustomerPaymentCompanyId").val()+"/"+$("#CustomerPaymentBranchId").val()+"/"+$("#CustomerPaymentCustomerId").val(),
                data: "",
                beforeSend: function(){   
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    $("#btnLoadTablePaymentCustomer").val("<?php echo ACTION_LOADING; ?>").attr("disabled", true);
                },
                success: function(msg){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#btnLoadTablePaymentCustomer").val("<?php echo GENERAL_SEARCH; ?>").attr("disabled", false);
                    $("#tblCustomerPayment").html(msg);
                }
            }); 
        }else{
            // alert message
            alertSelectRequireField();
        }
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#CustomerPaymentBranchId").filterOptions('com', '0', '');
        $("#CustomerPaymentForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#CustomerPaymentDate").val("<?php echo date("d/m/Y"); ?>");
        $("#CustomerPaymentDate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            minDate: '<?php echo $dataClosingDate[0]; ?>',
            maxDate: "<?php echo date("d/m/Y"); ?>",
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        $("#CustomerPaymentCompanyId").unbind("change").change(function(){
            $("#CustomerPaymentBranchId").filterOptions('com', $(this).val(), '');
            $("#CustomerPaymentBranchId").change();
        });
        $("#CustomerPaymentBranchId").unbind("change").change(function(){
            var mCode = $(this).find("option:selected").attr("mcode");
            $("#CustomerPaymentReference").val("<?php echo date("y"); ?>"+mCode);
        });
        $("#btnLoadTablePaymentCustomer").unbind("click").click(function(){
            loadTablePaymentCustomer();
        });
        // Search Customer
        $("#CustomerPaymentCustomerName").autocomplete("<?php echo $this->base ."/reports/searchCustomer"; ?>", {
            width: 410,
            max: 10,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            }
        }).result(function(event, value){
            $("#CustomerPaymentCustomerId").val(value.toString().split(".*")[0]);
            $("#CustomerPaymentCustomerName").val(value.toString().split(".*")[1]+" - "+value.toString().split(".*")[2]).attr("readonly", true);
            $("#clearCustomerCustomerPayment").show();
        });
        
        $("#clearCustomerCustomerPayment").unbind("click").click(function(){
            $("#CustomerPaymentCustomerId").val("");
            $("#CustomerPaymentCustomerName").val("");
            $("#CustomerPaymentCustomerName").removeAttr("readonly");
            $("#clearCustomerCustomerPayment").hide();
            $("#tblCustomerPayment").html('');
        });
        
        <?php
        if(count($companies) == 1){
        ?>
        var companyId = $("#CustomerPaymentCompanyId").val();
        $("#CustomerPaymentBranchId").filterOptions('com', companyId, '');
        $("#CustomerPaymentBranchId").change();
        <?php
        }
        ?>
    });
</script>
<?php echo $this->Form->create('CustomerPayment', array ('id'=>'CustomerPaymentForm', 'url'=>'/receive_payments/save/')); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div>
        <b style="font-size: 18px;"><?php echo MENU_RECEIVE_PAYMENTS; ?></b>
    </div>
    <div style="width: 100%">
        <div class="inputContainer" style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 7%; <?php if(count($companies) == 1){ ?>display: none;<?php } ?>"><label for="CustomerPaymentCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                        <select name="company_id" id="CustomerPaymentCompanyId" class="validate[required]" style="width: 200px;">
                            <?php
                            if(count($companies) != 1){
                            ?>
                            <option value=""><?php echo INPUT_SELECT; ?></option>
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
                    <td style="width: 7%; <?php if(count($branches) == 1){ ?>display: none;<?php } ?>"><label for="CustomerPaymentBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                        <select name="branch_id" id="CustomerPaymentBranchId" class="validate[required]" style="width: 200px;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['receive_pay_code']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 70px;"><label for="CustomerPaymentCustomerId"><?php echo TABLE_CUSTOMER; ?> <span class="red">*</span> :</label></td>
                    <td style="width: 350px;">
                        <input type="hidden" name="customer_id" id="CustomerPaymentCustomerId" />
                        <input type="text" style="width: 300px;" id="CustomerPaymentCustomerName" placeholder="<?php echo TABLE_ALL; ?>" />
                        <img alt="" src="<?php echo $this->webroot; ?>img/button/pos/remove-icon-png-25.png" style="cursor: pointer; display: none; height: 20px;" onmouseover="Tip('Clear Customer')" id="clearCustomerCustomerPayment" />
                    </td>
                    <td><input type="button" id="btnLoadTablePaymentCustomer" value="<?php echo GENERAL_SEARCH; ?>" style="height: 30px; width: 70px; cursor: pointer;" /></td>
                </tr>
            </table>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="tblCustomerPayment"></div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;padding-right: 10px;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="CustomerPaymentDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => 'readonly')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="CustomerPaymentReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('reference', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => TRUE)); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="CustomerPaymentNote"><?php echo TABLE_MEMO; ?>:</label>
            <?php echo $this->Form->text('note', array('style' => 'width: 450px;')); ?>
        </div>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveReceivePayment" disabled="disabled">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveCustomerPayment"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>