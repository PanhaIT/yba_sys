<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit');
mysql_query("TRUNCATE TABLE `promotional_pgroup_tmps`;");
?>
<script type="text/javascript">   
    var pGroupRequestProduct = null;
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".chzn-select").chosen({width:380});
        $(".float").autoNumeric({mDec: 2, aSep: ','});
        // Form Validate
        $("#PromotionalPointAddForm").validationEngine('attach');

        $("#PromotionalPointAddForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePromotionalPoint").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                // if($("#PromotionalPointPgroupId").val()=='' || $("#PromotionalPointPgroupId_chosen").val()==''){
                //     alertSelectProupGroup();
                //     return false;
                // }
                listbox_selectall('pgroupProductId', true);
                $("#PromotionalPointDate, #PromotionalPointStart, #PromotionalPointEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".targetPoint, .floatAmt").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error == 0){
                    $(".btnBackPromotionalPoint").dblclick();
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_HAS_BEEN_SAVED ?></p>');
                    $("#dialog").dialog({
                        title: '<?php echo DIALOG_INFORMATION; ?>',
                        resizable: false,
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_CLOSE; ?>': function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }else if(result.error == 1){
                    $(".btnBackPromotionalPoint").dblclick();
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED ?></p>');
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
                }else if(result.error == 2){
                    // Alert message
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM ?></p>');
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
        });
    
        $("#PromotionalPointDate").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        var dates = $("#PromotionalPointStart, #PromotionalPointEnd").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var endDate       = $("#PromotionalPointEnd");
                var imgLoad       = $(".loadStartEndDate");
                var available     = $(".availableStartEndDate");
                var noneAvailable = $(".noneAvailableStartEndDate");
                var checkDate     = true;

                var start = $("#PromotionalPointStart").val();
                var end   = $("#PromotionalPointEnd").val();

                if(start != '' && end != ''){
                    if(checkDate == true && endDate.val() != ''){
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkDuplicateStartEndDate/0",
                            data: "start="+start+"&end="+end,
                            beforeSend: function(){
                                imgLoad.show();
                                noneAvailable.hide();
                                available.hide();
                                $(".btnSavePromotionalPoint").hide();
                            },
                            success: function(result){
                                imgLoad.hide(); 
                                if(result == 'available'){
                                    noneAvailable.hide();
                                    available.show();
                                    endDate.select().focus();
                                }else if(result == 'not available'){
                                    noneAvailable.show();
                                    available.hide();
                                }else if(result == 'Error Date'){
                                    endDate.val('');
                                }
                                $(".btnSavePromotionalPoint").show();
                            }
                        });
                    }else{
                        noneAvailable.hide();
                        available.show();
                        endDate.val('');
                    }
                }else{
                    endDate.val("");
                }
                
                var option = this.id == "PromotionalPointStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });

        $("#PromotionalPointEnd,#PromotionalPointEnd").blur(function(){
            var endDate       = $(this);
            var imgLoad       = $(this).closest("tr").find(".loadStartEndDate");
            var available     = $(this).closest("tr").find(".availableStartEndDate");
            var noneAvailable = $(this).closest("tr").find(".noneAvailableStartEndDate");
            var checkDate     = true;

            var start = $("#PromotionalPointStart").val();
            var end   = $("#PromotionalPointEnd").val();

            if(start != '' && end != ''){
                if(checkDate == true && endDate.val() != ''){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/checkDuplicateStartEndDate/0",
                        data: "start="+start+"&end="+end,
                        beforeSend: function(){
                            imgLoad.show();
                            noneAvailable.hide();
                            available.hide();
                            $(".btnSavePromotionalPoint").hide();
                        },
                        success: function(result){
                            imgLoad.hide(); 
                            if(result == 'available'){
                                noneAvailable.hide();
                                available.show();
                                endDate.select().focus();
                            }else if(result == 'not available'){
                                noneAvailable.show();
                                available.hide();
                            }else if(result == 'Error Date'){
                                endDate.val('');
                            }
                            $(".btnSavePromotionalPoint").show();
                        }
                    });
                }else{
                    noneAvailable.hide();
                    available.show();
                    endDate.val('');
                }
            }else{
                endDate.val("");
            }
        });

        $("#PromotionalPointPgroupId").change(function(){
            var pgroupId  = $(this).find("option:selected").val();
            var pgroupPro = $("#pgroupProductId").find("option").attr('value');
            if(pgroupPro != undefined || pgroupPro != null){
                $.ajax({
                    dataType:"JSON",
                    type: "POST",
                    url: "<?php echo $this->base; ?>/promotional_points/checkPgroup/"+pgroupId+"/1",
                    data: "",
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                        if(result.check==1){
                            var oldPgroupId = result.old_pgroup_id
                            $("#dialog").html('<p style="color:red; font-size:14px;">Product member will be reset when change group.<br>Are you want to change?</p>');
                            $("#dialog").dialog({
                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                resizable: false,
                                modal: true,
                                closeOnEscape: false,
                                width: 'auto',
                                height: '190',
                                position:'center',
                                open: function(event, ui){
                                    $(".ui-dialog-buttonpane").show();
                                    $(".ui-dialog-titlebar-close").hide();
                                },
                                buttons: {
                                    '<?php echo ACTION_CANCEL; ?>': function() {
                                        $(this).dialog("close");
                                        $(".ui-dialog-titlebar-close").show();
                                        $("#PromotionalPointPgroupId").find("option").removeAttr("selected");
                                        $("#PromotionalPointPgroupId").find("option[value='"+oldPgroupId+"']").attr("selected",true);
                                        $("#pgroupIdHidden").val(oldPgroupId);
                                        $('#PromotionalPointPgroupId').trigger("chosen:updated");
                                    },
                                    '<?php echo ACTION_OK; ?>': function() {
                                        $(this).dialog("close");
                                        $(".ui-dialog-titlebar-close").show();
                                        $("#PromotionalPointPgroupId").find("option").removeAttr("selected");
                                        $("#PromotionalPointPgroupId").find("option[value='"+pgroupId+"']").attr("selected",true);
                                        $("#pgroupIdHidden").val(pgroupId);
                                        $('#pgroupProductId').html("");
                                        $('#PromotionalPointPgroupId').trigger("chosen:updated");
                                        $.ajax({
                                            dataType:"JSON",
                                            type: "POST",
                                            url: "<?php echo $this->base; ?>/promotional_points/checkPgroup/"+pgroupId+"/0",
                                            data: "",
                                            beforeSend: function(){
                                                $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                            },
                                            success: function(result){
                                                $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }else{
                $("#pgroupIdHidden").val(pgroupId);
                $.ajax({
                    dataType:"JSON",
                    type: "POST",
                    url: "<?php echo $this->base; ?>/promotional_points/checkPgroup/"+pgroupId+"/0",
                    data: "",
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(result){
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    }
                });
            }
        });

        $(".btnBackPromotionalPoint").dblclick(function(event){
            event.preventDefault();
            $.ajax({
                dataType:"JSON",
                type: "POST",
                url: "<?php echo $this->base; ?>/promotional_points/checkPgroup/0/2",
                data: "",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(result){
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                }
            });
            $('#PromotionalPointAddForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTablePromotionalPoint.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });

        // apply product promotional
        $(".searchPgroupProduct").click(function(){
            var pgroupId = $("#PromotionalPointPgroupId").val();
            var branchId = $("#PromotionalPointBranchId").val();
            if(pGroupRequestProduct != null){
                pGroupRequestProduct.abort();
            }
            pGroupRequestProduct = $.ajax({
                type: "POST",
                url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/product/"+(branchId!=""?branchId:"all")+"/"+pgroupId,
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    pGroupRequestProduct = null;
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg).dialog({
                        title: '<?php echo MENU_PRODUCT_MANAGEMENT_INFO; ?>',
                        resizable: false,
                        modal: true,
                        width: 850,
                        height: 500,
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_OK; ?>': function() {
                                $("input[name='chkProduct']:checked").each(function(){
                                    addSelect($(this).val());
                                });
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });

        $("#PgroupProduct").autocomplete("<?php echo $this->base . "/promotional_points/searchProduct/"; ?>", {
            width: 410,
            max: 10,
            highlight: false,
            scroll: true,
            scrollHeight: 500,
            formatItem: function(data, i, n, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            },
            formatResult: function(data, value) {
                return value.split(".*")[1] + " - " + value.split(".*")[2];
            }
        }).result(function(event, value){
            addSelect(value.toString());
            $(this).val('');
        });

        var addSelect =  function (value){
            var product_id    = value.split(".*")[0];
            var product_code  = value.split(".*")[1];
            var product_value = value.split(".*")[2];
            var companyId     = value.split(".*")[3];
            if(!checkValueIfExist(product_id)){
                $("#pgroupProductId").append('<option com="'+ companyId +'" value="'+product_id+'" rel="'+product_value+'" >'+product_code+" - "+product_value+'</option>');
            }
        };

        var checkValueIfExist = function(value){
            var result = false;
            $('#pgroupProductId').find("option").each(function(){
                if(value == $(this).val()) {
                    result = true;
                }
            });
            return result;
        };

        $("#pgroupProductId").dblclick(function() {
            var id = $(this).attr('value');
            var name = $(this).find("option:selected").attr('rel');
            $("#tabs").tabs("add", "<?php echo $this->base; ?>/products/view/" + id, name);
        });

        $("#PgroupMinus").click(function(){
            $('#pgroupProductId option:selected').remove();
            return false;
        });

        $(".btnBackPgroup").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTablePgroup.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // User Apply Change
        $("#PgroupUserApply").change(function(){
            var val = $(this).val();
            if(val == 1){
                $("#formUserApplyPgroup").show();
            } else {
                $("#formUserApplyPgroup").hide();
                $("#userPgroupSelected").find("option").attr("selected", true);
                listbox_moveacross('userPgroupSelected', 'userPgroup');
            }
        });
    });

    function resetProduct(oldPgrouopId){
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_GROUP; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).find("option[value='"+oldPgrouopId+"']").attr("selected",true);
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                    alert(oldPgrouopId);
                },
                '<?php echo ACTION_OK; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                    $('#pgroupProductId').html("");
                }
            }
        });
    }

    function alertSelectProupGroup(){
        $(".btnSavePromotionalPoint").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;"><?php echo MESSAGE_CONFIRM_SELECT_GROUP; ?></p>');
        $("#dialog").dialog({
            title: '<?php echo DIALOG_INFORMATION; ?>',
            resizable: false,
            modal: true,
            closeOnEscape: false,
            width: 'auto',
            height: 'auto',
            position:'center',
            open: function(event, ui){
                $(".ui-dialog-buttonpane").show();
                $(".ui-dialog-titlebar-close").hide();
            },
            buttons: {
                '<?php echo ACTION_CLOSE; ?>': function() {
                    $(this).dialog("close");
                    $(".ui-dialog-titlebar-close").show();
                }
            }
        });
    }
</script>
<?php echo $this->Form->create('PromotionalPoint'); ?>
<div id="PromotionalPointTop">
    <fieldset>
        <legend><?php __(MENU_PROMOTINO_PACK_INFO); ?></legend>
        <table style="width: 100%;">
            <tr>
                <td style="width: 13%;"><label for="PromotionalPointDescription"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
                <td style="width: 36%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('description', array('class'=>'validate[required]', 'style' => 'width: 80%;', 'value' => '')); ?>
                    </div>
                </td>
                <td style="width: 10%;"><label for="PromotionalPointBranchId"><?php echo TABLE_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td style="width: 40%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('branch_id', array('empty' => TABLE_ALL, 'style' => 'width: 200px;', 'label' => false, 'div' => false)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="PromotionalPointStart"><?php echo TABLE_START_DATE; ?> <span class="red">*</span> :</label></td>
                <td style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('start', array('class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE, 'value' => '')); ?>
                    </div>
                </td>
                <td style="vertical-align: top;"><label for="PromotionalPointDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                <td style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('date', array('value' => date("Y-m-d"), 'class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="PromotionalPointEnd"><?php echo TABLE_END_DATE; ?> <span class="red">*</span> :</label></td>
                <td style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('end', array('class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE, 'value' => '')); ?>
                        <img src="<?php echo $this->webroot; ?>img/layout/spinner.gif" class="loadStartEndDate" style="display:none;" />
                        <img src="<?php echo $this->webroot; ?>img/button/delete.png" onmouseover="Tip('<?php echo MESSAGE_START_END_DATE_EXIST_IN_SYSTEM; ?>')" class="availableStartEndDate" style="display:none;" /> 
                        <img src="<?php echo $this->webroot; ?>img/button/tick.png" class="noneAvailableStartEndDate" style="display:none;" />
                    </div>
                    <div class="inputContainer availableStartEndDate" style="width: 100%; color: red; display: none;">
                        <?php echo MESSAGE_START_END_DATE_EXIST_IN_SYSTEM; ?>
                    </div>
                </td>
                <td rowspan="2" style="vertical-align: top;"><label for="PromotionalPointNote"><?php echo TABLE_NOTE; ?> :</label></td>
                <td rowspan="2" style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('note', array('label' => false, 'style' => 'width: 80%; height: 70px;', 'value' => '')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><label for="PromotionalPointPgroupId"><?php echo MENU_PGROUP_MANAGEMENT; ?><span class="red"></span> :</label></td>
                <td style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <input type="hidden" id='pgroupIdHidden' value="">
                        <select name="data[PromotionalPoint][pgroup_id]" id="PromotionalPointPgroupId"​ class="chzn-select PromotionalPointPgroupId" style="width:380px;">
                            <option rel="0" value="all">All</option>
                            <?php
                            $sqlPgroup = mysql_query("SELECT * FROM pgroups WHERE is_active=1 AND id IN(SELECT pgroup_id FROM product_pgroups WHERE 1)");
                            while($rowPgroup = mysql_fetch_array($sqlPgroup)){
                            ?>
                            <option rel="0" value="<?php echo $rowPgroup['id'];?>"><?php echo $rowPgroup['name'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;" colspan="2">
                    <fieldset style="width:84%;">
                        <legend><?php __(MENU_APPY_POINT); ?></legend>
                        <table>
                            <tr>
                                <td style="width: 25%;">
                                    <?php echo $this->Form->text('total_point', array('class' => 'validate[required] float', 'style' => 'width:100px;')); ?> Point(s) = <?php echo $this->Form->text('point_in_dollar', array('class' => 'validate[required] float', 'style' => 'width:100px;')); ?> $
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="applyPromotionalProduct">
    <fieldset>
        <legend><?php __(TABLE_PRODUCT." ".GENERAL_MEMBER); ?></legend>
        <table style="width: 100%;">
            <tr>
                <th style="width: 70px;"><?php echo TABLE_PRODUCT; ?></th>
                <td>
                    <?php echo $this->Form->text('product', array('id' => 'PgroupProduct','readonly'=>true)); ?>
                    <img alt="Search" align="absmiddle" style="cursor: pointer;" class="searchPgroupProduct" onmouseover="Tip('<?php echo GENERAL_SEARCH; ?>')" src="<?php echo $this->webroot . 'img/button/search.png'; ?>" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="vertical-align: top;">
                    <select class="pgroupProducts" id="pgroupProductId" name="data[PromotionalPoint][product_id][]" multiple="multiple" style="width: 420px; height: 150px;"></select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="vertical-align: top;">
                    <div class="buttons">
                        <button type="submit" id="PgroupMinus" class="negative">
                            <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                            <span class="txtDelete"><?php echo ACTION_DELETE; ?></span>
                        </button>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div id="requestPromotionalPointFooter">
    <div class="buttons">
        <a href="#" class="positive btnBackPromotionalPoint">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSavePromotionalPoint">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSavePromotionalPoint"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>