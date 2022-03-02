<script type="text/javascript">
    $(document).ready(function(){
        <?php
        $sqlCompany = mysql_query("SELECT * FROM e_store_shares WHERE 1;");
        while($rowCompany = mysql_fetch_array($sqlCompany)){
        ?>
        new DG.OnOffSwitch({
            el: '#shareShopECommerce<?php echo $rowCompany['id']; ?>',
            height: 28,
            textOn:'On',
	    textOff:'Off',
            listener: function(name, checked){
                var status = 0;
                if(checked == true){
                    status = 1;
                    $("#btnSaveShop<?php echo $rowCompany['id']; ?>").show();
                    $("#btnSavePgroupShop<?php echo $rowCompany['id']; ?>").show();
                    $("#sharePgroup<?php echo $rowCompany['id']; ?>").attr('disabled', false);
                } else {
                    $("#btnSaveShop<?php echo $rowCompany['id']; ?>").hide();
                    $("#btnSavePgroupShop<?php echo $rowCompany['id']; ?>").hide();
                    $("#sharePgroup<?php echo $rowCompany['id']; ?>").attr('disabled', true);
                }
                updateShopShare(<?php echo $rowCompany['id']; ?>, status);
            }
        });
        <?php
        }
        ?>
        // Hide / Show Header
        $(".hideShowShopDetail").click(function(){
            var img    = '<?php echo $this->webroot . 'img/button/'; ?>';
            var action = $(this).attr("act");
            var panel  = $(this).attr("data");
            if(action == '1'){
                $(this).attr("act", "0");
                $("#divShop"+panel).hide();
                img += 'arrow-down.png';
            } else {
                $(this).attr("act", "1");
                $("#divShop"+panel).show();
                img += 'arrow-up.png';
            }
            $(this).attr("src", img);
        });
        // Save Edit Shop
        $(".saveShopShare").click(function(event){
            event.preventDefault();
            var frmData = $(this).closest("form").serialize();
            var objSave = $(this);
            var validateBack = $(this).closest("form").validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/editShopShare/",
                    data: frmData,
                    beforeSend: function(){
                        objSave.attr('disable', true);
                        objSave.find('span').html("<?php echo ACTION_LOADING; ?>");
                        $("#dialog").dialog("close");
                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                    },
                    success: function(result){
                        objSave.attr('disable', false);
                        objSave.find('span').html("<?php echo ACTION_SAVE; ?>");
                        $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                        if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                            createSysAct('E-Commerce Shop', 'Edit', 2, result);
                            $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
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
                        }else {
                            createSysAct('E-Commerce Shop', 'Edit', 1, '');
                        }
                    }
                });
            }
        });
        // Save Product Group Share
        $(".savePgroupShare").click(function(event){
            event.preventDefault();
            var frmData = $(this).closest("form").serialize();
            var objSave = $(this);
            $.ajax({
                type: "POST",
                url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/savePgroupShare/",
                data: frmData,
                beforeSend: function(){
                    objSave.attr('disable', true);
                    objSave.find('span').html("<?php echo ACTION_LOADING; ?>");
                    $("#dialog").dialog("close");
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                },
                success: function(result){
                    objSave.attr('disable', false);
                    objSave.find('span').html("<?php echo ACTION_SAVE; ?>");
                    $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                    if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                        createSysAct('E-Commerce Shop', 'Edit', 2, result);
                        $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
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
                    }else {
                        createSysAct('E-Commerce Shop', 'Edit', 1, '');
                    }
                }
            });
        });
        // Check Box Pgroup Share
        $(".sharePgroup").click(function(){
            if($(this).is(':checked')){
                $(this).closest("tr").find(".optSharePgroup").val(1);
            } else {
                $(this).closest("tr").find(".optSharePgroup").val(0);
            }
        });
    });
    
    function updateShopShare(id, status){
        $.ajax({
            type: "GET",
            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/saveShopShare/"+id+"/"+status,
            data: "",
            beforeSend: function(){
                $("#dialog").dialog("close");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result){
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                if(result != '<?php echo MESSAGE_DATA_INVALID; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                    createSysAct('E-Commerce Shop', 'Share', 2, result);
                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
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
                }else {
                    createSysAct('E-Commerce Shop', 'Share', 1, '');
                }
            }
        });
    }
