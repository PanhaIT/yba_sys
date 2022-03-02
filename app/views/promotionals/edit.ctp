<?php 
include("includes/function.php");
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
    var indexRowPromotional   = 0;
    var cloneRowPromotional   = $("#detailPromotional");
    
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        // Remove Clone Row List
        $("#detailPromotional").remove();
        
        var waitForFinalEventRPromotional = (function () {
          var timers = {};
          return function (callback, ms, uniqueId) {
            if (!uniqueId) {
              uniqueId = "Don't call this twice without a uniqueId";
            }
            if (timers[uniqueId]) {
              clearTimeout (timers[uniqueId]);
            }
            timers[uniqueId] = setTimeout(callback, ms);
          };
        })();

        // Click Tab Refresh Form List: Screen, Title, Scroll
        if(tabRPromotionalReg != tabRPromotionalId){
            $("a[href='"+tabRPromotionalId+"']").click(function(){
                if($("#bodyListPromotional").html() != '' && $("#bodyListPromotional").html() != null){
                    waitForFinalEventRPromotional(function(){
                        refreshScreenPromotional();
                        resizeFormTitlePromotional();
                        resizeFornScrollPromotional(1);  
                    }, 300, "Finish");
                }
            });
            tabRPromotionalReg = tabRPromotionalId;
        }

        waitForFinalEventRPromotional(function(){
              refreshScreenPromotional();
              resizeFormTitlePromotional();
              resizeFornScrollPromotional(1);  
            }, 300, "Finish");
            
        $(window).resize(function(){
            if(tabRPromotionalReg == $(".ui-tabs-selected a").attr("href")){
                waitForFinalEventRPromotional(function(){
                    refreshScreenPromotional();
                    resizeFormTitlePromotional();
                    resizeFornScrollPromotional(1);  
                  }, 500, "Finish");
            }
        });
        
        // Form Validate
        $("#PromotionalEditForm").validationEngine('detach');
        $("#PromotionalEditForm").validationEngine('attach');
        
        $(".btnSavePromotional").click(function(){
            if($("#promotionType").val()==1){
                if(checkBfSavePromotional() == true){
                    return true;
                }else{
                    return false;
                }
            }
        });
        
        $("#PromotionalEditForm").ajaxForm({
            dataType: "json",
            beforeSubmit: function(arr, $form, options) {
                $(".txtSavePromotional").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            beforeSerialize: function($form, options) {
                if($("#promotionType").val()==2){
                    if($(".productPromoId").val()==' '){
                        alertSelectProductPromotional();
                        return false;
                    }
                    if($(".totalPrice").val()==0 || $(".totalQtyPromo").val()==0){
                        alertSelectTotalAmountAndQtyPromotion();
                        return false;
                    }
                }
             
                if($("#promotionType").val()==3){
                    if($(".discountPercent").val()==0 || $(".totalPrice").val()==0){
                        alertSelectPriceAndDiscountPromotion();
                        return false;
                    }
                }
                $("#PromotionalDate, #PromotionalStart, #PromotionalEnd").datepicker("option", "dateFormat", "yy-mm-dd");
                $(".targetPoint").each(function(){
                    $(this).val($(this).val().replace(/,/g,""));
                });
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result.error == 0){
                    $(".btnBackPromotional").dblclick();
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
                    $(".btnBackPromotional").dblclick();
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

        $(".discountPercentPromo").blur(function(){
            if($("#promotionType").val()==2 || $("#promotionType").val()==3){
                $(".discountPercent,.discountPercentPromo").val($(this).val());
            }
        });

        $(".pricePromo").blur(function(){
            if($("#promotionType").val()==2 || $("#promotionType").val()==3){
                $(".totalPrice,.pricePromo").val($(this).val());
            }
        });

        $(".qtyPromo").blur(function(){
            if($("#promotionType").val()==2 || $("#promotionType").val()==3){
                $(".totalQtyPromo,.qtyPromo").val($(this).val());
            }
        });

        var promoType = '<?php echo $this->data['Promotional']['promotion_type'];?>';
        if(promoType==1){
            $(".firstPromotion").show();
            $(".secondPromotion,.thirdPromotion,.fouthPromotion").hide();
        }else if(promoType==2){
            $(".secondPromotion").show();
            $(".firstPromotion,.thirdPromotion,.fouthPromotion").hide();
        }else if(promoType==3){
            $(".thirdPromotion").show();
            $(".firstPromotion,.secondPromotion,.fouthPromotion").hide();
        }else if(promoType==4){
            $(".fouthPromotion").show();
            $(".firstPromotion,.secondPromotion,.thirdPromotion").hide();
        }

        // $(".secondPromotion,.thirdPromotion,.fouthPromotion").hide();
        $("#promotionType").change(function(){
            var promoType = $(this).val();
            if(promoType==1){
                $(".firstPromotion").show();
                $(".secondPromotion,.thirdPromotion,.fouthPromotion").hide();
            }else if(promoType==2){
                $(".secondPromotion").show();
                $(".firstPromotion,.thirdPromotion,.fouthPromotion").hide();
            }else if(promoType==3){
                $(".thirdPromotion").show();
                $(".firstPromotion,.secondPromotion,.fouthPromotion").hide();
            }else if(promoType==4){
                $(".fouthPromotion").show();
                $(".firstPromotion,.secondPromotion,.thirdPromotion").hide();
            }
            $("#btnBackPromotional").show();
        });
        
        $("#PromotionalDate").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        }).unbind("blur");
        
        var dates = $("#PromotionalStart, #PromotionalEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "PromotionalStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
        
        $(".btnBackPromotional").dblclick(function(event){
            event.preventDefault();
            $('#PromotionalEditForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTablePromotional.fnDraw(false);
            var rightPanel = $(this).parent().parent().parent().parent();
            var leftPanel  = rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        // Search Customer
        $("#PromotionalCustomer").autocomplete("<?php echo $this->base . "/reports/searchCustomer"; ?>", {
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
            var vendorId = value.toString().split(".*")[0];
            $("#PromotionalCustomerId").val(vendorId);
            $("#PromotionalCustomerDel").show();
            $(this).attr("readonly", true);
        });
        
        $("#PromotionalCustomerDel").click(function(){
            $("#PromotionalCustomerId").val('');
            $("#PromotionalCustomer").val('').attr("readonly", false);
            $(this).hide();
        });
        
        // Event Key
        checkEventReqPromotional();
    });
    
    function alertSelectProductPromotional(){
        $(".btnSavePromotional").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">Please select product promotional.</p>');
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

    function alertSelectTotalAmountAndQtyPromotion(){
        $(".btnSavePromotional").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">(Total amount/qty promotion>0) are required.</p>');
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

    function alertSelectPriceAndDiscountPromotion(){
        $(".btnSavePromotional").removeAttr('disabled');
        $("#dialog").html('<p style="color:red; font-size:14px;">(Total amount/discount percernt>0) are required.</p>');
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

    function resizeFormTitlePromotional(){
        var screen = 16;
        var widthList = $("#bodyListPromotional").width();
        $("#tblPromotionalHeader").css('width',widthList);
        var widthTitle = widthList - screen;
        $("#tblPromotionalHeader").css('padding','0px');
        $("#tblPromotionalHeader").css('margin-top','5px');
        $("#tblPromotionalHeader").css('width',widthTitle);
    }
    
    function resizeFornScrollPromotional(action){
        var windowHeight = $(window).height();
        var header = $("#tblPromotionalHeader").height();
        var footer = $("#requestPromotionalFooter").height();
        var title = $("#PromotionalTop").height();
        var screen = 240;
        if(action == 2){
            screen = 216;
        }
        var getHeight = windowHeight - (header + footer + screen + title);
        if(getHeight < 30){
           getHeight = 65; 
        }
        $("#bodyListPromotional").css('height',getHeight);
        $("#bodyListPromotional").css('padding','0px');
        $("#bodyListPromotional").css('width','100%');
        $("#bodyListPromotional").css('overflow-x','hidden');
        $("#bodyListPromotional").css('overflow-y','scroll');
    }
    
    function refreshScreenPromotional(){
        $("#tblPromotionalHeader").removeAttr('style');
    }
    
    function checkBfSavePromotional(){
        var formName     = "#PromotionalEditForm";
        var validateBack = $(formName).validationEngine("validate");
        if(!validateBack){
            return false;
        }else{
            if(checkRecordPromotional() == false){
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Please make an order first.</p>');
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
                return false;
            } else {
                return true;
            }
        }
    }
    
    function addPromotional(){
        // Get Index Row
        indexRowPromotional = Math.floor((Math.random() * 100000) + 1);
        var tr = cloneRowPromotional.clone(true);        
        tr.removeAttr("style").removeAttr("id");          
        tr.find("td:eq(0)").html(indexRowPromotional);
        tr.find("td .productRequestId").attr("id", "productRequestId"+indexRowPromotional);
        tr.find("td .productRequest").attr("id", "productRequest"+indexRowPromotional);
        tr.find("td .qtyRequest").attr("id", "qtyRequest"+indexRowPromotional).val(0);
        tr.find("td .uomRequest").attr("id", "uomRequest"+indexRowPromotional);
        tr.find("td .productPromoId").attr("id", "productPromoId"+indexRowPromotional);
        tr.find("td .productPromo").attr("id", "productPromo"+indexRowPromotional);
        tr.find("td .qtyPromo").attr("id", "qtyPromo"+indexRowPromotional).val(0);
        tr.find("td .uomPromo").attr("id", "uomPromo"+indexRowPromotional);
        tr.find("td .discountAmountPromo").attr("id", "discountAmountPromo"+indexRowPromotional);
        tr.find("td .discountPercentPromo").attr("id", "discountPercentPromo"+indexRowPromotional);
        tr.find("td .pricePromo").attr("id", "pricePromo"+indexRowPromotional);
        $("#tblPromotional").append(tr);
        var LenTr = parseInt($(".listBodyPromotional").length) - 1;
        if(LenTr == 0){
            $("#tblPromotional").find("tr:eq("+LenTr+")").find(".btnRemovePromotional").hide();
        }
        $("#tblPromotional").find("tr:eq("+LenTr+")").find(".btnAddRowPromotional").show();
        setIndexRowPromotional();
        checkEventReqPromotional();
    }
    
    function setIndexRowPromotional(){
        var sort = 1;
        $(".listBodyPromotional").each(function(){
            $(this).find("td:eq(0)").html(sort);
            sort++;
        });
    }
    
    function eventKeyRowPromotional(){
        $(".btnAddRowPromotional, .btnRemovePromotional, .productRequestDel, .productRequest, .discountAmountPromo, .discountPercentPromo").unbind("click").unbind("keyup").unbind("keydown").unbind("keypress");
        $(".floatAmt").autoNumeric({mDec: 2, aSep: ','});
        
        $(".qtyRequest, .qtyPromo, .floatAmt").focus(function(){
            if(replaceNum($(this).val()) == 0){
                $(this).val('');
            }
        });
        
        $(".qtyRequest, .floatAmt").blur(function(){
            if($(this).val() == ''){
                $(this).val('0');
            }
        });
        
        $(".qtyPromo").blur(function(){
            $(this).closest("tr").find(".uomPromo").removeClass("validate[required]");
            $(this).closest("tr").find(".productPromo").removeClass("validate[required]");
            if($(this).val() == ''){
                $(this).val('0');
            } else if(replaceNum($(this).val()) > 0){
                $(this).closest("tr").find(".uomPromo").addClass("validate[required]");
                $(this).closest("tr").find(".productPromo").addClass("validate[required]");
            }
        });
        
        // Discount 
        $(".discountAmountPromo").keyup(function(){
            $(this).closest("tr").find(".discountPercentPromo").val(0);
        });
        
        $(".discountPercentPromo").keyup(function(){
            $(this).closest("tr").find(".discountAmountPromo").val(0);
        });
        
        $(".productRequest").unautocomplete();
        $(".productRequest").autocomplete("<?php echo $this->base . "/promotionals/searchProduct/"; ?>"+$("#PromotionalVendorId").val(), {
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
            var productId = value.toString().split(".*")[0];
            var uomId = value.toString().split(".*")[3];
            var tr = $(this).closest("tr");
            tr.find(".productRequestId").val(productId).attr('readonly', true);
            tr.find(".productRequestDel").show();
            $.ajax({
                type: "GET",
                url: "<?php echo $this->base; ?>/promotionals/getRelativeUom/"+uomId,
                data: "",
                beforeSend: function(){
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){       
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    tr.find(".qty_uom_id").html(msg);
                }
            });
        });
        
        $(".productRequestDel").click(function(){
            $(this).closest("tr").find(".productRequest").val('').attr('readonly', false);
            $(this).hide();
        });

        $(".productPromo").autocomplete("<?php echo $this->base . "/promotionals/searchProduct/"; ?>", {
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
            var productId = value.toString().split(".*")[0];
            var productUomId = value.toString().split(".*")[3];
            var tr = $(this).closest("tr");
            tr.find(".productPromoId").val(productId).attr('readonly', true);
            tr.find(".productPromoDel").show();
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/promotionals/getRelativeUom/"; ?>"+productUomId+"/all/"+productId,
                success: function(msg){
                    tr.find(".uomPromo").html(msg);
                    tr.find(".uomPromo").find("option[conversion=1]").attr("selected", true);
                }
            });
        });
        
        $(".productPromoDel").click(function(){
            $(this).closest("tr").find(".productPromo").val('').attr('readonly', false);
            $(this).closest("tr").find(".uomPromo").html('<option value=""><?php echo INPUT_SELECT; ?></option>');
            $(this).hide();
        });
        
        $(".btnAddRowPromotional").click(function(){
            $(this).hide();
            $(this).closest("tr").find(".btnRemovePromotional").show();
            addPromotional();
        });
        
        $(".btnRemovePromotional").click(function(){
            var currentTr = $(this).closest("tr");
            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Are you sure to remove this order?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_INFORMATION; ?>',
                resizable: false,
                position:'center',
                modal: true,
                width: '300',
                height: 'auto',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_OK; ?>': function() {
                        currentTr.remove();
                        var lenTr = parseInt($(".listBodyPromotional").length) - 1;
                        if(lenTr == 0){
                            $("#tblPromotional").find("tr:eq("+lenTr+")").find(".btnRemovePromotional").hide();
                        }
                        $("#tblPromotional").find("tr:eq("+lenTr+")").find(".btnAddRowPromotional").show();
                        setIndexRowPromotional();
                        $(this).dialog("close");
                    },
                    '<?php echo ACTION_CLOSE; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
    }
    
    function checkEventReqPromotional(){
        eventKeyRowPromotional();
        $(".listBodyPromotional").unbind("click");
        $(".listBodyPromotional").click(function(){
            eventKeyRowPromotional();
        });
    }
    
    function checkRecordPromotional(){
        if(($(".listBodyPromotional").find(".productRequestId").val() == undefined || $(".listBodyPromotional").find(".productRequestId").val() == '')){
            return false;
        }else{
            return true;
        }
    }
    
</script>
<?php echo $this->Form->create('Promotional'); ?>
<div id="PromotionalTop">
    <fieldset>
        <legend><?php __(MENU_PROMOTINO_PACK_INFO); ?></legend>
        <table style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label for="PromotionalDescription"><?php echo GENERAL_DESCRIPTION; ?> <span class="red">*</span> :</label></td>
                <td style="width: 40%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('description', array('class'=>'validate[required]', 'style' => 'width: 80%;')); ?>
                    </div>
                </td>
                <td style="width: 10%;"><label for="PromotionalBranchId"><?php echo TABLE_BRANCH; ?> <span class="red">*</span> :</label></td>
                <td style="width: 40%;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('branch_id', array('empty' => TABLE_ALL, 'style' => 'width: 200px;', 'label' => false, 'div' => false)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="PromotionalStart"><?php echo TABLE_START_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        $dateStart = '';
                        if($this->data['Promotional']['start'] != '' && $this->data['Promotional']['start'] != '0000-00-00'){
                            $dateStart = dateShort($this->data['Promotional']['start']);
                        }
                        echo $this->Form->text('start', array('value' => $dateStart, 'class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE)); ?>
                    </div>
                </td>
                <td><label for="PromotionalDate"><?php echo TABLE_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->text('date', array('value' => dateShort($this->data['Promotional']['date']), 'class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE)); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="PromotionalEnd"><?php echo TABLE_END_DATE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php 
                        $dateEnd = '';
                        if($this->data['Promotional']['end'] != '' && $this->data['Promotional']['end'] != '0000-00-00'){
                            $dateEnd = dateShort($this->data['Promotional']['end']);
                        }
                        echo $this->Form->text('end', array('value' => $dateEnd, 'class'=>'validate[required]', 'style' => 'width: 80%;', 'readonly' => TRUE)); ?>
                    </div>
                </td>
                <td><label for="PromotionalPromotionType"><?php echo TABLE_PROMOTION_TYPE; ?> <span class="red">*</span> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('promotion_type', array('label' => false,'id'=>'promotionType', 'data-placeholder' => INPUT_SELECT, 'class' => 'chzn-select', 'style' => 'width: 80%;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="PromotionalCgroup"><?php echo MENU_CUSTOMER_GROUP_MANAGEMENT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php
                        $cgroupId   = '';
                        $cgroupName = '';
                        $cgroupDel  = 'display: none;';
                        if(!empty($this->data['Promotional']['cgroup_id'])){
                            $cgroupId   = $this->data['Promotional']['cgroup_id'];
                            $cgroupName = $this->data['Cgroup']['name'];
                            $cgroupDel  = '';
                        }
                        ?>
                        <input type="hidden" name="data[Promotional][cgroup_id]" id="PromotionalCgroupId" value="<?php echo $cgroupId; ?>" />
                        <?php echo $this->Form->text('cgroup', array('name' => '', 'style' => 'width: 80%;', 'value' => $cgroupName)); ?>
                        <img alt="Delete" align="absmiddle" style="<?php echo $cgroupDel; ?> cursor: pointer;" id="PromotionalCgroupDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
                <td rowspan="4" style="vertical-align: top;"><label for="PromotionalNote"><?php echo TABLE_NOTE; ?> :</label></td>
                <td rowspan="4" style="vertical-align: top;">
                    <div class="inputContainer" style="width: 100%;">
                        <?php echo $this->Form->input('note', array('label' => false, 'style' => 'width: 80%; height: 50px;')); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="PromotionalCustomer"><?php echo MENU_CUSTOMER_MANAGEMENT; ?> :</label></td>
                <td>
                    <div class="inputContainer" style="width: 100%;">
                        <?php
                        $customerId   = '';
                        $customerName = '';
                        $customerDel  = 'display: none;';
                        if(!empty($this->data['Promotional']['customer_id'])){
                            $customerId   = $this->data['Promotional']['customer_id'];
                            $customerName = $this->data['Customer']['customer_code']." - ".$this->data['Customer']['name'];
                            $customerDel  = '';
                        }
                        ?>
                        <input type="hidden" name="data[Promotional][customer_id]" id="PromotionalCustomerId" value="<?php echo $customerId; ?>" />
                        <?php echo $this->Form->text('customer', array('name' => '', 'style' => 'width: 80%;', 'value' => $customerName)); ?>
                        <img alt="Delete" align="absmiddle" style="<?php echo $customerDel; ?> cursor: pointer;" id="PromotionalCustomerDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<br>
<div>
    <div class="firstPromotion">
        <table id="tblPromotionalHeader" class="table" cellspacing="0" style="padding:0px; width:100%;">
            <tr>
                <th class="first" colspan="4" style="text-align:center;"><?php echo TABLE_PRODUCT; ?></th>
                <th colspan="7" style="text-align:center;"><?php echo MENU_PRODUCT_PROMOTION_INFO; ?></th>
            </tr>
            <tr>
                <th class="first" style="width:4%;"><?php echo TABLE_NO;?></th>
                <th style="width:15%;"><?php echo TABLE_PRODUCT;?></th>
                <th style="width:5%;"><?php echo TABLE_QTY;?></th>
                <th style="width:9%;"><?php echo TABLE_UOM;?></th>
                <th style="width:15%;"><?php echo TABLE_PRODUCT;?></th>
                <th style="width:5%;"><?php echo TABLE_F_O_C;?></th>
                <th style="width:9%;"><?php echo TABLE_UOM;?></th>
                <th style="width:8%;"><?php echo TABLE_DIS_AMOUNT;?></th>
                <th style="width:8%;"><?php echo TABLE_DIS_PERCENT;?></th>
                <th style="width:7%;"><?php echo TABLE_UNIT_PRICE_SHORT;?>($)</th>
                <th style="width:5%;"></th>
            </tr>
        </table>
        <div id="bodyListPromotional">
            <table id="tblPromotional" class="table" cellspacing="0" style="padding:0px;">
                <tr id="detailPromotional" class="listBodyPromotional" style="visibility: hidden;">
                    <td class="first" style="width:4%;"></td>
                    <td style="width:15%;">
                        <div class="inputContainer" style="width:100%">
                            <input type="hidden" name="product_request_id[]" class="productRequestId" />
                            <input type="text" id="productRequest" style="width:85%; height: 25px;" class="productRequest validate[required]" />
                            <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="productRequestDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:5%;">
                        <div class="inputContainer" style="width:100%">
                            <input type="text" id="qtyRequest" name="qty_request[]" style="width:70%; height: 25px;" class="qtyRequest targetPoint validate[required]" />
                        </div>
                    </td>
                    <td style="width:9%;">
                        <div class="inputContainer" style="width:100%;">
                            <select style="width:80%; height: 25px;" name="uom_request[]" class="qty_uom_id validate[required]">
                                <option value=""><?php echo INPUT_SELECT; ?></option>
                            </select>
                        </div>
                    </td>
                    <td style="width:15%;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="hidden" name="product_promo_id[]" class="productPromoId" />
                            <input type="text" id="productPromo" style="width:85%; height: 25px;" class="productPromo" />
                            <img alt="Delete" align="absmiddle" style="display: none; cursor: pointer;" class="productPromoDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:5%;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="text" id="qtyPromo" name="qty_promo[]" style="width:70%; height: 25px;" class="qtyPromo targetPoint" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:9%;">
                        <div class="inputContainer" style="width:100%"> 
                            <select name="uom_promo[]" id="uomPromo" class="uomPromo" style="width: 90%;">
                                <option value=""><?php echo INPUT_SELECT; ?></option>
                            </select>
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:8%;">
                        <div class="inputContainer" style="width:100%">
                            <input type="text" id="discountAmountPromo" name="discount_amount[]" style="width:70%; height: 25px;" class="discountAmountPromo floatAmt" value="0" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:8%;">
                        <div class="inputContainer" style="width:100%">
                            <input type="text" id="discountPercentPromo" name="discount_percent[]" style="width:70%; height: 25px;" class="discountPercentPromo floatAmt" value="0" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:7%;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="text" id="pricePromo" name="price[]" style="width:70%; height: 25px;" class="pricePromo floatAmt" value="0" />
                        </div>
                    </td>
                    <td style="white-space:nowrap; padding:0px; text-align:center; width:5%;">
                        <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddRowPromotional" style="cursor: pointer;" onmouseover="Tip('Add More')" />
                        &nbsp;<img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemovePromotional" style="cursor: pointer;" onmouseover="Tip('Remove')" />
                    </td>
                </tr>
                <?php
                if(!empty($promotionDetails)){
                    $index = 0;
                    $rand  = rand();
                    $rowLength = count($promotionDetails);
                    foreach($promotionDetails AS $promotionDetail){
                        $productRequestName = '';
                        $productPromoName = '';
                        $uomRequest = 0;
                        $uomPromo = 0;
                        if(!empty($promotionDetail['PromotionalDetail']['product_request_id'])){
                            $sqlProRequest = mysql_query("SELECT CONCAT_WS(' - ',code,name), price_uom_id FROM products WHERE id = ".$promotionDetail['PromotionalDetail']['product_request_id']);
                            $rowProRequest = mysql_fetch_array($sqlProRequest);
                            $productRequestName = $rowProRequest[0];
                            $uomRequest = $rowProRequest[1];
                        }
                        if(!empty($promotionDetail['PromotionalDetail']['product_promo_id'])){
                            $sqlProPromo = mysql_query("SELECT CONCAT_WS(' - ',code,name), price_uom_id FROM products WHERE id = ".$promotionDetail['PromotionalDetail']['product_promo_id']);
                            $rowProPromo = mysql_fetch_array($sqlProPromo);
                            $productPromoName = $rowProPromo[0];
                            $uomPromo = $rowProPromo[1];
                        }
                ?>
                <tr class="listBodyPromotional">
                    <td class="first" style="width:4%"><?php echo ++$index; ?></td>
                    <td style="width:15%">
                        <div class="inputContainer" style="width:100%">
                            <input type="hidden" name="product_request_id[]" class="productRequestId" value="<?php echo $promotionDetail['PromotionalDetail']['product_request_id']; ?>" />
                            <input type="text" id="productRequest<?php echo $rand; ?>" style="width:85%; height: 25px;" value="<?php echo $productRequestName; ?>" class="productRequest validate[required]" <?php if($productRequestName!=''){ ?>readonly="readonly"<?php } ?>/>
                            <img alt="Delete" align="absmiddle" style="cursor: pointer;" class="productRequestDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:5%">
                        <div class="inputContainer" style="width:100%">
                            <input type="text" id="qtyRequest<?php echo $rand; ?>" name="qty_request[]" value="<?php echo number_format($promotionDetail['PromotionalDetail']['qty_request'], 0); ?>" style="width:70%; height: 25px;" class="qtyRequest targetPoint validate[required]" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:9%">
                        <div class="inputContainer" style="width:100%"> 
                            <select name="uom_request[]" id="uomRequest<?php echo $rand; ?>" class="qty_uom_id validate[required]" style="width: 90%;">
                                <?php
                                if($uomRequest > 0){
                                    $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomRequest."
                                                    UNION
                                                    SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomRequest." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomRequest.")
                                                    ORDER BY conversion ASC");
                                    $i = 1;
                                    $length = mysql_num_rows($query);
                                    while($data=mysql_fetch_array($query)){
                                        $selected = "";
                                        if($data['id'] == $promotionDetail['PromotionalDetail']['uom_request']){
                                            $selected = ' selected="selected" ';
                                        }
                                        if($length == $i){
                                ?>
                                <option <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $uomRequest){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                                <?php }
                                    $i++;
                                    }
                                }  else {
                                ?>
                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </td>
                    <td style="width:15%">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="hidden" name="product_promo_id[]" class="productPromoId" value="<?php echo $promotionDetail['PromotionalDetail']['product_promo_id']; ?>" />
                            <input type="text" id="productPromo<?php echo $rand; ?>" style="width:85%; height: 25px;" value="<?php echo $productPromoName; ?>" class="productPromo" <?php if($productPromoName!=''){ ?>readonly="readonly"<?php } ?>/>
                            <img alt="Delete" align="absmiddle" style="cursor: pointer; <?php if(empty($promotionDetail['PromotionalDetail']['product_promo_id'])){ ?>display: none;<?php } ?>" class="productPromoDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:5%">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="text" id="qtyPromo<?php echo $rand; ?>" name="qty_promo[]" value="<?php echo number_format($promotionDetail['PromotionalDetail']['qty_promo'], 0); ?>" style="width:70%; height: 25px;" class="qtyPromo targetPoint" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:9%">
                        <div class="inputContainer" style="width:100%"> 
                            <select name="uom_promo[]" id="uomPromo<?php echo $rand; ?>" class="uomPromo" style="width: 90%;">
                                <?php
                                if($uomPromo > 0){
                                    $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomPromo."
                                                        UNION
                                                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomPromo." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomPromo.")
                                                        ORDER BY conversion ASC");
                                    $i = 1;
                                    $length = mysql_num_rows($query);
                                    while($data=mysql_fetch_array($query)){
                                        $selected = "";
                                        if($data['id'] == $promotionDetail['PromotionalDetail']['uom_promo']){
                                            $selected = ' selected="selected" ';
                                        }
                                        if($length == $i){
                                ?>
                                <option <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $uomPromo){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                                <?php }
                                    $i++;
                                    } 
                                } else {
                                ?>
                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:8%">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="text" id="discountAmountPromo<?php echo $index; ?>" name="discount_amount[]" value="<?php echo number_format($promotionDetail['PromotionalDetail']['discount_amount'], 2); ?>" style="width:70%; height: 25px;" class="discountAmountPromo floatAmt" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:8%">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="text" id="discountPercentPromo<?php echo $index; ?>" name="discount_percent[]" value="<?php echo number_format($promotionDetail['PromotionalDetail']['discount_percent'], 2); ?>" style="width:70%; height: 25px;" class="discountPercentPromo floatAmt" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:7%">
                        <div class="inputContainer" style="width:100%">
                            <input type="text" id="pricePromo" name="price[]" value="<?php echo number_format($promotionDetail['PromotionalDetail']['unit_price'], 3); ?>" style="width:70%; height: 25px;" class="pricePromo floatAmt" />
                        </div>
                    </td>
                    <td style="white-space:nowrap; padding:0px; text-align:center; width:5%">
                        <img alt="" src="<?php echo $this->webroot.'img/button/plus.png'; ?>" class="btnAddRowPromotional" style="<?php if($index != $rowLength){ ?>display: none;<?php } ?>cursor: pointer;" onmouseover="Tip('Add More')" />
                        &nbsp;<img alt="" src="<?php echo $this->webroot . 'img/button/cross.png'; ?>" class="btnRemovePromotional" style="<?php if($index == $rowLength){ ?>display: none;<?php } ?>cursor: pointer;" onmouseover="Tip('Remove')" />
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
    <div class="secondPromotion">
        <table class="table" cellspacing="0" style="padding:0px; width:100%;">
            <tr>
                <th class="first" style="width:12%;"><?php echo TABLE_TOTAL_AMOUNT;?>($)</th>
                <th style="width:15%;"><?php echo TABLE_PRODUCT;?></th>
                <th style="width:5%;"><?php echo TABLE_F_O_C;?></th>
                <th style="width:9%;"><?php echo TABLE_UOM;?></th>
            </tr>
        </table>
        <div>
            <table class="table" cellspacing="0" style="padding:0px; width:100%;">
                <?php
                $productPromoName   = '';
                $uomPromo   = 0;
                $productPromoId = '';
                $qtyPromo       = 0;
                $unitPrice      = 0;
                if(!empty($promotionDetails)){
                    $index = 0;
                    $rand  = rand();
                    $rowLength = count($promotionDetails);
                    foreach($promotionDetails AS $promotionDetail){
                        $productPromoId = $promotionDetail['PromotionalDetail']['product_promo_id'];
                        $qtyPromo       = $promotionDetail['PromotionalDetail']['qty_promo'];
                        $unitPrice      = $promotionDetail['PromotionalDetail']['unit_price'];
                        if(!empty($promotionDetail['PromotionalDetail']['product_promo_id'])){
                            $sqlProPromo = mysql_query("SELECT CONCAT_WS(' - ',code,name), price_uom_id FROM products WHERE id = ".$promotionDetail['PromotionalDetail']['product_promo_id']);
                            $rowProPromo = mysql_fetch_array($sqlProPromo);
                            $productPromoName = $rowProPromo[0];
                            $uomPromo = $rowProPromo[1];
                        }
                    }
                }
                ?>
                <tr class="listBodyPromotional">
                    <td class="first" style="padding:0px; text-align: center; width:12%; height: 30px;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="hidden" value="<?php echo number_format($unitPrice, 3); ?>" class="totalPrice" />
                            <input type="text" id="pricePromo" name="price[]" value="<?php echo number_format($unitPrice, 3); ?>" style="width:70%; height: 30px;" class="pricePromo floatAmt" />
                        </div>
                    </td>
                    <td style="width:15%; height: 30px;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="hidden" name="product_promo_id[]" class="productPromoId" value="<?php echo $productPromoId; ?>" />
                            <input type="text" id="productPromo<?php echo $rand; ?>" style="width:85%; height: 30px;" value="<?php echo $productPromoName; ?>" class="productPromo" <?php if($productPromoName!=''){ ?>readonly="readonly"<?php } ?>/>
                            <img alt="Delete" align="absmiddle" style="cursor: pointer; <?php if(empty($productPromoId)){ ?>display: none;<?php } ?>" class="productPromoDel" onmouseover="Tip('<?php echo ACTION_DELETE; ?>')" src="<?php echo $this->webroot . 'img/button/delete.png'; ?>" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:5%">
                        <div class="inputContainer" style="width:100%">
                            <input type="hidden" value="<?php echo number_format($qtyPromo, 0); ?>" class="totalQtyPromo" />
                            <input type="text" id="qtyPromo<?php echo $rand; ?>" name="qty_promo[]" value="<?php echo number_format($qtyPromo, 0); ?>" style="width:70%; height: 30px;" class="qtyPromo targetPoint" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; width:9%; height: 30px;">
                        <div class="inputContainer" style="width:100%"> 
                            <select name="uom_promo[]" id="uomPromo<?php echo $rand; ?>" class="uomPromo" style="width: 90%;">
                                <?php
                                if($uomPromo > 0){
                                    $query=mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$uomPromo."
                                                        UNION
                                                        SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomPromo." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$uomPromo.")
                                                        ORDER BY conversion ASC");
                                    $i = 1;
                                    $length = mysql_num_rows($query);
                                    while($data=mysql_fetch_array($query)){
                                        $selected = "";
                                        if($data['id'] == $promotionDetail['PromotionalDetail']['uom_promo']){
                                            $selected = ' selected="selected" ';
                                        }
                                        if($length == $i){
                                ?>
                                <option <?php echo $selected; ?>data-sm="<?php if($length == $i){ ?>1<?php }else{ ?>0<?php } ?>" data-item="<?php if($data['id'] == $uomPromo){ echo "first"; }else{ echo "other";} ?>" value="<?php echo $data['id']; ?>" conversion="<?php echo $data['conversion']; ?>"><?php echo $data['name']; ?></option>
                                <?php   }
                                        $i++;
                                    }
                                } else {
                                ?>
                                <option value=""><?php echo INPUT_SELECT; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="thirdPromotion">
        <table class="table" cellspacing="0" style="padding:0px; width:100%;">
            <tr>
                <th class="first" style="width:50%;"><?php echo TABLE_TOTAL_AMOUNT;?>($)</th>
                <th style=""><?php echo TABLE_DIS_PERCENT;?></th>
            </tr>
        </table>
        <div>
            <table class="table" cellspacing="0" style="padding:0px; width:100%;">
                <?php
                $unitPrice      = 0;
                $discountPer    = 0;
                if(!empty($promotionDetails)){
                    $index = 0;
                    foreach($promotionDetails AS $promotionDetail){
                        $unitPrice      = $promotionDetail['PromotionalDetail']['unit_price'];
                        $discountPer    = $promotionDetail['PromotionalDetail']['discount_percent'];
                    }
                }
                ?>
                <tr class="listBodyPromotional">
                    <td class="first" style="padding:0px; text-align: center; width:50%; height: 40px;">
                        <div class="inputContainer" style="width:100%">
                            <input type="hidden" value="<?php echo number_format($unitPrice, 3); ?>" class="totalPrice" />
                            <input type="text" id="pricePromo" name="price[]" value="<?php echo number_format($unitPrice, 3); ?>" style="width:70%; height: 30px;" class="pricePromo floatAmt" />
                        </div>
                    </td>
                    <td style="padding:0px; text-align: center; height: 40px;">
                        <div class="inputContainer" style="width:100%"> 
                            <input type="hidden" value="<?php echo number_format($discountPer, 2); ?>" class="discountPercent" />
                            <input type="text" id="discountPercentPromo<?php echo $index; ?>" name="discount_percent[]" value="<?php echo number_format($discountPer, 2); ?>" style="width:70%; height: 30px;" class="discountPercentPromo floatAmt" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="fouthPromotion">

    </div>
</div>
<br>
<div style="clear: both;"></div>
<div id="requestPromotionalFooter">
    <div class="buttons">
        <a href="#" class="positive btnBackPromotional">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div class="buttons">
        <button type="submit" class="positive btnSavePromotional">
            <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
            <span class="txtSavePromotional"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</div>
<?php echo $this->Form->end(); ?>