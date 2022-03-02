<?php $tblName = "tbl" . rand(); ?>
<script type="text/javascript">
    function loadTablePaymentCustomer(){
        if($("#ShiftCollectCompanyId").val()!="" && $("#ShiftCollectBranchId").val()!="" && $("#ShiftCollectUserId").val() != "" && $("#ShiftCollectEmployeeId").val() != ""){
            var companyId = $("#ShiftCollectCompanyId").val();
            var branchId  = $("#ShiftCollectBranchId").val();
            var user_id   = $("#ShiftCollectUserId").val();
            $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/ajax/"+companyId+"/"+ branchId+"/"+ user_id,
                data: "",
                beforeSend: function(){   
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    $("#btnLoadTableCollectShift").val("<?php echo ACTION_LOADING; ?>").attr("disabled", true);
                },
                success: function(msg){
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    $("#btnLoadTableCollectShift").val("<?php echo TABLE_SHOW; ?>").attr("disabled", false);
                    $("#tblShiftCollect").html(msg);
                   
                }
            }); 
        }else{
            // alert message
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_SELECT_FIELD_REQURIED; ?></p>');
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
    }
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Hide Branch
        $("#ShiftCollectBranchId").filterOptions('com', '0', '');
        // chosen init
        $(".chzn-select").chosen();

        $("#ShiftCollectForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        
        $("#ShiftCollectDate").datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            beforeShow: function(){
                setTimeout(function(){
                    $("#ui-datepicker-div").css("z-index", 1000);
                }, 10);
            }
        }).unbind("blur");
        
        $("#ShiftCollectCompanyId").change(function(){
            filterChartAccReceivePayment();
            $("#ShiftCollectBranchId").filterOptions('com', $(this).val(), '');
            $("#ShiftCollectBranchId").change();
        });
        
        $("#ShiftCollectBranchId").change(function(){
            var mCode = $(this).find("option:selected").attr("mcode");
            $("#ShiftCollectReference").val("<?php echo date("y"); ?>"+mCode);
        });
        
        $("#btnLoadTableCollectShift").click(function(){
            loadTablePaymentCustomer();
        });
        
        <?php
        if(count($companies) == 1){
        ?>
        var companyId = $("#ShiftCollectCompanyId").val();
        $("#ShiftCollectBranchId").filterOptions('com', companyId, '');
        $("#ShiftCollectBranchId").change();
        <?php
        }
        ?>
    });
</script>
<?php echo $this->Form->create('ShiftCollect', array ('id'=>'ShiftCollectForm', 'url'=>'/shift_collects/save/')); ?>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div>
        <b style="font-size: 18px;"><?php echo MENU_SHIFT_COLLECT_SHIFT; ?></b>
    </div>
    <div style="width: 100%">
        <div class="inputContainer" style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td style="<?php if(count($companies) == 1){ ?>display: none;<?php } ?>"><label for="ShiftCollectCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($companies) == 1){ ?>display: none;<?php } ?>">
                        <select name="data[ShiftCollect][company_id]" id="ShiftCollectCompanyId" class="validate[required]" style="width: 150px;">
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
                    <td style="<?php if(count($branches) == 1){ ?>display: none;<?php } ?>"><label for="ShiftCollectBranchId"><?php echo MENU_BRANCH; ?> <span class="red">*</span> :</label></td>
                    <td style="<?php if(count($branches) == 1){ ?>display: none;<?php } ?>">
                        <select name="data[ShiftCollect][branch_id]" id="ShiftCollectBranchId" class="validate[required]" style="width: 150px;">
                            <?php
                            if(count($branches) != 1){
                            ?>
                            <option value="" com="" mcode=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            }
                            foreach($branches AS $branch){
                            ?>
                            <option value="<?php echo $branch['Branch']['id']; ?>" com="<?php echo $branch['Branch']['company_id']; ?>" mcode="<?php echo $branch['ModuleCodeBranch']['receive_collect_shift']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 7%;"><label for="ShiftCollectEmployeeId"><?php echo TABLE_CHECK_BY; ?> <span class="red">*</span> :</label></td>
                    <td style="width: 15%;">
                        <?php echo $this->Form->select('EmployeeId', $employees, null, array('escape' => false, 'name' => 'data[ShiftCollect][employee_id]', 'class' => 'chzn-select', 'empty' => INPUT_SELECT)); ?>
                    </td>                    
                    <td style="width: 7%;"><label for="ShiftCollectUserId"><?php echo TABLE_SHIFT_USER_SALES; ?> <span class="red">*</span> :</label></td>
                    <td style="width: 15%;">
                        <select id="ShiftCollectUserId" name="data[ShiftCollect][user_id]" class="chzn-select validate[required]" style="width: 200px;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            $queryUser =  mysql_query("SELECT id, CONCAT_WS(' ',first_name,last_name) AS name FROM users WHERE is_active = 1 AND id IN (SELECT user_id FROM user_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id'].")) ORDER BY first_name");
                            while($dataUser=mysql_fetch_array($queryUser)){
                            ?>
                            <option value="<?php echo $dataUser['id']; ?>"><?php echo $dataUser['name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="button" id="btnLoadTableCollectShift" value="<?php echo TABLE_SHOW; ?>" style="height: 30px; width: 70px;" /></td>
                </tr>
            </table>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<div id="tblShiftCollect">
    
</div>
<br />
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div style="float: left;padding-right: 10px;">
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ShiftCollectDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('date', array('class' => 'validate[required]', 'style' => 'width: 150px;', 'readonly' => 'readonly')); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ShiftCollectReference"><?php echo TABLE_REFERENCE; ?> <span class="red">*</span> :</label>
            <?php echo $this->Form->text('reference', array('class' => 'validate[required]', 'style' => 'width: 100px;', 'readonly' => TRUE)); ?>
        </div>
        <div class="inputContainer" style="padding-right: 10px;">
            <label for="ShiftCollectNote"><?php echo TABLE_NOTE; ?>:</label>
            <?php echo $this->Form->text('note', array('style' => 'width: 300px;')); ?>
        </div>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSaveShiftCollect">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSaveShiftCollect"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>