</script>
<div>
    <?php
    $sqlCompany = mysql_query("SELECT * FROM e_store_shares WHERE 1;");
    while($rowCompany = mysql_fetch_array($sqlCompany)){
    ?>
    <div class="legend">
        <div class="legend_title" style="margin-top: 5px; margin-bottom: 0px;">
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="font-size: 14px; font-weight: bold;"><?php echo $rowCompany['name']; ?></td>
                    <td style="width: 30px;"><input type="hidden" id="shareShopECommerce<?php echo $rowCompany['id']; ?>" name="data[shop_share]" value="<?php echo $rowCompany['is_share']; ?>"></td>
                    <td style="width: 17px; text-align: right;">
                        <img class="hideShowShopDetail" act="1" align="absmiddle" style="width: 16px; height: 16px; cursor: pointer;" data="<?php echo $rowCompany['id']; ?>" src="<?php echo $this->webroot . 'img/button/arrow-up.png'; ?>" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div style="width: 99%; border: 1px solid #00afc1; padding: 5px;" id="divShop<?php echo $rowCompany['id']; ?>">
        <form id="formSaveShop<?php echo $rowCompany['id']; ?>">
            <input type="hidden" name="data[EStoreShare][id]" value="<?php echo $rowCompany['id']; ?>" />
            <input type="hidden" name="data[EStoreShare][sys_code]" value="<?php echo $rowCompany['sys_code']; ?>" />
            <table cellpadding="5" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label for="shopName<?php echo $rowCompany['id']; ?>"><?php echo TABLE_NAME; ?> : <span class="red">*</span></label></td>
                    <td style="width: 23%;">
                        <div class="inputContainer" style="width: 100%;">
                            <input type="text" id="shopName<?php echo $rowCompany['id']; ?>" style="width: 90%;" name="data[EStoreShare][name]" value="<?php echo $rowCompany['name']; ?>" class="validate[required]" />
                        </div>
                    </td>
                    <td style="width: 10%;"><label for="shopTel<?php echo $rowCompany['id']; ?>"><?php echo TABLE_TELEPHONE; ?> : <span class="red">*</span></label></td>
                    <td>
                        <div class="inputContainer" style="width: 100%;">
                            <input type="text" id="shopTel<?php echo $rowCompany['id']; ?>" style="width: 90%;" name="data[EStoreShare][telephone]" value="<?php echo $rowCompany['telephone']; ?>" class="validate[required]" />
                        </div>
                    </td>
                    <td style="width: 10%;"><label for="shopMail<?php echo $rowCompany['id']; ?>"><?php echo TABLE_EMAIL; ?> : <span class="red">*</span></label></td>
                    <td>
                        <div class="inputContainer" style="width: 100%;">
                            <input type="text" id="shopMail<?php echo $rowCompany['id']; ?>" style="width: 90%;" name="data[EStoreShare][e_mail]" value="<?php echo $rowCompany['e_mail']; ?>" class="validate[required,custom[email]]" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 10%; vertical-align: top;"><label for="shopWebsite<?php echo $rowCompany['id']; ?>"><?php echo TABLE_WEBSITE; ?></label></td>
                    <td style="vertical-align: top;">
                        <div class="inputContainer" style="width: 100%;">
                            <input type="text" id="shopWebsite<?php echo $rowCompany['id']; ?>" style="width: 90%;" name="data[EStoreShare][website]" value="<?php echo $rowCompany['website']; ?>" />
                        </div>
                    </td>
                    <td style="width: 10%; vertical-align: top;"><label for="shopAddress<?php echo $rowCompany['id']; ?>"><?php echo TABLE_ADDRESS; ?> : <span class="red">*</span></label></td>
                    <td style="vertical-align: top;" rowspan="2">
                        <div class="inputContainer" style="width: 100%;">
                            <textarea id="shopAddress<?php echo $rowCompany['id']; ?>" style="width: 90%; height: 50px;" name="data[EStoreShare][address]" class="validate[required]"><?php echo $rowCompany['address']; ?></textarea>
                        </div>
                    </td>
                    <td style="width: 10%; vertical-align: top;"><label for="shopDescription<?php echo $rowCompany['id']; ?>"><?php echo GENERAL_DESCRIPTION; ?> : <span class="red">*</span></label></td>
                    <td style="vertical-align: top;" rowspan="2">
                        <div class="inputContainer" style="width: 100%;">
                            <textarea id="shopDescription<?php echo $rowCompany['id']; ?>" style="width: 90%; height: 50px;" name="data[EStoreShare][description]" class="validate[required]"><?php echo $rowCompany['description']; ?></textarea>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><span class="red" style="display: none;">Note: Product price take from price type e-commerce in branch head office.</span></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <div class="buttons" id="btnSaveShop<?php echo $rowCompany['id']; ?>" <?php if($rowCompany['is_share'] == 0){ ?>style="display: none;"<?php } ?>>
                            <a href="" class="positive saveShopShare">
                                <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                                <span><?php echo ACTION_SAVE; ?></span>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                    </td>
                </tr>
            </table>
        </form>
        <form id="formSavePgroup<?php echo $rowCompany['id']; ?>">
            <input type="hidden" name="data[company_id]" value="<?php echo $rowCompany['company_id']; ?>" />
            <table cellpadding="5" cellspacing="0" class="table" style="width: 100%;">
                <tr>
                    <th class="first"><?php echo TABLE_NO; ?></th>
                    <th><?php echo MENU_PRODUCT_GROUP_MANAGEMENT; ?></th>
                    <th><?php echo TABLE_E_COMMERCE_CATEGORY; ?></th>
                    <th><?php echo TABLE_SHARE; ?></th>
                </tr>
                <?php
                $index = 0;
                $sqlPgroup = mysql_query("SELECT * FROM pgroups WHERE id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id = ".$rowCompany['id'].") AND is_active = 1;");
                while($rowPgroup = mysql_fetch_array($sqlPgroup)){
                    $isShare  = 0;
                    $cateUse  = '';
                    $sqlShare = mysql_query("SELECT * FROM e_pgroup_shares WHERE company_id = ".$rowCompany['id']." AND pgroup_id = ".$rowPgroup['id']);
                    if(mysql_num_rows($sqlShare)){
                        $isShare = 1;
                        $rowShare = mysql_fetch_array($sqlShare);
                        $cateUse  = $rowShare['e_product_category_id'];
                    }
                ?>
                <tr>
                    <td class="first"><?php echo ++$index; ?></td>
                    <td>
                        <input type="hidden" name="id[]" value="<?php echo $rowPgroup['id']; ?>" />
                        <?php echo $rowPgroup['name']; ?>
                    </td>
                    <td>
                        <select name="category[]" style="width: 90%;">
                            <option value=""><?php echo INPUT_SELECT; ?></option>
                            <?php
                            foreach($categories AS $category){
                            ?>
                            <option <?php if($cateUse == $category['EProductCategory']['id']){ ?>selected="selected"<?php } ?> value="<?php echo $category['EProductCategory']['id']; ?>"><?php echo $category['EProductCategory']['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="share[]" value="<?php echo $isShare; ?>" class="optSharePgroup" />
                        <input type="checkbox" <?php if($isShare == 1){ ?>checked="checked"<?php } ?> value="1" class="sharePgroup" id="sharePgroup<?php echo $rowCompany['id']; ?>" <?php if($rowCompany['is_share'] == 0){ ?>disabled=""<?php } ?> />
                    </td>
                </tr>
                <?php
                }
                ?>
            </table>
            <div class="buttons" id="btnSavePgroupShop<?php echo $rowCompany['id']; ?>" <?php if($rowCompany['is_share'] == 0){ ?>style="display: none;"<?php } ?>>
                <a href="" class="positive savePgroupShare">
                    <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                    <span><?php echo ACTION_SAVE; ?></span>
                </a>
            </div>
            <div style="clear: both;"></div>
        </form>
    </div>
    <?php
    }
    ?>
</div>